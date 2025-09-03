<?php
/**
 * Post Types Class - Working Version
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
 * PostTypes Class
 */
class PostTypes {
    
    /**
     * Single instance
     */
    private static ?PostTypes $instance = null;
    
    /**
     * Post types configuration
     */
    private array $post_types = [];
    
    /**
     * Get instance
     */
    public static function get_instance(): PostTypes {
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
        // Register post types (we're already in the 'init' action)
        $this->register_post_types();
        
        // Add support for thumbnails
        add_theme_support('post-thumbnails', array_keys($this->post_types));
        
        // Flush rewrite rules if needed
        add_action('init', [$this, 'maybe_flush_rewrite_rules'], 30);
        
        hp_log('Post Types initialized', 'info', 'POST_TYPES');
    }
    
    /**
     * Load configuration
     */
    private function load_config(): void {
        // Load from config file if exists
        $config_file = HP_CONFIG_DIR . 'post-types.json';
        
        if (file_exists($config_file)) {
            $config = json_decode(file_get_contents($config_file), true);
            if (isset($config['post_types'])) {
                $this->post_types = $config['post_types'];
                return;
            }
        }
        
        // Default configuration
        $this->post_types = $this->get_default_config();
    }
    
    /**
     * Get default post types configuration
     */
    private function get_default_config(): array {
        return [
            'listing' => [
                'labels' => $this->get_listing_labels(),
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => ['slug' => 'listings', 'with_front' => false],
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => 5,
                'menu_icon' => 'dashicons-admin-home',
                'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'],
                'show_in_rest' => true,
                'rest_base' => 'listings',
            ],
            'agent' => [
                'labels' => $this->get_agent_labels(),
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => ['slug' => 'agents', 'with_front' => false],
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => 6,
                'menu_icon' => 'dashicons-businessperson',
                'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
                'show_in_rest' => true,
                'rest_base' => 'agents',
            ],
            'staff' => [
                'labels' => $this->get_staff_labels(),
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => ['slug' => 'staff', 'with_front' => false],
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => 7,
                'menu_icon' => 'dashicons-admin-users',
                'supports' => ['title', 'editor', 'thumbnail'],
                'show_in_rest' => true,
                'rest_base' => 'staff',
            ],
            'open_house' => [
                'labels' => $this->get_open_house_labels(),
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => ['slug' => 'open-houses', 'with_front' => false],
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => 8,
                'menu_icon' => 'dashicons-calendar-alt',
                'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
                'show_in_rest' => true,
                'rest_base' => 'open-houses',
            ],
            'community' => [
                'labels' => $this->get_community_labels(),
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => ['slug' => 'communities', 'with_front' => false],
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => 9,
                'menu_icon' => 'dashicons-admin-multisite',
                'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
                'show_in_rest' => true,
                'rest_base' => 'communities',
            ],
            'lead' => [
                'labels' => $this->get_lead_labels(),
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => false,
                'rewrite' => false,
                'capability_type' => 'post',
                'has_archive' => false,
                'hierarchical' => false,
                'menu_position' => 10,
                'menu_icon' => 'dashicons-groups',
                'supports' => ['title', 'custom-fields'],
                'show_in_rest' => true,
                'rest_base' => 'leads',
            ],
            'transaction' => [
                'labels' => $this->get_transaction_labels(),
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => false,
                'rewrite' => false,
                'capability_type' => 'post',
                'has_archive' => false,
                'hierarchical' => false,
                'menu_position' => 11,
                'menu_icon' => 'dashicons-money-alt',
                'supports' => ['title', 'editor', 'custom-fields'],
                'show_in_rest' => true,
                'rest_base' => 'transactions',
            ],
        ];
    }
    
    /**
     * Register post types
     */
    public function register_post_types(): void {
        foreach ($this->post_types as $post_type => $args) {
            // Apply filters
            $args = apply_filters("hp_{$post_type}_args", $args);
            
            // Register post type
            register_post_type($post_type, $args);
            
            hp_log("Registered post type: {$post_type}", 'debug', 'POST_TYPES');
        }
        
        // Allow themes/plugins to hook after registration
        do_action('hp_post_types_registered', $this->post_types);
    }
    
    /**
     * Maybe flush rewrite rules
     */
    public function maybe_flush_rewrite_rules(): void {
        if (get_option('hp_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('hp_flush_rewrite_rules');
            hp_log('Rewrite rules flushed', 'info', 'POST_TYPES');
        }
    }
    
    /**
     * Get listing labels
     */
    private function get_listing_labels(): array {
        return [
            'name' => __('Listings', 'happy-place'),
            'singular_name' => __('Listing', 'happy-place'),
            'add_new' => __('Add New', 'happy-place'),
            'add_new_item' => __('Add New Listing', 'happy-place'),
            'edit_item' => __('Edit Listing', 'happy-place'),
            'new_item' => __('New Listing', 'happy-place'),
            'view_item' => __('View Listing', 'happy-place'),
            'view_items' => __('View Listings', 'happy-place'),
            'search_items' => __('Search Listings', 'happy-place'),
            'not_found' => __('No listings found', 'happy-place'),
            'not_found_in_trash' => __('No listings found in Trash', 'happy-place'),
            'all_items' => __('All Listings', 'happy-place'),
            'menu_name' => __('Listings', 'happy-place'),
            'name_admin_bar' => __('Listing', 'happy-place'),
        ];
    }
    
    /**
     * Get agent labels
     */
    private function get_agent_labels(): array {
        return [
            'name' => __('Agents', 'happy-place'),
            'singular_name' => __('Agent', 'happy-place'),
            'add_new' => __('Add New', 'happy-place'),
            'add_new_item' => __('Add New Agent', 'happy-place'),
            'edit_item' => __('Edit Agent', 'happy-place'),
            'new_item' => __('New Agent', 'happy-place'),
            'view_item' => __('View Agent', 'happy-place'),
            'view_items' => __('View Agents', 'happy-place'),
            'search_items' => __('Search Agents', 'happy-place'),
            'not_found' => __('No agents found', 'happy-place'),
            'not_found_in_trash' => __('No agents found in Trash', 'happy-place'),
            'all_items' => __('All Agents', 'happy-place'),
            'menu_name' => __('Agents', 'happy-place'),
            'name_admin_bar' => __('Agent', 'happy-place'),
        ];
    }
    
    /**
     * Get staff labels
     */
    private function get_staff_labels(): array {
        return [
            'name' => __('Staff', 'happy-place'),
            'singular_name' => __('Staff Member', 'happy-place'),
            'add_new' => __('Add New', 'happy-place'),
            'add_new_item' => __('Add New Staff Member', 'happy-place'),
            'edit_item' => __('Edit Staff Member', 'happy-place'),
            'new_item' => __('New Staff Member', 'happy-place'),
            'view_item' => __('View Staff Member', 'happy-place'),
            'view_items' => __('View Staff', 'happy-place'),
            'search_items' => __('Search Staff', 'happy-place'),
            'not_found' => __('No staff found', 'happy-place'),
            'not_found_in_trash' => __('No staff found in Trash', 'happy-place'),
            'all_items' => __('All Staff', 'happy-place'),
            'menu_name' => __('Staff', 'happy-place'),
            'name_admin_bar' => __('Staff', 'happy-place'),
        ];
    }
    
    /**
     * Get open house labels
     */
    private function get_open_house_labels(): array {
        return [
            'name' => __('Open Houses', 'happy-place'),
            'singular_name' => __('Open House', 'happy-place'),
            'add_new' => __('Add New', 'happy-place'),
            'add_new_item' => __('Add New Open House', 'happy-place'),
            'edit_item' => __('Edit Open House', 'happy-place'),
            'new_item' => __('New Open House', 'happy-place'),
            'view_item' => __('View Open House', 'happy-place'),
            'view_items' => __('View Open Houses', 'happy-place'),
            'search_items' => __('Search Open Houses', 'happy-place'),
            'not_found' => __('No open houses found', 'happy-place'),
            'not_found_in_trash' => __('No open houses found in Trash', 'happy-place'),
            'all_items' => __('All Open Houses', 'happy-place'),
            'menu_name' => __('Open Houses', 'happy-place'),
            'name_admin_bar' => __('Open House', 'happy-place'),
        ];
    }
    
    /**
     * Get community labels
     */
    private function get_community_labels(): array {
        return [
            'name' => __('Communities', 'happy-place'),
            'singular_name' => __('Community', 'happy-place'),
            'add_new' => __('Add New', 'happy-place'),
            'add_new_item' => __('Add New Community', 'happy-place'),
            'edit_item' => __('Edit Community', 'happy-place'),
            'new_item' => __('New Community', 'happy-place'),
            'view_item' => __('View Community', 'happy-place'),
            'view_items' => __('View Communities', 'happy-place'),
            'search_items' => __('Search Communities', 'happy-place'),
            'not_found' => __('No communities found', 'happy-place'),
            'not_found_in_trash' => __('No communities found in Trash', 'happy-place'),
            'all_items' => __('All Communities', 'happy-place'),
            'menu_name' => __('Communities', 'happy-place'),
            'name_admin_bar' => __('Community', 'happy-place'),
        ];
    }
    
    /**
     * Get lead labels
     */
    private function get_lead_labels(): array {
        return [
            'name' => __('Leads', 'happy-place'),
            'singular_name' => __('Lead', 'happy-place'),
            'add_new' => __('Add New', 'happy-place'),
            'add_new_item' => __('Add New Lead', 'happy-place'),
            'edit_item' => __('Edit Lead', 'happy-place'),
            'new_item' => __('New Lead', 'happy-place'),
            'view_item' => __('View Lead', 'happy-place'),
            'view_items' => __('View Leads', 'happy-place'),
            'search_items' => __('Search Leads', 'happy-place'),
            'not_found' => __('No leads found', 'happy-place'),
            'not_found_in_trash' => __('No leads found in Trash', 'happy-place'),
            'all_items' => __('All Leads', 'happy-place'),
            'menu_name' => __('Leads', 'happy-place'),
            'name_admin_bar' => __('Lead', 'happy-place'),
        ];
    }
    
    /**
     * Get transaction labels
     */
    private function get_transaction_labels(): array {
        return [
            'name' => __('Transactions', 'happy-place'),
            'singular_name' => __('Transaction', 'happy-place'),
            'add_new' => __('Add New', 'happy-place'),
            'add_new_item' => __('Add New Transaction', 'happy-place'),
            'edit_item' => __('Edit Transaction', 'happy-place'),
            'new_item' => __('New Transaction', 'happy-place'),
            'view_item' => __('View Transaction', 'happy-place'),
            'view_items' => __('View Transactions', 'happy-place'),
            'search_items' => __('Search Transactions', 'happy-place'),
            'not_found' => __('No transactions found', 'happy-place'),
            'not_found_in_trash' => __('No transactions found in Trash', 'happy-place'),
            'all_items' => __('All Transactions', 'happy-place'),
            'menu_name' => __('Transactions', 'happy-place'),
            'name_admin_bar' => __('Transaction', 'happy-place'),
        ];
    }
}