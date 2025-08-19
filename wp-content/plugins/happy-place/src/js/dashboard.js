/**
 * Dashboard JavaScript for Happy Place Plugin
 */

import '../scss/dashboard.scss';

class HappyPlaceDashboard {
    constructor() {
        this.currentSection = 'overview';
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.loadCurrentSection();
        
        console.log('Happy Place Dashboard initialized');
    }
    
    setupEventListeners() {
        // Navigation
        document.addEventListener('click', (e) => {
            if (e.target.matches('.dashboard-nav__link')) {
                e.preventDefault();
                this.navigateToSection(e.target.dataset.section);
            }
            
            if (e.target.matches('.btn-save-listing')) {
                e.preventDefault();
                this.saveListing(e.target.closest('form'));
            }
            
            if (e.target.matches('.btn-delete-listing')) {
                e.preventDefault();
                this.deleteListing(e.target.dataset.listingId);
            }
        });
        
        // Form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.dashboard-form')) {
                e.preventDefault();
                this.handleFormSubmit(e.target);
            }
        });
    }
    
    initializeComponents() {
        this.initCharts();
        this.initDataTables();
        this.initModals();
    }
    
    async loadCurrentSection() {
        const section = window.hp_dashboard?.current_section || 'overview';
        await this.navigateToSection(section);
    }
    
    async navigateToSection(section) {
        if (section === this.currentSection) return;
        
        try {
            this.showLoading();
            
            const response = await this.apiRequest('hp_dashboard_load_section', {
                section: section
            });
            
            if (response.success) {
                this.currentSection = section;
                this.updateSectionContent(response.data);
                this.updateNavigation(section);
                
                // Update URL without page reload
                if (history.pushState) {
                    history.pushState(null, null, `/agent-dashboard/${section}`);
                }
            } else {
                throw new Error(response.data?.message || 'Failed to load section');
            }
            
        } catch (error) {
            console.error('Navigation error:', error);
            this.showNotification('Error loading section', 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    updateSectionContent(data) {
        const contentArea = document.querySelector('.dashboard-content');
        if (contentArea && data.html) {
            contentArea.innerHTML = data.html;
            this.initializeComponents();
        }
    }
    
    updateNavigation(activeSection) {
        const navLinks = document.querySelectorAll('.dashboard-nav__link');
        navLinks.forEach(link => {
            link.classList.toggle('is-active', link.dataset.section === activeSection);
        });
    }
    
    async saveListing(form) {
        if (!form) return;
        
        try {
            const formData = new FormData(form);
            formData.append('action', 'hp_save_listing');
            
            const response = await this.apiRequest('hp_save_listing', formData);
            
            if (response.success) {
                this.showNotification('Listing saved successfully', 'success');
                
                // Refresh listings if we're on that section
                if (this.currentSection === 'listings') {
                    this.refreshListingsTable();
                }
            } else {
                throw new Error(response.data?.message || 'Failed to save listing');
            }
            
        } catch (error) {
            console.error('Save error:', error);
            this.showNotification('Error saving listing', 'error');
        }
    }
    
    async deleteListing(listingId) {
        if (!listingId) return;
        
        if (!confirm(window.hp_dashboard?.strings?.confirm_delete || 'Are you sure?')) {
            return;
        }
        
        try {
            const response = await this.apiRequest('hp_delete_listing', {
                listing_id: listingId
            });
            
            if (response.success) {
                this.showNotification('Listing deleted successfully', 'success');
                this.refreshListingsTable();
            } else {
                throw new Error(response.data?.message || 'Failed to delete listing');
            }
            
        } catch (error) {
            console.error('Delete error:', error);
            this.showNotification('Error deleting listing', 'error');
        }
    }
    
    async handleFormSubmit(form) {
        const action = form.dataset.action;
        if (!action) return;
        
        try {
            const formData = new FormData(form);
            formData.append('action', action);
            
            const response = await this.apiRequest(action, formData);
            
            if (response.success) {
                this.showNotification('Form submitted successfully', 'success');
                
                if (response.data?.redirect) {
                    window.location.href = response.data.redirect;
                }
            } else {
                throw new Error(response.data?.message || 'Form submission failed');
            }
            
        } catch (error) {
            console.error('Form error:', error);
            this.showNotification('Error submitting form', 'error');
        }
    }
    
    async apiRequest(action, data = {}) {
        const requestData = new FormData();
        
        if (data instanceof FormData) {
            // Append existing FormData
            for (const [key, value] of data.entries()) {
                requestData.append(key, value);
            }
        } else {
            // Convert object to FormData
            Object.keys(data).forEach(key => {
                requestData.append(key, data[key]);
            });
        }
        
        requestData.append('action', action);
        requestData.append('nonce', window.hp_dashboard?.nonce || '');
        
        const response = await fetch(window.hp_dashboard?.ajax_url || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: requestData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    }
    
    initCharts() {
        const chartElements = document.querySelectorAll('[data-chart]');
        
        chartElements.forEach(async (element) => {
            const chartType = element.dataset.chart;
            const chartData = JSON.parse(element.dataset.chartData || '{}');
            
            // Dynamic import Chart.js only when needed
            if (chartType && chartData) {
                try {
                    const { Chart, registerables } = await import('chart.js');
                    Chart.register(...registerables);
                    
                    new Chart(element, {
                        type: chartType,
                        data: chartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                } catch (error) {
                    console.error('Chart initialization error:', error);
                }
            }
        });
    }
    
    initDataTables() {
        const tables = document.querySelectorAll('.data-table');
        
        tables.forEach(table => {
            // Simple table enhancements
            this.makeTableSortable(table);
            this.addTableSearch(table);
        });
    }
    
    makeTableSortable(table) {
        const headers = table.querySelectorAll('th[data-sortable]');
        
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                // Simple sorting logic
                console.log('Sorting by:', header.textContent);
            });
        });
    }
    
    addTableSearch(table) {
        const searchInput = table.parentElement.querySelector('.table-search');
        if (!searchInput) return;
        
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    initModals() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-modal]')) {
                e.preventDefault();
                this.openModal(e.target.dataset.modal);
            }
            
            if (e.target.matches('.modal-close') || e.target.matches('.modal-backdrop')) {
                this.closeModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
    }
    
    showListingForm(listingId = null) {
        // Create or show listing form modal
        const modalId = 'listing-form-modal';
        let modal = document.getElementById(modalId);
        
        if (!modal) {
            modal = this.createListingFormModal(modalId);
            document.body.appendChild(modal);
        }
        
        // If editing, load existing data
        if (listingId) {
            this.loadListingData(listingId, modal);
        } else {
            // Clear form for new listing
            const form = modal.querySelector('form');
            if (form) form.reset();
        }
        
        this.openModal(modalId);
    }
    
    createListingFormModal(modalId) {
        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Listing</h2>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="listing-form dashboard-form" data-action="hp_save_listing">
                        <div class="form-group">
                            <label for="listing_title">Property Title</label>
                            <input type="text" id="listing_title" name="title" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="listing_price">Price</label>
                                <input type="number" id="listing_price" name="price" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="listing_status">Status</label>
                                <select id="listing_status" name="status">
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="sold">Sold</option>
                                    <option value="coming_soon">Coming Soon</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="listing_beds">Bedrooms</label>
                                <input type="number" id="listing_beds" name="bedrooms" min="0">
                            </div>
                            
                            <div class="form-group">
                                <label for="listing_baths">Bathrooms</label>
                                <input type="number" id="listing_baths" name="bathrooms" min="0" step="0.5">
                            </div>
                            
                            <div class="form-group">
                                <label for="listing_sqft">Square Feet</label>
                                <input type="number" id="listing_sqft" name="sqft" min="0">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="listing_address">Street Address</label>
                            <input type="text" id="listing_address" name="address" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="listing_city">City</label>
                                <input type="text" id="listing_city" name="city" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="listing_state">State</label>
                                <input type="text" id="listing_state" name="state" maxlength="2" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="listing_zip">ZIP Code</label>
                                <input type="text" id="listing_zip" name="zip" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="listing_description">Description</label>
                            <textarea id="listing_description" name="description" rows="4"></textarea>
                        </div>
                        
                        <input type="hidden" name="listing_id" value="">
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Listing</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        return modal;
    }
    
    async loadListingData(listingId, modal) {
        try {
            const response = await this.apiRequest('hp_get_listing', {
                listing_id: listingId
            });
            
            if (response.success && response.data) {
                const form = modal.querySelector('form');
                const data = response.data;
                
                // Fill form fields
                Object.keys(data).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.value = data[key];
                    }
                });
                
                // Update modal title
                modal.querySelector('.modal-header h2').textContent = 'Edit Listing';
            }
        } catch (error) {
            console.error('Error loading listing data:', error);
            this.showNotification('Error loading listing data', 'error');
        }
    }
    
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('is-active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    closeModal() {
        const activeModal = document.querySelector('.modal.is-active');
        if (activeModal) {
            activeModal.classList.remove('is-active');
            document.body.style.overflow = '';
        }
    }
    
    refreshListingsTable() {
        // Refresh the listings table data
        const table = document.querySelector('#listings-table');
        if (table) {
            console.log('Refreshing listings table...');
            // Implementation would reload table data
        }
    }
    
    showLoading() {
        const loader = document.querySelector('.dashboard-loader');
        if (loader) {
            loader.style.display = 'block';
        }
    }
    
    hideLoading() {
        const loader = document.querySelector('.dashboard-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `dashboard-notification dashboard-notification--${type}`;
        notification.innerHTML = `
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
        
        // Manual close
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    }
}

// Initialize when DOM is ready
let dashboardInstance = null;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        dashboardInstance = new HappyPlaceDashboard();
        
        // Make showListingForm globally available
        window.showListingForm = (listingId) => {
            dashboardInstance.showListingForm(listingId);
        };
    });
} else {
    dashboardInstance = new HappyPlaceDashboard();
    
    // Make showListingForm globally available
    window.showListingForm = (listingId) => {
        dashboardInstance.showListingForm(listingId);
    };
}