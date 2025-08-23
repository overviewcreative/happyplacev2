/**
 * Archive Listing JavaScript
 * Location: /wp-content/themes/happy-place/assets/js/pages/archive-listing.js
 * 
 * Handles archive listing interactions including:
 * - View switching (grid/list/map/gallery)
 * - Sort functionality
 * - Filter management
 * - AJAX loading
 * - Save search
 * - Advanced search panel
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';

    /**
     * Archive Listing Controller
     */
    class ArchiveListingController {
        constructor() {
            this.$container = $('.hph-archive-listing');
            if (!this.$container.length) return;
            
            // Cache DOM elements
            this.$searchToggle = $('.hph-archive__search-toggle');
            this.$searchPanel = $('#advanced-search');
            this.$viewSwitcher = $('.hph-archive__view-switcher');
            this.$sortSelect = $('#listing-sort');
            this.$perPageSelect = $('#per-page');
            this.$filterTags = $('.hph-filter-tag');
            this.$clearFilters = $('.hph-clear-all-filters');
            this.$saveSearch = $('.hph-archive__save-search');
            this.$listings = $('.hph-archive__listings');
            
            // State
            this.currentView = this.$container.data('view') || 'grid';
            this.isLoading = false;
            this.currentFilters = this.getFiltersFromURL();
            
            // Initialize
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.initializeView();
            this.restoreUserPreferences();
            
            // Initialize map if in map view
            if (this.currentView === 'map') {
                this.initializeMap();
            }
        }
        
        bindEvents() {
            // Search panel toggle
            this.$searchToggle.on('click', (e) => this.toggleSearchPanel(e));
            
            // View switcher
            this.$viewSwitcher.on('click', '.hph-view-btn', (e) => this.switchView(e));
            
            // Sort change
            this.$sortSelect.on('change', (e) => this.handleSort(e));
            
            // Per page change
            this.$perPageSelect.on('change', (e) => this.handlePerPageChange(e));
            
            // Filter tag removal
            $(document).on('click', '.hph-filter-tag__remove', (e) => this.removeFilter(e));
            
            // Clear all filters
            this.$clearFilters.on('click', (e) => this.clearAllFilters(e));
            
            // Save search
            this.$saveSearch.on('click', (e) => this.openSaveSearchModal(e));
            
            // Save/favorite buttons on cards
            $(document).on('click', '.hph-card__save', (e) => this.toggleFavorite(e));
            
            // Compare checkboxes
            $(document).on('change', '.hph-compare-checkbox', (e) => this.handleCompare(e));
            
            // Pagination AJAX
            $(document).on('click', '.hph-archive__pagination a', (e) => this.handlePagination(e));
            
            // Advanced search form
            $(document).on('submit', '#advanced-search-form', (e) => this.handleAdvancedSearch(e));
            
            // Save search form
            $(document).on('submit', '#save-search-form', (e) => this.handleSaveSearch(e));
            
            // Modal close
            $(document).on('click', '.hph-modal__close, .hph-modal__backdrop, .hph-modal__cancel', (e) => {
                $(e.target).closest('.hph-modal').fadeOut(200).attr('aria-hidden', 'true');
            });
            
            // Keyboard navigation
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape') {
                    $('.hph-modal:visible').fadeOut(200).attr('aria-hidden', 'true');
                    if (this.$searchPanel.attr('aria-hidden') === 'false') {
                        this.toggleSearchPanel();
                    }
                }
            });
        }
        
        /**
         * Toggle advanced search panel
         */
        toggleSearchPanel(e) {
            if (e) e.preventDefault();
            
            const isExpanded = this.$searchToggle.attr('aria-expanded') === 'true';
            
            this.$searchToggle
                .attr('aria-expanded', !isExpanded)
                .find('.hph-icon-chevron')
                .toggleClass('fa-chevron-down fa-chevron-up');
            
            this.$searchPanel
                .slideToggle(300)
                .attr('aria-hidden', isExpanded);
            
            // Focus first input when opened
            if (!isExpanded) {
                setTimeout(() => {
                    this.$searchPanel.find('input:first').focus();
                }, 300);
            }
        }
        
        /**
         * Switch listing view
         */
        switchView(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const newView = $btn.data('view');
            
            if (newView === this.currentView) return;
            
            // Update buttons
            this.$viewSwitcher.find('.hph-view-btn').removeClass('active').attr('aria-selected', 'false');
            $btn.addClass('active').attr('aria-selected', 'true');
            
            // Update view
            this.currentView = newView;
            this.$container.attr('data-view', newView);
            
            // Save preference
            this.setCookie('hph_listing_view', newView, 30);
            
            // Reload listings with new view
            this.reloadListings({ view: newView });
            
            // Initialize map if switching to map view
            if (newView === 'map') {
                setTimeout(() => this.initializeMap(), 500);
            }
        }
        
        /**
         * Handle sort change
         */
        handleSort(e) {
            const sortValue = $(e.target).val();
            this.setCookie('hph_listing_sort', sortValue, 30);
            this.reloadListings({ sort: sortValue });
        }
        
        /**
         * Handle per page change
         */
        handlePerPageChange(e) {
            const perPage = $(e.target).val();
            this.setCookie('hph_listings_per_page', perPage, 30);
            this.reloadListings({ per_page: perPage, paged: 1 });
        }
        
        /**
         * Remove single filter
         */
        removeFilter(e) {
            e.preventDefault();
            
            const $tag = $(e.target).closest('.hph-filter-tag');
            const filterKey = $tag.data('filter');
            
            // Remove from current filters
            delete this.currentFilters[filterKey];
            
            // Reload without this filter
            this.reloadListings(this.currentFilters);
        }
        
        /**
         * Clear all filters
         */
        clearAllFilters(e) {
            e.preventDefault();
            this.currentFilters = {};
            this.reloadListings({});
        }
        
        /**
         * Reload listings via AJAX or page reload
         */
        reloadListings(params = {}) {
            if (this.isLoading) return;
            
            // Merge with current params
            const urlParams = new URLSearchParams(window.location.search);
            Object.keys(params).forEach(key => {
                if (params[key]) {
                    urlParams.set(key, params[key]);
                } else {
                    urlParams.delete(key);
                }
            });
            
            // Update URL without reload
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            window.history.pushState({}, '', newUrl);
            
            // If AJAX is enabled, load via AJAX
            if (window.hphArchive && window.hphArchive.ajaxUrl) {
                this.loadListingsAJAX(params);
            } else {
                // Otherwise, reload the page
                window.location.href = newUrl;
            }
        }
        
        /**
         * Load listings via AJAX
         */
        loadListingsAJAX(params) {
            this.isLoading = true;
            
            // Show loading state
            this.$listings.addClass('hph-loading').append('<div class="hph-loader"></div>');
            
            $.ajax({
                url: window.hphArchive.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_load_listings',
                    nonce: window.hphArchive.nonce,
                    view: this.currentView,
                    ...params
                },
                success: (response) => {
                    if (response.success) {
                        // Update listings
                        this.$listings.html(response.data.html);
                        
                        // Update results count
                        $('.hph-results-number').text(response.data.total);
                        
                        // Update pagination
                        if (response.data.pagination) {
                            $('.hph-archive__pagination').html(response.data.pagination);
                        }
                        
                        // Reinitialize map if needed
                        if (this.currentView === 'map') {
                            this.initializeMap();
                        }
                        
                        // Trigger custom event
                        $(document).trigger('hph:listings:loaded', response.data);
                    }
                },
                error: () => {
                    this.showNotification('Error loading listings. Please try again.', 'error');
                },
                complete: () => {
                    this.isLoading = false;
                    this.$listings.removeClass('hph-loading').find('.hph-loader').remove();
                }
            });
        }
        
        /**
         * Handle pagination click
         */
        handlePagination(e) {
            if (!window.hphArchive || !window.hphArchive.ajaxUrl) return;
            
            e.preventDefault();
            
            const url = new URL($(e.currentTarget).attr('href'));
            const page = url.searchParams.get('paged') || 1;
            
            this.reloadListings({ paged: page });
            
            // Scroll to top of listings with proper header compensation
            const headerHeight = getComputedStyle(document.documentElement).getPropertyValue('--hph-total-header-height') || '120px';
            const headerHeightPx = parseInt(headerHeight);
            
            $('html, body').animate({
                scrollTop: this.$container.offset().top - headerHeightPx - 20
            }, 500);
        }
        
        /**
         * Toggle favorite status
         */
        toggleFavorite(e) {
            e.preventDefault();
            
            if (!window.hphArchive.isUserLoggedIn) {
                this.showNotification('Please log in to save properties.', 'warning');
                return;
            }
            
            const $btn = $(e.currentTarget);
            const listingId = $btn.data('listing');
            const isSaved = $btn.hasClass('is-saved');
            
            // Optimistic update
            $btn.toggleClass('is-saved');
            $btn.find('i').toggleClass('far fas');
            
            // Send AJAX request
            $.ajax({
                url: window.hphArchive.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_toggle_favorite',
                    nonce: window.hphArchive.nonce,
                    listing_id: listingId
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotification(
                            isSaved ? 'Removed from favorites' : 'Added to favorites',
                            'success'
                        );
                    } else {
                        // Revert on error
                        $btn.toggleClass('is-saved');
                        $btn.find('i').toggleClass('far fas');
                        this.showNotification('Error updating favorites.', 'error');
                    }
                },
                error: () => {
                    // Revert on error
                    $btn.toggleClass('is-saved');
                    $btn.find('i').toggleClass('far fas');
                    this.showNotification('Network error. Please try again.', 'error');
                }
            });
        }
        
        /**
         * Handle compare checkbox
         */
        handleCompare(e) {
            const $checkbox = $(e.target);
            const listingId = $checkbox.data('listing');
            
            // Get current compare list from localStorage
            let compareList = JSON.parse(localStorage.getItem('hph_compare_list') || '[]');
            
            if ($checkbox.prop('checked')) {
                // Add to compare list
                if (!compareList.includes(listingId)) {
                    compareList.push(listingId);
                }
                
                // Limit to 4 properties
                if (compareList.length > 4) {
                    compareList.shift();
                    this.showNotification('Maximum 4 properties can be compared', 'info');
                    
                    // Uncheck oldest
                    $(`.hph-compare-checkbox[data-listing="${compareList[0]}"]`).prop('checked', false);
                }
            } else {
                // Remove from compare list
                compareList = compareList.filter(id => id !== listingId);
            }
            
            // Save to localStorage
            localStorage.setItem('hph_compare_list', JSON.stringify(compareList));
            
            // Update compare bar
            this.updateCompareBar(compareList);
        }
        
        /**
         * Update compare bar
         */
        updateCompareBar(compareList) {
            // Implementation would show/hide a compare bar at the bottom
            // with selected properties and a compare button
            
            if (compareList.length >= 2) {
                // Show compare bar
                console.log('Ready to compare:', compareList);
            }
        }
        
        /**
         * Handle advanced search form submission
         */
        handleAdvancedSearch(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const params = {};
            
            // Convert FormData to object
            for (let [key, value] of formData.entries()) {
                if (value) {
                    if (params[key]) {
                        // Handle multiple values (like features)
                        if (!Array.isArray(params[key])) {
                            params[key] = [params[key]];
                        }
                        params[key].push(value);
                    } else {
                        params[key] = value;
                    }
                }
            }
            
            // Close search panel
            this.toggleSearchPanel();
            
            // Reload with new filters
            this.currentFilters = params;
            this.reloadListings(params);
        }
        
        /**
         * Open save search modal
         */
        openSaveSearchModal(e) {
            e.preventDefault();
            
            if (!window.hphArchive.isUserLoggedIn) {
                this.showNotification('Please log in to save searches.', 'warning');
                return;
            }
            
            $('#save-search-modal').fadeIn(200).attr('aria-hidden', 'false');
        }
        
        /**
         * Handle save search form
         */
        handleSaveSearch(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const searchData = {
                name: formData.get('search_name'),
                frequency: formData.get('email_frequency'),
                filters: this.currentFilters,
                url: window.location.href
            };
            
            $.ajax({
                url: window.hphArchive.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_save_search',
                    nonce: window.hphArchive.nonce,
                    search_data: searchData
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotification('Search saved successfully!', 'success');
                        $('#save-search-modal').fadeOut(200);
                        
                        // Update save search button
                        this.$saveSearch.find('i').removeClass('far').addClass('fas');
                    } else {
                        this.showNotification('Error saving search.', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Network error. Please try again.', 'error');
                }
            });
        }
        
        /**
         * Initialize map view
         */
        initializeMap() {
            if (typeof google === 'undefined' || !google.maps) {
                console.error('Google Maps not loaded');
                return;
            }
            
            const mapElement = document.getElementById('archive-map');
            if (!mapElement) return;
            
            // Create map
            const map = new google.maps.Map(mapElement, {
                zoom: 11,
                center: { lat: 25.7617, lng: -80.1918 }, // Miami default
                mapTypeControl: false,
                streetViewControl: false,
                styles: this.getMapStyles()
            });
            
            // Add markers for each listing
            const markers = [];
            const bounds = new google.maps.LatLngBounds();
            
            $('.hph-listing-card--map').each(function() {
                const $card = $(this);
                const lat = parseFloat($card.data('lat'));
                const lng = parseFloat($card.data('lng'));
                
                if (lat && lng) {
                    const position = { lat, lng };
                    
                    const marker = new google.maps.Marker({
                        position,
                        map,
                        title: $card.find('.hph-card-map__address a').text(),
                        icon: {
                            url: window.hphArchive.markerIcon || '',
                            scaledSize: new google.maps.Size(40, 40)
                        }
                    });
                    
                    // Add click listener to show card
                    marker.addListener('click', function() {
                        // Scroll to card in sidebar
                        const offset = $card.offset().top - $('.hph-archive__map-sidebar').offset().top;
                        $('.hph-archive__map-sidebar').animate({ scrollTop: offset }, 300);
                        
                        // Highlight card
                        $('.hph-listing-card--map').removeClass('active');
                        $card.addClass('active');
                    });
                    
                    markers.push(marker);
                    bounds.extend(position);
                }
            });
            
            // Fit map to markers
            if (markers.length > 0) {
                map.fitBounds(bounds);
            }
            
            // Store map instance
            this.map = map;
            this.markers = markers;
        }
        
        /**
         * Get map styles
         */
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
                },
                {
                    featureType: 'road.arterial',
                    elementType: 'geometry',
                    stylers: [{ color: '#ffffff' }, { lightness: 18 }]
                },
                {
                    featureType: 'road.local',
                    elementType: 'geometry',
                    stylers: [{ color: '#ffffff' }, { lightness: 16 }]
                },
                {
                    featureType: 'poi',
                    elementType: 'geometry',
                    stylers: [{ color: '#f5f5f5' }, { lightness: 21 }]
                },
                {
                    featureType: 'poi.park',
                    elementType: 'geometry',
                    stylers: [{ color: '#dedede' }, { lightness: 21 }]
                },
                {
                    elementType: 'labels.text.stroke',
                    stylers: [{ visibility: 'on' }, { color: '#ffffff' }, { lightness: 16 }]
                },
                {
                    elementType: 'labels.text.fill',
                    stylers: [{ saturation: 36 }, { color: '#333333' }, { lightness: 40 }]
                },
                {
                    elementType: 'labels.icon',
                    stylers: [{ visibility: 'off' }]
                },
                {
                    featureType: 'transit',
                    elementType: 'geometry',
                    stylers: [{ color: '#f2f2f2' }, { lightness: 19 }]
                },
                {
                    featureType: 'administrative',
                    elementType: 'geometry.fill',
                    stylers: [{ color: '#fefefe' }, { lightness: 20 }]
                },
                {
                    featureType: 'administrative',
                    elementType: 'geometry.stroke',
                    stylers: [{ color: '#fefefe' }, { lightness: 17 }, { weight: 1.2 }]
                }
            ];
        }
        
        /**
         * Initialize view on load
         */
        initializeView() {
            // Add view-specific classes
            $('body').addClass('hph-archive-view-' + this.currentView);
        }
        
        /**
         * Restore user preferences
         */
        restoreUserPreferences() {
            // These are already set server-side via cookies
            // This is for any additional client-side preferences
        }
        
        /**
         * Get filters from URL
         */
        getFiltersFromURL() {
            const params = new URLSearchParams(window.location.search);
            const filters = {};
            
            for (let [key, value] of params.entries()) {
                if (key !== 'view' && key !== 'sort' && key !== 'per_page' && key !== 'paged') {
                    filters[key] = value;
                }
            }
            
            return filters;
        }
        
        /**
         * Show notification
         */
        showNotification(message, type = 'info') {
            // Create notification element
            const $notification = $(`
                <div class="hph-notification hph-notification--${type}">
                    <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                    <span>${message}</span>
                </div>
            `);
            
            // Add to body
            $('body').append($notification);
            
            // Animate in
            setTimeout(() => {
                $notification.addClass('show');
            }, 100);
            
            // Remove after delay
            setTimeout(() => {
                $notification.removeClass('show');
                setTimeout(() => {
                    $notification.remove();
                }, 300);
            }, 3000);
        }
        
        /**
         * Get notification icon
         */
        getNotificationIcon(type) {
            const icons = {
                success: 'check-circle',
                error: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };
            return icons[type] || icons.info;
        }
        
        /**
         * Set cookie
         */
        setCookie(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
        }
    }
    
    // Initialize on DOM ready
    $(document).ready(() => {
        new ArchiveListingController();
    });
    
})(jQuery);