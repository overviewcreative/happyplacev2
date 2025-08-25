<?php
/**
 * Open Houses Grid Component - Grid display of open house cards
 *
 * @package HappyPlaceTheme
 */

// Default attributes specific to open houses
$open_houses_args = wp_parse_args($args ?? [], [
    'posts' => [],
    'query_args' => [],
    'columns' => 2,
    'show_search' => false,
    'show_filters' => true,
    'show_sort' => true,
    'card_layout' => 'default',
    'show_rsvp' => true
]);

// Merge with base card-grid arguments
$grid_args = array_merge($open_houses_args, [
    'post_type' => 'open_house',
    'search_form' => $open_houses_args['show_search'],
    'filter_controls' => $open_houses_args['show_filters'],
    'sort_controls' => $open_houses_args['show_sort'],
    'empty_message' => 'No open houses scheduled.',
    'card_args' => [
        'layout' => $open_houses_args['card_layout'],
        'show_price' => false,
        'show_meta' => true,
        'show_actions' => true,
        'actions' => $open_houses_args['show_rsvp'] ? [
            [
                'text' => 'RSVP',
                'color' => 'primary',
                'icon' => 'fa-calendar-plus',
                'url' => get_permalink()
            ],
            [
                'text' => 'View Property',
                'color' => 'outline',
                'icon' => 'fa-home'
            ]
        ] : [
            [
                'text' => 'View Details',
                'color' => 'primary',
                'icon' => 'fa-info-circle',
                'url' => get_permalink()
            ]
        ]
    ]
]);

// Set default query args for open houses if not provided
if (empty($grid_args['posts']) && empty($grid_args['query_args'])) {
    $grid_args['query_args'] = [
        'post_type' => 'open_house',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        'meta_query' => [
            [
                'key' => 'start_date',
                'value' => date('Y-m-d'),
                'compare' => '>='
            ]
        ],
        'meta_key' => 'start_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    ];
}

// Load the base card-grid component
hph_component('card-grid', $grid_args);
?>