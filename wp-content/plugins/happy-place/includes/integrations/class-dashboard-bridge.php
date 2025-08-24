<?php
/**
 * Dashboard Bridge Integration
 * 
 * Provides seamless integration between the theme's dashboard bridge functions
 * and the plugin's service layer. This class acts as a translator between
 * the theme's expected data format and our service architecture.
 * 
 * @package HappyPlace\Integrations
 * @version 4.0.0
 */

namespace HappyPlace\Integrations;

use HappyPlace\Services\ListingService;
use HappyPlace\Services\FormService;
use HappyPlace\Services\ImportService;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard Bridge Integration Class
 * 
 * Bridges the gap between theme dashboard functions and plugin services
 */
class DashboardBridge {
    
    /**
     * Service instances
     */
    private ListingService $listing_service;
    private FormService $form_service;
    private ImportService $import_service;
    
    /**
     * Singleton instance
     */
    private static ?DashboardBridge $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance(): DashboardBridge {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->listing_service = new ListingService();
        $this->form_service = new FormService();
        $this->import_service = new ImportService();
        
        // Initialize services
        $this->listing_service->init();
        $this->form_service->init();
        $this->import_service->init();
        
        // Hook into WordPress
        $this->register_hooks();
    }
    
    /**
     * Register WordPress hooks
     */
    private function register_hooks(): void {
        // Replace theme AJAX handlers with service-powered ones
        add_action('wp_ajax_hph_create_listing', [$this, 'ajax_create_listing'], 5);
        add_action('wp_ajax_hph_update_listing', [$this, 'ajax_update_listing'], 5);
        add_action('wp_ajax_hph_delete_listing', [$this, 'ajax_delete_listing'], 5);
        add_action('wp_ajax_hph_get_listings', [$this, 'ajax_get_listings'], 5);
        add_action('wp_ajax_hph_get_listing_stats', [$this, 'ajax_get_listing_stats'], 5);
        add_action('wp_ajax_hph_bulk_listings_action', [$this, 'ajax_bulk_listings_action'], 5);
        
        // Provide bridge functions for theme templates
        add_action('init', [$this, 'register_bridge_functions']);
        
        // Filter dashboard data through services
        add_filter('hpt_dashboard_listing_data', [$this, 'filter_listing_data'], 10, 2);
        add_filter('hpt_dashboard_stats', [$this, 'filter_dashboard_stats'], 10, 2);
    }
    
    /**
     * Register global bridge functions for theme compatibility
     */
    public function register_bridge_functions(): void {
        // Only register if theme bridge functions don't exist
        if (!function_exists('hpt_get_user_listings_via_service')) {
            require_once __DIR__ . '/dashboard-bridge-functions.php';
        }
    }
    
    /**
     * AJAX: Create listing using service layer
     */
    public function ajax_create_listing(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_dashboard_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        try {
            // Process through form service for validation
            $result = $this->form_service->process_submission($_POST, 'listing');
            
            if (is_wp_error($result)) {
                wp_send_json_error([
                    'message' => $result->get_error_message(),
                    'errors' => $result->get_error_data()
                ]);
            }
            
            // Get the created listing data for dashboard response
            $listing_data = $this->get_dashboard_listing_data($result['listing_id']);
            
            wp_send_json_success([
                'message' => 'Listing created successfully',
                'listing_id' => $result['listing_id'],
                'listing_data' => $listing_data,
                'redirect' => $result['redirect'] ?? null
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX: Update listing using service layer
     */
    public function ajax_update_listing(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_dashboard_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        
        if (!$listing_id) {
            wp_send_json_error(['message' => 'Invalid listing ID']);
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $listing_id)) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        try {
            // Update through listing service
            $result = $this->listing_service->update_listing($listing_id, $_POST);
            
            if (is_wp_error($result)) {
                wp_send_json_error(['message' => $result->get_error_message()]);
            }
            
            // Get updated listing data for dashboard
            $listing_data = $this->get_dashboard_listing_data($listing_id);
            
            wp_send_json_success([
                'message' => 'Listing updated successfully',
                'listing_id' => $listing_id,
                'listing_data' => $listing_data
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX: Delete listing using service layer
     */
    public function ajax_delete_listing(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_dashboard_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        
        if (!$listing_id) {
            wp_send_json_error(['message' => 'Invalid listing ID']);
        }
        
        // Check permissions
        if (!current_user_can('delete_post', $listing_id)) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        try {
            $result = $this->listing_service->delete_listing($listing_id);
            
            if (is_wp_error($result)) {
                wp_send_json_error(['message' => $result->get_error_message()]);
            }
            
            wp_send_json_success([
                'message' => 'Listing deleted successfully',
                'listing_id' => $listing_id
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX: Get listings with service layer enhancements
     */
    public function ajax_get_listings(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_dashboard_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        try {
            // Build query parameters
            $args = [
                'page' => intval($_POST['page'] ?? 1),
                'per_page' => intval($_POST['per_page'] ?? 12),
                'search' => sanitize_text_field($_POST['search'] ?? ''),
                'status' => sanitize_text_field($_POST['status'] ?? 'all'),
                'sort' => sanitize_text_field($_POST['sort'] ?? 'date-desc'),
                'user_id' => get_current_user_id()
            ];
            
            // Get listings using optimized service methods
            $listings = $this->get_dashboard_listings($args);
            $total = $this->get_dashboard_listings_count($args);
            
            wp_send_json_success([
                'listings' => $listings,
                'pagination' => [
                    'current_page' => $args['page'],
                    'total_pages' => ceil($total / $args['per_page']),
                    'total_listings' => $total,
                    'per_page' => $args['per_page']
                ]
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX: Get listing statistics
     */
    public function ajax_get_listing_stats(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_dashboard_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        try {
            $user_id = get_current_user_id();
            $stats = $this->get_enhanced_listing_stats($user_id);
            
            wp_send_json_success($stats);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX: Handle bulk listing actions
     */
    public function ajax_bulk_listings_action(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_dashboard_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $action = sanitize_text_field($_POST['action_type'] ?? '');
        $listing_ids = array_map('intval', $_POST['listing_ids'] ?? []);
        
        if (empty($action) || empty($listing_ids)) {
            wp_send_json_error(['message' => 'Invalid action or listing IDs']);
        }
        
        try {
            $results = [];
            
            switch ($action) {
                case 'delete':
                    $results = $this->listing_service->bulk_delete($listing_ids);
                    break;
                    
                case 'activate':
                    $results = $this->listing_service->bulk_update($listing_ids, ['status' => 'publish']);
                    break;
                    
                case 'deactivate':
                    $results = $this->listing_service->bulk_update($listing_ids, ['status' => 'draft']);
                    break;
                    
                case 'feature':
                    $results = $this->listing_service->bulk_update($listing_ids, ['featured' => true]);
                    break;
                    
                case 'unfeature':
                    $results = $this->listing_service->bulk_update($listing_ids, ['featured' => false]);
                    break;
                    
                default:
                    wp_send_json_error(['message' => 'Unknown action']);
                    return;
            }
            
            wp_send_json_success([
                'message' => sprintf(
                    'Bulk action completed: %d succeeded, %d failed',
                    $results['success'],
                    $results['failed']
                ),
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Filter listing data through services
     */
    public function filter_listing_data(array $data, int $listing_id): array {
        // Enhance with service-layer data processing
        return $this->get_dashboard_listing_data($listing_id);
    }
    
    /**
     * Filter dashboard stats through services
     */
    public function filter_dashboard_stats(array $stats, int $user_id): array {
        return $this->get_enhanced_listing_stats($user_id);
    }
    
    /**
     * Get listings formatted for dashboard display
     */
    private function get_dashboard_listings(array $args): array {
        // Build WP_Query args compatible with service expectations
        $query_args = [
            'post_type' => 'listing',
            'posts_per_page' => $args['per_page'],
            'paged' => $args['page'],
            'post_status' => ['publish', 'draft', 'private'],
            'orderby' => $this->parse_sort_order($args['sort']),
            'meta_query' => []
        ];
        
        // Add search
        if (!empty($args['search'])) {
            $query_args['s'] = $args['search'];
        }
        
        // Add status filter
        if ($args['status'] !== 'all') {
            $query_args['post_status'] = [$args['status']];
        }
        
        // Add user filtering
        if (!current_user_can('manage_options') && $args['user_id']) {
            $query_args['author'] = $args['user_id'];
        }
        
        $query = new \WP_Query($query_args);
        $listings = [];
        
        foreach ($query->posts as $post) {
            $listings[] = $this->get_dashboard_listing_data($post->ID);
        }
        
        return $listings;
    }
    
    /**
     * Get listings count for pagination
     */
    private function get_dashboard_listings_count(array $args): int {
        $count_args = [
            'post_type' => 'listing',
            'post_status' => $args['status'] !== 'all' ? [$args['status']] : ['publish', 'draft', 'private'],
            'posts_per_page' => -1,
            'fields' => 'ids'
        ];
        
        if (!empty($args['search'])) {
            $count_args['s'] = $args['search'];
        }
        
        if (!current_user_can('manage_options') && $args['user_id']) {
            $count_args['author'] = $args['user_id'];
        }
        
        $count_query = new \WP_Query($count_args);
        return $count_query->found_posts;
    }
    
    /**
     * Get enhanced listing statistics
     */
    private function get_enhanced_listing_stats(int $user_id): array {
        $base_args = [
            'post_type' => 'listing',
            'author' => current_user_can('manage_options') ? null : $user_id,
            'posts_per_page' => -1,
            'fields' => 'ids'
        ];
        
        // Active listings
        $active_query = new \WP_Query(array_merge($base_args, [
            'post_status' => 'publish'
        ]));
        
        // Draft listings
        $draft_query = new \WP_Query(array_merge($base_args, [
            'post_status' => 'draft'
        ]));
        
        // Featured listings
        $featured_query = new \WP_Query(array_merge($base_args, [
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_listing_featured',
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ]));
        
        // This month's listings
        $this_month_query = new \WP_Query(array_merge($base_args, [
            'date_query' => [
                [
                    'after' => date('Y-m-01'),
                    'inclusive' => true
                ]
            ]
        ]));
        
        return [
            'active_listings' => $active_query->found_posts,
            'draft_listings' => $draft_query->found_posts,
            'featured_listings' => $featured_query->found_posts,
            'this_month_listings' => $this_month_query->found_posts,
            'total_listings' => $active_query->found_posts + $draft_query->found_posts
        ];
    }
    
    /**
     * Get single listing data formatted for dashboard
     */
    private function get_dashboard_listing_data(int $listing_id): array {
        $post = get_post($listing_id);
        
        if (!$post || $post->post_type !== 'listing') {
            return [];
        }
        
        // Get ACF fields
        $fields = get_fields($listing_id) ?: [];
        
        // Get featured image
        $featured_image = get_the_post_thumbnail_url($listing_id, 'medium');
        
        return [
            'id' => $listing_id,
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'status' => $post->post_status,
            'date' => $post->post_date,
            'modified' => $post->post_modified,
            'author_id' => $post->post_author,
            'author_name' => get_the_author_meta('display_name', $post->post_author),
            'featured_image' => $featured_image,
            'excerpt' => get_the_excerpt($post),
            'permalink' => get_permalink($listing_id),
            'edit_link' => admin_url("post.php?post={$listing_id}&action=edit"),
            
            // Real estate specific fields
            'price' => $fields['price'] ?? 0,
            'bedrooms' => $fields['bedrooms'] ?? 0,
            'bathrooms' => $fields['bathrooms'] ?? 0,
            'square_feet' => $fields['square_feet'] ?? 0,
            'address' => [
                'street' => $fields['street_address'] ?? '',
                'city' => $fields['city'] ?? '',
                'state' => $fields['state'] ?? '',
                'zip' => $fields['zip_code'] ?? ''
            ],
            'mls_number' => $fields['mls_number'] ?? '',
            'property_type' => $fields['property_type'] ?? '',
            'listing_type' => $fields['listing_type'] ?? 'sale',
            'featured' => !empty($fields['_listing_featured']),
            
            // Dashboard specific data
            'dashboard_actions' => $this->get_listing_dashboard_actions($listing_id),
            'formatted_price' => $this->format_price($fields['price'] ?? 0),
            'status_label' => $this->get_status_label($post->post_status),
            'days_on_market' => $this->calculate_days_on_market($post->post_date)
        ];
    }
    
    /**
     * Get available dashboard actions for a listing
     */
    private function get_listing_dashboard_actions(int $listing_id): array {
        $actions = [];
        
        if (current_user_can('edit_post', $listing_id)) {
            $actions[] = ['label' => 'Edit', 'action' => 'edit', 'icon' => 'edit'];
            $actions[] = ['label' => 'Duplicate', 'action' => 'duplicate', 'icon' => 'copy'];
        }
        
        if (current_user_can('delete_post', $listing_id)) {
            $actions[] = ['label' => 'Delete', 'action' => 'delete', 'icon' => 'trash', 'confirm' => true];
        }
        
        $actions[] = ['label' => 'View', 'action' => 'view', 'icon' => 'eye'];
        
        return $actions;
    }
    
    /**
     * Parse sort order for WP_Query
     */
    private function parse_sort_order(string $sort): array {
        switch ($sort) {
            case 'date-desc':
                return ['date' => 'DESC'];
            case 'date-asc':
                return ['date' => 'ASC'];
            case 'title-asc':
                return ['title' => 'ASC'];
            case 'title-desc':
                return ['title' => 'DESC'];
            case 'price-asc':
                return ['meta_value_num' => 'ASC', 'meta_key' => 'price'];
            case 'price-desc':
                return ['meta_value_num' => 'DESC', 'meta_key' => 'price'];
            default:
                return ['date' => 'DESC'];
        }
    }
    
    /**
     * Format price for display
     */
    private function format_price(float $price): string {
        if ($price <= 0) {
            return 'Contact for Price';
        }
        
        return '$' . number_format($price);
    }
    
    /**
     * Get human-readable status label
     */
    private function get_status_label(string $status): string {
        $labels = [
            'publish' => 'Active',
            'draft' => 'Draft',
            'private' => 'Private',
            'pending' => 'Pending Review'
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
    
    /**
     * Calculate days on market
     */
    private function calculate_days_on_market(string $date): int {
        $created = new \DateTime($date);
        $now = new \DateTime();
        $diff = $now->diff($created);
        
        return $diff->days;
    }
    
    /**
     * Get form HTML for dashboard modal
     */
    public function get_listing_form_html(int $listing_id = null): string {
        return $this->form_service->render_listing_form($listing_id);
    }
    
    /**
     * Get import interface HTML
     */
    public function get_import_interface_html(): string {
        // This would render the CSV import interface
        // Integration with ImportService for dashboard
        return '<div id="importInterface">Import interface will be rendered here</div>';
    }
    
    /**
     * Create listing via service (called by bridge functions)
     */
    public function create_listing_via_service(array $data): int|WP_Error {
        try {
            return $this->listing_service->create_listing($data);
        } catch (\Exception $e) {
            return new WP_Error('service_error', $e->getMessage());
        }
    }
    
    /**
     * Update listing via service (called by bridge functions)
     */
    public function update_listing_via_service(int $listing_id, array $data): bool|WP_Error {
        try {
            return $this->listing_service->update_listing($listing_id, $data);
        } catch (\Exception $e) {
            return new WP_Error('service_error', $e->getMessage());
        }
    }
    
    /**
     * Delete listing via service (called by bridge functions)
     */
    public function delete_listing_via_service(int $listing_id): bool|WP_Error {
        try {
            return $this->listing_service->delete_listing($listing_id);
        } catch (\Exception $e) {
            return new WP_Error('service_error', $e->getMessage());
        }
    }
    
    /**
     * Get user listings via service (called by bridge functions)
     */
    public function get_user_listings_via_service(int $user_id, array $args = []): array {
        // Set defaults
        $args = wp_parse_args($args, [
            'per_page' => 20,
            'page' => 1,
            'status' => 'all',
            'sort' => 'date-desc'
        ]);
        
        try {
            return $this->get_dashboard_listings(array_merge($args, ['user_id' => $user_id]));
        } catch (\Exception $e) {
            if (function_exists('hp_log')) {
                hp_log('Get user listings via service error: ' . $e->getMessage(), 'error', 'dashboard');
            }
            return [];
        }
    }
    
    /**
     * Get enhanced dashboard statistics (called by bridge functions)
     */
    public function get_enhanced_dashboard_stats(int $user_id): array {
        try {
            return $this->get_enhanced_listing_stats($user_id);
        } catch (\Exception $e) {
            if (function_exists('hp_log')) {
                hp_log('Get enhanced dashboard stats error: ' . $e->getMessage(), 'error', 'dashboard');
            }
            return [];
        }
    }
}