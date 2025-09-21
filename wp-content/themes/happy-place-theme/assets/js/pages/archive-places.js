/**
 * Places Archive Page JavaScript
 * Handles archive-specific functionality for places
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 */

class HphArchivePlaces {
    constructor() {
        this.favorites = new Set();
        this.init();
    }
    
    init() {
        this.loadFavorites();
        this.bindEvents();
        this.initializeSort();
    }
    
    bindEvents() {
        // Favorite buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.hph-place-card__favorite')) {
                e.preventDefault();
                this.toggleFavorite(e.target.closest('.hph-place-card__favorite'));
            }
        });
        
        // Sort functionality
        const sortControl = document.querySelector('[data-sort-places]');
        if (sortControl) {
            sortControl.addEventListener('change', (e) => {
                this.sortPlaces(e.target.value);
            });
        }
        
        // Distance calculation (if geolocation available)
        if (navigator.geolocation) {
            this.calculateDistances();
        }
        
        // Listen for filter updates
        const filters = document.querySelector('.hph-filters--local');
        if (filters) {
            filters.addEventListener('resultsUpdated', () => {
                this.reinitialize();
            });
            
            filters.addEventListener('viewChanged', (e) => {
                this.handleViewChange(e.detail.view);
            });
        }
    }
    
    toggleFavorite(button) {
        const placeId = button.dataset.placeId;
        const icon = button.querySelector('.hph-icon');
        
        if (this.favorites.has(placeId)) {
            // Remove favorite
            this.favorites.delete(placeId);
            button.classList.remove('is-favorited');
            icon.classList.remove('hph-icon--heart-filled');
            icon.classList.add('hph-icon--heart-outline');
        } else {
            // Add favorite
            this.favorites.add(placeId);
            button.classList.add('is-favorited');
            icon.classList.remove('hph-icon--heart-outline');
            icon.classList.add('hph-icon--heart-filled');
        }
        
        this.saveFavorites();
        this.animateFavorite(button);
    }
    
    animateFavorite(button) {
        button.style.transform = 'scale(1.2)';
        setTimeout(() => {
            button.style.transform = '';
        }, 200);
    }
    
    loadFavorites() {
        const saved = localStorage.getItem('hph_favorite_places');
        if (saved) {
            this.favorites = new Set(JSON.parse(saved));
        }
        
        // Update UI
        this.updateFavoriteButtons();
    }
    
    saveFavorites() {
        localStorage.setItem('hph_favorite_places', JSON.stringify([...this.favorites]));
    }
    
    updateFavoriteButtons() {
        const favoriteButtons = document.querySelectorAll('.hph-place-card__favorite');
        favoriteButtons.forEach(button => {
            const placeId = button.dataset.placeId;
            const icon = button.querySelector('.hph-icon');
            
            if (this.favorites.has(placeId)) {
                button.classList.add('is-favorited');
                icon.classList.remove('hph-icon--heart-outline');
                icon.classList.add('hph-icon--heart-filled');
            }
        });
    }
    
    sortPlaces(sortBy) {
        const grid = document.querySelector('.hph-archive__grid');
        if (!grid) return;
        
        const cards = Array.from(grid.querySelectorAll('.hph-grid__item'));
        
        cards.sort((a, b) => {
            const cardA = a.querySelector('.hph-place-card');
            const cardB = b.querySelector('.hph-place-card');
            
            switch (sortBy) {
                case 'title':
                    const titleA = cardA.querySelector('.hph-place-card__title').textContent;
                    const titleB = cardB.querySelector('.hph-place-card__title').textContent;
                    return titleA.localeCompare(titleB);
                    
                case 'popular':
                    // Sort by favorites first, then by some popularity metric
                    const favA = this.favorites.has(cardA.dataset.placeId) ? 1 : 0;
                    const favB = this.favorites.has(cardB.dataset.placeId) ? 1 : 0;
                    return favB - favA;
                    
                case 'date':
                default:
                    // Default sort (keep current order)
                    return 0;
            }
        });
        
        // Animate the reorder
        grid.style.opacity = '0.5';
        setTimeout(() => {
            cards.forEach(card => grid.appendChild(card));
            grid.style.opacity = '';
        }, 200);
    }
    
    calculateDistances() {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                
                this.updateDistances(userLat, userLng);
            },
            (error) => {
            },
            {
                enableHighAccuracy: false,
                timeout: 10000,
                maximumAge: 600000 // 10 minutes
            }
        );
    }
    
    updateDistances(userLat, userLng) {
        const distanceElements = document.querySelectorAll('.hph-place-card__distance');
        
        distanceElements.forEach(element => {
            const placeLat = parseFloat(element.dataset.lat);
            const placeLng = parseFloat(element.dataset.lng);
            
            if (placeLat && placeLng) {
                const distance = this.calculateDistance(userLat, userLng, placeLat, placeLng);
                const distanceValue = element.querySelector('.distance-value');
                
                if (distanceValue) {
                    if (distance < 1) {
                        distanceValue.textContent = `${Math.round(distance * 1000)}m away`;
                    } else {
                        distanceValue.textContent = `${distance.toFixed(1)}km away`;
                    }
                }
            }
        });
    }
    
    calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Earth's radius in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
            Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    handleViewChange(view) {
        const body = document.body;
        
        // Update body classes for view-specific styling
        body.classList.remove('view-grid', 'view-list', 'view-map');
        body.classList.add(`view-${view}`);
        
        // View-specific initialization
        switch (view) {
            case 'map':
                this.initializeMapView();
                break;
            case 'list':
                this.initializeListView();
                break;
            case 'grid':
                this.initializeGridView();
                break;
        }
    }
    
    initializeMapView() {
        // Map view specific functionality
        const mapSidebar = document.querySelector('.hph-archive__map-sidebar');
        if (mapSidebar) {
            // Sync map markers with sidebar cards
            this.syncMapAndSidebar();
        }
    }
    
    syncMapAndSidebar() {
        // This would integrate with the map component
        // to highlight cards when markers are hovered/clicked
    }
    
    initializeListView() {
        // List view specific functionality
        // Could add infinite scroll, lazy loading, etc.
    }
    
    initializeGridView() {
        // Grid view specific functionality
        // Could add masonry layout, lazy loading, etc.
    }
    
    reinitialize() {
        // Called when content is updated via AJAX
        this.updateFavoriteButtons();
        
        if (navigator.geolocation) {
            this.calculateDistances();
        }
    }
    
    initializeSort() {
        // Initialize sort control with current URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const sortBy = urlParams.get('sort') || 'date';
        
        const sortControl = document.querySelector('[data-sort-places]');
        if (sortControl) {
            sortControl.value = sortBy;
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.body.classList.contains('post-type-archive-local_place')) {
        new HphArchivePlaces();
    }
});

// Export for use in other modules
window.HphArchivePlaces = HphArchivePlaces;
