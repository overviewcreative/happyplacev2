<?php
/**
 * Theme Support Service
 * 
 * Handles WordPress theme support declarations and setup
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

class HPH_Theme_Support implements HPH_Service, HPH_Hookable {
    
    /**
     * Service instance
     * @var HPH_Theme_Support
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     * @return HPH_Theme_Support
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
        $this->add_theme_support();
        $this->register_image_sizes();
    }
    
    /**
     * Register hooks
     */
    public function register_hooks() {
        add_action('after_setup_theme', array($this, 'setup_theme'), 10);
        add_action('widgets_init', array($this, 'register_sidebars'));
    }
    
    /**
     * Setup theme features
     */
    public function setup_theme() {
        // Add default posts and comments RSS feed links to head
        add_theme_support('automatic-feed-links');
        
        // Let WordPress manage the document title
        add_theme_support('title-tag');
        
        // Enable support for Post Thumbnails on posts and pages
        add_theme_support('post-thumbnails');
        
        // Enable HTML5 markup support
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ));
        
        // Add theme support for selective refresh for widgets
        add_theme_support('customize-selective-refresh-widgets');
        
        // Add support for responsive embedded content
        add_theme_support('responsive-embeds');
        
        // Add support for editor styles
        add_theme_support('editor-styles');
        
        // Add support for block editor
        add_theme_support('wp-block-styles');
        add_theme_support('align-wide');
        
        // Add editor stylesheet
        add_editor_style('assets/css/editor-style.css');
    }
    
    /**
     * Add theme support features
     */
    private function add_theme_support() {
        // This is called in setup_theme for proper timing
    }
    
    /**
     * Register custom image sizes
     */
    private function register_image_sizes() {
        // Property listing images
        add_image_size('listing-hero', 1920, 1080, true);
        add_image_size('listing-hero-mobile', 768, 600, true);
        add_image_size('listing-card', 600, 400, true);
        add_image_size('listing-card-small', 400, 300, true);
        add_image_size('listing-thumbnail', 300, 200, true);
        
        // Agent photos
        add_image_size('agent-profile', 400, 400, true);
        add_image_size('agent-card', 300, 300, true);
        add_image_size('agent-thumbnail', 150, 150, true);
        
        // Gallery images
        add_image_size('gallery-large', 1200, 800, true);
        add_image_size('gallery-medium', 800, 600, true);
        add_image_size('gallery-thumb', 200, 150, true);
        
        // Blog images
        add_image_size('blog-featured', 800, 500, true);
        add_image_size('blog-card', 400, 250, true);
    }
    
    /**
     * Register sidebar areas
     */
    public function register_sidebars() {
        // Primary Sidebar
        register_sidebar(array(
            'name'          => esc_html__('Primary Sidebar', 'happy-place-theme'),
            'id'            => 'sidebar-1',
            'description'   => esc_html__('Add widgets here.', 'happy-place-theme'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        
        // Listing Sidebar
        register_sidebar(array(
            'name'          => esc_html__('Listing Sidebar', 'happy-place-theme'),
            'id'            => 'listing-sidebar',
            'description'   => esc_html__('Sidebar for property listing pages.', 'happy-place-theme'),
            'before_widget' => '<div id="%1$s" class="widget listing-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ));
        
        // Footer Widgets
        for ($i = 1; $i <= 4; $i++) {
            register_sidebar(array(
                'name'          => sprintf(esc_html__('Footer %d', 'happy-place-theme'), $i),
                'id'            => 'footer-' . $i,
                'description'   => sprintf(esc_html__('Footer widget area %d', 'happy-place-theme'), $i),
                'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h4 class="widget-title">',
                'after_title'   => '</h4>',
            ));
        }
    }
    
    /**
     * Get the service ID
     * @return string
     */
    public function get_service_id() {
        return 'theme_support';
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
        return array('config');
    }
    
    /**
     * Get hook priority
     * @return int
     */
    public function get_hook_priority() {
        return 10;
    }
}
