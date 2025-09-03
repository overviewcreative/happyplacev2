<?php
/**
 * Archive Template for Communities
 * 
 * Enhanced archive template for community listings with neighborhood information
 * Uses archive-layout system for consistency with other archive pages
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();

// Get search and filter parameters
$search_query = sanitize_text_field($_GET['s'] ?? '');
$paged = get_query_var('paged', 1);
$posts_per_page = intval($_GET['per_page'] ?? 12);
$view_mode = sanitize_text_field($_GET['view'] ?? 'grid');
$sort = sanitize_text_field($_GET['sort'] ?? 'name_asc');

// Build base query args
$query_args = [
    'post_type' => 'community',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_status' => 'publish',
    'meta_query' => ['relation' => 'AND'],
];

// Add search query if provided
if (!empty($search_query)) {
    $query_args['s'] = $search_query;
    // Also search community meta fields
    $query_args['meta_query'][] = [
        'relation' => 'OR',
        [
            'key' => 'community_description',
            'value' => $search_query,
            'compare' => 'LIKE',
        ],
        [
            'key' => 'community_features',
            'value' => $search_query,
            'compare' => 'LIKE',
        ],
    ];
}

// City filter
$city_filter = sanitize_text_field($_GET['city'] ?? '');
if (!empty($city_filter)) {
    $query_args['meta_query'][] = [
        'key' => 'city',
        'value' => $city_filter,
        'compare' => '='
    ];
}

// Price range filter
$price_range = sanitize_text_field($_GET['price_range'] ?? '');
if (!empty($price_range)) {
    switch ($price_range) {
        case 'affordable':
            $query_args['meta_query'][] = [
                'key' => 'average_home_price',
                'value' => 300000,
                'compare' => '<',
                'type' => 'NUMERIC'
            ];
            break;
        case 'moderate':
            $query_args['meta_query'][] = [
                'key' => 'average_home_price',
                'value' => [300000, 600000],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC'
            ];
            break;
        case 'upscale':
            $query_args['meta_query'][] = [
                'key' => 'average_home_price',
                'value' => [600000, 1000000],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC'
            ];
            break;
        case 'luxury':
            $query_args['meta_query'][] = [
                'key' => 'average_home_price',
                'value' => 1000000,
                'compare' => '>',
                'type' => 'NUMERIC'
            ];
            break;
    }
}

// Handle sorting
switch ($sort) {
    case 'name_desc':
        $query_args['orderby'] = 'title';
        $query_args['order'] = 'DESC';
        break;
    case 'price_asc':
        $query_args['meta_key'] = 'average_home_price';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'ASC';
        break;
    case 'price_desc':
        $query_args['meta_key'] = 'average_home_price';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'DESC';
        break;
    case 'listings_desc':
        // Would need to add a calculated field for this
        $query_args['orderby'] = 'title';
        $query_args['order'] = 'ASC';
        break;
    case 'rating_desc':
        $query_args['meta_key'] = 'school_rating';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'DESC';
        break;
    default: // name_asc
        $query_args['orderby'] = 'title';
        $query_args['order'] = 'ASC';
}

// Execute query
$community_query = new WP_Query($query_args);

// Prepare archive title and description
$archive_title = __('Communities', 'happy-place-theme');
$archive_description = __('Explore the neighborhoods and communities where we serve', 'happy-place-theme');

if (!empty($search_query)) {
    $archive_title = sprintf(__('Communities matching "%s"', 'happy-place-theme'), $search_query);
}

if ($community_query->found_posts > 0) {
    $archive_description = sprintf(
        _n('Discover %d community', 'Discover %d communities', $community_query->found_posts, 'happy-place-theme'),
        $community_query->found_posts
    );
} else {
    $archive_description = __('We don\'t have any communities matching your search criteria. Please try different filters.', 'happy-place-theme');
}

// Use archive-layout component with community-specific configuration
get_template_part('template-parts/layout/archive-layout', null, [
    // Core configuration
    'post_type' => 'community',
    'posts' => $community_query->posts,
    'title' => $archive_title,
    'description' => $archive_description,
    'total_results' => $community_query->found_posts,
    'max_pages' => $community_query->max_num_pages,
    'current_page' => $paged,
    
    // Layout configuration
    'layout' => 'contained',
    'content_width' => 'normal',
    'padding' => 'xl',
    
    // View configuration
    'view_modes' => ['grid', 'list', 'map'],
    'current_view' => $view_mode,
    'default_view' => 'grid',
    'allow_view_toggle' => true,
    
    // Search & Filter configuration
    'show_search' => true,
    'show_filters' => true,
    'show_save_search' => false,
    
    // Sort configuration  
    'sort_options' => [
        'name_asc' => __('Name A-Z', 'happy-place-theme'),
        'name_desc' => __('Name Z-A', 'happy-place-theme'),
        'price_asc' => __('Most Affordable', 'happy-place-theme'),
        'price_desc' => __('Most Expensive', 'happy-place-theme'),
        'rating_desc' => __('Best Schools', 'happy-place-theme'),
        'listings_desc' => __('Most Properties', 'happy-place-theme')
    ],
    'current_sort' => $sort,
    'default_sort' => 'name_asc',
    
    // Display configuration
    'show_header' => true,
    'show_controls' => true,
    'show_pagination' => true,
    'show_results_count' => true,
    
    // Grid configuration (communities look good in 3-column grid)
    'columns' => 3,
    'columns_tablet' => 2,
    'columns_mobile' => 1,
    'gap' => 'lg',
    
    // Pagination configuration
    'per_page' => $posts_per_page,
    'per_page_options' => [12, 24, 48],
    'pagination_style' => 'numbered',
    
    // Advanced features
    'ajax_enabled' => true,
    'lazy_loading' => true,
    'hover_effects' => true,
    
    // Card configuration for community adapter system
    'card_adapter_function' => 'get_community_card_props',
    'card_style' => 'neighborhood',
    'card_size' => 'medium',
    
    // Animation configuration
    'animation_style' => 'fade',
    'stagger_delay' => 100,
    
    // Custom query args for AJAX refreshes
    'query_args' => $query_args
]);

wp_reset_postdata();

get_footer(); 
?>
