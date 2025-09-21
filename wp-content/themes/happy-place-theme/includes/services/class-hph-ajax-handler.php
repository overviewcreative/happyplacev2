<?php
/**
 * AJAX Handler Service
 * 
 * Handles dashboard-specific AJAX actions.
 * Other AJAX functionality is organized in includes/ajax/ directory.
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
 * 
 * Lightweight AJAX service focused on dashboard functionality.
 * Search, favorites, and other AJAX actions are handled by organized files in includes/ajax/
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
     * 
     * Note: Other AJAX handlers are organized in includes/ajax/ directory:
     * - Search: includes/ajax/search-ajax.php
     * - User interactions: includes/ajax/user-interactions.php
     * - Contact forms: includes/ajax/contact-forms.php
     * - Archive functionality: includes/ajax/archive-ajax.php
     */
    private function register_ajax_handlers() {
        // Dashboard actions for authenticated users
        add_action('wp_ajax_hph_dashboard_action', array($this, 'handle_dashboard_action'));
    }
    
    /**
     * Handle dashboard actions
     * 
     * Central handler for dashboard-specific AJAX requests
     */
    public function handle_dashboard_action() {
        // Verify nonce for security
        check_ajax_referer('hph_nonce', 'nonce');
        
        // Check user permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Insufficient permissions.', 'happy-place-theme'));
        }
        
        // Get and sanitize the action
        $action = sanitize_text_field($_POST['dashboard_action'] ?? '');
        
        if (empty($action)) {
            wp_send_json_error(__('No action specified.', 'happy-place-theme'));
        }
        
        // Route to appropriate handler
        switch ($action) {
            case 'save_listing':
                $this->save_listing();
                break;
                
            case 'delete_listing':
                $this->delete_listing();
                break;
                
            case 'update_status':
                $this->update_listing_status();
                break;
                
            default:
                wp_send_json_error(__('Invalid dashboard action.', 'happy-place-theme'));
        }
    }
    
    /**
     * Save listing via AJAX
     * 
     * Handles listing creation and updates from dashboard
     */
    private function save_listing() {
        // Get listing data
        $listing_data = array(
            'post_title' => sanitize_text_field($_POST['listing_title'] ?? ''),
            'post_content' => wp_kses_post($_POST['listing_description'] ?? ''),
            'post_status' => 'draft', // Default to draft for review
            'post_type' => 'listing'
        );
        
        // Validate required fields
        if (empty($listing_data['post_title'])) {
            wp_send_json_error(__('Listing title is required.', 'happy-place-theme'));
        }
        
        // Check if this is an update or new listing
        $listing_id = intval($_POST['listing_id'] ?? 0);
        
        if ($listing_id > 0) {
            // Update existing listing
            $listing_data['ID'] = $listing_id;
            $result = wp_update_post($listing_data);
            $message = __('Listing updated successfully.', 'happy-place-theme');
        } else {
            // Create new listing
            $listing_data['post_author'] = get_current_user_id();
            $result = wp_insert_post($listing_data);
            $message = __('Listing created successfully.', 'happy-place-theme');
            $listing_id = $result;
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error(__('Failed to save listing.', 'happy-place-theme'));
        }
        
        // Save custom fields if provided
        $this->save_listing_meta($listing_id);
        
        wp_send_json_success(array(
            'message' => $message,
            'listing_id' => $listing_id,
            'edit_url' => admin_url("post.php?post={$listing_id}&action=edit")
        ));
    }
    
    /**
     * Delete listing via AJAX
     * 
     * Handles listing deletion from dashboard
     */
    private function delete_listing() {
        $listing_id = intval($_POST['listing_id'] ?? 0);
        
        if ($listing_id <= 0) {
            wp_send_json_error(__('Invalid listing ID.', 'happy-place-theme'));
        }
        
        // Check if user can delete this listing
        if (!current_user_can('delete_post', $listing_id)) {
            wp_send_json_error(__('You cannot delete this listing.', 'happy-place-theme'));
        }
        
        // Move to trash instead of permanent delete
        $result = wp_trash_post($listing_id);
        
        if (!$result) {
            wp_send_json_error(__('Failed to delete listing.', 'happy-place-theme'));
        }
        
        wp_send_json_success(array(
            'message' => __('Listing moved to trash.', 'happy-place-theme'),
            'listing_id' => $listing_id
        ));
    }
    
    /**
     * Update listing status via AJAX
     * 
     * Handles status changes (active, sold, pending, etc.)
     */
    private function update_listing_status() {
        $listing_id = intval($_POST['listing_id'] ?? 0);
        $new_status = sanitize_text_field($_POST['listing_status'] ?? '');
        
        if ($listing_id <= 0) {
            wp_send_json_error(__('Invalid listing ID.', 'happy-place-theme'));
        }
        
        if (empty($new_status)) {
            wp_send_json_error(__('Status is required.', 'happy-place-theme'));
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $listing_id)) {
            wp_send_json_error(__('You cannot edit this listing.', 'happy-place-theme'));
        }
        
        // Valid status values
        $valid_statuses = array('active', 'pending', 'sold', 'expired', 'withdrawn');
        
        if (!in_array($new_status, $valid_statuses)) {
            wp_send_json_error(__('Invalid status value.', 'happy-place-theme'));
        }
        
        // Update the listing status meta field
        $result = update_post_meta($listing_id, 'listing_status', $new_status);
        
        if ($result === false) {
            wp_send_json_error(__('Failed to update listing status.', 'happy-place-theme'));
        }
        
        wp_send_json_success(array(
            'message' => __('Listing status updated successfully.', 'happy-place-theme'),
            'listing_id' => $listing_id,
            'new_status' => $new_status
        ));
    }
    
    /**
     * Save listing custom meta fields
     * 
     * @param int $listing_id The listing post ID
     */
    private function save_listing_meta($listing_id) {
        // Define allowed meta fields to prevent arbitrary meta updates
        $allowed_fields = array(
            'listing_price' => 'floatval',
            'bedrooms' => 'intval',
            'bathrooms_full' => 'intval',
            'bathrooms_half' => 'intval',
            'square_feet' => 'intval',
            'lot_size_acres' => 'floatval',
            'property_type' => 'sanitize_text_field',
            'listing_status' => 'sanitize_text_field',
            'street_address' => 'sanitize_text_field',
            'city' => 'sanitize_text_field',
            'state' => 'sanitize_text_field',
            'zip_code' => 'sanitize_text_field'
        );
        
        foreach ($allowed_fields as $field => $sanitize_callback) {
            if (isset($_POST[$field])) {
                $value = call_user_func($sanitize_callback, $_POST[$field]);
                update_post_meta($listing_id, $field, $value);
            }
        }
    }
}
