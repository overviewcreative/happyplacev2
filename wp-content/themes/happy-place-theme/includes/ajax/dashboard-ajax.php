<?php
/**
 * Dashboard AJAX Handlers - Delegation Pattern
 *
 * Thin wrapper that delegates all business logic to plugin DashboardService.
 * Handles only UI response formatting and WordPress-specific requirements.
 *
 * @package HappyPlaceTheme
 * @subpackage Dashboard
 * @since 4.2.0 - Refactored to delegation pattern
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard AJAX Delegation Handler
 *
 * All business logic has been moved to plugin DashboardService.
 * This class only handles:
 * 1. AJAX endpoint registration
 * 2. Basic nonce validation
 * 3. Delegation to plugin service
 * 4. UI response formatting
 */
class HPH_Dashboard_Ajax_Delegation {

    /**
     * Plugin DashboardService instance
     */
    private $dashboard_service;

    /**
     * Constructor
     */
    public function __construct() {
        // Register handlers immediately, get service when needed (lazy loading)
        $this->register_ajax_handlers();
    }

    /**
     * Get plugin DashboardService instance (lazy loading)
     */
    private function get_plugin_service() {
        // Return cached instance if available
        if ($this->dashboard_service) {
            return $this->dashboard_service;
        }

        // Try to get service from plugin
        try {
            // Method 1: Check if service class exists and can be instantiated
            if (class_exists('\HappyPlace\Services\DashboardService')) {
                $this->dashboard_service = new \HappyPlace\Services\DashboardService();
                return $this->dashboard_service;
            }

            // Method 2: Check for service container
            if (class_exists('\HappyPlace\Core\Container')) {
                // Service container might be available through global state
                // This is a simplified approach for now
                return null;
            }
        } catch (Exception $e) {
            error_log("Dashboard delegation service error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Register AJAX handlers - Delegation Pattern
     *
     * NOTE: Plugin DashboardService already registers these same actions.
     * Theme handlers run at default priority (10), plugin runs at priority 5.
     * Plugin handles business logic, theme handles UI response formatting.
     */
    private function register_ajax_handlers() {
        // Dashboard Stats & Activity
        add_action('wp_ajax_hph_get_dashboard_stats', [$this, 'delegate_get_dashboard_stats'], 15);
        add_action('wp_ajax_hph_get_recent_activity', [$this, 'delegate_get_recent_activity'], 15);
        add_action('wp_ajax_hph_get_upcoming_events', [$this, 'delegate_get_upcoming_events'], 15);
        add_action('wp_ajax_hph_get_hot_leads', [$this, 'delegate_get_hot_leads'], 15);

        // Listings Management
        add_action('wp_ajax_hph_get_listings', [$this, 'delegate_get_listings'], 15);
        add_action('wp_ajax_hph_get_listing_stats', [$this, 'delegate_get_listing_stats'], 15);
        add_action('wp_ajax_hph_create_listing', [$this, 'delegate_create_listing'], 15);
        add_action('wp_ajax_hph_update_listing', [$this, 'delegate_update_listing'], 15);
        add_action('wp_ajax_hph_delete_listing', [$this, 'delegate_delete_listing'], 15);
        add_action('wp_ajax_hph_update_listing_status', [$this, 'delegate_update_listing_status'], 15);
        add_action('wp_ajax_hph_update_listing_price', [$this, 'delegate_update_listing_price'], 15);

        // Lead Management
        add_action('wp_ajax_hph_create_lead', [$this, 'delegate_create_lead'], 15);
        add_action('wp_ajax_hph_update_lead', [$this, 'delegate_update_lead'], 15);
        add_action('wp_ajax_hph_get_leads', [$this, 'delegate_get_leads'], 15);

        // Profile Management
        add_action('wp_ajax_hph_update_profile', [$this, 'delegate_update_profile'], 15);
        add_action('wp_ajax_hph_upload_profile_image', [$this, 'delegate_upload_profile_image'], 15);

        // Analytics & Export
        add_action('wp_ajax_hph_get_analytics', [$this, 'delegate_get_analytics'], 15);
        add_action('wp_ajax_hph_export_data', [$this, 'delegate_export_data'], 15);
    }

    /**
     * Validate nonce for dashboard operations
     */
    private function validate_nonce($action = 'hph_dashboard_nonce') {
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
    private function delegate_to_service($method, $additional_validation = null) {
        // Validate nonce
        if (!$this->validate_nonce()) {
            return;
        }

        // Additional validation if needed
        if ($additional_validation && !call_user_func($additional_validation)) {
            return;
        }

        // Check service availability
        if (!$this->dashboard_service) {
            wp_send_json_error([
                'message' => 'Dashboard service unavailable',
                'code' => 'service_unavailable'
            ]);
            return;
        }

        // Delegate to plugin service
        try {
            if (method_exists($this->dashboard_service, $method)) {
                call_user_func([$this->dashboard_service, $method]);
            } else {
                wp_send_json_error([
                    'message' => "Method {$method} not found in DashboardService",
                    'code' => 'method_not_found'
                ]);
            }
        } catch (Exception $e) {
            error_log("Dashboard delegation error: " . $e->getMessage());
            wp_send_json_error([
                'message' => 'An error occurred processing your request',
                'code' => 'delegation_error'
            ]);
        }
    }

    // =================================================================
    // DELEGATION METHODS - All delegate to plugin DashboardService
    // =================================================================

    public function delegate_get_dashboard_stats() {
        $this->delegate_to_service('get_dashboard_stats');
    }

    public function delegate_get_recent_activity() {
        $this->delegate_to_service('get_recent_activity');
    }

    public function delegate_get_upcoming_events() {
        $this->delegate_to_service('get_upcoming_events');
    }

    public function delegate_get_hot_leads() {
        $this->delegate_to_service('get_hot_leads');
    }

    public function delegate_get_listings() {
        $this->delegate_to_service('get_listings');
    }

    public function delegate_get_listing_stats() {
        $this->delegate_to_service('get_listing_stats');
    }

    public function delegate_create_listing() {
        $this->delegate_to_service('create_listing', function() {
            return current_user_can('edit_posts');
        });
    }

    public function delegate_update_listing() {
        $this->delegate_to_service('update_listing', function() {
            return current_user_can('edit_posts');
        });
    }

    public function delegate_delete_listing() {
        $this->delegate_to_service('delete_listing', function() {
            return current_user_can('delete_posts');
        });
    }

    public function delegate_update_listing_status() {
        $this->delegate_to_service('update_listing_status', function() {
            return current_user_can('edit_posts');
        });
    }

    public function delegate_update_listing_price() {
        $this->delegate_to_service('update_listing_price', function() {
            return current_user_can('edit_posts');
        });
    }

    public function delegate_create_lead() {
        $this->delegate_to_service('create_lead');
    }

    public function delegate_update_lead() {
        $this->delegate_to_service('update_lead', function() {
            return current_user_can('edit_posts');
        });
    }

    public function delegate_get_leads() {
        $this->delegate_to_service('get_leads');
    }

    public function delegate_update_profile() {
        $this->delegate_to_service('update_profile');
    }

    public function delegate_upload_profile_image() {
        $this->delegate_to_service('upload_profile_image');
    }

    public function delegate_get_analytics() {
        $this->delegate_to_service('get_analytics');
    }

    public function delegate_export_data() {
        $this->delegate_to_service('export_data', function() {
            return current_user_can('export');
        });
    }
}

// Initialize the delegation handler
new HPH_Dashboard_Ajax_Delegation();