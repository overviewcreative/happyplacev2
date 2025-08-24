<?php
/**
 * Post Types Class
 * 
 * Handles registration and management of custom post types
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
 * Post Types Class
 * 
 * @since 4.0.0
 */
class Post_Types {
    
    /**
     * Single instance
     * 
     * @var Post_Types|null
     */
    private static ?Post_Types $instance = null;
    
    /**
     * Configuration file path
     * 
     * @var string
     */
    private string $config_file;
    
    /**
     * Post types configuration
     * 
     * @var array
     */
    private array $post_types_config = [];
    
    /**
     * Default post type configurations
     * 
     * @var array
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
                'name_admin_bar' => 'Listing',
                'archives' => 'Listing Archives',
                'attributes' => 'Listing Attributes',
                'parent_item_colon' => 'Parent Listing:',
                'insert_into_item' => 'Insert into listing',
                'uploaded_to_this_item' => 'Uploaded to this listing',
                'featured_image' => 'Featured Image',
                'set_featured_image' => 'Set featured image',
                'remove_featured_image' => 'Remove featured image',
                'use_featured_image' => 'Use as featured image',
                'filter_items_list' => 'Filter listings list',
                'items_list_navigation' => 'Listings list navigation',
                'items_list' => 'Listings list',
            ],
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
            'capability_type' => 'post',
            'capabilities' => [
                'edit_post' => 'edit_listing',
                'edit_posts' => 'edit_listings',
                'edit_others_posts' => 'edit_others_listings',
                'publish_posts' => 'publish_listings',
                'read_post' => 'read_listing',
                'read_private_posts' => 'read_private_listings',
                'delete_post' => 'delete_listing',
            ],
            'map_meta_cap' => true,
            'hierarchical' => false,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes'],
            'has_archive' => 'listings',
            'rewrite' => [
                'slug' => 'listing',
                'with_front' => false,
                'feeds' => true,
                'pages' => true,
            ],
            'query_var' => true,
            'can_export' => true,
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
                'menu_name' => 'Agents',
                'name_admin_bar' => 'Agent',
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'rest_base' => 'agents',
            'menu_position' => 6,
            'menu_icon' => 'dashicons-businessperson',
            'capability_type' => 'post',
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'],
            'has_archive' => 'agents',
            'rewrite' => [
                'slug' => 'agent',
                'with_front' => false,
            ],
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
                'view_items' => 'View Open Houses',
                'search_items' => 'Search Open Houses',
                'not_found' => 'No open houses found',
                'not_found_in_trash' => 'No open houses found in Trash',
                'all_items' => 'All Open Houses',
                'menu_name' => 'Open Houses',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_position' => 7,
            'menu_icon' => 'dashicons-calendar-alt',
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'has_archive' => 'open-houses',
            'rewrite' => [
                'slug' => 'open-house',
                'with_front' => false,
            ],
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
                'menu_name' => 'Communities',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_position' => 8,
            'menu_icon' => 'dashicons-location',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'has_archive' => 'communities',
            'rewrite' => [
                'slug' => 'community',
                'with_front' => false,
            ],
        ],
        'lead' => [
            'labels' => [
                'name' => 'Leads',
                'singular_name' => 'Lead',
                'add_new' => 'Add New Lead',
                'add_new_item' => 'Add New Lead',
                'edit_item' => 'Edit Lead',
                'new_item' => 'New Lead',
                'view_item' => 'View Lead',
                'view_items' => 'View Leads',
                'search_items' => 'Search Leads',
                'not_found' => 'No leads found',
                'not_found_in_trash' => 'No leads found in Trash',
                'all_items' => 'All Leads',
                'menu_name' => 'Leads',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_position' => 9,
            'menu_icon' => 'dashicons-groups',
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'create_leads',
            ],
            'map_meta_cap' => true,
            'supports' => ['title', 'custom-fields'],
            'has_archive' => false,
            'rewrite' => false,
            'publicly_queryable' => false,
        ],
    ];
    
    /**
     * Get instance
     * 
     * @return Post_Types
     */
    public static function get_instance(): Post_Types {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->config_file = HP_CONFIG_DIR . 'post-types.json';
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
            if ($config_data && isset($config_data['post_types'])) {
                $this->post_types_config = $config_data['post_types'];
                hp_log('Post types loaded from JSON configuration', 'info', 'POST_TYPES');
                return;
            }
        }
        
        // Fallback to default configuration
        $this->post_types_config = $this->default_post_types;
        hp_log('Post types loaded from default configuration', 'info', 'POST_TYPES');
    }
    
    /**
     * Initialize
     * 
     * @return void
     */
    public function init(): void {
        // Register post types
        $this->register_post_types();
        
        // Add capabilities
        $this->add_capabilities();
        
        // Set up hooks
        $this->setup_hooks();
        
        // Flush rewrite rules if needed
        if (get_option('hp_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('hp_flush_rewrite_rules');
        }
        
        hp_log('Post types initialized', 'info', 'POST_TYPES');
    }
    
    /**
     * Register post types
     * 
     * @return void
     */
    public function register_post_types(): void {
        foreach ($this->post_types_config as $post_type => $config) {
            // Apply text domain to labels
            if (isset($config['labels'])) {
                foreach ($config['labels'] as $key => $label) {
                    $config['labels'][$key] = __($label, 'happy-place');
                }
            }
            
            $result = register_post_type($post_type, $config);
            
            if (is_wp_error($result)) {
                hp_log("Failed to register post type {$post_type}: " . $result->get_error_message(), 'error', 'POST_TYPES');
            } else {
                hp_log("Registered post type: {$post_type}", 'debug', 'POST_TYPES');
            }
        }
    }
    
    /**
     * Add capabilities
     * 
     * @return void
     */
    private function add_capabilities(): void {
        $roles = ['administrator', 'editor'];
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            
            if (!$role) {
                continue;
            }
            
            // Listing capabilities
            $role->add_cap('edit_listing');
            $role->add_cap('edit_listings');
            $role->add_cap('edit_others_listings');
            $role->add_cap('publish_listings');
            $role->add_cap('read_listing');
            $role->add_cap('read_private_listings');
            $role->add_cap('delete_listing');
            $role->add_cap('delete_listings');
            
            // Lead capabilities
            if ($role_name === 'administrator') {
                $role->add_cap('create_leads');
                $role->add_cap('edit_leads');
                $role->add_cap('delete_leads');
            }
        }
        
        // Create agent role if it doesn't exist
        if (!get_role('agent')) {
            add_role('agent', 'Real Estate Agent', [
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'edit_listing' => true,
                'edit_listings' => true,
                'publish_listings' => true,
                'read_listing' => true,
                'delete_listing' => true,
                'upload_files' => true,
            ]);
        }
    }
    
    /**
     * Setup hooks
     * 
     * @return void
     */
    private function setup_hooks(): void {
        // Admin columns
        add_filter('manage_listing_posts_columns', [$this, 'add_listing_columns']);
        add_action('manage_listing_posts_custom_column', [$this, 'display_listing_columns'], 10, 2);
        add_filter('manage_edit-listing_sortable_columns', [$this, 'make_listing_columns_sortable']);
        
        add_filter('manage_agent_posts_columns', [$this, 'add_agent_columns']);
        add_action('manage_agent_posts_custom_column', [$this, 'display_agent_columns'], 10, 2);
        
        add_filter('manage_lead_posts_columns', [$this, 'add_lead_columns']);
        add_action('manage_lead_posts_custom_column', [$this, 'display_lead_columns'], 10, 2);
        
        // Title placeholders
        add_filter('enter_title_here', [$this, 'change_title_placeholders']);
        
        // Post updated messages
        add_filter('post_updated_messages', [$this, 'update_post_messages']);
        
        // Save post actions
        add_action('save_post_listing', [$this, 'save_listing_meta'], 10, 3);
        add_action('save_post_agent', [$this, 'save_agent_meta'], 10, 3);
        
        // Pre-get-posts modifications
        add_action('pre_get_posts', [$this, 'modify_queries']);
        
        // Admin menu modifications
        add_action('admin_menu', [$this, 'modify_admin_menu'], 999);
    }
    
    /**
     * Add listing columns
     * 
     * @param array $columns
     * @return array
     */
    public function add_listing_columns(array $columns): array {
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            if ($key === 'title') {
                $new_columns[$key] = $value;
                $new_columns['price'] = __('Price', 'happy-place');
                $new_columns['status'] = __('Status', 'happy-place');
                $new_columns['bedrooms'] = __('Beds', 'happy-place');
                $new_columns['bathrooms'] = __('Baths', 'happy-place');
                $new_columns['sqft'] = __('Sq Ft', 'happy-place');
            } elseif ($key === 'date') {
                $new_columns['agent'] = __('Agent', 'happy-place');
                $new_columns[$key] = $value;
            } else {
                $new_columns[$key] = $value;
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Display listing columns
     * 
     * @param string $column
     * @param int $post_id
     * @return void
     */
    public function display_listing_columns(string $column, int $post_id): void {
        switch ($column) {
            case 'price':
                $price = get_field('price', $post_id);
                if ($price) {
                    echo '$' . number_format($price);
                } else {
                    echo '—';
                }
                break;
                
            case 'status':
                $status = get_field('status', $post_id) ?: 'active';
                $status_labels = [
                    'active' => '<span class="status-badge status-active">Active</span>',
                    'pending' => '<span class="status-badge status-pending">Pending</span>',
                    'sold' => '<span class="status-badge status-sold">Sold</span>',
                    'inactive' => '<span class="status-badge status-inactive">Inactive</span>',
                ];
                echo $status_labels[$status] ?? $status;
                break;
                
            case 'bedrooms':
                echo get_field('bedrooms', $post_id) ?: '—';
                break;
                
            case 'bathrooms':
                echo get_field('bathrooms', $post_id) ?: '—';
                break;
                
            case 'sqft':
                $sqft = get_field('square_feet', $post_id);
                if ($sqft) {
                    echo number_format($sqft);
                } else {
                    echo '—';
                }
                break;
                
            case 'agent':
                $agent_id = get_field('listing_agent', $post_id);
                if ($agent_id) {
                    $agent = get_user($agent_id);
                    if ($agent) {
                        echo esc_html($agent->display_name);
                    }
                } else {
                    echo '—';
                }
                break;
        }
    }
    
    /**
     * Make listing columns sortable
     * 
     * @param array $columns
     * @return array
     */
    public function make_listing_columns_sortable(array $columns): array {
        $columns['price'] = 'price';
        $columns['status'] = 'status';
        $columns['bedrooms'] = 'bedrooms';
        $columns['bathrooms'] = 'bathrooms';
        $columns['sqft'] = 'sqft';
        
        return $columns;
    }
    
    /**
     * Add agent columns
     * 
     * @param array $columns
     * @return array
     */
    public function add_agent_columns(array $columns): array {
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            if ($key === 'title') {
                $new_columns[$key] = $value;
                $new_columns['email'] = __('Email', 'happy-place');
                $new_columns['phone'] = __('Phone', 'happy-place');
                $new_columns['listings'] = __('Listings', 'happy-place');
            } else {
                $new_columns[$key] = $value;
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Display agent columns
     * 
     * @param string $column
     * @param int $post_id
     * @return void
     */
    public function display_agent_columns(string $column, int $post_id): void {
        switch ($column) {
            case 'email':
                echo get_field('email', $post_id) ?: '—';
                break;
                
            case 'phone':
                echo get_field('phone', $post_id) ?: '—';
                break;
                
            case 'listings':
                $count = wp_count_posts('listing');
                $agent_listings = get_posts([
                    'post_type' => 'listing',
                    'meta_key' => 'listing_agent',
                    'meta_value' => $post_id,
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                ]);
                echo count($agent_listings);
                break;
        }
    }
    
    /**
     * Add lead columns
     * 
     * @param array $columns
     * @return array
     */
    public function add_lead_columns(array $columns): array {
        return [
            'cb' => $columns['cb'],
            'title' => __('Name', 'happy-place'),
            'email' => __('Email', 'happy-place'),
            'phone' => __('Phone', 'happy-place'),
            'source' => __('Source', 'happy-place'),
            'status' => __('Status', 'happy-place'),
            'agent' => __('Assigned To', 'happy-place'),
            'date' => __('Date', 'happy-place'),
        ];
    }
    
    /**
     * Display lead columns
     * 
     * @param string $column
     * @param int $post_id
     * @return void
     */
    public function display_lead_columns(string $column, int $post_id): void {
        switch ($column) {
            case 'email':
                echo get_field('email', $post_id) ?: '—';
                break;
                
            case 'phone':
                echo get_field('phone', $post_id) ?: '—';
                break;
                
            case 'source':
                echo get_field('source', $post_id) ?: '—';
                break;
                
            case 'status':
                $status = get_field('status', $post_id) ?: 'new';
                $status_labels = [
                    'new' => '<span class="status-badge status-new">New</span>',
                    'contacted' => '<span class="status-badge status-contacted">Contacted</span>',
                    'qualified' => '<span class="status-badge status-qualified">Qualified</span>',
                    'converted' => '<span class="status-badge status-converted">Converted</span>',
                    'lost' => '<span class="status-badge status-lost">Lost</span>',
                ];
                echo $status_labels[$status] ?? $status;
                break;
                
            case 'agent':
                $agent_id = get_field('assigned_agent', $post_id);
                if ($agent_id) {
                    $agent = get_post($agent_id);
                    if ($agent) {
                        echo esc_html($agent->post_title);
                    }
                } else {
                    echo '—';
                }
                break;
        }
    }
    
    /**
     * Change title placeholders
     * 
     * @param string $title
     * @return string
     */
    public function change_title_placeholders(string $title): string {
        $screen = get_current_screen();
        
        if (!$screen) {
            return $title;
        }
        
        switch ($screen->post_type) {
            case 'listing':
                $title = __('Enter property address', 'happy-place');
                break;
            case 'agent':
                $title = __('Enter agent name', 'happy-place');
                break;
            case 'open_house':
                $title = __('Enter open house title', 'happy-place');
                break;
            case 'community':
                $title = __('Enter community name', 'happy-place');
                break;
            case 'lead':
                $title = __('Enter lead name', 'happy-place');
                break;
        }
        
        return $title;
    }
    
    /**
     * Update post messages
     * 
     * @param array $messages
     * @return array
     */
    public function update_post_messages(array $messages): array {
        global $post;
        
        $post_types = [
            'listing' => __('Listing', 'happy-place'),
            'agent' => __('Agent', 'happy-place'),
            'open_house' => __('Open House', 'happy-place'),
            'community' => __('Community', 'happy-place'),
            'lead' => __('Lead', 'happy-place'),
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
     * Save listing meta
     * 
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     * @return void
     */
    public function save_listing_meta(int $post_id, \WP_Post $post, bool $update): void {
        // Skip autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Track analytics
        if (function_exists('hp_service')) {
            $db = hp_service('database');
            if ($db) {
                $action = $update ? 'listing_updated' : 'listing_created';
                $db->log_activity($action, 'listing', $post_id);
            }
        }
        
        // Update search index
        do_action('hp_update_listing_search_index', $post_id);
        
        hp_log("Listing saved: {$post_id}", 'debug', 'POST_TYPES');
    }
    
    /**
     * Save agent meta
     * 
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     * @return void
     */
    public function save_agent_meta(int $post_id, \WP_Post $post, bool $update): void {
        // Skip autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Sync with user if email provided
        $email = get_field('email', $post_id);
        if ($email) {
            $user = get_user_by('email', $email);
            if (!$user) {
                // Create user account for agent
                $user_id = wp_create_user(
                    sanitize_user($email),
                    wp_generate_password(),
                    $email
                );
                
                if (!is_wp_error($user_id)) {
                    $user = get_user_by('id', $user_id);
                    $user->set_role('agent');
                    
                    // Update user meta
                    update_user_meta($user_id, 'agent_post_id', $post_id);
                    update_field('user_id', $user_id, $post_id);
                }
            }
        }
        
        hp_log("Agent saved: {$post_id}", 'debug', 'POST_TYPES');
    }
    
    /**
     * Modify queries
     * 
     * @param \WP_Query $query
     * @return void
     */
    public function modify_queries(\WP_Query $query): void {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        
        // Modify listing archive query
        if ($query->is_post_type_archive('listing')) {
            $query->set('posts_per_page', 12);
            $query->set('orderby', 'date');
            $query->set('order', 'DESC');
            
            // Only show active listings
            $query->set('meta_query', [
                [
                    'key' => 'status',
                    'value' => 'active',
                    'compare' => '=',
                ],
            ]);
        }
        
        // Modify agent archive query
        if ($query->is_post_type_archive('agent')) {
            $query->set('posts_per_page', 12);
            $query->set('orderby', 'title');
            $query->set('order', 'ASC');
        }
    }
    
    /**
     * Modify admin menu
     * 
     * @return void
     */
    public function modify_admin_menu(): void {
        global $submenu;
        
        // Add separator after listings
        if (isset($submenu['edit.php?post_type=listing'])) {
            $submenu['edit.php?post_type=listing'][] = ['', 'read', 'separator', '', 'wp-menu-separator'];
        }
    }
    
    /**
     * Get post type configuration
     * 
     * @param string $post_type
     * @return array|null
     */
    public function get_post_type_config(string $post_type): ?array {
        return $this->post_types_config[$post_type] ?? null;
    }
    
    /**
     * Get all post types
     * 
     * @return array
     */
    public function get_post_types(): array {
        return array_keys($this->post_types_config);
    }
}