/**
 * Happy Place Lead Forms JavaScript
 * 
 * Handles form validation, AJAX submission, and user feedback
 */

(function($) {
    'use strict';

    // Wait for DOM ready
    $(document).ready(function() {
        initLeadForms();
    });

    /**
     * Initialize lead forms
     */
    function initLeadForms() {
        // Find all lead forms on the page
        $('.hp-lead-form').each(function() {
            initForm($(this));
        });
    }

    /**
     * Initialize individual form
     */
    function initForm($form) {
        // Handle form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            submitForm($form);
        });

        // Real-time validation
        $form.find('input[required], textarea[required]').on('blur', function() {
            validateField($(this));
        });

        // Email validation
        $form.find('input[type="email"]').on('blur', function() {
            validateEmail($(this));
        });

        // Phone validation and formatting
        $form.find('input[type="tel"]').on('input', function() {
            formatPhone($(this));
        });
    }

    /**
     * Submit form via AJAX
     */
    function submitForm($form) {
        // Check if already submitting
        if ($form.hasClass('submitting')) {
            return;
        }

        // Validate all fields
        var isValid = true;
        $form.find('input[required], textarea[required], select[required]').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        // Validate email fields
        $form.find('input[type="email"]').each(function() {
            if (!validateEmail($(this))) {
                isValid = false;
            }
        });

        if (!isValid) {
            showMessage($form, 'error', hp_lead_forms.messages.validation);
            return;
        }

        // Show loading state
        $form.addClass('submitting');
        $form.find('.hp-btn-text').hide();
        $form.find('.hp-btn-loading').show();
        $form.find('button[type="submit"]').prop('disabled', true);

        // Prepare form data
        var formData = new FormData($form[0]);
        formData.append('action', 'hp_submit_lead');
        formData.append('nonce', hp_lead_forms.nonce);

        // Submit via AJAX
        $.ajax({
            url: hp_lead_forms.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showMessage($form, 'success', response.data.message || hp_lead_forms.messages.success);
                    
                    // Reset form
                    $form[0].reset();
                    
                    // Trigger custom event
                    $form.trigger('hp_lead_form_success', [response.data]);
                    
                    // Scroll to message
                    scrollToMessage($form);
                } else {
                    // Show error message
                    showMessage($form, 'error', response.data.message || hp_lead_forms.messages.error);
                    
                    // Show field-specific errors
                    if (response.data.errors) {
                        showFieldErrors($form, response.data.errors);
                    }
                }
            },
            error: function() {
                showMessage($form, 'error', hp_lead_forms.messages.error);
            },
            complete: function() {
                // Reset loading state
                $form.removeClass('submitting');
                $form.find('.hp-btn-text').show();
                $form.find('.hp-btn-loading').hide();
                $form.find('button[type="submit"]').prop('disabled', false);
            }
        });
    }

    /**
     * Validate individual field
     */
    function validateField($field) {
        var value = $field.val().trim();
        var isValid = true;

        if ($field.prop('required') && !value) {
            showFieldError($field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError($field);
        }

        return isValid;
    }

    /**
     * Validate email field
     */
    function validateEmail($field) {
        var email = $field.val().trim();
        var isValid = true;

        if (email && !isValidEmail(email)) {
            showFieldError($field, hp_lead_forms.messages.email_invalid);
            isValid = false;
        } else if ($field.prop('required') && !email) {
            showFieldError($field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError($field);
        }

        return isValid;
    }

    /**
     * Check if email is valid
     */
    function isValidEmail(email) {
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    /**
     * Format phone number
     */
    function formatPhone($field) {
        var value = $field.val().replace(/\D/g, '');
        var formatted = '';

        if (value.length > 0) {
            if (value.length <= 3) {
                formatted = '(' + value;
            } else if (value.length <= 6) {
                formatted = '(' + value.slice(0, 3) + ') ' + value.slice(3);
            } else if (value.length <= 10) {
                formatted = '(' + value.slice(0, 3) + ') ' + value.slice(3, 6) + '-' + value.slice(6);
            } else {
                formatted = '(' + value.slice(0, 3) + ') ' + value.slice(3, 6) + '-' + value.slice(6, 10);
            }
        }

        $field.val(formatted);
    }

    /**
     * Show form message
     */
    function showMessage($form, type, message) {
        var $messageDiv = $form.find('.hp-form-messages');
        
        $messageDiv
            .removeClass('success error')
            .addClass(type)
            .html('<p>' + message + '</p>')
            .slideDown();

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                $messageDiv.slideUp();
            }, 5000);
        }
    }

    /**
     * Show field-specific errors
     */
    function showFieldErrors($form, errors) {
        $.each(errors, function(field, message) {
            var $field = $form.find('[name="' + field + '"]');
            if ($field.length) {
                showFieldError($field, message);
            }
        });
    }

    /**
     * Show field error
     */
    function showFieldError($field, message) {
        var $group = $field.closest('.hp-form-group');
        $group.addClass('has-error');
        
        // Remove existing error
        $group.find('.field-error').remove();
        
        // Add new error
        $field.after('<span class="field-error">' + message + '</span>');
    }

    /**
     * Clear field error
     */
    function clearFieldError($field) {
        var $group = $field.closest('.hp-form-group');
        $group.removeClass('has-error');
        $group.find('.field-error').remove();
    }

    /**
     * Scroll to message
     */
    function scrollToMessage($form) {
        var $messageDiv = $form.find('.hp-form-messages');
        if ($messageDiv.length) {
            $('html, body').animate({
                scrollTop: $messageDiv.offset().top - 100
            }, 500);
        }
    }

})(jQuery);
