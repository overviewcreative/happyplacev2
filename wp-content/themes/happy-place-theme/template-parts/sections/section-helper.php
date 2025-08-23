<?php
/**
 * Section Helper Functions
 * 
 * Helper functions to render sections with clean syntax
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

/**
 * Render a content section with configuration
 * 
 * @param array $config Section configuration
 */
function hph_render_content_section($config = array()) {
    get_template_part('template-parts/sections/content', null, $config);
}

/**
 * Quick content section with minimal config
 * 
 * @param string $headline
 * @param string $content
 * @param array $buttons
 * @param array $options Additional options
 */
function hph_simple_content_section($headline, $content = '', $buttons = array(), $options = array()) {
    $config = wp_parse_args($options, array(
        'headline' => $headline,
        'content' => $content,
        'buttons' => $buttons,
        'layout' => 'centered',
        'background' => 'white',
        'padding' => 'xl'
    ));
    
    hph_render_content_section($config);
}

/**
 * Image + content section
 * 
 * @param array $image Image configuration
 * @param string $headline
 * @param string $content
 * @param array $buttons
 * @param string $layout 'left-image' or 'right-image'
 * @param array $options Additional options
 */
function hph_image_content_section($image, $headline, $content = '', $buttons = array(), $layout = 'right-image', $options = array()) {
    $config = wp_parse_args($options, array(
        'image' => $image,
        'headline' => $headline,
        'content' => $content,
        'buttons' => $buttons,
        'layout' => $layout,
        'background' => 'white',
        'padding' => 'xl'
    ));
    
    hph_render_content_section($config);
}

/**
 * Hero-style content section
 * 
 * @param string $headline
 * @param string $subheadline
 * @param string $content
 * @param array $buttons
 * @param array $options Additional options
 */
function hph_hero_content_section($headline, $subheadline = '', $content = '', $buttons = array(), $options = array()) {
    $config = wp_parse_args($options, array(
        'headline' => $headline,
        'subheadline' => $subheadline,
        'content' => $content,
        'buttons' => $buttons,
        'layout' => 'centered',
        'background' => 'gradient',
        'padding' => '2xl'
    ));
    
    hph_render_content_section($config);
}

/**
 * CTA-style content section
 * 
 * @param string $headline
 * @param string $content
 * @param array $buttons
 * @param array $options Additional options
 */
function hph_cta_content_section($headline, $content = '', $buttons = array(), $options = array()) {
    $config = wp_parse_args($options, array(
        'headline' => $headline,
        'content' => $content,
        'buttons' => $buttons,
        'layout' => 'centered',
        'background' => 'primary',
        'padding' => 'xl'
    ));
    
    hph_render_content_section($config);
}