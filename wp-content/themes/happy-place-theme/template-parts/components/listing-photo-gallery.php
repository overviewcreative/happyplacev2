<?php
/**
 * HPH Property Gallery Section Template
 * 
 * Full-featured image gallery with lightbox, categories, and virtual tour
 * Location: /wp-content/themes/happy-place/template-parts/components/listing-photo-gallery.php
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - listing_id: int (required for bridge functions)
 * - images: array of arrays with 'url', 'title', 'category', 'caption' (optional - will use bridge if not provided)
 * - categories: array of category names (optional)
 * - virtual_tour_url: string (optional)
 * - video_tour_url: string (optional)
 * - floor_plan_images: array of floor plan image URLs (optional)
 * - default_view: 'grid' | 'list' | 'slider'
 * - images_per_row: int (3, 4, or 5)
 * - show_count: boolean
 * - enable_download: boolean
 * - enable_fullscreen: boolean
 * - lazy_load: boolean
 * - section_id: string (optional)
 */

// Extract listing ID from args or global context
$listing_id = $args['listing_id'] ?? get_the_ID();

// Default arguments
$defaults = array(
    'listing_id' => $listing_id,
    'images' => array(),
    'categories' => array(),
    'virtual_tour_url' => '',
    'video_tour_url' => '',
    'floor_plan_images' => array(),
    'default_view' => 'grid',
    'images_per_row' => 4,
    'show_count' => true,
    'enable_download' => false,
    'enable_fullscreen' => true,
    'lazy_load' => true,
    'section_id' => 'property-gallery'
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Use bridge functions to get data if not provided
if ($listing_id) {
    $gallery_data = hpt_get_listing_gallery_data($listing_id);
    
    // Use bridge data if component args are empty
    if (empty($config['images'])) {
        $config['images'] = hpt_get_listing_gallery_enhanced($listing_id);
    }
    if (empty($config['categories'])) {
        $config['categories'] = $gallery_data['categories'];
    }
    if (empty($config['virtual_tour_url'])) {
        $config['virtual_tour_url'] = $gallery_data['virtual_tour_url'];
    }
    if (empty($config['video_tour_url'])) {
        $config['video_tour_url'] = $gallery_data['video_tour_url'];
    }
    if (empty($config['floor_plan_images'])) {
        $config['floor_plan_images'] = $gallery_data['floor_plan_images'];
    }
    
    // Update section ID with listing ID
    $config['section_id'] = $gallery_data['section_id'];
}

extract($config);

// Generate default images if none provided (fallback for templates)
if (empty($images)) {
    $images = hpt_get_default_gallery_images();
}

// Count images by category
$category_counts = array('All' => count($images));
foreach ($categories as $category) {
    if ($category !== 'All') {
        $category_counts[$category] = count(array_filter($images, function($img) use ($category) {
            return $img['category'] === $category;
        }));
    }
}

// Enqueue gallery assets using bridge function
hpt_enqueue_gallery_assets();

// Pass configuration to JavaScript using bridge function
if ($listing_id) {
    $js_config = hpt_get_gallery_js_config($listing_id);
} else {
    // Fallback config for static templates
    $js_config = array(
        'images' => $images,
        'enableDownload' => $enable_download,
        'lazyLoad' => $lazy_load,
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hph_gallery_nonce'),
        'listingId' => 0
    );
}

wp_localize_script('hph-listing-gallery', 'hphGalleryConfig', $js_config);
?>

<section class="hph-property-gallery" id="<?php echo esc_attr($section_id); ?>">
    <div class="hph-gallery-container">
        
        <!-- Gallery Header -->
        <div class="hph-gallery-header">
            <div class="hph-gallery-title-wrapper">
                <h2 class="hph-gallery-title">
                    <i class="fas fa-images"></i>
                    Property Photos & Tours
                </h2>
                <?php if ($show_count): ?>
                <span class="hph-gallery-count">
                    <?php echo count($images); ?> Photos
                </span>
                <?php endif; ?>
            </div>
            
            <!-- View Controls -->
            <div class="hph-gallery-controls">
                <!-- View Type Switcher -->
                <div class="hph-view-switcher" role="tablist">
                    <button class="hph-view-btn <?php echo $default_view === 'grid' ? 'active' : ''; ?>" 
                            data-view="grid" 
                            role="tab" 
                            aria-selected="<?php echo $default_view === 'grid' ? 'true' : 'false'; ?>"
                            aria-label="Grid view">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="hph-view-btn <?php echo $default_view === 'list' ? 'active' : ''; ?>" 
                            data-view="list" 
                            role="tab"
                            aria-selected="<?php echo $default_view === 'list' ? 'true' : 'false'; ?>"
                            aria-label="List view">
                        <i class="fas fa-list"></i>
                    </button>
                    <button class="hph-view-btn <?php echo $default_view === 'slider' ? 'active' : ''; ?>" 
                            data-view="slider" 
                            role="tab"
                            aria-selected="<?php echo $default_view === 'slider' ? 'true' : 'false'; ?>"
                            aria-label="Slider view">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </div>
                
                <!-- Tour Buttons -->
                <div class="hph-tour-buttons">
                    <?php if ($virtual_tour_url): ?>
                    <a href="<?php echo esc_url($virtual_tour_url); ?>" 
                       class="hph-tour-btn hph-virtual-tour-btn" 
                       target="_blank"
                       rel="noopener noreferrer">
                        <i class="fas fa-vr-cardboard"></i>
                        <span>Virtual Tour</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($video_tour_url): ?>
                    <button class="hph-tour-btn hph-video-tour-btn" 
                            data-video="<?php echo esc_attr($video_tour_url); ?>">
                        <i class="fas fa-video"></i>
                        <span>Video Tour</span>
                    </button>
                    <?php endif; ?>
                    
                    <?php if (!empty($floor_plan_images)): ?>
                    <button class="hph-tour-btn hph-floor-plan-btn">
                        <i class="fas fa-home"></i>
                        <span>Floor Plans</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Category Filter -->
        <div class="hph-gallery-filter">
            <div class="hph-filter-wrapper">
                <?php foreach ($categories as $category): ?>
                <button class="hph-filter-item <?php echo $category === 'All' ? 'active' : ''; ?>" 
                        data-category="<?php echo esc_attr(strtolower(str_replace(' ', '-', $category))); ?>">
                    <?php echo esc_html($category); ?>
                    <?php if ($show_count && isset($category_counts[$category])): ?>
                    <span class="hph-filter-count"><?php echo $category_counts[$category]; ?></span>
                    <?php endif; ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Gallery Views Container -->
        <div class="hph-gallery-views">
            
            <!-- Grid View -->
            <div class="hph-gallery-view hph-grid-view <?php echo $default_view === 'grid' ? 'active' : ''; ?>" 
                 data-view-type="grid">
                <div class="hph-grid-container hph-grid-<?php echo esc_attr($images_per_row); ?>">
                    <?php foreach ($images as $index => $image): ?>
                    <div class="hph-gallery-item" 
                         data-category="<?php echo esc_attr(strtolower(str_replace(' ', '-', $image['category']))); ?>"
                         data-index="<?php echo esc_attr($index); ?>">
                        <div class="hph-gallery-item-inner">
                            <img src="<?php echo $lazy_load ? 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 9"%3E%3C/svg%3E' : esc_url($image['url']); ?>" 
                                 <?php if ($lazy_load): ?>
                                 data-src="<?php echo esc_url($image['url']); ?>"
                                 class="hph-lazy"
                                 <?php else: ?>
                                 src="<?php echo esc_url($image['url']); ?>"
                                 <?php endif; ?>
                                 alt="<?php echo esc_attr($image['title']); ?>"
                                 loading="lazy">
                            
                            <div class="hph-gallery-overlay">
                                <div class="hph-overlay-content">
                                    <h4 class="hph-image-title"><?php echo esc_html($image['title']); ?></h4>
                                    <?php if (!empty($image['caption'])): ?>
                                    <p class="hph-image-caption"><?php echo esc_html($image['caption']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="hph-overlay-actions">
                                    <button class="hph-action-btn hph-expand-btn" 
                                            data-index="<?php echo esc_attr($index); ?>"
                                            aria-label="View fullscreen">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                    <?php if ($enable_download): ?>
                                    <a href="<?php echo esc_url($image['url']); ?>" 
                                       download 
                                       class="hph-action-btn hph-download-btn"
                                       aria-label="Download image">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <span class="hph-image-category">
                                <?php echo esc_html($image['category']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Load More Button -->
                <?php if (count($images) > 12): ?>
                <div class="hph-load-more-wrapper">
                    <button class="hph-load-more-btn">
                        <i class="fas fa-plus-circle"></i>
                        Load More Photos
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- List View -->
            <div class="hph-gallery-view hph-list-view <?php echo $default_view === 'list' ? 'active' : ''; ?>" 
                 data-view-type="list">
                <div class="hph-list-container">
                    <?php foreach ($images as $index => $image): ?>
                    <div class="hph-list-item" 
                         data-category="<?php echo esc_attr(strtolower(str_replace(' ', '-', $image['category']))); ?>"
                         data-index="<?php echo esc_attr($index); ?>">
                        <div class="hph-list-image">
                            <img src="<?php echo $lazy_load ? 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 9"%3E%3C/svg%3E' : esc_url($image['url']); ?>"
                                 <?php if ($lazy_load): ?>
                                 data-src="<?php echo esc_url($image['url']); ?>"
                                 class="hph-lazy"
                                 <?php else: ?>
                                 src="<?php echo esc_url($image['url']); ?>"
                                 <?php endif; ?>
                                 alt="<?php echo esc_attr($image['title']); ?>"
                                 loading="lazy">
                        </div>
                        <div class="hph-list-content">
                            <h4 class="hph-list-title"><?php echo esc_html($image['title']); ?></h4>
                            <p class="hph-list-caption"><?php echo esc_html($image['caption']); ?></p>
                            <div class="hph-list-meta">
                                <span class="hph-list-category">
                                    <i class="fas fa-folder"></i>
                                    <?php echo esc_html($image['category']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="hph-list-actions">
                            <button class="hph-action-btn hph-expand-btn" 
                                    data-index="<?php echo esc_attr($index); ?>">
                                <i class="fas fa-expand"></i>
                            </button>
                            <?php if ($enable_download): ?>
                            <a href="<?php echo esc_url($image['url']); ?>" 
                               download 
                               class="hph-action-btn">
                                <i class="fas fa-download"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Slider View -->
            <div class="hph-gallery-view hph-slider-view <?php echo $default_view === 'slider' ? 'active' : ''; ?>" 
                 data-view-type="slider">
                <div class="hph-slider-container">
                    <div class="hph-slider-main">
                        <button class="hph-slider-nav hph-slider-prev" aria-label="Previous image">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        
                        <div class="hph-slider-track">
                            <?php foreach ($images as $index => $image): ?>
                            <div class="hph-slide <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 data-index="<?php echo esc_attr($index); ?>">
                                <img src="<?php echo esc_url($image['url']); ?>" 
                                     alt="<?php echo esc_attr($image['title']); ?>">
                                <div class="hph-slide-info">
                                    <h4><?php echo esc_html($image['title']); ?></h4>
                                    <p><?php echo esc_html($image['caption']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="hph-slider-nav hph-slider-next" aria-label="Next image">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    
                    <!-- Slider Thumbnails -->
                    <div class="hph-slider-thumbs">
                        <div class="hph-thumbs-track">
                            <?php foreach ($images as $index => $image): ?>
                            <button class="hph-thumb <?php echo $index === 0 ? 'active' : ''; ?>" 
                                    data-index="<?php echo esc_attr($index); ?>"
                                    aria-label="View image <?php echo $index + 1; ?>">
                                <img src="<?php echo esc_url($image['url']); ?>" 
                                     alt="<?php echo esc_attr($image['title']); ?>">
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Floor Plans Section -->
        <?php if (!empty($floor_plan_images)): ?>
        <div class="hph-floor-plans" style="display: none;">
            <div class="hph-floor-plans-header">
                <h3 class="hph-floor-plans-title">
                    <i class="fas fa-home"></i>
                    Floor Plans
                </h3>
                <button class="hph-close-floor-plans">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="hph-floor-plans-grid">
                <?php foreach ($floor_plan_images as $index => $plan): ?>
                <div class="hph-floor-plan-item">
                    <img src="<?php echo esc_url($plan['url']); ?>" 
                         alt="<?php echo esc_attr($plan['title'] ?? 'Floor Plan ' . ($index + 1)); ?>">
                    <div class="hph-floor-plan-info">
                        <h4><?php echo esc_html($plan['title'] ?? 'Floor Plan ' . ($index + 1)); ?></h4>
                        <?php if (isset($plan['sqft'])): ?>
                        <span><?php echo esc_html($plan['sqft']); ?> sq ft</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Lightbox Modal -->
    <div class="hph-lightbox" id="hph-gallery-lightbox" aria-hidden="true">
        <div class="hph-lightbox-overlay"></div>
        <div class="hph-lightbox-content">
            <button class="hph-lightbox-close" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="hph-lightbox-image-container">
                <button class="hph-lightbox-nav hph-lightbox-prev" aria-label="Previous">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="hph-lightbox-image">
                    <img src="" alt="">
                    <div class="hph-lightbox-loader">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
                
                <button class="hph-lightbox-nav hph-lightbox-next" aria-label="Next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <div class="hph-lightbox-info">
                <h3 class="hph-lightbox-title"></h3>
                <p class="hph-lightbox-caption"></p>
                <div class="hph-lightbox-meta">
                    <span class="hph-lightbox-counter">
                        <span class="current">1</span> / <span class="total">1</span>
                    </span>
                    <div class="hph-lightbox-actions">
                        <?php if ($enable_fullscreen): ?>
                        <button class="hph-lightbox-fullscreen" aria-label="Fullscreen">
                            <i class="fas fa-expand"></i>
                        </button>
                        <?php endif; ?>
                        <?php if ($enable_download): ?>
                        <a href="#" download class="hph-lightbox-download" aria-label="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Video Modal -->
    <?php if ($video_tour_url): ?>
    <div class="hph-video-modal" id="hph-video-modal" aria-hidden="true">
        <div class="hph-video-overlay"></div>
        <div class="hph-video-content">
            <button class="hph-video-close" aria-label="Close video">
                <i class="fas fa-times"></i>
            </button>
            <div class="hph-video-wrapper">
                <!-- Video iframe will be inserted here -->
            </div>
        </div>
    </div>
    <?php endif; ?>
    
</section>