/**
 * HPH Listing Map Component JavaScript - Updated for Mapbox
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Create global namespace for map functionality
window.HPHListingMap = window.HPHListingMap || {};

// Load HPH Map component first
if (typeof HPHMap === 'undefined') {
    console.error('Listing Map: HPHMap component not found. Loading fallback.');
}

(function($) {
    'use strict';

    // Map variables
    let map;
    let markers = [];
    let propertyMarker;

    // Component object
    window.HPHListingMap = {
        
        /**
         * Initialize the map component
         */
        init: function() {
            this.setupEventListeners();
            this.initializeTabs();
            this.initializeMapControls();
        },

        /**
         * Setup event listeners
         */
        setupEventListeners: function() {
            // Tab switching
            $(document).on('click', '.hph-tab', this.handleTabSwitch);
            
            // Map view switching
            $(document).on('click', '.hph-map-btn', this.handleMapViewSwitch);
            
            // Category filters
            $(document).on('click', '.hph-filter-btn', this.handleCategoryFilter);
            
            // Map control buttons
            $(document).on('click', '.hph-map-zoom-in', this.zoomIn);
            $(document).on('click', '.hph-map-zoom-out', this.zoomOut);
            $(document).on('click', '.hph-map-fullscreen', this.toggleFullscreen);
            
            // Directions buttons
            $(document).on('click', '.hph-place-directions', this.getDirections);
        },

        /**
         * Initialize tabs functionality
         */
        initializeTabs: function() {
            $('.hph-tab').first().addClass('hph-tab-active');
            $('.hph-tab-pane').first().addClass('hph-tab-pane-active');
        },

        /**
         * Initialize map controls
         */
        initializeMapControls: function() {
            $('.hph-map-btn').first().addClass('hph-map-btn-active');
            $('.hph-filter-btn').first().addClass('hph-filter-active');
        },

        /**
         * Handle tab switching
         */
        handleTabSwitch: function(e) {
            e.preventDefault();
            const $tab = $(this);
            const targetTab = $tab.data('tab');
            
            // Update active states
            $('.hph-tab').removeClass('hph-tab-active');
            $('.hph-tab-pane').removeClass('hph-tab-pane-active');
            
            $tab.addClass('hph-tab-active');
            $(`[data-content="${targetTab}"]`).addClass('hph-tab-pane-active');
        },

        /**
         * Handle map view switching
         */
        handleMapViewSwitch: function(e) {
            e.preventDefault();
            const $btn = $(this);
            const view = $btn.data('view');
            
            $('.hph-map-btn').removeClass('hph-map-btn-active');
            $btn.addClass('hph-map-btn-active');
            
            if (typeof map !== 'undefined' && map) {
                switch(view) {
                    case 'satellite':
                        map.setMapTypeId('satellite');
                        break;
                    case 'street':
                        window.HPHListingMap.getStreetView();
                        break;
                    default:
                        map.setMapTypeId('roadmap');
                }
            }
        },

        /**
         * Handle category filtering
         */
        handleCategoryFilter: function(e) {
            e.preventDefault();
            const $btn = $(this);
            const category = $btn.data('category');
            
            $('.hph-filter-btn').removeClass('hph-filter-active');
            $btn.addClass('hph-filter-active');
            
            // Filter markers based on category
            // Implementation would filter map markers
            console.log('Filtering by category:', category);
        },

        /**
         * Zoom in on map
         */
        zoomIn: function(e) {
            e.preventDefault();
            if (typeof map !== 'undefined' && map) {
                map.setZoom(map.getZoom() + 1);
            }
        },

        /**
         * Zoom out on map
         */
        zoomOut: function(e) {
            e.preventDefault();
            if (typeof map !== 'undefined' && map) {
                map.setZoom(map.getZoom() - 1);
            }
        },

        /**
         * Toggle fullscreen mode
         */
        toggleFullscreen: function(e) {
            e.preventDefault();
            const mapElement = document.querySelector('.hph-map-wrapper');
            if (!document.fullscreenElement) {
                mapElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        },

        /**
         * Get street view for property
         */
        getStreetView: function() {
            if (typeof map !== 'undefined' && map && window.hphMapContext) {
                const panorama = map.getStreetView();
                panorama.setPosition({
                    lat: window.hphMapContext.coordinates.lat,
                    lng: window.hphMapContext.coordinates.lng
                });
                panorama.setVisible(true);
            }
        },

        /**
         * Get directions to a place
         */
        getDirections: function(e) {
            e.preventDefault();
            const destination = $(this).closest('.hph-place-item').find('.hph-place-name').text();
            const address = window.hphMapContext ? window.hphMapContext.address : '';
            
            if (address && destination) {
                const url = `https://www.google.com/maps/dir/?api=1&origin=${encodeURIComponent(address)}&destination=${encodeURIComponent(destination)}`;
                window.open(url, '_blank');
            }
        },

        /**
         * Initialize Google Map
         */
        initGoogleMap: function(config) {
            if (!config || !config.lat || !config.lng) {
                console.error('Invalid map configuration');
                return;
            }

            // Map options
            const mapOptions = {
                center: { lat: config.lat, lng: config.lng },
                zoom: config.zoom || 15,
                mapTypeId: config.mapType || 'roadmap',
                styles: [
                    {
                        "featureType": "water",
                        "elementType": "geometry",
                        "stylers": [{"color": "#e9e9e9"}, {"lightness": 17}]
                    },
                    {
                        "featureType": "landscape",
                        "elementType": "geometry",
                        "stylers": [{"color": "#f5f5f5"}, {"lightness": 20}]
                    },
                    {
                        "featureType": "road.highway",
                        "elementType": "geometry.fill",
                        "stylers": [{"color": "#ffffff"}, {"lightness": 17}]
                    },
                    {
                        "featureType": "road.highway",
                        "elementType": "geometry.stroke",
                        "stylers": [{"color": "#ffffff"}, {"lightness": 29}, {"weight": 0.2}]
                    }
                ],
                mapTypeControl: false,
                streetViewControl: config.showStreetview !== false,
                fullscreenControl: false,
                zoomControl: false
            };
            
            // Create map
            const mapElement = document.querySelector('.hph-map-canvas');
            if (mapElement) {
                map = new google.maps.Map(mapElement, mapOptions);
                
                // Add property marker
                this.addPropertyMarker(config);
                
                // Add nearby places markers
                this.addNearbyMarkers(config.nearbyPlaces || []);
                
                // Add property boundaries if provided
                if (config.propertyBoundaries && config.propertyBoundaries.length > 0) {
                    this.addPropertyBoundaries(config.propertyBoundaries);
                }
            }
        },

        /**
         * Add property marker
         */
        addPropertyMarker: function(config) {
            if (!map) return;

            propertyMarker = new google.maps.Marker({
                position: { lat: config.lat, lng: config.lng },
                map: map,
                title: config.address || 'Property Location',
                icon: {
                    url: 'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"%3E%3Cpath fill="%2351BAE0" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/%3E%3C/svg%3E',
                    scaledSize: new google.maps.Size(40, 40)
                },
                animation: google.maps.Animation.DROP
            });
            
            // Add info window
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 10px;">
                        <h4 style="margin: 0 0 5px 0; color: #333;">${config.address || 'Property Location'}</h4>
                        <button onclick="window.HPHListingMap.getStreetView()" style="background: #51BAE0; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-street-view"></i> Street View
                        </button>
                    </div>
                `
            });
            
            propertyMarker.addListener('click', () => {
                infoWindow.open(map, propertyMarker);
            });
        },

        /**
         * Add nearby places markers
         */
        addNearbyMarkers: function(nearbyPlaces) {
            // Implementation would add markers for nearby places
            // This would require actual coordinates for each place
            console.log('Adding nearby markers:', nearbyPlaces);
        },

        /**
         * Add property boundaries
         */
        addPropertyBoundaries: function(boundaries) {
            if (!map || !boundaries.length) return;

            const propertyPolygon = new google.maps.Polygon({
                paths: boundaries,
                strokeColor: '#51BAE0',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#51BAE0',
                fillOpacity: 0.15
            });
            
            propertyPolygon.setMap(map);
        },

        /**
         * Setup static map fallback
         */
        setupStaticMap: function(config) {
            const mapCanvas = document.querySelector('.hph-map-canvas');
            if (mapCanvas) {
                mapCanvas.innerHTML = `
                    <div class="hph-map-static">
                        <img src="https://maps.googleapis.com/maps/api/staticmap?center=${config.lat},${config.lng}&zoom=${config.zoom || 15}&size=800x600&markers=color:blue%7C${config.lat},${config.lng}" alt="Property Location">
                        <div class="hph-map-overlay-message">
                            <i class="fas fa-map-marked-alt"></i>
                            <p>Interactive map requires Google Maps API key</p>
                        </div>
                    </div>
                `;
            }
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        window.HPHListingMap.init();
    });

})(jQuery);

// Global functions for map initialization
window.initMap = function() {
    if (window.hphMapContext) {
        window.HPHListingMap.initGoogleMap({
            lat: window.hphMapContext.coordinates.lat,
            lng: window.hphMapContext.coordinates.lng,
            address: window.hphMapContext.address,
            zoom: 15,
            mapType: 'roadmap',
            showStreetview: true
        });
    }
};

// Expose individual functions globally for inline handlers
window.zoomIn = function() { window.HPHListingMap.zoomIn(); };
window.zoomOut = function() { window.HPHListingMap.zoomOut(); };
window.toggleFullscreen = function() { window.HPHListingMap.toggleFullscreen(); };
window.getStreetView = function() { window.HPHListingMap.getStreetView(); };
window.getDirections = function(destination) { 
    // Create a fake event object for the handler
    const fakeEvent = { preventDefault: function() {} };
    const fakeElement = $('<div>').append($('<div class="hph-place-name">').text(destination));
    window.HPHListingMap.getDirections.call(fakeElement.find('.hph-place-directions'), fakeEvent);
};
window.shareProperty = function(url) {
    if (navigator.share) {
        navigator.share({
            title: 'Check out this property',
            url: url
        });
    } else {
        // Fallback to copying URL
        navigator.clipboard.writeText(url).then(function() {
            alert('Property URL copied to clipboard!');
        });
    }
};
window.copyToClipboard = function(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link copied to clipboard!');
    });
};
window.scheduleShowing = function() {
    // Implementation for scheduling showing
    alert('Schedule showing functionality would be implemented here');
};
window.calculateMortgage = function() {
    // Implementation for mortgage calculator
    alert('Mortgage calculator would be implemented here');
};
window.getPreApproved = function() {
    // Implementation for pre-approval
    alert('Pre-approval process would be implemented here');
};
window.requestInfo = function() {
    // Implementation for info request
    alert('Info request form would be implemented here');
};
window.saveProperty = function() {
    // Implementation for saving property
    alert('Save property functionality would be implemented here');
};
