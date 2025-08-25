/**
 * Dashboard Main JavaScript
 * File Location: /wp-content/themes/happy-place/assets/js/dashboard/dashboard-main.js
 */

(function($) {
    'use strict';

    // Dashboard Controller
    const DashboardController = {
        
        // Initialize
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.initializeComponents();
            this.loadDashboardData();
        },

        // Cache DOM elements
        cacheDom: function() {
            this.$wrapper = $('#dashboardWrapper');
            this.$sidebar = $('#dashboardSidebar');
            this.$mobileToggle = $('#mobileMenuToggle');
            this.$sidebarClose = $('#sidebarClose');
            this.$navLinks = $('.nav-link');
            this.$content = $('#dashboardContent');
            this.$quickAddBtn = $('#quickAddBtn');
            this.$quickAddModal = $('#quickAddModal');
            this.$modalClose = $('.modal-close');
            this.$quickAddOptions = $('.quick-add-option');
            this.$searchInput = $('.search-input');
            this.$actionCards = $('.action-card');
        },

        // Bind events
        bindEvents: function() {
            // Mobile menu toggle
            this.$mobileToggle.on('click', this.toggleMobileSidebar.bind(this));
            this.$sidebarClose.on('click', this.closeMobileSidebar.bind(this));
            
            // Navigation
            this.$navLinks.on('click', this.handleNavigation.bind(this));
            
            // Quick add modal
            this.$quickAddBtn.on('click', this.openQuickAddModal.bind(this));
            this.$modalClose.on('click', this.closeModal.bind(this));
            this.$quickAddOptions.on('click', this.handleQuickAdd.bind(this));
            
            // Quick actions
            this.$actionCards.on('click', this.handleQuickAction.bind(this));
            
            // Search
            this.$searchInput.on('keyup', this.debounce(this.handleSearch.bind(this), 300));
            
            // Close mobile sidebar on outside click
            $(document).on('click', this.handleOutsideClick.bind(this));
            
            // Handle window resize
            $(window).on('resize', this.handleResize.bind(this));
        },

        // Initialize components
        initializeComponents: function() {
            // Initialize tooltips
            this.initTooltips();
            
            // Initialize charts
            this.initCharts();
            
            // Initialize data tables
            this.initDataTables();
            
            // Initialize date pickers
            this.initDatePickers();
            
            // Initialize notifications
            this.initNotifications();
        },

        // Toggle mobile sidebar
        toggleMobileSidebar: function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.$sidebar.toggleClass('active');
        },

        // Close mobile sidebar
        closeMobileSidebar: function(e) {
            e.preventDefault();
            this.$sidebar.removeClass('active');
        },

        // Handle navigation
        handleNavigation: function(e) {
            // Let the router handle navigation
            // Router is initialized separately and handles all navigation
            return;
        },

        // Load page content (deprecated - now handled by router)
        loadPageContent: function(page) {
            // This is now handled by the DashboardRouter
            if (window.DashboardRouter) {
                window.DashboardRouter.goTo(page);
            }
        },

        // Get page title
        getPageTitle: function(page) {
            const titles = {
                'dashboard': 'Dashboard',
                'listings': 'My Listings',
                'open-houses': 'Open Houses',
                'leads': 'Lead Management',
                'transactions': 'Transactions',
                'profile': 'My Profile',
                'marketing': 'Marketing Suite',
                'cma': 'CMA Generator',
                'analytics': 'Analytics',
                'resources': 'Resources',
                'settings': 'Settings'
            };
            return titles[page] || 'Dashboard';
        },

        // Initialize page specific scripts
        initPageSpecificScripts: function(page) {
            switch(page) {
                case 'listings':
                    this.initListingsPage();
                    break;
                case 'leads':
                    this.initLeadsPage();
                    break;
                case 'analytics':
                    this.initAnalyticsPage();
                    break;
                case 'marketing':
                    this.initMarketingPage();
                    break;
                default:
                    // Re-initialize general components
                    this.initCharts();
            }
        },

        // Open quick add modal
        openQuickAddModal: function(e) {
            e.preventDefault();
            this.$quickAddModal.addClass('active');
            $('body').addClass('modal-open');
        },

        // Close modal
        closeModal: function(e) {
            e.preventDefault();
            $('.modal').removeClass('active');
            $('body').removeClass('modal-open');
        },

        // Handle quick add
        handleQuickAdd: function(e) {
            e.preventDefault();
            const type = $(e.currentTarget).data('type');
            
            // Close modal
            this.closeModal(e);
            
            // Navigate to appropriate page
            switch(type) {
                case 'listing':
                    this.loadPageContent('listings');
                    // Open add listing form
                    setTimeout(() => {
                        this.openAddListingForm();
                    }, 500);
                    break;
                case 'lead':
                    this.loadPageContent('leads');
                    setTimeout(() => {
                        this.openAddLeadForm();
                    }, 500);
                    break;
                case 'open-house':
                    this.loadPageContent('open-houses');
                    setTimeout(() => {
                        this.openScheduleOpenHouse();
                    }, 500);
                    break;
                case 'transaction':
                    this.loadPageContent('transactions');
                    setTimeout(() => {
                        this.openAddTransaction();
                    }, 500);
                    break;
            }
        },

        // Handle quick action
        handleQuickAction: function(e) {
            e.preventDefault();
            const action = $(e.currentTarget).data('action');
            
            switch(action) {
                case 'add-listing':
                    this.openAddListingForm();
                    break;
                case 'schedule-open-house':
                    this.openScheduleOpenHouse();
                    break;
                case 'create-cma':
                    this.openCMAGenerator();
                    break;
                case 'import-leads':
                    this.openLeadImporter();
                    break;
                case 'create-marketing':
                    this.openMarketingSuite();
                    break;
                case 'export-data':
                    this.openDataExporter();
                    break;
            }
        },

        // Handle search
        handleSearch: function(e) {
            const query = $(e.target).val();
            
            if (query.length < 3) {
                return;
            }
            
            // Perform search
            this.performSearch(query);
        },

        // Perform search
        performSearch: function(query) {
            $.ajax({
                url: hphDashboardSettings.root + 'hph/v1/search',
                method: 'GET',
                data: {
                    query: query,
                    context: 'dashboard'
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', hphDashboardSettings.nonce);
                },
                success: function(response) {
                    // Display search results
                    console.log('Search results:', response);
                }
            });
        },

        // Handle outside click
        handleOutsideClick: function(e) {
            if (window.innerWidth < 1024) {
                if (!$(e.target).closest('#dashboardSidebar, #mobileMenuToggle').length) {
                    this.$sidebar.removeClass('active');
                }
            }
        },

        // Handle window resize
        handleResize: function() {
            if (window.innerWidth >= 1024) {
                this.$sidebar.removeClass('active');
            }
        },

        // Load dashboard data
        loadDashboardData: function() {
            // Load stats
            this.loadStats();
            
            // Load recent activity
            this.loadRecentActivity();
            
            // Load upcoming events
            this.loadUpcomingEvents();
            
            // Load hot leads
            this.loadHotLeads();
        },

        // Load stats
        loadStats: function() {
            $.ajax({
                url: hphDashboardSettings.root + 'hph/v1/dashboard/stats',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', hphDashboardSettings.nonce);
                },
                success: function(response) {
                    // Update stat cards
                    if (response.active_listings) {
                        $('.stat-card').eq(0).find('.stat-value').text(response.active_listings);
                    }
                    if (response.closed_this_month) {
                        $('.stat-card').eq(1).find('.stat-value').text(response.closed_this_month);
                    }
                    if (response.new_leads) {
                        $('.stat-card').eq(2).find('.stat-value').text(response.new_leads);
                    }
                    if (response.open_houses) {
                        $('.stat-card').eq(3).find('.stat-value').text(response.open_houses);
                    }
                }
            });
        },

        // Load recent activity
        loadRecentActivity: function() {
            $.ajax({
                url: hphDashboardSettings.root + 'hph/v1/dashboard/activity',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', hphDashboardSettings.nonce);
                },
                success: function(response) {
                    if (response.activities && response.activities.length) {
                        const html = response.activities.map(activity => {
                            return `
                                <div class="activity-item">
                                    <div class="activity-icon activity-icon-${activity.type}">
                                        ${activity.icon}
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-text">${activity.text}</p>
                                        <span class="activity-time">${activity.time}</span>
                                    </div>
                                </div>
                            `;
                        }).join('');
                        $('#activityFeed').html(html);
                    }
                }
            });
        },

        // Load upcoming events
        loadUpcomingEvents: function() {
            $.ajax({
                url: hphDashboardSettings.root + 'hph/v1/dashboard/events',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', hphDashboardSettings.nonce);
                },
                success: function(response) {
                    if (response.events && response.events.length) {
                        const html = response.events.map(event => {
                            return `
                                <div class="event-item">
                                    <div class="event-date">
                                        <span class="event-day">${event.day}</span>
                                        <span class="event-month">${event.month}</span>
                                    </div>
                                    <div class="event-details">
                                        <h4 class="event-title">${event.title}</h4>
                                        <p class="event-time">${event.time}</p>
                                    </div>
                                </div>
                            `;
                        }).join('');
                        $('#upcomingEvents').html(html);
                    }
                }
            });
        },

        // Load hot leads
        loadHotLeads: function() {
            // This would integrate with FollowUpBoss CRM
            $.ajax({
                url: hphDashboardSettings.root + 'hph/v1/dashboard/leads/hot',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', hphDashboardSettings.nonce);
                },
                success: function(response) {
                    if (response.leads && response.leads.length) {
                        const html = response.leads.map(lead => {
                            const initials = lead.name.split(' ').map(n => n[0]).join('');
                            return `
                                <div class="lead-item">
                                    <div class="lead-avatar">${initials}</div>
                                    <div class="lead-info">
                                        <h4 class="lead-name">${lead.name}</h4>
                                        <p class="lead-details">${lead.details}</p>
                                        <span class="lead-badge lead-badge-${lead.status}">${lead.status_label}</span>
                                    </div>
                                    <button class="btn btn-sm btn-outline" data-lead-id="${lead.id}">Contact</button>
                                </div>
                            `;
                        }).join('');
                        $('#hotLeads').html(html);
                    }
                }
            });
        },

        // Initialize charts
        initCharts: function() {
            const ctx = document.getElementById('salesChart');
            if (ctx) {
                // Chart.js configuration
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Sales',
                            data: [12, 19, 3, 5, 2, 3],
                            borderColor: 'rgb(81, 186, 224)',
                            backgroundColor: 'rgba(81, 186, 224, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        },

        // Initialize tooltips
        initTooltips: function() {
            // Initialize tooltip library if available
        },

        // Initialize data tables
        initDataTables: function() {
            // Initialize DataTables if available
        },

        // Initialize date pickers
        initDatePickers: function() {
            // Initialize date picker library if available
        },

        // Initialize notifications
        initNotifications: function() {
            // Set up notification system
        },

        // Form handlers
        openAddListingForm: function() {
            console.log('Opening add listing form');
            // Implementation for add listing form
        },

        openAddLeadForm: function() {
            console.log('Opening add lead form');
            // Implementation for add lead form
        },

        openScheduleOpenHouse: function() {
            console.log('Opening schedule open house');
            // Implementation for schedule open house
        },

        openAddTransaction: function() {
            console.log('Opening add transaction');
            // Implementation for add transaction
        },

        openCMAGenerator: function() {
            console.log('Opening CMA generator');
            // Implementation for CMA generator
        },

        openLeadImporter: function() {
            console.log('Opening lead importer');
            // Implementation for lead importer
        },

        openMarketingSuite: function() {
            console.log('Opening marketing suite');
            // Implementation for marketing suite
        },

        openDataExporter: function() {
            console.log('Opening data exporter');
            // Implementation for data exporter
        },

        // Page initializers
        initListingsPage: function() {
            console.log('Initializing listings page');
            // Implementation for listings page
        },

        initLeadsPage: function() {
            console.log('Initializing leads page');
            // Implementation for leads page with FollowUpBoss integration
        },

        initAnalyticsPage: function() {
            console.log('Initializing analytics page');
            // Implementation for analytics page with Google Analytics integration
        },

        initMarketingPage: function() {
            console.log('Initializing marketing page');
            // Implementation for marketing page
        },

        // Utility functions
        showError: function(message) {
            console.error(message);
            // Show error notification
        },

        showSuccess: function(message) {
            console.log(message);
            // Show success notification
        },

        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        DashboardController.init();
    });

})(jQuery);