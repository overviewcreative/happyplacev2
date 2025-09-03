<?php
/**
 * Theme Settings Helper Functions
 * 
 * Easy access functions for theme settings and brand constants
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get theme setting with fallback
 * 
 * @param string $setting_name The setting name
 * @param mixed $default Default value if setting doesn't exist
 * @return mixed The setting value
 */
function hph_get_setting($setting_name, $default = '') {
    return get_option($setting_name, $default);
}

/**
 * Get agency information
 */
function hph_get_agency_name() {
    return hph_get_setting('hph_agency_name', get_bloginfo('name'));
}

function hph_get_agency_tagline() {
    return hph_get_setting('hph_agency_tagline', get_bloginfo('description'));
}

function hph_get_agency_phone() {
    return hph_get_setting('hph_agency_phone', '(555) 123-4567');
}

function hph_get_agency_email() {
    return hph_get_setting('hph_agency_email', get_option('admin_email'));
}

function hph_get_agency_address() {
    return hph_get_setting('hph_agency_address', '');
}

function hph_get_agency_license() {
    return hph_get_setting('hph_agency_license', '');
}

function hph_get_agency_hours() {
    return hph_get_setting('hph_agency_hours', 'Mon-Fri 9AM-6PM, Sat 10AM-4PM');
}

/**
 * Get brand assets
 */
function hph_get_brand_logo($size = 'full') {
    $logo_id = hph_get_setting('hph_brand_logo');
    if ($logo_id) {
        return wp_get_attachment_image_url($logo_id, $size);
    }
    return false;
}

function hph_get_brand_logo_white($size = 'full') {
    $logo_id = hph_get_setting('hph_brand_logo_white');
    if ($logo_id) {
        return wp_get_attachment_image_url($logo_id, $size);
    }
    return false;
}

function hph_get_brand_favicon() {
    $favicon_id = hph_get_setting('hph_brand_favicon');
    if ($favicon_id) {
        return wp_get_attachment_image_url($favicon_id, 'full');
    }
    return false;
}

function hph_get_brand_colors() {
    return hph_get_setting('hph_brand_colors', array(
        'primary' => '#0073aa',
        'secondary' => '#005177',
        'accent' => '#00a0d2',
        'success' => '#46b450',
        'warning' => '#ffb900',
        'error' => '#dc3232'
    ));
}

function hph_get_brand_color($color_name, $default = '#0073aa') {
    $colors = hph_get_brand_colors();
    return isset($colors[$color_name]) ? $colors[$color_name] : $default;
}

/**
 * Get social media links
 */
function hph_get_social_links() {
    return hph_get_setting('hph_social_links', array());
}

function hph_get_social_link($platform, $default = '') {
    $links = hph_get_social_links();
    return isset($links[$platform]) ? $links[$platform] : $default;
}

/**
 * Get API keys (with security considerations)
 */
function hph_get_google_maps_api_key() {
    return hph_get_setting('hph_google_maps_api_key', '');
}

function hph_get_google_analytics_id() {
    return hph_get_setting('hph_google_analytics_id', '');
}

function hph_get_facebook_pixel_id() {
    return hph_get_setting('hph_facebook_pixel_id', '');
}

function hph_get_recaptcha_site_key() {
    return hph_get_setting('hph_recaptcha_site_key', '');
}

// Note: Secret keys should only be accessed server-side
function hph_get_recaptcha_secret_key() {
    return hph_get_setting('hph_recaptcha_secret_key', '');
}

function hph_get_mls_api_key() {
    return hph_get_setting('hph_mls_api_key', '');
}

function hph_get_mls_api_secret() {
    return hph_get_setting('hph_mls_api_secret', '');
}

function hph_get_mailchimp_api_key() {
    return hph_get_setting('hph_mailchimp_api_key', '');
}

function hph_get_mailchimp_list_id() {
    return hph_get_setting('hph_mailchimp_list_id', '');
}

/**
 * Get theme feature settings
 */
function hph_is_sticky_header_enabled() {
    return hph_get_setting('hph_enable_sticky_header', true);
}

function hph_is_dark_mode_enabled() {
    return hph_get_setting('hph_enable_dark_mode', false);
}

function hph_is_lazy_loading_enabled() {
    return hph_get_setting('hph_enable_lazy_loading', true);
}

function hph_is_breadcrumbs_enabled() {
    return hph_get_setting('hph_enable_breadcrumbs', true);
}

function hph_is_property_favorites_enabled() {
    return hph_get_setting('hph_enable_property_favorites', true);
}

function hph_is_advanced_search_enabled() {
    return hph_get_setting('hph_enable_advanced_search', true);
}

function hph_is_virtual_tours_enabled() {
    return hph_get_setting('hph_enable_virtual_tours', true);
}

function hph_is_mortgage_calculator_enabled() {
    return hph_get_setting('hph_enable_mortgage_calculator', true);
}

/**
 * Get performance settings
 */
function hph_is_cache_listings_enabled() {
    return hph_get_setting('hph_cache_listings', true);
}

function hph_get_cache_duration() {
    return hph_get_setting('hph_cache_duration', 3600); // 1 hour default
}

function hph_is_optimize_images_enabled() {
    return hph_get_setting('hph_optimize_images', true);
}

function hph_is_minify_assets_enabled() {
    return hph_get_setting('hph_minify_assets', false);
}

function hph_is_preload_critical_css_enabled() {
    return hph_get_setting('hph_preload_critical_css', true);
}

/**
 * Output brand colors as CSS custom properties
 */
function hph_output_brand_colors_css() {
    $colors = hph_get_brand_colors();
    
    echo "<style id='hph-brand-colors'>\n";
    echo ":root {\n";
    
    foreach ($colors as $name => $color) {
        echo "    --hph-{$name}: {$color};\n";
    }
    
    echo "}\n";
    echo "</style>\n";
}

/**
 * Get complete agency contact information as array
 */
function hph_get_agency_contact_info() {
    return array(
        'name' => hph_get_agency_name(),
        'tagline' => hph_get_agency_tagline(),
        'phone' => hph_get_agency_phone(),
        'email' => hph_get_agency_email(),
        'address' => hph_get_agency_address(),
        'license' => hph_get_agency_license(),
        'hours' => hph_get_agency_hours()
    );
}

/**
 * Get complete brand assets as array
 */
function hph_get_brand_assets() {
    return array(
        'logo' => hph_get_brand_logo(),
        'logo_white' => hph_get_brand_logo_white(),
        'favicon' => hph_get_brand_favicon(),
        'colors' => hph_get_brand_colors()
    );
}

/**
 * Check if API is configured
 */
function hph_is_google_maps_configured() {
    return !empty(hph_get_google_maps_api_key());
}

function hph_is_analytics_configured() {
    return !empty(hph_get_google_analytics_id());
}

function hph_is_recaptcha_configured() {
    return !empty(hph_get_recaptcha_site_key()) && !empty(hph_get_recaptcha_secret_key());
}

function hph_is_mls_configured() {
    return !empty(hph_get_mls_api_key()) && !empty(hph_get_mls_api_secret());
}

function hph_is_mailchimp_configured() {
    return !empty(hph_get_mailchimp_api_key()) && !empty(hph_get_mailchimp_list_id());
}

/**
 * Generate structured data for organization
 */
function hph_get_organization_schema() {
    $contact = hph_get_agency_contact_info();
    $social_links = array_filter(hph_get_social_links());
    
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'RealEstateAgent',
        'name' => $contact['name'],
        'description' => $contact['tagline'],
        'telephone' => $contact['phone'],
        'email' => $contact['email'],
        'url' => home_url(),
    );
    
    if (!empty($contact['address'])) {
        $schema['address'] = array(
            '@type' => 'PostalAddress',
            'streetAddress' => $contact['address']
        );
    }
    
    if (!empty($social_links)) {
        $schema['sameAs'] = array_values($social_links);
    }
    
    if (hph_get_brand_logo()) {
        $schema['logo'] = hph_get_brand_logo();
    }
    
    return $schema;
}

/**
 * Output organization structured data
 */
function hph_output_organization_schema() {
    $schema = hph_get_organization_schema();
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
}

/**
 * Add brand colors to head
 */
add_action('wp_head', 'hph_output_brand_colors_css', 5);

/**
 * Add organization schema to head
 */
add_action('wp_head', 'hph_output_organization_schema', 10);

/**
 * Set favicon from settings
 */
function hph_set_custom_favicon() {
    $favicon = hph_get_brand_favicon();
    if ($favicon) {
        echo '<link rel="icon" type="image/x-icon" href="' . esc_url($favicon) . '">';
        echo '<link rel="shortcut icon" type="image/x-icon" href="' . esc_url($favicon) . '">';
    }
}

add_action('wp_head', 'hph_set_custom_favicon', 5);

/**
 * Add Google Analytics tracking code
 */
function hph_add_google_analytics() {
    $ga_id = hph_get_google_analytics_id();
    if (!empty($ga_id)) {
        ?>
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga_id); ?>"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo esc_js($ga_id); ?>');
        </script>
        <?php
    }
}

add_action('wp_head', 'hph_add_google_analytics', 20);

/**
 * Add Facebook Pixel tracking code
 */
function hph_add_facebook_pixel() {
    $pixel_id = hph_get_facebook_pixel_id();
    if (!empty($pixel_id)) {
        ?>
        <!-- Facebook Pixel -->
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?php echo esc_js($pixel_id); ?>');
        fbq('track', 'PageView');
        </script>
        <noscript>
        <img height="1" width="1" style="display:none" 
        src="https://www.facebook.com/tr?id=<?php echo esc_attr($pixel_id); ?>&ev=PageView&noscript=1"/>
        </noscript>
        <?php
    }
}

add_action('wp_head', 'hph_add_facebook_pixel', 25);
