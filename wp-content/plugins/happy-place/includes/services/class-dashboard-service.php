<?php
/**
 * Dashboard Service
 *
 * Handles all dashboard-related business logic and AJAX operations.
 * Migrated from theme dashboard-ajax.php for proper separation of concerns.
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
 * Dashboard Service Class
 *
 * Centralizes dashboard functionality including stats, listings, and user interactions
 */
class DashboardService extends Service {

    /**
     * Service instances
     */
    private ?ListingService $listing_service = null;
    private ?UnifiedLeadService $lead_service = null;
    private ?AgentService $agent_service = null;

    /**
     * Initialize the service
     */
    public function init(): void {
        $this->register_ajax_handlers();
        $this->init_dependencies();
    }

    /**
     * Initialize service dependencies
     */
    private function init_dependencies(): void {
        $this->listing_service = new ListingService();
        $this->lead_service = new UnifiedLeadService();
        $this->agent_service = new AgentService();
    }

    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        // Core dashboard functions
        add_action('wp_ajax_hph_get_dashboard_stats', [$this, 'get_dashboard_stats']);
        add_action('wp_ajax_hph_get_recent_activity', [$this, 'get_recent_activity']);
        add_action('wp_ajax_hph_get_upcoming_events', [$this, 'get_upcoming_events']);
        add_action('wp_ajax_hph_get_hot_leads', [$this, 'get_hot_leads']);

        // Listing CRUD operations
        add_action('wp_ajax_hph_create_listing', [$this, 'create_listing']);
        add_action('wp_ajax_hph_update_listing', [$this, 'update_listing']);
        add_action('wp_ajax_hph_delete_listing', [$this, 'delete_listing']);
        add_action('wp_ajax_hph_get_listings', [$this, 'get_listings']);
        add_action('wp_ajax_hph_get_listing_stats', [$this, 'get_listing_stats']);
        add_action('wp_ajax_hph_update_listing_price', [$this, 'update_listing_price']);
        add_action('wp_ajax_hph_update_listing_status', [$this, 'update_listing_status']);

        // Lead management
        add_action('wp_ajax_hph_create_lead', [$this, 'create_lead']);
        add_action('wp_ajax_hph_update_lead', [$this, 'update_lead']);
        add_action('wp_ajax_hph_get_leads', [$this, 'get_leads']);

        // Profile management
        add_action('wp_ajax_hph_update_profile', [$this, 'update_profile']);
        add_action('wp_ajax_hph_upload_profile_image', [$this, 'upload_profile_image']);

        // Analytics and reporting
        add_action('wp_ajax_hph_get_analytics', [$this, 'get_analytics']);
        add_action('wp_ajax_hph_export_data', [$this, 'export_data']);
    }

    /**
     * Get dashboard statistics
     */
    public function get_dashboard_stats(): void {
        try {
            // Verify nonce
            if (!$this->verify_nonce('dashboard_nonce')) {
                wp_send_json_error('Invalid nonce', 403);
                return;
            }

            $user_id = get_current_user_id();
            if (!$user_id) {
                wp_send_json_error('User not authenticated', 401);
                return;
            }

            // Get user role to determine what stats to show
            $user = wp_get_current_user();
            $is_admin = in_array('administrator', $user->roles);
            $is_agent = in_array('agent', $user->roles);

            $stats = [];

            if ($is_admin || $is_agent) {
                // Agent/Admin stats
                $stats = [
                    'total_listings' => $this->get_user_listing_count($user_id, 'all'),
                    'active_listings' => $this->get_user_listing_count($user_id, 'active'),
                    'pending_listings' => $this->get_user_listing_count($user_id, 'pending'),
                    'sold_listings' => $this->get_user_listing_count($user_id, 'sold'),
                    'total_leads' => $this->get_user_lead_count($user_id),
                    'hot_leads' => $this->get_user_lead_count($user_id, 'hot'),
                    'recent_activity' => $this->get_recent_activity_data($user_id, 5),
                ];
            } else {
                // Regular user stats
                $stats = [
                    'saved_listings' => $this->get_user_saved_listings_count($user_id),
                    'saved_searches' => $this->get_user_saved_searches_count($user_id),
                    'recent_views' => $this->get_user_recent_views_count($user_id),
                ];
            }

            wp_send_json_success($stats);

        } catch (Exception $e) {
            error_log('Dashboard stats error: ' . $e->getMessage());
            wp_send_json_error('Failed to get dashboard stats', 500);
        }
    }

    /**
     * Get user listing count by status
     */
    private function get_user_listing_count(int $user_id, string $status = 'all'): int {
        $args = [
            'post_type' => 'listing',
            'author' => $user_id,
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];

        if ($status !== 'all') {
            $args['meta_query'] = [
                [
                    'key' => '_listing_status',
                    'value' => $status,
                    'compare' => '='
                ]
            ];
        }

        $query = new \WP_Query($args);
        return $query->found_posts;
    }

    /**
     * Get user lead count
     */
    private function get_user_lead_count(int $user_id, string $status = 'all'): int {
        if (!$this->lead_service) {
            return 0;
        }

        $args = ['assigned_to' => $user_id];
        if ($status !== 'all') {
            $args['status'] = $status;
        }

        $leads = $this->lead_service->get_leads($args);
        return count($leads);
    }

    /**
     * Get user saved listings count
     */
    private function get_user_saved_listings_count(int $user_id): int {
        $saved = get_user_meta($user_id, 'saved_listings', true);
        return is_array($saved) ? count($saved) : 0;
    }

    /**
     * Get user saved searches count
     */
    private function get_user_saved_searches_count(int $user_id): int {
        $saved = get_user_meta($user_id, 'saved_searches', true);
        return is_array($saved) ? count($saved) : 0;
    }

    /**
     * Get user recent views count
     */
    private function get_user_recent_views_count(int $user_id): int {
        $views = get_user_meta($user_id, 'recent_listing_views', true);
        return is_array($views) ? count($views) : 0;
    }

    /**
     * Get recent activity data
     */
    private function get_recent_activity_data(int $user_id, int $limit = 10): array {
        // Get recent listing updates, lead activity, etc.
        $activities = [];

        // Recent listings
        $recent_listings = get_posts([
            'post_type' => 'listing',
            'author' => $user_id,
            'posts_per_page' => 3,
            'orderby' => 'modified',
            'order' => 'DESC'
        ]);

        foreach ($recent_listings as $listing) {
            $activities[] = [
                'type' => 'listing_updated',
                'title' => $listing->post_title,
                'date' => $listing->post_modified,
                'link' => get_permalink($listing->ID)
            ];
        }

        // Sort by date and limit
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, $limit);
    }

    /**
     * Get listings for dashboard
     */
    public function get_listings(): void {
        try {
            if (!$this->verify_nonce('dashboard_nonce')) {
                wp_send_json_error('Invalid nonce', 403);
                return;
            }

            $user_id = get_current_user_id();
            $page = intval($_POST['page'] ?? 1);
            $per_page = intval($_POST['per_page'] ?? 10);
            $status = sanitize_text_field($_POST['status'] ?? 'all');

            $args = [
                'post_type' => 'listing',
                'author' => $user_id,
                'posts_per_page' => $per_page,
                'paged' => $page,
                'orderby' => 'modified',
                'order' => 'DESC'
            ];

            if ($status !== 'all') {
                $args['meta_query'] = [
                    [
                        'key' => '_listing_status',
                        'value' => $status,
                        'compare' => '='
                    ]
                ];
            }

            $query = new \WP_Query($args);
            $listings = [];

            foreach ($query->posts as $post) {
                $listings[] = [
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'status' => get_post_meta($post->ID, '_listing_status', true),
                    'price' => get_post_meta($post->ID, '_listing_price', true),
                    'address' => get_post_meta($post->ID, '_listing_address', true),
                    'modified' => $post->post_modified,
                    'permalink' => get_permalink($post->ID)
                ];
            }

            wp_send_json_success([
                'listings' => $listings,
                'total' => $query->found_posts,
                'pages' => $query->max_num_pages
            ]);

        } catch (Exception $e) {
            error_log('Get listings error: ' . $e->getMessage());
            wp_send_json_error('Failed to get listings', 500);
        }
    }

    /**
     * Update listing price
     */
    public function update_listing_price(): void {
        try {
            if (!$this->verify_nonce('dashboard_nonce')) {
                wp_send_json_error('Invalid nonce', 403);
                return;
            }

            $listing_id = intval($_POST['listing_id'] ?? 0);
            $new_price = sanitize_text_field($_POST['price'] ?? '');

            if (!$listing_id || !$new_price) {
                wp_send_json_error('Missing required fields', 400);
                return;
            }

            // Check permissions
            if (!$this->can_user_edit_listing($listing_id)) {
                wp_send_json_error('Permission denied', 403);
                return;
            }

            // Update price
            update_post_meta($listing_id, '_listing_price', $new_price);

            // Log the change
            $this->log_listing_change($listing_id, 'price_updated', [
                'old_price' => get_post_meta($listing_id, '_listing_price', true),
                'new_price' => $new_price
            ]);

            wp_send_json_success(['message' => 'Price updated successfully']);

        } catch (Exception $e) {
            error_log('Update listing price error: ' . $e->getMessage());
            wp_send_json_error('Failed to update price', 500);
        }
    }

    /**
     * Check if user can edit listing
     */
    private function can_user_edit_listing(int $listing_id): bool {
        $current_user_id = get_current_user_id();

        // Admins can edit all
        if (current_user_can('administrator')) {
            return true;
        }

        // Check if user is the author
        $post = get_post($listing_id);
        if ($post && $post->post_author == $current_user_id) {
            return true;
        }

        // Check if user is assigned agent
        $assigned_agents = get_post_meta($listing_id, 'listing_agent', true);
        if ($assigned_agents) {
            $agent_ids = is_array($assigned_agents) ? $assigned_agents : [$assigned_agents];
            foreach ($agent_ids as $agent_id) {
                $synced_user_id = get_post_meta($agent_id, '_synced_user_id', true);
                if ($synced_user_id == $current_user_id) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Log listing changes
     */
    private function log_listing_change(int $listing_id, string $action, array $data = []): void {
        $log_entry = [
            'listing_id' => $listing_id,
            'action' => $action,
            'user_id' => get_current_user_id(),
            'timestamp' => current_time('mysql'),
            'data' => $data
        ];

        // Store in listing meta for now, could be moved to custom table later
        $existing_log = get_post_meta($listing_id, '_listing_activity_log', true);
        $existing_log = is_array($existing_log) ? $existing_log : [];
        $existing_log[] = $log_entry;

        // Keep only last 50 entries
        if (count($existing_log) > 50) {
            $existing_log = array_slice($existing_log, -50);
        }

        update_post_meta($listing_id, '_listing_activity_log', $existing_log);
    }

    /**
     * Verify nonce for AJAX requests
     */
    private function verify_nonce(string $nonce_name): bool {
        $nonce = sanitize_text_field($_POST['nonce'] ?? '');
        return wp_verify_nonce($nonce, $nonce_name);
    }

    /**
     * Placeholder methods for remaining AJAX handlers
     * These will be implemented as needed
     */
    public function get_recent_activity(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function get_upcoming_events(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function get_hot_leads(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function create_listing(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function update_listing(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function delete_listing(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function get_listing_stats(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function update_listing_status(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function create_lead(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function update_lead(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function get_leads(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function update_profile(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function upload_profile_image(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function get_analytics(): void {
        wp_send_json_error('Not implemented yet');
    }

    public function export_data(): void {
        wp_send_json_error('Not implemented yet');
    }
}