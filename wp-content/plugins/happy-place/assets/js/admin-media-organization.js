/**
 * Media Organization Admin JavaScript
 * Place in: /assets/js/admin/media-organization.js
 */

(function($) {
    'use strict';
    
    const MediaOrganizer = {
        processing: false,
        batchOffset: 0,
        
        init: function() {
            this.bindEvents();
            this.initDragDrop();
        },
        
        bindEvents: function() {
            // Find orphaned media
            $('#find-orphaned').on('click', this.findOrphanedMedia);
            
            // Bulk organize
            $('.bulk-organize').on('click', this.bulkOrganize);
            
            // Watch for media modal
            if (wp.media) {
                wp.media.view.Modal.prototype.on('open', this.enhanceMediaModal);
            }
        },
        
        initDragDrop: function() {
            // Add drag-drop organization in media library
            if ($('.media-frame-content').length) {
                this.setupDragDropOrganization();
            }
        },
        
        findOrphanedMedia: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $results = $('#orphaned-results');
            
            $button.prop('disabled', true);
            $results.html('<p>Scanning media library...</p>');
            
            $.post(hptMedia.ajaxurl, {
                action: 'hpt_find_orphaned_media',
                nonce: hptMedia.nonce
            }, function(response) {
                if (response.success) {
                    let html = '<h3>Found ' + response.data.count + ' orphaned files</h3>';
                    
                    if (response.data.items.length > 0) {
                        html += '<ul class="orphaned-list">';
                        response.data.items.forEach(function(item) {
                            html += '<li>';
                            html += '<img src="' + item.thumb + '" width="60">';
                            html += '<span>' + item.filename + '</span>';
                            html += '<button class="button assign-to-listing" data-id="' + item.id + '">Assign to Listing</button>';
                            html += '</li>';
                        });
                        html += '</ul>';
                    }
                    
                    $results.html(html);
                }
                
                $button.prop('disabled', false);
            });
        },
        
        bulkOrganize: function(e) {
            e.preventDefault();
            
            if (MediaOrganizer.processing) {
                return;
            }
            
            MediaOrganizer.processing = true;
            MediaOrganizer.batchOffset = 0;
            
            MediaOrganizer.processBatch();
        },
        
        processBatch: function() {
            $.post(hptMedia.ajaxurl, {
                action: 'hpt_process_media_batch',
                nonce: hptMedia.nonce,
                offset: MediaOrganizer.batchOffset
            }, function(response) {
                if (response.success) {
                    MediaOrganizer.batchOffset += response.data.processed;
                    
                    // Update progress
                    MediaOrganizer.updateProgress(response.data.processed);
                    
                    if (!response.data.complete) {
                        // Process next batch
                        setTimeout(function() {
                            MediaOrganizer.processBatch();
                        }, 500);
                    } else {
                        MediaOrganizer.processing = false;
                        MediaOrganizer.showComplete();
                    }
                }
            });
        },
        
        updateProgress: function(count) {
            const $progress = $('#organization-progress');
            
            if (!$progress.length) {
                $('<div id="organization-progress"><div class="progress-bar"><div class="progress-fill"></div></div><p class="progress-text">Processing...</p></div>')
                    .insertAfter('.bulk-organize');
            }
            
            $('.progress-text').text('Processed ' + MediaOrganizer.batchOffset + ' images...');
        },
        
        showComplete: function() {
            $('#organization-progress').html('<p class="success">✓ Organization complete!</p>');
        },
        
        enhanceMediaModal: function() {
            // Add quick metadata fields to media modal
            setTimeout(function() {
                MediaOrganizer.addQuickMetadata();
            }, 100);
        },
        
        addQuickMetadata: function() {
            if ($('.media-sidebar').length && !$('.hpt-quick-metadata').length) {
                const quickFields = `
                    <div class="hpt-quick-metadata">
                        <h3>Quick Organization</h3>
                        <label>
                            <span>Property Area:</span>
                            <select class="quick-property-area">
                                <option value="">— Select —</option>
                                <option value="exterior">Exterior</option>
                                <option value="interior">Interior</option>
                                <option value="kitchen">Kitchen</option>
                                <option value="bedroom">Bedroom</option>
                                <option value="bathroom">Bathroom</option>
                            </select>
                        </label>
                        <button class="button apply-to-selected">Apply to Selected</button>
                    </div>
                `;
                
                $('.media-sidebar').prepend(quickFields);
                
                // Bind apply button
                $('.apply-to-selected').on('click', MediaOrganizer.applyQuickMetadata);
            }
        },
        
        applyQuickMetadata: function(e) {
            e.preventDefault();
            
            const area = $('.quick-property-area').val();
            if (!area) return;
            
            // Get selected attachments
            const selection = wp.media.frame.state().get('selection');
            const ids = selection.map(model => model.id);
            
            $.post(hptMedia.ajaxurl, {
                action: 'hpt_apply_quick_metadata',
                nonce: hptMedia.nonce,
                ids: ids,
                property_area: area
            }, function(response) {
                if (response.success) {
                    // Show success message
                    $('.hpt-quick-metadata').append('<p class="success">Applied to ' + ids.length + ' images</p>');
                    
                    setTimeout(function() {
                        $('.success').fadeOut();
                    }, 2000);
                }
            });
        },
        
        setupDragDropOrganization: function() {
            // Enable drag-drop to organize images into property areas
            $('.attachment').draggable({
                helper: 'clone',
                zIndex: 10000
            });
            
            // Create drop zones
            const dropZones = `
                <div class="hpt-drop-zones">
                    <div class="drop-zone" data-area="exterior">
                        <i class="dashicons dashicons-admin-home"></i>
                        <span>Exterior</span>
                    </div>
                    <div class="drop-zone" data-area="interior">
                        <i class="dashicons dashicons-admin-multisite"></i>
                        <span>Interior</span>
                    </div>
                    <div class="drop-zone" data-area="kitchen">
                        <i class="dashicons dashicons-carrot"></i>
                        <span>Kitchen</span>
                    </div>
                </div>
            `;
            
            if (!$('.hpt-drop-zones').length) {
                $(dropZones).appendTo('.media-frame-content');
            }
            
            $('.drop-zone').droppable({
                accept: '.attachment',
                hoverClass: 'drop-hover',
                drop: function(event, ui) {
                    const attachmentId = ui.draggable.data('id');
                    const area = $(this).data('area');
                    
                    MediaOrganizer.assignArea(attachmentId, area);
                }
            });
        },
        
        assignArea: function(attachmentId, area) {
            $.post(hptMedia.ajaxurl, {
                action: 'hpt_assign_property_area',
                nonce: hptMedia.nonce,
                attachment_id: attachmentId,
                area: area
            }, function(response) {
                if (response.success) {
                    // Visual feedback
                    $('[data-id="' + attachmentId + '"]').addClass('organized-' + area);
                }
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        MediaOrganizer.init();
    });
    
    // Hook into media uploader
    if (window.wp && window.wp.Uploader) {
        $.extend(wp.Uploader.prototype, {
            success: function(attachment) {
                // Auto-categorize on upload
                if (attachment.attributes.filename) {
                    MediaOrganizer.autoCategorize(attachment);
                }
            }
        });
    }
    
})(jQuery);