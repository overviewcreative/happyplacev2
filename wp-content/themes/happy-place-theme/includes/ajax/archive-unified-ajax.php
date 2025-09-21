<?php
/**
 * HPH Unified Archive AJAX Handler
 * Single comprehensive handler for all archive functionality
 * Replaces fragmented handlers with unified approach
 *
 * @package HappyPlaceTheme
 * @version 3.0.0 - Unified System
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register unified archive AJAX handler
 */
add_action('wp_ajax_hph_load_listings_unified', 'hph_handle_unified_archive_ajax');
add_action('wp_ajax_nopriv_hph_load_listings_unified', 'hph_handle_unified_archive_ajax');

/**
 * Unified Archive AJAX Handler
 * Handles all archive operations: filtering, sorting, pagination, view switching
 */
function hph_handle_unified_archive_ajax() {
    // Verify nonce
    $nonce = sanitize_text_field($_POST['nonce'] ?? '');
    if (!wp_verify_nonce($nonce, 'hph_archive_nonce')) {
        wp_send_json_error(['message' => 'Security verification failed']);
        return;
    }

    // Load bridge functions
    $bridge_files = [
        'listing-bridge.php',
        'agent-bridge.php',
        'city-bridge.php',
        'community-bridge.php'
    ];

    foreach ($bridge_files as $file) {
        $path = get_template_directory() . '/includes/bridge/' . $file;
        if (file_exists($path)) {
            require_once $path;
        }
    }

    try {
        // Sanitize input parameters
        $post_type = sanitize_text_field($_POST['post_type'] ?? 'listing');
        $page = intval($_POST['page'] ?? 1);
        $view = sanitize_text_field($_POST['view'] ?? 'grid');
        $per_page = intval($_POST['per_page'] ?? 12);

        // Search parameters
        $search = sanitize_text_field($_POST['s'] ?? '');
        $sort = sanitize_text_field($_POST['sort'] ?? 'date_desc');

        // Price filters
        $min_price = intval($_POST['min_price'] ?? 0);
        $max_price = intval($_POST['max_price'] ?? 0);

        // Property filters
        $bedrooms = sanitize_text_field($_POST['bedrooms'] ?? '');
        $bathrooms = sanitize_text_field($_POST['bathrooms'] ?? '');
        $property_type = sanitize_text_field($_POST['property_type'] ?? '');
        $property_status = sanitize_text_field($_POST['property_status'] ?? '');

        // Location filters
        $location = sanitize_text_field($_POST['location'] ?? '');
        $listing_location = sanitize_text_field($_POST['listing_location'] ?? '');

        // Build WP_Query arguments
        $args = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => [],
            'tax_query' => []
        ];

        // Add search
        if (!empty($search)) {
            $args['s'] = $search;
        }

        // Add sorting
        switch ($sort) {
            case 'price_asc':
                $args['meta_key'] = 'listing_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'price_desc':
                $args['meta_key'] = 'listing_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'date_desc':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
            case 'date_asc':
                $args['orderby'] = 'date';
                $args['order'] = 'ASC';
                break;
            case 'title_asc':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'title_desc':
                $args['orderby'] = 'title';
                $args['order'] = 'DESC';
                break;
        }

        // Price range filter
        if ($min_price || $max_price) {
            $price_query = [
                'key' => 'listing_price',
                'type' => 'NUMERIC'
            ];

            if ($min_price && $max_price) {
                $price_query['value'] = [$min_price, $max_price];
                $price_query['compare'] = 'BETWEEN';
            } elseif ($min_price) {
                $price_query['value'] = $min_price;
                $price_query['compare'] = '>=';
            } else {
                $price_query['value'] = $max_price;
                $price_query['compare'] = '<=';
            }

            $args['meta_query'][] = $price_query;
        }

        // Bedrooms filter
        if (!empty($bedrooms)) {
            $bedrooms_value = (strpos($bedrooms, '+') !== false) ?
                intval(str_replace('+', '', $bedrooms)) :
                intval($bedrooms);

            $args['meta_query'][] = [
                'key' => 'listing_bedrooms',
                'value' => $bedrooms_value,
                'compare' => (strpos($bedrooms, '+') !== false) ? '>=' : '='
            ];
        }

        // Bathrooms filter
        if (!empty($bathrooms)) {
            $bathrooms_value = (strpos($bathrooms, '+') !== false) ?
                intval(str_replace('+', '', $bathrooms)) :
                intval($bathrooms);

            $args['meta_query'][] = [
                'key' => 'listing_bathrooms',
                'value' => $bathrooms_value,
                'compare' => (strpos($bathrooms, '+') !== false) ? '>=' : '='
            ];
        }

        // Property type taxonomy
        if (!empty($property_type)) {
            $args['tax_query'][] = [
                'taxonomy' => 'property_type',
                'field' => is_numeric($property_type) ? 'term_id' : 'slug',
                'terms' => $property_type
            ];
        }

        // Property status taxonomy
        if (!empty($property_status)) {
            $args['tax_query'][] = [
                'taxonomy' => 'property_status',
                'field' => is_numeric($property_status) ? 'term_id' : 'slug',
                'terms' => $property_status
            ];
        }

        // Location filters
        if (!empty($listing_location)) {
            $args['tax_query'][] = [
                'taxonomy' => 'listing_location',
                'field' => is_numeric($listing_location) ? 'term_id' : 'slug',
                'terms' => $listing_location
            ];
        }

        // Set meta_query relation
        if (count($args['meta_query']) > 1) {
            $args['meta_query']['relation'] = 'AND';
        }

        // Set tax_query relation
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }

        // Execute query
        $query = new WP_Query($args);

        // Generate HTML based on view
        $html = '';
        if ($query->have_posts()) {
            ob_start();

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                // Get change tracking data
                $change_data = [];
                if (function_exists('hpt_bridge_get_listing_changes')) {
                    $change_data = [
                        'changes' => hpt_bridge_get_listing_changes($post_id),
                        'badges' => hpt_bridge_get_listing_badges($post_id, 2),
                        'has_recent_changes' => hpt_bridge_has_recent_changes($post_id),
                        'is_new_listing' => hpt_is_new_listing($post_id)
                    ];
                }

                // Render card based on view
                $layout = ($view === 'list') ? 'horizontal' : 'vertical';

                hph_component('universal-card', [
                    'post_id' => $post_id,
                    'post_type' => 'listing',
                    'layout' => $layout,
                    'variant' => 'default',
                    'size' => 'md',
                    'show_days_on_market' => true,
                    'show_last_updated' => true,
                    'listing_changes' => $change_data['changes'] ?? [],
                    'listing_badges' => $change_data['badges'] ?? [],
                    'has_recent_changes' => $change_data['has_recent_changes'] ?? false,
                    'is_new_listing' => $change_data['is_new_listing'] ?? false
                ]);
            }

            $html = ob_get_clean();
            wp_reset_postdata();
        }

        // Prepare response data
        $response = [
            'html' => $html,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $query->max_num_pages,
                'has_more' => $page < $query->max_num_pages,
                'total_posts' => $query->found_posts
            ],
            'count' => [
                'total' => $query->found_posts,
                'per_page' => $per_page,
                'showing' => $query->post_count
            ],
            'view' => $view,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'min_price' => $min_price,
                'max_price' => $max_price,
                'bedrooms' => $bedrooms,
                'bathrooms' => $bathrooms,
                'property_type' => $property_type,
                'property_status' => $property_status
            ]
        ];

        wp_send_json_success($response);

    } catch (Exception $e) {
        error_log('HPH Archive AJAX Error: ' . $e->getMessage());
        wp_send_json_error([
            'message' => 'An error occurred while loading listings. Please try again.',
            'debug' => WP_DEBUG ? $e->getMessage() : null
        ]);
    }
}

/**
 * Helper function to get component template
 */
if (!function_exists('hph_component')) {
    function hph_component($component, $args = []) {
        get_template_part('template-parts/components/' . $component, null, $args);
    }
}