<?php
/**
 * Listing Card - Map View
 * Compact card for map sidebar display
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Use the universal card system with compact layout for map view
hph_component('universal-card', [
    'post_id' => $listing_id,
    'layout' => 'compact',
    'show_favorite' => false,
    'show_compare' => false
]);
?>