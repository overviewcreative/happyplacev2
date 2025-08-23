<?php
/**
 * Listing Card - List View
 * Location: /template-parts/components/listing-card-list.php
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get listing data (reuse same bridge function calls as grid)
$listing_id = $args['listing_id'] ?? get_the_ID();
if (!$listing_id) return;

// Using bridge functions to get all listing data
$config = wp_parse_args($args ?? array(), array(
    'show_description' => true,
    'show_agent' => true,
    'show_featured_badge' => true,
    'show_status_badge' => true,
    'show_price' => true,
    'show_address' => true,
    'show_details' => true,
    'show_save_button' => true,
    'image_size' => 'medium_large',
));

// Get additional data for list view
$description = function_exists('hpt_get_listing_description') ? 
    hpt_get_listing_description($listing_id) : get_the_excerpt($listing_id);
$agent = function_exists('hpt_get_listing_agent') ? 
    hpt_get_listing_agent($listing_id) : array();
$virtual_tour = function_exists('hpt_get_listing_virtual_tour_url') ? 
    hpt_get_listing_virtual_tour_url($listing_id) : '';
$open_house = function_exists('hpt_get_listing_open_house') ? 
    hpt_get_listing_open_house($listing_id) : '';

// Reuse data from grid view
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
$status = function_exists('hpt_get_listing_status') ? 
    hpt_get_listing_status($listing_id) : 'active';
$featured = function_exists('hpt_is_listing_featured') ? 
    hpt_is_listing_featured($listing_id) : false;

// Get featured image
$image_url = get_the_post_thumbnail_url($listing_id, $config['image_size']);
if (!$image_url) {
    $gallery = function_exists('hpt_get_listing_gallery') ? 
        hpt_get_listing_gallery($listing_id) : array();
    if (!empty($gallery)) {
        $first_image = reset($gallery);
        $image_url = is_array($first_image) ? $first_image['url'] : $first_image;
    }
}
if (!$image_url) {
    $image_url = HPH_ASSETS_URI . '/images/placeholder-property.jpg';
}
?>

<article class="hph-listing-card hph-listing-card--list" 
         data-listing-id="<?php echo esc_attr($listing_id); ?>">
    
    <div class="hph-card-list__wrapper">
        
        <!-- Image Section -->
        <div class="hph-card-list__image">
            <a href="<?php echo get_permalink($listing_id); ?>">
                <img src="<?php echo esc_url($image_url); ?>" 
                     alt="<?php echo esc_attr($address); ?>"
                     loading="lazy">
            </a>
            
            <?php if ($config['show_status_badge']): ?>
            <span class="hph-badge hph-badge--overlay hph-badge--<?php echo esc_attr($status); ?>">
                <?php echo esc_html($status === 'active' ? 'For Sale' : ucfirst($status)); ?>
            </span>
            <?php endif; ?>
            
            <?php if ($virtual_tour): ?>
            <span class="hph-card-list__tour-badge">
                <i class="fas fa-video"></i> Virtual Tour
            </span>
            <?php endif; ?>
        </div>
        
        <!-- Content Section -->
        <div class="hph-card-list__content">
            
            <div class="hph-card-list__header">
                <?php if ($config['show_price']): ?>
                <div class="hph-card-list__price">
                    <?php echo esc_html($price); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($featured && $config['show_featured_badge']): ?>
                <span class="hph-badge hph-badge--featured">
                    <i class="fas fa-star"></i> Featured
                </span>
                <?php endif; ?>
            </div>
            
            <?php if ($config['show_address']): ?>
            <h3 class="hph-card-list__title">
                <a href="<?php echo get_permalink($listing_id); ?>">
                    <?php echo esc_html($address); ?>
                </a>
            </h3>
            <div class="hph-card-list__location">
                <i class="fas fa-map-marker-alt"></i>
                <?php echo esc_html($city); ?>, <?php echo esc_html($state); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($config['show_details']): ?>
            <div class="hph-card-list__details">
                <span class="hph-detail-item">
                    <i class="fas fa-bed"></i> <?php echo esc_html($beds); ?> Beds
                </span>
                <span class="hph-detail-item">
                    <i class="fas fa-bath"></i> <?php echo esc_html($baths); ?> Baths
                </span>
                <?php if ($sqft): ?>
                <span class="hph-detail-item">
                    <i class="fas fa-ruler-combined"></i> <?php echo esc_html($sqft); ?> Sq Ft
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($config['show_description'] && $description): ?>
            <div class="hph-card-list__description">
                <?php echo wp_trim_words($description, 30); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($open_house): ?>
            <div class="hph-card-list__open-house">
                <i class="fas fa-door-open"></i> Open House: <?php echo esc_html($open_house); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($config['show_agent'] && !empty($agent)): ?>
            <div class="hph-card-list__agent">
                <?php if (!empty($agent['photo'])): ?>
                <img src="<?php echo esc_url($agent['photo']); ?>" 
                     alt="<?php echo esc_attr($agent['name']); ?>"
                     class="hph-agent-photo">
                <?php endif; ?>
                <div class="hph-agent-info">
                    <span class="hph-agent-name"><?php echo esc_html($agent['name']); ?></span>
                    <?php if (!empty($agent['phone'])): ?>
                    <span class="hph-agent-phone"><?php echo esc_html($agent['phone']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
        <!-- Actions Section -->
        <div class="hph-card-list__actions">
            <?php if ($config['show_save_button']): ?>
            <button class="hph-btn hph-btn--icon" 
                    data-listing="<?php echo esc_attr($listing_id); ?>"
                    aria-label="Save property">
                <i class="far fa-heart"></i>
            </button>
            <?php endif; ?>
            
            <a href="<?php echo get_permalink($listing_id); ?>" 
               class="hph-btn hph-btn--primary">
                View Details
            </a>
        </div>
        
    </div>
    
</article>
