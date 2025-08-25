/**
 * Archive Functionality - View switching, search, and sorting
 * 
 * @package HappyPlaceTheme
 */

jQuery(document).ready(function($) {
    
    // Archive controls functionality
    function initArchiveControls() {
        
        // View switcher functionality
        $('.hph-archive-layout [data-view]').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var newView = $button.data('view');
            var $container = $('.hph-archive-layout');
            var $grid = $('.hph-card-grid');
            
            // Update active button
            $button.siblings('[data-view]').removeClass('hph-bg-white hph-text-primary-600 hph-shadow-sm')
                   .addClass('hph-text-gray-600 hph-hover:text-gray-900 hph-hover:bg-gray-50');
            $button.removeClass('hph-text-gray-600 hph-hover:text-gray-900 hph-hover:bg-gray-50')
                   .addClass('hph-bg-white hph-text-primary-600 hph-shadow-sm');
            
            // Update container view attribute
            $container.attr('data-view', newView);
            
            // Apply view-specific classes
            switch(newView) {
                case 'list':
                    $grid.removeClass('hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 hph-gap-lg')
                         .addClass('hph-space-y-lg');
                    
                    // Update cards to list layout
                    $grid.find('.hph-grid-item > article').each(function() {
                        var $card = $(this);
                        $card.addClass('hph-flex hph-flex-col md:hph-flex-row');
                        
                        // Move image to left on desktop
                        var $imageContainer = $card.find('> div').first();
                        if ($imageContainer.length) {
                            $imageContainer.addClass('hph-w-full md:hph-w-2/5');
                        }
                        
                        // Adjust content container
                        var $contentContainer = $card.find('> div').last();
                        if ($contentContainer.length) {
                            $contentContainer.addClass('hph-flex-1');
                        }
                    });
                    break;
                    
                case 'grid':
                default:
                    $grid.removeClass('hph-space-y-lg')
                         .addClass('hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 hph-gap-lg');
                    
                    // Reset cards to grid layout
                    $grid.find('.hph-grid-item > article').each(function() {
                        var $card = $(this);
                        $card.removeClass('hph-flex hph-flex-col md:hph-flex-row');
                        
                        // Reset image container
                        var $imageContainer = $card.find('> div').first();
                        if ($imageContainer.length) {
                            $imageContainer.removeClass('hph-w-full md:hph-w-2/5').addClass('hph-w-full');
                        }
                        
                        // Reset content container
                        var $contentContainer = $card.find('> div').last();
                        if ($contentContainer.length) {
                            $contentContainer.removeClass('hph-flex-1').addClass('hph-w-full');
                        }
                    });
                    break;
            }
            
            // Add transition animation
            $grid.addClass('hph-animate-fade-in-up');
            setTimeout(function() {
                $grid.removeClass('hph-animate-fade-in-up');
            }, 300);
            
            // Update URL without refresh
            var currentUrl = new URL(window.location);
            currentUrl.searchParams.set('view', newView);
            window.history.replaceState({}, '', currentUrl);
        });
        
        // Sort functionality
        $('#archive-sort').on('change', function() {
            var sortValue = $(this).val();
            var currentUrl = new URL(window.location);
            currentUrl.searchParams.set('sort', sortValue);
            window.location.href = currentUrl.toString();
        });
        
        // Per-page functionality
        $('#per-page').on('change', function() {
            var perPageValue = $(this).val();
            var currentUrl = new URL(window.location);
            currentUrl.searchParams.set('per_page', perPageValue);
            currentUrl.searchParams.delete('paged'); // Reset to first page
            window.location.href = currentUrl.toString();
        });
        
        // Advanced search toggle
        $('button[aria-controls="advanced-search"]').on('click', function() {
            var $button = $(this);
            var $searchSection = $('#advanced-search');
            var isExpanded = $button.attr('aria-expanded') === 'true';
            
            if (isExpanded) {
                // Collapse
                $searchSection.addClass('hph-hidden').attr('aria-hidden', 'true');
                $button.attr('aria-expanded', 'false');
                $button.find('.fa-chevron-down').removeClass('hph-rotate-180');
            } else {
                // Expand
                $searchSection.removeClass('hph-hidden').attr('aria-hidden', 'false');
                $button.attr('aria-expanded', 'true');
                $button.find('.fa-chevron-down').addClass('hph-rotate-180');
            }
        });
    }
    
    // Initialize archive controls
    initArchiveControls();
    
    // Card interactions
    function initCardInteractions() {
        
        // Favorite button functionality (for listings)
        $('.hph-card-grid').on('click', '[title*="favorite"]', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $icon = $button.find('i');
            var isFavorited = $icon.hasClass('fas');
            
            // Toggle icon
            if (isFavorited) {
                $icon.removeClass('fas fa-heart').addClass('far fa-heart');
                $button.removeClass('hph-text-red-500 hph-bg-red-50')
                       .addClass('hph-text-gray-500 hph-hover:text-red-500 hph-hover:bg-red-50');
            } else {
                $icon.removeClass('far fa-heart').addClass('fas fa-heart');
                $button.removeClass('hph-text-gray-500 hph-hover:text-red-500 hph-hover:bg-red-50')
                       .addClass('hph-text-red-500 hph-bg-red-50');
            }
            
            // Add animation
            $button.addClass('hph-animate-bounce');
            setTimeout(function() {
                $button.removeClass('hph-animate-bounce');
            }, 600);
            
            // Here you would typically send an AJAX request to save the favorite
            console.log('Favorite toggled for post:', $button.closest('article').attr('id'));
        });
        
        // Share button functionality
        $('.hph-card-grid').on('click', '[title*="share"], [title*="Share"]', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $card = $button.closest('article');
            var postUrl = $card.find('h3 a').attr('href');
            var postTitle = $card.find('h3 a').text();
            
            // Simple share functionality (you could enhance this with a modal)
            if (navigator.share) {
                navigator.share({
                    title: postTitle,
                    url: postUrl
                }).then(function() {
                    console.log('Shared successfully');
                }).catch(function(error) {
                    console.log('Error sharing:', error);
                    fallbackShare(postUrl, postTitle);
                });
            } else {
                fallbackShare(postUrl, postTitle);
            }
        });
        
        function fallbackShare(url, title) {
            // Copy to clipboard as fallback
            navigator.clipboard.writeText(url).then(function() {
                // Show temporary notification
                var $notification = $('<div class="hph-fixed hph-bottom-4 hph-right-4 hph-bg-gray-900 hph-text-white hph-px-lg hph-py-md hph-rounded-lg hph-shadow-lg hph-z-50 hph-animate-fade-in">Link copied to clipboard!</div>');
                $('body').append($notification);
                setTimeout(function() {
                    $notification.addClass('hph-animate-fade-out');
                    setTimeout(function() {
                        $notification.remove();
                    }, 300);
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }
        
        // Contact buttons for agents
        $('.hph-card-grid').on('click', '[title*="Call"], [title*="Email"]', function(e) {
            var $button = $(this);
            var action = $button.attr('title').toLowerCase().includes('call') ? 'call' : 'email';
            var $card = $button.closest('article');
            
            // Add animation
            $button.addClass('hph-animate-pulse');
            setTimeout(function() {
                $button.removeClass('hph-animate-pulse');
            }, 600);
            
            // Here you would typically open a contact modal or redirect to contact info
            console.log('Contact action:', action, 'for agent:', $card.attr('id'));
        });
    }
    
    // Initialize card interactions
    initCardInteractions();
    
    // Responsive grid adjustments
    function handleResponsiveGrid() {
        var $grid = $('.hph-card-grid');
        var viewMode = $('.hph-archive-layout').attr('data-view') || 'grid';
        
        // Only apply responsive adjustments in grid view
        if (viewMode === 'grid') {
            var windowWidth = $(window).width();
            var newClasses;
            
            if (windowWidth < 768) {
                // Mobile: 1 column
                newClasses = 'hph-grid hph-grid-cols-1 hph-gap-lg';
            } else if (windowWidth < 1024) {
                // Tablet: 2 columns
                newClasses = 'hph-grid hph-grid-cols-2 hph-gap-lg';
            } else {
                // Desktop: 3 columns
                newClasses = 'hph-grid hph-grid-cols-3 hph-gap-lg';
            }
            
            // Update grid classes if they've changed
            var currentClasses = $grid.attr('class');
            if (!currentClasses.includes(newClasses.split(' ').pop())) {
                $grid.removeClass('hph-grid-cols-1 hph-grid-cols-2 hph-grid-cols-3')
                     .addClass(newClasses.split(' ').slice(-2).join(' '));
            }
        }
    }
    
    // Handle window resize
    var resizeTimeout;
    $(window).on('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleResponsiveGrid, 150);
    });
    
    // Initial responsive adjustment
    handleResponsiveGrid();
    
    // Smooth scrolling for anchor links (like "Advanced Search")
    $('a[href^="#"]').on('click', function(e) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 600);
        }
    });
    
});