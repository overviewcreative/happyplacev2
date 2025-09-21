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
        // Set up autoloader FIRST
        self::setup_autoloader();

        // Load helper functions
        self::load_helpers();

        // Set up hooks (don't initialize components yet!)
        self::setup_hooks();
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
        
        // Register AJAX handlers for test suite
        add_action('wp_ajax_run_user_system_migration', [__CLASS__, 'handle_migration_ajax']);
        add_action('wp_ajax_clear_user_system_crons', [__CLASS__, 'handle_clear_crons_ajax']);
        
        if (HP_DEBUG) {
            error_log('Happy Place: Hooks registered');
        }
    }
    
    /**
     * Initialize core components (called on 'init' action)
     */
    public static function init_core(): void {
        if (HP_DEBUG) {
            error_log('Happy Place: init_core() called on init action');
        }
        
        // Manually load Configuration Manager if not already loaded
        $config_manager_file = HP_INCLUDES_DIR . 'core/class-configuration-manager.php';
        if (file_exists($config_manager_file) && !class_exists('HappyPlace\\Core\\ConfigurationManager')) {
            require_once $config_manager_file;
        }
        
        // Initialize Configuration Manager first (this handles all API keys and settings)
        if (class_exists('HappyPlace\\Core\\ConfigurationManager')) {
            \HappyPlace\Core\ConfigurationManager::get_instance();
            if (HP_DEBUG) {
                error_log('Happy Place: Configuration Manager initialized');
            }
        }
        
        // Initialize integrations after Configuration Manager
        self::init_integrations();
        
        // Run database migrations
        self::run_migrations();
        
        // Initialize Post Types
        if (class_exists('HappyPlace\\Core\\PostTypes')) {
            $post_types = \HappyPlace\Core\PostTypes::get_instance();
            $post_types->init();
            if (HP_DEBUG) {
                error_log('Happy Place: Post types initialized');
            }
        }
        
        // Initialize Taxonomies
        if (class_exists('HappyPlace\\Core\\Taxonomies')) {
            $taxonomies = \HappyPlace\Core\Taxonomies::get_instance();
            $taxonomies->init();
            if (HP_DEBUG) {
                error_log('Happy Place: Taxonomies initialized');
            }
        }
        
        // Initialize Listing Title Automation (for auto-generating listing titles from address)
        if (class_exists('HappyPlace\\Core\\ListingTitleAutomation')) {
            \HappyPlace\Core\ListingTitleAutomation::get_instance();
            if (HP_DEBUG) {
                error_log('Happy Place: Listing Title Automation initialized');
            }
        }
        
        // Initialize Core Services
        self::init_services();
        
        // Initialize Admin Menu (needs to be early to register admin_menu hook)
        if (is_admin() && class_exists('HappyPlace\\Admin\\AdminMenu')) {
            $admin_menu = \HappyPlace\Admin\AdminMenu::get_instance();
            $admin_menu->init();
            if (HP_DEBUG) {
                error_log('Happy Place: Admin menu initialized');
            }
        }
        
        // Initialize Email Settings Admin Page
        if (is_admin() && class_exists('HappyPlace\\Admin\\EmailSettings')) {
            new \HappyPlace\Admin\EmailSettings();
            if (HP_DEBUG) {
                error_log('Happy Place: Email settings admin page initialized');
            }
        }
        
        // Initialize Admin Service Test (only in debug mode)
        if (is_admin() && HP_DEBUG && class_exists('HappyPlace\\Admin\\AdminServiceTest')) {
            $service_test = new \HappyPlace\Admin\AdminServiceTest();
            $service_test->init();
            if (HP_DEBUG) {
                error_log('Happy Place: Admin service test initialized');
            }
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
        if (function_exists('hpt_get_dashboard_stats')) {
            
            if (class_exists('HappyPlace\\Integrations\\DashboardBridge')) {
                $dashboard_bridge = \HappyPlace\Integrations\DashboardBridge::get_instance();
                if (HP_DEBUG) {
                    error_log('Happy Place: Dashboard bridge initialized');
                }
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
            if (HP_DEBUG) {
                error_log('Happy Place: Listing service initialized');
            }
        }
        
        // Initialize Form Service
        if (class_exists('HappyPlace\\Services\\FormService')) {
            $form_service = new \HappyPlace\Services\FormService();
            $form_service->init();
            if (HP_DEBUG) {
                error_log('Happy Place: Form service initialized');
            }
        }
        
        // Initialize Import Service
        if (class_exists('HappyPlace\\Services\\ImportService')) {
            $import_service = new \HappyPlace\Services\ImportService();
            $import_service->init();
            error_log('Happy Place: Import service initialized');
        }
        
        // Initialize Export Service
        if (class_exists('HappyPlace\\Services\\ExportService')) {
            $export_service = new \HappyPlace\Services\ExportService();
            $export_service->init();
            error_log('Happy Place: Export service initialized');
        }
        
        // Initialize Unified Lead Service (replaces old LeadService)
        if (class_exists('HappyPlace\\Services\\UnifiedLeadService')) {
            $unified_lead_service = new \HappyPlace\Services\UnifiedLeadService();
            $unified_lead_service->init();
            error_log('Happy Place: Unified Lead Service initialized');
        }
        
        // Initialize User Role Service (before Agent Service)
        if (class_exists('HappyPlace\\Services\\UserRoleService')) {
            $user_role_service = new \HappyPlace\Services\UserRoleService();
            $user_role_service->init();
            error_log('Happy Place: User Role service initialized');
        }
        
        // Initialize Agent Service
        if (class_exists('HappyPlace\\Services\\AgentService')) {
            $agent_service = new \HappyPlace\Services\AgentService();
            $agent_service->init();
            error_log('Happy Place: Agent service initialized');
        }
        
        // Initialize Open House Service
        if (class_exists('HappyPlace\\Services\\OpenHouseService')) {
            $open_house_service = new \HappyPlace\Services\OpenHouseService();
            $open_house_service->init();
            error_log('Happy Place: Open House service initialized');
        }

        // Initialize Media Organization Service (add after Open House Service)
        if (class_exists('HappyPlace\\Services\\MediaOrganizationService')) {
        $media_service = new \HappyPlace\Services\MediaOrganizationService();
        $media_service->init();
        error_log('Happy Place: Media Organization service initialized');
        }
        
        // Initialize Address Intelligence Bridge (for auto-geocoding)
        $address_intelligence_file = HP_INCLUDES_DIR . 'address-intelligence-bridge.php';
        if (file_exists($address_intelligence_file)) {
            require_once $address_intelligence_file;
            if (class_exists('HPH_Address_Intelligence_Bridge')) {
                new \HPH_Address_Intelligence_Bridge();
                error_log('Happy Place: Address Intelligence Bridge initialized');
            }
        }
        
        // Initialize Transaction Service
        if (class_exists('HappyPlace\\Services\\TransactionService')) {
            $transaction_service = new \HappyPlace\Services\TransactionService();
            $transaction_service->init();
            error_log('Happy Place: Transaction service initialized');
        }
        
        // Initialize Marketing Service
        if (class_exists('HappyPlace\\Services\\MarketingService')) {
            $marketing_service = new \HappyPlace\Services\MarketingService();
            $marketing_service->init();
            error_log('Happy Place: Marketing service initialized');
        }
        
        // Initialize Form Router (CRITICAL - handles all form submissions)
        if (class_exists('HappyPlace\\Services\\FormRouter')) {
            $form_router = new \HappyPlace\Services\FormRouter();
            $form_router->init();
            error_log('Happy Place: Form Router initialized');
        }

        // Initialize Unified Agent User Service (migrated from theme)
        if (class_exists('HappyPlace\\Services\\UnifiedAgentUserService')) {
            $agent_user_service = new \HappyPlace\Services\UnifiedAgentUserService();
            $agent_user_service->init();
            error_log('Happy Place: Unified Agent User Service initialized');
        }

        // Initialize Dashboard Service (migrated from theme)
        if (class_exists('HappyPlace\\Services\\DashboardService')) {
            $dashboard_service = new \HappyPlace\Services\DashboardService();
            $dashboard_service->init();
            if (HP_DEBUG) {
                error_log('Happy Place: Dashboard Service initialized');
            }
        }

        // Initialize Search Service (migrated from theme)
        if (class_exists('HappyPlace\\Services\\SearchService')) {
            $search_service = new \HappyPlace\Services\SearchService();
            $search_service->init();
            if (HP_DEBUG) {
                error_log('Happy Place: Search Service initialized');
            }
        }

        // Initialize User Interactions Service (migrated from theme)
        if (class_exists('HappyPlace\\Services\\UserInteractionsService')) {
            $user_interactions_service = new \HappyPlace\Services\UserInteractionsService();
            $user_interactions_service->init();
            if (HP_DEBUG) {
                error_log('Happy Place: User Interactions Service initialized');
            }
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
     * Initialize integrations
     */
    private static function init_integrations(): void {
        // Load Mapbox integration
        $mapbox_integration_file = HP_INCLUDES_DIR . 'integrations/mapbox-integration.php';
        if (file_exists($mapbox_integration_file)) {
            require_once $mapbox_integration_file;
            error_log('Happy Place: Mapbox integration initialized');
        }
        
        // Load FollowUp Boss integration
        $followup_boss_file = HP_INCLUDES_DIR . 'integrations/class-followup-boss-integration.php';
        if (file_exists($followup_boss_file)) {
            require_once $followup_boss_file;
            error_log('Happy Place: FollowUp Boss integration initialized');
        }
        
        // Add other integrations here as needed
        // Example:
        // $google_maps_file = HP_INCLUDES_DIR . 'integrations/google-maps-integration.php';
        // if (file_exists($google_maps_file)) {
        //     require_once $google_maps_file;
        // }
        
        error_log('Happy Place: All integrations loaded');
    }
    
    /**
     * Run database migrations
     */
    private static function run_migrations(): void {
        // Load the User System migration class if not already loaded
        $migration_file = HP_INCLUDES_DIR . 'migrations/class-user-system-migration.php';
        if (file_exists($migration_file) && !class_exists('HappyPlace\\Migrations\\UserSystemMigration')) {
            require_once $migration_file;
        }
        
        // Load the Marketing migration class if not already loaded
        $marketing_migration_file = HP_INCLUDES_DIR . 'migrations/class-marketing-migration.php';
        if (file_exists($marketing_migration_file) && !class_exists('HappyPlace\\Migrations\\MarketingMigration')) {
            require_once $marketing_migration_file;
        }
        
        // Run User System migrations if class exists
        if (class_exists('HappyPlace\\Migrations\\UserSystemMigration')) {
            try {
                \HappyPlace\Migrations\UserSystemMigration::maybe_migrate();
                error_log('Happy Place: User System migrations checked/completed');
            } catch (Exception $e) {
                error_log('Happy Place: User System migration error - ' . $e->getMessage());
            }
        } else {
            error_log('Happy Place: User System migration class not found - skipping user system migrations');
        }
        
        // Run Marketing migrations if class exists  
        if (class_exists('HappyPlace\\Migrations\\MarketingMigration')) {
            try {
                if (\HappyPlace\Migrations\MarketingMigration::needs_migration()) {
                    \HappyPlace\Migrations\MarketingMigration::run();
                    error_log('Happy Place: Marketing migrations completed');
                }
            } catch (Exception $e) {
                error_log('Happy Place: Marketing migration error - ' . $e->getMessage());
            }
        } else {
            error_log('Happy Place: Marketing migration class not found - skipping marketing migrations');
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
    
    /**
     * Handle migration AJAX request
     */
    public static function handle_migration_ajax(): void {
        // Check permissions and nonce
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'user_system_test')) {
            wp_send_json_error('Access denied');
            return;
        }
        
        try {
            // Load the migration class if not already loaded
            $migration_file = HP_INCLUDES_DIR . 'migrations/class-user-system-migration.php';
            if (file_exists($migration_file) && !class_exists('HappyPlace\\Migrations\\UserSystemMigration')) {
                require_once $migration_file;
            }
            
            if (class_exists('HappyPlace\\Migrations\\UserSystemMigration')) {
                \HappyPlace\Migrations\UserSystemMigration::run_migration();
                wp_send_json_success('Migration completed successfully');
            } else {
                wp_send_json_error('Migration class not found');
            }
        } catch (Exception $e) {
            error_log('Happy Place Migration Error: ' . $e->getMessage());
            wp_send_json_error('Migration failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle clear crons AJAX request
     */
    public static function handle_clear_crons_ajax(): void {
        // Check permissions and nonce
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'user_system_test')) {
            wp_send_json_error('Access denied');
            return;
        }
        
        $hooks = ['hp_process_search_alerts', 'hp_analyze_user_engagement', 'hp_daily_search_digest', 'hp_cleanup_old_activities'];
        
        foreach ($hooks as $hook) {
            wp_clear_scheduled_hook($hook);
        }
        
        wp_send_json_success('Cron jobs cleared successfully');
    }
}