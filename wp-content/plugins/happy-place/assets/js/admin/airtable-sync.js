/**
 * Airtable Sync Admin JavaScript
 * Handles UI interactions for the Airtable sync interface
 * 
 * @package HappyPlace
 */

jQuery(document).ready(function($) {
    'use strict';

    const AirtableSync = {
        
        init: function() {
            this.bindEvents();
            this.initLogScroll();
        },

        bindEvents: function() {
            // Test connection button
            $('#test-connection').on('click', this.testConnection.bind(this));
            
            // Sync from Airtable buttons
            $('.sync-from-airtable').on('click', this.syncFromAirtable.bind(this));
            $('.sync-all-from-airtable').on('click', this.syncAllFromAirtable.bind(this));
            
            // Sync to Airtable buttons  
            $('.sync-to-airtable').on('click', this.syncToAirtable.bind(this));
            $('.sync-all-to-airtable').on('click', this.syncAllToAirtable.bind(this));

            // Settings form
            $('#airtable-settings-form').on('submit', this.saveSettings.bind(this));
        },

        initLogScroll: function() {
            // Auto-scroll log to bottom when new content is added
            const logContent = document.getElementById('sync-log-content');
            if (logContent) {
                const observer = new MutationObserver(function() {
                    logContent.scrollTop = logContent.scrollHeight;
                });
                observer.observe(logContent, { 
                    childList: true, 
                    subtree: true, 
                    characterData: true 
                });
            }
        },

        testConnection: function(e) {
            e.preventDefault();
            
            const button = $(e.target);
            const originalText = button.text();
            
            button.text(hptAirtable.strings.testing).prop('disabled', true);
            this.clearLog();
            this.addLogEntry('info', 'Testing Airtable connection...');

            $.ajax({
                url: hptAirtable.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hpt_airtable_test_connection',
                    nonce: hptAirtable.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice('success', response.data.message);
                        this.addLogEntry('success', `Connection successful! Found ${response.data.data.record_count} records in base ${response.data.data.base_id}`);
                    } else {
                        this.showNotice('error', response.data.message);
                        this.addLogEntry('error', `Connection failed: ${response.data.message}`);
                    }
                },
                error: (xhr, status, error) => {
                    this.showNotice('error', 'AJAX error: ' + error);
                    this.addLogEntry('error', `Network error: ${error}`);
                },
                complete: () => {
                    button.text(originalText).prop('disabled', false);
                }
            });
        },

        syncFromAirtable: function(e) {
            e.preventDefault();
            
            const button = $(e.target);
            const postType = button.data('type');
            
            this.performSync('from_airtable', postType, button);
        },

        syncAllFromAirtable: function(e) {
            e.preventDefault();
            
            if (!confirm(hptAirtable.strings.confirmSync)) {
                return;
            }

            const button = $(e.target);
            this.performSync('from_airtable', 'all', button);
        },

        syncToAirtable: function(e) {
            e.preventDefault();
            
            const button = $(e.target);
            const postType = button.data('type');
            
            this.performSync('to_airtable', postType, button);
        },

        syncAllToAirtable: function(e) {
            e.preventDefault();
            
            if (!confirm(hptAirtable.strings.confirmSync)) {
                return;
            }

            const button = $(e.target);
            this.performSync('to_airtable', 'all', button);
        },

        performSync: function(direction, postType, button) {
            const originalText = button.text();
            const actionText = direction === 'from_airtable' ? 'Importing' : 'Exporting';
            const typeText = postType === 'all' ? 'all data' : postType;
            
            button.text(`${actionText}...`).prop('disabled', true);
            
            // Disable all sync buttons during operation
            $('.sync-from-airtable, .sync-to-airtable, .sync-all-from-airtable, .sync-all-to-airtable')
                .prop('disabled', true);

            this.addLogEntry('info', `Starting ${actionText.toLowerCase()} for ${typeText}...`);

            const ajaxAction = direction === 'from_airtable' ? 
                'hpt_airtable_sync_from_airtable' : 
                'hpt_airtable_sync_to_airtable';

            $.ajax({
                url: hptAirtable.ajaxUrl,
                type: 'POST',
                data: {
                    action: ajaxAction,
                    post_type: postType,
                    nonce: hptAirtable.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.handleSyncSuccess(response.data, direction, postType);
                    } else {
                        this.showNotice('error', response.data.message);
                        this.addLogEntry('error', `Sync failed: ${response.data.message}`);
                    }
                },
                error: (xhr, status, error) => {
                    this.showNotice('error', 'AJAX error: ' + error);
                    this.addLogEntry('error', `Network error: ${error}`);
                },
                complete: () => {
                    button.text(originalText).prop('disabled', false);
                    
                    // Re-enable all sync buttons
                    $('.sync-from-airtable, .sync-to-airtable, .sync-all-from-airtable, .sync-all-to-airtable')
                        .prop('disabled', false);
                        
                    // Refresh the status display
                    this.refreshStatus();
                }
            });
        },

        handleSyncSuccess: function(data, direction, postType) {
            this.showNotice('success', data.message);
            
            if (data.results) {
                // Multi-table sync results
                let totalCreated = 0;
                let totalUpdated = 0;
                let totalErrors = 0;

                Object.keys(data.results).forEach(type => {
                    const result = data.results[type];
                    if (result.success) {
                        totalCreated += result.created || 0;
                        totalUpdated += result.updated || 0;
                        
                        this.addLogEntry('success', 
                            `${type}: ${result.created || 0} created, ${result.updated || 0} updated, ${result.total || 0} total`
                        );

                        if (result.errors && result.errors.length > 0) {
                            totalErrors += result.errors.length;
                            result.errors.forEach(error => {
                                this.addLogEntry('warning', `${type} error: ${error}`);
                            });
                        }
                    } else {
                        this.addLogEntry('error', `${type}: ${result.message}`);
                    }
                });

                this.addLogEntry('info', 
                    `Total: ${totalCreated} created, ${totalUpdated} updated` + 
                    (totalErrors > 0 ? `, ${totalErrors} errors` : '')
                );

            } else {
                // Single table sync result
                const created = data.created || 0;
                const updated = data.updated || 0;
                const errors = data.errors || [];

                this.addLogEntry('success', 
                    `Sync completed: ${created} created, ${updated} updated, ${data.total || 0} total`
                );

                if (errors.length > 0) {
                    errors.forEach(error => {
                        this.addLogEntry('warning', `Error: ${error}`);
                    });
                }
            }
        },

        saveSettings: function(e) {
            // Let the form submit normally to WordPress settings API
            this.addLogEntry('info', 'Saving Airtable settings...');
        },

        refreshStatus: function() {
            $.ajax({
                url: hptAirtable.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hpt_airtable_get_status',
                    nonce: hptAirtable.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStatusDisplay(response.data);
                    }
                }
            });
        },

        updateStatusDisplay: function(status) {
            // Update the status numbers in the UI
            Object.keys(status.tables).forEach(postType => {
                const tableStatus = status.tables[postType];
                const statusItem = $(`.status-item:contains(${postType})`);
                
                if (statusItem.length) {
                    statusItem.find('.wp-count').text(tableStatus.wp_count);
                    statusItem.find('.airtable-count').text(tableStatus.airtable_count);
                    
                    if (tableStatus.last_sync) {
                        const lastSync = new Date(tableStatus.last_sync * 1000);
                        const lastSyncText = lastSync.toLocaleDateString() + ' ' + lastSync.toLocaleTimeString();
                        statusItem.find('.last-sync').text(`Last sync: ${lastSyncText}`);
                    }
                }
            });
        },

        showNotice: function(type, message) {
            // Remove existing notices
            $('.hpt-notice').remove();
            
            const noticeClass = type === 'success' ? 'notice-success' : 
                               type === 'error' ? 'notice-error' : 'notice-info';
            
            const notice = $(`
                <div class="notice ${noticeClass} is-dismissible hpt-notice">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);

            $('.wrap h1').after(notice);

            // Auto-dismiss success notices after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    notice.fadeOut(() => notice.remove());
                }, 5000);
            }

            // Handle dismiss button
            notice.find('.notice-dismiss').on('click', function() {
                notice.fadeOut(() => notice.remove());
            });
        },

        addLogEntry: function(level, message) {
            const timestamp = new Date().toLocaleTimeString();
            const levelClass = level === 'error' ? 'error' : 
                             level === 'warning' ? 'warning' :
                             level === 'success' ? 'success' : 'info';
                             
            const levelText = level.toUpperCase();
            
            const logEntry = $(`
                <div class="log-entry log-${levelClass}">
                    <span class="log-time">${timestamp}</span>
                    <span class="log-level">[${levelText}]</span>
                    <span class="log-message">${message}</span>
                </div>
            `);

            $('#sync-log-content').append(logEntry);
        },

        clearLog: function() {
            $('#sync-log-content').empty();
        }
    };

    // Initialize the Airtable sync interface
    AirtableSync.init();

    // Add some CSS for log styling
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .log-entry {
                margin-bottom: 5px;
                padding: 5px;
                border-left: 3px solid #ccc;
                background: rgba(0,0,0,0.02);
            }
            .log-entry.log-error {
                border-left-color: #dc3232;
                background: rgba(220, 50, 50, 0.1);
            }
            .log-entry.log-warning {
                border-left-color: #ffb900;
                background: rgba(255, 185, 0, 0.1);
            }
            .log-entry.log-success {
                border-left-color: #00a32a;
                background: rgba(0, 163, 42, 0.1);
            }
            .log-entry.log-info {
                border-left-color: #0073aa;
                background: rgba(0, 115, 170, 0.1);
            }
            .log-time {
                color: #666;
                font-size: 11px;
                margin-right: 8px;
            }
            .log-level {
                font-weight: bold;
                margin-right: 8px;
                font-size: 11px;
            }
            .log-message {
                font-family: inherit;
            }
            .log-error .log-level {
                color: #dc3232;
            }
            .log-warning .log-level {
                color: #ffb900;
            }
            .log-success .log-level {
                color: #00a32a;
            }
            .log-info .log-level {
                color: #0073aa;
            }
            .hpt-notice {
                margin: 15px 0;
            }
        `)
        .appendTo('head');
        
    // Initialize AirtableSync
    AirtableSync.init();
        
    // Initialize AirtableSync
    AirtableSync.init();
});

// Make functions globally available for onclick handlers
window.syncFromAirtable = function(postType) {
    // Create a fake event object and button to work with existing code
    const fakeEvent = {
        preventDefault: function() {},
        target: {
            dataset: { type: postType }
        }
    };
    
    if (typeof jQuery !== 'undefined' && jQuery.fn) {
        fakeEvent.target = jQuery('<button>').data('type', postType).addClass('sync-from-airtable')[0];
        AirtableSync.syncFromAirtable(fakeEvent);
    } else {
        console.error('jQuery not available for syncFromAirtable');
    }
};

window.syncToAirtable = function(postType) {
    const fakeEvent = {
        preventDefault: function() {},
        target: {
            dataset: { type: postType }
        }
    };
    
    if (typeof jQuery !== 'undefined' && jQuery.fn) {
        fakeEvent.target = jQuery('<button>').data('type', postType).addClass('sync-to-airtable')[0];
        AirtableSync.syncToAirtable(fakeEvent);
    } else {
        console.error('jQuery not available for syncToAirtable');
    }
};

window.testAirtableConnection = function() {
    const fakeEvent = {
        preventDefault: function() {},
        target: jQuery('#test-connection')[0] || jQuery('<button>')[0]
    };
    
    if (typeof jQuery !== 'undefined' && jQuery.fn) {
        AirtableSync.testConnection(fakeEvent);
    } else {
        console.error('jQuery not available for testAirtableConnection');
    }
};