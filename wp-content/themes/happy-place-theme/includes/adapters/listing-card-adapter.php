<?php
function get_listing_card_props($listing_id, $options = []) {
    error_log('ADAPTER DEBUG: get_listing_card_props called for listing ID: ' . $listing_id);
    
    // Test if bridge functions exist before calling
    $bridge_functions = [
        'hpt_get_listing_price',
        'hpt_get_listing_status', 
        'hpt_get_listing_bedrooms',
        'hpt_get_listing_bathrooms',
        'hpt_get_listing_square_feet',
        'hpt_get_listing_address',
        'hpt_get_listing_featured_image'
    ];
    
    foreach ($bridge_functions as $func) {
        if (!function_exists($func)) {
            error_log('ADAPTER DEBUG: Missing function: ' . $func);
        }
    }
    
    // Only use data available from bridge functions
    $price = function_exists('hpt_get_listing_price') ? hpt_get_listing_price($listing_id) : null;
    $status = function_exists('hpt_get_listing_status') ? hpt_get_listing_status($listing_id) : null;
    $bedrooms = function_exists('hpt_get_listing_bedrooms') ? hpt_get_listing_bedrooms($listing_id) : null;
    $bathrooms = function_exists('hpt_get_listing_bathrooms') ? hpt_get_listing_bathrooms($listing_id) : null;
    $sqft = function_exists('hpt_get_listing_square_feet') ? hpt_get_listing_square_feet($listing_id) : null;
    $address = function_exists('hpt_get_listing_address') ? hpt_get_listing_address($listing_id) : [];
    $featured_image_data = function_exists('hpt_get_listing_featured_image') ? hpt_get_listing_featured_image($listing_id) : null;
    
    // Extract URL from image data
    $featured_image_url = '';
    $featured_image_alt = '';
    if ($featured_image_data && is_array($featured_image_data)) {
        $featured_image_url = $featured_image_data['url'] ?? '';
        $featured_image_alt = $featured_image_data['alt'] ?? '';
    }
    
    error_log('ADAPTER DEBUG: Bridge data - Price: ' . ($price ?: 'null') . ', Status: ' . ($status ?: 'null') . ', Image: ' . ($featured_image_url ?: 'none'));
    
    $card_props = [
        'variant' => $options['variant'] ?? 'elevated',
        'layout' => $options['layout'] ?? 'vertical',
        'image' => [
            'src' => $featured_image_url,
            'alt' => $featured_image_alt ?: get_the_title($listing_id),
            'ratio' => 'landscape'
        ],
        'title' => [
            'text' => get_the_title($listing_id),
            'link' => get_permalink($listing_id)
        ],
        'subtitle' => $price ? (function_exists('hpt_get_listing_price_formatted') ? hpt_get_listing_price_formatted($listing_id) : '$' . number_format($price)) : '',
        'description' => $address['full'] ?? ($address['street'] . ', ' . $address['city'] . ', ' . $address['state']),
        'badges' => (function() use ($listing_id, $status) {
            $badges = [];

            // Get listing change badges (priority)
            if (function_exists('hpt_bridge_get_listing_badges')) {
                $change_badges = hpt_bridge_get_listing_badges($listing_id, 2);
                $badges = array_merge($badges, $change_badges);
            }

            // Add status badge only if we have room (max 2 badges total)
            if (count($badges) < 2 && $status) {
                $status_badge = [
                    'text' => function_exists('hpt_get_listing_status_label') ? hpt_get_listing_status_label($listing_id) : $status,
                    'variant' => 'primary'
                ];
                $badges[] = $status_badge;
            }

            return $badges;
        })(),
        'meta_items' => array_filter([
            $bedrooms ? ['icon' => 'bed', 'text' => $bedrooms . ' bed' . ($bedrooms != 1 ? 's' : '')] : null,
            $bathrooms ? ['icon' => 'bath', 'text' => $bathrooms . ' bath' . ($bathrooms != 1 ? 's' : '')] : null,
            $sqft ? ['icon' => 'ruler-combined', 'text' => number_format($sqft) . ' sqft'] : null
        ]),
        'price_per_sqft' => $sqft && $price ? '$' . number_format($price / $sqft) . ' per sqft' : null,
        'actions' => [
            ['text' => 'View Details', 'href' => get_permalink($listing_id), 'variant' => 'primary']
        ],
        'link_wrapper' => get_permalink($listing_id),
        'hover_effect' => 'lift'
    ];
    
    error_log('ADAPTER DEBUG: Final card props: ' . wp_json_encode($card_props));
    
    return $card_props;
}
