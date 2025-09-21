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
            this.bindEvents();
            this.initFilterToggle();
            this.loadListingsData();
            this.initViewMode();
        }

        bindEvents() {
            // View switcher - works with header view controls and any other view buttons
            document.addEventListener('click', (e) => {
                if (e.target) {
                    const viewBtn = e.target.closest('.hph-view-btn[data-view]');
                    if (viewBtn) {
                        e.preventDefault();
                        const view = viewBtn.dataset.view;
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
            document.addEventListener('mouseover', (e) => {
                if (e.target && typeof e.target.closest === 'function') {
                    const card = e.target.closest('.hph-map-card');
                    if (card) {
                        const listingId = card.dataset.listingId;
                        this.highlightMapMarker(listingId);
                    }
                }
            });

            document.addEventListener('mouseout', (e) => {
                if (e.target && typeof e.target.closest === 'function') {
                    const card = e.target.closest('.hph-map-card');
                    if (card) {
                        this.unhighlightMapMarkers();
                    }
                }
            });

            // Sidebar listing card synchronization with map
            document.addEventListener('mouseover', (e) => {
                if (e.target && typeof e.target.closest === 'function') {
                    const card = e.target.closest('[data-listing-id].listing-card-enhanced, [data-listing-id].hph-listing-card');
                    if (card && this.currentView === 'map' && this.mapbox) {
                        const listingId = card.dataset.listingId;
                        if (listingId && this.mapbox.highlightListingMarker) {
                            this.mapbox.highlightListingMarker(listingId);
                            this.highlightSidebarCard(listingId);
                        }
                    }
                }
            });

            document.addEventListener('mouseout', (e) => {
                if (e.target && typeof e.target.closest === 'function') {
                    const card = e.target.closest('[data-listing-id].listing-card-enhanced, [data-listing-id].hph-listing-card');
                    if (card && this.currentView === 'map' && this.mapbox) {
                        if (this.mapbox.unhighlightAllMarkers) {
                            this.mapbox.unhighlightAllMarkers();
                            this.unhighlightAllSidebarCards();
                        }
                    }
                }
            });

            // Sidebar listing card click to show map popup
            document.addEventListener('click', (e) => {
                if (e.target && typeof e.target.closest === 'function') {
                    const card = e.target.closest('[data-listing-id].listing-card-enhanced, [data-listing-id].hph-listing-card');
                    if (card && this.currentView === 'map' && this.mapbox) {
                        // Only if not clicking on a link or button inside the card
                        if (!e.target.closest('a, button, .btn, [role="button"]')) {
                            const listingId = card.dataset.listingId;
                            if (listingId && this.mapbox.showListingPopup) {
                                e.preventDefault();
                                this.mapbox.showListingPopup(listingId);
                            }
                        }
                    }
                }
            });

            // Listen for map marker click events to highlight sidebar cards
            document.addEventListener('hph-map-listing-click', (e) => {
                if (this.currentView === 'map' && e.detail && e.detail.listingId) {
                    this.highlightSidebarCard(e.detail.listingId);
                    
                    // Scroll to the corresponding sidebar card
                    const card = document.querySelector(`[data-listing-id="${e.detail.listingId}"].listing-card-enhanced, [data-listing-id="${e.detail.listingId}"].hph-listing-card`);
                    if (card) {
                        const sidebar = card.closest('.hph-map-sidebar, .hph-sidebar');
                        if (sidebar) {
                            // Scroll within the sidebar container
                            const cardTop = card.offsetTop;
                            const sidebarTop = sidebar.scrollTop;
                            const sidebarHeight = sidebar.clientHeight;
                            const cardHeight = card.offsetHeight;
                            
                            if (cardTop < sidebarTop || cardTop + cardHeight > sidebarTop + sidebarHeight) {
                                sidebar.scrollTo({
                                    top: cardTop - (sidebarHeight - cardHeight) / 2,
                                    behavior: 'smooth'
                                });
                            }
                        }
                    }
                }
            });
        }

        switchView(view) {

            if (this.currentView === view) {
                return;
            }

            // Update buttons
            document.querySelectorAll('.hph-view-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            });

            const activeBtn = document.querySelector(`.hph-view-btn[data-view="${view}"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
                activeBtn.setAttribute('aria-selected', 'true');
            } else {
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

                // Force display for map view if CSS isn't working
                if (view === 'map') {
                    activeContent.style.display = 'block';
                    activeContent.style.position = 'fixed';
                    activeContent.style.top = '0';
                    activeContent.style.left = '0';
                    activeContent.style.width = '100vw';
                    activeContent.style.height = '100vh';
                    activeContent.style.zIndex = '100';
                    activeContent.style.background = 'white';
                    activeContent.style.margin = '0';
                    activeContent.style.padding = '0';
                    activeContent.style.border = 'none';
                    activeContent.style.boxSizing = 'border-box';

                    // Configure map canvas for proper mobile display
                    const mapCanvas = activeContent.querySelector('.hph-map-canvas');
                    if (mapCanvas) {
                        mapCanvas.style.margin = '0';
                        mapCanvas.style.border = 'none';
                        mapCanvas.style.width = '100%';
                        // Don't override padding and height - let CSS handle mobile responsive header spacing
                    }

                } else if (!activeContent.classList.contains('hph-map-view-layout')) {
                    activeContent.style.display = 'block';
                }

            } else {
                const allContent = document.querySelectorAll('[data-view-content]');
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

                // Force hide regular headers with maximum specificity
                const regularHeaders = document.querySelectorAll(
                    '.hph-site-header-enhanced__wrapper, ' +
                    '.hph-topbar, ' +
                    '.hph-site-header-enhanced__topbar, ' +
                    '#masthead, ' +
                    'header#masthead, ' +
                    '.hph-site-header-enhanced, ' +
                    '.hph-site-header-enhanced__search-bar, ' +
                    '.hph-site-header-enhanced__mobile-menu, ' +
                    '.hph-site-header-enhanced__mobile-overlay'
                );
                regularHeaders.forEach(header => {
                    header.style.setProperty('display', 'none', 'important');
                    header.style.setProperty('visibility', 'hidden', 'important');
                    header.style.setProperty('opacity', '0', 'important');
                    header.style.setProperty('position', 'absolute', 'important');
                    header.style.setProperty('left', '-9999px', 'important');
                    header.style.setProperty('z-index', '-1000', 'important');
                    header.style.setProperty('pointer-events', 'none', 'important');
                });

                // Ensure specialized map header is visible
                const mapHeader = document.querySelector('.hph-map-header');
                if (mapHeader) {
                    mapHeader.style.setProperty('display', 'flex', 'important');
                    mapHeader.style.setProperty('visibility', 'visible', 'important');
                    mapHeader.style.setProperty('opacity', '1', 'important');
                    mapHeader.style.setProperty('position', 'fixed', 'important');
                    mapHeader.style.setProperty('top', '0', 'important');
                    mapHeader.style.setProperty('left', '0', 'important');
                    mapHeader.style.setProperty('right', '0', 'important');
                    mapHeader.style.setProperty('z-index', '1000', 'important');
                } else {
                }

                if (!this.mapbox) {
                    // Use requestAnimationFrame for better performance
                    requestAnimationFrame(() => {
                        this.initMap();
                        // Initialize sidebar with map cards immediately
                        setTimeout(() => {
                            this.initMapSidebar();
                        }, 500);
                    });
                } else {
                    // Map exists, just resize it
                    this.debouncedMapResize();
                    // Show map sidebar immediately
                    this.initMapSidebar();
                }
            } else {
                // Remove map-visible class when leaving map view
                document.body.classList.remove('hph-map-visible');

                // Restore regular headers
                const regularHeaders = document.querySelectorAll(
                    '.hph-site-header-enhanced__wrapper, ' +
                    '.hph-topbar, ' +
                    '.hph-site-header-enhanced__topbar, ' +
                    '#masthead, ' +
                    'header#masthead, ' +
                    '.hph-site-header-enhanced, ' +
                    '.hph-site-header-enhanced__search-bar, ' +
                    '.hph-site-header-enhanced__mobile-menu, ' +
                    '.hph-site-header-enhanced__mobile-overlay'
                );
                regularHeaders.forEach(header => {
                    header.style.removeProperty('display');
                    header.style.removeProperty('visibility');
                    header.style.removeProperty('opacity');
                    header.style.removeProperty('position');
                    header.style.removeProperty('left');
                    header.style.removeProperty('z-index');
                    header.style.removeProperty('pointer-events');
                });

                // Hide specialized map header when leaving map view
                const mapHeader = document.querySelector('.hph-map-header');
                if (mapHeader) {
                    mapHeader.style.setProperty('display', 'none', 'important');
                }

                // Reset any map-specific inline styles that might interfere
                const mapContent = document.querySelector('[data-view-content="map"]');
                if (mapContent) {
                    mapContent.style.removeProperty('z-index');
                    mapContent.style.removeProperty('position');
                    mapContent.style.removeProperty('top');
                    mapContent.style.removeProperty('left');
                    mapContent.style.removeProperty('width');
                    mapContent.style.removeProperty('height');
                    mapContent.style.removeProperty('background');
                    mapContent.style.removeProperty('margin');
                    mapContent.style.removeProperty('padding');
                    mapContent.style.removeProperty('border');
                    mapContent.style.removeProperty('box-sizing');
                    mapContent.style.display = 'none'; // Explicitly hide map content
                }

                // Reset map canvas properties but preserve responsive CSS
                const mapCanvas = document.querySelector('.hph-map-canvas');
                if (mapCanvas) {
                    mapCanvas.style.removeProperty('z-index');
                    mapCanvas.style.removeProperty('position');
                    mapCanvas.style.removeProperty('top');
                    mapCanvas.style.removeProperty('left');
                    mapCanvas.style.removeProperty('width');
                    mapCanvas.style.removeProperty('height');
                    // Don't remove padding-top as it's set by responsive CSS
                }

                // Reset any map panel z-index issues
                const mapPanel = document.querySelector('.hph-map-panel');
                if (mapPanel) {
                    mapPanel.style.removeProperty('z-index');
                    mapPanel.style.removeProperty('position');
                    mapPanel.style.removeProperty('top');
                    mapPanel.style.removeProperty('right');
                }

                // Ensure grid and list content are properly visible with correct z-index
                const gridContent = document.querySelector('[data-view-content="grid"]');
                const listContent = document.querySelector('[data-view-content="list"]');
                if (gridContent) {
                    gridContent.style.removeProperty('z-index');
                    gridContent.style.removeProperty('position');
                }
                if (listContent) {
                    listContent.style.removeProperty('z-index');
                    listContent.style.removeProperty('position');
                }

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
                        status: listing.status,
                        street_address: listing.street_address,
                        city: listing.city,
                        state: listing.state,
                        zip_code: listing.zip_code
                    })).filter(listing => {
                        const hasCoords = listing.lat && listing.lng && !isNaN(listing.lat) && !isNaN(listing.lng);
                        if (!hasCoords) {
                        }
                        return hasCoords;
                    });
                    
                    return;
                } catch (e) {
                }
            }
            
            // Fallback: Extract listing data from map cards or listing cards
            const mapCards = document.querySelectorAll('.hph-map-card, .hph-listing-card, [data-listing-id][data-lat][data-lng]');
            this.listings = Array.from(mapCards).map(card => {
                // Get title from card content if not in attributes
                let title = card.dataset.title || '';
                if (!title) {
                    const titleElement = card.querySelector('h3, .hph-listing-title, .listing-title, a[title]');
                    if (titleElement) {
                        title = titleElement.textContent || titleElement.getAttribute('title') || '';
                    }
                }
                
                // Get image from card
                let image = card.dataset.image || '';
                if (!image) {
                    const imageElement = card.querySelector('img');
                    if (imageElement) {
                        image = imageElement.src;
                    }
                }
                
                // Get URL from card
                let url = card.dataset.permalink || '';
                if (!url) {
                    const linkElement = card.querySelector('a');
                    if (linkElement) {
                        url = linkElement.href;
                    }
                }
                
                return {
                    id: card.dataset.listingId,
                    lat: parseFloat(card.dataset.lat),
                    lng: parseFloat(card.dataset.lng),
                    price: card.dataset.price,
                    address: card.dataset.address || title.trim(),
                    title: title.trim(),
                    permalink: url,
                    featured_image: image,
                    bedrooms: card.dataset.bedrooms,
                    bathrooms: card.dataset.bathrooms,
                    square_feet: card.dataset.sqft || card.dataset.squareFeet,
                    status: card.dataset.status,
                    street_address: card.dataset.streetAddress,
                    city: card.dataset.city,
                    state: card.dataset.state,
                    zip_code: card.dataset.zipCode,
                    element: card
                };
            }).filter(listing => listing.lat && listing.lng);
            
        }
        
        buildAddress(listing) {
            // Try to build from components first
            const parts = [];
            if (listing.street_address) parts.push(listing.street_address);
            if (listing.city) parts.push(listing.city);
            if (listing.state) parts.push(listing.state);
            if (listing.zip_code) parts.push(listing.zip_code);
            
            if (parts.length > 0) {
                return parts.join(', ');
            }
            
            // Fallback to the data-address attribute if component building fails
            if (listing.address) {
                return listing.address;
            }
            
            // Final fallback
            return 'View Property Details';
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

        waitForHPHMap() {
            return new Promise((resolve) => {
                const checkHPHMap = () => {
                    if (typeof HPHMap !== 'undefined') {
                        resolve();
                    } else {
                        setTimeout(checkHPHMap, 100);
                    }
                };
                checkHPHMap();
            });
        }

        async initMap() {

            // Check if we have Mapbox configuration
            if (typeof window.hph_mapbox_config === 'undefined') {
                this.showMapError('Map configuration not available');
                return;
            }

            // If no token is configured, show configuration message
            if (window.hph_mapbox_config.has_token === false) {
                this.showMapConfigurationMessage();
                return;
            }

            // Wait for HPH Map component to be available
            if (typeof HPHMap === 'undefined') {
                await this.waitForHPHMap();
                if (typeof HPHMap === 'undefined') {
                    this.showMapError('Map component failed to load');
                    return;
                }
            }

            const mapContainer = document.getElementById('mapbox-listings-map');
            if (!mapContainer) {
                return;
            }


            try {
                // Initialize HPH Map component - this handles all Mapbox setup internally

                this.hphMap = new HPHMap(mapContainer, {
                    style: 'mapbox://styles/mapbox/light-v11',
                    center: this.getMapCenter(),
                    zoom: 12,
                    navigationControl: true,
                    attributionControl: false,
                    styleTheme: 'professional'
                });

                // Store reference to Mapbox map instance
                this.mapbox = this.hphMap.map;

                // Store reference on container for external access
                mapContainer.hphMap = this.hphMap;

                // Wait for map to load then add markers
                this.mapbox.on('load', () => {
                    try {
                        this.addMapMarkers();
                        // Initial viewport filtering after map is loaded
                        setTimeout(() => {
                            if (this.currentView === 'map') {
                                this.updateSidebarForViewport();
                            }
                        }, 1000);
                    } catch (markerError) {
                    }
                });

                // Handle resize when switching to map view
                this.mapbox.on('style.load', () => {
                    requestAnimationFrame(() => {
                        if (this.mapbox && this.currentView === 'map') {
                            this.mapbox.resize();
                        }
                    });
                });

                // Add viewport-responsive sidebar filtering
                this.setupViewportResponsiveFiltering();

                // Setup map search functionality
                this.setupMapSearchFunctionality();

            } catch (error) {
                this.showMapError('Failed to initialize map');
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

        setupViewportResponsiveFiltering() {
            if (!this.mapbox) return;


            // Debounce viewport updates for performance
            let viewportUpdateTimeout;
            const debouncedViewportUpdate = () => {
                clearTimeout(viewportUpdateTimeout);
                viewportUpdateTimeout = setTimeout(() => {
                    this.updateSidebarForViewport();
                }, 250);
            };

            // Listen for map movement events
            this.mapbox.on('moveend', debouncedViewportUpdate);
            this.mapbox.on('zoomend', debouncedViewportUpdate);
            this.mapbox.on('dragend', debouncedViewportUpdate);
        }

        updateSidebarForViewport() {
            if (!this.mapbox || this.currentView !== 'map') return;


            // Get current map bounds
            const bounds = this.mapbox.getBounds();
            const visibleListings = [];

            // Filter listings that are visible in current viewport
            this.listings.forEach(listing => {
                if (listing.lat && listing.lng) {
                    const isInBounds = bounds.contains([listing.lng, listing.lat]);
                    if (isInBounds) {
                        visibleListings.push(listing);
                    }
                }
            });


            // Update sidebar cards
            this.renderSidebarCards(visibleListings);

            // Update results count
            this.updateResultsCount(visibleListings.length);
        }

        renderSidebarCards(listings) {
            const sidebar = document.querySelector('.hph-map-sidebar .hph-sidebar-content');
            if (!sidebar) return;

            // Show loading state briefly
            sidebar.style.opacity = '0.7';

            if (listings.length === 0) {
                sidebar.innerHTML = `
                    <div class="hph-text-center hph-py-xl">
                        <div class="hph-mb-md">
                            <i class="fas fa-search hph-text-gray-300" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-sm">No Properties in View</h3>
                        <p class="hph-text-gray-600">Zoom out or pan the map to see more listings.</p>
                    </div>
                `;
            } else {
                // Render map-style cards for visible listings
                const cardsHtml = listings.map(listing => this.createMapCardHTML(listing)).join('');
                sidebar.innerHTML = `<div class="hph-map-cards-container">${cardsHtml}</div>`;
            }

            // Restore opacity
            setTimeout(() => {
                sidebar.style.opacity = '1';
            }, 100);
        }

        createMapCardHTML(listing) {
            // Create compact horizontal map-style card HTML
            const price = listing.price ? `$${parseInt(listing.price).toLocaleString()}` : 'Price on Request';
            const photoUrl = listing.featured_image || listing.photo || '/wp-content/themes/happy-place-theme/assets/images/placeholder-listing.jpg';
            const title = listing.title || listing.address || 'Property';
            const location = listing.address || `${listing.city || ''}, ${listing.state || ''}`.trim();
            const url = listing.permalink || listing.url || '#';

            return `
                <div class="hph-map-card">
                    <div class="hph-map-card-image">
                        <img src="${photoUrl}" alt="${title}" loading="lazy">
                        <div class="hph-map-card-price">${price}</div>
                    </div>
                    <div class="hph-map-card-content">
                        <h4 class="hph-map-card-title">
                            <a href="${url}">${title}</a>
                        </h4>
                        <p class="hph-map-card-location">${location}</p>
                        <div class="hph-map-card-stats">
                            ${listing.bedrooms ? `<span><i class="fas fa-bed"></i> ${listing.bedrooms}</span>` : ''}
                            ${listing.bathrooms ? `<span><i class="fas fa-bath"></i> ${listing.bathrooms}</span>` : ''}
                            ${listing.square_feet || listing.sqft ? `<span><i class="fas fa-ruler-combined"></i> ${listing.square_feet || listing.sqft} sq ft</span>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }

        updateResultsCount(count) {
            const countElement = document.querySelector('[data-results-count]');
            if (countElement) {
                countElement.textContent = count.toLocaleString();
            }
        }

        setupMapSearchFunctionality() {
            const mapSearchForm = document.querySelector('.hph-map-search-form');
            if (!mapSearchForm) return;


            mapSearchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleMapSearch();
            });

            // Real-time search as user types
            const searchInput = mapSearchForm.querySelector('.hph-map-search-input');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', (e) => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        this.handleMapSearch();
                    }, 500);
                });
            }

            // Filter changes
            const filterSelects = mapSearchForm.querySelectorAll('.hph-map-filter-select');
            filterSelects.forEach(select => {
                select.addEventListener('change', () => {
                    this.handleMapSearch();
                });
            });
        }

        handleMapSearch() {
            const form = document.querySelector('.hph-map-search-form');
            if (!form) return;


            const formData = new FormData(form);
            const searchParams = new URLSearchParams(formData);

            // Show loading state
            const sidebar = document.querySelector('.hph-map-sidebar .hph-sidebar-content');
            if (sidebar) {
                sidebar.innerHTML = `
                    <div class="hph-map-loading-sidebar">
                        <div class="hph-loading-spinner"></div>
                        <p>Searching properties...</p>
                    </div>
                `;
            }

            // Make AJAX request to get filtered listings
            fetch(`${window.hphAjax.ajaxurl}?action=hph_filter_listings&${searchParams}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {

                    // Update listings data
                    this.listings = data.data.listings;

                    // Re-render map markers
                    this.clearMapMarkers();
                    this.addMapMarkers();

                    // Update sidebar
                    this.renderSidebarCards(this.listings);
                    this.updateResultsCount(this.listings.length);
                } else {
                    this.showSearchError();
                }
            })
            .catch(error => {
                this.showSearchError();
            });
        }

        showSearchError() {
            const sidebar = document.querySelector('.hph-map-sidebar .hph-sidebar-content');
            if (sidebar) {
                sidebar.innerHTML = `
                    <div class="hph-text-center hph-py-xl">
                        <div class="hph-mb-md">
                            <i class="fas fa-exclamation-triangle hph-text-red-300" style="font-size: 2rem;"></i>
                        </div>
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-sm">Search Error</h3>
                        <p class="hph-text-gray-600">Please try again.</p>
                    </div>
                `;
            }
        }

        initMapSidebar() {

            // Immediately show all listings as map cards when entering map view
            if (this.listings && this.listings.length > 0) {
                this.renderSidebarCards(this.listings);
                this.updateResultsCount(this.listings.length);
            } else {
                // Show empty state if no listings
                const sidebar = document.querySelector('.hph-map-sidebar .hph-sidebar-content');
                if (sidebar) {
                    sidebar.innerHTML = `
                        <div class="hph-text-center hph-py-xl">
                            <div class="hph-mb-md">
                                <i class="fas fa-home hph-text-gray-300" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="hph-text-lg hph-font-semibold hph-mb-sm">No Properties Found</h3>
                            <p class="hph-text-gray-600">Try adjusting your search criteria.</p>
                        </div>
                    `;
                }
            }
        }

        addMapMarkers() {
            if (!this.hphMap || !this.listings || this.listings.length === 0) {
                return;
            }


            // Clear existing markers first
            this.clearMapMarkers();

            // Prepare listings data in the correct format for HPH Map clustering
            const formattedListings = this.listings.map(listing => ({
                id: listing.id,
                title: listing.title || listing.address || 'Property',
                longitude: parseFloat(listing.lng),
                latitude: parseFloat(listing.lat),
                price: listing.price || 0,
                street_address: listing.street_address || listing.address || '',
                city: listing.city || '',
                state: listing.state || '',
                zip_code: listing.zip_code || '',
                status: listing.status || 'active',
                featured_image: listing.featured_image || '',
                bedrooms: listing.bedrooms || 0,
                bathrooms: listing.bathrooms || 0,
                square_feet: listing.square_feet || 0,
                permalink: listing.permalink || '#'
            })).filter(listing => {
                // Only include listings with valid coordinates
                return !isNaN(listing.longitude) && !isNaN(listing.latitude);
            });


            if (formattedListings.length === 0) {
                return;
            }

            // Use HPH Map clustering functionality
            try {
                this.hphMap.addListingMarkers(formattedListings, {
                    enableClustering: true,
                    clusterRadius: 50,
                    clusterMaxZoom: 14,
                    showPopup: true,
                    fitBounds: formattedListings.length > 1
                });


                // Listen for listing clicks to scroll to corresponding card
                this.mapbox.on('listing-click', (e) => {
                    const listingId = e.listing.id;
                    if (listingId) {
                        this.scrollToMapCard(listingId);
                    }
                });

            } catch (error) {
                // Fallback to individual markers
                this.addIndividualMarkers(formattedListings);
            }
        }

        addIndividualMarkers(listings) {
            // Fallback method for individual markers without clustering
            listings.forEach((listing, index) => {
                if (!listing.latitude || !listing.longitude) {
                    return;
                }

                // Create marker element
                const markerElement = document.createElement('div');
                markerElement.className = 'hph-map-marker';
                markerElement.innerHTML = `
                    <div class="hph-marker-pin">
                        <div class="hph-marker-price">$${this.formatPrice(listing.price)}</div>
                    </div>
                `;

                // Create marker
                const marker = new mapboxgl.Marker({
                    element: markerElement,
                    anchor: 'bottom'
                })
                    .setLngLat([listing.longitude, listing.latitude])
                    .addTo(this.mapbox);

                // Create popup
                const popup = new mapboxgl.Popup({ 
                    offset: 25,
                    closeButton: true,
                    closeOnClick: true,
                    maxWidth: '320px',
                    className: 'hph-listing-popup'
                })
                    .setHTML(this.createPopupHTML(listing));

                marker.setPopup(popup);

                // Store marker reference
                this.mapMarkers.push({
                    marker,
                    listingId: listing.id,
                    element: markerElement
                });

                // Add click handler to scroll to card
                let clickTimeout;
                markerElement.addEventListener('click', () => {
                    if (clickTimeout) return;
                    clickTimeout = setTimeout(() => {
                        this.scrollToMapCard(listing.id);
                        clickTimeout = null;
                    }, 100);
                });
            });

            // Fit bounds if multiple listings
            if (listings.length > 1) {
                this.fitMapToMarkers();
            }
        }

        createPopupHTML(listing) {
            return `
                <div class="hph-card hph-card--elevated hph-bg-white hph-overflow-hidden hph-max-w-80" data-popup-version="1.0.4">
                    ${listing.featured_image && !listing.featured_image.includes('placeholder') ? `
                        <div class="hph-relative hph-h-48 hph-overflow-hidden">
                            <img src="${listing.featured_image}" 
                                 alt="${listing.title || 'Property'}" 
                                 class="hph-w-full hph-h-full hph-object-cover hph-transition-transform hph-duration-300 hover:hph-scale-105"
                                 loading="lazy"
                                 onerror="this.parentElement.style.display='none'">
                            
                            ${listing.status ? `
                                <div class="hph-absolute hph-top-3 hph-left-3">
                                    <span class="hph-inline-flex hph-items-center hph-px-2 hph-py-1 hph-rounded-md hph-text-xs hph-font-semibold hph-shadow-sm
                                        ${listing.status.toLowerCase() === 'active' ? 'hph-bg-green-500 hph-text-white' : 
                                          listing.status.toLowerCase() === 'pending' ? 'hph-bg-yellow-500 hph-text-white' : 
                                          listing.status.toLowerCase() === 'sold' ? 'hph-bg-red-500 hph-text-white' : 
                                          'hph-bg-blue-500 hph-text-white'}">
                                        ${listing.status}
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
                        ${listing.price ? `
                            <div class="hph-text-primary hph-font-bold hph-text-xl hph-mb-2">
                                $${Number(listing.price).toLocaleString()}
                            </div>
                        ` : ''}
                        
                        <!-- Street Address -->
                        <h3 class="hph-text-gray-900 hph-font-semibold hph-text-base hph-mb-1 hph-line-clamp-1">
                            ${listing.street_address || listing.title || 'Property Address'}
                        </h3>
                        
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
                                        <span>${Number(listing.square_feet).toLocaleString()} sq ft</span>
                                    </div>
                                ` : ''}
                            </div>
                        ` : `<div class="hph-mb-4"></div>`}
                        
                        <!-- Action Button -->
                        <a href="${listing.permalink || '#'}" 
                           class="hph-btn hph-btn-primary hph-w-full hph-text-center hph-py-3 hph-font-semibold hph-transition-all hph-duration-200 hover:hph-shadow-lg"
                           onclick="event.stopPropagation();">
                            View Details
                            <i class="fas fa-arrow-right hph-ml-2"></i>
                        </a>
                    </div>
                </div>
            `;
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

        /**
         * Highlight a specific sidebar listing card
         */
        highlightSidebarCard(listingId) {
            if (!listingId) return;
            
            const card = document.querySelector(`[data-listing-id="${listingId}"].listing-card-enhanced, [data-listing-id="${listingId}"].hph-listing-card`);
            if (card) {
                card.classList.add('hph-highlighted');
                
                // Add subtle animation
                card.style.transform = 'scale(1.02)';
                card.style.transition = 'all 0.2s ease';
                card.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                card.style.zIndex = '10';
            }
        }

        /**
         * Remove highlight from all sidebar cards
         */
        unhighlightAllSidebarCards() {
            const cards = document.querySelectorAll('[data-listing-id].listing-card-enhanced.hph-highlighted, [data-listing-id].hph-listing-card.hph-highlighted');
            cards.forEach(card => {
                card.classList.remove('hph-highlighted');
                
                // Reset styles
                card.style.transform = '';
                card.style.transition = '';
                card.style.boxShadow = '';
                card.style.zIndex = '';
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

        clearMapMarkers() {
            // Clear markers using HPH Map component if available
            if (this.hphMap && typeof this.hphMap.clearMapMarkers === 'function') {
                this.hphMap.clearMapMarkers();
            }

            // Also clear any individual markers array as fallback
            if (this.mapMarkers && this.mapMarkers.length > 0) {
                this.mapMarkers.forEach(markerData => {
                    if (markerData.marker && markerData.marker.remove) {
                        markerData.marker.remove();
                    }
                });
                this.mapMarkers = [];
            }
        }

        showMapConfigurationMessage() {
            const mapContainer = document.getElementById('mapbox-listings-map');
            if (mapContainer) {
                mapContainer.innerHTML = `
                    <div class="hph-map-error">
                        <div class="hph-error-content">
                            <i class="fas fa-map-marked-alt" style="color: #0ea5e9;"></i>
                            <h3>Map Configuration Required</h3>
                            <p>To enable map view, please configure your Mapbox access token in the Happy Place plugin settings.</p>
                            <div style="margin-top: 1.5rem;">
                                <small style="color: #666; font-style: italic;">
                                    Administrators can configure this in the WordPress admin panel under Happy Place settings.
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        showMapError(message = 'Unable to load the map view. Please try the grid or list view instead.') {
            const mapContainer = document.getElementById('mapbox-listings-map');
            if (mapContainer) {
                mapContainer.innerHTML = `
                    <div class="hph-map-error">
                        <div class="hph-error-content">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Map Unavailable</h3>
                            <p>${message}</p>
                        </div>
                    </div>
                `;
            }
        }

        async handleAjaxSearch(form) {

            // Check if hph_ajax is available
            if (typeof hph_ajax === 'undefined') {
                return;
            }

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
            submitBtn.disabled = true;

            // Get form data
            const formData = new FormData(form);
            const formDataObj = Object.fromEntries(formData.entries());

            // Debug: Show what we're sending

            // Add AJAX action and nonce for security
            formData.append('action', 'hph_filter_listings');
            formData.append('nonce', hph_ajax.nonce);

            
            try {
                const response = await fetch(hph_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update results count using new data attributes
                    const resultsCounts = document.querySelectorAll('[data-results-count]');
                    if (resultsCounts.length && data.data.total !== undefined) {
                        resultsCounts.forEach(element => {
                            element.textContent = data.data.total.toLocaleString();
                        });
                    }

                    // Update results text in hero
                    const resultsText = document.querySelector('[data-results-text]');
                    if (resultsText && data.data.results_text) {
                        resultsText.innerHTML = data.data.results_text;
                    }

                    // Update active filter badges
                    this.updateActiveFilters(formDataObj);

                    // Update listings containers with the new HTML
                    const gridContainer = document.querySelector('[data-listings-container="grid"]');
                    const listContainer = document.querySelector('[data-listings-container="list"]');

                    // Use the unified HTML from the new AJAX handler
                    const newHTML = data.data.html || data.data.grid_html;

                    if (gridContainer && newHTML) {
                        gridContainer.innerHTML = newHTML;
                    }

                    if (listContainer && (data.data.list_html || newHTML)) {
                        listContainer.innerHTML = data.data.list_html || newHTML;
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
                    this.showSearchError();
                }
                
            } catch (error) {
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

            if (!this.hphMap) {
                return;
            }

            // Debug map methods availability
            console.log('HPH Map methods:', {
                clearMapMarkers: typeof this.hphMap.clearMapMarkers,
                removeZipCodeBoundary: typeof this.hphMap.removeZipCodeBoundary,
                addListingMarkers: typeof this.hphMap.addListingMarkers
            });

            // Clear existing markers
            if (typeof this.hphMap.clearMapMarkers === 'function') {
                this.hphMap.clearMapMarkers();
            }
            if (typeof this.hphMap.removeZipCodeBoundary === 'function') {
                this.hphMap.removeZipCodeBoundary();
            }

            // Add new listings
            if (mapData && mapData.length > 0) {

                // Format the data properly for HPH Map
                const formattedMapData = mapData.map(listing => ({
                    id: listing.id,
                    title: listing.title || 'Property',
                    longitude: parseFloat(listing.longitude),
                    latitude: parseFloat(listing.latitude),
                    price: listing.price || 0,
                    street_address: listing.street_address || '',
                    city: listing.city || '',
                    state: listing.state || '',
                    zip_code: listing.zip_code || '',
                    status: listing.status || 'active',
                    featured_image: listing.featured_image || '',
                    bedrooms: listing.bedrooms || 0,
                    bathrooms: listing.bathrooms || 0,
                    square_feet: listing.square_feet || 0,
                    permalink: listing.permalink || '#'
                })).filter(listing => {
                    return !isNaN(listing.longitude) && !isNaN(listing.latitude);
                });

                const markerOptions = {
                    enableClustering: true,
                    clusterRadius: 50,
                    clusterMaxZoom: 14,
                    showPopup: true,
                    fitBounds: formattedMapData.length > 1
                };

                // Add zip code boundary if filtering by zip code
                if (zipCode && typeof this.hphMap.addZipCodeBoundary === 'function') {
                    markerOptions.showZipBoundary = true;
                    markerOptions.zipCode = zipCode;
                }

                try {
                    this.hphMap.addListingMarkers(formattedMapData, markerOptions);

                    // Update sidebar listings
                    this.updateMapSidebar(mapData);
                } catch (error) {
                }
            } else {
                // Update sidebar to show empty state
                this.updateMapSidebar([]);
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
            
            // Prevent HPH Map component from auto-initializing our container
            const mapContainer = document.getElementById('mapbox-listings-map');
            if (mapContainer) {
                // Remove data-hph-map attribute to prevent conflicts
                mapContainer.removeAttribute('data-hph-map');
            }
            
            window.hphArchiveEnhanced = new ArchiveListingEnhanced();
        }
    });

    // Map marker styles now loaded from archive-map-fixes.css

})();
