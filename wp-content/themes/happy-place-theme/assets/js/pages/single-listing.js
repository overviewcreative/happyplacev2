/**
 * Single Listing JavaScript
 * 
 * Handles interactive functionality for individual property listings
 * Including photo gallery, favorite toggle, sharing, contact forms, etc.
 * 
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

(function($) {
    'use strict';

    // Main single listing class
    class SingleListingHandler {
        constructor() {
            this.init();
        }

        init() {
            this.setupPhotoGallery();
            this.setupActionButtons();
            this.setupMortgageCalculator();
            this.setupContactForms();
            this.bindEvents();
        }

        /**
         * Photo Gallery Navigation
         */
        setupPhotoGallery() {
            const $photoDisplay = $('#hero-photo-display');
            const $photos = $photoDisplay.find('.hero-photo');
            const $prevBtn = $('#photo-prev');
            const $nextBtn = $('#photo-next');
            const $counter = $('#photo-current');
            let currentPhoto = 0;

            if ($photos.length <= 1) {
                $prevBtn.hide();
                $nextBtn.hide();
                return;
            }

            // Navigation function
            const showPhoto = (index) => {
                if (index < 0) index = $photos.length - 1;
                if (index >= $photos.length) index = 0;

                $photos.removeClass('active').eq(index).addClass('active');
                $counter.text(index + 1);
                currentPhoto = index;
            };

            // Event listeners
            $prevBtn.on('click', () => showPhoto(currentPhoto - 1));
            $nextBtn.on('click', () => showPhoto(currentPhoto + 1));

            // Keyboard navigation
            $(document).on('keydown', (e) => {
                if ($('.modal').is(':visible')) return;

                switch(e.key) {
                    case 'ArrowLeft':
                        e.preventDefault();
                        showPhoto(currentPhoto - 1);
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        showPhoto(currentPhoto + 1);
                        break;
                }
            });
        }

        /**
         * Action Buttons (Favorite, Share, etc.)
         */
        setupActionButtons() {
            // Favorite toggle
            $(document).on('click', '.favorite-btn', (e) => {
                e.preventDefault();
                const $btn = $(e.currentTarget);
                const listingId = $btn.data('listing');
                
                this.toggleFavorite(listingId, $btn);
            });

            // Share button
            $(document).on('click', '.share-btn', (e) => {
                e.preventDefault();
                this.showShareOptions();
            });

            // Schedule tour
            $('#schedule-tour').on('click', (e) => {
                e.preventDefault();
                const listingId = $(e.currentTarget).data('listing');
                this.showScheduleTour(listingId);
            });
        }

        /**
         * Toggle favorite status
         */
        toggleFavorite(listingId, $btn) {
            const isCurrentlyFavorite = $btn.hasClass('is-favorite');
            
            // Optimistic UI update
            if (isCurrentlyFavorite) {
                $btn.removeClass('is-favorite')
                    .find('i').removeClass('fas').addClass('far');
            } else {
                $btn.addClass('is-favorite')
                    .find('i').removeClass('far').addClass('fas');
            }

            // Show notification
            const message = isCurrentlyFavorite ? 
                'Property removed from favorites' : 
                'Property saved to favorites';
            this.showNotification(message, 'success');
        }

        /**
         * Share functionality
         */
        showShareOptions() {
            const currentUrl = window.location.href;
            
            if (navigator.share) {
                // Use native sharing if available
                navigator.share({
                    title: document.title,
                    url: currentUrl
                });
            } else {
                // Fallback to copy link
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(currentUrl).then(() => {
                        this.showNotification('Link copied to clipboard!', 'success');
                    });
                }
            }
        }

        /**
         * Schedule tour functionality
         */
        showScheduleTour(listingId) {
            // Simple alert for now - could be enhanced with modal
            alert('Schedule tour feature would open here for listing ' + listingId);
        }

        /**
         * Mortgage Calculator
         */
        setupMortgageCalculator() {
            const $calculator = $('.mortgage-calculator');
            if (!$calculator.length) return;

            const calculateMortgage = () => {
                const principal = parseFloat($('#loan_amount').val()) || 0;
                const rate = parseFloat($('#interest_rate').val()) || 0;
                const term = parseFloat($('#loan_term').val()) || 30;

                if (principal <= 0 || rate <= 0) {
                    $('#monthly_payment').text('$0');
                    $('#total_interest').text('$0');
                    return;
                }

                const monthlyRate = rate / 100 / 12;
                const numberOfPayments = term * 12;

                const monthlyPayment = principal * 
                    (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) /
                    (Math.pow(1 + monthlyRate, numberOfPayments) - 1);

                const totalPaid = monthlyPayment * numberOfPayments;
                const totalInterest = totalPaid - principal;

                $('#monthly_payment').text('$' + monthlyPayment.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                $('#total_interest').text('$' + totalInterest.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            };

            // Auto-calculate on input change
            $calculator.find('input, select').on('change input', calculateMortgage);
            
            // Manual calculate button
            $('#calculate_mortgage').on('click', (e) => {
                e.preventDefault();
                calculateMortgage();
            });

            // Initial calculation
            calculateMortgage();
        }

        /**
         * Contact Forms
         */
        setupContactForms() {
            $('.listing-contact-form').on('submit', (e) => {
                e.preventDefault();
                const $form = $(e.currentTarget);
                this.submitContactForm($form);
            });
        }

        submitContactForm($form) {
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.html();
            
            // Show loading state
            $submitBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Sending...');

            // Simulate form submission
            setTimeout(() => {
                $form[0].reset();
                this.showNotification('Message sent successfully!', 'success');
                $submitBtn.prop('disabled', false).html(originalText);
            }, 1000);
        }

        /**
         * Notification System
         */
        showNotification(message, type = 'info') {
            // Create notification container if it doesn't exist
            if (!$('.notifications').length) {
                $('body').append('<div class="notifications"></div>');
            }

            const $notification = $(`
                <div class="notification notification--${type}">
                    <div class="notification__content">
                        <i class="fas ${this.getNotificationIcon(type)}"></i>
                        <span>${message}</span>
                    </div>
                    <button class="notification__close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);

            $('.notifications').append($notification);

            // Auto-hide after 4 seconds
            setTimeout(() => {
                $notification.addClass('notification--fade-out');
                setTimeout(() => $notification.remove(), 300);
            }, 4000);

            // Manual close
            $notification.find('.notification__close').on('click', () => {
                $notification.addClass('notification--fade-out');
                setTimeout(() => $notification.remove(), 300);
            });
        }

        getNotificationIcon(type) {
            switch(type) {
                case 'success': return 'fa-check-circle';
                case 'error': return 'fa-exclamation-circle';
                case 'warning': return 'fa-exclamation-triangle';
                default: return 'fa-info-circle';
            }
        }

        /**
         * Event Binding
         */
        bindEvents() {
            // Smooth scroll to sections
            $('a[href^="#"]').on('click', (e) => {
                const target = $(e.currentTarget).attr('href');
                if ($(target).length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: $(target).offset().top - 100
                    }, 500);
                }
            });
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        if ($('.single-listing-main').length) {
            new SingleListingHandler();
        }
    });

})(jQuery);