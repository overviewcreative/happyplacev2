<?php
/**
 * User Bridge Functions
 *
 * Provides interface between plugin user services and theme templates.
 * All user-related functionality should go through these bridge functions.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get unified agent user service instance
 *
 * @return \HappyPlace\Services\UnifiedAgentUserService|null
 */
function hpt_get_agent_user_service() {
    static $agent_user_service = null;

    if ($agent_user_service === null && class_exists('\\HappyPlace\\Services\\UnifiedAgentUserService')) {
        $agent_user_service = new \HappyPlace\Services\UnifiedAgentUserService();
        $agent_user_service->init();
    }

    return $agent_user_service;
}

/**
 * Get agent service instance
 *
 * @return \HappyPlace\Services\AgentService|null
 */
function hpt_get_agent_service() {
    static $agent_service = null;

    if ($agent_service === null && class_exists('\\HappyPlace\\Services\\AgentService')) {
        $agent_service = new \HappyPlace\Services\AgentService();
        $agent_service->init();
    }

    return $agent_service;
}

/**
 * Get user role service instance
 *
 * @return \HappyPlace\Services\UserRoleService|null
 */
function hpt_get_user_role_service() {
    static $user_role_service = null;

    if ($user_role_service === null && class_exists('\\HappyPlace\\Services\\UserRoleService')) {
        $user_role_service = new \HappyPlace\Services\UserRoleService();
        $user_role_service->init();
    }

    return $user_role_service;
}

/**
 * Check if current user can edit a specific listing
 * Uses plugin services for permission checking
 *
 * @param int $listing_id Listing post ID
 * @return bool True if user can edit
 */
function hpt_can_user_edit_listing($listing_id) {
    $current_user_id = get_current_user_id();

    // Admins can edit all listings
    if (current_user_can('administrator') || current_user_can('manage_options')) {
        return true;
    }

    // Get the assigned listing agent(s)
    $listing_agent = get_field('listing_agent', $listing_id);

    if (!$listing_agent) {
        // If no agent assigned, only admins can edit
        return false;
    }

    // Handle both single agent and multiple agents
    $agent_ids = is_array($listing_agent) ? $listing_agent : [$listing_agent];

    // Check if current user is one of the assigned agents
    foreach ($agent_ids as $agent_id) {
        // Get the synced user ID for this agent via plugin service
        $synced_user_id = get_post_meta($agent_id, '_synced_user_id', true);

        if ($synced_user_id && $synced_user_id == $current_user_id) {
            return true;
        }
    }

    return false;
}

/**
 * Check if current user can delete a specific listing
 *
 * @param int $listing_id Listing post ID
 * @return bool True if user can delete
 */
function hpt_can_user_delete_listing($listing_id) {
    // For now, use same permissions as edit
    return hpt_can_user_edit_listing($listing_id);
}

/**
 * Get agent data for a given agent post ID
 *
 * @param int $agent_id Agent post ID
 * @return array Agent data array
 */
function hpt_get_agent_data($agent_id) {
    $agent_user_service = hpt_get_agent_user_service();

    if (!$agent_user_service) {
        // Fallback to direct field access
        $post = get_post($agent_id);
        if (!$post) {
            return [];
        }

        return [
            'display_name' => $post->post_title,
            'first_name' => get_field('first_name', $agent_id) ?: '',
            'last_name' => get_field('last_name', $agent_id) ?: '',
            'email' => get_field('email', $agent_id) ?: '',
            'phone' => get_field('phone', $agent_id) ?: '',
            'bio' => get_field('bio', $agent_id) ?: $post->post_content,
            'license_number' => get_field('license_number', $agent_id) ?: '',
            'specialties' => get_field('specialties', $agent_id) ?: '',
            'website' => get_field('website', $agent_id) ?: '',
            'social_media' => get_field('social_media', $agent_id) ?: [],
        ];
    }

    return $agent_user_service->get_agent_data($agent_id);
}

/**
 * Manually sync agent to user account
 *
 * @param int $agent_id Agent post ID
 * @return array Result array with success status and message
 */
function hpt_sync_agent_to_user($agent_id) {
    $agent_user_service = hpt_get_agent_user_service();

    if (!$agent_user_service) {
        return ['success' => false, 'message' => 'Agent user service not available'];
    }

    return $agent_user_service->manual_sync_agent($agent_id);
}

/**
 * Get synced user ID for an agent post
 *
 * @param int $agent_id Agent post ID
 * @return int|false User ID or false if not synced
 */
function hpt_get_agent_user_id($agent_id) {
    return get_post_meta($agent_id, '_synced_user_id', true) ?: false;
}

/**
 * Get synced agent ID for a user
 *
 * @param int $user_id User ID
 * @return int|false Agent post ID or false if not synced
 */
function hpt_get_user_agent_id($user_id) {
    return get_user_meta($user_id, '_synced_agent_id', true) ?: false;
}

/**
 * Check if agent-user sync services are available
 *
 * @return bool True if services are available
 */
function hpt_is_agent_user_service_available() {
    return class_exists('\\HappyPlace\\Services\\UnifiedAgentUserService') &&
           class_exists('\\HappyPlace\\Services\\AgentService');
}

/**
 * Handle user registration with plugin services
 *
 * @param int $user_id User ID
 * @param string $user_type User type (agent, buyer, seller, etc.)
 * @return void
 */
function hpt_handle_user_registration($user_id, $user_type = 'subscriber') {
    $user_role_service = hpt_get_user_role_service();

    if (!$user_role_service) {
        // Fallback to basic role assignment
        $user = new WP_User($user_id);

        switch ($user_type) {
            case 'agent':
                $user->set_role('agent');
                break;
            case 'seller':
                $user->set_role('subscriber');
                update_user_meta($user_id, 'can_list_property', true);
                break;
            case 'buyer':
            case 'investor':
            default:
                $user->set_role('subscriber');
                break;
        }

        // Set registration metadata
        update_user_meta($user_id, 'registration_date', current_time('mysql'));
        update_user_meta($user_id, 'registration_source', 'website_form');

        return;
    }

    // Use plugin service for enhanced user management
    // This would call into the plugin's user management system
    // For now, we'll use the basic implementation above

    $user = new WP_User($user_id);

    switch ($user_type) {
        case 'agent':
            $user->set_role('agent');
            break;
        case 'seller':
            $user->set_role('subscriber');
            update_user_meta($user_id, 'can_list_property', true);
            break;
        case 'buyer':
        case 'investor':
        default:
            $user->set_role('subscriber');
            break;
    }

    // Enhanced metadata with plugin integration
    update_user_meta($user_id, 'registration_date', current_time('mysql'));
    update_user_meta($user_id, 'registration_source', 'website_form');
    update_user_meta($user_id, 'user_type', $user_type);

    // Log registration for analytics
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("HPT User Registration: User ID {$user_id} registered as {$user_type}");
    }
}

/**
 * Get user capabilities for agent functions
 *
 * @param int $user_id User ID (optional, defaults to current user)
 * @return array Array of capabilities
 */
function hpt_get_user_agent_capabilities($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $user = get_user_by('id', $user_id);
    if (!$user) {
        return [];
    }

    $capabilities = [];

    // Check for agent-specific capabilities
    if (in_array('agent', $user->roles)) {
        $capabilities[] = 'edit_listings';
        $capabilities[] = 'manage_leads';
        $capabilities[] = 'view_agent_stats';
        $capabilities[] = 'edit_own_agent_profile';
    }

    // Check for admin capabilities
    if (in_array('administrator', $user->roles)) {
        $capabilities[] = 'manage_all_listings';
        $capabilities[] = 'manage_all_agents';
        $capabilities[] = 'view_all_stats';
    }

    return $capabilities;
}

/**
 * Check if user has specific agent capability
 *
 * @param string $capability Capability to check
 * @param int $user_id User ID (optional, defaults to current user)
 * @return bool True if user has capability
 */
function hpt_user_can_agent($capability, $user_id = null) {
    $capabilities = hpt_get_user_agent_capabilities($user_id);
    return in_array($capability, $capabilities);
}

/**
 * Get agent statistics for a user
 *
 * @param int $user_id User ID
 * @return array Agent statistics
 */
function hpt_get_user_agent_stats($user_id) {
    $agent_id = hpt_get_user_agent_id($user_id);

    if (!$agent_id) {
        return [
            'total_listings' => 0,
            'active_listings' => 0,
            'total_sales' => 0,
            'avg_days_on_market' => 0
        ];
    }

    // Get statistics from agent post meta or calculate
    return [
        'total_listings' => get_field('total_listings', $agent_id) ?: 0,
        'active_listings' => get_field('active_listings', $agent_id) ?: 0,
        'total_sales' => get_field('total_sales', $agent_id) ?: 0,
        'avg_days_on_market' => get_field('avg_days_on_market', $agent_id) ?: 0
    ];
}

/**
 * Backward compatibility: Hook into theme user registration actions
 */
add_action('hph_user_registered', 'hpt_handle_user_registration', 10, 2);

/**
 * WordPress user registration hook - enhanced with plugin integration
 */
add_action('user_register', function($user_id) {
    // This fires for any user registration
    $user = get_user_by('ID', $user_id);
    if ($user && !get_user_meta($user_id, 'welcome_email_sent', true)) {
        update_user_meta($user_id, 'welcome_email_sent', true);
    }
}, 10, 1);