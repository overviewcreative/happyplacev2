<?php
/**
 * Advanced Filters Component
 * 
 * Intelligent filtering interface with natural language processing,
 * smart suggestions, and progressive disclosure of advanced options
 * 
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

// Parse component arguments
$filter_args = wp_parse_args($args ?? [], [
    'post_type' => 'listing',
    'show_nlp_input' => true,
    'show_suggestions' => true,
    'show_advanced' => true,
    'show_saved_searches' => is_user_logged_in(),
    'layout' => 'horizontal', // horizontal, vertical, sidebar
    'style' => 'modern',
    'ajax_enabled' => true,
    'current_filters' => [],
    'search_query' => '',
    'placeholder_text' => __('Try "3 bedroom house under $500k" or "waterfront condo downtown"', 'happy-place-theme')
]);

// Initialize services
$nlp_service = hph_get_search_nlp();
$suggestions_service = hph_get_search_suggestions();

// Get current URL parameters
$current_params = array_merge($_GET, $filter_args['current_filters']);

// Determine filter configuration based on post type
$filter_config = [
    'listing' => [
        'primary_filters' => ['price_range', 'bedrooms', 'bathrooms', 'property_type'],
        'secondary_filters' => ['status', 'square_feet', 'lot_size', 'year_built'],
        'feature_filters' => ['waterfront', 'pool', 'garage', 'fireplace', 'basement']
    ],
    'agent' => [
        'primary_filters' => ['specialty', 'experience', 'language', 'office'],
        'secondary_filters' => ['rating', 'active_listings', 'recent_sales'],
        'feature_filters' => ['luxury_specialist', 'first_time_buyers', 'relocation']
    ],
    'city' => [
        'primary_filters' => ['state', 'population', 'median_income'],
        'secondary_filters' => ['school_rating', 'crime_rate', 'climate'],
        'feature_filters' => ['coastal', 'college_town', 'retirement_friendly']
    ],
    'community' => [
        'primary_filters' => ['city', 'price_range', 'home_type'],
        'secondary_filters' => ['hoa_fees', 'age_restricted', 'gated'],
        'feature_filters' => ['golf_course', 'waterfront', 'new_construction']
    ]
];

$current_config = $filter_config[$filter_args['post_type']] ?? $filter_config['listing'];

// Generate unique component ID
$component_id = 'hph-advanced-filters-' . uniqid();
?>

<div id="<?php echo esc_attr($component_id); ?>" class="hph-advanced-filters hph-advanced-filters--<?php echo esc_attr($filter_args['layout']); ?> hph-advanced-filters--<?php echo esc_attr($filter_args['style']); ?>">
    
    <!-- Natural Language Processing Input -->
    <?php if ($filter_args['show_nlp_input']) : ?>
        <div class="hph-nlp-search-container">
            <div class="hph-nlp-input-wrapper hph-relative">
                <input 
                    type="text" 
                    id="hph-nlp-search" 
                    class="hph-nlp-search-input hph-w-full hph-px-4 hph-py-3 hph-text-lg hph-border hph-border-gray-300 hph-rounded-lg focus:hph-ring-2 focus:hph-ring-primary-500 focus:hph-border-primary-500"
                    placeholder="<?php echo esc_attr($filter_args['placeholder_text']); ?>"
                    value="<?php echo esc_attr($filter_args['search_query']); ?>"
                    autocomplete="off"
                    data-post-type="<?php echo esc_attr($filter_args['post_type']); ?>"
                    <?php echo $filter_args['ajax_enabled'] ? 'data-ajax="true"' : ''; ?>
                >
                
                <!-- NLP Processing Indicator -->
                <div class="hph-nlp-processing hph-hidden hph-absolute hph-right-3 hph-top-1/2 hph-transform hph--translate-y-1/2">
                    <div class="hph-spinner hph-w-5 hph-h-5 hph-border-2 hph-border-primary-200 hph-border-t-primary-600 hph-rounded-full hph-animate-spin"></div>
                </div>
                
                <!-- Suggestions Dropdown -->
                <div class="hph-nlp-suggestions hph-hidden hph-absolute hph-top-full hph-left-0 hph-right-0 hph-mt-2 hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-shadow-lg hph-z-50 hph-max-h-96 hph-overflow-y-auto">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
            
            <!-- Parsed Query Display -->
            <div class="hph-parsed-query hph-hidden hph-mt-3 hph-p-3 hph-bg-blue-50 hph-border hph-border-blue-200 hph-rounded-lg">
                <div class="hph-flex hph-items-start hph-gap-3">
                    <div class="hph-flex-shrink-0 hph-mt-1">
                        <span class="hph-inline-block hph-w-2 hph-h-2 hph-bg-blue-500 hph-rounded-full"></span>
                    </div>
                    <div class="hph-flex-1">
                        <p class="hph-text-sm hph-text-blue-800 hph-mb-2">
                            <strong><?php _e('We understood:', 'happy-place-theme'); ?></strong>
                        </p>
                        <div class="hph-parsed-filters hph-flex hph-flex-wrap hph-gap-2">
                            <!-- Populated by JavaScript -->
                        </div>
                        <button type="button" class="hph-apply-parsed hph-mt-2 hph-text-sm hph-text-blue-600 hover:hph-text-blue-800 hph-underline">
                            <?php _e('Apply these filters', 'happy-place-theme'); ?>
                        </button>
                    </div>
                    <button type="button" class="hph-close-parsed hph-text-blue-400 hover:hph-text-blue-600">
                        <span class="hph-sr-only"><?php _e('Close', 'happy-place-theme'); ?></span>
                        <svg class="hph-w-4 hph-h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Filter Form -->
    <form class="hph-filters-form" method="get" action="">
        
        <!-- Hidden fields for preserving state -->
        <input type="hidden" name="post_type" value="<?php echo esc_attr($filter_args['post_type']); ?>">
        <?php if (!empty($current_params['view'])) : ?>
            <input type="hidden" name="view" value="<?php echo esc_attr($current_params['view']); ?>">
        <?php endif; ?>
        <?php if (!empty($current_params['sort'])) : ?>
            <input type="hidden" name="sort" value="<?php echo esc_attr($current_params['sort']); ?>">
        <?php endif; ?>
        
        <!-- Primary Filters (Always Visible) -->
        <div class="hph-primary-filters">
            <div class="hph-filters-grid hph-grid hph-gap-4 <?php echo $filter_args['layout'] === 'horizontal' ? 'hph-grid-cols-2 md:hph-grid-cols-4' : 'hph-grid-cols-1'; ?>">
                
                <?php foreach ($current_config['primary_filters'] as $filter_type) : ?>
                    <?php echo $this->render_filter_field($filter_type, $filter_args['post_type'], $current_params); ?>
                <?php endforeach; ?>
                
            </div>
        </div>
        
        <!-- Advanced Filters Toggle -->
        <?php if ($filter_args['show_advanced']) : ?>
            <div class="hph-advanced-toggle hph-mt-4">
                <button type="button" class="hph-advanced-toggle-btn hph-flex hph-items-center hph-gap-2 hph-text-sm hph-text-gray-600 hover:hph-text-gray-900">
                    <svg class="hph-w-4 hph-h-4 hph-transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <span><?php _e('More Filters', 'happy-place-theme'); ?></span>
                    <span class="hph-active-filters-count hph-hidden hph-inline-flex hph-items-center hph-justify-center hph-px-2 hph-py-1 hph-text-xs hph-font-medium hph-text-white hph-bg-primary-600 hph-rounded-full"></span>
                </button>
            </div>
            
            <!-- Advanced Filters Panel -->
            <div class="hph-advanced-filters-panel hph-hidden hph-mt-4 hph-p-4 hph-bg-gray-50 hph-border hph-rounded-lg">
                
                <!-- Secondary Filters -->
                <div class="hph-secondary-filters hph-mb-6">
                    <h4 class="hph-text-sm hph-font-medium hph-text-gray-900 hph-mb-3">
                        <?php _e('Additional Criteria', 'happy-place-theme'); ?>
                    </h4>
                    <div class="hph-filters-grid hph-grid hph-gap-4 hph-grid-cols-1 md:hph-grid-cols-3">
                        <?php foreach ($current_config['secondary_filters'] as $filter_type) : ?>
                            <?php echo $this->render_filter_field($filter_type, $filter_args['post_type'], $current_params); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Feature Filters -->
                <div class="hph-feature-filters">
                    <h4 class="hph-text-sm hph-font-medium hph-text-gray-900 hph-mb-3">
                        <?php _e('Features & Amenities', 'happy-place-theme'); ?>
                    </h4>
                    <div class="hph-feature-checkboxes hph-grid hph-gap-3 hph-grid-cols-2 md:hph-grid-cols-3">
                        <?php foreach ($current_config['feature_filters'] as $feature) : ?>
                            <?php echo $this->render_checkbox_filter($feature, $current_params); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
            </div>
        <?php endif; ?>
        
        <!-- Filter Actions -->
        <div class="hph-filter-actions hph-flex hph-items-center hph-justify-between hph-mt-6">
            <div class="hph-filter-actions-left">
                <!-- Clear Filters -->
                <button type="button" class="hph-clear-filters hph-text-sm hph-text-gray-500 hover:hph-text-gray-700 hph-underline">
                    <?php _e('Clear All Filters', 'happy-place-theme'); ?>
                </button>
            </div>
            
            <div class="hph-filter-actions-right hph-flex hph-items-center hph-gap-3">
                
                <!-- Save Search (if logged in) -->
                <?php if ($filter_args['show_saved_searches']) : ?>
                    <button type="button" class="hph-save-search hph-text-sm hph-text-primary-600 hover:hph-text-primary-700 hph-flex hph-items-center hph-gap-1">
                        <svg class="hph-w-4 hph-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <?php _e('Save Search', 'happy-place-theme'); ?>
                    </button>
                <?php endif; ?>
                
                <!-- Apply Filters -->
                <button type="submit" class="hph-apply-filters hph-px-6 hph-py-2 hph-bg-primary-600 hph-text-white hph-text-sm hph-font-medium hph-rounded-md hover:hph-bg-primary-700 focus:hph-outline-none focus:hph-ring-2 focus:hph-ring-primary-500">
                    <?php _e('Apply Filters', 'happy-place-theme'); ?>
                </button>
            </div>
        </div>
        
    </form>
    
    <!-- Active Filters Display -->
    <div class="hph-active-filters hph-hidden hph-mt-4">
        <div class="hph-flex hph-items-center hph-gap-2 hph-flex-wrap">
            <span class="hph-text-sm hph-text-gray-600"><?php _e('Active filters:', 'happy-place-theme'); ?></span>
            <div class="hph-active-filter-tags hph-flex hph-flex-wrap hph-gap-2">
                <!-- Populated by JavaScript -->
            </div>
        </div>
    </div>
    
    <!-- Smart Suggestions -->
    <?php if ($filter_args['show_suggestions']) : ?>
        <div class="hph-smart-suggestions hph-hidden hph-mt-4 hph-p-3 hph-bg-yellow-50 hph-border hph-border-yellow-200 hph-rounded-lg">
            <div class="hph-flex hph-items-start hph-gap-3">
                <div class="hph-flex-shrink-0 hph-mt-1">
                    <svg class="hph-w-4 hph-h-4 hph-text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="hph-flex-1">
                    <p class="hph-text-sm hph-text-yellow-800 hph-mb-2">
                        <strong><?php _e('Suggestion:', 'happy-place-theme'); ?></strong>
                    </p>
                    <div class="hph-suggestion-content">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
                <button type="button" class="hph-close-suggestions hph-text-yellow-400 hover:hph-text-yellow-600">
                    <span class="hph-sr-only"><?php _e('Close', 'happy-place-theme'); ?></span>
                    <svg class="hph-w-4 hph-h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<?php
// Add inline CSS for better component styling
if (!wp_style_is('hph-advanced-filters', 'enqueued')) :
?>
<style>
/* Advanced Filters Component Styles */
.hph-advanced-filters {
    --hph-primary-50: #eff6ff;
    --hph-primary-100: #dbeafe;
    --hph-primary-200: #bfdbfe;
    --hph-primary-500: #3b82f6;
    --hph-primary-600: #2563eb;
    --hph-primary-700: #1d4ed8;
}

.hph-nlp-search-input {
    transition: all 0.15s ease-in-out;
    background-image: url("data:image/svg+xml,%3csvg width='20' height='20' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M9 9a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm7 4l-4-4m0 0l-4-4' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 40px;
}

.hph-nlp-suggestions {
    max-height: 400px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.hph-suggestion-item {
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
}

.hph-suggestion-item:hover {
    background-color: #f9fafb;
}

.hph-suggestion-item:last-child {
    border-bottom: none;
}

.hph-advanced-toggle-btn svg {
    transition: transform 0.2s ease;
}

.hph-advanced-toggle-btn.active svg {
    transform: rotate(180deg);
}

.hph-filter-field {
    transition: all 0.15s ease-in-out;
}

.hph-filter-field:focus-within {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.hph-checkbox-filter {
    transition: all 0.15s ease-in-out;
}

.hph-checkbox-filter:hover {
    background-color: #f9fafb;
}

.hph-active-filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background-color: var(--hph-primary-100);
    color: var(--hph-primary-700);
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.hph-active-filter-tag button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    margin-left: 4px;
    background: none;
    border: none;
    color: var(--hph-primary-600);
    cursor: pointer;
    border-radius: 2px;
}

.hph-active-filter-tag button:hover {
    background-color: var(--hph-primary-200);
}

/* Animation utilities */
.hph-animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.hph-animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hph-filters-grid {
        grid-template-columns: 1fr !important;
    }
    
    .hph-filter-actions {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    
    .hph-filter-actions-right {
        justify-content: space-between;
        width: 100%;
    }
}
</style>
<?php endif; ?>

<script>
// Initialize Advanced Filters Component
document.addEventListener('DOMContentLoaded', function() {
    const componentId = '<?php echo esc_js($component_id); ?>';
    const ajaxEnabled = <?php echo $filter_args['ajax_enabled'] ? 'true' : 'false'; ?>;
    const postType = '<?php echo esc_js($filter_args['post_type']); ?>';
    
    if (typeof HPHAdvancedFilters !== 'undefined') {
        new HPHAdvancedFilters(componentId, {
            ajaxEnabled: ajaxEnabled,
            postType: postType,
            ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('hph_advanced_filters'); ?>'
        });
    }
});
</script>

<?php
/**
 * Helper method to render individual filter fields
 * This would be expanded based on the specific field types needed
 */
function render_filter_field($filter_type, $post_type, $current_params) {
    $field_configs = [
        'price_range' => [
            'type' => 'price_range',
            'label' => __('Price Range', 'happy-place-theme'),
            'min_name' => 'min_price',
            'max_name' => 'max_price'
        ],
        'bedrooms' => [
            'type' => 'select',
            'label' => __('Bedrooms', 'happy-place-theme'),
            'name' => 'bedrooms',
            'options' => [
                '' => __('Any', 'happy-place-theme'),
                '1' => '1+',
                '2' => '2+',
                '3' => '3+',
                '4' => '4+',
                '5' => '5+'
            ]
        ],
        'bathrooms' => [
            'type' => 'select',
            'label' => __('Bathrooms', 'happy-place-theme'),
            'name' => 'bathrooms',
            'options' => [
                '' => __('Any', 'happy-place-theme'),
                '1' => '1+',
                '1.5' => '1.5+',
                '2' => '2+',
                '2.5' => '2.5+',
                '3' => '3+'
            ]
        ],
        'property_type' => [
            'type' => 'select',
            'label' => __('Property Type', 'happy-place-theme'),
            'name' => 'property_type',
            'options' => [
                '' => __('Any Type', 'happy-place-theme'),
                'house' => __('House', 'happy-place-theme'),
                'condo' => __('Condo', 'happy-place-theme'),
                'townhouse' => __('Townhouse', 'happy-place-theme'),
                'apartment' => __('Apartment', 'happy-place-theme')
            ]
        ]
    ];
    
    if (!isset($field_configs[$filter_type])) {
        return '';
    }
    
    $config = $field_configs[$filter_type];
    $current_value = $current_params[$config['name']] ?? '';
    
    ob_start();
    ?>
    <div class="hph-filter-field">
        <label class="hph-block hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-1">
            <?php echo esc_html($config['label']); ?>
        </label>
        
        <?php if ($config['type'] === 'select') : ?>
            <select name="<?php echo esc_attr($config['name']); ?>" class="hph-w-full hph-border hph-border-gray-300 hph-rounded-md hph-px-3 hph-py-2 hph-text-sm focus:hph-outline-none focus:hph-ring-2 focus:hph-ring-primary-500">
                <?php foreach ($config['options'] as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($current_value, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php elseif ($config['type'] === 'price_range') : ?>
            <div class="hph-flex hph-gap-2">
                <input type="number" name="<?php echo esc_attr($config['min_name']); ?>" 
                       placeholder="Min" 
                       value="<?php echo esc_attr($current_params[$config['min_name']] ?? ''); ?>"
                       class="hph-flex-1 hph-border hph-border-gray-300 hph-rounded-md hph-px-3 hph-py-2 hph-text-sm focus:hph-outline-none focus:hph-ring-2 focus:hph-ring-primary-500">
                <input type="number" name="<?php echo esc_attr($config['max_name']); ?>" 
                       placeholder="Max" 
                       value="<?php echo esc_attr($current_params[$config['max_name']] ?? ''); ?>"
                       class="hph-flex-1 hph-border hph-border-gray-300 hph-rounded-md hph-px-3 hph-py-2 hph-text-sm focus:hph-outline-none focus:hph-ring-2 focus:hph-ring-primary-500">
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Helper method to render checkbox filters
 */
function render_checkbox_filter($feature, $current_params) {
    $feature_labels = [
        'waterfront' => __('Waterfront', 'happy-place-theme'),
        'pool' => __('Pool', 'happy-place-theme'),
        'garage' => __('Garage', 'happy-place-theme'),
        'fireplace' => __('Fireplace', 'happy-place-theme'),
        'basement' => __('Basement', 'happy-place-theme'),
        'luxury_specialist' => __('Luxury Specialist', 'happy-place-theme'),
        'first_time_buyers' => __('First Time Buyers', 'happy-place-theme'),
        'relocation' => __('Relocation', 'happy-place-theme')
    ];
    
    $label = $feature_labels[$feature] ?? ucwords(str_replace('_', ' ', $feature));
    $checked = !empty($current_params[$feature]);
    
    ob_start();
    ?>
    <label class="hph-checkbox-filter hph-flex hph-items-center hph-gap-2 hph-px-3 hph-py-2 hph-rounded-md hph-cursor-pointer">
        <input type="checkbox" name="<?php echo esc_attr($feature); ?>" value="1" <?php checked($checked); ?>
               class="hph-w-4 hph-h-4 hph-text-primary-600 hph-border-gray-300 hph-rounded focus:hph-ring-primary-500">
        <span class="hph-text-sm hph-text-gray-700"><?php echo esc_html($label); ?></span>
    </label>
    <?php
    return ob_get_clean();
}
?>