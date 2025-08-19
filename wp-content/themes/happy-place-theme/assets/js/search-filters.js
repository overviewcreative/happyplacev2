/**
 * Search Filters JavaScript
 * 
 * Handles property search filtering functionality
 * AJAX-powered search with real-time filtering
 *
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Search namespace
    HPH.Search = {
        
        /**
         * Initialize search filters
         */
        init: function() {
            this.initAdvancedSearch();
            this.initQuickFilters();
            this.initMapToggle();
            this.initSaveSearch();
            this.initPagination();
        },
        
        /**
         * Initialize advanced search form
         */
        initAdvancedSearch: function() {
            var $searchForm = $('.advanced-search-form');
            
            if ($searchForm.length) {
                // Form submission
                $searchForm.on('submit', function(e) {
                    e.preventDefault();
                    HPH.Search.performSearch($(this));
                });
                
                // Real-time filtering
                $searchForm.find('input, select').on('change', HPH.debounce(function() {
                    if ($(this).closest('[data-realtime-search]').length) {
                        HPH.Search.performSearch($searchForm);
                    }
                }, 500));
                
                // Price range slider
                $searchForm.find('.price-range-slider').each(function() {
                    HPH.Search.initPriceSlider($(this));
                });
                
                // Location autocomplete
                $searchForm.find('.location-search').each(function() {
                    HPH.Search.initLocationAutocomplete($(this));
                });
                
                // Clear filters
                $searchForm.find('.clear-filters').on('click', function(e) {
                    e.preventDefault();
                    HPH.Search.clearFilters($searchForm);
                });
                
                // Toggle advanced filters
                $('.toggle-advanced-filters').on('click', function(e) {
                    e.preventDefault();
                    $searchForm.find('.advanced-filters').toggle();
                    $(this).toggleClass('filters-open');
                });
            }
        },
        
        /**
         * Initialize quick filters
         */
        initQuickFilters: function() {
            var $quickFilters = $('.quick-filters');
            
            if ($quickFilters.length) {
                $quickFilters.find('.filter-button').on('click', function(e) {
                    e.preventDefault();
                    
                    var $button = $(this);
                    var filterType = $button.data('filter-type');
                    var filterValue = $button.data('filter-value');
                    
                    // Toggle active state
                    $button.toggleClass('active');
                    
                    // Apply filter
                    HPH.Search.applyQuickFilter(filterType, filterValue, $button.hasClass('active'));
                });
            }
        },
        
        /**
         * Initialize price slider
         */
        initPriceSlider: function($slider) {
            var $minInput = $slider.find('.price-min');
            var $maxInput = $slider.find('.price-max');
            var $rangeTrack = $slider.find('.price-range-track');
            var $minThumb = $slider.find('.price-range-thumb-min');
            var $maxThumb = $slider.find('.price-range-thumb-max');
            
            var minPrice = parseInt($minInput.attr('min')) || 0;
            var maxPrice = parseInt($maxInput.attr('max')) || 1000000;
            
            // Update slider visual
            function updateSlider() {
                var minVal = parseInt($minInput.val()) || minPrice;
                var maxVal = parseInt($maxInput.val()) || maxPrice;
                
                var minPercent = ((minVal - minPrice) / (maxPrice - minPrice)) * 100;
                var maxPercent = ((maxVal - minPrice) / (maxPrice - minPrice)) * 100;
                
                $rangeTrack.css({
                    'left': minPercent + '%',
                    'width': (maxPercent - minPercent) + '%'
                });
                
                $minThumb.css('left', minPercent + '%');
                $maxThumb.css('left', maxPercent + '%');
            }
            
            // Input change events
            $minInput.add($maxInput).on('input change', function() {
                var $input = $(this);
                var value = parseInt($input.val());
                
                if ($input.hasClass('price-min')) {
                    var maxVal = parseInt($maxInput.val());
                    if (value >= maxVal) {
                        $input.val(maxVal - 1);
                    }
                } else {
                    var minVal = parseInt($minInput.val());
                    if (value <= minVal) {
                        $input.val(minVal + 1);
                    }
                }
                
                updateSlider();
            });
            
            // Initialize
            updateSlider();
        },
        
        /**
         * Initialize location autocomplete
         */
        initLocationAutocomplete: function($input) {
            var $dropdown = $('<div class="location-dropdown"></div>');
            $input.after($dropdown);
            
            $input.on('input', HPH.debounce(function() {
                var query = $(this).val();
                
                if (query.length >= 2) {
                    HPH.Search.fetchLocationSuggestions(query, $dropdown);
                } else {
                    $dropdown.hide();
                }
            }, 300));
            
            // Handle selection
            $dropdown.on('click', '.location-suggestion', function() {
                var location = $(this).text();
                $input.val(location);
                $dropdown.hide();
            });
            
            // Hide on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest($input.parent()).length) {
                    $dropdown.hide();
                }
            });
        },
        
        /**
         * Perform search
         */
        performSearch: function($form) {
            var formData = new FormData($form[0]);
            var $resultsContainer = $('.search-results');
            var $loadingIndicator = $('.search-loading');
            
            // Show loading
            $loadingIndicator.show();
            $resultsContainer.addClass('loading');
            
            // AJAX request
            $.ajax({
                url: happyPlace.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_property_search',
                    nonce: happyPlace.nonce,
                    search_data: Object.fromEntries(formData)
                },
                success: function(response) {
                    if (response.success) {
                        $resultsContainer.html(response.data.html);
                        HPH.Search.updateResultsCount(response.data.count);
                        HPH.Search.updateUrl(Object.fromEntries(formData));
                        
                        // Reinitialize components for new content
                        HPH.initComponents();
                    } else {
                        HPH.showAlert(response.data.message || 'Search failed', 'error');
                    }
                },
                error: function() {
                    HPH.showAlert('An error occurred while searching', 'error');
                },
                complete: function() {
                    $loadingIndicator.hide();
                    $resultsContainer.removeClass('loading');
                }
            });
        },
        
        /**
         * Apply quick filter
         */
        applyQuickFilter: function(filterType, filterValue, isActive) {
            var $searchForm = $('.advanced-search-form');
            var $targetField = $searchForm.find('[name="' + filterType + '"]');
            
            if ($targetField.length) {
                if ($targetField.is('select')) {
                    if (isActive) {
                        $targetField.val(filterValue);
                    } else {
                        $targetField.val('');
                    }
                } else if ($targetField.is(':checkbox')) {
                    $targetField.prop('checked', isActive);
                }
                
                // Trigger search if real-time is enabled
                if ($searchForm.data('realtime-search')) {
                    HPH.Search.performSearch($searchForm);
                }
            }
        },
        
        /**
         * Clear all filters
         */
        clearFilters: function($form) {
            $form[0].reset();
            $('.quick-filters .filter-button').removeClass('active');
            
            // Reset price sliders
            $form.find('.price-range-slider').each(function() {
                var $slider = $(this);
                var $minInput = $slider.find('.price-min');
                var $maxInput = $slider.find('.price-max');
                
                $minInput.val($minInput.attr('min'));
                $maxInput.val($maxInput.attr('max'));
                
                HPH.Search.initPriceSlider($slider);
            });
            
            // Perform search to show all results
            HPH.Search.performSearch($form);
        },
        
        /**
         * Fetch location suggestions
         */
        fetchLocationSuggestions: function(query, $dropdown) {
            $.ajax({
                url: happyPlace.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_location_autocomplete',
                    nonce: happyPlace.nonce,
                    query: query
                },
                success: function(response) {
                    if (response.success && response.data.suggestions) {
                        var html = '';
                        response.data.suggestions.forEach(function(suggestion) {
                            html += '<div class="location-suggestion">' + suggestion + '</div>';
                        });
                        $dropdown.html(html).show();
                    } else {
                        $dropdown.hide();
                    }
                },
                error: function() {
                    $dropdown.hide();
                }
            });
        },
        
        /**
         * Update results count
         */
        updateResultsCount: function(count) {
            var $countElement = $('.results-count');
            if ($countElement.length) {
                var countText = count === 1 ? 
                    count + ' property found' : 
                    count + ' properties found';
                $countElement.text(countText);
            }
        },
        
        /**
         * Update URL with search parameters
         */
        updateUrl: function(searchData) {
            var url = new URL(window.location);
            
            // Clear existing search params
            Object.keys(searchData).forEach(function(key) {
                url.searchParams.delete(key);
            });
            
            // Add new search params
            Object.keys(searchData).forEach(function(key) {
                if (searchData[key] && searchData[key] !== '') {
                    url.searchParams.set(key, searchData[key]);
                }
            });
            
            // Update URL without page reload
            window.history.replaceState({}, '', url);
        },
        
        /**
         * Initialize map toggle
         */
        initMapToggle: function() {
            var $mapToggle = $('.map-toggle');
            var $mapContainer = $('.search-map');
            var $resultsContainer = $('.search-results');
            
            if ($mapToggle.length && $mapContainer.length) {
                $mapToggle.on('click', function(e) {
                    e.preventDefault();
                    
                    var $button = $(this);
                    var isMapVisible = $mapContainer.is(':visible');
                    
                    if (isMapVisible) {
                        $mapContainer.hide();
                        $resultsContainer.removeClass('with-map');
                        $button.text('Show Map');
                    } else {
                        $mapContainer.show();
                        $resultsContainer.addClass('with-map');
                        $button.text('Hide Map');
                        
                        // Initialize map if needed
                        if (typeof HPH.Map !== 'undefined') {
                            HPH.Map.init();
                        }
                    }
                });
            }
        },
        
        /**
         * Initialize save search
         */
        initSaveSearch: function() {
            var $saveSearchBtn = $('.save-search');
            
            if ($saveSearchBtn.length) {
                $saveSearchBtn.on('click', function(e) {
                    e.preventDefault();
                    
                    var $form = $('.advanced-search-form');
                    var formData = new FormData($form[0]);
                    
                    $.ajax({
                        url: happyPlace.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'hph_save_search',
                            nonce: happyPlace.nonce,
                            search_data: Object.fromEntries(formData)
                        },
                        success: function(response) {
                            if (response.success) {
                                HPH.showAlert('Search saved successfully!', 'success');
                            } else {
                                HPH.showAlert(response.data.message || 'Failed to save search', 'error');
                            }
                        },
                        error: function() {
                            HPH.showAlert('An error occurred while saving search', 'error');
                        }
                    });
                });
            }
        },
        
        /**
         * Initialize pagination
         */
        initPagination: function() {
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                
                var $link = $(this);
                var page = $link.data('page') || 1;
                var $form = $('.advanced-search-form');
                
                // Add page to form data
                var formData = new FormData($form[0]);
                formData.append('page', page);
                
                // Perform search with pagination
                HPH.Search.performSearch($form, formData);
                
                // Scroll to results
                $('html, body').animate({
                    scrollTop: $('.search-results').offset().top - 100
                }, 500);
            });
        }
    };
    
    // Initialize search when DOM is ready
    $(document).ready(function() {
        HPH.Search.init();
    });
    
})(jQuery);
