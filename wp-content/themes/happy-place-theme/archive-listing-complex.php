<?php
/**
 * Enhanced Archive Template for Listings with Grid, List, and Map Views
 * 
 * Features:
 * - Three view modes: Grid, List, and Full-screen Map with sidebar
 * - AJAX filtering and pagination
 * - Mapbox integration for map view
 * - Instantaneous updates without page reload
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

get_header();

// Get initial parameters
$search_query = sanitize_text_field($_GET['s'] ?? '');
$view_mode = sanitize_text_field($_GET['view'] ?? 'grid');
$sort = sanitize_text_field($_GET['sort'] ?? 'date_desc');

// Get Mapbox configuration from plugin or theme
$mapbox_key = '';
if (function_exists('hp_get_mapbox_token')) {
    $mapbox_key = hp_get_mapbox_token();
} elseif (function_exists('hp_mapbox_available') && hp_mapbox_available()) {
    $mapbox_key = defined('HP_MAPBOX_ACCESS_TOKEN') ? HP_MAPBOX_ACCESS_TOKEN : '';
}

// Fallback to theme customizer if plugin not available
if (empty($mapbox_key)) {
    $mapbox_key = get_theme_mod('mapbox_api_key', '');
}

// Get map center and zoom from plugin
$map_center = [-98.4936, 29.4241]; // Default San Antonio
$map_zoom = 11; // Default zoom

if (function_exists('hp_get_map_center')) {
    $plugin_center = hp_get_map_center();
    if (!empty($plugin_center) && isset($plugin_center['lat'], $plugin_center['lng'])) {
        $map_center = [$plugin_center['lng'], $plugin_center['lat']]; // Mapbox uses [lng, lat]
    }
}

if (function_exists('hp_get_map_zoom')) {
    $plugin_zoom = hp_get_map_zoom();
    if ($plugin_zoom > 0) {
        $map_zoom = $plugin_zoom;
    }
}

// Enqueue required scripts if Mapbox is configured
if (!empty($mapbox_key)) {
    wp_enqueue_script('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], '2.15.0', true);
    wp_enqueue_style('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css', [], '2.15.0');
}

// NOTE: Temporarily disabling competing JavaScript files to debug inline script
// wp_enqueue_script('hph-archive-listings-enhanced', get_template_directory_uri() . '/assets/js/archive-listings-enhanced.js', ['jquery'], '1.0.0', true);

// Enqueue quick fixes CSS for missing elements
wp_enqueue_style('hph-quick-fixes', get_template_directory_uri() . '/assets/css/quick-fixes.css', [], '1.0.0');
wp_enqueue_style('hph-archive-map-fixes', get_template_directory_uri() . '/assets/css/archive-map-fixes.css', [], '1.0.0');

// Localize script for AJAX
wp_localize_script('hph-archive-listings-enhanced', 'hph_listings', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_listings_nonce'),
    'mapbox_token' => $mapbox_key,
    'default_center' => $map_center,
    'default_zoom' => $map_zoom
]);

// Initialize missing global hphArchive for archive-ajax.js compatibility
wp_localize_script('hph-archive-listings-enhanced', 'hphArchive', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_archive_nonce'),
    'postType' => 'listing',
    'currentView' => $view_mode,
    'currentSort' => $sort,
    'autoFilter' => true
]);
?>

<!-- Archive Hero with Search -->
<?php 
get_template_part('template-parts/sections/archive-hero', null, [
    'headline' => 'Find Your Perfect Home',
    'subheadline' => 'Browse through our extensive collection of properties',
    'theme' => 'primary',
    'height' => 'md',
    'show_search' => true,
    'show_filters' => true,
    'filter_layout' => 'inline',
    'show_quick_searches' => false, // Moving quick searches to main container
    'search_action' => '', // Prevent form submission, handle with AJAX
    'background_image' => get_template_directory_uri() . '/assets/images/hero-bg.jpg',
    'overlay' => 'dark',
    'fade_in' => true
]);
?>

<!-- Missing DOM Elements for JavaScript -->
<div class="hph-archive__header" style="display: none;">
    <div class="hph-archive__controls">
        <!-- Search Toggle Button -->
        <button class="hph-archive__search-toggle" aria-expanded="false">
            <i class="fas fa-search"></i>
            <span>Search & Filter</span>
        </button>
    </div>
    
    <!-- Search Panel -->
    <div class="hph-archive__search-panel" aria-hidden="true">
        <div class="hph-advanced-search">
            <!-- Search form content will be populated by JavaScript -->
        </div>
    </div>
    
    <!-- Active Filters Container -->
    <div class="hph-active-filters-container"></div>
</div>

<!-- Advanced Filters Wrapper -->
<div class="hph-advanced-filters-wrapper" style="display: none;">
    <button class="hph-advanced-toggle">
        <span>Advanced Filters</span>
    </button>
    <div class="hph-advanced-filters">
        <!-- Filter content -->
    </div>
</div>

<!-- DEBUG: Manual test button -->
<div style="position: fixed; top: 80px; right: 20px; z-index: 9999; background: #ff6b6b; color: white; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
    <button id="debug-test-ajax" style="background: white; color: #ff6b6b; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; font-weight: bold;">
        🧪 TEST AJAX
    </button>
    <div id="debug-status" style="margin-top: 8px; font-size: 12px;">Ready</div>
</div>

<!-- Main Archive Container -->
<div id="listings-archive" class="hph-listings-archive" data-view="<?php echo esc_attr($view_mode); ?>">
    
    <!-- View Controls Bar -->
    <div class="hph-archive-controls hph-bg-white hph-border-b hph-sticky hph-top-0 hph-z-100">
        <div class="hph-container hph-px-lg hph-py-md">
            <!-- Quick Filters Row -->
            <div class="hph-quick-filters hph-mb-md" style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-sm); align-items: center;">
                <span style="font-weight: var(--hph-font-semibold); color: var(--hph-gray-700); margin-right: var(--hph-margin-sm);">Quick Filters:</span>
                
                <!-- Status Filter Buttons -->
                <button class="hph-quick-filter-btn" data-filter="status" data-value="all" 
                        style="padding: var(--hph-padding-xs) var(--hph-padding-md); background: var(--hph-primary); color: var(--hph-white); border: none; border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm); cursor: pointer; transition: all 0.3s ease;">
                    All Listings
                </button>
                <button class="hph-quick-filter-btn" data-filter="status" data-value="active"
                        style="padding: var(--hph-padding-xs) var(--hph-padding-md); background: var(--hph-gray-200); color: var(--hph-gray-700); border: none; border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm); cursor: pointer; transition: all 0.3s ease;">
                    Active
                </button>
                <button class="hph-quick-filter-btn" data-filter="status" data-value="new"
                        style="padding: var(--hph-padding-xs) var(--hph-padding-md); background: var(--hph-gray-200); color: var(--hph-gray-700); border: none; border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm); cursor: pointer; transition: all 0.3s ease;">
                    New Listings
                </button>
                <button class="hph-quick-filter-btn" data-filter="status" data-value="reduced"
                        style="padding: var(--hph-padding-xs) var(--hph-padding-md); background: var(--hph-gray-200); color: var(--hph-gray-700); border: none; border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm); cursor: pointer; transition: all 0.3s ease;">
                    Price Reduced
                </button>
                
                <span style="margin: 0 var(--hph-margin-sm); color: var(--hph-gray-400);">|</span>
                
                <!-- Feature Filter Buttons -->
                <button class="hph-quick-filter-btn" data-filter="feature" data-value="waterfront"
                        style="padding: var(--hph-padding-xs) var(--hph-padding-md); background: var(--hph-gray-200); color: var(--hph-gray-700); border: none; border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm); cursor: pointer; transition: all 0.3s ease;">
                    <i class="fas fa-water"></i> Waterfront
                </button>
                <button class="hph-quick-filter-btn" data-filter="feature" data-value="pool"
                        style="padding: var(--hph-padding-xs) var(--hph-padding-md); background: var(--hph-gray-200); color: var(--hph-gray-700); border: none; border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm); cursor: pointer; transition: all 0.3s ease;">
                    <i class="fas fa-swimming-pool"></i> With Pool
                </button>
                <button class="hph-quick-filter-btn" data-filter="feature" data-value="garage"
                        style="padding: var(--hph-padding-xs) var(--hph-padding-md); background: var(--hph-gray-200); color: var(--hph-gray-700); border: none; border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm); cursor: pointer; transition: all 0.3s ease;">
                    <i class="fas fa-warehouse"></i> Garage
                </button>
                
                <!-- Clear Filters -->
                <button id="clear-quick-filters" 
                        style="padding: var(--hph-padding-xs) var(--hph-padding-md); background: transparent; color: var(--hph-primary); border: 1px solid var(--hph-primary); border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm); cursor: pointer; transition: all 0.3s ease; margin-left: auto;">
                    <i class="fas fa-times"></i> Clear Filters
                </button>
            </div>
            
            <!-- Main Controls Row -->
            <div class="d-flex justify-content-between align-items-center flex-wrap hph-gap-md">
                
                <!-- Results Count -->
                <div class="hph-results-count hph-text-base hph-text-gray-600">
                    <span id="results-count"><?php echo $direct_query->found_posts; ?></span> properties found
                </div>
                
                <!-- View Switcher -->
                <div class="hph-view-switcher">
                    <button class="hph-view-btn" data-view="grid">
                        <i class="fas fa-th"></i>
                        <span>Grid</span>
                    </button>
                    <button class="hph-view-btn" data-view="list">
                        <i class="fas fa-list"></i>
                        <span>List</span>
                    </button>
                    <button class="hph-view-btn" data-view="map">
                        <i class="fas fa-map"></i>
                        <span>Map</span>
                    </button>
                </div>
                
                <!-- Sort Dropdown -->
                <div class="hph-sort-controls d-flex hph-gap-md align-items-center">
                    <select id="sort-select" class="hph-sort-select hph-p-sm hph-border hph-border-gray-200 hph-rounded hph-bg-white cursor-pointer" style="border-width: 2px;">
                        <option value="date_desc">Newest First</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="bedrooms_desc">Most Bedrooms</option>
                        <option value="sqft_desc">Largest Size</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="hph-archive-content hph-relative hph-min-h-screen-200">
        
        <!-- Grid View -->
        <div id="grid-view" class="hph-view-content hph-view-grid">
            <div class="hph-container hph-px-lg hph-py-xl">
                <div id="listings-grid" class="hph-listings-grid hph-grid hph-grid-auto-fit hph-grid-min-350 hph-gap-lg">
                    <?php
                    // NUCLEAR OPTION: Bypass WordPress main query completely
                    $direct_query = new WP_Query([
                        'post_type' => 'listing',
                        'post_status' => 'publish',
                        'posts_per_page' => 20, // Force high number
                        'meta_query' => [
                            'relation' => 'OR',
                            [
                                'key' => 'listing_status',
                                'value' => 'active',
                                'compare' => '='
                            ],
                            [
                                'key' => 'listing_status',
                                'value' => '',
                                'compare' => '='
                            ],
                            [
                                'key' => 'listing_status',
                                'compare' => 'NOT EXISTS'
                            ]
                        ]
                    ]);
                    
                    
                    // Use direct query instead of main query
                    if ($direct_query->have_posts()) {
                        while ($direct_query->have_posts()) {
                            $direct_query->the_post();
                            $listing_id = get_the_ID();
                            $price = get_field('price', $listing_id);
                            $bedrooms = get_field('bedrooms', $listing_id);
                            $bathrooms_full = get_field('bathrooms_full', $listing_id);
                            $square_feet = get_field('square_feet', $listing_id);
                            $street_number = get_field('street_number', $listing_id);
                            $street_name = get_field('street_name', $listing_id);
                            $city = get_field('city', $listing_id);
                            $state = get_field('state', $listing_id);
                            
                            echo '<div class="hph-listing-card" data-listing-id="' . $listing_id . '" style="background: var(--hph-white); border-radius: var(--hph-radius-lg); overflow: hidden; box-shadow: var(--hph-shadow-md); transition: all 0.3s ease; cursor: pointer;">';
                            
                            // Featured image
                            if (has_post_thumbnail()) {
                                echo '<div class="hph-card-image" style="position: relative; padding-bottom: 66.67%; overflow: hidden;">';
                                echo '<img src="' . get_the_post_thumbnail_url($listing_id, 'medium_large') . '" alt="' . get_the_title() . '" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">';
                                echo '</div>';
                            } else {
                                echo '<div class="hph-card-image" style="position: relative; padding-bottom: 66.67%; overflow: hidden; background: var(--hph-gray-200); display: flex; align-items: center; justify-content: center;">';
                                echo '<i class="fas fa-home" style="font-size: 3rem; color: var(--hph-gray-400);"></i>';
                                echo '</div>';
                            }
                            
                            echo '<div class="hph-card-content" style="padding: var(--hph-padding-lg);">';
                            
                            // Price
                            if ($price) {
                                echo '<div class="hph-price" style="font-size: var(--hph-text-2xl); font-weight: var(--hph-font-bold); color: var(--hph-primary); margin-bottom: var(--hph-margin-sm);">';
                                echo '$' . number_format($price);
                                echo '</div>';
                            }
                            
                            // Title  
                            echo '<h3 class="hph-title" style="font-size: var(--hph-text-lg); margin-bottom: var(--hph-margin-sm);">';
                            echo '<a href="' . get_permalink() . '" style="text-decoration: none; color: inherit;">' . get_the_title() . '</a>';
                            echo '</h3>';
                            
                            // Address
                            if ($street_number && $street_name && $city && $state) {
                                echo '<p class="hph-address" style="color: var(--hph-gray-600); margin-bottom: var(--hph-margin-md);">';
                                echo '<i class="fas fa-map-marker-alt"></i> ' . $street_number . ' ' . $street_name . ', ' . $city . ', ' . $state;
                                echo '</p>';
                            }
                            
                            // Features
                            echo '<div class="hph-features" style="display: flex; gap: var(--hph-gap-lg); color: var(--hph-gray-700);">';
                            if ($bedrooms) {
                                echo '<span><i class="fas fa-bed"></i> ' . $bedrooms . ' bed' . ($bedrooms > 1 ? 's' : '') . '</span>';
                            }
                            if ($bathrooms_full) {
                                $total_baths = $bathrooms_full + (get_field('bathrooms_half', $listing_id) * 0.5);
                                echo '<span><i class="fas fa-bath"></i> ' . $total_baths . ' bath' . ($total_baths > 1 ? 's' : '') . '</span>';
                            }
                            if ($square_feet) {
                                echo '<span><i class="fas fa-ruler-combined"></i> ' . number_format($square_feet) . ' sq ft</span>';
                            }
                            echo '</div>';
                            
                            echo '</div>'; // card-content
                            echo '</div>'; // listing-card
                        }
                        wp_reset_postdata();
                    } else {
                        echo '<div class="hph-no-results" style="text-align: center; padding: 3rem;">';
                        echo '<i class="fas fa-home" style="font-size: 4rem; color: var(--hph-gray-300); margin-bottom: 1rem;"></i>';
                        echo '<h3 style="font-size: 1.5rem; margin-bottom: 1rem;">No properties found</h3>';
                        echo '<p style="color: var(--hph-gray-600);">Try adjusting your search criteria</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
                
                <!-- Load More Button -->
                <div class="hph-load-more-wrapper hph-text-center hph-mt-2xl hph-none">
                    <button id="load-more-btn" class="hph-btn-load-more">
                        Load More Properties
                    </button>
                </div>
            </div>
        </div>
        
        <!-- List View -->
        <div id="list-view" class="hph-view-content hph-view-list hph-none">
            <div class="hph-container hph-px-lg hph-py-xl">
                <div id="listings-list" class="hph-listings-list d-flex flex-column hph-gap-md">
                    <?php
                    // Reset the query for list view
                    $direct_query->rewind_posts();
                    if ($direct_query->have_posts()) {
                        while ($direct_query->have_posts()) {
                            $direct_query->the_post();
                            $listing_id = get_the_ID();
                            $price = get_field('price', $listing_id);
                            $bedrooms = get_field('bedrooms', $listing_id);
                            $bathrooms_full = get_field('bathrooms_full', $listing_id);
                            $bathrooms_half = get_field('bathrooms_half', $listing_id);
                            $square_feet = get_field('square_feet', $listing_id);
                            $street_number = get_field('street_number', $listing_id);
                            $street_name = get_field('street_name', $listing_id);
                            $city = get_field('city', $listing_id);
                            $state = get_field('state', $listing_id);
                            
                            echo '<div class="hph-listing-card-list" data-listing-id="' . $listing_id . '" style="background: var(--hph-white); border-radius: var(--hph-radius-lg); overflow: hidden; box-shadow: var(--hph-shadow-md); transition: all 0.3s ease; cursor: pointer; display: flex; min-height: 200px;">';
                            
                            // Image section (left)
                            echo '<div class="hph-card-image-list" style="flex: 0 0 300px; position: relative;">';
                            if (has_post_thumbnail()) {
                                echo '<img src="' . get_the_post_thumbnail_url($listing_id, 'medium_large') . '" alt="' . get_the_title() . '" style="width: 100%; height: 100%; object-fit: cover;">';
                            } else {
                                echo '<div style="width: 100%; height: 100%; background: var(--hph-gray-200); display: flex; align-items: center; justify-content: center;">';
                                echo '<i class="fas fa-home" style="font-size: 3rem; color: var(--hph-gray-400);"></i>';
                                echo '</div>';
                            }
                            echo '</div>';
                            
                            // Content section (right)
                            echo '<div class="hph-card-content-list" style="flex: 1; padding: var(--hph-padding-lg); display: flex; flex-direction: column; justify-content: space-between;">';
                            
                            // Top section
                            echo '<div>';
                            // Price
                            if ($price) {
                                echo '<div class="hph-price" style="font-size: var(--hph-text-2xl); font-weight: var(--hph-font-bold); color: var(--hph-primary); margin-bottom: var(--hph-margin-sm);">';
                                echo '$' . number_format($price);
                                echo '</div>';
                            }
                            
                            // Title  
                            echo '<h3 class="hph-title" style="font-size: var(--hph-text-lg); margin-bottom: var(--hph-margin-sm);">';
                            echo '<a href="' . get_permalink() . '" style="text-decoration: none; color: inherit;">' . get_the_title() . '</a>';
                            echo '</h3>';
                            
                            // Address
                            if ($street_number && $street_name && $city && $state) {
                                echo '<p class="hph-address" style="color: var(--hph-gray-600); margin-bottom: var(--hph-margin-md);">';
                                echo '<i class="fas fa-map-marker-alt"></i> ' . $street_number . ' ' . $street_name . ', ' . $city . ', ' . $state;
                                echo '</p>';
                            }
                            
                            // Description excerpt
                            $description = get_field('property_description', $listing_id);
                            if (!$description) {
                                $description = get_the_excerpt();
                            }
                            if ($description) {
                                echo '<p class="hph-description" style="color: var(--hph-gray-700); margin-bottom: var(--hph-margin-md); line-height: 1.5;">';
                                echo wp_trim_words($description, 20, '...');
                                echo '</p>';
                            }
                            echo '</div>';
                            
                            // Bottom section - Features
                            echo '<div class="hph-features" style="display: flex; gap: var(--hph-gap-lg); color: var(--hph-gray-700); padding-top: var(--hph-padding-md); border-top: 1px solid var(--hph-gray-200);">';
                            if ($bedrooms) {
                                echo '<span><i class="fas fa-bed"></i> ' . $bedrooms . ' bed' . ($bedrooms > 1 ? 's' : '') . '</span>';
                            }
                            if ($bathrooms_full || $bathrooms_half) {
                                $total_baths = floatval($bathrooms_full) + (floatval($bathrooms_half) * 0.5);
                                echo '<span><i class="fas fa-bath"></i> ' . $total_baths . ' bath' . ($total_baths > 1 ? 's' : '') . '</span>';
                            }
                            if ($square_feet) {
                                echo '<span><i class="fas fa-ruler-combined"></i> ' . number_format($square_feet) . ' sq ft</span>';
                            }
                            echo '</div>';
                            
                            echo '</div>'; // card-content-list
                            echo '</div>'; // listing-card-list
                        }
                        wp_reset_postdata();
                    } else {
                        echo '<div class="hph-no-results" style="text-align: center; padding: 3rem;">';
                        echo '<i class="fas fa-home" style="font-size: 4rem; color: var(--hph-gray-300); margin-bottom: 1rem;"></i>';
                        echo '<h3 style="font-size: 1.5rem; margin-bottom: 1rem;">No properties found</h3>';
                        echo '<p style="color: var(--hph-gray-600);">Try adjusting your search criteria</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
                
                <!-- Load More Button -->
                <div class="hph-load-more-wrapper hph-text-center hph-mt-2xl hph-none">
                    <button id="load-more-list-btn" class="hph-btn-load-more">
                        Load More Properties
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Map View -->
        <div id="map-view" class="hph-view-content hph-view-map hph-none hph-absolute hph-top-0 hph-left-0 hph-right-0 hph-bottom-0">
            <!-- Map Container -->
            <div id="mapbox-container" class="hph-w-full hph-h-full hph-relative">
                <!-- The map will be initialized here -->
            </div>
            
            <!-- Sidebar Toggle Button -->
            <button id="map-sidebar-toggle" class="hph-map-sidebar-toggle hph-absolute hph-z-10 hph-p-sm hph-bg-white hph-border-none hph-rounded hph-shadow-lg cursor-pointer" style="left: 20px; top: 20px;">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Map Sidebar -->
            <div id="map-sidebar" class="hph-map-sidebar hph-absolute hph-left-0 hph-top-0 hph-bottom-0 hph-w-400 hph-bg-white hph-shadow-xl hph-z-5 hph-transform-translate-x-0 hph-overflow-y-auto" style="transition: transform 0.3s ease;">
                
                <!-- Sidebar Header -->
                <div class="hph-sidebar-header hph-p-lg hph-border-b hph-sticky hph-top-0 hph-bg-white hph-z-1">
                    <h3 class="hph-m-0 hph-text-lg hph-font-semibold">
                        Properties (<span id="map-results-count"><?php echo $direct_query->found_posts; ?></span>)
                    </h3>
                    <button id="sidebar-close" class="hph-sidebar-close hph-absolute hph-right-md cursor-pointer hph-text-xl" style="top: 50%; transform: translateY(-50%); background: transparent; border: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Sidebar Listings -->
                <div id="map-listings" class="hph-map-listings hph-p-md">
                    <?php
                    // Reset the query for map view sidebar
                    $direct_query->rewind_posts();
                    if ($direct_query->have_posts()) {
                        while ($direct_query->have_posts()) {
                            $direct_query->the_post();
                            $listing_id = get_the_ID();
                            $price = get_field('price', $listing_id);
                            $bedrooms = get_field('bedrooms', $listing_id);
                            $bathrooms_full = get_field('bathrooms_full', $listing_id);
                            $bathrooms_half = get_field('bathrooms_half', $listing_id);
                            $square_feet = get_field('square_feet', $listing_id);
                            $street_number = get_field('street_number', $listing_id);
                            $street_name = get_field('street_name', $listing_id);
                            $city = get_field('city', $listing_id);
                            $state = get_field('state', $listing_id);
                            
                            // Get coordinates for map markers
                            $latitude = get_field('latitude', $listing_id);
                            $longitude = get_field('longitude', $listing_id);
                            
                            echo '<div class="hph-map-listing-card" data-listing-id="' . $listing_id . '" data-lat="' . $latitude . '" data-lng="' . $longitude . '" style="background: var(--hph-white); border-radius: var(--hph-radius-md); overflow: hidden; box-shadow: var(--hph-shadow-sm); margin-bottom: var(--hph-margin-md); transition: all 0.3s ease; cursor: pointer; border: 2px solid transparent;">';
                            
                            // Featured image
                            echo '<div class="hph-map-card-image" style="position: relative; height: 150px; overflow: hidden;">';
                            if (has_post_thumbnail()) {
                                echo '<img src="' . get_the_post_thumbnail_url($listing_id, 'medium') . '" alt="' . get_the_title() . '" style="width: 100%; height: 100%; object-fit: cover;">';
                            } else {
                                echo '<div style="width: 100%; height: 100%; background: var(--hph-gray-200); display: flex; align-items: center; justify-content: center;">';
                                echo '<i class="fas fa-home" style="font-size: 2rem; color: var(--hph-gray-400);"></i>';
                                echo '</div>';
                            }
                            echo '</div>';
                            
                            echo '<div class="hph-map-card-content" style="padding: var(--hph-padding-md);">';
                            
                            // Price
                            if ($price) {
                                echo '<div class="hph-price" style="font-size: var(--hph-text-lg); font-weight: var(--hph-font-bold); color: var(--hph-primary); margin-bottom: var(--hph-margin-xs);">';
                                echo '$' . number_format($price);
                                echo '</div>';
                            }
                            
                            // Title  
                            echo '<h4 class="hph-title" style="font-size: var(--hph-text-md); margin-bottom: var(--hph-margin-xs); font-weight: var(--hph-font-semibold);">';
                            echo '<a href="' . get_permalink() . '" style="text-decoration: none; color: inherit;">' . get_the_title() . '</a>';
                            echo '</h4>';
                            
                            // Address
                            if ($street_number && $street_name && $city && $state) {
                                echo '<p class="hph-address" style="color: var(--hph-gray-600); margin-bottom: var(--hph-margin-sm); font-size: var(--hph-text-sm);">';
                                echo '<i class="fas fa-map-marker-alt"></i> ' . $street_number . ' ' . $street_name . ', ' . $city . ', ' . $state;
                                echo '</p>';
                            }
                            
                            // Features
                            echo '<div class="hph-features" style="display: flex; gap: var(--hph-gap-sm); color: var(--hph-gray-700); font-size: var(--hph-text-xs);">';
                            if ($bedrooms) {
                                echo '<span><i class="fas fa-bed"></i> ' . $bedrooms . ' bed' . ($bedrooms > 1 ? 's' : '') . '</span>';
                            }
                            if ($bathrooms_full || $bathrooms_half) {
                                $total_baths = floatval($bathrooms_full) + (floatval($bathrooms_half) * 0.5);
                                echo '<span><i class="fas fa-bath"></i> ' . $total_baths . ' bath' . ($total_baths > 1 ? 's' : '') . '</span>';
                            }
                            if ($square_feet) {
                                echo '<span><i class="fas fa-ruler-combined"></i> ' . number_format($square_feet) . ' sf</span>';
                            }
                            echo '</div>';
                            
                            echo '</div>'; // map-card-content
                            echo '</div>'; // map-listing-card
                        }
                        wp_reset_postdata();
                    } else {
                        echo '<div class="hph-no-results" style="text-align: center; padding: 2rem;">';
                        echo '<i class="fas fa-home" style="font-size: 3rem; color: var(--hph-gray-300); margin-bottom: 1rem;"></i>';
                        echo '<p style="color: var(--hph-gray-600);">No properties found</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Loading Overlay -->
        <div id="listings-loading" class="hph-loading-overlay hph-none hph-absolute hph-top-0 hph-left-0 hph-right-0 hph-bottom-0 hph-z-1000 d-flex justify-content-center align-items-center" style="background: rgba(255, 255, 255, 0.9);">
            <div class="hph-spinner hph-text-center">
                <i class="fas fa-spinner fa-spin hph-text-primary" style="font-size: 3rem;"></i>
                <p class="hph-mt-md hph-text-gray-600">Loading properties...</p>
            </div>
        </div>
        
        <!-- No Results -->
        <div id="no-results" class="hph-no-results hph-none hph-p-2xl hph-text-center">
            <i class="fas fa-home hph-text-gray-300 hph-mb-lg" style="font-size: 4rem;"></i>
            <h3 class="hph-text-2xl hph-mb-md">No properties found</h3>
            <p class="hph-text-gray-600 hph-mb-lg">Try adjusting your filters or search criteria</p>
            <button id="clear-filters" class="hph-btn-clear hph-p-md hph-px-xl hph-bg-primary hph-text-white hph-border-none hph-rounded cursor-pointer">
                Clear All Filters
            </button>
        </div>
    </div>
</div>

<?php
// Output listing data for JavaScript map functionality
$listing_data = [];
$direct_query->rewind_posts();
if ($direct_query->have_posts()) {
    while ($direct_query->have_posts()) {
        $direct_query->the_post();
        $listing_id = get_the_ID();
        $latitude = get_field('latitude', $listing_id);
        $longitude = get_field('longitude', $listing_id);
        $price = get_field('price', $listing_id);
        $bedrooms = get_field('bedrooms', $listing_id);
        $bathrooms_full = get_field('bathrooms_full', $listing_id);
        $bathrooms_half = get_field('bathrooms_half', $listing_id);
        $square_feet = get_field('square_feet', $listing_id);
        $street_number = get_field('street_number', $listing_id);
        $street_name = get_field('street_name', $listing_id);
        $city = get_field('city', $listing_id);
        $state = get_field('state', $listing_id);
        
        // Only include listings with valid coordinates
        if ($latitude && $longitude) {
            $total_baths = floatval($bathrooms_full) + (floatval($bathrooms_half) * 0.5);
            $full_address = '';
            if ($street_number && $street_name && $city && $state) {
                $full_address = $street_number . ' ' . $street_name . ', ' . $city . ', ' . $state;
            }
            
            $listing_data[] = [
                'id' => $listing_id,
                'title' => get_the_title(),
                'price' => $price ? '$' . number_format($price) : '',
                'bedrooms' => $bedrooms,
                'bathrooms' => $total_baths,
                'sqft' => $square_feet ? number_format($square_feet) : '',
                'address' => $full_address,
                'latitude' => floatval($latitude),
                'longitude' => floatval($longitude),
                'permalink' => get_permalink(),
                'image' => has_post_thumbnail() ? get_the_post_thumbnail_url($listing_id, 'medium_large') : '',
                'status' => get_field('listing_status', $listing_id) ?: 'active'
            ];
        }
    }
    wp_reset_postdata();
}
?>

<!-- Listing Data for JavaScript -->
<script id="listings-data" type="application/json">
<?php echo wp_json_encode($listing_data); ?>
</script>

<!-- Listing Card Template (for JavaScript) -->
<script id="listing-card-template" type="text/template">
    <div class="hph-listing-card" data-listing-id="{{id}}" data-lat="{{lat}}" data-lng="{{lng}}"
         style="background: var(--hph-white); border-radius: var(--hph-radius-lg); overflow: hidden; box-shadow: var(--hph-shadow-md); transition: all 0.3s ease; cursor: pointer;">
        <div class="hph-card-image" style="position: relative; padding-bottom: 66.67%; overflow: hidden;">
            <img src="{{image}}" alt="{{title}}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
            {{#if status}}
            <span class="hph-status-badge" style="position: absolute; top: var(--hph-padding-md); left: var(--hph-padding-md); padding: var(--hph-padding-xs) var(--hph-padding-sm); background: var(--hph-primary); color: var(--hph-white); border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold);">
                {{status}}
            </span>
            {{/if}}
            {{#if featured}}
            <span class="hph-featured-badge" style="position: absolute; top: var(--hph-padding-md); right: var(--hph-padding-md); padding: var(--hph-padding-xs) var(--hph-padding-sm); background: var(--hph-accent); color: var(--hph-white); border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm);">
                <i class="fas fa-star"></i> Featured
            </span>
            {{/if}}
        </div>
        <div class="hph-card-content" style="padding: var(--hph-padding-lg);">
            <div class="hph-price" style="font-size: var(--hph-text-2xl); font-weight: var(--hph-font-bold); color: var(--hph-primary); margin-bottom: var(--hph-margin-sm);">
                {{price}}
            </div>
            <h3 class="hph-title" style="font-size: var(--hph-text-lg); margin-bottom: var(--hph-margin-sm);">
                {{title}}
            </h3>
            <p class="hph-address" style="color: var(--hph-gray-600); margin-bottom: var(--hph-margin-md);">
                <i class="fas fa-map-marker-alt"></i> {{address}}
            </p>
            <div class="hph-features" style="display: flex; gap: var(--hph-gap-lg); color: var(--hph-gray-700);">
                {{#if bedrooms}}
                <span><i class="fas fa-bed"></i> {{bedrooms}} Beds</span>
                {{/if}}
                {{#if bathrooms}}
                <span><i class="fas fa-bath"></i> {{bathrooms}} Baths</span>
                {{/if}}
                {{#if sqft}}
                <span><i class="fas fa-ruler-combined"></i> {{sqft}} Sq Ft</span>
                {{/if}}
            </div>
        </div>
    </div>
</script>

<!-- List Item Template -->
<script id="listing-list-template" type="text/template">
    <div class="hph-listing-list-item" data-listing-id="{{id}}"
         style="display: flex; gap: var(--hph-gap-lg); background: var(--hph-white); border-radius: var(--hph-radius-lg); overflow: hidden; box-shadow: var(--hph-shadow-md); transition: all 0.3s ease;">
        <div class="hph-list-image" style="width: 300px; flex-shrink: 0;">
            <img src="{{image}}" alt="{{title}}" style="width: 100%; height: 200px; object-fit: cover;">
        </div>
        <div class="hph-list-content" style="flex: 1; padding: var(--hph-padding-lg); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: var(--hph-margin-md);">
                    <div>
                        <h3 style="font-size: var(--hph-text-xl); margin-bottom: var(--hph-margin-sm);">{{title}}</h3>
                        <p style="color: var(--hph-gray-600);"><i class="fas fa-map-marker-alt"></i> {{address}}</p>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: var(--hph-text-2xl); font-weight: var(--hph-font-bold); color: var(--hph-primary);">{{price}}</div>
                        {{#if status}}
                        <span style="display: inline-block; margin-top: var(--hph-margin-sm); padding: var(--hph-padding-xs) var(--hph-padding-sm); background: var(--hph-primary-100); color: var(--hph-primary); border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm);">
                            {{status}}
                        </span>
                        {{/if}}
                    </div>
                </div>
                <p style="color: var(--hph-gray-700); margin-bottom: var(--hph-margin-md);">{{description}}</p>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="hph-features" style="display: flex; gap: var(--hph-gap-xl); color: var(--hph-gray-700);">
                    {{#if bedrooms}}<span><i class="fas fa-bed"></i> {{bedrooms}} Beds</span>{{/if}}
                    {{#if bathrooms}}<span><i class="fas fa-bath"></i> {{bathrooms}} Baths</span>{{/if}}
                    {{#if sqft}}<span><i class="fas fa-ruler-combined"></i> {{sqft}} Sq Ft</span>{{/if}}
                    {{#if garage}}<span><i class="fas fa-car"></i> {{garage}} Garage</span>{{/if}}
                </div>
                <a href="{{url}}" class="hph-btn-view" style="padding: var(--hph-padding-sm) var(--hph-padding-lg); background: var(--hph-primary); color: var(--hph-white); text-decoration: none; border-radius: var(--hph-radius-md);">
                    View Details
                </a>
            </div>
        </div>
    </div>
</script>

<style>
/* View-specific styles */
.hph-view-btn.active {
    background: var(--hph-white) !important;
    box-shadow: var(--hph-shadow-sm);
}

.hph-listing-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--hph-shadow-xl) !important;
}

.hph-listing-list-item:hover {
    box-shadow: var(--hph-shadow-xl) !important;
}

.hph-map-sidebar.collapsed {
    transform: translateX(-100%);
}

/* Mapbox marker styles */
.mapbox-marker {
    width: 40px;
    height: 40px;
    background: var(--hph-primary);
    border: 3px solid var(--hph-white);
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    box-shadow: var(--hph-shadow-lg);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.mapbox-marker:hover {
    transform: rotate(-45deg) scale(1.1);
}

.mapbox-marker-price {
    transform: rotate(45deg);
    color: var(--hph-white);
    font-weight: bold;
    font-size: 10px;
}

/* Archive specific responsive utilities */
@media (max-width: 768px) {
    .hph-listings-grid {
        grid-template-columns: 1fr !important;
    }
    
    .hph-map-sidebar {
        width: 100% !important;
    }
    
    .hph-listing-list-item {
        flex-direction: column;
    }
    
    .hph-list-image {
        width: 100% !important;
    }
    
    .hph-archive-controls .d-flex {
        flex-direction: column;
        gap: var(--hph-gap-sm, 0.75rem);
        align-items: stretch;
    }
    
    .hph-view-switcher {
        justify-self: center;
    }
}
</style>

<!-- Enhanced Archive JavaScript for AJAX -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    console.log('✅ Archive filtering script initialized');
    console.log('AJAX URL:', '<?php echo admin_url('admin-ajax.php'); ?>');
    
    // INITIALIZATION CHECK: Verify all required elements exist
    console.log('🔍 INITIALIZATION CHECK:');
    var requiredElements = [
        '#listings-grid',
        '#listings-list', 
        '#listings-loading',
        '#results-count',
        '#no-results',
        '.hph-quick-filter-btn'
    ];
    
    var missingElements = [];
    requiredElements.forEach(function(selector) {
        var $el = $(selector);
        var exists = $el.length > 0;
        console.log('  ' + selector + ':', exists ? '✅ Found (' + $el.length + ')' : '❌ Missing');
        if (!exists) missingElements.push(selector);
    });
    
    if (missingElements.length > 0) {
        console.error('❌ CRITICAL: Missing required elements:', missingElements);
        alert('DEBUG: Missing required elements for AJAX functionality: ' + missingElements.join(', '));
    } else {
        console.log('✅ All required elements found');
    }
    
    // Test click handler binding
    console.log('🔧 Testing event handler binding...');
    var testBtn = $('.hph-quick-filter-btn').first();
    if (testBtn.length > 0) {
        console.log('✅ Found first filter button:', testBtn.text());
        console.log('✅ Button data:', testBtn.data());
    } else {
        console.error('❌ No filter buttons found for event binding');
    }
    
    // Simple test - click any filter button
    $(document).on('click', '.hph-quick-filter-btn', function(e) {
        e.preventDefault();
        console.log('🔵 Filter button clicked:', $(this).text());
        
        var $btn = $(this);
        var filterType = $btn.data('filter');
        var filterValue = $btn.data('value');
        
        console.log('🔵 Filter type:', filterType, 'Value:', filterValue);
        console.log('🔵 Button element:', $btn[0]);
        console.log('🔵 All data attributes:', $btn.data());
        
        // Simple visual feedback
        $('.hph-quick-filter-btn').css('background', 'var(--hph-gray-200)').css('color', 'var(--hph-gray-700)');
        $btn.css('background', 'var(--hph-primary)').css('color', 'var(--hph-white)');
        
        // Enhanced AJAX call with proper data structure
        var filterData = {
            action: 'hph_load_listings',
            nonce: '<?php echo wp_create_nonce('hph_listings_nonce'); ?>',
            post_type: 'listing'
        };
        
        // Add filter as direct parameter (not nested)
        if (filterType === 'status') {
            filterData.status = filterValue;
        } else if (filterType === 'feature') {
            filterData.feature = filterValue;
        }
        
        // Also add as nested filter for compatibility
        filterData.filters = {};
        filterData.filters[filterType] = filterValue;
        
        console.log('🚀 Sending AJAX data:', filterData);
        
        // Check target elements before AJAX
        console.log('🔍 Target elements check:');
        console.log('  #listings-grid exists:', $('#listings-grid').length > 0);
        console.log('  #listings-list exists:', $('#listings-list').length > 0);
        console.log('  #listings-loading exists:', $('#listings-loading').length > 0);
        console.log('  #results-count exists:', $('#results-count').length > 0);
        console.log('  #no-results exists:', $('#no-results').length > 0);
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: filterData,
            beforeSend: function() {
                console.log('⏳ AJAX request starting...');
                $('#listings-loading').removeClass('hph-none').addClass('d-flex');
            },
            success: function(response) {
                console.log('✅ AJAX Success - Full response:', response);
                console.log('✅ Response success:', response.success);
                console.log('✅ Response data:', response.data);
                
                if (response.success && response.data) {
                    // Update the grid and list views
                    if (response.data.html) {
                        console.log('📄 HTML received, length:', response.data.html.length);
                        console.log('📄 HTML preview:', response.data.html.substring(0, 200) + '...');
                        
                        // Update containers and log results
                        var $grid = $('#listings-grid');
                        var $list = $('#listings-list');
                        
                        console.log('🎯 Updating #listings-grid, current content length:', $grid.html().length);
                        $grid.html(response.data.html);
                        console.log('🎯 Updated #listings-grid, new content length:', $grid.html().length);
                        
                        console.log('🎯 Updating #listings-list, current content length:', $list.html().length);
                        $list.html(response.data.html);
                        console.log('🎯 Updated #listings-list, new content length:', $list.html().length);
                    } else {
                        console.warn('⚠️ No HTML data received');
                    }
                    
                    // Update counts
                    if (response.data.total !== undefined) {
                        console.log('📊 Updating counts to:', response.data.total);
                        $('#results-count').text(response.data.total);
                        $('#map-results-count').text(response.data.total);
                    } else {
                        console.warn('⚠️ No total count received');
                    }
                    
                    // Show/hide no results
                    var hasResults = response.data.total > 0;
                    console.log('🔄 Has results:', hasResults);
                    $('#no-results').toggleClass('hph-none', hasResults);
                } else {
                    console.error('❌ AJAX Success but invalid response structure');
                    if (response.data) {
                        console.error('❌ Error message:', response.data);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX Error - Status:', status);
                console.error('❌ AJAX Error - Error:', error);
                console.error('❌ AJAX Error - Response Text:', xhr.responseText);
                console.error('❌ AJAX Error - Status Code:', xhr.status);
            },
            complete: function() {
                console.log('🏁 AJAX request completed');
                $('#listings-loading').removeClass('d-flex').addClass('hph-none');
            }
        });
    });
    
    // Enhanced form submission with autocomplete fix
    $(document).on('submit', '.hph-hero-search-form', function(e) {
        e.preventDefault();
        console.log('Form submitted - preventing default');
        
        var $form = $(this);
        var searchValue = $form.find('input[name="s"]').val();
        var propertyType = $form.find('select[name="property_type"]').val();
        
        console.log('Search:', searchValue, 'Type:', propertyType);
        
        // Clear previous active filters when new search is performed
        $('.hph-quick-filter-btn').css('background', 'var(--hph-gray-200)').css('color', 'var(--hph-gray-700)');
        $('.hph-quick-filter-btn[data-filter="status"][data-value="all"]').css('background', 'var(--hph-primary)').css('color', 'var(--hph-white)');
        
        // Enhanced AJAX with proper field names
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'hph_load_listings',
                nonce: '<?php echo wp_create_nonce('hph_listings_nonce'); ?>',
                post_type: 'listing',
                s: searchValue,
                property_type: propertyType,
                filters: {
                    property_type: propertyType
                }
            },
            beforeSend: function() {
                $('#listings-loading').removeClass('hph-none').addClass('d-flex');
            },
            success: function(response) {
                console.log('✅ Form AJAX Success:', response);
                if (response.success && response.data) {
                    // Update the listings container
                    if (response.data.html) {
                        $('#listings-grid').html(response.data.html);
                        $('#listings-list').html(response.data.html);
                        
                        // Reinitialize any necessary scripts
                        if (typeof HPHArchiveListings !== 'undefined') {
                            HPHArchiveListings.reinit();
                        }
                    }
                    
                    // Update results count
                    if (response.data.total !== undefined) {
                        $('#results-count').text(response.data.total);
                        $('#map-results-count').text(response.data.total);
                        $('.hph-results-number').text(response.data.total);
                    }
                    
                    // Show/hide no results message
                    $('#no-results').toggleClass('hph-none', response.data.total > 0);
                    
                    // Update pagination if provided
                    if (response.data.pagination) {
                        $('.hph-pagination').html(response.data.pagination);
                    }
                    
                    // Update map markers if map is visible
                    if (response.data.markers && typeof updateMapMarkers === 'function') {
                        updateMapMarkers(response.data.markers);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Form AJAX Error:', error);
                console.log('Response Text:', xhr.responseText);
            },
            complete: function() {
                $('#listings-loading').removeClass('d-flex').addClass('hph-none');
            }
        });
    });
    
    // DEBUG: Manual test button
    $(document).on('click', '#debug-test-ajax', function() {
        console.log('🧪 DEBUG: Manual test button clicked');
        $('#debug-status').text('Testing...');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'hph_load_listings',
                nonce: '<?php echo wp_create_nonce('hph_listings_nonce'); ?>',
                post_type: 'listing',
                feature: 'waterfront',
                test_mode: true,
                debug_mode: true, // Enable card debugging
                filters: {
                    feature: 'waterfront'
                }
            },
            success: function(response) {
                console.log('🧪 DEBUG: Manual test success:', response);
                $('#debug-status').html('✅ Success<br>Found: ' + (response.data?.total || 0));
                
                if (response.success && response.data?.html) {
                    console.log('🧪 DEBUG: Updating DOM with HTML:', response.data.html.substring(0, 300) + '...');
                    $('#listings-grid').html(response.data.html);
                    $('#results-count').text(response.data.total);
                    
                    // Check if cards are actually in the DOM
                    var cardCount = $('.hph-card').length;
                    var debugCount = $('[style*="CARD DEBUG"]').length;
                    console.log('🧪 DEBUG: Cards found in DOM:', cardCount, 'Debug elements:', debugCount);
                } else {
                    console.warn('🧪 DEBUG: No HTML in response');
                }
            },
            error: function(xhr, status, error) {
                console.error('🧪 DEBUG: Manual test error:', error);
                $('#debug-status').html('❌ Error<br>' + error);
            }
        });
    });

    // Clear filters functionality
    $(document).on('click', '#clear-quick-filters, #clear-filters', function(e) {
        e.preventDefault();
        console.log('Clear filters clicked');
        
        // Reset visual state of all filter buttons
        $('.hph-quick-filter-btn').css('background', 'var(--hph-gray-200)').css('color', 'var(--hph-gray-700)');
        $('.hph-quick-filter-btn[data-filter="status"][data-value="all"]').css('background', 'var(--hph-primary)').css('color', 'var(--hph-white)');
        
        // Clear form inputs
        $('.hph-hero-search-form input[name="s"]').val('');
        $('.hph-hero-search-form select[name="property_type"]').val('');
        
        // Load all listings (no filters)
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'hph_load_listings',
                nonce: '<?php echo wp_create_nonce('hph_listings_nonce'); ?>',
                post_type: 'listing',
                status: 'all',
                filters: {}
            },
            beforeSend: function() {
                $('#listings-loading').removeClass('hph-none').addClass('d-flex');
            },
            success: function(response) {
                console.log('✅ Clear Filters Success:', response);
                if (response.success && response.data) {
                    // Update the listings container
                    if (response.data.html) {
                        $('#listings-grid').html(response.data.html);
                        $('#listings-list').html(response.data.html);
                        
                        // Reinitialize any necessary scripts
                        if (typeof HPHArchiveListings !== 'undefined') {
                            HPHArchiveListings.reinit();
                        }
                    }
                    
                    // Update results count
                    if (response.data.total !== undefined) {
                        $('#results-count').text(response.data.total);
                        $('#map-results-count').text(response.data.total);
                        $('.hph-results-number').text(response.data.total);
                    }
                    
                    // Show/hide no results message
                    $('#no-results').toggleClass('hph-none', response.data.total > 0);
                    
                    // Update pagination if provided
                    if (response.data.pagination) {
                        $('.hph-pagination').html(response.data.pagination);
                    }
                    
                    // Update map markers if map is visible
                    if (response.data.markers && typeof updateMapMarkers === 'function') {
                        updateMapMarkers(response.data.markers);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Clear Filters Error:', error);
            },
            complete: function() {
                $('#listings-loading').removeClass('d-flex').addClass('hph-none');
            }
        });
    });

    // Auto-search on dropdown change
    $(document).on('change', '.hph-hero-search-form select', function() {
        console.log('Dropdown changed:', $(this).attr('name'), '=', $(this).val());
        $(this).closest('form').submit();
    });

    // Fix autocomplete functionality
    $(document).on('input', '.hph-hero-search-form input[name="s"]', function() {
        var $input = $(this);
        var query = $input.val().trim();
        
        if (query.length >= 2) {
            console.log('Autocomplete search for:', query);
            // Note: This would need a separate autocomplete AJAX action
            // For now, just trigger search on Enter or form submit
        }
    });
});
</script>

<!-- Temporary fix for missing elements and global variables -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize missing global for archive-ajax.js compatibility
    if (typeof hphArchive === 'undefined') {
        window.hphArchive = {
            ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('hph_archive_nonce'); ?>',
            postType: 'listing',
            currentView: 'grid',
            currentSort: 'date_desc',
            autoFilter: true
        };
    }
    
    // Add missing elements check and warn about missing DOM elements
    const requiredElements = [
        '.hph-archive__search-panel',
        '.hph-archive__search-toggle', 
        '.hph-advanced-toggle',
        '.hph-advanced-filters'
    ];
    
    requiredElements.forEach(selector => {
        if (!document.querySelector(selector)) {
            console.warn(`Missing required element: ${selector}`);
        }
    });
    
    // Initialize HPHAdvancedFilters with safe fallbacks
    if (typeof HPHAdvancedFilters !== 'undefined') {
        try {
            // Only initialize if required DOM elements exist
            const filtersContainer = document.querySelector('.hph-advanced-filters-wrapper');
            if (filtersContainer) {
                new HPHAdvancedFilters('advanced-filters', {
                    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
                    nonce: '<?php echo wp_create_nonce('hph_archive_nonce'); ?>',
                    postType: 'listing'
                });
            }
        } catch (error) {
            console.warn('Could not initialize HPHAdvancedFilters:', error.message);
        }
    }
});
</script>

<script>
// Archive mode: Disable header scroll behavior and auto-expand search
document.addEventListener('DOMContentLoaded', function() {
    // Mark as archive mode
    window.hphArchiveMode = true;
    
    // Disable header scroll handlers
    if (typeof window.hphHeader !== "undefined" && window.hphHeader.destroy) {
        window.hphHeader.destroy();
    }
    
    // Remove scroll event listeners that might interfere
    window.removeEventListener("scroll", window.hphHeaderScroll);
    
    // Auto-expand header search on archive pages
    setTimeout(function() {
        const searchToggle = document.querySelector(".hph-search-toggle");
        const searchBar = document.querySelector(".hph-search-bar[data-search-bar]");
        
        if (searchToggle && searchBar) {
            // Check if search is not already expanded
            if (!searchBar.classList.contains("active") && searchBar.style.display !== "block") {
                searchToggle.click();
                searchBar.classList.add("archive-auto-expanded");
                console.log("Archive Complex: Header search auto-expanded");
            }
        }
    }, 100);
    
    // Handle map view special behavior for complex archive
    function handleMapViewToggle() {
        const mapViewBtn = document.querySelector("[data-view=\"map\"]");
        const body = document.body;
        const header = document.querySelector(".hph-sticky-header");
        
        if (mapViewBtn) {
            mapViewBtn.addEventListener("click", function() {
                // Add body class for map view
                body.classList.add("map-view-active");
                
                // Hide header in map view
                if (header) {
                    header.style.display = "none";
                }
                
                // Prevent scrolling
                body.style.overflow = "hidden";
                
                console.log("Complex Archive: Map view activated - header hidden");
            });
        }
        
        // Handle switching back to other views
        const otherViewBtns = document.querySelectorAll("[data-view]:not([data-view=\"map\"])");
        
        otherViewBtns.forEach(btn => {
            if (btn) {
                btn.addEventListener("click", function() {
                    // Remove body class for map view
                    body.classList.remove("map-view-active");
                    
                    // Show header again
                    if (header) {
                        header.style.display = "";
                    }
                    
                    // Restore scrolling
                    body.style.overflow = "";
                    
                    console.log("Complex Archive: Standard view activated - header restored");
                });
            }
        });
    }
    
    // Initialize map view handlers
    handleMapViewToggle();
});
</script>

<?php
get_footer();
?>
