/**
 * Contact Form JavaScript - VALIDATION ONLY
 * 
 * Provides enhanced validation for contact forms
 * Form submission is handled by HPH.Forms unified system
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * @version 2.0.0 - Validation Only (no submission handling)
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
            // Note: Form submission is handled by HPH.Forms unified system
            // This class only provides validation enhancements
            
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
                $(this).addClass('hph-required-field');
            });
            
            // Add validation attributes for better UX
            this.form.find('input[type="email"]').attr('data-validation', 'email');
            this.form.find('input[type="tel"]').attr('data-validation', 'phone');
        }
        
        setupEnhancements() {
            // Character counter for textareas
            this.form.find('textarea[maxlength]').each(function() {
                const textarea = $(this);
                const maxLength = parseInt(textarea.attr('maxlength'));
                const counter = $(`<div class="hph-character-counter"><span class="current">0</span> / <span class="max">${maxLength}</span></div>`);
                
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
            });
            
            // Smooth scrolling for form anchors
            $('a[href="#contact-form"]').on('click', function(e) {
                e.preventDefault();
                const target = $('#contact-form');
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 800);
                }
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
                const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
                const cleanPhone = value.replace(/[\s\-\(\)\.]/g, '');
                if (!phoneRegex.test(cleanPhone)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid phone number';
                }
            }
            
            // URL validation
            else if (fieldType === 'url' && value !== '') {
                try {
                    new URL(value);
                } catch {
                    if (!/^https?:\/\/.+/.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid URL';
                    }
                }
            }
            
            // Length validation
            const minLength = field.attr('minlength');
            const maxLength = field.attr('maxlength');
            
            if (minLength && value.length < parseInt(minLength)) {
                isValid = false;
                errorMessage = `Please enter at least ${minLength} characters`;
            }
            
            if (maxLength && value.length > parseInt(maxLength)) {
                isValid = false;
                errorMessage = `Please enter no more than ${maxLength} characters`;
            }
            
            this.setFieldValidation(field, isValid, errorMessage);
            
            return isValid;
        }
        
        validateForm() {
            let isValid = true;
            
            this.form.find('input[required], textarea[required], select[required]').each((index, element) => {
                if (!this.validateField($(element))) {
                    isValid = false;
                }
            });
            
            // Validate non-required fields that have values
            this.form.find('input[type="email"], input[type="tel"], input[type="url"]').each((index, element) => {
                if ($(element).val().trim() !== '' && !this.validateField($(element))) {
                    isValid = false;
                }
            });
            
            return isValid;
        }
        
        setFieldValidation(field, isValid, errorMessage = '') {
            const formGroup = field.closest('.form-group, .hph-form-group');
            
            // Remove existing validation classes and messages
            field.removeClass('is-invalid is-valid');
            formGroup.find('.invalid-feedback, .hph-field-error').remove();
            
            if (isValid) {
                field.addClass('is-valid');
            } else {
                field.addClass('is-invalid');
                
                // Add error message
                if (errorMessage) {
                    const errorElement = $('<div class="invalid-feedback hph-field-error"></div>').text(errorMessage);
                    
                    if (formGroup.length) {
                        formGroup.append(errorElement);
                    } else {
                        field.after(errorElement);
                    }
                }
            }
        }
        
        clearFieldValidation(field) {
            const formGroup = field.closest('.form-group, .hph-form-group');
            
            field.removeClass('is-invalid is-valid');
            formGroup.find('.invalid-feedback, .hph-field-error').remove();
        }
        
        clearValidation() {
            this.form.find('input, textarea, select').removeClass('is-invalid is-valid');
            this.form.find('.invalid-feedback, .hph-field-error').remove();
        }
        
        // Legacy compatibility methods (deprecated)
        submitForm() {
            return false;
        }
        
        setLoadingState(loading) {
        }
        
        showMessage(message, type) {
        }
        
        clearMessages() {
        }
    }
    
    // Initialize contact form validation when document is ready
    $(document).ready(function() {
        new HPHContactForm();
    });
    
    // Legacy compatibility
    window.HPHContactForm = HPHContactForm;

})(jQuery);
