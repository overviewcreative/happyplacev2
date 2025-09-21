<?php
/**
 * Listing Card - Map View
 * Compact card for map sidebar display
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get listing data using correct ACF field names
$price = get_field('listing_price', $listing_id);
$bedrooms = get_field('bedrooms', $listing_id);
$bathrooms_full = get_field('bathrooms_full', $listing_id);
$bathrooms_half = get_field('bathrooms_half', $listing_id);
$square_feet = get_field('square_feet', $listing_id);
$listing_status = get_field('listing_status', $listing_id);
$property_type = get_field('property_type', $listing_id);

// Address fields
$street_number = get_field('street_number', $listing_id);
$street_name = get_field('street_name', $listing_id);
$street_type = get_field('street_type', $listing_id);
$city = get_field('city', $listing_id);
$state = get_field('state', $listing_id);

// Location data for map
$latitude = get_field('latitude', $listing_id);
$longitude = get_field('longitude', $listing_id);

// Media fields - prioritize primary photo over WordPress featured image
$primary_photo = get_field('primary_photo', $listing_id);
$photo_gallery = get_field('photo_gallery', $listing_id);

// Get best available image
$card_image = null;
$card_image_alt = '';

if ($primary_photo) {
    if (is_array($primary_photo)) {
        $card_image = $primary_photo['sizes']['medium'] ?? $primary_photo['url'];
        $card_image_alt = $primary_photo['alt'] ?? '';
    } elseif (is_numeric($primary_photo)) {
        $card_image = wp_get_attachment_image_url($primary_photo, 'medium');
        $card_image_alt = get_post_meta($primary_photo, '_wp_attachment_image_alt', true);
    }
} elseif (!empty($photo_gallery) && is_array($photo_gallery)) {
    $first_gallery_image = $photo_gallery[0];
    if ($first_gallery_image) {
        $card_image = $first_gallery_image['sizes']['medium'] ?? $first_gallery_image['url'];
        $card_image_alt = $first_gallery_image['alt'] ?? '';
    }
}

// Fallback to WordPress featured image if no ACF media found
if (!$card_image && has_post_thumbnail($listing_id)) {
    $card_image = get_the_post_thumbnail_url($listing_id, 'medium');
    $card_image_alt = get_post_meta(get_post_thumbnail_id($listing_id), '_wp_attachment_image_alt', true);
}

// Calculate total bathrooms
$total_baths = floatval($bathrooms_full) + (floatval($bathrooms_half) * 0.5);

// Build address
$address = trim($street_number . ' ' . $street_name . ' ' . $street_type);
$location = $city . ($state ? ', ' . $state : '');

// Status badges using BEM methodology
$status_config = [
    'active' => ['text' => 'Active', 'class' => 'hph-map-card__badge--success'],
    'pending' => ['text' => 'Pending', 'class' => 'hph-map-card__badge--warning'],
    'sold' => ['text' => 'Sold', 'class' => 'hph-map-card__badge--danger'],
    'new' => ['text' => 'New', 'class' => 'hph-map-card__badge--primary'],
];
?>

<div class="hph-map-card" 
     data-listing-id="<?php echo esc_attr($listing_id); ?>"
     data-lat="<?php echo esc_attr($latitude); ?>"
     data-lng="<?php echo esc_attr($longitude); ?>"
     data-price="<?php echo esc_attr($price); ?>"
     data-address="<?php echo esc_attr($address); ?>">
     
    <a href="<?php echo get_permalink($listing_id); ?>" class="hph-map-card__link">
        
        <!-- Image Section -->
        <div class="hph-map-card__image-wrapper">
            <?php if ($card_image) : ?>
                <img src="<?php echo esc_url($card_image); ?>" 
                     alt="<?php echo esc_attr($card_image_alt ?: $address); ?>"
                     class="hph-map-card__image"
                     loading="lazy">
            <?php else : ?>
                <div class="hph-map-card__image-placeholder">
                    <i class="fas fa-home"></i>
                </div>
            <?php endif; ?>
            
            <!-- Status Badge -->
            <?php if ($listing_status && isset($status_config[$listing_status])) : ?>
                <div class="hph-map-card__badge-wrapper">
                    <span class="hph-map-card__badge <?php echo $status_config[$listing_status]['class']; ?>">
                        <?php echo $status_config[$listing_status]['text']; ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Content Section -->
        <div class="hph-map-card__content">
            
            <!-- Price -->
            <?php if ($price) : ?>
                <div class="hph-map-card__price">
                    $<?php echo number_format($price); ?>
                </div>
            <?php endif; ?>
            
            <!-- Address -->
            <div class="hph-map-card__address">
                <h4 class="hph-map-card__title"><?php echo esc_html($address); ?></h4>
                <?php if ($location) : ?>
                    <p class="hph-map-card__location"><?php echo esc_html($location); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Quick Stats -->
            <div class="hph-map-card__stats">
                <?php if ($bedrooms) : ?>
                    <span class="hph-map-card__stat">
                        <i class="hph-map-card__stat-icon fas fa-bed"></i>
                        <span class="hph-map-card__stat-text"><?php echo esc_html($bedrooms); ?></span>
                    </span>
                <?php endif; ?>
                
                <?php if ($total_baths) : ?>
                    <span class="hph-map-card__stat">
                        <i class="hph-map-card__stat-icon fas fa-bath"></i>
                        <span class="hph-map-card__stat-text"><?php echo esc_html($total_baths); ?></span>
                    </span>
                <?php endif; ?>
                
                <?php if ($square_feet) : ?>
                    <span class="hph-map-card__stat">
                        <i class="hph-map-card__stat-icon fas fa-ruler-combined"></i>
                        <span class="hph-map-card__stat-text"><?php echo number_format($square_feet); ?> sq ft</span>
                    </span>
                <?php endif; ?>
                
                <?php if ($property_type) : ?>
                    <span class="hph-map-card__stat hph-map-card__stat--property-type">
                        <i class="hph-map-card__stat-icon fas fa-home"></i>
                        <span class="hph-map-card__stat-text"><?php echo esc_html(ucwords(str_replace('-', ' ', $property_type))); ?></span>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </a>
</div>
