<?php
/**
 * User Interactions Service
 *
 * Handles all user interaction tracking and management including:
 * - Favorite listings management
 * - View tracking and analytics
 * - User engagement metrics
 * - Nearby places functionality
 * - Lead form submissions and management
 *
 * @package HappyPlace\Services
 * @since 4.1.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * User Interactions Service Class
 *
 * Centralizes all user interaction functionality previously in theme AJAX handlers
 */
class UserInteractionsService extends Service {

    /**
     * Initialize the service
     */
    public function init(): void {
        $this->register_ajax_handlers();
    }

    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        // Favorites management
        add_action('wp_ajax_hph_toggle_listing_favorite', [$this, 'handle_toggle_favorite']);
        add_action('wp_ajax_nopriv_hph_toggle_listing_favorite', [$this, 'handle_guest_favorite_attempt']);

        // View tracking and statistics
        add_action('wp_ajax_hph_get_listing_stats', [$this, 'handle_get_listing_stats']);
        add_action('wp_ajax_nopriv_hph_get_listing_stats', [$this, 'handle_get_listing_stats']);
        add_action('wp_ajax_hph_track_listing_view', [$this, 'handle_track_listing_view']);
        add_action('wp_ajax_nopriv_hph_track_listing_view', [$this, 'handle_track_listing_view']);
        add_action('wp_ajax_hph_track_listing_view_enhanced', [$this, 'handle_enhanced_listing_view']);
        add_action('wp_ajax_nopriv_hph_track_listing_view_enhanced', [$this, 'handle_enhanced_listing_view']);

        // Maps and places
        add_action('wp_ajax_hph_get_nearby_places', [$this, 'handle_get_nearby_places']);
        add_action('wp_ajax_nopriv_hph_get_nearby_places', [$this, 'handle_get_nearby_places']);

        // User engagement
        add_action('wp_ajax_hph_track_engagement', [$this, 'handle_track_engagement']);
        add_action('wp_ajax_nopriv_hph_track_engagement', [$this, 'handle_track_engagement']);

        // Saved searches
        add_action('wp_ajax_hph_save_search', [$this, 'handle_save_search']);
        add_action('wp_ajax_nopriv_hph_save_search', [$this, 'handle_guest_save_search']);

        // Quick registration
        add_action('wp_ajax_nopriv_hph_quick_register', [$this, 'handle_quick_registration']);

        // Lead management
        add_action('wp_ajax_hph_get_lead_details', [$this, 'handle_get_lead_details']);
        add_action('wp_ajax_hph_update_lead_status', [$this, 'handle_update_lead_status']);
        add_action('wp_ajax_hph_add_lead_note', [$this, 'handle_add_lead_note']);
        add_action('wp_ajax_hph_create_lead', [$this, 'handle_create_lead']);
        add_action('wp_ajax_hph_assign_lead_to_agent', [$this, 'handle_assign_lead_to_agent']);
        add_action('wp_ajax_hph_delete_lead', [$this, 'handle_delete_lead']);

        // Form submissions
        add_action('wp_ajax_hph_submit_lead_form', [$this, 'handle_lead_form_submission']);
        add_action('wp_ajax_nopriv_hph_submit_lead_form', [$this, 'handle_lead_form_submission']);
        add_action('wp_ajax_hph_get_form_modal', [$this, 'handle_get_form_modal']);
        add_action('wp_ajax_nopriv_hph_get_form_modal', [$this, 'handle_get_form_modal']);
        add_action('wp_ajax_hph_get_form_content', [$this, 'handle_get_form_content']);
        add_action('wp_ajax_nopriv_hph_get_form_content', [$this, 'handle_get_form_content']);
    }

    /**
     * Handle toggle listing favorite
     */
    public function handle_toggle_favorite(): void {
        try {
            // Check if user is logged in
            if (!is_user_logged_in()) {
                wp_send_json_error('You must be logged in to save favorites');
                return;
            }

            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_nonce')) {
                wp_send_json_error('Security check failed');
                return;
            }

            $listing_id = intval($_POST['listing_id'] ?? 0);
            if (!$listing_id) {
                wp_send_json_error('Invalid listing ID');
                return;
            }

            // Validate listing
            $listing = get_post($listing_id);
            if (!$listing || $listing->post_type !== 'listing') {
                wp_send_json_error('Invalid listing');
                return;
            }

            $user_id = get_current_user_id();
            $result = $this->toggle_favorite($user_id, $listing_id);

            if ($result['success']) {
                wp_send_json_success([
                    'is_favorite' => $result['is_favorited'],
                    'action' => $result['action'],
                    'count' => $result['count']
                ]);
            } else {
                wp_send_json_error($result['message']);
            }

        } catch (Exception $e) {
            error_log('Toggle favorite error: ' . $e->getMessage());
            wp_send_json_error('Failed to update favorite status', 500);
        }
    }

    /**
     * Toggle favorite status for a user and listing
     */
    public function toggle_favorite(int $user_id, int $listing_id): array {
        // Get current favorites
        $favorites = get_user_meta($user_id, 'favorite_listings', true);
        if (!is_array($favorites)) {
            $favorites = [];
        }

        // Toggle favorite status
        $is_favorite = in_array($listing_id, $favorites);

        if ($is_favorite) {
            // Remove from favorites
            $favorites = array_diff($favorites, [$listing_id]);
            $action = 'removed';
        } else {
            // Add to favorites
            $favorites[] = $listing_id;
            $action = 'added';
        }

        // Update user meta
        $updated = update_user_meta($user_id, 'favorite_listings', $favorites);

        if ($updated !== false) {
            // Update listing favorite count
            $favorite_count = get_post_meta($listing_id, 'favorite_count', true) ?: 0;
            $new_count = $is_favorite ? max(0, $favorite_count - 1) : $favorite_count + 1;
            update_post_meta($listing_id, 'favorite_count', $new_count);

            // Log the action
            do_action('hph_listing_favorite_toggled', $listing_id, $user_id, $action);

            return [
                'success' => true,
                'is_favorited' => !$is_favorite,
                'action' => $action,
                'count' => count($favorites)
            ];
        }

        return [
            'success' => false,
            'message' => 'Error updating favorites'
        ];
    }

    /**
     * Handle guest favorite attempt
     */
    public function handle_guest_favorite_attempt(): void {
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

    /**
     * Handle get listing statistics
     */
    public function handle_get_listing_stats(): void {
        try {
            $listing_id = intval($_POST['listing_id'] ?? 0);

            if (!$listing_id) {
                wp_send_json_error('Invalid listing ID');
                return;
            }

            $listing = get_post($listing_id);
            if (!$listing || $listing->post_type !== 'listing') {
                wp_send_json_error('Invalid listing');
                return;
            }

            $stats = $this->get_listing_statistics($listing_id);
            wp_send_json_success($stats);

        } catch (Exception $e) {
            error_log('Get listing stats error: ' . $e->getMessage());
            wp_send_json_error('Failed to get listing statistics', 500);
        }
    }

    /**
     * Get comprehensive listing statistics
     */
    public function get_listing_statistics(int $listing_id): array {
        $listing = get_post($listing_id);

        return [
            'views_total' => $this->get_listing_views($listing_id),
            'views_week' => $this->get_listing_views($listing_id, 'week'),
            'views_month' => $this->get_listing_views($listing_id, 'month'),
            'favorite_count' => get_post_meta($listing_id, 'favorite_count', true) ?: 0,
            'inquiry_count' => get_post_meta($listing_id, 'inquiry_count', true) ?: 0,
            'is_featured' => $this->is_listing_featured($listing_id),
            'status' => $this->get_listing_status($listing_id),
            'days_active' => floor((time() - strtotime($listing->post_date)) / (60 * 60 * 24)),
        ];
    }

    /**
     * Handle listing view tracking
     */
    public function handle_track_listing_view(): void {
        try {
            $listing_id = intval($_POST['listing_id'] ?? 0);
            if (!$listing_id) {
                wp_send_json_error(['message' => 'Invalid listing ID']);
                return;
            }

            $user_id = get_current_user_id();
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

            // Prevent duplicate views from same user/IP within 30 minutes
            $cache_key = 'hph_view_' . $listing_id . '_' . ($user_id ?: md5($ip_address));
            if (get_transient($cache_key)) {
                wp_send_json_success(['message' => 'View already tracked']);
                return;
            }

            $this->track_listing_view($listing_id, $user_id, $ip_address);

            // Set cache to prevent duplicate tracking
            set_transient($cache_key, true, 30 * MINUTE_IN_SECONDS);

            wp_send_json_success(['message' => 'View tracked']);

        } catch (Exception $e) {
            error_log('Track listing view error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Failed to track view'], 500);
        }
    }

    /**
     * Track listing view with basic analytics
     */
    public function track_listing_view(int $listing_id, int $user_id = 0, string $ip_address = ''): bool {
        // Update simple view count
        $view_count = get_post_meta($listing_id, 'view_count', true) ?: 0;
        update_post_meta($listing_id, 'view_count', $view_count + 1);

        // Update daily view count
        $today = date('Y-m-d');
        $daily_views_key = "daily_views_{$today}";
        $daily_views = get_post_meta($listing_id, $daily_views_key, true) ?: 0;
        update_post_meta($listing_id, $daily_views_key, $daily_views + 1);

        // Store view record for logged-in users
        if ($user_id) {
            $recent_views = get_user_meta($user_id, 'recent_listing_views', true);
            if (!is_array($recent_views)) {
                $recent_views = [];
            }

            // Add this view and keep only last 50
            $recent_views = array_filter($recent_views, function($view) use ($listing_id) {
                return $view['listing_id'] !== $listing_id;
            });

            $recent_views[] = [
                'listing_id' => $listing_id,
                'viewed_at' => current_time('mysql'),
                'ip_address' => $ip_address
            ];

            $recent_views = array_slice($recent_views, -50);
            update_user_meta($user_id, 'recent_listing_views', $recent_views);
        }

        return true;
    }

    /**
     * Handle enhanced listing view tracking
     */
    public function handle_enhanced_listing_view(): void {
        try {
            $listing_id = intval($_POST['listing_id'] ?? 0);
            $view_time = intval($_POST['view_time'] ?? 0);
            $scroll_depth = intval($_POST['scroll_depth'] ?? 0);
            $user_id = get_current_user_id();

            if (!$listing_id) {
                wp_send_json_error(['message' => 'Invalid listing ID']);
                return;
            }

            // Track basic view
            $this->track_listing_view($listing_id, $user_id);

            // Track enhanced engagement if user is logged in
            if ($user_id) {
                $this->track_user_engagement($user_id, 'listing_viewed', $listing_id, 'listing', [
                    'view_time_seconds' => $view_time,
                    'scroll_depth' => $scroll_depth,
                    'source' => sanitize_text_field($_POST['source'] ?? 'direct')
                ]);
            }

            wp_send_json_success([
                'message' => 'View tracked',
                'engagement_tracked' => $user_id ? true : false
            ]);

        } catch (Exception $e) {
            error_log('Enhanced listing view error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Failed to track enhanced view'], 500);
        }
    }

    /**
     * Handle nearby places requests
     */
    public function handle_get_nearby_places(): void {
        try {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_map_nonce')) {
                wp_send_json_error(['message' => 'Security check failed']);
                return;
            }

            $lat = floatval($_POST['lat'] ?? 0);
            $lng = floatval($_POST['lng'] ?? 0);
            $category = sanitize_text_field($_POST['category'] ?? '');
            $radius = intval($_POST['radius'] ?? 2000);

            if (!$lat || !$lng) {
                wp_send_json_error(['message' => 'Invalid coordinates']);
                return;
            }

            $places = $this->get_nearby_places($lat, $lng, $category, $radius);
            wp_send_json_success($places);

        } catch (Exception $e) {
            error_log('Get nearby places error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Failed to get nearby places'], 500);
        }
    }

    /**
     * Get nearby places using Google Places API or fallback
     */
    public function get_nearby_places(float $lat, float $lng, string $category, int $radius = 2000): array {
        // Category to Google Places type mapping
        $category_types = [
            'schools' => ['school', 'university'],
            'shopping' => ['shopping_mall', 'store', 'supermarket'],
            'restaurants' => ['restaurant', 'meal_takeaway', 'food'],
            'healthcare' => ['hospital', 'doctor', 'pharmacy'],
            'parks' => ['park', 'amusement_park', 'gym'],
            'transit' => ['transit_station', 'bus_station', 'subway_station'],
            'entertainment' => ['movie_theater', 'night_club', 'bowling_alley'],
            'services' => ['bank', 'gas_station', 'car_repair']
        ];

        $place_types = $category_types[$category] ?? ['establishment'];

        // Cache the results for 1 hour
        $cache_key = 'hph_nearby_' . md5($lat . $lng . $category . $radius);
        $cached_results = get_transient($cache_key);

        if ($cached_results !== false) {
            return $cached_results;
        }

        // If Google Places API integration is available, use it
        if (function_exists('hpt_get_nearby_places')) {
            $places = hpt_get_nearby_places($lat, $lng, $place_types, $radius);
        } else {
            // Return mock data for development
            $places = [
                'results' => [],
                'status' => 'MOCK_DATA',
                'message' => 'Google Places API not configured'
            ];
        }

        // Cache the results
        set_transient($cache_key, $places, HOUR_IN_SECONDS);

        return $places;
    }

    /**
     * Handle user engagement tracking
     */
    public function handle_track_engagement(): void {
        try {
            $user_id = get_current_user_id();
            if (!$user_id) {
                wp_send_json_error(['message' => 'User not logged in']);
                return;
            }

            $event_type = sanitize_text_field($_POST['event_type'] ?? '');
            $object_id = intval($_POST['object_id'] ?? 0);
            $object_type = sanitize_text_field($_POST['object_type'] ?? '');
            $metadata = $_POST['metadata'] ?? [];

            if (!$event_type) {
                wp_send_json_error(['message' => 'Event type required']);
                return;
            }

            $this->track_user_engagement($user_id, $event_type, $object_id, $object_type, $metadata);
            wp_send_json_success(['message' => 'Engagement tracked']);

        } catch (Exception $e) {
            error_log('Track engagement error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Failed to track engagement'], 500);
        }
    }

    /**
     * Track user engagement activity
     */
    public function track_user_engagement(int $user_id, string $event_type, int $object_id = 0, string $object_type = '', array $metadata = []): bool {
        $engagement_data = get_user_meta($user_id, 'user_engagement_log', true);
        if (!is_array($engagement_data)) {
            $engagement_data = [];
        }

        $engagement_entry = [
            'event_type' => $event_type,
            'object_id' => $object_id,
            'object_type' => $object_type,
            'timestamp' => current_time('mysql'),
            'metadata' => $metadata
        ];

        $engagement_data[] = $engagement_entry;

        // Keep only last 200 entries
        if (count($engagement_data) > 200) {
            $engagement_data = array_slice($engagement_data, -200);
        }

        return update_user_meta($user_id, 'user_engagement_log', $engagement_data);
    }

    /**
     * Handle save search request
     */
    public function handle_save_search(): void {
        try {
            if (!is_user_logged_in()) {
                wp_send_json_error(['message' => 'Please login to save searches']);
                return;
            }

            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_nonce')) {
                wp_send_json_error(['message' => 'Security check failed']);
                return;
            }

            $user_id = get_current_user_id();
            $search_params = $_POST['search_params'] ?? [];
            $search_name = sanitize_text_field($_POST['search_name'] ?? '');

            if (empty($search_params)) {
                wp_send_json_error(['message' => 'Search parameters required']);
                return;
            }

            $result = $this->save_user_search($user_id, $search_params, $search_name);

            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }

        } catch (Exception $e) {
            error_log('Save search error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Failed to save search'], 500);
        }
    }

    /**
     * Save user search
     */
    public function save_user_search(int $user_id, array $search_params, string $search_name = ''): array {
        $saved_searches = get_user_meta($user_id, 'saved_searches', true);
        if (!is_array($saved_searches)) {
            $saved_searches = [];
        }

        $search_id = uniqid();
        $search_entry = [
            'id' => $search_id,
            'name' => $search_name ?: 'Search ' . (count($saved_searches) + 1),
            'params' => $search_params,
            'created_at' => current_time('mysql'),
            'last_run' => current_time('mysql')
        ];

        $saved_searches[] = $search_entry;

        // Keep only last 20 searches
        if (count($saved_searches) > 20) {
            $saved_searches = array_slice($saved_searches, -20);
        }

        $updated = update_user_meta($user_id, 'saved_searches', $saved_searches);

        if ($updated) {
            return [
                'success' => true,
                'message' => 'Search saved successfully',
                'search_id' => $search_id
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to save search'
        ];
    }

    /**
     * Handle guest save search attempt
     */
    public function handle_guest_save_search(): void {
        wp_send_json_error([
            'message' => 'Create a free account to save your search and get email alerts',
            'requires_registration' => true,
            'conversion_context' => 'save_search'
        ]);
    }

    /**
     * Handle quick registration
     */
    public function handle_quick_registration(): void {
        try {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_nonce')) {
                wp_send_json_error(['message' => 'Security check failed']);
                return;
            }

            $email = sanitize_email($_POST['email'] ?? '');
            $first_name = sanitize_text_field($_POST['first_name'] ?? '');
            $last_name = sanitize_text_field($_POST['last_name'] ?? '');
            $context = sanitize_text_field($_POST['context'] ?? '');

            if (!$email || !is_email($email)) {
                wp_send_json_error(['message' => 'Valid email address required']);
                return;
            }

            if (!$first_name || !$last_name) {
                wp_send_json_error(['message' => 'First and last name required']);
                return;
            }

            // Check if user already exists
            if (email_exists($email)) {
                wp_send_json_error(['message' => 'An account with this email already exists']);
                return;
            }

            // Create user
            $username = sanitize_user($email);
            $password = wp_generate_password(12, false);

            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                wp_send_json_error(['message' => $user_id->get_error_message()]);
                return;
            }

            // Update user meta
            wp_update_user([
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $first_name . ' ' . $last_name
            ]);

            // Add context-specific metadata
            update_user_meta($user_id, 'registration_context', $context);
            update_user_meta($user_id, 'registration_source', 'quick_register');

            // Send welcome email
            wp_new_user_notification($user_id, null, 'both');

            // Log them in
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);

            wp_send_json_success([
                'message' => 'Account created successfully! You are now logged in.',
                'user_id' => $user_id
            ]);

        } catch (Exception $e) {
            error_log('Quick registration error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Registration failed'], 500);
        }
    }

    /**
     * Helper methods for legacy function compatibility
     */
    private function get_listing_views(int $listing_id, string $period = 'all'): int {
        if ($period === 'all') {
            return get_post_meta($listing_id, 'view_count', true) ?: 0;
        }

        // For week/month, use daily view counts
        $days = $period === 'week' ? 7 : 30;
        $total_views = 0;

        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $daily_views = get_post_meta($listing_id, "daily_views_{$date}", true) ?: 0;
            $total_views += $daily_views;
        }

        return $total_views;
    }

    private function is_listing_featured(int $listing_id): bool {
        return (bool) get_post_meta($listing_id, '_listing_featured', true);
    }

    private function get_listing_status(int $listing_id): string {
        return get_post_meta($listing_id, '_listing_status', true) ?: 'active';
    }

    /**
     * Placeholder methods for lead management and form handling
     * These will delegate to existing lead service or implement basic functionality
     */
    public function handle_get_lead_details(): void {
        wp_send_json_error('Lead details not implemented yet');
    }

    public function handle_update_lead_status(): void {
        wp_send_json_error('Update lead status not implemented yet');
    }

    public function handle_add_lead_note(): void {
        wp_send_json_error('Add lead note not implemented yet');
    }

    public function handle_create_lead(): void {
        wp_send_json_error('Create lead not implemented yet');
    }

    public function handle_assign_lead_to_agent(): void {
        wp_send_json_error('Assign lead to agent not implemented yet');
    }

    public function handle_delete_lead(): void {
        wp_send_json_error('Delete lead not implemented yet');
    }

    public function handle_lead_form_submission(): void {
        wp_send_json_error('Lead form submission not implemented yet');
    }

    public function handle_get_form_modal(): void {
        wp_send_json_error('Get form modal not implemented yet');
    }

    public function handle_get_form_content(): void {
        wp_send_json_error('Get form content not implemented yet');
    }
}