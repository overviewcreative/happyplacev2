<?php
/**
 * Fresh Start Class - Clean ACF Setup
 * 
 * @package HappyPlace\Core
 */

namespace HappyPlace\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Fresh_Start {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Set up clean ACF configuration
        add_action('init', [$this, 'setup_clean_acf'], 1);
        add_filter('acf/settings/load_json', [$this, 'set_json_load_point'], 1);
        add_filter('acf/settings/save_json', [$this, 'set_json_save_point'], 1);
        
        // Prevent any database field groups from loading
        add_filter('acf/load_field_groups', [$this, 'json_only_field_groups'], 1);
        
        // Ensure post types are registered before ACF
        add_action('init', [$this, 'ensure_post_types'], 0);
        
        // Add admin notice for fresh start
        add_action('admin_notices', [$this, 'fresh_start_notice']);
    }
    
    /**
     * Ensure post types are registered first
     */
    public function ensure_post_types() {
        // Make sure listing post type exists
        if (!post_type_exists('listing')) {
            register_post_type('listing', [
                'labels' => [
                    'name' => 'Listings',
                    'singular_name' => 'Listing',
                ],
                'public' => true,
                'show_ui' => true,
                'show_in_rest' => true,
                'menu_icon' => 'dashicons-admin-home',
                'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
                'has_archive' => true,
                'rewrite' => ['slug' => 'listing', 'with_front' => false],
            ]);
        }
    }
    
    /**
     * Setup clean ACF
     */
    public function setup_clean_acf() {
        if (!function_exists('acf')) {
            return;
        }
        
        // Log status
        hp_log('Fresh Start: Setting up clean ACF configuration', 'info', 'FRESH_START');
        
        // Clear any ACF caches
        if (function_exists('acf_get_store')) {
            $store = acf_get_store('field-groups');
            if ($store) {
                $store->reset();
            }
        }
    }
    
    /**
     * Set JSON load point - ONLY our plugin directory
     */
    public function set_json_load_point($paths) {
        // Clear default paths
        $paths = [];
        
        // Add ONLY our plugin's JSON directory
        $plugin_json = HP_PLUGIN_DIR . 'includes/fields/acf-json';
        if (is_dir($plugin_json)) {
            $paths[] = $plugin_json;
            hp_log('Fresh Start: JSON load path set to ' . $plugin_json, 'info', 'FRESH_START');
        }
        
        return $paths;
    }
    
    /**
     * Set JSON save point
     */
    public function set_json_save_point($path) {
        $plugin_json = HP_PLUGIN_DIR . 'includes/fields/acf-json';
        if (is_dir($plugin_json)) {
            return $plugin_json;
        }
        return $path;
    }
    
    /**
     * Force JSON-only field groups
     */
    public function json_only_field_groups($field_groups) {
        // This ensures we only use JSON field groups
        foreach ($field_groups as $key => $group) {
            if (!isset($group['local']) || $group['local'] !== 'json') {
                unset($field_groups[$key]);
            }
        }
        return $field_groups;
    }
    
    /**
     * Show admin notice
     */
    public function fresh_start_notice() {
        if (get_transient('hp_fresh_start_complete')) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Happy Place Fresh Start:</strong> ACF field groups are now loading from JSON only. Database has been cleaned.</p>
            </div>
            <?php
            delete_transient('hp_fresh_start_complete');
        }
    }
    
    /**
     * Run fresh start setup
     */
    public static function run_fresh_start() {
        // Set transient for notice
        set_transient('hp_fresh_start_complete', true, 60);
        
        // Clear all ACF caches
        wp_cache_flush();
        
        // Force ACF to rescan
        if (function_exists('acf_get_field_groups')) {
            delete_transient('acf_field_groups');
        }
        
        hp_log('Fresh Start completed', 'info', 'FRESH_START');
    }
}

// Initialize
Fresh_Start::get_instance();