/**
 * Universal Loop Component JavaScript
 * Handles view switching, sorting, and other interactive features
 *
 * @package HappyPlaceTheme
 * @since 1.0.0
 */

class HphUniversalLoop {
    constructor(element) {
        this.container = element;
        this.loopContainer = element.querySelector('.hph-loop-container');
        this.viewButtons = element.querySelectorAll('[data-view]');
        this.sortSelect = element.querySelector('[data-sort-posts]');

        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // View toggle buttons
        this.viewButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchView(button.dataset.view);
            });
        });

        // Sort dropdown
        if (this.sortSelect) {
            this.sortSelect.addEventListener('change', (e) => {
                this.handleSort(e.target.value);
            });
        }
    }

    switchView(view) {
        // Update button states
        this.viewButtons.forEach(btn => {
            btn.classList.remove('hph-view-btn-active');
        });

        const activeButton = this.container.querySelector(`[data-view="${view}"]`);
        if (activeButton) {
            activeButton.classList.add('hph-view-btn-active');
        }

        // Update loop container classes
        if (this.loopContainer) {
            // Remove existing layout classes
            this.loopContainer.classList.remove('hph-loop-container--grid', 'hph-loop-container--list');

            // Add new layout class
            this.loopContainer.classList.add(`hph-loop-container--${view}`);

            // Update data attribute
            this.loopContainer.dataset.layout = view;

            // Update cards layout
            this.updateCardLayouts(view);
        }

        // Trigger custom event
        this.container.dispatchEvent(new CustomEvent('viewChanged', {
            detail: { view: view }
        }));
    }

    updateCardLayouts(view) {
        const cards = this.loopContainer.querySelectorAll('.hph-universal-card');

        cards.forEach(card => {
            // Update card layout classes
            card.classList.remove('hph-card-vertical', 'hph-card-horizontal');

            if (view === 'list') {
                card.classList.add('hph-card-horizontal');
            } else {
                card.classList.add('hph-card-vertical');
            }
        });
    }

    handleSort(sortValue) {
        // This would typically trigger an AJAX request to re-fetch sorted content

        // For now, just trigger an event that can be handled by other scripts
        this.container.dispatchEvent(new CustomEvent('sortChanged', {
            detail: { sortBy: sortValue }
        }));
    }

    // Public API methods
    destroy() {
        // Cleanup event listeners if needed
        this.container.classList.remove('is-initialized');
    }

    refresh() {
        // Re-initialize if content changes
        this.init();
    }

    getCurrentView() {
        const activeButton = this.container.querySelector('.hph-view-btn-active');
        return activeButton ? activeButton.dataset.view : 'grid';
    }
}

// Initialize all universal loops on page load
document.addEventListener('DOMContentLoaded', function() {
    const loops = document.querySelectorAll('.hph-universal-loop');
    const loopInstances = [];

    loops.forEach((loop, index) => {
        try {
            const instance = new HphUniversalLoop(loop);
            loopInstances.push(instance);

            // Store instance on element for external access
            loop.hphLoop = instance;
            loop.classList.add('is-initialized');
        } catch (error) {
        }
    });

    // Store global reference
    window.HphLoops = loopInstances;
});

// Export class for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HphUniversalLoop;
} else {
    window.HphUniversalLoop = HphUniversalLoop;
}