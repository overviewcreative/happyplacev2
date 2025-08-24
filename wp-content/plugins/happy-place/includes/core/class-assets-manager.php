<?php
/**
 * Assets Manager Class
 * 
 * Handles registration and enqueuing of CSS and JavaScript assets
 *
 * @package HappyPlace\Core
 * @version 4.0.0
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assets Manager Class
 * 
 * @since 4.0.0
 */
class AssetsManager {
    
    /**
     * Single instance
     * 
     * @var AssetsManager|null
     */
    private static ?AssetsManager $instance = null;
    
    /**
     * Registered assets
     * 
     * @var array
     */
    private array $assets = [
        'styles' => [],
        'scripts' => [],
    ];
    
    /**
     * Asset version for cache busting
     * 
     * @var string
     */
    private string $version;
    
    /**
     * Asset base URLs
     * 
     * @var array
     */
    private array $urls = [];
    
    /**
     * Get instance
     * 
     * @return AssetsManager
     */
    public static function get_instance(): AssetsManager {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->version = HP_DEBUG ? time() : HP_VERSION;
        $this->setup_urls();
        $this->register_assets();
    }
    
    /**
     * Initialize assets manager
     * 
     * @return void
     */
    public function init(): void {
        // Frontend assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        
        // Admin assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Block editor assets
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
        
        // Login page assets
        add_action('login_enqueue_scripts', [$this, 'enqueue_login_assets']);
        
        // Add custom script attributes
        add_filter('script_loader_tag', [$this, 'add_script_attributes'], 10, 3);
        
        // Add custom style attributes
        add_filter('style_loader_tag', [$this, 'add_style_attributes'], 10, 4);
        
        // Preload critical assets
        add_action('wp_head', [$this, 'preload_critical_assets'], 2);
        
        hp_log('Assets Manager initialized', 'info', 'ASSETS');
    }
    
    /**
     * Setup URLs
     * 
     * @return void
     */
    private function setup_urls(): void {
        $this->urls = [
            'css' => HP_ASSETS_URL . 'css/',
            'js' => HP_ASSETS_URL . 'js/',
            'images' => HP_ASSETS_URL . 'images/',
            'fonts' => HP_ASSETS_URL . 'fonts/',
            'vendor' => HP_ASSETS_URL . 'vendor/',
        ];
    }
    
    /**
     * Register all assets
     * 
     * @return void
     */
    private function register_assets(): void {
        $this->register_styles();
        $this->register_scripts();
    }
    
    /**
     * Register styles
     * 
     * @return void
     */
    private function register_styles(): void {
        $styles = [
            // Core styles
            'hp-variables' => [
                'src' => 'core/variables.css',
                'deps' => [],
                'priority' => 1,
            ],
            'hp-base' => [
                'src' => 'core/base.css',
                'deps' => ['hp-variables'],
                'priority' => 2,
            ],
            'hp-utilities' => [
                'src' => 'core/utilities.css',
                'deps' => ['hp-base'],
                'priority' => 3,
            ],
            
            // Component styles
            'hp-buttons' => [
                'src' => 'components/buttons.css',
                'deps' => ['hp-base'],
            ],
            'hp-cards' => [
                'src' => 'components/cards.css',
                'deps' => ['hp-base'],
            ],
            'hp-forms' => [
                'src' => 'components/forms.css',
                'deps' => ['hp-base'],
            ],
            'hp-modals' => [
                'src' => 'components/modals.css',
                'deps' => ['hp-base'],
            ],
            'hp-tables' => [
                'src' => 'components/tables.css',
                'deps' => ['hp-base'],
            ],
            
            // Page-specific styles
            'hp-listing-single' => [
                'src' => 'pages/single-listing.css',
                'deps' => ['hp-base', 'hp-cards'],
                'condition' => 'is_singular:listing',
            ],
            'hp-listing-archive' => [
                'src' => 'pages/archive-listing.css',
                'deps' => ['hp-base', 'hp-cards'],
                'condition' => 'is_post_type_archive:listing',
            ],
            'hp-agent-single' => [
                'src' => 'pages/single-agent.css',
                'deps' => ['hp-base', 'hp-cards'],
                'condition' => 'is_singular:agent',
            ],
            'hp-search-results' => [
                'src' => 'pages/search-results.css',
                'deps' => ['hp-base', 'hp-cards', 'hp-forms'],
                'condition' => 'is_search',
            ],
            
            // Admin styles
            'hp-admin' => [
                'src' => 'admin/admin.css',
                'deps' => [],
                'context' => 'admin',
            ],
            'hp-admin-listings' => [
                'src' => 'admin/listings.css',
                'deps' => ['hp-admin'],
                'context' => 'admin',
                'screen' => ['edit-listing', 'listing'],
            ],
            'hp-admin-dashboard' => [
                'src' => 'admin/dashboard.css',
                'deps' => ['hp-admin'],
                'context' => 'admin',
                'screen' => 'toplevel_page_happy-place',
            ],
            
            // Vendor styles
            'slick-carousel' => [
                'src' => 'vendor/slick/slick.css',
                'deps' => [],
                'version' => '1.8.1',
                'external' => true,
            ],
            'leaflet' => [
                'src' => 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
                'deps' => [],
                'version' => '1.9.4',
                'external' => true,
                'integrity' => 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=',
            ],
        ];
        
        foreach ($styles as $handle => $config) {
            $this->assets['styles'][$handle] = $config;
        }
    }
    
    /**
     * Register scripts
     * 
     * @return void
     */
    private function register_scripts(): void {
        $scripts = [
            // Core scripts
            'hp-utils' => [
                'src' => 'core/utils.js',
                'deps' => ['jquery'],
                'in_footer' => true,
                'priority' => 1,
            ],
            'hp-api' => [
                'src' => 'core/api.js',
                'deps' => ['hp-utils'],
                'in_footer' => true,
                'localize' => [
                    'object' => 'hpAPI',
                    'data' => [$this, 'get_api_localization'],
                ],
            ],
            
            // Component scripts
            'hp-search' => [
                'src' => 'components/search.js',
                'deps' => ['hp-api'],
                'in_footer' => true,
            ],
            'hp-filters' => [
                'src' => 'components/filters.js',
                'deps' => ['hp-api'],
                'in_footer' => true,
            ],
            'hp-gallery' => [
                'src' => 'components/gallery.js',
                'deps' => ['jquery'],
                'in_footer' => true,
            ],
            'hp-map' => [
                'src' => 'components/map.js',
                'deps' => ['leaflet'],
                'in_footer' => true,
            ],
            'hp-mortgage-calculator' => [
                'src' => 'components/mortgage-calculator.js',
                'deps' => ['hp-utils'],
                'in_footer' => true,
            ],
            'hp-contact-form' => [
                'src' => 'components/contact-form.js',
                'deps' => ['hp-api'],
                'in_footer' => true,
            ],
            
            // Page-specific scripts
            'hp-listing-single' => [
                'src' => 'pages/single-listing.js',
                'deps' => ['hp-gallery', 'hp-map', 'hp-contact-form'],
                'in_footer' => true,
                'condition' => 'is_singular:listing',
            ],
            'hp-listing-archive' => [
                'src' => 'pages/archive-listing.js',
                'deps' => ['hp-search', 'hp-filters', 'hp-map'],
                'in_footer' => true,
                'condition' => 'is_post_type_archive:listing',
            ],
            
            // Admin scripts
            'hp-admin' => [
                'src' => 'admin/admin.js',
                'deps' => ['jquery', 'wp-api'],
                'in_footer' => true,
                'context' => 'admin',
            ],
            'hp-admin-listings' => [
                'src' => 'admin/listings.js',
                'deps' => ['hp-admin'],
                'in_footer' => true,
                'context' => 'admin',
                'screen' => ['edit-listing', 'listing'],
            ],
            'hp-admin-media' => [
                'src' => 'admin/media.js',
                'deps' => ['hp-admin', 'media-upload'],
                'in_footer' => true,
                'context' => 'admin',
            ],
            
            // Vendor scripts
            'slick-carousel' => [
                'src' => 'vendor/slick/slick.min.js',
                'deps' => ['jquery'],
                'version' => '1.8.1',
                'in_footer' => true,
                'external' => true,
            ],
            'leaflet' => [
                'src' => 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
                'deps' => [],
                'version' => '1.9.4',
                'in_footer' => true,
                'external' => true,
                'integrity' => 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=',
            ],
            'chart-js' => [
                'src' => 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
                'deps' => [],
                'version' => '4.4.0',
                'in_footer' => true,
                'external' => true,
            ],
        ];
        
        foreach ($scripts as $handle => $config) {
            $this->assets['scripts'][$handle] = $config;
        }
    }
    
    /**
     * Enqueue frontend assets
     * 
     * @return void
     */
    public function enqueue_frontend_assets(): void {
        // Register all assets first
        $this->register_all_assets();
        
        // Enqueue global styles
        wp_enqueue_style('hp-base');
        wp_enqueue_style('hp-utilities');
        wp_enqueue_style('hp-buttons');
        wp_enqueue_style('hp-forms');
        
        // Enqueue global scripts
        wp_enqueue_script('hp-utils');
        wp_enqueue_script('hp-api');
        
        // Enqueue conditional assets
        $this->enqueue_conditional_assets();
        
        // Allow themes to enqueue additional assets
        do_action('hp_enqueue_frontend_assets');
    }
    
    /**
     * Enqueue admin assets
     * 
     * @param string $hook
     * @return void
     */
    public function enqueue_admin_assets(string $hook): void {
        // Register all assets first
        $this->register_all_assets();
        
        // Get current screen
        $screen = get_current_screen();
        
        // Enqueue admin base assets
        wp_enqueue_style('hp-admin');
        wp_enqueue_script('hp-admin');
        
        // Enqueue screen-specific assets
        $this->enqueue_admin_screen_assets($screen, $hook);
        
        // Media uploader
        if (in_array($screen->post_type ?? '', ['listing', 'agent', 'community'])) {
            wp_enqueue_media();
            wp_enqueue_script('hp-admin-media');
        }
        
        // Allow plugins to enqueue additional admin assets
        do_action('hp_enqueue_admin_assets', $hook, $screen);
    }
    
    /**
     * Enqueue block editor assets
     * 
     * @return void
     */
    public function enqueue_block_editor_assets(): void {
        // Register block editor specific assets
        wp_register_script(
            'hp-blocks',
            $this->urls['js'] . 'blocks/blocks.js',
            ['wp-blocks', 'wp-element', 'wp-editor'],
            $this->version,
            true
        );
        
        wp_register_style(
            'hp-blocks',
            $this->urls['css'] . 'blocks/blocks.css',
            ['wp-edit-blocks'],
            $this->version
        );
        
        wp_enqueue_script('hp-blocks');
        wp_enqueue_style('hp-blocks');
    }
    
    /**
     * Enqueue login page assets
     * 
     * @return void
     */
    public function enqueue_login_assets(): void {
        wp_register_style(
            'hp-login',
            $this->urls['css'] . 'login.css',
            [],
            $this->version
        );
        
        wp_enqueue_style('hp-login');
    }
    
    /**
     * Register all assets with WordPress
     * 
     * @return void
     */
    private function register_all_assets(): void {
        // Register styles
        foreach ($this->assets['styles'] as $handle => $config) {
            if (isset($config['registered']) && $config['registered']) {
                continue;
            }
            
            $src = $config['external'] ?? false
                ? $config['src']
                : $this->urls['css'] . $config['src'];
            
            $deps = $config['deps'] ?? [];
            $version = $config['version'] ?? $this->version;
            $media = $config['media'] ?? 'all';
            
            wp_register_style($handle, $src, $deps, $version, $media);
            $this->assets['styles'][$handle]['registered'] = true;
        }
        
        // Register scripts
        foreach ($this->assets['scripts'] as $handle => $config) {
            if (isset($config['registered']) && $config['registered']) {
                continue;
            }
            
            $src = $config['external'] ?? false
                ? $config['src']
                : $this->urls['js'] . $config['src'];
            
            $deps = $config['deps'] ?? [];
            $version = $config['version'] ?? $this->version;
            $in_footer = $config['in_footer'] ?? false;
            
            wp_register_script($handle, $src, $deps, $version, $in_footer);
            
            // Add localization if specified
            if (isset($config['localize'])) {
                $data = is_callable($config['localize']['data'])
                    ? call_user_func($config['localize']['data'])
                    : $config['localize']['data'];
                
                wp_localize_script($handle, $config['localize']['object'], $data);
            }
            
            $this->assets['scripts'][$handle]['registered'] = true;
        }
    }
    
    /**
     * Enqueue conditional assets
     * 
     * @return void
     */
    private function enqueue_conditional_assets(): void {
        foreach ($this->assets['styles'] as $handle => $config) {
            if (isset($config['condition']) && $this->check_condition($config['condition'])) {
                wp_enqueue_style($handle);
            }
        }
        
        foreach ($this->assets['scripts'] as $handle => $config) {
            if (isset($config['condition']) && $this->check_condition($config['condition'])) {
                wp_enqueue_script($handle);
            }
        }
    }
    
    /**
     * Enqueue admin screen-specific assets
     * 
     * @param \WP_Screen $screen
     * @param string $hook
     * @return void
     */
    private function enqueue_admin_screen_assets(\WP_Screen $screen, string $hook): void {
        foreach ($this->assets['styles'] as $handle => $config) {
            if (($config['context'] ?? '') !== 'admin') {
                continue;
            }
            
            if (isset($config['screen'])) {
                $screens = (array) $config['screen'];
                if (in_array($screen->id, $screens) || in_array($hook, $screens)) {
                    wp_enqueue_style($handle);
                }
            }
        }
        
        foreach ($this->assets['scripts'] as $handle => $config) {
            if (($config['context'] ?? '') !== 'admin') {
                continue;
            }
            
            if (isset($config['screen'])) {
                $screens = (array) $config['screen'];
                if (in_array($screen->id, $screens) || in_array($hook, $screens)) {
                    wp_enqueue_script($handle);
                }
            }
        }
    }
    
    /**
     * Check condition for asset loading
     * 
     * @param string $condition
     * @return bool
     */
    private function check_condition(string $condition): bool {
        if (strpos($condition, ':') !== false) {
            list($function, $param) = explode(':', $condition, 2);
            return function_exists($function) && call_user_func($function, $param);
        }
        
        return function_exists($condition) && call_user_func($condition);
    }
    
    /**
     * Add script attributes
     * 
     * @param string $tag
     * @param string $handle
     * @param string $src
     * @return string
     */
    public function add_script_attributes(string $tag, string $handle, string $src): string {
        $config = $this->assets['scripts'][$handle] ?? null;
        
        if (!$config) {
            return $tag;
        }
        
        // Add async attribute
        if (!empty($config['async'])) {
            $tag = str_replace(' src', ' async src', $tag);
        }
        
        // Add defer attribute
        if (!empty($config['defer'])) {
            $tag = str_replace(' src', ' defer src', $tag);
        }
        
        // Add module type
        if (!empty($config['module'])) {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        }
        
        // Add integrity attribute for external scripts
        if (!empty($config['integrity'])) {
            $tag = str_replace(' src', sprintf(' integrity="%s" crossorigin="anonymous" src', $config['integrity']), $tag);
        }
        
        return $tag;
    }
    
    /**
     * Add style attributes
     * 
     * @param string $tag
     * @param string $handle
     * @param string $href
     * @param string $media
     * @return string
     */
    public function add_style_attributes(string $tag, string $handle, string $href, string $media): string {
        $config = $this->assets['styles'][$handle] ?? null;
        
        if (!$config) {
            return $tag;
        }
        
        // Add integrity attribute for external styles
        if (!empty($config['integrity'])) {
            $tag = str_replace(' href', sprintf(' integrity="%s" crossorigin="anonymous" href', $config['integrity']), $tag);
        }
        
        // Add preload for critical styles
        if (!empty($config['preload'])) {
            $tag = str_replace(' rel=', ' rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" original-rel=', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Preload critical assets
     * 
     * @return void
     */
    public function preload_critical_assets(): void {
        // Preload fonts
        $fonts = [
            'inter-regular' => $this->urls['fonts'] . 'inter-regular.woff2',
            'inter-bold' => $this->urls['fonts'] . 'inter-bold.woff2',
        ];
        
        foreach ($fonts as $handle => $url) {
            printf(
                '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>',
                esc_url($url)
            );
        }
        
        // Preload critical images
        if (is_front_page()) {
            $logo = get_theme_mod('hp_logo');
            if ($logo) {
                printf(
                    '<link rel="preload" href="%s" as="image">',
                    esc_url($logo)
                );
            }
        }
    }
    
    /**
     * Get API localization data
     * 
     * @return array
     */
    public function get_api_localization(): array {
        return [
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url('hp/v1/'),
            'nonce' => wp_create_nonce('hp_api_nonce'),
            'user_id' => get_current_user_id(),
            'is_logged_in' => is_user_logged_in(),
            'strings' => [
                'loading' => __('Loading...', 'happy-place'),
                'error' => __('An error occurred', 'happy-place'),
                'success' => __('Success!', 'happy-place'),
                'confirm' => __('Are you sure?', 'happy-place'),
            ],
        ];
    }
    
    /**
     * Add inline style
     * 
     * @param string $handle
     * @param string $css
     * @return bool
     */
    public function add_inline_style(string $handle, string $css): bool {
        return wp_add_inline_style($handle, $css);
    }
    
    /**
     * Add inline script
     * 
     * @param string $handle
     * @param string $js
     * @param string $position
     * @return bool
     */
    public function add_inline_script(string $handle, string $js, string $position = 'after'): bool {
        return wp_add_inline_script($handle, $js, $position);
    }
}