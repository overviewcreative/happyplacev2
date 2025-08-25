/**
 * Hero Section Component JavaScript
 * Location: /wp-content/themes/happy-place/assets/js/components/hero-listing.js
 * 
 * Handles carousel navigation, favorites, sharing, and tour scheduling
 * 
 * Dependencies: jQuery, hphContext global variable
 */

(function($) {
    'use strict';

    // Check for required dependencies
    if (typeof $ === 'undefined') {
        console.error('HeroListing: jQuery is required');
        return;
    }

    // Provide fallback for hphContext if it doesn't exist
    if (typeof window.hphContext === 'undefined') {
        window.hphContext = {
            ajaxUrl: window.ajaxurl || '/wp-admin/admin-ajax.php',
            nonce: '',
            isLoggedIn: false,
            listingId: null
        };
    }

    /**
     * Hero Listing Component
     */
    class HeroListing {
        constructor(element) {
            this.$hero = $(element);
            // Extract listing ID from various possible sources
            this.listingId = this.$hero.data('listing-id') || 
                           this.$hero.closest('[data-listing-id]').data('listing-id') || 
                           window.hphContext.listingId || 
                           null;
            this.$slides = this.$hero.find('.hph-hero__slide');
            this.totalSlides = this.$slides.length;
            this.currentSlide = 0;
            
            // Extract images from slides for lightbox
            this.images = [];
            this.$slides.each((index, slide) => {
                const $slide = $(slide);
                const bgImage = $slide.css('background-image');
                if (bgImage && bgImage !== 'none') {
                    // Extract URL from background-image CSS property
                    const url = bgImage.replace(/^url\(['"]?/, '').replace(/['"]?\)$/, '');
                    this.images.push({ url: url, thumbnail: url, medium: url, large: url });
                }
            });
            
            this.init();
        }

        init() {
            this.bindEvents();
            this.initCarousel();
            this.initFavorites();
            this.initShare();
            this.initTourSchedule();
            
            // Initialize only if we have slides or it's a valid hero section
            if (this.totalSlides === 0 && !this.$hero.find('.hph-hero__content').length) {
                console.warn('Hero component initialized but no slides or content found');
                return false;
            }
            
            return true;
        }

        bindEvents() {
            // Carousel navigation - using correct class names from template
            this.$hero.on('click', '.hph-hero__nav-btn--prev', () => this.prevSlide());
            this.$hero.on('click', '.hph-hero__nav-btn--next', () => this.nextSlide());
            
            // Keyboard navigation
            $(document).on('keydown', (e) => {
                if (this.$hero.is(':visible')) {
                    if (e.key === 'ArrowLeft') this.prevSlide();
                    if (e.key === 'ArrowRight') this.nextSlide();
                }
            });
            
            // Touch/swipe support
            this.initTouchSupport();
            
            // CTA buttons - using inline onclick handlers from template
            // Note: The template uses inline onclick handlers, but we can also bind here
            
            // Auto-advance carousel
            if (this.totalSlides > 1) {
                this.startAutoPlay();
                this.$hero.on('mouseenter', () => this.stopAutoPlay());
                this.$hero.on('mouseleave', () => this.startAutoPlay());
            }
        }

        /**
         * Carousel functionality
         */
        initCarousel() {
            if (this.totalSlides <= 1) return;
            
            // Update counter
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
            this.$slides.removeClass('hph-hero__slide--active');
            
            // Add active class to current slide
            this.$slides.eq(this.currentSlide).addClass('hph-hero__slide--active');
            
            // Update counter
            this.updateCounter();
            
            // Trigger event for analytics
            this.$hero.trigger('slideChange', {
                listing: this.listingId,
                slide: this.currentSlide
            });
        }

        updateCounter() {
            this.$hero.find('.hph-hero__current-photo').text(this.currentSlide + 1);
        }

        /**
         * Auto-play functionality
         */
        startAutoPlay() {
            this.stopAutoPlay();
            this.autoPlayInterval = setInterval(() => {
                this.nextSlide();
            }, 5000); // Change slide every 5 seconds
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
            let touchStartX = 0;
            let touchEndX = 0;
            
            this.$hero.on('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            });
            
            this.$hero.on('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe(touchStartX, touchEndX);
            });
        }

        handleSwipe(startX, endX) {
            const threshold = 50; // Minimum swipe distance
            const diff = startX - endX;
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    // Swiped left
                    this.nextSlide();
                } else {
                    // Swiped right
                    this.prevSlide();
                }
            }
        }

        /**
         * Favorites functionality
         */
        initFavorites() {
            // Check if user is logged in - using global context variable
            const isLoggedIn = window.hphContext.isLoggedIn;
            
            if (!isLoggedIn) {
                this.$hero.on('click', '.hph-hero__btn--icon', (e) => {
                    e.preventDefault();
                    this.showLoginPrompt('Please log in to save properties to your favorites.');
                });
                return;
            }
        }

        /**
         * Share functionality initialization
         */
        initShare() {
            this.$hero.on('click', '.hph-hero__share', (e) => {
                this.shareProperty(e);
            });
        }

        /**
         * Tour scheduling functionality initialization
         */
        initTourSchedule() {
            this.$hero.on('click', '.hph-hero__tour-btn', (e) => {
                this.scheduleTour(e);
            });
        }

        toggleFavorite(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const $icon = $btn.find('i');
            const isFavorite = $btn.hasClass('is-favorite');
            
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
                        // Revert on error
                        if (isFavorite) {
                            $btn.addClass('is-favorite');
                            $icon.removeClass('far').addClass('fas');
                        } else {
                            $btn.removeClass('is-favorite');
                            $icon.removeClass('fas').addClass('far');
                        }
                        
                        this.showNotification('Error updating favorites. Please try again.', 'error');
                    } else {
                        this.showNotification(
                            isFavorite ? 'Removed from favorites' : 'Added to favorites',
                            'success'
                        );
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
         * Share functionality
         */
        shareProperty(e) {
            e.preventDefault();
            
            const shareData = {
                title: this.$hero.find('.hph-hero__address').text(),
                text: `Check out this property: ${this.$hero.find('.hph-hero__address').text()}`,
                url: window.location.href
            };
            
            // Use Web Share API if available
            if (navigator.share && this.isMobile()) {
                navigator.share(shareData).catch(() => {
                    this.showShareModal();
                });
            } else {
                this.showShareModal();
            }
        }

        showShareModal() {
            const url = window.location.href;
            const title = this.$hero.find('.hph-hero__address').text();
            
            // Create share modal
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
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}" 
                                   target="_blank" class="hph-share-btn hph-share-btn--linkedin">
                                    <i class="fab fa-linkedin-in"></i> LinkedIn
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
            
            // Bind events
            $modal.find('.hph-modal__close, .hph-modal__backdrop').on('click', () => {
                $modal.fadeOut(200, () => $modal.remove());
            });
            
            $modal.find('.hph-share-link__copy').on('click', () => {
                const $input = $modal.find('.hph-share-link__input');
                $input.select();
                document.execCommand('copy');
                this.showNotification('Link copied to clipboard!', 'success');
            });
            
            // Show modal
            $modal.fadeIn(200);
        }

        /**
         * Schedule tour functionality
         */
        scheduleTour(e) {
            e.preventDefault();
            
            // Check if tour modal exists in DOM
            const $tourModal = $('#tourScheduleModal');
            if ($tourModal.length) {
                $tourModal.modal('show');
            } else {
                // Load tour scheduling form via AJAX
                this.loadTourScheduler();
            }
        }

        loadTourScheduler() {
            $.ajax({
                url: window.hphContext.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_get_tour_scheduler',
                    listing_id: this.listingId,
                    nonce: window.hphContext.nonce
                },
                beforeSend: () => {
                    this.showLoadingOverlay();
                },
                success: (response) => {
                    this.hideLoadingOverlay();
                    if (response.success) {
                        $(response.data.html).appendTo('body').modal('show');
                    } else {
                        this.showNotification('Error loading tour scheduler. Please try again.', 'error');
                    }
                },
                error: () => {
                    this.hideLoadingOverlay();
                    this.showNotification('Network error. Please try again.', 'error');
                }
            });
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
                // Fallback to simple notification
                const $notification = $(`
                    <div class="hph-notification hph-notification--${type}">
                        ${message}
                    </div>
                `).appendTo('body');
                
                setTimeout(() => {
                    $notification.fadeIn(200);
                }, 100);
                
                setTimeout(() => {
                    $notification.fadeOut(200, () => $notification.remove());
                }, 3000);
            }
        }

        showLoginPrompt(message) {
            if (window.hphLoginModal) {
                window.hphLoginModal.show(message);
            } else {
                this.showNotification(message, 'warning');
            }
        }

        showLoadingOverlay() {
            this.$hero.addClass('hph-loading');
        }

        hideLoadingOverlay() {
            this.$hero.removeClass('hph-loading');
        }
    }

    /**
     * Initialize on DOM ready
     */
    $(document).ready(function() {
        $('.hph-hero').each(function() {
            new HeroListing(this);
        });
    });

    /**
     * Global functions for inline onclick handlers in the template
     */
    window.toggleFavorite = function(listingId) {
        const $hero = $('.hph-hero-listing');
        const heroInstance = $hero.data('heroInstance');
        
        if (heroInstance) {
            const $btn = $hero.find('.hph-hero__btn--icon');
            const mockEvent = { currentTarget: $btn[0] };
            heroInstance.toggleFavorite(mockEvent);
        } else {
            // Fallback - create a new instance
            const hero = new HeroListing($hero[0]);
            const $btn = $hero.find('.hph-hero__btn--icon');
            const mockEvent = { currentTarget: $btn[0] };
            hero.toggleFavorite(mockEvent);
        }
    };

    window.schedulePropertyTour = function(address) {
        const $hero = $('.hph-hero-listing');
        const heroInstance = $hero.data('heroInstance') || new HeroListing($hero[0]);
        heroInstance.scheduleTour({ preventDefault: () => {} });
    };

    window.contactAgent = function(address) {
        // Simple fallback - could be enhanced to open a contact modal
        alert('Contact agent functionality would be implemented here');
    };

    /**
     * Open photo lightbox with gallery
     */
    window.openPhotoLightbox = function(listingId) {
        // Try to find the hero instance multiple ways
        let heroInstance = null;
        let $hero = null;
        
        // First, try to find by listing ID
        if (listingId) {
            $hero = $(`[data-listing-id="${listingId}"]`).find('.hph-hero').first();
            if ($hero.length === 0) {
                $hero = $(`.hph-hero[data-listing-id="${listingId}"]`).first();
            }
        }
        
        // If no specific hero found, get the first one
        if (!$hero || $hero.length === 0) {
            $hero = $('.hph-hero').first();
        }
        
        // Get the hero instance
        if ($hero && $hero.length > 0) {
            heroInstance = $hero.data('heroInstance');
        }
        
        console.log('Lightbox Debug:', {
            listingId: listingId,
            heroFound: $hero ? $hero.length : 0,
            heroInstance: heroInstance,
            images: heroInstance ? heroInstance.images : null
        });
        
        if (!heroInstance || !heroInstance.images || heroInstance.images.length === 0) {
            console.error('No hero instance or images found');
            alert('No photos available');
            return;
        }
        
        const images = heroInstance.images;
        let currentIndex = 0;
        
        // Create lightbox modal
        const modalHtml = `
            <div class="hph-modal hph-lightbox-modal">
                <div class="hph-modal__backdrop"></div>
                <div class="hph-modal__content hph-lightbox__content">
                    <div class="hph-lightbox__header">
                        <div class="hph-lightbox__counter">
                            <span class="hph-lightbox__current">1</span> / ${images.length}
                        </div>
                        <button class="hph-modal__close">&times;</button>
                    </div>
                    <div class="hph-lightbox__body">
                        <div class="hph-lightbox__image-container">
                            <img src="${images[0].url || images[0]}" alt="Property Photo" class="hph-lightbox__image">
                        </div>
                        ${images.length > 1 ? `
                            <button class="hph-lightbox__nav hph-lightbox__nav--prev" aria-label="Previous photo">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="hph-lightbox__nav hph-lightbox__nav--next" aria-label="Next photo">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        ` : ''}
                    </div>
                    ${images.length > 1 ? `
                        <div class="hph-lightbox__thumbnails">
                            ${images.map((img, index) => `
                                <button class="hph-lightbox__thumb ${index === 0 ? 'active' : ''}" data-index="${index}">
                                    <img src="${(img.thumbnail || img.medium || img.url || img)}" alt="Thumbnail ${index + 1}">
                                </button>
                            `).join('')}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        const $modal = $(modalHtml).appendTo('body');
        
        // Update image function
        function updateImage(index) {
            currentIndex = index;
            const img = images[index];
            const imageUrl = img.url || img;
            
            $modal.find('.hph-lightbox__image').attr('src', imageUrl);
            $modal.find('.hph-lightbox__current').text(index + 1);
            $modal.find('.hph-lightbox__thumb').removeClass('active');
            $modal.find('.hph-lightbox__thumb').eq(index).addClass('active');
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
        
        // Thumbnail clicks
        $modal.find('.hph-lightbox__thumb').on('click', function() {
            const index = parseInt($(this).data('index'));
            updateImage(index);
        });
        
        // Keyboard navigation
        $(document).on('keydown.lightbox', (e) => {
            if (e.key === 'ArrowLeft') {
                $modal.find('.hph-lightbox__nav--prev').click();
            } else if (e.key === 'ArrowRight') {
                $modal.find('.hph-lightbox__nav--next').click();
            } else if (e.key === 'Escape') {
                $modal.find('.hph-modal__close').click();
            }
        });
        
        // Close events
        $modal.find('.hph-modal__close, .hph-modal__backdrop').on('click', () => {
            $(document).off('keydown.lightbox');
            $modal.fadeOut(200, () => $modal.remove());
        });
        
        // Show modal
        $modal.fadeIn(200);
    };

    /**
     * Initialize on DOM ready and store instances
     */
    $(document).ready(function() {
        $('.hph-hero').each(function() {
            const instance = new HeroListing(this);
            $(this).data('heroInstance', instance);
        });
    });

    /**
     * Expose to global scope for external access
     */
    window.HphHeroListing = HeroListing;

})(jQuery);