<?php
/**
 * Listings Grid Component - Grid display of listing cards
 * Utility-First Implementation
 *
 * @package HappyPlaceTheme
 */

// Parse arguments with defaults following utility-first standards
$listings_args = wp_parse_args($args ?? [], [
    'posts' => [],
    'query_args' => [],
    'columns' => 3,
    'show_search' => true,
    'show_filters' => true,
    'show_view_switcher' => true,
    'show_sort' => true,
    'card_layout' => 'default',
    'card_size' => 'medium',
    'animate' => true,
    'container_classes' => []
]);

// Build container classes using utility approach
$container_classes = array_merge([
    'hph-listings-grid',
    'hph-w-full'
], $listings_args['container_classes']);

if ($listings_args['animate']) {
    $container_classes[] = 'hph-animate-fade-in-up';
}

// Merge with base card-grid arguments
$grid_args = array_merge($listings_args, [
    'post_type' => 'listing',
    'search_form' => $listings_args['show_search'],
    'filter_controls' => $listings_args['show_filters'],
    'sort_controls' => $listings_args['show_sort'],
    'view_switcher' => $listings_args['show_view_switcher'],
    'empty_message' => __('No properties found matching your criteria.', 'happy-place-theme'),
    'container_classes' => $container_classes,
    'card_args' => [
        'layout' => $listings_args['card_layout'],
        'size' => $listings_args['card_size'],
        'show_price' => true,
        'show_meta' => true,
        'show_actions' => true,
        'actions' => [
            [
                'text' => __('View Details', 'happy-place-theme'),
                'color' => 'primary',
                'icon' => 'fa-eye'
            ]
        ]
    ]
]);

// Set default query args for listings if not provided
if (empty($grid_args['posts']) && empty($grid_args['query_args'])) {
    $grid_args['query_args'] = [
        'post_type' => 'listing',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        'meta_query' => [
            [
                'key' => 'listing_status',
                'value' => 'active',
                'compare' => '='
            ]
        ]
    ];
}

// Load the base card-grid component
hph_component('card-grid', $grid_args);
?>