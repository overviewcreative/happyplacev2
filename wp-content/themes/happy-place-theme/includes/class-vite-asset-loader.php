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

        // Look for the entry in manifest
        // For CSS files, look for src/css/{name}.css entries
        // For JS files, prioritize legacy versions for browser compatibility
        foreach ($manifest as $key => $asset) {
            // Match CSS files
            if ($type === 'css' && $key === "src/css/{$entry_name}.css" && isset($asset['file'])) {
                return get_template_directory_uri() . '/dist/' . $asset['file'];
            }
            // Match JS files - use legacy for compatibility, modern has ES6 import issues
            if ($type === 'file' && $key === "src/js/{$entry_name}-legacy.js" && isset($asset['file'])) {
                return get_template_directory_uri() . '/dist/' . $asset['file'] . '?v=' . time();
            }
        }

        // Fallback to modern version if legacy not found
        foreach ($manifest as $key => $asset) {
            if ($type === 'file' && $key === "src/js/{$entry_name}.js" && isset($asset['file'])) {
                return get_template_directory_uri() . '/dist/' . $asset['file'];
            }
        }

        return false;
    }

    /**
     * Enqueue Vite assets with optimal loading strategies
     * UPDATED: Uses unified bundle system to eliminate redundancies
     */
    public static function enqueue_vite_assets() {
        // DEBUG: Log that this function is being called
        error_log('HPH Asset Loader: enqueue_vite_assets() called');

        // Critical CSS is already inlined via functions.php

        // 0. LOAD LEGACY POLYFILLS FIRST (Required for SystemJS)
        self::enqueue_legacy_polyfills();

        // 1. UNIFIED CORE ASSETS (Load synchronously - contains consolidated patterns)
        // Eliminates 55 DOM ready patterns, 99 AJAX implementations, 6 validation systems
        self::enqueue_bundle('core', [
            'strategy' => 'blocking',
            'priority' => 1,
            'unified' => true
        ]);

        // 2. SITEWIDE ASSETS (Load with defer - needed soon)
        error_log('HPH Asset Loader: About to enqueue sitewide bundle');
        self::enqueue_bundle('sitewide', [
            'strategy' => 'defer',
            'priority' => 5
        ]);

        // 3. FEATURE-SPECIFIC UNIFIED BUNDLES (Load asynchronously)
        self::enqueue_conditional_assets();
    }

    /**
     * Enqueue Vite legacy polyfills (required for SystemJS)
     */
    private static function enqueue_legacy_polyfills() {
        $manifest = self::get_manifest();

        error_log('HPH Asset Loader: Attempting to load legacy polyfills');

        // Load legacy polyfills for SystemJS support
        if (isset($manifest['vite/legacy-polyfills-legacy']['file'])) {
            $polyfills_url = get_template_directory_uri() . '/dist/' . $manifest['vite/legacy-polyfills-legacy']['file'];

            error_log("HPH Asset Loader: Enqueuing polyfills with URL: {$polyfills_url}");

            wp_enqueue_script(
                'hph-legacy-polyfills',
                $polyfills_url,
                [], // No dependencies
                null,
                false // Load in head, before other scripts
            );

            error_log('HPH Asset Loader: Polyfills enqueued successfully');
        } else {
            error_log('HPH Asset Loader: ERROR - Legacy polyfills not found in manifest');
            error_log('HPH Asset Loader: Available manifest keys: ' . implode(', ', array_keys($manifest)));
        }
    }

    /**
     * Enqueue bundle dependencies from manifest imports
     */
    private static function enqueue_bundle_dependencies($bundle_name) {
        $manifest = self::get_manifest();

        // Find the bundle entry in manifest
        $bundle_key = "src/js/{$bundle_name}-legacy.js";
        if (!isset($manifest[$bundle_key])) {
            $bundle_key = "src/js/{$bundle_name}.js";
        }

        error_log("HPH Asset Loader: Looking for dependencies for bundle key: {$bundle_key}");

        if (isset($manifest[$bundle_key]['imports'])) {
            error_log("HPH Asset Loader: Found " . count($manifest[$bundle_key]['imports']) . " imports for {$bundle_name}");
            foreach ($manifest[$bundle_key]['imports'] as $import_key) {
                if (isset($manifest[$import_key]['file'])) {
                    $import_url = get_template_directory_uri() . '/dist/' . $manifest[$import_key]['file'];
                    $handle = 'hph-' . sanitize_title($import_key);

                    error_log("HPH Asset Loader: Enqueuing dependency {$handle} with URL: {$import_url}");

                    wp_enqueue_script(
                        $handle,
                        $import_url,
                        ['hph-legacy-polyfills'], // Depend on polyfills
                        null,
                        true
                    );
                } else {
                    error_log("HPH Asset Loader: WARNING - Import {$import_key} not found in manifest");
                }
            }
        } else {
            error_log("HPH Asset Loader: No imports found for bundle {$bundle_name} (key: {$bundle_key})");
        }
    }

    /**
     * Enqueue conditional assets based on page type
     * UPDATED: Uses unified bundles to eliminate redundancies
     */
    private static function enqueue_conditional_assets() {
        // Listing archive pages - UNIFIED ARCHIVE BUNDLE
        // Eliminates 7 filter, 5 pagination, 4 view switching implementations
        if (is_post_type_archive('listing')) {
            self::enqueue_bundle('archive', [
                'strategy' => 'async',
                'priority' => 10,
                'unified' => true
            ]);
        }

        // Single listing pages - UNIFIED LISTINGS BUNDLE
        // Eliminates 12 gallery, 8 map, 5 form implementations
        if (is_singular('listing')) {
            self::enqueue_bundle('listings', [
                'strategy' => 'async',
                'priority' => 10,
                'unified' => true
            ]);
        }

        // Other archive pages (cities, agents, events, places) - UNIFIED ARCHIVE BUNDLE
        if (is_archive() && !is_post_type_archive('listing')) {
            self::enqueue_bundle('archive', [
                'strategy' => 'async',
                'priority' => 10,
                'unified' => true
            ]);
        }

        // Agent pages - Uses archive bundle for filtering + agent-specific features
        if (is_post_type_archive('agent') || is_singular('agent')) {
            self::enqueue_bundle('archive', [
                'strategy' => 'async',
                'priority' => 9,
                'unified' => true
            ]);
            self::enqueue_bundle('agents', [
                'strategy' => 'async',
                'priority' => 10
            ]);
        }

        // Dashboard pages - UNIFIED DASHBOARD BUNDLE
        // Eliminates 8 CRUD, 6 table, 4 modal implementations
        if (is_page_template('page-dashboard.php') || is_page('dashboard') ||
            is_page('user-dashboard') || is_page('agent-dashboard')) {
            self::enqueue_bundle('dashboard', [
                'strategy' => 'defer',
                'priority' => 8,
                'unified' => true
            ]);
        }

        // Login pages
        if (is_page_template('page-login.php') || is_page('login')) {
            self::enqueue_bundle('login', [
                'strategy' => 'defer',
                'priority' => 8
            ]);
        }

        // City and local place pages - Use archive bundle for filtering
        if (is_post_type_archive('city') || is_singular('city') ||
            is_post_type_archive('local_place') || is_singular('local_place') ||
            is_post_type_archive('local_event') || is_singular('local_event')) {
            self::enqueue_bundle('archive', [
                'strategy' => 'async',
                'priority' => 10,
                'unified' => true
            ]);
        }

        // Property-related pages that need listing functionality
        if (is_page('advanced-search') || is_page('add-listing') ||
            has_shortcode(get_post_field('post_content'), 'hp_listing_search')) {
            self::enqueue_bundle('listings', [
                'strategy' => 'async',
                'priority' => 10,
                'unified' => true
            ]);
        }
    }

    /**
     * Enqueue a Vite bundle with optimal loading strategy
     * UPDATED: Enhanced for unified bundle system
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
            'unified' => false
        ];
        $options = wp_parse_args($options, $defaults);

        // Load bundle dependencies first
        self::enqueue_bundle_dependencies($bundle_name);

        // CSS
        $css_url = self::get_asset_url($bundle_name, 'css');
        if ($css_url) {
            wp_enqueue_style(
                "hph-{$bundle_name}-css",
                $css_url,
                [],
                null
            );
        }

        // JavaScript
        $js_url = self::get_asset_url($bundle_name, 'file');
        error_log("HPH Asset Loader: JS URL for {$bundle_name}: " . ($js_url ?: 'FALSE'));

        // Extra debugging for sitewide specifically
        if ($bundle_name === 'sitewide') {
            $manifest = self::get_manifest();
            error_log("HPH Asset Loader: Sitewide debugging - looking for legacy first");
            if (isset($manifest['src/js/sitewide-legacy.js'])) {
                error_log("HPH Asset Loader: Found sitewide-legacy entry: " . $manifest['src/js/sitewide-legacy.js']['file']);
            } else {
                error_log("HPH Asset Loader: sitewide-legacy NOT FOUND");
            }
            if (isset($manifest['src/js/sitewide.js'])) {
                error_log("HPH Asset Loader: Found sitewide modern entry: " . $manifest['src/js/sitewide.js']['file']);
            } else {
                error_log("HPH Asset Loader: sitewide modern NOT FOUND");
            }
        }

        if ($js_url) {
            $script_args = ['jquery'];

            // Unified bundles depend on core unified system
            if ($options['unified'] && $bundle_name !== 'core') {
                $script_args[] = 'hph-core-js';
            }

            // Ensure polyfills load first
            $script_args[] = 'hph-legacy-polyfills';

            error_log("HPH Asset Loader: Enqueuing script hph-{$bundle_name}-js with URL: {$js_url}");

            wp_enqueue_script(
                "hph-{$bundle_name}-js",
                $js_url,
                $script_args,
                null,
                true // Load in footer
            );

            error_log("HPH Asset Loader: Script hph-{$bundle_name}-js enqueued successfully");

            // Add loading strategy and unified bundle data
            add_filter('script_loader_tag', function($tag, $handle) use ($bundle_name, $options) {
                if ($handle === "hph-{$bundle_name}-js") {
                    $strategy = $options['strategy'];

                    // Add unified bundle data attribute for debugging
                    if ($options['unified']) {
                        $tag = str_replace('<script ', '<script data-unified-bundle="' . esc_attr($bundle_name) . '" ', $tag);
                    }

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

            // Add localized data for unified bundles and sitewide bundle (needs AJAX for modals)
            if ($options['unified'] || $bundle_name === 'sitewide') {
                self::localize_unified_bundle($bundle_name);
            }
        } else {
            error_log("HPH Asset Loader: FAILED to get JS URL for bundle: {$bundle_name}");
        }

        self::$loaded_bundles[] = $bundle_name;
    }

    /**
     * Add localized data for unified bundles
     */
    private static function localize_unified_bundle($bundle_name) {
        $localize_data = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_' . $bundle_name . '_nonce'),
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'bundleVersion' => '3.0.0-unified',
            'eliminatedRedundancies' => true
        ];

        // Bundle-specific data
        switch ($bundle_name) {
            case 'core':
                $localize_data['eliminatedPatterns'] = [
                    'domReady' => 55,
                    'ajax' => 99,
                    'validation' => 6
                ];
                break;
            case 'sitewide':
                // Sitewide bundle needs AJAX for contact form modals
                $localize_data['ajax_url'] = admin_url('admin-ajax.php');
                $localize_data['nonce'] = wp_create_nonce('hph_ajax_nonce');

                // Also add the hph_ajax variable for backward compatibility
                wp_localize_script("hph-{$bundle_name}-js", 'hph_ajax', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hph_ajax_nonce')
                ]);
                break;
            case 'listings':
                $localize_data['eliminatedPatterns'] = [
                    'galleries' => 12,
                    'maps' => 8,
                    'forms' => 5
                ];
                break;
            case 'dashboard':
                $localize_data['eliminatedPatterns'] = [
                    'crud' => 8,
                    'tables' => 6,
                    'modals' => 4
                ];
                break;
            case 'archive':
                $localize_data['eliminatedPatterns'] = [
                    'filters' => 7,
                    'pagination' => 5,
                    'views' => 4
                ];
                break;
        }

        wp_localize_script("hph-{$bundle_name}-js", 'hph' . ucfirst($bundle_name) . 'Data', $localize_data);
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
        // Preload core CSS (after critical CSS)
        $core_css = self::get_asset_url('core', 'css');
        if ($core_css) {
            echo '<link rel="preload" href="' . esc_url($core_css) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
            echo '<noscript><link rel="stylesheet" href="' . esc_url($core_css) . '"></noscript>' . "\n";
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
            'hph-sitewide-js',
            'hph-dashboard-js'
        ];

        // Add async to page-specific scripts
        $async_scripts = [
            'hph-listings-js',
            'hph-archive-js',
            'hph-agents-js'
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