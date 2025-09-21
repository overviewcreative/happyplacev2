<?php
/**
 * Enhanced Listing Card - Grid View
 * Uses New Component Architecture
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();
$view_type = $args['view_type'] ?? 'grid';
$show_selection = $args['show_selection'] ?? false;
$show_favorites = $args['show_favorites'] ?? false;
$show_compare = $args['show_compare'] ?? false;

// Use the universal card system
hph_component('universal-card', [
    'post_id' => $listing_id,
    'layout' => $view_type === 'list' ? 'horizontal' : 'vertical',
    'show_selection' => $show_selection,
    'show_favorite' => $show_favorites,
    'show_compare' => $show_compare
]);
?>