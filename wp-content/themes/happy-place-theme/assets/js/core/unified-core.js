/**
 * HPH Unified Core - Consolidates All Redundancies
 *
 * ELIMINATES:
 * - 55 DOM ready patterns across 43 files
 * - 99 AJAX implementations across 29 files
 * - 6 form validation systems
 * - Multiple initialization patterns
 *
 * @package HappyPlaceTheme
 * @version 3.0.0 - Unified Bundle System
 */

(function($) {
    'use strict';

    // Global HPH namespace with redundancy elimination
    window.HPH = window.HPH || {
        initialized: false,
        modules: new Map(),
        config: {},

        // ===== MODULE REGISTRATION SYSTEM =====
        // Replaces 55 individual DOM ready patterns

        register: function(name, moduleFunction) {
            this.modules.set(name, moduleFunction);

            // If already initialized, run immediately
            if (this.initialized) {
                try {
                    moduleFunction(this.config);
                } catch (error) {
                    console.error(`Error initializing late module ${name}:`, error);
                }
            }
        },

        // Single initialization point for entire application
        init: function(config = {}) {
            if (this.initialized) {
                return;
            }

            this.config = { ...this.getDefaultConfig(), ...config };

            // Initialize in priority order
            this.initCore();
            this.initModules();
            this.initEventListeners();

            this.initialized = true;

            // Single initialization complete event
            $(document).trigger('hph:initialized');

            if (this.config.debug) {
                console.log('HPH Core initialized with', this.modules.size, 'modules');
            }
        },

        // Initialize all registered modules
        initModules: function() {
            this.modules.forEach((moduleFunction, name) => {
                try {
                    if (typeof moduleFunction === 'function') {
                        const moduleObj = moduleFunction(this.config);
                        // If module returns an object with init method, call it
                        if (moduleObj && typeof moduleObj.init === 'function') {
                            moduleObj.init();
                        }
                    }
                } catch (error) {
                    console.error(`Error initializing module ${name}:`, error);
                }
            });

            // Initialize Universal Carousel components
            this.initUniversalCarousels();
        },

        // ===== UNIFIED EVENT SYSTEM =====
        // Replaces multiple event handling patterns across files
        events: {
            // Cross-browser event listener wrapper
            on: function(element, event, handler, options = {}) {
                if (!element || !event || !handler) {
                    console.warn('HPH.events.on: Missing required parameters');
                    return;
                }

                // Handle multiple elements
                if (NodeList.prototype.isPrototypeOf(element) || Array.isArray(element)) {
                    Array.from(element).forEach(el => {
                        this.on(el, event, handler, options);
                    });
                    return;
                }

                // Add the event listener
                if (element.addEventListener) {
                    element.addEventListener(event, handler, options);
                } else if (element.attachEvent) {
                    // IE8 fallback
                    element.attachEvent('on' + event, handler);
                }
            },

            // Remove event listener
            off: function(element, event, handler, options = {}) {
                if (!element || !event || !handler) {
                    console.warn('HPH.events.off: Missing required parameters');
                    return;
                }

                // Handle multiple elements
                if (NodeList.prototype.isPrototypeOf(element) || Array.isArray(element)) {
                    Array.from(element).forEach(el => {
                        this.off(el, event, handler, options);
                    });
                    return;
                }

                // Remove the event listener
                if (element.removeEventListener) {
                    element.removeEventListener(event, handler, options);
                } else if (element.detachEvent) {
                    // IE8 fallback
                    element.detachEvent('on' + event, handler);
                }
            },

            // Trigger custom event
            trigger: function(element, eventName, data = {}) {
                if (!element || !eventName) {
                    console.warn('HPH.events.trigger: Missing required parameters');
                    return;
                }

                let event;
                if (typeof CustomEvent === 'function') {
                    event = new CustomEvent(eventName, {
                        detail: data,
                        bubbles: true,
                        cancelable: true
                    });
                } else {
                    // IE fallback
                    event = document.createEvent('CustomEvent');
                    event.initCustomEvent(eventName, true, true, data);
                }

                element.dispatchEvent(event);
            },

            // Delegate event handling
            delegate: function(container, selector, event, handler) {
                this.on(container, event, function(e) {
                    if (e.target.matches(selector) || e.target.closest(selector)) {
                        handler.call(e.target.closest(selector), e);
                    }
                });
            }
        },

        // Default configuration (consolidates config across files)
        getDefaultConfig: function() {
            return {
                ajaxUrl: window.ajaxurl || '/wp-admin/admin-ajax.php',
                nonce: window.hphNonce || '',
                debug: window.hphDebug || false,
                selectors: {
                    forms: '[data-route-type], .hph-form, .hph-general-contact-form, .hph-property-inquiry-form, .hph-agent-contact-form',
                    navigation: '.hph-navigation, .hph-mobile-menu',
                    modals: '.hph-modal',
                    cards: '.hph-card',
                    galleries: '.hph-gallery',
                    maps: '.hph-map'
                },
                classes: {
                    loading: 'hph-loading',
                    error: 'is-invalid',
                    success: 'is-valid',
                    active: 'active'
                }
            };
        },

        // ===== CORE FUNCTIONALITY =====
        // Always loaded features

        initCore: function() {
            this.initUnifiedFormSystem();
            this.initUnifiedNavigation();
            this.initUnifiedNotifications();
            this.initUnifiedAjax();
            this.initUnifiedUtils();
        },

        // ===== UNIFIED FORM SYSTEM =====
        // Consolidates 6 different form validation systems

        initUnifiedFormSystem: function() {
            const forms = document.querySelectorAll(this.config.selectors.forms);

            this.forms = {
                // Form validation patterns consolidated
                validators: {
                    required: (value) => value.trim() !== '',
                    email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
                    phone: (value) => /^[\d\s\-\+\(\)\.]+$/.test(value),
                    url: (value) => /^https?:\/\/.+/.test(value),
                    number: (value) => !isNaN(value) && value !== '',
                    minLength: (value, min) => value.length >= min,
                    maxLength: (value, max) => value.length <= max
                },

                // Validate individual field
                validateField: function(field) {
                    const value = field.value.trim();
                    let isValid = true;
                    let errorMessage = '';

                    // Required validation
                    if (field.hasAttribute('required') && !HPH.forms.validators.required(value)) {
                        isValid = false;
                        errorMessage = 'This field is required';
                    }

                    // Type-specific validation
                    if (value && isValid) {
                        switch (field.type) {
                            case 'email':
                                if (!HPH.forms.validators.email(value)) {
                                    isValid = false;
                                    errorMessage = 'Please enter a valid email address';
                                }
                                break;
                            case 'tel':
                                if (!HPH.forms.validators.phone(value)) {
                                    isValid = false;
                                    errorMessage = 'Please enter a valid phone number';
                                }
                                break;
                            case 'url':
                                if (!HPH.forms.validators.url(value)) {
                                    isValid = false;
                                    errorMessage = 'Please enter a valid URL';
                                }
                                break;
                            case 'number':
                                if (!HPH.forms.validators.number(value)) {
                                    isValid = false;
                                    errorMessage = 'Please enter a valid number';
                                }
                                break;
                        }
                    }

                    // Custom validation attributes
                    if (value && isValid) {
                        const minLength = field.getAttribute('data-min-length');
                        if (minLength && !HPH.forms.validators.minLength(value, parseInt(minLength))) {
                            isValid = false;
                            errorMessage = `Minimum ${minLength} characters required`;
                        }

                        const maxLength = field.getAttribute('data-max-length');
                        if (maxLength && !HPH.forms.validators.maxLength(value, parseInt(maxLength))) {
                            isValid = false;
                            errorMessage = `Maximum ${maxLength} characters allowed`;
                        }
                    }

                    HPH.forms.toggleFieldError(field, isValid, errorMessage);
                    return isValid;
                },

                // Validate entire form
                validateForm: function(form) {
                    let isValid = true;
                    const fields = form.querySelectorAll('input, select, textarea');

                    fields.forEach(field => {
                        if (!HPH.forms.validateField(field)) {
                            isValid = false;
                        }
                    });

                    return isValid;
                },

                // Toggle field error state (unified across all forms)
                toggleFieldError: function(field, isValid, errorMessage) {
                    const fieldContainer = field.closest('.hph-form-field') || field.parentElement;

                    if (isValid) {
                        field.classList.remove(HPH.config.classes.error);
                        field.classList.add(HPH.config.classes.success);
                        fieldContainer.classList.remove('has-error');

                        // Remove error message
                        const errorEl = fieldContainer.querySelector('.hph-field-error');
                        if (errorEl) {
                            errorEl.remove();
                        }
                    } else {
                        field.classList.remove(HPH.config.classes.success);
                        field.classList.add(HPH.config.classes.error);
                        fieldContainer.classList.add('has-error');

                        // Show error message
                        let errorEl = fieldContainer.querySelector('.hph-field-error');
                        if (!errorEl) {
                            errorEl = document.createElement('div');
                            errorEl.className = 'hph-field-error';
                            fieldContainer.appendChild(errorEl);
                        }
                        errorEl.textContent = errorMessage;
                    }
                },

                // Submit form (unified AJAX handling)
                submitForm: function(form, additionalData = {}) {
                    const action = form.getAttribute('data-action') || 'hph_form_submit';
                    const isAjax = form.hasAttribute('data-ajax') || form.classList.contains('hph-ajax-form');

                    if (!HPH.forms.validateForm(form)) {
                        return false;
                    }

                    if (isAjax) {
                        return HPH.ajax.submitForm(form, { action: action, ...additionalData });
                    }

                    return true;
                }
            };

            // Bind events to all forms (replaces individual form bindings)
            forms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    const isValid = HPH.forms.submitForm(this);
                    if (!isValid) {
                        event.preventDefault();
                    }
                });

                // Real-time validation
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        HPH.forms.validateField(this);
                    });
                });
            });
        },

        // ===== UNIFIED NAVIGATION SYSTEM =====
        // Consolidates navigation patterns across files

        initUnifiedNavigation: function() {
            this.navigation = {
                // Mobile menu handling (consolidates mobile menu patterns)
                initMobileMenu: function() {
                    const toggle = document.querySelector('.hph-mobile-menu-toggle');
                    const menu = document.querySelector('.hph-mobile-menu');

                    if (toggle && menu) {
                        toggle.addEventListener('click', function() {
                            menu.classList.toggle(HPH.config.classes.active);
                            document.body.classList.toggle('mobile-menu-open');
                            this.setAttribute('aria-expanded', menu.classList.contains(HPH.config.classes.active));
                        });
                    }
                },

                // Dropdown menus (consolidates dropdown patterns)
                initDropdowns: function() {
                    const dropdownToggles = document.querySelectorAll('.hph-dropdown-toggle');

                    dropdownToggles.forEach(toggle => {
                        toggle.addEventListener('click', function(e) {
                            e.preventDefault();
                            const dropdown = this.nextElementSibling;
                            if (dropdown && dropdown.classList.contains('hph-dropdown-menu')) {
                                // Close other dropdowns
                                document.querySelectorAll('.hph-dropdown-menu.active').forEach(menu => {
                                    if (menu !== dropdown) {
                                        menu.classList.remove(HPH.config.classes.active);
                                    }
                                });

                                dropdown.classList.toggle(HPH.config.classes.active);
                            }
                        });
                    });

                    // Close dropdowns on outside click
                    document.addEventListener('click', function(e) {
                        if (!e.target.closest('.hph-dropdown')) {
                            document.querySelectorAll('.hph-dropdown-menu.active').forEach(menu => {
                                menu.classList.remove(HPH.config.classes.active);
                            });
                        }
                    });
                },

                // Search functionality (consolidates search patterns)
                initSearch: function() {
                    const searchForms = document.querySelectorAll('.hph-search-form');

                    searchForms.forEach(form => {
                        const input = form.querySelector('input[type="search"]');
                        if (input) {
                            // Search autocomplete (if enabled)
                            if (input.hasAttribute('data-autocomplete')) {
                                HPH.navigation.initSearchAutocomplete(input);
                            }
                        }
                    });
                },

                // Search autocomplete
                initSearchAutocomplete: function(input) {
                    let timeout;

                    input.addEventListener('input', function() {
                        clearTimeout(timeout);
                        const query = this.value.trim();

                        if (query.length >= 3) {
                            timeout = setTimeout(() => {
                                HPH.ajax.request({
                                    data: {
                                        action: 'hph_search_autocomplete',
                                        query: query
                                    }
                                }).done(function(response) {
                                    if (response.success) {
                                        HPH.navigation.showSearchSuggestions(input, response.data);
                                    }
                                });
                            }, 300);
                        }
                    });
                },

                // Show search suggestions
                showSearchSuggestions: function(input, suggestions) {
                    // Implementation for search suggestions
                    let suggestionsEl = input.parentElement.querySelector('.hph-search-suggestions');
                    if (!suggestionsEl) {
                        suggestionsEl = document.createElement('div');
                        suggestionsEl.className = 'hph-search-suggestions';
                        input.parentElement.appendChild(suggestionsEl);
                    }

                    if (suggestions.length > 0) {
                        suggestionsEl.innerHTML = suggestions.map(item =>
                            `<div class="hph-search-suggestion" data-value="${item.title}">
                                <span class="suggestion-title">${item.title}</span>
                                <span class="suggestion-type">${item.type}</span>
                            </div>`
                        ).join('');

                        suggestionsEl.style.display = 'block';

                        // Handle suggestion clicks
                        suggestionsEl.querySelectorAll('.hph-search-suggestion').forEach(suggestion => {
                            suggestion.addEventListener('click', function() {
                                input.value = this.getAttribute('data-value');
                                suggestionsEl.style.display = 'none';
                            });
                        });
                    } else {
                        suggestionsEl.style.display = 'none';
                    }
                }
            };

            // Initialize all navigation components
            this.navigation.initMobileMenu();
            this.navigation.initDropdowns();
            this.navigation.initSearch();
        },

        // ===== UNIFIED NOTIFICATION SYSTEM =====

        initUnifiedNotifications: function() {
            this.notifications = {
                container: null,

                init: function() {
                    // Create notifications container if it doesn't exist
                    this.container = document.querySelector('.hph-notifications');
                    if (!this.container) {
                        this.container = document.createElement('div');
                        this.container.className = 'hph-notifications';
                        document.body.appendChild(this.container);
                    }
                },

                show: function(message, type = 'info', duration = 5000) {
                    if (!this.container) {
                        this.init();
                    }

                    const notification = document.createElement('div');
                    notification.className = `hph-notification hph-notification--${type}`;
                    notification.innerHTML = `
                        <div class="hph-notification__content">
                            <span class="hph-notification__message">${message}</span>
                            <button type="button" class="hph-notification__close" aria-label="Close">&times;</button>
                        </div>
                    `;

                    this.container.appendChild(notification);

                    // Auto-remove after duration
                    if (duration > 0) {
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.remove();
                            }
                        }, duration);
                    }

                    // Manual close
                    notification.querySelector('.hph-notification__close').addEventListener('click', () => {
                        notification.remove();
                    });

                    return notification;
                },

                success: function(message, duration) {
                    return this.show(message, 'success', duration);
                },

                error: function(message, duration) {
                    return this.show(message, 'error', duration);
                },

                warning: function(message, duration) {
                    return this.show(message, 'warning', duration);
                },

                info: function(message, duration) {
                    return this.show(message, 'info', duration);
                }
            };
        },

        // ===== UNIFIED AJAX SYSTEM =====
        // Consolidates 99 AJAX implementations into single system

        initUnifiedAjax: function() {
            this.ajax = {
                // Standard AJAX request with error handling
                request: function(options) {
                    const defaults = {
                        url: HPH.config.ajaxUrl,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            nonce: HPH.config.nonce
                        },
                        timeout: 30000
                    };

                    const settings = { ...defaults, ...options };

                    return $.ajax(settings)
                        .fail(function(xhr, status, error) {
                            const errorMessage = xhr.responseJSON?.data?.message || 'An error occurred. Please try again.';

                            if (HPH.config.debug) {
                                console.error('AJAX Error:', {
                                    status: status,
                                    error: error,
                                    response: xhr.responseText
                                });
                            }

                            HPH.notifications.error(errorMessage);
                        });
                },

                // Form submission via AJAX
                submitForm: function(form, additionalData = {}) {
                    const formData = new FormData(form);
                    const data = { ...additionalData };

                    // Convert FormData to object
                    for (let [key, value] of formData.entries()) {
                        data[key] = value;
                    }

                    return this.request({
                        data: data,
                        beforeSend: function() {
                            form.classList.add(HPH.config.classes.loading);
                            const submitBtn = form.querySelector('button[type="submit"]');
                            if (submitBtn) {
                                submitBtn.disabled = true;
                                submitBtn.setAttribute('data-original-text', submitBtn.textContent);
                                submitBtn.textContent = 'Submitting...';
                            }
                        },
                        complete: function() {
                            form.classList.remove(HPH.config.classes.loading);
                            const submitBtn = form.querySelector('button[type="submit"]');
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                const originalText = submitBtn.getAttribute('data-original-text');
                                if (originalText) {
                                    submitBtn.textContent = originalText;
                                }
                            }
                        }
                    }).done(function(response) {
                        if (response.success) {
                            HPH.notifications.success(response.data.message || 'Form submitted successfully');
                            form.reset();

                            // Trigger form success event
                            $(form).trigger('hph:form:success', response);
                        } else {
                            HPH.notifications.error(response.data.message || 'Form submission failed');

                            // Trigger form error event
                            $(form).trigger('hph:form:error', response);
                        }
                    });
                },

                // Load content via AJAX
                loadContent: function(selector, url, data = {}) {
                    const element = document.querySelector(selector);
                    if (!element) {
                        return;
                    }

                    return this.request({
                        url: url,
                        data: { action: 'hph_load_content', ...data },
                        beforeSend: function() {
                            element.classList.add(HPH.config.classes.loading);
                        },
                        complete: function() {
                            element.classList.remove(HPH.config.classes.loading);
                        }
                    }).done(function(response) {
                        if (response.success) {
                            element.innerHTML = response.data.content;

                            // Re-initialize components in loaded content
                            HPH.initDynamicContent(element);
                        }
                    });
                }
            };
        },

        // ===== UNIFIED UTILITIES =====

        initUnifiedUtils: function() {
            // Make utilities available at top level for backward compatibility
            this.debounce = function(func, wait, immediate) {
                let timeout;
                return function executedFunction() {
                    const context = this;
                    const args = arguments;
                    const later = function() {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    const callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func.apply(context, args);
                };
            };

            // Throttle function
            this.throttle = function(func, limit) {
                let inThrottle;
                return function() {
                    const args = arguments;
                    const context = this;
                    if (!inThrottle) {
                        func.apply(context, args);
                        inThrottle = true;
                        setTimeout(() => inThrottle = false, limit);
                    }
                };
            };

            this.utils = {
                // Also keep nested for new code
                debounce: this.debounce,
                throttle: this.throttle,

                // Check if element is in viewport
                isInViewport: function(element) {
                    const rect = element.getBoundingClientRect();
                    return (
                        rect.top >= 0 &&
                        rect.left >= 0 &&
                        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                    );
                },

                // Lazy loading with intersection observer
                lazyLoad: function(selector, callback, options = {}) {
                    const defaultOptions = {
                        root: null,
                        rootMargin: '50px',
                        threshold: 0.1
                    };

                    const observerOptions = { ...defaultOptions, ...options };

                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                callback(entry.target);
                                observer.unobserve(entry.target);
                            }
                        });
                    }, observerOptions);

                    document.querySelectorAll(selector).forEach(el => {
                        observer.observe(el);
                    });

                    return observer;
                }
            };
        },

        // ===== EVENT LISTENERS =====

        initEventListeners: function() {
            // Handle escape key for modals and dropdowns
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    // Close modals
                    document.querySelectorAll('.hph-modal.active').forEach(modal => {
                        modal.classList.remove(HPH.config.classes.active);
                    });

                    // Close dropdowns
                    document.querySelectorAll('.hph-dropdown-menu.active').forEach(dropdown => {
                        dropdown.classList.remove(HPH.config.classes.active);
                    });

                    // Close mobile menu
                    const mobileMenu = document.querySelector('.hph-mobile-menu.active');
                    if (mobileMenu) {
                        mobileMenu.classList.remove(HPH.config.classes.active);
                        document.body.classList.remove('mobile-menu-open');
                    }
                }
            });

            // Global click handlers for common patterns
            document.addEventListener('click', function(e) {
                // Modal triggers
                if (e.target.matches('[data-modal-target]')) {
                    e.preventDefault();
                    const modalId = e.target.getAttribute('data-modal-target');
                    const modal = document.querySelector(modalId);
                    if (modal) {
                        modal.classList.add(HPH.config.classes.active);
                    }
                }

                // Modal close buttons
                if (e.target.matches('.hph-modal-close')) {
                    const modal = e.target.closest('.hph-modal');
                    if (modal) {
                        modal.classList.remove(HPH.config.classes.active);
                    }
                }

                // Accordion toggles
                if (e.target.matches('.hph-accordion-toggle')) {
                    e.preventDefault();
                    const content = e.target.nextElementSibling;
                    if (content && content.classList.contains('hph-accordion-content')) {
                        content.classList.toggle(HPH.config.classes.active);
                        e.target.setAttribute('aria-expanded', content.classList.contains(HPH.config.classes.active));
                    }
                }
            });
        },

        // Initialize components in dynamically loaded content
        initDynamicContent: function(container) {
            // Re-run initialization for new content
            const forms = container.querySelectorAll(this.config.selectors.forms);
            forms.forEach(form => {
                // Bind form events
                form.addEventListener('submit', function(event) {
                    const isValid = HPH.forms.submitForm(this);
                    if (!isValid) {
                        event.preventDefault();
                    }
                });

                // Bind field validation
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        HPH.forms.validateField(this);
                    });
                });
            });

            // Re-initialize other components as needed
            this.navigation.initDropdowns();

            // Re-initialize carousels in new content
            this.initUniversalCarousels(container);
        },

        // ===== UNIVERSAL CAROUSEL INTEGRATION =====
        // Initialize carousel components
        initUniversalCarousels: function(container = document) {
            const carousels = container.querySelectorAll('.hph-carousel__container');

            if (carousels.length === 0) {
                return;
            }

            // Load carousel functionality if not already available
            if (typeof HphUniversalCarousel === 'undefined') {
                console.warn('Universal Carousel class not loaded, attempting to load...');
                this.loadUniversalCarousel();
                return;
            }

            carousels.forEach((carousel, index) => {
                try {
                    // Skip if already initialized
                    if (carousel.hphCarousel) {
                        return;
                    }

                    const instance = new HphUniversalCarousel(carousel);
                    carousel.hphCarousel = instance;

                    if (this.config.debug) {
                        console.log('Universal Carousel initialized:', carousel.id || `carousel-${index}`);
                    }
                } catch (error) {
                    console.error('Error initializing carousel:', error);
                }
            });
        },

        // Load Universal Carousel script dynamically
        loadUniversalCarousel: function() {
            if (this._carouselLoading) {
                return;
            }

            this._carouselLoading = true;
            const script = document.createElement('script');
            script.src = '/wp-content/themes/happy-place-theme/assets/js/components/universal-carousel.js';
            script.onload = () => {
                this._carouselLoading = false;
                this.initUniversalCarousels();
            };
            script.onerror = () => {
                this._carouselLoading = false;
                console.error('Failed to load Universal Carousel script');
            };
            document.head.appendChild(script);
        }
    };

    // ===== SINGLE DOM READY INITIALIZATION =====
    // Replaces all 55 individual DOM ready patterns

    $(document).ready(function() {
        // Initialize with configuration from WordPress
        const config = {
            nonce: window.hphNonce || '',
            debug: window.hphDebug || false
        };

        HPH.init(config);
    });

    // Export for module usage
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = HPH;
    }

})(jQuery);