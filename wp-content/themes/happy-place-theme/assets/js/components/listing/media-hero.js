/**
 * Media Hero Component JavaScript
 * File: media-hero.js
 * 
 * Handles all interactions for the unified media hero section
 * Gallery carousel, lightbox, tabs, and slideshow functionality
 * 
 * @package HappyPlaceTheme
 */

class MediaHeroComponent {
    constructor(containerId) {
        this.containerId = containerId;
        this.container = document.querySelector(`[data-component="media-hero"][data-listing-id="${containerId}"]`);
        this.uniqueId = null;
        this.mediaData = null;
        this.currentIndex = 0;
        this.isSlideshow = false;
        this.slideshowInterval = null;
        this.lightboxOpen = false;
        
        if (this.container) {
            this.init();
        }
    }
    
    init() {
        // Find the unique ID from the media data
        this.findUniqueId();
        if (!this.uniqueId || !this.mediaData) {
            console.warn('MediaHero: Could not find media data for', this.containerId);
            return;
        }
        
        this.setupEventListeners();
        this.setupKeyboardNavigation();
        this.setupTouchGestures();
        
        // Initialize thumbnail states
        this.updateThumbnails();
        
        console.log('MediaHero initialized for', this.uniqueId);
    }
    
    findUniqueId() {
        // Look for the unique ID in the window media data variables
        for (let key in window) {
            if (key.startsWith('mediaHeroData_')) {
                const testId = key.replace('mediaHeroData_', '');
                const testData = window[key];
                if (testData && testData.images) {
                    this.uniqueId = testId;
                    this.mediaData = testData;
                    break;
                }
            }
        }
    }
    
    setupEventListeners() {
        // Tab switching
        const tabs = this.container.querySelectorAll('[data-media-tab]');
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => this.switchTab(e.target.closest('[data-media-tab]')));
        });
        
        // Carousel navigation
        const prevBtn = this.container.querySelector('.hph-carousel-btn--prev');
        const nextBtn = this.container.querySelector('.hph-carousel-btn--next');
        
        if (prevBtn) prevBtn.addEventListener('click', () => this.previousImage());
        if (nextBtn) nextBtn.addEventListener('click', () => this.nextImage());
        
        // Thumbnail clicks
        const thumbnails = this.container.querySelectorAll('.hph-thumbnail');
        thumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', () => this.goToImage(index));
        });
        
        // Carousel slides click for lightbox
        const slides = this.container.querySelectorAll('.hph-carousel-slide');
        slides.forEach((slide, index) => {
            slide.addEventListener('click', () => this.openLightbox(index));
        });
        
        // Action buttons
        const expandBtn = this.container.querySelector('[title="View Fullscreen"]');
        const slideshowBtn = this.container.querySelector('[title="Start Slideshow"]');
        
        if (expandBtn) expandBtn.addEventListener('click', () => this.openLightbox(this.currentIndex));
        if (slideshowBtn) slideshowBtn.addEventListener('click', () => this.toggleSlideshow());
        
        // Lightbox controls
        this.setupLightboxControls();
    }
    
    setupLightboxControls() {
        const lightbox = document.getElementById(`media-lightbox-${this.uniqueId}`);
        if (!lightbox) return;
        
        // Close button
        const closeBtn = lightbox.querySelector('[onclick*="closeMediaLightbox"]');
        if (closeBtn) {
            closeBtn.removeAttribute('onclick');
            closeBtn.addEventListener('click', () => this.closeLightbox());
        }
        
        // Navigation buttons
        const prevBtn = lightbox.querySelector('[onclick*="previousLightboxImage"]');
        const nextBtn = lightbox.querySelector('[onclick*="nextLightboxImage"]');
        
        if (prevBtn) {
            prevBtn.removeAttribute('onclick');
            prevBtn.addEventListener('click', () => this.previousLightboxImage());
        }
        
        if (nextBtn) {
            nextBtn.removeAttribute('onclick');
            nextBtn.addEventListener('click', () => this.nextLightboxImage());
        }
        
        // Click outside to close
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                this.closeLightbox();
            }
        });
    }
    
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            if (!this.lightboxOpen) return;
            
            switch (e.key) {
                case 'Escape':
                    this.closeLightbox();
                    break;
                case 'ArrowLeft':
                    this.previousLightboxImage();
                    break;
                case 'ArrowRight':
                    this.nextLightboxImage();
                    break;
                case ' ':
                    e.preventDefault();
                    this.toggleSlideshow();
                    break;
            }
        });
    }
    
    setupTouchGestures() {
        const carousel = this.container.querySelector('.hph-carousel-viewport');
        if (!carousel) return;
        
        let startX = 0;
        let startY = 0;
        let currentX = 0;
        let currentY = 0;
        let isDragging = false;
        
        carousel.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isDragging = true;
        });
        
        carousel.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            
            currentX = e.touches[0].clientX;
            currentY = e.touches[0].clientY;
            
            // Prevent default only for horizontal swipes
            const deltaX = Math.abs(currentX - startX);
            const deltaY = Math.abs(currentY - startY);
            
            if (deltaX > deltaY && deltaX > 10) {
                e.preventDefault();
            }
        });
        
        carousel.addEventListener('touchend', () => {
            if (!isDragging) return;
            
            const deltaX = currentX - startX;
            const threshold = 50;
            
            if (Math.abs(deltaX) > threshold) {
                if (deltaX > 0) {
                    this.previousImage();
                } else {
                    this.nextImage();
                }
            }
            
            isDragging = false;
        });
    }
    
    switchTab(tab) {
        const targetTab = tab.dataset.mediaTab;
        
        // Update tab states
        this.container.querySelectorAll('[data-media-tab]').forEach(t => {
            t.classList.remove('hph-media-tab--active');
        });
        tab.classList.add('hph-media-tab--active');
        
        // Update content sections
        this.container.querySelectorAll('[data-media-content]').forEach(content => {
            content.classList.remove('hph-media-section--active');
        });
        
        const targetContent = this.container.querySelector(`[data-media-content="${targetTab}"]`);
        if (targetContent) {
            targetContent.classList.add('hph-media-section--active');
        }
        
        // Show/hide thumbnails for photos tab
        const thumbnailStrip = this.container.querySelector('.hph-thumbnail-strip');
        if (thumbnailStrip) {
            thumbnailStrip.style.display = targetTab === 'photos' ? 'block' : 'none';
        }
        
        // Update action buttons visibility
        const actionButtons = this.container.querySelector('.hph-media-actions');
        if (actionButtons) {
            actionButtons.style.display = targetTab === 'photos' ? 'flex' : 'none';
        }
    }
    
    goToImage(index) {
        if (!this.mediaData.images[index]) return;
        
        this.currentIndex = index;
        this.updateCarousel();
        this.updateThumbnails();
    }
    
    nextImage() {
        this.currentIndex = (this.currentIndex + 1) % this.mediaData.totalImages;
        this.updateCarousel();
        this.updateThumbnails();
    }
    
    previousImage() {
        this.currentIndex = this.currentIndex === 0 ? this.mediaData.totalImages - 1 : this.currentIndex - 1;
        this.updateCarousel();
        this.updateThumbnails();
    }
    
    updateCarousel() {
        const track = this.container.querySelector(`[data-carousel-track="${this.uniqueId}"]`);
        if (!track || this.mediaData.totalImages === 0) return;
        
        const translateX = -(this.currentIndex * (100 / this.mediaData.totalImages));
        track.style.transform = `translateX(${translateX}%)`;
        
        // Update carousel data
        this.mediaData.currentIndex = this.currentIndex;
    }
    
    updateThumbnails() {
        const thumbnails = this.container.querySelectorAll(`.hph-thumbnail[data-gallery-id="${this.uniqueId}"]`);
        thumbnails.forEach((thumb, index) => {
            thumb.classList.toggle('hph-thumbnail--active', index === this.currentIndex);
        });
    }
    
    openLightbox(index = 0) {
        if (!this.mediaData.images[index]) return;
        
        this.currentIndex = index;
        this.lightboxOpen = true;
        
        const lightbox = document.getElementById(`media-lightbox-${this.uniqueId}`);
        const image = document.getElementById(`lightbox-image-${this.uniqueId}`);
        const counter = document.getElementById(`lightbox-counter-${this.uniqueId}`);
        
        if (lightbox && image) {
            image.src = this.mediaData.images[index].url;
            image.alt = this.mediaData.images[index].alt;
            
            if (counter) {
                counter.textContent = `${index + 1} of ${this.mediaData.totalImages}`;
            }
            
            lightbox.classList.remove('hph-hidden');
            document.body.style.overflow = 'hidden';
            
            // Focus management for accessibility
            const closeBtn = lightbox.querySelector('button');
            if (closeBtn) closeBtn.focus();
        }
    }
    
    closeLightbox() {
        const lightbox = document.getElementById(`media-lightbox-${this.uniqueId}`);
        if (lightbox) {
            lightbox.classList.add('hph-hidden');
            document.body.style.overflow = '';
            this.lightboxOpen = false;
        }
    }
    
    nextLightboxImage() {
        this.currentIndex = (this.currentIndex + 1) % this.mediaData.totalImages;
        this.updateLightboxImage();
    }
    
    previousLightboxImage() {
        this.currentIndex = this.currentIndex === 0 ? this.mediaData.totalImages - 1 : this.currentIndex - 1;
        this.updateLightboxImage();
    }
    
    updateLightboxImage() {
        const image = document.getElementById(`lightbox-image-${this.uniqueId}`);
        const counter = document.getElementById(`lightbox-counter-${this.uniqueId}`);
        
        if (image && this.mediaData.images[this.currentIndex]) {
            // Fade effect
            image.style.opacity = '0';
            
            setTimeout(() => {
                image.src = this.mediaData.images[this.currentIndex].url;
                image.alt = this.mediaData.images[this.currentIndex].alt;
                image.style.opacity = '1';
                
                if (counter) {
                    counter.textContent = `${this.currentIndex + 1} of ${this.mediaData.totalImages}`;
                }
            }, 150);
        }
    }
    
    toggleSlideshow() {
        const btn = this.container.querySelector('[title*="Slideshow"]');
        if (!btn) return;
        
        const icon = btn.querySelector('i');
        
        if (this.isSlideshow) {
            // Stop slideshow
            clearInterval(this.slideshowInterval);
            this.isSlideshow = false;
            if (icon) icon.className = 'fas fa-play';
            btn.title = 'Start Slideshow';
        } else {
            // Start slideshow
            this.isSlideshow = true;
            if (icon) icon.className = 'fas fa-pause';
            btn.title = 'Stop Slideshow';
            
            this.slideshowInterval = setInterval(() => {
                this.nextImage();
            }, 3000);
        }
    }
    
    destroy() {
        // Clean up intervals
        if (this.slideshowInterval) {
            clearInterval(this.slideshowInterval);
        }
        
        // Remove event listeners (if we stored references)
        // This would be implemented if needed for dynamic component removal
        
        console.log('MediaHero destroyed for', this.uniqueId);
    }
}

// Auto-initialize components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const mediaHeroElements = document.querySelectorAll('[data-component="media-hero"]');
    
    mediaHeroElements.forEach(element => {
        const listingId = element.dataset.listingId;
        if (listingId) {
            new MediaHeroComponent(listingId);
        }
    });
});

// Global functions for backward compatibility with inline handlers
window.MediaHeroComponent = MediaHeroComponent;

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MediaHeroComponent;
}
