/**
 * Agents JavaScript Bundle
 * Agent pages and interactions
 */

// Note: Imports removed to fix build system
// Will load dependencies via separate script tags if needed

// Initialize agents functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('%c👥 Happy Place Agents Loaded', 'color: #06b6d4; font-weight: bold;');
    
    // Initialize agent archive
    if (window.HPH && window.HPH.AgentArchive && window.HPH.AgentArchive.init) {
        window.HPH.AgentArchive.init();
    }
});