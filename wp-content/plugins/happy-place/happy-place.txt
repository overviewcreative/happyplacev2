<?php
/**
 * Plugin Name: Happy Place
 * Plugin URI: https://theparkergroup.com
 * Description: Advanced real estate platform with comprehensive property management, agent tools, marketing suite, and MLS compliance
 * Version: 4.0.0
 * Author: The Parker Group
 * Author URI: https://theparkergroup.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: happy-place
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// PLUGIN CONSTANTS
// =============================================================================

// Plugin Version
if (!defined('HP_VERSION')) {
    define('HP_VERSION', '4.0.0');
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

// Template paths
if (!defined('HP_TEMPLATES_DIR')) {
    define('HP_TEMPLATES_DIR', HP_PLUGIN_DIR . 'templates/');
}

if (!defined('HP_VIEWS_DIR')) {
    define('HP_VIEWS_DIR', HP_PLUGIN_DIR . 'views/');
}

// Configuration paths
if (!defined('HP_CONFIG_DIR')) {
    define('HP_CONFIG_DIR', HP_PLUGIN_DIR . 'config/');
}

// Environment Constants
if (!defined('HP_DEBUG')) {
    define('HP_DEBUG', defined('WP_DEBUG') && WP_DEBUG);
}

if (!defined('HP_CACHE_ENABLED')) {
    define('HP_CACHE_ENABLED', !HP_DEBUG);
}

// Database Table Prefix
global $wpdb;
if (!defined('HP_TABLE_PREFIX')) {
    define('HP_TABLE_PREFIX', $wpdb->prefix . 'hp_');
}

// Plugin Settings
if (!defined('HP_CAPABILITY')) {
    define('HP_CAPABILITY', 'manage_options');
}

if (!defined('HP_AJAX_NONCE_KEY')) {
    define('HP_AJAX_NONCE_KEY', 'hp_ajax_nonce');
}

// =============================================================================
// EARLY ERROR HANDLING & LOGGING
// =============================================================================

/**
 * Central error logging function (available globally)
 */
if (!function_exists('hp_log')) {
    function hp_log($message, $level = 'info', $context = '') {
        // Only log if debugging is enabled
        if (!HP_DEBUG && $level !== 'critical') {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $context_str = $context ? "[{$context}] " : '';
        $log_message = "HP [{$timestamp}] [{$level}] {$context_str}{$message}";
        
        // Log to file
        error_log($log_message);
        
        // For critical errors, also try to store in database
        if ($level === 'critical') {
            try {
                update_option('hp_last_critical_error', [
                    'message' => $message,
                    'context' => $context,
                    'timestamp' => $timestamp
                ], false);
            } catch (Exception $e) {
                // Fallback to error log only
                error_log("HP CRITICAL (failed to store): {$message}");
            }
        }
    }
}

/**
 * Check if plugin dependencies are met
 */
if (!function_exists('hp_check_dependencies')) {
    function hp_check_dependencies() {
        $errors = [];
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $errors[] = sprintf(
                __('Happy Place requires PHP 7.4 or higher. You are running PHP %s.', 'happy-place'),
                PHP_VERSION
            );
        }
        
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            $errors[] = sprintf(
                __('Happy Place requires WordPress 6.0 or higher. You are running WordPress %s.', 'happy-place'),
                get_bloginfo('version')
            );
        }
        
        // Check for required PHP extensions
        $required_extensions = ['json', 'mysqli', 'curl'];
        foreach ($required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $errors[] = sprintf(
                    __('Happy Place requires the PHP %s extension.', 'happy-place'),
                    $extension
                );
            }
        }
        
        // Display errors if any
        if (!empty($errors)) {
            if (is_admin()) {
                add_action('admin_notices', function() use ($errors) {
                    ?>
                    <div class="notice notice-error">
                        <p><strong><?php _e('Happy Place Plugin Cannot Activate', 'happy-place'); ?></strong></p>
                        <?php foreach ($errors as $error): ?>
                            <p>• <?php echo esc_html($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                    <?php
                });
            }
            
            hp_log('Dependency check failed: ' . implode(', ', $errors), 'critical', 'BOOTSTRAP');
            return false;
        }
        
        return true;
    }
}

// =============================================================================
// AUTOLOADER
// =============================================================================

/**
 * PSR-4 compliant autoloader for Happy Place classes
 * Handles our lowercase hyphenated file naming convention
 */
spl_autoload_register(function($class) {
    // Check if it's our namespace
    if (strpos($class, 'HappyPlace\\') !== 0) {
        return;
    }
    
    // Remove namespace prefix
    $relative_class = substr($class, 11); // Remove 'HappyPlace\'
    
    // Convert namespace separators to directory separators
    $path_parts = explode('\\', $relative_class);
    
    // Get the class name (last part)
    $class_name = array_pop($path_parts);
    
    // Convert directory names to lowercase
    $path_parts = array_map('strtolower', $path_parts);
    
    // Convert class name to hyphenated lowercase with 'class-' prefix
    $file_name = 'class-' . strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $class_name)) . '.php';
    
    // Build the file path
    if (!empty($path_parts)) {
        $file_path = HP_INCLUDES_DIR . implode('/', $path_parts) . '/' . $file_name;
    } else {
        $file_path = HP_INCLUDES_DIR . $file_name;
    }
    
    // Load file if it exists
    if (file_exists($file_path)) {
        require_once $file_path;
        return;
    }
    
    // Try alternative naming patterns for backwards compatibility
    $alt_patterns = [
        // Try without 'class-' prefix
        HP_INCLUDES_DIR . implode('/', $path_parts) . '/' . strtolower($class_name) . '.php',
        // Try exact class name
        HP_INCLUDES_DIR . implode('/', $path_parts) . '/' . $class_name . '.php',
    ];
    
    foreach ($alt_patterns as $alt_path) {
        if (file_exists($alt_path)) {
            require_once $alt_path;
            return;
        }
    }
    
    // Log failed autoload attempts in debug mode
    if (HP_DEBUG) {
        hp_log("Failed to autoload class: {$class} (tried: {$file_path})", 'debug', 'AUTOLOADER');
    }
});

// =============================================================================
// LOAD CORE DEPENDENCIES
// =============================================================================

// These core files must be loaded manually before the plugin initializes
$core_dependencies = [
    'core/class-container.php',
    'core/class-service-provider.php',
    'core/class-error-handler.php',
    'core/class-component-loader.php',
    'class-plugin.php',
];

foreach ($core_dependencies as $file) {
    $file_path = HP_INCLUDES_DIR . $file;
    
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        hp_log("Core dependency not found: {$file}", 'critical', 'BOOTSTRAP');
        
        if (is_admin()) {
            add_action('admin_notices', function() use ($file) {
                ?>
                <div class="notice notice-error">
                    <p>
                        <strong><?php _e('Happy Place Plugin Error:', 'happy-place'); ?></strong>
                        <?php printf(__('Required file missing: %s', 'happy-place'), esc_html($file)); ?>
                    </p>
                </div>
                <?php
            });
        }
        
        return; // Stop loading if core dependency is missing
    }
}

// =============================================================================
// PLUGIN INITIALIZATION
// =============================================================================

/**
 * Get main plugin instance
 * 
 * @return HappyPlace\Plugin
 */
function hp_plugin() {
    return HappyPlace\Plugin::instance();
}

/**
 * Get service from container
 * 
 * @param string $service Service ID
 * @return mixed
 */
function hp_service($service) {
    try {
        return hp_plugin()->get($service);
    } catch (Exception $e) {
        hp_log("Failed to get service: {$service} - " . $e->getMessage(), 'error', 'SERVICE');
        return null;
    }
}

/**
 * Check if plugin is ready
 * 
 * @return bool
 */
function hp_is_ready() {
    return hp_plugin()->is_booted();
}

/**
 * Get plugin version
 * 
 * @return string
 */
function hp_version() {
    return HP_VERSION;
}

/**
 * Get plugin URL
 * 
 * @param string $path Optional path to append
 * @return string
 */
function hp_url($path = '') {
    return hp_plugin()->url($path);
}

/**
 * Get plugin path
 * 
 * @param string $path Optional path to append
 * @return string
 */
function hp_path($path = '') {
    return hp_plugin()->path($path);
}

// =============================================================================
// INITIALIZATION HOOKS
// =============================================================================

/**
 * Initialize plugin when WordPress loads plugins
 */
add_action('plugins_loaded', function() {
    // Check dependencies first
    if (!hp_check_dependencies()) {
        return;
    }
    
    // Initialize the plugin
    try {
        $plugin = hp_plugin();
        
        if (!$plugin->initialize()) {
            throw new Exception('Plugin initialization failed');
        }
        
        hp_log('Happy Place Plugin initialized successfully', 'info', 'BOOTSTRAP');
        
    } catch (Exception $e) {
        hp_log('Failed to initialize plugin: ' . $e->getMessage(), 'critical', 'BOOTSTRAP');
        
        // Show admin notice
        if (is_admin()) {
            add_action('admin_notices', function() use ($e) {
                ?>
                <div class="notice notice-error">
                    <p>
                        <strong><?php _e('Happy Place Plugin Error:', 'happy-place'); ?></strong>
                        <?php echo esc_html($e->getMessage()); ?>
                    </p>
                    <?php if (HP_DEBUG): ?>
                        <details>
                            <summary><?php _e('Debug Information', 'happy-place'); ?></summary>
                            <pre><?php echo esc_html($e->getTraceAsString()); ?></pre>
                        </details>
                    <?php endif; ?>
                </div>
                <?php
            });
        }
        
        // Optionally deactivate the plugin on critical failure
        if (get_option('hp_deactivate_on_error', false)) {
            deactivate_plugins(plugin_basename(__FILE__));
        }
    }
}, 5); // Priority 5 to load early

// =============================================================================
// ACTIVATION/DEACTIVATION HOOKS
// =============================================================================

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, function() {
    hp_log('Plugin activation triggered', 'info', 'ACTIVATION');
    
    // Check dependencies
    if (!hp_check_dependencies()) {
        wp_die(
            __('Happy Place Plugin cannot be activated. Please check the requirements.', 'happy-place'),
            __('Activation Failed', 'happy-place'),
            ['back_link' => true]
        );
    }
    
    // Set activation flag
    update_option('hp_plugin_activated', time());
    update_option('hp_plugin_version', HP_VERSION);
    
    // Schedule activation redirect
    set_transient('hp_activation_redirect', true, 30);
    
    // Trigger plugin activation
    try {
        if (class_exists('HappyPlace\\Plugin')) {
            hp_plugin()->activate();
        }
    } catch (Exception $e) {
        hp_log('Activation error: ' . $e->getMessage(), 'error', 'ACTIVATION');
        
        wp_die(
            sprintf(
                __('Plugin activation failed: %s', 'happy-place'),
                esc_html($e->getMessage())
            ),
            __('Activation Error', 'happy-place'),
            ['back_link' => true]
        );
    }
});

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, function() {
    hp_log('Plugin deactivation triggered', 'info', 'DEACTIVATION');
    
    try {
        if (class_exists('HappyPlace\\Plugin')) {
            hp_plugin()->deactivate();
        }
    } catch (Exception $e) {
        hp_log('Deactivation error: ' . $e->getMessage(), 'error', 'DEACTIVATION');
    }
    
    // Clear scheduled events
    wp_clear_scheduled_hook('hp_hourly_cron');
    wp_clear_scheduled_hook('hp_daily_cron');
    wp_clear_scheduled_hook('hp_weekly_cron');
    
    // Clear transients
    delete_transient('hp_activation_redirect');
    
    // Update deactivation time
    update_option('hp_plugin_deactivated', time());
});

/**
 * Plugin uninstall hook (in separate uninstall.php file)
 */
// Note: Uninstall logic should be in uninstall.php for security

// =============================================================================
// ADMIN REDIRECT AFTER ACTIVATION
// =============================================================================

/**
 * Redirect to plugin settings after activation
 */
add_action('admin_init', function() {
    // Check if we should redirect
    if (get_transient('hp_activation_redirect')) {
        delete_transient('hp_activation_redirect');
        
        // Don't redirect on bulk activation or if activating from network admin
        if (is_network_admin() || isset($_GET['activate-multi'])) {
            return;
        }
        
        // Redirect to plugin dashboard
        wp_safe_redirect(admin_url('admin.php?page=happy-place'));
        exit;
    }
});

// =============================================================================
// COMPATIBILITY CHECKS
// =============================================================================

/**
 * Check for plugin conflicts
 */
add_action('admin_init', function() {
    // Check for known conflicting plugins
    $conflicts = [];
    
    // Check for other real estate plugins that might conflict
    $conflicting_plugins = [
        'estatik/estatik.php' => 'Estatik',
        'essential-real-estate/essential-real-estate.php' => 'Essential Real Estate',
        'realty-portal/realty-portal.php' => 'Realty Portal',
    ];
    
    foreach ($conflicting_plugins as $plugin => $name) {
        if (is_plugin_active($plugin)) {
            $conflicts[] = $name;
        }
    }
    
    if (!empty($conflicts)) {
        add_action('admin_notices', function() use ($conflicts) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php _e('Happy Place Plugin Warning:', 'happy-place'); ?></strong>
                    <?php _e('The following plugins may conflict with Happy Place:', 'happy-place'); ?>
                </p>
                <ul>
                    <?php foreach ($conflicts as $plugin): ?>
                        <li>• <?php echo esc_html($plugin); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><?php _e('Consider deactivating these plugins for optimal performance.', 'happy-place'); ?></p>
            </div>
            <?php
        });
    }
});

// =============================================================================
// DEVELOPMENT HELPERS (only in debug mode)
// =============================================================================

if (HP_DEBUG) {
    /**
     * Debug function to dump and die
     */
    if (!function_exists('hp_dd')) {
        function hp_dd($data, $label = '') {
            echo '<pre style="background:#f5f5f5;padding:10px;border:1px solid #ddd;">';
            if ($label) {
                echo '<strong>' . esc_html($label) . ':</strong><br>';
            }
            var_dump($data);
            echo '</pre>';
            die();
        }
    }
    
    /**
     * Debug function to dump (without dying)
     */
    if (!function_exists('hp_dump')) {
        function hp_dump($data, $label = '') {
            echo '<pre style="background:#f5f5f5;padding:10px;border:1px solid #ddd;margin:10px 0;">';
            if ($label) {
                echo '<strong>' . esc_html($label) . ':</strong><br>';
            }
            var_dump($data);
            echo '</pre>';
        }
    }
    
    /**
     * Add debug information to admin footer
     */
    add_action('admin_footer', function() {
        $plugin = hp_plugin();
        ?>
        <script>
            console.log('Happy Place Plugin Debug Info:', {
                version: '<?php echo HP_VERSION; ?>',
                initialized: <?php echo $plugin->is_initialized() ? 'true' : 'false'; ?>,
                booted: <?php echo $plugin->is_booted() ? 'true' : 'false'; ?>,
                php_version: '<?php echo PHP_VERSION; ?>',
                wp_version: '<?php echo get_bloginfo('version'); ?>'
            });
        </script>
        <?php
    });
}

// =============================================================================
// LOAD TEXT DOMAIN
// =============================================================================

/**
 * Load plugin text domain for translations
 */
add_action('init', function() {
    load_plugin_textdomain(
        'happy-place',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
});

// =============================================================================
// PLUGIN ACTION LINKS
// =============================================================================

/**
 * Add action links to plugins page
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        admin_url('admin.php?page=happy-place'),
        __('Settings', 'happy-place')
    );
    
    $docs_link = sprintf(
        '<a href="%s" target="_blank">%s</a>',
        'https://docs.happyplaceplugin.com',
        __('Docs', 'happy-place')
    );
    
    array_unshift($links, $settings_link);
    $links[] = $docs_link;
    
    return $links;
});

/**
 * Add meta links to plugins page
 */
add_filter('plugin_row_meta', function($links, $file) {
    if ($file === plugin_basename(__FILE__)) {
        $links[] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            'https://support.happyplaceplugin.com',
            __('Support', 'happy-place')
        );
        
        $links[] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            'https://happyplaceplugin.com/changelog',
            __('Changelog', 'happy-place')
        );
    }
    
    return $links;
}, 10, 2);

// Plugin is ready to go! 🚀