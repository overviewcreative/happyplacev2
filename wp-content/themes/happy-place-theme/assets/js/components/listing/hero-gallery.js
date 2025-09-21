/**
 * Hero Gallery Component - Integrated with HPH Unified System
 *
 * Replaces inline hero gallery JavaScript with proper modular component
 * Includes touch/swipe support and gallery mode functionality
 *
 * @package HappyPlaceTheme
 */

if (window.HPH) {

    HPH.register('heroGallery', function() {
        return {
            instances: new Map(),

            init: function(container = document) {
                const heroElements = container.querySelectorAll('[data-component="hero-gallery"]');
                heroElements.forEach(hero => this.initHero(hero));
            },

            initHero: function(heroElement) {
                const listingId = heroElement.dataset.listingId;
                const galleryId = heroElement.querySelector('.hph-hero__gallery')?.id || 'hero-' + Date.now();

                if (this.instances.has(galleryId)) return this.instances.get(galleryId);

                // Get gallery data from window object
                const dataKey = Object.keys(window).find(key => key.startsWith('heroGalleryData_'));
                const galleryData = dataKey ? window[dataKey] : null;

                if (!galleryData || !galleryData.images.length) {
                    console.warn('Hero gallery: No image data found');
                    return;
                }

                const instance = this.createHeroGallery(heroElement, galleryId, galleryData);
                this.instances.set(galleryId, instance);

                // Initialize touch support
                this.initTouchSupport(heroElement, galleryId);

                // Preload first few images
                this.preloadImages(heroElement, galleryData);

                return instance;
            },

            createHeroGallery: function(heroElement, galleryId, data) {
                return {
                    element: heroElement,
                    galleryId,
                    data,
                    currentIndex: 0,
                    isGalleryMode: false,

                    // Navigation methods
                    next: () => this.navigateHero(galleryId, 1),
                    prev: () => this.navigateHero(galleryId, -1),
                    goTo: (index) => this.setHeroImage(galleryId, index),
                    toggleGalleryMode: () => this.toggleHeroGalleryMode(galleryId),

                    // State methods
                    getCurrentIndex: () => this.instances.get(galleryId)?.currentIndex || 0,
                    isInGalleryMode: () => this.instances.get(galleryId)?.isGalleryMode || false
                };
            },

            initTouchSupport: function(heroElement, galleryId) {
                const heroGallery = heroElement.querySelector('.hph-hero__gallery');
                if (!heroGallery) return;

                let startX = 0;
                let startY = 0;
                let distX = 0;
                let distY = 0;
                let startTime = 0;

                const threshold = 100; // Minimum distance for swipe
                const restraint = 150; // Maximum perpendicular distance
                const allowedTime = 500; // Maximum time for swipe

                heroGallery.addEventListener('touchstart', (e) => {
                    const touchobj = e.changedTouches[0];
                    startX = touchobj.pageX;
                    startY = touchobj.pageY;
                    startTime = new Date().getTime();
                    e.preventDefault();
                });

                heroGallery.addEventListener('touchmove', (e) => {
                    e.preventDefault(); // Prevent scrolling
                });

                heroGallery.addEventListener('touchend', (e) => {
                    const touchobj = e.changedTouches[0];
                    distX = touchobj.pageX - startX;
                    distY = touchobj.pageY - startY;
                    const elapsedTime = new Date().getTime() - startTime;

                    if (elapsedTime <= allowedTime && Math.abs(distX) >= threshold && Math.abs(distY) <= restraint) {
                        if (distX > 0) {
                            this.navigateHero(galleryId, -1); // Swipe right = previous
                        } else {
                            this.navigateHero(galleryId, 1);  // Swipe left = next
                        }
                    }
                });

                // Keyboard navigation
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowLeft') this.navigateHero(galleryId, -1);
                    if (e.key === 'ArrowRight') this.navigateHero(galleryId, 1);
                    if (e.key === 'Escape') {
                        const instance = this.instances.get(galleryId);
                        if (instance && instance.isGalleryMode) {
                            this.toggleHeroGalleryMode(galleryId);
                        }
                    }
                });
            },

            navigateHero: function(galleryId, direction) {
                const instance = this.instances.get(galleryId);
                if (!instance) return;

                const totalImages = instance.data.totalImages;
                let newIndex = instance.currentIndex + direction;

                if (newIndex >= totalImages) newIndex = 0;
                if (newIndex < 0) newIndex = totalImages - 1;

                this.setHeroImage(galleryId, newIndex);
            },

            setHeroImage: function(galleryId, index) {
                const instance = this.instances.get(galleryId);
                if (!instance || !instance.data.images[index]) return;

                instance.currentIndex = index;

                // Update hero images
                const heroImages = instance.element.querySelectorAll('.hph-hero__image');
                heroImages.forEach((img, i) => {
                    img.classList.toggle('active', i === index);

                    // Lazy load background images
                    if (i === index && img.dataset.bg && !img.style.backgroundImage) {
                        img.style.backgroundImage = `url('${img.dataset.bg}')`;
                        img.classList.remove('lazy-bg');
                    }
                });

                // Update thumbnails
                const thumbnails = instance.element.querySelectorAll('.hph-hero__gallery-thumb');
                thumbnails.forEach((thumb, i) => {
                    thumb.classList.toggle('active', i === index);
                });

                // Update counter
                const counter = document.getElementById('current-photo');
                if (counter) counter.textContent = index + 1;

                // Preload next/prev images
                this.preloadAdjacentImages(instance.element, instance.data, index);
            },

            toggleHeroGalleryMode: function(galleryId) {
                const instance = this.instances.get(galleryId);
                if (!instance) return;

                const heroSection = instance.element.closest('.hph-hero');
                if (!heroSection) return;

                // Toggle gallery mode
                heroSection.classList.toggle('hph-hero--gallery-mode');
                instance.isGalleryMode = heroSection.classList.contains('hph-hero--gallery-mode');

                // Update button text
                const buttonText = instance.isGalleryMode ? 'Close Gallery' : 'View Gallery';
                const actionButtonText = instance.isGalleryMode ? 'Close Gallery' : 'View All Photos';

                const navBtn = document.getElementById(`gallery-toggle-btn-nav-${galleryId}`);
                const actionBtn = document.getElementById(`gallery-toggle-btn-action-${galleryId}`);

                if (navBtn) {
                    const textSpan = navBtn.querySelector('.gallery-btn-text');
                    if (textSpan) textSpan.textContent = buttonText;
                }

                if (actionBtn) {
                    const textSpan = actionBtn.querySelector('.gallery-btn-text');
                    if (textSpan) textSpan.textContent = actionButtonText;
                }

                // Auto-exit gallery mode on scroll
                if (instance.isGalleryMode) {
                    this.initScrollExit(galleryId);
                }
            },

            initScrollExit: function(galleryId) {
                let scrollTimeout;

                const handleScroll = () => {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        const instance = this.instances.get(galleryId);
                        if (!instance || !instance.isGalleryMode) return;

                        const heroSection = instance.element.closest('.hph-hero');
                        if (!heroSection) return;

                        const heroRect = heroSection.getBoundingClientRect();
                        if (heroRect.top < -50) {
                            this.toggleHeroGalleryMode(galleryId);
                            window.removeEventListener('scroll', handleScroll);
                        }
                    }, 100);
                };

                window.addEventListener('scroll', handleScroll);
            },

            preloadImages: function(heroElement, data) {
                const heroImages = heroElement.querySelectorAll('.hph-hero__image');
                const preloadCount = Math.min(3, data.totalImages);

                for (let i = 1; i < preloadCount; i++) {
                    const img = heroImages[i];
                    if (img && img.dataset.bg && !img.style.backgroundImage) {
                        img.style.backgroundImage = `url('${img.dataset.bg}')`;
                        img.classList.remove('lazy-bg');
                    }
                }
            },

            preloadAdjacentImages: function(heroElement, data, currentIndex) {
                const heroImages = heroElement.querySelectorAll('.hph-hero__image');

                // Preload next and previous images
                const nextIndex = (currentIndex + 1) % data.totalImages;
                const prevIndex = currentIndex === 0 ? data.totalImages - 1 : currentIndex - 1;

                [nextIndex, prevIndex].forEach(index => {
                    const img = heroImages[index];
                    if (img && img.dataset.bg && !img.style.backgroundImage) {
                        img.style.backgroundImage = `url('${img.dataset.bg}')`;
                        img.classList.remove('lazy-bg');
                    }
                });
            },

            // Global functions for template compatibility
            setupGlobalFunctions: function() {
                // Make functions available globally for onclick handlers
                window.navigateHero = (galleryId, direction) => this.navigateHero(galleryId, direction);
                window.setHeroImage = (galleryId, index) => this.setHeroImage(galleryId, index);
                window.toggleHeroGalleryMode = (galleryId) => this.toggleHeroGalleryMode(galleryId);
            }
        };
    });

    // Auto-initialize hero galleries on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        if (window.HPH && window.HPH.modules.heroGallery) {
            window.HPH.modules.heroGallery.init();
            window.HPH.modules.heroGallery.setupGlobalFunctions();
        }
    });

} else {
    console.warn('HPH Core system not found. Hero gallery requires unified core.');
}

if (window.hphDebug) {
    console.log('HPH Hero Gallery Component Loaded');
}