/**
 * Open House Card Interactions
 * 
 * Handles RSVP, sharing, favorites, and calendar integration
 * for open house event cards.
 * 
 * @package HappyPlaceTheme
 * @version 1.0.0
 */

(function($) {
    'use strict';

    class OpenHouseCard {
        constructor() {
            this.init();
            this.bindEvents();
        }

        init() {
            this.selectors = {
                card: '.hph-open-house-card',
                rsvpBtn: '.hph-rsvp-btn',
                shareBtn: '.hph-share-btn',
                favoriteBtn: '.hph-favorite-btn',
                calendarBtn: '.hph-calendar-btn'
            };

            this.state = {
                rsvpLoading: false,
                favoriteLoading: false
            };

            // Get localized data
            this.ajax = window.hph_open_house_ajax || {};
        }

        bindEvents() {
            // RSVP button click
            $(document).on('click', this.selectors.rsvpBtn, (e) => {
                e.preventDefault();
                this.handleRSVP(e.currentTarget);
            });

            // Share button click
            $(document).on('click', this.selectors.shareBtn, (e) => {
                e.preventDefault();
                this.handleShare(e.currentTarget);
            });

            // Favorite button click
            $(document).on('click', this.selectors.favoriteBtn, (e) => {
                e.preventDefault();
                this.handleFavorite(e.currentTarget);
            });

            // Calendar button click (analytics tracking)
            $(document).on('click', this.selectors.calendarBtn, (e) => {
                this.trackCalendarAdd(e.currentTarget);
            });

            // Card hover effects
            $(document).on('mouseenter', this.selectors.card, (e) => {
                this.handleCardHover(e.currentTarget, true);
            });

            $(document).on('mouseleave', this.selectors.card, (e) => {
                this.handleCardHover(e.currentTarget, false);
            });
        }

        /**
         * Handle RSVP submission
         */
        async handleRSVP(button) {
            if (this.state.rsvpLoading) return;

            const $button = $(button);
            const $card = $button.closest(this.selectors.card);
            const listingId = $card.data('listing-id');
            const openHouseDate = $card.data('open-house-date');

            if (!listingId || !openHouseDate) {
                this.showError('Invalid open house data');
                return;
            }

            try {
                this.state.rsvpLoading = true;
                this.setButtonLoading($button, true);

                // Show RSVP modal or form
                await this.showRSVPModal(listingId, openHouseDate);

            } catch (error) {
                this.showError('Failed to process RSVP. Please try again.');
            } finally {
                this.state.rsvpLoading = false;
                this.setButtonLoading($button, false);
            }
        }

        /**
         * Show RSVP modal
         */
        async showRSVPModal(listingId, openHouseDate) {
            return new Promise((resolve, reject) => {
                // Create modal HTML
                const modalHTML = this.createRSVPModalHTML(listingId, openHouseDate);
                
                // Add modal to DOM
                const $modal = $(modalHTML).appendTo('body');
                
                // Initialize modal
                $modal.modal('show');

                // Handle form submission
                $modal.find('.hph-rsvp-form').on('submit', async (e) => {
                    e.preventDefault();
                    
                    try {
                        const formData = new FormData(e.target);
                        const result = await this.submitRSVP(formData);
                        
                        if (result.success) {
                            $modal.modal('hide');
                            this.showSuccess('RSVP submitted successfully!');
                            resolve(result);
                        } else {
                            throw new Error(result.message || 'RSVP submission failed');
                        }
                    } catch (error) {
                        this.showError(error.message);
                        reject(error);
                    }
                });

                // Clean up on close
                $modal.on('hidden.bs.modal', () => {
                    $modal.remove();
                });
            });
        }

        /**
         * Create RSVP modal HTML
         */
        createRSVPModalHTML(listingId, openHouseDate) {
            return `
                <div class="modal fade hph-rsvp-modal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-md" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-calendar-plus"></i>
                                    RSVP for Open House
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form class="hph-rsvp-form">
                                    <input type="hidden" name="action" value="hph_submit_rsvp">
                                    <input type="hidden" name="nonce" value="${this.ajax.nonce || ''}">
                                    <input type="hidden" name="listing_id" value="${listingId}">
                                    <input type="hidden" name="open_house_date" value="${openHouseDate}">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="rsvp-first-name" class="form-label required">First Name</label>
                                                <input type="text" class="form-control" id="rsvp-first-name" name="first_name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="rsvp-last-name" class="form-label required">Last Name</label>
                                                <input type="text" class="form-control" id="rsvp-last-name" name="last_name" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="rsvp-email" class="form-label required">Email Address</label>
                                        <input type="email" class="form-control" id="rsvp-email" name="email" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="rsvp-phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="rsvp-phone" name="phone">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="rsvp-party-size" class="form-label">Party Size</label>
                                        <select class="form-control" id="rsvp-party-size" name="party_size">
                                            <option value="1">1 person</option>
                                            <option value="2">2 people</option>
                                            <option value="3">3 people</option>
                                            <option value="4">4 people</option>
                                            <option value="5+">5+ people</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="rsvp-message" class="form-label">Special Requests or Questions</label>
                                        <textarea class="form-control" id="rsvp-message" name="message" rows="3" placeholder="Any specific questions about the property or special accommodations needed..."></textarea>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="rsvp-updates" name="receive_updates" value="1">
                                        <label class="form-check-label" for="rsvp-updates">
                                            I'd like to receive updates about similar properties
                                        </label>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" form="hph-rsvp-form">
                                    <i class="fas fa-paper-plane"></i>
                                    Submit RSVP
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Submit RSVP via AJAX
         */
        async submitRSVP(formData) {
            const response = await fetch(this.ajax.url || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Network error');
            }

            const result = await response.json();
            return result;
        }

        /**
         * Handle sharing functionality
         */
        handleShare(button) {
            const $button = $(button);
            const url = $button.data('url');
            const title = $button.data('title');

            if (navigator.share) {
                // Use native Web Share API if available
                navigator.share({
                    title: title,
                    url: url
                }).catch(err => {
                    console.log('Error sharing:', err);
                });
            } else {
                // Fallback to social share options
                this.showShareModal(url, title);
            }

            // Track sharing analytics
            this.trackEvent('share', {
                method: 'open_house_card',
                content_type: 'open_house',
                item_id: $button.closest(this.selectors.card).data('listing-id')
            });
        }

        /**
         * Show share modal with social options
         */
        showShareModal(url, title) {
            const encodedUrl = encodeURIComponent(url);
            const encodedTitle = encodeURIComponent(title);
            
            const shareOptions = [
                {
                    name: 'Facebook',
                    icon: 'fab fa-facebook-f',
                    url: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`
                },
                {
                    name: 'Twitter',
                    icon: 'fab fa-twitter',
                    url: `https://twitter.com/intent/tweet?text=${encodedTitle}&url=${encodedUrl}`
                },
                {
                    name: 'LinkedIn',
                    icon: 'fab fa-linkedin-in',
                    url: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`
                },
                {
                    name: 'Email',
                    icon: 'fas fa-envelope',
                    url: `mailto:?subject=${encodedTitle}&body=${encodedUrl}`
                },
                {
                    name: 'Copy Link',
                    icon: 'fas fa-copy',
                    action: 'copy'
                }
            ];

            const modalHTML = `
                <div class="modal fade hph-share-modal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Share Open House</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="hph-share-options">
                                    ${shareOptions.map(option => `
                                        <a href="${option.url || '#'}" 
                                           class="hph-share-option ${option.action ? 'hph-share-copy' : ''}"
                                           ${option.action === 'copy' ? `data-url="${url}"` : 'target="_blank" rel="noopener"'}>
                                            <i class="${option.icon}"></i>
                                            <span>${option.name}</span>
                                        </a>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const $modal = $(modalHTML).appendTo('body');
            $modal.modal('show');

            // Handle copy link
            $modal.find('.hph-share-copy').on('click', (e) => {
                e.preventDefault();
                this.copyToClipboard(url);
                $modal.modal('hide');
                this.showSuccess('Link copied to clipboard!');
            });

            // Clean up
            $modal.on('hidden.bs.modal', () => {
                $modal.remove();
            });
        }

        /**
         * Handle favorite/save functionality
         */
        async handleFavorite(button) {
            if (this.state.favoriteLoading) return;

            const $button = $(button);
            const $card = $button.closest(this.selectors.card);
            const listingId = $card.data('listing-id');

            try {
                this.state.favoriteLoading = true;
                this.setButtonLoading($button, true);

                const result = await this.toggleFavorite(listingId);
                
                if (result.success) {
                    this.updateFavoriteButton($button, result.is_favorite);
                    this.showSuccess(result.is_favorite ? 'Property saved!' : 'Property removed from saved list');
                } else {
                    throw new Error(result.message || 'Failed to save property');
                }

            } catch (error) {
                this.showError('Failed to save property. Please try again.');
            } finally {
                this.state.favoriteLoading = false;
                this.setButtonLoading($button, false);
            }
        }

        /**
         * Toggle favorite status via AJAX
         */
        async toggleFavorite(listingId) {
            const formData = new FormData();
            formData.append('action', 'hph_toggle_favorite');
            formData.append('listing_id', listingId);
            formData.append('nonce', this.ajax.nonce || '');

            const response = await fetch(this.ajax.url || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            return await response.json();
        }

        /**
         * Update favorite button appearance
         */
        updateFavoriteButton($button, isFavorite) {
            const $icon = $button.find('i');
            
            if (isFavorite) {
                $icon.removeClass('far').addClass('fas');
                $button.addClass('favorited');
            } else {
                $icon.removeClass('fas').addClass('far');
                $button.removeClass('favorited');
            }
        }

        /**
         * Track calendar add events
         */
        trackCalendarAdd(button) {
            const $card = $(button).closest(this.selectors.card);
            
            this.trackEvent('add_to_calendar', {
                method: 'google_calendar',
                content_type: 'open_house',
                item_id: $card.data('listing-id')
            });
        }

        /**
         * Handle card hover effects
         */
        handleCardHover(card, isHovering) {
            const $card = $(card);
            
            if (isHovering) {
                $card.addClass('is-hovering');
            } else {
                $card.removeClass('is-hovering');
            }
        }

        /**
         * Utility: Set button loading state
         */
        setButtonLoading($button, isLoading) {
            if (isLoading) {
                $button.addClass('loading').prop('disabled', true);
            } else {
                $button.removeClass('loading').prop('disabled', false);
            }
        }

        /**
         * Utility: Copy text to clipboard
         */
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
            } catch (err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
            }
        }

        /**
         * Utility: Show success message
         */
        showSuccess(message) {
            this.showNotification(message, 'success');
        }

        /**
         * Utility: Show error message
         */
        showError(message) {
            this.showNotification(message, 'error');
        }

        /**
         * Utility: Show notification
         */
        showNotification(message, type = 'info') {
            // Use your existing notification system or create a simple one
            if (window.hph_notifications) {
                window.hph_notifications.show(message, type);
            } else {
                // Simple fallback
                const className = type === 'error' ? 'alert-danger' : 'alert-success';
                const $notification = $(`
                    <div class="alert ${className} alert-dismissible fade show hph-notification" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                        ${message}
                        <button type="button" class="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `).appendTo('body');

                // Auto dismiss
                setTimeout(() => {
                    $notification.alert('close');
                }, 5000);

                // Manual dismiss
                $notification.find('.close').on('click', () => {
                    $notification.alert('close');
                });
            }
        }

        /**
         * Utility: Track analytics events
         */
        trackEvent(eventName, parameters = {}) {
            // Google Analytics 4
            if (typeof gtag !== 'undefined') {
                gtag('event', eventName, parameters);
            }

            // Facebook Pixel
            if (typeof fbq !== 'undefined') {
                fbq('track', eventName, parameters);
            }

            // Custom analytics
            if (window.hph_analytics) {
                window.hph_analytics.track(eventName, parameters);
            }
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        new OpenHouseCard();
    });

})(jQuery);