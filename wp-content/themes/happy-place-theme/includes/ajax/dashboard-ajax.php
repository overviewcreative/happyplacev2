<?php
/**
 * Dashboard AJAX Handlers
 * 
 * @package HappyPlaceTheme
 * @subpackage Dashboard
 * 
 * File Location: /wp-content/themes/happy-place/includes/ajax/dashboard-ajax.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard AJAX Handler Class
 */
class HPH_Dashboard_Ajax {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->register_ajax_handlers();
        $this->register_rest_endpoints();
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers() {
        // Dashboard stats
        add_action('wp_ajax_hph_get_dashboard_stats', array($this, 'get_dashboard_stats'));
        
        // Recent activity
        add_action('wp_ajax_hph_get_recent_activity', array($this, 'get_recent_activity'));
        
        // Upcoming events
        add_action('wp_ajax_hph_get_upcoming_events', array($this, 'get_upcoming_events'));
        
        // Hot leads
        add_action('wp_ajax_hph_get_hot_leads', array($this, 'get_hot_leads'));
        
        // Listings CRUD
        add_action('wp_ajax_hph_create_listing', array($this, 'create_listing'));
        add_action('wp_ajax_hph_update_listing', array($this, 'update_listing'));
        add_action('wp_ajax_hph_delete_listing', array($this, 'delete_listing'));
        add_action('wp_ajax_hph_save_listing', array($this, 'save_listing'));
        add_action('wp_ajax_hph_get_listings', array($this, 'get_listings'));
        add_action('wp_ajax_hph_get_listing_stats', array($this, 'get_listing_stats'));
        
        // Listings filtering and sorting
        add_action('wp_ajax_hph_filter_listings', array($this, 'filter_listings'));
        add_action('wp_ajax_hph_sort_listings', array($this, 'sort_listings'));
        
        // Open Houses CRUD
        add_action('wp_ajax_hph_create_open_house', array($this, 'create_open_house'));
        add_action('wp_ajax_hph_update_open_house', array($this, 'update_open_house'));
        add_action('wp_ajax_hph_delete_open_house', array($this, 'delete_open_house'));
        add_action('wp_ajax_hph_get_open_houses', array($this, 'get_open_houses'));
        add_action('wp_ajax_hph_duplicate_open_house', array($this, 'duplicate_open_house'));
        add_action('wp_ajax_hph_get_rsvp_list', array($this, 'get_rsvp_list'));
        add_action('wp_ajax_hph_export_rsvp_list', array($this, 'export_rsvp_list'));
        add_action('wp_ajax_hph_bulk_open_house_actions', array($this, 'bulk_open_house_actions'));
        
        // Enhanced Phase 1 handlers
        add_action('wp_ajax_hph_bulk_listing_actions', array($this, 'bulk_listing_actions'));
        add_action('wp_ajax_hph_filter_listings', array($this, 'filter_listings'));
        add_action('wp_ajax_hph_duplicate_listing', array($this, 'duplicate_listing'));
        add_action('wp_ajax_hph_update_listing_status', array($this, 'update_listing_status'));
        add_action('wp_ajax_hph_update_listing_price', array($this, 'update_listing_price'));
        add_action('wp_ajax_hph_get_listing_details', array($this, 'get_listing_details'));
        add_action('wp_ajax_hph_get_listing', array($this, 'get_single_listing'));
        
        // Open Houses
        add_action('wp_ajax_hph_schedule_open_house', array($this, 'schedule_open_house'));
        add_action('wp_ajax_hph_get_open_houses', array($this, 'get_open_houses'));
        add_action('wp_ajax_hph_cancel_open_house', array($this, 'cancel_open_house'));
        
        // Leads Management
        add_action('wp_ajax_hph_create_lead', array($this, 'create_lead'));
        add_action('wp_ajax_hph_update_lead', array($this, 'update_lead'));
        add_action('wp_ajax_hph_get_leads', array($this, 'get_leads'));
        add_action('wp_ajax_hph_import_leads', array($this, 'import_leads'));
        
        // Transactions
        add_action('wp_ajax_hph_create_transaction', array($this, 'create_transaction'));
        add_action('wp_ajax_hph_update_transaction', array($this, 'update_transaction'));
        add_action('wp_ajax_hph_get_transactions', array($this, 'get_transactions'));
        
        // Profile
        add_action('wp_ajax_hph_update_profile', array($this, 'update_profile'));
        add_action('wp_ajax_hph_upload_profile_image', array($this, 'upload_profile_image'));
        
        // Marketing Materials - Phase 3
        add_action('wp_ajax_hph_generate_pdf_flyer', array($this, 'generate_pdf_flyer'));
        add_action('wp_ajax_hph_generate_social_template', array($this, 'generate_social_template'));
        add_action('wp_ajax_hph_send_marketing_email', array($this, 'send_marketing_email'));
        add_action('wp_ajax_hph_get_marketing_templates', array($this, 'get_marketing_templates'));
        add_action('wp_ajax_hph_get_agent_listings', array($this, 'get_agent_listings'));
        add_action('wp_ajax_hph_get_marketing_activity', array($this, 'get_marketing_activity'));
        
        // Legacy marketing (keep for backwards compatibility)
        add_action('wp_ajax_hph_generate_marketing', array($this, 'generate_marketing'));
        add_action('wp_ajax_hph_schedule_social_post', array($this, 'schedule_social_post'));
        
        // CMA Generation
        add_action('wp_ajax_hph_generate_cma', array($this, 'generate_cma'));
        add_action('wp_ajax_hph_get_comparables', array($this, 'get_comparables'));
        
        // Data Export/Import
        add_action('wp_ajax_hph_export_data', array($this, 'export_data'));
        add_action('wp_ajax_hph_import_csv', array($this, 'import_csv'));
        
        // Search
        add_action('wp_ajax_hph_dashboard_search', array($this, 'dashboard_search'));
        
        // Integrations
        add_action('wp_ajax_hph_sync_followupboss', array($this, 'sync_followupboss'));
        add_action('wp_ajax_hph_sync_airtable', array($this, 'sync_airtable'));
        add_action('wp_ajax_hph_get_analytics', array($this, 'get_analytics'));
    }
    
    /**
     * Register REST API endpoints
     */
    private function register_rest_endpoints() {
        add_action('rest_api_init', function() {
            // Dashboard pages
            register_rest_route('hph/v1', '/dashboard/(?P<page>[a-zA-Z0-9-]+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_dashboard_page'),
                'permission_callback' => array($this, 'check_permissions')
            ));
            
            // Stats endpoint
            register_rest_route('hph/v1', '/dashboard/stats', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_dashboard_stats_rest'),
                'permission_callback' => array($this, 'check_permissions')
            ));
            
            // Activity endpoint
            register_rest_route('hph/v1', '/dashboard/activity', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_recent_activity_rest'),
                'permission_callback' => array($this, 'check_permissions')
            ));
            
            // Events endpoint
            register_rest_route('hph/v1', '/dashboard/events', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_upcoming_events_rest'),
                'permission_callback' => array($this, 'check_permissions')
            ));
            
            // Leads endpoint
            register_rest_route('hph/v1', '/dashboard/leads/hot', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_hot_leads_rest'),
                'permission_callback' => array($this, 'check_permissions')
            ));
            
            // Search endpoint
            register_rest_route('hph/v1', '/search', array(
                'methods' => 'GET',
                'callback' => array($this, 'search_rest'),
                'permission_callback' => array($this, 'check_permissions')
            ));
        });
    }
    
    /**
     * Check permissions for REST API
     */
    public function check_permissions() {
        // Temporarily allow all requests for debugging
        return true;
        // return is_user_logged_in() && current_user_can('edit_posts');
    }
    
    /**
     * Verify AJAX nonce
     */
    private function verify_nonce() {
        // Check for dashboard nonce first (for dashboard AJAX calls)
        // Check both POST and GET requests
        if ((isset($_POST['nonce']) && check_ajax_referer('hph_dashboard_nonce', 'nonce', false)) ||
            (isset($_GET['nonce']) && check_ajax_referer('hph_dashboard_nonce', 'nonce', false))) {
            return;
        }
        
        // Check for listing form nonce (for listing form submissions)
        if (isset($_POST['listing_nonce']) && check_ajax_referer('hph_save_listing', 'listing_nonce', false)) {
            return;
        }
        
        wp_send_json_error('Security check failed');
    }
    
    /**
     * Get Dashboard Stats
     */
    public function get_dashboard_stats() {
        $this->verify_nonce();
        
        $user_id = get_current_user_id();
        
        // Get stats using bridge functions
        $stats = array(
            'active_listings' => $this->count_user_listings($user_id, 'active'),
            'closed_this_month' => $this->count_closed_transactions($user_id),
            'new_leads' => $this->count_new_leads($user_id),
            'open_houses' => $this->count_upcoming_open_houses($user_id),
            'total_leads' => $this->count_total_leads($user_id),
            'pending_transactions' => $this->count_pending_transactions($user_id),
            'monthly_revenue' => $this->calculate_monthly_revenue($user_id),
            'conversion_rate' => $this->calculate_conversion_rate($user_id)
        );
        
        wp_send_json_success($stats);
    }
    
    /**
     * Get Recent Activity
     */
    public function get_recent_activity() {
        $this->verify_nonce();
        
        $user_id = get_current_user_id();
        $activities = array();
        
        // Get recent listings
        $recent_listings = get_posts(array(
            'post_type' => 'listing',
            'author' => $user_id,
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        foreach ($recent_listings as $listing) {
            $activities[] = array(
                'type' => 'listing',
                'icon' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1l2 2v3h3l2 2v7H1V8l2-2h3V3l2-2z"/></svg>',
                'text' => 'New listing added: ' . get_the_title($listing),
                'time' => human_time_diff(get_post_time('U', true, $listing), current_time('timestamp')) . ' ago',
                'link' => get_permalink($listing),
                'timestamp' => get_post_time('U', true, $listing)
            );
        }
        
        // Get recent leads (if using custom post type or meta)
        $recent_leads = $this->get_recent_leads_activity($user_id);
        $activities = array_merge($activities, $recent_leads);
        
        // Sort by timestamp
        usort($activities, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        // Limit to 10 most recent
        $activities = array_slice($activities, 0, 10);
        
        wp_send_json_success(array('activities' => $activities));
    }
    
    /**
     * Get Upcoming Events
     */
    public function get_upcoming_events() {
        $this->verify_nonce();
        
        $user_id = get_current_user_id();
        $events = array();
        
        // Get upcoming open houses
        $open_houses = get_posts(array(
            'post_type' => 'open_house',
            'author' => $user_id,
            'meta_key' => 'event_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'event_date',
                    'value' => current_time('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            ),
            'posts_per_page' => 10
        ));
        
        foreach ($open_houses as $event) {
            $event_date = get_field('event_date', $event->ID);
            $event_time = get_field('event_time', $event->ID);
            $listing_id = get_field('listing_id', $event->ID);
            
            $events[] = array(
                'id' => $event->ID,
                'type' => 'open_house',
                'title' => 'Open House - ' . get_the_title($listing_id),
                'date' => $event_date,
                'day' => date('j', strtotime($event_date)),
                'month' => date('M', strtotime($event_date)),
                'time' => $event_time,
                'location' => get_field('address', $listing_id),
                'link' => get_permalink($listing_id)
            );
        }
        
        wp_send_json_success(array('events' => $events));
    }
    
    /**
     * Get Hot Leads
     */
    public function get_hot_leads() {
        $this->verify_nonce();
        
        $user_id = get_current_user_id();
        $leads = array();
        
        // Check if FollowUpBoss integration is enabled
        $fub_api_key = get_option('hph_followupboss_api_key');
        
        if ($fub_api_key) {
            // Get leads from FollowUpBoss
            $leads = $this->get_followupboss_leads($user_id);
        } else {
            // Get leads from local database
            $lead_posts = get_posts(array(
                'post_type' => 'lead',
                'author' => $user_id,
                'meta_key' => 'lead_score',
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
                'posts_per_page' => 5
            ));
            
            foreach ($lead_posts as $lead) {
                $score = get_field('lead_score', $lead->ID);
                $status = $score > 80 ? 'hot' : ($score > 50 ? 'warm' : 'cold');
                
                $leads[] = array(
                    'id' => $lead->ID,
                    'name' => get_the_title($lead),
                    'email' => get_field('email', $lead->ID),
                    'phone' => get_field('phone', $lead->ID),
                    'details' => get_field('requirements', $lead->ID),
                    'score' => $score,
                    'status' => $status,
                    'status_label' => ucfirst($status) . ' Lead',
                    'last_contact' => get_field('last_contact', $lead->ID),
                    'source' => get_field('lead_source', $lead->ID)
                );
            }
        }
        
        wp_send_json_success(array('leads' => $leads));
    }
    
    /**
     * Create Listing
     */
    public function create_listing() {
        $this->verify_nonce();
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Check if plugin services are available and active
        if (function_exists('hpt_services_available') && hpt_services_available()) {
            // Route through plugin DashboardBridge (priority 5 should have already handled this)
            // If we reach here, it means the service routing failed, so continue with fallback
            if (function_exists('hpt_create_listing_via_service')) {
                try {
                    $service_data = array(
                        'title' => sanitize_text_field($_POST['title'] ?? ''),
                        'description' => wp_kses_post($_POST['description'] ?? ''),
                        'status' => 'publish',
                        'price' => floatval($_POST['price'] ?? 0),
                        'bedrooms' => intval($_POST['bedrooms'] ?? 0),
                        'bathrooms' => floatval($_POST['bathrooms'] ?? 0),
                        'square_feet' => intval($_POST['square_feet'] ?? 0),
                        'address' => array(
                            'street_address' => sanitize_text_field($_POST['address'] ?? ''),
                            'city' => sanitize_text_field($_POST['city'] ?? ''),
                            'state' => sanitize_text_field($_POST['state'] ?? ''),
                            'zip_code' => sanitize_text_field($_POST['zip_code'] ?? '')
                        )
                    );
                    
                    $result = hpt_create_listing_via_service($service_data);
                    
                    if (!is_wp_error($result)) {
                        wp_send_json_success(array(
                            'listing_id' => $result,
                            'message' => 'Listing created successfully via service layer',
                            'redirect' => get_permalink($result)
                        ));
                        return;
                    } else {
                        // Log service error but continue with fallback
                        if (function_exists('hp_log')) {
                            hp_log('Service create_listing failed: ' . $result->get_error_message(), 'error', 'dashboard');
                        }
                    }
                } catch (Exception $e) {
                    if (function_exists('hp_log')) {
                        hp_log('Service create_listing exception: ' . $e->getMessage(), 'error', 'dashboard');
                    }
                }
            }
        }
        
        // Fallback to traditional method
        $listing_data = array(
            'post_title' => sanitize_text_field($_POST['title']),
            'post_content' => wp_kses_post($_POST['description']),
            'post_type' => 'listing',
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        );
        
        $listing_id = wp_insert_post($listing_data);
        
        if (is_wp_error($listing_id)) {
            wp_send_json_error($listing_id->get_error_message());
        }
        
        // Update ACF fields
        $fields = [
            'price' => floatval($_POST['price'] ?? 0),
            'listing_status' => sanitize_text_field($_POST['status'] ?? 'active'),
            'bedrooms' => intval($_POST['bedrooms'] ?? 0),
            'full_bathrooms' => floatval($_POST['bathrooms'] ?? 0),
            'square_feet' => intval($_POST['square_feet'] ?? 0),
            'street_address' => sanitize_text_field($_POST['address'] ?? ''),
            'city' => sanitize_text_field($_POST['city'] ?? ''),
            'state' => sanitize_text_field($_POST['state'] ?? ''),
            'zip_code' => sanitize_text_field($_POST['zip_code'] ?? ''),
            'property_type' => sanitize_text_field($_POST['property_type'] ?? ''),
            'year_built' => intval($_POST['year_built'] ?? 0),
            'mls_number' => sanitize_text_field($_POST['mls_number'] ?? ''),
            'featured_listing' => intval($_POST['featured'] ?? 0)
        ];
        
        foreach ($fields as $key => $value) {
            update_field($key, $value, $listing_id);
        }
        
        // Sync with Airtable if enabled
        if (get_option('hph_airtable_enabled')) {
            $this->sync_listing_to_airtable($listing_id);
        }
        
        // Get the created listing data
        $listing = hpt_get_listing($listing_id);
        
        wp_send_json_success(array(
            'message' => 'Listing created successfully',
            'listing' => $listing
        ));
    }
    
    /**
     * Update Listing
     */
    public function update_listing() {
        $this->verify_nonce();
        
        $listing_id = intval($_POST['listing_id']);
        
        if (!current_user_can('edit_post', $listing_id)) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Check if plugin services are available and active
        if (function_exists('hpt_services_available') && hpt_services_available()) {
            if (function_exists('hpt_update_listing_via_service')) {
                try {
                    $service_data = array(
                        'title' => sanitize_text_field($_POST['title'] ?? ''),
                        'description' => wp_kses_post($_POST['description'] ?? ''),
                        'price' => floatval($_POST['price'] ?? 0),
                        'status' => sanitize_text_field($_POST['status'] ?? 'active'),
                        'bedrooms' => intval($_POST['bedrooms'] ?? 0),
                        'bathrooms' => floatval($_POST['bathrooms'] ?? 0),
                        'square_feet' => intval($_POST['square_feet'] ?? 0),
                        'address' => array(
                            'street_address' => sanitize_text_field($_POST['address'] ?? ''),
                            'city' => sanitize_text_field($_POST['city'] ?? ''),
                            'state' => sanitize_text_field($_POST['state'] ?? ''),
                            'zip_code' => sanitize_text_field($_POST['zip_code'] ?? '')
                        ),
                        'property_type' => sanitize_text_field($_POST['property_type'] ?? ''),
                        'year_built' => intval($_POST['year_built'] ?? 0),
                        'mls_number' => sanitize_text_field($_POST['mls_number'] ?? ''),
                        'featured_listing' => intval($_POST['featured'] ?? 0)
                    );
                    
                    $result = hpt_update_listing_via_service($listing_id, $service_data);
                    
                    if (!is_wp_error($result)) {
                        wp_send_json_success(array(
                            'message' => 'Listing updated successfully via service layer',
                            'listing' => hpt_get_listing($listing_id)
                        ));
                        return;
                    } else {
                        // Log service error but continue with fallback
                        if (function_exists('hp_log')) {
                            hp_log('Service update_listing failed: ' . $result->get_error_message(), 'error', 'dashboard');
                        }
                    }
                } catch (Exception $e) {
                    if (function_exists('hp_log')) {
                        hp_log('Service update_listing exception: ' . $e->getMessage(), 'error', 'dashboard');
                    }
                }
            }
        }
        
        // Fallback to traditional method
        $listing_data = array(
            'ID' => $listing_id,
            'post_title' => sanitize_text_field($_POST['title']),
            'post_content' => wp_kses_post($_POST['description'])
        );
        
        $result = wp_update_post($listing_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        // Update ACF fields
        $fields = [
            'price' => floatval($_POST['price'] ?? 0),
            'listing_status' => sanitize_text_field($_POST['status'] ?? 'active'),
            'bedrooms' => intval($_POST['bedrooms'] ?? 0),
            'full_bathrooms' => floatval($_POST['bathrooms'] ?? 0),
            'square_feet' => intval($_POST['square_feet'] ?? 0),
            'street_address' => sanitize_text_field($_POST['address'] ?? ''),
            'city' => sanitize_text_field($_POST['city'] ?? ''),
            'state' => sanitize_text_field($_POST['state'] ?? ''),
            'zip_code' => sanitize_text_field($_POST['zip_code'] ?? ''),
            'property_type' => sanitize_text_field($_POST['property_type'] ?? ''),
            'year_built' => intval($_POST['year_built'] ?? 0),
            'mls_number' => sanitize_text_field($_POST['mls_number'] ?? ''),
            'featured_listing' => intval($_POST['featured'] ?? 0)
        ];
        
        foreach ($fields as $key => $value) {
            update_field($key, $value, $listing_id);
        }
        
        // Sync with Airtable
        if (get_option('hph_airtable_enabled')) {
            $this->sync_listing_to_airtable($listing_id);
        }
        
        wp_send_json_success(array(
            'message' => 'Listing updated successfully',
            'listing' => hpt_get_listing($listing_id)
        ));
    }
    
    /**
     * Delete Listing
     */
    public function delete_listing() {
        $this->verify_nonce();
        
        $listing_id = intval($_POST['listing_id']);
        
        if (!current_user_can('delete_post', $listing_id)) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Check specific listing permissions
        if (!$this->can_user_delete_listing($listing_id)) {
            wp_send_json_error('You do not have permission to delete this listing. Only administrators and the assigned listing agent can delete listings.');
        }
        
        // Check if plugin services are available and active
        if (function_exists('hpt_services_available') && hpt_services_available()) {
            if (function_exists('hpt_delete_listing_via_service')) {
                try {
                    $result = hpt_delete_listing_via_service($listing_id);
                    
                    if (!is_wp_error($result) && $result) {
                        wp_send_json_success(array(
                            'message' => 'Listing deleted successfully via service layer'
                        ));
                        return;
                    } else {
                        // Log service error but continue with fallback
                        if (function_exists('hp_log')) {
                            $error_msg = is_wp_error($result) ? $result->get_error_message() : 'Unknown error';
                            hp_log('Service delete_listing failed: ' . $error_msg, 'error', 'dashboard');
                        }
                    }
                } catch (Exception $e) {
                    if (function_exists('hp_log')) {
                        hp_log('Service delete_listing exception: ' . $e->getMessage(), 'error', 'dashboard');
                    }
                }
            }
        }
        
        // Fallback to traditional method
        $result = wp_trash_post($listing_id);
        
        if (!$result) {
            wp_send_json_error('Failed to delete listing');
        }
        
        wp_send_json_success(array(
            'message' => 'Listing deleted successfully'
        ));
    }
    
    /**
     * Save Listing (Create or Update)
     */
    public function save_listing() {
        // Add rate limiting to prevent repeated errors
        $user_id = get_current_user_id();
        $rate_limit_key = "hph_save_listing_rate_limit_{$user_id}";
        $rate_limit_count = get_transient($rate_limit_key);
        
        if ($rate_limit_count && $rate_limit_count >= 10) {
            wp_send_json_error('Too many requests. Please wait before trying again.');
        }
        
        $this->verify_nonce();
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        $is_update = $listing_id > 0;
        
        // Check specific listing permissions for updates
        if ($is_update && !$this->can_user_edit_listing($listing_id)) {
            wp_send_json_error('You do not have permission to edit this listing. Only administrators and the assigned listing agent can edit listings.');
        }
        
        // Validate required fields - property_title is optional, will be auto-generated if empty
        $required_fields = ['listing_price', 'city', 'state'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                // Increment rate limit counter for repeated missing field errors
                set_transient($rate_limit_key, ($rate_limit_count ?: 0) + 1, 60); // 1 minute
                wp_send_json_error("Required field missing: $field");
            }
        }

        // Generate property title if not provided
        $property_title = sanitize_text_field($_POST['property_title'] ?? '');
        if (empty($property_title)) {
            
            // Raw data check first
            $raw_street_name = $_POST['street_name'] ?? '';
            
            // Debug logging - let's see the raw POST data first
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("=== ADDRESS DEBUG START ===");
                error_log("Raw street_name value: '" . $raw_street_name . "' (length: " . strlen($raw_street_name) . ")");
                error_log("Hex dump: " . bin2hex($raw_street_name));
            }
            
            // Generate title from address components in proper order
            $street_number = sanitize_text_field($_POST['street_number'] ?? '');
            $street_dir_prefix = sanitize_text_field($_POST['street_dir_prefix'] ?? '');
            $street_name = sanitize_text_field($_POST['street_name'] ?? '');
            $street_type = sanitize_text_field($_POST['street_type'] ?? '');
            $street_dir_suffix = sanitize_text_field($_POST['street_dir_suffix'] ?? '');
            $city = sanitize_text_field($_POST['city'] ?? '');
            $state = sanitize_text_field($_POST['state'] ?? '');
            
            // Debug logging to see what we're receiving after sanitization
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("After sanitization:");
                error_log("Street Name: '$street_name' (length: " . strlen($street_name) . ")");
                error_log("Street Number: '$street_number'");
                error_log("Street Type: '$street_type'");
                error_log("City: '$city'");
                error_log("=== ADDRESS DEBUG END ===");
            }
            
            // Build address in correct order: [Number] [Prefix] [Street Name] [Street Type] [Suffix]
            // Use more defensive approach to handle any potential issues
            $address_parts = array();
            
            if (!empty($street_number)) {
                $address_parts[] = $street_number;
            }
            if (!empty($street_dir_prefix)) {
                $address_parts[] = $street_dir_prefix;
            }
            if (!empty($street_name)) {
                $address_parts[] = $street_name;
            }
            if (!empty($street_type)) {
                $address_parts[] = $street_type;
            }
            if (!empty($street_dir_suffix)) {
                $address_parts[] = $street_dir_suffix;
            }
            
            $address = implode(' ', $address_parts);
            
            // Debug the final address
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Address Debug - Final Address: '$address'");
                error_log("Address Debug - Address Parts count: " . count($address_parts));
                foreach ($address_parts as $i => $part) {
                    error_log("Address Debug - Part $i: '$part' (length: " . strlen($part) . ")");
                }
            }
            
            if ($address && $city) {
                $property_title = $address . ', ' . $city;
                if ($state) {
                    $property_title .= ', ' . $state;
                }
            } else {
                $property_title = 'New Listing'; // Fallback if no address
            }
        }

        // Determine post status
        $post_status = 'publish'; // Default for new listings
        if ($is_update) {
            // For updates, preserve existing status unless explicitly changed
            $current_status = get_post_status($listing_id);
            $post_status = isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : $current_status;
        } else {
            // For new listings, use provided status or default to draft
            $post_status = sanitize_text_field($_POST['post_status'] ?? 'draft');
        }

        // Prepare post data
        $post_data = [
            'post_title' => $property_title,
            'post_content' => sanitize_textarea_field($_POST['property_description'] ?? ''),
            'post_type' => 'listing',
            'post_status' => $post_status,
        ];        if ($is_update) {
            $post_data['ID'] = $listing_id;
            
            // Check permissions for updating
            if (!current_user_can('edit_post', $listing_id)) {
                wp_send_json_error('Insufficient permissions to edit this listing');
            }
            
            $result = wp_update_post($post_data);
        } else {
            $post_data['post_author'] = get_current_user_id();
            $result = wp_insert_post($post_data);
            $listing_id = $result;
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error('Failed to save listing: ' . $result->get_error_message());
        }
        
        if (!$result) {
            wp_send_json_error('Failed to save listing');
        }
        
        // Determine listing status for ACF field (separate from post_status)
        $listing_status = 'active'; // Default for new listings
        if ($is_update) {
            // For updates, preserve existing listing status unless explicitly changed
            $current_listing_status = get_field('listing_status', $listing_id) ?: 'active';
            $listing_status = isset($_POST['listing_status']) ? sanitize_text_field($_POST['listing_status']) : $current_listing_status;
        } else {
            // For new listings, use provided status or default to active
            $listing_status = sanitize_text_field($_POST['listing_status'] ?? 'active');
        }
        
        // If post is being published but listing_status is empty, set to active
        if ($post_status === 'publish' && empty($listing_status)) {
            $listing_status = 'active';
        }
        
        // Save ACF fields - using actual form field names
        $acf_fields = [
            'price' => floatval($_POST['listing_price'] ?? 0),
            'listing_status' => $listing_status,
            'listing_agent' => sanitize_text_field($_POST['listing_agent'] ?? ''),
            'mls_number' => sanitize_text_field($_POST['mls_number'] ?? ''),
            'street_number' => sanitize_text_field($_POST['street_number'] ?? ''),
            'street_name' => sanitize_text_field($_POST['street_name'] ?? ''),
            'street_type' => sanitize_text_field($_POST['street_type'] ?? ''),
            'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['state']),
            'zip_code' => sanitize_text_field($_POST['zip_code'] ?? ''),
            'bedrooms' => intval($_POST['bedrooms'] ?? 0),
            'bathrooms_full' => intval($_POST['bathrooms_full'] ?? 0),
            'bathrooms_half' => intval($_POST['bathrooms_half'] ?? 0),
            'square_feet' => intval($_POST['square_feet'] ?? 0),
            'lot_size_acres' => floatval($_POST['lot_size_acres'] ?? 0),
            'year_built' => intval($_POST['year_built'] ?? 0),
            'garage' => intval($_POST['garage'] ?? 0),
            'property_description' => sanitize_textarea_field($_POST['property_description'] ?? ''),
            'showing_instructions' => sanitize_textarea_field($_POST['showing_instructions'] ?? ''),
            'internal_notes' => sanitize_textarea_field($_POST['internal_notes'] ?? ''),
        ];
        
        // Save features if provided
        if (!empty($_POST['features']) && is_array($_POST['features'])) {
            $acf_fields['features'] = array_map('sanitize_text_field', $_POST['features']);
        }
        
        foreach ($acf_fields as $field_name => $field_value) {
            update_field($field_name, $field_value, $listing_id);
        }
        
        // Handle file uploads if present
        if (!empty($_FILES['featured_image']['name'])) {
            $featured_image_id = $this->handle_image_upload('featured_image');
            if ($featured_image_id) {
                set_post_thumbnail($listing_id, $featured_image_id);
            }
        }
        
        // Handle gallery images
        if (!empty($_FILES['gallery_images']['name'][0])) {
            $gallery_ids = $this->handle_gallery_upload('gallery_images');
            if (!empty($gallery_ids)) {
                update_field('gallery', $gallery_ids, $listing_id);
            }
        }
        
        wp_send_json_success([
            'message' => $is_update ? 'Listing updated successfully! Redirecting to fresh form...' : 'Listing created successfully! Redirecting to add another listing...',
            'listing_id' => $listing_id,
            'redirect' => home_url('/listing-form/') // Redirect to fresh listing form
        ]);
    }
    
    /**
     * Handle single image upload
     */
    private function handle_image_upload($file_key) {
        if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $attachment_id = media_handle_upload($file_key, 0);
        
        if (is_wp_error($attachment_id)) {
            error_log('Image upload error: ' . $attachment_id->get_error_message());
            return false;
        }
        
        return $attachment_id;
    }
    
    /**
     * Handle gallery upload
     */
    private function handle_gallery_upload($file_key) {
        if (!isset($_FILES[$file_key]) || empty($_FILES[$file_key]['name'][0])) {
            return [];
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $gallery_ids = [];
        $files = $_FILES[$file_key];
        $file_count = count($files['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];
            
            $_FILES[$file_key . '_' . $i] = $file;
            $attachment_id = media_handle_upload($file_key . '_' . $i, 0);
            
            if (!is_wp_error($attachment_id)) {
                $gallery_ids[] = $attachment_id;
            }
        }
        
        return $gallery_ids;
    }
    
    /**
     * Schedule Open House
     */
    public function schedule_open_house() {
        $this->verify_nonce();
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $listing_id = intval($_POST['listing_id']);
        $listing_title = get_the_title($listing_id) ?: 'Property';
        
        $open_house_data = array(
            'post_title' => 'Open House - ' . $listing_title,
            'post_type' => 'open_house',
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        );
        
        $open_house_id = wp_insert_post($open_house_data);
        
        if (is_wp_error($open_house_id)) {
            wp_send_json_error($open_house_id->get_error_message());
        }
        
        // Update fields
        update_field('listing_id', $listing_id, $open_house_id);
        update_field('event_date', sanitize_text_field($_POST['date']), $open_house_id);
        update_field('start_time', sanitize_text_field($_POST['start_time']), $open_house_id);
        update_field('end_time', sanitize_text_field($_POST['end_time']), $open_house_id);
        update_field('notes', sanitize_textarea_field($_POST['notes']), $open_house_id);
        
        wp_send_json_success(array(
            'message' => 'Open house scheduled successfully',
            'open_house_id' => $open_house_id
        ));
    }
    
    /**
     * Create Lead
     */
    public function create_lead() {
        $this->verify_nonce();
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $full_name = trim($first_name . ' ' . $last_name);
        
        $lead_data = array(
            'post_title' => $full_name,
            'post_content' => sanitize_textarea_field($_POST['notes']),
            'post_type' => 'lead',
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        );
        
        $lead_id = wp_insert_post($lead_data);
        
        if (is_wp_error($lead_id)) {
            wp_send_json_error($lead_id->get_error_message());
        }
        
        // Update ACF fields
        update_field('first_name', $first_name, $lead_id);
        update_field('last_name', $last_name, $lead_id);
        update_field('email', sanitize_email($_POST['email']), $lead_id);
        update_field('phone', sanitize_text_field($_POST['phone']), $lead_id);
        update_field('lead_source', sanitize_text_field($_POST['source']), $lead_id);
        update_field('lead_status', 'new', $lead_id);
        update_field('lead_score', 50, $lead_id); // Default score
        
        wp_send_json_success(array(
            'message' => 'Lead created successfully',
            'lead_id' => $lead_id
        ));
    }
    
    /**
     * Create Transaction
     */
    public function create_transaction() {
        $this->verify_nonce();
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $listing_id = intval($_POST['listing_id']);
        $listing_title = get_the_title($listing_id) ?: 'Property';
        $transaction_type = sanitize_text_field($_POST['transaction_type']);
        
        $transaction_data = array(
            'post_title' => ucfirst($transaction_type) . ' - ' . $listing_title,
            'post_content' => sanitize_textarea_field($_POST['notes']),
            'post_type' => 'transaction',
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        );
        
        $transaction_id = wp_insert_post($transaction_data);
        
        if (is_wp_error($transaction_id)) {
            wp_send_json_error($transaction_id->get_error_message());
        }
        
        // Update ACF fields
        update_field('listing_id', $listing_id, $transaction_id);
        update_field('transaction_type', $transaction_type, $transaction_id);
        update_field('status', sanitize_text_field($_POST['status']), $transaction_id);
        update_field('sale_price', floatval($_POST['sale_price']), $transaction_id);
        update_field('commission', floatval($_POST['commission']), $transaction_id);
        update_field('contract_date', sanitize_text_field($_POST['contract_date']), $transaction_id);
        update_field('close_date', sanitize_text_field($_POST['close_date']), $transaction_id);
        
        wp_send_json_success(array(
            'message' => 'Transaction created successfully',
            'transaction_id' => $transaction_id
        ));
    }
    
    /**
     * Generate CMA
     */
    public function generate_cma() {
        $this->verify_nonce();
        
        $address = sanitize_text_field($_POST['address']);
        $radius = floatval($_POST['radius']);
        $property_type = sanitize_text_field($_POST['property_type']);
        
        // Get comparable properties
        $comparables = $this->get_comparable_properties($address, $radius, $property_type);
        
        // Generate CMA data
        $cma_data = array(
            'subject_property' => array(
                'address' => $address,
                'estimated_value' => $this->calculate_estimated_value($comparables)
            ),
            'comparables' => $comparables,
            'market_trends' => $this->get_market_trends($address),
            'generated_date' => current_time('mysql'),
            'agent_id' => get_current_user_id()
        );
        
        // Generate PDF (requires PDF library)
        // $pdf_url = $this->generate_cma_pdf($cma_data);
        
        wp_send_json_success(array(
            'message' => 'CMA generated successfully',
            'cma_data' => $cma_data,
            // 'pdf_url' => $pdf_url
        ));
    }
    
    /**
     * Export Data
     */
    public function export_data() {
        $this->verify_nonce();
        
        $export_type = sanitize_text_field($_POST['export_type']);
        $format = sanitize_text_field($_POST['format']);
        
        $data = array();
        
        switch ($export_type) {
            case 'listings':
                $data = $this->get_user_listings_for_export();
                break;
            case 'leads':
                $data = $this->get_user_leads_for_export();
                break;
            case 'transactions':
                $data = $this->get_user_transactions_for_export();
                break;
        }
        
        if ($format === 'csv') {
            $file_url = $this->generate_csv($data, $export_type);
        } else {
            $file_url = $this->generate_json($data, $export_type);
        }
        
        wp_send_json_success(array(
            'message' => 'Data exported successfully',
            'file_url' => $file_url
        ));
    }
    
    /**
     * Dashboard Search
     */
    public function dashboard_search() {
        $this->verify_nonce();
        
        $query = sanitize_text_field($_POST['query']);
        $results = array();
        
        // Search listings
        $listings = get_posts(array(
            's' => $query,
            'post_type' => 'listing',
            'author' => get_current_user_id(),
            'posts_per_page' => 5
        ));
        
        foreach ($listings as $listing) {
            $results[] = array(
                'type' => 'listing',
                'title' => get_the_title($listing),
                'url' => get_permalink($listing),
                'meta' => get_field('address', $listing->ID)
            );
        }
        
        // Search leads
        $leads = get_posts(array(
            's' => $query,
            'post_type' => 'lead',
            'author' => get_current_user_id(),
            'posts_per_page' => 5
        ));
        
        foreach ($leads as $lead) {
            $results[] = array(
                'type' => 'lead',
                'title' => get_the_title($lead),
                'url' => get_edit_post_link($lead->ID),
                'meta' => get_field('email', $lead->ID)
            );
        }
        
        wp_send_json_success(array('results' => $results));
    }
    
    /**
     * Get Listing Stats for Dashboard
     */
    public function get_listing_stats() {
        $this->verify_nonce();
        
        // Query all listings (not filtering by agent due to relationship field issues)
        $args = [
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft']
        ];

        $listings = get_posts($args);
        $total = count($listings);
        $active = 0;
        $pending = 0;
        $sold = 0;
        
        // If no real listings, return sample stats
        if ($total == 0) {
            wp_send_json_success([
                'total' => 2,
                'active' => 1,
                'pending' => 1,
                'sold' => 0
            ]);
            return;
        }

        foreach ($listings as $listing) {
            $status = get_field('listing_status', $listing->ID);
            switch ($status) {
                case 'active':
                    $active++;
                    break;
                case 'pending':
                    $pending++;
                    break;
                case 'sold':
                    $sold++;
                    break;
                default:
                    $active++; // Default to active
            }
        }

        wp_send_json_success([
            'total' => $total,
            'active' => $active,
            'pending' => $pending,
            'sold' => $sold
        ]);
    }
    
    /**
     * Get Listings for Dashboard
     */
    public function get_listings() {
        $this->verify_nonce();
        
        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 12);
        $status = sanitize_text_field($_POST['status'] ?? 'all');
        $sort = sanitize_text_field($_POST['sort'] ?? 'date-desc');
        $search = sanitize_text_field($_POST['search'] ?? '');

        // Debug logging
        error_log('HPH Dashboard: get_listings called with status=' . $status . ', search=' . $search . ', sort=' . $sort);

        // Base query args - show listings based on status filter
        $args = [
            'post_type' => 'listing',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => []
        ];
        
        // Set post status based on filter
        if ($status === 'draft') {
            $args['post_status'] = ['draft'];
        } else {
            // For all other statuses, prioritize published posts but include drafts if needed
            $args['post_status'] = ['publish', 'draft'];
        }

        // Add search
        if (!empty($search)) {
            $args['s'] = $search;
        }

        // Add status filter
        if ($status !== 'all') {
            $args['meta_query'] = [
                [
                    'key' => 'listing_status',
                    'value' => $status,
                    'compare' => '='
                ]
            ];
        }

        // Add sorting
        switch ($sort) {
            case 'date-asc':
                $args['orderby'] = 'date';
                $args['order'] = 'ASC';
                break;
            case 'price-desc':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'price';
                $args['order'] = 'DESC';
                break;
            case 'price-asc':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'price';
                $args['order'] = 'ASC';
                break;
            case 'address-asc':
                $args['orderby'] = 'meta_value';
                $args['meta_key'] = 'street_address';
                $args['order'] = 'ASC';
                break;
            case 'status-asc':
                $args['orderby'] = 'meta_value';
                $args['meta_key'] = 'listing_status';
                $args['order'] = 'ASC';
                break;
            default: // date-desc
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }

        $query = new \WP_Query($args);
        
        // Debug logging
        error_log('HPH Dashboard: Query found ' . $query->found_posts . ' listings');
        
        $listings = [];
        $processed_listings = []; // Track listings by title+address to prevent duplicates
        
        // If no listings found, create a sample listing for testing
        if ($query->found_posts == 0) {
            $listings[] = [
                'id' => 999,
                'title' => 'Sample Property - 123 Main St',
                'price' => 450000,
                'status' => 'active',
                'status_label' => 'Active',
                'address' => '123 Main Street, Anytown, CA 12345',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_feet' => 1800,
                'mls_number' => 'ML123456',
                'featured_image' => '',
                'date_created' => current_time('Y-m-d H:i:s'),
                'date_modified' => current_time('Y-m-d H:i:s')
            ];
            
            $listings[] = [
                'id' => 998,
                'title' => 'Beautiful Home - 456 Oak Ave',
                'price' => 650000,
                'status' => 'pending',
                'status_label' => 'Pending',
                'address' => '456 Oak Avenue, Somewhere, CA 54321',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'square_feet' => 2400,
                'mls_number' => 'ML789012',
                'featured_image' => '',
                'date_created' => current_time('Y-m-d H:i:s'),
                'date_modified' => current_time('Y-m-d H:i:s')
            ];
            
            error_log('HPH Dashboard: Created sample listings for testing');
        }

        foreach ($query->posts as $post) {
            $listing_id = $post->ID;
            
            // Get listing data
            $price = get_field('price', $listing_id) ?: 0;
            $status = get_field('listing_status', $listing_id) ?: 'active';
            $address = get_field('street_address', $listing_id) ?: '';
            $city = get_field('city', $listing_id) ?: '';
            $state = get_field('state', $listing_id) ?: '';
            
            // Create a unique key based on title and address to detect duplicates
            $unique_key = strtolower(trim($post->post_title . '|' . $address . '|' . $city . '|' . $state));
            
            // Check if we've already processed this listing
            if (isset($processed_listings[$unique_key])) {
                error_log("HPH Dashboard: Found duplicate listing - {$post->post_title} (ID: {$listing_id}, Status: {$post->post_status})");
                
                // If current post is published and previous was draft, replace it
                if ($post->post_status === 'publish' && $processed_listings[$unique_key]['post_status'] === 'draft') {
                    error_log("HPH Dashboard: Replacing draft with published version - {$post->post_title}");
                    // Remove the previous draft version from listings array
                    $listings = array_filter($listings, function($listing) use ($processed_listings, $unique_key) {
                        return $listing['id'] !== $processed_listings[$unique_key]['id'];
                    });
                    // Continue processing this published version
                } else {
                    // Skip this duplicate (either both published or current is draft)
                    error_log("HPH Dashboard: Skipping duplicate listing - {$post->post_title} (keeping existing)");
                    continue;
                }
            }
            $bedrooms = get_field('bedrooms', $listing_id) ?: 0;
            $bathrooms_full = get_field('bathrooms_full', $listing_id) ?: 0;
            $bathrooms_half = get_field('bathrooms_half', $listing_id) ?: 0;
            $square_feet = get_field('square_feet', $listing_id) ?: 0;
            $lot_size_acres = get_field('lot_size_acres', $listing_id) ?: 0;
            $mls_number = get_field('mls_number', $listing_id) ?: '';
            $days_on_market = get_field('days_on_market', $listing_id) ?: 0;
            
            // Get listing agent
            $agent_id = get_field('listing_agent', $listing_id);
            $agent_name = '';
            if ($agent_id) {
                if (is_array($agent_id)) {
                    $agent_id = $agent_id[0]; // Get first agent if multiple
                }
                $agent_name = get_the_title($agent_id);
            }
            
            // Calculate total bathrooms
            $total_bathrooms = $bathrooms_full + ($bathrooms_half * 0.5);

            // Build full address
            $full_address = trim($address . ', ' . $city . ', ' . $state, ', ');

            // Get featured image
            $featured_image = has_post_thumbnail($listing_id) 
                ? get_the_post_thumbnail_url($listing_id, 'medium')
                : '';

            $listing_data = [
                'id' => $listing_id,
                'title' => $post->post_title,
                'price' => $price,
                'status' => $status,
                'status_label' => ucfirst($status),
                'post_status' => $post->post_status,
                'address' => $full_address,
                'bedrooms' => $bedrooms,
                'bathrooms' => $total_bathrooms,
                'square_feet' => $square_feet,
                'lot_size_acres' => $lot_size_acres,
                'mls_number' => $mls_number,
                'agent_name' => $agent_name,
                'days_on_market' => $days_on_market,
                'featured_image' => $featured_image,
                'date_created' => $post->post_date,
                'date_modified' => $post->post_modified
            ];
            
            $listings[] = $listing_data;
            
            // Track this listing to prevent future duplicates
            $processed_listings[$unique_key] = [
                'id' => $listing_id,
                'post_status' => $post->post_status
            ];
        }

        // Generate HTML for listings
        $html = '';
        foreach ($listings as $listing) {
            $html .= $this->generate_listing_card_html($listing);
        }
        
        // Generate pagination HTML
        $pagination = '';
        if ($query->max_num_pages > 1) {
            $pagination = $this->generate_pagination_html($page, $query->max_num_pages);
        }

        $total_count = $query->found_posts > 0 ? $query->found_posts : count($listings);
        
        wp_send_json_success([
            'listings' => $html,
            'total' => $total_count,
            'pages' => $query->max_num_pages > 0 ? $query->max_num_pages : 1,
            'current_page' => $page,
            'pagination' => $pagination
        ]);
    }

    /**
     * Generate HTML for a single listing card
     */
    private function generate_listing_card_html($listing) {
        $price_formatted = '$' . number_format($listing['price']);
        $details = [];
        
        // Build property details
        if ($listing['bedrooms']) {
            $details[] = $listing['bedrooms'] . ' bed';
        }
        if ($listing['bathrooms']) {
            // Format bathrooms (show as decimal if needed, otherwise as whole number)
            $bath_display = $listing['bathrooms'] == floor($listing['bathrooms']) 
                ? (int)$listing['bathrooms'] . ' bath'
                : $listing['bathrooms'] . ' bath';
            $details[] = $bath_display;
        }
        if ($listing['square_feet']) {
            $details[] = number_format($listing['square_feet']) . ' sqft';
        }
        if ($listing['lot_size_acres']) {
            $lot_display = $listing['lot_size_acres'] >= 1 
                ? number_format($listing['lot_size_acres'], 2) . ' acres'
                : number_format($listing['lot_size_acres'] * 43560) . ' sqft lot';
            $details[] = $lot_display;
        }
        
        $details_str = implode(' • ', $details);
        $image_html = $listing['featured_image'] 
            ? '<img src="' . esc_url($listing['featured_image']) . '" alt="' . esc_attr($listing['title']) . '" class="listing-image">'
            : '<div class="listing-image" style="background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af;">No Image</div>';
        
        // Build days on market display
        $dom_display = '';
        if ($listing['days_on_market'] > 0) {
            $dom_display = '<div style="font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem;">' . 
                          $listing['days_on_market'] . ' days on market</div>';
        }
        
        // Build agent display
        $agent_display = '';
        if ($listing['agent_name']) {
            $agent_display = '<div style="font-size: 0.8rem; color: #4b5563; margin-top: 0.25rem;">' .
                            '<i class="fas fa-user" style="margin-right: 0.25rem;"></i>' .
                            esc_html($listing['agent_name']) . '</div>';
        }
        
        // Add draft indicator if this is a draft post
        $draft_indicator = '';
        if (isset($listing['post_status']) && $listing['post_status'] === 'draft') {
            $draft_indicator = '<div class="listing-draft-badge" style="position: absolute; top: 10px; left: 10px; background: #f59e0b; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; z-index: 10;">DRAFT</div>';
        }
        
        // Add slight opacity for draft posts
        $card_style = isset($listing['post_status']) && $listing['post_status'] === 'draft' 
            ? 'position: relative; opacity: 0.85; border: 2px dashed #f59e0b;' 
            : 'position: relative;';
        
        // Check permissions for this listing
        $can_edit = $this->can_user_edit_listing($listing['id']);
        $can_delete = $this->can_user_delete_listing($listing['id']);
        
        // Build action buttons based on permissions
        $action_buttons = '';
        if ($can_edit) {
            $action_buttons .= '<button class="btn-sm" onclick="editListing(' . $listing['id'] . ')" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>';
        }
        if ($can_delete) {
            $action_buttons .= '<button class="btn-sm" onclick="deleteListing(' . $listing['id'] . ')" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>';
        }
        
        // Show locked indicator if user can't edit
        $locked_indicator = '';
        if (!$can_edit) {
            $locked_indicator = '<div class="listing-locked" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 0.25rem; border-radius: 4px; z-index: 10;" title="You can only edit listings where you are the assigned agent">
                <i class="fas fa-lock" style="font-size: 0.75rem;"></i>
            </div>';
        }
        
        return '
        <div class="listing-card" style="' . $card_style . '">
            ' . $image_html . '
            ' . $draft_indicator . '
            ' . $locked_indicator . '
            <div class="listing-status status-' . $listing['status'] . '">' . $listing['status_label'] . '</div>
            ' . ($action_buttons ? '<div class="listing-actions">' . $action_buttons . '</div>' : '') . '
            <div class="listing-content">
                <div class="listing-price">' . $price_formatted . '</div>
                <h3 class="listing-title">' . esc_html($listing['title']) . '</h3>
                <div class="listing-location">
                    <i class="fas fa-map-marker-alt"></i>
                    ' . esc_html($listing['address']) . '
                </div>
                <div class="listing-details">' . $details_str . '</div>
                ' . $agent_display . '
                ' . ($listing['mls_number'] ? '<div style="font-size: 0.8rem; color: #6b7280;">MLS: ' . esc_html($listing['mls_number']) . '</div>' : '') . '
                ' . $dom_display . '
            </div>
        </div>';
    }

    /**
     * Generate pagination HTML
     */
    private function generate_pagination_html($current_page, $max_pages) {
        if ($max_pages <= 1) return '';
        
        $html = '<div class="pagination-wrapper" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">';
        
        // Previous button
        if ($current_page > 1) {
            $html .= '<button class="btn btn-sm" onclick="ListingsDashboard.currentPage = ' . ($current_page - 1) . '; ListingsDashboard.loadListings();">« Previous</button>';
        }
        
        // Page numbers
        $start = max(1, $current_page - 2);
        $end = min($max_pages, $current_page + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i === $current_page) ? 'style="background: var(--hph-primary); color: white;"' : '';
            $html .= '<button class="btn btn-sm" ' . $active . ' onclick="ListingsDashboard.currentPage = ' . $i . '; ListingsDashboard.loadListings();">' . $i . '</button>';
        }
        
        // Next button
        if ($current_page < $max_pages) {
            $html .= '<button class="btn btn-sm" onclick="ListingsDashboard.currentPage = ' . ($current_page + 1) . '; ListingsDashboard.loadListings();">Next »</button>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Get Single Listing
     */
    public function get_single_listing() {
        $this->verify_nonce();
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        if (!$listing_id) {
            wp_send_json_error('Missing listing ID');
        }

        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            wp_send_json_error('Listing not found');
        }

        // Get all listing data
        $listing_data = [
            'id' => $listing_id,
            'title' => $listing->post_title,
            'description' => $listing->post_content,
            'price' => get_field('price', $listing_id) ?: 0,
            'status' => get_field('listing_status', $listing_id) ?: 'active',
            'bedrooms' => get_field('bedrooms', $listing_id) ?: 0,
            'bathrooms' => get_field('full_bathrooms', $listing_id) ?: 0,
            'square_feet' => get_field('square_feet', $listing_id) ?: 0,
            'address' => get_field('street_address', $listing_id) ?: '',
            'city' => get_field('city', $listing_id) ?: '',
            'state' => get_field('state', $listing_id) ?: '',
            'zip_code' => get_field('zip_code', $listing_id) ?: '',
            'mls_number' => get_field('mls_number', $listing_id) ?: '',
            'year_built' => get_field('year_built', $listing_id) ?: '',
            'property_type' => get_field('property_type', $listing_id) ?: '',
            'featured' => get_field('featured_listing', $listing_id) ?: false
        ];

        wp_send_json_success($listing_data);
    }

    /**
     * REST API Methods
     */
    public function get_dashboard_stats_rest($request) {
        $user_id = get_current_user_id();
        
        return array(
            'active_listings' => $this->count_user_listings($user_id, 'active'),
            'closed_this_month' => $this->count_closed_transactions($user_id),
            'new_leads' => $this->count_new_leads($user_id),
            'open_houses' => $this->count_upcoming_open_houses($user_id)
        );
    }
    
    public function get_recent_activity_rest($request) {
        $this->get_recent_activity();
    }
    
    public function get_upcoming_events_rest($request) {
        $this->get_upcoming_events();
    }
    
    public function get_hot_leads_rest($request) {
        $this->get_hot_leads();
    }
    
    /**
     * Helper Methods
     */
    
    /**
     * Check if current user can edit a specific listing
     */
    private function can_user_edit_listing($listing_id) {
        $current_user_id = get_current_user_id();
        
        // Admins can edit all listings
        if (current_user_can('administrator') || current_user_can('manage_options')) {
            return true;
        }
        
        // Get the assigned listing agent(s)
        $listing_agent = get_field('listing_agent', $listing_id);
        
        if (!$listing_agent) {
            // If no agent assigned, only admins can edit
            return false;
        }
        
        // Handle both single agent and multiple agents
        $agent_ids = is_array($listing_agent) ? $listing_agent : [$listing_agent];
        
        // Check if current user is one of the assigned agents
        foreach ($agent_ids as $agent_id) {
            // Get the synced user ID for this agent
            $synced_user_id = get_post_meta($agent_id, '_synced_user_id', true);
            
            if ($synced_user_id && $synced_user_id == $current_user_id) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if current user can delete a specific listing
     */
    private function can_user_delete_listing($listing_id) {
        // For now, use same permissions as edit
        // You can make this more restrictive if needed
        return $this->can_user_edit_listing($listing_id);
    }
    
    private function count_user_listings($user_id, $status = 'all') {
        $args = array(
            'post_type' => 'listing',
            'author' => $user_id,
            'post_status' => ['publish', 'draft']
        );
        
        if ($status !== 'all') {
            $args['meta_query'] = array(
                array(
                    'key' => 'listing_status',
                    'value' => $status
                )
            );
        }
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function count_closed_transactions($user_id) {
        $args = array(
            'post_type' => 'transaction',
            'author' => $user_id,
            'meta_query' => array(
                array(
                    'key' => 'status',
                    'value' => 'closed'
                ),
                array(
                    'key' => 'close_date',
                    'value' => date('Y-m-01'),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            )
        );
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function count_new_leads($user_id) {
        $week_ago = date('Y-m-d', strtotime('-1 week'));
        
        $args = array(
            'post_type' => 'lead',
            'author' => $user_id,
            'date_query' => array(
                array(
                    'after' => $week_ago
                )
            )
        );
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function count_upcoming_open_houses($user_id) {
        $args = array(
            'post_type' => 'open_house',
            'author' => $user_id,
            'meta_query' => array(
                array(
                    'key' => 'event_date',
                    'value' => current_time('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            )
        );
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    /**
     * Integration Methods
     */
    private function sync_listing_to_airtable($listing_id) {
        $api_key = get_option('hph_airtable_api_key');
        $base_id = get_option('hph_airtable_base_id');
        
        if (!$api_key || !$base_id) {
            return false;
        }
        
        $listing = hpt_get_listing($listing_id);
        
        $url = "https://api.airtable.com/v0/{$base_id}/Listings";
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'fields' => $listing
            ))
        ));
        
        return !is_wp_error($response);
    }
    
    private function get_followupboss_leads($user_id) {
        $api_key = get_option('hph_followupboss_api_key');
        
        if (!$api_key) {
            return array();
        }
        
        $url = "https://api.followupboss.com/v1/people";
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($api_key . ':'),
                'Content-Type' => 'application/json'
            )
        ));
        
        if (is_wp_error($response)) {
            return array();
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return isset($data['people']) ? $data['people'] : array();
    }
    
    /**
     * Enhanced Phase 1 AJAX Handlers
     */
    
    /**
     * Handle bulk actions on multiple listings
     */
    public function bulk_listing_actions() {
        // Verify nonce and permissions
        if (!check_ajax_referer('hph_dashboard_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'happy-place-theme')));
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'happy-place-theme')));
            return;
        }
        
        $action = sanitize_text_field($_POST['bulk_action']);
        $listing_ids = array_map('absint', $_POST['listing_ids']);
        
        if (empty($listing_ids)) {
            wp_send_json_error(array('message' => __('No listings selected', 'happy-place-theme')));
            return;
        }
        
        $updated = 0;
        $errors = 0;
        $permission_errors = 0;
        
        foreach ($listing_ids as $listing_id) {
            // Check if user can edit this specific listing
            if (!$this->can_user_edit_listing($listing_id)) {
                $permission_errors++;
                continue;
            }
            
            switch ($action) {
                case 'activate':
                    update_field('listing_status', 'active', $listing_id);
                    $updated++;
                    break;
                    
                case 'deactivate':
                    update_field('listing_status', 'pending', $listing_id);
                    $updated++;
                    break;
                    
                case 'sold':
                    update_field('listing_status', 'sold', $listing_id);
                    $updated++;
                    break;
                    
                case 'draft':
                    update_field('listing_status', 'draft', $listing_id);
                    $updated++;
                    break;
                    
                case 'delete':
                    if (wp_trash_post($listing_id)) {
                        $updated++;
                    } else {
                        $errors++;
                    }
                    break;
                    
                default:
                    $errors++;
            }
        }
        
        // Build response message
        $message_parts = [];
        if ($updated > 0) {
            $message_parts[] = sprintf(__('Updated %d listings', 'happy-place-theme'), $updated);
        }
        if ($permission_errors > 0) {
            $message_parts[] = sprintf(__('%d listings skipped (no permission)', 'happy-place-theme'), $permission_errors);
        }
        if ($errors > 0) {
            $message_parts[] = sprintf(__('%d errors', 'happy-place-theme'), $errors);
        }
        
        $final_message = implode('. ', $message_parts);
        
        wp_send_json_success(array(
            'message' => $final_message,
            'updated' => $updated,
            'permission_errors' => $permission_errors,
            'errors' => $errors
        ));
    }
    
    /**
     * Filter listings based on advanced criteria
     */
    public function filter_listings() {
        if (!check_ajax_referer('hph_dashboard_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'happy-place-theme')));
            return;
        }
        
        $filters = $_POST['filters'];
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 20;
        
        $args = array(
            'post_type' => 'listing',
            'post_status' => ['publish', 'draft'],
            'author' => get_current_user_id(),
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => array('relation' => 'AND'),
        );
        
        // Add filters
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $args['meta_query'][] = array(
                'key' => 'listing_status',
                'value' => sanitize_text_field($filters['status']),
                'compare' => '='
            );
        }
        
        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $price_query = array('key' => 'price', 'type' => 'NUMERIC');
            
            if (!empty($filters['min_price']) && !empty($filters['max_price'])) {
                $price_query['value'] = array(absint($filters['min_price']), absint($filters['max_price']));
                $price_query['compare'] = 'BETWEEN';
            } elseif (!empty($filters['min_price'])) {
                $price_query['value'] = absint($filters['min_price']);
                $price_query['compare'] = '>=';
            } elseif (!empty($filters['max_price'])) {
                $price_query['value'] = absint($filters['max_price']);
                $price_query['compare'] = '<=';
            }
            
            $args['meta_query'][] = $price_query;
        }
        
        if (!empty($filters['property_type'])) {
            $args['meta_query'][] = array(
                'key' => 'property_type',
                'value' => sanitize_text_field($filters['property_type']),
                'compare' => '='
            );
        }
        
        if (!empty($filters['bedrooms'])) {
            $args['meta_query'][] = array(
                'key' => 'bedrooms',
                'value' => absint($filters['bedrooms']),
                'compare' => '>='
            );
        }
        
        // Date filters
        if (!empty($filters['date_listed'])) {
            switch ($filters['date_listed']) {
                case 'today':
                    $args['date_query'] = array(
                        'after' => '1 day ago'
                    );
                    break;
                    
                case 'week':
                    $args['date_query'] = array(
                        'after' => '1 week ago'
                    );
                    break;
                    
                case 'month':
                    $args['date_query'] = array(
                        'after' => '1 month ago'
                    );
                    break;
                    
                case 'quarter':
                    $args['date_query'] = array(
                        'after' => '3 months ago'
                    );
                    break;
            }
        }
        
        // Search query
        if (!empty($filters['search'])) {
            $args['s'] = sanitize_text_field($filters['search']);
        }
        
        // Sorting
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price-asc':
                    $args['meta_key'] = 'price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                    break;
                    
                case 'price-desc':
                    $args['meta_key'] = 'price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                    
                case 'date-asc':
                    $args['orderby'] = 'date';
                    $args['order'] = 'ASC';
                    break;
                    
                case 'date-desc':
                default:
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                    break;
            }
        }
        
        $query = new WP_Query($args);
        $listings = array();
        
        while ($query->have_posts()) {
            $query->the_post();
            $listing_id = get_the_ID();
            
            $listings[] = array(
                'id' => $listing_id,
                'title' => get_the_title(),
                'price' => get_field('price', $listing_id),
                'status' => get_field('listing_status', $listing_id),
                'bedrooms' => get_field('bedrooms', $listing_id),
                'bathrooms' => get_field('bathrooms_full', $listing_id),
                'square_feet' => get_field('square_feet', $listing_id),
                'featured_image' => get_the_post_thumbnail_url($listing_id, 'medium'),
                'permalink' => get_permalink($listing_id),
                'date_listed' => get_the_date('c', $listing_id),
                'views' => get_post_meta($listing_id, '_listing_views', true) ?: 0,
                'leads_count' => $this->get_listing_leads_count($listing_id)
            );
        }
        
        wp_reset_postdata();
        
        wp_send_json_success(array(
            'listings' => $listings,
            'pagination' => array(
                'current_page' => $page,
                'total_pages' => $query->max_num_pages,
                'total_items' => $query->found_posts,
                'per_page' => $per_page
            )
        ));
    }
    
    /**
     * Duplicate a listing
     */
    public function duplicate_listing() {
        if (!check_ajax_referer('hph_dashboard_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'happy-place-theme')));
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'happy-place-theme')));
            return;
        }
        
        $listing_id = absint($_POST['listing_id']);
        
        if (!$listing_id || get_post_field('post_author', $listing_id) != get_current_user_id()) {
            wp_send_json_error(array('message' => __('Invalid listing', 'happy-place-theme')));
            return;
        }
        
        // Get original post
        $original_post = get_post($listing_id);
        if (!$original_post) {
            wp_send_json_error(array('message' => __('Listing not found', 'happy-place-theme')));
            return;
        }
        
        // Create new post
        $new_post = array(
            'post_title' => $original_post->post_title . ' (Copy)',
            'post_content' => $original_post->post_content,
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
            'post_type' => 'listing'
        );
        
        $new_listing_id = wp_insert_post($new_post);
        
        if (is_wp_error($new_listing_id)) {
            wp_send_json_error(array('message' => __('Failed to create copy', 'happy-place-theme')));
            return;
        }
        
        // Copy all custom fields
        $fields = get_fields($listing_id);
        if ($fields) {
            foreach ($fields as $key => $value) {
                update_field($key, $value, $new_listing_id);
            }
        }
        
        // Set as draft status
        update_field('listing_status', 'draft', $new_listing_id);
        
        wp_send_json_success(array(
            'message' => __('Listing duplicated successfully', 'happy-place-theme'),
            'new_listing_id' => $new_listing_id,
            'edit_url' => admin_url('post.php?post=' . $new_listing_id . '&action=edit')
        ));
    }
    
    /**
     * Update listing status
     */
    public function update_listing_status() {
        if (!check_ajax_referer('hph_dashboard_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'happy-place-theme')));
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'happy-place-theme')));
            return;
        }
        
        $listing_id = absint($_POST['listing_id']);
        $new_status = sanitize_text_field($_POST['status']);
        
        if (!$listing_id || get_post_field('post_author', $listing_id) != get_current_user_id()) {
            wp_send_json_error(array('message' => __('Invalid listing', 'happy-place-theme')));
            return;
        }
        
        $allowed_statuses = array('active', 'pending', 'sold', 'draft');
        if (!in_array($new_status, $allowed_statuses)) {
            wp_send_json_error(array('message' => __('Invalid status', 'happy-place-theme')));
            return;
        }
        
        update_field('listing_status', $new_status, $listing_id);
        
        wp_send_json_success(array(
            'message' => sprintf(__('Listing status updated to %s', 'happy-place-theme'), $new_status),
            'new_status' => $new_status
        ));
    }
    
    /**
     * Update listing price via AJAX
     * Handles inline price editing functionality
     */
    public function update_listing_price() {
        if (!check_ajax_referer('hph_dashboard_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'happy-place-theme')));
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'happy-place-theme')));
            return;
        }
        
        $listing_id = absint($_POST['listing_id']);
        $new_price = floatval($_POST['price']);
        
        if (!$listing_id) {
            wp_send_json_error(array('message' => __('Invalid listing ID', 'happy-place-theme')));
            return;
        }
        
        // Check if user owns this listing
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing' || $listing->post_author != get_current_user_id()) {
            wp_send_json_error(array('message' => __('Invalid listing or insufficient permissions', 'happy-place-theme')));
            return;
        }
        
        // Validate price (allow 0 for "Set Price" state, but not negative)
        if ($new_price < 0) {
            wp_send_json_error(array('message' => __('Price cannot be negative', 'happy-place-theme')));
            return;
        }
        
        // Update the price using ACF
        $price_field_updated = update_field('listing_price', $new_price, $listing_id);
        
        if (!$price_field_updated) {
            wp_send_json_error(array('message' => __('Failed to update price', 'happy-place-theme')));
            return;
        }
        
        // Log the price change for analytics
        do_action('hph_listing_price_updated', $listing_id, $new_price, get_current_user_id());
        
        wp_send_json_success(array(
            'message' => sprintf(__('Price updated to $%s', 'happy-place-theme'), number_format($new_price)),
            'new_price' => $new_price,
            'formatted_price' => number_format($new_price)
        ));
    }
    
    /**
     * Get detailed listing information
     */
    public function get_listing_details() {
        if (!check_ajax_referer('hph_dashboard_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'happy-place-theme')));
            return;
        }
        
        $listing_id = absint($_POST['listing_id']);
        
        if (!$listing_id) {
            wp_send_json_error(array('message' => __('Invalid listing ID', 'happy-place-theme')));
            return;
        }
        
        $listing = get_post($listing_id);
        if (!$listing) {
            wp_send_json_error(array('message' => __('Listing not found', 'happy-place-theme')));
            return;
        }
        
        // Get all custom fields
        $fields = get_fields($listing_id);
        
        // Get media
        $featured_image = get_the_post_thumbnail_url($listing_id, 'full');
        $gallery = array();
        
        if (function_exists('get_field') && $gallery_field = get_field('gallery', $listing_id)) {
            foreach ($gallery_field as $image) {
                $gallery[] = array(
                    'id' => $image['ID'],
                    'url' => $image['url'],
                    'title' => $image['title'],
                    'alt' => $image['alt']
                );
            }
        }
        
        wp_send_json_success(array(
            'listing' => array(
                'id' => $listing_id,
                'title' => $listing->post_title,
                'content' => $listing->post_content,
                'status' => $listing->post_status,
                'date_created' => $listing->post_date,
                'date_modified' => $listing->post_modified,
                'permalink' => get_permalink($listing_id),
                'edit_url' => admin_url('post.php?post=' . $listing_id . '&action=edit')
            ),
            'fields' => $fields,
            'media' => array(
                'featured_image' => $featured_image,
                'gallery' => $gallery
            ),
            'stats' => array(
                'views' => get_post_meta($listing_id, '_listing_views', true) ?: 0,
                'leads_count' => $this->get_listing_leads_count($listing_id),
                'favorites_count' => $this->get_listing_favorites_count($listing_id)
            )
        ));
    }
    
    /**
     * Helper: Get listing leads count
     */
    private function get_listing_leads_count($listing_id) {
        $leads = get_posts(array(
            'post_type' => 'lead',
            'meta_query' => array(
                array(
                    'key' => 'interested_listing',
                    'value' => $listing_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        return count($leads);
    }
    
    /**
     * Helper: Get listing favorites count
     */
    private function get_listing_favorites_count($listing_id) {
        global $wpdb;
        
        // Try new user system first
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}user_favorites WHERE listing_id = %d",
            $listing_id
        ));
        
        if ($count === null) {
            // Fallback to user_meta
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'favorite_listings' AND meta_value LIKE %s",
                '%"' . $listing_id . '"%'
            ));
        }
        
        return absint($count);
    }
    
    /**
     * Open House AJAX Handlers - Phase 2
     */
    
    /**
     * Create Open House
     */
    public function create_open_house() {
        $this->verify_nonce();
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        // Validate required fields
        $listing_id = absint($_POST['listing_id'] ?? 0);
        $title = sanitize_text_field($_POST['title'] ?? '');
        $event_date = sanitize_text_field($_POST['event_date'] ?? '');
        $start_time = sanitize_text_field($_POST['start_time'] ?? '');
        $end_time = sanitize_text_field($_POST['end_time'] ?? '');
        
        if (!$listing_id || !$title || !$event_date || !$start_time || !$end_time) {
            wp_send_json_error('Missing required fields');
            return;
        }
        
        // Create open house post
        $open_house_data = array(
            'post_type' => 'open_house',
            'post_title' => $title,
            'post_content' => sanitize_textarea_field($_POST['description'] ?? ''),
            'post_status' => 'publish',
            'meta_input' => array(
                'listing_id' => $listing_id,
                'start_date' => $event_date,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'agent_id' => current_user_can('administrator') && !empty($_POST['agent_id']) 
                    ? absint($_POST['agent_id']) 
                    : get_current_user_id(),
                'max_visitors' => absint($_POST['max_visitors'] ?? 0),
                'require_registration' => !empty($_POST['require_registration']),
                'public_visibility' => !empty($_POST['public_visibility']),
                'send_reminders' => !empty($_POST['send_reminders']),
                'special_instructions' => sanitize_textarea_field($_POST['special_instructions'] ?? ''),
                'timezone' => sanitize_text_field($_POST['timezone'] ?? 'America/New_York'),
                'event_status' => 'scheduled'
            )
        );
        
        $open_house_id = wp_insert_post($open_house_data);
        
        if (is_wp_error($open_house_id)) {
            wp_send_json_error('Failed to create open house: ' . $open_house_id->get_error_message());
            return;
        }
        
        wp_send_json_success(array(
            'message' => 'Open house created successfully',
            'open_house_id' => $open_house_id
        ));
    }
    
    /**
     * Update Open House
     */
    public function update_open_house() {
        $this->verify_nonce();
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $open_house_id = absint($_POST['open_house_id'] ?? 0);
        if (!$open_house_id) {
            wp_send_json_error('Missing open house ID');
            return;
        }
        
        $open_house = get_post($open_house_id);
        if (!$open_house || $open_house->post_type !== 'open_house') {
            wp_send_json_error('Open house not found');
            return;
        }
        
        // Update the post
        $update_data = array(
            'ID' => $open_house_id,
            'post_title' => sanitize_text_field($_POST['title'] ?? $open_house->post_title),
            'post_content' => sanitize_textarea_field($_POST['description'] ?? $open_house->post_content)
        );
        
        wp_update_post($update_data);
        
        // Update meta fields
        $meta_fields = array('start_date', 'start_time', 'end_time', 'max_visitors', 'timezone', 'special_instructions');
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($open_house_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        // Update checkboxes
        $checkbox_fields = array('require_registration', 'public_visibility', 'send_reminders');
        foreach ($checkbox_fields as $field) {
            update_post_meta($open_house_id, $field, !empty($_POST[$field]));
        }
        
        wp_send_json_success(array(
            'message' => 'Open house updated successfully'
        ));
    }
    
    /**
     * Delete Open House
     */
    public function delete_open_house() {
        $this->verify_nonce();
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $open_house_id = absint($_POST['open_house_id'] ?? 0);
        if (!$open_house_id) {
            wp_send_json_error('Missing open house ID');
            return;
        }
        
        // Check if user owns this open house or is admin
        $agent_id = get_post_meta($open_house_id, 'agent_id', true);
        if ($agent_id != get_current_user_id() && !current_user_can('administrator')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $deleted = wp_trash_post($open_house_id);
        
        if ($deleted) {
            wp_send_json_success(array(
                'message' => 'Open house deleted successfully'
            ));
        } else {
            wp_send_json_error('Failed to delete open house');
        }
    }
    
    /**
     * Get Open Houses (with filtering)
     */
    public function get_open_houses() {
        $this->verify_nonce();
        
        $args = array(
            'post_type' => 'open_house',
            'post_status' => 'publish',
            'posts_per_page' => absint($_REQUEST['per_page'] ?? 20),
            'paged' => absint($_REQUEST['page'] ?? 1),
            'orderby' => 'meta_value',
            'meta_key' => 'start_date',
            'order' => 'ASC',
            'meta_query' => array('relation' => 'AND')
        );
        
        // Filter by agent (agents only see their own unless admin)
        if (!current_user_can('administrator')) {
            $args['meta_query'][] = array(
                'key' => 'agent_id',
                'value' => get_current_user_id(),
                'compare' => '='
            );
        } elseif (!empty($_REQUEST['agent'])) {
            $args['meta_query'][] = array(
                'key' => 'agent_id',
                'value' => absint($_REQUEST['agent']),
                'compare' => '='
            );
        }
        
        // Filter by status
        if (!empty($_REQUEST['status'])) {
            $args['meta_query'][] = array(
                'key' => 'event_status',
                'value' => sanitize_text_field($_REQUEST['status']),
                'compare' => '='
            );
        }
        
        // Filter by date range
        if (!empty($_REQUEST['date_from']) || !empty($_REQUEST['date_to'])) {
            $date_query = array('key' => 'start_date');
            
            if (!empty($_REQUEST['date_from']) && !empty($_REQUEST['date_to'])) {
                $date_query['value'] = array(
                    sanitize_text_field($_REQUEST['date_from']),
                    sanitize_text_field($_REQUEST['date_to'])
                );
                $date_query['compare'] = 'BETWEEN';
            } elseif (!empty($_REQUEST['date_from'])) {
                $date_query['value'] = sanitize_text_field($_REQUEST['date_from']);
                $date_query['compare'] = '>=';
            } else {
                $date_query['value'] = sanitize_text_field($_REQUEST['date_to']);
                $date_query['compare'] = '<=';
            }
            
            $args['meta_query'][] = $date_query;
        }
        
        // Search
        if (!empty($_REQUEST['search'])) {
            $args['s'] = sanitize_text_field($_REQUEST['search']);
        }
        
        $query = new WP_Query($args);
        $html = '';
        $current_user = wp_get_current_user();
        $can_edit = current_user_can('manage_options') || in_array('agent', $current_user->roles) || in_array('staff', $current_user->roles);
        
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $listing_id = get_post_meta($post->ID, 'listing_id', true);
                $agent_id = get_post_meta($post->ID, 'agent_id', true);
                $event_date = get_post_meta($post->ID, 'start_date', true);
                $start_time = get_post_meta($post->ID, 'start_time', true);
                $end_time = get_post_meta($post->ID, 'end_time', true);
                $status = get_post_meta($post->ID, 'event_status', true) ?: 'scheduled';
                $max_visitors = get_post_meta($post->ID, 'max_visitors', true) ?: 0;
                
                // Get listing details
                $listing_title = $listing_id ? get_the_title($listing_id) : 'No Property';
                $listing_address = $listing_id ? get_post_meta($listing_id, 'street_address', true) : '';
                
                // Get agent name
                $agent_name = '';
                if ($agent_id) {
                    $agent_user = get_userdata($agent_id);
                    $agent_name = $agent_user ? $agent_user->display_name : 'Unknown Agent';
                }
                
                // Get RSVP count
                $rsvp_count = 0;
                if (class_exists('HPH_Open_House_Service')) {
                    $oh_service = new HPH_Open_House_Service();
                    $rsvp_count = $oh_service->get_rsvp_count($post->ID);
                }
                
                // Format date and time
                $formatted_date = $event_date ? date('M j, Y', strtotime($event_date)) : '';
                $formatted_time = '';
                if ($start_time && $end_time) {
                    $formatted_time = date('g:i A', strtotime($start_time)) . ' - ' . date('g:i A', strtotime($end_time));
                }
                
                // Status badge
                $status_class = '';
                switch ($status) {
                    case 'scheduled':
                        $status_class = 'hph-badge-blue';
                        break;
                    case 'active':
                        $status_class = 'hph-badge-green';
                        break;
                    case 'completed':
                        $status_class = 'hph-badge-gray';
                        break;
                    case 'cancelled':
                        $status_class = 'hph-badge-red';
                        break;
                    default:
                        $status_class = 'hph-badge-gray';
                }
                
                $html .= '<tr>';
                
                // Checkbox column
                if ($can_edit) {
                    $html .= '<td><input type="checkbox" class="hph-open-house-checkbox hph-form-checkbox" value="' . esc_attr($post->ID) . '"></td>';
                }
                
                // Property column
                $html .= '<td><div class="hph-font-medium">' . esc_html($listing_title) . '</div>';
                if ($listing_address) {
                    $html .= '<div class="hph-text-sm hph-text-gray-500">' . esc_html($listing_address) . '</div>';
                }
                $html .= '</td>';
                
                // Date & Time column
                $html .= '<td><div>' . esc_html($formatted_date) . '</div>';
                if ($formatted_time) {
                    $html .= '<div class="hph-text-sm hph-text-gray-500">' . esc_html($formatted_time) . '</div>';
                }
                $html .= '</td>';
                
                // Agent column
                $html .= '<td>' . esc_html($agent_name) . '</td>';
                
                // RSVPs column
                $rsvp_text = $rsvp_count;
                if ($max_visitors > 0) {
                    $rsvp_text .= ' / ' . $max_visitors;
                }
                $html .= '<td><span class="hph-badge hph-badge-outline">' . esc_html($rsvp_text) . '</span></td>';
                
                // Status column
                $html .= '<td><span class="hph-badge ' . esc_attr($status_class) . '">' . esc_html(ucfirst($status)) . '</span></td>';
                
                // Actions column
                $html .= '<td class="hph-text-center">';
                $html .= '<div class="hph-btn-group">';
                
                if ($can_edit) {
                    $html .= '<button type="button" class="hph-btn hph-btn-sm hph-btn-outline" data-action="view-rsvps" data-open-house-id="' . esc_attr($post->ID) . '" title="View RSVPs">';
                    $html .= '<i class="fas fa-users"></i>';
                    $html .= '</button>';
                    
                    $html .= '<button type="button" class="hph-btn hph-btn-sm hph-btn-outline" data-action="duplicate" data-open-house-id="' . esc_attr($post->ID) . '" title="Duplicate">';
                    $html .= '<i class="fas fa-copy"></i>';
                    $html .= '</button>';
                    
                    $html .= '<button type="button" class="hph-btn hph-btn-sm hph-btn-danger" data-action="delete" data-open-house-id="' . esc_attr($post->ID) . '" title="Delete">';
                    $html .= '<i class="fas fa-trash"></i>';
                    $html .= '</button>';
                } else {
                    $html .= '<button type="button" class="hph-btn hph-btn-sm hph-btn-outline" data-action="view-rsvps" data-open-house-id="' . esc_attr($post->ID) . '" title="View RSVPs">';
                    $html .= '<i class="fas fa-users"></i>';
                    $html .= '</button>';
                }
                
                $html .= '</div>';
                $html .= '</td>';
                
                $html .= '</tr>';
            }
        }
        
        wp_send_json_success(array(
            'html' => $html,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => absint($_REQUEST['page'] ?? 1)
        ));
    }
    
    /**
     * Duplicate Open House
     */
    public function duplicate_open_house() {
        $this->verify_nonce();
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $original_id = absint($_POST['open_house_id'] ?? 0);
        if (!$original_id) {
            wp_send_json_error('Missing open house ID');
            return;
        }
        
        $original = get_post($original_id);
        if (!$original || $original->post_type !== 'open_house') {
            wp_send_json_error('Open house not found');
            return;
        }
        
        // Create duplicate
        $duplicate_data = array(
            'post_type' => 'open_house',
            'post_title' => $original->post_title . ' (Copy)',
            'post_content' => $original->post_content,
            'post_status' => 'publish'
        );
        
        $duplicate_id = wp_insert_post($duplicate_data);
        
        if (is_wp_error($duplicate_id)) {
            wp_send_json_error('Failed to duplicate open house');
            return;
        }
        
        // Copy meta fields
        $meta_fields = get_post_meta($original_id);
        foreach ($meta_fields as $key => $values) {
            foreach ($values as $value) {
                add_post_meta($duplicate_id, $key, maybe_unserialize($value));
            }
        }
        
        wp_send_json_success(array(
            'message' => 'Open house duplicated successfully',
            'duplicate_id' => $duplicate_id
        ));
    }
    
    /**
     * Get RSVP List
     */
    public function get_rsvp_list() {
        $this->verify_nonce();
        
        $open_house_id = absint($_POST['open_house_id'] ?? 0);
        if (!$open_house_id) {
            wp_send_json_error('Missing open house ID');
            return;
        }
        
        // Check permissions
        $agent_id = get_post_meta($open_house_id, 'agent_id', true);
        if ($agent_id != get_current_user_id() && !current_user_can('administrator')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $rsvps = array();
        if (class_exists('HPH_Open_House_Service')) {
            $oh_service = new HPH_Open_House_Service();
            $rsvps = $oh_service->get_rsvps($open_house_id);
        }
        
        wp_send_json_success(array(
            'rsvps' => $rsvps
        ));
    }
    
    /**
     * Export RSVP List
     */
    public function export_rsvp_list() {
        $this->verify_nonce();
        
        $open_house_id = absint($_POST['open_house_id'] ?? 0);
        if (!$open_house_id) {
            wp_send_json_error('Missing open house ID');
            return;
        }
        
        // Check permissions
        $agent_id = get_post_meta($open_house_id, 'agent_id', true);
        if ($agent_id != get_current_user_id() && !current_user_can('administrator')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $rsvps = array();
        if (class_exists('HPH_Open_House_Service')) {
            $oh_service = new HPH_Open_House_Service();
            $rsvps = $oh_service->get_rsvps($open_house_id);
        }
        
        // Create CSV content
        $csv_data = array();
        $csv_data[] = array('Name', 'Email', 'Phone', 'Party Size', 'RSVP Date', 'Status', 'Attended');
        
        foreach ($rsvps as $rsvp) {
            $csv_data[] = array(
                $rsvp['first_name'] . ' ' . $rsvp['last_name'],
                $rsvp['email'],
                $rsvp['phone'],
                $rsvp['party_size'],
                $rsvp['rsvp_date'],
                $rsvp['status'],
                $rsvp['attended'] ? 'Yes' : 'No'
            );
        }
        
        wp_send_json_success(array(
            'csv_data' => $csv_data,
            'filename' => 'open-house-' . $open_house_id . '-rsvps.csv'
        ));
    }
    
    /**
     * Bulk Open House Actions
     */
    public function bulk_open_house_actions() {
        $this->verify_nonce();
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $action = sanitize_text_field($_POST['bulk_action']);
        $open_house_ids = array_map('absint', $_POST['open_house_ids']);
        
        if (empty($open_house_ids)) {
            wp_send_json_error('No open houses selected');
            return;
        }
        
        $updated = 0;
        $errors = 0;
        
        foreach ($open_house_ids as $open_house_id) {
            // Check permissions
            $agent_id = get_post_meta($open_house_id, 'agent_id', true);
            if ($agent_id != get_current_user_id() && !current_user_can('administrator')) {
                $errors++;
                continue;
            }
            
            switch ($action) {
                case 'cancel':
                    update_post_meta($open_house_id, 'event_status', 'cancelled');
                    $updated++;
                    break;
                    
                case 'duplicate':
                    $original = get_post($open_house_id);
                    if ($original) {
                        $duplicate_data = array(
                            'post_type' => 'open_house',
                            'post_title' => $original->post_title . ' (Copy)',
                            'post_content' => $original->post_content,
                            'post_status' => 'publish'
                        );
                        
                        $duplicate_id = wp_insert_post($duplicate_data);
                        
                        if (!is_wp_error($duplicate_id)) {
                            // Copy meta fields
                            $meta_fields = get_post_meta($open_house_id);
                            foreach ($meta_fields as $key => $values) {
                                foreach ($values as $value) {
                                    add_post_meta($duplicate_id, $key, maybe_unserialize($value));
                                }
                            }
                            $updated++;
                        } else {
                            $errors++;
                        }
                    } else {
                        $errors++;
                    }
                    break;
                    
                case 'delete':
                    if (wp_trash_post($open_house_id)) {
                        $updated++;
                    } else {
                        $errors++;
                    }
                    break;
                    
                default:
                    $errors++;
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf('Updated %d open houses. %d errors.', $updated, $errors),
            'updated' => $updated,
            'errors' => $errors
        ));
    }
    
    /**
     * Marketing Materials AJAX Handlers - Phase 3
     */
    
    /**
     * Generate PDF flyer for listing
     */
    public function generate_pdf_flyer() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'hph_marketing_nonce') || 
            !current_user_can('edit_posts')) {
            wp_send_json_error(__('Security check failed', 'happy-place-theme'));
        }
        
        // Get marketing service
        if (class_exists('HappyPlace\\Services\\MarketingService')) {
            $marketing_service = new \HappyPlace\Services\MarketingService();
            $marketing_service->init();
            $marketing_service->generate_pdf_flyer();
        } else {
            wp_send_json_error(__('Marketing service not available', 'happy-place-theme'));
        }
    }
    
    /**
     * Generate social media template
     */
    public function generate_social_template() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'hph_marketing_nonce') || 
            !current_user_can('edit_posts')) {
            wp_send_json_error(__('Security check failed', 'happy-place-theme'));
        }
        
        // Get marketing service
        if (class_exists('HappyPlace\\Services\\MarketingService')) {
            $marketing_service = new \HappyPlace\Services\MarketingService();
            $marketing_service->init();
            $marketing_service->generate_social_template();
        } else {
            wp_send_json_error(__('Marketing service not available', 'happy-place-theme'));
        }
    }
    
    /**
     * Send marketing email
     */
    public function send_marketing_email() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'hph_marketing_nonce') || 
            !current_user_can('edit_posts')) {
            wp_send_json_error(__('Security check failed', 'happy-place-theme'));
        }
        
        // Get marketing service
        if (class_exists('HappyPlace\\Services\\MarketingService')) {
            $marketing_service = new \HappyPlace\Services\MarketingService();
            $marketing_service->init();
            $marketing_service->send_marketing_email();
        } else {
            wp_send_json_error(__('Marketing service not available', 'happy-place-theme'));
        }
    }
    
    /**
     * Get marketing templates
     */
    public function get_marketing_templates() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Permission denied', 'happy-place-theme'));
        }
        
        // Get marketing service
        if (class_exists('HappyPlace\\Services\\MarketingService')) {
            $marketing_service = new \HappyPlace\Services\MarketingService();
            $marketing_service->init();
            $marketing_service->get_marketing_templates();
        } else {
            wp_send_json_error(__('Marketing service not available', 'happy-place-theme'));
        }
    }
    
    /**
     * Get agent listings for marketing
     */
    public function get_agent_listings() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'hph_marketing_nonce') || 
            !current_user_can('edit_posts')) {
            wp_send_json_error(__('Security check failed', 'happy-place-theme'));
        }
        
        $page = intval($_POST['page'] ?? 1);
        $search = sanitize_text_field($_POST['search'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? 'active');
        $per_page = 12;
        
        // Query args
        $args = [
            'post_type' => 'listing',
            'post_status' => ['publish', 'draft'],
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => []
        ];
        
        // Filter by current agent if not admin
        if (!current_user_can('manage_options')) {
            $args['meta_query'][] = [
                'key' => 'agent',
                'value' => get_current_user_id(),
                'compare' => '='
            ];
        }
        
        // Filter by status
        if ($status && $status !== 'all') {
            $args['meta_query'][] = [
                'key' => 'listing_status',
                'value' => $status,
                'compare' => '='
            ];
        }
        
        // Search functionality
        if ($search) {
            $args['s'] = $search;
        }
        
        $query = new WP_Query($args);
        $listings = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $listing_id = get_the_ID();
                
                $listings[] = [
                    'ID' => $listing_id,
                    'post_title' => get_the_title(),
                    'featured_image' => get_the_post_thumbnail_url($listing_id, 'medium'),
                    'price' => get_field('price', $listing_id) ? '$' . number_format(get_field('price', $listing_id)) : '',
                    'address' => get_field('address', $listing_id),
                    'bedrooms' => get_field('bedrooms', $listing_id),
                    'bathrooms' => (get_field('bathrooms_full', $listing_id) ?: 0) + (get_field('bathrooms_half', $listing_id) ?: 0) * 0.5,
                    'square_feet' => get_field('square_feet', $listing_id) ? number_format(get_field('square_feet', $listing_id)) . ' sq ft' : '',
                    'listing_status' => get_field('listing_status', $listing_id) ?: 'active'
                ];
            }
        }
        
        wp_reset_postdata();
        
        $pagination = [
            'current_page' => $page,
            'total_pages' => $query->max_num_pages,
            'total_posts' => $query->found_posts,
            'per_page' => $per_page
        ];
        
        wp_send_json_success([
            'listings' => $listings,
            'pagination' => $pagination
        ]);
    }
    
    /**
     * Get marketing activity log
     */
    public function get_marketing_activity() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Permission denied', 'happy-place-theme'));
        }
        
        global $wpdb;
        $activity_table = $wpdb->prefix . 'hp_marketing_activity';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$activity_table'") !== $activity_table) {
            wp_send_json_success([
                'activity' => [],
                'message' => 'Marketing activity tracking will be available after database migration.'
            ]);
            return;
        }
        
        $user_id = get_current_user_id();
        $limit = intval($_POST['limit'] ?? 10);
        
        // Get recent marketing activity
        $activity = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ma.*, p.post_title as listing_title 
                 FROM $activity_table ma
                 LEFT JOIN {$wpdb->posts} p ON ma.listing_id = p.ID
                 WHERE ma.user_id = %d
                 ORDER BY ma.created_at DESC
                 LIMIT %d",
                $user_id,
                $limit
            )
        );
        
        $formatted_activity = [];
        foreach ($activity as $item) {
            $activity_data = json_decode($item->activity_data, true);
            
            $formatted_activity[] = [
                'id' => $item->id,
                'listing_id' => $item->listing_id,
                'listing_title' => $item->listing_title ?: 'Unknown Listing',
                'activity_type' => $item->activity_type,
                'activity_data' => $activity_data,
                'download_count' => $item->download_count ?: 0,
                'shared_count' => $item->shared_count ?: 0,
                'created_at' => date('M j, Y g:i A', strtotime($item->created_at)),
                'time_ago' => human_time_diff(strtotime($item->created_at), current_time('timestamp')) . ' ago'
            ];
        }
        
        wp_send_json_success([
            'activity' => $formatted_activity
        ]);
    }
    
    // Legacy marketing methods (stubs for backwards compatibility)
    public function generate_marketing() {
        wp_send_json_error(__('This method has been replaced by specific marketing handlers', 'happy-place-theme'));
    }
    
    public function schedule_social_post() {
        wp_send_json_error(__('Social post scheduling coming in future update', 'happy-place-theme'));
    }
    
    // Removed duplicate filter_listings() method - already defined at line 1294
    
    /**
     * Sort listings
     */
    public function sort_listings() {
        $this->verify_nonce();
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $sort_by = sanitize_text_field($_POST['sort_by'] ?? 'date_desc');
        
        $args = [
            'post_type' => 'listing',
            'post_status' => ['publish', 'draft'],
            'posts_per_page' => 20
        ];
        
        switch ($sort_by) {
            case 'price_desc':
                $args['meta_key'] = 'listing_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'price_asc':
                $args['meta_key'] = 'listing_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }
        
        $query = new WP_Query($args);
        $listings = [];
        
        while ($query->have_posts()) {
            $query->the_post();
            $listings[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'price' => get_field('listing_price'),
                'status' => get_field('listing_status'),
                'permalink' => get_permalink()
            ];
        }
        wp_reset_postdata();
        
        wp_send_json_success(['listings' => $listings]);
    }


    private function count_total_leads($user_id) { return 0; }
    private function count_pending_transactions($user_id) { return 0; }
    private function calculate_monthly_revenue($user_id) { return 0; }
    private function calculate_conversion_rate($user_id) { return 0; }
    private function get_recent_leads_activity($user_id) { return array(); }
    private function get_comparable_properties($listing_id) { return array(); }
    private function calculate_estimated_value($data) { return 0; }
    private function get_market_trends($area) { return array(); }
    private function get_user_listings_for_export($user_id) { return array(); }
    private function get_user_leads_for_export($user_id) { return array(); }
    private function get_user_transactions_for_export($user_id) { return array(); }
    private function generate_csv($data) { return ''; }
    private function generate_json($data) { return json_encode($data); }
}

// Initialize the Dashboard Ajax handler
new HPH_Dashboard_Ajax();
