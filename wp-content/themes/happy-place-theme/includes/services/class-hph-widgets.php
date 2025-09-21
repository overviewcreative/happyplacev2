<?php
/**
 * Widgets Service
 * 
 * Registers and manages widget areas
 * 
 * @package HappyPlaceTheme
 */

class HPH_Widgets {
    
    /**
     * Widget areas configuration
     */
    private $widget_areas = array();
    
    /**
     * Initialize widgets
     */
    public function init() {
        $this->define_widget_areas();
        add_action('widgets_init', array($this, 'register_widget_areas'));
        add_action('widgets_init', array($this, 'register_custom_widgets'));
    }
    
    /**
     * Define widget areas
     */
    private function define_widget_areas() {
        $this->widget_areas = array(
            'sidebar-main' => array(
                'name'          => __('Main Sidebar', 'happy-place-theme'),
                'description'   => __('Main sidebar for blog and pages', 'happy-place-theme'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title">',
                'after_title'   => '</h3>',
            ),
            'sidebar-listing' => array(
                'name'          => __('Listing Sidebar', 'happy-place-theme'),
                'description'   => __('Sidebar for single listing pages', 'happy-place-theme'),
                'before_widget' => '<div id="%1$s" class="widget listing-widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title">',
                'after_title'   => '</h3>',
            ),
            'sidebar-agent' => array(
                'name'          => __('Agent Sidebar', 'happy-place-theme'),
                'description'   => __('Sidebar for agent pages', 'happy-place-theme'),
                'before_widget' => '<div id="%1$s" class="widget agent-widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title">',
                'after_title'   => '</h3>',
            ),
        );
        
        // Add footer widget areas
        for ($i = 1; $i <= 4; $i++) {
            $this->widget_areas['footer-' . $i] = array(
                'name'          => sprintf(__('Footer Column %d', 'happy-place-theme'), $i),
                'description'   => sprintf(__('Footer widget area column %d', 'happy-place-theme'), $i),
                'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h4 class="footer-widget-title">',
                'after_title'   => '</h4>',
            );
        }
        
        // Allow filtering
        $this->widget_areas = apply_filters('hph_widget_areas', $this->widget_areas);
    }
    
    /**
     * Register widget areas
     */
    public function register_widget_areas() {
        foreach ($this->widget_areas as $id => $args) {
            $args['id'] = $id;
            register_sidebar($args);
        }
    }
    
    /**
     * Register custom widgets
     */
    public function register_custom_widgets() {
        // Load widget classes
        $widget_files = array(
            'widget-featured-listings',
            'widget-property-search',
            'widget-agent-profile',
            'widget-mortgage-calculator',
            'widget-recent-properties',
        );
        
        foreach ($widget_files as $widget_file) {
            $file_path = HPH_INC_DIR . '/widgets/class-' . $widget_file . '.php';
            if (file_exists($file_path)) {
                require_once $file_path;
                
                // Convert filename to class name
                $class_name = 'HPH_' . str_replace('-', '_', ucwords($widget_file, '-'));
                $class_name = str_replace('_', '', $class_name);
                
                if (class_exists($class_name)) {
                    register_widget($class_name);
                }
            }
        }
    }
    
    /**
     * Check if sidebar is active
     */
    public function is_sidebar_active($sidebar_id) {
        return is_active_sidebar($sidebar_id);
    }
    
    /**
     * Get sidebar
     */
    public function get_sidebar($sidebar_id) {
        if ($this->is_sidebar_active($sidebar_id)) {
            dynamic_sidebar($sidebar_id);
        }
    }
    
    /**
     * Add widget area
     */
    public function add_widget_area($id, $args) {
        $this->widget_areas[$id] = $args;
        
        // Register immediately if widgets_init has already fired
        if (did_action('widgets_init')) {
            $args['id'] = $id;
            register_sidebar($args);
        }
    }
}
