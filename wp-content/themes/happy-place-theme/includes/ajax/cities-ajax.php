<?php
/**
 * AJAX Handler for Cities Load More
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle AJAX request for loading more cities
 */
function hph_load_more_cities() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hph_load_more_nonce')) {
        wp_die('Security check failed');
    }
    
    // Get parameters
    $page = intval($_POST['page']);
    $search_query = sanitize_text_field($_POST['search'] ?? '');
    $selected_state = sanitize_text_field($_POST['state'] ?? '');
    $sort_by = sanitize_text_field($_POST['sort'] ?? '');
    $posts_per_page = 12;
    
    // Build query args matching the archive page
    $query_args = [
        'post_type' => 'city',
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
        case 'state':
            $query_args['meta_key'] = 'state';
            $query_args['orderby'] = 'meta_value';
            $query_args['order'] = 'ASC';
            break;
        case 'population':
            $query_args['meta_key'] = 'population';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'DESC';
            break;
        default:
            // Keep default date ordering
            break;
    }
    
    // Apply search
    if (!empty($search_query)) {
        $query_args['s'] = $search_query;
    }
    
    // Build meta query
    $meta_query = [];
    
    if (!empty($selected_state)) {
        $meta_query[] = [
            'key' => 'state',
            'value' => $selected_state,
            'compare' => '='
        ];
    }
    
    if (!empty($meta_query)) {
        $query_args['meta_query'] = $meta_query;
    }
    
    // Execute query
    $cities_query = new WP_Query($query_args);
    
    ob_start();
    
    if ($cities_query->have_posts()) :
        while ($cities_query->have_posts()) : 
            $cities_query->the_post();
            ?>
            <div class="hph-city-card-wrapper">
                <?php hph_component('universal-card', [
                    'post_id' => get_the_ID(),
                    'post_type' => 'city',
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
        endwhile;
    endif;
    
    $html = ob_get_clean();
    wp_reset_postdata();
    
    wp_send_json_success([
        'html' => $html,
        'has_more' => $page < $cities_query->max_num_pages,
        'current_page' => $page,
        'max_pages' => $cities_query->max_num_pages
    ]);
}

// Hook the AJAX actions
add_action('wp_ajax_load_more_cities', 'hph_load_more_cities');
add_action('wp_ajax_nopriv_load_more_cities', 'hph_load_more_cities');