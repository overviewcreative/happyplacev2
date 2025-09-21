<?php
function get_community_card_props($community_id, $options = []) {
    // Get community data from custom fields
    $name = get_the_title($community_id);
    $city = get_post_meta($community_id, 'city', true);
    $average_price = get_post_meta($community_id, 'average_home_price', true);
    $school_rating = get_post_meta($community_id, 'school_rating', true);
    $featured_image = get_the_post_thumbnail_url($community_id, 'medium');
    $description = get_post_meta($community_id, 'community_description', true) ?: wp_trim_words(get_the_excerpt($community_id), 20);
    $amenities = get_post_meta($community_id, 'community_amenities', true);
    
    // Calculate active listings count for this community
    $listings_count = 0;
    if (function_exists('hpt_get_community_listings_count')) {
        $listings_count = hpt_get_community_listings_count($community_id);
    } else {
        // Fallback query
        $listings_query = new WP_Query([
            'post_type' => 'listing',
            'meta_query' => [
                [
                    'key' => 'community',
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
    
    // Determine price range category
    $price_category = '';
    if ($average_price) {
        if ($average_price < 300000) {
            $price_category = 'Affordable';
        } elseif ($average_price < 600000) {
            $price_category = 'Moderate';
        } elseif ($average_price < 1000000) {
            $price_category = 'Upscale';
        } else {
            $price_category = 'Luxury';
        }
    }
    
    return [
        'variant' => $options['variant'] ?? 'default',
        'layout' => $options['layout'] ?? 'vertical',
        'image' => [
            'src' => $featured_image ?: get_template_directory_uri() . '/assets/images/default-community.jpg',
            'alt' => $name . ' community',
            'ratio' => 'landscape'
        ],
        'title' => [
            'text' => $name,
            'link' => get_permalink($community_id)
        ],
        'subtitle' => $city,
        'badges' => array_filter([
            $price_category ? ['text' => $price_category, 'variant' => 'default'] : null,
            $school_rating ? ['text' => 'Schools: ' . $school_rating . '/10', 'variant' => 'success'] : null
        ]),
        'meta_items' => array_filter([
            $listings_count > 0 ? ['icon' => 'home', 'text' => $listings_count . ' properties'] : null,
            $average_price ? ['icon' => 'dollar', 'text' => 'Avg $' . number_format($average_price)] : null,
            $amenities ? ['icon' => 'star', 'text' => wp_trim_words($amenities, 5)] : null
        ]),
        'actions' => [
            ['text' => 'View Homes', 'href' => get_permalink($community_id), 'variant' => 'primary'],
            ['text' => 'Community Info', 'href' => get_permalink($community_id), 'variant' => 'outline']
        ],
        'link_wrapper' => get_permalink($community_id),
        'hover_effect' => 'lift'
    ];
}
