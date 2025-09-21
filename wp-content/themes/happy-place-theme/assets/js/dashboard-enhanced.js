/**
 * Enhanced Dashboard Listing Cards
 * Inline editing, image upload, and comprehensive form handling
 */

class HPH_Enhanced_Dashboard {
    constructor() {
        this.init();
    }

    init() {
        this.bindInlineEditingEvents();
        this.bindImageUploadEvents();
        this.bindListingActions();
        this.bindFormSubmission();
    }

    /**
     * Inline Editing Functionality
     */
    bindInlineEditingEvents() {
        // Click to edit
        document.addEventListener('click', (e) => {
            if (e.target.matches('.hph-inline-edit__display')) {
                this.startInlineEdit(e.target);
            }
        });

        // Save inline edit
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="save-inline"]') || 
                e.target.closest('[data-action="save-inline"]')) {
                e.preventDefault();
                const button = e.target.closest('[data-action="save-inline"]');
                this.saveInlineEdit(button);
            }
        });

        // Cancel inline edit
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="cancel-inline"]') || 
                e.target.closest('[data-action="cancel-inline"]')) {
                e.preventDefault();
                const button = e.target.closest('[data-action="cancel-inline"]');
                this.cancelInlineEdit(button);
            }
        });

        // Save on Enter key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.target.matches('.hph-inline-edit__input, .hph-inline-edit__select')) {
                e.preventDefault();
                const saveButton = e.target.closest('.hph-inline-edit__form').querySelector('[data-action="save-inline"]');
                this.saveInlineEdit(saveButton);
            }
        });

        // Cancel on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && e.target.matches('.hph-inline-edit__input, .hph-inline-edit__select')) {
                const cancelButton = e.target.closest('.hph-inline-edit__form').querySelector('[data-action="cancel-inline"]');
                this.cancelInlineEdit(cancelButton);
            }
        });
    }

    startInlineEdit(displayElement) {
        const inlineEdit = displayElement.closest('.hph-inline-edit');
        const form = inlineEdit.querySelector('.hph-inline-edit__form');
        
        // Hide display, show form
        displayElement.style.display = 'none';
        form.style.display = 'flex';
        
        // Focus the input
        const input = form.querySelector('.hph-inline-edit__input, .hph-inline-edit__select');
        if (input) {
            input.focus();
            if (input.type === 'text' || input.tagName === 'INPUT') {
                input.select();
            }
        }
    }

    async saveInlineEdit(saveButton) {
        const form = saveButton.closest('.hph-inline-edit__form');
        const inlineEdit = form.closest('.hph-inline-edit');
        const displayElement = inlineEdit.querySelector('.hph-inline-edit__display');
        const input = form.querySelector('.hph-inline-edit__input, .hph-inline-edit__select');
        const listingCard = inlineEdit.closest('.hph-listing-card');
        
        const listingId = input.dataset.listingId;
        const field = input.dataset.field;
        const newValue = input.value;
        
        if (!newValue.trim()) {
            this.showAlert('Value cannot be empty', 'warning');
            return;
        }

        // Show loading state
        this.showLoadingState(listingCard, true);
        
        try {
            const response = await fetch(hphDashboard.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'update_listing_field',
                    nonce: hphDashboard.nonce,
                    listing_id: listingId,
                    field: field,
                    value: newValue
                })
            });

            const result = await response.json();

            if (result.success) {
                // Update display value
                if (field === 'price') {
                    displayElement.textContent = '$' + parseInt(newValue).toLocaleString();
                } else if (field === 'status') {
                    displayElement.textContent = 'Status: ' + newValue.charAt(0).toUpperCase() + newValue.slice(1);
                    // Update badge if it exists
                    const badge = listingCard.querySelector('[data-field="status"]');
                    if (badge) {
                        badge.textContent = newValue.charAt(0).toUpperCase() + newValue.slice(1);
                        badge.className = `hph-badge hph-badge--${newValue.toLowerCase()}`;
                    }
                }

                // Hide form, show display
                form.style.display = 'none';
                displayElement.style.display = 'inline-block';
                
                this.showAlert('Updated successfully!', 'success');
            } else {
                throw new Error(result.data || 'Update failed');
            }
        } catch (error) {
            console.error('Inline edit error:', error);
            this.showAlert('Failed to update: ' + error.message, 'danger');
        } finally {
            this.showLoadingState(listingCard, false);
        }
    }

    cancelInlineEdit(cancelButton) {
        const form = cancelButton.closest('.hph-inline-edit__form');
        const inlineEdit = form.closest('.hph-inline-edit');
        const displayElement = inlineEdit.querySelector('.hph-inline-edit__display');
        const input = form.querySelector('.hph-inline-edit__input, .hph-inline-edit__select');
        
        // Reset input value to original
        const originalValue = displayElement.dataset.originalValue || displayElement.textContent;
        if (input.dataset.field === 'price') {
            input.value = originalValue.replace(/[$,]/g, '');
        }
        
        // Hide form, show display
        form.style.display = 'none';
        displayElement.style.display = 'inline-block';
    }

    /**
     * Image Upload Functionality
     */
    bindImageUploadEvents() {
        const uploadArea = document.getElementById('imageUploadArea');
        const fileInput = document.getElementById('property_images');
        const previewContainer = document.getElementById('imagePreviewContainer');

        if (!uploadArea || !fileInput || !previewContainer) return;

        // Drag and drop events
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            this.handleFileSelection(files, previewContainer);
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            this.handleFileSelection(e.target.files, previewContainer);
        });
    }

    handleFileSelection(files, previewContainer) {
        previewContainer.innerHTML = '';

        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'hph-image-preview';
                    previewDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Property Image ${index + 1}">
                        <div class="hph-image-preview-actions">
                            ${index === 0 ? '<span class="hph-btn hph-btn--xs hph-btn--primary">Featured</span>' : ''}
                            <button type="button" class="hph-btn hph-btn--xs hph-btn--danger" onclick="this.closest('.hph-image-preview').remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    previewContainer.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    /**
     * Listing Action Buttons
     */
    bindListingActions() {
        // Edit full listing
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="edit-listing-full"]') || 
                e.target.closest('[data-action="edit-listing-full"]')) {
                e.preventDefault();
                const button = e.target.closest('[data-action="edit-listing-full"]');
                const listingId = button.dataset.listingId;
                this.openFullListingEditor(listingId);
            }
        });

        // Duplicate listing
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="duplicate-listing"]') || 
                e.target.closest('[data-action="duplicate-listing"]')) {
                e.preventDefault();
                const button = e.target.closest('[data-action="duplicate-listing"]');
                const listingId = button.dataset.listingId;
                this.duplicateListing(listingId);
            }
        });

        // Delete listing
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="delete-listing"]') || 
                e.target.closest('[data-action="delete-listing"]')) {
                e.preventDefault();
                const button = e.target.closest('[data-action="delete-listing"]');
                const listingId = button.dataset.listingId;
                this.deleteListing(listingId);
            }
        });
    }

    async openFullListingEditor(listingId) {
        // This would typically open a modal or navigate to edit page
        // For now, we'll show a placeholder
        this.showAlert('Opening full listing editor...', 'info');
        
        // Could load the comprehensive form in a modal
        // or redirect to ?section=edit-listing&id=123
        window.location.href = `?section=edit-listing&id=${listingId}`;
    }

    async duplicateListing(listingId) {
        if (!confirm('Are you sure you want to duplicate this listing?')) return;

        try {
            const response = await fetch(hphDashboard.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'duplicate_listing',
                    nonce: hphDashboard.nonce,
                    listing_id: listingId
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Listing duplicated successfully!', 'success');
                // Refresh the listings or redirect
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(result.data || 'Duplication failed');
            }
        } catch (error) {
            console.error('Duplicate error:', error);
            this.showAlert('Failed to duplicate listing: ' + error.message, 'danger');
        }
    }

    async deleteListing(listingId) {
        if (!confirm('Are you sure you want to delete this listing? This action cannot be undone.')) return;

        try {
            const response = await fetch(hphDashboard.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'delete_listing',
                    nonce: hphDashboard.nonce,
                    listing_id: listingId
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Listing deleted successfully!', 'success');
                // Remove the listing card from DOM
                const listingCard = document.querySelector(`[data-listing-id="${listingId}"]`);
                if (listingCard) {
                    listingCard.style.transition = 'all 0.3s ease';
                    listingCard.style.opacity = '0';
                    listingCard.style.transform = 'translateX(-100%)';
                    setTimeout(() => listingCard.remove(), 300);
                }
            } else {
                throw new Error(result.data || 'Deletion failed');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showAlert('Failed to delete listing: ' + error.message, 'danger');
        }
    }

    /**
     * Comprehensive Form Submission
     */
    bindFormSubmission() {
        const form = document.getElementById('comprehensive-listing-form');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitListingForm(form);
        });

        // Save as draft
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="save-draft"]')) {
                e.preventDefault();
                this.submitListingForm(form, true);
            }
        });
    }

    async submitListingForm(form, isDraft = false) {
        const formData = new FormData(form);
        formData.append('action', 'save_comprehensive_listing');
        formData.append('nonce', hphDashboard.nonce);
        formData.append('is_draft', isDraft ? '1' : '0');

        // Show loading state
        const submitButton = form.querySelector('[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        try {
            const response = await fetch(hphDashboard.ajaxurl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert(
                    isDraft ? 'Listing saved as draft!' : 'Listing saved successfully!', 
                    'success'
                );
                
                if (!isDraft) {
                    // Redirect to listings or show success page
                    setTimeout(() => {
                        window.location.href = '?section=listings';
                    }, 1500);
                }
            } else {
                throw new Error(result.data || 'Save failed');
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.showAlert('Failed to save listing: ' + error.message, 'danger');
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    }

    /**
     * Utility Methods
     */
    showLoadingState(element, show) {
        const loadingElement = element.querySelector('.hph-card__loading');
        if (loadingElement) {
            loadingElement.style.display = show ? 'flex' : 'none';
        }
    }

    showAlert(message, type) {
        // Create and show alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `hph-alert hph-alert--${type}`;
        alertDiv.innerHTML = `
            <div class="hph-alert__icon">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'times-circle' : 'info-circle'}"></i>
            </div>
            <div class="hph-alert__content">
                <p class="hph-alert__message">${message}</p>
            </div>
        `;

        // Insert at top of dashboard content
        const dashboardContent = document.querySelector('.dashboard-content');
        if (dashboardContent) {
            dashboardContent.insertBefore(alertDiv, dashboardContent.firstChild);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                alertDiv.style.transition = 'all 0.3s ease';
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateY(-100%)';
                setTimeout(() => alertDiv.remove(), 300);
            }, 5000);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new HPH_Enhanced_Dashboard();
});
