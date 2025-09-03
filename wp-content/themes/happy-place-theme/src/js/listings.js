/**
 * Listings JavaScript Bundle
 * All listing-related functionality
 */

// Note: Imports removed to fix build system
// Will load dependencies via separate script tags if needed

// Initialize listings functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('%c🏘️ Happy Place Listings Loaded', 'color: #f59e0b; font-weight: bold;');
    
    // Initialize listing interactions
    if (window.HPH && window.HPH.ListingInteractions && window.HPH.ListingInteractions.init) {
        window.HPH.ListingInteractions.init();
    }
    
    // Initialize galleries
    if (window.HPH && window.HPH.Gallery && window.HPH.Gallery.init) {
        window.HPH.Gallery.init();
    }
    
    // Initialize maps (if Google Maps is available)
    if (typeof google !== 'undefined' && google.maps) {
        if (window.HPH && window.HPH.ListingMap && window.HPH.ListingMap.init) {
            window.HPH.ListingMap.init();
        }
    }
    
    // Initialize single listing functionality
    if (document.body.classList.contains('single-listing')) {
        if (window.HPH && window.HPH.SingleListing && window.HPH.SingleListing.init) {
            window.HPH.SingleListing.init();
        }
    }
    
    // Initialize contact forms
    if (window.HPH && window.HPH.ContactForm && window.HPH.ContactForm.init) {
        window.HPH.ContactForm.init();
    }
});