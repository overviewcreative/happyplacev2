/**
 * Enhanced Listing Components JavaScript
 * 
 * Handles all interactive functionality for the enhanced listing components including:
 * - Contact forms with AJAX
 * - Mortgage calculator with charts
 * - Interactive maps
 * - Agent components
 * - Feature filtering
 * - Hero interactions
 * 
 * @package HappyPlaceTheme
 * @version 3.0.0
 */

(function($) {
    'use strict';

    // Global namespace for enhanced components
    window.HPHEnhancedComponents = {
        contactForms: {},
        mortgageCalculators: {},
        maps: {},
        initialized: false,

        /**
         * Initialize all enhanced components
         */
        init: function() {
            if (this.initialized) return;
            
            this.initContactForms();
            this.initMortgageCalculators();
            this.initMaps();
            this.initPhotoGalleries();
            this.initVirtualTours();
            this.initFloorPlans();
            this.initAgentComponents();
            this.initFeatureComponents();
            this.initHeroComponents();
            this.initListingCards();
            
            this.initialized = true;
            console.log('HPH Enhanced Components initialized');
        },

        /**
         * Contact Form Handler
         */
        initContactForms: function() {
            $(document).on('submit', '.hph-ajax-form', this.handleContactFormSubmit);
            $(document).on('blur', '.hph-ajax-form .hph-form-control', this.validateField);
            $(document).on('change', '.hph-ajax-form select[name="form_type"]', this.handleFormTypeChange.bind(this));
        },

        handleContactFormSubmit: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const formId = $form.attr('id');
            const config = window.hphContactForms && window.hphContactForms[formId];
            
            if (!config) {
                console.error('Contact form configuration not found');
                return;
            }

            // Validate form
            if (!HPHEnhancedComponents.validateForm($form)) {
                HPHEnhancedComponents.showFormMessage($form, 'validation');
                return;
            }

            // Show loading state
            HPHEnhancedComponents.setFormLoading($form, true);

            // Prepare form data
            const formData = new FormData(this);
            formData.append('action', 'hph_submit_contact_form');
            formData.append('nonce', config.nonce);

            // Submit via AJAX
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    HPHEnhancedComponents.setFormLoading($form, false);
                    
                    if (response.success) {
                        HPHEnhancedComponents.showFormMessage($form, 'success');
                        $form[0].reset();
                        
                        // Redirect if configured
                        if (config.config.successRedirect) {
                            setTimeout(() => {
                                window.location.href = config.config.successRedirect;
                            }, 2000);
                        }
                        
                        // Track analytics
                        if (config.config.trackAnalytics && typeof gtag !== 'undefined') {
                            gtag('event', 'form_submit', {
                                event_category: 'engagement',
                                event_label: config.formType,
                                listing_id: config.listingId
                            });
                        }
                    } else {
                        HPHEnhancedComponents.showFormMessage($form, 'error', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    HPHEnhancedComponents.setFormLoading($form, false);
                    HPHEnhancedComponents.showFormMessage($form, 'error');
                    console.error('Form submission error:', error);
                }
            });
        },

        validateForm: function($form) {
            let isValid = true;
            const $fields = $form.find('[data-validate]');
            
            $fields.each(function() {
                const $field = $(this);
                const rules = $field.data('validate').split(' ');
                const value = $field.val().trim();
                let fieldValid = true;
                let errorMessage = '';
                
                // Check each validation rule
                rules.forEach(rule => {
                    if (rule === 'required' && !value) {
                        fieldValid = false;
                        errorMessage = 'This field is required';
                    } else if (rule === 'email' && value && !HPHEnhancedComponents.isValidEmail(value)) {
                        fieldValid = false;
                        errorMessage = 'Please enter a valid email address';
                    } else if (rule === 'phone' && value && !HPHEnhancedComponents.isValidPhone(value)) {
                        fieldValid = false;
                        errorMessage = 'Please enter a valid phone number';
                    } else if (rule.startsWith('maxlength:')) {
                        const maxLength = parseInt(rule.split(':')[1]);
                        if (value.length > maxLength) {
                            fieldValid = false;
                            errorMessage = `Maximum ${maxLength} characters allowed`;
                        }
                    }
                });
                
                HPHEnhancedComponents.showFieldError($field, fieldValid ? '' : errorMessage);
                if (!fieldValid) isValid = false;
            });
            
            return isValid;
        },

        validateField: function() {
            const $field = $(this);
            const $form = $field.closest('.hph-ajax-form');
            
            if ($form.length && $field.attr('data-validate')) {
                // Validate single field
                const rules = $field.data('validate').split(' ');
                const value = $field.val().trim();
                let isValid = true;
                let errorMessage = '';
                
                rules.forEach(rule => {
                    if (rule === 'required' && !value) {
                        isValid = false;
                        errorMessage = 'This field is required';
                    } else if (rule === 'email' && value && !HPHEnhancedComponents.isValidEmail(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    } else if (rule === 'phone' && value && !HPHEnhancedComponents.isValidPhone(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid phone number';
                    }
                });
                
                HPHEnhancedComponents.showFieldError($field, isValid ? '' : errorMessage);
            }
        },

        handleFormTypeChange: function(e) {
            const $select = $(e.target);
            const $form = $select.closest('.hph-ajax-form');
            const formType = $select.val();
            
            // Update form configuration based on type
            if (formType && $form.length) {
                $form.attr('data-form-type', formType);
                
                // Show/hide form fields based on type
                $form.find('.hph-form-group[data-form-types]').each(function() {
                    const $group = $(this);
                    const allowedTypes = $group.attr('data-form-types').split(',');
                    
                    if (allowedTypes.includes(formType) || allowedTypes.includes('all')) {
                        $group.show();
                    } else {
                        $group.hide();
                        // Clear hidden field values
                        $group.find('input, textarea, select').val('');
                    }
                });
            }
        },

        showFieldError: function($field, message) {
            const fieldName = $field.attr('name');
            const $errorDiv = $field.closest('.hph-form-group').find(`[data-field="${fieldName}"]`);
            
            if ($errorDiv.length) {
                $errorDiv.text(message).toggle(!!message);
            }
            
            $field.toggleClass('hph-form-control--error', !!message);
        },

        setFormLoading: function($form, loading) {
            const $submitBtn = $form.find('button[type="submit"]');
            const $btnText = $submitBtn.find('.hph-btn__text');
            const $btnLoading = $submitBtn.find('.hph-btn__loading');
            
            $submitBtn.prop('disabled', loading);
            $btnText.toggle(!loading);
            $btnLoading.toggle(loading);
        },

        showFormMessage: function($form, type, customMessage) {
            const $messagesContainer = $form.siblings('.hph-form-messages');
            const $message = $messagesContainer.find(`[data-message="${type}"]`);
            
            // Hide all messages first
            $messagesContainer.find('.hph-form-message').addClass('hph-hidden');
            
            // Show specific message
            if ($message.length) {
                if (customMessage) {
                    $message.find('.hph-form-message__text').text(customMessage);
                }
                $message.removeClass('hph-hidden');
                
                // Auto-hide after delay
                const formId = $form.attr('id');
                const config = window.hphContactForms && window.hphContactForms[formId];
                if (config && config.config.autoHideMessages) {
                    setTimeout(() => {
                        $message.addClass('hph-hidden');
                    }, config.config.autoHideMessages);
                }
            }
        },

        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        isValidPhone: function(phone) {
            const re = /^[\+]?[1-9][\d]{0,15}$/;
            const cleaned = phone.replace(/[\s\-\(\)\.]/g, '');
            return re.test(cleaned) && cleaned.length >= 10;
        },

        /**
         * Mortgage Calculator Handler
         */
        initMortgageCalculators: function() {
            $(document).on('click', '.hph-calc-submit', this.handleMortgageCalculation);
            $(document).on('input change', '.hph-calc-input', this.handleCalculatorInput);
            $(document).on('click', '.hph-down-payment-preset', this.handleDownPaymentPreset);
            $(document).on('click', '.hph-chart-tabs button', this.handleChartTabChange);
            $(document).on('click', '[id$="_share"]', this.handleCalculatorShare);
            $(document).on('click', '[id$="_print"]', this.handleCalculatorPrint);
        },

        handleMortgageCalculation: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $calc = $btn.closest('.hph-mortgage-calculator');
            const calcId = $calc.attr('id');
            const config = window.hphMortgageCalculators && window.hphMortgageCalculators[calcId];
            
            if (!config) {
                console.error('Mortgage calculator configuration not found');
                return;
            }

            // Get form values
            const values = HPHEnhancedComponents.getCalculatorValues($calc);
            
            // Calculate payments
            const results = HPHEnhancedComponents.calculateMortgage(values);
            
            // Display results
            HPHEnhancedComponents.displayMortgageResults($calc, results, config);
            
            // Show results section
            $calc.find('.hph-calc-results').removeClass('hph-hidden');
            
            // Generate charts if enabled
            if (config.config.showCharts) {
                HPHEnhancedComponents.generateMortgageCharts($calc, results, config);
            }
        },

        handleCalculatorInput: function() {
            const $input = $(this);
            const $calc = $input.closest('.hph-mortgage-calculator');
            const $form = $input.closest('.hph-calc-form');
            
            // Handle down payment sync
            if ($input.attr('name') === 'home_price' || $input.attr('name') === 'down_payment') {
                HPHEnhancedComponents.syncDownPaymentFields($calc);
            }
            
            // Auto-calculate if enabled
            if ($form.data('auto-calculate') === true) {
                clearTimeout($calc.data('calcTimeout'));
                $calc.data('calcTimeout', setTimeout(() => {
                    $calc.find('.hph-calc-submit').trigger('click');
                }, 500));
            }
        },

        handleDownPaymentPreset: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const percent = parseFloat($btn.data('percent'));
            const $calc = $btn.closest('.hph-mortgage-calculator');
            const homePrice = parseFloat($calc.find('[name="home_price"]').val()) || 0;
            
            // Update fields
            $calc.find('[name="down_payment_percent"]').val(percent);
            $calc.find('[name="down_payment"]').val(Math.round(homePrice * percent / 100));
            
            // Update button states
            $btn.siblings().removeClass('active');
            $btn.addClass('active');
            
            // Auto-calculate if enabled
            const $form = $calc.find('.hph-calc-form');
            if ($form.data('auto-calculate') === true) {
                $calc.find('.hph-calc-submit').trigger('click');
            }
        },

        syncDownPaymentFields: function($calc) {
            const homePrice = parseFloat($calc.find('[name="home_price"]').val()) || 0;
            const downPayment = parseFloat($calc.find('[name="down_payment"]').val()) || 0;
            const downPaymentPercent = parseFloat($calc.find('[name="down_payment_percent"]').val()) || 0;
            
            // Determine which field was changed and sync the other
            const $focusedField = $calc.find(':focus');
            
            if ($focusedField.attr('name') === 'down_payment') {
                // Dollar amount changed, update percentage
                const newPercent = homePrice > 0 ? (downPayment / homePrice * 100) : 0;
                $calc.find('[name="down_payment_percent"]').val(Math.round(newPercent * 100) / 100);
            } else if ($focusedField.attr('name') === 'down_payment_percent') {
                // Percentage changed, update dollar amount
                const newAmount = homePrice * downPaymentPercent / 100;
                $calc.find('[name="down_payment"]').val(Math.round(newAmount));
            } else if ($focusedField.attr('name') === 'home_price') {
                // Home price changed, update dollar amount based on current percentage
                const newAmount = homePrice * downPaymentPercent / 100;
                $calc.find('[name="down_payment"]').val(Math.round(newAmount));
            }
        },

        getCalculatorValues: function($calc) {
            return {
                homePrice: parseFloat($calc.find('[name="home_price"]').val()) || 0,
                downPayment: parseFloat($calc.find('[name="down_payment"]').val()) || 0,
                interestRate: parseFloat($calc.find('[name="interest_rate"]').val()) || 0,
                loanTerm: parseInt($calc.find('[name="loan_term"]').val()) || 30,
                propertyTaxRate: parseFloat($calc.find('[name="property_tax_rate"]').val()) || 0,
                insuranceRate: parseFloat($calc.find('[name="insurance_rate"]').val()) || 0,
                hoaFees: parseFloat($calc.find('[name="hoa_fees"]').val()) || 0,
                pmiRate: parseFloat($calc.find('[name="pmi_rate"]').val()) || 0
            };
        },

        calculateMortgage: function(values) {
            const loanAmount = values.homePrice - values.downPayment;
            const monthlyInterestRate = values.interestRate / 100 / 12;
            const numberOfPayments = values.loanTerm * 12;
            
            // Calculate principal and interest
            let principalInterest = 0;
            if (monthlyInterestRate > 0) {
                const x = Math.pow(1 + monthlyInterestRate, numberOfPayments);
                principalInterest = (loanAmount * x * monthlyInterestRate) / (x - 1);
            } else {
                principalInterest = loanAmount / numberOfPayments;
            }
            
            // Calculate other monthly costs
            const propertyTax = (values.homePrice * values.propertyTaxRate / 100) / 12;
            const insurance = (values.homePrice * values.insuranceRate / 100) / 12;
            const hoa = values.hoaFees;
            
            // Calculate PMI (if down payment < 20%)
            const downPaymentPercent = (values.downPayment / values.homePrice) * 100;
            const pmi = downPaymentPercent < 20 ? (loanAmount * values.pmiRate / 100) / 12 : 0;
            
            const totalMonthlyPayment = principalInterest + propertyTax + insurance + hoa + pmi;
            const totalInterest = (principalInterest * numberOfPayments) - loanAmount;
            const totalPaid = totalMonthlyPayment * numberOfPayments;
            
            return {
                monthlyPayment: totalMonthlyPayment,
                principalInterest: principalInterest,
                propertyTax: propertyTax,
                insurance: insurance,
                hoa: hoa,
                pmi: pmi,
                loanAmount: loanAmount,
                totalInterest: totalInterest,
                totalPaid: totalPaid,
                downPaymentPercent: downPaymentPercent
            };
        },

        displayMortgageResults: function($calc, results, config) {
            const calcId = config.calcId;
            
            // Format currency
            const formatCurrency = (amount) => {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(amount);
            };
            
            // Update display values
            $calc.find(`#${calcId}_monthly_payment`).text(formatCurrency(results.monthlyPayment));
            $calc.find(`#${calcId}_principal_interest`).text(formatCurrency(results.principalInterest));
            $calc.find(`#${calcId}_property_tax`).text(formatCurrency(results.propertyTax));
            $calc.find(`#${calcId}_insurance`).text(formatCurrency(results.insurance));
            $calc.find(`#${calcId}_hoa`).text(formatCurrency(results.hoa));
            $calc.find(`#${calcId}_pmi`).text(formatCurrency(results.pmi));
            $calc.find(`#${calcId}_loan_amount`).text(formatCurrency(results.loanAmount));
            $calc.find(`#${calcId}_total_interest`).text(formatCurrency(results.totalInterest));
            $calc.find(`#${calcId}_total_paid`).text(formatCurrency(results.totalPaid));
            
            // Hide/show PMI and HOA rows based on values
            $calc.find('.hph-breakdown-item--pmi').toggle(results.pmi > 0);
            $calc.find('.hph-breakdown-item--hoa').toggle(results.hoa > 0);
        },

        generateMortgageCharts: function($calc, results, config) {
            const calcId = config.calcId;
            const chartCanvas = $calc.find(`#${calcId}_chart`)[0];
            
            if (!chartCanvas || typeof Chart === 'undefined') {
                console.warn('Chart.js not available or canvas not found');
                return;
            }
            
            // Destroy existing chart
            if (window.mortgageCharts && window.mortgageCharts[calcId]) {
                window.mortgageCharts[calcId].destroy();
            }
            
            // Initialize chart storage
            if (!window.mortgageCharts) {
                window.mortgageCharts = {};
            }
            
            // Create payment breakdown chart
            const ctx = chartCanvas.getContext('2d');
            const chartData = {
                labels: ['Principal & Interest', 'Property Tax', 'Insurance'],
                datasets: [{
                    data: [results.principalInterest, results.propertyTax, results.insurance],
                    backgroundColor: [
                        config.config.chartColors.principalInterest,
                        config.config.chartColors.propertyTax,
                        config.config.chartColors.insurance
                    ],
                    borderWidth: 0
                }]
            };
            
            // Add HOA and PMI if applicable
            if (results.hoa > 0) {
                chartData.labels.push('HOA Fees');
                chartData.datasets[0].data.push(results.hoa);
                chartData.datasets[0].backgroundColor.push(config.config.chartColors.hoa);
            }
            
            if (results.pmi > 0) {
                chartData.labels.push('PMI');
                chartData.datasets[0].data.push(results.pmi);
                chartData.datasets[0].backgroundColor.push(config.config.chartColors.pmi);
            }
            
            window.mortgageCharts[calcId] = new Chart(ctx, {
                type: 'doughnut',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: 'USD'
                                    }).format(context.parsed);
                                    return `${context.label}: ${value}`;
                                }
                            }
                        }
                    }
                }
            });
        },

        handleChartTabChange: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const chartType = $btn.data('chart');
            const $calc = $btn.closest('.hph-mortgage-calculator');
            
            // Update tab states
            $btn.siblings().removeClass('active');
            $btn.addClass('active');
            
            // Generate appropriate chart based on type
            // This would be expanded to handle different chart types
            console.log('Chart type changed to:', chartType);
        },

        handleCalculatorShare: function(e) {
            e.preventDefault();
            
            const $calc = $(this).closest('.hph-mortgage-calculator');
            const calcId = $calc.attr('id');
            const config = window.hphMortgageCalculators && window.hphMortgageCalculators[calcId];
            
            if (navigator.share) {
                navigator.share({
                    title: 'Mortgage Calculator Results',
                    text: 'Check out this mortgage calculation',
                    url: window.location.href
                });
            } else {
                // Fallback to copying URL
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link copied to clipboard!');
                });
            }
        },

        handleCalculatorPrint: function(e) {
            e.preventDefault();
            
            const $calc = $(this).closest('.hph-mortgage-calculator');
            const $results = $calc.find('.hph-calc-results');
            
            // Create print window
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Mortgage Calculator Results</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .hph-calc-result-primary__amount { font-size: 2em; font-weight: bold; color: #2563eb; }
                        .hph-breakdown-item { display: flex; justify-content: space-between; margin: 10px 0; }
                        .hph-summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0; }
                        .hph-summary-item { text-align: center; }
                        @media print { button { display: none; } }
                    </style>
                </head>
                <body>
                    <h1>Mortgage Calculator Results</h1>
                    ${$results.html()}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        },

        /**
         * Map Components Handler
         */
        initMaps: function() {
            // Initialize maps when Google Maps API is ready
            if (typeof window.hphMapData !== 'undefined') {
                Object.keys(window.hphMapData).forEach(mapId => {
                    this.initSingleMap(mapId);
                });
            }
            
            // Handle map interactions
            $(document).on('click', '[data-map-type]', this.handleMapTypeChange);
            $(document).on('click', '[data-action="street-view"]', this.handleStreetView);
            $(document).on('click', '[data-action="directions"]', this.handleDirections);
            $(document).on('click', '[data-action="fullscreen"]', this.handleMapFullscreen);
            $(document).on('click', '.hph-filter-btn', this.handleNearbyFilter);
            $(document).on('click', '.hph-commute-calculate', this.handleCommuteCalculation);
        },

        /**
         * Photo Gallery Handler
         */
        initPhotoGalleries: function() {
            $(document).on('click', '.hph-lightbox-trigger', this.handleLightboxOpen);
            $(document).on('click', '.hph-lightbox-close', this.handleLightboxClose);
            $(document).on('click', '.hph-lightbox-nav', this.handleLightboxNavigation);
            $(document).on('click', '.hph-thumbnail-nav', this.handleThumbnailNavigation);
            $(document).on('click', '.hph-slider-nav', this.handleSliderNavigation);
            $(document).on('keydown', this.handleGalleryKeyboard);
        },

        handleLightboxOpen: function(e) {
            e.preventDefault();
            
            const $trigger = $(this);
            const $gallery = $trigger.closest('.hph-listing-photo-gallery');
            const imageIndex = parseInt($trigger.data('image-index'));
            const galleryData = JSON.parse($gallery.find('.hph-gallery-data').text());
            
            // Store current gallery context
            window.currentGallery = {
                images: galleryData.images,
                currentIndex: imageIndex,
                $gallery: $gallery
            };
            
            // Show lightbox
            const $lightbox = $gallery.find('.hph-lightbox-modal');
            HPHEnhancedComponents.displayLightboxImage(imageIndex, $lightbox);
            $lightbox.removeClass('hph-hidden');
            
            // Prevent body scroll
            $('body').addClass('hph-lightbox-open');
        },

        handleLightboxClose: function(e) {
            e.preventDefault();
            
            const $lightbox = $(this).closest('.hph-lightbox-modal');
            $lightbox.addClass('hph-hidden');
            $('body').removeClass('hph-lightbox-open');
            
            // Clear gallery context
            window.currentGallery = null;
        },

        handleLightboxNavigation: function(e) {
            e.preventDefault();
            
            if (!window.currentGallery) return;
            
            const $btn = $(this);
            const direction = $btn.hasClass('hph-lightbox-prev') ? -1 : 1;
            const newIndex = HPHEnhancedComponents.getNextImageIndex(direction);
            
            const $lightbox = $btn.closest('.hph-lightbox-modal');
            HPHEnhancedComponents.displayLightboxImage(newIndex, $lightbox);
        },

        handleThumbnailNavigation: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const imageIndex = parseInt($btn.data('image-index'));
            const $gallery = $btn.closest('.hph-listing-photo-gallery');
            
            // Update thumbnail states
            $btn.siblings().removeClass('hph-active');
            $btn.addClass('hph-active');
            
            // Update main display based on gallery style
            const galleryStyle = $gallery.data('gallery-style');
            if (galleryStyle === 'slider') {
                HPHEnhancedComponents.navigateSlider($gallery, imageIndex);
            }
            
            // Update counter
            $gallery.find('.hph-current-image').text(imageIndex + 1);
        },

        handleSliderNavigation: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $gallery = $btn.closest('.hph-listing-photo-gallery');
            const currentIndex = parseInt($gallery.find('.hph-current-image').text()) - 1;
            const direction = $btn.hasClass('hph-slider-prev') ? -1 : 1;
            const totalImages = parseInt($gallery.data('total-images'));
            
            let newIndex = currentIndex + direction;
            if (newIndex < 0) newIndex = totalImages - 1;
            if (newIndex >= totalImages) newIndex = 0;
            
            HPHEnhancedComponents.navigateSlider($gallery, newIndex);
            HPHEnhancedComponents.updateThumbnailState($gallery, newIndex);
        },

        navigateSlider: function($gallery, index) {
            const $container = $gallery.find('.hph-slider-container');
            const translateX = -100 * index;
            $container.css('transform', `translateX(${translateX}%)`);
            
            // Update counter
            $gallery.find('.hph-current-image').text(index + 1);
            
            // Lazy load next images
            const $nextItems = $gallery.find('.hph-slider-item').slice(index, index + 3);
            $nextItems.find('img[data-src]').each(function() {
                const $img = $(this);
                $img.attr('src', $img.data('src')).removeAttr('data-src');
            });
        },

        updateThumbnailState: function($gallery, index) {
            const $thumbnails = $gallery.find('.hph-thumbnail-nav');
            $thumbnails.removeClass('hph-active');
            $thumbnails.eq(index).addClass('hph-active');
        },

        displayLightboxImage: function(index, $lightbox) {
            if (!window.currentGallery) return;
            
            const images = window.currentGallery.images;
            const image = images[index];
            
            if (!image) return;
            
            // Update image
            const $lightboxImage = $lightbox.find('.hph-lightbox-image');
            $lightboxImage.attr('src', image.url || image.image_url);
            $lightboxImage.attr('alt', image.alt || image.caption || '');
            
            // Update caption
            const $caption = $lightbox.find('.hph-lightbox-caption');
            $caption.text(image.caption || '');
            
            // Update counter
            $lightbox.find('.hph-current-lightbox-image').text(index + 1);
            $lightbox.find('.hph-total-lightbox-images').text(images.length);
            
            // Update context
            window.currentGallery.currentIndex = index;
            
            // Show/hide navigation buttons
            const $prevBtn = $lightbox.find('.hph-lightbox-prev');
            const $nextBtn = $lightbox.find('.hph-lightbox-next');
            $prevBtn.toggle(images.length > 1);
            $nextBtn.toggle(images.length > 1);
        },

        getNextImageIndex: function(direction) {
            if (!window.currentGallery) return 0;
            
            const current = window.currentGallery.currentIndex;
            const total = window.currentGallery.images.length;
            
            let newIndex = current + direction;
            if (newIndex < 0) newIndex = total - 1;
            if (newIndex >= total) newIndex = 0;
            
            return newIndex;
        },

        handleGalleryKeyboard: function(e) {
            if (!window.currentGallery) return;
            
            switch(e.keyCode) {
                case 27: // Escape
                    $('.hph-lightbox-close').trigger('click');
                    break;
                case 37: // Left arrow
                    $('.hph-lightbox-prev').trigger('click');
                    break;
                case 39: // Right arrow
                    $('.hph-lightbox-next').trigger('click');
                    break;
            }
        },

        /**
         * Virtual Tour Handler
         */
        initVirtualTours: function() {
            $(document).on('click', '.hph-tour-tab', this.handleTourTabChange);
            $(document).on('click', '.hph-tour-nav', this.handleTourNavigation);
            $(document).on('click', '.hph-tour-thumbnail', this.handleTourThumbnailClick);
            $(document).on('click', '.hph-360-hotspot', this.handleHotspotClick);
            $(document).on('click', '.hph-hotspot-close', this.handleHotspotClose);
            $(document).on('click', '.hph-360-reset', this.handle360Reset);
            $(document).on('click', '.hph-360-fullscreen', this.handle360Fullscreen);
            
            // Initialize 360° viewers
            $('.hph-360-viewer').each(function() {
                HPHEnhancedComponents.init360Viewer($(this));
            });
        },

        handleTourTabChange: function(e) {
            e.preventDefault();
            
            const $tab = $(this);
            const tourIndex = parseInt($tab.data('tour-index'));
            const $container = $tab.closest('.hph-tour-tabs-container');
            
            // Update tab states
            $tab.siblings().removeClass('hph-border-primary hph-text-primary hph-bg-primary-10')
                .addClass('hph-border-transparent hph-text-gray-600');
            $tab.removeClass('hph-border-transparent hph-text-gray-600')
                .addClass('hph-border-primary hph-text-primary hph-bg-primary-10');
            
            // Update panel visibility
            const $panels = $container.find('.hph-tour-panel');
            $panels.addClass('hph-hidden');
            $panels.eq(tourIndex).removeClass('hph-hidden');
            
            // Update counter
            const $tour = $tab.closest('.hph-listing-virtual-tour');
            $tour.find('.hph-current-tour').text(tourIndex + 1);
        },

        handleTourNavigation: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $tour = $btn.closest('.hph-listing-virtual-tour');
            const currentIndex = parseInt($tour.find('.hph-current-tour').text()) - 1;
            const direction = $btn.hasClass('hph-tour-prev') ? -1 : 1;
            const totalTours = parseInt($tour.data('total-tours'));
            
            let newIndex = currentIndex + direction;
            if (newIndex < 0) newIndex = totalTours - 1;
            if (newIndex >= totalTours) newIndex = 0;
            
            HPHEnhancedComponents.navigateToTour($tour, newIndex);
        },

        handleTourThumbnailClick: function(e) {
            e.preventDefault();
            
            const $thumbnail = $(this);
            const tourIndex = parseInt($thumbnail.data('tour-index'));
            const $tour = $thumbnail.closest('.hph-listing-virtual-tour');
            
            HPHEnhancedComponents.navigateToTour($tour, tourIndex);
            HPHEnhancedComponents.updateTourThumbnailState($tour, tourIndex);
        },

        navigateToTour: function($tour, index) {
            // Hide all tour items
            const $items = $tour.find('.hph-tour-item');
            $items.addClass('hph-hidden');
            $items.eq(index).removeClass('hph-hidden');
            
            // Update counter
            $tour.find('.hph-current-tour').text(index + 1);
            
            // Update thumbnail state
            HPHEnhancedComponents.updateTourThumbnailState($tour, index);
        },

        updateTourThumbnailState: function($tour, index) {
            const $thumbnails = $tour.find('.hph-tour-thumbnail');
            $thumbnails.removeClass('hph-border-primary');
            $thumbnails.eq(index).addClass('hph-border-primary');
        },

        handleHotspotClick: function(e) {
            e.preventDefault();
            
            const $hotspot = $(this);
            const hotspotInfo = $hotspot.data('hotspot-info');
            const $tour = $hotspot.closest('.hph-listing-virtual-tour');
            const $modal = $tour.find('.hph-hotspot-modal');
            
            // Populate modal content
            const $content = $modal.find('.hph-hotspot-info-content');
            $content.html(`
                <h4 class="hph-h4 hph-mb-3">${hotspotInfo.title || 'Point of Interest'}</h4>
                <p class="hph-text-gray-600 hph-mb-4">${hotspotInfo.description || ''}</p>
                ${hotspotInfo.features ? `
                    <div class="hph-hotspot-features">
                        <h5 class="hph-text-sm hph-font-medium hph-mb-2">Features:</h5>
                        <ul class="hph-list-disc hph-pl-4">
                            ${hotspotInfo.features.map(feature => `<li>${feature}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            `);
            
            // Show modal
            $modal.removeClass('hph-hidden');
        },

        handleHotspotClose: function(e) {
            e.preventDefault();
            
            const $modal = $(this).closest('.hph-hotspot-modal');
            $modal.addClass('hph-hidden');
        },

        init360Viewer: function($viewer) {
            const $canvas = $viewer.find('.hph-360-canvas');
            const imageUrl = $canvas.data('image-url');
            
            if (!imageUrl || !$canvas.length) return;
            
            // Basic 360° viewer implementation
            // This would integrate with a library like Photo Sphere Viewer or custom WebGL
            console.log('Initializing 360° viewer for:', imageUrl);
            
            // Placeholder for 360° implementation
            const canvas = $canvas[0];
            const ctx = canvas.getContext('2d');
            
            // Load and display the 360° image
            const img = new Image();
            img.onload = function() {
                canvas.width = $viewer.width();
                canvas.height = $viewer.height();
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            };
            img.src = imageUrl;
        },

        handle360Reset: function(e) {
            e.preventDefault();
            
            const $viewer = $(this).closest('.hph-360-viewer');
            // Reset 360° view to default position
            console.log('Resetting 360° view');
        },

        handle360Fullscreen: function(e) {
            e.preventDefault();
            
            const $viewer = $(this).closest('.hph-360-viewer');
            const viewerElement = $viewer[0];
            
            if (viewerElement.requestFullscreen) {
                viewerElement.requestFullscreen();
            } else if (viewerElement.webkitRequestFullscreen) {
                viewerElement.webkitRequestFullscreen();
            } else if (viewerElement.msRequestFullscreen) {
                viewerElement.msRequestFullscreen();
            }
        },

        /**
         * Floor Plans Handler
         */
        initFloorPlans: function() {
            $(document).on('click', '.hph-floor-plan-tab', this.handleFloorPlanTabChange);
            $(document).on('click', '.hph-floor-plan-nav', this.handleFloorPlanNavigation);
            $(document).on('click', '.hph-floor-plan-thumbnail', this.handleFloorPlanThumbnailClick);
            $(document).on('click', '.hph-room-hotspot', this.handleRoomHotspotClick);
            $(document).on('click', '.hph-room-modal-close', this.handleRoomModalClose);
            $(document).on('click', '.hph-zoom-in', this.handleFloorPlanZoomIn);
            $(document).on('click', '.hph-zoom-out', this.handleFloorPlanZoomOut);
            $(document).on('click', '.hph-zoom-reset', this.handleFloorPlanZoomReset);
            
            // Initialize zoom functionality
            $('.hph-floor-plan-zoom-container').each(function() {
                HPHEnhancedComponents.initFloorPlanZoom($(this));
            });
        },

        handleFloorPlanTabChange: function(e) {
            e.preventDefault();
            
            const $tab = $(this);
            const planIndex = parseInt($tab.data('plan-index'));
            const $container = $tab.closest('.hph-floor-plans-tabs-container');
            
            // Update tab states
            $tab.siblings().removeClass('hph-border-primary hph-text-primary hph-bg-primary-10')
                .addClass('hph-border-transparent hph-text-gray-600');
            $tab.removeClass('hph-border-transparent hph-text-gray-600')
                .addClass('hph-border-primary hph-text-primary hph-bg-primary-10');
            
            // Update panel visibility
            const $panels = $container.find('.hph-floor-plan-panel');
            $panels.addClass('hph-hidden');
            $panels.eq(planIndex).removeClass('hph-hidden');
            
            // Update counter
            const $floorPlans = $tab.closest('.hph-listing-floor-plans');
            $floorPlans.find('.hph-current-plan').text(planIndex + 1);
        },

        handleFloorPlanNavigation: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $floorPlans = $btn.closest('.hph-listing-floor-plans');
            const currentIndex = parseInt($floorPlans.find('.hph-current-plan').text()) - 1;
            const direction = $btn.hasClass('hph-floor-plan-prev') ? -1 : 1;
            const totalPlans = parseInt($floorPlans.data('total-plans'));
            
            let newIndex = currentIndex + direction;
            if (newIndex < 0) newIndex = totalPlans - 1;
            if (newIndex >= totalPlans) newIndex = 0;
            
            HPHEnhancedComponents.navigateToFloorPlan($floorPlans, newIndex);
        },

        handleFloorPlanThumbnailClick: function(e) {
            e.preventDefault();
            
            const $thumbnail = $(this);
            const planIndex = parseInt($thumbnail.data('plan-index'));
            const $floorPlans = $thumbnail.closest('.hph-listing-floor-plans');
            
            HPHEnhancedComponents.navigateToFloorPlan($floorPlans, planIndex);
            HPHEnhancedComponents.updateFloorPlanThumbnailState($floorPlans, planIndex);
        },

        navigateToFloorPlan: function($floorPlans, index) {
            // Hide all floor plan items
            const $items = $floorPlans.find('.hph-floor-plan-item');
            $items.addClass('hph-hidden');
            $items.eq(index).removeClass('hph-hidden');
            
            // Update counter
            $floorPlans.find('.hph-current-plan').text(index + 1);
            
            // Update thumbnail state
            HPHEnhancedComponents.updateFloorPlanThumbnailState($floorPlans, index);
        },

        updateFloorPlanThumbnailState: function($floorPlans, index) {
            const $thumbnails = $floorPlans.find('.hph-floor-plan-thumbnail');
            $thumbnails.removeClass('hph-border-primary');
            $thumbnails.eq(index).addClass('hph-border-primary');
        },

        handleRoomHotspotClick: function(e) {
            e.preventDefault();
            
            const $hotspot = $(this);
            const roomInfo = $hotspot.data('room-info');
            const $floorPlans = $hotspot.closest('.hph-listing-floor-plans');
            const $modal = $floorPlans.find('.hph-room-modal');
            
            // Populate modal content
            const $content = $modal.find('.hph-room-info-content');
            $content.html(`
                <h4 class="hph-h4 hph-mb-3">${roomInfo.room_name || 'Room Details'}</h4>
                ${roomInfo.dimensions ? `<p class="hph-text-sm hph-text-gray-600 hph-mb-2"><strong>Dimensions:</strong> ${roomInfo.dimensions}</p>` : ''}
                ${roomInfo.square_feet ? `<p class="hph-text-sm hph-text-gray-600 hph-mb-2"><strong>Square Feet:</strong> ${roomInfo.square_feet}</p>` : ''}
                ${roomInfo.description ? `<p class="hph-text-gray-600 hph-mb-4">${roomInfo.description}</p>` : ''}
                ${roomInfo.features && roomInfo.features.length > 0 ? `
                    <div class="hph-room-features">
                        <h5 class="hph-text-sm hph-font-medium hph-mb-2">Features:</h5>
                        <ul class="hph-list-disc hph-pl-4">
                            ${roomInfo.features.map(feature => `<li class="hph-text-sm">${feature}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            `);
            
            // Show modal
            $modal.removeClass('hph-hidden');
        },

        handleRoomModalClose: function(e) {
            e.preventDefault();
            
            const $modal = $(this).closest('.hph-room-modal');
            $modal.addClass('hph-hidden');
        },

        initFloorPlanZoom: function($container) {
            const $image = $container.find('.hph-floor-plan-image');
            let scale = 1;
            let translateX = 0;
            let translateY = 0;
            let isDragging = false;
            let lastX = 0;
            let lastY = 0;
            
            // Store zoom state
            $container.data('zoom-state', {
                scale: scale,
                translateX: translateX,
                translateY: translateY
            });
            
            // Mouse wheel zoom
            $container.on('wheel', function(e) {
                e.preventDefault();
                
                const delta = e.originalEvent.deltaY;
                const zoomIntensity = 0.1;
                const newScale = delta > 0 ? scale - zoomIntensity : scale + zoomIntensity;
                
                HPHEnhancedComponents.setFloorPlanZoom($container, Math.max(1, Math.min(5, newScale)), translateX, translateY);
            });
            
            // Mouse drag for panning
            $container.on('mousedown', function(e) {
                if (scale > 1) {
                    isDragging = true;
                    lastX = e.clientX;
                    lastY = e.clientY;
                    $container.css('cursor', 'grabbing');
                }
            });
            
            $(document).on('mousemove', function(e) {
                if (isDragging) {
                    const deltaX = e.clientX - lastX;
                    const deltaY = e.clientY - lastY;
                    
                    translateX += deltaX;
                    translateY += deltaY;
                    
                    HPHEnhancedComponents.setFloorPlanZoom($container, scale, translateX, translateY);
                    
                    lastX = e.clientX;
                    lastY = e.clientY;
                }
            });
            
            $(document).on('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    $container.css('cursor', scale > 1 ? 'grab' : 'zoom-in');
                }
            });
            
            // Click to zoom
            $container.on('click', function(e) {
                if (!isDragging && scale === 1) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    // Center zoom on click point
                    const newTranslateX = -(x * 1.5 - rect.width / 2);
                    const newTranslateY = -(y * 1.5 - rect.height / 2);
                    
                    HPHEnhancedComponents.setFloorPlanZoom($container, 2, newTranslateX, newTranslateY);
                }
            });
        },

        setFloorPlanZoom: function($container, newScale, newTranslateX, newTranslateY) {
            const $image = $container.find('.hph-floor-plan-image');
            
            // Update stored state
            const state = $container.data('zoom-state');
            state.scale = newScale;
            state.translateX = newTranslateX;
            state.translateY = newTranslateY;
            
            // Apply transform
            $image.css('transform', `scale(${newScale}) translate(${newTranslateX}px, ${newTranslateY}px)`);
            
            // Update cursor
            $container.css('cursor', newScale > 1 ? 'grab' : 'zoom-in');
            
            // Update global scale for button handlers
            $container.data('current-scale', newScale);
        },

        handleFloorPlanZoomIn: function(e) {
            e.preventDefault();
            
            const $container = $(this).closest('.hph-floor-plan-item').find('.hph-floor-plan-zoom-container');
            const state = $container.data('zoom-state');
            const newScale = Math.min(5, state.scale + 0.5);
            
            HPHEnhancedComponents.setFloorPlanZoom($container, newScale, state.translateX, state.translateY);
        },

        handleFloorPlanZoomOut: function(e) {
            e.preventDefault();
            
            const $container = $(this).closest('.hph-floor-plan-item').find('.hph-floor-plan-zoom-container');
            const state = $container.data('zoom-state');
            const newScale = Math.max(1, state.scale - 0.5);
            
            HPHEnhancedComponents.setFloorPlanZoom($container, newScale, state.translateX, state.translateY);
        },

        handleFloorPlanZoomReset: function(e) {
            e.preventDefault();
            
            const $container = $(this).closest('.hph-floor-plan-item').find('.hph-floor-plan-zoom-container');
            HPHEnhancedComponents.setFloorPlanZoom($container, 1, 0, 0);
        },

        initSingleMap: function(mapId) {
            const mapData = window.hphMapData[mapId];
            if (!mapData || typeof google === 'undefined') return;
            
            const mapElement = document.getElementById(mapId);
            if (!mapElement) return;
            
            // Map options
            const mapOptions = {
                center: { lat: mapData.lat, lng: mapData.lng },
                zoom: mapData.zoom,
                mapTypeId: mapData.mapType,
                styles: this.getMapStyles(),
                zoomControl: true,
                mapTypeControl: false,
                scaleControl: true,
                streetViewControl: mapData.features.showStreetView,
                rotateControl: false,
                fullscreenControl: mapData.features.enableFullscreen
            };
            
            // Create map
            const map = new google.maps.Map(mapElement, mapOptions);
            
            // Add property marker
            const marker = new google.maps.Marker({
                position: { lat: mapData.lat, lng: mapData.lng },
                map: map,
                title: mapData.property.title,
                icon: {
                    url: '/wp-content/themes/happy-place-theme/assets/images/map-marker-property.svg',
                    scaledSize: new google.maps.Size(40, 40)
                }
            });
            
            // Add info window
            const infoWindow = new google.maps.InfoWindow({
                content: this.createPropertyInfoWindow(mapData.property)
            });
            
            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
            
            // Store map reference
            if (!window.hphMaps) window.hphMaps = {};
            window.hphMaps[mapId] = {
                map: map,
                marker: marker,
                infoWindow: infoWindow,
                data: mapData
            };
            
            // Hide loading state
            $(mapElement).find('.hph-map-loading').hide();
        },

        createPropertyInfoWindow: function(property) {
            return `
                <div class="hph-map-info-window" style="max-width: 300px;">
                    ${property.image ? `<img src="${property.image}" alt="${property.title}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 12px;">` : ''}
                    <h4 style="margin: 0 0 8px 0; font-size: 16px; font-weight: bold;">${property.title}</h4>
                    ${property.price ? `<div style="font-size: 18px; font-weight: bold; color: #2563eb; margin-bottom: 8px;">${property.price}</div>` : ''}
                    <div style="display: flex; gap: 12px; margin-bottom: 12px; font-size: 14px;">
                        ${property.bedrooms ? `<span>${property.bedrooms} beds</span>` : ''}
                        ${property.bathrooms ? `<span>${property.bathrooms} baths</span>` : ''}
                        ${property.squareFeet ? `<span>${property.squareFeet} sq ft</span>` : ''}
                    </div>
                    <a href="${property.url}" style="display: inline-block; background: #2563eb; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-size: 14px;">View Details</a>
                </div>
            `;
        },

        getMapStyles: function() {
            // Custom map styling for better brand integration
            return [
                {
                    featureType: "poi",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                },
                {
                    featureType: "transit",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                }
            ];
        },

        /**
         * Other Component Handlers
         */
        initAgentComponents: function() {
            $(document).on('click', '[data-action="contact-form"]', this.handleAgentContact);
        },

        initFeatureComponents: function() {
            $(document).on('click', '[data-action="show-more-features"]', this.handleShowMoreFeatures);
        },

        initHeroComponents: function() {
            $(document).on('click', '.hph-hero-gallery-nav button', this.handleHeroNavigation);
            $(document).on('click', '.hph-hero-action', this.handleHeroAction);
        },

        initListingCards: function() {
            $(document).on('click', '.hph-card-favorite', this.handleFavoriteToggle);
            $(document).on('click', '.hph-card-share', this.handleCardShare);
            $(document).on('click', '.hph-card-compare', this.handleCardCompare);
        },

        handleAgentContact: function(e) {
            e.preventDefault();
            const agentId = $(this).data('agent-id');
            // Trigger contact form modal or scroll to contact form
            console.log('Contact agent:', agentId);
        },

        handleShowMoreFeatures: function(e) {
            e.preventDefault();
            const $btn = $(this);
            const category = $btn.data('category');
            // Show more features logic
            console.log('Show more features for:', category);
        },

        handleHeroNavigation: function(e) {
            e.preventDefault();
            const $btn = $(this);
            const direction = $btn.data('direction');
            // Hero gallery navigation logic
            console.log('Hero navigation:', direction);
        },

        handleHeroAction: function(e) {
            e.preventDefault();
            const $btn = $(this);
            const action = $btn.data('action');
            // Hero action handling
            console.log('Hero action:', action);
        },

        handleFavoriteToggle: function(e) {
            e.preventDefault();
            const $btn = $(this);
            const listingId = $btn.closest('.hph-listing-card').data('listing-id');
            // Favorite toggle logic
            $btn.toggleClass('hph-card-favorite--active');
            console.log('Favorite toggle:', listingId);
        },

        handleCardShare: function(e) {
            e.preventDefault();
            const $card = $(this).closest('.hph-listing-card');
            const listingUrl = $card.find('.hph-card-title a').attr('href');
            
            if (navigator.share) {
                navigator.share({
                    title: $card.find('.hph-card-title').text(),
                    url: listingUrl
                });
            }
        },

        handleCardCompare: function(e) {
            e.preventDefault();
            const listingId = $(this).closest('.hph-listing-card').data('listing-id');
            // Compare functionality
            console.log('Add to compare:', listingId);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        HPHEnhancedComponents.init();
    });

    // Global functions for external access
    window.initHphMap = function(mapId) {
        HPHEnhancedComponents.initSingleMap(mapId);
    };

    window.initHphMortgageCalculator = function(calcId) {
        // Calculator is initialized via event delegation
        console.log('Mortgage calculator ready:', calcId);
    };

})(jQuery);