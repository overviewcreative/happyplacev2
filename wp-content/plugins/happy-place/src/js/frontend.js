/**
 * Frontend JavaScript for Happy Place Plugin
 */

import '../scss/frontend.scss';

class HappyPlaceFrontend {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializeComponents();
        
        console.log('Happy Place Frontend initialized');
    }
    
    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.initListingCards();
            this.initContactForms();
            this.initFilterSidebar();
        });
    }
    
    initializeComponents() {
        // Initialize any frontend components here
        if (typeof window.HappyPlace !== 'undefined') {
            console.log('Happy Place config loaded:', window.HappyPlace);
        }
    }
    
    initListingCards() {
        const listingCards = document.querySelectorAll('.listing-card');
        
        listingCards.forEach(card => {
            // Add hover effects
            card.addEventListener('mouseenter', () => {
                card.classList.add('is-hovered');
            });
            
            card.addEventListener('mouseleave', () => {
                card.classList.remove('is-hovered');
            });
            
            // Track card clicks
            card.addEventListener('click', (e) => {
                if (!e.target.closest('.listing-card__actions')) {
                    this.trackEvent('listing_card_click', {
                        listing_id: card.dataset.listingId
                    });
                }
            });
        });
    }
    
    initContactForms() {
        const forms = document.querySelectorAll('.hp-contact-form');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitContactForm(form);
            });
        });
    }
    
    initFilterSidebar() {
        const filterSidebar = document.querySelector('.listing-filters');
        if (!filterSidebar) return;
        
        const filterInputs = filterSidebar.querySelectorAll('input, select');
        
        filterInputs.forEach(input => {
            input.addEventListener('change', () => {
                this.applyFilters();
            });
        });
    }
    
    async submitContactForm(form) {
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn.textContent;
        
        try {
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;
            
            const formData = new FormData(form);
            formData.append('action', 'hp_submit_contact');
            formData.append('nonce', window.hp_frontend?.nonce || '');
            
            const response = await fetch(window.hp_frontend?.ajax_url || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Message sent successfully!', 'success');
                form.reset();
            } else {
                throw new Error(result.data?.message || 'Failed to send message');
            }
            
        } catch (error) {
            console.error('Contact form error:', error);
            this.showNotification('Error sending message. Please try again.', 'error');
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }
    
    applyFilters() {
        // Implement filter logic
        console.log('Applying filters...');
    }
    
    trackEvent(eventType, data = {}) {
        if (!window.hp_frontend?.ajax_url) return;
        
        fetch(window.hp_frontend.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'hp_track_event',
                nonce: window.hp_frontend.nonce,
                event_type: eventType,
                data: JSON.stringify(data)
            })
        }).catch(error => {
            console.error('Tracking error:', error);
        });
    }
    
    showNotification(message, type = 'info') {
        // Simple notification system
        const notification = document.createElement('div');
        notification.className = `hp-notification hp-notification--${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new HappyPlaceFrontend();
    });
} else {
    new HappyPlaceFrontend();
}