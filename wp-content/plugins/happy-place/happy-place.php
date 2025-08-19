<?php
/**
 * Plugin Name: Happy Place
 * Plugin URI: https://theparkergroup.com
 * Description: Advanced real estate platform with comprehensive property management, agent tools, marketing suite, and MLS compliance
 * Version: 3.0.0
 * Author: The Parker Group
 * Author URI: https://theparkergroup.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: happy-place
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 8.0
 * Network: false
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// DEFINE PLUGIN CONSTANTS
// =============================================================================

// Plugin Version
if (!defined('HP_VERSION')) {
    define('HP_VERSION', '3.0.0');
}

// Plugin File Path
if (!defined('HP_PLUGIN_FILE')) {
    define('HP_PLUGIN_FILE', __FILE__);
}

// Plugin Directory Paths
if (!defined('HP_PLUGIN_DIR')) {
    define('HP_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('HP_PLUGIN_URL')) {
    define('HP_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('HP_INCLUDES_DIR')) {
    define('HP_INCLUDES_DIR', HP_PLUGIN_DIR . 'includes/');
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

// =============================================================================
// DEPENDENCY CHECKS
// =============================================================================

/**
 * Check for required dependencies
 */
function hp_check_dependencies() {
    $errors = [];
    
    \hp_log('Checking dependencies...', 'info', 'DEPENDENCIES');
    \hp_log('WordPress version: ' . get_bloginfo('version'), 'info', 'DEPENDENCIES');
    \hp_log('PHP version: ' . PHP_VERSION, 'info', 'DEPENDENCIES');
    
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), '6.0', '<')) {
        $errors[] = __('Happy Place requires WordPress 6.0 or higher.', 'happy-place');
        \hp_log('WordPress version check failed', 'error', 'DEPENDENCIES');
    }
    
    // Check PHP version (relaxed for compatibility)
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $errors[] = __('Happy Place requires PHP 7.4 or higher.', 'happy-place');
        \hp_log('PHP version check failed', 'error', 'DEPENDENCIES');
    }
    
    // Check for ACF (Pro or Free) - make it optional for now
    if (!class_exists('ACF') && !function_exists('acf')) {
        // Just log a warning, don't fail
        \hp_log('ACF not found - plugin will work with limited functionality', 'warning', 'DEPENDENCIES');
        \hp_log('ACF check - class_exists(ACF): ' . (class_exists('ACF') ? 'true' : 'false'), 'debug', 'DEPENDENCIES');
        \hp_log('ACF check - function_exists(acf): ' . (function_exists('acf') ? 'true' : 'false'), 'debug', 'DEPENDENCIES');
        // Don't add to errors array to allow plugin to load
    }
    
    if (!empty($errors)) {
        add_action('admin_notices', function() use ($errors) {
            echo '<div class="error"><p>';
            echo implode('<br>', $errors);
            echo '</p></div>';
        });
        \hp_log('Dependencies check failed with ' . count($errors) . ' errors', 'error', 'DEPENDENCIES');
        return false;
    }
    
    \hp_log('All dependencies met', 'info', 'DEPENDENCIES');
    return true;
}

// =============================================================================
// AUTOLOADER
// =============================================================================

/**
 * PSR-4 compliant autoloader for Happy Place Pro classes
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
 * Load text domain
 */
function hp_load_textdomain() {
    load_plugin_textdomain('happy-place', false, dirname(plugin_basename(__FILE__)) . '/languages');
    if (function_exists('hp_log')) {
        hp_log('Text domain loaded', 'info', 'TEXTDOMAIN');
    }
}

/**
 * Main plugin initialization
 */
function hp_init_plugin() {
    \hp_log('Starting plugin initialization', 'info', 'INIT');
    
    // Check dependencies before loading
    if (!hp_check_dependencies()) {
        \hp_log('Dependencies check failed', 'error', 'INIT');
        return;
    }
    
    \hp_log('Dependencies check passed', 'info', 'INIT');
    
    // Load essential components directly
    hp_load_core_components();
    
    \hp_log('Plugin initialized successfully', 'info', 'INIT');
}

/**
 * Load core plugin components
 */
function hp_load_core_components() {
    \hp_log('Starting to load core components', 'info', 'COMPONENT');
    
    // Define core components to load
    $components = [
        'core/class-post-types.php',
        'core/class-taxonomies.php',
        'core/class-acf-manager.php',
        'core/class-config-sync-manager.php',
        'core/class-listing-automation.php',
        'core/class-agent-automation.php',
        'core/class-assets-manager.php',
        'api/class-rest-api.php',
        'admin/class-admin-menu.php',
        'integrations/class-airtable-sync-manager.php',
        'dashboard/class-dashboard-manager.php',
        'dashboard/class-frontend-admin-dashboard.php',
        'dashboard/class-frontend-ajax-handlers.php',
        'api/ajax/class-dashboard-ajax.php',
        'api/ajax/class-agent-ajax.php',
        'utilities/class-sample-data-generator.php',
        'forms/class-lead-capture.php',
    ];
    
    $critical_components = ['core/class-post-types.php', 'core/class-taxonomies.php'];
    $loaded_components = [];
    
    foreach ($components as $component) {
        $file_path = HP_INCLUDES_DIR . $component;
        \hp_log("Checking component: {$component}", 'debug', 'COMPONENT');
        
        if (file_exists($file_path)) {
            try {
                require_once $file_path;
                $loaded_components[] = $component;
                \hp_log("Loaded component: {$component}", 'info', 'COMPONENT');
            } catch (\Exception $e) {
                \hp_log("Error loading component {$component}: " . $e->getMessage(), 'error', 'COMPONENT');
                if (in_array($component, $critical_components)) {
                    wp_die('Critical component failed to load: ' . $component);
                }
            }
        } else {
            \hp_log("Component not found: {$component} at {$file_path}", 'error', 'COMPONENT');
            
            // Critical components must exist
            if (in_array($component, $critical_components)) {
                add_action('admin_notices', function() use ($component) {
                    echo '<div class="error"><p>';
                    echo sprintf(__('Happy Place critical component missing: %s', 'happy-place'), $component);
                    echo '</p></div>';
                });
                wp_die('Critical component missing: ' . $component);
            }
        }
    }
    
    \hp_log('Finished loading components, starting initialization', 'info', 'COMPONENT');
    
    // Initialize components with error handling
    hp_safe_init_component('HappyPlace\\Core\\Post_Types', 'Post Types');
    hp_safe_init_component('HappyPlace\\Core\\Taxonomies', 'Taxonomies');
    hp_safe_init_component('HappyPlace\\Core\\ACF_Manager', 'ACF Manager');
    hp_safe_init_component('HappyPlace\\Core\\Config_Sync_Manager', 'Config Sync Manager');
    hp_safe_init_component('HappyPlace\\Core\\Listing_Automation', 'Listing Automation');
    hp_safe_init_component('HappyPlace\\Core\\Agent_Automation', 'Agent Automation');
    // hp_safe_init_component('HappyPlace\\Core\\Assets_Manager', 'Assets Manager'); // Disabled - missing dist files
    hp_safe_init_component('HappyPlace\\Admin\\Admin_Menu', 'Admin Menu');
    hp_safe_init_component('HappyPlace\\Integrations\\Airtable_Sync_Manager', 'Airtable Sync', false);
    hp_safe_init_component('HappyPlace\\Dashboard\\Dashboard_Manager', 'Dashboard', false);
    hp_safe_init_component('HappyPlace\\Dashboard\\Frontend_Admin_Dashboard', 'Frontend Admin Dashboard', false);
    hp_safe_init_component('HappyPlace\\API\\REST_API', 'REST API', false);
    hp_safe_init_component('HappyPlace\\Forms\\Lead_Capture', 'Lead Capture', false);
    hp_safe_init_component('HappyPlace\\API\\Ajax\\Dashboard_Ajax', 'Dashboard Ajax');
    hp_safe_init_component('HappyPlace\\API\\Ajax\\Agent_Ajax', 'Agent Ajax');
}

/**
 * Safely initialize a component with error handling
 */
function hp_safe_init_component($class_name, $component_name, $call_init = true) {
    try {
        if (!class_exists($class_name)) {
            \hp_log("Component class not found: {$class_name}", 'error', 'INIT');
            return false;
        }
        
        $instance = $class_name::get_instance();
        
        if ($call_init && method_exists($instance, 'init')) {
            $instance->init();
        }
        
        \hp_log("{$component_name} initialized successfully", 'info', 'INIT');
        return true;
        
    } catch (\Exception $e) {
        \hp_log("Failed to initialize {$component_name}: " . $e->getMessage(), 'error', 'INIT');
        
        if (HP_DEBUG) {
            add_action('admin_notices', function() use ($component_name, $e) {
                echo '<div class="error"><p>';
                echo sprintf(__('Happy Place: Failed to initialize %s - %s', 'happy-place'), 
                    $component_name, $e->getMessage());
                echo '</p></div>';
            });
        }
        
        return false;
    }
}

// Hook into WordPress
add_action('init', 'hp_load_textdomain', 1); // Load text domain after basic setup
add_action('init', 'hp_init_plugin', 5);      // Initialize plugin components
add_action('init', 'hp_ensure_dashboard_page', 20); // Ensure dashboard page exists

// Debug file removed - core initialization is working properly

// Early hook for AJAX requests to ensure Airtable handlers are available
add_action('wp_ajax_hpt_airtable_test_connection', 'hp_ensure_airtable_ajax');
add_action('wp_ajax_hpt_airtable_sync_from_airtable', 'hp_ensure_airtable_ajax'); 
add_action('wp_ajax_hpt_airtable_sync_to_airtable', 'hp_ensure_airtable_ajax');

/**
 * Ensure Airtable AJAX handlers are loaded
 */
function hp_ensure_airtable_ajax() {
    // Load Airtable manager if not already loaded
    if (!class_exists('HappyPlace\\Integrations\\Airtable_Sync_Manager')) {
        $airtable_file = HP_INCLUDES_DIR . 'integrations/class-airtable-sync-manager.php';
        if (file_exists($airtable_file)) {
            require_once $airtable_file;
        }
    }
    
    // Get instance and let it handle the AJAX request
    if (class_exists('HappyPlace\\Integrations\\Airtable_Sync_Manager')) {
        $manager = \HappyPlace\Integrations\Airtable_Sync_Manager::get_instance();
        
        // Call the appropriate method based on the action
        $action = $_REQUEST['action'] ?? '';
        switch ($action) {
            case 'hpt_airtable_test_connection':
                $manager->handle_test_connection();
                break;
            case 'hpt_airtable_sync_from_airtable':
                $manager->handle_sync_from_airtable();
                break;
            case 'hpt_airtable_sync_to_airtable':
                $manager->handle_sync_to_airtable();
                break;
        }
    } else {
        wp_send_json_error(['message' => 'Airtable Sync Manager not available']);
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
    
    // Create default taxonomy terms
    require_once HP_INCLUDES_DIR . 'utilities/class-default-terms.php';
    \HappyPlace\Utilities\Default_Terms::create_default_terms();
    
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

/**
 * Ensure dashboard page exists (runs on init)
 */
function hp_ensure_dashboard_page() {
    // Only run for admin users and if page doesn't exist
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $existing_page = get_page_by_path('agent-dashboard');
    if (!$existing_page) {
        hp_create_dashboard_page();
    }
}

/**
 * Create agent dashboard page
 */
function hp_create_dashboard_page() {
    // Check if page already exists
    $existing_page = get_page_by_path('agent-dashboard');
    
    if (!$existing_page) {
        // Create the agent-dashboard page
        $page_data = [
            'post_title'     => 'Agent Dashboard',
            'post_name'      => 'agent-dashboard', 
            'post_content'   => '<!-- Agent Dashboard Content -->', // Content handled by template
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => get_current_user_id() ?: 1,
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'meta_input'     => [
                '_wp_page_template' => 'page-agent-dashboard.php'
            ]
        ];

        $page_id = wp_insert_post($page_data);

        if ($page_id && !is_wp_error($page_id)) {
            hp_log("Agent Dashboard page created (ID: $page_id)", 'info', 'ACTIVATION');
            
            // Flush rewrite rules
            flush_rewrite_rules();
        } else {
            hp_log('Failed to create Agent Dashboard page', 'error', 'ACTIVATION');
        }
    } else {
        // Make sure it has the right template
        update_post_meta($existing_page->ID, '_wp_page_template', 'page-agent-dashboard.php');
        hp_log("Agent Dashboard page already exists (ID: {$existing_page->ID})", 'info', 'ACTIVATION');
        
        // Flush rewrite rules anyway
        flush_rewrite_rules();
    }
}

/**
 * Plugin uninstall (handled in uninstall.php)
 */