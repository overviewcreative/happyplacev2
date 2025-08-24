<?php
/**
 * Config Class
 * 
 * Handles configuration management from files and database
 *
 * @package HappyPlace\Core
 * @version 4.0.0
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Config Class
 * 
 * @since 4.0.0
 */
class Config {
    
    /**
     * Configuration directory
     * 
     * @var string
     */
    private string $config_dir;
    
    /**
     * Loaded configuration
     * 
     * @var array
     */
    private array $config = [];
    
    /**
     * Configuration cache
     * 
     * @var array
     */
    private array $cache = [];
    
    /**
     * Constructor
     * 
     * @param string $config_dir Configuration directory
     */
    public function __construct(string $config_dir = '') {
        $this->config_dir = $config_dir ?: HP_CONFIG_DIR;
        $this->load_configs();
    }
    
    /**
     * Load configuration files
     * 
     * @return void
     */
    private function load_configs(): void {
        // Load PHP config files
        $php_files = glob($this->config_dir . '*.php');
        foreach ($php_files as $file) {
            $key = basename($file, '.php');
            $this->config[$key] = require $file;
        }
        
        // Load JSON config files
        $json_files = glob($this->config_dir . '*.json');
        foreach ($json_files as $file) {
            $key = basename($file, '.json');
            $content = file_get_contents($file);
            $this->config[$key] = json_decode($content, true);
        }
        
        // Load database configurations
        $this->load_database_configs();
        
        hp_log('Configuration loaded from ' . $this->config_dir, 'debug', 'CONFIG');
    }
    
    /**
     * Load configurations from database
     * 
     * @return void
     */
    private function load_database_configs(): void {
        // Load plugin settings from options table
        $db_config = get_option('hp_config', []);
        
        if (!empty($db_config)) {
            $this->config['settings'] = $db_config;
        }
        
        // Allow filtering of configuration
        $this->config = apply_filters('hp_config', $this->config);
    }
    
    /**
     * Get configuration value
     * 
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $key, $default = null) {
        // Check cache first
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        
        // Parse dot notation
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        // Cache the result
        $this->cache[$key] = $value;
        
        return $value;
    }
    
    /**
     * Set configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Value to set
     * @return void
     */
    public function set(string $key, $value): void {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $config[$k] = $value;
            } else {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }
        }
        
        // Clear cache for this key
        unset($this->cache[$key]);
    }
    
    /**
     * Check if configuration key exists
     * 
     * @param string $key Configuration key
     * @return bool
     */
    public function has(string $key): bool {
        return $this->get($key) !== null;
    }
    
    /**
     * Get all configuration
     * 
     * @param string|null $section Optional section to get
     * @return array
     */
    public function all(?string $section = null): array {
        if ($section) {
            return $this->config[$section] ?? [];
        }
        
        return $this->config;
    }
    
    /**
     * Save configuration to database
     * 
     * @param string $section Section to save
     * @return bool
     */
    public function save(string $section = 'settings'): bool {
        if (!isset($this->config[$section])) {
            return false;
        }
        
        return update_option('hp_config', $this->config[$section]);
    }
    
    /**
     * Reload configuration
     * 
     * @return void
     */
    public function reload(): void {
        $this->config = [];
        $this->cache = [];
        $this->load_configs();
    }
    
    /**
     * Get post type configuration
     * 
     * @param string|null $post_type Specific post type or all
     * @return array
     */
    public function get_post_types(?string $post_type = null): array {
        $post_types = $this->get('post-types', []);
        
        if ($post_type) {
            return $post_types['post_types'][$post_type] ?? [];
        }
        
        return $post_types['post_types'] ?? [];
    }
    
    /**
     * Get taxonomy configuration
     * 
     * @param string|null $taxonomy Specific taxonomy or all
     * @return array
     */
    public function get_taxonomies(?string $taxonomy = null): array {
        $taxonomies = $this->get('taxonomies', []);
        
        if ($taxonomy) {
            return $taxonomies['taxonomies'][$taxonomy] ?? [];
        }
        
        return $taxonomies['taxonomies'] ?? [];
    }
    
    /**
     * Get API configuration
     * 
     * @param string|null $key Specific API key
     * @return mixed
     */
    public function get_api_config(?string $key = null) {
        $api_config = $this->get('api', []);
        
        if ($key) {
            return $api_config[$key] ?? null;
        }
        
        return $api_config;
    }
    
    /**
     * Get feature flags
     * 
     * @param string|null $feature Specific feature
     * @return mixed
     */
    public function is_feature_enabled(?string $feature = null) {
        $features = $this->get('features', []);
        
        if ($feature) {
            return $features[$feature] ?? false;
        }
        
        return $features;
    }
}