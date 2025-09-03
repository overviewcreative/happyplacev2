<?php
/**
 * Listings Grid Adapter Component
 * 
 * Adapter component that uses the adapter service for batch transforming
 * listing data and renders individual listing cards efficiently
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Listing
 * @since 3.0.0
 */

// Get component args (compatible with both hph_component and get_template_part)
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', array());
$args = wp_parse_args($component_args, array(
    'listings' => array(),
    'posts_per_page' => 12,
    'columns' => array(
        'xl' => 4,
        'lg' => 3,
        'md' => 2,
        'sm' => 1
    ),
    'gap' => 'lg',
    'card_variant' => 'default',
    'card_size' => 'md',
    'show_favorite' => true,
    'empty_message' => 'No listings found',
    'class' => ''
));

// Get listings
$listing_ids = array();
if (!empty($args['listings'])) {
    foreach ($args['listings'] as $listing) {
        if (is_object($listing) && isset($listing->ID)) {
            $listing_ids[] = $listing->ID;
        } elseif (is_numeric($listing)) {
            $listing_ids[] = (int) $listing;
        }
    }
} else {
    // Default query
    $query = new WP_Query(array(
        'post_type' => 'listing',
        'post_status' => 'publish',
        'posts_per_page' => $args['posts_per_page']
    ));
    $listing_ids = wp_list_pluck($query->posts, 'ID');
}

// Use adapter service for batch transformation
$adapter_service = hpt_adapter();
$use_adapter_batch = $adapter_service && method_exists($adapter_service, 'transform_batch');

// Build grid items
$grid_items = array();
if ($use_adapter_batch && !empty($listing_ids)) {
    // Use adapter service for batch processing (more efficient)
    $batch_card_props = $adapter_service->transform_batch('listing_card', $listing_ids, array(
        'variant' => $args['card_variant'],
        'size' => $args['card_size']
    ));
    
    foreach ($batch_card_props as $index => $card_props) {
        if (!$card_props) continue;
        
        $listing_id = $listing_ids[$index];
        
        // Override display options from grid args
        $card_props['show_favorite'] = $args['show_favorite'];
        $card_props['variant'] = $args['card_variant'];
        $card_props['size'] = $args['card_size'];
        
        ob_start();
        hph_component('card', $card_props);
        $card_content = ob_get_clean();
        
        $grid_items[] = array(
            'content' => $card_content,
            'class' => 'hph-listing-grid-item'
        );
    }
} else {
    // Fallback: Individual card component calls
    foreach ($listing_ids as $listing_id) {
        $listing_post = get_post($listing_id);
        if (!$listing_post || $listing_post->post_type !== 'listing') {
            continue;
        }
        
        ob_start();
        hph_component('listing/card', array(
            'listing_id' => $listing_id,
            'variant' => $args['card_variant'],
            'size' => $args['card_size'],
            'show_favorite' => $args['show_favorite']
        ));
        $card_content = ob_get_clean();
        
        $grid_items[] = array(
            'content' => $card_content,
            'class' => 'hph-listing-grid-item'
        );
    }
}

// Empty state
$empty_content = '';
if (empty($grid_items)) {
    $empty_content = sprintf(
        '<div class="hph-empty-state hph-text-center hph-py-xl">
            <div class="hph-text-gray-400 hph-text-6xl hph-mb-lg">
                <i class="fas fa-home"></i>
            </div>
            <h3 class="hph-text-xl hph-font-semibold hph-mb-sm">%s</h3>
            <p class="hph-text-gray-600">Try adjusting your search criteria.</p>
        </div>',
        esc_html($args['empty_message'])
    );
}

// Grid arguments
$grid_args = array(
    'items' => $grid_items,
    'columns' => $args['columns'],
    'gap' => $args['gap'],
    'empty_content' => $empty_content,
    'class' => 'hph-listings-grid ' . $args['class']
);

// Load grid component
hph_component('grid', $grid_args);
?>
