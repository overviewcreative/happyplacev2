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
            if (typeof window.hphDebug !== 'undefined' && window.hphDebug) {
            }
            
            // This handler is now purely a compatibility bridge
            // All form handling is done by HPH.Forms to prevent duplicate submissions
            
            // Wait a moment to ensure HPH.Forms loads first
            $(document).ready(() => {
                if (typeof HPH !== 'undefined' && HPH.Forms) {
                    if (typeof window.hphDebug !== 'undefined' && window.hphDebug) {
                    }
                } else {
                    if (typeof window.hphDebug !== 'undefined' && window.hphDebug) {
                    }
                }
            });
        }

        /**
         * Legacy API compatibility methods (no longer handle forms directly)
         * These methods exist for backwards compatibility only
         */
        
        // Deprecated: Form handling is now done by HPH.Forms
        basicFormHandling() {
        }

        // Deprecated: Form submission is now done by HPH.Forms  
        handleFormSubmission(e) {
        }
    }

    // Initialize the form handler
    new HappyPlaceFormHandler();
    
    // Legacy compatibility
    window.HappyPlaceFormHandler = HappyPlaceFormHandler;

})(jQuery);
