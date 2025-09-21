<?php
/**
 * Listing Hero Component
 * 
 * Uses adapter service to transform listing data for hero display with carousel,
 * status badges, "Updated X Days Ago" badge, using framework CSS classes
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Listing
 * @since 3.0.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Ensure hero component CSS is loaded
if (function_exists('hph_enqueue_component')) {
    hph_enqueue_component('hero', [
        'css' => 'features/listing/listing-hero.css'
    ]);
}

// Get component args (compatible with both hph_component and get_template_part)
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', []);
$args = wp_parse_args($component_args, [
    'listing_id' => get_the_ID(),
    'layout' => 'full-width',
    'variant' => 'overlay',
    'height' => 'large',
    'show_gallery' => true,
    'show_price' => true,
    'show_stats' => true,
    'show_share' => true,
    'show_save' => true
]);

// Validate listing ID
$listing_id = $args['listing_id'];
if (!$listing_id) {
    return;
}

$post = get_post($listing_id);
if (!$post || $post->post_type !== 'listing') {
    return;
}

// Use adapter service to transform data
$adapter_service = hpt_adapter();

if (!$adapter_service) {
    // Fallback to direct bridge functions if adapter service unavailable
    $listing_data = function_exists('hpt_get_listing') ? hpt_get_listing($listing_id) : null;
    if (!$listing_data) {
        return;
    }
    
    // Build basic hero data manually
    $hero_data = [
        'title' => $listing_data['title'] ?: get_the_title($listing_id),
        'address' => is_array($listing_data['address']) ? 
                    ($listing_data['address']['display'] ?? $listing_data['address']['full'] ?? '') :
                    $listing_data['address'],
        'price' => $listing_data['price']['formatted'] ?? 'Contact for Price',
        'featured_image' => $listing_data['featured_image'] ?? '',
        'carousel_images' => [['url' => $listing_data['featured_image'] ?? '', 'alt' => $listing_data['title'] ?? '']],
        'status_badge' => null,
        'updated_badge' => null,
        'key_stats' => []
    ];
} else {
    // Transform data using adapter service
    $hero_data = $adapter_service->adapt_listing_hero($listing_id, array(
        'layout' => $args['layout'],
        'variant' => $args['variant'],
        'height' => $args['height'],
        'show_share' => $args['show_share'],
        'show_save' => $args['show_save']
    ));
    
    if (!$hero_data) {
        return;
    }
}

// Ensure we have at least basic data
if (empty($hero_data['title']) && empty($hero_data['address']) && empty($hero_data['carousel_images'])) {
    return;
}

?>

<section class="hph-hero hph-hero-image" 
         data-listing-id="<?php echo esc_attr($listing_id); ?>"
         <?php if (!empty($hero_data['carousel_images'][0]['url'])): ?>
         style="background-image: url('<?php echo esc_url($hero_data['carousel_images'][0]['url']); ?>');"
         <?php endif; ?>>
    
    <!-- Image Carousel Background -->
    <?php if ($args['show_gallery'] && !empty($hero_data['carousel_images'])): ?>
    <div class="hph-hero__carousel">
        <?php foreach ($hero_data['carousel_images'] as $index => $image): ?>
        <?php 
            // Handle array or string URL
            $image_url = '';
            if (is_array($image['url'])) {
                $image_url = isset($image['url']['url']) ? $image['url']['url'] : (isset($image['url'][0]) ? $image['url'][0] : '');
            } else {
                $image_url = $image['url'];
            }
            
            // Handle array or string alt text
            $image_alt = is_array($image['alt']) ? (isset($image['alt']['alt']) ? $image['alt']['alt'] : 'Property Image') : ($image['alt'] ?: 'Property Image');
        ?>
        <div class="hph-hero__slide <?php echo $index === 0 ? 'hph-hero__slide--active' : ''; ?>" 
             data-slide="<?php echo $index; ?>"
             style="background-image: url('<?php echo esc_url($image_url); ?>');">
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Photo Counter -->
    <?php if (count($hero_data['carousel_images']) > 1): ?>
    <button type="button" class="hph-hero__photo-counter" data-action="open-gallery">
        <i class="fas fa-camera"></i>
        <span class="hph-hero__current-photo">1</span> / <?php echo count($hero_data['carousel_images']); ?> Photos
    </button>
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Overlay -->
    <div class="hph-hero__overlay"></div>
    
    <!-- Main Wrapper -->
    <div class="hph-hero__wrapper">
        
        <!-- Header Section -->
        <div class="hph-hero__header">
            <div class="hph-hero__meta">
                <?php if (!empty($hero_data['status_badge'])): ?>
                <div class="hph-hero__badge hph-hero__badge--<?php echo esc_attr($hero_data['status_badge']['variant']); ?>">
                    <?php echo esc_html($hero_data['status_badge']['text']); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($hero_data['updated_badge'])): ?>
                <div class="hph-hero__updated-badge hph-hero__badge--<?php echo esc_attr($hero_data['updated_badge']['variant']); ?>">
                    <i class="fas fa-clock"></i>
                    <?php echo esc_html($hero_data['updated_badge']['text']); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="hph-hero__controls">
                <!-- Navigation Controls -->
                <?php if ($args['show_gallery'] && count($hero_data['carousel_images']) > 1): ?>
                <div class="hph-hero__nav">
                    <button type="button" class="hph-hero__nav-btn" data-action="prev" aria-label="Previous image">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="hph-hero__nav-btn" data-action="next" aria-label="Next image">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="hph-hero__content">
            <?php if (!empty($hero_data['property_type'])): ?>
            <div class="hph-hero__property-type">
                <?php echo esc_html($hero_data['property_type']); ?>
            </div>
            <?php endif; ?>
            
            <h1 class="hph-hero__address">
                <?php echo esc_html($hero_data['title']); ?>
            </h1>
            
            <?php if (!empty($hero_data['address'])): ?>
            <div class="hph-hero__location">
                <i class="fas fa-map-marker-alt"></i>
                <?php echo esc_html($hero_data['address']); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($args['show_price'] && !empty($hero_data['price'])): ?>
            <div class="hph-hero__price">
                <?php echo esc_html($hero_data['price']); ?>
                <?php if (!empty($hero_data['price_per_sqft'])): ?>
                <div class="hph-hero__price-per-sqft">
                    <?php echo esc_html($hero_data['price_per_sqft']); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer Section -->
        <div class="hph-hero__footer">
            <!-- Property Stats -->
            <?php if ($args['show_stats'] && !empty($hero_data['key_stats'])): ?>
            <div class="hph-hero__stats">
                <?php foreach ($hero_data['key_stats'] as $index => $stat): ?>
                <?php
                    // Handle string stats from adapter service
                    if (is_string($stat)) {
                        // Determine icon based on content
                        $icon = 'home';
                        if (strpos($stat, 'bed') !== false) {
                            $icon = 'bed';
                        } elseif (strpos($stat, '.') !== false && !strpos($stat, 'acres')) {
                            $icon = 'bath'; // bathroom count (has decimals)
                        } elseif (strpos($stat, 'sq ft') !== false) {
                            $icon = 'ruler-combined';
                        } elseif (strpos($stat, 'lot') !== false || strpos($stat, 'acres') !== false) {
                            $icon = 'map';
                        }
                        
                        $value = $stat;
                        $label = '';
                    } else {
                        // Handle object stats
                        $icon = $stat['icon'] ?? 'home';
                        $value = $stat['value'] ?? $stat;
                        $label = $stat['label'] ?? '';
                    }
                ?>
                <div class="hph-hero__stat">
                    <div class="hph-hero__stat-icon">
                        <i class="fas fa-<?php echo esc_attr($icon); ?>"></i>
                    </div>
                    <div class="hph-hero__stat-content">
                        <div class="hph-hero__stat-value"><?php echo esc_html($value); ?></div>
                        <?php if ($label): ?>
                        <div class="hph-hero__stat-label"><?php echo esc_html($label); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="hph-hero__actions">
                <?php if ($args['show_share']): ?>
                <button type="button" class="hph-hero__btn hph-hero__btn--secondary" data-action="share">
                    <i class="fas fa-share"></i>
                    <span class="btn-text">Share</span>
                </button>
                <?php endif; ?>
                
                <?php if ($args['show_save']): ?>
                <button type="button" class="hph-hero__btn hph-hero__btn--icon" data-action="save" data-listing-id="<?php echo esc_attr($listing_id); ?>">
                    <i class="far fa-heart"></i>
                    <span class="btn-text">Save</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
</section>
