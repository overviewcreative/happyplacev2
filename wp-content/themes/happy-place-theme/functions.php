<?php
/**
 * Happy Place Theme Functions
 * 
 * Minimal bootstrap file - all functionality is organized into classes
 * 
 * @package HappyPlaceTheme
 * @version 2.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core Theme Constants
 */
define('HPH_VERSION', wp_get_theme()->get('Version') ?: '2.2.0');
define('HPH_THEME_DIR', get_template_directory());
define('HPH_THEME_URI', get_template_directory_uri());
define('HPH_DEV_MODE', defined('WP_DEBUG') && WP_DEBUG);

/**
 * Initialize Happy Place Theme
 * 
 * This single require loads the entire theme system through
 * an organized, class-based architecture
 */
require_once HPH_THEME_DIR . '/includes/class-hph-theme.php';

// Bootstrap the theme
HPH_Theme::init();

