<?php
/**
 * Dashboard Map Component - Mapbox GL JS wrapper for interactive maps
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$map_args = wp_parse_args($args ?? [], [
    'map_id' => '',
    'title' => '',
    'subtitle' => '',
    'width' => '100%',
    'height' => '400px',
    'center' => [-74.5, 40], // [longitude, latitude]
    'zoom' => 9,
    'style' => 'mapbox://styles/mapbox/streets-v12',
    'markers' => [],
    'clustering' => false,
    'popup' => true,
    'navigation' => true,
    'fullscreen' => true,
    'geolocate' => false,
    'search' => false,
    'loading' => false,
    'error' => false,
    'error_message' => 'Unable to load map',
    'container_class' => '',
    'interactive' => true,
    'pitch' => 0,
    'bearing' => 0,
    'fit_bounds' => false, // Auto-fit to markers
    'draw_tools' => false,
    'custom_controls' => []
]);

// Generate unique map ID if not provided
if (empty($map_args['map_id'])) {
    $map_args['map_id'] = 'hph-map-' . uniqid();
}

$container_class = 'hph-map-container hph-relative hph-rounded hph-overflow-hidden';
if ($map_args['container_class']) {
    $container_class .= ' ' . $map_args['container_class'];
}

// Prepare markers data for JavaScript
$markers_json = json_encode($map_args['markers']);
$center_json = json_encode($map_args['center']);
?>

<div class="<?php echo esc_attr($container_class); ?>" style="width: <?php echo esc_attr($map_args['width']); ?>; height: <?php echo esc_attr($map_args['height']); ?>;">
    
    <?php if ($map_args['title'] || $map_args['subtitle']) : ?>
        <div class="hph-map-header hph-absolute hph-top-4 hph-left-4 hph-z-10 hph-bg-white hph-p-3 hph-rounded hph-shadow">
            <?php if ($map_args['title']) : ?>
                <h3 class="hph-map-title hph-font-medium hph-text-sm hph-mb-1">
                    <?php echo esc_html($map_args['title']); ?>
                </h3>
            <?php endif; ?>
            
            <?php if ($map_args['subtitle']) : ?>
                <p class="hph-map-subtitle hph-text-xs hph-text-muted hph-mb-0">
                    <?php echo esc_html($map_args['subtitle']); ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($map_args['search']) : ?>
        <div class="hph-map-search hph-absolute hph-top-4 hph-right-4 hph-z-10">
            <input type="text" 
                   id="<?php echo esc_attr($map_args['map_id']); ?>-search"
                   class="hph-form-control hph-form-control-sm"
                   placeholder="Search locations..."
                   style="width: 250px;">
        </div>
    <?php endif; ?>
    
    <?php if ($map_args['loading']) : ?>
        <!-- Loading State -->
        <div class="hph-map-loading hph-flex hph-items-center hph-justify-center hph-absolute hph-inset-0 hph-bg-gray-100">
            <div class="hph-text-center">
                <div class="hph-spinner hph-spinner-lg hph-text-primary hph-mb-2">
                    <i class="fas fa-circle-notch fa-spin"></i>
                </div>
                <p class="hph-text-muted hph-text-sm">Loading map...</p>
            </div>
        </div>
        
    <?php elseif ($map_args['error']) : ?>
        <!-- Error State -->
        <div class="hph-map-error hph-flex hph-items-center hph-justify-center hph-absolute hph-inset-0 hph-bg-light">
            <div class="hph-text-center hph-p-6">
                <div class="hph-error-icon hph-text-4xl hph-text-danger hph-mb-2">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <p class="hph-text-muted"><?php echo esc_html($map_args['error_message']); ?></p>
            </div>
        </div>
        
    <?php else : ?>
        <!-- Map Container -->
        <div id="<?php echo esc_attr($map_args['map_id']); ?>" 
             class="hph-map-canvas hph-w-full hph-h-full"
             data-map-center="<?php echo esc_attr($center_json); ?>"
             data-map-zoom="<?php echo esc_attr($map_args['zoom']); ?>"
             data-map-style="<?php echo esc_attr($map_args['style']); ?>"
             data-map-markers="<?php echo esc_attr($markers_json); ?>"
             data-map-interactive="<?php echo esc_attr($map_args['interactive'] ? 'true' : 'false'); ?>"
             data-map-clustering="<?php echo esc_attr($map_args['clustering'] ? 'true' : 'false'); ?>"
             data-map-popup="<?php echo esc_attr($map_args['popup'] ? 'true' : 'false'); ?>"
             data-map-fit-bounds="<?php echo esc_attr($map_args['fit_bounds'] ? 'true' : 'false'); ?>">
        </div>
    <?php endif; ?>
    
</div>

<?php if (!$map_args['loading'] && !$map_args['error']) : ?>
<script>
jQuery(document).ready(function($) {
    // Check if Mapbox GL JS is loaded and access token is available
    if (typeof mapboxgl === 'undefined') {
        console.error('Mapbox GL JS is not loaded');
        return;
    }
    
    if (!hphContext.hasMapbox || !hphContext.mapboxAccessToken) {
        console.error('Mapbox access token not configured');
        $('#<?php echo esc_js($map_args['map_id']); ?>').html(
            '<div class="hph-flex hph-items-center hph-justify-center hph-h-full hph-bg-light">' +
            '<p class="hph-text-muted">Mapbox access token required</p>' +
            '</div>'
        );
        return;
    }
    
    // Set access token
    mapboxgl.accessToken = hphContext.mapboxAccessToken;
    
    var mapId = '<?php echo esc_js($map_args['map_id']); ?>';
    var container = document.getElementById(mapId);
    
    if (!container) {
        console.error('Map container not found: ' + mapId);
        return;
    }
    
    // Map configuration
    var mapConfig = {
        container: mapId,
        style: '<?php echo esc_js($map_args['style']); ?>',
        center: <?php echo $center_json; ?>,
        zoom: <?php echo esc_js($map_args['zoom']); ?>,
        pitch: <?php echo esc_js($map_args['pitch']); ?>,
        bearing: <?php echo esc_js($map_args['bearing']); ?>,
        interactive: <?php echo $map_args['interactive'] ? 'true' : 'false'; ?>
    };
    
    // Create the map
    var map = new mapboxgl.Map(mapConfig);
    
    // Add navigation controls
    <?php if ($map_args['navigation']) : ?>
    map.addControl(new mapboxgl.NavigationControl(), 'top-right');
    <?php endif; ?>
    
    // Add fullscreen control
    <?php if ($map_args['fullscreen']) : ?>
    map.addControl(new mapboxgl.FullscreenControl(), 'top-right');
    <?php endif; ?>
    
    // Add geolocate control
    <?php if ($map_args['geolocate']) : ?>
    map.addControl(new mapboxgl.GeolocateControl({
        positionOptions: {
            enableHighAccuracy: true
        },
        trackUserLocation: true,
        showUserHeading: true
    }), 'top-right');
    <?php endif; ?>
    
    // Handle map load
    map.on('load', function() {
        var markers = <?php echo $markers_json; ?>;
        var bounds = new mapboxgl.LngLatBounds();
        var hasMarkers = false;
        
        // Add markers
        markers.forEach(function(marker, index) {
            if (!marker.coordinates || !Array.isArray(marker.coordinates)) {
                return;
            }
            
            hasMarkers = true;
            bounds.extend(marker.coordinates);
            
            // Create marker element
            var el = document.createElement('div');
            el.className = 'hph-map-marker';
            el.style.width = '30px';
            el.style.height = '30px';
            el.style.borderRadius = '50%';
            el.style.backgroundColor = marker.color || '#3b82f6';
            el.style.border = '2px solid white';
            el.style.boxShadow = '0 2px 4px rgba(0,0,0,0.3)';
            el.style.cursor = 'pointer';
            
            // Add icon if provided
            if (marker.icon) {
                el.innerHTML = '<i class="fas ' + marker.icon + '" style="color: white; font-size: 14px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i>';
            }
            
            // Create marker
            var mapboxMarker = new mapboxgl.Marker(el)
                .setLngLat(marker.coordinates)
                .addTo(map);
            
            // Add popup if enabled and content provided
            <?php if ($map_args['popup']) : ?>
            if (marker.popup || marker.title || marker.description) {
                var popupContent = '';
                
                if (marker.popup) {
                    popupContent = marker.popup;
                } else {
                    if (marker.title) {
                        popupContent += '<h4 class="hph-font-medium hph-mb-1">' + marker.title + '</h4>';
                    }
                    if (marker.description) {
                        popupContent += '<p class="hph-text-sm hph-text-muted hph-mb-0">' + marker.description + '</p>';
                    }
                    if (marker.price) {
                        popupContent += '<p class="hph-font-bold hph-text-primary hph-mt-1">' + marker.price + '</p>';
                    }
                }
                
                if (popupContent) {
                    var popup = new mapboxgl.Popup({
                        offset: 25,
                        closeButton: true,
                        closeOnClick: false
                    }).setHTML('<div class="hph-p-2">' + popupContent + '</div>');
                    
                    mapboxMarker.setPopup(popup);
                }
            }
            <?php endif; ?>
        });
        
        // Fit bounds to markers if enabled and markers exist
        <?php if ($map_args['fit_bounds']) : ?>
        if (hasMarkers) {
            map.fitBounds(bounds, {
                padding: 50,
                maxZoom: 15
            });
        }
        <?php endif; ?>
        
        // Trigger custom load event
        $(document).trigger('hph-map-loaded', [mapId, map]);
    });
    
    // Search functionality
    <?php if ($map_args['search']) : ?>
    var searchInput = $('#<?php echo esc_js($map_args['map_id']); ?>-search');
    var searchTimeout;
    
    searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        var query = $(this).val();
        
        if (query.length < 3) return;
        
        searchTimeout = setTimeout(function() {
            // Use Mapbox Geocoding API
            fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/' + encodeURIComponent(query) + '.json?access_token=' + mapboxgl.accessToken + '&limit=1')
                .then(response => response.json())
                .then(data => {
                    if (data.features && data.features.length > 0) {
                        var result = data.features[0];
                        map.flyTo({
                            center: result.center,
                            zoom: 14
                        });
                    }
                })
                .catch(error => console.error('Search error:', error));
        }, 500);
    });
    <?php endif; ?>
    
    // Store map instance for later access
    window.hphMaps = window.hphMaps || {};
    window.hphMaps[mapId] = map;
    
    // Expose update methods
    window['update_' + mapId.replace(/-/g, '_')] = function(newMarkers, newCenter, newZoom) {
        // This would be implemented to update markers dynamically
        console.log('Map update not yet implemented');
    };
    
    // Handle container resize
    var resizeObserver = new ResizeObserver(function() {
        map.resize();
    });
    resizeObserver.observe(container);
});
</script>
<?php endif; ?>

<?php
/**
 * Usage Examples:
 * 
 * Basic map:
 * get_template_part('template-parts/components/dashboard-map', '', [
 *     'title' => 'Property Locations',
 *     'center' => [-74.006, 40.7128], // NYC
 *     'zoom' => 12
 * ]);
 * 
 * Map with listings:
 * $markers = [
 *     [
 *         'coordinates' => [-74.006, 40.7128],
 *         'title' => '123 Main St',
 *         'description' => '3 bed, 2 bath',
 *         'price' => '$599,000',
 *         'color' => '#10b981'
 *     ],
 *     [
 *         'coordinates' => [-74.0059, 40.7127],
 *         'title' => '456 Oak Ave',
 *         'description' => '2 bed, 1 bath',
 *         'price' => '$449,000',
 *         'color' => '#3b82f6'
 *     ]
 * ];
 * 
 * get_template_part('template-parts/components/dashboard-map', '', [
 *     'title' => 'Available Properties',
 *     'markers' => $markers,
 *     'fit_bounds' => true,
 *     'search' => true,
 *     'clustering' => true
 * ]);
 * 
 * Interactive map with tools:
 * get_template_part('template-parts/components/dashboard-map', '', [
 *     'title' => 'Market Analysis',
 *     'style' => 'mapbox://styles/mapbox/satellite-streets-v12',
 *     'navigation' => true,
 *     'fullscreen' => true,
 *     'geolocate' => true,
 *     'draw_tools' => true
 * ]);
 */
?>