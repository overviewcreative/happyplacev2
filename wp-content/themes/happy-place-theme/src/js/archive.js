/**
 * Archive JavaScript Bundle
 * Archive pages, search results, and filtering
 */

// Note: Imports removed to fix build system
// Will load dependencies via separate script tags if needed

// Initialize archive functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('%c📋 Happy Place Archive Loaded', 'color: #8b5cf6; font-weight: bold;');
    
    // Initialize archive functionality
    if (window.HPH && window.HPH.Archive && window.HPH.Archive.init) {
        window.HPH.Archive.init();
    }
    
    // Initialize AJAX functionality
    if (window.HPH && window.HPH.ArchiveAjax && window.HPH.ArchiveAjax.init) {
        window.HPH.ArchiveAjax.init();
    }
    
    // Initialize advanced filters
    if (window.HPH && window.HPH.AdvancedFilters && window.HPH.AdvancedFilters.init) {
        window.HPH.AdvancedFilters.init();
    }
});