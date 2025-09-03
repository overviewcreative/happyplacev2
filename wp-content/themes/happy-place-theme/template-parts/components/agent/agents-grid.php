<?php
/**
 * Agents Grid Component - Grid display of agent cards
 *
 * @package HappyPlaceTheme
 */

// Default attributes specific to agents
$agents_args = wp_parse_args($args ?? [], [
    'posts' => [],
    'query_args' => [],
    'columns' => 3,
    'show_search' => true,
    'show_filters' => true,
    'show_sort' => true,
    'card_layout' => 'compact',
    'show_stats' => true,
    'show_contact' => true
]);

// Merge with base card-grid arguments
$grid_args = array_merge($agents_args, [
    'post_type' => 'agent',
    'search_form' => $agents_args['show_search'],
    'filter_controls' => $agents_args['show_filters'],
    'sort_controls' => $agents_args['show_sort'],
    'empty_message' => 'No agents found.',
    'card_args' => [
        'layout' => $agents_args['card_layout'],
        'show_price' => false,
        'show_meta' => false,
        'show_actions' => $agents_args['show_contact'],
        'actions' => [
            [
                'text' => 'View Profile',
                'color' => 'primary',
                'icon' => 'fa-user',
                'url' => get_permalink()
            ],
            [
                'text' => 'Contact',
                'color' => 'outline',
                'icon' => 'fa-envelope'
            ]
        ]
    ]
]);

// Set default query args for agents if not provided
if (empty($grid_args['posts']) && empty($grid_args['query_args'])) {
    $grid_args['query_args'] = [
        'post_type' => 'agent',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        'orderby' => 'title',
        'order' => 'ASC'
    ];
}

// Load the base card-grid component
hph_component('card-grid', $grid_args);
?>