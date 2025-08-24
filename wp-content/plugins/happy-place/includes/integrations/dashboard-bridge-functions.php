<?php
/**
 * Dashboard Bridge Functions
 * 
 * Global functions that provide compatibility between theme templates
 * and plugin services. These functions can be called directly from
 * theme templates and will route through the plugin service layer.
 * 
 * @package HappyPlace\Integrations
 * @version 4.0.0
 */

use HappyPlace\Integrations\DashboardBridge;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get user listings via service layer
 * Enhanced version of hpt_get_user_listings that uses plugin services
 * 
 * @param int|null $user_id User ID
 * @param array $args Query arguments
 * @return array Listings data
 */
function hpt_get_user_listings_via_service($user_id = null, $args = []) {
    $bridge = DashboardBridge::get_instance();
    
    $default_args = [
        'page' => 1,
        'per_page' => 20,
        'search' => '',
        'status' => 'all',
        'sort' => 'date-desc',
        'user_id' => $user_id ?: get_current_user_id()
    ];
    
    $args = wp_parse_args($args, $default_args);
    
    // Use the private method through reflection (for internal use)
    $reflection = new ReflectionClass($bridge);
    $method = $reflection->getMethod('get_dashboard_listings');
    $method->setAccessible(true);
    
    return $method->invoke($bridge, $args);
}

/**
 * Get enhanced dashboard statistics via service layer
 * 
 * @param int|null $user_id User ID
 * @return array Dashboard statistics
 */
function hpt_get_dashboard_stats_enhanced($user_id = null) {
    $bridge = DashboardBridge::get_instance();
    
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    // Use reflection to access private method
    $reflection = new ReflectionClass($bridge);
    $method = $reflection->getMethod('get_enhanced_listing_stats');
    $method->setAccessible(true);
    
    return $method->invoke($bridge, $user_id);
}

/**
 * Get listing form HTML for dashboard modals
 * 
 * @param int|null $listing_id Listing ID for editing
 * @return string Form HTML
 */
function hpt_get_listing_form_html($listing_id = null) {
    $bridge = DashboardBridge::get_instance();
    return $bridge->get_listing_form_html($listing_id);
}

/**
 * Get single listing data formatted for dashboard
 * 
 * @param int $listing_id Listing ID
 * @return array Listing data
 */
function hpt_get_listing_dashboard_data($listing_id) {
    $bridge = DashboardBridge::get_instance();
    
    // Use reflection to access private method
    $reflection = new ReflectionClass($bridge);
    $method = $reflection->getMethod('get_dashboard_listing_data');
    $method->setAccessible(true);
    
    return $method->invoke($bridge, $listing_id);
}

/**
 * Create listing via service layer
 * For programmatic listing creation from theme
 * 
 * @param array $data Listing data
 * @return int|WP_Error Listing ID or error
 */
function hpt_create_listing_via_service($data) {
    try {
        $listing_service = new \HappyPlace\Services\ListingService();
        $listing_service->init();
        
        return $listing_service->create_listing($data);
    } catch (Exception $e) {
        return new WP_Error('service_error', $e->getMessage());
    }
}

/**
 * Update listing via service layer
 * 
 * @param int $listing_id Listing ID
 * @param array $data Updated data
 * @return int|WP_Error Listing ID or error
 */
function hpt_update_listing_via_service($listing_id, $data) {
    try {
        $listing_service = new \HappyPlace\Services\ListingService();
        $listing_service->init();
        
        return $listing_service->update_listing($listing_id, $data);
    } catch (Exception $e) {
        return new WP_Error('service_error', $e->getMessage());
    }
}

/**
 * Delete listing via service layer
 * 
 * @param int $listing_id Listing ID
 * @param bool $force_delete Whether to force delete
 * @return bool|WP_Error True on success or error
 */
function hpt_delete_listing_via_service($listing_id, $force_delete = false) {
    try {
        $listing_service = new \HappyPlace\Services\ListingService();
        $listing_service->init();
        
        return $listing_service->delete_listing($listing_id, $force_delete);
    } catch (Exception $e) {
        return new WP_Error('service_error', $e->getMessage());
    }
}

/**
 * Validate listing data via service layer
 * 
 * @param array $data Listing data
 * @param string $context Context (create|update)
 * @return bool|WP_Error True if valid or error
 */
function hpt_validate_listing_data($data, $context = 'create') {
    try {
        $form_service = new \HappyPlace\Services\FormService();
        $form_service->init();
        
        return $form_service->validate_form($data, 'listing');
    } catch (Exception $e) {
        return new WP_Error('validation_error', $e->getMessage());
    }
}

/**
 * Get CSV import interface HTML
 * 
 * @return string Import interface HTML
 */
function hpt_get_import_interface_html() {
    $bridge = DashboardBridge::get_instance();
    return $bridge->get_import_interface_html();
}

/**
 * Process CSV import via service layer
 * 
 * @param string $file_path CSV file path
 * @param array $mapping Field mapping
 * @return array|WP_Error Import results
 */
function hpt_process_csv_import($file_path, $mapping) {
    try {
        $import_service = new \HappyPlace\Services\ImportService();
        $import_service->init();
        
        return $import_service->import_csv($file_path, $mapping);
    } catch (Exception $e) {
        return new WP_Error('import_error', $e->getMessage());
    }
}

/**
 * Get import templates
 * 
 * @return array Available mapping templates
 */
function hpt_get_import_templates() {
    try {
        $import_service = new \HappyPlace\Services\ImportService();
        $import_service->init();
        
        return $import_service->get_mapping_templates();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Enhanced listing count with service layer optimizations
 * 
 * @param int|null $user_id User ID
 * @param string $status Listing status filter
 * @param array $args Additional arguments
 * @return int Listing count
 */
function hpt_count_user_listings_enhanced($user_id = null, $status = 'all', $args = []) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $query_args = [
        'post_type' => 'listing',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => false
    ];
    
    // Add status filter
    if ($status !== 'all') {
        $query_args['post_status'] = is_array($status) ? $status : [$status];
    } else {
        $query_args['post_status'] = ['publish', 'draft', 'private'];
    }
    
    // Add user filter (unless admin)
    if (!current_user_can('manage_options') && $user_id) {
        $query_args['author'] = $user_id;
    }
    
    // Merge additional args
    if (!empty($args)) {
        $query_args = array_merge($query_args, $args);
    }
    
    $query = new WP_Query($query_args);
    return $query->found_posts;
}

/**
 * Get listing performance metrics
 * 
 * @param int $listing_id Listing ID
 * @return array Performance metrics
 */
function hpt_get_listing_performance($listing_id) {
    $post = get_post($listing_id);
    
    if (!$post || $post->post_type !== 'listing') {
        return [];
    }
    
    // Calculate days on market
    $created = new DateTime($post->post_date);
    $now = new DateTime();
    $days_on_market = $now->diff($created)->days;
    
    // Get view count (if tracking is enabled)
    $view_count = get_post_meta($listing_id, '_listing_views', true) ?: 0;
    
    // Get inquiry count
    $inquiry_count = get_post_meta($listing_id, '_listing_inquiries', true) ?: 0;
    
    return [
        'days_on_market' => $days_on_market,
        'view_count' => $view_count,
        'inquiry_count' => $inquiry_count,
        'last_updated' => $post->post_modified,
        'performance_score' => hpt_calculate_listing_performance_score($listing_id, [
            'days_on_market' => $days_on_market,
            'view_count' => $view_count,
            'inquiry_count' => $inquiry_count
        ])
    ];
}

/**
 * Calculate listing performance score
 * 
 * @param int $listing_id Listing ID
 * @param array $metrics Performance metrics
 * @return float Performance score (0-100)
 */
function hpt_calculate_listing_performance_score($listing_id, $metrics) {
    $score = 50; // Base score
    
    // Adjust based on days on market (fewer days = higher score)
    if ($metrics['days_on_market'] < 30) {
        $score += 20;
    } elseif ($metrics['days_on_market'] < 60) {
        $score += 10;
    } elseif ($metrics['days_on_market'] > 120) {
        $score -= 20;
    }
    
    // Adjust based on views (more views = higher score)
    if ($metrics['view_count'] > 100) {
        $score += 20;
    } elseif ($metrics['view_count'] > 50) {
        $score += 10;
    }
    
    // Adjust based on inquiries (more inquiries = higher score)
    if ($metrics['inquiry_count'] > 10) {
        $score += 30;
    } elseif ($metrics['inquiry_count'] > 5) {
        $score += 15;
    } elseif ($metrics['inquiry_count'] > 0) {
        $score += 10;
    }
    
    // Ensure score stays within bounds
    return max(0, min(100, $score));
}

/**
 * Get dashboard nonce for AJAX calls
 * 
 * @return string Nonce value
 */
function hpt_get_dashboard_nonce() {
    return wp_create_nonce('hph_dashboard_nonce');
}

/**
 * Check if plugin services are available
 * 
 * @return bool True if services are available
 */
function hpt_services_available() {
    return class_exists('HappyPlace\\Services\\ListingService') &&
           class_exists('HappyPlace\\Services\\FormService') &&
           class_exists('HappyPlace\\Services\\ImportService');
}

/**
 * Get service integration status for debugging
 * 
 * @return array Integration status
 */
function hpt_get_service_integration_status() {
    return [
        'bridge_available' => class_exists('HappyPlace\\Integrations\\DashboardBridge'),
        'services_available' => hpt_services_available(),
        'listing_service' => class_exists('HappyPlace\\Services\\ListingService'),
        'form_service' => class_exists('HappyPlace\\Services\\FormService'),
        'import_service' => class_exists('HappyPlace\\Services\\ImportService'),
        'plugin_version' => defined('HP_VERSION') ? HP_VERSION : 'unknown',
        'integration_timestamp' => current_time('mysql')
    ];
}