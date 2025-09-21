<?php
/**
 * AJAX Handler for Load More Local Places
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handler for loading more local places
 */
function hph_load_more_local_places() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_load_more_nonce')) {
        wp_die('Security check failed');
    }

    $page = intval($_POST['page'] ?? 1);
    $posts_per_page = 12;
    
    // Get filter parameters
    $search_query = sanitize_text_field($_POST['search'] ?? '');
    $selected_city = intval($_POST['city'] ?? 0);
    $selected_type = sanitize_text_field($_POST['type'] ?? '');
    $selected_price_range = sanitize_text_field($_POST['price_range'] ?? '');
    $sort_by = sanitize_text_field($_POST['sort'] ?? '');

    // Build query args (same as main archive)
    $query_args = [
        'post_type' => 'local_place',
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ];

    // Apply sorting
    switch ($sort_by) {
        case 'title':
            $query_args['orderby'] = 'title';
            $query_args['order'] = 'ASC';
            break;
        case 'city':
            $query_args['meta_key'] = 'primary_city';
            $query_args['orderby'] = 'meta_value';
            $query_args['order'] = 'ASC';
            break;
        case 'type':
            $query_args['orderby'] = 'title';
            $query_args['order'] = 'ASC';
            break;
        case 'price':
            $query_args['meta_key'] = 'price_range';
            $query_args['orderby'] = 'meta_value';
            $query_args['order'] = 'ASC';
            break;
        case 'rating':
            $query_args['meta_key'] = 'rating';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'DESC';
            break;
    }

    // Apply search
    if (!empty($search_query)) {
        $query_args['s'] = $search_query;
    }

    // Build meta query
    $meta_query = [];

    if (!empty($selected_city)) {
        $meta_query[] = [
            'key' => 'primary_city',
            'value' => $selected_city,
            'compare' => '='
        ];
    }

    if (!empty($selected_price_range)) {
        $meta_query[] = [
            'key' => 'price_range',
            'value' => $selected_price_range,
            'compare' => '='
        ];
    }

    if (!empty($meta_query)) {
        $query_args['meta_query'] = $meta_query;
    }

    // Build tax query
    $tax_query = [];

    if (!empty($selected_type)) {
        $tax_query[] = [
            'taxonomy' => 'place-type',
            'field' => 'slug',
            'terms' => $selected_type
        ];
    }

    if (!empty($tax_query)) {
        $query_args['tax_query'] = $tax_query;
    }

    // Execute query
    $places_query = new WP_Query($query_args);

    ob_start();

    if ($places_query->have_posts()) {
        while ($places_query->have_posts()) {
            $places_query->the_post();
            ?>
            <div class="hph-place-card-wrapper">
                <?php hph_component('universal-card', [
                    'post_id' => get_the_ID(),
                    'post_type' => 'local_place',
                    'layout' => 'vertical',
                    'variant' => 'default',
                    'size' => 'md',
                    'show_meta' => true,
                    'show_actions' => true,
                    'clickable' => true,
                    'hover_effects' => true
                ]); ?>
            </div>
            <?php
        }
    }

    wp_reset_postdata();

    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'has_more' => ($page < $places_query->max_num_pages),
        'current_page' => $page,
        'max_pages' => $places_query->max_num_pages
    ]);
}

// Register AJAX handlers
add_action('wp_ajax_load_more_local_places', 'hph_load_more_local_places');
add_action('wp_ajax_nopriv_load_more_local_places', 'hph_load_more_local_places');