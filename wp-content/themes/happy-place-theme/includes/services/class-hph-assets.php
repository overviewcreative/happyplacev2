<?php
/**
 * Assets Service - Simplified Version
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HPH_Assets
 */
class HPH_Assets implements HPH_Service {
    
    /**
     * Initialize service
     */
    public function init() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Get service identifier
     */
    public function get_service_id() {
        return 'assets';
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
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Load main framework (includes variables, resets, and all components)
        wp_enqueue_style(
            'hph-framework',
            HPH_THEME_URI . '/assets/css/hph-framework.css',
            array(),
            HPH_VERSION
        );
        
        // Load Font Awesome (centralized loading to avoid duplicates)
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
            array(),
            '6.5.1'
        );
        
        // Page-specific styles are now included in main framework
        
        wp_enqueue_script(
            'hph-framework-core',
            HPH_THEME_URI . '/assets/js/framework-core.js',
            array('jquery'),
            HPH_VERSION,
            true
        );
        
        // Load all CSS files from main directories
        $this->load_all_css_files();
        
        // Load all JS files from main directories
        $this->load_all_js_files();
        
        // Dashboard-specific assets
        if ($this->is_dashboard_page()) {
            $this->load_dashboard_assets();
        }
        
        // Load Chart.js for mortgage calculator
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js',
            array(),
            '4.4.0',
            true
        );
        
        // Load Google Maps API if needed
        if (is_singular('listing') || $this->has_map_components()) {
            $google_maps_api_key = get_option('hph_google_maps_api_key', '');
            if ($google_maps_api_key) {
                wp_enqueue_script(
                    'google-maps-api',
                    'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($google_maps_api_key) . '&libraries=places',
                    array(),
                    null,
                    true
                );
            }
        }
        
        // Localize script with enhanced context
        wp_localize_script('hph-framework-core', 'hphContext', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_nonce'),
            'restUrl' => rest_url('hph/v1/'),
            'restNonce' => wp_create_nonce('wp_rest'),
            'themeUri' => HPH_THEME_URI,
            'isDashboard' => $this->is_dashboard_page(),
            'isListing' => is_singular('listing'),
            'listingId' => is_singular('listing') ? get_the_ID() : null,
            'hasGoogleMaps' => !empty($google_maps_api_key),
            'strings' => array(
                'loading' => __('Loading...', 'happy-place-theme'),
                'error' => __('An error occurred. Please try again.', 'happy-place-theme'),
                'success' => __('Success!', 'happy-place-theme'),
                'confirm' => __('Are you sure?', 'happy-place-theme'),
            )
        ));
        
        // Initialize framework immediately after loading
        wp_add_inline_script('hph-framework-core', '
            jQuery(document).ready(function($) {
                if (typeof HPH !== "undefined" && typeof HPH.init === "function") {
                    HPH.init();
                }
            });
        ');
    }
    
    /**
     * Load all CSS files
     */
    private function load_all_css_files() {
        $css_directories = array(
            '/assets/css/',
            '/assets/css/components/',
            '/assets/css/pages/',
        );
        
        // Files to skip (already loaded manually)
        $skip_files = array(
            'framework-base',
            'hph-framework',
            'framework',
            'single-listing-layout'
        );
        
        $loaded_files = array();
        
        foreach ($css_directories as $dir) {
            $full_path = HPH_THEME_DIR . $dir;
            if (is_dir($full_path)) {
                $files = glob($full_path . '*.css');
                foreach ($files as $file) {
                    $filename = basename($file, '.css');
                    
                    // Skip if already loaded or in skip list
                    if (in_array($filename, $loaded_files) || in_array($filename, $skip_files)) {
                        continue;
                    }
                    
                    $handle = 'hph-' . $filename;
                    $url = HPH_THEME_URI . $dir . basename($file);
                    
                    // Ensure proper dependency chain
                    $deps = array('hph-framework');
                    if (is_singular('listing') && wp_style_is('hph-single-listing-layout', 'enqueued')) {
                        $deps[] = 'hph-single-listing-layout';
                    }
                    
                    wp_enqueue_style($handle, $url, $deps, HPH_VERSION);
                    $loaded_files[] = $filename;
                }
            }
        }
    }
    
    /**
     * Load all JS files
     */
    private function load_all_js_files() {
        $js_directories = array(
            '/assets/js/',
            '/assets/js/components/',
            '/assets/js/pages/',
        );
        
        $loaded_files = array();
        
        foreach ($js_directories as $dir) {
            $full_path = HPH_THEME_DIR . $dir;
            if (is_dir($full_path)) {
                $files = glob($full_path . '*.js');
                foreach ($files as $file) {
                    $filename = basename($file, '.js');
                    
                    // Skip if already loaded or if it's framework-core (loaded separately)
                    if (in_array($filename, $loaded_files) || $filename === 'framework-core') {
                        continue;
                    }
                    
                    $handle = 'hph-' . $filename;
                    $url = HPH_THEME_URI . $dir . basename($file);
                    
                    wp_enqueue_script($handle, $url, array('hph-framework-core'), HPH_VERSION, true);
                    $loaded_files[] = $filename;
                }
            }
        }
    }
    
    /**
     * Load dashboard-specific assets
     */
    private function load_dashboard_assets() {
        // Dashboard CSS files
        $dashboard_css_dir = HPH_THEME_DIR . '/assets/css/dashboard/';
        if (is_dir($dashboard_css_dir)) {
            $css_files = glob($dashboard_css_dir . '*.css');
            foreach ($css_files as $file) {
                $filename = basename($file, '.css');
                $handle = 'hph-dashboard-' . $filename;
                $url = HPH_THEME_URI . '/assets/css/dashboard/' . basename($file);
                
                wp_enqueue_style($handle, $url, array('hph-framework'), HPH_VERSION);
            }
        }
        
        // Dashboard JS files
        $dashboard_js_dir = HPH_THEME_DIR . '/assets/js/dashboard/';
        if (is_dir($dashboard_js_dir)) {
            $js_files = glob($dashboard_js_dir . '*.js');
            foreach ($js_files as $file) {
                $filename = basename($file, '.js');
                $handle = 'hph-dashboard-' . $filename;
                $url = HPH_THEME_URI . '/assets/js/dashboard/' . basename($file);
                
                wp_enqueue_script($handle, $url, array('hph-framework-core'), HPH_VERSION, true);
            }
        }
        
        // Dashboard-specific localization (only if dashboard-main exists)
        if (wp_script_is('hph-dashboard-main', 'enqueued')) {
            wp_localize_script('hph-dashboard-main', 'hphDashboard', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hph_dashboard_nonce'),
                'restUrl' => rest_url('hph/v1/'),
                'restNonce' => wp_create_nonce('wp_rest'),
                'userId' => get_current_user_id(),
                'section' => $this->get_dashboard_section(),
            ));
        }
    }
    
    /**
     * Check if current page is a dashboard page
     */
    private function is_dashboard_page() {
        return get_query_var('agent_dashboard') || 
               get_query_var('dashboard_page') ||
               (isset($_GET['dashboard']) && $_GET['dashboard']) ||
               strpos($_SERVER['REQUEST_URI'], 'agent-dashboard') !== false ||
               (defined('HPH_DASHBOARD_LOADING') && HPH_DASHBOARD_LOADING);
    }
    
    /**
     * Get current dashboard section
     */
    private function get_dashboard_section() {
        $section = get_query_var('dashboard_page', '');
        if (empty($section)) {
            $section = isset($_GET['dashboard_page']) ? sanitize_text_field($_GET['dashboard_page']) : 'overview';
        }
        return $section;
    }
    
    /**
     * Check if current page has map components
     */
    private function has_map_components() {
        // Check if we're on a page that typically uses maps
        return is_home() || is_archive() || is_search() || 
               (is_page() && (strpos(get_post()->post_content, 'hph_listing_map') !== false ||
                             strpos(get_post()->post_content, 'listing-map') !== false));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        // Load admin-specific CSS and JS if needed
        $admin_css = HPH_THEME_DIR . '/assets/css/admin.css';
        if (file_exists($admin_css)) {
            wp_enqueue_style('hph-admin', HPH_THEME_URI . '/assets/css/admin.css', array(), HPH_VERSION);
        }
        
        $admin_js = HPH_THEME_DIR . '/assets/js/admin.js';
        if (file_exists($admin_js)) {
            wp_enqueue_script('hph-admin', HPH_THEME_URI . '/assets/js/admin.js', array('jquery'), HPH_VERSION, true);
        }
    }
}
