/**
 * Listing Form JavaScript
 * Handles multi-step form functionality, validation, and submission
 */

(function($) {
    'use strict';

    const ListingForm = {
        currentStep: 1,
        totalSteps: 5,
        
        init: function() {
            this.bindEvents();
            this.initializeForm();
            this.loadFormSteps();
        },

        bindEvents: function() {
            // Step navigation
            $('#nextStep').on('click', this.nextStep.bind(this));
            $('#prevStep').on('click', this.prevStep.bind(this));
            
            // Form submission
            $('#listingForm').on('submit', this.submitForm.bind(this));
            
            // Character counter
            $('#short_description').on('input', this.updateCharCounter);
            
            // File upload previews
            $('#featured_image').on('change', this.previewFeaturedImage);
            $('#property_gallery').on('change', this.previewGallery);
            
            // Financial calculator
            $('#price, #hoa_fees, #property_taxes, #insurance_cost').on('input', this.calculatePayment);
            
            // Address auto-complete (if needed later)
            this.initializeAddressAutocomplete();
        },

        initializeForm: function() {
            // Set initial step
            this.showStep(1);
            this.updateProgress();
            this.updateCharCounter();
        },

        loadFormSteps: function() {
            // Load additional form steps via AJAX
            const formStepsContainer = $('.modal-body');
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'load_listing_form_steps',
                    nonce: hphDashboardSettings.dashboard_nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Find the navigation div and insert steps before it
                        const $navigation = $('.form-navigation');
                        $navigation.before(response.data.steps);
                        
                        // Re-initialize any dynamic content
                        ListingForm.initializeDynamicContent();
                    }
                },
                error: function() {
                    console.log('Failed to load form steps');
                }
            });
        },

        initializeDynamicContent: function() {
            // Re-bind events for dynamically loaded content
            $('#virtual_tour_url, #video_url').on('input', this.validateUrls);
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

        showStep: function(stepNumber) {
            // Hide all steps
            $('.form-step').removeClass('active');
            
            // Show current step
            $(`.form-step[data-step="${stepNumber}"]`).addClass('active');
            
            // Scroll to top of modal
            $('.modal-body').scrollTop(0);
        },

        updateProgress: function() {
            $('.progress-steps .step').removeClass('active completed');
            
            // Mark completed steps
            for (let i = 1; i < this.currentStep; i++) {
                $(`.progress-steps .step[data-step="${i}"]`).addClass('completed');
            }
            
            // Mark current step as active
            $(`.progress-steps .step[data-step="${this.currentStep}"]`).addClass('active');
        },

        updateNavigation: function() {
            const $prevBtn = $('#prevStep');
            const $nextBtn = $('#nextStep');
            const $submitBtn = $('#submitListing');
            
            // Show/hide previous button
            if (this.currentStep === 1) {
                $prevBtn.hide();
            } else {
                $prevBtn.show();
            }
            
            // Show/hide next/submit buttons
            if (this.currentStep === this.totalSteps) {
                $nextBtn.hide();
                $submitBtn.show();
            } else {
                $nextBtn.show();
                $submitBtn.hide();
            }
        },

        validateCurrentStep: function() {
            const $currentStep = $(`.form-step[data-step="${this.currentStep}"]`);
            const $requiredFields = $currentStep.find('[required]');
            let isValid = true;

            // Remove previous validation classes
            $currentStep.find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            $currentStep.find('.invalid-feedback').remove();

            // Validate required fields
            $requiredFields.each(function() {
                const $field = $(this);
                const value = $field.val();

                if (!value || value.trim() === '') {
                    $field.addClass('is-invalid');
                    $field.after('<div class="invalid-feedback">This field is required.</div>');
                    isValid = false;
                } else {
                    $field.addClass('is-valid');
                }
            });

            // Step-specific validations
            if (this.currentStep === 1) {
                isValid = this.validateStep1() && isValid;
            } else if (this.currentStep === 2) {
                isValid = this.validateStep2() && isValid;
            }

            return isValid;
        },

        validateStep1: function() {
            let isValid = true;
            
            // Validate price
            const price = $('#price').val();
            if (price && (isNaN(price) || parseFloat(price) < 0)) {
                $('#price').addClass('is-invalid');
                $('#price').after('<div class="invalid-feedback">Please enter a valid price.</div>');
                isValid = false;
            }

            // Validate year built
            const yearBuilt = $('#year_built').val();
            if (yearBuilt && (isNaN(yearBuilt) || yearBuilt < 1800 || yearBuilt > 2030)) {
                $('#year_built').addClass('is-invalid');
                $('#year_built').after('<div class="invalid-feedback">Please enter a valid year (1800-2030).</div>');
                isValid = false;
            }

            return isValid;
        },

        validateStep2: function() {
            // Address validation can be added here
            return true;
        },

        submitForm: function(e) {
            e.preventDefault();
            
            if (!this.validateCurrentStep()) {
                return false;
            }

            this.showLoading();

            const formData = new FormData($('#listingForm')[0]);
            
            $.ajax({
                url: hphDashboardSettings.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    ListingForm.hideLoading();
                    
                    if (response.success) {
                        ListingForm.showSuccess(response.data.message);
                        
                        // Close modal after delay
                        setTimeout(function() {
                            $('#listingFormModal').modal('hide');
                            
                            // Reload listings or redirect
                            if (typeof window.location !== 'undefined') {
                                window.location.reload();
                            }
                        }, 2000);
                    } else {
                        ListingForm.showError(response.data.message || 'An error occurred while saving the listing.');
                    }
                },
                error: function() {
                    ListingForm.hideLoading();
                    ListingForm.showError('Network error. Please try again.');
                }
            });

            return false;
        },

        showLoading: function() {
            $('#listingFormLoading').show();
        },

        hideLoading: function() {
            $('#listingFormLoading').hide();
        },

        showSuccess: function(message) {
            this.showNotification(message, 'success');
        },

        showError: function(message) {
            this.showNotification(message, 'error');
        },

        showNotification: function(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
            
            const notification = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${icon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            $('.modal-body').prepend(notification);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        },

        updateCharCounter: function() {
            const $textarea = $('#short_description');
            const $counter = $('.char-count');
            const currentLength = $textarea.val().length;
            const maxLength = $textarea.attr('maxlength') || 200;
            
            $counter.text(currentLength);
            
            if (currentLength > maxLength * 0.9) {
                $counter.addClass('text-warning');
            } else {
                $counter.removeClass('text-warning');
            }
        },

        previewFeaturedImage: function() {
            const file = this.files[0];
            const $preview = $('#featuredImagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $preview.find('img').attr('src', e.target.result);
                    $preview.show();
                };
                reader.readAsDataURL(file);
            } else {
                $preview.hide();
            }
        },

        previewGallery: function() {
            const files = this.files;
            const $preview = $('#galleryPreview');
            
            $preview.empty();
            
            Array.from(files).forEach(function(file, index) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const galleryItem = `
                        <div class="gallery-item">
                            <img src="${e.target.result}" alt="Gallery Image ${index + 1}">
                            <button type="button" class="remove-btn" onclick="removeGalleryItem(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    $preview.append(galleryItem);
                };
                reader.readAsDataURL(file);
            });
        },

        calculatePayment: function() {
            const price = parseFloat($('#price').val()) || 0;
            const hoaFees = parseFloat($('#hoa_fees').val()) || 0;
            const propertyTaxes = parseFloat($('#property_taxes').val()) || 0;
            const insurance = parseFloat($('#insurance_cost').val()) || 0;
            
            // Estimate principal & interest (assuming 30-year loan at 6% interest)
            const downPayment = price * 0.2; // 20% down
            const loanAmount = price - downPayment;
            const monthlyRate = 0.06 / 12; // 6% annual rate
            const numPayments = 30 * 12; // 30 years
            
            let principalInterest = 0;
            if (loanAmount > 0) {
                principalInterest = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numPayments)) / 
                                 (Math.pow(1 + monthlyRate, numPayments) - 1);
            }
            
            const monthlyTaxes = propertyTaxes / 12;
            const monthlyInsurance = insurance / 12;
            const totalMonthly = principalInterest + hoaFees + monthlyTaxes + monthlyInsurance;
            
            $('#principalInterest').text('$' + Math.round(principalInterest).toLocaleString());
            $('#totalMonthly').text('$' + Math.round(totalMonthly).toLocaleString());
        },

        validateUrls: function() {
            const $field = $(this);
            const url = $field.val();
            
            if (url && !this.isValidUrl(url)) {
                $field.addClass('is-invalid');
                $field.siblings('.invalid-feedback').remove();
                $field.after('<div class="invalid-feedback">Please enter a valid URL.</div>');
            } else {
                $field.removeClass('is-invalid').addClass('is-valid');
                $field.siblings('.invalid-feedback').remove();
            }
        },

        isValidUrl: function(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        },

        initializeAddressAutocomplete: function() {
            // Placeholder for Google Places autocomplete integration
            // This can be implemented when Google Maps API is set up
        }
    };

    // Global functions for removing items
    window.removePreviewImage = function(previewId) {
        $(`#${previewId}`).hide();
        $(`#${previewId}`).siblings('input[type="file"]').val('');
    };

    window.removeGalleryItem = function(index) {
        // Remove from preview
        $('.gallery-item').eq(index).remove();
        
        // Reset file input (unfortunately can't remove individual files)
        // User will need to re-select all files
        $('#property_gallery').val('');
        
        // Show message about re-selection
        if ($('.gallery-item').length === 0) {
            $('#galleryPreview').html('<div class="text-muted">Gallery cleared. Please re-select all images.</div>');
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        ListingForm.init();
    });

    // Initialize when modal is shown
    $('#listingFormModal').on('shown.bs.modal', function() {
        ListingForm.initializeForm();
    });

    // Reset form when modal is hidden
    $('#listingFormModal').on('hidden.bs.modal', function() {
        $('#listingForm')[0].reset();
        $('.form-step').removeClass('active');
        $('.form-step[data-step="1"]').addClass('active');
        ListingForm.currentStep = 1;
        ListingForm.updateProgress();
        ListingForm.updateNavigation();
        $('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
        $('.invalid-feedback, .valid-feedback').remove();
        $('#featuredImagePreview, #galleryPreview').empty().hide();
    });

    // Make ListingForm available globally
    window.ListingForm = ListingForm;

})(jQuery);