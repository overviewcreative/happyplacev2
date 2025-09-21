<?php
/**
 * Listing Features Component
 * Displays property features in organized categories
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get component args (WordPress standard way)
$args = get_query_var('args', []);
$args = wp_parse_args($args, [
    'features' => null,
    'listing_id' => null,
    'style' => 'grid',
    'show_categories' => true
]);

// Validate required data
if (!$args['features'] && !$args['listing_id']) {
    return;
}

// If no features passed, get from listing ID
if (!$args['features'] && $args['listing_id']) {
    $listing_data = hpt_get_listing($args['listing_id']);
    $features = [
        'interior' => $listing_data['interior_features'] ?? [],
        'exterior' => $listing_data['exterior_features'] ?? [],
        'property' => $listing_data['property_features'] ?? []
    ];
} else {
    $features = $args['features'];
}

// Feature category labels
$feature_categories = [
    'interior' => __('Interior Features', 'happy-place-theme'),
    'exterior' => __('Exterior Features', 'happy-place-theme'),
    'property' => __('Property Features', 'happy-place-theme'),
    'amenities' => __('Amenities', 'happy-place-theme'),
    'utilities' => __('Utilities & Systems', 'happy-place-theme'),
    'other' => __('Additional Features', 'happy-place-theme')
];

// Helper function to format feature names
function format_feature_name($feature) {
    if (is_array($feature) && isset($feature['name'])) {
        return $feature['name'];
    }
    return ucfirst(str_replace(['_', '-'], ' ', $feature));
}

$has_features = false;
foreach ($features as $category => $feature_list) {
    if (!empty($feature_list)) {
        $has_features = true;
        break;
    }
}

if (!$has_features) {
    return;
}
?>

<div class="hph-listing-features hph-listing-features--<?php echo esc_attr($args['style']); ?>">
    
    <div class="hph-card">
        <div class="hph-card__header">
            <h3 class="hph-card__title">
                <?php _e('Property Features & Amenities', 'happy-place-theme'); ?>
            </h3>
        </div>
        
        <div class="hph-card__content">
            
            <?php foreach ($features as $category => $feature_list): ?>
                <?php if (!empty($feature_list)): ?>
                
                <div class="hph-feature-category hph-mb-6 last:hph-mb-0">
                    
                    <?php if ($args['show_categories']): ?>
                    <h4 class="hph-feature-category__title hph-text-lg hph-font-semibold hph-mb-4 hph-pb-2 hph-border-b hph-border-gray-200">
                        <?php echo esc_html($feature_categories[$category] ?? ucfirst($category)); ?>
                    </h4>
                    <?php endif; ?>
                    
                    <div class="hph-feature-list hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 hph-gap-3">
                        
                        <?php foreach ($feature_list as $feature): ?>
                        <div class="hph-feature-item hph-flex hph-items-center hph-gap-2 hph-py-2">
                            <i class="hph-icon hph-icon-check hph-text-green-500 hph-flex-shrink-0" aria-hidden="true"></i>
                            <span class="hph-feature-name hph-text-gray-700">
                                <?php echo esc_html(format_feature_name($feature)); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        
                    </div>
                    
                </div>
                
                <?php endif; ?>
            <?php endforeach; ?>
            
        </div>
    </div>
    
</div>

<style>
.hph-feature-category + .hph-feature-category {
    border-top: 1px solid #e5e7eb;
    padding-top: 1.5rem;
}

.hph-feature-item {
    border-radius: 0.375rem;
    transition: background-color 0.15s ease-in-out;
}

.hph-feature-item:hover {
    background-color: #f9fafb;
}

.hph-feature-name {
    line-height: 1.25;
}

@media (max-width: 768px) {
    .hph-feature-list {
        grid-template-columns: 1fr;
    }
}
</style>
