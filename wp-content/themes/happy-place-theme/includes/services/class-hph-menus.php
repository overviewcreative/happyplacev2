<?php
/**
 * Menus Service
 * 
 * Handles WordPress menu registration and navigation
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

class HPH_Menus implements HPH_Service, HPH_Hookable {
    
    /**
     * Service instance
     * @var HPH_Menus
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     * @return HPH_Menus
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Private constructor for singleton
    }
    
    /**
     * Initialize the service
     */
    public function init() {
        $this->register_hooks();
    }
    
    /**
     * Register hooks
     */
    public function register_hooks() {
        add_action('after_setup_theme', array($this, 'register_nav_menus'));
        add_filter('nav_menu_css_class', array($this, 'add_menu_item_classes'), 10, 4);
        add_filter('nav_menu_link_attributes', array($this, 'add_menu_link_attributes'), 10, 4);
    }
    
    /**
     * Register navigation menus
     */
    public function register_nav_menus() {
        register_nav_menus(array(
            'primary'   => esc_html__('Primary Menu', 'happy-place-theme'),
            'footer'    => esc_html__('Footer Menu', 'happy-place-theme'),
            'mobile'    => esc_html__('Mobile Menu', 'happy-place-theme'),
            'utility'   => esc_html__('Utility Menu', 'happy-place-theme'),
        ));
    }
    
    /**
     * Add custom classes to menu items
     */
    public function add_menu_item_classes($classes, $item, $args, $depth) {
        // Add depth class
        $classes[] = 'menu-item-depth-' . $depth;
        
        // Add menu location class
        if (isset($args->theme_location)) {
            $classes[] = 'menu-location-' . $args->theme_location;
        }
        
        // Add button class for CTA items
        if (in_array('cta-button', $classes)) {
            $classes[] = 'btn';
            $classes[] = 'btn-primary';
        }
        
        return $classes;
    }
    
    /**
     * Add custom attributes to menu links
     */
    public function add_menu_link_attributes($atts, $item, $args, $depth) {
        // Add target for external links
        if (strpos($item->url, 'http') === 0 && strpos($item->url, home_url()) === false) {
            $atts['target'] = '_blank';
            $atts['rel'] = 'noopener noreferrer';
        }
        
        return $atts;
    }
    
    /**
     * Get the service ID
     * @return string
     */
    public function get_service_id() {
        return 'menus';
    }
    
    /**
     * Check if service is active
     * @return bool
     */
    public function is_active() {
        return true;
    }
    
    /**
     * Get service dependencies
     * @return array
     */
    public function get_dependencies() {
        return array();
    }
    
    /**
     * Get hook priority
     * @return int
     */
    public function get_hook_priority() {
        return 10;
    }
}
