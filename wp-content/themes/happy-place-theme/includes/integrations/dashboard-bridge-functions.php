<?php
/**
 * Dashboard Bridge Functions for Plugin Service Integration
 * 
 * Provides global functions for the theme to interact with plugin services.
 * These functions act as a bridge between the theme and the plugin's service layer.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if plugin services are available
 * 
 * @return bool True if plugin services are active and available
 */
function hpt_services_available() {
    return function_exists('\\HappyPlace\\Integrations\\DashboardBridge::get_instance') &&
           class_exists('\\HappyPlace\\Services\\ListingService') &&
           get_option('hp_services_initialized', false);
}

/**
 * Create listing via plugin service
 * 
 * @param array $data Listing data array
 * @return int|WP_Error Listing ID on success, WP_Error on failure
 */
function hpt_create_listing_via_service($data) {
    if (!hpt_services_available()) {
        return new WP_Error('service_unavailable', 'Plugin services are not available');
    }
    
    try {
        $bridge = \HappyPlace\Integrations\DashboardBridge::get_instance();
        return $bridge->create_listing_via_service($data);
    } catch (Exception $e) {
        return new WP_Error('service_error', 'Service error: ' . $e->getMessage());
    }
}

/**
 * Update listing via plugin service
 * 
 * @param int   $listing_id Listing ID to update
 * @param array $data       Updated listing data
 * @return bool|WP_Error    True on success, WP_Error on failure
 */
function hpt_update_listing_via_service($listing_id, $data) {
    if (!hpt_services_available()) {
        return new WP_Error('service_unavailable', 'Plugin services are not available');
    }
    
    try {
        $bridge = \HappyPlace\Integrations\DashboardBridge::get_instance();
        return $bridge->update_listing_via_service($listing_id, $data);
    } catch (Exception $e) {
        return new WP_Error('service_error', 'Service error: ' . $e->getMessage());
    }
}

/**
 * Delete listing via plugin service
 * 
 * @param int $listing_id Listing ID to delete
 * @return bool|WP_Error  True on success, WP_Error on failure
 */
function hpt_delete_listing_via_service($listing_id) {
    if (!hpt_services_available()) {
        return new WP_Error('service_unavailable', 'Plugin services are not available');
    }
    
    try {
        $bridge = \HappyPlace\Integrations\DashboardBridge::get_instance();
        return $bridge->delete_listing_via_service($listing_id);
    } catch (Exception $e) {
        return new WP_Error('service_error', 'Service error: ' . $e->getMessage());
    }
}

/**
 * Get user listings via plugin service
 * 
 * @param int|null $user_id User ID, defaults to current user
 * @param array    $args    Query arguments
 * @return array   Array of listing data
 */
function hpt_get_user_listings_via_service($user_id = null, $args = []) {
    if (!hpt_services_available()) {
        return [];
    }
    
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    try {
        $bridge = \HappyPlace\Integrations\DashboardBridge::get_instance();
        return $bridge->get_user_listings_via_service($user_id, $args);
    } catch (Exception $e) {
        if (function_exists('hp_log')) {
            hp_log('Get user listings via service error: ' . $e->getMessage(), 'error', 'dashboard');
        }
        return [];
    }
}

/**
 * Get enhanced dashboard statistics
 * 
 * @param int|null $user_id User ID, defaults to current user
 * @return array   Enhanced dashboard statistics
 */
function hpt_get_dashboard_stats_enhanced($user_id = null) {
    if (!hpt_services_available()) {
        return [];
    }
    
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    try {
        $bridge = \HappyPlace\Integrations\DashboardBridge::get_instance();
        return $bridge->get_enhanced_dashboard_stats($user_id);
    } catch (Exception $e) {
        if (function_exists('hp_log')) {
            hp_log('Get enhanced dashboard stats error: ' . $e->getMessage(), 'error', 'dashboard');
        }
        return [];
    }
}

/**
 * Get listing form HTML via plugin service
 * 
 * @param int|null $listing_id Listing ID for editing, null for new listing
 * @return string  Form HTML
 */
function hpt_get_listing_form_html($listing_id = null) {
    if (!hpt_services_available()) {
        return '';
    }
    
    try {
        $bridge = \HappyPlace\Integrations\DashboardBridge::get_instance();
        return $bridge->get_listing_form_html($listing_id);
    } catch (Exception $e) {
        if (function_exists('hp_log')) {
            hp_log('Get listing form HTML error: ' . $e->getMessage(), 'error', 'dashboard');
        }
        return '';
    }
}

/**
 * Get import interface HTML via plugin service
 * 
 * @return string Import interface HTML
 */
function hpt_get_import_interface_html() {
    if (!hpt_services_available()) {
        return '';
    }
    
    try {
        $bridge = \HappyPlace\Integrations\DashboardBridge::get_instance();
        return $bridge->get_import_interface_html();
    } catch (Exception $e) {
        if (function_exists('hp_log')) {
            hp_log('Get import interface HTML error: ' . $e->getMessage(), 'error', 'dashboard');
        }
        return '';
    }
}

/**
 * Get service integration status for debugging
 * 
 * @return array Status information
 */
function hpt_get_service_integration_status() {
    $status = [
        'plugin_active' => class_exists('\\HappyPlace\\Bootstrap'),
        'services_class_exists' => class_exists('\\HappyPlace\\Services\\ListingService'),
        'bridge_class_exists' => class_exists('\\HappyPlace\\Integrations\\DashboardBridge'),
        'functions_loaded' => function_exists('hpt_services_available'),
        'services_available' => hpt_services_available(),
        'services_initialized' => get_option('hp_services_initialized', false)
    ];
    
    $status['all_ready'] = array_reduce($status, function($carry, $item) {
        return $carry && $item;
    }, true);
    
    return $status;
}