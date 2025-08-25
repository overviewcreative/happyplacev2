<?php
/**
 * Open House Bridge Functions
 * 
 * Provides a comprehensive interface between the plugin layer and templates
 * for the open_house post type. All data access should go through these functions
 * rather than direct WordPress or ACF calls.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all open house data
 * 
 * @param int|WP_Post $open_house Open House ID or post object
 * @return array Complete open house data
 */
function hpt_get_open_house($open_house = null) {
    $open_house = get_post($open_house);
    
    if (!$open_house || $open_house->post_type !== 'open_house') {
        return null;
    }
    
    // Get enhanced data from Open House Service if available
    $enhanced_data = null;
    $rsvp_count = 0;
    
    if (class_exists('HappyPlace\\Services\\OpenHouseService')) {
        $service = new \HappyPlace\Services\OpenHouseService();
        $service->init();
        $rsvp_count = $service->get_rsvp_count($open_house->ID);
    }
    
    $base_data = array(
        'id' => $open_house->ID,
        'title' => get_the_title($open_house),
        'slug' => $open_house->post_name,
        'url' => get_permalink($open_house),
        'status' => $open_house->post_status,
        'date_created' => $open_house->post_date,
        'date_modified' => $open_house->post_modified,
        
        // Event details
        'open_house_date' => hpt_get_open_house_date($open_house->ID),
        'start_time' => hpt_get_open_house_start_time($open_house->ID),
        'end_time' => hpt_get_open_house_end_time($open_house->ID),
        'duration' => hpt_get_open_house_duration($open_house->ID),
        'timezone' => hpt_get_open_house_timezone($open_house->ID),
        
        // Description and notes
        'description' => hpt_get_open_house_description($open_house->ID),
        'special_instructions' => hpt_get_open_house_instructions($open_house->ID),
        'notes' => hpt_get_open_house_notes($open_house->ID),
        
        // Relationships
        'listing' => hpt_get_open_house_listing($open_house->ID),
        'agent' => hpt_get_open_house_agent($open_house->ID),
        'co_agent' => hpt_get_open_house_co_agent($open_house->ID),
        
        // Registration and tracking
        'registration_required' => hpt_is_open_house_registration_required($open_house->ID),
        'max_attendees' => hpt_get_open_house_max_attendees($open_house->ID),
        'current_registrations' => hpt_get_open_house_registration_count($open_house->ID),
        'available_spots' => hpt_get_open_house_available_spots($open_house->ID),
        'rsvp_count' => $rsvp_count, // From service
        
        // Contact information
        'contact_phone' => hpt_get_open_house_contact_phone($open_house->ID),
        'contact_email' => hpt_get_open_house_contact_email($open_house->ID),
        
        // Status and visibility
        'event_status' => hpt_get_open_house_event_status($open_house->ID),
        'is_featured' => hpt_is_open_house_featured($open_house->ID),
        'is_virtual' => hpt_is_open_house_virtual($open_house->ID),
        'virtual_link' => hpt_get_open_house_virtual_link($open_house->ID),
        
        // Computed fields
        'is_upcoming' => hpt_is_open_house_upcoming($open_house->ID),
        'is_today' => hpt_is_open_house_today($open_house->ID),
        'is_past' => hpt_is_open_house_past($open_house->ID),
        'formatted_datetime' => hpt_get_open_house_formatted_datetime($open_house->ID),
        'time_until' => hpt_get_open_house_time_until($open_house->ID),
    );
}

/**
 * Get open house date
 */
function hpt_get_open_house_date($open_house_id) {
    return get_field('open_house_date', $open_house_id) ?: get_field('event_date', $open_house_id) ?: '';
}

/**
 * Get open house start time
 */
function hpt_get_open_house_start_time($open_house_id) {
    return get_field('start_time', $open_house_id) ?: '10:00';
}

/**
 * Get open house end time
 */
function hpt_get_open_house_end_time($open_house_id) {
    return get_field('end_time', $open_house_id) ?: '12:00';
}

/**
 * Get open house duration in minutes
 */
function hpt_get_open_house_duration($open_house_id) {
    $start = hpt_get_open_house_start_time($open_house_id);
    $end = hpt_get_open_house_end_time($open_house_id);
    
    if (!$start || !$end) {
        return 120; // Default 2 hours
    }
    
    $start_timestamp = strtotime($start);
    $end_timestamp = strtotime($end);
    
    return round(($end_timestamp - $start_timestamp) / 60);
}

/**
 * Get open house timezone
 */
function hpt_get_open_house_timezone($open_house_id) {
    return get_field('timezone', $open_house_id) ?: wp_timezone_string();
}

/**
 * Get open house description
 */
function hpt_get_open_house_description($open_house_id) {
    $description = get_field('description', $open_house_id);
    
    if (!$description) {
        $post = get_post($open_house_id);
        $description = $post->post_content;
    }
    
    return $description;
}

/**
 * Get special instructions
 */
function hpt_get_open_house_instructions($open_house_id) {
    return get_field('special_instructions', $open_house_id) ?: '';
}

/**
 * Get open house notes
 */
function hpt_get_open_house_notes($open_house_id) {
    return get_field('notes', $open_house_id) ?: get_field('agent_notes', $open_house_id) ?: '';
}

/**
 * Get related listing
 */
function hpt_get_open_house_listing($open_house_id) {
    $listing_id = get_field('related_listing', $open_house_id);
    
    if (!$listing_id) {
        $listing_id = get_field('listing', $open_house_id);
    }
    
    return $listing_id ? intval($listing_id) : null;
}

/**
 * Get hosting agent
 */
function hpt_get_open_house_agent($open_house_id) {
    $agent_id = get_field('hosting_agent', $open_house_id);
    
    if (!$agent_id) {
        $agent_id = get_field('agent', $open_house_id);
    }
    
    // If no agent specified, try to get from related listing
    if (!$agent_id) {
        $listing_id = hpt_get_open_house_listing($open_house_id);
        if ($listing_id) {
            $agent_id = hpt_get_listing_agent($listing_id);
        }
    }
    
    return $agent_id ? intval($agent_id) : null;
}

/**
 * Get co-hosting agent
 */
function hpt_get_open_house_co_agent($open_house_id) {
    $agent_id = get_field('co_hosting_agent', $open_house_id);
    
    if (!$agent_id) {
        $agent_id = get_field('co_agent', $open_house_id);
    }
    
    return $agent_id ? intval($agent_id) : null;
}

/**
 * Check if registration is required
 */
function hpt_is_open_house_registration_required($open_house_id) {
    return get_field('registration_required', $open_house_id) == true;
}

/**
 * Get maximum attendees
 */
function hpt_get_open_house_max_attendees($open_house_id) {
    return intval(get_field('max_attendees', $open_house_id));
}

/**
 * Get current registration count
 */
function hpt_get_open_house_registration_count($open_house_id) {
    $count = get_field('registration_count', $open_house_id);
    
    if ($count === null || $count === false) {
        // Count registrations from a registration system
        // This would typically query a separate registrations table
        $count = 0;
    }
    
    return intval($count);
}

/**
 * Get available spots
 */
function hpt_get_open_house_available_spots($open_house_id) {
    $max = hpt_get_open_house_max_attendees($open_house_id);
    $current = hpt_get_open_house_registration_count($open_house_id);
    
    if ($max <= 0) {
        return -1; // Unlimited
    }
    
    return max(0, $max - $current);
}

/**
 * Get contact phone
 */
function hpt_get_open_house_contact_phone($open_house_id) {
    $phone = get_field('contact_phone', $open_house_id);
    
    if (!$phone) {
        $agent_id = hpt_get_open_house_agent($open_house_id);
        if ($agent_id) {
            $phone = hpt_get_agent_phone($agent_id);
        }
    }
    
    return $phone ?: '';
}

/**
 * Get contact email
 */
function hpt_get_open_house_contact_email($open_house_id) {
    $email = get_field('contact_email', $open_house_id);
    
    if (!$email) {
        $agent_id = hpt_get_open_house_agent($open_house_id);
        if ($agent_id) {
            $email = hpt_get_agent_email($agent_id);
        }
    }
    
    return $email ?: '';
}

/**
 * Get event status
 */
function hpt_get_open_house_event_status($open_house_id) {
    return get_field('event_status', $open_house_id) ?: 'scheduled';
}

/**
 * Get event status label
 */
function hpt_get_open_house_event_status_label($open_house_id) {
    $status = hpt_get_open_house_event_status($open_house_id);
    
    $labels = array(
        'scheduled' => __('Scheduled', 'happy-place-theme'),
        'cancelled' => __('Cancelled', 'happy-place-theme'),
        'postponed' => __('Postponed', 'happy-place-theme'),
        'completed' => __('Completed', 'happy-place-theme'),
        'in_progress' => __('In Progress', 'happy-place-theme'),
    );
    
    return $labels[$status] ?? ucfirst($status);
}

/**
 * Check if open house is featured
 */
function hpt_is_open_house_featured($open_house_id) {
    return get_field('featured', $open_house_id) == true;
}

/**
 * Check if open house is virtual
 */
function hpt_is_open_house_virtual($open_house_id) {
    return get_field('is_virtual', $open_house_id) == true;
}

/**
 * Get virtual meeting link
 */
function hpt_get_open_house_virtual_link($open_house_id) {
    return get_field('virtual_link', $open_house_id) ?: get_field('meeting_link', $open_house_id) ?: '';
}

/**
 * Check if open house is upcoming
 */
function hpt_is_open_house_upcoming($open_house_id) {
    $date = hpt_get_open_house_date($open_house_id);
    
    if (!$date) {
        return false;
    }
    
    return strtotime($date) > time();
}

/**
 * Check if open house is today
 */
function hpt_is_open_house_today($open_house_id) {
    $date = hpt_get_open_house_date($open_house_id);
    
    if (!$date) {
        return false;
    }
    
    return date('Y-m-d', strtotime($date)) === date('Y-m-d');
}

/**
 * Check if open house is past
 */
function hpt_is_open_house_past($open_house_id) {
    $date = hpt_get_open_house_date($open_house_id);
    $end_time = hpt_get_open_house_end_time($open_house_id);
    
    if (!$date) {
        return false;
    }
    
    $end_datetime = strtotime($date . ' ' . $end_time);
    
    return $end_datetime < time();
}

/**
 * Get formatted date and time
 */
function hpt_get_open_house_formatted_datetime($open_house_id) {
    $date = hpt_get_open_house_date($open_house_id);
    $start_time = hpt_get_open_house_start_time($open_house_id);
    $end_time = hpt_get_open_house_end_time($open_house_id);
    
    if (!$date) {
        return '';
    }
    
    $formatted_date = date_i18n(get_option('date_format'), strtotime($date));
    $formatted_start = date_i18n(get_option('time_format'), strtotime($start_time));
    $formatted_end = date_i18n(get_option('time_format'), strtotime($end_time));
    
    return sprintf(
        __('%s from %s to %s', 'happy-place-theme'),
        $formatted_date,
        $formatted_start,
        $formatted_end
    );
}

/**
 * Get time until open house
 */
function hpt_get_open_house_time_until($open_house_id) {
    $date = hpt_get_open_house_date($open_house_id);
    $start_time = hpt_get_open_house_start_time($open_house_id);
    
    if (!$date) {
        return '';
    }
    
    $event_timestamp = strtotime($date . ' ' . $start_time);
    $current_timestamp = time();
    
    if ($event_timestamp <= $current_timestamp) {
        return '';
    }
    
    $diff = $event_timestamp - $current_timestamp;
    
    if ($diff < 3600) { // Less than 1 hour
        $minutes = round($diff / 60);
        return sprintf(_n('%d minute', '%d minutes', $minutes, 'happy-place-theme'), $minutes);
    } elseif ($diff < 86400) { // Less than 1 day
        $hours = round($diff / 3600);
        return sprintf(_n('%d hour', '%d hours', $hours, 'happy-place-theme'), $hours);
    } else {
        $days = round($diff / 86400);
        return sprintf(_n('%d day', '%d days', $days, 'happy-place-theme'), $days);
    }
}

/**
 * Query open houses
 */
function hpt_query_open_houses($args = array()) {
    $defaults = array(
        'post_type' => 'open_house',
        'post_status' => 'publish',
        'posts_per_page' => 10,
        'meta_key' => 'open_house_date',
        'orderby' => 'meta_value',
        'order' => 'ASC',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    return new WP_Query($args);
}

/**
 * Get upcoming open houses
 */
function hpt_get_upcoming_open_houses($limit = 5) {
    return get_posts(array(
        'post_type' => 'open_house',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'open_house_date',
                'value' => date('Y-m-d'),
                'compare' => '>='
            ),
            array(
                'key' => 'event_status',
                'value' => 'scheduled',
                'compare' => '='
            )
        ),
        'meta_key' => 'open_house_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    ));
}

/**
 * Get today's open houses
 */
function hpt_get_todays_open_houses() {
    return get_posts(array(
        'post_type' => 'open_house',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'open_house_date',
                'value' => date('Y-m-d'),
                'compare' => '='
            ),
            array(
                'key' => 'event_status',
                'value' => 'scheduled',
                'compare' => '='
            )
        ),
        'meta_key' => 'start_time',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    ));
}

/**
 * Get open houses by agent
 */
function hpt_get_agent_open_houses($agent_id, $upcoming_only = true) {
    $meta_query = array(
        array(
            'key' => 'hosting_agent',
            'value' => $agent_id,
            'compare' => '='
        )
    );
    
    if ($upcoming_only) {
        $meta_query[] = array(
            'key' => 'open_house_date',
            'value' => date('Y-m-d'),
            'compare' => '>='
        );
    }
    
    return get_posts(array(
        'post_type' => 'open_house',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => $meta_query,
        'meta_key' => 'open_house_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    ));
}

/**
 * Get open houses by listing
 */
if (!function_exists('hpt_get_listing_open_houses')) {
    function hpt_get_listing_open_houses($listing_id, $upcoming_only = true) {
        $meta_query = array(
            array(
                'key' => 'related_listing',
                'value' => $listing_id,
                'compare' => '='
            )
        );
        
        if ($upcoming_only) {
            $meta_query[] = array(
                'key' => 'open_house_date',
                'value' => date('Y-m-d'),
                'compare' => '>='
            );
        }
        
        return get_posts(array(
            'post_type' => 'open_house',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => $meta_query,
            'meta_key' => 'open_house_date',
            'orderby' => 'meta_value',
            'order' => 'ASC'
        ));
    }
}

/**
 * Get featured open houses
 */
function hpt_get_featured_open_houses($limit = 3) {
    return get_posts(array(
        'post_type' => 'open_house',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'open_house_date',
                'value' => date('Y-m-d'),
                'compare' => '>='
            ),
            array(
                'key' => 'featured',
                'value' => true,
                'compare' => '='
            )
        ),
        'meta_key' => 'open_house_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    ));
}

/**
 * Get upcoming open houses with service integration
 * 
 * @param int $limit Number of open houses to retrieve
 * @return array Open house data with RSVP counts
 */
function hpt_get_upcoming_open_houses_with_service($limit = 5) {
    $upcoming = [];
    
    if (class_exists('HappyPlace\\Services\\OpenHouseService')) {
        $service = new \HappyPlace\Services\OpenHouseService();
        $service->init();
        $upcoming = $service->get_upcoming_open_houses($limit);
    } else {
        // Fallback to basic query
        $posts = hpt_get_upcoming_open_houses($limit);
        foreach ($posts as $post) {
            $data = hpt_get_open_house($post->ID);
            if ($data) {
                $upcoming[] = $data;
            }
        }
    }
    
    return $upcoming;
}