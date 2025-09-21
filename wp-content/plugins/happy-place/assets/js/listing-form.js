/**
 * Listing Form JavaScript
 * Handles multi-step form functionality, validation, and submission
 * 
 * @package HappyPlace
 * @version 1.0.0
 */

(function($) {
    'use strict';

    const ListingForm = {
        currentStep: 1,
        totalSteps: 5,
        
        init: function() {
            // Only initialize if the form exists on the page
            if ($('#listingForm').length === 0) {
                return; // No listing form found, skip initialization
            }
            
            this.bindEvents();
            this.initializeForm();
            this.initializeValidation();
        },

        bindEvents: function() {
            // Step navigation
            $(document).on('click', '#nextStep', this.nextStep.bind(this));
            $(document).on('click', '#prevStep', this.prevStep.bind(this));
            
            // Form submission
            $(document).on('submit', '#listingForm', this.submitForm.bind(this));
            
            // Real-time validation
            $(document).on('blur', '.form-control', this.validateField.bind(this));
            
            // Character counter
            $(document).on('input', '#short_description', this.updateCharCounter);
            
            // File upload previews
            $(document).on('change', '#featured_image', this.previewFeaturedImage);
            $(document).on('change', '#property_gallery', this.previewGallery);
            
            // Financial calculator
            $(document).on('input', '#price, #hoa_fees, #property_taxes, #insurance_cost', this.calculatePayment);
        },

        initializeForm: function() {
            // Set initial step
            this.showStep(1);
            this.updateProgress();
            this.updateCharCounter();
        },

        initializeValidation: function() {
            // Set up form validation rules
            if (typeof $.fn.validate !== 'undefined') {
                $('#listingForm').validate({
                    rules: {
                        title: {
                            required: true,
                            minlength: 10
                        },
                        price: {
                            required: true,
                            number: true,
                            min: 1
                        },
                        street_address: {
                            required: true,
                            minlength: 5
                        },
                        city: {
                            required: true,
                            minlength: 2
                        },
                        state: {
                            required: true
                        },
                        zip_code: {
                            required: true,
                            pattern: /^\d{5}(-\d{4})?$/
                        },
                        bedrooms: {
                            required: true,
                            min: 0
                        },
                        bathrooms: {
                            required: true,
                            min: 0
                        },
                        square_feet: {
                            required: true,
                            min: 1
                        }
                    },
                    messages: {
                        title: {
                            required: "Please enter a listing title",
                            minlength: "Title must be at least 10 characters long"
                        },
                        price: {
                            required: "Please enter a price",
                            number: "Please enter a valid number",
                            min: "Price must be greater than 0"
                        },
                        street_address: {
                            required: "Please enter a street address",
                            minlength: "Address must be at least 5 characters long"
                        },
                        city: {
                            required: "Please enter a city",
                            minlength: "City must be at least 2 characters long"
                        },
                        state: {
                            required: "Please select a state"
                        },
                        zip_code: {
                            required: "Please enter a ZIP code",
                            pattern: "Please enter a valid ZIP code (12345 or 12345-6789)"
                        }
                    },
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: function(error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('.form-group').append(error);
                    },
                    highlight: function(element) {
                        $(element).addClass('is-invalid').removeClass('is-valid');
                    },
                    unhighlight: function(element) {
                        $(element).addClass('is-valid').removeClass('is-invalid');
                    }
                });
            }
        },

        nextStep: function(e) {
            e.preventDefault();
            
            if (this.validateCurrentStep()) {
                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                    this.showStep(this.currentStep);
                    this.updateProgress();
                    this.updateNavigation();
                }
            }
        },

        prevStep: function(e) {
            e.preventDefault();
            
            if (this.currentStep > 1) {
                this.currentStep--;
                this.showStep(this.currentStep);
                this.updateProgress();
                this.updateNavigation();
            }
        },

        showStep: function(step) {
            $('.form-step').hide();
            $('.form-step[data-step="' + step + '"]').show();
            
            // Update step indicators
            $('.step-indicator').removeClass('active completed');
            for (let i = 1; i < step; i++) {
                $('.step-indicator[data-step="' + i + '"]').addClass('completed');
            }
            $('.step-indicator[data-step="' + step + '"]').addClass('active');
        },

        updateProgress: function() {
            const progress = (this.currentStep / this.totalSteps) * 100;
            $('.progress-bar').css('width', progress + '%');
            $('.step-counter').text(this.currentStep + ' of ' + this.totalSteps);
        },

        updateNavigation: function() {
            // Show/hide navigation buttons
            if (this.currentStep === 1) {
                $('#prevStep').hide();
            } else {
                $('#prevStep').show();
            }
            
            if (this.currentStep === this.totalSteps) {
                $('#nextStep').hide();
                $('#submitForm').show();
            } else {
                $('#nextStep').show();
                $('#submitForm').hide();
            }
        },

        validateCurrentStep: function() {
            let isValid = true;
            const currentStepElement = $('.form-step[data-step="' + this.currentStep + '"]');
            
            // Validate all required fields in current step
            currentStepElement.find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });
            
            return isValid;
        },

        validateField: function(e) {
            const $field = $(e.target);
            const value = $field.val();
            const fieldName = $field.attr('name');
            
            // Remove existing feedback
            $field.siblings('.invalid-feedback, .valid-feedback').remove();
            $field.removeClass('is-invalid is-valid');
            
            // Required field validation
            if ($field.attr('required') && !value) {
                this.showFieldError($field, 'This field is required');
                return false;
            }
            
            // Specific field validations
            switch (fieldName) {
                case 'email':
                    if (value && !this.isValidEmail(value)) {
                        this.showFieldError($field, 'Please enter a valid email address');
                        return false;
                    }
                    break;
                case 'price':
                    if (value && (!$.isNumeric(value) || value <= 0)) {
                        this.showFieldError($field, 'Please enter a valid price');
                        return false;
                    }
                    break;
                case 'zip_code':
                    if (value && !/^\d{5}(-\d{4})?$/.test(value)) {
                        this.showFieldError($field, 'Please enter a valid ZIP code');
                        return false;
                    }
                    break;
            }
            
            // If we get here, field is valid
            $field.addClass('is-valid');
            return true;
        },

        showFieldError: function($field, message) {
            $field.addClass('is-invalid');
            $field.after('<div class="invalid-feedback">' + message + '</div>');
        },

        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        updateCharCounter: function() {
            const $field = $(this);
            const maxLength = $field.attr('maxlength') || 500;
            const currentLength = $field.val().length;
            const $counter = $field.siblings('.char-counter');
            
            if ($counter.length) {
                $counter.text(currentLength + '/' + maxLength);
                
                if (currentLength > maxLength * 0.9) {
                    $counter.addClass('text-warning');
                } else {
                    $counter.removeClass('text-warning');
                }
            }
        },

        previewFeaturedImage: function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#featuredImagePreview').html('<img src="' + e.target.result + '" style="max-width: 200px; max-height: 200px;">').show();
                };
                reader.readAsDataURL(file);
            }
        },

        previewGallery: function(e) {
            const files = e.target.files;
            const $preview = $('#galleryPreview');
            $preview.empty();
            
            for (let i = 0; i < files.length && i < 10; i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $preview.append('<img src="' + e.target.result + '" style="max-width: 100px; max-height: 100px; margin: 5px;">');
                };
                reader.readAsDataURL(files[i]);
            }
            $preview.show();
        },

        calculatePayment: function() {
            const price = parseFloat($('#price').val()) || 0;
            const hoa = parseFloat($('#hoa_fees').val()) || 0;
            const taxes = parseFloat($('#property_taxes').val()) || 0;
            const insurance = parseFloat($('#insurance_cost').val()) || 0;
            
            // Simple mortgage calculation (assuming 20% down, 30-year, 4% interest)
            const principal = price * 0.8;
            const monthlyRate = 0.04 / 12;
            const numPayments = 30 * 12;
            
            let monthlyPayment = 0;
            if (principal > 0) {
                monthlyPayment = principal * (monthlyRate * Math.pow(1 + monthlyRate, numPayments)) / 
                                (Math.pow(1 + monthlyRate, numPayments) - 1);
            }
            
            const totalMonthly = monthlyPayment + (hoa / 12) + (taxes / 12) + (insurance / 12);
            
            $('#estimatedPayment').text('$' + Math.round(totalMonthly).toLocaleString());
        },

        submitForm: function(e) {
            e.preventDefault();
            
            if (!this.validateForm()) {
                return false;
            }
            
            const $form = $(e.target);
            const formData = new FormData($form[0]);
            
            // Add AJAX action
            formData.append('action', 'save_listing');
            
            // Show loading state
            $form.find('button[type="submit"]').prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: typeof HP_ListingForm !== 'undefined' ? HP_ListingForm.ajax_url : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('Listing saved successfully!');
                        // Redirect or reload as needed
                        if (response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        }
                    } else {
                        alert('Error: ' + (response.data.message || 'An error occurred'));
                    }
                },
                error: function() {
                    alert('An error occurred while saving the listing');
                },
                complete: function() {
                    $form.find('button[type="submit"]').prop('disabled', false).text('Save Listing');
                }
            });
        },

        validateForm: function() {
            let isValid = true;
            
            // Validate all required fields
            $('#listingForm [required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });
            
            return isValid;
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        ListingForm.init();
    });

    // Make ListingForm available globally
    window.ListingForm = ListingForm;

})(jQuery);
