<?php
/**
 * Post Types Class - FIXED VERSION
 * 
 * Handles registration and management of custom post types for Happy Place
 *
 * @package HappyPlace\Core
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Post_Types {
    
    private static $instance = null;
    private $config_file;
    private $post_types_config = [];
    
    /**
     * Default post type configurations
     */
    private array $default_post_types = [
        'listing' => [
            'labels' => [
                'name' => 'Listings',
                'singular_name' => 'Listing',
                'add_new' => 'Add New Listing',
                'add_new_item' => 'Add New Listing',
                'edit_item' => 'Edit Listing',
                'new_item' => 'New Listing',
                'view_item' => 'View Listing',
                'view_items' => 'View Listings',
                'search_items' => 'Search Listings',
                'not_found' => 'No listings found',
                'not_found_in_trash' => 'No listings found in Trash',
                'all_items' => 'All Listings',
                'menu_name' => 'Listings',
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'rest_base' => 'listings',
            'menu_position' => 5,
            'menu_icon' => 'dashicons-admin-home',
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'has_archive' => 'listings',
            'rewrite' => ['slug' => 'listing', 'with_front' => false],
        ],
        'agent' => [
            'labels' => [
                'name' => 'Agents',
                'singular_name' => 'Agent',
                'add_new' => 'Add New Agent',
                'add_new_item' => 'Add New Agent',
                'edit_item' => 'Edit Agent',
                'new_item' => 'New Agent',
                'view_item' => 'View Agent',
                'search_items' => 'Search Agents',
                'not_found' => 'No agents found',
                'all_items' => 'All Agents',
                'menu_name' => 'Agents',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_position' => 6,
            'menu_icon' => 'dashicons-businessperson',
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'has_archive' => 'agents',
            'rewrite' => ['slug' => 'agent', 'with_front' => false],
        ],
        'open_house' => [
            'labels' => [
                'name' => 'Open Houses',
                'singular_name' => 'Open House',
                'add_new' => 'Add New Open House',
                'add_new_item' => 'Add New Open House',
                'edit_item' => 'Edit Open House',
                'new_item' => 'New Open House',
                'view_item' => 'View Open House',
                'search_items' => 'Search Open Houses',
                'not_found' => 'No open houses found',
                'all_items' => 'All Open Houses',
                'menu_name' => 'Open Houses',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_position' => 7,
            'menu_icon' => 'dashicons-calendar-alt',
            'supports' => ['title', 'editor', 'custom-fields'],
            'has_archive' => 'open-houses',
            'rewrite' => ['slug' => 'open-house', 'with_front' => false],
        ]
    ];
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
            hp_log('Post_Types instance created', 'debug', 'POST_TYPES');
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->config_file = HP_PLUGIN_DIR . 'includes/config/post-types.json';
        $this->load_config();
        hp_log('Post_Types constructor called', 'debug', 'POST_TYPES');
    }

    /**
     * Load post types configuration from JSON file
     */
    private function load_config() {
        if (file_exists($this->config_file)) {
            $config_data = json_decode(file_get_contents($this->config_file), true);
            if ($config_data && isset($config_data['post_types'])) {
                $this->post_types_config = $config_data['post_types'];
                hp_log('Post types loaded from JSON configuration', 'info', 'POST_TYPES');
                return;
            }
        }
        
        // Fallback to default configuration
        $this->post_types_config = $this->default_post_types;
        hp_log('Post types loaded from default configuration', 'warning', 'POST_TYPES');
    }
    
    /**
     * Initialize component
     */
    public function init(): void {
        hp_log('Post_Types init() method called', 'info', 'POST_TYPES');
        
        // Register post types immediately
        $this->register_post_types();
        
        // Add capabilities
        $this->add_capabilities();
        
        // Add hooks
        add_filter('enter_title_here', [$this, 'change_title_placeholders']);
        add_filter('post_updated_messages', [$this, 'update_post_messages']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        
        // FIXED: Simplified save handler that doesn't interfere with ACF
        add_action('save_post_listing', [$this, 'save_listing_meta'], 10, 3);
        
        // FIXED: ACF integration hook - runs after ACF saves
        add_action('acf/save_post', [$this, 'after_acf_save'], 20);
        
        hp_log('Post Types component initialized', 'debug', 'POST_TYPES');
    }
    
    /**
     * Register all custom post types
     */
    public function register_post_types(): void {
        hp_log('Registering post types', 'info', 'POST_TYPES');
        
        foreach ($this->post_types_config as $post_type => $config) {
            // Handle both JSON and array configs
            $labels = $config['labels'] ?? [];
            $args = $config;
            
            if (isset($args['args'])) {
                $args = $config['args'];
            }
            
            // Remove labels from args
            unset($args['labels']);
            
            // Apply text domain
            foreach ($labels as $key => $label) {
                $labels[$key] = __($label, 'happy-place');
            }
            
            $args['labels'] = $labels;
            
            $result = register_post_type($post_type, $args);
            
            if (is_wp_error($result)) {
                hp_log("Failed to register post type {$post_type}: " . $result->get_error_message(), 'error', 'POST_TYPES');
            } else {
                hp_log("Successfully registered post type: {$post_type}", 'info', 'POST_TYPES');
            }
        }
        
        // Flush rewrite rules if needed
        if (get_option('hp_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('hp_flush_rewrite_rules');
            hp_log('Rewrite rules flushed', 'info', 'POST_TYPES');
        }
    }
    
    /**
     * Add custom capabilities
     */
    public function add_capabilities(): void {
        $admin_role = get_role('administrator');
        if (!$admin_role) {
            return;
        }
        
        // Add listing capabilities
        $caps = [
            'edit_listing',
            'edit_listings',
            'edit_others_listings',
            'publish_listings',
            'read_private_listings',
            'delete_listings',
        ];
        
        foreach ($caps as $cap) {
            $admin_role->add_cap($cap);
        }
        
        hp_log('Capabilities added', 'debug', 'POST_TYPES');
    }
    
    /**
     * Change title placeholders
     */
    public function change_title_placeholders($title) {
        $screen = get_current_screen();
        
        if (!$screen) {
            return $title;
        }
        
        switch ($screen->post_type) {
            case 'listing':
                $title = __('Enter property address or title', 'happy-place');
                break;
            case 'agent':
                $title = __('Enter agent name', 'happy-place');
                break;
            case 'open_house':
                $title = __('Enter open house title', 'happy-place');
                break;
        }
        
        return $title;
    }
    
    /**
     * Update post messages
     */
    public function update_post_messages($messages) {
        global $post;
        
        $post_types = [
            'listing' => __('Listing', 'happy-place'),
            'agent' => __('Agent', 'happy-place'),
            'open_house' => __('Open House', 'happy-place'),
        ];
        
        foreach ($post_types as $post_type => $label) {
            $messages[$post_type] = [
                0  => '',
                1  => sprintf(__('%s updated.', 'happy-place'), $label),
                2  => __('Custom field updated.', 'happy-place'),
                3  => __('Custom field deleted.', 'happy-place'),
                4  => sprintf(__('%s updated.', 'happy-place'), $label),
                5  => isset($_GET['revision']) ? sprintf(__('%s restored to revision from %s', 'happy-place'), $label, wp_post_revision_title((int) $_GET['revision'], false)) : false,
                6  => sprintf(__('%s published.', 'happy-place'), $label),
                7  => sprintf(__('%s saved.', 'happy-place'), $label),
                8  => sprintf(__('%s submitted.', 'happy-place'), $label),
                9  => sprintf(__('%s scheduled.', 'happy-place'), $label),
                10 => sprintf(__('%s draft updated.', 'happy-place'), $label),
            ];
        }
        
        return $messages;
    }
    
    /**
     * Add meta boxes for post types
     */
    public function add_meta_boxes(): void {
        // Add listing status meta box
        add_meta_box(
            'hp_listing_status',
            __('Listing Status', 'happy-place'),
            [$this, 'listing_status_meta_box'],
            'listing',
            'side',
            'high'
        );
        
        // Add quick stats meta box
        add_meta_box(
            'hp_listing_stats',
            __('Quick Stats', 'happy-place'),
            [$this, 'listing_stats_meta_box'],
            'listing',
            'side',
            'default'
        );
    }
    
    /**
     * Listing status meta box callback
     */
    public function listing_status_meta_box($post): void {
        wp_nonce_field('hp_listing_status_meta_box', 'hp_listing_status_nonce');
        
        $status = get_post_meta($post->ID, '_listing_status', true) ?: 'active';
        $featured = get_post_meta($post->ID, '_featured_listing', true);
        
        ?>
        <p>
            <label for="listing_status"><?php _e('Status:', 'happy-place'); ?></label>
            <select id="listing_status" name="listing_status" style="width: 100%;">
                <option value="active" <?php selected($status, 'active'); ?>><?php _e('Active', 'happy-place'); ?></option>
                <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'happy-place'); ?></option>
                <option value="sold" <?php selected($status, 'sold'); ?>><?php _e('Sold', 'happy-place'); ?></option>
                <option value="coming_soon" <?php selected($status, 'coming_soon'); ?>><?php _e('Coming Soon', 'happy-place'); ?></option>
            </select>
        </p>
        <p>
            <label>
                <input type="checkbox" name="featured_listing" value="1" <?php checked($featured, '1'); ?>>
                <?php _e('Featured Listing', 'happy-place'); ?>
            </label>
        </p>
        <?php
    }
    
    /**
     * Listing stats meta box callback
     */
    public function listing_stats_meta_box($post): void {
        $views = get_post_meta($post->ID, '_listing_views', true) ?: 0;
        $inquiries = get_post_meta($post->ID, '_listing_inquiries', true) ?: 0;
        $created = get_the_date('M j, Y', $post);
        
        ?>
        <p><strong><?php _e('Views:', 'happy-place'); ?></strong> <?php echo number_format($views); ?></p>
        <p><strong><?php _e('Inquiries:', 'happy-place'); ?></strong> <?php echo number_format($inquiries); ?></p>
        <p><strong><?php _e('Listed:', 'happy-place'); ?></strong> <?php echo $created; ?></p>
        <?php
    }
    
/**
 * FIXED: Save listing meta - only handles custom meta boxes, not ACF fields
 */
public function save_listing_meta($post_id, $post, $update): void {
    // Skip autosaves and revisions
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id)) {
        hp_log("Skipping save - autosave or revision", 'debug', 'POST_TYPES');
        return;
    }
    
    // Skip if not our form (check for our nonce)
    if (!isset($_POST['hp_listing_status_nonce'])) {
        hp_log("Skipping save - no HP nonce found", 'debug', 'POST_TYPES');
        return;
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['hp_listing_status_nonce'], 'hp_listing_status_meta_box')) {
        hp_log("Nonce verification failed", 'warning', 'POST_TYPES');
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        hp_log("User lacks permission to edit post", 'warning', 'POST_TYPES');
        return;
    }
    
    hp_log("Processing custom meta save for post {$post_id}", 'info', 'POST_TYPES');
    
    // Save listing status
    if (isset($_POST['listing_status'])) {
        $status = sanitize_text_field($_POST['listing_status']);
        update_post_meta($post_id, '_listing_status', $status);
        hp_log("Saved listing status: {$status}", 'debug', 'POST_TYPES');
    }
    
    // Save featured status
    if (isset($_POST['featured_listing']) && $_POST['featured_listing'] === '1') {
        update_post_meta($post_id, '_featured_listing', '1');
        hp_log("Saved featured status: Yes", 'debug', 'POST_TYPES');
    } else {
        update_post_meta($post_id, '_featured_listing', '0');
        hp_log("Saved featured status: No", 'debug', 'POST_TYPES');
    }
    
    hp_log("Custom meta save completed for post {$post_id}", 'info', 'POST_TYPES');
}

    
/**
 * FIXED: After ACF save - sync fields and process calculated values
 */
public function after_acf_save($post_id): void {
    // Skip if not a post ID (could be options page, user, term, etc)
    if (!is_numeric($post_id)) {
        return;
    }
    
    // Skip if not a listing
    if (get_post_type($post_id) !== 'listing') {
        return;
    }
    
    hp_log("Processing after ACF save for listing {$post_id}", 'info', 'POST_TYPES');
    
    // Get all ACF fields for this post
    $fields = get_field_objects($post_id);
    
    if ($fields) {
        hp_log("Found " . count($fields) . " ACF fields for post {$post_id}", 'debug', 'POST_TYPES');
        
        // Process specific fields that need special handling
        foreach ($fields as $field) {
            $field_name = $field['name'];
            $field_value = $field['value'];
            
            // Log important fields
            if (in_array($field_name, ['listing_price', 'bedrooms', 'bathrooms_full', 'bathrooms_half', 'square_feet'])) {
                hp_log("Field {$field_name}: " . (is_array($field_value) ? json_encode($field_value) : $field_value), 'debug', 'POST_TYPES');
            }
            
            // Sync specific fields to post meta for queries
            switch ($field_name) {
                case 'listing_price':
                    update_post_meta($post_id, '_price', $field_value);
                    update_post_meta($post_id, 'price', $field_value);
                    break;
                    
                case 'is_featured':
                    update_post_meta($post_id, '_featured_listing', $field_value ? '1' : '0');
                    break;
                    
                case 'mls_number':
                    update_post_meta($post_id, '_mls_number', $field_value);
                    break;
                    
                case 'property_title':
                    // Update post title if set
                    if (!empty($field_value)) {
                        wp_update_post([
                            'ID' => $post_id,
                            'post_title' => $field_value
                        ]);
                    }
                    break;
            }
        }
        
        // Calculate total bathrooms
        $full = get_field('bathrooms_full', $post_id) ?: 0;
        $half = get_field('bathrooms_half', $post_id) ?: 0;
        $total_bathrooms = floatval($full) + (floatval($half) * 0.5);
        update_post_meta($post_id, '_total_bathrooms', $total_bathrooms);
        hp_log("Calculated total bathrooms: {$total_bathrooms}", 'debug', 'POST_TYPES');
    } else {
        hp_log("No ACF fields found for post {$post_id}", 'warning', 'POST_TYPES');
    }
    
    // Use Field Mapper if available
    if (class_exists('HappyPlace\\Core\\Field_Mapper')) {
        \HappyPlace\Core\Field_Mapper::sync_fields($post_id);
        hp_log("Field Mapper sync completed", 'debug', 'POST_TYPES');
    }
    
    // Clear caches
    wp_cache_delete($post_id, 'posts');
    wp_cache_delete($post_id, 'post_meta');
    clean_post_cache($post_id);
    
    hp_log("After ACF save completed for listing {$post_id}", 'info', 'POST_TYPES');
}
}