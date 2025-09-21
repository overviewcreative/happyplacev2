/**
 * Advanced Filters Component JavaScript
 * 
 * Handles NLP processing, smart suggestions, filter management,
 * and integration with the search system
 * 
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

class HPHAdvancedFilters {
    
    constructor(componentId, options = {}) {
        this.componentId = componentId;
        this.container = document.getElementById(componentId);
        
        if (!this.container) {
            return;
        }
        
        // Configuration
        this.config = {
            ajaxEnabled: true,
            postType: 'listing',
            ajaxUrl: '/wp-admin/admin-ajax.php',
            nonce: '',
            debounceDelay: 500,
            nlpMinLength: 3,
            maxSuggestions: 8,
            ...options
        };
        
        // State management
        this.state = {
            isProcessing: false,
            currentFilters: {},
            parsedQuery: null,
            suggestions: [],
            activeSuggestionIndex: -1
        };
        
        // Cache DOM elements
        this.elements = this.cacheElements();
        
        // Initialize component
        this.init();
    }
    
    /**
     * Cache DOM elements for performance
     */
    cacheElements() {
        return {
            nlpInput: this.container.querySelector('#hph-nlp-search'),
            nlpSuggestions: this.container.querySelector('.hph-nlp-suggestions'),
            parsedQuery: this.container.querySelector('.hph-parsed-query'),
            parsedFilters: this.container.querySelector('.hph-parsed-filters'),
            smartSuggestions: this.container.querySelector('.hph-smart-suggestions'),
            form: this.container.querySelector('.hph-filters-form'),
            advancedToggle: this.container.querySelector('.hph-advanced-toggle-btn'),
            advancedPanel: this.container.querySelector('.hph-advanced-filters-panel'),
            clearFiltersBtn: this.container.querySelector('.hph-clear-filters'),
            saveSearchBtn: this.container.querySelector('.hph-save-search'),
            applyFiltersBtn: this.container.querySelector('.hph-apply-filters'),
            activeFilters: this.container.querySelector('.hph-active-filters'),
            activeFilterTags: this.container.querySelector('.hph-active-filter-tags'),
            processingIndicator: this.container.querySelector('.hph-nlp-processing')
        };
    }
    
    /**
     * Initialize the component
     */
    init() {
        this.bindEvents();
        this.initializeState();
        this.updateUI();
        
        // Initialize any existing filters
        this.processExistingFilters();
        
        // Trigger custom event
        this.container.dispatchEvent(new CustomEvent('hph:filters:initialized', {
            detail: { component: this }
        }));
    }
    
    /**
     * Bind all event handlers
     */
    bindEvents() {
        // NLP input events
        if (this.elements.nlpInput) {
            this.elements.nlpInput.addEventListener('input', this.debounce(
                this.handleNLPInput.bind(this), 
                this.config.debounceDelay
            ));
            
            this.elements.nlpInput.addEventListener('keydown', this.handleNLPKeydown.bind(this));
            this.elements.nlpInput.addEventListener('focus', this.handleNLPFocus.bind(this));
            this.elements.nlpInput.addEventListener('blur', this.handleNLPBlur.bind(this));
        }
        
        // Suggestions events
        if (this.elements.nlpSuggestions) {
            this.elements.nlpSuggestions.addEventListener('click', this.handleSuggestionClick.bind(this));
        }
        
        // Advanced toggle
        if (this.elements.advancedToggle) {
            this.elements.advancedToggle.addEventListener('click', this.toggleAdvancedFilters.bind(this));
        }
        
        // Form events
        if (this.elements.form) {
            this.elements.form.addEventListener('change', this.handleFilterChange.bind(this));
            this.elements.form.addEventListener('submit', this.handleFormSubmit.bind(this));
        }
        
        // Action buttons
        if (this.elements.clearFiltersBtn) {
            this.elements.clearFiltersBtn.addEventListener('click', this.clearAllFilters.bind(this));
        }
        
        if (this.elements.saveSearchBtn) {
            this.elements.saveSearchBtn.addEventListener('click', this.saveCurrentSearch.bind(this));
        }
        
        // Parsed query actions
        const applyParsedBtn = this.container.querySelector('.hph-apply-parsed');
        if (applyParsedBtn) {
            applyParsedBtn.addEventListener('click', this.applyParsedFilters.bind(this));
        }
        
        const closeParsedBtn = this.container.querySelector('.hph-close-parsed');
        if (closeParsedBtn) {
            closeParsedBtn.addEventListener('click', this.closeParsedQuery.bind(this));
        }
        
        // Close suggestions on outside click
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.hideSuggestions();
            }
        });
    }
    
    /**
     * Initialize component state
     */
    initializeState() {
        // Extract current filters from form
        if (this.elements.form) {
            const formData = new FormData(this.elements.form);
            for (const [key, value] of formData.entries()) {
                if (value && value !== '' && key !== 'post_type') {
                    this.state.currentFilters[key] = value;
                }
            }
        }
        
        // Initialize NLP input with current query
        if (this.elements.nlpInput && this.state.currentFilters.s) {
            this.elements.nlpInput.value = this.state.currentFilters.s;
        }
    }
    
    /**
     * Process existing filters on page load
     */
    processExistingFilters() {
        if (Object.keys(this.state.currentFilters).length > 0) {
            this.updateActiveFilters();
            this.updateAdvancedToggle();
        }
    }
    
    /**
     * Handle NLP input changes
     */
    async handleNLPInput(event) {
        const query = event.target.value.trim();
        
        if (query.length < this.config.nlpMinLength) {
            this.hideSuggestions();
            this.hideParsedQuery();
            return;
        }
        
        if (this.config.ajaxEnabled) {
            await this.processNLPQuery(query);
        }
    }
    
    /**
     * Process natural language query
     */
    async processNLPQuery(query) {
        this.showProcessing();
        
        try {
            // Process with NLP
            const nlpResponse = await this.makeAjaxRequest('hph_process_nlp_query', {
                query: query,
                post_type: this.config.postType
            });
            
            if (nlpResponse.success) {
                this.state.parsedQuery = nlpResponse.data;
                this.showParsedQuery();
            }
            
            // Get suggestions
            const suggestionsResponse = await this.makeAjaxRequest('hph_get_search_suggestions', {
                query: query,
                post_type: this.config.postType,
                context: this.state.currentFilters
            });
            
            if (suggestionsResponse.success) {
                this.state.suggestions = suggestionsResponse.data.suggestions || [];
                this.showSuggestions();
            }
            
        } catch (error) {
        } finally {
            this.hideProcessing();
        }
    }
    
    /**
     * Handle keyboard navigation in NLP input
     */
    handleNLPKeydown(event) {
        const suggestions = this.elements.nlpSuggestions.querySelectorAll('.hph-suggestion-item');
        
        if (suggestions.length === 0) return;
        
        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.state.activeSuggestionIndex = Math.min(
                    this.state.activeSuggestionIndex + 1,
                    suggestions.length - 1
                );
                this.highlightSuggestion();
                break;
                
            case 'ArrowUp':
                event.preventDefault();
                this.state.activeSuggestionIndex = Math.max(
                    this.state.activeSuggestionIndex - 1,
                    -1
                );
                this.highlightSuggestion();
                break;
                
            case 'Enter':
                event.preventDefault();
                if (this.state.activeSuggestionIndex >= 0) {
                    this.selectSuggestion(this.state.activeSuggestionIndex);
                } else if (this.state.parsedQuery) {
                    this.applyParsedFilters();
                }
                break;
                
            case 'Escape':
                this.hideSuggestions();
                this.hideParsedQuery();
                this.elements.nlpInput.blur();
                break;
        }
    }
    
    /**
     * Handle NLP input focus
     */
    handleNLPFocus() {
        if (this.state.suggestions.length > 0) {
            this.showSuggestions();
        }
    }
    
    /**
     * Handle NLP input blur
     */
    handleNLPBlur() {
        // Delay to allow suggestion clicks
        setTimeout(() => {
            this.hideSuggestions();
        }, 150);
    }
    
    /**
     * Handle suggestion clicks
     */
    handleSuggestionClick(event) {
        const suggestionItem = event.target.closest('.hph-suggestion-item');
        if (!suggestionItem) return;
        
        const index = Array.from(suggestionItem.parentNode.children).indexOf(suggestionItem);
        this.selectSuggestion(index);
    }
    
    /**
     * Select a suggestion
     */
    selectSuggestion(index) {
        const suggestion = this.state.suggestions[index];
        if (!suggestion) return;
        
        switch (suggestion.type) {
            case 'completion':
            case 'related':
            case 'trending':
                this.elements.nlpInput.value = suggestion.text;
                this.processNLPQuery(suggestion.text);
                break;
                
            case 'location':
                this.applyFilter('location_search', suggestion.text);
                break;
                
            default:
                this.elements.nlpInput.value = suggestion.text;
                break;
        }
        
        this.hideSuggestions();
    }
    
    /**
     * Highlight active suggestion
     */
    highlightSuggestion() {
        const suggestions = this.elements.nlpSuggestions.querySelectorAll('.hph-suggestion-item');
        
        suggestions.forEach((item, index) => {
            item.classList.toggle('active', index === this.state.activeSuggestionIndex);
        });
    }
    
    /**
     * Show suggestions dropdown
     */
    showSuggestions() {
        if (this.state.suggestions.length === 0) return;
        
        let html = '';
        
        this.state.suggestions.forEach((suggestion, index) => {
            const iconHtml = this.getSuggestionIcon(suggestion.type);
            const confidenceBar = suggestion.confidence ? 
                `<div class="hph-confidence-bar hph-w-full hph-h-1 hph-bg-gray-200 hph-rounded-full hph-mt-1">
                    <div class="hph-confidence-fill hph-h-full hph-bg-primary-400 hph-rounded-full" style="width: ${suggestion.confidence}%"></div>
                </div>` : '';
            
            html += `
                <div class="hph-suggestion-item hph-cursor-pointer hph-px-4 hph-py-3 hover:hph-bg-gray-50" data-index="${index}">
                    <div class="hph-flex hph-items-center hph-gap-3">
                        ${iconHtml}
                        <div class="hph-flex-1">
                            <div class="hph-text-sm hph-font-medium hph-text-gray-900">
                                ${this.escapeHtml(suggestion.text)}
                            </div>
                            ${suggestion.reason ? 
                                `<div class="hph-text-xs hph-text-gray-500 hph-mt-1">${this.escapeHtml(suggestion.reason)}</div>` : 
                                ''
                            }
                            ${confidenceBar}
                        </div>
                        ${suggestion.type === 'trending' ? '<span class="hph-text-xs hph-text-orange-500 hph-font-medium">🔥</span>' : ''}
                    </div>
                </div>
            `;
        });
        
        this.elements.nlpSuggestions.innerHTML = html;
        this.elements.nlpSuggestions.classList.remove('hph-hidden');
        this.elements.nlpSuggestions.classList.add('hph-animate-fade-in');
    }
    
    /**
     * Hide suggestions dropdown
     */
    hideSuggestions() {
        // Add null check for nlpSuggestions element
        if (!this.elements.nlpSuggestions) {
            return; // Exit gracefully if element doesn't exist
        }
        this.elements.nlpSuggestions.classList.add('hph-hidden');
        this.elements.nlpSuggestions.classList.remove('hph-animate-fade-in');
        this.state.activeSuggestionIndex = -1;
    }
    
    /**
     * Show parsed query display
     */
    showParsedQuery() {
        if (!this.state.parsedQuery || !this.state.parsedQuery.filters) return;
        
        const filters = this.state.parsedQuery.filters;
        let filtersHtml = '';
        
        Object.entries(filters).forEach(([key, value]) => {
            const label = this.getFilterLabel(key, value);
            filtersHtml += `
                <span class="hph-inline-flex hph-items-center hph-gap-1 hph-px-3 hph-py-1 hph-bg-blue-100 hph-text-blue-800 hph-text-sm hph-rounded-full">
                    ${this.escapeHtml(label)}
                </span>
            `;
        });
        
        this.elements.parsedFilters.innerHTML = filtersHtml;
        this.elements.parsedQuery.classList.remove('hph-hidden');
        this.elements.parsedQuery.classList.add('hph-animate-fade-in');
    }
    
    /**
     * Hide parsed query display
     */
    hideParsedQuery() {
        this.elements.parsedQuery.classList.add('hph-hidden');
        this.elements.parsedQuery.classList.remove('hph-animate-fade-in');
    }
    
    /**
     * Close parsed query display
     */
    closeParsedQuery() {
        this.hideParsedQuery();
        this.state.parsedQuery = null;
    }
    
    /**
     * Apply parsed filters to form
     */
    applyParsedFilters() {
        if (!this.state.parsedQuery || !this.state.parsedQuery.filters) return;
        
        const filters = this.state.parsedQuery.filters;
        
        // Apply each filter to the form
        Object.entries(filters).forEach(([key, value]) => {
            this.applyFilter(key, value);
        });
        
        // Apply search terms if any
        if (this.state.parsedQuery.search_terms) {
            this.applyFilter('s', this.state.parsedQuery.search_terms);
        }
        
        this.hideParsedQuery();
        
        // Submit form or trigger AJAX update
        if (this.config.ajaxEnabled) {
            this.updateResults();
        } else {
            this.elements.form.submit();
        }
    }
    
    /**
     * Apply a single filter
     */
    applyFilter(key, value) {
        const input = this.elements.form.querySelector(`[name="${key}"]`);
        
        if (input) {
            if (input.type === 'checkbox') {
                input.checked = !!value;
            } else {
                input.value = value;
            }
            
            // Trigger change event
            input.dispatchEvent(new Event('change', { bubbles: true }));
        } else {
            // Create hidden input for filters not in form
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = key;
            hiddenInput.value = value;
            this.elements.form.appendChild(hiddenInput);
        }
        
        this.state.currentFilters[key] = value;
    }
    
    /**
     * Handle filter changes
     */
    handleFilterChange(event) {
        const input = event.target;
        const name = input.name;
        let value = input.value;
        
        if (input.type === 'checkbox') {
            value = input.checked ? input.value : '';
        }
        
        if (value && value !== '') {
            this.state.currentFilters[name] = value;
        } else {
            delete this.state.currentFilters[name];
        }
        
        this.updateActiveFilters();
        this.updateAdvancedToggle();
        
        // Auto-update results if enabled
        if (this.config.ajaxEnabled && this.config.autoUpdate !== false) {
            this.debounce(this.updateResults.bind(this), 300)();
        }
    }
    
    /**
     * Handle form submission
     */
    handleFormSubmit(event) {
        if (this.config.ajaxEnabled) {
            event.preventDefault();
            this.updateResults();
        }
    }
    
    /**
     * Toggle advanced filters panel
     */
    toggleAdvancedFilters() {
        const panel = this.elements.advancedPanel;
        const toggle = this.elements.advancedToggle;
        
        if (panel.classList.contains('hph-hidden')) {
            panel.classList.remove('hph-hidden');
            panel.classList.add('hph-animate-fade-in');
            toggle.classList.add('active');
        } else {
            panel.classList.add('hph-hidden');
            panel.classList.remove('hph-animate-fade-in');
            toggle.classList.remove('active');
        }
    }
    
    /**
     * Clear all filters
     */
    clearAllFilters() {
        // Reset form
        this.elements.form.reset();
        
        // Clear NLP input
        if (this.elements.nlpInput) {
            this.elements.nlpInput.value = '';
        }
        
        // Reset state
        this.state.currentFilters = {};
        this.state.parsedQuery = null;
        
        // Update UI
        this.updateActiveFilters();
        this.updateAdvancedToggle();
        this.hideParsedQuery();
        this.hideSuggestions();
        
        // Update results
        if (this.config.ajaxEnabled) {
            this.updateResults();
        } else {
            // Navigate to clean URL
            window.location.href = window.location.pathname;
        }
    }
    
    /**
     * Save current search
     */
    async saveCurrentSearch() {
        if (!this.config.ajaxEnabled) return;
        
        try {
            const searchData = {
                ...this.state.currentFilters,
                post_type: this.config.postType
            };
            
            const response = await this.makeAjaxRequest('hph_save_search', {
                search_data: searchData,
                options: {
                    email_alerts: true
                }
            });
            
            if (response.success) {
                this.showNotification('Search saved successfully!', 'success');
            } else {
                this.showNotification('Failed to save search', 'error');
            }
            
        } catch (error) {
            this.showNotification('Failed to save search', 'error');
        }
    }
    
    /**
     * Update search results via AJAX
     */
    async updateResults() {
        if (!this.config.ajaxEnabled) return;
        
        try {
            const searchData = {
                ...this.state.currentFilters,
                post_type: this.config.postType,
                action_type: 'filter'
            };
            
            // Trigger update event
            this.container.dispatchEvent(new CustomEvent('hph:filters:updating', {
                detail: { filters: searchData }
            }));
            
            const response = await this.makeAjaxRequest('hpt_archive_ajax', searchData);
            
            if (response.success) {
                // Update URL
                if (response.data.url) {
                    history.pushState(null, '', response.data.url);
                }
                
                // Trigger results updated event
                this.container.dispatchEvent(new CustomEvent('hph:filters:updated', {
                    detail: { 
                        response: response.data,
                        filters: searchData
                    }
                }));
            }
            
        } catch (error) {
            this.showNotification('Failed to update results', 'error');
        }
    }
    
    /**
     * Update active filters display
     */
    updateActiveFilters() {
        // Add null check for activeFilters element
        if (!this.elements.activeFilters) {
            // Silently return if container doesn't exist (not all pages have it)
            return;
        }
        
        const activeCount = Object.keys(this.state.currentFilters).length;
        
        if (activeCount === 0) {
            this.elements.activeFilters.classList.add('hph-hidden');
            return;
        }
        
        let tagsHtml = '';
        
        Object.entries(this.state.currentFilters).forEach(([key, value]) => {
            if (key === 's' || key === 'post_type') return;
            
            const label = this.getFilterLabel(key, value);
            tagsHtml += `
                <span class="hph-active-filter-tag">
                    ${this.escapeHtml(label)}
                    <button type="button" class="hph-remove-filter" data-filter="${this.escapeHtml(key)}">
                        <svg class="hph-w-3 hph-h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </span>
            `;
        });
        
        // Add null check for activeFilterTags element
        if (this.elements.activeFilterTags) {
            this.elements.activeFilterTags.innerHTML = tagsHtml;
        }
        
        this.elements.activeFilters.classList.remove('hph-hidden');
        
        // Bind remove filter events with null check
        if (this.elements.activeFilterTags) {
            this.elements.activeFilterTags.querySelectorAll('.hph-remove-filter').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    this.removeFilter(e.target.closest('button').dataset.filter);
                });
            });
        }
    }
    
    /**
     * Update advanced toggle button
     */
    updateAdvancedToggle() {
        if (!this.elements.advancedToggle) return;
        
        const countElement = this.elements.advancedToggle.querySelector('.hph-active-filters-count');
        const activeCount = Object.keys(this.state.currentFilters).filter(key => 
            key !== 's' && key !== 'post_type'
        ).length;
        
        if (activeCount > 0) {
            countElement.textContent = activeCount;
            countElement.classList.remove('hph-hidden');
        } else {
            countElement.classList.add('hph-hidden');
        }
    }
    
    /**
     * Remove a specific filter
     */
    removeFilter(filterKey) {
        const input = this.elements.form.querySelector(`[name="${filterKey}"]`);
        
        if (input) {
            if (input.type === 'checkbox') {
                input.checked = false;
            } else {
                input.value = '';
            }
            
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        
        delete this.state.currentFilters[filterKey];
        this.updateActiveFilters();
        this.updateAdvancedToggle();
        
        if (this.config.ajaxEnabled) {
            this.updateResults();
        }
    }
    
    /**
     * Show processing indicator
     */
    showProcessing() {
        this.state.isProcessing = true;
        this.elements.processingIndicator?.classList.remove('hph-hidden');
    }
    
    /**
     * Hide processing indicator
     */
    hideProcessing() {
        this.state.isProcessing = false;
        this.elements.processingIndicator?.classList.add('hph-hidden');
    }
    
    /**
     * Show notification message
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `hph-notification hph-notification--${type} hph-fixed hph-top-4 hph-right-4 hph-p-4 hph-rounded-lg hph-shadow-lg hph-z-50`;
        notification.innerHTML = `
            <div class="hph-flex hph-items-center hph-gap-3">
                <span>${this.escapeHtml(message)}</span>
                <button class="hph-close-notification hph-ml-auto">
                    <svg class="hph-w-4 hph-h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        `;
        
        // Add styles based on type
        const styles = {
            success: 'hph-bg-green-100 hph-text-green-800 hph-border-green-200',
            error: 'hph-bg-red-100 hph-text-red-800 hph-border-red-200',
            info: 'hph-bg-blue-100 hph-text-blue-800 hph-border-blue-200'
        };
        
        notification.className += ` ${styles[type] || styles.info} hph-border`;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
        
        // Manual close
        notification.querySelector('.hph-close-notification').addEventListener('click', () => {
            notification.remove();
        });
    }
    
    /**
     * Make AJAX request
     */
    async makeAjaxRequest(action, data = {}) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', this.config.nonce);
        
        Object.entries(data).forEach(([key, value]) => {
            if (typeof value === 'object') {
                formData.append(key, JSON.stringify(value));
            } else {
                formData.append(key, value);
            }
        });
        
        const response = await fetch(this.config.ajaxUrl, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    }
    
    /**
     * Update UI state
     */
    updateUI() {
        this.updateActiveFilters();
        this.updateAdvancedToggle();
    }
    
    /**
     * Get suggestion icon HTML
     */
    getSuggestionIcon(type) {
        const icons = {
            completion: '🔧',
            related: '🔗',
            trending: '📈',
            location: '📍',
            filter: '🔍'
        };
        
        const icon = icons[type] || '💡';
        return `<span class="hph-suggestion-icon">${icon}</span>`;
    }
    
    /**
     * Get filter label for display
     */
    getFilterLabel(key, value) {
        const labels = {
            bedrooms: `${value}+ bed`,
            bathrooms: `${value}+ bath`,
            min_price: `Min $${this.formatPrice(value)}`,
            max_price: `Max $${this.formatPrice(value)}`,
            property_type: this.capitalizeFirst(value),
            status: this.capitalizeFirst(value),
            location_search: value,
            waterfront: 'Waterfront',
            pool: 'Pool',
            garage: 'Garage'
        };
        
        return labels[key] || `${this.capitalizeFirst(key)}: ${value}`;
    }
    
    /**
     * Format price for display
     */
    formatPrice(price) {
        const num = parseInt(price);
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(0) + 'k';
        }
        return num.toLocaleString();
    }
    
    /**
     * Capitalize first letter
     */
    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ');
    }
    
    /**
     * Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-initialize any advanced filters components
    document.querySelectorAll('[id^="hph-advanced-filters-"]').forEach(container => {
        const config = {
            ajaxEnabled: container.dataset.ajax === 'true',
            postType: container.dataset.postType || 'listing'
        };
        
        new HPHAdvancedFilters(container.id, config);
    });
});

// Expose to global scope
window.HPHAdvancedFilters = HPHAdvancedFilters;
