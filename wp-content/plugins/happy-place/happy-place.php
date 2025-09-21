<?php
/**
 * Plugin Name: Happy Place
 * Plugin URI: https://happyplace.com
 * Description: Comprehensive Real Estate Management Plugin
 * Version: 1.0.0
 * Author: Happy Place Team
 * Author URI: https://happyplace.com
 * Text Domain: happy-place
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * 
 * @package HappyPlace
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Debug constants are controlled by wp-config.php environment settings

// Define plugin constants
define('HP_VERSION', '1.0.0');
define('HP_PLUGIN_FILE', __FILE__);
define('HP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HP_PLUGIN_BASENAME', plugin_basename(__FILE__));

// More constants
define('HP_INCLUDES_DIR', HP_PLUGIN_DIR . 'includes/');
define('HP_TEMPLATES_DIR', HP_PLUGIN_DIR . 'templates/');
define('HP_ASSETS_DIR', HP_PLUGIN_DIR . 'assets/');
define('HP_ASSETS_URL', HP_PLUGIN_URL . 'assets/');
define('HP_CONFIG_DIR', HP_PLUGIN_DIR . 'config/');

// Debug mode based on WordPress environment
define('HP_DEBUG', defined('WP_DEBUG') && WP_DEBUG);

// Database
global $wpdb;
define('HP_TABLE_PREFIX', $wpdb->prefix . 'hp_');

// Security
define('HP_AJAX_NONCE_KEY', 'hp_ajax_security');

// Try to load the bootstrap file
$bootstrap_file = HP_INCLUDES_DIR . 'class-bootstrap.php';
if (!file_exists($bootstrap_file)) {
    wp_die('Happy Place Plugin Error: Bootstrap file not found at ' . $bootstrap_file);
}

require_once $bootstrap_file;

// Initialize the plugin
try {
    \HappyPlace\Bootstrap::init();
} catch (Exception $e) {
    if (HP_DEBUG) {
        error_log('Happy Place Fatal Error: ' . $e->getMessage());
    }
    wp_die('Happy Place Plugin Error: ' . $e->getMessage());
}