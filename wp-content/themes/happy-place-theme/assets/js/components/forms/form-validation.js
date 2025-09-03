/**
 * Form Validation Component
 * Provides comprehensive form validation for Happy Place Theme
 * 
 * @package HappyPlaceTheme
 * @version 1.0.0
 */

(function($) {
    'use strict';
    
    // Ensure HPH global exists
    if (typeof window.HPH === 'undefined') {
        window.HPH = {};
    }
    
    if (typeof HPH.components === 'undefined') {
        HPH.components = {};
    }

    /**
     * Form Validation Component
     */
    HPH.components.FormValidation = {
        
        /**
         * Initialize form validation
         */
        init: function() {
            this.bindEvents();
            this.setupValidationRules();
        },
        
        /**
         * Bind validation events
         */
        bindEvents: function() {
            // Real-time validation on input
            $(document).on('blur', 'input[required], select[required], textarea[required]', this.validateField);
            $(document).on('input', 'input[type="email"], input[type="tel"], input[type="url"]', this.validateField);
            
            // Form submission validation
            $(document).on('submit', 'form', this.validateForm);
            
            // Custom validation triggers
            $(document).on('hph:validate:field', this.validateField);
            $(document).on('hph:validate:form', this.validateForm);
        },
        
        /**
         * Setup validation rules
         */
        setupValidationRules: function() {
            this.rules = {
                required: function(value) {
                    return value && value.trim() !== '';
                },
                email: function(value) {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return !value || emailPattern.test(value);
                },
                phone: function(value) {
                    const phonePattern = /^[\+]?[1-9][\d]{0,15}$/;
                    return !value || phonePattern.test(value.replace(/[\s\-\(\)]/g, ''));
                },
                url: function(value) {
                    const urlPattern = /^https?:\/\/.+/;
                    return !value || urlPattern.test(value);
                },
                minLength: function(value, minLength) {
                    return !value || value.length >= minLength;
                },
                maxLength: function(value, maxLength) {
                    return !value || value.length <= maxLength;
                },
                number: function(value) {
                    return !value || !isNaN(value);
                },
                integer: function(value) {
                    return !value || /^\d+$/.test(value);
                },
                price: function(value) {
                    return !value || /^\d+(\.\d{0,2})?$/.test(value);
                }
            };
        },
        
        /**
         * Validate a single field
         */
        validateField: function(event) {
            const $field = $(event.target || this);
            const value = $field.val();
            const $fieldGroup = $field.closest('.form-group, .field-group, .input-group');
            
            // Clear previous errors
            $fieldGroup.removeClass('has-error');
            $fieldGroup.find('.error-message').remove();
            
            const validationResult = HPH.components.FormValidation.runFieldValidation($field, value);
            
            if (!validationResult.valid) {
                $fieldGroup.addClass('has-error');
                $fieldGroup.append('<div class="error-message">' + validationResult.message + '</div>');
                return false;
            }
            
            return true;
        },
        
        /**
         * Validate entire form
         */
        validateForm: function(event) {
            const $form = $(event.target || this);
            let isValid = true;
            
            // Validate all required and typed fields
            $form.find('input[required], select[required], textarea[required], input[type="email"], input[type="tel"], input[type="url"]').each(function() {
                const fieldValid = HPH.components.FormValidation.validateField.call(this, {target: this});
                if (!fieldValid) {
                    isValid = false;
                }
            });
            
            // Custom form-specific validation
            const formValidation = HPH.components.FormValidation.runFormValidation($form);
            if (!formValidation.valid) {
                isValid = false;
                HPH.components.FormValidation.showFormError($form, formValidation.message);
            }
            
            if (!isValid) {
                event.preventDefault();
                HPH.components.FormValidation.focusFirstError($form);
                return false;
            }
            
            return true;
        },
        
        /**
         * Run validation rules on a field
         */
        runFieldValidation: function($field, value) {
            const rules = HPH.components.FormValidation.rules;
            
            // Required validation
            if ($field.attr('required') && !rules.required(value)) {
                return {
                    valid: false,
                    message: 'This field is required.'
                };
            }
            
            // Type-based validation
            const fieldType = $field.attr('type') || $field.prop('tagName').toLowerCase();
            
            switch (fieldType) {
                case 'email':
                    if (!rules.email(value)) {
                        return {
                            valid: false,
                            message: 'Please enter a valid email address.'
                        };
                    }
                    break;
                    
                case 'tel':
                    if (!rules.phone(value)) {
                        return {
                            valid: false,
                            message: 'Please enter a valid phone number.'
                        };
                    }
                    break;
                    
                case 'url':
                    if (!rules.url(value)) {
                        return {
                            valid: false,
                            message: 'Please enter a valid URL.'
                        };
                    }
                    break;
                    
                case 'number':
                    if (!rules.number(value)) {
                        return {
                            valid: false,
                            message: 'Please enter a valid number.'
                        };
                    }
                    break;
            }
            
            // Custom attribute validation
            const minLength = $field.attr('minlength');
            if (minLength && !rules.minLength(value, parseInt(minLength))) {
                return {
                    valid: false,
                    message: `Please enter at least ${minLength} characters.`
                };
            }
            
            const maxLength = $field.attr('maxlength');
            if (maxLength && !rules.maxLength(value, parseInt(maxLength))) {
                return {
                    valid: false,
                    message: `Please enter no more than ${maxLength} characters.`
                };
            }
            
            // Data attribute validation
            if ($field.data('validation') === 'price' && !rules.price(value)) {
                return {
                    valid: false,
                    message: 'Please enter a valid price (e.g., 123.45).'
                };
            }
            
            if ($field.data('validation') === 'integer' && !rules.integer(value)) {
                return {
                    valid: false,
                    message: 'Please enter a whole number.'
                };
            }
            
            return { valid: true };
        },
        
        /**
         * Run form-specific validation
         */
        runFormValidation: function($form) {
            const formId = $form.attr('id');
            
            // Contact form validation
            if (formId === 'contact-form' || $form.hasClass('contact-form')) {
                const name = $form.find('input[name="name"]').val();
                const email = $form.find('input[name="email"]').val();
                const message = $form.find('textarea[name="message"]').val();
                
                if (!name || !email || !message) {
                    return {
                        valid: false,
                        message: 'Please fill in all required fields.'
                    };
                }
            }
            
            // Search form validation
            if (formId === 'advanced-search-form' || $form.hasClass('search-form')) {
                const hasSearchCriteria = $form.find('input, select').filter(function() {
                    return $(this).val() && $(this).val().trim() !== '';
                }).length > 0;
                
                if (!hasSearchCriteria) {
                    return {
                        valid: false,
                        message: 'Please enter at least one search criteria.'
                    };
                }
            }
            
            // Lead form validation
            if (formId === 'lead-form' || $form.hasClass('lead-form')) {
                const email = $form.find('input[name="email"]').val();
                const phone = $form.find('input[name="phone"]').val();
                
                if (!email && !phone) {
                    return {
                        valid: false,
                        message: 'Please provide either an email address or phone number.'
                    };
                }
            }
            
            return { valid: true };
        },
        
        /**
         * Show form-level error message
         */
        showFormError: function($form, message) {
            $form.find('.form-error').remove();
            $form.prepend('<div class="form-error alert alert-danger">' + message + '</div>');
        },
        
        /**
         * Focus on first field with error
         */
        focusFirstError: function($form) {
            const $firstError = $form.find('.has-error').first().find('input, select, textarea').first();
            if ($firstError.length) {
                $firstError.focus();
                
                // Scroll to error if needed
                $('html, body').animate({
                    scrollTop: $firstError.offset().top - 100
                }, 300);
            }
        },
        
        /**
         * Clear all validation errors
         */
        clearErrors: function($form) {
            $form.find('.has-error').removeClass('has-error');
            $form.find('.error-message').remove();
            $form.find('.form-error').remove();
        },
        
        /**
         * Add custom validation rule
         */
        addRule: function(name, validator) {
            this.rules[name] = validator;
        },
        
        /**
         * Public validation methods
         */
        validate: {
            field: function($field) {
                return HPH.components.FormValidation.validateField.call($field[0], {target: $field[0]});
            },
            form: function($form) {
                return HPH.components.FormValidation.validateForm.call($form[0], {target: $form[0]});
            }
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        HPH.components.FormValidation.init();
    });
    
})(jQuery);