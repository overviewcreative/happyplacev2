/**
 * Frontend Admin Dashboard JavaScript
 * 
 * Comprehensive dashboard functionality using HPH Framework
 */

(function($) {
    'use strict';

    /**
     * Dashboard Manager
     */
    var DashboardManager = {
        
        /**
         * Initialize dashboard
         */
        init: function() {
            this.initNavigation();
            this.initModals();
            this.initNotifications();
            this.initGlobalActions();
            this.initMobileMenu();
            this.bindEvents();
            
            console.log('Dashboard initialized');
        },

        /**
         * Initialize navigation
         */
        initNavigation: function() {
            // Handle active states
            $('.nav-link').on('click', function() {
                $('.nav-item').removeClass('active');
                $(this).closest('.nav-item').addClass('active');
            });

            // Handle section switching
            $('[data-section]').on('click', function(e) {
                e.preventDefault();
                const section = $(this).data('section');
                DashboardManager.switchSection(section);
            });
        },

        /**
         * Initialize modal system
         */
        initModals: function() {
            // Create modal container if it doesn't exist
            if ($('#hph-dashboard-modals').length === 0) {
                $('body').append('<div id="hph-dashboard-modals"></div>');
            }

            // Handle modal triggers
            $('[data-modal]').on('click', function(e) {
                e.preventDefault();
                const modalType = $(this).data('modal');
                const modalData = $(this).data();
                DashboardManager.openModal(modalType, modalData);
            });
        },

        /**
         * Initialize notification system
         */
        initNotifications: function() {
            // Auto-dismiss alerts
            $('.alert-dismissible').each(function() {
                const $alert = $(this);
                setTimeout(function() {
                    $alert.fadeOut();
                }, 5000);
            });
        },

        /**
         * Initialize global actions
         */
        initGlobalActions: function() {
            // Refresh data
            $('#refresh-data').on('click', function() {
                DashboardManager.refreshCurrentSection();
            });

            // Global search
            $('#global-search').on('input', debounce(function() {
                DashboardManager.performGlobalSearch($(this).val());
            }, 300));
        },

        /**
         * Initialize mobile menu
         */
        initMobileMenu: function() {
            // Add mobile toggle button if it doesn't exist
            if ($('.dashboard-mobile-toggle').length === 0) {
                $('body').prepend(`
                    <button class="dashboard-mobile-toggle">
                        <span class="hph-icon-menu"></span>
                    </button>
                `);
            }

            // Handle mobile menu toggle
            $('.dashboard-mobile-toggle').on('click', function() {
                $('.hph-dashboard-sidebar').toggleClass('active');
                $('body').toggleClass('sidebar-open');
            });

            // Close sidebar on overlay click
            $(document).on('click', function(e) {
                if ($(window).width() <= 991 && 
                    !$(e.target).closest('.hph-dashboard-sidebar, .dashboard-mobile-toggle').length) {
                    $('.hph-dashboard-sidebar').removeClass('active');
                    $('body').removeClass('sidebar-open');
                }
            });
        },

        /**
         * Bind global events
         */
        bindEvents: function() {
            // Handle form submissions
            $(document).on('submit', '.dashboard-form', function(e) {
                e.preventDefault();
                DashboardManager.handleFormSubmission($(this));
            });

            // Handle delete confirmations
            $(document).on('click', '[data-confirm-delete]', function(e) {
                e.preventDefault();
                const message = $(this).data('confirm-delete') || hph_dashboard.strings.confirm_delete;
                if (confirm(message)) {
                    DashboardManager.handleDelete($(this));
                }
            });

            // Handle bulk actions
            $(document).on('change', '.bulk-action-select', function() {
                DashboardManager.handleBulkAction();
            });

            // Handle keyboard shortcuts
            $(document).on('keydown', function(e) {
                DashboardManager.handleKeyboardShortcuts(e);
            });
        },

        /**
         * Switch dashboard section
         */
        switchSection: function(section) {
            const url = new URL(window.location);
            url.searchParams.set('dashboard_section', section);
            window.location.href = url.toString();
        },

        /**
         * Refresh current section
         */
        refreshCurrentSection: function() {
            this.showLoadingOverlay();
            
            // Get current section content
            const currentSection = hph_dashboard.current_section;
            
            $.ajax({
                url: hph_dashboard.ajax_url,
                type: 'POST',
                data: {
                    action: 'hph_refresh_section',
                    section: currentSection,
                    nonce: hph_dashboard.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.hph-dashboard-content').html(response.data.content);
                        DashboardManager.showNotification(
                            hph_dashboard.strings.success, 
                            'Section refreshed successfully', 
                            'success'
                        );
                    } else {
                        DashboardManager.showNotification(
                            hph_dashboard.strings.error, 
                            response.data.message, 
                            'error'
                        );
                    }
                },
                error: function() {
                    DashboardManager.showNotification(
                        hph_dashboard.strings.error, 
                        'Failed to refresh section', 
                        'error'
                    );
                },
                complete: function() {
                    DashboardManager.hideLoadingOverlay();
                }
            });
        },

        /**
         * Handle form submission
         */
        handleFormSubmission: function($form) {
            const formData = new FormData($form[0]);
            formData.append('nonce', hph_dashboard.nonce);

            const submitButton = $form.find('[type="submit"]');
            const originalText = submitButton.text();

            $.ajax({
                url: hph_dashboard.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    submitButton.prop('disabled', true).text(hph_dashboard.strings.loading);
                },
                success: function(response) {
                    if (response.success) {
                        DashboardManager.showNotification(
                            hph_dashboard.strings.success,
                            response.data.message,
                            'success'
                        );
                        
                        if (response.data.redirect) {
                            setTimeout(function() {
                                window.location.href = response.data.redirect;
                            }, 1500);
                        }
                        
                        if (response.data.refresh) {
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    } else {
                        DashboardManager.showNotification(
                            hph_dashboard.strings.error,
                            response.data.message,
                            'error'
                        );
                    }
                },
                error: function() {
                    DashboardManager.showNotification(
                        hph_dashboard.strings.error,
                        'An error occurred while submitting the form',
                        'error'
                    );
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Handle delete actions
         */
        handleDelete: function($element) {
            const deleteData = $element.data();
            
            $.ajax({
                url: hph_dashboard.ajax_url,
                type: 'POST',
                data: {
                    action: deleteData.action || 'hph_delete_item',
                    id: deleteData.id,
                    type: deleteData.type,
                    nonce: hph_dashboard.nonce
                },
                beforeSend: function() {
                    $element.prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        // Remove element or refresh section
                        if (deleteData.removeElement) {
                            $element.closest(deleteData.removeElement).fadeOut();
                        } else {
                            DashboardManager.refreshCurrentSection();
                        }
                        
                        DashboardManager.showNotification(
                            hph_dashboard.strings.success,
                            response.data.message,
                            'success'
                        );
                    } else {
                        DashboardManager.showNotification(
                            hph_dashboard.strings.error,
                            response.data.message,
                            'error'
                        );
                    }
                },
                error: function() {
                    DashboardManager.showNotification(
                        hph_dashboard.strings.error,
                        'Failed to delete item',
                        'error'
                    );
                },
                complete: function() {
                    $element.prop('disabled', false);
                }
            });
        },

        /**
         * Handle bulk actions
         */
        handleBulkAction: function() {
            const selectedItems = $('.bulk-select:checked');
            const action = $('.bulk-action-dropdown').val();
            
            if (selectedItems.length === 0) {
                alert('Please select items to perform bulk action');
                return;
            }
            
            if (!action) {
                alert('Please select an action');
                return;
            }
            
            const ids = selectedItems.map(function() {
                return $(this).val();
            }).get();
            
            $.ajax({
                url: hph_dashboard.ajax_url,
                type: 'POST',
                data: {
                    action: 'hph_bulk_action',
                    bulk_action: action,
                    ids: ids,
                    nonce: hph_dashboard.nonce
                },
                success: function(response) {
                    if (response.success) {
                        DashboardManager.showNotification(
                            hph_dashboard.strings.success,
                            response.data.message,
                            'success'
                        );
                        DashboardManager.refreshCurrentSection();
                    } else {
                        DashboardManager.showNotification(
                            hph_dashboard.strings.error,
                            response.data.message,
                            'error'
                        );
                    }
                }
            });
        },

        /**
         * Handle keyboard shortcuts
         */
        handleKeyboardShortcuts: function(e) {
            // Ctrl/Cmd + S to save forms
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const $form = $('.dashboard-form:visible').first();
                if ($form.length) {
                    $form.submit();
                }
            }
            
            // Escape to close modals
            if (e.key === 'Escape') {
                this.closeModal();
            }
        },

        /**
         * Open modal
         */
        openModal: function(modalType, data) {
            const modalContent = this.getModalContent(modalType, data);
            
            const modal = $(`
                <div class="hph-modal-overlay">
                    <div class="hph-modal">
                        <div class="hph-modal-header">
                            <h3 class="hph-modal-title">${data.title || 'Modal'}</h3>
                            <button type="button" class="hph-modal-close">&times;</button>
                        </div>
                        <div class="hph-modal-body">
                            ${modalContent}
                        </div>
                    </div>
                </div>
            `);
            
            $('#hph-dashboard-modals').html(modal);
            modal.fadeIn();
            
            // Bind close events
            modal.find('.hph-modal-close').on('click', this.closeModal);
            modal.on('click', function(e) {
                if (e.target === this) {
                    DashboardManager.closeModal();
                }
            });
        },

        /**
         * Close modal
         */
        closeModal: function() {
            $('.hph-modal-overlay').fadeOut(function() {
                $(this).remove();
            });
        },

        /**
         * Get modal content based on type
         */
        getModalContent: function(type, data) {
            switch (type) {
                case 'image_gallery':
                    return this.getImageGalleryModal(data);
                case 'agent_details':
                    return this.getAgentDetailsModal(data);
                case 'listing_details':
                    return this.getListingDetailsModal(data);
                default:
                    return '<p>Modal content not found</p>';
            }
        },

        /**
         * Show notification
         */
        showNotification: function(title, message, type = 'info') {
            const typeClasses = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            };
            
            const notification = $(`
                <div class="alert ${typeClasses[type]} alert-dismissible fade show notification" role="alert">
                    <strong>${title}</strong> ${message}
                    <button type="button" class="btn-close" aria-label="Close"></button>
                </div>
            `);
            
            // Add to notification container or create one
            let container = $('#notification-container');
            if (container.length === 0) {
                container = $('<div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;"></div>');
                $('body').append(container);
            }
            
            container.append(notification);
            
            // Auto-dismiss
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Manual dismiss
            notification.find('.btn-close').on('click', function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Show loading overlay
         */
        showLoadingOverlay: function() {
            $('#hph-loading-overlay').fadeIn();
        },

        /**
         * Hide loading overlay
         */
        hideLoadingOverlay: function() {
            $('#hph-loading-overlay').fadeOut();
        },

        /**
         * Perform global search
         */
        performGlobalSearch: function(query) {
            if (query.length < 3) return;
            
            $.ajax({
                url: hph_dashboard.ajax_url,
                type: 'POST',
                data: {
                    action: 'hph_global_search',
                    query: query,
                    nonce: hph_dashboard.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Display search results
                        console.log('Search results:', response.data);
                    }
                }
            });
        }
    };

    /**
     * Utility function: Debounce
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        // Only initialize on dashboard pages
        if ($('.hph-dashboard').length) {
            DashboardManager.init();
        }
    });

    // Make DashboardManager globally available
    window.HappyPlaceDashboard = DashboardManager;

})(jQuery);