<?php
/**
 * Contact Form Handler - Delegation Pattern
 *
 * DEPRECATED: Contact form processing now handled by plugin FormRouter.
 * This file maintains legacy compatibility but delegates all business logic.
 *
 * @package HappyPlaceTheme
 * @since 3.0.0 - Original implementation
 * @since 4.2.0 - Converted to delegation pattern
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LEGACY Contact Form Handler - Now delegates to plugin
 *
 * NOTE: All contact form handling has been moved to plugin FormRouter.
 * This class exists only for backward compatibility.
 * New forms should use the delegation pattern in user-interactions.php
 */
class HPH_Contact_Form_Handler {

    public function __construct() {
        // Legacy handlers - now handled by user-interactions.php delegation
        // These registrations are kept for backward compatibility but will be
        // overridden by the delegation pattern at priority 15

        // Test AJAX handler for debugging (still useful)
        add_action('wp_ajax_test_ajax_connectivity', array($this, 'test_ajax_connectivity'));
        add_action('wp_ajax_nopriv_test_ajax_connectivity', array($this, 'test_ajax_connectivity'));

        // Script enqueuing is handled by theme-assets.php to avoid conflicts
        // add_action('wp_enqueue_scripts', array($this, 'enqueue_contact_scripts'));
    }
    
    /**
     * Test AJAX connectivity - Still useful for debugging
     */
    public function test_ajax_connectivity() {
        wp_send_json_success(array('message' => 'AJAX connectivity test passed'));
    }

    // =================================================================
    // REMOVED BUSINESS LOGIC - Now handled by plugin FormRouter
    // =================================================================
    //
    // The following methods have been removed and moved to plugin:
    // - process_contact_form() -> plugin FormRouter::handle_contact_form()
    // - send_contact_email() -> plugin FormRouter
    // - save_contact_submission() -> plugin FormRouter
    // - create_submissions_table() -> plugin FormRouter
    // - get_submissions() -> plugin FormRouter
    //
    // All form processing now uses the delegation pattern in:
    // wp-content/themes/happy-place-theme/includes/ajax/user-interactions.php
}

// Initialize the contact form handler
new HPH_Contact_Form_Handler();
