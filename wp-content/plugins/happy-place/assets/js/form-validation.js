/**
 * Form Validation JavaScript
 * Provides comprehensive form validation functionality
 * 
 * @package HappyPlace
 * @version 1.0.0
 */

(function($) {
    'use strict';

    const FormValidation = {
        
        init: function() {
            this.bindEvents();
            this.initializeValidationRules();
        },

        bindEvents: function() {
            // Real-time validation on blur
            $(document).on('blur', '.form-control', this.validateField.bind(this));
            
            // Form submission validation
            $(document).on('submit', 'form', this.validateForm.bind(this));
            
            // Real-time validation for specific fields
            $(document).on('input', 'input[type="email"]', this.debounce(this.validateEmail.bind(this), 300));
            $(document).on('input', 'input[type="tel"]', this.debounce(this.validatePhone.bind(this), 300));
            $(document).on('input', 'input[type="url"]', this.debounce(this.validateUrl.bind(this), 300));
            $(document).on('input', 'input[type="number"]', this.debounce(this.validateNumber.bind(this), 300));
        },

        initializeValidationRules: function() {
            // Define validation rules for different field types
            this.rules = {
                required: {
                    test: function(value) { return value && value.trim().length > 0; },
                    message: 'This field is required'
                },
                email: {
                    test: function(value) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value); },
                    message: 'Please enter a valid email address'
                },
                phone: {
                    test: function(value) { return /^[\+]?[1-9][\d]{0,15}$/.test(value.replace(/[\s\-\(\)]/g, '')); },
                    message: 'Please enter a valid phone number'
                },
                url: {
                    test: function(value) { 
                        try {
                            new URL(value);
                            return true;
                        } catch {
                            return false;
                        }
                    },
                    message: 'Please enter a valid URL'
                },
                number: {
                    test: function(value) { return !isNaN(value) && isFinite(value); },
                    message: 'Please enter a valid number'
                },
                positiveNumber: {
                    test: function(value) { return !isNaN(value) && isFinite(value) && parseFloat(value) > 0; },
                    message: 'Please enter a positive number'
                },
                zipCode: {
                    test: function(value) { return /^\d{5}(-\d{4})?$/.test(value); },
                    message: 'Please enter a valid ZIP code (12345 or 12345-6789)'
                },
                minLength: {
                    test: function(value, minLength) { return value.length >= minLength; },
                    message: function(minLength) { return 'Minimum length is ' + minLength + ' characters'; }
                },
                maxLength: {
                    test: function(value, maxLength) { return value.length <= maxLength; },
                    message: function(maxLength) { return 'Maximum length is ' + maxLength + ' characters'; }
                }
            };
        },

        validateField: function(e) {
            const $field = $(e.target);
            this.validateSingleField($field);
        },

        validateSingleField: function($field) {
            const value = $field.val();
            const fieldType = $field.attr('type');
            const required = $field.attr('required') !== undefined;
            const minLength = $field.attr('minlength');
            const maxLength = $field.attr('maxlength');
            const pattern = $field.attr('pattern');
            
            // Clear previous validation state
            this.clearFieldValidation($field);
            
            // Required field validation
            if (required && !this.rules.required.test(value)) {
                this.setFieldError($field, this.rules.required.message);
                return false;
            }
            
            // Skip other validations if field is empty and not required
            if (!value && !required) {
                return true;
            }
            
            // Type-specific validation
            switch (fieldType) {
                case 'email':
                    if (!this.rules.email.test(value)) {
                        this.setFieldError($field, this.rules.email.message);
                        return false;
                    }
                    break;
                case 'tel':
                    if (!this.rules.phone.test(value)) {
                        this.setFieldError($field, this.rules.phone.message);
                        return false;
                    }
                    break;
                case 'url':
                    if (!this.rules.url.test(value)) {
                        this.setFieldError($field, this.rules.url.message);
                        return false;
                    }
                    break;
                case 'number':
                    if (!this.rules.number.test(value)) {
                        this.setFieldError($field, this.rules.number.message);
                        return false;
                    }
                    break;
            }
            
            // Length validation
            if (minLength && !this.rules.minLength.test(value, parseInt(minLength))) {
                this.setFieldError($field, this.rules.minLength.message(minLength));
                return false;
            }
            
            if (maxLength && !this.rules.maxLength.test(value, parseInt(maxLength))) {
                this.setFieldError($field, this.rules.maxLength.message(maxLength));
                return false;
            }
            
            // Pattern validation
            if (pattern && !new RegExp(pattern).test(value)) {
                this.setFieldError($field, 'Please enter a valid value');
                return false;
            }
            
            // Custom validation based on field name
            const fieldName = $field.attr('name');
            if (fieldName) {
                switch (fieldName) {
                    case 'price':
                    case 'monthly_rent':
                        if (!this.rules.positiveNumber.test(value)) {
                            this.setFieldError($field, this.rules.positiveNumber.message);
                            return false;
                        }
                        break;
                    case 'zip_code':
                    case 'postal_code':
                        if (!this.rules.zipCode.test(value)) {
                            this.setFieldError($field, this.rules.zipCode.message);
                            return false;
                        }
                        break;
                }
            }
            
            // If we get here, field is valid
            this.setFieldValid($field);
            return true;
        },

        validateForm: function(e) {
            const $form = $(e.target);
            let isValid = true;
            
            // Skip validation for forms with data-no-validate attribute
            if ($form.attr('data-no-validate') !== undefined) {
                return true;
            }
            
            // Validate all form fields
            $form.find('.form-control').each((index, element) => {
                if (!this.validateSingleField($(element))) {
                    isValid = false;
                }
            });
            
            // Prevent submission if invalid
            if (!isValid) {
                e.preventDefault();
                
                // Focus first invalid field
                const $firstInvalid = $form.find('.is-invalid').first();
                if ($firstInvalid.length) {
                    $firstInvalid.focus();
                }
                
                // Show general error message
                this.showFormError($form, 'Please correct the errors below before submitting');
            }
            
            return isValid;
        },

        setFieldError: function($field, message) {
            $field.addClass('is-invalid').removeClass('is-valid');
            
            // Remove existing feedback
            $field.siblings('.invalid-feedback').remove();
            
            // Add error message
            $field.after('<div class="invalid-feedback">' + message + '</div>');
        },

        setFieldValid: function($field) {
            $field.addClass('is-valid').removeClass('is-invalid');
            
            // Remove error feedback
            $field.siblings('.invalid-feedback').remove();
        },

        clearFieldValidation: function($field) {
            $field.removeClass('is-valid is-invalid');
            $field.siblings('.invalid-feedback, .valid-feedback').remove();
        },

        showFormError: function($form, message) {
            // Remove existing form-level errors
            $form.find('.form-error').remove();
            
            // Add form error at the top
            $form.prepend('<div class="alert alert-danger form-error">' + message + '</div>');
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $form.find('.form-error').fadeOut();
            }, 5000);
        },

        validateEmail: function(e) {
            const $field = $(e.target);
            this.validateSingleField($field);
        },

        validatePhone: function(e) {
            const $field = $(e.target);
            this.validateSingleField($field);
        },

        validateUrl: function(e) {
            const $field = $(e.target);
            this.validateSingleField($field);
        },

        validateNumber: function(e) {
            const $field = $(e.target);
            this.validateSingleField($field);
        },

        // Utility function for debouncing
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Public methods for external use
        validate: function($field) {
            return this.validateSingleField($field);
        },

        setError: function($field, message) {
            this.setFieldError($field, message);
        },

        setValid: function($field) {
            this.setFieldValid($field);
        },

        clear: function($field) {
            this.clearFieldValidation($field);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        FormValidation.init();
    });

    // Make FormValidation available globally
    window.FormValidation = FormValidation;

})(jQuery);
