<?php
/**
 * Enhanced Asset Manager
 * 
 * Efficiently loads CSS/JS assets for the Happy Place Theme
 * with support for the enhanced framework system
 *
 * @package HappyPlaceTheme
 * @version 2.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Asset Manager Class
 */
class Happy_Place_Enhanced_Asset_Manager {
    
    private $version;
    private $asset_url;
    private $asset_path;
    private $debug_mode;
    
    public function __construct() {
        $this->version = wp_get_theme()->get('Version') ?: '1.0.0';
        $this->asset_url = get_template_directory_uri() . '/assets';
        $this->asset_path = get_template_directory() . '/assets';
        $this->debug_mode = defined('WP_DEBUG') && WP_DEBUG;
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        if ($this->debug_mode) {
            add_action('wp_footer', array($this, 'debug_output'));
        }
    }
    
    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        // Skip if dashboard assets are handling this page
        if ($this->is_dashboard_page()) {
            return;
        }
        
        // Enqueue existing CSS files in correct order
        $this->enqueue_existing_css();
        
        // Enqueue existing JS files
        $this->enqueue_existing_js();
    }
    
    /**
     * Enqueue CSS files in proper order
     */
    private function enqueue_existing_css() {
        // Verify framework file exists before loading
        $framework_file = $this->asset_path . '/css/hph-framework.css';
        
        if (file_exists($framework_file)) {
            // Google Fonts (load first, no dependencies)
            wp_enqueue_style(
                'hph-google-fonts',
                'https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:wght@300;400;500;600;700;800;900&display=swap',
                array(),
                null
            );
            
            // Bootstrap removed - handled by dashboard asset manager when needed
            
            // Font Awesome (for icons)
            wp_enqueue_style(
                'font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                array(),
                '6.4.0'
            );
            
            // Main HPH Framework
            wp_enqueue_style(
                'hph-framework',
                $this->asset_url . '/css/hph-framework.css',
                array('font-awesome'),
                $this->version
            );
            
            // Page-specific CSS (conditionally loaded)
            $this->enqueue_conditional_css();
            
            // Main theme style.css (for WordPress theme info and final overrides)
            wp_enqueue_style(
                'happy-place-theme-style',
                get_stylesheet_uri(),
                array('hph-framework'),
                $this->version
            );
        } else {
            // Fallback: Load basic styles if framework is missing
            wp_enqueue_style(
                'happy-place-theme-fallback',
                get_stylesheet_uri(),
                array(),
                $this->version
            );
            
            if ($this->debug_mode) {
                error_log('Happy Place Theme: Framework CSS file not found at ' . $framework_file);
            }
        }
    }
    
    /**
     * Conditionally enqueue CSS based on page type
     */
    private function enqueue_conditional_css() {
        // Property listing specific styles
        if (is_singular('listing') || is_post_type_archive('listing')) {
            $listing_css = $this->asset_path . '/css/pages/listings.css';
            if (file_exists($listing_css)) {
                wp_enqueue_style(
                    'hph-listings',
                    $this->asset_url . '/css/pages/listings.css',
                    array('hph-framework'),
                    $this->version
                );
            }
        }
        
        // Agent dashboard styles
        if ((function_exists('hph_is_dashboard') && hph_is_dashboard()) || is_page_template('page-agent-dashboard.php')) {
            $dashboard_css = $this->asset_path . '/css/dashboard/dashboard.css';
            if (file_exists($dashboard_css)) {
                wp_enqueue_style(
                    'hph-dashboard',
                    $this->asset_url . '/css/dashboard/dashboard.css',
                    array('hph-framework'),
                    $this->version
                );
            }
        }
        
        // Homepage specific styles
        if (is_front_page()) {
            $homepage_css = $this->asset_path . '/css/pages/homepage.css';
            if (file_exists($homepage_css)) {
                wp_enqueue_style(
                    'hph-homepage',
                    $this->asset_url . '/css/pages/homepage.css',
                    array('hph-framework'),
                    $this->version
                );
            }
        }
        
        // Showcase page styles
        if (is_page_template('page-showcase.php')) {
            $showcase_css = $this->asset_path . '/css/pages/showcase.css';
            if (file_exists($showcase_css)) {
                wp_enqueue_style(
                    'hph-showcase',
                    $this->asset_url . '/css/pages/showcase.css',
                    array('hph-framework'),
                    $this->version
                );
            }
        }
    }
    
    /**
     * Enqueue existing JS files
     */
    private function enqueue_existing_js() {
        // Bootstrap JS removed - handled by dashboard asset manager when needed
        
        $js_files = array(
            'hph-framework-core' => 'js/framework-core.js',
            'hph-navigation' => 'js/navigation.js',
            'hph-search-filters' => 'js/search-filters.js',
            'hph-mortgage-calculator' => 'js/mortgage-calculator.js',
            'hph-image-gallery' => 'js/image-gallery.js',
            // 'happy-place-theme' => '../js/theme.js'  // Disabled - causing JS errors
        );
        
        $deps = array('jquery');
        foreach ($js_files as $handle => $file) {
            $file_path = $this->asset_path . '/' . $file;
            if (file_exists($file_path)) {
                wp_enqueue_script(
                    $handle,
                    $this->asset_url . '/' . $file,
                    $deps,
                    $this->version,
                    true
                );
                $deps = array($handle); // Each file depends on the previous one
            }
        }
        
        // Localize the main theme script (only if it's loaded)
        if (wp_script_is('happy-place-theme', 'enqueued')) {
            wp_localize_script('happy-place-theme', 'happyPlace', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('happy_place_nonce'),
                'themeUrl' => get_template_directory_uri(),
                'strings' => array(
                    'loading' => esc_html__('Loading...', 'happy-place-theme'),
                    'error' => esc_html__('An error occurred', 'happy-place-theme'),
                    'noResults' => esc_html__('No results found', 'happy-place-theme'),
                )
            ));
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        // Only load admin styles on relevant pages
        $screen = get_current_screen();
        if (!$screen) return;
        
        // Load admin styles for post editing
        if (in_array($screen->post_type, array('listing', 'agent', 'community', 'open_house'))) {
            $admin_css = $this->asset_path . '/css/admin/admin.css';
            if (file_exists($admin_css)) {
                wp_enqueue_style(
                    'hph-admin',
                    $this->asset_url . '/css/admin/admin.css',
                    array(),
                    $this->version
                );
            }
        }
    }
    
    /**
     * Debug output for development
     */
    public function debug_output() {
        if (!current_user_can('manage_options')) return;
        
        echo "\n<!-- Happy Place Theme Debug Info -->\n";
        echo "<!-- Framework Version: {$this->version} -->\n";
        echo "<!-- Assets URL: {$this->asset_url} -->\n";
        echo "<!-- Framework File: " . ($this->framework_exists() ? 'EXISTS' : 'MISSING') . " -->\n";
        echo "<!-- Enhanced Files Loaded: bootstrap-overrides.css, wp-overrides.css, reset-enhanced.css, spacing-enhanced.css, animations-enhanced.css, effects-enhanced.css -->\n";
        echo "<!-- CSS Loading Order: Google Fonts → Bootstrap CSS → Font Awesome → HPH Framework → Theme Styles -->\n";
        echo "<!-- End Happy Place Theme Debug -->\n\n";
    }
    
    /**
     * Check if framework file exists
     */
    private function framework_exists() {
        return file_exists($this->asset_path . '/css/hph-framework.css');
    }
    
    /**
     * Check if we're on a dashboard page
     */
    private function is_dashboard_page() {
        return (strpos($_SERVER['REQUEST_URI'], 'agent-dashboard') !== false || 
                isset($_GET['dashboard_page']));
    }
}

// Initialize the enhanced asset manager
new Happy_Place_Enhanced_Asset_Manager();