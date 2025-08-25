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
        },
        
        /**
         * Initialize mobile menu
         */
        initMobileMenu: function() {
            var $menuToggle = $('.navbar-toggle');
            var $mobileMenu = $('.mobile-menu');
            var $body = $('body');
            
            // Create mobile menu if it doesn't exist
            if (!$mobileMenu.length) {
                this.createMobileMenu();
                $mobileMenu = $('.mobile-menu');
            }
            
            // Menu toggle click
            $menuToggle.on('click', function(e) {
                e.preventDefault();
                
                var isOpen = $body.hasClass('mobile-menu-open');
                
                if (isOpen) {
                    HPH.Navigation.closeMobileMenu();
                } else {
                    HPH.Navigation.openMobileMenu();
                }
            });
            
            // Close on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.mobile-menu, .navbar-toggle').length) {
                    HPH.Navigation.closeMobileMenu();
                }
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
        },
        
        /**
         * Create mobile menu
         */
        createMobileMenu: function() {
            var $primaryMenu = $('.nav-menu').clone();
            var $mobileMenuHtml = `
                <div class="mobile-menu">
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
                <div class="mobile-menu-overlay"></div>
            `;
            
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
            $('body').addClass('mobile-menu-open');
            $('.navbar-toggle').addClass('menu-open');
            
            // Focus management
            setTimeout(function() {
                $('.mobile-menu .nav-menu a:first').focus();
            }, 300);
        },
        
        /**
         * Close mobile menu
         */
        closeMobileMenu: function() {
            $('body').removeClass('mobile-menu-open');
            $('.navbar-toggle').removeClass('menu-open');
            $('.menu-item-has-children').removeClass('submenu-open');
        },
        
        /**
         * Initialize dropdown menus
         */
        initDropdowns: function() {
            var $menuItems = $('.nav-menu .menu-item-has-children');
            
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
            var $searchToggle = $('.search-toggle');
            var $searchForm = $('.header-search-form');
            
            if ($searchToggle.length && $searchForm.length) {
                $searchToggle.on('click', function(e) {
                    e.preventDefault();
                    $searchForm.toggleClass('search-form-open');
                    
                    if ($searchForm.hasClass('search-form-open')) {
                        $searchForm.find('input[type="search"]').focus();
                    }
                });
                
                // Close on outside click
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.header-search-form, .search-toggle').length) {
                        $searchForm.removeClass('search-form-open');
                    }
                });
                
                // Close on escape
                $(document).on('keydown', function(e) {
                    if (e.key === 'Escape') {
                        $searchForm.removeClass('search-form-open');
                    }
                });
            }
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
        HPH.Navigation.init();
    });
    
})(jQuery);
