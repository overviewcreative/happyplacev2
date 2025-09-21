/**
 * HPH Unified Archive System
 * Comprehensive archive functionality using HPH unified framework
 * Handles: filtering, sorting, view toggling, load more, search
 *
 * @package HappyPlaceTheme
 * @version 3.0.0 - Unified System
 */

if (window.HPH) {
    HPH.register('archiveUnified', function(config) {
        return {
            // Archive state management
            currentView: 'grid',
            currentPage: 1,
            isLoading: false,
            hasMorePages: true,
            currentFilters: {},

            // DOM element references
            elements: {},

            init: function() {
                // Check if this is the map view template
                if (document.body.classList.contains('hph-map-view')) {
                    if (config.debug) {
                        console.log('HPH Archive Unified System: Initializing map-specific functionality');
                    }
                    this.initMapView();
                    return;
                }

                this.cacheElements();
                this.bindEvents();
                this.initializeView();
                this.loadSavedPreferences();

                if (config.debug) {
                    console.log('HPH Archive Unified System Initialized');
                }
            },

            /**
             * Cache DOM elements for performance
             */
            cacheElements: function() {
                this.elements = {
                    // View controls
                    viewButtons: document.querySelectorAll('[data-view]'),
                    viewContents: document.querySelectorAll('[data-view-content]'),

                    // Filter and sort controls
                    filterForm: document.querySelector('#hero-search-form, .hph-search-form'),
                    sortSelect: document.querySelector('[data-sort-select], #listings-sort'),
                    searchInput: document.querySelector('#hero-search-input'),

                    // Load more
                    loadMoreBtn: document.querySelector('[data-load-more]'),
                    loadMoreSection: document.querySelector('.hph-load-more-section'),

                    // Results containers
                    gridContainer: document.querySelector('[data-listings-container="grid"]'),
                    listContainer: document.querySelector('[data-listings-container="list"]'),
                    mapContainer: document.querySelector('[data-view-content="map"]'),

                    // Results info
                    resultsCount: document.querySelector('.hph-results-count'),
                    resultsInfo: document.querySelector('.hph-results-info')
                };
            },

            /**
             * Bind all event listeners using HPH.events
             */
            bindEvents: function() {
                // View switching
                this.elements.viewButtons.forEach(btn => {
                    HPH.events.on(btn, 'click', (e) => {
                        e.preventDefault();
                        const view = btn.dataset.view;
                        this.switchView(view);
                    });
                });

                // Filter form submission
                if (this.elements.filterForm) {
                    HPH.events.on(this.elements.filterForm, 'submit', (e) => {
                        e.preventDefault();
                        this.handleFilterSubmit();
                    });
                }

                // Sort change
                if (this.elements.sortSelect) {
                    HPH.events.on(this.elements.sortSelect, 'change', (e) => {
                        this.handleSortChange(e.target.value);
                    });
                }

                // Load more
                if (this.elements.loadMoreBtn) {
                    HPH.events.on(this.elements.loadMoreBtn, 'click', (e) => {
                        e.preventDefault();
                        this.loadMoreListings();
                    });
                }

                // Search input with debouncing
                if (this.elements.searchInput) {
                    let searchTimeout;
                    HPH.events.on(this.elements.searchInput, 'input', (e) => {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => {
                            this.handleSearchInput(e.target.value);
                        }, 500);
                    });
                }

                // Map close button
                const mapCloseBtn = document.querySelector('.hph-map-panel-close');
                if (mapCloseBtn) {
                    HPH.events.on(mapCloseBtn, 'click', (e) => {
                        e.preventDefault();
                        this.switchView('grid');
                    });
                }
            },

            /**
             * Switch between view modes (grid, list, map)
             */
            switchView: function(view) {
                if (this.currentView === view) return;

                this.currentView = view;

                // Update view buttons
                this.elements.viewButtons.forEach(btn => {
                    const isActive = btn.dataset.view === view;
                    btn.classList.toggle('active', isActive);
                    btn.setAttribute('aria-pressed', isActive);
                });

                // Update view contents
                this.elements.viewContents.forEach(content => {
                    const isActive = content.dataset.viewContent === view;
                    content.classList.toggle('active', isActive);
                    content.style.display = isActive ? 'block' : 'none';
                });

                // Handle special view logic
                switch (view) {
                    case 'map':
                        this.redirectToMapView();
                        return; // Exit early since we're redirecting
                    case 'grid':
                    case 'list':
                        document.body.classList.remove('hph-map-view-active');
                        this.showElementsForStandardViews();
                        break;
                }

                // Save preference
                this.saveViewPreference(view);

                // Trigger custom event
                HPH.events.trigger(document, 'hph:viewChanged', { view: view });
            },

            /**
             * Handle filter form submission
             */
            handleFilterSubmit: function() {
                if (this.isLoading) return;

                const formData = new FormData(this.elements.filterForm);
                const filters = {};

                // Extract all form data
                for (let [key, value] of formData.entries()) {
                    if (value && value !== '') {
                        filters[key] = value;
                    }
                }

                this.currentFilters = filters;
                this.currentPage = 1;
                this.loadListings({ reset: true });
            },

            /**
             * Handle sort change
             */
            handleSortChange: function(sortValue) {
                if (this.isLoading) return;

                this.currentFilters.sort = sortValue;
                this.currentPage = 1;
                this.loadListings({ reset: true });
            },

            /**
             * Handle search input with debouncing
             */
            handleSearchInput: function(searchTerm) {
                if (this.isLoading) return;

                if (searchTerm.length >= 3 || searchTerm.length === 0) {
                    this.currentFilters.s = searchTerm;
                    this.currentPage = 1;
                    this.loadListings({ reset: true });
                }
            },

            /**
             * Load more listings (pagination)
             */
            loadMoreListings: function() {
                if (this.isLoading || !this.hasMorePages) return;

                this.currentPage++;
                this.loadListings({ append: true });
            },

            /**
             * Main AJAX function to load listings
             */
            loadListings: function(options = {}) {
                if (this.isLoading) return;

                this.isLoading = true;
                this.showLoadingState(options.reset);

                // Prepare AJAX data
                const ajaxData = {
                    action: 'hph_load_listings_unified',
                    nonce: window.hphArchive?.nonce || '',
                    page: this.currentPage,
                    view: this.currentView,
                    post_type: 'listing',
                    ...this.currentFilters
                };

                // Make AJAX request using unified system
                fetch(window.hphArchive?.ajaxUrl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(ajaxData)
                })
                .then(response => response.json())
                .then(data => {
                    this.handleAjaxResponse(data, options);
                })
                .catch(error => {
                    console.error('Archive AJAX Error:', error);
                    this.showError('Failed to load listings. Please try again.');
                })
                .finally(() => {
                    this.isLoading = false;
                    this.hideLoadingState();
                });
            },

            /**
             * Handle AJAX response
             */
            handleAjaxResponse: function(data, options) {
                if (!data.success) {
                    this.showError(data.data?.message || 'Failed to load listings');
                    return;
                }

                const { html, pagination, count, view } = data.data;

                // Update listings
                if (options.reset) {
                    this.replaceListings(html, view);
                } else if (options.append) {
                    this.appendListings(html, view);
                }

                // Update pagination info
                this.updatePagination(pagination);

                // Update results count
                this.updateResultsCount(count);

                // Update URL without page reload
                this.updateURL();

                // Trigger custom event
                HPH.events.trigger(document, 'hph:listingsLoaded', {
                    view: view,
                    count: count,
                    page: this.currentPage
                });
            },

            /**
             * Replace listings content (for filtering/sorting)
             */
            replaceListings: function(html, view) {
                const container = view === 'list' ? this.elements.listContainer : this.elements.gridContainer;
                if (container) {
                    container.innerHTML = html;
                    this.initializeNewListings(container);
                }
            },

            /**
             * Append listings content (for load more)
             */
            appendListings: function(html, view) {
                const container = view === 'list' ? this.elements.listContainer : this.elements.gridContainer;
                if (container) {
                    container.insertAdjacentHTML('beforeend', html);
                    this.initializeNewListings(container);
                }
            },

            /**
             * Initialize newly loaded listings (favorites, etc.)
             */
            initializeNewListings: function(container) {
                // Initialize any new components that might need it
                const favoriteButtons = container.querySelectorAll('.favorite-btn');
                favoriteButtons.forEach(btn => {
                    if (!btn.dataset.initialized) {
                        btn.dataset.initialized = 'true';
                        // Add favorite functionality if available
                        if (window.userSystem?.initFavoriteButtons) {
                            window.userSystem.initFavoriteButtons();
                        }
                    }
                });
            },

            /**
             * Update pagination information
             */
            updatePagination: function(pagination) {
                this.hasMorePages = pagination.has_more;

                if (this.elements.loadMoreBtn) {
                    this.elements.loadMoreBtn.style.display = this.hasMorePages ? 'block' : 'none';
                }

                if (this.elements.loadMoreSection) {
                    this.elements.loadMoreSection.style.display = this.hasMorePages ? 'block' : 'none';
                }
            },

            /**
             * Update results count display
             */
            updateResultsCount: function(count) {
                if (this.elements.resultsCount) {
                    this.elements.resultsCount.textContent = count.total || '0';
                }

                if (this.elements.resultsInfo) {
                    const start = ((this.currentPage - 1) * (count.per_page || 12)) + 1;
                    const end = Math.min(start + (count.per_page || 12) - 1, count.total || 0);
                    this.elements.resultsInfo.textContent = `${start}-${end} of ${count.total || 0} properties`;
                }
            },

            /**
             * Update URL without page reload
             */
            updateURL: function() {
                const url = new URL(window.location);

                // Update URL params with current filters
                Object.keys(this.currentFilters).forEach(key => {
                    if (this.currentFilters[key]) {
                        url.searchParams.set(key, this.currentFilters[key]);
                    } else {
                        url.searchParams.delete(key);
                    }
                });

                // Add current view
                url.searchParams.set('view', this.currentView);

                // Update browser history
                window.history.replaceState({}, '', url.toString());
            },

            /**
             * Show loading state
             */
            showLoadingState: function(isReset = false) {
                if (this.elements.loadMoreBtn) {
                    const loadingText = this.elements.loadMoreBtn.querySelector('.hph-load-more-loading');
                    const normalText = this.elements.loadMoreBtn.querySelector('.hph-load-more-text');

                    if (loadingText && normalText) {
                        loadingText.style.display = 'inline';
                        normalText.style.display = 'none';
                    }

                    this.elements.loadMoreBtn.disabled = true;
                }

                if (isReset) {
                    // Show skeleton loading for full refresh
                    document.body.classList.add('hph-archive-loading');
                }
            },

            /**
             * Hide loading state
             */
            hideLoadingState: function() {
                if (this.elements.loadMoreBtn) {
                    const loadingText = this.elements.loadMoreBtn.querySelector('.hph-load-more-loading');
                    const normalText = this.elements.loadMoreBtn.querySelector('.hph-load-more-text');

                    if (loadingText && normalText) {
                        loadingText.style.display = 'none';
                        normalText.style.display = 'inline';
                    }

                    this.elements.loadMoreBtn.disabled = false;
                }

                document.body.classList.remove('hph-archive-loading');
            },

            /**
             * Show error message
             */
            showError: function(message) {
                // Create or update error notification
                const existingError = document.querySelector('.hph-archive-error');
                if (existingError) {
                    existingError.remove();
                }

                const errorDiv = document.createElement('div');
                errorDiv.className = 'hph-archive-error hph-alert hph-alert-error';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>${message}</span>
                    <button type="button" class="hph-alert-close" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                // Insert error message
                const targetContainer = this.elements.gridContainer?.parentNode || document.querySelector('.hph-listings-container');
                if (targetContainer) {
                    targetContainer.insertBefore(errorDiv, targetContainer.firstChild);

                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        if (errorDiv.parentNode) {
                            errorDiv.remove();
                        }
                    }, 5000);

                    // Add close functionality
                    const closeBtn = errorDiv.querySelector('.hph-alert-close');
                    HPH.events.on(closeBtn, 'click', () => {
                        errorDiv.remove();
                    });
                }
            },

            /**
             * Redirect to dedicated map view template
             */
            redirectToMapView: function() {
                // Prevent redirect loop if already on map view
                if (document.body.classList.contains('hph-map-view') ||
                    window.location.search.includes('view=map')) {
                    console.log('Already on map view, skipping redirect');
                    return;
                }

                // Get current URL and parameters
                const url = new URL(window.location);

                // Add view=map parameter
                url.searchParams.set('view', 'map');

                // Navigate to map view
                window.location.href = url.toString();
            },

            /**
             * Hide elements that should not show in map view
             */
            hideElementsForMapView: function() {
                const elementsToHide = document.querySelectorAll('[data-hide-in-views*="map"]');
                elementsToHide.forEach(element => {
                    element.style.display = 'none';
                });
            },

            /**
             * Show elements for standard views (grid/list)
             */
            showElementsForStandardViews: function() {
                const elementsToShow = document.querySelectorAll('[data-hide-in-views*="map"]');
                elementsToShow.forEach(element => {
                    element.style.display = '';
                });
            },

            /**
             * Initialize view based on URL parameters
             */
            initializeView: function() {
                const urlParams = new URLSearchParams(window.location.search);
                const viewFromUrl = urlParams.get('view');

                if (viewFromUrl && ['grid', 'list', 'map'].includes(viewFromUrl)) {
                    this.switchView(viewFromUrl);
                } else {
                    // Use saved preference or default
                    const savedView = localStorage.getItem('hph_archive_view') || 'grid';
                    this.switchView(savedView);
                }
            },

            /**
             * Load saved user preferences
             */
            loadSavedPreferences: function() {
                try {
                    const savedView = localStorage.getItem('hph_archive_view');
                    if (savedView && ['grid', 'list', 'map'].includes(savedView)) {
                        this.currentView = savedView;
                    }
                } catch (e) {
                    // localStorage not available
                }
            },

            /**
             * Save view preference
             */
            saveViewPreference: function(view) {
                try {
                    localStorage.setItem('hph_archive_view', view);
                } catch (e) {
                    // localStorage not available
                }
            },

            /**
             * Initialize map view functionality
             */
            initMapView: function() {
                if (config.debug) {
                    console.log('Initializing map view functionality...');
                }

                // Expose map initialization to global scope for the template
                window.HPHArchiveMap = {
                    initializeMapView: () => {
                        this.initializeMapView();
                    }
                };

                // Cache map-specific elements
                this.elements.mapContainer = document.getElementById('mapbox-listings-map');
                this.elements.mapCards = document.querySelectorAll('.hph-map-card');

                if (config.debug) {
                    console.log('Map view elements cached:', {
                        mapContainer: !!this.elements.mapContainer,
                        cardsFound: this.elements.mapCards.length
                    });
                }
            },

            /**
             * Initialize the actual map with clustering
             */
            initializeMapView: function() {
                if (config.debug) {
                    console.log('Starting map view initialization...');
                }

                const mapContainer = this.elements.mapContainer || document.getElementById('mapbox-listings-map');

                if (!mapContainer) {
                    console.error('Map container not found');
                    return;
                }

                // Check dependencies
                if (typeof mapboxgl === 'undefined') {
                    console.error('Mapbox GL JS not loaded');
                    return;
                }

                if (!window.hph_mapbox_config || !window.hph_mapbox_config.access_token) {
                    console.error('Mapbox configuration not available');
                    return;
                }

                if (!window.hphArchiveMap) {
                    console.error('Archive map data not available');
                    return;
                }

                // Use HPHMap class if available, otherwise fallback
                if (typeof HPHMap !== 'undefined') {
                    this.initializeClusteredMap(mapContainer);
                } else {
                    console.warn('HPHMap class not available, using fallback');
                    this.initializeFallbackMap(mapContainer);
                }
            },

            /**
             * Initialize clustered map using HPHMap
             */
            initializeClusteredMap: function(mapContainer) {
                const mapData = window.hphArchiveMap;
                const listings = mapData.listings || [];
                const center = mapData.center || [-75.1398, 38.7816];

                try {
                    const mapInstance = new HPHMap(mapContainer, {
                        center: center,
                        zoom: 11,
                        styleTheme: 'professional',
                        markerTheme: 'happyPlace'
                    });

                    mapInstance.map.on('load', () => {
                        if (config.debug) {
                            console.log('Map loaded, adding clustered markers for', listings.length, 'listings');
                        }

                        // Add listings with clustering
                        mapInstance.addListingMarkers(listings, {
                            enableClustering: mapData.enable_clustering !== false,
                            clusterRadius: mapData.cluster_radius || 50,
                            clusterMaxZoom: mapData.cluster_max_zoom || 14,
                            showPopup: true,
                            fitBounds: listings.length > 1
                        });

                        // Hide loading indicator
                        const loadingEl = mapContainer.querySelector('.hph-map-loading');
                        if (loadingEl) {
                            loadingEl.style.display = 'none';
                        }

                        // Setup sidebar synchronization
                        this.setupMapSidebarSync(mapInstance);

                        if (config.debug) {
                            console.log('Clustered map initialization complete');
                        }
                    });

                    // Store map instance for later use
                    this.mapInstance = mapInstance;

                } catch (error) {
                    console.error('Error initializing clustered map:', error);
                    this.initializeFallbackMap(mapContainer);
                }
            },

            /**
             * Fallback map initialization without clustering
             */
            initializeFallbackMap: function(mapContainer) {
                const mapData = window.hphArchiveMap;
                const listings = mapData.listings || [];
                const center = mapData.center || [-75.1398, 38.7816];

                try {
                    mapboxgl.accessToken = window.hph_mapbox_config.access_token;

                    const map = new mapboxgl.Map({
                        container: mapContainer,
                        style: 'mapbox://styles/mapbox/light-v11',
                        center: center,
                        zoom: 11
                    });

                    map.on('load', () => {
                        listings.forEach(listing => {
                            if (listing.latitude && listing.longitude) {
                                const marker = new mapboxgl.Marker({ color: '#2563eb' })
                                    .setLngLat([listing.longitude, listing.latitude])
                                    .addTo(map);

                                const popup = new mapboxgl.Popup({ offset: 25 })
                                    .setHTML(this.createPopupHTML(listing));

                                marker.setPopup(popup);
                            }
                        });

                        // Hide loading indicator
                        const loadingEl = mapContainer.querySelector('.hph-map-loading');
                        if (loadingEl) {
                            loadingEl.style.display = 'none';
                        }

                        console.log('Fallback map initialized with', listings.length, 'listings');
                    });

                } catch (error) {
                    console.error('Fallback map initialization failed:', error);
                }
            },

            /**
             * Setup map-sidebar synchronization
             */
            setupMapSidebarSync: function(mapInstance) {
                const mapCards = this.elements.mapCards || document.querySelectorAll('.hph-map-card');

                mapCards.forEach(card => {
                    card.addEventListener('click', (e) => {
                        const listingId = card.dataset.listingId;

                        // Update card states
                        mapCards.forEach(c => c.classList.remove('active'));
                        card.classList.add('active');

                        // Highlight on map
                        if (mapInstance && mapInstance.highlightListingMarker) {
                            mapInstance.highlightListingMarker(listingId);
                        }
                    });
                });

                // Listen for map marker clicks
                document.addEventListener('hph-map-listing-click', (event) => {
                    const listingId = event.detail.listingId;
                    const targetCard = document.querySelector(`[data-listing-id="${listingId}"]`);

                    if (targetCard) {
                        mapCards.forEach(c => c.classList.remove('active'));
                        targetCard.classList.add('active');
                        targetCard.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    }
                });
            },

            /**
             * Create popup HTML for fallback markers
             */
            createPopupHTML: function(listing) {
                const price = listing.price ? `$${Number(listing.price).toLocaleString()}` : '';
                const bedrooms = listing.bedrooms ? `${listing.bedrooms} bed` : '';
                const bathrooms = listing.bathrooms ? `${listing.bathrooms} bath` : '';
                const sqft = listing.square_feet ? `${Number(listing.square_feet).toLocaleString()} sq ft` : '';

                const details = [bedrooms, bathrooms, sqft].filter(Boolean).join(' • ');

                return `
                    <div style="padding: 12px; max-width: 280px;">
                        <h4 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600;">
                            <a href="${listing.permalink}" style="color: #1f2937; text-decoration: none;">${listing.title}</a>
                        </h4>
                        ${price ? `<div style="color: #2563eb; font-weight: 700; font-size: 18px; margin-bottom: 8px;">${price}</div>` : ''}
                        ${details ? `<div style="color: #6b7280; font-size: 14px; margin-bottom: 12px;">${details}</div>` : ''}
                        <a href="${listing.permalink}" style="color: #2563eb; text-decoration: none; font-size: 14px; font-weight: 600;">
                            View Details →
                        </a>
                    </div>
                `;
            }
        };
    });
}