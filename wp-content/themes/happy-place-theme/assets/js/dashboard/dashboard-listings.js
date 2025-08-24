/**
 * Dashboard Listings JavaScript Controller
 * Complete AJAX-powered listings management with no Bootstrap dependencies
 */

(function($) {
    'use strict';

    const ListingsController = {
        // Properties
        currentPage: 1,
        perPage: 12,
        currentFilter: 'all',
        currentSort: 'date-desc',
        selectedListings: [],
        isLoading: false,
        servicesAvailable: false,
        
        // Initialize
        init: function() {
            // Guard clause: Only initialize if on listings page (required by structure guide)
            if (!$('#listingsPage').length) {
                console.log('ListingsController: Not on listings page, skipping initialization');
                return;
            }
            
            console.log('ListingsController: Initializing...');
            this.detectServices();
            this.cacheDom();
            this.bindEvents();
            this.loadStats();
            this.loadListings();
        },
        
        // Detect if plugin services are available
        detectServices: function() {
            // Check if services are available through global variables
            this.servicesAvailable = (typeof window.HPH !== 'undefined' && 
                                    window.HPH.services_available === true) ||
                                    (typeof hphDashboardSettings !== 'undefined' && 
                                    hphDashboardSettings.services_available === true);
            
            console.log('ListingsController: Services available:', this.servicesAvailable);
        },
        
        // Cache DOM elements
        cacheDom: function() {
            this.$page = $('#listingsPage');
            this.$grid = $('#listingsGrid');
            this.$listView = $('#listingsListView');
            this.$statsContainer = $('#listingsStats');
            this.$modal = $('#listingModal');
            this.$form = $('#listingForm');
            this.$searchInput = $('#listingsSearch');
            this.$filterBtns = $('.filter-btn');
            this.$sortSelect = $('#listingsSort');
            this.$viewToggle = $('.view-btn');
            this.$selectAllCheckbox = $('#selectAllListings');
        },
        
        // Bind event handlers
        bindEvents: function() {
            // Search input
            this.$searchInput.on('input', this.debounce(this.handleSearch.bind(this), 300));
            
            // Filter buttons
            this.$filterBtns.on('click', this.handleFilterChange.bind(this));
            
            // Sort dropdown
            this.$sortSelect.on('change', this.handleSortChange.bind(this));
            
            // View toggle
            this.$viewToggle.on('click', this.handleViewChange.bind(this));
            
            // Select all checkbox
            this.$selectAllCheckbox.on('change', this.handleSelectAll.bind(this));
            
            // Bulk actions
            $('#bulkDeleteBtn').on('click', this.handleBulkDelete.bind(this));
            $('#bulkStatusBtn').on('click', this.handleBulkStatus.bind(this));
            
            // Add new listing button
            $(document).on('click', '[data-modal-target="#listingModal"]', this.openAddModal.bind(this));
            
            // Modal controls
            $(document).on('click', '.hph-modal-backdrop, .hph-modal-close', this.closeModal.bind(this));
            $(document).on('click', '.hph-modal-content', function(e) { e.stopPropagation(); });
            
            // Form submission
            this.$form.on('submit', this.handleFormSubmit.bind(this));
            
            // Pagination
            $(document).on('click', '.pagination-btn', this.handlePagination.bind(this));
            
            // Notification close
            $(document).on('click', '.notification-close', function() {
                $(this).closest('.hph-notification').fadeOut(() => $(this).remove());
            });
        },
        
        // Load stats via AJAX
        loadStats: function() {
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_listing_stats',
                    nonce: hphDashboardSettings.dashboard_nonce,
                    use_services: this.servicesAvailable ? '1' : '0'
                },
                success: (response) => {
                    if (response.success) {
                        this.renderStats(response.data);
                    } else {
                        console.error('Failed to load stats:', response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Stats AJAX error:', error);
                }
            });
        },
        
        // Render stats cards
        renderStats: function(stats) {
            const $statsGrid = $('.stats-grid');
            if (!$statsGrid.length) return;
            
            // Add service status indicator
            if (this.servicesAvailable) {
                console.log('ListingsController: Rendering stats with enhanced service data');
            }
            
            const html = `
                <div class="stat-card">
                    <div class="stat-icon stat-icon-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-label">Total Listings</h3>
                        <p class="stat-value">${stats.total || 0}</p>
                        <p class="stat-change stat-change-up">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                                <path d="M5.293 3.293a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L6 5.414 3.707 7.707a1 1 0 01-1.414-1.414l3-3z"/>
                            </svg>
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
                        <p class="stat-value">${stats.active || 0}</p>
                        <p class="stat-change stat-change-up">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                                <path d="M5.293 3.293a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L6 5.414 3.707 7.707a1 1 0 01-1.414-1.414l3-3z"/>
                            </svg>
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
                        <p class="stat-value">${stats.pending || 0}</p>
                        <p class="stat-change stat-change-up">
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
                        <p class="stat-value">${stats.sold || 0}</p>
                        <p class="stat-change stat-change-up">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                                <path d="M5.293 3.293a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L6 5.414 3.707 7.707a1 1 0 01-1.414-1.414l3-3z"/>
                            </svg>
                            <span>Closed deals</span>
                        </p>
                    </div>
                </div>
            `;
            
            $statsGrid.html(html);
        },
        
        // Load listings via AJAX
        loadListings: function() {
            this.showLoading();
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_listings',
                    nonce: hphDashboardSettings.dashboard_nonce,
                    page: this.currentPage,
                    per_page: this.perPage,
                    status: this.currentFilter,
                    sort: this.currentSort,
                    search: this.$searchInput.val() || '',
                    use_services: this.servicesAvailable ? '1' : '0'
                },
                success: (response) => {
                    if (response.success) {
                        this.renderListings(response.data.listings);
                        this.updatePagination(response.data);
                    } else {
                        this.showNotification('error', response.data || 'Failed to load listings');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Listings AJAX error:', error);
                    this.showNotification('error', 'Network error loading listings');
                },
                complete: () => {
                    this.hideLoading();
                }
            });
        },
        
        // Show loading state
        showLoading: function() {
            this.isLoading = true;
            const loadingHtml = this.getLoadingSkeleton();
            
            if ($('.view-btn.active').data('view') === 'grid') {
                $('#listingsGrid').html(loadingHtml);
            } else {
                $('#listingsListView').html(loadingHtml);
            }
        },
        
        // Get loading skeleton HTML
        getLoadingSkeleton: function() {
            let html = '';
            for (let i = 0; i < 6; i++) {
                html += `
                    <div class="listing-card loading">
                        <div class="skeleton-box" style="height: 200px; margin-bottom: 1rem;"></div>
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                        <div class="skeleton-line short"></div>
                    </div>
                `;
            }
            return html;
        },
        
        // Hide loading state
        hideLoading: function() {
            this.isLoading = false;
        },
        
        // Render listings
        renderListings: function(listings) {
            if (!listings || listings.length === 0) {
                this.showEmptyState();
                return;
            }
            
            const isGridView = $('.view-btn.active').data('view') === 'grid';
            let html = '';
            
            listings.forEach(listing => {
                if (isGridView) {
                    html += this.renderListingCard(listing);
                } else {
                    html += this.renderListingRow(listing);
                }
            });
            
            if (isGridView) {
                $('#listingsGrid').html(html);
            } else {
                $('#listingsListView').html(html);
            }
            
            // Update filter button counts
            this.updateFilterCounts(listings);
        },
        
        // Render single listing card (grid view)
        renderListingCard: function(listing) {
            return `
                <div class="listing-card" data-id="${listing.id}">
                    <div class="listing-card-checkbox-wrapper">
                        <input type="checkbox" 
                               class="listing-card-checkbox" 
                               value="${listing.id}">
                    </div>
                    <div class="listing-card-image">
                        <img src="${listing.featured_image || '/wp-content/themes/happy-place-theme/assets/images/placeholder.jpg'}" 
                             alt="${listing.title}"
                             loading="lazy">
                        <span class="listing-status-badge ${listing.status}">
                            ${listing.status_label}
                        </span>
                        <span class="listing-price-badge">
                            $${this.formatNumber(listing.price)}
                        </span>
                    </div>
                    <div class="listing-card-content">
                        <h3 class="listing-card-title">${listing.title}</h3>
                        <p class="listing-card-address">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M8 16s6-5.686 6-10A6 6 0 002 6c0 4.314 6 10 6 10z"/>
                            </svg>
                            ${listing.address}
                        </p>
                        <div class="listing-card-features">
                            ${listing.bedrooms ? `<span>${listing.bedrooms} BD</span>` : ''}
                            ${listing.bathrooms ? `<span>${listing.bathrooms} BA</span>` : ''}
                            ${listing.square_feet ? `<span>${this.formatNumber(listing.square_feet)} SF</span>` : ''}
                        </div>
                        <div class="listing-card-meta">
                            <span class="listing-mls">${listing.mls_number ? 'MLS# ' + listing.mls_number : ''}</span>
                            <span class="listing-date">Listed ${this.formatDate(listing.date_created)}</span>
                        </div>
                    </div>
                    <div class="listing-card-actions">
                        <button class="btn-icon" onclick="ListingsController.editListing(${listing.id})" title="Edit Listing">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M11.013 2.513a1.75 1.75 0 012.475 2.475L6.226 12.25a2.751 2.751 0 01-.892.596l-2.047.848a.75.75 0 01-.98-.98l.848-2.047a2.751 2.751 0 01.596-.892l7.262-7.262z"/>
                            </svg>
                        </button>
                        <button class="btn-icon" onclick="ListingsController.viewListing(${listing.id})" title="View Details">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M8 3.5a4.5 4.5 0 100 9 4.5 4.5 0 000-9zM2 8a6 6 0 1112 0A6 6 0 012 8z"/>
                            </svg>
                        </button>
                        <button class="btn-icon btn-danger" onclick="ListingsController.deleteListing(${listing.id})" title="Delete Listing">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M6.5 1.75a.25.25 0 01.25-.25h2.5a.25.25 0 01.25.25V3h-3V1.75zm4.5 0V3h2.25a.75.75 0 010 1.5H2.75a.75.75 0 010-1.5H5V1.75C5 .784 5.784 0 6.75 0h2.5C10.216 0 11 .784 11 1.75z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
        },
        
        // Show empty state
        showEmptyState: function() {
            const emptyHtml = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 64 64" fill="currentColor" opacity="0.3">
                            <path d="M32 8l-4 4v8h-8l-4 4v32h32V24l4-4h8v-8l-4-4z"/>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No Listings Found</h3>
                    <p class="empty-state-text">Start by adding your first property listing.</p>
                    <button class="btn btn-primary" onclick="ListingsController.openAddModal()">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                        Add Your First Listing
                    </button>
                </div>
            `;
            
            $('#listingsGrid').html(emptyHtml);
            $('#listingsListView').html(emptyHtml);
        },
        
        // Event handlers
        handleSearch: function() {
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
        
        handleViewChange: function(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const view = $btn.data('view');
            
            $('.view-btn').removeClass('active');
            $btn.addClass('active');
            
            if (view === 'grid') {
                $('#listingsGrid').show();
                $('#listingsListView').hide();
            } else {
                $('#listingsGrid').hide();
                $('#listingsListView').show();
            }
            
            // Re-render with current data
            this.loadListings();
        },
        
        // Modal operations
        openAddModal: function() {
            $('#listingModalTitle').text('Add New Listing');
            $('#listingId').val('');
            this.$form[0].reset();
            this.openModal();
        },
        
        editListing: function(id) {
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_listing',
                    nonce: hphDashboardSettings.dashboard_nonce,
                    listing_id: id,
                    use_services: this.servicesAvailable ? '1' : '0'
                },
                success: (response) => {
                    if (response.success) {
                        this.populateForm(response.data);
                        $('#listingModalTitle').text('Edit Listing');
                        this.openModal();
                    } else {
                        this.showNotification('error', response.data || 'Failed to load listing');
                    }
                },
                error: (xhr, status, error) => {
                    this.showNotification('error', 'Error loading listing details');
                }
            });
        },
        
        viewListing: function(id) {
            // Open listing in new tab
            window.open(`?p=${id}`, '_blank');
        },
        
        deleteListing: function(id) {
            if (!confirm('Are you sure you want to delete this listing?')) {
                return;
            }
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_delete_listing',
                    nonce: hphDashboardSettings.dashboard_nonce,
                    listing_id: id,
                    use_services: this.servicesAvailable ? '1' : '0'
                },
                success: (response) => {
                    if (response.success) {
                        let message = response.data.message || 'Listing deleted successfully';
                        
                        // Add service indicator if enhanced services were used
                        if (this.servicesAvailable && message.includes('service layer')) {
                            message += ' ⚡';
                        }
                        
                        this.showNotification('success', message);
                        this.loadListings();
                        this.loadStats();
                    } else {
                        this.showNotification('error', response.data || 'Failed to delete listing');
                    }
                },
                error: (xhr, status, error) => {
                    this.showNotification('error', 'Error deleting listing');
                }
            });
        },
        
        populateForm: function(data) {
            $('#listingId').val(data.id);
            
            // Populate all form fields
            Object.keys(data).forEach(key => {
                const $field = $(`[name="${key}"]`);
                if ($field.length) {
                    if ($field.is(':checkbox')) {
                        $field.prop('checked', data[key]);
                    } else if ($field.is('select')) {
                        $field.val(data[key]);
                    } else {
                        $field.val(data[key]);
                    }
                }
            });
        },
        
        openModal: function() {
            this.$modal.addClass('active');
            $('body').addClass('modal-open');
        },
        
        closeModal: function() {
            this.$modal.removeClass('active');
            $('body').removeClass('modal-open');
        },
        
        // Form submission
        handleFormSubmit: function(e) {
            e.preventDefault();
            
            const formData = new FormData(this.$form[0]);
            const listingId = $('#listingId').val();
            
            formData.append('action', listingId ? 'hph_update_listing' : 'hph_create_listing');
            formData.append('nonce', hphDashboardSettings.dashboard_nonce);
            formData.append('use_services', this.servicesAvailable ? '1' : '0');
            
            // Show loading
            const $submitBtn = $('#saveListingBtn');
            $submitBtn.find('.btn-text').hide();
            $submitBtn.find('.btn-loading').show();
            $submitBtn.prop('disabled', true);
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        let message = response.data.message || 'Listing saved successfully';
                        
                        // Add service indicator if enhanced services were used
                        if (this.servicesAvailable && message.includes('service layer')) {
                            message += ' ⚡';
                        }
                        
                        this.showNotification('success', message);
                        this.closeModal();
                        this.loadListings();
                        this.loadStats();
                    } else {
                        this.showNotification('error', response.data);
                    }
                },
                error: (xhr, status, error) => {
                    this.showNotification('error', 'Error saving listing');
                },
                complete: () => {
                    $submitBtn.find('.btn-text').show();
                    $submitBtn.find('.btn-loading').hide();
                    $submitBtn.prop('disabled', false);
                }
            });
        },
        
        // Notifications
        showNotification: function(type, message) {
            if (!$('#notifications').length) {
                $('body').append('<div id="notifications" class="hph-notifications"></div>');
            }
            
            const notification = $(`
                <div class="hph-notification ${type}">
                    <span>${message}</span>
                    <button class="notification-close">&times;</button>
                </div>
            `);
            
            $('#notifications').append(notification);
            
            setTimeout(() => {
                notification.fadeOut(() => notification.remove());
            }, 5000);
        },
        
        // Utilities
        formatNumber: function(num) {
            if (!num) return '0';
            return new Intl.NumberFormat('en-US').format(num);
        },
        
        formatDate: function(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleDateString();
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
    
    // Initialize
    $(document).ready(function() {
        ListingsController.init();
    });
    
    // Export for global access (required by structure guide)
    window.ListingsController = ListingsController;
    
})(jQuery);