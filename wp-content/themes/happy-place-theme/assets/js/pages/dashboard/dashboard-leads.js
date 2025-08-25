/**
 * Leads Dashboard Controller
 * File Location: /wp-content/themes/happy-place/assets/js/dashboard/dashboard-leads.js
 */

(function($) {
    'use strict';

    const LeadsController = {
        // State
        currentView: 'list',
        currentPage: 1,
        itemsPerPage: 20,
        totalLeads: 0,
        leads: [],
        filters: {
            status: '',
            source: '',
            score: '',
            search: ''
        },
        
        // Initialize
        init: function() {
            console.log('LeadsController: Initializing...');
            
            if (!$('#leadsPage').length) {
                console.log('LeadsController: Leads page not found, skipping initialization');
                return;
            }
            
            this.bindEvents();
            this.loadLeadStats();
            this.loadLeads();
            this.checkCRMStatus();
            
            console.log('LeadsController: Initialization complete');
        },
        
        // Bind events
        bindEvents: function() {
            // View toggle
            $(document).on('click', '.view-btn', this.handleViewToggle.bind(this));
            
            // Filters
            $(document).on('change', '#leadStatusFilter', this.handleFilterChange.bind(this));
            $(document).on('change', '#leadSourceFilter', this.handleFilterChange.bind(this));
            $(document).on('change', '#leadScoreFilter', this.handleFilterChange.bind(this));
            $(document).on('click', '#clearFilters', this.clearFilters.bind(this));
            
            // Lead actions
            $(document).on('click', '#addLeadBtn, #addFirstLead', this.showAddLeadModal.bind(this));
            $(document).on('click', '.lead-item, .lead-card', this.showLeadDetails.bind(this));
            $(document).on('click', '#editLeadBtn', this.editCurrentLead.bind(this));
            $(document).on('click', '#exportLeads', this.exportLeads.bind(this));
            
            // Lead form
            $(document).on('submit', '#leadForm', this.saveLead.bind(this));
            
            // Pagination
            $(document).on('click', '#prevPage', this.previousPage.bind(this));
            $(document).on('click', '#nextPage', this.nextPage.bind(this));
            $(document).on('click', '.page-number', this.goToPage.bind(this));
            
            // Modal handlers
            $(document).on('click', '.modal-close, [data-dismiss="modal"]', this.closeModals.bind(this));
            $(document).on('click', '.modal', this.handleModalBackdrop.bind(this));
            
            // Escape key to close modals
            $(document).on('keydown', this.handleKeyDown.bind(this));
        },
        
        // Load lead statistics
        loadLeadStats: function() {
            console.log('LeadsController: Loading lead stats...');
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_lead_stats',
                    nonce: hphDashboardSettings.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStats(response.data);
                    } else {
                        console.error('Failed to load lead stats:', response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error loading lead stats:', error);
                }
            });
        },
        
        // Update stats display
        updateStats: function(stats) {
            $('#totalLeads').text(stats.total || 0);
            $('#hotLeads').text(stats.hot || 0);
            $('#followUpRequired').text(stats.followUp || 0);
            $('#conversionRate').text((stats.conversionRate || 0) + '%');
        },
        
        // Load leads
        loadLeads: function(showLoading = true) {
            console.log('LeadsController: Loading leads...');
            
            if (showLoading) {
                $('#leadsLoading').show();
                $('#leadsList, #leadsGrid, #emptyState, #leadsPagination').hide();
            }
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_leads',
                    nonce: hphDashboardSettings.nonce,
                    page: this.currentPage,
                    per_page: this.itemsPerPage,
                    filters: this.filters
                },
                success: (response) => {
                    $('#leadsLoading').hide();
                    
                    if (response.success) {
                        this.leads = response.data.leads || [];
                        this.totalLeads = response.data.total || 0;
                        
                        if (this.leads.length === 0) {
                            this.showEmptyState();
                        } else {
                            this.renderLeads();
                            this.updatePagination();
                        }
                    } else {
                        console.error('Failed to load leads:', response.data);
                        this.showError('Failed to load leads. Please try again.');
                    }
                },
                error: (xhr, status, error) => {
                    $('#leadsLoading').hide();
                    console.error('Error loading leads:', error);
                    this.showError('Network error. Please try again.');
                }
            });
        },
        
        // Render leads
        renderLeads: function() {
            if (this.currentView === 'list') {
                this.renderLeadsList();
            } else {
                this.renderLeadsGrid();
            }
        },
        
        // Render leads list
        renderLeadsList: function() {
            let html = '';
            
            this.leads.forEach(lead => {
                const avatar = this.getLeadInitials(lead.first_name, lead.last_name);
                const scoreClass = this.getScoreClass(lead.score);
                const timeAgo = this.getTimeAgo(lead.created_at);
                
                html += `
                    <div class="lead-item" data-lead-id="${lead.id}">
                        <div class="lead-avatar">${avatar}</div>
                        <div class="lead-info">
                            <h4 class="lead-name">${lead.first_name} ${lead.last_name}</h4>
                            <p class="lead-details">${lead.email}${lead.phone ? ' • ' + lead.phone : ''}</p>
                            <div class="lead-meta">
                                <span class="lead-badge lead-badge-${scoreClass}">${this.getScoreLabel(lead.score)}</span>
                                <span class="lead-score">Score: ${lead.score}/100</span>
                                <span class="lead-source">${this.formatSource(lead.source)}</span>
                                <span class="lead-date">${timeAgo}</span>
                            </div>
                        </div>
                        <div class="lead-actions">
                            <button class="btn btn-sm btn-outline" onclick="LeadsController.contactLead(${lead.id})">
                                Contact
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="LeadsController.editLead(${lead.id})">
                                Edit
                            </button>
                        </div>
                    </div>
                `;
            });
            
            $('#leadsList').html(html).show();
            $('#leadsGrid').hide();
        },
        
        // Render leads grid
        renderLeadsGrid: function() {
            let html = '';
            
            this.leads.forEach(lead => {
                const avatar = this.getLeadInitials(lead.first_name, lead.last_name);
                const scoreClass = this.getScoreClass(lead.score);
                const timeAgo = this.getTimeAgo(lead.created_at);
                
                html += `
                    <div class="lead-card" data-lead-id="${lead.id}">
                        <div class="lead-card-header">
                            <div class="lead-avatar">${avatar}</div>
                            <div class="lead-info">
                                <h4 class="lead-name">${lead.first_name} ${lead.last_name}</h4>
                                <p class="lead-details">${lead.email}</p>
                            </div>
                        </div>
                        <div class="lead-card-body">
                            ${lead.phone ? `<p class="lead-preference">📞 ${lead.phone}</p>` : ''}
                            ${lead.budget_range ? `<p class="lead-preference">💰 ${this.formatBudget(lead.budget_range)}</p>` : ''}
                            ${lead.property_type ? `<p class="lead-preference">🏠 ${this.formatPropertyType(lead.property_type)}</p>` : ''}
                            ${lead.preferred_areas ? `<p class="lead-preference">📍 ${lead.preferred_areas}</p>` : ''}
                        </div>
                        <div class="lead-card-footer">
                            <div class="lead-meta">
                                <span class="lead-badge lead-badge-${scoreClass}">${this.getScoreLabel(lead.score)}</span>
                                <span class="lead-source">${this.formatSource(lead.source)}</span>
                            </div>
                            <div class="lead-actions">
                                <button class="btn btn-sm btn-outline" onclick="LeadsController.contactLead(${lead.id})">
                                    Contact
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            $('#leadsGrid').html(html).show();
            $('#leadsList').hide();
        },
        
        // Show empty state
        showEmptyState: function() {
            $('#emptyState').show();
            $('#leadsList, #leadsGrid, #leadsPagination').hide();
        },
        
        // Show error message
        showError: function(message) {
            const html = `
                <div class="error-state" style="padding: 3rem; text-align: center;">
                    <div class="error-icon" style="color: var(--hph-danger); margin-bottom: 1rem;">
                        <svg width="48" height="48" viewBox="0 0 48 48" fill="currentColor">
                            <path d="M24 4C12.96 4 4 12.96 4 24s8.96 20 20 20 20-8.96 20-20S35.04 4 24 4zm2 30h-4v-4h4v4zm0-8h-4V14h4v12z"/>
                        </svg>
                    </div>
                    <h3>${message}</h3>
                    <button class="btn btn-primary" onclick="LeadsController.loadLeads()">Try Again</button>
                </div>
            `;
            
            $('#leadsList').html(html).show();
            $('#leadsGrid, #emptyState').hide();
        },
        
        // Handle view toggle
        handleViewToggle: function(e) {
            const $btn = $(e.currentTarget);
            const view = $btn.data('view');
            
            if (view === this.currentView) return;
            
            $('.view-btn').removeClass('active');
            $btn.addClass('active');
            
            this.currentView = view;
            this.renderLeads();
        },
        
        // Handle filter change
        handleFilterChange: function(e) {
            const $filter = $(e.currentTarget);
            const filterType = $filter.attr('id').replace('lead', '').replace('Filter', '').toLowerCase();
            
            this.filters[filterType] = $filter.val();
            this.currentPage = 1;
            this.loadLeads();
        },
        
        // Clear filters
        clearFilters: function() {
            this.filters = {
                status: '',
                source: '',
                score: '',
                search: ''
            };
            
            $('#leadStatusFilter, #leadSourceFilter, #leadScoreFilter').val('');
            this.currentPage = 1;
            this.loadLeads();
        },
        
        // Show add lead modal
        showAddLeadModal: function() {
            this.resetLeadForm();
            $('#leadFormTitle').text('Add New Lead');
            $('#leadFormModal').addClass('active').show();
            $('#leadFirstName').focus();
        },
        
        // Show lead details
        showLeadDetails: function(e) {
            // Prevent if clicking on action buttons
            if ($(e.target).closest('.lead-actions').length) {
                return;
            }
            
            const leadId = $(e.currentTarget).data('lead-id');
            const lead = this.leads.find(l => l.id == leadId);
            
            if (!lead) return;
            
            const html = this.generateLeadDetailsHTML(lead);
            $('#leadDetailsContent').html(html);
            $('#leadDetailsModal').addClass('active').show();
            $('#editLeadBtn').data('lead-id', leadId);
        },
        
        // Generate lead details HTML
        generateLeadDetailsHTML: function(lead) {
            const avatar = this.getLeadInitials(lead.first_name, lead.last_name);
            const scoreClass = this.getScoreClass(lead.score);
            const timeAgo = this.getTimeAgo(lead.created_at);
            
            return `
                <div class="lead-details">
                    <div class="lead-header">
                        <div class="lead-avatar">${avatar}</div>
                        <div class="lead-info">
                            <h3 class="lead-name">${lead.first_name} ${lead.last_name}</h3>
                            <p class="lead-contact">${lead.email}${lead.phone ? ' • ' + lead.phone : ''}</p>
                            <div class="lead-badges">
                                <span class="lead-badge lead-badge-${scoreClass}">${this.getScoreLabel(lead.score)}</span>
                                <span class="status-badge status-${lead.status}">${this.formatStatus(lead.status)}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="lead-body">
                        <div class="detail-section">
                            <h4>Lead Information</h4>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <label>Score</label>
                                    <span>${lead.score}/100</span>
                                </div>
                                <div class="detail-item">
                                    <label>Source</label>
                                    <span>${this.formatSource(lead.source)}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Status</label>
                                    <span>${this.formatStatus(lead.status)}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Created</label>
                                    <span>${timeAgo}</span>
                                </div>
                            </div>
                        </div>
                        
                        ${lead.budget_range || lead.property_type || lead.bedrooms || lead.preferred_areas ? `
                            <div class="detail-section">
                                <h4>Property Preferences</h4>
                                <div class="detail-grid">
                                    ${lead.budget_range ? `
                                        <div class="detail-item">
                                            <label>Budget</label>
                                            <span>${this.formatBudget(lead.budget_range)}</span>
                                        </div>
                                    ` : ''}
                                    ${lead.property_type ? `
                                        <div class="detail-item">
                                            <label>Property Type</label>
                                            <span>${this.formatPropertyType(lead.property_type)}</span>
                                        </div>
                                    ` : ''}
                                    ${lead.bedrooms ? `
                                        <div class="detail-item">
                                            <label>Bedrooms</label>
                                            <span>${lead.bedrooms}+</span>
                                        </div>
                                    ` : ''}
                                    ${lead.preferred_areas ? `
                                        <div class="detail-item">
                                            <label>Preferred Areas</label>
                                            <span>${lead.preferred_areas}</span>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        ` : ''}
                        
                        ${lead.notes ? `
                            <div class="detail-section">
                                <h4>Notes</h4>
                                <div class="notes-content">
                                    ${lead.notes.replace(/\n/g, '<br>')}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        },
        
        // Edit current lead
        editCurrentLead: function() {
            const leadId = $('#editLeadBtn').data('lead-id');
            this.editLead(leadId);
            this.closeModals();
        },
        
        // Edit lead
        editLead: function(leadId) {
            const lead = this.leads.find(l => l.id == leadId);
            if (!lead) return;
            
            this.populateLeadForm(lead);
            $('#leadFormTitle').text('Edit Lead');
            $('#leadFormModal').addClass('active').show();
            $('#leadFirstName').focus();
        },
        
        // Populate lead form
        populateLeadForm: function(lead) {
            $('#leadId').val(lead.id);
            $('#leadFirstName').val(lead.first_name);
            $('#leadLastName').val(lead.last_name);
            $('#leadEmail').val(lead.email);
            $('#leadPhone').val(lead.phone);
            $('#leadStatus').val(lead.status);
            $('#leadSource').val(lead.source);
            $('#leadScore').val(lead.score);
            $('#leadBudget').val(lead.budget_range);
            $('#leadPropertyType').val(lead.property_type);
            $('#leadBedrooms').val(lead.bedrooms);
            $('#leadAreas').val(lead.preferred_areas);
            $('#leadNotes').val(lead.notes);
        },
        
        // Reset lead form
        resetLeadForm: function() {
            $('#leadForm')[0].reset();
            $('#leadId').val('');
            $('#leadScore').val('50');
        },
        
        // Save lead
        saveLead: function(e) {
            e.preventDefault();
            
            const $btn = $('#saveLeadBtn');
            $btn.addClass('loading').prop('disabled', true);
            
            const formData = {
                action: 'hph_save_lead',
                nonce: hphDashboardSettings.nonce
            };
            
            // Collect form data
            $('#leadForm').serializeArray().forEach(field => {
                formData[field.name] = field.value;
            });
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: formData,
                success: (response) => {
                    if (response.success) {
                        this.closeModals();
                        this.loadLeads();
                        this.loadLeadStats();
                        this.showNotification('Lead saved successfully!', 'success');
                    } else {
                        this.showNotification('Error: ' + (response.data || 'Unknown error'), 'error');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error saving lead:', error);
                    this.showNotification('Network error. Please try again.', 'error');
                },
                complete: () => {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        },
        
        // Contact lead
        contactLead: function(leadId) {
            const lead = this.leads.find(l => l.id == leadId);
            if (!lead) return;
            
            // Open email client
            window.location.href = `mailto:${lead.email}?subject=Hello ${lead.first_name}`;
        },
        
        // Export leads
        exportLeads: function() {
            window.location.href = `${hphDashboardSettings.ajaxurl}?action=hph_export_leads&nonce=${hphDashboardSettings.nonce}`;
        },
        
        // Update pagination
        updatePagination: function() {
            if (this.totalLeads <= this.itemsPerPage) {
                $('#leadsPagination').hide();
                return;
            }
            
            const totalPages = Math.ceil(this.totalLeads / this.itemsPerPage);
            const start = ((this.currentPage - 1) * this.itemsPerPage) + 1;
            const end = Math.min(this.currentPage * this.itemsPerPage, this.totalLeads);
            
            $('#paginationInfo').text(`Showing ${start}-${end} of ${this.totalLeads} leads`);
            
            // Update navigation buttons
            $('#prevPage').prop('disabled', this.currentPage === 1);
            $('#nextPage').prop('disabled', this.currentPage === totalPages);
            
            // Generate page numbers
            let pagesHtml = '';
            const maxVisible = 5;
            let startPage = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);
            
            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }
            
            for (let i = startPage; i <= endPage; i++) {
                pagesHtml += `
                    <a href="#" class="page-number ${i === this.currentPage ? 'active' : ''}" data-page="${i}">
                        ${i}
                    </a>
                `;
            }
            
            $('#pageNumbers').html(pagesHtml);
            $('#leadsPagination').show();
        },
        
        // Pagination handlers
        previousPage: function() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadLeads();
            }
        },
        
        nextPage: function() {
            const totalPages = Math.ceil(this.totalLeads / this.itemsPerPage);
            if (this.currentPage < totalPages) {
                this.currentPage++;
                this.loadLeads();
            }
        },
        
        goToPage: function(e) {
            e.preventDefault();
            const page = parseInt($(e.currentTarget).data('page'));
            if (page !== this.currentPage) {
                this.currentPage = page;
                this.loadLeads();
            }
        },
        
        // Check CRM status
        checkCRMStatus: function() {
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_check_crm_status',
                    nonce: hphDashboardSettings.nonce
                },
                success: (response) => {
                    if (response.success && response.data.connected) {
                        $('#crmStatus').show();
                    }
                }
            });
        },
        
        // Utility functions
        getLeadInitials: function(firstName, lastName) {
            return (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
        },
        
        getScoreClass: function(score) {
            if (score >= 80) return 'hot';
            if (score >= 60) return 'warm';
            return 'cold';
        },
        
        getScoreLabel: function(score) {
            if (score >= 80) return 'Hot';
            if (score >= 60) return 'Warm';
            return 'Cold';
        },
        
        formatSource: function(source) {
            const sources = {
                'website': 'Website',
                'listing_inquiry': 'Listing Inquiry',
                'open_house': 'Open House',
                'referral': 'Referral',
                'social_media': 'Social Media',
                'zillow': 'Zillow',
                'realtor_com': 'Realtor.com',
                'other': 'Other'
            };
            return sources[source] || source;
        },
        
        formatStatus: function(status) {
            const statuses = {
                'new': 'New',
                'contacted': 'Contacted',
                'qualified': 'Qualified',
                'nurturing': 'Nurturing',
                'converted': 'Converted',
                'lost': 'Lost'
            };
            return statuses[status] || status;
        },
        
        formatBudget: function(budget) {
            const budgets = {
                'under_200k': 'Under $200K',
                '200k_400k': '$200K - $400K',
                '400k_600k': '$400K - $600K',
                '600k_800k': '$600K - $800K',
                '800k_1m': '$800K - $1M',
                'over_1m': 'Over $1M'
            };
            return budgets[budget] || budget;
        },
        
        formatPropertyType: function(type) {
            const types = {
                'single_family': 'Single Family Home',
                'townhouse': 'Townhouse',
                'condo': 'Condominium',
                'multi_family': 'Multi-Family',
                'land': 'Land/Lot',
                'commercial': 'Commercial'
            };
            return types[type] || type;
        },
        
        getTimeAgo: function(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));
            
            if (diffInHours < 1) return 'Just now';
            if (diffInHours < 24) return `${diffInHours} hours ago`;
            
            const diffInDays = Math.floor(diffInHours / 24);
            if (diffInDays < 7) return `${diffInDays} days ago`;
            
            const diffInWeeks = Math.floor(diffInDays / 7);
            if (diffInWeeks < 4) return `${diffInWeeks} weeks ago`;
            
            return date.toLocaleDateString();
        },
        
        // Modal handlers
        closeModals: function() {
            $('.modal').removeClass('active').hide();
        },
        
        handleModalBackdrop: function(e) {
            if (e.target === e.currentTarget) {
                this.closeModals();
            }
        },
        
        handleKeyDown: function(e) {
            if (e.key === 'Escape') {
                this.closeModals();
            }
        },
        
        // Show notification
        showNotification: function(message, type = 'info') {
            const notification = $(`
                <div class="notification notification-${type}">
                    <span>${message}</span>
                    <button class="notification-close">&times;</button>
                </div>
            `);
            
            $('body').append(notification);
            
            notification.addClass('show');
            
            setTimeout(() => {
                notification.removeClass('show');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
            
            notification.find('.notification-close').on('click', () => {
                notification.removeClass('show');
                setTimeout(() => notification.remove(), 300);
            });
        }
    };

    // Initialize when page loads
    $(document).ready(function() {
        if ($('#leadsPage').length) {
            LeadsController.init();
        }
    });

    // Export to global scope for external access
    window.LeadsController = LeadsController;

})(jQuery);