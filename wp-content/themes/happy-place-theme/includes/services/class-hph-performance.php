<?php
/**
 * Performance Service
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HPH_Performance
 */
class HPH_Performance implements HPH_Service {
    
    /**
     * Initialize service
     */
    public function init() {
        add_action('wp_enqueue_scripts', array($this, 'optimize_assets'), 999);
        add_action('wp_head', array($this, 'add_preload_hints'), 1);
        add_filter('script_loader_tag', array($this, 'add_defer_attribute'), 10, 2);
    }
    
    /**
     * Get service identifier
     */
    public function get_service_id() {
        return 'performance';
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
        return array('config');
    }
    
    /**
     * Optimize assets
     */
    public function optimize_assets() {
        // Remove unnecessary scripts on non-admin pages
        if (!is_admin()) {
            wp_dequeue_script('wp-embed');
        }
        
        // Conditional loading
        if (!is_singular('listing')) {
            wp_dequeue_script('hph-listing-details');
        }
    }
    
    /**
     * Add preload hints
     */
    public function add_preload_hints() {
        // Preload critical framework CSS that actually exists
        if (file_exists(get_template_directory() . '/assets/css/hph-framework.css')) {
            echo '<link rel="preload" href="' . HPH_THEME_URI . '/assets/css/hph-framework.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
        }
        
        // Preload core JavaScript
        if (file_exists(get_template_directory() . '/assets/js/framework-core.js')) {
            echo '<link rel="preload" href="' . HPH_THEME_URI . '/assets/js/framework-core.js" as="script">' . "\n";
        }
        
        // Preconnect to external domains
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://maps.googleapis.com">' . "\n";
    }
    
    /**
     * Add defer attribute to scripts
     */
    public function add_defer_attribute($tag, $handle) {
        $defer_scripts = array('hph-main', 'hph-dashboard');
        
        if (in_array($handle, $defer_scripts)) {
            return str_replace(' src', ' defer src', $tag);
        }
        
        return $tag;
    }
}
