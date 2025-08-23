<?php
/**
 * ACF Manager - Simplified Version
 * Minimal ACF integration - lets ACF Pro handle native sync
 *
 * @package HappyPlace
 */

namespace HappyPlace\Core;

if (!defined('ABSPATH')) {
    exit;
}

class ACF_Manager {

    private static $instance = null;
    private $acf_available = false;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Alias for backwards compatibility
    public static function instance() {
        return self::get_instance();
    }

    private function __construct() {
        // Constructor only sets up the instance
    }

    /**
     * Initialize the ACF Manager - Simplified
     */
    public function init() {
        $this->check_acf_availability();
        if ($this->acf_available) {
            $this->init_hooks();
            hp_log('ACF Manager initialized (simplified mode)', 'info', 'ACF_MANAGER');
        } else {
            hp_log('ACF not available - ACF Manager disabled', 'warning', 'ACF_MANAGER');
        }
    }

    /**
     * Check if ACF is available
     */
    public function check_acf_availability() {
        $acf_class_exists = class_exists('ACF');
        $acf_function_exists = function_exists('acf');
        
        hp_log('ACF availability check - class_exists(ACF): ' . ($acf_class_exists ? 'true' : 'false'), 'info', 'ACF_MANAGER');
        hp_log('ACF availability check - function_exists(acf): ' . ($acf_function_exists ? 'true' : 'false'), 'info', 'ACF_MANAGER');
        
        $this->acf_available = $acf_class_exists && $acf_function_exists;
        
        hp_log('ACF availability result: ' . ($this->acf_available ? 'true' : 'false'), 'info', 'ACF_MANAGER');
        
        return $this->acf_available;
    }

    /**
     * Check if ACF is available (public method)
     */
    public function is_acf_available() {
        return $this->acf_available;
    }

    /**
     * Initialize hooks - Minimal setup only
     */
    private function init_hooks() {
        // Set ACF JSON path - this is standard ACF Pro functionality
        add_filter('acf/settings/save_json', [$this, 'acf_json_save_path']);
        add_filter('acf/settings/load_json', [$this, 'acf_json_load_paths']);
        
        // Basic setup
        add_action('acf/init', [$this, 'setup_options_pages']);
        
        hp_log('ACF Manager hooks initialized - basic setup only', 'info', 'ACF_MANAGER');
    }

    /**
     * Set ACF JSON save path
     */
    public function acf_json_save_path($path) {
        $custom_path = HP_PLUGIN_DIR . 'includes/fields/acf-json';
        if (is_dir($custom_path)) {
            return $custom_path;
        }
        return $path;
    }

    /**
     * Add ACF JSON load paths
     */
    public function acf_json_load_paths($paths) {
        $custom_path = HP_PLUGIN_DIR . 'includes/fields/acf-json';
        if (is_dir($custom_path)) {
            $paths[] = $custom_path;
        }
        return $paths;
    }

    /**
     * Setup ACF Options Pages
     */
    public function setup_options_pages() {
        if (!function_exists('acf_add_options_page')) {
            return;
        }

        // Main settings page
        acf_add_options_page([
            'page_title' => 'Happy Place Settings',
            'menu_title' => 'HP Settings',
            'menu_slug' => 'hp-settings',
            'capability' => 'manage_options',
            'icon_url' => 'dashicons-admin-generic',
            'position' => 30,
        ]);

        hp_log('ACF options pages registered', 'info', 'ACF_MANAGER');
    }

    /**
     * Get field groups (simple method for compatibility)
     */
    public function get_field_groups() {
        if (function_exists('acf_get_field_groups')) {
            return acf_get_field_groups();
        }
        return [];
    }

    /**
     * Register custom field types (if needed)
     */
    private function register_custom_fields() {
        // Only basic custom field registration if needed
        // Let ACF Pro handle the complex stuff
        
        if (!function_exists('acf_register_field_type')) {
            return;
        }

        // Add any custom field types here if needed in the future
        // For now, we'll rely on ACF Pro's built-in fields
        
        hp_log('Custom field types check completed', 'debug', 'ACF_MANAGER');
    }

    /**
     * Simple analytics data (without complex field group loading)
     */
    public function get_analytics_data($post_id) {
        if (!$this->acf_available || !$post_id) {
            return [];
        }

        // Basic analytics without complex syncing
        $data = [
            'total_views' => get_field('total_views', $post_id) ?: 0,
            'monthly_views' => get_field('monthly_views', $post_id) ?: 0,
            'weekly_views' => get_field('weekly_views', $post_id) ?: 0,
        ];

        return $data;
    }

    /**
     * Get field value with fallback
     */
    public function get_field_value($field_name, $post_id = false) {
        if (!$this->acf_available) {
            return null;
        }

        return get_field($field_name, $post_id);
    }

    /**
     * Update field value with validation
     */
    public function update_field_value($field_name, $value, $post_id) {
        if (!$this->acf_available) {
            return false;
        }

        return update_field($field_name, $value, $post_id);
    }

    // ========================================================================
    // REMOVED COMPLEX FUNCTIONS
    // ========================================================================
    // The following functions have been removed to simplify the ACF Manager:
    // - immediate_field_groups_load()
    // - load_field_groups() 
    // - force_sync_field_groups()
    // - force_reload_field_groups()
    // - verify_field_groups()
    // - register_field_groups()
    // - handle_field_group_sync()
    // - All complex JSON sync logic
    // - All custom field group registration
    // - All expected groups validation
    // - All ACF Pro sync integration
    // 
    // ACF Pro will handle all field group syncing natively.
    // ========================================================================

    /**
     * Legacy method compatibility - returns simple status
     */
    public function force_sync_field_groups() {
        hp_log('force_sync_field_groups called - simplified version does not perform custom sync', 'info', 'ACF_MANAGER');
        return 0; // Return 0 as no custom sync is performed
    }
}