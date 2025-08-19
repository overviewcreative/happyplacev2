<?php
/**
 * Theme Customizer
 * WordPress Customizer settings and controls
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add customizer settings
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object
 */
function happy_place_customize_register($wp_customize) {
    
    // Add theme colors section
    $wp_customize->add_section('happy_place_colors', array(
        'title' => __('Theme Colors', 'happy-place-theme'),
        'priority' => 30,
        'description' => __('Customize the theme colors', 'happy-place-theme'),
    ));
    
    // Primary color
    $wp_customize->add_setting('happy_place_primary_color', array(
        'default' => '#05182dff',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'happy_place_primary_color', array(
        'label' => __('Primary Color', 'happy-place-theme'),
        'section' => 'happy_place_colors',
        'settings' => 'happy_place_primary_color',
    )));
    
    // Secondary color
    $wp_customize->add_setting('happy_place_secondary_color', array(
        'default' => '#6c757d',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'happy_place_secondary_color', array(
        'label' => __('Secondary Color', 'happy-place-theme'),
        'section' => 'happy_place_colors',
        'settings' => 'happy_place_secondary_color',
    )));
    
    // Add header section
    $wp_customize->add_section('happy_place_header', array(
        'title' => __('Header Settings', 'happy-place-theme'),
        'priority' => 40,
        'description' => __('Customize the header area', 'happy-place-theme'),
    ));
    
    // Logo upload
    $wp_customize->add_setting('happy_place_logo', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'happy_place_logo', array(
        'label' => __('Logo', 'happy-place-theme'),
        'section' => 'happy_place_header',
        'settings' => 'happy_place_logo',
        'mime_type' => 'image',
    )));
    
    // Header phone number
    $wp_customize->add_setting('happy_place_header_phone', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('happy_place_header_phone', array(
        'label' => __('Header Phone Number', 'happy-place-theme'),
        'section' => 'happy_place_header',
        'type' => 'text',
    ));
    
    // Header email
    $wp_customize->add_setting('happy_place_header_email', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_email',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('happy_place_header_email', array(
        'label' => __('Header Email', 'happy-place-theme'),
        'section' => 'happy_place_header',
        'type' => 'email',
    ));
    
    // Add footer section
    $wp_customize->add_section('happy_place_footer', array(
        'title' => __('Footer Settings', 'happy-place-theme'),
        'priority' => 50,
        'description' => __('Customize the footer area', 'happy-place-theme'),
    ));
    
    // Footer copyright text
    $wp_customize->add_setting('happy_place_footer_copyright', array(
        'default' => sprintf(__('© %s Your Company Name. All rights reserved.', 'happy-place-theme'), date('Y')),
        'sanitize_callback' => 'wp_kses_post',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('happy_place_footer_copyright', array(
        'label' => __('Copyright Text', 'happy-place-theme'),
        'section' => 'happy_place_footer',
        'type' => 'textarea',
    ));
    
    // Footer social links
    $social_networks = array(
        'facebook' => __('Facebook', 'happy-place-theme'),
        'twitter' => __('Twitter', 'happy-place-theme'),
        'instagram' => __('Instagram', 'happy-place-theme'),
        'linkedin' => __('LinkedIn', 'happy-place-theme'),
        'youtube' => __('YouTube', 'happy-place-theme'),
    );
    
    foreach ($social_networks as $network => $label) {
        $wp_customize->add_setting("happy_place_social_{$network}", array(
            'default' => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport' => 'refresh',
        ));
        
        $wp_customize->add_control("happy_place_social_{$network}", array(
            'label' => sprintf(__('%s URL', 'happy-place-theme'), $label),
            'section' => 'happy_place_footer',
            'type' => 'url',
        ));
    }
    
    // Add real estate section
    $wp_customize->add_section('happy_place_real_estate', array(
        'title' => __('Real Estate Settings', 'happy-place-theme'),
        'priority' => 60,
        'description' => __('Settings specific to real estate functionality', 'happy-place-theme'),
    ));
    
    // Featured listings count
    $wp_customize->add_setting('happy_place_featured_listings_count', array(
        'default' => 6,
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('happy_place_featured_listings_count', array(
        'label' => __('Featured Listings Count', 'happy-place-theme'),
        'section' => 'happy_place_real_estate',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 1,
            'max' => 20,
        ),
    ));
    
    // Featured agents count
    $wp_customize->add_setting('happy_place_featured_agents_count', array(
        'default' => 4,
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('happy_place_featured_agents_count', array(
        'label' => __('Featured Agents Count', 'happy-place-theme'),
        'section' => 'happy_place_real_estate',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 1,
            'max' => 12,
        ),
    ));
    
    // Currency symbol
    $wp_customize->add_setting('happy_place_currency_symbol', array(
        'default' => '$',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('happy_place_currency_symbol', array(
        'label' => __('Currency Symbol', 'happy-place-theme'),
        'section' => 'happy_place_real_estate',
        'type' => 'text',
    ));
    
    // Add blog section
    $wp_customize->add_section('happy_place_blog', array(
        'title' => __('Blog Settings', 'happy-place-theme'),
        'priority' => 70,
        'description' => __('Customize blog appearance and functionality', 'happy-place-theme'),
    ));
    
    // Blog excerpt length
    $wp_customize->add_setting('happy_place_excerpt_length', array(
        'default' => 25,
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('happy_place_excerpt_length', array(
        'label' => __('Excerpt Length (words)', 'happy-place-theme'),
        'section' => 'happy_place_blog',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 10,
            'max' => 100,
        ),
    ));
    
    // Show author info
    $wp_customize->add_setting('happy_place_show_author_info', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('happy_place_show_author_info', array(
        'label' => __('Show Author Information', 'happy-place-theme'),
        'section' => 'happy_place_blog',
        'type' => 'checkbox',
    ));
    
    // Show related posts
    $wp_customize->add_setting('happy_place_show_related_posts', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('happy_place_show_related_posts', array(
        'label' => __('Show Related Posts', 'happy-place-theme'),
        'section' => 'happy_place_blog',
        'type' => 'checkbox',
    ));
}
add_action('customize_register', 'happy_place_customize_register');

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function happy_place_customize_partial_blogname() {
    bloginfo('name');
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function happy_place_customize_partial_blogdescription() {
    bloginfo('description');
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function happy_place_customize_preview_js() {
    wp_enqueue_script(
        'happy-place-customizer',
        get_template_directory_uri() . '/js/customizer.js',
        array('customize-preview'),
        '1.0.0',
        true
    );
}
add_action('customize_preview_init', 'happy_place_customize_preview_js');

/**
 * Add customizer styles to the head
 */
function happy_place_customizer_styles() {
    $primary_color = get_theme_mod('happy_place_primary_color', '#007bff');
    $secondary_color = get_theme_mod('happy_place_secondary_color', '#6c757d');
    
    if ($primary_color !== '#007bff' || $secondary_color !== '#6c757d') {
        ?>
        <style type="text/css">
            :root {
                --primary-color: <?php echo esc_attr($primary_color); ?>;
                --secondary-color: <?php echo esc_attr($secondary_color); ?>;
            }
            
            .btn-primary {
                background-color: <?php echo esc_attr($primary_color); ?>;
                border-color: <?php echo esc_attr($primary_color); ?>;
            }
            
            .btn-primary:hover {
                background-color: <?php echo esc_attr(happy_place_darken_color($primary_color, 10)); ?>;
                border-color: <?php echo esc_attr(happy_place_darken_color($primary_color, 10)); ?>;
            }
            
            .btn-secondary {
                background-color: <?php echo esc_attr($secondary_color); ?>;
                border-color: <?php echo esc_attr($secondary_color); ?>;
            }
            
            .text-primary {
                color: <?php echo esc_attr($primary_color); ?> !important;
            }
            
            .link-primary {
                color: <?php echo esc_attr($primary_color); ?>;
            }
            
            .link-primary:hover {
                color: <?php echo esc_attr(happy_place_darken_color($primary_color, 15)); ?>;
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'happy_place_customizer_styles');

/**
 * Helper function to darken a color
 *
 * @param string $color Hex color
 * @param int $percent Percentage to darken
 * @return string
 */
function happy_place_darken_color($color, $percent) {
    $color = ltrim($color, '#');
    
    if (strlen($color) !== 6) {
        return $color;
    }
    
    $rgb = array_map('hexdec', str_split($color, 2));
    
    foreach ($rgb as &$value) {
        $value = max(0, min(255, $value - round($value * ($percent / 100))));
    }
    
    return '#' . implode('', array_map(function($value) {
        return str_pad(dechex($value), 2, '0', STR_PAD_LEFT);
    }, $rgb));
}

/**
 * Get customizer value with fallback
 *
 * @param string $setting Setting name
 * @param mixed $default Default value
 * @return mixed
 */
function happy_place_get_theme_mod($setting, $default = '') {
    return get_theme_mod($setting, $default);
}
