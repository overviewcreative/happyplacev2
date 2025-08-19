<?php
/**
 * Configuration Sync Manager
 * Handles synchronization of post types, taxonomies, and ACF field groups
 * with automatic change detection and dashboard integration
 *
 * @package HappyPlace\Core
 */

namespace HappyPlace\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Config_Sync_Manager {

    private static $instance = null;
    private $config_dir;
    private $changes_detected = [];
    private $last_check_time;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->config_dir = HP_PLUGIN_DIR . 'includes/config/';
        $this->last_check_time = get_option('hp_config_last_check', 0);
    }

    public function init() {
        // Hook into WordPress init to check for changes
        add_action('init', [$this, 'check_config_changes'], 1);
        
        // Admin hooks for sync management
        add_action('admin_init', [$this, 'handle_sync_actions']);
        add_action('admin_notices', [$this, 'show_sync_notices']);
        
        // Dashboard integration
        add_action('wp_ajax_hp_get_sync_status', [$this, 'ajax_get_sync_status']);
        add_action('wp_ajax_hp_sync_config', [$this, 'ajax_sync_config']);
        add_action('wp_ajax_hp_dismiss_sync_notice', [$this, 'ajax_dismiss_sync_notice']);
        
        hp_log('Config Sync Manager initialized', 'info', 'CONFIG_SYNC');
    }

    /**
     * Check for configuration changes in JSON files
     */
    public function check_config_changes() {
        $current_time = time();
        
        // Only check every 5 minutes to avoid performance issues
        if ($current_time - $this->last_check_time < 300) {
            return;
        }

        $this->changes_detected = [];
        
        // Check post types
        $this->check_post_types_changes();
        
        // Check taxonomies
        $this->check_taxonomies_changes();
        
        // Check ACF field groups
        $this->check_acf_field_groups_changes();
        
        // Update last check time
        update_option('hp_config_last_check', $current_time);
        $this->last_check_time = $current_time;
        
        // Store detected changes
        if (!empty($this->changes_detected)) {
            update_option('hp_config_changes_detected', $this->changes_detected);
            hp_log('Configuration changes detected: ' . json_encode($this->changes_detected), 'info', 'CONFIG_SYNC');
        }
    }

    /**
     * Check for post type configuration changes
     */
    private function check_post_types_changes() {
        $config_file = $this->config_dir . 'post-types.json';
        
        if (!file_exists($config_file)) {
            return;
        }

        $file_mtime = filemtime($config_file);
        $last_sync = get_option('hp_post_types_last_sync', 0);
        
        if ($file_mtime > $last_sync) {
            $this->changes_detected['post_types'] = [
                'type' => 'post_types',
                'file' => 'post-types.json',
                'last_modified' => $file_mtime,
                'last_sync' => $last_sync,
                'status' => 'needs_sync'
            ];
        }
    }

    /**
     * Check for taxonomy configuration changes
     */
    private function check_taxonomies_changes() {
        $config_file = $this->config_dir . 'taxonomies.json';
        
        if (!file_exists($config_file)) {
            return;
        }

        $file_mtime = filemtime($config_file);
        $last_sync = get_option('hp_taxonomies_last_sync', 0);
        
        if ($file_mtime > $last_sync) {
            $this->changes_detected['taxonomies'] = [
                'type' => 'taxonomies',
                'file' => 'taxonomies.json',
                'last_modified' => $file_mtime,
                'last_sync' => $last_sync,
                'status' => 'needs_sync'
            ];
        }
    }

    /**
     * Check for ACF field group changes
     */
    private function check_acf_field_groups_changes() {
        $acf_json_dir = HP_PLUGIN_DIR . 'includes/fields/acf-json/';
        
        if (!is_dir($acf_json_dir)) {
            return;
        }

        $json_files = glob($acf_json_dir . '*.json');
        $last_sync = get_option('hp_acf_fields_last_sync', 0);
        $needs_sync = false;

        foreach ($json_files as $file) {
            // Skip export files
            if (strpos(basename($file), 'acf-export-') === 0) {
                continue;
            }

            $file_mtime = filemtime($file);
            if ($file_mtime > $last_sync) {
                $needs_sync = true;
                break;
            }
        }

        if ($needs_sync) {
            $this->changes_detected['acf_fields'] = [
                'type' => 'acf_fields',
                'file' => 'acf-json/*.json',
                'last_modified' => max(array_map('filemtime', $json_files)),
                'last_sync' => $last_sync,
                'status' => 'needs_sync',
                'count' => count($json_files)
            ];
        }
    }

    /**
     * Get current sync status for dashboard
     */
    public function get_sync_status() {
        $this->check_config_changes();
        
        $stored_changes = get_option('hp_config_changes_detected', []);
        $dismissed_notices = get_option('hp_dismissed_sync_notices', []);
        
        $status = [
            'has_changes' => !empty($stored_changes),
            'changes' => $stored_changes,
            'dismissed' => $dismissed_notices,
            'last_check' => $this->last_check_time
        ];

        return $status;
    }

    /**
     * Sync post types from JSON configuration
     */
    public function sync_post_types() {
        $config_file = $this->config_dir . 'post-types.json';
        
        if (!file_exists($config_file)) {
            return ['success' => false, 'message' => 'Post types configuration file not found'];
        }

        $config_data = json_decode(file_get_contents($config_file), true);
        if (!$config_data || !isset($config_data['post_types'])) {
            return ['success' => false, 'message' => 'Invalid post types configuration'];
        }

        $synced_count = 0;
        $errors = [];

        foreach ($config_data['post_types'] as $post_type => $config) {
            try {
                // Register the post type
                register_post_type($post_type, $config);
                $synced_count++;
                hp_log("Synced post type: {$post_type}", 'info', 'CONFIG_SYNC');
            } catch (Exception $e) {
                $errors[] = "Failed to sync post type {$post_type}: " . $e->getMessage();
                hp_log("Error syncing post type {$post_type}: " . $e->getMessage(), 'error', 'CONFIG_SYNC');
            }
        }

        // Update last sync time
        update_option('hp_post_types_last_sync', time());

        // Flush rewrite rules
        flush_rewrite_rules();

        return [
            'success' => true,
            'synced' => $synced_count,
            'errors' => $errors
        ];
    }

    /**
     * Sync taxonomies from JSON configuration
     */
    public function sync_taxonomies() {
        $config_file = $this->config_dir . 'taxonomies.json';
        
        if (!file_exists($config_file)) {
            return ['success' => false, 'message' => 'Taxonomies configuration file not found'];
        }

        $config_data = json_decode(file_get_contents($config_file), true);
        if (!$config_data || !isset($config_data['taxonomies'])) {
            return ['success' => false, 'message' => 'Invalid taxonomies configuration'];
        }

        $synced_count = 0;
        $errors = [];

        foreach ($config_data['taxonomies'] as $taxonomy => $config) {
            try {
                $object_type = $config['object_type'] ?? ['post'];
                unset($config['object_type']); // Remove from config as it's passed separately

                register_taxonomy($taxonomy, $object_type, $config);
                $synced_count++;
                hp_log("Synced taxonomy: {$taxonomy}", 'info', 'CONFIG_SYNC');
            } catch (Exception $e) {
                $errors[] = "Failed to sync taxonomy {$taxonomy}: " . $e->getMessage();
                hp_log("Error syncing taxonomy {$taxonomy}: " . $e->getMessage(), 'error', 'CONFIG_SYNC');
            }
        }

        // Update last sync time
        update_option('hp_taxonomies_last_sync', time());

        // Flush rewrite rules
        flush_rewrite_rules();

        return [
            'success' => true,
            'synced' => $synced_count,
            'errors' => $errors
        ];
    }

    /**
     * Sync ACF field groups
     */
    public function sync_acf_field_groups() {
        if (!class_exists('HappyPlace\\Core\\ACF_Manager')) {
            return ['success' => false, 'message' => 'ACF Manager not available'];
        }

        $acf_manager = ACF_Manager::get_instance();
        
        if (!method_exists($acf_manager, 'force_sync_field_groups')) {
            return ['success' => false, 'message' => 'ACF sync method not available'];
        }

        $result = $acf_manager->force_sync_field_groups();

        if ($result !== false) {
            // Update last sync time
            update_option('hp_acf_fields_last_sync', time());

            return [
                'success' => true,
                'synced' => $result,
                'errors' => []
            ];
        } else {
            return ['success' => false, 'message' => 'ACF field groups sync failed'];
        }
    }

    /**
     * Sync all configurations
     */
    public function sync_all() {
        $results = [
            'post_types' => $this->sync_post_types(),
            'taxonomies' => $this->sync_taxonomies(),
            'acf_fields' => $this->sync_acf_field_groups()
        ];

        // Clear detected changes after successful sync
        $all_success = true;
        foreach ($results as $result) {
            if (!$result['success']) {
                $all_success = false;
                break;
            }
        }

        if ($all_success) {
            delete_option('hp_config_changes_detected');
        }

        return $results;
    }

    /**
     * Handle sync actions from admin
     */
    public function handle_sync_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['hp_sync_action'])) {
            $action = sanitize_text_field($_GET['hp_sync_action']);
            $nonce = $_GET['_wpnonce'] ?? '';

            if (!wp_verify_nonce($nonce, 'hp_sync_action')) {
                return;
            }

            switch ($action) {
                case 'sync_post_types':
                    $result = $this->sync_post_types();
                    $this->show_admin_notice($result, 'Post Types');
                    break;

                case 'sync_taxonomies':
                    $result = $this->sync_taxonomies();
                    $this->show_admin_notice($result, 'Taxonomies');
                    break;

                case 'sync_acf_fields':
                    $result = $this->sync_acf_field_groups();
                    $this->show_admin_notice($result, 'ACF Field Groups');
                    break;

                case 'sync_all':
                    $results = $this->sync_all();
                    $this->show_sync_all_notice($results);
                    break;
            }
        }
    }

    /**
     * Show admin notice for sync results
     */
    private function show_admin_notice($result, $type) {
        if ($result['success']) {
            add_action('admin_notices', function() use ($result, $type) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>' . $type . ' Sync:</strong> Successfully synced ' . $result['synced'] . ' items.</p>';
                if (!empty($result['errors'])) {
                    echo '<p><strong>Warnings:</strong></p><ul>';
                    foreach ($result['errors'] as $error) {
                        echo '<li>' . esc_html($error) . '</li>';
                    }
                    echo '</ul>';
                }
                echo '</div>';
            });
        } else {
            add_action('admin_notices', function() use ($result, $type) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>' . $type . ' Sync Failed:</strong> ' . esc_html($result['message']) . '</p>';
                echo '</div>';
            });
        }
    }

    /**
     * Show admin notice for sync all results
     */
    private function show_sync_all_notice($results) {
        add_action('admin_notices', function() use ($results) {
            $success_count = 0;
            $total_synced = 0;

            foreach ($results as $type => $result) {
                if ($result['success']) {
                    $success_count++;
                    $total_synced += $result['synced'] ?? 0;
                }
            }

            if ($success_count === count($results)) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>Complete Sync Successful:</strong> Synced ' . $total_synced . ' configuration items.</p>';
                echo '</div>';
            } else {
                echo '<div class="notice notice-warning is-dismissible">';
                echo '<p><strong>Partial Sync Completed:</strong> ' . $success_count . ' of ' . count($results) . ' configurations synced successfully.</p>';
                echo '</div>';
            }
        });
    }

    /**
     * Show sync notices in admin
     */
    public function show_sync_notices() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $changes = get_option('hp_config_changes_detected', []);
        $dismissed = get_option('hp_dismissed_sync_notices', []);

        if (empty($changes)) {
            return;
        }

        foreach ($changes as $type => $change) {
            if (in_array($type, $dismissed)) {
                continue;
            }

            $this->render_sync_notice($type, $change);
        }
    }

    /**
     * Render individual sync notice
     */
    private function render_sync_notice($type, $change) {
        $type_labels = [
            'post_types' => 'Post Types',
            'taxonomies' => 'Taxonomies',
            'acf_fields' => 'ACF Field Groups'
        ];

        $label = $type_labels[$type] ?? ucfirst($type);
        $sync_url = wp_nonce_url(
            admin_url("admin.php?page=happy-place-sync&sync_action=sync_{$type}"),
            'hp_sync_action'
        );
        $dismiss_url = wp_nonce_url(
            admin_url("admin.php?hp_dismiss_notice={$type}"),
            'hp_dismiss_notice'
        );

        echo '<div class="notice notice-info is-dismissible hp-sync-notice" data-type="' . esc_attr($type) . '">';
        echo '<p><strong>🔄 Happy Place Configuration Update</strong></p>';
        echo '<p>' . $label . ' configuration has been updated. ';
        echo '<strong>Last modified:</strong> ' . date('M j, Y g:i A', $change['last_modified']) . '</p>';
        
        echo '<p>';
        echo '<a href="' . esc_url($sync_url) . '" class="button button-primary">Sync ' . $label . '</a> ';
        echo '<a href="' . esc_url($dismiss_url) . '" class="button button-secondary">Dismiss</a>';
        echo '</p>';
        echo '</div>';
    }

    /**
     * AJAX handler for getting sync status
     */
    public function ajax_get_sync_status() {
        check_ajax_referer('hp_dashboard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $status = $this->get_sync_status();
        wp_send_json_success($status);
    }

    /**
     * AJAX handler for syncing configurations
     */
    public function ajax_sync_config() {
        check_ajax_referer('hp_dashboard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $type = sanitize_text_field($_POST['type'] ?? 'all');

        switch ($type) {
            case 'post_types':
                $result = $this->sync_post_types();
                break;
            case 'taxonomies':
                $result = $this->sync_taxonomies();
                break;
            case 'acf_fields':
                $result = $this->sync_acf_field_groups();
                break;
            case 'all':
            default:
                $result = $this->sync_all();
                break;
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX handler for dismissing sync notices
     */
    public function ajax_dismiss_sync_notice() {
        check_ajax_referer('hp_dashboard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $type = sanitize_text_field($_POST['type'] ?? '');
        if (empty($type)) {
            wp_send_json_error('Type not specified');
        }

        $dismissed = get_option('hp_dismissed_sync_notices', []);
        if (!in_array($type, $dismissed)) {
            $dismissed[] = $type;
            update_option('hp_dismissed_sync_notices', $dismissed);
        }

        wp_send_json_success(['dismissed' => $type]);
    }

    /**
     * Export current WordPress configurations to JSON
     */
    public function export_current_configs() {
        $results = [
            'post_types' => $this->export_post_types_config(),
            'taxonomies' => $this->export_taxonomies_config()
        ];

        return $results;
    }

    /**
     * Export registered post types to JSON configuration
     */
    private function export_post_types_config() {
        $post_types = get_post_types(['_builtin' => false], 'objects');
        $config = [
            'version' => '1.0.0',
            'last_modified' => time(),
            'post_types' => []
        ];

        foreach ($post_types as $post_type => $post_type_object) {
            // Only export Happy Place post types
            if (!in_array($post_type, ['listing', 'agent', 'community', 'open_house', 'lead'])) {
                continue;
            }

            $config['post_types'][$post_type] = [
                'label' => $post_type_object->label,
                'labels' => (array) $post_type_object->labels,
                'description' => $post_type_object->description,
                'public' => $post_type_object->public,
                'publicly_queryable' => $post_type_object->publicly_queryable,
                'show_ui' => $post_type_object->show_ui,
                'show_in_menu' => $post_type_object->show_in_menu,
                'query_var' => $post_type_object->query_var,
                'rewrite' => $post_type_object->rewrite,
                'capability_type' => $post_type_object->capability_type,
                'has_archive' => $post_type_object->has_archive,
                'hierarchical' => $post_type_object->hierarchical,
                'menu_position' => $post_type_object->menu_position,
                'menu_icon' => $post_type_object->menu_icon,
                'supports' => $post_type_object->supports ?? [],
                'show_in_rest' => $post_type_object->show_in_rest,
            ];
        }

        $config_file = $this->config_dir . 'post-types.json';
        $result = file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));

        return $result !== false ? ['success' => true, 'count' => count($config['post_types'])] : ['success' => false];
    }

    /**
     * Export registered taxonomies to JSON configuration
     */
    private function export_taxonomies_config() {
        $taxonomies = get_taxonomies(['_builtin' => false], 'objects');
        $config = [
            'version' => '1.0.0',
            'last_modified' => time(),
            'taxonomies' => []
        ];

        foreach ($taxonomies as $taxonomy => $taxonomy_object) {
            $config['taxonomies'][$taxonomy] = [
                'label' => $taxonomy_object->label,
                'labels' => (array) $taxonomy_object->labels,
                'description' => $taxonomy_object->description,
                'public' => $taxonomy_object->public,
                'publicly_queryable' => $taxonomy_object->publicly_queryable,
                'hierarchical' => $taxonomy_object->hierarchical,
                'show_ui' => $taxonomy_object->show_ui,
                'show_in_menu' => $taxonomy_object->show_in_menu,
                'show_in_nav_menus' => $taxonomy_object->show_in_nav_menus,
                'show_tagcloud' => $taxonomy_object->show_tagcloud,
                'show_in_quick_edit' => $taxonomy_object->show_in_quick_edit,
                'show_admin_column' => $taxonomy_object->show_admin_column,
                'object_type' => $taxonomy_object->object_type,
                'rewrite' => $taxonomy_object->rewrite,
                'query_var' => $taxonomy_object->query_var,
                'show_in_rest' => $taxonomy_object->show_in_rest,
            ];
        }

        $config_file = $this->config_dir . 'taxonomies.json';
        $result = file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));

        return $result !== false ? ['success' => true, 'count' => count($config['taxonomies'])] : ['success' => false];
    }
}