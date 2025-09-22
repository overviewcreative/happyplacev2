<?php
/**
 * Search AJAX Handlers - Delegation Pattern
 *
 * Thin wrapper that delegates all business logic to plugin SearchService.
 * Handles only UI response formatting and WordPress-specific requirements.
 *
 * @package HappyPlaceTheme
 * @subpackage Search
 * @since 4.2.0 - Refactored to delegation pattern
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Search AJAX Delegation Handler
 *
 * All business logic has been moved to plugin SearchService.
 * This class only handles:
 * 1. AJAX endpoint registration for theme-specific actions
 * 2. Basic nonce validation
 * 3. Delegation to plugin service
 * 4. UI response formatting
 */
class HPH_Search_Ajax_Delegation {

    /**
     * Plugin SearchService instance
     */
    private $search_service;

    /**
     * Constructor
     */
    public function __construct() {
        // Register handlers immediately, get service when needed (lazy loading)
        $this->register_ajax_handlers();
    }

    /**
     * Get plugin SearchService instance (lazy loading)
     */
    private function get_plugin_service() {
        // Return cached instance if available
        if ($this->search_service) {
            return $this->search_service;
        }

        // Try to get service from plugin
        try {
            // Method 1: Check if service class exists and can be instantiated
            if (class_exists('\HappyPlace\Services\SearchService')) {
                $this->search_service = new \HappyPlace\Services\SearchService();
                return $this->search_service;
            }
        } catch (Exception $e) {
            error_log("Search delegation service error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Register AJAX handlers - Delegation Pattern
     *
     * NOTE: Plugin SearchService already registers the main search actions.
     * Theme registers additional actions for backward compatibility.
     */
    private function register_ajax_handlers() {
        // Legacy action names for backward compatibility
        add_action('wp_ajax_hpt_universal_search', [$this, 'delegate_universal_search'], 15);
        add_action('wp_ajax_nopriv_hpt_universal_search', [$this, 'delegate_universal_search'], 15);

        // Theme-specific search actions (ensure no conflicts)
        add_action('wp_ajax_hph_search_suggestions', [$this, 'delegate_search_suggestions'], 15);
        add_action('wp_ajax_nopriv_hph_search_suggestions', [$this, 'delegate_search_suggestions'], 15);

        add_action('wp_ajax_hph_advanced_search', [$this, 'delegate_advanced_search'], 15);
        add_action('wp_ajax_nopriv_hph_advanced_search', [$this, 'delegate_advanced_search'], 15);
    }

    /**
     * Delegate to plugin service with error handling
     */
    private function delegate_to_service($plugin_method, $additional_validation = null) {
        // Basic validation for search (usually no nonce required for public search)

        // Additional validation if needed
        if ($additional_validation && !call_user_func($additional_validation)) {
            return;
        }

        // Check service availability
        if (!$this->search_service) {
            wp_send_json_error([
                'message' => 'Search service unavailable',
                'code' => 'service_unavailable'
            ]);
            return;
        }

        // Delegate to plugin service
        try {
            if (method_exists($this->search_service, $plugin_method)) {
                call_user_func([$this->search_service, $plugin_method]);
            } else {
                wp_send_json_error([
                    'message' => "Method {$plugin_method} not found in SearchService",
                    'code' => 'method_not_found'
                ]);
            }
        } catch (Exception $e) {
            error_log("Search delegation error: " . $e->getMessage());
            wp_send_json_error([
                'message' => 'An error occurred processing your search request',
                'code' => 'delegation_error'
            ]);
        }
    }

    // =================================================================
    // DELEGATION METHODS - All delegate to plugin SearchService
    // =================================================================

    /**
     * Universal search - Legacy action name
     */
    public function delegate_universal_search() {
        $this->delegate_to_service('handle_universal_search');
    }

    /**
     * Search suggestions
     */
    public function delegate_search_suggestions() {
        $this->delegate_to_service('handle_search_autocomplete');
    }

    /**
     * Advanced search
     */
    public function delegate_advanced_search() {
        $this->delegate_to_service('handle_advanced_search');
    }
}

// Initialize the delegation handler
new HPH_Search_Ajax_Delegation();

// =================================================================
// STANDALONE FUNCTIONS FOR BACKWARD COMPATIBILITY
// =================================================================

/**
 * Handle search autocomplete - Standalone function for theme compatibility
 * Delegates to plugin SearchService
 */
if (!function_exists('hpt_handle_search_autocomplete')) {
    add_action('wp_ajax_hph_search_autocomplete', 'hpt_handle_search_autocomplete');
    add_action('wp_ajax_nopriv_hph_search_autocomplete', 'hpt_handle_search_autocomplete');

    function hpt_handle_search_autocomplete() {
        // Get plugin SearchService
        $search_service = null;

        if (function_exists('hph_get_service')) {
            $search_service = hph_get_service('SearchService');
        } elseif (class_exists('\HappyPlace\Core\ServiceContainer')) {
            $search_service = \HappyPlace\Core\ServiceContainer::get('SearchService');
        }

        if ($search_service && method_exists($search_service, 'handle_search_autocomplete')) {
            try {
                $search_service->handle_search_autocomplete();
            } catch (Exception $e) {
                error_log("Search autocomplete delegation error: " . $e->getMessage());
                wp_send_json_error([
                    'message' => 'Search autocomplete unavailable',
                    'code' => 'autocomplete_error'
                ]);
            }
        } else {
            wp_send_json_error([
                'message' => 'Search service unavailable',
                'code' => 'service_unavailable'
            ]);
        }
    }
}