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
    
    // Display options
    'layout' => 'vertical', // vertical, horizontal, compact
    'variant' => 'default', // default, elevated, bordered, minimal
    'size' => 'md', // sm, md, lg
    'show_agent' => false,
    'show_favorite' => true,
    'show_actions' => false,
    'show_stats' => true,
    'show_status' => true,
    
    // Behavior
    'clickable' => true,
    'hover_effects' => true,
    
    // HTML
    'class' => '',
    'attributes' => array()
));

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
$adapter_service = hpt_adapter();
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
            'url' => $listing['featured_image']['url'] ?? '',
            'alt' => $listing['featured_image']['alt'] ?? $listing['title'],
            'width' => $listing['featured_image']['width'] ?? null,
            'height' => $listing['featured_image']['height'] ?? null
        );
    } elseif (!empty($listing['gallery'])) {
        $first_image = $listing['gallery'][0];
        $image_data = array(
            'url' => $first_image['url'] ?? '',
            'alt' => $first_image['alt'] ?? $listing['title'],
            'width' => $first_image['width'] ?? null,
            'height' => $first_image['height'] ?? null
        );
    }

    // Build location string
    $location_parts = array_filter(array(
        $listing['address']['city'] ?? '',
        $listing['address']['state'] ?? ''
    ));
    $location = implode(', ', $location_parts);

    // Build property details
    $property_details = array();
    if ($listing['bedrooms']) {
        $property_details[] = $listing['bedrooms'] . ' bed' . ($listing['bedrooms'] > 1 ? 's' : '');
    }
    if ($listing['bathrooms']) {
        $property_details[] = $listing['bathrooms'] . ' bath' . ($listing['bathrooms'] > 1 ? 's' : '');
    }
    if ($listing['square_feet']) {
        $property_details[] = number_format($listing['square_feet']) . ' sq ft';
    }

    // Build badges
    $badges = array();
    if ($args['show_status'] && $listing['listing_status']) {
        $badges[] = array(
            'text' => $listing['listing_status'],
            'variant' => strtolower($listing['listing_status']) === 'active' ? 'success' : 'default',
            'position' => 'top-left'
        );
    }
    if ($listing['is_featured']) {
        $badges[] = array(
            'text' => 'Featured',
            'variant' => 'primary',
            'position' => 'top-right',
            'icon' => 'star'
        );
    }

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

    // Build stats content
    $stats_content = '';
    if ($args['show_stats'] && !empty($property_details)) {
        $stats_content = sprintf(
            '<div class="hph-listing-stats hph-text-sm hph-text-gray-600 hph-flex hph-flex-wrap hph-gap-x-md hph-gap-y-xs">
                %s
            </div>',
            implode(' • ', array_map('esc_html', $property_details))
        );
    }

    // Prepare base card arguments (fallback)
    $final_card_args = array(
        // Content
        'title' => $listing['address']['street'] ?? $listing['title'],
        'subtitle' => $location,
        'content' => $stats_content . $agent_content,
        'footer' => $args['show_stats'] && $listing['listing_date'] ? 
                    '<div class="hph-text-xs hph-text-gray-500">Listed ' . date('M j, Y', strtotime($listing['listing_date'])) . '</div>' : '',
        
        // Media
        'image' => $image_data,
        'badges' => $badges,
        
        // Behavior
        'href' => $args['clickable'] ? $listing['url'] : '',
        'target' => '_self',
        
        // Appearance
        'variant' => $args['variant'],
        'layout' => $args['layout'],
        'size' => $args['size'],
        'hover_effects' => $args['hover_effects'],
        
        // Actions
        'actions' => $actions,
        
        // Price as overlay or header
        'header' => array(
            'content' => sprintf(
                '<div class="hph-listing-price hph-text-lg hph-font-bold hph-text-primary">%s</div>',
                esc_html($listing['price']['formatted'] ?? 'Contact for Price')
            ),
            'position' => $args['layout'] === 'horizontal' ? 'left' : 'top'
        ),
        
        // HTML
        'id' => 'listing-card-' . $listing_id,
        'class' => 'hph-listing-card ' . $args['class'],
        'attributes' => array_merge(array(
            'data-listing-id' => $listing_id,
            'data-listing-status' => $listing['listing_status'],
            'data-listing-price' => $listing['price']['raw'] ?? 0
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
