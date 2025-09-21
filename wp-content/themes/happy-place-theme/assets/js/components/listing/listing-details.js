/**
 * HPH Listing Details Component JavaScript
 * 
 * Interactive functionality for the property overview section including
 * read more/less, sharing, agent actions, and AJAX integrations
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';

    // Ensure global context exists
    if (typeof window.hphContext === 'undefined') {
        window.hphContext = {
            ajaxUrl: window.ajaxurl || '/wp-admin/admin-ajax.php',
            nonce: window.hph_nonce || '',
            propertyId: null
        };
    }

    // Global HPH Listing Details object
    window.HPHListingDetails = {
        
        /**
         * Initialize the component
         */
        init: function() {
            this.initReadMore();
            this.initShareFunctionality();
            this.initAgentActions();
            this.initQuickActions();
            this.initStickyElements();
            this.bindEvents();
        },

        /**
         * Initialize read more/less functionality
         */
        initReadMore: function() {
            const description = document.querySelector('.hph-description-content');
            const readMoreBtn = document.querySelector('.hph-read-more-btn');
            
            if (description && description.scrollHeight > 300) {
                readMoreBtn.style.display = 'flex';
                
                readMoreBtn.addEventListener('click', function() {
                    description.classList.toggle('expanded');
                    this.classList.toggle('expanded');
                    
                    const readMore = this.querySelector('.hph-read-more-text');
                    const readLess = this.querySelector('.hph-read-less-text');
                    
                    if (description.classList.contains('expanded')) {
                        readMore.style.display = 'none';
                        readLess.style.display = 'inline';
                    } else {
                        readMore.style.display = 'inline';
                        readLess.style.display = 'none';
                    }
                });
            }
        },

        /**
         * Initialize share functionality
         */
        initShareFunctionality: function() {
            // Share dropdown hover
            $('.hph-share-buttons').on('mouseenter', function() {
                $(this).find('.hph-share-dropdown').stop().fadeIn(200);
            }).on('mouseleave', function() {
                $(this).find('.hph-share-dropdown').stop().fadeOut(200);
            });

            // Copy link functionality
            $('.hph-share-option').on('click', function(e) {
                if ($(this).find('i').hasClass('fa-link')) {
                    e.preventDefault();
                    const url = $(this).closest('.hph-share-buttons').find('.hph-share-btn').data('url') || window.location.href;
                    HPHListingDetails.copyToClipboard(url);
                }
            });
        },

        /**
         * Initialize agent actions
         */
        initAgentActions: function() {
            // Phone number formatting and tracking
            $('.hph-agent-btn[href^="tel:"]').on('click', function() {
                HPHListingDetails.trackEvent('agent_call', {
                    phone: $(this).attr('href').replace('tel:', '')
                });
            });

            // Email tracking
            $('.hph-agent-btn[href^="mailto:"]').on('click', function() {
                HPHListingDetails.trackEvent('agent_email', {
                    email: $(this).attr('href').replace('mailto:', '')
                });
            });

            // Schedule showing modal
            $(document).on('click', '[onclick*="scheduleShowing"]', function(e) {
                e.preventDefault();
                HPHListingDetails.scheduleShowing();
            });
        },

        /**
         * Initialize quick actions
         */
        initQuickActions: function() {
            // Quick action buttons
            $('.hph-action-btn').on('click', function() {
                const action = this.onclick?.toString().match(/(\w+)\(\)/)?.[1];
                if (action) {
                    HPHListingDetails[action]?.();
                }
            });

            // CTA buttons
            $('.hph-cta-btn').on('click', function() {
                const btnText = $(this).text().trim().toLowerCase();
                if (btnText.includes('contact')) {
                    HPHListingDetails.contactAgent();
                } else if (btnText.includes('tour')) {
                    HPHListingDetails.schedulePropertyTour();
                }
            });
        },

        /**
         * Initialize sticky elements
         */
        initStickyElements: function() {
            // Sticky sidebar tracking
            if ($('.hph-overview-sidebar').length) {
                $(window).on('scroll', function() {
                    const sidebar = $('.hph-overview-sidebar');
                    const sidebarTop = sidebar.offset().top;
                    const scrollTop = $(window).scrollTop();
                    
                    if (scrollTop > sidebarTop - 100) {
                        sidebar.addClass('hph-sticky-active');
                    } else {
                        sidebar.removeClass('hph-sticky-active');
                    }
                });
            }
        },

        /**
         * Bind additional events
         */
        bindEvents: function() {
            // Print functionality
            $('.hph-print-btn').on('click', function() {
                window.print();
                HPHListingDetails.trackEvent('listing_print');
            });

            // Share button tracking
            $('.hph-share-option').on('click', function() {
                const platform = $(this).find('i').attr('class').match(/fa-(\w+)/)?.[1] || 'unknown';
                HPHListingDetails.trackEvent('listing_share', { platform });
            });

            // Detail item interactions
            $('.hph-detail-item').on('click', function() {
                $(this).addClass('hph-clicked');
                setTimeout(() => $(this).removeClass('hph-clicked'), 200);
            });
        },

        /**
         * Share property function
         */
        shareProperty: function(url) {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: url || window.location.href
                });
            } else {
                // Fallback to copy URL
                this.copyToClipboard(url || window.location.href);
            }
        },

        /**
         * Copy to clipboard utility
         */
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showNotification('Link copied to clipboard!', 'success');
                }).catch(() => {
                    this.fallbackCopyToClipboard(text);
                });
            } else {
                this.fallbackCopyToClipboard(text);
            }
        },

        /**
         * Fallback copy to clipboard
         */
        fallbackCopyToClipboard: function(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                this.showNotification('Link copied to clipboard!', 'success');
            } catch (err) {
                this.showNotification('Could not copy link', 'error');
            }
            
            document.body.removeChild(textArea);
        },

        /**
         * Schedule showing modal/form
         */
        scheduleShowing: function() {
            // Check for existing modal system or create simple one
            if (typeof window.HPHModal !== 'undefined') {
                window.HPHModal.open('schedule-showing');
            } else {
                // Simple fallback - you might want to integrate with your modal system
                const phone = $('.hph-agent-btn[href^="tel:"]').first().attr('href')?.replace('tel:', '');
                if (phone) {
                    const message = `I'd like to schedule a showing for this property: ${window.location.href}`;
                    window.open(`sms:${phone}?body=${encodeURIComponent(message)}`, '_blank');
                } else {
                    this.showNotification('Please contact the agent directly to schedule a showing', 'info');
                }
            }
            
            this.trackEvent('schedule_showing_click');
        },

        /**
         * Calculate mortgage
         */
        calculateMortgage: function() {
            // Integration point for mortgage calculator
            if (typeof window.HPHMortgageCalculator !== 'undefined') {
                window.HPHMortgageCalculator.open();
            } else {
                // Fallback - redirect to calculator page or external tool
                const calculatorUrl = '/mortgage-calculator/';
                window.open(calculatorUrl, '_blank');
            }
            
            this.trackEvent('mortgage_calculator_click');
        },

        /**
         * Get pre-approved
         */
        getPreApproved: function() {
            // Integration point for pre-approval form
            if (typeof window.HPHPreApproval !== 'undefined') {
                window.HPHPreApproval.open();
            } else {
                // Fallback - redirect to pre-approval page
                const preApprovalUrl = '/get-pre-approved/';
                window.location.href = preApprovalUrl;
            }
            
            this.trackEvent('pre_approval_click');
        },

        /**
         * Request property information
         */
        requestInfo: function() {
            // Integration point for info request form
            if (typeof window.HPHContactForm !== 'undefined') {
                window.HPHContactForm.open('property-info');
            } else {
                // Fallback - mailto with property info
                const agentEmail = $('.hph-agent-btn[href^="mailto:"]').first().attr('href')?.replace('mailto:', '');
                if (agentEmail) {
                    const subject = 'Property Information Request';
                    const body = `I would like more information about this property: ${window.location.href}`;
                    window.location.href = `mailto:${agentEmail}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
                } else {
                    this.showNotification('Please contact the agent directly for more information', 'info');
                }
            }
            
            this.trackEvent('request_info_click');
        },

        /**
         * Save property
         */
        saveProperty: function() {
            // AJAX call to save property
            if (typeof window.hphContext !== 'undefined' && window.hphContext.ajaxUrl) {
                $.ajax({
                    url: window.hphContext.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'hph_save_property',
                        property_id: window.hphContext.propertyId || this.getPropertyId(),
                        nonce: window.hphContext.nonce
                    },
                    success: (response) => {
                        if (response.success) {
                            this.showNotification('Property saved to your favorites!', 'success');
                            $('.hph-action-btn:contains("Save")').addClass('hph-saved').html('<i class="fas fa-heart"></i><span>Saved</span>');
                        } else {
                            this.showNotification('Could not save property', 'error');
                        }
                    },
                    error: () => {
                        this.showNotification('Could not save property', 'error');
                    }
                });
            } else {
                // Fallback - local storage
                const propertyId = this.getPropertyId();
                const savedProperties = JSON.parse(localStorage.getItem('hph_saved_properties') || '[]');
                
                if (!savedProperties.includes(propertyId)) {
                    savedProperties.push(propertyId);
                    localStorage.setItem('hph_saved_properties', JSON.stringify(savedProperties));
                    this.showNotification('Property saved to your favorites!', 'success');
                } else {
                    this.showNotification('Property already saved', 'info');
                }
            }
            
            this.trackEvent('save_property');
        },

        /**
         * Contact agent
         */
        contactAgent: function() {
            const agentPhone = $('.hph-agent-btn[href^="tel:"]').first();
            if (agentPhone.length) {
                agentPhone[0].click();
            } else {
                this.requestInfo();
            }
        },

        /**
         * Schedule property tour
         */
        schedulePropertyTour: function() {
            this.scheduleShowing();
        },

        /**
         * Get property ID from various sources
         */
        getPropertyId: function() {
            // Try multiple methods to get property ID
            if (window.hphContext?.propertyId) {
                return window.hphContext.propertyId;
            }
            
            // Extract from URL
            const urlMatch = window.location.pathname.match(/\/property\/(\d+)/);
            if (urlMatch) {
                return urlMatch[1];
            }
            
            // Extract from body class
            const bodyClasses = document.body.className.match(/property-id-(\d+)/);
            if (bodyClasses) {
                return bodyClasses[1];
            }
            
            // Fallback to current timestamp
            return Date.now().toString();
        },

        /**
         * Show notification
         */
        showNotification: function(message, type = 'info') {
            // Check for existing notification system
            if (typeof window.HPHNotifications !== 'undefined') {
                window.HPHNotifications.show(message, type);
                return;
            }
            
            // Simple fallback notification
            const notification = $(`
                <div class="hph-notification hph-notification-${type}">
                    <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                    <span>${message}</span>
                    <button class="hph-notification-close">×</button>
                </div>
            `);
            
            $('body').append(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.fadeOut(300, () => notification.remove());
            }, 5000);
            
            // Manual close
            notification.find('.hph-notification-close').on('click', () => {
                notification.fadeOut(300, () => notification.remove());
            });
        },

        /**
         * Get notification icon by type
         */
        getNotificationIcon: function(type) {
            const icons = {
                success: 'check-circle',
                error: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };
            return icons[type] || icons.info;
        },

        /**
         * Track events for analytics
         */
        trackEvent: function(event, data = {}) {
            // Google Analytics 4
            if (typeof gtag !== 'undefined') {
                gtag('event', event, {
                    event_category: 'listing_details',
                    ...data
                });
            }
            
            // Google Analytics Universal
            if (typeof ga !== 'undefined') {
                ga('send', 'event', 'listing_details', event, data.label || '', data.value || 0);
            }
            
            // Facebook Pixel
            if (typeof fbq !== 'undefined') {
                fbq('track', 'CustomEvent', {
                    event_name: event,
                    content_category: 'listing_details',
                    ...data
                });
            }
            
            // Console log for debugging
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        HPHListingDetails.init();
    });

    // Expose global functions for backward compatibility
    window.shareProperty = function(url) {
        HPHListingDetails.shareProperty(url);
    };

    window.copyToClipboard = function(text) {
        HPHListingDetails.copyToClipboard(text);
    };

    window.scheduleShowing = function() {
        HPHListingDetails.scheduleShowing();
    };

    window.calculateMortgage = function() {
        HPHListingDetails.calculateMortgage();
    };

    window.getPreApproved = function() {
        HPHListingDetails.getPreApproved();
    };

    window.requestInfo = function() {
        HPHListingDetails.requestInfo();
    };

    window.saveProperty = function() {
        HPHListingDetails.saveProperty();
    };

    window.contactAgent = function() {
        HPHListingDetails.contactAgent();
    };

    window.schedulePropertyTour = function() {
        HPHListingDetails.schedulePropertyTour();
    };

})(jQuery);
