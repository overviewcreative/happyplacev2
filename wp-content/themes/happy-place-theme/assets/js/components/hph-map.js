/**
 * HPH Map Component
 * Flexible Mapbox GL JS integration for Happy Place Theme
 * Supports listings, POIs, and multiple use cases
 */

// HPH Map Component loaded

// Prevent duplicate class declaration
if (typeof HPHMap === 'undefined') {
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
            return;
        }

        if (!window.hph_mapbox_config) {
            this.showError('Map configuration not available');
            return;
        }

        if (!window.hph_mapbox_config.access_token && window.hph_mapbox_config.has_token !== false) {
            this.showError('Map configuration not available');
            return;
        }

        // Handle case when no token is configured
        if (window.hph_mapbox_config.has_token === false) {
            this.showConfigurationMessage();
            return;
        }

        if (typeof mapboxgl === 'undefined') {
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
     * Add multiple listing markers with clustering support
     */
    addListingMarkers(listings, options = {}) {
        
        const markerOptions = {
            enableClustering: true,
            clusterRadius: 50,
            clusterMaxZoom: 14,
            showPopup: true,
            fitBounds: true,
            ...options
        };


        if (markerOptions.enableClustering && listings.length > 1) {
            this.addClusteredMarkers(listings, markerOptions);
        } else {
            // Use individual markers for small numbers
            const markers = [];
            listings.forEach(listing => {
                const marker = this.addListingMarker(listing, markerOptions);
                if (marker) markers.push(marker);
            });
            
            // Fit bounds if requested
            if (markerOptions.fitBounds && markers.length > 1) {
                this.fitToMarkers(markers);
            }
        }

        // Add zip code boundary if filtering by zip code
        if (markerOptions.showZipBoundary && markerOptions.zipCode) {
            this.addZipCodeBoundary(listings, markerOptions.zipCode);
        }

        return this.markers;
    }

    /**
     * Get HPH theme colors from CSS variables
     */
    getHPHColors() {
        // Get computed styles from document root to read CSS variables
        const rootStyles = getComputedStyle(document.documentElement);
        
        return {
            primary: rootStyles.getPropertyValue('--hph-primary').trim() || '#1e40af',
            secondary: rootStyles.getPropertyValue('--hph-secondary').trim() || '#059669', 
            accent: rootStyles.getPropertyValue('--hph-accent').trim() || '#dc2626',
            warning: rootStyles.getPropertyValue('--hph-warning').trim() || '#f59e0b',
            muted: rootStyles.getPropertyValue('--hph-muted').trim() || '#6b7280'
        };
    }

    /**
     * Add clustered markers using Mapbox GL JS clustering
     */
    addClusteredMarkers(listings, options = {}) {
        console.log('HPHMap: Starting addClusteredMarkers with', listings.length, 'listings');
        console.log('HPHMap: Options:', options);

        // Clear existing markers and sources
        this.clearMapMarkers();

        // Get HPH theme colors
        const colors = this.getHPHColors();
        console.log('HPHMap: Colors:', colors);

        // Filter out listings without valid coordinates
        const validListings = listings.filter(listing => {
            const hasCoords = listing.latitude && listing.longitude &&
                             !isNaN(parseFloat(listing.latitude)) &&
                             !isNaN(parseFloat(listing.longitude));
            if (!hasCoords && listing.id) {
                console.warn('HPHMap: Listing', listing.id, 'missing valid coordinates:', {
                    lat: listing.latitude,
                    lng: listing.longitude
                });
            }
            return hasCoords;
        });

        console.log('HPHMap: Valid listings with coordinates:', validListings.length);

        if (validListings.length === 0) {
            console.warn('HPHMap: No listings with valid coordinates found');
            return;
        }

        // Prepare GeoJSON data for clustering
        const geojsonData = {
            type: 'FeatureCollection',
            features: validListings.map(listing => ({
                type: 'Feature',
                geometry: {
                    type: 'Point',
                    coordinates: [parseFloat(listing.longitude), parseFloat(listing.latitude)]
                },
                properties: {
                    id: listing.id,
                    title: listing.title || '',
                    price: listing.price || 0,
                    address: listing.street_address || '',
                    status: listing.status || 'active',
                    featured_image: listing.featured_image || '',
                    bedrooms: listing.bedrooms || 0,
                    bathrooms: listing.bathrooms || 0,
                    square_feet: listing.square_feet || 0,
                    permalink: listing.permalink || '#'
                }
            }))
        };

        console.log('HPHMap: GeoJSON prepared with', geojsonData.features.length, 'features');
        console.log('HPHMap: Sample feature:', geojsonData.features[0]);


        // Add source for clustering
        try {
            const sourceConfig = {
                type: 'geojson',
                data: geojsonData,
                cluster: true,
                clusterMaxZoom: options.clusterMaxZoom || 14,
                clusterRadius: options.clusterRadius || 50
            };

            console.log('HPHMap: Adding source with config:', sourceConfig);
            this.map.addSource('listings', sourceConfig);
            console.log('HPHMap: Source added successfully');
        } catch (error) {
            console.error('HPHMap: Error adding source:', error);
            return;
        }

        // Add cluster circles layer with brand colors
        try {
            const clusterLayerConfig = {
                id: 'clusters',
                type: 'circle',
                source: 'listings',
                filter: ['has', 'point_count'],
                paint: {
                    'circle-color': [
                        'step',
                        ['get', 'point_count'],
                        colors.primary || '#2563eb',      // HPH brand primary for small clusters
                        5,
                        colors.secondary || '#06b6d4',    // HPH brand secondary for medium clusters
                        10,
                        colors.accent || '#dc2626'       // HPH brand accent for large clusters
                    ],
                    'circle-radius': [
                        'step',
                        ['get', 'point_count'],
                        25,   // Larger base size for better visibility
                        5,
                        35,   // Medium clusters
                        10,
                        45    // Large clusters
                    ],
                    'circle-stroke-width': 3,
                    'circle-stroke-color': '#ffffff',
                    'circle-opacity': 0.9
                }
            };

            console.log('HPHMap: Adding cluster layer with config:', clusterLayerConfig);
            this.map.addLayer(clusterLayerConfig);
            console.log('HPHMap: Cluster layer added successfully');
        } catch (error) {
            console.error('HPHMap: Error adding cluster layer:', error);
            return;
        }

        // Add cluster count labels with better styling
        this.map.addLayer({
            id: 'cluster-count',
            type: 'symbol',
            source: 'listings',
            filter: ['has', 'point_count'],
            layout: {
                'text-field': '{point_count}',
                'text-font': ['Open Sans Bold', 'Arial Unicode MS Bold'],
                'text-size': 16,
                'text-anchor': 'center'
            },
            paint: {
                'text-color': '#ffffff',
                'text-halo-color': 'rgba(0, 0, 0, 0.3)',
                'text-halo-width': 1
            }
        });

        // Add individual unclustered points with better brand styling
        try {
            const unclusteredLayerConfig = {
                id: 'unclustered-point',
                type: 'circle',
                source: 'listings',
                filter: ['!', ['has', 'point_count']],
                paint: {
                    'circle-color': [
                        'match',
                        ['get', 'status'],
                        'active', colors.secondary || '#10b981',     // HPH green for active
                        'pending', colors.warning || '#f59e0b',     // HPH warning for pending
                        'sold', colors.muted || '#6b7280',          // HPH muted for sold
                        colors.primary || '#2563eb'                 // HPH primary default
                    ],
                    'circle-radius': 12,         // Larger for better visibility
                    'circle-stroke-width': 3,
                    'circle-stroke-color': '#ffffff',
                    'circle-opacity': 0.9
                }
            };

            console.log('HPHMap: Adding unclustered point layer with config:', unclusteredLayerConfig);
            this.map.addLayer(unclusteredLayerConfig);
            console.log('HPHMap: Unclustered point layer added successfully');
        } catch (error) {
            console.error('HPHMap: Error adding unclustered point layer:', error);
            return;
        }

        // Remove price labels from individual points - details will show in popup instead

        // Add click handlers
        console.log('HPHMap: Adding cluster event handlers...');
        this.addClusterEventHandlers(options);

        // Fit bounds to show all markers
        if (options.fitBounds && geojsonData.features.length > 0) {
            console.log('HPHMap: Fitting bounds to show all markers...');
            this.fitToGeoJSON(geojsonData);
        }

        console.log('HPHMap: Clustered markers setup complete');
    }

    /**
     * Highlight a specific listing marker
     */
    highlightListingMarker(listingId) {
        if (!this.map || !listingId) return;

        // Remove any existing highlight
        this.unhighlightAllMarkers();

        // Add highlight source if it doesn't exist
        if (!this.map.getSource('highlight-marker')) {
            this.map.addSource('highlight-marker', {
                type: 'geojson',
                data: {
                    type: 'FeatureCollection',
                    features: []
                }
            });

            // Add highlight layer
            this.map.addLayer({
                id: 'highlight-marker-circle',
                type: 'circle',
                source: 'highlight-marker',
                paint: {
                    'circle-radius': 20,
                    'circle-color': 'rgba(255, 255, 255, 0.8)',
                    'circle-stroke-width': 4,
                    'circle-stroke-color': '#ff6b35'
                }
            });
        }

        // Find the listing in our GeoJSON data
        const listingsSource = this.map.getSource('listings');
        if (listingsSource && listingsSource._data) {
            const targetFeature = listingsSource._data.features.find(feature => 
                feature.properties.id == listingId
            );

            if (targetFeature) {
                // Update highlight source with the target feature
                this.map.getSource('highlight-marker').setData({
                    type: 'FeatureCollection',
                    features: [targetFeature]
                });

                // Pan to the marker
                this.map.easeTo({
                    center: targetFeature.geometry.coordinates,
                    zoom: Math.max(this.map.getZoom(), 16),
                    duration: 1000
                });
            }
        }
    }

    /**
     * Remove highlight from all markers
     */
    unhighlightAllMarkers() {
        if (!this.map) return;

        try {
            if (this.map.getSource('highlight-marker')) {
                this.map.getSource('highlight-marker').setData({
                    type: 'FeatureCollection',
                    features: []
                });
            }
        } catch (error) {
        }
    }

    /**
     * Show popup for a specific listing
     */
    showListingPopup(listingId) {
        if (!this.map || !listingId) return;

        // Find the listing in our GeoJSON data
        const listingsSource = this.map.getSource('listings');
        if (listingsSource && listingsSource._data) {
            const targetFeature = listingsSource._data.features.find(feature => 
                feature.properties.id == listingId
            );

            if (targetFeature) {
                const popup = this.createListingPopupFromFeature(
                    targetFeature.properties, 
                    targetFeature.geometry.coordinates
                );
                popup.addTo(this.map);
            }
        }
    }

    /**
     * Add event handlers for clustered markers
     */
    addClusterEventHandlers(options = {}) {
        // Click on cluster to zoom in
        this.map.on('click', 'clusters', (e) => {
            const features = this.map.queryRenderedFeatures(e.point, {
                layers: ['clusters']
            });

            const clusterId = features[0].properties.cluster_id;
            this.map.getSource('listings').getClusterExpansionZoom(
                clusterId,
                (err, zoom) => {
                    if (err) return;

                    this.map.easeTo({
                        center: features[0].geometry.coordinates,
                        zoom: zoom
                    });
                }
            );
        });

        // Click on individual point to show popup
        this.map.on('click', 'unclustered-point', (e) => {
            const features = e.features[0];
            const listing = features.properties;
            
            if (options.showPopup) {
                const popup = this.createListingPopupFromFeature(listing, e.lngLat);
                popup.addTo(this.map);
            }

            // Trigger custom event for external handling and sidebar synchronization
            this.map.fire('listing-click', {
                listing: listing,
                coordinates: e.lngLat
            });

            // Dispatch custom event for sidebar synchronization
            document.dispatchEvent(new CustomEvent('hph-map-listing-click', {
                detail: {
                    listingId: listing.id,
                    listing: listing,
                    coordinates: e.lngLat
                }
            }));
        });

        // Change cursor on hover
        this.map.on('mouseenter', 'clusters', () => {
            this.map.getCanvas().style.cursor = 'pointer';
        });

        this.map.on('mouseleave', 'clusters', () => {
            this.map.getCanvas().style.cursor = '';
        });

        this.map.on('mouseenter', 'unclustered-point', () => {
            this.map.getCanvas().style.cursor = 'pointer';
        });

        this.map.on('mouseleave', 'unclustered-point', () => {
            this.map.getCanvas().style.cursor = '';
        });
    }

    /**
     * Create popup from GeoJSON feature
     */
    createListingPopupFromFeature(listing, coordinates) {
        const popup = new mapboxgl.Popup({
            offset: 25,
            closeButton: true,
            closeOnClick: false,
            className: 'hph-listing-popup hph-map-card-popup'
        });

        // Get HPH theme colors
        const colors = this.getHPHColors();

        const price = listing.price ? this.formatPrice(listing.price) : '';
        const image = listing.featured_image || '';
        const title = listing.title || listing.address || 'Property';
        const streetAddress = listing.address || listing.street_address || '';
        
        // Status configuration with HPH colors
        const statusConfig = {
            'active': { text: 'Active', color: colors.secondary },
            'pending': { text: 'Pending', color: colors.warning },
            'sold': { text: 'Sold', color: colors.muted },
            'new': { text: 'New', color: colors.primary }
        };
        
        const status = listing.status ? statusConfig[listing.status.toLowerCase()] : null;

        // Improved popup content with HPH colors
        const popupContent = `
            <div style="width: 320px; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                ${image && !image.includes('placeholder') ? `
                    <div style="position: relative; height: 200px; overflow: hidden;">
                        <img src="${image}" alt="${title}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                        ${status ? `
                            <div style="position: absolute; top: 12px; left: 12px;">
                                <span style="background: ${status.color}; color: white; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">${status.text}</span>
                            </div>
                        ` : ''}
                    </div>
                ` : ''}
                
                <div style="padding: 16px;">
                    <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 600; color: #1f2937;">${title}</h3>
                    
                    ${streetAddress ? `
                        <p style="margin: 0 0 12px 0; font-size: 14px; color: #6b7280;">${streetAddress}</p>
                    ` : ''}
                    
                    <div style="font-size: 24px; font-weight: 700; color: ${colors.primary}; margin-bottom: 12px;">${price}</div>
                    
                    ${listing.bedrooms || listing.bathrooms || listing.square_feet ? `
                        <div style="display: flex; gap: 16px; font-size: 14px; color: #6b7280; margin-bottom: 16px;">
                            ${listing.bedrooms ? `<div style="display: flex; align-items: center; gap: 4px;"><i class="fas fa-bed"></i><span>${listing.bedrooms} bed</span></div>` : ''}
                            ${listing.bathrooms ? `<div style="display: flex; align-items: center; gap: 4px;"><i class="fas fa-bath"></i><span>${listing.bathrooms} bath</span></div>` : ''}
                            ${listing.square_feet ? `<div style="display: flex; align-items: center; gap: 4px;"><i class="fas fa-ruler-combined"></i><span>${Number(listing.square_feet).toLocaleString()} sqft</span></div>` : ''}
                        </div>
                    ` : ''}
                    
                    ${listing.permalink ? `
                        <a href="${listing.permalink}" style="display: inline-block; background: ${colors.primary}; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600;">
                            View Details →
                        </a>
                    ` : ''}
                </div>
            </div>
        `;

        popup.setLngLat(coordinates).setHTML(popupContent);
        return popup;
    }

    /**
     * Fit map to GeoJSON bounds
     */
    fitToGeoJSON(geojsonData) {
        if (!geojsonData.features || geojsonData.features.length === 0) return;

        const bounds = new mapboxgl.LngLatBounds();
        geojsonData.features.forEach(feature => {
            bounds.extend(feature.geometry.coordinates);
        });

        this.map.fitBounds(bounds, {
            padding: 50,
            maxZoom: 15
        });
    }

    /**
     * Clear all markers and clustering layers from the map
     */
    clearMapMarkers() {
        if (!this.map) return;
        
        // Remove individual markers
        if (this.markers && this.markers.length > 0) {
            this.markers.forEach(markerObj => {
                if (markerObj.marker && markerObj.marker.remove) {
                    markerObj.marker.remove();
                }
            });
            this.markers = [];
        }

        // Remove clustering layers and source
        try {
            if (this.map.getLayer('clusters')) {
                this.map.removeLayer('clusters');
            }
            if (this.map.getLayer('cluster-count')) {
                this.map.removeLayer('cluster-count');
            }
            if (this.map.getLayer('unclustered-point')) {
                this.map.removeLayer('unclustered-point');
            }
            if (this.map.getLayer('unclustered-point-label')) {
                this.map.removeLayer('unclustered-point-label');
            }
            if (this.map.getSource('listings')) {
                this.map.removeSource('listings');
            }
        } catch (error) {
        }
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

        // Get HPH theme colors
        const colors = this.getHPHColors();

        const price = listing.price ? this.formatPrice(listing.price) : '';
        const image = listing.featured_image || '';
        
        // Format address like the sidebar cards
        const streetAddress = listing.street_address || '';
        const location = this.formatLocation(listing);
        
        // Status configuration with HPH colors
        const statusConfig = {
            'active': { text: 'Active', color: colors.secondary },
            'pending': { text: 'Pending', color: colors.warning },
            'sold': { text: 'Sold', color: colors.muted },
            'new': { text: 'New', color: colors.primary }
        };
        
        const status = listing.status ? statusConfig[listing.status.toLowerCase()] : null;

        // Popup content using improved framework classes for consistency
        const popupContent = `
            <div class="hph-card hph-card--elevated hph-bg-white hph-overflow-hidden hph-max-w-80">
                ${image && !image.includes('placeholder') ? `
                    <div class="hph-relative hph-h-48 hph-overflow-hidden">
                        <img src="${image}" 
                             alt="${listing.title || streetAddress || 'Property'}" 
                             class="hph-w-full hph-h-full hph-object-cover hph-transition-transform hph-duration-300 hover:hph-scale-105"
                             loading="lazy"
                             onerror="this.parentElement.style.display='none'">
                        
                        ${status ? `
                            <div class="hph-absolute hph-top-3 hph-left-3">
                                <span class="hph-inline-flex hph-items-center hph-px-2 hph-py-1 hph-rounded-md hph-text-xs hph-font-semibold hph-shadow-sm"
                                      style="background-color: ${status.color}; color: white;">
                                    ${status.text}
                                </span>
                            </div>
                        ` : ''}
                        
                        <button class="hph-absolute hph-top-3 hph-right-3 hph-w-9 hph-h-9 hph-bg-white hph-rounded-full hph-flex hph-items-center hph-justify-center hph-text-gray-500 hover:hph-text-red-500 hph-transition-colors hph-shadow-sm" 
                                data-listing-id="${listing.id || ''}" 
                                onclick="event.stopPropagation();">
                            <i class="fas fa-heart hph-text-sm"></i>
                        </button>
                    </div>
                ` : ''}
                
                <div class="hph-p-4">
                    <!-- Price -->
                    ${price ? `
                        <div class="hph-font-bold hph-text-xl hph-mb-2" style="color: ${colors.primary};">
                            ${price}
                        </div>
                    ` : ''}
                    
                    <!-- Street Address -->
                    <h3 class="hph-text-gray-900 hph-font-semibold hph-text-base hph-mb-1 hph-line-clamp-1">
                        ${streetAddress || listing.title || 'Property Address'}
                    </h3>
                    
                    <!-- Location (if different from street address) -->
                    ${location && location !== streetAddress ? `
                        <p class="hph-text-sm hph-text-gray-500 hph-mb-3">${location}</p>
                    ` : ''}
                    
                    <!-- Property Details -->
                    ${listing.bedrooms || listing.bathrooms || listing.square_feet ? `
                        <div class="hph-flex hph-items-center hph-gap-4 hph-text-sm hph-text-gray-600 hph-mb-4">
                            ${listing.bedrooms ? `
                                <div class="hph-flex hph-items-center hph-gap-1">
                                    <i class="fas fa-bed hph-text-gray-500"></i>
                                    <span>${listing.bedrooms} bed${listing.bedrooms != 1 ? 's' : ''}</span>
                                </div>
                            ` : ''}
                            ${listing.bathrooms ? `
                                <div class="hph-flex hph-items-center hph-gap-1">
                                    <i class="fas fa-bath hph-text-gray-500"></i>
                                    <span>${listing.bathrooms} bath${listing.bathrooms != 1 ? 's' : ''}</span>
                                </div>
                            ` : ''}
                            ${listing.square_feet ? `
                                <div class="hph-flex hph-items-center hph-gap-1">
                                    <i class="fas fa-ruler-combined hph-text-gray-500"></i>
                                    <span>${this.formatNumber(listing.square_feet)} sq ft</span>
                                </div>
                            ` : ''}
                        </div>
                    ` : `<div class="hph-mb-4"></div>`}
                    
                    <!-- Action Button -->
                    <a href="${listing.permalink || '#'}" 
                       class="hph-btn hph-w-full hph-text-center hph-py-3 hph-font-semibold hph-transition-all hph-duration-200 hover:hph-shadow-lg"
                       style="background-color: ${colors.primary}; color: white; display: inline-block; padding: 8px 16px; border-radius: 6px; text-decoration: none;"
                       onclick="event.stopPropagation();">
                        View Details
                        <i class="fas fa-arrow-right hph-ml-2"></i>
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
     * Show configuration message when no Mapbox token is set
     */
    showConfigurationMessage() {
        if (!this.container) return;

        this.container.innerHTML = `
            <div class="hph-map-error">
                <div class="hph-map-error-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="hph-map-error-message">
                    <h4>Map Configuration Required</h4>
                    <p>To enable map view, please configure your Mapbox access token in the Happy Place plugin settings.</p>
                    <div style="margin-top: 1rem;">
                        <small style="color: #666; font-style: italic;">
                            Visit the Happy Place admin panel to add your Mapbox API key.
                        </small>
                    </div>
                </div>
            </div>
        `;
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
            
            
        } catch (error) {
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
        }
    }

    /**
     * Clear all markers from the map
     */
    clearMarkers() {
        if (!this.map) return;
        
        // Clear individual markers if they exist
        if (this.markers && this.markers.length > 0) {
            this.markers.forEach(markerObj => {
                if (markerObj.marker && markerObj.marker.remove) {
                    markerObj.marker.remove();
                }
            });
            this.markers = [];
        }
        
        // Clear clustering layers and sources directly (not calling another method)
        try {
            if (this.map.getLayer('clusters')) {
                this.map.removeLayer('clusters');
            }
            if (this.map.getLayer('cluster-count')) {
                this.map.removeLayer('cluster-count');
            }
            if (this.map.getLayer('unclustered-point')) {
                this.map.removeLayer('unclustered-point');
            }
            if (this.map.getLayer('unclustered-point-label')) {
                this.map.removeLayer('unclustered-point-label');
            }
            if (this.map.getSource('listings')) {
                this.map.removeSource('listings');
            }
        } catch (error) {
        }
    }

    /**
     * Destroy map instance
     */
    destroy() {
        if (this.map) {
            this.clearMapMarkers();
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
        // Skip containers managed by archive-listing-enhanced.js
        if (container.hasAttribute('data-archive-map')) {
            return;
        }
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
        }
    });
});

    // End of HPHMap class declaration guard
    
    // Export for module use
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = HPHMap;
    } else {
        window.HPHMap = HPHMap;
    }
}
