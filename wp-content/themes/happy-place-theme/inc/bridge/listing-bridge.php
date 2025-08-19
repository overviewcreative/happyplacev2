<?php
/**
 * Listing Bridge Functions
 * 
 * Provides a comprehensive interface between the plugin layer and templates
 * for the listing post type. All data access should go through these functions
 * rather than direct WordPress or ACF calls.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all listing data
 * 
 * @param int|WP_Post $listing Listing ID or post object
 * @return array Complete listing data
 */
function hpt_get_listing($listing = null) {
    $listing = get_post($listing);
    
    if (!$listing || $listing->post_type !== 'listing') {
        return null;
    }
    
    return array(
        'id' => $listing->ID,
        'title' => get_the_title($listing),
        'slug' => $listing->post_name,
        'url' => get_permalink($listing),
        'status' => $listing->post_status,
        'date_created' => $listing->post_date,
        'date_modified' => $listing->post_modified,
        
        // Core fields
        'price' => hpt_get_listing_price($listing->ID),
        'listing_status' => hpt_get_listing_status($listing->ID),
        'mls_number' => hpt_get_listing_mls_number($listing->ID),
        'property_type' => hpt_get_listing_property_type($listing->ID),
        
        // Property details
        'bedrooms' => hpt_get_listing_bedrooms($listing->ID),
        'bathrooms' => hpt_get_listing_bathrooms($listing->ID),
        'square_feet' => hpt_get_listing_square_feet($listing->ID),
        'lot_size' => hpt_get_listing_lot_size($listing->ID),
        'year_built' => hpt_get_listing_year_built($listing->ID),
        'garage_spaces' => hpt_get_listing_garage_spaces($listing->ID),
        
        // Location
        'address' => hpt_get_listing_address($listing->ID),
        'coordinates' => hpt_get_listing_coordinates($listing->ID),
        'neighborhood' => hpt_get_listing_neighborhood($listing->ID),
        
        // Description
        'description' => hpt_get_listing_description($listing->ID),
        'features' => hpt_get_listing_features($listing->ID),
        'amenities' => hpt_get_listing_amenities($listing->ID),
        
        // Media
        'featured_image' => hpt_get_listing_featured_image($listing->ID),
        'gallery' => hpt_get_listing_gallery($listing->ID),
        'virtual_tour' => hpt_get_listing_virtual_tour($listing->ID),
        'video_url' => hpt_get_listing_video($listing->ID),
        'floor_plans' => hpt_get_listing_floor_plans($listing->ID),
        
        // Relationships
        'agent' => hpt_get_listing_agent($listing->ID),
        'co_agent' => hpt_get_listing_co_agent($listing->ID),
        'office' => hpt_get_listing_office($listing->ID),
        'community' => hpt_get_listing_community($listing->ID),
        'open_houses' => hpt_get_listing_open_houses($listing->ID),
        
        // Financial
        'hoa_fees' => hpt_get_listing_hoa_fees($listing->ID),
        'property_taxes' => hpt_get_listing_property_taxes($listing->ID),
        'commission' => hpt_get_listing_commission($listing->ID),
        
        // Metadata
        'views' => hpt_get_listing_views($listing->ID),
        'is_featured' => hpt_is_listing_featured($listing->ID),
        'listing_date' => hpt_get_listing_date($listing->ID),
    );
}

/**
 * Get listing price
 */
function hpt_get_listing_price($listing_id) {
    return get_field('listing_price', $listing_id) ?: get_field('price', $listing_id) ?: 0;
}

/**
 * Get formatted listing price
 */
function hpt_get_listing_price_formatted($listing_id) {
    $price = hpt_get_listing_price($listing_id);
    
    if (!$price) {
        return __('Price on request', 'happy-place-theme');
    }
    
    return '$' . number_format($price);
}

/**
 * Get listing status
 */
function hpt_get_listing_status($listing_id) {
    return get_field('listing_status', $listing_id) ?: 'active';
}

/**
 * Get listing status label
 */
function hpt_get_listing_status_label($listing_id) {
    $status = hpt_get_listing_status($listing_id);
    
    $labels = array(
        'active' => __('Active', 'happy-place-theme'),
        'pending' => __('Pending', 'happy-place-theme'),
        'sold' => __('Sold', 'happy-place-theme'),
        'coming_soon' => __('Coming Soon', 'happy-place-theme'),
        'contingent' => __('Contingent', 'happy-place-theme'),
        'withdrawn' => __('Withdrawn', 'happy-place-theme'),
    );
    
    return $labels[$status] ?? ucfirst($status);
}

/**
 * Get listing status badge HTML
 */
function hpt_get_listing_status_badge($listing_id) {
    $status = hpt_get_listing_status($listing_id);
    $label = hpt_get_listing_status_label($listing_id);
    
    $class = 'hp-listing-status hp-status-' . esc_attr($status);
    
    return sprintf('<span class="%s">%s</span>', $class, esc_html($label));
}

/**
 * Get MLS number
 */
function hpt_get_listing_mls_number($listing_id) {
    return get_field('mls_number', $listing_id) ?: '';
}

/**
 * Get property type
 */
function hpt_get_listing_property_type($listing_id) {
    return get_field('property_type', $listing_id) ?: 'house';
}

/**
 * Get property type label
 */
function hpt_get_listing_property_type_label($listing_id) {
    $type = hpt_get_listing_property_type($listing_id);
    
    $labels = array(
        'house' => __('House', 'happy-place-theme'),
        'condo' => __('Condo', 'happy-place-theme'),
        'townhouse' => __('Townhouse', 'happy-place-theme'),
        'apartment' => __('Apartment', 'happy-place-theme'),
        'land' => __('Land', 'happy-place-theme'),
        'commercial' => __('Commercial', 'happy-place-theme'),
        'multi_family' => __('Multi-Family', 'happy-place-theme'),
    );
    
    return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
}

/**
 * Get bedrooms
 */
function hpt_get_listing_bedrooms($listing_id) {
    return intval(get_field('bedrooms', $listing_id));
}

/**
 * Get bathrooms
 */
function hpt_get_listing_bathrooms($listing_id) {
    return floatval(get_field('bathrooms', $listing_id));
}

/**
 * Get formatted bathrooms
 */
function hpt_get_listing_bathrooms_formatted($listing_id) {
    $bathrooms = hpt_get_listing_bathrooms($listing_id);
    
    if ($bathrooms == intval($bathrooms)) {
        return intval($bathrooms);
    }
    
    return number_format($bathrooms, 1);
}

/**
 * Get square feet
 */
function hpt_get_listing_square_feet($listing_id) {
    return intval(get_field('square_feet', $listing_id));
}

/**
 * Get formatted square feet
 */
function hpt_get_listing_square_feet_formatted($listing_id) {
    $sqft = hpt_get_listing_square_feet($listing_id);
    
    if (!$sqft) {
        return '';
    }
    
    return number_format($sqft) . ' sq ft';
}

/**
 * Get lot size
 */
function hpt_get_listing_lot_size($listing_id) {
    return floatval(get_field('lot_size', $listing_id));
}

/**
 * Get formatted lot size
 */
function hpt_get_listing_lot_size_formatted($listing_id) {
    $lot_size = hpt_get_listing_lot_size($listing_id);
    
    if (!$lot_size) {
        return '';
    }
    
    if ($lot_size < 1) {
        return number_format($lot_size * 43560) . ' sq ft';
    }
    
    return number_format($lot_size, 2) . ' acres';
}

/**
 * Get year built
 */
function hpt_get_listing_year_built($listing_id) {
    return intval(get_field('year_built', $listing_id));
}

/**
 * Get garage spaces
 */
function hpt_get_listing_garage_spaces($listing_id) {
    return intval(get_field('garage_spaces', $listing_id));
}

/**
 * Get listing address
 */
function hpt_get_listing_address($listing_id, $format = 'full') {
    $address = array(
        'street' => get_field('street_address', $listing_id) ?: '',
        'city' => get_field('city', $listing_id) ?: '',
        'state' => get_field('state', $listing_id) ?: '',
        'zip' => get_field('zip_code', $listing_id) ?: '',
        'county' => get_field('county', $listing_id) ?: '',
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
 * Get listing street address only
 */
function hpt_get_listing_street_address($listing_id) {
    return get_field('street_address', $listing_id) ?: '';
}

/**
 * Get listing city only
 */
function hpt_get_listing_city($listing_id) {
    return get_field('city', $listing_id) ?: '';
}

/**
 * Get listing state only
 */
function hpt_get_listing_state($listing_id) {
    return get_field('state', $listing_id) ?: '';
}

/**
 * Get listing zip code only
 */
function hpt_get_listing_zip_code($listing_id) {
    return get_field('zip_code', $listing_id) ?: '';
}

/**
 * Get listing county
 */
function hpt_get_listing_county($listing_id) {
    return get_field('county', $listing_id) ?: '';
}

/**
 * Get listing coordinates
 */
function hpt_get_listing_coordinates($listing_id) {
    return array(
        'lat' => floatval(get_field('latitude', $listing_id)),
        'lng' => floatval(get_field('longitude', $listing_id)),
    );
}

/**
 * Get listing neighborhood
 */
function hpt_get_listing_neighborhood($listing_id) {
    return get_field('neighborhood', $listing_id) ?: '';
}

/**
 * Get listing description
 */
function hpt_get_listing_description($listing_id) {
    $description = get_field('description', $listing_id);
    
    if (!$description) {
        $post = get_post($listing_id);
        $description = $post->post_content;
    }
    
    return $description;
}

/**
 * Get listing features
 */
function hpt_get_listing_features($listing_id) {
    $features = get_field('features', $listing_id);
    
    if (!is_array($features)) {
        $features = get_field('property_features', $listing_id) ?: array();
    }
    
    return is_array($features) ? $features : array();
}

/**
 * Get listing amenities
 */
function hpt_get_listing_amenities($listing_id) {
    $amenities = get_field('amenities', $listing_id);
    
    if (!is_array($amenities)) {
        $amenities = get_field('community_amenities', $listing_id) ?: array();
    }
    
    return is_array($amenities) ? $amenities : array();
}

/**
 * Get listing featured image
 */
function hpt_get_listing_featured_image($listing_id, $size = 'large') {
    if (has_post_thumbnail($listing_id)) {
        return array(
            'id' => get_post_thumbnail_id($listing_id),
            'url' => get_the_post_thumbnail_url($listing_id, $size),
            'alt' => get_post_meta(get_post_thumbnail_id($listing_id), '_wp_attachment_image_alt', true),
        );
    }
    
    // Try ACF featured image field
    $featured = get_field('featured_image', $listing_id);
    
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
        'url' => get_template_directory_uri() . '/assets/images/listing-placeholder.jpg',
        'alt' => __('Property image', 'happy-place-theme'),
    );
}

/**
 * Get listing gallery
 */
function hpt_get_listing_gallery($listing_id) {
    $gallery = get_field('gallery', $listing_id);
    
    if (!$gallery) {
        $gallery = get_field('gallery_images', $listing_id) ?: array();
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
 * Get listing virtual tour
 */
function hpt_get_listing_virtual_tour($listing_id) {
    return get_field('virtual_tour_url', $listing_id) ?: get_field('virtual_tour', $listing_id) ?: '';
}

/**
 * Get listing video
 */
function hpt_get_listing_video($listing_id) {
    return get_field('video_url', $listing_id) ?: get_field('property_video', $listing_id) ?: '';
}

/**
 * Get listing floor plans
 */
function hpt_get_listing_floor_plans($listing_id) {
    $floor_plans = get_field('floor_plans', $listing_id);
    
    if (!is_array($floor_plans)) {
        return array();
    }
    
    return $floor_plans;
}

/**
 * Get listing agent
 */
function hpt_get_listing_agent($listing_id) {
    $agent_id = get_field('listing_agent', $listing_id);
    
    if (!$agent_id) {
        $agent_id = get_field('agent', $listing_id);
    }
    
    if (!$agent_id) {
        return null;
    }
    
    return intval($agent_id);
}

/**
 * Get listing co-agent
 */
function hpt_get_listing_co_agent($listing_id) {
    $agent_id = get_field('co_listing_agent', $listing_id);
    
    if (!$agent_id) {
        $agent_id = get_field('co_agent', $listing_id);
    }
    
    return $agent_id ? intval($agent_id) : null;
}

/**
 * Get listing office
 */
function hpt_get_listing_office($listing_id) {
    $office_id = get_field('listing_office', $listing_id);
    
    if (!$office_id) {
        $office_id = get_field('office', $listing_id);
    }
    
    return $office_id ? intval($office_id) : null;
}

/**
 * Get listing community
 */
function hpt_get_listing_community($listing_id) {
    $community_id = get_field('community', $listing_id);
    
    if (!$community_id) {
        $community_id = get_field('subdivision', $listing_id);
    }
    
    return $community_id ? intval($community_id) : null;
}

/**
 * Note: hpt_get_listing_open_houses() function is defined in open-house-bridge.php
 * to avoid duplication and provide more comprehensive functionality.
 */

/**
 * Get listing HOA fees
 */
function hpt_get_listing_hoa_fees($listing_id) {
    return floatval(get_field('hoa_fees', $listing_id));
}

/**
 * Get listing property taxes
 */
function hpt_get_listing_property_taxes($listing_id) {
    return floatval(get_field('property_taxes', $listing_id));
}

/**
 * Get listing commission
 */
function hpt_get_listing_commission($listing_id) {
    return get_field('buyer_agent_commission', $listing_id) ?: '';
}

/**
 * Get listing views
 */
function hpt_get_listing_views($listing_id) {
    return intval(get_post_meta($listing_id, 'listing_views', true));
}

/**
 * Check if listing is featured
 */
function hpt_is_listing_featured($listing_id) {
    return get_field('featured_listing', $listing_id) == '1';
}

/**
 * Get listing date
 */
function hpt_get_listing_date($listing_id) {
    $date = get_field('listing_date', $listing_id);
    
    if (!$date) {
        $post = get_post($listing_id);
        $date = $post->post_date;
    }
    
    return $date;
}

/**
 * Query listings
 */
function hpt_query_listings($args = array()) {
    $defaults = array(
        'post_type' => 'listing',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Add default meta query for active listings
    if (!isset($args['meta_query'])) {
        $args['meta_query'] = array();
    }
    
    if (!isset($args['show_all_statuses'])) {
        $args['meta_query'][] = array(
            'key' => 'listing_status',
            'value' => 'active',
            'compare' => '='
        );
    }
    
    return new WP_Query($args);
}

/**
 * Get recent listings
 */
function hpt_get_recent_listings($limit = 6) {
    $query = hpt_query_listings(array(
        'posts_per_page' => $limit,
    ));
    
    return $query->posts;
}

/**
 * Get featured listings
 */
function hpt_get_featured_listings($limit = 6) {
    $query = hpt_query_listings(array(
        'posts_per_page' => $limit,
        'meta_query' => array(
            array(
                'key' => 'listing_status',
                'value' => 'active',
                'compare' => '='
            ),
            array(
                'key' => 'featured_listing',
                'value' => '1',
                'compare' => '='
            )
        )
    ));
    
    return $query->posts;
}

/**
 * Get similar listings
 */
function hpt_get_similar_listings($listing_id, $limit = 4) {
    $property_type = hpt_get_listing_property_type($listing_id);
    $price = hpt_get_listing_price($listing_id);
    $bedrooms = hpt_get_listing_bedrooms($listing_id);
    
    $query = hpt_query_listings(array(
        'posts_per_page' => $limit + 1,
        'post__not_in' => array($listing_id),
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'listing_status',
                'value' => 'active',
                'compare' => '='
            ),
            array(
                'key' => 'property_type',
                'value' => $property_type,
                'compare' => '='
            ),
            array(
                'key' => 'listing_price',
                'value' => array($price * 0.8, $price * 1.2),
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ),
            array(
                'key' => 'bedrooms',
                'value' => array($bedrooms - 1, $bedrooms + 1),
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            )
        )
    ));
    
    return array_slice($query->posts, 0, $limit);
}

/**
 * Note: hpt_get_agent_listings() function is defined in agent-bridge.php
 * to avoid duplication and provide more comprehensive functionality.
 */