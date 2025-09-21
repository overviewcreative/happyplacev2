<?php
/**
 * User Interaction AJAX Handlers
 *
 * Theme wrapper for user interaction AJAX requests.
 * Business logic has been migrated to HappyPlace\Services\UserInteractionsService.
 * These handlers now delegate to the plugin service for proper separation of concerns.
 *
 * @package HappyPlaceTheme
 * @since 3.1.0
 * @updated 4.1.0 - Migrated to plugin service architecture
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle toggle listing favorite
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_toggle_listing_favorite')) {
    add_action('wp_ajax_toggle_listing_favorite', 'handle_toggle_listing_favorite');
    add_action('wp_ajax_nopriv_toggle_listing_favorite', 'handle_guest_favorite_attempt');

    function handle_toggle_listing_favorite() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_toggle_favorite();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Get listing quick stats for AJAX requests
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_get_listing_stats')) {
    add_action('wp_ajax_get_listing_stats', 'handle_get_listing_stats');
    add_action('wp_ajax_nopriv_get_listing_stats', 'handle_get_listing_stats');

    function handle_get_listing_stats() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_get_listing_stats();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle listing view tracking
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_hph_track_listing_view')) {
    add_action('wp_ajax_hph_track_listing_view', 'handle_hph_track_listing_view');
    add_action('wp_ajax_nopriv_hph_track_listing_view', 'handle_hph_track_listing_view');

    function handle_hph_track_listing_view() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_track_listing_view();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle nearby places search for maps
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_hph_get_nearby_places')) {
    add_action('wp_ajax_hph_get_nearby_places', 'handle_hph_get_nearby_places');
    add_action('wp_ajax_nopriv_hph_get_nearby_places', 'handle_hph_get_nearby_places');

    function handle_hph_get_nearby_places() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_get_nearby_places();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle guest favorite attempt - prompt for registration
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_guest_favorite_attempt')) {
    function handle_guest_favorite_attempt() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_guest_favorite_attempt();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle user engagement tracking
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_track_user_engagement')) {
    add_action('wp_ajax_hph_track_engagement', 'handle_track_user_engagement');
    add_action('wp_ajax_nopriv_hph_track_engagement', 'handle_track_user_engagement');

    function handle_track_user_engagement() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_track_engagement();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle save search requests
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_save_search_request')) {
    add_action('wp_ajax_save_search', 'handle_save_search_request');
    add_action('wp_ajax_nopriv_save_search', 'handle_guest_save_search');

    function handle_save_search_request() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_save_search();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }

    function handle_guest_save_search() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_guest_save_search();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle quick user registration
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_quick_registration')) {
    add_action('wp_ajax_nopriv_quick_register', 'handle_quick_registration');

    function handle_quick_registration() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_quick_registration();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Enhanced listing view tracking with engagement
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_enhanced_listing_view')) {
    add_action('wp_ajax_track_listing_view_enhanced', 'handle_enhanced_listing_view');
    add_action('wp_ajax_nopriv_track_listing_view_enhanced', 'handle_enhanced_listing_view');

    function handle_enhanced_listing_view() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_enhanced_listing_view();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Lead Management AJAX Handlers
 * 
 * Handles lead CRUD operations for dashboard
 */

/**
 * Handle get lead details
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_get_lead_details')) {
    add_action('wp_ajax_get_lead_details', 'handle_get_lead_details');

    function handle_get_lead_details() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_get_lead_details();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle update lead status
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_update_lead_status')) {
    add_action('wp_ajax_update_lead_status', 'handle_update_lead_status');

    function handle_update_lead_status() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_update_lead_status();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle add lead note
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_add_lead_note')) {
    add_action('wp_ajax_add_lead_note', 'handle_add_lead_note');

    function handle_add_lead_note() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_add_lead_note();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle create new lead
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_create_lead')) {
    add_action('wp_ajax_create_lead', 'handle_create_lead');

    function handle_create_lead() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_create_lead();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle assign lead to agent
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_assign_lead_to_agent')) {
    add_action('wp_ajax_assign_lead_to_agent', 'handle_assign_lead_to_agent');

    function handle_assign_lead_to_agent() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_assign_lead_to_agent();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle delete lead
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_delete_lead')) {
    add_action('wp_ajax_delete_lead', 'handle_delete_lead');

    function handle_delete_lead() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_delete_lead();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Handle lead form submissions from contact, buyer guide, and valuation forms
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_lead_form_submission')) {
    add_action('wp_ajax_hph_submit_lead_form', 'handle_lead_form_submission');
    add_action('wp_ajax_nopriv_hph_submit_lead_form', 'handle_lead_form_submission');

    function handle_lead_form_submission() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_lead_form_submission();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Get form modal HTML via AJAX
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_get_form_modal')) {
    add_action('wp_ajax_hph_get_form_modal', 'handle_get_form_modal');
    add_action('wp_ajax_nopriv_hph_get_form_modal', 'handle_get_form_modal');

    function handle_get_form_modal() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_get_form_modal();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}

/**
 * Get form content HTML via AJAX
 * Delegates to UserInteractionsService in plugin
 */
if (!function_exists('handle_get_form_content')) {
    add_action('wp_ajax_hph_get_form_content', 'handle_get_form_content');
    add_action('wp_ajax_nopriv_hph_get_form_content', 'handle_get_form_content');

    function handle_get_form_content() {
        // Delegate to plugin service
        if (class_exists('\HappyPlace\Services\UserInteractionsService')) {
            $service = new \HappyPlace\Services\UserInteractionsService();
            $service->handle_get_form_content();
            return;
        }

        // Fallback if plugin service not available
        wp_send_json_error('User interactions service not available');
    }
}
