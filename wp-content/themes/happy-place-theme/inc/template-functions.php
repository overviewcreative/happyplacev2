<?php
/**
 * Template Functions
 * Additional template functions and helpers
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get post thumbnail with fallback
 *
 * @param int|WP_Post $post Post ID or post object
 * @param string|array $size Image size
 * @param array $attr Additional attributes
 * @return string HTML img element or empty string
 */
function happy_place_get_post_thumbnail($post = null, $size = 'post-thumbnail', $attr = array()) {
    $post = get_post($post);
    
    if (!$post) {
        return '';
    }
    
    // Default attributes
    $default_attr = array(
        'class' => 'img-responsive',
        'alt' => get_the_title($post)
    );
    
    $attr = wp_parse_args($attr, $default_attr);
    
    if (has_post_thumbnail($post)) {
        return get_the_post_thumbnail($post, $size, $attr);
    }
    
    // Fallback image based on post type
    $fallback_url = '';
    $post_type = get_post_type($post);
    
    switch ($post_type) {
        case 'listing':
            $fallback_url = get_template_directory_uri() . '/assets/images/placeholder-property.jpg';
            break;
        case 'agent':
            $fallback_url = get_template_directory_uri() . '/assets/images/placeholder-agent.jpg';
            break;
        default:
            $fallback_url = get_template_directory_uri() . '/assets/images/placeholder-post.jpg';
            break;
    }
    
    // If fallback exists, use it
    if ($fallback_url) {
        $class = isset($attr['class']) ? $attr['class'] : '';
        $alt = isset($attr['alt']) ? $attr['alt'] : get_the_title($post);
        
        return sprintf(
            '<img src="%s" class="%s" alt="%s" />',
            esc_url($fallback_url),
            esc_attr($class),
            esc_attr($alt)
        );
    }
    
    return '';
}

/**
 * Get excerpt with custom length
 *
 * @param int $length Excerpt length in words
 * @param string $more More text
 * @param int|WP_Post $post Post ID or post object
 * @return string
 */
function happy_place_get_excerpt($length = 25, $more = '...', $post = null) {
    $post = get_post($post);
    
    if (!$post) {
        return '';
    }
    
    $excerpt = $post->post_excerpt;
    
    if (empty($excerpt)) {
        $excerpt = $post->post_content;
    }
    
    $excerpt = strip_shortcodes($excerpt);
    $excerpt = wp_strip_all_tags($excerpt);
    $excerpt = wp_trim_words($excerpt, $length, $more);
    
    return $excerpt;
}

/**
 * Get formatted post meta
 *
 * @param int|WP_Post $post Post ID or post object
 * @return string
 */
function happy_place_get_post_meta($post = null) {
    $post = get_post($post);
    
    if (!$post || get_post_type($post) !== 'post') {
        return '';
    }
    
    $meta_parts = array();
    
    // Date
    $meta_parts[] = sprintf(
        '<time datetime="%s">%s</time>',
        esc_attr(get_the_date('c', $post)),
        esc_html(get_the_date('', $post))
    );
    
    // Author
    $author_id = $post->post_author;
    $author_name = get_the_author_meta('display_name', $author_id);
    $author_url = get_author_posts_url($author_id);
    
    $meta_parts[] = sprintf(
        '<a href="%s" class="author-link">%s</a>',
        esc_url($author_url),
        esc_html($author_name)
    );
    
    // Categories
    $categories = get_the_category($post);
    if (!empty($categories)) {
        $category_links = array();
        foreach ($categories as $category) {
            $category_links[] = sprintf(
                '<a href="%s" class="category-link">%s</a>',
                esc_url(get_category_link($category->term_id)),
                esc_html($category->name)
            );
        }
        $meta_parts[] = implode(', ', $category_links);
    }
    
    return implode(' • ', $meta_parts);
}

/**
 * Get social sharing buttons
 *
 * @param int|WP_Post $post Post ID or post object
 * @return string
 */
function happy_place_get_social_share($post = null) {
    $post = get_post($post);
    
    if (!$post) {
        return '';
    }
    
    $url = get_permalink($post);
    $title = get_the_title($post);
    
    $facebook_url = add_query_arg(array(
        'u' => urlencode($url)
    ), 'https://www.facebook.com/sharer/sharer.php');
    
    $twitter_url = add_query_arg(array(
        'url' => urlencode($url),
        'text' => urlencode($title)
    ), 'https://twitter.com/intent/tweet');
    
    $linkedin_url = add_query_arg(array(
        'url' => urlencode($url),
        'title' => urlencode($title)
    ), 'https://www.linkedin.com/sharing/share-offsite/');
    
    $email_url = add_query_arg(array(
        'subject' => urlencode($title),
        'body' => urlencode($url)
    ), 'mailto:');
    
    $output = '<div class="social-share">';
    $output .= '<span class="share-label">' . esc_html__('Share:', 'happy-place-theme') . '</span>';
    $output .= '<div class="share-buttons">';
    
    $output .= sprintf(
        '<a href="%s" target="_blank" rel="noopener" class="share-button facebook" title="%s"><i class="fab fa-facebook-f"></i></a>',
        esc_url($facebook_url),
        esc_attr__('Share on Facebook', 'happy-place-theme')
    );
    
    $output .= sprintf(
        '<a href="%s" target="_blank" rel="noopener" class="share-button twitter" title="%s"><i class="fab fa-twitter"></i></a>',
        esc_url($twitter_url),
        esc_attr__('Share on Twitter', 'happy-place-theme')
    );
    
    $output .= sprintf(
        '<a href="%s" target="_blank" rel="noopener" class="share-button linkedin" title="%s"><i class="fab fa-linkedin-in"></i></a>',
        esc_url($linkedin_url),
        esc_attr__('Share on LinkedIn', 'happy-place-theme')
    );
    
    $output .= sprintf(
        '<a href="%s" class="share-button email" title="%s"><i class="fas fa-envelope"></i></a>',
        esc_url($email_url),
        esc_attr__('Share via Email', 'happy-place-theme')
    );
    
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}

/**
 * Get breadcrumb navigation
 *
 * @param array $args Arguments
 * @return string
 */
function happy_place_get_breadcrumbs($args = array()) {
    // Default arguments
    $defaults = array(
        'separator' => '<i class="fas fa-chevron-right"></i>',
        'home_text' => __('Home', 'happy-place-theme'),
        'class' => 'breadcrumbs'
    );
    
    $args = wp_parse_args($args, $defaults);
    
    if (is_front_page()) {
        return '';
    }
    
    $breadcrumbs = array();
    
    // Home link
    $breadcrumbs[] = sprintf(
        '<a href="%s" class="breadcrumb-link">%s</a>',
        esc_url(home_url('/')),
        esc_html($args['home_text'])
    );
    
    if (is_category() || is_single()) {
        $category = get_the_category();
        if (!empty($category)) {
            $breadcrumbs[] = sprintf(
                '<a href="%s" class="breadcrumb-link">%s</a>',
                esc_url(get_category_link($category[0]->term_id)),
                esc_html($category[0]->name)
            );
        }
    }
    
    if (is_single()) {
        $breadcrumbs[] = '<span class="breadcrumb-current">' . get_the_title() . '</span>';
    } elseif (is_page()) {
        $breadcrumbs[] = '<span class="breadcrumb-current">' . get_the_title() . '</span>';
    } elseif (is_category()) {
        $breadcrumbs[] = '<span class="breadcrumb-current">' . single_cat_title('', false) . '</span>';
    } elseif (is_tag()) {
        $breadcrumbs[] = '<span class="breadcrumb-current">' . single_tag_title('', false) . '</span>';
    } elseif (is_archive()) {
        $breadcrumbs[] = '<span class="breadcrumb-current">' . post_type_archive_title('', false) . '</span>';
    } elseif (is_search()) {
        $breadcrumbs[] = '<span class="breadcrumb-current">' . __('Search Results', 'happy-place-theme') . '</span>';
    } elseif (is_404()) {
        $breadcrumbs[] = '<span class="breadcrumb-current">' . __('404 Error', 'happy-place-theme') . '</span>';
    }
    
    if (empty($breadcrumbs)) {
        return '';
    }
    
    $output = sprintf(
        '<nav class="%s" aria-label="%s">',
        esc_attr($args['class']),
        esc_attr__('Breadcrumb navigation', 'happy-place-theme')
    );
    
    $output .= implode(' ' . $args['separator'] . ' ', $breadcrumbs);
    $output .= '</nav>';
    
    return $output;
}

/**
 * Check if we're on a real estate related page
 *
 * @return bool
 */
function happy_place_is_real_estate_page() {
    return is_singular('listing') || is_post_type_archive('listing') || is_singular('agent') || is_post_type_archive('agent');
}

/**
 * Get theme version for cache busting
 *
 * @return string
 */
function happy_place_get_theme_version() {
    $theme = wp_get_theme();
    return $theme->get('Version');
}

/**
 * Sanitize HTML classes
 *
 * @param string|array $classes CSS classes
 * @return string
 */
function happy_place_sanitize_html_classes($classes) {
    if (is_array($classes)) {
        $classes = implode(' ', $classes);
    }
    
    return sanitize_html_class($classes);
}

/**
 * Fallback function for missing bridge functions
 * These provide basic functionality when bridge functions are not available
 */

if (!function_exists('hpt_get_listing_price_numeric')) {
    function hpt_get_listing_price_numeric($listing_id) {
        $price = get_field('price', $listing_id);
        return $price ? (int) preg_replace('/[^0-9]/', '', $price) : 0;
    }
}

if (!function_exists('hpt_get_related_listings')) {
    function hpt_get_related_listings($listing_id, $count = 3) {
        $property_type = get_field('property_type', $listing_id);
        $city = get_field('city', $listing_id);
        
        $args = array(
            'post_type' => 'listing',
            'posts_per_page' => $count,
            'post__not_in' => array($listing_id),
            'meta_query' => array()
        );
        
        if ($property_type) {
            $args['meta_query'][] = array(
                'key' => 'property_type',
                'value' => $property_type,
                'compare' => '='
            );
        }
        
        if ($city) {
            $args['meta_query'][] = array(
                'key' => 'city',
                'value' => $city,
                'compare' => '='
            );
        }
        
        $query = new WP_Query($args);
        return $query->posts;
    }
}

if (!function_exists('hpt_get_agent_active_listings')) {
    function hpt_get_agent_active_listings($agent_id, $count = 6) {
        $args = array(
            'post_type' => 'listing',
            'posts_per_page' => $count,
            'meta_query' => array(
                array(
                    'key' => 'listing_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'listing_status',
                    'value' => 'active',
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query($args);
        return $query->posts;
    }
}

// Function hpt_get_agent_sold_listings() is defined in bridge/agent-bridge.php

if (!function_exists('hpt_get_agent_years_experience')) {
    function hpt_get_agent_years_experience($agent_id) {
        return get_field('years_experience', $agent_id);
    }
}

if (!function_exists('hpt_get_community_listings_count')) {
    function hpt_get_community_listings_count($community_id) {
        $args = array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'community',
                    'value' => $community_id,
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
}

if (!function_exists('hpt_get_city_listings_count')) {
    function hpt_get_city_listings_count($city_id) {
        $args = array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'city',
                    'value' => $city_id,
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
}
