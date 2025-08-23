<?php
/**
 * Listing Card - Gallery View
 * Location: /template-parts/components/listing-card-gallery.php
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

// Gallery view specific configuration
$config = wp_parse_args($args ?? array(), array(
    'image_size' => 'large',
    'show_overlay_details' => true,
));

// Get listing data
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
$gallery = function_exists('hpt_get_listing_gallery') ? 
    hpt_get_listing_gallery($listing_id) : array();

// Get featured image
$image_url = get_the_post_thumbnail_url($listing_id, $config['image_size']);
if (!$image_url && !empty($gallery)) {
    $first_image = reset($gallery);
    $image_url = is_array($first_image) ? $first_image['url'] : $first_image;
}
if (!$image_url) {
    $image_url = HPH_ASSETS_URI . '/images/placeholder-property.jpg';
}
?>

<article class="hph-listing-card hph-listing-card--gallery" 
         data-listing-id="<?php echo esc_attr($listing_id); ?>">
    
    <a href="<?php echo get_permalink($listing_id); ?>" class="hph-card-gallery__link">
        
        <div class="hph-card-gallery__image">
            <img src="<?php echo esc_url($image_url); ?>" 
                 alt="<?php echo esc_attr($address); ?>"
                 loading="lazy">
            
            <?php if (!empty($gallery) && count($gallery) > 1): ?>
            <div class="hph-card-gallery__count">
                <i class="fas fa-images"></i> <?php echo count($gallery); ?> Photos
            </div>
            <?php endif; ?>
        </div>
        
        <div class="hph-card-gallery__overlay">
            <div class="hph-card-gallery__price">
                <?php echo esc_html($price); ?>
            </div>
            
            <h3 class="hph-card-gallery__address">
                <?php echo esc_html($address); ?>
            </h3>
            
            <?php if ($config['show_overlay_details']): ?>
            <div class="hph-card-gallery__details">
                <span><?php echo esc_html($beds); ?> Beds</span>
                <span><?php echo esc_html($baths); ?> Baths</span>
                <?php if ($sqft): ?>
                <span><?php echo esc_html($sqft); ?> Sq Ft</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
    </a>
    
</article>
