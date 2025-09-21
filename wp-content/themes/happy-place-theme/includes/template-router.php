<?php
/**
 * Template Router for Archive Views
 * Handles routing to different archive templates based on view parameter
 *
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Route archive templates based on view parameter
 */
function hph_route_archive_template($template) {
    // Only handle listing archives
    if (!is_post_type_archive('listing')) {
        return $template;
    }

    // Prevent loops - only run once
    static $processed = false;
    if ($processed) {
        return $template;
    }
    $processed = true;

    // Check for view parameter
    $view = sanitize_text_field($_GET['view'] ?? '');

    // Route to map template if map view is requested
    if ($view === 'map') {
        $map_template = locate_template('archive-listing-map.php');
        if ($map_template && file_exists($map_template)) {
            return $map_template;
        }
    }

    // Return default template for grid/list views
    return $template;
}

// Hook into template routing - use only template_include
add_filter('template_include', 'hph_route_archive_template', 99);