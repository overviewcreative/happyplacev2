<?php
/**
 * Assets Manager Class
 * 
 * Handles loading and management of CSS/JS assets for the Happy Place Plugin
 *
 * @package HappyPlace\Core
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Assets_Manager {
    
    private static $instance = null;
    
    /**
     * Asset configurations
     */
    private $frontend_assets = [
        'scripts' => [
            'happy-place-frontend' => [
                'src' => 'dist/frontend.js',
                'deps' => ['jquery'],
                'footer' => true,
                'localize' => [
                    'object' => 'hp_frontend',
                    'data' => 'get_frontend_data'
                ]
            ],
            'happy-place-dashboard' => [
                'src' => 'dist/dashboard.js',
                'deps' => ['jquery', 'wp-api'],
                'footer' => true,
                'condition' => 'is_dashboard_page',
                'localize' => [
                    'object' => 'hp_dashboard',
                    'data' => 'get_dashboard_data'
                ]
            ]
        ],
        'styles' => [
            'happy-place-frontend' => [
                'src' => 'dist/frontend.css',
                'deps' => [],
            ],
            'happy-place-dashboard' => [
                'src' => 'dist/dashboard.css',
                'deps' => ['happy-place-frontend'],
                'condition' => 'is_dashboard_page',
            ]
        ]
    ];
    
    private $admin_assets = [
        'scripts' => [
            'happy-place-admin' => [
                'src' => 'dist/admin.js',
                'deps' => ['jquery', 'wp-api'],
                'footer' => true,
                'localize' => [
                    'object' => 'hp_admin',
                    'data' => 'get_admin_data'
                ]
            ],
            'happy-place-listing-automation' => [
                'src' => 'assets/js/listing-automation.js',
                'deps' => ['jquery', 'acf-input'],
                'footer' => true,
                'condition' => 'is_listing_edit_page',
                'localize' => [
                    'object' => 'hp_listing_automation',
                    'data' => 'get_listing_automation_data'
                ]
            ],
            'happy-place-agent-automation' => [
                'src' => 'assets/js/agent-automation.js',
                'deps' => ['jquery', 'acf-input'],
                'footer' => true,
                'condition' => 'is_agent_edit_page',
                'localize' => [
                    'object' => 'hp_agent_automation',
                    'data' => 'get_agent_automation_data'
                ]
            ]
        ],
        'styles' => [
            'happy-place-admin' => [
                'src' => 'dist/admin.css',
                'deps' => [],
            ],
            'happy-place-listing-automation' => [
                'src' => 'assets/css/listing-automation.css',
                'deps' => [],
                'condition' => 'is_listing_edit_page',
            ],
            'happy-place-agent-automation' => [
                'src' => 'assets/css/agent-automation.css',
                'deps' => [],
                'condition' => 'is_agent_edit_page',
            ]
        ]
    ];
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize assets manager
     */
    private function __construct() {
        // Assets will be enqueued via main plugin class
    }
    
    /**
     * Initialize component
     */
    public function init() {
        // Register asset enqueue hooks
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend'], 10);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin'], 10);
        
        // Add inline styles for critical CSS
        add_action('wp_head', [$this, 'add_critical_css'], 1);
        
        // Preload key assets
        add_action('wp_head', [$this, 'preload_assets'], 2);
        
        hp_log('Assets Manager hooks registered', 'info', 'ASSETS');
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend() {
        $this->enqueue_asset_group($this->frontend_assets);
        
        // Add inline variables
        $this->add_inline_variables();
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin($hook) {
        // Only load on our plugin pages
        if (!$this->is_plugin_admin_page($hook)) {
            return;
        }
        
        $this->enqueue_asset_group($this->admin_assets);
    }
    
    /**
     * Enqueue a group of assets
     */
    private function enqueue_asset_group($assets) {
        // Enqueue styles first
        if (!empty($assets['styles'])) {
            foreach ($assets['styles'] as $handle => $config) {
                $this->enqueue_style($handle, $config);
            }
        }
        
        // Enqueue scripts
        if (!empty($assets['scripts'])) {
            foreach ($assets['scripts'] as $handle => $config) {
                $this->enqueue_script($handle, $config);
            }
        }
    }
    
    /**
     * Enqueue a single style
     */
    private function enqueue_style($handle, $config) {
        // Check condition if specified
        if (!empty($config['condition']) && !$this->check_condition($config['condition'])) {
            return;
        }
        
        $src = HP_ASSETS_URL . $config['src'];
        $deps = $config['deps'] ?? [];
        $version = $this->get_asset_version($config['src']);
        
        wp_enqueue_style($handle, $src, $deps, $version);
    }
    
    /**
     * Enqueue a single script
     */
    private function enqueue_script($handle, $config) {
        // Check condition if specified
        if (!empty($config['condition']) && !$this->check_condition($config['condition'])) {
            return;
        }
        
        $src = HP_ASSETS_URL . $config['src'];
        $deps = $config['deps'] ?? [];
        $version = $this->get_asset_version($config['src']);
        $in_footer = $config['footer'] ?? true;
        
        wp_enqueue_script($handle, $src, $deps, $version, $in_footer);
        
        // Add localization if specified
        if (!empty($config['localize'])) {
            $this->localize_script($handle, $config['localize']);
        }
    }
    
    /**
     * Localize script with data
     */
    private function localize_script($handle, $localize_config) {
        $object = $localize_config['object'];
        $data_method = $localize_config['data'];
        
        if (method_exists($this, $data_method)) {
            $data = $this->$data_method();
            wp_localize_script($handle, $object, $data);
        }
    }
    
    /**
     * Get frontend localization data
     */
    private function get_frontend_data() {
        return [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_ajax_nonce'),
            'rest_url' => rest_url('hp/v1/'),
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'is_user_logged_in' => is_user_logged_in(),
            'current_user_id' => get_current_user_id(),
            'strings' => [
                'loading' => __('Loading...', 'happy-place'),
                'error' => __('An error occurred', 'happy-place'),
                'success' => __('Success!', 'happy-place'),
            ]
        ];
    }
    
    /**
     * Get dashboard localization data
     */
    private function get_dashboard_data() {
        return array_merge($this->get_frontend_data(), [
            'dashboard_url' => home_url('/agent-dashboard/'),
            'current_section' => get_query_var('dashboard_section', 'overview'),
            'user_can_edit_listings' => current_user_can('edit_listings'),
            'strings' => array_merge($this->get_frontend_data()['strings'], [
                'confirm_delete' => __('Are you sure you want to delete this item?', 'happy-place'),
                'save_success' => __('Changes saved successfully', 'happy-place'),
                'save_error' => __('Error saving changes', 'happy-place'),
            ])
        ]);
    }
    
    /**
     * Get admin localization data
     */
    private function get_admin_data() {
        return [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_admin_nonce'),
            'strings' => [
                'loading' => __('Loading...', 'happy-place'),
                'saved' => __('Settings saved', 'happy-place'),
                'error' => __('Error occurred', 'happy-place'),
            ]
        ];
    }
    
    /**
     * Get listing automation localization data
     */
    private function get_listing_automation_data() {
        return [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_listing_auto'),
            'post_id' => get_the_ID(),
            'strings' => [
                'bathroom_display_label' => __('Bathroom Display Format', 'happy-place'),
                'slug_preview_label' => __('Post Slug Preview', 'happy-place'),
                'status_preview_label' => __('Status Badge Preview', 'happy-place'),
                'tag_summary_label' => __('Image Categories', 'happy-place'),
                'no_images' => __('No images categorized yet.', 'happy-place'),
                'formatting' => __('Formatting...', 'happy-place'),
                'error_format' => __('Error formatting data', 'happy-place'),
            ]
        ];
    }
    
    /**
     * Get agent automation localization data
     */
    private function get_agent_automation_data() {
        return [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_agent_auto'),
            'post_id' => get_the_ID(),
            'strings' => [
                'display_name_preview' => __('Display Name Preview', 'happy-place'),
                'slug_preview' => __('Slug Preview', 'happy-place'),
                'experience_preview' => __('Experience Preview', 'happy-place'),
                'performance_preview' => __('Performance Preview', 'happy-place'),
                'certification_summary' => __('Certifications Summary', 'happy-place'),
                'no_certifications' => __('No certifications added yet.', 'happy-place'),
                'calculating' => __('Calculating...', 'happy-place'),
                'error_calculation' => __('Error calculating data', 'happy-place'),
            ]
        ];
    }
    
    /**
     * Check loading condition
     */
    private function check_condition($condition) {
        switch ($condition) {
            case 'is_dashboard_page':
                return $this->is_dashboard_page();
                
            case 'is_listing_page':
                return is_singular('listing') || is_post_type_archive('listing');
                
            case 'is_agent_page':
                return is_singular('agent') || is_post_type_archive('agent');
                
            case 'is_listing_edit_page':
                return $this->is_listing_edit_page();
                
            case 'is_agent_edit_page':
                return $this->is_agent_edit_page();
                
            default:
                return true;
        }
    }
    
    /**
     * Check if current page is dashboard
     */
    private function is_dashboard_page() {
        global $wp_query;
        return isset($wp_query->query_vars['pagename']) && 
               $wp_query->query_vars['pagename'] === 'agent-dashboard';
    }
    
    /**
     * Check if current page is listing edit
     */
    private function is_listing_edit_page() {
        global $post, $pagenow;
        return is_admin() && 
               (($pagenow === 'post.php' && isset($_GET['post']) && get_post_type($_GET['post']) === 'listing') ||
                ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'listing'));
    }
    
    /**
     * Check if current page is agent edit
     */
    private function is_agent_edit_page() {
        global $post, $pagenow;
        return is_admin() && 
               (($pagenow === 'post.php' && isset($_GET['post']) && get_post_type($_GET['post']) === 'agent') ||
                ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'agent'));
    }
    
    /**
     * Check if current admin page belongs to plugin
     */
    private function is_plugin_admin_page($hook) {
        $plugin_pages = [
            'toplevel_page_happy-place',
            'happy-place_page_happy-place-settings',
            'happy-place_page_happy-place-dashboard',
        ];
        
        return in_array($hook, $plugin_pages) || 
               strpos($hook, 'happy-place') !== false;
    }
    
    /**
     * Get asset version for cache busting
     */
    private function get_asset_version($asset_path) {
        if (HP_DEBUG) {
            return time(); // Always fresh in debug mode
        }
        
        $file_path = HP_ASSETS_DIR . $asset_path;
        if (file_exists($file_path)) {
            return filemtime($file_path);
        }
        
        return HP_VERSION;
    }
    
    /**
     * Add critical CSS inline
     */
    public function add_critical_css() {
        if ($this->is_dashboard_page() || is_singular('listing')) {
            echo '<style id="hp-critical-css">';
            echo '.hp-loading{display:flex;justify-content:center;align-items:center;height:200px;}';
            echo '.hp-spinner{border:3px solid #f3f3f3;border-top:3px solid #3498db;border-radius:50%;width:30px;height:30px;animation:spin 1s linear infinite;}';
            echo '@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}';
            echo '</style>';
        }
    }
    
    /**
     * Preload key assets
     */
    public function preload_assets() {
        // Preload key fonts
        echo '<link rel="preload" href="' . HP_ASSETS_URL . 'fonts/inter.woff2" as="font" type="font/woff2" crossorigin>';
        
        // Preload critical images
        if (is_front_page()) {
            echo '<link rel="preload" href="' . HP_ASSETS_URL . 'images/hero-bg.jpg" as="image">';
        }
    }
    
    /**
     * Add inline JavaScript variables
     */
    private function add_inline_variables() {
        $variables = [
            'HP_ASSETS_URL' => HP_ASSETS_URL,
            'HP_VERSION' => HP_VERSION,
            'HP_DEBUG' => HP_DEBUG,
        ];
        
        echo '<script>window.HappyPlace = ' . wp_json_encode($variables) . ';</script>';
    }
}