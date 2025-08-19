<?php
/**
 * Agent AJAX Handlers
 * 
 * Handles all AJAX operations for agent management
 */

namespace HappyPlace\API\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

class Agent_Ajax {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->init_ajax_handlers();
    }
    
    /**
     * Initialize AJAX handlers
     */
    private function init_ajax_handlers() {
        // Agent CRUD operations
        add_action('wp_ajax_hph_save_agent', [$this, 'save_agent']);
        add_action('wp_ajax_hph_delete_agent', [$this, 'delete_agent']);
        add_action('wp_ajax_hph_duplicate_agent', [$this, 'duplicate_agent']);
        add_action('wp_ajax_hph_bulk_agent_action', [$this, 'bulk_agent_action']);
        
        // Agent data retrieval
        add_action('wp_ajax_hph_get_agent_data', [$this, 'get_agent_data']);
        add_action('wp_ajax_hph_search_agents', [$this, 'search_agents']);
        
        // Agent status updates
        add_action('wp_ajax_hph_update_agent_status', [$this, 'update_agent_status']);
        
        // WordPress user integration
        add_action('wp_ajax_hph_create_wp_user_for_agent', [$this, 'create_wp_user_for_agent']);
        add_action('wp_ajax_hph_link_agent_to_user', [$this, 'link_agent_to_user']);
    }
    
    /**
     * Save agent (create or update)
     */
    public function save_agent() {
        // Security checks
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        if (!current_user_can('manage_options') && !current_user_can('edit_agents')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        try {
            $action = sanitize_text_field($_POST['action_type'] ?? 'add');
            $agent_id = intval($_POST['agent_id'] ?? 0);
            
            // Validate required fields
            $required_fields = ['first_name', 'last_name', 'email'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    wp_send_json_error(['message' => "Field '{$field}' is required"]);
                }
            }
            
            // Sanitize and prepare data
            $agent_data = $this->sanitize_agent_data($_POST);
            
            if ($action === 'edit' && $agent_id) {
                $result = $this->update_agent($agent_id, $agent_data);
            } else {
                $result = $this->create_agent($agent_data);
            }
            
            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                    'agent_id' => $result['agent_id'],
                    'redirect_url' => remove_query_arg(['dashboard_action', 'dashboard_id'], wp_get_referer())
                ]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }
            
        } catch (Exception $e) {
            error_log('Agent save error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'An error occurred while saving the agent']);
        }
    }
    
    /**
     * Create new agent
     */
    private function create_agent($agent_data) {
        // Check for duplicate email
        $existing_agent = get_posts([
            'post_type' => 'agent',
            'meta_query' => [
                [
                    'key' => 'email',
                    'value' => $agent_data['email'],
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1
        ]);
        
        if (!empty($existing_agent)) {
            return ['success' => false, 'message' => 'An agent with this email already exists'];
        }
        
        // Create agent post
        $post_data = [
            'post_title' => $agent_data['display_name'] ?: $agent_data['first_name'] . ' ' . $agent_data['last_name'],
            'post_type' => 'agent',
            'post_status' => 'publish',
            'post_content' => $agent_data['bio'] ?: '',
            'meta_input' => []
        ];
        
        $agent_id = wp_insert_post($post_data);
        
        if (is_wp_error($agent_id)) {
            return ['success' => false, 'message' => 'Failed to create agent post'];
        }
        
        // Save agent meta data
        $this->save_agent_meta($agent_id, $agent_data);
        
        // Create WordPress user if requested
        if (!empty($agent_data['create_wp_user'])) {
            $this->create_wordpress_user($agent_id, $agent_data);
        } elseif (!empty($agent_data['wordpress_user_id'])) {
            update_field('wordpress_user_id', $agent_data['wordpress_user_id'], $agent_id);
        }
        
        return [
            'success' => true,
            'message' => 'Agent created successfully',
            'agent_id' => $agent_id
        ];
    }
    
    /**
     * Update existing agent
     */
    private function update_agent($agent_id, $agent_data) {
        $agent_post = get_post($agent_id);
        
        if (!$agent_post || $agent_post->post_type !== 'agent') {
            return ['success' => false, 'message' => 'Agent not found'];
        }
        
        // Check for duplicate email (excluding current agent)
        $existing_agent = get_posts([
            'post_type' => 'agent',
            'exclude' => [$agent_id],
            'meta_query' => [
                [
                    'key' => 'email',
                    'value' => $agent_data['email'],
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1
        ]);
        
        if (!empty($existing_agent)) {
            return ['success' => false, 'message' => 'Another agent with this email already exists'];
        }
        
        // Update agent post
        $post_data = [
            'ID' => $agent_id,
            'post_title' => $agent_data['display_name'] ?: $agent_data['first_name'] . ' ' . $agent_data['last_name'],
            'post_content' => $agent_data['bio'] ?: ''
        ];
        
        $result = wp_update_post($post_data);
        
        if (is_wp_error($result)) {
            return ['success' => false, 'message' => 'Failed to update agent post'];
        }
        
        // Save agent meta data
        $this->save_agent_meta($agent_id, $agent_data);
        
        // Update WordPress user link
        if (!empty($agent_data['wordpress_user_id'])) {
            update_field('wordpress_user_id', $agent_data['wordpress_user_id'], $agent_id);
        }
        
        return [
            'success' => true,
            'message' => 'Agent updated successfully',
            'agent_id' => $agent_id
        ];
    }
    
    /**
     * Save agent meta data
     */
    private function save_agent_meta($agent_id, $agent_data) {
        $meta_fields = [
            'first_name', 'last_name', 'display_name', 'title', 'short_bio',
            'email', 'phone', 'mobile_phone', 'office_phone', 'website_url',
            'license_number', 'license_state', 'license_expiration', 'date_started',
            'years_experience', 'office_name', 'agent_status',
            'facebook_url', 'instagram_url', 'linkedin_url', 'twitter_url'
        ];
        
        foreach ($meta_fields as $field) {
            if (isset($agent_data[$field])) {
                update_field($field, $agent_data[$field], $agent_id);
            }
        }
        
        // Handle array fields
        if (isset($agent_data['specialties'])) {
            update_field('specialties', $agent_data['specialties'], $agent_id);
        }
        
        if (isset($agent_data['languages'])) {
            update_field('languages', $agent_data['languages'], $agent_id);
        }
        
        // Handle file uploads
        if (!empty($_FILES['profile_photo']['name'])) {
            $photo_id = $this->handle_file_upload('profile_photo');
            if ($photo_id) {
                update_field('profile_photo', $photo_id, $agent_id);
            }
        }
        
        if (!empty($_FILES['cover_photo']['name'])) {
            $cover_id = $this->handle_file_upload('cover_photo');
            if ($cover_id) {
                update_field('cover_photo', $cover_id, $agent_id);
            }
        }
    }
    
    /**
     * Delete agent
     */
    public function delete_agent() {
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        if (!current_user_can('manage_options') && !current_user_can('delete_agents')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $agent_id = intval($_POST['agent_id']);
        
        if (!$agent_id) {
            wp_send_json_error(['message' => 'Invalid agent ID']);
        }
        
        $agent_post = get_post($agent_id);
        
        if (!$agent_post || $agent_post->post_type !== 'agent') {
            wp_send_json_error(['message' => 'Agent not found']);
        }
        
        // Check if agent has active listings
        $active_listings = get_posts([
            'post_type' => 'listing',
            'meta_query' => [
                [
                    'key' => 'listing_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1
        ]);
        
        if (!empty($active_listings)) {
            wp_send_json_error(['message' => 'Cannot delete agent with active listings. Please reassign listings first.']);
        }
        
        // Delete agent
        $result = wp_delete_post($agent_id, true);
        
        if ($result) {
            wp_send_json_success(['message' => 'Agent deleted successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete agent']);
        }
    }
    
    /**
     * Duplicate agent
     */
    public function duplicate_agent() {
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        if (!current_user_can('manage_options') && !current_user_can('edit_agents')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $agent_id = intval($_POST['agent_id']);
        
        if (!$agent_id) {
            wp_send_json_error(['message' => 'Invalid agent ID']);
        }
        
        $original_agent = get_post($agent_id);
        
        if (!$original_agent || $original_agent->post_type !== 'agent') {
            wp_send_json_error(['message' => 'Agent not found']);
        }
        
        // Create duplicate
        $new_post_data = [
            'post_title' => $original_agent->post_title . ' (Copy)',
            'post_type' => 'agent',
            'post_status' => 'draft',
            'post_content' => $original_agent->post_content
        ];
        
        $new_agent_id = wp_insert_post($new_post_data);
        
        if (is_wp_error($new_agent_id)) {
            wp_send_json_error(['message' => 'Failed to duplicate agent']);
        }
        
        // Copy meta fields
        $meta_fields = get_fields($agent_id);
        
        if ($meta_fields) {
            foreach ($meta_fields as $key => $value) {
                // Modify email to avoid duplicates
                if ($key === 'email') {
                    $value = 'copy_' . $value;
                }
                
                // Don't copy WordPress user link
                if ($key === 'wordpress_user_id') {
                    continue;
                }
                
                update_field($key, $value, $new_agent_id);
            }
        }
        
        wp_send_json_success([
            'message' => 'Agent duplicated successfully',
            'new_agent_id' => $new_agent_id
        ]);
    }
    
    /**
     * Bulk actions for agents
     */
    public function bulk_agent_action() {
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        if (!current_user_can('manage_options') && !current_user_can('edit_agents')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $action = sanitize_text_field($_POST['bulk_action']);
        $agent_ids = array_map('intval', $_POST['agent_ids'] ?? []);
        
        if (empty($agent_ids)) {
            wp_send_json_error(['message' => 'No agents selected']);
        }
        
        $results = [];
        
        switch ($action) {
            case 'activate':
                foreach ($agent_ids as $agent_id) {
                    update_field('agent_status', 'active', $agent_id);
                    $results[] = $agent_id;
                }
                wp_send_json_success(['message' => count($results) . ' agents activated']);
                break;
                
            case 'deactivate':
                foreach ($agent_ids as $agent_id) {
                    update_field('agent_status', 'inactive', $agent_id);
                    $results[] = $agent_id;
                }
                wp_send_json_success(['message' => count($results) . ' agents deactivated']);
                break;
                
            case 'delete':
                if (!current_user_can('delete_agents')) {
                    wp_send_json_error(['message' => 'Insufficient permissions to delete agents']);
                }
                
                foreach ($agent_ids as $agent_id) {
                    // Check for active listings
                    $active_listings = get_posts([
                        'post_type' => 'listing',
                        'meta_query' => [
                            [
                                'key' => 'listing_agent',
                                'value' => $agent_id,
                                'compare' => '='
                            ]
                        ],
                        'posts_per_page' => 1
                    ]);
                    
                    if (empty($active_listings)) {
                        wp_delete_post($agent_id, true);
                        $results[] = $agent_id;
                    }
                }
                wp_send_json_success(['message' => count($results) . ' agents deleted']);
                break;
                
            case 'export':
                $export_data = $this->export_agents($agent_ids);
                wp_send_json_success([
                    'message' => 'Export prepared',
                    'export_data' => $export_data
                ]);
                break;
                
            default:
                wp_send_json_error(['message' => 'Invalid bulk action']);
        }
    }
    
    /**
     * Get agent data
     */
    public function get_agent_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $agent_id = intval($_POST['agent_id']);
        
        if (!$agent_id) {
            wp_send_json_error(['message' => 'Invalid agent ID']);
        }
        
        $agent_post = get_post($agent_id);
        
        if (!$agent_post || $agent_post->post_type !== 'agent') {
            wp_send_json_error(['message' => 'Agent not found']);
        }
        
        $agent_data = [
            'id' => $agent_id,
            'title' => $agent_post->post_title,
            'content' => $agent_post->post_content,
            'status' => $agent_post->post_status
        ];
        
        // Get all ACF fields
        $fields = get_fields($agent_id);
        if ($fields) {
            $agent_data = array_merge($agent_data, $fields);
        }
        
        wp_send_json_success($agent_data);
    }
    
    /**
     * Search agents
     */
    public function search_agents() {
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $search_term = sanitize_text_field($_POST['search'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        $specialty = sanitize_text_field($_POST['specialty'] ?? '');
        
        $meta_query = ['relation' => 'AND'];
        
        if ($status) {
            $meta_query[] = [
                'key' => 'agent_status',
                'value' => $status,
                'compare' => '='
            ];
        }
        
        if ($specialty) {
            $meta_query[] = [
                'key' => 'specialties',
                'value' => $specialty,
                'compare' => 'LIKE'
            ];
        }
        
        $query_args = [
            'post_type' => 'agent',
            'posts_per_page' => 20,
            'post_status' => 'publish',
            'meta_query' => $meta_query
        ];
        
        if ($search_term) {
            $query_args['s'] = $search_term;
        }
        
        $agents = get_posts($query_args);
        $results = [];
        
        foreach ($agents as $agent) {
            $results[] = [
                'id' => $agent->ID,
                'title' => $agent->post_title,
                'first_name' => get_field('first_name', $agent->ID),
                'last_name' => get_field('last_name', $agent->ID),
                'email' => get_field('email', $agent->ID),
                'phone' => get_field('phone', $agent->ID),
                'status' => get_field('agent_status', $agent->ID),
                'photo' => get_field('profile_photo', $agent->ID)
            ];
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * Update agent status
     */
    public function update_agent_status() {
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        if (!current_user_can('edit_agents')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $agent_id = intval($_POST['agent_id']);
        $status = sanitize_text_field($_POST['status']);
        
        if (!$agent_id || !in_array($status, ['active', 'inactive', 'pending'])) {
            wp_send_json_error(['message' => 'Invalid parameters']);
        }
        
        update_field('agent_status', $status, $agent_id);
        
        wp_send_json_success(['message' => 'Agent status updated']);
    }
    
    /**
     * Create WordPress user for agent
     */
    public function create_wp_user_for_agent() {
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $agent_id = intval($_POST['agent_id']);
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        
        if (!$agent_id || !$username || !$email || !$password) {
            wp_send_json_error(['message' => 'Missing required fields']);
        }
        
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()]);
        }
        
        // Set user role
        $user = new WP_User($user_id);
        $user->set_role('agent');
        
        // Link to agent
        update_field('wordpress_user_id', $user_id, $agent_id);
        
        wp_send_json_success([
            'message' => 'WordPress user created and linked',
            'user_id' => $user_id
        ]);
    }
    
    /**
     * Sanitize agent data
     */
    private function sanitize_agent_data($data) {
        $sanitized = [];
        
        // Text fields
        $text_fields = [
            'first_name', 'last_name', 'display_name', 'title', 'office_name',
            'phone', 'mobile_phone', 'office_phone', 'license_number', 'license_state'
        ];
        
        foreach ($text_fields as $field) {
            $sanitized[$field] = sanitize_text_field($data[$field] ?? '');
        }
        
        // Email
        $sanitized['email'] = sanitize_email($data['email'] ?? '');
        
        // URLs
        $url_fields = ['website_url', 'facebook_url', 'instagram_url', 'linkedin_url', 'twitter_url'];
        foreach ($url_fields as $field) {
            $sanitized[$field] = esc_url_raw($data[$field] ?? '');
        }
        
        // Textarea fields
        $sanitized['short_bio'] = sanitize_textarea_field($data['short_bio'] ?? '');
        $sanitized['bio'] = wp_kses_post($data['bio'] ?? '');
        
        // Dates
        $sanitized['date_started'] = sanitize_text_field($data['date_started'] ?? '');
        $sanitized['license_expiration'] = sanitize_text_field($data['license_expiration'] ?? '');
        
        // Numbers
        $sanitized['years_experience'] = intval($data['years_experience'] ?? 0);
        $sanitized['wordpress_user_id'] = intval($data['wordpress_user_id'] ?? 0);
        
        // Select fields
        $sanitized['agent_status'] = in_array($data['agent_status'] ?? '', ['active', 'inactive', 'pending']) 
            ? $data['agent_status'] : 'active';
        
        // Arrays
        $sanitized['specialties'] = isset($data['specialties']) && is_array($data['specialties']) 
            ? array_map('sanitize_text_field', $data['specialties']) : [];
        $sanitized['languages'] = isset($data['languages']) && is_array($data['languages']) 
            ? array_map('sanitize_text_field', $data['languages']) : [];
        
        return $sanitized;
    }
    
    /**
     * Handle file uploads
     */
    private function handle_file_upload($field_name) {
        if (empty($_FILES[$field_name]['name'])) {
            return false;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $attachment_id = media_handle_upload($field_name, 0);
        
        if (is_wp_error($attachment_id)) {
            return false;
        }
        
        return $attachment_id;
    }
    
    /**
     * Export agents data
     */
    private function export_agents($agent_ids) {
        $export_data = [];
        
        foreach ($agent_ids as $agent_id) {
            $agent = get_post($agent_id);
            if (!$agent) continue;
            
            $fields = get_fields($agent_id);
            
            $export_data[] = array_merge([
                'id' => $agent_id,
                'title' => $agent->post_title,
                'content' => $agent->post_content,
                'status' => $agent->post_status,
                'date_created' => $agent->post_date
            ], $fields ?: []);
        }
        
        return $export_data;
    }
    
    /**
     * Create WordPress user for agent
     */
    private function create_wordpress_user($agent_id, $agent_data) {
        $username = sanitize_user(strtolower($agent_data['first_name'] . '.' . $agent_data['last_name']));
        $email = $agent_data['email'];
        $password = wp_generate_password(12, true);
        
        // Check if username exists
        if (username_exists($username)) {
            $username .= '_' . $agent_id;
        }
        
        $user_id = wp_create_user($username, $password, $email);
        
        if (!is_wp_error($user_id)) {
            // Set user role
            $user = new WP_User($user_id);
            $user->set_role('agent');
            
            // Update user meta
            update_user_meta($user_id, 'first_name', $agent_data['first_name']);
            update_user_meta($user_id, 'last_name', $agent_data['last_name']);
            update_user_meta($user_id, 'display_name', $agent_data['display_name']);
            
            // Link to agent
            update_field('wordpress_user_id', $user_id, $agent_id);
            
            // TODO: Send welcome email with credentials
            
            return $user_id;
        }
        
        return false;
    }
}

// Initialize the agent AJAX handlers
new Agent_Ajax();