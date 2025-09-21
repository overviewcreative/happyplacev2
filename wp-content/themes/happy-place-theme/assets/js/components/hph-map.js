/**
 * HPH Map Component
 * Flexible Mapbox GL JS integration for Happy Place Theme
 * Supports listings, POIs, and multiple use cases
 */

// HPH Map Component loaded

class HPHMap {
    /**
     * Get appropriate map style based on theme
     */
    getMapStyle(theme) {
        const styles = {
            'professional': 'mapbox://styles/mapbox/light-v11',
            'luxury': 'mapbox://styles/mapbox/dark-v11',
            'streets': 'mapbox://styles/mapbox/streets-v12',
            'satellite': 'mapbox://styles/mapbox/satellite-streets-v12',
            'minimal': 'mapbox://styles/mapbox/light-v11',
            'navigation': 'mapbox://styles/mapbox/navigation-day-v1'
        };
        
        return styles[theme] || styles.professional;
    }

    constructor(container, options = {}) {
        this.container = typeof container === 'string' ? document.getElementById(container) : container;
        this.map = null;
        this.markers = [];
        this.popups = [];
        
        // Default options
        this.options = {
            style: this.getMapStyle(options.styleTheme || 'professional'),
            zoom: 15,
            center: [-75.1398, 38.7816], // Default to Delaware
            pitch: 0,
            bearing: 0,
            attributionControl: true,
            navigationControl: true,
            fullscreenControl: false,
            geolocateControl: false,
            scaleControl: false,
            styleTheme: 'professional', // Custom theme
            markerTheme: 'happyPlace',   // Marker color theme
            ...options
        };

        this.init();
    }

    /**
     * Initialize the map
     */
    init() {
        if (!this.container) {
            console.error('HPH Map: Container not found');
            return;
        }

        if (!window.hph_mapbox_config || !window.hph_mapbox_config.access_token) {
            console.error('HPH Map: Mapbox access token not found');
            this.showError('Map configuration not available');
            return;
        }

        if (typeof mapboxgl === 'undefined') {
            console.error('HPH Map: Mapbox GL JS not loaded');
            this.showError('Mapbox GL JS library not available');
            return;
        }

        try {
            // Set Mapbox access token
            mapboxgl.accessToken = window.hph_mapbox_config.access_token;

            // Clear map container to prevent conflicts
            if (this.container) {
                this.container.innerHTML = '';
            }

            // Create map
            this.map = new mapboxgl.Map({
                container: this.container,
                style: this.options.style,
                center: this.options.center,
                zoom: this.options.zoom,
                pitch: this.options.pitch,
                bearing: this.options.bearing,
                attributionControl: this.options.attributionControl
            });

            // Add controls
            this.addControls();

            // Handle map load
            this.map.on('load', () => {
                this.onMapLoad();
            });

            // Handle resize
            this.map.on('resize', () => {
                this.map.resize();
            });

            // Trigger custom event
            this.container.dispatchEvent(new CustomEvent('hph-map-initialized', {
                detail: { map: this.map, instance: this }
            }));

        } catch (error) {
            console.error('HPH Map: Initialization error', error);
            this.showError('Failed to load map');
        }
    }

    /**
     * Add map controls
     */
    addControls() {
        if (this.options.navigationControl) {
            this.map.addControl(new mapboxgl.NavigationControl({
                showCompass: false,
                visualizePitch: false
            }), 'top-right');
        }

        if (this.options.fullscreenControl) {
            this.map.addControl(new mapboxgl.FullscreenControl(), 'top-right');
        }

        if (this.options.geolocateControl) {
            this.map.addControl(new mapboxgl.GeolocateControl({
                positionOptions: { enableHighAccuracy: true },
                trackUserLocation: false
            }), 'top-right');
        }

        if (this.options.scaleControl) {
            this.map.addControl(new mapboxgl.ScaleControl({
                maxWidth: 100,
                unit: 'imperial'
            }), 'bottom-left');
        }
    }

    /**
     * Handle map load event
     */
    onMapLoad() {
        // Add any default sources or layers here
        // Map loaded successfully
    }

    /**
     * Add a single listing marker
     */
    addListingMarker(listing, options = {}) {
        if (!listing.latitude || !listing.longitude) {
            console.warn('HPH Map: Listing missing coordinates', listing);
            return null;
        }

        const markerOptions = {
            showPopup: true,
            popupOffset: 25,
            className: 'hph-listing-marker',
            ...options
        };

        // Use simple default Mapbox marker (small blue dot) instead of custom complex markers
        const marker = new mapboxgl.Marker({
            color: '#0ea5e9' // Simple blue marker
        })
        .setLngLat([parseFloat(listing.longitude), parseFloat(listing.latitude)])
        .addTo(this.map);

        // Add popup if enabled
        if (markerOptions.showPopup) {
            const popup = this.createListingPopup(listing, markerOptions);
            marker.setPopup(popup);
        }

        // Store marker reference
        this.markers.push({
            type: 'listing',
            id: listing.id,
            marker: marker,
            data: listing
        });

        return marker;
    }

    /**
     * Add multiple listing markers
     */
    addListingMarkers(listings, options = {}) {
        const markers = [];
        listings.forEach(listing => {
            const marker = this.addListingMarker(listing, options);
            if (marker) markers.push(marker);
        });
        
        // Fit bounds if requested
        if (options.fitBounds && markers.length > 1) {
            this.fitToMarkers(markers);
        }

        // Add zip code boundary if filtering by zip code
        if (options.showZipBoundary && options.zipCode) {
            this.addZipCodeBoundary(listings, options.zipCode);
        }

        return markers;
    }

    /**
     * Get themed colors for markers
     */
    getMarkerTheme(theme = null) {
        const markerTheme = theme || this.options.markerTheme || 'happyPlace';
        
        const themes = {
            happyPlace: {
                active: '#10b981',    // Green
                pending: '#f59e0b',   // Amber  
                sold: '#ef4444',      // Red
                default: 'var(--hph-primary)'    // Use CSS variable
            },
            luxury: {
                active: '#8fbc8f',    // Sage green
                pending: '#cd853f',   // Peru
                sold: '#8b0000',      // Dark red
                default: '#d4af37'    // Gold
            },
            minimal: {
                active: '#059669',    // Emerald
                pending: '#d97706',   // Orange
                sold: '#dc2626',      // Red
                default: '#000000'    // Black
            }
        };
        
        return themes[markerTheme] || themes.happyPlace;
    }

    /**
     * Create listing marker element (DEPRECATED - now using simple Mapbox markers)
     * Keeping this method for backward compatibility but it's no longer used
     */
    createListingMarkerElement(listing, options) {
        // This method is deprecated - we now use simple Mapbox default markers
        // instead of complex custom elements to avoid hover issues
        console.warn('HPH Map: createListingMarkerElement is deprecated, using simple Mapbox markers instead');
        return null;
    }

    /**
     * Create listing popup
     */
    createListingPopup(listing, options) {
        const popup = new mapboxgl.Popup({
            offset: options.popupOffset || 25,
            closeButton: true,
            closeOnClick: false,
            className: 'hph-listing-popup hph-map-card-popup'
        });

        const price = listing.price ? this.formatPrice(listing.price) : '';
        const image = listing.featured_image || '';
        
        // Format address like the sidebar cards
        const streetAddress = listing.street_address || '';
        const location = this.formatLocation(listing);
        
        // Status configuration matching sidebar cards with BEM methodology
        const statusConfig = {
            'active': { text: 'Active', class: 'hph-map-card__badge--success' },
            'pending': { text: 'Pending', class: 'hph-map-card__badge--warning' },
            'sold': { text: 'Sold', class: 'hph-map-card__badge--danger' },
            'new': { text: 'New', class: 'hph-map-card__badge--primary' }
        };
        
        const status = listing.status ? statusConfig[listing.status.toLowerCase()] : null;

        // Popup content using framework classes for consistency
        const popupContent = `
            <div class="hph-listing-card hph-listing-card--map">
                <!-- Image Section using framework classes -->
                <div class="hph-card-map__image">
                    ${image ? `
                        <img src="${image}" 
                             alt="${streetAddress}" 
                             loading="lazy">
                    ` : `
                        <div class="hph-card__image-placeholder">
                            <i class="fas fa-home"></i>
                        </div>
                    `}
                    
                    ${status ? `
                        <div class="hph-card__status hph-card__status--${listing.status?.toLowerCase() || 'active'}">
                            ${status.text}
                        </div>
                    ` : ''}
                    
                    <button class="hph-card__favorite" data-listing-id="${listing.id || ''}">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
                
                <!-- Content Section using framework classes -->
                <div class="hph-card-map__content">
                    <!-- Price -->
                    ${price ? `<div class="hph-card-map__price">${price}</div>` : ''}
                    
                    <!-- Address -->
                    <div class="hph-card-map__address">
                        ${streetAddress || 'Property Listing'}
                    </div>
                    
                    <!-- Features -->
                    <div class="hph-card-map__details">
                        ${listing.bedrooms ? `<span>${listing.bedrooms} beds</span>` : ''}
                        ${listing.bathrooms ? `<span>${listing.bathrooms} baths</span>` : ''}
                        ${listing.square_feet ? `<span>${this.formatNumber(listing.square_feet)} sq ft</span>` : ''}
                    </div>
                    
                    <!-- Action Link -->
                    <a href="${listing.permalink || '#'}" class="hph-card-map__link">
                        View Details
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        `;

        popup.setHTML(popupContent);
        return popup;
    }

    /**
     * Add POI marker
     */
    addPOIMarker(poi, options = {}) {
        if (!poi.latitude || !poi.longitude) {
            console.warn('HPH Map: POI missing coordinates', poi);
            return null;
        }

        // Use simple default Mapbox marker for POIs too
        const marker = new mapboxgl.Marker({
            color: '#f59e0b' // Orange color for POIs to distinguish from listings
        })
        .setLngLat([parseFloat(poi.longitude), parseFloat(poi.latitude)])
        .addTo(this.map);

        if (options.showPopup !== false) {
            const popup = this.createPOIPopup(poi, options);
            marker.setPopup(popup);
        }

        this.markers.push({
            type: 'poi',
            id: poi.id || poi.name,
            marker: marker,
            data: poi
        });

        return marker;
    }

    /**
     * Create POI marker element (DEPRECATED - now using simple Mapbox markers)
     * Keeping this method for backward compatibility but it's no longer used
     */
    createPOIMarkerElement(poi, options) {
        // This method is deprecated - we now use simple Mapbox default markers
        console.warn('HPH Map: createPOIMarkerElement is deprecated, using simple Mapbox markers instead');
        return null;
    }

    /**
     * Create POI popup
     */
    createPOIPopup(poi, options) {
        const popup = new mapboxgl.Popup({
            offset: 15,
            closeButton: true,
            className: 'hph-poi-popup'
        });

        const popupContent = `
            <div class="hph-popup-content">
                <div class="hph-popup-details">
                    <h4 class="hph-popup-title">${poi.name || poi.title}</h4>
                    ${poi.type ? `<div class="hph-popup-type">${poi.type}</div>` : ''}
                    ${poi.address ? `<div class="hph-popup-address">
                        <i class="fas fa-map-marker-alt"></i>
                        ${poi.address}
                    </div>` : ''}
                    ${poi.description ? `<div class="hph-popup-description">
                        ${poi.description}
                    </div>` : ''}
                </div>
            </div>
        `;

        popup.setHTML(popupContent);
        return popup;
    }

    /**
     * Get POI icon based on type
     */
    getPOIIcon(type) {
        const icons = {
            school: 'fas fa-graduation-cap',
            hospital: 'fas fa-hospital',
            restaurant: 'fas fa-utensils',
            shopping: 'fas fa-shopping-bag',
            park: 'fas fa-tree',
            gas_station: 'fas fa-gas-pump',
            bank: 'fas fa-university',
            gym: 'fas fa-dumbbell',
            pharmacy: 'fas fa-pills',
            default: 'fas fa-map-pin'
        };

        return icons[type] || icons.default;
    }

    /**
     * Fit map to show all markers
     */
    fitToMarkers(markers = null) {
        const markersToFit = markers || this.markers.map(m => m.marker);
        
        if (markersToFit.length === 0) return;

        if (markersToFit.length === 1) {
            // Single marker - just center
            const lngLat = markersToFit[0].getLngLat();
            this.map.flyTo({
                center: lngLat,
                zoom: this.options.zoom
            });
            return;
        }

        // Multiple markers - fit bounds
        const bounds = new mapboxgl.LngLatBounds();
        markersToFit.forEach(marker => {
            bounds.extend(marker.getLngLat());
        });

        this.map.fitBounds(bounds, {
            padding: 50,
            maxZoom: 16
        });
    }

    /**
     * Clear all markers
     */
    clearMarkers(type = null) {
        this.markers.forEach((markerObj, index) => {
            if (!type || markerObj.type === type) {
                markerObj.marker.remove();
                this.markers.splice(index, 1);
            }
        });
    }

    /**
     * Get marker by ID
     */
    getMarker(id, type = null) {
        return this.markers.find(m => 
            m.id === id && (!type || m.type === type)
        );
    }

    /**
     * Show error message
     */
    showError(message) {
        if (!this.container) return;

        this.container.innerHTML = `
            <div class="hph-map-error">
                <div class="hph-map-error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="hph-map-error-message">
                    <h4>Map Unavailable</h4>
                    <p>${message}</p>
                </div>
            </div>
        `;
    }

    /**
     * Format price for display
     */
    formatPrice(price) {
        const numPrice = parseFloat(price);
        if (isNaN(numPrice)) return price;
        
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(numPrice);
    }

    /**
     * Format number with commas
     */
    formatNumber(number) {
        return new Intl.NumberFormat('en-US').format(number);
    }

    /**
     * Format address for display
     */
    formatAddress(listing) {
        const parts = [];
        
        if (listing.street_address || (listing.street_number && listing.street_name)) {
            parts.push(listing.street_address || `${listing.street_number} ${listing.street_name} ${listing.street_type || ''}`.trim());
        }
        
        if (listing.city) parts.push(listing.city);
        if (listing.state) parts.push(listing.state);
        if (listing.zip_code) parts.push(listing.zip_code);

        return parts.join(', ');
    }

    /**
     * Format location (city, state) for display like sidebar cards
     */
    formatLocation(listing) {
        const parts = [];
        
        if (listing.city) parts.push(listing.city);
        if (listing.state) parts.push(listing.state);

        return parts.join(', ');
    }

    /**
     * Change map style dynamically
     */
    setStyle(styleTheme, markerTheme = null) {
        if (!this.map) return;
        
        const newStyle = this.getMapStyle(styleTheme);
        this.map.setStyle(newStyle);
        
        // Update options
        this.options.styleTheme = styleTheme;
        if (markerTheme) {
            this.options.markerTheme = markerTheme;
            
            // Update existing markers with new theme
            this.updateMarkerThemes();
        }
    }
    
    /**
     * Update existing marker themes
     */
    updateMarkerThemes() {
        const colors = this.getMarkerTheme();
        
        this.markers.forEach(markerObj => {
            if (markerObj.type === 'listing') {
                const el = markerObj.marker.getElement();
                const listing = markerObj.data;
                const statusColor = colors[listing.status?.toLowerCase()] || colors.default;
                
                // Update CSS properties
                el.style.setProperty('--marker-color', statusColor);
                el.style.setProperty('--marker-border-color', statusColor);
                
                // Update theme class
                el.className = el.className.replace(/hph-marker-theme--\w+/, '');
                el.classList.add(`hph-marker-theme--${this.options.markerTheme}`);
                
                // Update icon background
                const icon = el.querySelector('.hph-marker-icon');
                if (icon) {
                    icon.style.backgroundColor = statusColor;
                }
            }
        });
    }
    
    /**
     * Add style control to map
     */
    addStyleControl() {
        const styleControl = {
            onAdd: () => {
                const div = document.createElement('div');
                div.className = 'mapboxgl-ctrl mapboxgl-ctrl-group hph-style-control';
                
                const styles = [
                    { name: 'Professional', value: 'professional', icon: 'fas fa-building' },
                    { name: 'Luxury', value: 'luxury', icon: 'fas fa-gem' },
                    { name: 'Satellite', value: 'satellite', icon: 'fas fa-satellite' },
                    { name: 'Streets', value: 'streets', icon: 'fas fa-road' }
                ];
                
                styles.forEach(style => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.innerHTML = `<i class="${style.icon}"></i>`;
                    button.title = style.name;
                    button.addEventListener('click', () => {
                        this.setStyle(style.value);
                    });
                    div.appendChild(button);
                });
                
                return div;
            },
            onRemove: () => {}
        };
        
        this.map.addControl(styleControl, 'top-left');
    }
    
    /**
     * Enable/disable 3D buildings
     */
    toggle3DBuildings(enable = true) {
        if (!this.map) return;
        
        this.map.on('style.load', () => {
            if (enable) {
                // Add 3D building layer
                this.map.addLayer({
                    'id': 'add-3d-buildings',
                    'source': 'composite',
                    'source-layer': 'building',
                    'filter': ['==', 'extrude', 'true'],
                    'type': 'fill-extrusion',
                    'minzoom': 15,
                    'paint': {
                        'fill-extrusion-color': '#aaa',
                        'fill-extrusion-height': [
                            'interpolate',
                            ['linear'],
                            ['zoom'],
                            15, 0,
                            15.05, ['get', 'height']
                        ],
                        'fill-extrusion-base': [
                            'interpolate',
                            ['linear'],
                            ['zoom'],
                            15, 0,
                            15.05, ['get', 'min_height']
                        ],
                        'fill-extrusion-opacity': 0.6
                    }
                });
            } else {
                // Remove 3D buildings layer
                if (this.map.getLayer('add-3d-buildings')) {
                    this.map.removeLayer('add-3d-buildings');
                }
            }
        });
    }
    
    /**
     * Add zip code boundary polygon
     */
    addZipCodeBoundary(listings, zipCode) {
        if (!this.map) return;
        
        // Filter listings for the specific zip code
        const zipListings = listings.filter(listing => 
            listing.zip_code && listing.zip_code === zipCode
        );
        
        if (zipListings.length < 2) {
            console.log('HPH Map: Not enough listings in zip code to create boundary');
            return;
        }
        
        try {
            // Create coordinates array
            const coordinates = zipListings.map(listing => [
                parseFloat(listing.longitude), 
                parseFloat(listing.latitude)
            ]);
            
            // Create a simple bounding polygon (could be enhanced with proper convex hull)
            const bounds = this.calculateBounds(coordinates);
            const boundaryCoords = this.createBoundingPolygon(bounds);
            
            // Remove existing zip boundary if it exists
            if (this.map.getSource('zip-boundary')) {
                this.map.removeLayer('zip-boundary-fill');
                this.map.removeLayer('zip-boundary-line');
                this.map.removeSource('zip-boundary');
            }
            
            // Add zip code boundary source and layers
            this.map.addSource('zip-boundary', {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    geometry: {
                        type: 'Polygon',
                        coordinates: [boundaryCoords]
                    },
                    properties: {
                        zipCode: zipCode
                    }
                }
            });
            
            // Add fill layer
            this.map.addLayer({
                id: 'zip-boundary-fill',
                type: 'fill',
                source: 'zip-boundary',
                paint: {
                    'fill-color': '#0ea5e9',
                    'fill-opacity': 0.1
                }
            });
            
            // Add stroke layer
            this.map.addLayer({
                id: 'zip-boundary-line',
                type: 'line',
                source: 'zip-boundary',
                paint: {
                    'line-color': '#0ea5e9',
                    'line-width': 2,
                    'line-dasharray': [2, 2]
                }
            });
            
            console.log(`HPH Map: Added boundary for zip code ${zipCode}`);
            
        } catch (error) {
            console.error('HPH Map: Error adding zip boundary:', error);
        }
    }
    
    /**
     * Calculate bounds from coordinates
     */
    calculateBounds(coordinates) {
        let minLng = Infinity, maxLng = -Infinity;
        let minLat = Infinity, maxLat = -Infinity;
        
        coordinates.forEach(([lng, lat]) => {
            minLng = Math.min(minLng, lng);
            maxLng = Math.max(maxLng, lng);
            minLat = Math.min(minLat, lat);
            maxLat = Math.max(maxLat, lat);
        });
        
        return { minLng, maxLng, minLat, maxLat };
    }
    
    /**
     * Create a simple rectangular bounding polygon with rounded corners
     */
    createBoundingPolygon(bounds) {
        const padding = 0.005; // Add padding around the bounds
        
        const minLng = bounds.minLng - padding;
        const maxLng = bounds.maxLng + padding;
        const minLat = bounds.minLat - padding;
        const maxLat = bounds.maxLat + padding;
        
        // Create a rectangle with slightly rounded corners
        return [
            [minLng, minLat],
            [maxLng, minLat],
            [maxLng, maxLat],
            [minLng, maxLat],
            [minLng, minLat] // Close the polygon
        ];
    }
    
    /**
     * Remove zip code boundary
     */
    removeZipCodeBoundary() {
        if (!this.map) return;
        
        try {
            if (this.map.getSource('zip-boundary')) {
                this.map.removeLayer('zip-boundary-fill');
                this.map.removeLayer('zip-boundary-line');
                this.map.removeSource('zip-boundary');
            }
        } catch (error) {
            console.error('HPH Map: Error removing zip boundary:', error);
        }
    }

    /**
     * Destroy map instance
     */
    destroy() {
        if (this.map) {
            this.clearMarkers();
            this.removeZipCodeBoundary();
            this.map.remove();
            this.map = null;
        }
    }
}

// Auto-initialize maps with data attributes
document.addEventListener('DOMContentLoaded', () => {
    const mapContainers = document.querySelectorAll('[data-hph-map]');
    
    mapContainers.forEach(container => {
        try {
            // Parse map options from data attributes
            const options = {};
            
            if (container.dataset.mapCenter) {
                options.center = JSON.parse(container.dataset.mapCenter);
            }
            
            if (container.dataset.mapZoom) {
                options.zoom = parseInt(container.dataset.mapZoom);
            }
            
            if (container.dataset.mapStyle) {
                options.style = container.dataset.mapStyle;
            }

            // Initialize map
            const map = new HPHMap(container, options);
            
            // Add listings if provided
            if (container.dataset.mapListings) {
                const listings = JSON.parse(container.dataset.mapListings);
                const markerOptions = {
                    fitBounds: listings.length > 1
                };
                
                // Add zip code boundary options if filtering by zip code
                if (container.dataset.showZipBoundary && container.dataset.zipCode) {
                    markerOptions.showZipBoundary = true;
                    markerOptions.zipCode = container.dataset.zipCode;
                }
                
                map.addListingMarkers(listings, markerOptions);
            }

            // Add POIs if provided
            if (container.dataset.mapPois) {
                const pois = JSON.parse(container.dataset.mapPois);
                pois.forEach(poi => map.addPOIMarker(poi));
            }

            // Store reference for external access
            container.hphMap = map;
            
        } catch (error) {
            console.error('HPH Map: Auto-initialization error', error);
        }
    });
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HPHMap;
} else {
    window.HPHMap = HPHMap;
}
