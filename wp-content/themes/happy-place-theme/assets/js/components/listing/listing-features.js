/**
 * HPH Property Features & Amenities JavaScript
 * Location: /wp-content/themes/happy-place/assets/js/property-features.js
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';

    /**
     * HPH Property Features Class
     */
    class HPHPropertyFeatures {
        constructor() {
            this.currentView = 'grid';
            this.currentFilter = 'all';
            this.searchTerm = '';
            this.activeTab = null;
            this.activeAccordion = null;
            
            this.init();
        }

        /**
         * Initialize features
         */
        init() {
            this.cacheElements();
            this.bindEvents();
            this.initView();
            this.initAccordion();
            this.initTabs();
        }

        /**
         * Cache DOM elements
         */
        cacheElements() {
            // Main elements
            this.$container = $('.hph-property-features');
            this.$viewBtns = $('.hph-view-btn');
            this.$views = $('.hph-features-view');
            this.$filterBtns = $('.hph-filter-btn');
            
            // Search elements
            this.$searchInput = $('.hph-search-input');
            this.$searchClear = $('.hph-search-clear');
            this.$searchResults = $('.hph-search-results');
            this.$resultsCount = $('.hph-results-count');
            
            // View-specific elements
            this.$categories = $('.hph-feature-category');
            this.$featureItems = $('.hph-feature-item, .hph-feature-tag, .hph-panel-item');
            this.$accordionItems = $('.hph-accordion-item');
            this.$accordionHeaders = $('.hph-accordion-header');
            this.$tabBtns = $('.hph-tab-btn');
            this.$tabPanels = $('.hph-tab-panel');
            
            // Action buttons
            this.$printBtn = $('.hph-print-features');
            this.$downloadBtn = $('.hph-download-features');
            this.$shareBtn = $('.hph-share-features');
        }

        /**
         * Bind events
         */
        bindEvents() {
            // View switching
            this.$viewBtns.on('click', (e) => this.switchView($(e.currentTarget)));
            
            // Filtering
            this.$filterBtns.on('click', (e) => this.applyFilter($(e.currentTarget)));
            
            // Search
            this.$searchInput.on('input', () => this.handleSearch());
            this.$searchClear.on('click', () => this.clearSearch());
            
            // Accordion
            this.$accordionHeaders.on('click', (e) => this.toggleAccordion($(e.currentTarget)));
            
            // Tabs
            this.$tabBtns.on('click', (e) => this.switchTab($(e.currentTarget)));
            
            // Actions
            this.$downloadBtn.on('click', () => this.downloadPDF());
            this.$shareBtn.on('click', () => this.shareFeatures());
            
            // Keyboard navigation
            this.setupKeyboardNavigation();
            
            // Window resize
            $(window).on('resize', () => this.handleResize());
        }

        /**
         * Initialize default view
         */
        initView() {
            const savedView = localStorage.getItem('hph_features_view');
            if (savedView && this.$views.filter(`[data-view-type="${savedView}"]`).length) {
                this.currentView = savedView;
                this.switchToView(savedView);
            }
        }

        /**
         * Switch view
         */
        switchView($btn) {
            const view = $btn.data('view');
            
            // Update buttons
            this.$viewBtns.removeClass('active').attr('aria-selected', 'false');
            $btn.addClass('active').attr('aria-selected', 'true');
            
            // Update views
            this.$views.removeClass('active');
            $(`.hph-${view}-view`).addClass('active');
            
            this.currentView = view;
            
            // Save preference
            localStorage.setItem('hph_features_view', view);
            
            // Trigger view change event
            this.$container.trigger('viewChanged', [view]);
        }

        /**
         * Switch to specific view
         */
        switchToView(view) {
            const $btn = this.$viewBtns.filter(`[data-view="${view}"]`);
            if ($btn.length) {
                this.switchView($btn);
            }
        }

        /**
         * Apply filter
         */
        applyFilter($btn) {
            const filter = $btn.data('filter');
            
            // Update buttons
            this.$filterBtns.removeClass('active');
            $btn.addClass('active');
            
            this.currentFilter = filter;
            
            // Apply filter based on type
            if (filter === 'all') {
                this.showAllFeatures();
            } else if (filter === 'premium') {
                this.showPremiumFeatures();
            } else if (filter === 'green') {
                this.showGreenFeatures();
            }
            
            // Update count
            this.updateFeatureCount();
        }

        /**
         * Show all features
         */
        showAllFeatures() {
            this.$categories.show();
            this.$featureItems.show();
            this.$accordionItems.show();
            this.$tabPanels.find('.hph-panel-item').show();
        }

        /**
         * Show premium features only
         */
        showPremiumFeatures() {
            // Hide all first
            this.$categories.each(function() {
                const $category = $(this);
                const $premiumItems = $category.find('.hph-premium');
                
                if ($premiumItems.length === 0) {
                    $category.hide();
                } else {
                    $category.show();
                    $category.find('.hph-feature-item').hide();
                    $premiumItems.show();
                }
            });
            
            // Accordion view
            this.$accordionItems.each(function() {
                const $item = $(this);
                const $premiumTags = $item.find('.hph-tag-premium');
                
                if ($premiumTags.length === 0) {
                    $item.hide();
                } else {
                    $item.show();
                    $item.find('.hph-feature-tag').hide();
                    $premiumTags.show();
                }
            });
            
            // Tabs view
            this.$tabPanels.find('.hph-panel-item').hide();
            this.$tabPanels.find('.hph-item-premium').show();
        }

        /**
         * Show green features only
         */
        showGreenFeatures() {
            // Find green category
            const greenCategory = 'green_features';
            
            this.$categories.hide();
            this.$categories.filter(`[data-category="${greenCategory}"]`).show();
            
            this.$accordionItems.hide();
            this.$accordionItems.filter(`[data-category="${greenCategory}"]`).show();
            
            // For tabs, switch to green features tab if exists
            const $greenTab = this.$tabBtns.filter('[data-tab="green_features"]');
            if ($greenTab.length) {
                this.switchTab($greenTab);
            }
        }

        /**
         * Handle search
         */
        handleSearch() {
            const term = this.$searchInput.val().toLowerCase().trim();
            this.searchTerm = term;
            
            if (term === '') {
                this.clearSearch();
                return;
            }
            
            // Show clear button
            this.$searchClear.show();
            
            let matchCount = 0;
            
            // Search in all views
            this.$featureItems.each(function() {
                const $item = $(this);
                const text = $item.text().toLowerCase();
                
                if (text.includes(term)) {
                    $item.show().addClass('hph-highlight');
                    matchCount++;
                    
                    // Show parent category
                    $item.closest('.hph-feature-category, .hph-accordion-item').show();
                } else {
                    $item.hide().removeClass('hph-highlight');
                }
            });
            
            // Update results
            this.$resultsCount.text(`${matchCount} features found`);
            this.$searchResults.show();
            
            // Remove highlight after animation
            setTimeout(() => {
                $('.hph-highlight').removeClass('hph-highlight');
            }, 1000);
        }

        /**
         * Clear search
         */
        clearSearch() {
            this.$searchInput.val('');
            this.$searchClear.hide();
            this.$searchResults.hide();
            this.searchTerm = '';
            
            // Reset filter
            this.applyFilter(this.$filterBtns.filter('.active'));
        }

        /**
         * Initialize accordion
         */
        initAccordion() {
            // Set initial state
            this.$accordionHeaders.attr('aria-expanded', 'false');
            $('.hph-accordion-content').removeClass('active');
        }

        /**
         * Toggle accordion
         */
        toggleAccordion($header) {
            const $item = $header.parent();
            const $content = $item.find('.hph-accordion-content');
            const isExpanded = $header.attr('aria-expanded') === 'true';
            
            if (isExpanded) {
                // Close
                $header.attr('aria-expanded', 'false');
                $content.removeClass('active');
                this.activeAccordion = null;
            } else {
                // Close others (optional - remove for multi-open)
                this.$accordionHeaders.attr('aria-expanded', 'false');
                $('.hph-accordion-content').removeClass('active');
                
                // Open this one
                $header.attr('aria-expanded', 'true');
                $content.addClass('active');
                this.activeAccordion = $item.data('category');
            }
        }

        /**
         * Initialize tabs
         */
        initTabs() {
            // Set first tab as active if none selected
            if (!this.$tabBtns.filter('.active').length && this.$tabBtns.length) {
                this.$tabBtns.first().addClass('active');
                this.$tabPanels.first().addClass('active');
                this.activeTab = this.$tabBtns.first().data('tab');
            }
        }

        /**
         * Switch tab
         */
        switchTab($btn) {
            const tab = $btn.data('tab');
            
            // Update buttons
            this.$tabBtns.removeClass('active');
            $btn.addClass('active');
            
            // Update panels
            this.$tabPanels.removeClass('active');
            this.$tabPanels.filter(`[data-panel="${tab}"]`).addClass('active');
            
            this.activeTab = tab;
            
            // Scroll into view if needed
            this.scrollTabIntoView($btn);
        }

        /**
         * Scroll tab into view
         */
        scrollTabIntoView($tab) {
            const container = $('.hph-tabs-nav')[0];
            const tab = $tab[0];
            
            if (container && tab) {
                const scrollLeft = tab.offsetLeft - (container.offsetWidth / 2) + (tab.offsetWidth / 2);
                container.scrollTo({ left: scrollLeft, behavior: 'smooth' });
            }
        }

        /**
         * Update feature count
         */
        updateFeatureCount() {
            const visibleCount = this.$featureItems.filter(':visible').length;
            $('.hph-features-count').text(`${visibleCount} Features`);
        }

        /**
         * Download PDF
         */
        downloadPDF() {
            // Prepare features data
            const featuresData = this.collectFeaturesData();
            
            // In production, this would send to server endpoint
            // For demo, we'll create a simple text download
            const content = this.generateFeaturesText(featuresData);
            const blob = new Blob([content], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'property-features.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            // Show success message
            this.showNotification('Features list downloaded!');
        }

        /**
         * Collect features data
         */
        collectFeaturesData() {
            const data = {};
            
            $('.hph-feature-category').each(function() {
                const $category = $(this);
                const categoryName = $category.find('.hph-category-title').text();
                const features = [];
                
                $category.find('.hph-feature-item').each(function() {
                    features.push($(this).find('span').text());
                });
                
                if (features.length > 0) {
                    data[categoryName] = features;
                }
            });
            
            return data;
        }

        /**
         * Generate features text
         */
        generateFeaturesText(data) {
            let text = 'PROPERTY FEATURES & AMENITIES\n';
            text += '================================\n\n';
            
            for (const [category, features] of Object.entries(data)) {
                text += `${category}\n`;
                text += '-'.repeat(category.length) + '\n';
                features.forEach(feature => {
                    text += `• ${feature}\n`;
                });
                text += '\n';
            }
            
            text += `\nGenerated: ${new Date().toLocaleDateString()}\n`;
            return text;
        }

        /**
         * Share features
         */
        shareFeatures() {
            const url = window.location.href;
            const text = 'Check out the features of this property';
            
            if (navigator.share) {
                // Use Web Share API if available
                navigator.share({
                    title: 'Property Features',
                    text: text,
                    url: url
                }).catch(err => console.log('Error sharing:', err));
            } else {
                // Fallback to copying link
                this.copyToClipboard(url);
            }
        }

        /**
         * Copy to clipboard
         */
        copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            this.showNotification('Link copied to clipboard!');
        }

        /**
         * Show notification
         */
        showNotification(message) {
            const $notification = $('<div class="hph-notification">')
                .text(message)
                .appendTo('body');
            
            setTimeout(() => {
                $notification.addClass('show');
            }, 100);
            
            setTimeout(() => {
                $notification.removeClass('show');
                setTimeout(() => {
                    $notification.remove();
                }, 300);
            }, 3000);
        }

        /**
         * Setup keyboard navigation
         */
        setupKeyboardNavigation() {
            // Tab navigation with arrow keys
            this.$tabBtns.on('keydown', (e) => {
                if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                    e.preventDefault();
                    const $current = $(e.currentTarget);
                    const $tabs = this.$tabBtns;
                    const currentIndex = $tabs.index($current);
                    let nextIndex;
                    
                    if (e.key === 'ArrowLeft') {
                        nextIndex = currentIndex - 1;
                        if (nextIndex < 0) nextIndex = $tabs.length - 1;
                    } else {
                        nextIndex = currentIndex + 1;
                        if (nextIndex >= $tabs.length) nextIndex = 0;
                    }
                    
                    $tabs.eq(nextIndex).click().focus();
                }
            });
            
            // Accordion navigation
            this.$accordionHeaders.on('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(e.currentTarget).click();
                }
            });
            
            // Search shortcuts
            $(document).on('keydown', (e) => {
                // Ctrl/Cmd + F to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    this.$searchInput.focus();
                }
                
                // Escape to clear search
                if (e.key === 'Escape' && this.$searchInput.is(':focus')) {
                    this.clearSearch();
                    this.$searchInput.blur();
                }
            });
        }

        /**
         * Handle window resize
         */
        handleResize() {
            const width = $(window).width();
            
            // Adjust view for mobile
            if (width < 768 && this.currentView === 'tabs') {
                // Switch to accordion on mobile for better UX
                this.switchToView('accordion');
            }
            
            // Update tab scroll
            if (this.activeTab) {
                const $activeTab = this.$tabBtns.filter(`[data-tab="${this.activeTab}"]`);
                if ($activeTab.length) {
                    this.scrollTabIntoView($activeTab);
                }
            }
        }

        /**
         * Expand all categories (for print)
         */
        expandAll() {
            this.$accordionHeaders.attr('aria-expanded', 'true');
            $('.hph-accordion-content').addClass('active');
            this.$tabPanels.addClass('active');
        }

        /**
         * Collapse all categories
         */
        collapseAll() {
            this.$accordionHeaders.attr('aria-expanded', 'false');
            $('.hph-accordion-content').removeClass('active');
            this.$tabPanels.removeClass('active');
            
            // Keep first tab active
            this.$tabPanels.first().addClass('active');
        }
    }

    /**
     * Initialize when DOM is ready
     */
    $(document).ready(function() {
        if ($('.hph-property-features').length) {
            window.hphPropertyFeatures = new HPHPropertyFeatures();
        }
    });

    /**
     * Print preparation
     */
    window.addEventListener('beforeprint', function() {
        if (window.hphPropertyFeatures) {
            window.hphPropertyFeatures.expandAll();
        }
    });

    window.addEventListener('afterprint', function() {
        if (window.hphPropertyFeatures) {
            window.hphPropertyFeatures.collapseAll();
        }
    });

})(jQuery);

/**
 * Notification styles (add to CSS or inline)
 */
const notificationStyles = `
<style>
.hph-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: var(--hph-gray-900);
    color: var(--hph-white);
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 10000;
}

.hph-notification.show {
    transform: translateY(0);
    opacity: 1;
}
</style>
`;

// Add notification styles to head
document.head.insertAdjacentHTML('beforeend', notificationStyles);
