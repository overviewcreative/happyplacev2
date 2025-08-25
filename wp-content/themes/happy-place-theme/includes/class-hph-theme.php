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
        define('HPH_ASSETS_DIR', HPH_THEME_DIR . '/assets');
        define('HPH_ASSETS_URI', HPH_THEME_URI . '/assets');
        define('HPH_CSS_URI', HPH_ASSETS_URI . '/css');
        define('HPH_JS_URI', HPH_ASSETS_URI . '/js');
        define('HPH_IMAGES_URI', HPH_ASSETS_URI . '/images');
        
        // Include paths
        define('HPH_INC_DIR', HPH_THEME_DIR . '/includes');
        define('HPH_TEMPLATE_DIR', HPH_THEME_DIR . '/template-parts');
        
        // Configuration
        if (!defined('HPH_GOOGLE_MAPS_KEY')) {
            define('HPH_GOOGLE_MAPS_KEY', 'AIzaSyAg9FL1dH2oEPy-yWgR2LvMQweKO827Csw');
        }
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
        
        // Load the template loader
        require_once HPH_INC_DIR . '/class-hph-template-loader.php';
        
        // Load component loader
        require_once HPH_INC_DIR . '/class-hph-component-loader.php';
        
        // Load menu walker class
        require_once HPH_THEME_DIR . '/includes/class-menu-walker.php';
        
        // Load integrations
        $this->load_integrations();
        
        // Load AJAX handlers for frontend functionality
        require_once HPH_INC_DIR . '/ajax-handlers.php';
        
        // Load theme settings helpers
        require_once HPH_INC_DIR . '/theme-settings-helpers.php';
        
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
     * Load service classes
     */
    private function load_service_classes() {
        $services = array(
            // Core services
            'class-hph-config.php',
            'class-hph-theme-support.php',
            'class-hph-assets.php',
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
            'assets'        => 'HPH_Assets',
            'template'      => 'HPH_Template_Loader',
            
            // Features
            'widgets'       => 'HPH_Widgets',
            'menus'         => 'HPH_Menus',
            'customizer'    => 'HPH_Customizer',
            'query'         => 'HPH_Query_Manager',
            'router'        => 'HPH_Router',
            'dashboard'     => 'HPH_Dashboard',
            'ajax'          => 'HPH_Ajax_Handler',
            'shortcodes'    => 'HPH_Shortcodes',
            'performance'   => 'HPH_Performance',
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
}