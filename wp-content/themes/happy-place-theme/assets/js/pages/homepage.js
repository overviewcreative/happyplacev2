/**
 * Homepage JavaScript
 * Interactive functionality for the modern homepage
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';
    
    // Wait for DOM to be ready
    $(document).ready(function() {
        
        // Initialize homepage functionality
        initSmoothScrolling();
        initPropertyCardInteractions();
        initFormHandling();
        initScrollAnimations();
        initNewsletterForm();
        
    });
    
    /**
     * Smooth scrolling for anchor links
     */
    function initSmoothScrolling() {
        $('a[href^="#"]').on('click', function(e) {
            const target = $(this.getAttribute('href'));
            
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 80 // Account for fixed header
                }, 800, 'easeInOutCubic');
            }
        });
    }
    
    /**
     * Property card interactions
     */
    function initPropertyCardInteractions() {
        
        // Favorite button toggle
        $('.hph-property-card [data-property-id]').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const propertyId = $btn.data('property-id');
            const action = $btn.data('action');
            
            switch(action) {
                case 'toggle-favorite':
                    toggleFavorite($btn, propertyId);
                    break;
                    
                case 'schedule-tour':
                    scheduleTour(propertyId);
                    break;
                    
                case 'share':
                    shareProperty(propertyId);
                    break;
            }
        });
        
        // Property card hover effects
        $('.hph-property-card').hover(
            function() {
                $(this).addClass('hovered');
            },
            function() {
                $(this).removeClass('hovered');
            }
        );
    }
    
    /**
     * Toggle property favorite status
     */
    function toggleFavorite($btn, propertyId) {
        const $icon = $btn.find('i');
        const isFavorited = $icon.hasClass('fas');
        
        // Optimistic UI update
        if (isFavorited) {
            $icon.removeClass('fas').addClass('far');
            $btn.removeClass('is-favorite');
        } else {
            $icon.removeClass('far').addClass('fas');
            $btn.addClass('is-favorite');
        }
        
        // Send AJAX request
        $.ajax({
            url: hph_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hph_toggle_favorite',
                property_id: propertyId,
                nonce: hph_ajax.nonce
            },
            success: function(response) {
                if (!response.success) {
                    // Revert UI changes on failure
                    if (isFavorited) {
                        $icon.removeClass('far').addClass('fas');
                        $btn.addClass('is-favorite');
                    } else {
                        $icon.removeClass('fas').addClass('far');
                        $btn.removeClass('is-favorite');
                    }
                    showNotification('error', 'Failed to update favorite status');
                }
            },
            error: function() {
                // Revert UI changes on error
                if (isFavorited) {
                    $icon.removeClass('far').addClass('fas');
                    $btn.addClass('is-favorite');
                } else {
                    $icon.removeClass('fas').addClass('far');
                    $btn.removeClass('is-favorite');
                }
                showNotification('error', 'Something went wrong');
            }
        });
    }
    
    /**
     * Schedule property tour
     */
    function scheduleTour(propertyId) {
        // This would typically open a modal or redirect to a tour scheduling page
        showNotification('info', 'Tour scheduling feature coming soon!');
        
        // Example: Open a modal
        // $('#tour-modal').attr('data-property-id', propertyId).modal('show');
    }
    
    /**
     * Share property
     */
    function shareProperty(propertyId) {
        if (navigator.share) {
            // Use native sharing if available
            navigator.share({
                title: 'Check out this property',
                text: 'I found this amazing property!',
                url: window.location.origin + '/listing/' + propertyId + '/'
            
            });
        } else {
            // Fallback: copy to clipboard
            const url = window.location.origin + '/listing/' + propertyId + '/';
            copyToClipboard(url);
            showNotification('success', 'Property link copied to clipboard!');
        }
    }
    
    /**
     * Copy text to clipboard
     */
    function copyToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    }
    
    /**
     * Form handling
     */
    function initFormHandling() {
        
        // Hero search form
        $('.hph-hero-home form').on('submit', function(e) {
            const $form = $(this);
            const location = $form.find('[name="location"]').val();
            
            // Add loading state
            const $submitBtn = $form.find('[type="submit"]');
            const originalText = $submitBtn.text();
            $submitBtn.text('Searching...').prop('disabled', true);
            
            // Allow form to submit normally, but with visual feedback
            setTimeout(() => {
                $submitBtn.text(originalText).prop('disabled', false);
            }, 1000);
        });
        
        // Contact forms
        $('.hph-contact-form').on('submit', function(e) {
            e.preventDefault();
            handleContactForm($(this));
        });
    }
    
    /**
     * Handle contact form submission
     */
    function handleContactForm($form) {
        const formData = $form.serialize();
        const $submitBtn = $form.find('[type="submit"]');
        const originalText = $submitBtn.text();
        
        // Show loading state
        $submitBtn.text(hph_ajax.strings.loading).prop('disabled', true);
        
        $.ajax({
            url: hph_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=hph_contact_form&nonce=' + hph_ajax.nonce,
            success: function(response) {
                if (response.success) {
                    showNotification('success', hph_ajax.strings.success);
                    $form[0].reset();
                } else {
                    showNotification('error', response.data || hph_ajax.strings.error);
                }
            },
            error: function() {
                showNotification('error', hph_ajax.strings.error);
            },
            complete: function() {
                $submitBtn.text(originalText).prop('disabled', false);
            }
        });
    }
    
    /**
     * Newsletter form handling
     */
    function initNewsletterForm() {
        $('#newsletter-form').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('[type="submit"]');
            const originalText = $submitBtn.html();
            
            // Show loading state
            $submitBtn.html('<i class="fas fa-spinner fa-spin hph-mr-sm"></i>Subscribing...').prop('disabled', true);
            
            // Simulate API call (replace with actual newsletter service)
            setTimeout(() => {
                showNotification('success', 'Thank you for subscribing! You\'ll receive your first newsletter soon.');
                $form[0].reset();
                $submitBtn.html(originalText).prop('disabled', false);
            }, 2000);
        });
    }
    
    /**
     * Scroll-based animations
     */
    function initScrollAnimations() {
        
        // Intersection Observer for fade-in animations
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });
            
            // Observe elements for animation
            $('.hph-section h2, .hph-section h3, .hph-property-card').each(function() {
                observer.observe(this);
            });
        }
        
        // Parallax effect for hero background (desktop only)
        if (window.innerWidth > 1024) {
            $(window).on('scroll', function() {
                const scrolled = $(this).scrollTop();
                const heroHeight = $('.hph-hero-home').outerHeight();
                
                if (scrolled < heroHeight) {
                    $('.hph-hero-home').css('transform', 'translateY(' + (scrolled * 0.5) + 'px)');
                }
            });
        }
    }
    
    /**
     * Show notification to user
     */
    function showNotification(type, message) {
        // Create notification element
        const notification = $(`
            <div class="hph-notification hph-notification-${type}">
                <div class="hph-notification-content">
                    <i class="fas fa-${getNotificationIcon(type)} hph-mr-sm"></i>
                    <span>${message}</span>
                    <button class="hph-notification-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `);
        
        // Add to page
        $('body').append(notification);
        
        // Show notification
        setTimeout(() => notification.addClass('show'), 100);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            notification.removeClass('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
        
        // Manual close
        notification.find('.hph-notification-close').on('click', function() {
            notification.removeClass('show');
            setTimeout(() => notification.remove(), 300);
        });
    }
    
    /**
     * Get notification icon based on type
     */
    function getNotificationIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-triangle',
            'warning': 'exclamation-circle',
            'info': 'info-circle'
        };
        
        return icons[type] || 'info-circle';
    }
    
    // Add CSS for notifications
    $('<style>').text(`
        .hph-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            padding: 16px;
            border-radius: 8px;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hph-notification.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .hph-notification-success {
            border-left: 4px solid var(--hph-success, #10b981);
        }
        
        .hph-notification-error {
            border-left: 4px solid var(--hph-danger, #ef4444);
        }
        
        .hph-notification-warning {
            border-left: 4px solid var(--hph-warning, #f59e0b);
        }
        
        .hph-notification-info {
            border-left: 4px solid var(--hph-info);
        }
        
        .hph-notification-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        
        .hph-notification-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #6b7280;
            border-radius: 4px;
        }
        
        .hph-notification-close:hover {
            background: #f3f4f6;
        }
    `).appendTo('head');
    
})(jQuery);
