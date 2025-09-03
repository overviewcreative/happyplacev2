<?php
/**
 * User Interaction AJAX Handlers
 * 
 * Handles user interactions including:
 * - Favorite listings toggle
 * - View tracking
 * - Property sharing
 * - User engagement analytics
 * 
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle toggle listing favorite
 */
if (!function_exists('handle_toggle_listing_favorite')) {
    add_action('wp_ajax_toggle_listing_favorite', 'handle_toggle_listing_favorite');

    function handle_toggle_listing_favorite() {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to save favorites');
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hph_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    $listing_id = intval($_POST['listing_id']);
    $user_id = get_current_user_id();
    
    // Validate listing
    $listing = get_post($listing_id);
    if (!$listing || $listing->post_type !== 'listing') {
        wp_send_json_error('Invalid listing');
    }
    
    // Get current favorites
    $favorites = get_user_meta($user_id, 'favorite_listings', true);
    if (!is_array($favorites)) {
        $favorites = array();
    }
    
    // Toggle favorite status
    $is_favorite = in_array($listing_id, $favorites);
    
    if ($is_favorite) {
        // Remove from favorites
        $favorites = array_diff($favorites, array($listing_id));
        $action = 'removed';
    } else {
        // Add to favorites
        $favorites[] = $listing_id;
        $action = 'added';
    }
    
    // Update user meta
    $updated = update_user_meta($user_id, 'favorite_listings', $favorites);
    
    if ($updated !== false) {
        // Update listing favorite count (optional)
        $favorite_count = get_post_meta($listing_id, 'favorite_count', true) ?: 0;
        $new_count = $is_favorite ? max(0, $favorite_count - 1) : $favorite_count + 1;
        update_post_meta($listing_id, 'favorite_count', $new_count);
        
        // Log the action (optional)
        do_action('hph_listing_favorite_toggled', $listing_id, $user_id, $action);
        
        wp_send_json_success(array(
            'is_favorite' => !$is_favorite,
            'action' => $action,
            'count' => count($favorites)
        ));
    } else {
        wp_send_json_error('Error updating favorites');
    }
    }
}

/**
 * Get listing quick stats for AJAX requests
 */
if (!function_exists('handle_get_listing_stats')) {
    add_action('wp_ajax_get_listing_stats', 'handle_get_listing_stats');
    add_action('wp_ajax_nopriv_get_listing_stats', 'handle_get_listing_stats');

    function handle_get_listing_stats() {
    $listing_id = intval($_POST['listing_id']);
    
    if (!$listing_id) {
        wp_send_json_error('Invalid listing ID');
    }
    
    $listing = get_post($listing_id);
    if (!$listing || $listing->post_type !== 'listing') {
        wp_send_json_error('Invalid listing');
    }
    
    // Gather stats
    $stats = array(
        'views_total' => hpt_get_listing_views($listing_id),
        'views_week' => hpt_get_listing_views($listing_id, 'week'),
        'views_month' => hpt_get_listing_views($listing_id, 'month'),
        'favorite_count' => get_post_meta($listing_id, 'favorite_count', true) ?: 0,
        'inquiry_count' => get_post_meta($listing_id, 'inquiry_count', true) ?: 0,
        'is_featured' => hpt_is_listing_featured($listing_id),
        'status' => hpt_get_listing_status($listing_id),
        'days_active' => floor((time() - strtotime($listing->post_date)) / (60 * 60 * 24)),
    );
    
    wp_send_json_success($stats);
    }
}

/**
 * Handle listing view tracking
 */
if (!function_exists('handle_hph_track_listing_view')) {
    add_action('wp_ajax_hph_track_listing_view', 'handle_hph_track_listing_view');
    add_action('wp_ajax_nopriv_hph_track_listing_view', 'handle_hph_track_listing_view');

    function handle_hph_track_listing_view() {
    $listing_id = intval($_POST['listing_id']);
    $user_id = get_current_user_id();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    if (!$listing_id) {
        wp_send_json_error(['message' => 'Invalid listing ID']);
    }
    
    // Prevent duplicate views from same user/IP within 30 minutes
    $cache_key = 'hph_view_' . $listing_id . '_' . ($user_id ?: md5($ip_address));
    if (get_transient($cache_key)) {
        wp_send_json_success(['message' => 'View already tracked']);
    }
    
    // Track the view
    if (function_exists('hpt_track_listing_view')) {
        hpt_track_listing_view($listing_id, $user_id, $ip_address);
    } else {
        // Fallback: update simple view count
        $view_count = get_post_meta($listing_id, 'view_count', true) ?: 0;
        update_post_meta($listing_id, 'view_count', $view_count + 1);
    }
    
    // Set cache to prevent duplicate tracking
    set_transient($cache_key, true, 30 * MINUTE_IN_SECONDS);
    
    wp_send_json_success(['message' => 'View tracked']);
    }
}

/**
 * Handle nearby places search for maps
 */
if (!function_exists('handle_hph_get_nearby_places')) {
    add_action('wp_ajax_hph_get_nearby_places', 'handle_hph_get_nearby_places');
    add_action('wp_ajax_nopriv_hph_get_nearby_places', 'handle_hph_get_nearby_places');

    function handle_hph_get_nearby_places() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hph_map_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);
    $category = sanitize_text_field($_POST['category']);
    $radius = intval($_POST['radius']) ?: 2000; // Default 2km radius
    
    if (!$lat || !$lng) {
        wp_send_json_error(['message' => 'Invalid coordinates']);
    }
    
    // Category to Google Places type mapping
    $category_types = array(
        'schools' => ['school', 'university'],
        'shopping' => ['shopping_mall', 'store', 'supermarket'],
        'restaurants' => ['restaurant', 'meal_takeaway', 'food'],
        'healthcare' => ['hospital', 'doctor', 'pharmacy'],
        'parks' => ['park', 'amusement_park', 'gym'],
        'transit' => ['transit_station', 'bus_station', 'subway_station'],
        'entertainment' => ['movie_theater', 'night_club', 'bowling_alley'],
        'services' => ['bank', 'gas_station', 'car_repair']
    );
    
    $place_types = $category_types[$category] ?? ['establishment'];
    
    // Cache the results for 1 hour
    $cache_key = 'hph_nearby_' . md5($lat . $lng . $category . $radius);
    $cached_results = get_transient($cache_key);
    
    if ($cached_results !== false) {
        wp_send_json_success($cached_results);
    }
    
    // If Google Places API integration is available, use it
    if (function_exists('hpt_get_nearby_places')) {
        $places = hpt_get_nearby_places($lat, $lng, $place_types, $radius);
    } else {
        // Return mock data for development
        $places = array(
            'results' => array(),
            'status' => 'MOCK_DATA',
            'message' => 'Google Places API not configured'
        );
    }
    
    // Cache the results
    set_transient($cache_key, $places, HOUR_IN_SECONDS);
    
    wp_send_json_success($places);
    }
}