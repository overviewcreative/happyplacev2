<?php
/**
 * Force Register Post Types
 * 
 * This file directly registers all Happy Place custom post types
 * Add this to your theme's functions.php or run it as a must-use plugin
 */

// Register Happy Place Custom Post Types
add_action('init', 'hp_force_register_post_types', 5);

function hp_force_register_post_types() {
    
    // 1. LISTING POST TYPE
    if (!post_type_exists('listing')) {
        register_post_type('listing', [
            'labels' => [
                'name' => 'Listings',
                'singular_name' => 'Listing',
                'add_new' => 'Add New Listing',
                'add_new_item' => 'Add New Listing',
                'edit_item' => 'Edit Listing',
                'new_item' => 'New Listing',
                'view_item' => 'View Listing',
                'search_items' => 'Search Listings',
                'not_found' => 'No listings found',
                'not_found_in_trash' => 'No listings found in Trash',
                'menu_name' => 'Listings',
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'menu_icon' => 'dashicons-admin-home',
            'menu_position' => 5,
            'rewrite' => ['slug' => 'listing', 'with_front' => false],
        ]);
    }
    
    // 2. AGENT POST TYPE
    if (!post_type_exists('agent')) {
        register_post_type('agent', [
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
                'not_found_in_trash' => 'No agents found in Trash',
                'menu_name' => 'Agents',
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'menu_icon' => 'dashicons-businessperson',
            'menu_position' => 6,
            'rewrite' => ['slug' => 'agent', 'with_front' => false],
        ]);
    }
    
    // 3. COMMUNITY POST TYPE
    if (!post_type_exists('community')) {
        register_post_type('community', [
            'labels' => [
                'name' => 'Communities',
                'singular_name' => 'Community',
                'add_new' => 'Add New Community',
                'add_new_item' => 'Add New Community',
                'edit_item' => 'Edit Community',
                'new_item' => 'New Community',
                'view_item' => 'View Community',
                'search_items' => 'Search Communities',
                'not_found' => 'No communities found',
                'not_found_in_trash' => 'No communities found in Trash',
                'menu_name' => 'Communities',
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'menu_icon' => 'dashicons-building',
            'menu_position' => 7,
            'rewrite' => ['slug' => 'community', 'with_front' => false],
        ]);
    }
    
    // 4. CITY POST TYPE
    if (!post_type_exists('city')) {
        register_post_type('city', [
            'labels' => [
                'name' => 'Cities',
                'singular_name' => 'City',
                'add_new' => 'Add New City',
                'add_new_item' => 'Add New City',
                'edit_item' => 'Edit City',
                'new_item' => 'New City',
                'view_item' => 'View City',
                'search_items' => 'Search Cities',
                'not_found' => 'No cities found',
                'menu_name' => 'Cities',
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'menu_icon' => 'dashicons-location-alt',
            'menu_position' => 8,
            'rewrite' => ['slug' => 'city', 'with_front' => false],
        ]);
    }
    
    // 5. OPEN HOUSE POST TYPE
    if (!post_type_exists('open_house')) {
        register_post_type('open_house', [
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
                'menu_name' => 'Open Houses',
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'author', 'custom-fields'],
            'menu_icon' => 'dashicons-calendar-alt',
            'menu_position' => 9,
            'rewrite' => ['slug' => 'open-house', 'with_front' => false],
        ]);
    }
    
    // 6. LOCAL PLACE POST TYPE
    if (!post_type_exists('local_place')) {
        register_post_type('local_place', [
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
                'menu_name' => 'Local Places',
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'menu_icon' => 'dashicons-store',
            'menu_position' => 10,
            'rewrite' => ['slug' => 'local-place', 'with_front' => false],
        ]);
    }
    
    // 7. TEAM POST TYPE
    if (!post_type_exists('team')) {
        register_post_type('team', [
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
                'menu_name' => 'Team',
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'menu_icon' => 'dashicons-groups',
            'menu_position' => 11,
            'rewrite' => ['slug' => 'team-member', 'with_front' => false],
        ]);
    }
    
    // 8. TRANSACTION POST TYPE
    if (!post_type_exists('transaction')) {
        register_post_type('transaction', [
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
                'menu_name' => 'Transactions',
            ],
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => false,
            'supports' => ['title', 'editor', 'author', 'custom-fields'],
            'menu_icon' => 'dashicons-money-alt',
            'menu_position' => 12,
            'capability_type' => 'post',
        ]);
    }
    
    // Flush rewrite rules after registering
    flush_rewrite_rules();
}

// Also register taxonomies
add_action('init', 'hp_force_register_taxonomies', 4);

function hp_force_register_taxonomies() {
    
    // Property Type Taxonomy
    if (!taxonomy_exists('property_type')) {
        register_taxonomy('property_type', ['listing'], [
            'labels' => [
                'name' => 'Property Types',
                'singular_name' => 'Property Type',
                'search_items' => 'Search Property Types',
                'all_items' => 'All Property Types',
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
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'property-type'],
        ]);
    }
    
    // Property Status Taxonomy
    if (!taxonomy_exists('property_status')) {
        register_taxonomy('property_status', ['listing'], [
            'labels' => [
                'name' => 'Property Status',
                'singular_name' => 'Status',
                'search_items' => 'Search Statuses',
                'all_items' => 'All Statuses',
                'edit_item' => 'Edit Status',
                'update_item' => 'Update Status',
                'add_new_item' => 'Add New Status',
                'new_item_name' => 'New Status Name',
                'menu_name' => 'Status',
            ],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'status'],
        ]);
    }
    
    // Property Features Taxonomy
    if (!taxonomy_exists('property_features')) {
        register_taxonomy('property_features', ['listing'], [
            'labels' => [
                'name' => 'Property Features',
                'singular_name' => 'Feature',
                'search_items' => 'Search Features',
                'all_items' => 'All Features',
                'edit_item' => 'Edit Feature',
                'update_item' => 'Update Feature',
                'add_new_item' => 'Add New Feature',
                'new_item_name' => 'New Feature Name',
                'menu_name' => 'Features',
            ],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => false,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'feature'],
        ]);
    }
}

// Success message for admin
add_action('admin_notices', 'hp_post_types_registered_notice');
function hp_post_types_registered_notice() {
    if (isset($_GET['hp_registered']) && $_GET['hp_registered'] == '1') {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Happy Place:</strong> Custom post types have been registered successfully!</p>
        </div>
        <?php
    }
}