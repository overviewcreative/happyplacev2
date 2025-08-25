<?php
/**
 * Archive Layout Component - Professional modular archive display system
 *
 * A comprehensive archive layout component that provides structured, responsive
 * archive displays with extensive customization options. Built using the
 * foundation-first approach with seamless integration to enhanced card-grid
 * and other base components.
 *
 * === Configuration Options ===
 *
 * Core Configuration:
 * - post_type: string - Post type for archive (default: 'post')
 * - posts: array - Custom posts array (overrides query)
 * - title: string - Archive title (auto-generated from context)
 * - description: string - Archive description (auto-generated)
 * - total_results: int - Total result count (auto-calculated)
 *
 * Layout Structure:
 * - layout: string - 'full-width', 'contained', 'narrow' (default: 'contained')
 * - container_class: string - Container wrapper classes
 * - content_width: string - 'narrow', 'normal', 'wide', 'full'
 * - padding: string - 'none', 'sm', 'md', 'lg', 'xl', '2xl'
 * - background: string - 'white', 'gray', 'light', 'dark', 'transparent'
 *
 * View Configuration:
 * - view_modes: array - Available view modes ['grid', 'list', 'map']
 * - current_view: string - Active view mode (from URL params)
 * - default_view: string - Fallback view mode
 * - allow_view_toggle: bool - Show view mode controls
 *
 * Sorting & Filtering:
 * - sort_options: array - Available sort options with labels
 * - current_sort: string - Active sort mode (from URL params) 
 * - default_sort: string - Fallback sort mode
 * - show_search: bool - Display search functionality
 * - show_filters: bool - Display filter controls
 * - show_save_search: bool - Allow saved searches
 *
 * Display Components:
 * - show_header: bool - Display archive header section
 * - show_controls: bool - Display view/sort controls
 * - show_pagination: bool - Display pagination component
 * - show_results_count: bool - Display result count
 * - show_related: bool - Display related content section
 *
 * Grid Configuration:
 * - columns: int - Desktop grid columns (1-6)
 * - columns_tablet: int - Tablet grid columns
 * - columns_mobile: int - Mobile grid columns
 * - gap: string - Grid gap size
 * - card_style: string - Card appearance style
 * - card_size: string - Card size variant
 *
 * Sidebar Configuration:
 * - show_sidebar: bool - Enable sidebar display
 * - sidebar_position: string - 'left', 'right'
 * - sidebar_width: string - Sidebar width ratio
 * - sidebar_sticky: bool - Sticky sidebar behavior
 *
 * Pagination:
 * - per_page: int - Items per page (from URL or default)
 * - per_page_options: array - Available per-page options
 * - pagination_style: string - 'numbered', 'load-more', 'infinite'
 * - max_pages: int - Maximum page count
 * - current_page: int - Active page number
 *
 * Advanced Features:
 * - ajax_enabled: bool - Enable AJAX loading
 * - infinite_scroll: bool - Enable infinite scroll
 * - lazy_loading: bool - Enable lazy loading
 * - analytics_tracking: bool - Enable analytics events
 * - cache_results: bool - Enable result caching
 *
 * Animation & Effects:
 * - animation_style: string - 'fade', 'slide', 'scale', 'none'
 * - stagger_delay: int - Animation stagger delay (ms)
 * - hover_effects: bool - Enable card hover effects
 * - loading_skeleton: bool - Show loading skeletons
 *
 * Responsive Behavior:
 * - mobile_stack: bool - Stack layout on mobile
 * - tablet_columns: int - Tablet-specific column count
 * - breakpoint_behavior: array - Custom breakpoint rules
 *
 * Accessibility:
 * - aria_label: string - ARIA label for archive
 * - keyboard_navigation: bool - Enable keyboard nav
 * - screen_reader_text: array - Screen reader labels
 * - focus_management: bool - Manage focus states
 *
 * SEO & Meta:
 * - structured_data: bool - Generate structured data
 * - meta_description: string - Page meta description
 * - canonical_url: string - Canonical URL
 * - open_graph: array - Open Graph meta data
 *
 * Integration Hooks:
 * - before_archive: callable - Execute before archive
 * - after_archive: callable - Execute after archive
 * - custom_query_filters: array - Custom query modifications
 * - result_processors: array - Post-query result processors
 *
 * @package HappyPlaceTheme
 * @since 3.0.0
 * @version 3.2.0
 */

// Parse and validate arguments with comprehensive defaults
$archive_args = wp_parse_args($args ?? [], [
    // Core Configuration
    'post_type' => get_post_type() ?: 'post',
    'posts' => null,
    'title' => get_the_archive_title(),
    'description' => get_the_archive_description(),
    'total_results' => null,
    
    // Layout Structure
    'layout' => 'contained',
    'container_class' => 'hph-container',
    'content_width' => 'normal',
    'padding' => 'xl',
    'background' => 'white',
    
    // View Configuration
    'view_modes' => ['grid', 'list'],
    'current_view' => get_query_var('view', 'grid'),
    'default_view' => 'grid',
    'allow_view_toggle' => true,
    
    // Sorting & Filtering
    'sort_options' => [
        'date-desc' => __('Newest First', 'happy-place-theme'),
        'date-asc' => __('Oldest First', 'happy-place-theme'),
        'title-asc' => __('Title A-Z', 'happy-place-theme'),
        'title-desc' => __('Title Z-A', 'happy-place-theme')
    ],
    'current_sort' => get_query_var('sort', 'date-desc'),
    'default_sort' => 'date-desc',
    'show_search' => true,
    'show_filters' => false,
    'show_save_search' => false,
    
    // Display Components
    'show_header' => true,
    'show_controls' => true,
    'show_pagination' => true,
    'show_results_count' => true,
    'show_related' => false,
    
    // Grid Configuration
    'columns' => 3,
    'columns_tablet' => 2,
    'columns_mobile' => 1,
    'gap' => 'lg',
    'card_style' => 'modern',
    'card_size' => 'medium',
    
    // Sidebar Configuration
    'show_sidebar' => false,
    'sidebar_position' => 'right',
    'sidebar_width' => '1/4',
    'sidebar_sticky' => true,
    
    // Pagination
    'per_page' => get_option('posts_per_page', 12),
    'per_page_options' => [12, 24, 48],
    'pagination_style' => 'numbered',
    'max_pages' => null,
    'current_page' => get_query_var('paged', 1),
    
    // Advanced Features
    'ajax_enabled' => true,
    'infinite_scroll' => false,
    'lazy_loading' => true,
    'analytics_tracking' => false,
    'cache_results' => false,
    
    // Animation & Effects
    'animation_style' => 'fade',
    'stagger_delay' => 100,
    'hover_effects' => true,
    'loading_skeleton' => true,
    
    // Responsive Behavior
    'mobile_stack' => true,
    'tablet_columns' => null,
    'breakpoint_behavior' => [],
    
    // Accessibility
    'aria_label' => null,
    'keyboard_navigation' => true,
    'screen_reader_text' => [],
    'focus_management' => true,
    
    // SEO & Meta
    'structured_data' => true,
    'meta_description' => null,
    'canonical_url' => null,
    'open_graph' => [],
    
    // Integration Hooks
    'before_archive' => null,
    'after_archive' => null,
    'custom_query_filters' => [],
    'result_processors' => [],
    
    // Legacy compatibility
    'query_args' => []
]);

// Sanitize and validate URL parameters
$current_view = sanitize_text_field($_GET['view'] ?? $archive_args['current_view']);
$current_sort = sanitize_text_field($_GET['sort'] ?? $archive_args['current_sort']);
$per_page = intval($_GET['per_page'] ?? $archive_args['per_page']);
$current_page = max(1, intval($_GET['paged'] ?? $archive_args['current_page']));

// Validate view mode against allowed options
if (!in_array($current_view, $archive_args['view_modes'])) {
    $current_view = $archive_args['default_view'];
}

// Validate sort option against available options
if (!array_key_exists($current_sort, $archive_args['sort_options'])) {
    $current_sort = $archive_args['default_sort'];
}

// Validate per page option
if (!in_array($per_page, $archive_args['per_page_options'])) {
    $per_page = $archive_args['per_page'];
}

// Update args with validated values
$archive_args['current_view'] = $current_view;
$archive_args['current_sort'] = $current_sort;
$archive_args['per_page'] = $per_page;
$archive_args['current_page'] = $current_page;

// Determine query source and posts array
$archive_query = null;
$posts_array = [];

if (!empty($archive_args['posts'])) {
    // Use provided posts array
    $posts_array = $archive_args['posts'];
    $archive_args['total_results'] = count($posts_array);
} elseif (!empty($archive_args['query_args'])) {
    // Execute custom query
    $archive_query = new WP_Query($archive_args['query_args']);
    $posts_array = $archive_query->posts ?? [];
    $archive_args['total_results'] = $archive_query->found_posts ?? 0;
    $archive_args['max_pages'] = $archive_query->max_num_pages ?? 1;
} else {
    // Use global query
    global $wp_query;
    $archive_query = $wp_query;
    $posts_array = $archive_query->posts ?? [];
    $archive_args['total_results'] = $archive_query->found_posts ?? 0;
    $archive_args['max_pages'] = $archive_query->max_num_pages ?? 1;
}

// Execute before archive hook
if (is_callable($archive_args['before_archive'])) {
    call_user_func($archive_args['before_archive'], $archive_args);
}

// Process custom query filters
if (!empty($archive_args['custom_query_filters'])) {
    foreach ($archive_args['custom_query_filters'] as $filter) {
        if (is_callable($filter)) {
            $posts_array = call_user_func($filter, $posts_array, $archive_args);
        }
    }
}

// Process result processors
if (!empty($archive_args['result_processors'])) {
    foreach ($archive_args['result_processors'] as $processor) {
        if (is_callable($processor)) {
            $posts_array = call_user_func($processor, $posts_array, $archive_args);
        }
    }
}

// Build comprehensive layout classes using utility-first approach
$layout_classes = [
    'hph-archive-layout',
    'hph-relative',
    'hph-min-h-screen'
];

// Add background classes
switch ($archive_args['background']) {
    case 'white':
        $layout_classes[] = 'hph-bg-white';
        break;
    case 'gray':
        $layout_classes[] = 'hph-bg-gray-100';
        break;
    case 'light':
        $layout_classes[] = 'hph-bg-gray-50';
        break;
    case 'dark':
        $layout_classes[] = 'hph-bg-gray-900';
        break;
    case 'transparent':
        break;
    default:
        $layout_classes[] = 'hph-bg-white';
}

// Add padding classes
if ($archive_args['padding'] !== 'none') {
    $layout_classes[] = 'hph-py-' . $archive_args['padding'];
}

// Add post type specific classes for targeted styling
$layout_classes[] = 'hph-archive-' . $archive_args['post_type'];
$layout_classes[] = 'hph-archive-' . $archive_args['post_type'] . '--' . $current_view;

// Add layout mode classes
switch ($archive_args['layout']) {
    case 'full-width':
        $layout_classes[] = 'hph-w-full';
        break;
    case 'contained':
        $layout_classes[] = 'hph-max-w-7xl';
        $layout_classes[] = 'hph-mx-auto';
        break;
    case 'narrow':
        $layout_classes[] = 'hph-max-w-4xl';
        $layout_classes[] = 'hph-mx-auto';
        break;
}

// Add content width classes
switch ($archive_args['content_width']) {
    case 'narrow':
        $layout_classes[] = 'hph-content-narrow';
        break;
    case 'wide':
        $layout_classes[] = 'hph-content-wide';
        break;
    case 'full':
        $layout_classes[] = 'hph-content-full';
        break;
    default: // normal
        $layout_classes[] = 'hph-content-normal';
}

// Add view mode classes for CSS targeting
$layout_classes[] = 'hph-view-' . $current_view;
$layout_classes[] = 'hph-sort-' . str_replace('-', '_', $current_sort);

// Add responsive behavior classes
if ($archive_args['mobile_stack']) {
    $layout_classes[] = 'hph-mobile-stack';
}

// Add animation classes
if ($archive_args['animation_style'] !== 'none') {
    $layout_classes[] = 'hph-animate-' . $archive_args['animation_style'];
}

// Add accessibility classes
if ($archive_args['keyboard_navigation']) {
    $layout_classes[] = 'hph-keyboard-nav';
}

// Add AJAX classes
if ($archive_args['ajax_enabled']) {
    $layout_classes[] = 'hph-ajax-enabled';
}

// Add loading classes
if ($archive_args['loading_skeleton']) {
    $layout_classes[] = 'hph-skeleton-enabled';
}

// Build main content wrapper classes
$main_content_classes = ['hph-archive-content'];

if ($archive_args['show_sidebar']) {
    $main_content_classes[] = 'hph-grid';
    $main_content_classes[] = 'hph-grid-cols-1';
    
    // Sidebar width configuration
    if ($archive_args['sidebar_width'] === '1/3') {
        $main_content_classes[] = 'lg:hph-grid-cols-3';
    } else {
        $main_content_classes[] = 'lg:hph-grid-cols-4';
    }
    
    $main_content_classes[] = 'hph-gap-' . $archive_args['gap'];
}

// Build main content column classes
$content_column_classes = [];
if ($archive_args['show_sidebar']) {
    if ($archive_args['sidebar_width'] === '1/3') {
        $content_column_classes[] = 'hph-col-span-1 lg:hph-col-span-2';
    } else {
        $content_column_classes[] = 'hph-col-span-1 lg:hph-col-span-3';
    }
} else {
    $content_column_classes[] = 'hph-w-full';
}

// Build sidebar classes
$sidebar_classes = [
    'hph-col-span-1',
    'hph-space-y-' . $archive_args['gap']
];

if ($archive_args['sidebar_sticky']) {
    $sidebar_classes[] = 'hph-sticky';
    $sidebar_classes[] = 'hph-top-8';
}

// Generate unique component ID for targeting
$component_id = 'hph-archive-' . uniqid();
$archive_args['component_id'] = $component_id;
?>

<div 
    id="<?php echo esc_attr($component_id); ?>"
    class="<?php echo esc_attr(implode(' ', $layout_classes)); ?>"
    data-view="<?php echo esc_attr($current_view); ?>"
    data-sort="<?php echo esc_attr($current_sort); ?>"
    data-per-page="<?php echo esc_attr($per_page); ?>"
    data-page="<?php echo esc_attr($current_page); ?>"
    data-total="<?php echo esc_attr($archive_args['total_results']); ?>"
    data-post-type="<?php echo esc_attr($archive_args['post_type']); ?>"
    <?php if ($archive_args['ajax_enabled']) : ?>
        data-ajax="true"
        data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
        data-nonce="<?php echo esc_attr(wp_create_nonce('hph_archive_ajax_' . $archive_args['post_type'])); ?>"
    <?php endif; ?>
    <?php if ($archive_args['aria_label']) : ?>
        aria-label="<?php echo esc_attr($archive_args['aria_label']); ?>"
    <?php else : ?>
        aria-label="<?php echo esc_attr(sprintf(__('%s Archive', 'happy-place-theme'), ucfirst($archive_args['post_type']))); ?>"
    <?php endif; ?>
    <?php if ($archive_args['infinite_scroll']) : ?>
        data-infinite-scroll="true"
    <?php endif; ?>
    role="main"
>
    
    <?php if ($archive_args['show_header']) : ?>
        <header class="hph-archive-header hph-mb-<?php echo esc_attr($archive_args['gap']); ?>" role="banner">
            <?php
            get_template_part('template-parts/components/archive-header', null, array_merge($archive_args, [
                'current_view' => $current_view,
                'current_sort' => $current_sort,
                'total_results' => $archive_args['total_results'],
                'animation_style' => $archive_args['animation_style'],
                'stagger_delay' => $archive_args['stagger_delay']
            ]));
            ?>
        </header>
    <?php endif; ?>
    
    <?php if ($archive_args['show_controls']) : ?>
        <div class="hph-archive-controls hph-mb-<?php echo esc_attr($archive_args['gap']); ?>" role="navigation" aria-label="<?php esc_attr_e('Archive Controls', 'happy-place-theme'); ?>">
            <?php
            get_template_part('template-parts/components/archive-controls', null, array_merge($archive_args, [
                'current_view' => $current_view,
                'current_sort' => $current_sort,
                'per_page' => $per_page,
                'view_modes' => $archive_args['view_modes'],
                'sort_options' => $archive_args['sort_options'],
                'per_page_options' => $archive_args['per_page_options'],
                'allow_view_toggle' => $archive_args['allow_view_toggle'],
                'show_search' => $archive_args['show_search'],
                'show_results_count' => $archive_args['show_results_count'],
                'ajax_enabled' => $archive_args['ajax_enabled'],
                'animation_style' => $archive_args['animation_style']
            ]));
            ?>
        </div>
    <?php endif; ?>
    
    <?php if ($archive_args['show_filters']) : ?>
        <div class="hph-archive-filters hph-mb-<?php echo esc_attr($archive_args['gap']); ?>" role="search" aria-label="<?php esc_attr_e('Archive Filters', 'happy-place-theme'); ?>">
            <?php
            get_template_part('template-parts/components/archive-filters', null, array_merge($archive_args, [
                'post_type' => $archive_args['post_type'],
                'current_view' => $current_view,
                'ajax_enabled' => $archive_args['ajax_enabled'],
                'show_save_search' => $archive_args['show_save_search'],
                'animation_style' => $archive_args['animation_style']
            ]));
            ?>
        </div>
    <?php endif; ?>
    
    <main class="hph-archive-main hph-flex-1" role="main" aria-live="polite">
        <div class="<?php echo esc_attr($archive_args['container_class']); ?>">
            
            <!-- Loading Skeleton (Hidden by default, shown during AJAX) -->
            <?php if ($archive_args['loading_skeleton'] && $archive_args['ajax_enabled']) : ?>
                <div class="hph-loading-skeleton hph-hidden" aria-hidden="true">
                    <?php
                    get_template_part('template-parts/components/loading-skeleton', null, [
                        'type' => 'archive',
                        'view_mode' => $current_view,
                        'columns' => $archive_args['columns'],
                        'count' => $archive_args['per_page']
                    ]);
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Main Content Area -->
            <div class="<?php echo esc_attr(implode(' ', $main_content_classes)); ?>" data-content-area="true">
                
                <?php if ($archive_args['show_sidebar'] && $archive_args['sidebar_position'] === 'left') : ?>
                    <aside class="<?php echo esc_attr(implode(' ', $sidebar_classes)); ?>" role="complementary" aria-label="<?php esc_attr_e('Archive Sidebar', 'happy-place-theme'); ?>">
                        <?php
                        get_template_part('template-parts/components/archive-sidebar', null, array_merge($archive_args, [
                            'position' => 'left',
                            'sticky' => $archive_args['sidebar_sticky'],
                            'animation_style' => $archive_args['animation_style']
                        ]));
                        ?>
                    </aside>
                <?php endif; ?>
                
                <div class="<?php echo esc_attr(implode(' ', $content_column_classes)); ?>" data-main-content="true">
                    
                    <?php if (!empty($posts_array)) : ?>
                        
                        <div class="hph-archive-results hph-space-y-<?php echo esc_attr($archive_args['gap']); ?>" data-results-container="true">
                            
                            <?php
                            // Build enhanced card display arguments
                            $card_display_args = array_merge($archive_args, [
                                'posts' => $posts_array,
                                'layout' => $current_view,
                                'columns' => $archive_args['columns'],
                                'columns_tablet' => $archive_args['columns_tablet'] ?: max(1, $archive_args['columns'] - 1),
                                'columns_mobile' => $archive_args['columns_mobile'],
                                'gap' => $archive_args['gap'],
                                'card_style' => $archive_args['card_style'],
                                'card_size' => $archive_args['card_size'],
                                'hover_effects' => $archive_args['hover_effects'],
                                'lazy_loading' => $archive_args['lazy_loading'],
                                'show_excerpt' => true,
                                'show_meta' => true,
                                'show_actions' => true,
                                'animation_style' => $archive_args['animation_style'],
                                'stagger_animation' => true,
                                'stagger_delay' => $archive_args['stagger_delay'],
                                'container_classes' => [
                                    'hph-archive-grid',
                                    'hph-transition-all',
                                    'hph-duration-300',
                                    'hph-ease-in-out'
                                ]
                            ]);
                            
                            // Determine layout component based on view mode
                            switch ($current_view) {
                                case 'grid':
                                    get_template_part('template-parts/base/card-grid', null, $card_display_args);
                                    break;
                                    
                                case 'list':
                                    get_template_part('template-parts/base/card-grid', null, array_merge($card_display_args, [
                                        'layout' => 'list',
                                        'columns' => 1,
                                        'columns_tablet' => 1,
                                        'columns_mobile' => 1,
                                        'card_layout' => 'horizontal'
                                    ]));
                                    break;
                                    
                                case 'masonry':
                                    get_template_part('template-parts/base/card-grid', null, array_merge($card_display_args, [
                                        'layout' => 'masonry',
                                        'masonry_enabled' => true
                                    ]));
                                    break;
                                    
                                case 'map':
                                    if (file_exists(get_template_directory() . '/template-parts/components/archive-map.php')) {
                                        get_template_part('template-parts/components/archive-map', null, array_merge($archive_args, [
                                            'posts' => $posts_array,
                                            'show_list' => true,
                                            'map_height' => '400px',
                                            'animation_style' => $archive_args['animation_style']
                                        ]));
                                    } else {
                                        // Fallback to grid if map component doesn't exist
                                        get_template_part('template-parts/base/card-grid', null, $card_display_args);
                                    }
                                    break;
                                    
                                default:
                                    // Fallback to enhanced card-grid
                                    get_template_part('template-parts/base/card-grid', null, $card_display_args);
                                    break;
                            }
                            ?>
                            
                            <?php if ($archive_args['show_pagination'] && ($archive_args['max_pages'] > 1)) : ?>
                                <nav class="hph-archive-pagination hph-mt-2xl" role="navigation" aria-label="<?php esc_attr_e('Archive Pagination', 'happy-place-theme'); ?>">
                                    <div class="<?php echo $archive_args['animation_style'] !== 'none' ? 'hph-animate-fade-in-up' : ''; ?>">
                                        <?php
                                        $pagination_args = [
                                            'query' => $archive_query,
                                            'current_page' => $archive_args['current_page'],
                                            'max_pages' => $archive_args['max_pages'],
                                            'per_page' => $archive_args['per_page'],
                                            'total_results' => $archive_args['total_results'],
                                            'style' => $archive_args['pagination_style'],
                                            'ajax_enabled' => $archive_args['ajax_enabled'],
                                            'infinite_scroll' => $archive_args['infinite_scroll'],
                                            'post_type' => $archive_args['post_type']
                                        ];
                                        
                                        if ($archive_args['pagination_style'] === 'load-more' || $archive_args['infinite_scroll']) {
                                            get_template_part('template-parts/components/load-more-pagination', null, $pagination_args);
                                        } else {
                                            get_template_part('template-parts/components/numbered-pagination', null, $pagination_args);
                                        }
                                        ?>
                                    </div>
                                </nav>
                            <?php endif; ?>
                        </div>
                        
                    <?php else : ?>
                        
                        <div class="hph-archive-no-results <?php echo $archive_args['animation_style'] !== 'none' ? 'hph-animate-fade-in-up' : ''; ?>" role="status" aria-live="polite">
                            <?php
                            get_template_part('template-parts/components/archive-no-results', null, array_merge($archive_args, [
                                'search_query' => get_search_query(),
                                'current_filters' => [],
                                'show_suggestions' => true,
                                'show_clear_filters' => !empty($_GET),
                                'animation_style' => $archive_args['animation_style']
                            ]));
                            ?>
                        </div>
                        
                    <?php endif; ?>
                    
                </div>
                
                <?php if ($archive_args['show_sidebar'] && $archive_args['sidebar_position'] === 'right') : ?>
                    <aside class="<?php echo esc_attr(implode(' ', $sidebar_classes)); ?>" role="complementary" aria-label="<?php esc_attr_e('Archive Sidebar', 'happy-place-theme'); ?>">
                        <?php
                        get_template_part('template-parts/components/archive-sidebar', null, array_merge($archive_args, [
                            'position' => 'right',
                            'sticky' => $archive_args['sidebar_sticky'],
                            'animation_style' => $archive_args['animation_style']
                        ]));
                        ?>
                    </aside>
                <?php endif; ?>
                
            </div>
            
        </div>
    </main>
    
    <?php if ($archive_args['show_related']) : ?>
        <section class="hph-archive-related hph-mt-2xl hph-pt-2xl hph-border-t hph-border-gray-200" role="complementary" aria-labelledby="related-content-title">
            <?php
            get_template_part('template-parts/components/archive-related', null, array_merge($archive_args, [
                'related_type' => 'posts',
                'related_count' => 3,
                'show_title' => true,
                'animation_style' => $archive_args['animation_style']
            ]));
            ?>
        </section>
    <?php endif; ?>
    
    <!-- Execute after archive hook -->
    <?php
    if (is_callable($archive_args['after_archive'])) {
        call_user_func($archive_args['after_archive'], $archive_args);
    }
    
    // Generate structured data if enabled
    if ($archive_args['structured_data']) {
        $structured_data = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $archive_args['title'],
            'description' => $archive_args['description'],
            'url' => get_permalink(),
            'numberOfItems' => $archive_args['total_results']
        ];
        
        if (!empty($posts_array)) {
            $structured_data['mainEntity'] = [];
            foreach (array_slice($posts_array, 0, 10) as $post) {
                $structured_data['mainEntity'][] = [
                    '@type' => 'Article',
                    'headline' => get_the_title($post),
                    'url' => get_permalink($post)
                ];
            }
        }
        
        echo '<script type="application/ld+json">' . wp_json_encode($structured_data, JSON_UNESCAPED_SLASHES) . '</script>';
    }
    ?>
    
</div>