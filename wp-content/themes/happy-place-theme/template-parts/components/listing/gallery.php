<?php
/**
 * Listing Gallery Component
 * Registered as 'listing-gallery' in component registry
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Extract component args (compatible with hph_component system)
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', []);
$args = wp_parse_args($component_args, [
    'listing_id' => null,
    'style' => 'grid',
    'show_thumbnails' => true,
    'lightbox' => true
]);

// Validate listing ID
$listing_id = $args['listing_id'];
if (!$listing_id) {
    return;
}

// Get listing gallery data
$gallery_images = [];
if (function_exists('hpt_get_listing_gallery')) {
    $gallery_images = hpt_get_listing_gallery($listing_id);
} else {
    // Fallback to featured image
    $listing_data = function_exists('hpt_get_listing') ? hpt_get_listing($listing_id) : null;
    if ($listing_data && !empty($listing_data['featured_image'])) {
        $gallery_images = [
            [
                'url' => $listing_data['featured_image'],
                'title' => $listing_data['title'] ?? '',
                'alt' => $listing_data['title'] ?? ''
            ]
        ];
    }
}

if (empty($gallery_images)) {
    return;
}

// Generate unique ID for this gallery instance
$gallery_id = 'listing-gallery-' . $listing_id;
?>

<div class="hph-listing-gallery hph-gallery--<?php echo esc_attr($args['style']); ?>" 
     data-component="listing-gallery" 
     data-listing-id="<?php echo esc_attr($listing_id); ?>">
    
    <div class="gallery-header">
        <h3 class="gallery-title">Property Photos</h3>
        <div class="gallery-count">
            <?php echo count($gallery_images); ?> Photos
        </div>
    </div>
    
    <!-- Main Gallery Grid -->
    <div class="gallery-grid" id="<?php echo esc_attr($gallery_id); ?>">
        
        <?php foreach ($gallery_images as $index => $image): ?>
        <div class="gallery-item <?php echo $index === 0 ? 'gallery-featured' : ''; ?>" 
             data-index="<?php echo esc_attr($index); ?>">
            
            <img src="<?php echo esc_url($image['url']); ?>" 
                 alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
                 class="gallery-image"
                 <?php if ($args['lightbox']): ?>
                 data-lightbox="listing-<?php echo esc_attr($listing_id); ?>"
                 data-title="<?php echo esc_attr($image['title'] ?? ''); ?>"
                 <?php endif; ?>
                 loading="lazy">
            
            <!-- Overlay for additional images count -->
            <?php if ($index > 8 && count($gallery_images) > 9): ?>
            <div class="gallery-overlay">
                <span class="overlay-text">
                    +<?php echo count($gallery_images) - 9; ?> More
                </span>
            </div>
            <?php endif; ?>
            
        </div>
        <?php 
        // Only show first 9 images in grid view
        if ($index >= 8) break;
        ?>
        <?php endforeach; ?>
        
    </div>
    
    <!-- Thumbnail Strip (if enabled) -->
    <?php if ($args['show_thumbnails'] && count($gallery_images) > 1): ?>
    <div class="gallery-thumbnails">
        <div class="thumbnail-strip">
            <?php foreach ($gallery_images as $index => $image): ?>
            <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                 data-index="<?php echo esc_attr($index); ?>">
                <img src="<?php echo esc_url($image['url']); ?>" 
                     alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
                     class="thumbnail-image">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Gallery Controls -->
    <?php if ($args['lightbox']): ?>
    <div class="gallery-controls">
        <button type="button" class="gallery-btn gallery-btn--view-all" 
                data-action="view-all" 
                data-gallery="<?php echo esc_attr($gallery_id); ?>">
            <i class="fas fa-expand"></i>
            View All Photos
        </button>
        
        <?php if (count($gallery_images) > 1): ?>
        <button type="button" class="gallery-btn gallery-btn--slideshow" 
                data-action="slideshow" 
                data-gallery="<?php echo esc_attr($gallery_id); ?>">
            <i class="fas fa-play"></i>
            Slideshow
        </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
</div>

<style>
.hph-listing-gallery {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.gallery-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.gallery-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.gallery-count {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
}

.gallery-grid {
    display: grid;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

/* Grid Layout Styles */
.hph-gallery--grid .gallery-grid {
    grid-template-columns: 2fr 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    height: 400px;
}

.hph-gallery--grid .gallery-featured {
    grid-row: span 2;
}

.hph-gallery--grid .gallery-item:nth-child(n+6) {
    display: none;
}

/* List Layout */
.hph-gallery--list .gallery-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.gallery-item:hover {
    transform: scale(1.02);
}

.gallery-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.2s ease;
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.125rem;
}

/* Thumbnail Strip */
.gallery-thumbnails {
    margin-top: 1rem;
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

.thumbnail-strip {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
}

.thumbnail-item {
    flex-shrink: 0;
    width: 80px;
    height: 60px;
    border-radius: 6px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color 0.2s ease;
}

.thumbnail-item.active,
.thumbnail-item:hover {
    border-color: #3b82f6;
}

.thumbnail-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Gallery Controls */
.gallery-controls {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.gallery-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    color: #374151;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.gallery-btn:hover {
    background: #e5e7eb;
    border-color: #9ca3af;
}

.gallery-btn--view-all {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.gallery-btn--view-all:hover {
    background: #2563eb;
    border-color: #2563eb;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hph-gallery--grid .gallery-grid {
        grid-template-columns: 1fr;
        grid-template-rows: auto;
        height: auto;
    }
    
    .hph-gallery--grid .gallery-featured {
        grid-row: span 1;
        height: 250px;
    }
    
    .hph-gallery--grid .gallery-item {
        height: 150px;
    }
    
    .gallery-controls {
        flex-direction: column;
    }
    
    .gallery-btn {
        justify-content: center;
    }
}
</style>

<?php if ($args['lightbox']): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize gallery interactions
    const gallery = document.getElementById('<?php echo esc_js($gallery_id); ?>');
    if (!gallery) return;
    
    // Thumbnail click handlers
    const thumbnails = gallery.closest('.hph-listing-gallery').querySelectorAll('.thumbnail-item');
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const index = this.dataset.index;
            // Update main image or trigger lightbox at specific index
            console.log('Show image at index:', index);
        });
    });
    
    // Gallery control handlers
    const controls = gallery.closest('.hph-listing-gallery').querySelectorAll('.gallery-btn');
    controls.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            if (action === 'view-all') {
                // Trigger lightbox view all
                console.log('View all photos');
            } else if (action === 'slideshow') {
                // Start slideshow
                console.log('Start slideshow');
            }
        });
    });
});
</script>
<?php endif; ?>