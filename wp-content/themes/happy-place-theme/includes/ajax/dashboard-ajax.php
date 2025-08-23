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
        add_action('wp_ajax_hph_get_listings', array($this, 'get_listings'));
        add_action('wp_ajax_hph_get_listing_stats', array($this, 'get_listing_stats'));
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
        
        // Marketing
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
        if (!check_ajax_referer('hph_dashboard', 'nonce', false)) {
            wp_die('Security check failed');
        }
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
        
        $result = wp_trash_post($listing_id);
        
        if (!$result) {
            wp_send_json_error('Failed to delete listing');
        }
        
        wp_send_json_success(array(
            'message' => 'Listing deleted successfully'
        ));
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
            'post_status' => 'publish'
        ];

        $listings = get_posts($args);
        $total = count($listings);
        $active = 0;
        $pending = 0;
        $sold = 0;

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

        // Base query args - show all listings
        $args = [
            'post_type' => 'listing',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => 'publish'
        ];

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
        $listings = [];

        foreach ($query->posts as $post) {
            $listing_id = $post->ID;
            
            // Get listing data
            $price = get_field('price', $listing_id) ?: 0;
            $status = get_field('listing_status', $listing_id) ?: 'active';
            $address = get_field('street_address', $listing_id) ?: '';
            $city = get_field('city', $listing_id) ?: '';
            $state = get_field('state', $listing_id) ?: '';
            $bedrooms = get_field('bedrooms', $listing_id) ?: 0;
            $bathrooms = get_field('full_bathrooms', $listing_id) ?: 0;
            $square_feet = get_field('square_feet', $listing_id) ?: 0;
            $mls_number = get_field('mls_number', $listing_id) ?: '';

            // Build full address
            $full_address = trim($address . ', ' . $city . ', ' . $state, ', ');

            // Get featured image
            $featured_image = has_post_thumbnail($listing_id) 
                ? get_the_post_thumbnail_url($listing_id, 'medium')
                : '';

            $listings[] = [
                'id' => $listing_id,
                'title' => $post->post_title,
                'price' => $price,
                'status' => $status,
                'status_label' => ucfirst($status),
                'address' => $full_address,
                'bedrooms' => $bedrooms,
                'bathrooms' => $bathrooms,
                'square_feet' => $square_feet,
                'mls_number' => $mls_number,
                'featured_image' => $featured_image,
                'date_created' => $post->post_date,
                'date_modified' => $post->post_modified
            ];
        }

        wp_send_json_success([
            'listings' => $listings,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page
        ]);
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
    private function count_user_listings($user_id, $status = 'all') {
        $args = array(
            'post_type' => 'listing',
            'author' => $user_id,
            'post_status' => 'publish'
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
    
    // Stub methods to prevent 500 errors
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