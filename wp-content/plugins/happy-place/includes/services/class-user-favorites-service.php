<?php
/**
 * User Favorites Service
 * 
 * Manages user property favorites with database storage,
 * integrates with existing user meta system for backward compatibility
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class UserFavoritesService extends Service {
    
    protected string $name = 'user_favorites_service';
    protected string $version = '4.0.0';
    
    /**
     * Table name
     */
    private string $table_name;
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'user_favorites';
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
        
        // Register hooks for engagement tracking
        add_action('user_favorite_added', [$this, 'track_favorite_engagement'], 10, 2);
        add_action('user_favorite_removed', [$this, 'track_unfavorite_engagement'], 10, 2);
        
        // Register shortcodes
        add_shortcode('user_favorites', [$this, 'render_user_favorites_shortcode']);
        add_shortcode('favorite_button', [$this, 'render_favorite_button_shortcode']);
        
        $this->initialized = true;
        $this->log('User Favorites Service initialized successfully');
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        // Public handlers (logged in users)
        add_action('wp_ajax_toggle_favorite', [$this, 'ajax_toggle_favorite']);
        add_action('wp_ajax_get_user_favorites', [$this, 'ajax_get_user_favorites']);
        add_action('wp_ajax_remove_favorite', [$this, 'ajax_remove_favorite']);
        add_action('wp_ajax_add_favorite_note', [$this, 'ajax_add_favorite_note']);
        add_action('wp_ajax_get_favorites_count', [$this, 'ajax_get_favorites_count']);
        
        // Keep existing handler for compatibility
        add_action('wp_ajax_toggle_listing_favorite', [$this, 'ajax_toggle_favorite_legacy']);
    }
    
    /**
     * Toggle favorite status for a listing
     */
    public function toggle_favorite(int $listing_id, int $user_id = null): array {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return [
                'success' => false,
                'message' => 'Please login to save favorites',
                'requires_login' => true
            ];
        }
        
        // Validate listing exists
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing' || $listing->post_status !== 'publish') {
            return [
                'success' => false,
                'message' => 'Invalid listing'
            ];
        }
        
        $is_favorited = $this->is_favorited($listing_id, $user_id);
        
        if ($is_favorited) {
            $result = $this->remove_favorite($listing_id, $user_id);
            $action = 'removed';
        } else {
            $result = $this->add_favorite($listing_id, $user_id);
            $action = 'added';
        }
        
        if ($result) {
            // Update legacy user meta for backward compatibility
            $this->sync_with_user_meta($user_id);
            
            // Fire action hooks
            do_action("user_favorite_{$action}", $listing_id, $user_id);
            
            return [
                'success' => true,
                'action' => $action,
                'is_favorited' => !$is_favorited,
                'count' => $this->get_user_favorites_count($user_id)
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update favorite status'
        ];
    }
    
    /**
     * Add listing to favorites
     */
    public function add_favorite(int $listing_id, int $user_id, array $metadata = []): bool {
        global $wpdb;
        
        // Check if already exists
        if ($this->is_favorited($listing_id, $user_id)) {
            return true; // Already favorited
        }
        
        $data = [
            'user_id' => $user_id,
            'listing_id' => $listing_id,
            'created_at' => current_time('mysql'),
            'is_active' => 1
        ];
        
        // Add optional metadata
        if (!empty($metadata['notes'])) {
            $data['notes'] = sanitize_textarea_field($metadata['notes']);
        }
        
        if (!empty($metadata['rating']) && $metadata['rating'] >= 1 && $metadata['rating'] <= 5) {
            $data['rating'] = intval($metadata['rating']);
        }
        
        if (!empty($metadata['tags'])) {
            $data['tags'] = sanitize_text_field($metadata['tags']);
        }
        
        $result = $wpdb->insert($this->table_name, $data);
        
        if ($result !== false) {
            // Update listing favorite count
            $this->update_listing_favorite_count($listing_id);
            
            $this->log("Added favorite: User {$user_id}, Listing {$listing_id}");
            return true;
        }
        
        $this->log("Failed to add favorite: User {$user_id}, Listing {$listing_id} - " . $wpdb->last_error, 'error');
        return false;
    }
    
    /**
     * Remove listing from favorites
     */
    public function remove_favorite(int $listing_id, int $user_id): bool {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            [
                'user_id' => $user_id,
                'listing_id' => $listing_id
            ],
            ['%d', '%d']
        );
        
        if ($result !== false) {
            // Update listing favorite count
            $this->update_listing_favorite_count($listing_id);
            
            $this->log("Removed favorite: User {$user_id}, Listing {$listing_id}");
            return true;
        }
        
        $this->log("Failed to remove favorite: User {$user_id}, Listing {$listing_id} - " . $wpdb->last_error, 'error');
        return false;
    }
    
    /**
     * Check if listing is favorited by user
     */
    public function is_favorited(int $listing_id, int $user_id = null): bool {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }
        
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
             WHERE user_id = %d AND listing_id = %d AND is_active = 1",
            $user_id,
            $listing_id
        ));
        
        return $count > 0;
    }
    
    /**
     * Get user's favorite listings
     */
    public function get_user_favorites(int $user_id, array $args = []): array {
        global $wpdb;
        
        $defaults = [
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'include_details' => true
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $sql = "SELECT f.*, p.post_title, p.post_status, p.post_date
                FROM {$this->table_name} f
                LEFT JOIN {$wpdb->posts} p ON f.listing_id = p.ID
                WHERE f.user_id = %d AND f.is_active = 1";
        
        if ($args['include_details']) {
            $sql .= " AND p.post_status = 'publish' AND p.post_type = 'listing'";
        }
        
        $sql .= " ORDER BY f.{$args['orderby']} {$args['order']}";
        
        if ($args['limit'] > 0) {
            $sql .= " LIMIT {$args['limit']}";
            if ($args['offset'] > 0) {
                $sql .= " OFFSET {$args['offset']}";
            }
        }
        
        $favorites = $wpdb->get_results($wpdb->prepare($sql, $user_id), ARRAY_A);
        
        if (!$args['include_details']) {
            return $favorites;
        }
        
        // Enrich with listing data
        foreach ($favorites as &$favorite) {
            if ($favorite['listing_id']) {
                $favorite['listing_data'] = $this->get_listing_summary($favorite['listing_id']);
            }
        }
        
        return $favorites;
    }
    
    /**
     * Get user favorites count
     */
    public function get_user_favorites_count(int $user_id): int {
        global $wpdb;
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
             WHERE user_id = %d AND is_active = 1",
            $user_id
        ));
    }
    
    /**
     * Get listing favorite count
     */
    public function get_listing_favorite_count(int $listing_id): int {
        global $wpdb;
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
             WHERE listing_id = %d AND is_active = 1",
            $listing_id
        ));
    }
    
    /**
     * Update listing favorite count meta
     */
    private function update_listing_favorite_count(int $listing_id): void {
        $count = $this->get_listing_favorite_count($listing_id);
        update_post_meta($listing_id, 'favorite_count', $count);
    }
    
    /**
     * Sync favorites with legacy user meta system
     */
    private function sync_with_user_meta(int $user_id): void {
        global $wpdb;
        
        // Get all active favorites for user
        $favorites = $wpdb->get_col($wpdb->prepare(
            "SELECT listing_id FROM {$this->table_name} 
             WHERE user_id = %d AND is_active = 1",
            $user_id
        ));
        
        // Update legacy user meta
        update_user_meta($user_id, 'favorite_listings', $favorites);
    }
    
    /**
     * Get listing summary data
     */
    private function get_listing_summary(int $listing_id): array {
        $listing = get_post($listing_id);
        if (!$listing) {
            return [];
        }
        
        return [
            'id' => $listing_id,
            'title' => $listing->post_title,
            'permalink' => get_permalink($listing_id),
            'price' => hpt_get_listing_price($listing_id),
            'price_formatted' => hpt_get_listing_price_formatted($listing_id),
            'address' => hpt_get_listing_address($listing_id),
            'bedrooms' => get_field('bedrooms', $listing_id),
            'bathrooms' => hpt_get_listing_bathrooms($listing_id),
            'square_feet' => get_field('square_feet', $listing_id),
            'featured_image' => get_the_post_thumbnail_url($listing_id, 'medium'),
            'status' => hpt_get_listing_status($listing_id),
            'agent_id' => get_field('listing_agent', $listing_id)
        ];
    }
    
    /**
     * Track favorite engagement
     */
    public function track_favorite_engagement(int $listing_id, int $user_id): void {
        // Track in user activity if service is available
        if (class_exists('\HappyPlace\Services\UserEngagementService')) {
            $engagement_service = new \HappyPlace\Services\UserEngagementService();
            $engagement_service->track_activity($user_id, 'favorite_added', $listing_id, 'listing', [
                'points' => 5 // Award points for favoriting
            ]);
        }
    }
    
    /**
     * Track unfavorite engagement
     */
    public function track_unfavorite_engagement(int $listing_id, int $user_id): void {
        // Track in user activity if service is available
        if (class_exists('\HappyPlace\Services\UserEngagementService')) {
            $engagement_service = new \HappyPlace\Services\UserEngagementService();
            $engagement_service->track_activity($user_id, 'favorite_removed', $listing_id, 'listing');
        }
    }
    
    /**
     * AJAX handler for toggle favorite
     */
    public function ajax_toggle_favorite(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        
        if (!$listing_id) {
            wp_send_json_error(['message' => 'Invalid listing ID']);
            return;
        }
        
        $result = $this->toggle_favorite($listing_id);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX handler for getting user favorites
     */
    public function ajax_get_user_favorites(): void {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please login to view favorites']);
            return;
        }
        
        $user_id = get_current_user_id();
        $page = intval($_POST['page'] ?? 1);
        $limit = intval($_POST['limit'] ?? 10);
        
        $args = [
            'limit' => min($limit, 50), // Cap at 50
            'offset' => ($page - 1) * $limit
        ];
        
        $favorites = $this->get_user_favorites($user_id, $args);
        $total_count = $this->get_user_favorites_count($user_id);
        
        wp_send_json_success([
            'favorites' => $favorites,
            'total_count' => $total_count,
            'page' => $page,
            'has_more' => count($favorites) >= $limit
        ]);
    }
    
    /**
     * Legacy AJAX handler for compatibility
     */
    public function ajax_toggle_favorite_legacy(): void {
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
        $result = $this->toggle_favorite($listing_id);
        
        if ($result['success']) {
            wp_send_json_success([
                'is_favorite' => $result['is_favorited'],
                'action' => $result['action'],
                'count' => $result['count']
            ]);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * Render user favorites shortcode
     */
    public function render_user_favorites_shortcode($atts): string {
        if (!is_user_logged_in()) {
            return '<p>Please <a href="' . wp_login_url(get_permalink()) . '">login</a> to view your saved properties.</p>';
        }
        
        $atts = shortcode_atts([
            'limit' => 10,
            'layout' => 'grid',
            'show_notes' => 'false'
        ], $atts);
        
        $user_id = get_current_user_id();
        $favorites = $this->get_user_favorites($user_id, ['limit' => intval($atts['limit'])]);
        
        if (empty($favorites)) {
            return '<p>You haven\'t saved any properties yet. <a href="' . home_url('/listings') . '">Browse listings</a> to get started.</p>';
        }
        
        ob_start();
        ?>
        <div class="user-favorites-list layout-<?php echo esc_attr($atts['layout']); ?>">
            <?php foreach ($favorites as $favorite): ?>
                <div class="favorite-item" data-listing-id="<?php echo esc_attr($favorite['listing_id']); ?>">
                    <?php
                    // Use existing listing card component
                    get_template_part('template-parts/components/listing/card', null, [
                        'listing_id' => $favorite['listing_id'],
                        'show_favorite_button' => true,
                        'show_favorite_date' => true,
                        'favorite_date' => $favorite['created_at']
                    ]);
                    ?>
                    
                    <?php if ($atts['show_notes'] === 'true' && !empty($favorite['notes'])): ?>
                        <div class="favorite-notes">
                            <strong>Your Notes:</strong>
                            <p><?php echo esc_html($favorite['notes']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="favorites-pagination">
            <button type="button" id="load-more-favorites" class="btn btn-secondary" data-page="2">
                Load More Properties
            </button>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render favorite button shortcode
     */
    public function render_favorite_button_shortcode($atts): string {
        $atts = shortcode_atts([
            'listing_id' => get_the_ID(),
            'class' => '',
            'text_add' => 'Save Property',
            'text_remove' => 'Saved',
            'size' => 'medium'
        ], $atts);
        
        $listing_id = intval($atts['listing_id']);
        if (!$listing_id) {
            return '';
        }
        
        $is_favorited = $this->is_favorited($listing_id);
        $button_class = 'favorite-btn btn-favorite';
        $button_class .= $is_favorited ? ' is-favorited' : '';
        $button_class .= ' btn-' . esc_attr($atts['size']);
        $button_class .= !empty($atts['class']) ? ' ' . esc_attr($atts['class']) : '';
        
        if (!is_user_logged_in()) {
            $button_class .= ' requires-login';
        }
        
        $button_text = $is_favorited ? $atts['text_remove'] : $atts['text_add'];
        $icon_class = $is_favorited ? 'fas fa-heart' : 'far fa-heart';
        
        return sprintf(
            '<button type="button" class="%s" data-listing-id="%d" data-nonce="%s">
                <i class="%s"></i>
                <span class="btn-text">%s</span>
            </button>',
            esc_attr($button_class),
            $listing_id,
            wp_create_nonce('hph_nonce'),
            esc_attr($icon_class),
            esc_html($button_text)
        );
    }
}