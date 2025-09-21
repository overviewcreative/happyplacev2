/**
 * Enhanced Search and Filters Functionality
 * Fixes search panel toggle, advanced filters, and autocomplete
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

(function($) {
    'use strict';

    const SearchFilters = {
        // Configuration
        config: {
            ajaxUrl: window.hph_ajax?.ajax_url || '/wp-admin/admin-ajax.php',
            nonce: window.hph_ajax?.nonce || '',
            debounceDelay: 300,
            autocompleteMinChars: 2
        },

        // Cached selectors
        elements: {
            searchPanel: null,
            searchToggle: null,
            advancedToggle: null,
            advancedFilters: null,
            searchInput: null,
            autocompleteResults: null,
            searchForm: null
        },

        /**
         * Initialize all search and filter functionality
         */
        init() {
            this.cacheElements();
            this.bindEvents();
            this.initSearchPanelToggle();
            this.initAdvancedFiltersToggle();
            this.initAutocomplete();
            this.initFormHandlers();
            this.fixEventListeners();
        },

        /**
         * Cache DOM elements
         */
        cacheElements() {
            this.elements = {
                searchPanel: $('.hph-archive__search-panel'),
                searchToggle: $('.hph-archive__search-toggle'),
                advancedToggle: $('.advanced-toggle'),
                advancedFilters: $('.hero-advanced-filters'),
                searchInput: $('.hph-search-input, input[name="s"]'),
                autocompleteResults: $('.search-autocomplete-results'),
                searchForm: $('.hph-search-form, .search-form')
            };
        },

        /**
         * Bind all event handlers
         */
        bindEvents() {
            const self = this;

            // Prevent multiple bindings
            $(document).off('.searchFilters');

            // Search panel toggle
            $(document).on('click.searchFilters', '.hph-archive__search-toggle', function(e) {
                e.preventDefault();
                self.toggleSearchPanel($(this));
            });

            // Advanced filters toggle
            $(document).on('click.searchFilters', '.advanced-toggle', function(e) {
                e.preventDefault();
                self.toggleAdvancedFilters($(this));
            });

            // Search input focus/blur styling
            $(document).on('focus.searchFilters', '.hph-search-input, input[name="s"]', function() {
                $(this).css('border-color', 'var(--hph-primary)');
                $(this).closest('.search-input-wrapper').addClass('focused');
            });

            $(document).on('blur.searchFilters', '.hph-search-input, input[name="s"]', function() {
                $(this).css('border-color', 'var(--hph-gray-200, #E5E7EB)');
                $(this).closest('.search-input-wrapper').removeClass('focused');
            });

            // Clear filters button
            $(document).on('click.searchFilters', '.clear-filters, .hpt-clear-filters', function(e) {
                e.preventDefault();
                self.clearAllFilters();
            });

            // Form submission
            $(document).on('submit.searchFilters', '.hph-search-form, .search-form', function(e) {
                // Don't prevent default for now, let form submit normally
                // We'll enhance with AJAX later
                self.validateSearchForm($(this));
            });
        },

        /**
         * Initialize search panel toggle functionality
         */
        initSearchPanelToggle() {
            const searchToggle = this.elements.searchToggle;
            const searchPanel = this.elements.searchPanel;

            if (!searchToggle.length || !searchPanel.length) return;

            // Set initial state
            const isInitiallyOpen = searchPanel.attr('aria-hidden') === 'false';
            
            if (isInitiallyOpen) {
                searchPanel.show().removeClass('hidden');
                searchToggle.attr('aria-expanded', 'true');
                searchToggle.find('.toggle-icon').addClass('rotate-180');
            } else {
                searchPanel.hide().addClass('hidden');
                searchToggle.attr('aria-expanded', 'false');
                searchToggle.find('.toggle-icon').removeClass('rotate-180');
            }
        },

        /**
         * Toggle search panel visibility
         */
        toggleSearchPanel($toggle) {
            const searchPanel = $('.hph-archive__search-panel');
            const isExpanded = $toggle.attr('aria-expanded') === 'true';

            if (isExpanded) {
                // Close panel
                searchPanel.slideUp(300, function() {
                    $(this).addClass('hidden').attr('aria-hidden', 'true');
                });
                $toggle.attr('aria-expanded', 'false');
                $toggle.find('.toggle-icon, .fa-chevron-down').removeClass('rotate-180');
                $toggle.find('.toggle-text').text('Show Search');
            } else {
                // Open panel
                searchPanel.removeClass('hidden').attr('aria-hidden', 'false').slideDown(300);
                $toggle.attr('aria-expanded', 'true');
                $toggle.find('.toggle-icon, .fa-chevron-down').addClass('rotate-180');
                $toggle.find('.toggle-text').text('Hide Search');
            }

            // Save state to localStorage
            localStorage.setItem('hph_search_panel_open', !isExpanded);
        },

        /**
         * Initialize advanced filters toggle
         */
        initAdvancedFiltersToggle() {
            const advancedToggle = this.elements.advancedToggle;
            const advancedFilters = this.elements.advancedFilters;

            if (!advancedToggle.length || !advancedFilters.length) return;

            // Check localStorage for saved state
            const savedState = localStorage.getItem('hph_advanced_filters_open') === 'true';
            
            if (savedState) {
                advancedFilters.addClass('show').css('max-height', '1000px');
                advancedToggle.addClass('active');
                advancedToggle.find('.toggle-icon').addClass('rotate-180');
            } else {
                advancedFilters.removeClass('show').css('max-height', '0');
                advancedToggle.removeClass('active');
                advancedToggle.find('.toggle-icon').removeClass('rotate-180');
            }
        },

        /**
         * Toggle advanced filters visibility
         */
        toggleAdvancedFilters($toggle) {
            const filtersSection = $('.hero-advanced-filters');
            const isActive = $toggle.hasClass('active');

            if (isActive) {
                // Close filters
                filtersSection.removeClass('show').animate({
                    'max-height': '0',
                    'opacity': '0'
                }, 300);
                $toggle.removeClass('active');
                $toggle.find('.toggle-icon, .fa-chevron-down').removeClass('rotate-180');
                $toggle.find('.toggle-text').text('Show Advanced Filters');
            } else {
                // Open filters
                filtersSection.addClass('show').animate({
                    'max-height': '1000px',
                    'opacity': '1'
                }, 300);
                $toggle.addClass('active');
                $toggle.find('.toggle-icon, .fa-chevron-down').addClass('rotate-180');
                $toggle.find('.toggle-text').text('Hide Advanced Filters');
            }

            // Save state to localStorage
            localStorage.setItem('hph_advanced_filters_open', !isActive);
        },

        /**
         * Initialize autocomplete functionality
         * DISABLED: Using HPH framework search-autocomplete component instead
         */
        initAutocomplete() {
            // Skip autocomplete initialization - using framework component
            if ($('#header-search-input').length || $('.hph-site-header-enhanced').length) {
                return;
            }

            const self = this;
            let autocompleteTimeout;

            // Only initialize for non-header search inputs
            if (!$('.search-autocomplete-results').length) {
                $('.hph-search-input:not(#header-search-input), input[name="s"]:not(#header-search-input)').each(function() {
                    const $input = $(this);
                    if (!$input.next('.search-autocomplete-results').length && !$input.siblings('.hph-search-results').length) {
                        $input.after('<div class="search-autocomplete-results" style="display:none;"></div>');
                    }
                });
            }

            // Autocomplete on input - DISABLED for header search
            $(document).on('input.searchFilters', '.hph-search-input:not(#header-search-input), input[name="s"]:not(#header-search-input)', function() {
                // Skip if this is the header search input
                if ($(this).attr('id') === 'header-search-input' || $(this).closest('.hph-site-header-enhanced').length) {
                    return;
                }

                const $input = $(this);
                const query = $input.val().trim();
                const $results = $input.next('.search-autocomplete-results');

                clearTimeout(autocompleteTimeout);

                if (query.length < self.config.autocompleteMinChars) {
                    $results.hide().empty();
                    return;
                }

                // Show loading state
                $results.html('<div class="autocomplete-loading">Searching...</div>').show();

                autocompleteTimeout = setTimeout(() => {
                    self.fetchAutocomplete(query, $results);
                }, self.config.debounceDelay);
            });

            // Hide autocomplete on click outside
            $(document).on('click.searchFilters', function(e) {
                if (!$(e.target).closest('.search-input-wrapper, .hph-search-input').length) {
                    $('.search-autocomplete-results').hide();
                }
            });

            // Handle autocomplete item selection
            $(document).on('click.searchFilters', '.autocomplete-item', function(e) {
                e.preventDefault();
                const $item = $(this);
                const $input = $item.closest('.search-autocomplete-results').prev('input');
                const value = $item.data('value') || $item.text();
                
                $input.val(value);
                $('.search-autocomplete-results').hide();
                
                // Optionally submit the form
                if ($item.data('url')) {
                    window.location.href = $item.data('url');
                }
            });
        },

        /**
         * Fetch autocomplete suggestions via AJAX
         */
        fetchAutocomplete(query, $resultsContainer) {
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_search_autocomplete',
                    q: query,
                    nonce: this.config.nonce,
                    types: ['listing', 'agent', 'city', 'community']
                },
                success: function(response) {
                    if (response.success && response.data.suggestions) {
                        const suggestions = response.data.suggestions;
                        
                        if (suggestions.length > 0) {
                            let html = '<ul class="autocomplete-list">';
                            suggestions.forEach(item => {
                                html += `
                                    <li class="autocomplete-item" data-value="${item.title}" data-url="${item.url}">
                                        <span class="autocomplete-type">${item.type}</span>
                                        <span class="autocomplete-title">${item.title}</span>
                                        ${item.subtitle ? `<span class="autocomplete-subtitle">${item.subtitle}</span>` : ''}
                                    </li>
                                `;
                            });
                            html += '</ul>';
                            $resultsContainer.html(html).show();
                        } else {
                            $resultsContainer.html('<div class="autocomplete-no-results">No results found</div>').show();
                        }
                    } else {
                        $resultsContainer.hide();
                    }
                },
                error: function(xhr, status, error) {
                    $resultsContainer.html('<div class="autocomplete-error">Search failed. Please try again.</div>').show();
                }
            });
        },

        /**
         * Initialize form handlers
         */
        initFormHandlers() {
            // Standardize form field names
            $('input[name="type"]').each(function() {
                if ($(this).attr('type') === 'hidden') {
                    $(this).attr('name', 'post_type');
                }
            });

            // Add nonce fields if missing
            $('.hph-search-form, .search-form').each(function() {
                const $form = $(this);
                if (!$form.find('input[name="search_nonce"]').length) {
                    $form.append(`<input type="hidden" name="search_nonce" value="${SearchFilters.config.nonce}">`);
                }
            });
        },

        /**
         * Fix event listeners for inputs
         */
        fixEventListeners() {
            // Remove inline event handlers
            $('.hph-search-input, input[name="s"]').each(function() {
                const $input = $(this);
                $input.removeAttr('onfocus').removeAttr('onblur');
            });

            // Add proper hover effects for buttons using modern event listeners
            $('.hph-button, .search-button').on('mouseenter', function() {
                $(this).addClass('hover');
            }).on('mouseleave', function() {
                $(this).removeClass('hover');
            });
        },

        /**
         * Validate search form before submission
         */
        validateSearchForm($form) {
            const searchInput = $form.find('input[name="s"]').val().trim();
            
            if (searchInput.length === 0) {
                // Don't submit empty searches
                return false;
            }

            // Add loading state
            const $submitBtn = $form.find('button[type="submit"]');
            $submitBtn.prop('disabled', true).addClass('loading');
            
            return true;
        },

        /**
         * Clear all filters and reset form
         */
        clearAllFilters() {
            // Clear all form inputs
            $('.hph-search-form, .search-form').find('input[type="text"], input[type="number"], input[type="search"]').val('');
            $('.hph-search-form, .search-form').find('select').prop('selectedIndex', 0);
            $('.hph-search-form, .search-form').find('input[type="checkbox"]').prop('checked', false);
            
            // Clear price range sliders if they exist
            if ($('.price-range-slider').length) {
                $('.price-range-slider').each(function() {
                    const $slider = $(this);
                    const min = $slider.data('min');
                    const max = $slider.data('max');
                    $slider.slider('values', [min, max]);
                });
            }

            // Remove URL parameters
            const url = new URL(window.location);
            url.search = '';
            window.history.replaceState({}, '', url);

            // Trigger form submission to show all results
            $('.hph-search-form, .search-form').first().submit();
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        SearchFilters.init();

        // Restore saved states
        const searchPanelOpen = localStorage.getItem('hph_search_panel_open') === 'true';
        if (searchPanelOpen && $('.hph-archive__search-toggle').length) {
            $('.hph-archive__search-toggle').trigger('click.searchFilters');
        }
    });

    // Export for global access
    window.HPHSearchFilters = SearchFilters;

})(jQuery);
