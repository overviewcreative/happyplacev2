<?php
/**
 * HPH Property Map & Neighborhood Section
 * 
 * Interactive map with nearby amenities, schools, and points of interest
 * Includes walk scores, commute times, and neighborhood insights
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - listing_id: int (required for bridge functions)
 */

// Extract listing ID from args or global context
$listing_id = $args['listing_id'] ?? get_the_ID();

// Generate unique map ID early
$map_id = 'property-map-' . uniqid();

// Return early if no listing ID
if (!$listing_id) {
    return;
}

// Default arguments
$defaults = array(
    'listing_id' => $listing_id,
    'latitude' => 38.7749,
    'longitude' => -75.1395,
    'address' => '',
    'google_maps_api_key' => '',
    'map_style' => 'standard',
    'default_zoom' => 15,
    'show_streetview' => true,
    'walk_score' => 0,
    'transit_score' => 0,
    'bike_score' => 0,
    'nearby_places' => array(),
    'schools' => array(),
    'commute_times' => array(),
    'neighborhood_description' => '',
    'crime_rating' => 'Unknown',
    'noise_level' => 'Unknown',
    'demographics' => array(),
    'property_boundaries' => array(),
    'section_id' => 'property-map'
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Use bridge function to get map data if available
if ($listing_id && function_exists('hpt_get_listing_map_data')) {
    $map_data = hpt_get_listing_map_data($listing_id);
    
    // Merge bridge data with config (bridge data takes precedence for empty values)
    foreach ($map_data as $key => $value) {
        if (empty($config[$key]) && !empty($value)) {
            $config[$key] = $value;
        }
    }
}

// Set Google Maps API key from constant if not provided
if (empty($config['google_maps_api_key']) && defined('HPH_GOOGLE_MAPS_KEY')) {
    $config['google_maps_api_key'] = HPH_GOOGLE_MAPS_KEY;
}

// Get address from listing if not provided
if (empty($config['address']) && function_exists('hpt_get_listing_address')) {
    $config['address'] = hpt_get_listing_address($listing_id, 'full');
}

extract($config);

// Add JavaScript context data (ensure script is available)
$script_handle = 'hph-listing-map';

// Check if the script is registered/enqueued, if not try to enqueue it
if (!wp_script_is($script_handle, 'enqueued') && !wp_script_is($script_handle, 'registered')) {
    // Try to enqueue from the HPH_Assets service
    if (class_exists('HPH_Assets')) {
        // The HPH_Assets service will handle loading the component scripts
    } else {
        // Fallback: register the script manually
        $script_url = get_template_directory_uri() . '/assets/js/components/listing-map.js';
        if (file_exists(get_template_directory() . '/assets/js/components/listing-map.js')) {
            wp_enqueue_script($script_handle, $script_url, array('jquery'), '1.0.0', true);
        }
    }
}

$map_js_data = sprintf(
    'window.hphMapContext = window.hphMapContext || {}; window.hphMapContext.propertyId = %d; window.hphMapContext.coordinates = %s; window.hphMapContext.address = %s; window.hphMapContext.mapId = %s;',
    $listing_id,
    wp_json_encode(array('lat' => $latitude, 'lng' => $longitude)),
    wp_json_encode($address),
    wp_json_encode($map_id)
);

// Add debug info in development mode
if (defined('WP_DEBUG') && WP_DEBUG) {
    $map_js_data .= sprintf(
        ' console.log("HPH Map Debug - Listing ID: %d, Coordinates: %s, Address: %s, Map ID: %s");',
        $listing_id,
        wp_json_encode(array('lat' => $latitude, 'lng' => $longitude)),
        wp_json_encode($address),
        wp_json_encode($map_id)
    );
}

// Add inline script if the handle exists, otherwise add to footer
if (wp_script_is($script_handle, 'enqueued') || wp_script_is($script_handle, 'registered')) {
    wp_add_inline_script($script_handle, $map_js_data, 'before');
} else {
    // Fallback: add to wp_footer
    add_action('wp_footer', function() use ($map_js_data) {
        echo '<script>' . $map_js_data . '</script>';
    });
}

// Default nearby places if none provided
if (empty($nearby_places)) {
    $nearby_places = array(
        array(
            'category' => 'Grocery',
            'places' => array(
                array('name' => 'Whole Foods Market', 'distance' => '0.3 mi', 'time' => '5 min walk', 'rating' => 4.5),
                array('name' => 'Trader Joe\'s', 'distance' => '0.8 mi', 'time' => '3 min drive', 'rating' => 4.6),
            )
        ),
        array(
            'category' => 'Restaurants',
            'places' => array(
                array('name' => 'The Blue Hen', 'distance' => '0.2 mi', 'time' => '3 min walk', 'rating' => 4.7),
                array('name' => 'Salt Air', 'distance' => '0.4 mi', 'time' => '7 min walk', 'rating' => 4.8),
                array('name' => 'Henlopen City Oyster House', 'distance' => '0.5 mi', 'time' => '10 min walk', 'rating' => 4.6),
            )
        ),
        array(
            'category' => 'Shopping',
            'places' => array(
                array('name' => 'Tanger Outlets', 'distance' => '1.2 mi', 'time' => '5 min drive', 'rating' => 4.3),
                array('name' => 'Rehoboth Beach Boardwalk', 'distance' => '0.6 mi', 'time' => '12 min walk', 'rating' => 4.7),
            )
        ),
        array(
            'category' => 'Healthcare',
            'places' => array(
                array('name' => 'Beebe Healthcare', 'distance' => '2.1 mi', 'time' => '8 min drive', 'rating' => 4.2),
                array('name' => 'CVS Pharmacy', 'distance' => '0.4 mi', 'time' => '7 min walk', 'rating' => 3.8),
            )
        )
    );
}

// Default schools if none provided
if (empty($schools)) {
    $schools = array(
        array('name' => 'Rehoboth Elementary', 'type' => 'Elementary', 'rating' => 9, 'distance' => '0.8 mi', 'grades' => 'K-5'),
        array('name' => 'Beacon Middle School', 'type' => 'Middle', 'rating' => 8, 'distance' => '1.2 mi', 'grades' => '6-8'),
        array('name' => 'Cape Henlopen High School', 'type' => 'High', 'rating' => 8, 'distance' => '2.5 mi', 'grades' => '9-12'),
    );
}

// Default commute times if none provided
if (empty($commute_times)) {
    $commute_times = array(
        array('destination' => 'Downtown Rehoboth', 'drive_time' => '5 min', 'transit_time' => '15 min'),
        array('destination' => 'Dewey Beach', 'drive_time' => '8 min', 'transit_time' => '20 min'),
        array('destination' => 'Dover', 'drive_time' => '45 min', 'transit_time' => 'N/A'),
    );
}

// Score colors helper function
function getScoreColor($score) {
    if ($score >= 90) return 'excellent';
    if ($score >= 70) return 'good';
    if ($score >= 50) return 'fair';
    return 'poor';
}

// Crime rating colors mapping
$crime_colors = array(
    'Very Low' => 'hph-crime-very-low',
    'Low' => 'hph-crime-low',
    'Moderate' => 'hph-crime-moderate',
    'High' => 'hph-crime-high'
);

// Component assets are loaded by HPH_Assets service automatically
?>

<section class="hph-property-map" <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>>
    <div class="hph-map-container">
        
        <!-- Section Header -->
        <div class="hph-map-header">
            <div class="hph-header-content">
                <h2 class="hph-map-title">
                    <i class="fas fa-map-marked-alt"></i>
                    Location & Neighborhood
                </h2>
                <p class="hph-map-subtitle">
                    Explore the area around <?php echo esc_html($address); ?>
                </p>
            </div>
            
            <!-- Map Controls -->
            <div class="hph-map-controls">
                <button class="hph-map-btn hph-map-btn-active" data-view="map">
                    <i class="fas fa-map"></i>
                    Map View
                </button>
                <?php if ($show_streetview): ?>
                <button class="hph-map-btn" data-view="street">
                    <i class="fas fa-street-view"></i>
                    Street View
                </button>
                <?php endif; ?>
                <button class="hph-map-btn" data-view="satellite">
                    <i class="fas fa-satellite"></i>
                    Satellite
                </button>
            </div>
        </div>
        
        <!-- Main Grid -->
        <div class="hph-map-grid">
            
            <!-- Map Column -->
            <div class="hph-map-column">
                
                <!-- Interactive Map -->
                <div class="hph-map-wrapper">
                    <div id="<?php echo esc_attr($map_id); ?>" class="hph-map-canvas"></div>
                    
                    <!-- Map Overlay Controls -->
                    <div class="hph-map-overlay-controls">
                        <button class="hph-map-zoom-in" data-map-id="<?php echo esc_attr($map_id); ?>" data-action="zoom-in">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="hph-map-zoom-out" data-map-id="<?php echo esc_attr($map_id); ?>" data-action="zoom-out">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button class="hph-map-fullscreen" data-map-id="<?php echo esc_attr($map_id); ?>" data-action="fullscreen">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                    
                    <!-- Category Filters -->
                    <div class="hph-map-filters">
                        <button class="hph-filter-btn hph-filter-active" data-category="all">
                            <i class="fas fa-globe"></i>
                            All
                        </button>
                        <button class="hph-filter-btn" data-category="schools">
                            <i class="fas fa-graduation-cap"></i>
                            Schools
                        </button>
                        <button class="hph-filter-btn" data-category="restaurants">
                            <i class="fas fa-utensils"></i>
                            Dining
                        </button>
                        <button class="hph-filter-btn" data-category="shopping">
                            <i class="fas fa-shopping-bag"></i>
                            Shopping
                        </button>
                        <button class="hph-filter-btn" data-category="transit">
                            <i class="fas fa-bus"></i>
                            Transit
                        </button>
                    </div>
                </div>
                
                <!-- Walk Scores -->
                <div class="hph-scores-container">
                    <div class="hph-score-item">
                        <div class="hph-score-icon">
                            <i class="fas fa-walking"></i>
                        </div>
                        <div class="hph-score-content">
                            <div class="hph-score-value <?php echo getScoreColor($walk_score); ?>">
                                <?php echo esc_html($walk_score); ?>
                            </div>
                            <div class="hph-score-label">Walk Score</div>
                            <div class="hph-score-desc">Walker's Paradise</div>
                        </div>
                    </div>
                    
                    <div class="hph-score-item">
                        <div class="hph-score-icon">
                            <i class="fas fa-bus"></i>
                        </div>
                        <div class="hph-score-content">
                            <div class="hph-score-value <?php echo getScoreColor($transit_score); ?>">
                                <?php echo esc_html($transit_score); ?>
                            </div>
                            <div class="hph-score-label">Transit Score</div>
                            <div class="hph-score-desc">Some Transit</div>
                        </div>
                    </div>
                    
                    <div class="hph-score-item">
                        <div class="hph-score-icon">
                            <i class="fas fa-bicycle"></i>
                        </div>
                        <div class="hph-score-content">
                            <div class="hph-score-value <?php echo getScoreColor($bike_score); ?>">
                                <?php echo esc_html($bike_score); ?>
                            </div>
                            <div class="hph-score-label">Bike Score</div>
                            <div class="hph-score-desc">Very Bikeable</div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Info Column -->
            <div class="hph-info-column">
                
                <!-- Neighborhood Overview -->
                <?php if ($neighborhood_description): ?>
                <div class="hph-neighborhood-card">
                    <h3 class="hph-card-title">
                        <i class="fas fa-home"></i>
                        About the Neighborhood
                    </h3>
                    <p class="hph-neighborhood-desc">
                        <?php echo esc_html($neighborhood_description); ?>
                    </p>
                    
                    <div class="hph-neighborhood-stats">
                        <div class="hph-stat">
                            <span class="hph-stat-label">Crime Rate:</span>
                            <span class="hph-stat-value <?php echo esc_attr($crime_colors[$crime_rating]); ?>">
                                <?php echo esc_html($crime_rating); ?>
                            </span>
                        </div>
                        <div class="hph-stat">
                            <span class="hph-stat-label">Noise Level:</span>
                            <span class="hph-stat-value"><?php echo esc_html($noise_level); ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Nearby Places Tabs -->
                <div class="hph-places-card">
                    <h3 class="hph-card-title">
                        <i class="fas fa-map-pin"></i>
                        What's Nearby
                    </h3>
                    
                    <!-- Tab Navigation -->
                    <div class="hph-tabs">
                        <?php foreach ($nearby_places as $index => $category): ?>
                        <button class="hph-tab <?php echo $index === 0 ? 'hph-tab-active' : ''; ?>" 
                                data-tab="<?php echo esc_attr(strtolower($category['category'])); ?>">
                            <?php echo esc_html($category['category']); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="hph-tabs-content">
                        <?php foreach ($nearby_places as $index => $category): ?>
                        <div class="hph-tab-pane <?php echo $index === 0 ? 'hph-tab-pane-active' : ''; ?>" 
                             data-content="<?php echo esc_attr(strtolower($category['category'])); ?>">
                            <?php foreach ($category['places'] as $place): ?>
                            <div class="hph-place-item">
                                <div class="hph-place-info">
                                    <h4 class="hph-place-name"><?php echo esc_html($place['name']); ?></h4>
                                    <div class="hph-place-details">
                                        <span class="hph-place-distance">
                                            <i class="fas fa-route"></i>
                                            <?php echo esc_html($place['distance']); ?>
                                        </span>
                                        <span class="hph-place-time">
                                            <i class="fas fa-clock"></i>
                                            <?php echo esc_html($place['time']); ?>
                                        </span>
                                        <?php if (isset($place['rating'])): ?>
                                        <span class="hph-place-rating">
                                            <i class="fas fa-star"></i>
                                            <?php echo esc_html($place['rating']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button class="hph-place-directions" 
                                        data-map-id="<?php echo esc_attr($map_id); ?>"
                                        data-destination="<?php echo esc_attr($place['name']); ?>">
                                    <i class="fas fa-directions"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Schools -->
                <div class="hph-schools-card">
                    <h3 class="hph-card-title">
                        <i class="fas fa-graduation-cap"></i>
                        Nearby Schools
                    </h3>
                    
                    <div class="hph-schools-list">
                        <?php foreach ($schools as $school): ?>
                        <div class="hph-school-item">
                            <div class="hph-school-rating">
                                <span class="hph-rating-value"><?php echo esc_html($school['rating']); ?></span>
                                <span class="hph-rating-max">/10</span>
                            </div>
                            <div class="hph-school-info">
                                <h4 class="hph-school-name"><?php echo esc_html($school['name']); ?></h4>
                                <div class="hph-school-details">
                                    <span class="hph-school-type"><?php echo esc_html($school['type']); ?></span>
                                    <span class="hph-school-grades">Grades <?php echo esc_html($school['grades']); ?></span>
                                    <span class="hph-school-distance"><?php echo esc_html($school['distance']); ?></span>
                                </div>
                            </div>
                            <a href="#" class="hph-school-link">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Commute Times -->
                <?php if (!empty($commute_times)): ?>
                <div class="hph-commute-card">
                    <h3 class="hph-card-title">
                        <i class="fas fa-route"></i>
                        Commute Times
                    </h3>
                    
                    <div class="hph-commute-list">
                        <?php foreach ($commute_times as $commute): ?>
                        <div class="hph-commute-item">
                            <div class="hph-commute-destination">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo esc_html($commute['destination']); ?>
                            </div>
                            <div class="hph-commute-times">
                                <span class="hph-commute-method">
                                    <i class="fas fa-car"></i>
                                    <?php echo esc_html($commute['drive_time']); ?>
                                </span>
                                <?php if ($commute['transit_time'] !== 'N/A'): ?>
                                <span class="hph-commute-method">
                                    <i class="fas fa-bus"></i>
                                    <?php echo esc_html($commute['transit_time']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            
        </div>
        
    </div>
</section>

<!-- Google Maps Integration -->
<?php if ($google_maps_api_key): ?>
<?php 
// Only load Google Maps API once per page
$maps_api_loaded = false;
if (!$maps_api_loaded && !wp_script_is('google-maps', 'enqueued')) {
    $maps_api_loaded = true;
?>
<script>
// Prevent multiple Google Maps API loads
if (typeof window.googleMapsApiLoaded === 'undefined') {
    window.googleMapsApiLoaded = false;
    window.googleMapsCallbacks = window.googleMapsCallbacks || [];
    
    // Add this map's callback to the queue
    window.googleMapsCallbacks.push('initMap<?php echo esc_js(str_replace('-', '_', $map_id)); ?>');
    
    // Load Google Maps API only once
    if (!window.googleMapsApiLoaded) {
        var script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($google_maps_api_key); ?>&loading=async&callback=initAllMaps';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
        window.googleMapsApiLoaded = true;
        
        // Global callback to init all maps
        window.initAllMaps = function() {
            window.googleMapsCallbacks.forEach(function(callback) {
                if (typeof window[callback] === 'function') {
                    window[callback]();
                }
            });
        };
    }
} else {
    // Maps API already loaded, just add callback
    window.googleMapsCallbacks = window.googleMapsCallbacks || [];
    window.googleMapsCallbacks.push('initMap<?php echo esc_js(str_replace('-', '_', $map_id)); ?>');
}
</script>
<?php } ?>

<script>
// Create specific callback for this map instance
window.initMap<?php echo esc_js(str_replace('-', '_', $map_id)); ?> = function() {
    // Check if required dependencies are available
    if (typeof google === 'undefined' || !google.maps) {
        console.warn('HPH Map: Google Maps API not loaded');
        return;
    }
    
    if (typeof window.HPHListingMap === 'undefined') {
        console.warn('HPH Map: HPHListingMap class not available');
        return;
    }
    
    if (typeof window.hphMapContext === 'undefined') {
        console.warn('HPH Map: hphMapContext not available');
        return;
    }
    
    try {
        window.HPHListingMap.initGoogleMap({
            mapId: '<?php echo esc_js($map_id); ?>',
            lat: <?php echo esc_js($latitude); ?>,
            lng: <?php echo esc_js($longitude); ?>,
            address: <?php echo wp_json_encode($address); ?>,
            zoom: <?php echo esc_js($default_zoom); ?>,
            mapType: '<?php echo esc_js($map_style); ?>',
            showStreetview: <?php echo $show_streetview ? 'true' : 'false'; ?>
        });
    } catch (error) {
        console.error('HPH Map: Error initializing map:', error);
    }
};
</script>
<?php else: ?>
<!-- Static Map Fallback -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.HPHListingMap) {
        window.HPHListingMap.setupStaticMap({
            mapId: '<?php echo esc_js($map_id); ?>',
            lat: <?php echo esc_js($latitude); ?>,
            lng: <?php echo esc_js($longitude); ?>,
            zoom: <?php echo esc_js($default_zoom); ?>
        });
    } else {
        // Fallback static image
        var mapContainer = document.getElementById('<?php echo esc_js($map_id); ?>');
        if (mapContainer) {
            mapContainer.innerHTML = '<div class="hph-map-static-fallback"><img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo esc_js($latitude); ?>,<?php echo esc_js($longitude); ?>&zoom=<?php echo esc_js($default_zoom); ?>&size=800x400&markers=color:red%7C<?php echo esc_js($latitude); ?>,<?php echo esc_js($longitude); ?>" alt="Property Location Map" style="width: 100%; height: 100%; object-fit: cover;"><div class="hph-map-overlay"><p>Interactive map requires Google Maps API</p></div></div>';
        }
    }
});
</script>
<?php endif; ?>