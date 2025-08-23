<?php
/**
 * Listing Card Templates Collection
 * Location: /wp-content/themes/happy-place/template-parts/components/
 * 
 * This file contains all the listing card variations for different view types:
 * - listing-card-grid.php (Grid view card)
 * - listing-card-list.php (List view card)
 * - listing-card-gallery.php (Gallery view card)
 * - listing-card-minimal.php (Minimal/sidebar card)
 * - listing-card-map.php (Map popup card)
 * - listing-card-featured.php (Featured property card)
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// ============================================================================
// LISTING CARD - GRID VIEW
// File: listing-card-grid.php
// ============================================================================
?>
<?php
/**
 * Listing Card - Grid View
 * Standard grid layout card for property listings
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get listing data using bridge functions
$listing_id = $args['listing_id'] ?? get_the_ID();
if (!$listing_id) return;

// Extract args with defaults
$defaults = array(
    'card_style' => 'default',
    'show_featured_badge' => true,
    'show_status_badge' => true,
    'show_price' => true,
    'show_address' => true,
    'show_details' => true,
    'show_save_button' => true,
    'show_compare' => false,
    'image_size' => 'large',
);
$config = wp_parse_args($args ?? array(), $defaults);

// Get listing data via bridge functions
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
$status_label = function_exists('hpt_get_listing_status_label') ? 
    hpt_get_listing_status_label($listing_id) : '';
$featured = function_exists('hpt_is_listing_featured') ? 
    hpt_is_listing_featured($listing_id) : false;
$gallery = function_exists('hpt_get_listing_gallery') ? 
    hpt_get_listing_gallery($listing_id) : array();
$property_type = function_exists('hpt_get_listing_property_type_label') ? 
    hpt_get_listing_property_type_label($listing_id) : '';

// Get featured image
$image_url = get_the_post_thumbnail_url($listing_id, $config['image_size']);
if (!$image_url && !empty($gallery)) {
    $first_image = reset($gallery);
    $image_url = is_array($first_image) ? $first_image['url'] : $first_image;
}
if (!$image_url) {
    $image_url = HPH_ASSETS_URI . '/images/placeholder-property.jpg';
}

// Check if favorited by current user
$is_favorite = false;
if (is_user_logged_in() && function_exists('hpt_is_listing_favorite')) {
    $is_favorite = hpt_is_listing_favorite($listing_id, get_current_user_id());
}

// Status badge configuration
$status_badges = array(
    'active' => array('class' => 'hph-badge--success', 'text' => 'For Sale'),
    'pending' => array('class' => 'hph-badge--warning', 'text' => 'Pending'),
    'sold' => array('class' => 'hph-badge--danger', 'text' => 'Sold'),
    'coming-soon' => array('class' => 'hph-badge--info', 'text' => 'Coming Soon'),
);
$current_badge = $status_badges[$status] ?? $status_badges['active'];
if (!empty($status_label)) {
    $current_badge['text'] = $status_label;
}
?>

<article class="hph-listing-card hph-listing-card--grid <?php echo esc_attr('hph-card--' . $config['card_style']); ?>" 
         data-listing-id="<?php echo esc_attr($listing_id); ?>">
    
    <!-- Card Image -->
    <div class="hph-card__image-wrapper">
        <a href="<?php echo get_permalink($listing_id); ?>" class="hph-card__image-link">
            <img src="<?php echo esc_url($image_url); ?>" 
                 alt="<?php echo esc_attr($address); ?>" 
                 class="hph-card__image"
                 loading="lazy">
            
            <?php if (!empty($gallery) && count($gallery) > 1): ?>
            <span class="hph-card__photo-count">
                <i class="fas fa-camera"></i> <?php echo count($gallery); ?>
            </span>
            <?php endif; ?>
        </a>
        
        <!-- Badges -->
        <div class="hph-card__badges">
            <?php if ($config['show_status_badge']): ?>
            <span class="hph-badge <?php echo esc_attr($current_badge['class']); ?>">
                <?php echo esc_html($current_badge['text']); ?>
            </span>
            <?php endif; ?>
            
            <?php if ($config['show_featured_badge'] && $featured): ?>
            <span class="hph-badge hph-badge--featured">
                <i class="fas fa-star"></i> Featured
            </span>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="hph-card__actions">
            <?php if ($config['show_save_button']): ?>
            <button class="hph-card__save <?php echo $is_favorite ? 'is-saved' : ''; ?>" 
                    data-listing="<?php echo esc_attr($listing_id); ?>"
                    aria-label="Save property">
                <i class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-heart"></i>
            </button>
            <?php endif; ?>
            
            <?php if ($config['show_compare']): ?>
            <label class="hph-card__compare">
                <input type="checkbox" 
                       class="hph-compare-checkbox" 
                       data-listing="<?php echo esc_attr($listing_id); ?>"
                       aria-label="Compare property">
                <i class="fas fa-balance-scale"></i>
            </label>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Card Content -->
    <div class="hph-card__content">
        
        <?php if ($config['show_price']): ?>
        <div class="hph-card__price">
            <?php echo esc_html($price); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($config['show_address']): ?>
        <h3 class="hph-card__title">
            <a href="<?php echo get_permalink($listing_id); ?>">
                <?php echo esc_html($address); ?>
            </a>
        </h3>
        <div class="hph-card__location">
            <?php echo esc_html($city); ?><?php if ($city && $state): ?>, <?php endif; ?><?php echo esc_html($state); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($property_type)): ?>
        <div class="hph-card__type">
            <?php echo esc_html($property_type); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($config['show_details']): ?>
        <div class="hph-card__details">
            <span class="hph-card__detail">
                <i class="fas fa-bed"></i>
                <span><?php echo esc_html($beds); ?></span>
                <span class="hph-detail-label">Beds</span>
            </span>
            <span class="hph-card__detail">
                <i class="fas fa-bath"></i>
                <span><?php echo esc_html($baths); ?></span>
                <span class="hph-detail-label">Baths</span>
            </span>
            <?php if ($sqft): ?>
            <span class="hph-card__detail">
                <i class="fas fa-ruler-combined"></i>
                <span><?php echo esc_html($sqft); ?></span>
                <span class="hph-detail-label">Sq Ft</span>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
    </div>
    
</article>

<?php
// ============================================================================
// LISTING CARD - LIST VIEW
// File: listing-card-list.php
// ============================================================================
?>
<?php
/**
 * Listing Card - List View
 * Horizontal list layout for property listings
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get listing data (reuse same bridge function calls as grid)
$listing_id = $args['listing_id'] ?? get_the_ID();
if (!$listing_id) return;

// [Same data fetching code as grid view...]
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

<?php
// ============================================================================
// LISTING CARD - GALLERY VIEW
// File: listing-card-gallery.php
// ============================================================================
?>
<?php
/**
 * Listing Card - Gallery View
 * Large image-focused layout for visual browsing
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

<?php
// ============================================================================
// LISTING CARD - MINIMAL VIEW
// File: listing-card-minimal.php
// ============================================================================
?>
<?php
/**
 * Listing Card - Minimal View
 * Compact layout for sidebars and small spaces
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

$listing_id = $args['listing_id'] ?? get_the_ID();
if (!$listing_id) return;

// Get listing data
$price = function_exists('hpt_get_listing_price_formatted') ? 
    hpt_get_listing_price_formatted($listing_id) : '';
$address = function_exists('hpt_get_listing_street_address') ? 
    hpt_get_listing_street_address($listing_id) : get_the_title($listing_id);
$city = function_exists('hpt_get_listing_city') ? 
    hpt_get_listing_city($listing_id) : '';
$beds = function_exists('hpt_get_listing_bedrooms') ? 
    hpt_get_listing_bedrooms($listing_id) : 0;
$baths = function_exists('hpt_get_listing_bathrooms_formatted') ? 
    hpt_get_listing_bathrooms_formatted($listing_id) : 0;

// Get thumbnail
$image_url = get_the_post_thumbnail_url($listing_id, 'thumbnail');
if (!$image_url) {
    $image_url = HPH_ASSETS_URI . '/images/placeholder-property-thumb.jpg';
}
?>

<article class="hph-listing-card hph-listing-card--minimal" 
         data-listing-id="<?php echo esc_attr($listing_id); ?>">
    
    <a href="<?php echo get_permalink($listing_id); ?>" class="hph-card-minimal__link">
        
        <div class="hph-card-minimal__image">
            <img src="<?php echo esc_url($image_url); ?>" 
                 alt="<?php echo esc_attr($address); ?>"
                 loading="lazy">
        </div>
        
        <div class="hph-card-minimal__content">
            <div class="hph-card-minimal__price">
                <?php echo esc_html($price); ?>
            </div>
            
            <h4 class="hph-card-minimal__address">
                <?php echo esc_html($address); ?>
            </h4>
            
            <div class="hph-card-minimal__meta">
                <span><?php echo esc_html($city); ?></span>
                <span><?php echo esc_html($beds); ?>bd / <?php echo esc_html($baths); ?>ba</span>
            </div>
        </div>
        
    </a>
    
</article>

<?php
// ============================================================================
// LISTING CARD - MAP VIEW
// File: listing-card-map.php
// ============================================================================
?>
<?php
/**
 * Listing Card - Map View
 * Compact card for map popups and sidebars
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

<?php
// ============================================================================
// LISTING CARD - FEATURED
// File: listing-card-featured.php
// ============================================================================
?>
<?php
/**
 * Listing Card - Featured
 * Premium card design for featured properties
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