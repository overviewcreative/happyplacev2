<?php
/**
 * Maps Service
 *
 * Handles Google Maps API integration and geocoding
 * Migrated from theme to establish proper plugin-theme separation
 *
 * @package HappyPlace
 * @subpackage Services
 * @since 4.3.0 - Migrated from theme
 */

namespace HappyPlace\Services;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Maps Service Class
 *
 * Manages Google Maps API integration and geographic functionality
 */
class MapsService {

    /**
     * Google Maps API Key
     */
    private $api_key;

    /**
     * Service initialization
     */
    public function init() {
        $this->api_key = $this->get_api_key();

        // Only load maps on appropriate pages
        if ($this->should_load_maps()) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_maps_api'], 5);
            add_action('wp_head', [$this, 'add_maps_callback']);
        }

        // Register settings for API key management
        add_action('init', [$this, 'register_settings']);

        // AJAX handlers for map functionality
        add_action('wp_ajax_hph_geocode_address', [$this, 'handle_geocode_request']);
        add_action('wp_ajax_hph_reverse_geocode', [$this, 'handle_reverse_geocode']);
    }

    /**
     * Get Google Maps API key
     */
    private function get_api_key() {
        // Try environment variable first (most secure)
        $api_key = getenv('GOOGLE_MAPS_API_KEY');

        // Fallback to WordPress option
        if (!$api_key) {
            $api_key = get_option('hph_google_maps_api_key', '');
        }

        // Fallback to constant (for wp-config.php)
        if (!$api_key && defined('HPH_GOOGLE_MAPS_API_KEY')) {
            $api_key = HPH_GOOGLE_MAPS_API_KEY;
        }

        return $api_key;
    }

    /**
     * Check if maps should be loaded on current page
     */
    private function should_load_maps() {
        // Load on single listing pages
        if (is_singular('listing')) {
            return true;
        }

        // Load on pages with map shortcodes or components
        global $post;
        if ($post && (
            has_shortcode($post->post_content, 'hph_property_map') ||
            strpos($post->post_content, 'property-map') !== false ||
            strpos($post->post_content, 'hph-map') !== false
        )) {
            return true;
        }

        // Load on search results pages
        if (is_search() || is_post_type_archive('listing')) {
            return true;
        }

        // Load on dashboard pages
        if (get_query_var('agent_dashboard')) {
            return true;
        }

        return apply_filters('hph_should_load_maps', false);
    }

    /**
     * Enqueue Google Maps API
     */
    public function enqueue_maps_api() {
        if (!$this->api_key) {
            if (WP_DEBUG) {
                error_log('Google Maps API key not found. Please configure HPH_GOOGLE_MAPS_API_KEY.');
            }
            return;
        }

        $maps_url = add_query_arg([
            'key' => $this->api_key,
            'libraries' => 'places,geometry',
            'callback' => 'initHPHMaps'
        ], 'https://maps.googleapis.com/maps/api/js');

        wp_enqueue_script(
            'google-maps-api',
            $maps_url,
            [],
            null,
            true
        );

        // Add maps configuration
        wp_localize_script('google-maps-api', 'HPHMapsConfig', [
            'apiKey' => $this->api_key,
            'defaultCenter' => $this->get_default_center(),
            'defaultZoom' => apply_filters('hph_maps_default_zoom', 12),
            'mapStyles' => $this->get_map_styles(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_maps_nonce')
        ]);
    }

    /**
     * Add maps callback function
     */
    public function add_maps_callback() {
        if (!$this->api_key) {
            return;
        }
        ?>
        <script type="text/javascript">
        window.initHPHMaps = function() {
            if (typeof HPH !== 'undefined' && HPH.Maps) {
                HPH.Maps.initialize();
            }
        };
        </script>
        <?php
    }

    /**
     * Register settings for Maps configuration
     */
    public function register_settings() {
        // Register API key setting
        register_setting('hph_maps_settings', 'hph_google_maps_api_key', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        // Register default center setting
        register_setting('hph_maps_settings', 'hph_maps_default_center', [
            'sanitize_callback' => [$this, 'sanitize_coordinates']
        ]);
    }

    /**
     * Sanitize coordinates
     */
    public function sanitize_coordinates($value) {
        if (is_array($value) && isset($value['lat']) && isset($value['lng'])) {
            return [
                'lat' => floatval($value['lat']),
                'lng' => floatval($value['lng'])
            ];
        }
        return ['lat' => 39.7392, 'lng' => -104.9903]; // Default to Denver
    }

    /**
     * Get default map center
     */
    private function get_default_center() {
        $default = get_option('hph_maps_default_center', [
            'lat' => 39.7392,
            'lng' => -104.9903
        ]);

        return apply_filters('hph_maps_default_center', $default);
    }

    /**
     * Get map styles
     */
    private function get_map_styles() {
        $styles = apply_filters('hph_maps_styles', []);

        // Default to clean, professional style
        if (empty($styles)) {
            $styles = [
                [
                    'featureType' => 'poi',
                    'elementType' => 'labels',
                    'stylers' => [['visibility' => 'off']]
                ],
                [
                    'featureType' => 'transit',
                    'elementType' => 'labels',
                    'stylers' => [['visibility' => 'off']]
                ]
            ];
        }

        return $styles;
    }

    /**
     * Handle geocoding AJAX request
     */
    public function handle_geocode_request() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_maps_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        $address = sanitize_text_field($_POST['address'] ?? '');
        if (empty($address)) {
            wp_send_json_error('Address is required');
        }

        $result = $this->geocode_address($address);

        if ($result) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error('Unable to geocode address');
        }
    }

    /**
     * Handle reverse geocoding AJAX request
     */
    public function handle_reverse_geocode() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_maps_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        $lat = floatval($_POST['lat'] ?? 0);
        $lng = floatval($_POST['lng'] ?? 0);

        if (!$lat || !$lng) {
            wp_send_json_error('Valid coordinates are required');
        }

        $result = $this->reverse_geocode($lat, $lng);

        if ($result) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error('Unable to reverse geocode coordinates');
        }
    }

    /**
     * Geocode an address to coordinates
     */
    public function geocode_address($address) {
        if (!$this->api_key) {
            return false;
        }

        $url = add_query_arg([
            'address' => urlencode($address),
            'key' => $this->api_key
        ], 'https://maps.googleapis.com/maps/api/geocode/json');

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($data['status'] === 'OK' && !empty($data['results'])) {
            $result = $data['results'][0];
            return [
                'lat' => $result['geometry']['location']['lat'],
                'lng' => $result['geometry']['location']['lng'],
                'formatted_address' => $result['formatted_address'],
                'place_id' => $result['place_id'] ?? '',
                'address_components' => $result['address_components'] ?? []
            ];
        }

        return false;
    }

    /**
     * Reverse geocode coordinates to address
     */
    public function reverse_geocode($lat, $lng) {
        if (!$this->api_key) {
            return false;
        }

        $url = add_query_arg([
            'latlng' => $lat . ',' . $lng,
            'key' => $this->api_key
        ], 'https://maps.googleapis.com/maps/api/geocode/json');

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($data['status'] === 'OK' && !empty($data['results'])) {
            $result = $data['results'][0];
            return [
                'formatted_address' => $result['formatted_address'],
                'place_id' => $result['place_id'] ?? '',
                'address_components' => $result['address_components'] ?? []
            ];
        }

        return false;
    }

    /**
     * Get distance between two points
     */
    public function calculate_distance($lat1, $lng1, $lat2, $lng2, $unit = 'miles') {
        $theta = $lng1 - $lng2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        if ($unit === 'kilometers') {
            return $miles * 1.609344;
        }

        return $miles;
    }

    /**
     * Check if API key is configured
     */
    public function is_configured() {
        return !empty($this->api_key);
    }

    /**
     * Get API usage information
     */
    public function get_api_usage() {
        // This would require additional Google Cloud Console API calls
        // For now, return basic configuration status
        return [
            'configured' => $this->is_configured(),
            'api_key_set' => !empty($this->api_key),
            'last_check' => get_option('hph_maps_last_check', 0)
        ];
    }
}