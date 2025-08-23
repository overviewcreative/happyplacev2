<?php
/**
 * Listing Virtual Tour Component
 * Full-width virtual tour gallery with 360° views, video tours, and interactive hotspots
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - listing_id: int (required for bridge functions)
 */

// Extract listing ID from args or global context
$listing_id = $args['listing_id'] ?? get_the_ID();

// Return early if no listing ID
if (!$listing_id) {
    return;
}

// Default arguments
$defaults = array(
    'listing_id' => $listing_id,
    'style' => 'immersive', // immersive, gallery, tabs, fullscreen
    'show_navigation' => true,
    'show_minimap' => true,
    'show_room_labels' => true,
    'show_hotspots' => true,
    'autoplay' => false,
    'full_width' => true,
    'height' => 'viewport', // viewport, fixed, auto
    'controls' => 'modern', // modern, minimal, classic
    'quality' => 'high' // high, medium, low
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Get virtual tour data using bridge functions with null safety
$virtual_tours = array();
$video_tours = array();
$tour_360_images = array();
$listing_title = '';

if ($listing_id) {
    if (function_exists('hpt_get_listing_virtual_tours')) {
        $virtual_tours = hpt_get_listing_virtual_tours($listing_id) ?: array();
    }
    
    if (function_exists('hpt_get_listing_video_tours')) {
        $video_tours = hpt_get_listing_video_tours($listing_id) ?: array();
    }
    
    if (function_exists('hpt_get_listing_360_images')) {
        $tour_360_images = hpt_get_listing_360_images($listing_id) ?: array();
    }
    
    if (function_exists('hpt_get_listing_title')) {
        $listing_title = hpt_get_listing_title($listing_id) ?: get_the_title($listing_id);
    } else {
        $listing_title = get_the_title($listing_id);
    }
}

// Combine all tour types
$all_tours = array();

// Add 360° images
foreach ($tour_360_images as $image) {
    $all_tours[] = array(
        'type' => '360_image',
        'title' => $image['title'] ?? __('360° View', 'happy-place-theme'),
        'url' => $image['url'] ?? '',
        'thumbnail' => $image['thumbnail'] ?? $image['url'] ?? '',
        'room' => $image['room'] ?? __('Living Space', 'happy-place-theme'),
        'hotspots' => $image['hotspots'] ?? array()
    );
}

// Add video tours
foreach ($video_tours as $video) {
    $all_tours[] = array(
        'type' => 'video',
        'title' => $video['title'] ?? __('Video Tour', 'happy-place-theme'),
        'url' => $video['url'] ?? '',
        'thumbnail' => $video['thumbnail'] ?? '',
        'poster' => $video['poster'] ?? '',
        'duration' => $video['duration'] ?? '',
        'room' => $video['room'] ?? __('Property Tour', 'happy-place-theme')
    );
}

// Add virtual tours (Matterport, etc.)
foreach ($virtual_tours as $tour) {
    $all_tours[] = array(
        'type' => 'virtual_tour',
        'title' => $tour['title'] ?? __('Virtual Tour', 'happy-place-theme'),
        'url' => $tour['url'] ?? '',
        'embed_code' => $tour['embed_code'] ?? '',
        'thumbnail' => $tour['thumbnail'] ?? '',
        'provider' => $tour['provider'] ?? 'custom',
        'room' => $tour['room'] ?? __('Full Property', 'happy-place-theme')
    );
}

// Return early if no tours available
if (empty($all_tours)) {
    return;
}

extract($config);

// Get listing title for fallback
$listing_title = '';
if (function_exists('hpt_get_listing_title')) {
    $listing_title = hpt_get_listing_title($listing_id) ?: get_the_title($listing_id);
} else {
    $listing_title = get_the_title($listing_id);
}

// Generate component classes
$component_classes = [
    'hph-listing-virtual-tour',
    'hph-component',
    'hph-tour-style-' . esc_attr($args['style']),
    $args['full_width'] ? 'hph-full-width' : 'hph-contained',
    'hph-tour-height-' . esc_attr($args['height']),
    'hph-tour-controls-' . esc_attr($args['controls'])
];

// Generate unique ID for this tour instance
$tour_id = 'hph-virtual-tour-' . $listing_id . '-' . uniqid();
?>

<div class="<?php echo esc_attr(implode(' ', $component_classes)); ?>" 
     id="<?php echo esc_attr($tour_id); ?>"
     data-listing-id="<?php echo esc_attr($listing_id); ?>"
     data-tour-style="<?php echo esc_attr($args['style']); ?>"
     data-total-tours="<?php echo count($all_tours); ?>">

    <!-- Tour Header -->
    <div class="hph-tour-header hph-flex hph-justify-between hph-align-center hph-mb-6">
        <div class="hph-tour-title-section">
            <h3 class="hph-tour-title hph-h3 hph-mb-2">
                <?php _e('Virtual Tour & Media', 'happy-place-theme'); ?>
            </h3>
            <p class="hph-tour-subtitle hph-text-muted hph-text-sm">
                <?php printf(__('Explore %s with interactive tours and media', 'happy-place-theme'), esc_html($listing_title)); ?>
            </p>
        </div>
        <div class="hph-tour-counter hph-text-sm hph-text-muted">
            <span class="hph-current-tour">1</span> / <span class="hph-total-tours"><?php echo count($all_tours); ?></span>
        </div>
    </div>

    <?php if ($args['style'] === 'tabs'): ?>
    <!-- Tabbed Tour Interface -->
    <div class="hph-tour-tabs-container">
        <!-- Tab Navigation -->
        <div class="hph-tour-tabs hph-flex hph-flex-wrap hph-gap-2 hph-mb-6 hph-border-b hph-border-gray-200">
            <?php foreach ($all_tours as $index => $tour): ?>
            <button class="hph-tour-tab hph-px-4 hph-py-2 hph-text-sm hph-font-medium hph-rounded-t-lg hph-border-b-2 hph-transition-all <?php echo $index === 0 ? 'hph-border-primary hph-text-primary hph-bg-primary-10' : 'hph-border-transparent hph-text-gray-600 hph-hover:text-primary hph-hover:border-gray-300'; ?>"
                    data-tour-index="<?php echo $index; ?>"
                    data-tour-type="<?php echo esc_attr($tour['type']); ?>">
                <div class="hph-flex hph-items-center hph-gap-2">
                    <?php if ($tour['type'] === '360_image'): ?>
                    <svg class="hph-w-4 hph-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                    </svg>
                    <?php elseif ($tour['type'] === 'video'): ?>
                    <svg class="hph-w-4 hph-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M3 3l18 18M15 3H9v12"></path>
                    </svg>
                    <?php else: ?>
                    <svg class="hph-w-4 hph-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <?php endif; ?>
                    <span><?php echo esc_html($tour['title']); ?></span>
                </div>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Tab Content -->
        <div class="hph-tour-tab-content">
            <?php foreach ($all_tours as $index => $tour): ?>
            <div class="hph-tour-panel <?php echo $index === 0 ? 'hph-active' : 'hph-hidden'; ?>"
                 data-tour-index="<?php echo $index; ?>">
                <?php echo $this->render_tour_content($tour, $args); ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php else: ?>
    <!-- Immersive/Gallery Tour Interface -->
    <div class="hph-tour-main-container hph-relative">
        <!-- Main Tour Display -->
        <div class="hph-tour-display hph-relative hph-rounded-lg hph-overflow-hidden hph-shadow-2xl <?php echo $args['height'] === 'viewport' ? 'hph-h-screen hph-max-h-800' : 'hph-h-96'; ?>">
            <?php foreach ($all_tours as $index => $tour): ?>
            <div class="hph-tour-item <?php echo $index === 0 ? 'hph-active' : 'hph-hidden'; ?> hph-w-full hph-h-full hph-relative"
                 data-tour-index="<?php echo $index; ?>"
                 data-tour-type="<?php echo esc_attr($tour['type']); ?>">
                
                <?php if ($tour['type'] === '360_image'): ?>
                <!-- 360° Image Viewer -->
                <div class="hph-360-viewer hph-w-full hph-h-full hph-relative hph-bg-black">
                    <canvas class="hph-360-canvas hph-w-full hph-h-full" 
                            data-image-url="<?php echo esc_url($tour['url']); ?>"></canvas>
                    
                    <!-- 360° Controls -->
                    <div class="hph-360-controls hph-absolute hph-bottom-4 hph-left-1/2 hph-transform hph--translate-x-1/2 hph-bg-black-60 hph-rounded-full hph-p-2 hph-flex hph-gap-2">
                        <button class="hph-360-reset hph-p-2 hph-text-white hph-hover:text-primary hph-transition-colors" title="<?php esc_attr_e('Reset View', 'happy-place-theme'); ?>">
                            <svg class="hph-w-5 hph-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                        <button class="hph-360-fullscreen hph-p-2 hph-text-white hph-hover:text-primary hph-transition-colors" title="<?php esc_attr_e('Fullscreen', 'happy-place-theme'); ?>">
                            <svg class="hph-w-5 hph-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Hotspots -->
                    <?php if ($args['show_hotspots'] && !empty($tour['hotspots'])): ?>
                    <?php foreach ($tour['hotspots'] as $hotspot): ?>
                    <div class="hph-360-hotspot hph-absolute hph-w-6 hph-h-6 hph-bg-primary hph-rounded-full hph-border-2 hph-border-white hph-shadow-lg hph-cursor-pointer hph-animate-pulse"
                         style="left: <?php echo esc_attr($hotspot['x'] ?? '50'); ?>%; top: <?php echo esc_attr($hotspot['y'] ?? '50'); ?>%;"
                         data-hotspot-info="<?php echo esc_attr(wp_json_encode($hotspot)); ?>"
                         title="<?php echo esc_attr($hotspot['title'] ?? ''); ?>">
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php elseif ($tour['type'] === 'video'): ?>
                <!-- Video Tour -->
                <div class="hph-video-tour hph-w-full hph-h-full hph-relative">
                    <video class="hph-tour-video hph-w-full hph-h-full hph-object-cover"
                           controls
                           <?php echo $args['autoplay'] ? 'autoplay muted' : ''; ?>
                           <?php if ($tour['poster']): ?>poster="<?php echo esc_url($tour['poster']); ?>"<?php endif; ?>>
                        <source src="<?php echo esc_url($tour['url']); ?>" type="video/mp4">
                        <?php _e('Your browser does not support the video tag.', 'happy-place-theme'); ?>
                    </video>
                    
                    <?php if ($tour['duration']): ?>
                    <div class="hph-video-duration hph-absolute hph-top-4 hph-right-4 hph-bg-black-60 hph-text-white hph-px-2 hph-py-1 hph-rounded hph-text-sm">
                        <?php echo esc_html($tour['duration']); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php else: ?>
                <!-- Virtual Tour Embed -->
                <div class="hph-virtual-tour-embed hph-w-full hph-h-full hph-relative">
                    <?php if ($tour['embed_code']): ?>
                        <?php echo wp_kses($tour['embed_code'], [
                            'iframe' => [
                                'src' => [],
                                'width' => [],
                                'height' => [],
                                'frameborder' => [],
                                'allowfullscreen' => [],
                                'class' => [],
                                'style' => []
                            ]
                        ]); ?>
                    <?php else: ?>
                        <iframe src="<?php echo esc_url($tour['url']); ?>"
                                width="100%"
                                height="100%"
                                frameborder="0"
                                allowfullscreen
                                class="hph-tour-iframe">
                        </iframe>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Tour Info Overlay -->
                <?php if ($args['show_room_labels']): ?>
                <div class="hph-tour-info hph-absolute hph-top-4 hph-left-4 hph-bg-black-60 hph-text-white hph-px-3 hph-py-2 hph-rounded-lg">
                    <h4 class="hph-tour-room hph-font-medium hph-text-sm hph-mb-0"><?php echo esc_html($tour['room']); ?></h4>
                    <p class="hph-tour-type hph-text-xs hph-text-white-70 hph-mb-0"><?php echo esc_html($tour['title']); ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <!-- Navigation Controls -->
            <?php if ($args['show_navigation'] && count($all_tours) > 1): ?>
            <button class="hph-tour-nav hph-tour-prev hph-absolute hph-left-4 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-bg-black-60 hph-text-white hph-rounded-full hph-p-3 hph-hover:bg-black-80 hph-transition-colors">
                <svg class="hph-w-6 hph-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button class="hph-tour-nav hph-tour-next hph-absolute hph-right-4 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-bg-black-60 hph-text-white hph-rounded-full hph-p-3 hph-hover:bg-black-80 hph-transition-colors">
                <svg class="hph-w-6 hph-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            <?php endif; ?>
        </div>

        <!-- Tour Thumbnails -->
        <?php if (count($all_tours) > 1): ?>
        <div class="hph-tour-thumbnails hph-mt-6 hph-flex hph-gap-4 hph-overflow-x-auto hph-pb-2">
            <?php foreach ($all_tours as $index => $tour): ?>
            <button class="hph-tour-thumbnail hph-flex-shrink-0 hph-relative hph-group hph-rounded-lg hph-overflow-hidden hph-border-2 hph-transition-all <?php echo $index === 0 ? 'hph-border-primary' : 'hph-border-transparent hph-hover:border-gray-300'; ?>"
                    data-tour-index="<?php echo $index; ?>">
                <div class="hph-w-24 hph-h-16 hph-relative">
                    <?php if ($tour['thumbnail']): ?>
                    <img class="hph-w-full hph-h-full hph-object-cover"
                         src="<?php echo esc_url($tour['thumbnail']); ?>"
                         alt="<?php echo esc_attr($tour['title']); ?>">
                    <?php else: ?>
                    <div class="hph-w-full hph-h-full hph-bg-gray-200 hph-flex hph-items-center hph-justify-center">
                        <?php if ($tour['type'] === '360_image'): ?>
                        <svg class="hph-w-6 hph-h-6 hph-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                        </svg>
                        <?php elseif ($tour['type'] === 'video'): ?>
                        <svg class="hph-w-6 hph-h-6 hph-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M3 3l18 18M15 3H9v12"></path>
                        </svg>
                        <?php else: ?>
                        <svg class="hph-w-6 hph-h-6 hph-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Type Indicator -->
                    <div class="hph-absolute hph-top-1 hph-right-1 hph-bg-black-60 hph-text-white hph-text-xs hph-px-1 hph-rounded">
                        <?php if ($tour['type'] === '360_image'): ?>360°
                        <?php elseif ($tour['type'] === 'video'): ?>Video
                        <?php else: ?>VR<?php endif; ?>
                    </div>
                </div>
                <div class="hph-absolute hph-bottom-0 hph-left-0 hph-right-0 hph-bg-gradient-to-t hph-from-black-60 hph-to-transparent hph-p-2">
                    <p class="hph-text-white hph-text-xs hph-font-medium hph-mb-0 hph-truncate"><?php echo esc_html($tour['room']); ?></p>
                </div>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Minimap (for 360° tours) -->
    <?php if ($args['show_minimap']): ?>
    <div class="hph-tour-minimap hph-absolute hph-bottom-4 hph-right-4 hph-w-32 hph-h-24 hph-bg-white hph-rounded-lg hph-shadow-lg hph-border hph-overflow-hidden hph-hidden">
        <div class="hph-minimap-content hph-w-full hph-h-full hph-relative">
            <!-- Minimap will be populated by JavaScript -->
        </div>
    </div>
    <?php endif; ?>

    <!-- Hotspot Info Modal -->
    <div class="hph-hotspot-modal hph-fixed hph-inset-0 hph-bg-black-50 hph-z-50 hph-hidden hph-flex hph-items-center hph-justify-center hph-p-4">
        <div class="hph-hotspot-content hph-bg-white hph-rounded-lg hph-p-6 hph-max-w-md hph-w-full hph-relative">
            <button class="hph-hotspot-close hph-absolute hph-top-4 hph-right-4 hph-text-gray-400 hph-hover:text-gray-600">
                <svg class="hph-w-6 hph-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="hph-hotspot-info-content">
                <!-- Content populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Hidden data for JavaScript -->
    <script type="application/json" class="hph-tour-data">
    <?php echo wp_json_encode([
        'tours' => $all_tours,
        'settings' => $args,
        'listing_title' => $listing_title
    ]); ?>
    </script>
</div>