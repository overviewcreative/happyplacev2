/**
 * Property Map Component
 * Google Maps integration for single listing pages
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';
    
    // Property Map namespace
    HPH.PropertyMap = {
        
        maps: {},
        infoWindows: {},
        markers: {},
        directionsService: null,
        directionsRenderer: null,
        placesService: null,
        nearbyMarkers: {},
        
        /**
         * Initialize all property maps on the page
         */
        init: function() {
            if (typeof google === 'undefined' || !google.maps) {
                console.warn('Google Maps API not loaded');
                return;
            }
            
            this.directionsService = new google.maps.DirectionsService();
            
            // Initialize each map container
            $('.hph-property-map__container').each(function() {
                var mapContainer = $(this);
                var mapId = mapContainer.attr('id');
                
                if (window.hphMapData && window.hphMapData[mapId]) {
                    HPH.PropertyMap.initializeMap(mapId, window.hphMapData[mapId]);
                }
            });
            
            // Bind event handlers
            this.bindEvents();
        },
        
        /**
         * Initialize individual map
         */
        initializeMap: function(mapId, mapData) {
            var container = document.getElementById(mapId);
            if (!container) return;
            
            // Hide loading indicator
            $(container).find('.hph-map-loading').fadeOut();
            
            // Map options
            var mapOptions = {
                center: { lat: mapData.lat, lng: mapData.lng },
                zoom: mapData.zoom,
                mapTypeId: google.maps.MapTypeId[mapData.type.toUpperCase()] || google.maps.MapTypeId.ROADMAP,
                styles: this.getMapStyles(),
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
                zoomControl: true,
                zoomControlOptions: {
                    position: google.maps.ControlPosition.RIGHT_BOTTOM
                }
            };
            
            // Create map
            var map = new google.maps.Map(container, mapOptions);
            this.maps[mapId] = map;
            
            // Create property marker
            this.createPropertyMarker(mapId, mapData);
            
            // Initialize places service
            this.placesService = new google.maps.places.PlacesService(map);
            
            // Create info window
            this.infoWindows[mapId] = new google.maps.InfoWindow({
                maxWidth: 300
            });
            
            // Initialize directions renderer
            if (!this.directionsRenderer) {
                this.directionsRenderer = new google.maps.DirectionsRenderer({
                    draggable: true,
                    suppressMarkers: false
                });
            }
        },
        
        /**
         * Create property marker with custom info window
         */
        createPropertyMarker: function(mapId, mapData) {
            var map = this.maps[mapId];
            if (!map) return;
            
            // Custom marker icon
            var markerIcon = {
                url: HPH_THEME_URI + '/assets/images/map-marker-property.svg',
                scaledSize: new google.maps.Size(40, 50),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(20, 50)
            };
            
            // Create marker
            var marker = new google.maps.Marker({
                position: { lat: mapData.lat, lng: mapData.lng },
                map: map,
                title: mapData.propertyTitle,
                icon: markerIcon,
                animation: google.maps.Animation.DROP
            });
            
            this.markers[mapId] = marker;
            
            // Create info window content
            var infoContent = this.createPropertyInfoContent(mapData);
            
            // Add click listener
            marker.addListener('click', () => {
                this.infoWindows[mapId].setContent(infoContent);
                this.infoWindows[mapId].open(map, marker);
            });
            
            // Open info window by default
            setTimeout(() => {
                this.infoWindows[mapId].setContent(infoContent);
                this.infoWindows[mapId].open(map, marker);
            }, 1000);
        },
        
        /**
         * Create property info window content
         */
        createPropertyInfoContent: function(mapData) {
            var bedsText = mapData.propertyBeds ? mapData.propertyBeds + ' beds' : '';
            var bathsText = mapData.propertyBaths ? mapData.propertyBaths + ' baths' : '';
            var sqftText = mapData.propertySqft ? mapData.propertySqft + ' sqft' : '';
            var details = [bedsText, bathsText, sqftText].filter(Boolean).join(' • ');
            
            return `
                <div class="hph-map-info-window">
                    <div class="hph-map-info__header">
                        <h4 class="hph-map-info__title">${mapData.propertyTitle}</h4>
                        <div class="hph-map-info__price">${mapData.propertyPrice}</div>
                    </div>
                    <div class="hph-map-info__details">${details}</div>
                    <div class="hph-map-info__address">
                        <i class="fas fa-map-marker-alt"></i>
                        ${mapData.address}
                    </div>
                    <div class="hph-map-info__actions">
                        <button class="hph-map-info__btn" onclick="HPH.PropertyMap.getDirections('${mapData.address}')">
                            <i class="fas fa-route"></i> Directions
                        </button>
                        <button class="hph-map-info__btn" onclick="HPH.PropertyMap.openStreetView(${mapData.lat}, ${mapData.lng})">
                            <i class="fas fa-street-view"></i> Street View
                        </button>
                    </div>
                </div>
            `;
        },
        
        /**
         * Get custom map styles
         */
        getMapStyles: function() {
            return [
                {
                    featureType: "poi",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                },
                {
                    featureType: "transit",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                }
            ];
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            var self = this;
            
            // Map type controls
            $(document).on('click', '.hph-map-control[data-map-type]', function(e) {
                e.preventDefault();
                var mapType = $(this).data('map-type');
                var mapContainer = $(this).closest('.hph-property-map').find('.hph-property-map__container');
                var mapId = mapContainer.attr('id');
                
                if (self.maps[mapId]) {
                    self.maps[mapId].setMapTypeId(google.maps.MapTypeId[mapType.toUpperCase()]);
                    $(this).addClass('active').siblings().removeClass('active');
                }
            });
            
            // Street view control
            $(document).on('click', '.hph-map-control[data-action="street-view"]', function(e) {
                e.preventDefault();
                var mapContainer = $(this).closest('.hph-property-map').find('.hph-property-map__container');
                var mapId = mapContainer.attr('id');
                var mapData = window.hphMapData[mapId];
                
                if (mapData) {
                    self.openStreetView(mapData.lat, mapData.lng);
                }
            });
            
            // Fullscreen control
            $(document).on('click', '.hph-map-control[data-action="fullscreen"]', function(e) {
                e.preventDefault();
                var mapContainer = $(this).closest('.hph-property-map').find('.hph-property-map__container');
                self.toggleFullscreen(mapContainer[0]);
            });
            
            // Nearby places filters
            $(document).on('click', '.hph-map-filter-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var category = $btn.data('category');
                var color = $btn.data('color');
                var mapContainer = $btn.closest('.hph-property-map').find('.hph-property-map__container');
                var mapId = mapContainer.attr('id');
                
                if ($btn.hasClass('active')) {
                    $btn.removeClass('active');
                    self.clearNearbyMarkers(mapId, category);
                } else {
                    $btn.addClass('active');
                    self.searchNearbyPlaces(mapId, category, color);
                }
            });
            
            // Commute calculator
            $(document).on('click', '.hph-commute-mode', function(e) {
                e.preventDefault();
                $(this).addClass('active').siblings().removeClass('active');
            });
            
            $(document).on('click', '.hph-commute-calculate', function(e) {
                e.preventDefault();
                var $form = $(this).closest('.hph-commute-form');
                var destination = $form.find('.hph-commute-destination').val();
                var mode = $form.find('.hph-commute-mode.active').data('mode');
                var mapContainer = $(this).closest('.hph-property-map').find('.hph-property-map__container');
                var mapId = mapContainer.attr('id');
                
                if (destination) {
                    self.calculateCommute(mapId, destination, mode);
                }
            });
        },
        
        /**
         * Search for nearby places
         */
        searchNearbyPlaces: function(mapId, category, color) {
            var map = this.maps[mapId];
            var mapData = window.hphMapData[mapId];
            if (!map || !this.placesService) return;
            
            var request = {
                location: new google.maps.LatLng(mapData.lat, mapData.lng),
                radius: 2000, // 2km radius
                type: this.getPlaceTypeForCategory(category)
            };
            
            this.placesService.nearbySearch(request, (results, status) => {
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    this.displayNearbyPlaces(mapId, category, results, color);
                }
            });
        },
        
        /**
         * Display nearby places on map
         */
        displayNearbyPlaces: function(mapId, category, places, color) {
            var map = this.maps[mapId];
            if (!map) return;
            
            if (!this.nearbyMarkers[mapId]) {
                this.nearbyMarkers[mapId] = {};
            }
            if (!this.nearbyMarkers[mapId][category]) {
                this.nearbyMarkers[mapId][category] = [];
            }
            
            // Create markers for places
            places.slice(0, 10).forEach((place, index) => {
                var marker = new google.maps.Marker({
                    position: place.geometry.location,
                    map: map,
                    title: place.name,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 8,
                        fillColor: color,
                        fillOpacity: 0.8,
                        strokeColor: '#ffffff',
                        strokeWeight: 2
                    }
                });
                
                // Create info window for place
                var infoContent = `
                    <div class="hph-place-info">
                        <h5>${place.name}</h5>
                        <div class="hph-place-rating">
                            ${this.createStarRating(place.rating)}
                            <span>${place.rating || 'N/A'}</span>
                        </div>
                        <div class="hph-place-address">${place.vicinity}</div>
                    </div>
                `;
                
                marker.addListener('click', () => {
                    this.infoWindows[mapId].setContent(infoContent);
                    this.infoWindows[mapId].open(map, marker);
                });
                
                this.nearbyMarkers[mapId][category].push(marker);
            });
        },
        
        /**
         * Clear nearby markers for category
         */
        clearNearbyMarkers: function(mapId, category) {
            if (this.nearbyMarkers[mapId] && this.nearbyMarkers[mapId][category]) {
                this.nearbyMarkers[mapId][category].forEach(marker => {
                    marker.setMap(null);
                });
                this.nearbyMarkers[mapId][category] = [];
            }
        },
        
        /**
         * Get place type for category
         */
        getPlaceTypeForCategory: function(category) {
            const typeMap = {
                schools: 'school',
                shopping: 'shopping_mall',
                restaurants: 'restaurant',
                healthcare: 'hospital',
                parks: 'park',
                transit: 'transit_station'
            };
            return typeMap[category] || category;
        },
        
        /**
         * Create star rating HTML
         */
        createStarRating: function(rating) {
            if (!rating) return '';
            
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= Math.floor(rating)) {
                    stars += '<i class="fas fa-star"></i>';
                } else if (i - 0.5 <= rating) {
                    stars += '<i class="fas fa-star-half-alt"></i>';
                } else {
                    stars += '<i class="far fa-star"></i>';
                }
            }
            return stars;
        },
        
        /**
         * Calculate commute time and distance
         */
        calculateCommute: function(mapId, destination, mode) {
            var mapData = window.hphMapData[mapId];
            if (!mapData || !this.directionsService) return;
            
            var travelMode = google.maps.TravelMode[mode.toUpperCase()] || google.maps.TravelMode.DRIVING;
            
            var request = {
                origin: new google.maps.LatLng(mapData.lat, mapData.lng),
                destination: destination,
                travelMode: travelMode,
                unitSystem: google.maps.UnitSystem.IMPERIAL
            };
            
            this.directionsService.route(request, (result, status) => {
                if (status === google.maps.DirectionsStatus.OK) {
                    var route = result.routes[0];
                    var leg = route.legs[0];
                    
                    // Display results
                    var $results = $(`.hph-property-map[data-listing-id] .hph-commute-results`);
                    $results.find('[data-result="distance"]').text(leg.distance.text);
                    $results.find('[data-result="duration"]').text(leg.duration.text);
                    $results.show();
                    
                    // Show route on map
                    this.directionsRenderer.setDirections(result);
                    this.directionsRenderer.setMap(this.maps[mapId]);
                } else {
                    alert('Could not calculate route: ' + status);
                }
            });
        },
        
        /**
         * Get directions to property
         */
        getDirections: function(address) {
            var url = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(address)}`;
            window.open(url, '_blank');
        },
        
        /**
         * Open Street View
         */
        openStreetView: function(lat, lng) {
            var url = `https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=${lat},${lng}`;
            window.open(url, '_blank');
        },
        
        /**
         * Toggle fullscreen map
         */
        toggleFullscreen: function(element) {
            if (!document.fullscreenElement) {
                element.requestFullscreen().catch(err => {
                    console.warn('Error attempting to enable fullscreen:', err);
                });
            } else {
                document.exitFullscreen();
            }
        }
    };
    
    // Initialize when Google Maps API is ready
    function initGoogleMaps() {
        if (typeof google !== 'undefined' && google.maps) {
            HPH.PropertyMap.init();
        } else {
            // Retry after short delay
            setTimeout(initGoogleMaps, 500);
        }
    }
    
    // Initialize on document ready and when Google Maps loads
    $(document).ready(function() {
        // Check if Google Maps is already loaded
        if (typeof google !== 'undefined' && google.maps) {
            HPH.PropertyMap.init();
        } else {
            // Wait for Google Maps to load
            window.initPropertyMaps = function() {
                HPH.PropertyMap.init();
            };
        }
    });
    
})(jQuery);