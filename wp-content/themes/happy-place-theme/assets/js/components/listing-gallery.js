/**
 * HPH Property Gallery JavaScript
 * Location: /wp-content/themes/happy-place/assets/js/property-gallery.js
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';

    /**
     * HPH Property Gallery Class
     */
    class HPHPropertyGallery {
        constructor() {
            this.currentView = 'grid';
            this.currentFilter = 'all';
            this.currentSlide = 0;
            this.currentLightboxIndex = 0;
            this.images = window.hphGalleryConfig?.images || [];
            this.filteredImages = [...this.images];
            this.itemsPerLoad = 12;
            this.itemsLoaded = 12;
            
            this.init();
        }

        /**
         * Initialize gallery
         */
        init() {
            this.cacheElements();
            this.bindEvents();
            this.initLazyLoading();
            this.initSlider();
            this.setupKeyboardNavigation();
        }

        /**
         * Cache DOM elements
         */
        cacheElements() {
            // Main elements
            this.$gallery = $('.hph-property-gallery');
            this.$viewBtns = $('.hph-view-btn');
            this.$views = $('.hph-gallery-view');
            this.$filterBtns = $('.hph-filter-item');
            this.$galleryItems = $('.hph-gallery-item');
            
            // Lightbox elements
            this.$lightbox = $('#hph-gallery-lightbox');
            this.$lightboxImage = this.$lightbox.find('.hph-lightbox-image img');
            this.$lightboxTitle = this.$lightbox.find('.hph-lightbox-title');
            this.$lightboxCaption = this.$lightbox.find('.hph-lightbox-caption');
            this.$lightboxCounter = this.$lightbox.find('.hph-lightbox-counter');
            this.$lightboxLoader = this.$lightbox.find('.hph-lightbox-loader');
            
            // Video modal
            this.$videoModal = $('#hph-video-modal');
            this.$videoWrapper = this.$videoModal.find('.hph-video-wrapper');
            
            // Floor plans
            this.$floorPlans = $('.hph-floor-plans');
            
            // Slider elements
            this.$slides = $('.hph-slide');
            this.$thumbs = $('.hph-thumb');
        }

        /**
         * Bind events
         */
        bindEvents() {
            // View switcher
            this.$viewBtns.on('click', (e) => this.switchView($(e.currentTarget)));
            
            // Category filter
            this.$filterBtns.on('click', (e) => this.filterImages($(e.currentTarget)));
            
            // Gallery item click
            this.$gallery.on('click', '.hph-expand-btn', (e) => {
                e.preventDefault();
                const index = $(e.currentTarget).data('index');
                this.openLightbox(index);
            });
            
            // Lightbox navigation
            this.$lightbox.on('click', '.hph-lightbox-prev', () => this.previousImage());
            this.$lightbox.on('click', '.hph-lightbox-next', () => this.nextImage());
            this.$lightbox.on('click', '.hph-lightbox-close, .hph-lightbox-overlay', () => this.closeLightbox());
            
            // Fullscreen
            this.$lightbox.on('click', '.hph-lightbox-fullscreen', () => this.toggleFullscreen());
            
            // Video tour
            $('.hph-video-tour-btn').on('click', (e) => this.openVideoModal($(e.currentTarget)));
            this.$videoModal.on('click', '.hph-video-close, .hph-video-overlay', () => this.closeVideoModal());
            
            // Floor plans
            $('.hph-floor-plan-btn').on('click', () => this.showFloorPlans());
            $('.hph-close-floor-plans').on('click', () => this.hideFloorPlans());
            
            // Load more
            $('.hph-load-more-btn').on('click', () => this.loadMoreImages());
            
            // Slider navigation
            $('.hph-slider-prev').on('click', () => this.previousSlide());
            $('.hph-slider-next').on('click', () => this.nextSlide());
            this.$thumbs.on('click', (e) => this.goToSlide($(e.currentTarget).data('index')));
            
            // Window resize
            $(window).on('resize', () => this.handleResize());
        }

        /**
         * Switch view type
         */
        switchView($btn) {
            const view = $btn.data('view');
            
            // Update buttons
            this.$viewBtns.removeClass('active').attr('aria-selected', 'false');
            $btn.addClass('active').attr('aria-selected', 'true');
            
            // Update views
            this.$views.removeClass('active');
            $(`.hph-${view}-view`).addClass('active');
            
            this.currentView = view;
            
            // Reinitialize lazy loading for new view
            if (view === 'grid' || view === 'list') {
                this.initLazyLoading();
            }
        }

        /**
         * Filter images by category
         */
        filterImages($btn) {
            const category = $btn.data('category');
            
            // Update buttons
            this.$filterBtns.removeClass('active');
            $btn.addClass('active');
            
            this.currentFilter = category;
            
            // Filter items
            if (category === 'all') {
                this.$galleryItems.fadeIn(300);
                this.filteredImages = [...this.images];
            } else {
                this.$galleryItems.each(function() {
                    const $item = $(this);
                    if ($item.data('category') === category) {
                        $item.fadeIn(300);
                    } else {
                        $item.fadeOut(300);
                    }
                });
                
                this.filteredImages = this.images.filter(img => 
                    img.category.toLowerCase().replace(' ', '-') === category
                );
            }
            
            // Update counter
            this.updateCounter();
        }

        /**
         * Initialize lazy loading
         */
        initLazyLoading() {
            if (!window.hphGalleryConfig?.lazyLoad) return;
            
            const lazyImages = document.querySelectorAll('.hph-lazy:not(.loaded)');
            
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.add('loaded');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                lazyImages.forEach(img => imageObserver.observe(img));
            } else {
                // Fallback for browsers without IntersectionObserver
                lazyImages.forEach(img => {
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                });
            }
        }

        /**
         * Initialize slider
         */
        initSlider() {
            if (this.$slides.length > 0) {
                this.updateSlider();
            }
        }

        /**
         * Setup keyboard navigation
         */
        setupKeyboardNavigation() {
            $(document).on('keydown', (e) => {
                if (this.$lightbox.hasClass('active')) {
                    switch(e.keyCode) {
                        case 27: // Escape
                            this.closeLightbox();
                            break;
                        case 37: // Left arrow
                            this.previousImage();
                            break;
                        case 39: // Right arrow
                            this.nextImage();
                            break;
                    }
                }
            });
        }

        /**
         * Open lightbox
         */
        openLightbox(index) {
            this.currentLightboxIndex = parseInt(index);
            this.updateLightboxImage();
            this.$lightbox.addClass('active').attr('aria-hidden', 'false');
            $('body').addClass('hph-lightbox-open');
        }

        /**
         * Close lightbox
         */
        closeLightbox() {
            this.$lightbox.removeClass('active').attr('aria-hidden', 'true');
            $('body').removeClass('hph-lightbox-open');
        }

        /**
         * Previous image in lightbox
         */
        previousImage() {
            this.currentLightboxIndex = this.currentLightboxIndex > 0 
                ? this.currentLightboxIndex - 1 
                : this.filteredImages.length - 1;
            this.updateLightboxImage();
        }

        /**
         * Next image in lightbox
         */
        nextImage() {
            this.currentLightboxIndex = this.currentLightboxIndex < this.filteredImages.length - 1 
                ? this.currentLightboxIndex + 1 
                : 0;
            this.updateLightboxImage();
        }

        /**
         * Update lightbox image
         */
        updateLightboxImage() {
            const image = this.filteredImages[this.currentLightboxIndex];
            if (!image) return;

            this.$lightboxLoader.addClass('active');
            
            const $img = this.$lightboxImage;
            $img.on('load', () => {
                this.$lightboxLoader.removeClass('active');
            });

            $img.attr('src', image.url);
            $img.attr('alt', image.alt || image.title);
            
            this.$lightboxTitle.text(image.title || '');
            this.$lightboxCaption.text(image.caption || '');
            
            // Update counter
            this.$lightboxCounter.find('.current').text(this.currentLightboxIndex + 1);
            this.$lightboxCounter.find('.total').text(this.filteredImages.length);
        }

        /**
         * Toggle fullscreen
         */
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                this.$lightbox[0].requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        /**
         * Open video modal
         */
        openVideoModal($btn) {
            const videoUrl = $btn.data('video');
            if (!videoUrl) return;

            // Create iframe
            const $iframe = $('<iframe>', {
                src: videoUrl,
                frameborder: 0,
                allowfullscreen: true,
                width: '100%',
                height: '100%'
            });

            this.$videoWrapper.html($iframe);
            this.$videoModal.addClass('active').attr('aria-hidden', 'false');
            $('body').addClass('hph-modal-open');
        }

        /**
         * Close video modal
         */
        closeVideoModal() {
            this.$videoModal.removeClass('active').attr('aria-hidden', 'true');
            this.$videoWrapper.empty(); // Stop video playback
            $('body').removeClass('hph-modal-open');
        }

        /**
         * Show floor plans
         */
        showFloorPlans() {
            this.$floorPlans.slideDown(300);
        }

        /**
         * Hide floor plans
         */
        hideFloorPlans() {
            this.$floorPlans.slideUp(300);
        }

        /**
         * Load more images
         */
        loadMoreImages() {
            if (!window.hphGalleryConfig?.ajaxUrl) return;

            const $btn = $('.hph-load-more-btn');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

            $.ajax({
                url: window.hphGalleryConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_load_more_images',
                    nonce: window.hphGalleryConfig.nonce,
                    listing_id: window.hphGalleryConfig.listingId || 0,
                    offset: this.itemsLoaded,
                    limit: this.itemsPerLoad,
                    category: this.currentFilter
                },
                success: (response) => {
                    if (response.success && response.data.images) {
                        // Add new images to DOM
                        this.appendImages(response.data.images);
                        this.itemsLoaded += response.data.images.length;

                        if (!response.data.hasMore) {
                            $btn.hide();
                        }
                    }
                },
                complete: () => {
                    $btn.prop('disabled', false).html('<i class="fas fa-plus-circle"></i> Load More Photos');
                }
            });
        }

        /**
         * Append images to grid
         */
        appendImages(images) {
            const $grid = $('.hph-grid-container');
            
            images.forEach((image, index) => {
                const $item = this.createImageElement(image, this.itemsLoaded + index);
                $grid.append($item);
            });

            // Reinitialize lazy loading for new images
            this.initLazyLoading();
        }

        /**
         * Create image element
         */
        createImageElement(image, index) {
            return $(`
                <div class="hph-gallery-item" 
                     data-category="${image.category.toLowerCase().replace(' ', '-')}"
                     data-index="${index}">
                    <div class="hph-gallery-item-inner">
                        <img src="${window.hphGalleryConfig.lazyLoad ? 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 9"%3E%3C/svg%3E' : image.url}" 
                             ${window.hphGalleryConfig.lazyLoad ? `data-src="${image.url}" class="hph-lazy"` : `src="${image.url}"`}
                             alt="${image.alt || image.title}"
                             loading="lazy">
                        
                        <div class="hph-gallery-overlay">
                            <div class="hph-overlay-content">
                                <h4 class="hph-image-title">${image.title}</h4>
                                ${image.caption ? `<p class="hph-image-caption">${image.caption}</p>` : ''}
                            </div>
                            <div class="hph-overlay-actions">
                                <button class="hph-action-btn hph-expand-btn" data-index="${index}">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                        
                        <span class="hph-image-category">${image.category}</span>
                    </div>
                </div>
            `);
        }

        /**
         * Previous slide
         */
        previousSlide() {
            this.currentSlide = this.currentSlide > 0 
                ? this.currentSlide - 1 
                : this.$slides.length - 1;
            this.updateSlider();
        }

        /**
         * Next slide
         */
        nextSlide() {
            this.currentSlide = this.currentSlide < this.$slides.length - 1 
                ? this.currentSlide + 1 
                : 0;
            this.updateSlider();
        }

        /**
         * Go to specific slide
         */
        goToSlide(index) {
            this.currentSlide = parseInt(index);
            this.updateSlider();
        }

        /**
         * Update slider display
         */
        updateSlider() {
            this.$slides.removeClass('active').eq(this.currentSlide).addClass('active');
            this.$thumbs.removeClass('active').eq(this.currentSlide).addClass('active');
        }

        /**
         * Update counter
         */
        updateCounter() {
            $('.hph-gallery-count').text(`${this.filteredImages.length} Photos`);
        }

        /**
         * Handle window resize
         */
        handleResize() {
            // Recalculate layouts if needed
            if (this.currentView === 'slider') {
                this.updateSlider();
            }
        }
    }

    // Initialize gallery when DOM is ready
    $(document).ready(function() {
        if ($('.hph-property-gallery').length) {
            window.hphPropertyGallery = new HPHPropertyGallery();
        }
    });

})(jQuery);