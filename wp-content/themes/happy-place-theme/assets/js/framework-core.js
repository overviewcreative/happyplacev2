/**
 * HPH Framework Core JavaScript
 * 
 * Core functionality for the HPH Framework components
 * Handles initialization and common utilities
 *
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Framework namespace
    window.HPH = window.HPH || {};
    
    /**
     * Framework initialization
     */
    HPH.init = function() {
        console.log('HPH Framework initialized');
        
        // Initialize all components
        HPH.initComponents();
        HPH.initUtilities();
        HPH.initAccessibility();
    };
    
    /**
     * Initialize framework components
     */
    HPH.initComponents = function() {
        // Initialize cards
        HPH.initCards();
        
        // Initialize modals
        HPH.initModals();
        
        // Initialize alerts
        HPH.initAlerts();
        
        // Initialize forms
        HPH.initForms();
    };
    
    /**
     * Initialize card components
     */
    HPH.initCards = function() {
        $('.hph-card').each(function() {
            var $card = $(this);
            
            // Add hover effects
            $card.on('mouseenter', function() {
                $(this).addClass('hph-card-hover');
            }).on('mouseleave', function() {
                $(this).removeClass('hph-card-hover');
            });
            
            // Handle card actions
            $card.find('.hph-card-action').on('click', function(e) {
                e.preventDefault();
                var action = $(this).data('action');
                HPH.handleCardAction(action, $card);
            });
        });
    };
    
    /**
     * Initialize modal components
     */
    HPH.initModals = function() {
        // Modal triggers
        $('[data-modal-target]').on('click', function(e) {
            e.preventDefault();
            var targetModal = $(this).data('modal-target');
            HPH.openModal(targetModal);
        });
        
        // Modal close buttons
        $('.hph-modal-close, .hph-modal-backdrop').on('click', function(e) {
            e.preventDefault();
            HPH.closeModal();
        });
        
        // ESC key to close modal
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                HPH.closeModal();
            }
        });
    };
    
    /**
     * Initialize alert components
     */
    HPH.initAlerts = function() {
        $('.hph-alert-dismissible .hph-alert-close').on('click', function() {
            $(this).closest('.hph-alert').fadeOut();
        });
        
        // Auto-dismiss alerts
        $('.hph-alert-auto-dismiss').each(function() {
            var $alert = $(this);
            var delay = $alert.data('dismiss-delay') || 5000;
            
            setTimeout(function() {
                $alert.fadeOut();
            }, delay);
        });
    };
    
    /**
     * Initialize form enhancements
     */
    HPH.initForms = function() {
        // Floating labels
        $('.hph-form-group').each(function() {
            var $group = $(this);
            var $input = $group.find('.hph-input, .hph-textarea, .hph-select');
            var $label = $group.find('.hph-label');
            
            if ($input.length && $label.length) {
                // Check if input has value on load
                if ($input.val()) {
                    $group.addClass('hph-has-value');
                }
                
                // Handle focus/blur events
                $input.on('focus', function() {
                    $group.addClass('hph-focused');
                });
                
                $input.on('blur', function() {
                    $group.removeClass('hph-focused');
                    if ($(this).val()) {
                        $group.addClass('hph-has-value');
                    } else {
                        $group.removeClass('hph-has-value');
                    }
                });
            }
        });
        
        // Form validation
        $('form[data-validate]').on('submit', function(e) {
            if (!HPH.validateForm($(this))) {
                e.preventDefault();
            }
        });
    };
    
    /**
     * Initialize utility functions
     */
    HPH.initUtilities = function() {
        // Smooth scrolling for anchor links
        $('a[href*="#"]:not([href="#"])').on('click', function(e) {
            var target = $(this.hash);
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 800);
            }
        });
        
        // Lazy loading for images
        if ('IntersectionObserver' in window) {
            HPH.initLazyLoading();
        }
    };
    
    /**
     * Initialize accessibility features
     */
    HPH.initAccessibility = function() {
        // Skip links
        $('.hph-skip-link').on('click', function(e) {
            var target = $($(this).attr('href'));
            if (target.length) {
                target.focus();
            }
        });
        
        // Focus management for modals
        $(document).on('hph-modal-opened', function(e, modalId) {
            var $modal = $('#' + modalId);
            var $focusableElements = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if ($focusableElements.length) {
                $focusableElements.first().focus();
            }
        });
    };
    
    /**
     * Open modal
     */
    HPH.openModal = function(modalId) {
        var $modal = $('#' + modalId);
        if ($modal.length) {
            $modal.addClass('hph-modal-open');
            $('body').addClass('hph-modal-active');
            $(document).trigger('hph-modal-opened', [modalId]);
        }
    };
    
    /**
     * Close modal
     */
    HPH.closeModal = function() {
        $('.hph-modal').removeClass('hph-modal-open');
        $('body').removeClass('hph-modal-active');
        $(document).trigger('hph-modal-closed');
    };
    
    /**
     * Handle card actions
     */
    HPH.handleCardAction = function(action, $card) {
        switch (action) {
            case 'favorite':
                $card.toggleClass('hph-card-favorited');
                break;
            case 'share':
                HPH.shareCard($card);
                break;
            case 'expand':
                $card.toggleClass('hph-card-expanded');
                break;
            default:
                console.log('Unknown card action:', action);
        }
    };
    
    /**
     * Share card functionality
     */
    HPH.shareCard = function($card) {
        if (navigator.share) {
            var title = $card.find('.hph-card-title').text();
            var url = $card.find('a').attr('href') || window.location.href;
            
            navigator.share({
                title: title,
                url: url
            });
        } else {
            // Fallback: copy to clipboard
            var url = $card.find('a').attr('href') || window.location.href;
            HPH.copyToClipboard(url);
            HPH.showAlert('Link copied to clipboard!', 'success');
        }
    };
    
    /**
     * Copy text to clipboard
     */
    HPH.copyToClipboard = function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }
    };
    
    /**
     * Show alert message
     */
    HPH.showAlert = function(message, type = 'info', duration = 3000) {
        var alertHtml = `
            <div class="hph-alert hph-alert-${type} hph-alert-dismissible hph-alert-auto-dismiss" data-dismiss-delay="${duration}">
                <span class="hph-alert-message">${message}</span>
                <button type="button" class="hph-alert-close" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        var $alert = $(alertHtml);
        $('body').append($alert);
        
        // Auto dismiss
        setTimeout(function() {
            $alert.fadeOut(function() {
                $alert.remove();
            });
        }, duration);
    };
    
    /**
     * Form validation
     */
    HPH.validateForm = function($form) {
        var isValid = true;
        
        // Clear previous errors
        $form.find('.hph-form-error').remove();
        $form.find('.hph-has-error').removeClass('hph-has-error');
        
        // Validate required fields
        $form.find('[required]').each(function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (!value) {
                HPH.showFieldError($field, 'This field is required');
                isValid = false;
            }
        });
        
        // Validate email fields
        $form.find('input[type="email"]').each(function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (value && !HPH.isValidEmail(value)) {
                HPH.showFieldError($field, 'Please enter a valid email address');
                isValid = false;
            }
        });
        
        return isValid;
    };
    
    /**
     * Show field error
     */
    HPH.showFieldError = function($field, message) {
        var $group = $field.closest('.hph-form-group');
        $group.addClass('hph-has-error');
        $group.append(`<div class="hph-form-error">${message}</div>`);
    };
    
    /**
     * Validate email address
     */
    HPH.isValidEmail = function(email) {
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    };
    
    /**
     * Initialize lazy loading
     */
    HPH.initLazyLoading = function() {
        var imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('hph-lazy');
                    img.classList.add('hph-loaded');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(function(img) {
            imageObserver.observe(img);
        });
    };
    
    /**
     * Debounce function
     */
    HPH.debounce = function(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    };
    
    /**
     * Throttle function
     */
    HPH.throttle = function(func, limit) {
        var inThrottle;
        return function() {
            var args = arguments;
            var context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(function() { inThrottle = false; }, limit);
            }
        };
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        HPH.init();
    });
    
})(jQuery);
