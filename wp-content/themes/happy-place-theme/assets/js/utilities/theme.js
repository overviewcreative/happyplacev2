/**
 * Happy Place Theme JavaScript
 * Main theme functionality and WordPress integration
 * Theme-specific features and customizations
 *
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Theme namespace
    window.HappyPlaceTheme = {
        
        /**
         * Initialize theme
         */
        init: function() {
            this.initThemeFeatures();
            this.initWordPressIntegration();
            this.initCustomizations();
            this.initContactForms();
            this.initPropertyFeatures();
        },
        
        /**
         * Initialize theme features
         */
        initThemeFeatures: function() {
            // Smooth scroll for anchor links
            this.initSmoothScroll();
            
            // Back to top button
            this.initBackToTop();
            
            // Loading animations
            this.initLoadingAnimations();
            
            // Cookie consent
            this.initCookieConsent();
            
            // Theme switcher removed (incomplete implementation)
            // TODO: Re-implement comprehensive dark mode in future version
        },
        
        /**
         * Initialize WordPress integration
         */
        initWordPressIntegration: function() {
            // WordPress comment forms
            this.initCommentForms();
            
            // Search functionality
            this.initSearchEnhancements();
            
            // Widget enhancements
            this.initWidgetEnhancements();
            
            // Customizer preview
            this.initCustomizerPreview();
        },
        
        /**
         * Initialize customizations
         */
        initCustomizations: function() {
            // Custom dropdown selects
            this.initCustomSelects();
            
            // Tooltip functionality
            this.initTooltips();
            
            // Copy to clipboard
            this.initCopyToClipboard();
            
            // Print functionality
            this.initPrintFeatures();
        },
        
        /**
         * Initialize smooth scrolling
         */
        initSmoothScroll: function() {
            $('a[href*="#"]:not([href="#"])').on('click', function(e) {
                var target = $(this.hash);
                var offset = $('.site-header').outerHeight() || 0;
                
                if (target.length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - offset - 20
                    }, 800, 'easeInOutCubic');
                }
            });
        },
        
        /**
         * Initialize back to top button
         */
        initBackToTop: function() {
            // Create back to top button if it doesn't exist
            if (!$('.back-to-top').length) {
                $('body').append(`
                    <button type="button" class="back-to-top hph-btn hph-btn-primary hph-btn-icon" aria-label="Back to top">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                `);
            }
            
            var $backToTop = $('.back-to-top');
            
            // Show/hide based on scroll
            $(window).on('scroll', HPH.throttle(function() {
                var scrollTop = $(window).scrollTop();
                
                if (scrollTop > 500) {
                    $backToTop.addClass('visible');
                } else {
                    $backToTop.removeClass('visible');
                }
            }, 100));
            
            // Click handler
            $backToTop.on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: 0
                }, 800, 'easeInOutCubic');
            });
        },
        
        /**
         * Initialize loading animations
         */
        initLoadingAnimations: function() {
            // Fade in content on load
            $('.fade-in-on-load').each(function() {
                $(this).css('opacity', '0').animate({ opacity: 1 }, 600);
            });
            
            // Intersection Observer for scroll animations
            if ('IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('in-view');
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });
                
                document.querySelectorAll('.animate-on-scroll').forEach(function(el) {
                    observer.observe(el);
                });
            }
        },
        
        /**
         * Initialize cookie consent
         */
        initCookieConsent: function() {
            // Check if cookie consent is needed
            if (localStorage.getItem('cookieConsent') !== 'accepted') {
                this.showCookieConsent();
            }
        },
        
        /**
         * Show cookie consent banner
         */
        showCookieConsent: function() {
            var consentHtml = `
                <div id="hph-cookie-consent" class="hph-cookie-consent">
                    <div class="hph-cookie-consent-content">
                        <p>We use cookies to enhance your browsing experience and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.</p>
                        <div class="hph-cookie-consent-actions">
                            <button type="button" class="hph-accept-cookies hph-btn hph-btn-primary hph-btn-sm">Accept All</button>
                            <button type="button" class="hph-decline-cookies hph-btn hph-btn-outline hph-btn-sm">Decline</button>
                            <a href="/legal/#privacy" class="hph-text-sm hph-underline">Privacy Policy</a>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(consentHtml);
            
            // Add visible class with slight delay for animation
            setTimeout(function() {
                $('#hph-cookie-consent').addClass('visible');
            }, 100);
            
            $('.hph-accept-cookies').on('click', function() {
                localStorage.setItem('cookieConsent', 'accepted');
                $('#hph-cookie-consent').removeClass('visible');
                setTimeout(function() {
                    $('#hph-cookie-consent').remove();
                }, 300);
            });
            
            $('.hph-decline-cookies').on('click', function() {
                localStorage.setItem('cookieConsent', 'declined');
                $('#hph-cookie-consent').removeClass('visible');
                setTimeout(function() {
                    $('#hph-cookie-consent').remove();
                }, 300);
            });
        },
        
        /**
         * Initialize contact forms
         */
        initContactForms: function() {
            $('.contact-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $submitBtn = $form.find('[type="submit"]');
                var originalText = $submitBtn.text();
                
                // Show loading state
                $submitBtn.prop('disabled', true).text('Sending...');
                
                // Get form data
                var formData = new FormData(this);
                formData.append('action', 'hph_contact_form');
                formData.append('nonce', happyPlace.nonce);
                
                // Submit form
                $.ajax({
                    url: happyPlace.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            HPH.showAlert('Message sent successfully!', 'success');
                            $form[0].reset();
                        } else {
                            HPH.showAlert(response.data.message || 'Failed to send message', 'error');
                        }
                    },
                    error: function() {
                        HPH.showAlert('An error occurred while sending your message', 'error');
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });
        },
        
        /**
         * Initialize property features
         */
        initPropertyFeatures: function() {
            // Property comparison
            this.initPropertyComparison();
            
            // Favorite properties
            this.initFavoriteProperties();
            
            // Property sharing
            this.initPropertySharing();
            
            // Property alerts
            this.initPropertyAlerts();
        },
        
        /**
         * Initialize property comparison
         */
        initPropertyComparison: function() {
            $('.compare-property').on('click', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var propertyId = $button.data('property-id');
                var isComparing = $button.hasClass('comparing');
                
                if (isComparing) {
                    HappyPlaceTheme.removeFromComparison(propertyId);
                    $button.removeClass('comparing').find('.btn-text').text('Compare');
                } else {
                    HappyPlaceTheme.addToComparison(propertyId);
                    $button.addClass('comparing').find('.btn-text').text('Remove from Compare');
                }
                
                HappyPlaceTheme.updateComparisonUI();
            });
            
            // Initialize comparison from localStorage
            var comparison = JSON.parse(localStorage.getItem('propertyComparison') || '[]');
            comparison.forEach(function(propertyId) {
                $(`.compare-property[data-property-id="${propertyId}"]`).addClass('comparing').find('.btn-text').text('Remove from Compare');
            });
            
            if (comparison.length > 0) {
                this.updateComparisonUI();
            }
        },
        
        /**
         * Add property to comparison
         */
        addToComparison: function(propertyId) {
            var comparison = JSON.parse(localStorage.getItem('propertyComparison') || '[]');
            
            if (comparison.length >= 3) {
                HPH.showAlert('You can compare up to 3 properties at a time', 'warning');
                return false;
            }
            
            if (comparison.indexOf(propertyId) === -1) {
                comparison.push(propertyId);
                localStorage.setItem('propertyComparison', JSON.stringify(comparison));
            }
            
            return true;
        },
        
        /**
         * Remove property from comparison
         */
        removeFromComparison: function(propertyId) {
            var comparison = JSON.parse(localStorage.getItem('propertyComparison') || '[]');
            var index = comparison.indexOf(propertyId);
            
            if (index > -1) {
                comparison.splice(index, 1);
                localStorage.setItem('propertyComparison', JSON.stringify(comparison));
            }
        },
        
        /**
         * Update comparison UI
         */
        updateComparisonUI: function() {
            var comparison = JSON.parse(localStorage.getItem('propertyComparison') || '[]');
            var $comparisonBar = $('#comparison-bar');
            
            if (comparison.length > 0) {
                if (!$comparisonBar.length) {
                    var comparisonHtml = `
                        <div id="comparison-bar" class="comparison-bar">
                            <div class="comparison-content">
                                <span class="comparison-count">${comparison.length} properties selected</span>
                                <div class="comparison-actions">
                                    <button type="button" class="compare-now hph-btn hph-btn-primary hph-btn-sm">Compare Now</button>
                                    <button type="button" class="clear-comparison hph-btn hph-btn-outline hph-btn-sm">Clear All</button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('body').append(comparisonHtml);
                    
                    // Bind events
                    $('.compare-now').on('click', function() {
                        window.location.href = '/compare-properties?ids=' + comparison.join(',');
                    });
                    
                    $('.clear-comparison').on('click', function() {
                        localStorage.removeItem('propertyComparison');
                        $('.compare-property').removeClass('comparing').find('.btn-text').text('Compare');
                        $('#comparison-bar').remove();
                    });
                } else {
                    $comparisonBar.find('.comparison-count').text(comparison.length + ' properties selected');
                }
                
                $comparisonBar.addClass('visible');
            } else {
                $comparisonBar.remove();
            }
        },
        
        /**
         * Initialize favorite properties
         */
        initFavoriteProperties: function() {
            $('.favorite-property').on('click', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var propertyId = $button.data('property-id');
                var isFavorited = $button.hasClass('favorited');
                
                // Toggle favorite status
                $.ajax({
                    url: happyPlace.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'hph_toggle_favorite',
                        nonce: happyPlace.nonce,
                        property_id: propertyId,
                        is_favorited: isFavorited
                    },
                    success: function(response) {
                        if (response.success) {
                            $button.toggleClass('favorited');
                            var icon = $button.find('i');
                            
                            if ($button.hasClass('favorited')) {
                                icon.removeClass('far').addClass('fas');
                                HPH.showAlert('Added to favorites', 'success');
                            } else {
                                icon.removeClass('fas').addClass('far');
                                HPH.showAlert('Removed from favorites', 'info');
                            }
                        }
                    }
                });
            });
        },
        
        /**
         * Initialize property sharing
         */
        initPropertySharing: function() {
            $('.share-property').on('click', function(e) {
                e.preventDefault();
                
                var propertyTitle = $(this).data('property-title') || document.title;
                var propertyUrl = $(this).data('property-url') || window.location.href;
                
                if (navigator.share) {
                    navigator.share({
                        title: propertyTitle,
                        url: propertyUrl
                    });
                } else {
                    HappyPlaceTheme.showShareModal(propertyTitle, propertyUrl);
                }
            });
        },
        
        /**
         * Show share modal
         */
        showShareModal: function(title, url) {
            var encodedUrl = encodeURIComponent(url);
            var encodedTitle = encodeURIComponent(title);
            
            var modalHtml = `
                <div id="share-modal" class="hph-modal">
                    <div class="hph-modal-content">
                        <div class="hph-modal-header">
                            <h3>Share Property</h3>
                            <button type="button" class="hph-modal-close">&times;</button>
                        </div>
                        <div class="hph-modal-body">
                            <div class="share-options hph-grid hph-grid-cols-2 hph-gap-4">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}" target="_blank" class="share-option facebook">
                                    <i class="fab fa-facebook-f"></i> Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}" target="_blank" class="share-option twitter">
                                    <i class="fab fa-twitter"></i> Twitter
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}" target="_blank" class="share-option linkedin">
                                    <i class="fab fa-linkedin-in"></i> LinkedIn
                                </a>
                                <button type="button" class="share-option copy-link" data-url="${url}">
                                    <i class="fas fa-link"></i> Copy Link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            HPH.openModal('share-modal');
            
            // Copy link functionality
            $('.copy-link').on('click', function() {
                var url = $(this).data('url');
                HPH.copyToClipboard(url);
                HPH.showAlert('Link copied to clipboard!', 'success');
                HPH.closeModal();
            });
            
            // Clean up when modal is closed
            $(document).on('hph-modal-closed', function() {
                $('#share-modal').remove();
            });
        },
        
        /**
         * Initialize property alerts
         */
        initPropertyAlerts: function() {
            $('.save-search-alert').on('click', function(e) {
                e.preventDefault();
                HappyPlaceTheme.showSaveSearchModal();
            });
        },
        
        /**
         * Show save search modal
         */
        showSaveSearchModal: function() {
            var modalHtml = `
                <div id="save-search-modal" class="hph-modal">
                    <div class="hph-modal-content">
                        <div class="hph-modal-header">
                            <h3>Save Your Search</h3>
                            <button type="button" class="hph-modal-close">&times;</button>
                        </div>
                        <div class="hph-modal-body">
                            <p>Get notified when new properties match your search criteria.</p>
                            <form class="save-search-form hph-space-y-4">
                                <div class="hph-form-group">
                                    <label class="hph-label">Alert Name</label>
                                    <input type="text" class="hph-input" name="alert_name" required>
                                </div>
                                <div class="hph-form-group">
                                    <label class="hph-label">Email Address</label>
                                    <input type="email" class="hph-input" name="email" required>
                                </div>
                                <div class="hph-form-group">
                                    <label class="hph-label">Alert Frequency</label>
                                    <select class="hph-select" name="frequency">
                                        <option value="immediate">Immediate</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly" selected>Weekly</option>
                                    </select>
                                </div>
                                <button type="submit" class="hph-btn hph-btn-primary hph-w-full">
                                    Save Search Alert
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            HPH.openModal('save-search-modal');
            
            // Handle form submission
            $('.save-search-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                formData.append('action', 'hph_save_search_alert');
                formData.append('nonce', happyPlace.nonce);
                
                $.ajax({
                    url: happyPlace.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            HPH.showAlert('Search alert saved successfully!', 'success');
                            HPH.closeModal();
                        } else {
                            HPH.showAlert(response.data.message || 'Failed to save search alert', 'error');
                        }
                    },
                    error: function() {
                        HPH.showAlert('An error occurred while saving your search alert', 'error');
                    }
                });
            });
            
            // Clean up when modal is closed
            $(document).on('hph-modal-closed', function() {
                $('#save-search-modal').remove();
            });
        },
        
        /**
         * Initialize comment forms
         */
        initCommentForms: function() {
            // Enhance WordPress comment forms
            $('.comment-form').addClass('hph-form');
            $('.comment-form input, .comment-form textarea').addClass('hph-input');
            $('.comment-form input[type="submit"]').addClass('hph-btn hph-btn-primary');
        },
        
        /**
         * Initialize search enhancements
         */
        initSearchEnhancements: function() {
            // Enhance search forms
            $('.search-form').each(function() {
                var $form = $(this);
                var $input = $form.find('input[type="search"]');
                
                if (!$input.hasClass('enhanced')) {
                    $input.addClass('enhanced hph-input');
                    $form.find('input[type="submit"]').addClass('hph-btn hph-btn-primary');
                }
            });
        },
        
        /**
         * Initialize widget enhancements
         */
        initWidgetEnhancements: function() {
            // Enhance widget forms and buttons
            $('.widget input, .widget select, .widget textarea').addClass('hph-input');
            $('.widget input[type="submit"], .widget button').addClass('hph-btn hph-btn-primary');
        },
        
        /**
         * Initialize customizer preview
         */
        initCustomizerPreview: function() {
            if (typeof wp !== 'undefined' && wp.customize) {
                // Handle customizer live preview updates
                wp.customize.preview.bind('active', function() {
                    $('body').addClass('customizer-preview');
                });
            }
        },
        
        /**
         * Initialize custom selects
         */
        initCustomSelects: function() {
            // Custom dropdown styling (if needed)
            $('.custom-select').each(function() {
                // Custom select implementation would go here
            });
        },
        
        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            $('[data-tooltip]').each(function() {
                var $element = $(this);
                var tooltipText = $element.data('tooltip');
                
                $element.on('mouseenter', function() {
                    HappyPlaceTheme.showTooltip($(this), tooltipText);
                }).on('mouseleave', function() {
                    HappyPlaceTheme.hideTooltip();
                });
            });
        },
        
        /**
         * Show tooltip
         */
        showTooltip: function($element, text) {
            var $tooltip = $('<div class="hph-tooltip">' + text + '</div>');
            $('body').append($tooltip);
            
            var elementRect = $element[0].getBoundingClientRect();
            var tooltipRect = $tooltip[0].getBoundingClientRect();
            
            $tooltip.css({
                top: elementRect.top - tooltipRect.height - 10,
                left: elementRect.left + (elementRect.width / 2) - (tooltipRect.width / 2)
            }).addClass('visible');
        },
        
        /**
         * Hide tooltip
         */
        hideTooltip: function() {
            $('.hph-tooltip').remove();
        },
        
        /**
         * Initialize copy to clipboard
         */
        initCopyToClipboard: function() {
            $('.copy-to-clipboard').on('click', function(e) {
                e.preventDefault();
                var text = $(this).data('copy-text') || $(this).text();
                HPH.copyToClipboard(text);
                HPH.showAlert('Copied to clipboard!', 'success');
            });
        },
        
        /**
         * Initialize print features
         */
        initPrintFeatures: function() {
            $('.print-page').on('click', function(e) {
                e.preventDefault();
                window.print();
            });
            
            $('.print-section').on('click', function(e) {
                e.preventDefault();
                var sectionSelector = $(this).data('print-section');
                HappyPlaceTheme.printSection(sectionSelector);
            });
        },
        
        /**
         * Print specific section
         */
        printSection: function(selector) {
            var $section = $(selector);
            if ($section.length) {
                var printContent = $section.html();
                var printWindow = window.open('', '_blank');
                
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Print</title>
                        <link rel="stylesheet" href="${happyPlace.themeUrl}/assets/css/print.css">
                    </head>
                    <body>
                        ${printContent}
                        <script>window.print(); window.close();</script>
                    </body>
                    </html>
                `);
                
                printWindow.document.close();
            }
        }
    };
    
    // Initialize theme when DOM is ready
    $(document).ready(function() {
        HappyPlaceTheme.init();
    });
    
})(jQuery);
