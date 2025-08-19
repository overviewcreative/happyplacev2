<?php
/**
 * Dashboard AJAX Handler
 * Handles all AJAX requests for the dashboard system
 *
 * @package HappyPlace
 */

namespace HappyPlace\API\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

class Dashboard_Ajax {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Alias for backwards compatibility
    public static function instance() {
        return self::get_instance();
    }

    private function __construct() {
        // Constructor only sets up the instance
    }

    /**
     * Initialize the AJAX handler
     */
    public function init() {
        $this->init_hooks();
        hp_log('Dashboard AJAX initialized', 'info', 'DASHBOARD_AJAX');
    }

    private function init_hooks() {
        // Dashboard data endpoints
        add_action('wp_ajax_hpt_dashboard_data', [$this, 'handle_dashboard_data']);
        
        // Dashboard action endpoints
        add_action('wp_ajax_hpt_dashboard_action', [$this, 'handle_dashboard_action']);
        
        // Specific dashboard actions
        add_action('wp_ajax_hpt_save_listing', [$this, 'handle_save_listing']);
        add_action('wp_ajax_hp_dashboard_save_listing', [$this, 'handle_save_listing_corrected']);
        add_action('wp_ajax_hpt_delete_listing', [$this, 'handle_delete_listing']);
        add_action('wp_ajax_hpt_toggle_featured', [$this, 'handle_toggle_featured']);
        add_action('wp_ajax_hpt_generate_marketing', [$this, 'handle_generate_marketing']);
        add_action('wp_ajax_hpt_save_event', [$this, 'handle_save_event']);
        add_action('wp_ajax_hpt_delete_event', [$this, 'handle_delete_event']);
        add_action('wp_ajax_hpt_save_lead', [$this, 'handle_save_lead']);
        add_action('wp_ajax_hpt_delete_lead', [$this, 'handle_delete_lead']);
        
        // Utility endpoints
        add_action('wp_ajax_hpt_upload_image', [$this, 'handle_upload_image']);
        add_action('wp_ajax_hpt_search_properties', [$this, 'handle_search_properties']);
        add_action('wp_ajax_hpt_bulk_listing_action', [$this, 'handle_bulk_listing_action']);
        add_action('wp_ajax_hpt_listing_action', [$this, 'handle_listing_action']);
    }

    public function handle_dashboard_data() {
        $this->verify_request();
        
        $section = sanitize_text_field($_POST['section'] ?? '');
        $action_type = sanitize_text_field($_POST['action_type'] ?? '');
        
        if (!$section || !$action_type) {
            wp_send_json_error(['message' => 'Missing required parameters']);
        }

        $dashboard_manager = \HappyPlace\Dashboard\Dashboard_Manager::instance();
        $section_instance = $dashboard_manager->get_section($section);
        
        if (!$section_instance) {
            wp_send_json_error(['message' => 'Invalid section']);
        }

        $handler_method = 'handle_ajax_' . $action_type;
        
        if (!method_exists($section_instance, $handler_method)) {
            wp_send_json_error(['message' => 'Invalid action type']);
        }

        try {
            $result = $section_instance->$handler_method($_POST);
            wp_send_json($result);
        } catch (\Exception $e) {
            error_log('Dashboard AJAX Error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'An error occurred processing your request']);
        }
    }

    public function handle_dashboard_action() {
        $this->verify_request();
        
        $dashboard_action = sanitize_text_field($_POST['dashboard_action'] ?? '');
        
        if (!$dashboard_action) {
            wp_send_json_error(['message' => 'Missing dashboard action']);
        }

        $dashboard_manager = \HappyPlace\Dashboard\Dashboard_Manager::instance();
        
        try {
            switch ($dashboard_action) {
                case 'save_listing':
                    $result = $this->process_save_listing($_POST);
                    break;
                    
                case 'delete_listing':
                    $result = $this->process_delete_listing($_POST);
                    break;
                    
                case 'toggle_featured':
                    $result = $this->process_toggle_featured($_POST);
                    break;
                    
                case 'generate_marketing':
                    $result = $this->process_generate_marketing($_POST);
                    break;
                    
                case 'save_event':
                    $result = $this->process_save_event($_POST);
                    break;
                    
                case 'delete_event':
                    $result = $this->process_delete_event($_POST);
                    break;
                    
                case 'save_lead':
                    $result = $this->process_save_lead($_POST);
                    break;
                    
                case 'delete_lead':
                    $result = $this->process_delete_lead($_POST);
                    break;
                    
                case 'update_profile':
                    $result = $this->process_update_profile($_POST);
                    break;
                    
                case 'get_listing_data':
                    $result = $this->process_get_listing_data($_POST);
                    break;
                    
                case 'generate_pdf':
                    $result = $this->process_generate_pdf($_POST);
                    break;
                    
                default:
                    wp_send_json_error(['message' => 'Unknown dashboard action']);
            }
            
            wp_send_json($result);
            
        } catch (\Exception $e) {
            error_log('Dashboard Action Error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'An error occurred processing your request']);
        }
    }

    private function process_save_listing($data) {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();

        if (!$agent_id) {
            return ['success' => false, 'message' => 'Agent not found'];
        }

        // Validate listing ownership for edits
        if ($listing_id > 0) {
            if (!$this->verify_listing_ownership($listing_id, $agent_id)) {
                return ['success' => false, 'message' => 'You do not have permission to edit this listing'];
            }
        }

        // Validate required fields
        $required_fields = ['title', 'price', 'status', 'address', 'city', 'state', 'zip_code'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Missing required field: {$field}"];
            }
        }

        // Prepare post data
        $post_data = [
            'post_title' => sanitize_text_field($data['title']),
            'post_content' => wp_kses_post($data['description'] ?? ''),
            'post_status' => 'publish',
            'post_type' => 'listing'
        ];

        if ($listing_id > 0) {
            $post_data['ID'] = $listing_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
        }

        if (is_wp_error($result)) {
            return ['success' => false, 'message' => $result->get_error_message()];
        }

        $final_listing_id = $listing_id > 0 ? $listing_id : $result;

        // Save ACF fields
        $acf_fields = [
            'price' => (float) ($data['price'] ?? 0),
            'listing_status' => sanitize_text_field($data['status'] ?? 'active'),
            'bedrooms' => (int) ($data['bedrooms'] ?? 0),
            'bathrooms' => (float) ($data['bathrooms'] ?? 0),
            'square_feet' => (int) ($data['square_feet'] ?? 0),
            'street_address' => sanitize_text_field($data['address'] ?? ''),
            'city' => sanitize_text_field($data['city'] ?? ''),
            'state' => sanitize_text_field($data['state'] ?? ''),
            'zip_code' => sanitize_text_field($data['zip_code'] ?? ''),
            'featured_listing' => !empty($data['featured']),
            'listing_agent' => [$agent_id]
        ];

        foreach ($acf_fields as $key => $value) {
            update_field($key, $value, $final_listing_id);
        }

        return [
            'success' => true,
            'data' => [
                'message' => $listing_id ? 'Listing updated successfully' : 'Listing created successfully',
                'listing_id' => $final_listing_id,
                'redirect' => home_url("/agent-dashboard/listings/")
            ]
        ];
    }

    private function process_delete_listing($data) {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();

        if (!$listing_id || !$agent_id) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        if (!$this->verify_listing_ownership($listing_id, $agent_id)) {
            return ['success' => false, 'message' => 'You do not have permission to delete this listing'];
        }

        $result = wp_delete_post($listing_id, true);
        
        if ($result) {
            return ['success' => true, 'data' => ['message' => 'Listing deleted successfully']];
        } else {
            return ['success' => false, 'message' => 'Failed to delete listing'];
        }
    }

    private function process_toggle_featured($data) {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();

        if (!$listing_id || !$agent_id) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        if (!$this->verify_listing_ownership($listing_id, $agent_id)) {
            return ['success' => false, 'message' => 'You do not have permission to modify this listing'];
        }

        $current_featured = get_field('featured_listing', $listing_id);
        $new_featured = !$current_featured;
        
        update_field('featured_listing', $new_featured, $listing_id);

        return [
            'success' => true,
            'data' => [
                'message' => $new_featured ? 'Listing featured' : 'Listing unfeatured',
                'featured' => $new_featured
            ]
        ];
    }

    private function process_generate_marketing($data) {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        $format = sanitize_text_field($data['format'] ?? '');
        $agent_id = $this->get_current_agent_id();
        
        if (!$listing_id || !$format || !$agent_id) {
            return ['success' => false, 'message' => 'Missing required parameters'];
        }

        if (!$this->verify_listing_ownership($listing_id, $agent_id)) {
            return ['success' => false, 'message' => 'You do not have permission to create marketing for this listing'];
        }

        // Get listing data
        $listing = get_post($listing_id);
        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found'];
        }

        // Generate marketing materials
        $marketing_generator = new \HappyPlace\Marketing\Marketing_Generator();
        $result = $marketing_generator->generate_materials($listing_id, $format);

        if ($result['success']) {
            // Log marketing activity
            $this->log_marketing_activity($agent_id, $listing_id, $format);
            
            return [
                'success' => true,
                'data' => [
                    'message' => 'Marketing materials generated successfully',
                    'download_url' => $result['download_url'],
                    'preview_url' => $result['preview_url'] ?? ''
                ]
            ];
        } else {
            return ['success' => false, 'message' => $result['message']];
        }
    }

    private function process_save_event($data) {
        $event_id = (int) ($data['event_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();
        
        if (!$agent_id) {
            return ['success' => false, 'message' => 'Agent not found'];
        }

        // Validate required fields
        $required_fields = ['title', 'type', 'date', 'start_time'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Missing required field: {$field}"];
            }
        }

        // Sanitize event data
        $event_data = [
            'title' => sanitize_text_field($data['title']),
            'type' => sanitize_text_field($data['type']),
            'date' => sanitize_text_field($data['date']),
            'start_time' => sanitize_text_field($data['start_time']),
            'end_time' => sanitize_text_field($data['end_time'] ?? ''),
            'listing_id' => (int) ($data['listing_id'] ?? 0),
            'client' => sanitize_text_field($data['client'] ?? ''),
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
            'agent_id' => $agent_id
        ];

        // In real implementation, save to events table
        // For now, mock the response
        $final_event_id = $event_id ?: wp_generate_uuid4();

        return [
            'success' => true,
            'data' => [
                'message' => $event_id ? 'Event updated successfully' : 'Event created successfully',
                'event_id' => $final_event_id
            ]
        ];
    }

    private function process_delete_event($data) {
        $event_id = (int) ($data['event_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();
        
        if (!$event_id || !$agent_id) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        // In real implementation, verify ownership and delete from events table
        
        return [
            'success' => true,
            'data' => ['message' => 'Event deleted successfully']
        ];
    }

    private function process_save_lead($data) {
        $lead_id = (int) ($data['lead_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();
        
        if (!$agent_id) {
            return ['success' => false, 'message' => 'Agent not found'];
        }

        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'email'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Missing required field: {$field}"];
            }
        }

        // Sanitize lead data
        $lead_data = [
            'first_name' => sanitize_text_field($data['first_name']),
            'last_name' => sanitize_text_field($data['last_name']),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'address' => sanitize_text_field($data['address'] ?? ''),
            'source' => sanitize_text_field($data['source'] ?? ''),
            'status' => sanitize_text_field($data['status'] ?? 'new'),
            'budget_min' => (int) ($data['budget_min'] ?? 0),
            'budget_max' => (int) ($data['budget_max'] ?? 0),
            'bedrooms' => (int) ($data['bedrooms'] ?? 0),
            'bathrooms' => (float) ($data['bathrooms'] ?? 0),
            'preferred_areas' => sanitize_text_field($data['preferred_areas'] ?? ''),
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
            'agent_id' => $agent_id,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];

        // In real implementation, save to leads table
        $final_lead_id = $lead_id ?: wp_generate_uuid4();

        return [
            'success' => true,
            'data' => [
                'message' => $lead_id ? 'Lead updated successfully' : 'Lead created successfully',
                'lead_id' => $final_lead_id
            ]
        ];
    }

    private function process_delete_lead($data) {
        $lead_id = (int) ($data['lead_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();
        
        if (!$lead_id || !$agent_id) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        // In real implementation, verify ownership and delete from leads table
        
        return [
            'success' => true,
            'data' => ['message' => 'Lead deleted successfully']
        ];
    }

    private function process_update_profile($data) {
        $agent_id = $this->get_current_agent_id();
        
        if (!$agent_id) {
            return ['success' => false, 'message' => 'Agent not found'];
        }

        // Update agent post
        $agent_data = [
            'ID' => $agent_id,
            'post_content' => wp_kses_post($data['bio'] ?? '')
        ];

        wp_update_post($agent_data);

        // Update ACF fields
        $acf_fields = [
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'mobile_phone' => sanitize_text_field($data['mobile_phone'] ?? ''),
            'email' => sanitize_email($data['email'] ?? ''),
            'website' => esc_url_raw($data['website'] ?? ''),
            'facebook_url' => esc_url_raw($data['facebook_url'] ?? ''),
            'instagram_url' => esc_url_raw($data['instagram_url'] ?? ''),
            'linkedin_url' => esc_url_raw($data['linkedin_url'] ?? ''),
            'twitter_url' => esc_url_raw($data['twitter_url'] ?? ''),
        ];

        foreach ($acf_fields as $key => $value) {
            if ($value) {
                update_field($key, $value, $agent_id);
            }
        }

        return [
            'success' => true,
            'data' => ['message' => 'Profile updated successfully']
        ];
    }

    public function handle_upload_image() {
        $this->verify_request();
        
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_overrides = ['test_form' => false];
        $moved_file = wp_handle_upload($_FILES['image'], $upload_overrides);

        if ($moved_file && !isset($moved_file['error'])) {
            wp_send_json_success([
                'url' => $moved_file['url'],
                'file' => $moved_file['file']
            ]);
        } else {
            wp_send_json_error(['message' => $moved_file['error'] ?? 'Upload failed']);
        }
    }

    public function handle_search_properties() {
        $this->verify_request();
        
        $search_term = sanitize_text_field($_POST['search'] ?? '');
        $agent_id = $this->get_current_agent_id();
        
        if (!$agent_id) {
            wp_send_json_error(['message' => 'Agent not found']);
        }

        $args = [
            'post_type' => 'listing',
            'posts_per_page' => 10,
            's' => $search_term,
            'meta_query' => [
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ]
            ]
        ];

        $properties = get_posts($args);
        $results = [];

        foreach ($properties as $property) {
            $address = get_field('street_address', $property->ID);
            $city = get_field('city', $property->ID);
            $state = get_field('state', $property->ID);
            $price = get_field('price', $property->ID);
            
            $results[] = [
                'id' => $property->ID,
                'title' => $property->post_title,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'price' => $price,
                'full_address' => trim($address . ', ' . $city . ', ' . $state, ', ')
            ];
        }

        wp_send_json_success(['properties' => $results]);
    }

    private function verify_request() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required']);
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hpt_dashboard')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check dashboard access
        $dashboard_manager = \HappyPlace\Dashboard\Dashboard_Manager::instance();
        if (!$dashboard_manager->user_has_dashboard_access()) {
            wp_send_json_error(['message' => 'Access denied']);
        }
    }

    private function get_current_agent_id() {
        $dashboard_manager = \HappyPlace\Dashboard\Dashboard_Manager::instance();
        return $dashboard_manager->get_current_agent_id();
    }

    private function verify_listing_ownership($listing_id, $agent_id) {
        $listing_agent = get_field('listing_agent', $listing_id);
        
        if (!$listing_agent) {
            return false;
        }

        // Handle both single agent and array of agents
        if (is_array($listing_agent)) {
            return in_array($agent_id, wp_list_pluck($listing_agent, 'ID'));
        } else {
            return $listing_agent->ID === $agent_id;
        }
    }

    private function process_get_listing_data($data) {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        
        if (!$listing_id) {
            return ['success' => false, 'message' => 'Missing listing ID'];
        }
        
        // Get listing post
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            return ['success' => false, 'message' => 'Listing not found'];
        }
        
        // Get all ACF fields for the listing
        $acf_data = get_fields($listing_id);
        
        // Get listing agent data
        $listing_agent = get_field('listing_agent', $listing_id);
        $agent_data = null;
        
        if ($listing_agent) {
            // Handle both single agent and array of agents
            $agent_post = is_array($listing_agent) ? $listing_agent[0] : $listing_agent;
            if ($agent_post) {
                $agent_data = [
                    'id' => $agent_post->ID,
                    'name' => $agent_post->post_title,
                    'phone' => get_field('phone', $agent_post->ID),
                    'email' => get_field('email', $agent_post->ID),
                    'profile_photo' => get_field('profile_photo', $agent_post->ID),
                    'license_number' => get_field('license_number', $agent_post->ID),
                ];
            }
        }
        
        // Get featured image
        $featured_image = null;
        if (has_post_thumbnail($listing_id)) {
            $featured_image = [
                'url' => get_the_post_thumbnail_url($listing_id, 'full'),
                'thumbnail' => get_the_post_thumbnail_url($listing_id, 'thumbnail'),
                'medium' => get_the_post_thumbnail_url($listing_id, 'medium'),
            ];
        }
        
        // Format the address
        $address_parts = array_filter([
            $acf_data['street_address'] ?? '',
            $acf_data['city'] ?? '',
            $acf_data['state'] ?? '',
            $acf_data['zip_code'] ?? ''
        ]);
        
        // Build comprehensive listing data
        $listing_data = [
            'id' => $listing_id,
            'title' => $listing->post_title,
            'description' => $listing->post_content,
            'price' => $acf_data['price'] ?? 0,
            'formatted_price' => $acf_data['price'] ? '$' . number_format($acf_data['price']) : '',
            'listing_status' => $acf_data['listing_status'] ?? 'active',
            'property_type' => $acf_data['property_type'] ?? 'single_family',
            'bedrooms' => $acf_data['bedrooms'] ?? 0,
            'bathrooms' => $acf_data['bathrooms'] ?? 0,
            'square_feet' => $acf_data['square_feet'] ?? 0,
            'lot_size' => $acf_data['lot_size'] ?? 0,
            'year_built' => $acf_data['year_built'] ?? '',
            'garage_spaces' => $acf_data['garage_spaces'] ?? 0,
            'mls_number' => $acf_data['mls_number'] ?? '',
            'featured_listing' => !empty($acf_data['featured_listing']),
            
            // Address fields
            'street_address' => $acf_data['street_address'] ?? '',
            'city' => $acf_data['city'] ?? '',
            'state' => $acf_data['state'] ?? '',
            'zip_code' => $acf_data['zip_code'] ?? '',
            'full_address' => implode(', ', $address_parts),
            
            // Media
            'featured_image' => $featured_image,
            'gallery_images' => $acf_data['gallery_images'] ?? [],
            
            // Agent data
            'agent' => $agent_data,
            
            // All ACF data (for any additional fields)
            'acf' => $acf_data,
            
            // WordPress post data
            'post_date' => $listing->post_date,
            'post_modified' => $listing->post_modified,
            'permalink' => get_permalink($listing_id),
        ];
        
        return [
            'success' => true,
            'data' => $listing_data
        ];
    }
    
    private function process_generate_pdf($data) {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        $template = sanitize_text_field($data['template'] ?? 'default');
        $agent_id = $this->get_current_agent_id();
        
        if (!$listing_id || !$agent_id) {
            return ['success' => false, 'message' => 'Missing required parameters'];
        }
        
        // Verify listing ownership
        if (!$this->verify_listing_ownership($listing_id, $agent_id)) {
            return ['success' => false, 'message' => 'You do not have permission to generate materials for this listing'];
        }
        
        // Get listing data
        $listing_result = $this->process_get_listing_data(['listing_id' => $listing_id]);
        if (!$listing_result['success']) {
            return $listing_result;
        }
        
        $listing_data = $listing_result['data'];
        
        // For now, return a mock success response
        // In a full implementation, this would generate the actual PDF
        return [
            'success' => true,
            'data' => [
                'message' => 'PDF generated successfully',
                'download_url' => '#', // Would be actual PDF URL
                'preview_url' => $listing_data['featured_image']['url'] ?? '',
                'template' => $template,
                'listing_id' => $listing_id
            ]
        ];
    }

    private function log_marketing_activity($agent_id, $listing_id, $format) {
        // In real implementation, log to activity table
        $activity_data = [
            'agent_id' => $agent_id,
            'listing_id' => $listing_id,
            'action' => 'generate_marketing',
            'details' => json_encode(['format' => $format]),
            'created_at' => current_time('mysql')
        ];
        
        // Would insert into custom activity table
        error_log('Marketing activity: ' . json_encode($activity_data));
    }

    /**
     * Handle bulk listing actions
     */
    public function handle_bulk_listing_action() {
        $this->verify_request();
        
        $bulk_action = sanitize_text_field($_POST['bulk_action'] ?? '');
        $listing_ids = array_map('intval', $_POST['listing_ids'] ?? []);
        $agent_id = $this->get_current_agent_id();
        
        if (!$bulk_action || empty($listing_ids) || !$agent_id) {
            wp_send_json_error(['message' => 'Invalid request parameters']);
        }

        // Verify user can perform this action on all selected listings
        foreach ($listing_ids as $listing_id) {
            if (!$this->verify_listing_ownership($listing_id, $agent_id)) {
                wp_send_json_error(['message' => "You don't have permission to modify listing {$listing_id}"]);
            }
        }

        $success_count = 0;
        $error_count = 0;
        $errors = [];

        switch ($bulk_action) {
            case 'activate':
                foreach ($listing_ids as $listing_id) {
                    $result = update_field('listing_status', 'active', $listing_id);
                    if ($result) {
                        $success_count++;
                    } else {
                        $error_count++;
                        $errors[] = "Failed to activate listing {$listing_id}";
                    }
                }
                $action_message = 'activated';
                break;

            case 'deactivate':
                foreach ($listing_ids as $listing_id) {
                    $result = update_field('listing_status', 'withdrawn', $listing_id);
                    if ($result) {
                        $success_count++;
                    } else {
                        $error_count++;
                        $errors[] = "Failed to deactivate listing {$listing_id}";
                    }
                }
                $action_message = 'deactivated';
                break;

            case 'delete':
                foreach ($listing_ids as $listing_id) {
                    $result = wp_delete_post($listing_id, true);
                    if ($result) {
                        $success_count++;
                    } else {
                        $error_count++;
                        $errors[] = "Failed to delete listing {$listing_id}";
                    }
                }
                $action_message = 'deleted';
                break;

            case 'export':
                // Generate CSV export
                $export_data = $this->generate_listings_export($listing_ids);
                wp_send_json_success([
                    'message' => 'Export generated successfully',
                    'export_data' => $export_data,
                    'download_url' => '#' // Would be actual export file URL
                ]);
                return;

            default:
                wp_send_json_error(['message' => 'Invalid bulk action']);
                return;
        }

        // Prepare response message
        if ($success_count > 0 && $error_count === 0) {
            $message = sprintf(
                '%d listing(s) successfully %s',
                $success_count,
                $action_message
            );
            wp_send_json_success(['message' => $message]);
        } else if ($success_count > 0 && $error_count > 0) {
            $message = sprintf(
                '%d listing(s) successfully %s, %d failed. Errors: %s',
                $success_count,
                $action_message,
                $error_count,
                implode(', ', array_slice($errors, 0, 3))
            );
            wp_send_json_success(['message' => $message, 'partial' => true]);
        } else {
            $message = sprintf(
                'Failed to %s listings. Errors: %s',
                str_replace('ed', 'e', $action_message),
                implode(', ', array_slice($errors, 0, 3))
            );
            wp_send_json_error(['message' => $message]);
        }
    }

    /**
     * Generate listings export data
     */
    private function generate_listings_export($listing_ids) {
        $export_data = [];
        $export_data[] = [
            'ID',
            'Title',
            'Status',
            'Price',
            'Address',
            'City',
            'State',
            'Bedrooms',
            'Bathrooms',
            'Square Feet',
            'Created Date'
        ];

        foreach ($listing_ids as $listing_id) {
            $listing = get_post($listing_id);
            if (!$listing) continue;

            $export_data[] = [
                $listing_id,
                $listing->post_title,
                get_field('listing_status', $listing_id) ?: 'active',
                get_field('price', $listing_id) ?: '',
                get_field('street_address', $listing_id) ?: '',
                get_field('city', $listing_id) ?: '',
                get_field('state', $listing_id) ?: '',
                get_field('bedrooms', $listing_id) ?: '',
                get_field('bathrooms', $listing_id) ?: '',
                get_field('square_feet', $listing_id) ?: '',
                $listing->post_date
            ];
        }

        return $export_data;
    }

    /**
     * Handle individual listing actions
     */
    public function handle_listing_action() {
        $this->verify_request();
        
        $listing_action = sanitize_text_field($_POST['listing_action'] ?? '');
        $listing_id = intval($_POST['listing_id'] ?? 0);
        
        if (!$listing_action) {
            wp_send_json_error(['message' => 'Missing listing action']);
        }
        
        if (!$listing_id) {
            wp_send_json_error(['message' => 'Missing listing ID']);
        }
        
        // Verify user can edit this listing
        if (!$this->user_can_edit_listing($listing_id)) {
            wp_send_json_error(['message' => 'You do not have permission to perform this action']);
        }
        
        try {
            switch ($listing_action) {
                case 'delete':
                    $result = $this->process_single_delete($listing_id);
                    break;
                    
                case 'duplicate':
                    $result = $this->process_duplicate($listing_id);
                    break;
                    
                case 'toggle_featured':
                    $result = $this->process_single_featured($listing_id);
                    break;
                    
                case 'feature':
                    $result = $this->process_set_featured($listing_id, true);
                    break;
                    
                case 'unfeature':
                    $result = $this->process_set_featured($listing_id, false);
                    break;
                    
                case 'export':
                    $result = $this->process_single_export($listing_id);
                    break;
                    
                default:
                    wp_send_json_error(['message' => 'Unknown listing action']);
            }
            
            wp_send_json_success($result);
            
        } catch (\Exception $e) {
            error_log('Listing Action Error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'An error occurred processing your request']);
        }
    }
    
    private function process_single_delete($listing_id) {
        $result = wp_delete_post($listing_id, true);
        
        if ($result) {
            return [
                'message' => 'Listing deleted successfully',
                'deleted' => true
            ];
        } else {
            throw new \Exception('Failed to delete listing');
        }
    }
    
    private function process_duplicate($listing_id) {
        $original_post = get_post($listing_id);
        if (!$original_post) {
            throw new \Exception('Original listing not found');
        }
        
        // Create new post
        $new_post_data = [
            'post_title' => $original_post->post_title . ' (Copy)',
            'post_content' => $original_post->post_content,
            'post_status' => 'draft',
            'post_type' => $original_post->post_type,
            'post_author' => get_current_user_id()
        ];
        
        $new_post_id = wp_insert_post($new_post_data);
        
        if (is_wp_error($new_post_id)) {
            throw new \Exception('Failed to create duplicate listing');
        }
        
        // Copy all meta fields
        $meta_keys = get_post_meta($listing_id);
        foreach ($meta_keys as $key => $values) {
            if (substr($key, 0, 1) !== '_') { // Skip WordPress internal meta
                foreach ($values as $value) {
                    add_post_meta($new_post_id, $key, maybe_unserialize($value));
                }
            }
        }
        
        // Copy featured image
        $thumbnail_id = get_post_thumbnail_id($listing_id);
        if ($thumbnail_id) {
            set_post_thumbnail($new_post_id, $thumbnail_id);
        }
        
        return [
            'message' => 'Listing duplicated successfully',
            'new_listing_id' => $new_post_id,
            'edit_url' => home_url("/agent-dashboard/listings/edit/{$new_post_id}/")
        ];
    }
    
    private function process_single_featured($listing_id) {
        $current_featured = get_field('featured_listing', $listing_id);
        $new_featured = $current_featured ? '0' : '1';
        
        $result = update_field('featured_listing', $new_featured, $listing_id);
        
        if ($result) {
            return [
                'message' => $new_featured ? 'Listing featured successfully' : 'Listing unfeatured successfully',
                'featured' => (bool)$new_featured
            ];
        } else {
            throw new \Exception('Failed to update featured status');
        }
    }
    
    private function process_set_featured($listing_id, $featured) {
        $new_featured = $featured ? '1' : '0';
        
        $result = update_field('featured_listing', $new_featured, $listing_id);
        
        if ($result) {
            return [
                'message' => $featured ? 'Listing featured successfully' : 'Listing unfeatured successfully',
                'featured' => $featured
            ];
        } else {
            throw new \Exception('Failed to update featured status');
        }
    }
    
    private function process_single_export($listing_id) {
        $export_data = $this->generate_listings_export([$listing_id]);
        
        // Generate CSV content
        $csv_content = '';
        foreach ($export_data as $row) {
            $csv_content .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }
        
        // Save to temporary file
        $upload_dir = wp_upload_dir();
        $filename = 'listing-' . $listing_id . '-' . date('Y-m-d-H-i-s') . '.csv';
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        if (file_put_contents($file_path, $csv_content)) {
            return [
                'message' => 'Listing exported successfully',
                'download_url' => $upload_dir['url'] . '/' . $filename
            ];
        } else {
            throw new \Exception('Failed to create export file');
        }
    }
    
    private function user_can_edit_listing($listing_id) {
        // Admin can edit any listing
        if (current_user_can('administrator')) {
            return true;
        }
        
        // Check if user is the listing agent
        $current_user_id = get_current_user_id();
        $listing_agent = get_field('listing_agent', $listing_id);
        
        if ($listing_agent == $current_user_id) {
            return true;
        }
        
        // Check if user has agent ID meta that matches
        $agent_id = get_user_meta($current_user_id, 'agent_id', true);
        if ($agent_id && $listing_agent == $agent_id) {
            return true;
        }
        
        return false;
    }

    /**
     * Handle save listing with corrected ACF field mapping
     */
    public function handle_save_listing_corrected() {
        $this->verify_request();
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        $form_type = sanitize_text_field($_POST['form_type'] ?? 'add_listing');
        
        // Check permissions
        if ($listing_id > 0) {
            // Editing existing listing
            if (!$this->user_can_edit_listing($listing_id)) {
                wp_send_json_error(['message' => 'You do not have permission to edit this listing']);
            }
        } else {
            // Creating new listing
            if (!current_user_can('publish_posts')) {
                wp_send_json_error(['message' => 'You do not have permission to create listings']);
            }
        }
        
        try {
            // Sanitize and validate input
            $post_data = $this->sanitize_listing_post_data($_POST);
            $acf_data = $this->sanitize_listing_acf_data($_POST);
            
            // Validate required fields
            $validation_result = $this->validate_listing_data($post_data, $acf_data);
            if ($validation_result !== true) {
                wp_send_json_error(['message' => $validation_result]);
            }
            
            // Create or update the post
            $post_id = $this->save_listing_post($post_data, $listing_id);
            
            if (is_wp_error($post_id)) {
                wp_send_json_error(['message' => 'Failed to save listing: ' . $post_id->get_error_message()]);
            }
            
            // Save ACF fields with proper field mapping
            $this->save_listing_acf_fields($post_id, $acf_data);
            
            // Handle file uploads
            $this->handle_listing_media_uploads($post_id, $_FILES);
            
            // Auto-generate full address and geocode
            $this->process_address_intelligence($post_id, $acf_data);
            
            // Generate title if empty
            if (empty($post_data['post_title'])) {
                $this->auto_generate_listing_title($post_id, $acf_data);
            }
            
            // Clear relevant caches
            wp_cache_delete('listings_query', 'happy_place');
            
            $message = $listing_id > 0 ? 'Listing updated successfully!' : 'Listing created successfully!';
            $redirect_url = add_query_arg([
                'dashboard_section' => 'listings',
                'dashboard_action' => 'edit',
                'dashboard_id' => $post_id,
                'updated' => '1'
            ], home_url('/agent-dashboard/'));
            
            wp_send_json_success([
                'message' => $message,
                'listing_id' => $post_id,
                'redirect' => $redirect_url
            ]);
            
        } catch (Exception $e) {
            hp_log('Listing save error: ' . $e->getMessage(), 'error', 'LISTING_SAVE');
            wp_send_json_error(['message' => 'An error occurred while saving the listing. Please try again.']);
        }
    }
    
    /**
     * Sanitize WordPress post data
     */
    private function sanitize_listing_post_data($post_data) {
        return [
            'post_title' => sanitize_text_field($post_data['post_title'] ?? ''),
            'post_content' => wp_kses_post($post_data['post_content'] ?? ''),
            'post_excerpt' => sanitize_textarea_field($post_data['short_description'] ?? ''),
            'post_status' => sanitize_text_field($post_data['post_status'] ?? 'publish'),
            'post_type' => 'listing'
        ];
    }
    
    /**
     * Sanitize ACF field data with proper field mapping - matches form structure exactly
     */
    private function sanitize_listing_acf_data($form_data) {
        return [
            // Basic Information (group_listing_basic)
            'mls_number' => sanitize_text_field($form_data['mls_number'] ?? ''),
            'short_description' => sanitize_textarea_field($form_data['short_description'] ?? ''),
            'listing_date' => sanitize_text_field($form_data['listing_date'] ?? ''),
            'price' => floatval($form_data['price'] ?? 0),
            'listing_status_taxonomy' => intval($form_data['listing_status_taxonomy'] ?? 0),
            'property_type' => sanitize_text_field($form_data['property_type'] ?? ''),
            'bedrooms' => intval($form_data['bedrooms'] ?? 0),
            'full_bathrooms' => intval($form_data['full_bathrooms'] ?? 0),
            'half_bathrooms' => intval($form_data['half_bathrooms'] ?? 0),
            'square_feet' => intval($form_data['square_feet'] ?? 0),
            'year_built' => intval($form_data['year_built'] ?? 0),
            'lot_size' => intval($form_data['lot_size'] ?? 0),
            'garage_spaces' => intval($form_data['garage_spaces'] ?? 0),
            
            // Address Information (group_listing_address) 
            'street_address' => sanitize_text_field($form_data['street_address'] ?? ''),
            'unit_number' => sanitize_text_field($form_data['unit_number'] ?? ''),
            'city' => sanitize_text_field($form_data['city'] ?? ''),
            'state' => sanitize_text_field($form_data['state'] ?? ''),
            'zip_code' => sanitize_text_field($form_data['zip_code'] ?? ''),
            'county' => sanitize_text_field($form_data['county'] ?? ''),
            'neighborhood' => sanitize_text_field($form_data['neighborhood'] ?? ''),
            'latitude' => floatval($form_data['latitude'] ?? 0),
            'longitude' => floatval($form_data['longitude'] ?? 0),
            
            // Features (group_listing_features) - checkboxes
            'pool' => isset($form_data['pool']) ? 1 : 0,
            'waterfront' => isset($form_data['waterfront']) ? 1 : 0,
            'fireplace' => isset($form_data['fireplace']) ? 1 : 0,
            'garage' => isset($form_data['garage']) ? 1 : 0,
            'basement' => isset($form_data['basement']) ? 1 : 0,
            'furnished' => isset($form_data['furnished']) ? 1 : 0,
            'pet_friendly' => isset($form_data['pet_friendly']) ? 1 : 0,
            
            // Financial (group_listing_financial)
            'hoa_fee' => floatval($form_data['hoa_fee'] ?? 0),
            'property_taxes' => floatval($form_data['property_taxes'] ?? 0),
            
            // Relationships (group_listing_relationships)
            'listing_agent' => intval($form_data['listing_agent'] ?? 0),
            'co_listing_agent' => intval($form_data['co_listing_agent'] ?? 0),
            
            // Media (group_listing_media)
            'virtual_tour_url' => esc_url_raw($form_data['virtual_tour_url'] ?? ''),
            'video_tour_url' => esc_url_raw($form_data['video_tour_url'] ?? ''),
        ];
    }
    
    /**
     * Validate listing data
     */
    private function validate_listing_data($post_data, $acf_data) {
        // Required field validation
        if (empty($acf_data['price']) || $acf_data['price'] <= 0) {
            return 'Price is required and must be greater than 0';
        }
        
        if (empty($acf_data['listing_date'])) {
            return 'Listing date is required';
        }
        
        if (empty($acf_data['listing_status_taxonomy'])) {
            return 'Listing status is required';
        }
        
        if (empty($acf_data['property_type'])) {
            return 'Property type is required';
        }
        
        if (empty($acf_data['listing_agent'])) {
            return 'Listing agent is required';
        }
        
        // Address validation - require at least street address, city and state
        if (empty($acf_data['street_address'])) {
            return 'Street address is required';
        }
        
        if (empty($acf_data['city']) || empty($acf_data['state'])) {
            return 'City and state are required for the property address';
        }
        
        if (empty($acf_data['zip_code'])) {
            return 'ZIP code is required';
        }
        
        return true;
    }
    
    /**
     * Save or update the listing post
     */
    private function save_listing_post($post_data, $listing_id = 0) {
        if ($listing_id > 0) {
            // Update existing post
            $post_data['ID'] = $listing_id;
            return wp_update_post($post_data);
        } else {
            // Create new post
            return wp_insert_post($post_data);
        }
    }
    
    /**
     * Save ACF fields with proper field names
     */
    private function save_listing_acf_fields($post_id, $acf_data) {
        foreach ($acf_data as $field_name => $field_value) {
            update_field($field_name, $field_value, $post_id);
        }
        
        // Auto-calculate price per sqft if not provided
        if (empty($acf_data['price_per_sqft']) && !empty($acf_data['price']) && !empty($acf_data['square_feet'])) {
            $price_per_sqft = $acf_data['price'] / $acf_data['square_feet'];
            update_field('price_per_sqft', round($price_per_sqft, 2), $post_id);
        }
    }
    
    /**
     * Handle media uploads for listing - corrected for form structure
     */
    private function handle_listing_media_uploads($post_id, $files) {
        // Handle existing image removal
        if (!empty($_POST['remove_images'])) {
            $this->handle_image_removal($post_id, $_POST['remove_images']);
        }
        
        // Handle new featured image from existing
        if (!empty($_POST['new_featured_image'])) {
            $featured_id = intval($_POST['new_featured_image']);
            set_post_thumbnail($post_id, $featured_id);
        }
        
        // Handle new image uploads
        if (!empty($files['listing_images']) && is_array($files['listing_images']['name'])) {
            $uploaded_images = $this->handle_multiple_image_uploads($files['listing_images']);
            if (!empty($uploaded_images)) {
                // Get existing gallery images
                $existing_gallery = get_field('gallery_images', $post_id) ?: [];
                
                // Merge with new uploads
                $all_gallery_images = array_merge($existing_gallery, $uploaded_images);
                
                // Update gallery
                update_field('gallery_images', $all_gallery_images, $post_id);
                
                // Set first image as featured if no featured image exists
                if (!has_post_thumbnail($post_id) && !empty($uploaded_images)) {
                    set_post_thumbnail($post_id, $uploaded_images[0]);
                }
            }
        }
    }
    
    /**
     * Handle removal of existing images
     */
    private function handle_image_removal($post_id, $remove_images) {
        if (!is_array($remove_images)) {
            return;
        }
        
        $current_gallery = get_field('gallery_images', $post_id) ?: [];
        $current_featured = get_post_thumbnail_id($post_id);
        
        foreach ($remove_images as $attachment_id) {
            $attachment_id = intval($attachment_id);
            
            // Remove from gallery
            $current_gallery = array_filter($current_gallery, function($id) use ($attachment_id) {
                return intval($id) !== $attachment_id;
            });
            
            // Remove featured image if it's being deleted
            if ($current_featured == $attachment_id) {
                delete_post_thumbnail($post_id);
            }
            
            // Optionally delete the attachment completely
            // wp_delete_attachment($attachment_id, true);
        }
        
        // Update gallery
        update_field('gallery_images', array_values($current_gallery), $post_id);
    }
    
    /**
     * Handle multiple image uploads
     */
    private function handle_multiple_image_uploads($files) {
        $uploaded_ids = [];
        
        if (!is_array($files['name'])) {
            return $uploaded_ids;
        }
        
        // WordPress upload handling
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            $upload_id = $this->handle_single_image_upload($file);
            if ($upload_id) {
                $uploaded_ids[] = $upload_id;
            }
        }
        
        return $uploaded_ids;
    }
    
    /**
     * Process address intelligence and geocoding
     */
    private function process_address_intelligence($post_id, $acf_data) {
        // Generate full address from components
        $full_address = $this->generate_full_address($acf_data);
        if ($full_address) {
            update_field('full_address', $full_address, $post_id);
        }
        
        // Geocode if we have coordinates from the form
        if (!empty($acf_data['latitude']) && !empty($acf_data['longitude'])) {
            update_field('latitude', $acf_data['latitude'], $post_id);
            update_field('longitude', $acf_data['longitude'], $post_id);
        }
    }
    
    /**
     * Generate full address from components - updated for corrected form structure
     */
    private function generate_full_address($acf_data) {
        $address_parts = [];
        
        // Street address (single field in corrected form)
        if (!empty($acf_data['street_address'])) {
            $address_parts[] = $acf_data['street_address'];
        }
        
        if (!empty($acf_data['unit_number'])) {
            $address_parts[] = $acf_data['unit_number'];
        }
        
        if (!empty($acf_data['city'])) {
            $address_parts[] = $acf_data['city'];
        }
        
        if (!empty($acf_data['state'])) {
            $address_parts[] = $acf_data['state'];
        }
        
        if (!empty($acf_data['zip_code'])) {
            $address_parts[] = $acf_data['zip_code'];
        }
        
        return implode(', ', array_filter($address_parts));
    }
    
    /**
     * Auto-generate listing title from address - updated for corrected form structure
     */
    private function auto_generate_listing_title($post_id, $acf_data) {
        $title_parts = [];
        
        // Use street address from corrected form
        if (!empty($acf_data['street_address'])) {
            $title_parts[] = $acf_data['street_address'];
        }
        
        if (!empty($acf_data['unit_number'])) {
            $title_parts[] = $acf_data['unit_number'];
        }
        
        // Add city for more descriptive title
        if (!empty($acf_data['city'])) {
            $title_parts[] = $acf_data['city'];
        }
        
        if (!empty($title_parts)) {
            $title = implode(', ', $title_parts);
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $title
            ]);
        }
    }
    
    /**
     * Handle single image upload
     */
    private function handle_single_image_upload($file) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $upload = wp_handle_upload($file, ['test_form' => false]);
        
        if (!empty($upload['error'])) {
            return false;
        }
        
        // Create attachment
        $attachment = [
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_file_name(basename($upload['file'])),
            'post_content' => '',
            'post_status' => 'inherit'
        ];
        
        $attachment_id = wp_insert_attachment($attachment, $upload['file']);
        
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
        
        return $attachment_id;
    }
    
    /**
     * Handle gallery images upload
     */
    private function handle_gallery_images_upload($files) {
        $gallery_images = [];
        
        if (is_array($files['name'])) {
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $single_file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    
                    $image_id = $this->handle_single_image_upload($single_file);
                    if ($image_id) {
                        $gallery_images[] = [
                            'image' => $image_id,
                            'description' => '',
                            'room_tag' => ''
                        ];
                    }
                }
            }
        }
        
        return $gallery_images;
    }
}