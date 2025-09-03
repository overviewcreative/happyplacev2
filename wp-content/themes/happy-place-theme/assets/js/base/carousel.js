/**
 * HPH Carousel Component
 * 
 * Provides carousel/slider functionality for the base carousel component
 * Handles navigation, indicators, touch/swipe, and responsive behavior
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';

    /**
     * Carousel Component Class
     */
    class HPHCarousel {
        
        constructor(element, options = {}) {
            this.$carousel = $(element);
            this.options = $.extend({}, this.defaults, options);
            
            this.currentSlide = 0;
            this.totalSlides = 0;
            this.slidesToShow = parseInt(this.options.slidesToShow) || 1;
            this.slidesToScroll = parseInt(this.options.slidesToScroll) || 1;
            this.isMultiSlide = this.slidesToShow > 1;
            
            this.init();
        }
        
        /**
         * Default options
         */
        get defaults() {
            return {
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: false,
                autoplaySpeed: 3000,
                pauseOnHover: true,
                showArrows: true,
                showIndicators: true,
                infinite: true,
                responsive: true,
                swipe: true,
                adaptiveHeight: false,
                centerMode: false,
                variableWidth: false,
                speed: 300,
                easing: 'ease-in-out'
            };
        }
        
        /**
         * Initialize carousel
         */
        init() {
            if (!this.$carousel.length) return;
            
            this.setupElements();
            this.setupSlides();
            this.bindEvents();
            this.updateDisplay();
            
            if (this.options.autoplay) {
                this.startAutoplay();
            }
            
            // Mark as initialized
            this.$carousel.addClass('hph-carousel-initialized');
        }
        
        /**
         * Setup carousel elements
         */
        setupElements() {
            this.$container = this.$carousel.find('.hph-carousel-inner');
            this.$slides = this.$carousel.find('.hph-carousel-slide');
            this.$prevBtn = this.$carousel.find('.hph-carousel-prev');
            this.$nextBtn = this.$carousel.find('.hph-carousel-next');
            this.$indicators = this.$carousel.find('.hph-carousel-indicators');
            
            this.totalSlides = this.$slides.length;
            
            // Get slides to show from data attribute or CSS class
            if (this.$carousel.hasClass('hph-carousel-multi')) {
                // Check for specific multi-slide classes
                if (this.$carousel.hasClass('hph-carousel-show-2')) {
                    this.slidesToShow = 2;
                } else if (this.$carousel.hasClass('hph-carousel-show-3')) {
                    this.slidesToShow = 3;
                } else if (this.$carousel.hasClass('hph-carousel-show-4')) {
                    this.slidesToShow = 4;
                } else {
                    this.slidesToShow = parseInt(this.$carousel.data('slides-to-show')) || 3;
                }
                this.isMultiSlide = true;
            }
            
            // Update container for multi-slide layout
            if (this.isMultiSlide && this.$container.length) {
                this.$container.css({
                    'display': 'flex',
                    'transition': `transform ${this.options.speed}ms ${this.options.easing}`
                });
                
                // Set slide widths
                const slideWidth = 100 / this.slidesToShow;
                this.$slides.css({
                    'flex': `0 0 ${slideWidth}%`,
                    'max-width': `${slideWidth}%`
                });
            }
        }
        
        /**
         * Setup slides
         */
        setupSlides() {
            this.$slides.each((index, slide) => {
                $(slide).attr('data-slide-index', index);
            });
            
            // Create indicators if enabled and don't exist
            if (this.options.showIndicators && !this.$indicators.length) {
                this.createIndicators();
            }
        }
        
        /**
         * Create indicators
         */
        createIndicators() {
            const indicatorCount = this.isMultiSlide ? 
                Math.ceil(this.totalSlides / this.slidesToScroll) : 
                this.totalSlides;
                
            let indicatorsHtml = '<div class="hph-carousel-indicators">';
            for (let i = 0; i < indicatorCount; i++) {
                indicatorsHtml += `<button type="button" class="hph-carousel-indicator" data-slide="${i}" aria-label="Go to slide ${i + 1}"></button>`;
            }
            indicatorsHtml += '</div>';
            
            this.$carousel.append(indicatorsHtml);
            this.$indicators = this.$carousel.find('.hph-carousel-indicators');
        }
        
        /**
         * Bind events
         */
        bindEvents() {
            // Navigation buttons
            this.$prevBtn.on('click', (e) => {
                e.preventDefault();
                this.previousSlide();
            });
            
            this.$nextBtn.on('click', (e) => {
                e.preventDefault();
                this.nextSlide();
            });
            
            // Indicators
            this.$carousel.on('click', '.hph-carousel-indicator', (e) => {
                e.preventDefault();
                const slideIndex = parseInt($(e.target).data('slide'));
                this.goToSlide(slideIndex);
            });
            
            // Keyboard navigation
            this.$carousel.on('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.previousSlide();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.nextSlide();
                }
            });
            
            // Pause autoplay on hover
            if (this.options.autoplay && this.options.pauseOnHover) {
                this.$carousel.on('mouseenter', () => this.stopAutoplay());
                this.$carousel.on('mouseleave', () => this.startAutoplay());
            }
            
            // Touch/swipe support
            if (this.options.swipe) {
                this.bindSwipeEvents();
            }
            
            // Window resize
            $(window).on('resize', () => {
                clearTimeout(this.resizeTimeout);
                this.resizeTimeout = setTimeout(() => {
                    this.updateDisplay();
                }, 150);
            });
        }
        
        /**
         * Bind swipe events
         */
        bindSwipeEvents() {
            let startX = 0;
            let startY = 0;
            let distX = 0;
            let distY = 0;
            let threshold = 30;
            let allowedTime = 300;
            let startTime = 0;
            
            this.$container.on('touchstart', (e) => {
                const touch = e.originalEvent.touches[0];
                startX = touch.clientX;
                startY = touch.clientY;
                startTime = new Date().getTime();
                distX = 0;
                distY = 0;
            });
            
            this.$container.on('touchmove', (e) => {
                e.preventDefault();
            });
            
            this.$container.on('touchend', (e) => {
                const touch = e.originalEvent.changedTouches[0];
                distX = touch.clientX - startX;
                distY = touch.clientY - startY;
                const elapsedTime = new Date().getTime() - startTime;
                
                if (elapsedTime <= allowedTime && Math.abs(distX) >= threshold && Math.abs(distY) <= 100) {
                    if (distX > 0) {
                        this.previousSlide();
                    } else {
                        this.nextSlide();
                    }
                }
            });
        }
        
        /**
         * Go to specific slide
         */
        goToSlide(slideIndex) {
            if (slideIndex < 0 || slideIndex >= this.totalSlides) return;
            
            this.currentSlide = slideIndex;
            this.updateDisplay();
        }
        
        /**
         * Go to next slide
         */
        nextSlide() {
            let nextIndex = this.currentSlide + this.slidesToScroll;
            
            if (this.isMultiSlide) {
                if (nextIndex >= this.totalSlides) {
                    nextIndex = this.options.infinite ? 0 : this.totalSlides - this.slidesToShow;
                }
            } else {
                if (nextIndex >= this.totalSlides) {
                    nextIndex = this.options.infinite ? 0 : this.totalSlides - 1;
                }
            }
            
            this.goToSlide(nextIndex);
        }
        
        /**
         * Go to previous slide
         */
        previousSlide() {
            let prevIndex = this.currentSlide - this.slidesToScroll;
            
            if (prevIndex < 0) {
                if (this.options.infinite) {
                    prevIndex = this.isMultiSlide ? 
                        this.totalSlides - this.slidesToShow : 
                        this.totalSlides - 1;
                } else {
                    prevIndex = 0;
                }
            }
            
            this.goToSlide(prevIndex);
        }
        
        /**
         * Update display
         */
        updateDisplay() {
            this.updateSlides();
            this.updateNavigation();
            this.updateIndicators();
        }
        
        /**
         * Update slides position
         */
        updateSlides() {
            if (this.isMultiSlide && this.$container.length) {
                // Multi-slide: translate the container
                const translateX = -(this.currentSlide * (100 / this.slidesToShow));
                this.$container.css('transform', `translateX(${translateX}%)`);
            } else {
                // Single slide: show/hide slides
                this.$slides.removeClass('hph-carousel-slide-active');
                this.$slides.eq(this.currentSlide).addClass('hph-carousel-slide-active');
            }
        }
        
        /**
         * Update navigation buttons
         */
        updateNavigation() {
            if (!this.options.showArrows) return;
            
            if (this.options.infinite) {
                this.$prevBtn.prop('disabled', false);
                this.$nextBtn.prop('disabled', false);
            } else {
                this.$prevBtn.prop('disabled', this.currentSlide === 0);
                
                if (this.isMultiSlide) {
                    this.$nextBtn.prop('disabled', this.currentSlide >= this.totalSlides - this.slidesToShow);
                } else {
                    this.$nextBtn.prop('disabled', this.currentSlide >= this.totalSlides - 1);
                }
            }
        }
        
        /**
         * Update indicators
         */
        updateIndicators() {
            if (!this.options.showIndicators || !this.$indicators.length) return;
            
            const activeIndicator = this.isMultiSlide ? 
                Math.floor(this.currentSlide / this.slidesToScroll) : 
                this.currentSlide;
                
            this.$indicators.find('.hph-carousel-indicator')
                .removeClass('hph-carousel-indicator-active')
                .eq(activeIndicator)
                .addClass('hph-carousel-indicator-active');
        }
        
        /**
         * Start autoplay
         */
        startAutoplay() {
            if (!this.options.autoplay) return;
            
            this.stopAutoplay();
            this.autoplayTimer = setInterval(() => {
                this.nextSlide();
            }, this.options.autoplaySpeed);
        }
        
        /**
         * Stop autoplay
         */
        stopAutoplay() {
            if (this.autoplayTimer) {
                clearInterval(this.autoplayTimer);
                this.autoplayTimer = null;
            }
        }
        
        /**
         * Destroy carousel
         */
        destroy() {
            this.stopAutoplay();
            this.$carousel.off('.hph-carousel');
            this.$carousel.removeClass('hph-carousel-initialized');
            
            if (this.$container.length) {
                this.$container.css({
                    'transform': '',
                    'transition': ''
                });
            }
            
            this.$slides.css({
                'flex': '',
                'max-width': ''
            }).removeClass('hph-carousel-slide-active');
        }
    }
    
    /**
     * jQuery plugin
     */
    $.fn.hphCarousel = function(options) {
        return this.each(function() {
            const $this = $(this);
            let carousel = $this.data('hph-carousel');
            
            if (!carousel) {
                carousel = new HPHCarousel(this, options);
                $this.data('hph-carousel', carousel);
            }
        });
    };
    
    /**
     * Auto-initialize carousels
     */
    $(document).ready(function() {
        $('.hph-carousel').each(function() {
            const $carousel = $(this);
            
            // Get options from data attributes
            const options = {
                slidesToShow: $carousel.data('slides-to-show') || 1,
                slidesToScroll: $carousel.data('slides-to-scroll') || 1,
                autoplay: $carousel.data('autoplay') || false,
                autoplaySpeed: $carousel.data('autoplay-speed') || 3000,
                showArrows: $carousel.data('show-arrows') !== false,
                showIndicators: $carousel.data('show-indicators') !== false,
                infinite: $carousel.data('infinite') !== false,
                swipe: $carousel.data('swipe') !== false
            };
            
            $carousel.hphCarousel(options);
        });
    });

})(jQuery);
