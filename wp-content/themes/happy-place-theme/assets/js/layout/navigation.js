/**
 * Navigation JavaScript
 * 
 * Handles navigation functionality for the Happy Place Theme
 * Mobile menu, dropdown menus, and navigation enhancements
 *
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Navigation namespace
    HPH.Navigation = {
        
        /**
         * Initialize navigation
         */
        init: function() {
            this.initMobileMenu();
            this.initDropdowns();
            this.initStickyHeader();
            this.initSearchToggle();
            this.initHeaderSpacing();
        },
        
        /**
         * Initialize mobile menu
         */
        initMobileMenu: function() {
            var $menuToggle = $('.hph-mobile-toggle');
            var $mobileMenu = $('.hph-mobile-menu');
            var $mobileClose = $('.hph-mobile-close');
            var $mobileOverlay = $('.hph-mobile-overlay');
            var $body = $('body');
            
            if ($menuToggle.length && $mobileMenu.length) {
                // Menu toggle click
                $menuToggle.on('click', function(e) {
                    e.preventDefault();
                    
                    var isOpen = $body.hasClass('hph-mobile-menu-open');
                    
                    if (isOpen) {
                        HPH.Navigation.closeMobileMenu();
                    } else {
                        HPH.Navigation.openMobileMenu();
                    }
                });
                
                // Close button click
                $mobileClose.on('click', function(e) {
                    e.preventDefault();
                    HPH.Navigation.closeMobileMenu();
                });
                
                // Overlay click
                $mobileOverlay.on('click', function(e) {
                    e.preventDefault();
                    HPH.Navigation.closeMobileMenu();
                });
                
                // Close on window resize
                $(window).on('resize', HPH.debounce(function() {
                    if ($(window).width() >= 1024) {
                        HPH.Navigation.closeMobileMenu();
                    }
                }, 250));
                
                // Handle submenu toggles
                $mobileMenu.on('click', '.menu-item-has-children > a', function(e) {
                    e.preventDefault();
                    var $item = $(this).parent();
                    $item.toggleClass('submenu-open');
                });
            }
        },
        
        /**
         * Create mobile menu
         */
        createMobileMenu: function() {
            var $primaryMenu = $('.nav-menu').clone();
            var $mobileMenuHtml = `
                <div class='mobile-menu">
                    <div class="mobile-menu-header">
                        <div class="mobile-menu-logo">
                            ${$('.navbar-brand').html()}
                        </div>
                        <button type="button" class="mobile-menu-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mobile-menu-content">
                        <nav class="mobile-nav">
                            ${$primaryMenu.length ? $primaryMenu.prop('outerHTML') : ''}
                        </nav>
                    </div>
                </div>
                <div class="mobile-menu-overlay"></div>`;
            
            $('body').append($mobileMenuHtml);
            
            // Close button click
            $('.mobile-menu-close, .mobile-menu-overlay').on('click', function() {
                HPH.Navigation.closeMobileMenu();
            });
        },
        
        /**
         * Open mobile menu
         */
        openMobileMenu: function() {
            $('body').addClass('hph-mobile-menu-open');
            $('.hph-mobile-toggle').attr('aria-expanded', 'true');
            
            // Focus management
            setTimeout(function() {
                $('.hph-mobile-menu .hph-mobile-menu-list a:first').focus();
            }, 300);
        },
        
        /**
         * Close mobile menu
         */
        closeMobileMenu: function() {
            $('body').removeClass('hph-mobile-menu-open');
            $('.hph-mobile-toggle').attr('aria-expanded', 'false');
            $('.menu-item-has-children').removeClass('submenu-open');
        },
        
        /**
         * Initialize dropdown menus
         */
        initDropdowns: function() {
            var $menuItems = $('.hph-nav-menu .menu-item-has-children');
            
            $menuItems.each(function() {
                var $item = $(this);
                var $link = $item.children('a');
                var $submenu = $item.children('.sub-menu');
                
                // Add dropdown indicator
                if (!$link.find('.dropdown-indicator').length) {
                    $link.append('<i class="dropdown-indicator fas fa-chevron-down"></i>');
                }
                
                // Hover events for desktop
                $item.on('mouseenter', function() {
                    if ($(window).width() >= 1024) {
                        $(this).addClass('dropdown-open');
                    }
                }).on('mouseleave', function() {
                    if ($(window).width() >= 1024) {
                        $(this).removeClass('dropdown-open');
                    }
                });
                
                // Click events for touch devices
                $link.on('click', function(e) {
                    if ($(window).width() >= 1024 && $submenu.length) {
                        if (!$item.hasClass('dropdown-open')) {
                            e.preventDefault();
                            $menuItems.removeClass('dropdown-open');
                            $item.addClass('dropdown-open');
                        }
                    }
                });
            });
            
            // Close dropdowns on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.menu-item-has-children').length) {
                    $menuItems.removeClass('dropdown-open');
                }
            });
        },
        
        /**
         * Initialize sticky header
         */
        initStickyHeader: function() {
            var $header = $('#masthead');
            var headerHeight = $header.outerHeight();
            var scrollThreshold = headerHeight;
            
            $(window).on('scroll', HPH.throttle(function() {
                var scrollTop = $(window).scrollTop();
                
                if (scrollTop > scrollThreshold) {
                    $header.addClass('header-sticky');
                    $('body').css('padding-top', headerHeight + 'px');
                } else {
                    $header.removeClass('header-sticky');
                    $('body').css('padding-top', '0');
                }
            }, 100));
        },
        
        /**
         * Initialize search toggle
         */
        initSearchToggle: function() {
            var $searchToggle = $('.hph-search-toggle');
            var $searchBar = $('.hph-search-bar');
            var $searchClose = $('.hph-search-close');
            
            if ($searchToggle.length && $searchBar.length) {
                console.log('HPH Navigation: Search toggle elements found', {
                    toggleButton: $searchToggle.length,
                    searchBar: $searchBar.length
                });
                
                // Toggle search bar
                $searchToggle.on('click', function(e) {
                    console.log('HPH Navigation: Search toggle clicked');
                    e.preventDefault();
                    $searchBar.toggleClass('active');
                    $('body').toggleClass('hph-search-active');
                    
                    if ($searchBar.hasClass('active')) {
                        setTimeout(function() {
                            $searchBar.find('.hph-search-input').focus();
                        }, 100);
                    }
                });
                
                // Close search bar
                $searchClose.on('click', function(e) {
                    e.preventDefault();
                    $searchBar.removeClass('active');
                    $('body').removeClass('hph-search-active');
                });
                
                // Close on outside click
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.hph-search-bar, .hph-search-toggle').length) {
                        $searchBar.removeClass('active');
                        $('body').removeClass('hph-search-active');
                    }
                });
                
                // Close on escape
                $(document).on('keydown', function(e) {
                    if (e.key === 'Escape') {
                        $searchBar.removeClass('active');
                        $('body').removeClass('hph-search-active');
                    }
                });
            }
        },
        
        /**
         * Initialize header spacing to prevent content overlap
         */
        initHeaderSpacing: function() {
            var $body = $('body');
            var $header = $('.hph-header');
            var $topbar = $('.hph-topbar');
            var $searchBar = $('.hph-search-bar');
            
            // Add class to body if sticky header exists
            if ($header.hasClass('hph-sticky-header')) {
                $body.addClass('hph-sticky-header-enabled');
            }
            
            // Track header states
            var headerStates = {
                isScrolled: false,
                isCompact: false,
                isSearchActive: false,
                topbarVisible: true
            };
            
            // Function to update body classes based on header state
            function updateBodyClasses() {
                $body.toggleClass('header-scrolled', headerStates.isScrolled);
                $body.toggleClass('header-compact', headerStates.isCompact);
                $body.toggleClass('hph-search-active', headerStates.isSearchActive);
                $body.toggleClass('topbar-visible', headerStates.topbarVisible);
            }
            
            // Function to calculate and set proper body padding
            function updateHeaderSpacing() {
                var headerHeight = $header.outerHeight() || 80;
                var topbarHeight = $topbar.length && $topbar.is(':visible') ? $topbar.outerHeight() : 0;
                var searchBarHeight = $searchBar.length && $searchBar.hasClass('active') ? $searchBar.outerHeight() : 0;
                
                // Update CSS custom properties with actual measurements
                $(':root').css('--hph-calculated-header-height', headerHeight + 'px');
                $(':root').css('--hph-calculated-topbar-height', topbarHeight + 'px');
                $(':root').css('--hph-calculated-search-height', searchBarHeight + 'px');
                
                // Update header states
                headerStates.topbarVisible = topbarHeight > 0;
                headerStates.isSearchActive = searchBarHeight > 0;
                
                updateBodyClasses();
            }
            
            // Handle sticky header scroll behavior
            if ($header.hasClass('hph-sticky-header')) {
                var scrollThresholds = {
                    hideTopbar: 100,
                    compactHeader: 200
                };
                
                $(window).on('scroll', HPH.throttle(function() {
                    var scrollTop = $(window).scrollTop();
                    
                    // Update scroll state
                    var wasScrolled = headerStates.isScrolled;
                    headerStates.isScrolled = scrollTop > scrollThresholds.hideTopbar;
                    headerStates.isCompact = scrollTop > scrollThresholds.compactHeader;
                    
                    // Apply classes to header elements
                    $topbar.toggleClass('scrolled', headerStates.isScrolled);
                    $header.toggleClass('scrolled', headerStates.isScrolled);
                    $header.toggleClass('header-compact', headerStates.isCompact);
                    
                    // Update body classes and spacing if scroll state changed
                    if (wasScrolled !== headerStates.isScrolled) {
                        updateBodyClasses();
                    }
                }, 10));
            }
            
            // Monitor search bar state changes
            var searchObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        var wasSearchActive = headerStates.isSearchActive;
                        headerStates.isSearchActive = $searchBar.hasClass('active');
                        
                        if (wasSearchActive !== headerStates.isSearchActive) {
                            updateBodyClasses();
                        }
                    }
                });
            });
            
            if ($searchBar.length) {
                searchObserver.observe($searchBar[0], { attributes: true, attributeFilter: ['class'] });
            }
            
            // Update spacing on load and resize
            updateHeaderSpacing();
            $(window).on('resize', HPH.throttle(updateHeaderSpacing, 100));
            
            // Handle search toggle events
            $(document).on('click', '.hph-search-toggle', function(e) {
                e.preventDefault();
                setTimeout(updateHeaderSpacing, 50); // Allow animation to start
            });
            
            $(document).on('click', '.hph-search-close', function(e) {
                e.preventDefault();
                setTimeout(updateHeaderSpacing, 50); // Allow animation to start
            });
            
            // Initial state setup
            updateBodyClasses();
        },
        
        /**
         * Initialize breadcrumbs
         */
        initBreadcrumbs: function() {
            var $breadcrumbs = $('.hph-breadcrumbs');
            
            if ($breadcrumbs.length) {
                // Add structured data
                var breadcrumbData = {
                    "@context": "https://schema.org",
                    "@type": "BreadcrumbList",
                    "itemListElement": []
                };
                
                $breadcrumbs.find('a').each(function(index) {
                    breadcrumbData.itemListElement.push({
                        "@type": "ListItem",
                        "position": index + 1,
                        "name": $(this).text(),
                        "item": $(this).attr('href')
                    });
                });
                
                if (breadcrumbData.itemListElement.length > 0) {
                    $('head').append(
                        '<script type="application/ld+json">' +
                        JSON.stringify(breadcrumbData) +
                        '</script>'
                    );
                }
            }
        },
        
        /**
         * Handle navigation keyboard accessibility
         */
        initKeyboardNavigation: function() {
            var $menuItems = $('.nav-menu a');
            
            $menuItems.on('keydown', function(e) {
                var $current = $(this);
                var $parent = $current.closest('.menu-item');
                var $submenu = $parent.children('.sub-menu');
                
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if ($submenu.length) {
                            $submenu.find('a:first').focus();
                        } else {
                            var $next = $parent.next().find('a:first');
                            if ($next.length) $next.focus();
                        }
                        break;
                        
                    case 'ArrowUp':
                        e.preventDefault();
                        var $prev = $parent.prev().find('a:first');
                        if ($prev.length) $prev.focus();
                        break;
                        
                    case 'ArrowRight':
                        e.preventDefault();
                        if ($submenu.length) {
                            $parent.addClass('dropdown-open');
                            $submenu.find('a:first').focus();
                        }
                        break;
                        
                    case 'ArrowLeft':
                        e.preventDefault();
                        var $parentItem = $parent.closest('.sub-menu').parent();
                        if ($parentItem.length) {
                            $parentItem.removeClass('dropdown-open');
                            $parentItem.children('a').focus();
                        }
                        break;
                        
                    case 'Escape':
                        e.preventDefault();
                        $('.menu-item').removeClass('dropdown-open');
                        $current.blur();
                        break;
                }
            });
        }
    };
    
    // Initialize navigation when DOM is ready
    $(document).ready(function() {
        console.log('HPH Navigation: Starting initialization');
        HPH.Navigation.init();
        console.log('HPH Navigation: Initialization complete');
    });
    
})(jQuery);
