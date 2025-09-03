<?php
/**
 * Single Listing Template - Complete Rewrite
 * 
 * Modern, responsive single listing page with comprehensive null handling
 * Uses modular template parts for maintainability
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) : 
    while (have_posts()) : the_post();
        
        $listing_id = get_the_ID();
        
        // Comprehensive data validation and defaults
        $listing_data = hph_get_safe_listing_data($listing_id);
        
        // Check if listing exists and is valid
        if (!$listing_data || get_post_status($listing_id) !== 'publish') {
            get_template_part('template-parts/listing/listing-not-found');
            break;
        }
        
        // Pass data to template parts
        $template_args = [
            'listing_id' => $listing_id,
            'listing_data' => $listing_data
        ];
        ?>
        
        <main id="primary" class="hph-single-listing">
            
            <!-- Hero Section with Gallery -->
            <?php get_template_part('template-parts/listing/hero', null, $template_args); ?>
            
            <!-- Main Content Area -->
            <div class="hph-listing-container">
                <div class="hph-listing-layout">
                    
                    <!-- Left Column: Main Content -->
                    <div class="hph-listing-main">
                        
                        <!-- Property Description & Details -->
                        <?php get_template_part('template-parts/listing/main-body', null, $template_args); ?>
                        
                        <!-- Photo Gallery & Virtual Tour -->
                        <?php if (hph_has_media($listing_data)) : ?>
                            <?php get_template_part('template-parts/listing/gallery-tour-section', null, $template_args); ?>
                        <?php endif; ?>
                        
                        <!-- Map Section -->
                        <?php if (hph_has_location($listing_data)) : ?>
                            <?php get_template_part('template-parts/listing/map-section', null, $template_args); ?>
                        <?php endif; ?>
                        
                        <!-- Neighborhood Information -->
                        <?php get_template_part('template-parts/listing/neighborhood-section', null, $template_args); ?>
                        
                        <!-- School Information -->
                        <?php get_template_part('template-parts/listing/schools-section', null, $template_args); ?>
                        
                        <!-- Similar Listings -->
                        <?php get_template_part('template-parts/listing/similar-listings', null, $template_args); ?>
                        
                    </div>
                    
                    <!-- Right Column: Sidebar -->
                    <div class="hph-listing-sidebar-wrapper">
                        <?php get_template_part('template-parts/listing/sidebar-agent', null, $template_args); ?>
                    </div>
                    
                </div>
            </div>
            
        </main>
        
        <?php
        
    endwhile;
endif;

get_footer();

/**
 * Get safe listing data with comprehensive null handling
 */
function hph_get_safe_listing_data($listing_id) {
    if (!$listing_id || !get_post($listing_id)) {
        return false;
    }
    
    // Default structure for all possible fields
    $defaults = [
        // Basic Info
        'listing_price' => 0,
        'listing_status' => 'active',
        'mls_number' => '',
        'property_type' => '',
        'property_subtype' => '',
        
        // Property Details
        'bedrooms' => 0,
        'bathrooms_full' => 0,
        'bathrooms_half' => 0,
        'square_feet' => 0,
        'lot_size_acres' => 0,
        'lot_size_sqft' => 0,
        'garage_spaces' => 0,
        'parking_spaces' => 0,
        'year_built' => '',
        
        // Address Components
        'street_number' => '',
        'street_name' => '',
        'street_type' => '',
        'unit_number' => '',
        'city' => '',
        'state' => '',
        'zip_code' => '',
        'county' => '',
        'subdivision' => '',
        
        // Financial Info
        'annual_taxes' => 0,
        'tax_year' => '',
        'hoa_fee' => 0,
        'hoa_frequency' => 'monthly',
        'price_per_sqft' => 0,
        
        // Description & Marketing
        'property_description' => '',
        'public_remarks' => '',
        'private_remarks' => '',
        'showing_instructions' => '',
        
        // Media
        'primary_photo' => null,
        'photo_gallery' => [],
        'virtual_tour_url' => '',
        'video_url' => '',
        'floor_plans' => [],
        'property_documents' => [],
        
        // Features
        'interior_features' => [],
        'exterior_features' => [],
        'property_features' => [],
        'appliances' => [],
        'heating_cooling' => [],
        'flooring' => [],
        
        // Location
        'latitude' => null,
        'longitude' => null,
        'directions' => '',
        
        // Listing Details
        'list_date' => '',
        'status_change_date' => '',
        'days_on_market' => 0,
        'listing_agent' => null,
        'listing_office' => '',
        'co_listing_agent' => null,
        
        // Utilities & Systems
        'utilities' => [],
        'construction_materials' => [],
        'roof_type' => '',
        'foundation_type' => '',
        'water_source' => '',
        'sewer_type' => '',
        
        // School Information
        'school_district' => '',
        'elementary_school' => '',
        'middle_school' => '',
        'high_school' => '',
        
        // Additional
        'zoning' => '',
        'flood_zone' => '',
        'waterfront' => false,
        'pool' => false,
        'fireplace' => false,
        'basement' => false,
        'attic' => false
    ];
    
    // Get field values with null checking
    $data = [];
    foreach ($defaults as $field => $default) {
        $value = get_field($field, $listing_id);
        
        // Handle different data types appropriately
        if ($value === null || $value === '' || $value === false) {
            $data[$field] = $default;
        } else {
            // Type-specific validation
            switch (gettype($default)) {
                case 'integer':
                    $data[$field] = is_numeric($value) ? (int) $value : $default;
                    break;
                case 'double':
                    $data[$field] = is_numeric($value) ? (float) $value : $default;
                    break;
                case 'array':
                    $data[$field] = is_array($value) ? $value : $default;
                    break;
                case 'boolean':
                    $data[$field] = (bool) $value;
                    break;
                default:
                    $data[$field] = sanitize_text_field($value);
            }
        }
    }
    
    // Calculate derived fields
    $data['total_bathrooms'] = $data['bathrooms_full'] + ($data['bathrooms_half'] * 0.5);
    $data['full_address'] = hph_build_address($data);
    $data['street_address'] = trim($data['street_number'] . ' ' . $data['street_name'] . ' ' . $data['street_type']);
    $data['city_state_zip'] = trim($data['city'] . ', ' . $data['state'] . ' ' . $data['zip_code']);
    
    // Calculate price per square foot
    if ($data['listing_price'] > 0 && $data['square_feet'] > 0) {
        $data['price_per_sqft'] = round($data['listing_price'] / $data['square_feet']);
    }
    
    // Ensure media arrays are not empty
    if (empty($data['photo_gallery']) && has_post_thumbnail($listing_id)) {
        $data['photo_gallery'] = [
            [
                'url' => get_the_post_thumbnail_url($listing_id, 'full'),
                'alt' => get_the_title($listing_id),
                'caption' => ''
            ]
        ];
    }
    
    return $data;
}

/**
 * Build full address from components
 */
function hph_build_address($data) {
    $parts = [];
    
    // Street address
    $street_parts = array_filter([
        $data['street_number'],
        $data['street_name'],
        $data['street_type']
    ]);
    
    if (!empty($street_parts)) {
        $street = implode(' ', $street_parts);
        if (!empty($data['unit_number'])) {
            $street .= ', Unit ' . $data['unit_number'];
        }
        $parts[] = $street;
    }
    
    // City, State ZIP
    $location_parts = array_filter([
        $data['city'],
        $data['state'],
        $data['zip_code']
    ]);
    
    if (!empty($location_parts)) {
        $parts[] = implode(', ', $location_parts);
    }
    
    return implode(', ', $parts);
}

/**
 * Check if listing has media content
 */
function hph_has_media($listing_data) {
    return !empty($listing_data['photo_gallery']) || 
           !empty($listing_data['virtual_tour_url']) || 
           !empty($listing_data['video_url']) ||
           !empty($listing_data['primary_photo']);
}

/**
 * Check if listing has location data
 */
function hph_has_location($listing_data) {
    return !empty($listing_data['latitude']) && !empty($listing_data['longitude']);
}

/**
 * Get formatted price
 */
function hph_format_price($price, $show_currency = true) {
    if (!is_numeric($price) || $price <= 0) {
        return 'Price Available Upon Request';
    }
    
    $formatted = number_format($price);
    return $show_currency ? '$' . $formatted : $formatted;
}

/**
 * Get formatted square footage
 */
function hph_format_sqft($sqft) {
    if (!is_numeric($sqft) || $sqft <= 0) {
        return '';
    }
    
    return number_format($sqft) . ' sq ft';
}

/**
 * Get formatted lot size
 */
function hph_format_lot_size($acres = 0, $sqft = 0) {
    if ($acres > 0) {
        return number_format($acres, 2) . ' acres';
    } elseif ($sqft > 0) {
        return number_format($sqft) . ' sq ft';
    }
    
    return '';
}

/**
 * Get bathroom count display
 */
function hph_format_bathrooms($full, $half) {
    $total = $full + ($half * 0.5);
    
    if ($total <= 0) {
        return '';
    }
    
    if ($half > 0) {
        return $full . '.' . $half . ' baths';
    }
    
    return $total . ' bath' . ($total != 1 ? 's' : '');
}

/**
 * Get listing status display
 */
function hph_get_status_display($status) {
    $statuses = [
        'active' => 'Active',
        'pending' => 'Sale Pending',
        'sold' => 'Sold',
        'coming_soon' => 'Coming Soon',
        'withdrawn' => 'Withdrawn',
        'expired' => 'Expired',
        'off_market' => 'Off Market'
    ];
    
    return $statuses[$status] ?? 'Available';
}

/**
 * Check if field has content
 */
function hph_has_content($value) {
    if (is_array($value)) {
        return !empty($value);
    }
    
    return !empty($value) && $value !== '0' && $value !== 0;
}
