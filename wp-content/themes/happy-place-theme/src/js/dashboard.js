/**
 * Dashboard JavaScript Bundle
 * Agent dashboard functionality
 */

// Note: Imports removed to fix build system
// Will load dependencies via separate script tags if needed

// Initialize dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('%c📊 Happy Place Dashboard Loaded', 'color: #ef4444; font-weight: bold;');
    
    // Initialize dashboard
    if (window.HPH && window.HPH.Dashboard && window.HPH.Dashboard.init) {
        window.HPH.Dashboard.init();
    }
    
    // Initialize data tables
    if (window.HPH && window.HPH.DataTable && window.HPH.DataTable.init) {
        window.HPH.DataTable.init();
    }
    
    // Initialize charts (if Chart.js is available)
    if (typeof Chart !== 'undefined') {
        if (window.HPH && window.HPH.Charts && window.HPH.Charts.init) {
            window.HPH.Charts.init();
        }
    }
});