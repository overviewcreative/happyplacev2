<?php
/**
 * Taxonomies Class - Fixed Version
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
 * Taxonomies Class
 */
class Taxonomies {
    
    /**
     * Single instance
     */
    private static ?Taxonomies $instance = null;
    
    /**
     * Taxonomies configuration
     */
    private array $taxonomies = [];
    
    /**
     * Get instance
     */
    public static function get_instance(): Taxonomies {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_config();
    }
    
    /**
     * Initialize
     */
    public function init(): void {
        // Register taxonomies (we're already in the 'init' action)
        $this->register_taxonomies();
        
        // Add default terms later
        add_action('init', [$this, 'add_default_terms'], 15);
        
        hp_log('Taxonomies initialized', 'info', 'TAXONOMIES');
    }
    
    /**
     * Load configuration
     */
    private function load_config(): void {
        // Load from config file if exists
        $config_file = HP_CONFIG_DIR . 'taxonomies.json';
        
        if (file_exists($config_file)) {
            $config = json_decode(file_get_contents($config_file), true);
            if (isset($config['taxonomies'])) {
                $this->taxonomies = $config['taxonomies'];
                hp_log('Taxonomies loaded from config file', 'info', 'TAXONOMIES');
                return;
            }
        }
        
        // Default configuration
        $this->taxonomies = $this->get_default_config();
        hp_log('Taxonomies loaded from default configuration', 'info', 'TAXONOMIES');
    }
    
    /**
     * Get default taxonomies configuration
     */
    private function get_default_config(): array {
        return [
            'property_type' => [
                'object_type' => ['listing'],
                'args' => [
                    'labels' => $this->get_property_type_labels(),
                    'hierarchical' => true,
                    'public' => true,
                    'show_ui' => true,
                    'show_admin_column' => true,
                    'show_in_nav_menus' => true,
                    'show_tagcloud' => false,
                    'show_in_rest' => true,
                    'rewrite' => ['slug' => 'property-type', 'with_front' => false],
                ]
            ],
            'property_status' => [
                'object_type' => ['listing'],
                'args' => [
                    'labels' => $this->get_property_status_labels(),
                    'hierarchical' => false,
                    'public' => true,
                    'show_ui' => true,
                    'show_admin_column' => true,
                    'show_in_nav_menus' => true,
                    'show_tagcloud' => false,
                    'show_in_rest' => true,
                    'rewrite' => ['slug' => 'property-status', 'with_front' => false],
                ]
            ],
        ];
    }
    
    /**
     * Register taxonomies
     */
    public function register_taxonomies(): void {
        foreach ($this->taxonomies as $taxonomy => $config) {
            $object_type = $config['object_type'] ?? ['post'];
            $args = $config['args'] ?? [];
            
            // Register taxonomy
            register_taxonomy($taxonomy, $object_type, $args);
            
            hp_log("Registered taxonomy: {$taxonomy}", 'debug', 'TAXONOMIES');
        }
    }
    
    /**
     * Add default terms
     */
    public function add_default_terms(): void {
        // Check if we've already added default terms
        if (get_option('hp_default_terms_added')) {
            return;
        }
        
        // Property types
        $property_types = [
            'single-family' => __('Single Family', 'happy-place'),
            'condo' => __('Condo', 'happy-place'),
            'townhouse' => __('Townhouse', 'happy-place'),
            'multi-family' => __('Multi-Family', 'happy-place'),
            'land' => __('Land', 'happy-place'),
        ];
        
        foreach ($property_types as $slug => $name) {
            if (!term_exists($slug, 'property_type')) {
                wp_insert_term($name, 'property_type', ['slug' => $slug]);
            }
        }
        
        // Property statuses
        $property_statuses = [
            'active' => __('Active', 'happy-place'),
            'pending' => __('Pending', 'happy-place'),
            'sold' => __('Sold', 'happy-place'),
        ];
        
        foreach ($property_statuses as $slug => $name) {
            if (!term_exists($slug, 'property_status')) {
                wp_insert_term($name, 'property_status', ['slug' => $slug]);
            }
        }
        
        // Mark as complete
        update_option('hp_default_terms_added', true);
        
        hp_log('Default taxonomy terms added', 'info', 'TAXONOMIES');
    }
    
    /**
     * Get property type labels
     */
    private function get_property_type_labels(): array {
        return [
            'name' => __('Property Types', 'happy-place'),
            'singular_name' => __('Property Type', 'happy-place'),
            'search_items' => __('Search Property Types', 'happy-place'),
            'all_items' => __('All Property Types', 'happy-place'),
            'parent_item' => __('Parent Property Type', 'happy-place'),
            'parent_item_colon' => __('Parent Property Type:', 'happy-place'),
            'edit_item' => __('Edit Property Type', 'happy-place'),
            'update_item' => __('Update Property Type', 'happy-place'),
            'add_new_item' => __('Add New Property Type', 'happy-place'),
            'new_item_name' => __('New Property Type Name', 'happy-place'),
            'menu_name' => __('Property Types', 'happy-place'),
        ];
    }
    
    /**
     * Get property status labels
     */
    private function get_property_status_labels(): array {
        return [
            'name' => __('Property Statuses', 'happy-place'),
            'singular_name' => __('Property Status', 'happy-place'),
            'search_items' => __('Search Property Statuses', 'happy-place'),
            'all_items' => __('All Property Statuses', 'happy-place'),
            'edit_item' => __('Edit Property Status', 'happy-place'),
            'update_item' => __('Update Property Status', 'happy-place'),
            'add_new_item' => __('Add New Property Status', 'happy-place'),
            'new_item_name' => __('New Property Status Name', 'happy-place'),
            'menu_name' => __('Property Statuses', 'happy-place'),
        ];
    }
}