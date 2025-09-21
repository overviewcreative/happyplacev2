<?php
/**
 * Enhanced Listing Archive Template
 * Features: Multiple view modes, enhanced filtering, map integration
 */

// Archive assets are now handled automatically by theme-assets.php bundles
// Individual assets only loaded in development mode

// Enqueue Mapbox GL JS if we have a token
$mapbox_token = '';
if (function_exists('hp_get_mapbox_token')) {
    $mapbox_token = hp_get_mapbox_token();
} else if (defined('HP_MAPBOX_ACCESS_TOKEN')) {
    $mapbox_token = HP_MAPBOX_ACCESS_TOKEN;
}

if (!empty($mapbox_token)) {
    wp_enqueue_style('mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css', [], '2.15.0');
    wp_enqueue_script('mapbox-gl-js', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], '2.15.0', false);
}

// Add body class for archive page styling - this enables our CSS modifiers
add_filter('body_class', function($classes) {
    $classes[] = 'archive-listing';
    return $classes;
});

// Ensure bridge functions are loaded for this template
require_once get_template_directory() . '/includes/bridge/listing-bridge.php';

// Use standard header with archive modifiers instead of custom header
get_header();

// Add script to disable header scroll behavior for archive mode and expand search
add_action('wp_footer', function() {
    echo '<script>
        // Disable header scroll handlers for archive mode
        window.hphArchiveMode = true;
        
        // Override any header scroll functionality
        if (typeof window.hphHeader !== "undefined" && window.hphHeader.destroy) {
            window.hphHeader.destroy();
        }
        
        // Remove any scroll event listeners that might interfere
        document.addEventListener("DOMContentLoaded", function() {
            window.removeEventListener("scroll", window.hphHeaderScroll);
            
            // Auto-expand header search on archive pages
            setTimeout(function() {
                const searchToggle = document.querySelector(".hph-search-toggle");
                const searchBar = document.querySelector(".hph-search-bar[data-search-bar]");
                
                if (searchToggle && searchBar) {
                    // Check if search is not already expanded
                    if (!searchBar.classList.contains("active") && !searchBar.style.display === "block") {
                        searchToggle.click();
                        searchBar.classList.add("archive-auto-expanded");
                        console.log("Archive: Header search auto-expanded");
                    }
                }
            }, 100);
        });
    </script>';
});

// Get Mapbox token for map functionality
$mapbox_token = '';
if (defined('HP_MAPBOX_ACCESS_TOKEN')) {
    $mapbox_token = HP_MAPBOX_ACCESS_TOKEN;
} else if (class_exists('\HappyPlace\Core\ConfigurationManager')) {
    // Try to get from plugin configuration manager
    $config_manager = \HappyPlace\Core\ConfigurationManager::get_instance();
    $mapbox_token = $config_manager->get('mapbox_access_token', '');
}

// Load Mapbox scripts if token is available
if (!empty($mapbox_token)) {
    wp_enqueue_script('mapbox-gl-js', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], '2.15.0', false);
    wp_enqueue_style('mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css', [], '2.15.0');

    // Add Mapbox configuration to page
    wp_add_inline_script('mapbox-gl-js', 'window.hph_mapbox_config = {
        access_token: "' . esc_js($mapbox_token) . '",
        has_token: true
    };', 'before');
} else {
    // Add configuration indicating no token
    wp_add_inline_script('hph-archive-js', 'window.hph_mapbox_config = {
        access_token: "",
        has_token: false
    };', 'before');
}

// Set up AJAX variables for archive functionality
wp_localize_script('hph-archive-js', 'hph_ajax', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_ajax_nonce'),
    'archive_nonce' => wp_create_nonce('hph_archive_nonce'),
    'listings_nonce' => wp_create_nonce('hph_listings_nonce'),
]);

wp_localize_script('hph-archive-js', 'hphArchive', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_archive_nonce'),
    'post_type' => 'listing',
    'current_page' => get_query_var('paged', 1),
    'per_page' => 12,
    'max_pages' => 1,
    'current_view' => sanitize_text_field($_GET['view'] ?? 'grid'),
    'current_sort' => sanitize_text_field($_GET['sort'] ?? 'date_desc'),
    'mapbox_token' => $mapbox_token,
    'debug' => defined('WP_DEBUG') && WP_DEBUG,
]);

// Set Mapbox configuration for HPH Map component
wp_add_inline_script('hph-archive-js', '
    // Ensure Mapbox configuration is available for HPH Map component
    window.hph_mapbox_config = window.hph_mapbox_config || {};
    window.hph_mapbox_config.access_token = "' . esc_js($mapbox_token) . '";
    window.hph_mapbox_config.has_token = ' . (!empty($mapbox_token) ? 'true' : 'false') . ';

    console.log("Archive: Mapbox token configured:", window.hph_mapbox_config.has_token);
    if (window.hph_mapbox_config.has_token) {
        console.log("Archive: Mapbox token set for HPH Map component:", window.hph_mapbox_config.access_token.substring(0, 20) + "...");
    } else {
        console.log("Archive: No Mapbox token configured - map view will show configuration message");
    }
', 'before');

// Get filter parameters (supporting both header search and advanced search formats)
$search = sanitize_text_field($_GET['s'] ?? $_GET['search'] ?? '');

// Handle price range from header search (format: "min-max") or advanced search (separate fields)
$price_range = sanitize_text_field($_GET['price_range'] ?? '');
$min_price = 0;
$max_price = 0;

if (!empty($price_range) && strpos($price_range, '-') !== false) {
    list($min_price, $max_price) = explode('-', $price_range);
    $min_price = intval($min_price);
    $max_price = intval($max_price);
} else {
    $min_price = intval($_GET['min_price'] ?? 0);
    $max_price = intval($_GET['max_price'] ?? 0);
}

$bedrooms = sanitize_text_field($_GET['bedrooms'] ?? '');
$bathrooms = sanitize_text_field($_GET['bathrooms'] ?? '');

// Taxonomy filters
$property_type = sanitize_text_field($_GET['property_type'] ?? ''); // Can be slug or term ID
$property_status = sanitize_text_field($_GET['property_status'] ?? '');
$listing_features = array_map('sanitize_text_field', (array)($_GET['listing_features'] ?? []));
$listing_location = sanitize_text_field($_GET['listing_location'] ?? '');

// Legacy support
$zip_code = sanitize_text_field($_GET['zip_code'] ?? '');

// Build query
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$args = [
    'post_type' => 'listing',
    'post_status' => 'publish', 
    'posts_per_page' => 12,
    'paged' => $paged
];

// Add search
if (!empty($search)) {
    $args['s'] = $search;
}

// Initialize meta_query and tax_query arrays
$meta_queries = [];
$tax_queries = [];

// Add price range filter (still meta query since it's a custom field)
if ($min_price || $max_price) {
    $price_query = [
        'key' => 'listing_price',
        'type' => 'NUMERIC'
    ];
    
    if ($min_price && $max_price) {
        $price_query['value'] = [$min_price, $max_price];
        $price_query['compare'] = 'BETWEEN';
    } elseif ($min_price) {
        $price_query['value'] = $min_price;
        $price_query['compare'] = '>=';
    } elseif ($max_price) {
        $price_query['value'] = $max_price;
        $price_query['compare'] = '<=';
    }
    
    $meta_queries[] = $price_query;
}

// Add bedrooms filter (still meta query since it's a custom field)
if (!empty($bedrooms)) {
    $bedroom_value = intval($bedrooms);
    if ($bedrooms === '4+' || $bedroom_value >= 4) {
        $meta_queries[] = [
            'key' => 'bedrooms',
            'value' => 4,
            'type' => 'NUMERIC',
            'compare' => '>='
        ];
    } else {
        $meta_queries[] = [
            'key' => 'bedrooms',
            'value' => $bedroom_value,
            'type' => 'NUMERIC',
            'compare' => '>='
        ];
    }
}

// Add bathrooms filter (still meta query since it's a custom field)
if (!empty($bathrooms)) {
    $bathroom_value = intval($bathrooms);
    if ($bathrooms === '3+' || $bathroom_value >= 3) {
        $meta_queries[] = [
            'key' => 'bathrooms_full',
            'value' => 3,
            'type' => 'NUMERIC',
            'compare' => '>='
        ];
    } else {
        $meta_queries[] = [
            'key' => 'bathrooms_full',
            'value' => $bathroom_value,
            'type' => 'NUMERIC',
            'compare' => '>='
        ];
    }
}

// TAXONOMY FILTERS

// Add property type taxonomy filter
if (!empty($property_type)) {
    $tax_queries[] = [
        'taxonomy' => 'property_type',
        'field' => is_numeric($property_type) ? 'term_id' : 'slug',
        'terms' => $property_type
    ];
}

// Add property status taxonomy filter
if (!empty($property_status)) {
    $tax_queries[] = [
        'taxonomy' => 'property_status',
        'field' => is_numeric($property_status) ? 'term_id' : 'slug',
        'terms' => $property_status
    ];
}

// Add listing features taxonomy filter (multiple features support)
if (!empty($listing_features)) {
    $tax_queries[] = [
        'taxonomy' => 'listing_features',
        'field' => 'slug',
        'terms' => $listing_features,
        'operator' => 'AND' // All features must be present
    ];
}

// Add listing location taxonomy filter
if (!empty($listing_location)) {
    $tax_queries[] = [
        'taxonomy' => 'listing_location',
        'field' => is_numeric($listing_location) ? 'term_id' : 'slug',
        'terms' => $listing_location
    ];
}

// Legacy zip code filter (still meta query for backward compatibility)
if (!empty($zip_code)) {
    $meta_queries[] = [
        'key' => 'zip_code',
        'value' => $zip_code,
        'compare' => '='
    ];
}

// Add meta_query to args only if we have filters
if (!empty($meta_queries)) {
    $args['meta_query'] = $meta_queries;
}

// Add tax_query to args only if we have taxonomy filters
if (!empty($tax_queries)) {
    if (count($tax_queries) > 1) {
        $tax_queries['relation'] = 'AND'; // All taxonomy conditions must be met
    }
    $args['tax_query'] = $tax_queries;
}

$listings = new WP_Query($args);

// Prepare listing change tracking data for all listings
$listings_change_data = [];
if ($listings->have_posts()) {
    while ($listings->have_posts()) {
        $listings->the_post();
        $listing_id = get_the_ID();

        // Get change tracking data for each listing
        $listings_change_data[$listing_id] = [
            'changes' => function_exists('hpt_bridge_get_listing_changes') ? hpt_bridge_get_listing_changes($listing_id) : [],
            'badges' => function_exists('hpt_bridge_get_listing_badges') ? hpt_bridge_get_listing_badges($listing_id, 2) : [],
            'has_recent_changes' => function_exists('hpt_bridge_has_recent_changes') ? hpt_bridge_has_recent_changes($listing_id) : false,
            'is_new_listing' => function_exists('hpt_is_new_listing') ? hpt_is_new_listing($listing_id) : false
        ];
    }
    wp_reset_postdata();
}
?>

<div class="hph-listing-archive hph-clean-layout" data-hide-in-views="map">

    <!-- Archive Hero Section - Hidden in Map View -->
    <div class="hph-archive-hero-section" data-hide-in-views="map">
        <?php 
        // Get count of current listings
        $total_listings = $listings->found_posts;
        
        // Build search terms display with proper taxonomy names
        $search_terms = [];
        if ($search) $search_terms[] = "Search: \"$search\"";
        
        if ($min_price || $max_price) {
            if ($min_price && $max_price) {
                $search_terms[] = "Price: $" . number_format($min_price) . " - $" . number_format($max_price);
            } elseif ($min_price) {
                $search_terms[] = "Min Price: $" . number_format($min_price);
            } else {
                $search_terms[] = "Max Price: $" . number_format($max_price);
            }
        }
        
        if ($bedrooms) $search_terms[] = "Beds: $bedrooms" . ($bedrooms === '4+' ? '' : '+');
        if ($bathrooms) $search_terms[] = "Baths: $bathrooms" . ($bathrooms === '3+' ? '' : '+');
        
        // Property type taxonomy display
        if ($property_type) {
            $property_type_term = get_term_by(is_numeric($property_type) ? 'id' : 'slug', $property_type, 'property_type');
            $property_type_name = $property_type_term ? $property_type_term->name : ucfirst(str_replace('-', ' ', $property_type));
            $search_terms[] = "Type: $property_type_name";
        }
        
        // Property status taxonomy display
        if ($property_status) {
            $property_status_term = get_term_by(is_numeric($property_status) ? 'id' : 'slug', $property_status, 'property_status');
            $property_status_name = $property_status_term ? $property_status_term->name : ucfirst(str_replace('-', ' ', $property_status));
            $search_terms[] = "Status: $property_status_name";
        }
        
        // Listing features taxonomy display
        if (!empty($listing_features)) {
            $feature_names = [];
            foreach ($listing_features as $feature) {
                $feature_term = get_term_by('slug', $feature, 'listing_features');
                $feature_names[] = $feature_term ? $feature_term->name : ucfirst(str_replace('-', ' ', $feature));
            }
            $search_terms[] = "Features: " . implode(', ', $feature_names);
        }
        
        // Listing location taxonomy display
        if ($listing_location) {
            $location_term = get_term_by(is_numeric($listing_location) ? 'id' : 'slug', $listing_location, 'listing_location');
            $location_name = $location_term ? $location_term->name : ucfirst(str_replace('-', ' ', $listing_location));
            $search_terms[] = "Location: $location_name";
        }
        
        // Legacy zip code display
        if ($zip_code) $search_terms[] = "Zip: $zip_code";
        
        $subtitle = empty($search_terms) 
            ? "Discover your perfect home from our extensive collection of properties"
            : "Found <span data-results-count>" . number_format($total_listings) . "</span> properties matching: " . implode(', ', $search_terms);
        
        // Custom hero section with integrated search form
        ?>
        <div class="hph-archive-listing-hero" style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/hero-bg1.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <div class="hph-archive-hero-overlay"></div>
            <div class="hph-archive-hero-container">
                <div class="hph-archive-hero-content">
                    <h1 class="hph-hero-headline">
                        <?php echo empty($search_terms) ? 'Browse All Properties' : 'Property Search Results'; ?>
                    </h1>
                    <p class="hph-hero-subheadline"><?php echo $subtitle; ?></p>

                    <!-- Integrated Search Form -->
                    <div class="hph-hero-search">
                        <form class="hph-hero-search-form" action="<?php echo esc_url(home_url('/listings/')); ?>" method="GET">

                            <div class="hph-hero-search-row">
                                <!-- Search Input -->
                                <div class="hph-search-input-container hph-hero-search-input">
                                    <div class="hph-search-input-wrapper has-close">
                                        <input type="text"
                                               name="s"
                                               id="hero-search-input"
                                               class="hph-form-input hph-search-input"
                                               placeholder="Enter city, zip, address, or MLS#"
                                               value="<?php echo esc_attr($search); ?>"
                                               autocomplete="off">
                                        <i class="fas fa-search hph-search-input-icon"></i>
                                        <?php if (!empty($search)) : ?>
                                            <button type="button" class="hph-btn-clear hph-btn-circle" aria-label="Clear search">
                                                <i class="fas fa-times hph-btn-icon"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php get_template_part('template-parts/components/search/search-autocomplete', null, [
                                        'input_id' => 'hero-search-input',
                                        'container_id' => 'hero-search-autocomplete',
                                        'post_types' => ['listing'],
                                        'max_suggestions' => 8
                                    ]); ?>
                                </div>

                                <!-- Min Price -->
                                <select name="min_price" class="hph-form-select">
                                    <option value="">Min Price</option>
                                    <?php
                                    $price_options = [100000, 150000, 200000, 250000, 300000, 400000, 500000, 600000, 700000, 800000, 900000, 1000000, 1250000, 1500000, 2000000];
                                    foreach ($price_options as $price) :
                                        $selected = ($min_price == $price) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $price; ?>" <?php echo $selected; ?>>
                                            $<?php echo $price >= 1000000 ? number_format($price / 1000000, 2) . 'M' : number_format($price / 1000) . 'k'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <!-- Max Price -->
                                <select name="max_price" class="hph-form-select">
                                    <option value="">Max Price</option>
                                    <?php
                                    $max_price_options = [150000, 200000, 250000, 300000, 400000, 500000, 600000, 700000, 800000, 900000, 1000000, 1250000, 1500000, 2000000, 3000000];
                                    foreach ($max_price_options as $price) :
                                        $selected = ($max_price == $price) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $price; ?>" <?php echo $selected; ?>>
                                            $<?php echo $price >= 1000000 ? number_format($price / 1000000, 2) . 'M' : number_format($price / 1000) . 'k'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <!-- Beds -->
                                <select name="bedrooms" class="hph-form-select">
                                    <option value="">Beds</option>
                                    <?php for ($i = 1; $i <= 5; $i++) :
                                        $selected = ($bedrooms == $i) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $i; ?>" <?php echo $selected; ?>>
                                            <?php echo $i; ?>+
                                        </option>
                                    <?php endfor; ?>
                                </select>

                                <!-- Baths -->
                                <select name="bathrooms" class="hph-form-select">
                                    <option value="">Baths</option>
                                    <?php for ($i = 1; $i <= 4; $i++) :
                                        $selected = ($bathrooms == $i) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $i; ?>" <?php echo $selected; ?>>
                                            <?php echo $i; ?>+
                                        </option>
                                    <?php endfor; ?>
                                </select>

                                <!-- Submit Button -->
                                <button type="submit" class="hph-btn hph-btn-primary">
                                    <i class="fas fa-search hph-btn-icon"></i>
                                    Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Controls - Hidden in Map View -->
    <div class="hph-archive-controls-section" data-hide-in-views="map">
              <div class="hph-archive-controls">
                <!-- Left: Results Count & Filter Summary -->
                <div class="hph-archive-controls-left">
                    <div class="hph-results-summary" data-results-text>
                        <strong data-results-count><?php echo number_format($listings->found_posts); ?></strong>
                        <span>properties found</span>
                    </div>

                    <?php if (!empty($search_terms)) : ?>
                    <div class="hph-active-filters">
                        <span class="hph-filters-label">Filters:</span>
                        <?php foreach ($search_terms as $term) : ?>
                            <span class="hph-filter-tag"><?php echo esc_html($term); ?></span>
                        <?php endforeach; ?>
                        <button type="button" class="hpt-clear-filters" data-clear-filters>
                            Clear All <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Sort & View Controls -->
                <div class="hph-archive-controls-right">

                    <!-- Sort Dropdown -->
                    <div class="hph-sort-controls">
                        <label for="listings-sort" class="hph-sort-label">Sort by:</label>
                        <select id="listings-sort" name="sort" class="hph-sort-select" data-sort-select>
                            <option value="date_desc" <?php selected($sort ?? 'date_desc', 'date_desc'); ?>>Newest First</option>
                            <option value="date_asc" <?php selected($sort ?? '', 'date_asc'); ?>>Oldest First</option>
                            <option value="price_desc" <?php selected($sort ?? '', 'price_desc'); ?>>Price: High to Low</option>
                            <option value="price_asc" <?php selected($sort ?? '', 'price_asc'); ?>>Price: Low to High</option>
                            <option value="bedrooms_desc" <?php selected($sort ?? '', 'bedrooms_desc'); ?>>Most Bedrooms</option>
                            <option value="sqft_desc" <?php selected($sort ?? '', 'sqft_desc'); ?>>Largest First</option>
                        </select>
                    </div>

                    <!-- View Toggle Controls -->
                    <div class="hph-view-controls">
                        <div class="hph-view-toggle-group" role="group" aria-label="View options">
                            <button type="button" class="hph-view-btn active" data-view="grid" title="Grid View" aria-pressed="true">
                                <i class="fas fa-th-large"></i>
                                <span class="sr-only">Grid</span>
                            </button>
                            <button type="button" class="hph-view-btn" data-view="list" title="List View" aria-pressed="false">
                                <i class="fas fa-list"></i>
                                <span class="sr-only">List</span>
                            </button>
                            <!-- Map view commented out -->
                            <?php /*
                            <button type="button" class="hph-view-btn" data-view="map" title="Map View" aria-pressed="false">
                                <i class="fas fa-map-marked-alt"></i>
                                <span class="sr-only">Map</span>
                            </button>
                            */ ?>
                        </div>
                    </div>
                </div>
            </div>
        
    </div>

    <!-- Standard Views (Grid & List) - Hidden in Map View -->
    <div class="hph-standard-views-container" data-hide-in-views="map">
        <?php if ($listings->have_posts()) : ?>

        <div class="hph-listings-container">
            <div class="hph-full-width-container">
                
                <!-- Grid View -->
                <div class="hph-listings-grid hph-view-content active" data-view-content="grid" data-ajax-results="grid" data-results-container>
                    <div class="hph-grid hph-grid-cols-1 sm:hph-grid-cols-2 lg:hph-grid-cols-3 xl:hph-grid-cols-4 hph-gap-xl" data-listings-container="grid">
                        <?php while ($listings->have_posts()) : $listings->the_post();
                            $current_id = get_the_ID();
                            $change_data = $listings_change_data[$current_id] ?? [];
                        ?>
                            <?php hph_component('universal-card', [
                                'post_id' => $current_id,
                                'post_type' => 'listing',
                                'layout' => 'vertical',
                                'variant' => 'default',
                                'size' => 'md',
                                // Meta display options
                                'show_days_on_market' => true,
                                'show_last_updated' => true,
                                // Change tracking data
                                'listing_changes' => $change_data['changes'] ?? [],
                                'listing_badges' => $change_data['badges'] ?? [],
                                'has_recent_changes' => $change_data['has_recent_changes'] ?? false,
                                'is_new_listing' => $change_data['is_new_listing'] ?? false
                            ]); ?>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <!-- List View -->
                <div class="hph-listings-list hph-view-content" data-view-content="list" data-ajax-results="list" data-results-container>
                    <div class="hph-list-container" data-listings-container="list">
                        <?php $listings->rewind_posts(); while ($listings->have_posts()) : $listings->the_post();
                            $current_id = get_the_ID();
                            $change_data = $listings_change_data[$current_id] ?? [];
                        ?>
                            <?php hph_component('universal-card', [
                                'post_id' => $current_id,
                                'post_type' => 'listing',
                                'layout' => 'horizontal',
                                'variant' => 'default',
                                'size' => 'md',
                                // Meta display options
                                'show_days_on_market' => true,
                                'show_last_updated' => true,
                                // Change tracking data
                                'listing_changes' => $change_data['changes'] ?? [],
                                'listing_badges' => $change_data['badges'] ?? [],
                                'has_recent_changes' => $change_data['has_recent_changes'] ?? false,
                                'is_new_listing' => $change_data['is_new_listing'] ?? false
                            ]); ?>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /.hph-listing-archive -->

<!-- Map View - COMMENTED OUT -->
    <?php /*
    // Build listings data for map BEFORE using it
    $map_listings = [];
    if ($listings->have_posts()) {
        while ($listings->have_posts()) {
            $listings->the_post();
            $listing_id = get_the_ID();

            // Get coordinates
            $coordinates = null;
            if (function_exists('hpt_get_listing_coordinates')) {
                try {
                    $coordinates = hpt_get_listing_coordinates($listing_id);
                } catch (Exception $e) {
                    $lat = get_field('latitude', $listing_id);
                    $lng = get_field('longitude', $listing_id);
                    $coordinates = ($lat && $lng) ? ['lat' => floatval($lat), 'lng' => floatval($lng)] : null;
                }
            } else {
                $lat = get_field('latitude', $listing_id);
                $lng = get_field('longitude', $listing_id);
                $coordinates = ($lat && $lng) ? ['lat' => floatval($lat), 'lng' => floatval($lng)] : null;
            }

            if ($coordinates && $coordinates['lat'] && $coordinates['lng']) {
                // Build listing data for map
                $listing_data = [
                    'id' => $listing_id,
                    'title' => get_the_title($listing_id),
                    'latitude' => $coordinates['lat'],
                    'longitude' => $coordinates['lng'],
                    'permalink' => get_permalink($listing_id),
                    'status' => get_field('listing_status', $listing_id) ?: 'active',
                    'featured_image' => get_the_post_thumbnail_url($listing_id, 'medium')
                ];

                // Add price
                if (function_exists('hpt_get_listing_price')) {
                    try {
                        $listing_data['price'] = hpt_get_listing_price($listing_id);
                    } catch (Exception $e) {
                        $listing_data['price'] = get_field('price', $listing_id);
                    }
                } else {
                    $listing_data['price'] = get_field('price', $listing_id);
                }

                // Add basic stats
                $listing_data['bedrooms'] = get_field('bedrooms', $listing_id);
                $listing_data['bathrooms'] = get_field('bathrooms_full', $listing_id);
                $listing_data['square_feet'] = get_field('square_feet', $listing_id);

                // Add address
                if (function_exists('hpt_get_listing_address')) {
                    try {
                        $address_data = hpt_get_listing_address($listing_id);
                        $listing_data['street_address'] = $address_data['street_address'] ?? '';
                        $listing_data['city'] = $address_data['city'] ?? '';
                        $listing_data['state'] = $address_data['state'] ?? '';
                        $listing_data['zip_code'] = $address_data['zip_code'] ?? '';
                    } catch (Exception $e) {
                        $listing_data['street_address'] = trim((get_field('street_number', $listing_id) ?: '') . ' ' . (get_field('street_name', $listing_id) ?: '') . ' ' . (get_field('street_type', $listing_id) ?: ''));
                        $listing_data['city'] = get_field('city', $listing_id);
                        $listing_data['state'] = get_field('state', $listing_id);
                        $listing_data['zip_code'] = get_field('zip_code', $listing_id);
                    }
                } else {
                    $listing_data['street_address'] = trim((get_field('street_number', $listing_id) ?: '') . ' ' . (get_field('street_name', $listing_id) ?: '') . ' ' . (get_field('street_type', $listing_id) ?: ''));
                    $listing_data['city'] = get_field('city', $listing_id);
                    $listing_data['state'] = get_field('state', $listing_id);
                    $listing_data['zip_code'] = get_field('zip_code', $listing_id);
                }

                $map_listings[] = $listing_data;
            }
        }
        wp_reset_postdata();
    }

    // Calculate center point from all listings
    $map_center = [-75.1398, 38.7816]; // Default to Delaware
    if (!empty($map_listings)) {
        $avg_lat = array_sum(array_column($map_listings, 'latitude')) / count($map_listings);
        $avg_lng = array_sum(array_column($map_listings, 'longitude')) / count($map_listings);
        $map_center = [$avg_lng, $avg_lat];
    }
    */ ?>

    <?php /*
    <div class="hph-map-view-layout hph-view-content" data-view-content="map" style="display: none;">

        <!-- Specialized Map Header -->
        <header class="hph-map-header">
            <div class="hph-map-header-container">
                <!-- Left: Logo/Brand -->
                <div class="hph-map-header-left">
                    <a href="<?php echo home_url(); ?>" class="hph-map-logo">
                        <?php
                        $custom_logo_id = get_theme_mod('custom_logo');
                        if ($custom_logo_id) {
                            echo wp_get_attachment_image($custom_logo_id, 'full', false, ['class' => 'hph-map-logo-img']);
                        } else {
                            echo '<span class="hph-map-logo-text">' . get_bloginfo('name') . '</span>';
                        }
                        ?>
                    </a>
                </div>

                <!-- Center: Search Bar -->
                <div class="hph-map-header-center">
                    <form class="hph-map-search-form" data-ajax-search method="GET" action="<?php echo get_post_type_archive_link('listing'); ?>">
                        <div class="hph-map-search-container">
                            <input type="text"
                                   name="s"
                                   placeholder="Search by address, city, or ZIP code"
                                   class="hph-map-search-input"
                                   value="<?php echo get_search_query(); ?>"
                                   autocomplete="off">

                            <div class="hph-map-search-filters">
                                <!-- Property Type -->
                                <select name="property_type" class="hph-map-filter-select">
                                    <option value="">Property Type</option>
                                    <option value="single-family" <?php selected($property_type, 'single-family'); ?>>Single Family</option>
                                    <option value="condo" <?php selected($property_type, 'condo'); ?>>Condo</option>
                                    <option value="townhouse" <?php selected($property_type, 'townhouse'); ?>>Townhouse</option>
                                    <option value="multi-family" <?php selected($property_type, 'multi-family'); ?>>Multi-Family</option>
                                </select>

                                <!-- Min Price -->
                                <select name="min_price" class="hph-map-filter-select">
                                    <option value="">Min Price</option>
                                    <?php
                                    $price_options = [100000, 150000, 200000, 250000, 300000, 400000, 500000, 600000, 700000, 800000, 900000, 1000000, 1250000, 1500000, 2000000];
                                    foreach ($price_options as $price) :
                                        $selected = ($min_price == $price) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $price; ?>" <?php echo $selected; ?>>
                                            $<?php echo $price >= 1000000 ? number_format($price / 1000000, 2) . 'M' : number_format($price / 1000) . 'k'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <!-- Max Price -->
                                <select name="max_price" class="hph-map-filter-select">
                                    <option value="">Max Price</option>
                                    <?php
                                    $max_price_options = [150000, 200000, 250000, 300000, 400000, 500000, 600000, 700000, 800000, 900000, 1000000, 1250000, 1500000, 2000000, 3000000];
                                    foreach ($max_price_options as $price) :
                                        $selected = ($max_price == $price) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $price; ?>" <?php echo $selected; ?>>
                                            $<?php echo $price >= 1000000 ? number_format($price / 1000000, 2) . 'M' : number_format($price / 1000) . 'k'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <!-- Beds -->
                                <select name="bedrooms" class="hph-map-filter-select">
                                    <option value="">Beds</option>
                                    <?php for ($i = 1; $i <= 5; $i++) :
                                        $selected = ($bedrooms == $i) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $i; ?>" <?php echo $selected; ?>>
                                            <?php echo $i; ?>+
                                        </option>
                                    <?php endfor; ?>
                                </select>

                                <!-- Baths -->
                                <select name="bathrooms" class="hph-map-filter-select">
                                    <option value="">Baths</option>
                                    <?php for ($i = 1; $i <= 4; $i++) :
                                        $selected = ($bathrooms == $i) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $i; ?>" <?php echo $selected; ?>>
                                            <?php echo $i; ?>+
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <button type="submit" class="hph-map-search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Results Count Only -->
                <div class="hph-map-header-right">
                    <div class="hph-map-results-count" data-results-text>
                        <strong data-results-count><?php echo $listings->found_posts; ?></strong> properties
                    </div>
                </div>
            </div>
        </header>

        <!-- Map Container for Archive Enhanced System -->
        <div id="mapbox-listings-map"
             class="hph-map-canvas"
             data-map-center="<?php echo esc_attr(json_encode($map_center)); ?>"
             data-map-zoom="12"
             data-map-style="mapbox://styles/mapbox/light-v11"
             data-map-listings="<?php echo esc_attr(json_encode($map_listings)); ?>"
             <?php if (!empty($zip_code)) : ?>
                data-zip-code="<?php echo esc_attr($zip_code); ?>"
                data-show-zip-boundary="true"
             <?php endif; ?>
             data-archive-map="true"
             style="height: 100vh; width: 100vw; margin: 0; padding: 0;">
            <div class="hph-map-loading">
                <div class="hph-map-spinner"></div>
                <p>Loading map...</p>
            </div>
        </div>

        <!-- Floating sidebar panel -->
        <div class="hph-map-panel hph-map-sidebar">
            <div class="hph-map-panel-header">
                <button class="hph-map-panel-close" aria-label="Close map view" data-view="grid">
                    <i class="fas fa-times"></i>
                </button>
                <div class="hph-map-results-summary">
                    <span data-results-count><?php echo $listings->found_posts; ?></span> Properties
                </div>
            </div>

            <div class="hph-map-panel-content hph-sidebar-content">
                <!-- This will be populated by JavaScript with map-style cards -->
                <div class="hph-map-loading-sidebar">
                    <div class="hph-loading-spinner"></div>
                    <p>Loading properties...</p>
                </div>
            </div>
        </div>
    </div>
    */ ?>

    <?php if ($listings->have_posts()) : ?>

        <!-- Load More Section -->
        <div class="hph-load-more-section">
            <div class="hph-container">
                <div class="hph-load-more-content">

                    <!-- Results Summary -->
                    <div class="hph-results-summary-detailed">
                        <span>
                            Showing
                            <strong data-current-showing><?php echo min($paged * 12, $listings->found_posts); ?></strong>
                            of
                            <strong data-total-results><?php echo number_format($listings->found_posts); ?></strong>
                            properties
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="hph-load-more-actions">

                        <!-- Load More Button -->
                        <?php if ($listings->max_num_pages > 1 && $paged < $listings->max_num_pages) : ?>
                        <button type="button"
                                class="hph-load-more-btn hph-button hph-button-primary"
                                data-load-more
                                data-current-page="<?php echo $paged; ?>"
                                data-max-pages="<?php echo $listings->max_num_pages; ?>"
                                data-post-type="listing">
                            <span class="hph-load-more-text">Load More Properties</span>
                            <span class="hph-load-more-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </span>
                        </button>
                        <?php endif; ?>

                        <!-- Market Wide Search Button -->
                        <a href="https://search.parkergroupsells.com/search/map?s[orderBy]=featured&s[page]=1&s[bbox]=38.830380586773515%2C-75.05765152684316%2C38.51409016575655%2C-75.69485855809316"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="hph-market-search-btn hph-button hph-button-outline">
                            <i class="fas fa-external-link-alt"></i>
                            <span>Search Entire Market</span>
                        </a>
                    </div>

                    <!-- Additional Info -->
                    <div class="hph-load-more-info">
                        <p class="hph-load-more-note">
                            Want to see more properties? Use our market-wide search to explore all available listings in the region.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    <?php else : ?>

        <!-- Empty State - Will be replaced by AJAX response when filters are active -->
        <div class="hph-empty-state" data-initial-empty-state>
            <div class="hph-container">
                <div class="hph-empty-content">
                    <div class="hph-empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h2 class="hph-empty-title">No Properties Found</h2>
                    <p class="hph-empty-description">
                        We couldn't find any properties matching your criteria.
                        Try adjusting your filters or <a href="<?php echo get_post_type_archive_link('listing'); ?>">browse all properties</a>.
                    </p>
                    <div class="hph-empty-actions">
                        <a href="<?php echo get_post_type_archive_link('listing'); ?>" class="hph-btn hph-btn-primary">
                            <i class="fas fa-home hph-btn-icon"></i>
                            View All Properties
                        </a>
                        <button type="button" class="hpt-clear-filters" data-clear-filters>
                            <i class="fas fa-filter hph-btn-icon"></i>
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure correct view mode is applied on page load
    // Remove any map view classes that might be incorrectly applied
    document.body.classList.remove('hph-view-map', 'map-view-active');
    
    // Apply grid view as default (matches our active button)
    document.body.classList.add('hph-view-grid');
    
    // Ensure hero section is visible
    const heroSection = document.querySelector('.hph-archive-hero-section, .hph-archive-listing-hero');
    if (heroSection) {
        heroSection.style.display = '';
    }
    
    // Hero search form functionality
    const heroSearchForm = document.querySelector('.hph-hero-search-form');
    const clearButton = document.querySelector('.hph-hero-search-input .hph-btn-clear');
    const searchInput = document.getElementById('hero-search-input');

    // Handle clear button click
    if (clearButton && searchInput) {
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            // Hide the clear button
            clearButton.style.display = 'none';
        });

        // Show/hide clear button based on input content
        searchInput.addEventListener('input', function() {
            if (this.value.trim()) {
                clearButton.style.display = 'flex';
            } else {
                clearButton.style.display = 'none';
            }
        });
    }

    // Handle form submission
    if (heroSearchForm) {
        heroSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const baseUrl = '<?php echo esc_js(home_url('/listings/')); ?>';
            const params = new URLSearchParams();

            // Add all form fields to URL
            for (let [key, value] of formData.entries()) {
                if (value && value.trim() !== '') {
                    params.append(key, value);
                }
            }

            // Navigate to filtered results
            const finalUrl = params.toString() ? baseUrl + '?' + params.toString() : baseUrl;
            window.location.href = finalUrl;
        });
    }
});

// Initialize HPH Unified Archive System
jQuery(document).ready(function($) {
    $(document).on('hph:initialized', function() {
        if (window.HPH && HPH.modules.has('archiveUnified')) {
            // The archiveUnified module is automatically initialized by the unified system
            console.log('HPH Unified Archive System is ready');
        } else {
            console.warn('HPH archiveUnified module not found or HPH not loaded');
        }
    });
});
</script>

<?php
wp_reset_postdata();
get_footer();
?>
