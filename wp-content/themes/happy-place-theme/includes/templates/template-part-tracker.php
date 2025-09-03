<?php
/**
 * Template Part Asset Tracker
 * 
 * Helper functions for tracking template part usage and loading assets
 * 
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register a template part as being used
 * 
 * This should be called at the top of each template part file
 * to ensure proper asset loading.
 * 
 * @param string $template_part Template part identifier (e.g., 'sections/hero')
 */
function hph_register_template_part($template_part) {
    // Get the assets service
    $theme_instance = HPH_Theme::instance();
    $assets_service = $theme_instance->get_service('assets');
    
    if ($assets_service && method_exists($assets_service, 'track_template_part')) {
        $assets_service->track_template_part($template_part);
    }
    
    // Also store globally for other functions
    global $hph_active_template_parts;
    if (!isset($hph_active_template_parts)) {
        $hph_active_template_parts = array();
    }
    $hph_active_template_parts[] = $template_part;
}

/**
 * Get list of active template parts
 * 
 * @return array List of template parts in use
 */
function hph_get_active_template_parts() {
    global $hph_active_template_parts;
    return isset($hph_active_template_parts) ? $hph_active_template_parts : array();
}

/**
 * Check if a specific template part is active
 * 
 * @param string $template_part Template part to check
 * @return bool True if template part is active
 */
function hph_is_template_part_active($template_part) {
    $active_parts = hph_get_active_template_parts();
    return in_array($template_part, $active_parts);
}

/**
 * Load template part with automatic asset registration
 * 
 * Enhanced version of get_template_part() that automatically
 * registers the template part for asset loading.
 * 
 * @param string $slug Template slug
 * @param string $name Template name (optional)
 * @param array $args Arguments to pass to template (optional)
 */
function hph_load_template_part($slug, $name = '', $args = array()) {
    // Build template part identifier
    $template_part = $slug;
    if (!empty($name)) {
        $template_part .= '/' . $name;
    }
    
    // Register the template part
    hph_register_template_part($template_part);
    
    // Load the template part
    if (!empty($args) && function_exists('get_template_part')) {
        // WordPress 5.5+ supports args parameter
        get_template_part($slug, $name, $args);
    } else {
        // Fallback for older WordPress versions
        if (!empty($args)) {
            // Make args available to template
            set_query_var('template_args', $args);
        }
        get_template_part($slug, $name);
    }
}

/**
 * Preload assets for known template parts
 * 
 * Call this early in page load to preload assets for template parts
 * that you know will be used.
 * 
 * @param array $template_parts Array of template part identifiers
 */
function hph_preload_template_assets($template_parts) {
    foreach ($template_parts as $template_part) {
        hph_register_template_part($template_part);
    }
}