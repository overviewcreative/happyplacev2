<?php
/**
 * Plugin Name: Happy Place
 * Plugin URI: https://theparkergroup.com
 * Description: Advanced real estate platform - RESTORED CORE
 * Version: 4.0.0
 * Author: The Parker Group
 * Author URI: https://theparkergroup.com
 * License: GPL v2 or later
 * Text Domain: happy-place
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// PLUGIN CONSTANTS
// =============================================================================

define('HP_VERSION', '4.0.0');
define('HP_PLUGIN_FILE', __FILE__);
define('HP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HP_INCLUDES_DIR', HP_PLUGIN_DIR . 'includes/');
define('HP_ASSETS_DIR', HP_PLUGIN_DIR . 'assets/');
define('HP_ASSETS_URL', HP_PLUGIN_URL . 'assets/');
define('HP_DEBUG', defined('WP_DEBUG') && WP_DEBUG);

// Database table prefix
global $wpdb;
define('HP_TABLE_PREFIX', $wpdb->prefix . 'hp_');

// =============================================================================
// ERROR HANDLING
// =============================================================================

if (!function_exists('hp_log')) {
    function hp_log($message, $level = 'info', $context = '') {
        if (HP_DEBUG) {
            $timestamp = date('Y-m-d H:i:s');
            $context_str = $context ? "[{$context}] " : '';
            $log_message = "HP [{$timestamp}] [{$level}] {$context_str}{$message}";
            
            if ($level === 'critical' || $level === 'error') {
                error_log($log_message);
            } elseif (HP_DEBUG) {
                error_log($log_message);
            }
        }
    }
}

// =============================================================================
// LOAD CORE FILES
// =============================================================================

// Load Container first
require_once HP_INCLUDES_DIR . 'Core/Container.php';
require_once HP_INCLUDES_DIR . 'Core/ServiceProvider.php';
require_once HP_INCLUDES_DIR . 'Core/ErrorHandler.php';
require_once HP_INCLUDES_DIR . 'Core/ComponentLoader.php';

// Load main plugin class
require_once HP_INCLUDES_DIR . 'class-plugin.php';

// =============================================================================
// INITIALIZE PLUGIN
// =============================================================================

/**
 * Get plugin instance
 * 
 * @return HappyPlace\Plugin
 */
function hp_plugin() {
    return HappyPlace\Plugin::instance();
}

// Initialize plugin
add_action('plugins_loaded', function() {
    $plugin = hp_plugin();
    
    if (!$plugin->initialize()) {
        hp_log('Plugin initialization failed', 'critical', 'BOOTSTRAP');
        
        // Show admin notice
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php _e('Happy Place Plugin Error:', 'happy-place'); ?></strong>
                    <?php _e('The plugin failed to initialize. Please check the error logs for details.', 'happy-place'); ?>
                </p>
            </div>
            <?php
        });
        
        return;
    }
    
    hp_log('Plugin initialized via bootstrap', 'info', 'BOOTSTRAP');
});

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Get service from container
 * 
 * @param string $service Service ID
 * @return mixed
 */
function hp_service($service) {
    return hp_plugin()->get($service);
}

/**
 * Check if plugin is ready
 * 
 * @return bool
 */
function hp_is_ready() {
    return hp_plugin()->is_booted();
}

if (!defined('HP_ASSETS_DIR')) {
    define('HP_ASSETS_DIR', HP_PLUGIN_DIR . 'assets/');
}

if (!defined('HP_ASSETS_URL')) {
    define('HP_ASSETS_URL', HP_PLUGIN_URL . 'assets/');
}

if (!defined('HP_DIST_URL')) {
    define('HP_DIST_URL', HP_PLUGIN_URL . 'dist/');
}

// Environment Constants
if (!defined('HP_DEBUG')) {
    define('HP_DEBUG', defined('WP_DEBUG') && WP_DEBUG);
}

// Database Table Prefix
global $wpdb;
if (!defined('HP_TABLE_PREFIX')) {
    define('HP_TABLE_PREFIX', $wpdb->prefix . 'hp_');
}

// =============================================================================
// ERROR HANDLING AND LOGGING
// =============================================================================

/**
 * Central error logging function
 */
if (!function_exists('hp_log')) {
    function hp_log($message, $level = 'info', $context = '') {
        if (HP_DEBUG) {
            $timestamp = date('Y-m-d H:i:s');
            $context_str = $context ? "[{$context}] " : '';
            error_log("HP [{$timestamp}] {$level}: {$context_str}{$message}");
        }
    }
}

// Enable debugging in development
if (HP_DEBUG) {
    @ini_set('display_errors', 1);
    @ini_set('display_startup_errors', 1);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

// =============================================================================
// DEPENDENCY CHECKS
// =============================================================================

/**
 * Check for required dependencies
 */
function hp_check_dependencies() {
    $errors = [];
    
    hp_log('Checking dependencies...', 'info', 'DEPENDENCIES');
    hp_log('WordPress version: ' . get_bloginfo('version'), 'info', 'DEPENDENCIES');
    hp_log('PHP version: ' . PHP_VERSION, 'info', 'DEPENDENCIES');
    
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), '6.0', '<')) {
        $errors[] = __('Happy Place requires WordPress 6.0 or higher.', 'happy-place');
        hp_log('WordPress version check failed', 'error', 'DEPENDENCIES');
    }
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $errors[] = __('Happy Place requires PHP 7.4 or higher.', 'happy-place');
        hp_log('PHP version check failed', 'error', 'DEPENDENCIES');
    }
    
    // Check for ACF (optional but recommended)
    if (!class_exists('ACF') && !function_exists('acf')) {
        hp_log('ACF not found - plugin will work with limited functionality', 'warning', 'DEPENDENCIES');
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>';
            echo __('Happy Place works best with Advanced Custom Fields Pro. Some features may be limited without it.', 'happy-place');
            echo '</p></div>';
        });
    }
    
    if (!empty($errors)) {
        add_action('admin_notices', function() use ($errors) {
            echo '<div class="error"><p>';
            echo implode('<br>', $errors);
            echo '</p></div>';
        });
        hp_log('Dependencies check failed with ' . count($errors) . ' errors', 'error', 'DEPENDENCIES');
        return false;
    }
    
    hp_log('All dependencies met', 'info', 'DEPENDENCIES');
    return true;
}

// =============================================================================
// AUTOLOADER
// =============================================================================

/**
 * PSR-4 compliant autoloader for Happy Place classes
 */
spl_autoload_register(function($class) {
    // Check if it's our namespace
    if (strpos($class, 'HappyPlace\\') !== 0) {
        return;
    }
    
    // Remove namespace prefix and convert to file path
    $relative_class = substr($class, 11); // Remove 'HappyPlace\'
    $file_path = str_replace(['\\', '_'], ['/', '-'], strtolower($relative_class));
    
    // Add class- prefix for WordPress naming convention
    $path_parts = explode('/', $file_path);
    $class_name = array_pop($path_parts);
    $class_file = 'class-' . $class_name . '.php';
    
    if (!empty($path_parts)) {
        $full_path = HP_INCLUDES_DIR . implode('/', $path_parts) . '/' . $class_file;
    } else {
        $full_path = HP_INCLUDES_DIR . $class_file;
    }
    
    if (file_exists($full_path)) {
        require_once $full_path;
    }
});

// =============================================================================
// PLUGIN INITIALIZATION
// =============================================================================

/**
 * Load text domain for translations
 */
function hp_load_textdomain() {
    load_plugin_textdomain('happy-place', false, dirname(plugin_basename(__FILE__)) . '/languages');
    hp_log('Text domain loaded', 'info', 'TEXTDOMAIN');
}

/**
 * Main plugin initialization
 */
function hp_init_plugin() {
    hp_log('Starting plugin initialization', 'info', 'INIT');
    
    // Check dependencies before loading
    if (!hp_check_dependencies()) {
        hp_log('Dependencies check failed', 'error', 'INIT');
        return;
    }
    
    hp_log('Dependencies check passed', 'info', 'INIT');
    
    // Load essential components
    hp_load_core_components();
    
    hp_log('Plugin initialized successfully', 'info', 'INIT');
}

/**
 * Load core plugin components with better error handling
 */
function hp_load_core_components() {
    hp_log('Starting to load core components', 'info', 'COMPONENT');
    
    // Component loading configuration
    $components = [
        // Core components (critical - must load)
        'core' => [
            'class-post-types.php' => ['class' => 'HappyPlace\\Core\\Post_Types', 'init' => true],
            'class-taxonomies.php' => ['class' => 'HappyPlace\\Core\\Taxonomies', 'init' => true],
            'class-field-mapper.php' => ['class' => 'HappyPlace\\Core\\Field_Mapper', 'init' => false],
            'class-acf-manager.php' => ['class' => 'HappyPlace\\Core\\ACF_Manager', 'init' => true],
            'class-acf-json-loader.php' => ['class' => 'HappyPlace\\Core\\ACF_JSON_Loader', 'init' => false],
            'core/class-fresh-start.php' => ['class' => 'HappyPlace\\Core\\Fresh_Start', 'init' => false],
        ],
        
        // Optional components (nice to have)
        'optional' => [
            'core/class-config-sync-manager.php' => ['class' => 'HappyPlace\\Core\\Config_Sync_Manager', 'init' => true],
            'core/class-listing-automation.php' => ['class' => 'HappyPlace\\Core\\Listing_Automation', 'init' => false],
            'core/class-agent-automation.php' => ['class' => 'HappyPlace\\Core\\Agent_Automation', 'init' => false],
            'core/class-assets-manager.php' => ['class' => 'HappyPlace\\Core\\Assets_Manager', 'init' => true],
            'api/class-rest-api.php' => ['class' => 'HappyPlace\\Api\\REST_API', 'init' => false],
            'api/ajax/class-listing-ajax.php' => ['class' => 'HappyPlace\\Api\\Ajax\\Listing_Ajax', 'init_static' => true],
            'admin/class-admin-menu.php' => ['class' => 'HappyPlace\\Admin\\Admin_Menu', 'init' => true],
            'integrations/class-airtable-sync-manager.php' => ['class' => 'HappyPlace\\Integrations\\Airtable_Sync_Manager', 'init' => true],
            'integrations/class-followup-boss-integration.php' => ['class' => 'HappyPlace\\Integrations\\FollowUp_Boss_Integration', 'init' => true],
            'utilities/class-sample-data-generator.php' => ['class' => 'HappyPlace\\Utilities\\Sample_Data_Generator', 'init' => false],
            'forms/class-lead-capture.php' => ['class' => 'HappyPlace\\Forms\\Lead_Capture', 'init' => true],
            'debug/class-listing-debug.php' => ['class' => 'HappyPlace\\Debug\\Listing_Debug', 'init_static' => true],
        ]
    ];
    
    $loaded = [];
    $failed = [];
    
    // Load core components first
    foreach ($components['core'] as $file => $config) {
        $result = hp_load_component('core/' . $file, $config, true);
        if ($result) {
            $loaded[] = $file;
        } else {
            $failed[] = $file;
        }
    }
    
    // Load optional components
    foreach ($components['optional'] as $file => $config) {
        $result = hp_load_component($file, $config, false);
        if ($result) {
            $loaded[] = $file;
        }
    }
    
    // Log results
    hp_log('Components loaded: ' . count($loaded), 'info', 'COMPONENT');
    if (!empty($failed)) {
        hp_log('Failed to load critical components: ' . implode(', ', $failed), 'error', 'COMPONENT');
    }
    
    // Initialize AJAX handlers if the class was loaded
    if (class_exists('HappyPlace\\Api\\Ajax\\Listing_Ajax')) {
        HappyPlace\Api\Ajax\Listing_Ajax::init();
        hp_log('Listing AJAX handlers initialized', 'info', 'COMPONENT');
    }
}

/**
 * Load a single component with error handling
 */
function hp_load_component($file, $config, $is_critical = false) {
    $file_path = HP_INCLUDES_DIR . $file;
    
    if (!file_exists($file_path)) {
        hp_log("Component file not found: {$file}", $is_critical ? 'error' : 'warning', 'COMPONENT');
        
        if ($is_critical) {
            add_action('admin_notices', function() use ($file) {
                echo '<div class="error"><p>';
                echo sprintf(__('Happy Place: Critical component missing: %s', 'happy-place'), $file);
                echo '</p></div>';
            });
        }
        
        return false;
    }
    
    try {
        require_once $file_path;
        hp_log("Loaded component file: {$file}", 'info', 'COMPONENT');
        
        // Initialize if needed
        if (!empty($config['class'])) {
            $class_name = $config['class'];
            
            if (class_exists($class_name)) {
                // Handle static initialization
                if (!empty($config['init_static'])) {
                    if (method_exists($class_name, 'init')) {
                        call_user_func([$class_name, 'init']);
                        hp_log("Static init called for {$class_name}", 'info', 'COMPONENT');
                    }
                }
                // Handle instance initialization
                elseif (!empty($config['init'])) {
                    if (method_exists($class_name, 'get_instance')) {
                        $instance = call_user_func([$class_name, 'get_instance']);
                        
                        if ($instance && method_exists($instance, 'init')) {
                            $instance->init();
                            hp_log("Instance init called for {$class_name}", 'info', 'COMPONENT');
                        }
                    }
                }
                
                return true;
            } else {
                hp_log("Class not found after loading: {$class_name}", 'warning', 'COMPONENT');
            }
        }
        
        return true;
        
    } catch (\Exception $e) {
        hp_log("Error loading component {$file}: " . $e->getMessage(), $is_critical ? 'error' : 'warning', 'COMPONENT');
        
        if ($is_critical) {
            add_action('admin_notices', function() use ($file, $e) {
                echo '<div class="error"><p>';
                echo sprintf(__('Happy Place: Failed to load %s - %s', 'happy-place'), $file, $e->getMessage());
                echo '</p></div>';
            });
        }
        
        return false;
    }
}

/**
 * Create agent dashboard page if it doesn't exist
 */
function hp_create_dashboard_page() {
    $existing_page = get_page_by_path('agent-dashboard');
    
    if (!$existing_page) {
        $page_data = [
            'post_title'     => 'Agent Dashboard',
            'post_name'      => 'agent-dashboard',
            'post_content'   => '<!-- Agent Dashboard Content -->',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => get_current_user_id() ?: 1,
            'page_template'  => 'template-agent-dashboard.php'
        ];
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id && !is_wp_error($page_id)) {
            update_option('hp_dashboard_page_id', $page_id);
            hp_log('Dashboard page created with ID: ' . $page_id, 'info', 'SETUP');
        }
    }
}

/**
 * Ensure dashboard page exists
 */
function hp_ensure_dashboard_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $existing_page = get_page_by_path('agent-dashboard');
    if (!$existing_page) {
        hp_create_dashboard_page();
    }
}

// =============================================================================
// ACTIVATION/DEACTIVATION HOOKS
// =============================================================================

/**
 * Plugin activation
 */
register_activation_hook(__FILE__, function() {
    // Create database tables if needed
    do_action('hp_activate');
    
    // Create agent dashboard page
    hp_create_dashboard_page();
    
    // Create default taxonomy terms if the class exists
    $terms_file = HP_INCLUDES_DIR . 'utilities/class-default-terms.php';
    if (file_exists($terms_file)) {
        require_once $terms_file;
        if (class_exists('HappyPlace\\Utilities\\Default_Terms')) {
            HappyPlace\Utilities\Default_Terms::create_default_terms();
        }
    }
    
    // Set flag to flush rewrite rules
    add_option('hp_flush_rewrite_rules', true);
    
    // Set default options
    add_option('hp_version', HP_VERSION);
    add_option('hp_activated_time', current_time('timestamp'));
    
    hp_log('Plugin activated', 'info', 'ACTIVATION');
});

/**
 * Plugin deactivation
 */
register_deactivation_hook(__FILE__, function() {
    // Clean up temporary data
    do_action('hp_deactivate');
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    hp_log('Plugin deactivated', 'info', 'DEACTIVATION');
});

// =============================================================================
// HOOKS AND FILTERS
// =============================================================================

// Initialize plugin
add_action('init', 'hp_load_textdomain', 1);
add_action('init', 'hp_init_plugin', 5);
add_action('init', 'hp_ensure_dashboard_page', 20);

// Add script localization for AJAX
add_action('wp_enqueue_scripts', function() {
    // Dashboard scripts
    if (is_page('agent-dashboard')) {
        wp_register_script('happy-place-dashboard', HP_DIST_URL . 'dashboard.js', ['jquery'], HP_VERSION, true);
        wp_localize_script('happy-place-dashboard', 'hp_dashboard', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_dashboard_nonce'),
            'current_user' => get_current_user_id(),
            'strings' => [
                'confirm_delete' => __('Are you sure you want to delete this listing?', 'happy-place'),
                'loading' => __('Loading...', 'happy-place'),
                'error' => __('An error occurred. Please try again.', 'happy-place'),
            ]
        ]);
        wp_enqueue_script('happy-place-dashboard');
    }
    
    // Frontend scripts
    wp_register_script('happy-place-frontend', HP_DIST_URL . 'frontend.js', ['jquery'], HP_VERSION, true);
    wp_localize_script('happy-place-frontend', 'hp_frontend', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hp_frontend_nonce'),
    ]);
    wp_enqueue_script('happy-place-frontend');
});

// Admin scripts
add_action('admin_enqueue_scripts', function() {
    wp_register_script('happy-place-admin', HP_DIST_URL . 'admin.js', ['jquery'], HP_VERSION, true);
    wp_localize_script('happy-place-admin', 'hp_admin', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hp_dashboard_nonce'),
    ]);
    wp_enqueue_script('happy-place-admin');
});

// Early AJAX hooks for integrations
add_action('wp_ajax_hpt_airtable_test_connection', 'hp_ensure_airtable_ajax');
add_action('wp_ajax_hpt_airtable_sync_from_airtable', 'hp_ensure_airtable_ajax');
add_action('wp_ajax_hpt_airtable_sync_to_airtable', 'hp_ensure_airtable_ajax');
add_action('wp_ajax_hp_test_followup_boss_connection', 'hp_ensure_followup_boss_ajax');
add_action('wp_ajax_hp_sync_lead_to_followup_boss', 'hp_ensure_followup_boss_ajax');

/**
 * Ensure Airtable AJAX handlers are loaded
 */
function hp_ensure_airtable_ajax() {
    if (!class_exists('HappyPlace\\Integrations\\Airtable_Sync_Manager')) {
        $airtable_file = HP_INCLUDES_DIR . 'integrations/class-airtable-sync-manager.php';
        if (file_exists($airtable_file)) {
            require_once $airtable_file;
        }
    }
    
    if (class_exists('HappyPlace\\Integrations\\Airtable_Sync_Manager')) {
        $manager = HappyPlace\Integrations\Airtable_Sync_Manager::get_instance();
        
        $action = $_REQUEST['action'] ?? '';
        switch ($action) {
            case 'hpt_airtable_test_connection':
                if (method_exists($manager, 'handle_test_connection')) {
                    $manager->handle_test_connection();
                }
                break;
            case 'hpt_airtable_sync_from_airtable':
                if (method_exists($manager, 'handle_sync_from_airtable')) {
                    $manager->handle_sync_from_airtable();
                }
                break;
            case 'hpt_airtable_sync_to_airtable':
                if (method_exists($manager, 'handle_sync_to_airtable')) {
                    $manager->handle_sync_to_airtable();
                }
                break;
        }
    } else {
        wp_send_json_error(['message' => 'Airtable Sync Manager not available']);
    }
}

/**
 * Ensure FollowUp Boss AJAX handlers are loaded
 */
function hp_ensure_followup_boss_ajax() {
    if (!class_exists('HappyPlace\\Integrations\\FollowUp_Boss_Integration')) {
        $followup_boss_file = HP_INCLUDES_DIR . 'integrations/class-followup-boss-integration.php';
        if (file_exists($followup_boss_file)) {
            require_once $followup_boss_file;
        }
    }
    
    if (class_exists('HappyPlace\\Integrations\\FollowUp_Boss_Integration')) {
        $integration = HappyPlace\Integrations\FollowUp_Boss_Integration::get_instance();
        
        $action = $_REQUEST['action'] ?? '';
        switch ($action) {
            case 'hp_test_followup_boss_connection':
                if (method_exists($integration, 'test_connection')) {
                    $integration->test_connection();
                }
                break;
            case 'hp_sync_lead_to_followup_boss':
                if (method_exists($integration, 'sync_lead')) {
                    $integration->sync_lead();
                }
                break;
        }
    } else {
        wp_send_json_error(['message' => 'FollowUp Boss Integration not available']);
    }
}

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

/**
 * Get the plugin instance (for external access)
 */
function hp_get_plugin_instance() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = [
            'version' => HP_VERSION,
            'dir' => HP_PLUGIN_DIR,
            'url' => HP_PLUGIN_URL,
            'debug' => HP_DEBUG,
        ];
    }
    
    return $instance;
}

/**
 * Check if a component is loaded
 */
function hp_is_component_loaded($class_name) {
    return class_exists($class_name);
}

// End of file - happy-place.php