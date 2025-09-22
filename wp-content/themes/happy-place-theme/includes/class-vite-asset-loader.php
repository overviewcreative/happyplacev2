<?php
/**
 * Vite Asset Loader for WordPress
 * Optimized loading strategy to prevent render blocking
 *
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class HPH_Vite_Asset_Loader {

    private static $manifest = null;
    private static $loaded_bundles = [];

    /**
     * Initialize the asset loader
     */
    public static function init() {
        error_log('HPH Asset Loader: init() method called');
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_vite_assets'], 5);
        add_action('wp_head', [__CLASS__, 'add_resource_hints'], 1);
        add_action('wp_head', [__CLASS__, 'add_preload_tags'], 2);
        error_log('HPH Asset Loader: Hooks registered for wp_enqueue_scripts, wp_head');
    }

    /**
     * Get the Vite manifest
     */
    private static function get_manifest() {
        if (self::$manifest === null) {
            $manifest_path = get_template_directory() . '/dist/.vite/manifest.json';
            error_log("HPH Asset Loader: Looking for manifest at: {$manifest_path}");

            if (file_exists($manifest_path)) {
                $manifest_content = file_get_contents($manifest_path);
                self::$manifest = json_decode($manifest_content, true);

                if (self::$manifest === null) {
                    error_log('HPH Asset Loader: ERROR - Failed to decode manifest JSON');
                    self::$manifest = [];
                } else {
                    error_log('HPH Asset Loader: Manifest loaded successfully with ' . count(self::$manifest) . ' entries');
                }
            } else {
                error_log('HPH Asset Loader: ERROR - Manifest file not found');
                self::$manifest = [];
            }
        }
        return self::$manifest;
    }

    /**
     * Get asset URL from manifest
     */
    private static function get_asset_url($entry_name, $type = 'file') {
        $manifest = self::get_manifest();

        // For our new optimized bundles, look directly in manifest by input name
        foreach ($manifest as $key => $asset) {
            // Match CSS files (new bundle names)
            if ($type === 'css' && $key === "src/css/{$entry_name}.css" && isset($asset['file'])) {
                return get_template_directory_uri() . '/dist/' . $asset['file'];
            }
            // Match JS files (new bundle names)
            if ($type === 'file' && $key === "src/js/{$entry_name}.js" && isset($asset['file'])) {
                return get_template_directory_uri() . '/dist/' . $asset['file'];
            }
        }

        // Legacy fallback for compatibility
        foreach ($manifest as $key => $asset) {
            if ($type === 'file' && $key === "src/js/{$entry_name}-legacy.js" && isset($asset['file'])) {
                return get_template_directory_uri() . '/dist/' . $asset['file'] . '?v=' . time();
            }
        }

        return false;
    }

    /**
     * Enqueue Vite assets with optimal loading strategies
     * UPDATED: Uses optimized modular bundle system
     */
    public static function enqueue_vite_assets() {
        // DEBUG: Log that this function is being called
        error_log('HPH Asset Loader: enqueue_vite_assets() called');

        // 1. CORE CSS (essential foundation) - Load immediately
        self::enqueue_bundle('core', [
            'strategy' => 'blocking',
            'priority' => 1,
            'type' => 'css'
        ]);

        // 2. CRITICAL CSS (header/footer essentials) - Load immediately after core
        self::enqueue_bundle('critical-optimized', [
            'strategy' => 'blocking',
            'priority' => 2,
            'type' => 'css'
        ]);

        // 2. CORE JAVASCRIPT (essential functionality)
        self::enqueue_bundle('core', [
            'strategy' => 'defer',
            'priority' => 2,
            'type' => 'js'
        ]);

        // 3. SITEWIDE JAVASCRIPT (navigation, forms, etc.)
        error_log('HPH Asset Loader: About to enqueue sitewide bundle');
        self::enqueue_bundle('sitewide', [
            'strategy' => 'defer',
            'priority' => 5,
            'type' => 'js'
        ]);

        // 4. PAGE-SPECIFIC OPTIMIZED BUNDLES
        self::enqueue_conditional_assets();
    }

    /**
     * Enqueue Vite legacy polyfills (no longer needed with modern-only build)
     */
    private static function enqueue_legacy_polyfills() {
        // REMOVED: Legacy polyfills no longer needed with optimized modern-only build
        error_log('HPH Asset Loader: Skipping legacy polyfills (modern-only build)');
    }

    /**
     * Enqueue bundle dependencies from manifest imports (simplified for modern bundles)
     */
    private static function enqueue_bundle_dependencies($bundle_name) {
        // SIMPLIFIED: Modern bundles handle their own dependencies via Vite's ES modules
        error_log("HPH Asset Loader: Skipping manual dependency loading for {$bundle_name} (handled by ES modules)");
    }

    /**
     * Enqueue conditional assets based on page type
     * UPDATED: Uses optimized modular bundles
     */
    private static function enqueue_conditional_assets() {
        // Homepage - HOMEPAGE BUNDLE (hero, stats, content sections)
        if (is_front_page()) {
            self::enqueue_bundle('homepage', [
                'strategy' => 'async',
                'priority' => 6,
                'type' => 'css'
            ]);
        }

        // Listing archive pages - LISTINGS ARCHIVE BUNDLE
        if (is_post_type_archive('listing')) {
            self::enqueue_bundle('listings-archive', [
                'strategy' => 'async',
                'priority' => 7,
                'type' => 'css'
            ]);
            self::enqueue_bundle('archive', [
                'strategy' => 'async',
                'priority' => 10,
                'type' => 'js'
            ]);
        }

        // Single listing pages - SINGLE PROPERTY BUNDLE
        if (is_singular('listing')) {
            self::enqueue_bundle('single-property', [
                'strategy' => 'async',
                'priority' => 7,
                'type' => 'css'
            ]);
            self::enqueue_bundle('listings', [
                'strategy' => 'async',
                'priority' => 10,
                'type' => 'js'
            ]);
        }

        // Other archive pages (cities, agents, events, places) - Use listings archive for filters
        if (is_archive() && !is_post_type_archive('listing') && !is_front_page()) {
            self::enqueue_bundle('listings-archive', [
                'strategy' => 'async',
                'priority' => 7,
                'type' => 'css'
            ]);
            self::enqueue_bundle('archive', [
                'strategy' => 'async',
                'priority' => 10,
                'type' => 'js'
            ]);
        }

        // Agent pages - Add agent-specific features
        if (is_post_type_archive('agent') || is_singular('agent')) {
            self::enqueue_bundle('agents', [
                'strategy' => 'async',
                'priority' => 11,
                'type' => 'js'
            ]);
        }

        // Dashboard pages - DASHBOARD BUNDLE
        if (is_page_template('page-dashboard.php') || is_page('dashboard') ||
            is_page('user-dashboard') || is_page('agent-dashboard')) {
            self::enqueue_bundle('dashboard', [
                'strategy' => 'defer',
                'priority' => 8,
                'type' => 'js'
            ]);
        }
    }

    /**
     * Enqueue a Vite bundle with optimal loading strategy
     * UPDATED: Enhanced for optimized modular bundles
     */
    private static function enqueue_bundle($bundle_name, $options = []) {
        error_log("HPH Asset Loader: Attempting to enqueue bundle: {$bundle_name}");

        if (in_array($bundle_name, self::$loaded_bundles)) {
            error_log("HPH Asset Loader: Bundle {$bundle_name} already loaded, skipping");
            return; // Already loaded
        }

        $defaults = [
            'strategy' => 'defer',
            'priority' => 10,
            'type' => 'both'
        ];
        $options = wp_parse_args($options, $defaults);

        // CSS - Only load if type is 'css' or 'both'
        if ($options['type'] === 'css' || $options['type'] === 'both') {
            $css_url = self::get_asset_url($bundle_name, 'css');
            if ($css_url) {
                error_log("HPH Asset Loader: Enqueuing CSS for {$bundle_name}: {$css_url}");
                wp_enqueue_style(
                    "hph-{$bundle_name}-css",
                    $css_url,
                    [],
                    null
                );
            } else {
                error_log("HPH Asset Loader: No CSS found for bundle: {$bundle_name}");
            }
        }

        // JavaScript - Only load if type is 'js' or 'both'
        if ($options['type'] === 'js' || $options['type'] === 'both') {
            $js_url = self::get_asset_url($bundle_name, 'file');
            error_log("HPH Asset Loader: JS URL for {$bundle_name}: " . ($js_url ?: 'FALSE'));

            if ($js_url) {
                $script_args = ['jquery'];

                // Core JavaScript dependencies for other bundles
                if ($bundle_name !== 'core') {
                    $script_args[] = 'hph-core';
                }

                error_log("HPH Asset Loader: Enqueuing script hph-{$bundle_name} with URL: {$js_url}");

                wp_enqueue_script(
                    "hph-{$bundle_name}",
                    $js_url,
                    $script_args,
                    null,
                    true // Load in footer
                );

                error_log("HPH Asset Loader: Script hph-{$bundle_name} enqueued successfully");

                // Add loading strategy
                add_filter('script_loader_tag', function($tag, $handle) use ($bundle_name, $options) {
                    if ($handle === "hph-{$bundle_name}") {
                        $strategy = $options['strategy'];

                        if ($strategy === 'defer') {
                            return str_replace(' src', ' defer src', $tag);
                        } elseif ($strategy === 'async') {
                            return str_replace(' src', ' async src', $tag);
                        } elseif ($strategy === 'module') {
                            return str_replace(' src', ' type="module" src', $tag);
                        }
                    }
                    return $tag;
                }, 10, 2);

                // Add localized data for JS bundles that need AJAX
                if (in_array($bundle_name, ['sitewide', 'archive', 'listings', 'dashboard'])) {
                    self::localize_bundle($bundle_name);
                }
            } else {
                error_log("HPH Asset Loader: FAILED to get JS URL for bundle: {$bundle_name}");
            }
        }

        self::$loaded_bundles[] = $bundle_name;
    }

    /**
     * Add localized data for bundles that need AJAX
     */
    private static function localize_bundle($bundle_name) {
        $localize_data = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_ajax_nonce'),
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'bundleVersion' => '3.0.0-optimized'
        ];

        // Bundle-specific data
        switch ($bundle_name) {
            case 'sitewide':
                // Sitewide bundle needs AJAX for contact form modals
                $localize_data['ajax_url'] = admin_url('admin-ajax.php');

                // Also add the hph_ajax variable for backward compatibility
                wp_localize_script("hph-{$bundle_name}", 'hph_ajax', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hph_ajax_nonce')
                ]);
                break;
            case 'listings':
                // Listing pages need AJAX for property interactions
                $localize_data['listing_id'] = is_singular('listing') ? get_the_ID() : 0;
                $localize_data['mapbox_key'] = get_theme_mod('mapbox_api_key', '');
                break;
            case 'dashboard':
                // Dashboard needs AJAX for CRUD operations
                $localize_data['user_id'] = get_current_user_id();
                $localize_data['can_edit'] = current_user_can('edit_posts');
                break;
            case 'archive':
                // Archive pages need AJAX for filtering and search
                $localize_data['post_type'] = get_post_type();
                break;
        }

        wp_localize_script("hph-{$bundle_name}", 'hphData', $localize_data);
    }

    /**
     * Add resource hints for better performance
     */
    public static function add_resource_hints() {
        // Preconnect to external domains
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";

        // DNS prefetch for likely external resources
        echo '<link rel="dns-prefetch" href="//maps.googleapis.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//www.google-analytics.com">' . "\n";
    }

    /**
     * Add preload tags for critical resources
     */
    public static function add_preload_tags() {
        // Preload critical CSS (most important)
        $critical_css = self::get_asset_url('critical-optimized', 'css');
        if ($critical_css) {
            echo '<link rel="preload" href="' . esc_url($critical_css) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
            echo '<noscript><link rel="stylesheet" href="' . esc_url($critical_css) . '"></noscript>' . "\n";
        }

        // Preload core JS with high priority
        $core_js = self::get_asset_url('core', 'file');
        if ($core_js) {
            echo '<link rel="preload" href="' . esc_url($core_js) . '" as="script">' . "\n";
        }

        // Preload sitewide JS
        $sitewide_js = self::get_asset_url('sitewide', 'file');
        if ($sitewide_js) {
            echo '<link rel="preload" href="' . esc_url($sitewide_js) . '" as="script">' . "\n";
        }
    }

    /**
     * Add modern loading attributes to scripts
     */
    public static function add_script_attributes($tag, $handle, $src) {
        // Add defer to non-critical scripts
        $defer_scripts = [
            'hph-sitewide',
            'hph-dashboard'
        ];

        // Add async to page-specific scripts
        $async_scripts = [
            'hph-listings',
            'hph-archive',
            'hph-agents'
        ];

        if (in_array($handle, $defer_scripts)) {
            return str_replace(' src', ' defer src', $tag);
        }

        if (in_array($handle, $async_scripts)) {
            return str_replace(' src', ' async src', $tag);
        }

        return $tag;
    }

    /**
     * Disable asset concatenation for better caching
     */
    public static function disable_concatenation() {
        // Prevent WordPress from concatenating our optimized bundles
        if (!is_admin()) {
            global $concatenate_scripts;
            $concatenate_scripts = false;
        }
    }
}

// Initialize the Vite asset loader
add_action('init', [HPH_Vite_Asset_Loader::class, 'init']);
add_action('init', [HPH_Vite_Asset_Loader::class, 'disable_concatenation']);
add_filter('script_loader_tag', [HPH_Vite_Asset_Loader::class, 'add_script_attributes'], 10, 3);