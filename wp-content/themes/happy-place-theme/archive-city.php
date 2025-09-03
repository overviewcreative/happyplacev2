<?php
/**
 * Archive Template for Cities
 * 
 * Enhanced archive template for city listings with area information
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
    'post_type' => 'city',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_status' => 'publish',
    'meta_query' => ['relation' => 'AND'],
];

// Add search query if provided
if (!empty($search_query)) {
    $query_args['s'] = $search_query;
    // Also search city meta fields
    $query_args['meta_query'][] = [
        'relation' => 'OR',
        [
            'key' => 'city_description',
            'value' => $search_query,
            'compare' => 'LIKE',
        ],
        [
            'key' => 'state',
            'value' => $search_query,
            'compare' => 'LIKE',
        ],
    ];
}

// State filter
$state = sanitize_text_field($_GET['state'] ?? '');
if (!empty($state)) {
    $query_args['meta_query'][] = [
        'key' => 'state',
        'value' => $state,
        'compare' => '='
    ];
}

// Population filter
$population_range = sanitize_text_field($_GET['population'] ?? '');
if (!empty($population_range)) {
    switch ($population_range) {
        case 'small':
            $query_args['meta_query'][] = [
                'key' => 'population',
                'value' => 10000,
                'compare' => '<',
                'type' => 'NUMERIC'
            ];
            break;
        case 'medium':
            $query_args['meta_query'][] = [
                'key' => 'population',
                'value' => [10000, 50000],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC'
            ];
            break;
        case 'large':
            $query_args['meta_query'][] = [
                'key' => 'population',
                'value' => 50000,
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
    case 'population_desc':
        $query_args['meta_key'] = 'population';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'DESC';
        break;
    case 'population_asc':
        $query_args['meta_key'] = 'population';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'ASC';
        break;
    case 'listings_desc':
        // Would need to add a calculated field for this
        $query_args['orderby'] = 'title';
        $query_args['order'] = 'ASC';
        break;
    default: // name_asc
        $query_args['orderby'] = 'title';
        $query_args['order'] = 'ASC';
}

// Execute query
$city_query = new WP_Query($query_args);

// Prepare archive title and description
$archive_title = __('Cities We Serve', 'happy-place-theme');
$archive_description = __('Discover the cities and areas where our expertise can help you find your perfect home', 'happy-place-theme');

if (!empty($search_query)) {
    $archive_title = sprintf(__('Cities matching "%s"', 'happy-place-theme'), $search_query);
}

if ($city_query->found_posts > 0) {
    $archive_description = sprintf(
        _n('Serving %d city', 'Serving %d cities', $city_query->found_posts, 'happy-place-theme'),
        $city_query->found_posts
    );
} else {
    $archive_description = __('We are always expanding our service areas. Contact us to learn about opportunities in your city.', 'happy-place-theme');
}

// Use archive-layout component with city-specific configuration
get_template_part('template-parts/layout/archive-layout', null, [
    // Core configuration
    'post_type' => 'city',
    'posts' => $city_query->posts,
    'title' => $archive_title,
    'description' => $archive_description,
    'total_results' => $city_query->found_posts,
    'max_pages' => $city_query->max_num_pages,
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
        'population_desc' => __('Largest Population', 'happy-place-theme'),
        'population_asc' => __('Smallest Population', 'happy-place-theme'),
        'listings_desc' => __('Most Properties', 'happy-place-theme')
    ],
    'current_sort' => $sort,
    'default_sort' => 'name_asc',
    
    // Display configuration
    'show_header' => true,
    'show_controls' => true,
    'show_pagination' => true,
    'show_results_count' => true,
    
    // Grid configuration (cities look good in 4-column grid)
    'columns' => 4,
    'columns_tablet' => 3,
    'columns_mobile' => 2,
    'gap' => 'lg',
    
    // Pagination configuration
    'per_page' => $posts_per_page,
    'per_page_options' => [12, 24, 48],
    'pagination_style' => 'numbered',
    
    // Advanced features
    'ajax_enabled' => true,
    'lazy_loading' => true,
    'hover_effects' => true,
    
    // Card configuration for city adapter system
    'card_adapter_function' => 'get_city_card_props',
    'card_style' => 'location',
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
