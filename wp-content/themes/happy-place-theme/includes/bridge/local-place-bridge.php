<?php
/**
 * Local Place Bridge Functions
 * 
 * Provides a comprehensive interface between the plugin layer and templates
 * for the local_place post type. All data access should go through these functions
 * rather than direct WordPress or ACF calls.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all local place data
 * 
 * @param int|WP_Post $local_place Local Place ID or post object
 * @return array Complete local place data
 */
function hpt_get_local_place($local_place = null) {
    $local_place = get_post($local_place);
    
    if (!$local_place || $local_place->post_type !== 'local_place') {
        return null;
    }
    
    return array(
        'id' => $local_place->ID,
        'name' => get_the_title($local_place),
        'slug' => $local_place->post_name,
        'url' => get_permalink($local_place),
        'status' => $local_place->post_status,
        'date_created' => $local_place->post_date,
        'date_modified' => $local_place->post_modified,
        
        // Basic information
        'description' => hpt_get_local_place_description($local_place->ID),
        'place_type' => hpt_get_local_place_type($local_place->ID),
        'category' => hpt_get_local_place_category($local_place->ID),
        'subcategory' => hpt_get_local_place_subcategory($local_place->ID),
        'tags' => hpt_get_local_place_tags($local_place->ID),
        
        // Location information
        'address' => hpt_get_local_place_address($local_place->ID),
        'coordinates' => hpt_get_local_place_coordinates($local_place->ID),
        'city' => hpt_get_local_place_city($local_place->ID),
        'state' => hpt_get_local_place_state($local_place->ID),
        'zip_code' => hpt_get_local_place_zip($local_place->ID),
        'neighborhood' => hpt_get_local_place_neighborhood($local_place->ID),
        
        // Contact information
        'phone' => hpt_get_local_place_phone($local_place->ID),
        'website' => hpt_get_local_place_website($local_place->ID),
        'email' => hpt_get_local_place_email($local_place->ID),
        
        // Business details
        'hours' => hpt_get_local_place_hours($local_place->ID),
        'price_range' => hpt_get_local_place_price_range($local_place->ID),
        'amenities' => hpt_get_local_place_amenities($local_place->ID),
        'features' => hpt_get_local_place_features($local_place->ID),
        'parking' => hpt_get_local_place_parking($local_place->ID),
        'accessibility' => hpt_get_local_place_accessibility($local_place->ID),
        
        // Ratings and reviews
        'rating' => hpt_get_local_place_rating($local_place->ID),
        'review_count' => hpt_get_local_place_review_count($local_place->ID),
        'yelp_rating' => hpt_get_local_place_yelp_rating($local_place->ID),
        'google_rating' => hpt_get_local_place_google_rating($local_place->ID),
        
        // Media
        'featured_image' => hpt_get_local_place_featured_image($local_place->ID),
        'gallery' => hpt_get_local_place_gallery($local_place->ID),
        'logo' => hpt_get_local_place_logo($local_place->ID),
        'video_url' => hpt_get_local_place_video($local_place->ID),
        
        // Social media
        'social_links' => hpt_get_local_place_social_links($local_place->ID),
        
        // External IDs and links
        'google_place_id' => hpt_get_local_place_google_id($local_place->ID),
        'yelp_url' => hpt_get_local_place_yelp_url($local_place->ID),
        'foursquare_id' => hpt_get_local_place_foursquare_id($local_place->ID),
        
        // Distance calculations (when context is available)
        'distance_from_context' => 0, // Would be calculated based on current listing/location
        
        // Status and visibility
        'is_featured' => hpt_is_local_place_featured($local_place->ID),
        'is_verified' => hpt_is_local_place_verified($local_place->ID),
        'business_status' => hpt_get_local_place_business_status($local_place->ID),
    );
}

/**
 * Get local place name
 */
function hpt_get_local_place_name($local_place_id) {
    return get_the_title($local_place_id);
}

/**
 * Get local place description
 */
function hpt_get_local_place_description($local_place_id) {
    $description = get_field('description', $local_place_id);
    
    if (!$description) {
        $post = get_post($local_place_id);
        $description = $post->post_content;
    }
    
    return $description;
}

/**
 * Get place type
 */
function hpt_get_local_place_type($local_place_id) {
    return get_field('place_type', $local_place_id) ?: 'business';
}

/**
 * Get place type label
 */
function hpt_get_local_place_type_label($local_place_id) {
    $type = hpt_get_local_place_type($local_place_id);
    
    $labels = array(
        'business' => __('Business', 'happy-place-theme'),
        'restaurant' => __('Restaurant', 'happy-place-theme'),
        'school' => __('School', 'happy-place-theme'),
        'hospital' => __('Hospital', 'happy-place-theme'),
        'park' => __('Park', 'happy-place-theme'),
        'shopping' => __('Shopping', 'happy-place-theme'),
        'entertainment' => __('Entertainment', 'happy-place-theme'),
        'services' => __('Services', 'happy-place-theme'),
        'transportation' => __('Transportation', 'happy-place-theme'),
        'government' => __('Government', 'happy-place-theme'),
        'religious' => __('Religious', 'happy-place-theme'),
        'landmark' => __('Landmark', 'happy-place-theme'),
    );
    
    return $labels[$type] ?? ucfirst($type);
}

/**
 * Get place category
 */
function hpt_get_local_place_category($local_place_id) {
    return get_field('category', $local_place_id) ?: '';
}

/**
 * Get place subcategory
 */
function hpt_get_local_place_subcategory($local_place_id) {
    return get_field('subcategory', $local_place_id) ?: '';
}

/**
 * Get place tags
 */
function hpt_get_local_place_tags($local_place_id) {
    $tags = get_field('tags', $local_place_id);
    
    if (!is_array($tags)) {
        $tags = array();
    }
    
    return $tags;
}

/**
 * Get local place address
 */
function hpt_get_local_place_address($local_place_id, $format = 'full') {
    $address = array(
        'street' => get_field('street_address', $local_place_id) ?: '',
        'city' => get_field('city', $local_place_id) ?: '',
        'state' => get_field('state', $local_place_id) ?: '',
        'zip' => get_field('zip_code', $local_place_id) ?: '',
    );
    
    if ($format === 'array') {
        return $address;
    }
    
    if ($format === 'street') {
        return $address['street'];
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
 * Get local place coordinates
 */
function hpt_get_local_place_coordinates($local_place_id) {
    return array(
        'lat' => floatval(get_field('latitude', $local_place_id)),
        'lng' => floatval(get_field('longitude', $local_place_id)),
    );
}

/**
 * Get local place city
 */
function hpt_get_local_place_city($local_place_id) {
    return get_field('city', $local_place_id) ?: '';
}

/**
 * Get local place state
 */
function hpt_get_local_place_state($local_place_id) {
    return get_field('state', $local_place_id) ?: '';
}

/**
 * Get local place zip code
 */
function hpt_get_local_place_zip($local_place_id) {
    return get_field('zip_code', $local_place_id) ?: '';
}

/**
 * Get neighborhood
 */
function hpt_get_local_place_neighborhood($local_place_id) {
    return get_field('neighborhood', $local_place_id) ?: '';
}

/**
 * Get phone number
 */
function hpt_get_local_place_phone($local_place_id) {
    return get_field('phone', $local_place_id) ?: '';
}

/**
 * Get website URL
 */
function hpt_get_local_place_website($local_place_id) {
    return get_field('website', $local_place_id) ?: '';
}

/**
 * Get email address
 */
function hpt_get_local_place_email($local_place_id) {
    return get_field('email', $local_place_id) ?: '';
}

/**
 * Get business hours
 */
function hpt_get_local_place_hours($local_place_id) {
    $hours = get_field('hours', $local_place_id);
    
    if (!is_array($hours)) {
        $hours = array();
    }
    
    return $hours;
}

/**
 * Get price range
 */
function hpt_get_local_place_price_range($local_place_id) {
    return get_field('price_range', $local_place_id) ?: '';
}

/**
 * Get price range label
 */
function hpt_get_local_place_price_range_label($local_place_id) {
    $range = hpt_get_local_place_price_range($local_place_id);
    
    $labels = array(
        '$' => __('Budget-friendly', 'happy-place-theme'),
        '$$' => __('Moderate', 'happy-place-theme'),
        '$$$' => __('Upscale', 'happy-place-theme'),
        '$$$$' => __('Fine Dining/Luxury', 'happy-place-theme'),
    );
    
    return $labels[$range] ?? $range;
}

/**
 * Get amenities
 */
function hpt_get_local_place_amenities($local_place_id) {
    $amenities = get_field('amenities', $local_place_id);
    
    if (!is_array($amenities)) {
        $amenities = array();
    }
    
    return $amenities;
}

/**
 * Get features
 */
function hpt_get_local_place_features($local_place_id) {
    $features = get_field('features', $local_place_id);
    
    if (!is_array($features)) {
        $features = array();
    }
    
    return $features;
}

/**
 * Get parking information
 */
function hpt_get_local_place_parking($local_place_id) {
    return get_field('parking', $local_place_id) ?: '';
}

/**
 * Get accessibility information
 */
function hpt_get_local_place_accessibility($local_place_id) {
    return get_field('accessibility', $local_place_id) ?: '';
}

/**
 * Get overall rating
 */
function hpt_get_local_place_rating($local_place_id) {
    return floatval(get_field('rating', $local_place_id));
}

/**
 * Get review count
 */
function hpt_get_local_place_review_count($local_place_id) {
    return intval(get_field('review_count', $local_place_id));
}

/**
 * Get Yelp rating
 */
function hpt_get_local_place_yelp_rating($local_place_id) {
    return floatval(get_field('yelp_rating', $local_place_id));
}

/**
 * Get Google rating
 */
function hpt_get_local_place_google_rating($local_place_id) {
    return floatval(get_field('google_rating', $local_place_id));
}

/**
 * Get local place featured image
 */
function hpt_get_local_place_featured_image($local_place_id, $size = 'large') {
    if (has_post_thumbnail($local_place_id)) {
        return array(
            'id' => get_post_thumbnail_id($local_place_id),
            'url' => get_the_post_thumbnail_url($local_place_id, $size),
            'alt' => get_post_meta(get_post_thumbnail_id($local_place_id), '_wp_attachment_image_alt', true),
        );
    }
    
    // Try ACF featured image field
    $featured = get_field('featured_image', $local_place_id);
    
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
        'url' => get_template_directory_uri() . '/assets/images/place-placeholder.jpg',
        'alt' => __('Local place image', 'happy-place-theme'),
    );
}

/**
 * Get local place gallery
 */
function hpt_get_local_place_gallery($local_place_id) {
    $gallery = get_field('gallery', $local_place_id);
    
    if (!$gallery) {
        $gallery = get_field('gallery_images', $local_place_id) ?: array();
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
 * Get business logo
 */
function hpt_get_local_place_logo($local_place_id, $size = 'medium') {
    $logo = get_field('logo', $local_place_id);
    
    if ($logo) {
        return array(
            'id' => is_array($logo) ? $logo['ID'] : $logo,
            'url' => is_array($logo) ? $logo['sizes'][$size] ?? $logo['url'] : wp_get_attachment_image_url($logo, $size),
            'alt' => is_array($logo) ? $logo['alt'] : get_post_meta($logo, '_wp_attachment_image_alt', true),
        );
    }
    
    return null;
}

/**
 * Get video URL
 */
function hpt_get_local_place_video($local_place_id) {
    return get_field('video_url', $local_place_id) ?: '';
}

/**
 * Get social media links
 */
function hpt_get_local_place_social_links($local_place_id) {
    $social = get_field('social_links', $local_place_id);
    
    if (!is_array($social)) {
        $social = array();
        
        // Try individual fields
        $platforms = array('facebook', 'instagram', 'twitter', 'yelp', 'google');
        
        foreach ($platforms as $platform) {
            $url = get_field($platform . '_url', $local_place_id);
            if ($url) {
                $social[$platform] = $url;
            }
        }
    }
    
    return $social;
}

/**
 * Get Google Place ID
 */
function hpt_get_local_place_google_id($local_place_id) {
    return get_field('google_place_id', $local_place_id) ?: '';
}

/**
 * Get Yelp URL
 */
function hpt_get_local_place_yelp_url($local_place_id) {
    return get_field('yelp_url', $local_place_id) ?: '';
}

/**
 * Get Foursquare ID
 */
function hpt_get_local_place_foursquare_id($local_place_id) {
    return get_field('foursquare_id', $local_place_id) ?: '';
}

/**
 * Check if local place is featured
 */
function hpt_is_local_place_featured($local_place_id) {
    return get_field('featured', $local_place_id) == true;
}

/**
 * Check if local place is verified
 */
function hpt_is_local_place_verified($local_place_id) {
    return get_field('verified', $local_place_id) == true;
}

/**
 * Get business status
 */
function hpt_get_local_place_business_status($local_place_id) {
    return get_field('business_status', $local_place_id) ?: 'open';
}

/**
 * Get business status label
 */
function hpt_get_local_place_business_status_label($local_place_id) {
    $status = hpt_get_local_place_business_status($local_place_id);
    
    $labels = array(
        'open' => __('Open', 'happy-place-theme'),
        'closed' => __('Closed', 'happy-place-theme'),
        'temporarily_closed' => __('Temporarily Closed', 'happy-place-theme'),
        'permanently_closed' => __('Permanently Closed', 'happy-place-theme'),
        'coming_soon' => __('Coming Soon', 'happy-place-theme'),
    );
    
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

/**
 * Calculate distance from coordinates
 */
function hpt_calculate_local_place_distance($local_place_id, $lat, $lng, $unit = 'miles') {
    $place_coords = hpt_get_local_place_coordinates($local_place_id);
    
    if (!$place_coords['lat'] || !$place_coords['lng']) {
        return 0;
    }
    
    $earth_radius = ($unit === 'km') ? 6371 : 3959; // km or miles
    
    $lat_diff = deg2rad($place_coords['lat'] - $lat);
    $lng_diff = deg2rad($place_coords['lng'] - $lng);
    
    $a = sin($lat_diff / 2) * sin($lat_diff / 2) +
         cos(deg2rad($lat)) * cos(deg2rad($place_coords['lat'])) *
         sin($lng_diff / 2) * sin($lng_diff / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return round($earth_radius * $c, 1);
}

/**
 * Query local places
 */
function hpt_query_local_places($args = array()) {
    $defaults = array(
        'post_type' => 'local_place',
        'post_status' => 'publish',
        'posts_per_page' => 20,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    return new WP_Query($args);
}

/**
 * Get featured local places
 */
function hpt_get_featured_local_places($limit = 6) {
    return get_posts(array(
        'post_type' => 'local_place',
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
 * Get local places by type
 */
function hpt_get_local_places_by_type($type, $limit = -1) {
    return get_posts(array(
        'post_type' => 'local_place',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'place_type',
                'value' => $type,
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    ));
}

/**
 * Get local places by city
 */
function hpt_get_local_places_by_city($city, $limit = -1) {
    return get_posts(array(
        'post_type' => 'local_place',
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
 * Get nearby local places
 */
function hpt_get_nearby_local_places($lat, $lng, $radius = 5, $limit = 10, $types = array()) {
    // For a basic implementation, get all places and filter by distance
    // In production, you'd want to use a spatial database query
    
    $meta_query = array();
    
    if (!empty($types)) {
        $meta_query[] = array(
            'key' => 'place_type',
            'value' => $types,
            'compare' => 'IN'
        );
    }
    
    $places = get_posts(array(
        'post_type' => 'local_place',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => $meta_query
    ));
    
    $nearby_places = array();
    
    foreach ($places as $place) {
        $distance = hpt_calculate_local_place_distance($place->ID, $lat, $lng);
        if ($distance > 0 && $distance <= $radius) {
            $nearby_places[] = array(
                'place' => $place,
                'distance' => $distance
            );
        }
    }
    
    // Sort by distance
    usort($nearby_places, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });
    
    // Return just the place objects, limited by count
    $result = array();
    for ($i = 0; $i < min($limit, count($nearby_places)); $i++) {
        $result[] = $nearby_places[$i]['place'];
    }
    
    return $result;
}
