/**
 * Universal Carousel Component JavaScript
 * Handles carousel functionality for any post type
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 */

class HphUniversalCarousel {
    constructor(element) {
        this.container = element;
        this.track = element.querySelector('[data-carousel-track]');
        this.slides = Array.from(element.querySelectorAll('.hph-carousel__slide'));
        this.prevButton = element.querySelector('[data-carousel-prev]');
        this.nextButton = element.querySelector('[data-carousel-next]');
        this.dots = Array.from(element.querySelectorAll('.hph-carousel__dot'));
        this.loading = element.querySelector('[data-carousel-loading]');

        console.log('Universal Carousel initialized:', {
            track: !!this.track,
            slides: this.slides.length,
            prevButton: !!this.prevButton,
            nextButton: !!this.nextButton,
            dots: this.dots.length
        });
        
        // Configuration from data attributes
        this.config = {
            postType: element.dataset.postType || 'post',
            autoplay: element.dataset.autoplay === 'true',
            autoplaySpeed: parseInt(element.dataset.autoplaySpeed) || 5000,
            showNavigation: element.dataset.showNavigation === 'true',
            showDots: element.dataset.showDots === 'true',
            infinite: element.dataset.infinite !== 'false' // Default to true for infinite loop
        };
        
        // State
        this.currentIndex = 0;
        this.totalSlides = this.slides.length;
        this.slidesPerView = this.calculateSlidesPerView();
        this.maxIndex = Math.max(0, this.totalSlides - this.slidesPerView);
        this.autoplayInterval = null;
        this.isAnimating = false;
        this.touchStartX = 0;
        this.touchEndX = 0;
        this.slideWidth = 0;
        
        this.init();
    }
    
    calculateSlidesPerView() {
        if (!this.slides.length) return 1;
        
        const containerWidth = this.container.clientWidth;
        const slideWidth = this.slides[0].offsetWidth;
        const gap = parseFloat(getComputedStyle(this.container).getPropertyValue('--hph-carousel-gap')) || 16;
        
        if (slideWidth <= 0) return 1;
        
        return Math.floor((containerWidth + gap) / (slideWidth + gap));
    }
    
    calculateDimensions() {
        this.slidesPerView = this.calculateSlidesPerView();
        this.maxIndex = Math.max(0, this.totalSlides - this.slidesPerView);
        this.slideWidth = this.slides.length > 0 ? this.slides[0].offsetWidth : 280;
        
        // Ensure current index doesn't exceed max
        if (this.currentIndex > this.maxIndex) {
            this.currentIndex = this.maxIndex;
        }
    }
    
    init() {
        if (this.totalSlides === 0) {
            return;
        }

        // Delay initialization slightly to ensure elements are fully rendered
        setTimeout(() => {
            this.calculateDimensions();
            this.setupResponsive();
            this.bindEvents();
            this.updateCarousel();

            if (this.config.autoplay) {
                this.startAutoplay();
            }

            // Initialize accessibility
            this.setupAccessibility();

            // Mark as initialized
            this.container.classList.add('is-initialized');
        }, 100);
    }
    
    setupResponsive() {
        // Watch for resize events
        const resizeObserver = new ResizeObserver(() => {
            this.handleResize();
        });
        
        resizeObserver.observe(this.container);
        this.resizeObserver = resizeObserver;
        
        // Also listen for window resize as fallback
        this.handleResize = this.handleResize.bind(this);
        window.addEventListener('resize', this.handleResize);
    }
    
    handleResize() {
        // Debounce resize events
        clearTimeout(this.resizeTimeout);
        this.resizeTimeout = setTimeout(() => {
            this.calculateDimensions();
            this.updateCarousel();
        }, 250);
    }
    
    bindEvents() {
        // Navigation buttons
        if (this.prevButton) {
            this.prevButton.addEventListener('click', () => this.goToPrevious());
        }
        
        if (this.nextButton) {
            this.nextButton.addEventListener('click', () => this.goToNext());
        }
        
        // Dots navigation
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                const slideIndex = index * this.slidesPerView;
                this.goToSlide(slideIndex);
            });
        });
        
        // Touch/swipe support
        this.track.addEventListener('touchstart', (e) => {
            this.touchStartX = e.touches[0].clientX;
            this.pauseAutoplay();
        });
        
        this.track.addEventListener('touchend', (e) => {
            this.touchEndX = e.changedTouches[0].clientX;
            this.handleSwipe();
            this.resumeAutoplay();
        });
        
        // Mouse events for desktop
        this.container.addEventListener('mouseenter', () => {
            this.pauseAutoplay();
        });
        
        this.container.addEventListener('mouseleave', () => {
            this.resumeAutoplay();
        });
        
        // Keyboard navigation
        this.container.addEventListener('keydown', (e) => {
            switch (e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    this.goToPrevious();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.goToNext();
                    break;
                case 'Home':
                    e.preventDefault();
                    this.goToSlide(0);
                    break;
                case 'End':
                    e.preventDefault();
                    this.goToSlide(this.maxIndex);
                    break;
            }
        });
        
        // Intersection Observer for lazy loading and analytics
        if (window.IntersectionObserver) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.onCarouselVisible();
                        observer.unobserve(this.container);
                    }
                });
            });
            
            observer.observe(this.container);
        }
    }
    
    handleSwipe() {
        const swipeDistance = this.touchEndX - this.touchStartX;
        const minSwipeDistance = 50;
        
        if (Math.abs(swipeDistance) < minSwipeDistance) return;
        
        if (swipeDistance > 0) {
            this.goToPrevious();
        } else {
            this.goToNext();
        }
    }
    
    goToPrevious() {
        if (this.isAnimating) return;

        if (this.config.infinite && this.currentIndex === 0) {
            // Jump to end for infinite loop
            this.goToSlide(this.maxIndex);
        } else {
            const newIndex = Math.max(0, this.currentIndex - this.slidesPerView);
            this.goToSlide(newIndex);
        }
    }

    goToNext() {
        if (this.isAnimating) return;

        if (this.config.infinite && this.currentIndex >= this.maxIndex) {
            // Jump to beginning for infinite loop
            this.goToSlide(0);
        } else {
            const newIndex = Math.min(this.maxIndex, this.currentIndex + this.slidesPerView);
            this.goToSlide(newIndex);
        }
    }
    
    goToSlide(index) {
        if (this.isAnimating || index === this.currentIndex) return;
        
        this.isAnimating = true;
        this.currentIndex = Math.max(0, Math.min(index, this.maxIndex));
        
        this.updateCarousel();
        
        // Reset animation flag after transition
        setTimeout(() => {
            this.isAnimating = false;
        }, 300);
        
        // Trigger custom event
        this.container.dispatchEvent(new CustomEvent('slideChanged', {
            detail: {
                currentIndex: this.currentIndex,
                totalSlides: this.totalSlides,
                postType: this.config.postType
            }
        }));
    }
    
    updateCarousel() {
        // Calculate transform in pixels for fixed-width slides
        const gap = parseFloat(getComputedStyle(this.container).getPropertyValue('--hph-carousel-gap')) || 16;
        const slideWithGap = this.slideWidth + gap;
        const translateX = -(this.currentIndex * slideWithGap);
        
        // Apply transform
        this.track.style.transform = `translateX(${translateX}px)`;
        
        // Update navigation button states
        this.updateNavigationStates();
        
        // Update dots
        this.updateDotsStates();
        
        // Update slide visibility for accessibility
        this.updateSlideVisibility();
    }
    
    updateNavigationStates() {
        if (this.config.infinite) {
            // Never disable buttons in infinite mode
            if (this.prevButton) {
                this.prevButton.disabled = false;
            }
            if (this.nextButton) {
                this.nextButton.disabled = false;
            }
        } else {
            if (this.prevButton) {
                this.prevButton.disabled = this.currentIndex === 0;
            }

            if (this.nextButton) {
                this.nextButton.disabled = this.currentIndex === this.maxIndex;
            }
        }
    }
    
    updateDotsStates() {
        this.dots.forEach((dot, index) => {
            const slideIndex = index * this.slidesPerView;
            const isActive = slideIndex === this.currentIndex;
            
            dot.classList.toggle('is-active', isActive);
            dot.setAttribute('aria-pressed', isActive.toString());
        });
    }
    
    updateSlideVisibility() {
        this.slides.forEach((slide, index) => {
            const isVisible = index >= this.currentIndex && 
                             index < this.currentIndex + this.slidesPerView;
            
            slide.setAttribute('aria-hidden', (!isVisible).toString());
            
            // Lazy load images in visible slides
            if (isVisible) {
                this.lazyLoadSlideImages(slide);
            }
        });
    }
    
    lazyLoadSlideImages(slide) {
        const lazyImages = slide.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
            img.classList.add('is-loaded');
        });
    }
    
    setupAccessibility() {
        // Add ARIA attributes
        this.container.setAttribute('role', 'region');
        this.container.setAttribute('aria-label', `${this.config.postType} carousel`);
        
        this.track.setAttribute('role', 'group');
        this.track.setAttribute('aria-live', 'polite');
        this.track.setAttribute('aria-atomic', 'false');
        
        // Make container focusable for keyboard navigation
        if (this.container.tabIndex === -1) {
            this.container.tabIndex = 0;
        }
        
        // Update slide ARIA labels
        this.slides.forEach((slide, index) => {
            slide.setAttribute('role', 'group');
            slide.setAttribute('aria-label', `${index + 1} of ${this.totalSlides}`);
        });
    }
    
    startAutoplay() {
        if (!this.config.autoplay) return;
        
        this.autoplayInterval = setInterval(() => {
            this.goToNext(); // Use the enhanced goToNext that handles infinite loop
        }, this.config.autoplaySpeed);
    }
    
    pauseAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    }
    
    resumeAutoplay() {
        if (this.config.autoplay && !this.autoplayInterval) {
            this.startAutoplay();
        }
    }
    
    onCarouselVisible() {
        // Trigger analytics or other actions when carousel becomes visible
        this.container.dispatchEvent(new CustomEvent('carouselVisible', {
            detail: {
                postType: this.config.postType,
                totalSlides: this.totalSlides
            }
        }));
    }
    
    // Public API methods
    destroy() {
        this.pauseAutoplay();
        
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }
        
        // Remove event listeners would go here if needed
        this.container.classList.remove('is-initialized');
    }
    
    refresh() {
        this.slides = Array.from(this.container.querySelectorAll('.hph-carousel__slide'));
        this.totalSlides = this.slides.length;
        this.updateSlidesPerView();
        this.updateCarousel();
    }
    
    goTo(index) {
        this.goToSlide(index);
    }
    
    getCurrentIndex() {
        return this.currentIndex;
    }
    
    getTotalSlides() {
        return this.totalSlides;
    }
}

// Initialize all carousels on page load
function initializeCarousels() {
    const carousels = document.querySelectorAll('.hph-carousel__container');
    const carouselInstances = [];

    carousels.forEach((carousel, index) => {
        try {
            const instance = new HphUniversalCarousel(carousel);
            carouselInstances.push(instance);

            // Store instance on element for external access
            carousel.hphCarousel = instance;
        } catch (error) {
        }
    });

    // Store global reference
    window.HphCarousels = carouselInstances;
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCarousels);
} else {
    // DOM already loaded
    initializeCarousels();
}

// Also initialize if jQuery is available
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(initializeCarousels);
}

// Export class for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HphUniversalCarousel;
} else {
    window.HphUniversalCarousel = HphUniversalCarousel;
}
