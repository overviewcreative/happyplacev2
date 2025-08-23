<?php
/**
 * ACF JSON Loader
 * Ensures ACF field groups are loaded from the plugin's JSON files
 *
 * @package HappyPlace\Core
 */

namespace HappyPlace\Core;

if (!defined('ABSPATH')) {
    exit;
}

class ACF_JSON_Loader {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Set up ACF JSON load points early
        add_filter('acf/settings/load_json', [$this, 'add_json_load_point'], 1);
        
        // Force sync on admin init if needed
        add_action('admin_init', [$this, 'check_and_sync_field_groups']);
    }
    
    /**
     * Add our JSON directory to ACF load points
     */
    public function add_json_load_point($paths) {
        // Add plugin's ACF JSON directory
        $plugin_path = HP_PLUGIN_DIR . 'includes/fields/acf-json';
        if (is_dir($plugin_path)) {
            $paths[] = $plugin_path;
            hp_log('Added ACF JSON path: ' . $plugin_path, 'debug', 'ACF_JSON');
        }
        
        return $paths;
    }
    
    /**
     * Check if field groups need syncing
     */
    public function check_and_sync_field_groups() {
        if (!function_exists('acf_get_field_groups')) {
            return;
        }
        
        // Check if we have listing field groups
        $field_groups = acf_get_field_groups(['post_type' => 'listing']);
        
        if (empty($field_groups)) {
            hp_log('No field groups found for listings, attempting sync', 'warning', 'ACF_JSON');
            $this->sync_field_groups();
        } else {
            hp_log('Found ' . count($field_groups) . ' field groups for listings', 'debug', 'ACF_JSON');
        }
    }
    
    /**
     * Force sync field groups from JSON
     */
    public function sync_field_groups() {
        if (!function_exists('acf_get_field_group')) {
            return;
        }
        
        $json_dir = HP_PLUGIN_DIR . 'includes/fields/acf-json';
        if (!is_dir($json_dir)) {
            hp_log('ACF JSON directory not found', 'error', 'ACF_JSON');
            return;
        }
        
        $json_files = glob($json_dir . '/*.json');
        $synced = 0;
        
        foreach ($json_files as $file) {
            $json = json_decode(file_get_contents($file), true);
            
            if (!$json || !isset($json['key'])) {
                continue;
            }
            
            // Check if this field group exists
            $existing = acf_get_field_group($json['key']);
            
            if (!$existing) {
                // Import the field group
                $json['local'] = 'json';
                $json['local_file'] = $file;
                
                // Import field group
                $field_group = acf_import_field_group($json);
                
                if ($field_group) {
                    $synced++;
                    hp_log('Synced field group: ' . $json['title'], 'info', 'ACF_JSON');
                }
            }
        }
        
        if ($synced > 0) {
            hp_log('Synced ' . $synced . ' field groups from JSON', 'info', 'ACF_JSON');
        }
    }
}

// Initialize immediately
ACF_JSON_Loader::get_instance();