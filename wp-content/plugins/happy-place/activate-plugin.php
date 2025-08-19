<?php
/**
 * Direct Plugin Activation Helper
 * 
 * Access this file directly to activate the Happy Place plugin
 * URL: /wp-content/plugins/happy-place/activate-plugin.php
 */

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

// Check if user is logged in and is admin
if (!is_user_logged_in() || !current_user_can('activate_plugins')) {
    wp_die('You must be logged in as an administrator to activate plugins.');
}

$plugin_file = 'happy-place/happy-place.php';
$result = activate_plugin($plugin_file);

if (is_wp_error($result)) {
    echo '<h2>Activation Failed</h2>';
    echo '<p>Error: ' . $result->get_error_message() . '</p>';
    echo '<pre>';
    print_r($result);
    echo '</pre>';
} else {
    echo '<h2>✅ Happy Place Plugin Activated Successfully!</h2>';
    echo '<p>The plugin has been activated.</p>';
    echo '<h3>Next Steps:</h3>';
    echo '<ul>';
    echo '<li><a href="' . admin_url('admin.php?page=happy-place') . '">Go to Happy Place Admin</a></li>';
    echo '<li><a href="' . admin_url('plugins.php') . '">View All Plugins</a></li>';
    echo '<li><a href="' . home_url('/agent-dashboard/') . '">Visit Agent Dashboard</a></li>';
    echo '</ul>';
    
    // Also try to ensure the admin menu is registered
    if (class_exists('HappyPlace\Plugin')) {
        $plugin = HappyPlace\Plugin::get_instance();
        echo '<p>Plugin class loaded successfully.</p>';
    }
}

// Debug info
echo '<hr>';
echo '<h3>Debug Information:</h3>';
echo '<p>Plugin File: ' . $plugin_file . '</p>';
echo '<p>Plugin Path: ' . WP_PLUGIN_DIR . '/' . $plugin_file . '</p>';
echo '<p>File Exists: ' . (file_exists(WP_PLUGIN_DIR . '/' . $plugin_file) ? 'Yes' : 'No') . '</p>';

$active_plugins = get_option('active_plugins');
echo '<p>Is in active plugins list: ' . (in_array($plugin_file, $active_plugins) ? 'Yes' : 'No') . '</p>';

if (in_array($plugin_file, $active_plugins)) {
    echo '<p style="color: green;"><strong>✓ Plugin is in the active plugins list</strong></p>';
}