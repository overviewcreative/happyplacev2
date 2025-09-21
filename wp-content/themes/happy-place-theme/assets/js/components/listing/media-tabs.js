/**
 * Media Tabs JavaScript
 * Simple tab functionality for media section
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all media tabs
    const mediaTabs = document.querySelectorAll('.hph-media-tab');
    
    mediaTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const mediaType = this.dataset.mediaType;
            const galleryId = this.dataset.galleryId;
            
            if (!mediaType || !galleryId) return;
            
            // Update tab states
            const allTabs = document.querySelectorAll(`[data-gallery-id="${galleryId}"].hph-media-tab`);
            allTabs.forEach(t => t.classList.remove('hph-media-tab--active'));
            this.classList.add('hph-media-tab--active');
            
            // Update content visibility
            const allContent = document.querySelectorAll(`[data-gallery-id="${galleryId}"][data-media-content]`);
            allContent.forEach(content => {
                content.classList.remove('hph-media-content--active');
            });
            
            const targetContent = document.querySelector(`[data-gallery-id="${galleryId}"][data-media-content="${mediaType}"]`);
            if (targetContent) {
                targetContent.classList.add('hph-media-content--active');
            }
            
            // Handle thumbnail strip visibility (only show for photos)
            const thumbnailStrip = document.querySelector(`[data-gallery-id="${galleryId}"].hph-thumbnail-strip`);
            if (thumbnailStrip) {
                thumbnailStrip.style.display = mediaType === 'photos' ? 'block' : 'none';
            }
            
            console.log(`Switched to ${mediaType} for gallery ${galleryId}`);
        });
    });
    
    // Gallery navigation
    const prevButtons = document.querySelectorAll('[data-gallery-prev]');
    const nextButtons = document.querySelectorAll('[data-gallery-next]');
    
    prevButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const galleryId = this.dataset.galleryPrev;
            navigateGallery(galleryId, -1);
        });
    });
    
    nextButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const galleryId = this.dataset.galleryNext;
            navigateGallery(galleryId, 1);
        });
    });
    
    // Thumbnail clicks
    const thumbnails = document.querySelectorAll('.hph-thumbnail');
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const galleryId = this.dataset.galleryId;
            const index = parseInt(this.dataset.thumbnailIndex);
            goToSlide(galleryId, index);
        });
    });
    
    function navigateGallery(galleryId, direction) {
        const track = document.querySelector(`[data-gallery-track="${galleryId}"]`);
        const slides = track ? track.querySelectorAll('.hph-carousel-slide') : [];
        
        if (slides.length === 0) return;
        
        // Get current slide
        let currentIndex = parseInt(track.dataset.currentIndex || '0');
        
        // Calculate next index
        if (direction > 0) {
            currentIndex = currentIndex >= slides.length - 1 ? 0 : currentIndex + 1;
        } else {
            currentIndex = currentIndex <= 0 ? slides.length - 1 : currentIndex - 1;
        }
        
        goToSlide(galleryId, currentIndex);
    }
    
    function goToSlide(galleryId, index) {
        const track = document.querySelector(`[data-gallery-track="${galleryId}"]`);
        if (!track) return;
        
        const slides = track.querySelectorAll('.hph-carousel-slide');
        if (!slides[index]) return;
        
        // Update track position
        const translateX = -(index * 100);
        track.style.transform = `translateX(${translateX}%)`;
        track.dataset.currentIndex = index;
        
        // Update thumbnails
        const thumbnails = document.querySelectorAll(`[data-gallery-id="${galleryId}"].hph-thumbnail`);
        thumbnails.forEach((thumb, i) => {
            thumb.classList.toggle('hph-thumbnail--active', i === index);
        });
    }
    
    // Initialize first slide
    const tracks = document.querySelectorAll('[data-gallery-track]');
    tracks.forEach(track => {
        track.dataset.currentIndex = '0';
        track.style.transform = 'translateX(0%)';
    });
    
    console.log('Media tabs initialized');
});
