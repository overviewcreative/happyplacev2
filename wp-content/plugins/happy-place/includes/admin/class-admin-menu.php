<?php
/**
 * Admin Menu Class
 * 
 * Handles admin menu creation and management for Happy Place
 *
 * @package HappyPlace\Admin
 */

namespace HappyPlace\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Admin_Menu {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize admin menu
     */
    private function __construct() {
        // Menu will be added in init()
    }
    
    /**
     * Initialize component
     */
    public function init() {
        // Add the admin menu hook
        add_action('admin_menu', [$this, 'add_admin_menu'], 5);
        
        // Add admin styles and scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        
        // Handle AJAX requests
        add_action('wp_ajax_hp_admin_action', [$this, 'handle_admin_ajax']);
        
        if (function_exists('hp_log')) {
            hp_log('Admin Menu component initialized', 'debug', 'ADMIN_MENU');
        }
    }
    
    /**
     * Enqueue admin styles and scripts
     */
    public function enqueue_admin_styles($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'happy-place') === false) {
            return;
        }
        
        wp_enqueue_style(
            'happy-place-admin',
            HP_PLUGIN_URL . 'assets/css/admin.css',
            [],
            HP_VERSION
        );
        
        // Add inline styles for clean admin interface
        $admin_css = "
            /* Happy Place Admin Styles */
            .happy-place-admin h1 {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 20px;
            }
            
            .sync-dashboard {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-top: 20px;
            }
            
            .sync-card {
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            
            .sync-card-title {
                display: flex;
                align-items: center;
                gap: 8px;
                margin: 0 0 15px 0;
                font-size: 16px;
                font-weight: 600;
                color: #1d2327;
            }
            
            .sync-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
            }
            
            .sync-badge-success {
                background: #d4edda;
                color: #155724;
            }
            
            .sync-badge-warning {
                background: #fff3cd;
                color: #856404;
            }
            
            .sync-badge-error {
                background: #f8d7da;
                color: #721c24;
            }
            
            .sync-badge-info {
                background: #d1ecf1;
                color: #0c5460;
            }
            
            .sync-status-indicator {
                margin-bottom: 15px;
            }
            
            .sync-last-check {
                color: #646970;
                font-size: 14px;
                margin-bottom: 20px;
            }
            
            .sync-actions-primary {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .sync-changes-list {
                margin-top: 15px;
            }
            
            .sync-change-item {
                background: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 6px;
                padding: 15px;
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            
            .sync-change-info {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            
            .sync-change-info .dashicons {
                font-size: 20px;
                color: #3c434a;
            }
            
            .sync-change-details h4 {
                margin: 0 0 4px 0;
                font-size: 14px;
                font-weight: 600;
            }
            
            .sync-change-details p {
                margin: 0 0 4px 0;
                color: #646970;
                font-size: 13px;
            }
            
            .sync-change-details small {
                color: #8c8f94;
                font-size: 12px;
            }
            
            .sync-file-status {
                margin-top: 15px;
            }
            
            .file-status-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 0;
                border-bottom: 1px solid #f0f0f1;
            }
            
            .file-status-item:last-child {
                border-bottom: none;
            }
            
            .file-status-item code {
                background: #f6f7f7;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 12px;
                color: #23282d;
            }
            
            .sync-tools-grid {
                display: flex;
                gap: 10px;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }
            
            .sync-help {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #f0f0f1;
            }
            
            .sync-help h4 {
                margin: 0 0 10px 0;
                color: #1d2327;
            }
            
            .sync-help ul {
                margin: 0;
                padding-left: 20px;
            }
            
            .sync-help li {
                margin-bottom: 5px;
                color: #646970;
                font-size: 14px;
            }
            
            .import-tools-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            
            .import-tool {
                background: #f9f9f9;
                padding: 20px;
                border-radius: 6px;
                border: 1px solid #ddd;
            }
            
            .import-tool h3 {
                margin: 0 0 10px 0;
                font-size: 16px;
                color: #1d2327;
            }
            
            .import-tool p {
                margin: 0 0 15px 0;
                color: #646970;
            }
            
            .export-actions {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-top: 15px;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .stat-card {
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 8px;
                padding: 20px;
                text-align: center;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            
            .stat-number {
                font-size: 32px;
                font-weight: 700;
                color: #0073aa;
                line-height: 1;
                margin-bottom: 5px;
            }
            
            .stat-label {
                color: #646970;
                font-size: 14px;
                font-weight: 500;
            }
            
            .action-buttons {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .setting-group {
                margin-bottom: 30px;
            }
            
            .setting-group h3 {
                margin: 0 0 15px 0;
                padding: 0 0 10px 0;
                border-bottom: 1px solid #ddd;
                color: #23282d;
            }
            
            /* Responsive design */
            @media (max-width: 768px) {
                .sync-dashboard {
                    grid-template-columns: 1fr;
                }
                
                .stats-grid {
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                }
                
                .sync-change-item {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 15px;
                }
                
                .sync-actions-primary,
                .sync-tools-grid,
                .export-actions {
                    flex-direction: column;
                }
                
                .sync-actions-primary .button,
                .sync-tools-grid .button,
                .export-actions .button {
                    text-align: center;
                }
            }
            
            /* WordPress admin color scheme integration */
            .wp-admin .happy-place-admin .button-primary {
                background: #0073aa;
                border-color: #005a87 #00405f #00405f;
                color: #fff;
            }
            
            .wp-admin .happy-place-admin .button-primary:hover {
                background: #005a87;
                border-color: #00405f;
            }
            
            .wp-admin .happy-place-admin .postbox h2.hndle {
                font-size: 16px;
                font-weight: 600;
                color: #1d2327;
                border-bottom: 1px solid #ddd;
                padding-bottom: 10px;
                margin-bottom: 15px;
            }
        ";
        
        wp_add_inline_style('happy-place-admin', $admin_css);
        
        wp_enqueue_script(
            'happy-place-admin',
            HP_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            HP_VERSION,
            true
        );
        
        wp_localize_script('happy-place-admin', 'hp_admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_admin_nonce')
        ]);
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('Happy Place', 'happy-place'),
            __('Happy Place', 'happy-place'),
            'manage_options',
            'happy-place',
            [$this, 'dashboard_page'],
            'dashicons-admin-home',
            30
        );
        
        // Dashboard (rename main page)
        add_submenu_page(
            'happy-place',
            __('Dashboard', 'happy-place'),
            __('Dashboard', 'happy-place'),
            'manage_options',
            'happy-place',
            [$this, 'dashboard_page']
        );

        // === CONTENT MANAGEMENT === //
        
        // Listings management
        add_submenu_page(
            'happy-place',
            __('All Listings', 'happy-place'),
            __('All Listings', 'happy-place'),
            'edit_posts',
            'edit.php?post_type=listing'
        );
        
        // Add New Listing
        add_submenu_page(
            'happy-place',
            __('Add New Listing', 'happy-place'),
            __('Add New Listing', 'happy-place'),
            'edit_posts',
            'post-new.php?post_type=listing'
        );
        
        // Agents management
        add_submenu_page(
            'happy-place',
            __('All Agents', 'happy-place'),
            __('All Agents', 'happy-place'),
            'edit_posts',
            'edit.php?post_type=agent'
        );
        
        // Communities
        add_submenu_page(
            'happy-place',
            __('Communities', 'happy-place'),
            __('Communities', 'happy-place'),
            'edit_posts',
            'edit.php?post_type=community'
        );
        
        // Open Houses
        add_submenu_page(
            'happy-place',
            __('Open Houses', 'happy-place'),
            __('Open Houses', 'happy-place'),
            'edit_posts',
            'edit.php?post_type=open_house'
        );
        
        // Leads management
        add_submenu_page(
            'happy-place',
            __('Leads', 'happy-place'),
            __('Leads', 'happy-place'),
            'edit_posts',
            'edit.php?post_type=lead'
        );

        // === CONFIGURATION & TOOLS === //

        // Configuration Sync - NEW!
        add_submenu_page(
            'happy-place',
            __('Configuration Sync', 'happy-place'),
            __('Configuration Sync', 'happy-place'),
            'manage_options',
            'happy-place-sync',
            [$this, 'sync_page']
        );
        
        // Airtable Integration
        add_submenu_page(
            'happy-place',
            __('Airtable Integration', 'happy-place'),
            __('Airtable Integration', 'happy-place'),
            'manage_options',
            'happy-place-airtable',
            [$this, 'airtable_page']
        );
        
        // Import/Export Tools
        add_submenu_page(
            'happy-place',
            __('Import/Export', 'happy-place'),
            __('Import/Export', 'happy-place'),
            'manage_options',
            'happy-place-import-export',
            [$this, 'import_export_page']
        );

        // === SETTINGS & STATUS === //
        
        // General Settings
        add_submenu_page(
            'happy-place',
            __('Settings', 'happy-place'),
            __('Settings', 'happy-place'),
            'manage_options',
            'happy-place-settings',
            [$this, 'settings_page']
        );
        
        // System Status & Diagnostics
        add_submenu_page(
            'happy-place',
            __('System Status', 'happy-place'),
            __('System Status', 'happy-place'),
            'manage_options',
            'happy-place-system',
            [$this, 'system_page']
        );
        
        if (function_exists('hp_log')) {
            hp_log('Happy Place admin menu organized and added', 'info', 'ADMIN_MENU');
        }
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        ?>
        <div class="wrap happy-place-admin">
            <h1><?php _e('Happy Place', 'happy-place'); ?></h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo wp_count_posts('listing')->publish; ?></div>
                    <div class="stat-label"><?php _e('Active Listings', 'happy-place'); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo wp_count_posts('agent')->publish; ?></div>
                    <div class="stat-label"><?php _e('Agents', 'happy-place'); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo wp_count_posts('community')->publish; ?></div>
                    <div class="stat-label"><?php _e('Communities', 'happy-place'); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo wp_count_posts('open_house')->publish; ?></div>
                    <div class="stat-label"><?php _e('Open Houses', 'happy-place'); ?></div>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('Quick Actions', 'happy-place'); ?></h2>
                <div class="inside">
                    <div class="action-buttons">
                        <a href="<?php echo admin_url('post-new.php?post_type=listing'); ?>" class="button button-primary">
                            <?php _e('Add New Listing', 'happy-place'); ?>
                        </a>
                        <a href="<?php echo admin_url('post-new.php?post_type=agent'); ?>" class="button">
                            <?php _e('Add New Agent', 'happy-place'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=happy-place-settings'); ?>" class="button">
                            <?php _e('Settings', 'happy-place'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('System Information', 'happy-place'); ?></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Plugin Version', 'happy-place'); ?></th>
                            <td><?php echo HP_VERSION; ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('WordPress Version', 'happy-place'); ?></th>
                            <td><?php echo get_bloginfo('version'); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('PHP Version', 'happy-place'); ?></th>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('ACF Pro', 'happy-place'); ?></th>
                            <td>
                                <?php if (class_exists('ACF') && function_exists('acf_pro')) : ?>
                                    <span class="status-indicator status-indicator--active">
                                        <?php _e('Active', 'happy-place'); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="status-indicator status-indicator--inactive">
                                        <?php _e('Inactive', 'happy-place'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap happy-place-admin">
            <h1><?php _e('Happy Place Settings', 'happy-place'); ?></h1>
            
            <form method="post" action="" class="admin-form">
                <?php wp_nonce_field('hp_save_settings', 'hp_settings_nonce'); ?>
                
                <div class="setting-group">
                    <h3><?php _e('General Settings', 'happy-place'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="company_name"><?php _e('Company Name', 'happy-place'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="company_name" name="company_name" 
                                       value="<?php echo esc_attr(get_option('hp_company_name', '')); ?>" 
                                       class="regular-text" />
                                <p class="description"><?php _e('Enter your real estate company name', 'happy-place'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_phone"><?php _e('Company Phone', 'happy-place'); ?></label>
                            </th>
                            <td>
                                <input type="tel" id="company_phone" name="company_phone" 
                                       value="<?php echo esc_attr(get_option('hp_company_phone', '')); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_email"><?php _e('Company Email', 'happy-place'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="company_email" name="company_email" 
                                       value="<?php echo esc_attr(get_option('hp_company_email', '')); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="setting-group">
                    <h3><?php _e('Display Settings', 'happy-place'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="listings_per_page"><?php _e('Listings Per Page', 'happy-place'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="listings_per_page" name="listings_per_page" 
                                       value="<?php echo esc_attr(get_option('hp_listings_per_page', '12')); ?>" 
                                       min="1" max="100" />
                                <p class="description"><?php _e('Number of listings to show per page', 'happy-place'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="enable_map"><?php _e('Enable Map View', 'happy-place'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="enable_map" name="enable_map" value="1" 
                                       <?php checked(get_option('hp_enable_map', '1'), '1'); ?> />
                                <label for="enable_map"><?php _e('Show map on listing pages', 'happy-place'); ?></label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php submit_button(__('Save Settings', 'happy-place'), 'primary', 'save_settings'); ?>
            </form>
        </div>
        <?php
        
        // Handle form submission
        if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['hp_settings_nonce'], 'hp_save_settings')) {
            $this->save_settings();
        }
    }
    
    
    /**
     * Configuration Sync page
     */
    public function sync_page() {
        // Get sync manager instance
        $sync_manager = null;
        if (class_exists('HappyPlace\\Core\\Config_Sync_Manager')) {
            $sync_manager = \HappyPlace\Core\Config_Sync_Manager::get_instance();
        }
        
        if (!$sync_manager) {
            ?>
            <div class="wrap happy-place-admin">
                <h1><?php _e('Configuration Sync', 'happy-place'); ?></h1>
                <div class="notice notice-error">
                    <p><?php _e('Configuration Sync Manager not available. Please ensure the plugin is properly loaded.', 'happy-place'); ?></p>
                </div>
            </div>
            <?php
            return;
        }
        
        // Handle sync actions (support both sync_action and hp_sync_action parameters)
        $sync_action = $_GET['sync_action'] ?? $_GET['hp_sync_action'] ?? '';
        if ($sync_action && wp_verify_nonce($_GET['_wpnonce'], 'hp_sync_action')) {
            $action = sanitize_text_field($sync_action);
            $message = '';
            $message_type = 'success';
            
            switch ($action) {
                case 'check_changes':
                    $sync_manager->check_config_changes();
                    $message = __('Configuration changes checked successfully.', 'happy-place');
                    break;
                    
                case 'sync_all':
                    $results = $sync_manager->sync_all();
                    $sync_count = array_sum(array_column($results, 'synced'));
                    $message = sprintf(__('Successfully synced %d configurations.', 'happy-place'), $sync_count);
                    break;
                    
                case 'export_configs':
                    $results = $sync_manager->export_current_configs();
                    $export_count = array_sum(array_column($results, 'count'));
                    $message = sprintf(__('Successfully exported %d configurations to JSON.', 'happy-place'), $export_count);
                    break;
                    
                case 'sync_post_types':
                    $result = $sync_manager->sync_post_types();
                    if ($result['success']) {
                        $message = sprintf(__('Successfully synced %d post types.', 'happy-place'), $result['synced']);
                    } else {
                        $message = __('Failed to sync post types: ', 'happy-place') . $result['message'];
                        $message_type = 'error';
                    }
                    break;
                    
                case 'sync_taxonomies':
                    $result = $sync_manager->sync_taxonomies();
                    if ($result['success']) {
                        $message = sprintf(__('Successfully synced %d taxonomies.', 'happy-place'), $result['synced']);
                    } else {
                        $message = __('Failed to sync taxonomies: ', 'happy-place') . $result['message'];
                        $message_type = 'error';
                    }
                    break;
                    
                case 'sync_acf_fields':
                    $result = $sync_manager->sync_acf_field_groups();
                    if ($result['success']) {
                        $message = sprintf(__('Successfully synced %d ACF field groups.', 'happy-place'), $result['synced']);
                    } else {
                        $message = __('Failed to sync ACF field groups: ', 'happy-place') . $result['message'];
                        $message_type = 'error';
                    }
                    break;
            }
            
            if ($message) {
                add_action('admin_notices', function() use ($message, $message_type) {
                    echo '<div class="notice notice-' . esc_attr($message_type) . ' is-dismissible"><p>' . 
                         esc_html($message) . 
                         '</p></div>';
                });
            }
        }
        
        $sync_status = $sync_manager->get_sync_status();
        ?>
        <div class="wrap happy-place-admin">
            <h1><?php _e('Configuration Sync', 'happy-place'); ?></h1>
            
            <div class="sync-dashboard">
                <!-- Overall Status -->
                <div class="sync-card">
                    <h2 class="sync-card-title">
                        <span class="dashicons dashicons-update"></span>
                        <?php _e('Sync Status', 'happy-place'); ?>
                    </h2>
                    
                    <div class="sync-status-indicator">
                        <?php if ($sync_status['has_changes']): ?>
                            <span class="sync-badge sync-badge-warning">
                                <span class="dashicons dashicons-warning"></span>
                                <?php echo count($sync_status['changes']); ?> <?php _e('Updates Available', 'happy-place'); ?>
                            </span>
                        <?php else: ?>
                            <span class="sync-badge sync-badge-success">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php _e('All Up to Date', 'happy-place'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="sync-last-check">
                        <strong><?php _e('Last Check:', 'happy-place'); ?></strong> 
                        <?php echo $sync_status['last_check'] ? date('M j, Y g:i A', $sync_status['last_check']) : __('Never', 'happy-place'); ?>
                    </p>
                    
                    <div class="sync-actions-primary">
                        <?php if ($sync_status['has_changes']): ?>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=happy-place-sync&sync_action=sync_all'), 'hp_sync_action'); ?>" 
                               class="button button-primary button-large">
                                <span class="dashicons dashicons-update"></span>
                                <?php _e('Sync All Changes', 'happy-place'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=happy-place-sync&sync_action=check_changes'), 'hp_sync_action'); ?>" 
                           class="button button-secondary">
                            <span class="dashicons dashicons-search"></span>
                            <?php _e('Check for Changes', 'happy-place'); ?>
                        </a>
                    </div>
                </div>
                
                <!-- Configuration Changes -->
                <?php if ($sync_status['has_changes']): ?>
                <div class="sync-card">
                    <h2 class="sync-card-title">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php _e('Pending Changes', 'happy-place'); ?>
                    </h2>
                    
                    <div class="sync-changes-list">
                        <?php 
                        $change_labels = [
                            'post_types' => ['dashicons-admin-post', __('Post Types', 'happy-place'), __('Custom post type definitions', 'happy-place')],
                            'taxonomies' => ['dashicons-tag', __('Taxonomies', 'happy-place'), __('Taxonomy configurations', 'happy-place')],
                            'acf_fields' => ['dashicons-admin-tools', __('ACF Field Groups', 'happy-place'), __('Field group definitions', 'happy-place')]
                        ];
                        
                        foreach ($sync_status['changes'] as $type => $change): 
                            $info = $change_labels[$type] ?? ['dashicons-admin-generic', ucfirst($type), __('Configuration', 'happy-place')];
                        ?>
                        <div class="sync-change-item">
                            <div class="sync-change-info">
                                <span class="dashicons <?php echo $info[0]; ?>"></span>
                                <div class="sync-change-details">
                                    <h4><?php echo $info[1]; ?></h4>
                                    <p><?php echo $info[2]; ?></p>
                                    <small><?php printf(__('Modified: %s', 'happy-place'), date('M j, Y g:i A', $change['last_modified'])); ?></small>
                                </div>
                            </div>
                            <div class="sync-change-actions">
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=happy-place-sync&sync_action=sync_' . $type), 'hp_sync_action'); ?>" 
                                   class="button button-primary">
                                    <?php _e('Sync', 'happy-place'); ?>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Configuration Files -->
                <div class="sync-card">
                    <h2 class="sync-card-title">
                        <span class="dashicons dashicons-media-code"></span>
                        <?php _e('Configuration Files', 'happy-place'); ?>
                    </h2>
                    
                    <div class="sync-file-status">
                        <div class="file-status-item">
                            <strong><?php _e('Post Types:', 'happy-place'); ?></strong>
                            <code>includes/config/post-types.json</code>
                            <?php if (file_exists(HP_PLUGIN_DIR . 'includes/config/post-types.json')): ?>
                                <span class="sync-badge sync-badge-success"><?php _e('Found', 'happy-place'); ?></span>
                            <?php else: ?>
                                <span class="sync-badge sync-badge-error"><?php _e('Missing', 'happy-place'); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="file-status-item">
                            <strong><?php _e('Taxonomies:', 'happy-place'); ?></strong>
                            <code>includes/config/taxonomies.json</code>
                            <?php if (file_exists(HP_PLUGIN_DIR . 'includes/config/taxonomies.json')): ?>
                                <span class="sync-badge sync-badge-success"><?php _e('Found', 'happy-place'); ?></span>
                            <?php else: ?>
                                <span class="sync-badge sync-badge-error"><?php _e('Missing', 'happy-place'); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="file-status-item">
                            <strong><?php _e('ACF Field Groups:', 'happy-place'); ?></strong>
                            <code>includes/fields/acf-json/</code>
                            <?php 
                            $acf_json_count = count(glob(HP_PLUGIN_DIR . 'includes/fields/acf-json/*.json'));
                            ?>
                            <span class="sync-badge sync-badge-info"><?php printf(__('%d files', 'happy-place'), $acf_json_count); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Tools -->
                <div class="sync-card">
                    <h2 class="sync-card-title">
                        <span class="dashicons dashicons-admin-tools"></span>
                        <?php _e('Advanced Tools', 'happy-place'); ?>
                    </h2>
                    
                    <div class="sync-tools-grid">
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=happy-place-sync&sync_action=export_configs'), 'hp_sync_action'); ?>" 
                           class="button button-secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Export Configurations', 'happy-place'); ?>
                        </a>
                        
                        <a href="<?php echo HP_PLUGIN_URL; ?>complete-sync-manager.php" 
                           class="button button-secondary" target="_blank">
                            <span class="dashicons dashicons-external"></span>
                            <?php _e('Advanced Sync Manager', 'happy-place'); ?>
                        </a>
                        
                        <a href="<?php echo HP_PLUGIN_URL; ?>acf-pro-sync.php" 
                           class="button button-secondary" target="_blank">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php _e('ACF Pro Sync', 'happy-place'); ?>
                        </a>
                    </div>
                    
                    <div class="sync-help">
                        <h4><?php _e('How Configuration Sync Works', 'happy-place'); ?></h4>
                        <ul>
                            <li><?php _e('File changes are automatically detected every 5 minutes', 'happy-place'); ?></li>
                            <li><?php _e('Dashboard notifications appear when updates are available', 'happy-place'); ?></li>
                            <li><?php _e('One-click sync applies changes to WordPress database', 'happy-place'); ?></li>
                            <li><?php _e('JSON files ensure version control compatibility', 'happy-place'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Import/Export page
     */
    public function import_export_page() {
        ?>
        <div class="wrap happy-place-admin">
            <h1><?php _e('Import/Export Tools', 'happy-place'); ?></h1>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('CSV Import Tools', 'happy-place'); ?></h2>
                <div class="inside">
                    <p><?php _e('Import listings, agents, and other data from CSV files.', 'happy-place'); ?></p>
                    
                    <div class="import-tools-grid">
                        <div class="import-tool">
                            <h3><?php _e('Listings Import', 'happy-place'); ?></h3>
                            <p><?php _e('Import property listings from CSV file', 'happy-place'); ?></p>
                            <a href="#" class="button button-primary"><?php _e('Import Listings', 'happy-place'); ?></a>
                        </div>
                        
                        <div class="import-tool">
                            <h3><?php _e('Agents Import', 'happy-place'); ?></h3>
                            <p><?php _e('Import agent profiles from CSV file', 'happy-place'); ?></p>
                            <a href="#" class="button button-primary"><?php _e('Import Agents', 'happy-place'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('Export Tools', 'happy-place'); ?></h2>
                <div class="inside">
                    <p><?php _e('Export your data for backup or migration purposes.', 'happy-place'); ?></p>
                    
                    <div class="export-actions">
                        <a href="#" class="button button-secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Export All Listings', 'happy-place'); ?>
                        </a>
                        <a href="#" class="button button-secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Export All Agents', 'happy-place'); ?>
                        </a>
                        <a href="#" class="button button-secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Export Settings', 'happy-place'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Airtable page
     */
    public function airtable_page() {
        // Check if Airtable manager exists
        if (class_exists('HappyPlace\\Integrations\\Airtable_Sync_Manager')) {
            $airtable_manager = \HappyPlace\Integrations\Airtable_Sync_Manager::get_instance();
            
            if (method_exists($airtable_manager, 'render_admin_page')) {
                $airtable_manager->render_admin_page();
                return;
            }
        }
        
        // Fallback interface
        ?>
        <div class="wrap happy-place-admin">
            <h1><?php _e('Airtable Integration', 'happy-place'); ?></h1>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('Airtable Sync Configuration', 'happy-place'); ?></h2>
                <div class="inside">
                    <p><?php _e('Configure your Airtable integration for two-way data synchronization.', 'happy-place'); ?></p>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field('hp_airtable_settings', 'hp_airtable_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="airtable_api_key"><?php _e('API Key', 'happy-place'); ?></label>
                                </th>
                                <td>
                                    <input type="password" id="airtable_api_key" name="airtable_api_key" 
                                           value="<?php echo esc_attr(get_option('hpt_airtable_api_key', '')); ?>" 
                                           class="regular-text" />
                                    <p class="description"><?php _e('Your Airtable Personal Access Token', 'happy-place'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="airtable_base_id"><?php _e('Base ID', 'happy-place'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="airtable_base_id" name="airtable_base_id" 
                                           value="<?php echo esc_attr(get_option('hpt_airtable_base_id', '')); ?>" 
                                           class="regular-text" />
                                    <p class="description"><?php _e('Your Airtable Base ID (starts with "app")', 'happy-place'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button(__('Save Airtable Settings', 'happy-place'), 'primary', 'save_airtable'); ?>
                    </form>
                    
                    <?php
                    if (isset($_POST['save_airtable']) && wp_verify_nonce($_POST['hp_airtable_nonce'], 'hp_airtable_settings')) {
                        $this->save_airtable_settings();
                    }
                    ?>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('Sync Status', 'happy-place'); ?></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th><?php _e('API Key Status', 'happy-place'); ?></th>
                            <td>
                                <?php if (get_option('hpt_airtable_api_key')): ?>
                                    <span class="sync-badge sync-badge-success"><?php _e('Configured', 'happy-place'); ?></span>
                                <?php else: ?>
                                    <span class="sync-badge sync-badge-error"><?php _e('Not Configured', 'happy-place'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Base ID Status', 'happy-place'); ?></th>
                            <td>
                                <?php if (get_option('hpt_airtable_base_id')): ?>
                                    <span class="sync-badge sync-badge-success"><?php _e('Configured', 'happy-place'); ?></span>
                                <?php else: ?>
                                    <span class="sync-badge sync-badge-error"><?php _e('Not Configured', 'happy-place'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * System page
     */
    public function system_page() {
        ?>
        <div class="wrap happy-place-admin">
            <h1><?php _e('System Status', 'happy-place'); ?></h1>
            
            <!-- Plugin Information -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('Plugin Information', 'happy-place'); ?></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <tbody>
                            <tr>
                                <td><strong><?php _e('Plugin Version', 'happy-place'); ?></strong></td>
                                <td><?php echo HP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Plugin Directory', 'happy-place'); ?></strong></td>
                                <td><code><?php echo HP_PLUGIN_DIR; ?></code></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Debug Mode', 'happy-place'); ?></strong></td>
                                <td>
                                    <span class="sync-badge <?php echo defined('HP_DEBUG') && HP_DEBUG ? 'sync-badge-success' : 'sync-badge-warning'; ?>">
                                        <?php echo defined('HP_DEBUG') && HP_DEBUG ? __('Enabled', 'happy-place') : __('Disabled', 'happy-place'); ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- WordPress Environment -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('WordPress Environment', 'happy-place'); ?></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <tbody>
                            <tr>
                                <td><strong><?php _e('WordPress Version', 'happy-place'); ?></strong></td>
                                <td><?php echo get_bloginfo('version'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('PHP Version', 'happy-place'); ?></strong></td>
                                <td><?php echo phpversion(); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Memory Limit', 'happy-place'); ?></strong></td>
                                <td><?php echo ini_get('memory_limit'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Max Execution Time', 'happy-place'); ?></strong></td>
                                <td><?php echo ini_get('max_execution_time') . 's'; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Post Types Status -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('Post Types Status', 'happy-place'); ?></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php _e('Post Type', 'happy-place'); ?></th>
                                <th><?php _e('Published', 'happy-place'); ?></th>
                                <th><?php _e('Draft', 'happy-place'); ?></th>
                                <th><?php _e('Total', 'happy-place'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $post_types = ['listing', 'agent', 'community', 'open_house', 'lead'];
                            foreach ($post_types as $post_type):
                                $counts = wp_count_posts($post_type);
                                if ($counts):
                            ?>
                            <tr>
                                <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $post_type))); ?></td>
                                <td><?php echo (int) $counts->publish; ?></td>
                                <td><?php echo (int) $counts->draft; ?></td>
                                <td><?php echo (int) ($counts->publish + $counts->draft); ?></td>
                            </tr>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- ACF Status -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('ACF Status', 'happy-place'); ?></h2>
                <div class="inside">
                    <?php if (class_exists('ACF') && function_exists('acf_pro')): ?>
                        <p>
                            <span class="sync-badge sync-badge-success">
                                <?php _e('ACF Pro is Active', 'happy-place'); ?>
                            </span>
                        </p>
                        
                        <?php
                        if (class_exists('HappyPlace\\Core\\ACF_Manager')):
                            $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
                            if (method_exists($acf_manager, 'get_sync_status')):
                                $sync_status = $acf_manager->get_sync_status();
                        ?>
                        <h4><?php _e('Field Group Status', 'happy-place'); ?></h4>
                        <ul>
                            <li><?php printf(__('JSON Field Groups: %d', 'happy-place'), $sync_status['json_groups']); ?></li>
                            <li><?php printf(__('Database Field Groups: %d', 'happy-place'), $sync_status['db_groups']); ?></li>
                            <li><?php printf(__('In Sync: %d', 'happy-place'), $sync_status['in_sync']); ?></li>
                            <li><?php printf(__('Out of Sync: %d', 'happy-place'), $sync_status['out_of_sync']); ?></li>
                        </ul>
                        <?php 
                            endif;
                        endif; 
                        ?>
                    <?php else: ?>
                        <p>
                            <span class="sync-badge sync-badge-error">
                                <?php _e('ACF Pro is Not Active', 'happy-place'); ?>
                            </span>
                        </p>
                        <p><?php _e('Advanced Custom Fields Pro is required for this plugin to function properly.', 'happy-place'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        // Sanitize and save settings
        $settings = [
            'hp_company_name' => sanitize_text_field($_POST['company_name'] ?? ''),
            'hp_company_phone' => sanitize_text_field($_POST['company_phone'] ?? ''),
            'hp_company_email' => sanitize_email($_POST['company_email'] ?? ''),
            'hp_listings_per_page' => intval($_POST['listings_per_page'] ?? 12),
            'hp_enable_map' => isset($_POST['enable_map']) ? '1' : '0',
        ];
        
        foreach ($settings as $option => $value) {
            update_option($option, $value);
        }
        
        // Show success message
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 __('Settings saved successfully!', 'happy-place') . 
                 '</p></div>';
        });
        
        hp_log('Settings saved', 'info', 'ADMIN_MENU');
    }
    
    /**
     * Airtable sync page
     */
    public function airtable_sync_page() {
        // Get the Airtable Sync Manager instance
        $airtable_manager = \HappyPlace\Integrations\Airtable_Sync_Manager::get_instance();
        
        // Check if the render_admin_page method exists and call it
        if (method_exists($airtable_manager, 'render_admin_page')) {
            $airtable_manager->render_admin_page();
        } else {
            // Fallback basic interface
            ?>
            <div class="wrap">
                <h1><?php _e('Airtable Sync', 'happy-place'); ?></h1>
                <div class="notice notice-info">
                    <p><?php _e('Airtable synchronization is available. Configure your settings below.', 'happy-place'); ?></p>
                </div>
                
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Sync Status', 'happy-place'); ?></h2>
                    <div class="inside">
                        <p><?php _e('Airtable sync functionality is being initialized.', 'happy-place'); ?></p>
                        <p>
                            <strong><?php _e('API Key:', 'happy-place'); ?></strong> 
                            <?php echo get_option('hpt_airtable_api_key') ? __('Configured', 'happy-place') : __('Not configured', 'happy-place'); ?>
                        </p>
                        <p>
                            <strong><?php _e('Base ID:', 'happy-place'); ?></strong> 
                            <?php echo get_option('hpt_airtable_base_id') ? __('Configured', 'happy-place') : __('Not configured', 'happy-place'); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    
    /**
     * Save Airtable settings
     */
    private function save_airtable_settings() {
        // Sanitize and save Airtable settings
        $api_key = sanitize_text_field($_POST['airtable_api_key'] ?? '');
        $base_id = sanitize_text_field($_POST['airtable_base_id'] ?? '');
        
        update_option('hpt_airtable_api_key', $api_key);
        update_option('hpt_airtable_base_id', $base_id);
        
        // Show success message
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 __('Airtable settings saved successfully!', 'happy-place') . 
                 '</p></div>';
        });
        
        hp_log('Airtable settings saved', 'info', 'ADMIN_MENU');
    }
    
    /**
     * Sample Data page
     */
    public function sample_data_page() {
        if (!class_exists('\HappyPlace\Utilities\Sample_Data_Generator')) {
            ?>
            <div class="wrap">
                <h1><?php _e('Sample Data Generator', 'happy-place'); ?></h1>
                <div class="notice notice-error">
                    <p><?php _e('Sample Data Generator class not found.', 'happy-place'); ?></p>
                </div>
            </div>
            <?php
            return;
        }
        
        $generator = \HappyPlace\Utilities\Sample_Data_Generator::get_instance();
        $action = $_GET['sample_action'] ?? '';
        $message = '';
        $message_type = 'info';
        
        // Handle actions
        if ($action === 'generate' && wp_verify_nonce($_GET['_wpnonce'], 'hp_sample_data')) {
            $force = isset($_GET['force']) && $_GET['force'] === '1';
            $results = $generator->generate_all_sample_data($force);
            
            if (!empty($results['errors'])) {
                $message_type = 'error';
                $message = __('Sample data generation completed with errors: ', 'happy-place') . implode(', ', $results['errors']);
            } elseif (isset($results['message'])) {
                $message = $results['message'];
            } else {
                $message_type = 'success';
                $message = sprintf(
                    __('Sample data created successfully! Generated %d agents, %d listings, %d communities, %d leads.', 'happy-place'),
                    $results['agents'], $results['listings'], $results['communities'], $results['leads']
                );
            }
        } elseif ($action === 'cleanup' && wp_verify_nonce($_GET['_wpnonce'], 'hp_sample_data')) {
            $deleted_count = $generator->cleanup_sample_data();
            $message_type = 'success';
            $message = sprintf(__('Deleted %d sample data posts.', 'happy-place'), $deleted_count);
        }
        
        $stats = $generator->get_sample_data_stats();
        
        ?>
        <div class="wrap happy-place-admin">
            <h1><?php _e('Sample Data Generator', 'happy-place'); ?></h1>
            
            <?php if ($message): ?>
                <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
                    <p><?php echo esc_html($message); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('Current Sample Data', 'happy-place'); ?></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php _e('Post Type', 'happy-place'); ?></th>
                                <th><?php _e('Sample Data Count', 'happy-place'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats as $post_type => $count): ?>
                                <tr>
                                    <td><?php echo esc_html(ucfirst($post_type)); ?></td>
                                    <td><?php echo (int) $count; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('Sample Data Actions', 'happy-place'); ?></h2>
                <div class="inside">
                    <div class="action-buttons">
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=happy-place-sample-data&sample_action=generate'), 'hp_sample_data'); ?>" 
                           class="button button-primary">
                            <?php _e('Generate Sample Data', 'happy-place'); ?>
                        </a>
                        
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=happy-place-sample-data&sample_action=generate&force=1'), 'hp_sample_data'); ?>" 
                           class="button button-secondary">
                            <?php _e('Force Regenerate', 'happy-place'); ?>
                        </a>
                        
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=happy-place-sample-data&sample_action=cleanup'), 'hp_sample_data'); ?>" 
                           class="button button-link-delete"
                           onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete all sample data?', 'happy-place'); ?>')">
                            <?php _e('Delete All Sample Data', 'happy-place'); ?>
                        </a>
                    </div>
                    
                    <h4><?php _e('What gets created:', 'happy-place'); ?></h4>
                    <ul>
                        <li><?php _e('3 Sample Agents with complete profiles', 'happy-place'); ?></li>
                        <li><?php _e('3 Sample Listings with different property types', 'happy-place'); ?></li>
                        <li><?php _e('2 Sample Communities with amenities', 'happy-place'); ?></li>
                        <li><?php _e('2 Sample Leads with different statuses', 'happy-place'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * System Status page
     */
    public function system_status_page() {
        ?>
        <div class="wrap happy-place-admin">
            <h1><?php _e('System Status', 'happy-place'); ?></h1>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('Plugin Information', 'happy-place'); ?></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <tbody>
                            <tr>
                                <td><strong><?php _e('Plugin Version', 'happy-place'); ?></strong></td>
                                <td><?php echo HP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Plugin Directory', 'happy-place'); ?></strong></td>
                                <td><?php echo HP_PLUGIN_DIR; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Plugin URL', 'happy-place'); ?></strong></td>
                                <td><?php echo HP_PLUGIN_URL; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Debug Mode', 'happy-place'); ?></strong></td>
                                <td>
                                    <span class="status-indicator <?php echo HP_DEBUG ? 'status-indicator--active' : 'status-indicator--inactive'; ?>">
                                        <?php echo HP_DEBUG ? __('Enabled', 'happy-place') : __('Disabled', 'happy-place'); ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('WordPress Environment', 'happy-place'); ?></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <tbody>
                            <tr>
                                <td><strong><?php _e('WordPress Version', 'happy-place'); ?></strong></td>
                                <td><?php echo get_bloginfo('version'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('PHP Version', 'happy-place'); ?></strong></td>
                                <td><?php echo phpversion(); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Memory Limit', 'happy-place'); ?></strong></td>
                                <td><?php echo ini_get('memory_limit'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Max Execution Time', 'happy-place'); ?></strong></td>
                                <td><?php echo ini_get('max_execution_time') . 's'; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('Post Types Status', 'happy-place'); ?></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php _e('Post Type', 'happy-place'); ?></th>
                                <th><?php _e('Published', 'happy-place'); ?></th>
                                <th><?php _e('Draft', 'happy-place'); ?></th>
                                <th><?php _e('Total', 'happy-place'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $post_types = ['listing', 'agent', 'community', 'open_house', 'lead'];
                            foreach ($post_types as $post_type):
                                $counts = wp_count_posts($post_type);
                                if ($counts):
                            ?>
                            <tr>
                                <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $post_type))); ?></td>
                                <td><?php echo (int) $counts->publish; ?></td>
                                <td><?php echo (int) $counts->draft; ?></td>
                                <td><?php echo (int) ($counts->publish + $counts->draft); ?></td>
                            </tr>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle"><?php _e('ACF Status', 'happy-place'); ?></h2>
                <div class="inside">
                    <?php if (class_exists('ACF') && function_exists('acf_pro')): ?>
                        <p>
                            <span class="status-indicator status-indicator--active">
                                <?php _e('ACF Pro is Active', 'happy-place'); ?>
                            </span>
                        </p>
                        
                        <?php
                        if (class_exists('\HappyPlace\Core\ACF_Manager')):
                            $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
                            if (method_exists($acf_manager, 'get_sync_status')):
                                $sync_status = $acf_manager->get_sync_status();
                        ?>
                        <h4><?php _e('Field Group Status', 'happy-place'); ?></h4>
                        <ul>
                            <li><?php printf(__('JSON Field Groups: %d', 'happy-place'), $sync_status['json_groups']); ?></li>
                            <li><?php printf(__('Database Field Groups: %d', 'happy-place'), $sync_status['db_groups']); ?></li>
                            <li><?php printf(__('In Sync: %d', 'happy-place'), $sync_status['in_sync']); ?></li>
                            <li><?php printf(__('Out of Sync: %d', 'happy-place'), $sync_status['out_of_sync']); ?></li>
                            <li><?php printf(__('Orphaned: %d', 'happy-place'), $sync_status['orphaned_groups']); ?></li>
                        </ul>
                        <?php 
                            endif;
                        endif; 
                        ?>
                    <?php else: ?>
                        <p>
                            <span class="status-indicator status-indicator--inactive">
                                <?php _e('ACF Pro is Not Active', 'happy-place'); ?>
                            </span>
                        </p>
                        <p><?php _e('Advanced Custom Fields Pro is required for this plugin to function properly.', 'happy-place'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <style>
            .status-indicator {
                padding: 4px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
            }
            .status-indicator--active {
                background-color: #d1e7dd;
                color: #0a3622;
            }
            .status-indicator--inactive {
                background-color: #f8d7da;
                color: #58151c;
            }
            .action-buttons .button {
                margin-right: 10px;
                margin-bottom: 5px;
            }
        </style>
        <?php
    }
    
    /**
     * Handle admin AJAX requests
     */
    public function handle_admin_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'hp_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $action = $_POST['admin_action'] ?? '';
        
        switch ($action) {
            case 'test_connection':
                wp_send_json_success(['message' => 'Connection test successful']);
                break;
                
            default:
                wp_send_json_error('Unknown action');
                break;
        }
    }
}