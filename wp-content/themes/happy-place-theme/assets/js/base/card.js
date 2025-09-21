/**
 * Base Card Component JavaScript
 * Universal card functionality for all post types
 * 
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Ensure HPH global exists
    if (typeof window.HPH === 'undefined') {
        window.HPH = {};
    }
    
    // Card namespace
    HPH.Card = {
        
        /**
         * Initialize card components
         */
        init: function() {
            this.initCardHover();
            this.initCardActions();
            this.initCardFilters();
            this.initCardSorting();
        },
        
        /**
         * Initialize card hover effects
         */
        initCardHover: function() {
            $('.hph-card').on('mouseenter', function() {
                $(this).addClass('hph-card--hover');
            }).on('mouseleave', function() {
                $(this).removeClass('hph-card--hover');
            });
        },
        
        /**
         * Initialize card actions
         */
        initCardActions: function() {
            // Favorite functionality
            $('.hph-card__action[data-action="favorite"]').on('click', function(e) {
                e.preventDefault();
                HPH.Card.toggleFavorite($(this));
            });
            
            // Share functionality
            $('.hph-card__action[data-action="share"]').on('click', function(e) {
                e.preventDefault();
                HPH.Card.shareCard($(this));
            });
            
            // Compare functionality
            $('.hph-card__action[data-action="compare"]').on('click', function(e) {
                e.preventDefault();
                HPH.Card.toggleCompare($(this));
            });
            
            // Contact functionality
            $('.hph-card__action[data-action="contact"]').on('click', function(e) {
                e.preventDefault();
                HPH.Card.showContactForm($(this));
            });
        },
        
        /**
         * Initialize card filters
         */
        initCardFilters: function() {
            $('.hph-card-filter').on('click', function(e) {
                e.preventDefault();
                
                var $filter = $(this);
                var filterType = $filter.data('filter-type');
                var filterValue = $filter.data('filter-value');
                var $container = $filter.closest('.hph-cards-container');
                
                // Toggle filter active state
                $filter.toggleClass('active');
                
                // Apply filter
                HPH.Card.applyFilter($container, filterType, filterValue, $filter.hasClass('active'));
            });
        },
        
        /**
         * Initialize card sorting
         */
        initCardSorting: function() {
            $('.hph-card-sort').on('change', function() {
                var $select = $(this);
                var sortBy = $select.val();
                var $container = $select.closest('.hph-cards-container');
                
                HPH.Card.sortCards($container, sortBy);
            });
        },
        
        /**
         * Toggle favorite status
         */
        toggleFavorite: function($button) {
            var postId = $button.data('post-id');
            var isFavorited = $button.hasClass('favorited');
            
            // Show loading state
            $button.addClass('loading');
            
            $.ajax({
                url: hphContext.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_toggle_favorite',
                    nonce: hphContext.nonce,
                    post_id: postId,
                    is_favorited: isFavorited
                },
                success: function(response) {
                    if (response.success) {
                        $button.toggleClass('favorited');
                        var icon = $button.find('i');
                        
                        if ($button.hasClass('favorited')) {
                            icon.removeClass('far').addClass('fas');
                            HPH.showAlert(hphContext.strings.addedToFavorites || 'Added to favorites', 'success');
                        } else {
                            icon.removeClass('fas').addClass('far');
                            HPH.showAlert(hphContext.strings.removedFromFavorites || 'Removed from favorites', 'info');
                        }
                    } else {
                        HPH.showAlert(response.data.message || hphContext.strings.error, 'error');
                    }
                },
                error: function() {
                    HPH.showAlert(hphContext.strings.error, 'error');
                },
                complete: function() {
                    $button.removeClass('loading');
                }
            });
        },
        
        /**
         * Share card functionality
         */
        shareCard: function($button) {
            var postId = $button.data('post-id');
            var postTitle = $button.data('post-title');
            var postUrl = $button.data('post-url');
            
            if (navigator.share && window.location.protocol === 'https:') {
                navigator.share({
                    title: postTitle,
                    url: postUrl
                }).catch(function(error) {
                    console.log('Error sharing:', error);
                });
            } else {
                // Fallback to modal
                HPH.Card.showShareModal(postTitle, postUrl);
            }
        },
        
        /**
         * Show share modal
         */
        showShareModal: function(title, url) {
            var encodedUrl = encodeURIComponent(url);
            var encodedTitle = encodeURIComponent(title);
            
            var modalHtml = `
                <div id="share-modal" class="hph-modal">
                    <div class="hph-modal-content">
                        <div class="hph-modal-header">
                            <h3>Share Property</h3>
                            <button type="button" class="hph-modal-close">&times;</button>
                        </div>
                        <div class="hph-modal-body">
                            <div class="share-options">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}" target="_blank" class="share-option facebook">
                                    <i class="fab fa-facebook-f"></i> Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}" target="_blank" class="share-option twitter">
                                    <i class="fab fa-twitter"></i> Twitter
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}" target="_blank" class="share-option linkedin">
                                    <i class="fab fa-linkedin-in"></i> LinkedIn
                                </a>
                                <button type="button" class="share-option copy-link" data-url="${url}">
                                    <i class="fas fa-link"></i> Copy Link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            HPH.openModal('share-modal');
            
            // Copy link functionality
            $('.copy-link').on('click', function() {
                var url = $(this).data('url');
                HPH.copyToClipboard(url);
                HPH.showAlert('Link copied to clipboard!', 'success');
                HPH.closeModal();
            });
        },
        
        /**
         * Toggle compare functionality
         */
        toggleCompare: function($button) {
            var postId = $button.data('post-id');
            var isComparing = $button.hasClass('comparing');
            
            if (isComparing) {
                HPH.Card.removeFromComparison(postId);
                $button.removeClass('comparing');
            } else {
                if (HPH.Card.addToComparison(postId)) {
                    $button.addClass('comparing');
                }
            }
            
            HPH.Card.updateComparisonUI();
        },
        
        /**
         * Add to comparison
         */
        addToComparison: function(postId) {
            var comparison = JSON.parse(localStorage.getItem('hph_comparison') || '[]');
            
            if (comparison.length >= 3) {
                HPH.showAlert('You can compare up to 3 properties at a time', 'warning');
                return false;
            }
            
            if (comparison.indexOf(postId) === -1) {
                comparison.push(postId);
                localStorage.setItem('hph_comparison', JSON.stringify(comparison));
            }
            
            return true;
        },
        
        /**
         * Remove from comparison
         */
        removeFromComparison: function(postId) {
            var comparison = JSON.parse(localStorage.getItem('hph_comparison') || '[]');
            var index = comparison.indexOf(postId);
            
            if (index > -1) {
                comparison.splice(index, 1);
                localStorage.setItem('hph_comparison', JSON.stringify(comparison));
            }
        },
        
        /**
         * Update comparison UI
         */
        updateComparisonUI: function() {
            var comparison = JSON.parse(localStorage.getItem('hph_comparison') || '[]');
            var $comparisonBar = $('#comparison-bar');
            
            if (comparison.length > 0) {
                if (!$comparisonBar.length) {
                    var comparisonHtml = `
                        <div id="comparison-bar" class="comparison-bar">
                            <div class="comparison-content">
                                <span class="comparison-count">${comparison.length} items selected</span>
                                <div class="comparison-actions">
                                    <button type="button" class="compare-now hph-btn hph-btn-primary hph-btn-sm">Compare Now</button>
                                    <button type="button" class="clear-comparison hph-btn hph-btn-outline hph-btn-sm">Clear All</button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('body').append(comparisonHtml);
                    
                    // Bind events
                    $('.compare-now').on('click', function() {
                        window.location.href = '/compare?ids=' + comparison.join(',');
                    });
                    
                    $('.clear-comparison').on('click', function() {
                        localStorage.removeItem('hph_comparison');
                        $('.hph-card__action[data-action="compare"]').removeClass('comparing');
                        $('#comparison-bar').remove();
                    });
                } else {
                    $comparisonBar.find('.comparison-count').text(comparison.length + ' items selected');
                }
            } else {
                $comparisonBar.remove();
            }
        },
        
        /**
         * Show contact form
         */
        showContactForm: function($button) {
            var postId = $button.data('post-id');
            var postTitle = $button.data('post-title');
            
            // Load contact form via AJAX
            $.ajax({
                url: hphContext.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_get_contact_form',
                    nonce: hphContext.nonce,
                    post_id: postId,
                    post_title: postTitle
                },
                success: function(response) {
                    if (response.success) {
                        $('body').append(response.data.html);
                        HPH.openModal('contact-modal');
                    } else {
                        HPH.showAlert(response.data.message || hphContext.strings.error, 'error');
                    }
                },
                error: function() {
                    HPH.showAlert(hphContext.strings.error, 'error');
                }
            });
        },
        
        /**
         * Apply filter to cards
         */
        applyFilter: function($container, filterType, filterValue, isActive) {
            var $cards = $container.find('.hph-card');
            
            if (isActive) {
                // Show only cards that match the filter
                $cards.each(function() {
                    var $card = $(this);
                    var cardValue = $card.data(filterType);
                    
                    if (cardValue === filterValue) {
                        $card.show();
                    } else {
                        $card.hide();
                    }
                });
            } else {
                // Remove filter - show all cards
                var activeFilters = $container.find('.hph-card-filter.active');
                
                if (activeFilters.length === 0) {
                    $cards.show();
                } else {
                    // Re-apply remaining active filters
                    activeFilters.each(function() {
                        var $filter = $(this);
                        HPH.Card.applyFilter($container, $filter.data('filter-type'), $filter.data('filter-value'), true);
                    });
                }
            }
            
            // Update results count
            var visibleCount = $container.find('.hph-card:visible').length;
            $container.find('.results-count').text(visibleCount + ' results');
        },
        
        /**
         * Sort cards
         */
        sortCards: function($container, sortBy) {
            var $cards = $container.find('.hph-card');
            
            $cards.sort(function(a, b) {
                var aVal = $(a).data(sortBy);
                var bVal = $(b).data(sortBy);
                
                // Handle different data types
                if (sortBy === 'price') {
                    aVal = parseInt(aVal) || 0;
                    bVal = parseInt(bVal) || 0;
                    return bVal - aVal; // Highest first
                } else if (sortBy === 'date') {
                    aVal = new Date(aVal);
                    bVal = new Date(bVal);
                    return bVal - aVal; // Newest first
                } else {
                    // String comparison
                    return aVal.localeCompare(bVal);
                }
            });
            
            // Reorder cards in DOM
            var $parent = $cards.parent();
            $cards.detach().appendTo($parent);
        }
    };
    
    // Initialize cards when DOM is ready
    $(document).ready(function() {
        HPH.Card.init();
    });
    
})(jQuery);
