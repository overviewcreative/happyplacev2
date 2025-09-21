/**
 * Single Place Page JavaScript
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 */

class HphSinglePlace {
    constructor() {
        this.placeId = this.getPlaceId();
        this.init();
    }
    
    init() {
        this.initMap();
        this.bindEvents();
        this.initGallery();
        this.loadFavoriteState();
    }
    
    bindEvents() {
        // Favorite button
        const favoriteBtn = document.querySelector('.hph-place-favorite');
        if (favoriteBtn) {
            favoriteBtn.addEventListener('click', () => {
                this.toggleFavorite();
            });
        }
        
        // Share buttons
        const copyBtn = document.querySelector('[data-copy-url]');
        if (copyBtn) {
            copyBtn.addEventListener('click', (e) => {
                this.copyToClipboard(e.target.dataset.copyUrl);
            });
        }
    }
    
    initMap() {
        const mapContainer = document.getElementById('place-map');
        if (!mapContainer || !window.mapboxgl) return;
        
        const lat = parseFloat(mapContainer.dataset.lat);
        const lng = parseFloat(mapContainer.dataset.lng);
        const title = mapContainer.dataset.title;
        const address = mapContainer.dataset.address;
        
        if (!lat || !lng) return;
        
        const map = new mapboxgl.Map({
            container: 'place-map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [lng, lat],
            zoom: 15
        });
        
        // Add marker
        new mapboxgl.Marker()
            .setLngLat([lng, lat])
            .setPopup(new mapboxgl.Popup().setHTML(`
                <div class="hph-map-popup">
                    <h4>${title}</h4>
                    ${address ? `<p>${address}</p>` : ''}
                </div>
            `))
            .addTo(map);
        
        // Add navigation controls
        map.addControl(new mapboxgl.NavigationControl());
    }
    
    initGallery() {
        // Initialize Fancybox for gallery if available
        if (window.Fancybox) {
            Fancybox.bind('[data-fancybox="place-gallery"]', {
                Toolbar: {
                    display: ["zoom", "slideshow", "thumbs", "close"]
                }
            });
        }
    }
    
    toggleFavorite() {
        const favoriteBtn = document.querySelector('.hph-place-favorite');
        const icon = favoriteBtn.querySelector('.hph-icon');
        
        let favorites = JSON.parse(localStorage.getItem('hph_favorite_places') || '[]');
        
        if (favorites.includes(this.placeId)) {
            // Remove from favorites
            favorites = favorites.filter(id => id !== this.placeId);
            favoriteBtn.classList.remove('is-favorited');
            icon.classList.remove('hph-icon--heart-filled');
            icon.classList.add('hph-icon--heart-outline');
            favoriteBtn.querySelector('span').textContent = 'Save';
        } else {
            // Add to favorites
            favorites.push(this.placeId);
            favoriteBtn.classList.add('is-favorited');
            icon.classList.remove('hph-icon--heart-outline');
            icon.classList.add('hph-icon--heart-filled');
            favoriteBtn.querySelector('span').textContent = 'Saved';
        }
        
        localStorage.setItem('hph_favorite_places', JSON.stringify(favorites));
        
        // Animation
        favoriteBtn.style.transform = 'scale(1.1)';
        setTimeout(() => {
            favoriteBtn.style.transform = '';
        }, 200);
    }
    
    loadFavoriteState() {
        const favorites = JSON.parse(localStorage.getItem('hph_favorite_places') || '[]');
        const favoriteBtn = document.querySelector('.hph-place-favorite');
        
        if (favorites.includes(this.placeId) && favoriteBtn) {
            const icon = favoriteBtn.querySelector('.hph-icon');
            favoriteBtn.classList.add('is-favorited');
            icon.classList.remove('hph-icon--heart-outline');
            icon.classList.add('hph-icon--heart-filled');
            favoriteBtn.querySelector('span').textContent = 'Saved';
        }
    }
    
    copyToClipboard(url) {
        navigator.clipboard.writeText(url).then(() => {
            const btn = document.querySelector('[data-copy-url]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="hph-icon hph-icon--check"></i> Copied!';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        });
    }
    
    getPlaceId() {
        const placeCard = document.querySelector('[data-place-id]');
        return placeCard ? placeCard.dataset.placeId : null;
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.body.classList.contains('single-local_place')) {
        new HphSinglePlace();
    }
});

window.HphSinglePlace = HphSinglePlace;
