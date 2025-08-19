<?php
/**
 * Community Bridge Functions
 * 
 * Provides a comprehensive interface between the plugin layer and templates
 * for the community post type. All data access should go through these functions
 * rather than direct WordPress or ACF calls.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all community data
 * 
 * @param int|WP_Post $community Community ID or post object
 * @return array Complete community data
 */
function hpt_get_community($community = null) {
    $community = get_post($community);
    
    if (!$community || $community->post_type !== 'community') {
        return null;
    }
    
    return array(
        'id' => $community->ID,
        'name' => get_the_title($community),
        'slug' => $community->post_name,
        'url' => get_permalink($community),
        'status' => $community->post_status,
        'date_created' => $community->post_date,
        'date_modified' => $community->post_modified,
        
        // Basic information
        'description' => hpt_get_community_description($community->ID),
        'community_type' => hpt_get_community_type($community->ID),
        'developer' => hpt_get_community_developer($community->ID),
        'year_built' => hpt_get_community_year_built($community->ID),
        'year_established' => hpt_get_community_year_established($community->ID),
        'total_homes' => hpt_get_community_total_homes($community->ID),
        'available_homes' => hpt_get_community_available_homes($community->ID),
        
        // Location
        'address' => hpt_get_community_address($community->ID),
        'coordinates' => hpt_get_community_coordinates($community->ID),
        'city' => hpt_get_community_city($community->ID),
        'state' => hpt_get_community_state($community->ID),
        'zip_code' => hpt_get_community_zip($community->ID),
        'county' => hpt_get_community_county($community->ID),
        
        // Amenities and features
        'amenities' => hpt_get_community_amenities($community->ID),
        'features' => hpt_get_community_features($community->ID),
        'schools' => hpt_get_community_schools($community->ID),
        'nearby_places' => hpt_get_community_nearby_places($community->ID),
        
        // Media
        'featured_image' => hpt_get_community_featured_image($community->ID),
        'gallery' => hpt_get_community_gallery($community->ID),
        'virtual_tour' => hpt_get_community_virtual_tour($community->ID),
        'video_url' => hpt_get_community_video($community->ID),
        
        // HOA and pricing
        'hoa_fees' => hpt_get_community_hoa_fees($community->ID),
        'hoa_contact' => hpt_get_community_hoa_contact($community->ID),
        'price_range' => hpt_get_community_price_range($community->ID),
        'lot_sizes' => hpt_get_community_lot_sizes($community->ID),
        
        // Statistics
        'listing_count' => hpt_get_community_listing_count($community->ID),
        'avg_price' => hpt_get_community_average_price($community->ID),
        'avg_dom' => hpt_get_community_average_dom($community->ID),
        
        // Status
        'is_featured' => hpt_is_community_featured($community->ID),
        'is_active' => hpt_is_community_active($community->ID),
    );
}

/**
 * Get community name
 */
function hpt_get_community_name($community_id) {
    return get_the_title($community_id);
}

/**
 * Get community description
 */
function hpt_get_community_description($community_id) {
    $description = get_field('description', $community_id);
    
    if (!$description) {
        $post = get_post($community_id);
        $description = $post->post_content;
    }
    
    return $description;
}

/**
 * Get community type
 */
function hpt_get_community_type($community_id) {
    return get_field('community_type', $community_id) ?: 'residential';
}

/**
 * Get community type label
 */
function hpt_get_community_type_label($community_id) {
    $type = hpt_get_community_type($community_id);
    
    $labels = array(
        'residential' => __('Residential', 'happy-place-theme'),
        'mixed_use' => __('Mixed Use', 'happy-place-theme'),
        'luxury' => __('Luxury', 'happy-place-theme'),
        'active_adult' => __('Active Adult', 'happy-place-theme'),
        '55_plus' => __('55+ Community', 'happy-place-theme'),
        'gated' => __('Gated Community', 'happy-place-theme'),
        'master_planned' => __('Master Planned', 'happy-place-theme'),
        'golf_course' => __('Golf Course Community', 'happy-place-theme'),
        'waterfront' => __('Waterfront', 'happy-place-theme'),
    );
    
    return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
}

/**
 * Get community developer
 */
function hpt_get_community_developer($community_id) {
    return get_field('developer', $community_id) ?: '';
}

/**
 * Get community year built
 */
function hpt_get_community_year_built($community_id) {
    return intval(get_field('year_built', $community_id));
}

/**
 * Get community year established
 */
function hpt_get_community_year_established($community_id) {
    return intval(get_field('year_established', $community_id) ?: get_field('year_built', $community_id));
}

/**
 * Get total homes in community
 */
function hpt_get_community_total_homes($community_id) {
    return intval(get_field('total_homes', $community_id));
}

/**
 * Get available homes count
 */
function hpt_get_community_available_homes($community_id) {
    $available = get_field('available_homes', $community_id);
    
    if ($available === null || $available === false) {
        // Count active listings in this community
        $available = hpt_get_community_listing_count($community_id);
    }
    
    return intval($available);
}

/**
 * Get community address
 */
function hpt_get_community_address($community_id, $format = 'full') {
    $address = array(
        'street' => get_field('street_address', $community_id) ?: '',
        'city' => get_field('city', $community_id) ?: '',
        'state' => get_field('state', $community_id) ?: '',
        'zip' => get_field('zip_code', $community_id) ?: '',
        'county' => get_field('county', $community_id) ?: '',
    );
    
    if ($format === 'array') {
        return $address;
    }
    
    if ($format === 'city_state') {
        return trim($address['city'] . ', ' . $address['state']);
    }
    
    // Full address
    $parts = array_filter(array(
        $address['street'],
        $address['city'],
        $address['state'] . ' ' . $address['zip']
    ));
    
    return implode(', ', $parts);
}

/**
 * Get community coordinates
 */
function hpt_get_community_coordinates($community_id) {
    return array(
        'lat' => floatval(get_field('latitude', $community_id)),
        'lng' => floatval(get_field('longitude', $community_id)),
    );
}

/**
 * Get community city
 */
function hpt_get_community_city($community_id) {
    return get_field('city', $community_id) ?: '';
}

/**
 * Get community state
 */
function hpt_get_community_state($community_id) {
    return get_field('state', $community_id) ?: '';
}

/**
 * Get community zip code
 */
function hpt_get_community_zip($community_id) {
    return get_field('zip_code', $community_id) ?: '';
}

/**
 * Get community county
 */
function hpt_get_community_county($community_id) {
    return get_field('county', $community_id) ?: '';
}

/**
 * Get community amenities
 */
function hpt_get_community_amenities($community_id) {
    $amenities = get_field('amenities', $community_id);
    
    if (!is_array($amenities)) {
        $amenities = get_field('community_amenities', $community_id) ?: array();
    }
    
    return is_array($amenities) ? $amenities : array();
}

/**
 * Get community features
 */
function hpt_get_community_features($community_id) {
    $features = get_field('features', $community_id);
    
    if (!is_array($features)) {
        $features = get_field('community_features', $community_id) ?: array();
    }
    
    return is_array($features) ? $features : array();
}

/**
 * Get community schools
 */
function hpt_get_community_schools($community_id) {
    $schools = get_field('schools', $community_id);
    
    if (!is_array($schools)) {
        $schools = array();
    }
    
    return $schools;
}

/**
 * Get nearby places
 */
function hpt_get_community_nearby_places($community_id) {
    $places = get_field('nearby_places', $community_id);
    
    if (!is_array($places)) {
        $places = array();
    }
    
    return $places;
}

/**
 * Get community featured image
 */
function hpt_get_community_featured_image($community_id, $size = 'large') {
    if (has_post_thumbnail($community_id)) {
        return array(
            'id' => get_post_thumbnail_id($community_id),
            'url' => get_the_post_thumbnail_url($community_id, $size),
            'alt' => get_post_meta(get_post_thumbnail_id($community_id), '_wp_attachment_image_alt', true),
        );
    }
    
    // Try ACF featured image field
    $featured = get_field('featured_image', $community_id);
    
    if ($featured) {
        return array(
            'id' => is_array($featured) ? $featured['ID'] : $featured,
            'url' => is_array($featured) ? $featured['sizes'][$size] ?? $featured['url'] : wp_get_attachment_image_url($featured, $size),
            'alt' => is_array($featured) ? $featured['alt'] : get_post_meta($featured, '_wp_attachment_image_alt', true),
        );
    }
    
    // Return placeholder
    return array(
        'id' => 0,
        'url' => get_template_directory_uri() . '/assets/images/community-placeholder.jpg',
        'alt' => __('Community image', 'happy-place-theme'),
    );
}

/**
 * Get community gallery
 */
function hpt_get_community_gallery($community_id) {
    $gallery = get_field('gallery', $community_id);
    
    if (!$gallery) {
        $gallery = get_field('gallery_images', $community_id) ?: array();
    }
    
    if (!is_array($gallery)) {
        return array();
    }
    
    return array_map(function($image) {
        if (is_array($image)) {
            return array(
                'id' => $image['ID'],
                'url' => $image['url'],
                'thumbnail' => $image['sizes']['thumbnail'] ?? $image['url'],
                'medium' => $image['sizes']['medium'] ?? $image['url'],
                'large' => $image['sizes']['large'] ?? $image['url'],
                'alt' => $image['alt'],
                'caption' => $image['caption'] ?? '',
            );
        }
        
        return array(
            'id' => $image,
            'url' => wp_get_attachment_image_url($image, 'full'),
            'thumbnail' => wp_get_attachment_image_url($image, 'thumbnail'),
            'medium' => wp_get_attachment_image_url($image, 'medium'),
            'large' => wp_get_attachment_image_url($image, 'large'),
            'alt' => get_post_meta($image, '_wp_attachment_image_alt', true),
            'caption' => wp_get_attachment_caption($image),
        );
    }, $gallery);
}

/**
 * Get community virtual tour
 */
function hpt_get_community_virtual_tour($community_id) {
    return get_field('virtual_tour_url', $community_id) ?: get_field('virtual_tour', $community_id) ?: '';
}

/**
 * Get community video
 */
function hpt_get_community_video($community_id) {
    return get_field('video_url', $community_id) ?: get_field('community_video', $community_id) ?: '';
}

/**
 * Get community HOA fees
 */
function hpt_get_community_hoa_fees($community_id) {
    return get_field('hoa_fees', $community_id) ?: '';
}

/**
 * Get formatted HOA fees
 */
function hpt_get_community_hoa_fees_formatted($community_id) {
    $fees = hpt_get_community_hoa_fees($community_id);
    
    if (!$fees) {
        return __('Contact for details', 'happy-place-theme');
    }
    
    if (is_numeric($fees)) {
        return '$' . number_format($fees) . '/month';
    }
    
    return $fees;
}

/**
 * Get HOA contact information
 */
function hpt_get_community_hoa_contact($community_id) {
    return get_field('hoa_contact', $community_id) ?: array();
}

/**
 * Get community price range
 */
function hpt_get_community_price_range($community_id) {
    $price_range = get_field('price_range', $community_id);
    
    if (is_array($price_range)) {
        return $price_range;
    }
    
    // Try to calculate from listings
    $listings = hpt_get_community_listings($community_id);
    if (!empty($listings)) {
        $prices = array();
        foreach ($listings as $listing) {
            $price = hpt_get_listing_price($listing->ID);
            if ($price > 0) {
                $prices[] = $price;
            }
        }
        
        if (!empty($prices)) {
            return array(
                'min' => min($prices),
                'max' => max($prices),
            );
        }
    }
    
    return array('min' => 0, 'max' => 0);
}

/**
 * Get formatted price range
 */
function hpt_get_community_price_range_formatted($community_id) {
    $range = hpt_get_community_price_range($community_id);
    
    if (!$range || (!$range['min'] && !$range['max'])) {
        return __('Contact for pricing', 'happy-place-theme');
    }
    
    if ($range['min'] && $range['max']) {
        return '$' . number_format($range['min']) . ' - $' . number_format($range['max']);
    }
    
    if ($range['min']) {
        return __('From', 'happy-place-theme') . ' $' . number_format($range['min']);
    }
    
    return '$' . number_format($range['max']);
}

/**
 * Get community lot sizes
 */
function hpt_get_community_lot_sizes($community_id) {
    return get_field('lot_sizes', $community_id) ?: '';
}

/**
 * Get community listing count
 */
function hpt_get_community_listing_count($community_id) {
    $count = get_posts(array(
        'post_type' => 'listing',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'community',
                'value' => $community_id,
                'compare' => '='
            ),
            array(
                'key' => 'listing_status',
                'value' => 'active',
                'compare' => '='
            )
        ),
        'fields' => 'ids'
    ));
    
    return count($count);
}

/**
 * Get community average price
 */
function hpt_get_community_average_price($community_id) {
    $listings = hpt_get_community_listings($community_id);
    
    if (empty($listings)) {
        return 0;
    }
    
    $total = 0;
    $count = 0;
    
    foreach ($listings as $listing) {
        $price = hpt_get_listing_price($listing->ID);
        if ($price > 0) {
            $total += $price;
            $count++;
        }
    }
    
    return $count > 0 ? round($total / $count) : 0;
}

/**
 * Get community average days on market
 */
function hpt_get_community_average_dom($community_id) {
    return intval(get_field('average_dom', $community_id));
}

/**
 * Check if community is featured
 */
function hpt_is_community_featured($community_id) {
    return get_field('featured', $community_id) == true;
}

/**
 * Check if community is active
 */
function hpt_is_community_active($community_id) {
    $status = get_field('community_status', $community_id);
    return $status !== 'inactive';
}

/**
 * Get community listings
 */
function hpt_get_community_listings($community_id, $args = array()) {
    $defaults = array(
        'post_type' => 'listing',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'community',
                'value' => $community_id,
                'compare' => '='
            ),
            array(
                'key' => 'listing_status',
                'value' => 'active',
                'compare' => '='
            )
        ),
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    $args = wp_parse_args($args, $defaults);
    
    return get_posts($args);
}

/**
 * Query communities
 */
function hpt_query_communities($args = array()) {
    $defaults = array(
        'post_type' => 'community',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    return new WP_Query($args);
}

/**
 * Get featured communities
 */
function hpt_get_featured_communities($limit = 6) {
    return get_posts(array(
        'post_type' => 'community',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'featured',
                'value' => true,
                'compare' => '='
            )
        ),
        'orderby' => 'menu_order title',
        'order' => 'ASC'
    ));
}

/**
 * Get communities by city
 */
function hpt_get_communities_by_city($city, $limit = -1) {
    return get_posts(array(
        'post_type' => 'community',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'city',
                'value' => $city,
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    ));
}

/**
 * Get nearby communities
 */
function hpt_get_nearby_communities($community_id, $radius = 10, $limit = 5) {
    $coordinates = hpt_get_community_coordinates($community_id);
    
    if (!$coordinates['lat'] || !$coordinates['lng']) {
        return array();
    }
    
    // This would typically use a spatial query or external geocoding service
    // For now, return communities in the same city
    $city = hpt_get_community_city($community_id);
    
    return get_posts(array(
        'post_type' => 'community',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'post__not_in' => array($community_id),
        'meta_query' => array(
            array(
                'key' => 'city',
                'value' => $city,
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    ));
}