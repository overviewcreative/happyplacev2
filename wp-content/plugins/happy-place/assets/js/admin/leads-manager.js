/**
 * Happy Place Admin Lead Manager
 * 
 * Handles lead management functionality in the admin panel
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initLeadManager();
    });

    /**
     * Initialize lead manager
     */
    function initLeadManager() {
        // Handle status changes
        $('.hp-lead-status').on('change', function() {
            updateLeadStatus($(this));
        });

        // Handle lead deletion
        $('.hp-delete-lead').on('click', function(e) {
            e.preventDefault();
            deleteLead($(this));
        });

        // Handle note addition
        $('.hp-add-note').on('click', function(e) {
            e.preventDefault();
            openNoteModal($(this).data('lead-id'));
        });

        // Handle note form submission
        $('#hp-add-note-form').on('submit', function(e) {
            e.preventDefault();
            submitNote();
        });

        // Handle lead details view
        $('.hp-lead-details').on('click', function(e) {
            e.preventDefault();
            viewLeadDetails($(this).data('lead-id'));
        });

        // Handle message view
        $('.hp-view-message').on('click', function(e) {
            e.preventDefault();
            viewMessage($(this).data('message'));
        });

        // Handle CSV export
        $('.hp-export-leads').on('click', function(e) {
            e.preventDefault();
            exportLeads();
        });

        // Modal close handlers
        $('.hp-modal-close').on('click', function() {
            $(this).closest('.hp-modal').fadeOut();
        });

        // Close modal on outside click
        $('.hp-modal').on('click', function(e) {
            if (e.target === this) {
                $(this).fadeOut();
            }
        });
    }

    /**
     * Update lead status
     */
    function updateLeadStatus($select) {
        var leadId = $select.data('lead-id');
        var newStatus = $select.val();
        var $row = $select.closest('tr');

        // Show loading state
        $select.prop('disabled', true);

        $.ajax({
            url: hp_leads_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hp_update_lead_status',
                lead_id: leadId,
                status: newStatus,
                nonce: hp_leads_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Flash row to indicate success
                    $row.css('background-color', '#d4edda');
                    setTimeout(function() {
                        $row.css('background-color', '');
                    }, 1000);
                    
                    showNotice('success', response.data.message);
                } else {
                    showNotice('error', response.data.message || 'Failed to update status');
                    // Revert selection
                    $select.val($select.data('original-value'));
                }
            },
            error: function() {
                showNotice('error', 'Network error. Please try again.');
                $select.val($select.data('original-value'));
            },
            complete: function() {
                $select.prop('disabled', false);
            }
        });
    }

    /**
     * Delete lead
     */
    function deleteLead($button) {
        if (!confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
            return;
        }

        var leadId = $button.data('lead-id');
        var $row = $button.closest('tr');

        // Show loading state
        $button.prop('disabled', true).text('Deleting...');

        $.ajax({
            url: hp_leads_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hp_delete_lead',
                lead_id: leadId,
                nonce: hp_leads_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        
                        // Check if table is empty
                        if ($('.wp-list-table tbody tr').length === 0) {
                            $('.wp-list-table tbody').html(
                                '<tr><td colspan="8" class="no-items">No leads found.</td></tr>'
                            );
                        }
                    });
                    
                    showNotice('success', response.data.message);
                } else {
                    showNotice('error', response.data.message || 'Failed to delete lead');
                }
            },
            error: function() {
                showNotice('error', 'Network error. Please try again.');
            },
            complete: function() {
                $button.prop('disabled', false).text('Delete');
            }
        });
    }

    /**
     * Open note modal
     */
    function openNoteModal(leadId) {
        $('#note-lead-id').val(leadId);
        $('#note-content').val('');
        $('#hp-add-note-modal').fadeIn();
        $('#note-content').focus();
    }

    /**
     * Submit note
     */
    function submitNote() {
        var leadId = $('#note-lead-id').val();
        var noteContent = $('#note-content').val().trim();

        if (!noteContent) {
            alert('Please enter a note');
            return;
        }

        var $submitBtn = $('#hp-add-note-form button[type="submit"]');
        $submitBtn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: hp_leads_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hp_add_lead_note',
                lead_id: leadId,
                note: noteContent,
                nonce: hp_leads_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#hp-add-note-modal').fadeOut();
                    showNotice('success', response.data.message);
                    
                    // Update UI to show note was added
                    var $row = $('tr[data-lead-id="' + leadId + '"]');
                    $row.find('.hp-add-note').text('Note ✓');
                } else {
                    showNotice('error', response.data.message || 'Failed to add note');
                }
            },
            error: function() {
                showNotice('error', 'Network error. Please try again.');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text('Add Note');
            }
        });
    }

    /**
     * View lead details
     */
    function viewLeadDetails(leadId) {
        // This would typically load detailed lead information via AJAX
        // For now, we'll show a placeholder
        var $modal = $('#hp-lead-details-modal');
        var $content = $modal.find('.hp-lead-details-content');
        
        $content.html('<p>Loading lead details...</p>');
        $modal.fadeIn();
        
        // In a real implementation, you would fetch lead details via AJAX
        setTimeout(function() {
            $content.html(
                '<div class="lead-details">' +
                '<p><strong>Lead ID:</strong> ' + leadId + '</p>' +
                '<p><strong>Status:</strong> Active</p>' +
                '<p><strong>Last Contact:</strong> 2 days ago</p>' +
                '<p><strong>Notes:</strong> Follow up scheduled for next week.</p>' +
                '</div>'
            );
        }, 500);
    }

    /**
     * View message
     */
    function viewMessage(message) {
        alert(message);
    }

    /**
     * Export leads to CSV
     */
    function exportLeads() {
        var $exportBtn = $('.hp-export-leads');
        $exportBtn.text('Exporting...').prop('disabled', true);

        $.ajax({
            url: hp_leads_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hp_export_leads',
                nonce: hp_leads_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Create and download CSV file
                    var blob = new Blob([response.data.csv], { type: 'text/csv' });
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = response.data.filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                    
                    showNotice('success', 'Leads exported successfully');
                } else {
                    showNotice('error', response.data.message || 'Failed to export leads');
                }
            },
            error: function() {
                showNotice('error', 'Network error. Please try again.');
            },
            complete: function() {
                $exportBtn.text('Export CSV').prop('disabled', false);
            }
        });
    }

    /**
     * Show admin notice
     */
    function showNotice(type, message) {
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var $notice = $(
            '<div class="notice ' + noticeClass + ' is-dismissible">' +
            '<p>' + message + '</p>' +
            '<button type="button" class="notice-dismiss"></button>' +
            '</div>'
        );

        $('.wrap h1').after($notice);

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);

        // Handle manual dismiss
        $notice.find('.notice-dismiss').on('click', function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        });
    }

})(jQuery);