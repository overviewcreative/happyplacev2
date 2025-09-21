/**
 * User System JavaScript
 * 
 * Handles all user interactions including favorites, saved searches,
 * engagement tracking, and account management
 * 
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

class UserSystemManager {
    constructor() {
        this.isLoggedIn = HPUserSystem?.isLoggedIn || false;
        this.userId = HPUserSystem?.userId || 0;
        this.nonce = HPUserSystem?.nonce || '';
        this.ajaxUrl = HPUserSystem?.ajaxUrl || '/wp-admin/admin-ajax.php';
        
        this.pageStartTime = Date.now();
        this.maxScrollDepth = 0;
        this.currentListingId = this.getCurrentListingId();
        
        this.init();
    }
    
    init() {
        this.initFavoriteButtons();
        this.initSaveSearchButtons();
        this.initQuickRegistration();
        this.initEngagementTracking();
        this.initDashboard();
        this.initModals();
        
        // Track page view if on a listing
        if (this.currentListingId) {
            this.trackListingView();
        }
        
    }
    
    /**
     * Initialize favorite buttons
     */
    initFavoriteButtons() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.favorite-btn, .btn-favorite')) {
                e.preventDefault();
                this.handleFavoriteToggle(e.target.closest('.favorite-btn, .btn-favorite'));
            }
        });
    }
    
    /**
     * Handle favorite toggle
     */
    async handleFavoriteToggle(button) {
        const listingId = button.dataset.listingId;
        
        if (!listingId) {
            return;
        }
        
        // Check if user is logged in
        if (!this.isLoggedIn) {
            this.showLoginPrompt('Save your favorite properties', {
                benefits: [
                    'Save unlimited favorite properties',
                    'Get email alerts for new matches',
                    'Track your viewing history'
                ]
            });
            return;
        }
        
        // Show loading state
        button.classList.add('loading');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        try {
            const response = await this.makeRequest('hph_toggle_favorite', {
                listing_id: listingId,
                nonce: this.nonce
            });
            
            if (response.success) {
                this.updateFavoriteButton(button, response.data);
                this.showNotification(
                    response.data.action === 'added' ? 'Property saved to favorites!' : 'Property removed from favorites',
                    response.data.action === 'added' ? 'success' : 'info'
                );
                
                // Update favorites count in header/nav
                this.updateFavoritesCount(response.data.count);
            } else {
                this.showNotification(response.data.message || 'Error updating favorite', 'error');
            }
        } catch (error) {
            this.showNotification('Error updating favorite', 'error');
        } finally {
            // Restore button state
            button.classList.remove('loading');
            button.innerHTML = originalContent;
            button.disabled = false;
        }
    }
    
    /**
     * Update favorite button appearance
     */
    updateFavoriteButton(button, data) {
        const icon = button.querySelector('i');
        const textSpan = button.querySelector('.btn-text');
        
        if (data.is_favorited) {
            button.classList.add('is-favorited');
            if (icon) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            }
            if (textSpan) {
                textSpan.textContent = button.dataset.textRemove || 'Saved';
            }
        } else {
            button.classList.remove('is-favorited');
            if (icon) {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
            if (textSpan) {
                textSpan.textContent = button.dataset.textAdd || 'Save Property';
            }
        }
    }
    
    /**
     * Update favorites count in UI
     */
    updateFavoritesCount(count) {
        const counters = document.querySelectorAll('.favorites-count');
        counters.forEach(counter => {
            counter.textContent = count;
            counter.style.display = count > 0 ? 'inline' : 'none';
        });
    }
    
    /**
     * Initialize save search buttons
     */
    initSaveSearchButtons() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.save-search-btn, #save-current-search')) {
                e.preventDefault();
                this.handleSaveSearch(e.target.closest('.save-search-btn, #save-current-search'));
            }
        });
    }
    
    /**
     * Handle save search
     */
    async handleSaveSearch(button) {
        if (!this.isLoggedIn) {
            this.showLoginPrompt('Save your search and get email alerts', {
                conversion_context: 'save_search'
            });
            return;
        }
        
        // Get current search parameters from URL or form
        const searchParams = this.getCurrentSearchParams();
        
        if (Object.keys(searchParams).length === 0) {
            this.showNotification('Please perform a search first', 'warning');
            return;
        }
        
        // Show save search modal
        this.showSaveSearchModal(searchParams);
    }
    
    /**
     * Get current search parameters
     */
    getCurrentSearchParams() {
        const params = {};
        const urlParams = new URLSearchParams(window.location.search);
        
        // Get parameters from URL
        urlParams.forEach((value, key) => {
            if (value && key !== 'paged') {
                params[key] = value;
            }
        });
        
        // Also check active filters on the page
        const activeFilters = document.querySelectorAll('.active-filter');
        activeFilters.forEach(filter => {
            const key = filter.dataset.filterKey;
            const value = filter.dataset.filterValue;
            if (key && value) {
                params[key] = value;
            }
        });
        
        return params;
    }
    
    /**
     * Show save search modal
     */
    showSaveSearchModal(searchParams) {
        const modal = document.getElementById('save-search-modal');
        if (!modal) {
            // Create modal dynamically if it doesn't exist
            this.createSaveSearchModal(searchParams);
            return;
        }
        
        // Populate criteria summary
        this.populateSearchCriteria(searchParams);
        
        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
    
    /**
     * Create save search modal dynamically
     */
    createSaveSearchModal(searchParams) {
        const modalHtml = `
            <div class="modal fade" id="dynamic-save-search-modal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Save Your Search</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="dynamic-save-search-form">
                                <div class="mb-3">
                                    <label for="search-name-dynamic" class="form-label">Search Name</label>
                                    <input type="text" class="form-control" id="search-name-dynamic" name="search_name" 
                                           placeholder="e.g., 3BR Houses in Downtown" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email-frequency-dynamic" class="form-label">Email Frequency</label>
                                    <select class="form-select" id="email-frequency-dynamic" name="frequency">
                                        <option value="instant">Instant</option>
                                        <option value="daily" selected>Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                
                                <div class="search-criteria-summary">
                                    <h6>Search Criteria:</h6>
                                    <div id="criteria-list-dynamic"></div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" form="dynamic-save-search-form" class="btn btn-primary">Save Search</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Populate criteria
        this.populateSearchCriteria(searchParams, 'criteria-list-dynamic');
        
        // Handle form submission
        document.getElementById('dynamic-save-search-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitSaveSearch(e.target, searchParams);
        });
        
        // Show modal
        const modal = document.getElementById('dynamic-save-search-modal');
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Clean up modal after hiding
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    /**
     * Populate search criteria summary
     */
    populateSearchCriteria(searchParams, containerId = 'search-criteria-summary') {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const criteriaLabels = {
            's': 'Keywords',
            'location': 'Location',
            'property_type': 'Property Type',
            'min_price': 'Min Price',
            'max_price': 'Max Price',
            'bedrooms': 'Bedrooms',
            'bathrooms': 'Bathrooms',
            'features': 'Features'
        };
        
        let html = '';
        Object.keys(searchParams).forEach(key => {
            const label = criteriaLabels[key] || key.replace('_', ' ');
            const value = searchParams[key];
            html += `<span class="badge bg-secondary me-1 mb-1">${label}: ${value}</span>`;
        });
        
        container.innerHTML = html || '<span class="text-muted">No specific criteria</span>';
    }
    
    /**
     * Submit save search form
     */
    async submitSaveSearch(form, searchParams) {
        const formData = new FormData(form);
        const searchName = formData.get('search_name');
        const frequency = formData.get('frequency');
        
        if (!searchName.trim()) {
            this.showNotification('Please enter a search name', 'warning');
            return;
        }
        
        const submitButton = form.querySelector('[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        try {
            const response = await this.makeRequest('hph_save_search', {
                search_name: searchName,
                criteria: searchParams,
                frequency: frequency,
                nonce: this.nonce
            });
            
            if (response.success) {
                this.showNotification('Search saved successfully! You\'ll receive email alerts for new matches.', 'success');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                modal.hide();
            } else {
                this.showNotification(response.data.message || 'Error saving search', 'error');
            }
        } catch (error) {
            this.showNotification('Error saving search', 'error');
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Save Search';
        }
    }
    
    /**
     * Initialize quick registration
     */
    initQuickRegistration() {
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('quick-registration-form')) {
                e.preventDefault();
                this.handleQuickRegistration(e.target);
            }
        });
    }
    
    /**
     * Handle quick registration
     */
    async handleQuickRegistration(form) {
        const formData = new FormData(form);
        const submitButton = form.querySelector('[type="submit"]');
        
        // Validate
        const email = formData.get('email');
        const firstName = formData.get('first_name');
        
        if (!email || !firstName) {
            this.showNotification('Please fill in all required fields', 'warning');
            return;
        }
        
        // Show loading state
        const btnText = submitButton.querySelector('.btn-text');
        const btnLoading = submitButton.querySelector('.btn-loading');
        
        if (btnText && btnLoading) {
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
        }
        submitButton.disabled = true;
        
        try {
            const response = await this.makeRequest('quick_register', {
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                email: formData.get('email'),
                nonce: form.dataset.nonce || this.nonce
            });
            
            if (response.success) {
                this.showNotification(response.data.message, 'success');
                
                // Redirect or reload
                if (response.data.redirect) {
                    setTimeout(() => {
                        window.location.href = response.data.redirect;
                    }, 2000);
                } else {
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            } else {
                this.showNotification(response.data.message || 'Registration failed', 'error');
            }
        } catch (error) {
            this.showNotification('Registration error. Please try again.', 'error');
        } finally {
            // Restore button state
            if (btnText && btnLoading) {
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
            }
            submitButton.disabled = false;
        }
    }
    
    /**
     * Initialize engagement tracking
     */
    initEngagementTracking() {
        // Track scroll depth
        let maxScrollDepth = 0;
        window.addEventListener('scroll', () => {
            const scrollPercent = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
            if (scrollPercent > maxScrollDepth) {
                maxScrollDepth = scrollPercent;
                this.maxScrollDepth = maxScrollDepth;
            }
        });
        
        // Track time on page when leaving
        window.addEventListener('beforeunload', () => {
            this.trackTimeOnPage();
        });
        
        // Track page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                this.trackTimeOnPage();
            }
        });
    }
    
    /**
     * Track time spent on page
     */
    trackTimeOnPage() {
        if (!this.isLoggedIn) return;
        
        const timeSpent = Math.floor((Date.now() - this.pageStartTime) / 1000);
        const minutes = Math.floor(timeSpent / 60);
        
        if (minutes > 0) {
            // Send beacon to avoid blocking page unload
            const data = new URLSearchParams({
                action: 'hph_track_engagement',
                user_id: this.userId,
                engagement_action: 'time_on_site',
                minutes: minutes,
                scroll_depth: this.maxScrollDepth
            });
            
            navigator.sendBeacon(this.ajaxUrl, data);
        }
    }
    
    /**
     * Track listing view
     */
    trackListingView() {
        const viewStartTime = Date.now();
        
        // Track after 10 seconds on the page
        setTimeout(() => {
            const viewTime = Math.floor((Date.now() - viewStartTime) / 1000);
            
            this.makeRequest('track_listing_view_enhanced', {
                listing_id: this.currentListingId,
                view_time: viewTime,
                scroll_depth: this.maxScrollDepth,
                source: 'single_listing'
            }).catch(error => {
            });
        }, 10000);
    }
    
    /**
     * Get current listing ID
     */
    getCurrentListingId() {
        const listingElement = document.querySelector('[data-listing-id]');
        return listingElement ? listingElement.dataset.listingId : null;
    }
    
    /**
     * Initialize dashboard functionality
     */
    initDashboard() {
        // Handle dashboard tab switching with URL hash
        if (window.location.hash && document.querySelector('.user-dashboard')) {
            const hash = window.location.hash.substring(1);
            const tabButton = document.querySelector(`[data-bs-target="#${hash}"]`);
            if (tabButton) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            }
        }
        
        // Update URL hash when tabs change
        document.addEventListener('shown.bs.tab', (e) => {
            const target = e.target.getAttribute('data-bs-target');
            if (target) {
                const hash = target.substring(1);
                history.replaceState(null, null, '#' + hash);
            }
        });
        
        // Load dashboard data
        this.loadDashboardData();
    }
    
    /**
     * Load dashboard data via AJAX
     */
    async loadDashboardData() {
        if (!document.querySelector('.user-dashboard')) return;
        
        try {
            // Load recent activity
            const recentActivityContainer = document.getElementById('recent-activity');
            if (recentActivityContainer) {
                // This would load actual activity data
                recentActivityContainer.innerHTML = '<p class="text-muted">Recent activity will be displayed here.</p>';
            }
            
            // Load recommendations  
            const recommendationsContainer = document.getElementById('recommendations');
            if (recommendationsContainer) {
                // This would load personalized recommendations
                recommendationsContainer.innerHTML = '<p class="text-muted">Recommendations will appear here.</p>';
            }
        } catch (error) {
        }
    }
    
    /**
     * Initialize modals
     */
    initModals() {
        // No specific modal initialization needed for now
        // Bootstrap handles most of it
    }
    
    /**
     * Show login prompt modal
     */
    showLoginPrompt(message, options = {}) {
        const modalHtml = `
            <div class="modal fade" id="login-prompt-modal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Sign In Required</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="lead">${message}</p>
                            ${options.benefits ? `
                                <div class="benefits-list mb-3">
                                    <h6>With your free account you can:</h6>
                                    <ul class="list-unstyled">
                                        ${options.benefits.map(benefit => `<li><i class="fas fa-check text-success me-2"></i>${benefit}</li>`).join('')}
                                    </ul>
                                </div>
                            ` : ''}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Maybe Later</button>
                            <a href="/login?redirect_to=${encodeURIComponent(window.location.href)}" class="btn btn-primary">Sign In</a>
                            <a href="/register?redirect_to=${encodeURIComponent(window.location.href)}" class="btn btn-success">Create Free Account</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal
        const existingModal = document.getElementById('login-prompt-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add new modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = document.getElementById('login-prompt-modal');
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Clean up after hiding
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    /**
     * Show notification toast
     */
    showNotification(message, type = 'info') {
        // Create toast if container doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        const toastId = 'toast-' + Date.now();
        const iconMap = {
            success: 'fa-check-circle text-success',
            error: 'fa-times-circle text-danger', 
            warning: 'fa-exclamation-triangle text-warning',
            info: 'fa-info-circle text-info'
        };
        
        const icon = iconMap[type] || iconMap.info;
        
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert">
                <div class="toast-header">
                    <i class="fas ${icon} me-2"></i>
                    <strong class="me-auto">Notification</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: type === 'error' ? 8000 : 5000
        });
        
        toast.show();
        
        // Clean up after hiding
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
    
    /**
     * Make AJAX request
     */
    async makeRequest(action, data = {}) {
        const formData = new URLSearchParams({
            action: action,
            ...data
        });
        
        const response = await fetch(this.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.userSystem = new UserSystemManager();
});

// Expose for global use
window.UserSystemManager = UserSystemManager;
