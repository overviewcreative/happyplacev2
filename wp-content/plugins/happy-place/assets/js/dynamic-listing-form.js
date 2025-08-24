// happy-place-homes/assets/js/dynamic-listing-form.js

(function($) {
    'use strict';
    
    const HPH_DynamicListingForm = {
        
        form: null,
        fieldConfig: null,
        isDirty: false,
        
        /**
         * Initialize
         */
        init() {
            this.form = $('#hph-listing-form');
            if (!this.form.length) return;
            
            this.bindEvents();
            this.initializeTabs();
            this.initializeConditionalLogic();
            this.initializeFieldEnhancements();
            
            console.log('HPH Dynamic Listing Form initialized');
        },
        
        /**
         * Bind events
         */
        bindEvents() {
            // Form submission
            this.form.on('submit', this.handleSubmit.bind(this));
            
            // Field changes
            this.form.on('change', 'input, select, textarea', () => {
                this.isDirty = true;
            });
            
            // Tab navigation
            $(document).on('click', '.hph-form-tab a', this.handleTabClick.bind(this));
            
            // Real-time validation
            this.form.on('blur', 'input[required], select[required], textarea[required]', 
                this.validateField.bind(this));
            
            // Prevent leaving with unsaved changes
            $(window).on('beforeunload', (e) => {
                if (this.isDirty) {
                    const message = 'You have unsaved changes. Are you sure you want to leave?';
                    e.returnValue = message;
                    return message;
                }
            });
        },
        
        /**
         * Initialize tabs
         */
        initializeTabs() {
            // Hide all sections except first
            $('.hph-form-section').not(':first').hide();
            
            // Add keyboard navigation
            $('.hph-form-tab').attr('role', 'tab');
            $('.hph-form-section').attr('role', 'tabpanel');
        },
        
        /**
         * Handle tab click
         */
        handleTabClick(e) {
            e.preventDefault();
            
            const $tab = $(e.currentTarget).closest('.hph-form-tab');
            const tabId = $tab.data('tab');
            
            // Update active states
            $('.hph-form-tab').removeClass('active');
            $tab.addClass('active');
            
            // Show corresponding section
            $('.hph-form-section').hide();
            $('#' + tabId).fadeIn();
            
            // Update URL hash
            window.location.hash = tabId;
        },
        
        /**
         * Initialize conditional logic
         */
        initializeConditionalLogic() {
            $('[data-conditional-logic]').each((index, element) => {
                const $field = $(element);
                const logic = $field.data('conditional-logic');
                
                if (!logic || !Array.isArray(logic)) return;
                
                // Set up watchers for conditional fields
                logic.forEach(ruleGroup => {
                    ruleGroup.forEach(rule => {
                        const $triggerField = $(`[data-field-key="${rule.field}"]`).find('input, select, textarea');
                        
                        if ($triggerField.length) {
                            $triggerField.on('change', () => {
                                this.evaluateConditionalLogic($field, logic);
                            });
                            
                            // Initial evaluation
                            this.evaluateConditionalLogic($field, logic);
                        }
                    });
                });
            });
        },
        
        /**
         * Evaluate conditional logic
         */
        evaluateConditionalLogic($field, logic) {
            let show = false;
            
            // OR logic between rule groups
            for (const ruleGroup of logic) {
                let groupResult = true;
                
                // AND logic within rule group
                for (const rule of ruleGroup) {
                    const $triggerField = $(`[data-field-key="${rule.field}"]`).find('input, select, textarea');
                    if (!$triggerField.length) continue;
                    
                    const value = $triggerField.val();
                    const ruleResult = this.evaluateRule(value, rule.operator, rule.value);
                    
                    if (!ruleResult) {
                        groupResult = false;
                        break;
                    }
                }
                
                if (groupResult) {
                    show = true;
                    break;
                }
            }
            
            // Show/hide field
            if (show) {
                $field.slideDown();
            } else {
                $field.slideUp();
                // Clear field value when hidden
                $field.find('input, select, textarea').val('');
            }
        },
        
        /**
         * Evaluate single rule
         */
        evaluateRule(value, operator, compareValue) {
            switch (operator) {
                case '==':
                    return value == compareValue;
                case '!=':
                    return value != compareValue;
                case '>':
                    return parseFloat(value) > parseFloat(compareValue);
                case '<':
                    return parseFloat(value) < parseFloat(compareValue);
                case '>=':
                    return parseFloat(value) >= parseFloat(compareValue);
                case '<=':
                    return parseFloat(value) <= parseFloat(compareValue);
                case '==empty':
                    return !value || value === '';
                case '!=empty':
                    return value && value !== '';
                default:
                    return false;
            }
        },
        
        /**
         * Initialize field enhancements
         */
        initializeFieldEnhancements() {
            // Enhanced select fields
            $('.hph-select[data-ui="1"]').each((index, element) => {
                // Could integrate Select2 or similar here
                $(element).addClass('enhanced-select');
            });
            
            // Date pickers
            $('.hph-field-date_picker input').each((index, element) => {
                // Initialize date picker if library is available
                if ($.fn.datepicker) {
                    $(element).datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true
                    });
                }
            });
            
            // Image upload fields
            this.initializeImageFields();
            
            // Repeater fields
            this.initializeRepeaterFields();
        },
        
        /**
         * Initialize image upload fields
         */
        initializeImageFields() {
            $('.hph-field-image').each((index, element) => {
                const $field = $(element);
                const $input = $field.find('input[type="hidden"]');
                const $preview = $field.find('.image-preview');
                const $button = $field.find('.select-image-button');
                
                $button.on('click', (e) => {
                    e.preventDefault();
                    
                    // Open WordPress media library if available
                    if (wp && wp.media) {
                        const frame = wp.media({
                            title: 'Select Image',
                            button: { text: 'Use this image' },
                            multiple: false
                        });
                        
                        frame.on('select', () => {
                            const attachment = frame.state().get('selection').first().toJSON();
                            $input.val(attachment.id);
                            $preview.html(`<img src="${attachment.url}" alt="">`);
                        });
                        
                        frame.open();
                    }
                });
            });
        },
        
        /**
         * Initialize repeater fields
         */
        initializeRepeaterFields() {
            $('.hph-field-repeater').each((index, element) => {
                const $repeater = $(element);
                const $addButton = $repeater.find('.add-row-button');
                const $rows = $repeater.find('.repeater-rows');
                
                $addButton.on('click', (e) => {
                    e.preventDefault();
                    // Clone row template and append
                    const $template = $repeater.find('.row-template').clone();
                    $template.removeClass('row-template').addClass('repeater-row');
                    $rows.append($template);
                });
                
                $repeater.on('click', '.remove-row', (e) => {
                    e.preventDefault();
                    $(e.target).closest('.repeater-row').fadeOut(() => {
                        $(e.target).closest('.repeater-row').remove();
                    });
                });
            });
        },
        
        /**
         * Handle form submission
         */
        handleSubmit(e) {
            e.preventDefault();
            
            // Validate all required fields
            if (!this.validateForm()) {
                this.showNotification('Please fix the validation errors', 'error');
                return;
            }
            
            // Show loading state
            this.setLoadingState(true);
            
            // Serialize form data
            const formData = new FormData(this.form[0]);
            formData.append('action', 'hph_save_listing');
            
            // Submit via AJAX
            $.ajax({
                url: HPH_Form.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    this.setLoadingState(false);
                    
                    if (response.success) {
                        this.isDirty = false;
                        this.showNotification(response.data.message, 'success');
                        
                        // Redirect if URL provided
                        if (response.data.redirect) {
                            setTimeout(() => {
                                window.location.href = response.data.redirect;
                            }, 1500);
                        }
                    } else {
                        this.handleErrors(response.data);
                    }
                },
                error: (xhr, status, error) => {
                    this.setLoadingState(false);
                    this.showNotification('An error occurred. Please try again.', 'error');
                    console.error('Form submission error:', error);
                }
            });
        },
        
        /**
         * Validate form
         */
        validateForm() {
            let isValid = true;
            
            // Check all required fields
            this.form.find('[required]').each((index, element) => {
                if (!this.validateField({ target: element })) {
                    isValid = false;
                }
            });
            
            return isValid;
        },
        
        /**
         * Validate single field
         */
        validateField(e) {
            const $field = $(e.target);
            const $wrapper = $field.closest('.hph-field-wrapper');
            const value = $field.val();
            
            // Remove previous error
            $wrapper.removeClass('has-error');
            $wrapper.find('.field-error').remove();
            
            // Check if required and empty
            if ($field.prop('required') && !value) {
                this.showFieldError($wrapper, 'This field is required');
                return false;
            }
            
            // Type-specific validation
            const type = $field.attr('type');
            
            if (type === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    this.showFieldError($wrapper, 'Please enter a valid email address');
                    return false;
                }
            }
            
            if (type === 'url' && value) {
                try {
                    new URL(value);
                } catch {
                    this.showFieldError($wrapper, 'Please enter a valid URL');
                    return false;
                }
            }
            
            if (type === 'number' && value) {
                const min = parseFloat($field.attr('min'));
                const max = parseFloat($field.attr('max'));
                const numValue = parseFloat(value);
                
                if (isNaN(numValue)) {
                    this.showFieldError($wrapper, 'Please enter a valid number');
                    return false;
                }
                
                if (!isNaN(min) && numValue < min) {
                    this.showFieldError($wrapper, `Value must be at least ${min}`);
                    return false;
                }
                
                if (!isNaN(max) && numValue > max) {
                    this.showFieldError($wrapper, `Value must not exceed ${max}`);
                    return false;
                }
            }
            
            return true;
        },
        
        /**
         * Show field error
         */
        showFieldError($wrapper, message) {
            $wrapper.addClass('has-error');
            $wrapper.append(`<span class="field-error">${message}</span>`);
        },
        
        /**
         * Handle validation errors
         */
        handleErrors(data) {
            if (data.errors) {
                // Show field-specific errors
                Object.keys(data.errors).forEach(fieldKey => {
                    const $field = $(`[name="acf[${fieldKey}]"]`);
                    if ($field.length) {
                        const $wrapper = $field.closest('.hph-field-wrapper');
                        this.showFieldError($wrapper, data.errors[fieldKey]);
                    }
                });
                
                // Scroll to first error
                const $firstError = $('.has-error:first');
                if ($firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 100
                    }, 500);
                }
            }
            
            this.showNotification(data.message || 'Please fix the errors below', 'error');
        },
        
        /**
         * Set loading state
         */
        setLoadingState(loading) {
            const $submitBtn = this.form.find('[type="submit"]');
            
            if (loading) {
                $submitBtn.prop('disabled', true);
                $submitBtn.find('.btn-text').hide();
                $submitBtn.find('.btn-loading').show();
                this.form.addClass('is-loading');
            } else {
                $submitBtn.prop('disabled', false);
                $submitBtn.find('.btn-text').show();
                $submitBtn.find('.btn-loading').hide();
                this.form.removeClass('is-loading');
            }
        },
        
        /**
         * Show notification
         */
        showNotification(message, type = 'info') {
            // Remove existing notifications
            $('.hph-notification').remove();
            
            const $notification = $(`
                <div class="hph-notification hph-notification-${type}">
                    <div class="notification-content">
                        ${message}
                    </div>
                    <button class="notification-close">&times;</button>
                </div>
            `);
            
            $('body').append($notification);
            
            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(() => {
                    $notification.fadeOut(() => $notification.remove());
                }, 5000);
            }
            
            // Close button
            $notification.find('.notification-close').on('click', () => {
                $notification.fadeOut(() => $notification.remove());
            });
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(() => {
        HPH_DynamicListingForm.init();
    });
    
})(jQuery);