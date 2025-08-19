/**
 * Open Houses Dashboard Controller
 * File Location: /wp-content/themes/happy-place/assets/js/dashboard/dashboard-open-houses.js
 */

(function($) {
    'use strict';

    const OpenHousesController = {
        // State
        currentView: 'calendar',
        currentDate: new Date(),
        openHouses: [],
        listings: [],
        filters: {
            status: '',
            time: 'all'
        },
        
        // Initialize
        init: function() {
            console.log('OpenHousesController: Initializing...');
            
            if (!$('#openHousesPage').length) {
                console.log('OpenHousesController: Open houses page not found, skipping initialization');
                return;
            }
            
            this.bindEvents();
            this.loadStats();
            this.loadListings();
            this.loadOpenHouses();
            
            console.log('OpenHousesController: Initialization complete');
        },
        
        // Bind events
        bindEvents: function() {
            // View toggle
            $(document).on('click', '.view-btn', this.handleViewToggle.bind(this));
            
            // Calendar navigation
            $(document).on('click', '#prevMonth', this.previousMonth.bind(this));
            $(document).on('click', '#nextMonth', this.nextMonth.bind(this));
            $(document).on('click', '.calendar-day', this.handleCalendarDayClick.bind(this));
            $(document).on('click', '.calendar-event', this.showOpenHouseDetails.bind(this));
            
            // Filters
            $(document).on('change', '#statusFilter', this.handleFilterChange.bind(this));
            $(document).on('change', '#timeFilter', this.handleFilterChange.bind(this));
            
            // Open house actions
            $(document).on('click', '#scheduleOpenHouseBtn, #scheduleFirstOpenHouse', this.showScheduleModal.bind(this));
            $(document).on('click', '.open-house-item', this.showOpenHouseDetails.bind(this));
            $(document).on('click', '#editOpenHouseBtn', this.editCurrentOpenHouse.bind(this));
            $(document).on('click', '#exportOpenHouses', this.exportOpenHouses.bind(this));
            
            // Forms
            $(document).on('submit', '#openHouseForm', this.saveOpenHouse.bind(this));
            $(document).on('submit', '#visitorForm', this.registerVisitor.bind(this));
            $(document).on('change', '#listingSelect', this.handleListingSelect.bind(this));
            
            // Visitor registration
            $(document).on('click', '.register-visitor-btn', this.showVisitorRegistration.bind(this));
            
            // Modal handlers
            $(document).on('click', '.modal-close, [data-dismiss="modal"]', this.closeModals.bind(this));
            $(document).on('click', '.modal', this.handleModalBackdrop.bind(this));
            $(document).on('keydown', this.handleKeyDown.bind(this));
        },
        
        // Load statistics
        loadStats: function() {
            console.log('OpenHousesController: Loading stats...');
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_open_house_stats',
                    nonce: hphDashboardSettings.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStats(response.data);
                    } else {
                        console.error('Failed to load open house stats:', response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error loading open house stats:', error);
                }
            });
        },
        
        // Update stats display
        updateStats: function(stats) {
            $('#totalOpenHouses').text(stats.total || 0);
            $('#totalVisitors').text(stats.visitors || 0);
            $('#avgVisitors').text(stats.avgVisitors || 0);
            $('#leadsGenerated').text(stats.leads || 0);
        },
        
        // Load listings for dropdown
        loadListings: function() {
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_agent_listings',
                    nonce: hphDashboardSettings.nonce,
                    status: 'active'
                },
                success: (response) => {
                    if (response.success) {
                        this.listings = response.data || [];
                        this.populateListingsDropdown();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error loading listings:', error);
                }
            });
        },
        
        // Populate listings dropdown
        populateListingsDropdown: function() {
            let html = '<option value="">Choose a listing...</option>';
            
            this.listings.forEach(listing => {
                html += `<option value="${listing.id}">${listing.title} - ${listing.address}</option>`;
            });
            
            $('#listingSelect').html(html);
        },
        
        // Load open houses
        loadOpenHouses: function(showLoading = true) {
            console.log('OpenHousesController: Loading open houses...');
            
            if (showLoading) {
                $('#openHousesLoading').show();
                $('#calendarContainer, #openHousesList, #emptyState').hide();
            }
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_open_houses',
                    nonce: hphDashboardSettings.nonce,
                    filters: this.filters,
                    month: this.currentDate.getMonth() + 1,
                    year: this.currentDate.getFullYear()
                },
                success: (response) => {
                    $('#openHousesLoading').hide();
                    
                    if (response.success) {
                        this.openHouses = response.data || [];
                        
                        if (this.openHouses.length === 0) {
                            this.showEmptyState();
                        } else {
                            this.renderOpenHouses();
                        }
                    } else {
                        console.error('Failed to load open houses:', response.data);
                        this.showError('Failed to load open houses. Please try again.');
                    }
                },
                error: (xhr, status, error) => {
                    $('#openHousesLoading').hide();
                    console.error('Error loading open houses:', error);
                    this.showError('Network error. Please try again.');
                }
            });
        },
        
        // Render open houses based on current view
        renderOpenHouses: function() {
            if (this.currentView === 'calendar') {
                this.renderCalendarView();
            } else {
                this.renderListView();
            }
        },
        
        // Render calendar view
        renderCalendarView: function() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            
            // Update calendar title
            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            $('#calendarTitle').text(`${monthNames[month]} ${year}`);
            
            // Generate calendar grid
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());
            
            let html = '';
            
            // Day headers
            const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            dayHeaders.forEach(day => {
                html += `<div class="calendar-day-header">${day}</div>`;
            });
            
            // Calendar days
            const currentDate = new Date(startDate);
            for (let week = 0; week < 6; week++) {
                for (let day = 0; day < 7; day++) {
                    const dayDate = new Date(currentDate);
                    const dateStr = this.formatDateString(dayDate);
                    const dayEvents = this.openHouses.filter(oh => oh.date === dateStr);
                    
                    let dayClasses = 'calendar-day';
                    if (dayDate.getMonth() !== month) {
                        dayClasses += ' other-month';
                    }
                    if (this.isToday(dayDate)) {
                        dayClasses += ' today';
                    }
                    
                    html += `
                        <div class="${dayClasses}" data-date="${dateStr}">
                            <div class="calendar-day-number">${dayDate.getDate()}</div>
                            ${dayEvents.map(event => `
                                <div class="calendar-event status-${event.status}" 
                                     data-event-id="${event.id}" 
                                     title="${event.title}">
                                    ${event.time} ${event.title}
                                </div>
                            `).join('')}
                        </div>
                    `;
                    
                    currentDate.setDate(currentDate.getDate() + 1);
                }
            }
            
            $('#calendarGrid').html(html);
            $('#calendarContainer').show();
            $('#openHousesList').hide();
        },
        
        // Render list view
        renderListView: function() {
            let html = '';
            
            // Sort open houses by date
            const sortedOpenHouses = [...this.openHouses].sort((a, b) => 
                new Date(a.datetime) - new Date(b.datetime)
            );
            
            sortedOpenHouses.forEach(openHouse => {
                const eventDate = new Date(openHouse.datetime);
                const dayNum = eventDate.getDate();
                const monthName = eventDate.toLocaleDateString('en-US', { month: 'short' });
                const timeStr = eventDate.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
                
                html += `
                    <div class="open-house-item" data-event-id="${openHouse.id}">
                        <div class="open-house-date">
                            <p class="date-day">${dayNum}</p>
                            <p class="date-month">${monthName}</p>
                            <p class="date-time">${timeStr}</p>
                        </div>
                        
                        <div class="open-house-info">
                            <h3 class="open-house-title">${openHouse.title}</h3>
                            <p class="open-house-address">${openHouse.address}</p>
                            <div class="open-house-meta">
                                <span class="status-badge status-${openHouse.status}">
                                    ${this.formatStatus(openHouse.status)}
                                </span>
                                <span class="visitor-count">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                    </svg>
                                    ${openHouse.visitor_count || 0} visitors
                                </span>
                                ${openHouse.description ? `<p class="open-house-description">${openHouse.description}</p>` : ''}
                            </div>
                        </div>
                        
                        <div class="open-house-actions">
                            <button class="btn btn-sm btn-outline register-visitor-btn" 
                                    data-event-id="${openHouse.id}"
                                    onclick="event.stopPropagation(); OpenHousesController.showVisitorRegistration(${openHouse.id})">
                                Add Visitor
                            </button>
                            <button class="btn btn-sm btn-primary" 
                                    onclick="event.stopPropagation(); OpenHousesController.editOpenHouse(${openHouse.id})">
                                Edit
                            </button>
                        </div>
                    </div>
                `;
            });
            
            $('#openHousesList').html(html).show();
            $('#calendarContainer').hide();
        },
        
        // Show empty state
        showEmptyState: function() {
            $('#emptyState').show();
            $('#calendarContainer, #openHousesList').hide();
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
                    <button class="btn btn-primary" onclick="OpenHousesController.loadOpenHouses()">Try Again</button>
                </div>
            `;
            
            if (this.currentView === 'calendar') {
                $('#calendarContainer').html(html).show();
            } else {
                $('#openHousesList').html(html).show();
            }
            
            $('#emptyState').hide();
        },
        
        // Handle view toggle
        handleViewToggle: function(e) {
            const $btn = $(e.currentTarget);
            const view = $btn.data('view');
            
            if (view === this.currentView) return;
            
            $('.view-btn').removeClass('active');
            $btn.addClass('active');
            
            this.currentView = view;
            this.renderOpenHouses();
        },
        
        // Calendar navigation
        previousMonth: function() {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.loadOpenHouses();
        },
        
        nextMonth: function() {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.loadOpenHouses();
        },
        
        // Handle calendar day click
        handleCalendarDayClick: function(e) {
            if ($(e.target).hasClass('calendar-event')) {
                return; // Let event handler handle it
            }
            
            const dateStr = $(e.currentTarget).data('date');
            console.log('Clicked date:', dateStr);
            
            // Could open a "Schedule for this date" modal
            this.showScheduleModal(dateStr);
        },
        
        // Handle filter changes
        handleFilterChange: function(e) {
            const $filter = $(e.currentTarget);
            const filterId = $filter.attr('id');
            
            if (filterId === 'statusFilter') {
                this.filters.status = $filter.val();
            } else if (filterId === 'timeFilter') {
                this.filters.time = $filter.val();
            }
            
            this.loadOpenHouses();
        },
        
        // Show schedule modal
        showScheduleModal: function(preselectedDate = null) {
            this.resetOpenHouseForm();
            $('#openHouseFormTitle').text('Schedule Open House');
            
            // Set default date if provided
            if (preselectedDate) {
                $('#eventDate').val(preselectedDate);
            } else {
                // Set to today
                const today = new Date().toISOString().split('T')[0];
                $('#eventDate').val(today);
            }
            
            // Set default times
            $('#startTime').val('14:00');
            $('#endTime').val('16:00');
            
            $('#openHouseFormModal').addClass('active').show();
            $('#listingSelect').focus();
        },
        
        // Show open house details
        showOpenHouseDetails: function(e) {
            e.stopPropagation();
            
            let openHouseId;
            
            if ($(e.currentTarget).hasClass('calendar-event')) {
                openHouseId = $(e.currentTarget).data('event-id');
            } else {
                openHouseId = $(e.currentTarget).data('event-id');
            }
            
            const openHouse = this.openHouses.find(oh => oh.id == openHouseId);
            if (!openHouse) return;
            
            const html = this.generateOpenHouseDetailsHTML(openHouse);
            $('#openHouseDetailsContent').html(html);
            $('#openHouseDetailsModal').addClass('active').show();
            $('#editOpenHouseBtn').data('event-id', openHouseId);
        },
        
        // Generate open house details HTML
        generateOpenHouseDetailsHTML: function(openHouse) {
            const eventDate = new Date(openHouse.datetime);
            const dayNum = eventDate.getDate();
            const monthName = eventDate.toLocaleDateString('en-US', { month: 'short' });
            const timeStr = eventDate.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            
            return `
                <div class="open-house-details">
                    <div class="details-header">
                        <div class="details-date">
                            <p class="date-day">${dayNum}</p>
                            <p class="date-month">${monthName}</p>
                            <p class="date-time">${timeStr}</p>
                        </div>
                        <div class="details-info">
                            <h3>${openHouse.title}</h3>
                            <p>${openHouse.address}</p>
                            <span class="status-badge status-${openHouse.status}">
                                ${this.formatStatus(openHouse.status)}
                            </span>
                        </div>
                    </div>
                    
                    <div class="details-body">
                        <div>
                            <div class="detail-section">
                                <h4>Event Information</h4>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Duration</label>
                                        <span>${this.formatDuration(openHouse.start_time, openHouse.end_time)}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Status</label>
                                        <span>${this.formatStatus(openHouse.status)}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Expected Visitors</label>
                                        <span>${openHouse.max_visitors || 'Not set'}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Actual Visitors</label>
                                        <span>${openHouse.visitor_count || 0}</span>
                                    </div>
                                </div>
                            </div>
                            
                            ${openHouse.description ? `
                                <div class="detail-section">
                                    <h4>Description</h4>
                                    <p>${openHouse.description.replace(/\n/g, '<br>')}</p>
                                </div>
                            ` : ''}
                            
                            ${openHouse.special_instructions ? `
                                <div class="detail-section">
                                    <h4>Special Instructions</h4>
                                    <p>${openHouse.special_instructions.replace(/\n/g, '<br>')}</p>
                                </div>
                            ` : ''}
                        </div>
                        
                        <div>
                            <div class="detail-section">
                                <h4>Visitors (${openHouse.visitors ? openHouse.visitors.length : 0})</h4>
                                <div class="visitors-list">
                                    ${openHouse.visitors && openHouse.visitors.length > 0 
                                        ? openHouse.visitors.map(visitor => `
                                            <div class="visitor-item">
                                                <div class="visitor-info">
                                                    <h5>${visitor.name}</h5>
                                                    <p>${visitor.email || ''} ${visitor.phone ? '• ' + visitor.phone : ''}</p>
                                                </div>
                                                <span class="interest-badge ${visitor.interest_level || 'browsing'}">
                                                    ${this.formatInterestLevel(visitor.interest_level)}
                                                </span>
                                            </div>
                                        `).join('')
                                        : '<p style="color: var(--hph-gray-600); text-align: center; padding: 1rem;">No visitors registered yet</p>'
                                    }
                                </div>
                                <button class="btn btn-outline btn-sm register-visitor-btn" 
                                        style="width: 100%; margin-top: 1rem;"
                                        onclick="OpenHousesController.showVisitorRegistration(${openHouse.id})">
                                    Register New Visitor
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        },
        
        // Edit current open house
        editCurrentOpenHouse: function() {
            const openHouseId = $('#editOpenHouseBtn').data('event-id');
            this.editOpenHouse(openHouseId);
            this.closeModals();
        },
        
        // Edit open house
        editOpenHouse: function(openHouseId) {
            const openHouse = this.openHouses.find(oh => oh.id == openHouseId);
            if (!openHouse) return;
            
            this.populateOpenHouseForm(openHouse);
            $('#openHouseFormTitle').text('Edit Open House');
            $('#openHouseFormModal').addClass('active').show();
        },
        
        // Populate open house form
        populateOpenHouseForm: function(openHouse) {
            $('#openHouseId').val(openHouse.id);
            $('#listingSelect').val(openHouse.listing_id);
            $('#eventDate').val(openHouse.date);
            $('#startTime').val(openHouse.start_time);
            $('#endTime').val(openHouse.end_time);
            $('#eventStatus').val(openHouse.status);
            $('#eventTitle').val(openHouse.custom_title || '');
            $('#eventDescription').val(openHouse.description || '');
            $('#maxVisitors').val(openHouse.max_visitors || '');
            $('#reminderTime').val(openHouse.reminder_time || '');
            $('#specialInstructions').val(openHouse.special_instructions || '');
            
            // Update selected listing display
            this.handleListingSelect({ target: $('#listingSelect')[0] });
        },
        
        // Reset form
        resetOpenHouseForm: function() {
            $('#openHouseForm')[0].reset();
            $('#openHouseId').val('');
            $('#selectedListing').hide();
        },
        
        // Handle listing selection
        handleListingSelect: function(e) {
            const listingId = $(e.target).val();
            const listing = this.listings.find(l => l.id == listingId);
            
            if (listing) {
                const html = `
                    <div class="selected-listing-header">
                        <img src="${listing.featured_image || '/wp-content/themes/happy-place-theme/assets/images/placeholder-home.jpg'}" 
                             alt="${listing.title}" class="listing-image">
                        <div class="listing-info">
                            <h4>${listing.title}</h4>
                            <p>${listing.address}</p>
                            <p>${listing.price_formatted}</p>
                        </div>
                    </div>
                `;
                $('#selectedListing').html(html).show();
            } else {
                $('#selectedListing').hide();
            }
        },
        
        // Save open house
        saveOpenHouse: function(e) {
            e.preventDefault();
            
            const $btn = $('#saveOpenHouseBtn');
            $btn.addClass('loading').prop('disabled', true);
            
            const formData = {
                action: 'hph_save_open_house',
                nonce: hphDashboardSettings.nonce
            };
            
            // Collect form data
            $('#openHouseForm').serializeArray().forEach(field => {
                formData[field.name] = field.value;
            });
            
            // Collect checkboxes
            $('#openHouseForm input[type="checkbox"]:checked').each(function() {
                formData[$(this).attr('name')] = $(this).val();
            });
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: formData,
                success: (response) => {
                    if (response.success) {
                        this.closeModals();
                        this.loadOpenHouses();
                        this.loadStats();
                        this.showNotification('Open house saved successfully!', 'success');
                    } else {
                        this.showNotification('Error: ' + (response.data || 'Unknown error'), 'error');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error saving open house:', error);
                    this.showNotification('Network error. Please try again.', 'error');
                },
                complete: () => {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        },
        
        // Show visitor registration modal
        showVisitorRegistration: function(openHouseId) {
            $('#visitorForm')[0].reset();
            $('#visitorOpenHouseId').val(openHouseId);
            $('#visitorRegistrationModal').addClass('active').show();
            $('#visitorName').focus();
        },
        
        // Register visitor
        registerVisitor: function(e) {
            e.preventDefault();
            
            const formData = {
                action: 'hph_register_visitor',
                nonce: hphDashboardSettings.nonce
            };
            
            $('#visitorForm').serializeArray().forEach(field => {
                formData[field.name] = field.value;
            });
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: formData,
                success: (response) => {
                    if (response.success) {
                        this.closeModals();
                        this.loadOpenHouses();
                        this.loadStats();
                        this.showNotification('Visitor registered successfully!', 'success');
                    } else {
                        this.showNotification('Error: ' + (response.data || 'Unknown error'), 'error');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error registering visitor:', error);
                    this.showNotification('Network error. Please try again.', 'error');
                }
            });
        },
        
        // Export open houses
        exportOpenHouses: function() {
            window.location.href = `${hphDashboardSettings.ajaxurl}?action=hph_export_open_houses&nonce=${hphDashboardSettings.nonce}`;
        },
        
        // Utility functions
        formatDateString: function(date) {
            return date.toISOString().split('T')[0];
        },
        
        isToday: function(date) {
            const today = new Date();
            return date.toDateString() === today.toDateString();
        },
        
        formatStatus: function(status) {
            const statuses = {
                'scheduled': 'Scheduled',
                'active': 'Active',
                'completed': 'Completed',
                'cancelled': 'Cancelled'
            };
            return statuses[status] || status;
        },
        
        formatDuration: function(startTime, endTime) {
            return `${this.formatTime(startTime)} - ${this.formatTime(endTime)}`;
        },
        
        formatTime: function(time24) {
            const [hours, minutes] = time24.split(':');
            const hour12 = hours % 12 || 12;
            const ampm = hours >= 12 ? 'PM' : 'AM';
            return `${hour12}:${minutes} ${ampm}`;
        },
        
        formatInterestLevel: function(level) {
            const levels = {
                'browsing': 'Browsing',
                'interested': 'Interested',
                'very_interested': 'Very Interested',
                'ready_to_buy': 'Ready to Buy'
            };
            return levels[level] || 'Browsing';
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
        if ($('#openHousesPage').length) {
            OpenHousesController.init();
        }
    });

    // Export to global scope
    window.OpenHousesController = OpenHousesController;

})(jQuery);