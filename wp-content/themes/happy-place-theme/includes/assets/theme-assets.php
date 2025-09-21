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
        
        // Performance optimizations
        add_action('wp_head', [__CLASS__, 'add_preconnects'], 2);
        add_action('wp_enqueue_scripts', [__CLASS__, 'optimize_jquery'], 1);
    }
    
    /**
     * Add preconnect links for performance
     */
    public static function add_preconnects() {
        // Preconnect to CDN domains
        echo '<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>' . "\n";
        echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";
    }
    
    /**
     * Optimize jQuery loading
     */
    public static function optimize_jquery() {
        if (!is_admin() && !wp_doing_ajax()) {
            // Use jQuery from footer for better performance
            wp_scripts()->add_data('jquery', 'group', 1);
            wp_scripts()->add_data('jquery-core', 'group', 1);
            wp_scripts()->add_data('jquery-migrate', 'group', 1);
        }
    }
    
    /**
     * Always load framework CSS/JS (core functionality)
     */
    public static function load_framework() {
        // Check for optimized bundles first (production mode)
        $core_bundle_path = '/dist/css/core.css';
        $core_bundle_file = get_template_directory() . $core_bundle_path;
        
        if (file_exists($core_bundle_file) && !defined('WP_DEBUG')) {
            // Production: Use optimized core bundle (216KB vs previous 107+ files)
            wp_enqueue_style('hph-framework', 
                get_template_directory_uri() . $core_bundle_path,
                [], 
                self::get_file_version($core_bundle_path)
            );
            
            // Also load sitewide bundle for common components
            $sitewide_bundle_path = '/dist/css/sitewide.css';
            $sitewide_bundle_file = get_template_directory() . $sitewide_bundle_path;
            
            if (file_exists($sitewide_bundle_file)) {
                wp_enqueue_style('hph-sitewide', 
                    get_template_directory_uri() . $sitewide_bundle_path,
                    ['hph-framework'], 
                    self::get_file_version($sitewide_bundle_path)
                );
            }
        } else {
            // Development: Use framework index (original @import system for debugging)
            $framework_css_path = '/assets/css/framework/index.css';
            $framework_css_file = get_template_directory() . $framework_css_path;
            
            if (file_exists($framework_css_file)) {
                wp_enqueue_style('hph-framework', 
                    get_template_directory_uri() . $framework_css_path,
                    [], 
                    self::get_file_version($framework_css_path)
                );
            } else {
                error_log("HPH Critical Error: Framework CSS not found at: {$framework_css_file}");
            }
        }
        
        // Check if framework JS exists
        $framework_js_path = '/assets/js/core/framework-core.js';
        $framework_js_file = get_template_directory() . $framework_js_path;
        
        if (file_exists($framework_js_file)) {
            // Framework JavaScript - core utilities and HPH namespace
            wp_enqueue_script('hph-framework', 
                get_template_directory_uri() . $framework_js_path,
                ['jquery'], 
                self::get_file_version($framework_js_path), 
                true
            );
        } else {
            error_log("HPH Critical Error: Framework JS not found at: {$framework_js_file}");
        }
        
        // Enhanced lazy loading - load after framework
        $lazy_loading_path = '/assets/js/components/enhanced-lazy-loading.js';
        $lazy_loading_file = get_template_directory() . $lazy_loading_path;
        
        if (file_exists($lazy_loading_file)) {
            wp_enqueue_script('hph-enhanced-lazy-loading', 
                get_template_directory_uri() . $lazy_loading_path,
                ['hph-framework'], 
                self::get_file_version($lazy_loading_path), 
                true
            );
        }
        
        // Navigation JavaScript - header, menu, search toggle (sitewide)
        $nav_js_path = '/assets/js/layout/navigation.js';
        if (file_exists(get_template_directory() . $nav_js_path)) {
            wp_enqueue_script('hph-navigation', 
                get_template_directory_uri() . $nav_js_path,
                ['hph-framework'], 
                self::get_file_version($nav_js_path), 
                true
            );
        }
        
        // Unified Form System - handles all form validation and submission (sitewide)
        $unified_forms_path = '/assets/js/core/forms-unified.js';
        if (file_exists(get_template_directory() . $unified_forms_path)) {
            wp_enqueue_script('hph-forms-unified', 
                get_template_directory_uri() . $unified_forms_path,
                ['jquery'], 
                self::get_file_version($unified_forms_path), 
                true
            );
        } else {
            error_log("HPH Warning: Unified Forms not found at: {$unified_forms_path}");
        }
        
        // Universal Form Handler - compatibility bridge for legacy forms
        $form_handler_path = '/assets/js/universal-form-handler.js';
        if (file_exists(get_template_directory() . $form_handler_path)) {
            wp_enqueue_script('hph-universal-forms', 
                get_template_directory_uri() . $form_handler_path,
                ['jquery', 'hph-forms-unified'], 
                self::get_file_version($form_handler_path), 
                true
            );
        }
        
        // Form Modal Handler - handles modal form triggers (sitewide)
        $form_modal_path = '/assets/js/components/form-modal.js';
        if (file_exists(get_template_directory() . $form_modal_path)) {
            wp_enqueue_script('hph-form-modal', 
                get_template_directory_uri() . $form_modal_path,
                ['jquery', 'hph-forms-unified'], 
                self::get_file_version($form_modal_path), 
                true
            );
        }
        
        // Font Awesome (only if not already loaded by plugins)
        if (!wp_style_is('font-awesome', 'enqueued') && !wp_style_is('fontawesome', 'enqueued')) {
            wp_enqueue_style('font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
                [], 
                '6.5.1'
            );
            
            // Add preload for better performance
            wp_style_add_data('font-awesome', 'preload', true);
        }
        
        // Defer non-critical JavaScript for better performance
        add_filter('script_loader_tag', [__CLASS__, 'defer_scripts'], 10, 3);
    }
    
    /**
     * Load page-specific assets based on context
     */
    public static function load_conditional_assets() {
        
        // Single listing pages - use optimized listings bundle
        if (is_singular('listing')) {
            // Check for optimized listings bundle
            $listings_bundle_path = '/dist/css/listings.css';
            $listings_bundle_file = get_template_directory() . $listings_bundle_path;
            
            if (file_exists($listings_bundle_file) && !defined('WP_DEBUG')) {
                // Production: Use optimized listings bundle (374KB of all listing styles)
                wp_enqueue_style('hph-listings', 
                    get_template_directory_uri() . $listings_bundle_path,
                    ['hph-framework'], 
                    self::get_file_version($listings_bundle_path)
                );
            } else {
                // Development: Use individual components
                self::load_component('single-listing', [
                    'css' => 'framework/features/listing/single-listing.css',
                    'js' => 'listing-single.js'
                ]);
            }
            
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
            
            // Trigger Mapbox loading first for single listings
            add_filter('hph_should_enqueue_mapbox', '__return_true');
            
            // Load HPH Map component (depends on Mapbox GL JS from plugin)
            self::load_component('hph-map', [
                'css' => 'framework/components/organisms/map.css',
                'js' => 'components/hph-map.js',
                'js_deps' => ['mapbox-gl-js'] // Plugin's Mapbox script handle
            ]);
            
            self::load_component('listing-map', [
                'css' => 'framework/features/listing/listing-map.css',
                'js' => 'components/listing/listing-map.js',
                'js_deps' => ['hph-hph-map'] // Depends on our HPH Map component
            ]);
            
            
            self::load_component('listing-card', [
                'css' => 'framework/features/listing/listing-card.css',
                'js' => 'components/listing/listing-card.js'
            ]);
            
            self::load_component('mortgage-calculator', [
                'js' => 'components/mortgage-calculator.js'
            ]);
        }
        
        // Listing archives - cards, filters, pagination, map view
        if (is_post_type_archive('listing') || is_tax(['listing_type', 'listing_status', 'listing_city'])) {
            // Check for optimized listings bundle for archives too
            $listings_bundle_path = '/dist/css/listings.css';
            $listings_bundle_file = get_template_directory() . $listings_bundle_path;
            
            if (file_exists($listings_bundle_file) && !defined('WP_DEBUG')) {
                // Production: Use optimized listings bundle (includes map card styles)
                wp_enqueue_style('hph-listings', 
                    get_template_directory_uri() . $listings_bundle_path,
                    ['hph-core', 'hph-sitewide'],
                    self::get_file_version($listings_bundle_path)
                );
            } else {
                // Development: Load individual components
                self::load_component('listing-archive', [
                    'css' => 'features/listing/listing-card.css',
                ]);
                
                // Load map card styles for map view
                self::load_component('listing-map-card', [
                    'css' => 'framework/features/listing/listing-map-card.css'
                ]);
            }
            
            // Load JavaScript bundle or individual components
            $archive_js_bundle_path = '/dist/js/archive.js';
            $archive_js_bundle_file = get_template_directory() . $archive_js_bundle_path;
            
            if (file_exists($archive_js_bundle_file) && !defined('WP_DEBUG')) {
                // Production: Use archive JavaScript bundle (includes map functionality)
                wp_enqueue_script('hph-archive', 
                    get_template_directory_uri() . $archive_js_bundle_path,
                    ['jquery', 'mapbox-gl-js'],
                    self::get_file_version($archive_js_bundle_path),
                    true
                );
            } else {
                // Development: Load individual JavaScript components
                self::load_component('listing-archive-js', [
                    'js' => 'features/archive-ajax.js'
                ]);
                
                self::load_component('advanced-filters', [
                    'js' => 'features/advanced-filters.js'
                ]);
                
                self::load_component('hph-map', [
                    'js' => 'components/hph-map.js',
                    'js_deps' => ['mapbox-gl-js'] // Plugin's Mapbox script handle
                ]);
            }
            
            // Always load map components for map view functionality
            add_filter('hph_should_enqueue_mapbox', '__return_true');
            
            // Load map CSS (not included in JS bundle)
            if (!file_exists($listings_bundle_file) || defined('WP_DEBUG')) {
                self::load_component('hph-map-css', [
                    'css' => 'framework/components/organisms/map.css'
                ]);
            }
            
            // Archive AJAX functionality already loaded above in listing-archive-js component
        }
        
        // Agent pages - profiles, cards, contact
        if (is_singular('agent') || is_post_type_archive('agent') || 
            is_singular('staff') || is_post_type_archive('staff') ||
            is_page_template('page-meet-the-team.php') || is_page('meet-the-team')) {
            self::load_component('agent', [
                'css' => 'features/agent/agent-card.css',
                'js' => 'pages/archive-agent.js'
            ]);
        }
        
        // Carousel component - load on pages that might use carousels
        if (is_page_template('page-carousel-demo.php') || is_page('carousel-demo') ||
            is_front_page() || is_home() || is_post_type_archive()) {
            self::load_component('universal-carousel', [
                'css' => 'framework/components/organisms/universal-carousel.css',
                'js' => 'components/universal-carousel.js'
            ]);
        }
        
        // Dashboard pages - comprehensive dashboard system
        // Simplified condition: load on any page with 'dashboard' in URL or page-dashboard.php template
        $is_dashboard_page = (
            is_page_template('page-dashboard.php') || 
            is_page_template('page-user-dashboard.php') || 
            is_page('dashboard') || 
            is_page('user-dashboard') ||
            strpos($_SERVER['REQUEST_URI'] ?? '', 'dashboard') !== false ||
            is_admin()
        );
        
        if ($is_dashboard_page) {
            
            // Load dashboard CSS only on dashboard pages
            wp_enqueue_style('hph-dashboard', 
                get_template_directory_uri() . '/assets/css/framework/features/dashboard/index.css',
                ['hph-framework'], 
                self::get_file_version('/assets/css/framework/features/dashboard/index.css')
            );
            
            // Bootstrap for user dashboard tabs and components
            wp_enqueue_style('bootstrap', 
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                ['hph-dashboard'], 
                '5.3.0'
            );
            wp_enqueue_script('bootstrap', 
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
                ['jquery'], 
                '5.3.0', 
                true
            );
            
            // Load listing form modal CSS (not part of framework)
            self::load_component('listing-form-modal', [
                'css' => 'components/listing-form-modal.css'
            ]);
            
            // UNIFIED DASHBOARD SYSTEM - Single controller for all dashboard functionality
            self::load_component('dashboard-main', [
                'js' => 'pages/dashboard/dashboard-main.js'
            ]);
            
            // REMOVED CONFLICTING FILES:
            // - dashboard-open-houses.js (functionality moved to dashboard-main.js)
            // - dashboard-leads.js (functionality moved to dashboard-main.js)  
            // - listing-form.js (functionality moved to dashboard-main.js)
        }
        
        // Contact/form pages
        if (is_page_template('page-contact.php') || is_page('contact') || has_shortcode($GLOBALS['post']->post_content ?? '', 'contact_form') || is_singular('listing')) {
            self::load_component('contact-form', [
                'js' => 'features/contact-form.js'
            ]);
            
            // Ensure contact form localization is available
            wp_localize_script('hph-contact-form', 'hphContact', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hph_contact_nonce'),
                'messages' => [
                    'sending' => __('Sending...', 'happy-place-theme'),
                    'success' => __('Thank you! Your message has been sent successfully.', 'happy-place-theme'),
                    'error' => __('Sorry, there was an error sending your message. Please try again.', 'happy-place-theme'),
                    'validation' => __('Please fill in all required fields.', 'happy-place-theme')
                ]
            ]);
        }
        
        // Meet The Team page
        if (is_page_template('page-meet-the-team.php') || is_page('meet-the-team')) {
            self::load_component('meet-the-team', [
                'css' => 'pages/meet-the-team.css'
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
                // Use custom dependencies if provided, otherwise default to framework
                $js_deps = isset($assets['js_deps']) ? $assets['js_deps'] : ['hph-framework'];
                
                $script_handle = "hph-{$component}";
                wp_enqueue_script($script_handle, 
                    get_template_directory_uri() . $js_path,
                    $js_deps, 
                    self::get_file_version($js_path), 
                    true
                );
                
                // Add defer attribute for better performance
                add_filter('script_loader_tag', function($tag, $handle) use ($script_handle) {
                    if ($handle === $script_handle) {
                        return str_replace(' src', ' defer src', $tag);
                    }
                    return $tag;
                }, 10, 2);
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
            'ajax_url' => admin_url('admin-ajax.php'),
            'url' => admin_url('admin-ajax.php'), // Backward compatibility
            'nonce' => wp_create_nonce('hph_ajax_nonce'),
            'loading_text' => __('Loading...', 'happy-place-theme'),
            'error_text' => __('An error occurred', 'happy-place-theme'),
            'success_text' => __('Success!', 'happy-place-theme')
        ]);
        
        // Universal Form Handler context
        wp_localize_script('hph-universal-forms', 'hphContext', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_route_form_nonce'), // Main nonce for form router
            'formNonce' => wp_create_nonce('hph_general_contact'), // Backup nonce for contact forms
            'leadNonce' => wp_create_nonce('hph_lead_nonce'), // Backup nonce for lead forms
            'strings' => [
                'loading' => __('Sending your message...', 'happy-place-theme'),
                'success' => __('Thank you! Your message has been sent successfully.', 'happy-place-theme'),
                'error' => __('An error occurred. Please try again.', 'happy-place-theme'),
                'validationError' => __('Please fill in all required fields correctly.', 'happy-place-theme'),
                'networkError' => __('Network error. Please check your connection and try again.', 'happy-place-theme')
            ]
        ]);
        
        // Archive-specific localization for listing archives
        if (is_post_type_archive('listing') || is_tax(['listing_type', 'listing_status', 'listing_city'])) {
            wp_localize_script('hph-listing-archive', 'hphArchive', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hph_archive_nonce'),
                'postType' => 'listing',
                'currentView' => isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'grid',
                'currentSort' => isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'date_desc',
                'autoFilter' => true,
                'strings' => [
                    'loading' => __('Loading listings...', 'happy-place-theme'),
                    'error' => __('Error loading listings', 'happy-place-theme'),
                    'no_results' => __('No listings found', 'happy-place-theme'),
                    'load_more' => __('Load More', 'happy-place-theme')
                ]
            ]);
        }
        
        // Dashboard-specific localization - matches the CSS loading condition  
        $is_dashboard_page_localization = (
            is_page_template('page-dashboard.php') || 
            is_page_template('page-user-dashboard.php') || 
            is_page('dashboard') || 
            is_page('user-dashboard') ||
            strpos($_SERVER['REQUEST_URI'] ?? '', 'dashboard') !== false ||
            is_admin()
        );
        
        if ($is_dashboard_page_localization) {
            
            wp_localize_script('hph-dashboard-main', 'hphDashboard', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hph_dashboard_nonce'),
                'userId' => get_current_user_id(),
                'isAgent' => current_user_can('manage_listings') || current_user_can('manage_options') ? 1 : 0,
                'isAdmin' => current_user_can('manage_options') ? 1 : 0,
                'userRole' => self::get_user_dashboard_role(),
                'marketingNonce' => wp_create_nonce('hph_marketing_nonce'),
                'strings' => [
                    'loading' => __('Loading...', 'happy-place-theme'),
                    'error' => __('An error occurred', 'happy-place-theme'),
                    'success' => __('Success!', 'happy-place-theme'),
                    'confirm_delete' => __('Are you sure you want to delete this item?', 'happy-place-theme'),
                    'no_results' => __('No results found', 'happy-place-theme'),
                    'confirmDelete' => __('Are you sure you want to delete this item?', 'happy-place-theme'),
                    'errorGeneral' => __('An error occurred. Please try again.', 'happy-place-theme'),
                    'successSaved' => __('Successfully saved!', 'happy-place-theme'),
                    'loadingText' => __('Loading...', 'happy-place-theme')
                ]
            ]);
            
            // Ensure ajaxurl is available globally for inline scripts
            wp_localize_script('hph-framework', 'ajaxurl', admin_url('admin-ajax.php'));
        }
    }
    
    /**
     * Inline critical CSS for performance
     */
    public static function inline_critical_css() {
        // Use optimized critical CSS bundle
        $critical_bundle_path = get_template_directory() . '/dist/css/critical.css';
        $critical_fallback_path = get_template_directory() . '/assets/css/critical-inline.css';
        
        $critical_file = file_exists($critical_bundle_path) ? $critical_bundle_path : $critical_fallback_path;
        
        if (file_exists($critical_file)) {
            $critical_css = file_get_contents($critical_file);
            if ($critical_css) {
                echo '<style id="hph-critical-css">' . $critical_css . '</style>' . "\n";
                
                // Add performance hints for main CSS bundles
                if (file_exists(get_template_directory() . '/dist/css/core.css')) {
                    echo '<link rel="preload" href="' . get_template_directory_uri() . '/dist/css/core.css?v=' . self::get_file_version('/dist/css/core.css') . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
                    echo '<noscript><link rel="stylesheet" href="' . get_template_directory_uri() . '/dist/css/core.css?v=' . self::get_file_version('/dist/css/core.css') . '"></noscript>' . "\n";
                }
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
        
        // During development, use current timestamp to force refresh
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return time();
        }
        
        // Cache file modification times for better performance
        static $file_versions = [];
        
        if (!isset($file_versions[$path])) {
            $file_versions[$path] = file_exists($file_path) ? filemtime($file_path) : (defined('HPH_VERSION') ? HPH_VERSION : '1.0.0');
        }
        
        return $file_versions[$path];
    }
    
    /**
     * Defer non-critical scripts for better performance
     * 
     * @param string $tag The script tag
     * @param string $handle The script handle
     * @param string $src The script source
     * @return string Modified script tag
     */
    public static function defer_scripts($tag, $handle, $src) {
        // Don't defer these critical scripts
        $no_defer = [
            'jquery', 'jquery-core', 'jquery-migrate', 
            'hph-framework', // Core framework needed immediately
            'admin-bar'
        ];
        
        if (in_array($handle, $no_defer) || is_admin()) {
            return $tag;
        }
        
        // Add defer to non-critical scripts
        return str_replace('<script ', '<script defer ', $tag);
    }
    
    /**
     * Get user dashboard role for localization
     */
    private static function get_user_dashboard_role() {
        if (current_user_can('manage_options')) {
            return 'admin';
        } elseif (current_user_can('manage_listings') || in_array('agent', wp_get_current_user()->roles)) {
            return 'agent';
        } else {
            return 'user';
        }
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

// Initialize the simple asset system
HPH_Simple_Assets::init();
