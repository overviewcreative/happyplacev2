/**
 * Universal Form Handler for Happy Place Forms
 * 
 * CONSOLIDATED VERSION - Now uses HPH.Forms unified system
 * This file serves as a bridge to maintain compatibility with existing forms
 * 
 * @package HappyPlaceTheme
 * @version 2.0.0 - Consolidated Edition
 */

(function($) {
    'use strict';

    /**
     * Universal Form Handler - Bridge to Unified System
     * 
     * This maintains compatibility while using the new HPH.Forms system
     */
    class HappyPlaceFormHandler {
        constructor() {
            this.init();
        }

        init() {
            console.log('🔗 Universal Form Handler: Compatibility bridge active');
            console.log('ℹ️ Form handling delegated to HPH.Forms unified system');
            
            // This handler is now purely a compatibility bridge
            // All form handling is done by HPH.Forms to prevent duplicate submissions
            
            // Wait a moment to ensure HPH.Forms loads first
            $(document).ready(() => {
                if (typeof HPH !== 'undefined' && HPH.Forms) {
                    console.log('✅ Universal Form Handler: HPH.Forms unified system detected');
                } else {
                    console.error('❌ Universal Form Handler: HPH.Forms not loaded! Forms may not work properly.');
                }
            });
        }

        /**
         * Legacy API compatibility methods (no longer handle forms directly)
         * These methods exist for backwards compatibility only
         */
        
        // Deprecated: Form handling is now done by HPH.Forms
        basicFormHandling() {
            console.warn('⚠️ basicFormHandling() is deprecated. HPH.Forms handles all form submissions.');
        }

        // Deprecated: Form submission is now done by HPH.Forms  
        handleFormSubmission(e) {
            console.warn('⚠️ handleFormSubmission() is deprecated. HPH.Forms handles all form submissions.');
        }
    }

    // Initialize the form handler
    new HappyPlaceFormHandler();
    
    // Legacy compatibility
    window.HappyPlaceFormHandler = HappyPlaceFormHandler;

})(jQuery);
