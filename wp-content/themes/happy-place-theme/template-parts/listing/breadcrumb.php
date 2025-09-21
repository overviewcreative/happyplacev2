<?php
/**
 * Breadcrumb Navigation Template Part
 * File: template-parts/listing/breadcrumb.php
 * 
 * Displays hierarchical navigation using HPH framework utilities and bridge functions
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get listing data for breadcrumb using bridge functions with fallbacks
$listing_address = null;
if (function_exists('hpt_get_listing_address')) {
    try {
        $listing_address = hpt_get_listing_address($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_address failed: ' . $e->getMessage());
    }
}

$property_type = null;
if (function_exists('hpt_get_listing_property_type')) {
    try {
        $property_type = hpt_get_listing_property_type($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_property_type failed: ' . $e->getMessage());
    }
}

$property_title = get_the_title($listing_id);

// Extract city and state from address with fallbacks
if ($listing_address) {
    $city = $listing_address['city'] ?? null;
    $state = $listing_address['state'] ?? null;
} else {
    // Fallback to direct field access
    $city = get_field('city', $listing_id);
    $state = get_field('state', $listing_id);
}

// Fallback for property type
if (!$property_type) {
    $property_type = get_field('property_type', $listing_id);
}

// Build breadcrumb items
$breadcrumb_items = [
    [
        'label' => 'Home',
        'url' => home_url('/'),
        'icon' => 'fa-home'
    ],
    [
        'label' => 'Properties',
        'url' => home_url('/listings/')
    ]
];

// Add state if available
if ($state) {
    $breadcrumb_items[] = [
        'label' => $state,
        'url' => home_url('/listings/?state=' . urlencode($state))
    ];
}

// Add city if available
if ($city) {
    $breadcrumb_items[] = [
        'label' => $city,
        'url' => home_url('/listings/?city=' . urlencode($city))
    ];
}

// Add property type if available
if ($property_type) {
    $breadcrumb_items[] = [
        'label' => $property_type,
        'url' => home_url('/listings/?property_type=' . urlencode($property_type))
    ];
}

// Current page (no URL)
$breadcrumb_items[] = [
    'label' => $property_title,
    'current' => true
];
?>

<nav class="hph-breadcrumb hph-py-md hph-bg-gray-50" aria-label="Breadcrumb">
    <div class="hph-container">
        <ol class="hph-breadcrumb__list hph-flex hph-flex-wrap hph-items-center gap-responsive text-responsive-sm">
            
            <?php foreach ($breadcrumb_items as $index => $item) : ?>
            <li class="hph-breadcrumb__item hph-flex hph-items-center">
                
                <?php if (!empty($item['current'])) : ?>
                <!-- Current Page -->
                <span class="hph-breadcrumb__current hph-text-gray-700 hph-font-medium" aria-current="page">
                    <span class="hide-mobile"><?php echo esc_html($item['label']); ?></span>
                    <span class="show-mobile"><?php echo esc_html(wp_trim_words($item['label'], 3)); ?></span>
                </span>
                
                <?php else : ?>
                <!-- Link -->
                <a href="<?php echo esc_url($item['url']); ?>" 
                   class="hph-breadcrumb__link hph-text-gray-600 hover:hph-text-primary hph-transition-colors hph-flex hph-items-center gap-responsive">
                    <?php if (!empty($item['icon'])) : ?>
                    <i class="fas <?php echo esc_attr($item['icon']); ?>"></i>
                    <?php endif; ?>
                    <?php echo esc_html($item['label']); ?>
                </a>
                <?php endif; ?>
                
                <?php if ($index < count($breadcrumb_items) - 1) : ?>
                <!-- Separator -->
                <span class="hph-breadcrumb__separator hph-mx-xs hph-text-gray-400" aria-hidden="true">
                    <i class="fas fa-chevron-right hph-text-xs"></i>
                </span>
                <?php endif; ?>
                
            </li>
            <?php endforeach; ?>
            
        </ol>
    </div>
</nav>
