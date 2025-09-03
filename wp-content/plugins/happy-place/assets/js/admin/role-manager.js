/**
 * Happy Place Admin Role Management
 * 
 * Handles role management functionality in the admin panel
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initRoleManager();
    });

    /**
     * Initialize role manager
     */
    function initRoleManager() {
        // Handle preview button
        $('#hp-preview-role-changes').on('click', function(e) {
            e.preventDefault();
            previewRoleChanges();
        });

        // Handle cleanup button
        $('#hp-cleanup-roles').on('click', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to clean up user roles? This will migrate users from legacy roles to new roles and cannot be undone.')) {
                cleanupRoles();
            }
        });
    }

    /**
     * Preview role changes
     */
    function previewRoleChanges() {
        var $button = $('#hp-preview-role-changes');
        var $preview = $('#hp-role-preview');
        var $content = $('#hp-role-preview-content');
        
        $button.text('Loading...').prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hp_preview_role_changes',
                nonce: hp_admin_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    var changes = response.data.changes;
                    var html = '';
                    
                    if (changes.length === 0) {
                        html = '<p>No role changes needed. All roles are up to date.</p>';
                    } else {
                        html = '<div class="hp-changes-list">';
                        
                        changes.forEach(function(change) {
                            html += '<div class="hp-change-item ' + change.action + '">';
                            html += '<strong>Remove Role:</strong> ' + change.role_name + ' (' + change.role + ')';
                            
                            if (change.user_count > 0) {
                                html += '<br><strong>Affected Users:</strong> ' + change.user_count + ' users will be migrated to "' + change.migration_target + '" role';
                            }
                            
                            html += '</div>';
                        });
                        
                        html += '</div>';
                    }
                    
                    $content.html(html);
                    $preview.slideDown();
                } else {
                    showNotice('error', response.data.message || 'Failed to preview changes');
                }
            },
            error: function() {
                showNotice('error', 'Network error. Please try again.');
            },
            complete: function() {
                $button.text('Preview Changes').prop('disabled', false);
            }
        });
    }

    /**
     * Clean up roles
     */
    function cleanupRoles() {
        var $button = $('#hp-cleanup-roles');
        var $preview = $('#hp-role-preview');
        
        $button.text('Cleaning up...').prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hp_cleanup_roles',
                nonce: hp_admin_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message || 'Roles cleaned up successfully');
                    
                    // Reload the page to show updated role table
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    showNotice('error', response.data.message || 'Failed to clean up roles');
                }
            },
            error: function() {
                showNotice('error', 'Network error. Please try again.');
            },
            complete: function() {
                $button.text('Clean Up User Roles').prop('disabled', false);
            }
        });
    }

    /**
     * Show admin notice
     */
    function showNotice(type, message) {
        var noticeClass = 'notice-' + type;
        var $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        $('.wrap').prepend($notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut();
        }, 5000);
    }

})(jQuery);
