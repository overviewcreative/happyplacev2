<?php
/**
 * Custom Post Type Bridge Functions
 *
 * Bridge functions to connect theme to plugin CPTService
 * Provides backward compatibility and clean interface
 *
 * @package HappyPlaceTheme
 * @subpackage Bridge
 * @since 4.3.0 - Created for CPT delegation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get blog post card data via plugin service
 */
function hpt_get_card_data_blog_post($post_id) {
    if (class_exists('HappyPlace\Services\CPTService')) {
        $cpt_service = new HappyPlace\Services\CPTService();
        return $cpt_service->get_blog_post_card_data($post_id);
    }

    // Fallback for compatibility
    return [];
}

/**
 * Check if a post type is managed by the plugin CPT service
 */
function hpt_is_plugin_managed_post_type($post_type) {
    if (class_exists('HappyPlace\Services\CPTService')) {
        $cpt_service = new HappyPlace\Services\CPTService();
        return $cpt_service->is_managed_post_type($post_type);
    }

    return false;
}

/**
 * Get all plugin-managed post types
 */
function hpt_get_plugin_managed_post_types() {
    if (class_exists('HappyPlace\Services\CPTService')) {
        $cpt_service = new HappyPlace\Services\CPTService();
        return $cpt_service->get_managed_post_types();
    }

    return [];
}

/**
 * Get all plugin-managed taxonomies
 */
function hpt_get_plugin_managed_taxonomies() {
    if (class_exists('HappyPlace\Services\CPTService')) {
        $cpt_service = new HappyPlace\Services\CPTService();
        return $cpt_service->get_managed_taxonomies();
    }

    return [];
}