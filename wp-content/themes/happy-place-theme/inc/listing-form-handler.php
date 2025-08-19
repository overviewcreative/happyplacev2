<?php
/**
 * Listing Form Handler
 * Handles AJAX requests for listing form submission and validation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class HP_Listing_Form_Handler {
    
    public function __construct() {
        add_action('wp_ajax_save_listing', array($this, 'save_listing'));
        add_action('wp_ajax_load_listing_form_steps', array($this, 'load_form_steps'));
        add_action('wp_ajax_validate_listing_field', array($this, 'validate_field'));
        add_action('wp_ajax_load_listing_data', array($this, 'load_listing_data'));
    }

    /**
     * Save or update a listing
     */
    public function save_listing() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'save_listing_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'You do not have permission to create listings.'));
        }

        $listing_id = intval($_POST['listing_id']);
        $is_editing = $listing_id > 0;

        // Validate required fields
        $validation_result = $this->validate_listing_data($_POST);
        if (!$validation_result['valid']) {
            wp_send_json_error(array('message' => $validation_result['message']));
        }

        try {
            // Prepare post data
            $post_data = array(
                'post_type' => 'listing',
                'post_status' => 'publish',
                'post_title' => sanitize_text_field($_POST['post_title']),
                'post_content' => wp_kses_post($_POST['post_content']),
                'post_author' => get_current_user_id()
            );

            if ($is_editing) {
                $post_data['ID'] = $listing_id;
                $result = wp_update_post($post_data);
                $action = 'updated';
            } else {
                $result = wp_insert_post($post_data);
                $listing_id = $result;
                $action = 'created';
            }

            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            // Save ACF fields
            $this->save_listing_fields($listing_id, $_POST);

            // Handle file uploads
            $this->handle_file_uploads($listing_id, $_FILES);

            wp_send_json_success(array(
                'message' => sprintf('Listing %s successfully!', $action),
                'listing_id' => $listing_id,
                'redirect_url' => admin_url('post.php?post=' . $listing_id . '&action=edit')
            ));

        } catch (Exception $e) {
            error_log('Listing save error: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'An error occurred while saving the listing. Please try again.'));
        }
    }

    /**
     * Load form steps via AJAX
     */
    public function load_form_steps() {
        if (!wp_verify_nonce($_POST['nonce'], 'dashboard_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        ob_start();
        include get_template_directory() . '/templates/dashboard/listing-form-steps.php';
        $steps = ob_get_clean();

        wp_send_json_success(array('steps' => $steps));
    }

    /**
     * Load listing data for editing
     */
    public function load_listing_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'dashboard_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        $listing_id = intval($_POST['listing_id']);
        
        if (!$listing_id || !current_user_can('edit_post', $listing_id)) {
            wp_send_json_error(array('message' => 'Invalid listing or insufficient permissions.'));
        }

        $post = get_post($listing_id);
        if (!$post || $post->post_type !== 'listing') {
            wp_send_json_error(array('message' => 'Listing not found.'));
        }

        // Get all ACF fields
        $fields = get_fields($listing_id);
        
        // Add post data
        $data = array(
            'post_title' => $post->post_title,
            'post_content' => $post->post_content,
        );

        // Merge ACF fields
        if (is_array($fields)) {
            $data = array_merge($data, $fields);
        }

        wp_send_json_success($data);
    }

    /**
     * Validate individual field via AJAX
     */
    public function validate_field() {
        if (!wp_verify_nonce($_POST['nonce'], 'dashboard_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        $field_name = sanitize_text_field($_POST['field_name']);
        $field_value = sanitize_text_field($_POST['field_value']);

        $validation_result = $this->validate_single_field($field_name, $field_value);

        if ($validation_result['valid']) {
            wp_send_json_success($validation_result);
        } else {
            wp_send_json_error($validation_result);
        }
    }

    /**
     * Validate listing data
     */
    private function validate_listing_data($data) {
        $errors = array();

        // Required fields
        $required_fields = array(
            'post_title' => 'Property Title',
            'listing_date' => 'Listing Date',
            'price' => 'Price',
            'listing_status_taxonomy' => 'Listing Status',
            'property_type' => 'Property Type'
        );

        foreach ($required_fields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = $label . ' is required.';
            }
        }

        // Validate price
        if (!empty($data['price']) && (!is_numeric($data['price']) || floatval($data['price']) < 0)) {
            $errors[] = 'Price must be a valid number.';
        }

        // Validate year built
        if (!empty($data['year_built'])) {
            $year = intval($data['year_built']);
            if ($year < 1800 || $year > 2030) {
                $errors[] = 'Year built must be between 1800 and 2030.';
            }
        }

        // Validate URLs
        $url_fields = array('virtual_tour_url', 'video_url');
        foreach ($url_fields as $field) {
            if (!empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_URL)) {
                $errors[] = ucwords(str_replace('_', ' ', $field)) . ' must be a valid URL.';
            }
        }

        return array(
            'valid' => empty($errors),
            'message' => empty($errors) ? 'Validation passed' : implode(' ', $errors),
            'errors' => $errors
        );
    }

    /**
     * Validate single field
     */
    private function validate_single_field($field_name, $field_value) {
        switch ($field_name) {
            case 'price':
                if (!empty($field_value) && (!is_numeric($field_value) || floatval($field_value) < 0)) {
                    return array('valid' => false, 'message' => 'Price must be a valid number.');
                }
                break;
                
            case 'year_built':
                if (!empty($field_value)) {
                    $year = intval($field_value);
                    if ($year < 1800 || $year > 2030) {
                        return array('valid' => false, 'message' => 'Year built must be between 1800 and 2030.');
                    }
                }
                break;
                
            case 'virtual_tour_url':
            case 'video_url':
                if (!empty($field_value) && !filter_var($field_value, FILTER_VALIDATE_URL)) {
                    return array('valid' => false, 'message' => 'Please enter a valid URL.');
                }
                break;
        }

        return array('valid' => true, 'message' => 'Valid');
    }

    /**
     * Save listing ACF fields
     */
    private function save_listing_fields($listing_id, $data) {
        // Basic fields
        $basic_fields = array(
            'short_description', 'listing_date', 'price', 'listing_status_taxonomy',
            'featured_listing', 'mls_number', 'property_type', 'bedrooms',
            'full_bathrooms', 'half_bathrooms', 'square_feet', 'lot_size_acres',
            'year_built', 'garage_spaces'
        );

        foreach ($basic_fields as $field) {
            if (isset($data[$field])) {
                $value = $field === 'featured_listing' ? (bool) $data[$field] : sanitize_text_field($data[$field]);
                update_field($field, $value, $listing_id);
            }
        }

        // Address fields
        $address_fields = array(
            'street_number', 'street_name', 'street_suffix', 'unit_number',
            'city', 'state', 'zip_code', 'parcel_number', 'address_visibility'
        );

        foreach ($address_fields as $field) {
            if (isset($data[$field])) {
                update_field($field, sanitize_text_field($data[$field]), $listing_id);
            }
        }

        // Generate full address
        $this->generate_full_address($listing_id, $data);

        // Feature arrays
        if (isset($data['interior_features']) && is_array($data['interior_features'])) {
            $interior_features = array_map('sanitize_text_field', $data['interior_features']);
            update_field('interior_features', $interior_features, $listing_id);
        } else {
            // Clear if no features selected
            update_field('interior_features', array(), $listing_id);
        }

        if (isset($data['exterior_features']) && is_array($data['exterior_features'])) {
            $exterior_features = array_map('sanitize_text_field', $data['exterior_features']);
            update_field('exterior_features', $exterior_features, $listing_id);
        } else {
            // Clear if no features selected
            update_field('exterior_features', array(), $listing_id);
        }

        // Media fields
        $media_fields = array('virtual_tour_url', 'video_url');
        foreach ($media_fields as $field) {
            if (isset($data[$field])) {
                update_field($field, esc_url_raw($data[$field]), $listing_id);
            }
        }

        // Financial fields
        $financial_fields = array('hoa_fees', 'property_taxes', 'insurance_cost');
        foreach ($financial_fields as $field) {
            if (isset($data[$field])) {
                update_field($field, floatval($data[$field]), $listing_id);
            }
        }

        // Set taxonomy terms
        if (isset($data['listing_status_taxonomy'])) {
            wp_set_object_terms($listing_id, $data['listing_status_taxonomy'], 'property_status');
        }
    }

    /**
     * Generate full address from components
     */
    private function generate_full_address($listing_id, $data) {
        $address_parts = array();

        if (!empty($data['street_number'])) {
            $address_parts[] = $data['street_number'];
        }

        if (!empty($data['street_name'])) {
            $address_parts[] = $data['street_name'];
        }

        if (!empty($data['street_suffix'])) {
            $address_parts[] = $data['street_suffix'];
        }

        if (!empty($data['unit_number'])) {
            $address_parts[] = $data['unit_number'];
        }

        $street_address = implode(' ', $address_parts);

        $full_address_parts = array();
        if (!empty($street_address)) {
            $full_address_parts[] = $street_address;
        }

        if (!empty($data['city'])) {
            $full_address_parts[] = $data['city'];
        }

        if (!empty($data['state'])) {
            $state_zip = $data['state'];
            if (!empty($data['zip_code'])) {
                $state_zip .= ' ' . $data['zip_code'];
            }
            $full_address_parts[] = $state_zip;
        }

        $full_address = implode(', ', $full_address_parts);
        update_field('full_address', $full_address, $listing_id);
    }

    /**
     * Handle file uploads
     */
    private function handle_file_uploads($listing_id, $files) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Handle featured image
        if (!empty($files['featured_image']['name'])) {
            $featured_image_id = media_handle_upload('featured_image', $listing_id);
            if (!is_wp_error($featured_image_id)) {
                set_post_thumbnail($listing_id, $featured_image_id);
            }
        }

        // Handle gallery images
        if (!empty($files['property_gallery']['name'][0])) {
            $gallery_ids = array();
            
            foreach ($files['property_gallery']['name'] as $key => $value) {
                if ($files['property_gallery']['name'][$key]) {
                    $file = array(
                        'name' => $files['property_gallery']['name'][$key],
                        'type' => $files['property_gallery']['type'][$key],
                        'tmp_name' => $files['property_gallery']['tmp_name'][$key],
                        'error' => $files['property_gallery']['error'][$key],
                        'size' => $files['property_gallery']['size'][$key]
                    );

                    $_FILES = array('upload' => $file);
                    $attachment_id = media_handle_upload('upload', $listing_id);
                    
                    if (!is_wp_error($attachment_id)) {
                        $gallery_ids[] = $attachment_id;
                    }
                }
            }

            if (!empty($gallery_ids)) {
                update_field('property_gallery', $gallery_ids, $listing_id);
            }
        }
    }
}

// Initialize the handler
new HP_Listing_Form_Handler();