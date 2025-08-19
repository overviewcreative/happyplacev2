/**
 * Happy Place Dashboard JavaScript
 * Main dashboard functionality and interactions
 */

(function($) {
    'use strict';

    // Dashboard namespace
    window.HptDashboard = {
        init: function() {
            this.setupEventListeners();
            this.initializeComponents();
            this.setupAutoRefresh();
        },

        setupEventListeners: function() {
            // Global dashboard events
            $(document).on('click', '.hpt-dashboard-nav__link', this.handleNavigation);
            $(document).on('submit', '.hpt-ajax-form', this.handleAjaxForm);
            $(document).on('click', '[data-action]', this.handleDataAction);
            $(document).on('change', '.hpt-auto-save', this.handleAutoSave);
            
            // Modal events
            $(document).on('click', '[data-modal]', this.openModal);
            $(document).on('click', '.hpt-modal__close, .hpt-modal__backdrop', this.closeModal);
            $(document).on('keydown', this.handleModalKeydown);

            // Form validation
            $(document).on('input', '.hpt-form__input[required]', this.validateInput);
            $(document).on('submit', '.hpt-form', this.validateForm);

            // File uploads
            $(document).on('change', '.hpt-file-upload', this.handleFileUpload);

            // Search and filters
            $(document).on('input', '.hpt-search-input', this.debounce(this.handleSearch, 300));
            $(document).on('change', '.hpt-filter-select', this.handleFilter);
        },

        initializeComponents: function() {
            // Initialize DataTables
            this.initDataTables();
            
            // Initialize charts
            this.initCharts();
            
            // Initialize date pickers
            this.initDatePickers();
            
            // Initialize tooltips
            this.initTooltips();
            
            // Load initial dashboard data
            this.loadDashboardData();
        },

        initDataTables: function() {
            if ($.fn.DataTable) {
                $('.hpt-data-table').each(function() {
                    const $table = $(this);
                    const options = $table.data('table-options') || {};
                    
                    const defaultOptions = {
                        processing: true,
                        responsive: true,
                        pageLength: 25,
                        language: {
                            processing: '<div class="hpt-table-loading">Loading...</div>',
                            emptyTable: 'No data available',
                            zeroRecords: 'No matching records found',
                            search: 'Search:',
                            lengthMenu: 'Show _MENU_ entries',
                            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                            paginate: {
                                first: 'First',
                                last: 'Last',
                                next: 'Next',
                                previous: 'Previous'
                            }
                        }
                    };

                    $table.DataTable($.extend(defaultOptions, options));
                });
            }
        },

        initCharts: function() {
            if (typeof Chart !== 'undefined') {
                // Set global chart defaults
                Chart.defaults.font.family = 'Poppins, -apple-system, BlinkMacSystemFont, sans-serif';
                Chart.defaults.plugins.legend.position = 'top';
                Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(44, 62, 80, 0.9)';
                Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
                Chart.defaults.plugins.tooltip.bodyColor = '#ffffff';
                Chart.defaults.plugins.tooltip.cornerRadius = 6;
                Chart.defaults.scale.grid.color = 'rgba(0, 0, 0, 0.1)';
            }
        },

        initDatePickers: function() {
            // Initialize date pickers if library is available
            $('.hpt-date-picker').each(function() {
                const $input = $(this);
                if ($.fn.datepicker) {
                    $input.datepicker({
                        dateFormat: 'yy-mm-dd',
                        showButtonPanel: true,
                        changeMonth: true,
                        changeYear: true
                    });
                } else {
                    // Fallback to HTML5 date input
                    $input.attr('type', 'date');
                }
            });

            $('.hpt-datetime-picker').each(function() {
                const $input = $(this);
                if ($.fn.datetimepicker) {
                    $input.datetimepicker({
                        dateFormat: 'yy-mm-dd',
                        timeFormat: 'HH:mm:ss'
                    });
                } else {
                    $input.attr('type', 'datetime-local');
                }
            });
        },

        initTooltips: function() {
            // Initialize tooltips
            $('[data-tooltip]').each(function() {
                const $element = $(this);
                const tooltip = $element.data('tooltip');
                
                $element.hover(
                    function() {
                        const $tooltip = $('<div class="hpt-tooltip">' + tooltip + '</div>');
                        $('body').append($tooltip);
                        
                        const offset = $element.offset();
                        const elementHeight = $element.outerHeight();
                        
                        $tooltip.css({
                            top: offset.top + elementHeight + 10,
                            left: offset.left + ($element.outerWidth() / 2) - ($tooltip.outerWidth() / 2)
                        });
                        
                        setTimeout(() => $tooltip.addClass('show'), 10);
                    },
                    function() {
                        $('.hpt-tooltip').remove();
                    }
                );
            });
        },

        loadDashboardData: function() {
            const section = this.getCurrentSection();
            if (section && this[`load${section}Data`]) {
                this[`load${section}Data`]();
            }
        },

        getCurrentSection: function() {
            const path = window.location.pathname;
            const match = path.match(/\/agent-dashboard\/([^\/]+)/);
            return match ? match[1].charAt(0).toUpperCase() + match[1].slice(1) : 'Overview';
        },

        handleNavigation: function(e) {
            e.preventDefault();
            const $link = $(this);
            const href = $link.attr('href');
            
            // Update active state
            $('.hpt-dashboard-nav__item').removeClass('is-active');
            $link.closest('.hpt-dashboard-nav__item').addClass('is-active');
            
            // Load content via AJAX or navigate
            if ($link.data('ajax')) {
                HptDashboard.loadSectionContent($link.data('section'));
            } else {
                window.location.href = href;
            }
        },

        handleAjaxForm: function(e) {
            e.preventDefault();
            const $form = $(this);
            const action = $form.data('action') || 'hpt_dashboard_action';
            
            // Show loading state
            const $submitBtn = $form.find('[type="submit"]');
            const originalText = $submitBtn.text();
            $submitBtn.prop('disabled', true).text('Processing...');

            // Prepare form data
            const formData = new FormData(this);
            formData.append('action', action);
            formData.append('nonce', hptDashboard.nonce);

            // Submit form
            $.ajax({
                url: hptDashboard.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        hptShowNotice('success', response.data.message);
                        
                        // Handle redirect
                        if (response.data.redirect) {
                            setTimeout(() => {
                                window.location.href = response.data.redirect;
                            }, 1500);
                        }
                        
                        // Trigger custom event
                        $form.trigger('hpt:form:success', [response]);
                    } else {
                        hptShowNotice('error', response.data.message || 'An error occurred');
                        $form.trigger('hpt:form:error', [response]);
                    }
                },
                error: function(xhr) {
                    hptShowNotice('error', 'Request failed. Please try again.');
                    $form.trigger('hpt:form:error', [xhr]);
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        handleDataAction: function(e) {
            e.preventDefault();
            const $element = $(this);
            const action = $element.data('action');
            const confirm = $element.data('confirm');
            
            // Confirmation dialog
            if (confirm && !window.confirm(confirm)) {
                return;
            }

            // Show loading state
            const originalHtml = $element.html();
            $element.html('<span class="hpt-spinner"></span>').prop('disabled', true);

            // Prepare data
            const data = {
                action: 'hpt_dashboard_action',
                dashboard_action: action,
                nonce: hptDashboard.nonce
            };

            // Add element data attributes
            $.each($element.data(), function(key, value) {
                if (key !== 'action' && key !== 'confirm') {
                    data[key] = value;
                }
            });

            // Send request
            $.ajax({
                url: hptDashboard.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        hptShowNotice('success', response.data.message);
                        
                        // Handle specific actions
                        if (action === 'delete_listing' || action === 'delete_lead') {
                            $element.closest('tr').fadeOut(() => {
                                $element.closest('tr').remove();
                            });
                        } else if (action === 'toggle_featured') {
                            $element.toggleClass('featured');
                        }
                        
                        // Trigger custom event
                        $element.trigger('hpt:action:success', [response, action]);
                    } else {
                        hptShowNotice('error', response.data.message || 'Action failed');
                        $element.trigger('hpt:action:error', [response, action]);
                    }
                },
                error: function(xhr) {
                    hptShowNotice('error', 'Request failed. Please try again.');
                    $element.trigger('hpt:action:error', [xhr, action]);
                },
                complete: function() {
                    $element.html(originalHtml).prop('disabled', false);
                }
            });
        },

        handleAutoSave: function() {
            const $input = $(this);
            const $form = $input.closest('form');
            
            clearTimeout($form.data('autosave-timeout'));
            
            const timeout = setTimeout(() => {
                const formData = $form.serialize();
                formData += '&action=hpt_autosave&nonce=' + hptDashboard.nonce;
                
                $.post(hptDashboard.ajaxUrl, formData)
                    .done(function(response) {
                        if (response.success) {
                            hptShowNotice('info', 'Draft saved', 2000);
                        }
                    });
            }, 1000);
            
            $form.data('autosave-timeout', timeout);
        },

        openModal: function(e) {
            e.preventDefault();
            const modalId = $(this).data('modal');
            const $modal = $('#' + modalId);
            
            if ($modal.length) {
                $modal.show();
                $('body').addClass('hpt-modal-open');
                
                // Focus first input
                setTimeout(() => {
                    $modal.find('.hpt-form__input:first').focus();
                }, 100);
            }
        },

        closeModal: function(e) {
            if (e.target === this || $(e.target).hasClass('hpt-modal__close')) {
                const $modal = $(this).closest('.hpt-modal');
                $modal.hide();
                $('body').removeClass('hpt-modal-open');
                
                // Reset form
                $modal.find('form')[0]?.reset();
            }
        },

        handleModalKeydown: function(e) {
            if (e.keyCode === 27) { // Escape key
                $('.hpt-modal:visible').hide();
                $('body').removeClass('hpt-modal-open');
            }
        },

        validateInput: function() {
            const $input = $(this);
            const value = $input.val();
            const type = $input.attr('type');
            let isValid = true;
            let message = '';

            // Required validation
            if ($input.prop('required') && !value.trim()) {
                isValid = false;
                message = 'This field is required';
            }

            // Type-specific validation
            if (isValid && value) {
                switch (type) {
                    case 'email':
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(value)) {
                            isValid = false;
                            message = 'Please enter a valid email address';
                        }
                        break;
                    case 'tel':
                        const phoneRegex = /^[\d\s\-\(\)\+\.]+$/;
                        if (!phoneRegex.test(value)) {
                            isValid = false;
                            message = 'Please enter a valid phone number';
                        }
                        break;
                    case 'url':
                        try {
                            new URL(value);
                        } catch {
                            isValid = false;
                            message = 'Please enter a valid URL';
                        }
                        break;
                }
            }

            // Update input state
            $input.toggleClass('error', !isValid);
            
            // Show/hide error message
            let $error = $input.next('.hpt-field-error');
            if (!isValid) {
                if (!$error.length) {
                    $error = $('<div class="hpt-field-error"></div>');
                    $input.after($error);
                }
                $error.text(message);
            } else {
                $error.remove();
            }

            return isValid;
        },

        validateForm: function(e) {
            const $form = $(this);
            let isValid = true;

            // Validate all required fields
            $form.find('.hpt-form__input[required]').each(function() {
                if (!HptDashboard.validateInput.call(this)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                hptShowNotice('error', 'Please correct the errors below');
            }
        },

        handleFileUpload: function() {
            const $input = $(this);
            const files = this.files;
            
            if (files.length > 0) {
                const file = files[0];
                const maxSize = $input.data('max-size') || 5 * 1024 * 1024; // 5MB default
                const allowedTypes = $input.data('allowed-types') || 'image/*';
                
                // Validate file size
                if (file.size > maxSize) {
                    hptShowNotice('error', 'File is too large. Maximum size is ' + (maxSize / 1024 / 1024) + 'MB');
                    $input.val('');
                    return;
                }
                
                // Validate file type
                if (allowedTypes !== '*' && !file.type.match(allowedTypes)) {
                    hptShowNotice('error', 'File type not allowed');
                    $input.val('');
                    return;
                }
                
                // Show preview for images
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const $preview = $input.next('.hpt-image-preview');
                        if ($preview.length) {
                            $preview.find('img').attr('src', e.target.result);
                        } else {
                            $('<div class="hpt-image-preview"><img src="' + e.target.result + '" style="max-width: 200px; max-height: 200px;"></div>')
                                .insertAfter($input);
                        }
                    };
                    reader.readAsDataURL(file);
                }
            }
        },

        handleSearch: function() {
            const $input = $(this);
            const query = $input.val();
            const target = $input.data('search-target');
            
            if (target) {
                HptDashboard.performSearch(query, target);
            }
        },

        handleFilter: function() {
            const $select = $(this);
            const target = $select.data('filter-target');
            const value = $select.val();
            
            if (target) {
                HptDashboard.applyFilter(target, $select.attr('name'), value);
            }
        },

        performSearch: function(query, target) {
            const $target = $(target);
            
            if ($target.length) {
                if ($target.hasClass('hpt-data-table') && $.fn.DataTable) {
                    const table = $target.DataTable();
                    table.search(query).draw();
                } else {
                    // Custom search implementation
                    $target.find('[data-searchable]').each(function() {
                        const $item = $(this);
                        const text = $item.text().toLowerCase();
                        const match = text.includes(query.toLowerCase());
                        $item.toggle(match);
                    });
                }
            }
        },

        applyFilter: function(target, filterName, value) {
            // Implement custom filtering logic based on target
            console.log('Applying filter:', filterName, value, 'to', target);
        },

        setupAutoRefresh: function() {
            // Auto-refresh dashboard data every 5 minutes
            setInterval(() => {
                if (!document.hidden) {
                    this.refreshDashboardData();
                }
            }, 300000);
        },

        refreshDashboardData: function() {
            const section = this.getCurrentSection();
            
            $.ajax({
                url: hptDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hpt_dashboard_data',
                    section: section.toLowerCase(),
                    action_type: 'refresh',
                    nonce: hptDashboard.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Update specific elements with new data
                        $('.hpt-stat-card__value').each(function(index) {
                            const newValue = response.data.stats?.[index];
                            if (newValue !== undefined) {
                                $(this).text(newValue);
                            }
                        });
                    }
                }
            });
        },

        // Listing Form Modal
        showListingForm: function(listingId = null) {
            const modalId = 'listing-form-modal';
            let modal = document.getElementById(modalId);
            
            if (!modal) {
                modal = this.createListingFormModal(modalId, listingId);
                document.body.appendChild(modal);
            }
            
            // Show modal with animation
            $(modal).addClass('hpt-modal--active').fadeIn(300);
            $('body').addClass('hpt-modal-open');
            
            // If editing, load existing data
            if (listingId) {
                this.loadListingData(listingId, modal);
            } else {
                // Clear form for new listing
                const form = modal.querySelector('form');
                if (form) form.reset();
            }
        },

        createListingFormModal: function(modalId, listingId = null) {
            const isEdit = listingId ? true : false;
            
            const modal = document.createElement('div');
            modal.id = modalId;
            modal.className = 'hpt-modal hpt-listing-modal';
            modal.innerHTML = `
                <div class="hpt-modal__backdrop" onclick="HptDashboard.closeListingModal()"></div>
                <div class="hpt-modal__content">
                    <div class="hpt-modal__header">
                        <h3>${isEdit ? 'Edit Listing' : 'Add New Listing'}</h3>
                        <button type="button" class="hpt-modal__close" onclick="HptDashboard.closeListingModal()">&times;</button>
                    </div>
                    <div class="hpt-modal__body">
                        <form id="modal-listing-form" class="hpt-form">
                            <input type="hidden" name="listing_id" value="${listingId || 0}">
                            
                            <div class="hpt-form-tabs">
                                <div class="hpt-form-tab-nav">
                                    <button type="button" class="hpt-tab-btn active" data-tab="basic">Basic Info</button>
                                    <button type="button" class="hpt-tab-btn" data-tab="location">Location</button>
                                    <button type="button" class="hpt-tab-btn" data-tab="financial">Financial</button>
                                </div>
                                
                                <!-- Basic Information Tab -->
                                <div class="hpt-form-tab" id="tab-basic">
                                    <div class="hpt-form-grid">
                                        <div class="hpt-form__group hpt-form__group--full">
                                            <label class="hpt-form__label">Property Title <span class="required">*</span></label>
                                            <input type="text" name="title" class="hpt-form__input" required>
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Price <span class="required">*</span></label>
                                            <input type="number" name="price" class="hpt-form__input" min="0" step="1000" required>
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Listing Status <span class="required">*</span></label>
                                            <select name="listing_status" class="hpt-form__select" required>
                                                <option value="active">Active</option>
                                                <option value="pending">Pending</option>
                                                <option value="sold">Sold</option>
                                                <option value="coming_soon">Coming Soon</option>
                                                <option value="off_market">Off Market</option>
                                            </select>
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Property Type <span class="required">*</span></label>
                                            <select name="property_type" class="hpt-form__select" required>
                                                <option value="single_family">Single Family Home</option>
                                                <option value="condo">Condominium</option>
                                                <option value="townhouse">Townhouse</option>
                                                <option value="duplex">Duplex</option>
                                                <option value="multi_family">Multi-Family</option>
                                                <option value="commercial">Commercial</option>
                                                <option value="land">Land</option>
                                                <option value="mobile_home">Mobile Home</option>
                                            </select>
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">MLS Number</label>
                                            <input type="text" name="mls_number" class="hpt-form__input" placeholder="MLS123456">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Bedrooms</label>
                                            <input type="number" name="bedrooms" class="hpt-form__input" min="0" max="20">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Bathrooms</label>
                                            <input type="number" name="bathrooms" class="hpt-form__input" min="0" max="20" step="0.5">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Square Feet</label>
                                            <input type="number" name="square_feet" class="hpt-form__input" min="0">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Lot Size (sq ft)</label>
                                            <input type="number" name="lot_size" class="hpt-form__input" min="0" placeholder="8000">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Year Built</label>
                                            <input type="number" name="year_built" class="hpt-form__input" min="1800" max="2030" placeholder="2020">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Garage Spaces</label>
                                            <input type="number" name="garage_spaces" class="hpt-form__input" min="0" max="10">
                                        </div>
                                        
                                        <div class="hpt-form__group hpt-form__group--full">
                                            <label class="hpt-form__label">Description</label>
                                            <textarea name="description" class="hpt-form__textarea" rows="4"></textarea>
                                        </div>
                                        
                                        <div class="hpt-form__group hpt-form__group--full">
                                            <label class="hpt-form__checkbox">
                                                <input type="checkbox" name="featured_listing" value="1">
                                                <span class="hpt-form__checkbox-text">Feature this listing</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Location Tab -->
                                <div class="hpt-form-tab" id="tab-location" style="display: none;">
                                    <div class="hpt-form-grid">
                                        <div class="hpt-form__group hpt-form__group--full">
                                            <label class="hpt-form__label">Street Address <span class="required">*</span></label>
                                            <input type="text" name="street_address" class="hpt-form__input" required placeholder="123 Main Street">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">City <span class="required">*</span></label>
                                            <input type="text" name="city" class="hpt-form__input" required placeholder="Austin">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">State <span class="required">*</span></label>
                                            <select name="state" class="hpt-form__select" required>
                                                <option value="">Select State</option>
                                                <option value="AL">Alabama</option>
                                                <option value="AK">Alaska</option>
                                                <option value="AZ">Arizona</option>
                                                <option value="AR">Arkansas</option>
                                                <option value="CA">California</option>
                                                <option value="CO">Colorado</option>
                                                <option value="CT">Connecticut</option>
                                                <option value="DE">Delaware</option>
                                                <option value="FL">Florida</option>
                                                <option value="GA">Georgia</option>
                                                <option value="HI">Hawaii</option>
                                                <option value="ID">Idaho</option>
                                                <option value="IL">Illinois</option>
                                                <option value="IN">Indiana</option>
                                                <option value="IA">Iowa</option>
                                                <option value="KS">Kansas</option>
                                                <option value="KY">Kentucky</option>
                                                <option value="LA">Louisiana</option>
                                                <option value="ME">Maine</option>
                                                <option value="MD">Maryland</option>
                                                <option value="MA">Massachusetts</option>
                                                <option value="MI">Michigan</option>
                                                <option value="MN">Minnesota</option>
                                                <option value="MS">Mississippi</option>
                                                <option value="MO">Missouri</option>
                                                <option value="MT">Montana</option>
                                                <option value="NE">Nebraska</option>
                                                <option value="NV">Nevada</option>
                                                <option value="NH">New Hampshire</option>
                                                <option value="NJ">New Jersey</option>
                                                <option value="NM">New Mexico</option>
                                                <option value="NY">New York</option>
                                                <option value="NC">North Carolina</option>
                                                <option value="ND">North Dakota</option>
                                                <option value="OH">Ohio</option>
                                                <option value="OK">Oklahoma</option>
                                                <option value="OR">Oregon</option>
                                                <option value="PA">Pennsylvania</option>
                                                <option value="RI">Rhode Island</option>
                                                <option value="SC">South Carolina</option>
                                                <option value="SD">South Dakota</option>
                                                <option value="TN">Tennessee</option>
                                                <option value="TX" selected>Texas</option>
                                                <option value="UT">Utah</option>
                                                <option value="VT">Vermont</option>
                                                <option value="VA">Virginia</option>
                                                <option value="WA">Washington</option>
                                                <option value="WV">West Virginia</option>
                                                <option value="WI">Wisconsin</option>
                                                <option value="WY">Wyoming</option>
                                            </select>
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">ZIP Code <span class="required">*</span></label>
                                            <input type="text" name="zip_code" class="hpt-form__input" pattern="[0-9]{5}" maxlength="10" required placeholder="78701">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">County</label>
                                            <input type="text" name="county" class="hpt-form__input" placeholder="Travis County">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Subdivision</label>
                                            <input type="text" name="subdivision" class="hpt-form__input" placeholder="Oak Hill Estates">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Latitude</label>
                                            <input type="number" name="latitude" class="hpt-form__input" min="-90" max="90" step="any" placeholder="30.2672">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Longitude</label>
                                            <input type="number" name="longitude" class="hpt-form__input" min="-180" max="180" step="any" placeholder="-97.7431">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Financial Tab -->
                                <div class="hpt-form-tab" id="tab-financial" style="display: none;">
                                    <div class="hpt-form-grid">
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Property Taxes (Annual)</label>
                                            <input type="number" name="property_taxes" class="hpt-form__input" min="0" placeholder="12000">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Tax Year</label>
                                            <input type="number" name="tax_year" class="hpt-form__input" min="2000" max="2030" placeholder="2024">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">HOA Fees (Monthly)</label>
                                            <input type="number" name="hoa_fees" class="hpt-form__input" min="0" placeholder="150">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">HOA Frequency</label>
                                            <select name="hoa_frequency" class="hpt-form__select">
                                                <option value="monthly">Monthly</option>
                                                <option value="quarterly">Quarterly</option>
                                                <option value="annually">Annually</option>
                                            </select>
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">CDD Fees</label>
                                            <input type="number" name="cdd_fees" class="hpt-form__input" min="0" placeholder="200">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Listing Agent Commission (%)</label>
                                            <input type="number" name="listing_commission" class="hpt-form__input" min="0" max="10" step="0.25" placeholder="3">
                                        </div>
                                        
                                        <div class="hpt-form__group">
                                            <label class="hpt-form__label">Buyer Agent Commission (%)</label>
                                            <input type="number" name="buyer_commission" class="hpt-form__input" min="0" max="10" step="0.25" placeholder="3">
                                        </div>
                                        
                                        <div class="hpt-form__group hpt-form__group--full">
                                            <label class="hpt-form__checkbox">
                                                <input type="checkbox" name="homestead_exempt" value="1">
                                                <span class="hpt-form__checkbox-text">Has Homestead Exemption</span>
                                            </label>
                                        </div>
                                        
                                        <div class="hpt-form__group hpt-form__group--full">
                                            <label class="hpt-form__label">Financing Available</label>
                                            <div class="hpt-form__checkbox-group">
                                                <label class="hpt-form__checkbox">
                                                    <input type="checkbox" name="financing_available[]" value="conventional" checked>
                                                    <span class="hpt-form__checkbox-text">Conventional Loan</span>
                                                </label>
                                                <label class="hpt-form__checkbox">
                                                    <input type="checkbox" name="financing_available[]" value="fha" checked>
                                                    <span class="hpt-form__checkbox-text">FHA Loan</span>
                                                </label>
                                                <label class="hpt-form__checkbox">
                                                    <input type="checkbox" name="financing_available[]" value="va" checked>
                                                    <span class="hpt-form__checkbox-text">VA Loan</span>
                                                </label>
                                                <label class="hpt-form__checkbox">
                                                    <input type="checkbox" name="financing_available[]" value="usda">
                                                    <span class="hpt-form__checkbox-text">USDA Loan</span>
                                                </label>
                                                <label class="hpt-form__checkbox">
                                                    <input type="checkbox" name="financing_available[]" value="cash" checked>
                                                    <span class="hpt-form__checkbox-text">Cash Only</span>
                                                </label>
                                                <label class="hpt-form__checkbox">
                                                    <input type="checkbox" name="financing_available[]" value="owner_finance">
                                                    <span class="hpt-form__checkbox-text">Owner Financing</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="hpt-form__actions">
                                <button type="submit" class="hpt-button hpt-button--primary">
                                    ${isEdit ? 'Update Listing' : 'Create Listing'}
                                </button>
                                <button type="button" onclick="HptDashboard.closeListingModal()" class="hpt-button hpt-button--secondary">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            // Add form submit handler
            const form = modal.querySelector('#modal-listing-form');
            $(form).on('submit', function(e) {
                e.preventDefault();
                HptDashboard.saveListingFromModal(this);
            });
            
            // Add tab functionality
            const tabButtons = modal.querySelectorAll('.hpt-tab-btn');
            const tabPanes = modal.querySelectorAll('.hpt-form-tab');
            
            tabButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetTab = this.dataset.tab;
                    
                    // Remove active class from all buttons and tabs
                    tabButtons.forEach(b => b.classList.remove('active'));
                    tabPanes.forEach(p => p.style.display = 'none');
                    
                    // Add active class to clicked button and show corresponding tab
                    this.classList.add('active');
                    document.getElementById(`tab-${targetTab}`).style.display = 'block';
                });
            });
            
            return modal;
        },

        loadListingData: function(listingId, modal) {
            $.post(hptDashboard.ajaxUrl, {
                action: 'hpt_dashboard_action',
                dashboard_action: 'get_listing_data',
                listing_id: listingId,
                nonce: hptDashboard.nonce
            }, function(response) {
                if (response.success && response.data) {
                    const form = modal.querySelector('form');
                    const data = response.data;
                    
                    // Fill basic form fields
                    $(form).find('input[name="title"]').val(data.title || '');
                    $(form).find('input[name="price"]').val(data.price || '');
                    $(form).find('select[name="listing_status"]').val(data.listing_status || 'active');
                    $(form).find('select[name="property_type"]').val(data.property_type || 'single_family');
                    $(form).find('input[name="mls_number"]').val(data.mls_number || '');
                    $(form).find('input[name="bedrooms"]').val(data.bedrooms || '');
                    $(form).find('input[name="bathrooms"]').val(data.bathrooms || '');
                    $(form).find('input[name="square_feet"]').val(data.square_feet || '');
                    $(form).find('input[name="lot_size"]').val(data.lot_size || '');
                    $(form).find('input[name="year_built"]').val(data.year_built || '');
                    $(form).find('input[name="garage_spaces"]').val(data.garage_spaces || '');
                    $(form).find('textarea[name="description"]').val(data.description || '');
                    $(form).find('input[name="featured_listing"]').prop('checked', data.featured_listing || false);
                    
                    // Fill location fields
                    $(form).find('input[name="street_address"]').val(data.street_address || '');
                    $(form).find('input[name="city"]').val(data.city || '');
                    $(form).find('select[name="state"]').val(data.state || 'TX');
                    $(form).find('input[name="zip_code"]').val(data.zip_code || '');
                    $(form).find('input[name="county"]').val(data.county || '');
                    $(form).find('input[name="subdivision"]').val(data.subdivision || '');
                    $(form).find('input[name="latitude"]').val(data.latitude || '');
                    $(form).find('input[name="longitude"]').val(data.longitude || '');
                    
                    // Fill financial fields
                    $(form).find('input[name="property_taxes"]').val(data.property_taxes || '');
                    $(form).find('input[name="tax_year"]').val(data.tax_year || '');
                    $(form).find('input[name="hoa_fees"]').val(data.hoa_fees || '');
                    $(form).find('select[name="hoa_frequency"]').val(data.hoa_frequency || 'monthly');
                    $(form).find('input[name="cdd_fees"]').val(data.cdd_fees || '');
                    $(form).find('input[name="listing_commission"]').val(data.listing_commission || '');
                    $(form).find('input[name="buyer_commission"]').val(data.buyer_commission || '');
                    $(form).find('input[name="homestead_exempt"]').prop('checked', data.homestead_exempt || false);
                    
                    // Handle financing available checkboxes
                    if (data.financing_available) {
                        const financing = Array.isArray(data.financing_available) ? data.financing_available : data.financing_available.split(',');
                        $(form).find('input[name="financing_available[]"]').each(function() {
                            $(this).prop('checked', financing.includes($(this).val()));
                        });
                    }
                    
                    // Update modal title
                    modal.querySelector('.hpt-modal__header h3').textContent = 'Edit Listing';
                }
            }).fail(function() {
                console.error('Failed to load listing data');
            });
        },

        saveListingFromModal: function(form) {
            const formData = $(form).serialize();
            const submitBtn = $(form).find('button[type="submit"]');
            const originalText = submitBtn.text();
            
            submitBtn.prop('disabled', true).text('Saving...');
            
            $.post(hptDashboard.ajaxUrl, 
                formData + '&action=hpt_dashboard_action&dashboard_action=save_listing&nonce=' + hptDashboard.nonce,
                function(response) {
                    if (response.success) {
                        // Show success notification
                        HptDashboard.showNotification('success', response.data.message);
                        
                        // Close modal
                        HptDashboard.closeListingModal();
                        
                        // Refresh listings table if it exists
                        if (window.listingsTable) {
                            window.listingsTable.ajax.reload(null, false);
                        } else if ($('#listings-table').length) {
                            location.reload();
                        }
                        
                        // Redirect if specified
                        if (response.data.redirect) {
                            setTimeout(() => {
                                window.location.href = response.data.redirect;
                            }, 1500);
                        }
                    } else {
                        HptDashboard.showNotification('error', response.data.message);
                    }
                }
            ).fail(function() {
                HptDashboard.showNotification('error', 'An error occurred. Please try again.');
            }).always(function() {
                submitBtn.prop('disabled', false).text(originalText);
            });
        },

        closeListingModal: function() {
            const modal = document.getElementById('listing-form-modal');
            if (modal) {
                $(modal).removeClass('hpt-modal--active').fadeOut(300, function() {
                    modal.remove();
                });
                $('body').removeClass('hpt-modal-open');
            }
        },

        showNotification: function(type, message) {
            // Use the global notification function if available
            if (typeof window.hptShowNotice === 'function') {
                window.hptShowNotice(type, message);
            } else {
                // Fallback notification
                alert(message);
            }
        },

        // Utility functions
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
        },

        formatPrice: function(price) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 0
            }).format(price);
        },

        formatNumber: function(number) {
            return new Intl.NumberFormat('en-US').format(number);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        if ($('.hpt-dashboard').length > 0) {
            HptDashboard.init();
        }
    });

    // Make showListingForm globally available
    window.showListingForm = function(listingId = null) {
        HptDashboard.showListingForm(listingId);
    };

    // Add modal CSS if not already present
    if (!document.getElementById('hpt-modal-styles')) {
        const style = document.createElement('style');
        style.id = 'hpt-modal-styles';
        style.textContent = `
            .hpt-modal { 
                display: none; 
                position: fixed; 
                top: 0; 
                left: 0; 
                width: 100%; 
                height: 100%; 
                z-index: 9999; 
                align-items: center; 
                justify-content: center;
            }
            .hpt-modal--active { 
                display: flex !important; 
            }
            .hpt-modal__backdrop { 
                position: absolute; 
                top: 0; 
                left: 0; 
                width: 100%; 
                height: 100%; 
                background: rgba(0, 0, 0, 0.7); 
            }
            .hpt-modal__content { 
                position: relative; 
                background: white; 
                border-radius: 8px; 
                max-width: 800px; 
                width: 90%; 
                max-height: 90vh; 
                overflow-y: auto; 
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            }
            .hpt-modal__header { 
                display: flex; 
                justify-content: space-between; 
                align-items: center; 
                padding: 20px; 
                border-bottom: 1px solid #ddd; 
                background: #f9f9f9; 
                border-radius: 8px 8px 0 0;
            }
            .hpt-modal__header h3 { 
                margin: 0; 
                font-size: 18px;
            }
            .hpt-modal__close { 
                background: none; 
                border: none; 
                font-size: 24px; 
                cursor: pointer; 
                padding: 0; 
                width: 30px; 
                height: 30px; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
            }
            .hpt-modal__body { 
                padding: 20px; 
            }
            .hpt-form-grid { 
                display: grid; 
                grid-template-columns: 1fr 1fr; 
                gap: 20px; 
            }
            .hpt-form__group--full { 
                grid-column: 1 / -1; 
            }
            .hpt-form__label { 
                display: block; 
                margin-bottom: 5px; 
                font-weight: 500; 
            }
            .hpt-form__input, .hpt-form__select, .hpt-form__textarea { 
                width: 100%; 
                padding: 8px 12px; 
                border: 1px solid #ddd; 
                border-radius: 4px; 
                font-size: 14px;
                box-sizing: border-box;
            }
            .hpt-form__actions { 
                margin-top: 20px; 
                display: flex; 
                gap: 10px; 
                justify-content: flex-end;
            }
            .hpt-button { 
                padding: 10px 20px; 
                border: none; 
                border-radius: 4px; 
                cursor: pointer; 
                font-size: 14px; 
                font-weight: 500;
            }
            .hpt-button--primary { 
                background: #0073aa; 
                color: white; 
            }
            .hpt-button--secondary { 
                background: #f1f1f1; 
                color: #555; 
                border: 1px solid #ddd;
            }
            .hpt-button:hover { 
                opacity: 0.9; 
            }
            .hpt-button:disabled { 
                opacity: 0.6; 
                cursor: not-allowed; 
            }
            .required { 
                color: #dc3545; 
            }
            .hpt-form__checkbox { 
                display: flex; 
                align-items: center; 
                gap: 8px; 
            }
            .hpt-form__checkbox-group { 
                display: grid; 
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
                gap: 10px; 
                margin-top: 10px;
            }
            .hpt-form-tabs {
                width: 100%;
            }
            .hpt-form-tab-nav {
                display: flex;
                border-bottom: 1px solid #ddd;
                margin-bottom: 20px;
            }
            .hpt-tab-btn {
                background: none;
                border: none;
                padding: 12px 20px;
                cursor: pointer;
                border-bottom: 3px solid transparent;
                font-size: 14px;
                font-weight: 500;
                color: #666;
                transition: all 0.3s ease;
            }
            .hpt-tab-btn:hover {
                background-color: #f5f5f5;
                color: #333;
            }
            .hpt-tab-btn.active {
                color: #0073aa;
                border-bottom-color: #0073aa;
                background-color: #f9f9f9;
            }
            .hpt-form-tab {
                width: 100%;
            }
            .hpt-modal-open { 
                overflow: hidden; 
            }
            @media (max-width: 768px) {
                .hpt-form-grid {
                    grid-template-columns: 1fr;
                }
                .hpt-modal__content {
                    width: 95%;
                    max-height: 95vh;
                }
                .hpt-modal__header, .hpt-modal__body {
                    padding: 15px;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Make functions globally available for buttons and external calls
    window.showListingForm = function(listingId = null) {
        return HptDashboard.showListingForm(listingId);
    };
    
    window.HptDashboard = HptDashboard;

})(jQuery);