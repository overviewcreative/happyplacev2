/**
 * Contact Form JavaScript
 * 
 * Handles contact form submission and validation
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';
    
    class HPHContactForm {
        constructor() {
            this.form = $('.hph-contact-form');
            this.submitBtn = this.form.find('button[type="submit"]');
            this.originalBtnText = this.submitBtn.text();
            
            this.init();
        }
        
        init() {
            if (this.form.length === 0) {
                return;
            }
            
            this.bindEvents();
            this.setupValidation();
            this.setupEnhancements();
        }
        
        bindEvents() {
            // Form submission
            this.form.on('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });
            
            // Real-time validation
            this.form.find('input, textarea, select').on('blur', (e) => {
                this.validateField($(e.target));
            });
            
            // Clear validation on focus
            this.form.find('input, textarea, select').on('focus', (e) => {
                this.clearFieldValidation($(e.target));
            });
        }
        
        setupValidation() {
            // Add validation classes
            this.form.find('input[required], textarea[required], select[required]').each(function() {
                $(this).closest('.hph-form-group').addClass('required');
            });
        }
        
        setupEnhancements() {
            // Phone number formatting
            this.form.find('input[type="tel"]').on('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length >= 6) {
                    value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                } else if (value.length >= 3) {
                    value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
                }
                this.value = value;
            });
            
            // Character counter for textarea
            const textarea = this.form.find('textarea[name="message"]');
            if (textarea.length > 0) {
                const maxLength = 1000;
                const counter = $(`<div class="hph-char-counter hph-text-sm hph-text-gray-500 hph-mt-xs">
                    <span class="current">0</span> / ${maxLength} characters
                </div>`);
                
                textarea.after(counter);
                
                textarea.on('input', function() {
                    const current = this.value.length;
                    counter.find('.current').text(current);
                    
                    if (current > maxLength * 0.9) {
                        counter.addClass('hph-text-warning');
                    } else {
                        counter.removeClass('hph-text-warning');
                    }
                    
                    if (current > maxLength) {
                        counter.addClass('hph-text-danger');
                        this.value = this.value.substring(0, maxLength);
                        counter.find('.current').text(maxLength);
                    } else {
                        counter.removeClass('hph-text-danger');
                    }
                });
                
                textarea.trigger('input');
            }
            
            // Smooth scrolling for form anchors
            $('a[href="#contact-form"]').on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $('#contact-form').offset().top - 100
                }, 800);
            });
        }
        
        validateField(field) {
            const value = field.val().trim();
            const fieldName = field.attr('name');
            const isRequired = field.prop('required');
            const fieldType = field.attr('type') || field.prop('tagName').toLowerCase();
            
            let isValid = true;
            let errorMessage = '';
            
            // Required field validation
            if (isRequired && value === '') {
                isValid = false;
                errorMessage = 'This field is required';
            }
            
            // Email validation
            else if (fieldType === 'email' && value !== '') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address';
                }
            }
            
            // Phone validation
            else if (fieldType === 'tel' && value !== '') {
                const phoneRegex = /^\(\d{3}\) \d{3}-\d{4}$/;
                if (!phoneRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid phone number';
                }
            }
            
            // Message length validation
            else if (fieldName === 'message' && value.length > 0) {
                if (value.length < 10) {
                    isValid = false;
                    errorMessage = 'Message must be at least 10 characters long';
                } else if (value.length > 1000) {
                    isValid = false;
                    errorMessage = 'Message cannot exceed 1000 characters';
                }
            }
            
            this.setFieldValidation(field, isValid, errorMessage);
            return isValid;
        }
        
        setFieldValidation(field, isValid, errorMessage = '') {
            const formGroup = field.closest('.hph-form-group');
            
            // Remove existing validation classes and messages
            formGroup.removeClass('error success');
            formGroup.find('.hph-form-error, .hph-form-success').remove();
            
            if (isValid) {
                formGroup.addClass('success');
                field.removeClass('error').addClass('success');
            } else {
                formGroup.addClass('error');
                field.removeClass('success').addClass('error');
                
                if (errorMessage) {
                    field.after(`<span class="hph-form-error">${errorMessage}</span>`);
                }
            }
        }
        
        clearFieldValidation(field) {
            const formGroup = field.closest('.hph-form-group');
            formGroup.removeClass('error success');
            field.removeClass('error success');
            formGroup.find('.hph-form-error, .hph-form-success').remove();
        }
        
        validateForm() {
            let isValid = true;
            
            // Validate all fields
            this.form.find('input, textarea, select').each((index, element) => {
                const field = $(element);
                if (!this.validateField(field)) {
                    isValid = false;
                }
            });
            
            return isValid;
        }
        
        submitForm() {
            // Clear any existing messages
            this.clearMessages();
            
            // Validate form
            if (!this.validateForm()) {
                this.showMessage('Please fix the errors above and try again.', 'error');
                return;
            }
            
            // Show loading state
            this.setLoadingState(true);
            
            // Prepare form data
            const formData = new FormData(this.form[0]);
            formData.append('action', 'hph_contact_form');
            formData.append('nonce', hphContact.nonce);
            
            // Submit form
            $.ajax({
                url: hphContact.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    this.setLoadingState(false);
                    
                    if (response.success) {
                        this.showMessage(response.data.message, 'success');
                        this.form[0].reset();
                        this.clearAllValidation();
                        
                        // Track conversion (if analytics is available)
                        if (typeof gtag !== 'undefined') {
                            gtag('event', 'form_submit', {
                                event_category: 'Contact',
                                event_label: 'Contact Form'
                            });
                        }
                        
                    } else {
                        this.showMessage(response.data.message, 'error');
                        
                        // Highlight problematic fields if provided
                        if (response.data.fields) {
                            response.data.fields.forEach(fieldName => {
                                const field = this.form.find(`[name="${fieldName}"]`);
                                this.setFieldValidation(field, false, 'Please check this field');
                            });
                        }
                    }
                },
                error: (xhr, status, error) => {
                    this.setLoadingState(false);
                    this.showMessage('There was a network error. Please try again.', 'error');
                    console.error('Contact form error:', error);
                }
            });
        }
        
        setLoadingState(loading) {
            if (loading) {
                this.form.addClass('loading');
                this.submitBtn.prop('disabled', true);
                this.submitBtn.html('<i class="fas fa-spinner fa-spin hph-mr-sm"></i>Sending...');
            } else {
                this.form.removeClass('loading');
                this.submitBtn.prop('disabled', false);
                this.submitBtn.html(this.originalBtnText);
            }
        }
        
        showMessage(message, type = 'info') {
            const alertClass = type === 'success' ? 'hph-alert-success' : 'hph-alert-error';
            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
            
            const messageHtml = `
                <div class="hph-form-message ${alertClass} hph-p-lg hph-mb-lg hph-rounded-lg hph-flex hph-items-center hph-gap-md">
                    <i class="fas ${iconClass} hph-text-lg"></i>
                    <span>${message}</span>
                </div>
            `;
            
            this.form.prepend(messageHtml);
            
            // Scroll to message
            $('html, body').animate({
                scrollTop: this.form.offset().top - 100
            }, 500);
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    this.clearMessages();
                }, 5000);
            }
        }
        
        clearMessages() {
            this.form.find('.hph-form-message').remove();
        }
        
        clearAllValidation() {
            this.form.find('.hph-form-group').removeClass('error success');
            this.form.find('input, textarea, select').removeClass('error success');
            this.form.find('.hph-form-error, .hph-form-success').remove();
        }
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        new HPHContactForm();
    });
    
})(jQuery);