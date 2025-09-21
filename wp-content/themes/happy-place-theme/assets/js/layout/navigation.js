/**
 * Header Navigation JavaScript
 * 
 * Simple, clean JavaScript for header functionality
 * Handles dropdowns, mobile menu, and search toggle
 */

(function() {
    'use strict';
    
    // Wait for DOM to load
    document.addEventListener('DOMContentLoaded', function() {
        
        // Initialize all header functionality
        initMobileMenu();
        initSearchToggle();
        initStickyHeader();
        
        console.log('Header navigation initialized');
    });
    
    
    /**
     * Initialize mobile menu
     */
    function initMobileMenu() {
        const mobileToggle = document.querySelector('.hph-mobile-toggle');
        const mobileMenu = document.querySelector('.hph-mobile-menu');
        const mobileClose = document.querySelector('.hph-mobile-close');
        const mobileOverlay = document.querySelector('.hph-mobile-overlay');
        
        if (!mobileToggle || !mobileMenu) return;
        
        // Toggle mobile menu
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const isExpanded = mobileToggle.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
        
        // Close button
        if (mobileClose) {
            mobileClose.addEventListener('click', function(e) {
                e.preventDefault();
                closeMobileMenu();
            });
        }
        
        // Overlay click
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', function(e) {
                e.preventDefault();
                closeMobileMenu();
            });
        }
        
        // Close on window resize (desktop)
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 769) {
                closeMobileMenu();
            }
        });
        
        function openMobileMenu() {
            document.body.classList.add('hph-mobile-menu-open');
            mobileMenu.classList.add('active');
            if (mobileOverlay) mobileOverlay.classList.add('active');
            mobileToggle.setAttribute('aria-expanded', 'true');
            mobileToggle.classList.add('active');
        }
        
        function closeMobileMenu() {
            document.body.classList.remove('hph-mobile-menu-open');
            mobileMenu.classList.remove('active');
            if (mobileOverlay) mobileOverlay.classList.remove('active');
            mobileToggle.setAttribute('aria-expanded', 'false');
            mobileToggle.classList.remove('active');
        }
    }
    
    /**
     * Initialize search toggle
     */
    function initSearchToggle() {
        const searchToggle = document.querySelector('.hph-search-toggle');
        const searchBar = document.querySelector('.hph-search-bar');
        const searchClose = document.querySelector('.hph-search-close');
        const searchInput = document.querySelector('.hph-search-input');
        
        if (!searchToggle || !searchBar) return;
        
        // Toggle search bar
        searchToggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (searchBar.classList.contains('active')) {
                closeSearch();
            } else {
                openSearch();
            }
        });
        
        // Close button
        if (searchClose) {
            searchClose.addEventListener('click', function(e) {
                e.preventDefault();
                closeSearch();
            });
        }
        
        // Close on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchBar.classList.contains('active')) {
                closeSearch();
            }
        });
        
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!searchBar.contains(e.target) && !searchToggle.contains(e.target)) {
                closeSearch();
            }
        });
        
        function openSearch() {
            searchBar.classList.add('active');
            document.body.classList.add('hph-search-active');
            
            // Focus the search input
            if (searchInput) {
                setTimeout(function() {
                    searchInput.focus();
                }, 100);
            }
        }
        
        function closeSearch() {
            searchBar.classList.remove('active');
            document.body.classList.remove('hph-search-active');
        }
    }
    
    /**
     * Initialize sticky header with optimized scroll handling
     */
    function initStickyHeader() {
        const header = document.querySelector('.hph-header');
        const topbar = document.querySelector('.hph-topbar');
        const body = document.body;
        
        if (!header) return;
        
        let lastScrollY = window.scrollY;
        let isScrolled = false;
        let isCompact = false;
        let isMobile = window.innerWidth <= 768;
        
        // Optimized throttling with reduced frequency for mobile
        let ticking = false;
        const throttleDelay = isMobile ? 16 : 8; // Less frequent on mobile for better performance
        
        function handleScroll() {
            const scrollY = window.scrollY;
            
            // Adjust thresholds for mobile
            const scrollThreshold = isMobile ? 80 : 100;
            const compactThreshold = isMobile ? 120 : 200;
            
            // Scrolled past topbar
            if (scrollY > scrollThreshold && !isScrolled) {
                isScrolled = true;
                header.classList.add('scrolled');
                if (topbar && !isMobile) topbar.classList.add('scrolled'); // Only hide topbar on desktop
                body.classList.add('header-scrolled');
            } else if (scrollY <= scrollThreshold && isScrolled) {
                isScrolled = false;
                header.classList.remove('scrolled');
                if (topbar) topbar.classList.remove('scrolled');
                body.classList.remove('header-scrolled');
            }
            
            // Compact header when scrolled more - simplified for mobile
            if (!isMobile) {
                if (scrollY > compactThreshold && !isCompact) {
                    isCompact = true;
                    header.classList.add('compact');
                    body.classList.add('header-compact');
                } else if (scrollY <= compactThreshold && isCompact) {
                    isCompact = false;
                    header.classList.remove('compact');
                    body.classList.remove('header-compact');
                }
            }
            
            lastScrollY = scrollY;
        }
        
        function requestTick() {
            if (!ticking) {
                setTimeout(() => {
                    handleScroll();
                    ticking = false;
                }, throttleDelay);
                ticking = true;
            }
        }
        
        // Handle window resize
        function handleResize() {
            isMobile = window.innerWidth <= 768;
            // Reset compact state on mobile
            if (isMobile && isCompact) {
                isCompact = false;
                header.classList.remove('compact');
                body.classList.remove('header-compact');
            }
        }
        
        // Listen for scroll events with passive listener
        window.addEventListener('scroll', requestTick, { passive: true });
        window.addEventListener('resize', handleResize, { passive: true });
        
        // Initial check
        handleScroll();
    }
    
})();
