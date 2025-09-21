<?php
/**
 * Enhanced Listing Archive Template
 * Features: Multiple view modes, enhanced filtering, map integration
 */

// Enqueue archive-specific assets BEFORE wp_head() runs
wp_enqueue_style('hph-archive-enhanced', get_template_directory_uri() . '/assets/css/framework/features/listing/archive-enhanced.css', ['hph-framework'], '1.0.0');
wp_enqueue_style('hph-archive-map-fixes', get_template_directory_uri() . '/assets/css/archive-map-fixes.css', ['hph-framework'], filemtime(get_template_directory() . '/assets/css/archive-map-fixes.css'));
wp_enqueue_script('hph-archive-enhanced', get_template_directory_uri() . '/assets/js/pages/archive-listing-enhanced.js', ['hph-framework'], '1.0.0', true);

// Enqueue HPH Map component for map view
wp_enqueue_script('hph-map-component', get_template_directory_uri() . '/assets/js/components/hph-map.js', ['hph-framework'], '1.0.0', true);

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

// Set Mapbox configuration for HPH Map component
if (!empty($mapbox_token)) {
    wp_add_inline_script('hph-archive-enhanced', '
        // Ensure Mapbox configuration is available for HPH Map component
        window.hph_mapbox_config = window.hph_mapbox_config || {};
        window.hph_mapbox_config.access_token = "' . esc_js($mapbox_token) . '";
        
        console.log("Archive: Mapbox token set for HPH Map component:", window.hph_mapbox_config.access_token ? window.hph_mapbox_config.access_token.substring(0, 20) + "..." : "NOT SET");
    ', 'before');
}

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
$property_type = sanitize_text_field($_GET['property_type'] ?? '');
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

// Initialize meta_query only if we need it
$meta_queries = [];

// Add price range filter
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

// Add bedrooms filter
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

// Add bathrooms filter
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

// Add property type filter
if (!empty($property_type)) {
    $meta_queries[] = [
        'key' => 'property_type',
        'value' => $property_type,
        'compare' => '='
    ];
}

// Add zip code filter
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

$listings = new WP_Query($args);
?>

<div class="hph-listing-archive hph-clean-layout">

    <!-- Archive Hero Section - Hidden in Map View -->
    <div class="hph-archive-hero-section" data-hide-in-views="map">
        <?php 
        // Get count of current listings
        $total_listings = $listings->found_posts;
        
        // Build search terms display
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
        if ($property_type) $search_terms[] = "Type: " . ucfirst(str_replace('-', ' ', $property_type));
        if ($zip_code) $search_terms[] = "Zip: $zip_code";
        
        $subtitle = empty($search_terms) 
            ? "Discover your perfect home from our extensive collection of properties" 
            : "Found " . number_format($total_listings) . " properties matching: " . implode(', ', $search_terms);
        
        get_template_part('template-parts/sections/archive-hero', null, [
            'style' => 'image',
            'background_image' => get_template_directory_uri() . '/assets/images/hero-bg.jpg',
            'height' => 'md',
            'overlay' => 'dark',
            'overlay_opacity' => '50',
            'headline' => empty($search_terms) ? 'Browse All Properties' : 'Property Search Results',
            'subheadline' => $subtitle,
            'show_search' => false, // Hide search form since header search is expanded
            'fade_in' => true,
            'alignment' => 'center'
        ]); 
        ?>
    </div>

    <!-- Standard Views (Grid & List) - Hidden in Map View -->
    <div class="hph-standard-views-container" data-hide-in-views="map">
        <?php if ($listings->have_posts()) : ?>
        <div class="hph-listings-container">
            <div class="hph-container">
                
                <!-- Grid View -->
                <div class="hph-listings-grid hph-view-content active" data-view-content="grid">
                    <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-2 xl:hph-grid-cols-3 hph-gap-xl">
                        <?php while ($listings->have_posts()) : $listings->the_post(); ?>
                            <?php get_template_part('template-parts/listing-card-enhanced', null, ['listing_id' => get_the_ID()]); ?>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <!-- List View -->
                <div class="hph-listings-list hph-view-content" data-view-content="list">
                    <div class="hph-list-container">
                        <?php $listings->rewind_posts(); while ($listings->have_posts()) : $listings->the_post(); ?>
                            <?php get_template_part('template-parts/listing-card-list', null, ['listing_id' => get_the_ID()]); ?>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Map View - Full Viewport (No Scroll) -->
    <?php
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
    ?>
    
    <div class="hph-map-view-layout hph-view-content" data-view-content="map" style="display: none;">
        
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
             style="height: 100vh; width: 100vw;">
            <div class="hph-map-loading">
                <div class="hph-map-spinner"></div>
                <p>Loading map...</p>
            </div>
        </div>
        
        <!-- Floating sidebar panel -->
        <div class="hph-map-panel">
            <div class="hph-map-panel-header">
                <div class="hph-map-panel-title">
                    <?php if ($listings->have_posts()) : ?>
                        <strong><?php echo number_format($listings->found_posts); ?></strong> 
                        propert<?php echo $listings->found_posts != 1 ? 'ies' : 'y'; ?> found
                    <?php else : ?>
                        No properties found
                    <?php endif; ?>
                </div>
                <button class="hph-map-panel-close" aria-label="Close map view">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="hph-map-panel-content">
                <?php if ($listings->have_posts()) : ?>
                    <div class="hph-map-listings">
                        <?php $listings->rewind_posts(); while ($listings->have_posts()) : $listings->the_post(); ?>
                            <?php get_template_part('template-parts/listing-card-map', null, ['listing_id' => get_the_ID()]); ?>
                        <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <div class="hph-map-empty">
                        <p>No properties to display on the map.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($listings->have_posts()) : ?>

        <!-- Pagination Section -->
        <?php if ($listings->max_num_pages > 1) : ?>
            <div class="hph-pagination-section">
                <div class="hph-container">
                    <nav class="hph-pagination-nav" aria-label="Listings pagination">
                        <?php
                        $pagination_links = paginate_links([
                            'total' => $listings->max_num_pages,
                            'current' => $paged,
                            'prev_text' => '<i class="fas fa-chevron-left"></i><span>Previous</span>',
                            'next_text' => '<span>Next</span><i class="fas fa-chevron-right"></i>',
                            'type' => 'array',
                            'before_page_number' => '<span class="page-number">',
                            'after_page_number' => '</span>'
                        ]);
                        
                        if (is_array($pagination_links)) {
                            echo '<div class="hph-pagination-container">';
                            foreach ($pagination_links as $link) {
                                echo $link;
                            }
                            echo '</div>';
                        }
                        ?>
                    </nav>
                    
                    <!-- Results Summary -->
                    <div class="hph-pagination-summary">
                        <span>
                            Showing 
                            <strong><?php echo (($paged - 1) * 12) + 1; ?></strong>
                            to 
                            <strong><?php echo min($paged * 12, $listings->found_posts); ?></strong>
                            of 
                            <strong><?php echo number_format($listings->found_posts); ?></strong>
                            properties
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <!-- Empty State -->
        <div class="hph-empty-state">
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
                            <i class="fas fa-home"></i>
                            View All Properties
                        </a>
                        <button type="button" class="hph-btn hph-btn-ghost" onclick="document.querySelector('[data-filter-toggle]')?.click()">
                            <i class="fas fa-filter"></i>
                            Modify Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php
wp_reset_postdata();
get_footer();
?>
