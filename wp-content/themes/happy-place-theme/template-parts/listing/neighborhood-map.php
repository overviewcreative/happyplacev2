<?php
/**
 * Neighborhood-Focused Map Section for Single Listings
 * File: template-parts/listing/neighborhood-map.php
 * 
 * Displays neighborhood information with city data and local places integration
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get listing location data
$listing_coordinates = null;
if (function_exists('hpt_get_listing_coordinates')) {
    try {
        $listing_coordinates = hpt_get_listing_coordinates($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_coordinates failed: ' . $e->getMessage());
    }
}

$listing_address = null;
if (function_exists('hpt_get_listing_address')) {
    try {
        $listing_address = hpt_get_listing_address($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_address failed: ' . $e->getMessage());
    }
}

// Fallback to ACF fields if bridge functions aren't available
if (!$listing_coordinates) {
    $listing_coordinates = [
        'lat' => get_field('latitude', $listing_id),
        'lng' => get_field('longitude', $listing_id)
    ];
}

if (!$listing_address) {
    $street_number = get_field('street_number', $listing_id);
    $street_name = get_field('street_name', $listing_id);
    $street_type = get_field('street_type', $listing_id);
    $city = get_field('city', $listing_id);
    $state = get_field('state', $listing_id);
    $zip_code = get_field('zip_code', $listing_id);
    
    $listing_address = [
        'street_address' => trim($street_number . ' ' . $street_name . ' ' . $street_type),
        'city' => $city,
        'state' => $state,
        'zip_code' => $zip_code,
        'full_address' => trim("$street_number $street_name $street_type, $city, $state $zip_code", ', ')
    ];
}

$latitude = $listing_coordinates['lat'] ?? null;
$longitude = $listing_coordinates['lng'] ?? null;

// Get Mapbox API key from plugin integration
$mapbox_api_key = '';
if (defined('HP_MAPBOX_ACCESS_TOKEN')) {
    $mapbox_api_key = HP_MAPBOX_ACCESS_TOKEN;
} else {
    // Fallback to theme customizer
    $mapbox_api_key = get_theme_mod('mapbox_api_key', '');
}

if (!$latitude || !$longitude || !$mapbox_api_key) {
    return;
}

// Find matching city post based on listing city field
$city_query = new WP_Query([
    'post_type' => 'city',
    'posts_per_page' => 1,
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => '_wp_old_slug',
            'value' => sanitize_title($listing_address['city']),
            'compare' => '='
        ]
    ],
    'title' => $listing_address['city']
]);

$city_post = null;
$city_data = [];

if ($city_query->have_posts()) {
    $city_post = $city_query->posts[0];
    $city_data = [
        'title' => $city_post->post_title,
        'description' => get_field('description', $city_post->ID),
        'tagline' => get_field('tagline', $city_post->ID),
        'population' => get_field('population', $city_post->ID),
        'county' => get_field('county', $city_post->ID),
        'hero_image' => get_field('hero_image', $city_post->ID),
        'gallery' => get_field('gallery', $city_post->ID),
        'related_places' => get_field('related_places', $city_post->ID),
        'featured_places' => get_field('featured_places', $city_post->ID),
        'external_links' => get_field('external_links', $city_post->ID)
    ];
}

wp_reset_postdata();

// Get community/neighborhood data if available
$community_query = new WP_Query([
    'post_type' => 'community',
    'posts_per_page' => 5,
    'meta_query' => [
        [
            'key' => 'city',
            'value' => $listing_address['city'],
            'compare' => 'LIKE'
        ]
    ]
]);

$communities = [];
if ($community_query->have_posts()) {
    while ($community_query->have_posts()) {
        $community_query->the_post();
        $communities[] = [
            'title' => get_the_title(),
            'permalink' => get_permalink(),
            'excerpt' => get_the_excerpt(),
            'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'medium')
        ];
    }
}
wp_reset_postdata();

// Get featured places from city data
$featured_places = [];
if (!empty($city_data['featured_places'])) {
    $places_query = new WP_Query([
        'post_type' => 'local_place',
        'post__in' => $city_data['featured_places'],
        'posts_per_page' => 6,
        'orderby' => 'post__in'
    ]);
    
    if ($places_query->have_posts()) {
        while ($places_query->have_posts()) {
            $places_query->the_post();
            $featured_places[] = [
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'excerpt' => get_the_excerpt(),
                'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'medium'),
                'place_type' => get_field('place_type', get_the_ID()),
                'address' => get_field('address', get_the_ID())
            ];
        }
    }
    wp_reset_postdata();
}

$map_id = 'neighborhood-map-' . $listing_id;
?>

<section class="hph-neighborhood-section" id="neighborhood">
    <div class="hph-neighborhood-wrapper">
        
        <!-- Section Header -->
        <div class="hph-neighborhood-header">
            <h2 class="hph-neighborhood-title">
                <i class="fas fa-map-marker-alt"></i>
                Get to know the neighborhood
            </h2>
            <?php if (!empty($city_data['tagline'])) : ?>
                <p class="hph-neighborhood-tagline"><?php echo esc_html($city_data['tagline']); ?></p>
            <?php endif; ?>
        </div>

        <div class="hph-neighborhood-content">
            
            <!-- Map Section -->
            <div class="hph-neighborhood-map-container">
                <div id="<?php echo esc_attr($map_id); ?>" 
                     class="hph-neighborhood-map"
                     data-lat="<?php echo esc_attr($latitude); ?>"
                     data-lng="<?php echo esc_attr($longitude); ?>"
                     data-address="<?php echo esc_attr($listing_address['street_address']); ?>">
                    <div class="hph-map-loading">
                        <div class="hph-map-spinner"></div>
                        <p>Loading neighborhood map...</p>
                    </div>
                </div>
                
                <!-- Map Controls -->
                <div class="hph-map-overlay-controls">
                    <button class="hph-map-control" onclick="toggleMapStyle('<?php echo esc_js($map_id); ?>')" title="Toggle Map View">
                        <i class="fas fa-satellite"></i>
                    </button>
                    <button class="hph-map-control" onclick="centerOnProperty('<?php echo esc_js($map_id); ?>')" title="Center on Property">
                        <i class="fas fa-home"></i>
                    </button>
                </div>
            </div>

            <!-- City Information -->
            <?php if ($city_post && !empty($city_data)) : ?>
            <div class="hph-city-info">
                <div class="hph-city-header">
                    <?php if ($city_data['hero_image']) : ?>
                        <div class="hph-city-image">
                            <?php echo wp_get_attachment_image($city_data['hero_image'], 'large', false, ['alt' => $city_data['title']]); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="hph-city-overview">
                        <h3><?php echo esc_html($city_data['title']); ?></h3>
                        
                        <div class="hph-city-stats">
                            <?php if ($city_data['population']) : ?>
                                <div class="hph-stat">
                                    <i class="fas fa-users"></i>
                                    <span class="hph-stat-value"><?php echo number_format($city_data['population']); ?></span>
                                    <span class="hph-stat-label">Population</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($city_data['county']) : ?>
                                <div class="hph-stat">
                                    <i class="fas fa-map"></i>
                                    <span class="hph-stat-value"><?php echo esc_html($city_data['county']); ?></span>
                                    <span class="hph-stat-label">County</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="hph-stat">
                                <i class="fas fa-location-dot"></i>
                                <span class="hph-stat-value"><?php echo esc_html($listing_address['state']); ?></span>
                                <span class="hph-stat-label">State</span>
                            </div>
                        </div>
                        
                        <?php if ($city_data['description']) : ?>
                            <div class="hph-city-description">
                                <?php echo wp_kses_post($city_data['description']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($city_data['external_links'])) : ?>
                            <div class="hph-city-links">
                                <h4>Learn More</h4>
                                <ul>
                                    <?php foreach ($city_data['external_links'] as $link) : ?>
                                        <li>
                                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer">
                                                <i class="fas fa-external-link-alt"></i>
                                                <?php echo esc_html($link['label']); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Featured Local Places -->
            <?php if (!empty($featured_places)) : ?>
            <div class="hph-local-places">
                <h3>Local Highlights</h3>
                <div class="hph-places-grid">
                    <?php foreach ($featured_places as $place) : ?>
                        <div class="hph-place-card">
                            <?php if ($place['thumbnail']) : ?>
                                <div class="hph-place-image">
                                    <img src="<?php echo esc_url($place['thumbnail']); ?>" 
                                         alt="<?php echo esc_attr($place['title']); ?>"
                                         loading="lazy">
                                </div>
                            <?php endif; ?>
                            
                            <div class="hph-place-content">
                                <h4><?php echo esc_html($place['title']); ?></h4>
                                
                                <?php if ($place['place_type']) : ?>
                                    <span class="hph-place-type"><?php echo esc_html($place['place_type']); ?></span>
                                <?php endif; ?>
                                
                                <?php if ($place['excerpt']) : ?>
                                    <p><?php echo esc_html(wp_trim_words($place['excerpt'], 15)); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($place['address']) : ?>
                                    <div class="hph-place-address">
                                        <i class="fas fa-location-dot"></i>
                                        <span><?php echo esc_html($place['address']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Communities/Neighborhoods -->
            <?php if (!empty($communities)) : ?>
            <div class="hph-communities">
                <h3>Nearby Communities</h3>
                <div class="hph-communities-grid">
                    <?php foreach ($communities as $community) : ?>
                        <div class="hph-community-card">
                            <?php if ($community['thumbnail']) : ?>
                                <div class="hph-community-image">
                                    <img src="<?php echo esc_url($community['thumbnail']); ?>" 
                                         alt="<?php echo esc_attr($community['title']); ?>"
                                         loading="lazy">
                                </div>
                            <?php endif; ?>
                            
                            <div class="hph-community-content">
                                <h4>
                                    <a href="<?php echo esc_url($community['permalink']); ?>">
                                        <?php echo esc_html($community['title']); ?>
                                    </a>
                                </h4>
                                
                                <?php if ($community['excerpt']) : ?>
                                    <p><?php echo esc_html(wp_trim_words($community['excerpt'], 20)); ?></p>
                                <?php endif; ?>
                                
                                <a href="<?php echo esc_url($community['permalink']); ?>" class="hph-learn-more">
                                    Learn More <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="hph-neighborhood-actions">
                <h3>Explore More</h3>
                <div class="hph-action-buttons">
                    <button class="hph-action-btn" onclick="searchNearby('restaurants', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                        <i class="fas fa-utensils"></i>
                        <span>Restaurants</span>
                    </button>
                    <button class="hph-action-btn" onclick="searchNearby('shopping', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Shopping</span>
                    </button>
                    <button class="hph-action-btn" onclick="searchNearby('schools', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Schools</span>
                    </button>
                    <button class="hph-action-btn" onclick="searchNearby('parks', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                        <i class="fas fa-tree"></i>
                        <span>Parks</span>
                    </button>
                    <button class="hph-action-btn" onclick="searchNearby('medical', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                        <i class="fas fa-hospital"></i>
                        <span>Healthcare</span>
                    </button>
                    <button class="hph-action-btn" onclick="searchNearby('grocery', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Grocery</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mapbox CSS and JS are loaded by the Happy Place Plugin Mapbox Integration -->

<!-- JavaScript functionality is handled by the neighborhood-map.js component file -->
