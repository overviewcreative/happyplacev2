<?php
/**
 * Archive Filters Component - Advanced Filtering Panel
 * 
 * Comprehensive filtering system for archive pages with support for
 * multiple filter types including categories, tags, custom taxonomies,
 * price ranges, and property-specific filters.
 * 
 * @package HappyPlaceTheme
 * @version 3.0.0
 * 
 * === Configuration Options ===
 * 
 * Core Settings:
 * - post_type: string - Post type being filtered
 * - filters: array - Array of filter configurations
 * - active_filters: array - Currently active filter values
 * 
 * Display Options:
 * - layout: string - 'horizontal', 'sidebar', 'dropdown'
 * - show_title: bool - Display filters title
 * - show_clear_all: bool - Display clear all filters button
 * - collapsible: bool - Make filter groups collapsible
 * - initially_collapsed: bool - Start with filters collapsed
 * 
 * Filter Types Available:
 * - taxonomy: Categories, tags, custom taxonomies
 * - price_range: Min/max price inputs or slider
 * - date_range: Date picker for date filtering
 * - checkbox_group: Multiple checkbox options
 * - radio_group: Single selection options
 * - select: Dropdown selection
 * - range_slider: Numeric range slider
 * - text_input: Text search within results
 * 
 * Style Options:
 * - container_classes: array - Additional container classes
 * - background: string - Background style
 * - padding: string - Padding size
 * 
 * Advanced Features:
 * - ajax_enabled: bool - Enable AJAX filtering
 * - show_count: bool - Show result count for each filter
 * - logical_operator: string - 'AND' or 'OR' for multiple filters
 * - save_filters: bool - Remember filter selections
 */

// Parse arguments with defaults
$args = wp_parse_args($args ?? [], [
    // Core Settings
    'post_type' => get_post_type() ?: 'post',
    'filters' => [],
    'active_filters' => $_GET ?? [],
    
    // Display Options
    'layout' => 'horizontal',
    'show_title' => true,
    'show_clear_all' => true,
    'collapsible' => true,
    'initially_collapsed' => false,
    
    // Style Options
    'container_classes' => [],
    'background' => 'gray',
    'padding' => 'lg',
    
    // Advanced Features
    'ajax_enabled' => false,
    'show_count' => true,
    'logical_operator' => 'AND',
    'save_filters' => false,
]);

// Define default filters based on post type
if (empty($args['filters'])) {
    switch ($args['post_type']) {
        case 'listing':
            $args['filters'] = [
                [
                    'type' => 'price_range',
                    'id' => 'price',
                    'label' => __('Price Range', 'happy-place-theme'),
                    'min' => 0,
                    'max' => 5000000,
                    'step' => 10000,
                    'format' => '$%s'
                ],
                [
                    'type' => 'checkbox_group',
                    'id' => 'property_type',
                    'label' => __('Property Type', 'happy-place-theme'),
                    'options' => [
                        'single-family' => __('Single Family', 'happy-place-theme'),
                        'condo' => __('Condo', 'happy-place-theme'),
                        'townhouse' => __('Townhouse', 'happy-place-theme'),
                        'multi-family' => __('Multi-Family', 'happy-place-theme'),
                        'land' => __('Land', 'happy-place-theme'),
                    ]
                ],
                [
                    'type' => 'range_slider',
                    'id' => 'bedrooms',
                    'label' => __('Bedrooms', 'happy-place-theme'),
                    'min' => 0,
                    'max' => 10,
                    'step' => 1,
                    'suffix' => '+'
                ],
                [
                    'type' => 'range_slider',
                    'id' => 'bathrooms',
                    'label' => __('Bathrooms', 'happy-place-theme'),
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.5,
                    'suffix' => '+'
                ],
                [
                    'type' => 'checkbox_group',
                    'id' => 'features',
                    'label' => __('Features', 'happy-place-theme'),
                    'options' => [
                        'pool' => __('Pool', 'happy-place-theme'),
                        'garage' => __('Garage', 'happy-place-theme'),
                        'fireplace' => __('Fireplace', 'happy-place-theme'),
                        'basement' => __('Basement', 'happy-place-theme'),
                        'waterfront' => __('Waterfront', 'happy-place-theme'),
                    ]
                ],
                [
                    'type' => 'taxonomy',
                    'id' => 'listing_area',
                    'label' => __('Location', 'happy-place-theme'),
                    'taxonomy' => 'listing_area',
                    'multiple' => true
                ]
            ];
            break;
            
        case 'agent':
            $args['filters'] = [
                [
                    'type' => 'checkbox_group',
                    'id' => 'specialties',
                    'label' => __('Specialties', 'happy-place-theme'),
                    'options' => [
                        'buyer-agent' => __('Buyer\'s Agent', 'happy-place-theme'),
                        'listing-agent' => __('Listing Agent', 'happy-place-theme'),
                        'relocation' => __('Relocation Specialist', 'happy-place-theme'),
                        'foreclosure' => __('Foreclosure Specialist', 'happy-place-theme'),
                        'consulting' => __('Consulting', 'happy-place-theme'),
                    ]
                ],
                [
                    'type' => 'range_slider',
                    'id' => 'experience',
                    'label' => __('Years of Experience', 'happy-place-theme'),
                    'min' => 0,
                    'max' => 30,
                    'step' => 1,
                    'suffix' => '+ years'
                ],
                [
                    'type' => 'checkbox_group',
                    'id' => 'languages',
                    'label' => __('Languages', 'happy-place-theme'),
                    'options' => [
                        'english' => __('English', 'happy-place-theme'),
                        'spanish' => __('Spanish', 'happy-place-theme'),
                        'french' => __('French', 'happy-place-theme'),
                        'chinese' => __('Chinese', 'happy-place-theme'),
                        'other' => __('Other', 'happy-place-theme'),
                    ]
                ],
                [
                    'type' => 'radio_group',
                    'id' => 'rating',
                    'label' => __('Minimum Rating', 'happy-place-theme'),
                    'options' => [
                        '5' => __('5 Stars', 'happy-place-theme'),
                        '4' => __('4+ Stars', 'happy-place-theme'),
                        '3' => __('3+ Stars', 'happy-place-theme'),
                        'any' => __('Any Rating', 'happy-place-theme'),
                    ]
                ]
            ];
            break;
            
        default:
            // Generic post filters
            $args['filters'] = [
                [
                    'type' => 'taxonomy',
                    'id' => 'category',
                    'label' => __('Categories', 'happy-place-theme'),
                    'taxonomy' => 'category',
                    'multiple' => true
                ],
                [
                    'type' => 'taxonomy',
                    'id' => 'tags',
                    'label' => __('Tags', 'happy-place-theme'),
                    'taxonomy' => 'post_tag',
                    'multiple' => true
                ],
                [
                    'type' => 'date_range',
                    'id' => 'date',
                    'label' => __('Date Range', 'happy-place-theme'),
                ]
            ];
    }
}

// Build container classes
$container_classes = [
    'hph-archive-filters',
    'hph-archive-filters--' . $args['layout']
];

// Add background classes
switch ($args['background']) {
    case 'white':
        $container_classes[] = 'hph-bg-white';
        break;
    case 'gray':
        $container_classes[] = 'hph-bg-gray-50';
        break;
    case 'transparent':
        break;
}

// Add padding classes
$container_classes[] = 'hph-p-' . $args['padding'];

// Add border
$container_classes[] = 'hph-border-b';
$container_classes[] = 'hph-border-gray-200';

// Add initially collapsed state
if ($args['initially_collapsed']) {
    $container_classes[] = 'hph-hidden';
}

// Add custom classes
if (!empty($args['container_classes'])) {
    $container_classes = array_merge($container_classes, $args['container_classes']);
}

// Check if we have active filters
$has_active_filters = false;
foreach ($args['active_filters'] as $key => $value) {
    if (!empty($value) && $key !== 'paged' && $key !== 'view' && $key !== 'sort') {
        $has_active_filters = true;
        break;
    }
}

?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" 
     data-filters-panel="true"
     data-ajax="<?php echo $args['ajax_enabled'] ? 'true' : 'false'; ?>"
     <?php if ($args['initially_collapsed']) echo 'aria-hidden="true"'; ?>>
    
    <div class="hph-container">
        
        <?php if ($args['show_title'] || $args['show_clear_all']) : ?>
        <div class="hph-flex hph-items-center hph-justify-between hph-mb-md">
            <?php if ($args['show_title']) : ?>
            <h3 class="hph-text-lg hph-font-semibold hph-text-gray-900">
                <?php esc_html_e('Filter Results', 'happy-place-theme'); ?>
                <?php if ($has_active_filters) : ?>
                    <span class="hph-badge hph-badge--primary hph-ml-2">
                        <?php esc_html_e('Active', 'happy-place-theme'); ?>
                    </span>
                <?php endif; ?>
            </h3>
            <?php endif; ?>
            
            <?php if ($args['show_clear_all'] && $has_active_filters) : ?>
            <button type="button" class="hph-btn hph-btn-sm hph-btn-outline" data-clear-filters>
                <i class="fas fa-times"></i>
                <span><?php esc_html_e('Clear All', 'happy-place-theme'); ?></span>
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <form class="hph-filters-form" method="get" action="">
            <?php if ($args['post_type'] !== 'post') : ?>
                <input type="hidden" name="post_type" value="<?php echo esc_attr($args['post_type']); ?>">
            <?php endif; ?>
            
            <?php
            // Preserve other query parameters
            foreach (['view', 'sort', 'per_page'] as $param) {
                if (isset($_GET[$param])) {
                    echo '<input type="hidden" name="' . esc_attr($param) . '" value="' . esc_attr($_GET[$param]) . '">';
                }
            }
            ?>
            
            <div class="<?php echo $args['layout'] === 'horizontal' ? 'hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 xl:hph-grid-cols-6 hph-gap-md' : 'hph-space-y-lg'; ?>">
                
                <?php foreach ($args['filters'] as $filter) : ?>
                    <?php
                    $filter_id = 'filter_' . $filter['id'];
                    $filter_value = $args['active_filters'][$filter['id']] ?? null;
                    ?>
                    
                    <div class="hph-filter-group" data-filter="<?php echo esc_attr($filter['id']); ?>">
                        
                        <?php if (!empty($filter['label'])) : ?>
                        <label class="hph-filter-label hph-block hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-2">
                            <?php echo esc_html($filter['label']); ?>
                        </label>
                        <?php endif; ?>
                        
                        <?php switch ($filter['type']) :
                            
                            case 'price_range': ?>
                                <div class="hph-flex hph-gap-sm">
                                    <div class="hph-flex-1">
                                        <input 
                                            type="number" 
                                            name="<?php echo esc_attr($filter['id']); ?>_min"
                                            value="<?php echo esc_attr($filter_value['min'] ?? ''); ?>"
                                            placeholder="<?php esc_attr_e('Min', 'happy-place-theme'); ?>"
                                            class="hph-form-control hph-form-control-sm"
                                            min="<?php echo esc_attr($filter['min']); ?>"
                                            max="<?php echo esc_attr($filter['max']); ?>"
                                            step="<?php echo esc_attr($filter['step']); ?>"
                                        >
                                    </div>
                                    <div class="hph-flex-1">
                                        <input 
                                            type="number" 
                                            name="<?php echo esc_attr($filter['id']); ?>_max"
                                            value="<?php echo esc_attr($filter_value['max'] ?? ''); ?>"
                                            placeholder="<?php esc_attr_e('Max', 'happy-place-theme'); ?>"
                                            class="hph-form-control hph-form-control-sm"
                                            min="<?php echo esc_attr($filter['min']); ?>"
                                            max="<?php echo esc_attr($filter['max']); ?>"
                                            step="<?php echo esc_attr($filter['step']); ?>"
                                        >
                                    </div>
                                </div>
                                <?php break;
                                
                            case 'checkbox_group': ?>
                                <div class="hph-space-y-2 hph-max-h-48 hph-overflow-y-auto">
                                    <?php foreach ($filter['options'] as $value => $label) : ?>
                                        <?php
                                        $checked = is_array($filter_value) ? in_array($value, $filter_value) : $filter_value === $value;
                                        ?>
                                        <label class="hph-flex hph-items-center hph-cursor-pointer">
                                            <input 
                                                type="checkbox" 
                                                name="<?php echo esc_attr($filter['id']); ?>[]"
                                                value="<?php echo esc_attr($value); ?>"
                                                <?php checked($checked); ?>
                                                class="hph-form-checkbox hph-mr-2"
                                            >
                                            <span class="hph-text-sm hph-text-gray-700"><?php echo esc_html($label); ?></span>
                                            <?php if ($args['show_count']) : ?>
                                                <span class="hph-ml-auto hph-text-xs hph-text-gray-500">(0)</span>
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <?php break;
                                
                            case 'radio_group': ?>
                                <div class="hph-space-y-2">
                                    <?php foreach ($filter['options'] as $value => $label) : ?>
                                        <label class="hph-flex hph-items-center hph-cursor-pointer">
                                            <input 
                                                type="radio" 
                                                name="<?php echo esc_attr($filter['id']); ?>"
                                                value="<?php echo esc_attr($value); ?>"
                                                <?php checked($filter_value, $value); ?>
                                                class="hph-form-radio hph-mr-2"
                                            >
                                            <span class="hph-text-sm hph-text-gray-700"><?php echo esc_html($label); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <?php break;
                                
                            case 'range_slider': ?>
                                <div class="hph-range-slider" data-min="<?php echo esc_attr($filter['min']); ?>" data-max="<?php echo esc_attr($filter['max']); ?>" data-step="<?php echo esc_attr($filter['step']); ?>">
                                    <div class="hph-flex hph-items-center hph-gap-sm">
                                        <input 
                                            type="range" 
                                            name="<?php echo esc_attr($filter['id']); ?>"
                                            value="<?php echo esc_attr($filter_value ?: $filter['min']); ?>"
                                            min="<?php echo esc_attr($filter['min']); ?>"
                                            max="<?php echo esc_attr($filter['max']); ?>"
                                            step="<?php echo esc_attr($filter['step']); ?>"
                                            class="hph-flex-1"
                                        >
                                        <span class="hph-text-sm hph-font-medium hph-text-gray-900 hph-min-w-12">
                                            <span data-range-value><?php echo esc_html($filter_value ?: $filter['min']); ?></span><?php echo esc_html($filter['suffix'] ?? ''); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php break;
                                
                            case 'select': ?>
                                <select 
                                    name="<?php echo esc_attr($filter['id']); ?>"
                                    class="hph-form-select hph-form-select-sm hph-w-full"
                                >
                                    <option value=""><?php esc_html_e('All', 'happy-place-theme'); ?></option>
                                    <?php foreach ($filter['options'] as $value => $label) : ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($filter_value, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php break;
                                
                            case 'taxonomy': ?>
                                <?php
                                $terms = get_terms([
                                    'taxonomy' => $filter['taxonomy'],
                                    'hide_empty' => true,
                                ]);
                                
                                if (!is_wp_error($terms) && !empty($terms)) :
                                ?>
                                    <?php if ($filter['multiple'] ?? false) : ?>
                                        <div class="hph-space-y-2 hph-max-h-48 hph-overflow-y-auto">
                                            <?php foreach ($terms as $term) : ?>
                                                <?php
                                                $checked = is_array($filter_value) ? in_array($term->slug, $filter_value) : $filter_value === $term->slug;
                                                ?>
                                                <label class="hph-flex hph-items-center hph-cursor-pointer">
                                                    <input 
                                                        type="checkbox" 
                                                        name="<?php echo esc_attr($filter['id']); ?>[]"
                                                        value="<?php echo esc_attr($term->slug); ?>"
                                                        <?php checked($checked); ?>
                                                        class="hph-form-checkbox hph-mr-2"
                                                    >
                                                    <span class="hph-text-sm hph-text-gray-700"><?php echo esc_html($term->name); ?></span>
                                                    <?php if ($args['show_count']) : ?>
                                                        <span class="hph-ml-auto hph-text-xs hph-text-gray-500">(<?php echo esc_html($term->count); ?>)</span>
                                                    <?php endif; ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else : ?>
                                        <select 
                                            name="<?php echo esc_attr($filter['id']); ?>"
                                            class="hph-form-select hph-form-select-sm hph-w-full"
                                        >
                                            <option value=""><?php esc_html_e('All', 'happy-place-theme'); ?></option>
                                            <?php foreach ($terms as $term) : ?>
                                                <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($filter_value, $term->slug); ?>>
                                                    <?php echo esc_html($term->name); ?>
                                                    <?php if ($args['show_count']) : ?>
                                                        (<?php echo esc_html($term->count); ?>)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php break;
                                
                            case 'date_range': ?>
                                <div class="hph-flex hph-gap-sm">
                                    <div class="hph-flex-1">
                                        <input 
                                            type="date" 
                                            name="<?php echo esc_attr($filter['id']); ?>_from"
                                            value="<?php echo esc_attr($filter_value['from'] ?? ''); ?>"
                                            class="hph-form-control hph-form-control-sm"
                                        >
                                    </div>
                                    <div class="hph-flex-1">
                                        <input 
                                            type="date" 
                                            name="<?php echo esc_attr($filter['id']); ?>_to"
                                            value="<?php echo esc_attr($filter_value['to'] ?? ''); ?>"
                                            class="hph-form-control hph-form-control-sm"
                                        >
                                    </div>
                                </div>
                                <?php break;
                                
                        endswitch; ?>
                        
                    </div>
                <?php endforeach; ?>
                
            </div>
            
            <?php if (!$args['ajax_enabled']) : ?>
            <div class="hph-mt-lg hph-flex hph-gap-sm">
                <button type="submit" class="hph-btn hph-btn-primary">
                    <i class="fas fa-filter"></i>
                    <span><?php esc_html_e('Apply Filters', 'happy-place-theme'); ?></span>
                </button>
                
                <?php if ($has_active_filters) : ?>
                <a href="<?php echo esc_url(remove_query_arg(array_keys($args['active_filters']))); ?>" class="hph-btn hph-btn-outline">
                    <i class="fas fa-times"></i>
                    <span><?php esc_html_e('Reset', 'happy-place-theme'); ?></span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </form>
        
    </div>
</div>

<?php if ($args['ajax_enabled']) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtersPanel = document.querySelector('[data-filters-panel]');
    if (!filtersPanel) return;
    
    const form = filtersPanel.querySelector('.hph-filters-form');
    const inputs = form.querySelectorAll('input, select');
    
    // Auto-submit on change if AJAX enabled
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            if (filtersPanel.dataset.ajax === 'true') {
                const formData = new FormData(form);
                window.dispatchEvent(new CustomEvent('filters-changed', {
                    detail: { filters: Object.fromEntries(formData) }
                }));
            }
        });
    });
    
    // Range slider value display
    const rangeSliders = filtersPanel.querySelectorAll('input[type="range"]');
    rangeSliders.forEach(slider => {
        const valueDisplay = slider.closest('.hph-range-slider').querySelector('[data-range-value]');
        slider.addEventListener('input', function() {
            valueDisplay.textContent = this.value;
        });
    });
    
    // Clear all filters
    const clearBtn = filtersPanel.querySelector('[data-clear-filters]');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            form.reset();
            if (filtersPanel.dataset.ajax === 'true') {
                window.dispatchEvent(new CustomEvent('filters-cleared'));
            } else {
                window.location = window.location.pathname + '?post_type=' + form.post_type.value;
            }
        });
    }
    
    // Toggle panel visibility
    window.addEventListener('toggle-filters', function() {
        filtersPanel.classList.toggle('hph-hidden');
        filtersPanel.setAttribute('aria-hidden', filtersPanel.classList.contains('hph-hidden'));
    });
});
</script>
<?php endif; ?>