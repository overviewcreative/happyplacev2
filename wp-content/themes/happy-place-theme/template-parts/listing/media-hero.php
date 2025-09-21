<?php
/**
 * Unified Media Section - Gallery, Virtual Tour, Floor Plans
 * File: template-parts/listing/media-hero.php
 * 
 * Gallery carousel positioned right below hero section like reference screenshot
 * Simple layout with tab functionality for different media types
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();
if (!$listing_id) {
    return;
}

// Get gallery data
$gallery_images = [];
if (function_exists('hpt_get_listing_gallery_enhanced')) {
    try {
        $gallery_data = hpt_get_listing_gallery_enhanced($listing_id);
        $gallery_images = $gallery_data['images'] ?? [];
    } catch (Exception $e) {
        error_log('Gallery bridge function failed: ' . $e->getMessage());
    }
}

// Fallback for gallery images
if (empty($gallery_images)) {
    $gallery_images = get_field('property_gallery', $listing_id) 
                   ?: get_field('photo_gallery', $listing_id) 
                   ?: [];
    
    // Last resort: featured image
    if (empty($gallery_images) && has_post_thumbnail($listing_id)) {
        $featured_image_id = get_post_thumbnail_id($listing_id);
        $attachment = wp_get_attachment_image_src($featured_image_id, 'full');
        if ($attachment) {
            $gallery_images = [[
                'ID' => $featured_image_id,
                'url' => $attachment[0],
                'sizes' => [
                    'thumbnail' => wp_get_attachment_image_src($featured_image_id, 'thumbnail')[0],
                    'large' => wp_get_attachment_image_src($featured_image_id, 'large')[0]
                ],
                'alt' => get_post_meta($featured_image_id, '_wp_attachment_image_alt', true),
                'title' => get_the_title($featured_image_id)
            ]];
        }
    }
}

// Get virtual tour data
$virtual_tour_url = get_field('virtual_tour_url', $listing_id);
$video_tour_url = get_field('video_tour_url', $listing_id) ?: get_field('video_url', $listing_id);

// Get floor plans
$floor_plan_images = get_field('floor_plans', $listing_id) ?: [];

// If no media at all, don't show section
if (empty($gallery_images) && !$virtual_tour_url && !$video_tour_url && empty($floor_plan_images)) {
    return;
}

$unique_id = uniqid('gallery_');
$total_images = count($gallery_images);

// Count available media types
$available_tabs = [];
if (!empty($gallery_images)) {
    $available_tabs[] = 'photos';
}
if ($virtual_tour_url) {
    $available_tabs[] = 'virtual-tour';
}
if ($video_tour_url) {
    $available_tabs[] = 'video-tour';
}
if (!empty($floor_plan_images)) {
    $available_tabs[] = 'floor-plans';
}
?>

<section class="hph-listing-media hph-bg-white hph-py-0" data-component="listing-media" data-listing-id="<?php echo esc_attr($listing_id); ?>">
    
    <!-- Status Badge -->
    <div class="hph-absolute hph-top-md hph-right-md hph-z-20">
        <span class="hph-badge hph-badge--<?php echo esc_attr($status_color); ?> hph-badge--lg">
            <?php echo esc_html(ucwords(str_replace('_', ' ', $listing_status))); ?>
        </span>
    </div>
    
    <!-- Media Tabs Navigation -->
    <div class="hph-absolute hph-top-md hph-left-md hph-z-20">
        <div class="hph-media-tabs hph-flex hph-gap-xs">
            
            <?php if (!empty($gallery_images)) : ?>
            <button class="hph-media-tab hph-media-tab--active hph-px-md hph-py-sm hph-bg-white hph-bg-opacity-90 hph-rounded-md hph-font-medium hph-text-gray-900 hph-transition-all hover:hph-bg-opacity-100"
                    data-media-tab="photos">
                <i class="fas fa-images hph-mr-xs"></i>
                <span><?php echo esc_html($total_images); ?> Photos</span>
            </button>
            <?php endif; ?>
            
            <?php if ($virtual_tour_url) : ?>
            <button class="hph-media-tab hph-px-md hph-py-sm hph-bg-white hph-bg-opacity-75 hph-rounded-md hph-font-medium hph-text-gray-900 hph-transition-all hover:hph-bg-opacity-90"
                    data-media-tab="virtual-tour">
                <i class="fas fa-vr-cardboard hph-mr-xs"></i>
                <span>Virtual Tour</span>
            </button>
            <?php endif; ?>
            
            <?php if ($video_tour_url) : ?>
            <button class="hph-media-tab hph-px-md hph-py-sm hph-bg-white hph-bg-opacity-75 hph-rounded-md hph-font-medium hph-text-gray-900 hph-transition-all hover:hph-bg-opacity-90"
                    data-media-tab="video-tour">
                <i class="fas fa-video hph-mr-xs"></i>
                <span>Video Tour</span>
            </button>
            <?php endif; ?>
            
            <?php if (!empty($floor_plan_images)) : ?>
            <button class="hph-media-tab hph-px-md hph-py-sm hph-bg-white hph-bg-opacity-75 hph-rounded-md hph-font-medium hph-text-gray-900 hph-transition-all hover:hph-bg-opacity-90"
                    data-media-tab="floor-plans">
                <i class="fas fa-blueprint hph-mr-xs"></i>
                <span><?php echo count($floor_plan_images); ?> Floor Plans</span>
            </button>
            <?php endif; ?>
            
        </div>
    </div>
    
    <!-- Gallery Actions -->
    <div class="hph-absolute hph-bottom-md hph-right-md hph-z-20">
        <div class="hph-media-actions hph-flex hph-gap-xs">
            <button class="hph-action-btn hph-w-12 hph-h-12 hph-bg-white hph-bg-opacity-90 hph-rounded-full hph-flex hph-items-center hph-justify-center hph-text-gray-900 hph-transition-all hover:hph-bg-opacity-100 hover:hph-scale-110"
                    title="View Fullscreen">
                <i class="fas fa-expand-alt"></i>
            </button>
            <button class="hph-action-btn hph-w-12 hph-h-12 hph-bg-white hph-bg-opacity-90 hph-rounded-full hph-flex hph-items-center hph-justify-center hph-text-gray-900 hph-transition-all hover:hph-bg-opacity-100 hover:hph-scale-110"
                    title="Start Slideshow">
                <i class="fas fa-play"></i>
            </button>
        </div>
    </div>
    
    <!-- Media Content Container -->
    <div class="hph-media-content hph-relative hph-w-full" style="height: 70vh; min-height: 500px; max-height: 800px;">
        
        <!-- Photo Gallery Content -->
        <?php if (!empty($gallery_images)) : ?>
        <div class="hph-media-section hph-media-section--active hph-absolute hph-inset-0" data-media-content="photos">
            
            <!-- Main Gallery Carousel -->
            <div class="hph-gallery-carousel hph-relative hph-w-full hph-h-full">
                
                <div class="hph-carousel-viewport hph-w-full hph-h-full hph-overflow-hidden">
                    <div class="hph-carousel-track hph-flex hph-transition-transform hph-duration-500 hph-ease-in-out hph-h-full" 
                         data-carousel-track="<?php echo esc_attr($unique_id); ?>"
                         style="width: <?php echo count($gallery_images) * 100; ?>%;">
                        
                        <?php foreach ($gallery_images as $index => $image) : ?>
                        <div class="hph-carousel-slide hph-w-full hph-h-full hph-flex-shrink-0 hph-relative hph-cursor-pointer"
                             data-slide-index="<?php echo esc_attr($index); ?>">
                            
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
                        <?php endforeach; ?>
                        
                    </div>
                </div>
                
                <!-- Navigation Arrows -->
                <?php if (count($gallery_images) > 1) : ?>
                <button class="hph-carousel-btn hph-carousel-btn--prev hph-absolute hph-left-md hph-top-1/2 hph-transform hph--translate-y-1/2 hph-z-10 hph-w-12 hph-h-12 hph-bg-white hph-bg-opacity-75 hph-rounded-full hph-flex hph-items-center hph-justify-center hph-text-gray-900 hph-transition-all hover:hph-bg-opacity-90">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="hph-carousel-btn hph-carousel-btn--next hph-absolute hph-right-md hph-top-1/2 hph-transform hph--translate-y-1/2 hph-z-10 hph-w-12 hph-h-12 hph-bg-white hph-bg-opacity-75 hph-rounded-full hph-flex hph-items-center hph-justify-center hph-text-gray-900 hph-transition-all hover:hph-bg-opacity-90">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <?php endif; ?>
                
            </div>
            
        </div>
        <?php endif; ?>
        
        <!-- Virtual Tour Content -->
        <?php if ($virtual_tour_url) : ?>
        <div class="hph-media-section hph-absolute hph-inset-0" data-media-content="virtual-tour">
            <div class="hph-w-full hph-h-full hph-bg-gray-900 hph-flex hph-items-center hph-justify-center">
                <iframe src="<?php echo esc_url($virtual_tour_url); ?>" 
                        class="hph-w-full hph-h-full"
                        frameborder="0" 
                        allowfullscreen
                        allow="vr; xr; accelerometer; gyroscope; autoplay">
                </iframe>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Video Tour Content -->
        <?php if ($video_tour_url) : ?>
        <div class="hph-media-section hph-absolute hph-inset-0" data-media-content="video-tour">
            <div class="hph-w-full hph-h-full hph-bg-gray-900 hph-flex hph-items-center hph-justify-center">
                <?php
                // Check if YouTube or Vimeo
                $embed_url = $video_tour_url;
                if (strpos($video_tour_url, 'youtube.com') !== false || strpos($video_tour_url, 'youtu.be') !== false) {
                    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $video_tour_url, $matches);
                    $youtube_id = $matches[1] ?? '';
                    if ($youtube_id) {
                        $embed_url = "https://www.youtube.com/embed/{$youtube_id}";
                    }
                } elseif (strpos($video_tour_url, 'vimeo.com') !== false) {
                    preg_match('/vimeo\.com\/([0-9]+)/', $video_tour_url, $matches);
                    $vimeo_id = $matches[1] ?? '';
                    if ($vimeo_id) {
                        $embed_url = "https://player.vimeo.com/video/{$vimeo_id}";
                    }
                }
                ?>
                <iframe src="<?php echo esc_url($embed_url); ?>" 
                        class="hph-w-full hph-h-full"
                        frameborder="0" 
                        allowfullscreen>
                </iframe>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Floor Plans Content -->
        <?php if (!empty($floor_plan_images)) : ?>
        <div class="hph-media-section hph-absolute hph-inset-0" data-media-content="floor-plans">
            <div class="hph-w-full hph-h-full hph-bg-gray-100 hph-p-md hph-overflow-y-auto">
                <div class="hph-h-full hph-flex hph-items-center hph-justify-center">
                    <div class="hph-floor-plans-grid hph-grid hph-grid-cols-1 hph-lg:hph-grid-cols-2 hph-gap-lg hph-max-w-6xl">
                        <?php foreach ($floor_plan_images as $index => $floor_plan) : ?>
                        <div class="hph-floor-plan-item hph-bg-white hph-rounded-lg hph-shadow-md hph-overflow-hidden hph-cursor-pointer"
                             onclick="openMediaLightbox('<?php echo esc_js($unique_id); ?>', <?php echo esc_js($index); ?>, 'floor-plan')">
                            <img src="<?php echo esc_url($floor_plan['url']); ?>" 
                                 alt="<?php echo esc_attr($floor_plan['alt'] ?? 'Floor Plan'); ?>"
                                 class="hph-w-full hph-h-auto"
                                 loading="lazy">
                            <?php if (!empty($floor_plan['caption'])) : ?>
                            <div class="hph-p-md hph-text-center hph-font-medium hph-text-gray-700">
                                <?php echo esc_html($floor_plan['caption']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Thumbnail Strip (Only for Photos) -->
    <?php if (!empty($gallery_images) && count($gallery_images) > 1) : ?>
    <div class="hph-thumbnail-strip hph-absolute hph-bottom-0 hph-left-0 hph-right-0 hph-bg-gradient-to-t hph-from-black hph-from-0% hph-to-transparent hph-to-100% hph-pt-xl hph-pb-md hph-px-md"
         data-media-thumbnails="photos">
        <div class="hph-thumbnails-container hph-flex hph-gap-xs hph-justify-center hph-overflow-x-auto hph-pb-xs">
            <?php foreach ($gallery_images as $index => $image) : ?>
            <?php
            $thumb_src = '';
            if (is_array($image)) {
                $thumb_src = $image['sizes']['thumbnail'] ?? $image['url'] ?? '';
            } elseif (is_numeric($image)) {
                $thumb_src = wp_get_attachment_image_src($image, 'thumbnail')[0] ?? '';
            }
            ?>
            <button class="hph-thumbnail <?php echo $index === 0 ? 'hph-thumbnail--active' : ''; ?> hph-w-16 hph-h-12 hph-rounded hph-overflow-hidden hph-flex-shrink-0 hph-transition-all" 
                    data-thumbnail-index="<?php echo esc_attr($index); ?>"
                    data-gallery-id="<?php echo esc_attr($unique_id); ?>">
                <img src="<?php echo esc_url($thumb_src); ?>" 
                     alt="Thumbnail <?php echo $index + 1; ?>"
                     class="hph-w-full hph-h-full hph-object-cover"
                     loading="lazy">
            </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
</section>

<!-- Lightbox Modal -->
<div class="hph-media-lightbox hph-fixed hph-inset-0 hph-z-50 hph-bg-black hph-bg-opacity-95 hph-hidden hph-flex hph-items-center hph-justify-center hph-p-md"
     id="media-lightbox-<?php echo esc_attr($unique_id); ?>">
    
    <!-- Close Button -->
    <button class="hph-lightbox-close hph-absolute hph-top-md hph-right-md hph-z-20 hph-w-12 hph-h-12 hph-flex hph-items-center hph-justify-center hph-text-white hph-text-2xl hph-bg-black hph-bg-opacity-50 hph-rounded-full hph-transition-all hover:hph-bg-opacity-70">
        <i class="fas fa-times"></i>
    </button>
    
    <!-- Lightbox Content -->
    <div class="hph-lightbox-content hph-relative hph-w-full hph-h-full hph-flex hph-items-center hph-justify-center">
        
        <img id="lightbox-image-<?php echo esc_attr($unique_id); ?>" 
             class="hph-max-w-full hph-max-h-full hph-object-contain" 
             src="" 
             alt="">
        
        <!-- Navigation Buttons -->
        <?php if (count($gallery_images) > 1) : ?>
        <button class="hph-lightbox-prev hph-absolute hph-left-md hph-top-1/2 hph-transform hph--translate-y-1/2 hph-w-12 hph-h-12 hph-flex hph-items-center hph-justify-center hph-text-white hph-text-xl hph-bg-black hph-bg-opacity-50 hph-rounded-full hph-transition-all hover:hph-bg-opacity-70">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="hph-lightbox-next hph-absolute hph-right-md hph-top-1/2 hph-transform hph--translate-y-1/2 hph-w-12 hph-h-12 hph-flex hph-items-center hph-justify-center hph-text-white hph-text-xl hph-bg-black hph-bg-opacity-50 hph-rounded-full hph-transition-all hover:hph-bg-opacity-70">
            <i class="fas fa-chevron-right"></i>
        </button>
        <?php endif; ?>
        
    </div>
    
    <!-- Image Info -->
    <div class="hph-absolute hph-bottom-md hph-left-1/2 hph-transform hph--translate-x-1/2 hph-text-center hph-text-white">
        <div class="hph-text-sm hph-opacity-75">
            <span id="lightbox-counter-<?php echo esc_attr($unique_id); ?>"></span>
        </div>
    </div>
    
</div>

<?php
// Add media data to the page for JavaScript
wp_add_inline_script('hph-components', 
    'window.mediaHeroData_' . esc_js($unique_id) . ' = ' . json_encode([
        'images' => array_values(array_map(function($img, $index) {
            if (is_array($img)) {
                return [
                    'url' => $img['url'] ?? '',
                    'alt' => $img['alt'] ?? $img['title'] ?? "Property photo " . ($index + 1)
                ];
            } elseif (is_numeric($img)) {
                $attachment = wp_get_attachment_image_src($img, 'full');
                return [
                    'url' => $attachment[0] ?? '',
                    'alt' => get_post_meta($img, '_wp_attachment_image_alt', true) ?: get_the_title($img) ?: "Property photo " . ($index + 1)
                ];
            }
            return ['url' => '', 'alt' => "Property photo " . ($index + 1)];
        }, $gallery_images, array_keys($gallery_images))),
        'totalImages' => count($gallery_images),
        'currentIndex' => 0,
        'isSlideshow' => false
    ]) . ';', 
    'after'
);

// Enqueue the media hero JavaScript
// wp_enqueue_script('hph-media-hero', get_template_directory_uri() . '/assets/js/components/listing/media-hero.js', ['hph-components'], '1.0.0', true);
?>

<style>
/* Media Hero specific styles */
.hph-media-hero {
    position: relative;
}

.hph-media-section {
    display: none;
}

.hph-media-section--active {
    display: block;
}

.hph-media-tab--active {
    background-color: rgba(255, 255, 255, 0.9) !important;
}

.hph-thumbnail--active {
    border: 2px solid #fff;
    opacity: 1;
}

.hph-thumbnail:not(.hph-thumbnail--active) {
    opacity: 0.6;
}

.hph-thumbnail:hover {
    opacity: 1;
}

/* Ensure proper aspect ratios */
.hph-carousel-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Lightbox improvements */
.hph-media-lightbox img {
    max-width: 90vw;
    max-height: 90vh;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .hph-media-tabs {
        flex-wrap: wrap;
    }
    
    .hph-media-tab {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    
    .hph-media-content {
        height: 50vh !important;
        min-height: 300px !important;
    }
    
    .hph-thumbnail-strip {
        display: none;
    }
}
</style>
