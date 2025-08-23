<?php
/**
 * Dashboard Initialization
 * Sets up all dashboard functionality
 * 
 * @package HappyPlaceTheme
 * @subpackage Dashboard
 * 
 * File Location: /wp-content/themes/happy-place/includes/dashboard/dashboard-init.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize Dashboard System
 */
class HPH_Dashboard_Init {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Hook into WordPress
        add_action('init', array($this, 'init'));
        // Asset loading handled by HP_Dashboard_Assets class - removed duplicate
        // add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('template_redirect', array($this, 'handle_dashboard_access'));
        
        // Load required files
        $this->load_dependencies();
    }
    
    /**
     * Initialize dashboard
     */
    public function init() {
        // Add rewrite rules for dashboard
        $this->add_rewrite_rules();
        
        // Register dashboard page template
        add_filter('theme_page_templates', array($this, 'add_dashboard_template'));
        add_filter('template_include', array($this, 'load_dashboard_template'));
    }
    
    /**
     * Load dependencies
     */
    private function load_dependencies() {
        // Load AJAX handlers
        require_once get_template_directory() . '/includes/ajax/dashboard-ajax.php';
        
        // Load page loader
        require_once get_template_directory() . '/includes/dashboard/dashboard-page-loader.php';
        
        // Load bridge functions if not already loaded
        $bridge_files = array(
            'listing-bridge.php',
            'gallery-bridge.php',
            'agent-bridge.php',
            'transaction-bridge.php',
            'community-bridge.php',
            'local-place-bridge.php'
        );
        
        foreach ($bridge_files as $file) {
            $filepath = get_template_directory() . '/includes/bridge/' . $file;
            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }
    }
    
    /**
     * Add rewrite rules
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^dashboard/?$',
            'index.php?pagename=dashboard',
            'top'
        );
        
        add_rewrite_rule(
            '^dashboard/([^/]*)/?',
            'index.php?pagename=dashboard&dashboard_page=$matches[1]',
            'top'
        );
        
        add_rewrite_rule(
            '^agent-dashboard/?$',
            'index.php?pagename=agent-dashboard',
            'top'
        );
        
        add_rewrite_rule(
            '^agent-dashboard/([^/]*)/?',
            'index.php?pagename=agent-dashboard&dashboard_page=$matches[1]',
            'top'
        );
        
        add_rewrite_tag('%dashboard_page%', '([^&]+)');
    }
    
    /**
     * Add dashboard template to page templates
     */
    public function add_dashboard_template($templates) {
        $templates['dashboard-main.php'] = 'Agent Dashboard';
        return $templates;
    }
    
    /**
     * Load dashboard template
     */
    public function load_dashboard_template($template) {
        global $post;
        
        if (!$post) {
            return $template;
        }
        
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ($page_template === 'dashboard-main.php' || $post->post_name === 'dashboard' || $post->post_name === 'agent-dashboard') {
            $dashboard_template = get_template_directory() . '/templates/dashboard/dashboard-main.php';
            
            if (file_exists($dashboard_template)) {
                return $dashboard_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Enqueue dashboard assets - DISABLED
     * Asset loading is now handled by HP_Dashboard_Assets class
     */
    public function enqueue_assets_DISABLED() {
        // Check if we're on dashboard
        if (!$this->is_dashboard()) {
            return;
        }
        
        // Core styles
        wp_enqueue_style(
            'hph-framework',
            get_template_directory_uri() . '/assets/css/hph-framework.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_style(
            'hph-dashboard',
            get_template_directory_uri() . '/assets/css/dashboard/dashboard.css',
            array('hph-framework'),
            '1.0.0'
        );
        
        // Core scripts
        wp_enqueue_script('jquery');
        
        // Chart.js for analytics
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
            array(),
            '3.9.1'
        );
        
        // Dashboard router (MUST load first)
        wp_enqueue_script(
            'hph-dashboard-router',
            get_template_directory_uri() . '/assets/js/dashboard/dashboard-router.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Main dashboard script
        wp_enqueue_script(
            'hph-dashboard-main',
            get_template_directory_uri() . '/assets/js/dashboard/dashboard-main.js',
            array('jquery', 'hph-dashboard-router'),
            '1.0.0',
            true
        );
        
        // Localize scripts
        wp_localize_script('hph-dashboard-router', 'hphDashboardSettings', array(
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'dashboard_nonce' => wp_create_nonce('hph_dashboard'),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'themeUrl' => get_template_directory_uri(),
            'version' => '1.0.0',
            'user_id' => get_current_user_id(),
            'is_mobile' => wp_is_mobile()
        ));
        
        // Preload commonly used page styles - disabled until all files exist
        // $this->preload_page_styles();
    }
    
    /**
     * Preload page styles for better performance
     */
    private function preload_page_styles() {
        $pages = array('listings', 'leads', 'analytics');
        
        foreach ($pages as $page) {
            $style_url = get_template_directory_uri() . '/assets/css/dashboard/dashboard-' . $page . '.css';
            echo '<link rel="preload" href="' . esc_url($style_url) . '" as="style">';
        }
    }
    
    /**
     * Check if current page is dashboard
     */
    private function is_dashboard() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Check by page template
        $template = get_post_meta($post->ID, '_wp_page_template', true);
        if ($template === 'dashboard-main.php') {
            return true;
        }
        
        // Check by page slug
        if ($post->post_name === 'dashboard' || $post->post_name === 'agent-dashboard') {
            return true;
        }
        
        // Check by page ID (if dashboard page ID is known)
        $dashboard_page_id = get_option('hph_dashboard_page_id');
        if ($dashboard_page_id && $post->ID == $dashboard_page_id) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle dashboard access control
     */
    public function handle_dashboard_access() {
        if (!$this->is_dashboard()) {
            return;
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(get_permalink()));
            exit;
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die('You do not have permission to access the dashboard.');
        }
    }
}

// Initialize dashboard
new HPH_Dashboard_Init();


/**
 * Helper function to get dashboard URL
 */
function hph_get_dashboard_url($page = '') {
    $dashboard_url = home_url('/agent-dashboard/');
    
    if ($page) {
        $dashboard_url .= '#' . $page;
    }
    
    return $dashboard_url;
}