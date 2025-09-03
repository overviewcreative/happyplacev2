<?php
/**
 * Listing Gallery Component
 * File: template-parts/listing/gallery.php
 * 
 * Displays property photos with lightbox and various layouts
 * Uses HPH framework utilities and CSS variables
 * Uses bridge functions for data access
 * 
 * @package HappyPlaceTheme
 */

// Extract component args
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', []);
$args = wp_parse_args($component_args, [
    'listing_id' => get_the_ID(),
    'style' => 'grid', // grid | masonry | slider | thumbnails
    'columns' => 3,
    'show_thumbnails' => true,
    'lightbox' => true,
    'show_count' => true,
    'max_visible' => 9
]);

$listing_id = $args['listing_id'];
if (!$listing_id) {
    return;
}

// Get gallery data using bridge functions with robust error handling
$gallery_data = null;
if (function_exists('hpt_get_listing_gallery_enhanced')) {
    try {
        $gallery_data = hpt_get_listing_gallery_enhanced($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_gallery_enhanced failed: ' . $e->getMessage());
    }
}

// Multiple fallback strategies for gallery images
$gallery_images = [];

// Try bridge function first
if ($gallery_data && !empty($gallery_data['images'])) {
    $gallery_images = $gallery_data['images'];
} else {
    // Fallback 1: Direct field access to property_gallery
    $gallery_images = get_field('property_gallery', $listing_id);
    
    // Fallback 2: Try photo_gallery field
    if (empty($gallery_images)) {
        $gallery_images = get_field('photo_gallery', $listing_id);
    }
    
    // Fallback 3: Try primary_photo + gallery
    if (empty($gallery_images)) {
        $primary_photo = get_field('primary_photo', $listing_id);
        $additional_photos = get_field('gallery_images', $listing_id) ?: [];
        
        if ($primary_photo) {
            $gallery_images = [$primary_photo];
            if (!empty($additional_photos)) {
                $gallery_images = array_merge($gallery_images, $additional_photos);
            }
        } elseif (!empty($additional_photos)) {
            $gallery_images = $additional_photos;
        }
    }
    
    // Fallback 4: Featured image as last resort
    if (empty($gallery_images) && has_post_thumbnail($listing_id)) {
        $featured_image_id = get_post_thumbnail_id($listing_id);
        if ($featured_image_id) {
            $gallery_images = [$featured_image_id];
        }
    }
}

if (empty($gallery_images)) {
    return;
}

$total_images = count($gallery_images);
$gallery_id = 'listing-gallery-' . $listing_id;
?>

<div class="hph-listing-gallery hph-gallery--<?php echo esc_attr($args['style']); ?> hph-bg-white hph-rounded-lg hph-shadow-md hph-p-lg" 
     data-component="listing-gallery" 
     data-listing-id="<?php echo esc_attr($listing_id); ?>"
     data-lightbox="<?php echo $args['lightbox'] ? 'true' : 'false'; ?>">
    
    <!-- Gallery Header -->
    <div class="hph-gallery__header hph-flex hph-justify-between hph-items-center hph-mb-lg">
        <h3 class="hph-gallery__title hph-text-xl hph-font-bold">Property Photos</h3>
        <?php if ($args['show_count'] && $total_images > 1) : ?>
        <span class="hph-gallery__count hph-text-sm hph-text-gray-600">
            <?php echo esc_html($total_images); ?> Photos
        </span>
        <?php endif; ?>
    </div>
    
    <!-- Main Gallery Container -->
    <div class="hph-gallery__container" id="<?php echo esc_attr($gallery_id); ?>">
        
        <?php if ($args['style'] === 'grid') : ?>
        <!-- Grid Layout -->
        <div class="hph-gallery__grid hph-grid hph-gap-sm" 
             style="grid-template-columns: repeat(<?php echo esc_attr($args['columns']); ?>, 1fr);">
            
            <?php foreach ($gallery_images as $index => $image) : ?>
            <?php if ($index >= $args['max_visible']) break; ?>
            
            <div class="hph-gallery__item <?php echo $index === 0 ? 'hph-col-span-2 hph-row-span-2' : ''; ?> hph-relative hph-overflow-hidden hph-rounded-md hph-cursor-pointer hph-group"
                 data-index="<?php echo esc_attr($index); ?>"
                 data-src="<?php echo esc_url($image['url']); ?>"
                 data-title="<?php echo esc_attr($image['title'] ?? ''); ?>">
                
                <img src="<?php echo esc_url($image['sizes']['large'] ?? $image['url']); ?>" 
                     alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
                     class="hph-w-full hph-h-full hph-object-cover hph-transition-transform hph-duration-300 group-hover:hph-scale-110"
                     loading="lazy">
                
                <!-- Overlay for "View More" on last visible item -->
                <?php if ($index === $args['max_visible'] - 1 && $total_images > $args['max_visible']) : ?>
                <div class="hph-gallery__overlay hph-absolute hph-inset-0 hph-bg-black hph-bg-opacity-70 hph-flex hph-items-center hph-justify-center">
                    <span class="hph-text-white hph-font-semibold hph-text-lg">
                        +<?php echo esc_html($total_images - $args['max_visible']); ?> More
                    </span>
                </div>
                <?php endif; ?>
                
            </div>
            <?php endforeach; ?>
            
        </div>
        
        <?php elseif ($args['style'] === 'slider') : ?>
        <!-- Slider Layout -->
        <div class="hph-gallery__slider hph-relative hph-overflow-hidden hph-rounded-lg" style="height: 500px;">
            
            <div class="hph-slider__track hph-flex hph-transition-transform hph-duration-500" data-current="0">
                <?php foreach ($gallery_images as $index => $image) : ?>
                <div class="hph-slider__slide hph-w-full hph-flex-shrink-0">
                    <img src="<?php echo esc_url($image['url']); ?>" 
                         alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
                         class="hph-w-full hph-h-full hph-object-cover">
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Slider Controls -->
            <button class="hph-slider__prev hph-absolute hph-left-md hph-top-1/2 hph--translate-y-1/2 hph-bg-white hph-bg-opacity-80 hph-rounded-full hph-p-sm hph-shadow-md hover:hph-bg-opacity-100 hph-transition-all">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hph-slider__next hph-absolute hph-right-md hph-top-1/2 hph--translate-y-1/2 hph-bg-white hph-bg-opacity-80 hph-rounded-full hph-p-sm hph-shadow-md hover:hph-bg-opacity-100 hph-transition-all">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Slider Indicators -->
            <div class="hph-slider__indicators hph-absolute hph-bottom-md hph-left-1/2 hph--translate-x-1/2 hph-flex hph-gap-xs">
                <?php for ($i = 0; $i < min($total_images, 5); $i++) : ?>
                <button class="hph-slider__dot hph-w-2 hph-h-2 hph-rounded-full hph-bg-white hph-bg-opacity-50 <?php echo $i === 0 ? 'hph-bg-opacity-100' : ''; ?> hph-transition-all"
                        data-slide="<?php echo esc_attr($i); ?>"></button>
                <?php endfor; ?>
            </div>
            
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Thumbnail Strip -->
    <?php if ($args['show_thumbnails'] && $total_images > 1) : ?>
    <div class="hph-gallery__thumbnails hph-mt-lg hph-pt-lg hph-border-t hph-border-gray-200">
        <div class="hph-thumbnails__strip hph-flex hph-gap-sm hph-overflow-x-auto hph-pb-sm">
            <?php foreach ($gallery_images as $index => $image) : ?>
            <button class="hph-thumbnail hph-flex-shrink-0 hph-w-20 hph-h-16 hph-rounded-md hph-overflow-hidden hph-border-2 hph-border-transparent hover:hph-border-primary hph-transition-all <?php echo $index === 0 ? 'hph-border-primary' : ''; ?>"
                    data-index="<?php echo esc_attr($index); ?>"
                    data-src="<?php echo esc_url($image['url']); ?>">
                <img src="<?php echo esc_url($image['sizes']['thumbnail'] ?? $image['url']); ?>" 
                     alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
                     class="hph-w-full hph-h-full hph-object-cover">
            </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Gallery Actions -->
    <div class="hph-gallery__actions hph-flex hph-gap-md hph-mt-lg hph-pt-lg hph-border-t hph-border-gray-200">
        
        <?php if ($args['lightbox']) : ?>
        <button class="hph-btn hph-btn--primary hph-flex hph-items-center hph-gap-sm"
                data-action="fullscreen"
                data-gallery="<?php echo esc_attr($gallery_id); ?>">
            <i class="fas fa-expand"></i>
            <span>View Fullscreen</span>
        </button>
        <?php endif; ?>
        
        <button class="hph-btn hph-btn--secondary hph-flex hph-items-center hph-gap-sm"
                data-action="slideshow">
            <i class="fas fa-play"></i>
            <span>Slideshow</span>
        </button>
        
        <button class="hph-btn hph-btn--secondary hph-flex hph-items-center hph-gap-sm"
                data-action="download">
            <i class="fas fa-download"></i>
            <span>Download</span>
        </button>
        
    </div>
    
</div>

<!-- Lightbox Modal (if enabled) -->
<?php if ($args['lightbox']) : ?>
<div class="hph-lightbox hph-fixed hph-inset-0 hph-z-50 hph-bg-black hph-bg-opacity-95 hph-hidden"
     id="lightbox-<?php echo esc_attr($gallery_id); ?>">
    
    <button class="hph-lightbox__close hph-absolute hph-top-lg hph-right-lg hph-text-white hph-text-3xl hover:hph-text-gray-300">
        <i class="fas fa-times"></i>
    </button>
    
    <div class="hph-lightbox__container hph-h-full hph-flex hph-flex-col hph-items-center hph-justify-center hph-p-xl">
        
        <div class="hph-lightbox__image-wrapper hph-relative hph-max-w-full hph-max-h-full">
            <img class="hph-lightbox__image hph-max-w-full hph-max-h-screen hph-object-contain" src="" alt="">
            
            <!-- Navigation -->
            <button class="hph-lightbox__prev hph-absolute hph-left-lg hph-top-1/2 hph--translate-y-1/2 hph-text-white hph-text-2xl hover:hph-text-gray-300">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hph-lightbox__next hph-absolute hph-right-lg hph-top-1/2 hph--translate-y-1/2 hph-text-white hph-text-2xl hover:hph-text-gray-300">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <div class="hph-lightbox__info hph-text-white hph-text-center hph-mt-lg">
            <h4 class="hph-lightbox__title hph-text-lg hph-mb-sm"></h4>
            <span class="hph-lightbox__counter hph-text-sm hph-text-gray-400"></span>
        </div>
        
    </div>
    
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gallery = document.getElementById('<?php echo esc_js($gallery_id); ?>');
    if (!gallery) return;
    
    const galleryData = <?php echo json_encode(array_map(function($img) {
        return [
            'url' => $img['url'],
            'title' => $img['title'] ?? '',
            'alt' => $img['alt'] ?? ''
        ];
    }, $gallery_images)); ?>;
    
    // Initialize gallery interactions
    initializeGallery(gallery, galleryData);
});

function initializeGallery(gallery, images) {
    let currentIndex = 0;
    
    // Gallery item clicks
    gallery.querySelectorAll('.hph-gallery__item').forEach(item => {
        item.addEventListener('click', function() {
            currentIndex = parseInt(this.dataset.index);
            openLightbox(currentIndex, images);
        });
    });
    
    // Thumbnail clicks
    document.querySelectorAll('.hph-thumbnail').forEach(thumb => {
        thumb.addEventListener('click', function() {
            currentIndex = parseInt(this.dataset.index);
            updateMainImage(currentIndex, images);
        });
    });
}

function openLightbox(index, images) {
    // Lightbox implementation
}
</script>