/**
 * CSV Import Admin Interface
 * Handles the frontend for CSV imports with field mapping
 * 
 * @package HappyPlace
 */

jQuery(document).ready(function($) {
    'use strict';

    // CSV Import Handler
    const CSVImport = {
        
        // Properties
        currentFile: null,
        currentMapping: {},
        templates: {},
        progressInterval: null,
        
        // Initialize
        init: function() {
            this.bindEvents();
            this.loadTemplates();
        },
        
        // Bind event handlers
        bindEvents: function() {
            // File upload
            $(document).on('change', '#csv-file-input', this.handleFileUpload.bind(this));
            $(document).on('click', '#upload-button', this.triggerFileUpload.bind(this));
            
            // Drag and drop
            $(document).on('dragover', '.csv-upload-area', this.handleDragOver.bind(this));
            $(document).on('drop', '.csv-upload-area', this.handleFileDrop.bind(this));
            
            // Field mapping
            $(document).on('change', '.field-mapping-select', this.handleMappingChange.bind(this));
            $(document).on('click', '.auto-map-btn', this.autoMapFields.bind(this));
            
            // Template management
            $(document).on('change', '#mapping-template-select', this.loadTemplate.bind(this));
            $(document).on('click', '#save-template-btn', this.saveTemplate.bind(this));
            
            // Import process
            $(document).on('click', '#start-import-btn', this.startImport.bind(this));
            $(document).on('click', '#cancel-import-btn', this.cancelImport.bind(this));
            
            // Navigation
            $(document).on('click', '.step-nav-btn', this.navigateStep.bind(this));
        },
        
        // Trigger file upload dialog
        triggerFileUpload: function(e) {
            e.preventDefault();
            $('#csv-file-input').click();
        },
        
        // Handle file upload
        handleFileUpload: function(e) {
            const file = e.target.files[0];
            if (file) {
                this.processFile(file);
            }
        },
        
        // Handle drag over
        handleDragOver: function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(e.currentTarget).addClass('drag-over');
        },
        
        // Handle file drop
        handleFileDrop: function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(e.currentTarget).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                this.processFile(files[0]);
            }
        },
        
        // Process uploaded file
        processFile: function(file) {
            // Validate file type
            if (!file.name.toLowerCase().endsWith('.csv')) {
                this.showError('Please select a CSV file.');
                return;
            }
            
            // Validate file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                this.showError('File size must be less than 10MB.');
                return;
            }
            
            this.showLoading('Uploading and validating CSV file...');
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'hp_upload_import_file');
            formData.append('nonce', hpImport.nonce);
            formData.append('csv_file', file);
            
            // Upload file
            $.ajax({
                url: hpImport.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: this.handleUploadSuccess.bind(this),
                error: this.handleUploadError.bind(this)
            });
        },
        
        // Handle successful upload
        handleUploadSuccess: function(response) {
            this.hideLoading();
            
            if (response.success) {
                this.currentFile = response.data.file_path;
                this.showMappingInterface(response.data);
                this.navigateToStep(2);
            } else {
                this.showError(response.data.message || 'Upload failed');
            }
        },
        
        // Handle upload error
        handleUploadError: function(xhr, status, error) {
            this.hideLoading();
            this.showError('Upload failed: ' + error);
        },
        
        // Show field mapping interface
        showMappingInterface: function(data) {
            const { headers, suggested_mapping, mapping_templates } = data;
            
            // Update templates dropdown
            this.updateTemplatesDropdown(mapping_templates);
            
            // Build mapping interface
            let html = '<div class="field-mapping-container">';
            html += '<div class="mapping-header">';
            html += '<h3>Map CSV Fields to Listing Fields</h3>';
            html += '<p>Select how CSV columns should be mapped to listing fields.</p>';
            html += '</div>';
            
            html += '<div class="mapping-grid">';
            html += '<div class="mapping-grid-header">';
            html += '<span>CSV Column</span>';
            html += '<span>Sample Data</span>';
            html += '<span>Maps To</span>';
            html += '<span>Actions</span>';
            html += '</div>';
            
            headers.forEach((header, index) => {
                const suggested = suggested_mapping[index] || {};
                const suggestedField = suggested.listing_field || '';
                const isAutoMapped = suggested.suggested || false;
                
                html += '<div class="mapping-row" data-index="' + index + '">';
                html += '<div class="csv-column">';
                html += '<strong>' + this.escapeHtml(header) + '</strong>';
                html += '</div>';
                html += '<div class="sample-data">';
                html += '<span class="sample-placeholder">Loading...</span>';
                html += '</div>';
                html += '<div class="field-select">';
                html += this.buildFieldSelect(index, suggestedField, isAutoMapped);
                html += '</div>';
                html += '<div class="field-actions">';
                if (isAutoMapped) {
                    html += '<span class="auto-mapped-badge">Auto-mapped</span>';
                }
                html += '</div>';
                html += '</div>';
            });
            
            html += '</div>';
            html += '</div>';
            
            $('#mapping-interface').html(html);
            
            // Bind mapping change events
            $(document).on('change', '.field-mapping-select', this.handleMappingChange.bind(this));
            
            // Load sample data
            this.loadSampleData();
            
            // Initial button state update
            this.updateImportButton();
        },
        
        // Build field select dropdown
        buildFieldSelect: function(index, selectedValue, isAutoMapped) {
            const fields = [
                { value: '', label: '-- Do not import --' },
                
                // Basic Information
                { value: 'post_title', label: 'Listing Title' },
                { value: 'post_content', label: 'Description (Post Content)' },
                { value: 'property_description', label: 'Property Description' },
                { value: 'property_title', label: 'Marketing Title' },
                { value: 'property_highlights', label: 'Property Highlights' },
                
                // Pricing & Financial
                { value: 'price', label: 'Price' },
                { value: 'property_taxes', label: 'Property Taxes' },
                { value: 'hoa_fees', label: 'HOA Fees' },
                { value: 'buyer_commission', label: 'Buyer Commission' },
                { value: 'estimated_insurance', label: 'Estimated Insurance' },
                { value: 'estimated_utilities', label: 'Estimated Utilities' },
                
                // Property Details
                { value: 'bedrooms', label: 'Bedrooms' },
                { value: 'bathrooms_full', label: 'Full Bathrooms' },
                { value: 'bathrooms_half', label: 'Half Bathrooms' },
                { value: 'square_feet', label: 'Square Feet' },
                { value: 'lot_size_acres', label: 'Lot Size (Acres)' },
                { value: 'lot_size_sqft', label: 'Lot Size (Square Feet)' },
                { value: 'year_built', label: 'Year Built' },
                { value: 'property_type', label: 'Property Type' },
                
                // Address Fields
                { value: 'street_number', label: 'Street Number' },
                { value: 'street_name', label: 'Street Name' },
                { value: 'street_type', label: 'Street Type (St, Ave, etc.)' },
                { value: 'city', label: 'City' },
                { value: 'state', label: 'State' },
                { value: 'zip_code', label: 'ZIP Code' },
                { value: 'county', label: 'County' },
                { value: 'parcel_number', label: 'Parcel Number' },
                
                // Listing Information
                { value: 'mls_number', label: 'MLS Number' },
                { value: 'status', label: 'Status' },
                { value: 'listing_date', label: 'Listing Date' },
                { value: 'sold_date', label: 'Sold Date' },
                { value: 'days_on_market', label: 'Days on Market' },
                
                // Location Data
                { value: 'latitude', label: 'Latitude' },
                { value: 'longitude', label: 'Longitude' },
                
                // Additional Info
                { value: 'showing_instructions', label: 'Showing Instructions' },
                { value: 'internal_notes', label: 'Internal Notes' }
            ];
            
            let html = '<select class="field-mapping-select" data-index="' + index + '"';
            if (isAutoMapped) html += ' data-auto-mapped="true"';
            html += '>';
            
            fields.forEach(field => {
                const selected = field.value === selectedValue ? ' selected' : '';
                html += '<option value="' + field.value + '"' + selected + '>' + field.label + '</option>';
            });
            
            html += '</select>';
            return html;
        },
        
        // Load sample data for preview
        loadSampleData: function() {
            if (!this.currentFile) return;
            
            $.ajax({
                url: hpImport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hp_get_csv_sample',
                    nonce: hpImport.nonce,
                    file_path: this.currentFile
                },
                success: function(response) {
                    if (response.success && response.data.sample) {
                        response.data.sample.forEach((row, rowIndex) => {
                            if (rowIndex === 0) { // First data row
                                row.forEach((value, colIndex) => {
                                    const $row = $('.mapping-row[data-index="' + colIndex + '"]');
                                    $row.find('.sample-data .sample-placeholder').text(
                                        value ? this.truncateText(value, 30) : '(empty)'
                                    );
                                });
                            }
                        });
                    }
                }.bind(this),
                error: function() {
                    $('.sample-placeholder').text('Unable to load sample');
                }
            });
        },
        
        // Handle mapping change
        handleMappingChange: function(e) {
            const $select = $(e.target);
            const index = $select.data('index');
            const value = $select.val();
            
            // Update current mapping
            this.currentMapping[index] = {
                csv_header: $('.mapping-row[data-index="' + index + '"] .csv-column strong').text(),
                listing_field: value
            };
            
            // Remove auto-mapped badge if user changes
            if ($select.data('auto-mapped') && !$select.hasClass('user-changed')) {
                $select.addClass('user-changed');
                $select.closest('.mapping-row').find('.auto-mapped-badge').remove();
            }
            
            // Update import button state
            this.updateImportButton();
        },
        
        // Auto-map all fields
        autoMapFields: function(e) {
            e.preventDefault();
            
            $.ajax({
                url: hpImport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hp_auto_map_fields',
                    nonce: hpImport.nonce,
                    file_path: this.currentFile
                },
                success: function(response) {
                    if (response.success) {
                        // Update all selects with auto-mapped values
                        Object.keys(response.data.mapping).forEach(index => {
                            const mapping = response.data.mapping[index];
                            const $select = $('.field-mapping-select[data-index="' + index + '"]');
                            if (mapping.listing_field) {
                                $select.val(mapping.listing_field);
                                $select.trigger('change');
                            }
                        });
                        this.showSuccess('Fields auto-mapped successfully');
                    }
                }.bind(this),
                error: function() {
                    this.showError('Auto-mapping failed');
                }.bind(this)
            });
        },
        
        // Load mapping template
        loadTemplate: function(e) {
            const templateName = $(e.target).val();
            if (!templateName || !this.templates[templateName]) {
                return;
            }
            
            const template = this.templates[templateName];
            const mapping = template.mapping;
            
            // Apply template mapping
            Object.keys(mapping).forEach(index => {
                const $select = $('.field-mapping-select[data-index="' + index + '"]');
                if ($select.length && mapping[index].listing_field) {
                    $select.val(mapping[index].listing_field);
                    $select.trigger('change');
                }
            });
            
            this.showSuccess('Template "' + templateName + '" applied');
        },
        
        // Save mapping template
        saveTemplate: function(e) {
            e.preventDefault();
            
            const templateName = prompt('Enter a name for this mapping template:');
            if (!templateName) {
                return;
            }
            
            $.ajax({
                url: hpImport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hp_save_mapping_template',
                    nonce: hpImport.nonce,
                    template_name: templateName,
                    mapping: this.currentMapping
                },
                success: function(response) {
                    if (response.success) {
                        this.showSuccess('Template saved successfully');
                        this.loadTemplates(); // Refresh templates
                    } else {
                        this.showError(response.data.message || 'Failed to save template');
                    }
                }.bind(this),
                error: function() {
                    this.showError('Failed to save template');
                }.bind(this)
            });
        },
        
        // Start import process
        startImport: function(e) {
            e.preventDefault();
            
            if (!this.validateMapping()) {
                return;
            }
            
            this.navigateToStep(3);
            this.showLoading('Starting import process...');
            
            $.ajax({
                url: hpImport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hp_process_import',
                    nonce: hpImport.nonce,
                    file_path: this.currentFile,
                    mapping: this.currentMapping
                },
                success: this.handleImportStart.bind(this),
                error: this.handleImportError.bind(this)
            });
        },
        
        // Handle import start
        handleImportStart: function(response) {
            this.hideLoading();
            
            if (response.success) {
                const sessionId = response.data.session_id;
                this.startProgressTracking(sessionId);
                this.showProgress(response.data);
            } else {
                this.showError(response.data.message || 'Import failed to start');
            }
        },
        
        // Handle import error
        handleImportError: function(xhr, status, error) {
            this.hideLoading();
            this.showError('Import failed: ' + error);
        },
        
        // Start progress tracking
        startProgressTracking: function(sessionId) {
            this.progressInterval = setInterval(() => {
                this.checkProgress(sessionId);
            }, 2000);
        },
        
        // Check import progress
        checkProgress: function(sessionId) {
            $.ajax({
                url: hpImport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hp_get_import_progress',
                    nonce: hpImport.nonce,
                    session_id: sessionId
                },
                success: this.updateProgress.bind(this),
                error: function() {
                    // Continue checking unless too many errors
                }
            });
        },
        
        // Update progress display
        updateProgress: function(response) {
            if (response.success) {
                const data = response.data;
                const percent = data.progress_percent || 0;
                
                // Update progress bar
                $('.progress-bar-fill').css('width', percent + '%');
                $('.progress-text').text(Math.round(percent) + '%');
                $('.progress-details').text(
                    `${data.processed || 0} of ${data.total_rows || 0} records processed`
                );
                
                // Check if completed
                if (data.status === 'completed') {
                    clearInterval(this.progressInterval);
                    this.showImportResults(data.results);
                }
            }
        },
        
        // Show import results
        showImportResults: function(results) {
            let html = '<div class="import-results">';
            html += '<h3>Import Complete!</h3>';
            html += '<div class="results-summary">';
            html += '<div class="result-item success"><span class="count">' + (results.success || 0) + '</span> Successful</div>';
            html += '<div class="result-item failed"><span class="count">' + (results.failed || 0) + '</span> Failed</div>';
            html += '<div class="result-item skipped"><span class="count">' + (results.skipped || 0) + '</span> Skipped</div>';
            html += '</div>';
            
            if (results.errors && results.errors.length > 0) {
                html += '<div class="import-errors">';
                html += '<h4>Errors:</h4>';
                html += '<div class="error-list">';
                results.errors.slice(0, 10).forEach(error => {
                    html += '<div class="error-item">Row ' + error.row + ': ' + error.error + '</div>';
                });
                if (results.errors.length > 10) {
                    html += '<div class="error-item">... and ' + (results.errors.length - 10) + ' more errors</div>';
                }
                html += '</div>';
                html += '</div>';
            }
            
            html += '<div class="results-actions">';
            html += '<button class="button button-primary" onclick="location.reload()">Import Another File</button>';
            html += '<a href="' + hpImport.listingsUrl + '" class="button">View Listings</a>';
            html += '</div>';
            html += '</div>';
            
            $('#import-results').html(html).show();
            $('#import-progress').hide();
        },
        
        // Validate mapping
        validateMapping: function() {
            const hasValidMapping = Object.values(this.currentMapping).some(mapping => 
                mapping.listing_field && mapping.listing_field !== ''
            );
            
            if (!hasValidMapping) {
                this.showError('Please map at least one field before importing.');
                return false;
            }
            
            return true;
        },
        
        // Navigate to step
        navigateToStep: function(step) {
            $('.import-step').removeClass('active');
            $('#step-' + step).addClass('active');
            
            $('.step-indicator').removeClass('active completed');
            for (let i = 1; i < step; i++) {
                $('#step-indicator-' + i).addClass('completed');
            }
            $('#step-indicator-' + step).addClass('active');
        },
        
        // Navigate step (button handler)
        navigateStep: function(e) {
            e.preventDefault();
            const step = parseInt($(e.target).data('step'));
            this.navigateToStep(step);
        },
        
        // Update templates dropdown
        updateTemplatesDropdown: function(templates) {
            this.templates = templates;
            
            let html = '<option value="">Select a template...</option>';
            Object.keys(templates).forEach(name => {
                html += '<option value="' + name + '">' + this.escapeHtml(name) + '</option>';
            });
            
            $('#mapping-template-select').html(html);
        },
        
        // Load templates
        loadTemplates: function() {
            $.ajax({
                url: hpImport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hp_get_mapping_templates',
                    nonce: hpImport.nonce
                },
                success: function(response) {
                    if (response.success) {
                        this.updateTemplatesDropdown(response.data.templates);
                    }
                }.bind(this)
            });
        },
        
        // Update import button state
        updateImportButton: function() {
            const hasMapping = Object.keys(this.currentMapping).length > 0;
            $('#start-import-btn').prop('disabled', !hasMapping);
        },
        
        // Utility functions
        showLoading: function(message) {
            $('#loading-overlay').find('.loading-message').text(message);
            $('#loading-overlay').show();
        },
        
        hideLoading: function() {
            $('#loading-overlay').hide();
        },
        
        showError: function(message) {
            this.showNotice(message, 'error');
        },
        
        showSuccess: function(message) {
            this.showNotice(message, 'success');
        },
        
        showNotice: function(message, type) {
            const html = '<div class="notice notice-' + type + ' is-dismissible"><p>' + 
                        this.escapeHtml(message) + '</p></div>';
            $('#import-notices').html(html);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                $('#import-notices').empty();
            }, 5000);
        },
        
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        truncateText: function(text, maxLength) {
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        }
    };
    
    // Initialize if on import page
    if ($('#csv-import-interface').length) {
        CSVImport.init();
    }
});
