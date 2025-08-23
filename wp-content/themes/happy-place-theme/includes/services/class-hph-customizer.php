<?php
/**
 * Customizer Service
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HPH_Customizer
 */
class HPH_Customizer implements HPH_Service {
    
    /**
     * Initialize service
     */
    public function init() {
        add_action('customize_register', array($this, 'register_customizer_settings'));
    }
    
    /**
     * Get service identifier
     */
    public function get_service_id() {
        return 'customizer';
    }
    
    /**
     * Check if service is active
     */
    public function is_active() {
        return true;
    }
    
    /**
     * Get service dependencies
     */
    public function get_dependencies() {
        return array();
    }
    
    /**
     * Register customizer settings
     */
    public function register_customizer_settings($wp_customize) {
        // Theme Colors Section
        $wp_customize->add_section('hph_colors', array(
            'title' => __('Theme Colors', 'happy-place-theme'),
            'priority' => 30,
        ));
        
        // Primary Color
        $wp_customize->add_setting('hph_primary_color', array(
            'default' => '#51BAE0',
            'sanitize_callback' => 'sanitize_hex_color',
        ));
        
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hph_primary_color', array(
            'label' => __('Primary Color', 'happy-place-theme'),
            'section' => 'hph_colors',
        )));
        
        // Layout Section
        $wp_customize->add_section('hph_layout', array(
            'title' => __('Layout Options', 'happy-place-theme'),
            'priority' => 40,
        ));
        
        // Container Width
        $wp_customize->add_setting('hph_container_width', array(
            'default' => '1200',
            'sanitize_callback' => 'absint',
        ));
        
        $wp_customize->add_control('hph_container_width', array(
            'label' => __('Container Width (px)', 'happy-place-theme'),
            'section' => 'hph_layout',
            'type' => 'number',
        ));
    }
}
