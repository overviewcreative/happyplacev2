<?php
/**
 * Archive Controls Component - Search, Sort, View, and Filter Controls
 * 
 * Professional control bar for archive pages with comprehensive options
 * for searching, sorting, changing view modes, and filtering results.
 * Designed to integrate seamlessly below hero sections.
 * 
 * @package HappyPlaceTheme
 * @version 3.0.0
 * 
 * === Configuration Options ===
 * 
 * Core Settings:
 * - post_type: string - Post type being displayed
 * - total_results: int - Total number of results
 * - current_view: string - Active view mode (grid/list/map)
 * - current_sort: string - Active sort option
 * - per_page: int - Items per page
 * 
 * Display Options:
 * - show_search: bool - Display search bar (default: true)
 * - show_view_toggle: bool - Display view mode switcher (default: true)
 * - show_sort: bool - Display sort dropdown (default: true)
 * - show_per_page: bool - Display per-page selector (default: true)
 * - show_results_count: bool - Display result count (default: true)
 * - show_filters_toggle: bool - Display filters toggle button (default: true)
 * 
 * Available Options:
 * - view_modes: array - Available view modes ['grid', 'list', 'map']
 * - sort_options: array - Available sort options with labels
 * - per_page_options: array - Available per-page options [12, 24, 48]
 * 
 * Search Configuration:
 * - search_placeholder: string - Search input placeholder text
 * - search_button_text: string - Search button text
 * - search_action: string - Form action URL
 * - search_value: string - Current search query
 * 
 * Style & Layout:
 * - layout: string - 'single-row', 'stacked', 'minimal'
 * - container_classes: array - Additional container classes
 * - sticky: bool - Make controls sticky on scroll
 * - background: string - Background style (white/gray/transparent)
 * 
 * Advanced Features:
 * - ajax_enabled: bool - Enable AJAX updates
 * - instant_search: bool - Search as you type
 * - save_preferences: bool - Remember user preferences
 * - show_reset: bool - Show reset filters button
 */

// Parse arguments with defaults
$args = wp_parse_args($args ?? [], [
    // Core Settings
    'post_type' => get_post_type() ?: 'post',
    'total_results' => 0,
    'current_view' => 'grid',
    'current_sort' => 'date-desc',
    'per_page' => 12,
    
    // Display Options
    'show_search' => true,
    'show_view_toggle' => true,
    'show_sort' => true,
    'show_per_page' => true,
    'show_results_count' => true,
    'show_filters_toggle' => true,
    
    // Available Options
    'view_modes' => ['grid', 'list'],
    'sort_options' => [
        'date-desc' => __('Newest First', 'happy-place-theme'),
        'date-asc' => __('Oldest First', 'happy-place-theme'),
        'title-asc' => __('Title A-Z', 'happy-place-theme'),
        'title-desc' => __('Title Z-A', 'happy-place-theme'),
    ],
    'per_page_options' => [12, 24, 48],
    
    // Search Configuration
    'search_placeholder' => __('Search...', 'happy-place-theme'),
    'search_button_text' => __('Search', 'happy-place-theme'),
    'search_action' => '',
    'search_value' => get_search_query(),
    
    // Style & Layout
    'layout' => 'single-row',
    'container_classes' => [],
    'sticky' => false,
    'background' => 'white',
    
    // Advanced Features
    'ajax_enabled' => false,
    'instant_search' => false,
    'save_preferences' => false,
    'show_reset' => false,
]);

// Build container classes
$container_classes = [
    'hph-archive-controls',
    'hph-archive-controls--' . $args['layout']
];

// Add background classes
switch ($args['background']) {
    case 'white':
        $container_classes[] = 'hph-bg-white';
        $container_classes[] = 'hph-border-b';
        $container_classes[] = 'hph-border-gray-200';
        break;
    case 'gray':
        $container_classes[] = 'hph-bg-gray-50';
        $container_classes[] = 'hph-border-b';
        $container_classes[] = 'hph-border-gray-200';
        break;
    case 'transparent':
        // No background
        break;
}

// Add padding
$container_classes[] = 'hph-py-lg';

// Add sticky behavior
if ($args['sticky']) {
    $container_classes[] = 'hph-sticky';
    $container_classes[] = 'hph-top-0';
    $container_classes[] = 'hph-z-sticky';
    $container_classes[] = 'hph-shadow-md';
}

// Add custom classes
if (!empty($args['container_classes'])) {
    $container_classes = array_merge($container_classes, $args['container_classes']);
}

// Add data attributes for JavaScript
$data_attrs = [
    'data-ajax' => $args['ajax_enabled'] ? 'true' : 'false',
    'data-instant-search' => $args['instant_search'] ? 'true' : 'false',
    'data-post-type' => esc_attr($args['post_type']),
];

?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" <?php foreach ($data_attrs as $attr => $value) echo $attr . '="' . $value . '" '; ?>>
    <div class="hph-container">
        
        <?php if ($args['layout'] === 'stacked') : ?>
            <!-- Stacked Layout: Search on top, controls below -->
            
            <?php if ($args['show_search']) : ?>
            <div class="hph-mb-md">
                <form class="hph-archive-search hph-flex hph-gap-sm" action="<?php echo esc_url($args['search_action'] ?: home_url('/')); ?>" method="get">
                    <?php if ($args['post_type'] !== 'post') : ?>
                        <input type="hidden" name="post_type" value="<?php echo esc_attr($args['post_type']); ?>">
                    <?php endif; ?>
                    
                    <div class="hph-flex-1 hph-relative">
                        <input 
                            type="search" 
                            name="s" 
                            value="<?php echo esc_attr($args['search_value']); ?>"
                            placeholder="<?php echo esc_attr($args['search_placeholder']); ?>"
                            class="hph-form-control hph-w-full hph-pl-12"
                            <?php if ($args['instant_search']) echo 'data-instant-search="true"'; ?>
                        >
                        <i class="fas fa-search hph-absolute hph-left-4 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-text-gray-400"></i>
                    </div>
                    
                    <button type="submit" class="hph-btn hph-btn-primary">
                        <i class="fas fa-search"></i>
                        <span><?php echo esc_html($args['search_button_text']); ?></span>
                    </button>
                    
                    <?php if ($args['show_reset'] && !empty($args['search_value'])) : ?>
                    <a href="<?php echo esc_url(remove_query_arg('s')); ?>" class="hph-btn hph-btn-outline">
                        <i class="fas fa-times"></i>
                        <span><?php esc_html_e('Clear', 'happy-place-theme'); ?></span>
                    </a>
                    <?php endif; ?>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="hph-flex hph-flex-wrap hph-items-center hph-justify-between hph-gap-md">
        <?php else : ?>
            <!-- Single Row Layout -->
            <div class="hph-flex hph-flex-wrap hph-items-center hph-justify-between hph-gap-md">
                
                <?php if ($args['show_search']) : ?>
                <div class="hph-flex-1 hph-min-w-0 lg:hph-max-w-md">
                    <form class="hph-archive-search hph-relative" action="<?php echo esc_url($args['search_action'] ?: home_url('/')); ?>" method="get">
                        <?php if ($args['post_type'] !== 'post') : ?>
                            <input type="hidden" name="post_type" value="<?php echo esc_attr($args['post_type']); ?>">
                        <?php endif; ?>
                        
                        <input 
                            type="search" 
                            name="s" 
                            value="<?php echo esc_attr($args['search_value']); ?>"
                            placeholder="<?php echo esc_attr($args['search_placeholder']); ?>"
                            class="hph-form-control hph-w-full hph-pr-10"
                            <?php if ($args['instant_search']) echo 'data-instant-search="true"'; ?>
                        >
                        <button type="submit" class="hph-absolute hph-right-2 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-p-2 hph-text-gray-400 hover:hph-text-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
        <?php endif; ?>
        
                <!-- Left side: Results count and filters toggle -->
                <div class="hph-flex hph-items-center hph-gap-md">
                    
                    <?php if ($args['show_results_count']) : ?>
                    <div class="hph-results-count hph-text-sm hph-text-gray-600">
                        <?php
                        if ($args['total_results'] > 0) {
                            printf(
                                _n(
                                    '<span class="hph-font-semibold">%s</span> Result',
                                    '<span class="hph-font-semibold">%s</span> Results',
                                    $args['total_results'],
                                    'happy-place-theme'
                                ),
                                number_format_i18n($args['total_results'])
                            );
                        } else {
                            esc_html_e('No Results', 'happy-place-theme');
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($args['show_filters_toggle']) : ?>
                    <button 
                        type="button" 
                        class="hph-btn hph-btn-outline hph-btn-sm"
                        data-toggle="filters"
                        aria-label="<?php esc_attr_e('Toggle Filters', 'happy-place-theme'); ?>"
                    >
                        <i class="fas fa-filter"></i>
                        <span class="hph-hidden sm:hph-inline"><?php esc_html_e('Filters', 'happy-place-theme'); ?></span>
                        <span class="hph-badge hph-badge--primary hph-ml-2 hph-hidden" data-filter-count>0</span>
                    </button>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Right side: View toggle, sort, and per-page -->
                <div class="hph-flex hph-items-center hph-gap-sm">
                    
                    <?php if ($args['show_view_toggle'] && count($args['view_modes']) > 1) : ?>
                    <div class="hph-view-toggle hph-flex hph-bg-gray-100 hph-rounded-md hph-p-1" role="group" aria-label="<?php esc_attr_e('View Mode', 'happy-place-theme'); ?>">
                        <?php foreach ($args['view_modes'] as $mode) : ?>
                            <?php
                            $icon = '';
                            $label = '';
                            switch ($mode) {
                                case 'grid':
                                    $icon = 'fas fa-th';
                                    $label = __('Grid', 'happy-place-theme');
                                    break;
                                case 'list':
                                    $icon = 'fas fa-list';
                                    $label = __('List', 'happy-place-theme');
                                    break;
                                case 'map':
                                    $icon = 'fas fa-map';
                                    $label = __('Map', 'happy-place-theme');
                                    break;
                            }
                            ?>
                            <button 
                                type="button"
                                class="hph-view-toggle-btn hph-px-3 hph-py-2 hph-rounded <?php echo $mode === $args['current_view'] ? 'hph-bg-white hph-shadow-sm hph-text-primary' : 'hph-text-gray-600 hover:hph-text-gray-900'; ?>"
                                data-view="<?php echo esc_attr($mode); ?>"
                                aria-label="<?php echo esc_attr($label); ?>"
                                <?php if ($mode === $args['current_view']) echo 'aria-pressed="true"'; ?>
                            >
                                <i class="<?php echo esc_attr($icon); ?>"></i>
                                <span class="hph-hidden lg:hph-inline hph-ml-2"><?php echo esc_html($label); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($args['show_sort'] && !empty($args['sort_options'])) : ?>
                    <div class="hph-sort-dropdown hph-relative">
                        <select 
                            class="hph-form-select hph-pr-10"
                            data-sort-select
                            aria-label="<?php esc_attr_e('Sort Results', 'happy-place-theme'); ?>"
                        >
                            <?php foreach ($args['sort_options'] as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($value, $args['current_sort']); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($args['show_per_page'] && !empty($args['per_page_options'])) : ?>
                    <div class="hph-per-page-dropdown hph-relative">
                        <select 
                            class="hph-form-select hph-pr-8"
                            data-per-page-select
                            aria-label="<?php esc_attr_e('Items per page', 'happy-place-theme'); ?>"
                        >
                            <?php foreach ($args['per_page_options'] as $option) : ?>
                                <option value="<?php echo esc_attr($option); ?>" <?php selected($option, $args['per_page']); ?>>
                                    <?php echo esc_html($option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                </div>
                
        <?php if ($args['layout'] === 'stacked') : ?>
            </div>
        <?php endif; ?>
        
        </div><!-- .hph-flex -->
    </div><!-- .hph-container -->
</div><!-- .hph-archive-controls -->

<?php if ($args['ajax_enabled']) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const controls = document.querySelector('.hph-archive-controls');
    if (!controls) return;
    
    // View toggle handler
    const viewButtons = controls.querySelectorAll('[data-view]');
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            const url = new URL(window.location);
            url.searchParams.set('view', view);
            
            if (controls.dataset.ajax === 'true') {
                // Trigger custom event for AJAX handling
                window.dispatchEvent(new CustomEvent('archive-view-change', {
                    detail: { view }
                }));
            } else {
                window.location = url.toString();
            }
        });
    });
    
    // Sort change handler
    const sortSelect = controls.querySelector('[data-sort-select]');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sort = this.value;
            const url = new URL(window.location);
            url.searchParams.set('sort', sort);
            
            if (controls.dataset.ajax === 'true') {
                window.dispatchEvent(new CustomEvent('archive-sort-change', {
                    detail: { sort }
                }));
            } else {
                window.location = url.toString();
            }
        });
    }
    
    // Per page change handler
    const perPageSelect = controls.querySelector('[data-per-page-select]');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const perPage = this.value;
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            
            if (controls.dataset.ajax === 'true') {
                window.dispatchEvent(new CustomEvent('archive-per-page-change', {
                    detail: { perPage }
                }));
            } else {
                window.location = url.toString();
            }
        });
    }
    
    // Instant search handler
    if (controls.dataset.instantSearch === 'true') {
        const searchInput = controls.querySelector('[data-instant-search]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('archive-search', {
                        detail: { query: this.value }
                    }));
                }, 300);
            });
        }
    }
    
    // Filters toggle handler
    const filtersToggle = controls.querySelector('[data-toggle="filters"]');
    if (filtersToggle) {
        filtersToggle.addEventListener('click', function() {
            window.dispatchEvent(new CustomEvent('toggle-filters'));
            this.classList.toggle('hph-bg-primary');
            this.classList.toggle('hph-text-white');
        });
    }
});
</script>
<?php endif; ?>