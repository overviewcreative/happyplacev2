/**
 * HPH Hero Section JavaScript
 * 
 * Handles hero section interactive features including:
 * - Video playback control
 * - Parallax effects
 * - Scroll animations
 * - Button interactions
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function() {
    'use strict';

    // Hero Section Controller
    class HeroSection {
        constructor(element) {
            this.element = element;
            this.style = element.dataset.heroStyle;
            this.video = element.querySelector('.hph-hero-video');
            this.scrollIndicator = element.querySelector('.hph-hero-scroll');
            this.animatedElements = element.querySelectorAll('[class*="hph-animate-"]');
            this.parallaxEnabled = element.classList.contains('hph-hero-parallax');
            
            this.init();
        }

        init() {
            // Initialize video if present
            if (this.video) {
                this.initVideo();
            }

            // Initialize parallax effect
            if (this.parallaxEnabled && !this.isMobile()) {
                this.initParallax();
            }

            // Initialize scroll indicator
            if (this.scrollIndicator) {
                this.initScrollIndicator();
            }

            // Initialize animations
            if (this.animatedElements.length > 0) {
                this.initAnimations();
            }

            // Initialize property-specific features
            if (this.style === 'property') {
                this.initPropertyFeatures();
            }
        }

        initVideo() {
            // Ensure video plays on mobile devices
            this.video.addEventListener('loadedmetadata', () => {
                this.video.play().catch(e => {
                    console.log('Video autoplay prevented:', e);
                });
            });

            // Pause video when not in viewport for performance
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.video.play();
                    } else {
                        this.video.pause();
                    }
                });
            }, { threshold: 0.25 });

            observer.observe(this.video);
        }

        initParallax() {
            let ticking = false;

            const updateParallax = () => {
                const scrolled = window.pageYOffset;
                const speed = 0.5;
                const yPos = -(scrolled * speed);
                
                this.element.style.backgroundPosition = `center ${yPos}px`;
                ticking = false;
            };

            const requestTick = () => {
                if (!ticking) {
                    window.requestAnimationFrame(updateParallax);
                    ticking = true;
                }
            };

            window.addEventListener('scroll', requestTick);
            window.addEventListener('resize', requestTick);
        }

        initScrollIndicator() {
            this.scrollIndicator.addEventListener('click', (e) => {
                e.preventDefault();
                const heroHeight = this.element.offsetHeight;
                const targetPosition = this.element.offsetTop + heroHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            });

            // Hide scroll indicator when scrolled
            let scrollTimeout;
            const hideOnScroll = () => {
                this.scrollIndicator.style.opacity = '0.75';
                clearTimeout(scrollTimeout);
                
                scrollTimeout = setTimeout(() => {
                    if (window.pageYOffset > 100) {
                        this.scrollIndicator.style.opacity = '0';
                    }
                }, 150);
            };

            window.addEventListener('scroll', hideOnScroll);
        }

        initAnimations() {
            // Use Intersection Observer for scroll-triggered animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -10% 0px'
            };

            const animationObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                        animationObserver.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            this.animatedElements.forEach(element => {
                // Initially pause animations
                element.style.animationPlayState = 'paused';
                animationObserver.observe(element);
            });
        }

        initPropertyFeatures() {
            // Initialize property gallery if exists
            const galleryButton = this.element.querySelector('.hph-hero-gallery-btn');
            if (galleryButton) {
                galleryButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.openPropertyGallery();
                });
            }

            // Initialize favorite button
            const favoriteBtn = this.element.querySelector('.hph-hero-favorite-btn');
            if (favoriteBtn) {
                favoriteBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleFavorite(favoriteBtn);
                });
            }
        }

        openPropertyGallery() {
            // Trigger custom event for gallery
            const event = new CustomEvent('hph:gallery:open', {
                detail: { 
                    listingId: this.element.dataset.listingId 
                }
            });
            document.dispatchEvent(event);
        }

        toggleFavorite(button) {
            button.classList.toggle('is-favorite');
            
            const listingId = this.element.dataset.listingId;
            const isFavorite = button.classList.contains('is-favorite');
            
            // Trigger custom event
            const event = new CustomEvent('hph:favorite:toggle', {
                detail: { 
                    listingId: listingId,
                    isFavorite: isFavorite
                }
            });
            document.dispatchEvent(event);

            // Update button text/icon
            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('far');
                icon.classList.toggle('fas');
            }
        }

        isMobile() {
            return window.innerWidth < 768;
        }
    }

    // Initialize on DOM ready
    function initHeroSections() {
        const heroSections = document.querySelectorAll('.hph-hero');
        heroSections.forEach(section => {
            new HeroSection(section);
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroSections);
    } else {
        initHeroSections();
    }

    // Re-initialize on dynamic content load (for AJAX)
    document.addEventListener('hph:content:loaded', initHeroSections);

    // Export for use in other modules
    window.HeroSection = HeroSection;

})();

/**
 * Additional Hero Utilities
 */

// Smooth scroll polyfill for older browsers
if (!('scrollBehavior' in document.documentElement.style)) {
    const smoothScrollTo = (targetY, duration = 500) => {
        const startY = window.pageYOffset;
        const difference = targetY - startY;
        const startTime = performance.now();

        const step = () => {
            const progress = (performance.now() - startTime) / duration;
            const amount = easeInOutCubic(progress);
            
            window.scrollTo(0, startY + amount * difference);
            
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };

        const easeInOutCubic = (t) => {
            return t < 0.5
                ? 4 * t * t * t
                : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1;
        };

        step();
    };

    // Override scrollTo behavior
    const originalScrollTo = window.scrollTo;
    window.scrollTo = function(options) {
        if (options && typeof options === 'object' && options.behavior === 'smooth') {
            smoothScrollTo(options.top || 0);
        } else {
            originalScrollTo.apply(window, arguments);
        }
    };
}
