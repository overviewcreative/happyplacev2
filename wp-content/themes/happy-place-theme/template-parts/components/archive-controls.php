<?php
/**
 * Archive Controls Component
 * 
 * Provides view switching, sorting, results count, and per-page controls for archive pages
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Parse arguments
$controls_args = wp_parse_args($args ?? [], [
    'post_type' => 'post',
    'current_view' => 'grid',
    'current_sort' => 'date_desc',
    'per_page' => 12,
    'total_results' => 0,
    'view_modes' => ['grid', 'list'],
    'sort_options' => [],
    'per_page_options' => [12, 24, 48],
    'allow_view_toggle' => true,
    'show_search' => false,
    'show_results_count' => true,
    'ajax_enabled' => true,
    'animation_style' => 'fade'
]);

// Only show if we have controls to display
if (!$controls_args['allow_view_toggle'] && empty($controls_args['sort_options']) && !$controls_args['show_results_count']) {
    return;
}

// Animation classes
$animation_classes = '';
if ($controls_args['animation_style'] !== 'none') {
    $animation_classes = 'hph-animate-' . $controls_args['animation_style'] . '-in';
}
?>

<div class="hph-archive-controls hph-flex hph-flex-wrap hph-items-center hph-justify-between hph-gap-4 hph-mb-6 <?php echo esc_attr($animation_classes); ?>">
    
    <!-- Results Count and Info -->
    <?php if ($controls_args['show_results_count']) : ?>
        <div class="hph-results-info hph-flex hph-items-center hph-gap-4">
            <div class="hph-results-count hph-text-sm hph-text-gray-600">
                <?php
                if ($controls_args['total_results'] > 0) {
                    printf(
                        _n('%d result found', '%d results found', $controls_args['total_results'], 'happy-place-theme'),
                        $controls_args['total_results']
                    );
                } else {
                    _e('No results found', 'happy-place-theme');
                }
                ?>
            </div>
            
            <?php if ($controls_args['show_search'] && !empty($_GET['s'])) : ?>
                <div class="hph-search-query hph-text-sm hph-text-gray-500">
                    <?php printf(__('for "%s"', 'happy-place-theme'), '<strong>' . esc_html($_GET['s']) . '</strong>'); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Main Controls -->
    <div class="hph-controls-main hph-flex hph-items-center hph-gap-4">
        
        <!-- View Mode Toggle -->
        <?php if ($controls_args['allow_view_toggle'] && count($controls_args['view_modes']) > 1) : ?>
            <div class="hph-view-toggle" role="tablist" aria-label="<?php esc_attr_e('View Mode', 'happy-place-theme'); ?>">
                <?php foreach ($controls_args['view_modes'] as $view_mode) : 
                    $is_active = $controls_args['current_view'] === $view_mode;
                    $view_labels = [
                        'grid' => __('Grid View', 'happy-place-theme'),
                        'list' => __('List View', 'happy-place-theme'),
                        'masonry' => __('Masonry View', 'happy-place-theme'),
                        /* 'map' => __('Map View', 'happy-place-theme') */
                    ];
                    $view_icons = [
                        'grid' => '⊞',
                        'list' => '☰',
                        'masonry' => '▦',
                        /* 'map' => '🗺️' */
                    ];
                ?>
                    <button 
                        type="button" 
                        class="hph-view-button <?php echo $is_active ? 'hph-active' : ''; ?> hph-inline-flex hph-items-center hph-justify-center hph-w-10 hph-h-10 hph-border hph-border-gray-300 hph-bg-white hph-text-gray-600 hover:hph-bg-gray-50 hover:hph-text-gray-900 focus:hph-outline-none focus:hph-ring-2 focus:hph-ring-primary-500"
                        data-view="<?php echo esc_attr($view_mode); ?>"
                        role="tab"
                        aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                        aria-label="<?php echo esc_attr($view_labels[$view_mode] ?? ucfirst($view_mode)); ?>"
                        title="<?php echo esc_attr($view_labels[$view_mode] ?? ucfirst($view_mode)); ?>"
                    >
                        <span class="hph-view-icon" aria-hidden="true">
                            <?php echo $view_icons[$view_mode] ?? '◦'; ?>
                        </span>
                        <span class="hph-sr-only"><?php echo esc_html($view_labels[$view_mode] ?? ucfirst($view_mode)); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Sort Dropdown -->
        <?php if (!empty($controls_args['sort_options'])) : ?>
            <div class="hph-sort-control">
                <label for="hph-sort-select" class="hph-sr-only">
                    <?php _e('Sort by', 'happy-place-theme'); ?>
                </label>
                <select 
                    id="hph-sort-select" 
                    class="hph-sort-select hph-border hph-border-gray-300 hph-rounded hph-px-3 hph-py-2 hph-text-sm hph-bg-white focus:hph-outline-none focus:hph-ring-2 focus:hph-ring-primary-500"
                    name="sort"
                    <?php echo $controls_args['ajax_enabled'] ? 'data-ajax="true"' : ''; ?>
                >
                    <?php foreach ($controls_args['sort_options'] as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($controls_args['current_sort'], $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        
        <!-- Per Page Dropdown -->
        <?php if (count($controls_args['per_page_options']) > 1) : ?>
            <div class="hph-per-page-control">
                <label for="hph-per-page-select" class="hph-text-sm hph-text-gray-600 hph-mr-2">
                    <?php _e('Show:', 'happy-place-theme'); ?>
                </label>
                <select 
                    id="hph-per-page-select" 
                    class="hph-per-page-select hph-border hph-border-gray-300 hph-rounded hph-px-2 hph-py-1 hph-text-sm hph-bg-white focus:hph-outline-none focus:hph-ring-2 focus:hph-ring-primary-500"
                    name="per_page"
                    <?php echo $controls_args['ajax_enabled'] ? 'data-ajax="true"' : ''; ?>
                >
                    <?php foreach ($controls_args['per_page_options'] as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>" <?php selected($controls_args['per_page'], $option); ?>>
                            <?php echo esc_html($option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        
        <!-- Additional Actions -->
        <div class="hph-archive-actions hph-flex hph-items-center hph-gap-2">
            
            <!-- Clear Filters Button (shown when filters are active) -->
            <?php if (!empty($_GET) && (isset($_GET['s']) || count(array_intersect_key($_GET, $controls_args)) > 0)) : ?>
                <button 
                    type="button" 
                    class="hpt-clear-filters hph-text-sm hph-text-gray-500 hover:hph-text-gray-700 hph-underline"
                >
                    <?php _e('Clear Filters', 'happy-place-theme'); ?>
                </button>
            <?php endif; ?>
            
            <!-- Save Search (if user is logged in) -->
            <?php if (is_user_logged_in() && $controls_args['show_search']) : ?>
                <button 
                    type="button" 
                    class="hph-save-search hph-text-sm hph-text-primary-600 hover:hph-text-primary-700"
                    title="<?php esc_attr_e('Save this search', 'happy-place-theme'); ?>"
                >
                    <?php _e('Save Search', 'happy-place-theme'); ?>
                </button>
            <?php endif; ?>
        </div>
        
    </div>
    
</div>

<?php
// Add inline styles for better visual consistency
if (!wp_style_is('hph-archive-controls', 'enqueued')) :
?>
<style>
.hph-archive-controls .hph-view-button {
    transition: all 0.2s ease;
}
.hph-archive-controls .hph-view-button:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
    border-right: 0;
}
.hph-archive-controls .hph-view-button:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}
.hph-archive-controls .hph-view-button:not(:first-child):not(:last-child) {
    border-right: 0;
}
.hph-archive-controls .hph-view-button.hph-active {
    background-color: var(--hph-primary-500, var(--hph-primary));
    border-color: var(--hph-primary-500, var(--hph-primary));
    color: white;
}
</style>
<?php endif; ?>
