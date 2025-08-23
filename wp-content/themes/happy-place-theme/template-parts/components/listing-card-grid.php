<?php
/**
 * Listing Card - Grid View
 * Location: /template-parts/components/listing-card-grid.php
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue component assets
if (!wp_style_is('hph-listing-card', 'enqueued')) {
    wp_enqueue_style('hph-listing-card', 
        get_template_directory_uri() . '/assets/css/framework/03-components/hph-listing-card.css', 
        array('hph-framework'), 
        get_theme_mod('theme_version', '3.0.0')
    );
}

if (!wp_script_is('hph-listing-card', 'enqueued')) {
    wp_enqueue_script('hph-listing-card', 
        get_template_directory_uri() . '/assets/js/components/listing-card.js', 
        array('jquery'), 
        get_theme_mod('theme_version', '3.0.0'), 
        true
    );
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
$sqft = function_exists('hpt_get_listing_square_feet') ? 
    hpt_get_listing_square_feet($listing_id) : 0;
$lot_size = function_exists('hpt_get_listing_lot_size') ? 
    hpt_get_listing_lot_size($listing_id) : null;
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

// Prepare images array for carousel (combine featured image with gallery)
$images = array();
if ($image_url) {
    $images[] = array('url' => $image_url, 'alt' => $address);
}
if (!empty($gallery)) {
    foreach ($gallery as $img) {
        $img_url = is_array($img) ? $img['url'] : $img;
        $img_alt = is_array($img) ? ($img['alt'] ?: $address) : $address;
        // Don't duplicate the featured image
        if ($img_url !== $image_url) {
            $images[] = array('url' => $img_url, 'alt' => $img_alt);
        }
    }
}

// Generate unique carousel ID
$carousel_id = 'card-carousel-' . $listing_id;

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
    
    <!-- Card Background Carousel (entire card) -->
    <div class="hph-card__carousel" id="<?php echo esc_attr($carousel_id); ?>">
        <?php if (!empty($images)): ?>
            <?php foreach ($images as $index => $image): ?>
            <div class="hph-card__carousel-slide <?php echo $index === 0 ? 'hph-card__carousel-slide--active' : ''; ?>" 
                 style="background-image: url('<?php echo esc_url($image['url']); ?>');"
                 data-slide="<?php echo esc_attr($index); ?>">
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="hph-card__carousel-slide hph-card__carousel-slide--active"
                 style="background-image: url('<?php echo esc_url($image_url); ?>');">
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Gradient Overlay -->
    <div class="hph-card__overlay"></div>
    
    <!-- Card Content (overlaid on background) -->
    <div class="hph-card__content">
        
        <!-- Top Section: Badges and Actions -->
        <div class="hph-card__header">
            
            <!-- Badges (left side) -->
            <div class="hph-card__badges">
                <?php if ($config['show_status_badge'] && !empty($status)): ?>
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
            
            <!-- Actions (right side) -->
            <div class="hph-card__actions">
                <?php if ($config['show_save_button']): ?>
                <button type="button" 
                        class="hph-card__save <?php echo $is_favorite ? 'is-saved' : ''; ?>" 
                        data-listing-id="<?php echo esc_attr($listing_id); ?>"
                        aria-label="<?php echo $is_favorite ? 'Remove from favorites' : 'Add to favorites'; ?>">
                    <i class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-heart"></i>
                </button>
                <?php endif; ?>
                
                <?php if ($config['show_compare']): ?>
                <button type="button" 
                        class="hph-card__compare" 
                        data-listing-id="<?php echo esc_attr($listing_id); ?>"
                        aria-label="Add to comparison">
                    <i class="fas fa-plus"></i>
                </button>
                <?php endif; ?>
            </div>
            
        </div>
        
        <!-- Bottom Section: Property Info -->
        <div class="hph-card__info">
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
            <i class="fas fa-map-marker-alt"></i>
            <?php echo esc_html($city); ?><?php if ($city && $state): ?>, <?php endif; ?><?php echo esc_html($state); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($property_type)): ?>
        <div class="hph-card__type">
            <?php echo esc_html($property_type); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($config['show_details']): ?>
        <!-- Property Stats (single line layout) -->
        <div class="hph-card__stats">
            
            <!-- Bedrooms -->
            <div class="hph-card__stat">
                <div class="hph-card__stat-icon">
                    <i class="fas fa-bed"></i>
                </div>
                <div class="hph-card__stat-content">
                    <div class="hph-card__stat-value"><?php echo esc_html($beds); ?></div>
                    <div class="hph-card__stat-label">Beds</div>
                </div>
            </div>
            
            <!-- Bathrooms -->
            <div class="hph-card__stat">
                <div class="hph-card__stat-icon">
                    <i class="fas fa-bath"></i>
                </div>
                <div class="hph-card__stat-content">
                    <div class="hph-card__stat-value"><?php echo esc_html($baths); ?></div>
                    <div class="hph-card__stat-label">Baths</div>
                </div>
            </div>
            
            <!-- Square Footage -->
            <?php if ($sqft && $sqft > 0): ?>
            <div class="hph-card__stat">
                <div class="hph-card__stat-icon">
                    <i class="fas fa-ruler-combined"></i>
                </div>
                <div class="hph-card__stat-content">
                    <div class="hph-card__stat-value"><?php echo esc_html(number_format($sqft)); ?></div>
                    <div class="hph-card__stat-label">Sq Ft</div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Lot Size (as acreage) -->
            <?php if ($lot_size && $lot_size > 0): ?>
            <div class="hph-card__stat">
                <div class="hph-card__stat-icon">
                    <i class="fas fa-expand-arrows-alt"></i>
                </div>
                <div class="hph-card__stat-content">
                    <div class="hph-card__stat-value"><?php echo esc_html(number_format($lot_size, 2)); ?></div>
                    <div class="hph-card__stat-label">Acres</div>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Carousel Navigation -->
    <?php if (count($images) > 1): ?>
    <div class="hph-card__nav">
        <button class="hph-card__nav-btn hph-card__nav-btn--prev" 
                data-carousel="<?php echo esc_attr($carousel_id); ?>"
                aria-label="Previous image">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="hph-card__nav-btn hph-card__nav-btn--next" 
                data-carousel="<?php echo esc_attr($carousel_id); ?>"
                aria-label="Next image">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <?php endif; ?>
    
    <!-- Photo Count Badge -->
    <?php if (count($images) > 1): ?>
    <div class="hph-card__photo-count">
        <i class="fas fa-camera"></i>
        <span>1 / <?php echo count($images); ?></span>
    </div>
    <?php endif; ?>
    
</article>
