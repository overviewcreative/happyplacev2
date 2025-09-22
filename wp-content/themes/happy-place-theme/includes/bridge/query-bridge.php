<?php
/**
 * Query Bridge Functions
 *
 * Bridge functions to connect theme to plugin QueryService
 * Provides backward compatibility and clean interface
 *
 * @package HappyPlaceTheme
 * @subpackage Bridge
 * @since 4.3.0 - Created for query delegation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Build search query via plugin service
 */
function hpt_build_search_query($args = array()) {
    if (class_exists('HappyPlace\Services\QueryService')) {
        $query_service = new HappyPlace\Services\QueryService();
        return $query_service->build_search_query($args);
    }

    // Fallback to standard WP_Query
    return new WP_Query($args);
}

/**
 * Get available query variables from plugin service
 */
function hpt_get_available_query_vars() {
    if (class_exists('HappyPlace\Services\QueryService')) {
        $query_service = new HappyPlace\Services\QueryService();
        return $query_service->get_query_vars();
    }

    return array();
}

/**
 * Check if a query variable is registered
 */
function hpt_is_registered_query_var($var) {
    if (class_exists('HappyPlace\Services\QueryService')) {
        $query_service = new HappyPlace\Services\QueryService();
        return $query_service->is_registered_query_var($var);
    }

    return false;
}

/**
 * Get current search filters from query variables
 */
function hpt_get_current_search_filters() {
    $filters = array();
    $available_vars = hpt_get_available_query_vars();

    foreach ($available_vars as $var) {
        $value = get_query_var($var);
        if (!empty($value)) {
            $filters[$var] = $value;
        }
    }

    return $filters;
}

/**
 * Build filter URL with query parameters
 */
function hpt_build_filter_url($filters, $base_url = null) {
    if (!$base_url) {
        $base_url = get_post_type_archive_link('listing');
    }

    return add_query_arg($filters, $base_url);
}

/**
 * Get clean filter parameters (sanitized and validated)
 */
function hpt_get_clean_filter_params($filters) {
    $clean_filters = array();
    $available_vars = hpt_get_available_query_vars();

    foreach ($filters as $key => $value) {
        if (in_array($key, $available_vars) && !empty($value)) {
            switch ($key) {
                case 'min_price':
                case 'max_price':
                case 'bedrooms':
                case 'bathrooms':
                case 'square_feet_min':
                case 'square_feet_max':
                case 'year_built_min':
                case 'year_built_max':
                    $clean_filters[$key] = intval($value);
                    break;

                case 'latitude':
                case 'longitude':
                case 'radius':
                    $clean_filters[$key] = floatval($value);
                    break;

                default:
                    $clean_filters[$key] = sanitize_text_field($value);
                    break;
            }
        }
    }

    return $clean_filters;
}