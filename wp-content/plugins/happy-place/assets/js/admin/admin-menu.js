/**
 * Happy Place Admin Menu JavaScript
 * 
 * Handles tab switching and other admin interface functionality
 * 
 * @package HappyPlace\Admin
 * @since 4.0.0
 */

(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        initTabs();
        initToolActions();
        initSyncActions();
        initFormValidation();
        initRoleManagement();
    });
    
    /**
     * Initialize tab functionality for admin pages
     */
    function initTabs() {
        $('.hp-nav-tabs .nav-tab').on('click', function(e) {
            e.preventDefault();
            
            const $tab = $(this);
            const targetSection = $tab.attr('href').substring(1); // Remove #
            
            // Update active tab
            $tab.closest('.hp-nav-tabs').find('.nav-tab').removeClass('nav-tab-active');
            $tab.addClass('nav-tab-active');
            
            // Show corresponding content section
            $tab.closest('.wrap').find('.hp-settings-section').hide();
            $('#' + targetSection).fadeIn(300);
            
            // Update URL hash
            window.location.hash = targetSection;
        });
        
        // Initialize based on URL hash
        const hash = window.location.hash.substring(1);
        if (hash && $('#' + hash).length && $('.hp-nav-tabs .nav-tab[href="#' + hash + '"]').length) {
            $('.hp-nav-tabs .nav-tab[href="#' + hash + '"]').trigger('click');
        } else {
            // Default to first tab
            $('.hp-nav-tabs .nav-tab:first').trigger('click');
        }
    }
    
    /**
     * Initialize tool actions (Clear Cache, Regenerate Thumbnails, etc.)
     */
    function initToolActions() {
        // Clear Cache
        $('#hp-clear-cache').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            
            $btn.prop('disabled', true).text('Clearing...');
            
            $.post(ajaxurl, {
                action: 'hp_clear_cache',
                nonce: hp_admin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    showNotice('Cache cleared successfully!', 'success');
                } else {
                    showNotice('Failed to clear cache: ' + (response.data || 'Unknown error'), 'error');
                }
            })
            .fail(function() {
                showNotice('Failed to clear cache. Please try again.', 'error');
            })
            .always(function() {
                $btn.prop('disabled', false).text('Clear Cache');
            });
        });
        
        // Regenerate Thumbnails
        $('#hp-regenerate-thumbnails').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            
            $btn.prop('disabled', true).text('Regenerating...');
            
            $.post(ajaxurl, {
                action: 'hp_regenerate_thumbnails',
                nonce: hp_admin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    showNotice('Thumbnails regenerated successfully!', 'success');
                } else {
                    showNotice('Failed to regenerate thumbnails: ' + (response.data || 'Unknown error'), 'error');
                }
            })
            .fail(function() {
                showNotice('Failed to regenerate thumbnails. Please try again.', 'error');
            })
            .always(function() {
                $btn.prop('disabled', false).text('Regenerate');
            });
        });
        
        // Database Optimization
        $('#hp-optimize-db').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            
            if (!confirm('Are you sure you want to optimize the database? This may take a few minutes.')) {
                return;
            }
            
            $btn.prop('disabled', true).text('Optimizing...');
            
            $.post(ajaxurl, {
                action: 'hp_optimize_database',
                nonce: hp_admin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    showNotice('Database optimized successfully!', 'success');
                } else {
                    showNotice('Failed to optimize database: ' + (response.data || 'Unknown error'), 'error');
                }
            })
            .fail(function() {
                showNotice('Failed to optimize database. Please try again.', 'error');
            })
            .always(function() {
                $btn.prop('disabled', false).text('Optimize');
            });
        });
        
        // Airtable Connection Test
        $('.hp-test-airtable-connection').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $status = $btn.siblings('.hp-connection-status');
            
            $btn.prop('disabled', true).text('Testing...');
            $status.html('<span style="color: #666;">Testing connection...</span>');
            
            $.post(ajaxurl, {
                action: 'hp_test_airtable_connection',
                nonce: hp_admin.nonce,
                api_key: $('input[name="hp_airtable_api_key"]').val(),
                base_id: $('input[name="hp_airtable_base_id"]').val()
            })
            .done(function(response) {
                if (response.success) {
                    $status.html('<span style="color: #46b450;">✓ Connection successful!</span>');
                    showNotice('Airtable connection successful!', 'success');
                } else {
                    $status.html('<span style="color: #dc3232;">✗ Connection failed</span>');
                    showNotice('Airtable connection failed: ' + (response.data || 'Unknown error'), 'error');
                }
            })
            .fail(function() {
                $status.html('<span style="color: #dc3232;">✗ Connection failed</span>');
                showNotice('Failed to test Airtable connection. Please try again.', 'error');
            })
            .always(function() {
                $btn.prop('disabled', false).text('Test Connection');
            });
        });
        
        // FollowUp Boss Connection Test
        $('.hp-test-followup-boss-connection').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $status = $btn.siblings('.hp-connection-status');
            
            $btn.prop('disabled', true).text('Testing...');
            $status.html('<span style="color: #666;">Testing connection...</span>');
            
            $.post(ajaxurl, {
                action: 'hp_test_followup_boss_connection',
                nonce: hp_admin.nonce,
                api_key: $('input[name="hp_followup_boss_api_key"]').val(),
                api_secret: $('input[name="hp_followup_boss_api_secret"]').val()
            })
            .done(function(response) {
                if (response.success) {
                    $status.html('<span style="color: #46b450;">✓ Connection successful!</span>');
                    showNotice('FollowUp Boss connection successful!', 'success');
                    
                    // Update agent dropdown if agents were returned
                    if (response.data && response.data.agents) {
                        updateAgentDropdown(response.data.agents);
                    }
                } else {
                    $status.html('<span style="color: #dc3232;">✗ Connection failed</span>');
                    showNotice('FollowUp Boss connection failed: ' + (response.data || 'Unknown error'), 'error');
                }
            })
            .fail(function() {
                $status.html('<span style="color: #dc3232;">✗ Connection failed</span>');
                showNotice('Failed to test FollowUp Boss connection. Please try again.', 'error');
            })
            .always(function() {
                $btn.prop('disabled', false).text('Test Connection');
            });
        });
        
        // Copy Webhook URL
        $('.hp-copy-webhook-url').on('click', function(e) {
            e.preventDefault();
            const $input = $(this).siblings('input[name="hp_followup_boss_webhook_url"]');
            
            $input.select();
            document.execCommand('copy');
            
            showNotice('Webhook URL copied to clipboard!', 'success');
        });
    }
    
    /**
     * Initialize sync actions
     */
    function initSyncActions() {
        // MLS Sync
        $('.hp-sync-mls').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            
            $btn.prop('disabled', true).text('Syncing...');
            
            $.post(ajaxurl, {
                action: 'hp_sync_mls',
                nonce: hp_admin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    showNotice('MLS sync completed successfully!', 'success');
                    // Reload page to show updated sync status
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    showNotice('MLS sync failed: ' + (response.data || 'Unknown error'), 'error');
                }
            })
            .fail(function() {
                showNotice('MLS sync failed. Please try again.', 'error');
            })
            .always(function() {
                $btn.prop('disabled', false).text('Sync Now');
            });
        });
        
        // Individual Config Sync
        $('.hp-sync-config').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const configKey = $btn.data('key');
            
            $btn.prop('disabled', true).text('Syncing...');
            
            $.post(ajaxurl, {
                action: 'hp_sync_config',
                config_key: configKey,
                nonce: hp_admin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    showNotice('Configuration synced successfully!', 'success');
                    // Update the status in the table
                    $btn.closest('tr').find('.hp-status-badge')
                        .removeClass('hp-status-conflict')
                        .addClass('hp-status-synced')
                        .text('Synced');
                    $btn.remove();
                } else {
                    showNotice('Config sync failed: ' + (response.data || 'Unknown error'), 'error');
                }
            })
            .fail(function() {
                showNotice('Config sync failed. Please try again.', 'error');
            })
            .always(function() {
                $btn.prop('disabled', false).text('Sync');
            });
        });
    }
    
    /**
     * Initialize form validation
     */
    function initFormValidation() {
        // API Key validation
        $('input[name="hp_google_maps_api_key"]').on('blur', function() {
            const apiKey = $(this).val().trim();
            if (apiKey.length > 0 && apiKey.length < 20) {
                showNotice('Google Maps API key appears to be too short.', 'warning');
            }
        });
        
        // Email validation
        $('input[type="email"]').on('blur', function() {
            const email = $(this).val().trim();
            if (email && !isValidEmail(email)) {
                showNotice('Please enter a valid email address.', 'warning');
            }
        });
        
        // URL validation
        $('input[type="url"]').on('blur', function() {
            const url = $(this).val().trim();
            if (url && !isValidURL(url)) {
                showNotice('Please enter a valid URL.', 'warning');
            }
        });
    }
    
    /**
     * Show admin notice
     */
    function showNotice(message, type = 'info') {
        const noticeClass = type === 'error' ? 'notice-error' : 
                           type === 'warning' ? 'notice-warning' :
                           type === 'success' ? 'notice-success' : 'notice-info';
        
        const $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        // Remove existing notices
        $('.wrap .notice').remove();
        
        // Add new notice
        $('.wrap h1').after($notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut();
        }, 5000);
        
        // Handle dismiss button
        $notice.on('click', '.notice-dismiss', function() {
            $notice.fadeOut();
        });
    }
    
    /**
     * Validate email address
     */
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    /**
     * Validate URL
     */
    function isValidURL(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }
    
    /**
     * Update agent dropdown with fresh data from FollowUp Boss
     */
    function updateAgentDropdown(agents) {
        const $dropdown = $('select[name="hp_followup_boss_auto_assign"]');
        const currentValue = $dropdown.val();
        
        // Clear existing options except the first one
        $dropdown.find('option:not(:first)').remove();
        
        // Add new agent options
        $.each(agents, function(id, name) {
            const $option = $('<option></option>')
                .attr('value', id)
                .text(name);
            
            if (id === currentValue) {
                $option.prop('selected', true);
            }
            
            $dropdown.append($option);
        });
    }
    
    /**
     * Initialize role management functionality
     */
    function initRoleManagement() {
        // Preview role changes
        $(document).on('click', '#hp-preview-role-changes', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $previewContainer = $('#hp-role-preview-content');
            const $previewSection = $('#hp-role-preview');
            
            // Show loading state
            $button.prop('disabled', true).text('Loading...');
            $previewSection.show();
            $previewContainer.html('<div class="spinner is-active"></div>');
            
            $.ajax({
                url: hp_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hp_preview_role_changes',
                    nonce: hp_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $previewContainer.html(response.data.html);
                    } else {
                        $previewContainer.html('<div class="notice notice-error"><p>' + (response.data.message || 'An error occurred') + '</p></div>');
                    }
                },
                error: function(xhr, status, error) {
                    $previewContainer.html('<div class="notice notice-error"><p>Failed to load preview. Please try again.</p></div>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Preview Changes');
                }
            });
        });
        
        // Clean up user roles
        $(document).on('click', '#hp-cleanup-roles', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to clean up user roles? This will:\n\n' +
                '• Remove legacy roles (Real Estate Agent, Broker, Client)\n' +
                '• Convert users to new role structure (Agent, Lead, Staff, Admin)\n' +
                '• Create corresponding agent/staff records where needed\n\n' +
                'This action cannot be undone.')) {
                return;
            }
            
            const $button = $(this);
            const $statusContainer = $('#hp-role-status');
            
            // Show loading state
            $button.prop('disabled', true).text('Cleaning up...');
            $statusContainer.html('<div class="spinner is-active"></div><p>Processing role cleanup...</p>');
            
            $.ajax({
                url: hp_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hp_cleanup_roles',
                    nonce: hp_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $statusContainer.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                        
                        // Refresh the role status table
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $statusContainer.html('<div class="notice notice-error"><p>' + (response.data.message || 'An error occurred') + '</p></div>');
                    }
                },
                error: function(xhr, status, error) {
                    $statusContainer.html('<div class="notice notice-error"><p>Failed to cleanup roles. Please try again.</p></div>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Clean Up User Roles');
                }
            });
        });
    }

})(jQuery);
