<?php
/**
 * Main Theme Class
 * 
 * @package HappyPlaceTheme
 * @since 2.2.0
 */

class HPH_Theme {
    
    /**
     * Theme instance
     * @var HPH_Theme
     */
    private static $instance = null;
    
    /**
     * Loaded services
     * @var array
     */
    private $services = array();
    
    /**
     * Get instance
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize theme
     */
    public static function init() {
        return self::instance()->bootstrap();
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->define_constants();
    }
    
    /**
     * Define additional constants
     */
    private function define_constants() {
        // Asset paths
        if (!defined('HPH_ASSETS_DIR')) define('HPH_ASSETS_DIR', HPH_THEME_DIR . '/assets');
        if (!defined('HPH_ASSETS_URI')) define('HPH_ASSETS_URI', HPH_THEME_URI . '/assets');
        if (!defined('HPH_CSS_URI')) define('HPH_CSS_URI', HPH_ASSETS_URI . '/css');
        if (!defined('HPH_JS_URI')) define('HPH_JS_URI', HPH_ASSETS_URI . '/js');
        if (!defined('HPH_IMAGES_URI')) define('HPH_IMAGES_URI', HPH_ASSETS_URI . '/images');
        
        // Include paths
        if (!defined('HPH_INC_DIR')) define('HPH_INC_DIR', HPH_THEME_DIR . '/includes');
        if (!defined('HPH_TEMPLATE_DIR')) define('HPH_TEMPLATE_DIR', HPH_THEME_DIR . '/template-parts');
        
        // Configuration
        // HPH_GOOGLE_MAPS_KEY is now defined securely in wp-config.php
    }
    
    /**
     * Bootstrap the theme
     */
    public function bootstrap() {
        // Set up text domain loading on proper hook
        add_action('init', array($this, 'setup_textdomain'));
        
        // Load core dependencies first
        $this->load_dependencies();
        
        // Register all services
        $this->register_services();
        
        // Initialize services
        $this->init_services();
        
        // Load AJAX handlers
        $this->load_ajax_handlers();
        
        // Archive assets now handled by main theme-assets.php system
        
        // Force rewrite flush on theme initialization if needed
        add_action('init', array($this, 'maybe_flush_rewrite_rules'), 999);
        
        return $this;
    }
    
    /**
     * Setup text domain loading
     */
    public function setup_textdomain() {
        load_theme_textdomain('happy-place-theme', HPH_THEME_DIR . '/languages');
    }
    
    /**
     * Maybe flush rewrite rules on theme activation
     */
    public function maybe_flush_rewrite_rules() {
        $router_service = $this->get_service('router');
        if ($router_service && method_exists($router_service, 'maybe_flush_rewrite_rules')) {
            // This will automatically flush if the theme version has changed
            $router_service->maybe_flush_rewrite_rules();
        }
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Load interfaces first
        $this->load_interfaces();
        
        // Load service classes
        $this->load_service_classes();
        
        // Legacy template loader removed - using unified HPH_Component_Loader system
        // require_once HPH_INC_DIR . '/class-hph-template-loader.php';
        
        // Load template part tracker - file removed in cleanup
        // require_once HPH_INC_DIR . '/templates/template-part-tracker.php';

        // Load block register
        require_once HPH_INC_DIR . '/services/class-hph-block-register.php';
        
        // Load component loader
        require_once HPH_INC_DIR . '/class-hph-component-loader.php';
        
        // Load the adapter service
        require_once HPH_INC_DIR . '/services/class-adapter-service.php';
        
        // Load menu walker class
        require_once HPH_THEME_DIR . '/includes/class-menu-walker.php';
        
        // Load bridge functions
        $this->load_bridge_functions();
        
        // Load integrations
        $this->load_integrations();
        
        // Agent user synchronization system - MIGRATED to plugin (UnifiedAgentUserService)

        // Load template router for archive views
        require_once HPH_INC_DIR . '/template-router.php';
        
        // Load organized AJAX handlers (New organized system)
        // NOTE: contact-forms.php disabled - using plugin FormRouter instead
        require_once HPH_INC_DIR . '/ajax/user-interactions.php';
        require_once HPH_INC_DIR . '/ajax/search-ajax.php';
        require_once HPH_INC_DIR . '/ajax/archive-ajax.php';
        require_once HPH_INC_DIR . '/ajax/archive-unified-ajax.php';
        require_once HPH_INC_DIR . '/ajax/dashboard-ajax.php';
        require_once HPH_INC_DIR . '/ajax/listings-dashboard-ajax.php';
        
        // Load admin AJAX handlers only for admin users
        if (is_admin() || wp_doing_ajax()) {
            require_once HPH_INC_DIR . '/ajax/admin-ajax.php';
        }
        
        // Legacy AJAX file - TODO: Remove after testing new system
        // require_once HPH_INC_DIR . '/ajax-handlers.php';
        
        // Load admin interfaces
        if (is_admin()) {
            require_once HPH_INC_DIR . '/admin/class-hph-asset-admin.php';
        }
        
        // Load theme settings helpers
        require_once HPH_INC_DIR . '/helpers/theme-settings-helpers.php';
        
        // Load contact form handler
        require_once HPH_INC_DIR . '/handlers/contact-form-handler.php';
        
        // Load agent helpers
        require_once HPH_INC_DIR . '/helpers/agent-helpers.php';
        
        // Load card adapter functions
        require_once HPH_INC_DIR . '/adapters/listing-card-adapter.php';
        require_once HPH_INC_DIR . '/adapters/agent-card-adapter.php';
        require_once HPH_INC_DIR . '/adapters/city-card-adapter.php';
        require_once HPH_INC_DIR . '/adapters/community-card-adapter.php';
        
        // Load archive hero helpers
        require_once HPH_INC_DIR . '/helpers/archive-hero-helpers.php';
        
        // Load component helpers
        require_once HPH_INC_DIR . '/helpers/component-helpers.php';
        
        // Load image helpers
        require_once HPH_INC_DIR . '/helpers/image-helpers.php';
        
        // Load debug helpers (development only) - files removed in cleanup
        if (defined('WP_DEBUG') && WP_DEBUG) {
            if (file_exists(HPH_INC_DIR . '/debug/asset-debug.php')) {
                require_once HPH_INC_DIR . '/debug/asset-debug.php';
            }
            if (file_exists(HPH_INC_DIR . '/debug/asset-testing.php')) {
                require_once HPH_INC_DIR . '/debug/asset-testing.php';
            }
            if (file_exists(HPH_INC_DIR . '/debug/file-audit.php')) {
                require_once HPH_INC_DIR . '/debug/file-audit.php';
            }
        }
        
        // Load contact form handler
        require_once HPH_INC_DIR . '/handlers/contact-form-handler.php';
        
        // Load template registration - file removed in cleanup
        // require_once HPH_INC_DIR . '/templates/template-registration.php';
        
        // Load admin functionality
        if (is_admin()) {
            $this->load_admin_classes();
        }
    }
    
    /**
     * Load interfaces
     */
    private function load_interfaces() {
        $interfaces = array(
            'interface-hph-service.php',
            'interface-hph-hookable.php',
        );
        
        foreach ($interfaces as $interface) {
            $file = HPH_INC_DIR . '/interfaces/' . $interface;
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
    
    /**
     * Load integrations
     */
    private function load_integrations() {
        $integrations = array(
            'class-google-maps.php',
            'dashboard-bridge-functions.php', // Bridge functions for plugin integration
        );
        
        foreach ($integrations as $integration) {
            $file = HPH_INC_DIR . '/integrations/' . $integration;
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
    
    /**
     * Load bridge functions
     */
    private function load_bridge_functions() {
        $bridge_files = array(
            'listing-bridge.php',
            'agent-bridge.php',
            'community-bridge.php',
            'open-house-bridge.php',
            'lead-bridge.php', // Lead management bridge to plugin service
            'user-bridge.php', // User management bridge to plugin services
            'dashboard-bridge.php',
            'local-place-bridge.php', // Local place bridge functions
            'universal-card-bridge.php', // Universal card data adapters
            'listing-changes-bridge.php', // Listing change tracking system
        );
        
        foreach ($bridge_files as $bridge_file) {
            $file = HPH_INC_DIR . '/bridge/' . $bridge_file;
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
    
    /**
     * Load service classes
     */
    private function load_service_classes() {
        $services = array(
            // Core services
            'class-hph-config.php',
            'class-hph-theme-support.php',
            // 'class-hph-assets.php', // REMOVED: Replaced with simple theme-assets.php
            'class-hph-widgets.php',
            'class-hph-menus.php',
            
            // Query and routing
            'class-hph-query-manager.php',
            'class-hph-router.php',
            
            // Customization
            'class-hph-customizer.php',
            'class-hph-performance.php',
            
            // Features
            'class-hph-dashboard.php',
            'class-hph-ajax-handler.php',
            'class-hph-shortcodes.php',
            
            // Utilities
            'class-hph-utils.php',
        );
        
        foreach ($services as $service) {
            $file = HPH_INC_DIR . '/services/' . $service;
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
    
    /**
     * Register all services
     */
    private function register_services() {
        $this->services = array(
            // Core services (order matters)
            'config'        => 'HPH_Config',
            'theme_support' => 'HPH_Theme_Support',
            // 'assets'        => 'HPH_Assets', // REMOVED: Replaced with simple theme-assets.php
            // 'template'      => 'HPH_Template_Loader', // REMOVED: Using HPH_Component_Loader instead
            
            // Features
            'widgets'       => 'HPH_Widgets',
            'menus'         => 'HPH_Menus',
            'customizer'    => 'HPH_Customizer',
            'query'         => 'HPH_Query_Manager',
            // 'router'        => 'HPH_Router', // TEMPORARILY DISABLED - Causes wp-admin redirect issues
            'dashboard'     => 'HPH_Dashboard',
            'ajax'          => 'HPH_Ajax_Handler',
            'shortcodes'    => 'HPH_Shortcodes',
            'performance'   => 'HPH_Performance',
            
            // Data services
            'adapter'       => 'HappyPlaceTheme\\Services\\AdapterService',
        );
        
        // Allow filtering of services
        $this->services = apply_filters('hph_theme_services', $this->services);
    }
    
    /**
     * Initialize services
     */
    private function init_services() {
        foreach ($this->services as $id => $class_name) {
            if (class_exists($class_name)) {
                // Check if it's a singleton
                if (method_exists($class_name, 'get_instance')) {
                    $this->services[$id] = $class_name::get_instance();
                } else {
                    $this->services[$id] = new $class_name();
                }
                
                // Initialize if it has an init method
                if (method_exists($this->services[$id], 'init')) {
                    $this->services[$id]->init();
                }
            }
        }
        
        // Initialize component loader
        if (class_exists('HPH_Component_Loader')) {
            HPH_Component_Loader::init();
        }
    }
    
    /**
     * Get a service instance
     */
    public function get_service($service_id) {
        return isset($this->services[$service_id]) ? $this->services[$service_id] : null;
    }
    
    /**
     * Magic getter for services
     */
    public function __get($service_id) {
        return $this->get_service($service_id);
    }
    
    /**
     * Check if plugin is active
     */
    public static function is_plugin_active() {
        return class_exists('HappyPlace\\Plugin') && function_exists('get_field');
    }
    
    /**
     * Load admin classes
     */
    private function load_admin_classes() {
        $admin_classes = array(
            'class-hph-admin-settings.php',
        );
        
        foreach ($admin_classes as $admin_class) {
            $file = HPH_INC_DIR . '/admin/' . $admin_class;
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
    
    /**
     * Load AJAX handlers
     */
    private function load_ajax_handlers() {
        $ajax_handlers = array(
            'contact-forms.php',
            'user-interactions.php',
            // Note: archive-ajax.php is loaded via ajax-handlers.php already
        );
        
        foreach ($ajax_handlers as $handler) {
            $file = HPH_INC_DIR . '/ajax/' . $handler;
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}

/**
 * Helper function for easy access to Adapter Service
 * 
 * @return \HappyPlaceTheme\Services\AdapterService
 */
function hpt_adapter() {
    return \HappyPlaceTheme\Services\AdapterService::get_instance();
}


//ACF Flexible Content Helper

function hph_render_flexible_section($layout) {
    $template_path = 'template-parts/flexible-content/' . str_replace('_', '-', $layout);
    $full_path = get_template_directory() . '/' . $template_path . '.php';
    
    if (file_exists($full_path)) {
        get_template_part($template_path);
    } else {
        // Development helper - shows missing templates
        if (WP_DEBUG) {
            echo '<!-- Missing template: ' . $template_path . ' -->';
        }
    }
}
