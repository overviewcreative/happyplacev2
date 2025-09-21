<?php
/**
 * HPH Map Section Template
 * Uses Mapbox GL JS instead of Google Maps for contact pages and location displays
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/map');
}

// Default arguments
$defaults = array(
    'style' => 'full', // Options: 'full', 'contained', 'rounded', 'minimal'
    'height' => 'md', // Options: 'sm', 'md', 'lg', 'xl'
    'theme' => 'light', // Options: 'light', 'dark', 'satellite', 'streets'
    'padding' => 'none', // Section padding: 'none', 'sm', 'md', 'lg', 'xl'
    'container' => 'full', // Container: 'full', 'default', 'narrow', 'wide'
    'latitude' => 38.7745, // Default to Georgetown, DE
    'longitude' => -75.4621,
    'zoom' => 12,
    'marker_title' => 'Our Location',
    'info_window' => '',
    'markers' => array(), // Array of additional markers
    'show_controls' => true,
    'show_fullscreen' => true,
    'section_id' => '',
    'offices' => array(), // Multiple office locations
    'map_id' => '',
    'interactive' => true,
    'fit_bounds' => false // Auto-fit to all markers
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Generate unique map ID
$map_container_id = $map_id ?: 'hph_map_' . uniqid();

// Get Mapbox token
$mapbox_token = '';
if (function_exists('hp_get_mapbox_token')) {
    $mapbox_token = hp_get_mapbox_token();
} elseif (defined('HP_MAPBOX_ACCESS_TOKEN')) {
    $mapbox_token = HP_MAPBOX_ACCESS_TOKEN;
} elseif (function_exists('get_option')) {
    $mapbox_token = get_option('hp_mapbox_access_token', '');
}

// Build section styles
$section_styles = array(
    'position: relative',
    'width: 100%'
);

// Padding styles for section
if ($padding !== 'none') {
    switch ($padding) {
        case 'sm':
            $section_styles[] = 'padding-top: var(--hph-space-6)';
            $section_styles[] = 'padding-bottom: var(--hph-space-6)';
            break;
        case 'md':
            $section_styles[] = 'padding-top: var(--hph-space-8)';
            $section_styles[] = 'padding-bottom: var(--hph-space-8)';
            break;
        case 'lg':
            $section_styles[] = 'padding-top: var(--hph-space-12)';
            $section_styles[] = 'padding-bottom: var(--hph-space-12)';
            break;
        case 'xl':
            $section_styles[] = 'padding-top: var(--hph-space-16)';
            $section_styles[] = 'padding-bottom: var(--hph-space-16)';
            break;
    }
}

// Container styles
$container_styles = array();
if ($container !== 'full') {
    $container_styles[] = 'margin-left: auto';
    $container_styles[] = 'margin-right: auto';
    $container_styles[] = 'padding-left: var(--hph-space-6)';
    $container_styles[] = 'padding-right: var(--hph-space-6)';
    
    switch ($container) {
        case 'narrow':
            $container_styles[] = 'max-width: var(--hph-container-sm)';
            break;
        case 'wide':
            $container_styles[] = 'max-width: var(--hph-container-2xl)';
            break;
        case 'default':
        default:
            $container_styles[] = 'max-width: var(--hph-container-xl)';
            break;
    }
}

// Map container styles
$map_styles = array(
    'position: relative',
    'width: 100%',
    'background: var(--hph-gray-100)'
);

// Height styles
switch ($height) {
    case 'sm':
        $map_styles[] = 'height: 300px';
        break;
    case 'md':
        $map_styles[] = 'height: 400px';
        break;
    case 'lg':
        $map_styles[] = 'height: 500px';
        break;
    case 'xl':
        $map_styles[] = 'height: 600px';
        break;
}

// Style-based appearance
switch ($style) {
    case 'rounded':
        $map_styles[] = 'border-radius: var(--hph-radius-xl)';
        $map_styles[] = 'overflow: hidden';
        $map_styles[] = 'box-shadow: 0 10px 30px rgba(0,0,0,0.1)';
        break;
    case 'contained':
        $map_styles[] = 'border-radius: var(--hph-radius-lg)';
        $map_styles[] = 'overflow: hidden';
        $map_styles[] = 'border: 1px solid var(--hph-gray-200)';
        break;
    case 'minimal':
        $map_styles[] = 'border-radius: var(--hph-radius-md)';
        $map_styles[] = 'overflow: hidden';
        break;
    case 'full':
    default:
        // No additional styling for full width
        break;
}

// Map theme/style
$map_theme = '';
switch ($theme) {
    case 'dark':
        $map_theme = 'mapbox://styles/mapbox/dark-v11';
        break;
    case 'satellite':
        $map_theme = 'mapbox://styles/mapbox/satellite-streets-v12';
        break;
    case 'streets':
        $map_theme = 'mapbox://styles/mapbox/streets-v12';
        break;
    case 'light':
    default:
        $map_theme = 'mapbox://styles/mapbox/light-v11';
        break;
}

// Prepare markers data
$all_markers = array();

// Add main marker if coordinates provided
if ($latitude && $longitude) {
    $all_markers[] = array(
        'lat' => $latitude,
        'lng' => $longitude,
        'title' => $marker_title,
        'description' => $info_window,
        'type' => 'primary'
    );
}

// Add office markers
if (!empty($offices)) {
    foreach ($offices as $office) {
        if (!empty($office['lat']) && !empty($office['lng'])) {
            $all_markers[] = array(
                'lat' => $office['lat'],
                'lng' => $office['lng'],
                'title' => $office['title'] ?? 'Office Location',
                'description' => $office['description'] ?? '',
                'type' => 'office'
            );
        }
    }
}

// Add custom markers
if (!empty($markers)) {
    foreach ($markers as $marker) {
        if (!empty($marker['lat']) && !empty($marker['lng'])) {
            $all_markers[] = array_merge(array(
                'title' => '',
                'description' => '',
                'type' => 'custom'
            ), $marker);
        }
    }
}

// Ensure Mapbox assets are loaded
if (function_exists('wp_enqueue_script')) {
    wp_enqueue_style('mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css', array(), '2.15.0');
    wp_enqueue_script('mapbox-gl-js', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', array(), '2.15.0', true);
}
?>

<section 
    class="hph-map-section"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    style="<?php echo implode('; ', $section_styles); ?>"
>
    <?php if ($container !== 'full'): ?>
    <div style="<?php echo implode('; ', $container_styles); ?>">
    <?php endif; ?>
    
        <!-- Map Container -->
        <div 
            id="<?php echo esc_attr($map_container_id); ?>" 
            class="hph-map-container"
            style="<?php echo implode('; ', $map_styles); ?>"
            data-hph-map
            data-lat="<?php echo esc_attr($latitude); ?>"
            data-lng="<?php echo esc_attr($longitude); ?>"
            data-zoom="<?php echo esc_attr($zoom); ?>"
            data-theme="<?php echo esc_attr($map_theme); ?>"
            data-interactive="<?php echo $interactive ? 'true' : 'false'; ?>"
            data-fit-bounds="<?php echo $fit_bounds ? 'true' : 'false'; ?>"
            data-show-controls="<?php echo $show_controls ? 'true' : 'false'; ?>"
            data-show-fullscreen="<?php echo $show_fullscreen ? 'true' : 'false'; ?>"
            <?php if (!empty($all_markers)): ?>
            data-markers="<?php echo esc_attr(json_encode($all_markers)); ?>"
            <?php endif; ?>
        >
            
            <?php if (!$mapbox_token): ?>
            <!-- No Token Error -->
            <div class="hph-map-error" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; padding: var(--hph-space-8); text-align: center; color: var(--hph-gray-600);">
                <div class="hph-map-error-icon" style="font-size: 3rem; color: var(--hph-warning); margin-bottom: var(--hph-space-4);">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="hph-map-error-message">
                    <h4 style="margin: 0 0 var(--hph-space-2) 0; font-size: var(--hph-text-lg); font-weight: 600; color: var(--hph-gray-900);">
                        Map Configuration Required
                    </h4>
                    <p style="margin: 0; font-size: var(--hph-text-sm);">
                        Mapbox access token is required to display the map.
                    </p>
                </div>
            </div>
            <?php else: ?>
            <!-- Loading State -->
            <div class="hph-map-loading" style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--hph-gray-500);">
                <div style="text-align: center;">
                    <i class="fas fa-spinner fa-spin" style="font-size: var(--hph-text-2xl); margin-bottom: var(--hph-space-2);"></i>
                    <p style="margin: 0; font-size: var(--hph-text-sm);">Loading map...</p>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    
    <?php if ($container !== 'full'): ?>
    </div>
    <?php endif; ?>
</section>

<?php if ($mapbox_token): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapContainer = document.getElementById('<?php echo esc_js($map_container_id); ?>');
    if (!mapContainer || typeof mapboxgl === 'undefined') {
        console.warn('Mapbox container or library not found');
        return;
    }
    
    // Set Mapbox access token
    mapboxgl.accessToken = '<?php echo esc_js($mapbox_token); ?>';
    
    // Get map configuration
    const lat = parseFloat(mapContainer.dataset.lat) || <?php echo floatval($latitude); ?>;
    const lng = parseFloat(mapContainer.dataset.lng) || <?php echo floatval($longitude); ?>;
    const zoom = parseInt(mapContainer.dataset.zoom) || <?php echo intval($zoom); ?>;
    const theme = mapContainer.dataset.theme || '<?php echo esc_js($map_theme); ?>';
    const interactive = mapContainer.dataset.interactive === 'true';
    const fitBounds = mapContainer.dataset.fitBounds === 'true';
    const showControls = mapContainer.dataset.showControls === 'true';
    const showFullscreen = mapContainer.dataset.showFullscreen === 'true';
    const markersData = JSON.parse(mapContainer.dataset.markers || '[]');
    
    // Hide loading state
    const loadingState = mapContainer.querySelector('.hph-map-loading');
    if (loadingState) {
        loadingState.style.display = 'none';
    }
    
    try {
        // Initialize map
        const map = new mapboxgl.Map({
            container: mapContainer,
            style: theme,
            center: [lng, lat],
            zoom: zoom,
            interactive: interactive
        });
        
        // Add controls
        if (showControls) {
            map.addControl(new mapboxgl.NavigationControl(), 'top-right');
        }
        
        if (showFullscreen) {
            map.addControl(new mapboxgl.FullscreenControl(), 'top-right');
        }
        
        // Add markers
        const bounds = new mapboxgl.LngLatBounds();
        const mapMarkers = [];
        
        markersData.forEach(function(markerData, index) {
            const markerLng = parseFloat(markerData.lng);
            const markerLat = parseFloat(markerData.lat);
            
            if (isNaN(markerLng) || isNaN(markerLat)) return;
            
            // Create marker element
            const markerEl = document.createElement('div');
            markerEl.className = 'hph-map-marker';
            
            // Style based on marker type
            if (markerData.type === 'office') {
                markerEl.innerHTML = `
                    <div class="hph-listing-marker">
                        <div class="hph-marker-content">
                            <div class="hph-marker-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="hph-marker-price">${markerData.title || 'Office'}</div>
                        </div>
                    </div>
                `;
            } else if (markerData.type === 'primary') {
                markerEl.innerHTML = `
                    <div class="hph-listing-marker hph-marker--active">
                        <div class="hph-marker-content">
                            <div class="hph-marker-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="hph-marker-price">${markerData.title || 'Location'}</div>
                        </div>
                    </div>
                `;
            } else {
                // POI marker style
                markerEl.innerHTML = `
                    <div class="hph-poi-marker">
                        <i class="fas fa-map-pin"></i>
                    </div>
                `;
            }
            
            // Create marker
            const marker = new mapboxgl.Marker(markerEl)
                .setLngLat([markerLng, markerLat])
                .addTo(map);
            
            mapMarkers.push(marker);
            bounds.extend([markerLng, markerLat]);
            
            // Add popup if description provided
            if (markerData.description) {
                const popup = new mapboxgl.Popup({ 
                    offset: 25,
                    className: 'hph-map-popup'
                }).setHTML(`
                    <div class="hph-popup-content">
                        <div class="hph-popup-details">
                            <h3 class="hph-popup-title">${markerData.title || 'Location'}</h3>
                            <div class="hph-popup-description">${markerData.description}</div>
                        </div>
                    </div>
                `);
                
                marker.setPopup(popup);
            }
        });
        
        // Fit bounds if multiple markers or fitBounds is true
        if (fitBounds && markersData.length > 1) {
            map.fitBounds(bounds, { 
                padding: 50,
                maxZoom: 15
            });
        }
        
        // Store map instance for external access
        mapContainer.hphMap = map;
        mapContainer.hphMarkers = mapMarkers;
        
    } catch (error) {
        console.error('Map initialization error:', error);
        
        // Show error state
        mapContainer.innerHTML = `
            <div class="hph-map-error" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; padding: var(--hph-space-8); text-align: center; color: var(--hph-gray-600);">
                <div class="hph-map-error-icon" style="font-size: 3rem; color: var(--hph-danger); margin-bottom: var(--hph-space-4);">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="hph-map-error-message">
                    <h4 style="margin: 0 0 var(--hph-space-2) 0; font-size: var(--hph-text-lg); font-weight: 600; color: var(--hph-gray-900);">
                        Map Loading Error
                    </h4>
                    <p style="margin: 0; font-size: var(--hph-text-sm);">
                        Unable to load the map. Please try refreshing the page.
                    </p>
                </div>
            </div>
        `;
    }
});
</script>
<?php endif; ?>

<style>
/* Ensure map popup styles are applied */
.mapboxgl-popup-content {
    padding: 0;
    border-radius: var(--hph-radius-lg, 12px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    border: none;
    max-width: 320px;
}

.hph-popup-content {
    overflow: hidden;
}

.hph-popup-details {
    padding: var(--hph-space-6, 20px);
}

.hph-popup-title {
    margin: 0 0 var(--hph-space-2, 8px) 0;
    font-size: var(--hph-text-lg, 18px);
    font-weight: 600;
    color: var(--hph-gray-900, #111827);
    line-height: 1.3;
}

.hph-popup-description {
    font-size: var(--hph-text-sm, 14px);
    color: var(--hph-gray-600, #6b7280);
    line-height: 1.5;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hph-map-container {
        height: 300px !important;
        border-radius: var(--hph-radius-md, 8px) !important;
    }
    
    .mapboxgl-popup-content {
        max-width: 280px;
    }
    
    .hph-popup-details {
        padding: var(--hph-space-4, 16px);
    }
}

@media (max-width: 480px) {
    .hph-map-container {
        height: 250px !important;
    }
    
    .mapboxgl-popup-content {
        max-width: 260px;
    }
}
</style>
