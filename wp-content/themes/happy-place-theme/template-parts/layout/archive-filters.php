<?php
/**
 * Base Archive Filters - Active filters display with remove functionality
 *
 * @package HappyPlaceTheme
 */

$filters_args = wp_parse_args($args ?? [], [
    'container_class' => 'hph-container',
    'show_active_filters' => true,
    'show_clear_all' => true,
    'filter_labels' => [
        'status' => __('Status', 'happy-place-theme'),
        'property_type' => __('Property Type', 'happy-place-theme'),
        'min_price' => __('Min Price', 'happy-place-theme'),
        'max_price' => __('Max Price', 'happy-place-theme'),
        'beds' => __('Bedrooms', 'happy-place-theme'),
        'baths' => __('Bathrooms', 'happy-place-theme'),
        'min_sqft' => __('Min Size', 'happy-place-theme'),
        'max_sqft' => __('Max Size', 'happy-place-theme'),
        'city' => __('City', 'happy-place-theme'),
        'neighborhood' => __('Neighborhood', 'happy-place-theme'),
        'features' => __('Features', 'happy-place-theme'),
        'keywords' => __('Keywords', 'happy-place-theme'),
        'search' => __('Search', 'happy-place-theme'),
        'specialty' => __('Specialty', 'happy-place-theme'),
        'languages' => __('Languages', 'happy-place-theme')
    ]
]);

// Get active filters from URL parameters
$active_filters = [];
$filter_params = [
    'status', 'property_type', 'min_price', 'max_price', 'beds', 'baths', 
    'min_sqft', 'max_sqft', 'city', 'neighborhood', 'features', 'keywords',
    'search', 'specialty', 'languages', 's'
];

foreach ($filter_params as $param) {
    $value = isset($_GET[$param]) ? $_GET[$param] : '';
    if (!empty($value)) {
        // Handle search parameter mapping
        if ($param === 's') {
            $param = 'search';
        }
        
        $active_filters[$param] = $value;
    }
}

// Don't show if no active filters
if (empty($active_filters) || !$filters_args['show_active_filters']) {
    return;
}
?>

<section class="hph-archive-filters">
    <div class="<?php echo esc_attr($filters_args['container_class']); ?>">
        <div class="hph-active-filters">
            
            <span class="hph-active-filters__label">
                <?php _e('Active Filters:', 'happy-place-theme'); ?>
            </span>
            
            <div class="hph-active-filters__list">
                
                <?php foreach ($active_filters as $key => $value) : ?>
                    <?php
                    $filter_label = $filters_args['filter_labels'][$key] ?? ucwords(str_replace('_', ' ', $key));
                    $filter_value = $value;
                    
                    // Format specific filter values
                    if (in_array($key, ['min_price', 'max_price']) && is_numeric($value)) {
                        $filter_value = '$' . number_format($value);
                    } elseif (in_array($key, ['min_sqft', 'max_sqft']) && is_numeric($value)) {
                        $filter_value = number_format($value) . ' sq ft';
                    } elseif ($key === 'beds' && is_numeric($value)) {
                        $filter_value = $value . '+ bed' . ($value > 1 ? 's' : '');
                    } elseif ($key === 'baths' && is_numeric($value)) {
                        $filter_value = $value . '+ bath' . ($value > 1 ? 's' : '');
                    } elseif (is_array($value)) {
                        $filter_value = implode(', ', array_map('ucfirst', $value));
                    } else {
                        $filter_value = ucfirst(str_replace(['_', '-'], ' ', $value));
                    }
                    
                    // Map URL parameter back for removal
                    $url_param = ($key === 'search') ? 's' : $key;
                    ?>
                    
                    <span class="hph-filter-tag" data-filter="<?php echo esc_attr($url_param); ?>">
                        <span class="hph-filter-tag__label"><?php echo esc_html($filter_label); ?>:</span>
                        <span class="hph-filter-tag__value"><?php echo esc_html($filter_value); ?></span>
                        <button class="hph-filter-tag__remove" 
                                data-param="<?php echo esc_attr($url_param); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Remove %s filter', 'happy-place-theme'), $filter_label)); ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                    
                <?php endforeach; ?>
                
                <?php if ($filters_args['show_clear_all']) : ?>
                    <button class="hph-btn hph-btn--ghost hph-btn--sm hph-clear-all-filters">
                        <i class="fas fa-times-circle"></i>
                        <?php _e('Clear All', 'happy-place-theme'); ?>
                    </button>
                <?php endif; ?>
                
            </div>
            
        </div>
    </div>
</section>