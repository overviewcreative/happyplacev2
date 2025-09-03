<?php
/**
 * Happy Place Configuration Manager
 * 
 * Centralized configuration management for all API keys, tokens, and settings
 * This class serves as the single source of truth for all external integrations
 * 
 * @package HappyPlace\Core
 * @since 4.0.0
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configuration Manager Class
 * 
 * @since 4.0.0
 */
class ConfigurationManager {
    
    /**
     * Single instance
     * 
     * @var ConfigurationManager|null
     */
    private static ?ConfigurationManager $instance = null;
    
    /**
     * Configuration cache
     * 
     * @var array
     */
    private array $config_cache = [];
    
    /**
     * Configuration mapping
     * Maps legacy option names to new unified option names
     * 
     * @var array
     */
    private array $config_mapping = [
        // Google Maps API
        'google_maps_api_key' => [
            'unified_key' => 'hp_google_maps_api_key',
            'legacy_keys' => ['hph_google_maps_api_key', 'HPH_GOOGLE_MAPS_API_KEY', 'HPH_GOOGLE_MAPS_KEY'],
            'env_key' => 'GOOGLE_MAPS_API_KEY',
            'description' => 'Google Maps API Key for geocoding and map display',
            'type' => 'api_key',
            'required' => true,
            'category' => 'maps'
        ],
        
        // Airtable Integration
        'airtable_api_key' => [
            'unified_key' => 'hp_airtable_api_key',
            'legacy_keys' => ['hph_airtable_api_key'],
            'env_key' => 'AIRTABLE_API_KEY',
            'description' => 'Airtable API Key for data synchronization',
            'type' => 'api_key',
            'required' => false,
            'category' => 'crm'
        ],
        'airtable_base_id' => [
            'unified_key' => 'hp_airtable_base_id',
            'legacy_keys' => ['hph_airtable_base_id'],
            'env_key' => 'AIRTABLE_BASE_ID',
            'description' => 'Airtable Base ID',
            'type' => 'string',
            'required' => false,
            'category' => 'crm'
        ],
        'airtable_listings_table' => [
            'unified_key' => 'hp_airtable_listings_table',
            'legacy_keys' => [],
            'default' => 'Listings',
            'description' => 'Airtable table name for listings',
            'type' => 'string',
            'category' => 'crm'
        ],
        'airtable_contacts_table' => [
            'unified_key' => 'hp_airtable_contacts_table',
            'legacy_keys' => [],
            'default' => 'Contacts',
            'description' => 'Airtable table name for contacts',
            'type' => 'string',
            'category' => 'crm'
        ],
        
        // FollowUp Boss Integration
        'followup_boss_api_key' => [
            'unified_key' => 'hp_followup_boss_api_key',
            'legacy_keys' => ['hph_followupboss_api_key'],
            'env_key' => 'FOLLOWUP_BOSS_API_KEY',
            'description' => 'FollowUp Boss API Key',
            'type' => 'api_key',
            'required' => false,
            'category' => 'crm'
        ],
        'followup_boss_api_secret' => [
            'unified_key' => 'hp_followup_boss_api_secret',
            'legacy_keys' => ['hph_followupboss_api_secret'],
            'env_key' => 'FOLLOWUP_BOSS_API_SECRET',
            'description' => 'FollowUp Boss API Secret',
            'type' => 'api_secret',
            'required' => false,
            'category' => 'crm'
        ],
        'followup_boss_lead_source' => [
            'unified_key' => 'hp_followup_boss_lead_source',
            'legacy_keys' => [],
            'default' => 'Website',
            'description' => 'Default lead source for FollowUp Boss',
            'type' => 'string',
            'category' => 'crm'
        ],
        
        // Email Services
        'mailchimp_api_key' => [
            'unified_key' => 'hp_mailchimp_api_key',
            'legacy_keys' => ['hph_mailchimp_api_key'],
            'env_key' => 'MAILCHIMP_API_KEY',
            'description' => 'Mailchimp API Key',
            'type' => 'api_key',
            'required' => false,
            'category' => 'email'
        ],
        'mailchimp_list_id' => [
            'unified_key' => 'hp_mailchimp_list_id',
            'legacy_keys' => ['hph_mailchimp_list_id'],
            'env_key' => 'MAILCHIMP_LIST_ID',
            'description' => 'Default Mailchimp List ID',
            'type' => 'string',
            'category' => 'email'
        ],
        
        // Mapbox Integration
        'mapbox_access_token' => [
            'unified_key' => 'hp_mapbox_access_token',
            'legacy_keys' => ['hph_mapbox_api_key', 'mapbox_api_key', 'HPH_MAPBOX_TOKEN'],
            'env_key' => 'MAPBOX_ACCESS_TOKEN',
            'description' => 'Mapbox public access token for advanced map functionality',
            'type' => 'api_key',
            'required' => false,
            'category' => 'maps'
        ],
        'mapbox_default_map_provider' => [
            'unified_key' => 'hp_mapbox_default_map_provider',
            'legacy_keys' => [],
            'default' => false,
            'description' => 'Use Mapbox as the default map provider',
            'type' => 'boolean',
            'category' => 'maps'
        ],
        'map_center_lat' => [
            'unified_key' => 'hp_map_center_lat',
            'legacy_keys' => ['hph_map_center_lat'],
            'default' => '29.4241',
            'description' => 'Default map center latitude',
            'type' => 'string',
            'category' => 'maps'
        ],
        'map_center_lng' => [
            'unified_key' => 'hp_map_center_lng',
            'legacy_keys' => ['hph_map_center_lng'],
            'default' => '-98.4936',
            'description' => 'Default map center longitude',
            'type' => 'string',
            'category' => 'maps'
        ],
        'map_default_zoom' => [
            'unified_key' => 'hp_map_default_zoom',
            'legacy_keys' => ['hph_map_default_zoom'],
            'default' => 11,
            'description' => 'Default map zoom level',
            'type' => 'integer',
            'category' => 'maps'
        ],

        // Analytics & Tracking
        'google_analytics_id' => [
            'unified_key' => 'hp_google_analytics_id',
            'legacy_keys' => ['hph_google_analytics_id'],
            'env_key' => 'GOOGLE_ANALYTICS_ID',
            'description' => 'Google Analytics Tracking ID',
            'type' => 'string',
            'category' => 'analytics'
        ],
        'facebook_pixel_id' => [
            'unified_key' => 'hp_facebook_pixel_id',
            'legacy_keys' => ['hph_facebook_pixel_id'],
            'env_key' => 'FACEBOOK_PIXEL_ID',
            'description' => 'Facebook Pixel ID',
            'type' => 'string',
            'category' => 'analytics'
        ],
        
        // Social Media
        'facebook_app_id' => [
            'unified_key' => 'hp_facebook_app_id',
            'legacy_keys' => [],
            'env_key' => 'FACEBOOK_APP_ID',
            'description' => 'Facebook App ID',
            'type' => 'string',
            'category' => 'social'
        ],
        'twitter_api_key' => [
            'unified_key' => 'hp_twitter_api_key',
            'legacy_keys' => [],
            'env_key' => 'TWITTER_API_KEY',
            'description' => 'Twitter API Key',
            'type' => 'api_key',
            'category' => 'social'
        ],
        
        // MLS Integration
        'mls_provider' => [
            'unified_key' => 'hp_mls_provider',
            'legacy_keys' => [],
            'description' => 'MLS Provider (RETS, IDX, etc.)',
            'type' => 'string',
            'category' => 'mls'
        ],
        'mls_api_key' => [
            'unified_key' => 'hp_mls_api_key',
            'legacy_keys' => ['hph_mls_api_key'],
            'env_key' => 'MLS_API_KEY',
            'description' => 'MLS API Key',
            'type' => 'api_key',
            'category' => 'mls'
        ],
        'mls_api_secret' => [
            'unified_key' => 'hp_mls_api_secret',
            'legacy_keys' => ['hph_mls_api_secret'],
            'env_key' => 'MLS_API_SECRET',
            'description' => 'MLS API Secret',
            'type' => 'api_secret',
            'category' => 'mls'
        ],
        
        // WalkScore
        'walkscore_api_key' => [
            'unified_key' => 'hp_walkscore_api_key',
            'legacy_keys' => [],
            'env_key' => 'WALKSCORE_API_KEY',
            'description' => 'WalkScore API Key',
            'type' => 'api_key',
            'category' => 'location'
        ],
        
        // GreatSchools
        'greatschools_api_key' => [
            'unified_key' => 'hp_greatschools_api_key',
            'legacy_keys' => [],
            'env_key' => 'GREATSCHOOLS_API_KEY',
            'description' => 'GreatSchools API Key',
            'type' => 'api_key',
            'category' => 'location'
        ],
        
        // reCAPTCHA
        'recaptcha_site_key' => [
            'unified_key' => 'hp_recaptcha_site_key',
            'legacy_keys' => ['hph_recaptcha_site_key'],
            'env_key' => 'RECAPTCHA_SITE_KEY',
            'description' => 'reCAPTCHA Site Key',
            'type' => 'string',
            'category' => 'security'
        ],
        'recaptcha_secret_key' => [
            'unified_key' => 'hp_recaptcha_secret_key',
            'legacy_keys' => ['hph_recaptcha_secret_key'],
            'env_key' => 'RECAPTCHA_SECRET_KEY',
            'description' => 'reCAPTCHA Secret Key',
            'type' => 'api_secret',
            'category' => 'security'
        ]
    ];
    
    /**
     * API endpoints configuration
     * 
     * @var array
     */
    private array $api_endpoints = [
        'airtable' => [
            'base_url' => 'https://api.airtable.com/v0/',
            'documentation' => 'https://airtable.com/developers/web/api/introduction',
            'auth_type' => 'bearer'
        ],
        'followup_boss' => [
            'base_url' => 'https://api.followupboss.com/v1/',
            'documentation' => 'https://docs.followupboss.com/article/77-api-getting-started',
            'auth_type' => 'basic'
        ],
        'google_maps' => [
            'base_url' => 'https://maps.googleapis.com/maps/api/',
            'geocode_endpoint' => 'https://maps.googleapis.com/maps/api/geocode/json',
            'places_endpoint' => 'https://maps.googleapis.com/maps/api/place/',
            'documentation' => 'https://developers.google.com/maps/documentation',
            'auth_type' => 'key'
        ],
        'mapbox' => [
            'base_url' => 'https://api.mapbox.com/',
            'styles_api' => 'https://api.mapbox.com/styles/v1/',
            'geocoding_api' => 'https://api.mapbox.com/geocoding/v5/',
            'directions_api' => 'https://api.mapbox.com/directions/v5/',
            'js_api' => 'https://api.mapbox.com/mapbox-gl-js/',
            'documentation' => 'https://docs.mapbox.com/api/',
            'auth_type' => 'token'
        ],
        'walkscore' => [
            'base_url' => 'https://api.walkscore.com/',
            'documentation' => 'https://www.walkscore.com/professional/api.php',
            'auth_type' => 'key'
        ],
        'greatschools' => [
            'base_url' => 'https://api.greatschools.org/v1/',
            'documentation' => 'https://www.greatschools.org/api/docs/',
            'auth_type' => 'key'
        ],
        'mailchimp' => [
            'base_url' => 'https://{dc}.api.mailchimp.com/3.0/',
            'documentation' => 'https://mailchimp.com/developer/marketing/api/',
            'auth_type' => 'key'
        ]
    ];
    
    /**
     * Get instance
     * 
     * @return ConfigurationManager
     */
    public static function get_instance(): ConfigurationManager {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Initialize configuration
        $this->init();
    }
    
    /**
     * Initialize configuration manager
     * 
     * @return void
     */
    private function init(): void {
        // Hook into WordPress initialization
        add_action('init', [$this, 'migrate_legacy_settings'], 5);
        add_action('admin_init', [$this, 'register_settings']);
        
        hp_log('Configuration Manager initialized', 'info', 'ConfigurationManager');
    }
    
    /**
     * Get configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @return mixed Configuration value
     */
    public function get(string $key, $default = null) {
        // Check cache first
        if (isset($this->config_cache[$key])) {
            return $this->config_cache[$key];
        }
        
        $config = $this->config_mapping[$key] ?? null;
        if (!$config) {
            hp_log("Unknown configuration key: $key", 'warning', 'ConfigurationManager');
            return $default;
        }
        
        $value = null;
        
        // 1. Check environment variable first
        if (isset($config['env_key'])) {
            $env_value = getenv($config['env_key']);
            if ($env_value !== false && !empty($env_value)) {
                $value = $env_value;
            }
        }
        
        // 2. Check unified option
        if ($value === null) {
            $value = get_option($config['unified_key'], null);
        }
        
        // 3. Check legacy options
        if ($value === null && isset($config['legacy_keys'])) {
            foreach ($config['legacy_keys'] as $legacy_key) {
                if (defined($legacy_key)) {
                    $value = constant($legacy_key);
                    break;
                }
                $legacy_value = get_option($legacy_key, null);
                if ($legacy_value !== null) {
                    $value = $legacy_value;
                    break;
                }
            }
        }
        
        // 4. Use default value
        if ($value === null) {
            $value = $config['default'] ?? $default;
        }
        
        // Cache the value
        $this->config_cache[$key] = $value;
        
        return $value;
    }
    
    /**
     * Set configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return bool Success status
     */
    public function set(string $key, $value): bool {
        if (HP_DEBUG) {
            hp_log("Attempting to set configuration: $key = " . print_r($value, true), 'debug', 'ConfigurationManager');
        }
        
        $config = $this->config_mapping[$key] ?? null;
        if (!$config) {
            hp_log("Cannot set unknown configuration key: $key", 'error', 'ConfigurationManager');
            return false;
        }
        
        if (HP_DEBUG) {
            hp_log("Using unified key: {$config['unified_key']}", 'debug', 'ConfigurationManager');
        }
        
        $result = update_option($config['unified_key'], $value);
        
        if (HP_DEBUG) {
            hp_log("update_option result for {$config['unified_key']}: " . ($result ? 'success' : 'failed'), 'debug', 'ConfigurationManager');
        }
        
        // Update cache
        if ($result) {
            $this->config_cache[$key] = $value;
            hp_log("Configuration updated: $key", 'info', 'ConfigurationManager');
        } else {
            hp_log("Failed to update configuration: $key", 'warning', 'ConfigurationManager');
        }
        
        return $result;
    }
    
    /**
     * Get API endpoint configuration
     * 
     * @param string $service Service name
     * @return array|null Endpoint configuration
     */
    public function get_endpoint(string $service): ?array {
        return $this->api_endpoints[$service] ?? null;
    }
    
    /**
     * Get all configurations by category
     * 
     * @param string $category Category name
     * @return array Configurations in category
     */
    public function get_category(string $category): array {
        $configs = [];
        foreach ($this->config_mapping as $key => $config) {
            if (($config['category'] ?? '') === $category) {
                $configs[$key] = $this->get($key);
            }
        }
        return $configs;
    }
    
    /**
     * Check if a service is configured
     * 
     * @param string $service Service name
     * @return bool Configuration status
     */
    public function is_service_configured(string $service): bool {
        switch ($service) {
            case 'google_maps':
                return !empty($this->get('google_maps_api_key'));
            
            case 'airtable':
                return !empty($this->get('airtable_api_key')) && !empty($this->get('airtable_base_id'));
            
            case 'followup_boss':
                return !empty($this->get('followup_boss_api_key')) && !empty($this->get('followup_boss_api_secret'));
            
            case 'mailchimp':
                return !empty($this->get('mailchimp_api_key'));
            
            case 'walkscore':
                return !empty($this->get('walkscore_api_key'));
            
            case 'greatschools':
                return !empty($this->get('greatschools_api_key'));
            
            case 'mapbox':
                return !empty($this->get('mapbox_access_token'));
            
            default:
                return false;
        }
    }
    
    /**
     * Get webhook URLs
     * 
     * @return array Webhook URLs
     */
    public function get_webhook_urls(): array {
        return [
            'followup_boss' => home_url('/wp-json/hp/v1/followup-boss-webhook'),
            'airtable' => home_url('/wp-json/hp/v1/airtable-webhook'),
            'mailchimp' => home_url('/wp-json/hp/v1/mailchimp-webhook'),
            'general_lead' => home_url('/wp-json/hp/v1/lead-webhook'),
            'mls_sync' => home_url('/wp-json/hp/v1/mls-sync-webhook')
        ];
    }
    
    /**
     * Migrate legacy settings to unified system
     * 
     * @return void
     */
    public function migrate_legacy_settings(): void {
        $migrated = get_option('hp_config_migrated', false);
        if ($migrated) {
            return;
        }
        
        $migrated_count = 0;
        
        foreach ($this->config_mapping as $key => $config) {
            $unified_key = $config['unified_key'];
            $current_value = get_option($unified_key, null);
            
            // Skip if unified setting already exists
            if ($current_value !== null) {
                continue;
            }
            
            // Check legacy options
            if (isset($config['legacy_keys'])) {
                foreach ($config['legacy_keys'] as $legacy_key) {
                    $legacy_value = null;
                    
                    // Check if it's a constant
                    if (defined($legacy_key)) {
                        $legacy_value = constant($legacy_key);
                    } else {
                        $legacy_value = get_option($legacy_key, null);
                    }
                    
                    if ($legacy_value !== null && !empty($legacy_value)) {
                        update_option($unified_key, $legacy_value);
                        $migrated_count++;
                        hp_log("Migrated $legacy_key to $unified_key", 'info', 'ConfigurationManager');
                        break;
                    }
                }
            }
        }
        
        // Mark migration as complete
        update_option('hp_config_migrated', true);
        
        if ($migrated_count > 0) {
            hp_log("Migrated $migrated_count configuration settings", 'info', 'ConfigurationManager');
        }
    }
    
    /**
     * Register WordPress settings
     * 
     * @return void
     */
    public function register_settings(): void {
        foreach ($this->config_mapping as $key => $config) {
            register_setting('hp_integrations_settings', $config['unified_key'], [
                'type' => $config['type'] === 'api_key' || $config['type'] === 'api_secret' ? 'string' : $config['type'],
                'description' => $config['description'],
                'sanitize_callback' => [$this, 'sanitize_setting'],
                'show_in_rest' => false // Keep API keys private
            ]);
        }
    }
    
    /**
     * Sanitize setting value
     * 
     * @param mixed $value Setting value
     * @return mixed Sanitized value
     */
    public function sanitize_setting($value) {
        if (HP_DEBUG) {
            hp_log('Sanitizing setting value: ' . print_r($value, true), 'debug', 'ConfigurationManager');
        }
        
        if (is_string($value)) {
            $sanitized = sanitize_text_field($value);
            if (HP_DEBUG) {
                hp_log('Sanitized string value: ' . $sanitized, 'debug', 'ConfigurationManager');
            }
            return $sanitized;
        }
        return $value;
    }
    
    /**
     * Get configuration mapping
     * 
     * @return array Configuration mapping
     */
    public function get_mapping(): array {
        return $this->config_mapping;
    }
    
    /**
     * Get configuration status for admin display
     * 
     * @return array Configuration status
     */
    public function get_configuration_status(): array {
        $status = [];
        
        foreach ($this->config_mapping as $key => $config) {
            $value = $this->get($key);
            $status[$key] = [
                'configured' => !empty($value),
                'required' => $config['required'] ?? false,
                'category' => $config['category'] ?? 'other',
                'description' => $config['description'] ?? '',
                'has_value' => !empty($value)
            ];
        }
        
        return $status;
    }
    
    /**
     * Delete a configuration value
     * 
     * @param string $key Configuration key
     * @return bool True on success, false on failure
     */
    public function delete(string $key): bool {
        $config = $this->config_mapping[$key] ?? null;
        
        if (!$config) {
            // If not in mapping, try to delete as direct key
            return delete_option($key);
        }
        
        // Delete the unified option
        $result = delete_option($config['unified_key']);
        
        // Also clean up any legacy keys
        if (!empty($config['legacy_keys'])) {
            foreach ($config['legacy_keys'] as $legacy_key) {
                delete_option($legacy_key);
            }
        }
        
        return $result;
    }
}
