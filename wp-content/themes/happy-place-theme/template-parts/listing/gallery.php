<?php
/**
 * Enhanced Listing Gallery Component
 * File: template-parts/listing/gallery.php
 * 
 * Modern property gallery with lightbox, responsive grid, and proper JavaScript
 * Uses only HPH framework styling - no inline styles
 * 
 * @package HappyPlaceTheme
 */

// Register template for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('listing/gallery');
}

// Extract component args
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', []);
$args = wp_parse_args($component_args, [
    'listing_id' => get_the_ID(),
    'style' => 'grid', // grid | slider
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
    // Fallback 1: Direct bridge function
    if (function_exists('hpt_get_listing_gallery')) {
        try {
            $gallery_images = hpt_get_listing_gallery($listing_id);
        } catch (Exception $e) {
            error_log('Bridge function hpt_get_listing_gallery failed: ' . $e->getMessage());
        }
    }
    
    // Fallback 2: Direct field access to property_gallery
    if (empty($gallery_images)) {
        $gallery_images = get_field('property_gallery', $listing_id);
    }
    
    // Fallback 3: Try photo_gallery field
    if (empty($gallery_images)) {
        $gallery_images = get_field('photo_gallery', $listing_id);
    }
    
    // Fallback 4: Try listing_gallery field
    if (empty($gallery_images)) {
        $gallery_images = get_field('listing_gallery', $listing_id);
    }
    
    // Fallback 5: Try gallery field
    if (empty($gallery_images)) {
        $gallery_images = get_field('gallery', $listing_id);
    }
    
    // Fallback 6: Featured image as last resort
    if (empty($gallery_images) && has_post_thumbnail($listing_id)) {
        $featured_image_id = get_post_thumbnail_id($listing_id);
        if ($featured_image_id) {
            $attachment = wp_get_attachment_image_src($featured_image_id, 'full');
            if ($attachment) {
                $gallery_images = [[
                    'ID' => $featured_image_id,
                    'id' => $featured_image_id,
                    'url' => $attachment[0],
                    'sizes' => [
                        'thumbnail' => wp_get_attachment_image_src($featured_image_id, 'thumbnail')[0],
                        'medium' => wp_get_attachment_image_src($featured_image_id, 'medium')[0],
                        'large' => wp_get_attachment_image_src($featured_image_id, 'large')[0]
                    ],
                    'alt' => get_post_meta($featured_image_id, '_wp_attachment_image_alt', true),
                    'title' => get_the_title($featured_image_id)
                ]];
            }
        }
    }
}

// Count images and handle empty case
$total_images = !empty($gallery_images) ? count($gallery_images) : 0;
$gallery_id = 'listing-gallery-' . $listing_id;
$unique_id = uniqid('gallery_');

// Ensure Font Awesome is loaded for icons
if (!wp_style_is('font-awesome', 'enqueued')) {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}

// Display gallery or no images message
if ($total_images > 0) :
?>
<section class="hph-listing-gallery hph-section hph-mb-lg" data-component="listing-gallery" data-listing-id="<?php echo esc_attr($listing_id); ?>">>
    
    <!-- Section Header -->
    <div class="hph-section__header hph-mb-md hph-flex hph-justify-between hph-items-center">
        <h2 class="hph-section__title hph-text-xl hph-font-bold hph-flex hph-items-center hph-gap-sm">
            <i class="fas fa-images hph-text-primary"></i>
            Property Photos
        </h2>
        <?php if ($args['show_count'] && $total_images > 1) : ?>
        <span class="hph-gallery__count hph-px-sm hph-py-xs hph-bg-primary-100 hph-text-primary-700 hph-rounded-full hph-text-sm hph-font-medium">
            <?php echo esc_html($total_images); ?> Photos
        </span>
        <?php endif; ?>
    </div>
    
    <!-- Main Gallery Container -->
    <div class="hph-gallery__container hph-bg-white hph-rounded-lg hph-shadow-md hph-overflow-hidden" id="<?php echo esc_attr($gallery_id); ?>">
        
        <?php if ($args['style'] === 'grid') : ?>
        <!-- Grid Layout -->
        <div class="hph-gallery__grid hph-p-sm">
            <div class="hph-gallery__main-grid hph-grid hph-grid-cols-2 hph-md:hph-grid-cols-4 hph-gap-sm">
                
                <?php foreach ($gallery_images as $index => $image) : ?>
                <?php if ($index >= $args['max_visible']) break; ?>
                
                <?php
                // Determine grid positioning
                $grid_classes = 'hph-gallery__item hph-relative hph-overflow-hidden hph-rounded-md hph-cursor-pointer hph-group hph-bg-gray-100';
                if ($index === 0) {
                    // First image spans 2x2 on larger screens
                    $grid_classes .= ' hph-col-span-2 hph-row-span-2';
                }
                ?>
                
                <div class="<?php echo esc_attr($grid_classes); ?>"
                     data-index="<?php echo esc_attr($index); ?>"
                     onclick="openGalleryModalAtIndex('<?php echo esc_js($unique_id); ?>', <?php echo esc_js($index); ?>)">
                    
                    <!-- Image -->
                    <div class="hph-gallery__image-wrapper hph-w-full hph-h-full hph-min-h-24">
                        <?php
                        $image_src = '';
                        $image_alt = '';
                        
                        if (is_array($image)) {
                            $image_src = $image['sizes']['large'] ?? $image['large'] ?? $image['url'] ?? '';
                            $image_alt = $image['alt'] ?? $image['title'] ?? '';
                        } elseif (is_numeric($image)) {
                            $image_src = wp_get_attachment_image_src($image, 'large')[0] ?? '';
                            $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: get_the_title($image);
                        }
                        ?>
                        
                        <img src="<?php echo esc_url($image_src); ?>" 
                             alt="<?php echo esc_attr($image_alt); ?>"
                             class="hph-w-full hph-h-full hph-object-cover hph-transition-all hph-duration-300 group-hover:hph-scale-105"
                             loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                    </div>
                    
                    <!-- Hover Overlay -->
                    <div class="hph-gallery__overlay hph-absolute hph-inset-0 hph-bg-black hph-bg-opacity-0 group-hover:hph-bg-opacity-30 hph-transition-all hph-duration-300 hph-flex hph-items-center hph-justify-center">
                        <div class="hph-gallery__icon hph-opacity-0 group-hover:hph-opacity-100 hph-transition-all hph-duration-300 hph-text-white hph-text-2xl">
                            <i class="fas fa-expand-alt"></i>
                        </div>
                    </div>
                    
                    <!-- "View More" overlay for last visible item -->
                    <?php if ($index === $args['max_visible'] - 1 && $total_images > $args['max_visible']) : ?>
                    <div class="hph-gallery__more-overlay hph-absolute hph-inset-0 hph-bg-black hph-bg-opacity-70 hph-flex hph-flex-col hph-items-center hph-justify-center hph-text-white">
                        <i class="fas fa-images hph-text-3xl hph-mb-sm"></i>
                        <span class="hph-font-semibold hph-text-lg">+<?php echo esc_html($total_images - $args['max_visible']); ?> More</span>
                        <span class="hph-text-sm hph-opacity-80">Click to view all</span>
                    </div>
                    <?php endif; ?>
                    
                </div>
                <?php endforeach; ?>
                
            </div>
        </div>
        
        <?php else : ?>
        <!-- Slider Layout - Keep from enhanced version -->
        <div class="hph-gallery__slider hph-relative" data-slider="<?php echo esc_attr($unique_id); ?>">
            
            <div class="hph-slider__viewport hph-overflow-hidden hph-rounded-lg hph-bg-gray-100" data-slider-viewport>
                <div class="hph-slider__track hph-flex hph-transition-transform hph-duration-500 hph-ease-in-out" data-slider-track>
                    <?php foreach ($gallery_images as $index => $image) : ?>
                    <div class="hph-slider__slide hph-w-full hph-flex-shrink-0 hph-relative">
                        <div class="hph-slider__image-wrapper hph-w-full hph-h-96 hph-md:hph-h-128">
                            <?php
                            $image_src = '';
                            $image_alt = '';
                            
                            if (is_array($image)) {
                                $image_src = $image['url'] ?? '';
                                $image_alt = $image['alt'] ?? $image['title'] ?? '';
                            } elseif (is_numeric($image)) {
                                $image_src = wp_get_attachment_image_src($image, 'full')[0] ?? '';
                                $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: get_the_title($image);
                            }
                            ?>
                            
                            <img src="<?php echo esc_url($image_src); ?>" 
                                 alt="<?php echo esc_attr($image_alt); ?>"
                                 class="hph-w-full hph-h-full hph-object-cover"
                                 loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Slider Controls -->
            <?php if ($total_images > 1) : ?>
            <button class="hph-slider__btn hph-slider__btn--prev hph-absolute hph-left-md hph-top-1/2 hph-transform hph--translate-y-1/2 hph-z-10" 
                    data-slider-prev="<?php echo esc_attr($unique_id); ?>">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hph-slider__btn hph-slider__btn--next hph-absolute hph-right-md hph-top-1/2 hph-transform hph--translate-y-1/2 hph-z-10"
                    data-slider-next="<?php echo esc_attr($unique_id); ?>">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Slider Indicators -->
            <div class="hph-slider__indicators hph-absolute hph-bottom-md hph-left-1/2 hph-transform hph--translate-x-1/2 hph-flex hph-gap-xs">
                <?php for ($i = 0; $i < $total_images; $i++) : ?>
                <button class="hph-slider__dot <?php echo $i === 0 ? 'hph-slider__dot--active' : ''; ?>" 
                        data-slider-goto="<?php echo esc_attr($i); ?>"
                        data-slider-id="<?php echo esc_attr($unique_id); ?>">
                    <span class="hph-sr-only">Go to slide <?php echo $i + 1; ?></span>
                </button>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
        <!-- Gallery Actions -->
        <div class="hph-gallery__actions hph-p-sm hph-border-t hph-border-gray-200 hph-bg-gray-50">
            <div class="hph-flex hph-flex-wrap hph-gap-sm">
                
                <?php if ($args['lightbox']) : ?>
                <button class="hph-btn hph-btn-primary hph-btn-sm hph-flex hph-items-center hph-gap-xs"
                        onclick="openGalleryModal('<?php echo esc_js($unique_id); ?>')">
                    <i class="fas fa-expand-alt"></i>
                    <span>View Fullscreen</span>
                </button>
                <?php endif; ?>
                
                <button class="hph-btn hph-btn-secondary hph-btn-sm hph-flex hph-items-center hph-gap-xs"
                        onclick="toggleGallerySlideshow('<?php echo esc_js($unique_id); ?>')">
                    <i class="fas fa-play"></i>
                    <span>Slideshow</span>
                </button>
                
            </div>
        </div>
        
    </div>
    
</section>

<!-- Lightbox Modal -->
<?php if ($args['lightbox']) : ?>
<div id="gallery-modal-<?php echo esc_attr($unique_id); ?>" 
     class="hph-gallery-modal hph-fixed hph-inset-0 hph-z-50 hph-bg-black hph-bg-opacity-90 hph-hidden"
     data-modal="gallery">
    
    <div class="hph-modal__container hph-w-full hph-h-full hph-flex hph-items-center hph-justify-center hph-p-md">
        
        <!-- Modal Header -->
        <div class="hph-modal__header hph-absolute hph-top-0 hph-left-0 hph-right-0 hph-z-10 hph-p-md hph-flex hph-justify-between hph-items-center">
            <div class="hph-modal__info hph-text-white">
                <span class="hph-modal__counter">1 / <?php echo esc_html($total_images); ?></span>
            </div>
            <button class="hph-modal__close hph-text-white hph-text-2xl hph-hover:hph-text-gray-300"
                    onclick="closeGalleryModal('<?php echo esc_js($unique_id); ?>')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Modal Content -->
        <div class="hph-modal__content hph-relative hph-w-full hph-h-full hph-flex hph-items-center hph-justify-center">
            
            <!-- Main Image Container -->
            <div class="hph-modal__image-container hph-max-w-full hph-max-h-full hph-flex hph-items-center hph-justify-center">
                <img id="modal-image-<?php echo esc_attr($unique_id); ?>" 
                     src="" 
                     alt=""
                     class="hph-modal__image hph-max-w-full hph-max-h-full hph-object-contain">
            </div>
            
            <!-- Navigation Arrows -->
            <?php if ($total_images > 1) : ?>
            <button class="hph-modal__nav hph-modal__nav--prev hph-absolute hph-left-md hph-text-white hph-text-3xl hph-hover:hph-text-gray-300"
                    onclick="navigateGalleryModal('<?php echo esc_js($unique_id); ?>', -1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hph-modal__nav hph-modal__nav--next hph-absolute hph-right-md hph-text-white hph-text-3xl hph-hover:hph-text-gray-300"
                    onclick="navigateGalleryModal('<?php echo esc_js($unique_id); ?>', 1)">
                <i class="fas fa-chevron-right"></i>
            </button>
            <?php endif; ?>
            
        </div>
        
        <!-- Modal Footer -->
        <div class="hph-modal__footer hph-absolute hph-bottom-0 hph-left-0 hph-right-0 hph-p-md hph-text-center">
            <?php if ($total_images > 1) : ?>
            <div class="hph-modal__thumbnails hph-flex hph-justify-center hph-gap-xs hph-overflow-x-auto hph-max-w-full">
                <?php foreach ($gallery_images as $index => $image) : ?>
                <?php
                $thumb_src = '';
                if (is_array($image)) {
                    $thumb_src = $image['sizes']['thumbnail'] ?? $image['sizes']['medium'] ?? $image['url'] ?? '';
                } elseif (is_numeric($image)) {
                    $thumb_src = wp_get_attachment_image_src($image, 'thumbnail')[0] ?? '';
                }
                ?>
                <button class="hph-modal__thumb hph-w-16 hph-h-16 hph-overflow-hidden hph-rounded hph-border-2 hph-border-transparent hph-opacity-60 hover:hph-opacity-100"
                        data-index="<?php echo esc_attr($index); ?>"
                        onclick="setGalleryModalImage('<?php echo esc_js($unique_id); ?>', <?php echo esc_js($index); ?>)">
                    <img src="<?php echo esc_url($thumb_src); ?>" 
                         alt="Thumbnail <?php echo $index + 1; ?>"
                         class="hph-w-full hph-h-full hph-object-cover"
                         loading="lazy">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
    
</div>
<?php endif; ?>

<script>
// Gallery data for lightbox
window.galleryData = window.galleryData || {};
window.galleryData['<?php echo $unique_id; ?>'] = {
    images: [
        <?php
        $image_data = [];
        foreach ($gallery_images as $image) {
            $full_src = '';
            $image_alt = '';
            
            if (is_array($image)) {
                $full_src = $image['url'] ?? '';
                $image_alt = $image['alt'] ?? $image['title'] ?? '';
            } elseif (is_numeric($image)) {
                $full_src = wp_get_attachment_image_src($image, 'full')[0] ?? '';
                $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: get_the_title($image);
            }
            
            $image_data[] = json_encode(['url' => $full_src, 'alt' => $image_alt]);
        }
        echo implode(',', $image_data);
        ?>
    ],
    currentIndex: 0
};

// Gallery Modal Functions
function openGalleryModal(galleryId) {
    openGalleryModalAtIndex(galleryId, 0);
}

function openGalleryModalAtIndex(galleryId, index) {
    const modal = document.getElementById('gallery-modal-' + galleryId);
    const data = window.galleryData[galleryId];
    
    if (!modal || !data) return;
    
    data.currentIndex = parseInt(index);
    setGalleryModalImage(galleryId, data.currentIndex);
    modal.classList.remove('hph-hidden');
    document.body.style.overflow = 'hidden';
}

function closeGalleryModal(galleryId) {
    const modal = document.getElementById('gallery-modal-' + galleryId);
    if (modal) {
        modal.classList.add('hph-hidden');
        document.body.style.overflow = '';
    }
}

function navigateGalleryModal(galleryId, direction) {
    const data = window.galleryData[galleryId];
    if (!data) return;
    
    let newIndex = data.currentIndex + direction;
    
    if (newIndex < 0) newIndex = data.images.length - 1;
    if (newIndex >= data.images.length) newIndex = 0;
    
    setGalleryModalImage(galleryId, newIndex);
}

function setGalleryModalImage(galleryId, index) {
    const data = window.galleryData[galleryId];
    const image = document.getElementById('modal-image-' + galleryId);
    const counter = document.querySelector('#gallery-modal-' + galleryId + ' .hph-modal__counter');
    const thumbs = document.querySelectorAll('#gallery-modal-' + galleryId + ' .hph-modal__thumb');
    
    if (!data || !image) return;
    
    data.currentIndex = parseInt(index);
    const currentImage = data.images[data.currentIndex];
    
    if (currentImage) {
        image.src = currentImage.url;
        image.alt = currentImage.alt;
        
        if (counter) {
            counter.textContent = (data.currentIndex + 1) + ' / ' + data.images.length;
        }
        
        // Update thumbnail selection
        thumbs.forEach((thumb, i) => {
            if (i === data.currentIndex) {
                thumb.classList.add('hph-border-white', 'hph-opacity-100');
                thumb.classList.remove('hph-border-transparent', 'hph-opacity-60');
            } else {
                thumb.classList.remove('hph-border-white', 'hph-opacity-100');
                thumb.classList.add('hph-border-transparent', 'hph-opacity-60');
            }
        });
    }
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const openModal = document.querySelector('.hph-gallery-modal:not(.hph-hidden)');
    if (!openModal) return;
    
    const galleryId = openModal.id.replace('gallery-modal-', '');
    
    switch(e.key) {
        case 'Escape':
            closeGalleryModal(galleryId);
            break;
        case 'ArrowLeft':
            navigateGalleryModal(galleryId, -1);
            break;
        case 'ArrowRight':
            navigateGalleryModal(galleryId, 1);
            break;
    }
});

// Click outside modal to close
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('hph-gallery-modal')) {
        const galleryId = e.target.id.replace('gallery-modal-', '');
        closeGalleryModal(galleryId);
    }
});

// Slideshow functionality
function toggleGallerySlideshow(galleryId) {
    const data = window.galleryData[galleryId];
    if (!data) return;
    
    if (data.slideshowInterval) {
        clearInterval(data.slideshowInterval);
        data.slideshowInterval = null;
    } else {
        openGalleryModal(galleryId);
        data.slideshowInterval = setInterval(() => {
            navigateGalleryModal(galleryId, 1);
        }, 3000);
    }
}
</script>

<?php else : ?>

<!-- No Images Available -->
<section class="hph-listing-gallery hph-section hph-mb-lg">
    <div class="hph-no-gallery hph-bg-gray-100 hph-rounded-lg hph-p-xl hph-text-center">
        <div class="hph-no-gallery__icon hph-mb-md">
            <i class="fas fa-image hph-text-6xl hph-text-gray-400"></i>
        </div>
        <h3 class="hph-no-gallery__title hph-text-lg hph-font-semibold hph-text-gray-600 hph-mb-sm">
            No Photos Available
        </h3>
        <p class="hph-no-gallery__text hph-text-gray-500">
            Photos for this property will be available soon.
        </p>
    </div>
</section>

<?php endif; ?>

<?php
// Cleanup
wp_reset_postdata();
?>
