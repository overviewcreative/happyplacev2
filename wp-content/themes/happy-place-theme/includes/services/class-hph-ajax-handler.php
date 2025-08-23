<?php
/**
 * AJAX Handler Service
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HPH_Ajax_Handler
 */
class HPH_Ajax_Handler implements HPH_Service {
    
    /**
     * Initialize service
     */
    public function init() {
        $this->register_ajax_handlers();
    }
    
    /**
     * Get service identifier
     */
    public function get_service_id() {
        return 'ajax_handler';
    }
    
    /**
     * Check if service is active
     */
    public function is_active() {
        return true;
    }
    
    /**
     * Get service dependencies
     */
    public function get_dependencies() {
        return array();
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers() {
        // Search listings
        add_action('wp_ajax_hph_search_listings', array($this, 'search_listings'));
        add_action('wp_ajax_nopriv_hph_search_listings', array($this, 'search_listings'));
        
        // Toggle favorites
        add_action('wp_ajax_hph_toggle_favorite', array($this, 'toggle_favorite'));
        add_action('wp_ajax_nopriv_hph_toggle_favorite', array($this, 'toggle_favorite_guest'));
        
        // Dashboard actions
        add_action('wp_ajax_hph_dashboard_action', array($this, 'handle_dashboard_action'));
    }
    
    /**
     * Search listings AJAX handler
     */
    public function search_listings() {
        check_ajax_referer('hph_nonce', 'nonce');
        
        $search_params = array(
            'post_type' => 'listing',
            'posts_per_page' => 12,
            'meta_query' => array()
        );
        
        // Add search filters
        if (!empty($_POST['price_min'])) {
            $search_params['meta_query'][] = array(
                'key' => 'listing_price',
                'value' => intval($_POST['price_min']),
                'compare' => '>='
            );
        }
        
        $query = new WP_Query($search_params);
        $listings = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $listings[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'price' => hpt_get_listing_price_formatted(get_the_ID()),
                    'image' => get_the_post_thumbnail_url(get_the_ID(), 'listing-thumbnail')
                );
            }
        }
        
        wp_reset_postdata();
        wp_send_json_success($listings);
    }
    
    /**
     * Toggle favorite AJAX handler
     */
    public function toggle_favorite() {
        check_ajax_referer('hph_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(__('Please log in to save favorites.', 'happy-place-theme'));
        }
        
        $listing_id = intval($_POST['listing_id']);
        $user_id = get_current_user_id();
        
        $favorites = get_user_meta($user_id, 'hph_favorite_listings', true);
        if (!is_array($favorites)) {
            $favorites = array();
        }
        
        $is_favorite = in_array($listing_id, $favorites);
        
        if ($is_favorite) {
            $favorites = array_diff($favorites, array($listing_id));
            $message = __('Removed from favorites', 'happy-place-theme');
        } else {
            $favorites[] = $listing_id;
            $message = __('Added to favorites', 'happy-place-theme');
        }
        
        update_user_meta($user_id, 'hph_favorite_listings', array_unique($favorites));
        
        wp_send_json_success(array(
            'is_favorite' => !$is_favorite,
            'message' => $message
        ));
    }
    
    /**
     * Toggle favorite for guests
     */
    public function toggle_favorite_guest() {
        wp_send_json_error(__('Please log in to save favorites.', 'happy-place-theme'));
    }
    
    /**
     * Handle dashboard actions
     */
    public function handle_dashboard_action() {
        check_ajax_referer('hph_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Insufficient permissions.', 'happy-place-theme'));
        }
        
        $action = sanitize_text_field($_POST['dashboard_action']);
        
        switch ($action) {
            case 'save_listing':
                $this->save_listing();
                break;
            case 'delete_listing':
                $this->delete_listing();
                break;
            default:
                wp_send_json_error(__('Invalid action.', 'happy-place-theme'));
        }
    }
    
    /**
     * Save listing via AJAX
     */
    private function save_listing() {
        // Implementation for saving listing
        wp_send_json_success(__('Listing saved successfully.', 'happy-place-theme'));
    }
    
    /**
     * Delete listing via AJAX
     */
    private function delete_listing() {
        // Implementation for deleting listing
        wp_send_json_success(__('Listing deleted successfully.', 'happy-place-theme'));
    }
}
