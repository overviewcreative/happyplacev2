<?php
/**
 * Post Types Class
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
     * Default post type configurations (fallback if JSON not available)
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
                'archives' => 'Listing Archives',
                'attributes' => 'Listing Attributes',
                'insert_into_item' => 'Insert into listing',
                'uploaded_to_this_item' => 'Uploaded to this listing',
                'featured_image' => 'Featured Image',
                'set_featured_image' => 'Set featured image',
                'remove_featured_image' => 'Remove featured image',
                'use_featured_image' => 'Use as featured image',
                'menu_name' => 'Listings',
                'filter_items_list' => 'Filter listings list',
                'filter_by_date' => 'Filter by date',
                'items_list_navigation' => 'Listings list navigation',
                'items_list' => 'Listings list',
                'item_published' => 'Listing published',
                'item_published_privately' => 'Listing published privately',
                'item_reverted_to_draft' => 'Listing reverted to draft',
                'item_scheduled' => 'Listing scheduled',
                'item_updated' => 'Listing updated',
            ],
            'args' => [
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'show_in_admin_bar' => true,
                'show_in_rest' => true,
                'rest_base' => 'listings',
                'rest_controller_class' => 'WP_REST_Posts_Controller',
                'menu_position' => 5,
                'menu_icon' => 'dashicons-admin-home',
                'capability_type' => ['listing', 'listings'],
                'map_meta_cap' => true,
                'hierarchical' => false,
                'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author', 'custom-fields'],
                'has_archive' => 'listings',
                'rewrite' => ['slug' => 'listing', 'with_front' => false],
                'query_var' => true,
                'can_export' => true,
                'delete_with_user' => false,
            ]
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
                'view_items' => 'View Agents',
                'search_items' => 'Search Agents',
                'not_found' => 'No agents found',
                'not_found_in_trash' => 'No agents found in Trash',
                'all_items' => 'All Agents',
                'archives' => 'Agent Archives',
                'attributes' => 'Agent Attributes',
                'insert_into_item' => 'Insert into agent profile',
                'uploaded_to_this_item' => 'Uploaded to this agent',
                'featured_image' => 'Profile Photo',
                'set_featured_image' => 'Set profile photo',
                'remove_featured_image' => 'Remove profile photo',
                'use_featured_image' => 'Use as profile photo',
                'menu_name' => 'Agents',
                'filter_items_list' => 'Filter agents list',
                'items_list_navigation' => 'Agents list navigation',
                'items_list' => 'Agents list',
                'item_published' => 'Agent published',
                'item_updated' => 'Agent updated',
            ],
            'args' => [
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'show_in_admin_bar' => true,
                'show_in_rest' => true,
                'rest_base' => 'agents',
                'menu_position' => 6,
                'menu_icon' => 'dashicons-businessperson',
                'capability_type' => ['agent', 'agents'],
                'map_meta_cap' => true,
                'hierarchical' => false,
                'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author'],
                'has_archive' => 'agents',
                'rewrite' => ['slug' => 'agent', 'with_front' => false],
                'query_var' => true,
                'can_export' => true,
            ]
        ],
        
        'community' => [
            'labels' => [
                'name' => 'Communities',
                'singular_name' => 'Community',
                'add_new' => 'Add New Community',
                'add_new_item' => 'Add New Community',
                'edit_item' => 'Edit Community',
                'new_item' => 'New Community',
                'view_item' => 'View Community',
                'view_items' => 'View Communities',
                'search_items' => 'Search Communities',
                'not_found' => 'No communities found',
                'not_found_in_trash' => 'No communities found in Trash',
                'all_items' => 'All Communities',
                'archives' => 'Community Archives',
                'menu_name' => 'Communities',
                'item_published' => 'Community published',
                'item_updated' => 'Community updated',
            ],
            'args' => [
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
                'rest_base' => 'communities',
                'menu_position' => 7,
                'menu_icon' => 'dashicons-building',
                'capability_type' => 'post',
                'hierarchical' => false,
                'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
                'has_archive' => 'communities',
                'rewrite' => ['slug' => 'community', 'with_front' => false],
                'query_var' => true,
                'can_export' => true,
            ]
        ],
        
        'city' => [
            'labels' => [
                'name' => 'Cities',
                'singular_name' => 'City',
                'add_new' => 'Add New City',
                'add_new_item' => 'Add New City',
                'edit_item' => 'Edit City',
                'new_item' => 'New City',
                'view_item' => 'View City',
                'view_items' => 'View Cities',
                'search_items' => 'Search Cities',
                'not_found' => 'No cities found',
                'all_items' => 'All Cities',
                'menu_name' => 'Cities',
            ],
            'args' => [
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_rest' => true,
                'rest_base' => 'cities',
                'menu_position' => 8,
                'menu_icon' => 'dashicons-location-alt',
                'capability_type' => 'post',
                'hierarchical' => false,
                'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
                'has_archive' => 'cities',
                'rewrite' => ['slug' => 'city', 'with_front' => false],
                'query_var' => true,
            ]
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
            'args' => [
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_rest' => true,
                'rest_base' => 'open-houses',
                'menu_position' => 9,
                'menu_icon' => 'dashicons-calendar-alt',
                'capability_type' => ['open_house', 'open_houses'],
                'map_meta_cap' => true,
                'hierarchical' => false,
                'supports' => ['title', 'editor', 'author', 'custom-fields'],
                'has_archive' => 'open-houses',
                'rewrite' => ['slug' => 'open-house', 'with_front' => false],
                'query_var' => true,
            ]
        ],
        
        'local_place' => [
            'labels' => [
                'name' => 'Local Places',
                'singular_name' => 'Local Place',
                'add_new' => 'Add New Local Place',
                'add_new_item' => 'Add New Local Place',
                'edit_item' => 'Edit Local Place',
                'new_item' => 'New Local Place',
                'view_item' => 'View Local Place',
                'search_items' => 'Search Local Places',
                'not_found' => 'No local places found',
                'all_items' => 'All Local Places',
                'menu_name' => 'Local Places',
            ],
            'args' => [
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_rest' => true,
                'rest_base' => 'local-places',
                'menu_position' => 10,
                'menu_icon' => 'dashicons-store',
                'capability_type' => 'post',
                'hierarchical' => false,
                'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
                'has_archive' => 'local-places',
                'rewrite' => ['slug' => 'local-place', 'with_front' => false],
                'query_var' => true,
            ]
        ],
        
        'team' => [
            'labels' => [
                'name' => 'Team Members',
                'singular_name' => 'Team Member',
                'add_new' => 'Add New Team Member',
                'add_new_item' => 'Add New Team Member',
                'edit_item' => 'Edit Team Member',
                'new_item' => 'New Team Member',
                'view_item' => 'View Team Member',
                'search_items' => 'Search Team Members',
                'not_found' => 'No team members found',
                'all_items' => 'All Team Members',
                'menu_name' => 'Team',
            ],
            'args' => [
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_rest' => true,
                'rest_base' => 'team',
                'menu_position' => 11,
                'menu_icon' => 'dashicons-groups',
                'capability_type' => 'post',
                'hierarchical' => false,
                'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
                'has_archive' => 'team',
                'rewrite' => ['slug' => 'team-member', 'with_front' => false],
                'query_var' => true,
            ]
        ],
        
        'transaction' => [
            'labels' => [
                'name' => 'Transactions',
                'singular_name' => 'Transaction',
                'add_new' => 'Add New Transaction',
                'add_new_item' => 'Add New Transaction',
                'edit_item' => 'Edit Transaction',
                'new_item' => 'New Transaction',
                'view_item' => 'View Transaction',
                'search_items' => 'Search Transactions',
                'not_found' => 'No transactions found',
                'all_items' => 'All Transactions',
                'menu_name' => 'Transactions',
            ],
            'args' => [
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_rest' => false,
                'menu_position' => 12,
                'menu_icon' => 'dashicons-money-alt',
                'capability_type' => ['transaction', 'transactions'],
                'map_meta_cap' => true,
                'hierarchical' => false,
                'supports' => ['title', 'editor', 'author', 'custom-fields'],
                'has_archive' => false,
                'rewrite' => false,
                'query_var' => true,
            ]
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
     * Initialize post types
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
        hp_log('Post types loaded from default configuration (JSON not found)', 'warning', 'POST_TYPES');
    }
    
    /**
     * Initialize component
     */
    public function init(): void {
        hp_log('Post_Types init() method called', 'info', 'POST_TYPES');
        
        // Register post types immediately - timing is critical for ACF integration
        $this->register_post_types();
        
        // Schedule other hooks for later execution
        add_action('init', [$this, 'add_capabilities'], 15);
        add_filter('enter_title_here', [$this, 'change_title_placeholders']);
        add_filter('post_updated_messages', [$this, 'update_post_messages']);
        
        // Add post type specific hooks
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_boxes']);
        
        // Ensure ACF knows about our post types
        add_action('acf/init', [$this, 'register_acf_post_type_support'], 5);
        
        hp_log('Post Types component initialized', 'debug', 'POST_TYPES');
    }
    
    /**
     * Register all custom post types
     */
    public function register_post_types(): void {
        hp_log('register_post_types() method called', 'info', 'POST_TYPES');
        
        foreach ($this->post_types_config as $post_type => $config) {
            // For JSON config, the structure is different - everything is in the root
            if (isset($config['labels']) && isset($config['args'])) {
                // Old structure with separate labels and args
                $labels = $config['labels'];
                $args = $config['args'];
            } else {
                // New JSON structure - labels and other args are mixed
                $labels = $config['labels'] ?? [];
                $args = $config;
                unset($args['labels']); // Remove labels from args since we handle them separately
            }
            
            // Apply text domain to labels
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
        
        // Flush rewrite rules on first activation
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
        $capabilities = [
            'listing' => ['edit_listing', 'edit_listings', 'edit_others_listings', 'publish_listings', 'read_private_listings', 'delete_listing', 'delete_listings', 'delete_private_listings', 'delete_published_listings', 'delete_others_listings', 'edit_private_listings', 'edit_published_listings'],
            'agent' => ['edit_agent', 'edit_agents', 'edit_others_agents', 'publish_agents', 'read_private_agents', 'delete_agent', 'delete_agents', 'delete_private_agents', 'delete_published_agents', 'delete_others_agents', 'edit_private_agents', 'edit_published_agents'],
            'open_house' => ['edit_open_house', 'edit_open_houses', 'edit_others_open_houses', 'publish_open_houses', 'read_private_open_houses', 'delete_open_house', 'delete_open_houses', 'delete_private_open_houses', 'delete_published_open_houses', 'delete_others_open_houses', 'edit_private_open_houses', 'edit_published_open_houses'],
            'transaction' => ['edit_transaction', 'edit_transactions', 'edit_others_transactions', 'publish_transactions', 'read_private_transactions', 'delete_transaction', 'delete_transactions', 'delete_private_transactions', 'delete_published_transactions', 'delete_others_transactions', 'edit_private_transactions', 'edit_published_transactions'],
        ];
        
        // Add capabilities to administrator and editor roles
        $roles = ['administrator', 'editor'];
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($capabilities as $post_type => $caps) {
                    foreach ($caps as $cap) {
                        $role->add_cap($cap);
                    }
                }
            }
        }
        
        // Add specific capabilities for real estate agent role
        $agent_role = get_role('hp_agent');
        if (!$agent_role) {
            add_role('hp_agent', __('Real Estate Agent', 'happy-place'), [
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
            ]);
            $agent_role = get_role('hp_agent');
        }
        
        if ($agent_role) {
            // Agents can manage their own listings and open houses
            $agent_caps = [
                'edit_listing', 'edit_listings', 'publish_listings', 'delete_listing', 'delete_listings',
                'edit_open_house', 'edit_open_houses', 'publish_open_houses', 'delete_open_house', 'delete_open_houses',
                'edit_agent', 'read_private_listings', 'read_private_open_houses'
            ];
            
            foreach ($agent_caps as $cap) {
                $agent_role->add_cap($cap);
            }
        }
    }
    
    /**
     * Change title placeholders for custom post types
     */
    public function change_title_placeholders(string $title): string {
        global $post_type;
        
        $placeholders = [
            'listing' => __('Enter property address', 'happy-place'),
            'agent' => __('Enter agent name', 'happy-place'),
            'community' => __('Enter community name', 'happy-place'),
            'city' => __('Enter city name', 'happy-place'),
            'open_house' => __('Enter open house title', 'happy-place'),
            'local_place' => __('Enter place name', 'happy-place'),
            'team' => __('Enter team member name', 'happy-place'),
            'transaction' => __('Enter transaction title', 'happy-place'),
        ];
        
        return $placeholders[$post_type] ?? $title;
    }
    
    /**
     * Update post messages for custom post types
     */
    public function update_post_messages(array $messages): array {
        global $post, $post_ID;
        
        $post_type_labels = [
            'listing' => __('Listing', 'happy-place'),
            'agent' => __('Agent', 'happy-place'),
            'community' => __('Community', 'happy-place'),
            'city' => __('City', 'happy-place'),
            'open_house' => __('Open House', 'happy-place'),
            'local_place' => __('Local Place', 'happy-place'),
            'team' => __('Team Member', 'happy-place'),
            'transaction' => __('Transaction', 'happy-place'),
        ];
        
        foreach ($post_type_labels as $post_type => $label) {
            $permalink = get_permalink($post_ID);
            
            $messages[$post_type] = [
                0  => '', // Unused. Messages start at index 1.
                1  => sprintf(__('%s updated. <a href="%s">View %s</a>', 'happy-place'), $label, esc_url($permalink), strtolower($label)),
                2  => __('Custom field updated.', 'happy-place'),
                3  => __('Custom field deleted.', 'happy-place'),
                4  => sprintf(__('%s updated.', 'happy-place'), $label),
                5  => isset($_GET['revision']) ? sprintf(__('%s restored to revision from %s', 'happy-place'), $label, wp_post_revision_title((int) $_GET['revision'], false)) : false,
                6  => sprintf(__('%s published. <a href="%s">View %s</a>', 'happy-place'), $label, esc_url($permalink), strtolower($label)),
                7  => sprintf(__('%s saved.', 'happy-place'), $label),
                8  => sprintf(__('%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'happy-place'), $label, esc_url(add_query_arg('preview', 'true', $permalink)), strtolower($label)),
                9  => sprintf(__('%s scheduled for: <strong>%s</strong>. <a target="_blank" href="%s">Preview %s</a>', 'happy-place'), $label, date_i18n(__('M j, Y @ G:i', 'happy-place'), strtotime($post->post_date)), esc_url($permalink), strtolower($label)),
                10 => sprintf(__('%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'happy-place'), $label, esc_url(add_query_arg('preview', 'true', $permalink)), strtolower($label)),
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
        
        // Add quick stats meta box for listings
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
     * Save meta box data
     */
    public function save_meta_boxes(int $post_id): void {
        // Check if our nonce is set and verify it
        if (!isset($_POST['hp_listing_status_nonce']) || 
            !wp_verify_nonce($_POST['hp_listing_status_nonce'], 'hp_listing_status_meta_box')) {
            return;
        }
        
        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save listing status
        if (isset($_POST['listing_status'])) {
            update_post_meta($post_id, '_listing_status', sanitize_text_field($_POST['listing_status']));
        }
        
        // Save featured status
        $featured = isset($_POST['featured_listing']) ? '1' : '0';
        update_post_meta($post_id, '_featured_listing', $featured);
    }
    
    /**
     * Register ACF post type support
     */
    public function register_acf_post_type_support(): void {
        if (!function_exists('acf_get_setting')) {
            return;
        }
        
        $post_types = array_keys($this->post_types_config);
        
        foreach ($post_types as $post_type) {
            // Ensure post type is available in ACF location rules
            add_filter('acf/location/rule_values/post_type', function($choices) use ($post_type) {
                if (!isset($choices[$post_type])) {
                    $post_type_obj = get_post_type_object($post_type);
                    if ($post_type_obj) {
                        $choices[$post_type] = $post_type_obj->labels->name;
                    }
                }
                return $choices;
            });
        }
        
        hp_log('Registered ACF support for post types: ' . implode(', ', $post_types), 'info', 'POST_TYPES');
    }
    
    /**
     * Get registered post types
     */
    public function get_post_types(): array {
        return array_keys($this->post_types_config);
    }
}