/**
 * FollowUp Boss Admin JavaScript
 * 
 * Handles admin interface interactions for FollowUp Boss integration
 * 
 * @package HappyPlace
 * @since 3.1.0
 */

(function($) {
    'use strict';

    class FollowUpBossAdmin {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.initPasswordToggle();
            
            // Test connection on page load if API key exists
            if ($('#api_key').val()) {
                this.testConnection();
            }
        }

        bindEvents() {
            // Test connection button
            $('#test-connection').on('click', (e) => {
                e.preventDefault();
                this.testConnection();
            });

            // Auto-test connection when API key changes
            $('#api_key').on('blur', () => {
                if ($('#api_key').val().length > 10) {
                    setTimeout(() => {
                        this.testConnection();
                    }, 500);
                }
            });

            // Sync individual lead buttons (if they exist)
            $(document).on('click', '.sync-lead-button', (e) => {
                e.preventDefault();
                const leadId = $(e.currentTarget).data('lead-id');
                this.syncLead(leadId);
            });

            // Form validation
            $('form').on('submit', (e) => {
                if (!this.validateForm()) {
                    e.preventDefault();
                    return false;
                }
            });
        }

        initPasswordToggle() {
            $('.toggle-password').on('click', function(e) {
                e.preventDefault();
                const targetInput = $('#' + $(this).data('target'));
                const icon = $(this).find('i');
                
                if (targetInput.attr('type') === 'password') {
                    targetInput.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    targetInput.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        }

        testConnection() {
            const $button = $('#test-connection');
            const $status = $('#connection-status');
            const originalText = $button.html();
            
            // Show loading state
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + hpFollowUpBoss.strings.testing);
            
            // Update status to testing
            $status.html(`
                <div class="status-indicator status-unknown">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>${hpFollowUpBoss.strings.testing}</span>
                </div>
            `);

            $.post(hpFollowUpBoss.ajaxUrl, {
                action: 'hp_test_followup_boss_connection',
                nonce: hpFollowUpBoss.nonce
            })
            .done((response) => {
                if (response.success) {
                    this.updateConnectionStatus('connected', response.data.message);
                    this.showNotification(response.data.message, 'success');
                } else {
                    this.updateConnectionStatus('disconnected', response.data.message);
                    this.showNotification(response.data.message, 'error');
                }
            })
            .fail((xhr, status, error) => {
                const message = 'Connection test failed: ' + error;
                this.updateConnectionStatus('disconnected', message);
                this.showNotification(message, 'error');
            })
            .always(() => {
                $button.prop('disabled', false).html(originalText);
            });
        }

        updateConnectionStatus(status, message) {
            const $status = $('#connection-status');
            const icons = {
                connected: 'fas fa-check-circle',
                disconnected: 'fas fa-times-circle',
                unknown: 'fas fa-question-circle'
            };

            $status.html(`
                <div class="status-indicator status-${status}">
                    <i class="${icons[status]}"></i>
                    <span>${message}</span>
                </div>
            `);
        }

        syncLead(leadId) {
            if (!leadId) {
                this.showNotification('Invalid lead ID', 'error');
                return;
            }

            const $button = $(`.sync-lead-button[data-lead-id="${leadId}"]`);
            const originalText = $button.html();

            // Show loading state
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + hpFollowUpBoss.strings.syncing);

            $.post(hpFollowUpBoss.ajaxUrl, {
                action: 'hp_sync_lead_to_followup_boss',
                lead_id: leadId,
                nonce: hpFollowUpBoss.nonce
            })
            .done((response) => {
                if (response.success) {
                    this.showNotification(hpFollowUpBoss.strings.syncSuccess, 'success');
                    $button.removeClass('button-primary').addClass('button-secondary')
                           .html('<i class="fas fa-check"></i> Synced');
                } else {
                    this.showNotification(response.data.message || hpFollowUpBoss.strings.syncError, 'error');
                }
            })
            .fail(() => {
                this.showNotification(hpFollowUpBoss.strings.syncError, 'error');
            })
            .always(() => {
                if (!response || !response.success) {
                    $button.prop('disabled', false).html(originalText);
                }
            });
        }

        validateForm() {
            let isValid = true;
            const $apiKey = $('#api_key');
            const $enabled = $('#enabled');

            // Clear previous errors
            $('.form-error').remove();

            // Validate API key if integration is enabled
            if ($enabled.is(':checked') && !$apiKey.val().trim()) {
                this.showFieldError($apiKey, 'API key is required when integration is enabled');
                isValid = false;
            }

            // Validate API key format
            if ($apiKey.val().trim() && !$apiKey.val().startsWith('fka_')) {
                this.showFieldError($apiKey, 'API key should start with "fka_"');
                isValid = false;
            }

            return isValid;
        }

        showFieldError($field, message) {
            $field.addClass('error');
            $field.after(`<div class="form-error" style="color: #d63638; margin-top: 5px;">${message}</div>`);
        }

        showNotification(message, type = 'info') {
            // Create notification container if it doesn't exist
            if (!$('.hp-notifications').length) {
                $('body').append('<div class="hp-notifications"></div>');
            }

            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };

            const $notification = $(`
                <div class="hp-notification hp-notification--${type}">
                    <div class="hp-notification__content">
                        <i class="${icons[type]}"></i>
                        <span>${message}</span>
                    </div>
                    <button class="hp-notification__close" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);

            $('.hp-notifications').append($notification);

            // Auto-hide after 5 seconds
            setTimeout(() => {
                $notification.addClass('hp-notification--fade-out');
                setTimeout(() => $notification.remove(), 300);
            }, 5000);

            // Manual close
            $notification.find('.hp-notification__close').on('click', () => {
                $notification.addClass('hp-notification--fade-out');
                setTimeout(() => $notification.remove(), 300);
            });
        }

        // Utility method to get form data as object
        getFormData($form) {
            const formData = {};
            $form.serializeArray().forEach(item => {
                if (formData[item.name]) {
                    if (Array.isArray(formData[item.name])) {
                        formData[item.name].push(item.value);
                    } else {
                        formData[item.name] = [formData[item.name], item.value];
                    }
                } else {
                    formData[item.name] = item.value;
                }
            });
            return formData;
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        if ($('#api_key').length) {
            new FollowUpBossAdmin();
        }
    });

})(jQuery);

// Additional CSS for notifications (injected via JS)
const notificationStyles = `
<style>
.hp-notifications {
    position: fixed;
    top: 32px;
    right: 20px;
    z-index: 999999;
    max-width: 400px;
}

.hp-notification {
    background: white;
    border-left: 4px solid #0073aa;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    margin-bottom: 10px;
    padding: 0;
    transition: all 0.3s ease;
    transform: translateX(100%);
    animation: slideInRight 0.3s ease forwards;
}

@keyframes slideInRight {
    to {
        transform: translateX(0);
    }
}

.hp-notification--fade-out {
    transform: translateX(100%);
    opacity: 0;
}

.hp-notification--success {
    border-left-color: #46b450;
}

.hp-notification--error {
    border-left-color: #d63638;
}

.hp-notification--warning {
    border-left-color: #ffb900;
}

.hp-notification__content {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    padding-right: 45px;
}

.hp-notification__content i {
    font-size: 16px;
}

.hp-notification--success .hp-notification__content i {
    color: #46b450;
}

.hp-notification--error .hp-notification__content i {
    color: #d63638;
}

.hp-notification--warning .hp-notification__content i {
    color: #ffb900;
}

.hp-notification--info .hp-notification__content i {
    color: #0073aa;
}

.hp-notification__close {
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-size: 14px;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hp-notification__close:hover {
    color: #333;
}

.form-error {
    font-size: 12px;
    margin-top: 5px;
}

input.error {
    border-color: #d63638;
    box-shadow: 0 0 2px rgba(214, 54, 56, 0.5);
}
</style>
`;

// Inject styles
document.head.insertAdjacentHTML('beforeend', notificationStyles);