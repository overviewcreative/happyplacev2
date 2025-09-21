<?php
/**
 * Listing Card - Grid View - UPDATED: Uses Enhanced Bridge System
 * File: template-parts/listing-card.php
 *
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Use the universal card system with enhanced badges
hph_component('universal-card', [
    'post_id' => $listing_id,
    'layout' => 'vertical',
    'use_enhanced_badges' => true
]);

