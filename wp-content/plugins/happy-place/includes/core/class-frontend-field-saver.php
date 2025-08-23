<?php
/**
 * Frontend Field Saver
 * 
 * Custom save field functions for frontend forms with comprehensive validation
 * and integration with both ACF and WordPress post meta.
 * 
 * @package HappyPlace
 * @version 3.0.0
 */

namespace HappyPlace\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Field_Saver {

    private static $instance = null;
    private $supported_post_types = ['listing', 'agent', 'community', 'city', 'open_house', 'local_place', 'team', 'transaction'];
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Constructor
    }

    /**
     * Save listing fields from frontend form
     * 
     * @param int $post_id Listing post ID
     * @param array $field_data Field data from form
     * @param array $options Save options
     * @return array Result with success/error status
     */
    public function save_listing_fields($post_id, $field_data, $options = []) {
        try {
            // Validate post
            if (!$this->validate_post($post_id, 'listing')) {
                return $this->error_response('Invalid listing post');
            }

            // Check permissions
            if (!$this->check_permissions($post_id, $options)) {
                return $this->error_response('Insufficient permissions');
            }

            hp_log("Frontend field save started for listing {$post_id}", 'info', 'FRONTEND_SAVER');

            $saved_fields = [];
            $errors = [];

            // Process different field types
            foreach ($field_data as $field_name => $field_value) {
                $result = $this->save_single_field($post_id, $field_name, $field_value, $options);
                
                if ($result['success']) {
                    $saved_fields[] = $field_name;
                } else {
                    $errors[$field_name] = $result['error'];
                }
            }

            // Post-save processing
            if (!empty($saved_fields)) {
                $this->post_save_processing($post_id, $saved_fields, $options);
            }

            $response = [
                'success' => empty($errors),
                'saved_fields' => $saved_fields,
                'errors' => $errors,
                'post_id' => $post_id
            ];

            hp_log("Frontend field save completed for listing {$post_id}. Saved: " . count($saved_fields) . ", Errors: " . count($errors), 'info', 'FRONTEND_SAVER');

            return $response;

        } catch (\Exception $e) {
            hp_log("Frontend field save error for listing {$post_id}: " . $e->getMessage(), 'error', 'FRONTEND_SAVER');
            return $this->error_response('Save failed: ' . $e->getMessage());
        }
    }

    /**
     * Save a single field with appropriate method
     */
    private function save_single_field($post_id, $field_name, $field_value, $options = []) {
        try {
            // Determine field type and save method
            $field_config = $this->get_field_config($field_name);
            
            if ($field_config['is_acf']) {
                return $this->save_acf_field($post_id, $field_name, $field_value, $field_config);
            } else {
                return $this->save_meta_field($post_id, $field_name, $field_value, $field_config);
            }
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Save ACF field
     */
    private function save_acf_field($post_id, $field_name, $field_value, $field_config) {
        if (!function_exists('update_field')) {
            return ['success' => false, 'error' => 'ACF not available'];
        }

        // Validate field value
        $validated_value = $this->validate_field_value($field_name, $field_value, $field_config);
        if ($validated_value === false) {
            return ['success' => false, 'error' => 'Invalid field value'];
        }

        // Save using ACF
        $result = update_field($field_name, $validated_value, $post_id);
        
        if ($result) {
            hp_log("ACF field '{$field_name}' saved for post {$post_id}", 'debug', 'FRONTEND_SAVER');
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'ACF save failed'];
        }
    }

    /**
     * Save WordPress post meta field
     */
    private function save_meta_field($post_id, $field_name, $field_value, $field_config) {
        // Validate field value
        $validated_value = $this->validate_field_value($field_name, $field_value, $field_config);
        if ($validated_value === false) {
            return ['success' => false, 'error' => 'Invalid field value'];
        }

        // Add meta key prefix if needed
        $meta_key = $field_config['meta_key'] ?? '_' . $field_name;
        
        $result = update_post_meta($post_id, $meta_key, $validated_value);
        
        hp_log("Meta field '{$meta_key}' saved for post {$post_id}", 'debug', 'FRONTEND_SAVER');
        return ['success' => true];
    }

    /**
     * Get field configuration
     */
    private function get_field_config($field_name) {
        // Define field configurations
        $field_configs = [
            // ACF Fields
            'listing_price' => ['is_acf' => true, 'type' => 'number', 'min' => 0],
            'bedrooms' => ['is_acf' => true, 'type' => 'number', 'min' => 0, 'max' => 20],
            'full_bathrooms' => ['is_acf' => true, 'type' => 'number', 'min' => 0, 'max' => 20],
            'half_bathrooms' => ['is_acf' => true, 'type' => 'number', 'min' => 0, 'max' => 10],
            'square_feet' => ['is_acf' => true, 'type' => 'number', 'min' => 0],
            'lot_size_acres' => ['is_acf' => true, 'type' => 'number', 'min' => 0],
            'year_built' => ['is_acf' => true, 'type' => 'number', 'min' => 1800, 'max' => 2030],
            'mls_number' => ['is_acf' => true, 'type' => 'text'],
            'street_address' => ['is_acf' => true, 'type' => 'text'],
            'city' => ['is_acf' => true, 'type' => 'text'],
            'state' => ['is_acf' => true, 'type' => 'text'],
            'zip_code' => ['is_acf' => true, 'type' => 'text'],
            'property_type' => ['is_acf' => true, 'type' => 'text'],
            'listing_status' => ['is_acf' => true, 'type' => 'text'],
            'short_description' => ['is_acf' => true, 'type' => 'textarea'],
            'interior_features' => ['is_acf' => true, 'type' => 'array'],
            'exterior_features' => ['is_acf' => true, 'type' => 'array'],
            'property_gallery' => ['is_acf' => true, 'type' => 'array'],
            'virtual_tour_url' => ['is_acf' => true, 'type' => 'url'],
            'video_url' => ['is_acf' => true, 'type' => 'url'],
            
            // Custom Meta Fields (non-ACF)
            'listing_status_meta' => ['is_acf' => false, 'type' => 'text', 'meta_key' => '_listing_status'],
            'featured_listing' => ['is_acf' => false, 'type' => 'boolean', 'meta_key' => '_featured_listing'],
            'listing_views' => ['is_acf' => false, 'type' => 'number', 'meta_key' => '_listing_views'],
            'favorite_count' => ['is_acf' => false, 'type' => 'number', 'meta_key' => '_favorite_count'],
        ];

        // Return config or default
        return $field_configs[$field_name] ?? ['is_acf' => true, 'type' => 'text'];
    }

    /**
     * Validate field value based on type
     */
    private function validate_field_value($field_name, $field_value, $field_config) {
        $type = $field_config['type'] ?? 'text';

        switch ($type) {
            case 'number':
                if (!is_numeric($field_value)) {
                    return false;
                }
                $value = floatval($field_value);
                
                // Check min/max
                if (isset($field_config['min']) && $value < $field_config['min']) {
                    return false;
                }
                if (isset($field_config['max']) && $value > $field_config['max']) {
                    return false;
                }
                return $value;

            case 'text':
                return sanitize_text_field($field_value);

            case 'textarea':
                return sanitize_textarea_field($field_value);

            case 'url':
                $url = esc_url_raw($field_value);
                return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;

            case 'email':
                return sanitize_email($field_value);

            case 'boolean':
                return $field_value ? '1' : '0';

            case 'array':
                if (!is_array($field_value)) {
                    return false;
                }
                return array_map('sanitize_text_field', $field_value);

            default:
                return sanitize_text_field($field_value);
        }
    }

    /**
     * Validate post and type
     */
    private function validate_post($post_id, $expected_type = null) {
        $post = get_post($post_id);
        
        if (!$post) {
            return false;
        }

        if ($expected_type && $post->post_type !== $expected_type) {
            return false;
        }

        if (!in_array($post->post_type, $this->supported_post_types)) {
            return false;
        }

        return true;
    }

    /**
     * Check permissions
     */
    private function check_permissions($post_id, $options = []) {
        // Skip permission check if explicitly disabled
        if (isset($options['skip_permissions']) && $options['skip_permissions']) {
            return true;
        }

        return current_user_can('edit_post', $post_id);
    }

    /**
     * Post-save processing
     */
    private function post_save_processing($post_id, $saved_fields, $options = []) {
        // Update modified date
        update_post_meta($post_id, '_hp_last_frontend_save', current_time('mysql'));
        
        // Trigger address intelligence if address fields were updated
        $address_fields = ['street_address', 'city', 'state', 'zip_code'];
        if (array_intersect($address_fields, $saved_fields)) {
            do_action('hp_address_fields_updated', $post_id);
        }

        // Clear caches
        if (function_exists('wp_cache_delete')) {
            wp_cache_delete($post_id, 'posts');
        }

        hp_log("Post-save processing completed for post {$post_id}", 'debug', 'FRONTEND_SAVER');
    }

    /**
     * Create error response
     */
    private function error_response($message) {
        return [
            'success' => false,
            'error' => $message,
            'saved_fields' => [],
            'errors' => []
        ];
    }

    /**
     * Bulk save multiple listings
     */
    public function bulk_save_listings($listings_data, $options = []) {
        $results = [];
        
        foreach ($listings_data as $post_id => $field_data) {
            $results[$post_id] = $this->save_listing_fields($post_id, $field_data, $options);
        }
        
        return $results;
    }

    /**
     * Save listing from AJAX request
     */
    public function ajax_save_listing() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'frontend_save_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        $post_id = intval($_POST['post_id'] ?? 0);
        $field_data = $_POST['fields'] ?? [];
        $options = $_POST['options'] ?? [];

        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }

        $result = $this->save_listing_fields($post_id, $field_data, $options);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
}

// Initialize the class
add_action('init', function() {
    $frontend_saver = Frontend_Field_Saver::get_instance();
    
    // Register AJAX handlers
    add_action('wp_ajax_frontend_save_listing', [$frontend_saver, 'ajax_save_listing']);
    add_action('wp_ajax_nopriv_frontend_save_listing', [$frontend_saver, 'ajax_save_listing']);
});
