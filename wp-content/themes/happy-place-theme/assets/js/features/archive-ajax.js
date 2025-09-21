/**
 * Universal Archive AJAX Handler
 * 
 * Provides AJAX functionality for all archive pages including:
 * - Dynamic filtering and sorting
 * - View mode switching (grid/list/masonry)
 * - Pagination without page reloads
 * - URL history management
 * - Loading states and error handling
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';

    // Archive AJAX manager
    const ArchiveAjax = {
        // Configuration
        config: {
            ajaxUrl: hphArchive?.ajaxUrl || '/wp-admin/admin-ajax.php',
            nonce: hphArchive?.nonce || '',
            postType: hphArchive?.postType || 'listing',
            currentView: hphArchive?.currentView || 'grid',
            currentSort: hphArchive?.currentSort || 'date_desc',
            currentPage: 1,
            perPage: 12,
            autoFilter: hphArchive?.autoFilter !== false,
            debounceDelay: 300
        },

        // Cache selectors
        selectors: {
            container: '.hph-archive-layout',
            resultsContainer: '[data-results-container]',
            loadingContainer: '.hph-loading-skeleton',
            filtersForm: '.hph-archive-filters form, .hph-search-form',
            viewButtons: '.hph-view-toggle button',
            sortSelect: '.hph-sort-select',
            perPageSelect: '.hph-per-page-select',
            pagination: '.hph-pagination a',
            clearFilters: '.hpt-clear-filters',
            resultsCount: '.hph-results-count',
            noResults: '.hph-empty-state'
        },

        // State management
        state: {
            isLoading: false,
            currentFilters: {},
            searchQuery: '',
            hasResults: true
        },

        // Initialize the archive AJAX system
        init() {
            this.bindEvents();
            this.initializeState();
            this.setupHistoryManagement();
        },

        // Bind all event handlers
        bindEvents() {
            const self = this;

            // Filter form submissions
            $(document).on('submit', this.selectors.filtersForm, function(e) {
                e.preventDefault();
                self.handleFilterSubmit($(this));
            });

            // Real-time filter changes (with debouncing)
            if (this.config.autoFilter) {
                let filterTimeout;
                $(document).on('change input', this.selectors.filtersForm + ' input, ' + this.selectors.filtersForm + ' select', function() {
                    clearTimeout(filterTimeout);
                    filterTimeout = setTimeout(() => {
                        self.handleFilterChange();
                    }, self.config.debounceDelay);
                });
            }

            // View mode switching
            $(document).on('click', this.selectors.viewButtons, function(e) {
                e.preventDefault();
                const newView = $(this).data('view');
                if (newView && newView !== self.config.currentView) {
                    self.handleViewChange(newView);
                }
            });

            // Sort changes
            $(document).on('change', this.selectors.sortSelect, function() {
                const newSort = $(this).val();
                if (newSort !== self.config.currentSort) {
                    self.handleSortChange(newSort);
                }
            });

            // Per page changes
            $(document).on('change', this.selectors.perPageSelect, function() {
                const newPerPage = parseInt($(this).val());
                if (newPerPage !== self.config.perPage) {
                    self.handlePerPageChange(newPerPage);
                }
            });

            // Pagination clicks
            $(document).on('click', this.selectors.pagination, function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const pageMatch = url.match(/[?&]paged=(\d+)/);
                if (pageMatch) {
                    const newPage = parseInt(pageMatch[1]);
                    self.handlePageChange(newPage);
                }
            });

            // Clear filters
            $(document).on('click', this.selectors.clearFilters, function(e) {
                e.preventDefault();
                self.clearAllFilters();
            });

            // Handle browser back/forward
            $(window).on('popstate', function(e) {
                if (e.originalEvent.state && e.originalEvent.state.isArchiveAjax) {
                    self.loadStateFromHistory(e.originalEvent.state);
                }
            });
        },

        // Initialize state from current page
        initializeState() {
            const urlParams = new URLSearchParams(window.location.search);
            
            // Extract current state from URL
            this.state.searchQuery = urlParams.get('s') || '';
            this.config.currentView = urlParams.get('view') || this.config.currentView;
            this.config.currentSort = urlParams.get('sort') || this.config.currentSort;
            this.config.currentPage = parseInt(urlParams.get('paged')) || 1;
            this.config.perPage = parseInt(urlParams.get('per_page')) || this.config.perPage;

            // Extract filters
            this.state.currentFilters = {};
            for (let [key, value] of urlParams.entries()) {
                if (!['s', 'view', 'sort', 'paged', 'per_page'].includes(key)) {
                    this.state.currentFilters[key] = value;
                }
            }

            // Update UI to match current state
            this.updateUI();
        },

        // Setup browser history management
        setupHistoryManagement() {
            // Replace current history state
            const currentState = {
                isArchiveAjax: true,
                postType: this.config.postType,
                view: this.config.currentView,
                sort: this.config.currentSort,
                page: this.config.currentPage,
                perPage: this.config.perPage,
                filters: this.state.currentFilters,
                searchQuery: this.state.searchQuery
            };
            
            history.replaceState(currentState, document.title, window.location.href);
        },

        // Handle filter form submission
        handleFilterSubmit(form) {
            this.collectFiltersFromForm(form);
            this.config.currentPage = 1; // Reset to first page
            this.performAjaxRequest('filter');
        },

        // Handle real-time filter changes
        handleFilterChange() {
            if (this.state.isLoading) return;
            
            this.collectFiltersFromForm();
            this.config.currentPage = 1; // Reset to first page
            this.performAjaxRequest('filter');
        },

        // Handle view mode changes
        handleViewChange(newView) {
            if (this.state.isLoading) return;
            
            this.config.currentView = newView;
            this.updateViewButtons();
            this.performAjaxRequest('view_change');
        },

        // Handle sort changes
        handleSortChange(newSort) {
            if (this.state.isLoading) return;
            
            this.config.currentSort = newSort;
            this.config.currentPage = 1; // Reset to first page
            this.performAjaxRequest('sort');
        },

        // Handle per page changes
        handlePerPageChange(newPerPage) {
            if (this.state.isLoading) return;
            
            this.config.perPage = newPerPage;
            this.config.currentPage = 1; // Reset to first page
            this.performAjaxRequest('per_page_change');
        },

        // Handle pagination
        handlePageChange(newPage) {
            if (this.state.isLoading) return;
            
            this.config.currentPage = newPage;
            this.performAjaxRequest('paginate');
            
            // Scroll to top of results
            $('html, body').animate({
                scrollTop: $(this.selectors.container).offset().top - 100
            }, 300);
        },

        // Collect filters from form
        collectFiltersFromForm(form) {
            form = form || $(this.selectors.filtersForm).first();
            
            this.state.currentFilters = {};
            this.state.searchQuery = '';

            // Get search query
            const searchInput = form.find('input[name="s"]');
            if (searchInput.length) {
                this.state.searchQuery = searchInput.val().trim();
            }

            // Get all form inputs
            form.find('input, select, textarea').each((index, element) => {
                const $element = $(element);
                const name = $element.attr('name');
                const type = $element.attr('type');
                
                if (!name || name === 's') return;

                let value = null;
                
                if (type === 'checkbox' || type === 'radio') {
                    if ($element.is(':checked')) {
                        value = $element.val();
                    }
                } else if (type !== 'submit' && type !== 'button') {
                    value = $element.val();
                }

                if (value && value !== '') {
                    // Handle multiple values (checkboxes with same name)
                    if (this.state.currentFilters[name]) {
                        if (!Array.isArray(this.state.currentFilters[name])) {
                            this.state.currentFilters[name] = [this.state.currentFilters[name]];
                        }
                        this.state.currentFilters[name].push(value);
                    } else {
                        this.state.currentFilters[name] = value;
                    }
                }
            });
        },

        // Perform the AJAX request
        performAjaxRequest(actionType) {
            if (this.state.isLoading) return;

            this.showLoadingState();

            const requestData = {
                action: 'hpt_archive_ajax',
                nonce: this.config.nonce,
                post_type: this.config.postType,
                action_type: actionType,
                view: this.config.currentView,
                sort: this.config.currentSort,
                per_page: this.config.perPage,
                paged: this.config.currentPage,
                s: this.state.searchQuery,
                filters: this.state.currentFilters
            };

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: requestData,
                dataType: 'json'
            })
            .done((response) => {
                this.handleSuccess(response, actionType);
            })
            .fail((xhr, status, error) => {
                this.handleError(xhr, status, error);
            })
            .always(() => {
                this.hideLoadingState();
            });
        },

        // Handle successful AJAX response
        handleSuccess(response, actionType) {
            if (response.success && response.data) {
                const data = response.data;
                
                // Update results
                this.updateResults(data.html);
                
                // Update state
                this.state.hasResults = data.total > 0;
                
                // Update UI elements
                this.updateResultsCount(data.results_text);
                this.updateUI();
                
                // Update browser history
                this.updateHistory(data.url, actionType);
                
                // Trigger custom event
                $(document).trigger('hph:archive:updated', [data, actionType]);
                
            } else {
                this.handleError(null, 'success_false', response.data || 'Unknown error');
            }
        },

        // Handle AJAX errors
        handleError(xhr, status, error) {
            console.error('Archive AJAX Error:', status, error);
            
            // Show user-friendly error message
            const errorMessage = hphArchive?.strings?.error || 'Unable to load results. Please try again.';
            this.showErrorMessage(errorMessage);
            
            // Trigger custom event
            $(document).trigger('hph:archive:error', [xhr, status, error]);
        },

        // Update results container
        updateResults(html) {
            const $container = $(this.selectors.resultsContainer);
            const $noResults = $(this.selectors.noResults);
            
            if (html && html.trim()) {
                $container.html(html);
                $container.show();
                $noResults.hide();
                
                // Re-initialize any components in the new content
                this.initializeNewContent();
            } else {
                $container.hide();
                $noResults.show();
            }
        },

        // Update results count display
        updateResultsCount(text) {
            $(this.selectors.resultsCount).text(text);
        },

        // Update UI elements to match current state
        updateUI() {
            this.updateViewButtons();
            this.updateSortSelect();
            this.updatePerPageSelect();
        },

        // Update view toggle buttons
        updateViewButtons() {
            $(this.selectors.viewButtons).removeClass('active');
            $(this.selectors.viewButtons + '[data-view="' + this.config.currentView + '"]').addClass('active');
        },

        // Update sort select
        updateSortSelect() {
            $(this.selectors.sortSelect).val(this.config.currentSort);
        },

        // Update per page select
        updatePerPageSelect() {
            $(this.selectors.perPageSelect).val(this.config.perPage);
        },

        // Show loading state
        showLoadingState() {
            this.state.isLoading = true;
            $(this.selectors.container).addClass('hph-loading');
            $(this.selectors.loadingContainer).removeClass('hph-hidden').show();
            $(this.selectors.resultsContainer).addClass('hph-opacity-50');
        },

        // Hide loading state
        hideLoadingState() {
            this.state.isLoading = false;
            $(this.selectors.container).removeClass('hph-loading');
            $(this.selectors.loadingContainer).addClass('hph-hidden').hide();
            $(this.selectors.resultsContainer).removeClass('hph-opacity-50');
        },

        // Show error message
        showErrorMessage(message) {
            // Create or update error message element
            let $errorEl = $('.hph-archive-error');
            if (!$errorEl.length) {
                $errorEl = $('<div class="hph-archive-error hph-alert hph-alert-error hph-mb-lg"></div>');
                $(this.selectors.resultsContainer).before($errorEl);
            }
            
            $errorEl.html(message).show();
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                $errorEl.fadeOut();
            }, 5000);
        },

        // Clear all filters
        clearAllFilters() {
            // Reset form
            $(this.selectors.filtersForm).find('input[type="text"], input[type="number"], textarea').val('');
            $(this.selectors.filtersForm).find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
            $(this.selectors.filtersForm).find('select').prop('selectedIndex', 0);
            
            // Reset state
            this.state.currentFilters = {};
            this.state.searchQuery = '';
            this.config.currentPage = 1;
            
            // Perform request
            this.performAjaxRequest('clear_filters');
        },

        // Update browser history
        updateHistory(url, actionType) {
            const state = {
                isArchiveAjax: true,
                postType: this.config.postType,
                view: this.config.currentView,
                sort: this.config.currentSort,
                page: this.config.currentPage,
                perPage: this.config.perPage,
                filters: this.state.currentFilters,
                searchQuery: this.state.searchQuery,
                actionType: actionType
            };
            
            const title = document.title; // Keep current title
            
            if (url !== window.location.href) {
                history.pushState(state, title, url);
            }
        },

        // Load state from browser history
        loadStateFromHistory(state) {
            this.config.currentView = state.view;
            this.config.currentSort = state.sort;
            this.config.currentPage = state.page;
            this.config.perPage = state.perPage;
            this.state.currentFilters = state.filters || {};
            this.state.searchQuery = state.searchQuery || '';
            
            this.performAjaxRequest('history');
        },

        // Initialize new content after AJAX load
        initializeNewContent() {
            // Re-initialize any JavaScript components in the new content
            // This could include image lazy loading, tooltips, etc.
            
            // Example: Reinitialize image lazy loading
            if (window.LazyLoad) {
                window.LazyLoad.update();
            }
            
            // Trigger custom event for other scripts
            $(document).trigger('hph:content:loaded');
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        // Only initialize on archive pages with AJAX enabled
        if (typeof hphArchive !== 'undefined' && hphArchive.ajaxEnabled) {
            ArchiveAjax.init();
        }
    });

    // Expose to global scope for extensibility
    window.HPHArchiveAjax = ArchiveAjax;

})(jQuery);
