// File: /includes/admin/class-hp-listing-geocoder.php

<?php
class HP_Listing_Geocoder {
    
    private $google_api_key;
    
    public function __construct() {
        $this->google_api_key = get_option('hp_google_maps_api_key', '');
        
        // Hook into ACF save
        add_action('acf/save_post', [$this, 'geocode_on_save'], 20);
        
        // AJAX handlers
        add_action('wp_ajax_hp_geocode_address', [$this, 'ajax_geocode_address']);
        add_action('wp_ajax_hp_convert_lot_size', [$this, 'ajax_convert_lot_size']);
        
        // Admin scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    /**
     * Geocode address when listing is saved
     */
    public function geocode_on_save($post_id) {
        // Only for listings
        if (get_post_type($post_id) !== 'listing') {
            return;
        }
        
        // Build address from components
        $address_parts = [
            get_field('street_number', $post_id),
            get_field('street_dir_prefix', $post_id),
            get_field('street_name', $post_id),
            get_field('street_type', $post_id),
            get_field('street_dir_suffix', $post_id)
        ];
        
        // Filter out empty parts and build street address
        $street = implode(' ', array_filter($address_parts));
        
        // Add unit if exists
        $unit = get_field('unit_number', $post_id);
        if ($unit) {
            $street .= ' ' . $unit;
        }
        
        // Full address for geocoding
        $full_address = sprintf(
            '%s, %s, %s %s',
            $street,
            get_field('city', $post_id),
            get_field('state', $post_id),
            get_field('zip_code', $post_id)
        );
        
        // Clean up spacing
        $full_address = preg_replace('/\s+/', ' ', trim($full_address));
        
        // Save assembled address
        update_field('full_address', $full_address, $post_id);
        update_field('street_address', $street, $post_id);
        
        // Geocode if we have an API key
        if ($this->google_api_key) {
            $this->geocode_address($full_address, $post_id);
        }
        
        // Convert lot sizes if needed
        $this->sync_lot_sizes($post_id);
        
        // Log if debug mode
        if (HP_DEBUG) {
            error_log('HP Geocoder: Processed listing #' . $post_id . ' - ' . $full_address);
        }
    }
    
    /**
     * Call Google Geocoding API
     */
    private function geocode_address($address, $post_id) {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json';
        $params = [
            'address' => $address,
            'key' => $this->google_api_key
        ];
        
        $response = wp_remote_get(add_query_arg($params, $url));
        
        if (is_wp_error($response)) {
            if (HP_DEBUG) {
                error_log('HP Geocoder Error: ' . $response->get_error_message());
            }
            return false;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($data['status'] === 'OK' && !empty($data['results'][0])) {
            $result = $data['results'][0];
            
            // Save coordinates
            update_field('latitude', $result['geometry']['location']['lat'], $post_id);
            update_field('longitude', $result['geometry']['location']['lng'], $post_id);
            
            // Extract additional components
            foreach ($result['address_components'] as $component) {
                $types = $component['types'];
                
                if (in_array('administrative_area_level_2', $types)) {
                    update_field('county', $component['long_name'], $post_id);
                }
                
                if (in_array('neighborhood', $types)) {
                    update_field('neighborhood', $component['long_name'], $post_id);
                }
            }
            
            // Store geocoding timestamp
            update_post_meta($post_id, '_hp_geocoded_at', current_time('mysql'));
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Sync lot size between acres and square feet
     */
    private function sync_lot_sizes($post_id) {
        $acres = get_field('lot_size_acres', $post_id);
        $sqft = get_field('lot_size_sqft', $post_id);
        
        // If acres is set but not sqft, calculate sqft
        if ($acres && !$sqft) {
            $calculated_sqft = $acres * 43560;
            update_field('lot_size_sqft', $calculated_sqft, $post_id);
        }
        // If sqft is set but not acres, calculate acres
        elseif ($sqft && !$acres) {
            $calculated_acres = round($sqft / 43560, 2);
            update_field('lot_size_acres', $calculated_acres, $post_id);
        }
    }
    
    /**
     * AJAX handler for manual geocoding
     */
    public function ajax_geocode_address() {
        check_ajax_referer('hp_admin_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        
        if (!current_user_can('edit_post', $post_id)) {
            wp_die('Unauthorized');
        }
        
        // Trigger geocoding
        $this->geocode_on_save($post_id);
        
        wp_send_json_success([
            'message' => 'Address geocoded successfully',
            'latitude' => get_field('latitude', $post_id),
            'longitude' => get_field('longitude', $post_id),
            'county' => get_field('county', $post_id)
        ]);
    }
    
    /**
     * AJAX handler for lot size conversion
     */
    public function ajax_convert_lot_size() {
        check_ajax_referer('hp_admin_nonce', 'nonce');
        
        $from = sanitize_text_field($_POST['from']);
        $value = floatval($_POST['value']);
        
        if ($from === 'acres') {
            $result = $value * 43560;
            $formatted = number_format($result) . ' sq ft';
        } else {
            $result = round($value / 43560, 2);
            $formatted = $result . ' acres';
        }
        
        wp_send_json_success([
            'value' => $result,
            'formatted' => $formatted
        ]);
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        global $post;
        
        if (!$post || $post->post_type !== 'listing') {
            return;
        }
        
        wp_enqueue_script(
            'hp-listing-admin',
            HP_ASSETS_URL . 'js/admin/listing-admin.js',
            ['jquery', 'acf-input'],
            HP_VERSION,
            true
        );
        
        wp_localize_script('hp-listing-admin', 'hpAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_admin_nonce'),
            'postId' => $post->ID,
            'googleMapsKey' => $this->google_api_key,
            'debug' => HP_DEBUG
        ]);
        
        // Also enqueue admin styles
        wp_enqueue_style(
            'hp-listing-admin',
            HP_ASSETS_URL . 'css/admin/listing-admin.css',
            [],
            HP_VERSION
        );
    }
}

// Initialize only if ACF is active
add_action('init', function() {
    if (class_exists('ACF')) {
        new HP_Listing_Geocoder();
    }
});