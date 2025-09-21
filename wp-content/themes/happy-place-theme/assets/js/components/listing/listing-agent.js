/**
 * HPH Agent Card with Lead Form JavaScript
 * Location: /wp-content/themes/happy-place/assets/js/agent-lead-form.js
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';

    /**
     * HPH Agent Lead Form Class
     */
    class HPHAgentLeadForm {
        constructor(element) {
            this.$element = $(element);
            this.$form = this.$element.find('.hph-lead-form');
            this.formId = this.$form.attr('id');
            this.currentTab = 'contact';
            this.formData = {};
            
            this.init();
        }

        /**
         * Initialize form
         */
        init() {
            this.cacheElements();
            this.bindEvents();
            this.initValidation();
            this.setupCharacterCount();
        }

        /**
         * Cache DOM elements
         */
        cacheElements() {
            // Form elements
            this.$tabBtns = this.$element.find('.hph-tab-btn');
            this.$tabContents = this.$element.find('.hph-form-tab');
            this.$submitBtn = this.$form.find('.hph-submit-btn');
            this.$successMessage = this.$form.find('.hph-success-message');
            this.$errorMessage = this.$form.find('.hph-form-message.hph-error-message');
            
            // Form fields
            this.$nameField = this.$form.find('#lead_name');
            this.$emailField = this.$form.find('#lead_email');
            this.$phoneField = this.$form.find('#lead_phone');
            this.$messageField = this.$form.find('#lead_message');
            this.$consentField = this.$form.find('input[name="consent"]');
            
            // Quick messages
            this.$quickMessages = this.$element.find('.hph-quick-msg');
            
            // Agent details
            this.$detailsToggle = this.$element.find('.hph-details-toggle');
            this.$detailsContent = this.$element.find('.hph-details-content');
            
            // Modal elements (if applicable)
            this.$modalOverlay = $('#agent-modal-overlay');
            this.$modalClose = this.$modalOverlay.find('.hph-modal-close');
        }

        /**
         * Bind events
         */
        bindEvents() {
            // Tab switching
            this.$tabBtns.on('click', (e) => this.switchTab($(e.currentTarget)));
            
            // Form submission
            this.$form.on('submit', (e) => this.handleSubmit(e));
            
            // Quick messages
            this.$quickMessages.on('click', (e) => this.insertQuickMessage($(e.currentTarget)));
            
            // Field validation
            this.$nameField.on('blur', () => this.validateField(this.$nameField));
            this.$emailField.on('blur', () => this.validateField(this.$emailField));
            this.$phoneField.on('blur', () => this.validateField(this.$phoneField));
            
            // Phone formatting
            this.$phoneField.on('input', () => this.formatPhone());
            
            // Character counter
            this.$messageField.on('input', () => this.updateCharCount());
            
            // Agent details toggle
            this.$detailsToggle.on('click', () => this.toggleDetails());
            
            // Modal controls
            this.$modalClose.on('click', () => this.closeModal());
            this.$modalOverlay.on('click', (e) => {
                if ($(e.target).is(this.$modalOverlay)) {
                    this.closeModal();
                }
            });
            
            // Escape key to close modal
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.$modalOverlay.hasClass('active')) {
                    this.closeModal();
                }
            });
        }

        /**
         * Switch tab
         */
        switchTab($btn) {
            const tab = $btn.data('tab');
            
            // Update buttons
            this.$tabBtns.removeClass('active');
            $btn.addClass('active');
            
            // Update content
            this.$tabContents.removeClass('active');
            this.$tabContents.filter(`[data-tab-content="${tab}"]`).addClass('active');
            
            // Update form type
            this.$form.find('input[name="form_type"]').val(tab);
            
            // Update submit button text
            this.updateSubmitButton(tab);
            
            this.currentTab = tab;
        }

        /**
         * Update submit button text
         */
        updateSubmitButton(tab) {
            const texts = {
                'contact': 'Send Message',
                'tour': 'Schedule Tour',
                'finance': 'Get Pre-Approved'
            };
            
            this.$submitBtn.find('.hph-btn-text').text(texts[tab] || 'Submit');
        }

        /**
         * Insert quick message
         */
        insertQuickMessage($btn) {
            const message = $btn.data('message');
            this.$messageField.val(message);
            this.updateCharCount();
            
            // Visual feedback
            $btn.addClass('selected');
            setTimeout(() => {
                $btn.removeClass('selected');
            }, 1000);
        }

        /**
         * Toggle agent details
         */
        toggleDetails() {
            const isExpanded = this.$detailsToggle.attr('aria-expanded') === 'true';
            
            if (isExpanded) {
                this.$detailsToggle.attr('aria-expanded', 'false');
                this.$detailsContent.removeClass('active');
                this.$detailsToggle.find('span').text('View Agent Details');
            } else {
                this.$detailsToggle.attr('aria-expanded', 'true');
                this.$detailsContent.addClass('active');
                this.$detailsToggle.find('span').text('Hide Agent Details');
            }
        }

        /**
         * Initialize validation
         */
        initValidation() {
            // Email regex
            this.emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // Phone regex (US format)
            this.phoneRegex = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
        }

        /**
         * Validate field
         */
        validateField($field) {
            const $group = $field.closest('.hph-form-group');
            const value = $field.val().trim();
            const isRequired = $field.prop('required');
            let isValid = true;
            let errorMessage = '';
            
            // Clear previous error
            $group.removeClass('error');
            
            // Check if required and empty
            if (isRequired && !value) {
                isValid = false;
                errorMessage = 'This field is required';
            }
            
            // Specific field validation
            if (value) {
                if ($field.attr('type') === 'email' && !this.emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address';
                }
                
                if ($field.attr('type') === 'tel' && !this.phoneRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid phone number';
                }
            }
            
            // Show error if invalid
            if (!isValid) {
                $group.addClass('error');
                $group.find('.hph-error-message').text(errorMessage);
            }
            
            return isValid;
        }

        /**
         * Format phone number
         */
        formatPhone() {
            let value = this.$phoneField.val().replace(/\D/g, '');
            
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = `(${value}`;
                } else if (value.length <= 6) {
                    value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
                } else {
                    value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
                }
            }
            
            this.$phoneField.val(value);
        }

        /**
         * Setup character count
         */
        setupCharacterCount() {
            const maxLength = 500;
            this.$messageField.attr('maxlength', maxLength);
        }

        /**
         * Update character count
         */
        updateCharCount() {
            const currentLength = this.$messageField.val().length;
            const maxLength = 500;
            this.$element.find('.hph-char-count').text(`${currentLength}/${maxLength}`);
        }

        /**
         * Handle form submission
         */
        handleSubmit(e) {
            e.preventDefault();
            
            // Reset messages
            this.$successMessage.hide();
            this.$errorMessage.hide();
            
            // Validate all required fields
            let isValid = true;
            
            // Validate based on current tab
            if (this.currentTab === 'contact' || this.currentTab === 'tour') {
                isValid = this.validateField(this.$nameField) && isValid;
                isValid = this.validateField(this.$emailField) && isValid;
                isValid = this.validateField(this.$phoneField) && isValid;
            }
            
            // Validate consent
            if (!this.$consentField.is(':checked')) {
                isValid = false;
                this.$consentField.closest('.hph-form-consent').find('.hph-error-message').text('Please accept the terms').show();
            }
            
            if (!isValid) {
                this.showNotification('Please fill in all required fields', 'error');
                return;
            }
            
            // Collect form data
            this.collectFormData();
            
            // Show loading state
            this.setLoadingState(true);
            
            // Submit via AJAX
            this.submitForm();
        }

        /**
         * Collect form data
         */
        collectFormData() {
            this.formData = {
                action: 'hph_route_form',
                route_type: 'property_inquiry',
                nonce: window.hphAgentForm.nonce,
                property_id: window.hphAgentForm.propertyId,
                agent_id: window.hphAgentForm.agentId,
                form_type: this.currentTab,
                name: this.$nameField.val(),
                email: this.$emailField.val(),
                phone: this.$phoneField.val(),
                message: this.$messageField.val(),
                consent: this.$consentField.is(':checked') ? 1 : 0
            };
            
            // Add tab-specific data
            if (this.currentTab === 'tour') {
                this.formData.tour_type = this.$form.find('input[name="tour_type"]:checked').val();
                this.formData.tour_date = this.$form.find('#tour_date').val();
                this.formData.tour_time = this.$form.find('#tour_time').val();
                this.formData.tour_reminder = this.$form.find('input[name="tour_reminder"]').is(':checked') ? 1 : 0;
            }
            
            if (this.currentTab === 'finance') {
                this.formData.price_range = this.$form.find('#price_range').val();
                this.formData.purchase_timeline = this.$form.find('#purchase_timeline').val();
                this.formData.first_time_buyer = this.$form.find('input[name="first_time_buyer"]').is(':checked') ? 1 : 0;
            }
            
            // Add hidden fields
            this.formData.property_address = this.$form.find('input[name="property_address"]').val();
            this.formData.property_mls = this.$form.find('input[name="property_mls"]').val();
        }

        /**
         * Submit form via AJAX
         */
        submitForm() {
            $.ajax({
                url: window.hphAgentForm.ajaxUrl,
                type: 'POST',
                data: this.formData,
                success: (response) => {
                    if (response.success) {
                        this.handleSuccess(response.data);
                    } else {
                        this.handleError(response.data?.message || 'Something went wrong. Please try again.');
                    }
                },
                error: (xhr, status, error) => {
                    this.handleError('Network error. Please check your connection and try again.');
                },
                complete: () => {
                    this.setLoadingState(false);
                }
            });
        }

        /**
         * Handle successful submission
         */
        handleSuccess(data) {
            // Show success message
            this.$successMessage.fadeIn();
            
            // Track event
            this.trackEvent('Lead Form Submitted', {
                form_type: this.currentTab,
                property_id: this.formData.property_id,
                agent_id: this.formData.agent_id
            });
            
            // Reset form after delay
            setTimeout(() => {
                this.$form[0].reset();
                this.updateCharCount();
                
                // Switch back to contact tab
                if (this.currentTab !== 'contact') {
                    this.switchTab(this.$tabBtns.filter('[data-tab="contact"]'));
                }
            }, 3000);
            
            // Show notification
            this.showNotification('Thank you! The agent will contact you shortly.', 'success');
            
            // Trigger custom event
            this.$form.trigger('leadSubmitted', [data]);
        }

        /**
         * Handle submission error
         */
        handleError(message) {
            this.$errorMessage.find('span').text(message);
            this.$errorMessage.fadeIn();
            
            // Show notification
            this.showNotification(message, 'error');
            
            // Track error
            this.trackEvent('Lead Form Error', {
                error: message,
                form_type: this.currentTab
            });
        }

        /**
         * Set loading state
         */
        setLoadingState(isLoading) {
            if (isLoading) {
                this.$submitBtn.prop('disabled', true);
                this.$submitBtn.find('.hph-btn-text').hide();
                this.$submitBtn.find('.hph-btn-loading').show();
            } else {
                this.$submitBtn.prop('disabled', false);
                this.$submitBtn.find('.hph-btn-text').show();
                this.$submitBtn.find('.hph-btn-loading').hide();
            }
        }

        /**
         * Show notification
         */
        showNotification(message, type = 'info') {
            const $notification = $('<div class="hph-notification">')
                .addClass(`hph-notification-${type}`)
                .html(`
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                `)
                .appendTo('body');
            
            setTimeout(() => {
                $notification.addClass('show');
            }, 100);
            
            setTimeout(() => {
                $notification.removeClass('show');
                setTimeout(() => {
                    $notification.remove();
                }, 300);
            }, 5000);
        }

        /**
         * Track event (GA4, GTM, etc.)
         */
        trackEvent(eventName, parameters = {}) {
            // Google Analytics 4
            if (typeof gtag !== 'undefined') {
                gtag('event', eventName, parameters);
            }
            
            // Google Tag Manager
            if (typeof dataLayer !== 'undefined') {
                dataLayer.push({
                    event: eventName,
                    ...parameters
                });
            }
            
            // Facebook Pixel
            if (typeof fbq !== 'undefined') {
                fbq('track', 'Lead', parameters);
            }
        }

        /**
         * Open modal
         */
        openModal() {
            this.$modalOverlay.addClass('active');
            $('body').css('overflow', 'hidden');
            
            // Move form content to modal
            const $content = this.$element.clone();
            this.$modalOverlay.find('.hph-agent-modal-content').append($content);
            
            // Reinitialize form in modal
            new HPHAgentLeadForm($content[0]);
        }

        /**
         * Close modal
         */
        closeModal() {
            this.$modalOverlay.removeClass('active');
            $('body').css('overflow', '');
            
            // Clear modal content
            this.$modalOverlay.find('.hph-agent-modal-content').find('.hph-agent-lead-form').remove();
        }
    }

    /**
     * Initialize all agent lead forms
     */
    $(document).ready(function() {
        $('.hph-agent-lead-form').each(function() {
            new HPHAgentLeadForm(this);
        });
        
        // Handle modal triggers
        $('[data-agent-modal]').on('click', function(e) {
            e.preventDefault();
            const targetId = $(this).data('agent-modal');
            const $target = $('#' + targetId);
            if ($target.length) {
                const form = $target.data('agentForm');
                if (form) {
                    form.openModal();
                }
            }
        });
    });

    /**
     * jQuery plugin
     */
    $.fn.hphAgentLeadForm = function(options) {
        return this.each(function() {
            if (!$(this).data('agentForm')) {
                $(this).data('agentForm', new HPHAgentLeadForm(this));
            }
        });
    };

})(jQuery);

/**
 * AJAX Handler (PHP side would handle this)
 * This would go in your theme's functions.php or a plugin
 */
/*
add_action('wp_ajax_hph_submit_agent_lead', 'handle_agent_lead_submission');
add_action('wp_ajax_nopriv_hph_submit_agent_lead', 'handle_agent_lead_submission');

function handle_agent_lead_submission() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hph_agent_form_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    // Sanitize data
    $data = [
        'name' => sanitize_text_field($_POST['name']),
        'email' => sanitize_email($_POST['email']),
        'phone' => sanitize_text_field($_POST['phone']),
        'message' => sanitize_textarea_field($_POST['message']),
        'property_id' => intval($_POST['property_id']),
        'agent_id' => intval($_POST['agent_id']),
        'form_type' => sanitize_text_field($_POST['form_type']),
        // ... additional fields
    ];
    
    // Save to database
    global $wpdb;
    $result = $wpdb->insert(
        $wpdb->prefix . 'property_leads',
        $data
    );
    
    if ($result) {
        // Send email notifications
        $agent_email = get_user_meta($data['agent_id'], 'email', true);
        
        // Email to agent
        wp_mail(
            $agent_email,
            'New Property Inquiry',
            'You have a new lead...',
            ['Content-Type: text/html; charset=UTF-8']
        );
        
        // Email to lead
        wp_mail(
            $data['email'],
            'Thank you for your inquiry',
            'We received your message...',
            ['Content-Type: text/html; charset=UTF-8']
        );
        
        wp_send_json_success(['message' => 'Lead submitted successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to save lead']);
    }
}
*/

/**
 * Notification styles (inline for quick loading)
 */
const notificationStyles = `
<style>
.hph-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: var(--hph-gray-900);
    color: var(--hph-white);
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    max-width: 400px;
}

.hph-notification.show {
    transform: translateY(0);
    opacity: 1;
}

.hph-notification-success {
    background: var(--hph-success);
}

.hph-notification-error {
    background: var(--hph-danger);
}

.hph-notification i {
    font-size: 1.25rem;
}

.hph-quick-msg.selected {
    background: var(--hph-primary);
    color: var(--hph-white);
    border-color: var(--hph-primary);
}
</style>
`;

// Add notification styles to head
document.head.insertAdjacentHTML('beforeend', notificationStyles);
