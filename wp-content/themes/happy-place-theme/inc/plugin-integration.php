<?php
/**
 * Plugin Integration
 * 
 * Handles integration with WordPress plugins and the Happy Place Plugin
 *
 * @package HappyPlaceTheme
 * @version 2.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Happy Place Plugin Integration
 */
function hpt_plugin_integration_setup() {
    // Check if Happy Place Plugin is active
    if (class_exists('HappyPlace\\Plugin')) {
        // Plugin is active, add theme integration hooks
        add_action('init', 'hpt_plugin_theme_integration');
    } else {
        // Plugin is not active, show admin notice
        add_action('admin_notices', 'hpt_plugin_missing_notice');
    }
    
    // ACF Integration
    if (function_exists('get_field')) {
        add_action('init', 'hpt_acf_integration');
    }
    
    // Contact Form 7 Integration
    if (function_exists('wpcf7')) {
        add_action('init', 'hpt_cf7_integration');
    }
    
    // Yoast SEO Integration
    if (defined('WPSEO_VERSION')) {
        add_action('init', 'hpt_yoast_integration');
    }
}
add_action('after_setup_theme', 'hpt_plugin_integration_setup');

/**
 * Happy Place Plugin theme integration
 */
function hpt_plugin_theme_integration() {
    // Add theme-specific actions for plugin integration
    // This is where we can add hooks to interact with the plugin
}

/**
 * Plugin missing notice
 */
function hpt_plugin_missing_notice() {
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Happy Place Theme:</strong> The Happy Place Plugin is not active. Some theme features may not work properly.</p>';
        echo '</div>';
    }
}

/**
 * ACF Integration
 */
function hpt_acf_integration() {
    // Add ACF theme integration if needed
    // This is for additional ACF functionality beyond the plugin
}

/**
 * Contact Form 7 Integration
 */
function hpt_cf7_integration() {
    // Add Contact Form 7 styling integration
    add_action('wp_enqueue_scripts', function() {
        wp_dequeue_style('contact-form-7'); // Remove default CF7 styles
        // Our framework will handle the styling
    });
}

/**
 * Yoast SEO Integration
 */
function hpt_yoast_integration() {
    // Add Yoast SEO theme integration
    // This can include custom schema for real estate listings
}