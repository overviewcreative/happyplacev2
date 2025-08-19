<?php
/**
 * Taxonomies Class
 * 
 * Handles registration and management of custom taxonomies for Happy Place
 *
 * @package HappyPlace\Core
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Taxonomies {
    
    private static $instance = null;
    private $config_file;
    private $taxonomies_config = [];
    
    /**
     * Default taxonomy configurations (fallback if JSON not available)
     */
    private $default_taxonomies = [
        'property_type' => [
            'post_types' => ['listing'],
            'labels' => [
                'name' => 'Property Types',
                'singular_name' => 'Property Type',
                'search_items' => 'Search Property Types',
                'popular_items' => 'Popular Property Types',
                'all_items' => 'All Property Types',
                'parent_item' => 'Parent Property Type',
                'parent_item_colon' => 'Parent Property Type:',
                'edit_item' => 'Edit Property Type',
                'view_item' => 'View Property Type',
                'update_item' => 'Update Property Type',
                'add_new_item' => 'Add New Property Type',
                'new_item_name' => 'New Property Type Name',
                'separate_items_with_commas' => 'Separate property types with commas',
                'add_or_remove_items' => 'Add or remove property types',
                'choose_from_most_used' => 'Choose from the most used property types',
                'not_found' => 'No property types found',
                'no_terms' => 'No property types',
                'filter_by_item' => 'Filter by property type',
                'items_list_navigation' => 'Property types list navigation',
                'items_list' => 'Property types list',
                'most_used' => 'Most Used',
                'back_to_items' => '&larr; Back to Property Types',
                'item_link' => 'Property Type Link',
                'item_link_description' => 'A link to a property type',
            ],
            'args' => [
                'hierarchical' => true,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
                'rest_base' => 'property-types',
                'show_tagcloud' => true,
                'show_in_quick_edit' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => [
                    'slug' => 'property-type',
                    'with_front' => false,
                    'hierarchical' => true,
                ],
                'default_term' => [
                    'name' => 'Single Family Home',
                    'slug' => 'single-family-home',
                    'description' => 'Traditional single family residential homes',
                ]
            ]
        ],
        
        'property_status' => [
            'post_types' => ['listing'],
            'labels' => [
                'name' => 'Property Status',
                'singular_name' => 'Property Status',
                'search_items' => 'Search Property Status',
                'popular_items' => 'Popular Status',
                'all_items' => 'All Property Status',
                'edit_item' => 'Edit Property Status',
                'view_item' => 'View Property Status',
                'update_item' => 'Update Property Status',
                'add_new_item' => 'Add New Property Status',
                'new_item_name' => 'New Property Status Name',
                'separate_items_with_commas' => 'Separate status with commas',
                'add_or_remove_items' => 'Add or remove status',
                'choose_from_most_used' => 'Choose from the most used status',
                'not_found' => 'No property status found',
                'no_terms' => 'No property status',
                'items_list' => 'Property status list',
            ],
            'args' => [
                'hierarchical' => false,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
                'rest_base' => 'property-status',
                'show_tagcloud' => false,
                'show_in_quick_edit' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => [
                    'slug' => 'status',
                    'with_front' => false,
                ],
                'default_term' => [
                    'name' => 'For Sale',
                    'slug' => 'for-sale',
                    'description' => 'Properties currently for sale',
                ]
            ]
        ],
        
        'listing_features' => [
            'post_types' => ['listing'],
            'labels' => [
                'name' => 'Features',
                'singular_name' => 'Feature',
                'search_items' => 'Search Features',
                'popular_items' => 'Popular Features',
                'all_items' => 'All Features',
                'edit_item' => 'Edit Feature',
                'view_item' => 'View Feature',
                'update_item' => 'Update Feature',
                'add_new_item' => 'Add New Feature',
                'new_item_name' => 'New Feature Name',
                'separate_items_with_commas' => 'Separate features with commas',
                'add_or_remove_items' => 'Add or remove features',
                'choose_from_most_used' => 'Choose from the most used features',
                'not_found' => 'No features found',
                'items_list' => 'Features list',
            ],
            'args' => [
                'hierarchical' => false,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => false,
                'show_in_rest' => true,
                'rest_base' => 'listing-features',
                'show_tagcloud' => true,
                'show_in_quick_edit' => true,
                'show_admin_column' => false,
                'query_var' => true,
                'rewrite' => [
                    'slug' => 'feature',
                    'with_front' => false,
                ],
            ]
        ],
        
        'location' => [
            'post_types' => ['listing', 'agent', 'community', 'local_place'],
            'labels' => [
                'name' => 'Locations',
                'singular_name' => 'Location',
                'search_items' => 'Search Locations',
                'popular_items' => 'Popular Locations',
                'all_items' => 'All Locations',
                'parent_item' => 'Parent Location',
                'parent_item_colon' => 'Parent Location:',
                'edit_item' => 'Edit Location',
                'view_item' => 'View Location',
                'update_item' => 'Update Location',
                'add_new_item' => 'Add New Location',
                'new_item_name' => 'New Location Name',
                'separate_items_with_commas' => 'Separate locations with commas',
                'add_or_remove_items' => 'Add or remove locations',
                'choose_from_most_used' => 'Choose from the most used locations',
                'not_found' => 'No locations found',
                'items_list' => 'Locations list',
            ],
            'args' => [
                'hierarchical' => true,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
                'rest_base' => 'locations',
                'show_tagcloud' => false,
                'show_in_quick_edit' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => [
                    'slug' => 'location',
                    'with_front' => false,
                    'hierarchical' => true,
                ],
            ]
        ],
        
        'agent_specialty' => [
            'post_types' => ['agent'],
            'labels' => [
                'name' => 'Specialties',
                'singular_name' => 'Specialty',
                'search_items' => 'Search Specialties',
                'popular_items' => 'Popular Specialties',
                'all_items' => 'All Specialties',
                'edit_item' => 'Edit Specialty',
                'view_item' => 'View Specialty',
                'update_item' => 'Update Specialty',
                'add_new_item' => 'Add New Specialty',
                'new_item_name' => 'New Specialty Name',
                'separate_items_with_commas' => 'Separate specialties with commas',
                'add_or_remove_items' => 'Add or remove specialties',
                'choose_from_most_used' => 'Choose from the most used specialties',
                'not_found' => 'No specialties found',
                'items_list' => 'Specialties list',
            ],
            'args' => [
                'hierarchical' => false,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
                'rest_base' => 'agent-specialties',
                'show_tagcloud' => true,
                'show_in_quick_edit' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => [
                    'slug' => 'specialty',
                    'with_front' => false,
                ],
            ]
        ],
        
        'place_category' => [
            'post_types' => ['local_place'],
            'labels' => [
                'name' => 'Place Categories',
                'singular_name' => 'Place Category',
                'search_items' => 'Search Categories',
                'popular_items' => 'Popular Categories',
                'all_items' => 'All Categories',
                'parent_item' => 'Parent Category',
                'parent_item_colon' => 'Parent Category:',
                'edit_item' => 'Edit Category',
                'view_item' => 'View Category',
                'update_item' => 'Update Category',
                'add_new_item' => 'Add New Category',
                'new_item_name' => 'New Category Name',
                'separate_items_with_commas' => 'Separate categories with commas',
                'add_or_remove_items' => 'Add or remove categories',
                'choose_from_most_used' => 'Choose from the most used categories',
                'not_found' => 'No categories found',
                'items_list' => 'Categories list',
            ],
            'args' => [
                'hierarchical' => true,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
                'rest_base' => 'place-categories',
                'show_tagcloud' => true,
                'show_in_quick_edit' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => [
                    'slug' => 'place-category',
                    'with_front' => false,
                    'hierarchical' => true,
                ],
            ]
        ]
    ];
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize taxonomies
     */
    private function __construct() {
        $this->config_file = HP_PLUGIN_DIR . 'includes/config/taxonomies.json';
        $this->load_config();
    }

    /**
     * Load taxonomies configuration from JSON file
     */
    private function load_config() {
        if (file_exists($this->config_file)) {
            $config_data = json_decode(file_get_contents($this->config_file), true);
            if ($config_data && isset($config_data['taxonomies'])) {
                $this->taxonomies_config = $config_data['taxonomies'];
                hp_log('Taxonomies loaded from JSON configuration', 'info', 'TAXONOMIES');
                return;
            }
        }
        
        // Fallback to default configuration
        $this->taxonomies_config = $this->default_taxonomies;
        hp_log('Taxonomies loaded from default configuration (JSON not found)', 'warning', 'TAXONOMIES');
    }
    
    /**
     * Initialize component
     */
    public function init() {
        // Register taxonomies immediately - timing is critical for ACF integration
        $this->register_taxonomies();
        
        // Schedule other hooks for later execution
        add_action('init', [$this, 'add_default_terms'], 15);
        add_action('create_term', [$this, 'set_term_meta'], 10, 3);
        add_filter('manage_edit-listing_columns', [$this, 'add_taxonomy_columns']);
        add_action('manage_listing_posts_custom_column', [$this, 'show_taxonomy_columns'], 10, 2);
        
        // Register ACF taxonomy support
        add_action('acf/init', [$this, 'register_acf_taxonomy_support'], 5);
        
        hp_log('Taxonomies component initialized', 'debug', 'TAXONOMIES');
    }
    
    /**
     * Register all custom taxonomies
     */
    public function register_taxonomies() {
        foreach ($this->taxonomies_config as $taxonomy => $config) {
            // Handle both old and new config formats
            if (isset($config['post_types'])) {
                // Old structure with separate post_types, labels, and args
                $post_types = $config['post_types'];
                $labels = $config['labels'] ?? [];
                $args = $config['args'] ?? [];
            } else {
                // New JSON structure - object_type contains post types
                $post_types = $config['object_type'] ?? ['post'];
                $labels = $config['labels'] ?? [];
                $args = $config;
                unset($args['labels'], $args['object_type']); // Remove from args
            }
            
            // Apply text domain to labels
            foreach ($labels as $key => $label) {
                $labels[$key] = __($label, 'happy-place');
            }
            
            $args['labels'] = $labels;
            
            $result = register_taxonomy($taxonomy, $post_types, $args);
            
            if (is_wp_error($result)) {
                hp_log("Failed to register taxonomy {$taxonomy}: " . $result->get_error_message(), 'error', 'TAXONOMIES');
            } else {
                hp_log("Successfully registered taxonomy: {$taxonomy} for post types: " . implode(', ', $post_types), 'info', 'TAXONOMIES');
            }
        }
    }
    
    /**
     * Add default terms
     */
    public function add_default_terms() {
        foreach ($this->taxonomies_config as $taxonomy => $config) {
            if (isset($config['args']['default_term'])) {
                $default_term = $config['args']['default_term'];
                
                if (!term_exists($default_term['slug'], $taxonomy)) {
                    wp_insert_term(
                        $default_term['name'],
                        $taxonomy,
                        [
                            'slug' => $default_term['slug'],
                            'description' => $default_term['description'] ?? '',
                        ]
                    );
                    
                    hp_log("Added default term '{$default_term['name']}' for taxonomy: {$taxonomy}", 'debug', 'TAXONOMIES');
                }
            }
        }
        
        // Add common property types
        $this->add_property_type_terms();
        
        // Add common property status terms
        $this->add_property_status_terms();
        
        // Add common features
        $this->add_feature_terms();
        
        // Add common agent specialties
        $this->add_agent_specialty_terms();
        
        // Add common place categories
        $this->add_place_category_terms();
    }
    
    /**
     * Add common property type terms
     */
    private function add_property_type_terms() {
        $property_types = [
            'Single Family Home' => 'single-family-home',
            'Townhouse' => 'townhouse',
            'Condominium' => 'condominium',
            'Multi-Family' => 'multi-family',
            'Vacant Land' => 'vacant-land',
            'Commercial' => 'commercial',
            'Mobile Home' => 'mobile-home',
        ];
        
        foreach ($property_types as $name => $slug) {
            if (!term_exists($slug, 'property_type')) {
                wp_insert_term($name, 'property_type', ['slug' => $slug]);
            }
        }
    }
    
    /**
     * Add common property status terms
     */
    private function add_property_status_terms() {
        $statuses = [
            'For Sale' => 'for-sale',
            'Pending' => 'pending',
            'Sold' => 'sold',
            'Coming Soon' => 'coming-soon',
            'Off Market' => 'off-market',
            'Rent' => 'rent',
            'Leased' => 'leased',
        ];
        
        foreach ($statuses as $name => $slug) {
            if (!term_exists($slug, 'property_status')) {
                wp_insert_term($name, 'property_status', ['slug' => $slug]);
            }
        }
    }
    
    /**
     * Add common feature terms
     */
    private function add_feature_terms() {
        $features = [
            'Pool' => 'pool',
            'Spa' => 'spa',
            'Fireplace' => 'fireplace',
            'Garage' => 'garage',
            'Hardwood Floors' => 'hardwood-floors',
            'Updated Kitchen' => 'updated-kitchen',
            'Master Suite' => 'master-suite',
            'Walk-in Closet' => 'walk-in-closet',
            'Patio' => 'patio',
            'Balcony' => 'balcony',
            'Garden' => 'garden',
            'Security System' => 'security-system',
        ];
        
        foreach ($features as $name => $slug) {
            if (!term_exists($slug, 'listing_features')) {
                wp_insert_term($name, 'listing_features', ['slug' => $slug]);
            }
        }
    }
    
    /**
     * Add common agent specialty terms
     */
    private function add_agent_specialty_terms() {
        $specialties = [
            'Buyer\'s Agent' => 'buyers-agent',
            'Listing Agent' => 'listing-agent',
            'First Time Buyers' => 'first-time-buyers',
            'Luxury Homes' => 'luxury-homes',
            'Investment Properties' => 'investment-properties',
            'Commercial Real Estate' => 'commercial-real-estate',
            'Relocation' => 'relocation',
            'Short Sales' => 'short-sales',
            'Foreclosures' => 'foreclosures',
        ];
        
        foreach ($specialties as $name => $slug) {
            if (!term_exists($slug, 'agent_specialty')) {
                wp_insert_term($name, 'agent_specialty', ['slug' => $slug]);
            }
        }
    }
    
    /**
     * Add common place category terms
     */
    private function add_place_category_terms() {
        $categories = [
            'Restaurants' => 'restaurants',
            'Shopping' => 'shopping',
            'Schools' => 'schools',
            'Parks' => 'parks',
            'Healthcare' => 'healthcare',
            'Entertainment' => 'entertainment',
            'Transportation' => 'transportation',
            'Services' => 'services',
            'Fitness' => 'fitness',
            'Worship' => 'worship',
        ];
        
        foreach ($categories as $name => $slug) {
            if (!term_exists($slug, 'place_category')) {
                wp_insert_term($name, 'place_category', ['slug' => $slug]);
            }
        }
    }
    
    /**
     * Set term meta on creation
     */
    public function set_term_meta($term_id, $tt_id, $taxonomy) {
        // Add custom meta for specific taxonomies
        switch ($taxonomy) {
            case 'location':
                add_term_meta($term_id, 'location_type', 'city', true);
                add_term_meta($term_id, 'coordinates', '', true);
                break;
                
            case 'property_type':
                add_term_meta($term_id, 'icon', 'dashicons-admin-home', true);
                add_term_meta($term_id, 'color', '#3498db', true);
                break;
                
            case 'listing_features':
                add_term_meta($term_id, 'icon', 'dashicons-yes', true);
                add_term_meta($term_id, 'featured', '0', true);
                break;
        }
    }
    
    /**
     * Add taxonomy columns to listing admin
     */
    public function add_taxonomy_columns($columns) {
        $new_columns = [];
        
        foreach ($columns as $key => $label) {
            $new_columns[$key] = $label;
            
            // Insert taxonomy columns after title
            if ($key === 'title') {
                $new_columns['property_type'] = __('Type', 'happy-place');
                $new_columns['property_status'] = __('Status', 'happy-place');
                $new_columns['location'] = __('Location', 'happy-place');
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Show taxonomy columns content
     */
    public function show_taxonomy_columns($column, $post_id) {
        switch ($column) {
            case 'property_type':
                $terms = get_the_terms($post_id, 'property_type');
                if ($terms && !is_wp_error($terms)) {
                    $term_names = wp_list_pluck($terms, 'name');
                    echo implode(', ', $term_names);
                } else {
                    echo '—';
                }
                break;
                
            case 'property_status':
                $terms = get_the_terms($post_id, 'property_status');
                if ($terms && !is_wp_error($terms)) {
                    $term_names = wp_list_pluck($terms, 'name');
                    echo implode(', ', $term_names);
                } else {
                    echo '—';
                }
                break;
                
            case 'location':
                $terms = get_the_terms($post_id, 'location');
                if ($terms && !is_wp_error($terms)) {
                    $term_names = wp_list_pluck($terms, 'name');
                    echo implode(', ', $term_names);
                } else {
                    echo '—';
                }
                break;
        }
    }
    
    /**
     * Register ACF taxonomy support
     */
    public function register_acf_taxonomy_support(): void {
        if (!function_exists('acf_get_setting')) {
            return;
        }
        
        $taxonomies = array_keys($this->taxonomies_config);
        
        foreach ($taxonomies as $taxonomy) {
            // Ensure taxonomy is available in ACF location rules
            add_filter('acf/location/rule_values/taxonomy', function($choices) use ($taxonomy) {
                if (!isset($choices[$taxonomy])) {
                    $taxonomy_obj = get_taxonomy($taxonomy);
                    if ($taxonomy_obj) {
                        $choices[$taxonomy] = $taxonomy_obj->labels->name;
                    }
                }
                return $choices;
            });
        }
        
        hp_log('Registered ACF support for taxonomies: ' . implode(', ', $taxonomies), 'info', 'TAXONOMIES');
    }
    
    /**
     * Get registered taxonomies
     */
    public function get_taxonomies() {
        return array_keys($this->taxonomies_config);
    }
    
    /**
     * Get taxonomy configuration
     */
    public function get_taxonomy_config($taxonomy) {
        return $this->taxonomies_config[$taxonomy] ?? null;
    }
}