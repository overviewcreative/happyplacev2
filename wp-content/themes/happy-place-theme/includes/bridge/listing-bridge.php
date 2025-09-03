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
        
        // Additional extended fields
        'style' => hpt_get_listing_style($listing->ID),
        'virtual_tour_url' => hpt_get_listing_virtual_tour_url($listing->ID),
        'price_per_sqft' => hpt_get_listing_price_per_sqft($listing->ID),
    );
}

/**
 * Get listing title
 * 
 * @param int $listing_id Listing ID
 * @return string Listing title
 */
function hpt_get_listing_title($listing_id) {
    return get_the_title($listing_id) ?: '';
}

/**
 * Get listing price
 */
function hpt_get_listing_price($listing_id) {
    $price = get_field('listing_price', $listing_id) ?: get_field('price', $listing_id) ?: 0;
    
    // Handle string values with commas/formatting
    if (is_string($price)) {
        // Remove any non-numeric characters except decimal point
        $price = preg_replace('/[^0-9.]/', '', $price);
    }
    
    return floatval($price);
}

/**
 * Get formatted listing price
 */
function hpt_get_listing_price_formatted($listing_id) {
    $price = hpt_get_listing_price($listing_id);
    
    if (!$price || $price <= 0) {
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
    // Try taxonomy first (as in extended bridge functions)
    $terms = get_the_terms($listing_id, 'property_type');
    if ($terms && !is_wp_error($terms)) {
        return $terms[0]->name;
    }
    
    // Fallback to ACF field
    $field_value = get_field('property_type', $listing_id);
    return $field_value ?: 'Residential';
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
    if (!$listing_id) {
        return null;
    }
    
    // Get separate full and half bathroom fields from ACF
    $full_baths = get_field('bathrooms_full', $listing_id);
    $half_baths = get_field('bathrooms_half', $listing_id);
    
    // Convert to numbers with null safety
    $full_baths = $full_baths !== null ? floatval($full_baths) : 0;
    $half_baths = $half_baths !== null ? floatval($half_baths) : 0;
    
    // Calculate total: full baths + (half baths * 0.5)
    $total_baths = $full_baths + ($half_baths * 0.5);
    
    // Return null if no bathroom data exists
    if ($total_baths <= 0) {
        // Fallback to legacy single field or post meta
        $legacy_baths = get_field('bathrooms', $listing_id) ?: get_post_meta($listing_id, 'bathrooms', true);
        if ($legacy_baths) {
            // Handle string values with commas/formatting  
            if (is_string($legacy_baths)) {
                $legacy_baths = preg_replace('/[^0-9.]/', '', $legacy_baths);
            }
            return floatval($legacy_baths) ?: null;
        }
        return null;
    }
    
    return $total_baths;
}

/**
 * Get full bathrooms count
 */
function hpt_get_listing_bathrooms_full($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $full_baths = get_field('bathrooms_full', $listing_id);
    return $full_baths !== null ? intval($full_baths) : null;
}

/**
 * Get half bathrooms count
 */
function hpt_get_listing_bathrooms_half($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $half_baths = get_field('bathrooms_half', $listing_id);
    return $half_baths !== null ? intval($half_baths) : null;
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
    $sqft = get_field('square_feet', $listing_id);
    
    // Handle string values with commas/formatting
    if (is_string($sqft)) {
        // Remove any non-numeric characters
        $sqft = preg_replace('/[^0-9]/', '', $sqft);
    }
    
    return intval($sqft);
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
 * Get lot size (in acres)
 */
function hpt_get_listing_lot_size($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    // Get ACF lot size fields - prefer acres, fallback to sqft  
    $lot_size_acres = get_field('lot_size_acres', $listing_id);
    $lot_size_sqft = get_field('lot_size_sqft', $listing_id);
    
    // If we have acres, use that
    if ($lot_size_acres !== null && $lot_size_acres > 0) {
        return round(floatval($lot_size_acres), 3); // Return in acres
    }
    
    // If we have square feet, convert to acres
    if ($lot_size_sqft !== null && $lot_size_sqft > 0) {
        return round(floatval($lot_size_sqft) / 43560, 3); // Convert sq ft to acres
    }
    
    // Fallback to legacy field for backward compatibility
    $legacy_lot_size = get_field('lot_size', $listing_id);
    if ($legacy_lot_size) {
        // Check if there's a unit field to determine conversion
        $lot_size_unit = get_field('lot_size_unit', $listing_id);
        if ($lot_size_unit === 'sqft') {
            return round(floatval($legacy_lot_size) / 43560, 3);
        }
        return round(floatval($legacy_lot_size), 3);
    }
    
    return null;
}

/**
 * Get lot size in acres (raw field)
 */
function hpt_get_listing_lot_size_acres($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $acres = get_field('lot_size_acres', $listing_id);
    return $acres !== null ? floatval($acres) : null;
}

/**
 * Get lot size in square feet (raw field)
 */
function hpt_get_listing_lot_size_sqft($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $sqft = get_field('lot_size_sqft', $listing_id);
    return $sqft !== null ? floatval($sqft) : null;
}

/**
 * Get lot size in square feet (converted if needed)
 */
function hpt_get_listing_lot_size_in_sqft($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    // First check if we have sqft directly
    $lot_size_sqft = get_field('lot_size_sqft', $listing_id);
    if ($lot_size_sqft !== null && $lot_size_sqft > 0) {
        return intval($lot_size_sqft);
    }
    
    // Convert from acres if available
    $lot_size_acres = get_field('lot_size_acres', $listing_id);
    if ($lot_size_acres !== null && $lot_size_acres > 0) {
        return intval($lot_size_acres * 43560);
    }
    
    return null;
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
    // Get individual address components
    $street_number = get_field('street_number', $listing_id) ?: '';
    $street_dir_prefix = get_field('street_dir_prefix', $listing_id) ?: '';
    $street_name = get_field('street_name', $listing_id) ?: '';
    $street_type = get_field('street_type', $listing_id) ?: get_field('street_suffix', $listing_id) ?: '';
    $street_dir_suffix = get_field('street_dir_suffix', $listing_id) ?: '';
    $unit_number = get_field('unit_number', $listing_id) ?: '';
    $street_address = get_field('street_address', $listing_id) ?: '';
    
    // Build complete street address from components
    if ($street_number || $street_name) {
        $street_parts = array_filter(array(
            $street_number,
            $street_dir_prefix,
            $street_name,
            $street_type,
            $street_dir_suffix,
            $unit_number ? $unit_number : ''
        ));
        $street = implode(' ', $street_parts);
    } else {
        // Fallback to street_address field for backward compatibility
        $street = $street_address;
    }
    
    $address = array(
        'street' => $street,
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
    // Get individual address components
    $street_number = get_field('street_number', $listing_id) ?: '';
    $street_dir_prefix = get_field('street_dir_prefix', $listing_id) ?: '';
    $street_name = get_field('street_name', $listing_id) ?: '';
    $street_type = get_field('street_type', $listing_id) ?: get_field('street_suffix', $listing_id) ?: '';
    $street_dir_suffix = get_field('street_dir_suffix', $listing_id) ?: '';
    $unit_number = get_field('unit_number', $listing_id) ?: '';
    $street_address = get_field('street_address', $listing_id) ?: '';
    
    // Build complete street address from components
    if ($street_number || $street_name) {
        $street_parts = array_filter(array(
            $street_number,
            $street_dir_prefix,
            $street_name,
            $street_type,
            $street_dir_suffix,
            $unit_number ? $unit_number : ''
        ));
        return implode(' ', $street_parts);
    }
    
    // Fallback to street_address field for backward compatibility
    return $street_address;
}

/**
 * Get listing street number
 */
function hpt_get_listing_street_number($listing_id) {
    return get_field('street_number', $listing_id) ?: '';
}

/**
 * Get listing street name
 */
function hpt_get_listing_street_name($listing_id) {
    return get_field('street_name', $listing_id) ?: '';
}

/**
 * Get listing street type/suffix (abbreviated)
 */
function hpt_get_listing_street_type($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    // Get correct ACF field name 
    $street_type = get_field('street_type', $listing_id);
    
    if (!$street_type) {
        // Fallback to legacy field name for backward compatibility
        $street_type = get_field('street_suffix', $listing_id);
    }
    
    return !empty($street_type) ? $street_type : null;
}

/**
 * Get listing street direction prefix
 */
function hpt_get_listing_street_prefix($listing_id) {
    return get_field('street_dir_prefix', $listing_id) ?: '';
}

/**
 * Get listing street direction suffix
 */
function hpt_get_listing_street_suffix($listing_id) {
    return get_field('street_dir_suffix', $listing_id) ?: '';
}

/**
 * Get listing unit number
 */
function hpt_get_listing_unit_number($listing_id) {
    return get_field('unit_number', $listing_id) ?: '';
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
    // Try primary coordinate fields
    $lat = get_field('latitude', $listing_id);
    $lng = get_field('longitude', $listing_id);
    
    // Try alternative field names if primary ones are empty
    if (empty($lat) || empty($lng)) {
        $lat = $lat ?: get_field('lat', $listing_id);
        $lng = $lng ?: get_field('lng', $listing_id);
        $lng = $lng ?: get_field('long', $listing_id);
    }
    
    // Try location field (ACF Google Map field)
    if (empty($lat) || empty($lng)) {
        $location = get_field('location', $listing_id);
        if (is_array($location) && isset($location['lat']) && isset($location['lng'])) {
            $lat = $location['lat'];
            $lng = $location['lng'];
        }
    }
    
    // Try google_map field
    if (empty($lat) || empty($lng)) {
        $google_map = get_field('google_map', $listing_id);
        if (is_array($google_map) && isset($google_map['lat']) && isset($google_map['lng'])) {
            $lat = $google_map['lat'];
            $lng = $google_map['lng'];
        }
    }
    
    return array(
        'lat' => floatval($lat),
        'lng' => floatval($lng),
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
    if (!$listing_id) {
        return null;
    }
    
    // Get ACF property description field (WYSIWYG)
    $description = get_field('property_description', $listing_id);
    
    // Fallback chain for backward compatibility
    if (!$description) {
        // Try legacy description field
        $description = get_field('description', $listing_id);
    }
    
    if (!$description) {
        // Try post content as final fallback
        $post = get_post($listing_id);
        if ($post && !empty($post->post_content)) {
            $description = $post->post_content;
        }
    }
    
    // Clean up and return
    if ($description) {
        // Remove empty paragraphs and extra whitespace
        $description = trim(preg_replace('/<p[^>]*>\\s*<\\/p>/i', '', $description));
        return !empty($description) ? $description : null;
    }
    
    return null;
}

/**
 * Get property marketing title (override)
 */
function hpt_get_listing_marketing_title($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $title = get_field('property_title', $listing_id);
    return !empty($title) ? trim($title) : null;
}

/**
 * Get property highlights array
 */
function hpt_get_listing_highlights($listing_id) {
    if (!$listing_id) {
        return array();
    }
    
    $highlights = get_field('property_highlights', $listing_id);
    
    if (!$highlights || !is_array($highlights)) {
        return array();
    }
    
    // Extract just the text values from repeater field
    $highlight_texts = array();
    foreach ($highlights as $highlight) {
        if (isset($highlight['text']) && !empty($highlight['text'])) {
            $highlight_texts[] = trim($highlight['text']);
        }
    }
    
    return $highlight_texts;
}

/**
 * Get showing instructions
 */
function hpt_get_listing_showing_instructions($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $instructions = get_field('showing_instructions', $listing_id);
    return !empty($instructions) ? trim($instructions) : null;
}

/**
 * Get internal notes (private)
 */
function hpt_get_listing_internal_notes($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $notes = get_field('internal_notes', $listing_id);
    return !empty($notes) ? trim($notes) : null;
}

/**
 * Get listing features (basic)
 */
function hpt_get_listing_features($listing_id) {
    $features = get_field('features', $listing_id);
    
    if (!is_array($features)) {
        $features = get_field('property_features', $listing_id) ?: array();
    }
    
    return is_array($features) ? $features : array();
}

/**
 * Get comprehensive listing features organized by category
 */
function hpt_get_listing_features_categorized($listing_id) {
    return array(
        'interior_features' => hpt_get_listing_interior_features($listing_id),
        'exterior_features' => hpt_get_listing_exterior_features($listing_id),
        'heating_cooling' => hpt_get_listing_heating_cooling($listing_id),
        'kitchen_appliances' => hpt_get_listing_kitchen_appliances($listing_id),
        'flooring' => hpt_get_listing_flooring($listing_id),
        'parking_garage' => hpt_get_listing_parking_garage($listing_id),
        'lot_features' => hpt_get_listing_lot_features($listing_id),
        'utilities' => hpt_get_listing_utilities($listing_id),
        'construction' => hpt_get_listing_construction($listing_id),
        'roof_info' => hpt_get_listing_roof_info($listing_id),
        'foundation' => hpt_get_listing_foundation($listing_id),
        'security_features' => hpt_get_listing_security_features($listing_id),
        'accessibility_features' => hpt_get_listing_accessibility_features($listing_id),
        'green_features' => hpt_get_listing_green_features($listing_id),
        'additional_features' => hpt_get_listing_additional_features($listing_id)
    );
}

/**
 * Get interior features
 */
function hpt_get_listing_interior_features($listing_id) {
    return hpt_get_feature_category($listing_id, 'interior_features', array(
        'Central Air', 'Ceiling Fans', 'Crown Molding', 'Recessed Lighting',
        'Walk-In Closets', 'Primary Bedroom on Main', 'Office/Den', 'Breakfast Bar',
        'Pantry', 'Laundry Room', 'Wet Bar', 'Built-In Shelving',
        'Cathedral Ceilings', 'Skylights', 'Window Treatments'
    ));
}

/**
 * Get exterior features
 */
function hpt_get_listing_exterior_features($listing_id) {
    return hpt_get_feature_category($listing_id, 'exterior_features', array(
        'Covered Patio', 'Deck', 'Fenced Yard', 'Sprinkler System',
        'Outdoor Kitchen', 'Fire Pit', 'Storage Shed', 'Mature Trees',
        'Professional Landscaping', 'Exterior Lighting', 'Gutters', 'Storm Doors'
    ));
}

/**
 * Get heating and cooling features
 */
function hpt_get_listing_heating_cooling($listing_id) {
    return hpt_get_feature_category($listing_id, 'heating_cooling', array(
        'Central Air', 'Forced Air Heat', 'Heat Pump', 'Zoned HVAC',
        'Programmable Thermostat', 'Ceiling Fans'
    ));
}

/**
 * Get kitchen and appliances features
 */
function hpt_get_listing_kitchen_appliances($listing_id) {
    return hpt_get_feature_category($listing_id, 'kitchen_appliances', array(
        'Stainless Steel Appliances', 'Gas Range', 'Double Oven', 'Built-In Microwave',
        'Dishwasher', 'Garbage Disposal', 'Kitchen Island', 'Granite Countertops',
        'Tile Backsplash', 'Under Cabinet Lighting'
    ));
}

/**
 * Get flooring features
 */
function hpt_get_listing_flooring($listing_id) {
    return hpt_get_feature_category($listing_id, 'flooring', array(
        'Hardwood Floors', 'Tile', 'Carpet', 'Luxury Vinyl Plank', 'Laminate'
    ));
}

/**
 * Get parking and garage features
 */
function hpt_get_listing_parking_garage($listing_id) {
    return hpt_get_feature_category($listing_id, 'parking_garage', array(
        '2 Car Garage', 'Attached Garage', 'Garage Door Opener',
        'Driveway - Paved', 'Additional Parking Pad'
    ));
}

/**
 * Get lot features
 */
function hpt_get_listing_lot_features($listing_id) {
    return hpt_get_feature_category($listing_id, 'lot_features', array(
        'Corner Lot', 'Cul-de-Sac', 'Level Lot', 'Wooded Lot', 'Private Backyard'
    ));
}

/**
 * Get utilities
 */
function hpt_get_listing_utilities($listing_id) {
    return hpt_get_feature_category($listing_id, 'utilities', array(
        'Electric', 'Natural Gas', 'City Water', 'City Sewer', 'Cable Ready'
    ));
}

/**
 * Get construction features
 */
function hpt_get_listing_construction($listing_id) {
    return hpt_get_feature_category($listing_id, 'construction', array(
        'Brick', 'Vinyl Siding', 'Frame Construction', 'Stone Accents'
    ));
}

/**
 * Get roof information
 */
function hpt_get_listing_roof_info($listing_id) {
    return hpt_get_feature_category($listing_id, 'roof_info', array(
        'Shingle Roof', 'Metal Roof', 'Tile Roof', 'New Roof'
    ));
}

/**
 * Get foundation features
 */
function hpt_get_listing_foundation($listing_id) {
    return hpt_get_feature_category($listing_id, 'foundation', array(
        'Basement', 'Crawl Space', 'Slab Foundation', 'Finished Basement'
    ));
}

/**
 * Get security features
 */
function hpt_get_listing_security_features($listing_id) {
    return hpt_get_feature_category($listing_id, 'security_features', array(
        'Security System', 'Smoke Detectors', 'Carbon Monoxide Detectors', 'Dead Bolts'
    ));
}

/**
 * Get accessibility features
 */
function hpt_get_listing_accessibility_features($listing_id) {
    return hpt_get_feature_category($listing_id, 'accessibility_features', array(
        'Wheelchair Accessible', 'Ramp Access', 'Wide Doorways', 'Accessible Bathroom'
    ));
}

/**
 * Get green/energy features
 */
function hpt_get_listing_green_features($listing_id) {
    return hpt_get_feature_category($listing_id, 'green_features', array(
        'Energy Star Appliances', 'Solar Panels', 'High Efficiency Windows',
        'Programmable Thermostat', 'LED Lighting'
    ));
}

/**
 * Get additional features
 */
function hpt_get_listing_additional_features($listing_id) {
    return hpt_get_feature_category($listing_id, 'additional_features', array());
}

/**
 * Helper function to get feature category with fallbacks
 */
function hpt_get_feature_category($listing_id, $category, $default_features = array()) {
    // Try to get specific category field
    $features = get_field($category, $listing_id);
    
    if (!empty($features) && is_array($features)) {
        return $features;
    }
    
    // Try alternate field names
    $alternate_names = array(
        'interior_features' => array('interior', 'inside_features'),
        'exterior_features' => array('exterior', 'outside_features'),
        'heating_cooling' => array('hvac', 'climate_control'),
        'kitchen_appliances' => array('kitchen', 'appliances'),
        'parking_garage' => array('parking', 'garage'),
        'lot_features' => array('lot', 'yard_features'),
        'security_features' => array('security', 'safety_features'),
        'green_features' => array('energy_features', 'eco_features')
    );
    
    if (isset($alternate_names[$category])) {
        foreach ($alternate_names[$category] as $alt_name) {
            $alt_features = get_field($alt_name, $listing_id);
            if (!empty($alt_features) && is_array($alt_features)) {
                return $alt_features;
            }
        }
    }
    
    // Return default features for demo purposes
    return $default_features;
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
    
    return array_map(function($plan) {
        if (is_array($plan)) {
            return array(
                'id' => $plan['image']['ID'] ?? $plan['ID'],
                'url' => $plan['image']['url'] ?? $plan['url'],
                'title' => $plan['title'] ?? '',
                'sqft' => $plan['square_feet'] ?? $plan['sqft'] ?? '',
                'description' => $plan['description'] ?? ''
            );
        }
        
        return array(
            'id' => $plan,
            'url' => wp_get_attachment_image_url($plan, 'full'),
            'title' => get_the_title($plan),
            'sqft' => get_field('square_feet', $plan) ?: '',
            'description' => wp_get_attachment_caption($plan) ?: ''
        );
    }, $floor_plans);
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
 * Get listing property taxes
 */
function hpt_get_listing_property_taxes($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $taxes = get_field('property_taxes', $listing_id);
    return $taxes ? floatval($taxes) : null;
}

/**
 * Get HOA fees
 */
function hpt_get_listing_hoa_fees($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $hoa_fees = get_field('hoa_fees', $listing_id);
    return $hoa_fees ? floatval($hoa_fees) : null;
}

/**
 * Get buyer commission
 */
function hpt_get_listing_buyer_commission($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $commission = get_field('buyer_commission', $listing_id);
    return !empty($commission) ? trim($commission) : null;
}

/**
 * Get estimated insurance (monthly)
 */
function hpt_get_listing_estimated_insurance($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $insurance = get_field('estimated_insurance', $listing_id);
    return $insurance ? floatval($insurance) : null;
}

/**
 * Get estimated utilities (monthly)
 */
function hpt_get_listing_estimated_utilities($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $utilities = get_field('estimated_utilities', $listing_id);
    return $utilities ? floatval($utilities) : null;
}

/**
 * Get address display setting
 */
function hpt_get_listing_address_display($listing_id) {
    if (!$listing_id) {
        return 'full'; // Default to full display
    }
    
    $display = get_field('address_display', $listing_id);
    return !empty($display) ? $display : 'full';
}

/**
 * Get parcel number
 */
function hpt_get_listing_parcel_number($listing_id) {
    if (!$listing_id) {
        return null;
    }
    
    $parcel = get_field('parcel_number', $listing_id);
    return !empty($parcel) ? trim($parcel) : null;
}

/**
 * Get address with privacy controls applied
 */
function hpt_get_listing_address_public($listing_id, $format = 'full') {
    if (!$listing_id) {
        return null;
    }
    
    $display_setting = hpt_get_listing_address_display($listing_id);
    
    switch ($display_setting) {
        case 'hidden':
            return null; // Do not display address
            
        case 'area':
            // Show only city, state
            $city = get_field('city', $listing_id);
            $state = get_field('state', $listing_id);
            return trim("$city, $state");
            
        case 'street':
            // Show street name without number
            $street_name = get_field('street_name', $listing_id);
            $street_type = get_field('street_type', $listing_id) ?: get_field('street_suffix', $listing_id);
            $city = get_field('city', $listing_id);
            $state = get_field('state', $listing_id);
            return trim("$street_name $street_type, $city, $state");
            
        case 'full':
        default:
            // Show full address
            return hpt_get_listing_address($listing_id, $format);
    }
}

/**
 * Get listing commission
 */
function hpt_get_listing_commission($listing_id) {
    return get_field('buyer_agent_commission', $listing_id) ?: '';
}

/**
 * Get listing views count
 */
function hpt_get_listing_views($listing_id, $period = 'total') {
    $views_meta_key = 'listing_views_' . $period;
    return intval(get_post_meta($listing_id, $views_meta_key, true));
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
 * Get listing style
 */
function hpt_get_listing_style($listing_id) {
    return get_field('property_style', $listing_id) ?: '';
}

/**
 * Get virtual tour URL
 */
function hpt_get_listing_virtual_tour_url($listing_id) {
    return get_field('virtual_tour_url', $listing_id) ?: null;
}

/**
 * Check if listing is user favorite
 */
function hpt_is_listing_favorite($listing_id, $user_id) {
    if (!$user_id) return false;
    
    $favorites = get_user_meta($user_id, 'hph_favorite_listings', true);
    if (!is_array($favorites)) {
        $favorites = array();
    }
    
    return in_array($listing_id, $favorites);
}

/**
 * Get listing price per square foot
 */
function hpt_get_listing_price_per_sqft($listing_id) {
    $price = hpt_get_listing_price($listing_id);
    $sqft = hpt_get_listing_square_feet($listing_id);
    
    if ($price && $sqft && $sqft > 0) {
        return round($price / $sqft);
    }
    
    return null;
}

/**
 * Get raw listing price (for calculations)
 */
function hpt_get_listing_price_raw($listing_id) {
    return hpt_get_listing_price($listing_id);
}

/**
 * Get listing heating system
 */
function hpt_get_listing_heating($listing_id) {
    $heating = get_field('heating_system', $listing_id);
    
    if (!$heating) {
        $heating = get_field('heating', $listing_id);
    }
    
    if (!$heating) {
        $heating = get_field('heat_type', $listing_id);
    }
    
    return $heating ?: __('Central', 'happy-place-theme');
}

/**
 * Get listing cooling system
 */
function hpt_get_listing_cooling($listing_id) {
    $cooling = get_field('cooling_system', $listing_id);
    
    if (!$cooling) {
        $cooling = get_field('cooling', $listing_id);
    }
    
    if (!$cooling) {
        $cooling = get_field('air_conditioning', $listing_id);
    }
    
    return $cooling ?: __('Central Air', 'happy-place-theme');
}

/**
 * Get listing parking information
 */
function hpt_get_listing_parking($listing_id) {
    $parking = get_field('parking', $listing_id);
    
    if (!$parking) {
        $garage_spaces = hpt_get_listing_garage_spaces($listing_id);
        if ($garage_spaces) {
            $parking = $garage_spaces . ' ' . __('Car Garage', 'happy-place-theme');
        }
    }
    
    if (!$parking) {
        $parking = get_field('parking_spaces', $listing_id);
        if ($parking) {
            $parking .= ' ' . __('Spaces', 'happy-place-theme');
        }
    }
    
    return $parking ?: __('See listing details', 'happy-place-theme');
}

/**
 * Get formatted HOA fees
 */
function hpt_get_listing_hoa_fees_formatted($listing_id) {
    $fees = hpt_get_listing_hoa_fees($listing_id);
    
    if (!$fees || $fees == 0) {
        return __('None', 'happy-place-theme');
    }
    
    return '$' . number_format($fees);
}

/**
 * Get formatted property taxes
 */
function hpt_get_listing_property_taxes_formatted($listing_id) {
    $taxes = hpt_get_listing_property_taxes($listing_id);
    
    if (!$taxes || $taxes == 0) {
        return __('Contact agent for details', 'happy-place-theme');
    }
    
    return '$' . number_format($taxes);
}

/**
 * Get listing saves/favorites count
 */
function hpt_get_listing_saves_count($listing_id) {
    $count = get_post_meta($listing_id, 'saves_count', true);
    
    if (!$count) {
        // Count from user meta if no cached count
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = 'hph_favorite_listings' 
            AND meta_value LIKE %s
        ", '%' . $listing_id . '%'));
    }
    
    return intval($count);
}

/**
 * Get comprehensive listing details for overview component
 */
function hpt_get_listing_overview_details($listing_id) {
    return array(
        'description' => hpt_get_listing_description($listing_id),
        'highlights' => hpt_get_listing_highlights($listing_id),
        'features' => array(
            array('icon' => 'fas fa-home', 'label' => __('Property Style', 'happy-place-theme'), 'value' => hpt_get_listing_style($listing_id) ?: __('Traditional', 'happy-place-theme')),
            array('icon' => 'fas fa-expand-arrows-alt', 'label' => __('Lot Size', 'happy-place-theme'), 'value' => hpt_get_listing_lot_size_formatted($listing_id) ?: __('See details', 'happy-place-theme')),
            array('icon' => 'fas fa-car', 'label' => __('Parking', 'happy-place-theme'), 'value' => hpt_get_listing_parking($listing_id)),
            array('icon' => 'fas fa-fire', 'label' => __('Heating', 'happy-place-theme'), 'value' => hpt_get_listing_heating($listing_id)),
            array('icon' => 'fas fa-snowflake', 'label' => __('Cooling', 'happy-place-theme'), 'value' => hpt_get_listing_cooling($listing_id)),
            array('icon' => 'fas fa-building', 'label' => __('HOA Fee', 'happy-place-theme'), 'value' => hpt_get_listing_hoa_fees_formatted($listing_id))
        ),
        'property_style' => hpt_get_listing_style($listing_id),
        'lot_size' => hpt_get_listing_lot_size_formatted($listing_id),
        'parking' => hpt_get_listing_parking($listing_id),
        'heating' => hpt_get_listing_heating($listing_id),
        'cooling' => hpt_get_listing_cooling($listing_id),
        'hoa_fee' => hpt_get_listing_hoa_fees_formatted($listing_id),
        'tax_amount' => hpt_get_listing_property_taxes_formatted($listing_id),
        'tax_year' => date('Y') - 1,
        'listing_date' => hpt_get_listing_date($listing_id),
        'last_updated' => get_the_modified_time('c', $listing_id),
        'views' => hpt_get_listing_views($listing_id),
        'saves' => hpt_get_listing_saves_count($listing_id),
        'share_url' => get_permalink($listing_id),
        'listing_agent' => hpt_get_listing_overview_agent_data($listing_id)
    );
}

/**
 * Get agent data formatted for listing overview
 */
function hpt_get_listing_overview_agent_data($listing_id) {
    $agent_id = hpt_get_listing_agent($listing_id);
    
    if (!$agent_id || !function_exists('hpt_get_agent')) {
        return array();
    }
    
    $agent = hpt_get_agent($agent_id);
    
    if (!$agent) {
        return array();
    }
    
    return array(
        'name' => $agent['name'],
        'phone' => $agent['phone'],
        'email' => $agent['email'],
        'photo' => $agent['profile_photo']['url'] ?? '',
        'license' => $agent['license_number']
    );
}

/**
 * Note: hpt_get_agent_listings() function is defined in agent-bridge.php
 * to avoid duplication and provide more comprehensive functionality.
 */

// ====================================
// MAP & NEIGHBORHOOD DATA FUNCTIONS
// ====================================

/**
 * Get comprehensive map data for listing
 */
function hpt_get_listing_map_data($listing_id) {
    $coordinates = hpt_get_listing_coordinates($listing_id);
    $address = hpt_get_listing_address($listing_id);
    
    // If coordinates are missing but we have an address, try to geocode
    if ((!$coordinates['lat'] || !$coordinates['lng']) && $address) {
        $geocoded = hpt_geocode_address($address);
        if ($geocoded && $geocoded['lat'] && $geocoded['lng']) {
            $coordinates = $geocoded;
        }
    }
    
    // Use fallback coordinates if still empty (Rehoboth Beach, DE)
    $latitude = $coordinates['lat'] ?: 38.7217;
    $longitude = $coordinates['lng'] ?: -75.0761;
    
    return array(
        'latitude' => $latitude,
        'longitude' => $longitude,
        'address' => $address ?: '123 Main Street, Rehoboth Beach, DE 19971',
        'google_maps_api_key' => defined('HPH_GOOGLE_MAPS_KEY') ? HPH_GOOGLE_MAPS_KEY : get_option('hph_google_maps_api_key', ''),
        'walk_score' => hpt_get_listing_walk_score($listing_id),
        'transit_score' => hpt_get_listing_transit_score($listing_id),
        'bike_score' => hpt_get_listing_bike_score($listing_id),
        'neighborhood_description' => hpt_get_listing_neighborhood_description($listing_id),
        'crime_rating' => hpt_get_listing_crime_rating($listing_id),
        'noise_level' => hpt_get_listing_noise_level($listing_id),
        'nearby_places' => hpt_get_listing_nearby_places($listing_id),
        'schools' => hpt_get_listing_nearby_schools($listing_id),
        'commute_times' => hpt_get_listing_commute_times($listing_id),
        'demographics' => hpt_get_listing_demographics($listing_id),
        'property_boundaries' => hpt_get_listing_property_boundaries($listing_id)
    );
}

/**
 * Get walk score for listing
 */
function hpt_get_listing_walk_score($listing_id) {
    $score = get_field('walk_score', $listing_id);
    return $score ? intval($score) : 85; // Default high walk score for beach areas
}

/**
 * Get transit score for listing
 */
function hpt_get_listing_transit_score($listing_id) {
    $score = get_field('transit_score', $listing_id);
    return $score ? intval($score) : 45; // Default moderate transit score
}

/**
 * Get bike score for listing
 */
function hpt_get_listing_bike_score($listing_id) {
    $score = get_field('bike_score', $listing_id);
    return $score ? intval($score) : 72; // Default good bike score
}

/**
 * Get neighborhood description
 */
function hpt_get_listing_neighborhood_description($listing_id) {
    $description = get_field('neighborhood_description', $listing_id);
    
    if (!$description) {
        $neighborhood = hpt_get_listing_neighborhood($listing_id);
        if ($neighborhood) {
            $description = "Beautiful {$neighborhood} neighborhood with excellent amenities and convenient location.";
        } else {
            $description = "This charming property is located in a desirable neighborhood with easy access to local amenities, dining, and entertainment options.";
        }
    }
    
    return $description;
}

/**
 * Get crime rating for neighborhood
 */
function hpt_get_listing_crime_rating($listing_id) {
    $rating = get_field('crime_rating', $listing_id);
    
    if (!$rating) {
        // Default to "Low" for beach communities
        $rating = 'Low';
    }
    
    return $rating;
}

/**
 * Get noise level for neighborhood
 */
function hpt_get_listing_noise_level($listing_id) {
    $level = get_field('noise_level', $listing_id);
    
    if (!$level) {
        // Default to "Quiet" for residential areas
        $level = 'Quiet';
    }
    
    return $level;
}

/**
 * Get nearby places organized by category
 */
function hpt_get_listing_nearby_places($listing_id) {
    $places = get_field('nearby_places', $listing_id);
    
    if (empty($places)) {
        // Default nearby places for Rehoboth Beach area
        $places = array(
            array(
                'category' => 'Grocery',
                'places' => array(
                    array('name' => 'Whole Foods Market', 'distance' => '0.3 mi', 'time' => '5 min walk', 'rating' => 4.5),
                    array('name' => 'Trader Joe\'s', 'distance' => '0.8 mi', 'time' => '3 min drive', 'rating' => 4.6),
                )
            ),
            array(
                'category' => 'Restaurants',
                'places' => array(
                    array('name' => 'The Blue Hen', 'distance' => '0.2 mi', 'time' => '3 min walk', 'rating' => 4.7),
                    array('name' => 'Salt Air', 'distance' => '0.4 mi', 'time' => '7 min walk', 'rating' => 4.8),
                    array('name' => 'Henlopen City Oyster House', 'distance' => '0.5 mi', 'time' => '10 min walk', 'rating' => 4.6),
                )
            ),
            array(
                'category' => 'Shopping',
                'places' => array(
                    array('name' => 'Tanger Outlets', 'distance' => '1.2 mi', 'time' => '5 min drive', 'rating' => 4.3),
                    array('name' => 'Rehoboth Beach Boardwalk', 'distance' => '0.6 mi', 'time' => '12 min walk', 'rating' => 4.7),
                )
            ),
            array(
                'category' => 'Healthcare',
                'places' => array(
                    array('name' => 'Beebe Healthcare', 'distance' => '2.1 mi', 'time' => '8 min drive', 'rating' => 4.2),
                    array('name' => 'CVS Pharmacy', 'distance' => '0.4 mi', 'time' => '7 min walk', 'rating' => 3.8),
                )
            )
        );
    }
    
    return $places;
}

/**
 * Get nearby schools
 */
function hpt_get_listing_nearby_schools($listing_id) {
    $schools = get_field('nearby_schools', $listing_id);
    
    if (empty($schools)) {
        // Default schools for Rehoboth Beach area
        $schools = array(
            array('name' => 'Rehoboth Elementary', 'type' => 'Elementary', 'rating' => 9, 'distance' => '0.8 mi', 'grades' => 'K-5'),
            array('name' => 'Beacon Middle School', 'type' => 'Middle', 'rating' => 8, 'distance' => '1.2 mi', 'grades' => '6-8'),
            array('name' => 'Cape Henlopen High School', 'type' => 'High', 'rating' => 8, 'distance' => '2.5 mi', 'grades' => '9-12'),
        );
    }
    
    return $schools;
}

/**
 * Get commute times from property
 */
function hpt_get_listing_commute_times($listing_id) {
    $commutes = get_field('commute_times', $listing_id);
    
    if (empty($commutes)) {
        // Default commute times for Rehoboth Beach area
        $commutes = array(
            array('destination' => 'Downtown Rehoboth', 'drive_time' => '5 min', 'transit_time' => '15 min'),
            array('destination' => 'Dewey Beach', 'drive_time' => '8 min', 'transit_time' => '20 min'),
            array('destination' => 'Dover', 'drive_time' => '45 min', 'transit_time' => 'N/A'),
        );
    }
    
    return $commutes;
}

/**
 * Get neighborhood demographics
 */
function hpt_get_listing_demographics($listing_id) {
    $demographics = get_field('demographics', $listing_id);
    
    if (empty($demographics)) {
        $demographics = array(
            'median_age' => 42,
            'median_income' => '$75,000',
            'population' => '1,500'
        );
    }
    
    return $demographics;
}

/**
 * Get property boundary coordinates
 */
function hpt_get_listing_property_boundaries($listing_id) {
    $boundaries = get_field('property_boundaries', $listing_id);
    
    if (empty($boundaries)) {
        $boundaries = array(); // No boundaries by default
    }
    
    return $boundaries;
}

/**
 * Simple geocoding function to get coordinates from address
 * 
 * @param string $address The address to geocode
 * @return array|false Array with lat/lng or false on failure
 */
function hpt_geocode_address($address) {
    if (empty($address)) {
        return false;
    }
    
    // Check if we have Google Maps API key
    $api_key = defined('HPH_GOOGLE_MAPS_KEY') ? HPH_GOOGLE_MAPS_KEY : get_option('hph_google_maps_api_key', '');
    
    if (empty($api_key)) {
        return false; // No API key available
    }
    
    // Create cache key for this address
    $cache_key = 'hpt_geocode_' . md5($address);
    $cached_result = get_transient($cache_key);
    
    if ($cached_result !== false) {
        return $cached_result;
    }
    
    // Geocode the address using Google Maps API
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . $api_key;
    
    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (empty($data['results'][0]['geometry']['location'])) {
        return false;
    }
    
    $location = $data['results'][0]['geometry']['location'];
    $result = array(
        'lat' => floatval($location['lat']),
        'lng' => floatval($location['lng'])
    );
    
    // Cache the result for 24 hours
    set_transient($cache_key, $result, DAY_IN_SECONDS);
    
    return $result;
}

/**
 * Agent Bridge Functions
 * Functions for retrieving agent/user data
 */

/**
 * Get listing agent ID
 * 
 * @param int $listing_id Listing ID
 * @return int|null Agent user ID
 */
function hpt_get_listing_agent_id($listing_id) {
    $agent_id = null;
    
    // Try ACF field first
    if (function_exists('get_field')) {
        $agent_id = get_field('listing_agent', $listing_id);
    }
    
    // Try meta key fallback
    if (!$agent_id) {
        $agent_id = get_post_meta($listing_id, '_listing_agent', true);
    }
    
    return $agent_id ? intval($agent_id) : null;
}

/**
 * Get comprehensive agent data
 * 
 * @param int $agent_id User ID
 * @return array Agent data
 */
function hpt_get_agent_data($agent_id) {
    if (!$agent_id) {
        return array();
    }
    
    $user = get_userdata($agent_id);
    if (!$user) {
        return array();
    }
    
    return array(
        'agent_id' => $agent_id,
        'agent_name' => $user->display_name ?: $user->user_nicename,
        'agent_email' => $user->user_email,
        'agent_phone' => get_user_meta($agent_id, 'phone', true) ?: get_user_meta($agent_id, 'agent_phone', true),
        'agent_mobile' => get_user_meta($agent_id, 'mobile', true) ?: get_user_meta($agent_id, 'agent_mobile', true),
        'agent_photo' => get_avatar_url($agent_id, array('size' => 200)),
        'agent_bio' => get_user_meta($agent_id, 'description', true) ?: get_user_meta($agent_id, 'agent_bio', true),
        'agent_license' => get_user_meta($agent_id, 'license_number', true) ?: get_user_meta($agent_id, 'agent_license', true),
        'agent_title' => get_user_meta($agent_id, 'agent_title', true) ?: 'Real Estate Agent',
        'agent_specialties' => get_user_meta($agent_id, 'specialties', true) ?: array(),
        'agent_languages' => get_user_meta($agent_id, 'languages', true) ?: array('English'),
        'agent_rating' => floatval(get_user_meta($agent_id, 'rating', true) ?: 0),
        'agent_reviews_count' => intval(get_user_meta($agent_id, 'reviews_count', true) ?: 0),
        'agent_listings_count' => intval(get_user_meta($agent_id, 'listings_count', true) ?: 0),
        'agent_sold_count' => intval(get_user_meta($agent_id, 'sold_count', true) ?: 0),
        'agent_years_experience' => intval(get_user_meta($agent_id, 'years_experience', true) ?: 0),
        'agency_name' => get_user_meta($agent_id, 'agency_name', true) ?: '',
        'agency_logo' => get_user_meta($agent_id, 'agency_logo', true) ?: '',
        'agency_phone' => get_user_meta($agent_id, 'agency_phone', true) ?: ''
    );
}