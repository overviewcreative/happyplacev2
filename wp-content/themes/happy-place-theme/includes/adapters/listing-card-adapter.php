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
    $featured_image = function_exists('hpt_get_listing_featured_image') ? hpt_get_listing_featured_image($listing_id) : '';
    
    error_log('ADAPTER DEBUG: Bridge data - Price: ' . ($price ?: 'null') . ', Status: ' . ($status ?: 'null') . ', Image: ' . ($featured_image ?: 'none'));
    
    $card_props = [
        'variant' => $options['variant'] ?? 'elevated',
        'layout' => $options['layout'] ?? 'vertical',
        'image' => [
            'src' => $featured_image,
            'alt' => get_the_title($listing_id),
            'ratio' => 'landscape'
        ],
        'title' => [
            'text' => get_the_title($listing_id),
            'link' => get_permalink($listing_id)
        ],
        'subtitle' => $address['city'] ?? '',
        'badges' => array_filter([
            $status ? ['text' => function_exists('hpt_get_listing_status_label') ? hpt_get_listing_status_label($listing_id) : $status, 'variant' => 'primary'] : null,
            $price ? ['text' => function_exists('hpt_get_listing_price_formatted') ? hpt_get_listing_price_formatted($listing_id) : '$' . number_format($price), 'variant' => 'default'] : null
        ]),
        'meta_items' => array_filter([
            $bedrooms ? ['icon' => 'bed', 'text' => $bedrooms . ' bed' . ($bedrooms != 1 ? 's' : '')] : null,
            $bathrooms ? ['icon' => 'bath', 'text' => $bathrooms . ' bath' . ($bathrooms != 1 ? 's' : '')] : null,
            $sqft ? ['icon' => 'ruler', 'text' => number_format($sqft) . ' sqft'] : null
        ]),
        'actions' => [
            ['text' => 'View Details', 'href' => get_permalink($listing_id), 'variant' => 'primary']
        ],
        'link_wrapper' => get_permalink($listing_id),
        'hover_effect' => 'lift'
    ];
    
    error_log('ADAPTER DEBUG: Final card props: ' . wp_json_encode($card_props));
    
    return $card_props;
}