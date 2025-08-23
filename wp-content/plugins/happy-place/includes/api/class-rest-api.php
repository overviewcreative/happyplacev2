<?php
/**
 * REST API Handler
 * 
 * Manages all REST API endpoints for the Happy Place plugin
 * Provides external access to listings, agents, and other data
 *
 * @package HappyPlace\API
 */

namespace HappyPlace\API;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class REST_API {
    
    /**
     * API namespace
     */
    private const NAMESPACE = 'happy-place/v1';
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Available endpoints configuration
     */
    private array $endpoints;
    
    /**
     * Protected endpoints requiring authentication
     */
    private array $protected_endpoints;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_endpoints();
        add_action('rest_api_init', [$this, 'register_routes'], 10);
        
        // Also register on init as a fallback
        add_action('init', function() {
            if (did_action('rest_api_init')) {
                return;
            }
            // Force registration if rest_api_init hasn't fired yet
            global $wp_rest_server;
            if (empty($wp_rest_server)) {
                return;
            }
            $this->register_routes();
        }, 100);
    }
    
    /**
     * Initialize endpoints configuration
     */
    private function init_endpoints() {
        $this->endpoints = [
        'listings' => [
            'methods' => 'GET',
            'callback' => 'get_listings',
            'permission_callback' => '__return_true',
            'args' => [
                'per_page' => [
                    'default' => 10,
                    'sanitize_callback' => 'absint',
                ],
                'page' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'status' => [
                    'default' => 'active',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'min_price' => [
                    'sanitize_callback' => 'absint',
                ],
                'max_price' => [
                    'sanitize_callback' => 'absint',
                ],
                'bedrooms' => [
                    'sanitize_callback' => 'absint',
                ],
                'bathrooms_full' => [
                    'sanitize_callback' => 'absint',
                ],
                'city' => [
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'agent_id' => [
                    'sanitize_callback' => 'absint',
                ],
            ],
        ],
        'listings/(?P<id>\d+)' => [
            'methods' => 'GET',
            'callback' => 'get_listing',
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
            ],
        ],
        'agents' => [
            'methods' => 'GET',
            'callback' => 'get_agents',
            'permission_callback' => '__return_true',
            'args' => [
                'per_page' => [
                    'default' => 10,
                    'sanitize_callback' => 'absint',
                ],
                'page' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'active' => [
                    'default' => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
            ],
        ],
        'agents/(?P<id>\d+)' => [
            'methods' => 'GET',
            'callback' => 'get_agent',
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
            ],
        ],
        'communities' => [
            'methods' => 'GET',
            'callback' => 'get_communities',
            'permission_callback' => '__return_true',
            'args' => [
                'per_page' => [
                    'default' => 20,
                    'sanitize_callback' => 'absint',
                ],
                'page' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'city' => [
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ],
        'open-houses' => [
            'methods' => 'GET',
            'callback' => 'get_open_houses',
            'permission_callback' => '__return_true',
            'args' => [
                'upcoming' => [
                    'default' => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'per_page' => [
                    'default' => 10,
                    'sanitize_callback' => 'absint',
                ],
                'page' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ],
        'search' => [
            'methods' => 'POST',
            'callback' => 'search_properties',
            'permission_callback' => '__return_true',
            'args' => [
                'query' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'filters' => [
                    'type' => 'object',
                ],
            ],
        ],
        'lead' => [
            'methods' => 'POST',
            'callback' => 'submit_lead',
            'permission_callback' => '__return_true',
            'args' => [
                'name' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'email' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_email',
                ],
                'phone' => [
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'message' => [
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'listing_id' => [
                    'sanitize_callback' => 'absint',
                ],
                'agent_id' => [
                    'sanitize_callback' => 'absint',
                ],
            ],
        ],
    ];
    
        $this->protected_endpoints = [
        'listings' => [
            'methods' => 'POST',
            'callback' => 'create_listing',
            'permission_callback' => 'check_agent_permission',
        ],
        'listings/(?P<id>\d+)' => [
            'methods' => 'PUT,PATCH',
            'callback' => 'update_listing',
            'permission_callback' => 'check_listing_permission',
        ],
        'listings/(?P<id>\d+)' => [
            'methods' => 'DELETE',
            'callback' => 'delete_listing',
            'permission_callback' => 'check_listing_permission',
        ],
        'agent/profile' => [
            'methods' => 'GET,PUT',
            'callback' => 'handle_agent_profile',
            'permission_callback' => 'check_agent_permission',
        ],
        'agent/listings' => [
            'methods' => 'GET',
            'callback' => 'get_agent_listings',
            'permission_callback' => 'check_agent_permission',
        ],
        'agent/leads' => [
            'methods' => 'GET',
            'callback' => 'get_agent_leads',
            'permission_callback' => 'check_agent_permission',
        ],
    ];
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Register public endpoints
        foreach ($this->endpoints as $route => $config) {
            register_rest_route(self::NAMESPACE, $route, [
                'methods' => $config['methods'],
                'callback' => [$this, $config['callback']],
                'permission_callback' => $config['permission_callback'],
                'args' => $config['args'] ?? [],
            ]);
        }
        
        // Register protected endpoints
        foreach ($this->protected_endpoints as $route => $config) {
            register_rest_route(self::NAMESPACE, $route, [
                'methods' => $config['methods'],
                'callback' => [$this, $config['callback']],
                'permission_callback' => [$this, $config['permission_callback']],
                'args' => $config['args'] ?? [],
            ]);
        }
        
        hp_log('REST API routes registered', 'info', 'REST_API');
    }
    
    /**
     * Get listings endpoint
     */
    public function get_listings(\WP_REST_Request $request) {
        $params = $request->get_params();
        
        $args = [
            'post_type' => 'listing',
            'posts_per_page' => $params['per_page'],
            'paged' => $params['page'],
            'post_status' => 'publish',
            'meta_query' => [],
        ];
        
        // Add status filter
        if (!empty($params['status'])) {
            $args['meta_query'][] = [
                'key' => 'property_status',
                'value' => $params['status'],
                'compare' => '=',
            ];
        }
        
        // Add price filters
        if (!empty($params['min_price']) || !empty($params['max_price'])) {
            $price_query = [
                'key' => 'price',
                'type' => 'NUMERIC',
            ];
            
            if (!empty($params['min_price']) && !empty($params['max_price'])) {
                $price_query['value'] = [$params['min_price'], $params['max_price']];
                $price_query['compare'] = 'BETWEEN';
            } elseif (!empty($params['min_price'])) {
                $price_query['value'] = $params['min_price'];
                $price_query['compare'] = '>=';
            } else {
                $price_query['value'] = $params['max_price'];
                $price_query['compare'] = '<=';
            }
            
            $args['meta_query'][] = $price_query;
        }
        
        // Add bedroom filter
        if (!empty($params['bedrooms'])) {
            $args['meta_query'][] = [
                'key' => 'bedrooms',
                'value' => $params['bedrooms'],
                'compare' => '>=',
                'type' => 'NUMERIC',
            ];
        }
        
        // Add bathroom filter
        if (!empty($params['bathrooms'])) {
            $args['meta_query'][] = [
                'key' => 'bathrooms_full',
                'value' => $params['bathrooms'],
                'compare' => '>=',
                'type' => 'NUMERIC',
            ];
        }
        
        // Add city filter
        if (!empty($params['city'])) {
            $args['meta_query'][] = [
                'key' => 'city',
                'value' => $params['city'],
                'compare' => 'LIKE',
            ];
        }
        
        // Add agent filter
        if (!empty($params['agent_id'])) {
            $args['meta_query'][] = [
                'key' => 'listing_agent',
                'value' => $params['agent_id'],
                'compare' => '=',
            ];
        }
        
        $query = new \WP_Query($args);
        $listings = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $listings[] = $this->format_listing_data(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $listings,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ], 200);
    }
    
    /**
     * Get single listing endpoint
     */
    public function get_listing(\WP_REST_Request $request) {
        $listing_id = $request->get_param('id');
        $listing = get_post($listing_id);
        
        if (!$listing || $listing->post_type !== 'listing') {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Listing not found',
            ], 404);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $this->format_listing_data($listing_id, true),
        ], 200);
    }
    
    /**
     * Get agents endpoint
     */
    public function get_agents(\WP_REST_Request $request) {
        $params = $request->get_params();
        
        $args = [
            'post_type' => 'agent',
            'posts_per_page' => $params['per_page'],
            'paged' => $params['page'],
            'post_status' => 'publish',
        ];
        
        if ($params['active']) {
            $args['meta_query'] = [
                [
                    'key' => 'agent_status',
                    'value' => 'active',
                    'compare' => '=',
                ],
            ];
        }
        
        $query = new \WP_Query($args);
        $agents = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $agents[] = $this->format_agent_data(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $agents,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ], 200);
    }
    
    /**
     * Get single agent endpoint
     */
    public function get_agent(\WP_REST_Request $request) {
        $agent_id = $request->get_param('id');
        $agent = get_post($agent_id);
        
        if (!$agent || $agent->post_type !== 'agent') {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Agent not found',
            ], 404);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $this->format_agent_data($agent_id, true),
        ], 200);
    }
    
    /**
     * Get communities endpoint
     */
    public function get_communities(\WP_REST_Request $request) {
        $params = $request->get_params();
        
        $args = [
            'post_type' => 'community',
            'posts_per_page' => $params['per_page'],
            'paged' => $params['page'],
            'post_status' => 'publish',
        ];
        
        if (!empty($params['city'])) {
            $args['meta_query'] = [
                [
                    'key' => 'city',
                    'value' => $params['city'],
                    'compare' => 'LIKE',
                ],
            ];
        }
        
        $query = new \WP_Query($args);
        $communities = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $communities[] = $this->format_community_data(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $communities,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ], 200);
    }
    
    /**
     * Get open houses endpoint
     */
    public function get_open_houses(\WP_REST_Request $request) {
        $params = $request->get_params();
        
        $args = [
            'post_type' => 'open_house',
            'posts_per_page' => $params['per_page'],
            'paged' => $params['page'],
            'post_status' => 'publish',
        ];
        
        if ($params['upcoming']) {
            $args['meta_query'] = [
                [
                    'key' => 'event_date',
                    'value' => current_time('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE',
                ],
            ];
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = 'event_date';
            $args['order'] = 'ASC';
        }
        
        $query = new \WP_Query($args);
        $open_houses = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $open_houses[] = $this->format_open_house_data(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $open_houses,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ], 200);
    }
    
    /**
     * Search properties endpoint
     */
    public function search_properties(\WP_REST_Request $request) {
        $query = $request->get_param('query');
        $filters = $request->get_param('filters') ?? [];
        
        $args = [
            'post_type' => 'listing',
            'posts_per_page' => 20,
            'post_status' => 'publish',
            's' => $query,
            'meta_query' => [],
        ];
        
        // Apply filters
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $args['meta_query'][] = [
                        'key' => $key,
                        'value' => $value,
                        'compare' => is_array($value) ? 'IN' : '=',
                    ];
                }
            }
        }
        
        $query = new \WP_Query($args);
        $results = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $results[] = $this->format_listing_data(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $results,
            'total' => $query->found_posts,
        ], 200);
    }
    
    /**
     * Submit lead endpoint
     */
    public function submit_lead(\WP_REST_Request $request) {
        $params = $request->get_params();
        
        // Validate required fields
        if (empty($params['name']) || empty($params['email'])) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Name and email are required',
            ], 400);
        }
        
        // Create lead post
        $lead_data = [
            'post_title' => sprintf('Lead: %s', $params['name']),
            'post_type' => 'lead',
            'post_status' => 'private',
            'meta_input' => [
                'lead_name' => $params['name'],
                'lead_email' => $params['email'],
                'lead_phone' => $params['phone'] ?? '',
                'lead_message' => $params['message'] ?? '',
                'lead_listing_id' => $params['listing_id'] ?? 0,
                'lead_agent_id' => $params['agent_id'] ?? 0,
                'lead_source' => 'api',
                'lead_date' => current_time('mysql'),
            ],
        ];
        
        $lead_id = wp_insert_post($lead_data);
        
        if (is_wp_error($lead_id)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Failed to create lead',
            ], 500);
        }
        
        // Trigger lead notification
        do_action('hp_new_lead_submitted', $lead_id, $params);
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => 'Lead submitted successfully',
            'lead_id' => $lead_id,
        ], 201);
    }
    
    /**
     * Create listing endpoint (protected)
     */
    public function create_listing(\WP_REST_Request $request) {
        $params = $request->get_params();
        $current_user_id = get_current_user_id();
        
        // Get agent ID from user
        $agent_id = $this->get_agent_id_from_user($current_user_id);
        
        if (!$agent_id) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'User is not associated with an agent profile',
            ], 403);
        }
        
        // Create listing
        $listing_data = [
            'post_title' => $params['title'] ?? 'New Listing',
            'post_type' => 'listing',
            'post_status' => 'draft',
            'post_author' => $current_user_id,
        ];
        
        $listing_id = wp_insert_post($listing_data);
        
        if (is_wp_error($listing_id)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Failed to create listing',
            ], 500);
        }
        
        // Update ACF fields
        if (function_exists('update_field')) {
            update_field('listing_agent', $agent_id, $listing_id);
            
            // Update other fields from params
            foreach ($params as $key => $value) {
                if ($key !== 'title') {
                    update_field($key, $value, $listing_id);
                }
            }
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => 'Listing created successfully',
            'listing_id' => $listing_id,
        ], 201);
    }
    
    /**
     * Update listing endpoint (protected)
     */
    public function update_listing(\WP_REST_Request $request) {
        $listing_id = $request->get_param('id');
        $params = $request->get_params();
        
        // Check if listing exists
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Listing not found',
            ], 404);
        }
        
        // Update post data
        if (isset($params['title'])) {
            wp_update_post([
                'ID' => $listing_id,
                'post_title' => $params['title'],
            ]);
        }
        
        // Update ACF fields
        if (function_exists('update_field')) {
            foreach ($params as $key => $value) {
                if (!in_array($key, ['id', 'title'])) {
                    update_field($key, $value, $listing_id);
                }
            }
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => 'Listing updated successfully',
            'listing_id' => $listing_id,
        ], 200);
    }
    
    /**
     * Delete listing endpoint (protected)
     */
    public function delete_listing(\WP_REST_Request $request) {
        $listing_id = $request->get_param('id');
        
        // Check if listing exists
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Listing not found',
            ], 404);
        }
        
        // Move to trash
        $result = wp_trash_post($listing_id);
        
        if (!$result) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Failed to delete listing',
            ], 500);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => 'Listing deleted successfully',
        ], 200);
    }
    
    /**
     * Handle agent profile endpoint (protected)
     */
    public function handle_agent_profile(\WP_REST_Request $request) {
        $current_user_id = get_current_user_id();
        $agent_id = $this->get_agent_id_from_user($current_user_id);
        
        if (!$agent_id) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'User is not associated with an agent profile',
            ], 403);
        }
        
        if ($request->get_method() === 'GET') {
            // Get profile
            return new \WP_REST_Response([
                'success' => true,
                'data' => $this->format_agent_data($agent_id, true),
            ], 200);
        } else {
            // Update profile
            $params = $request->get_params();
            
            if (function_exists('update_field')) {
                foreach ($params as $key => $value) {
                    update_field($key, $value, $agent_id);
                }
            }
            
            return new \WP_REST_Response([
                'success' => true,
                'message' => 'Profile updated successfully',
            ], 200);
        }
    }
    
    /**
     * Get agent listings endpoint (protected)
     */
    public function get_agent_listings(\WP_REST_Request $request) {
        $current_user_id = get_current_user_id();
        $agent_id = $this->get_agent_id_from_user($current_user_id);
        
        if (!$agent_id) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'User is not associated with an agent profile',
            ], 403);
        }
        
        $args = [
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft', 'pending'],
            'meta_query' => [
                [
                    'key' => 'listing_agent',
                    'value' => $agent_id,
                    'compare' => '=',
                ],
            ],
        ];
        
        $query = new \WP_Query($args);
        $listings = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $listings[] = $this->format_listing_data(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $listings,
            'total' => $query->found_posts,
        ], 200);
    }
    
    /**
     * Get agent leads endpoint (protected)
     */
    public function get_agent_leads(\WP_REST_Request $request) {
        $current_user_id = get_current_user_id();
        $agent_id = $this->get_agent_id_from_user($current_user_id);
        
        if (!$agent_id) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'User is not associated with an agent profile',
            ], 403);
        }
        
        $args = [
            'post_type' => 'lead',
            'posts_per_page' => -1,
            'post_status' => 'private',
            'meta_query' => [
                [
                    'key' => 'lead_agent_id',
                    'value' => $agent_id,
                    'compare' => '=',
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        $query = new \WP_Query($args);
        $leads = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $lead_id = get_the_ID();
                
                $leads[] = [
                    'id' => $lead_id,
                    'name' => get_post_meta($lead_id, 'lead_name', true),
                    'email' => get_post_meta($lead_id, 'lead_email', true),
                    'phone' => get_post_meta($lead_id, 'lead_phone', true),
                    'message' => get_post_meta($lead_id, 'lead_message', true),
                    'listing_id' => get_post_meta($lead_id, 'lead_listing_id', true),
                    'date' => get_post_meta($lead_id, 'lead_date', true),
                    'source' => get_post_meta($lead_id, 'lead_source', true),
                ];
            }
            wp_reset_postdata();
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $leads,
            'total' => $query->found_posts,
        ], 200);
    }
    
    /**
     * Check agent permission
     */
    public function check_agent_permission(\WP_REST_Request $request) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $user = wp_get_current_user();
        return in_array('agent', $user->roles) || in_array('administrator', $user->roles);
    }
    
    /**
     * Check listing permission
     */
    public function check_listing_permission(\WP_REST_Request $request) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $listing_id = $request->get_param('id');
        $listing = get_post($listing_id);
        
        if (!$listing) {
            return false;
        }
        
        $current_user_id = get_current_user_id();
        $agent_id = $this->get_agent_id_from_user($current_user_id);
        
        // Check if user owns the listing
        if (function_exists('get_field')) {
            $listing_agent = get_field('listing_agent', $listing_id);
            if ($listing_agent && $listing_agent == $agent_id) {
                return true;
            }
        }
        
        // Check if user is admin
        $user = wp_get_current_user();
        return in_array('administrator', $user->roles);
    }
    
    /**
     * Format listing data for API response
     */
    private function format_listing_data($listing_id, $full = false) {
        $listing = get_post($listing_id);
        
        $data = [
            'id' => $listing_id,
            'title' => get_the_title($listing_id),
            'slug' => $listing->post_name,
            'url' => get_permalink($listing_id),
            'status' => $listing->post_status,
            'date' => $listing->post_date,
            'modified' => $listing->post_modified,
        ];
        
        // Add ACF fields
        if (function_exists('get_field')) {
            $data['price'] = get_field('listing_price', $listing_id);
            $data['property_status'] = get_field('property_status', $listing_id);
            $data['mls_number'] = get_field('mls_number', $listing_id);
            $data['bedrooms'] = get_field('bedrooms', $listing_id);
            $data['bathrooms_full'] = get_field('bathrooms_full', $listing_id);
            $data['bathrooms_half'] = get_field('bathrooms_half', $listing_id);
            $data['square_feet'] = get_field('square_feet', $listing_id);
            $data['lot_size'] = get_field('lot_size', $listing_id);
            $data['year_built'] = get_field('year_built', $listing_id);
            
            // Location
            $data['address'] = [
                'street' => get_field('street_address', $listing_id),
                'city' => get_field('city', $listing_id),
                'state' => get_field('state', $listing_id),
                'zip' => get_field('zip_code', $listing_id),
                'county' => get_field('county', $listing_id),
            ];
            
            $data['coordinates'] = [
                'lat' => get_field('latitude', $listing_id),
                'lng' => get_field('longitude', $listing_id),
            ];
            
            // Media
            $primary_photo = get_field('primary_photo', $listing_id);
            if ($primary_photo) {
                $data['primary_photo'] = [
                    'id' => $primary_photo['ID'],
                    'url' => $primary_photo['url'],
                    'thumb' => $primary_photo['sizes']['thumbnail'] ?? '',
                    'medium' => $primary_photo['sizes']['medium'] ?? '',
                    'large' => $primary_photo['sizes']['large'] ?? '',
                ];
            }
            
            if ($full) {
                // Add full details for single listing view
                $data['description'] = get_field('description', $listing_id);
                $data['property_features'] = get_field('property_features', $listing_id);
                $data['interior_features'] = get_field('interior_features', $listing_id);
                $data['exterior_features'] = get_field('exterior_features', $listing_id);
                $data['garage_spaces'] = get_field('garage_spaces', $listing_id);
                $data['has_pool'] = get_field('has_pool', $listing_id);
                $data['has_spa'] = get_field('has_spa', $listing_id);
                $data['hoa_fees'] = get_field('hoa_fees', $listing_id);
                $data['property_taxes'] = get_field('property_taxes', $listing_id);
                
                // Gallery
                $gallery = get_field('photo_gallery', $listing_id);
                if ($gallery) {
                    $data['gallery'] = array_map(function($image_id) {
                        return [
                            'id' => $image_id,
                            'url' => wp_get_attachment_url($image_id),
                            'thumb' => wp_get_attachment_image_url($image_id, 'thumbnail'),
                            'medium' => wp_get_attachment_image_url($image_id, 'medium'),
                            'large' => wp_get_attachment_image_url($image_id, 'large'),
                        ];
                    }, $gallery);
                }
                
                // Virtual tour
                $data['video_tour_url'] = get_field('video_tour_url', $listing_id);
                $data['virtual_tour_embed'] = get_field('virtual_tour_embed', $listing_id);
                
                // Agent info
                $agent_id = get_field('listing_agent', $listing_id);
                if ($agent_id) {
                    $data['agent'] = $this->format_agent_data($agent_id, false);
                }
                
                // Community
                $community_id = get_field('community', $listing_id);
                if ($community_id) {
                    $data['community'] = [
                        'id' => $community_id,
                        'name' => get_the_title($community_id),
                        'url' => get_permalink($community_id),
                    ];
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Format agent data for API response
     */
    private function format_agent_data($agent_id, $full = false) {
        $agent = get_post($agent_id);
        
        $data = [
            'id' => $agent_id,
            'name' => get_the_title($agent_id),
            'slug' => $agent->post_name,
            'url' => get_permalink($agent_id),
        ];
        
        // Add ACF fields
        if (function_exists('get_field')) {
            $data['first_name'] = get_field('first_name', $agent_id);
            $data['last_name'] = get_field('last_name', $agent_id);
            $data['title'] = get_field('title', $agent_id);
            $data['phone'] = get_field('phone', $agent_id);
            $data['email'] = get_field('email', $agent_id);
            
            // Profile photo
            $profile_photo = get_field('profile_photo', $agent_id);
            if ($profile_photo) {
                $data['profile_photo'] = [
                    'url' => wp_get_attachment_url($profile_photo),
                    'thumb' => wp_get_attachment_image_url($profile_photo, 'thumbnail'),
                    'medium' => wp_get_attachment_image_url($profile_photo, 'medium'),
                ];
            }
            
            if ($full) {
                // Add full details for single agent view
                $data['bio'] = get_field('bio', $agent_id);
                $data['license_number'] = get_field('license_number', $agent_id);
                $data['years_experience'] = get_field('years_experience', $agent_id);
                $data['specialties'] = get_field('specialties', $agent_id);
                $data['languages'] = get_field('languages', $agent_id);
                $data['certifications'] = get_field('certifications', $agent_id);
                
                // Contact info
                $data['mobile_phone'] = get_field('mobile_phone', $agent_id);
                $data['website'] = get_field('website', $agent_id);
                
                // Social media
                $data['social'] = [
                    'facebook' => get_field('facebook_url', $agent_id),
                    'instagram' => get_field('instagram_url', $agent_id),
                    'linkedin' => get_field('linkedin_url', $agent_id),
                    'twitter' => get_field('twitter_url', $agent_id),
                    'youtube' => get_field('youtube_url', $agent_id),
                ];
                
                // Performance metrics
                $data['metrics'] = [
                    'total_sales_volume' => get_field('total_sales_volume', $agent_id),
                    'active_listings_count' => get_field('active_listings_count', $agent_id),
                    'sold_listings_count' => get_field('sold_listings_count', $agent_id),
                    'average_dom' => get_field('average_dom', $agent_id),
                    'agent_rating' => get_field('agent_rating', $agent_id),
                ];
            }
        }
        
        return $data;
    }
    
    /**
     * Format community data for API response
     */
    private function format_community_data($community_id) {
        $community = get_post($community_id);
        
        $data = [
            'id' => $community_id,
            'name' => get_the_title($community_id),
            'slug' => $community->post_name,
            'url' => get_permalink($community_id),
            'description' => get_the_excerpt($community_id),
        ];
        
        // Add ACF fields
        if (function_exists('get_field')) {
            $data['city'] = get_field('city', $community_id);
            $data['state'] = get_field('state', $community_id);
            $data['amenities'] = get_field('amenities', $community_id);
            $data['schools'] = get_field('schools', $community_id);
            $data['hoa_info'] = get_field('hoa_info', $community_id);
            
            // Featured image
            $featured_image_id = get_post_thumbnail_id($community_id);
            if ($featured_image_id) {
                $data['image'] = [
                    'url' => wp_get_attachment_url($featured_image_id),
                    'thumb' => wp_get_attachment_image_url($featured_image_id, 'thumbnail'),
                    'medium' => wp_get_attachment_image_url($featured_image_id, 'medium'),
                ];
            }
        }
        
        return $data;
    }
    
    /**
     * Format open house data for API response
     */
    private function format_open_house_data($open_house_id) {
        $open_house = get_post($open_house_id);
        
        $data = [
            'id' => $open_house_id,
            'title' => get_the_title($open_house_id),
            'url' => get_permalink($open_house_id),
        ];
        
        // Add ACF fields
        if (function_exists('get_field')) {
            $data['event_date'] = get_field('event_date', $open_house_id);
            $data['start_time'] = get_field('start_time', $open_house_id);
            $data['end_time'] = get_field('end_time', $open_house_id);
            $data['notes'] = get_field('notes', $open_house_id);
            
            // Related listing
            $listing_id = get_field('listing', $open_house_id);
            if ($listing_id) {
                $data['listing'] = [
                    'id' => $listing_id,
                    'title' => get_the_title($listing_id),
                    'url' => get_permalink($listing_id),
                    'address' => get_field('street_address', $listing_id) . ', ' . 
                                get_field('city', $listing_id) . ', ' . 
                                get_field('state', $listing_id) . ' ' . 
                                get_field('zip_code', $listing_id),
                ];
            }
            
            // Host agent
            $agent_id = get_field('host_agent', $open_house_id);
            if ($agent_id) {
                $data['agent'] = [
                    'id' => $agent_id,
                    'name' => get_the_title($agent_id),
                    'phone' => get_field('phone', $agent_id),
                    'email' => get_field('email', $agent_id),
                ];
            }
        }
        
        return $data;
    }
    
    /**
     * Get agent ID from WordPress user
     */
    private function get_agent_id_from_user($user_id) {
        // Query for agent post linked to this user
        $args = [
            'post_type' => 'agent',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'user_account',
                    'value' => $user_id,
                    'compare' => '=',
                ],
            ],
        ];
        
        $query = new \WP_Query($args);
        
        if ($query->have_posts()) {
            $query->the_post();
            $agent_id = get_the_ID();
            wp_reset_postdata();
            return $agent_id;
        }
        
        return false;
    }
}
