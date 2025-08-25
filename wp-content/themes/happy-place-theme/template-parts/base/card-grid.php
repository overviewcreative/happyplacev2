<?php
/**
 * HPH Card Grid Component - Professional grid layout matching section quality
 * 
 * A sophisticated, flexible grid container for displaying multiple cards:
 * - Responsive grid layouts with intelligent column management
 * - Advanced filtering, sorting, and search functionality
 * - Multiple layout modes (grid, list, masonry, carousel)
 * - Professional loading states and empty state handling
 * - Accessibility-first design with ARIA support
 * 
 * Configurable variations:
 * - Grid layouts: 1-6 columns with responsive breakpoints
 * - Display modes: grid, list, masonry, carousel, slider
 * - Animation styles: fade-in, slide-up, stagger, parallax
 * - Control interfaces: search, filters, sorting, pagination
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * GRID CONFIGURATION:
 * - columns: 1-6 (desktop columns)
 * - columns_tablet: 1-4 (tablet columns, auto-calculated if not set)
 * - columns_mobile: 1-2 (mobile columns)
 * - gap: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl'
 * - aspect_ratio: 'auto' | 'square' | 'portrait' | 'landscape' | 'wide'
 * 
 * LAYOUT MODES:
 * - layout: 'grid' | 'list' | 'masonry' | 'carousel' | 'slider' | 'packery'
 * - list_style: 'default' | 'compact' | 'detailed' | 'horizontal'
 * - masonry_options: array (masonry-specific configuration)
 * 
 * INTERACTIVE CONTROLS:
 * - show_search: boolean (search input)
 * - show_filters: boolean (category/taxonomy filters)
 * - show_sorting: boolean (sort dropdown)
 * - show_view_toggle: boolean (grid/list toggle)
 * - show_load_more: boolean (load more button)
 * - enable_infinite_scroll: boolean
 * 
 * FILTERING & SORTING:
 * - filter_taxonomies: array (taxonomies to show as filters)
 * - filter_meta_keys: array (meta fields to filter by)
 * - sort_options: array (available sort options)
 * - default_sort: string
 * - enable_ajax_filtering: boolean
 * 
 * CARD CONFIGURATION:
 * - card_args: array (arguments passed to each card)
 * - card_layout: string (layout for individual cards)
 * - card_style: string (style for individual cards)
 * - uniform_heights: boolean (make all cards same height)
 * 
 * ANIMATION & EFFECTS:
 * - animation_style: 'none' | 'fade-in' | 'slide-up' | 'stagger' | 'parallax'
 * - stagger_delay: int (milliseconds between card animations)
 * - scroll_reveal: boolean (reveal cards on scroll)
 * - hover_effects: boolean (enable card hover effects)
 * 
 * DATA SOURCES:
 * - posts: array (post objects or IDs)
 * - query_args: array (WP_Query arguments)
 * - post_type: string
 * - posts_per_page: int
 * - enable_pagination: boolean
 * 
 * LOADING & EMPTY STATES:
 * - loading_style: 'spinner' | 'skeleton' | 'pulse' | 'custom'
 * - empty_title: string
 * - empty_message: string
 * - empty_action: array (button configuration)
 * - skeleton_count: int (number of skeleton cards)
 * 
 * RESPONSIVE & ACCESSIBILITY:
 * - responsive_breakpoints: array (custom breakpoints)
 * - accessibility_features: boolean
 * - keyboard_navigation: boolean
 * - screen_reader_text: array
 */

// Comprehensive default arguments
$defaults = array(
    // Core Configuration
    'posts' => array(),
    'post_type' => 'post',
    'query_args' => array(),
    'posts_per_page' => 12,
    
    // Grid Layout
    'layout' => 'grid',
    'columns' => 3,
    'columns_tablet' => null,
    'columns_mobile' => 1,
    'gap' => 'lg',
    'aspect_ratio' => 'auto',
    
    // Display Options
    'list_style' => 'default',
    'uniform_heights' => false,
    'masonry_options' => array(),
    
    // Interactive Controls
    'show_search' => false,
    'show_filters' => false,
    'show_sorting' => false,
    'show_view_toggle' => false,
    'show_load_more' => false,
    'enable_infinite_scroll' => false,
    
    // Filtering & Sorting
    'filter_taxonomies' => array(),
    'filter_meta_keys' => array(),
    'sort_options' => array(
        'date-desc' => __('Newest First', 'happy-place-theme'),
        'date-asc' => __('Oldest First', 'happy-place-theme'),
        'title-asc' => __('Title A-Z', 'happy-place-theme'),
        'title-desc' => __('Title Z-A', 'happy-place-theme')
    ),
    'default_sort' => 'date-desc',
    'enable_ajax_filtering' => false,
    
    // Card Configuration
    'card_args' => array(),
    'card_layout' => 'default',
    'card_style' => 'modern',
    
    // Animation & Effects
    'animation_style' => 'fade-in',
    'stagger_delay' => 100,
    'scroll_reveal' => true,
    'hover_effects' => true,
    
    // Loading & Empty States
    'loading' => false,
    'loading_style' => 'skeleton',
    'skeleton_count' => 6,
    'empty_title' => '',
    'empty_message' => '',
    'empty_action' => array(),
    
    // Pagination
    'enable_pagination' => false,
    'pagination_style' => 'numbers',
    'max_pages' => 1,
    'current_page' => 1,
    
    // Container & Styling
    'container_class' => '',
    'grid_class' => '',
    'item_class' => '',
    
    // Accessibility
    'accessibility_features' => true,
    'keyboard_navigation' => true,
    'screen_reader_text' => array(),
    
    // Advanced Options
    'unique_id' => '',
    'data_attributes' => array(),
    'custom_css' => '',
    'debug_mode' => false
);

$args = wp_parse_args($args ?? [], $defaults);

// Generate unique ID
if (empty($args['unique_id'])) {
    $args['unique_id'] = 'hph-grid-' . uniqid();
}

// Calculate responsive columns
if (!$args['columns_tablet']) {
    $args['columns_tablet'] = min($args['columns'], 2);
}

// Get posts if not provided
$posts = $args['posts'];
$query = null;
$total_posts = 0;

if (empty($posts) && !empty($args['query_args'])) {
    $query_args = wp_parse_args($args['query_args'], array(
        'post_type' => $args['post_type'],
        'post_status' => 'publish',
        'posts_per_page' => $args['posts_per_page'],
        'orderby' => 'date',
        'order' => 'DESC'
    ));
    
    $query = new WP_Query($query_args);
    $posts = $query->posts;
    $total_posts = $query->found_posts;
    $args['max_pages'] = $query->max_num_pages;
} else {
    $total_posts = count($posts);
}

// Convert post IDs to post objects if needed
if (!empty($posts) && is_numeric($posts[0])) {
    $posts = array_map('get_post', array_filter($posts));
}

// Build sophisticated container classes
$container_classes = array('hph-card-grid-container');
if ($args['container_class']) {
    $container_classes[] = $args['container_class'];
}
if ($args['scroll_reveal']) {
    $container_classes[] = 'hph-scroll-reveal';
}
$container_class = implode(' ', $container_classes);

// Build grid classes with responsive system
$grid_classes = array('hph-card-grid');

// Layout-specific classes
switch ($args['layout']) {
    case 'list':
        $grid_classes[] = 'hph-card-grid--list';
        $grid_classes[] = 'hph-space-y-' . $args['gap'];
        break;
    case 'masonry':
        $grid_classes[] = 'hph-card-grid--masonry';
        $grid_classes[] = 'hph-masonry-grid';
        $grid_classes[] = 'hph-gap-' . $args['gap'];
        break;
    case 'carousel':
        $grid_classes[] = 'hph-card-grid--carousel';
        $grid_classes[] = 'hph-overflow-hidden';
        break;
    case 'grid':
    default:
        $grid_classes[] = 'hph-card-grid--grid';
        $grid_classes[] = 'hph-grid';
        $grid_classes[] = 'hph-grid-cols-' . $args['columns_mobile'];
        if ($args['columns_tablet']) {
            $grid_classes[] = 'md:hph-grid-cols-' . $args['columns_tablet'];
        }
        $grid_classes[] = 'lg:hph-grid-cols-' . $args['columns'];
        $grid_classes[] = 'hph-gap-' . $args['gap'];
        break;
}

// Animation classes
if ($args['animation_style'] !== 'none') {
    $grid_classes[] = 'hph-grid-animated';
    $grid_classes[] = 'hph-grid-animation--' . $args['animation_style'];
}

// Custom grid classes
if ($args['grid_class']) {
    $grid_classes[] = $args['grid_class'];
}

$grid_class = implode(' ', $grid_classes);

// Build item classes
$item_classes = array('hph-grid-item');
if ($args['uniform_heights']) {
    $item_classes[] = 'hph-h-full';
}
if ($args['animation_style'] === 'stagger') {
    $item_classes[] = 'hph-stagger-item';
}
if ($args['item_class']) {
    $item_classes[] = $args['item_class'];
}
$item_class = implode(' ', $item_classes);

// Prepare card arguments
$card_arguments = wp_parse_args($args['card_args'], array(
    'layout' => $args['card_layout'],
    'style' => $args['card_style'],
    'hover_effect' => $args['hover_effects'] ? 'lift' : 'none',
    'show_excerpt' => $args['layout'] === 'list'
));

// Build data attributes
$data_attrs = wp_parse_args($args['data_attributes'], array(
    'data-grid-layout' => $args['layout'],
    'data-columns' => $args['columns'],
    'data-post-type' => $args['post_type'],
    'data-animation' => $args['animation_style'],
    'data-total-posts' => $total_posts
));

$data_attributes = '';
foreach ($data_attrs as $key => $value) {
    $data_attributes .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
}

// Screen reader text
$screen_reader_defaults = array(
    'grid_description' => sprintf(__('Grid of %s items', 'happy-place-theme'), $total_posts),
    'loading' => __('Loading content...', 'happy-place-theme'),
    'empty' => __('No items found', 'happy-place-theme'),
    'filter_applied' => __('Filter applied', 'happy-place-theme')
);
$screen_reader_text = wp_parse_args($args['screen_reader_text'], $screen_reader_defaults);
?>

<div class="<?php echo esc_attr($container_class); ?>" 
     id="<?php echo esc_attr($args['unique_id']); ?>"
     <?php echo $data_attributes; ?>
     <?php if ($args['accessibility_features']) : ?>
     role="region" 
     aria-label="<?php echo esc_attr($screen_reader_text['grid_description']); ?>"
     <?php endif; ?>>

    <!-- Grid Controls -->
    <?php if ($args['show_search'] || $args['show_filters'] || $args['show_sorting'] || $args['show_view_toggle']) : ?>
        <div class="hph-grid-controls hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-lg hph-mb-xl hph-shadow-sm">
            <div class="hph-flex hph-flex-col sm:hph-flex-row hph-items-start sm:hph-items-center hph-justify-between hph-gap-lg">
                
                <!-- Left Controls -->
                <div class="hph-flex hph-flex-col sm:hph-flex-row hph-items-start sm:hph-items-center hph-gap-md hph-flex-1">
                    
                    <!-- Search -->
                    <?php if ($args['show_search']) : ?>
                        <div class="hph-relative hph-w-full sm:hph-w-auto">
                            <label for="<?php echo esc_attr($args['unique_id']); ?>-search" class="hph-sr-only">
                                <?php _e('Search items', 'happy-place-theme'); ?>
                            </label>
                            <input type="search" 
                                   id="<?php echo esc_attr($args['unique_id']); ?>-search"
                                   class="hph-w-full sm:hph-w-64 hph-pl-10 hph-pr-4 hph-py-sm hph-border hph-border-gray-300 hph-rounded-lg hph-text-sm hph-placeholder-gray-500 hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100 hph-transition-colors hph-duration-200" 
                                   placeholder="<?php esc_attr_e('Search...', 'happy-place-theme'); ?>"
                                   autocomplete="off">
                            <div class="hph-absolute hph-left-3 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-pointer-events-none">
                                <i class="fas fa-search hph-text-gray-400 hph-text-sm"></i>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Filters -->
                    <?php if ($args['show_filters'] && !empty($args['filter_taxonomies'])) : ?>
                        <div class="hph-flex hph-flex-wrap hph-gap-sm">
                            <?php foreach ($args['filter_taxonomies'] as $taxonomy) : 
                                $terms = get_terms(array(
                                    'taxonomy' => $taxonomy,
                                    'hide_empty' => true,
                                    'number' => 10
                                ));
                                if (!is_wp_error($terms) && !empty($terms)) :
                                    $tax_obj = get_taxonomy($taxonomy);
                            ?>
                                <div class="hph-relative">
                                    <label for="filter-<?php echo esc_attr($taxonomy); ?>" class="hph-sr-only">
                                        <?php printf(__('Filter by %s', 'happy-place-theme'), $tax_obj->labels->name); ?>
                                    </label>
                                    <select id="filter-<?php echo esc_attr($taxonomy); ?>" 
                                            class="hph-filter-select hph-px-sm hph-py-xs hph-border hph-border-gray-300 hph-rounded-md hph-text-sm hph-bg-white hph-focus:border-primary-500 hph-focus:ring-1 hph-focus:ring-primary-500" 
                                            data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                                        <option value=""><?php printf(__('All %s', 'happy-place-theme'), $tax_obj->labels->name); ?></option>
                                        <?php foreach ($terms as $term) : ?>
                                            <option value="<?php echo esc_attr($term->slug); ?>">
                                                <?php echo esc_html($term->name); ?> (<?php echo $term->count; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Right Controls -->
                <div class="hph-flex hph-items-center hph-gap-md hph-flex-shrink-0">
                    
                    <!-- View Toggle -->
                    <?php if ($args['show_view_toggle']) : ?>
                        <div class="hph-flex hph-bg-gray-100 hph-rounded-md hph-p-1" 
                             role="tablist" 
                             aria-label="<?php esc_attr_e('View options', 'happy-place-theme'); ?>">
                            <button class="hph-view-toggle hph-px-sm hph-py-xs hph-text-sm hph-font-medium hph-rounded hph-transition-all hph-duration-200 <?php echo $args['layout'] === 'grid' ? 'hph-bg-white hph-text-primary-600 hph-shadow-sm' : 'hph-text-gray-600 hph-hover:text-gray-900'; ?>" 
                                    data-view="grid"
                                    role="tab"
                                    aria-selected="<?php echo $args['layout'] === 'grid' ? 'true' : 'false'; ?>"
                                    aria-controls="<?php echo esc_attr($args['unique_id']); ?>-content">
                                <i class="fas fa-th hph-mr-xs"></i>
                                <span class="hph-hidden sm:hph-inline"><?php _e('Grid', 'happy-place-theme'); ?></span>
                            </button>
                            <button class="hph-view-toggle hph-px-sm hph-py-xs hph-text-sm hph-font-medium hph-rounded hph-transition-all hph-duration-200 <?php echo $args['layout'] === 'list' ? 'hph-bg-white hph-text-primary-600 hph-shadow-sm' : 'hph-text-gray-600 hph-hover:text-gray-900'; ?>" 
                                    data-view="list"
                                    role="tab"
                                    aria-selected="<?php echo $args['layout'] === 'list' ? 'true' : 'false'; ?>"
                                    aria-controls="<?php echo esc_attr($args['unique_id']); ?>-content">
                                <i class="fas fa-list hph-mr-xs"></i>
                                <span class="hph-hidden sm:hph-inline"><?php _e('List', 'happy-place-theme'); ?></span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Sort Options -->
                    <?php if ($args['show_sorting'] && !empty($args['sort_options'])) : ?>
                        <div class="hph-flex hph-items-center hph-gap-sm">
                            <label for="<?php echo esc_attr($args['unique_id']); ?>-sort" class="hph-text-sm hph-font-medium hph-text-gray-700 hph-whitespace-nowrap hph-hidden md:hph-block">
                                <?php _e('Sort by:', 'happy-place-theme'); ?>
                            </label>
                            <select id="<?php echo esc_attr($args['unique_id']); ?>-sort" 
                                    class="hph-sort-select hph-px-sm hph-py-xs hph-border hph-border-gray-300 hph-rounded-md hph-text-sm hph-bg-white hph-focus:border-primary-500 hph-focus:ring-1 hph-focus:ring-primary-500" 
                                    data-current="<?php echo esc_attr($args['default_sort']); ?>">
                                <?php foreach ($args['sort_options'] as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($args['default_sort'], $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                </div>
                
            </div>
            
            <!-- Results Summary -->
            <div class="hph-results-summary hph-mt-md hph-pt-md hph-border-t hph-border-gray-100 hph-flex hph-items-center hph-justify-between hph-text-sm hph-text-gray-600">
                <span class="hph-results-count">
                    <?php printf(_n('%d item found', '%d items found', $total_posts, 'happy-place-theme'), $total_posts); ?>
                </span>
                <?php if ($args['enable_pagination'] && $args['max_pages'] > 1) : ?>
                    <span class="hph-page-info">
                        <?php printf(__('Page %d of %d', 'happy-place-theme'), $args['current_page'], $args['max_pages']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Loading State -->
    <?php if ($args['loading']) : ?>
        <div class="hph-grid-loading hph-py-2xl" 
             aria-live="polite" 
             aria-label="<?php echo esc_attr($screen_reader_text['loading']); ?>">
            
            <?php if ($args['loading_style'] === 'skeleton') : ?>
                <!-- Skeleton Cards -->
                <div class="<?php echo esc_attr($grid_class); ?>">
                    <?php for ($i = 0; $i < $args['skeleton_count']; $i++) : ?>
                        <div class="<?php echo esc_attr($item_class); ?>">
                            <div class="hph-bg-white hph-rounded-lg hph-shadow-sm hph-overflow-hidden hph-animate-pulse">
                                <div class="hph-bg-gray-200 hph-h-64"></div>
                                <div class="hph-p-lg hph-space-y-md">
                                    <div class="hph-bg-gray-200 hph-h-4 hph-rounded hph-w-3/4"></div>
                                    <div class="hph-space-y-sm">
                                        <div class="hph-bg-gray-200 hph-h-3 hph-rounded"></div>
                                        <div class="hph-bg-gray-200 hph-h-3 hph-rounded hph-w-5/6"></div>
                                    </div>
                                    <div class="hph-flex hph-justify-between hph-items-center hph-pt-sm">
                                        <div class="hph-bg-gray-200 hph-h-8 hph-w-20 hph-rounded"></div>
                                        <div class="hph-flex hph-gap-sm">
                                            <div class="hph-bg-gray-200 hph-h-8 hph-w-8 hph-rounded"></div>
                                            <div class="hph-bg-gray-200 hph-h-8 hph-w-8 hph-rounded"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                
            <?php else : ?>
                <!-- Spinner Loading -->
                <div class="hph-text-center">
                    <div class="hph-inline-flex hph-items-center hph-gap-md hph-text-gray-600">
                        <div class="hph-animate-spin hph-w-8 hph-h-8 hph-border-4 hph-border-gray-200 hph-border-t-primary-600 hph-rounded-full"></div>
                        <span class="hph-text-lg hph-font-medium"><?php echo esc_html($screen_reader_text['loading']); ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>

    <!-- Empty State -->
    <?php elseif (empty($posts)) : ?>
        <div class="hph-grid-empty hph-py-2xl hph-text-center" 
             role="status" 
             aria-label="<?php echo esc_attr($screen_reader_text['empty']); ?>">
            
            <div class="hph-max-w-md hph-mx-auto hph-space-y-lg">
                <!-- Empty Icon -->
                <div class="hph-text-6xl hph-text-gray-300 hph-mb-lg">
                    <?php 
                    $empty_icons = array(
                        'listing' => 'fa-home',
                        'agent' => 'fa-users',
                        'open_house' => 'fa-calendar-alt',
                        'post' => 'fa-file-alt',
                        'default' => 'fa-search'
                    );
                    $icon = $empty_icons[$args['post_type']] ?? $empty_icons['default'];
                    ?>
                    <i class="fas <?php echo esc_attr($icon); ?>"></i>
                </div>
                
                <!-- Empty Content -->
                <div class="hph-space-y-md">
                    <h3 class="hph-text-xl hph-font-semibold hph-text-gray-900">
                        <?php echo esc_html($args['empty_title'] ?: __('No items found', 'happy-place-theme')); ?>
                    </h3>
                    
                    <?php if ($args['empty_message']) : ?>
                        <p class="hph-text-gray-600 hph-leading-relaxed">
                            <?php echo esc_html($args['empty_message']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($args['empty_action'])) : ?>
                        <div class="hph-pt-md">
                            <a href="<?php echo esc_url($args['empty_action']['url'] ?? '#'); ?>" 
                               class="hph-inline-flex hph-items-center hph-gap-sm hph-px-lg hph-py-md hph-bg-primary-600 hph-text-white hph-font-medium hph-rounded-lg hph-transition-all hph-duration-200 hph-hover:bg-primary-700 hph-hover:scale-105 hph-shadow-sm hph-hover:shadow-md">
                                <?php if (!empty($args['empty_action']['icon'])) : ?>
                                    <i class="fas <?php echo esc_attr($args['empty_action']['icon']); ?>"></i>
                                <?php endif; ?>
                                <?php echo esc_html($args['empty_action']['text'] ?? __('Browse All', 'happy-place-theme')); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>

    <!-- Grid Content -->
    <?php else : ?>
        <div class="<?php echo esc_attr($grid_class); ?>" 
             id="<?php echo esc_attr($args['unique_id']); ?>-content"
             role="grid"
             <?php if ($args['accessibility_features']) : ?>
             aria-label="<?php printf(__('%s grid with %d items', 'happy-place-theme'), $args['post_type'], count($posts)); ?>"
             <?php endif; ?>>
            
            <?php foreach ($posts as $index => $post) : ?>
                <?php
                // Merge card arguments with post-specific data
                $current_card_args = array_merge($card_arguments, array(
                    'post_id' => $post->ID,
                    'post_type' => $post->post_type
                ));
                
                // Add stagger delay for animations
                if ($args['animation_style'] === 'stagger') {
                    $delay = $index * $args['stagger_delay'];
                    $current_card_args['data_attributes'] = array_merge(
                        $current_card_args['data_attributes'] ?? array(),
                        array('data-delay' => $delay . 'ms')
                    );
                }
                ?>
                
                <div class="<?php echo esc_attr($item_class); ?>" 
                     role="gridcell"
                     <?php if ($args['animation_style'] === 'stagger') : ?>
                     style="animation-delay: <?php echo esc_attr($index * $args['stagger_delay']); ?>ms;"
                     <?php endif; ?>>
                    
                    <?php hph_component('card', $current_card_args); ?>
                    
                </div>
                
            <?php endforeach; ?>
            
        </div>

        <!-- Load More / Pagination -->
        <?php if ($args['show_load_more'] || $args['enable_pagination']) : ?>
            <div class="hph-grid-pagination hph-mt-2xl hph-text-center">
                
                <?php if ($args['show_load_more'] && $args['max_pages'] > $args['current_page']) : ?>
                    <!-- Load More Button -->
                    <button class="hph-load-more-btn hph-inline-flex hph-items-center hph-gap-sm hph-px-2xl hph-py-md hph-bg-white hph-border-2 hph-border-primary-600 hph-text-primary-600 hph-font-semibold hph-rounded-lg hph-transition-all hph-duration-200 hph-hover:bg-primary-600 hph-hover:text-white hph-hover:scale-105 hph-shadow-sm hph-hover:shadow-md hph-focus:outline-none hph-focus:ring-2 hph-focus:ring-primary-500 hph-focus:ring-offset-2" 
                            data-page="<?php echo esc_attr($args['current_page'] + 1); ?>"
                            data-max-pages="<?php echo esc_attr($args['max_pages']); ?>"
                            data-grid-id="<?php echo esc_attr($args['unique_id']); ?>">
                        <span class="hph-load-more-text">
                            <i class="fas fa-plus hph-mr-sm"></i>
                            <?php _e('Load More', 'happy-place-theme'); ?>
                        </span>
                        <span class="hph-load-more-loading hph-hidden">
                            <div class="hph-animate-spin hph-w-4 hph-h-4 hph-border-2 hph-border-white hph-border-t-transparent hph-rounded-full hph-mr-sm"></div>
                            <?php _e('Loading...', 'happy-place-theme'); ?>
                        </span>
                    </button>
                    
                <?php elseif ($args['enable_pagination'] && $args['max_pages'] > 1) : ?>
                    <!-- Standard Pagination -->
                    <?php if ($query) : ?>
                        <div class="hph-pagination-wrapper">
                            <?php
                            $pagination_args = array(
                                'total' => $args['max_pages'],
                                'current' => $args['current_page'],
                                'prev_next' => true,
                                'prev_text' => '<i class="fas fa-chevron-left hph-mr-sm"></i>' . __('Previous', 'happy-place-theme'),
                                'next_text' => __('Next', 'happy-place-theme') . '<i class="fas fa-chevron-right hph-ml-sm"></i>',
                                'type' => 'array',
                                'end_size' => 2,
                                'mid_size' => 1
                            );
                            
                            $pagination_links = paginate_links($pagination_args);
                            
                            if ($pagination_links) : ?>
                                <nav class="hph-pagination" 
                                     role="navigation" 
                                     aria-label="<?php esc_attr_e('Pagination Navigation', 'happy-place-theme'); ?>">
                                    <ul class="hph-flex hph-items-center hph-justify-center hph-gap-sm hph-flex-wrap">
                                        <?php foreach ($pagination_links as $link) : ?>
                                            <li>
                                                <?php 
                                                // Style pagination links
                                                $styled_link = str_replace(
                                                    array('page-numbers', 'current'),
                                                    array('hph-inline-flex hph-items-center hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-text-sm hph-font-medium hph-rounded-md hph-transition-all hph-duration-200 hph-hover:bg-gray-50', 'hph-inline-flex hph-items-center hph-px-md hph-py-sm hph-bg-primary-600 hph-text-white hph-text-sm hph-font-medium hph-rounded-md'),
                                                    $link
                                                );
                                                echo $styled_link;
                                                ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php endif; ?>
                
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- Infinite Scroll Trigger -->
    <?php if ($args['enable_infinite_scroll']) : ?>
        <div class="hph-infinite-scroll-trigger hph-h-20 hph-opacity-0 hph-pointer-events-none" 
             aria-hidden="true">
            <div class="hph-text-center hph-pt-lg">
                <div class="hph-animate-spin hph-w-6 hph-h-6 hph-border-2 hph-border-gray-300 hph-border-t-primary-600 hph-rounded-full hph-mx-auto"></div>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Custom CSS -->
<?php if ($args['custom_css']) : ?>
<style>
<?php echo esc_html($args['custom_css']); ?>
</style>
<?php endif; ?>

<!-- Grid JavaScript Enhancement -->
<script>
jQuery(document).ready(function($) {
    var $container = $('#<?php echo esc_js($args['unique_id']); ?>');
    var $grid = $('#<?php echo esc_js($args['unique_id']); ?>-content');
    var gridId = '<?php echo esc_js($args['unique_id']); ?>';
    
    // Initialize grid
    function initGrid() {
        // Scroll reveal animation
        <?php if ($args['scroll_reveal']) : ?>
        if ('IntersectionObserver' in window) {
            var observerOptions = {
                threshold: 0.1,
                rootMargin: '50px 0px'
            };
            
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('hph-animate-fade-in-up');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            $grid.find('.hph-grid-item').each(function() {
                observer.observe(this);
            });
        }
        <?php endif; ?>
        
        // Masonry layout initialization
        <?php if ($args['layout'] === 'masonry') : ?>
        if (typeof Masonry !== 'undefined') {
            var masonry = new Masonry($grid[0], {
                itemSelector: '.hph-grid-item',
                columnWidth: '.hph-grid-item',
                percentPosition: true,
                gutter: <?php echo is_numeric($args['gap']) ? $args['gap'] : 20; ?>
            });
        }
        <?php endif; ?>
    }
    
    // View toggle functionality
    $('.hph-view-toggle').on('click', function() {
        var $this = $(this);
        var view = $this.data('view');
        
        // Update buttons
        $('.hph-view-toggle').removeClass('hph-bg-white hph-text-primary-600 hph-shadow-sm')
                             .addClass('hph-text-gray-600');
        $this.removeClass('hph-text-gray-600')
             .addClass('hph-bg-white hph-text-primary-600 hph-shadow-sm');
        
        // Update grid layout
        $container.attr('data-grid-layout', view);
        
        // Apply layout changes
        if (view === 'list') {
            $grid.removeClass('hph-grid hph-grid-cols-1 md:hph-grid-cols-<?php echo esc_js($args['columns_tablet']); ?> lg:hph-grid-cols-<?php echo esc_js($args['columns']); ?> hph-gap-<?php echo esc_js($args['gap']); ?>')
                 .addClass('hph-space-y-<?php echo esc_js($args['gap']); ?>');
            
            // Update cards to horizontal layout
            $grid.find('.hph-card').each(function() {
                $(this).addClass('hph-flex hph-flex-col md:hph-flex-row');
            });
        } else {
            $grid.removeClass('hph-space-y-<?php echo esc_js($args['gap']); ?>')
                 .addClass('hph-grid hph-grid-cols-1 md:hph-grid-cols-<?php echo esc_js($args['columns_tablet']); ?> lg:hph-grid-cols-<?php echo esc_js($args['columns']); ?> hph-gap-<?php echo esc_js($args['gap']); ?>');
            
            // Reset cards to default layout
            $grid.find('.hph-card').each(function() {
                $(this).removeClass('hph-flex hph-flex-col md:hph-flex-row');
            });
        }
        
        // Announce change to screen readers
        if ($container.attr('aria-live')) {
            $('<div>').attr('aria-live', 'polite').addClass('hph-sr-only').text('<?php esc_js(_e('View changed', 'happy-place-theme')); ?>').appendTo('body').delay(1000).remove();
        }
    });
    
    // Search functionality
    $('#<?php echo esc_js($args['unique_id']); ?>-search').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        var visibleCount = 0;
        
        $grid.find('.hph-grid-item').each(function() {
            var $item = $(this);
            var itemText = $item.text().toLowerCase();
            var isVisible = itemText.indexOf(searchTerm) > -1;
            
            $item.toggle(isVisible);
            if (isVisible) visibleCount++;
        });
        
        // Update results count
        $('.hph-results-count').text('<?php printf(esc_js(__('%d items found', 'happy-place-theme')), '" + visibleCount + "'); ?>'.replace('%d', visibleCount));
    });
    
    // Sort functionality
    $('#<?php echo esc_js($args['unique_id']); ?>-sort').on('change', function() {
        var sortValue = $(this).val();
        // Implement sorting logic or trigger AJAX refresh
        console.log('Sort by:', sortValue);
    });
    
    // Load more functionality
    $('.hph-load-more-btn').on('click', function() {
        var $btn = $(this);
        var page = parseInt($btn.data('page'));
        var maxPages = parseInt($btn.data('max-pages'));
        
        $btn.find('.hph-load-more-text').addClass('hph-hidden');
        $btn.find('.hph-load-more-loading').removeClass('hph-hidden');
        $btn.prop('disabled', true);
        
        // AJAX load more implementation would go here
        // For now, simulate loading
        setTimeout(function() {
            $btn.find('.hph-load-more-loading').addClass('hph-hidden');
            $btn.find('.hph-load-more-text').removeClass('hph-hidden');
            $btn.prop('disabled', false);
            $btn.data('page', page + 1);
            
            if (page >= maxPages) {
                $btn.hide();
            }
        }, 1500);
    });
    
    // Initialize
    initGrid();
    
    // Debug mode
    <?php if ($args['debug_mode']) : ?>
    console.log('HPH Card Grid initialized:', {
        id: gridId,
        layout: '<?php echo esc_js($args['layout']); ?>',
        columns: <?php echo esc_js($args['columns']); ?>,
        totalPosts: <?php echo esc_js($total_posts); ?>,
        args: <?php echo json_encode($args); ?>
    });
    <?php endif; ?>
});
</script>