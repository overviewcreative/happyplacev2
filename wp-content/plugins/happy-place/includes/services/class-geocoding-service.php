<?php
/**
 * Geocoding Service - Automatic address geocoding and location intelligence
 * 
 * Handles automatic geocoding of listing addresses using Google Maps API
 * and populates latitude/longitude fields when address components are saved.
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Geocoding Service Class
 * 
 * Handles automatic geocoding and location data population
 */
class GeocodingService extends Service {
    
    /**
     * Service name
     */
    protected string $name = 'geocoding_service';
    
    /**
     * Service version
     */
    protected string $version = '4.0.0';
    
    /**
     * Google Maps API key
     */
    private string $google_maps_api_key = '';
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        // Get Google Maps API key from options
        $this->google_maps_api_key = get_option('hp_google_maps_api_key', '');
        
        // Hook into ACF save for listings
        add_action('acf/save_post', [$this, 'on_listing_save'], 20);
        
        // Register AJAX handlers
        add_action('wp_ajax_hp_geocode_address', [$this, 'ajax_geocode_address']);
        add_action('wp_ajax_hp_refresh_location_data', [$this, 'ajax_refresh_location_data']);
        
        $this->initialized = true;
        $this->log('Geocoding Service initialized successfully');
    }
    
    /**
     * Auto-populate geocoding data when listing is saved
     */
    public function on_listing_save($post_id): void {
        // Only process listing post type
        if (get_post_type($post_id) !== 'listing') {
            return;
        }
        
        // Check if address components have changed
        if ($this->should_refresh_geocoding($post_id)) {
            $this->geocode_listing_address($post_id);
        }
    }
    
    /**
     * Check if we should refresh geocoding data
     */
    private function should_refresh_geocoding(int $post_id): bool {
        // Get current address components
        $current_address = $this->build_address_from_components($post_id);
        
        // Get last processed address
        $last_address = get_field('last_geocoded_address', $post_id);
        
        // Refresh if address has changed or never processed
        return ($current_address !== $last_address);
    }
    
    /**
     * Build full address string from ACF components
     */
    private function build_address_from_components(int $post_id): string {
        $components = [];
        
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
        $street_type = get_field('street_type', $post_id);
        if ($street_type) {
            $components[] = $street_type;
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
     * Geocode a listing's address and update lat/lng fields
     */
    public function geocode_listing_address(int $post_id): bool {
        if (empty($this->google_maps_api_key)) {
            $this->log('Google Maps API key not configured', 'warning');
            return false;
        }
        
        $address = $this->build_address_from_components($post_id);
        if (empty($address)) {
            $this->log("No address found for listing {$post_id}", 'warning');
            return false;
        }
        
        $geocoding_result = $this->geocode_address($address);
        
        if ($geocoding_result && isset($geocoding_result['lat'], $geocoding_result['lng'])) {
            // Update latitude and longitude fields
            update_field('latitude', $geocoding_result['lat'], $post_id);
            update_field('longitude', $geocoding_result['lng'], $post_id);
            
            // Store additional data if available
            if (isset($geocoding_result['place_id'])) {
                update_field('google_place_id', $geocoding_result['place_id'], $post_id);
            }
            
            if (isset($geocoding_result['accuracy'])) {
                update_field('geocoding_accuracy', $geocoding_result['accuracy'], $post_id);
            }
            
            // Store the address we geocoded for comparison next time
            update_field('last_geocoded_address', $address, $post_id);
            
            $this->log("Successfully geocoded listing {$post_id}: {$geocoding_result['lat']}, {$geocoding_result['lng']}");
            
            return true;
        }
        
        $this->log("Failed to geocode address for listing {$post_id}: {$address}", 'error');
        return false;
    }
    
    /**
     * Geocode an address using Google Maps API
     */
    private function geocode_address(string $address): ?array {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
            'address' => $address,
            'key' => $this->google_maps_api_key,
            'region' => 'us' // Prioritize US results
        ]);
        
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'Happy Place WordPress Plugin'
            ]
        ]);
        
        if (is_wp_error($response)) {
            $this->log('Google Maps API request failed: ' . $response->get_error_message(), 'error');
            return null;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || $data['status'] !== 'OK' || empty($data['results'])) {
            $this->log('Google Maps API returned no results or error: ' . ($data['status'] ?? 'Unknown'), 'error');
            return null;
        }
        
        $result = $data['results'][0];
        $location = $result['geometry']['location'];
        
        return [
            'lat' => $location['lat'],
            'lng' => $location['lng'],
            'place_id' => $result['place_id'] ?? null,
            'accuracy' => $result['geometry']['location_type'] ?? null,
            'formatted_address' => $result['formatted_address'] ?? null,
            'address_components' => $result['address_components'] ?? null
        ];
    }
    
    /**
     * AJAX handler for manual address geocoding
     */
    public function ajax_geocode_address(): void {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hp_geocoding_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id || get_post_type($post_id) !== 'listing') {
            wp_send_json_error(['message' => 'Invalid listing ID']);
            return;
        }
        
        $success = $this->geocode_listing_address($post_id);
        
        if ($success) {
            $lat = get_field('latitude', $post_id);
            $lng = get_field('longitude', $post_id);
            
            wp_send_json_success([
                'message' => 'Address geocoded successfully',
                'latitude' => $lat,
                'longitude' => $lng
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to geocode address']);
        }
    }
    
    /**
     * AJAX handler for refreshing location data
     */
    public function ajax_refresh_location_data(): void {
        // Same security checks as above
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hp_geocoding_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id || get_post_type($post_id) !== 'listing') {
            wp_send_json_error(['message' => 'Invalid listing ID']);
            return;
        }
        
        // Force refresh by clearing the last geocoded address
        delete_field('last_geocoded_address', $post_id);
        
        $success = $this->geocode_listing_address($post_id);
        
        if ($success) {
            wp_send_json_success(['message' => 'Location data refreshed successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to refresh location data']);
        }
    }
    
    /**
     * Check if geocoding is available
     */
    public function is_geocoding_available(): bool {
        return !empty($this->google_maps_api_key);
    }
    
    /**
     * Get current geocoding configuration
     */
    public function get_geocoding_config(): array {
        return [
            'api_key_configured' => !empty($this->google_maps_api_key),
            'service_enabled' => $this->initialized,
            'auto_geocode' => true
        ];
    }
}
