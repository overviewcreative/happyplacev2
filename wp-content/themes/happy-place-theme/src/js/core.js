/**
 * Core JavaScript Bundle
 * Essential functionality loaded on all pages
 */

// Initialize core functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('%c🏠 Happy Place Theme Core Loaded', 'color: #2563eb; font-weight: bold;');
    
    // Initialize basic theme functionality
    window.HPH = window.HPH || {};
    
    // Basic utility functions
    window.HPH.utils = {
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        throttle: function(func, limit) {
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
        }
    };
    
    console.log('HPH core utilities initialized');
});