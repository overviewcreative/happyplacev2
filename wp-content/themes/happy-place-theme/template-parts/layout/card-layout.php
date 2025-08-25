<?php
/**
 * HPH Card Layout Manager Template
 * 
 * A flexible layout manager for displaying cards in different views:
 * - Grid layout with responsive columns
 * - List layout with enhanced details
 * - Map layout with sidebar listing
 * - Masonry layout for varied heights
 * - Carousel/slider layout
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - layout: 'grid' | 'list' | 'map' | 'masonry' | 'carousel'
 * - columns: array('mobile' => 1, 'tablet' => 2, 'desktop' => 3, 'wide' => 4)
 * - gap: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl'
 * - items: array of items or WP_Query
 * - card_args: array of default args to pass to card template
 * - show_controls: boolean - show layout switcher
 * - show_filters: boolean - show filter controls
 * - show_sort: boolean - show sort controls
 * - show_pagination: boolean
 * - items_per_page: int
 * - infinite_scroll: boolean
 * - map_args: array (for map layout)
 *   - center_lat: float
 *   - center_lng: float
 *   - zoom: int
 *   - map_style: 'streets' | 'satellite' | 'hybrid' | 'terrain'
 *   - cluster_markers: boolean
 * - filters: array of filter configurations
 * - sort_options: array of sort options
 * - empty_message: string
 * - loading_message: string
 * - container_classes: string
 * - container_id: string
 * - ajax_enabled: boolean
 * - animate_cards: boolean
 */

// Default arguments
$defaults = array(
    'layout' => 'grid',
    'columns' => array(
        'mobile' => 1,
        'tablet' => 2,
        'desktop' => 3,
        'wide' => 4
    ),
    'gap' => 'lg',
    'items' => array(),
    'card_args' => array(),
    'show_controls' => true,
    'show_filters' => false,
    'show_sort' => false,
    'show_pagination' => true,
    'items_per_page' => 12,
    'infinite_scroll' => false,
    'map_args' => array(
        'center_lat' => 38.7296,
        'center_lng' => -75.1327, // Milton, Delaware
        'zoom' => 12,
        'map_style' => 'streets',
        'cluster_markers' => true
    ),
    'filters' => array(),
    'sort_options' => array(
        'date_desc' => 'Newest First',
        'date_asc' => 'Oldest First',
        'price_asc' => 'Price: Low to High',
        'price_desc' => 'Price: High to Low',
        'title_asc' => 'Title: A-Z',
        'title_desc' => 'Title: Z-A'
    ),
    'empty_message' => 'No items found.',
    'loading_message' => 'Loading...',
    'container_classes' => '',
    'container_id' => '',
    'ajax_enabled' => true,
    'animate_cards' => true
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Extract configuration
$layout = $config['layout'];
$columns = $config['columns'];
$gap = $config['gap'];
$items = $config['items'];
$card_args = $config['card_args'];
$show_controls = $config['show_controls'];
$show_filters = $config['show_filters'];
$show_sort = $config['show_sort'];
$show_pagination = $config['show_pagination'];
$items_per_page = $config['items_per_page'];
$infinite_scroll = $config['infinite_scroll'];
$map_args = wp_parse_args($config['map_args'], $defaults['map_args']);
$filters = $config['filters'];
$sort_options = $config['sort_options'];
$empty_message = $config['empty_message'];
$loading_message = $config['loading_message'];
$container_classes = $config['container_classes'];
$container_id = $config['container_id'] ?: 'hph-layout-' . wp_generate_uuid4();
$ajax_enabled = $config['ajax_enabled'];
$animate_cards = $config['animate_cards'];

// Process items if WP_Query
$posts_array = array();
if ($items instanceof WP_Query) {
    while ($items->have_posts()) {
        $items->the_post();
        $posts_array[] = get_post();
    }
    wp_reset_postdata();
    $items = $posts_array;
}

// Build container classes
$container_class_array = array(
    'hph-card-layout',
    'hph-layout-' . $layout,
    'hph-gap-' . $gap
);

if ($container_classes) {
    $container_class_array[] = $container_classes;
}

if ($ajax_enabled) {
    $container_class_array[] = 'hph-ajax-enabled';
}

// Set up card defaults based on layout
$layout_card_defaults = array();
switch ($layout) {
    case 'list':
        $layout_card_defaults = array(
            'layout' => 'horizontal',
            'size' => 'lg',
            'image_position' => 'left'
        );
        break;
    case 'map':
        $layout_card_defaults = array(
            'layout' => 'compact',
            'size' => 'sm'
        );
        break;
    case 'carousel':
        $layout_card_defaults = array(
            'size' => 'md',
            'hover_effect' => 'scale'
        );
        break;
}

// Merge layout defaults with provided card args
$card_args = wp_parse_args($card_args, $layout_card_defaults);

// Generate unique namespace for this instance
$instance_id = 'hph_layout_' . substr(md5(uniqid()), 0, 8);

// Prepare data attributes for AJAX
$data_attrs = array(
    'data-layout' => $layout,
    'data-instance' => $instance_id,
    'data-page' => 1,
    'data-per-page' => $items_per_page,
    'data-total' => count($items)
);

if ($infinite_scroll) {
    $data_attrs['data-infinite'] = 'true';
}

// Ensure required scripts are loaded
if ($show_controls || $ajax_enabled) {
    wp_enqueue_script('hph-card-layout', get_template_directory_uri() . '/assets/js/card-layout.js', array('jquery'), '1.0.0', true);
    wp_localize_script('hph-card-layout', 'hph_layout_' . $instance_id, array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hph_layout_nonce')
    ));
}

if ($layout === 'map') {
    // Enqueue map scripts (customize based on your map provider)
    wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initHPHMaps', array(), null, true);
}

if ($layout === 'carousel') {
    // Enqueue carousel library (e.g., Swiper)
    wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
    wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true);
}

// Ensure Font Awesome is loaded
if (!wp_script_is('font-awesome', 'enqueued')) {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}
?>

<div 
    id="<?php echo esc_attr($container_id); ?>"
    class="<?php echo esc_attr(implode(' ', $container_class_array)); ?>"
    <?php foreach ($data_attrs as $attr => $value): ?>
        <?php echo esc_attr($attr); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
>
    
    <?php if ($show_controls || $show_filters || $show_sort): ?>
    <!-- Controls Bar -->
    <div class="hph-layout-controls">
        
        <?php if ($show_filters && !empty($filters)): ?>
        <!-- Filters -->
        <div class="hph-layout-filters">
            <?php foreach ($filters as $filter): 
                $filter_defaults = array(
                    'type' => 'select',
                    'name' => '',
                    'label' => '',
                    'options' => array(),
                    'placeholder' => 'All'
                );
                $filter = wp_parse_args($filter, $filter_defaults);
            ?>
            <div class="hph-filter-item">
                <?php if ($filter['label']): ?>
                <label class="hph-filter-label"><?php echo esc_html($filter['label']); ?></label>
                <?php endif; ?>
                
                <?php if ($filter['type'] === 'select'): ?>
                <select class="hph-filter-select" data-filter="<?php echo esc_attr($filter['name']); ?>">
                    <option value=""><?php echo esc_html($filter['placeholder']); ?></option>
                    <?php foreach ($filter['options'] as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php elseif ($filter['type'] === 'checkbox'): ?>
                <div class="hph-filter-checkboxes">
                    <?php foreach ($filter['options'] as $value => $label): ?>
                    <label class="hph-checkbox-label">
                        <input type="checkbox" class="hph-filter-checkbox" data-filter="<?php echo esc_attr($filter['name']); ?>" value="<?php echo esc_attr($value); ?>">
                        <span><?php echo esc_html($label); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php elseif ($filter['type'] === 'range'): ?>
                <div class="hph-filter-range" data-filter="<?php echo esc_attr($filter['name']); ?>">
                    <input type="range" class="hph-range-min" min="<?php echo esc_attr($filter['min']); ?>" max="<?php echo esc_attr($filter['max']); ?>" value="<?php echo esc_attr($filter['min']); ?>">
                    <input type="range" class="hph-range-max" min="<?php echo esc_attr($filter['min']); ?>" max="<?php echo esc_attr($filter['max']); ?>" value="<?php echo esc_attr($filter['max']); ?>">
                    <div class="hph-range-values">
                        <span class="hph-range-min-value"><?php echo esc_html($filter['min']); ?></span>
                        <span>-</span>
                        <span class="hph-range-max-value"><?php echo esc_html($filter['max']); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
            <button class="hph-filter-clear hph-btn hph-btn-ghost hph-btn-sm">
                <i class="fas fa-times"></i>
                Clear Filters
            </button>
        </div>
        <?php endif; ?>
        
        <?php if ($show_sort && !empty($sort_options)): ?>
        <!-- Sort Controls -->
        <div class="hph-layout-sort">
            <label class="hph-sort-label">Sort by:</label>
            <select class="hph-sort-select">
                <?php foreach ($sort_options as $value => $label): ?>
                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <?php if ($show_controls): ?>
        <!-- Layout Switcher -->
        <div class="hph-layout-switcher">
            <button class="hph-layout-btn <?php echo $layout === 'grid' ? 'active' : ''; ?>" data-layout="grid" aria-label="Grid view">
                <i class="fas fa-th"></i>
            </button>
            <button class="hph-layout-btn <?php echo $layout === 'list' ? 'active' : ''; ?>" data-layout="list" aria-label="List view">
                <i class="fas fa-list"></i>
            </button>
            <?php if ($layout === 'map' || !empty($map_args['enabled'])): ?>
            <button class="hph-layout-btn <?php echo $layout === 'map' ? 'active' : ''; ?>" data-layout="map" aria-label="Map view">
                <i class="fas fa-map"></i>
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
    </div>
    <?php endif; ?>
    
    <!-- Layout Container -->
    <div class="hph-layout-container hph-layout-<?php echo esc_attr($layout); ?>">
        
        <?php if ($layout === 'map'): ?>
        <!-- Map Layout -->
        <div class="hph-map-container">
            <div class="hph-map-sidebar">
                <div class="hph-map-results">
                    <?php if (empty($items)): ?>
                    <div class="hph-empty-state">
                        <i class="fas fa-search"></i>
                        <p><?php echo esc_html($empty_message); ?></p>
                    </div>
                    <?php else: ?>
                    <div class="hph-map-cards">
                        <?php foreach (array_slice($items, 0, $items_per_page) as $index => $item): ?>
                            <?php 
                            $item_card_args = $card_args;
                            if (is_object($item) && isset($item->ID)) {
                                $item_card_args['listing_id'] = $item->ID;
                                $item_card_args['style'] = 'property';
                            }
                            if ($animate_cards) {
                                $item_card_args['animate'] = true;
                                $item_card_args['animation_delay'] = (string)($index * 50);
                            }
                            ?>
                            <div class="hph-map-card-wrapper" data-marker-id="marker-<?php echo esc_attr($item->ID ?? $index); ?>">
                                <?php get_template_part('templates/components/card', null, $item_card_args); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hph-map-canvas" id="<?php echo esc_attr($container_id); ?>-map"></div>
        </div>
        
        <?php elseif ($layout === 'carousel'): ?>
        <!-- Carousel Layout -->
        <div class="hph-carousel-container swiper">
            <div class="swiper-wrapper">
                <?php foreach ($items as $index => $item): ?>
                    <?php 
                    $item_card_args = $card_args;
                    if (is_object($item) && isset($item->ID)) {
                        $item_card_args['listing_id'] = $item->ID;
                        $item_card_args['style'] = 'property';
                    }
                    ?>
                    <div class="swiper-slide">
                        <?php get_template_part('templates/components/card', null, $item_card_args); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
        
        <?php else: ?>
        <!-- Grid/List/Masonry Layout -->
        <div class="hph-cards-container hph-grid-cols-<?php echo esc_attr($columns['mobile']); ?> hph-md-grid-cols-<?php echo esc_attr($columns['tablet']); ?> hph-lg-grid-cols-<?php echo esc_attr($columns['desktop']); ?> hph-xl-grid-cols-<?php echo esc_attr($columns['wide']); ?>">
            
            <?php if (empty($items)): ?>
            <!-- Empty State -->
            <div class="hph-empty-state hph-col-span-full">
                <div class="hph-empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3 class="hph-empty-title">No Results Found</h3>
                <p class="hph-empty-message"><?php echo esc_html($empty_message); ?></p>
            </div>
            
            <?php else: ?>
            <!-- Cards -->
            <?php 
            $display_items = $show_pagination ? array_slice($items, 0, $items_per_page) : $items;
            foreach ($display_items as $index => $item): 
                $item_card_args = $card_args;
                
                // Auto-configure for post objects
                if (is_object($item) && isset($item->ID)) {
                    $item_card_args['listing_id'] = $item->ID;
                    $item_card_args['style'] = 'property';
                }
                
                // Add animation delay
                if ($animate_cards) {
                    $item_card_args['animate'] = true;
                    $item_card_args['animation_delay'] = (string)($index * 50);
                }
            ?>
                <div class="hph-card-wrapper">
                    <?php get_template_part('template-parts/base/card', null, $item_card_args); ?>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
    </div>
    
    <?php if ($show_pagination && count($items) > $items_per_page && !$infinite_scroll): ?>
    <!-- Pagination -->
    <div class="hph-layout-pagination">
        <?php
        $total_pages = ceil(count($items) / $items_per_page);
        $current_page = 1;
        ?>
        <button class="hph-pagination-prev hph-btn hph-btn-outline hph-btn-sm" disabled>
            <i class="fas fa-chevron-left"></i>
            Previous
        </button>
        
        <div class="hph-pagination-pages">
            <?php for ($i = 1; $i <= min(5, $total_pages); $i++): ?>
            <button class="hph-pagination-page <?php echo $i === 1 ? 'active' : ''; ?>" data-page="<?php echo $i; ?>">
                <?php echo $i; ?>
            </button>
            <?php endfor; ?>
            
            <?php if ($total_pages > 5): ?>
            <span class="hph-pagination-ellipsis">...</span>
            <button class="hph-pagination-page" data-page="<?php echo $total_pages; ?>">
                <?php echo $total_pages; ?>
            </button>
            <?php endif; ?>
        </div>
        
        <button class="hph-pagination-next hph-btn hph-btn-outline hph-btn-sm" <?php echo $total_pages <= 1 ? 'disabled' : ''; ?>>
            Next
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <?php endif; ?>
    
    <?php if ($infinite_scroll): ?>
    <!-- Infinite Scroll Loader -->
    <div class="hph-infinite-loader" style="display: none;">
        <div class="hph-spinner">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
        <p><?php echo esc_html($loading_message); ?></p>
    </div>
    <?php endif; ?>
    
</div>

<script>
// Initialize layout manager
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('<?php echo esc_js($container_id); ?>');
    
    if (!container) return;
    
    // Layout switcher
    const layoutBtns = container.querySelectorAll('.hph-layout-btn');
    layoutBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const newLayout = this.dataset.layout;
            
            // Update active state
            layoutBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update container classes
            container.className = container.className.replace(/hph-layout-\w+/, 'hph-layout-' + newLayout);
            
            // Update layout container
            const layoutContainer = container.querySelector('.hph-layout-container');
            layoutContainer.className = layoutContainer.className.replace(/hph-layout-\w+/, 'hph-layout-' + newLayout);
            
            // Trigger layout change event
            const event = new CustomEvent('hph:layout-change', {
                detail: { layout: newLayout, container: container }
            });
            document.dispatchEvent(event);
            
            // Initialize map if switching to map view
            if (newLayout === 'map' && typeof initHPHMap === 'function') {
                initHPHMap(container);
            }
        });
    });
    
    // Carousel initialization
    <?php if ($layout === 'carousel'): ?>
    const swiper = new Swiper('#<?php echo esc_js($container_id); ?> .swiper', {
        slidesPerView: 1,
        spaceBetween: <?php echo $gap === 'sm' ? 10 : ($gap === 'md' ? 20 : ($gap === 'lg' ? 30 : 40)); ?>,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: <?php echo $columns['tablet']; ?>,
            },
            1024: {
                slidesPerView: <?php echo $columns['desktop']; ?>,
            },
            1280: {
                slidesPerView: <?php echo $columns['wide']; ?>,
            },
        },
    });
    <?php endif; ?>
    
    // Filter handling
    const filters = container.querySelectorAll('.hph-filter-select, .hph-filter-checkbox, .hph-filter-range');
    filters.forEach(filter => {
        filter.addEventListener('change', function() {
            // Collect all filter values
            const filterData = {};
            container.querySelectorAll('[data-filter]').forEach(f => {
                const filterName = f.dataset.filter;
                if (f.type === 'checkbox') {
                    if (!filterData[filterName]) filterData[filterName] = [];
                    if (f.checked) filterData[filterName].push(f.value);
                } else {
                    filterData[filterName] = f.value;
                }
            });
            
            // Trigger filter event
            const event = new CustomEvent('hph:filter-change', {
                detail: { filters: filterData, container: container }
            });
            document.dispatchEvent(event);
        });
    });
    
    // Clear filters
    const clearBtn = container.querySelector('.hph-filter-clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            container.querySelectorAll('.hph-filter-select').forEach(s => s.value = '');
            container.querySelectorAll('.hph-filter-checkbox').forEach(c => c.checked = false);
            
            const event = new CustomEvent('hph:filter-clear', { detail: { container: container } });
            document.dispatchEvent(event);
        });
    }
    
    // Sort handling
    const sortSelect = container.querySelector('.hph-sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const event = new CustomEvent('hph:sort-change', {
                detail: { sort: this.value, container: container }
            });
            document.dispatchEvent(event);
        });
    }
    
    // Pagination
    const paginationBtns = container.querySelectorAll('.hph-pagination-page, .hph-pagination-prev, .hph-pagination-next');
    paginationBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) return;
            
            let page = this.dataset.page;
            if (this.classList.contains('hph-pagination-prev')) {
                page = parseInt(container.dataset.page) - 1;
            } else if (this.classList.contains('hph-pagination-next')) {
                page = parseInt(container.dataset.page) + 1;
            }
            
            container.dataset.page = page;
            
            const event = new CustomEvent('hph:page-change', {
                detail: { page: page, container: container }
            });
            document.dispatchEvent(event);
        });
    });
    
    // Infinite scroll
    <?php if ($infinite_scroll): ?>
    let isLoading = false;
    const loader = container.querySelector('.hph-infinite-loader');
    
    window.addEventListener('scroll', function() {
        if (isLoading) return;
        
        const scrollPos = window.scrollY + window.innerHeight;
        const docHeight = document.documentElement.scrollHeight;
        
        if (scrollPos >= docHeight - 100) {
            isLoading = true;
            loader.style.display = 'block';
            
            const nextPage = parseInt(container.dataset.page) + 1;
            const event = new CustomEvent('hph:infinite-load', {
                detail: { page: nextPage, container: container }
            });
            document.dispatchEvent(event);
            
            // Reset after load (handled by AJAX callback)
            setTimeout(() => {
                isLoading = false;
                loader.style.display = 'none';
                container.dataset.page = nextPage;
            }, 1000);
        }
    });
    <?php endif; ?>
});

// Map initialization callback
<?php if ($layout === 'map'): ?>
function initHPHMaps() {
    const mapContainers = document.querySelectorAll('.hph-map-canvas');
    mapContainers.forEach(mapEl => {
        const map = new google.maps.Map(mapEl, {
            center: { lat: <?php echo esc_js($map_args['center_lat']); ?>, lng: <?php echo esc_js($map_args['center_lng']); ?> },
            zoom: <?php echo esc_js($map_args['zoom']); ?>,
            mapTypeId: '<?php echo esc_js($map_args['map_style']); ?>'
        });
        
        // Add markers for items
        <?php foreach ($items as $index => $item): ?>
            <?php if (function_exists('hpt_get_listing_coordinates') && isset($item->ID)): ?>
                <?php $coords = hpt_get_listing_coordinates($item->ID); ?>
                <?php if ($coords): ?>
                const marker<?php echo $index; ?> = new google.maps.Marker({
                    position: { lat: <?php echo esc_js($coords['lat']); ?>, lng: <?php echo esc_js($coords['lng']); ?> },
                    map: map,
                    title: '<?php echo esc_js(get_the_title($item->ID)); ?>'
                });
                
                marker<?php echo $index; ?>.addListener('click', function() {
                    // Highlight corresponding card
                    const card = document.querySelector('[data-marker-id="marker-<?php echo esc_js($item->ID); ?>"]');
                    if (card) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        card.classList.add('hph-highlighted');
                        setTimeout(() => card.classList.remove('hph-highlighted'), 2000);
                    }
                });
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    });
}
<?php endif; ?>
</script>