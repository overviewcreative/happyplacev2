/**
 * ACF Sync Manager JavaScript
 */

jQuery(document).ready(function($) {
    const ACFSync = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $('#sync-field-groups').on('click', this.syncFieldGroups);
            $('#cleanup-field-groups').on('click', this.cleanupFieldGroups);
            $('#refresh-field-groups').on('click', this.refreshFieldGroups);
            
            $(document).on('click', '.sync-group', this.syncSingleGroup);
            $(document).on('click', '.import-group', this.importSingleGroup);
            $(document).on('click', '.export-group', this.exportSingleGroup);
        },

        syncFieldGroups: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const originalText = $button.text();
            
            $button.prop('disabled', true).text(hptACFSync.strings.syncing);
            ACFSync.showLog();
            ACFSync.addLogEntry('Starting field group sync...');

            $.ajax({
                url: hptACFSync.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hpt_sync_field_groups',
                    nonce: hptACFSync.nonce
                },
                success: function(response) {
                    if (response.success) {
                        ACFSync.addLogEntry(`Successfully synced ${response.data.synced} field groups`);
                        
                        if (response.data.log && response.data.log.length > 0) {
                            response.data.log.forEach(function(logEntry) {
                                ACFSync.addLogEntry(logEntry);
                            });
                        }

                        if (response.data.errors && response.data.errors.length > 0) {
                            response.data.errors.forEach(function(error) {
                                ACFSync.addLogEntry(error, 'error');
                            });
                        }

                        ACFSync.showNotice('success', hptACFSync.strings.success);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        ACFSync.addLogEntry('Sync failed: ' + response.data.message, 'error');
                        ACFSync.showNotice('error', response.data.message || hptACFSync.strings.error);
                    }
                },
                error: function() {
                    ACFSync.addLogEntry('Ajax request failed', 'error');
                    ACFSync.showNotice('error', hptACFSync.strings.error);
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        cleanupFieldGroups: function(e) {
            e.preventDefault();
            
            if (!confirm(hptACFSync.strings.confirmCleanup)) {
                return;
            }
            
            const $button = $(this);
            const originalText = $button.text();
            
            $button.prop('disabled', true).text(hptACFSync.strings.cleaning);
            ACFSync.showLog();
            ACFSync.addLogEntry('Starting field group cleanup...');

            $.ajax({
                url: hptACFSync.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hpt_cleanup_field_groups',
                    nonce: hptACFSync.nonce
                },
                success: function(response) {
                    if (response.success) {
                        ACFSync.addLogEntry(`Successfully removed ${response.data.removed} orphaned field groups`);
                        
                        if (response.data.log && response.data.log.length > 0) {
                            response.data.log.forEach(function(logEntry) {
                                ACFSync.addLogEntry(logEntry);
                            });
                        }

                        ACFSync.showNotice('success', hptACFSync.strings.success);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        ACFSync.addLogEntry('Cleanup failed: ' + response.data.message, 'error');
                        ACFSync.showNotice('error', response.data.message || hptACFSync.strings.error);
                    }
                },
                error: function() {
                    ACFSync.addLogEntry('Ajax request failed', 'error');
                    ACFSync.showNotice('error', hptACFSync.strings.error);
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        refreshFieldGroups: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const originalText = $button.text();
            
            $button.prop('disabled', true).text(hptACFSync.strings.refreshing);
            ACFSync.showLog();
            ACFSync.addLogEntry('Starting field group refresh from JSON...');

            $.ajax({
                url: hptACFSync.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hpt_refresh_field_groups',
                    nonce: hptACFSync.nonce
                },
                success: function(response) {
                    if (response.success) {
                        ACFSync.addLogEntry(`Successfully refreshed ${response.data.synced} field groups`);
                        
                        if (response.data.log && response.data.log.length > 0) {
                            response.data.log.forEach(function(logEntry) {
                                ACFSync.addLogEntry(logEntry);
                            });
                        }

                        ACFSync.showNotice('success', hptACFSync.strings.success);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        ACFSync.addLogEntry('Refresh failed: ' + response.data.message, 'error');
                        ACFSync.showNotice('error', response.data.message || hptACFSync.strings.error);
                    }
                },
                error: function() {
                    ACFSync.addLogEntry('Ajax request failed', 'error');
                    ACFSync.showNotice('error', hptACFSync.strings.error);
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        syncSingleGroup: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const groupKey = $button.data('group');
            const originalText = $button.text();
            
            $button.prop('disabled', true).text('Syncing...');
            
            // For now, we'll reload the page to sync single groups
            // In a more advanced implementation, you could add individual group sync endpoints
            location.reload();
        },

        importSingleGroup: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const groupKey = $button.data('group');
            const originalText = $button.text();
            
            $button.prop('disabled', true).text('Importing...');
            
            // For now, we'll reload the page
            // In a more advanced implementation, you could add individual group import endpoints
            location.reload();
        },

        exportSingleGroup: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const groupKey = $button.data('group');
            const originalText = $button.text();
            
            $button.prop('disabled', true).text('Exporting...');
            
            // For now, we'll reload the page
            // In a more advanced implementation, you could add individual group export endpoints
            location.reload();
        },

        showLog: function() {
            $('#hpt-sync-log').show();
            $('#hpt-sync-log-content').empty();
        },

        addLogEntry: function(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            let className = 'log-info';
            
            if (type === 'error') {
                className = 'log-error';
            } else if (type === 'warning') {
                className = 'log-warning';
            } else if (type === 'success') {
                className = 'log-success';
            }
            
            const logEntry = `<div class="${className}">[${timestamp}] ${message}</div>`;
            $('#hpt-sync-log-content').append(logEntry);
            
            // Auto-scroll to bottom
            const logContent = document.getElementById('hpt-sync-log-content');
            logContent.scrollTop = logContent.scrollHeight;
        },

        showNotice: function(type, message) {
            // Remove any existing notices
            $('.hpt-sync-notice').remove();
            
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const notice = `<div class="notice ${noticeClass} hpt-sync-notice is-dismissible"><p>${message}</p></div>`;
            
            $('.hpt-acf-sync-container').prepend(notice);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.hpt-sync-notice').fadeOut();
            }, 5000);
        }
    };

    // Initialize
    ACFSync.init();

    // Add some CSS for log styling
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            #hpt-sync-log-content .log-info { color: #333; }
            #hpt-sync-log-content .log-success { color: #28a745; }
            #hpt-sync-log-content .log-warning { color: #ffc107; }
            #hpt-sync-log-content .log-error { color: #dc3545; }
            #hpt-sync-log-content div { margin: 2px 0; padding: 2px 0; }
        `)
        .appendTo('head');
});