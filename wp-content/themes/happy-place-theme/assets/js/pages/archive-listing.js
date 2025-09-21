/**
 * Archive Listing JavaScript - Foundation-First Architecture
 * Enhanced interactivity for property listing archive pages
 * 
 * @package HappyPlaceTheme
 * @version 3.0.0
 */

(function($) {
    'use strict';

    /**
     * Listing Archive Controller
     */
    class ListingArchive {
        constructor() {
            this.config = window.hphListingArchive || {};
            this.isLoading = false;
            this.init();
        }

        /**
         * Initialize listing archive functionality
         */
        init() {
            this.bindEvents();
            this.setupViewToggle();
            
            // Initialize if AJAX is enabled
            if (this.config.ajaxEnabled) {
                this.initializeAjax();
            }
        }

        /**
         * Bind event listeners
         */
        bindEvents() {
            // View toggle
            $(document).on('click', '[data-view-mode]', this.handleViewChange.bind(this));
            
            // Sort change
            $(document).on('change', '[data-sort]', this.handleSortChange.bind(this));
            
            // Per page change
            $(document).on('change', '[data-per-page]', this.handlePerPageChange.bind(this));
        }

        /**
         * Handle view mode changes
         */
        handleViewChange(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const newView = $button.data('view-mode');
            
            if (newView === this.config.currentView) {
                return;
            }

            // Update active state
            $('[data-view-mode]').removeClass('active');
            $button.addClass('active');
            
            // Apply view mode
            this.applyViewMode(newView);
        }

        /**
         * Apply view mode to grid
         */
        applyViewMode(viewMode) {
            const $grid = $('.card-grid');
            
            // Remove existing view classes
            $grid.removeClass('view-grid view-list view-map');
            
            // Add new view class
            $grid.addClass(`view-${viewMode}`);
            
            // Update config
            this.config.currentView = viewMode;
        }

        /**
         * Handle sort changes
         */
        handleSortChange(e) {
            const $select = $(e.currentTarget);
            const newSort = $select.val();
            
            if (this.config.ajaxEnabled) {
                this.loadResults({ sort: newSort });
            } else {
                window.location.href = this.updateUrlParam('sort', newSort);
            }
        }

        /**
         * Handle per page changes
         */
        handlePerPageChange(e) {
            const $select = $(e.currentTarget);
            const newPerPage = $select.val();
            
            if (this.config.ajaxEnabled) {
                this.loadResults({ per_page: newPerPage, paged: 1 });
            } else {
                window.location.href = this.updateUrlParam('per_page', newPerPage);
            }
        }

        /**
         * Setup view toggle buttons
         */
        setupViewToggle() {
            const currentView = this.config.currentView || 'grid';
            $(`[data-view-mode="${currentView}"]`).addClass('active');
        }

        /**
         * Initialize AJAX functionality
         */
        initializeAjax() {
            // AJAX pagination
            $(document).on('click', '.pagination a', this.handleAjaxPagination.bind(this));
        }

        /**
         * Handle AJAX pagination
         */
        handleAjaxPagination(e) {
            e.preventDefault();
            
            const $link = $(e.currentTarget);
            const href = $link.attr('href');
            const page = this.getUrlParam(href, 'paged') || 1;
            
            this.loadResults({ paged: page });
        }

        /**
         * Update single URL parameter
         */
        updateUrlParam(param, value) {
            const url = new URL(window.location);
            url.searchParams.set(param, value);
            return url.toString();
        }

        /**
         * Get URL parameter value
         */
        getUrlParam(url, param) {
            const urlObj = new URL(url);
            return urlObj.searchParams.get(param);
        }
    }

    /**
     * Initialize when DOM is ready
     */
    $(document).ready(function() {
        if (document.body.classList.contains('post-type-archive-listing')) {
            new ListingArchive();
        }
    });

})(jQuery);
