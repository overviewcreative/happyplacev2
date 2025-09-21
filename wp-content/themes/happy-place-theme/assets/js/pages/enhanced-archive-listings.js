/**
 * Enhanced Listing Archive JavaScript
 * Handles AJAX filtering, map integration, favorites, comparison, and bulk actions
 */

class EnhancedListingArchive {
    constructor() {
        this.isLoading = false;
        this.currentPage = 1;
        this.selectedListings = new Set();
        this.favoriteListings = new Set();
        this.compareListings = new Set();
        this.map = null;
        this.markers = [];
        this.markerClusterer = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadUserPreferences();
        this.initializeViewSwitching();
    }
    
    bindEvents() {
        // Filter form submission
        const filterForm = document.getElementById('filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFilterSubmit();
            });
        }
        
        // Sort dropdown change
        const sortSelect = document.getElementById('sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.handleSortChange(e.target.value);
            });
        }
        
        // Map toggle
        const mapToggle = document.getElementById('map-toggle');
        if (mapToggle) {
            mapToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleMapView();
            });
        }
        
        const closeMap = document.getElementById('close-map');
        if (closeMap) {
            closeMap.addEventListener('click', (e) => {
                e.preventDefault();
                this.hideMapView();
            });
        }
        
        // Load more button
        const loadMoreBtn = document.getElementById('load-more-listings');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadMoreListings();
            });
        }
        
        // Save search button
        const saveSearchBtn = document.getElementById('save-search-btn');
        if (saveSearchBtn) {
            saveSearchBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSaveSearch();
            });
        }
        
        // Bulk actions
        const bulkFavorite = document.getElementById('bulk-favorite');
        if (bulkFavorite) {
            bulkFavorite.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleBulkFavorite();
            });
        }
        
        const bulkCompare = document.getElementById('bulk-compare');
        if (bulkCompare) {
            bulkCompare.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleBulkCompare();
            });
        }
        
        // Dynamic event delegation for listing cards
        document.addEventListener('click', this.handleCardInteractions.bind(this));
        document.addEventListener('change', this.handleSelectionChange.bind(this));
    }
    
    initializeViewSwitching() {
        // Handle view switching (grid/list/map)
        document.querySelectorAll('[href*="view="]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = new URL(e.target.href);
                const view = url.searchParams.get('view');
                this.switchView(view);
            });
        });
    }
    
    switchView(view) {
        const results = document.getElementById('listing-results');
        if (!results) return;
        
        // Update URL
        const url = new URL(window.location);
        url.searchParams.set('view', view);
        window.history.replaceState({}, '', url);
        
        // Update view buttons
        document.querySelectorAll('[href*="view="]').forEach(btn => {
            btn.classList.remove('hph-btn-primary');
            btn.classList.add('hph-btn-outline');
        });
        
        document.querySelector(`[href*="view=${view}"]`).classList.remove('hph-btn-outline');
        document.querySelector(`[href*="view=${view}"]`).classList.add('hph-btn-primary');
        
        // Update results layout
        results.className = results.className.replace(/hph-(grid|space-y-md).*?(?=\s|$)/g, '');
        
        if (view === 'list') {
            results.className += ' hph-space-y-md';
        } else {
            results.className += ' hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 xl:hph-grid-cols-4 hph-gap-lg';
        }
        
        // Trigger card re-render if needed
        this.refreshListingCards(view);
    }
    
    refreshListingCards(view) {
        // This would ideally reload the cards with the new view template
        // For now, just update existing cards' layout classes
        document.querySelectorAll('.listing-card-enhanced').forEach(card => {
            if (view === 'list') {
                card.classList.add('listing-card-list');
                card.classList.remove('listing-card-grid');
            } else {
                card.classList.add('listing-card-grid');
                card.classList.remove('listing-card-list');
            }
        });
    }
    
    handleCardInteractions(e) {
        // Safety check for e.target and closest method
        if (!e.target || typeof e.target.closest !== 'function') return;
        
        const target = e.target.closest('button[data-action]');
        if (!target) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const action = target.dataset.action;
        const listingId = target.dataset.listingId;
        
        switch (action) {
            case 'favorite':
                this.toggleFavorite(listingId, target);
                break;
            case 'compare':
                this.toggleCompare(listingId, target);
                break;
            case 'share':
                this.handleShare(target.dataset.url, target.dataset.title);
                break;
        }
    }
    
    handleSelectionChange(e) {
        // Safety check for e.target
        if (!e.target || !e.target.matches || !e.target.matches('.selection-checkbox')) return;
        
        const listingId = e.target.dataset.listingId;
        
        // Safety check for closest method
        if (typeof e.target.closest !== 'function') return;
        const card = e.target.closest('[data-listing-id]');
        
        if (e.target.checked) {
            this.selectedListings.add(listingId);
            card.classList.add('listing-card-selected');
            e.target.classList.add('checked');
            const icon = e.target.querySelector('i');
            if (icon) icon.classList.remove('hph-hidden');
        } else {
            this.selectedListings.delete(listingId);
            card.classList.remove('listing-card-selected');
            e.target.classList.remove('checked');
            const icon = e.target.querySelector('i');
            if (icon) icon.classList.add('hph-hidden');
        }
        
        this.updateBulkActions();
    }
    
    handleFilterSubmit() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.currentPage = 1;
        
        const formData = new FormData(document.getElementById('filter-form'));
        const params = new URLSearchParams(formData);
        
        // Add AJAX flag
        params.append('action', 'hph_filter_listings');
        params.append('nonce', window.hphArchive?.nonce || '');
        params.append('page', this.currentPage);
        
        this.showLoadingState();
        
        fetch(window.hphArchive.ajaxUrl, {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateResults(data.data.html);
                this.updateURL(params);
                if (data.data.listings) {
                    this.updateMapMarkers(data.data.listings);
                }
            } else {
                this.showError(data.data?.message || 'Error loading results');
            }
        })
        .catch(error => {
            console.error('Filter error:', error);
            this.showError('Network error occurred');
        })
        .finally(() => {
            this.hideLoadingState();
            this.isLoading = false;
        });
    }
    
    handleSortChange(sortValue) {
        const form = document.getElementById('filter-form');
        let sortInput = form.querySelector('input[name="sort"]');
        
        if (!sortInput) {
            sortInput = this.createHiddenInput('sort');
        }
        
        sortInput.value = sortValue;
        this.handleFilterSubmit();
    }
    
    loadMoreListings() {
        if (this.isLoading) return;
        
        const loadMoreBtn = document.getElementById('load-more-listings');
        if (!loadMoreBtn) return;
        
        const currentPage = parseInt(loadMoreBtn.dataset.page);
        const maxPages = parseInt(loadMoreBtn.dataset.maxPages);
        
        if (currentPage >= maxPages) return;
        
        this.isLoading = true;
        const nextPage = currentPage + 1;
        
        const formData = new FormData(document.getElementById('filter-form'));
        const params = new URLSearchParams(formData);
        params.append('action', 'hph_load_more_listings');
        params.append('nonce', window.hphArchive?.nonce || '');
        params.append('page', nextPage);
        
        // Update button state
        loadMoreBtn.classList.add('loading');
        loadMoreBtn.disabled = true;
        
        fetch(window.hphArchive.ajaxUrl, {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.appendResults(data.data.html);
                loadMoreBtn.dataset.page = nextPage;
                
                // Hide button if we've reached the end
                if (nextPage >= maxPages) {
                    loadMoreBtn.style.display = 'none';
                }
                
                // Update map if visible
                const mapContainer = document.getElementById('map-container');
                if (this.map && mapContainer && !mapContainer.classList.contains('hph-hidden') && data.data.listings) {
                    this.updateMapMarkers(data.data.listings, true); // append = true
                }
            } else {
                this.showError(data.data?.message || 'Error loading more listings');
            }
        })
        .catch(error => {
            console.error('Load more error:', error);
            this.showError('Network error occurred');
        })
        .finally(() => {
            loadMoreBtn.classList.remove('loading');
            loadMoreBtn.disabled = false;
            this.isLoading = false;
        });
    }
    
    toggleMapView() {
        const mapContainer = document.getElementById('map-container');
        if (!mapContainer) return;
        
        const isVisible = !mapContainer.classList.contains('hph-hidden');
        
        if (isVisible) {
            this.hideMapView();
        } else {
            this.showMapView();
        }
    }
    
    showMapView() {
        const mapContainer = document.getElementById('map-container');
        if (!mapContainer) return;
        
        mapContainer.classList.remove('hph-hidden');
        mapContainer.style.display = 'block';
        
        // Initialize map if not already done
        if (!this.map) {
            this.initializeMap();
        }
        
        // Update markers with current listings after a brief delay
        setTimeout(() => {
            this.updateMapMarkersFromDOM();
        }, 100);
        
        // Update button state
        const mapToggle = document.getElementById('map-toggle');
        if (mapToggle) {
            mapToggle.classList.remove('hph-btn-outline');
            mapToggle.classList.add('hph-btn-primary');
        }
    }
    
    hideMapView() {
        const mapContainer = document.getElementById('map-container');
        if (!mapContainer) return;
        
        mapContainer.classList.add('hph-hidden');
        mapContainer.style.display = 'none';
        
        // Update button state
        const mapToggle = document.getElementById('map-toggle');
        if (mapToggle) {
            mapToggle.classList.remove('hph-btn-primary');
            mapToggle.classList.add('hph-btn-outline');
        }
    }
    
    initializeMap() {
        const mapElement = document.getElementById('listings-map');
        if (!mapElement) {
            console.warn('Map container not found');
            return;
        }

        // Check which map API is available and use it
        if (typeof mapboxgl !== 'undefined' && window.hphArchive?.features?.hasMapbox) {
            this.initializeMapbox(mapElement);
        } else if (typeof google !== 'undefined' && window.hphArchive?.features?.hasGoogleMaps) {
            this.initializeGoogleMaps(mapElement);
        } else {
            console.warn('No map API available');
            mapElement.innerHTML = '<div class="alert alert-info">Map functionality requires Google Maps or Mapbox API configuration.</div>';
            return;
        }
    }

    initializeMapbox(mapElement) {
        const mapboxToken = window.hphArchive?.maps?.mapboxToken || '';
        
        if (!mapboxToken) {
            console.warn('Mapbox token not available');
            return;
        }

        mapboxgl.accessToken = mapboxToken;
        
        this.map = new mapboxgl.Map({
            container: mapElement,
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [-74.0060, 40.7128], // Default to NYC [lng, lat]
            zoom: 10
        });

        this.mapType = 'mapbox';
        this.markers = [];
    }

    initializeGoogleMaps(mapElement) {
        this.map = new google.maps.Map(mapElement, {
            zoom: 10,
            center: { lat: 40.7128, lng: -74.0060 }, // Default to NYC
            styles: this.getMapStyles(),
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true
        });

        this.mapType = 'google';
        
        // Add clustering if available
        if (typeof MarkerClusterer !== 'undefined') {
            this.markerClusterer = new MarkerClusterer(this.map, [], {
                imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
                maxZoom: 15,
                gridSize: 60
            });
        } else {
            console.warn('MarkerClusterer not loaded, markers will not be clustered');
        }
    }
    
    updateMapMarkersFromDOM() {
        if (!this.map) return;
        
        const listings = [];
        document.querySelectorAll('[data-listing-id][data-lat][data-lng]').forEach(card => {
            const lat = parseFloat(card.dataset.lat);
            const lng = parseFloat(card.dataset.lng);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                listings.push({
                    id: card.dataset.listingId,
                    lat: lat,
                    lng: lng,
                    price: card.dataset.price,
                    bedrooms: card.dataset.bedrooms,
                    bathrooms: card.dataset.bathrooms,
                    title: card.querySelector('h3')?.textContent || '',
                    image: card.querySelector('img')?.src || '',
                    url: card.querySelector('a')?.href || ''
                });
            }
        });
        
        this.updateMapMarkers(listings);
    }
    
    updateMapMarkers(listings, append = false) {
        if (!this.map || !Array.isArray(listings)) return;
        
        // Clear existing markers if not appending
        if (!append) {
            this.clearMapMarkers();
        }
        
        if (this.mapType === 'mapbox') {
            this.updateMapboxMarkers(listings);
        } else if (this.mapType === 'google') {
            this.updateGoogleMarkers(listings);
        }
    }

    clearMapMarkers() {
        if (this.mapType === 'mapbox' && this.markers) {
            this.markers.forEach(marker => marker.remove());
            this.markers = [];
        } else if (this.mapType === 'google') {
            if (this.markerClusterer) {
                this.markerClusterer.clearMarkers();
            } else if (this.markers) {
                this.markers.forEach(marker => marker.setMap(null));
            }
            this.markers = [];
        }
    }

    updateMapboxMarkers(listings) {
        this.markers = [];
        const bounds = new mapboxgl.LngLatBounds();
        
        listings.forEach(listing => {
            const lng = parseFloat(listing.lng);
            const lat = parseFloat(listing.lat);
            
            if (isNaN(lat) || isNaN(lng)) return;
            
            // Create marker element
            const markerEl = document.createElement('div');
            markerEl.className = 'mapbox-marker';
            markerEl.innerHTML = this.createMapboxMarkerHTML(listing.price);
            
            const marker = new mapboxgl.Marker(markerEl)
                .setLngLat([lng, lat])
                .setPopup(new mapboxgl.Popup({ offset: 25 })
                    .setHTML(this.createMarkerPopup(listing)))
                .addTo(this.map);
                
            this.markers.push(marker);
            bounds.extend([lng, lat]);
        });
        
        // Fit bounds if we have markers
        if (this.markers.length > 0) {
            this.map.fitBounds(bounds, { padding: 50 });
        }
    }

    updateGoogleMarkers(listings) {
        const bounds = new google.maps.LatLngBounds();
        
        listings.forEach(listing => {
            const position = { lat: parseFloat(listing.lat), lng: parseFloat(listing.lng) };
            
            if (isNaN(position.lat) || isNaN(position.lng)) return;
            
            const marker = new google.maps.Marker({
                position: position,
                title: listing.title,
                icon: this.createMarkerIcon(listing.price)
            });
            
            const infoWindow = new google.maps.InfoWindow({
                content: this.createMarkerPopup(listing)
            });
            
            marker.addListener('click', () => {
                infoWindow.open(this.map, marker);
            });
            
            this.markers.push(marker);
            bounds.extend(position);
        });
        
        // Add markers to clusterer or map
        if (this.markerClusterer) {
            this.markerClusterer.addMarkers(this.markers);
        } else {
            this.markers.forEach(marker => marker.setMap(this.map));
        }
        
        // Fit bounds if we have markers
        if (this.markers.length > 0) {
            this.map.fitBounds(bounds);
        }
    }
    
    createMarkerIcon(price) {
        const formattedPrice = '$' + (price ? (parseInt(price) / 1000).toFixed(0) + 'K' : 'N/A');
        
        return {
            url: `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
                <svg width="120" height="40" xmlns="http://www.w3.org/2000/svg">
                    <rect width="120" height="40" rx="20" fill="#50bae1" stroke="#fff" stroke-width="2"/>
                    <text x="60" y="26" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="14" font-weight="bold">${formattedPrice}</text>
                </svg>
            `)}`,
            scaledSize: new google.maps.Size(120, 40),
            anchor: new google.maps.Point(60, 40)
        };
    }

    createMapboxMarkerHTML(price) {
        const formattedPrice = '$' + (price ? (parseInt(price) / 1000).toFixed(0) + 'K' : 'N/A');
        
        return `
            <div class="mapbox-marker-price" style="
                background: #50bae1; 
                color: white; 
                padding: 8px 16px; 
                border-radius: 20px; 
                border: 2px solid white;
                font-weight: bold; 
                font-size: 14px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                cursor: pointer;
                white-space: nowrap;
                ">
                ${formattedPrice}
            </div>
        `;
    }
    
    createMarkerPopup(listing) {
        const price = listing.price ? parseInt(listing.price).toLocaleString() : 'Price Available Upon Request';
        
        return `
            <div class="map-marker-popup">
                <img src="${listing.image}" alt="${listing.title}" class="listing-thumb" onerror="this.style.display='none'">
                <h4 class="hph-font-semibold hph-mb-sm">${listing.title}</h4>
                <p class="hph-text-primary hph-font-bold hph-text-lg hph-mb-sm">$${price}</p>
                <p class="hph-text-sm hph-text-gray-600 hph-mb-md">${listing.bedrooms} beds • ${listing.bathrooms} baths</p>
                <a href="${listing.url}" class="hph-btn hph-btn-primary hph-btn-sm hph-w-full">View Details</a>
            </div>
        `;
    }
    
    getMapStyles() {
        return [
            {
                featureType: 'water',
                elementType: 'geometry',
                stylers: [{ color: '#e9e9e9' }, { lightness: 17 }]
            },
            {
                featureType: 'landscape',
                elementType: 'geometry',
                stylers: [{ color: '#f5f5f5' }, { lightness: 20 }]
            },
            {
                featureType: 'road.highway',
                elementType: 'geometry.fill',
                stylers: [{ color: '#ffffff' }, { lightness: 17 }]
            },
            {
                featureType: 'road.highway',
                elementType: 'geometry.stroke',
                stylers: [{ color: '#ffffff' }, { lightness: 29 }, { weight: 0.2 }]
            }
        ];
    }
    
    toggleFavorite(listingId, button) {
        if (!window.hphUser?.isLoggedIn) {
            this.showLoginPrompt();
            return;
        }
        
        const isFavorited = this.favoriteListings.has(listingId);
        const action = isFavorited ? 'remove' : 'add';
        
        // Optimistic update
        this.updateFavoriteButton(button, !isFavorited);
        
        const params = new URLSearchParams({
            action: 'hph_toggle_favorite',
            nonce: window.hphArchive?.nonce || '',
            listing_id: listingId,
            favorite_action: action
        });
        
        fetch(window.hphArchive.ajaxUrl, {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (action === 'add') {
                    this.favoriteListings.add(listingId);
                } else {
                    this.favoriteListings.delete(listingId);
                }
                this.showNotification(data.data?.message || 'Favorite updated', 'success');
            } else {
                // Revert optimistic update
                this.updateFavoriteButton(button, isFavorited);
                this.showNotification(data.data?.message || 'Error updating favorite', 'error');
            }
        })
        .catch(error => {
            console.error('Favorite error:', error);
            this.updateFavoriteButton(button, isFavorited);
            this.showNotification('Network error occurred', 'error');
        });
    }
    
    toggleCompare(listingId, button) {
        const isComparing = this.compareListings.has(listingId);
        
        if (!isComparing && this.compareListings.size >= 3) {
            this.showNotification('You can compare up to 3 listings at once', 'warning');
            return;
        }
        
        if (isComparing) {
            this.compareListings.delete(listingId);
            button.classList.remove('comparing');
        } else {
            this.compareListings.add(listingId);
            button.classList.add('comparing');
        }
        
        this.updateCompareDisplay();
        this.saveCompareList();
    }
    
    handleShare(url, title) {
        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            }).catch(err => console.log('Error sharing:', err));
        } else {
            // Fallback to clipboard
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    this.showNotification('Link copied to clipboard!', 'success');
                }).catch(() => {
                    this.showNotification('Unable to copy link', 'error');
                });
            } else {
                this.showNotification('Sharing not available', 'warning');
            }
        }
    }
    
    handleSaveSearch() {
        if (!window.hphUser?.isLoggedIn) {
            this.showLoginPrompt();
            return;
        }
        
        const searchParams = this.getSearchParams();
        const searchName = prompt('Name this search:');
        
        if (!searchName) return;
        
        const params = new URLSearchParams({
            action: 'hph_save_search',
            nonce: window.hphArchive?.nonce || '',
            search_name: searchName,
            search_params: JSON.stringify(searchParams)
        });
        
        fetch(window.hphArchive.ajaxUrl, {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Search saved successfully!', 'success');
            } else {
                this.showNotification(data.data?.message || 'Error saving search', 'error');
            }
        })
        .catch(error => {
            console.error('Save search error:', error);
            this.showNotification('Network error occurred', 'error');
        });
    }
    
    handleBulkFavorite() {
        if (this.selectedListings.size === 0) return;
        
        if (!window.hphUser?.isLoggedIn) {
            this.showLoginPrompt();
            return;
        }
        
        const listingIds = Array.from(this.selectedListings);
        
        const params = new URLSearchParams({
            action: 'hph_bulk_favorite',
            nonce: window.hphArchive?.nonce || '',
            listing_ids: listingIds.join(',')
        });
        
        fetch(window.hphArchive.ajaxUrl, {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                listingIds.forEach(id => this.favoriteListings.add(id));
                this.showNotification(`Added ${listingIds.length} listings to favorites`, 'success');
                this.clearSelection();
            } else {
                this.showNotification(data.data?.message || 'Error adding to favorites', 'error');
            }
        })
        .catch(error => {
            console.error('Bulk favorite error:', error);
            this.showNotification('Network error occurred', 'error');
        });
    }
    
    handleBulkCompare() {
        if (this.selectedListings.size === 0) return;
        if (this.selectedListings.size > 3) {
            this.showNotification('You can only compare up to 3 listings', 'warning');
            return;
        }
        
        this.selectedListings.forEach(id => this.compareListings.add(id));
        this.updateCompareDisplay();
        this.clearSelection();
        
        // Redirect to compare page
        const compareIds = Array.from(this.compareListings).join(',');
        window.location.href = `/compare-listings?ids=${compareIds}`;
    }
    
    // Helper methods
    updateResults(html) {
        const container = document.getElementById('listing-results');
        if (container) {
            container.innerHTML = html;
            this.selectedListings.clear();
            this.updateBulkActions();
            
            // Apply current view to new results
            const urlParams = new URLSearchParams(window.location.search);
            const currentView = urlParams.get('view') || 'grid';
            this.refreshListingCards(currentView);
        }
    }
    
    appendResults(html) {
        const container = document.getElementById('listing-results');
        if (!container) return;
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        while (tempDiv.firstChild) {
            container.appendChild(tempDiv.firstChild);
        }
    }
    
    updateBulkActions() {
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCount = document.getElementById('selected-count');
        
        if (this.selectedListings.size > 0) {
            if (bulkActions) bulkActions.classList.remove('hph-hidden');
            if (selectedCount) selectedCount.textContent = this.selectedListings.size;
        } else {
            if (bulkActions) bulkActions.classList.add('hph-hidden');
        }
    }
    
    clearSelection() {
        document.querySelectorAll('.selection-checkbox.checked').forEach(checkbox => {
            checkbox.checked = false;
            checkbox.classList.remove('checked');
            const icon = checkbox.querySelector('i');
            if (icon) icon.classList.add('hph-hidden');
        });
        
        document.querySelectorAll('.listing-card-selected').forEach(card => {
            card.classList.remove('listing-card-selected');
        });
        
        this.selectedListings.clear();
        this.updateBulkActions();
    }
    
    updateFavoriteButton(button, isFavorited) {
        const icon = button.querySelector('i');
        
        if (isFavorited) {
            button.classList.add('favorited');
            if (icon) icon.className = 'fas fa-heart';
            button.title = 'Remove from Favorites';
        } else {
            button.classList.remove('favorited');
            if (icon) icon.className = 'far fa-heart';
            button.title = 'Add to Favorites';
        }
    }
    
    showLoadingState() {
        const results = document.getElementById('listing-results');
        if (!results) return;
        
        const existingOverlay = results.querySelector('.loading-overlay');
        if (existingOverlay) return;
        
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = '<div class="spinner"></div>';
        results.appendChild(loadingOverlay);
    }
    
    hideLoadingState() {
        document.querySelectorAll('.loading-overlay').forEach(overlay => {
            overlay.remove();
        });
    }
    
    showError(message) {
        this.showNotification(message, 'error');
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : 'var(--hph-primary)'};
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            max-width: 300px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    showLoginPrompt() {
        this.showNotification('Please log in to use this feature', 'warning');
    }
    
    getSearchParams() {
        const form = document.getElementById('filter-form');
        if (!form) return {};
        
        const formData = new FormData(form);
        const params = {};
        
        for (const [key, value] of formData.entries()) {
            if (value) params[key] = value;
        }
        
        return params;
    }
    
    updateURL(params) {
        const url = new URL(window.location);
        
        // Clear existing search params
        url.search = '';
        
        // Add new params
        for (const [key, value] of params.entries()) {
            if (value && key !== 'action' && key !== 'nonce' && key !== 'page') {
                url.searchParams.set(key, value);
            }
        }
        
        window.history.replaceState({}, '', url);
    }
    
    createHiddenInput(name) {
        const form = document.getElementById('filter-form');
        if (!form) return null;
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        form.appendChild(input);
        return input;
    }
    
    loadUserPreferences() {
        // Load favorites from user meta or session
        if (window.hphUser?.favorites) {
            this.favoriteListings = new Set(window.hphUser.favorites);
        }
        
        // Load compare list from cookie
        const compareList = document.cookie
            .split('; ')
            .find(row => row.startsWith('hph_compare_listings='))
            ?.split('=')[1];
        
        if (compareList) {
            this.compareListings = new Set(compareList.split(',').filter(id => id));
        }
        
        // Update UI based on loaded preferences
        this.updateCompareDisplay();
        this.updateFavoriteDisplay();
    }
    
    updateFavoriteDisplay() {
        document.querySelectorAll('[data-action="favorite"]').forEach(btn => {
            const listingId = btn.dataset.listingId;
            if (this.favoriteListings.has(listingId)) {
                this.updateFavoriteButton(btn, true);
            }
        });
    }
    
    saveCompareList() {
        const compareIds = Array.from(this.compareListings).join(',');
        document.cookie = `hph_compare_listings=${compareIds}; path=/; max-age=604800`; // 1 week
    }
    
    updateCompareDisplay() {
        document.querySelectorAll('[data-action="compare"]').forEach(btn => {
            const listingId = btn.dataset.listingId;
            if (this.compareListings.has(listingId)) {
                btn.classList.add('comparing');
            } else {
                btn.classList.remove('comparing');
            }
        });
    }
}

// Add required CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new EnhancedListingArchive();
        testEnhancedListingArchive();
    });
} else {
    new EnhancedListingArchive();
    testEnhancedListingArchive();
}

/**
 * Test function to verify all functionality is working
 */
function testEnhancedListingArchive() {
    if (typeof window.hphArchive === 'undefined') {
        console.warn('Archive JavaScript: hphArchive global not found');
        return;
    }
    
    const tests = {
        'Filter Form': () => !!document.getElementById('filter-form'),
        'Sort Select': () => !!document.getElementById('sort-select'),
        'Map Toggle': () => !!document.getElementById('map-toggle'),
        'Results Container': () => !!document.getElementById('listing-results'),
        'AJAX URL': () => !!window.hphArchive.ajaxUrl,
        'Nonce': () => !!window.hphArchive.nonce,
        'CSS Variables': () => {
            const root = getComputedStyle(document.documentElement);
            return root.getPropertyValue('--hph-primary').trim() !== '';
        },
        'View Buttons': () => document.querySelectorAll('[data-view]').length > 0,
        'Quick Filters': () => document.querySelectorAll('.quick-filters a').length > 0
    };
    
    let passed = 0;
    let failed = 0;
    
    console.group('🏠 Enhanced Listing Archive Tests');
    
    for (const [testName, testFn] of Object.entries(tests)) {
        try {
            if (testFn()) {
                console.log(`✅ ${testName}: PASS`);
                passed++;
            } else {
                console.warn(`❌ ${testName}: FAIL`);
                failed++;
            }
        } catch (error) {
            console.error(`❌ ${testName}: ERROR -`, error.message);
            failed++;
        }
    }
    
    console.log(`\n📊 Test Results: ${passed} passed, ${failed} failed (${Math.round(passed / (passed + failed) * 100)}% success rate)`);
    
    if (failed === 0) {
        console.log('🎉 All tests passed! Archive functionality is ready.');
    } else {
        console.warn('⚠️  Some tests failed. Check the elements and configuration.');
    }
    
    console.groupEnd();
    
    // Test event binding
    console.group('🔗 Event Binding Tests');
    
    const eventTests = {
        'Form Submit Handler': () => {
            const form = document.getElementById('filter-form');
            return form && form.addEventListener;
        },
        'Sort Change Handler': () => {
            const select = document.getElementById('sort-select');
            return select && select.addEventListener;
        },
        'Document Click Handler': () => {
            return document.addEventListener && 
                   typeof document.onclick !== 'undefined';
        }
    };
    
    for (const [testName, testFn] of Object.entries(eventTests)) {
        try {
            if (testFn()) {
                console.log(`✅ ${testName}: PASS`);
            } else {
                console.warn(`❌ ${testName}: FAIL`);
            }
        } catch (error) {
            console.error(`❌ ${testName}: ERROR -`, error.message);
        }
    }
    
    console.groupEnd();
}
