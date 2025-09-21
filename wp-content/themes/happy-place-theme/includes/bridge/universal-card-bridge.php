<?php
/**
 * Universal Card Data Adapters
 * 
 * Provides standardized data adapters for transforming any post type
 * into card component format using bridge functions.
 * 
 * @package HappyPlaceTheme
 * @subpackage Bridge
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generic card data adapter for any post type
 * 
 * @param int $post_id Post ID
 * @param array $args Component arguments
 * @return array|null Card data array or null if invalid
 */
function hpt_get_card_data_generic($post_id, $args = array()) {
    $post = get_post($post_id);
    if (!$post) {
        return null;
    }
    
    // Get featured image
    $image_data = null;
    if (has_post_thumbnail($post_id)) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        $image_url = wp_get_attachment_image_url($thumbnail_id, 'large');
        $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
        
        $image_data = array(
            'src' => $image_url,
            'alt' => $image_alt ?: $post->post_title,
            'ratio' => 'landscape'
        );
    }
    
    return array(
        'title' => array(
            'text' => $post->post_title,
            'tag' => 'h3'
        ),
        'subtitle' => get_the_date('', $post),
        'description' => $args['show_excerpt'] ? get_the_excerpt($post) : '',
        'image' => $image_data,
        'link_wrapper' => $args['clickable'] ? get_permalink($post) : '',
        'meta_items' => array(
            array('icon' => 'calendar', 'text' => get_the_date('M j, Y', $post)),
            array('icon' => 'user', 'text' => get_the_author_meta('display_name', $post->post_author))
        ),
        'badges' => array(),
        'actions' => array()
    );
}

/**
 * Listing card data adapter
 * 
 * @param int $post_id Post ID
 * @param array $args Component arguments
 * @return array|null Card data array or null if invalid
 */
function hpt_get_card_data_listing($post_id, $args = array()) {
    // Use existing listing bridge function
    $listing = hpt_get_listing($post_id);
    if (!$listing) {
        return null;
    }
    
    // Build image data
    $image_data = null;
    if ($listing['featured_image']) {
        $image_data = array(
            'src' => $listing['featured_image']['url'] ?? '',
            'alt' => $listing['featured_image']['alt'] ?? $listing['title'],
            'ratio' => 'landscape'
        );
    } elseif (!empty($listing['gallery'])) {
        $first_image = $listing['gallery'][0];
        $image_data = array(
            'src' => $first_image['url'] ?? '',
            'alt' => $first_image['alt'] ?? $listing['title'],
            'ratio' => 'landscape'
        );
    }
    
    // Build location string
    $address = hpt_get_listing_address($post_id, 'array');
    $location_parts = array_filter(array(
        $address['city'] ?? '',
        $address['state'] ?? ''
    ));
    $location = implode(', ', $location_parts);
    
    // Get property details
    $bedrooms = hpt_get_listing_bedrooms($post_id);
    $bathrooms = hpt_get_listing_bathrooms($post_id);
    $square_feet = hpt_get_listing_square_feet($post_id);
    $price_formatted = hpt_get_listing_price_formatted($post_id);
    $listing_status = hpt_get_listing_status($post_id);
    
    // Build meta items with toggleable options
    $meta_items = array();

    // Core property details (always shown if available)
    if ($bedrooms) {
        $meta_items[] = ['icon' => 'bed', 'text' => $bedrooms . ' bed' . ($bedrooms != 1 ? 's' : ''), 'type' => 'bedrooms'];
    }
    if ($bathrooms) {
        $meta_items[] = ['icon' => 'bath', 'text' => $bathrooms . ' bath' . ($bathrooms != 1 ? 's' : ''), 'type' => 'bathrooms'];
    }
    if ($square_feet) {
        $meta_items[] = ['icon' => 'ruler-combined', 'text' => number_format($square_feet) . ' sqft', 'type' => 'square_feet'];
    }

    // Toggleable meta items
    if ($args['show_days_on_market'] ?? false) {
        $days_on_market = hpt_get_listing_days_on_market($post_id);
        if ($days_on_market > 0) {
            $meta_items[] = [
                'icon' => 'calendar-alt',
                'text' => $days_on_market . ' day' . ($days_on_market != 1 ? 's' : '') . ' on market',
                'type' => 'days_on_market',
                'class' => 'hph-meta-days-on-market'
            ];
        }
    }

    if ($args['show_last_updated'] ?? false) {
        $last_updated = hpt_get_listing_last_updated_formatted($post_id, 'relative');
        if ($last_updated) {
            $meta_items[] = [
                'icon' => 'clock',
                'text' => 'Updated ' . $last_updated,
                'type' => 'last_updated',
                'class' => 'hph-meta-last-updated'
            ];
        }
    }

    // Filter out null values
    $meta_items = array_filter($meta_items);
    
    // Build badges - ENHANCED: Use comprehensive badge system
    $badges = array();

    // Use comprehensive badge system that includes all badge types
    $comprehensive_badges = hpt_bridge_get_comprehensive_badges($post_id, 3);

    foreach ($comprehensive_badges as $badge) {
        $badges[] = array(
            'text' => $badge['text'],
            'variant' => $badge['variant'],
            'type' => $badge['type'] ?? 'default',
            'priority' => $badge['priority'] ?? 5,
            'data' => $badge['data'] ?? null
        );
    }

    // Add featured badge if we have room and it's not already included
    $has_featured = false;
    foreach ($badges as $badge) {
        if ($badge['type'] === 'featured') {
            $has_featured = true;
            break;
        }
    }

    if (!$has_featured && count($badges) < 3 && $listing['is_featured']) {
        $badges[] = array(
            'text' => 'Featured',
            'variant' => 'primary',
            'type' => 'featured',
            'priority' => 3
        );
    }

    // Sort badges by priority and limit to 3
    usort($badges, function($a, $b) {
        return $b['priority'] <=> $a['priority'];
    });
    $badges = array_slice($badges, 0, 3);
    
    return array(
        'title' => array(
            'text' => $address['street'] ?? $listing['title'],
            'tag' => 'h3'
        ),
        'subtitle' => $price_formatted,
        'description' => $location,
        'image' => $image_data,
        'link_wrapper' => $args['clickable'] ? $listing['url'] : '',
        'meta_items' => $meta_items,
        'badges' => $badges,
        'actions' => array()
    );
}

/**
 * Agent card data adapter
 * 
 * @param int $post_id Post ID
 * @param array $args Component arguments
 * @return array|null Card data array or null if invalid
 */
function hpt_get_card_data_agent($post_id, $args = array()) {
    // Debug logging
    error_log("Universal Card Bridge: Getting agent data for post ID: " . $post_id);
    
    // Use agent bridge function
    $agent = hpt_get_agent($post_id);
    if (!$agent) {
        error_log("Universal Card Bridge: Agent data is null for post ID: " . $post_id);
        return null;
    }
    
    error_log("Universal Card Bridge: Agent data: " . print_r($agent, true));
    
    // Build image data
    $image_data = null;
    if ($agent['profile_photo']) {
        $image_data = array(
            'src' => $agent['profile_photo']['url'] ?? $agent['profile_photo'],
            'alt' => $agent['name'],
            'ratio' => 'square'
        );
    }
    
    // Build meta items
    $meta_items = array();
    if ($agent['license_number']) {
        $meta_items[] = array('icon' => 'id-card', 'text' => 'License #' . $agent['license_number']);
    }
    if ($agent['years_experience']) {
        $meta_items[] = array('icon' => 'calendar', 'text' => $agent['years_experience'] . ' years exp.');
    }
    if ($agent['phone']) {
        $meta_items[] = array('icon' => 'phone', 'text' => $agent['phone']);
    }
    
    // Build contact actions
    $actions = array();
    if ($agent['email']) {
        $actions[] = array(
            'type' => 'link',
            'href' => 'mailto:' . $agent['email'],
            'icon' => 'envelope',
            'text' => 'Email',
            'variant' => 'outline',
            'size' => 'sm'
        );
    }
    
    return array(
        'title' => array(
            'text' => $agent['name'],
            'tag' => 'h3'
        ),
        'subtitle' => $agent['title'] ?? 'Real Estate Agent',
        'description' => $agent['bio'] ?? '',
        'image' => $image_data,
        'link_wrapper' => $args['clickable'] ? $agent['url'] : '',
        'meta_items' => $meta_items,
        'badges' => array(),
        'actions' => $actions
    );
}

/**
 * Local Place card data adapter
 * 
 * @param int $post_id Post ID
 * @param array $args Component arguments
 * @return array|null Card data array or null if invalid
 */
function hpt_get_card_data_local_place($post_id, $args = array()) {
    // Use local place bridge function
    $place = hpt_get_local_place($post_id);
    if (!$place) {
        return null;
    }
    
    // Build image data
    $image_data = null;
    if ($place['featured_image']) {
        $image_data = array(
            'src' => $place['featured_image']['url'] ?? $place['featured_image'],
            'alt' => $place['name'],
            'ratio' => 'landscape'
        );
    }
    
    // Build location for subtitle - make city, state prominent
    $location_subtitle = '';
    $city = $place['address']['city'] ?? $place['city'] ?? '';
    $state = $place['address']['state'] ?? $place['state'] ?? '';
    if ($city && $state) {
        $location_subtitle = $city . ', ' . $state;
    } else if ($city) {
        $location_subtitle = $city;
    }
    
    // Build meta items
    $meta_items = array();
    if ($place['place_type']) {
        $meta_items[] = array('icon' => 'tag', 'text' => $place['place_type']);
    }
    if ($place['phone']) {
        $meta_items[] = array('icon' => 'phone', 'text' => $place['phone']);
    }
    if ($place['rating']) {
        $meta_items[] = array('icon' => 'star', 'text' => $place['rating'] . '/5');
    }
    
    // Build badges
    $badges = array();
    if ($place['price_range']) {
        $badges[] = array('text' => $place['price_range'], 'type' => 'price');
    }
    if ($place['is_family_friendly']) {
        $badges[] = array('text' => 'Family Friendly', 'type' => 'family', 'icon' => 'users');
    }
    
    return array(
        'title' => array(
            'text' => get_the_title($post_id), // Use actual post title instead of place name
            'tag' => 'h3'
        ),
        'subtitle' => $location_subtitle, // Prominent city, state display
        'description' => $place['description'] ?: ($place['place_type'] ?? ''),
        'image' => $image_data,
        'link_wrapper' => $args['clickable'] ? $place['url'] : '',
        'meta_items' => $meta_items,
        'badges' => $badges,
        'actions' => array()
    );
}

/**
 * Local Event card data adapter
 * 
 * @param int $post_id Post ID
 * @param array $args Component arguments
 * @return array|null Card data array or null if invalid
 */
function hpt_get_card_data_local_event($post_id, $args = array()) {
    // For now, check if bridge function exists
    if (function_exists('hpt_get_local_event')) {
        $event = hpt_get_local_event($post_id);
        if (!$event) {
            return null;
        }
        
        // Build event-specific data
        $image_data = null;
        if ($event['featured_image']) {
            $image_data = array(
                'src' => $event['featured_image']['url'] ?? $event['featured_image'],
                'alt' => $event['name'],
                'ratio' => 'landscape'
            );
        }
        
        $meta_items = array();
        if ($event['event_date']) {
            $meta_items[] = array('icon' => 'calendar', 'text' => date('M j, Y', strtotime($event['event_date'])));
        }
        if ($event['location']) {
            $meta_items[] = array('icon' => 'map-marker', 'text' => $event['location']);
        }
        
        return array(
            'title' => array(
                'text' => $event['name'],
                'tag' => 'h3'
            ),
            'subtitle' => $event['event_date'] ? date('M j, Y', strtotime($event['event_date'])) : '',
            'description' => $event['location'] ?? '',
            'image' => $image_data,
            'link_wrapper' => $args['clickable'] ? $event['url'] : '',
            'meta_items' => $meta_items,
            'badges' => array(),
            'actions' => array()
        );
    }
    
    // Fallback to generic adapter
    return hpt_get_card_data_generic($post_id, $args);
}

/**
 * Community card data adapter
 * 
 * @param int $post_id Post ID
 * @param array $args Component arguments
 * @return array|null Card data array or null if invalid
 */
function hpt_get_card_data_community($post_id, $args = array()) {
    // For now, fallback to generic adapter
    // TODO: Implement community-specific bridge functions if needed
    return hpt_get_card_data_generic($post_id, $args);
}

/**
 * City card data adapter
 * 
 * @param int $post_id Post ID
 * @param array $args Component arguments
 * @return array|null Card data array or null if invalid
 */
function hpt_get_card_data_city($post_id, $args = array()) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'city') {
        return null;
    }
    
    // Get city data using bridge
    require_once get_template_directory() . '/includes/bridge/city-bridge.php';
    $city_data = hpt_get_city($post_id);
    
    if (!$city_data) {
        return null;
    }
    
    // Get featured image
    $image_url = '';
    $image_alt = '';
    if (has_post_thumbnail($post_id)) {
        $image_url = get_the_post_thumbnail_url($post_id, 'medium');
        $image_alt = get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true) ?: $post->post_title;
    }
    
    // Build location string using actual fields (State, County format)
    $location_parts = array_filter([
        $city_data['state'] ?? '',
        $city_data['county'] ?? ''
    ]);
    $location_string = implode(', ', $location_parts);
    
    // Build stats array using actual fields
    $stats = [];
    
    if (!empty($city_data['population'])) {
        $population = number_format((int)$city_data['population']);
        $stats[] = "Population: {$population}";
    }
    
    if (!empty($city_data['tagline'])) {
        $stats[] = $city_data['tagline'];
    }
    
    // Add coordinates if available
    if (!empty($city_data['lat']) && !empty($city_data['lng'])) {
        $stats[] = "Lat: {$city_data['lat']}, Lng: {$city_data['lng']}";
    }
    
    // Build image data for card template
    $image_data = null;
    if ($image_url) {
        $image_data = array(
            'src' => $image_url,
            'alt' => $image_alt,
            'ratio' => 'landscape'
        );
    }
    
    return array(
        'title' => array(
            'text' => $location_string ?: $post->post_title,
            'tag' => 'h3'
        ),
        'subtitle' => $post->post_title,
        'description' => wp_trim_words(!empty($city_data['description']) ? strip_tags($city_data['description']) : get_the_excerpt($post_id), 20),
        'image' => $image_data,
        'link_wrapper' => get_permalink($post_id),
        'meta_items' => array(
            array('icon' => 'map-marker', 'text' => $location_string)
        ),
        'badges' => array(),
        'actions' => array(),
        'stats' => $stats,
        'meta' => array(
            'state' => $city_data['state'] ?? '',
            'county' => $city_data['county'] ?? '',
            'population' => $city_data['population'] ?? '',
            'tagline' => $city_data['tagline'] ?? '',
            'lat' => $city_data['lat'] ?? '',
            'lng' => $city_data['lng'] ?? ''
        )
    );
}

/**
 * Open House card data adapter
 * 
 * @param int $post_id Post ID
 * @param array $args Component arguments
 * @return array|null Card data array or null if invalid
 */
function hpt_get_card_data_open_house($post_id, $args = array()) {
    // Similar to events for now
    return hpt_get_card_data_local_event($post_id, $args);
}

/**
 * Staff card data adapter
 * 
 * @param int $post_id Post ID
 * @param array $args Component arguments
 * @return array|null Card data array or null if invalid
 */
function hpt_get_card_data_staff($post_id, $args = array()) {
    // Similar to agents for now
    return hpt_get_card_data_agent($post_id, $args);
}

/**
 * Blog Post card data adapter
 * 
 * @param int $post_id Post ID
 * @param array $args Component arguments
 * @return array|null Card data array or null if invalid
 */
function hpt_get_card_data_post($post_id, $args = array()) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'post') {
        return null;
    }
    
    // Build image data
    $image_data = null;
    if (has_post_thumbnail($post_id)) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        $image_url = wp_get_attachment_image_url($thumbnail_id, 'large');
        $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
        
        $image_data = array(
            'src' => $image_url,
            'alt' => $image_alt ?: $post->post_title,
            'ratio' => 'landscape'
        );
    }
    
    // Get categories
    $categories = get_the_category($post_id);
    $category_names = wp_list_pluck($categories, 'name');
    
    // Build meta items
    $meta_items = array(
        array('icon' => 'calendar', 'text' => get_the_date('M j, Y', $post)),
        array('icon' => 'user', 'text' => get_the_author_meta('display_name', $post->post_author))
    );
    
    if (!empty($category_names)) {
        $meta_items[] = array('icon' => 'folder', 'text' => implode(', ', $category_names));
    }
    
    return array(
        'title' => array(
            'text' => $post->post_title,
            'tag' => 'h3'
        ),
        'subtitle' => get_the_date('M j, Y', $post),
        'description' => $args['show_excerpt'] ? get_the_excerpt($post) : '',
        'image' => $image_data,
        'link_wrapper' => $args['clickable'] ? get_permalink($post) : '',
        'meta_items' => $meta_items,
        'badges' => array(),
        'actions' => array()
    );
}
?>