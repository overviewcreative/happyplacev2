<?php
/**
 * Listing Media Section - Gallery, Virtual Tour, Floor Plans
 * File: template-parts/listing/media-section.php
 * 
 * Gallery positioned right below hero section like reference screenshot
 * Simple carousel with tabs for different media types
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
?>

<!-- Media Gallery Section -->
<section class="hph-listing-gallery-section hph-relative hph-bg-white hph-full-width">
    
    <!-- Main Gallery Container -->
    <div class="hph-gallery-container hph-relative">
        
        <!-- Media Tabs (positioned over the gallery) -->
        <?php if (count(array_filter([$gallery_images, $virtual_tour_url, $video_tour_url, $floor_plan_images])) > 1): ?>
        <div class="hph-media-tabs hph-absolute hph-top-4 hph-left-4 hph-z-20">
            
            <?php if (!empty($gallery_images)) : ?>
            <button class="hph-media-tab hph-media-tab--active" 
                    data-media-type="photos"
                    data-gallery-id="<?php echo esc_attr($unique_id); ?>">
                <i class="fas fa-images"></i>
                <span>Photos (<?php echo count($gallery_images); ?>)</span>
            </button>
            <?php endif; ?>
            
            <?php if ($virtual_tour_url) : ?>
            <button class="hph-media-tab" 
                    data-media-type="virtual-tour"
                    data-gallery-id="<?php echo esc_attr($unique_id); ?>">
                <i class="fas fa-vr-cardboard"></i>
                <span>Virtual Tour</span>
            </button>
            <?php endif; ?>
            
            <?php if ($video_tour_url) : ?>
            <button class="hph-media-tab" 
                    data-media-type="video-tour"
                    data-gallery-id="<?php echo esc_attr($unique_id); ?>">
                <i class="fas fa-video"></i>
                <span>Video Tour</span>
            </button>
            <?php endif; ?>
            
            <?php if (!empty($floor_plan_images)) : ?>
            <button class="hph-media-tab" 
                    data-media-type="floor-plans"
                    data-gallery-id="<?php echo esc_attr($unique_id); ?>">
                <i class="fas fa-blueprint"></i>
                <span>Floor Plans</span>
            </button>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
        <!-- Gallery Actions -->
        <div class="hph-gallery-actions hph-absolute hph-top-4 hph-right-4 hph-z-20">
            <button class="hph-action-btn hph-lightbox-trigger" 
                    title="View All Photos"
                    data-gallery-id="<?php echo esc_attr($unique_id); ?>">
                <i class="fas fa-th"></i>
                <span>View All Photos</span>
            </button>
        </div>
        
        <!-- Photo Gallery Content -->
        <?php if (!empty($gallery_images)) : ?>
        <div class="hph-media-content hph-media-content--active" 
             data-media-content="photos"
             data-gallery-id="<?php echo esc_attr($unique_id); ?>">
            
            <!-- Main Gallery Carousel -->
            <div class="hph-gallery-carousel hph-relative">
                
                <div class="hph-carousel-viewport">
                    <div class="hph-carousel-track" 
                         data-gallery-track="<?php echo esc_attr($unique_id); ?>"
                         style="width: <?php echo count($gallery_images) * 100; ?>%;">
                        
                        <?php foreach ($gallery_images as $index => $image) : ?>
                        <div class="hph-carousel-slide" 
                             data-slide-index="<?php echo esc_attr($index); ?>"
                             style="width: <?php echo 100 / count($gallery_images); ?>%;">
                            
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
                                 class="hph-gallery-image"
                                 loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                        </div>
                        <?php endforeach; ?>
                        
                    </div>
                </div>
                
                <!-- Navigation Arrows -->
                <?php if (count($gallery_images) > 1) : ?>
                <button class="hph-carousel-btn hph-carousel-btn--prev" 
                        data-gallery-prev="<?php echo esc_attr($unique_id); ?>">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="hph-carousel-btn hph-carousel-btn--next" 
                        data-gallery-next="<?php echo esc_attr($unique_id); ?>">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <?php endif; ?>
                
            </div>
            
        </div>
        <?php endif; ?>
        
        <!-- Virtual Tour Content -->
        <?php if ($virtual_tour_url) : ?>
        <div class="hph-media-content" 
             data-media-content="virtual-tour"
             data-gallery-id="<?php echo esc_attr($unique_id); ?>">
            
            <div class="hph-tour-container">
                <iframe src="<?php echo esc_url($virtual_tour_url); ?>" 
                        class="hph-tour-iframe"
                        frameborder="0" 
                        allowfullscreen
                        allow="vr; xr; accelerometer; gyroscope; autoplay">
                </iframe>
            </div>
            
        </div>
        <?php endif; ?>
        
        <!-- Video Tour Content -->
        <?php if ($video_tour_url) : ?>
        <div class="hph-media-content" 
             data-media-content="video-tour"
             data-gallery-id="<?php echo esc_attr($unique_id); ?>">
            
            <div class="hph-tour-container">
                <?php
                // Handle YouTube and Vimeo URLs
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
                        class="hph-tour-iframe"
                        frameborder="0" 
                        allowfullscreen>
                </iframe>
            </div>
            
        </div>
        <?php endif; ?>
        
        <!-- Floor Plans Content -->
        <?php if (!empty($floor_plan_images)) : ?>
        <div class="hph-media-content" 
             data-media-content="floor-plans"
             data-gallery-id="<?php echo esc_attr($unique_id); ?>">
            
            <div class="hph-floor-plans-container hph-h-full hph-flex hph-items-center hph-justify-center hph-p-8">
                <div class="hph-floor-plans-grid hph-grid hph-grid-cols-1 hph-lg:hph-grid-cols-2 hph-gap-8 hph-max-w-6xl">
                    <?php foreach ($floor_plan_images as $index => $floor_plan) : ?>
                    <div class="hph-floor-plan-item hph-bg-white hph-rounded-lg hph-shadow-lg hph-overflow-hidden">
                        <img src="<?php echo esc_url($floor_plan['url']); ?>" 
                             alt="<?php echo esc_attr($floor_plan['alt'] ?? 'Floor Plan'); ?>"
                             class="hph-floor-plan-image hph-w-full hph-h-auto"
                             loading="lazy">
                        <?php if (!empty($floor_plan['caption'])) : ?>
                        <div class="hph-floor-plan-caption hph-p-4 hph-text-center hph-font-medium hph-text-gray-700">
                            <?php echo esc_html($floor_plan['caption']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Thumbnail Strip -->
    <?php if (!empty($gallery_images) && count($gallery_images) > 1) : ?>
    <div class="hph-thumbnail-strip" data-gallery-id="<?php echo esc_attr($unique_id); ?>">
        <div class="hph-thumbnails-container">
            <?php foreach ($gallery_images as $index => $image) : ?>
            <?php
            $thumb_src = '';
            if (is_array($image)) {
                $thumb_src = $image['sizes']['thumbnail'] ?? $image['url'] ?? '';
            } elseif (is_numeric($image)) {
                $thumb_src = wp_get_attachment_image_src($image, 'thumbnail')[0] ?? '';
            }
            ?>
            <button class="hph-thumbnail <?php echo $index === 0 ? 'hph-thumbnail--active' : ''; ?>" 
                    data-thumbnail-index="<?php echo esc_attr($index); ?>"
                    data-gallery-id="<?php echo esc_attr($unique_id); ?>">
                <img src="<?php echo esc_url($thumb_src); ?>" 
                     alt="Thumbnail <?php echo $index + 1; ?>"
                     loading="lazy">
            </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
</section>

<!-- Lightbox Modal -->
<div class="hph-gallery-lightbox hph-hidden" 
     id="lightbox-<?php echo esc_attr($unique_id); ?>"
     data-gallery-id="<?php echo esc_attr($unique_id); ?>">
    
    <div class="hph-lightbox-overlay">
        
        <!-- Close Button -->
        <button class="hph-lightbox-close">
            <i class="fas fa-times"></i>
        </button>
        
        <!-- Lightbox Content -->
        <div class="hph-lightbox-content">
            <img class="hph-lightbox-image" src="" alt="">
            
            <!-- Navigation -->
            <?php if (count($gallery_images) > 1) : ?>
            <button class="hph-lightbox-nav hph-lightbox-nav--prev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hph-lightbox-nav hph-lightbox-nav--next">
                <i class="fas fa-chevron-right"></i>
            </button>
            <?php endif; ?>
            
        </div>
        
        <!-- Image Counter -->
        <div class="hph-lightbox-counter">
            <span class="hph-current">1</span> of <span class="hph-total"><?php echo count($gallery_images); ?></span>
        </div>
        
    </div>
    
</div>

<?php
// Add gallery data to JavaScript
wp_add_inline_script('hph-components', 
    'window.galleryData_' . esc_js($unique_id) . ' = ' . json_encode([
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
        'currentIndex' => 0
    ]) . ';', 
    'after'
);

// Enqueue JavaScript
wp_enqueue_script('hph-media-tabs', get_template_directory_uri() . '/assets/js/components/listing/media-tabs.js', [], '1.0.0', true);
?>

<style>
/* Inline critical styles for immediate rendering */
.hph-listing-gallery-section {
    position: relative;
    background: #000;
    height: 70vh;
    min-height: 600px;
    max-height: 800px;
    overflow: hidden;
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    margin-right: calc(-50vw + 50%);
}

.hph-gallery-container {
    height: 100%;
    position: relative;
}

.hph-media-content {
    display: none;
    height: 100%;
}

.hph-media-content--active {
    display: block;
}

.hph-gallery-carousel {
    height: 100%;
    position: relative;
}

.hph-carousel-viewport {
    height: 100%;
    overflow: hidden;
}

.hph-carousel-track {
    display: flex;
    height: 100%;
    transition: transform 0.5s ease-in-out;
}

.hph-carousel-slide {
    height: 100%;
    position: relative;
    flex-shrink: 0;
}

.hph-gallery-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.hph-media-tabs {
    display: flex;
    gap: 0.5rem;
    z-index: 30;
}

.hph-media-tab {
    background: rgba(0, 0, 0, 0.7);
    color: white;
    border: none;
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(8px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.hph-media-tab:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: translateY(-1px);
}

.hph-media-tab--active {
    background: rgba(var(--hph-primary-rgb), 0.9);
    color: white;
    box-shadow: 0 4px 12px rgba(var(--hph-primary-rgb), 0.3);
}

.hph-media-tab i {
    font-size: 1rem;
}

.hph-carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.9);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
}

.hph-carousel-btn--prev { left: 1rem; }
.hph-carousel-btn--next { right: 1rem; }

.hph-thumbnail-strip {
    background: rgba(0, 0, 0, 0.8);
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1rem;
}

.hph-thumbnails-container {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    overflow-x: auto;
}

.hph-thumbnail {
    width: 60px;
    height: 40px;
    border: 2px solid transparent;
    border-radius: 0.25rem;
    overflow: hidden;
    cursor: pointer;
    opacity: 0.7;
    transition: all 0.3s ease;
}

.hph-thumbnail--active {
    border-color: white;
    opacity: 1;
}

.hph-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hph-tour-container {
    height: 100%;
    background: #000;
}

.hph-tour-iframe {
    width: 100%;
    height: 100%;
}

.hph-tour-container {
    height: 100%;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hph-tour-iframe {
    width: 100%;
    height: 100%;
    min-height: 500px;
}

.hph-floor-plans-container {
    background: rgba(0, 0, 0, 0.1);
}

.hph-gallery-actions {
    z-index: 30;
}

.hph-action-btn {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.hph-action-btn:hover {
    background: rgba(255, 255, 255, 1);
    transform: translateY(-1px);
}

@media (max-width: 1024px) {
    .hph-listing-gallery-section {
        height: 60vh;
        min-height: 400px;
        margin-left: calc(-50vw + 50%);
        margin-right: calc(-50vw + 50%);
    }
}

@media (max-width: 768px) {
    .hph-listing-gallery-section {
        height: 50vh;
        min-height: 300px;
        margin-left: -1rem;
        margin-right: -1rem;
        width: calc(100% + 2rem);
    }
    
    .hph-thumbnail-strip {
        display: none;
    }
    
    .hph-media-tabs {
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .hph-media-tab {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
    }
    
    .hph-media-tab span {
        display: none;
    }
    
    .hph-floor-plans-grid {
        grid-template-columns: 1fr !important;
    }
    
    .hph-action-btn span {
        display: none;
    }
    
    .hph-action-btn {
        padding: 0.75rem;
    }
}
</style>
