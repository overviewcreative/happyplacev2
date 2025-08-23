<?php
/**
 * Reusable Listing Loop Component
 * Location: /wp-content/themes/happy-place/template-parts/components/listing-loop.php
 * 
 * A flexible, reusable listing loop that can be embedded anywhere with custom queries.
 * Supports multiple display modes and configurations.
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Usage Examples:
 * 
 * // Basic usage with default settings
 * get_template_part('template-parts/components/listing-loop');
 * 
 * // Custom query with specific parameters
 * get_template_part('template-parts/components/listing-loop', null, array(
 *     'posts_per_page' => 6,
 *     'view_type' => 'grid',
 *     'columns' => 3,
 *     'featured_only' => true,
 *     'city' => 'Miami',
 *     'show_pagination' => false
 * ));
 * 
 * // Use existing query
 * get_template_part('template-parts/components/listing-loop', null, array(
 *     'query' => $my_custom_query,
 *     'view_type' => 'list'
 * ));
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Default configuration
$defaults = array(
    // Query parameters
    'query' => null,                    // Use existing WP_Query object if provided
    'posts_per_page' => 12,             // Number of listings to show
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
    'orderby' => 'date',                // Sort field
    'order' => 'DESC',                  // Sort order
    'meta_key' => '',                   // Meta key for sorting
    
    // Filters
    'status' => '',                     // Listing status (active, pending, sold)
    'property_type' => '',              // Property type slug
    'city' => '',                       // City filter
    'neighborhood' => '',               // Neighborhood filter
    'min_price' => '',                  // Minimum price
    'max_price' => '',                  // Maximum price
    'min_beds' => '',                   // Minimum bedrooms
    'min_baths' => '',                  // Minimum bathrooms
    'min_sqft' => '',                   // Minimum square feet
    'max_sqft' => '',                   // Maximum square feet
    'featured_only' => false,           // Show only featured listings
    'exclude_sold' => false,            // Exclude sold properties
    'agent_id' => '',                   // Filter by specific agent
    
    // Display options
    'view_type' => 'grid',              // Display type: grid, list, card, minimal, gallery
    'columns' => 3,                     // Number of columns for grid view
    'show_pagination' => true,          // Show pagination
    'show_count' => true,               // Show results count
    'show_sort' => false,               // Show sort options
    'show_filters' => false,            // Show filter bar
    'enable_ajax' => false,             // Enable AJAX loading
    'infinite_scroll' => false,         // Enable infinite scroll
    
    // Component options
    'card_style' => 'default',          // Card style: default, modern, classic, minimal
    'show_featured_badge' => true,      // Show featured badge
    'show_status_badge' => true,        // Show status badge
    'show_price' => true,               // Show price
    'show_address' => true,             // Show address
    'show_details' => true,             // Show beds/baths/sqft
    'show_description' => false,        // Show excerpt/description
    'show_agent' => false,              // Show agent info
    'show_save_button' => true,         // Show save/favorite button
    'show_compare' => false,            // Show compare checkbox
    'image_size' => 'large',            // Featured image size
    
    // Container settings
    'container_class' => '',            // Additional container classes
    'container_id' => '',               // Container ID
    'wrapper' => true,                  // Add wrapper div
    'section_title' => '',              // Optional section title
    'section_subtitle' => '',           // Optional section subtitle
    'no_results_message' => 'No properties found matching your criteria.',
    
    // Advanced options
    'cache_results' => false,           // Cache query results
    'cache_key' => '',                  // Custom cache key
    'load_more_text' => 'Load More Properties',
    'loading_text' => 'Loading...',
);

// Merge with provided arguments
$args = wp_parse_args($args ?? array(), $defaults);
extract($args);

// If no query provided, build one
if (!$query) {
    // Build query arguments
    $query_args = array(
        'post_type' => 'listing',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'post_status' => 'publish',
        'orderby' => $orderby,
        'order' => $order,
    );
    
    // Add meta key if needed for sorting
    if (!empty($meta_key)) {
        $query_args['meta_key'] = $meta_key;
        if (in_array($orderby, array('meta_value', 'meta_value_num'))) {
            $query_args['orderby'] = strpos($meta_key, 'price') !== false || strpos($meta_key, 'sqft') !== false ? 
                'meta_value_num' : 'meta_value';
        }
    }
    
    // Build meta query
    $meta_query = array('relation' => 'AND');
    
    // Price filters
    if (!empty($min_price) || !empty($max_price)) {
        $price_query = array('relation' => 'AND');
        if (!empty($min_price)) {
            $price_query[] = array(
                'key' => '_listing_price',
                'value' => $min_price,
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        if (!empty($max_price)) {
            $price_query[] = array(
                'key' => '_listing_price',
                'value' => $max_price,
                'compare' => '<=',
                'type' => 'NUMERIC'
            );
        }
        $meta_query[] = $price_query;
    }
    
    // Bedroom filter
    if (!empty($min_beds)) {
        $meta_query[] = array(
            'key' => '_listing_bedrooms',
            'value' => $min_beds,
            'compare' => '>=',
            'type' => 'NUMERIC'
        );
    }
    
    // Bathroom filter
    if (!empty($min_baths)) {
        $meta_query[] = array(
            'key' => '_listing_bathrooms',
            'value' => $min_baths,
            'compare' => '>=',
            'type' => 'NUMERIC'
        );
    }
    
    // Square footage filters
    if (!empty($min_sqft) || !empty($max_sqft)) {
        $sqft_query = array('relation' => 'AND');
        if (!empty($min_sqft)) {
            $sqft_query[] = array(
                'key' => '_listing_square_feet',
                'value' => $min_sqft,
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        if (!empty($max_sqft)) {
            $sqft_query[] = array(
                'key' => '_listing_square_feet',
                'value' => $max_sqft,
                'compare' => '<=',
                'type' => 'NUMERIC'
            );
        }
        $meta_query[] = $sqft_query;
    }
    
    // City filter
    if (!empty($city)) {
        $meta_query[] = array(
            'key' => '_listing_city',
            'value' => $city,
            'compare' => '='
        );
    }
    
    // Neighborhood filter
    if (!empty($neighborhood)) {
        $meta_query[] = array(
            'key' => '_listing_neighborhood',
            'value' => $neighborhood,
            'compare' => '='
        );
    }
    
    // Featured only filter
    if ($featured_only) {
        $meta_query[] = array(
            'key' => '_listing_featured',
            'value' => '1',
            'compare' => '='
        );
    }
    
    // Agent filter
    if (!empty($agent_id)) {
        $meta_query[] = array(
            'key' => '_listing_agent',
            'value' => $agent_id,
            'compare' => '='
        );
    }
    
    // Add meta query if not empty
    if (count($meta_query) > 1) {
        $query_args['meta_query'] = $meta_query;
    }
    
    // Build tax query
    $tax_query = array('relation' => 'AND');
    
    // Property type filter
    if (!empty($property_type)) {
        $tax_query[] = array(
            'taxonomy' => 'property_type',
            'field' => 'slug',
            'terms' => $property_type,
        );
    }
    
    // Status filter
    if (!empty($status)) {
        $tax_query[] = array(
            'taxonomy' => 'listing_status',
            'field' => 'slug',
            'terms' => $status,
        );
    } elseif ($exclude_sold) {
        // Exclude sold properties
        $tax_query[] = array(
            'taxonomy' => 'listing_status',
            'field' => 'slug',
            'terms' => array('sold'),
            'operator' => 'NOT IN',
        );
    }
    
    // Add tax query if not empty
    if (count($tax_query) > 1) {
        $query_args['tax_query'] = $tax_query;
    }
    
    // Check cache if enabled
    if ($cache_results && !empty($cache_key)) {
        $cached_query = get_transient($cache_key);
        if ($cached_query !== false) {
            $query = $cached_query;
        }
    }
    
    // Execute query if not cached
    if (!$query) {
        $query = new WP_Query($query_args);
        
        // Cache results if enabled
        if ($cache_results && !empty($cache_key)) {
            set_transient($cache_key, $query, HOUR_IN_SECONDS);
        }
    }
}

// Generate unique ID if not provided
$loop_id = $container_id ?: 'listing-loop-' . uniqid();

// Determine grid class based on view type and columns
$grid_classes = array('hph-listing-loop');
switch($view_type) {
    case 'grid':
        $grid_classes[] = 'hph-grid';
        $grid_classes[] = 'hph-grid--' . $columns . '-cols';
        break;
    case 'list':
        $grid_classes[] = 'hph-list-view';
        break;
    case 'gallery':
        $grid_classes[] = 'hph-gallery-view';
        break;
    case 'minimal':
        $grid_classes[] = 'hph-minimal-view';
        break;
    case 'card':
    default:
        $grid_classes[] = 'hph-card-view';
        break;
}

if (!empty($container_class)) {
    $grid_classes[] = $container_class;
}

// Load component assets
if (!wp_style_is('hph-listing-loop', 'enqueued')) {
    wp_enqueue_style('hph-listing-loop', 
        HPH_CSS_URI . '/components/listing-loop.css', 
        array('hph-framework'), 
        HPH_VERSION
    );
}

if ($enable_ajax || $infinite_scroll) {
    if (!wp_script_is('hph-listing-loop', 'enqueued')) {
        wp_enqueue_script('hph-listing-loop', 
            HPH_JS_URI . '/components/listing-loop.js', 
            array('jquery'), 
            HPH_VERSION, 
            true
        );
        
        wp_localize_script('hph-listing-loop', 'hphListingLoop', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_listing_loop_nonce'),
        ));
    }
}
?>

<?php if ($wrapper): ?>
<div class="hph-listing-loop-wrapper" id="<?php echo esc_attr($loop_id); ?>">
<?php endif; ?>

    <?php if (!empty($section_title) || !empty($section_subtitle)): ?>
    <div class="hph-listing-loop__header">
        <?php if (!empty($section_title)): ?>
            <h2 class="hph-listing-loop__title"><?php echo esc_html($section_title); ?></h2>
        <?php endif; ?>
        <?php if (!empty($section_subtitle)): ?>
            <p class="hph-listing-loop__subtitle"><?php echo esc_html($section_subtitle); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($show_count && $query->found_posts > 0): ?>
    <div class="hph-listing-loop__count">
        <span class="hph-count-number"><?php echo number_format($query->found_posts); ?></span>
        <span class="hph-count-label">Properties Found</span>
    </div>
    <?php endif; ?>
    
    <?php if ($show_sort || $show_filters): ?>
    <div class="hph-listing-loop__controls">
        
        <?php if ($show_filters): ?>
        <div class="hph-loop-filters">
            <!-- Quick filter buttons -->
            <button class="hph-filter-btn" data-filter="all">All</button>
            <button class="hph-filter-btn" data-filter="for-sale">For Sale</button>
            <button class="hph-filter-btn" data-filter="pending">Pending</button>
            <button class="hph-filter-btn" data-filter="featured">Featured</button>
        </div>
        <?php endif; ?>
        
        <?php if ($show_sort): ?>
        <div class="hph-loop-sort">
            <select class="hph-sort-select" data-loop-id="<?php echo esc_attr($loop_id); ?>">
                <option value="date-desc">Newest First</option>
                <option value="date-asc">Oldest First</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
                <option value="featured">Featured First</option>
            </select>
        </div>
        <?php endif; ?>
        
    </div>
    <?php endif; ?>
    
    <?php if ($query->have_posts()): ?>
        
        <div class="<?php echo esc_attr(implode(' ', $grid_classes)); ?>" 
             data-view="<?php echo esc_attr($view_type); ?>"
             data-columns="<?php echo esc_attr($columns); ?>"
             <?php if ($enable_ajax): ?>data-ajax="true"<?php endif; ?>
             <?php if ($infinite_scroll): ?>data-infinite="true"<?php endif; ?>>
            
            <?php
            while ($query->have_posts()) {
                $query->the_post();
                $listing_id = get_the_ID();
                
                // Prepare card arguments
                $card_args = array(
                    'listing_id' => $listing_id,
                    'view_type' => $view_type,
                    'card_style' => $card_style,
                    'show_featured_badge' => $show_featured_badge,
                    'show_status_badge' => $show_status_badge,
                    'show_price' => $show_price,
                    'show_address' => $show_address,
                    'show_details' => $show_details,
                    'show_description' => $show_description,
                    'show_agent' => $show_agent,
                    'show_save_button' => $show_save_button,
                    'show_compare' => $show_compare,
                    'image_size' => $image_size,
                );
                
                // Load the appropriate card template
                get_template_part('template-parts/components/listing-card', $view_type, $card_args);
            }
            wp_reset_postdata();
            ?>
            
        </div>
        
        <?php if ($show_pagination && $query->max_num_pages > 1): ?>
            
            <?php if ($enable_ajax && !$infinite_scroll): ?>
                <!-- AJAX Load More Button -->
                <div class="hph-listing-loop__load-more">
                    <button class="hph-btn hph-btn--primary hph-load-more-btn" 
                            data-page="<?php echo esc_attr($paged); ?>"
                            data-max="<?php echo esc_attr($query->max_num_pages); ?>"
                            data-loop-id="<?php echo esc_attr($loop_id); ?>">
                        <span class="hph-load-more-text"><?php echo esc_html($load_more_text); ?></span>
                        <span class="hph-loading-text" style="display:none;">
                            <i class="fas fa-spinner fa-spin"></i> <?php echo esc_html($loading_text); ?>
                        </span>
                    </button>
                </div>
            <?php elseif (!$infinite_scroll): ?>
                <!-- Standard Pagination -->
                <div class="hph-listing-loop__pagination">
                    <?php
                    echo paginate_links(array(
                        'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                        'format' => '?paged=%#%',
                        'current' => max(1, $paged),
                        'total' => $query->max_num_pages,
                        'prev_text' => '<i class="fas fa-chevron-left"></i>',
                        'next_text' => '<i class="fas fa-chevron-right"></i>',
                        'type' => 'list',
                        'end_size' => 1,
                        'mid_size' => 2,
                    ));
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if ($infinite_scroll): ?>
                <!-- Infinite Scroll Loader -->
                <div class="hph-listing-loop__infinite-loader" style="display:none;">
                    <i class="fas fa-spinner fa-spin"></i> Loading more properties...
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
        
    <?php else: ?>
        
        <!-- No Results -->
        <div class="hph-listing-loop__no-results">
            <div class="hph-no-results">
                <i class="fas fa-home hph-no-results__icon"></i>
                <p class="hph-no-results__message"><?php echo esc_html($no_results_message); ?></p>
            </div>
        </div>
        
    <?php endif; ?>

<?php if ($wrapper): ?>
</div>
<?php endif; ?>