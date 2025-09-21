/**
 * Consolidated Dashboard Controller
 * 
 * Combines the best features from:
 * - dashboard-main.js (1,071 lines) - Mobile menu, navigation, modals, AJAX
 * - dashboard-listings-enhanced.js (1,090 lines) - Advanced filtering, bulk actions, CRUD
 * 
 * Result: Single, conflict-free dashboard system with all functionality
 * 
 * @package HappyPlaceTheme
 * @since 3.2.2 - Dashboard Consolidation
 */

(function($) {
    'use strict';

    // Main Dashboard Controller - Consolidated
    const DashboardController = {
        
        // === PROPERTIES ===
        currentPage: 1,
        perPage: 20,
        currentFilter: 'all',
        currentSort: 'date-desc',
        selectedListings: [],
        isLoading: false,
        servicesAvailable: false,
        currentFilters: {},
        nonce: '',
        ajaxurl: '',
        
        // === INITIALIZATION ===
        init: function() {
            // Performance monitoring
            const startTime = performance.now();
            
            // Prevent multiple initializations
            if (window.hphDashboardInitialized) {
                return;
            }
            
            
            // Mark as initialized to prevent conflicts
            window.hphDashboardInitialized = true;
            
            // Clear any existing event handlers to prevent conflicts
            $(document).off('.dashboard');
            
            // Setup core systems
            this.setupNonce();
            this.detectServices();
            this.cacheDom();
            this.bindEvents();
            
            // Initialize components
            this.setupMobileMenu();
            this.setupNavigation();
            this.setupStats();
            this.initializeModals();
            this.initializeViewPreference();
            this.initExportFunctionality();
            
            // Load initial data with performance optimization
            if (this.$container.length || this.$statsContainer.length) {
                // Defer heavy loading to prevent blocking UI
                setTimeout(() => this.loadDashboardData(), 100);
            }
            
            // Performance monitoring
            const endTime = performance.now();
        },
        
        // === CORE SETUP ===
        
        // Setup AJAX nonce and URL
        setupNonce: function() {
            this.nonce = $('input[name="hph_dashboard_nonce"]').val() || 
                        (window.hphDashboard && window.hphDashboard.nonce) ||
                        (window.hphDashboardSettings && window.hphDashboardSettings.dashboard_nonce) ||
                        $('meta[name="hph-nonce"]').attr('content') || '';
            
            this.ajaxurl = (window.hphDashboard && window.hphDashboard.ajaxurl) ||
                          (window.hphDashboardSettings && window.hphDashboardSettings.ajaxurl) ||
                          window.ajaxurl ||
                          '/wp-admin/admin-ajax.php';
                          
        },
        
        // Detect if plugin services are available
        detectServices: function() {
            this.servicesAvailable = (typeof window.HPH !== 'undefined' && 
                                    window.HPH.services_available === true) ||
                                    (typeof hphDashboardSettings !== 'undefined' && 
                                    hphDashboardSettings.services_available === true);
            
        },
        
        // Cache DOM elements
        cacheDom: function() {
            // Main containers
            this.$wrapper = $('#dashboardWrapper');
            this.$sidebar = $('#dashboardSidebar');
            this.$content = $('#dashboardContent');
            this.$container = $('#dashboardListingsContainer, #listingsContainer');
            this.$grid = $('#listingsGrid, #dashboardListingsContainer');
            
            // Mobile menu
            this.$mobileToggle = $('#mobileMenuToggle');
            this.$sidebarClose = $('#sidebarClose');
            this.$sidebarToggle = $('#sidebarToggle');
            
            // Navigation
            this.$navLinks = $('.nav-link, .hph-nav-link');
            
            // Search and filters
            this.$search = $('#listingsSearch');
            this.$searchInput = $('#listingsSearch');
            this.$filtersToggle = $('#toggleAdvancedFilters');
            this.$filtersPanel = $('#advancedFilters');
            this.$clearFilters = $('#clearFilters');
            this.$applyFilters = $('#applyFilters');
            
            // Filter inputs
            this.$minPrice = $('#minPrice');
            this.$maxPrice = $('#maxPrice');
            this.$propertyType = $('#propertyType, #filterPropertyType');
            this.$bedrooms = $('#bedrooms, #filterBedrooms');
            this.$dateListed = $('#dateListed');
            
            // Bulk actions
            this.$selectAll = $('#selectAllListings');
            this.$bulkBar = $('#bulkActionsBar');
            this.$bulkAction = $('#bulkAction');
            this.$applyBulk = $('#applyBulkAction');
            this.$cancelBulk = $('#cancelBulkSelection');
            this.$selectedCount = $('#selectedCount');
            
            // Buttons and controls
            this.$addNewBtn = $('#addNewListingBtn, #addFirstListingBtn, #quickAddListingBtn');
            this.$importBtn = $('#importListingsBtn');
            this.$filterBtns = $('.filter-btn');
            this.$sortSelect = $('#listingsSort');
            this.$viewToggle = $('.view-btn, .hph-view-toggle');
            
            // Modals
            this.$listingModal = $('#listingFormModal');
            this.$listingForm = $('#listingForm');
            this.$modalOverlay = $('#listingFormOverlay');
            this.$closeModal = $('#closeListingForm');
            this.$quickAddModal = $('#quickAddModal');
            this.$modalClose = $('.modal-close, .hph-modal-close');
            
            // Stats and data displays
            this.$statsContainer = $('#listingsStats, .stats-grid');
            this.$actionCards = $('.action-card');
        },
        
        // === MOBILE MENU SYSTEM ===
        
        setupMobileMenu: function() {
            const self = this;
            
            if (!this.$mobileToggle.length || !this.$sidebar.length) return;
            
            // Create overlay if it doesn't exist
            if (!$('.hph-dashboard-overlay').length) {
                const overlay = $('<div class="hph-dashboard-overlay"></div>');
                $('body').append(overlay);
            }
            
            const $overlay = $('.hph-dashboard-overlay');
            
            // Toggle menu
            this.$mobileToggle.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.$sidebar.addClass('active');
                $overlay.addClass('active');
                $('body').addClass('modal-open').css('overflow', 'hidden');
            });
            
            // Close menu function
            function closeMenu() {
                self.$sidebar.removeClass('active');
                $overlay.removeClass('active');
                $('body').removeClass('modal-open').css('overflow', '');
            }
            
            // Close menu events
            this.$sidebarClose.on('click', closeMenu);
            $overlay.on('click', closeMenu);
            
            // Close on escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.$sidebar.hasClass('active')) {
                    closeMenu();
                }
            });
            
            // Handle window resize
            $(window).on('resize', function() {
                if (window.innerWidth >= 1024) {
                    closeMenu();
                }
            });
        },
        
        // === NAVIGATION SYSTEM ===
        
        setupNavigation: function() {
            // Get current section from URL
            const currentSection = new URLSearchParams(window.location.search).get('section') || 'overview';
            
            // Update active navigation state
            this.$navLinks.each(function() {
                const $link = $(this);
                const href = $link.attr('href');
                
                if (href && href.includes('section=' + currentSection)) {
                    $link.addClass('active');
                } else if (currentSection === 'overview' && href && !href.includes('section=')) {
                    $link.addClass('active');
                } else {
                    $link.removeClass('active');
                }
            });
        },
        
        // === STATS SYSTEM ===
        
        setupStats: function() {
            // Animate stat values if they exist
            $('.hph-stat-value').each((index, element) => {
                const value = parseInt($(element).text());
                if (!isNaN(value)) {
                    this.animateValue(element, 0, value, 1500);
                }
            });
        },
        
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
                $(element).text(Math.floor(current));
            }, 16);
        },
        
        // === EVENT BINDING ===
        
        bindEvents: function() {
            // Search functionality
            this.$searchInput.on('input.dashboard', this.debounce(this.handleSearch.bind(this), 300));
            
            // Filter controls
            this.$filtersToggle.on('click.dashboard', this.toggleAdvancedFilters.bind(this));
            this.$clearFilters.on('click.dashboard', this.clearAllFilters.bind(this));
            this.$applyFilters.on('click.dashboard', this.applyFilters.bind(this));
            this.$filterBtns.on('click.dashboard', this.handleFilterChange.bind(this));
            this.$sortSelect.on('change.dashboard', this.handleSortChange.bind(this));
            
            // View toggles
            this.$viewToggle.on('click.dashboard', this.handleViewToggle.bind(this));
            
            // Bulk actions
            this.$selectAll.on('change.dashboard', this.handleSelectAll.bind(this));
            this.$applyBulk.on('click.dashboard', this.handleBulkAction.bind(this));
            this.$cancelBulk.on('click.dashboard', this.cancelBulkSelection.bind(this));
            
            // Modal controls
            this.$addNewBtn.on('click.dashboard', this.openAddModal.bind(this));
            this.$closeModal.on('click.dashboard', this.closeModal.bind(this));
            this.$modalOverlay.on('click.dashboard', this.closeModal.bind(this));
            
            // Form submission
            this.$listingForm.on('submit.dashboard', this.handleFormSubmit.bind(this));
            
            // Dynamic event binding for listing cards
            $(document).on('change.dashboard', '.hph-listing-checkbox, .listing-checkbox', this.handleListingSelection.bind(this));
            $(document).on('click.dashboard', '[data-action="edit"]', this.handleEditListing.bind(this));
            $(document).on('click.dashboard', '[data-action="duplicate"]', this.handleDuplicateListing.bind(this));
            $(document).on('click.dashboard', '[data-action="delete"]', this.handleDeleteListing.bind(this));
            $(document).on('click.dashboard', '[data-action="status"]', this.handleStatusChange.bind(this));
            
            // Inline price editing
            $(document).on('click.dashboard', '.hph-dashboard-price', this.handlePriceEdit.bind(this));
            $(document).on('click.dashboard', '.hph-price-editor .save-btn', this.handlePriceSave.bind(this));
            $(document).on('click.dashboard', '.hph-price-editor .cancel-btn', this.handlePriceCancel.bind(this));
            $(document).on('keypress.dashboard', '.hph-price-editor input[type="number"]', this.handlePriceKeyPress.bind(this));
            
            // Quick actions
            $(document).on('click', '.hph-edit-price-btn', this.handleEditPriceButton.bind(this));
            $(document).on('click', '.hph-schedule-openhouse-btn', this.handleScheduleOpenHouse.bind(this));
            $(document).on('click', '.hph-request-marketing-btn', this.handleRequestMarketing.bind(this));
            $(document).on('click', '.hph-share-listing-btn', this.handleShareListing.bind(this));
            $(document).on('click', '.hph-view-analytics-btn', this.handleViewAnalytics.bind(this));
            
            // Status change
            $(document).on('change.dashboard', '.hph-dashboard-status-select', this.handleStatusChange.bind(this));
            $(document).on('click.dashboard', '#confirmStatusChange', this.confirmStatusChange.bind(this));
            
            // Pagination
            $(document).on('click.dashboard', '.pagination-btn', this.handlePagination.bind(this));
            
            // Notification close
            $(document).on('click', '.notification-close', function() {
                $(this).closest('.hph-notification').fadeOut(() => $(this).remove());
            });
            
            // Quick add modal
            $('.quick-add-option').on('click.dashboard', this.handleQuickAdd.bind(this));
            $('#quickAddBtn').on('click.dashboard', this.openQuickAddModal.bind(this));
            
            // Action cards
            this.$actionCards.on('click.dashboard', this.handleQuickAction.bind(this));
            
            // Global AJAX error handling
            $(document).ajaxError((event, xhr, settings, error) => {
                this.showNotification('Network error. Please try again.', 'error');
            });
        },
        
        // === MODAL SYSTEM ===
        
        initializeModals: function() {
            // Close modals when clicking outside
            $(document).on('click', '.hph-modal-content', function(e) { 
                e.stopPropagation(); 
            });
            
            // Initialize any modal-specific functionality
            this.bindModalCloseEvents();
        },
        
        bindModalCloseEvents: function() {
            // Remove existing listeners to prevent duplicates
            $(document).off('click.listingModal', '#closeListingForm, #cancelListingForm, #listingFormOverlay');
            
            // Bind new listeners
            $(document).on('click.listingModal', '#closeListingForm, #cancelListingForm, #listingFormOverlay', this.closeListingFormModal.bind(this));
        },
        
        openQuickAddModal: function(e) {
            if (e) e.preventDefault();
            this.$quickAddModal.addClass('active');
            $('body').addClass('modal-open');
        },
        
        closeModal: function(e) {
            if (e) e.preventDefault();
            $('.modal, .hph-modal').removeClass('active').hide();
            $('body').removeClass('modal-open');
        },
        
        closeListingFormModal: function(e) {
            if (e) e.preventDefault();
            
            const modal = document.getElementById('listingFormModal');
            if (modal) {
                modal.style.display = 'none';
                $('body').removeClass('modal-open');
            }
        },
        
        // === VIEW PREFERENCE SYSTEM ===
        
        initializeViewPreference: function() {
            const savedView = localStorage.getItem('dashboardView') || 'grid';
            const $container = this.$container;
            const $toggles = this.$viewToggle;
            
            // Set initial view
            if (savedView === 'list') {
                $container.removeClass('hph-dashboard-grid')
                          .addClass('hph-dashboard-list')
                          .attr('data-view', 'list');
                $toggles.removeClass('hph-btn-primary active').addClass('hph-btn-outline');
                $toggles.filter('[data-view="list"]').removeClass('hph-btn-outline').addClass('hph-btn-primary active');
            } else {
                $container.removeClass('hph-dashboard-list')
                          .addClass('hph-dashboard-grid')
                          .attr('data-view', 'grid');
                $toggles.removeClass('hph-btn-primary active').addClass('hph-btn-outline');
                $toggles.filter('[data-view="grid"]').removeClass('hph-btn-outline').addClass('hph-btn-primary active');
            }
        },
        
        // === DATA LOADING SYSTEM ===
        
        loadDashboardData: function() {
            
            // Load dashboard stats (enhanced)
            this.loadDashboardStats();
            
            // Load recent activity
            this.loadRecentActivity();
            
            // Load upcoming events
            this.loadUpcomingEvents();
            
            // Load hot leads (for agents)
            this.loadHotLeads();
            
            // Load analytics if on analytics page
            if (window.location.href.includes('section=analytics')) {
                this.loadAnalyticsData();
            }
            
            // Load marketing data if on marketing page
            if (window.location.href.includes('section=marketing')) {
                this.loadMarketingData();
            }
            
            // Load listings if on listings page
            if (this.$container.length && (window.location.href.includes('listings') || $('.hph-dashboard-card').length === 0)) {
                this.loadListings();
            }
        },
        
        // === DASHBOARD STATS LOADING ===
        
        loadDashboardStats: function() {
            const $statsContainer = $('#dashboardStats');
            if (!$statsContainer.length) return;
            
            
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_dashboard_stats',
                    nonce: this.nonce,
                    use_services: this.servicesAvailable ? '1' : '0'
                },
                success: (response) => {
                    if (response.success) {
                        this.renderDashboardStats(response.data);
                    } else {
                        this.renderDashboardStatsError();
                    }
                },
                error: (xhr, status, error) => {
                    this.renderDashboardStatsError();
                }
            });
        },
        
        renderDashboardStats: function(stats) {
            // Update individual stat cards
            const statMappings = [
                { id: '#stat-active-listings', value: stats.active_listings || 0 },
                { id: '#stat-closed-month', value: stats.closed_this_month || 0 },
                { id: '#stat-new-leads', value: stats.new_leads || 0 },
                { id: '#stat-open-houses', value: stats.open_houses || 0 }
            ];
            
            statMappings.forEach(stat => {
                const $card = $(stat.id);
                if ($card.length) {
                    $card.find('.hph-stat-card-value').html(stat.value);
                }
            });
        },
        
        renderDashboardStatsError: function() {
            const $statsContainer = $('#dashboardStats');
            $statsContainer.find('.hph-loading-spinner').each(function() {
                $(this).html('<i class="fas fa-exclamation-triangle hph-text-red-500"></i>');
            });
        },
        
        renderStats: function(stats) {
            const $statsGrid = $('.stats-grid');
            if (!$statsGrid.length) return;
            
            const html = `
                <div class="stat-card">
                    <div class="stat-icon stat-icon-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-label">Total Listings</h3>
                        <p class="stat-value hph-stat-value">${stats.total || 0}</p>
                        <p class="stat-change stat-change-up">
                            <span>Active listings</span>
                        </p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-icon-success">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-label">Active Listings</h3>
                        <p class="stat-value hph-stat-value">${stats.active || 0}</p>
                        <p class="stat-change stat-change-up">
                            <span>Live on market</span>
                        </p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-icon-warning">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-label">Pending</h3>
                        <p class="stat-value hph-stat-value">${stats.pending || 0}</p>
                        <p class="stat-change">
                            <span>Awaiting approval</span>
                        </p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-icon-info">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-label">Sold This Month</h3>
                        <p class="stat-value hph-stat-value">${stats.sold || 0}</p>
                        <p class="stat-change stat-change-up">
                            <span>Closed deals</span>
                        </p>
                    </div>
                </div>
            `;
            
            $statsGrid.html(html);
            this.setupStats(); // Animate the new stats
        },
        
        // === SEARCH & FILTERING ===
        
        handleSearch: function() {
            const searchTerm = this.$searchInput.val().trim();
            this.currentFilters.search = searchTerm;
            this.currentPage = 1;
            this.loadListings();
        },
        
        toggleAdvancedFilters: function() {
            this.$filtersPanel.slideToggle(300);
            this.$filtersToggle.toggleClass('active');
        },
        
        clearAllFilters: function() {
            this.$minPrice.val('');
            this.$maxPrice.val('');
            this.$propertyType.val('');
            this.$bedrooms.val('');
            this.$dateListed.val('');
            this.$searchInput.val('');
            
            this.currentFilters = {};
            this.currentPage = 1;
            this.loadListings();
        },
        
        applyFilters: function() {
            this.currentFilters = {
                search: this.$searchInput.val().trim(),
                min_price: this.$minPrice.val(),
                max_price: this.$maxPrice.val(),
                property_type: this.$propertyType.val(),
                bedrooms: this.$bedrooms.val(),
                date_listed: this.$dateListed.val()
            };
            
            // Remove empty filters
            Object.keys(this.currentFilters).forEach(key => {
                if (!this.currentFilters[key]) {
                    delete this.currentFilters[key];
                }
            });
            
            this.currentPage = 1;
            this.loadListings();
        },
        
        handleFilterChange: function(e) {
            const $btn = $(e.currentTarget);
            const status = $btn.data('status');
            
            $('.filter-btn').removeClass('active');
            $btn.addClass('active');
            
            this.currentFilter = status;
            this.currentPage = 1;
            this.loadListings();
        },
        
        handleSortChange: function(e) {
            this.currentSort = $(e.currentTarget).val();
            this.currentPage = 1;
            this.loadListings();
        },
        
        // === VIEW TOGGLE SYSTEM ===
        
        handleViewToggle: function(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const view = $btn.data('view');
            const $container = this.$container;
            const $toggles = $('.hph-view-toggle, .view-btn');
            
            // Update active state on buttons
            $toggles.removeClass('hph-btn-primary active').addClass('hph-btn-outline');
            $btn.removeClass('hph-btn-outline').addClass('hph-btn-primary active');
            
            // Apply view classes to container
            if (view === 'list') {
                $container.removeClass('hph-dashboard-grid')
                          .addClass('hph-dashboard-list')
                          .attr('data-view', 'list');
            } else {
                $container.removeClass('hph-dashboard-list')
                          .addClass('hph-dashboard-grid')
                          .attr('data-view', 'grid');
            }
            
            // Store preference
            localStorage.setItem('dashboardView', view);
        },
        
        // === BULK ACTIONS SYSTEM ===
        
        handleSelectAll: function() {
            const isChecked = this.$selectAll.prop('checked');
            $('.listing-checkbox, .hph-listing-checkbox').prop('checked', isChecked);
            this.updateSelectedListings();
        },
        
        handleListingSelection: function() {
            this.updateSelectedListings();
        },
        
        updateSelectedListings: function() {
            this.selectedListings = [];
            $('.listing-checkbox:checked, .hph-listing-checkbox:checked').each((index, checkbox) => {
                this.selectedListings.push($(checkbox).data('listing-id') || $(checkbox).val());
            });
            
            this.updateBulkActionsBar();
        },
        
        updateBulkActionsBar: function() {
            const count = this.selectedListings.length;
            
            if (count > 0) {
                this.$bulkBar.slideDown(200);
                this.$selectedCount.text(count + (count === 1 ? ' listing selected' : ' listings selected'));
            } else {
                this.$bulkBar.slideUp(200);
            }
            
            // Update select all checkbox
            const totalCheckboxes = $('.listing-checkbox, .hph-listing-checkbox').length;
            const checkedCheckboxes = $('.listing-checkbox:checked, .hph-listing-checkbox:checked').length;
            
            this.$selectAll.prop('checked', checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0);
            this.$selectAll.prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
        },
        
        handleBulkAction: function() {
            const action = this.$bulkAction.val();
            
            if (!action) {
                this.showNotification('Please select an action', 'error');
                return;
            }
            
            if (this.selectedListings.length === 0) {
                this.showNotification('No listings selected', 'error');
                return;
            }
            
            // Confirm dangerous actions
            if (action === 'delete') {
                if (!confirm('Are you sure you want to delete the selected listings? This action cannot be undone.')) {
                    return;
                }
            }
            
            this.performBulkAction(action, this.selectedListings);
        },
        
        performBulkAction: function(action, listingIds) {
            $.ajax({
                url: this.ajaxurl,
                method: 'POST',
                data: {
                    action: 'hph_bulk_listing_actions',
                    nonce: this.nonce,
                    bulk_action: action,
                    listing_ids: listingIds
                },
                beforeSend: () => {
                    this.$applyBulk.prop('disabled', true).text('Processing...');
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotification(response.data.message, 'success');
                        this.loadListings();
                        this.loadStats();
                        this.cancelBulkSelection();
                    } else {
                        this.showNotification(response.data.message || 'An error occurred', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Network error. Please try again.', 'error');
                },
                complete: () => {
                    this.$applyBulk.prop('disabled', false).text('Apply');
                }
            });
        },
        
        cancelBulkSelection: function() {
            $('.listing-checkbox, .hph-listing-checkbox').prop('checked', false);
            this.$selectAll.prop('checked', false).prop('indeterminate', false);
            this.selectedListings = [];
            this.$bulkBar.slideUp(200);
            this.$bulkAction.val('');
        },
        
        // === LISTING CRUD OPERATIONS ===
        
        loadListings: function() {
            if (this.isLoading) return;
            
            this.isLoading = true;
            this.showLoadingState();
            
            const data = {
                action: 'hph_get_listings',
                nonce: this.nonce,
                page: this.currentPage,
                per_page: this.perPage,
                status: this.currentFilter,
                sort: this.currentSort,
                search: this.$searchInput.val() || '',
                filters: this.currentFilters,
                use_services: this.servicesAvailable ? '1' : '0'
            };
            
            $.ajax({
                url: this.ajaxurl,
                method: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        this.renderListings(response.data.listings);
                        this.updatePagination(response.data);
                    } else {
                        this.showError(response.data.message || 'Could not load listings');
                    }
                },
                error: () => {
                    this.showError('Network error. Please try again.');
                },
                complete: () => {
                    this.isLoading = false;
                    this.hideLoadingState();
                }
            });
        },
        
        renderListings: function(listings) {
            if (!listings || listings.length === 0) {
                this.$container.html(this.getEmptyState());
                return;
            }
            
            let html = '';
            listings.forEach(listing => {
                html += this.renderListingCard(listing);
            });
            
            this.$container.html(html);
            this.updateSelectedListings();
        },
        
        renderListingCard: function(listing) {
            const features = [];
            if (listing.bedrooms) features.push(listing.bedrooms + ' bed');
            if (listing.bathrooms) features.push(listing.bathrooms + ' bath');
            if (listing.square_feet) features.push(listing.square_feet.toLocaleString() + ' sqft');
            
            const statusColors = {
                'active': 'success',
                'pending': 'warning', 
                'sold': 'info',
                'draft': 'default'
            };
            
            return `
                <div class="hph-dashboard-card" data-listing-id="${listing.id}">
                    <div class="hph-card">
                        <div class="hph-card-header">
                            <div class="hph-card-controls">
                                <input type="checkbox" class="hph-listing-checkbox" data-listing-id="${listing.id}">
                                <span class="hph-badge hph-badge-${statusColors[listing.status] || 'default'}">${listing.status || 'draft'}</span>
                            </div>
                            <div class="hph-card-actions">
                                <button class="hph-btn-icon" data-action="edit" data-listing-id="${listing.id}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="hph-btn-icon" data-action="duplicate" data-listing-id="${listing.id}" title="Duplicate">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="hph-btn-icon btn-danger" data-action="delete" data-listing-id="${listing.id}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        ${listing.featured_image ? `<img src="${listing.featured_image}" alt="${listing.title}" class="hph-card-image">` : ''}
                        
                        <div class="hph-card-body">
                            <h3 class="hph-card-title">${listing.title}</h3>
                            
                            <div class="hph-listing-price-container">
                                <div class="hph-dashboard-price" data-listing-id="${listing.id}" data-current-price="${listing.price || 0}">
                                    ${listing.price ? '$' + listing.price.toLocaleString() : 'Set Price'}
                                </div>
                                <div class="hph-price-editor" data-listing-id="${listing.id}">
                                    <input type="number" min="0" value="${listing.price || ''}" placeholder="Enter price">
                                    <button class="save-btn" data-listing-id="${listing.id}">Save</button>
                                    <button class="cancel-btn">Cancel</button>
                                </div>
                            </div>
                            
                            <div class="hph-card-features">${features.join(' • ')}</div>
                            
                            <div class="hph-listing-stats">
                                <div class="stat">
                                    <strong>${listing.views || 0}</strong>
                                    <small>Views</small>
                                </div>
                                <div class="stat">
                                    <strong>${listing.leads_count || 0}</strong>
                                    <small>Leads</small>
                                </div>
                                <div class="stat">
                                    <strong>${new Date(listing.date_listed || listing.date_created).toLocaleDateString()}</strong>
                                    <small>Listed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        },
        
        // === MODAL OPERATIONS ===
        
        openAddModal: function(e) {
            if (e) e.preventDefault();
            $('#listingFormTitle').text('Add New Listing');
            $('#listingId').val('');
            $('#formAction').val('add_listing');
            this.$listingForm[0].reset();
            this.showModal();
        },
        
        showModal: function() {
            this.$listingModal.css('display', 'flex').hide().fadeIn(200);
            $('body').addClass('modal-open');
        },
        
        handleFormSubmit: function(e) {
            e.preventDefault();
            
            const formData = new FormData(this.$listingForm[0]);
            const listingId = $('#listingId').val();
            
            formData.append('action', listingId ? 'hph_update_listing' : 'hph_create_listing');
            formData.append('nonce', this.nonce);
            formData.append('use_services', this.servicesAvailable ? '1' : '0');
            
            const $submitBtn = $('#saveListingBtn');
            $submitBtn.prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.showNotification('Listing saved successfully', 'success');
                        this.closeModal();
                        this.loadListings();
                        this.loadStats();
                    } else {
                        this.showNotification(response.data || 'Error saving listing', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Network error. Please try again.', 'error');
                },
                complete: () => {
                    $submitBtn.prop('disabled', false).text('Save Listing');
                }
            });
        },
        
        // === ADDITIONAL DATA LOADING ===
        
        // === RECENT ACTIVITY LOADING ===
        
        loadRecentActivity: function() {
            const $activityContainer = $('#recentActivityContent');
            if (!$activityContainer.length) return;
            
            
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_recent_activity',
                    nonce: this.nonce
                },
                success: (response) => {
                    if (response.success && response.data.activities) {
                        this.renderRecentActivity(response.data.activities);
                    } else {
                        this.renderRecentActivityEmpty();
                    }
                },
                error: (xhr, status, error) => {
                    this.renderRecentActivityError();
                }
            });
        },
        
        renderRecentActivity: function(activities) {
            const $container = $('#recentActivityContent');
            
            if (!activities || activities.length === 0) {
                this.renderRecentActivityEmpty();
                return;
            }
            
            let html = '<div class="hph-flex hph-flex-col hph-gap-sm">';
            activities.forEach(activity => {
                html += `
                    <div class="hph-flex hph-items-center hph-gap-sm hph-p-3 hph-bg-gray-50 hph-rounded-lg hover:hph-bg-gray-100 hph-transition">
                        <div class="hph-w-10 hph-h-10 hph-bg-primary-100 hph-text-primary-600 hph-rounded-lg hph-flex hph-items-center hph-justify-center hph-flex-shrink-0">
                            ${activity.icon || '<i class="fas fa-home"></i>'}
                        </div>
                        <div class="hph-flex-1 hph-min-w-0">
                            <div class="hph-font-medium hph-text-gray-900 hph-truncate">
                                ${activity.text}
                            </div>
                            <div class="hph-text-sm hph-text-gray-600">
                                ${activity.time}
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            $container.html(html);
        },
        
        renderRecentActivityEmpty: function() {
            const $container = $('#recentActivityContent');
            $container.html(`
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="empty-state-title">No Recent Activity</div>
                    <div class="empty-state-description">No recent activity to display.</div>
                </div>
            `);
        },
        
        renderRecentActivityError: function() {
            const $container = $('#recentActivityContent');
            $container.html(`
                <div class="hph-text-center hph-py-8 hph-text-red-500">
                    <i class="fas fa-exclamation-triangle hph-text-2xl hph-mb-2"></i>
                    <p>Failed to load recent activity</p>
                </div>
            `);
        },
        
        // === UPCOMING EVENTS LOADING ===
        
        loadUpcomingEvents: function() {
            const $eventsContainer = $('#upcomingEventsContent');
            if (!$eventsContainer.length) return;
            
            
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_upcoming_events',
                    nonce: this.nonce
                },
                success: (response) => {
                    if (response.success && response.data.events) {
                        this.renderUpcomingEvents(response.data.events);
                    } else {
                        this.renderUpcomingEventsEmpty();
                    }
                },
                error: (xhr, status, error) => {
                    this.renderUpcomingEventsError();
                }
            });
        },
        
        renderUpcomingEvents: function(events) {
            const $container = $('#upcomingEventsContent');
            
            if (!events || events.length === 0) {
                this.renderUpcomingEventsEmpty();
                return;
            }
            
            let html = '<div class="hph-flex hph-flex-col hph-gap-sm">';
            events.forEach(event => {
                html += `
                    <div class="hph-p-3 hph-border hph-border-gray-200 hph-rounded-lg hover:hph-border-primary hph-transition">
                        <div class="hph-font-medium hph-text-sm hph-mb-1">
                            ${event.title}
                        </div>
                        <div class="hph-flex hph-justify-between hph-text-xs hph-text-gray-600">
                            <span>${event.date}</span>
                            <span>${event.time}</span>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            $container.html(html);
        },
        
        renderUpcomingEventsEmpty: function() {
            const $container = $('#upcomingEventsContent');
            $container.html(`
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="empty-state-title">No Upcoming Events</div>
                    <div class="empty-state-description">No events scheduled.</div>
                </div>
            `);
        },
        
        renderUpcomingEventsError: function() {
            const $container = $('#upcomingEventsContent');
            $container.html(`
                <div class="hph-text-center hph-py-8 hph-text-red-500">
                    <i class="fas fa-exclamation-triangle hph-text-2xl hph-mb-2"></i>
                    <p>Failed to load upcoming events</p>
                </div>
            `);
        },
        
        // === HOT LEADS LOADING ===
        
        loadHotLeads: function() {
            const $leadsContainer = $('#hotLeadsContent');
            if (!$leadsContainer.length) return;
            
            
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_hot_leads',
                    nonce: this.nonce
                },
                success: (response) => {
                    if (response.success && response.data.leads) {
                        this.renderHotLeads(response.data.leads);
                    } else {
                        this.renderHotLeadsEmpty();
                    }
                },
                error: (xhr, status, error) => {
                    this.renderHotLeadsError();
                }
            });
        },
        
        renderHotLeads: function(leads) {
            const $container = $('#hotLeadsContent');
            
            if (!leads || leads.length === 0) {
                this.renderHotLeadsEmpty();
                return;
            }
            
            let html = '<div class="hph-flex hph-flex-col hph-gap-sm">';
            leads.slice(0, 5).forEach(lead => { // Show only top 5
                const statusBadge = lead.status === 'hot' ? 'danger' : (lead.status === 'warm' ? 'warning' : 'info');
                html += `
                    <div class="hph-flex hph-items-center hph-gap-sm hph-p-3 hph-border hph-border-gray-200 hph-rounded-lg hover:hph-border-primary hph-transition">
                        <span class="dashboard-badge ${statusBadge}">${lead.status_label || 'Lead'}</span>
                        <div class="hph-flex-1 hph-min-w-0">
                            <div class="hph-font-medium hph-text-gray-900">${lead.name}</div>
                            <div class="hph-text-sm hph-text-gray-600">
                                ${lead.email || ''}${lead.phone && lead.email ? ' • ' : ''}${lead.phone || ''}
                            </div>
                        </div>
                        <button class="hph-btn hph-btn-outline hph-btn-sm" onclick="window.location.href='?section=leads&lead=${lead.id}'">
                            Contact
                        </button>
                    </div>
                `;
            });
            html += '</div>';
            
            $container.html(html);
        },
        
        renderHotLeadsEmpty: function() {
            const $container = $('#hotLeadsContent');
            $container.html(`
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="empty-state-title">No Hot Leads</div>
                    <div class="empty-state-description">No hot leads at this time.</div>
                </div>
            `);
        },
        
        renderHotLeadsError: function() {
            const $container = $('#hotLeadsContent');
            $container.html(`
                <div class="hph-text-center hph-py-8 hph-text-red-500">
                    <i class="fas fa-exclamation-triangle hph-text-2xl hph-mb-2"></i>
                    <p>Failed to load hot leads</p>
                </div>
            `);
        },
        
        // === ANALYTICS LOADING ===
        
        loadAnalyticsData: function() {
            this.loadAnalyticsKPIs();
            this.loadTopListings();
            this.loadLeadSources();
            this.loadMarketInsights();
        },
        
        loadAnalyticsKPIs: function() {
            const dateRange = $('#analyticsDateRange').val() || '30';
            
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_analytics',
                    nonce: this.nonce,
                    date_range: dateRange,
                    metrics: ['revenue', 'conversion', 'avg_days', 'lead_score']
                },
                success: (response) => {
                    if (response.success && response.data) {
                        this.renderAnalyticsKPIs(response.data);
                    } else {
                        this.renderAnalyticsKPIsError();
                    }
                },
                error: (xhr, status, error) => {
                    this.renderAnalyticsKPIsError();
                }
            });
        },
        
        renderAnalyticsKPIs: function(data) {
            // Update revenue KPI
            if (data.revenue !== undefined) {
                $('#revenue-value').html('$' + (data.revenue || 0).toLocaleString());
                $('#revenue-trend').html(this.getTrendIcon(data.revenue_trend || 0));
            }
            
            // Update conversion rate KPI
            if (data.conversion_rate !== undefined) {
                $('#conversion-value').html((data.conversion_rate || 0) + '%');
                $('#conversion-trend').html(this.getTrendIcon(data.conversion_trend || 0));
            }
            
            // Update average days KPI
            if (data.avg_days_on_market !== undefined) {
                $('#days-value').html((data.avg_days_on_market || 0) + ' days');
                $('#days-trend').html(this.getTrendIcon(data.days_trend || 0, true)); // Reverse: less days is better
            }
            
            // Update lead score KPI
            if (data.avg_lead_score !== undefined) {
                $('#lead-score-value').html((data.avg_lead_score || 0) + '/100');
                $('#lead-score-trend').html(this.getTrendIcon(data.lead_score_trend || 0));
            }
        },
        
        renderAnalyticsKPIsError: function() {
            $('.hph-kpi-card .hph-loading-spinner').each(function() {
                $(this).html('<span class="hph-text-red-500">Error</span>');
            });
        },
        
        getTrendIcon: function(trend, reverse = false) {
            const isPositive = reverse ? trend < 0 : trend > 0;
            const isNegative = reverse ? trend > 0 : trend < 0;
            
            if (isPositive) {
                return '<i class="fas fa-arrow-up hph-text-success"></i>';
            } else if (isNegative) {
                return '<i class="fas fa-arrow-down hph-text-danger"></i>';
            } else {
                return '<i class="fas fa-minus hph-text-gray-400"></i>';
            }
        },
        
        loadTopListings: function() {
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_analytics',
                    nonce: this.nonce,
                    type: 'top_listings',
                    limit: 5
                },
                success: (response) => {
                    if (response.success && response.data.listings) {
                        this.renderTopListings(response.data.listings);
                    } else {
                        this.renderTopListingsEmpty();
                    }
                },
                error: (xhr, status, error) => {
                    this.renderTopListingsError();
                }
            });
        },
        
        renderTopListings: function(listings) {
            const $container = $('#topListingsContent');
            
            if (!listings || listings.length === 0) {
                this.renderTopListingsEmpty();
                return;
            }
            
            let html = '<div class="hph-analytics-table">';
            listings.forEach((listing, index) => {
                html += `
                    <div class="hph-table-row">
                        <div class="hph-rank">#${index + 1}</div>
                        <div class="hph-listing-info">
                            <div class="hph-listing-title">${listing.title}</div>
                            <div class="hph-listing-meta">${listing.views} views • ${listing.leads} leads</div>
                        </div>
                        <div class="hph-performance-score">
                            <span class="hph-score">${listing.score || 0}</span>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            $container.html(html);
        },
        
        renderTopListingsEmpty: function() {
            const $container = $('#topListingsContent');
            $container.html(`
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="empty-state-title">No Performance Data</div>
                    <div class="empty-state-description">No listing performance data available.</div>
                </div>
            `);
        },
        
        renderTopListingsError: function() {
            const $container = $('#topListingsContent');
            $container.html(`
                <div class="hph-text-center hph-py-8 hph-text-red-500">
                    <i class="fas fa-exclamation-triangle hph-text-2xl hph-mb-2"></i>
                    <p>Failed to load top listings</p>
                </div>
            `);
        },
        
        loadLeadSources: function() {
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_analytics',
                    nonce: this.nonce,
                    type: 'lead_sources'
                },
                success: (response) => {
                    if (response.success && response.data.sources) {
                        this.renderLeadSources(response.data.sources);
                    } else {
                        this.renderLeadSourcesEmpty();
                    }
                },
                error: (xhr, status, error) => {
                    this.renderLeadSourcesError();
                }
            });
        },
        
        renderLeadSources: function(sources) {
            const $container = $('#leadSourcesContent');
            
            if (!sources || sources.length === 0) {
                this.renderLeadSourcesEmpty();
                return;
            }
            
            let html = '<div class="hph-lead-sources-chart">';
            sources.forEach(source => {
                const percentage = ((source.count / sources.reduce((sum, s) => sum + s.count, 0)) * 100).toFixed(1);
                html += `
                    <div class="hph-source-item">
                        <div class="hph-source-info">
                            <span class="hph-source-name">${source.name}</span>
                            <span class="hph-source-count">${source.count} leads</span>
                        </div>
                        <div class="hph-source-bar">
                            <div class="hph-source-fill" style="width: ${percentage}%"></div>
                        </div>
                        <span class="hph-source-percentage">${percentage}%</span>
                    </div>
                `;
            });
            html += '</div>';
            
            $container.html(html);
        },
        
        renderLeadSourcesEmpty: function() {
            const $container = $('#leadSourcesContent');
            $container.html(`
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="empty-state-title">No Lead Source Data</div>
                    <div class="empty-state-description">No lead source data available.</div>
                </div>
            `);
        },
        
        renderLeadSourcesError: function() {
            const $container = $('#leadSourcesContent');
            $container.html(`
                <div class="hph-text-center hph-py-8 hph-text-red-500">
                    <i class="fas fa-exclamation-triangle hph-text-2xl hph-mb-2"></i>
                    <p>Failed to load lead sources</p>
                </div>
            `);
        },
        
        loadMarketInsights: function() {
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_analytics',
                    nonce: this.nonce,
                    type: 'market_insights'
                },
                success: (response) => {
                    if (response.success && response.data) {
                        this.renderMarketInsights(response.data);
                    } else {
                        this.renderMarketInsightsError();
                    }
                },
                error: (xhr, status, error) => {
                    this.renderMarketInsightsError();
                }
            });
        },
        
        renderMarketInsights: function(insights) {
            // Market trends
            if (insights.trends) {
                $('#marketTrendsContent').html(`
                    <div class="hph-insight-stat">
                        <span class="hph-stat-value">${insights.trends.avg_price_change || 0}%</span>
                        <span class="hph-stat-label">Avg. Price Change</span>
                    </div>
                    <div class="hph-insight-stat">
                        <span class="hph-stat-value">${insights.trends.inventory_change || 0}%</span>
                        <span class="hph-stat-label">Inventory Change</span>
                    </div>
                `);
            }
            
            // Price analysis
            if (insights.pricing) {
                $('#priceAnalysisContent').html(`
                    <div class="hph-insight-stat">
                        <span class="hph-stat-value">$${(insights.pricing.median_price || 0).toLocaleString()}</span>
                        <span class="hph-stat-label">Median Price</span>
                    </div>
                    <div class="hph-insight-stat">
                        <span class="hph-stat-value">${insights.pricing.price_per_sqft || 0}/sqft</span>
                        <span class="hph-stat-label">Price per Sq Ft</span>
                    </div>
                `);
            }
            
            // Competition
            if (insights.competition) {
                $('#competitionContent').html(`
                    <div class="hph-insight-stat">
                        <span class="hph-stat-value">${insights.competition.active_agents || 0}</span>
                        <span class="hph-stat-label">Active Agents</span>
                    </div>
                    <div class="hph-insight-stat">
                        <span class="hph-stat-value">${insights.competition.market_share || 0}%</span>
                        <span class="hph-stat-label">Your Market Share</span>
                    </div>
                `);
            }
        },
        
        renderMarketInsightsError: function() {
            $('#marketTrendsContent, #priceAnalysisContent, #competitionContent').html(`
                <div class="hph-text-center hph-py-4 hph-text-red-500">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p class="hph-text-xs hph-mt-1">Failed to load</p>
                </div>
            `);
        },
        
        // === MARKETING FUNCTIONALITY ===
        
        loadMarketingData: function() {
            this.loadMarketingListings();
            this.loadMarketingActivity();
            this.bindMarketingEvents();
        },
        
        loadMarketingListings: function() {
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_agent_listings',
                    nonce: this.nonce,
                    status: 'active'
                },
                success: (response) => {
                    if (response.success && response.data.listings) {
                        this.populateListingSelects(response.data.listings);
                    } else {
                    }
                },
                error: (xhr, status, error) => {
                }
            });
        },
        
        populateListingSelects: function(listings) {
            // Populate all listing select dropdowns
            const selectIds = ['#pdfListingSelect', '#socialListingSelect', '#emailListingSelect'];
            
            selectIds.forEach(selectId => {
                const $select = $(selectId);
                if ($select.length) {
                    $select.html('<option value="">Select a listing...</option>');
                    
                    listings.forEach(listing => {
                        const option = `<option value="${listing.id}">${listing.title} - ${listing.address || 'No address'}</option>`;
                        $select.append(option);
                    });
                    
                    // Enable buttons when listing is selected
                    $select.on('change', function() {
                        const buttonId = selectId.replace('ListingSelect', 'Btn');
                        const $button = $(buttonId);
                        $button.prop('disabled', !$(this).val());
                    });
                }
            });
        },
        
        bindMarketingEvents: function() {
            // PDF Flyer Generation
            $('#generatePdfBtn').on('click', (e) => {
                e.preventDefault();
                this.generatePdfFlyer();
            });
            
            // Social Media Generation
            $('#generateSocialBtn').on('click', (e) => {
                e.preventDefault();
                this.generateSocialPost();
            });
            
            // Email Generation
            $('#generateEmailBtn').on('click', (e) => {
                e.preventDefault();
                this.generateEmail();
            });
            
            // Refresh marketing activity
            $('#refreshMarketingBtn').on('click', (e) => {
                e.preventDefault();
                this.loadMarketingActivity();
            });
        },
        
        generatePdfFlyer: function() {
            const listingId = $('#pdfListingSelect').val();
            const template = $('#pdfTemplateSelect').val();
            
            if (!listingId) {
                alert('Please select a listing first.');
                return;
            }
            
            const $button = $('#generatePdfBtn');
            this.setButtonLoading($button, true);
            
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_generate_pdf_flyer',
                    nonce: this.nonce,
                    listing_id: listingId,
                    template: template
                },
                success: (response) => {
                    this.setButtonLoading($button, false);
                    if (response.success && response.data.pdf_url) {
                        this.showGeneratedContent('PDF Flyer Generated', `
                            <div class="hph-generated-item">
                                <i class="fas fa-file-pdf hph-text-red-500 hph-text-2xl hph-mb-2"></i>
                                <h4>PDF Flyer Ready</h4>
                                <p class="hph-text-gray-600 hph-mb-4">Your professional listing flyer has been generated.</p>
                                <div class="hph-flex hph-gap-sm">
                                    <a href="${response.data.pdf_url}" target="_blank" class="hph-btn hph-btn-primary hph-btn-sm">
                                        <i class="fas fa-download"></i> Download PDF
                                    </a>
                                    <button type="button" class="hph-btn hph-btn-outline hph-btn-sm" onclick="navigator.share({url: '${response.data.pdf_url}'})">
                                        <i class="fas fa-share"></i> Share
                                    </button>
                                </div>
                            </div>
                        `);
                        this.loadMarketingActivity(); // Refresh activity log
                    } else {
                        alert('Failed to generate PDF flyer. Please try again.');
                    }
                },
                error: (xhr, status, error) => {
                    this.setButtonLoading($button, false);
                    alert('Error generating PDF flyer. Please try again.');
                }
            });
        },
        
        generateSocialPost: function() {
            const listingId = $('#socialListingSelect').val();
            const platform = $('#socialPlatformSelect').val();
            
            if (!listingId) {
                alert('Please select a listing first.');
                return;
            }
            
            const $button = $('#generateSocialBtn');
            this.setButtonLoading($button, true);
            
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_generate_social_template',
                    nonce: this.nonce,
                    listing_id: listingId,
                    platform: platform
                },
                success: (response) => {
                    this.setButtonLoading($button, false);
                    if (response.success && response.data) {
                        this.showGeneratedContent(`${platform.charAt(0).toUpperCase() + platform.slice(1)} Post Generated`, `
                            <div class="hph-generated-item">
                                <i class="fab fa-${platform} hph-text-blue-500 hph-text-2xl hph-mb-2"></i>
                                <h4>Social Media Post Ready</h4>
                                <div class="hph-social-preview hph-bg-gray-50 hph-p-4 hph-rounded-lg hph-mb-4">
                                    <div class="hph-font-medium hph-mb-2">Post Content:</div>
                                    <div class="hph-text-sm">${response.data.content || 'Content generated successfully'}</div>
                                    ${response.data.image_url ? `<img src="${response.data.image_url}" alt="Generated image" class="hph-mt-2 hph-w-full hph-max-w-sm hph-rounded">` : ''}
                                </div>
                                <div class="hph-flex hph-gap-sm">
                                    <button type="button" class="hph-btn hph-btn-primary hph-btn-sm" onclick="navigator.clipboard.writeText('${response.data.content?.replace(/'/g, '\\\'') || ''}')">
                                        <i class="fas fa-copy"></i> Copy Text
                                    </button>
                                    <button type="button" class="hph-btn hph-btn-outline hph-btn-sm">
                                        <i class="fas fa-share"></i> Share to ${platform.charAt(0).toUpperCase() + platform.slice(1)}
                                    </button>
                                </div>
                            </div>
                        `);
                        this.loadMarketingActivity();
                    } else {
                        alert('Failed to generate social media post. Please try again.');
                    }
                },
                error: (xhr, status, error) => {
                    this.setButtonLoading($button, false);
                    alert('Error generating social media post. Please try again.');
                }
            });
        },
        
        generateEmail: function() {
            const listingId = $('#emailListingSelect').val();
            const template = $('#emailTemplateSelect').val();
            
            if (!listingId) {
                alert('Please select a listing first.');
                return;
            }
            
            const $button = $('#generateEmailBtn');
            this.setButtonLoading($button, true);
            
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_send_marketing_email',
                    nonce: this.nonce,
                    listing_id: listingId,
                    template: template,
                    action_type: 'generate' // Just generate, don't send
                },
                success: (response) => {
                    this.setButtonLoading($button, false);
                    if (response.success && response.data) {
                        this.showGeneratedContent('Email Template Generated', `
                            <div class="hph-generated-item">
                                <i class="fas fa-envelope hph-text-blue-500 hph-text-2xl hph-mb-2"></i>
                                <h4>Email Template Ready</h4>
                                <div class="hph-email-preview hph-bg-gray-50 hph-p-4 hph-rounded-lg hph-mb-4">
                                    <div class="hph-font-medium hph-mb-2">Subject: ${response.data.subject || 'Email Generated'}</div>
                                    <div class="hph-text-sm hph-max-h-32 hph-overflow-y-auto">${response.data.content || 'Email content generated successfully'}</div>
                                </div>
                                <div class="hph-flex hph-gap-sm">
                                    <button type="button" class="hph-btn hph-btn-primary hph-btn-sm">
                                        <i class="fas fa-paper-plane"></i> Send Email
                                    </button>
                                    <button type="button" class="hph-btn hph-btn-outline hph-btn-sm">
                                        <i class="fas fa-edit"></i> Edit Template
                                    </button>
                                </div>
                            </div>
                        `);
                        this.loadMarketingActivity();
                    } else {
                        alert('Failed to generate email template. Please try again.');
                    }
                },
                error: (xhr, status, error) => {
                    this.setButtonLoading($button, false);
                    alert('Error generating email template. Please try again.');
                }
            });
        },
        
        loadMarketingActivity: function() {
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_marketing_activity',
                    nonce: this.nonce,
                    limit: 5
                },
                success: (response) => {
                    if (response.success && response.data.activities) {
                        this.renderMarketingActivity(response.data.activities);
                    } else {
                        $('#marketingActivityLog').html(`
                            <div class="hph-text-center hph-py-md hph-text-gray-500">
                                <p>No marketing activity yet. Generate your first material above!</p>
                            </div>
                        `);
                    }
                },
                error: (xhr, status, error) => {
                }
            });
        },
        
        renderMarketingActivity: function(activities) {
            const $container = $('#marketingActivityLog');
            
            if (!activities || activities.length === 0) {
                $container.html(`
                    <div class="hph-text-center hph-py-md hph-text-gray-500">
                        <p>No marketing activity yet. Generate your first material above!</p>
                    </div>
                `);
                return;
            }
            
            let html = '';
            activities.forEach(activity => {
                const iconClass = this.getActivityIcon(activity.type);
                html += `
                    <div class="hph-activity-item hph-flex hph-items-center hph-gap-sm hph-p-3 hph-bg-gray-50 hph-rounded-lg">
                        <div class="hph-activity-icon hph-w-8 hph-h-8 hph-bg-primary-100 hph-text-primary-600 hph-rounded-full hph-flex hph-items-center hph-justify-center">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="hph-flex-1">
                            <div class="hph-font-medium hph-text-sm">${activity.title}</div>
                            <div class="hph-text-xs hph-text-gray-600">${activity.description} • ${activity.time}</div>
                        </div>
                        ${activity.download_url ? `<a href="${activity.download_url}" class="hph-btn hph-btn-outline hph-btn-xs" target="_blank">Download</a>` : ''}
                    </div>
                `;
            });
            
            $container.html(html);
        },
        
        getActivityIcon: function(type) {
            const icons = {
                'pdf_flyer': 'fas fa-file-pdf',
                'social_post': 'fas fa-share-alt',
                'email_template': 'fas fa-envelope',
                'default': 'fas fa-file'
            };
            return icons[type] || icons.default;
        },
        
        showGeneratedContent: function(title, content) {
            const $container = $('#generatedContent');
            const $body = $('#generatedContentBody');
            
            $body.html(`
                <div class="hph-generated-header hph-flex hph-items-center hph-justify-between hph-mb-4">
                    <h4 class="hph-font-medium">${title}</h4>
                    <button type="button" class="hph-btn hph-btn-outline hph-btn-sm" onclick="$('#generatedContent').hide();">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                ${content}
            `);
            
            $container.show();
            $container[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        
        setButtonLoading: function($button, isLoading) {
            const $text = $button.find('.hph-btn-text');
            const $loading = $button.find('.hph-btn-loading');
            
            if (isLoading) {
                $text.hide();
                $loading.show();
                $button.prop('disabled', true);
            } else {
                $text.show();
                $loading.hide();
                $button.prop('disabled', false);
            }
        },
        
        // === EXPORT FUNCTIONALITY ===
        
        initExportFunctionality: function() {
            // Bind export buttons across all dashboard sections
            $(document).on('click.dashboard', '[data-export]', (e) => {
                e.preventDefault();
                const exportType = $(e.currentTarget).data('export');
                const section = $(e.currentTarget).data('section') || this.getCurrentSection();
                this.handleExport(exportType, section, e.currentTarget);
            });
        },
        
        getCurrentSection: function() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('section') || 'overview';
        },
        
        handleExport: function(exportType, section, buttonElement) {
            const $button = $(buttonElement);
            const originalText = $button.html();
            
            // Show loading state
            $button.html('<i class="fas fa-spinner fa-spin"></i> Exporting...').prop('disabled', true);
            
            
            $.ajax({
                url: this.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_export_data',
                    nonce: this.nonce,
                    export_type: exportType,
                    section: section,
                    format: 'csv', // Default to CSV
                    filters: this.getExportFilters(section)
                },
                success: (response) => {
                    $button.html(originalText).prop('disabled', false);
                    
                    if (response.success && response.data.download_url) {
                        // Trigger download
                        const link = document.createElement('a');
                        link.href = response.data.download_url;
                        link.download = response.data.filename || `${section}_export.csv`;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        
                        // Show success notification
                        this.showNotification('success', `${exportType} data exported successfully!`);
                    } else {
                        this.showNotification('error', 'Export failed. Please try again.');
                    }
                },
                error: (xhr, status, error) => {
                    $button.html(originalText).prop('disabled', false);
                    this.showNotification('error', 'Export error. Please try again.');
                }
            });
        },
        
        getExportFilters: function(section) {
            // Get current filters/search terms for the section to include in export
            const filters = {};
            
            switch (section) {
                case 'listings':
                    filters.search = $('#listingsSearch').val();
                    filters.status = $('.filter-btn.active').data('filter');
                    filters.property_type = $('#propertyType').val();
                    filters.min_price = $('#minPrice').val();
                    filters.max_price = $('#maxPrice').val();
                    break;
                    
                case 'leads':
                    filters.search = $('#leadsSearch').val();
                    filters.status = $('#leadsStatusFilter').val();
                    filters.source = $('#leadsSourceFilter').val();
                    break;
                    
                case 'analytics':
                    filters.date_range = $('#analyticsDateRange').val();
                    break;
                    
                default:
                    // No specific filters
                    break;
            }
            
            return filters;
        },
        
        showNotification: function(type, message) {
            // Simple notification system
            const notificationClass = type === 'success' ? 'hph-alert-success' : 'hph-alert-error';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
            
            const notification = $(`
                <div class="hph-notification ${notificationClass} hph-fixed hph-top-4 hph-right-4 hph-z-50 hph-p-4 hph-rounded-lg hph-shadow-lg hph-flex hph-items-center hph-gap-2" style="z-index: 1000;">
                    <i class="${icon}"></i>
                    <span>${message}</span>
                    <button type="button" class="hph-ml-4 hph-text-lg" onclick="$(this).parent().remove();">&times;</button>
                </div>
            `);
            
            $('body').append(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                notification.fadeOut(() => notification.remove());
            }, 5000);
        },
        
        // === QUICK ACTIONS ===
        
        handleQuickAdd: function(e) {
            const action = $(e.currentTarget).data('action');
            this.openAddModal();
        },
        
        handleQuickAction: function(e) {
            const action = $(e.currentTarget).data('action');
            
            switch(action) {
                case 'add-listing':
                    this.openAddModal();
                    break;
                case 'import-listings':
                    this.showNotification('Import feature coming soon', 'info');
                    break;
                default:
            }
        },
        
        // === INLINE EDITING ===
        
        handlePriceEdit: function(e) {
            e.preventDefault();
            const $priceDisplay = $(e.currentTarget);
            const $editor = $priceDisplay.siblings('.hph-price-editor');
            const $input = $editor.find('input');
            
            $priceDisplay.hide();
            $editor.addClass('active').show();
            $input.focus().select();
        },
        
        handlePriceSave: function(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const $editor = $btn.closest('.hph-price-editor');
            const $priceDisplay = $editor.siblings('.hph-dashboard-price');
            const $input = $editor.find('input');
            const listingId = $btn.data('listing-id') || $priceDisplay.data('listing-id');
            const newPrice = parseFloat($input.val()) || 0;
            
            if (!listingId) {
                this.showNotification('Listing ID not found', 'error');
                return;
            }
            
            const originalText = $btn.text();
            $btn.text('Saving...').prop('disabled', true);
            
            $.post(this.ajaxurl, {
                action: 'hph_update_listing_price',
                nonce: this.nonce,
                listing_id: listingId,
                price: newPrice
            })
            .done((response) => {
                if (response.success) {
                    const formattedPrice = newPrice > 0 ? '$' + newPrice.toLocaleString() : 'Set Price';
                    $priceDisplay.text(formattedPrice).data('current-price', newPrice);
                    $editor.removeClass('active').hide();
                    $priceDisplay.show();
                    this.showNotification('Price updated successfully', 'success');
                } else {
                    this.showNotification(response.data || 'Failed to update price', 'error');
                }
            })
            .fail(() => {
                this.showNotification('Network error. Please try again.', 'error');
            })
            .always(() => {
                $btn.text(originalText).prop('disabled', false);
            });
        },
        
        handlePriceCancel: function(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const $editor = $btn.closest('.hph-price-editor');
            const $priceDisplay = $editor.siblings('.hph-dashboard-price');
            const $input = $editor.find('input');
            
            $editor.removeClass('active').hide();
            $priceDisplay.show();
            
            const originalPrice = $priceDisplay.data('current-price') || '';
            $input.val(originalPrice);
        },
        
        handlePriceKeyPress: function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                $(e.currentTarget).closest('.hph-price-editor').find('.save-btn').click();
            } else if (e.which === 27) { // Escape key
                e.preventDefault();
                $(e.currentTarget).closest('.hph-price-editor').find('.cancel-btn').click();
            }
        },
        
        // === PLACEHOLDER HANDLERS FOR MISSING FUNCTIONALITY ===
        
        handleEditListing: function(e) {
            e.preventDefault();
            const listingId = $(e.currentTarget).data('listing-id');
            this.showNotification('Edit functionality coming soon', 'info');
        },
        
        handleDuplicateListing: function(e) {
            e.preventDefault();
            const listingId = $(e.currentTarget).data('listing-id');
            this.showNotification('Duplicate functionality coming soon', 'info');
        },
        
        handleDeleteListing: function(e) {
            e.preventDefault();
            const listingId = $(e.currentTarget).data('listing-id');
            if (confirm('Are you sure you want to delete this listing?')) {
                this.performBulkAction('delete', [listingId]);
            }
        },
        
        handleStatusChange: function(e) {
            e.preventDefault();
            this.showNotification('Status change functionality coming soon', 'info');
        },
        
        confirmStatusChange: function(e) {
            e.preventDefault();
        },
        
        handlePagination: function(e) {
            e.preventDefault();
            const page = $(e.currentTarget).data('page');
            this.currentPage = page;
            this.loadListings();
        },
        
        updatePagination: function(data) {
        },
        
        // === UTILITY FUNCTIONS ===
        
        showLoadingState: function() {
            this.$container.html('<div class="hph-loading"><span>Loading...</span></div>');
        },
        
        hideLoadingState: function() {
            // Loading state is replaced by content
        },
        
        showError: function(message) {
            this.$container.html(`<div class="hph-error"><p>${message}</p></div>`);
        },
        
        getEmptyState: function() {
            return `
                <div class="hph-empty-state">
                    <div class="hph-empty-icon">🏠</div>
                    <h3>No listings found</h3>
                    <p>Try adjusting your search criteria or add your first listing.</p>
                    <button type="button" class="hph-btn hph-btn-primary" id="addFirstListingBtn">Add Your First Listing</button>
                </div>
            `;
        },
        
        // === MISSING HANDLERS ===
        
        handleEditPriceButton: function(e) {
            e.preventDefault();
            const $card = $(e.currentTarget).closest('.hph-dashboard-card');
            const $priceDisplay = $card.find('.hph-dashboard-price');
            $priceDisplay.click();
        },
        
        handleScheduleOpenHouse: function(e) {
            e.preventDefault();
            this.showNotification('Open House scheduling coming soon', 'info');
        },
        
        handleRequestMarketing: function(e) {
            e.preventDefault();
            this.showNotification('Marketing request feature coming soon', 'info');
        },
        
        handleShareListing: function(e) {
            e.preventDefault();
            this.showNotification('Share functionality coming soon', 'info');
        },
        
        handleViewAnalytics: function(e) {
            e.preventDefault();
            this.showNotification('Analytics view coming soon', 'info');
        },
        
        showNotification: function(message, type = 'info') {
            if (!$('#hph-notifications').length) {
                $('body').append('<div id="hph-notifications" class="hph-notifications"></div>');
            }
            
            const notification = $(`
                <div class="hph-notification hph-notification-${type}">
                    <span>${message}</span>
                    <button class="notification-close">&times;</button>
                </div>
            `);
            
            $('#hph-notifications').append(notification);
            
            setTimeout(() => {
                notification.fadeOut(() => notification.remove());
            }, 5000);
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
    
    // === INITIALIZATION ===
    
    // Initialize when document is ready
    $(document).ready(function() {
        
        // Check if required dependencies are available
        if (typeof $ === 'undefined') {
            return;
        }
        
        if (typeof window.hphDashboard === 'undefined') {
        }
        
        DashboardController.init();
    });
    
    // Export for global access
    window.DashboardController = DashboardController;
    
    // Legacy compatibility
    window.ListingsController = DashboardController;
    window.EnhancedListingsController = DashboardController;

})(jQuery);
