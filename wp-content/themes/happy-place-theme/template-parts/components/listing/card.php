<?php
/**
 * Listing Card Adapter Component
 * 
 * Adapter component that uses the adapter service to transform listing data
 * for the base card component. Follows proper separation of data and presentation.
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Listing
 * @since 3.0.0
 */

// Get component args (compatible with both hph_component and get_template_part)
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', array());
$args = wp_parse_args($component_args, array(
    // Data props
    'listing_id' => get_the_ID(),
    
    // View type (legacy support)
    'view_type' => 'grid', // grid, list, map, gallery
    
    // Display options
    'layout' => 'vertical', // vertical, horizontal, compact
    'variant' => 'default', // default, elevated, bordered, minimal
    'size' => 'md', // sm, md, lg
    'show_agent' => false,
    'show_favorite' => false,  // Disable favorite button
    'show_actions' => false,
    'show_stats' => true,
    'show_status' => true,
    'compact' => false, // for map view

    // Meta display toggles
    'show_days_on_market' => false,
    'show_last_updated' => false,

    // Behavior
    'clickable' => true,
    'hover_effects' => true,
    
    // HTML
    'class' => '',
    'attributes' => array(),

    // Change tracking data
    'listing_changes' => [],
    'listing_badges' => [],
    'has_recent_changes' => false,
    'is_new_listing' => false
));

// Map view type to layout (legacy support)
$view_type_map = array(
    'grid' => 'vertical',
    'list' => 'horizontal',
    'map' => 'compact',
    'gallery' => 'vertical'
);

if (isset($view_type_map[$args['view_type']])) {
    $args['layout'] = $view_type_map[$args['view_type']];
}

// Handle compact flag for map view
if ($args['compact'] || $args['view_type'] === 'map') {
    $args['layout'] = 'compact';
    $args['size'] = 'sm';
}

// Validate listing
$listing_id = $args['listing_id'];
if (!$listing_id) {
    return;
}

$post = get_post($listing_id);
if (!$post || $post->post_type !== 'listing') {
    return;
}

// Use adapter service to transform data
// Temporarily disable adapter service to avoid errors - use bridge functions directly
$adapter_service = null; // hpt_adapter();
if (!$adapter_service) {
    // Fallback to direct bridge functions if adapter service unavailable
    $listing = hpt_get_listing($listing_id);
    if (!$listing) {
        return;
    }
} else {
    // Transform data using adapter service
    $card_props = $adapter_service->transform('listing_card', $listing_id, array(
        'variant' => $args['variant'],
        'layout' => $args['layout'],
        'size' => $args['size']
    ));
    
    if (!$card_props) {
        return;
    }
}

// Use adapter service data if available, otherwise fallback to manual building
if ($adapter_service && isset($card_props)) {
    // Adapter service provides properly formatted card arguments
    $final_card_args = $card_props;
    
    // Apply display options from component args
    if ($args['show_agent'] && !isset($final_card_args['show_agent'])) {
        $final_card_args['show_agent'] = $args['show_agent'];
    }
    
    // Override any layout/variant options
    $final_card_args['variant'] = $args['variant'];
    $final_card_args['layout'] = $args['layout'];
    $final_card_args['size'] = $args['size'];
    $final_card_args['hover_effects'] = $args['hover_effects'];
    
    // Add component-specific classes
    $final_card_args['class'] = ($final_card_args['class'] ?? '') . ' ' . $args['class'];
    $final_card_args['attributes'] = array_merge($final_card_args['attributes'] ?? array(), $args['attributes']);
    
} else {
    // Fallback: Build card arguments manually using bridge functions
    // Build image data
    $image_data = null;
    if ($listing['featured_image']) {
        $image_data = array(
            'src' => $listing['featured_image']['url'] ?? '',  // Use 'src' instead of 'url'
            'alt' => $listing['featured_image']['alt'] ?? $listing['title'],
            'ratio' => 'landscape'
        );
    } elseif (!empty($listing['gallery'])) {
        $first_image = $listing['gallery'][0];
        $image_data = array(
            'src' => $first_image['url'] ?? '',  // Use 'src' instead of 'url'
            'alt' => $first_image['alt'] ?? $listing['title'],
            'ratio' => 'landscape'
        );
    }

    // Build location string - get address properly
    $address = hpt_get_listing_address($listing_id, 'array');
    $location_parts = array_filter(array(
        $address['city'] ?? '',
        $address['state'] ?? ''
    ));
    $location = implode(', ', $location_parts);

    // Get price data
    $price_raw = hpt_get_listing_price($listing_id);
    $price_formatted = hpt_get_listing_price_formatted($listing_id);
    
    // Get property details
    $bedrooms = hpt_get_listing_bedrooms($listing_id);
    $bathrooms = hpt_get_listing_bathrooms($listing_id);
    $square_feet = hpt_get_listing_square_feet($listing_id);
    $listing_status = hpt_get_listing_status($listing_id);

    // Build property details
    $property_details = array();
    if ($bedrooms) {
        $property_details[] = $bedrooms . ' bed' . ($bedrooms > 1 ? 's' : '');
    }
    if ($bathrooms) {
        $property_details[] = $bathrooms . ' bath' . ($bathrooms > 1 ? 's' : '');
    }
    if ($square_feet) {
        $property_details[] = number_format($square_feet) . ' sq ft';
    }

    // Build badges - ENHANCED: Use new comprehensive badge system
    $badges = array();

    // Get comprehensive badges from bridge system
    if (function_exists('hpt_bridge_get_comprehensive_badges')) {
        $comprehensive_badges = hpt_bridge_get_comprehensive_badges($listing_id, 3);

        foreach ($comprehensive_badges as $badge) {
            $badges[] = array(
                'text' => $badge['text'],
                'variant' => $badge['variant'],
                'position' => 'top-left',
                'type' => $badge['type'] ?? 'default',
                'priority' => $badge['priority'] ?? 5,
                'data' => $badge['data'] ?? null
            );
        }
    }

    // Add featured badge if we have room and not already included
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
            'position' => 'top-right',
            'icon' => 'star',
            'type' => 'featured',
            'priority' => 3
        );
    }

    // Sort by priority and limit to 3
    usort($badges, function($a, $b) {
        return $b['priority'] <=> $a['priority'];
    });
    $badges = array_slice($badges, 0, 3);

    // Build actions
    $actions = array();
    if ($args['show_favorite']) {
        $is_favorite = function_exists('hpt_is_listing_favorite') ? 
                       hpt_is_listing_favorite($listing_id, get_current_user_id()) : false;
        
        $actions[] = array(
            'type' => 'button',
            'icon' => $is_favorite ? 'heart-solid' : 'heart',
            'variant' => $is_favorite ? 'danger' : 'ghost',
            'size' => 'sm',
            'class' => 'hph-favorite-btn',
            'attributes' => array(
                'data-action' => 'toggle-favorite',
                'data-listing' => $listing_id,
                'aria-label' => $is_favorite ? 'Remove from favorites' : 'Add to favorites'
            )
        );
    }

    if ($args['show_actions']) {
        $actions[] = array(
            'type' => 'button',
            'icon' => 'share',
            'variant' => 'ghost',
            'size' => 'sm',
            'class' => 'hph-share-btn',
            'attributes' => array(
                'data-action' => 'share-listing',
                'data-listing' => $listing_id,
                'aria-label' => 'Share listing'
            )
        );
    }

    // Build agent section
    $agent_content = '';
    if ($args['show_agent'] && $listing['agent']) {
        $agent = $listing['agent'];
        $agent_content = sprintf(
            '<div class="hph-listing-agent hph-flex hph-items-center hph-gap-sm hph-text-xs hph-text-gray-600">
                %s
                <span>%s</span>
            </div>',
            $agent['avatar'] ? sprintf('<img src="%s" alt="%s" class="hph-w-6 hph-h-6 hph-rounded-full">', 
                                      esc_url($agent['avatar']), 
                                      esc_attr($agent['name'])) : '',
            esc_html($agent['name'])
        );
    }

    // Build stats content with proper structure
    $stats_content = '';
    if ($args['show_stats'] && !empty($property_details)) {
        $stats_content = sprintf(
            '<div class="hph-listing-stats">
                %s
            </div>',
            implode(' • ', array_map('esc_html', $property_details))
        );
    }

    // Build price per sqft if available
    $price_per_sqft = '';
    if ($listing['price']['raw'] && $listing['square_feet']) {
        $per_sqft = round($listing['price']['raw'] / $listing['square_feet']);
        $price_per_sqft = sprintf(
            '<div class="hph-listing-price-per-sqft">$%s per sqft</div>',
            number_format($per_sqft)
        );
    }

    // Prepare base card arguments (fallback)
    $final_card_args = array(
        // Content - properly structured for listings
        'title' => array(
            'text' => $address['street'] ?? $listing['title'],
            'tag' => 'h3'
        ),
        'subtitle' => $price_formatted,
        'description' => $location,
        'meta_items' => array_filter([
            $bedrooms ? ['icon' => 'bed', 'text' => $bedrooms . ' bed' . ($bedrooms != 1 ? 's' : '')] : null,
            $bathrooms ? ['icon' => 'bath', 'text' => $bathrooms . ' bath' . ($bathrooms != 1 ? 's' : '')] : null,
            $square_feet ? ['icon' => 'ruler-combined', 'text' => number_format($square_feet) . ' sqft'] : null
        ]),
        'price_per_sqft' => ($price_raw && $square_feet) ? 
            '$' . number_format($price_raw / $square_feet) . ' per sqft' : null,
        
        // Media
        'image' => $image_data,
        'badges' => $badges,
        
        // Behavior
        'link_wrapper' => $args['clickable'] ? $listing['url'] : '',
        'hover_effect' => $args['hover_effects'] ? 'lift' : 'none',
        
        // Appearance
        'variant' => $args['variant'],
        'layout' => $args['layout'],
        'size' => $args['size'],
        
        // Actions
        'actions' => $actions,
        
        // HTML
        'id' => 'listing-card-' . $listing_id,
        'class' => 'hph-listing-card ' . $args['class'],
        'attributes' => array_merge(array(
            'data-listing-id' => $listing_id,
            'data-listing-status' => $listing_status,
            'data-listing-price' => $price_raw ?? 0
        ), $args['attributes'])
    );

    // Add layout-specific modifications
    if ($args['layout'] === 'compact') {
        $final_card_args['content'] = $stats_content; // Remove agent in compact view
        $final_card_args['footer'] = ''; // Remove footer in compact view
    }
}

// Load the base card component with final arguments
hph_component('card', $final_card_args);
?>
