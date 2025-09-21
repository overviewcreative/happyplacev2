<?php
/**
 * Universal Card Component
 * 
 * Generic card component that can adapt to any post type using bridge functions.
 * Provides a standardized interface for displaying any WordPress content as cards.
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Universal
 * @since 3.0.0
 */

// Get component args - compatible with both hph_component and get_template_part
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', array());

// Check if layout was explicitly passed (prevents legacy view_type from overriding)
$layout_explicitly_set = isset($component_args['layout']);

$args = wp_parse_args($component_args, array(
    // Data props
    'post_id' => get_the_ID(),
    'post_type' => get_post_type(),
    
    // View type (legacy support)
    'view_type' => 'grid', // grid, list, map, gallery
    
    // Display options
    'layout' => 'vertical', // vertical, horizontal, compact
    'variant' => 'default', // default, elevated, bordered, minimal
    'size' => 'md', // sm, md, lg
    'show_meta' => true,
    'show_actions' => false,
    'show_excerpt' => true,
    'show_status' => true,
    'compact' => false, // for map view

    // Meta display toggles (for listings)
    'show_days_on_market' => false,
    'show_last_updated' => false,

    // Behavior
    'clickable' => true,
    'hover_effects' => true,
    
    // HTML
    'class' => '',
    'attributes' => array(),

    // Change tracking data (for listings)
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

// Only apply view_type mapping if layout wasn't explicitly set
if (!$layout_explicitly_set && isset($view_type_map[$args['view_type']])) {
    $args['layout'] = $view_type_map[$args['view_type']];
}

// Handle compact flag for map view
if ($args['compact'] || $args['view_type'] === 'map') {
    $args['layout'] = 'compact';
    $args['size'] = 'sm';
}

// Validate post
$post_id = $args['post_id'];
$post_type = $args['post_type'];
if (!$post_id) {
    return;
}

$post = get_post($post_id);
if (!$post) {
    return;
}

// Note: Previously delegated agent cards to dedicated components,
// but now all card types use the universal adapter system for consistency

// For other post types, use the adapter system
// Get the post type adapter
$adapter_function = "hpt_get_card_data_{$post_type}";
if (!function_exists($adapter_function)) {
    // Fallback to generic post data adapter
    $adapter_function = 'hpt_get_card_data_generic';
}

// Get card data from adapter
// Pass change tracking data to adapter for listings
$adapter_args = $args;
if ($post_type === 'listing') {
    $adapter_args['change_data'] = [
        'changes' => $args['listing_changes'],
        'badges' => $args['listing_badges'],
        'has_recent_changes' => $args['has_recent_changes'],
        'is_new_listing' => $args['is_new_listing']
    ];
}

$card_data = call_user_func($adapter_function, $post_id, $adapter_args);
if (!$card_data) {
    return;
}

// Layout processing complete

// Build final card arguments by merging adapter data with component args
$final_card_args = array_merge($card_data, array(
    // Override with component args - ensure layout is properly passed
    'variant' => $args['variant'],
    'layout' => $args['layout'], // This should be 'horizontal' for list view
    'size' => $args['size'],
    'hover_effect' => $args['hover_effects'] ? 'lift' : 'none',

    // Add component-specific classes
    'class' => ($card_data['class'] ?? '') . ' hph-universal-card hph-' . $post_type . '-card ' . $args['class'],
    'attributes' => array_merge($card_data['attributes'] ?? array(), $args['attributes'], array(
        'data-post-id' => $post_id,
        'data-post-type' => $post_type
    ))
));

// Layout-specific modifications
if ($args['layout'] === 'compact') {
    // Simplify content for compact view
    if (isset($final_card_args['meta_items']) && count($final_card_args['meta_items']) > 2) {
        $final_card_args['meta_items'] = array_slice($final_card_args['meta_items'], 0, 2);
    }
    $final_card_args['description'] = wp_trim_words($final_card_args['description'] ?? '', 15, '...');
}

// Load the base card component with final arguments
hph_component('card', $final_card_args);
?>