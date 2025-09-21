<?php
/**
 * Enhanced Listing Map Section
 * File: template-parts/listing/map-section.php
 * 
 * Modern map display with improved UX/UI for The Parker Group
 * Styles are in separate CSS file: listing-map-enhanced.css
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get location data using bridge functions with fallbacks
$listing_address = null;
if (function_exists('hpt_get_listing_address')) {
    try {
        $listing_address = hpt_get_listing_address($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_address failed: ' . $e->getMessage());
    }
}

$listing_coordinates = null;
if (function_exists('hpt_get_listing_coordinates')) {
    try {
        $listing_coordinates = hpt_get_listing_coordinates($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_coordinates failed: ' . $e->getMessage());
    }
}

// Extract coordinates
if ($listing_coordinates) {
    $latitude = $listing_coordinates['lat'] ?? null;
    $longitude = $listing_coordinates['lng'] ?? null;
} else {
    $latitude = get_field('latitude', $listing_id);
    $longitude = get_field('longitude', $listing_id);
}

// Build full address
if ($listing_address) {
    $full_address = $listing_address['full_address'] ?? '';
    $street_address = $listing_address['street_address'] ?? '';
    $city = $listing_address['city'] ?? '';
    $state = $listing_address['state'] ?? '';
    $zip_code = $listing_address['zip_code'] ?? '';
    
    if (!$full_address) {
        $full_address = trim("$street_address, $city, $state $zip_code", ', ');
    }
} else {
    $street_number = get_field('street_number', $listing_id);
    $street_name = get_field('street_name', $listing_id);
    $street_type = get_field('street_type', $listing_id);
    $city = get_field('city', $listing_id);
    $state = get_field('state', $listing_id);
    $zip_code = get_field('zip_code', $listing_id);
    
    $street_address = trim($street_number . ' ' . $street_name . ' ' . $street_type);
    $full_address = $street_address . ', ' . $city . ', ' . $state . ' ' . $zip_code;
}

// Get subdivision/community name if available
$subdivision = get_field('subdivision', $listing_id) ?: '';

// Get Mapbox API key
$mapbox_api_key = get_theme_mod('mapbox_api_key', '');

if (!$latitude || !$longitude || !$mapbox_api_key) {
    return;
}

$map_id = 'property-map-' . $listing_id;
?>

<section class="hph-map-section" id="location">
    <div class="hph-map-wrapper">
        <!-- Section Header -->
        <div class="hph-map-header">
            <div class="hph-map-title-group">
                <h2 class="hph-map-title">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Location</span>
                </h2>
                <p class="hph-map-subtitle">Explore the neighborhood</p>
            </div>
            
            <div class="hph-map-actions">
                <button class="hph-map-btn hph-map-btn--view" 
                        onclick="toggleMapView('<?php echo esc_js($map_id); ?>')"
                        data-view="streets">
                    <i class="fas fa-satellite"></i>
                    <span class="hph-btn-text">Satellite</span>
                </button>
                <button class="hph-map-btn" 
                        onclick="openStreetView(<?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                    <i class="fas fa-street-view"></i>
                    <span class="hph-btn-text">Street View</span>
                </button>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($full_address); ?>" 
                   target="_blank"
                   rel="noopener noreferrer"
                   class="hph-map-btn hph-map-btn--directions">
                    <i class="fas fa-route"></i>
                    <span class="hph-btn-text">Get Directions</span>
                </a>
            </div>
        </div>

        <!-- Map Container with Address Bar -->
        <div class="hph-map-container-wrapper">
            <!-- Map -->
            <div id="<?php echo esc_attr($map_id); ?>" 
                 class="hph-map-container"
                 data-lat="<?php echo esc_attr($latitude); ?>"
                 data-lng="<?php echo esc_attr($longitude); ?>"
                 data-address="<?php echo esc_attr($street_address); ?>">
                <div class="hph-map-loading">
                    <div class="hph-map-spinner"></div>
                    <p>Loading map...</p>
                </div>
            </div>

            <!-- Floating Controls -->
            <div class="hph-map-controls">
                <button class="hph-map-control" 
                        onclick="zoomToProperty('<?php echo esc_js($map_id); ?>')"
                        title="Center on Property">
                    <i class="fas fa-home"></i>
                </button>
                <button class="hph-map-control" 
                        onclick="toggleFullscreen('<?php echo esc_js($map_id); ?>')"
                        title="Fullscreen">
                    <i class="fas fa-expand"></i>
                </button>
            </div>

            <!-- Address Bar -->
            <div class="hph-map-address-bar">
                <div class="hph-address-content">
                    <i class="fas fa-map-pin"></i>
                    <div class="hph-address-details">
                        <span class="hph-address-main"><?php echo esc_html($street_address); ?></span>
                        <span class="hph-address-city"><?php echo esc_html($city); ?>, <?php echo esc_html($state); ?> <?php echo esc_html($zip_code); ?></span>
                    </div>
                    <?php if ($subdivision) : ?>
                        <div class="hph-address-community">
                            <i class="fas fa-home-lg-alt"></i>
                            <span><?php echo esc_html($subdivision); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <button class="hph-copy-address" onclick="copyAddress('<?php echo esc_js($full_address); ?>')">
                    <i class="fas fa-copy"></i>
                    <span>Copy</span>
                </button>
            </div>
        </div>

        <!-- What's Nearby Section -->
        <div class="hph-map-nearby">
            <h3 class="hph-nearby-title">What's Nearby</h3>
            <div class="hph-nearby-grid">
                <button class="hph-nearby-item" onclick="searchNearby('schools', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Schools</span>
                </button>
                <button class="hph-nearby-item" onclick="searchNearby('grocery', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Grocery</span>
                </button>
                <button class="hph-nearby-item" onclick="searchNearby('restaurants', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                    <i class="fas fa-utensils"></i>
                    <span>Dining</span>
                </button>
                <button class="hph-nearby-item" onclick="searchNearby('parks', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                    <i class="fas fa-tree"></i>
                    <span>Parks</span>
                </button>
                <button class="hph-nearby-item" onclick="searchNearby('medical', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                    <i class="fas fa-hospital"></i>
                    <span>Medical</span>
                </button>
                <button class="hph-nearby-item" onclick="searchNearby('shopping', <?php echo $latitude; ?>, <?php echo $longitude; ?>)">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Shopping</span>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Mapbox CSS -->
<link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />

<!-- Mapbox JS -->
<script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>

<script>
// Map JavaScript
let map;
let currentStyle = 'streets';
let propertyMarker;

document.addEventListener('DOMContentLoaded', function() {
    const mapContainer = document.getElementById('<?php echo esc_js($map_id); ?>');
    if (!mapContainer) return;
    
    const lat = parseFloat(mapContainer.dataset.lat);
    const lng = parseFloat(mapContainer.dataset.lng);
    const address = mapContainer.dataset.address;
    
    // Initialize Mapbox
    mapboxgl.accessToken = '<?php echo esc_js($mapbox_api_key); ?>';
    
    try {
        // Remove loading indicator
        mapContainer.innerHTML = '';
        
        // Create map
        map = new mapboxgl.Map({
            container: '<?php echo esc_js($map_id); ?>',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [lng, lat],
            zoom: 15,
            pitch: 0,
            bearing: 0,
            antialias: true
        });
        
        // Add navigation controls
        map.addControl(new mapboxgl.NavigationControl({
            showCompass: false
        }), 'top-right');
        
        // Create custom property marker
        const markerEl = document.createElement('div');
        markerEl.className = 'hph-property-marker';
        markerEl.innerHTML = '<i class="fas fa-home"></i>';
        
        propertyMarker = new mapboxgl.Marker({
            element: markerEl,
            anchor: 'bottom'
        })
        .setLngLat([lng, lat])
        .addTo(map);
        
        // Add popup to marker
        const popup = new mapboxgl.Popup({
            offset: 25,
            closeButton: false,
            className: 'hph-property-popup'
        })
        .setHTML(`
            <div class="hph-popup-content">
                <strong>${address}</strong>
                <p>Your future happy place</p>
            </div>
        `);
        
        propertyMarker.setPopup(popup);
        
        // Show popup on load
        setTimeout(() => {
            popup.addTo(map);
        }, 500);
        
    } catch (error) {
        console.error('Error initializing map:', error);
        mapContainer.innerHTML = '<div class="hph-map-error"><i class="fas fa-exclamation-triangle"></i><p>Unable to load map</p></div>';
    }
});

// Toggle map view between streets and satellite
function toggleMapView(mapId) {
    if (!map) return;
    
    const btn = event.currentTarget;
    currentStyle = currentStyle === 'streets' ? 'satellite' : 'streets';
    
    if (currentStyle === 'satellite') {
        map.setStyle('mapbox://styles/mapbox/satellite-streets-v12');
        btn.innerHTML = '<i class="fas fa-map"></i><span class="hph-btn-text">Streets</span>';
    } else {
        map.setStyle('mapbox://styles/mapbox/streets-v12');
        btn.innerHTML = '<i class="fas fa-satellite"></i><span class="hph-btn-text">Satellite</span>';
    }
}

// Open Google Street View
function openStreetView(lat, lng) {
    const url = `https://www.google.com/maps/@${lat},${lng},3a,75y,90t/data=!3m6!1e1!3m4!1s!2e0!7i16384!8i8192`;
    window.open(url, '_blank');
}

// Zoom to property
function zoomToProperty(mapId) {
    if (!map || !propertyMarker) return;
    
    map.flyTo({
        center: propertyMarker.getLngLat(),
        zoom: 16,
        duration: 1500
    });
}

// Toggle fullscreen
function toggleFullscreen(mapId) {
    const mapWrapper = document.querySelector('.hph-map-container-wrapper');
    
    if (!document.fullscreenElement) {
        mapWrapper.requestFullscreen().catch(err => {
            console.error('Error attempting to enable fullscreen:', err);
        });
    } else {
        document.exitFullscreen();
    }
}

// Copy address to clipboard
function copyAddress(address) {
    navigator.clipboard.writeText(address).then(() => {
        const btn = event.currentTarget;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i><span>Copied!</span>';
        btn.classList.add('hph-copy-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('hph-copy-success');
        }, 2000);
    });
}

// Search nearby places (opens in Google Maps)
function searchNearby(type, lat, lng) {
    const searches = {
        schools: 'schools',
        grocery: 'grocery+stores',
        restaurants: 'restaurants',
        parks: 'parks',
        medical: 'hospitals+medical',
        shopping: 'shopping'
    };
    
    const query = searches[type] || type;
    const url = `https://www.google.com/maps/search/${query}/@${lat},${lng},14z`;
    window.open(url, '_blank');
}
</script>
