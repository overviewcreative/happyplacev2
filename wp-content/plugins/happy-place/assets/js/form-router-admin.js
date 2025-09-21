/**
 * Form Router Admin JavaScript
 * 
 * Handles admin interface interactions for the Form Router configuration
 */

jQuery(document).ready(function($) {
    
    // Modal handling
    function openModal(modalId) {
        $('#' + modalId).show();
    }
    
    function closeModal(modalId) {
        $('#' + modalId).hide();
    }
    
    // Close modals when clicking outside or on close button
    $(document).on('click', '.hph-modal', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    $(document).on('click', '.hph-modal-close', function() {
        $(this).closest('.hph-modal').hide();
    });
    
    // Route configuration
    let conditionIndex = 1;
    
    // Add new route
    $('#hph-add-route').on('click', function() {
        // Reset form
        $('#hph-route-form')[0].reset();
        $('#hph-route-id').val('');
        $('#hph-modal-title').text('Add New Route');
        openModal('hph-route-modal');
    });
    
    // Edit existing route
    $(document).on('click', '.hph-edit-route', function() {
        const routeId = $(this).data('route-id');
        const routes = hphFormRouter.routes;
        
        if (routes[routeId]) {
            const route = routes[routeId];
            
            // Populate form
            $('#hph-route-id').val(routeId);
            $('#hph-route-name').val(route.name || '');
            $('#hph-route-description').val(route.description || '');
            $('#hph-route-priority').val(route.priority || 10);
            $('#hph-route-enabled').prop('checked', route.enabled !== false);
            
            // Populate conditions
            $('#hph-route-conditions').empty();
            if (route.conditions && route.conditions.length > 0) {
                route.conditions.forEach(function(condition, index) {
                    addConditionRow(condition, index);
                });
                conditionIndex = route.conditions.length;
            } else {
                addConditionRow({}, 0);
                conditionIndex = 1;
            }
            
            // Populate actions
            if (route.actions) {
                $('input[name="actions[database][enabled]"]').prop('checked', route.actions.database?.enabled || false);
                $('select[name="actions[database][table]"]').val(route.actions.database?.table || 'wp_hp_leads');
                
                $('input[name="actions[email][enabled]"]').prop('checked', route.actions.email?.enabled || false);
                $('input[name="actions[email][admin_email]"]').val(route.actions.email?.admin_email || '');
                $('input[name="actions[email][auto_responder]"]').prop('checked', route.actions.email?.auto_responder || false);
                
                $('input[name="actions[calendly][enabled]"]').prop('checked', route.actions.calendly?.enabled || false);
                $('select[name="actions[calendly][calendar_type]"]').val(route.actions.calendly?.calendar_type || 'consultation');
                
                $('input[name="actions[followup_boss][enabled]"]').prop('checked', route.actions.followup_boss?.enabled || false);
                $('input[name="actions[followup_boss][source]"]').val(route.actions.followup_boss?.source || 'Website Form');
            }
            
            $('#hph-modal-title').text('Edit Route: ' + (route.name || routeId));
            openModal('hph-route-modal');
        }
    });
    
    // Add condition
    $('#hph-add-condition').on('click', function() {
        addConditionRow({}, conditionIndex);
        conditionIndex++;
    });
    
    // Remove condition
    $(document).on('click', '.hph-remove-condition', function() {
        $(this).closest('.hph-condition-item').remove();
    });
    
    function addConditionRow(condition, index) {
        const conditionHtml = `
            <div class="hph-condition-item">
                <select name="conditions[${index}][field]">
                    <option value="form_type" ${condition.field === 'form_type' ? 'selected' : ''}>Form Type</option>
                    <option value="form_id" ${condition.field === 'form_id' ? 'selected' : ''}>Form ID</option>
                    <option value="source_url" ${condition.field === 'source_url' ? 'selected' : ''}>Source URL</option>
                    <option value="custom_field" ${condition.field === 'custom_field' ? 'selected' : ''}>Custom Field</option>
                </select>
                
                <select name="conditions[${index}][operator]">
                    <option value="equals" ${condition.operator === 'equals' ? 'selected' : ''}>Equals</option>
                    <option value="contains" ${condition.operator === 'contains' ? 'selected' : ''}>Contains</option>
                    <option value="starts_with" ${condition.operator === 'starts_with' ? 'selected' : ''}>Starts with</option>
                    <option value="regex" ${condition.operator === 'regex' ? 'selected' : ''}>Matches regex</option>
                </select>
                
                <input type="text" name="conditions[${index}][value]" value="${condition.value || ''}" placeholder="Value" />
                
                <button type="button" class="button hph-remove-condition">Remove</button>
            </div>
        `;
        $('#hph-route-conditions').append(conditionHtml);
    }
    
    // Save route
    $('#hph-save-route').on('click', function() {
        const formData = new FormData(document.getElementById('hph-route-form'));
        
        // Convert FormData to regular object for easier handling
        const routeData = {};
        for (let [key, value] of formData.entries()) {
            setNestedProperty(routeData, key, value);
        }
        
        // Handle checkboxes that might not be in FormData if unchecked
        routeData.enabled = $('#hph-route-enabled').prop('checked');
        
        $.ajax({
            url: hphFormRouter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'hph_save_route_config',
                route_id: routeData.route_id || 'new_route_' + Date.now(),
                route_data: routeData,
                nonce: hphFormRouter.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Route saved successfully!');
                    closeModal('hph-route-modal');
                    location.reload(); // Refresh to show updated routes
                } else {
                    alert('Error: ' + (response.data?.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Network error occurred while saving route.');
            }
        });
    });
    
    // Test route
    $(document).on('click', '.hph-test-route', function() {
        const routeId = $(this).data('route-id');
        
        $.ajax({
            url: hphFormRouter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'hph_test_route',
                route_id: routeId,
                test_data: {
                    first_name: 'Test',
                    last_name: 'User',
                    email: 'test@example.com',
                    form_type: 'contact'
                },
                nonce: hphFormRouter.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Route test completed successfully!');
                } else {
                    alert('Route test failed: ' + (response.data?.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Network error during route test.');
            }
        });
    });
    
    // Delete route
    $(document).on('click', '.hph-delete-route', function() {
        const routeId = $(this).data('route-id');
        
        if (confirm('Are you sure you want to delete this route? This action cannot be undone.')) {
            // Implementation for route deletion would go here
            alert('Route deletion functionality needs to be implemented in the backend.');
        }
    });
    
    // Cancel route editing
    $('#hph-cancel-route').on('click', function() {
        closeModal('hph-route-modal');
    });
    
    // Field mappings page
    $('#hph-add-field-mapping').on('click', function() {
        const fieldName = $('#hph-new-field-name').val().trim();
        const fieldSources = $('#hph-new-field-sources').val().trim();
        
        if (!fieldName || !fieldSources) {
            alert('Please enter both field name and source fields.');
            return;
        }
        
        // Add new row to the table
        const newRow = `
            <tr data-field="${fieldName}">
                <td>
                    <strong>${fieldName}</strong>
                    <input type="hidden" name="hph_form_router_field_mappings[${fieldName}][key]" value="${fieldName}" />
                </td>
                <td>
                    <input type="text" 
                           name="hph_form_router_field_mappings[${fieldName}][sources]" 
                           value="${fieldSources}" 
                           class="regular-text"
                           placeholder="field1, field2, field3" />
                    <p class="description">Comma-separated list of possible field names</p>
                </td>
                <td>
                    <select name="hph_form_router_field_mappings[${fieldName}][transform]">
                        <option value="">None</option>
                        <option value="split_name">Split Full Name</option>
                        <option value="format_phone">Format Phone</option>
                        <option value="normalize_email">Normalize Email</option>
                    </select>
                </td>
                <td>
                    <select name="hph_form_router_field_mappings[${fieldName}][validation]">
                        <option value="">None</option>
                        <option value="email">Email</option>
                        <option value="phone">Phone</option>
                        <option value="required">Required</option>
                    </select>
                </td>
                <td>
                    <input type="checkbox" 
                           name="hph_form_router_field_mappings[${fieldName}][required]" 
                           value="1" />
                </td>
                <td>
                    <button type="button" class="button button-small hph-delete-mapping" data-field="${fieldName}">
                        Delete
                    </button>
                </td>
            </tr>
        `;
        
        $('table.wp-list-table tbody').append(newRow);
        
        // Clear input fields
        $('#hph-new-field-name').val('');
        $('#hph-new-field-sources').val('');
    });
    
    // Delete field mapping
    $(document).on('click', '.hph-delete-mapping', function() {
        if (confirm('Are you sure you want to delete this field mapping?')) {
            $(this).closest('tr').remove();
        }
    });
    
    // Activity log page
    $('#filter-logs').on('click', function() {
        const level = $('#log-level-filter').val();
        const search = $('#log-search').val();
        
        // Reload page with filters
        const url = new URL(window.location);
        if (level) url.searchParams.set('level', level);
        else url.searchParams.delete('level');
        
        if (search) url.searchParams.set('search', search);
        else url.searchParams.delete('search');
        
        window.location = url;
    });
    
    $('#clear-logs').on('click', function() {
        if (confirm('Are you sure you want to clear all log entries? This action cannot be undone.')) {
            // Implementation for clearing logs would go here
            alert('Log clearing functionality needs to be implemented.');
        }
    });
    
    // View log context/data
    $(document).on('click', '.hph-view-context', function() {
        const context = $(this).data('context');
        try {
            const contextData = JSON.parse(context);
            $('#hph-context-content').text(JSON.stringify(contextData, null, 2));
        } catch (e) {
            $('#hph-context-content').text(context);
        }
        openModal('hph-context-modal');
    });
    
    $(document).on('click', '.hph-view-data', function() {
        const formData = $(this).data('form-data');
        try {
            const data = JSON.parse(formData);
            $('#hph-data-content').text(JSON.stringify(data, null, 2));
        } catch (e) {
            $('#hph-data-content').text(formData);
        }
        openModal('hph-data-modal');
    });
    
    // Calendly integration test
    $('#hph-test-calendly').on('click', function() {
        const username = $('input[name="hp_calendly_username"]').val();
        
        if (!username) {
            alert('Please enter your Calendly username first.');
            return;
        }
        
        // Test each calendar type
        const calendarTypes = ['consultation', 'showing', 'valuation', 'call'];
        const results = [];
        
        calendarTypes.forEach(function(type) {
            const slug = $(`input[name="hp_calendly_calendar_slugs[${type}]"]`).val();
            if (slug) {
                const url = `https://calendly.com/${username}/${slug}`;
                results.push(`<p><strong>${type}:</strong> <a href="${url}" target="_blank">${url}</a></p>`);
            }
        });
        
        $('#hph-calendly-test-results').html('<h4>Test Results:</h4>' + results.join(''));
    });
    
    // Export routes
    $('#hph-export-routes').on('click', function() {
        const url = hphFormRouter.ajaxUrl + '?action=hph_export_routes&nonce=' + hphFormRouter.nonce;
        window.location = url;
    });
    
    // Import routes
    $('#hph-import-routes').on('click', function() {
        const fileInput = $('#hph-import-file')[0];
        if (!fileInput.files.length) {
            alert('Please select a file to import.');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'hph_import_routes');
        formData.append('import_file', fileInput.files[0]);
        formData.append('nonce', hphFormRouter.nonce);
        
        $.ajax({
            url: hphFormRouter.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Routes imported successfully!');
                    location.reload();
                } else {
                    alert('Import failed: ' + (response.data?.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Network error during import.');
            }
        });
    });
    
    // Utility function to set nested object properties from form field names
    function setNestedProperty(obj, path, value) {
        const keys = path.replace(/\[(\w+)\]/g, '.$1').replace(/^\./, '').split('.');
        let current = obj;
        
        for (let i = 0; i < keys.length - 1; i++) {
            const key = keys[i];
            if (!(key in current)) {
                current[key] = {};
            }
            current = current[key];
        }
        
        const finalKey = keys[keys.length - 1];
        
        // Handle arrays (like conditions)
        if (finalKey.match(/^\d+$/)) {
            if (!Array.isArray(current)) {
                const temp = current;
                current = [];
                // Copy existing numeric properties
                Object.keys(temp).forEach(k => {
                    if (k.match(/^\d+$/)) {
                        current[parseInt(k)] = temp[k];
                    }
                });
            }
            current[parseInt(finalKey)] = value;
        } else {
            current[finalKey] = value;
        }
    }
});