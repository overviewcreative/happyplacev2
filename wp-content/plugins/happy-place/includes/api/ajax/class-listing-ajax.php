<?php
/**
 * Listing AJAX Handler Class
 * 
 * Handles all AJAX requests for listing operations
 *
 * @package HappyPlace\Api\Ajax
 */

namespace HappyPlace\Api\Ajax;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Listing_Ajax {
    
    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        // Register AJAX actions for logged-in users
        add_action('wp_ajax_hp_save_listing', [__CLASS__, 'save_listing']);
        add_action('wp_ajax_hp_get_listing', [__CLASS__, 'get_listing']);
        add_action('wp_ajax_hp_delete_listing', [__CLASS__, 'delete_listing']);
        add_action('wp_ajax_hp_update_listing_field', [__CLASS__, 'update_field']);
        add_action('wp_ajax_hp_dashboard_load_section', [__CLASS__, 'load_dashboard_section']);
        
        // Public AJAX actions (if needed)
        add_action('wp_ajax_nopriv_hp_get_listing_public', [__CLASS__, 'get_listing_public']);
        
        hp_log('Listing AJAX handlers initialized', 'info', 'LISTING_AJAX');
    }
    
    /**
     * Save listing via AJAX (create or update)
     */
    public static function save_listing() {
        // Verify nonce
        $nonce = $_POST['nonce'] ?? $_POST['_ajax_nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'hp_dashboard_nonce') && !wp_verify_nonce($nonce, 'hp_listing_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'You do not have permission to edit listings']);
            return;
        }
        
        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        
        // Prepare post data
        $post_data = [
            'post_type' => 'listing',
            'post_status' => $_POST['post_status'] ?? 'publish',
            'post_title' => sanitize_text_field($_POST['title'] ?? ''),
            'post_content' => wp_kses_post($_POST['description'] ?? ''),
        ];
        
        // Update or insert
        if ($listing_id > 0) {
            $post_data['ID'] = $listing_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
            return;
        }
        
        $listing_id = $result;
        
        // Update ACF fields
        if (function_exists('update_field')) {
            // Price and basic info
            if (isset($_POST['price'])) {
                update_field('listing_price', floatval($_POST['price']), $listing_id);
            }
            if (isset($_POST['bedrooms'])) {
                update_field('bedrooms', intval($_POST['bedrooms']), $listing_id);
            }
            if (isset($_POST['bathrooms'])) {
                // Split bathrooms into full and half
                $bathrooms = floatval($_POST['bathrooms']);
                $full = floor($bathrooms);
                $half = ($bathrooms - $full) > 0 ? 1 : 0;
                update_field('bathrooms_full', $full, $listing_id);
                update_field('bathrooms_half', $half, $listing_id);
            }
            if (isset($_POST['sqft'])) {
                update_field('square_feet', intval($_POST['sqft']), $listing_id);
            }
            
            // Address fields
            if (isset($_POST['address'])) {
                update_field('street_address', sanitize_text_field($_POST['address']), $listing_id);
            }
            if (isset($_POST['city'])) {
                update_field('city', sanitize_text_field($_POST['city']), $listing_id);
            }
            if (isset($_POST['state'])) {
                update_field('state', sanitize_text_field($_POST['state']), $listing_id);
            }
            if (isset($_POST['zip'])) {
                update_field('zip_code', sanitize_text_field($_POST['zip']), $listing_id);
            }
            
            // Description
            if (isset($_POST['description'])) {
                update_field('property_description', wp_kses_post($_POST['description']), $listing_id);
            }
            
            // Status
            if (isset($_POST['status'])) {
                update_post_meta($listing_id, '_listing_status', sanitize_text_field($_POST['status']));
            }
        }
        
        // Sync fields using Field Mapper
        if (class_exists('HappyPlace\\Core\\Field_Mapper')) {
            \HappyPlace\Core\Field_Mapper::sync_fields($listing_id);
        }
        
        // Clear caches
        wp_cache_delete($listing_id, 'posts');
        wp_cache_delete($listing_id, 'post_meta');
        
        wp_send_json_success([
            'message' => $listing_id ? 'Listing updated successfully' : 'Listing created successfully',
            'listing_id' => $listing_id,
            'redirect' => admin_url('post.php?post=' . $listing_id . '&action=edit')
        ]);
    }
    
    /**
     * Get listing data via AJAX
     */
    public static function get_listing() {
        $listing_id = intval($_POST['listing_id'] ?? 0);
        
        if (!$listing_id) {
            wp_send_json_error(['message' => 'Invalid listing ID']);
            return;
        }
        
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            wp_send_json_error(['message' => 'Listing not found']);
            return;
        }
        
        // Check permissions for private listings
        if ($listing->post_status === 'private' && !current_user_can('read_private_posts')) {
            wp_send_json_error(['message' => 'You do not have permission to view this listing']);
            return;
        }
        
        // Get all ACF fields
        $data = [
            'listing_id' => $listing_id,
            'title' => $listing->post_title,
            'content' => $listing->post_content,
            'status' => get_post_meta($listing_id, '_listing_status', true) ?: 'active',
            'featured' => get_post_meta($listing_id, '_featured_listing', true) === '1',
        ];
        
        // Get ACF fields if available
        if (function_exists('get_field')) {
            $data['price'] = get_field('listing_price', $listing_id);
            $data['bedrooms'] = get_field('bedrooms', $listing_id);
            $data['bathrooms_full'] = get_field('bathrooms_full', $listing_id);
            $data['bathrooms_half'] = get_field('bathrooms_half', $listing_id);
            $data['bathrooms'] = floatval($data['bathrooms_full']) + (floatval($data['bathrooms_half']) * 0.5);
            $data['sqft'] = get_field('square_feet', $listing_id);
            $data['address'] = get_field('street_address', $listing_id);
            $data['city'] = get_field('city', $listing_id);
            $data['state'] = get_field('state', $listing_id);
            $data['zip'] = get_field('zip_code', $listing_id);
            $data['description'] = get_field('property_description', $listing_id);
            $data['year_built'] = get_field('year_built', $listing_id);
            $data['lot_size'] = get_field('lot_size_acres', $listing_id);
            $data['garage_spaces'] = get_field('garage_spaces', $listing_id);
            $data['mls_number'] = get_field('mls_number', $listing_id);
            
            // Get features
            $data['interior_features'] = get_field('interior_features', $listing_id);
            $data['exterior_features'] = get_field('exterior_features', $listing_id);
            $data['property_features'] = get_field('property_features', $listing_id);
            
            // Get media
            $data['gallery'] = get_field('photo_gallery', $listing_id);
            $data['virtual_tour'] = get_field('virtual_tour_url', $listing_id);
            $data['video'] = get_field('video_url', $listing_id);
        }
        
        // Get featured image
        if (has_post_thumbnail($listing_id)) {
            $data['featured_image'] = get_the_post_thumbnail_url($listing_id, 'large');
        }
        
        wp_send_json_success($data);
    }
    
    /**
     * Delete listing via AJAX
     */
    public static function delete_listing() {
        // Verify nonce
        $nonce = $_POST['nonce'] ?? $_POST['_ajax_nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'hp_dashboard_nonce') && !wp_verify_nonce($nonce, 'hp_listing_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        
        if (!$listing_id) {
            wp_send_json_error(['message' => 'Invalid listing ID']);
            return;
        }
        
        // Check permissions
        if (!current_user_can('delete_post', $listing_id)) {
            wp_send_json_error(['message' => 'You do not have permission to delete this listing']);
            return;
        }
        
        // Move to trash (safer than permanent delete)
        $result = wp_trash_post($listing_id);
        
        if ($result) {
            wp_send_json_success([
                'message' => 'Listing moved to trash',
                'listing_id' => $listing_id
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to delete listing']);
        }
    }
    
    /**
     * Update single field via AJAX
     */
    public static function update_field() {
        // Verify nonce
        $nonce = $_POST['nonce'] ?? $_POST['_ajax_nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'hp_dashboard_nonce') && !wp_verify_nonce($nonce, 'hp_listing_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        $field_name = sanitize_text_field($_POST['field_name'] ?? '');
        $field_value = $_POST['field_value'] ?? '';
        
        if (!$listing_id || !$field_name) {
            wp_send_json_error(['message' => 'Invalid request']);
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $listing_id)) {
            wp_send_json_error(['message' => 'You do not have permission to edit this listing']);
            return;
        }
        
        // Update field using Field Mapper
        if (class_exists('HappyPlace\\Core\\Field_Mapper')) {
            $result = \HappyPlace\Core\Field_Mapper::update_system_field($listing_id, $field_name, $field_value);
            
            if ($result) {
                // Sync all fields
                \HappyPlace\Core\Field_Mapper::sync_fields($listing_id);
                
                wp_send_json_success([
                    'message' => 'Field updated successfully',
                    'field' => $field_name,
                    'value' => $field_value
                ]);
            } else {
                wp_send_json_error(['message' => 'Failed to update field']);
            }
        } else {
            wp_send_json_error(['message' => 'Field Mapper not available']);
        }
    }
    
    /**
     * Load dashboard section via AJAX
     */
    public static function load_dashboard_section() {
        $section = sanitize_text_field($_POST['section'] ?? 'overview');
        
        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Access denied']);
            return;
        }
        
        ob_start();
        
        // Load section template (these would be in your theme)
        $template_file = locate_template("dashboard/sections/{$section}.php");
        
        if ($template_file) {
            include $template_file;
        } else {
            echo '<div class="dashboard-section">';
            echo '<h2>' . ucfirst($section) . '</h2>';
            echo '<p>Section content goes here.</p>';
            echo '</div>';
        }
        
        $html = ob_get_clean();
        
        wp_send_json_success([
            'html' => $html,
            'section' => $section
        ]);
    }
    
    /**
     * Get listing data for public users
     */
    public static function get_listing_public() {
        $listing_id = intval($_POST['listing_id'] ?? 0);
        
        if (!$listing_id) {
            wp_send_json_error(['message' => 'Invalid listing ID']);
            return;
        }
        
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing' || $listing->post_status !== 'publish') {
            wp_send_json_error(['message' => 'Listing not found']);
            return;
        }
        
        // Return limited public data
        $data = [
            'title' => $listing->post_title,
            'price' => get_field('listing_price', $listing_id),
            'bedrooms' => get_field('bedrooms', $listing_id),
            'bathrooms' => get_post_meta($listing_id, '_calculated_bathrooms', true),
            'sqft' => get_field('square_feet', $listing_id),
            'city' => get_field('city', $listing_id),
            'state' => get_field('state', $listing_id),
            'featured_image' => get_the_post_thumbnail_url($listing_id, 'large')
        ];
        
        wp_send_json_success($data);
    }
}