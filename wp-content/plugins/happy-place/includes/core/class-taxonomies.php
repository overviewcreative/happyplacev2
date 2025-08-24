<?php
/**
 * Taxonomies Class
 * 
 * Handles registration and management of custom taxonomies
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
 * 
 * @since 4.0.0
 */
class Taxonomies {
    
    /**
     * Single instance
     * 
     * @var Taxonomies|null
     */
    private static ?Taxonomies $instance = null;
    
    /**
     * Configuration file path
     * 
     * @var string
     */
    private string $config_file;
    
    /**
     * Taxonomies configuration
     * 
     * @var array
     */
    private array $taxonomies_config = [];
    
    /**
     * Default taxonomy configurations
     * 
     * @var array
     */
    private array $default_taxonomies = [
        'property_type' => [
            'object_type' => ['listing'],
            'labels' => [
                'name' => 'Property Types',
                'singular_name' => 'Property Type',
                'search_items' => 'Search Property Types',
                'all_items' => 'All Property Types',
                'parent_item' => 'Parent Property Type',
                'parent_item_colon' => 'Parent Property Type:',
                'edit_item' => 'Edit Property Type',
                'update_item' => 'Update Property Type',
                'add_new_item' => 'Add New Property Type',
                'new_item_name' => 'New Property Type Name',
                'menu_name' => 'Property Types',
            ],
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'property-type',
                'with_front' => false,
                'hierarchical' => true,
            ],
        ],
        'property_status' => [
            'object_type' => ['listing'],
            'labels' => [
                'name' => 'Property Status',
                'singular_name' => 'Property Status',
                'search_items' => 'Search Property Status',
                'all_items' => 'All Property Status',
                'edit_item' => 'Edit Property Status',
                'update_item' => 'Update Property Status',
                'add_new_item' => 'Add New Property Status',
                'new_item_name' => 'New Property Status',
                'menu_name' => 'Property Status',
            ],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'property-status',
                'with_front' => false,
            ],
        ],
        'location' => [
            'object_type' => ['listing'],
            'labels' => [
                'name' => 'Locations',
                'singular_name' => 'Location',
                'search_items' => 'Search Locations',
                'all_items' => 'All Locations',
                'parent_item' => 'Parent Location',
                'parent_item_colon' => 'Parent Location:',
                'edit_item' => 'Edit Location',
                'update_item' => 'Update Location',
                'add_new_item' => 'Add New Location',
                'new_item_name' => 'New Location Name',
                'menu_name' => 'Locations',
            ],
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'location',
                'with_front' => false,
                'hierarchical' => true,
            ],
        ],
        'property_feature' => [
            'object_type' => ['listing'],
            'labels' => [
                'name' => 'Property Features',
                'singular_name' => 'Property Feature',
                'search_items' => 'Search Property Features',
                'all_items' => 'All Property Features',
                'edit_item' => 'Edit Property Feature',
                'update_item' => 'Update Property Feature',
                'add_new_item' => 'Add New Property Feature',
                'new_item_name' => 'New Property Feature',
                'menu_name' => 'Property Features',
                'popular_items' => 'Popular Features',
                'separate_items_with_commas' => 'Separate features with commas',
                'add_or_remove_items' => 'Add or remove features',
                'choose_from_most_used' => 'Choose from the most used features',
            ],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'property-feature',
                'with_front' => false,
            ],
        ],
        'agent_specialty' => [
            'object_type' => ['agent'],
            'labels' => [
                'name' => 'Agent Specialties',
                'singular_name' => 'Agent Specialty',
                'search_items' => 'Search Specialties',
                'all_items' => 'All Specialties',
                'edit_item' => 'Edit Specialty',
                'update_item' => 'Update Specialty',
                'add_new_item' => 'Add New Specialty',
                'new_item_name' => 'New Specialty',
                'menu_name' => 'Specialties',
            ],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'agent-specialty',
                'with_front' => false,
            ],
        ],
        'community_type' => [
            'object_type' => ['community'],
            'labels' => [
                'name' => 'Community Types',
                'singular_name' => 'Community Type',
                'search_items' => 'Search Community Types',
                'all_items' => 'All Community Types',
                'edit_item' => 'Edit Community Type',
                'update_item' => 'Update Community Type',
                'add_new_item' => 'Add New Community Type',
                'new_item_name' => 'New Community Type',
                'menu_name' => 'Community Types',
            ],
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'community-type',
                'with_front' => false,
            ],
        ],
    ];
    
    /**
     * Get instance
     * 
     * @return Taxonomies
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
        $this->config_file = HP_CONFIG_DIR . 'taxonomies.json';
        $this->load_config();
    }
    
    /**
     * Load configuration
     * 
     * @return void
     */
    private function load_config(): void {
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
        hp_log('Taxonomies loaded from default configuration', 'info', 'TAXONOMIES');
    }
    
    /**
     * Initialize
     * 
     * @return void
     */
    public function init(): void {
        // Register taxonomies early
        $this->register_taxonomies();
        
        // Add default terms
        add_action('init', [$this, 'add_default_terms'], 15);
        
        // Set up hooks
        $this->setup_hooks();
        
        hp_log('Taxonomies initialized', 'info', 'TAXONOMIES');
    }
    
    /**
     * Register taxonomies
     * 
     * @return void
     */
    public function register_taxonomies(): void {
        foreach ($this->taxonomies_config as $taxonomy => $config) {
            // Extract object types
            $object_type = $config['object_type'] ?? ['post'];
            unset($config['object_type']);
            
            // Apply text domain to labels
            if (isset($config['labels'])) {
                foreach ($config['labels'] as $key => $label) {
                    $config['labels'][$key] = __($label, 'happy-place');
                }
            }
            
            $result = register_taxonomy($taxonomy, $object_type, $config);
            
            if (is_wp_error($result)) {
                hp_log("Failed to register taxonomy {$taxonomy}: " . $result->get_error_message(), 'error', 'TAXONOMIES');
            } else {
                hp_log("Registered taxonomy: {$taxonomy}", 'debug', 'TAXONOMIES');
            }
        }
    }
    
    /**
     * Add default terms
     * 
     * @return void
     */
    public function add_default_terms(): void {
        // Property types
        $property_types = [
            'Single Family Home',
            'Condo',
            'Townhouse',
            'Multi-Family',
            'Land',
            'Commercial',
            'Rental',
        ];
        
        foreach ($property_types as $type) {
            if (!term_exists($type, 'property_type')) {
                wp_insert_term($type, 'property_type');
            }
        }
        
        // Property status
        $property_statuses = [
            'For Sale',
            'For Rent',
            'Pending',
            'Sold',
            'Off Market',
        ];
        
        foreach ($property_statuses as $status) {
            if (!term_exists($status, 'property_status')) {
                wp_insert_term($status, 'property_status');
            }
        }
        
        // Property features
        $property_features = [
            'Air Conditioning',
            'Hardwood Floors',
            'Swimming Pool',
            'Garage',
            'Fireplace',
            'Garden',
            'Security System',
            'Walk-in Closet',
            'Home Office',
            'Basement',
            'Balcony',
            'Waterfront',
        ];
        
        foreach ($property_features as $feature) {
            if (!term_exists($feature, 'property_feature')) {
                wp_insert_term($feature, 'property_feature');
            }
        }
        
        // Agent specialties
        $agent_specialties = [
            'Buyer\'s Agent',
            'Listing Agent',
            'Relocation',
            'Foreclosure',
            'Short-Sale',
            'Consulting',
            'Property Management',
            'Commercial',
            'Luxury Homes',
            'First-Time Buyers',
        ];
        
        foreach ($agent_specialties as $specialty) {
            if (!term_exists($specialty, 'agent_specialty')) {
                wp_insert_term($specialty, 'agent_specialty');
            }
        }
        
        // Community types
        $community_types = [
            'Urban',
            'Suburban',
            'Rural',
            'Gated Community',
            'Golf Community',
            'Waterfront',
            'Active Adult',
            'Master Planned',
        ];
        
        foreach ($community_types as $type) {
            if (!term_exists($type, 'community_type')) {
                wp_insert_term($type, 'community_type');
            }
        }
    }
    
    /**
     * Setup hooks
     * 
     * @return void
     */
    private function setup_hooks(): void {
        // Add custom fields to taxonomies
        add_action('property_type_add_form_fields', [$this, 'add_taxonomy_fields']);
        add_action('property_type_edit_form_fields', [$this, 'edit_taxonomy_fields']);
        add_action('created_property_type', [$this, 'save_taxonomy_fields']);
        add_action('edited_property_type', [$this, 'save_taxonomy_fields']);
        
        add_action('location_add_form_fields', [$this, 'add_location_fields']);
        add_action('location_edit_form_fields', [$this, 'edit_location_fields']);
        add_action('created_location', [$this, 'save_location_fields']);
        add_action('edited_location', [$this, 'save_location_fields']);
        
        // Add columns to taxonomy list tables
        add_filter('manage_edit-property_type_columns', [$this, 'add_taxonomy_columns']);
        add_filter('manage_property_type_custom_column', [$this, 'display_taxonomy_columns'], 10, 3);
        
        add_filter('manage_edit-location_columns', [$this, 'add_location_columns']);
        add_filter('manage_location_custom_column', [$this, 'display_location_columns'], 10, 3);
    }
    
    /**
     * Add taxonomy fields
     * 
     * @return void
     */
    public function add_taxonomy_fields(): void {
        ?>
        <div class="form-field">
            <label for="property_type_icon"><?php _e('Icon', 'happy-place'); ?></label>
            <input type="text" name="property_type_icon" id="property_type_icon" value="">
            <p class="description"><?php _e('Icon class or URL', 'happy-place'); ?></p>
        </div>
        <div class="form-field">
            <label for="property_type_color"><?php _e('Color', 'happy-place'); ?></label>
            <input type="color" name="property_type_color" id="property_type_color" value="#000000">
            <p class="description"><?php _e('Display color', 'happy-place'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Edit taxonomy fields
     * 
     * @param \WP_Term $term
     * @return void
     */
    public function edit_taxonomy_fields(\WP_Term $term): void {
        $icon = get_term_meta($term->term_id, 'icon', true);
        $color = get_term_meta($term->term_id, 'color', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="property_type_icon"><?php _e('Icon', 'happy-place'); ?></label>
            </th>
            <td>
                <input type="text" name="property_type_icon" id="property_type_icon" value="<?php echo esc_attr($icon); ?>">
                <p class="description"><?php _e('Icon class or URL', 'happy-place'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="property_type_color"><?php _e('Color', 'happy-place'); ?></label>
            </th>
            <td>
                <input type="color" name="property_type_color" id="property_type_color" value="<?php echo esc_attr($color ?: '#000000'); ?>">
                <p class="description"><?php _e('Display color', 'happy-place'); ?></p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save taxonomy fields
     * 
     * @param int $term_id
     * @return void
     */
    public function save_taxonomy_fields(int $term_id): void {
        if (isset($_POST['property_type_icon'])) {
            update_term_meta($term_id, 'icon', sanitize_text_field($_POST['property_type_icon']));
        }
        
        if (isset($_POST['property_type_color'])) {
            update_term_meta($term_id, 'color', sanitize_hex_color($_POST['property_type_color']));
        }
    }
    
    /**
     * Add location fields
     * 
     * @return void
     */
    public function add_location_fields(): void {
        ?>
        <div class="form-field">
            <label for="location_latitude"><?php _e('Latitude', 'happy-place'); ?></label>
            <input type="text" name="location_latitude" id="location_latitude" value="">
        </div>
        <div class="form-field">
            <label for="location_longitude"><?php _e('Longitude', 'happy-place'); ?></label>
            <input type="text" name="location_longitude" id="location_longitude" value="">
        </div>
        <div class="form-field">
            <label for="location_featured_image"><?php _e('Featured Image URL', 'happy-place'); ?></label>
            <input type="text" name="location_featured_image" id="location_featured_image" value="">
        </div>
        <?php
    }
    
    /**
     * Edit location fields
     * 
     * @param \WP_Term $term
     * @return void
     */
    public function edit_location_fields(\WP_Term $term): void {
        $latitude = get_term_meta($term->term_id, 'latitude', true);
        $longitude = get_term_meta($term->term_id, 'longitude', true);
        $featured_image = get_term_meta($term->term_id, 'featured_image', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="location_latitude"><?php _e('Latitude', 'happy-place'); ?></label>
            </th>
            <td>
                <input type="text" name="location_latitude" id="location_latitude" value="<?php echo esc_attr($latitude); ?>">
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="location_longitude"><?php _e('Longitude', 'happy-place'); ?></label>
            </th>
            <td>
                <input type="text" name="location_longitude" id="location_longitude" value="<?php echo esc_attr($longitude); ?>">
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="location_featured_image"><?php _e('Featured Image URL', 'happy-place'); ?></label>
            </th>
            <td>
                <input type="text" name="location_featured_image" id="location_featured_image" value="<?php echo esc_attr($featured_image); ?>">
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save location fields
     * 
     * @param int $term_id
     * @return void
     */
    public function save_location_fields(int $term_id): void {
        if (isset($_POST['location_latitude'])) {
            update_term_meta($term_id, 'latitude', sanitize_text_field($_POST['location_latitude']));
        }
        
        if (isset($_POST['location_longitude'])) {
            update_term_meta($term_id, 'longitude', sanitize_text_field($_POST['location_longitude']));
        }
        
        if (isset($_POST['location_featured_image'])) {
            update_term_meta($term_id, 'featured_image', esc_url_raw($_POST['location_featured_image']));
        }
    }
    
    /**
     * Add taxonomy columns
     * 
     * @param array $columns
     * @return array
     */
    public function add_taxonomy_columns(array $columns): array {
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            if ($key === 'name') {
                $new_columns[$key] = $value;
                $new_columns['icon'] = __('Icon', 'happy-place');
                $new_columns['color'] = __('Color', 'happy-place');
            } else {
                $new_columns[$key] = $value;
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Display taxonomy columns
     * 
     * @param string $content
     * @param string $column_name
     * @param int $term_id
     * @return string
     */
    public function display_taxonomy_columns(string $content, string $column_name, int $term_id): string {
        switch ($column_name) {
            case 'icon':
                $icon = get_term_meta($term_id, 'icon', true);
                if ($icon) {
                    if (strpos($icon, 'http') === 0) {
                        $content = '<img src="' . esc_url($icon) . '" width="20" height="20">';
                    } else {
                        $content = '<i class="' . esc_attr($icon) . '"></i>';
                    }
                }
                break;
                
            case 'color':
                $color = get_term_meta($term_id, 'color', true);
                if ($color) {
                    $content = '<span style="display:inline-block;width:20px;height:20px;background-color:' . esc_attr($color) . ';border:1px solid #ccc;"></span>';
                }
                break;
        }
        
        return $content;
    }
    
    /**
     * Add location columns
     * 
     * @param array $columns
     * @return array
     */
    public function add_location_columns(array $columns): array {
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            if ($key === 'name') {
                $new_columns[$key] = $value;
                $new_columns['coordinates'] = __('Coordinates', 'happy-place');
                $new_columns['listings'] = __('Listings', 'happy-place');
            } else {
                $new_columns[$key] = $value;
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Display location columns
     * 
     * @param string $content
     * @param string $column_name
     * @param int $term_id
     * @return string
     */
    public function display_location_columns(string $content, string $column_name, int $term_id): string {
        switch ($column_name) {
            case 'coordinates':
                $latitude = get_term_meta($term_id, 'latitude', true);
                $longitude = get_term_meta($term_id, 'longitude', true);
                if ($latitude && $longitude) {
                    $content = $latitude . ', ' . $longitude;
                }
                break;
                
            case 'listings':
                $term = get_term($term_id, 'location');
                if ($term && !is_wp_error($term)) {
                    $content = $term->count;
                }
                break;
        }
        
        return $content;
    }
    
    /**
     * Get taxonomy configuration
     * 
     * @param string $taxonomy
     * @return array|null
     */
    public function get_taxonomy_config(string $taxonomy): ?array {
        return $this->taxonomies_config[$taxonomy] ?? null;
    }
    
    /**
     * Get all taxonomies
     * 
     * @return array
     */
    public function get_taxonomies(): array {
        return array_keys($this->taxonomies_config);
    }
}