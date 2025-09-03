/**
 * Archive Agent JavaScript - Foundation-First Architecture
 * Enhanced interactivity for agent archive pages
 * 
 * @package HappyPlaceTheme
 * @version 3.0.0
 */

(function($) {
    'use strict';

    /**
     * Agent Archive Controller
     */
    class AgentArchive {
        constructor() {
            this.config = window.hphAgentArchive || {};
            this.isLoading = false;
            this.init();
        }

        /**
         * Initialize agent archive functionality
         */
        init() {
            this.bindEvents();
            this.initializeFilters();
            this.setupViewToggle();
            this.initializeContactActions();
            
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
            
            // Agent contact actions
            $(document).on('click', '.agent-contact-btn', this.handleContactClick.bind(this));
            
            // Filter toggles
            $(document).on('click', '[data-filter-toggle]', this.handleFilterToggle.bind(this));
            
            // Advanced filters toggle
            $(document).on('click', '.advanced-toggle', this.handleAdvancedToggle.bind(this));
            
            // Search form submission
            $(document).on('submit', '.hero-search-form', this.handleSearchSubmit.bind(this));
            
            // Filter form changes
            $(document).on('change', '.hero-search-form select', this.handleFilterChange.bind(this));
            $(document).on('change', '.hero-search-form input[type="checkbox"]', this.handleFilterChange.bind(this));
            
            // Clear filters
            $(document).on('click', '.clear-filters', this.handleClearFilters.bind(this));
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
            
            // Update URL if needed
            if (this.config.ajaxEnabled) {
                this.updateUrl({ view: newView });
            }
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
                // Redirect with new sort
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
                // Redirect with new per page
                window.location.href = this.updateUrlParam('per_page', newPerPage);
            }
        }

        /**
         * Initialize filters
         */
        initializeFilters() {
            // Initialize any custom filter components
            $('.archive-filters').each(function() {
                // Filter initialization logic here
            });
        }

        /**
         * Setup view toggle buttons
         */
        setupViewToggle() {
            const currentView = this.config.currentView || 'grid';
            $(`[data-view-mode="${currentView}"]`).addClass('active');
        }

        /**
         * Initialize contact actions
         */
        initializeContactActions() {
            // Add click tracking for agent contact buttons
            $('.agent-contact-btn').each(function() {
                const $btn = $(this);
                const agentId = $btn.closest('.hph-card').data('agent-id');
                
                if (agentId) {
                    $btn.data('agent-id', agentId);
                }
            });
        }

        /**
         * Handle agent contact clicks
         */
        handleContactClick(e) {
            const $btn = $(e.currentTarget);
            const agentId = $btn.data('agent-id');
            const actionType = $btn.data('contact-type') || 'general';
            
            // Track the interaction
            if (window.gtag) {
                gtag('event', 'agent_contact_click', {
                    'agent_id': agentId,
                    'contact_type': actionType,
                    'page_location': window.location.href
                });
            }
        }

        /**
         * Handle filter toggles
         */
        handleFilterToggle(e) {
            e.preventDefault();
            
            const $toggle = $(e.currentTarget);
            const $filters = $('.archive-filters');
            
            $filters.toggleClass('filters-visible');
            $toggle.toggleClass('active');
        }

        /**
         * Handle advanced filters toggle
         */
        handleAdvancedToggle(e) {
            e.preventDefault();
            
            const $toggle = $(e.currentTarget);
            const $advanced = $('.hero-advanced-filters');
            const $chevron = $toggle.find('.hph-icon');
            
            // Toggle visibility
            $advanced.slideToggle(300);
            
            // Toggle button state
            $toggle.toggleClass('active');
            
            // Rotate chevron
            if ($toggle.hasClass('active')) {
                $chevron.addClass('rotate-180');
            } else {
                $chevron.removeClass('rotate-180');
            }
        }

        /**
         * Handle search form submission
         */
        handleSearchSubmit(e) {
            e.preventDefault();
            
            const $form = $(e.currentTarget);
            const formData = this.getFormData($form);
            
            if (this.config.ajaxEnabled) {
                this.loadResults(formData);
            } else {
                // Build URL with parameters
                const url = this.buildSearchUrl(formData);
                window.location.href = url;
            }
        }

        /**
         * Handle filter changes (auto-submit)
         */
        handleFilterChange(e) {
            if (this.config.autoFilter !== false) {
                // Debounce the filter change
                clearTimeout(this.filterTimeout);
                this.filterTimeout = setTimeout(() => {
                    const $form = $('.hero-search-form');
                    this.handleSearchSubmit({ currentTarget: $form, preventDefault: () => {} });
                }, 300);
            }
        }

        /**
         * Handle clear filters
         */
        handleClearFilters(e) {
            e.preventDefault();
            
            const $form = $('.hero-search-form');
            
            // Clear all form fields
            $form[0].reset();
            $form.find('select').prop('selectedIndex', 0);
            $form.find('input[type="checkbox"]').prop('checked', false);
            
            // Trigger search with cleared filters
            this.handleSearchSubmit({ currentTarget: $form, preventDefault: () => {} });
        }

        /**
         * Get form data as object
         */
        getFormData($form) {
            const formData = {};
            const serialized = $form.serializeArray();
            
            // Convert to object
            serialized.forEach(item => {
                if (formData[item.name]) {
                    // Handle arrays (multiple values)
                    if (!Array.isArray(formData[item.name])) {
                        formData[item.name] = [formData[item.name]];
                    }
                    formData[item.name].push(item.value);
                } else {
                    formData[item.name] = item.value;
                }
            });
            
            // Handle checkboxes that might not be in serialized data
            $form.find('input[type="checkbox"]').each(function() {
                const $checkbox = $(this);
                const name = $checkbox.attr('name');
                
                if ($checkbox.is(':checked')) {
                    if (!formData[name]) {
                        formData[name] = [];
                    }
                    if (!Array.isArray(formData[name])) {
                        formData[name] = [formData[name]];
                    }
                    if (formData[name].indexOf($checkbox.val()) === -1) {
                        formData[name].push($checkbox.val());
                    }
                }
            });
            
            return formData;
        }

        /**
         * Build search URL from form data
         */
        buildSearchUrl(formData) {
            const url = new URL(window.location.href);
            
            // Clear existing search params
            ['keyword', 'specialty', 'language', 'office', 'experience', 'sort', 'view'].forEach(param => {
                url.searchParams.delete(param);
            });
            
            // Add new params
            Object.keys(formData).forEach(key => {
                const value = formData[key];
                if (value && value !== '') {
                    if (Array.isArray(value)) {
                        value.forEach(v => url.searchParams.append(key + '[]', v));
                    } else {
                        url.searchParams.set(key, value);
                    }
                }
            });
            
            return url.toString();
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
         * Load results via AJAX
         */
        loadResults(params = {}) {
            if (this.isLoading) return;
            
            this.isLoading = true;
            this.showLoading();
            
            const data = {
                action: 'hph_filter_agents',
                nonce: this.config.nonce,
                post_type: 'agent',
                ...this.config,
                ...params
            };
            
            $.ajax({
                url: this.config.ajaxUrl || window.ajaxurl,
                type: 'POST',
                data: data,
                success: this.handleLoadSuccess.bind(this),
                error: this.handleLoadError.bind(this),
                complete: this.handleLoadComplete.bind(this)
            });
        }

        /**
         * Handle successful AJAX load
         */
        handleLoadSuccess(response) {
            if (response.success && response.data.html) {
                $('.archive-layout-content').html(response.data.html);
                
                // Update URL
                if (response.data.url) {
                    history.pushState(null, '', response.data.url);
                }
                
                // Update config
                if (response.data.config) {
                    Object.assign(this.config, response.data.config);
                }
            } else {
                this.handleLoadError();
            }
        }

        /**
         * Handle AJAX load error
         */
        handleLoadError() {
            const errorMessage = this.config.strings?.error || 'Unable to load agents. Please try again.';
            $('.archive-layout-content').prepend(
                `<div class="alert alert-danger">${errorMessage}</div>`
            );
        }

        /**
         * Handle AJAX load completion
         */
        handleLoadComplete() {
            this.isLoading = false;
            this.hideLoading();
        }

        /**
         * Show loading state
         */
        showLoading() {
            $('.archive-layout').addClass('is-loading');
            
            const loadingMessage = this.config.strings?.loading || 'Loading agents...';
            $('.archive-layout-content').prepend(
                `<div class="loading-overlay">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">${loadingMessage}</div>
                </div>`
            );
        }

        /**
         * Hide loading state
         */
        hideLoading() {
            $('.archive-layout').removeClass('is-loading');
            $('.loading-overlay').remove();
        }

        /**
         * Update URL parameters
         */
        updateUrl(params) {
            const url = new URL(window.location);
            
            Object.keys(params).forEach(key => {
                if (params[key]) {
                    url.searchParams.set(key, params[key]);
                } else {
                    url.searchParams.delete(key);
                }
            });
            
            history.pushState(null, '', url);
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
        if (document.body.classList.contains('post-type-archive-agent')) {
            new AgentArchive();
        }
    });

})(jQuery);