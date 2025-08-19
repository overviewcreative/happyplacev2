<?php
/**
 * City Bridge Functions
 * 
 * Provides a comprehensive interface between the plugin layer and templates
 * for the city post type. All data access should go through these functions
 * rather than direct WordPress or ACF calls.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all city data
 * 
 * @param int|WP_Post $city City ID or post object
 * @return array Complete city data
 */
function hpt_get_city($city = null) {
    $city = get_post($city);
    
    if (!$city || $city->post_type !== 'city') {
        return null;
    }
    
    return array(
        'id' => $city->ID,
        'name' => get_the_title($city),
        'slug' => $city->post_name,
        'url' => get_permalink($city),
        'status' => $city->post_status,
        'date_created' => $city->post_date,
        'date_modified' => $city->post_modified,
        
        // Basic information
        'description' => hpt_get_city_description($city->ID),
        'state' => hpt_get_city_state($city->ID),
        'county' => hpt_get_city_county($city->ID),
        'zip_codes' => hpt_get_city_zip_codes($city->ID),
        'area_codes' => hpt_get_city_area_codes($city->ID),
        'time_zone' => hpt_get_city_time_zone($city->ID),
        
        // Location and geography
        'coordinates' => hpt_get_city_coordinates($city->ID),
        'elevation' => hpt_get_city_elevation($city->ID),
        'area_sq_miles' => hpt_get_city_area($city->ID),
        'climate' => hpt_get_city_climate($city->ID),
        
        // Demographics
        'population' => hpt_get_city_population($city->ID),
        'population_density' => hpt_get_city_population_density($city->ID),
        'median_age' => hpt_get_city_median_age($city->ID),
        'median_income' => hpt_get_city_median_income($city->ID),
        'cost_of_living_index' => hpt_get_city_cost_of_living($city->ID),
        
        // Housing market
        'median_home_price' => hpt_get_city_median_home_price($city->ID),
        'average_rent' => hpt_get_city_average_rent($city->ID),
        'property_tax_rate' => hpt_get_city_property_tax_rate($city->ID),
        'home_appreciation' => hpt_get_city_home_appreciation($city->ID),
        
        // Economy and employment
        'major_employers' => hpt_get_city_major_employers($city->ID),
        'unemployment_rate' => hpt_get_city_unemployment_rate($city->ID),
        'job_growth_rate' => hpt_get_city_job_growth_rate($city->ID),
        'primary_industries' => hpt_get_city_primary_industries($city->ID),
        
        // Education
        'school_districts' => hpt_get_city_school_districts($city->ID),
        'school_ratings' => hpt_get_city_school_ratings($city->ID),
        'colleges_universities' => hpt_get_city_colleges($city->ID),
        
        // Lifestyle and amenities
        'attractions' => hpt_get_city_attractions($city->ID),
        'parks_recreation' => hpt_get_city_parks($city->ID),
        'dining_entertainment' => hpt_get_city_dining($city->ID),
        'shopping' => hpt_get_city_shopping($city->ID),
        'transportation' => hpt_get_city_transportation($city->ID),
        'hospitals_healthcare' => hpt_get_city_healthcare($city->ID),
        
        // Media
        'featured_image' => hpt_get_city_featured_image($city->ID),
        'gallery' => hpt_get_city_gallery($city->ID),
        'video_tour' => hpt_get_city_video($city->ID),
        'virtual_tour' => hpt_get_city_virtual_tour($city->ID),
        
        // Statistics and metrics
        'listing_count' => hpt_get_city_listing_count($city->ID),
        'avg_listing_price' => hpt_get_city_average_listing_price($city->ID),
        'community_count' => hpt_get_city_community_count($city->ID),
        'agent_count' => hpt_get_city_agent_count($city->ID),
        
        // Status
        'is_featured' => hpt_is_city_featured($city->ID),
        'market_status' => hpt_get_city_market_status($city->ID),
    );
}

/**
 * Get city name
 */
function hpt_get_city_name($city_id) {
    return get_the_title($city_id);
}

/**
 * Get city description
 */
function hpt_get_city_description($city_id) {
    $description = get_field('description', $city_id);
    
    if (!$description) {
        $post = get_post($city_id);
        $description = $post->post_content;
    }
    
    return $description;
}

/**
 * Get city state
 */
function hpt_get_city_state($city_id) {
    return get_field('state', $city_id) ?: '';
}

/**
 * Get city county
 */
function hpt_get_city_county($city_id) {
    return get_field('county', $city_id) ?: '';
}

/**
 * Get city zip codes
 */
function hpt_get_city_zip_codes($city_id) {
    $zip_codes = get_field('zip_codes', $city_id);
    
    if (!is_array($zip_codes)) {
        $zip_codes = array();
    }
    
    return $zip_codes;
}

/**
 * Get city area codes
 */
function hpt_get_city_area_codes($city_id) {
    $area_codes = get_field('area_codes', $city_id);
    
    if (!is_array($area_codes)) {
        $area_codes = array();
    }
    
    return $area_codes;
}

/**
 * Get city time zone
 */
function hpt_get_city_time_zone($city_id) {
    return get_field('time_zone', $city_id) ?: wp_timezone_string();
}

/**
 * Get city coordinates
 */
function hpt_get_city_coordinates($city_id) {
    return array(
        'lat' => floatval(get_field('latitude', $city_id)),
        'lng' => floatval(get_field('longitude', $city_id)),
    );
}

/**
 * Get city elevation
 */
function hpt_get_city_elevation($city_id) {
    return intval(get_field('elevation', $city_id));
}

/**
 * Get city area in square miles
 */
function hpt_get_city_area($city_id) {
    return floatval(get_field('area_sq_miles', $city_id));
}

/**
 * Get city climate
 */
function hpt_get_city_climate($city_id) {
    return get_field('climate', $city_id) ?: '';
}

/**
 * Get city population
 */
function hpt_get_city_population($city_id) {
    return intval(get_field('population', $city_id));
}

/**
 * Get formatted population
 */
function hpt_get_city_population_formatted($city_id) {
    $population = hpt_get_city_population($city_id);
    
    if (!$population) {
        return '';
    }
    
    return number_format($population);
}

/**
 * Get population density
 */
function hpt_get_city_population_density($city_id) {
    return floatval(get_field('population_density', $city_id));
}

/**
 * Get median age
 */
function hpt_get_city_median_age($city_id) {
    return floatval(get_field('median_age', $city_id));
}

/**
 * Get median income
 */
function hpt_get_city_median_income($city_id) {
    return floatval(get_field('median_income', $city_id));
}

/**
 * Get formatted median income
 */
function hpt_get_city_median_income_formatted($city_id) {
    $income = hpt_get_city_median_income($city_id);
    
    if (!$income) {
        return '';
    }
    
    return '$' . number_format($income);
}

/**
 * Get cost of living index
 */
function hpt_get_city_cost_of_living($city_id) {
    return floatval(get_field('cost_of_living_index', $city_id));
}

/**
 * Get median home price
 */
function hpt_get_city_median_home_price($city_id) {
    return floatval(get_field('median_home_price', $city_id));
}

/**
 * Get formatted median home price
 */
function hpt_get_city_median_home_price_formatted($city_id) {
    $price = hpt_get_city_median_home_price($city_id);
    
    if (!$price) {
        return '';
    }
    
    return '$' . number_format($price);
}

/**
 * Get average rent
 */
function hpt_get_city_average_rent($city_id) {
    return floatval(get_field('average_rent', $city_id));
}

/**
 * Get property tax rate
 */
function hpt_get_city_property_tax_rate($city_id) {
    return floatval(get_field('property_tax_rate', $city_id));
}

/**
 * Get home appreciation rate
 */
function hpt_get_city_home_appreciation($city_id) {
    return floatval(get_field('home_appreciation', $city_id));
}

/**
 * Get major employers
 */
function hpt_get_city_major_employers($city_id) {
    $employers = get_field('major_employers', $city_id);
    
    if (!is_array($employers)) {
        $employers = array();
    }
    
    return $employers;
}

/**
 * Get unemployment rate
 */
function hpt_get_city_unemployment_rate($city_id) {
    return floatval(get_field('unemployment_rate', $city_id));
}

/**
 * Get job growth rate
 */
function hpt_get_city_job_growth_rate($city_id) {
    return floatval(get_field('job_growth_rate', $city_id));
}

/**
 * Get primary industries
 */
function hpt_get_city_primary_industries($city_id) {
    $industries = get_field('primary_industries', $city_id);
    
    if (!is_array($industries)) {
        $industries = array();
    }
    
    return $industries;
}

/**
 * Get school districts
 */
function hpt_get_city_school_districts($city_id) {
    $districts = get_field('school_districts', $city_id);
    
    if (!is_array($districts)) {
        $districts = array();
    }
    
    return $districts;
}

/**
 * Get school ratings
 */
function hpt_get_city_school_ratings($city_id) {
    return get_field('school_ratings', $city_id) ?: '';
}

/**
 * Get colleges and universities
 */
function hpt_get_city_colleges($city_id) {
    $colleges = get_field('colleges_universities', $city_id);
    
    if (!is_array($colleges)) {
        $colleges = array();
    }
    
    return $colleges;
}

/**
 * Get attractions
 */
function hpt_get_city_attractions($city_id) {
    $attractions = get_field('attractions', $city_id);
    
    if (!is_array($attractions)) {
        $attractions = array();
    }
    
    return $attractions;
}

/**
 * Get parks and recreation
 */
function hpt_get_city_parks($city_id) {
    $parks = get_field('parks_recreation', $city_id);
    
    if (!is_array($parks)) {
        $parks = array();
    }
    
    return $parks;
}

/**
 * Get dining and entertainment
 */
function hpt_get_city_dining($city_id) {
    $dining = get_field('dining_entertainment', $city_id);
    
    if (!is_array($dining)) {
        $dining = array();
    }
    
    return $dining;
}

/**
 * Get shopping
 */
function hpt_get_city_shopping($city_id) {
    $shopping = get_field('shopping', $city_id);
    
    if (!is_array($shopping)) {
        $shopping = array();
    }
    
    return $shopping;
}

/**
 * Get transportation
 */
function hpt_get_city_transportation($city_id) {
    return get_field('transportation', $city_id) ?: '';
}

/**
 * Get healthcare
 */
function hpt_get_city_healthcare($city_id) {
    $healthcare = get_field('hospitals_healthcare', $city_id);
    
    if (!is_array($healthcare)) {
        $healthcare = array();
    }
    
    return $healthcare;
}

/**
 * Get city featured image
 */
function hpt_get_city_featured_image($city_id, $size = 'large') {
    if (has_post_thumbnail($city_id)) {
        return array(
            'id' => get_post_thumbnail_id($city_id),
            'url' => get_the_post_thumbnail_url($city_id, $size),
            'alt' => get_post_meta(get_post_thumbnail_id($city_id), '_wp_attachment_image_alt', true),
        );
    }
    
    // Try ACF featured image field
    $featured = get_field('featured_image', $city_id);
    
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
        'url' => get_template_directory_uri() . '/assets/images/city-placeholder.jpg',
        'alt' => __('City image', 'happy-place-theme'),
    );
}

/**
 * Get city gallery
 */
function hpt_get_city_gallery($city_id) {
    $gallery = get_field('gallery', $city_id);
    
    if (!$gallery) {
        $gallery = get_field('gallery_images', $city_id) ?: array();
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
 * Get city video
 */
function hpt_get_city_video($city_id) {
    return get_field('video_url', $city_id) ?: get_field('city_video', $city_id) ?: '';
}

/**
 * Get city virtual tour
 */
function hpt_get_city_virtual_tour($city_id) {
    return get_field('virtual_tour_url', $city_id) ?: get_field('virtual_tour', $city_id) ?: '';
}

/**
 * Get city listing count
 */
function hpt_get_city_listing_count($city_id) {
    $city_name = hpt_get_city_name($city_id);
    
    $count = get_posts(array(
        'post_type' => 'listing',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'city',
                'value' => $city_name,
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
 * Get city average listing price
 */
function hpt_get_city_average_listing_price($city_id) {
    $listings = hpt_get_city_listings($city_id);
    
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
 * Get city community count
 */
function hpt_get_city_community_count($city_id) {
    $city_name = hpt_get_city_name($city_id);
    
    $count = get_posts(array(
        'post_type' => 'community',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'city',
                'value' => $city_name,
                'compare' => '='
            )
        ),
        'fields' => 'ids'
    ));
    
    return count($count);
}

/**
 * Get city agent count
 */
function hpt_get_city_agent_count($city_id) {
    // This could be based on agent office location or coverage area
    return 0; // Implement based on your agent location fields
}

/**
 * Check if city is featured
 */
function hpt_is_city_featured($city_id) {
    return get_field('featured', $city_id) == true;
}

/**
 * Get city market status
 */
function hpt_get_city_market_status($city_id) {
    return get_field('market_status', $city_id) ?: 'balanced';
}

/**
 * Get market status label
 */
function hpt_get_city_market_status_label($city_id) {
    $status = hpt_get_city_market_status($city_id);
    
    $labels = array(
        'hot' => __('Hot Market', 'happy-place-theme'),
        'balanced' => __('Balanced Market', 'happy-place-theme'),
        'buyers' => __('Buyer\'s Market', 'happy-place-theme'),
        'sellers' => __('Seller\'s Market', 'happy-place-theme'),
        'emerging' => __('Emerging Market', 'happy-place-theme'),
    );
    
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

/**
 * Get city listings
 */
function hpt_get_city_listings($city_id, $args = array()) {
    $city_name = hpt_get_city_name($city_id);
    
    $defaults = array(
        'post_type' => 'listing',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'city',
                'value' => $city_name,
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
 * Get city communities
 */
function hpt_get_city_communities($city_id, $limit = -1) {
    $city_name = hpt_get_city_name($city_id);
    
    return get_posts(array(
        'post_type' => 'community',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'city',
                'value' => $city_name,
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    ));
}

/**
 * Query cities
 */
function hpt_query_cities($args = array()) {
    $defaults = array(
        'post_type' => 'city',
        'post_status' => 'publish',
        'posts_per_page' => 20,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    return new WP_Query($args);
}

/**
 * Get featured cities
 */
function hpt_get_featured_cities($limit = 6) {
    return get_posts(array(
        'post_type' => 'city',
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
 * Get cities by state
 */
function hpt_get_cities_by_state($state, $limit = -1) {
    return get_posts(array(
        'post_type' => 'city',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'state',
                'value' => $state,
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    ));
}

/**
 * Get popular cities (by listing count)
 */
function hpt_get_popular_cities($limit = 10) {
    // This would be better handled with a custom query or cached data
    // For now, get all cities and sort by listing count
    $cities = get_posts(array(
        'post_type' => 'city',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));
    
    $cities_with_counts = array();
    
    foreach ($cities as $city) {
        $listing_count = hpt_get_city_listing_count($city->ID);
        if ($listing_count > 0) {
            $cities_with_counts[] = array(
                'city' => $city,
                'listing_count' => $listing_count
            );
        }
    }
    
    // Sort by listing count
    usort($cities_with_counts, function($a, $b) {
        return $b['listing_count'] - $a['listing_count'];
    });
    
    // Return just the city objects
    $popular_cities = array();
    for ($i = 0; $i < min($limit, count($cities_with_counts)); $i++) {
        $popular_cities[] = $cities_with_counts[$i]['city'];
    }
    
    return $popular_cities;
}