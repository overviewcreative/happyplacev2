/**
 * Unified Form System for Happy Place
 * 
 * Consolidates form validation, handling, and UI interactions
 * Replaces multiple form libraries with one comprehensive system
 * 
 * @package HappyPlaceTheme
 * @version 2.0.0 - Consolidated Edition
 * @author Form Consolidation Team
 */

(function($) {
    'use strict';

    // Ensure HPH global namespace exists
    if (typeof window.HPH === 'undefined') {
        window.HPH = {};
    }

    /**
     * Unified Happy Place Form System
     * 
     * Combines functionality from:
     * - form-validation.js (plugin & theme versions)
     * - universal-form-handler.js  
     * - lead-forms.js
     * - agent-lead-form.js
     * - contact-form.js
     */
    HPH.Forms = {
        
        // === CONFIGURATION ===
        
        config: {
            ajaxUrl: window.ajaxurl || '/wp-admin/admin-ajax.php',
            nonce: '',
            debug: false,
            selectors: {
                forms: '[data-route-type], .hph-form, .hph-general-contact-form, .hph-property-inquiry-form, .hph-agent-contact-form',
                validateForms: 'form[data-validate="true"]',
                requiredFields: 'input[required], select[required], textarea[required]',
                emailFields: 'input[type="email"]',
                phoneFields: 'input[type="tel"]',
                urlFields: 'input[type="url"]',
                numberFields: 'input[type="number"]'
            },
            classes: {
                error: 'is-invalid',
                success: 'is-valid',
                loading: 'is-loading',
                disabled: 'is-disabled'
            }
        },
        
        // === VALIDATION RULES ===
        
        rules: {
            required: {
                test: function(value) { 
                    return value && value.trim().length > 0; 
                },
                message: 'This field is required'
            },
            email: {
                test: function(value) { 
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value); 
                },
                message: 'Please enter a valid email address'
            },
            phone: {
                test: function(value) { 
                    const cleaned = value.replace(/[\s\-\(\)\.]/g, '');
                    return /^[\+]?[1-9][\d]{0,15}$/.test(cleaned); 
                },
                message: 'Please enter a valid phone number'
            },
            url: {
                test: function(value) {
                    try {
                        new URL(value);
                        return true;
                    } catch {
                        return /^https?:\/\/.+/.test(value);
                    }
                },
                message: 'Please enter a valid URL'
            },
            number: {
                test: function(value) {
                    return !isNaN(value) && value !== '';
                },
                message: 'Please enter a valid number'
            },
            minLength: {
                test: function(value, min) {
                    return value.length >= min;
                },
                message: function(min) {
                    return `Please enter at least ${min} characters`;
                }
            },
            maxLength: {
                test: function(value, max) {
                    return value.length <= max;
                },
                message: function(max) {
                    return `Please enter no more than ${max} characters`;
                }
            },
            price: {
                test: function(value) {
                    return /^\$?[\d,]+(\.\d{2})?$/.test(value.replace(/\s/g, ''));
                },
                message: 'Please enter a valid price (e.g., $250,000 or 250000)'
            }
        },
        
        // === INITIALIZATION ===
        
        init: function() {
            
            this.setupNonce();
            this.bindEvents();
            this.setupFormEnhancements();
            this.initializeExistingForms();
            
        },
        
        // Setup AJAX nonce
        setupNonce: function() {
            this.config.nonce = $('meta[name="hph-nonce"]').attr('content') ||
                               $('input[name="hph_nonce"]').val() ||
                               $('input[name="_wpnonce"]').val() || '';
                               
            // Get AJAX URL from various sources
            this.config.ajaxUrl = window.hph_ajax?.url ||
                                 window.ajaxurl ||
                                 '/wp-admin/admin-ajax.php';
                                 
            if (this.config.debug) {
            }
        },
        
        // === EVENT BINDING ===
        
        bindEvents: function() {
            console.log('HPH Forms: Binding events for selectors:', this.config.selectors.forms);

            // AGGRESSIVE FORM PREVENTION - Use native addEventListener with capture to prevent ANY form submission
            const preventAllFormSubmissions = (e) => {
                const form = e.target;
                if (form.tagName === 'FORM' && (
                    form.action.includes('admin-ajax.php') ||
                    form.querySelector('input[name="action"][value="hph_route_form"]') ||
                    form.classList.contains('hph-form') ||
                    form.closest('.hph-modal')
                )) {
                    console.log('🚨 PREVENTING FORM SUBMISSION:', form);
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    // Manually trigger our handler
                    this.handleFormSubmission(e);
                    return false;
                }
            };

            // Remove existing listener if any
            if (this._nativeSubmitListener) {
                document.removeEventListener('submit', this._nativeSubmitListener, true);
            }

            // Add native listener with capture phase
            this._nativeSubmitListener = preventAllFormSubmissions.bind(this);
            document.addEventListener('submit', this._nativeSubmitListener, true);

            // Form submission handling - jQuery fallback
            $(document).off('submit.hph-forms').on('submit.hph-forms', this.config.selectors.forms, this.handleFormSubmission.bind(this));

            // CRITICAL: Add additional form submission prevention for modal forms specifically
            $(document).off('submit.hph-modal-forms').on('submit.hph-modal-forms', '.hph-modal form', this.handleFormSubmission.bind(this));

            // Real-time validation
            $(document).off('blur.hph-forms').on('blur.hph-forms', this.config.selectors.requiredFields, this.validateField.bind(this));
            $(document).off('input.hph-forms').on('input.hph-forms', this.config.selectors.emailFields, this.debounce(this.validateField.bind(this), 300));
            $(document).off('input.hph-forms').on('input.hph-forms', this.config.selectors.phoneFields, this.debounce(this.validateField.bind(this), 300));
            $(document).off('input.hph-forms').on('input.hph-forms', this.config.selectors.urlFields, this.debounce(this.validateField.bind(this), 300));
            $(document).off('input.hph-forms').on('input.hph-forms', this.config.selectors.numberFields, this.debounce(this.validateField.bind(this), 300));

            // Form validation on submit
            $(document).off('submit.hph-validate').on('submit.hph-validate', this.config.selectors.validateForms, this.validateForm.bind(this));

            // Clear validation on focus
            $(document).off('focus.hph-forms').on('focus.hph-forms', 'input, select, textarea', this.clearFieldValidation.bind(this));

            // Custom events
            $(document).off('hph:forms:validate').on('hph:forms:validate', this.validateForm.bind(this));
            $(document).off('hph:forms:reset').on('hph:forms:reset', this.resetForm.bind(this));

            // Extra safety: Bind to any form with action="admin-ajax.php"
            $(document).off('submit.hph-ajax-forms').on('submit.hph-ajax-forms', 'form[action*="admin-ajax.php"]', this.handleFormSubmission.bind(this));
        },
        
        // === FORM SUBMISSION HANDLING ===
        
        handleFormSubmission: function(e) {
            // CRITICAL: Prevent default form submission immediately
            if (e.preventDefault) {
                e.preventDefault();
            }
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            if (e.stopImmediatePropagation) {
                e.stopImmediatePropagation();
            }

            const $form = $(e.currentTarget || e.target);

            // Prevent duplicate submissions
            if ($form.data('hph-submitting')) {
                console.log('HPH Forms: Preventing duplicate submission');
                return false;
            }

            console.log('HPH Forms: Processing form submission', {
                form: $form[0],
                action: $form.attr('action'),
                method: $form.attr('method'),
                routeType: $form.data('route-type')
            });

            // Remove any action attribute to prevent fallback submission
            const originalAction = $form.attr('action');
            $form.removeAttr('action');

            const formData = this.getFormData($form);

            if (this.config.debug) {
                console.log('Form submission debug:', {
                    form: $form[0],
                    data: formData,
                    routeType: $form.data('route-type')
                });
            }

            // Validate form before submission
            if (!this.validateForm.call(this, e)) {
                // Restore action attribute if validation fails
                if (originalAction) {
                    $form.attr('action', originalAction);
                }
                return false;
            }

            // Mark form as submitting to prevent duplicates
            $form.data('hph-submitting', true);

            // Determine submission method
            const routeType = $form.data('route-type') || 'contact';
            const submitMethod = this.getSubmissionMethod($form, routeType);

            // Handle submission
            this.submitForm($form, formData, submitMethod, originalAction);

            return false;
        },
        
        getFormData: function($form) {
            const formData = new FormData($form[0]);
            
            // Add common data
            formData.append('nonce', this.config.nonce);
            
            // Add route type if not present
            if (!formData.has('route_type') && $form.data('route-type')) {
                formData.append('route_type', $form.data('route-type'));
            }
            
            return formData;
        },
        
        getSubmissionMethod: function($form, routeType) {
            // Check if form has specific action set
            const formAction = $form.find('input[name="action"]').val();
            if (formAction) {
                return formAction;
            }

            // Route ALL forms through the plugin's unified FormRouter
            // This consolidates all form processing in the plugin instead of theme
            const routeMap = {
                'contact': 'hph_route_form',
                'property-inquiry': 'hph_route_form',
                'agent-contact': 'hph_route_form',
                'valuation-request': 'hph_route_form',
                'showing-request': 'hph_route_form',
                'general-inquiry': 'hph_route_form',
                'listing-form': 'hph_route_form',
                'lead-form': 'hph_route_form',
                'lead_capture': 'hph_route_form'
            };

            return routeMap[routeType] || 'hph_route_form';
        },
        
        submitForm: function($form, formData, action, originalAction) {
            const self = this;

            // Add AJAX action
            formData.append('action', action);

            console.log('HPH Forms: Submitting via AJAX', {
                url: this.config.ajaxUrl,
                action: action,
                formId: $form.attr('id') || 'unnamed'
            });

            // Show loading state
            this.setFormLoading($form, true);

            // Submit form
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('HPH Forms: AJAX success', response);
                    self.handleFormSuccess($form, response);
                },
                error: function(xhr, status, error) {
                    console.error('HPH Forms: AJAX error', { xhr, status, error });
                    self.handleFormError($form, `Submission failed: ${error}`);
                },
                complete: function() {
                    // Reset submitting flag and loading state
                    $form.removeData('hph-submitting');
                    self.setFormLoading($form, false);

                    // Restore original action attribute
                    if (originalAction) {
                        $form.attr('action', originalAction);
                    }
                }
            });
        },
        
        // === FORM SUCCESS/ERROR HANDLING ===
        
        handleFormSuccess: function($form, response) {
            // Reset submitting flag
            $form.removeData('hph-submitting');

            if (response.success) {
                // Check if this is a modal form
                const $modal = $form.closest('.hph-modal');

                if ($modal.length) {
                    // This is a modal form - handle success differently
                    this.handleModalFormSuccess($form, $modal, response);
                } else {
                    // Regular form - use existing logic
                    this.showFormMessage($form, response.data.message || 'Form submitted successfully!', 'success');
                    this.resetForm({ target: $form[0] });
                }

                // Trigger custom success event
                $form.trigger('hph:forms:success', [response]);

                // Handle redirects
                if (response.data.redirect) {
                    setTimeout(() => {
                        window.location.href = response.data.redirect;
                    }, 1500);
                }
            } else {
                this.handleFormError($form, response.data?.message || 'Form submission failed');
            }
        },

        handleModalFormSuccess: function($form, $modal, response) {
            // Hide the form container
            const $formContainer = $modal.find('.hph-modal-form-container');
            const $successContainer = $modal.find('.hph-modal-success');

            if ($formContainer.length && $successContainer.length) {
                // Hide form and show success message
                $formContainer.fadeOut(300, function() {
                    // Update success message if provided
                    const message = response.data?.message || 'Thank you! We\'ll contact you soon.';
                    const $successText = $successContainer.find('p');
                    if ($successText.length) {
                        $successText.text(message);
                    }

                    // Show success container
                    $successContainer.fadeIn(300);
                });

                // Auto-close modal after delay if specified
                const shouldAutoClose = $modal.data('close-on-success') !== 'false';
                if (shouldAutoClose) {
                    setTimeout(() => {
                        if (window.closeHphFormModal) {
                            window.closeHphFormModal($modal.attr('id'));
                        }
                    }, 3000);
                }
            } else {
                // Fallback to regular message
                this.showFormMessage($form, response.data?.message || 'Thank you! We\'ll contact you soon.', 'success');
            }

            // Reset form for next use
            this.resetForm({ target: $form[0] });
        },
        
        handleFormError: function($form, errorMessage) {
            // Reset submitting flag
            $form.removeData('hph-submitting');
            
            this.showFormMessage($form, errorMessage, 'error');
            
            // Trigger custom error event
            $form.trigger('hph:forms:error', [errorMessage]);
        },
        
        showFormMessage: function($form, message, type = 'info') {
            // Remove existing messages
            $form.find('.hph-form-message').remove();
            
            // Create message element
            const $message = $(`
                <div class="hph-form-message hph-form-message-${type}">
                    <span class="hph-form-message-text">${message}</span>
                    <button type="button" class="hph-form-message-close">&times;</button>
                </div>
            `);
            
            // Insert message
            $form.prepend($message);
            
            // Auto-remove success messages
            if (type === 'success') {
                setTimeout(() => {
                    $message.fadeOut(() => $message.remove());
                }, 5000);
            }
            
            // Handle close button
            $message.find('.hph-form-message-close').on('click', function() {
                $message.fadeOut(() => $message.remove());
            });
        },
        
        // === FORM VALIDATION ===
        
        validateForm: function(e) {
            const $form = $(e.target || e.currentTarget);
            let isValid = true;
            
            // Skip validation if form doesn't require it
            if (!$form.is(this.config.selectors.validateForms)) {
                return true;
            }
            
            // Validate all required fields
            $form.find(this.config.selectors.requiredFields).each((index, field) => {
                if (!this.validateField({ target: field })) {
                    isValid = false;
                }
            });
            
            // Validate specific field types
            $form.find(this.config.selectors.emailFields).each((index, field) => {
                if ($(field).val() && !this.validateField({ target: field })) {
                    isValid = false;
                }
            });
            
            $form.find(this.config.selectors.phoneFields).each((index, field) => {
                if ($(field).val() && !this.validateField({ target: field })) {
                    isValid = false;
                }
            });
            
            if (!isValid && e.preventDefault) {
                e.preventDefault();
            }
            
            return isValid;
        },
        
        validateField: function(e) {
            const $field = $(e.target || e.currentTarget);
            const value = $field.val();
            const fieldType = $field.attr('type');
            const isRequired = $field.prop('required');
            
            let isValid = true;
            let errorMessage = '';
            
            // Required field validation
            if (isRequired && !this.rules.required.test(value)) {
                isValid = false;
                errorMessage = this.rules.required.message;
            }
            
            // Type-specific validation (only if field has value)
            if (value && isValid) {
                switch (fieldType) {
                    case 'email':
                        if (!this.rules.email.test(value)) {
                            isValid = false;
                            errorMessage = this.rules.email.message;
                        }
                        break;
                    case 'tel':
                        if (!this.rules.phone.test(value)) {
                            isValid = false;
                            errorMessage = this.rules.phone.message;
                        }
                        break;
                    case 'url':
                        if (!this.rules.url.test(value)) {
                            isValid = false;
                            errorMessage = this.rules.url.message;
                        }
                        break;
                    case 'number':
                        if (!this.rules.number.test(value)) {
                            isValid = false;
                            errorMessage = this.rules.number.message;
                        }
                        break;
                }
            }
            
            // Custom validation rules (data attributes)
            if (value && isValid) {
                const minLength = $field.data('min-length');
                const maxLength = $field.data('max-length');
                
                if (minLength && !this.rules.minLength.test(value, minLength)) {
                    isValid = false;
                    errorMessage = this.rules.minLength.message(minLength);
                }
                
                if (maxLength && !this.rules.maxLength.test(value, maxLength)) {
                    isValid = false;
                    errorMessage = this.rules.maxLength.message(maxLength);
                }
                
                // Price validation for price fields
                if ($field.hasClass('price-field') && !this.rules.price.test(value)) {
                    isValid = false;
                    errorMessage = this.rules.price.message;
                }
            }
            
            // Apply validation state
            this.setFieldValidationState($field, isValid, errorMessage);
            
            return isValid;
        },
        
        setFieldValidationState: function($field, isValid, errorMessage = '') {
            const $formGroup = $field.closest('.form-group, .hph-form-group');
            
            // Remove existing validation classes and messages
            $field.removeClass(`${this.config.classes.error} ${this.config.classes.success}`);
            $formGroup.find('.invalid-feedback, .hph-field-error').remove();
            
            if (isValid) {
                $field.addClass(this.config.classes.success);
            } else {
                $field.addClass(this.config.classes.error);
                
                // Add error message
                if (errorMessage) {
                    const $errorElement = $('<div class="invalid-feedback hph-field-error"></div>').text(errorMessage);
                    
                    if ($formGroup.length) {
                        $formGroup.append($errorElement);
                    } else {
                        $field.after($errorElement);
                    }
                }
            }
        },
        
        clearFieldValidation: function(e) {
            const $field = $(e.target);
            const $formGroup = $field.closest('.form-group, .hph-form-group');
            
            $field.removeClass(`${this.config.classes.error} ${this.config.classes.success}`);
            $formGroup.find('.invalid-feedback, .hph-field-error').remove();
        },
        
        // === FORM UI ENHANCEMENTS ===
        
        setupFormEnhancements: function() {
            this.setupLoadingStates();
            this.setupPlaceholderEnhancements();
            this.setupFieldFormatting();
        },
        
        setupLoadingStates: function() {
            // Add loading indicators to submit buttons
            $(this.config.selectors.forms).each(function() {
                const $form = $(this);
                const $submitBtn = $form.find('button[type="submit"], input[type="submit"]');
                
                if (!$submitBtn.find('.loading-spinner').length) {
                    $submitBtn.append('<span class="loading-spinner" style="display:none;">⏳</span>');
                }
            });
        },
        
        setupPlaceholderEnhancements: function() {
            // Enhanced placeholder behavior for better UX
            $('input[placeholder], textarea[placeholder]').on('focus', function() {
                $(this).data('original-placeholder', $(this).attr('placeholder'));
            }).on('blur', function() {
                const original = $(this).data('original-placeholder');
                if (original) {
                    $(this).attr('placeholder', original);
                }
            });
        },
        
        setupFieldFormatting: function() {
            // Phone number formatting
            $(this.config.selectors.phoneFields).on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length > 0) {
                    if (value.length <= 3) {
                        value = `(${value}`;
                    } else if (value.length <= 6) {
                        value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
                    } else {
                        value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
                    }
                    $(this).val(value);
                }
            });
        },
        
        setFormLoading: function($form, isLoading) {
            const $submitBtn = $form.find('button[type="submit"], input[type="submit"]');
            const $spinner = $submitBtn.find('.loading-spinner');
            
            if (isLoading) {
                $form.addClass(this.config.classes.loading);
                $submitBtn.prop('disabled', true).addClass(this.config.classes.disabled);
                $spinner.show();
                
                // Store original button text
                if (!$submitBtn.data('original-text')) {
                    $submitBtn.data('original-text', $submitBtn.text());
                }
                $submitBtn.text('Submitting...');
            } else {
                $form.removeClass(this.config.classes.loading);
                $submitBtn.prop('disabled', false).removeClass(this.config.classes.disabled);
                $spinner.hide();
                
                // Restore original button text
                const originalText = $submitBtn.data('original-text');
                if (originalText) {
                    $submitBtn.text(originalText);
                }
            }
        },
        
        // === FORM RESET ===
        
        resetForm: function(e) {
            const $form = $(e.target || e.currentTarget);
            
            // Reset form fields
            $form[0].reset();
            
            // Clear validation states
            $form.find('input, select, textarea').removeClass(`${this.config.classes.error} ${this.config.classes.success}`);
            $form.find('.invalid-feedback, .hph-field-error').remove();
            $form.find('.hph-form-message').remove();
            
            // Trigger custom reset event
            $form.trigger('hph:forms:reset');
        },
        
        // === FORM INITIALIZATION ===
        
        initializeExistingForms: function() {
            // Initialize any forms that are already on the page
            $(this.config.selectors.forms).each((index, form) => {
                const $form = $(form);
                
                // Add form attributes if missing
                if (!$form.attr('novalidate')) {
                    $form.attr('novalidate', 'novalidate');
                }
                
                // Add validation classes for Bootstrap compatibility
                if (!$form.hasClass('needs-validation')) {
                    $form.addClass('needs-validation');
                }
            });
        },
        
        // === UTILITIES ===
        
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
        
        // === PUBLIC API ===
        
        // Validate a specific form programmatically
        validate: function(formSelector) {
            const $form = $(formSelector);
            return this.validateForm({ target: $form[0] });
        },
        
        // Submit a form programmatically
        submit: function(formSelector) {
            const $form = $(formSelector);
            $form.trigger('submit');
        },
        
        // Add custom validation rule
        addRule: function(name, test, message) {
            this.rules[name] = {
                test: test,
                message: message
            };
        }
    };
    
    // === INITIALIZATION ===
    
    // Initialize when document is ready
    $(document).ready(function() {
        HPH.Forms.init();
    });
    
    // Legacy compatibility exports
    window.FormValidation = HPH.Forms;
    window.HappyPlaceFormHandler = HPH.Forms;
    
    // Export for module systems
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = HPH.Forms;
    }

})(jQuery);
