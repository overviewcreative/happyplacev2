/**
 * Form Modal Handler
 * 
 * Handles form modal triggers and dynamic form loading
 * Replaces contact page redirects with inline modals
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

class HPH_FormModal {
    constructor() {
        this.modalElement = null;
        this.currentFormType = 'general-contact';
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.createModalContainer();
            this.bindTriggers();
            this.handleCustomEvents();
        });
    }

    /**
     * Create modal container if it doesn't exist
     */
    createModalContainer() {
        if (!document.getElementById('hph-modal-container')) {
            const modalContainer = document.createElement('div');
            modalContainer.id = 'hph-modal-container';
            document.body.appendChild(modalContainer);
        }
    }

    /**
     * Bind click events to modal triggers
     */
    bindTriggers() {
        // Handle buttons with data-modal-form attribute
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-modal-form]');
            if (trigger) {
                e.preventDefault();
                this.openModal(trigger);
            }
        });

        // Handle links that redirect to /contact/ ONLY for specific modal triggers
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href*="/contact"]');
            // Only intercept if it has modal attributes OR is specifically marked for modal
            if (link && !link.hasAttribute('data-no-modal') && 
                (link.hasAttribute('data-modal-form') || link.classList.contains('modal-trigger'))) {
                console.log('HPH Form Modal: Contact modal trigger clicked', link.href);
                e.preventDefault();
                this.openModal(link);
            }
        });
    }

    /**
     * Handle custom events for opening modals
     */
    handleCustomEvents() {
        document.addEventListener('hph:openFormModal', (e) => {
            this.openModal(null, e.detail);
        });
    }

    /**
     * Open modal with specified form type
     */
    openModal(trigger, options = {}) {
        const config = this.getModalConfig(trigger, options);
        
        // Check if static modal exists (included in footer)
        const staticModal = document.getElementById('hph-form-modal');
        if (staticModal) {
            console.log('HPH Form Modal: Using static modal', config);
            this.modalElement = staticModal;
            this.currentFormType = staticModal.dataset.formTemplate || 'general-contact';
            this.updateModal(config);
            this.showModal();
        } else {
            console.log('HPH Form Modal: Static modal not found, loading dynamically');
            // Fallback to dynamic loading
            this.showLoadingState();
            this.loadModal(config);
        }
    }

    /**
     * Get modal configuration from trigger element or options
     */
    getModalConfig(trigger, options = {}) {
        const defaults = {
            formTemplate: 'general-contact',
            modalTitle: 'Contact Us',
            modalSubtitle: 'Send us a message and we\'ll get back to you soon.',
            modalSize: 'lg',
            closeOnSuccess: true,
            successRedirect: '',
            formArgs: {}
        };

        let config = { ...defaults };

        // Extract from trigger element
        if (trigger) {
            config.formTemplate = trigger.dataset.modalForm || config.formTemplate;
            config.modalTitle = trigger.dataset.modalTitle || config.modalTitle;
            config.modalSubtitle = trigger.dataset.modalSubtitle || config.modalSubtitle;
            config.modalSize = trigger.dataset.modalSize || config.modalSize;
            config.closeOnSuccess = trigger.dataset.closeOnSuccess !== 'false';
            config.successRedirect = trigger.dataset.successRedirect || '';

            // Handle specific form types based on context
            const context = this.detectContext(trigger);
            if (context.formType) {
                config.formTemplate = context.formType;
                config.modalTitle = context.title;
                config.modalSubtitle = context.subtitle;
                config.formArgs = context.formArgs;
            }
        }

        // Override with explicit options
        Object.assign(config, options);

        return config;
    }

    /**
     * Detect context from trigger element to determine form type
     */
    detectContext(trigger) {
        const context = {
            formType: null,
            title: 'Contact Us',
            subtitle: 'Send us a message and we\'ll get back to you soon.',
            formArgs: {}
        };

        // Check if we're on a listing page
        const listingId = document.body.dataset.listingId || 
                         document.querySelector('[data-listing-id]')?.dataset.listingId;
        
        if (listingId) {
            context.formType = 'property-inquiry';
            context.title = 'Inquire About This Property';
            context.subtitle = 'Get more information or schedule a viewing.';
            context.formArgs = {
                listing_id: listingId,
                show_property_details: true
            };
        }

        // Check if we're on an agent page
        const agentId = document.body.dataset.agentId || 
                       document.querySelector('[data-agent-id]')?.dataset.agentId;
        
        if (agentId) {
            context.formType = 'agent-contact';
            context.title = 'Contact This Agent';
            context.subtitle = 'Get in touch for expert real estate guidance.';
            context.formArgs = {
                agent_id: agentId,
                show_agent_details: true
            };
        }

        // Check trigger text for form type hints
        const triggerText = trigger.textContent.toLowerCase();
        
        if (triggerText.includes('schedule') || triggerText.includes('showing') || triggerText.includes('tour')) {
            context.formType = 'showing-request';
            context.title = 'Schedule a Showing';
            context.subtitle = 'Let us know when you\'d like to view this property.';
        } else if (triggerText.includes('valuation') || triggerText.includes('estimate') || triggerText.includes('worth')) {
            context.formType = 'valuation-request';
            context.title = 'Property Valuation Request';
            context.subtitle = 'Get a professional estimate of your property\'s value.';
        }

        return context;
    }

    /**
     * Load modal content via AJAX
     */
    async loadModal(config) {
        try {
            // Check if AJAX URL is available
            const ajaxUrl = window.hph_ajax?.ajax_url || window.hph_ajax?.url || '/wp-admin/admin-ajax.php';
            const nonce = window.hph_ajax?.nonce || '';
            
            if (!nonce) {
                throw new Error('AJAX nonce not available. Please reload the page.');
            }
            
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'hph_get_form_modal',
                    form_template: config.formTemplate,
                    modal_title: config.modalTitle,
                    modal_subtitle: config.modalSubtitle,
                    modal_size: config.modalSize,
                    close_on_success: config.closeOnSuccess,
                    success_redirect: config.successRedirect,
                    form_args: JSON.stringify(config.formArgs),
                    nonce: nonce
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.hideLoadingState();
                this.insertModal(data.data.html);
                this.showModal();
            } else {
                throw new Error(data.data?.message || 'Failed to load modal');
            }
        } catch (error) {
            console.error('Error loading form modal:', error);
            this.hideLoadingState();
            this.showError('Unable to load contact form. Please try again.');
        }
    }

    /**
     * Insert modal HTML into page
     */
    insertModal(html) {
        const container = document.getElementById('hph-modal-container');
        container.innerHTML = html;
        this.modalElement = container.querySelector('.hph-modal');
    }

    /**
     * Update existing modal with new configuration
     */
    updateModal(config) {
        if (!this.modalElement) return;

        // Update modal attributes
        this.modalElement.dataset.formTemplate = config.formTemplate;
        this.modalElement.dataset.closeOnSuccess = config.closeOnSuccess;
        this.modalElement.dataset.successRedirect = config.successRedirect;

        // Update title and subtitle
        const titleElement = this.modalElement.querySelector('.hph-modal-title');
        const subtitleElement = this.modalElement.querySelector('.hph-modal-subtitle-section p');
        
        if (titleElement) titleElement.textContent = config.modalTitle;
        if (subtitleElement) subtitleElement.textContent = config.modalSubtitle;

        // If form type changed, reload form content
        if (this.currentFormType !== config.formTemplate) {
            this.loadFormContent(config);
        }
    }

    /**
     * Load form content into existing modal
     */
    async loadFormContent(config) {
        const formContainer = this.modalElement.querySelector('.hph-modal-form-container');
        if (!formContainer) return;

        try {
            // Show loading in form area
            formContainer.innerHTML = '<div class="text-center p-4"><div class="hph-loading-spinner mx-auto mb-2"></div><p>Loading form...</p></div>';

            // Check if AJAX URL is available
            const ajaxUrl = window.hph_ajax?.ajax_url || window.hph_ajax?.url || '/wp-admin/admin-ajax.php';
            const nonce = window.hph_ajax?.nonce || '';
            
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'hph_get_form_content',
                    form_template: config.formTemplate,
                    form_args: JSON.stringify(config.formArgs),
                    nonce: nonce
                })
            });

            const data = await response.json();
            
            if (data.success) {
                formContainer.innerHTML = data.data.html;
                this.currentFormType = config.formTemplate;
            } else {
                throw new Error(data.data?.message || 'Failed to load form');
            }
        } catch (error) {
            console.error('Error loading form content:', error);
            formContainer.innerHTML = '<div class="alert alert-danger">Unable to load form. Please try again.</div>';
        }
    }

    /**
     * Show modal - uses Happy Place framework
     */
    showModal() {
        if (this.modalElement) {
            // Use Happy Place modal framework
            this.modalElement.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            requestAnimationFrame(() => {
                this.modalElement.classList.add('active');
            });
        }
    }

    /**
     * Show loading state
     */
    showLoadingState() {
        // Create a simple loading overlay
        const overlay = document.createElement('div');
        overlay.id = 'hph-modal-loading-overlay';
        overlay.className = 'hph-modal-loading-overlay';
        overlay.innerHTML = `
            <div class="hph-modal-loading-content">
                <div class="hph-loading-spinner"></div>
                <p>Loading contact form...</p>
            </div>
        `;
        
        document.body.appendChild(overlay);
        document.body.classList.add('hph-modal-loading');
    }

    /**
     * Hide loading state
     */
    hideLoadingState() {
        const overlay = document.getElementById('hph-modal-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
        document.body.classList.remove('hph-modal-loading');
    }

    /**
     * Show error message
     */
    showError(message) {
        // Simple alert for now - could be enhanced with a proper error modal
        alert(message);
    }

    /**
     * Public method to open modal programmatically
     */
    static open(options = {}) {
        document.dispatchEvent(new CustomEvent('hph:openFormModal', {
            detail: options
        }));
    }
}

// Initialize when loaded
const hphFormModal = new HPH_FormModal();

// Global helper function
window.hphOpenFormModal = function(options) {
    HPH_FormModal.open(options);
};

// CSS for loading overlay
const style = document.createElement('style');
style.textContent = `
.hph-modal-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.hph-modal-loading-content {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.hph-modal-loading-content .hph-loading-spinner {
    width: 2rem;
    height: 2rem;
    border: 3px solid #e5e7eb;
    border-top: 3px solid var(--hph-primary);
    border-radius: 50%;
    animation: hph-spin 1s linear infinite;
    margin: 0 auto 1rem;
}

.hph-modal-loading-content p {
    margin: 0;
    color: #6b7280;
}

@keyframes hph-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

body.hph-modal-loading {
    overflow: hidden;
}
`;
document.head.appendChild(style);
