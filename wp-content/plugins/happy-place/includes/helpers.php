<?php
/**
 * Helper Functions
 * 
 * @package HappyPlace
 * @version 4.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Simple logging function
 */
if (!function_exists('hp_log')) {
    function hp_log($message, $level = 'info', $context = 'GENERAL') {
        if (HP_DEBUG) {
            error_log("Happy Place [{$level}] [{$context}]: {$message}");
        }
    }
}

/**
 * Get plugin URL
 */
if (!function_exists('hp_get_url')) {
    function hp_get_url($path = '') {
        return HP_PLUGIN_URL . ltrim($path, '/');
    }
}

/**
 * Get plugin path
 */
if (!function_exists('hp_get_path')) {
    function hp_get_path($path = '') {
        return HP_PLUGIN_DIR . ltrim($path, '/');
    }
}

/**
 * Check if we're on a Happy Place admin page
 */
if (!function_exists('hp_is_admin_page')) {
    function hp_is_admin_page() {
        $screen = get_current_screen();
        return $screen && (
            strpos($screen->id, 'happy-place') !== false ||
            in_array($screen->post_type, ['listing', 'agent', 'open_house', 'community', 'lead'])
        );
    }
}