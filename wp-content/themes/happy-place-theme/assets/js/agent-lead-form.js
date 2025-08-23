/**
 * Agent Lead Form Component JavaScript
 * Handles form submission, validation, and user interactions
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initAgentLeadForms();
    });

    function initAgentLeadForms() {
        $('.hph-agent-lead-form').each(function() {
            const $form = $(this);
            const $submitBtn = $form.find('.hph-form-submit');
            const $loadingState = $form.find('.hph-form-loading');
            const $successMessage = $form.find('.hph-form-success');
            const $errorMessage = $form.find('.hph-form-error');

            // Form submission
            $form.on('submit', function(e) {
                e.preventDefault();
                submitForm($form);
            });

            // Real-time validation
            $form.find('input, textarea').on('blur', function() {
                validateField($(this));
            });

            // Phone number formatting
            $form.find('input[type="tel"]').on('input', function() {
                formatPhoneNumber($(this));
            });
        });
    }

    function submitForm($form) {
        const formData = new FormData($form[0]);
        formData.append('action', 'hph_submit_lead_form');
        formData.append('nonce', hphContext.nonce);

        // Show loading state
        $form.addClass('hph-form-loading');
        
        $.ajax({
            url: hphContext.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showSuccess($form, response.data.message);
                    $form[0].reset();
                } else {
                    showError($form, response.data.message);
                }
            },
            error: function() {
                showError($form, 'An error occurred. Please try again.');
            },
            complete: function() {
                $form.removeClass('hph-form-loading');
            }
        });
    }

    function validateField($field) {
        const value = $field.val().trim();
        const type = $field.attr('type');
        const required = $field.prop('required');
        
        let isValid = true;
        let message = '';

        if (required && !value) {
            isValid = false;
            message = 'This field is required.';
        } else if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Please enter a valid email address.';
            }
        } else if (type === 'tel' && value) {
            const phoneRegex = /^\(\d{3}\) \d{3}-\d{4}$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
                message = 'Please enter a valid phone number.';
            }
        }

        // Update field state
        $field.toggleClass('is-invalid', !isValid);
        $field.siblings('.invalid-feedback').text(message);

        return isValid;
    }

    function formatPhoneNumber($input) {
        let value = $input.val().replace(/\D/g, '');
        
        if (value.length >= 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
        }
        
        $input.val(value);
    }

    function showSuccess($form, message) {
        $form.find('.hph-form-success').text(message || 'Thank you! Your message has been sent.').show();
        $form.find('.hph-form-error').hide();
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $form.find('.hph-form-success').fadeOut();
        }, 5000);
    }

    function showError($form, message) {
        $form.find('.hph-form-error').text(message || 'An error occurred. Please try again.').show();
        $form.find('.hph-form-success').hide();
    }

    // Expose functions globally if needed
    window.HPHAgentForm = {
        submit: submitForm,
        validate: validateField,
        formatPhone: formatPhoneNumber
    };

})(jQuery);
