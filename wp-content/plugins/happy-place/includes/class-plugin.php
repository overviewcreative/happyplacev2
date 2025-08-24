<?php
/**
 * Main Plugin Class - RESTORED
 * 
 * Central orchestrator for the Happy Place Plugin with proper service container,
 * dependency injection, and error recovery mechanisms
 *
 * @package HappyPlace
 * @version 4.0.0
 */

namespace HappyPlace;

use HappyPlace\Core\Container;
use HappyPlace\Core\ServiceProvider;
use HappyPlace\Core\ErrorHandler;
use HappyPlace\Core\ComponentLoader;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Class
 * 
 * @since 4.0.0
 */
final class Plugin {
    
    /**
     * Plugin version
     */
    const VERSION = '4.0.0';
    
    /**
     * Minimum PHP version required
     */
    const MINIMUM_PHP_VERSION = '7.4';
    
    /**
     * Minimum WordPress version required
     */
    const MINIMUM_WP_VERSION = '6.0';
    
    /**
     * Single instance of the plugin
     * 
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;
    
    /**
     * Service container
     * 
     * @var Container
     */
    private Container $container;
    
    /**
     * Component loader
     * 
     * @var ComponentLoader
     */
    private ComponentLoader $loader;
    
    /**
     * Error handler
     * 
     * @var ErrorHandler
     */
    private ErrorHandler $error_handler;
    
    /**
     * Plugin initialization status
     * 
     * @var bool
     */
    private bool $initialized = false;
    
    /**
     * Boot status
     * 
     * @var bool
     */
    private bool $booted = false;
    
    /**
     * Service providers with lowercase hyphenated paths
     * 
     * @var array
     */
    private array $providers = [
        'HappyPlace\\Providers\\CoreServiceProvider',
        'HappyPlace\\Providers\\PostTypeServiceProvider',
        'HappyPlace\\Providers\\TaxonomyServiceProvider',
        'HappyPlace\\Providers\\ACFServiceProvider',
        'HappyPlace\\Providers\\AdminServiceProvider',
        'HappyPlace\\Providers\\APIServiceProvider',
        'HappyPlace\\Providers\\AssetServiceProvider',
        'HappyPlace\\Providers\\SecurityServiceProvider',
        'HappyPlace\\Providers\\DashboardServiceProvider',
    ];
    
    /**
     * Core components to load (using actual file names)
     * 
     * @var array
     */
    private array $core_components = [
        'PostTypes' => [
            'file' => 'core/class-post-types.php',
            'class' => 'HappyPlace\\Core\\PostTypes',
            'init' => true
        ],
        'Taxonomies' => [
            'file' => 'core/class-taxonomies.php',
            'class' => 'HappyPlace\\Core\\Taxonomies',
            'init' => true
        ],
        'ACFManager' => [
            'file' => 'core/class-acf-manager.php',
            'class' => 'HappyPlace\\Core\\ACFManager',
            'init' => true
        ],
        'FieldMapper' => [
            'file' => 'core/class-field-mapper.php',
            'class' => 'HappyPlace\\Core\\FieldMapper',
            'init' => false
        ],
        'ConfigSyncManager' => [
            'file' => 'core/class-config-sync-manager.php',
            'class' => 'HappyPlace\\Core\\ConfigSyncManager',
            'init' => true
        ],
        'AssetsManager' => [
            'file' => 'core/class-assets-manager.php',
            'class' => 'HappyPlace\\Core\\AssetsManager',
            'init' => true
        ],
    ];
    
    /**
     * Admin components
     * 
     * @var array
     */
    private array $admin_components = [
        'AdminMenu' => [
            'file' => 'admin/class-admin-menu.php',
            'class' => 'HappyPlace\\Admin\\AdminMenu',
            'init' => true
        ],
        'ACFSyncManager' => [
            'file' => 'admin/class-acf-sync-manager.php',
            'class' => 'HappyPlace\\Admin\\ACF_Sync_Manager',
            'init' => false
        ],
    ];
    
    /**
     * API components
     * 
     * @var array
     */
    private array $api_components = [
        'RestAPI' => [
            'file' => 'api/class-rest-api.php',
            'class' => 'HappyPlace\\API\\REST_API',
            'init' => false
        ],
        'DashboardAjax' => [
            'file' => 'api/ajax/class-dashboard-ajax.php',
            'class' => 'HappyPlace\\API\\Ajax\\Dashboard_Ajax',
            'init' => false
        ],
    ];
    
    /**
     * Get singleton instance
     * 
     * @return Plugin
     */
    public static function instance(): Plugin {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Initialize container first
        $this->container = new Container();
        
        // Bind self to container
        $this->container->singleton('plugin', function() {
            return $this;
        });
        
        // Initialize error handler
        $this->error_handler = new ErrorHandler($this->container);
        $this->container->singleton('error_handler', function() {
            return $this->error_handler;
        });
        
        // Initialize component loader
        $this->loader = new ComponentLoader($this->container, $this->error_handler);
        $this->container->singleton('loader', function() {
            return $this->loader;
        });
    }
    
    /**
     * Initialize the plugin
     * 
     * @return bool
     */
    public function initialize(): bool {
        if ($this->initialized) {
            return true;
        }
        
        try {
            // Check requirements
            if (!$this->check_requirements()) {
                return false;
            }
            
            // Set up error handling
            $this->setup_error_handling();
            
            // Load text domain
            $this->load_textdomain();
            
            // Load core components
            $this->load_core_components();
            
            // Register service providers
            $this->register_providers();
            
            // Set up hooks
            $this->setup_hooks();
            
            $this->initialized = true;
            
            hp_log('Plugin initialized successfully', 'info', 'PLUGIN');
            
            return true;
            
        } catch (\Exception $e) {
            $this->error_handler->handle_exception($e);
            return false;
        }
    }
    
    /**
     * Boot the plugin
     * 
     * @return void
     */
    public function boot(): void {
        if ($this->booted || !$this->initialized) {
            return;
        }
        
        try {
            // Load admin components if in admin
            if (is_admin()) {
                $this->load_admin_components();
            }
            
            // Load API components
            $this->load_api_components();
            
            // Boot all registered service providers
            foreach ($this->container->getProviders() as $provider) {
                if (method_exists($provider, 'boot')) {
                    $provider->boot();
                }
            }
            
            $this->booted = true;
            
            // Fire booted action
            do_action('hp_plugin_booted', $this);
            
            hp_log('Plugin booted successfully', 'info', 'PLUGIN');
            
        } catch (\Exception $e) {
            $this->error_handler->handle_exception($e);
        }
    }
    
    /**
     * Check plugin requirements
     * 
     * @return bool
     */
    private function check_requirements(): bool {
        $errors = [];
        
        // Check PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            $errors[] = sprintf(
                __('Happy Place requires PHP %s or higher. You are running PHP %s.', 'happy-place'),
                self::MINIMUM_PHP_VERSION,
                PHP_VERSION
            );
        }
        
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), self::MINIMUM_WP_VERSION, '<')) {
            $errors[] = sprintf(
                __('Happy Place requires WordPress %s or higher. You are running WordPress %s.', 'happy-place'),
                self::MINIMUM_WP_VERSION,
                get_bloginfo('version')
            );
        }
        
        // Check for required PHP extensions
        $required_extensions = ['json', 'mysqli'];
        foreach ($required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $errors[] = sprintf(
                    __('Happy Place requires the PHP %s extension.', 'happy-place'),
                    $extension
                );
            }
        }
        
        if (!empty($errors)) {
            if (is_admin()) {
                add_action('admin_notices', function() use ($errors) {
                    ?>
                    <div class="notice notice-error">
                        <p><strong><?php _e('Happy Place Plugin Error', 'happy-place'); ?></strong></p>
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo esc_html($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                    <?php
                });
            }
            
            hp_log('Requirements check failed: ' . implode(', ', $errors), 'error', 'PLUGIN');
            return false;
        }
        
        return true;
    }
    
    /**
     * Set up error handling
     * 
     * @return void
     */
    private function setup_error_handling(): void {
        // Set custom error handler for plugin errors
        set_error_handler([$this->error_handler, 'handle_error'], E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        
        // Set custom exception handler
        set_exception_handler([$this->error_handler, 'handle_exception']);
        
        // Register shutdown function for fatal errors
        register_shutdown_function([$this->error_handler, 'handle_shutdown']);
    }
    
    /**
     * Load plugin text domain
     * 
     * @return void
     */
    private function load_textdomain(): void {
        load_plugin_textdomain(
            'happy-place',
            false,
            dirname(plugin_basename(HP_PLUGIN_FILE)) . '/languages'
        );
    }
    
    /**
     * Load core components
     * 
     * @return void
     */
    private function load_core_components(): void {
        hp_log('Loading core components', 'info', 'PLUGIN');
        
        foreach ($this->core_components as $name => $config) {
            if (!$this->loader->load($name, $config)) {
                hp_log("Failed to load core component: {$name}", 'error', 'PLUGIN');
            }
        }
    }
    
    /**
     * Load admin components
     * 
     * @return void
     */
    private function load_admin_components(): void {
        hp_log('Loading admin components', 'info', 'PLUGIN');
        
        foreach ($this->admin_components as $name => $config) {
            if (!$this->loader->load($name, $config)) {
                hp_log("Failed to load admin component: {$name}", 'warning', 'PLUGIN');
            }
        }
    }
    
    /**
     * Load API components
     * 
     * @return void
     */
    private function load_api_components(): void {
        hp_log('Loading API components', 'info', 'PLUGIN');
        
        foreach ($this->api_components as $name => $config) {
            if (!$this->loader->load($name, $config)) {
                hp_log("Failed to load API component: {$name}", 'warning', 'PLUGIN');
            }
        }
    }
    
    /**
     * Register service providers
     * 
     * @return void
     */
    private function register_providers(): void {
        foreach ($this->providers as $provider_class) {
            try {
                // Check if provider file exists using our naming convention
                $provider_file = $this->get_provider_file($provider_class);
                
                if ($provider_file && file_exists($provider_file)) {
                    require_once $provider_file;
                }
                
                if (!class_exists($provider_class)) {
                    hp_log("Provider class not found: {$provider_class}", 'warning', 'PLUGIN');
                    continue;
                }
                
                $provider = new $provider_class($this->container);
                
                if (!$provider instanceof ServiceProvider) {
                    hp_log("Invalid provider: {$provider_class}", 'error', 'PLUGIN');
                    continue;
                }
                
                $provider->register();
                $this->container->addProvider($provider);
                
                hp_log("Registered provider: {$provider_class}", 'debug', 'PLUGIN');
                
            } catch (\Exception $e) {
                $this->error_handler->handle_exception($e);
            }
        }
    }
    
    /**
     * Get provider file path
     * 
     * @param string $class_name Full class name with namespace
     * @return string|null
     */
    private function get_provider_file(string $class_name): ?string {
        // Extract just the class name
        $parts = explode('\\', $class_name);
        $class = array_pop($parts);
        
        // Convert to file name: CoreServiceProvider -> class-core-service-provider.php
        $file_name = preg_replace('/([a-z])([A-Z])/', '$1-$2', $class);
        $file_name = 'class-' . strtolower($file_name) . '.php';
        
        return HP_INCLUDES_DIR . 'providers/' . $file_name;
    }
    
    /**
     * Set up WordPress hooks
     * 
     * @return void
     */
    private function setup_hooks(): void {
        // Core hooks
        add_action('init', [$this, 'boot'], 0);
        add_action('wp_loaded', [$this, 'on_wp_loaded']);
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_init', [$this, 'admin_init']);
            add_action('admin_menu', [$this, 'admin_menu']);
            add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        }
        
        // Frontend hooks
        if (!is_admin()) {
            add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);
        }
        
        // AJAX hooks
        add_action('wp_ajax_hp_heartbeat', [$this, 'ajax_heartbeat']);
        add_action('wp_ajax_nopriv_hp_heartbeat', [$this, 'ajax_heartbeat']);
        
        // Activation/Deactivation hooks
        register_activation_hook(HP_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(HP_PLUGIN_FILE, [$this, 'deactivate']);
        register_uninstall_hook(HP_PLUGIN_FILE, [__CLASS__, 'uninstall']);
    }
    
    /**
     * WordPress loaded hook
     * 
     * @return void
     */
    public function on_wp_loaded(): void {
        // Trigger loaded action
        do_action('hp_plugin_loaded', $this);
    }
    
    /**
     * Admin initialization
     * 
     * @return void
     */
    public function admin_init(): void {
        // Trigger admin init action
        do_action('hp_admin_init', $this);
    }
    
    /**
     * Admin menu setup
     * 
     * @return void
     */
    public function admin_menu(): void {
        // Trigger admin menu action
        do_action('hp_admin_menu', $this);
    }
    
    /**
     * Enqueue admin assets
     * 
     * @param string $hook Current admin page hook
     * @return void
     */
    public function admin_assets(string $hook): void {
        // Global admin assets
        wp_enqueue_style(
            'hp-admin-global',
            HP_ASSETS_URL . 'css/admin/admin-global.css',
            [],
            self::VERSION
        );
        
        wp_enqueue_script(
            'hp-admin-global',
            HP_ASSETS_URL . 'js/admin/admin-global.js',
            ['jquery'],
            self::VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('hp-admin-global', 'hp_admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(HP_AJAX_NONCE_KEY),
            'plugin_url' => HP_PLUGIN_URL,
            'version' => self::VERSION,
        ]);
        
        // Trigger admin assets action
        do_action('hp_admin_assets', $hook);
    }
    
    /**
     * Enqueue frontend assets
     * 
     * @return void
     */
    public function frontend_assets(): void {
        // Global frontend assets
        wp_enqueue_style(
            'hp-frontend',
            HP_ASSETS_URL . 'css/frontend.css',
            [],
            self::VERSION
        );
        
        wp_enqueue_script(
            'hp-frontend',
            HP_ASSETS_URL . 'js/frontend.js',
            ['jquery'],
            self::VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('hp-frontend', 'hp_frontend', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(HP_AJAX_NONCE_KEY),
            'plugin_url' => HP_PLUGIN_URL,
        ]);
        
        // Trigger frontend assets action
        do_action('hp_frontend_assets');
    }
    
    /**
     * AJAX heartbeat handler
     * 
     * @return void
     */
    public function ajax_heartbeat(): void {
        check_ajax_referer(HP_AJAX_NONCE_KEY, 'nonce');
        
        wp_send_json_success([
            'status' => 'alive',
            'timestamp' => time()
        ]);
    }
    
    /**
     * Plugin activation
     * 
     * @return void
     */
    public function activate(): void {
        try {
            hp_log('Plugin activation started', 'info', 'PLUGIN');
            
            // Create database tables
            if ($this->container->has('database')) {
                $database = $this->container->get('database');
                if (method_exists($database, 'create_tables')) {
                    $database->create_tables();
                }
            }
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
            // Set activation flag
            update_option('hp_plugin_activated', time());
            update_option('hp_plugin_version', self::VERSION);
            
            // Schedule cron jobs
            if ($this->container->has('scheduler')) {
                $scheduler = $this->container->get('scheduler');
                if (method_exists($scheduler, 'schedule_jobs')) {
                    $scheduler->schedule_jobs();
                }
            }
            
            hp_log('Plugin activated successfully', 'info', 'PLUGIN');
            
        } catch (\Exception $e) {
            hp_log('Activation failed: ' . $e->getMessage(), 'error', 'PLUGIN');
            
            // Deactivate plugin on error
            deactivate_plugins(plugin_basename(HP_PLUGIN_FILE));
            
            wp_die(
                sprintf(
                    __('Plugin activation failed: %s', 'happy-place'),
                    $e->getMessage()
                ),
                __('Activation Error', 'happy-place'),
                ['back_link' => true]
            );
        }
    }
    
    /**
     * Plugin deactivation
     * 
     * @return void
     */
    public function deactivate(): void {
        try {
            hp_log('Plugin deactivation started', 'info', 'PLUGIN');
            
            // Clear scheduled cron jobs
            if ($this->container->has('scheduler')) {
                $scheduler = $this->container->get('scheduler');
                if (method_exists($scheduler, 'clear_jobs')) {
                    $scheduler->clear_jobs();
                }
            }
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
            // Clear cache
            if ($this->container->has('cache')) {
                $cache = $this->container->get('cache');
                if (method_exists($cache, 'flush')) {
                    $cache->flush();
                }
            }
            
            // Update deactivation time
            update_option('hp_plugin_deactivated', time());
            
            hp_log('Plugin deactivated successfully', 'info', 'PLUGIN');
            
        } catch (\Exception $e) {
            hp_log('Deactivation error: ' . $e->getMessage(), 'error', 'PLUGIN');
        }
    }
    
    /**
     * Plugin uninstall
     * 
     * @return void
     */
    public static function uninstall(): void {
        // Check if uninstall is allowed
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            return;
        }
        
        // Get option to check if data should be deleted
        $delete_data = get_option('hp_delete_data_on_uninstall', false);
        
        if ($delete_data) {
            // Delete database tables
            global $wpdb;
            $tables = [
                $wpdb->prefix . 'hp_listings_meta',
                $wpdb->prefix . 'hp_agent_meta',
                $wpdb->prefix . 'hp_analytics',
                $wpdb->prefix . 'hp_leads',
                $wpdb->prefix . 'hp_error_log',
            ];
            
            foreach ($tables as $table) {
                $wpdb->query("DROP TABLE IF EXISTS {$table}");
            }
            
            // Delete options
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'hp_%'");
            
            // Delete user meta
            $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'hp_%'");
            
            // Delete posts
            $post_types = ['listing', 'agent', 'open_house', 'lead', 'community'];
            foreach ($post_types as $post_type) {
                $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = '{$post_type}'");
            }
            
            // Delete taxonomies and terms
            $taxonomies = ['property_type', 'property_status', 'location', 'property_feature'];
            foreach ($taxonomies as $taxonomy) {
                $terms = get_terms([
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                    'fields' => 'ids'
                ]);
                
                foreach ($terms as $term_id) {
                    wp_delete_term($term_id, $taxonomy);
                }
            }
        }
        
        hp_log('Plugin uninstalled', 'info', 'PLUGIN');
    }
    
    /**
     * Get service container
     * 
     * @return Container
     */
    public function container(): Container {
        return $this->container;
    }
    
    /**
     * Get service from container
     * 
     * @param string $id Service ID
     * @return mixed
     */
    public function get(string $id) {
        return $this->container->get($id);
    }
    
    /**
     * Check if plugin is initialized
     * 
     * @return bool
     */
    public function is_initialized(): bool {
        return $this->initialized;
    }
    
    /**
     * Check if plugin is booted
     * 
     * @return bool
     */
    public function is_booted(): bool {
        return $this->booted;
    }
    
    /**
     * Get plugin version
     * 
     * @return string
     */
    public function version(): string {
        return self::VERSION;
    }
    
    /**
     * Get plugin URL
     * 
     * @param string $path Optional path to append
     * @return string
     */
    public function url(string $path = ''): string {
        $url = HP_PLUGIN_URL;
        
        if ($path) {
            $url .= ltrim($path, '/');
        }
        
        return $url;
    }
    
    /**
     * Get plugin path
     * 
     * @param string $path Optional path to append
     * @return string
     */
    public function path(string $path = ''): string {
        $plugin_path = HP_PLUGIN_DIR;
        
        if ($path) {
            $plugin_path .= ltrim($path, '/');
        }
        
        return $plugin_path;
    }
}