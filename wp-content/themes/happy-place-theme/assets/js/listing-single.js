/**
 * Single Listing Scripts
 * File: /assets/js/listing-single.js
 * 
 * @package HappyPlaceTheme
 */

(function() {
    'use strict';

    // ============================================
    // GALLERY FUNCTIONALITY
    // ============================================
    
    let currentImageIndex = 0;
    let galleryImages = [];

    /**
     * Initialize gallery data from PHP
     */
    window.initGallery = function(images) {
        galleryImages = images;
    };

    /**
     * Change hero image when thumbnail clicked
     */
    window.changeHeroImage = function(url, thumb) {
        const heroSlide = document.querySelector('.hph-hero__slide');
        if (heroSlide) {
            heroSlide.style.backgroundImage = `url('${url}')`;
            
            // Update active thumbnail
            document.querySelectorAll('.hph-hero__thumb').forEach(t => {
                t.classList.remove('active');
            });
            thumb.classList.add('active');
        }
    };

    /**
     * Open gallery modal
     */
    window.openGalleryModal = function() {
        const modal = document.getElementById('gallery-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            galleryGoTo(0);
        }
    };

    /**
     * Close gallery modal
     */
    window.closeGalleryModal = function() {
        const modal = document.getElementById('gallery-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    /**
     * Navigate to specific image in gallery
     */
    window.galleryGoTo = function(index) {
        if (index < 0 || index >= galleryImages.length) return;
        
        currentImageIndex = index;
        const image = galleryImages[index];
        
        const modalImage = document.getElementById('modal-image');
        const counter = document.getElementById('image-counter');
        
        if (modalImage) {
            modalImage.src = image.url;
            modalImage.alt = image.alt || '';
        }
        
        if (counter) {
            counter.textContent = index + 1;
        }
        
        // Update active thumbnail
        document.querySelectorAll('.hph-gallery-modal__thumb').forEach((thumb, i) => {
            thumb.classList.toggle('active', i === index);
        });
    };

    /**
     * Gallery next image
     */
    window.galleryNext = function() {
        galleryGoTo((currentImageIndex + 1) % galleryImages.length);
    };

    /**
     * Gallery previous image
     */
    window.galleryPrevious = function() {
        galleryGoTo((currentImageIndex - 1 + galleryImages.length) % galleryImages.length);
    };

    // ============================================
    // PROPERTY DESCRIPTION
    // ============================================
    
    /**
     * Toggle description expand/collapse
     */
    window.toggleDescription = function() {
        const content = document.getElementById('property-description');
        const btn = event.currentTarget;
        
        if (!content || !btn) return;
        
        const readMore = btn.querySelector('.read-more');
        const readLess = btn.querySelector('.read-less');
        const icon = btn.querySelector('i');
        
        content.classList.toggle('expanded');
        
        if (content.classList.contains('expanded')) {
            if (readMore) readMore.style.display = 'none';
            if (readLess) readLess.style.display = 'inline';
            if (icon) icon.style.transform = 'rotate(180deg)';
        } else {
            if (readMore) readMore.style.display = 'inline';
            if (readLess) readLess.style.display = 'none';
            if (icon) icon.style.transform = 'rotate(0)';
        }
    };

    // ============================================
    // MAP FUNCTIONALITY
    // ============================================
    
    /**
     * Open Google Street View
     */
    window.openStreetView = function(lat, lng) {
        window.open(
            `https://www.google.com/maps/@${lat},${lng},3a,75y,90t/data=!3m6!1e1!3m4!1s!2e0!7i16384!8i8192`,
            '_blank'
        );
    };

    /**
     * Search nearby places
     */
    window.searchNearby = function(type, lat, lng) {
        window.open(
            `https://www.google.com/maps/search/${type}/@${lat},${lng},15z`,
            '_blank'
        );
    };

    /**
     * Initialize property map
     */
    window.initPropertyMap = function(mapId, lat, lng, address) {
        const mapElement = document.getElementById(mapId);
        if (!mapElement) return;

        // Check for Mapbox
        if (typeof mapboxgl !== 'undefined' && window.MAPBOX_TOKEN) {
            mapboxgl.accessToken = window.MAPBOX_TOKEN;
            const map = new mapboxgl.Map({
                container: mapId,
                style: 'mapbox://styles/mapbox/streets-v11',
                center: [lng, lat],
                zoom: 15
            });
            
            // Add marker
            new mapboxgl.Marker()
                .setLngLat([lng, lat])
                .setPopup(new mapboxgl.Popup().setHTML(`<p>${address}</p>`))
                .addTo(map);
                
            // Add controls
            map.addControl(new mapboxgl.NavigationControl());
        }
        // Check for Google Maps
        else if (typeof google !== 'undefined') {
            const map = new google.maps.Map(mapElement, {
                center: { lat: lat, lng: lng },
                zoom: 15
            });
            
            const marker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: map,
                title: address
            });
            
            const infoWindow = new google.maps.InfoWindow({
                content: `<p>${address}</p>`
            });
            
            marker.addListener('click', function() {
                infoWindow.open(map, marker);
            });
        }
        // Fallback to static display
        else {
            mapElement.innerHTML = `
                <div class="hph-map-static">
                    <i class="fas fa-map-marked-alt"></i>
                    <p>${address}</p>
                    <a href="https://www.google.com/maps?q=${lat},${lng}" 
                       target="_blank" 
                       class="hph-btn hph-btn--primary">
                        View on Google Maps
                    </a>
                </div>
            `;
        }
    };

    // ============================================
    // LISTING ACTIONS
    // ============================================
    
    /**
     * Share listing
     */
    window.shareListing = function() {
        if (navigator.share) {
            navigator.share({
                title: document.title,
                url: window.location.href
            }).catch(err => console.log('Error sharing:', err));
        } else {
            // Fallback to copy URL
            copyToClipboard(window.location.href);
            showNotification('Link copied to clipboard!');
        }
    };

    /**
     * Save/favorite listing
     */
    window.saveListing = function(listingId) {
        const btn = event.currentTarget;
        const icon = btn.querySelector('i');
        
        // Check if user is logged in
        if (!window.USER_ID) {
            showNotification('Please log in to save listings');
            return;
        }
        
        // Toggle favorite state
        const isFavorited = icon.classList.contains('fas');
        
        fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: isFavorited ? 'remove_favorite' : 'add_favorite',
                listing_id: listingId,
                nonce: window.NONCE
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                icon.classList.toggle('fas');
                icon.classList.toggle('far');
                showNotification(isFavorited ? 'Removed from favorites' : 'Added to favorites');
            }
        })
        .catch(err => console.error('Error saving listing:', err));
    };

    /**
     * Print listing
     */
    window.printListing = function() {
        window.print();
    };

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================
    
    /**
     * Copy text to clipboard
     */
    function copyToClipboard(text) {
        const temp = document.createElement('input');
        document.body.appendChild(temp);
        temp.value = text;
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
    }

    /**
     * Show notification message
     */
    function showNotification(message, type = 'success') {
        // Remove any existing notifications
        const existing = document.querySelector('.hph-notification');
        if (existing) existing.remove();
        
        // Create new notification
        const notification = document.createElement('div');
        notification.className = `hph-notification hph-notification--${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => notification.classList.add('show'), 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    
    document.addEventListener('DOMContentLoaded', function() {
        
        // Keyboard navigation for gallery
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('gallery-modal');
            if (modal && modal.style.display !== 'none') {
                if (e.key === 'ArrowRight') galleryNext();
                if (e.key === 'ArrowLeft') galleryPrevious();
                if (e.key === 'Escape') closeGalleryModal();
            }
        });
        
        // Click outside modal to close
        const modal = document.getElementById('gallery-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeGalleryModal();
                }
            });
        }
        
        // Initialize all maps on the page
        document.querySelectorAll('.hph-map').forEach(mapEl => {
            const lat = parseFloat(mapEl.dataset.lat);
            const lng = parseFloat(mapEl.dataset.lng);
            const address = mapEl.dataset.address || '';
            
            if (lat && lng) {
                initPropertyMap(mapEl.id, lat, lng, address);
            }
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Lazy load images in gallery
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img.lazy').forEach(img => {
                imageObserver.observe(img);
            });
        }
    });

    // ============================================
    // PRINT STYLES
    // ============================================
    
    window.addEventListener('beforeprint', function() {
        // Expand all collapsed content
        document.querySelectorAll('.hph-description-content').forEach(el => {
            el.classList.add('expanded');
        });
        
        // Hide interactive elements
        document.querySelectorAll('.hph-read-more-btn, .hph-hero__actions').forEach(el => {
            el.style.display = 'none';
        });
    });
    
    window.addEventListener('afterprint', function() {
        // Restore collapsed state
        document.querySelectorAll('.hph-description-content').forEach(el => {
            el.classList.remove('expanded');
        });
        
        // Show interactive elements
        document.querySelectorAll('.hph-read-more-btn, .hph-hero__actions').forEach(el => {
            el.style.display = '';
        });
    });

})();