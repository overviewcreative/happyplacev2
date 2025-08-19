<?php
/**
 * Address Intelligence Bridge
 * 
 * Handles auto-population of location intelligence data from address components
 * Uses Google Maps API, Walk Score API, and GreatSchools API
 */

if (!defined('ABSPATH')) {
    exit;
}

class HPH_Address_Intelligence_Bridge {
    
    private $google_maps_api_key;
    private $walk_score_api_key;
    private $greatschools_api_key;
    
    public function __construct() {
        // Get API keys from settings (will create settings page later)
        $this->google_maps_api_key = get_option('hph_google_maps_api_key', '');
        $this->walk_score_api_key = get_option('hph_walk_score_api_key', '');
        $this->greatschools_api_key = get_option('hph_greatschools_api_key', '');
        
        // Hook into ACF save
        add_action('acf/save_post', array($this, 'on_listing_save'), 20);
        
        // AJAX hooks for manual refresh
        add_action('wp_ajax_hph_geocode_address', array($this, 'ajax_geocode_address'));
        add_action('wp_ajax_hph_refresh_location_data', array($this, 'ajax_refresh_location_data'));
        add_action('wp_ajax_hph_refresh_school_data', array($this, 'ajax_refresh_school_data'));
        add_action('wp_ajax_hph_refresh_walkability_data', array($this, 'ajax_refresh_walkability_data'));
        add_action('wp_ajax_hph_refresh_amenities', array($this, 'ajax_refresh_amenities'));
    }
    
    /**
     * Auto-populate data when listing is saved
     */
    public function on_listing_save($post_id) {
        // Only process listing post type
        if (get_post_type($post_id) !== 'listing') {
            return;
        }
        
        // Check if address components have changed
        if ($this->should_refresh_data($post_id)) {
            $this->populate_all_location_data($post_id);
        }
    }
    
    /**
     * Check if we should refresh location data
     */
    private function should_refresh_data($post_id) {
        // Get current address components
        $current_address = $this->build_address_from_components($post_id);
        
        // Get last processed address
        $last_address = get_field('last_processed_address', $post_id);
        
        // Refresh if address has changed or never processed
        return ($current_address !== $last_address);
    }
    
    /**
     * Build full address string from components
     */
    public function build_address_from_components($post_id) {
        $components = array();
        
        // Street number
        $street_number = get_field('street_number', $post_id);
        if ($street_number) {
            $components[] = $street_number;
        }
        
        // Pre-direction
        $pre_direction = get_field('street_dir_prefix', $post_id);
        if ($pre_direction) {
            $components[] = $pre_direction;
        }
        
        // Street name
        $street_name = get_field('street_name', $post_id);
        if ($street_name) {
            $components[] = $street_name;
        }
        
        // Street type
        $street_suffix = get_field('street_suffix', $post_id);
        if ($street_suffix) {
            $components[] = $street_suffix;
        }
        
        // Post-direction  
        $post_direction = get_field('street_dir_suffix', $post_id);
        if ($post_direction) {
            $components[] = $post_direction;
        }
        
        // Unit number
        $unit_number = get_field('unit_number', $post_id);
        if ($unit_number) {
            $components[] = $unit_number;
        }
        
        // Build street address
        $street_address = implode(' ', $components);
        
        // Add city, state, zip
        $city = get_field('city', $post_id);
        $state = get_field('state', $post_id);
        $zip = get_field('zip_code', $post_id);
        
        $full_address = $street_address;
        
        if ($city) {
            $full_address .= ', ' . $city;
        }
        
        if ($state) {
            $full_address .= ', ' . $state;
        }
        
        if ($zip) {
            $full_address .= ' ' . $zip;
        }
        
        return $full_address;
    }
    
    /**
     * Populate all location intelligence data
     */
    public function populate_all_location_data($post_id) {
        $full_address = $this->build_address_from_components($post_id);
        
        if (empty($full_address)) {
            return false;
        }
        
        // Update the formatted address field
        update_field('full_address', $full_address, $post_id);
        
        // Geocode the address
        $geocoding_result = $this->geocode_address($full_address);
        
        if ($geocoding_result) {
            // Save coordinates
            update_field('latitude', $geocoding_result['lat'], $post_id);
            update_field('longitude', $geocoding_result['lng'], $post_id);
            update_field('google_place_id', $geocoding_result['place_id'], $post_id);
            update_field('geocoding_accuracy', $geocoding_result['accuracy'], $post_id);
            
            // Save enhanced address components from Google
            if (isset($geocoding_result['address_components'])) {
                $this->update_address_components($post_id, $geocoding_result['address_components']);
            }
            
            // Get walkability scores
            $this->populate_walkability_data($post_id, $geocoding_result['lat'], $geocoding_result['lng']);
            
            // Get school data
            $this->populate_school_data($post_id, $geocoding_result['lat'], $geocoding_result['lng']);
            
            // Get nearby amenities
            $this->populate_amenities_data($post_id, $geocoding_result['lat'], $geocoding_result['lng']);
        }
        
        // Update timestamps
        update_field('location_intelligence_last_updated', current_time('mysql'), $post_id);
        update_field('last_processed_address', $full_address, $post_id);
        update_field('location_api_status', 'success', $post_id);
        
        return true;
    }
    
    /**
     * Geocode address using Google Maps API
     */
    private function geocode_address($address) {
        if (empty($this->google_maps_api_key)) {
            return false;
        }
        
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query(array(
            'address' => $address,
            'key' => $this->google_maps_api_key
        ));
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data['results'])) {
            return false;
        }
        
        $result = $data['results'][0];
        
        return array(
            'lat' => $result['geometry']['location']['lat'],
            'lng' => $result['geometry']['location']['lng'],
            'place_id' => $result['place_id'],
            'accuracy' => $result['geometry']['location_type'],
            'address_components' => $result['address_components'],
            'formatted_address' => $result['formatted_address']
        );
    }
    
    /**
     * Update address components from Google geocoding result
     */
    private function update_address_components($post_id, $components) {
        foreach ($components as $component) {
            $types = $component['types'];
            
            if (in_array('administrative_area_level_2', $types)) {
                // County
                update_field('county', $component['long_name'], $post_id);
            } elseif (in_array('neighborhood', $types) || in_array('sublocality', $types)) {
                // Neighborhood
                update_field('neighborhood', $component['long_name'], $post_id);
            }
        }
    }
    
    /**
     * Get walkability scores from Walk Score API
     */
    private function populate_walkability_data($post_id, $lat, $lng) {
        if (empty($this->walk_score_api_key)) {
            return false;
        }
        
        $address = get_field('full_address', $post_id);
        
        $url = 'https://api.walkscore.com/score?' . http_build_query(array(
            'format' => 'json',
            'address' => $address,
            'lat' => $lat,
            'lon' => $lng,
            'transit' => 1,
            'bike' => 1,
            'wsapikey' => $this->walk_score_api_key
        ));
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['walkscore'])) {
            update_field('walkability_scores_walk_score', $data['walkscore'], $post_id);
        }
        
        if (isset($data['transit']['score'])) {
            update_field('walkability_scores_transit_score', $data['transit']['score'], $post_id);
        }
        
        if (isset($data['bike']['score'])) {
            update_field('walkability_scores_bike_score', $data['bike']['score'], $post_id);
        }
        
        return true;
    }
    
    /**
     * Get school district and assigned schools
     */
    private function populate_school_data($post_id, $lat, $lng) {
        if (empty($this->greatschools_api_key)) {
            return false;
        }
        
        // Get school district
        $district_url = 'https://api.greatschools.org/v1/districts/nearby?' . http_build_query(array(
            'key' => $this->greatschools_api_key,
            'lat' => $lat,
            'lon' => $lng,
            'radius' => 5
        ));
        
        $response = wp_remote_get($district_url);
        
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $xml = simplexml_load_string($body);
            
            if ($xml && isset($xml->district[0])) {
                $district_name = (string) $xml->district[0]->districtName;
                update_field('school_district', $district_name, $post_id);
            }
        }
        
        // Get nearby schools by level
        $this->get_schools_by_level($post_id, $lat, $lng, 'elementary-schools', 'elementary_school');
        $this->get_schools_by_level($post_id, $lat, $lng, 'middle-schools', 'middle_school');  
        $this->get_schools_by_level($post_id, $lat, $lng, 'high-schools', 'high_school');
        
        return true;
    }
    
    /**
     * Get schools by level (elementary, middle, high)
     */
    private function get_schools_by_level($post_id, $lat, $lng, $level, $field_name) {
        $url = "https://api.greatschools.org/v1/schools/nearby?key={$this->greatschools_api_key}&lat={$lat}&lon={$lng}&schoolType=public&levelCode={$level}&radius=3&limit=1";
        
        $response = wp_remote_get($url);
        
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $xml = simplexml_load_string($body);
            
            if ($xml && isset($xml->school[0])) {
                $school_name = (string) $xml->school[0]->schoolName;
                update_field("assigned_schools_{$field_name}", $school_name, $post_id);
            }
        }
    }
    
    /**
     * Get nearby amenities count using Google Places API
     */
    private function populate_amenities_data($post_id, $lat, $lng) {
        if (empty($this->google_maps_api_key)) {
            return false;
        }
        
        $radius = 3218; // 2 miles in meters
        
        // Define amenity types to search for
        $amenity_types = array(
            'restaurants_count' => 'restaurant',
            'shopping_count' => 'shopping_mall|store',
            'healthcare_count' => 'hospital|pharmacy|doctor',
            'entertainment_count' => 'movie_theater|amusement_park|night_club'
        );
        
        foreach ($amenity_types as $field => $types) {
            $count = $this->get_places_count($lat, $lng, $types, $radius);
            update_field("nearby_amenities_summary_{$field}", $count, $post_id);
        }
        
        return true;
    }
    
    /**
     * Get count of places by type using Google Places API
     */
    private function get_places_count($lat, $lng, $types, $radius) {
        $url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?' . http_build_query(array(
            'location' => "{$lat},{$lng}",
            'radius' => $radius,
            'type' => $types,
            'key' => $this->google_maps_api_key
        ));
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return 0;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return isset($data['results']) ? count($data['results']) : 0;
    }
    
    /**
     * AJAX handler for manual geocoding
     */
    public function ajax_geocode_address() {
        check_ajax_referer('hph_address_intelligence', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        
        if (!current_user_can('edit_post', $post_id)) {
            wp_die('Unauthorized');
        }
        
        $full_address = $this->build_address_from_components($post_id);
        $result = $this->geocode_address($full_address);
        
        if ($result) {
            update_field('latitude', $result['lat'], $post_id);
            update_field('longitude', $result['lng'], $post_id);
            update_field('google_place_id', $result['place_id'], $post_id);
            update_field('geocoding_accuracy', $result['accuracy'], $post_id);
            update_field('full_address', $full_address, $post_id);
            
            wp_send_json_success(array(
                'message' => 'Address geocoded successfully',
                'data' => $result
            ));
        } else {
            wp_send_json_error('Failed to geocode address');
        }
    }
    
    /**
     * AJAX handler for refreshing all location data
     */
    public function ajax_refresh_location_data() {
        check_ajax_referer('hph_address_intelligence', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        
        if (!current_user_can('edit_post', $post_id)) {
            wp_die('Unauthorized');
        }
        
        $success = $this->populate_all_location_data($post_id);
        
        if ($success) {
            wp_send_json_success('All location data refreshed successfully');
        } else {
            wp_send_json_error('Failed to refresh location data');
        }
    }
    
    /**
     * AJAX handler for refreshing school data
     */
    public function ajax_refresh_school_data() {
        check_ajax_referer('hph_address_intelligence', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        
        if (!current_user_can('edit_post', $post_id)) {
            wp_die('Unauthorized');
        }
        
        $lat = get_field('latitude', $post_id);
        $lng = get_field('longitude', $post_id);
        
        if ($lat && $lng) {
            $success = $this->populate_school_data($post_id, $lat, $lng);
            
            if ($success) {
                wp_send_json_success('School data refreshed successfully');
            } else {
                wp_send_json_error('Failed to refresh school data');
            }
        } else {
            wp_send_json_error('No coordinates available. Please geocode address first.');
        }
    }
    
    /**
     * AJAX handler for refreshing walkability data
     */
    public function ajax_refresh_walkability_data() {
        check_ajax_referer('hph_address_intelligence', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        
        if (!current_user_can('edit_post', $post_id)) {
            wp_die('Unauthorized');
        }
        
        $lat = get_field('latitude', $post_id);
        $lng = get_field('longitude', $post_id);
        
        if ($lat && $lng) {
            $success = $this->populate_walkability_data($post_id, $lat, $lng);
            
            if ($success) {
                wp_send_json_success('Walkability data refreshed successfully');
            } else {
                wp_send_json_error('Failed to refresh walkability data');
            }
        } else {
            wp_send_json_error('No coordinates available. Please geocode address first.');
        }
    }
    
    /**
     * AJAX handler for refreshing amenities data
     */
    public function ajax_refresh_amenities() {
        check_ajax_referer('hph_address_intelligence', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        
        if (!current_user_can('edit_post', $post_id)) {
            wp_die('Unauthorized');
        }
        
        $lat = get_field('latitude', $post_id);
        $lng = get_field('longitude', $post_id);
        
        if ($lat && $lng) {
            $success = $this->populate_amenities_data($post_id, $lat, $lng);
            
            if ($success) {
                wp_send_json_success('Amenities data refreshed successfully');
            } else {
                wp_send_json_error('Failed to refresh amenities data');
            }
        } else {
            wp_send_json_error('No coordinates available. Please geocode address first.');
        }
    }
}

// Initialize the bridge
new HPH_Address_Intelligence_Bridge();
