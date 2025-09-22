<?php
/**
 * Happy Place Theme Functions
 * 
 * Minimal bootstrap file - all functionality is organized into classes
 * 
 * @package HappyPlaceTheme
 * @version 2.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core Theme Constants
 */
define('HPH_VERSION', wp_get_theme()->get('Version') ?: '2.2.0');
define('HPH_THEME_DIR', get_template_directory());
define('HPH_THEME_URI', get_template_directory_uri());
define('HPH_DEV_MODE', defined('WP_DEBUG') && WP_DEBUG);

// Additional constants needed by theme classes
define('HPH_INC_DIR', HPH_THEME_DIR . '/includes');
define('HPH_ASSETS_DIR', HPH_THEME_DIR . '/assets');
define('HPH_TEMPLATE_DIR', HPH_THEME_DIR . '/template-parts');

/**
 * Load unified asset system test if requested (admin users only)
 */
if (isset($_GET['test_assets']) && current_user_can('manage_options')) {
    require_once HPH_THEME_DIR . '/test-unified-assets.php';
}

/**
 * Load template parts debug if requested (admin users only)
 */
if (isset($_GET['debug_template_parts']) && current_user_can('manage_options')) {
    require_once HPH_THEME_DIR . '/debug-template-parts.php';
}

/**
 * Load image functionality test if requested (admin users only)
 */
if (isset($_GET['test_image_functionality']) && current_user_can('manage_options')) {
    require_once HPH_THEME_DIR . '/test-image-functionality.php';
    exit;
}

/**
 * Load asset loader test if requested (admin users only)
 */
if (isset($_GET['test_asset_loader']) && current_user_can('manage_options')) {
    require_once HPH_THEME_DIR . '/test-asset-loader.php';
    exit;
}

/**
 * Load render output test if requested (admin users only)
 */
if (isset($_GET['test_render_output']) && current_user_can('manage_options')) {
    require_once HPH_THEME_DIR . '/test-render-output.php';
    exit;
}

/**
 * Load universal card test if requested (admin users only)
 */
if (isset($_GET['test-universal-cards']) && current_user_can('manage_options')) {
    require_once HPH_THEME_DIR . '/test-universal-cards.php';
}

/**
 * Load asset loading test if requested (admin users only)
 */
if (isset($_GET['test_asset_loading']) && current_user_can('manage_options')) {
    require_once HPH_THEME_DIR . '/test-asset-loading.php';
    exit;
}

/**
 * Load open house demo (available to all users)
 */
if (isset($_GET['open_house_demo']) || 
    (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/open-house-demo') !== false)) {
    require_once ABSPATH . 'open-house-demo.php';
    exit;
}

/**
 * Create listing form page
 */
function hph_create_listing_form_page() {
    // Check if page already exists
    $page = get_page_by_path('listing-form');
    
    if (!$page) {
        $page_data = array(
            'post_title'    => 'Listing Form',
            'post_name'     => 'listing-form', 
            'post_content'  => 'This page uses the Listing Form template to add/edit property listings.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
        );
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id) {
            update_post_meta($page_id, '_wp_page_template', 'page-listing-form.php');
        }
    }
}
add_action('after_setup_theme', 'hph_create_listing_form_page');

/**
 * Initialize Happy Place Theme
 * 
 * This single require loads the entire theme system through
 * an organized, class-based architecture
 */
require_once HPH_THEME_DIR . '/includes/class-hph-theme.php';

// Load optimized Vite Asset Loader for render performance (replaces HPH_Simple_Assets)
require_once HPH_THEME_DIR . '/includes/class-vite-asset-loader.php';

// Initialize the Vite Asset Loader
HPH_Vite_Asset_Loader::init();

// Load maintenance mode functionality
require_once HPH_THEME_DIR . '/includes/class-maintenance-mode.php';

// Load property formatting helpers
require_once HPH_THEME_DIR . '/includes/property-formatting-helpers.php';

// Load image helper functions
require_once HPH_THEME_DIR . '/includes/helpers/image-helpers.php';

// Load lazy loading helpers
require_once HPH_THEME_DIR . '/includes/helpers/lazy-loading-helpers.php';

// Load WebP optimization system
require_once HPH_THEME_DIR . '/includes/helpers/webp-optimization.php';

// Load remaining theme AJAX handlers (delegation pattern)
// NOTE: dashboard-ajax.php now loaded via class-hph-theme.php
require_once HPH_THEME_DIR . '/includes/ajax/local-places-ajax.php';
require_once HPH_THEME_DIR . '/includes/ajax/cities-ajax.php';

// Include bridge functions for plugin delegation
require_once HPH_THEME_DIR . '/includes/bridge/email-bridge.php';
require_once HPH_THEME_DIR . '/includes/bridge/cpt-bridge.php';
require_once HPH_THEME_DIR . '/includes/bridge/query-bridge.php';

// Include hero helpers
require_once HPH_THEME_DIR . '/includes/helpers/archive-hero-helpers.php';


/**
 * Prevent Raw JSON Display from Direct admin-ajax.php Access
 * This prevents users from seeing raw JSON when forms submit directly
 */
add_action('init', function() {
    // Only apply this protection for admin-ajax.php requests
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        return;
    }

    // Check if this is a direct browser access (not an AJAX request)
    $is_direct_access = !empty($_SERVER['HTTP_ACCEPT']) &&
                       strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false &&
                       empty($_SERVER['HTTP_X_REQUESTED_WITH']);

    // If it's a direct access to admin-ajax.php, redirect to home page
    if ($is_direct_access && !wp_doing_ajax()) {
        wp_redirect(home_url('/?ajax_error=direct_access'));
        exit;
    }
}, 1);

/**
 * Add AJAX Response Header Protection
 * Ensures AJAX responses have proper headers to prevent browser display
 */
add_action('wp_ajax_hph_route_form', function() {
    // This will be handled by the plugin, but ensure proper headers
    if (!headers_sent()) {
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
    }
}, 1);

add_action('wp_ajax_nopriv_hph_route_form', function() {
    // This will be handled by the plugin, but ensure proper headers
    if (!headers_sent()) {
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
    }
}, 1);

// Bootstrap the theme
HPH_Theme::init();

// Temporary fix for listing archive query - removed as no longer needed

/**
 * Template redirect hook removed to prevent redirect loops
 * JavaScript form interception in header.php handles search redirection instead
 */

// Lead handling is now managed by the Happy Place plugin's UnifiedLeadService
// Bridge functions available via lead-bridge.php provide access to plugin service

// Email configuration now handled by plugin EmailService
// Bridge functions provide access to email functionality

/**
 * Initialize dashboard AJAX handlers
 */
// Dashboard AJAX handlers are now initialized automatically via class-hph-theme.php
// in the delegation pattern (dashboard-ajax.php)

/**
 * Slug Redirect System
 * Handles singular/plural URL variations and common redirect needs
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */
class HPH_Slug_Redirects {
    
    /**
     * Initialize redirects
     */
    public static function init() {
        add_action('template_redirect', [self::class, 'handle_redirects']);
    }
    
    /**
     * Define redirect rules
     * Maps from-slug => to-slug
     */
    private static function get_redirect_rules() {
        return [
            // Singular to Plural
            'mortgage' => 'mortgages',
            'buyer' => 'buyers',
            'seller' => 'sellers',
            'listing' => 'listing', // Keep as is - this is the archive
            
            // Plural to Singular (if needed in reverse)
            // Uncomment if you want reverse redirects
            // 'contacts' => 'contact',
            
            // Common variations
            'mortgage-calculator' => 'mortgages',
            'mortgage-rates' => 'mortgages', 
            'home-buyer' => 'buyers',
            'home-buyers' => 'buyers',
            'home-seller' => 'sellers',
            'home-sellers' => 'sellers',
            
            // Alternative spellings/variations
            'about-us' => 'about',
            'contact-us' => 'contact',
            'get-started' => 'contact',
            
            // Legacy URLs (add any old URLs you want to redirect)
            'properties' => 'listing',
            'homes' => 'listing',
            'real-estate' => 'listing',
        ];
    }
    
    /**
     * Handle redirects on template_redirect
     */
    public static function handle_redirects() {
        // Only run on main site, not admin
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }
        
        // Get current request URI
        $request_uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        
        // Skip if empty (home page)
        if (empty($request_uri)) {
            return;
        }
        
        // Remove trailing slashes for comparison
        $slug = rtrim($request_uri, '/');
        
        // Get redirect rules
        $redirect_rules = self::get_redirect_rules();
        
        // Check if this slug needs redirecting
        if (isset($redirect_rules[$slug])) {
            $target_slug = $redirect_rules[$slug];
            $redirect_url = home_url('/' . $target_slug . '/');
            
            // Log the redirect for debugging (only in dev mode)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("HPH Redirect: {$slug} -> {$target_slug}");
            }
            
            // Perform 301 redirect
            wp_redirect($redirect_url, 301);
            exit;
        }
        
        // Handle nested paths (e.g., /mortgage/something -> /mortgages/something)
        foreach ($redirect_rules as $from_slug => $to_slug) {
            if (strpos($slug, $from_slug . '/') === 0) {
                $new_path = str_replace($from_slug . '/', $to_slug . '/', $slug);
                $redirect_url = home_url('/' . $new_path . '/');
                
                // Log the redirect for debugging (only in dev mode)
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("HPH Nested Redirect: {$slug} -> {$new_path}");
                }
                
                // Perform 301 redirect
                wp_redirect($redirect_url, 301);
                exit;
            }
        }
    }
    
    /**
     * Add custom redirect rule (for use by other code)
     */
    public static function add_redirect($from_slug, $to_slug) {
        // This would require storing in options or a filter
        // For now, rules are defined in get_redirect_rules()
        // Could be enhanced to store in database if needed
    }
}

// Initialize the redirect system
// Front-end role-based redirects (disabled to prevent wp-admin access issues)
// HPH_Slug_Redirects::init();

/**
 * Add admin menu for redirect testing (only for admins)
 */
if (is_admin()) {
    add_action('admin_menu', function() {
        add_submenu_page(
            'tools.php',
            'Slug Redirects Test',
            'Slug Redirects',
            'manage_options',
            'hph-redirects',
            'hph_redirects_test_page'
        );
    });
    
    function hph_redirects_test_page() {
        $redirect_rules = [
            'mortgage' => 'mortgages',
            'buyer' => 'buyers', 
            'seller' => 'sellers',
            'about-us' => 'about',
            'contact-us' => 'contact',
            'properties' => 'listing',
            'homes' => 'listing'
        ];
        
        echo '<div class="wrap">';
        echo '<h1>Slug Redirects Test</h1>';
        echo '<p>The following redirects are configured:</p>';
        echo '<table class="widefat">';
        echo '<thead><tr><th>From URL</th><th>To URL</th><th>Test Link</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($redirect_rules as $from => $to) {
            $from_url = home_url('/' . $from);
            $to_url = home_url('/' . $to);
            echo '<tr>';
            echo '<td><code>/' . $from . '</code></td>';
            echo '<td><code>/' . $to . '</code></td>';
            echo '<td><a href="' . $from_url . '" target="_blank">Test →</a></td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        echo '<br><p><strong>How to test:</strong><br>';
        echo '1. Click any "Test →" link above<br>';
        echo '2. It should redirect you to the correct page<br>';
        echo '3. Check the URL in your browser to confirm the redirect worked</p>';
        echo '</div>';
    }
}

/**
 * Enqueue Login Page Styles
 */
function hph_enqueue_login_page_styles() {
    if (is_page_template('page-login.php') || is_page_template('page-registration.php')) {
        // Enqueue login-specific styles (utilities are included in main CSS)
        wp_enqueue_style(
            'hph-login-styles',
            HPH_THEME_URI . '/src/css/login.css',
            array('hph-framework'), // Depend on main framework
            HPH_VERSION
        );
    }
}
// Login redirect handling (disabled to prevent login issues)
// add_action('wp_enqueue_scripts', 'hph_enqueue_login_page_styles');

/**
 * Custom Login Page Template Detection
 */
function hph_login_page_template($template) {
    if (is_page() && get_page_template_slug() === 'page-login.php') {
        $new_template = locate_template(array('page-login.php'));
        if ('' != $new_template) {
            return $new_template;
        }
    }
    return $template;
}
// Login redirect handling (disabled to prevent login issues)
// add_filter('template_include', 'hph_login_page_template');

/**
 * Helper function to get brand logo (if not already defined)
 */
if (!function_exists('hph_get_brand_logo')) {
    function hph_get_brand_logo() {
        $custom_logo = get_theme_mod('custom_brand_logo');
        if ($custom_logo) {
            return wp_get_attachment_image_url($custom_logo, 'full');
        }
        return false;
    }
}

/**
 * Helper function for social login buttons placeholder
 */
if (!function_exists('hph_social_login_buttons')) {
    function hph_social_login_buttons() {
        // Placeholder for social login integration
        // This could be integrated with plugins like NextendSocialLogin
        echo '<div class="hph-social-login-buttons">';
        echo '<p class="hph-text-xs hph-text-gray-500">' . __('Social login integration available via plugins', 'happy-place-theme') . '</p>';
        echo '</div>';
    }
}

/**
 * Check if current user can edit a specific listing
 * MIGRATED: Now uses plugin services via bridge functions
 */
function hph_can_user_edit_listing($listing_id) {
    // Use bridge function that connects to plugin services
    return hpt_can_user_edit_listing($listing_id);
}

/**
 * Check if current user can delete a specific listing
 * MIGRATED: Now uses plugin services via bridge functions
 */
function hph_can_user_delete_listing($listing_id) {
    // Use bridge function that connects to plugin services
    return hpt_can_user_delete_listing($listing_id);
}

/**
 * User Registration Action Hooks
 * Handle post-registration actions for better plugin compatibility
 */

// Custom action hook for when a user registers via our custom registration form
add_action('hph_user_registered', 'hph_handle_user_registration', 10, 2);

function hph_handle_user_registration($user_id, $user_type) {
    // MIGRATED: Now uses plugin services via bridge functions
    hpt_handle_user_registration($user_id, $user_type);
}

// Hook into WordPress user registration for additional processing
add_action('user_register', 'hph_wordpress_user_registration', 10, 1);

function hph_wordpress_user_registration($user_id) {
    // This fires for any user registration (including wp-admin, plugins, etc.)
    // Add any universal post-registration logic here
    
    // Send welcome email (if not already sent by wp_new_user_notification)
    $user = get_user_by('ID', $user_id);
    if ($user && !get_user_meta($user_id, 'welcome_email_sent', true)) {
        // Flag that we've sent welcome email to prevent duplicates
        update_user_meta($user_id, 'welcome_email_sent', true);
    }
}

// Customize new user notification emails
add_filter('wp_new_user_notification_email', 'hph_customize_new_user_notification', 10, 3);

function hph_customize_new_user_notification($wp_new_user_notification_email, $user, $blogname) {
    $user_type = get_user_meta($user->ID, 'user_type', true);
    
    // Customize subject based on user type
    switch ($user_type) {
        case 'agent':
            $subject = sprintf('[%s] Welcome to our Real Estate Platform - Agent Account Created', $blogname);
            break;
        case 'seller':
            $subject = sprintf('[%s] Welcome! Ready to List Your Property?', $blogname);
            break;
        case 'buyer':
            $subject = sprintf('[%s] Welcome! Start Your Home Search Journey', $blogname);
            break;
        case 'investor':
            $subject = sprintf('[%s] Welcome to Investment Opportunities', $blogname);
            break;
        default:
            $subject = sprintf('[%s] Welcome to Our Platform', $blogname);
            break;
    }
    
    $wp_new_user_notification_email['subject'] = $subject;
    
    return $wp_new_user_notification_email;
}

/**
 * Authentication Redirect System
 * Override WordPress default login/register pages with custom pages
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */
class HPH_Auth_Redirects {
    
    /**
     * Initialize authentication redirects
     */
    public static function init() {
        // Redirect wp-login.php to custom login page
        add_action('login_init', [self::class, 'redirect_login_page']);
        
        // Override login URL
        add_filter('login_url', [self::class, 'custom_login_url'], 10, 3);
        
        // Override registration URL
        add_filter('register_url', [self::class, 'custom_register_url']);
        
        // Override lost password URL
        add_filter('lostpassword_url', [self::class, 'custom_lost_password_url'], 10, 2);
        
        // Redirect after logout
        add_action('wp_logout', [self::class, 'redirect_after_logout']);
        
        // Create login and register pages if they don't exist
        add_action('after_setup_theme', [self::class, 'create_auth_pages']);
    }
    
    /**
     * Redirect wp-login.php requests to custom login page
     */
    public static function redirect_login_page() {
        // Skip if we're doing AJAX or if this is the admin
        if (wp_doing_ajax() || is_admin()) {
            return;
        }
        
        // Skip if user is already logged in and accessing wp-admin
        if (is_user_logged_in() && isset($_GET['action']) && $_GET['action'] === 'logout') {
            return; // Allow logout
        }
        
        // Get the action parameter
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        
        // Determine redirect URL based on action
        switch ($action) {
            case 'register':
                $redirect_url = home_url('/register/');
                break;
            case 'lostpassword':
            case 'rp':
                $redirect_url = wp_lostpassword_url();
                break;
            case 'logout':
                return; // Don't redirect logout
            default:
                $redirect_url = home_url('/login/');
                
                // Preserve redirect_to parameter
                if (isset($_GET['redirect_to'])) {
                    $redirect_url = add_query_arg('redirect_to', urlencode($_GET['redirect_to']), $redirect_url);
                }
                break;
        }
        
        // Perform the redirect
        wp_redirect($redirect_url, 302);
        exit;
    }
    
    /**
     * Override login URL
     */
    public static function custom_login_url($login_url, $redirect, $force_reauth) {
        $custom_url = home_url('/login/');
        
        if (!empty($redirect)) {
            $custom_url = add_query_arg('redirect_to', urlencode($redirect), $custom_url);
        }
        
        if ($force_reauth) {
            $custom_url = add_query_arg('reauth', '1', $custom_url);
        }
        
        return $custom_url;
    }
    
    /**
     * Override registration URL
     */
    public static function custom_register_url() {
        return home_url('/register/');
    }
    
    /**
     * Override lost password URL
     */
    public static function custom_lost_password_url($lostpassword_url) {
        // For now, keep using WordPress default for password reset
        // You can create a custom password reset page later if needed
        return $lostpassword_url;
    }
    
    /**
     * Redirect to custom login page after logout
     */
    public static function redirect_after_logout() {
        $redirect_url = home_url('/login/?logged_out=1');
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Create login and register pages if they don't exist
     */
    public static function create_auth_pages() {
        // Create login page
        $login_page = get_page_by_path('login');
        if (!$login_page) {
            $login_page_data = [
                'post_title'    => 'Login',
                'post_name'     => 'login',
                'post_content'  => 'This page uses the Login template for user authentication.',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_author'   => 1,
            ];
            
            $login_page_id = wp_insert_post($login_page_data);
            if ($login_page_id) {
                update_post_meta($login_page_id, '_wp_page_template', 'page-login.php');
            }
        }
        
        // Create register page
        $register_page = get_page_by_path('register');
        if (!$register_page) {
            $register_page_data = [
                'post_title'    => 'Register',
                'post_name'     => 'register',
                'post_content'  => 'This page uses the Register template for user registration.',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_author'   => 1,
            ];
            
            $register_page_id = wp_insert_post($register_page_data);
            if ($register_page_id) {
                update_post_meta($register_page_id, '_wp_page_template', 'page-register.php');
            }
        }
    }
    
    /**
     * Prevent access to wp-login.php via htaccess (optional)
     * This method provides instructions for manual htaccess update
     */
    public static function get_htaccess_rules() {
        return "
# Redirect wp-login.php to custom login page
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^wp-login\.php(.*)$ /login/$1 [R=302,L]
</IfModule>";
    }
}

// Initialize the authentication redirect system
// Front-end role-based redirects (disabled to prevent wp-admin access issues)
// HPH_Auth_Redirects::init();

/**
 * Enqueue scripts and styles for single listing pages
 */
function hph_enqueue_single_listing_assets() {
    if (is_singular('listing')) {
        // Get Mapbox key for localization (Mapbox itself is loaded by HPH_Simple_Assets)
        $mapbox_key = defined('HPH_GOOGLE_MAPS_KEY') ? HPH_GOOGLE_MAPS_KEY : get_theme_mod('mapbox_api_key');
        
        // Note: Asset loading is now handled by HPH_Simple_Assets system
        // which automatically loads single-listing, gallery, hero, and other assets
        
        // Localize script for AJAX operations using correct handle from HPH_Simple_Assets
        wp_localize_script('hph-listing-single', 'hph_listing', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_listing_nonce'),
            'listing_id' => get_the_ID(),
            'mapbox_key' => $mapbox_key
        ]);
        
        // Contact form localization is now handled by HPH_Simple_Assets via hphContext
        // No need for duplicate localization here
    }
}
add_action('wp_enqueue_scripts', 'hph_enqueue_single_listing_assets');

/**
 * Localize User System Script for AJAX operations
 */
function hph_localize_user_system() {
    // Only localize if user-system script will be loaded
    wp_localize_script('hph-sitewide', 'HPUserSystem', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hph_nonce'), // Match plugin service expectation
        'isLoggedIn' => is_user_logged_in(),
        'userId' => get_current_user_id(),
        'strings' => [
            'loginRequired' => __('Please log in to use this feature', 'happy-place-theme'),
            'securityError' => __('Security check failed', 'happy-place-theme'),
            'genericError' => __('Something went wrong. Please try again.', 'happy-place-theme')
        ]
    ]);
}
add_action('wp_enqueue_scripts', 'hph_localize_user_system', 15);

/**
 * Register listing post type if not already registered
 */
function hph_register_listing_post_type() {
    if (!post_type_exists('listing')) {
        register_post_type('listing', [
            'labels' => [
                'name' => 'Listings',
                'singular_name' => 'Listing',
                'add_new' => 'Add New Listing',
                'add_new_item' => 'Add New Listing',
                'edit_item' => 'Edit Listing',
                'new_item' => 'New Listing',
                'view_item' => 'View Listing',
                'search_items' => 'Search Listings',
                'not_found' => 'No listings found',
                'not_found_in_trash' => 'No listings found in trash'
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'listings'],
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'menu_icon' => 'dashicons-building',
            'show_in_rest' => true
        ]);
    }
}
add_action('init', 'hph_register_listing_post_type');


/**
 * AJAX handler for RSVP form submission
 */
function hph_handle_rsvp_submission() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hph_listing_nonce')) {
        wp_die('Security check failed');
    }
    
    // Get form data
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $guests = sanitize_text_field($_POST['guests']);
    $message = sanitize_textarea_field($_POST['message']);
    $listing_id = intval($_POST['listing_id']);
    $open_house_date = sanitize_text_field($_POST['open_house_date']);
    
    // Get agent email
    $agent_id = get_field('listing_agent', $listing_id);
    $agent_email = get_field('agent_email', $agent_id);
    
    if (!$agent_email) {
        $agent_email = get_option('admin_email');
    }
    
    // Prepare email
    $subject = 'New RSVP for Open House - ' . get_the_title($listing_id);
    $body = "New RSVP received for open house:\n\n";
    $body .= "Property: " . get_the_title($listing_id) . "\n";
    $body .= "Open House Date: " . $open_house_date . "\n\n";
    $body .= "Contact Information:\n";
    $body .= "Name: " . $name . "\n";
    $body .= "Email: " . $email . "\n";
    $body .= "Phone: " . $phone . "\n";
    $body .= "Number of Guests: " . $guests . "\n";
    
    if ($message) {
        $body .= "\nMessage:\n" . $message;
    }
    
    // Send email
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    $sent = wp_mail($agent_email, $subject, $body, $headers);
    
    if ($sent) {
        wp_send_json_success(['message' => 'RSVP submitted successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to submit RSVP']);
    }
}
add_action('wp_ajax_hph_submit_rsvp', 'hph_handle_rsvp_submission');
add_action('wp_ajax_nopriv_hph_submit_rsvp', 'hph_handle_rsvp_submission');

/**
 * AJAX handler for contact form submission
 */
function hph_handle_contact_submission() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hph_listing_nonce')) {
        wp_die('Security check failed');
    }
    
    // Get form data
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $message = sanitize_textarea_field($_POST['message']);
    $listing_id = intval($_POST['listing_id']);
    $agent_id = intval($_POST['agent_id']);
    
    // Get agent email
    $agent_email = get_field('agent_email', $agent_id);
    
    if (!$agent_email) {
        $agent_email = get_option('admin_email');
    }
    
    // Prepare email
    $subject = 'New Inquiry for ' . get_the_title($listing_id);
    $body = "New inquiry received:\n\n";
    $body .= "Property: " . get_the_title($listing_id) . "\n";
    $body .= "Link: " . get_permalink($listing_id) . "\n\n";
    $body .= "Contact Information:\n";
    $body .= "Name: " . $name . "\n";
    $body .= "Email: " . $email . "\n";
    $body .= "Phone: " . $phone . "\n\n";
    $body .= "Message:\n" . $message;
    
    // Send email
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    $sent = wp_mail($agent_email, $subject, $body, $headers);
    
    // Store lead in database (optional)
    if ($sent) {
        // You could store this in a custom table or as a custom post type
        $lead_data = [
            'post_title' => 'Lead: ' . $name,
            'post_type' => 'lead',
            'post_status' => 'private',
            'meta_input' => [
                'lead_name' => $name,
                'lead_email' => $email,
                'lead_phone' => $phone,
                'lead_message' => $message,
                'lead_listing' => $listing_id,
                'lead_agent' => $agent_id
            ]
        ];
        wp_insert_post($lead_data);
        
        wp_send_json_success(['message' => 'Message sent successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to send message']);
    }
}
add_action('wp_ajax_hph_submit_contact', 'hph_handle_contact_submission');
add_action('wp_ajax_nopriv_hph_submit_contact', 'hph_handle_contact_submission');

// After-import one-timer: fetch hero images from Wikipedia and set as featured.
add_action('admin_init', function(){
  if (!current_user_can('manage_options') || !isset($_GET['hpl_fetch_city_heroes'])) return;

  $q = new WP_Query(['post_type' => 'city','posts_per_page'=>-1,'no_found_rows'=>true]);
  $done = 0;
  while ($q->have_posts()) { $q->the_post();
    $post_id = get_the_ID();
    $wiki = get_post_meta($post_id, 'hero_image_url', true); // we stored the page URL here
    if (!$wiki || has_post_thumbnail($post_id)) continue;

    // Convert Wikipedia page → REST summary → thumbnail URL
    $slug = basename(parse_url($wiki, PHP_URL_PATH)); // e.g. Lewes,_Delaware
    $api  = "https://en.wikipedia.org/api/rest_v1/page/summary/{$slug}";

    $resp = wp_remote_get($api, ['timeout'=>20]);
    if (is_wp_error($resp)) continue;

    $data = json_decode(wp_remote_retrieve_body($resp), true);
    $img  = $data['thumbnail']['source'] ?? '';
    if (!$img) continue;

    // Sideload and set featured image
    $att_id = media_sideload_image($img, $post_id, get_the_title($post_id), 'id');
    if (!is_wp_error($att_id)) {
      set_post_thumbnail($post_id, $att_id);
      $done++;
    }
  }
  wp_reset_postdata();

  wp_die("HPL City hero import complete. Set {$done} featured images. You can remove this snippet.");
});

/**
 * AJAX Handler for Toggling Saved Listings
 */
add_action('wp_ajax_toggle_saved_listing', 'hph_handle_toggle_saved_listing');
add_action('wp_ajax_nopriv_toggle_saved_listing', 'hph_handle_toggle_saved_listing_guest');

function hph_handle_toggle_saved_listing() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hph_listing_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
        return;
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Please log in to save listings']);
        return;
    }
    
    $user_id = get_current_user_id();
    $listing_id = intval($_POST['listing_id']);
    
    if (!$listing_id) {
        wp_send_json_error(['message' => 'Invalid listing ID']);
        return;
    }
    
    // Get current saved listings
    $saved_listings = get_user_meta($user_id, 'saved_listings', true);
    if (!is_array($saved_listings)) {
        $saved_listings = [];
    }
    
    // Toggle listing in saved array
    $key = array_search($listing_id, $saved_listings);
    if ($key !== false) {
        // Remove from saved
        unset($saved_listings[$key]);
        $saved_listings = array_values($saved_listings); // Re-index array
        $action = 'removed';
        $message = 'Listing removed from saved properties';
    } else {
        // Add to saved
        $saved_listings[] = $listing_id;
        $action = 'added';
        $message = 'Listing saved to your favorites';
    }
    
    // Update user meta
    update_user_meta($user_id, 'saved_listings', $saved_listings);
    
    wp_send_json_success([
        'message' => $message,
        'action' => $action,
        'listing_id' => $listing_id,
        'total_saved' => count($saved_listings)
    ]);
}

function hph_handle_toggle_saved_listing_guest() {
    wp_send_json_error(['message' => 'Please log in to save listings']);
}

/**
 * Debug function to test open house calendar data
 */
function hph_debug_open_houses() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Test the calendar query
    $calendar_args = array(
        'view' => 'list',
        'post_type' => 'listing',
        'date_field' => 'open_house_date',
        'time_field' => 'open_house_time',
        'end_date_field' => 'open_house_end_time',
        'location_field' => 'address_full',
        'category_field' => 'property_type',
        'current_date' => date('Y-m-d'),
        'posts_per_page' => 10,
        'additional_meta_query' => array(
            array(
                'key' => 'listing_has_open_house',
                'value' => 'yes',
                'compare' => '='
            )
        )
    );
    
    // Include the calendar component to get the function
    require_once get_template_directory() . '/template-parts/components/universal-calendar.php';
    
    $events_data = hph_get_calendar_events($calendar_args);
    
    echo '<div style="background: #f1f1f1; padding: 20px; margin: 20px; border-radius: 5px;">';
    echo '<h3>Open House Calendar Debug</h3>';
    echo '<p><strong>Found ' . count($events_data['events']) . ' open house events:</strong></p>';
    
    if (!empty($events_data['events'])) {
        echo '<ul>';
        foreach ($events_data['events'] as $event) {
            echo '<li>';
            echo '<strong>' . esc_html($event['title']) . '</strong><br>';
            echo 'Date: ' . esc_html($event['date']) . '<br>';
            echo 'Time: ' . esc_html($event['time']) . '<br>';
            echo 'Location: ' . esc_html($event['location']) . '<br>';
            if (isset($event['property'])) {
                echo 'Price: ' . esc_html($event['property']['price_formatted']) . '<br>';
                echo 'Type: ' . esc_html($event['property']['property_type']) . '<br>';
            }
            echo '</li><br>';
        }
        echo '</ul>';
    } else {
        echo '<p>No open houses found. Check that:</p>';
        echo '<ul>';
        echo '<li>You have listings with open_house_date meta field</li>';
        echo '<li>Listings have listing_has_open_house = "yes"</li>';
        echo '<li>Open house dates are in the future</li>';
        echo '</ul>';
    }
    
    echo '</div>';
}

// Add debug hook if requested
if (isset($_GET['debug_open_houses']) && current_user_can('manage_options')) {
    add_action('wp_footer', 'hph_debug_open_houses');
}

/**
 * EMERGENCY TEMPLATE CACHING SYSTEM
 * Added for launch performance optimization
 */

/**
 * Cache expensive template fragments
 */
function hph_cached_template_part($template, $args = [], $cache_duration = 300) {
    // Create cache key from template and args
    $cache_key = 'hph_template_' . md5($template . serialize($args));

    // Try to get cached version
    $cached_content = get_transient($cache_key);

    if (false === $cached_content) {
        // Start output buffering
        ob_start();

        // Load the template
        get_template_part($template, null, $args);

        // Get the content
        $cached_content = ob_get_clean();

        // Cache for specified duration (default 5 minutes)
        set_transient($cache_key, $cached_content, $cache_duration);
    }

    echo $cached_content;
}

/**
 * Cache expensive query results
 */
function hph_cached_query($query_args, $cache_duration = 300) {
    $cache_key = 'hph_query_' . md5(serialize($query_args));

    $cached_results = get_transient($cache_key);

    if (false === $cached_results) {
        $cached_results = new WP_Query($query_args);
        set_transient($cache_key, $cached_results, $cache_duration);
    }

    return $cached_results;
}

/**
 * Clear template cache when content is updated
 */
function hph_clear_template_cache($post_id) {
    // Clear all template-related transients
    global $wpdb;

    $wpdb->query(
        "DELETE FROM {$wpdb->options}
         WHERE option_name LIKE '_transient_hph_template_%'
         OR option_name LIKE '_transient_hph_query_%'"
    );
}

// Hook cache clearing to content updates
add_action('save_post', 'hph_clear_template_cache');
add_action('delete_post', 'hph_clear_template_cache');

/**
 * Emergency inline CSS cleanup utility
 */
function hph_cleanup_inline_styles($content) {
    // Basic inline style cleanup for emergency launch
    $common_patterns = [
        'style="display: none;"' => 'class="hph-hidden"',
        'style="display: block;"' => 'class="hph-visible"',
        'style="text-align: center;"' => 'class="hph-text-center"',
        'style="text-align: left;"' => 'class="hph-text-left"',
        'style="text-align: right;"' => 'class="hph-text-right"',
    ];

    return str_replace(array_keys($common_patterns), array_values($common_patterns), $content);
}

// Add emergency CSS utility classes to head
add_action('wp_head', function() {
    echo '<style>
    .hph-hidden { display: none !important; }
    .hph-visible { display: block !important; }
    .hph-text-center { text-align: center !important; }
    .hph-text-left { text-align: left !important; }
    .hph-text-right { text-align: right !important; }
    .hph-positioned { position: relative !important; }
    .hph-positioned--absolute { position: absolute !important; }
    </style>';
}, 5);

/**
 * CRITICAL CSS OPTIMIZATION SYSTEM
 * Inline critical CSS for above-the-fold content performance
 */

/**
 * Inline critical CSS for immediate above-the-fold rendering
 */
function hph_inline_critical_css() {
    $critical_css_file = get_template_directory() . '/dist/css/critical-' . hph_get_asset_hash('critical') . '.min.css';

    // Fallback to non-hashed version for development
    if (!file_exists($critical_css_file)) {
        $critical_css_file = get_template_directory() . '/dist/css/critical.min.css';
    }

    if (file_exists($critical_css_file)) {
        $critical_css = file_get_contents($critical_css_file);
        $critical_css = hph_optimize_critical_css($critical_css);

        echo '<style id="critical-css">' . $critical_css . '</style>';

        // Preload the full CSS asynchronously
        echo '<link rel="preload" href="' . hph_get_asset_url('core.min.css') . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
        echo '<noscript><link rel="stylesheet" href="' . hph_get_asset_url('core.min.css') . '"></noscript>';
    }
}

/**
 * Optimize critical CSS by removing unnecessary whitespace and comments
 */
function hph_optimize_critical_css($css) {
    // Remove comments
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

    // Remove unnecessary whitespace
    $css = str_replace(array("\r\n", "\r", "\n", "\t"), '', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    $css = str_replace('; ', ';', $css);
    $css = str_replace(' {', '{', $css);
    $css = str_replace('{ ', '{', $css);
    $css = str_replace(' }', '}', $css);
    $css = str_replace('} ', '}', $css);

    return trim($css);
}

/**
 * Get asset hash from manifest for cache busting
 */
function hph_get_asset_hash($asset_name) {
    static $manifest = null;

    if ($manifest === null) {
        $manifest_file = get_template_directory() . '/dist/.vite/manifest.json';
        if (file_exists($manifest_file)) {
            $manifest = json_decode(file_get_contents($manifest_file), true);
        } else {
            $manifest = [];
        }
    }

    // Look for the asset in manifest
    foreach ($manifest as $key => $asset) {
        if (strpos($key, $asset_name) !== false && isset($asset['file'])) {
            $file = $asset['file'];
            // Extract hash from filename
            if (preg_match('/[a-zA-Z0-9_-]+-([a-zA-Z0-9_-]+)\.min\.css$/', $file, $matches)) {
                return $matches[1];
            }
        }
    }

    return '';
}

/**
 * Get optimized asset URL with proper cache busting
 */
function hph_get_asset_url($filename) {
    $asset_hash = hph_get_asset_hash(str_replace('.min.css', '', $filename));

    if ($asset_hash) {
        $hashed_filename = str_replace('.min.css', '-' . $asset_hash . '.min.css', $filename);
        return get_template_directory_uri() . '/dist/css/' . $hashed_filename;
    }

    // Fallback to non-hashed version
    return get_template_directory_uri() . '/dist/css/' . $filename;
}

// Add critical CSS to head with high priority (before other styles)
add_action('wp_head', 'hph_inline_critical_css', 1);

/**
 * PRODUCTION PERFORMANCE MONITORING
 * Add performance tracking for production optimization
 */

/**
 * Performance monitoring for critical metrics
 */
function hph_add_performance_monitoring() {
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        return; // Only for admin users in production
    }

    ?>
    <script>
    // Performance monitoring for critical metrics
    window.addEventListener('load', function() {
        if ('performance' in window) {
            const perfData = performance.getEntriesByType('navigation')[0];
            const loadTime = perfData.loadEventEnd - perfData.loadEventStart;
            const domContentLoaded = perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart;

            // Only log if times are reasonable (avoid invalid data)
            if (loadTime > 0 && loadTime < 30000 && domContentLoaded > 0) {
                console.log('HPH Performance Metrics:', {
                    'Load Time': Math.round(loadTime) + 'ms',
                    'DOM Content Loaded': Math.round(domContentLoaded) + 'ms',
                    'First Paint': perfData.responseStart ? Math.round(perfData.responseStart - perfData.fetchStart) + 'ms' : 'N/A'
                });
            }
        }
    });
    </script>
    <?php
}

// Add performance monitoring for admin users
add_action('wp_footer', 'hph_add_performance_monitoring');

/**
 * Override plugin's default view behavior to prevent automatic map view switching
 * This prevents the map view from becoming "sticky" after visiting the map page
 */
add_filter('hph_property_search_default_view', function($default_view) {
    // Only allow map view if explicitly requested via URL parameter
    if (isset($_GET['view']) && $_GET['view'] === 'map') {
        return 'map';
    }
    // Always default to grid view otherwise
    return 'grid';
}, 10, 1);

// Alternative hook for different plugin filter names
add_filter('happy_place_default_view', function($default_view) {
    if (isset($_GET['view']) && $_GET['view'] === 'map') {
        return 'map';
    }
    return 'grid';
}, 10, 1);

