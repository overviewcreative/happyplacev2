/**
 * Enhanced Lazy Loading System
 * 
 * Improved lazy loading with smooth fade-in transitions,
 * error handling, and performance optimizations
 * 
 * @package HappyPlaceTheme
 * @version 3.2.1
 */

(function() {
    'use strict';
    
    /**
     * Enhanced Lazy Loading Class
     */
    class EnhancedLazyLoading {
        constructor(options = {}) {
            this.options = {
                selector: 'img[data-src]',
                rootMargin: '50px 0px',
                threshold: 0.1,
                fadeInDuration: 600,
                enableBlurEffect: true,
                enableErrorHandling: true,
                enableProgressiveLoading: true,
                retryAttempts: 3,
                retryDelay: 1000,
                ...options
            };
            
            this.imageObserver = null;
            this.loadedImages = new Set();
            this.errorImages = new Set();
            this.retryCount = new Map();
            
            this.init();
        }
        
        /**
         * Initialize lazy loading
         */
        init() {
            // Check for browser support
            if (!this.isSupported()) {
                this.fallbackToEagerLoading();
                return;
            }
            
            // Create intersection observer
            this.createObserver();
            
            // Process existing images
            this.observeImages();
            
            // Listen for new images added to DOM
            this.observeNewImages();
            
            // Add utility methods to global scope
            this.exposeUtilities();
        }
        
        /**
         * Check if lazy loading is supported
         */
        isSupported() {
            return 'IntersectionObserver' in window && 
                   'Promise' in window;
        }
        
        /**
         * Create intersection observer
         */
        createObserver() {
            const observerOptions = {
                rootMargin: this.options.rootMargin,
                threshold: this.options.threshold
            };
            
            this.imageObserver = new IntersectionObserver(
                this.handleIntersection.bind(this),
                observerOptions
            );
        }
        
        /**
         * Handle intersection observer callback
         */
        handleIntersection(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadImage(entry.target);
                    this.imageObserver.unobserve(entry.target);
                }
            });
        }
        
        /**
         * Load image with enhanced features
         */
        async loadImage(img) {
            if (this.loadedImages.has(img) || this.errorImages.has(img)) {
                return;
            }
            
            const dataSrc = img.dataset.src;
            const dataSrcset = img.dataset.srcset;
            if (!dataSrc) return;
            
            try {
                // Add loading class
                img.classList.add('hph-loading');
                
                // Show loading placeholder if enabled
                if (this.options.enableProgressiveLoading) {
                    this.showLoadingPlaceholder(img);
                }
                
                // Load the image
                await this.loadImageWithRetry(img, dataSrc, dataSrcset);
                
                // Handle successful load
                this.handleImageLoad(img, dataSrc, dataSrcset);
                
            } catch (error) {
                // Handle error
                this.handleImageError(img, error);
            }
        }
        
        /**
         * Load image with retry logic
         */
        loadImageWithRetry(img, src, srcset = null) {
            return new Promise((resolve, reject) => {
                const loadImage = (attemptNumber = 1) => {
                    const image = new Image();
                    
                    image.onload = () => {
                        resolve(image);
                    };
                    
                    image.onerror = () => {
                        if (attemptNumber < this.options.retryAttempts) {
                            setTimeout(() => {
                                loadImage(attemptNumber + 1);
                            }, this.options.retryDelay * attemptNumber);
                        } else {
                            reject(new Error(`Failed to load image after ${this.options.retryAttempts} attempts`));
                        }
                    };
                    
                    // Set srcset first if available (better for responsive images)
                    if (srcset) {
                        image.srcset = srcset;
                    }
                    
                    // Set source to trigger load
                    image.src = src;
                    
                    // Store retry count
                    this.retryCount.set(img, attemptNumber);
                };
                
                loadImage();
            });
        }
        
        /**
         * Handle successful image load
         */
        handleImageLoad(img, src, srcset = null) {
            // Update image source
            img.src = src;
            
            // Update srcset if available
            if (srcset) {
                img.srcset = srcset;
            }
            
            // Mark as loaded
            this.loadedImages.add(img);
            
            // Remove loading state
            img.classList.remove('hph-loading');
            
            // Add loaded class with delay for smooth transition
            requestAnimationFrame(() => {
                img.classList.add('hph-loaded');
            });
            
            // Remove data attributes
            delete img.dataset.src;
            if (img.dataset.srcset) {
                delete img.dataset.srcset;
            }
            
            // Hide loading placeholder
            if (this.options.enableProgressiveLoading) {
                this.hideLoadingPlaceholder(img);
            }
            
            // Trigger custom event
            img.dispatchEvent(new CustomEvent('hph-image-loaded', {
                detail: { 
                    src: src, 
                    srcset: srcset,
                    retryCount: this.retryCount.get(img) || 1 
                }
            }));
            
            // Clean up retry count
            this.retryCount.delete(img);
        }
        
        /**
         * Handle image loading error
         */
        handleImageError(img, error) {
            
            // Mark as error
            this.errorImages.add(img);
            
            // Remove loading state
            img.classList.remove('hph-loading');
            
            if (this.options.enableErrorHandling) {
                // Add error class
                img.classList.add('hph-image-error');
                
                // Set fallback image if specified
                const fallbackSrc = img.dataset.fallback;
                if (fallbackSrc && fallbackSrc !== img.dataset.src) {
                    img.dataset.src = fallbackSrc;
                    // Retry with fallback
                    setTimeout(() => {
                        this.errorImages.delete(img);
                        this.loadImage(img);
                    }, 1000);
                }
            }
            
            // Trigger error event
            img.dispatchEvent(new CustomEvent('hph-image-error', {
                detail: { error: error.message, src: img.dataset.src }
            }));
        }
        
        /**
         * Show loading placeholder
         */
        showLoadingPlaceholder(img) {
            // Add skeleton effect based on image context
            if (img.closest('.hph-gallery')) {
                img.classList.add('hph-gallery-lazy');
            } else if (img.closest('.hph-card')) {
                img.classList.add('hph-card-image-lazy');
            } else if (img.closest('.hph-hero')) {
                img.classList.add('hph-hero-lazy');
            } else if (this.options.enableBlurEffect && img.dataset.blurSrc) {
                // Use low-quality blur placeholder
                img.src = img.dataset.blurSrc;
                img.classList.add('hph-lazy-blur');
            } else {
                img.classList.add('hph-lazy-enhanced');
            }
        }
        
        /**
         * Hide loading placeholder
         */
        hideLoadingPlaceholder(img) {
            // Remove placeholder classes after a short delay
            setTimeout(() => {
                const placeholderClasses = [
                    'hph-gallery-lazy',
                    'hph-card-image-lazy', 
                    'hph-hero-lazy',
                    'hph-lazy-blur',
                    'hph-lazy-enhanced'
                ];
                
                placeholderClasses.forEach(className => {
                    if (img.classList.contains(className)) {
                        img.classList.remove(className);
                    }
                });
            }, this.options.fadeInDuration + 100);
        }
        
        /**
         * Observe existing images
         */
        observeImages() {
            const images = document.querySelectorAll(this.options.selector);
            images.forEach(img => this.imageObserver.observe(img));
        }
        
        /**
         * Observe new images added to DOM
         */
        observeNewImages() {
            if ('MutationObserver' in window) {
                const mutationObserver = new MutationObserver(mutations => {
                    mutations.forEach(mutation => {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                // Check if the node itself matches
                                if (node.matches && node.matches(this.options.selector)) {
                                    this.imageObserver.observe(node);
                                }
                                
                                // Check for matching children
                                const childImages = node.querySelectorAll && node.querySelectorAll(this.options.selector);
                                if (childImages) {
                                    childImages.forEach(img => this.imageObserver.observe(img));
                                }
                            }
                        });
                    });
                });
                
                mutationObserver.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
        }
        
        /**
         * Fallback for unsupported browsers
         */
        fallbackToEagerLoading() {
            const images = document.querySelectorAll(this.options.selector);
            images.forEach(img => {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.classList.add('hph-loaded');
                    delete img.dataset.src;
                }
            });
            
            // Add fallback class to document
            document.documentElement.classList.add('no-intersection-observer');
        }
        
        /**
         * Expose utility methods
         */
        exposeUtilities() {
            // Make utilities available globally
            window.HPH = window.HPH || {};
            window.HPH.lazyLoading = {
                loadImage: (selector) => {
                    const img = typeof selector === 'string' ? 
                        document.querySelector(selector) : selector;
                    if (img) this.loadImage(img);
                },
                
                refreshObserver: () => {
                    this.observeImages();
                },
                
                getStats: () => ({
                    loaded: this.loadedImages.size,
                    errors: this.errorImages.size,
                    pending: document.querySelectorAll(this.options.selector).length
                })
            };
        }
        
        /**
         * Destroy lazy loading instance
         */
        destroy() {
            if (this.imageObserver) {
                this.imageObserver.disconnect();
            }
            
            this.loadedImages.clear();
            this.errorImages.clear();
            this.retryCount.clear();
        }
    }
    
    /**
     * Initialize enhanced lazy loading when DOM is ready
     */
    function initEnhancedLazyLoading() {
        // Check if already initialized
        if (window.HPH && window.HPH.enhancedLazyLoading) {
            return;
        }
        
        // Create instance with theme-specific options
        const lazyLoader = new EnhancedLazyLoading({
            selector: 'img[data-src], img[data-srcset], img[loading="lazy"][data-src]',
            rootMargin: '100px 0px',
            threshold: 0.1,
            enableBlurEffect: true,
            enableErrorHandling: true,
            enableProgressiveLoading: true
        });
        
        // Store instance globally
        window.HPH = window.HPH || {};
        window.HPH.enhancedLazyLoading = lazyLoader;
        
        // Integrate with existing HPH framework
        if (window.HPH && window.HPH.initLazyLoading) {
            // Replace original lazy loading
            window.HPH.initLazyLoading = () => {
                // Enhanced lazy loading is already active
            };
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEnhancedLazyLoading);
    } else {
        initEnhancedLazyLoading();
    }
    
    // Also initialize on window load as fallback
    window.addEventListener('load', () => {
        if (!window.HPH || !window.HPH.enhancedLazyLoading) {
            initEnhancedLazyLoading();
        }
    });
    
})();
