<?php
/**
 * Listing Card - Map View
 * Location: /template-parts/components/listing-card-map.php
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

$listing_id = $args['listing_id'] ?? get_the_ID();
if (!$listing_id) return;

// Get listing data with coordinates for map
$price = function_exists('hpt_get_listing_price_formatted') ? 
    hpt_get_listing_price_formatted($listing_id) : '';
$address = function_exists('hpt_get_listing_street_address') ? 
    hpt_get_listing_street_address($listing_id) : get_the_title($listing_id);
$beds = function_exists('hpt_get_listing_bedrooms') ? 
    hpt_get_listing_bedrooms($listing_id) : 0;
$baths = function_exists('hpt_get_listing_bathrooms_formatted') ? 
    hpt_get_listing_bathrooms_formatted($listing_id) : 0;
$sqft = function_exists('hpt_get_listing_square_feet_formatted') ? 
    hpt_get_listing_square_feet_formatted($listing_id) : '';
$coordinates = function_exists('hpt_get_listing_coordinates') ? 
    hpt_get_listing_coordinates($listing_id) : array();

// Get thumbnail
$image_url = get_the_post_thumbnail_url($listing_id, 'medium');
if (!$image_url) {
    $image_url = HPH_ASSETS_URI . '/images/placeholder-property.jpg';
}
?>

<article class="hph-listing-card hph-listing-card--map" 
         data-listing-id="<?php echo esc_attr($listing_id); ?>"
         <?php if (!empty($coordinates)): ?>
         data-lat="<?php echo esc_attr($coordinates['lat']); ?>"
         data-lng="<?php echo esc_attr($coordinates['lng']); ?>"
         <?php endif; ?>>
    
    <div class="hph-card-map__image">
        <img src="<?php echo esc_url($image_url); ?>" 
             alt="<?php echo esc_attr($address); ?>"
             loading="lazy">
    </div>
    
    <div class="hph-card-map__content">
        <div class="hph-card-map__price">
            <?php echo esc_html($price); ?>
        </div>
        
        <h4 class="hph-card-map__address">
            <a href="<?php echo get_permalink($listing_id); ?>">
                <?php echo esc_html($address); ?>
            </a>
        </h4>
        
        <div class="hph-card-map__details">
            <span><?php echo esc_html($beds); ?> Beds</span>
            <span><?php echo esc_html($baths); ?> Baths</span>
            <?php if ($sqft): ?>
            <span><?php echo esc_html($sqft); ?> Sq Ft</span>
            <?php endif; ?>
        </div>
        
        <a href="<?php echo get_permalink($listing_id); ?>" 
           class="hph-card-map__link">
            View Details <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    
</article>
