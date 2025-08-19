<?php
/**
 * Happy Place Theme Functions
 * 
 * Enhanced theme setup and initialization for the Happy Place real estate platform
 * Includes bridge function loading, asset management, and WordPress integrations
 *
 * @package HappyPlaceTheme
 * @version 2.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme constants
 */
define('HPT_VERSION', wp_get_theme()->get('Version') ?: '2.1.0');
define('HPT_THEME_URL', get_template_directory_uri());
define('HPT_THEME_PATH', get_template_directory());

/**
 * Main Theme Setup
 */
function happy_place_theme_setup() {
    // Load theme textdomain for translations
    load_theme_textdomain('happy-place-theme', HPT_THEME_PATH . '/languages');
    
    // Add theme support
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'script',
        'style'
    ));
    add_theme_support('custom-logo', array(
        'height'      => 80,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('responsive-embeds');
    add_theme_support('wp-block-styles');
    add_theme_support('align-wide');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'happy-place-theme'),
        'footer'  => esc_html__('Footer Menu', 'happy-place-theme'),
        'mobile'  => esc_html__('Mobile Menu', 'happy-place-theme'),
    ));
    
    // Add custom image sizes for real estate
    add_image_size('listing-thumbnail', 400, 300, true);
    add_image_size('listing-medium', 600, 450, true);
    add_image_size('listing-large', 800, 600, true);
    add_image_size('listing-hero', 1200, 600, true);
    add_image_size('agent-avatar', 150, 150, true);
    add_image_size('agent-profile', 300, 300, true);
    
    // Content width for media embeds
    if (!isset($content_width)) {
        $content_width = 1200;
    }
}
add_action('after_setup_theme', 'happy_place_theme_setup');

/**
 * Load Core Components
 * Organized loading of all theme components
 */
function happy_place_load_components() {
    $components = array(
        // Core systems (load first)
        'asset-manager-simple.php',    // Enhanced asset management
        'template-functions.php',       // Template helper functions
        'template-hooks.php',          // WordPress hooks and filters
        'customizer.php',              // Theme customizer options
        
        // Integration components
        'plugin-integration.php',       // Plugin compatibility
        'template-utilities.php',       // Template utilities
        
        // Optional components (load if exist)
        'performance.php',             // Performance optimizations
        'security.php',                // Security enhancements
        'seo.php',                     // SEO integrations
        'sample-data.php',             // Sample data for testing
    );
    
    foreach ($components as $component) {
        $filepath = HPT_THEME_PATH . '/inc/' . $component;
        if (file_exists($filepath)) {
            require_once $filepath;
        } else {
            // Log missing optional files in debug mode
            if (defined('WP_DEBUG') && WP_DEBUG && !in_array($component, array(
                'performance.php', 'security.php', 'seo.php'
            ))) {
                error_log("Happy Place Theme: Missing component - {$filepath}");
            }
        }
    }
}
happy_place_load_components();

/**
 * Load Bridge Functions
 * 
 * Bridge functions provide consistent data access patterns
 * for all custom post types and WordPress integration
 */
function happy_place_load_bridge_functions() {
    $bridge_files = array(
        // Core post type bridges
        'listing-bridge.php',
        'agent-bridge.php',
        'community-bridge.php',
        'open-house-bridge.php',
        'transaction-bridge.php',
        'team-bridge.php',
        'city-bridge.php',
        'local-place-bridge.php',
        
        // Utility bridges
        'search-bridge.php',
        'map-bridge.php',
        'form-bridge.php',
        'analytics-bridge.php',
    );
    
    $bridge_path = HPT_THEME_PATH . '/inc/bridge/';
    $loaded_count = 0;
    
    foreach ($bridge_files as $file) {
        $filepath = $bridge_path . $file;
        if (file_exists($filepath)) {
            require_once $filepath;
            $loaded_count++;
        }
    }
    
    // Store loaded bridge count for debugging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        add_action('wp_footer', function() use ($loaded_count) {
            if (current_user_can('manage_options')) {
                echo "\n<!-- Bridge Functions Loaded: {$loaded_count} -->\n";
            }
        });
    }
}
happy_place_load_bridge_functions();

/**
 * Check Happy Place Plugin Integration
 */
function is_happy_place_plugin_active() {
    return class_exists('HappyPlace\\Plugin') && function_exists('get_field');
}

/**
 * Enhanced Query Modifications
 */
function happy_place_query_modifications($query) {
    if (!is_admin() && $query->is_main_query()) {
        // Listing archive modifications
        if (is_post_type_archive('listing') || is_tax()) {
            // Handle search parameters
            if (get_query_var('listing_search')) {
                $search = sanitize_text_field(get_query_var('listing_search'));
                $query->set('s', $search);
            }
            
            // Build meta query for filters
            $meta_query = array('relation' => 'AND');
            
            // Property type filter
            if (get_query_var('property_type')) {
                $meta_query[] = array(
                    'key' => 'property_type',
                    'value' => sanitize_text_field(get_query_var('property_type')),
                    'compare' => '='
                );
            }
            
            // Price range filters
            if (get_query_var('min_price')) {
                $meta_query[] = array(
                    'key' => 'listing_price',
                    'value' => intval(get_query_var('min_price')),
                    'type' => 'NUMERIC',
                    'compare' => '>='
                );
            }
            
            if (get_query_var('max_price')) {
                $meta_query[] = array(
                    'key' => 'listing_price',
                    'value' => intval(get_query_var('max_price')),
                    'type' => 'NUMERIC',
                    'compare' => '<='
                );
            }
            
            // Bedroom filter
            if (get_query_var('bedrooms')) {
                $meta_query[] = array(
                    'key' => 'bedrooms',
                    'value' => intval(get_query_var('bedrooms')),
                    'type' => 'NUMERIC',
                    'compare' => '>='
                );
            }
            
            // Bathroom filter
            if (get_query_var('bathrooms')) {
                $meta_query[] = array(
                    'key' => 'bathrooms',
                    'value' => intval(get_query_var('bathrooms')),
                    'type' => 'NUMERIC',
                    'compare' => '>='
                );
            }
            
            // Only show active listings by default
            $meta_query[] = array(
                'key' => 'listing_status',
                'value' => 'active',
                'compare' => '='
            );
            
            if (count($meta_query) > 1) {
                $query->set('meta_query', $meta_query);
            }
            
            // Set posts per page
            $query->set('posts_per_page', apply_filters('hpt_listings_per_page', 12));
            
            // Default ordering by date, allow custom ordering
            $orderby = get_query_var('orderby') ?: 'date';
            $order = get_query_var('order') ?: 'DESC';
            
            if ($orderby === 'price') {
                $query->set('meta_key', 'listing_price');
                $query->set('orderby', 'meta_value_num');
            } else {
                $query->set('orderby', $orderby);
            }
            $query->set('order', $order);
        }
    }
}
add_action('pre_get_posts', 'happy_place_query_modifications');

/**
 * Register Custom Query Variables
 */
function happy_place_query_vars($vars) {
    $custom_vars = array(
        'listing_search',
        'property_type',
        'min_price',
        'max_price',
        'bedrooms',
        'bathrooms',
        'city',
        'state',
        'zip_code',
        'property_status',
        'features',
        'orderby',
        'order',
        'dashboard_page' // Add dashboard page parameter
    );
    
    return array_merge($vars, $custom_vars);
}
add_filter('query_vars', 'happy_place_query_vars');

/**
 * Utility Functions
 */

// Get property types for filtering
function hpt_get_property_types() {
    $property_types = array(
        'house'        => __('House', 'happy-place-theme'),
        'condo'        => __('Condo', 'happy-place-theme'),
        'townhouse'    => __('Townhouse', 'happy-place-theme'),
        'apartment'    => __('Apartment', 'happy-place-theme'),
        'land'         => __('Land', 'happy-place-theme'),
        'commercial'   => __('Commercial', 'happy-place-theme'),
        'multi_family' => __('Multi-Family', 'happy-place-theme'),
    );
    
    return apply_filters('hpt_property_types', $property_types);
}

// Get price ranges for filtering
function hpt_get_price_ranges() {
    $price_ranges = array(
        50000, 100000, 150000, 200000, 250000, 300000,
        400000, 500000, 600000, 750000, 1000000,
        1500000, 2000000, 3000000, 5000000
    );
    
    return apply_filters('hpt_price_ranges', $price_ranges);
}

// Enhanced pagination for listings
function hpt_listing_pagination($query = null) {
    global $wp_query;
    
    if (!$query) {
        $query = $wp_query;
    }
    
    if ($query->max_num_pages <= 1) {
        return;
    }
    
    $big = 999999999;
    
    $pagination = paginate_links(array(
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $query->max_num_pages,
        'prev_text' => '<i class="fas fa-chevron-left"></i> ' . esc_html__('Previous', 'happy-place-theme'),
        'next_text' => esc_html__('Next', 'happy-place-theme') . ' <i class="fas fa-chevron-right"></i>',
        'type' => 'list',
        'class' => 'hpt-pagination'
    ));
    
    if ($pagination) {
        echo '<nav class="hpt-pagination-wrapper" role="navigation" aria-label="' . esc_attr__('Posts navigation', 'happy-place-theme') . '">';
        echo $pagination;
        echo '</nav>';
    }
}

/**
 * Enhanced Customizer Integration
 */
function happy_place_customizer($wp_customize) {
    // Colors section
    $wp_customize->add_section('happy_place_colors', array(
        'title' => esc_html__('Happy Place Colors', 'happy-place-theme'),
        'priority' => 30,
    ));
    
    // Primary color
    $wp_customize->add_setting('primary_color', array(
        'default' => '#2563eb',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'postMessage',
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'primary_color', array(
        'label' => esc_html__('Primary Color', 'happy-place-theme'),
        'section' => 'happy_place_colors',
    )));
    
    // Homepage settings
    $wp_customize->add_section('happy_place_homepage', array(
        'title' => esc_html__('Homepage Settings', 'happy-place-theme'),
        'priority' => 31,
    ));
    
    // Hero title
    $wp_customize->add_setting('hero_title', array(
        'default' => esc_html__('Find Your Dream Home', 'happy-place-theme'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'postMessage',
    ));
    
    $wp_customize->add_control('hero_title', array(
        'label' => esc_html__('Hero Title', 'happy-place-theme'),
        'section' => 'happy_place_homepage',
        'type' => 'text',
    ));
    
    // Hero subtitle
    $wp_customize->add_setting('hero_subtitle', array(
        'default' => esc_html__('Discover the perfect property with our expert real estate team', 'happy-place-theme'),
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport' => 'postMessage',
    ));
    
    $wp_customize->add_control('hero_subtitle', array(
        'label' => esc_html__('Hero Subtitle', 'happy-place-theme'),
        'section' => 'happy_place_homepage',
        'type' => 'textarea',
    ));
}
add_action('customize_register', 'happy_place_customizer');

/**
 * Output custom CSS from customizer
 */
function happy_place_custom_css() {
    $primary_color = get_theme_mod('primary_color', '#2563eb');
    
    if ($primary_color !== '#2563eb') {
        echo '<style type="text/css">';
        echo ':root {';
        echo '--hph-primary: ' . esc_html($primary_color) . ';';
        echo '--hph-primary-dark: ' . esc_html(hpt_adjust_color($primary_color, -20)) . ';';
        echo '--hph-primary-light: ' . esc_html(hpt_adjust_color($primary_color, 20)) . ';';
        echo '}';
        echo '</style>';
    }
}
add_action('wp_head', 'happy_place_custom_css');

/**
 * Adjust color brightness
 */
function hpt_adjust_color($hex, $steps) {
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));
    
    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }
    
    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';
    
    foreach ($color_parts as $color) {
        $color = hexdec($color); // Convert to decimal
        $color = max(0, min(255, $color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }
    
    return $return;
}

/**
 * Fallback asset enqueuing
 * Only used if Enhanced Asset Manager fails
 */
function happy_place_fallback_scripts() {
    // This should rarely be called thanks to our enhanced asset manager
    if (!wp_style_is('hph-framework', 'enqueued')) {
        wp_enqueue_style(
            'happy-place-fallback',
            get_stylesheet_uri(),
            array(),
            HPT_VERSION
        );
        
        // Ensure jQuery is available
        wp_enqueue_script('jquery');
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Happy Place Theme: Using fallback asset loading');
        }
    }
}
add_action('wp_enqueue_scripts', 'happy_place_fallback_scripts', 999);

/**
 * Performance optimizations
 */
function happy_place_performance_optimizations() {
    // Remove unnecessary WordPress features for real estate sites
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    
    // Disable XML-RPC if not needed
    add_filter('xmlrpc_enabled', '__return_false');
    
    // Remove unnecessary REST API links
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    
    // Clean up WordPress head
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_generator');
}
add_action('init', 'happy_place_performance_optimizations');

/**
 * Debug information for development
 */
if (defined('WP_DEBUG') && WP_DEBUG) {
    function happy_place_debug_info() {
        if (current_user_can('manage_options')) {
            $plugin_active = is_happy_place_plugin_active() ? 'ACTIVE' : 'INACTIVE';
            echo "\n<!-- Happy Place Theme Debug -->\n";
            echo "<!-- Version: " . HPT_VERSION . " -->\n";
            echo "<!-- Plugin Status: {$plugin_active} -->\n";
            echo "<!-- Enhanced Framework: LOADED -->\n";
            echo "<!-- End Debug -->\n\n";
        }
    }
    add_action('wp_footer', 'happy_place_debug_info');
}
/**
 * Dashboard Setup
 */
function hph_dashboard_setup() {
    // Register dashboard page template
    add_action('init', 'hph_register_dashboard_endpoint');
    
    // Add template routing
    add_filter('template_include', 'hph_dashboard_template_routing');
    
    // Dashboard asset loading is handled by HP_Dashboard_Assets class
    // Removed duplicate enqueue action
    
    // Initialize AJAX handlers
    require_once get_template_directory() . '/inc/ajax/dashboard-ajax.php';
    new HPH_Dashboard_Ajax();
    
    // Flush rewrite rules if needed (only on theme activation or when rules change)
    add_action('after_switch_theme', 'hph_flush_dashboard_rewrite_rules');
}

/**
 * Flush rewrite rules for dashboard
 */
function hph_flush_dashboard_rewrite_rules() {
    hph_register_dashboard_endpoint();
    flush_rewrite_rules();
}

/**
 * Manual flush rewrite rules (for testing)
 * Can be called by adding ?flush_rewrite=1 to any admin page
 */
function hph_maybe_flush_rewrite_rules() {
    if (is_admin() && current_user_can('manage_options') && isset($_GET['flush_rewrite'])) {
        hph_flush_dashboard_rewrite_rules();
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>Rewrite rules flushed successfully!</p></div>';
        });
    }
}
add_action('admin_init', 'hph_maybe_flush_rewrite_rules');
add_action('after_setup_theme', 'hph_dashboard_setup');

/**
 * Register dashboard endpoint
 */
function hph_register_dashboard_endpoint() {
    add_rewrite_rule('^agent-dashboard/?', 'index.php?agent_dashboard=1', 'top');
    add_rewrite_tag('%agent_dashboard%', '([^&]+)');
    
    // Ensure query vars are preserved when using agent-dashboard URL
    add_action('parse_request', 'hph_parse_dashboard_request');
}

/**
 * Parse dashboard request and preserve query parameters
 */
function hph_parse_dashboard_request($wp) {
    // If this is an agent-dashboard request, parse additional query parameters
    if (isset($wp->query_vars['agent_dashboard'])) {
        // Get the dashboard_page parameter from the URL
        if (isset($_GET['dashboard_page'])) {
            $wp->query_vars['dashboard_page'] = sanitize_text_field($_GET['dashboard_page']);
        }
        
        // Preserve other query parameters as well
        foreach ($_GET as $key => $value) {
            if (!isset($wp->query_vars[$key])) {
                $wp->query_vars[$key] = sanitize_text_field($value);
            }
        }
    }
}

/**
 * Dashboard template routing
 */
function hph_dashboard_template_routing($template) {
    // Check if this is a dashboard request
    if (get_query_var('agent_dashboard')) {
        // Check if user is logged in and has permission
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(home_url('/agent-dashboard/')));
            exit;
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to access this dashboard.', 'happy-place-theme'));
        }
        
        // Disable admin bar for dashboard
        add_filter('show_admin_bar', '__return_false');
        
        // Always use the main dashboard template
        // The content will be determined within the template based on the dashboard_page parameter
        $dashboard_template = get_template_directory() . '/templates/dashboard/dashboard-main.php';
        
        if (file_exists($dashboard_template)) {
            return $dashboard_template;
        }
    }
    
    return $template;
}

/**
 * Check if we're on the dashboard
 */
function hph_is_dashboard() {
    global $post;
    
    // Check by page template
    if (is_page_template('templates/dashboard/dashboard-main.php')) {
        return true;
    }
    
    // Check by page slug
    if ($post && ($post->post_name === 'dashboard' || $post->post_name === 'agent-dashboard')) {
        return true;
    }
    
    // Check query var
    if (get_query_var('agent_dashboard')) {
        return true;
    }
    
    return false;
}

/**
 * Legacy dashboard assets function - REMOVED
 * Asset loading is now handled by HP_Dashboard_Assets class in dashboard-assets.php
 * This avoids conflicts and provides better organization
 */
// Include dashboard initialization
require_once get_template_directory() . '/inc/dashboard/dashboard-init.php';