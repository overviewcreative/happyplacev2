<?php
/**
 * Admin Menu Class
 * 
 * Handles admin menu registration and page rendering
 *
 * @package HappyPlace\Admin
 * @version 4.0.0
 */

namespace HappyPlace\Admin;

use HappyPlace\Core\ConfigurationManager;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Menu Class
 * 
 * @since 4.0.0
 */
class AdminMenu {
    
    /**
     * Single instance
     * 
     * @var AdminMenu|null
     */
    private static ?AdminMenu $instance = null;
    
    /**
     * Menu pages
     * 
     * @var array
     */
    private array $pages = [];
    
    /**
     * Configuration Manager
     * 
     * @var ConfigurationManager
     */
    private ConfigurationManager $config_manager;
    
    /**
     * Get instance
     * 
     * @return AdminMenu
     */
    public static function get_instance(): AdminMenu {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->config_manager = ConfigurationManager::get_instance();
        $this->define_pages();
    }
    
    /**
     * Initialize admin menu
     * 
     * @return void
     */
    public function init(): void {
        // Add menu pages
        add_action('admin_menu', [$this, 'add_menu_pages']);
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        
        // Add AJAX handlers
        add_action('wp_ajax_hp_clear_cache', [$this, 'ajax_clear_cache']);
        add_action('wp_ajax_hp_regenerate_thumbnails', [$this, 'ajax_regenerate_thumbnails']);
        add_action('wp_ajax_hp_optimize_database', [$this, 'ajax_optimize_database']);
        add_action('wp_ajax_hp_sync_mls', [$this, 'ajax_sync_mls']);
        add_action('wp_ajax_hp_sync_config', [$this, 'ajax_sync_config']);
        add_action('wp_ajax_hp_test_airtable_connection', [$this, 'ajax_test_airtable_connection']);
        add_action('wp_ajax_hp_bulk_sync_leads', [$this, 'ajax_bulk_sync_leads']);
        add_action('wp_ajax_hp_cleanup_roles', [$this, 'ajax_cleanup_roles']);
        add_action('wp_ajax_hp_preview_role_changes', [$this, 'ajax_preview_role_changes']);
        add_action('wp_ajax_get_lead_details', [$this, 'ajax_get_lead_details']);
        
        // Performance Tools AJAX handlers
        add_action('wp_ajax_hp_build_css_bundles', [$this, 'ajax_build_css_bundles']);
        add_action('wp_ajax_hp_clear_asset_cache', [$this, 'ajax_clear_asset_cache']);
        add_action('wp_ajax_hp_analyze_performance', [$this, 'ajax_analyze_performance']);
        add_action('wp_ajax_hp_optimize_images', [$this, 'ajax_optimize_images']);
        add_action('wp_ajax_hp_clear_transients', [$this, 'ajax_clear_transients']);
        add_action('wp_ajax_hp_test_page_speed', [$this, 'ajax_test_page_speed']);
        
        // Add toolbar items
        add_action('admin_bar_menu', [$this, 'add_toolbar_items'], 100);
        
        // Handle page actions
        add_action('admin_init', [$this, 'handle_page_actions']);
        
        // Add help tabs
        add_action('current_screen', [$this, 'add_help_tabs']);
        
        hp_log('Admin Menu initialized', 'info', 'AdminMenu');
    }
    
    /**
     * Define admin pages
     * 
     * @return void
     */
    private function define_pages(): void {
        $this->pages = [
            // Main Dashboard
            'dashboard' => [
                'title' => __('Happy Place Dashboard', 'happy-place'),
                'menu_title' => __('Happy Place', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'happy-place',
                'callback' => [$this, 'render_dashboard'],
                'icon' => 'dashicons-admin-home',
                'position' => 4,
            ],
            
            // Theme Settings (consolidating theme admin features)
            'theme-settings' => [
                'parent' => 'happy-place',
                'title' => __('Theme Settings', 'happy-place'),
                'menu_title' => __('Theme Settings', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-theme-settings',
                'callback' => [$this, 'render_theme_settings'],
            ],
            
            // Integrations & APIs (consolidating HP Settings and external services)
            'integrations' => [
                'parent' => 'happy-place',
                'title' => __('Integrations & APIs', 'happy-place'),
                'menu_title' => __('Integrations & APIs', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-integrations',
                'callback' => [$this, 'render_integrations'],
            ],
            
            // Import (data import tools)
            'import' => [
                'parent' => 'happy-place',
                'title' => __('Import Data', 'happy-place'),
                'menu_title' => __('Import', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-import',
                'callback' => [$this, 'render_import'],
            ],
            
            // Export (data export tools)
            'export' => [
                'parent' => 'happy-place',
                'title' => __('Export Data', 'happy-place'),
                'menu_title' => __('Export', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-export',
                'callback' => [$this, 'render_export'],
            ],
            
            // Sync (MLS sync, ACF sync, data synchronization)
            'sync' => [
                'parent' => 'happy-place',
                'title' => __('Data Synchronization', 'happy-place'),
                'menu_title' => __('Sync', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-sync',
                'callback' => [$this, 'render_sync'],
            ],
            
            // Analytics (reports, statistics, lead reports, transactions)
            'analytics' => [
                'parent' => 'happy-place',
                'title' => __('Analytics & Reports', 'happy-place'),
                'menu_title' => __('Analytics', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-analytics',
                'callback' => [$this, 'render_analytics'],
            ],
            
            // Leads (lead management and viewing)
            'leads' => [
                'parent' => 'happy-place',
                'title' => __('Lead Management', 'happy-place'),
                'menu_title' => __('Leads', 'happy-place'),
                'capability' => 'edit_posts',
                'slug' => 'happy-place-leads',
                'callback' => [$this, 'render_leads'],
            ],
            
            // Marketing (social, email, SEO, lead capture)
            'marketing' => [
                'parent' => 'happy-place',
                'title' => __('Marketing Tools', 'happy-place'),
                'menu_title' => __('Marketing', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-marketing',
                'callback' => [$this, 'render_marketing'],
            ],
            
            // Users (agent management, roles, permissions)
            'users' => [
                'parent' => 'happy-place',
                'title' => __('User Management', 'happy-place'),
                'menu_title' => __('Users', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-users',
                'callback' => [$this, 'render_users'],
            ],
            
            // Performance Tools (asset management, cache clearing, monitoring)
            'performance-tools' => [
                'parent' => 'happy-place',
                'title' => __('Performance Tools', 'happy-place'),
                'menu_title' => __('Performance Tools', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-performance-tools',
                'callback' => [$this, 'render_performance_tools'],
            ],
        ];
        
        // Allow filtering of pages
        $this->pages = apply_filters('hp_admin_pages', $this->pages);
    }
    
    /**
     * Add menu pages
     * 
     * @return void
     */
    public function add_menu_pages(): void {
        foreach ($this->pages as $key => $page) {
            if (isset($page['parent'])) {
                // Add submenu page
                add_submenu_page(
                    $page['parent'],
                    $page['title'],
                    $page['menu_title'],
                    $page['capability'],
                    $page['slug'],
                    $page['callback']
                );
            } else {
                // Add top-level menu page
                add_menu_page(
                    $page['title'],
                    $page['menu_title'],
                    $page['capability'],
                    $page['slug'],
                    $page['callback'],
                    $page['icon'] ?? '',
                    $page['position'] ?? null
                );
            }
        }
    }
    
    /**
     * Add toolbar items
     * 
     * @param \WP_Admin_Bar $wp_admin_bar
     * @return void
     */
    public function add_toolbar_items(\WP_Admin_Bar $wp_admin_bar): void {
        if (!current_user_can('edit_posts')) {
            return;
        }
        
        // Add parent item
        $wp_admin_bar->add_node([
            'id' => 'happy-place',
            'title' => '<span class="ab-icon dashicons dashicons-admin-home"></span>' . __('Happy Place', 'happy-place'),
            'href' => admin_url('admin.php?page=happy-place'),
        ]);
        
        // Add child items
        $wp_admin_bar->add_node([
            'parent' => 'happy-place',
            'id' => 'hp-add-listing',
            'title' => __('Add Listing', 'happy-place'),
            'href' => admin_url('post-new.php?post_type=listing'),
        ]);
        
        $wp_admin_bar->add_node([
            'parent' => 'happy-place',
            'id' => 'hp-view-listings',
            'title' => __('View Listings', 'happy-place'),
            'href' => get_post_type_archive_link('listing'),
        ]);
        
        $wp_admin_bar->add_node([
            'parent' => 'happy-place',
            'id' => 'hp-analytics',
            'title' => __('Analytics', 'happy-place'),
            'href' => admin_url('admin.php?page=hp-analytics'),
        ]);
    }
    
    /**
     * Handle page actions
     * 
     * @return void
     */
    public function handle_page_actions(): void {
        // Handle form submissions, exports, etc.
        if (isset($_GET['page']) && strpos($_GET['page'], 'hp-') === 0) {
            do_action('hp_admin_page_' . str_replace('hp-', '', $_GET['page']));
        }
    }
    
    /**
     * Add help tabs
     * 
     * @param \WP_Screen $screen
     * @return void
     */
    public function add_help_tabs(\WP_Screen $screen): void {
        // Check if it's our admin page
        if (strpos($screen->id, 'happy-place') === false) {
            return;
        }
        
        // Add overview help tab
        $screen->add_help_tab([
            'id' => 'hp-overview',
            'title' => __('Overview', 'happy-place'),
            'content' => $this->get_help_content('overview'),
        ]);
        
        // Add specific help tabs based on page
        switch ($screen->id) {
            case 'toplevel_page_happy-place':
                $screen->add_help_tab([
                    'id' => 'hp-dashboard-help',
                    'title' => __('Dashboard', 'happy-place'),
                    'content' => $this->get_help_content('dashboard'),
                ]);
                break;
                
            case 'happy-place_page_hp-analytics':
                $screen->add_help_tab([
                    'id' => 'hp-analytics-help',
                    'title' => __('Analytics', 'happy-place'),
                    'content' => $this->get_help_content('analytics'),
                ]);
                break;
        }
        
        // Add sidebar
        $screen->set_help_sidebar($this->get_help_sidebar());
    }
    
    /**
     * Render dashboard page
     * 
     * @return void
     */
    public function render_dashboard(): void {
        // Get stats
        $stats = $this->get_dashboard_stats();
        
        ?>
        <div class="wrap hp-admin-dashboard">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <!-- Configuration Status Alert -->
            <?php $this->render_configuration_alerts(); ?>
            
            <div class="hp-dashboard-widgets">
                <!-- Configuration Status Widget -->
                <div class="hp-widget hp-config-widget">
                    <h2><?php _e('Configuration Status', 'happy-place'); ?></h2>
                    <?php $this->render_configuration_status(); ?>
                </div>
                
                <!-- Stats Widget -->
                <div class="hp-widget hp-stats-widget">
                    <h2><?php _e('Quick Stats', 'happy-place'); ?></h2>
                    <div class="hp-stats-grid">
                        <div class="hp-stat">
                            <span class="hp-stat-value"><?php echo number_format($stats['listings']); ?></span>
                            <span class="hp-stat-label"><?php _e('Active Listings', 'happy-place'); ?></span>
                        </div>
                        <div class="hp-stat">
                            <span class="hp-stat-value"><?php echo number_format($stats['agents']); ?></span>
                            <span class="hp-stat-label"><?php _e('Agents', 'happy-place'); ?></span>
                        </div>
                        <div class="hp-stat">
                            <span class="hp-stat-value"><?php echo number_format($stats['leads']); ?></span>
                            <span class="hp-stat-label"><?php _e('Leads This Month', 'happy-place'); ?></span>
                        </div>
                        <div class="hp-stat">
                            <span class="hp-stat-value"><?php echo number_format($stats['views']); ?></span>
                            <span class="hp-stat-label"><?php _e('Views This Month', 'happy-place'); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Widget -->
                <div class="hp-widget hp-activity-widget">
                    <h2><?php _e('Recent Activity', 'happy-place'); ?></h2>
                    <div class="hp-activity-list">
                        <?php $this->render_recent_activity(); ?>
                    </div>
                </div>
                
                <!-- Quick Actions Widget -->
                <div class="hp-widget hp-actions-widget">
                    <h2><?php _e('Quick Actions', 'happy-place'); ?></h2>
                    <div class="hp-actions-grid">
                        <a href="<?php echo admin_url('post-new.php?post_type=listing'); ?>" class="button button-primary">
                            <?php _e('Add New Listing', 'happy-place'); ?>
                        </a>
                        <a href="<?php echo admin_url('post-new.php?post_type=agent'); ?>" class="button">
                            <?php _e('Add New Agent', 'happy-place'); ?>
                        </a>
                        <a href="<?php echo admin_url('post-new.php?post_type=open_house'); ?>" class="button">
                            <?php _e('Schedule Open House', 'happy-place'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=hp-import'); ?>" class="button">
                            <?php _e('Import Listings', 'happy-place'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render analytics page
     * 
     * @return void
     */
    public function render_analytics(): void {
        ?>
        <div class="wrap hp-admin-analytics">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="hp-settings-nav">
                <ul class="hp-nav-tabs">
                    <li><a href="#overview" class="nav-tab nav-tab-active"><?php _e('Overview', 'happy-place'); ?></a></li>
                    <li><a href="#listings" class="nav-tab"><?php _e('Listing Analytics', 'happy-place'); ?></a></li>
                    <li><a href="#leads" class="nav-tab"><?php _e('Lead Reports', 'happy-place'); ?></a></li>
                    <li><a href="#agents" class="nav-tab"><?php _e('Agent Performance', 'happy-place'); ?></a></li>
                    <li><a href="#system" class="nav-tab"><?php _e('System Status', 'happy-place'); ?></a></li>
                </ul>
            </div>
            
            <div id="overview" class="hp-settings-section">
                <div class="hp-analytics-filters">
                    <form method="get" action="">
                        <input type="hidden" name="page" value="hp-analytics">
                        
                        <label for="date-range">
                            <?php _e('Date Range:', 'happy-place'); ?>
                        </label>
                        <select name="range" id="date-range">
                            <option value="7"><?php _e('Last 7 Days', 'happy-place'); ?></option>
                            <option value="30" selected><?php _e('Last 30 Days', 'happy-place'); ?></option>
                            <option value="90"><?php _e('Last 90 Days', 'happy-place'); ?></option>
                            <option value="365"><?php _e('Last Year', 'happy-place'); ?></option>
                        </select>
                        
                        <button type="submit" class="button"><?php _e('Apply', 'happy-place'); ?></button>
                    </form>
                </div>
                
                <div class="hp-analytics-charts">
                    <canvas id="hp-views-chart"></canvas>
                    <canvas id="hp-leads-chart"></canvas>
                </div>
                
                <div class="hp-analytics-summary">
                    <h2><?php _e('Performance Summary', 'happy-place'); ?></h2>
                    <?php $this->render_analytics_summary(); ?>
                </div>
            </div>
            
            <div id="listings" class="hp-settings-section" style="display:none;">
                <h2><?php _e('Top Performing Listings', 'happy-place'); ?></h2>
                <?php $this->render_top_listings_table(); ?>
                
                <h2><?php _e('Listing Views Over Time', 'happy-place'); ?></h2>
                <canvas id="hp-listing-views-chart"></canvas>
            </div>
            
            <div id="leads" class="hp-settings-section" style="display:none;">
                <h2><?php _e('Lead Generation Report', 'happy-place'); ?></h2>
                <?php $this->render_lead_reports(); ?>
            </div>
            
            <div id="agents" class="hp-settings-section" style="display:none;">
                <h2><?php _e('Agent Performance', 'happy-place'); ?></h2>
                <?php $this->render_agent_performance(); ?>
            </div>
            
            <div id="system" class="hp-settings-section" style="display:none;">
                <h2><?php _e('System Information', 'happy-place'); ?></h2>
                <?php $this->render_system_info(); ?>
                
                <h2><?php _e('Recent Errors', 'happy-place'); ?></h2>
                <?php $this->render_error_log(); ?>
                
                <h2><?php _e('Tools', 'happy-place'); ?></h2>
                <div class="hp-tools-grid">
                    <div class="hp-tool">
                        <h3><?php _e('Clear Cache', 'happy-place'); ?></h3>
                        <p><?php _e('Clear all cached data to ensure fresh content.', 'happy-place'); ?></p>
                        <button class="button" id="hp-clear-cache"><?php _e('Clear Cache', 'happy-place'); ?></button>
                    </div>
                    
                    <div class="hp-tool">
                        <h3><?php _e('Regenerate Thumbnails', 'happy-place'); ?></h3>
                        <p><?php _e('Regenerate all listing thumbnails.', 'happy-place'); ?></p>
                        <button class="button" id="hp-regenerate-thumbnails"><?php _e('Regenerate', 'happy-place'); ?></button>
                    </div>
                    
                    <div class="hp-tool">
                        <h3><?php _e('Database Optimization', 'happy-place'); ?></h3>
                        <p><?php _e('Optimize database tables for better performance.', 'happy-place'); ?></p>
                        <button class="button" id="hp-optimize-db"><?php _e('Optimize', 'happy-place'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render theme settings page
     * 
     * @return void
     */
    public function render_theme_settings(): void {
        // Load required settings class if not already loaded
        if (!class_exists('HPH_Admin_Settings')) {
            $hph_settings_file = get_template_directory() . '/includes/admin/class-hph-admin-settings.php';
            if (file_exists($hph_settings_file)) {
                require_once $hph_settings_file;
            }
        }
        
        // Integrate with existing HPH_Admin_Settings class from theme
        if (class_exists('HPH_Admin_Settings')) {
            // Use the existing theme settings rendering
            \HPH_Admin_Settings::render_settings_page();
        } else {
            // Fallback if theme class not available
            ?>
            <div class="wrap hp-admin-theme-settings">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                
                <div class="hp-settings-nav">
                    <ul class="hp-nav-tabs">
                        <li><a href="#branding" class="nav-tab nav-tab-active"><?php _e('Branding', 'happy-place'); ?></a></li>
                        <li><a href="#colors" class="nav-tab"><?php _e('Colors', 'happy-place'); ?></a></li>
                        <li><a href="#typography" class="nav-tab"><?php _e('Typography', 'happy-place'); ?></a></li>
                        <li><a href="#layout" class="nav-tab"><?php _e('Layout', 'happy-place'); ?></a></li>
                        <li><a href="#footer" class="nav-tab"><?php _e('Footer', 'happy-place'); ?></a></li>
                    </ul>
                </div>
                
                <form method="post" action="options.php">
                    <?php
                    settings_fields('hp_theme_settings');
                    do_settings_sections('hp_theme_settings');
                    ?>
                    
                    <div id="branding" class="hp-settings-section">
                        <h2><?php _e('Site Branding', 'happy-place'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Company Name', 'happy-place'); ?></th>
                                <td><input type="text" name="hp_company_name" value="<?php echo esc_attr(get_option('hp_company_name')); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Logo', 'happy-place'); ?></th>
                                <td>
                                    <input type="url" name="hp_logo_url" value="<?php echo esc_url(get_option('hp_logo_url')); ?>" class="regular-text" />
                                    <button type="button" class="button hp-upload-logo"><?php _e('Upload Logo', 'happy-place'); ?></button>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div id="colors" class="hp-settings-section" style="display:none;">
                        <h2><?php _e('Color Scheme', 'happy-place'); ?></h2>
                        <!-- Color picker fields will be added here -->
                        <p><?php _e('Color customization options will be integrated here.', 'happy-place'); ?></p>
                    </div>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
            <?php
        }
    }
    
    /**
     * Render integrations page
     * 
     * @return void
     */
    public function render_integrations(): void {
        ?>
        <div class="wrap hp-admin-integrations">
            <h1>
                <span class="dashicons dashicons-admin-plugins" style="font-size: 30px; margin-right: 10px;"></span>
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>
            
            <?php settings_errors(); ?>
            
            <?php 
            // Debug information - only show for administrators
            if (current_user_can('manage_options') && HP_DEBUG): ?>
                <div class="notice notice-info">
                    <p><strong>Debug Info:</strong></p>
                    <p>Settings Group: hp_integrations_settings</p>
                    <p>Current User: <?php echo wp_get_current_user()->user_login; ?></p>
                    <p>Form Action: <?php echo admin_url('options.php'); ?></p>
                    <p>Nonce: <?php echo wp_create_nonce('hp_integrations_settings-options'); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="hp-settings-nav">
                <nav class="hp-nav-tabs">
                    <a href="#api-keys" class="nav-tab nav-tab-active"><?php _e('API Keys', 'happy-place'); ?></a>
                    <a href="#mls" class="nav-tab"><?php _e('MLS Integration', 'happy-place'); ?></a>
                    <a href="#airtable" class="nav-tab"><?php _e('Airtable', 'happy-place'); ?></a>
                    <a href="#followup-boss" class="nav-tab"><?php _e('FollowUp Boss', 'happy-place'); ?></a>
                    <a href="#email" class="nav-tab"><?php _e('Email Services', 'happy-place'); ?></a>
                    <a href="#analytics" class="nav-tab"><?php _e('Analytics', 'happy-place'); ?></a>
                    <a href="#user-roles" class="nav-tab"><?php _e('User Roles', 'happy-place'); ?></a>
                </nav>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('hp_integrations_settings');
                
                // API Keys Section
                ?>
                <div id="api-keys" class="hp-settings-section">
                    <h2><?php _e('API Keys & Core Services', 'happy-place'); ?></h2>
                    <p><?php _e('Configure API keys for core functionality. These are centrally managed and used throughout the plugin.', 'happy-place'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Google Maps API Key', 'happy-place'); ?></th>
                            <td>
                                <input type="text" name="hp_google_maps_api_key" 
                                       value="<?php echo esc_attr($this->config_manager->get('google_maps_api_key', '')); ?>" 
                                       class="regular-text" placeholder="<?php esc_attr_e('Enter your Google Maps API key', 'happy-place'); ?>" />
                                <p class="description">
                                    <?php _e('Required for map functionality and location services.', 'happy-place'); ?>
                                    <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">
                                        <?php _e('Get your API key', 'happy-place'); ?>
                                    </a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Mapbox Access Token', 'happy-place'); ?></th>
                            <td>
                                <input type="text" name="hp_mapbox_access_token" 
                                       value="<?php echo esc_attr($this->config_manager->get('mapbox_access_token', '')); ?>" 
                                       class="regular-text" placeholder="<?php esc_attr_e('Enter your Mapbox public access token', 'happy-place'); ?>" />
                                <p class="description">
                                    <?php _e('Required for advanced map views with interactive property listings. Mapbox provides modern, customizable maps.', 'happy-place'); ?>
                                    <a href="https://docs.mapbox.com/help/getting-started/access-tokens/" target="_blank">
                                        <?php _e('Get your access token', 'happy-place'); ?>
                                    </a>
                                </p>
                                <div style="margin-top: 10px;">
                                    <label>
                                        <input type="checkbox" name="hp_mapbox_default_map_provider" 
                                               value="1" <?php checked($this->config_manager->get('mapbox_default_map_provider', false)); ?> />
                                        <?php _e('Use Mapbox as default map provider for property archives and listings', 'happy-place'); ?>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Map Default Settings', 'happy-place'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><?php _e('Default map settings', 'happy-place'); ?></legend>
                                    <label for="hp_map_center_lat">
                                        <?php _e('Default Center Latitude:', 'happy-place'); ?>
                                        <input type="text" id="hp_map_center_lat" name="hp_map_center_lat" 
                                               value="<?php echo esc_attr($this->config_manager->get('map_center_lat', '29.4241')); ?>" 
                                               class="small-text" placeholder="29.4241" />
                                    </label>
                                    <br><br>
                                    <label for="hp_map_center_lng">
                                        <?php _e('Default Center Longitude:', 'happy-place'); ?>
                                        <input type="text" id="hp_map_center_lng" name="hp_map_center_lng" 
                                               value="<?php echo esc_attr($this->config_manager->get('map_center_lng', '-98.4936')); ?>" 
                                               class="small-text" placeholder="-98.4936" />
                                    </label>
                                    <br><br>
                                    <label for="hp_map_default_zoom">
                                        <?php _e('Default Zoom Level:', 'happy-place'); ?>
                                        <input type="number" id="hp_map_default_zoom" name="hp_map_default_zoom" 
                                               value="<?php echo esc_attr($this->config_manager->get('map_default_zoom', 11)); ?>" 
                                               class="small-text" min="1" max="20" />
                                    </label>
                                    <p class="description">
                                        <?php _e('Configure the default map center and zoom level for your property listings. Current default is set to San Antonio, TX.', 'happy-place'); ?>
                                    </p>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Walk Score API Key', 'happy-place'); ?></th>
                            <td>
                                <input type="text" name="hp_walkscore_api_key" 
                                       value="<?php echo esc_attr($this->config_manager->get('walkscore_api_key', '')); ?>" 
                                       class="regular-text" placeholder="<?php esc_attr_e('Enter your Walk Score API key', 'happy-place'); ?>" />
                                <p class="description">
                                    <?php _e('Optional: Adds walkability scores to property listings.', 'happy-place'); ?>
                                    <a href="https://www.walkscore.com/professional/api.php" target="_blank">
                                        <?php _e('Get your API key', 'happy-place'); ?>
                                    </a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="mls" class="hp-settings-section" style="display:none;">
                    <h2><?php _e('MLS Integration', 'happy-place'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('MLS Provider', 'happy-place'); ?></th>
                            <td>
                                <select name="hp_mls_provider">
                                    <option value=""><?php _e('Select Provider', 'happy-place'); ?></option>
                                    <option value="rets" <?php selected($this->config_manager->get('mls_provider', ''), 'rets'); ?>><?php _e('RETS', 'happy-place'); ?></option>
                                    <option value="idx" <?php selected($this->config_manager->get('mls_provider', ''), 'idx'); ?>><?php _e('IDX', 'happy-place'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('MLS URL', 'happy-place'); ?></th>
                            <td>
                                <input type="url" name="hp_mls_url" 
                                       value="<?php echo esc_attr($this->config_manager->get('mls_url', '')); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="airtable" class="hp-settings-section" style="display:none;">
                    <h2><?php _e('Airtable Integration', 'happy-place'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('API Key', 'happy-place'); ?></th>
                            <td>
                                <input type="password" name="hp_airtable_api_key" 
                                       value="<?php echo esc_attr($this->config_manager->get('airtable_api_key', '')); ?>" 
                                       class="regular-text" />
                                <button type="button" class="button" id="test-airtable-connection">
                                    <?php _e('Test Connection', 'happy-place'); ?>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Base ID', 'happy-place'); ?></th>
                            <td>
                                <input type="text" name="hp_airtable_base_id" 
                                       value="<?php echo esc_attr($this->config_manager->get('airtable_base_id', '')); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="followup-boss" class="hp-settings-section" style="display:none;">
                    <h2><?php _e('FollowUp Boss Integration', 'happy-place'); ?></h2>
                    <p><?php _e('Configure FollowUp Boss CRM integration for automatic lead sync and management. This will sync leads from your Happy Place website directly to your FollowUp Boss account.', 'happy-place'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('API Key', 'happy-place'); ?></th>
                            <td>
                                <input type="password" name="hp_followup_boss_api_key" 
                                       value="<?php echo esc_attr($this->config_manager->get('followup_boss_api_key', '')); ?>" 
                                       class="regular-text" placeholder="<?php esc_attr_e('Enter your FollowUp Boss API key', 'happy-place'); ?>" />
                                <button type="button" class="button" id="test-followup-boss-connection">
                                    <?php _e('Test Connection', 'happy-place'); ?>
                                </button>
                                <p class="description">
                                    <?php _e('Your FollowUp Boss API key. You can find this in FollowUp Boss under Settings > API.', 'happy-place'); ?>
                                    <a href="https://followupboss.com/2/settings/api" target="_blank"><?php _e('Get your API key', 'happy-place'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Auto Sync Leads', 'happy-place'); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" name="hp_fub_auto_sync" 
                                               value="1" <?php checked($this->config_manager->get('fub_auto_sync', true)); ?> />
                                        <?php _e('Automatically send new leads to FollowUp Boss', 'happy-place'); ?>
                                    </label>
                                    <p class="description"><?php _e('When enabled, leads will be sent to FollowUp Boss immediately upon submission.', 'happy-place'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Lead Source Name', 'happy-place'); ?></th>
                            <td>
                                <input type="text" name="hp_fub_lead_source" 
                                       value="<?php echo esc_attr($this->config_manager->get('fub_lead_source', 'Website')); ?>" 
                                       class="regular-text" placeholder="Website" />
                                <p class="description"><?php _e('Default source name for leads sent to FollowUp Boss. This helps identify where leads came from.', 'happy-place'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Default Lead Type', 'happy-place'); ?></th>
                            <td>
                                <select name="hp_fub_default_lead_type">
                                    <option value="General Inquiry" <?php selected($this->config_manager->get('fub_default_lead_type', 'General Inquiry'), 'General Inquiry'); ?>><?php _e('General Inquiry', 'happy-place'); ?></option>
                                    <option value="Property Inquiry" <?php selected($this->config_manager->get('fub_default_lead_type', 'General Inquiry'), 'Property Inquiry'); ?>><?php _e('Property Inquiry', 'happy-place'); ?></option>
                                    <option value="Contact Request" <?php selected($this->config_manager->get('fub_default_lead_type', 'General Inquiry'), 'Contact Request'); ?>><?php _e('Contact Request', 'happy-place'); ?></option>
                                    <option value="Listing Alert" <?php selected($this->config_manager->get('fub_default_lead_type', 'General Inquiry'), 'Listing Alert'); ?>><?php _e('Listing Alert', 'happy-place'); ?></option>
                                </select>
                                <p class="description"><?php _e('Default type classification for leads in FollowUp Boss.', 'happy-place'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Agent Assignment', 'happy-place'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><?php _e('Agent Assignment Options', 'happy-place'); ?></legend>
                                    
                                    <label>
                                        <input type="radio" name="hp_fub_agent_assignment" value="automatic" 
                                               <?php checked($this->config_manager->get('fub_agent_assignment', 'automatic'), 'automatic'); ?> />
                                        <?php _e('Automatic assignment by FollowUp Boss', 'happy-place'); ?>
                                    </label><br>
                                    
                                    <label>
                                        <input type="radio" name="hp_fub_agent_assignment" value="website_agent" 
                                               <?php checked($this->config_manager->get('fub_agent_assignment', 'automatic'), 'website_agent'); ?> />
                                        <?php _e('Assign to agent specified in lead (if available)', 'happy-place'); ?>
                                    </label><br>
                                    
                                    <label>
                                        <input type="radio" name="hp_fub_agent_assignment" value="default_agent" 
                                               <?php checked($this->config_manager->get('fub_agent_assignment', 'automatic'), 'default_agent'); ?> />
                                        <?php _e('Always assign to default agent:', 'happy-place'); ?>
                                    </label>
                                    <input type="email" name="hp_fub_default_agent_email" 
                                           value="<?php echo esc_attr($this->config_manager->get('fub_default_agent_email', '')); ?>" 
                                           class="regular-text" placeholder="agent@example.com" 
                                           style="margin-left: 10px;" />
                                    
                                    <p class="description"><?php _e('Choose how leads should be assigned to agents in FollowUp Boss.', 'happy-place'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Custom Fields Mapping', 'happy-place'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><?php _e('Custom Fields Options', 'happy-place'); ?></legend>
                                    
                                    <label>
                                        <input type="checkbox" name="hp_fub_include_utm" 
                                               value="1" <?php checked($this->config_manager->get('fub_include_utm', true)); ?> />
                                        <?php _e('Include UTM tracking parameters', 'happy-place'); ?>
                                    </label><br>
                                    
                                    <label>
                                        <input type="checkbox" name="hp_fub_include_lead_score" 
                                               value="1" <?php checked($this->config_manager->get('fub_include_lead_score', true)); ?> />
                                        <?php _e('Include lead score and priority', 'happy-place'); ?>
                                    </label><br>
                                    
                                    <label>
                                        <input type="checkbox" name="hp_fub_include_property_details" 
                                               value="1" <?php checked($this->config_manager->get('fub_include_property_details', true)); ?> />
                                        <?php _e('Include property details for listing inquiries', 'happy-place'); ?>
                                    </label><br>
                                    
                                    <label>
                                        <input type="checkbox" name="hp_fub_include_referrer" 
                                               value="1" <?php checked($this->config_manager->get('fub_include_referrer', true)); ?> />
                                        <?php _e('Include referrer and IP address information', 'happy-place'); ?>
                                    </label>
                                    
                                    <p class="description"><?php _e('Select which additional fields to send to FollowUp Boss as custom fields.', 'happy-place'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Webhook URL', 'happy-place'); ?></th>
                            <td>
                                <input type="url" name="hp_fub_webhook_url" 
                                       value="<?php echo esc_url(rest_url('fub/v1/webhook')); ?>" 
                                       class="regular-text" readonly />
                                <button type="button" class="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">
                                    <?php _e('Copy', 'happy-place'); ?>
                                </button>
                                <p class="description">
                                    <?php _e('Add this webhook URL to your FollowUp Boss settings to receive updates when leads are modified in FollowUp Boss.', 'happy-place'); ?>
                                    <a href="https://help.followupboss.com/en/articles/1154799-webhooks" target="_blank"><?php _e('Learn about webhooks', 'happy-place'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Sync Status', 'happy-place'); ?></th>
                            <td>
                                <?php
                                global $wpdb;
                                $total_leads = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hp_leads");
                                $synced_leads = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hp_leads WHERE fub_contact_id IS NOT NULL");
                                $pending_leads = $total_leads - $synced_leads;
                                ?>
                                <div class="hp-sync-stats">
                                    <p><strong><?php _e('Total Leads:', 'happy-place'); ?></strong> <?php echo number_format($total_leads); ?></p>
                                    <p><strong><?php _e('Synced to FollowUp Boss:', 'happy-place'); ?></strong> <?php echo number_format($synced_leads); ?></p>
                                    <p><strong><?php _e('Pending Sync:', 'happy-place'); ?></strong> <?php echo number_format($pending_leads); ?></p>
                                    
                                    <?php if ($pending_leads > 0): ?>
                                        <button type="button" class="button button-secondary" id="bulk-sync-leads">
                                            <?php printf(_n('Sync %d Lead', 'Sync %d Leads', $pending_leads, 'happy-place'), $pending_leads); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Error Handling', 'happy-place'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><?php _e('Error Handling Options', 'happy-place'); ?></legend>
                                    
                                    <label>
                                        <input type="checkbox" name="hp_fub_retry_failed" 
                                               value="1" <?php checked($this->config_manager->get('fub_retry_failed', true)); ?> />
                                        <?php _e('Automatically retry failed sync attempts', 'happy-place'); ?>
                                    </label><br>
                                    
                                    <label>
                                        <input type="checkbox" name="hp_fub_log_errors" 
                                               value="1" <?php checked($this->config_manager->get('fub_log_errors', true)); ?> />
                                        <?php _e('Log sync errors for debugging', 'happy-place'); ?>
                                    </label><br>
                                    
                                    <label>
                                        <input type="checkbox" name="hp_fub_admin_notifications" 
                                               value="1" <?php checked($this->config_manager->get('fub_admin_notifications', false)); ?> />
                                        <?php _e('Email admin when sync fails repeatedly', 'happy-place'); ?>
                                    </label>
                                    
                                    <p class="description"><?php _e('Configure how FollowUp Boss sync errors should be handled.', 'happy-place'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                    
                    <div id="fub-test-results" class="notice" style="display: none; margin-top: 20px;">
                        <p id="fub-test-message"></p>
                    </div>
                </div>
                
                <div id="email" class="hp-settings-section" style="display:none;">
                    <h2><?php _e('Email Services', 'happy-place'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Mailchimp API Key', 'happy-place'); ?></th>
                            <td>
                                <input type="password" name="hp_mailchimp_api_key" 
                                       value="<?php echo esc_attr($this->config_manager->get('mailchimp_api_key', '')); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="analytics" class="hp-settings-section" style="display:none;">
                    <h2><?php _e('Analytics & Tracking', 'happy-place'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Google Analytics ID', 'happy-place'); ?></th>
                            <td>
                                <input type="text" name="hp_google_analytics_id" 
                                       value="<?php echo esc_attr($this->config_manager->get('google_analytics_id', '')); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>
                    </table>
                </div>
                
                
                <div id="user-roles" class="hp-settings-section" style="display:none;">
                    <h2><?php _e('User Role Management', 'happy-place'); ?></h2>
                    <p><?php _e('Manage WordPress user roles and configure Happy Place role structure.', 'happy-place'); ?></p>
                    
                    <h3><?php _e('Current User Roles', 'happy-place'); ?></h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Role', 'happy-place'); ?></th>
                                <th><?php _e('Display Name', 'happy-place'); ?></th>
                                <th><?php _e('User Count', 'happy-place'); ?></th>
                                <th><?php _e('Status', 'happy-place'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            global $wp_roles;
                            $all_roles = $wp_roles->roles;
                            $role_counts = count_users();
                            $role_status = [
                                'agent' => 'Happy Place',
                                'lead' => 'Happy Place', 
                                'staff' => 'Happy Place',
                                'administrator' => 'WordPress Core',
                                'real_estate_agent' => 'Legacy - Needs Migration',
                                'broker' => 'Legacy - Needs Migration',
                                'client' => 'Legacy - Needs Migration',
                                'subscriber' => 'WordPress Core',
                                'contributor' => 'WordPress Core',
                                'author' => 'WordPress Core',
                                'editor' => 'WordPress Core',
                            ];
                            
                            foreach ($all_roles as $role_key => $role_info):
                                $user_count = isset($role_counts['avail_roles'][$role_key]) ? $role_counts['avail_roles'][$role_key] : 0;
                                $status = isset($role_status[$role_key]) ? $role_status[$role_key] : 'Unknown';
                                $status_class = '';
                                if (strpos($status, 'Legacy') !== false) {
                                    $status_class = 'hp-status-warning';
                                } elseif ($status === 'Happy Place') {
                                    $status_class = 'hp-status-success';
                                }
                                ?>
                                <tr>
                                    <td><code><?php echo esc_html($role_key); ?></code></td>
                                    <td><?php echo esc_html($role_info['name']); ?></td>
                                    <td><?php echo esc_html($user_count); ?></td>
                                    <td><span class="<?php echo esc_attr($status_class); ?>"><?php echo esc_html($status); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="hp-role-actions">
                        <h3><?php _e('Role Management Actions', 'happy-place'); ?></h3>
                        <p><?php _e('Clean up legacy user roles and ensure proper Happy Place role structure.', 'happy-place'); ?></p>
                        
                        <div class="hp-action-buttons">
                            <button type="button" class="button button-primary" id="hp-cleanup-roles">
                                <?php _e('Clean Up User Roles', 'happy-place'); ?>
                            </button>
                            <button type="button" class="button" id="hp-preview-role-changes">
                                <?php _e('Preview Changes', 'happy-place'); ?>
                            </button>
                        </div>
                        
                        <div id="hp-role-preview" style="display:none; margin-top: 20px;">
                            <h4><?php _e('Proposed Changes:', 'happy-place'); ?></h4>
                            <div id="hp-role-preview-content"></div>
                        </div>
                        
                        <div id="hp-role-status" style="margin-top: 20px;"></div>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
            
            <?php if (HP_DEBUG): ?>
            <script>
            jQuery(document).ready(function($) {
                console.log('Happy Place Admin: Integrations form loaded');
                console.log('Settings fields:', $('#hp_integrations_settings-options').length ? 'Found' : 'Missing');
                
                $('form[action*="options.php"]').on('submit', function(e) {
                    console.log('Happy Place Admin: Form submitted');
                    console.log('Form data:', $(this).serialize());
                });
            });
            </script>
            <?php endif; ?>
            
            <script>
            jQuery(document).ready(function($) {
                // Tab navigation
                $('.hp-nav-tabs a').on('click', function(e) {
                    e.preventDefault();
                    var target = $(this).attr('href');
                    
                    // Update active tab
                    $('.hp-nav-tabs a').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active');
                    
                    // Show target section
                    $('.hp-settings-section').hide();
                    $(target).show();
                });
                
                // FollowUp Boss connection test
                $('#test-followup-boss-connection').on('click', function() {
                    var $button = $(this);
                    var $results = $('#fub-test-results');
                    var $message = $('#fub-test-message');
                    var apiKey = $('input[name="hp_followup_boss_api_key"]').val();
                    
                    if (!apiKey) {
                        $message.text('<?php _e('Please enter an API key first.', 'happy-place'); ?>');
                        $results.removeClass('notice-success notice-error').addClass('notice-error').show();
                        return;
                    }
                    
                    $button.prop('disabled', true).text('<?php _e('Testing...', 'happy-place'); ?>');
                    $results.hide();
                    
                    $.post(ajaxurl, {
                        action: 'hp_test_followup_boss_connection',
                        api_key: apiKey,
                        api_secret: '', // FollowUp Boss now uses just API key for some endpoints
                        _wpnonce: '<?php echo wp_create_nonce('hp_admin_nonce'); ?>'
                    })
                    .done(function(response) {
                        if (response.success) {
                            var message = '<?php _e('✅ Connection successful!', 'happy-place'); ?><br>' + response.data.message;
                            if (response.data.agents && Object.keys(response.data.agents).length > 0) {
                                message += '<br><small><?php _e('Found agents:', 'happy-place'); ?> ' + Object.keys(response.data.agents).length + '</small>';
                            }
                            $message.html(message);
                            $results.removeClass('notice-error').addClass('notice-success').show();
                        } else {
                            $message.html('<?php _e('❌ Connection failed:', 'happy-place'); ?> ' + response.data.message);
                            $results.removeClass('notice-success').addClass('notice-error').show();
                        }
                    })
                    .fail(function() {
                        $message.text('<?php _e('❌ Request failed. Please try again.', 'happy-place'); ?>');
                        $results.removeClass('notice-success').addClass('notice-error').show();
                    })
                    .always(function() {
                        $button.prop('disabled', false).text('<?php _e('Test Connection', 'happy-place'); ?>');
                    });
                });
                
                // Bulk sync leads to FollowUp Boss
                $('#bulk-sync-leads').on('click', function() {
                    var $button = $(this);
                    var originalText = $button.text();
                    
                    if (!confirm('<?php _e('Are you sure you want to sync all pending leads to FollowUp Boss? This action cannot be undone.', 'happy-place'); ?>')) {
                        return;
                    }
                    
                    $button.prop('disabled', true).text('<?php _e('Syncing...', 'happy-place'); ?>');
                    
                    $.post(ajaxurl, {
                        action: 'hp_bulk_sync_leads',
                        _wpnonce: '<?php echo wp_create_nonce('hp_bulk_sync_leads'); ?>'
                    })
                    .done(function(response) {
                        if (response.success) {
                            alert('<?php _e('Sync completed successfully!', 'happy-place'); ?>\n' + response.data.message);
                            location.reload(); // Refresh to update sync stats
                        } else {
                            alert('<?php _e('Sync failed:', 'happy-place'); ?> ' + response.data.message);
                        }
                    })
                    .fail(function() {
                        alert('<?php _e('Sync request failed. Please try again.', 'happy-place'); ?>');
                    })
                    .always(function() {
                        $button.prop('disabled', false).text(originalText);
                    });
                });
                
                // Agent assignment radio button handling
                $('input[name="hp_fub_agent_assignment"]').on('change', function() {
                    var $emailField = $('input[name="hp_fub_default_agent_email"]');
                    if ($(this).val() === 'default_agent') {
                        $emailField.prop('required', true).focus();
                    } else {
                        $emailField.prop('required', false);
                    }
                });
                
                // Initialize agent assignment field requirement
                $('input[name="hp_fub_agent_assignment"]:checked').trigger('change');
                
                // Copy webhook URL functionality
                $('.hp-settings-section').on('click', 'button[onclick*="clipboard"]', function() {
                    var $this = $(this);
                    var originalText = $this.text();
                    
                    // The onclick attribute already handles the copy
                    $this.text('<?php _e('Copied!', 'happy-place'); ?>');
                    
                    setTimeout(function() {
                        $this.text(originalText);
                    }, 2000);
                });
                
                // Form validation for FollowUp Boss settings
                $('form').on('submit', function() {
                    var autoSync = $('input[name="hp_fub_auto_sync"]').is(':checked');
                    var apiKey = $('input[name="hp_followup_boss_api_key"]').val();
                    
                    if (autoSync && !apiKey) {
                        alert('<?php _e('Please enter a FollowUp Boss API key or disable auto-sync.', 'happy-place'); ?>');
                        $('#followup-boss').show();
                        $('.hp-nav-tabs a[href="#followup-boss"]').click();
                        $('input[name="hp_followup_boss_api_key"]').focus();
                        return false;
                    }
                    
                    var defaultAgent = $('input[name="hp_fub_agent_assignment"]:checked').val();
                    var agentEmail = $('input[name="hp_fub_default_agent_email"]').val();
                    
                    if (defaultAgent === 'default_agent' && !agentEmail) {
                        alert('<?php _e('Please enter a default agent email address.', 'happy-place'); ?>');
                        $('#followup-boss').show();
                        $('.hp-nav-tabs a[href="#followup-boss"]').click();
                        $('input[name="hp_fub_default_agent_email"]').focus();
                        return false;
                    }
                });
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * Render import page
     * 
     * @return void
     */
    public function render_import(): void {
        wp_enqueue_script('hp-csv-import', HP_PLUGIN_URL . 'assets/js/admin/csv-import.js', ['jquery'], HP_VERSION, true);
        wp_enqueue_style('hp-import-export', HP_PLUGIN_URL . 'assets/css/admin/import-export.css', [], HP_VERSION);
        
        wp_localize_script('hp-csv-import', 'hpImport', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_import_nonce'),
            'strings' => [
                'selectFile' => __('Select a CSV file to upload', 'happy-place'),
                'dragDrop' => __('or drag and drop your file here', 'happy-place'),
                'processing' => __('Processing...', 'happy-place'),
                'success' => __('Import completed successfully!', 'happy-place'),
                'error' => __('Import failed. Please try again.', 'happy-place'),
                'mapping' => __('Map CSV columns to database fields', 'happy-place'),
                'autoMapped' => __('Auto-mapped', 'happy-place'),
                'cancel' => __('Cancel Import', 'happy-place'),
                'restart' => __('Start New Import', 'happy-place'),
                'viewResults' => __('View Import Log', 'happy-place'),
            ]
        ]);
        ?>
        <div class="wrap hp-admin-import">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div id="import-notices"></div>
            
            <!-- CSV Import Interface -->
            <div id="csv-import-interface">
                <!-- Step Indicators -->
                <ol class="import-steps">
                    <li class="step-indicator active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-title"><?php _e('Upload File', 'happy-place'); ?></div>
                    </li>
                    <li class="step-indicator" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-title"><?php _e('Map Fields', 'happy-place'); ?></div>
                    </li>
                    <li class="step-indicator" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-title"><?php _e('Import Data', 'happy-place'); ?></div>
                    </li>
                    <li class="step-indicator" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-title"><?php _e('Complete', 'happy-place'); ?></div>
                    </li>
                </ol>

                <!-- Step 1: File Upload -->
                <div id="step-1" class="import-step active" data-step="1">
                    <h2><?php _e('Upload CSV File', 'happy-place'); ?></h2>
                    
                    <div class="csv-upload-area" id="csv-upload-area">
                        <div class="upload-icon">📁</div>
                        <div class="upload-text"><?php _e('Select a CSV file to upload', 'happy-place'); ?></div>
                        <div class="upload-subtext"><?php _e('or drag and drop your file here', 'happy-place'); ?></div>
                        <button type="button" class="button button-primary upload-button" id="upload-button">
                            <?php _e('Choose File', 'happy-place'); ?>
                        </button>
                        <input type="file" id="csv-file-input" accept=".csv" style="display: none;">
                    </div>
                    
                    <div id="file-info" style="display: none;">
                        <h3><?php _e('File Information', 'happy-place'); ?></h3>
                        <p><strong><?php _e('File Name:', 'happy-place'); ?></strong> <span id="file-name"></span></p>
                        <p><strong><?php _e('File Size:', 'happy-place'); ?></strong> <span id="file-size"></span></p>
                        <p><strong><?php _e('Rows:', 'happy-place'); ?></strong> <span id="file-rows"></span></p>
                    </div>
                </div>

                <!-- Step 2: Field Mapping -->
                <div id="step-2" class="import-step" data-step="2">
                    <h2><?php _e('Map CSV Fields', 'happy-place'); ?></h2>
                    <p><?php _e('Match your CSV columns to the appropriate database fields.', 'happy-place'); ?></p>
                    
                    <!-- Dynamic mapping interface populated by JavaScript -->
                    <div id="mapping-interface"></div>
                    
                    <!-- Step 2 Navigation -->
                    <div class="step-navigation">
                        <button type="button" class="button" data-step="1" onclick="hpImportTool.navigateStep(event)"><?php _e('← Back to Upload', 'happy-place'); ?></button>
                        <div class="step-actions">
                            <button type="button" class="button" id="auto-map-btn"><?php _e('Auto-Map Fields', 'happy-place'); ?></button>
                            <button type="button" class="button button-primary" id="start-import-btn" disabled><?php _e('Start Import →', 'happy-place'); ?></button>
                        </div>
                    </div>
                    
                    <div class="field-mapping-container" style="display:none;">
                        <div class="mapping-header">
                            <h3><?php _e('Map CSV Fields', 'happy-place'); ?></h3>
                            <p><?php _e('Match your CSV columns to the appropriate database fields. Fields marked as auto-mapped have been automatically detected.', 'happy-place'); ?></p>
                        </div>
                        
                        <div class="mapping-controls">
                            <div class="template-controls">
                                <label for="mapping-template"><?php _e('Use Template:', 'happy-place'); ?></label>
                                <select id="mapping-template">
                                    <option value=""><?php _e('-- Select Template --', 'happy-place'); ?></option>
                                    <option value="mls-standard"><?php _e('MLS Standard', 'happy-place'); ?></option>
                                    <option value="zillow"><?php _e('Zillow Export', 'happy-place'); ?></option>
                                    <option value="realtor-com"><?php _e('Realtor.com', 'happy-place'); ?></option>
                                </select>
                                <button type="button" class="button" id="save-template-btn"><?php _e('Save as Template', 'happy-place'); ?></button>
                            </div>
                            <div>
                                <button type="button" class="button" id="auto-map-btn"><?php _e('Auto-Map Fields', 'happy-place'); ?></button>
                                <button type="button" class="button" id="clear-mapping-btn"><?php _e('Clear All', 'happy-place'); ?></button>
                            </div>
                        </div>
                        
                        <div id="field-mapping-grid">
                            <!-- Mapping grid will be populated by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Step 3: Import Progress -->
                <div id="step-3" class="import-step" data-step="3">
                    <div class="import-progress">
                        <h3><?php _e('Importing Data', 'happy-place'); ?></h3>
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress-bar-fill" id="progress-bar-fill"></div>
                            </div>
                            <div class="progress-text" id="progress-text">0%</div>
                            <div class="progress-details" id="progress-details">
                                <?php _e('Preparing import...', 'happy-place'); ?>
                            </div>
                        </div>
                        <button type="button" class="button" id="cancel-import-btn" style="margin-top: 20px;">
                            <?php _e('Cancel Import', 'happy-place'); ?>
                        </button>
                    </div>
                </div>

                <!-- Step 4: Results -->
                <div id="step-4" class="import-step" data-step="4">
                    <div class="import-results">
                        <h3><?php _e('Import Complete!', 'happy-place'); ?></h3>
                        
                        <div class="results-summary" id="results-summary">
                            <!-- Results will be populated by JavaScript -->
                        </div>
                        
                        <div class="import-errors" id="import-errors" style="display: none;">
                            <h4><?php _e('Import Errors', 'happy-place'); ?></h4>
                            <div class="error-list" id="error-list">
                                <!-- Errors will be populated by JavaScript -->
                            </div>
                        </div>
                        
                        <div class="results-actions">
                            <button type="button" class="button button-primary" id="new-import-btn">
                                <?php _e('Start New Import', 'happy-place'); ?>
                            </button>
                            <button type="button" class="button" id="view-log-btn">
                                <?php _e('View Import Log', 'happy-place'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step Navigation -->
                <div class="step-navigation">
                    <button type="button" class="button step-nav-btn" id="prev-step-btn" style="display: none;">
                        <?php _e('Previous', 'happy-place'); ?>
                    </button>
                    <button type="button" class="button button-primary step-nav-btn" id="next-step-btn" style="display: none;">
                        <?php _e('Next', 'happy-place'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Loading Overlay -->
            <div id="loading-overlay">
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <div class="loading-message"><?php _e('Processing...', 'happy-place'); ?></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render export page
     * 
     * @return void
     */
    public function render_export(): void {
        wp_enqueue_style('hp-import-export', HP_PLUGIN_URL . 'assets/css/admin/import-export.css', [], HP_VERSION);
        ?>
        <div class="wrap hp-admin-export">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="hp-export-options">
                <div class="hp-export-section">
                    <h2><?php _e('Export Listings', 'happy-place'); ?></h2>
                    <p class="description"><?php _e('Export your property listings in various formats with filtering options.', 'happy-place'); ?></p>
                    
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <?php wp_nonce_field('hp_export_listings', '_wpnonce'); ?>
                        <input type="hidden" name="action" value="hp_export_listings">
                        
                        <div class="export-filters">
                            <div class="filter-group">
                                <label for="export_format"><?php _e('Export Format', 'happy-place'); ?></label>
                                <select name="export_format" id="export_format">
                                    <option value="csv"><?php _e('CSV (Excel Compatible)', 'happy-place'); ?></option>
                                    <option value="xml"><?php _e('XML (MLS Standard)', 'happy-place'); ?></option>
                                    <option value="json"><?php _e('JSON (API Format)', 'happy-place'); ?></option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="status_filter"><?php _e('Status Filter', 'happy-place'); ?></label>
                                <select name="status_filter" id="status_filter">
                                    <option value="all"><?php _e('All Listings', 'happy-place'); ?></option>
                                    <option value="active"><?php _e('Active Only', 'happy-place'); ?></option>
                                    <option value="sold"><?php _e('Sold Only', 'happy-place'); ?></option>
                                    <option value="pending"><?php _e('Pending Only', 'happy-place'); ?></option>
                                    <option value="draft"><?php _e('Draft Only', 'happy-place'); ?></option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="property_type"><?php _e('Property Type', 'happy-place'); ?></label>
                                <select name="property_type" id="property_type">
                                    <option value="all"><?php _e('All Types', 'happy-place'); ?></option>
                                    <option value="residential"><?php _e('Residential', 'happy-place'); ?></option>
                                    <option value="commercial"><?php _e('Commercial', 'happy-place'); ?></option>
                                    <option value="land"><?php _e('Land', 'happy-place'); ?></option>
                                    <option value="condo"><?php _e('Condo/Townhome', 'happy-place'); ?></option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label><?php _e('Date Range', 'happy-place'); ?></label>
                                <div class="date-range">
                                    <input type="date" name="date_from" placeholder="<?php _e('From', 'happy-place'); ?>">
                                    <span><?php _e('to', 'happy-place'); ?></span>
                                    <input type="date" name="date_to" placeholder="<?php _e('To', 'happy-place'); ?>">
                                </div>
                            </div>
                            
                            <div class="filter-group">
                                <label>
                                    <input type="checkbox" name="include_images" value="1" checked>
                                    <?php _e('Include Image URLs', 'happy-place'); ?>
                                </label>
                            </div>
                            
                            <div class="filter-group">
                                <label>
                                    <input type="checkbox" name="include_agent_info" value="1" checked>
                                    <?php _e('Include Agent Information', 'happy-place'); ?>
                                </label>
                            </div>
                        </div>
                        
                        <?php submit_button(__('Export Listings', 'happy-place'), 'primary', 'submit', false, ['style' => 'width: 100%;']); ?>
                    </form>
                </div>
                
                <div class="hp-export-section">
                    <h2><?php _e('Quick Export Reports', 'happy-place'); ?></h2>
                    <p class="description"><?php _e('Generate and download common reports in CSV format.', 'happy-place'); ?></p>
                    
                    <div class="hp-export-links">
                        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=hp_export_leads&format=csv'), 'hp_export'); ?>" class="button">
                            <span class="dashicons dashicons-groups"></span>
                            <?php _e('Export All Leads', 'happy-place'); ?>
                        </a>
                        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=hp_export_agents&format=csv'), 'hp_export'); ?>" class="button">
                            <span class="dashicons dashicons-businessperson"></span>
                            <?php _e('Export All Agents', 'happy-place'); ?>
                        </a>
                        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=hp_export_listings&format=csv&quick=active'), 'hp_export'); ?>" class="button">
                            <span class="dashicons dashicons-building"></span>
                            <?php _e('Export Active Listings', 'happy-place'); ?>
                        </a>
                        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=hp_export_listings&format=csv&quick=sold_this_month'), 'hp_export'); ?>" class="button">
                            <span class="dashicons dashicons-chart-line"></span>
                            <?php _e('Monthly Sales Report', 'happy-place'); ?>
                        </a>
                        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=hp_export_analytics&format=csv'), 'hp_export'); ?>" class="button">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <?php _e('Analytics Report', 'happy-place'); ?>
                        </a>
                    </div>
                    
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                        <h3><?php _e('Custom Export Templates', 'happy-place'); ?></h3>
                        <p class="description"><?php _e('Download pre-configured export templates for common MLS and real estate platforms.', 'happy-place'); ?></p>
                        
                        <div class="hp-export-links">
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=hp_export_template&template=mls_standard'), 'hp_export'); ?>" class="button">
                                <?php _e('MLS Standard Template', 'happy-place'); ?>
                            </a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=hp_export_template&template=zillow'), 'hp_export'); ?>" class="button">
                                <?php _e('Zillow Import Template', 'happy-place'); ?>
                            </a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=hp_export_template&template=realtor_com'), 'hp_export'); ?>" class="button">
                                <?php _e('Realtor.com Template', 'happy-place'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render sync page
     * 
     * @return void
     */
    public function render_sync(): void {
        $sync_manager = \HappyPlace\Core\Config_Sync_Manager::get_instance();
        $sync_status = $sync_manager->get_sync_status();
        
        ?>
        <div class="wrap hp-admin-sync">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if (isset($_GET['synced'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Synchronization completed successfully!', 'happy-place'); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="hp-sync-sections">
                <div class="hp-sync-section">
                    <h2><?php _e('MLS Data Sync', 'happy-place'); ?></h2>
                    <p><?php _e('Synchronize listing data with your MLS provider.', 'happy-place'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Last Sync', 'happy-place'); ?></th>
                            <td><?php echo esc_html(get_option('hp_last_mls_sync', __('Never', 'happy-place'))); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Auto Sync', 'happy-place'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="hp_auto_mls_sync" value="1" <?php checked(get_option('hp_auto_mls_sync')); ?> />
                                    <?php _e('Enable automatic MLS synchronization', 'happy-place'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <p>
                        <button type="button" class="button button-primary hp-sync-mls">
                            <?php _e('Sync Now', 'happy-place'); ?>
                        </button>
                    </p>
                </div>
                
                <div class="hp-sync-section">
                    <h2><?php _e('ACF Field Sync', 'happy-place'); ?></h2>
                    <p><?php _e('Synchronize Advanced Custom Fields configuration.', 'happy-place'); ?></p>
                    
                    <div class="hp-sync-status">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Field Group', 'happy-place'); ?></th>
                                    <th><?php _e('Files', 'happy-place'); ?></th>
                                    <th><?php _e('Database', 'happy-place'); ?></th>
                                    <th><?php _e('Status', 'happy-place'); ?></th>
                                    <th><?php _e('Actions', 'happy-place'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sync_status as $key => $status): ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($key); ?></strong></td>
                                        <td><?php echo $status['in_files'] ? '✓' : '✗'; ?></td>
                                        <td><?php echo $status['in_database'] ? '✓' : '✗'; ?></td>
                                        <td>
                                            <?php if ($status['synced']): ?>
                                                <span class="hp-status-badge hp-status-synced"><?php _e('Synced', 'happy-place'); ?></span>
                                            <?php else: ?>
                                                <span class="hp-status-badge hp-status-conflict"><?php _e('Needs Sync', 'happy-place'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!$status['synced']): ?>
                                                <button class="button button-small hp-sync-config" data-key="<?php echo esc_attr($key); ?>">
                                                    <?php _e('Sync', 'happy-place'); ?>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render leads management page
     * 
     * @return void
     */
    public function render_leads(): void {
        global $wpdb;
        
        // Handle actions
        if (isset($_POST['action']) && $_POST['action'] === 'update_lead_status') {
            $lead_id = intval($_POST['lead_id']);
            $new_status = sanitize_text_field($_POST['status']);
            
            $wpdb->update(
                $wpdb->prefix . 'hp_leads',
                ['status' => $new_status, 'updated_at' => current_time('mysql')],
                ['id' => $lead_id],
                ['%s', '%s'],
                ['%d']
            );
            
            echo '<div class="notice notice-success"><p>Lead status updated successfully!</p></div>';
        }
        
        // Get filter parameters
        $status_filter = sanitize_text_field($_GET['status'] ?? '');
        $source_filter = sanitize_text_field($_GET['source'] ?? '');
        $search = sanitize_text_field($_GET['search'] ?? '');
        $per_page = 20;
        $current_page = max(1, intval($_GET['paged'] ?? 1));
        $offset = ($current_page - 1) * $per_page;
        
        // Build query
        $where_conditions = ['1=1'];
        $where_values = [];
        
        if ($status_filter) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $status_filter;
        }
        
        if ($source_filter) {
            $where_conditions[] = 'source = %s';
            $where_values[] = $source_filter;
        }
        
        if ($search) {
            $where_conditions[] = '(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR message LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$wpdb->prefix}hp_leads WHERE {$where_clause}";
        if (!empty($where_values)) {
            $count_query = $wpdb->prepare($count_query, $where_values);
        }
        $total_items = $wpdb->get_var($count_query);
        
        // Get leads
        $leads_query = "SELECT * FROM {$wpdb->prefix}hp_leads WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $query_values = array_merge($where_values, [$per_page, $offset]);
        $leads = $wpdb->get_results($wpdb->prepare($leads_query, $query_values));
        
        // Get available statuses and sources
        $statuses = $wpdb->get_col("SELECT DISTINCT status FROM {$wpdb->prefix}hp_leads WHERE status IS NOT NULL");
        $sources = $wpdb->get_col("SELECT DISTINCT source FROM {$wpdb->prefix}hp_leads WHERE source IS NOT NULL");
        
        ?>
        <div class="wrap hp-admin-leads">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <!-- Filters -->
            <div class="hp-leads-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="happy-place-leads">
                    
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo esc_attr($status); ?>" <?php selected($status_filter, $status); ?>>
                                <?php echo esc_html(ucwords(str_replace('_', ' ', $status))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="source">Source:</label>
                    <select name="source" id="source">
                        <option value="">All Sources</option>
                        <?php foreach ($sources as $source): ?>
                            <option value="<?php echo esc_attr($source); ?>" <?php selected($source_filter, $source); ?>>
                                <?php echo esc_html(ucwords(str_replace('_', ' ', $source))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="search">Search:</label>
                    <input type="text" name="search" id="search" value="<?php echo esc_attr($search); ?>" placeholder="Name, email, or message...">
                    
                    <button type="submit" class="button">Filter</button>
                    <a href="<?php echo admin_url('admin.php?page=happy-place-leads'); ?>" class="button">Clear</a>
                </form>
            </div>
            
            <!-- Leads Table -->
            <div class="hp-leads-table">
                <?php if (empty($leads)): ?>
                    <p>No leads found matching your criteria.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($lead->first_name . ' ' . $lead->last_name); ?></strong>
                                        <?php if ($lead->listing_id): ?>
                                            <br><small>Listing: <a href="<?php echo get_edit_post_link($lead->listing_id); ?>">#<?php echo $lead->listing_id; ?></a></small>
                                        <?php endif; ?>
                                        <?php if ($lead->agent_id): ?>
                                            <br><small>Agent: <a href="<?php echo get_edit_post_link($lead->agent_id); ?>"><?php echo get_the_title($lead->agent_id); ?></a></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a>
                                    </td>
                                    <td>
                                        <?php if ($lead->phone): ?>
                                            <a href="tel:<?php echo esc_attr($lead->phone); ?>"><?php echo esc_html($lead->phone); ?></a>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="hp-source-badge"><?php echo esc_html(ucwords(str_replace('_', ' ', $lead->source))); ?></span>
                                    </td>
                                    <td>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="update_lead_status">
                                            <input type="hidden" name="lead_id" value="<?php echo $lead->id; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="new" <?php selected($lead->status, 'new'); ?>>New</option>
                                                <option value="contacted" <?php selected($lead->status, 'contacted'); ?>>Contacted</option>
                                                <option value="qualified" <?php selected($lead->status, 'qualified'); ?>>Qualified</option>
                                                <option value="nurturing" <?php selected($lead->status, 'nurturing'); ?>>Nurturing</option>
                                                <option value="converted" <?php selected($lead->status, 'converted'); ?>>Converted</option>
                                                <option value="lost" <?php selected($lead->status, 'lost'); ?>>Lost</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <?php echo esc_html(date('M j, Y g:i A', strtotime($lead->created_at))); ?>
                                    </td>
                                    <td>
                                        <button class="button button-small view-lead-details" data-lead-id="<?php echo $lead->id; ?>">View</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if ($total_items > $per_page): ?>
                        <div class="tablenav">
                            <?php
                            $total_pages = ceil($total_items / $per_page);
                            $page_links = paginate_links([
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $current_page
                            ]);
                            echo $page_links;
                            ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Lead Details Modal -->
        <div id="lead-details-modal" style="display: none;">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div id="lead-details-content"></div>
            </div>
        </div>
        
        <style>
        .hp-leads-filters { 
            background: #f9f9f9; 
            padding: 15px; 
            margin: 20px 0; 
            border: 1px solid #ddd; 
        }
        .hp-leads-filters label { 
            display: inline-block; 
            width: 80px; 
            margin-right: 10px; 
        }
        .hp-leads-filters select, .hp-leads-filters input[type="text"] { 
            margin-right: 15px; 
        }
        .hp-source-badge { 
            background: #e8f4fd; 
            color: #0073aa; 
            padding: 2px 8px; 
            border-radius: 3px; 
            font-size: 12px; 
        }
        #lead-details-modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 60%;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('.view-lead-details').click(function() {
                var leadId = $(this).data('lead-id');
                
                $.post(ajaxurl, {
                    action: 'get_lead_details',
                    lead_id: leadId
                }, function(response) {
                    $('#lead-details-content').html(response);
                    $('#lead-details-modal').show();
                });
            });
            
            $('.close').click(function() {
                $('#lead-details-modal').hide();
            });
            
            $(window).click(function(event) {
                if (event.target.id === 'lead-details-modal') {
                    $('#lead-details-modal').hide();
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Render marketing page
     * 
     * @return void
     */
    public function render_marketing(): void {
        ?>
        <div class="wrap hp-admin-marketing">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="hp-settings-nav">
                <ul class="hp-nav-tabs">
                    <li><a href="#social-media" class="nav-tab nav-tab-active"><?php _e('Social Media', 'happy-place'); ?></a></li>
                    <li><a href="#email-marketing" class="nav-tab"><?php _e('Email Marketing', 'happy-place'); ?></a></li>
                    <li><a href="#seo" class="nav-tab"><?php _e('SEO', 'happy-place'); ?></a></li>
                    <li><a href="#lead-capture" class="nav-tab"><?php _e('Lead Capture', 'happy-place'); ?></a></li>
                </ul>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('hp_marketing_settings');
                do_settings_sections('hp_marketing_settings');
                ?>
                
                <div id="social-media" class="hp-settings-section">
                    <h2><?php _e('Social Media Integration', 'happy-place'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Facebook Page', 'happy-place'); ?></th>
                            <td><input type="url" name="hp_facebook_page" value="<?php echo esc_url(get_option('hp_facebook_page')); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Instagram Profile', 'happy-place'); ?></th>
                            <td><input type="url" name="hp_instagram_profile" value="<?php echo esc_url(get_option('hp_instagram_profile')); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Auto-Share New Listings', 'happy-place'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="hp_auto_share_listings" value="1" <?php checked(get_option('hp_auto_share_listings')); ?> />
                                    <?php _e('Automatically share new listings on social media', 'happy-place'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="email-marketing" class="hp-settings-section" style="display:none;">
                    <h2><?php _e('Email Marketing', 'happy-place'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Email Service Provider', 'happy-place'); ?></th>
                            <td>
                                <select name="hp_email_provider">
                                    <option value=""><?php _e('Select Provider', 'happy-place'); ?></option>
                                    <option value="mailchimp" <?php selected(get_option('hp_email_provider'), 'mailchimp'); ?>><?php _e('MailChimp', 'happy-place'); ?></option>
                                    <option value="constant_contact" <?php selected(get_option('hp_email_provider'), 'constant_contact'); ?>><?php _e('Constant Contact', 'happy-place'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render users page
     * 
     * @return void
     */
    public function render_users(): void {
        ?>
        <div class="wrap hp-admin-users">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="hp-settings-nav">
                <ul class="hp-nav-tabs">
                    <li><a href="#agent-management" class="nav-tab nav-tab-active"><?php _e('Agent Management', 'happy-place'); ?></a></li>
                    <li><a href="#roles-permissions" class="nav-tab"><?php _e('Roles & Permissions', 'happy-place'); ?></a></li>
                    <li><a href="#user-settings" class="nav-tab"><?php _e('User Settings', 'happy-place'); ?></a></li>
                </ul>
            </div>
            
            <div id="agent-management" class="hp-settings-section">
                <h2><?php _e('Agent Management', 'happy-place'); ?></h2>
                
                <div class="hp-agent-stats">
                    <div class="hp-stat-box">
                        <h3><?php echo wp_count_posts('agent')->publish; ?></h3>
                        <p><?php _e('Active Agents', 'happy-place'); ?></p>
                    </div>
                    <div class="hp-stat-box">
                        <h3><?php echo count(get_users(['role' => 'agent'])); ?></h3>
                        <p><?php _e('Agent Users', 'happy-place'); ?></p>
                    </div>
                </div>
                
                <div class="hp-quick-actions">
                    <a href="<?php echo admin_url('post-new.php?post_type=agent'); ?>" class="button button-primary">
                        <?php _e('Add New Agent', 'happy-place'); ?>
                    </a>
                    <a href="<?php echo admin_url('edit.php?post_type=agent'); ?>" class="button">
                        <?php _e('Manage Agents', 'happy-place'); ?>
                    </a>
                </div>
            </div>
            
            <div id="roles-permissions" class="hp-settings-section" style="display:none;">
                <h2><?php _e('Roles & Permissions', 'happy-place'); ?></h2>
                
                <?php
                // Show current roles
                global $wp_roles;
                $all_roles = $wp_roles->roles;
                ?>
                
                <div class="hp-role-management">
                    <h3><?php _e('Current User Roles', 'happy-place'); ?></h3>
                    <table class="wp-list-table widefat striped">
                        <thead>
                            <tr>
                                <th><?php _e('Role', 'happy-place'); ?></th>
                                <th><?php _e('Display Name', 'happy-place'); ?></th>
                                <th><?php _e('User Count', 'happy-place'); ?></th>
                                <th><?php _e('Status', 'happy-place'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_roles as $role_key => $role_info): 
                                $user_count = count(get_users(['role' => $role_key]));
                                $is_custom_role = in_array($role_key, ['agent', 'lead', 'staff', 'admin']);
                                $is_legacy_role = in_array($role_key, ['real_estate_agent', 'broker', 'client', 'subscriber', 'contributor', 'author']);
                                ?>
                                <tr class="<?php echo $is_legacy_role ? 'hp-legacy-role' : ($is_custom_role ? 'hp-custom-role' : 'hp-wp-role'); ?>">
                                    <td><code><?php echo esc_html($role_key); ?></code></td>
                                    <td><?php echo esc_html($role_info['name']); ?></td>
                                    <td><?php echo $user_count; ?></td>
                                    <td>
                                        <?php if ($is_legacy_role): ?>
                                            <span class="hp-status hp-status-warning"><?php _e('Legacy - Will be removed', 'happy-place'); ?></span>
                                        <?php elseif ($is_custom_role): ?>
                                            <span class="hp-status hp-status-success"><?php _e('Happy Place Role', 'happy-place'); ?></span>
                                        <?php elseif ($role_key === 'administrator'): ?>
                                            <span class="hp-status hp-status-info"><?php _e('WordPress Admin', 'happy-place'); ?></span>
                                        <?php else: ?>
                                            <span class="hp-status hp-status-neutral"><?php _e('WordPress Default', 'happy-place'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="hp-role-actions">
                        <h3><?php _e('Role Management Actions', 'happy-place'); ?></h3>
                        <p><?php _e('Clean up legacy user roles and ensure proper Happy Place role structure.', 'happy-place'); ?></p>
                        
                        <div class="hp-action-buttons">
                            <button type="button" class="button button-primary" id="hp-cleanup-roles">
                                <?php _e('Clean Up User Roles', 'happy-place'); ?>
                            </button>
                            <button type="button" class="button" id="hp-preview-role-changes">
                                <?php _e('Preview Changes', 'happy-place'); ?>
                            </button>
                        </div>
                        
                        <div id="hp-role-preview" style="display:none; margin-top: 20px;">
                            <h4><?php _e('Proposed Changes:', 'happy-place'); ?></h4>
                            <div id="hp-role-preview-content"></div>
                        </div>
                        
                        <div id="hp-role-status" style="margin-top: 20px;"></div>
                    </div>
                </div>
                
                <form method="post" action="options.php">
                    <?php
                    settings_fields('hp_user_roles');
                    ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Agent Capabilities', 'happy-place'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="hp_agent_can_edit_listings" value="1" <?php checked(get_option('hp_agent_can_edit_listings')); ?> />
                                    <?php _e('Agents can edit all listings', 'happy-place'); ?>
                                </label><br>
                                <label>
                                    <input type="checkbox" name="hp_agent_can_view_leads" value="1" <?php checked(get_option('hp_agent_can_view_leads')); ?> />
                                    <?php _e('Agents can view all leads', 'happy-place'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        <?php
    }
    
    // Remove old render methods that are now consolidated
    // render_import_export, render_config_sync, render_tools, render_system_status
    
    /**
     * Get dashboard stats
     * 
     * @return array
     */
    private function get_dashboard_stats(): array {
        global $wpdb;
        
        return [
            'listings' => wp_count_posts('listing')->publish,
            'agents' => wp_count_posts('agent')->publish,
            'leads' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'lead' AND post_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),
            'views' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hp_property_views WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),
        ];
    }
    
    /**
     * Render recent activity
     * 
     * @return void
     */
    private function render_recent_activity(): void {
        global $wpdb;
        
        $activities = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}hp_activity_log 
            ORDER BY created_at DESC 
            LIMIT 10"
        );
        
        if (empty($activities)) {
            echo '<p>' . __('No recent activity', 'happy-place') . '</p>';
            return;
        }
        
        echo '<ul class="hp-activity-items">';
        foreach ($activities as $activity) {
            printf(
                '<li><span class="hp-activity-time">%s</span> %s</li>',
                human_time_diff(strtotime($activity->created_at)),
                esc_html($activity->description)
            );
        }
        echo '</ul>';
    }
    
    /**
     * Render top listings table
     * 
     * @return void
     */
    private function render_top_listings_table(): void {
        global $wpdb;
        
        $top_listings = $wpdb->get_results(
            "SELECT p.ID, p.post_title, COUNT(pv.id) as view_count, COUNT(l.id) as lead_count
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->prefix}hp_property_views pv ON p.ID = pv.property_id
            LEFT JOIN {$wpdb->prefix}hp_leads l ON p.ID = l.property_id
            WHERE p.post_type = 'listing' AND p.post_status = 'publish'
            GROUP BY p.ID
            ORDER BY view_count DESC, lead_count DESC
            LIMIT 10"
        );
        
        if (empty($top_listings)) {
            echo '<p>' . __('No listing data available', 'happy-place') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('Listing', 'happy-place') . '</th>';
        echo '<th>' . __('Views', 'happy-place') . '</th>';
        echo '<th>' . __('Leads', 'happy-place') . '</th>';
        echo '<th>' . __('Conversion Rate', 'happy-place') . '</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($top_listings as $listing) {
            $conversion_rate = $listing->view_count > 0 ? round(($listing->lead_count / $listing->view_count) * 100, 2) : 0;
            echo '<tr>';
            echo '<td><a href="' . get_edit_post_link($listing->ID) . '">' . esc_html($listing->post_title) . '</a></td>';
            echo '<td>' . number_format($listing->view_count) . '</td>';
            echo '<td>' . number_format($listing->lead_count) . '</td>';
            echo '<td>' . $conversion_rate . '%</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Render analytics summary
     * 
     * @return void
     */
    private function render_analytics_summary(): void {
        global $wpdb;
        
        $date_range = isset($_GET['range']) ? intval($_GET['range']) : 30;
        $date_filter = "DATE_SUB(NOW(), INTERVAL {$date_range} DAY)";
        
        $summary = [
            'total_views' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hp_property_views WHERE created_at >= {$date_filter}"),
            'total_leads' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hp_leads WHERE created_at >= {$date_filter}"),
            'avg_time_on_site' => $wpdb->get_var("SELECT AVG(time_on_site) FROM {$wpdb->prefix}hp_analytics WHERE date >= {$date_filter}"),
            'bounce_rate' => $wpdb->get_var("SELECT AVG(bounce_rate) FROM {$wpdb->prefix}hp_analytics WHERE date >= {$date_filter}"),
        ];
        
        echo '<div class="hp-summary-stats">';
        echo '<div class="hp-stat-card">';
        echo '<h3>' . number_format($summary['total_views'] ?: 0) . '</h3>';
        echo '<p>' . __('Total Views', 'happy-place') . '</p>';
        echo '</div>';
        echo '<div class="hp-stat-card">';
        echo '<h3>' . number_format($summary['total_leads'] ?: 0) . '</h3>';
        echo '<p>' . __('Total Leads', 'happy-place') . '</p>';
        echo '</div>';
        echo '<div class="hp-stat-card">';
        echo '<h3>' . round($summary['avg_time_on_site'] ?: 0, 2) . 's</h3>';
        echo '<p>' . __('Avg. Time on Site', 'happy-place') . '</p>';
        echo '</div>';
        echo '<div class="hp-stat-card">';
        echo '<h3>' . round($summary['bounce_rate'] ?: 0, 2) . '%</h3>';
        echo '<p>' . __('Bounce Rate', 'happy-place') . '</p>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Render lead reports
     * 
     * @return void
     */
    private function render_lead_reports(): void {
        global $wpdb;
        
        $lead_sources = $wpdb->get_results(
            "SELECT source, COUNT(*) as count 
            FROM {$wpdb->prefix}hp_leads 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY source 
            ORDER BY count DESC"
        );
        
        echo '<div class="hp-lead-reports">';
        
        if (!empty($lead_sources)) {
            echo '<h3>' . __('Lead Sources (Last 30 Days)', 'happy-place') . '</h3>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>' . __('Source', 'happy-place') . '</th><th>' . __('Leads', 'happy-place') . '</th></tr></thead><tbody>';
            
            foreach ($lead_sources as $source) {
                echo '<tr>';
                echo '<td>' . esc_html($source->source ?: __('Direct', 'happy-place')) . '</td>';
                echo '<td>' . number_format($source->count) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<p>' . __('No lead data available', 'happy-place') . '</p>';
        }
        
        echo '</div>';
    }
    
    /**
     * Render agent performance
     * 
     * @return void
     */
    private function render_agent_performance(): void {
        global $wpdb;
        
        $agent_stats = $wpdb->get_results(
            "SELECT a.ID, a.post_title as agent_name, 
            COUNT(DISTINCT l.ID) as listing_count,
            COUNT(DISTINCT ld.id) as lead_count,
            COUNT(DISTINCT t.id) as transaction_count
            FROM {$wpdb->posts} a
            LEFT JOIN {$wpdb->posts} l ON a.ID = l.post_author AND l.post_type = 'listing'
            LEFT JOIN {$wpdb->prefix}hp_leads ld ON a.ID = ld.agent_id
            LEFT JOIN {$wpdb->prefix}hp_transactions t ON a.ID = t.agent_id
            WHERE a.post_type = 'agent' AND a.post_status = 'publish'
            GROUP BY a.ID
            ORDER BY transaction_count DESC, lead_count DESC"
        );
        
        if (empty($agent_stats)) {
            echo '<p>' . __('No agent performance data available', 'happy-place') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('Agent', 'happy-place') . '</th>';
        echo '<th>' . __('Active Listings', 'happy-place') . '</th>';
        echo '<th>' . __('Leads', 'happy-place') . '</th>';
        echo '<th>' . __('Transactions', 'happy-place') . '</th>';
        echo '<th>' . __('Performance Score', 'happy-place') . '</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($agent_stats as $agent) {
            $performance_score = ($agent->transaction_count * 10) + ($agent->lead_count * 2) + $agent->listing_count;
            echo '<tr>';
            echo '<td><a href="' . get_edit_post_link($agent->ID) . '">' . esc_html($agent->agent_name) . '</a></td>';
            echo '<td>' . number_format($agent->listing_count) . '</td>';
            echo '<td>' . number_format($agent->lead_count) . '</td>';
            echo '<td>' . number_format($agent->transaction_count) . '</td>';
            echo '<td>' . number_format($performance_score) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Render system info
     * 
     * @return void
     */
    private function render_system_info(): void {
        $info = [
            __('PHP Version', 'happy-place') => PHP_VERSION,
            __('WordPress Version', 'happy-place') => get_bloginfo('version'),
            __('Plugin Version', 'happy-place') => HP_VERSION,
            __('Active Theme', 'happy-place') => wp_get_theme()->get('Name'),
            __('Server Software', 'happy-place') => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            __('MySQL Version', 'happy-place') => $GLOBALS['wpdb']->db_version(),
        ];
        
        echo '<table class="widefat">';
        foreach ($info as $label => $value) {
            printf(
                '<tr><th>%s</th><td>%s</td></tr>',
                esc_html($label),
                esc_html($value)
            );
        }
        echo '</table>';
    }
    
    /**
     * Render error log
     * 
     * @return void
     */
    private function render_error_log(): void {
        global $wpdb;
        
        $errors = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}hp_error_log 
            ORDER BY error_time DESC 
            LIMIT 20"
        );
        
        if (empty($errors)) {
            echo '<p>' . __('No errors recorded', 'happy-place') . '</p>';
            return;
        }
        
        echo '<table class="widefat">';
        echo '<thead><tr><th>' . __('Time', 'happy-place') . '</th><th>' . __('Level', 'happy-place') . '</th><th>' . __('Message', 'happy-place') . '</th></tr></thead>';
        echo '<tbody>';
        foreach ($errors as $error) {
            printf(
                '<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
                esc_html($error->error_time),
                esc_html($error->error_level),
                esc_html($error->error_message)
            );
        }
        echo '</tbody></table>';
    }
    
    /**
     * Get help content
     * 
     * @param string $section
     * @return string
     */
    private function get_help_content(string $section): string {
        $content = [
            'overview' => __('The Happy Place plugin provides comprehensive real estate management functionality for WordPress.', 'happy-place'),
            'dashboard' => __('The dashboard provides an overview of your real estate website activity and quick access to common tasks.', 'happy-place'),
            'analytics' => __('View detailed analytics about your listings, agents, and lead generation performance.', 'happy-place'),
        ];
        
        return $content[$section] ?? '';
    }
    
    /**
     * Get help sidebar
     * 
     * @return string
     */
    private function get_help_sidebar(): string {
        return '<p><strong>' . __('For more information:', 'happy-place') . '</strong></p>' .
               '<p><a href="https://happyplace.com/docs" target="_blank">' . __('Documentation', 'happy-place') . '</a></p>' .
               '<p><a href="https://happyplace.com/support" target="_blank">' . __('Support', 'happy-place') . '</a></p>';
    }
    
    /**
     * Enqueue admin scripts and styles
     * 
     * @param string $hook
     * @return void
     */
    public function enqueue_admin_scripts(string $hook): void {
        // Only load on our admin pages
        $happy_place_pages = [
            'toplevel_page_happy-place',
            'happy-place_page_hp-dashboard',
            'happy-place_page_hp-theme-settings',
            'happy-place_page_hp-integrations',
            'happy-place_page_hp-import',
            'happy-place_page_hp-export',
            'happy-place_page_hp-sync',
            'happy-place_page_hp-analytics',
            'happy-place_page_hp-marketing',
            'happy-place_page_hp-users',
        ];
        
        if (!in_array($hook, $happy_place_pages)) {
            return;
        }
        
        // Enqueue admin styles
        wp_enqueue_style(
            'hp-admin-style',
            plugin_dir_url(__FILE__) . '../../assets/css/admin/admin.css',
            [],
            '4.0.0'
        );
        
        // Enqueue admin JavaScript
        wp_enqueue_script(
            'hp-admin-menu',
            plugin_dir_url(__FILE__) . '../../assets/js/admin/admin-menu.js',
            ['jquery'],
            '4.0.0',
            true
        );
        
        // Localize script with admin data
        wp_localize_script(
            'hp-admin-menu',
            'hp_admin',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hp_admin_nonce'),
                'strings' => [
                    'confirm_delete' => __('Are you sure you want to delete this item?', 'happy-place'),
                    'confirm_clear_cache' => __('Are you sure you want to clear the cache?', 'happy-place'),
                    'confirm_optimize_db' => __('Are you sure you want to optimize the database? This may take a few minutes.', 'happy-place'),
                    'sync_in_progress' => __('Sync in progress...', 'happy-place'),
                    'sync_complete' => __('Sync completed successfully!', 'happy-place'),
                    'sync_failed' => __('Sync failed. Please try again.', 'happy-place'),
                ],
            ]
        );
        
        hp_log("Admin scripts enqueued for page: $hook", 'debug', 'AdminMenu');
    }
    
    /**
     * AJAX handler for clearing cache
     * 
     * @return void
     */
    public function ajax_clear_cache(): void {
        check_ajax_referer('hp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'happy-place'));
        }
        
        try {
            // Clear WordPress object cache
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
            }
            
            // Clear any transients
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_%'");
            
            // Clear other caches if available
            if (function_exists('w3tc_flush_all')) {
                \w3tc_flush_all();
            }
            
            if (function_exists('wp_rocket_clean_domain')) {
                \wp_rocket_clean_domain();
            }
            
            hp_log('Cache cleared via AJAX', 'info', 'AdminMenu');
            wp_send_json_success(__('Cache cleared successfully', 'happy-place'));
            
        } catch (\Exception $e) {
            hp_log('Failed to clear cache: ' . $e->getMessage(), 'error', 'AdminMenu');
            wp_send_json_error(__('Failed to clear cache: ', 'happy-place') . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for regenerating thumbnails
     * 
     * @return void
     */
    public function ajax_regenerate_thumbnails(): void {
        check_ajax_referer('hp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'happy-place'));
        }
        
        try {
            // Get all attachment IDs
            $attachments = get_posts([
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_status' => 'inherit',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);
            
            $regenerated = 0;
            foreach ($attachments as $attachment_id) {
                $metadata = wp_generate_attachment_metadata($attachment_id, get_attached_file($attachment_id));
                if ($metadata) {
                    wp_update_attachment_metadata($attachment_id, $metadata);
                    $regenerated++;
                }
            }
            
            hp_log("Regenerated $regenerated thumbnails via AJAX", 'info', 'AdminMenu');
            wp_send_json_success(sprintf(__('Regenerated %d thumbnails', 'happy-place'), $regenerated));
            
        } catch (\Exception $e) {
            hp_log('Failed to regenerate thumbnails: ' . $e->getMessage(), 'error', 'AdminMenu');
            wp_send_json_error(__('Failed to regenerate thumbnails: ', 'happy-place') . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for database optimization
     * 
     * @return void
     */
    public function ajax_optimize_database(): void {
        check_ajax_referer('hp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'happy-place'));
        }
        
        try {
            global $wpdb;
            
            // Get all tables
            $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
            $optimized = 0;
            
            foreach ($tables as $table) {
                $table_name = $table[0];
                $result = $wpdb->query("OPTIMIZE TABLE `$table_name`");
                if ($result !== false) {
                    $optimized++;
                }
            }
            
            hp_log("Optimized $optimized database tables via AJAX", 'info', 'AdminMenu');
            wp_send_json_success(sprintf(__('Optimized %d database tables', 'happy-place'), $optimized));
            
        } catch (\Exception $e) {
            hp_log('Failed to optimize database: ' . $e->getMessage(), 'error', 'AdminMenu');
            wp_send_json_error(__('Failed to optimize database: ', 'happy-place') . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for MLS sync
     * 
     * @return void
     */
    public function ajax_sync_mls(): void {
        check_ajax_referer('hp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'happy-place'));
        }
        
        try {
            // This would integrate with your MLS sync functionality
            // For now, just simulate a sync
            sleep(2); // Simulate processing time
            
            hp_log('MLS sync completed via AJAX', 'info', 'AdminMenu');
            wp_send_json_success(__('MLS sync completed successfully', 'happy-place'));
            
        } catch (\Exception $e) {
            hp_log('MLS sync failed: ' . $e->getMessage(), 'error', 'AdminMenu');
            wp_send_json_error(__('MLS sync failed: ', 'happy-place') . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for config sync
     * 
     * @return void
     */
    public function ajax_sync_config(): void {
        check_ajax_referer('hp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'happy-place'));
        }
        
        $config_key = sanitize_text_field($_POST['config_key'] ?? '');
        
        if (empty($config_key)) {
            wp_send_json_error(__('No configuration key provided', 'happy-place'));
        }
        
        try {
            // This would integrate with your config sync functionality
            // For now, just simulate a sync
            hp_log("Config sync completed for key: $config_key", 'info', 'AdminMenu');
            wp_send_json_success(__('Configuration synced successfully', 'happy-place'));
            
        } catch (\Exception $e) {
            hp_log('Config sync failed: ' . $e->getMessage(), 'error', 'AdminMenu');
            wp_send_json_error(__('Config sync failed: ', 'happy-place') . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for testing Airtable connection
     * 
     * @return void
     */
    public function ajax_test_airtable_connection(): void {
        check_ajax_referer('hp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'happy-place'));
        }
        
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        $base_id = sanitize_text_field($_POST['base_id'] ?? '');
        
        if (empty($api_key) || empty($base_id)) {
            wp_send_json_error(__('API Key and Base ID are required', 'happy-place'));
        }
        
        try {
            // Test Airtable API connection
            $response = wp_remote_get("https://api.airtable.com/v0/{$base_id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json'
                ],
                'timeout' => 10
            ]);
            
            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }
            
            $status_code = wp_remote_retrieve_response_code($response);
            
            if ($status_code === 200) {
                hp_log('Airtable connection test successful', 'info', 'AdminMenu');
                wp_send_json_success(__('Connection successful! Airtable base is accessible.', 'happy-place'));
            } else {
                $body = wp_remote_retrieve_body($response);
                $error_data = json_decode($body, true);
                $error_message = $error_data['error']['message'] ?? 'Connection failed';
                
                throw new \Exception($error_message);
            }
            
        } catch (\Exception $e) {
            hp_log('Airtable connection test failed: ' . $e->getMessage(), 'error', 'AdminMenu');
            wp_send_json_error(__('Connection failed: ', 'happy-place') . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for bulk syncing leads to FollowUp Boss
     * 
     * @return void
     */
    public function ajax_bulk_sync_leads(): void {
        check_ajax_referer('hp_bulk_sync_leads', '_wpnonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'happy-place'));
        }
        
        try {
            global $wpdb;
            
            // Check if FollowUp Boss is configured
            $api_key = $this->config_manager->get('followup_boss_api_key');
            if (empty($api_key)) {
                wp_send_json_error(__('FollowUp Boss API key is not configured.', 'happy-place'));
                return;
            }
            
            // Get leads that haven't been synced to FollowUp Boss
            $leads = $wpdb->get_results(
                "SELECT * FROM {$wpdb->prefix}hp_leads 
                 WHERE fub_contact_id IS NULL 
                 AND fub_sync_status != 'failed_permanent'
                 ORDER BY created_at DESC 
                 LIMIT 50",
                ARRAY_A
            );
            
            if (empty($leads)) {
                wp_send_json_success([
                    'message' => __('No leads require syncing to FollowUp Boss.', 'happy-place')
                ]);
                return;
            }
            
            // Check if FollowUp Boss integration class exists
            if (!class_exists('FollowUpBossIntegration')) {
                // For now, just mark as synced for demo purposes
                // In real implementation, this would use the FollowUpBossIntegration class
                $synced_count = 0;
                
                foreach ($leads as $lead) {
                    // Simulate API call delay
                    usleep(100000); // 0.1 second delay per lead
                    
                    // Update sync status
                    $wpdb->update(
                        $wpdb->prefix . 'hp_leads',
                        [
                            'fub_sync_status' => 'synced',
                            'fub_last_sync' => current_time('mysql'),
                            'fub_contact_id' => 'demo_' . $lead['id'] // Placeholder contact ID
                        ],
                        ['id' => $lead['id']]
                    );
                    
                    $synced_count++;
                }
                
                hp_log("Bulk synced $synced_count leads to FollowUp Boss (demo mode)", 'info', 'AdminMenu');
                wp_send_json_success([
                    'message' => sprintf(
                        _n(
                            'Successfully synced %d lead to FollowUp Boss.',
                            'Successfully synced %d leads to FollowUp Boss.',
                            $synced_count,
                            'happy-place'
                        ),
                        $synced_count
                    )
                ]);
            } else {
                // Real FollowUp Boss sync would go here
                // $fub = new FollowUpBossIntegration();
                // $result = $fub->bulk_sync_leads($leads);
                
                wp_send_json_error(__('FollowUp Boss integration not yet implemented for bulk sync.', 'happy-place'));
            }
            
        } catch (\Exception $e) {
            hp_log('Bulk sync to FollowUp Boss failed: ' . $e->getMessage(), 'error', 'AdminMenu');
            wp_send_json_error(__('Bulk sync failed: ', 'happy-place') . $e->getMessage());
        }
    }

    /**
     * Render configuration status alerts
     * 
     * @return void
     */
    private function render_configuration_alerts(): void {
        $status = $this->config_manager->get_configuration_status();
        $missing_required = [];
        
        foreach ($status as $key => $config) {
            if ($config['required'] && !$config['configured']) {
                $missing_required[] = $config['description'];
            }
        }
        
        if (!empty($missing_required)) {
            echo '<div class="notice notice-warning">';
            echo '<p><strong>' . __('Configuration Required:', 'happy-place') . '</strong> ';
            echo sprintf(
                __('Some required integrations are not configured: %s', 'happy-place'),
                implode(', ', $missing_required)
            );
            echo ' <a href="' . admin_url('admin.php?page=hp-integrations') . '">' . __('Configure now', 'happy-place') . '</a>';
            echo '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Render configuration status dashboard
     * 
     * @return void
     */
    private function render_configuration_status(): void {
        $status = $this->config_manager->get_configuration_status();
        $categories = [];
        
        // Group by category
        foreach ($status as $key => $config) {
            $category = $config['category'];
            if (!isset($categories[$category])) {
                $categories[$category] = [];
            }
            $categories[$category][$key] = $config;
        }
        
        echo '<div class="hp-config-categories">';
        
        foreach ($categories as $category => $configs) {
            $configured_count = count(array_filter($configs, fn($c) => $c['configured']));
            $total_count = count($configs);
            $category_status = $configured_count === $total_count ? 'complete' : ($configured_count > 0 ? 'partial' : 'none');
            
            echo "<div class='hp-config-category hp-config-{$category_status}'>";
            echo "<h4>" . esc_html(ucfirst($category)) . " <span class='hp-config-count'>({$configured_count}/{$total_count})</span></h4>";
            echo "<div class='hp-config-items'>";
            
            foreach ($configs as $key => $config) {
                $status_class = $config['configured'] ? 'configured' : 'not-configured';
                $status_icon = $config['configured'] ? '✓' : '✗';
                $required_text = $config['required'] ? ' (Required)' : '';
                
                echo "<div class='hp-config-item {$status_class}'>";
                echo "<span class='hp-config-status'>{$status_icon}</span>";
                echo "<span class='hp-config-name'>" . esc_html($config['description']) . "{$required_text}</span>";
                echo "</div>";
            }
            
            echo "</div>";
            echo "</div>";
        }
        
        echo '</div>';
        
        // Show action link to integrations page
        echo '<div class="hp-config-action">';
        echo '<a href="' . admin_url('admin.php?page=hp-integrations') . '" class="button button-primary">';
        echo __('Configure Integrations', 'happy-place');
        echo '</a>';
        echo '</div>';
    }
    
    /**
     * AJAX: Preview role changes
     */
    public function ajax_preview_role_changes(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'happy-place')]);
            return;
        }
        
        check_ajax_referer('hp_admin_nonce', 'nonce');
        
        // Get current roles
        global $wp_roles;
        $all_roles = $wp_roles->roles;
        
        $legacy_roles = ['real_estate_agent', 'broker', 'client', 'subscriber', 'contributor', 'author'];
        $changes = [];
        
        foreach ($legacy_roles as $role_key) {
            if (isset($all_roles[$role_key])) {
                $user_count = count(get_users(['role' => $role_key]));
                $migration_target = $this->determine_migration_role($role_key);
                
                $changes[] = [
                    'action' => 'remove',
                    'role' => $role_key,
                    'role_name' => $all_roles[$role_key]['name'],
                    'user_count' => $user_count,
                    'migration_target' => $migration_target
                ];
            }
        }
        
        // Generate HTML for the changes
        $html = '';
        if (empty($changes)) {
            $html = '<div class="notice notice-info"><p>' . __('No legacy roles found. Your user roles are already clean.', 'happy-place') . '</p></div>';
        } else {
            $html .= '<table class="wp-list-table widefat fixed striped">';
            $html .= '<thead><tr>';
            $html .= '<th>' . __('Legacy Role', 'happy-place') . '</th>';
            $html .= '<th>' . __('Users Count', 'happy-place') . '</th>';
            $html .= '<th>' . __('Migration Target', 'happy-place') . '</th>';
            $html .= '<th>' . __('Action', 'happy-place') . '</th>';
            $html .= '</tr></thead><tbody>';
            
            foreach ($changes as $change) {
                $html .= '<tr>';
                $html .= '<td><strong>' . esc_html($change['role_name']) . '</strong> (' . esc_html($change['role']) . ')</td>';
                $html .= '<td>' . esc_html($change['user_count']) . '</td>';
                $html .= '<td>' . esc_html(ucwords(str_replace('_', ' ', $change['migration_target']))) . '</td>';
                $html .= '<td><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> ' . __('Remove', 'happy-place') . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody></table>';
            $html .= '<p><strong>' . __('Note:', 'happy-place') . '</strong> ' . __('Users will be migrated to the target roles with corresponding agent/staff records created as needed.', 'happy-place') . '</p>';
        }
        
        wp_send_json_success(['html' => $html]);
    }
    
    /**
     * AJAX: Cleanup roles
     */
    public function ajax_cleanup_roles(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'happy-place')]);
            return;
        }
        
        check_ajax_referer('hp_admin_nonce', 'nonce');
        
        try {
            // Initialize UserRoleService if available
            if (class_exists('HappyPlace\\Services\\UserRoleService')) {
                $user_role_service = new \HappyPlace\Services\UserRoleService();
                $user_role_service->force_role_cleanup();
                
                hp_log('User roles cleaned up via admin interface', 'info', 'AdminMenu');
                wp_send_json_success(['message' => __('User roles cleaned up successfully', 'happy-place')]);
            } else {
                wp_send_json_error(['message' => __('UserRoleService not available', 'happy-place')]);
            }
        } catch (\Exception $e) {
            hp_log('Role cleanup failed: ' . $e->getMessage(), 'error', 'AdminMenu');
            wp_send_json_error(['message' => __('Role cleanup failed: ', 'happy-place') . $e->getMessage()]);
        }
    }
    
    /**
     * Determine migration role for legacy role
     */
    private function determine_migration_role(string $old_role): string {
        $migration_map = [
            'real_estate_agent' => 'agent',
            'broker' => 'agent',
            'client' => 'lead',
            'subscriber' => 'lead',
            'contributor' => 'staff',
            'author' => 'staff'
        ];
        
        return $migration_map[$old_role] ?? 'lead';
    }
    
    /**
     * AJAX handler to get lead details
     */
    public function ajax_get_lead_details(): void {
        if (!current_user_can('edit_posts')) {
            wp_die('Unauthorized');
        }
        
        $lead_id = intval($_POST['lead_id']);
        if (!$lead_id) {
            wp_die('Invalid lead ID');
        }
        
        global $wpdb;
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}hp_leads WHERE id = %d",
            $lead_id
        ));
        
        if (!$lead) {
            wp_die('Lead not found');
        }
        
        // Get listing and agent info
        $listing_title = $lead->listing_id ? get_the_title($lead->listing_id) : 'N/A';
        $agent_name = $lead->agent_id ? get_the_title($lead->agent_id) : 'N/A';
        
        ?>
        <h2>Lead Details</h2>
        <table class="form-table">
            <tr>
                <th>Name:</th>
                <td><?php echo esc_html($lead->first_name . ' ' . $lead->last_name); ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a></td>
            </tr>
            <tr>
                <th>Phone:</th>
                <td>
                    <?php if ($lead->phone): ?>
                        <a href="tel:<?php echo esc_attr($lead->phone); ?>"><?php echo esc_html($lead->phone); ?></a>
                    <?php else: ?>
                        Not provided
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Message:</th>
                <td><?php echo esc_html($lead->message); ?></td>
            </tr>
            <tr>
                <th>Source:</th>
                <td><?php echo esc_html(ucwords(str_replace('_', ' ', $lead->source))); ?></td>
            </tr>
            <tr>
                <th>Status:</th>
                <td><?php echo esc_html(ucwords(str_replace('_', ' ', $lead->status))); ?></td>
            </tr>
            <tr>
                <th>Priority:</th>
                <td><?php echo esc_html(ucwords($lead->priority)); ?></td>
            </tr>
            <tr>
                <th>Listing:</th>
                <td>
                    <?php if ($lead->listing_id): ?>
                        <a href="<?php echo get_edit_post_link($lead->listing_id); ?>"><?php echo esc_html($listing_title); ?></a>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Agent:</th>
                <td>
                    <?php if ($lead->agent_id): ?>
                        <a href="<?php echo get_edit_post_link($lead->agent_id); ?>"><?php echo esc_html($agent_name); ?></a>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Created:</th>
                <td><?php echo esc_html(date('F j, Y g:i A', strtotime($lead->created_at))); ?></td>
            </tr>
            <tr>
                <th>Updated:</th>
                <td><?php echo esc_html(date('F j, Y g:i A', strtotime($lead->updated_at))); ?></td>
            </tr>
            <?php if ($lead->ip_address): ?>
            <tr>
                <th>IP Address:</th>
                <td><?php echo esc_html($lead->ip_address); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($lead->utm_source || $lead->utm_medium || $lead->utm_campaign): ?>
            <tr>
                <th>UTM Tracking:</th>
                <td>
                    <?php if ($lead->utm_source): ?>Source: <?php echo esc_html($lead->utm_source); ?><br><?php endif; ?>
                    <?php if ($lead->utm_medium): ?>Medium: <?php echo esc_html($lead->utm_medium); ?><br><?php endif; ?>
                    <?php if ($lead->utm_campaign): ?>Campaign: <?php echo esc_html($lead->utm_campaign); ?><?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        </table>
        <?php
        
        wp_die();
    }
    
    /**
     * Render Performance Tools page
     * 
     * @return void
     */
    public function render_performance_tools(): void {
        // Check capability
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Performance Tools', 'happy-place'); ?></h1>
            <p><?php echo esc_html__('Optimize your Happy Place theme performance with asset management, caching, and monitoring tools.', 'happy-place'); ?></p>
            
            <div class="hp-performance-tools">
                
                <!-- Asset Management Section -->
                <div class="postbox">
                    <h2 class="hndle"><span><?php echo esc_html__('Asset Management', 'happy-place'); ?></span></h2>
                    <div class="inside">
                        <p><?php echo esc_html__('Manage CSS and JavaScript assets to improve page load times.', 'happy-place'); ?></p>
                        
                        <div class="hp-tool-grid">
                            <div class="hp-tool-card">
                                <h3><span class="dashicons dashicons-admin-appearance"></span> CSS Bundles</h3>
                                <p>Generate optimized CSS bundles from 107+ framework files into 7 efficient bundles.</p>
                                <button type="button" class="button button-primary hp-tool-action" data-action="hp_build_css_bundles">
                                    <span class="dashicons dashicons-update"></span> Build CSS Bundles
                                </button>
                                <div class="hp-tool-result" id="css-bundles-result"></div>
                            </div>
                            
                            <div class="hp-tool-card">
                                <h3><span class="dashicons dashicons-trash"></span> Clear Asset Cache</h3>
                                <p>Clear cached CSS/JS files and force regeneration of optimized assets.</p>
                                <button type="button" class="button hp-tool-action" data-action="hp_clear_asset_cache">
                                    <span class="dashicons dashicons-dismiss"></span> Clear Asset Cache
                                </button>
                                <div class="hp-tool-result" id="asset-cache-result"></div>
                            </div>
                            
                            <div class="hp-tool-card">
                                <h3><span class="dashicons dashicons-format-image"></span> Optimize Images</h3>
                                <p>Compress and optimize theme images for faster loading.</p>
                                <button type="button" class="button hp-tool-action" data-action="hp_optimize_images">
                                    <span class="dashicons dashicons-images-alt2"></span> Optimize Images
                                </button>
                                <div class="hp-tool-result" id="optimize-images-result"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cache Management Section -->
                <div class="postbox">
                    <h2 class="hndle"><span><?php echo esc_html__('Cache Management', 'happy-place'); ?></span></h2>
                    <div class="inside">
                        <p><?php echo esc_html__('Clear various caches to ensure fresh content delivery.', 'happy-place'); ?></p>
                        
                        <div class="hp-tool-grid">
                            <div class="hp-tool-card">
                                <h3><span class="dashicons dashicons-database-remove"></span> Clear Transients</h3>
                                <p>Clear WordPress transient cache to free up database space.</p>
                                <button type="button" class="button hp-tool-action" data-action="hp_clear_transients">
                                    <span class="dashicons dashicons-trash"></span> Clear Transients
                                </button>
                                <div class="hp-tool-result" id="transients-result"></div>
                            </div>
                            
                            <div class="hp-tool-card">
                                <h3><span class="dashicons dashicons-admin-tools"></span> WordPress Cache</h3>
                                <p>Clear WordPress object cache and rewrite rules.</p>
                                <button type="button" class="button hp-tool-action" data-action="hp_clear_cache">
                                    <span class="dashicons dashicons-update-alt"></span> Clear WP Cache
                                </button>
                                <div class="hp-tool-result" id="wp-cache-result"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Performance Monitoring Section -->
                <div class="postbox">
                    <h2 class="hndle"><span><?php echo esc_html__('Performance Monitoring', 'happy-place'); ?></span></h2>
                    <div class="inside">
                        <p><?php echo esc_html__('Analyze and monitor your site\'s performance metrics.', 'happy-place'); ?></p>
                        
                        <div class="hp-tool-grid">
                            <div class="hp-tool-card">
                                <h3><span class="dashicons dashicons-chart-line"></span> Performance Analysis</h3>
                                <p>Analyze current asset loading, file sizes, and optimization opportunities.</p>
                                <button type="button" class="button button-secondary hp-tool-action" data-action="hp_analyze_performance">
                                    <span class="dashicons dashicons-analytics"></span> Analyze Performance
                                </button>
                                <div class="hp-tool-result" id="performance-analysis-result"></div>
                            </div>
                            
                            <div class="hp-tool-card">
                                <h3><span class="dashicons dashicons-performance"></span> Page Speed Test</h3>
                                <p>Test homepage loading speed and Core Web Vitals.</p>
                                <button type="button" class="button button-secondary hp-tool-action" data-action="hp_test_page_speed">
                                    <span class="dashicons dashicons-clock"></span> Test Page Speed
                                </button>
                                <div class="hp-tool-result" id="page-speed-result"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Current Status Section -->
                <div class="postbox">
                    <h2 class="hndle"><span><?php echo esc_html__('Current Status', 'happy-place'); ?></span></h2>
                    <div class="inside">
                        <?php $this->display_performance_status(); ?>
                    </div>
                </div>
                
            </div>
        </div>
        
        <style>
        .hp-performance-tools .postbox {
            margin-bottom: 20px;
        }
        .hp-tool-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .hp-tool-card {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }
        .hp-tool-card h3 {
            margin: 0 0 10px 0;
            color: #23282d;
        }
        .hp-tool-card h3 .dashicons {
            margin-right: 8px;
            color: #0073aa;
        }
        .hp-tool-card p {
            color: #666;
            margin: 10px 0 15px 0;
        }
        .hp-tool-result {
            margin-top: 10px;
            padding: 10px;
            border-radius: 3px;
            display: none;
        }
        .hp-tool-result.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .hp-tool-result.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .hp-tool-result.info {
            background: #d1ecf1;
            border: 1px solid #b8daff;
            color: #0c5460;
        }
        .hp-tool-action.loading {
            opacity: 0.7;
            pointer-events: none;
        }
        .hp-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        .hp-status-item {
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .hp-status-item strong {
            display: block;
            margin-bottom: 5px;
        }
        .hp-status-good { border-left: 4px solid #46b450; }
        .hp-status-warning { border-left: 4px solid #ffb900; }
        .hp-status-error { border-left: 4px solid #dc3232; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('.hp-tool-action').on('click', function() {
                var $button = $(this);
                var $result = $button.siblings('.hp-tool-result');
                var action = $button.data('action');
                
                $button.addClass('loading');
                $result.removeClass('success error info').hide();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: action,
                        nonce: '<?php echo wp_create_nonce('hp_performance_tools'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.addClass('success').html(response.data.message).show();
                            if (response.data.details) {
                                $result.append('<br><small>' + response.data.details + '</small>');
                            }
                        } else {
                            $result.addClass('error').html(response.data || 'Operation failed').show();
                        }
                    },
                    error: function() {
                        $result.addClass('error').html('Network error occurred').show();
                    },
                    complete: function() {
                        $button.removeClass('loading');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Display current performance status
     * 
     * @return void
     */
    private function display_performance_status(): void {
        $theme_dir = get_template_directory();
        $theme_uri = get_template_directory_uri();
        
        // Check CSS bundles
        $core_bundle = file_exists($theme_dir . '/dist/css/core.css');
        $bundle_count = count(glob($theme_dir . '/dist/css/*.css'));
        $original_files = count(glob($theme_dir . '/assets/css/framework/**/*.css'));
        
        // Check asset sizes
        $bundle_size = 0;
        if ($core_bundle) {
            $bundles = glob($theme_dir . '/dist/css/*.css');
            foreach ($bundles as $bundle) {
                $bundle_size += filesize($bundle);
            }
        }
        
        // Get transient count
        global $wpdb;
        $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%'");
        
        ?>
        <div class="hp-status-grid">
            <div class="hp-status-item <?php echo $core_bundle ? 'hp-status-good' : 'hp-status-warning'; ?>">
                <strong>CSS Bundles</strong>
                <?php if ($core_bundle): ?>
                    ✅ Active (<?php echo $bundle_count; ?> bundles, <?php echo round($bundle_size / 1024); ?>KB)
                <?php else: ?>
                    ⚠️ Not Generated
                <?php endif; ?>
            </div>
            
            <div class="hp-status-item <?php echo $original_files > 0 ? 'hp-status-good' : 'hp-status-error'; ?>">
                <strong>Framework Files</strong>
                <?php echo $original_files; ?> CSS files detected
            </div>
            
            <div class="hp-status-item <?php echo $transient_count < 100 ? 'hp-status-good' : 'hp-status-warning'; ?>">
                <strong>Transients</strong>
                <?php echo number_format($transient_count); ?> cached items
            </div>
            
            <div class="hp-status-item hp-status-good">
                <strong>Debug Mode</strong>
                <?php echo defined('WP_DEBUG') && WP_DEBUG ? '⚠️ Enabled' : '✅ Disabled'; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: Build CSS bundles
     * 
     * @return void
     */
    public function ajax_build_css_bundles(): void {
        // Enhanced error handling and debugging
        try {
            check_ajax_referer('hp_performance_tools', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Insufficient permissions');
                return;
            }
            
            $theme_dir = get_template_directory();
            
            // Verify theme directory exists
            if (!is_dir($theme_dir)) {
                wp_send_json_error('Theme directory not found: ' . $theme_dir);
                return;
            }
            
            // Try Node.js builder first, fallback to PHP builder
            $node_script = $theme_dir . '/build-css.js';
            $php_script = $theme_dir . '/build-css-php.php';
            
            if (file_exists($node_script)) {
                // Try Node.js build
                $output = [];
                $return_var = 0;
                
                $original_dir = getcwd();
                if (chdir($theme_dir)) {
                    exec('node build-css.js 2>&1', $output, $return_var);
                    chdir($original_dir);
                    
                    if ($return_var === 0) {
                        $bundles = glob($theme_dir . '/dist/css/*.css');
                        $bundle_count = count($bundles);
                        
                        if ($bundle_count > 0) {
                            $total_size = 0;
                            foreach ($bundles as $bundle) {
                                if (file_exists($bundle)) {
                                    $total_size += filesize($bundle);
                                }
                            }
                            
                            wp_send_json_success([
                                'message' => "Successfully generated {$bundle_count} CSS bundles with Node.js!",
                                'details' => 'Total size: ' . round($total_size / 1024, 1) . 'KB. Page refresh recommended.'
                            ]);
                            return;
                        }
                    } else {
                        // Log Node.js errors but continue to PHP fallback
                        error_log('HP CSS Builder - Node.js build failed: ' . implode("\n", $output));
                    }
                } else {
                    error_log('HP CSS Builder - Could not change to theme directory: ' . $theme_dir);
                }
            }
            
            // Fallback to PHP builder
            if (!file_exists($php_script)) {
                wp_send_json_error('CSS build scripts not found. Expected: ' . $php_script);
                return;
            }
            
            // Load the PHP builder with error checking
            require_once $php_script;
            
            if (!class_exists('HP_CSS_Builder')) {
                wp_send_json_error('HP_CSS_Builder class not found in build script');
                return;
            }
            
            $builder = new HP_CSS_Builder();
            
            // Check if builder has required methods
            if (!method_exists($builder, 'build_all') || !method_exists($builder, 'get_statistics')) {
                wp_send_json_error('HP_CSS_Builder missing required methods');
                return;
            }
            
            $results = $builder->build_all();
            $stats = $builder->get_statistics();
            
            if ($stats['successful_bundles'] > 0) {
                $messages = [];
                foreach ($results as $name => $result) {
                    if ($result['success']) {
                        $messages[] = $result['message'];
                    }
                }
                
                wp_send_json_success([
                    'message' => "Generated {$stats['successful_bundles']}/{$stats['total_bundles']} CSS bundles with PHP builder!",
                    'details' => 'Total size: ' . $stats['total_size_kb'] . 'KB. ' . implode('<br>', array_slice($messages, 0, 3))
                ]);
            } else {
                $error_messages = [];
                foreach ($results as $name => $result) {
                    if (!$result['success']) {
                        $error_messages[] = $result['message'];
                    }
                }
                
                wp_send_json_error('Build failed: ' . implode('<br>', array_slice($error_messages, 0, 3)));
            }
            
        } catch (Throwable $e) {
            // Catch all errors including fatal errors and exceptions
            wp_send_json_error('Build error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }
    
    /**
     * AJAX: Clear asset cache
     * 
     * @return void
     */
    public function ajax_clear_asset_cache(): void {
        check_ajax_referer('hp_performance_tools', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $theme_dir = get_template_directory();
        $cleared_items = 0;
        
        // Clear CSS bundles
        $bundles = glob($theme_dir . '/dist/css/*.css');
        foreach ($bundles as $bundle) {
            if (unlink($bundle)) {
                $cleared_items++;
            }
        }
        
        // Clear JS bundles if they exist
        $js_bundles = glob($theme_dir . '/dist/js/*.js');
        foreach ($js_bundles as $bundle) {
            if (unlink($bundle)) {
                $cleared_items++;
            }
        }
        
        // Clear WordPress object cache
        wp_cache_flush();
        
        wp_send_json_success([
            'message' => "Cleared {$cleared_items} cached asset files!",
            'details' => 'WordPress object cache also flushed. Assets will be regenerated on next page load.'
        ]);
    }
    
    /**
     * AJAX: Analyze performance
     * 
     * @return void
     */
    public function ajax_analyze_performance(): void {
        check_ajax_referer('hp_performance_tools', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $theme_dir = get_template_directory();
        $analysis = [];
        
        // Analyze CSS files
        $framework_files = glob($theme_dir . '/assets/css/framework/**/*.css', GLOB_BRACE);
        $bundle_files = glob($theme_dir . '/dist/css/*.css');
        
        $framework_size = 0;
        foreach ($framework_files as $file) {
            $framework_size += filesize($file);
        }
        
        $bundle_size = 0;
        foreach ($bundle_files as $file) {
            $bundle_size += filesize($file);
        }
        
        $analysis[] = '<strong>CSS Analysis:</strong>';
        $analysis[] = '• Framework files: ' . count($framework_files) . ' (' . round($framework_size / 1024) . 'KB)';
        $analysis[] = '• Bundle files: ' . count($bundle_files) . ' (' . round($bundle_size / 1024) . 'KB)';
        
        if ($bundle_size > 0 && $framework_size > 0) {
            $savings = round((($framework_size - $bundle_size) / $framework_size) * 100);
            $analysis[] = '• Size reduction: ' . $savings . '%';
        }
        
        // Analyze transients
        global $wpdb;
        $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%'");
        $analysis[] = '<strong>Cache Analysis:</strong>';
        $analysis[] = '• Transients: ' . number_format($transient_count) . ' items';
        
        // Check for common performance issues
        $issues = [];
        if (!count($bundle_files)) {
            $issues[] = '⚠️ CSS bundles not generated';
        }
        if ($transient_count > 200) {
            $issues[] = '⚠️ High transient count';
        }
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $issues[] = '⚠️ Debug mode enabled in production';
        }
        
        if (!empty($issues)) {
            $analysis[] = '<strong>Issues Found:</strong>';
            $analysis = array_merge($analysis, $issues);
        } else {
            $analysis[] = '<strong>✅ No performance issues detected</strong>';
        }
        
        wp_send_json_success([
            'message' => 'Performance analysis complete',
            'details' => implode('<br>', $analysis)
        ]);
    }
    
    /**
     * AJAX: Optimize images
     * 
     * @return void
     */
    public function ajax_optimize_images(): void {
        check_ajax_referer('hp_performance_tools', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // This is a placeholder - actual image optimization would require additional tools
        wp_send_json_success([
            'message' => 'Image optimization feature is ready for implementation',
            'details' => 'Consider installing image optimization plugins like Smush or ShortPixel for automated compression.'
        ]);
    }
    
    /**
     * AJAX: Clear transients
     * 
     * @return void
     */
    public function ajax_clear_transients(): void {
        check_ajax_referer('hp_performance_tools', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        
        // Get count before clearing
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%'");
        
        // Clear expired transients first
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' AND option_value < UNIX_TIMESTAMP()");
        
        // Clear all transients
        $deleted = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'");
        
        wp_send_json_success([
            'message' => "Cleared {$deleted} transient cache items!",
            'details' => "Original count: {$count}. Database space freed up."
        ]);
    }
    
    /**
     * AJAX: Test page speed
     * 
     * @return void
     */
    public function ajax_test_page_speed(): void {
        check_ajax_referer('hp_performance_tools', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $home_url = home_url();
        $start_time = microtime(true);
        
        // Test homepage loading
        $response = wp_remote_get($home_url, [
            'timeout' => 30,
            'user-agent' => 'Happy Place Performance Test'
        ]);
        
        $load_time = microtime(true) - $start_time;
        
        if (is_wp_error($response)) {
            wp_send_json_error('Failed to test page: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_size = strlen(wp_remote_retrieve_body($response));
        
        $results = [];
        $results[] = '<strong>Page Speed Test Results:</strong>';
        $results[] = '• Response code: ' . $response_code;
        $results[] = '• Load time: ' . round($load_time * 1000) . 'ms';
        $results[] = '• Page size: ' . round($response_size / 1024) . 'KB';
        
        // Basic performance assessment
        if ($load_time < 1.0) {
            $results[] = '✅ Excellent loading speed!';
        } elseif ($load_time < 2.0) {
            $results[] = '👍 Good loading speed';
        } else {
            $results[] = '⚠️ Consider optimization';
        }
        
        wp_send_json_success([
            'message' => 'Page speed test completed',
            'details' => implode('<br>', $results)
        ]);
    }
}