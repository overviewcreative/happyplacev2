<?php
/**
 * Template Utilities
 * 
 * Additional utility functions for templates and theme functionality
 *
 * @package HappyPlaceTheme
 * @version 2.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get template part with data
 */
function hpt_get_template_part($slug, $name = null, $args = array()) {
    if (!empty($args)) {
        extract($args);
    }
    
    get_template_part($slug, $name);
}

/**
 * Conditional asset loading
 */
function hpt_is_real_estate_page() {
    return is_singular(array('listing', 'agent', 'community', 'open_house')) || 
           is_post_type_archive(array('listing', 'agent', 'community', 'open_house'));
}

/**
 * Get page context for asset loading
 */
function hpt_get_page_context() {
    if (is_singular('listing') || is_post_type_archive('listing')) {
        return 'listings';
    }
    
    if ((function_exists('hph_is_dashboard') && hph_is_dashboard()) || is_page_template('page-agent-dashboard.php')) {
        return 'dashboard';
    }
    
    if (is_front_page()) {
        return 'homepage';
    }
    
    return 'general';
}

/**
 * Enhanced body classes
 */
function hpt_body_classes($classes) {
    // Add page context class
    $classes[] = 'hpt-context-' . hpt_get_page_context();
    
    // Add real estate page class
    if (hpt_is_real_estate_page()) {
        $classes[] = 'hpt-real-estate-page';
    }
    
    // Add plugin status class
    if (is_happy_place_plugin_active()) {
        $classes[] = 'hpt-plugin-active';
    } else {
        $classes[] = 'hpt-plugin-inactive';
    }
    
    return $classes;
}
add_filter('body_class', 'hpt_body_classes');

/**
 * Enhanced post classes
 */
function hpt_post_classes($classes, $class, $post_id) {
    $post_type = get_post_type($post_id);
    
    if (in_array($post_type, array('listing', 'agent', 'community', 'open_house'))) {
        $classes[] = 'hpt-' . $post_type;
        
        // Add status classes for listings
        if ($post_type === 'listing') {
            $status = get_field('listing_status', $post_id);
            if ($status) {
                $classes[] = 'hpt-listing-status-' . $status;
            }
        }
    }
    
    return $classes;
}
add_filter('post_class', 'hpt_post_classes', 10, 3);

/**
 * Template hierarchy modifications
 */
function hpt_template_hierarchy($template) {
    // Enhanced template hierarchy for real estate post types
    return $template;
}
add_filter('template_include', 'hpt_template_hierarchy');

/**
 * Excerpt modifications for real estate content
 */
function hpt_custom_excerpt_length($length) {
    if (hpt_is_real_estate_page()) {
        return 25; // Shorter excerpts for property listings
    }
    return $length;
}
add_filter('excerpt_length', 'hpt_custom_excerpt_length');

/**
 * Custom excerpt more text
 */
function hpt_excerpt_more($more) {
    if (hpt_is_real_estate_page()) {
        return '...';
    }
    return $more;
}
add_filter('excerpt_more', 'hpt_excerpt_more');