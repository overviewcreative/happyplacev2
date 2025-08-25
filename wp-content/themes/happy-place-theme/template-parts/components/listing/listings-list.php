<?php
/**
 * Listings List Component - List display of listing cards
 *
 * @package HappyPlaceTheme
 */

// Default attributes specific to listings
$listings_args = wp_parse_args($args ?? [], [
    'posts' => [],
    'query_args' => [],
    'show_search' => true,
    'show_filters' => true,
    'show_sort' => true,
    'spacing' => 4,
    'dividers' => true,
    'show_excerpts' => true
]);

// Merge with base card-list arguments
$list_args = array_merge($listings_args, [
    'post_type' => 'listing',
    'search_form' => $listings_args['show_search'],
    'filter_controls' => $listings_args['show_filters'],
    'sort_controls' => $listings_args['show_sort'],
    'empty_message' => 'No properties found matching your criteria.',
    'card_args' => [
        'layout' => 'list',
        'image_position' => 'left',
        'show_price' => true,
        'show_meta' => true,
        'show_excerpt' => $listings_args['show_excerpts'],
        'show_actions' => true,
        'actions' => [
            [
                'text' => 'View Details',
                'color' => 'primary',
                'icon' => 'fa-eye',
                'url' => get_permalink()
            ]
        ]
    ]
]);

// Set default query args for listings if not provided
if (empty($list_args['posts']) && empty($list_args['query_args'])) {
    $list_args['query_args'] = [
        'post_type' => 'listing',
        'post_status' => 'publish',
        'posts_per_page' => 10,
        'meta_query' => [
            [
                'key' => 'listing_status',
                'value' => 'active',
                'compare' => '='
            ]
        ]
    ];
}

// Load the base card-list component
hph_component('card-list', $list_args);
?>