<?php
/**
 * Mapbox Integration for Happy Place
 * 
 * Provides Mapbox functionality and configuration access
 * 
 * @package HappyPlace\Integrations
 * @since 4.0.0
 */

namespace HappyPlace\Integrations;

use HappyPlace\Core\ConfigurationManager;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mapbox Integration Class
 */
class MapboxIntegration {
    
    /**
     * Configuration Manager instance
     */
    private ConfigurationManager $config_manager;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->config_manager = ConfigurationManager::get_instance();
        $this->init();
    }
    
    /**
     * Initialize integration
     */
    public function init(): void {
        // Define constants for theme/plugin access
        add_action('init', [$this, 'define_constants'], 5);
        
        // Add filters for theme access
        add_filter('hph_mapbox_access_token', [$this, 'get_access_token']);
        add_filter('hph_map_center_coordinates', [$this, 'get_center_coordinates']);
        add_filter('hph_map_default_zoom', [$this, 'get_default_zoom']);
        add_filter('hph_mapbox_is_default_provider', [$this, 'is_default_provider']);
        
        // Add action for theme enqueuing
        add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_mapbox'], 5);
    }
    
    /**
     * Define Mapbox constants for global access
     */
    public function define_constants(): void {
        $access_token = $this->config_manager->get('mapbox_access_token');
        
        if (!defined('HP_MAPBOX_ACCESS_TOKEN') && !empty($access_token)) {
            define('HP_MAPBOX_ACCESS_TOKEN', $access_token);
        }
        
        $center_lat = $this->config_manager->get('map_center_lat', '29.4241');
        $center_lng = $this->config_manager->get('map_center_lng', '-98.4936');
        
        if (!defined('HP_MAP_CENTER_LAT')) {
            define('HP_MAP_CENTER_LAT', $center_lat);
        }
        
        if (!defined('HP_MAP_CENTER_LNG')) {
            define('HP_MAP_CENTER_LNG', $center_lng);
        }
        
        $default_zoom = $this->config_manager->get('map_default_zoom', 11);
        if (!defined('HP_MAP_DEFAULT_ZOOM')) {
            define('HP_MAP_DEFAULT_ZOOM', $default_zoom);
        }
        
        $is_default = $this->config_manager->get('mapbox_default_map_provider', false);
        if (!defined('HP_MAPBOX_DEFAULT_PROVIDER')) {
            define('HP_MAPBOX_DEFAULT_PROVIDER', $is_default);
        }
    }
    
    /**
     * Get Mapbox access token
     */
    public function get_access_token(): string {
        return $this->config_manager->get('mapbox_access_token', '');
    }
    
    /**
     * Get map center coordinates
     */
    public function get_center_coordinates(): array {
        return [
            'lat' => floatval($this->config_manager->get('map_center_lat', '29.4241')),
            'lng' => floatval($this->config_manager->get('map_center_lng', '-98.4936'))
        ];
    }
    
    /**
     * Get default zoom level
     */
    public function get_default_zoom(): int {
        return intval($this->config_manager->get('map_default_zoom', 11));
    }
    
    /**
     * Check if Mapbox is the default provider
     */
    public function is_default_provider(): bool {
        return (bool) $this->config_manager->get('mapbox_default_map_provider', false);
    }
    
    /**
     * Check if Mapbox is configured and available
     */
    public function is_available(): bool {
        return !empty($this->get_access_token());
    }
    
    /**
     * Maybe enqueue Mapbox scripts if configured
     */
    public function maybe_enqueue_mapbox(): void {
        // Only enqueue on relevant pages
        if (!$this->should_enqueue_mapbox()) {
            return;
        }
        
        $access_token = $this->get_access_token();
        if (empty($access_token)) {
            return;
        }
        
        // Enqueue Mapbox GL JS
        wp_enqueue_script(
            'mapbox-gl-js',
            'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js',
            [],
            '2.15.0',
            true
        );
        
        wp_enqueue_style(
            'mapbox-gl-css',
            'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css',
            [],
            '2.15.0'
        );
        
        // Localize Mapbox configuration
        wp_localize_script('mapbox-gl-js', 'hph_mapbox_config', [
            'access_token' => $access_token,
            'center' => $this->get_center_coordinates(),
            'default_zoom' => $this->get_default_zoom(),
            'is_default_provider' => $this->is_default_provider(),
        ]);
    }
    
    /**
     * Determine if Mapbox should be enqueued
     */
    private function should_enqueue_mapbox(): bool {
        // Enqueue on listing archives
        if (is_post_type_archive('listing')) {
            return true;
        }
        
        // Enqueue on property taxonomy pages
        if (is_tax('property_type') || is_tax('property_status') || is_tax('property_feature')) {
            return true;
        }
        
        // Enqueue on single listings if configured as default
        if (is_singular('listing') && $this->is_default_provider()) {
            return true;
        }
        
        // Enqueue on search pages
        if (is_search()) {
            return true;
        }
        
        // Allow themes/plugins to override
        return apply_filters('hph_should_enqueue_mapbox', false);
    }
    
    /**
     * Get Mapbox API endpoint
     */
    public function get_api_endpoint(string $service = 'base'): string {
        $endpoints = [
            'base' => 'https://api.mapbox.com/',
            'styles' => 'https://api.mapbox.com/styles/v1/',
            'geocoding' => 'https://api.mapbox.com/geocoding/v5/',
            'directions' => 'https://api.mapbox.com/directions/v5/',
        ];
        
        return $endpoints[$service] ?? $endpoints['base'];
    }
    
    /**
     * Test Mapbox connection
     */
    public function test_connection(): array {
        $access_token = $this->get_access_token();
        
        if (empty($access_token)) {
            return [
                'success' => false,
                'message' => __('No access token configured', 'happy-place')
            ];
        }
        
        // Test with a simple API call
        $test_url = $this->get_api_endpoint('geocoding') . 'mapbox.places/test.json?access_token=' . $access_token;
        
        $response = wp_remote_get($test_url, [
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'Happy Place WordPress Plugin'
            ]
        ]);
        
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => sprintf(__('Connection failed: %s', 'happy-place'), $response->get_error_message())
            ];
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code === 200) {
            return [
                'success' => true,
                'message' => __('Mapbox connection successful', 'happy-place')
            ];
        } else {
            $body = wp_remote_retrieve_body($response);
            $error_data = json_decode($body, true);
            
            return [
                'success' => false,
                'message' => sprintf(__('API error (HTTP %d): %s', 'happy-place'), 
                    $response_code,
                    $error_data['message'] ?? __('Unknown error', 'happy-place')
                )
            ];
        }
    }
}

// Initialize the integration
new MapboxIntegration();

// Helper functions for global access
if (!function_exists('hp_get_mapbox_token')) {
    /**
     * Get Mapbox access token
     */
    function hp_get_mapbox_token(): string {
        if (defined('HP_MAPBOX_ACCESS_TOKEN')) {
            return HP_MAPBOX_ACCESS_TOKEN;
        }
        
        return apply_filters('hph_mapbox_access_token', '');
    }
}

if (!function_exists('hp_get_map_center')) {
    /**
     * Get map center coordinates
     */
    function hp_get_map_center(): array {
        return apply_filters('hph_map_center_coordinates', [
            'lat' => defined('HP_MAP_CENTER_LAT') ? HP_MAP_CENTER_LAT : 29.4241,
            'lng' => defined('HP_MAP_CENTER_LNG') ? HP_MAP_CENTER_LNG : -98.4936
        ]);
    }
}

if (!function_exists('hp_get_map_zoom')) {
    /**
     * Get default map zoom
     */
    function hp_get_map_zoom(): int {
        if (defined('HP_MAP_DEFAULT_ZOOM')) {
            return HP_MAP_DEFAULT_ZOOM;
        }
        
        return apply_filters('hph_map_default_zoom', 11);
    }
}

if (!function_exists('hp_is_mapbox_default')) {
    /**
     * Check if Mapbox is the default map provider
     */
    function hp_is_mapbox_default(): bool {
        if (defined('HP_MAPBOX_DEFAULT_PROVIDER')) {
            return HP_MAPBOX_DEFAULT_PROVIDER;
        }
        
        return apply_filters('hph_mapbox_is_default_provider', false);
    }
}

if (!function_exists('hp_mapbox_available')) {
    /**
     * Check if Mapbox is available
     */
    function hp_mapbox_available(): bool {
        return !empty(hp_get_mapbox_token());
    }
}