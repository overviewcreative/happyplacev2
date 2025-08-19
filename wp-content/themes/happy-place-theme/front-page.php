<?php
/**
 * Front Page Template
 * 
 * A modern, component-based template utilizing the HPH framework
 *
 * @package HappyPlaceTheme
 */

get_header();

// Load sections
$sections = array(
    'hero',
    'featured-properties',
    'services',
    'featured-locations',
    'cta'
);

foreach ($sections as $section) {
    $template_path = 'template-parts/sections/' . $section;
    if (locate_template($template_path . '.php')) {
        get_template_part($template_path);
    }
}

get_footer();
