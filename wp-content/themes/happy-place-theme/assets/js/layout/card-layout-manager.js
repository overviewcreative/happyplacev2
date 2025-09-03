/**
 * HPH Card Layout Manager
 * JavaScript for card layout switching, filtering, and map integration
 * 
 * @package HappyPlaceTheme
 */

// Global object to hold card layout instances
window.HPH = window.HPH || {};
window.HPH.CardLayoutManager = {};

/**
 * Initialize card layout manager for a container
 * @param {string} containerId - The container ID
 * @param {Object} options - Configuration options
 */
window.HPH.CardLayoutManager.init = function(containerId, options = {}) {
    const container = document.getElementById(containerId);
    
    if (!container) return;
    
    const defaults = {
        defaultLayout: 'grid',
        allowLayoutSwitching: true,
        enableFiltering: true,
        enableSorting: true,
        enableMap: false,
        mapOptions: {},
        animateTransitions: true
    };
    
    const config = Object.assign(defaults, options);
    
    // Layout switcher functionality
    if (config.allowLayoutSwitching) {
        initLayoutSwitcher(container, config);
    }
    
    // Filter functionality
    if (config.enableFiltering) {
        initFiltering(container, config);
    }
    
    // Sort functionality
    if (config.enableSorting) {
        initSorting(container, config);
    }
    
    // View toggle (show/hide filters)
    initViewToggle(container, config);
    
    // Map integration
    if (config.enableMap && config.mapOptions.enabled) {
        initMapIntegration(container, config);
    }
};

/**
 * Initialize layout switcher
 */
function initLayoutSwitcher(container, config) {
    const layoutBtns = container.querySelectorAll('.hph-layout-btn');
    const cardContainer = container.querySelector('.hph-card-container');
    
    if (!cardContainer) return;
    
    layoutBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const newLayout = this.dataset.layout;
            
            // Update active state
            layoutBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Switch layout classes
            cardContainer.className = cardContainer.className.replace(/hph-layout-\w+/g, '');
            cardContainer.classList.add('hph-layout-' + newLayout);
            
            // Animate transition if enabled
            if (config.animateTransitions) {
                cardContainer.classList.add('hph-layout-transitioning');
                setTimeout(() => {
                    cardContainer.classList.remove('hph-layout-transitioning');
                }, 300);
            }
            
            // Store preference
            if (typeof(Storage) !== "undefined") {
                localStorage.setItem('hph_preferred_layout', newLayout);
            }
        });
    });
    
    // Load saved preference
    if (typeof(Storage) !== "undefined") {
        const savedLayout = localStorage.getItem('hph_preferred_layout');
        if (savedLayout) {
            const savedBtn = container.querySelector(`.hph-layout-btn[data-layout="${savedLayout}"]`);
            if (savedBtn) {
                savedBtn.click();
            }
        }
    }
}

/**
 * Initialize filtering functionality
 */
function initFiltering(container, config) {
    const filterBtns = container.querySelectorAll('.hph-filter-btn');
    const cards = container.querySelectorAll('.hph-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filterValue = this.dataset.filter;
            
            // Update active state
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filter cards
            cards.forEach(card => {
                if (filterValue === 'all' || card.dataset.category === filterValue) {
                    card.style.display = 'block';
                    if (config.animateTransitions) {
                        card.style.animation = 'fadeIn 0.3s ease-in-out';
                    }
                } else {
                    if (config.animateTransitions) {
                        card.style.animation = 'fadeOut 0.3s ease-in-out';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
            
            // Update result count
            updateResultCount(container);
        });
    });
}

/**
 * Initialize sorting functionality
 */
function initSorting(container, config) {
    const sortSelect = container.querySelector('.hph-sort-select');
    if (!sortSelect) return;
    
    const cardContainer = container.querySelector('.hph-card-container');
    if (!cardContainer) return;
    
    sortSelect.addEventListener('change', function() {
        const sortBy = this.value;
        const cards = Array.from(cardContainer.querySelectorAll('.hph-card'));
        
        cards.sort((a, b) => {
            let aValue, bValue;
            
            switch (sortBy) {
                case 'name':
                    aValue = a.querySelector('.hph-card-title')?.textContent || '';
                    bValue = b.querySelector('.hph-card-title')?.textContent || '';
                    return aValue.localeCompare(bValue);
                    
                case 'price':
                    aValue = parseFloat(a.dataset.price) || 0;
                    bValue = parseFloat(b.dataset.price) || 0;
                    return aValue - bValue;
                    
                case 'date':
                    aValue = new Date(a.dataset.date) || new Date(0);
                    bValue = new Date(b.dataset.date) || new Date(0);
                    return bValue - aValue; // Newest first
                    
                default:
                    return 0;
            }
        });
        
        // Re-append cards in new order
        cards.forEach(card => cardContainer.appendChild(card));
        
        // Animate if enabled
        if (config.animateTransitions) {
            cardContainer.style.animation = 'fadeIn 0.5s ease-in-out';
        }
    });
}

/**
 * Initialize view toggle
 */
function initViewToggle(container, config) {
    const viewToggle = container.querySelector('.hph-view-toggle');
    const filtersPanel = container.querySelector('.hph-filters-panel');
    
    if (!viewToggle || !filtersPanel) return;
    
    viewToggle.addEventListener('click', function() {
        const isVisible = filtersPanel.style.display !== 'none';
        
        if (isVisible) {
            filtersPanel.style.display = 'none';
            this.setAttribute('aria-expanded', 'false');
            this.textContent = 'Show Filters';
        } else {
            filtersPanel.style.display = 'block';
            this.setAttribute('aria-expanded', 'true');
            this.textContent = 'Hide Filters';
        }
    });
}

/**
 * Initialize map integration
 */
function initMapIntegration(container, config) {
    if (typeof google === 'undefined' || !config.mapOptions.enabled) {
        return;
    }
    
    const mapContainer = container.querySelector('.hph-map-container');
    if (!mapContainer) return;
    
    // Initialize map
    const map = new google.maps.Map(mapContainer, {
        center: { 
            lat: config.mapOptions.centerLat || 40.7128, 
            lng: config.mapOptions.centerLng || -74.0060 
        },
        zoom: config.mapOptions.zoom || 12,
        mapTypeId: config.mapOptions.mapStyle || 'roadmap'
    });
    
    // Add markers
    if (config.mapOptions.markers && Array.isArray(config.mapOptions.markers)) {
        config.mapOptions.markers.forEach((markerData, index) => {
            const marker = new google.maps.Marker({
                position: { lat: markerData.lat, lng: markerData.lng },
                map: map,
                title: markerData.title || `Marker ${index + 1}`
            });
            
            // Add click listener to highlight corresponding card
            marker.addListener('click', function() {
                const card = container.querySelector(`[data-marker-id="marker-${markerData.id}"]`);
                if (card) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    card.classList.add('hph-highlighted');
                    setTimeout(() => card.classList.remove('hph-highlighted'), 2000);
                }
            });
        });
    }
}

/**
 * Update result count display
 */
function updateResultCount(container) {
    const resultCount = container.querySelector('.hph-result-count');
    if (!resultCount) return;
    
    const visibleCards = container.querySelectorAll('.hph-card[style*="display: block"], .hph-card:not([style*="display: none"])').length;
    resultCount.textContent = `${visibleCards} results found`;
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Look for containers with data-card-layout attribute
    const containers = document.querySelectorAll('[data-card-layout]');
    
    containers.forEach(container => {
        const options = JSON.parse(container.dataset.cardLayoutOptions || '{}');
        window.HPH.CardLayoutManager.init(container.id, options);
    });
});