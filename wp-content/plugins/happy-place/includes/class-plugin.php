<?php
/**
 * Main Plugin Class
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
     * Service providers
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
            
            // Register autoloader
            $this->register_autoloader();
            
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
     * Register autoloader for plugin classes
     * 
     * @return void
     */
    private function register_autoloader(): void {
        spl_autoload_register(function($class) {
            // Check if it's our namespace
            if (strpos($class, 'HappyPlace\\') !== 0) {
                return;
            }
            
            // Convert namespace to file path
            $relative_class = substr($class, 11);
            $file_path = str_replace('\\', '/', $relative_class);
            
            // Build full path
            $file = HP_PLUGIN_DIR . 'includes/' . $file_path . '.php';
            
            // Load file if it exists
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
    
    /**
     * Register service providers
     * 
     * @return void
     */
    private function register_providers(): void {
        foreach ($this->providers as $provider_class) {
            try {
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
        // Trigger admin assets action
        do_action('hp_admin_assets', $hook);
    }
    
    /**
     * Enqueue frontend assets
     * 
     * @return void
     */
    public function frontend_assets(): void {
        // Trigger frontend assets action
        do_action('hp_frontend_assets');
    }
    
    /**
     * AJAX heartbeat handler
     * 
     * @return void
     */
    public function ajax_heartbeat(): void {
        check_ajax_referer('hp_ajax_nonce', 'nonce');
        
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
            // Create database tables
            $this->container->get('database')->create_tables();
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
            // Set activation flag
            update_option('hp_plugin_activated', time());
            
            // Schedule cron jobs
            $this->container->get('scheduler')->schedule_jobs();
            
            hp_log('Plugin activated', 'info', 'PLUGIN');
            
        } catch (\Exception $e) {
            $this->error_handler->handle_exception($e);
            
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
            // Clear scheduled cron jobs
            $this->container->get('scheduler')->clear_jobs();
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
            // Clear cache
            $this->container->get('cache')->flush();
            
            hp_log('Plugin deactivated', 'info', 'PLUGIN');
            
        } catch (\Exception $e) {
            $this->error_handler->handle_exception($e);
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
            ];
            
            foreach ($tables as $table) {
                $wpdb->query("DROP TABLE IF EXISTS {$table}");
            }
            
            // Delete options
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'hp_%'");
            
            // Delete user meta
            $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'hp_%'");
            
            // Delete posts
            $post_types = ['listing', 'agent', 'open_house', 'lead'];
            foreach ($post_types as $post_type) {
                $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = '{$post_type}'");
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