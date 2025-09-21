/**
 * Enhanced Archive Listing JavaScript
 * Handles view switching, map integration, and enhanced interactions
 */

(function() {
    'use strict';

    class ArchiveListingEnhanced {
        constructor() {
            this.currentView = 'grid';
            this.mapbox = null;
            this.mapMarkers = [];
            this.listings = [];
            this.resizeTimeout = null;
            
            this.init();
        }

        init() {
            console.log('Initializing Archive Listing Enhanced...');
            this.bindEvents();
            this.initFilterToggle();
            this.loadListingsData();
            this.initViewMode();
            console.log('Archive Listing Enhanced initialized with', this.listings.length, 'listings');
        }

        bindEvents() {
            // View switcher - works with header view controls and any other view buttons
            document.addEventListener('click', (e) => {
                if (e.target) {
                    const viewBtn = e.target.closest('.hph-view-btn[data-view]');
                    if (viewBtn) {
                        e.preventDefault();
                        const view = viewBtn.dataset.view;
                        console.log('View button clicked:', view);
                        this.switchView(view);
                    }
                }
            });

            // Map panel close button
            document.addEventListener('click', (e) => {
                if (e.target && e.target.closest('.hph-map-panel-close')) {
                    e.preventDefault();
                    this.switchView('grid'); // Return to grid view
                }
            });

            // Filter toggle
            document.addEventListener('click', (e) => {
                if (e.target && e.target.closest('[data-filter-toggle]')) {
                    e.preventDefault();
                    this.toggleFilters();
                }
            });

            // AJAX Search Form
            document.addEventListener('submit', (e) => {
                const ajaxForm = e.target ? e.target.closest('[data-ajax-search]') : null;
                if (ajaxForm) {
                    e.preventDefault();
                    this.handleAjaxSearch(ajaxForm);
                }
            });

            // Input change events for real-time filtering
            document.addEventListener('change', (e) => {
                const searchForm = e.target ? e.target.closest('[data-ajax-search]') : null;
                if (searchForm && e.target.matches('select, input[type="text"]')) {
                    // Debounce the search
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.handleAjaxSearch(searchForm);
                    }, 300);
                }
            });

            // Sort functionality
            const sortSelect = document.querySelector('[data-sort-select]');
            if (sortSelect) {
                sortSelect.addEventListener('change', (e) => {
                    this.handleSort(e.target.value);
                });
            }

            // Map card hover effects with proper event delegation
            document.addEventListener('mouseenter', (e) => {
                if (e.target) {
                    const card = e.target.closest('.hph-map-card');
                    if (card) {
                        const listingId = card.dataset.listingId;
                        this.highlightMapMarker(listingId);
                    }
                }
            }, true);

            document.addEventListener('mouseleave', (e) => {
                if (e.target) {
                    const card = e.target.closest('.hph-map-card');
                    if (card) {
                        this.unhighlightMapMarkers();
                    }
                }
            }, true);
        }

        switchView(view) {
            console.log('Switching to view:', view, 'Current view:', this.currentView);
            
            if (this.currentView === view) return;

            // Update buttons
            document.querySelectorAll('.hph-view-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            });

            const activeBtn = document.querySelector(`.hph-view-btn[data-view="${view}"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
                activeBtn.setAttribute('aria-selected', 'true');
                console.log('Updated active button for:', view);
            } else {
                console.warn('Could not find button for view:', view);
            }

            // Update content - special handling for map view
            document.querySelectorAll('.hph-view-content').forEach(content => {
                content.classList.remove('active');
                if (!content.classList.contains('hph-map-view-layout')) {
                    content.style.display = 'none';
                }
            });

            const activeContent = document.querySelector(`[data-view-content="${view}"]`);
            if (activeContent) {
                activeContent.classList.add('active');
                if (!activeContent.classList.contains('hph-map-view-layout')) {
                    activeContent.style.display = 'block';
                }
                console.log('Updated active content for:', view);
            } else {
                console.warn('Could not find content for view:', view);
            }

            const previousView = this.currentView;
            this.currentView = view;

            // Add body classes for layout state (Zillow approach)
            document.body.classList.remove('hph-view-grid', 'hph-view-list', 'hph-view-map');
            document.body.classList.add(`hph-view-${view}`);

            // Handle map initialization and resize
            if (view === 'map') {
                // Add map-visible class to body
                document.body.classList.add('hph-map-visible');
                
                if (!this.mapbox) {
                    console.log('Initializing map...');
                    // Use requestAnimationFrame for better performance
                    requestAnimationFrame(() => this.initMap());
                } else {
                    // Map exists, just resize it
                    this.debouncedMapResize();
                }
            } else {
                // Remove map-visible class when leaving map view
                document.body.classList.remove('hph-map-visible');
            }
            
            // Store preference
            localStorage.setItem('hph_archive_view', view);

            // Trigger custom event
            document.dispatchEvent(new CustomEvent('hph:view:changed', {
                detail: { view, previousView }
            }));
        }
        
        debouncedMapResize() {
            if (this.resizeTimeout) {
                clearTimeout(this.resizeTimeout);
            }
            this.resizeTimeout = setTimeout(() => {
                if (this.mapbox && this.currentView === 'map') {
                    this.mapbox.resize();
                    this.fitMapToMarkers();
                }
            }, 150);
        }

        initViewMode() {
            // Restore saved view preference
            const savedView = localStorage.getItem('hph_archive_view');
            if (savedView && document.querySelector(`[data-view="${savedView}"]`)) {
                this.switchView(savedView);
            }
        }

        toggleFilters() {
            const toggle = document.querySelector('[data-filter-toggle]');
            const content = document.querySelector('[data-filter-content]');
            const icon = toggle?.querySelector('.toggle-icon');
            const text = toggle?.querySelector('.toggle-text');

            if (!content || !toggle) return;

            const isCollapsed = content.classList.contains('collapsed');

            if (isCollapsed) {
                // Expand
                content.classList.remove('collapsed');
                toggle.classList.remove('collapsed');
                if (icon) icon.style.transform = 'rotate(0deg)';
                if (text) text.textContent = 'Hide Filters';
            } else {
                // Collapse
                content.classList.add('collapsed');
                toggle.classList.add('collapsed');
                if (icon) icon.style.transform = 'rotate(180deg)';
                if (text) text.textContent = 'Show Filters';
            }

            // Store preference
            localStorage.setItem('hph_filters_collapsed', isCollapsed ? 'false' : 'true');
        }

        initFilterToggle() {
            // On archive pages, keep filters expanded by default
            if (document.body.classList.contains('archive-listing')) {
                const content = document.querySelector('[data-filter-content]');
                const toggle = document.querySelector('[data-filter-toggle]');
                
                if (content && toggle) {
                    content.classList.remove('collapsed');
                    toggle.classList.remove('collapsed');
                }
                return; // Skip localStorage restore on archive pages
            }
            
            // Restore filter state for other pages
            const filtersCollapsed = localStorage.getItem('hph_filters_collapsed') === 'true';
            if (filtersCollapsed) {
                setTimeout(() => this.toggleFilters(), 100);
            }
        }

        loadListingsData() {
            // First try to get listings data from the map container
            const mapContainer = document.getElementById('mapbox-listings-map');
            if (mapContainer && mapContainer.dataset.mapListings) {
                try {
                    const listingsData = JSON.parse(mapContainer.dataset.mapListings);
                    this.listings = listingsData.map(listing => ({
                        id: listing.id,
                        lat: listing.latitude,
                        lng: listing.longitude,
                        price: listing.price,
                        address: this.buildAddress(listing),
                        title: listing.title,
                        permalink: listing.permalink,
                        featured_image: listing.featured_image,
                        bedrooms: listing.bedrooms,
                        bathrooms: listing.bathrooms,
                        square_feet: listing.square_feet,
                        status: listing.status
                    })).filter(listing => listing.lat && listing.lng);
                    
                    console.log('Loaded listings from map data:', this.listings.length, 'listings');
                    return;
                } catch (e) {
                    console.warn('Could not parse map listings data:', e);
                }
            }
            
            // Fallback: Extract listing data from map cards
            const mapCards = document.querySelectorAll('.hph-map-card');
            this.listings = Array.from(mapCards).map(card => ({
                id: card.dataset.listingId,
                lat: parseFloat(card.dataset.lat),
                lng: parseFloat(card.dataset.lng),
                price: card.dataset.price,
                address: card.dataset.address,
                element: card
            })).filter(listing => listing.lat && listing.lng);
            
            console.log('Loaded listings from cards:', this.listings.length, 'listings');
        }
        
        buildAddress(listing) {
            const parts = [];
            if (listing.street_address) parts.push(listing.street_address);
            if (listing.city) parts.push(listing.city);
            if (listing.state) parts.push(listing.state);
            if (listing.zip_code) parts.push(listing.zip_code);
            return parts.join(', ') || 'Address not available';
        }

        waitForMapbox() {
            return new Promise((resolve) => {
                const checkMapbox = () => {
                    if (typeof mapboxgl !== 'undefined') {
                        resolve();
                    } else {
                        setTimeout(checkMapbox, 100);
                    }
                };
                checkMapbox();
            });
        }

        async initMap() {
            console.log('initMap called');
            
            // Wait for Mapbox to load if it's not ready yet
            if (typeof mapboxgl === 'undefined') {
                console.log('Waiting for Mapbox to load...');
                await this.waitForMapbox();
                if (typeof mapboxgl === 'undefined') {
                    console.error('Mapbox GL JS failed to load');
                    this.showMapError();
                    return;
                }
            }
            console.log('Mapbox GL JS is loaded');

            // Set access token if not already set
            if (!mapboxgl.accessToken && typeof hph_mapbox_config !== 'undefined' && hph_mapbox_config.access_token) {
                mapboxgl.accessToken = hph_mapbox_config.access_token;
                console.log('Set access token from hph_mapbox_config');
            }

            // Check if access token is set
            if (!mapboxgl.accessToken) {
                console.error('Mapbox access token not set. Current token:', mapboxgl.accessToken);
                console.log('Available configs:', {
                    hph_mapbox_config: typeof hph_mapbox_config !== 'undefined' ? hph_mapbox_config : 'undefined'
                });
                this.showMapError();
                return;
            }
            console.log('Mapbox access token is set:', mapboxgl.accessToken.substring(0, 20) + '...');

            const mapContainer = document.getElementById('mapbox-listings-map');
            if (!mapContainer) {
                console.error('Map container not found: #mapbox-listings-map');
                return;
            }
            console.log('Map container found:', mapContainer);
            
            // Clear the map container to avoid Mapbox warning
            mapContainer.innerHTML = '';

            console.log('Listings data:', this.listings.length, 'listings found');
            console.log('Sample listing:', this.listings[0] || 'No listings');

            try {
                console.log('Initializing Mapbox map...');
                
                // Initialize map
                this.mapbox = new mapboxgl.Map({
                    container: 'mapbox-listings-map',
                    style: 'mapbox://styles/mapbox/light-v11',
                    center: this.getMapCenter(),
                    zoom: 12,
                    attributionControl: false // Remove attribution to save space
                });

                // Add controls with optimized settings
                const navControl = new mapboxgl.NavigationControl({
                    showCompass: true,
                    showZoom: true,
                    visualizePitch: false
                });
                this.mapbox.addControl(navControl, 'top-right');

                // Wait for map to load then add markers
                this.mapbox.on('load', () => {
                    console.log('Map loaded, adding markers');
                    try {
                        this.addMapMarkers();
                        this.fitMapToMarkers();
                    } catch (markerError) {
                        console.error('Error adding markers:', markerError);
                    }
                });

                // Handle resize when map view becomes active
                this.mapbox.on('style.load', () => {
                    requestAnimationFrame(() => {
                        if (this.mapbox && this.currentView === 'map') {
                            this.mapbox.resize();
                        }
                    });
                });

            } catch (error) {
                console.error('Map initialization error:', error);
                this.showMapError();
            }
        }

        getMapCenter() {
            if (this.listings.length === 0) {
                return [-75.5277, 39.7391]; // Default to Delaware
            }

            const avgLat = this.listings.reduce((sum, listing) => sum + listing.lat, 0) / this.listings.length;
            const avgLng = this.listings.reduce((sum, listing) => sum + listing.lng, 0) / this.listings.length;
            
            return [avgLng, avgLat];
        }

        addMapMarkers() {
            // Use document fragment for efficient DOM manipulation
            const fragment = document.createDocumentFragment();
            
            this.listings.forEach((listing, index) => {
                // Create marker element
                const markerElement = document.createElement('div');
                markerElement.className = 'hph-map-marker';
                markerElement.innerHTML = `
                    <div class="hph-marker-pin">
                        <div class="hph-marker-price">$${this.formatPrice(listing.price)}</div>
                    </div>
                `;

                // Create marker with optimized settings
                const marker = new mapboxgl.Marker({
                    element: markerElement,
                    anchor: 'bottom'
                })
                    .setLngLat([listing.lng, listing.lat])
                    .addTo(this.mapbox);

                // Create popup with enhanced content
                const popup = new mapboxgl.Popup({ 
                    offset: 25,
                    closeButton: true,
                    closeOnClick: true,
                    maxWidth: '320px'
                })
                    .setHTML(`
                        <div class="hph-listing-card hph-listing-card--map">
                            <!-- Image Section using framework classes -->
                            <div class="hph-card-map__image">
                                ${listing.image ? `
                                    <img src="${listing.image}" 
                                         alt="${listing.address}" 
                                         loading="lazy">
                                ` : `
                                    <div class="hph-card__image-placeholder">
                                        <i class="fas fa-home"></i>
                                    </div>
                                `}
                                
                                ${listing.status ? `
                                    <div class="hph-card__status hph-card__status--${listing.status.toLowerCase()}">
                                        ${listing.status}
                                    </div>
                                ` : ''}
                                
                                <button class="hph-card__favorite" data-listing-id="${listing.id}">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            
                            <!-- Content Section using framework classes -->
                            <div class="hph-card-map__content">
                                <!-- Price -->
                                <div class="hph-card-map__price">$${Number(listing.price).toLocaleString()}</div>
                                
                                <!-- Address -->
                                <div class="hph-card-map__address">
                                    ${listing.address}
                                </div>
                                
                                <!-- Features -->
                                <div class="hph-card-map__details">
                                    ${listing.bedrooms ? `<span>${listing.bedrooms} beds</span>` : ''}
                                    ${listing.bathrooms ? `<span>${listing.bathrooms} baths</span>` : ''}
                                    ${listing.square_feet ? `<span>${Number(listing.square_feet).toLocaleString()} sq ft</span>` : ''}
                                </div>
                                
                                <!-- Action Link -->
                                <a href="${listing.permalink || '#'}" class="hph-card-map__link">
                                    View Details
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    `);

                marker.setPopup(popup);

                // Store marker reference
                this.mapMarkers.push({
                    marker,
                    listingId: listing.id,
                    element: markerElement
                });

                // Add click handler to scroll to card with throttling
                let clickTimeout;
                markerElement.addEventListener('click', () => {
                    if (clickTimeout) return;
                    clickTimeout = setTimeout(() => {
                        this.scrollToMapCard(listing.id);
                        clickTimeout = null;
                    }, 100);
                });
            });
        }

        fitMapToMarkers() {
            if (this.listings.length === 0) return;

            const bounds = new mapboxgl.LngLatBounds();
            this.listings.forEach(listing => {
                bounds.extend([listing.lng, listing.lat]);
            });

            this.mapbox.fitBounds(bounds, {
                padding: 50,
                maxZoom: 15
            });
        }

        highlightMapMarker(listingId) {
            const markerData = this.mapMarkers.find(m => m.listingId === listingId);
            if (markerData) {
                markerData.element.classList.add('highlighted');
            }
        }

        unhighlightMapMarkers() {
            this.mapMarkers.forEach(markerData => {
                markerData.element.classList.remove('highlighted');
            });
        }

        scrollToMapCard(listingId) {
            const card = document.querySelector(`[data-listing-id="${listingId}"]`);
            if (card) {
                card.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // Highlight card temporarily
                card.classList.add('highlighted');
                setTimeout(() => card.classList.remove('highlighted'), 2000);
            }
        }

        formatPrice(price) {
            const num = Number(price);
            if (num >= 1000000) {
                return `${(num / 1000000).toFixed(1)}M`;
            } else if (num >= 1000) {
                return `${Math.round(num / 1000)}K`;
            }
            return num.toString();
        }

        showMapError() {
            const mapContainer = document.getElementById('mapbox-listings-map');
            if (mapContainer) {
                mapContainer.innerHTML = `
                    <div class="hph-map-error">
                        <div class="hph-error-content">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Map Unavailable</h3>
                            <p>Unable to load the map view. Please try the grid or list view instead.</p>
                        </div>
                    </div>
                `;
            }
        }

        async handleAjaxSearch(form) {
            console.log('AJAX Search triggered');
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
            submitBtn.disabled = true;
            
            // Get form data
            const formData = new FormData(form);
            const formDataObj = Object.fromEntries(formData.entries());
            
            // Add nonce for security
            formData.append('nonce', hph_ajax.nonce);
            
            try {
                const response = await fetch(hph_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                console.log('=== AJAX Response Received ===');
                console.log('Full response data:', data);
                console.log('data.data.map_data:', data.data ? data.data.map_data : 'no data.data');
                console.log('data.data.zip_code:', data.data ? data.data.zip_code : 'no data.data');
                console.log('this.currentView:', this.currentView);
                
                if (data.success) {
                    // Update results count
                    const countElement = document.querySelector('.hph-results-count');
                    if (countElement && data.data.count_text) {
                        countElement.innerHTML = data.data.count_text;
                    }
                    
                    // Update active filter badges
                    this.updateActiveFilters(formDataObj);
                    
                    // Update listings container
                    const gridContainer = document.querySelector('.hph-listings-grid .hph-grid');
                    const listContainer = document.querySelector('.hph-listings-list .hph-list-container');
                    
                    if (gridContainer && data.data.grid_html) {
                        gridContainer.innerHTML = data.data.grid_html;
                    }
                    
                    if (listContainer && data.data.list_html) {
                        listContainer.innerHTML = data.data.list_html;
                    }
                    
                    // Update pagination if provided
                    if (data.data.pagination_html) {
                        const paginationSection = document.querySelector('.hph-pagination-section');
                        if (paginationSection) {
                            paginationSection.innerHTML = data.data.pagination_html;
                        }
                    }
                    
                    // Update map data if in map view
                    if (this.currentView === 'map' && data.data.map_data) {
                        this.updateMapListings(data.data.map_data, data.data.zip_code);
                    }
                    
                    // Update URL without reload
                    const url = new URL(window.location);
                    
                    // Clear existing search params
                    ['s', 'property_type', 'price_range', 'zip_code', 'bedrooms', 'bathrooms'].forEach(key => {
                        url.searchParams.delete(key);
                    });
                    
                    // Add non-empty params
                    Object.keys(formDataObj).forEach(key => {
                        if (formDataObj[key] && formDataObj[key] !== '' && key !== 'action' && key !== 'nonce') {
                            url.searchParams.set(key, formDataObj[key]);
                        }
                    });
                    
                    // Update browser URL
                    window.history.pushState(null, '', url.toString());
                    
                } else {
                    console.error('AJAX Error:', data.data);
                    this.showSearchError();
                }
                
            } catch (error) {
                console.error('Search request failed:', error);
                this.showSearchError();
            } finally {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }
        
        showSearchError() {
            // Show error message to user
            const alertDiv = document.createElement('div');
            alertDiv.className = 'hph-alert hph-alert-error';
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                Search request failed. Please try again.
            `;
            
            const container = document.querySelector('.hph-search-bar .hph-container');
            if (container) {
                container.insertBefore(alertDiv, container.firstChild);
                
                // Remove after 5 seconds
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            }
        }

        handleSort(sortValue) {
            console.log('Sort changed to:', sortValue);
            
            // Trigger AJAX search with sort parameter
            const form = document.querySelector('[data-ajax-search]');
            if (form) {
                // Add sort to form
                let sortInput = form.querySelector('input[name="sort"]');
                if (!sortInput) {
                    sortInput = document.createElement('input');
                    sortInput.type = 'hidden';
                    sortInput.name = 'sort';
                    form.appendChild(sortInput);
                }
                sortInput.value = sortValue;
                
                // Trigger AJAX search
                this.handleAjaxSearch(form);
            }
        }

        updateMapListings(mapData, zipCode = null) {
            console.log('=== updateMapListings called ===');
            console.log('mapData:', mapData);
            console.log('mapData length:', mapData ? mapData.length : 'null/undefined');
            console.log('zipCode:', zipCode);
            
            const mapContainer = document.getElementById('mapbox-listings-map');
            console.log('mapContainer found:', !!mapContainer);
            
            if (!mapContainer || !mapContainer.hphMap) {
                console.warn('Map not found or not initialized');
                console.log('mapContainer.hphMap:', mapContainer ? mapContainer.hphMap : 'no container');
                return;
            }
            
            const map = mapContainer.hphMap;
            console.log('Map object:', map);
            console.log('Map methods available:', {
                clearMarkers: typeof map.clearMarkers,
                removeZipCodeBoundary: typeof map.removeZipCodeBoundary,
                addListingMarkers: typeof map.addListingMarkers
            });
            
            // Clear existing markers
            console.log('Clearing existing markers...');
            map.clearMarkers();
            map.removeZipCodeBoundary();
            
            // Add new listings
            if (mapData && mapData.length > 0) {
                console.log('Adding', mapData.length, 'new markers...');
                const markerOptions = {
                    fitBounds: mapData.length > 1
                };
                
                // Add zip code boundary if filtering by zip code
                if (zipCode) {
                    markerOptions.showZipBoundary = true;
                    markerOptions.zipCode = zipCode;
                    console.log('Adding zip code boundary for:', zipCode);
                }
                
                console.log('Calling addListingMarkers with options:', markerOptions);
                map.addListingMarkers(mapData, markerOptions);
                
                // Update sidebar listings
                this.updateMapSidebar(mapData);
                console.log('Map update completed');
            } else {
                console.log('No map data to add');
            }
        }
        
        updateMapSidebar(mapData) {
            const sidebarContent = document.querySelector('.hph-map-panel-content .hph-map-listings');
            if (!sidebarContent) return;
            
            if (mapData && mapData.length > 0) {
                // Generate HTML for map cards
                let sidebarHTML = '';
                mapData.forEach(listing => {
                    sidebarHTML += this.generateMapCardHTML(listing);
                });
                sidebarContent.innerHTML = sidebarHTML;
            } else {
                sidebarContent.innerHTML = '<div class="hph-map-empty"><p>No properties to display.</p></div>';
            }
        }
        
        generateMapCardHTML(listing) {
            const price = listing.price ? '$' + new Intl.NumberFormat('en-US').format(listing.price) : '';
            const image = listing.featured_image || '';
            const statusBadges = {
                'active': { text: 'Active', class: 'hph-badge-success' },
                'pending': { text: 'Pending', class: 'hph-badge-warning' },
                'sold': { text: 'Sold', class: 'hph-badge-danger' },
                'new': { text: 'New', class: 'hph-badge-primary' }
            };
            const status = statusBadges[listing.status?.toLowerCase()] || null;
            
            return `
                <div class="hph-map-card" 
                     data-listing-id="${listing.id}"
                     data-lat="${listing.latitude}"
                     data-lng="${listing.longitude}"
                     data-price="${listing.price || ''}"
                     data-address="${listing.street_address || ''}">
                    <a href="${listing.permalink || '#'}" class="hph-map-card-link">
                        <div class="hph-map-card-image">
                            ${image ? `
                                <img src="${image}" 
                                     alt="${listing.street_address || listing.title}" 
                                     class="hph-map-image" 
                                     loading="lazy">
                            ` : `
                                <div class="hph-map-placeholder">
                                    <i class="fas fa-home"></i>
                                </div>
                            `}
                            
                            ${status ? `
                                <div class="hph-map-badge">
                                    <span class="hph-badge hph-badge-xs ${status.class}">
                                        ${status.text}
                                    </span>
                                </div>
                            ` : ''}
                        </div>
                        
                        <div class="hph-map-card-content">
                            ${price ? `<div class="hph-map-price">${price}</div>` : ''}
                            
                            <div class="hph-map-address">
                                <h4 class="hph-map-title">${listing.street_address || listing.title}</h4>
                                ${listing.city || listing.state ? `
                                    <p class="hph-map-location">${[listing.city, listing.state].filter(Boolean).join(', ')}</p>
                                ` : ''}
                            </div>
                            
                            <div class="hph-map-stats">
                                ${listing.bedrooms ? `
                                    <span class="hph-map-stat">
                                        <i class="fas fa-bed"></i>
                                        ${listing.bedrooms}
                                    </span>
                                ` : ''}
                                
                                ${listing.bathrooms ? `
                                    <span class="hph-map-stat">
                                        <i class="fas fa-bath"></i>
                                        ${listing.bathrooms}
                                    </span>
                                ` : ''}
                                
                                ${listing.square_feet ? `
                                    <span class="hph-map-stat">
                                        <i class="fas fa-ruler-combined"></i>
                                        ${new Intl.NumberFormat('en-US').format(listing.square_feet)} sq ft
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                    </a>
                </div>
            `;
        }
        
        updateActiveFilters(formData) {
            const activeFiltersContainer = document.querySelector('.hph-active-filters');
            if (!activeFiltersContainer) return;
            
            const filterMap = {
                's': (value) => `Search: "${value}"`,
                'property_type': (value) => value.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()),
                'min_price': (value) => `Min: $${new Intl.NumberFormat('en-US').format(value)}`,
                'max_price': (value) => `Max: $${new Intl.NumberFormat('en-US').format(value)}`,
                'zip_code': (value) => `Zip: ${value}`,
                'bedrooms': (value) => `${value}+ beds`,
                'bathrooms': (value) => `${value}+ baths`
            };
            
            let filtersHTML = '';
            Object.keys(formData).forEach((key, index) => {
                if (formData[key] && formData[key] !== '' && filterMap[key]) {
                    const badgeClass = index === 0 ? 'hph-filter-badge' : 'hph-filter-badge';
                    filtersHTML += `<span class="${badgeClass}">${filterMap[key](formData[key])}</span>`;
                }
            });
            
            activeFiltersContainer.innerHTML = filtersHTML;
        }
    }

    // Initialize when DOM is ready - prevent multiple instances and HPH Map conflicts
    document.addEventListener('DOMContentLoaded', () => {
        if (document.querySelector('.hph-listing-archive') && !window.hphArchiveEnhanced) {
            console.log('Initializing Enhanced Archive Listing');
            
            // Prevent HPH Map component from auto-initializing our container
            const mapContainer = document.getElementById('mapbox-listings-map');
            if (mapContainer) {
                // Remove data-hph-map attribute to prevent conflicts
                mapContainer.removeAttribute('data-hph-map');
                console.log('Archive Enhanced: Prevented HPH Map auto-initialization conflict');
            }
            
            window.hphArchiveEnhanced = new ArchiveListingEnhanced();
        }
    });

    // Add map marker styles
    const mapStyles = `
        <style>
        .hph-map-marker {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .hph-map-marker:hover {
            z-index: 1000;
        }
        
        .hph-marker-pin {
            background: var(--hph-primary, #0073aa);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            position: relative;
            min-width: 60px;
            text-align: center;
        }
        
        .hph-marker-pin::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid var(--hph-primary, #0073aa);
        }
        
        .hph-map-marker.highlighted .hph-marker-pin {
            background: var(--hph-accent, #00a0d2);
            transform: scale(1.1);
        }
        
        .hph-map-marker.highlighted .hph-marker-pin::after {
            border-top-color: var(--hph-accent, #00a0d2);
        }
        
        .hph-map-popup {
            text-align: center;
        }
        
        .hph-popup-price {
            font-size: 1rem;
            font-weight: 700;
            color: var(--hph-primary, #0073aa);
            margin-bottom: 0.25rem;
        }
        
        .hph-popup-address {
            font-size: 0.875rem;
            color: var(--hph-gray-700, #374151);
        }
        
        .hph-map-card.highlighted {
            border-color: var(--hph-accent, #00a0d2);
            box-shadow: 0 4px 12px rgba(0, 160, 210, 0.2);
        }
        
        .hph-map-error {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--hph-gray-50, #f9fafb);
        }
        
        .hph-error-content {
            text-align: center;
            color: var(--hph-gray-600, #6b7280);
        }
        
        .hph-error-content i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--hph-warning, #f59e0b);
        }
        
        .hph-error-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--hph-gray-900, #111827);
        }
        </style>
    `;
    
    document.head.insertAdjacentHTML('beforeend', mapStyles);

})();
