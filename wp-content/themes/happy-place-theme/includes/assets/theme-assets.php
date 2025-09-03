<?php
/**
 * Simple Theme Asset Management
 * 
 * Replaces the complex 700+ line asset system with a simple,
 * reliable approach that just works.
 * 
 * @package HappyPlaceTheme
 * @since 3.2.1
 * @author Asset Cleanup Team
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Simple Asset Manager
 * 
 * Key principles:
 * - No build tools required
 * - Load only what's needed
 * - Use WordPress standards
 * - Easy to debug and maintain
 */
class HPH_Simple_Assets {
    
    /**
     * Track loaded components to prevent duplicates
     */
    private static $loaded_components = [];
    
    /**
     * Initialize asset system
     */
    public static function init() {
        add_action('wp_enqueue_scripts', [__CLASS__, 'load_framework'], 5);
        add_action('wp_enqueue_scripts', [__CLASS__, 'load_conditional_assets'], 10);
        add_action('wp_enqueue_scripts', [__CLASS__, 'localize_ajax'], 15);
        
        // Critical CSS inline (for performance)
        add_action('wp_head', [__CLASS__, 'inline_critical_css'], 1);
    }
    
    /**
     * Always load framework CSS/JS (core functionality)
     */
    public static function load_framework() {
        // Framework CSS - contains all base styles
        wp_enqueue_style('hph-framework', 
            get_template_directory_uri() . '/assets/css/framework/index.css',
            [], 
            self::get_file_version('/assets/css/framework/index.css')
        );
        
        // Framework JavaScript - core utilities and HPH namespace
        wp_enqueue_script('hph-framework', 
            get_template_directory_uri() . '/assets/js/core/framework-core.js',
            ['jquery'], 
            self::get_file_version('/assets/js/core/framework-core.js'), 
            true
        );
        
        // Navigation JavaScript - header, menu, search toggle (sitewide)
        wp_enqueue_script('hph-navigation', 
            get_template_directory_uri() . '/assets/js/layout/navigation.js',
            ['hph-framework'], 
            self::get_file_version('/assets/js/layout/navigation.js'), 
            true
        );
        
        // Font Awesome (only if not already loaded by plugins)
        if (!wp_style_is('font-awesome', 'enqueued') && !wp_style_is('fontawesome', 'enqueued')) {
            wp_enqueue_style('font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
                [], 
                '6.5.1'
            );
        }
    }
    
    /**
     * Load page-specific assets based on context
     */
    public static function load_conditional_assets() {
        
        // Single listing pages - hero, details, gallery, contact form
        if (is_singular('listing')) {
            self::load_component('single-listing', [
                'css' => 'framework/features/listing/single-listing.css',
                'js' => 'listing-single.js'
            ]);
            
            self::load_component('listing-hero', [
                'css' => 'framework/features/listing/listing-hero.css',
                'js' => 'base/carousel.js'
            ]);
            
            self::load_component('listing-details', [
                'css' => 'framework/features/listing/listing-details.css',
                'js' => 'components/listing/listing-details.js'
            ]);
            
            self::load_component('listing-gallery', [
                'css' => 'framework/features/listing/listing-gallery.css',
                'js' => 'components/listing/listing-gallery.js'
            ]);
            
            self::load_component('listing-contact', [
                'css' => 'framework/features/listing/listing-contact.css',
                'js' => 'features/contact-form.js'
            ]);
            
            self::load_component('listing-map', [
                'css' => 'framework/features/listing/listing-map.css',
                'js' => 'components/listing/listing-map.js'
            ]);
            
            self::load_component('listing-card', [
                'css' => 'framework/features/listing/listing-card.css',
                'js' => 'components/listing/listing-card.js'
            ]);
            
            self::load_component('mortgage-calculator', [
                'js' => 'components/mortgage-calculator.js'
            ]);
        }
        
        // Listing archives - cards, filters, pagination
        if (is_post_type_archive('listing') || is_tax(['listing_type', 'listing_status', 'listing_city'])) {
            self::load_component('listing-archive', [
                'css' => 'features/listing/listing-card.css',
                'js' => 'features/archive-ajax.js'
            ]);
            
            self::load_component('advanced-filters', [
                'js' => 'features/advanced-filters.js'
            ]);
            
            // Archive AJAX functionality already loaded above in listing-archive component
        }
        
        // Agent pages - profiles, cards, contact
        if (is_singular('agent') || is_post_type_archive('agent')) {
            self::load_component('agent', [
                'css' => 'features/agent/agent-card.css',
                'js' => 'pages/archive-agent.js'
            ]);
        }
        
        // Dashboard pages (if they exist)
        if (is_page_template('page-dashboard.php') || is_page('dashboard')) {
            self::load_component('dashboard', [
                'css' => 'dashboard.css',
                'js' => 'dashboard/dashboard-main.js'
            ]);
        }
        
        // Contact/form pages
        if (is_page_template('page-contact.php') || is_page('contact')) {
            self::load_component('contact-form', [
                'js' => 'features/contact-form.js'
            ]);
        }
        
        // Search functionality - load on pages that have search (header search, archive pages)
        if (!is_admin() && !wp_doing_ajax()) {
            self::load_component('search-filters', [
                'js' => 'features/search-filters-enhanced.js'
            ]);
        }
    }
    
    /**
     * Load specific component assets
     * 
     * @param string $component Component name
     * @param array $assets Array with 'css' and/or 'js' paths
     */
    private static function load_component($component, $assets) {
        // Prevent loading the same component twice
        if (in_array($component, self::$loaded_components)) {
            return;
        }
        
        // Load CSS
        if (!empty($assets['css'])) {
            $css_path = '/assets/css/' . $assets['css'];
            $css_file = get_template_directory() . $css_path;
            
            if (file_exists($css_file)) {
                wp_enqueue_style("hph-{$component}", 
                    get_template_directory_uri() . $css_path,
                    ['hph-framework'], 
                    self::get_file_version($css_path)
                );
            } else {
                error_log("HPH Asset Warning: CSS file not found: {$css_file}");
            }
        }
        
        // Load JavaScript
        if (!empty($assets['js'])) {
            $js_path = '/assets/js/' . $assets['js'];
            $js_file = get_template_directory() . $js_path;
            
            if (file_exists($js_file)) {
                wp_enqueue_script("hph-{$component}", 
                    get_template_directory_uri() . $js_path,
                    ['hph-framework'], 
                    self::get_file_version($js_path), 
                    true
                );
            } else {
                error_log("HPH Asset Warning: JS file not found: {$js_file}");
            }
        }
        
        // Mark as loaded
        self::$loaded_components[] = $component;
    }
    
    /**
     * Setup AJAX variables for frontend JavaScript
     */
    public static function localize_ajax() {
        wp_localize_script('hph-framework', 'hph_ajax', [
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_ajax_nonce'),
            'loading_text' => __('Loading...', 'happy-place-theme'),
            'error_text' => __('An error occurred', 'happy-place-theme'),
            'success_text' => __('Success!', 'happy-place-theme')
        ]);
    }
    
    /**
     * Inline critical CSS for performance
     */
    public static function inline_critical_css() {
        $critical_css_file = get_template_directory() . '/assets/css/critical-inline.css';
        
        if (file_exists($critical_css_file)) {
            $critical_css = file_get_contents($critical_css_file);
            if ($critical_css) {
                echo '<style id="hph-critical-css">' . $critical_css . '</style>' . "\n";
            }
        }
    }
    
    /**
     * Get file modification time for cache busting
     * 
     * @param string $path Relative path from theme root
     * @return string|int File modification time or theme version
     */
    private static function get_file_version($path) {
        $file_path = get_template_directory() . $path;
        return file_exists($file_path) ? filemtime($file_path) : HPH_VERSION;
    }
}

/**
 * Helper function for components to enqueue their own assets
 * 
 * Usage in template files:
 * hph_enqueue_component('hero', ['css' => 'features/listing/hero.css']);
 * 
 * @param string $component Component name
 * @param array $assets Assets to load
 */
function hph_enqueue_component($component, $assets = []) {
    // This function can be called from template parts
    // to ensure their required assets are loaded
    
    if (empty($assets)) {
        return;
    }
    
    // Use a simple approach - just enqueue directly
    if (!empty($assets['css'])) {
        $handle = "hph-component-{$component}";
        if (!wp_style_is($handle, 'enqueued')) {
            wp_enqueue_style($handle, 
                get_template_directory_uri() . '/assets/css/' . $assets['css'],
                ['hph-framework'],
                filemtime(get_template_directory() . '/assets/css/' . $assets['css'])
            );
        }
    }
    
    if (!empty($assets['js'])) {
        $handle = "hph-component-{$component}-js";
        if (!wp_script_is($handle, 'enqueued')) {
            wp_enqueue_script($handle, 
                get_template_directory_uri() . '/assets/js/' . $assets['js'],
                ['hph-framework'],
                filemtime(get_template_directory() . '/assets/js/' . $assets['js']),
                true
            );
        }
    }
}

/**
 * Check if we're in development mode
 */
function hph_is_dev_mode() {
    return defined('HPH_DEV_MODE') && HPH_DEV_MODE;
}

// Asset system initialized from functions.php