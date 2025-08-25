<?php
/**
 * Transactions List Component - List display of transaction cards
 *
 * @package HappyPlaceTheme
 */

// Default attributes specific to transactions
$transactions_args = wp_parse_args($args ?? [], [
    'posts' => [],
    'query_args' => [],
    'agent_id' => null,
    'show_search' => true,
    'show_filters' => true,
    'show_sort' => true,
    'spacing' => 4,
    'dividers' => true,
    'show_commission' => false // Only show for authorized users
]);

// Check if current user can view commission data
$current_user_id = get_current_user_id();
$can_view_commission = false;

if ($transactions_args['agent_id'] && $current_user_id) {
    // Check if current user is the agent or admin
    $agent_user_id = get_post_meta($transactions_args['agent_id'], 'agent_user_id', true);
    $can_view_commission = ($agent_user_id == $current_user_id) || current_user_can('manage_options');
}

// Merge with base card-list arguments
$list_args = array_merge($transactions_args, [
    'post_type' => 'transaction',
    'search_form' => $transactions_args['show_search'],
    'filter_controls' => $transactions_args['show_filters'],
    'sort_controls' => $transactions_args['show_sort'],
    'empty_message' => 'No transactions found.',
    'card_args' => [
        'layout' => 'list',
        'image_position' => 'left',
        'show_price' => true,
        'show_meta' => true,
        'show_excerpt' => false,
        'show_actions' => true,
        'custom_fields' => $can_view_commission ? [
            [
                'key' => 'agent_commission',
                'label' => 'Commission'
            ]
        ] : [],
        'actions' => [
            [
                'text' => 'View Details',
                'color' => 'primary',
                'icon' => 'fa-chart-line',
                'url' => get_permalink()
            ]
        ]
    ]
]);

// Set default query args for transactions if not provided
if (empty($list_args['posts']) && empty($list_args['query_args'])) {
    $query_args = [
        'post_type' => 'transaction',
        'post_status' => 'publish',
        'posts_per_page' => 20,
        'orderby' => 'date',
        'order' => 'DESC'
    ];
    
    // Filter by agent if specified
    if ($transactions_args['agent_id']) {
        $query_args['meta_query'] = [
            'relation' => 'OR',
            [
                'key' => 'listing_agent',
                'value' => $transactions_args['agent_id'],
                'compare' => '='
            ],
            [
                'key' => 'buyer_agent',
                'value' => $transactions_args['agent_id'],
                'compare' => '='
            ]
        ];
    }
    
    $list_args['query_args'] = $query_args;
}

// Load the base card-list component
hph_component('card-list', $list_args);
?>