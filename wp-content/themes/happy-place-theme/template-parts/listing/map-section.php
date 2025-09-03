<?php
/**
 * Simple Mapbox Map Template Part
 * File: template-parts/listing/map-section.php
 * 
 * Displays property location using Mapbox GL JS with bridge functions
 * Requires Mapbox API key in theme customizer
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

// Extract data from bridge function results with fallbacks
if ($listing_coordinates) {
    $latitude = $listing_coordinates['lat'] ?? null;
    $longitude = $listing_coordinates['lng'] ?? null;
} else {
    // Fallback to direct field access
    $latitude = get_field('latitude', $listing_id);
    $longitude = get_field('longitude', $listing_id);
}

// Build full address from bridge function data with fallbacks
if ($listing_address) {
    $full_address = $listing_address['full_address'] ?? '';
    if (!$full_address) {
        // Fallback to manual building if bridge function doesn't provide full address
        $street_address = $listing_address['street_address'] ?? '';
        $city = $listing_address['city'] ?? '';
        $state = $listing_address['state'] ?? '';
        $zip_code = $listing_address['zip_code'] ?? '';
        $full_address = trim("$street_address, $city, $state $zip_code", ', ');
    }
} else {
    // Complete fallback to direct field access
    $street_number = get_field('street_number', $listing_id);
    $street_name = get_field('street_name', $listing_id);
    $street_type = get_field('street_type', $listing_id);
    $city = get_field('city', $listing_id);
    $state = get_field('state', $listing_id);
    $zip_code = get_field('zip_code', $listing_id);
    
    $street_address = trim($street_number . ' ' . $street_name . ' ' . $street_type);
    $full_address = $street_address . ', ' . $city . ', ' . $state . ' ' . $zip_code;
}

// Get Mapbox API key from theme settings
$mapbox_api_key = get_theme_mod('mapbox_api_key', '');

// Don't show map if no coordinates or API key
if (!$latitude || !$longitude || !$mapbox_api_key) {
    return;
}

// Generate unique map ID
$map_id = 'property-map-' . $listing_id;
?>

<section class="hph-map-section">
    <div class="hph-map-header">
        <h2 class="hph-map-title">Location</h2>
        <div class="hph-map-actions">
            <button onclick="openStreetView(<?php echo $latitude; ?>, <?php echo $longitude; ?>)" 
                    class="hph-map-btn">
                <i class="fas fa-street-view"></i>
                Street View
            </button>
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($full_address); ?>" 
               target="_blank"
               class="hph-map-btn">
                <i class="fas fa-directions"></i>
                Get Directions
            </a>
        </div>
    </div>
    
    <!-- Map Container -->
    <div id="<?php echo esc_attr($map_id); ?>" 
         class="hph-map-container"
         data-lat="<?php echo esc_attr($latitude); ?>"
         data-lng="<?php echo esc_attr($longitude); ?>"
         data-address="<?php echo esc_attr($street_address); ?>">
    </div>
    
    <!-- Address Display -->
    <div class="hph-map-address">
        <i class="fas fa-map-marker-alt"></i>
        <span><?php echo esc_html($full_address); ?></span>
    </div>
</section>

<!-- Mapbox CSS -->
<link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />

<!-- Mapbox JS -->
<script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>

<style>
/* Map Section Styles */
.hph-map-section {
    background: white;
    border-radius: var(--hph-radius-lg);
    padding: var(--hph-spacing-2xl);
    box-shadow: var(--hph-shadow-sm);
    margin-bottom: var(--hph-spacing-2xl);
}

.hph-map-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--hph-spacing-xl);
}

.hph-map-title {
    font-size: var(--hph-text-2xl);
    font-weight: 600;
    color: var(--hph-gray-900);
    margin: 0;
}

.hph-map-actions {
    display: flex;
    gap: var(--hph-spacing-sm);
}

.hph-map-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--hph-spacing-sm);
    padding: var(--hph-spacing-sm) var(--hph-spacing-lg);
    background: white;
    border: 1px solid var(--hph-gray-300);
    border-radius: var(--hph-radius-md);
    color: var(--hph-gray-700);
    font-size: var(--hph-text-sm);
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.hph-map-btn:hover {
    background: var(--hph-primary-light);
    border-color: var(--hph-primary);
    color: var(--hph-primary);
}

/* Map Container */
.hph-map-container {
    width: 100%;
    height: 450px;
    border-radius: var(--hph-radius-lg);
    overflow: hidden;
    background: var(--hph-gray-100);
    position: relative;
}

/* Mapbox Controls */
.mapboxgl-ctrl-attrib {
    font-size: 10px;
}

.mapboxgl-ctrl-logo {
    margin: 0 !important;
}

/* Custom Marker Styles */
.hph-map-marker {
    width: 40px;
    height: 40px;
    background: var(--hph-primary);
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    border: 3px solid white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.hph-map-marker i {
    transform: rotate(45deg);
    color: white;
    font-size: 18px;
}

/* Popup Styles */
.mapboxgl-popup-content {
    padding: var(--hph-spacing-md);
    border-radius: var(--hph-radius-md);
    box-shadow: var(--hph-shadow-lg);
    min-width: 200px;
}

.hph-popup-content {
    text-align: center;
}

.hph-popup-title {
    font-weight: 600;
    color: var(--hph-gray-900);
    margin-bottom: var(--hph-spacing-xs);
}

.hph-popup-address {
    font-size: var(--hph-text-sm);
    color: var(--hph-gray-600);
}

/* Address Display */
.hph-map-address {
    display: flex;
    align-items: center;
    gap: var(--hph-spacing-sm);
    margin-top: var(--hph-spacing-lg);
    padding: var(--hph-spacing-md);
    background: var(--hph-gray-50);
    border-radius: var(--hph-radius-md);
    font-size: var(--hph-text-sm);
    color: var(--hph-gray-700);
}

.hph-map-address i {
    color: var(--hph-primary);
}

/* Responsive */
@media (max-width: 768px) {
    .hph-map-section {
        padding: var(--hph-spacing-xl);
        border-radius: 0;
    }
    
    .hph-map-container {
        height: 350px;
    }
    
    .hph-map-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--hph-spacing-md);
    }
    
    .hph-map-actions {
        width: 100%;
    }
    
    .hph-map-btn {
        flex: 1;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .hph-map-container {
        height: 300px;
        border-radius: var(--hph-radius-md);
    }
    
    .hph-map-title {
        font-size: var(--hph-text-xl);
    }
}

/* Loading State */
.hph-map-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: var(--hph-gray-500);
}

.hph-map-loading i {
    font-size: 2rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 1; }
}
</style>

<script>
// Initialize Mapbox Map
document.addEventListener('DOMContentLoaded', function() {
    const mapContainer = document.getElementById('<?php echo esc_js($map_id); ?>');
    
    if (!mapContainer) return;
    
    // Get map data
    const lat = parseFloat(mapContainer.dataset.lat);
    const lng = parseFloat(mapContainer.dataset.lng);
    const address = mapContainer.dataset.address;
    
    // Set Mapbox access token
    mapboxgl.accessToken = '<?php echo esc_js($mapbox_api_key); ?>';
    
    try {
        // Create map
        const map = new mapboxgl.Map({
            container: '<?php echo esc_js($map_id); ?>',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [lng, lat],
            zoom: 15,
            pitch: 0,
            bearing: 0
        });
        
        // Add navigation controls
        map.addControl(new mapboxgl.NavigationControl({
            showCompass: false
        }), 'top-right');
        
        // Create custom marker element
        const markerEl = document.createElement('div');
        markerEl.className = 'hph-map-marker';
        markerEl.innerHTML = '<i class="fas fa-home"></i>';
        
        // Add marker to map
        const marker = new mapboxgl.Marker({
            element: markerEl,
            anchor: 'bottom'
        })
        .setLngLat([lng, lat])
        .addTo(map);
        
        // Create popup
        const popup = new mapboxgl.Popup({
            offset: 25,
            closeButton: false
        })
        .setHTML(`
            <div class="hph-popup-content">
                <div class="hph-popup-title">Property Location</div>
                <div class="hph-popup-address">${address}</div>
            </div>
        `);
        
        // Attach popup to marker
        marker.setPopup(popup);
        
        // Show popup on load
        popup.addTo(map);
        
        // Add hover effect
        markerEl.addEventListener('mouseenter', () => {
            markerEl.style.transform = 'rotate(-45deg) scale(1.1)';
        });
        
        markerEl.addEventListener('mouseleave', () => {
            markerEl.style.transform = 'rotate(-45deg) scale(1)';
        });
        
        // Resize map on window resize
        window.addEventListener('resize', () => {
            map.resize();
        });
        
    } catch (error) {
        console.error('Error initializing map:', error);
        
        // Show error message
        mapContainer.innerHTML = `
            <div class="hph-map-loading">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Unable to load map</p>
            </div>
        `;
    }
});

// Open Street View
function openStreetView(lat, lng) {
    const url = `https://www.google.com/maps/@${lat},${lng},3a,75y,90t/data=!3m6!1e1!3m4!1s!2e0!7i16384!8i8192`;
    window.open(url, '_blank');
}
</script>