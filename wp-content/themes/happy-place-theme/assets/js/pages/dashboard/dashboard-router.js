/**
 * Dashboard Router & Page Loader
 * Handles SPA-like navigation between dashboard sections
 * File Location: /wp-content/themes/happy-place/assets/js/dashboard/dashboard-router.js
 */

(function($) {
    'use strict';

    // Dashboard Router
    const DashboardRouter = {
        
        // Current page state
        currentPage: 'dashboard',
        pageCache: {},
        isLoading: false,
        
        // Page configurations
        pages: {
            'dashboard': {
                title: 'Dashboard',
                template: 'dashboard-home',
                script: null,
                styles: null
            },
            'listings': {
                title: 'My Listings',
                template: 'dashboard-listings',
                script: 'dashboard-listings',
                styles: 'dashboard-listings'
            },
            'open-houses': {
                title: 'Open Houses',
                template: 'dashboard-open-houses',
                script: 'dashboard-open-houses',
                styles: 'dashboard-open-houses'
            },
            'leads': {
                title: 'Lead Management',
                template: 'dashboard-leads',
                script: 'dashboard-leads',
                styles: 'dashboard-leads'
            },
            'transactions': {
                title: 'Transactions',
                template: 'dashboard-transactions',
                script: 'dashboard-transactions',
                styles: 'dashboard-transactions'
            },
            'profile': {
                title: 'My Profile',
                template: 'dashboard-profile',
                script: 'dashboard-profile',
                styles: 'dashboard-profile'
            },
            'marketing': {
                title: 'Marketing Suite',
                template: 'dashboard-marketing',
                script: 'dashboard-marketing',
                styles: 'dashboard-marketing'
            },
            'cma': {
                title: 'CMA Generator',
                template: 'dashboard-cma',
                script: 'dashboard-cma',
                styles: 'dashboard-cma'
            },
            'analytics': {
                title: 'Analytics',
                template: 'dashboard-analytics',
                script: 'dashboard-analytics',
                styles: 'dashboard-analytics'
            },
            'resources': {
                title: 'Resources',
                template: 'dashboard-resources',
                script: null,
                styles: null
            },
            'settings': {
                title: 'Settings',
                template: 'dashboard-settings',
                script: 'dashboard-settings',
                styles: 'dashboard-settings'
            }
        },
        
        // Initialize router
        init: function() {
            console.log('DashboardRouter: Initializing...');
            this.bindEvents();
            this.setupHistory();
            this.checkInitialPage();
            console.log('DashboardRouter: Initialization complete');
        },
        
        // Bind navigation events (DISABLED - using simple navigation)
        bindEvents: function() {
            // DISABLED: Navigation links - let them work normally
            // $(document).on('click', '.nav-link', this.handleNavigation.bind(this));
            
            // DISABLED: Browser back/forward buttons  
            // window.addEventListener('popstate', this.handlePopState.bind(this));
            
            // DISABLED: Hash change events
            // window.addEventListener('hashchange', this.handleHashChange.bind(this));
            
            // Keep quick action navigations for programmatic navigation
            $(document).on('dashboard:navigate', this.handleCustomNavigation.bind(this));
        },
        
        // Setup browser history
        setupHistory: function() {
            // Check if browser supports history API
            if (!window.history || !window.history.pushState) {
                console.warn('Browser does not support History API');
                return;
            }
        },
        
        // Check initial page from URL (DISABLED - using simple navigation)
        checkInitialPage: function() {
            console.log('DashboardRouter: Initial page check disabled - using simple navigation');
            // DISABLED: No longer loading pages via AJAX
            // Let the server-rendered page display normally
        },
        
        // Get page from URL hash
        getPageFromHash: function() {
            const hash = window.location.hash.replace('#', '');
            return hash && this.pages[hash] ? hash : null;
        },
        
        // Handle navigation click
        handleNavigation: function(e) {
            e.preventDefault();
            console.log('DashboardRouter: Navigation clicked');
            
            const $link = $(e.currentTarget);
            const page = $link.data('page');
            
            console.log('DashboardRouter: Clicked page =', page);
            
            if (!page || page === this.currentPage) {
                console.log('DashboardRouter: No page or same page, ignoring');
                return;
            }
            
            console.log('DashboardRouter: Navigating to:', page);
            this.navigateTo(page);
        },
        
        // Navigate to page
        navigateTo: function(page, addToHistory = true) {
            if (!this.pages[page]) {
                console.error('Page not found:', page);
                return;
            }
            
            // Check if already loading
            if (this.isLoading) {
                return;
            }
            
            // Update navigation state
            this.updateNavigation(page);
            
            // Load the page
            this.loadPage(page, addToHistory);
        },
        
        // Load page content
        loadPage: function(page, addToHistory = true) {
            const pageConfig = this.pages[page];
            
            // Set loading state
            this.isLoading = true;
            this.showLoading();
            
            // Check cache first
            if (this.pageCache[page] && !this.shouldRefresh(page)) {
                this.renderPage(page, this.pageCache[page]);
                this.isLoading = false;
                
                if (addToHistory) {
                    this.updateHistory(page);
                }
                return;
            }
            
            // Load page via AJAX
            console.log('DashboardRouter: Making AJAX request for page:', page);
            console.log('DashboardRouter: hphDashboardSettings =', hphDashboardSettings);
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_load_dashboard_page',
                    nonce: hphDashboardSettings.dashboard_nonce,
                    page: page,
                    template: pageConfig.template
                },
                success: (response) => {
                    console.log('DashboardRouter: AJAX response:', response);
                    if (response.success) {
                        // Cache the content
                        this.pageCache[page] = response.data;
                        
                        // Render the page
                        this.renderPage(page, response.data);
                        
                        // Update history
                        if (addToHistory) {
                            this.updateHistory(page);
                        }
                    } else {
                        console.error('DashboardRouter: AJAX failed:', response.data);
                        this.showError('Failed to load page: ' + (response.data || 'Unknown error'));
                    }
                },
                error: (xhr, status, error) => {
                    console.error('DashboardRouter: AJAX error:', xhr, status, error);
                    this.showError('Network error. Please try again.');
                },
                complete: () => {
                    this.isLoading = false;
                    this.hideLoading();
                }
            });
        },
        
        // Render page content
        renderPage: function(page, data) {
            const pageConfig = this.pages[page];
            
            // Update page title
            $('.dashboard-title').text(pageConfig.title);
            $('.dashboard-subtitle').text(data.subtitle || 'Welcome back!');
            
            // Update content
            $('#dashboardContent').html(data.html);
            
            // Load page-specific styles if needed
            if (pageConfig.styles && !this.isStyleLoaded(pageConfig.styles)) {
                this.loadStyles(pageConfig.styles);
            }
            
            // Load page-specific scripts if needed
            if (pageConfig.script && !this.isScriptLoaded(pageConfig.script)) {
                this.loadScript(pageConfig.script);
            } else if (pageConfig.script) {
                // Re-initialize if script already loaded
                this.initializePageScript(page);
            }
            
            // Update current page
            this.currentPage = page;
            
            // Trigger page loaded event
            $(document).trigger('dashboard:page-loaded', [page]);
            
            // Scroll to top
            window.scrollTo(0, 0);
        },
        
        // Update navigation UI
        updateNavigation: function(page) {
            // Remove active class from all nav items
            $('.nav-item').removeClass('active');
            
            // Add active class to current nav item
            $(`.nav-link[data-page="${page}"]`).parent().addClass('active');
            
            // Close mobile sidebar if open
            if (window.innerWidth < 1024) {
                $('#dashboardSidebar').removeClass('active');
            }
        },
        
        // Update browser history
        updateHistory: function(page) {
            // For hash-based routing, just update the hash
            window.location.hash = page;
            
            // Update document title
            document.title = `${this.pages[page].title} - Happy Place Dashboard`;
        },
        
        // Handle browser back/forward
        handlePopState: function(e) {
            if (e.state && e.state.page) {
                this.loadPage(e.state.page, false);
            }
        },
        
        // Handle hash change events
        handleHashChange: function() {
            const page = this.getPageFromHash();
            if (page && page !== this.currentPage) {
                this.loadPage(page, false);
                this.updateNavigation(page);
            }
        },
        
        // Handle custom navigation events
        handleCustomNavigation: function(e, page, params) {
            if (params) {
                // Store params for the page
                this.pageParams = params;
            }
            
            this.navigateTo(page);
        },
        
        // Navigate with parameters support
        navigateWithParams: function(page, params) {
            if (params) {
                this.pageParams = params;
            }
            this.navigateTo(page);
        },
        
        // Check if page should refresh
        shouldRefresh: function(page) {
            // Define pages that should always refresh
            const alwaysRefresh = ['dashboard', 'analytics', 'leads'];
            
            if (alwaysRefresh.includes(page)) {
                return true;
            }
            
            // Check cache age (5 minutes)
            const cacheTime = this.pageCache[page]?.timestamp;
            if (cacheTime && Date.now() - cacheTime > 300000) {
                return true;
            }
            
            return false;
        },
        
        // Load page styles
        loadStyles: function(styleId) {
            if ($(`#${styleId}-css`).length) {
                return;
            }
            
            const link = document.createElement('link');
            link.id = `${styleId}-css`;
            link.rel = 'stylesheet';
            link.href = `${hphDashboardSettings.themeUrl}/assets/css/dashboard/${styleId}.css?ver=${hphDashboardSettings.version}`;
            document.head.appendChild(link);
        },
        
        // Load page script
        loadScript: function(scriptId) {
            if ($(`#${scriptId}-js`).length) {
                this.initializePageScript(this.currentPage);
                return;
            }
            
            const script = document.createElement('script');
            script.id = `${scriptId}-js`;
            script.src = `${hphDashboardSettings.themeUrl}/assets/js/dashboard/${scriptId}.js?ver=${hphDashboardSettings.version}`;
            script.onload = () => {
                this.initializePageScript(this.currentPage);
            };
            document.body.appendChild(script);
        },
        
        // Initialize page-specific script
        initializePageScript: function(page) {
            // Call page-specific initialization
            switch(page) {
                case 'listings':
                    if (window.ListingsController) {
                        window.ListingsController.init();
                    }
                    break;
                case 'leads':
                    if (window.LeadsController) {
                        window.LeadsController.init();
                    }
                    break;
                case 'analytics':
                    if (window.AnalyticsController) {
                        window.AnalyticsController.init();
                    }
                    break;
                case 'marketing':
                    if (window.MarketingController) {
                        window.MarketingController.init();
                    }
                    break;
                case 'cma':
                    if (window.CMAController) {
                        window.CMAController.init();
                    }
                    break;
                case 'profile':
                    if (window.ProfileController) {
                        window.ProfileController.init();
                    }
                    break;
                case 'transactions':
                    if (window.TransactionsController) {
                        window.TransactionsController.init();
                    }
                    break;
                case 'open-houses':
                    if (window.OpenHousesController) {
                        window.OpenHousesController.init();
                    }
                    break;
                case 'settings':
                    if (window.SettingsController) {
                        window.SettingsController.init();
                    }
                    break;
            }
        },
        
        // Check if style is loaded
        isStyleLoaded: function(styleId) {
            return $(`#${styleId}-css`).length > 0;
        },
        
        // Check if script is loaded
        isScriptLoaded: function(scriptId) {
            return $(`#${scriptId}-js`).length > 0;
        },
        
        // Show loading state
        showLoading: function() {
            $('#dashboardContent').html(`
                <div class="page-loading">
                    <div class="loading-spinner">
                        <svg class="spinner" width="48" height="48" viewBox="0 0 48 48">
                            <circle cx="24" cy="24" r="20" stroke="var(--hph-primary)" stroke-width="3" fill="none" stroke-dasharray="125.66" stroke-dashoffset="94.245" stroke-linecap="round">
                                <animateTransform attributeName="transform" type="rotate" from="0 24 24" to="360 24 24" dur="1s" repeatCount="indefinite"/>
                            </circle>
                        </svg>
                    </div>
                    <p class="loading-text">Loading...</p>
                </div>
            `);
        },
        
        // Hide loading state
        hideLoading: function() {
            // Loading is replaced by content
        },
        
        // Show error message
        showError: function(message) {
            $('#dashboardContent').html(`
                <div class="error-state">
                    <div class="error-icon">
                        <svg width="48" height="48" viewBox="0 0 48 48" fill="currentColor">
                            <path d="M24 4C12.96 4 4 12.96 4 24s8.96 20 20 20 20-8.96 20-20S35.04 4 24 4zm2 30h-4v-4h4v4zm0-8h-4V14h4v12z"/>
                        </svg>
                    </div>
                    <h3 class="error-title">Oops! Something went wrong</h3>
                    <p class="error-message">${message}</p>
                    <button class="btn btn-primary" onclick="location.reload()">Reload Page</button>
                </div>
            `);
        },
        
        // Public API
        api: {
            // Navigate programmatically
            goTo: function(page, params) {
                if (params) {
                    DashboardRouter.navigateWithParams(page, params);
                } else {
                    DashboardRouter.navigateTo(page);
                }
            },
            
            // Get current page
            getCurrentPage: function() {
                return DashboardRouter.currentPage;
            },
            
            // Refresh current page
            refresh: function() {
                delete DashboardRouter.pageCache[DashboardRouter.currentPage];
                DashboardRouter.loadPage(DashboardRouter.currentPage, false);
            },
            
            // Clear cache
            clearCache: function(page) {
                if (page) {
                    delete DashboardRouter.pageCache[page];
                } else {
                    DashboardRouter.pageCache = {};
                }
            }
        }
    };
    
    // Initialize router
    $(document).ready(function() {
        DashboardRouter.init();
    });
    
    // Export API
    window.DashboardRouter = DashboardRouter.api;
    
})(jQuery);