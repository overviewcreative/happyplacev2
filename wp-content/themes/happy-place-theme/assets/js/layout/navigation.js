/**
 * Header Navigation JavaScript
 *
 * Unified system integration for header functionality
 * Handles dropdowns, mobile menu, and search toggle
 */

if (window.HPH) {
    HPH.register('headerNavigation', function() {
        return {
            init: function() {
                this.initMobileMenu();
                this.initSearchToggle();
                this.initStickyHeader();
            },

            /**
             * Initialize mobile menu
             */
            initMobileMenu: function() {
        const mobileToggle = document.querySelector('.hph-header-actions__btn--menu, .hph-site-header-enhanced__mobile-toggle, [data-mobile-toggle]');
        const mobileMenu = document.querySelector('.hph-site-header-enhanced__mobile-menu, [data-mobile-menu]');
        const mobileClose = document.querySelector('.hph-header-actions__btn--close, .hph-mobile-close');
        const mobileOverlay = document.querySelector('.hph-site-header-enhanced__mobile-overlay, [data-mobile-overlay]');

        if (!mobileToggle || !mobileMenu) return;

        // Store references for use in openMobileMenu/closeMobileMenu methods
        this.mobileToggle = mobileToggle;
        this.mobileMenu = mobileMenu;
        this.mobileClose = mobileClose;
        this.mobileOverlay = mobileOverlay;

                // Toggle mobile menu
                HPH.events.on(mobileToggle, 'click', (e) => {
                    e.preventDefault();

                    const isExpanded = mobileToggle.getAttribute('aria-expanded') === 'true';

                    if (isExpanded) {
                        this.closeMobileMenu();
                    } else {
                        this.openMobileMenu();
                    }
                });

                // Close button
                if (mobileClose) {
                    HPH.events.on(mobileClose, 'click', (e) => {
                        e.preventDefault();
                        this.closeMobileMenu();
                    });
                }

                // Overlay click
                if (mobileOverlay) {
                    HPH.events.on(mobileOverlay, 'click', (e) => {
                        e.preventDefault();
                        this.closeMobileMenu();
                    });
                }

                // Close on window resize (desktop)
                HPH.events.on(window, 'resize', () => {
                    if (window.innerWidth >= 769) {
                        this.closeMobileMenu();
                    }
                });
            },

            openMobileMenu: function() {
            document.body.classList.add('hph-mobile-menu-open');
            this.mobileMenu.classList.add('active', 'is-active');
            if (this.mobileOverlay) this.mobileOverlay.classList.add('active', 'is-active');
            this.mobileToggle.setAttribute('aria-expanded', 'true');
            this.mobileToggle.classList.add('active', 'is-active');

            // Add BEM state modifier classes
            const headerActions = document.querySelector('.hph-header-actions');
            if (headerActions) {
                headerActions.classList.add('hph-header-actions--menu-active');
            }
            if (this.mobileToggle.classList.contains('hph-header-actions__btn--menu')) {
                this.mobileToggle.classList.add('hph-header-actions__btn--active');
            }

            // Add specific class for enhanced header (legacy support)
            if (this.mobileToggle.classList.contains('hph-site-header-enhanced__mobile-toggle')) {
                this.mobileToggle.classList.add('is-active');
            }
            },

            closeMobileMenu: function() {
            document.body.classList.remove('hph-mobile-menu-open');
            this.mobileMenu.classList.remove('active', 'is-active');
            if (this.mobileOverlay) this.mobileOverlay.classList.remove('active', 'is-active');
            this.mobileToggle.setAttribute('aria-expanded', 'false');
            this.mobileToggle.classList.remove('active', 'is-active');

            // Remove BEM state modifier classes
            const headerActions = document.querySelector('.hph-header-actions');
            if (headerActions) {
                headerActions.classList.remove('hph-header-actions--menu-active');
            }
            if (this.mobileToggle.classList.contains('hph-header-actions__btn--menu')) {
                this.mobileToggle.classList.remove('hph-header-actions__btn--active');
            }
            },

            /**
             * Initialize search toggle
             */
            initSearchToggle: function() {
                const searchToggle = document.querySelector('.hph-header-actions__btn--search, .hph-site-header-enhanced__search-toggle, [data-search-toggle]');
                const searchBar = document.querySelector('.hph-site-header-enhanced__search-bar, [data-search-bar]');
                const searchClose = document.querySelector('.hph-header-actions__btn--clear, .hph-search-close');
                const searchInput = document.querySelector('#header-search-input, .hph-site-header-enhanced__search-input, .hph-search-input');

                if (!searchToggle || !searchBar) {
                    console.warn('Search elements not found:', { searchToggle, searchBar });
                    return;
                }

                // Store references for use in openSearch/closeSearch methods
                this.searchBar = searchBar;
                this.searchToggle = searchToggle;
                this.searchInput = searchInput;

                // Toggle search bar
                HPH.events.on(searchToggle, 'click', (e) => {
                    e.preventDefault();

                    if (searchBar.classList.contains('is-active')) {
                        this.closeSearch();
                    } else {
                        this.openSearch();
                    }
                });

                // Close button
                if (searchClose) {
                    HPH.events.on(searchClose, 'click', (e) => {
                        e.preventDefault();
                        this.closeSearch();
                    });
                }

                // Close on escape
                HPH.events.on(document, 'keydown', (e) => {
                    if (e.key === 'Escape' && searchBar.classList.contains('is-active')) {
                        this.closeSearch();
                    }
                });

                // Close on outside click
                HPH.events.on(document, 'click', (e) => {
                    if (!searchBar.contains(e.target) && !searchToggle.contains(e.target)) {
                        this.closeSearch();
                    }
                });
            },

            openSearch: function() {
            console.log('openSearch called - searchBar:', this.searchBar, 'searchToggle:', this.searchToggle);
            this.searchBar.classList.add('is-active');
            this.searchToggle.classList.add('is-active');
            // Add BEM state modifier classes
            const headerActions = document.querySelector('.hph-header-actions');
            if (headerActions) {
                headerActions.classList.add('hph-header-actions--search-active');
            }
            document.body.classList.add('hph-search-active');

            // Focus the search input
            if (this.searchInput) {
                setTimeout(() => {
                    this.searchInput.focus();
                }, 100);
            }
            },

            closeSearch: function() {
            this.searchBar.classList.remove('is-active');
            this.searchToggle.classList.remove('is-active');
            // Remove BEM state modifier classes
            const headerActions = document.querySelector('.hph-header-actions');
            if (headerActions) {
                headerActions.classList.remove('hph-header-actions--search-active');
            }
            document.body.classList.remove('hph-search-active');
            },

            /**
             * Initialize sticky header with optimized scroll handling
             */
            initStickyHeader: function() {
        const header = document.querySelector('.hph-site-header-enhanced, .hph-header');
        const topbar = document.querySelector('.hph-site-header-enhanced__topbar, .hph-topbar');
        const body = document.body;
        
        if (!header) return;
        
        let lastScrollY = window.scrollY;
        let isScrolled = false;
        let isCompact = false;
        let isMobile = window.innerWidth <= 768;
        
        // Optimized throttling with reduced frequency for mobile
        let ticking = false;
        const throttleDelay = isMobile ? 16 : 8; // Less frequent on mobile for better performance

                const handleScroll = () => {
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
                };

                const requestTick = () => {
                    if (!ticking) {
                        setTimeout(() => {
                            handleScroll();
                            ticking = false;
                        }, throttleDelay);
                        ticking = true;
                    }
                };

                // Handle window resize
                const handleResize = () => {
                    isMobile = window.innerWidth <= 768;
                    // Reset compact state on mobile
                    if (isMobile && isCompact) {
                        isCompact = false;
                        header.classList.remove('compact');
                        body.classList.remove('header-compact');
                    }
                };

                // Listen for scroll events with passive listener
                HPH.events.on(window, 'scroll', requestTick, { passive: true });
                HPH.events.on(window, 'resize', handleResize, { passive: true });

                // Initial check
                handleScroll();
            }
        };
    });
}
