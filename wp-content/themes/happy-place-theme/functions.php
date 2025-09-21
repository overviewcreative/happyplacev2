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

// Load unified asset system (replaces complex HPH_Assets class and separate enqueue files)
require_once HPH_THEME_DIR . '/includes/assets/theme-assets.php';

// Load property formatting helpers
require_once HPH_THEME_DIR . '/includes/property-formatting-helpers.php';

// Load image helper functions
require_once HPH_THEME_DIR . '/includes/helpers/image-helpers.php';

// Load lazy loading helpers
require_once HPH_THEME_DIR . '/includes/helpers/lazy-loading-helpers.php';

// Load dashboard AJAX handlers
require_once HPH_THEME_DIR . '/includes/ajax/listings-dashboard-ajax.php';
require_once HPH_THEME_DIR . '/includes/ajax/dashboard-ajax.php';


// Bootstrap the theme
HPH_Theme::init();

// Initialize asset system
HPH_Simple_Assets::init();

// Temporary fix for listing archive query
require_once HPH_THEME_DIR . '/archive-listing-fix.php';

// Include unified lead handler
require_once HPH_THEME_DIR . '/includes/class-unified-lead-handler.php';

// Include email configuration for production
require_once HPH_THEME_DIR . '/includes/email-config.php';

/**
 * Initialize unified lead handling system
 */
HPH_Unified_Lead_Handler::init();

/**
 * Initialize dashboard AJAX handlers
 */
new HPH_Listings_Dashboard_Ajax();

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
// TEMPORARILY DISABLED - Causes redirect issues with wp-admin access
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
// TEMPORARILY DISABLED - Causes login redirect issues
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
// TEMPORARILY DISABLED - Causes login redirect issues
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
 * Global helper function for use outside of AJAX handlers
 */
function hph_can_user_edit_listing($listing_id) {
    $current_user_id = get_current_user_id();
    
    // Admins can edit all listings
    if (current_user_can('administrator') || current_user_can('manage_options')) {
        return true;
    }
    
    // Get the assigned listing agent(s)
    $listing_agent = get_field('listing_agent', $listing_id);
    
    if (!$listing_agent) {
        // If no agent assigned, only admins can edit
        return false;
    }
    
    // Handle both single agent and multiple agents
    $agent_ids = is_array($listing_agent) ? $listing_agent : [$listing_agent];
    
    // Check if current user is one of the assigned agents
    foreach ($agent_ids as $agent_id) {
        // Get the synced user ID for this agent
        $synced_user_id = get_post_meta($agent_id, '_synced_user_id', true);
        
        if ($synced_user_id && $synced_user_id == $current_user_id) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check if current user can delete a specific listing
 * Global helper function for use outside of AJAX handlers
 */
function hph_can_user_delete_listing($listing_id) {
    // For now, use same permissions as edit
    return hph_can_user_edit_listing($listing_id);
}

/**
 * User Registration Action Hooks
 * Handle post-registration actions for better plugin compatibility
 */

// Custom action hook for when a user registers via our custom registration form
add_action('hph_user_registered', 'hph_handle_user_registration', 10, 2);

function hph_handle_user_registration($user_id, $user_type) {
    // Set default user role based on user type
    $user = new WP_User($user_id);
    
    switch ($user_type) {
        case 'agent':
            $user->set_role('contributor'); // Agents can create/edit their own content
            break;
        case 'seller':
            $user->set_role('subscriber'); // Sellers have basic access
            update_user_meta($user_id, 'can_list_property', true);
            break;
        case 'buyer':
        case 'investor':
        default:
            $user->set_role('subscriber'); // Default role
            break;
    }
    
    // Set registration date
    update_user_meta($user_id, 'registration_date', current_time('mysql'));
    
    // Set registration source
    update_user_meta($user_id, 'registration_source', 'website_form');
    
    // Log registration for analytics
    error_log("HPH User Registration: User ID {$user_id} registered as {$user_type}");
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
// TEMPORARILY DISABLED - Causes redirect issues with wp-admin access
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

