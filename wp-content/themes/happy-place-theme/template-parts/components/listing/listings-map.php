<?php
/**
 * Listings Map Component - Map display of listing cards
 *
 * @package HappyPlaceTheme
 */

// Default attributes specific to listings
$listings_args = wp_parse_args($args ?? [], [
    'posts' => [],
    'query_args' => [],
    'layout' => 'sidebar',
    'sidebar_position' => 'left',
    'map_height' => '600px',
    'show_search' => true,
    'show_filters' => true,
    'clustering' => true,
    'auto_fit_bounds' => true
]);

// Merge with base card-map arguments
$map_args = array_merge($listings_args, [
    'post_type' => 'listing',
    'search_form' => $listings_args['show_search'],
    'filter_controls' => $listings_args['show_filters'],
    'empty_message' => 'No properties found with location data.',
    'marker_color' => '#3b82f6',
    'active_marker_color' => '#ef4444',
    'card_args' => [
        'layout' => 'compact',
        'size' => 'small',
        'show_price' => true,
        'show_meta' => true,
        'show_actions' => true,
        'show_excerpt' => false,
        'actions' => [
            [
                'text' => 'View',
                'color' => 'primary',
                'icon' => 'fa-eye',
                'url' => get_permalink()
            ]
        ]
    ]
]);

// Set default query args for listings if not provided
if (empty($map_args['posts']) && empty($map_args['query_args'])) {
    $map_args['query_args'] = [
        'post_type' => 'listing',
        'post_status' => 'publish',
        'posts_per_page' => 50,
        'meta_query' => [
            [
                'relation' => 'AND',
                [
                    'key' => 'listing_status',
                    'value' => 'active',
                    'compare' => '='
                ],
                [
                    'key' => 'latitude',
                    'value' => '',
                    'compare' => '!='
                ],
                [
                    'key' => 'longitude',
                    'value' => '',
                    'compare' => '!='
                ]
            ]
        ]
    ];
}

// Load the base card-map component
hph_component('card-map', $map_args);
?>