<?php
/**
 * Base Card Map Component - Map layout with cards as popups/sidebar
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$map_args = wp_parse_args($args ?? [], [
    'posts' => [], // Array of post objects or post IDs
    'post_type' => 'post',
    'card_args' => [], // Arguments to pass to each card
    'query_args' => [], // WP_Query args if posts not provided
    'layout' => 'sidebar', // sidebar, overlay, popup-only
    'sidebar_position' => 'left', // left, right (when layout is sidebar)
    'sidebar_width' => '33.333333%', // 1/3 default
    'map_height' => '600px',
    'map_style' => 'mapbox://styles/mapbox/streets-v12',
    'center' => null, // Auto-calculate if not provided
    'zoom' => 12,
    'clustering' => true,
    'search_form' => false,
    'filter_controls' => false,
    'container_class' => '',
    'map_class' => '',
    'sidebar_class' => '',
    'marker_color' => '#3b82f6',
    'active_marker_color' => '#ef4444',
    'show_navigation' => true,
    'show_fullscreen' => true,
    'auto_fit_bounds' => true,
    'empty_message' => 'No items found on map.',
    'loading' => false
]);

// Get posts if not provided
$posts = $map_args['posts'];
$query = null;

if (empty($posts) && !empty($map_args['query_args'])) {
    $query_args = wp_parse_args($map_args['query_args'], [
        'post_type' => $map_args['post_type'],
        'post_status' => 'publish',
        'posts_per_page' => 50 // Reasonable limit for map display
    ]);
    
    $query = new WP_Query($query_args);
    $posts = $query->posts;
}

// Convert post IDs to post objects if needed
if (!empty($posts) && is_numeric($posts[0])) {
    $posts = array_map('get_post', $posts);
}

// Prepare map markers
$markers = [];
$bounds = ['min_lat' => null, 'max_lat' => null, 'min_lng' => null, 'max_lng' => null];

foreach ($posts as $post) {
    $coordinates = null;
    $marker_data = [];
    
    // Get coordinates based on post type
    switch ($post->post_type) {
        case 'listing':
            if (function_exists('hpt_get_listing')) {
                $listing_data = hpt_get_listing($post->ID);
                if (!empty($listing_data['coordinates'])) {
                    $coordinates = $listing_data['coordinates'];
                    $marker_data = [
                        'title' => $listing_data['address'] ?? get_the_title($post->ID),
                        'price' => $listing_data['price_formatted'] ?? '',
                        'image' => $listing_data['featured_image'] ?? '',
                        'bedrooms' => $listing_data['bedrooms'] ?? '',
                        'bathrooms' => $listing_data['bathrooms'] ?? '',
                        'sqft' => $listing_data['square_feet'] ?? ''
                    ];
                }
            }
            break;
            
        case 'agent':
            // Agents might have office locations
            $office_lat = get_post_meta($post->ID, 'office_latitude', true);
            $office_lng = get_post_meta($post->ID, 'office_longitude', true);
            if ($office_lat && $office_lng) {
                $coordinates = [(float)$office_lng, (float)$office_lat];
                if (function_exists('hpt_get_agent')) {
                    $agent_data = hpt_get_agent($post->ID);
                    $marker_data = [
                        'title' => $agent_data['name'] ?? get_the_title($post->ID),
                        'subtitle' => $agent_data['title'] ?? '',
                        'phone' => $agent_data['phone'] ?? '',
                        'image' => $agent_data['profile_photo']['url'] ?? ''
                    ];
                }
            }
            break;
            
        case 'open_house':
            // Open houses use listing location
            $listing_id = get_post_meta($post->ID, 'listing_id', true);
            if ($listing_id && function_exists('hpt_get_listing')) {
                $listing_data = hpt_get_listing($listing_id);
                if (!empty($listing_data['coordinates'])) {
                    $coordinates = $listing_data['coordinates'];
                    $marker_data = [
                        'title' => get_the_title($post->ID),
                        'address' => $listing_data['address'] ?? '',
                        'date' => get_post_meta($post->ID, 'start_date', true),
                        'time' => get_post_meta($post->ID, 'start_time', true)
                    ];
                }
            }
            break;
            
        default:
            // Try generic coordinates
            $lat = get_post_meta($post->ID, 'latitude', true);
            $lng = get_post_meta($post->ID, 'longitude', true);
            if ($lat && $lng) {
                $coordinates = [(float)$lng, (float)$lat];
                $marker_data = [
                    'title' => get_the_title($post->ID),
                    'excerpt' => get_the_excerpt($post->ID)
                ];
            }
            break;
    }
    
    if ($coordinates) {
        $markers[] = [
            'post_id' => $post->ID,
            'coordinates' => $coordinates,
            'color' => $map_args['marker_color'],
            'data' => $marker_data
        ];
        
        // Update bounds
        $lat = $coordinates[1];
        $lng = $coordinates[0];
        
        $bounds['min_lat'] = $bounds['min_lat'] === null ? $lat : min($bounds['min_lat'], $lat);
        $bounds['max_lat'] = $bounds['max_lat'] === null ? $lat : max($bounds['max_lat'], $lat);
        $bounds['min_lng'] = $bounds['min_lng'] === null ? $lng : min($bounds['min_lng'], $lng);
        $bounds['max_lng'] = $bounds['max_lng'] === null ? $lng : max($bounds['max_lng'], $lng);
    }
}

// Calculate center if not provided
if (!$map_args['center'] && !empty($bounds['min_lat'])) {
    $map_args['center'] = [
        ($bounds['min_lng'] + $bounds['max_lng']) / 2,
        ($bounds['min_lat'] + $bounds['max_lat']) / 2
    ];
} elseif (!$map_args['center']) {
    $map_args['center'] = [-74.006, 40.7128]; // Default to NYC
}

$unique_id = 'hph-map-' . uniqid();
$sidebar_style = '';
$map_style = '';

if ($map_args['layout'] === 'sidebar') {
    if ($map_args['sidebar_position'] === 'left') {
        $sidebar_style = 'width: ' . $map_args['sidebar_width'] . ';';
        $map_style = 'width: calc(100% - ' . $map_args['sidebar_width'] . ');';
    } else {
        $map_style = 'width: calc(100% - ' . $map_args['sidebar_width'] . ');';
        $sidebar_style = 'width: ' . $map_args['sidebar_width'] . ';';
    }
}
?>

<div class="hph-card-map-container <?php echo esc_attr($map_args['container_class']); ?>" id="<?php echo esc_attr($unique_id); ?>">
    
    <!-- Map Controls -->
    <?php if ($map_args['search_form'] || $map_args['filter_controls']) : ?>
        <div class="hph-map-controls hph-mb-6 hph-flex hph-flex-wrap hph-items-center hph-justify-between hph-gap-4">
            
            <!-- Search Form -->
            <?php if ($map_args['search_form']) : ?>
                <div class="hph-map-search hph-flex-1 hph-max-w-md">
                    <div class="hph-relative">
                        <input type="text" 
                               class="hph-form-control hph-pl-8" 
                               placeholder="Search location..."
                               id="<?php echo esc_attr($unique_id); ?>-search">
                        <i class="fas fa-search hph-absolute hph-left-2 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-text-muted"></i>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Filter Controls -->
            <?php if ($map_args['filter_controls']) : ?>
                <div class="hph-map-filters">
                    <div class="hph-btn-group" role="group">
                        <button class="hph-btn hph-btn-outline hph-btn-sm hph-map-filter-btn active" data-filter="*">
                            All (<?php echo count($markers); ?>)
                        </button>
                        
                        <?php if ($map_args['post_type'] === 'listing') : ?>
                            <?php
                            $status_counts = [];
                            foreach ($posts as $post) {
                                $status = get_post_meta($post->ID, 'listing_status', true) ?: 'active';
                                $status_counts[$status] = ($status_counts[$status] ?? 0) + 1;
                            }
                            
                            foreach ($status_counts as $status => $count) : ?>
                                <button class="hph-btn hph-btn-outline hph-btn-sm hph-map-filter-btn" data-filter="<?php echo esc_attr($status); ?>">
                                    <?php echo esc_html(ucfirst($status)); ?> (<?php echo $count; ?>)
                                </button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    <?php endif; ?>
    
    <!-- Loading State -->
    <?php if ($map_args['loading']) : ?>
        <div class="hph-map-loading hph-flex hph-items-center hph-justify-center hph-bg-gray-100 hph-rounded" 
             style="height: <?php echo esc_attr($map_args['map_height']); ?>;">
            <div class="hph-text-center">
                <div class="hph-spinner hph-spinner-lg hph-text-primary hph-mb-4">
                    <i class="fas fa-circle-notch fa-spin"></i>
                </div>
                <p class="hph-text-muted">Loading map...</p>
            </div>
        </div>
        
    <!-- Empty State -->
    <?php elseif (empty($markers)) : ?>
        <div class="hph-map-empty hph-flex hph-items-center hph-justify-center hph-bg-gray-100 hph-rounded" 
             style="height: <?php echo esc_attr($map_args['map_height']); ?>;">
            <div class="hph-text-center hph-p-8">
                <div class="hph-empty-icon hph-text-6xl hph-text-muted hph-mb-4">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3 class="hph-text-lg hph-font-medium hph-mb-2">No Locations Found</h3>
                <p class="hph-text-muted"><?php echo esc_html($map_args['empty_message']); ?></p>
            </div>
        </div>
        
    <!-- Map Layout -->
    <?php else : ?>
        <div class="hph-map-layout hph-flex hph-rounded hph-overflow-hidden hph-bg-white hph-shadow" 
             style="height: <?php echo esc_attr($map_args['map_height']); ?>;">
            
            <?php if ($map_args['layout'] === 'sidebar' && $map_args['sidebar_position'] === 'left') : ?>
                <!-- Left Sidebar -->
                <div class="hph-map-sidebar hph-map-sidebar-left <?php echo esc_attr($map_args['sidebar_class']); ?>" 
                     style="<?php echo esc_attr($sidebar_style); ?>">
                    <div class="hph-sidebar-content hph-h-full hph-overflow-y-auto hph-p-4 hph-space-y-4">
                        <?php foreach ($posts as $post) : ?>
                            <?php
                            $card_arguments = array_merge($map_args['card_args'], [
                                'post_id' => $post->ID,
                                'post_type' => $post->post_type,
                                'layout' => 'compact',
                                'size' => 'small',
                                'hover_effect' => 'none',
                                'container_class' => 'hph-map-card',
                                'data_attributes' => ['post-id' => $post->ID]
                            ]);
                            ?>
                            <?php hph_component('card', $card_arguments); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Map Container -->
            <div class="hph-map-canvas <?php echo esc_attr($map_args['map_class']); ?>" 
                 style="<?php echo esc_attr($map_style); ?>"
                 id="<?php echo esc_attr($unique_id); ?>-map"
                 data-markers="<?php echo esc_attr(json_encode($markers)); ?>"
                 data-center="<?php echo esc_attr(json_encode($map_args['center'])); ?>"
                 data-zoom="<?php echo esc_attr($map_args['zoom']); ?>"
                 data-style="<?php echo esc_attr($map_args['map_style']); ?>"
                 data-clustering="<?php echo esc_attr($map_args['clustering'] ? 'true' : 'false'); ?>">
            </div>
            
            <?php if ($map_args['layout'] === 'sidebar' && $map_args['sidebar_position'] === 'right') : ?>
                <!-- Right Sidebar -->
                <div class="hph-map-sidebar hph-map-sidebar-right <?php echo esc_attr($map_args['sidebar_class']); ?>" 
                     style="<?php echo esc_attr($sidebar_style); ?>">
                    <div class="hph-sidebar-content hph-h-full hph-overflow-y-auto hph-p-4 hph-space-y-4">
                        <?php foreach ($posts as $post) : ?>
                            <?php
                            $card_arguments = array_merge($map_args['card_args'], [
                                'post_id' => $post->ID,
                                'post_type' => $post->post_type,
                                'layout' => 'compact',
                                'size' => 'small',
                                'hover_effect' => 'none',
                                'container_class' => 'hph-map-card',
                                'data_attributes' => ['post-id' => $post->ID]
                            ]);
                            ?>
                            <?php hph_component('card', $card_arguments); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
        
        <!-- Overlay Cards (for overlay layout) -->
        <?php if ($map_args['layout'] === 'overlay') : ?>
            <div class="hph-map-overlay-cards hph-absolute hph-bottom-4 hph-left-4 hph-right-4 hph-z-10">
                <div class="hph-flex hph-gap-4 hph-overflow-x-auto hph-pb-2">
                    <?php foreach (array_slice($posts, 0, 3) as $post) : ?>
                        <?php
                        $card_arguments = array_merge($map_args['card_args'], [
                            'post_id' => $post->ID,
                            'post_type' => $post->post_type,
                            'layout' => 'compact',
                            'size' => 'small',
                            'container_class' => 'hph-flex-shrink-0 hph-w-64 hph-map-overlay-card',
                            'data_attributes' => ['post-id' => $post->ID]
                        ]);
                        ?>
                        <?php hph_component('card', $card_arguments); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
    
</div>

<?php if (!$map_args['loading'] && !empty($markers)) : ?>
<script>
jQuery(document).ready(function($) {
    // Check if Mapbox GL JS is loaded
    if (typeof mapboxgl === 'undefined' || !hphContext.hasMapbox) {
        console.error('Mapbox GL JS not available');
        return;
    }
    
    mapboxgl.accessToken = hphContext.mapboxAccessToken;
    
    var mapId = '<?php echo esc_js($unique_id); ?>-map';
    var markers = <?php echo json_encode($markers); ?>;
    var center = <?php echo json_encode($map_args['center']); ?>;
    
    // Create map
    var map = new mapboxgl.Map({
        container: mapId,
        style: '<?php echo esc_js($map_args['map_style']); ?>',
        center: center,
        zoom: <?php echo esc_js($map_args['zoom']); ?>
    });
    
    // Add controls
    <?php if ($map_args['show_navigation']) : ?>
    map.addControl(new mapboxgl.NavigationControl(), 'top-right');
    <?php endif; ?>
    
    <?php if ($map_args['show_fullscreen']) : ?>
    map.addControl(new mapboxgl.FullscreenControl(), 'top-right');
    <?php endif; ?>
    
    // Store marker instances
    var mapMarkers = [];
    var activeMarker = null;
    
    map.on('load', function() {
        // Add markers
        markers.forEach(function(markerData, index) {
            // Create marker element
            var el = document.createElement('div');
            el.className = 'hph-map-marker';
            el.style.width = '30px';
            el.style.height = '30px';
            el.style.borderRadius = '50%';
            el.style.backgroundColor = markerData.color || '<?php echo esc_js($map_args['marker_color']); ?>';
            el.style.border = '2px solid white';
            el.style.boxShadow = '0 2px 4px rgba(0,0,0,0.3)';
            el.style.cursor = 'pointer';
            el.dataset.postId = markerData.post_id;
            
            // Create popup content
            var popupContent = '<div class="hph-map-popup hph-p-3">';
            
            if (markerData.data.image) {
                popupContent += '<img src="' + markerData.data.image + '" class="hph-w-full hph-h-32 hph-object-cover hph-rounded hph-mb-2">';
            }
            
            popupContent += '<h4 class="hph-font-medium hph-mb-1">' + markerData.data.title + '</h4>';
            
            if (markerData.data.price) {
                popupContent += '<p class="hph-text-primary hph-font-bold hph-mb-1">' + markerData.data.price + '</p>';
            }
            
            if (markerData.data.bedrooms || markerData.data.bathrooms) {
                popupContent += '<div class="hph-flex hph-gap-2 hph-text-sm hph-text-muted">';
                if (markerData.data.bedrooms) popupContent += '<span>' + markerData.data.bedrooms + ' bed</span>';
                if (markerData.data.bathrooms) popupContent += '<span>' + markerData.data.bathrooms + ' bath</span>';
                popupContent += '</div>';
            }
            
            popupContent += '</div>';
            
            // Create popup
            var popup = new mapboxgl.Popup({
                offset: 25,
                closeButton: true
            }).setHTML(popupContent);
            
            // Create marker
            var marker = new mapboxgl.Marker(el)
                .setLngLat(markerData.coordinates)
                .setPopup(popup)
                .addTo(map);
            
            mapMarkers.push({
                marker: marker,
                element: el,
                data: markerData
            });
            
            // Add click handler for marker highlighting
            el.addEventListener('click', function() {
                highlightMarker(markerData.post_id);
                highlightCard(markerData.post_id);
            });
        });
        
        // Auto-fit bounds if enabled
        <?php if ($map_args['auto_fit_bounds'] && !empty($bounds['min_lat'])) : ?>
        var bounds = new mapboxgl.LngLatBounds();
        markers.forEach(function(markerData) {
            bounds.extend(markerData.coordinates);
        });
        map.fitBounds(bounds, { padding: 50 });
        <?php endif; ?>
    });
    
    // Highlight marker function
    function highlightMarker(postId) {
        mapMarkers.forEach(function(item) {
            if (item.data.post_id == postId) {
                item.element.style.backgroundColor = '<?php echo esc_js($map_args['active_marker_color']); ?>';
                item.element.style.transform = 'scale(1.2)';
                activeMarker = item;
            } else {
                item.element.style.backgroundColor = item.data.color || '<?php echo esc_js($map_args['marker_color']); ?>';
                item.element.style.transform = 'scale(1)';
            }
        });
    }
    
    // Highlight card function
    function highlightCard(postId) {
        $('.hph-map-card').removeClass('hph-ring-2 hph-ring-primary');
        $('.hph-map-card[data-post-id="' + postId + '"]').addClass('hph-ring-2 hph-ring-primary');
    }
    
    // Card hover handlers
    $(document).on('mouseenter', '.hph-map-card', function() {
        var postId = $(this).data('post-id');
        highlightMarker(postId);
    });
    
    $(document).on('mouseleave', '.hph-map-card', function() {
        if (activeMarker) {
            activeMarker.element.style.backgroundColor = activeMarker.data.color || '<?php echo esc_js($map_args['marker_color']); ?>';
            activeMarker.element.style.transform = 'scale(1)';
        }
    });
    
    // Card click handlers
    $(document).on('click', '.hph-map-card', function() {
        var postId = $(this).data('post-id');
        var markerItem = mapMarkers.find(item => item.data.post_id == postId);
        
        if (markerItem) {
            map.flyTo({
                center: markerItem.data.coordinates,
                zoom: Math.max(map.getZoom(), 15)
            });
            
            setTimeout(function() {
                markerItem.marker.togglePopup();
            }, 500);
        }
    });
    
    // Filter functionality
    <?php if ($map_args['filter_controls']) : ?>
    $('.hph-map-filter-btn').on('click', function() {
        $('.hph-map-filter-btn').removeClass('active');
        $(this).addClass('active');
        
        var filter = $(this).data('filter');
        
        mapMarkers.forEach(function(item) {
            var show = filter === '*' || filter === 'all';
            
            if (!show && filter) {
                // Check post meta for filter criteria
                // This would need to be implemented based on specific filtering needs
                show = true; // Placeholder
            }
            
            if (show) {
                item.marker.getElement().style.display = 'block';
            } else {
                item.marker.getElement().style.display = 'none';
            }
        });
        
        // Filter sidebar cards
        if (filter === '*') {
            $('.hph-map-card').show();
        } else {
            $('.hph-map-card').hide();
            $('.hph-map-card.' + filter).show();
        }
    });
    <?php endif; ?>
    
    // Search functionality
    <?php if ($map_args['search_form']) : ?>
    $('#<?php echo esc_js($unique_id); ?>-search').on('keyup', function(e) {
        if (e.key === 'Enter') {
            var query = $(this).val();
            
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
                .catch(error => console.error('Geocoding error:', error));
        }
    });
    <?php endif; ?>
});
</script>
<?php endif; ?>