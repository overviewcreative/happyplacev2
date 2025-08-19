/**
 * Dashboard Listings JavaScript
 * Handles listing page interactions and view switching
 */

(function($) {
    'use strict';

    const ListingsController = {
        init: function() {
            this.bindEvents();
            this.initializeView();
        },

        bindEvents: function() {
            // View toggle buttons
            $('.view-btn').on('click', this.handleViewChange.bind(this));
            
            // Filter buttons
            $('.filter-btn').on('click', this.handleFilterChange.bind(this));
            
            // Sort dropdown
            $('#listingsSort').on('change', this.handleSortChange.bind(this));
            
            // Search input
            $('#listingsSearch').on('input', this.debounce(this.handleSearch.bind(this), 300));
            
            // Select all checkbox
            $('#selectAllListings').on('change', this.handleSelectAll.bind(this));
            
            // Individual checkboxes
            $(document).on('change', '.listing-checkbox', this.handleCheckboxChange.bind(this));
        },

        initializeView: function() {
            // Set default view to list
            const savedView = localStorage.getItem('listings_view') || 'list';
            this.switchView(savedView);
        },

        handleViewChange: function(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const view = $btn.data('view');
            
            // Update button states
            $('.view-btn').removeClass('active');
            $btn.addClass('active');
            
            // Switch view
            this.switchView(view);
            
            // Save preference
            localStorage.setItem('listings_view', view);
        },

        switchView: function(view) {
            // Hide all views
            $('.listings-list, .listings-grid, .listings-map').hide();
            $('.listings-list, .listings-grid, .listings-map').removeClass('active');
            
            // Show selected view
            switch(view) {
                case 'list':
                    $('.listings-list').show().addClass('active');
                    break;
                case 'grid':
                    $('.listings-grid').show().addClass('active');
                    break;
                case 'map':
                    $('.listings-map').show().addClass('active');
                    // Initialize map if needed
                    if (!this.mapInitialized) {
                        this.initializeMap();
                    }
                    break;
            }
            
            // Update active button
            $('.view-btn').removeClass('active');
            $(`.view-btn[data-view="${view}"]`).addClass('active');
        },

        handleFilterChange: function(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const status = $btn.data('status');
            
            // Update button states
            $('.filter-btn').removeClass('active');
            $btn.addClass('active');
            
            // Filter listings
            this.filterListings(status);
        },

        filterListings: function(status) {
            console.log('Filtering by status:', status);
            
            // Filter items in both list and grid views
            if (status === 'all') {
                $('.listing-list-item, .hph-listing-card').show();
            } else {
                $('.listing-list-item, .hph-listing-card').hide();
                // Show items that match the status
                $(`.listing-status.${status}`).closest('.listing-list-item').show();
                // For grid view, check for status badges in listing cards
                $(`.hph-badge:contains("${status.charAt(0).toUpperCase() + status.slice(1)}")`).closest('.hph-listing-card').show();
                
                // Special handling for 'active' status (items without status badges are usually active)
                if (status === 'active') {
                    $('.hph-listing-card').each(function() {
                        if ($(this).find('.hph-badge').length === 1) { // Only price badge, no status badge
                            $(this).show();
                        }
                    });
                }
            }
        },

        handleSortChange: function(e) {
            const sortBy = $(e.target).val();
            console.log('Sorting by:', sortBy);
            // Implement sorting logic here
        },

        handleSearch: function(e) {
            const searchTerm = $(e.target).val().toLowerCase();
            console.log('Searching for:', searchTerm);
            
            if (searchTerm === '') {
                // Show all items if search is empty
                $('.listing-list-item, .hph-listing-card').show();
                return;
            }
            
            // Hide all items first
            $('.listing-list-item, .hph-listing-card').hide();
            
            // Search in list view
            $('.listing-list-item').each(function() {
                const $item = $(this);
                const title = $item.find('.listing-title').text().toLowerCase();
                const address = $item.find('.listing-address').text().toLowerCase();
                const mls = $item.find('.listing-meta').text().toLowerCase();
                
                if (title.includes(searchTerm) || address.includes(searchTerm) || mls.includes(searchTerm)) {
                    $item.show();
                }
            });
            
            // Search in grid view
            $('.hph-listing-card').each(function() {
                const $card = $(this);
                const title = $card.find('.hph-card-title a').text().toLowerCase();
                const address = $card.find('.fa-map-marker-alt').parent().text().toLowerCase();
                
                if (title.includes(searchTerm) || address.includes(searchTerm)) {
                    $card.show();
                }
            });
        },

        handleSelectAll: function(e) {
            const isChecked = $(e.target).prop('checked');
            $('.listing-checkbox').prop('checked', isChecked);
            this.updateBulkActions();
        },

        handleCheckboxChange: function() {
            this.updateBulkActions();
        },

        updateBulkActions: function() {
            const checkedCount = $('.listing-checkbox:checked').length;
            if (checkedCount > 0) {
                // Show bulk actions bar if needed
                console.log(checkedCount + ' items selected');
            }
        },

        initializeMap: function() {
            // Placeholder for map initialization
            console.log('Initializing map view');
            this.mapInitialized = true;
        },

        debounce: function(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.listings-management-section').length > 0) {
            ListingsController.init();
        }
    });

    // Make controller available globally
    window.ListingsController = ListingsController;

})(jQuery);