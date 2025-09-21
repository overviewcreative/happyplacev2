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
 * Enhanced with new UserFavoritesService integration
 */
if (!function_exists('handle_toggle_listing_favorite')) {
    add_action('wp_ajax_toggle_listing_favorite', 'handle_toggle_listing_favorite');
    add_action('wp_ajax_nopriv_toggle_listing_favorite', 'handle_guest_favorite_attempt');

    function handle_toggle_listing_favorite() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to save favorites');
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        $listing_id = intval($_POST['listing_id']);
        
        // Try to use new UserFavoritesService if available
        if (class_exists('\HappyPlace\Services\UserFavoritesService')) {
            try {
                $favorites_service = new \HappyPlace\Services\UserFavoritesService();
                $favorites_service->init();
                
                $result = $favorites_service->toggle_favorite($listing_id);
                
                if ($result['success']) {
                    wp_send_json_success([
                        'is_favorite' => $result['is_favorited'],
                        'action' => $result['action'],
                        'count' => $result['count']
                    ]);
                } else {
                    wp_send_json_error($result['message']);
                }
                return;
            } catch (Exception $e) {
                error_log('UserFavoritesService error: ' . $e->getMessage());
                // Fall through to legacy implementation
            }
        }
        
        // Legacy implementation as fallback
        $user_id = get_current_user_id();
        
        // Validate listing
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            wp_send_json_error('Invalid listing');
            return;
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

/**
 * Handle guest favorite attempt - prompt for registration
 */
if (!function_exists('handle_guest_favorite_attempt')) {
    function handle_guest_favorite_attempt() {
        // Use LeadConversionService if available
        if (class_exists('\HappyPlace\Services\LeadConversionService')) {
            $conversion_service = new \HappyPlace\Services\LeadConversionService();
            $conversion_service->handle_guest_favorite_attempt();
            return;
        }
        
        // Fallback response
        wp_send_json_error([
            'message' => 'Please create an account to save your favorite properties',
            'requires_registration' => true,
            'benefits' => [
                'Save unlimited favorite properties',
                'Get email alerts for new matches',
                'Track your viewing history'
            ]
        ]);
    }
}

/**
 * Handle user engagement tracking
 */
if (!function_exists('handle_track_user_engagement')) {
    add_action('wp_ajax_track_engagement', 'handle_track_user_engagement');
    add_action('wp_ajax_nopriv_track_engagement', 'handle_track_user_engagement');
    
    function handle_track_user_engagement() {
        // Use UserEngagementService if available
        if (class_exists('\HappyPlace\Services\UserEngagementService')) {
            $engagement_service = new \HappyPlace\Services\UserEngagementService();
            $engagement_service->ajax_track_engagement();
            return;
        }
        
        wp_send_json_error(['message' => 'Engagement tracking not available']);
    }
}

/**
 * Handle save search requests
 */
if (!function_exists('handle_save_search_request')) {
    add_action('wp_ajax_save_search', 'handle_save_search_request');
    add_action('wp_ajax_nopriv_save_search', 'handle_guest_save_search');
    
    function handle_save_search_request() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please login to save searches']);
            return;
        }
        
        // Use SavedSearchesService if available
        if (class_exists('\HappyPlace\Services\SavedSearchesService')) {
            $search_service = new \HappyPlace\Services\SavedSearchesService();
            $search_service->ajax_save_search();
            return;
        }
        
        wp_send_json_error(['message' => 'Saved searches not available']);
    }
    
    function handle_guest_save_search() {
        // Use LeadConversionService if available
        if (class_exists('\HappyPlace\Services\LeadConversionService')) {
            $conversion_service = new \HappyPlace\Services\LeadConversionService();
            $conversion_service->handle_guest_save_search_attempt();
            return;
        }
        
        wp_send_json_error([
            'message' => 'Create a free account to save your search and get email alerts',
            'requires_registration' => true,
            'conversion_context' => 'save_search'
        ]);
    }
}

/**
 * Handle quick user registration
 */
if (!function_exists('handle_quick_registration')) {
    add_action('wp_ajax_nopriv_quick_register', 'handle_quick_registration');
    
    function handle_quick_registration() {
        // Use LeadConversionService if available
        if (class_exists('\HappyPlace\Services\LeadConversionService')) {
            $conversion_service = new \HappyPlace\Services\LeadConversionService();
            $conversion_service->ajax_quick_register();
            return;
        }
        
        wp_send_json_error(['message' => 'Registration service not available']);
    }
}

/**
 * Enhanced listing view tracking with engagement
 */
if (!function_exists('handle_enhanced_listing_view')) {
    add_action('wp_ajax_track_listing_view_enhanced', 'handle_enhanced_listing_view');
    add_action('wp_ajax_nopriv_track_listing_view_enhanced', 'handle_enhanced_listing_view');
    
    function handle_enhanced_listing_view() {
        $listing_id = intval($_POST['listing_id'] ?? 0);
        $view_time = intval($_POST['view_time'] ?? 0);
        $scroll_depth = intval($_POST['scroll_depth'] ?? 0);
        $user_id = get_current_user_id();
        
        if (!$listing_id) {
            wp_send_json_error(['message' => 'Invalid listing ID']);
            return;
        }
        
        // Track with engagement service if user is logged in
        if ($user_id && class_exists('\HappyPlace\Services\UserEngagementService')) {
            $engagement_service = new \HappyPlace\Services\UserEngagementService();
            $engagement_service->track_activity($user_id, 'listing_viewed', $listing_id, 'listing', [
                'view_time_seconds' => $view_time,
                'scroll_depth' => $scroll_depth,
                'source' => sanitize_text_field($_POST['source'] ?? 'direct')
            ]);
        }
        
        // Update listing view count
        $view_count = get_post_meta($listing_id, 'view_count', true) ?: 0;
        update_post_meta($listing_id, 'view_count', $view_count + 1);
        
        // Update daily view count
        $today = date('Y-m-d');
        $daily_views_key = "daily_views_{$today}";
        $daily_views = get_post_meta($listing_id, $daily_views_key, true) ?: 0;
        update_post_meta($listing_id, $daily_views_key, $daily_views + 1);
        
        wp_send_json_success([
            'message' => 'View tracked',
            'engagement_tracked' => $user_id ? true : false
        ]);
    }
}

/**
 * Lead Management AJAX Handlers
 * 
 * Handles lead CRUD operations for dashboard
 */

/**
 * Handle get lead details
 */
if (!function_exists('handle_get_lead_details')) {
    add_action('wp_ajax_get_lead_details', 'handle_get_lead_details');
    
    function handle_get_lead_details() {
        // Verify user permissions
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        $lead_id = intval($_POST['lead_id']);
        
        if (!$lead_id) {
            wp_send_json_error('Invalid lead ID');
            return;
        }
        
        global $wpdb;
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$leads_table} WHERE id = %d",
            $lead_id
        ));
        
        if (!$lead) {
            wp_send_json_error('Lead not found');
            return;
        }
        
        wp_send_json_success([
            'lead' => $lead
        ]);
    }
}

/**
 * Handle update lead status
 */
if (!function_exists('handle_update_lead_status')) {
    add_action('wp_ajax_update_lead_status', 'handle_update_lead_status');
    
    function handle_update_lead_status() {
        // Verify user permissions
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        $lead_id = intval($_POST['lead_id']);
        $new_status = sanitize_text_field($_POST['status']);
        
        if (!$lead_id || !$new_status) {
            wp_send_json_error('Missing required parameters');
            return;
        }
        
        // Valid status values
        $valid_statuses = ['new', 'contacted', 'qualified', 'hot', 'warm', 'cold', 'converted', 'lost'];
        
        if (!in_array($new_status, $valid_statuses)) {
            wp_send_json_error('Invalid status value');
            return;
        }
        
        global $wpdb;
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        $result = $wpdb->update(
            $leads_table,
            [
                'status' => $new_status,
                'last_contact' => current_time('mysql')
            ],
            ['id' => $lead_id],
            ['%s', '%s'],
            ['%d']
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to update lead status');
            return;
        }
        
        wp_send_json_success([
            'message' => 'Lead status updated successfully',
            'new_status' => $new_status
        ]);
    }
}

/**
 * Handle add lead note
 */
if (!function_exists('handle_add_lead_note')) {
    add_action('wp_ajax_add_lead_note', 'handle_add_lead_note');
    
    function handle_add_lead_note() {
        // Verify user permissions
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        $lead_id = intval($_POST['lead_id']);
        $note = sanitize_textarea_field($_POST['note']);
        
        if (!$lead_id || empty($note)) {
            wp_send_json_error('Missing required parameters');
            return;
        }
        
        global $wpdb;
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        // Get current notes
        $current_notes = $wpdb->get_var($wpdb->prepare(
            "SELECT notes FROM {$leads_table} WHERE id = %d",
            $lead_id
        ));
        
        // Append new note with timestamp
        $timestamp = current_time('mysql');
        $user = wp_get_current_user();
        $new_note_entry = "\n[{$timestamp}] {$user->display_name}: {$note}";
        $updated_notes = $current_notes . $new_note_entry;
        
        $result = $wpdb->update(
            $leads_table,
            [
                'notes' => $updated_notes,
                'last_contact' => $timestamp
            ],
            ['id' => $lead_id],
            ['%s', '%s'],
            ['%d']
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to add note');
            return;
        }
        
        wp_send_json_success([
            'message' => 'Note added successfully',
            'note_entry' => $new_note_entry
        ]);
    }
}

/**
 * Handle create new lead
 */
if (!function_exists('handle_create_lead')) {
    add_action('wp_ajax_create_lead', 'handle_create_lead');
    
    function handle_create_lead() {
        // Verify user permissions
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $message = sanitize_textarea_field($_POST['message']);
        $source = sanitize_text_field($_POST['source']) ?: 'manual';
        
        if (!$first_name || !$last_name || !$email) {
            wp_send_json_error('Required fields missing');
            return;
        }
        
        global $wpdb;
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        // Check if email already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$leads_table} WHERE email = %s",
            $email
        ));
        
        if ($existing) {
            wp_send_json_error('A lead with this email already exists');
            return;
        }
        
        $result = $wpdb->insert(
            $leads_table,
            [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
                'source' => $source,
                'status' => 'new',
                'created_date' => current_time('mysql'),
                'last_contact' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to create lead');
            return;
        }
        
        wp_send_json_success([
            'message' => 'Lead created successfully',
            'lead_id' => $wpdb->insert_id
        ]);
    }
}

/**
 * Handle assign lead to agent
 */
if (!function_exists('handle_assign_lead_to_agent')) {
    add_action('wp_ajax_assign_lead_to_agent', 'handle_assign_lead_to_agent');
    
    function handle_assign_lead_to_agent() {
        // Verify user permissions
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        $lead_id = intval($_POST['lead_id']);
        $agent_id = intval($_POST['agent_id']);
        
        if (!$lead_id || !$agent_id) {
            wp_send_json_error('Missing required parameters');
            return;
        }
        
        // Verify agent exists and has proper role
        $agent_user = get_user_by('id', $agent_id);
        if (!$agent_user || !in_array('agent', $agent_user->roles) && !in_array('administrator', $agent_user->roles)) {
            wp_send_json_error('Invalid agent selected');
            return;
        }
        
        global $wpdb;
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        $result = $wpdb->update(
            $leads_table,
            [
                'assigned_to' => $agent_id,
                'last_contact' => current_time('mysql')
            ],
            ['id' => $lead_id],
            ['%d', '%s'],
            ['%d']
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to assign lead');
            return;
        }
        
        wp_send_json_success([
            'message' => 'Lead assigned successfully',
            'agent_name' => $agent_user->display_name
        ]);
    }
}

/**
 * Handle delete lead
 */
if (!function_exists('handle_delete_lead')) {
    add_action('wp_ajax_delete_lead', 'handle_delete_lead');
    
    function handle_delete_lead() {
        // Verify user permissions
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        $lead_id = intval($_POST['lead_id']);
        
        if (!$lead_id) {
            wp_send_json_error('Invalid lead ID');
            return;
        }
        
        global $wpdb;
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        $result = $wpdb->delete(
            $leads_table,
            ['id' => $lead_id],
            ['%d']
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to delete lead');
            return;
        }
        
        wp_send_json_success([
            'message' => 'Lead deleted successfully'
        ]);
    }
}

/**
 * Handle lead form submissions from contact, buyer guide, and valuation forms
 * Integrates with existing lead service and table structure
 */
if (!function_exists('handle_lead_form_submission')) {
    add_action('wp_ajax_hph_submit_lead_form', 'handle_lead_form_submission');
    add_action('wp_ajax_nopriv_hph_submit_lead_form', 'handle_lead_form_submission');
    
    function handle_lead_form_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['hph_lead_nonce'], 'hph_lead_form_submission')) {
            wp_send_json_error([
                'message' => 'Security check failed'
            ]);
            return;
        }
        
        // Get form data
        $form_type = sanitize_text_field($_POST['form_type'] ?? 'contact');
        $lead_source = sanitize_text_field($_POST['lead_source'] ?? 'website_form');
        $page_url = sanitize_url($_POST['page_url'] ?? '');
        $page_title = sanitize_text_field($_POST['page_title'] ?? '');
        
        // Required fields based on form type
        $required_fields = ['first_name', 'last_name', 'email'];
        
        // Form-specific required fields
        switch ($form_type) {
            case 'buyer_guide':
                $required_fields[] = 'phone';
                $required_fields[] = 'timeline';
                break;
            case 'valuation':
                $required_fields[] = 'phone';
                $required_fields[] = 'address';
                $required_fields[] = 'city';
                $required_fields[] = 'bedrooms';
                $required_fields[] = 'bathrooms';
                $required_fields[] = 'property_type';
                break;
            case 'contact':
                $required_fields[] = 'message';
                $required_fields[] = 'interest';
                break;
        }
        
        // Validate required fields
        $missing_fields = [];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            wp_send_json_error([
                'message' => 'Please fill out all required fields: ' . implode(', ', $missing_fields)
            ]);
            return;
        }
        
        // Sanitize all form data
        $form_data = [];
        $allowed_fields = [
            'first_name', 'last_name', 'email', 'phone', 'message', 'interest',
            'timeline', 'address', 'city', 'state', 'zip', 'bedrooms', 'bathrooms',
            'square_feet', 'property_type'
        ];
        
        foreach ($allowed_fields as $field) {
            if (isset($_POST[$field])) {
                if ($field === 'message') {
                    $form_data[$field] = sanitize_textarea_field($_POST[$field]);
                } else {
                    $form_data[$field] = sanitize_text_field($_POST[$field]);
                }
            }
        }
        
        // Validate email format
        if (!is_email($form_data['email'])) {
            wp_send_json_error([
                'message' => 'Please enter a valid email address'
            ]);
            return;
        }
        
        global $wpdb;
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        // Check if email already exists
        $existing_lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$leads_table} WHERE email = %s",
            $form_data['email']
        ));
        
        $lead_id = null;
        
        if ($existing_lead) {
            // Update existing lead with new information
            $update_data = [
                'last_contact' => current_time('mysql'),
                'source' => $lead_source
            ];
            
            // Update non-empty fields
            $updateable_fields = ['phone', 'first_name', 'last_name'];
            foreach ($updateable_fields as $field) {
                if (!empty($form_data[$field]) && $form_data[$field] !== $existing_lead->$field) {
                    $update_data[$field] = $form_data[$field];
                }
            }
            
            $result = $wpdb->update(
                $leads_table,
                $update_data,
                ['id' => $existing_lead->id],
                array_fill(0, count($update_data), '%s'),
                ['%d']
            );
            
            $lead_id = $existing_lead->id;
            $is_new_lead = false;
        } else {
            // Create new lead
            $insert_data = [
                'first_name' => $form_data['first_name'],
                'last_name' => $form_data['last_name'],
                'email' => $form_data['email'],
                'phone' => $form_data['phone'] ?? '',
                'source' => $lead_source,
                'status' => 'new',
                'created_date' => current_time('mysql'),
                'last_contact' => current_time('mysql')
            ];
            
            $result = $wpdb->insert(
                $leads_table,
                $insert_data,
                ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
            );
            
            if ($result === false) {
                wp_send_json_error([
                    'message' => 'Failed to create lead record. Please try again.'
                ]);
                return;
            }
            
            $lead_id = $wpdb->insert_id;
            $is_new_lead = true;
        }
        
        // Store form-specific data as lead meta
        if ($lead_id) {
            // Common form data
            if (!empty($form_data['message'])) {
                update_post_meta($lead_id, 'lead_message', $form_data['message']);
            }
            if (!empty($form_data['interest'])) {
                update_post_meta($lead_id, 'lead_interest', $form_data['interest']);
            }
            if (!empty($page_url)) {
                update_post_meta($lead_id, 'lead_page_url', $page_url);
            }
            if (!empty($page_title)) {
                update_post_meta($lead_id, 'lead_page_title', $page_title);
            }
            
            // Form-specific data
            switch ($form_type) {
                case 'buyer_guide':
                    if (!empty($form_data['timeline'])) {
                        update_post_meta($lead_id, 'buyer_timeline', $form_data['timeline']);
                    }
                    break;
                    
                case 'valuation':
                    $property_data = [
                        'address' => $form_data['address'] ?? '',
                        'city' => $form_data['city'] ?? '',
                        'state' => $form_data['state'] ?? '',
                        'zip' => $form_data['zip'] ?? '',
                        'bedrooms' => $form_data['bedrooms'] ?? '',
                        'bathrooms' => $form_data['bathrooms'] ?? '',
                        'square_feet' => $form_data['square_feet'] ?? '',
                        'property_type' => $form_data['property_type'] ?? '',
                        'timeline' => $form_data['timeline'] ?? ''
                    ];
                    
                    update_post_meta($lead_id, 'valuation_property_data', $property_data);
                    break;
            }
        }
        
        // Send notification email to admin/agents (optional)
        $send_notifications = apply_filters('hph_send_lead_notifications', true, $form_type, $lead_id);
        
        if ($send_notifications) {
            $admin_email = get_option('admin_email');
            $site_name = get_option('blogname');
            
            $subject = sprintf('[%s] New %s Lead: %s %s', 
                $site_name, 
                ucfirst(str_replace('_', ' ', $form_type)),
                $form_data['first_name'], 
                $form_data['last_name']
            );
            
            $message = sprintf(
                "A new lead has been submitted through the %s form.\n\n" .
                "Name: %s %s\n" .
                "Email: %s\n" .
                "Phone: %s\n" .
                "Source: %s\n" .
                "Page: %s\n\n",
                ucfirst(str_replace('_', ' ', $form_type)),
                $form_data['first_name'],
                $form_data['last_name'],
                $form_data['email'],
                $form_data['phone'] ?? 'Not provided',
                $lead_source,
                $page_title ?: $page_url
            );
            
            if (!empty($form_data['message'])) {
                $message .= "Message:\n" . $form_data['message'] . "\n\n";
            }
            
            if ($form_type === 'valuation' && isset($property_data)) {
                $message .= "Property Information:\n";
                $message .= "Address: {$property_data['address']}, {$property_data['city']}, {$property_data['state']} {$property_data['zip']}\n";
                $message .= "Bedrooms: {$property_data['bedrooms']}\n";
                $message .= "Bathrooms: {$property_data['bathrooms']}\n";
                $message .= "Property Type: {$property_data['property_type']}\n";
                if ($property_data['square_feet']) {
                    $message .= "Square Feet: {$property_data['square_feet']}\n";
                }
                if ($property_data['timeline']) {
                    $message .= "Selling Timeline: {$property_data['timeline']}\n";
                }
            }
            
            $message .= "\n--\nThis lead was generated automatically from your website.";
            
            wp_mail($admin_email, $subject, $message);
        }
        
        // Success response based on form type
        $success_messages = [
            'contact' => 'Thank you for your message! We\'ll get back to you within 24 hours.',
            'buyer_guide' => 'Thank you! Your Home Buyer\'s Guide has been sent to your email.',
            'valuation' => 'Thank you! We\'ll prepare your property valuation report and contact you soon.'
        ];
        
        $response_data = [
            'message' => $success_messages[$form_type] ?? $success_messages['contact'],
            'lead_id' => $lead_id,
            'is_new_lead' => $is_new_lead ?? true,
            'form_type' => $form_type
        ];
        
        // Add form-specific response data
        if ($form_type === 'buyer_guide') {
            $response_data['download_url'] = '#'; // Add actual download URL if available
        }
        
        wp_send_json_success($response_data);
    }
}

/**
 * Get form modal HTML via AJAX
 * Handles dynamic loading of form modals
 */
if (!function_exists('handle_get_form_modal')) {
    add_action('wp_ajax_hph_get_form_modal', 'handle_get_form_modal');
    add_action('wp_ajax_nopriv_hph_get_form_modal', 'handle_get_form_modal');

    function handle_get_form_modal() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_ajax_nonce')) {
            wp_send_json_error(['message' => 'Security verification failed.']);
            return;
        }

        // Get parameters
        $form_template = sanitize_text_field($_POST['form_template'] ?? 'general-contact');
        $modal_title = sanitize_text_field($_POST['modal_title'] ?? 'Contact Us');
        $modal_subtitle = sanitize_text_field($_POST['modal_subtitle'] ?? '');
        $modal_size = sanitize_text_field($_POST['modal_size'] ?? 'lg');
        $close_on_success = filter_var($_POST['close_on_success'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $success_redirect = esc_url_raw($_POST['success_redirect'] ?? '');
        $form_args = json_decode(stripslashes($_POST['form_args'] ?? '{}'), true) ?: [];

        try {
            // Start output buffering
            ob_start();

            // Include the modal template
            get_template_part('template-parts/components/form-modal', null, [
                'modal_id' => 'hph-dynamic-form-modal',
                'form_template' => $form_template,
                'form_args' => $form_args,
                'modal_title' => $modal_title,
                'modal_subtitle' => $modal_subtitle,
                'modal_size' => $modal_size,
                'close_on_success' => $close_on_success,
                'success_redirect' => $success_redirect
            ]);

            // Get the output
            $html = ob_get_clean();

            wp_send_json_success([
                'html' => $html,
                'form_template' => $form_template,
                'modal_title' => $modal_title
            ]);

        } catch (Exception $e) {
            ob_end_clean(); // Clean up output buffer on error
            wp_send_json_error([
                'message' => 'Failed to load modal: ' . $e->getMessage()
            ]);
        }
    }
}

/**
 * Get form content HTML via AJAX
 * Handles dynamic loading of form content within existing modals
 */
if (!function_exists('handle_get_form_content')) {
    add_action('wp_ajax_hph_get_form_content', 'handle_get_form_content');
    add_action('wp_ajax_nopriv_hph_get_form_content', 'handle_get_form_content');

    function handle_get_form_content() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_ajax_nonce')) {
            wp_send_json_error(['message' => 'Security verification failed.']);
            return;
        }

        // Get parameters
        $form_template = sanitize_text_field($_POST['form_template'] ?? 'general-contact');
        $form_args = json_decode(stripslashes($_POST['form_args'] ?? '{}'), true) ?: [];

        // Add modal context to form args
        $form_args['modal_context'] = true;
        $form_args['variant'] = 'modern';
        $form_args['show_office_info'] = false;

        try {
            // Start output buffering
            ob_start();

            // Load the form template
            $form_template_path = "template-parts/forms/{$form_template}";
            if (locate_template($form_template_path . '.php')) {
                get_template_part($form_template_path, null, $form_args);
            } else {
                // Fallback to general contact form
                get_template_part('template-parts/forms/general-contact', null, $form_args);
            }

            // Get the output
            $html = ob_get_clean();

            wp_send_json_success([
                'html' => $html,
                'form_template' => $form_template
            ]);

        } catch (Exception $e) {
            ob_end_clean(); // Clean up output buffer on error
            wp_send_json_error([
                'message' => 'Failed to load form: ' . $e->getMessage()
            ]);
        }
    }
}
