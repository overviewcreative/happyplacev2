/**
 * Listing Card Component JavaScript
 * Location: /wp-content/themes/happy-place/assets/js/components/listing-card.js
 * 
 * Handles carousel navigation, favorites, sharing, and interactions for listing cards
 * 
 * Dependencies: jQuery, hphContext global variable
 */

(function($) {
    'use strict';

    // Check for required dependencies
    if (typeof $ === 'undefined') {
        console.error('ListingCard: jQuery is required');
        return;
    }

    // Provide fallback for hphContext if it doesn't exist
    if (typeof window.hphContext === 'undefined') {
        window.hphContext = {
            ajaxUrl: window.ajaxurl || '/wp-admin/admin-ajax.php',
            nonce: '',
            isLoggedIn: false
        };
    }

    /**
     * Listing Card Component
     */
    class ListingCard {
        constructor(element) {
            this.$card = $(element);
            this.listingId = this.$card.data('listing-id') || null;
            this.$carousel = this.$card.find('.hph-card__carousel');
            this.$slides = this.$carousel.find('.hph-card__carousel-slide');
            this.totalSlides = this.$slides.length;
            this.currentSlide = 0;
            
            // Images will be extracted in initCarousel from background-image styles
            this.images = [];
            
            this.init();
        }

        init() {
            this.bindEvents();
            this.initCarousel();
            this.initFavorites();
            this.initActions();
            
            // Initialize only if we have content
            if (this.totalSlides === 0 && !this.$card.find('.hph-card__content').length) {
                console.warn('Card component initialized but no content found');
                return false;
            }
            
            return true;
        }

        bindEvents() {
            // Carousel navigation - updated for new hero-style structure
            this.$card.on('click', '.hph-card__nav-btn--prev', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.prevSlide();
            });
            
            this.$card.on('click', '.hph-card__nav-btn--next', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.nextSlide();
            });
            
            // Photo counter click
            this.$card.on('click', '.hph-card__photo-count', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.openLightbox();
            });
            
            // Touch/swipe support for carousel
            this.initTouchSupport();
            
            // Auto-advance carousel on hover (optional)
            if (this.totalSlides > 1) {
                this.$card.on('mouseenter', () => this.startAutoPlay());
                this.$card.on('mouseleave', () => this.stopAutoPlay());
            }
        }

        /**
         * Carousel functionality
         */
        initCarousel() {
            if (this.totalSlides <= 1) {
                // Hide navigation if only one slide
                this.$card.find('.hph-card__nav').hide();
                this.$card.find('.hph-card__photo-count').hide();
                return;
            }
            
            // Extract images from background-image styles for lightbox
            this.$slides.each((index, slide) => {
                const $slide = $(slide);
                const bgImage = $slide.css('background-image');
                if (bgImage && bgImage !== 'none') {
                    // Extract URL from background-image style
                    const matches = bgImage.match(/url\(['"]?([^'")]+)['"]?\)/);
                    if (matches && matches[1]) {
                        this.images.push({
                            url: matches[1],
                            alt: `Property Image ${index + 1}`
                        });
                    }
                }
            });
            
            // Show first slide
            this.$slides.first().addClass('hph-card__carousel-slide--active');
            this.updateCounter();
        }

        prevSlide() {
            if (this.totalSlides <= 1) return;
            
            this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
            this.updateSlide();
        }

        nextSlide() {
            if (this.totalSlides <= 1) return;
            
            this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
            this.updateSlide();
        }

        updateSlide() {
            // Remove active class from all slides
            this.$slides.removeClass('hph-card__carousel-slide--active');
            
            // Add active class to current slide
            this.$slides.eq(this.currentSlide).addClass('hph-card__carousel-slide--active');
            
            // Update counter
            this.updateCounter();
            
            // Trigger event for analytics
            this.$card.trigger('slideChange', {
                listing: this.listingId,
                slide: this.currentSlide
            });
        }

        updateCounter() {
            const $counter = this.$card.find('.hph-card__photo-count .hph-card__current-photo');
            if ($counter.length) {
                $counter.text(this.currentSlide + 1);
            }
        }

        /**
         * Auto-play functionality (subtle)
         */
        startAutoPlay() {
            this.stopAutoPlay();
            if (this.totalSlides > 1) {
                this.autoPlayInterval = setInterval(() => {
                    this.nextSlide();
                }, 4000); // Slower than hero for cards
            }
        }

        stopAutoPlay() {
            if (this.autoPlayInterval) {
                clearInterval(this.autoPlayInterval);
                this.autoPlayInterval = null;
            }
        }

        /**
         * Touch support for mobile
         */
        initTouchSupport() {
            if (this.totalSlides <= 1) return;
            
            let touchStartX = 0;
            let touchEndX = 0;
            
            this.$carousel.on('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            });
            
            this.$carousel.on('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe(touchStartX, touchEndX);
            });
        }

        handleSwipe(startX, endX) {
            const threshold = 50; // Minimum swipe distance
            const diff = startX - endX;
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            }
        }

        /**
         * Favorites functionality
         */
        initFavorites() {
            this.$card.on('click', '.hph-card__action-btn--favorite', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleFavorite(e);
            });
        }

        toggleFavorite(e) {
            const $btn = $(e.currentTarget);
            const $icon = $btn.find('i');
            const isFavorite = $btn.hasClass('is-favorite');
            
            // Check if user is logged in
            if (!window.hphContext.isLoggedIn) {
                this.showLoginPrompt('Please log in to save favorites');
                return;
            }
            
            // Optimistic UI update
            if (isFavorite) {
                $btn.removeClass('is-favorite');
                $icon.removeClass('fas').addClass('far');
            } else {
                $btn.addClass('is-favorite');
                $icon.removeClass('far').addClass('fas');
            }
            
            // Send AJAX request
            $.ajax({
                url: window.hphContext.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_toggle_favorite',
                    listing_id: this.listingId,
                    nonce: window.hphContext.nonce
                },
                success: (response) => {
                    if (!response.success) {
                        // Revert on server error
                        if (isFavorite) {
                            $btn.addClass('is-favorite');
                            $icon.removeClass('far').addClass('fas');
                        } else {
                            $btn.removeClass('is-favorite');
                            $icon.removeClass('fas').addClass('far');
                        }
                        this.showNotification(response.data.message || 'Error updating favorite', 'error');
                    } else {
                        const action = response.data.favorited ? 'added to' : 'removed from';
                        this.showNotification(`Property ${action} favorites`, 'success');
                    }
                },
                error: () => {
                    // Revert on error
                    if (isFavorite) {
                        $btn.addClass('is-favorite');
                        $icon.removeClass('far').addClass('fas');
                    } else {
                        $btn.removeClass('is-favorite');
                        $icon.removeClass('fas').addClass('far');
                    }
                    this.showNotification('Network error. Please try again.', 'error');
                }
            });
        }

        /**
         * Card actions initialization
         */
        initActions() {
            // Share functionality
            this.$card.on('click', '.hph-card__action-btn--share', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.shareProperty();
            });
            
            // Compare functionality
            this.$card.on('click', '.hph-card__action-btn--compare', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleCompare();
            });
            
            // Contact functionality
            this.$card.on('click', '.hph-card__action-btn--contact', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.contactAgent();
            });
        }

        /**
         * Open photo lightbox
         */
        openLightbox() {
            if (this.images.length === 0) {
                console.warn('No images available for lightbox');
                return;
            }
            
            // Use the global lightbox function if available
            if (typeof window.openPhotoLightbox === 'function') {
                window.openPhotoLightbox(this.listingId);
            } else {
                // Fallback - simple modal
                this.showSimpleLightbox();
            }
        }

        showSimpleLightbox() {
            const images = this.images;
            let currentIndex = this.currentSlide || 0;
            
            const modalHtml = `
                <div class="hph-modal hph-lightbox-modal">
                    <div class="hph-modal__backdrop"></div>
                    <div class="hph-modal__content hph-lightbox__content">
                        <div class="hph-lightbox__header">
                            <div class="hph-lightbox__counter">
                                <span class="hph-lightbox__current">${currentIndex + 1}</span> / ${images.length}
                            </div>
                            <button class="hph-modal__close">&times;</button>
                        </div>
                        <div class="hph-lightbox__body">
                            <div class="hph-lightbox__image-container">
                                <img src="${images[currentIndex].url}" alt="${images[currentIndex].alt}" class="hph-lightbox__image">
                            </div>
                            ${images.length > 1 ? `
                                <button class="hph-lightbox__nav hph-lightbox__nav--prev">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="hph-lightbox__nav hph-lightbox__nav--next">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            const $modal = $(modalHtml).appendTo('body');
            
            // Update image function
            function updateImage(index) {
                currentIndex = index;
                const img = images[index];
                $modal.find('.hph-lightbox__image').attr('src', img.url).attr('alt', img.alt);
                $modal.find('.hph-lightbox__current').text(index + 1);
            }
            
            // Navigation
            $modal.find('.hph-lightbox__nav--prev').on('click', () => {
                const newIndex = currentIndex > 0 ? currentIndex - 1 : images.length - 1;
                updateImage(newIndex);
            });
            
            $modal.find('.hph-lightbox__nav--next').on('click', () => {
                const newIndex = currentIndex < images.length - 1 ? currentIndex + 1 : 0;
                updateImage(newIndex);
            });
            
            // Close events
            $modal.find('.hph-modal__close, .hph-modal__backdrop').on('click', () => {
                $modal.fadeOut(200, () => $modal.remove());
            });
            
            // Keyboard navigation
            $(document).on('keydown.cardLightbox', (e) => {
                if (e.key === 'ArrowLeft') {
                    const newIndex = currentIndex > 0 ? currentIndex - 1 : images.length - 1;
                    updateImage(newIndex);
                } else if (e.key === 'ArrowRight') {
                    const newIndex = currentIndex < images.length - 1 ? currentIndex + 1 : 0;
                    updateImage(newIndex);
                } else if (e.key === 'Escape') {
                    $(document).off('keydown.cardLightbox');
                    $modal.fadeOut(200, () => $modal.remove());
                }
            });
            
            // Show modal
            $modal.fadeIn(200);
        }

        /**
         * Share functionality
         */
        shareProperty() {
            const url = this.$card.find('.hph-card__title a').attr('href') || window.location.href;
            const title = this.$card.find('.hph-card__title').text().trim();
            
            const shareData = {
                title: title,
                text: `Check out this property: ${title}`,
                url: url
            };
            
            // Use Web Share API if available on mobile
            if (navigator.share && this.isMobile()) {
                navigator.share(shareData).catch(err => console.log('Error sharing:', err));
            } else {
                this.showShareModal(url, title);
            }
        }

        showShareModal(url, title) {
            // Simple share modal implementation
            const modalHtml = `
                <div class="hph-modal hph-share-modal">
                    <div class="hph-modal__backdrop"></div>
                    <div class="hph-modal__content">
                        <div class="hph-modal__header">
                            <h3>Share This Property</h3>
                            <button class="hph-modal__close">&times;</button>
                        </div>
                        <div class="hph-modal__body">
                            <div class="hph-share-buttons">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}" 
                                   target="_blank" class="hph-share-btn hph-share-btn--facebook">
                                    <i class="fab fa-facebook-f"></i> Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}" 
                                   target="_blank" class="hph-share-btn hph-share-btn--twitter">
                                    <i class="fab fa-twitter"></i> Twitter
                                </a>
                                <a href="mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(url)}" 
                                   class="hph-share-btn hph-share-btn--email">
                                    <i class="fas fa-envelope"></i> Email
                                </a>
                            </div>
                            <div class="hph-share-link">
                                <input type="text" value="${url}" readonly class="hph-share-link__input">
                                <button class="hph-share-link__copy">Copy Link</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const $modal = $(modalHtml).appendTo('body');
            
            // Copy link functionality
            $modal.find('.hph-share-link__copy').on('click', () => {
                const input = $modal.find('.hph-share-link__input')[0];
                input.select();
                document.execCommand('copy');
                $modal.find('.hph-share-link__copy').text('Copied!');
                setTimeout(() => {
                    $modal.find('.hph-share-link__copy').text('Copy Link');
                }, 2000);
            });
            
            // Close events
            $modal.find('.hph-modal__close, .hph-modal__backdrop').on('click', () => {
                $modal.fadeOut(200, () => $modal.remove());
            });
            
            $modal.fadeIn(200);
        }

        /**
         * Compare functionality
         */
        toggleCompare() {
            const $btn = this.$card.find('.hph-card__action-btn--compare');
            const isComparing = $btn.hasClass('is-comparing');
            
            // Toggle visual state
            if (isComparing) {
                $btn.removeClass('is-comparing');
                this.$card.removeClass('is-comparing');
            } else {
                $btn.addClass('is-comparing');
                this.$card.addClass('is-comparing');
            }
            
            // Trigger compare event for external handling
            this.$card.trigger('compareToggle', {
                listing: this.listingId,
                comparing: !isComparing
            });
            
            const action = !isComparing ? 'added to' : 'removed from';
            this.showNotification(`Property ${action} comparison list`, 'info');
        }

        /**
         * Contact agent functionality
         */
        contactAgent() {
            // Trigger contact event for external handling
            this.$card.trigger('contactAgent', {
                listing: this.listingId
            });
            
            // Simple fallback
            this.showNotification('Contact functionality would open here', 'info');
        }

        /**
         * Utility functions
         */
        isMobile() {
            return window.matchMedia('(max-width: 768px)').matches;
        }

        showNotification(message, type = 'info') {
            // Use theme's notification system if available
            if (window.hphNotify) {
                window.hphNotify(message, type);
            } else {
                // Simple fallback
                console.log(`${type.toUpperCase()}: ${message}`);
                
                // Simple toast notification
                const toast = $(`
                    <div class="hph-toast hph-toast--${type}">
                        ${message}
                    </div>
                `).appendTo('body');
                
                setTimeout(() => {
                    toast.fadeOut(300, () => toast.remove());
                }, 3000);
            }
        }

        showLoginPrompt(message) {
            if (window.hphLoginModal) {
                window.hphLoginModal.show();
            } else {
                alert(message);
            }
        }
    }

    /**
     * Initialize on DOM ready
     */
    $(document).ready(function() {
        $('.hph-listing-card').each(function() {
            const instance = new ListingCard(this);
            $(this).data('cardInstance', instance);
        });
    });

    /**
     * Global functions for inline onclick handlers (if needed)
     */
    window.toggleCardFavorite = function(listingId) {
        const $card = $(`.hph-listing-card[data-listing-id="${listingId}"]`);
        const cardInstance = $card.data('cardInstance');
        
        if (cardInstance) {
            const $btn = $card.find('.hph-card__action-btn--favorite');
            const mockEvent = { currentTarget: $btn[0] };
            cardInstance.toggleFavorite(mockEvent);
        }
    };

    /**
     * Expose to global scope for external access
     */
    window.HphListingCard = ListingCard;

})(jQuery);
