<?php
/**
 * HPH Hero Listing Template
 * 
 * A specialized hero section for real estate property listings with:
 * - Image carousel/gallery functionality
 * - Property details and statistics
 * - Status badges and pricing
 * - Virtual tour and contact CTAs
 * - Mobile-optimized responsive design
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - listing_id: int (required for bridge functions)
 */

// Extract listing ID from args or global context
$listing_id = $args['listing_id'] ?? get_the_ID();

// Return early if no valid listing
if (!$listing_id) {
    return;
}

$post = get_post($listing_id);
if (!$post || $post->post_type !== 'listing') {
    return;
}

// Default arguments
$defaults = array(
    'listing_id' => $listing_id,
    'overlay_gradient' => 'heavy',
    'autoplay' => true,
    'autoplay_speed' => 5000,
    'section_id' => 'listing-hero'
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Get all listing data via bridge functions with null safety
$listing_data = array();

if ($listing_id) {
    $listing_data = array(
        // Gallery images
        'images' => function_exists('hpt_get_listing_gallery') ? hpt_get_listing_gallery($listing_id) : array(),
        
        // Basic information
        'property_type' => function_exists('hpt_get_listing_property_type_label') ? hpt_get_listing_property_type_label($listing_id) : 'Single Family Home',
        'address' => function_exists('hpt_get_listing_address') ? hpt_get_listing_address($listing_id, 'street') : (function_exists('hpt_get_listing_street_address') ? hpt_get_listing_street_address($listing_id) : get_the_title($listing_id)),
        'city' => function_exists('hpt_get_listing_city') ? hpt_get_listing_city($listing_id) : '',
        'state' => function_exists('hpt_get_listing_state') ? hpt_get_listing_state($listing_id) : '',
        'zip' => function_exists('hpt_get_listing_zip_code') ? hpt_get_listing_zip_code($listing_id) : '',
        
        // Price data
        'price' => function_exists('hpt_get_listing_price_formatted') ? hpt_get_listing_price_formatted($listing_id) : 'Price on Request',
        
        // Status
        'status' => function_exists('hpt_get_listing_status') ? hpt_get_listing_status($listing_id) : 'active',
        'status_label' => function_exists('hpt_get_listing_status_label') ? hpt_get_listing_status_label($listing_id) : '',
        
        // Property details
        'beds' => function_exists('hpt_get_listing_bedrooms') ? hpt_get_listing_bedrooms($listing_id) : 0,
        'baths' => function_exists('hpt_get_listing_bathrooms_formatted') ? hpt_get_listing_bathrooms_formatted($listing_id) : 0,
        'sqft' => function_exists('hpt_get_listing_square_feet') ? hpt_get_listing_square_feet($listing_id) : 0,
        'lot_size' => function_exists('hpt_get_listing_lot_size') ? hpt_get_listing_lot_size($listing_id) : 0,
        
        // Additional data
        'virtual_tour_url' => function_exists('hpt_get_listing_virtual_tour_url') ? hpt_get_listing_virtual_tour_url($listing_id) : '',
        'mls_number' => function_exists('hpt_get_listing_mls_number') ? hpt_get_listing_mls_number($listing_id) : '',
        'agent' => function_exists('hpt_get_listing_agent') ? hpt_get_listing_agent($listing_id) : array(),
        
        // Computed values
        'featured' => function_exists('hpt_is_listing_featured') ? hpt_is_listing_featured($listing_id) : false,
        'open_house' => function_exists('hpt_get_listing_open_house') ? hpt_get_listing_open_house($listing_id) : '',
    );
}

// Merge bridge data with config
$config = array_merge($config, $listing_data);

// Ensure featured image is first in carousel
if (!empty($config['images'])) {
    $featured_image_id = get_post_thumbnail_id($listing_id);
    if ($featured_image_id) {
        $featured_image_url = wp_get_attachment_image_url($featured_image_id, 'full');
        if ($featured_image_url) {
            // Create featured image array in same format as gallery
            $featured_image = array(
                'id' => $featured_image_id,
                'url' => $featured_image_url,
                'thumbnail' => wp_get_attachment_image_url($featured_image_id, 'thumbnail'),
                'medium' => wp_get_attachment_image_url($featured_image_id, 'medium'),
                'large' => wp_get_attachment_image_url($featured_image_id, 'large'),
                'alt' => get_post_meta($featured_image_id, '_wp_attachment_image_alt', true),
                'caption' => wp_get_attachment_caption($featured_image_id),
            );
            
            // Remove featured image from gallery if it exists to avoid duplicates
            $config['images'] = array_filter($config['images'], function($image) use ($featured_image_id) {
                $image_id = is_array($image) ? $image['id'] : $image;
                return $image_id != $featured_image_id;
            });
            
            // Add featured image to the beginning
            array_unshift($config['images'], $featured_image);
        }
    }
}

extract($config);

// Fallback values for missing data
$property_type = $property_type ?? 'Single Family Home';
$address = $address ?? get_the_title($listing_id);
$price = $price ?? 'Price on Request';
$beds = $beds ?? 0;
$baths = $baths ?? 0;
$sqft = $sqft ?? 0;
$lot_size = $lot_size ?? 0;
$status = $status ?? 'active';

// Format values for display
$formatted_sqft = $sqft ? number_format($sqft) : '';
$formatted_lot_size = $lot_size ? number_format($lot_size, 2) : '';

// Calculate days since last update
$post_modified = get_post_modified_time('U', false, $listing_id);
$current_time = current_time('timestamp');
$days_since_update = floor(($current_time - $post_modified) / DAY_IN_SECONDS);
$update_text = '';
if ($days_since_update === 0) {
    $update_text = 'Updated Today';
} elseif ($days_since_update === 1) {
    $update_text = 'Updated 1 Day Ago';
} else {
    $update_text = 'Updated ' . $days_since_update . ' Days Ago';
}

// Agent information
$agent_name = $agent['name'] ?? '';
$agent_phone = $agent['phone'] ?? '';

// Check user favorites
$favorite = function_exists('hpt_is_listing_favorite') ? hpt_is_listing_favorite($listing_id, get_current_user_id()) : false;

// Component assets are loaded by HPH_Assets service automatically

// Generate unique ID for carousel if not provided
$carousel_id = $config['section_id'] ?: 'hero-carousel-' . uniqid();

// Status badge configuration - using bridge function data
$status_badges = array(
    'active' => array('class' => 'hph-hero__badge--active', 'text' => 'For Sale', 'icon' => 'fas fa-home'),
    'pending' => array('class' => 'hph-hero__badge--pending', 'text' => 'Pending', 'icon' => 'fas fa-clock'),
    'sold' => array('class' => 'hph-hero__badge--sold', 'text' => 'Sold', 'icon' => 'fas fa-check-circle'),
    'coming-soon' => array('class' => 'hph-hero__badge--coming-soon', 'text' => 'Coming Soon', 'icon' => 'fas fa-calendar')
);

// Use status label from bridge function if available, otherwise use status
$status_text = $status_label ?: ($status_badges[$status]['text'] ?? 'For Sale');
$current_badge = $status_badges[$status] ?? $status_badges['active'];
$current_badge['text'] = $status_text;

// Overlay gradient classes
$overlay_classes = array(
    'standard' => 'hph-hero__overlay',
    'heavy' => 'hph-hero__overlay hph-hero__overlay--heavy',
    'light' => 'hph-hero__overlay hph-hero__overlay--light',
    'bottom-heavy' => 'hph-hero__overlay hph-hero__overlay--bottom-heavy'
);

$overlay_class = $overlay_classes[$config['overlay_gradient']] ?? $overlay_classes['standard'];
?>

<section class="hph-hero hph-hero-listing" 
         data-listing-id="<?php echo esc_attr($listing_id); ?>"
         <?php if ($config['section_id']): ?>id="<?php echo esc_attr($config['section_id']); ?>"<?php endif; ?>>
    
    <!-- Image Carousel -->
    <?php if (!empty($images)): ?>
    <div class="hph-hero__carousel" id="<?php echo esc_attr($carousel_id); ?>">
        <?php foreach ($images as $index => $image): ?>
        <div class="hph-hero__slide <?php echo $index === 0 ? 'hph-hero__slide--active' : ''; ?>" 
             style="background-image: url('<?php echo esc_url(is_array($image) ? $image['url'] : $image); ?>');"
             data-slide="<?php echo esc_attr($index); ?>">
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Fallback gradient if no images -->
    <div class="hph-hero__carousel">
        <div class="hph-hero__slide hph-hero__slide--active hph-hero__slide--fallback"></div>
    </div>
    <?php endif; ?>
    
    <!-- Gradient Overlay -->
    <div class="<?php echo esc_attr($overlay_class); ?>"></div>
    
    <!-- Main Content Wrapper -->
    <div class="hph-hero__wrapper">
        
        <!-- Header Section -->
        <div class="hph-hero__header">
            
            <!-- Status and Meta Badges -->
            <div class="hph-hero__meta">
                <!-- Status Badge -->
                <span class="hph-hero__badge <?php echo esc_attr($current_badge['class']); ?>">
                    <i class="<?php echo esc_attr($current_badge['icon']); ?>"></i>
                    <?php echo esc_html($current_badge['text']); ?>
                </span>
                
                <?php if ($featured): ?>
                <span class="hph-hero__meta-badge">
                    <i class="fas fa-star"></i> Featured
                </span>
                <?php endif; ?>
                
                <?php if ($open_house): ?>
                <span class="hph-hero__meta-badge">
                    <i class="fas fa-door-open"></i> Open House: <?php echo esc_html($open_house); ?>
                </span>
                <?php endif; ?>
                
                <?php if ($mls_number): ?>
                <span class="hph-hero__meta-badge">
                    MLS# <?php echo esc_html($mls_number); ?>
                </span>
                <?php endif; ?>
            </div>
            
            <!-- Controls -->
            <div class="hph-hero__controls">
                <?php if (!empty($images)): ?>
                <!-- Photo Counter / See All Photos -->
                <?php if (count($images) > 1): ?>
                <button class="hph-hero__photo-counter" onclick="openPhotoLightbox(<?php echo esc_attr($listing_id); ?>)" aria-label="See all photos">
                    <i class="fas fa-images"></i>
                    <span class="hph-hero__current-photo">1</span> / <?php echo count($images); ?>
                </button>
                <?php endif; ?>
                
                <?php if (count($images) > 1): ?>
                <!-- Navigation Controls -->
                <div class="hph-hero__nav">
                    <button class="hph-hero__nav-btn hph-hero__nav-btn--prev" aria-label="Previous image">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="hph-hero__nav-btn hph-hero__nav-btn--next" aria-label="Next image">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            
        </div>
        
        <!-- Main Content -->
        <div class="hph-hero__content">
            
            <!-- Property Type -->
            <div class="hph-hero__property-type hph-animate-fade-in">
                <?php echo esc_html($property_type); ?>
            </div>
            
            <!-- Address -->
            <h1 class="hph-hero__address hph-animate-fade-in-up">
                <?php echo esc_html($address); ?>
            </h1>
            
            <!-- Location -->
            <div class="hph-hero__location hph-animate-fade-in-up hph-delay-100">
                <i class="fas fa-map-marker-alt"></i>
                <span><?php echo esc_html($city); ?>, <?php echo esc_html($state); ?> <?php echo esc_html($zip); ?></span>
            </div>
            
            <!-- Price -->
            <div class="hph-hero__price hph-animate-fade-in-up hph-delay-200">
                <?php echo esc_html($price); ?>
            </div>
            
            <!-- Last Updated Badge -->
            <div class="hph-hero__updated-badge hph-animate-fade-in-up hph-delay-300">
                <i class="fas fa-clock"></i>
                <?php echo esc_html($update_text); ?>
            </div>
            
        </div>
        
        <!-- Footer Section -->
        <div class="hph-hero__footer">
            
            <!-- Property Stats -->
            <div class="hph-hero__stats">
                
                <!-- Bedrooms -->
                <div class="hph-hero__stat hph-animate-slide-up">
                    <div class="hph-hero__stat-icon">
                        <i class="fas fa-bed"></i>
                    </div>
                    <div class="hph-hero__stat-content">
                        <div class="hph-hero__stat-value"><?php echo esc_html($beds); ?></div>
                        <div class="hph-hero__stat-label">Beds</div>
                    </div>
                </div>
                
                <!-- Bathrooms -->
                <div class="hph-hero__stat hph-animate-slide-up hph-delay-100">
                    <div class="hph-hero__stat-icon">
                        <i class="fas fa-bath"></i>
                    </div>
                    <div class="hph-hero__stat-content">
                        <div class="hph-hero__stat-value"><?php echo esc_html($baths); ?></div>
                        <div class="hph-hero__stat-label">Baths</div>
                    </div>
                </div>
                
                <!-- Square Footage -->
                <div class="hph-hero__stat hph-animate-slide-up hph-delay-200">
                    <div class="hph-hero__stat-icon">
                        <i class="fas fa-ruler-combined"></i>
                    </div>
                    <div class="hph-hero__stat-content">
                        <div class="hph-hero__stat-value"><?php echo esc_html($formatted_sqft); ?></div>
                        <div class="hph-hero__stat-label">Sq Ft</div>
                    </div>
                </div>
                
                <!-- Lot Size -->
                <div class="hph-hero__stat hph-animate-slide-up hph-delay-300">
                    <div class="hph-hero__stat-icon">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </div>
                    <div class="hph-hero__stat-content">
                        <div class="hph-hero__stat-value"><?php echo esc_html($formatted_lot_size ?: 'N/A'); ?></div>
                        <div class="hph-hero__stat-label">ACRES</div>
                    </div>
                </div>
                
            </div>
            
            <!-- CTA Actions -->
            <div class="hph-hero__actions hph-animate-fade-in-up hph-delay-400">
                
                <?php if ($virtual_tour_url): ?>
                <!-- Virtual Tour Button -->
                <a href="<?php echo esc_url($virtual_tour_url); ?>" 
                   class="hph-hero__btn hph-hero__btn--primary"
                   target="_blank"
                   rel="noopener noreferrer">
                    <i class="fas fa-video"></i>
                    Virtual Tour
                </a>
                <?php endif; ?>
                
                <!-- Schedule Tour Button -->
                <button class="hph-hero__btn hph-hero__btn--primary" 
                        onclick="schedulePropertyTour('<?php echo esc_attr($address); ?>')">
                    <i class="fas fa-calendar-check"></i>
                    Schedule Tour
                </button>
                
                <!-- Contact Agent Button -->
                <?php if ($agent_phone): ?>
                <a href="tel:<?php echo esc_attr($agent_phone); ?>" 
                   class="hph-hero__btn hph-hero__btn--secondary">
                    <i class="fas fa-phone"></i>
                    <span class="btn-text">Call <?php echo esc_html($agent_name ? $agent_name : 'Agent'); ?></span>
                </a>
                <?php else: ?>
                <button class="hph-hero__btn hph-hero__btn--secondary"
                        onclick="contactAgent('<?php echo esc_attr($address); ?>')">
                    <i class="fas fa-envelope"></i>
                    <span class="btn-text">Contact Agent</span>
                </button>
                <?php endif; ?>
                
                <!-- Favorite Button -->
                <button class="hph-hero__btn hph-hero__btn--icon <?php echo $favorite ? 'is-favorite' : ''; ?>"
                        onclick="toggleFavorite('<?php echo esc_attr($listing_id); ?>')"
                        aria-label="Add to favorites">
                    <i class="<?php echo $favorite ? 'fas' : 'far'; ?> fa-heart"></i>
                    <span class="btn-text">Save</span>
                </button>
                
            </div>
            
        </div>
        
    </div>
    
</section>