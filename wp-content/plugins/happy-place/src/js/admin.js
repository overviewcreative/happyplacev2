/**
 * Admin JavaScript for Happy Place Plugin
 */

import '../scss/admin.scss';

class HappyPlaceAdmin {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializeComponents();
        
        console.log('Happy Place Admin initialized');
    }
    
    setupEventListeners() {
        document.addEventListener('click', (e) => {
            // Settings form save
            if (e.target.matches('.btn-save-settings')) {
                e.preventDefault();
                this.saveSettings(e.target.closest('form'));
            }
            
            // Import data
            if (e.target.matches('.btn-import-data')) {
                e.preventDefault();
                this.importData(e.target.dataset.type);
            }
            
            // Export data
            if (e.target.matches('.btn-export-data')) {
                e.preventDefault();
                this.exportData(e.target.dataset.type);
            }
        });
        
        // Form validation
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.admin-form')) {
                if (!this.validateForm(e.target)) {
                    e.preventDefault();
                }
            }
        });
    }
    
    initializeComponents() {
        this.initTabs();
        this.initColorPickers();
        this.initFileUploads();
        this.initTooltips();
    }
    
    async saveSettings(form) {
        if (!form) return;
        
        try {
            this.showLoading();
            
            const formData = new FormData(form);
            formData.append('action', 'hp_save_settings');
            formData.append('nonce', window.hp_admin?.nonce || '');
            
            const response = await fetch(window.hp_admin?.ajax_url || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Settings saved successfully', 'success');
            } else {
                throw new Error(result.data?.message || 'Failed to save settings');
            }
            
        } catch (error) {
            console.error('Settings save error:', error);
            this.showNotification('Error saving settings', 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    async importData(type) {
        const fileInput = document.querySelector(`#import-${type}-file`);
        if (!fileInput || !fileInput.files[0]) {
            this.showNotification('Please select a file to import', 'error');
            return;
        }
        
        try {
            this.showLoading();
            
            const formData = new FormData();
            formData.append('action', 'hp_import_data');
            formData.append('type', type);
            formData.append('file', fileInput.files[0]);
            formData.append('nonce', window.hp_admin?.nonce || '');
            
            const response = await fetch(window.hp_admin?.ajax_url || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification(`${type} data imported successfully`, 'success');
                fileInput.value = ''; // Clear file input
            } else {
                throw new Error(result.data?.message || 'Import failed');
            }
            
        } catch (error) {
            console.error('Import error:', error);
            this.showNotification('Error importing data', 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    async exportData(type) {
        try {
            this.showLoading();
            
            const params = new URLSearchParams({
                action: 'hp_export_data',
                type: type,
                nonce: window.hp_admin?.nonce || ''
            });
            
            // Create a download link
            const url = `${window.hp_admin?.ajax_url || '/wp-admin/admin-ajax.php'}?${params}`;
            
            const link = document.createElement('a');
            link.href = url;
            link.download = `${type}-export.csv`;
            link.style.display = 'none';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            this.showNotification(`${type} data exported successfully`, 'success');
            
        } catch (error) {
            console.error('Export error:', error);
            this.showNotification('Error exporting data', 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    validateForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });
        
        // Email validation
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                this.showFieldError(field, 'Please enter a valid email address');
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    showFieldError(field, message) {
        this.clearFieldError(field);
        
        const error = document.createElement('div');
        error.className = 'field-error';
        error.textContent = message;
        
        field.classList.add('is-invalid');
        field.parentNode.appendChild(error);
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }
    
    initTabs() {
        const tabNavs = document.querySelectorAll('.nav-tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabNavs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                
                const targetTab = tab.getAttribute('href').substring(1);
                
                // Update nav
                tabNavs.forEach(t => t.classList.remove('nav-tab-active'));
                tab.classList.add('nav-tab-active');
                
                // Update content
                tabContents.forEach(content => {
                    content.style.display = content.id === targetTab ? 'block' : 'none';
                });
            });
        });
    }
    
    initColorPickers() {
        const colorInputs = document.querySelectorAll('input[type="color"]');
        
        colorInputs.forEach(input => {
            // Add color picker functionality if needed
            input.addEventListener('change', (e) => {
                console.log('Color changed:', e.target.value);
            });
        });
    }
    
    initFileUploads() {
        const fileInputs = document.querySelectorAll('.file-upload input[type="file"]');
        
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                const files = e.target.files;
                const label = input.nextElementSibling;
                
                if (files.length > 0) {
                    label.textContent = files[0].name;
                } else {
                    label.textContent = 'Choose file';
                }
            });
        });
    }
    
    initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            
            element.addEventListener('mouseleave', (e) => {
                this.hideTooltip();
            });
        });
    }
    
    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'admin-tooltip';
        tooltip.textContent = text;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.position = 'absolute';
        tooltip.style.left = rect.left + 'px';
        tooltip.style.top = (rect.bottom + 5) + 'px';
    }
    
    hideTooltip() {
        const tooltip = document.querySelector('.admin-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }
    
    showLoading() {
        const loader = document.querySelector('.admin-loader');
        if (loader) {
            loader.style.display = 'block';
        }
    }
    
    hideLoading() {
        const loader = document.querySelector('.admin-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }
    
    showNotification(message, type = 'info') {
        // Use WordPress admin notices style
        const notice = document.createElement('div');
        notice.className = `notice notice-${type} is-dismissible`;
        notice.innerHTML = `
            <p>${message}</p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>
        `;
        
        const adminNotices = document.querySelector('.wrap h1');
        if (adminNotices) {
            adminNotices.parentNode.insertBefore(notice, adminNotices.nextSibling);
        } else {
            document.body.appendChild(notice);
        }
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notice.remove();
        }, 5000);
        
        // Manual dismiss
        notice.querySelector('.notice-dismiss').addEventListener('click', () => {
            notice.remove();
        });
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new HappyPlaceAdmin();
    });
} else {
    new HappyPlaceAdmin();
}