<?php
/**
 * Combined City Location Section
 * File: template-parts/listing/city-location-section.php
 * 
 * Displays map and city information in a clean, flowing layout
 * Uses HPH framework utility classes
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get coordinates for map
$coordinates = null;
if (function_exists('hpt_get_listing_coordinates')) {
    try {
        $coordinates = hpt_get_listing_coordinates($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_coordinates failed: ' . $e->getMessage());
    }
}

if (!$coordinates || !$coordinates['lat'] || !$coordinates['lng']) {
    $lat = get_field('latitude', $listing_id);
    $lng = get_field('longitude', $listing_id);
    $coordinates = ($lat && $lng) ? ['lat' => floatval($lat), 'lng' => floatval($lng)] : null;
}

// Get city name
$city_name = null;
if (function_exists('hpt_get_listing_city')) {
    try {
        $city_name = hpt_get_listing_city($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_city failed: ' . $e->getMessage());
    }
}
if (!$city_name) {
    $city_name = get_field('city', $listing_id);
}

// Don't show section if no city or coordinates
if (!$city_name && !$coordinates) {
    // Debug: Log what we found
    if (WP_DEBUG) {
        error_log('Simple Map Debug - City: ' . ($city_name ?: 'not found') . ', Coordinates: ' . ($coordinates ? json_encode($coordinates) : 'not found'));
    }
    return;
}

// If we have city but no coordinates, try to geocode or use a default location
if (!$coordinates && $city_name) {
    // For now, let's use a default coordinate for the city center
    // This should be replaced with proper geocoding
    $coordinates = ['lat' => 39.0, 'lng' => -75.5]; // Default Delaware coordinates
}

// Get address data for map
$listing_data = [
    'id' => $listing_id,
    'title' => get_the_title($listing_id),
    'latitude' => $coordinates['lat'],
    'longitude' => $coordinates['lng'],
    'permalink' => get_permalink($listing_id),
];

// Get price for popup
$listing_price = 0;
if (function_exists('hpt_get_listing_data')) {
    try {
        $listing_info = hpt_get_listing_data($listing_id);
        $listing_price = $listing_info['price'] ?? 0;
    } catch (Exception $e) {
        $listing_price = get_field('listing_price', $listing_id) ?: 0;
    }
} else {
    $listing_price = get_field('listing_price', $listing_id) ?: 0;
}

// Get full address
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
    $listing_data['city'] = $city_name;
    $listing_data['state'] = get_field('state', $listing_id);
    $listing_data['zip_code'] = get_field('zip_code', $listing_id);
}

// Find city post for additional info
$city_post = null;
if ($city_name) {
    $city_query = new WP_Query([
        'post_type' => 'city',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => 'city_name',
                'value' => $city_name,
                'compare' => '='
            ]
        ]
    ]);
    
    if (!$city_query->have_posts()) {
        $city_query = new WP_Query([
            'post_type' => 'city',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            's' => $city_name
        ]);
    }
    
    if ($city_query->have_posts()) {
        $city_post = $city_query->posts[0];
    }
    wp_reset_postdata();
}

// Get Mapbox token
$mapbox_token = '';
if (function_exists('hp_get_mapbox_token')) {
    $mapbox_token = hp_get_mapbox_token();
} else if (defined('HP_MAPBOX_ACCESS_TOKEN')) {
    $mapbox_token = HP_MAPBOX_ACCESS_TOKEN;
} else if (class_exists('\HappyPlace\Core\ConfigurationManager')) {
    $config_manager = \HappyPlace\Core\ConfigurationManager::get_instance();
    $mapbox_token = $config_manager->get('mapbox_access_token', '');
}

if (empty($mapbox_token)) {
    if (WP_DEBUG) {
        error_log('Happy Place Map: No Mapbox token found for listing ' . $listing_id);
    }
    return;
}

// Map configuration
$map_options = [
    'center' => [$coordinates['lng'], $coordinates['lat']],
    'zoom' => 15,
    'style' => 'mapbox://styles/mapbox/streets-v12',
];

$map_id = 'hph-listing-map-' . $listing_id;

// Get city data if post exists
$city_data = [];
if ($city_post) {
    $city_id = $city_post->ID;
    $city_data['url'] = get_permalink($city_id);
    $city_data['state'] = get_field('state', $city_id) ?: $listing_data['state'];
    $city_data['population'] = get_field('population', $city_id);
    $city_data['description'] = get_field('description', $city_id) ?: get_the_excerpt($city_id);
    $city_data['image'] = get_the_post_thumbnail_url($city_id, 'large');
    
    // Get highlights
    $city_data['attractions'] = get_field('attractions', $city_id) ?: get_field('points_of_interest', $city_id);
    $city_data['dining'] = get_field('dining_entertainment', $city_id) ?: get_field('restaurants', $city_id);
    $city_data['parks'] = get_field('parks_recreation', $city_id) ?: get_field('recreation', $city_id);
    
    // Get stats
    $city_listings_query = new WP_Query([
        'post_type' => 'listing',
        'meta_query' => [
            [
                'key' => 'city',
                'value' => $city_name,
                'compare' => '='
            ]
        ],
        'post_status' => 'publish',
        'posts_per_page' => -1
    ]);
    $city_data['listing_count'] = $city_listings_query->found_posts;
    wp_reset_postdata();
    
    $city_data['median_price'] = get_field('median_home_price', $city_id);
}

?>

<section class="hph-section hph-mb-lg">
    <div class="hph-container">
        
        <!-- Section Header -->
        <div class="hph-section__header hph-mb-md">
            <h2 class="hph-section__title hph-text-xl hph-font-bold">
                About <?php echo esc_html($city_name); ?>
                <?php if (!empty($city_data['state'])) : ?>
                    <span class="hph-text-gray-600 hph-font-normal">, <?php echo esc_html($city_data['state']); ?></span>
                <?php endif; ?>
            </h2>
        </div>
        
        <!-- Map -->
        <div class="hph-section__content">
            <div class="hph-map-wrapper hph-mb-xl">
                <div class="hph-rounded-lg hph-overflow-hidden hph-shadow-lg">
                    <div id="<?php echo esc_attr($map_id); ?>" 
                         style="height: 400px;"
                         data-hph-map="true"
                         data-map-center="<?php echo esc_attr(json_encode($map_options['center'])); ?>"
                         data-map-zoom="<?php echo esc_attr($map_options['zoom']); ?>"
                         data-map-style="<?php echo esc_attr($map_options['style']); ?>">
                    </div>
                </div>
            
            <!-- Map Actions Bar -->
            <div class="hph-flex hph-justify-between hph-items-center hph-mt-md">
                <div class="hph-text-gray-700">
                    <i class="fas fa-map-marker-alt hph-text-primary hph-mr-2"></i>
                    <?php 
                    $address_parts = array_filter([
                        $listing_data['street_address'],
                        $listing_data['city'],
                        $listing_data['state'],
                        $listing_data['zip_code']
                    ]);
                    echo esc_html(implode(', ', $address_parts));
                    ?>
                </div>
                
                <div class="hph-flex hph-gap-sm">
                    <?php 
                    $directions_url = "https://www.google.com/maps/dir/?api=1&destination=" . urlencode(trim("{$listing_data['street_address']}, {$listing_data['city']}, {$listing_data['state']} {$listing_data['zip_code']}", ', '));
                    ?>
                    <a href="<?php echo esc_url($directions_url); ?>" 
                       target="_blank"
                       class="hph-btn hph-btn-primary hph-btn-sm">
                        <i class="fas fa-directions hph-mr-2"></i>
                        Get Directions
                    </a>
                </div>
            </div>
            
            <?php if ($city_post && !empty($city_data)) : ?>
            
            <!-- City Information -->
            <div class="hph-city-info">
                
                <?php if (!empty($city_data['image']) || !empty($city_data['description'])) : ?>
                <div class="hph-grid hph-grid-cols-1 hph-grid-cols-lg-2 hph-gap-xl hph-mb-xl">
                
                <?php if (!empty($city_data['image'])) : ?>
                <div>
                    <img src="<?php echo esc_url($city_data['image']); ?>" 
                         alt="<?php echo esc_attr($city_name); ?>"
                         class="hph-w-full hph-rounded-lg hph-shadow-md">
                </div>
                <?php endif; ?>
                
                <div>
                    <?php if (!empty($city_data['description'])) : ?>
                        <p class="hph-text-lg hph-leading-relaxed hph-text-gray-700">
                            <?php echo wp_kses_post(wp_trim_words($city_data['description'], 60)); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($city_data['population']) || !empty($city_data['listing_count']) || !empty($city_data['median_price'])) : ?>
                    <div class="hph-flex hph-gap-lg hph-mt-lg">
                        <?php if (!empty($city_data['population'])) : ?>
                        <div>
                            <div class="hph-text-2xl hph-font-bold hph-text-primary"><?php echo number_format($city_data['population']); ?></div>
                            <div class="hph-text-sm hph-text-gray-600">Population</div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($city_data['listing_count']) && $city_data['listing_count'] > 0) : ?>
                        <div>
                            <div class="hph-text-2xl hph-font-bold hph-text-primary"><?php echo number_format($city_data['listing_count']); ?></div>
                            <div class="hph-text-sm hph-text-gray-600">Active Listings</div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($city_data['median_price'])) : ?>
                        <div>
                            <div class="hph-text-2xl hph-font-bold hph-text-primary">$<?php echo number_format($city_data['median_price'] / 1000); ?>K</div>
                            <div class="hph-text-sm hph-text-gray-600">Median Price</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
            </div>
            <?php endif; ?>
            
            <!-- City Highlights -->
            <?php if (!empty($city_data['attractions']) || !empty($city_data['dining']) || !empty($city_data['parks'])) : ?>
            <div class="hph-grid hph-grid-cols-1 hph-grid-cols-md-3 hph-gap-lg">
                
                <?php if (!empty($city_data['attractions'])) : ?>
                <div>
                    <h4 class="hph-text-lg hph-font-semibold hph-mb-sm">
                        <i class="fas fa-map-marker-alt hph-text-primary hph-mr-2"></i>
                        Local Attractions
                    </h4>
                    <p class="hph-text-gray-600">
                        <?php echo esc_html(is_array($city_data['attractions']) ? implode(', ', array_slice($city_data['attractions'], 0, 3)) : wp_trim_words($city_data['attractions'], 20)); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($city_data['dining'])) : ?>
                <div>
                    <h4 class="hph-text-lg hph-font-semibold hph-mb-sm">
                        <i class="fas fa-utensils hph-text-primary hph-mr-2"></i>
                        Dining & Entertainment
                    </h4>
                    <p class="hph-text-gray-600">
                        <?php echo esc_html(is_array($city_data['dining']) ? implode(', ', array_slice($city_data['dining'], 0, 3)) : wp_trim_words($city_data['dining'], 20)); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($city_data['parks'])) : ?>
                <div>
                    <h4 class="hph-text-lg hph-font-semibold hph-mb-sm">
                        <i class="fas fa-tree hph-text-primary hph-mr-2"></i>
                        Parks & Recreation
                    </h4>
                    <p class="hph-text-gray-600">
                        <?php echo esc_html(is_array($city_data['parks']) ? implode(', ', array_slice($city_data['parks'], 0, 3)) : wp_trim_words($city_data['parks'], 20)); ?>
                    </p>
                </div>
                <?php endif; ?>
                
            </div>
            <?php endif; ?>
            
            <?php if (!empty($city_data['url'])) : ?>
            <div class="hph-mt-xl">
                <a href="<?php echo esc_url($city_data['url']); ?>" 
                   class="hph-text-primary hph-font-semibold hover:hph-text-primary-dark">
                    Learn more about <?php echo esc_html($city_name); ?>
                    <i class="fas fa-arrow-right hph-ml-2"></i>
                </a>
            </div>
            <?php endif; ?>
            
        </div>
        
        <?php endif; ?>
        
    </div>
</section>

<script>
// Mapbox configuration
window.hph_mapbox_config = window.hph_mapbox_config || {};
window.hph_mapbox_config.access_token = '<?php echo esc_js($mapbox_token); ?>';

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking for mapboxgl...');
    if (typeof mapboxgl !== 'undefined') {
        console.log('Mapbox GL JS is loaded');
        mapboxgl.accessToken = '<?php echo esc_js($mapbox_token); ?>';
        
        var mapCenter = <?php echo json_encode($map_options['center']); ?>;
        console.log('Map center coordinates:', mapCenter);
        
        var map = new mapboxgl.Map({
            container: '<?php echo esc_js($map_id); ?>',
            style: '<?php echo esc_js($map_options['style']); ?>',
            center: mapCenter,
            zoom: <?php echo esc_js($map_options['zoom']); ?>
        });
        
        map.addControl(new mapboxgl.NavigationControl());
        
        // Create simple marker with HPH primary color
        var marker = new mapboxgl.Marker({
            color: '#50bae1'
        })
        .setLngLat(mapCenter)
        .addTo(map);
        
        console.log('Marker added to map at coordinates:', mapCenter);
    } else {
        console.error('Mapbox GL JS is not loaded');
    }
});
</script>

<?php
if (!wp_script_is('mapbox-gl-js', 'enqueued')) {
    ?>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>
    <?php
}
?>