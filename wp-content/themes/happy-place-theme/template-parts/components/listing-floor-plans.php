<?php
/**
 * Listing Floor Plans Component
 * Interactive floor plans with room details, measurements, and overlays
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - listing_id: int (required for bridge functions)
 */

// Extract listing ID from args or global context
$listing_id = $args['listing_id'] ?? get_the_ID();

// Default arguments
$defaults = array(
    'listing_id' => $listing_id,
    'style' => 'interactive', // interactive, gallery, tabs, comparison
    'show_measurements' => true,
    'show_room_labels' => true,
    'show_square_footage' => true,
    'show_room_details' => true,
    'show_navigation' => true,
    'show_thumbnails' => true,
    'zoom_enabled' => true,
    'full_width' => true,
    'layout' => 'stacked', // stacked, side-by-side, tabs
    'controls' => 'modern' // modern, minimal, classic
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Use bridge functions to get data if listing_id exists
$floor_plans = array();
$total_sqft = '';
$bedrooms = '';
$bathrooms = '';
$listing_title = '';

if ($listing_id) {
    // Get floor plans using bridge function with null safety
    if (function_exists('hpt_get_listing_floor_plans')) {
        $floor_plans = hpt_get_listing_floor_plans($listing_id) ?: array();
    }
    
    // Get property details for context
    if (function_exists('hpt_get_listing_square_feet')) {
        $total_sqft = hpt_get_listing_square_feet($listing_id);
    }
    if (function_exists('hpt_get_listing_bedrooms')) {
        $bedrooms = hpt_get_listing_bedrooms($listing_id);
    }
    if (function_exists('hpt_get_listing_bathrooms')) {
        $bathrooms = hpt_get_listing_bathrooms($listing_id);
    }
    if (function_exists('hpt_get_listing_title')) {
        $listing_title = hpt_get_listing_title($listing_id) ?: get_the_title($listing_id);
    } else {
        $listing_title = get_the_title($listing_id);
    }
}

// Return early if no floor plans
if (empty($floor_plans)) {
    return;
}

extract($config);

// Process floor plans data
$processed_plans = [];
foreach ($floor_plans as $index => $plan) {
    $processed_plans[] = [
        'id' => $plan['id'] ?? 'plan-' . $index,
        'title' => $plan['title'] ?? sprintf(__('Floor Plan %d', 'happy-place-theme'), $index + 1),
        'level' => $plan['level'] ?? sprintf(__('Level %d', 'happy-place-theme'), $index + 1),
        'image_url' => $plan['image_url'] ?? $plan['image'] ?? '',
        'thumbnail_url' => $plan['thumbnail_url'] ?? $plan['thumbnail'] ?? $plan['image_url'] ?? $plan['image'] ?? '',
        'square_footage' => $plan['square_footage'] ?? '',
        'rooms' => $plan['rooms'] ?? [],
        'dimensions' => $plan['dimensions'] ?? [],
        'hotspots' => $plan['hotspots'] ?? [],
        'description' => $plan['description'] ?? '',
        'features' => $plan['features'] ?? []
    ];
}

// Get listing title for fallback
$listing_title = '';
if (function_exists('hpt_get_listing_title')) {
    $listing_title = hpt_get_listing_title($listing_id) ?: get_the_title($listing_id);
} else {
    $listing_title = get_the_title($listing_id);
}

// Generate component classes
$component_classes = [
    'hph-listing-floor-plans',
    'hph-component',
    'hph-floor-plans-style-' . esc_attr($args['style']),
    $args['full_width'] ? 'hph-full-width' : 'hph-contained',
    'hph-floor-plans-layout-' . esc_attr($args['layout']),
    'hph-floor-plans-controls-' . esc_attr($args['controls'])
];

// Generate unique ID for this floor plans instance
$plans_id = 'hph-floor-plans-' . $listing_id . '-' . uniqid();
?>

<div class="<?php echo esc_attr(implode(' ', $component_classes)); ?>" 
     id="<?php echo esc_attr($plans_id); ?>"
     data-listing-id="<?php echo esc_attr($listing_id); ?>"
     data-plans-style="<?php echo esc_attr($args['style']); ?>"
     data-total-plans="<?php echo count($processed_plans); ?>">

    <!-- Floor Plans Header -->
    <div class="hph-floor-plans-header hph-mb-6">
        <div class="hph-flex hph-justify-between hph-align-center hph-mb-4">
            <div class="hph-floor-plans-title-section">
                <h3 class="hph-floor-plans-title hph-h3 hph-mb-2">
                    <?php _e('Floor Plans & Layout', 'happy-place-theme'); ?>
                </h3>
                <p class="hph-floor-plans-subtitle hph-text-muted hph-text-sm">
                    <?php printf(__('Detailed floor plans for %s', 'happy-place-theme'), esc_html($listing_title)); ?>
                </p>
            </div>
            <div class="hph-floor-plans-counter hph-text-sm hph-text-muted">
                <span class="hph-current-plan">1</span> / <span class="hph-total-plans"><?php echo count($processed_plans); ?></span>
            </div>
        </div>

        <!-- Property Summary -->
        <?php if ($total_sqft || $bedrooms || $bathrooms): ?>
        <div class="hph-property-summary hph-flex hph-flex-wrap hph-gap-4 hph-p-4 hph-bg-gray-50 hph-rounded-lg hph-mb-4">
            <?php if ($total_sqft): ?>
            <div class="hph-summary-item hph-flex hph-items-center hph-gap-2">
                <svg class="hph-w-5 hph-h-5 hph-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                </svg>
                <span class="hph-text-sm hph-font-medium"><?php echo esc_html($total_sqft); ?> <?php _e('sq ft', 'happy-place-theme'); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($bedrooms): ?>
            <div class="hph-summary-item hph-flex hph-items-center hph-gap-2">
                <svg class="hph-w-5 hph-h-5 hph-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10v11M20 10v11"></path>
                </svg>
                <span class="hph-text-sm hph-font-medium"><?php echo esc_html($bedrooms); ?> <?php _e('Bedrooms', 'happy-place-theme'); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($bathrooms): ?>
            <div class="hph-summary-item hph-flex hph-items-center hph-gap-2">
                <svg class="hph-w-5 hph-h-5 hph-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10v11M20 10v11"></path>
                </svg>
                <span class="hph-text-sm hph-font-medium"><?php echo esc_html($bathrooms); ?> <?php _e('Bathrooms', 'happy-place-theme'); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($args['style'] === 'tabs'): ?>
    <!-- Tabbed Floor Plans Interface -->
    <div class="hph-floor-plans-tabs-container">
        <!-- Tab Navigation -->
        <div class="hph-floor-plans-tabs hph-flex hph-flex-wrap hph-gap-2 hph-mb-6 hph-border-b hph-border-gray-200">
            <?php foreach ($processed_plans as $index => $plan): ?>
            <button class="hph-floor-plan-tab hph-px-4 hph-py-3 hph-text-sm hph-font-medium hph-rounded-t-lg hph-border-b-2 hph-transition-all <?php echo $index === 0 ? 'hph-border-primary hph-text-primary hph-bg-primary-10' : 'hph-border-transparent hph-text-gray-600 hph-hover:text-primary hph-hover:border-gray-300'; ?>"
                    data-plan-index="<?php echo $index; ?>">
                <div class="hph-flex hph-items-center hph-gap-2">
                    <svg class="hph-w-4 hph-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                    </svg>
                    <div class="hph-text-left">
                        <div class="hph-font-medium"><?php echo esc_html($plan['title']); ?></div>
                        <?php if ($plan['square_footage']): ?>
                        <div class="hph-text-xs hph-text-gray-500"><?php echo esc_html($plan['square_footage']); ?> sq ft</div>
                        <?php endif; ?>
                    </div>
                </div>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Tab Content -->
        <div class="hph-floor-plans-tab-content">
            <?php foreach ($processed_plans as $index => $plan): ?>
            <div class="hph-floor-plan-panel <?php echo $index === 0 ? 'hph-active' : 'hph-hidden'; ?>"
                 data-plan-index="<?php echo $index; ?>">
                <?php echo $this->render_floor_plan_content($plan, $args); ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php else: ?>
    <!-- Interactive/Gallery Floor Plans Interface -->
    <div class="hph-floor-plans-main-container">
        <!-- Main Floor Plan Display -->
        <div class="hph-floor-plan-display hph-relative hph-rounded-lg hph-overflow-hidden hph-shadow-lg hph-bg-white">
            <?php foreach ($processed_plans as $index => $plan): ?>
            <div class="hph-floor-plan-item <?php echo $index === 0 ? 'hph-active' : 'hph-hidden'; ?> hph-relative"
                 data-plan-index="<?php echo $index; ?>"
                 data-plan-id="<?php echo esc_attr($plan['id']); ?>">
                
                <!-- Floor Plan Image Container -->
                <div class="hph-floor-plan-image-container hph-relative hph-group">
                    <?php if ($args['zoom_enabled']): ?>
                    <div class="hph-floor-plan-zoom-container hph-relative hph-overflow-hidden" style="cursor: zoom-in;">
                        <img class="hph-floor-plan-image hph-w-full hph-h-auto hph-block hph-transition-transform"
                             src="<?php echo esc_url($plan['image_url']); ?>"
                             alt="<?php echo esc_attr($plan['title']); ?>"
                             data-zoom-enabled="true">
                    </div>
                    <?php else: ?>
                    <img class="hph-floor-plan-image hph-w-full hph-h-auto hph-block"
                         src="<?php echo esc_url($plan['image_url']); ?>"
                         alt="<?php echo esc_attr($plan['title']); ?>">
                    <?php endif; ?>

                    <!-- Room Hotspots -->
                    <?php if ($args['show_room_labels'] && !empty($plan['hotspots'])): ?>
                    <?php foreach ($plan['hotspots'] as $hotspot_index => $hotspot): ?>
                    <div class="hph-room-hotspot hph-absolute hph-w-8 hph-h-8 hph-bg-primary hph-rounded-full hph-border-2 hph-border-white hph-shadow-lg hph-cursor-pointer hph-flex hph-items-center hph-justify-center hph-text-white hph-text-xs hph-font-bold hph-hover:scale-110 hph-transition-transform"
                         style="left: <?php echo esc_attr($hotspot['x'] ?? '50'); ?>%; top: <?php echo esc_attr($hotspot['y'] ?? '50'); ?>%;"
                         data-hotspot-index="<?php echo $hotspot_index; ?>"
                         data-room-info="<?php echo esc_attr(wp_json_encode($hotspot)); ?>"
                         title="<?php echo esc_attr($hotspot['room_name'] ?? ''); ?>">
                        <?php echo $hotspot_index + 1; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Measurement Lines -->
                    <?php if ($args['show_measurements'] && !empty($plan['dimensions'])): ?>
                    <?php foreach ($plan['dimensions'] as $dimension): ?>
                    <div class="hph-dimension-line hph-absolute hph-pointer-events-none"
                         style="left: <?php echo esc_attr($dimension['x1'] ?? '0'); ?>%; top: <?php echo esc_attr($dimension['y1'] ?? '0'); ?>%; width: <?php echo esc_attr($dimension['width'] ?? '10'); ?>%; height: <?php echo esc_attr($dimension['height'] ?? '2'); ?>px;">
                        <div class="hph-dimension-value hph-absolute hph-bg-black hph-text-white hph-text-xs hph-px-2 hph-py-1 hph-rounded hph-whitespace-nowrap"
                             style="top: -24px; left: 50%; transform: translateX(-50%);">
                            <?php echo esc_html($dimension['value'] ?? ''); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Zoom Controls -->
                    <?php if ($args['zoom_enabled']): ?>
                    <div class="hph-zoom-controls hph-absolute hph-top-4 hph-right-4 hph-bg-white hph-rounded-lg hph-shadow-md hph-p-2 hph-flex hph-flex-col hph-gap-1 hph-opacity-0 hph-group-hover:opacity-100 hph-transition-opacity">
                        <button class="hph-zoom-in hph-p-2 hph-text-gray-600 hph-hover:text-primary hph-transition-colors" title="<?php esc_attr_e('Zoom In', 'happy-place-theme'); ?>">
                            <svg class="hph-w-4 hph-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                            </svg>
                        </button>
                        <button class="hph-zoom-out hph-p-2 hph-text-gray-600 hph-hover:text-primary hph-transition-colors" title="<?php esc_attr_e('Zoom Out', 'happy-place-theme'); ?>">
                            <svg class="hph-w-4 hph-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                            </svg>
                        </button>
                        <button class="hph-zoom-reset hph-p-2 hph-text-gray-600 hph-hover:text-primary hph-transition-colors" title="<?php esc_attr_e('Reset Zoom', 'happy-place-theme'); ?>">
                            <svg class="hph-w-4 hph-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Floor Plan Info -->
                <div class="hph-floor-plan-info hph-p-6 hph-bg-white hph-border-t">
                    <div class="hph-flex hph-justify-between hph-align-center hph-mb-4">
                        <div>
                            <h4 class="hph-floor-plan-title hph-h4 hph-mb-1"><?php echo esc_html($plan['title']); ?></h4>
                            <p class="hph-floor-plan-level hph-text-sm hph-text-muted hph-mb-0"><?php echo esc_html($plan['level']); ?></p>
                        </div>
                        <?php if ($args['show_square_footage'] && $plan['square_footage']): ?>
                        <div class="hph-floor-plan-sqft hph-text-right">
                            <div class="hph-text-lg hph-font-bold hph-text-primary"><?php echo esc_html($plan['square_footage']); ?></div>
                            <div class="hph-text-sm hph-text-muted"><?php _e('Square Feet', 'happy-place-theme'); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($plan['description']): ?>
                    <p class="hph-floor-plan-description hph-text-sm hph-text-gray-600 hph-mb-4"><?php echo esc_html($plan['description']); ?></p>
                    <?php endif; ?>

                    <!-- Room List -->
                    <?php if ($args['show_room_details'] && !empty($plan['rooms'])): ?>
                    <div class="hph-room-list hph-grid hph-grid-cols-1 hph-md:grid-cols-2 hph-lg:grid-cols-3 hph-gap-4">
                        <?php foreach ($plan['rooms'] as $room): ?>
                        <div class="hph-room-item hph-p-3 hph-bg-gray-50 hph-rounded-lg">
                            <div class="hph-room-name hph-font-medium hph-text-sm hph-mb-1"><?php echo esc_html($room['name'] ?? ''); ?></div>
                            <div class="hph-room-details hph-text-xs hph-text-muted">
                                <?php if ($room['dimensions']): ?>
                                <div><?php echo esc_html($room['dimensions']); ?></div>
                                <?php endif; ?>
                                <?php if ($room['square_feet']): ?>
                                <div><?php echo esc_html($room['square_feet']); ?> sq ft</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Features -->
                    <?php if (!empty($plan['features'])): ?>
                    <div class="hph-floor-plan-features hph-mt-4">
                        <h5 class="hph-text-sm hph-font-medium hph-mb-2"><?php _e('Features:', 'happy-place-theme'); ?></h5>
                        <div class="hph-flex hph-flex-wrap hph-gap-2">
                            <?php foreach ($plan['features'] as $feature): ?>
                            <span class="hph-feature-tag hph-bg-primary-10 hph-text-primary hph-text-xs hph-px-2 hph-py-1 hph-rounded"><?php echo esc_html($feature); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Navigation Controls -->
            <?php if ($args['show_navigation'] && count($processed_plans) > 1): ?>
            <button class="hph-floor-plan-nav hph-floor-plan-prev hph-absolute hph-left-4 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-bg-black-60 hph-text-white hph-rounded-full hph-p-3 hph-hover:bg-black-80 hph-transition-colors hph-z-10">
                <svg class="hph-w-6 hph-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button class="hph-floor-plan-nav hph-floor-plan-next hph-absolute hph-right-4 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-bg-black-60 hph-text-white hph-rounded-full hph-p-3 hph-hover:bg-black-80 hph-transition-colors hph-z-10">
                <svg class="hph-w-6 hph-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            <?php endif; ?>
        </div>

        <!-- Floor Plan Thumbnails -->
        <?php if ($args['show_thumbnails'] && count($processed_plans) > 1): ?>
        <div class="hph-floor-plan-thumbnails hph-mt-6 hph-flex hph-gap-4 hph-overflow-x-auto hph-pb-2">
            <?php foreach ($processed_plans as $index => $plan): ?>
            <button class="hph-floor-plan-thumbnail hph-flex-shrink-0 hph-relative hph-group hph-rounded-lg hph-overflow-hidden hph-border-2 hph-transition-all <?php echo $index === 0 ? 'hph-border-primary' : 'hph-border-transparent hph-hover:border-gray-300'; ?>"
                    data-plan-index="<?php echo $index; ?>">
                <div class="hph-w-32 hph-h-24 hph-relative">
                    <img class="hph-w-full hph-h-full hph-object-cover"
                         src="<?php echo esc_url($plan['thumbnail_url']); ?>"
                         alt="<?php echo esc_attr($plan['title']); ?>">
                    <div class="hph-absolute hph-inset-0 hph-bg-gradient-to-t hph-from-black-60 hph-to-transparent hph-opacity-0 hph-group-hover:opacity-100 hph-transition-opacity"></div>
                </div>
                <div class="hph-absolute hph-bottom-0 hph-left-0 hph-right-0 hph-p-2">
                    <p class="hph-text-white hph-text-xs hph-font-medium hph-mb-0 hph-truncate"><?php echo esc_html($plan['title']); ?></p>
                    <?php if ($plan['square_footage']): ?>
                    <p class="hph-text-white-70 hph-text-xs hph-mb-0"><?php echo esc_html($plan['square_footage']); ?> sq ft</p>
                    <?php endif; ?>
                </div>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Room Details Modal -->
    <div class="hph-room-modal hph-fixed hph-inset-0 hph-bg-black-50 hph-z-50 hph-hidden hph-flex hph-items-center hph-justify-center hph-p-4">
        <div class="hph-room-modal-content hph-bg-white hph-rounded-lg hph-p-6 hph-max-w-lg hph-w-full hph-relative">
            <button class="hph-room-modal-close hph-absolute hph-top-4 hph-right-4 hph-text-gray-400 hph-hover:text-gray-600">
                <svg class="hph-w-6 hph-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="hph-room-info-content">
                <!-- Content populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Hidden data for JavaScript -->
    <script type="application/json" class="hph-floor-plans-data">
    <?php echo wp_json_encode([
        'plans' => $processed_plans,
        'settings' => $args,
        'listing_title' => $listing_title,
        'property_info' => [
            'total_sqft' => $total_sqft,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms
        ]
    ]); ?>
    </script>
</div>