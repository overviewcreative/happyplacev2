<?php
/**
 * User Interactions AJAX Handlers - Delegation Pattern
 *
 * Thin wrapper that delegates all business logic to plugin UserInteractionsService.
 * Handles only UI response formatting and WordPress-specific requirements.
 *
 * @package HappyPlaceTheme
 * @subpackage UserInteractions
 * @since 4.2.0 - Refactored to delegation pattern
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * User Interactions AJAX Delegation Handler
 *
 * All business logic has been moved to plugin UserInteractionsService.
 * This class only handles:
 * 1. AJAX endpoint registration for theme-specific actions
 * 2. Basic nonce validation
 * 3. Delegation to plugin service
 * 4. UI response formatting
 */
class HPH_User_Interactions_Ajax_Delegation {

    /**
     * Plugin UserInteractionsService instance
     */
    private $interactions_service;

    /**
     * Constructor
     */
    public function __construct() {
        // Register handlers immediately, get service when needed (lazy loading)
        $this->register_ajax_handlers();
    }

    /**
     * Get plugin UserInteractionsService instance (lazy loading)
     */
    private function get_plugin_service() {
        // Return cached instance if available
        if ($this->interactions_service) {
            return $this->interactions_service;
        }

        // Try to get service from plugin
        try {
            // Method 1: Check if service class exists and can be instantiated
            if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
                $this->interactions_service = new \HappyPlace\Services\UserInteractionsService();
                return $this->interactions_service;
            }
        } catch (Exception $e) {
            error_log("User interactions delegation service error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Register AJAX handlers - Delegation Pattern
     *
     * NOTE: Plugin UserInteractionsService already registers hph_* prefixed actions.
     * Theme registers legacy action names for backward compatibility.
     * All business logic is handled by the plugin.
     */
    private function register_ajax_handlers() {
        // Legacy action names for backward compatibility
        add_action('wp_ajax_toggle_listing_favorite', [$this, 'delegate_toggle_favorite'], 15);
        add_action('wp_ajax_nopriv_toggle_listing_favorite', [$this, 'delegate_guest_favorite'], 15);

        add_action('wp_ajax_get_listing_stats', [$this, 'delegate_get_listing_stats'], 15);
        add_action('wp_ajax_nopriv_get_listing_stats', [$this, 'delegate_get_listing_stats'], 15);

        add_action('wp_ajax_save_search', [$this, 'delegate_save_search'], 15);
        add_action('wp_ajax_nopriv_save_search', [$this, 'delegate_guest_save_search'], 15);

        add_action('wp_ajax_track_listing_view_enhanced', [$this, 'delegate_enhanced_listing_view'], 15);
        add_action('wp_ajax_nopriv_track_listing_view_enhanced', [$this, 'delegate_enhanced_listing_view'], 15);

        // Theme-specific handlers (delegate to plugin with hph_ prefix)
        add_action('wp_ajax_quick_register', [$this, 'delegate_quick_registration'], 15);
        add_action('wp_ajax_nopriv_quick_register', [$this, 'delegate_quick_registration'], 15);

        // Lead management (delegate to plugin)
        add_action('wp_ajax_get_lead_details', [$this, 'delegate_get_lead_details'], 15);
        add_action('wp_ajax_update_lead_status', [$this, 'delegate_update_lead_status'], 15);
        add_action('wp_ajax_add_lead_note', [$this, 'delegate_add_lead_note'], 15);
        add_action('wp_ajax_create_lead', [$this, 'delegate_create_lead'], 15);
        add_action('wp_ajax_assign_lead_to_agent', [$this, 'delegate_assign_lead_to_agent'], 15);
        add_action('wp_ajax_delete_lead', [$this, 'delegate_delete_lead'], 15);

        // Form management (delegate to plugin FormRouter)
        add_action('wp_ajax_hph_route_form', [$this, 'delegate_route_form'], 15);
        add_action('wp_ajax_nopriv_hph_route_form', [$this, 'delegate_route_form'], 15);
        add_action('wp_ajax_hph_submit_lead_form', [$this, 'delegate_submit_lead_form'], 15);
        add_action('wp_ajax_nopriv_hph_submit_lead_form', [$this, 'delegate_submit_lead_form'], 15);
        add_action('wp_ajax_hph_get_form_modal', [$this, 'delegate_get_form_modal'], 15);
        add_action('wp_ajax_nopriv_hph_get_form_modal', [$this, 'delegate_get_form_modal'], 15);
        add_action('wp_ajax_hph_get_form_content', [$this, 'delegate_get_form_content'], 15);
        add_action('wp_ajax_nopriv_hph_get_form_content', [$this, 'delegate_get_form_content'], 15);
        add_action('wp_ajax_hph_contact_form', [$this, 'delegate_contact_form'], 15);
        add_action('wp_ajax_nopriv_hph_contact_form', [$this, 'delegate_contact_form'], 15);
    }

    /**
     * Validate nonce for user interactions
     */
    private function validate_nonce($action = 'hph_user_nonce') {
        $nonce = sanitize_text_field($_POST['nonce'] ?? $_GET['nonce'] ?? '');

        if (!wp_verify_nonce($nonce, $action)) {
            wp_send_json_error([
                'message' => 'Security verification failed',
                'code' => 'invalid_nonce'
            ]);
            return false;
        }

        return true;
    }

    /**
     * Delegate to plugin service with error handling
     */
    private function delegate_to_service($plugin_method, $additional_validation = null) {
        // Basic nonce validation for user interactions
        $nonce = sanitize_text_field($_POST['nonce'] ?? $_GET['nonce'] ?? '');
        if (!$nonce) {
            // Some user interactions don't require nonce (tracking, stats)
            error_log("User interaction without nonce: {$plugin_method}");
        }

        // Additional validation if needed
        if ($additional_validation && !call_user_func($additional_validation)) {
            return;
        }

        // Check service availability
        if (!$this->interactions_service) {
            wp_send_json_error([
                'message' => 'User interactions service unavailable',
                'code' => 'service_unavailable'
            ]);
            return;
        }

        // Delegate to plugin service
        try {
            if (method_exists($this->interactions_service, $plugin_method)) {
                call_user_func([$this->interactions_service, $plugin_method]);
            } else {
                wp_send_json_error([
                    'message' => "Method {$plugin_method} not found in UserInteractionsService",
                    'code' => 'method_not_found'
                ]);
            }
        } catch (Exception $e) {
            error_log("User interactions delegation error: " . $e->getMessage());
            wp_send_json_error([
                'message' => 'An error occurred processing your request',
                'code' => 'delegation_error'
            ]);
        }
    }

    // =================================================================
    // DELEGATION METHODS - All delegate to plugin UserInteractionsService
    // =================================================================

    /**
     * Toggle listing favorite - Legacy action name
     */
    public function delegate_toggle_favorite() {
        $this->delegate_to_service('handle_toggle_favorite');
    }

    /**
     * Guest favorite attempt - Legacy action name
     */
    public function delegate_guest_favorite() {
        $this->delegate_to_service('handle_guest_favorite_attempt');
    }

    /**
     * Get listing stats - Legacy action name
     */
    public function delegate_get_listing_stats() {
        $this->delegate_to_service('handle_get_listing_stats');
    }

    /**
     * Save search - Legacy action name
     */
    public function delegate_save_search() {
        $this->delegate_to_service('handle_save_search');
    }

    /**
     * Guest save search - Legacy action name
     */
    public function delegate_guest_save_search() {
        $this->delegate_to_service('handle_guest_save_search');
    }

    /**
     * Enhanced listing view tracking
     */
    public function delegate_enhanced_listing_view() {
        $this->delegate_to_service('handle_enhanced_listing_view');
    }

    /**
     * Quick user registration
     */
    public function delegate_quick_registration() {
        // For now, return a message that this should be handled by the registration system
        wp_send_json_error([
            'message' => 'Please use the full registration form',
            'code' => 'quick_registration_disabled'
        ]);
    }

    /**
     * Get lead details
     */
    public function delegate_get_lead_details() {
        $this->delegate_to_service('get_lead_details', function() {
            return current_user_can('edit_posts');
        });
    }

    /**
     * Update lead status
     */
    public function delegate_update_lead_status() {
        $this->delegate_to_service('update_lead_status', function() {
            return current_user_can('edit_posts');
        });
    }

    /**
     * Add lead note
     */
    public function delegate_add_lead_note() {
        $this->delegate_to_service('add_lead_note', function() {
            return current_user_can('edit_posts');
        });
    }

    /**
     * Create lead
     */
    public function delegate_create_lead() {
        $this->delegate_to_service('create_lead');
    }

    /**
     * Assign lead to agent
     */
    public function delegate_assign_lead_to_agent() {
        $this->delegate_to_service('assign_lead_to_agent', function() {
            return current_user_can('edit_posts');
        });
    }

    /**
     * Delete lead
     */
    public function delegate_delete_lead() {
        $this->delegate_to_service('delete_lead', function() {
            return current_user_can('delete_posts');
        });
    }

    // =================================================================
    // FORM DELEGATION METHODS - All delegate to plugin FormRouter
    // =================================================================

    /**
     * Route form submissions to appropriate handlers
     */
    public function delegate_route_form() {
        $this->delegate_to_service('handle_route_form');
    }

    /**
     * Submit lead form
     */
    public function delegate_submit_lead_form() {
        $this->delegate_to_service('handle_lead_form_submission');
    }

    /**
     * Get form modal HTML
     */
    public function delegate_get_form_modal() {
        $this->delegate_to_service('handle_get_form_modal');
    }

    /**
     * Get form content HTML
     */
    public function delegate_get_form_content() {
        $this->delegate_to_service('handle_get_form_content');
    }

    /**
     * Handle contact form submissions
     */
    public function delegate_contact_form() {
        $this->delegate_to_service('handle_contact_form');
    }
}

// Initialize the delegation handler
new HPH_User_Interactions_Ajax_Delegation();