<?php
/**
 * Archive Listing Template with Multiple Views
 * Location: /wp-content/themes/happy-place/archive-listing.php
 * 
 * Main archive template for property listings with:
 * - Multiple view modes (Grid, List, Map, Gallery)
 * - Advanced search/filtering
 * - Sortable results
 * - Save search functionality
 * - Responsive design
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get user preferences from session/cookies
$default_view = isset($_COOKIE['hph_listing_view']) ? sanitize_text_field($_COOKIE['hph_listing_view']) : 'grid';
$default_sort = isset($_COOKIE['hph_listing_sort']) ? sanitize_text_field($_COOKIE['hph_listing_sort']) : 'date-desc';
$default_per_page = isset($_COOKIE['hph_listings_per_page']) ? intval($_COOKIE['hph_listings_per_page']) : 12;

// Get query parameters
$current_view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : $default_view;
$current_sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : $default_sort;
$per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : $default_per_page;
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// Validate view mode
$allowed_views = array('grid', 'list', 'map', 'gallery');
if (!in_array($current_view, $allowed_views)) {
    $current_view = 'grid';
}

// Get pagination
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// Get active filters from URL parameters
$active_filters = array(
    'status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
    'property_type' => isset($_GET['property_type']) ? sanitize_text_field($_GET['property_type']) : '',
    'min_price' => isset($_GET['min_price']) ? intval($_GET['min_price']) : '',
    'max_price' => isset($_GET['max_price']) ? intval($_GET['max_price']) : '',
    'beds' => isset($_GET['beds']) ? intval($_GET['beds']) : '',
    'baths' => isset($_GET['baths']) ? intval($_GET['baths']) : '',
    'min_sqft' => isset($_GET['min_sqft']) ? intval($_GET['min_sqft']) : '',
    'max_sqft' => isset($_GET['max_sqft']) ? intval($_GET['max_sqft']) : '',
    'city' => isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '',
    'neighborhood' => isset($_GET['neighborhood']) ? sanitize_text_field($_GET['neighborhood']) : '',
    'features' => isset($_GET['features']) ? array_map('sanitize_text_field', (array)$_GET['features']) : array(),
    'keywords' => isset($_GET['keywords']) ? sanitize_text_field($_GET['keywords']) : '',
);

// Build the query args using bridge function pattern
$listing_args = array(
    'post_type' => 'listing',
    'posts_per_page' => $per_page,
    'paged' => $paged,
    'post_status' => 'publish',
);

// Apply sorting
switch($current_sort) {
    case 'price-asc':
        $listing_args['meta_key'] = '_listing_price';
        $listing_args['orderby'] = 'meta_value_num';
        $listing_args['order'] = 'ASC';
        break;
    case 'price-desc':
        $listing_args['meta_key'] = '_listing_price';
        $listing_args['orderby'] = 'meta_value_num';
        $listing_args['order'] = 'DESC';
        break;
    case 'sqft-asc':
        $listing_args['meta_key'] = '_listing_square_feet';
        $listing_args['orderby'] = 'meta_value_num';
        $listing_args['order'] = 'ASC';
        break;
    case 'sqft-desc':
        $listing_args['meta_key'] = '_listing_square_feet';
        $listing_args['orderby'] = 'meta_value_num';
        $listing_args['order'] = 'DESC';
        break;
    case 'date-asc':
        $listing_args['orderby'] = 'date';
        $listing_args['order'] = 'ASC';
        break;
    case 'featured':
        $listing_args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key' => '_listing_featured',
                'value' => '1',
                'compare' => '='
            ),
            array(
                'key' => '_listing_featured',
                'compare' => 'NOT EXISTS'
            )
        );
        $listing_args['orderby'] = array(
            'meta_value_num' => 'DESC',
            'date' => 'DESC'
        );
        break;
    case 'date-desc':
    default:
        $listing_args['orderby'] = 'date';
        $listing_args['order'] = 'DESC';
        break;
}

// Apply search
if (!empty($search_query) || !empty($active_filters['keywords'])) {
    $listing_args['s'] = !empty($search_query) ? $search_query : $active_filters['keywords'];
}

// Build meta query for filters
$meta_query = array('relation' => 'AND');

// Price range filter
if (!empty($active_filters['min_price']) || !empty($active_filters['max_price'])) {
    $price_query = array('relation' => 'AND');
    
    if (!empty($active_filters['min_price'])) {
        $price_query[] = array(
            'key' => '_listing_price',
            'value' => $active_filters['min_price'],
            'compare' => '>=',
            'type' => 'NUMERIC'
        );
    }
    
    if (!empty($active_filters['max_price'])) {
        $price_query[] = array(
            'key' => '_listing_price',
            'value' => $active_filters['max_price'],
            'compare' => '<=',
            'type' => 'NUMERIC'
        );
    }
    
    $meta_query[] = $price_query;
}

// Bedrooms filter
if (!empty($active_filters['beds'])) {
    $meta_query[] = array(
        'key' => '_listing_bedrooms',
        'value' => $active_filters['beds'],
        'compare' => '>=',
        'type' => 'NUMERIC'
    );
}

// Bathrooms filter
if (!empty($active_filters['baths'])) {
    $meta_query[] = array(
        'key' => '_listing_bathrooms',
        'value' => $active_filters['baths'],
        'compare' => '>=',
        'type' => 'NUMERIC'
    );
}

// Square footage filter
if (!empty($active_filters['min_sqft']) || !empty($active_filters['max_sqft'])) {
    $sqft_query = array('relation' => 'AND');
    
    if (!empty($active_filters['min_sqft'])) {
        $sqft_query[] = array(
            'key' => '_listing_square_feet',
            'value' => $active_filters['min_sqft'],
            'compare' => '>=',
            'type' => 'NUMERIC'
        );
    }
    
    if (!empty($active_filters['max_sqft'])) {
        $sqft_query[] = array(
            'key' => '_listing_square_feet',
            'value' => $active_filters['max_sqft'],
            'compare' => '<=',
            'type' => 'NUMERIC'
        );
    }
    
    $sqft_query[] = $sqft_query;
}

// City filter
if (!empty($active_filters['city'])) {
    $meta_query[] = array(
        'key' => '_listing_city',
        'value' => $active_filters['city'],
        'compare' => '='
    );
}

// Add meta query to args if not empty
if (count($meta_query) > 1) {
    $listing_args['meta_query'] = $meta_query;
}

// Build tax query for property type and status
$tax_query = array('relation' => 'AND');

// Property type filter
if (!empty($active_filters['property_type'])) {
    $tax_query[] = array(
        'taxonomy' => 'property_type',
        'field' => 'slug',
        'terms' => $active_filters['property_type'],
    );
}

// Status filter
if (!empty($active_filters['status'])) {
    $tax_query[] = array(
        'taxonomy' => 'listing_status',
        'field' => 'slug',
        'terms' => $active_filters['status'],
    );
}

// Add tax query to args if not empty
if (count($tax_query) > 1) {
    $listing_args['tax_query'] = $tax_query;
}

// Execute the query
$listings_query = new WP_Query($listing_args);

// Get total results for display
$total_results = $listings_query->found_posts;

// Load component assets
if (!wp_style_is('hph-archive-listing', 'enqueued')) {
    wp_enqueue_style('hph-archive-listing', 
        HPH_CSS_URI . '/framework/05-pages/hph-archive-listing.css', 
        array('hph-framework'), 
        HPH_VERSION
    );
}

if (!wp_script_is('hph-archive-listing', 'enqueued')) {
    wp_enqueue_script('hph-archive-listing', 
        HPH_JS_URI . '/pages/archive-listing.js', 
        array('jquery'), 
        HPH_VERSION, 
        true
    );
    
    // Localize script with AJAX data and enhanced context
    wp_localize_script('hph-archive-listing', 'hphArchive', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hph_archive_nonce'),
        'currentView' => $current_view,
        'currentSort' => $current_sort,
        'perPage' => $per_page,
        'isUserLoggedIn' => is_user_logged_in(),
        'userId' => get_current_user_id(),
        'cardFeatures' => array(
            'carousel' => true,
            'favorites' => true,
            'sharing' => true,
            'lightbox' => true,
            'compare' => false // Can be enabled later
        ),
        'strings' => array(
            'loading' => __('Loading...', 'happy-place-theme'),
            'favoriteAdded' => __('Property added to favorites', 'happy-place-theme'),
            'favoriteRemoved' => __('Property removed from favorites', 'happy-place-theme'),
            'loginRequired' => __('Please log in to save favorites', 'happy-place-theme'),
            'shareProperty' => __('Share this property', 'happy-place-theme'),
        )
    ));
    
    // Also localize for listing cards (hphContext expected by cards)
    wp_localize_script('hph-listing-card', 'hphContext', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hph_nonce'),
        'isLoggedIn' => is_user_logged_in(),
        'userId' => get_current_user_id(),
        'strings' => array(
            'loading' => __('Loading...', 'happy-place-theme'),
            'favoriteAdded' => __('Property added to favorites', 'happy-place-theme'),
            'favoriteRemoved' => __('Property removed from favorites', 'happy-place-theme'),
            'loginRequired' => __('Please log in to save favorites', 'happy-place-theme'),
            'shareProperty' => __('Share this property', 'happy-place-theme'),
            'networkError' => __('Network error. Please try again.', 'happy-place-theme'),
        )
    ));
}

// Ensure listing card assets are loaded for enhanced functionality
if (!wp_style_is('hph-listing-card', 'enqueued')) {
    wp_enqueue_style('hph-listing-card', 
        HPH_CSS_URI . '/framework/03-components/hph-listing-card.css', 
        array('hph-framework'), 
        HPH_VERSION
    );
}

if (!wp_script_is('hph-listing-card', 'enqueued')) {
    wp_enqueue_script('hph-listing-card', 
        HPH_JS_URI . '/components/listing-card.js', 
        array('jquery'), 
        HPH_VERSION, 
        true
    );
}

// Load view-specific assets
if ($current_view === 'map') {
    wp_enqueue_script('hph-map-view', HPH_JS_URI . '/components/map-view.js', array('jquery'), HPH_VERSION, true);
}
?>

<div class="hph-archive-listing" data-view="<?php echo esc_attr($current_view); ?>">
    
    <!-- Archive Header -->
    <section class="hph-archive__header">
        <div class="hph-container">
            
            <!-- Title and Results Count -->
            <div class="hph-archive__header-top">
                <h1 class="hph-archive__title">
                    <?php
                    if (is_tax('property_type')) {
                        echo single_term_title('', false) . ' Properties';
                    } elseif (is_tax('listing_status')) {
                        echo single_term_title('', false) . ' Listings';
                    } elseif (!empty($search_query)) {
                        echo 'Search Results for "' . esc_html($search_query) . '"';
                    } else {
                        echo 'All Properties';
                    }
                    ?>
                </h1>
                
                <div class="hph-archive__results-count">
                    <span class="hph-results-number"><?php echo number_format($total_results); ?></span>
                    <span class="hph-results-label">Properties Found</span>
                </div>
            </div>
            
            <!-- Controls Bar -->
            <div class="hph-archive__controls">
                
                <!-- Advanced Search Toggle -->
                <button class="hph-btn hph-btn--secondary hph-archive__search-toggle" 
                        aria-expanded="false"
                        aria-controls="advanced-search">
                    <i class="fas fa-search"></i>
                    <span>Advanced Search</span>
                    <i class="fas fa-chevron-down hph-icon-chevron"></i>
                </button>
                
                <!-- View Switcher -->
                <div class="hph-archive__view-switcher" role="tablist">
                    <button class="hph-view-btn <?php echo $current_view === 'grid' ? 'active' : ''; ?>" 
                            data-view="grid"
                            role="tab"
                            aria-selected="<?php echo $current_view === 'grid' ? 'true' : 'false'; ?>"
                            title="Grid View">
                        <i class="fas fa-th"></i>
                        <span class="hph-view-label">Grid</span>
                    </button>
                    <button class="hph-view-btn <?php echo $current_view === 'list' ? 'active' : ''; ?>" 
                            data-view="list"
                            role="tab"
                            aria-selected="<?php echo $current_view === 'list' ? 'true' : 'false'; ?>"
                            title="List View">
                        <i class="fas fa-list"></i>
                        <span class="hph-view-label">List</span>
                    </button>
                    <button class="hph-view-btn <?php echo $current_view === 'map' ? 'active' : ''; ?>" 
                            data-view="map"
                            role="tab"
                            aria-selected="<?php echo $current_view === 'map' ? 'true' : 'false'; ?>"
                            title="Map View">
                        <i class="fas fa-map-marked-alt"></i>
                        <span class="hph-view-label">Map</span>
                    </button>
                    <button class="hph-view-btn <?php echo $current_view === 'gallery' ? 'active' : ''; ?>" 
                            data-view="gallery"
                            role="tab"
                            aria-selected="<?php echo $current_view === 'gallery' ? 'true' : 'false'; ?>"
                            title="Gallery View">
                        <i class="fas fa-images"></i>
                        <span class="hph-view-label">Gallery</span>
                    </button>
                </div>
                
                <!-- Sort Options -->
                <div class="hph-archive__sort">
                    <label for="listing-sort" class="hph-sort-label">Sort by:</label>
                    <select id="listing-sort" class="hph-sort-select" data-current="<?php echo esc_attr($current_sort); ?>">
                        <option value="date-desc" <?php selected($current_sort, 'date-desc'); ?>>Newest First</option>
                        <option value="date-asc" <?php selected($current_sort, 'date-asc'); ?>>Oldest First</option>
                        <option value="price-asc" <?php selected($current_sort, 'price-asc'); ?>>Price: Low to High</option>
                        <option value="price-desc" <?php selected($current_sort, 'price-desc'); ?>>Price: High to Low</option>
                        <option value="sqft-asc" <?php selected($current_sort, 'sqft-asc'); ?>>Size: Small to Large</option>
                        <option value="sqft-desc" <?php selected($current_sort, 'sqft-desc'); ?>>Size: Large to Small</option>
                        <option value="featured" <?php selected($current_sort, 'featured'); ?>>Featured First</option>
                    </select>
                </div>
                
                <!-- Per Page Options -->
                <div class="hph-archive__per-page">
                    <label for="per-page" class="hph-per-page-label">Show:</label>
                    <select id="per-page" class="hph-per-page-select">
                        <option value="12" <?php selected($per_page, 12); ?>>12</option>
                        <option value="24" <?php selected($per_page, 24); ?>>24</option>
                        <option value="48" <?php selected($per_page, 48); ?>>48</option>
                        <option value="96" <?php selected($per_page, 96); ?>>96</option>
                    </select>
                </div>
                
                <!-- Save Search Button -->
                <?php if (is_user_logged_in()): ?>
                <button class="hph-btn hph-btn--icon hph-archive__save-search" 
                        title="Save this search"
                        aria-label="Save search">
                    <i class="far fa-star"></i>
                </button>
                <?php endif; ?>
                
            </div>
            
        </div>
    </section>
    
    <!-- Advanced Search Panel (Hidden by default) -->
    <section id="advanced-search" class="hph-archive__search-panel" aria-hidden="true">
        <?php get_template_part('template-parts/components/listing-search-advanced'); ?>
    </section>
    
    <!-- Active Filters Display -->
    <?php if (array_filter($active_filters)): ?>
    <section class="hph-archive__active-filters">
        <div class="hph-container">
            <div class="hph-active-filters">
                <span class="hph-active-filters__label">Active Filters:</span>
                <div class="hph-active-filters__list">
                    <?php foreach ($active_filters as $key => $value): ?>
                        <?php if (!empty($value)): ?>
                            <?php
                            $filter_label = ucwords(str_replace('_', ' ', $key));
                            $filter_value = is_array($value) ? implode(', ', $value) : $value;
                            ?>
                            <span class="hph-filter-tag" data-filter="<?php echo esc_attr($key); ?>">
                                <span class="hph-filter-tag__label"><?php echo esc_html($filter_label); ?>:</span>
                                <span class="hph-filter-tag__value"><?php echo esc_html($filter_value); ?></span>
                                <button class="hph-filter-tag__remove" aria-label="Remove filter">
                                    <i class="fas fa-times"></i>
                                </button>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <button class="hph-clear-all-filters">Clear All</button>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Main Content Area -->
    <section class="hph-archive__content">
        <div class="hph-container hph-container--wide">
            
            <!-- Map Container (for map view) -->
            <?php if ($current_view === 'map'): ?>
            <div class="hph-archive__map-container">
                <div id="archive-map" class="hph-archive__map"></div>
                <div class="hph-archive__map-sidebar">
                    <div class="hph-map-results">
                        <?php
                        // Load map-specific listing cards
                        if ($listings_query->have_posts()) {
                            while ($listings_query->have_posts()) {
                                $listings_query->the_post();
                                get_template_part('template-parts/components/listing-card', 'map', array(
                                    'listing_id' => get_the_ID(),
                                    'display_mode' => 'map'
                                ));
                            }
                            wp_reset_postdata();
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            
            <!-- Regular Listings Grid/List/Gallery -->
            <div class="hph-archive__listings hph-archive__listings--<?php echo esc_attr($current_view); ?>">
                
                <?php if ($listings_query->have_posts()): ?>
                    
                    <?php
                    // Determine grid class based on view
                    $grid_class = '';
                    switch($current_view) {
                        case 'grid':
                            $grid_class = 'hph-grid hph-grid--3-cols hph-listings-grid';
                            break;
                        case 'list':
                            $grid_class = 'hph-list-view hph-listings-list';
                            break;
                        case 'gallery':
                            $grid_class = 'hph-gallery-view hph-listings-gallery';
                            break;
                    }
                    ?>
                    
                    <div class="<?php echo esc_attr($grid_class); ?>">
                        <?php
                        while ($listings_query->have_posts()) {
                            $listings_query->the_post();
                            
                            // Load appropriate card template based on view with enhanced arguments
                            get_template_part('template-parts/components/listing-card', $current_view, array(
                                'listing_id' => get_the_ID(),
                                'display_mode' => $current_view,
                                'card_style' => 'modern', // Use modern card style
                                'show_featured_badge' => true,
                                'show_status_badge' => true,
                                'show_price' => true,
                                'show_address' => true,
                                'show_details' => true,
                                'show_save_button' => true,
                                'show_compare' => false, // Enable for comparison feature
                                'image_size' => 'large',
                            ));
                        }
                        wp_reset_postdata();
                        ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($listings_query->max_num_pages > 1): ?>
                    <div class="hph-archive__pagination">
                        <?php
                        echo paginate_links(array(
                            'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                            'format' => '?paged=%#%',
                            'current' => max(1, $paged),
                            'total' => $listings_query->max_num_pages,
                            'prev_text' => '<i class="fas fa-chevron-left"></i> Previous',
                            'next_text' => 'Next <i class="fas fa-chevron-right"></i>',
                            'type' => 'list',
                            'end_size' => 2,
                            'mid_size' => 2,
                        ));
                        ?>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    
                    <!-- No Results -->
                    <div class="hph-archive__no-results">
                        <div class="hph-no-results">
                            <i class="fas fa-home hph-no-results__icon"></i>
                            <h2 class="hph-no-results__title">No Properties Found</h2>
                            <p class="hph-no-results__message">
                                <?php if (!empty($search_query)): ?>
                                    Sorry, no properties match your search for "<?php echo esc_html($search_query); ?>".
                                <?php elseif (array_filter($active_filters)): ?>
                                    No properties match your current filters. Try adjusting your search criteria.
                                <?php else: ?>
                                    There are currently no properties available.
                                <?php endif; ?>
                            </p>
                            <div class="hph-no-results__actions">
                                <?php if (array_filter($active_filters) || !empty($search_query)): ?>
                                    <a href="<?php echo get_post_type_archive_link('listing'); ?>" 
                                       class="hph-btn hph-btn--primary">
                                        <i class="fas fa-redo"></i> Clear Filters
                                    </a>
                                <?php endif; ?>
                                <button class="hph-btn hph-btn--secondary hph-save-search-alert">
                                    <i class="fas fa-bell"></i> Get Alerts for This Search
                                </button>
                            </div>
                        </div>
                    </div>
                    
                <?php endif; ?>
                
            </div>
            <?php endif; ?>
            
        </div>
    </section>
    
    <!-- Related Sections -->
    <section class="hph-archive__related">
        <div class="hph-container">
            
            <!-- Featured Properties -->
            <?php
            $featured_listings = function_exists('hpt_get_featured_listings') ? 
                hpt_get_featured_listings(3) : null;
            
            if ($featured_listings && $featured_listings->have_posts()): ?>
            <div class="hph-archive__featured">
                <h2 class="hph-section-title">Featured Properties</h2>
                <div class="hph-grid hph-grid--3-cols hph-listings-grid">
                    <?php
                    while ($featured_listings->have_posts()) {
                        $featured_listings->the_post();
                        // Use grid card template for featured listings too
                        get_template_part('template-parts/components/listing-card', 'grid', array(
                            'listing_id' => get_the_ID(),
                            'display_mode' => 'featured',
                            'card_style' => 'elevated', // Special style for featured
                            'show_featured_badge' => true,
                            'show_status_badge' => true,
                            'show_price' => true,
                            'show_address' => true,
                            'show_details' => true,
                            'show_save_button' => true,
                            'show_compare' => false,
                            'image_size' => 'large',
                            'is_featured' => true
                        ));
                    }
                    wp_reset_postdata();
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Recent Searches (for logged in users) -->
            <?php if (is_user_logged_in()): ?>
            <div class="hph-archive__recent-searches">
                <h3 class="hph-subsection-title">Your Recent Searches</h3>
                <div class="hph-recent-searches" data-user="<?php echo get_current_user_id(); ?>">
                    <!-- Loaded via AJAX -->
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </section>
    
</div>

<!-- Save Search Modal -->
<?php if (is_user_logged_in()): ?>
<div id="save-search-modal" class="hph-modal" aria-hidden="true">
    <div class="hph-modal__backdrop"></div>
    <div class="hph-modal__content">
        <div class="hph-modal__header">
            <h3 class="hph-modal__title">Save This Search</h3>
            <button class="hph-modal__close" aria-label="Close modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="hph-modal__body">
            <form id="save-search-form" class="hph-save-search-form">
                <div class="hph-form-group">
                    <label for="search-name">Search Name</label>
                    <input type="text" 
                           id="search-name" 
                           name="search_name" 
                           class="hph-form-control" 
                           placeholder="e.g., 3BR Homes in Downtown"
                           required>
                </div>
                <div class="hph-form-group">
                    <label for="email-frequency">Email Alert Frequency</label>
                    <select id="email-frequency" name="email_frequency" class="hph-form-control">
                        <option value="instant">Instant</option>
                        <option value="daily">Daily</option>
                        <option value="weekly" selected>Weekly</option>
                        <option value="never">No Email Alerts</option>
                    </select>
                </div>
                <div class="hph-form-actions">
                    <button type="button" class="hph-btn hph-btn--ghost hph-modal__cancel">
                        Cancel
                    </button>
                    <button type="submit" class="hph-btn hph-btn--primary">
                        <i class="fas fa-save"></i> Save Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php get_footer(); ?>