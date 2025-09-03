/**
 * Dashboard Main JavaScript
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';

    /**
     * Dashboard Controller
     */
    window.DashboardController = {
        
        /**
         * Initialize dashboard
         */
        init: function() {
            console.log('Dashboard initializing...');
            
            this.setupMobileMenu();
            this.setupNavigation();
            this.setupStats();
            this.setupEventListeners();
            
            console.log('Dashboard initialized successfully');
        },
        
        /**
         * Setup mobile menu functionality
         */
        setupMobileMenu: function() {
            const mobileToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.querySelector('.hph-dashboard-sidebar');
            const sidebarClose = document.getElementById('sidebarClose');
            
            if (mobileToggle && sidebar) {
                // Create overlay
                const overlay = document.createElement('div');
                overlay.className = 'hph-dashboard-overlay';
                document.body.appendChild(overlay);
                
                // Toggle menu
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.add('active');
                    overlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
                
                // Close menu
                function closeMenu() {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
                
                if (sidebarClose) {
                    sidebarClose.addEventListener('click', closeMenu);
                }
                
                overlay.addEventListener('click', closeMenu);
                
                // Close on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                        closeMenu();
                    }
                });
            }
        },
        
        /**
         * Setup navigation highlighting
         */
        setupNavigation: function() {
            const currentSection = new URLSearchParams(window.location.search).get('section') || 'overview';
            const navLinks = document.querySelectorAll('.hph-nav-link');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && href.includes('section=' + currentSection)) {
                    link.classList.add('active');
                } else if (currentSection === 'overview' && href && !href.includes('section=')) {
                    link.classList.add('active');
                }
            });
        },
        
        /**
         * Setup stats animations
         */
        setupStats: function() {
            const statValues = document.querySelectorAll('.hph-stat-value');
            
            statValues.forEach(stat => {
                const value = parseInt(stat.textContent);
                if (!isNaN(value)) {
                    this.animateValue(stat, 0, value, 1500);
                }
            });
        },
        
        /**
         * Animate number values
         */
        animateValue: function(element, start, end, duration) {
            const range = end - start;
            const increment = range / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= end) {
                    current = end;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 16);
        },
        
        /**
         * Setup global event listeners
         */
        setupEventListeners: function() {
            // Handle AJAX errors
            $(document).ajaxError(function(event, xhr, settings, error) {
                console.error('AJAX Error:', error);
                // Could show user-friendly error message
            });
            
            // Handle form submissions
            $('.hph-ajax-form').on('submit', function(e) {
                e.preventDefault();
                // Handle AJAX form submissions
            });
        },
        
        /**
         * Show loading state
         */
        showLoading: function(container) {
            const loadingHtml = `
                <div class="hph-loading">
                    <div class="hph-spinner"></div>
                    <span style="margin-left: 0.5rem;">Loading...</span>
                </div>
            `;
            $(container).html(loadingHtml);
        },
        
        /**
         * Show error state
         */
        showError: function(container, message = 'Something went wrong') {
            const errorHtml = `
                <div class="hph-error">
                    <div class="hph-error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="hph-error-title">Error</h3>
                    <p class="hph-error-message">${message}</p>
                </div>
            `;
            $(container).html(errorHtml);
        },
        
        /**
         * Utility: Get URL parameter
         */
        getUrlParameter: function(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        },
        
        /**
         * Utility: Update URL without reload
         */
        updateUrl: function(params) {
            const url = new URL(window.location);
            Object.keys(params).forEach(key => {
                if (params[key]) {
                    url.searchParams.set(key, params[key]);
                } else {
                    url.searchParams.delete(key);
                }
            });
            window.history.replaceState({}, '', url);
        }
    };
    
    /**
     * Dashboard Stats Widget
     */
    window.DashboardStats = {
        
        /**
         * Refresh stats via AJAX
         */
        refresh: function() {
            if (typeof hph_ajax === 'undefined') {
                console.warn('AJAX object not available');
                return;
            }
            
            $.ajax({
                url: hph_ajax.url,
                type: 'POST',
                data: {
                    action: 'hph_get_dashboard_stats',
                    nonce: hph_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update stats display
                        console.log('Stats updated:', response.data);
                    }
                },
                error: function() {
                    console.error('Failed to refresh stats');
                }
            });
        }
    };
    
    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        DashboardController.init();
    });

})(jQuery);
