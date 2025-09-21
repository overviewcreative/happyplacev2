<?php
/**
 * Lead Bridge Functions
 *
 * Provides interface between the plugin UnifiedLeadService and theme templates.
 * All lead-related data access should go through these functions.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get unified lead service instance
 *
 * @return \HappyPlace\Services\UnifiedLeadService|null
 */
function hpt_get_lead_service() {
    static $lead_service = null;

    if ($lead_service === null && class_exists('\HappyPlace\Services\UnifiedLeadService')) {
        $lead_service = new \HappyPlace\Services\UnifiedLeadService();
        $lead_service->init();
    }

    return $lead_service;
}

/**
 * Submit a lead through the unified service
 *
 * @param array $lead_data Lead information
 * @param array $config Lead configuration
 * @return int|false Lead ID on success, false on failure
 */
function hpt_submit_lead($lead_data, $config = []) {
    $lead_service = hpt_get_lead_service();

    if (!$lead_service) {
        error_log('HPT Lead Bridge: UnifiedLeadService not available');
        return false;
    }

    try {
        return $lead_service->create_lead($lead_data, $config);
    } catch (Exception $e) {
        error_log('HPT Lead Bridge: Error submitting lead - ' . $e->getMessage());
        return false;
    }
}

/**
 * Get leads with filtering
 *
 * @param array $args Query arguments
 * @return array Array of leads
 */
function hpt_get_leads($args = []) {
    $lead_service = hpt_get_lead_service();

    if (!$lead_service) {
        return [];
    }

    return $lead_service->get_leads($args);
}

/**
 * Get lead statistics
 *
 * @return array Lead statistics
 */
function hpt_get_lead_stats() {
    $lead_service = hpt_get_lead_service();

    if (!$lead_service) {
        return [
            'total' => 0,
            'new' => 0,
            'contacted' => 0,
            'qualified' => 0,
            'converted' => 0,
            'by_source' => [],
            'avg_score' => 0
        ];
    }

    return $lead_service->get_lead_stats();
}

/**
 * Check if unified lead service is available and active
 *
 * @return bool True if service is available
 */
function hpt_is_lead_service_available() {
    return class_exists('\HappyPlace\Services\UnifiedLeadService');
}

/**
 * Get lead nonce for forms
 *
 * @param string $action Nonce action
 * @return string Nonce value
 */
function hpt_get_lead_nonce($action = 'hph_route_form_nonce') {
    return wp_create_nonce($action);
}

/**
 * Render lead form via plugin service
 *
 * @param array $atts Shortcode attributes
 * @return string Form HTML
 */
function hpt_render_lead_form($atts = []) {
    $lead_service = hpt_get_lead_service();

    if (!$lead_service) {
        return '<p>Lead form service not available.</p>';
    }

    return $lead_service->render_lead_form_shortcode($atts);
}

/**
 * Backward compatibility: Handle legacy lead submissions
 * This ensures existing forms continue to work during the transition
 */
function hpt_handle_legacy_lead_submission() {
    // If new service is available, let it handle the request
    if (hpt_is_lead_service_available()) {
        // The plugin service will handle the AJAX request
        return;
    }

    // Fallback to old system if plugin service not available
    if (class_exists('HPH_Unified_Lead_Handler')) {
        HPH_Unified_Lead_Handler::handle_lead_submission();
    }
}

/**
 * Get lead types configuration
 *
 * @return array Lead types and their configurations
 */
function hpt_get_lead_types() {
    return [
        'agent_contact' => [
            'source' => 'agent_contact_form',
            'default_status' => 'new',
            'default_priority' => 'normal',
            'requires_agent' => true,
            'email_template' => 'agent_contact'
        ],
        'general_inquiry' => [
            'source' => 'general_inquiry_form',
            'default_status' => 'new',
            'default_priority' => 'normal',
            'requires_agent' => false,
            'email_template' => 'general_inquiry'
        ],
        'listing_inquiry' => [
            'source' => 'listing_inquiry_form',
            'default_status' => 'new',
            'default_priority' => 'high',
            'requires_agent' => false,
            'email_template' => 'listing_inquiry'
        ],
        'rsvp' => [
            'source' => 'rsvp_form',
            'default_status' => 'confirmed',
            'default_priority' => 'normal',
            'requires_agent' => false,
            'email_template' => 'rsvp_confirmation'
        ]
    ];
}