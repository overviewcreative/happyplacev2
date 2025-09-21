<?php
/**
 * Listing Card - List View - Uses New Component Architecture
 * File: template-parts/listing-card-list.php
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Use the universal card system with horizontal layout for list view
hph_component('universal-card', [
    'post_id' => $listing_id,
    'layout' => 'horizontal',
    'show_favorite' => false,
    'show_compare' => false
]);
