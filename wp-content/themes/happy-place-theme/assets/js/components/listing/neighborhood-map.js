/**
 * Neighborhood Map Component
 * File: assets/js/components/listing/neighborhood-map.js
 * 
 * Handles neighborhood-focused map functionality with city and local places integration
 * Depends on Mapbox GL JS loaded by Happy Place Plugin
 */

(function($) {
    'use strict';

    /**
     * Neighborhood Map Handler
     */
    window.NeighborhoodMap = {
        maps: {},
        currentMapStyle: {},
        propertyMarkers: {},
        
        /**
         * Initialize all neighborhood maps on the page
         */
        init: function() {
            $('.hph-neighborhood-map[data-lat][data-lng]').each(function() {
                const mapId = $(this).attr('id');
                if (mapId) {
                    NeighborhoodMap.initMap(mapId);
                }
            });
        },
        
        /**
         * Initialize a single neighborhood map
         * @param {string} mapId - The map container ID
         */
        initMap: function(mapId) {
            const mapContainer = document.getElementById(mapId);
            if (!mapContainer || NeighborhoodMap.maps[mapId]) {
                return; // Already initialized or container not found
            }
            
            const lat = parseFloat(mapContainer.dataset.lat);
            const lng = parseFloat(mapContainer.dataset.lng);
            const address = mapContainer.dataset.address || '';
            
            if (!lat || !lng) {
                console.warn('NeighborhoodMap: Invalid coordinates for map', mapId);
                return;
            }
            
            // Initialize Mapbox using plugin configuration
            if (typeof hph_mapbox_config !== 'undefined' && hph_mapbox_config.access_token) {
                mapboxgl.accessToken = hph_mapbox_config.access_token;
            } else if (typeof window.HP_MAPBOX_ACCESS_TOKEN !== 'undefined') {
                mapboxgl.accessToken = window.HP_MAPBOX_ACCESS_TOKEN;
            } else {
                console.error('NeighborhoodMap: No Mapbox access token available');
                NeighborhoodMap.showError(mapContainer, 'Map configuration error');
                return;
            }
            
            try {
                // Remove loading indicator
                mapContainer.innerHTML = '';
                
                // Create map with neighborhood focus (broader zoom level)
                const map = new mapboxgl.Map({
                    container: mapId,
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [lng, lat],
                    zoom: 13, // Broader view for neighborhood context
                    pitch: 0,
                    bearing: 0,
                    antialias: true
                });
                
                // Store map instance
                NeighborhoodMap.maps[mapId] = map;
                NeighborhoodMap.currentMapStyle[mapId] = 'streets';
                
                // Add navigation controls
                map.addControl(new mapboxgl.NavigationControl({
                    showCompass: false
                }), 'top-right');
                
                // Create and add property marker
                NeighborhoodMap.addPropertyMarker(map, mapId, lng, lat, address);
                
                // Map loaded successfully
                console.log('NeighborhoodMap: Initialized map', mapId);
                
            } catch (error) {
                console.error('NeighborhoodMap: Error initializing map', mapId, error);
                NeighborhoodMap.showError(mapContainer, 'Unable to load neighborhood map');
            }
        },
        
        /**
         * Add property marker to map
         * @param {mapboxgl.Map} map - Mapbox map instance
         * @param {string} mapId - Map container ID
         * @param {number} lng - Longitude
         * @param {number} lat - Latitude
         * @param {string} address - Property address
         */
        addPropertyMarker: function(map, mapId, lng, lat, address) {
            // Create custom property marker element
            const markerEl = document.createElement('div');
            markerEl.className = 'hph-property-marker';
            markerEl.innerHTML = '<i class="fas fa-home"></i>';
            
            // Create marker
            const propertyMarker = new mapboxgl.Marker({
                element: markerEl,
                anchor: 'bottom'
            })
            .setLngLat([lng, lat])
            .addTo(map);
            
            // Store marker reference
            NeighborhoodMap.propertyMarkers[mapId] = propertyMarker;
            
            // Add popup to marker
            const popup = new mapboxgl.Popup({
                offset: 25,
                closeButton: false,
                className: 'hph-property-popup'
            })
            .setHTML(`
                <div class="hph-popup-content">
                    <strong>Your Property</strong>
                    <p>${address}</p>
                </div>
            `);
            
            propertyMarker.setPopup(popup);
            
            // Show popup after map loads
            map.on('load', function() {
                setTimeout(() => {
                    popup.addTo(map);
                }, 1000);
            });
        },
        
        /**
         * Show error message in map container
         * @param {HTMLElement} container - Map container element
         * @param {string} message - Error message
         */
        showError: function(container, message) {
            container.innerHTML = `
                <div class="hph-map-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${message}</p>
                </div>
            `;
        },
        
        /**
         * Toggle map style between streets and satellite
         * @param {string} mapId - Map container ID
         */
        toggleMapStyle: function(mapId) {
            const map = NeighborhoodMap.maps[mapId];
            if (!map) return;
            
            const currentStyle = NeighborhoodMap.currentMapStyle[mapId];
            const newStyle = currentStyle === 'streets' ? 'satellite' : 'streets';
            
            if (newStyle === 'satellite') {
                map.setStyle('mapbox://styles/mapbox/satellite-streets-v12');
            } else {
                map.setStyle('mapbox://styles/mapbox/streets-v12');
            }
            
            NeighborhoodMap.currentMapStyle[mapId] = newStyle;
        },
        
        /**
         * Center map on property
         * @param {string} mapId - Map container ID
         */
        centerOnProperty: function(mapId) {
            const map = NeighborhoodMap.maps[mapId];
            const marker = NeighborhoodMap.propertyMarkers[mapId];
            
            if (!map || !marker) return;
            
            map.flyTo({
                center: marker.getLngLat(),
                zoom: 15,
                duration: 1500
            });
        },
        
        /**
         * Search nearby places (opens in Google Maps)
         * @param {string} type - Search type
         * @param {number} lat - Latitude
         * @param {number} lng - Longitude
         */
        searchNearby: function(type, lat, lng) {
            const searches = {
                restaurants: 'restaurants',
                shopping: 'shopping+centers',
                schools: 'schools',
                parks: 'parks+recreation',
                medical: 'hospitals+medical+centers',
                grocery: 'grocery+stores'
            };
            
            const query = searches[type] || type;
            const url = `https://www.google.com/maps/search/${query}/@${lat},${lng},14z`;
            window.open(url, '_blank');
        },
        
        /**
         * Copy address to clipboard
         * @param {string} address - Address to copy
         */
        copyAddress: function(address) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(address).then(() => {
                    NeighborhoodMap.showCopySuccess();
                }).catch(err => {
                    console.error('Failed to copy address:', err);
                    NeighborhoodMap.fallbackCopyAddress(address);
                });
            } else {
                NeighborhoodMap.fallbackCopyAddress(address);
            }
        },
        
        /**
         * Show copy success feedback
         */
        showCopySuccess: function() {
            const btn = event.currentTarget;
            if (!btn) return;
            
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i><span>Copied!</span>';
            btn.classList.add('hph-copy-success');
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('hph-copy-success');
            }, 2000);
        },
        
        /**
         * Fallback copy method for older browsers
         * @param {string} address - Address to copy
         */
        fallbackCopyAddress: function(address) {
            const textArea = document.createElement('textarea');
            textArea.value = address;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                NeighborhoodMap.showCopySuccess();
            } catch (err) {
                console.error('Fallback copy failed:', err);
            }
            
            document.body.removeChild(textArea);
        },
        
        /**
         * Open Google Street View
         * @param {number} lat - Latitude
         * @param {number} lng - Longitude
         */
        openStreetView: function(lat, lng) {
            const url = `https://www.google.com/maps/@${lat},${lng},3a,75y,90t/data=!3m6!1e1!3m4!1s!2e0!7i16384!8i8192`;
            window.open(url, '_blank');
        },
        
        /**
         * Toggle fullscreen for map container
         * @param {string} mapId - Map container ID
         */
        toggleFullscreen: function(mapId) {
            const mapWrapper = document.querySelector(`#${mapId}`).closest('.hph-map-container-wrapper');
            
            if (!mapWrapper) return;
            
            if (!document.fullscreenElement) {
                mapWrapper.requestFullscreen().catch(err => {
                    console.error('Error attempting to enable fullscreen:', err);
                });
            } else {
                document.exitFullscreen();
            }
        }
    };
    
    /**
     * Global functions for template compatibility
     */
    window.toggleMapStyle = function(mapId) {
        NeighborhoodMap.toggleMapStyle(mapId);
    };
    
    window.centerOnProperty = function(mapId) {
        NeighborhoodMap.centerOnProperty(mapId);
    };
    
    window.searchNearby = function(type, lat, lng) {
        NeighborhoodMap.searchNearby(type, lat, lng);
    };
    
    window.copyAddress = function(address) {
        NeighborhoodMap.copyAddress(address);
    };
    
    window.openStreetView = function(lat, lng) {
        NeighborhoodMap.openStreetView(lat, lng);
    };
    
    window.toggleFullscreen = function(mapId) {
        NeighborhoodMap.toggleFullscreen(mapId);
    };
    
    /**
     * Initialize when DOM is ready
     */
    $(document).ready(function() {
        // Wait for Mapbox to be available
        if (typeof mapboxgl !== 'undefined') {
            NeighborhoodMap.init();
        } else {
            // Wait for Mapbox to load (up to 5 seconds)
            let attempts = 0;
            const checkMapbox = setInterval(function() {
                attempts++;
                if (typeof mapboxgl !== 'undefined') {
                    clearInterval(checkMapbox);
                    NeighborhoodMap.init();
                } else if (attempts > 50) { // 5 seconds
                    clearInterval(checkMapbox);
                    console.error('NeighborhoodMap: Mapbox GL JS not loaded after 5 seconds');
                }
            }, 100);
        }
    });

})(jQuery);
