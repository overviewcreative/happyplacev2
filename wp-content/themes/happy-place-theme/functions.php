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
 * Load system test script if requested (admin users only)
 */
if (isset($_GET['hph_test_systems']) && current_user_can('manage_options')) {
    require_once HPH_THEME_DIR . '/test-system-improvements.php';
}

/**
 * Initialize Happy Place Theme
 * 
 * This single require loads the entire theme system through
 * an organized, class-based architecture
 */
require_once HPH_THEME_DIR . '/includes/class-hph-theme.php';

// Load unified asset system (replaces complex HPH_Assets class and separate enqueue files)
require_once HPH_THEME_DIR . '/includes/assets/theme-assets.php';

// Bootstrap the theme
HPH_Theme::init();

// Initialize asset system
HPH_Simple_Assets::init();

// Temporary fix for listing archive query
require_once HPH_THEME_DIR . '/archive-listing-fix.php';

// Include unified lead handler
require_once HPH_THEME_DIR . '/includes/class-unified-lead-handler.php';

/**
 * Initialize unified lead handling system
 */
HPH_Unified_Lead_Handler::init();

/**
 * Enqueue Login Page Styles
 */
function hph_enqueue_login_page_styles() {
    if (is_page_template('page-login.php') || is_page_template('page-registration.php')) {
        wp_enqueue_style(
            'hph-login-styles',
            HPH_THEME_URI . '/src/css/login.css',
            array(),
            HPH_VERSION
        );
    }
}
add_action('wp_enqueue_scripts', 'hph_enqueue_login_page_styles');

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
add_filter('template_include', 'hph_login_page_template');

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
 * Enqueue scripts and styles for single listing pages
 */
function hph_enqueue_single_listing_assets() {
    if (is_singular('listing')) {
        // Enqueue Mapbox if we have an API key
        $mapbox_key = get_theme_mod('mapbox_api_key');
        if ($mapbox_key) {
            wp_enqueue_style('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css');
            wp_enqueue_script('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], '2.15.0', true);
        }
        
        // Enqueue lightbox for gallery
        wp_enqueue_style('lightbox', get_template_directory_uri() . '/assets/css/lightbox.css');
        wp_enqueue_script('lightbox', get_template_directory_uri() . '/assets/js/lightbox.js', ['jquery'], '1.0.0', true);
        
        // Enqueue single listing specific styles
        wp_enqueue_style('single-listing', get_template_directory_uri() . '/assets/css/single-listing.css');
        wp_enqueue_script('single-listing', get_template_directory_uri() . '/assets/js/single-listing.js', ['jquery'], '1.0.0', true);
        
        // Localize script for AJAX operations
        wp_localize_script('single-listing', 'hph_listing', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_listing_nonce'),
            'listing_id' => get_the_ID(),
            'mapbox_key' => $mapbox_key
        ]);
        
        // Localize script for contact form (fixes contact-form.js errors)
        wp_localize_script('single-listing', 'hphContact', [
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

