/**
 * Local Filters Component
 * Handles filtering functionality for places and events
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 */

class HphLocalFilters {
    constructor(element) {
        this.element = element;
        this.form = element.querySelector('.hph-filters__form');
        this.postType = element.dataset.postType;
        this.viewToggle = element.querySelector('[data-view-toggle]');
        this.activeView = 'grid';
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeFromURL();
        this.setupViewToggle();
    }
    
    bindEvents() {
        // Form submission
        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                this.handleFormSubmit(e);
            });
        }
        
        // Real-time filtering for select changes
        const selects = this.element.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', () => {
                this.applyFilters();
            });
        });
        
        // Checkbox changes
        const checkboxes = this.element.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.applyFilters();
            });
        });
        
        // Search input with debouncing
        const searchInput = this.element.querySelector('input[type="search"]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.applyFilters();
                }, 500);
            });
        }
        
        // Remove filter tags
        const removeTags = this.element.querySelectorAll('.hph-tag__remove');
        removeTags.forEach(tag => {
            tag.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = tag.href;
            });
        });
    }
    
    setupViewToggle() {
        if (!this.viewToggle) return;
        
        const buttons = this.viewToggle.querySelectorAll('.hph-view-toggle__btn');
        const archiveLayout = document.querySelector('[data-archive-layout]');
        
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                const view = button.dataset.view;
                this.switchView(view, buttons, archiveLayout);
            });
        });
    }
    
    switchView(view, buttons, archiveLayout) {
        if (this.activeView === view) return;
        
        this.activeView = view;
        
        // Update button states
        buttons.forEach(btn => {
            btn.classList.remove('is-active');
            if (btn.dataset.view === view) {
                btn.classList.add('is-active');
            }
        });
        
        // Update content visibility
        const viewContents = document.querySelectorAll('[data-view-content]');
        viewContents.forEach(content => {
            content.style.display = content.dataset.viewContent === view ? 'block' : 'none';
        });
        
        // Update archive layout class
        if (archiveLayout) {
            archiveLayout.dataset.archiveLayout = view;
        }
        
        // Trigger view-specific initialization
        this.initializeView(view);
        
        // Store preference
        localStorage.setItem(`hph_${this.postType}_view`, view);
        
        // Trigger custom event
        this.element.dispatchEvent(new CustomEvent('viewChanged', {
            detail: { view, postType: this.postType }
        }));
    }
    
    initializeView(view) {
        switch (view) {
            case 'map':
                this.initializeMap();
                break;
            case 'calendar':
                this.initializeCalendar();
                break;
        }
    }
    
    initializeMap() {
        // Map initialization will be handled by places-map.js
        const mapContainer = document.querySelector('#places-map');
        if (mapContainer && window.HphPlacesMap) {
            window.HphPlacesMap.initialize(mapContainer);
        }
    }
    
    initializeCalendar() {
        // Calendar initialization for events
        const calendarContainer = document.querySelector('#events-calendar');
        if (calendarContainer && window.HphEventsCalendar) {
            window.HphEventsCalendar.initialize(calendarContainer);
        }
    }
    
    handleFormSubmit(e) {
        e.preventDefault();
        this.applyFilters();
    }
    
    applyFilters() {
        const formData = new FormData(this.form);
        const params = new URLSearchParams();
        
        // Build query parameters
        for (let [key, value] of formData.entries()) {
            if (value && value.trim() !== '') {
                params.append(key, value);
            }
        }
        
        // Update URL and reload
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.location.href = newUrl;
    }
    
    initializeFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Restore form values from URL
        urlParams.forEach((value, key) => {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = value === '1';
                } else {
                    input.value = value;
                }
            }
        });
        
        // Restore view preference
        const savedView = localStorage.getItem(`hph_${this.postType}_view`);
        if (savedView && this.viewToggle) {
            const viewButton = this.viewToggle.querySelector(`[data-view="${savedView}"]`);
            if (viewButton) {
                viewButton.click();
            }
        }
    }
    
    updateResults(html) {
        // Update results dynamically without page reload
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');
        
        // Update grid content
        const currentGrid = document.querySelector('.hph-archive__grid');
        const newGrid = newDoc.querySelector('.hph-archive__grid');
        if (currentGrid && newGrid) {
            currentGrid.innerHTML = newGrid.innerHTML;
        }
        
        // Update count
        const currentCount = document.querySelector('.hph-archive__count');
        const newCount = newDoc.querySelector('.hph-archive__count');
        if (currentCount && newCount) {
            currentCount.innerHTML = newCount.innerHTML;
        }
        
        // Update pagination
        const currentPagination = document.querySelector('.hph-archive__pagination');
        const newPagination = newDoc.querySelector('.hph-archive__pagination');
        if (currentPagination && newPagination) {
            currentPagination.innerHTML = newPagination.innerHTML;
        } else if (currentPagination) {
            currentPagination.innerHTML = '';
        }
        
        // Trigger update event
        this.element.dispatchEvent(new CustomEvent('resultsUpdated'));
    }
    
    // Public methods
    getActiveFilters() {
        const formData = new FormData(this.form);
        const filters = {};
        
        for (let [key, value] of formData.entries()) {
            if (value && value.trim() !== '') {
                filters[key] = value;
            }
        }
        
        return filters;
    }
    
    clearFilters() {
        // Reset form
        this.form.reset();
        
        // Remove URL parameters
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
        
        // Reload page
        window.location.reload();
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    const filterElements = document.querySelectorAll('.hph-filters--local');
    filterElements.forEach(element => {
        new HphLocalFilters(element);
    });
});

// Export for use in other modules
window.HphLocalFilters = HphLocalFilters;
