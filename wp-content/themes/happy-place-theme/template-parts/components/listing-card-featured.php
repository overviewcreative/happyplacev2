<?php
/**
 * Listing Card - Featured
 * Location: /template-parts/components/listing-card-featured.php
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

// Get comprehensive listing data for featured display
$price = function_exists('hpt_get_listing_price_formatted') ? 
    hpt_get_listing_price_formatted($listing_id) : '';
$address = function_exists('hpt_get_listing_street_address') ? 
    hpt_get_listing_street_address($listing_id) : get_the_title($listing_id);
$city = function_exists('hpt_get_listing_city') ? 
    hpt_get_listing_city($listing_id) : '';
$state = function_exists('hpt_get_listing_state') ? 
    hpt_get_listing_state($listing_id) : '';
$beds = function_exists('hpt_get_listing_bedrooms') ? 
    hpt_get_listing_bedrooms($listing_id) : 0;
$baths = function_exists('hpt_get_listing_bathrooms_formatted') ? 
    hpt_get_listing_bathrooms_formatted($listing_id) : 0;
$sqft = function_exists('hpt_get_listing_square_feet_formatted') ? 
    hpt_get_listing_square_feet_formatted($listing_id) : '';
$property_type = function_exists('hpt_get_listing_property_type_label') ? 
    hpt_get_listing_property_type_label($listing_id) : '';
$description = function_exists('hpt_get_listing_description') ? 
    hpt_get_listing_description($listing_id) : get_the_excerpt($listing_id);
$virtual_tour = function_exists('hpt_get_listing_virtual_tour_url') ? 
    hpt_get_listing_virtual_tour_url($listing_id) : '';
$gallery = function_exists('hpt_get_listing_gallery') ? 
    hpt_get_listing_gallery($listing_id) : array();

// Get featured image
$image_url = get_the_post_thumbnail_url($listing_id, 'large');
if (!$image_url && !empty($gallery)) {
    $first_image = reset($gallery);
    $image_url = is_array($first_image) ? $first_image['url'] : $first_image;
}
if (!$image_url) {
    $image_url = HPH_ASSETS_URI . '/images/placeholder-property.jpg';
}
?>

<article class="hph-listing-card hph-listing-card--featured" 
         data-listing-id="<?php echo esc_attr($listing_id); ?>">
    
    <div class="hph-card-featured__wrapper">
        
        <!-- Image Section with Overlay -->
        <div class="hph-card-featured__image">
            <img src="<?php echo esc_url($image_url); ?>" 
                 alt="<?php echo esc_attr($address); ?>"
                 loading="lazy">
            
            <div class="hph-card-featured__overlay">
                <span class="hph-badge hph-badge--featured-large">
                    <i class="fas fa-star"></i> Featured Property
                </span>
                
                <?php if ($virtual_tour): ?>
                <a href="<?php echo esc_url($virtual_tour); ?>" 
                   class="hph-card-featured__tour"
                   target="_blank"
                   rel="noopener">
                    <i class="fas fa-video"></i> Virtual Tour Available
                </a>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($gallery) && count($gallery) > 1): ?>
            <div class="hph-card-featured__gallery-count">
                <i class="fas fa-camera"></i> <?php echo count($gallery); ?> Photos
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Content Section -->
        <div class="hph-card-featured__content">
            
            <div class="hph-card-featured__header">
                <div class="hph-card-featured__price">
                    <?php echo esc_html($price); ?>
                </div>
                <?php if ($property_type): ?>
                <span class="hph-card-featured__type">
                    <?php echo esc_html($property_type); ?>
                </span>
                <?php endif; ?>
            </div>
            
            <h3 class="hph-card-featured__title">
                <a href="<?php echo get_permalink($listing_id); ?>">
                    <?php echo esc_html($address); ?>
                </a>
            </h3>
            
            <div class="hph-card-featured__location">
                <i class="fas fa-map-marker-alt"></i>
                <?php echo esc_html($city); ?>, <?php echo esc_html($state); ?>
            </div>
            
            <div class="hph-card-featured__details">
                <div class="hph-detail-item">
                    <i class="fas fa-bed"></i>
                    <strong><?php echo esc_html($beds); ?></strong> Bedrooms
                </div>
                <div class="hph-detail-item">
                    <i class="fas fa-bath"></i>
                    <strong><?php echo esc_html($baths); ?></strong> Bathrooms
                </div>
                <?php if ($sqft): ?>
                <div class="hph-detail-item">
                    <i class="fas fa-ruler-combined"></i>
                    <strong><?php echo esc_html($sqft); ?></strong> Square Feet
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($description): ?>
            <div class="hph-card-featured__description">
                <?php echo wp_trim_words($description, 40); ?>
            </div>
            <?php endif; ?>
            
            <div class="hph-card-featured__actions">
                <a href="<?php echo get_permalink($listing_id); ?>" 
                   class="hph-btn hph-btn--primary">
                    <i class="fas fa-home"></i> View Property
                </a>
                <button class="hph-btn hph-btn--secondary hph-save-property"
                        data-listing="<?php echo esc_attr($listing_id); ?>">
                    <i class="far fa-heart"></i> Save
                </button>
                <button class="hph-btn hph-btn--ghost hph-share-property"
                        data-listing="<?php echo esc_attr($listing_id); ?>">
                    <i class="fas fa-share-alt"></i> Share
                </button>
            </div>
            
        </div>
        
    </div>
    
</article>
