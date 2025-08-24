<?php
/**
 * ACF Sync Manager
 * Handles field group synchronization, cleanup, and refresh functionality
 *
 * @package HappyPlace
 */

namespace HappyPlace\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class AcfSyncManager {

    private static $instance = null;
    private $json_save_path;
    private $field_groups_loaded = [];
    private $orphaned_groups = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->json_save_path = HP_PLUGIN_DIR . 'includes/fields/acf-json/';
        $this->init_hooks();
    }

    private function init_hooks() {
        // NOTE: ACF JSON save/load points are handled by ACF_Manager to prevent duplicates
        // DO NOT register acf/settings/save_json and acf/settings/load_json here
        
        // Set ACF JSON save and load points - DISABLED to prevent duplicates
        // add_filter('acf/settings/save_json', [$this, 'acf_json_save_point']);
        // add_filter('acf/settings/load_json', [$this, 'acf_json_load_point']);
        
        // Admin menu for sync management
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // AJAX handlers
        add_action('wp_ajax_hpt_sync_field_groups', [$this, 'ajax_sync_field_groups']);
        add_action('wp_ajax_hpt_cleanup_field_groups', [$this, 'ajax_cleanup_field_groups']);
        add_action('wp_ajax_hpt_refresh_field_groups', [$this, 'ajax_refresh_field_groups']);
        
        // Admin notices
        add_action('admin_notices', [$this, 'sync_notices']);
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function acf_json_save_point($path) {
        return $this->json_save_path;
    }

    public function acf_json_load_point($paths) {
        $paths[] = $this->json_save_path;
        return $paths;
    }

    public function add_admin_menu() {
        add_submenu_page(
            'happy-place',
            __('ACF Field Groups', 'happy-place'),
            __('Field Groups', 'happy-place'),
            'manage_options',
            'happy-place-acf-sync',
            [$this, 'render_sync_page']
        );
    }

    public function enqueue_admin_scripts($hook) {
        // Check for our admin page - the hook will be something like 'happy-place_page_happy-place-acf-sync'
        if (strpos($hook, 'happy-place-acf-sync') === false) {
            return;
        }

        wp_enqueue_script(
            'hpt-acf-sync',
            HP_ASSETS_URL . 'js/admin/acf-sync.js',
            ['jquery'],
            HP_VERSION,
            true
        );

        wp_localize_script('hpt-acf-sync', 'hptACFSync', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hpt_acf_sync'),
            'strings' => [
                'syncing' => __('Syncing field groups...', 'happy-place'),
                'cleaning' => __('Cleaning up field groups...', 'happy-place'),
                'refreshing' => __('Refreshing field groups...', 'happy-place'),
                'success' => __('Operation completed successfully', 'happy-place'),
                'error' => __('An error occurred', 'happy-place'),
                'confirmCleanup' => __('Are you sure you want to remove unused field groups? This cannot be undone.', 'happy-place'),
            ]
        ]);
    }

    public function render_sync_page() {
        $this->analyze_field_groups();
        
        ?>
        <div class="wrap">
            <h1><?php _e('ACF Field Groups Sync', 'happy-place'); ?></h1>
            
            <div class="hpt-acf-sync-container">
                <div class="hpt-sync-status">
                    <h2><?php _e('Field Groups Status', 'happy-place'); ?></h2>
                    
                    <div class="hpt-status-grid">
                        <div class="hpt-status-card">
                            <h3><?php _e('JSON Files', 'happy-place'); ?></h3>
                            <div class="hpt-status-number"><?php echo count($this->get_json_field_groups()); ?></div>
                            <p><?php _e('Field groups in JSON files', 'happy-place'); ?></p>
                        </div>
                        
                        <div class="hpt-status-card">
                            <h3><?php _e('Database Groups', 'happy-place'); ?></h3>
                            <div class="hpt-status-number"><?php echo count($this->get_database_field_groups()); ?></div>
                            <p><?php _e('Field groups in database', 'happy-place'); ?></p>
                        </div>
                        
                        <div class="hpt-status-card">
                            <h3><?php _e('Orphaned Groups', 'happy-place'); ?></h3>
                            <div class="hpt-status-number hpt-status-warning"><?php echo count($this->orphaned_groups); ?></div>
                            <p><?php _e('Groups without JSON files', 'happy-place'); ?></p>
                        </div>
                        
                        <div class="hpt-status-card">
                            <h3><?php _e('Out of Sync', 'happy-place'); ?></h3>
                            <div class="hpt-status-number hpt-status-error"><?php echo count($this->get_out_of_sync_groups()); ?></div>
                            <p><?php _e('Groups needing sync', 'happy-place'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="hpt-sync-actions">
                    <h2><?php _e('Sync Actions', 'happy-place'); ?></h2>
                    
                    <div class="hpt-action-buttons">
                        <button type="button" id="sync-field-groups" class="button button-primary">
                            <span class="dashicons dashicons-update"></span>
                            <?php _e('Sync All Field Groups', 'happy-place'); ?>
                        </button>
                        
                        <button type="button" id="refresh-field-groups" class="button button-secondary">
                            <span class="dashicons dashicons-arrow-down-alt"></span>
                            <?php _e('Refresh from JSON', 'happy-place'); ?>
                        </button>
                        
                        <button type="button" id="cleanup-field-groups" class="button button-secondary hpt-danger">
                            <span class="dashicons dashicons-trash"></span>
                            <?php _e('Cleanup Orphaned Groups', 'happy-place'); ?>
                        </button>
                    </div>
                </div>

                <div class="hpt-field-groups-list">
                    <h2><?php _e('Field Groups Details', 'happy-place'); ?></h2>
                    
                    <?php $this->render_field_groups_table(); ?>
                </div>

                <div id="hpt-sync-log" class="hpt-sync-log" style="display: none;">
                    <h3><?php _e('Sync Log', 'happy-place'); ?></h3>
                    <div id="hpt-sync-log-content"></div>
                </div>
            </div>
        </div>
        
        <style>
        .hpt-acf-sync-container { margin-top: 20px; }
        .hpt-status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .hpt-status-card { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; text-align: center; }
        .hpt-status-number { font-size: 2em; font-weight: bold; color: #2271b1; }
        .hpt-status-warning { color: #d63384; }
        .hpt-status-error { color: #dc3545; }
        .hpt-action-buttons { display: flex; gap: 15px; margin: 20px 0; }
        .hpt-action-buttons .button { display: flex; align-items: center; gap: 5px; }
        .hpt-danger { border-color: #dc3545; color: #dc3545; }
        .hpt-danger:hover { background-color: #dc3545; color: white; }
        .hpt-field-groups-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .hpt-field-groups-table th, .hpt-field-groups-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .hpt-field-groups-table th { background: #f8f9fa; }
        .hpt-sync-status { color: #28a745; }
        .hpt-sync-status.warning { color: #ffc107; }
        .hpt-sync-status.error { color: #dc3545; }
        .hpt-sync-log { background: #f8f9fa; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px; }
        .hpt-sync-log-content { background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 3px; height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px; }
        </style>
        <?php
    }

    private function render_field_groups_table() {
        $json_groups = $this->get_json_field_groups();
        $db_groups = $this->get_database_field_groups();
        $all_groups = array_unique(array_merge(array_keys($json_groups), array_keys($db_groups)));
        
        ?>
        <table class="hpt-field-groups-table wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Field Group', 'happy-place'); ?></th>
                    <th><?php _e('Key', 'happy-place'); ?></th>
                    <th><?php _e('JSON File', 'happy-place'); ?></th>
                    <th><?php _e('Database', 'happy-place'); ?></th>
                    <th><?php _e('Status', 'happy-place'); ?></th>
                    <th><?php _e('Actions', 'happy-place'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_groups as $group_key): ?>
                    <?php
                    $json_exists = isset($json_groups[$group_key]);
                    $db_exists = isset($db_groups[$group_key]);
                    $title = $json_exists ? $json_groups[$group_key]['title'] : ($db_exists ? $db_groups[$group_key]['title'] : $group_key);
                    $status = $this->get_group_sync_status($group_key, $json_groups, $db_groups);
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($title); ?></strong></td>
                        <td><code><?php echo esc_html($group_key); ?></code></td>
                        <td>
                            <?php if ($json_exists): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #28a745;"></span>
                                <?php _e('Exists', 'happy-place'); ?>
                            <?php else: ?>
                                <span class="dashicons dashicons-dismiss" style="color: #dc3545;"></span>
                                <?php _e('Missing', 'happy-place'); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($db_exists): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #28a745;"></span>
                                <?php _e('Exists', 'happy-place'); ?>
                            <?php else: ?>
                                <span class="dashicons dashicons-dismiss" style="color: #dc3545;"></span>
                                <?php _e('Missing', 'happy-place'); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="hpt-sync-status <?php echo $status['class']; ?>">
                                <?php echo $status['text']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!$json_exists && $db_exists): ?>
                                <button type="button" class="button button-small export-group" data-group="<?php echo esc_attr($group_key); ?>">
                                    <?php _e('Export to JSON', 'happy-place'); ?>
                                </button>
                            <?php elseif ($json_exists && !$db_exists): ?>
                                <button type="button" class="button button-small import-group" data-group="<?php echo esc_attr($group_key); ?>">
                                    <?php _e('Import to DB', 'happy-place'); ?>
                                </button>
                            <?php elseif ($status['needs_sync']): ?>
                                <button type="button" class="button button-small sync-group" data-group="<?php echo esc_attr($group_key); ?>">
                                    <?php _e('Sync', 'happy-place'); ?>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function analyze_field_groups() {
        $json_groups = $this->get_json_field_groups();
        $db_groups = $this->get_database_field_groups();
        
        // Find orphaned groups (in DB but no JSON)
        $this->orphaned_groups = array_diff_key($db_groups, $json_groups);
    }

    private function get_json_field_groups() {
        if (!empty($this->field_groups_loaded)) {
            return $this->field_groups_loaded;
        }

        // Use ACF Manager to get field groups
        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
        $this->field_groups_loaded = $acf_manager->get_field_groups();
        
        return $this->field_groups_loaded;
    }

    private function get_database_field_groups() {
        // Use ACF Manager to get database field groups
        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
        $db_groups = $acf_manager->get_all_database_field_groups();
        
        $groups = [];
        foreach ($db_groups as $group) {
            // Only include Happy Place field groups
            if (strpos($group['key'], 'group_') === 0 || 
                strpos($group['key'], 'happy_place') !== false || 
                strpos($group['key'], 'happy-place') !== false ||
                strpos($group['key'], 'hp_') !== false) {
                $groups[$group['key']] = $group;
            }
        }
        
        return $groups;
    }

    private function get_out_of_sync_groups() {
        $json_groups = $this->get_json_field_groups();
        $db_groups = $this->get_database_field_groups();
        $out_of_sync = [];
        
        foreach ($json_groups as $key => $json_group) {
            if (isset($db_groups[$key])) {
                $db_group = $db_groups[$key];
                
                // Compare modification times
                $json_time = isset($json_group['modified']) ? strtotime($json_group['modified']) : 0;
                $db_time = isset($db_group['modified']) ? strtotime($db_group['modified']) : 0;
                
                if ($json_time !== $db_time) {
                    $out_of_sync[$key] = $json_group;
                }
            }
        }
        
        return $out_of_sync;
    }

    private function get_group_sync_status($group_key, $json_groups, $db_groups) {
        $json_exists = isset($json_groups[$group_key]);
        $db_exists = isset($db_groups[$group_key]);
        
        if (!$json_exists && !$db_exists) {
            return [
                'text' => __('Missing', 'happy-place'),
                'class' => 'error',
                'needs_sync' => false
            ];
        }
        
        if (!$json_exists) {
            return [
                'text' => __('No JSON file', 'happy-place'),
                'class' => 'warning',
                'needs_sync' => true
            ];
        }
        
        if (!$db_exists) {
            return [
                'text' => __('Not in database', 'happy-place'),
                'class' => 'warning',
                'needs_sync' => true
            ];
        }
        
        // Both exist, check if they're in sync
        $json_group = $json_groups[$group_key];
        $db_group = $db_groups[$group_key];
        
        $json_time = isset($json_group['modified']) ? strtotime($json_group['modified']) : 0;
        $db_time = isset($db_group['modified']) ? strtotime($db_group['modified']) : 0;
        
        if ($json_time !== $db_time) {
            return [
                'text' => __('Out of sync', 'happy-place'),
                'class' => 'warning',
                'needs_sync' => true
            ];
        }
        
        return [
            'text' => __('In sync', 'happy-place'),
            'class' => '',
            'needs_sync' => false
        ];
    }

    public function ajax_sync_field_groups() {
        check_ajax_referer('hpt_acf_sync', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'happy-place')]);
        }

        try {
            $result = $this->sync_all_field_groups();
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_cleanup_field_groups() {
        check_ajax_referer('hpt_acf_sync', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'happy-place')]);
        }

        try {
            $result = $this->cleanup_orphaned_groups();
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_refresh_field_groups() {
        check_ajax_referer('hpt_acf_sync', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'happy-place')]);
        }

        try {
            $result = $this->refresh_from_json();
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    private function sync_all_field_groups() {
        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
        $json_groups = $this->get_json_field_groups();
        $synced = [];
        $errors = [];

        foreach ($json_groups as $key => $group_data) {
            try {
                if (function_exists('acf_import_field_group')) {
                    acf_import_field_group($group_data);
                    $synced[] = $group_data['title'];
                } else if (function_exists('acf_add_local_field_group')) {
                    acf_add_local_field_group($group_data);
                    $synced[] = $group_data['title'];
                }
            } catch (Exception $e) {
                $errors[] = sprintf(__('Failed to sync %s: %s', 'happy-place'), $group_data['title'], $e->getMessage());
            }
        }

        return [
            'synced' => count($synced),
            'errors' => $errors,
            'log' => array_merge(
                array_map(function($title) { return __('Synced: ', 'happy-place') . $title; }, $synced),
                $errors
            )
        ];
    }

    private function cleanup_orphaned_groups() {
        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
        $results = $acf_manager->cleanup_orphaned_field_groups(false);
        
        $log = [];
        foreach ($results['groups'] as $group) {
            if ($group['action'] === 'removed') {
                $log[] = __('Removed: ', 'happy-place') . $group['title'];
            }
        }
        
        $log = array_merge($log, $results['errors']);

        return [
            'removed' => $results['removed'],
            'errors' => $results['errors'],
            'log' => $log
        ];
    }

    private function refresh_from_json() {
        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
        $results = $acf_manager->refresh_field_groups_from_json();
        
        return [
            'synced' => $results['imported_from_json'],
            'errors' => $results['errors'],
            'log' => [
                sprintf(__('Removed %d existing field groups from database', 'happy-place'), $results['removed_from_db']),
                sprintf(__('Imported %d field groups from JSON files', 'happy-place'), $results['imported_from_json'])
            ]
        ];
    }

    public function sync_notices() {
        $screen = get_current_screen();
        if ($screen->id !== 'hp-settings_page_happy-place-acf-sync') {
            return;
        }

        $this->analyze_field_groups();
        $out_of_sync = $this->get_out_of_sync_groups();

        if (!empty($this->orphaned_groups)) {
            echo '<div class="notice notice-warning"><p>';
            echo sprintf(
                __('Found %d orphaned field groups that should be cleaned up.', 'happy-place'),
                count($this->orphaned_groups)
            );
            echo '</p></div>';
        }

        if (!empty($out_of_sync)) {
            echo '<div class="notice notice-warning"><p>';
            echo sprintf(
                __('Found %d field groups that are out of sync.', 'happy-place'),
                count($out_of_sync)
            );
            echo '</p></div>';
        }
    }
}