<?php
function get_city_card_props($city_id, $options = []) {
    // Get city data from custom fields
    $name = get_the_title($city_id);
    $state = get_post_meta($city_id, 'state', true);
    $population = get_post_meta($city_id, 'population', true);
    $featured_image = get_the_post_thumbnail_url($city_id, 'medium');
    $description = get_post_meta($city_id, 'city_description', true) ?: wp_trim_words(get_the_excerpt($city_id), 20);
    
    // Calculate active listings count for this city
    $listings_count = 0;
    if (function_exists('hpt_get_city_listings_count')) {
        $listings_count = hpt_get_city_listings_count($city_id);
    } else {
        // Fallback query
        $listings_query = new WP_Query([
            'post_type' => 'listing',
            'meta_query' => [
                [
                    'key' => 'city',
                    'value' => $name,
                    'compare' => '='
                ]
            ],
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
        $listings_count = $listings_query->found_posts;
        wp_reset_postdata();
    }
    
    return [
        'variant' => $options['variant'] ?? 'default',
        'layout' => $options['layout'] ?? 'vertical',
        'image' => [
            'src' => $featured_image ?: get_template_directory_uri() . '/assets/images/default-city.jpg',
            'alt' => $name . ', ' . $state,
            'ratio' => 'landscape'
        ],
        'title' => [
            'text' => $name,
            'link' => get_permalink($city_id)
        ],
        'subtitle' => $state,
        'badges' => array_filter([
            $population ? ['text' => number_format($population) . ' residents', 'variant' => 'default'] : null
        ]),
        'meta_items' => array_filter([
            $listings_count > 0 ? ['icon' => 'home', 'text' => $listings_count . ' properties'] : null,
            $description ? ['icon' => 'info', 'text' => wp_trim_words($description, 8)] : null
        ]),
        'actions' => [
            ['text' => 'View Properties', 'href' => get_permalink($city_id), 'variant' => 'primary'],
            ['text' => 'Learn More', 'href' => get_permalink($city_id), 'variant' => 'outline']
        ],
        'link_wrapper' => get_permalink($city_id),
        'hover_effect' => 'lift'
    ];
}