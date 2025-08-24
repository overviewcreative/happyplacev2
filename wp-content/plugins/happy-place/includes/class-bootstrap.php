<?php
/**
 * Bootstrap Class - Fixed Timing Issues
 * 
 * @package HappyPlace
 * @version 4.0.0
 */

namespace HappyPlace;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Bootstrap Class
 */
class Bootstrap {
    
    /**
     * Initialize the plugin
     */
    public static function init(): void {
        error_log('Happy Place: Bootstrap::init() called');
        
        // Set up autoloader FIRST
        self::setup_autoloader();
        
        // Load helper functions
        self::load_helpers();
        
        // Set up hooks (don't initialize components yet!)
        self::setup_hooks();
        
        error_log('Happy Place: Bootstrap setup complete');
    }
    
    /**
     * Set up autoloader
     */
    private static function setup_autoloader(): void {
        spl_autoload_register(function ($class) {
            // Only handle our namespace
            if (strpos($class, 'HappyPlace\\') !== 0) {
                return;
            }
            
            // Remove namespace prefix
            $class = str_replace('HappyPlace\\', '', $class);
            
            // Convert namespace separators to directory separators
            $parts = explode('\\', $class);
            
            // Get the actual class name (last part)
            $class_name = array_pop($parts);
            
            // Convert class name to file name
            // PostTypes -> class-post-types.php
            $file_name = 'class-' . strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $class_name)) . '.php';
            
            // Build the directory path
            $dir_path = '';
            if (!empty($parts)) {
                $dir_path = strtolower(implode('/', $parts)) . '/';
            }
            
            // Full file path
            $file = HP_INCLUDES_DIR . $dir_path . $file_name;
            
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
    
    /**
     * Load helper functions
     */
    private static function load_helpers(): void {
        $helpers_file = HP_INCLUDES_DIR . 'helpers.php';
        if (file_exists($helpers_file)) {
            require_once $helpers_file;
        }
    }
    
    /**
     * Set up WordPress hooks
     */
    private static function setup_hooks(): void {
        // Initialize core components AFTER WordPress is ready
        add_action('init', [__CLASS__, 'init_core'], 0);
        
        // Load text domain at the right time
        add_action('init', [__CLASS__, 'load_textdomain'], 1);
        
        // Activation/Deactivation
        register_activation_hook(HP_PLUGIN_FILE, [__CLASS__, 'activate']);
        register_deactivation_hook(HP_PLUGIN_FILE, [__CLASS__, 'deactivate']);
        
        // Admin notices
        add_action('admin_notices', [__CLASS__, 'admin_notices']);
        
        // Maybe flush rewrite rules
        add_action('init', [__CLASS__, 'maybe_flush_rewrite_rules'], 999);
        
        error_log('Happy Place: Hooks registered');
    }
    
    /**
     * Initialize core components (called on 'init' action)
     */
    public static function init_core(): void {
        error_log('Happy Place: init_core() called on init action');
        
        // Initialize Post Types
        if (class_exists('HappyPlace\\Core\\PostTypes')) {
            $post_types = \HappyPlace\Core\PostTypes::get_instance();
            $post_types->init();
            error_log('Happy Place: Post types initialized');
        }
        
        // Initialize Taxonomies
        if (class_exists('HappyPlace\\Core\\Taxonomies')) {
            $taxonomies = \HappyPlace\Core\Taxonomies::get_instance();
            $taxonomies->init();
            error_log('Happy Place: Taxonomies initialized');
        }
        
        // Initialize Core Services
        self::init_services();
        
        // Initialize Admin Menu
        if (is_admin() && class_exists('HappyPlace\\Admin\\AdminMenu')) {
            add_action('admin_menu', function() {
                $admin_menu = \HappyPlace\Admin\AdminMenu::get_instance();
                $admin_menu->init();
                error_log('Happy Place: Admin menu initialized');
            });
        }
        
        // Initialize Admin Service Test (only in debug mode)
        if (is_admin() && HP_DEBUG && class_exists('HappyPlace\\Admin\\AdminServiceTest')) {
            $service_test = new \HappyPlace\Admin\AdminServiceTest();
            $service_test->init();
            error_log('Happy Place: Admin service test initialized');
        }
        
        // Initialize Form Handlers
        self::init_form_handlers();
        
        // Initialize Dashboard Bridge (if theme is active)
        self::init_dashboard_bridge();
        
        // Mark services as initialized
        update_option('hp_services_initialized', true);
    }
    
    /**
     * Initialize dashboard bridge integration
     */
    private static function init_dashboard_bridge(): void {
        // Only initialize if theme functions indicate dashboard usage
        if (function_exists('hpt_get_dashboard_stats') || 
            (defined('HPH_DASHBOARD_LOADING') && HPH_DASHBOARD_LOADING)) {
            
            if (class_exists('HappyPlace\\Integrations\\DashboardBridge')) {
                $dashboard_bridge = \HappyPlace\Integrations\DashboardBridge::get_instance();
                error_log('Happy Place: Dashboard bridge initialized');
            }
        }
    }
    
    /**
     * Initialize core services
     */
    private static function init_services(): void {
        // Initialize Listing Service
        if (class_exists('HappyPlace\\Services\\ListingService')) {
            $listing_service = new \HappyPlace\Services\ListingService();
            $listing_service->init();
            error_log('Happy Place: Listing service initialized');
        }
        
        // Initialize Form Service
        if (class_exists('HappyPlace\\Services\\FormService')) {
            $form_service = new \HappyPlace\Services\FormService();
            $form_service->init();
            error_log('Happy Place: Form service initialized');
        }
        
        // Initialize Import Service
        if (class_exists('HappyPlace\\Services\\ImportService')) {
            $import_service = new \HappyPlace\Services\ImportService();
            $import_service->init();
            error_log('Happy Place: Import service initialized');
        }
    }
    
    /**
     * Initialize form handlers
     */
    private static function init_form_handlers(): void {
        // Initialize Listing Form Handler
        if (class_exists('HappyPlace\\Forms\\ListingFormHandler')) {
            $form_handler = new \HappyPlace\Forms\ListingFormHandler();
            error_log('Happy Place: Listing form handler initialized');
        }
    }
    
    /**
     * Load text domain
     */
    public static function load_textdomain(): void {
        load_plugin_textdomain(
            'happy-place',
            false,
            dirname(plugin_basename(HP_PLUGIN_FILE)) . '/languages'
        );
    }
    
    /**
     * Maybe flush rewrite rules
     */
    public static function maybe_flush_rewrite_rules(): void {
        if (get_option('hp_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('hp_flush_rewrite_rules');
            error_log('Happy Place: Rewrite rules flushed');
        }
    }
    
    /**
     * Admin notices
     */
    public static function admin_notices(): void {
        // Check if post types are registered
        $post_types_registered = post_type_exists('listing');
        $taxonomies_registered = taxonomy_exists('property_type');
        
        if (HP_DEBUG) {
            $status = $post_types_registered ? '✅ Post Types Registered' : '❌ Post Types NOT Registered';
            $tax_status = $taxonomies_registered ? '✅ Taxonomies Registered' : '❌ Taxonomies NOT Registered';
            
            ?>
            <div class="notice notice-info is-dismissible">
                <p><strong>Happy Place Plugin Status:</strong></p>
                <ul style="margin: 0.5em 0 0.5em 2em;">
                    <li><?php echo $status; ?></li>
                    <li><?php echo $tax_status; ?></li>
                </ul>
            </div>
            <?php
        }
    }
    
    /**
     * Plugin activation
     */
    public static function activate(): void {
        error_log('Happy Place: Plugin activated');
        
        // Set a flag to flush rewrite rules
        update_option('hp_flush_rewrite_rules', true);
        
        // Create database tables
        self::create_tables();
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate(): void {
        error_log('Happy Place: Plugin deactivated');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables
     */
    private static function create_tables(): void {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create a simple test table
        $table_name = $wpdb->prefix . 'hp_test';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            test_field varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        error_log('Happy Place: Test table created');
    }
}