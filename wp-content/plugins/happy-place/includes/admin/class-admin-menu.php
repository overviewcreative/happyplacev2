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
            'dashboard' => [
                'title' => __('Happy Place Dashboard', 'happy-place'),
                'menu_title' => __('Happy Place', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'happy-place',
                'callback' => [$this, 'render_dashboard'],
                'icon' => 'dashicons-admin-multisite',
                'position' => 4,
            ],
            'analytics' => [
                'parent' => 'happy-place',
                'title' => __('Analytics', 'happy-place'),
                'menu_title' => __('Analytics', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-analytics',
                'callback' => [$this, 'render_analytics'],
            ],
            // Leads menu moved to Lead Service class
            'import' => [
                'parent' => 'happy-place',
                'title' => __('Import/Export', 'happy-place'),
                'menu_title' => __('Import/Export', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-import-export',
                'callback' => [$this, 'render_import_export'],
            ],
            'config-sync' => [
                'parent' => 'happy-place',
                'title' => __('Configuration Sync', 'happy-place'),
                'menu_title' => __('Config Sync', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-config-sync',
                'callback' => [$this, 'render_config_sync'],
            ],
            'tools' => [
                'parent' => 'happy-place',
                'title' => __('Tools', 'happy-place'),
                'menu_title' => __('Tools', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-tools',
                'callback' => [$this, 'render_tools'],
            ],
            'system' => [
                'parent' => 'happy-place',
                'title' => __('System Status', 'happy-place'),
                'menu_title' => __('System Status', 'happy-place'),
                'capability' => 'manage_options',
                'slug' => 'hp-system',
                'callback' => [$this, 'render_system_status'],
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
            
            <div class="hp-dashboard-widgets">
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
                        <a href="<?php echo admin_url('admin.php?page=hp-import-export'); ?>" class="button">
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
            
            <div class="hp-analytics-tables">
                <h2><?php _e('Top Performing Listings', 'happy-place'); ?></h2>
                <?php $this->render_top_listings_table(); ?>
            </div>
        </div>
        <?php
    }
    
    // render_leads method removed - now handled by Lead Service
    
    /**
     * Render import/export page
     * 
     * @return void
     */
    public function render_import_export(): void {
        ?>
        <div class="wrap hp-admin-import-export">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="hp-import-section">
                <h2><?php _e('Import Listings', 'happy-place'); ?></h2>
                <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('hp_import_listings'); ?>
                    <input type="hidden" name="action" value="hp_import_listings">
                    
                    <p>
                        <label for="import-file"><?php _e('Select CSV file:', 'happy-place'); ?></label>
                        <input type="file" name="import_file" id="import-file" accept=".csv">
                    </p>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php _e('Import Listings', 'happy-place'); ?>
                        </button>
                    </p>
                </form>
            </div>
            
            <div class="hp-export-section">
                <h2><?php _e('Export Data', 'happy-place'); ?></h2>
                <p>
                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=hp_export_listings'), 'hp_export'); ?>" class="button">
                        <?php _e('Export Listings', 'happy-place'); ?>
                    </a>
                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=hp_export_leads'), 'hp_export'); ?>" class="button">
                        <?php _e('Export Leads', 'happy-place'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render config sync page
     * 
     * @return void
     */
    public function render_config_sync(): void {
        $sync_manager = \HappyPlace\Core\Config_Sync_Manager::get_instance();
        $sync_status = $sync_manager->get_sync_status();
        
        ?>
        <div class="wrap hp-admin-config-sync">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if (isset($_GET['imported'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Configuration imported successfully!', 'happy-place'); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="hp-sync-status">
                <h2><?php _e('Sync Status', 'happy-place'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Configuration', 'happy-place'); ?></th>
                            <th><?php _e('Files', 'happy-place'); ?></th>
                            <th><?php _e('Database', 'happy-place'); ?></th>
                            <th><?php _e('ACF', 'happy-place'); ?></th>
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
                                <td><?php echo $status['in_acf'] ? '✓' : '✗'; ?></td>
                                <td>
                                    <?php if ($status['synced']): ?>
                                        <span class="hp-status-badge hp-status-synced"><?php _e('Synced', 'happy-place'); ?></span>
                                    <?php else: ?>
                                        <span class="hp-status-badge hp-status-conflict"><?php _e('Conflict', 'happy-place'); ?></span>
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
        <?php
    }
    
    /**
     * Render tools page
     * 
     * @return void
     */
    public function render_tools(): void {
        ?>
        <div class="wrap hp-admin-tools">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
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
                    <h3><?php _e('Sync MLS Data', 'happy-place'); ?></h3>
                    <p><?php _e('Manually trigger MLS data synchronization.', 'happy-place'); ?></p>
                    <button class="button" id="hp-sync-mls"><?php _e('Sync Now', 'happy-place'); ?></button>
                </div>
                
                <div class="hp-tool">
                    <h3><?php _e('Database Optimization', 'happy-place'); ?></h3>
                    <p><?php _e('Optimize database tables for better performance.', 'happy-place'); ?></p>
                    <button class="button" id="hp-optimize-db"><?php _e('Optimize', 'happy-place'); ?></button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render system status page
     * 
     * @return void
     */
    public function render_system_status(): void {
        ?>
        <div class="wrap hp-admin-system">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="hp-system-info">
                <h2><?php _e('System Information', 'happy-place'); ?></h2>
                <?php $this->render_system_info(); ?>
            </div>
            
            <div class="hp-error-log">
                <h2><?php _e('Recent Errors', 'happy-place'); ?></h2>
                <?php $this->render_error_log(); ?>
            </div>
        </div>
        <?php
    }
    
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
        // Implementation for top listings table
        echo '<p>' . __('Top listings data will be displayed here', 'happy-place') . '</p>';
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
}