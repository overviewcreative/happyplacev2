<?php
/**
 * Happy Place Theme Admin Settings
 * 
 * Comprehensive theme settings page for brand constants, API keys, and other configurations
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class HPH_Admin_Settings {
    
    /**
     * Settings page slug
     */
    const PAGE_SLUG = 'hph-theme-settings';
    
    /**
     * Option group name
     */
    const OPTION_GROUP = 'hph_theme_settings';
    
    /**
     * Initialize the admin settings
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
    }
    
    /**
     * Add the admin menu page
     */
    public static function add_admin_menu() {
        add_theme_page(
            __('Happy Place Theme Settings', 'happy-place-theme'),
            __('Theme Settings', 'happy-place-theme'),
            'manage_options',
            self::PAGE_SLUG,
            array(__CLASS__, 'render_settings_page')
        );
    }
    
    /**
     * Register all settings
     */
    public static function register_settings() {
        // Brand Constants Section
        add_settings_section(
            'hph_brand_constants',
            __('Brand Constants', 'happy-place-theme'),
            array(__CLASS__, 'render_brand_constants_description'),
            self::PAGE_SLUG
        );
        
        // Agency Information
        register_setting(self::OPTION_GROUP, 'hph_agency_name');
        register_setting(self::OPTION_GROUP, 'hph_agency_tagline');
        register_setting(self::OPTION_GROUP, 'hph_agency_phone');
        register_setting(self::OPTION_GROUP, 'hph_agency_email');
        register_setting(self::OPTION_GROUP, 'hph_agency_address');
        register_setting(self::OPTION_GROUP, 'hph_agency_license');
        register_setting(self::OPTION_GROUP, 'hph_agency_hours');
        
        // Brand Assets
        register_setting(self::OPTION_GROUP, 'hph_brand_logo');
        register_setting(self::OPTION_GROUP, 'hph_brand_logo_white');
        register_setting(self::OPTION_GROUP, 'hph_brand_favicon');
        register_setting(self::OPTION_GROUP, 'hph_brand_colors', array(
            'sanitize_callback' => array(__CLASS__, 'sanitize_brand_colors')
        ));
        
        // Social Media
        register_setting(self::OPTION_GROUP, 'hph_social_links', array(
            'sanitize_callback' => array(__CLASS__, 'sanitize_social_links')
        ));
        
        // API Keys Section
        add_settings_section(
            'hph_api_keys',
            __('API Keys & Integrations', 'happy-place-theme'),
            array(__CLASS__, 'render_api_keys_description'),
            self::PAGE_SLUG
        );
        
        register_setting(self::OPTION_GROUP, 'hph_google_maps_api_key');
        register_setting(self::OPTION_GROUP, 'hph_google_analytics_id');
        register_setting(self::OPTION_GROUP, 'hph_facebook_pixel_id');
        register_setting(self::OPTION_GROUP, 'hph_mls_api_key');
        register_setting(self::OPTION_GROUP, 'hph_mls_api_secret');
        register_setting(self::OPTION_GROUP, 'hph_mailchimp_api_key');
        register_setting(self::OPTION_GROUP, 'hph_mailchimp_list_id');
        register_setting(self::OPTION_GROUP, 'hph_recaptcha_site_key');
        register_setting(self::OPTION_GROUP, 'hph_recaptcha_secret_key');
        
        // Theme Features Section
        add_settings_section(
            'hph_theme_features',
            __('Theme Features', 'happy-place-theme'),
            array(__CLASS__, 'render_theme_features_description'),
            self::PAGE_SLUG
        );
        
        register_setting(self::OPTION_GROUP, 'hph_enable_sticky_header');
        register_setting(self::OPTION_GROUP, 'hph_enable_dark_mode');
        register_setting(self::OPTION_GROUP, 'hph_enable_lazy_loading');
        register_setting(self::OPTION_GROUP, 'hph_enable_breadcrumbs');
        register_setting(self::OPTION_GROUP, 'hph_enable_property_favorites');
        register_setting(self::OPTION_GROUP, 'hph_enable_advanced_search');
        register_setting(self::OPTION_GROUP, 'hph_enable_virtual_tours');
        register_setting(self::OPTION_GROUP, 'hph_enable_mortgage_calculator');
        
        // Performance Section
        add_settings_section(
            'hph_performance',
            __('Performance & Optimization', 'happy-place-theme'),
            array(__CLASS__, 'render_performance_description'),
            self::PAGE_SLUG
        );
        
        register_setting(self::OPTION_GROUP, 'hph_cache_listings');
        register_setting(self::OPTION_GROUP, 'hph_cache_duration');
        register_setting(self::OPTION_GROUP, 'hph_optimize_images');
        register_setting(self::OPTION_GROUP, 'hph_minify_assets');
        register_setting(self::OPTION_GROUP, 'hph_preload_critical_css');
        
        // Add settings fields
        self::add_brand_fields();
        self::add_api_fields();
        self::add_feature_fields();
        self::add_performance_fields();
    }
    
    /**
     * Add brand constant fields
     */
    private static function add_brand_fields() {
        // Agency Information
        add_settings_field(
            'hph_agency_name',
            __('Agency Name', 'happy-place-theme'),
            array(__CLASS__, 'render_text_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_agency_name',
                'description' => __('Your real estate agency name', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_agency_tagline',
            __('Agency Tagline', 'happy-place-theme'),
            array(__CLASS__, 'render_text_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_agency_tagline',
                'description' => __('Your agency tagline or slogan', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_agency_phone',
            __('Phone Number', 'happy-place-theme'),
            array(__CLASS__, 'render_text_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_agency_phone',
                'description' => __('Main agency phone number', 'happy-place-theme'),
                'placeholder' => '(555) 123-4567'
            )
        );
        
        add_settings_field(
            'hph_agency_email',
            __('Email Address', 'happy-place-theme'),
            array(__CLASS__, 'render_email_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_agency_email',
                'description' => __('Main agency email address', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_agency_address',
            __('Agency Address', 'happy-place-theme'),
            array(__CLASS__, 'render_textarea_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_agency_address',
                'description' => __('Complete agency address', 'happy-place-theme'),
                'rows' => 3
            )
        );
        
        add_settings_field(
            'hph_agency_license',
            __('License Number', 'happy-place-theme'),
            array(__CLASS__, 'render_text_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_agency_license',
                'description' => __('Real estate license number', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_agency_hours',
            __('Business Hours', 'happy-place-theme'),
            array(__CLASS__, 'render_text_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_agency_hours',
                'description' => __('Business operating hours', 'happy-place-theme'),
                'placeholder' => 'Mon-Fri 9AM-6PM, Sat 10AM-4PM'
            )
        );
        
        // Brand Assets
        add_settings_field(
            'hph_brand_logo',
            __('Primary Logo', 'happy-place-theme'),
            array(__CLASS__, 'render_media_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_brand_logo',
                'description' => __('Primary logo for light backgrounds', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_brand_logo_white',
            __('White Logo', 'happy-place-theme'),
            array(__CLASS__, 'render_media_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_brand_logo_white',
                'description' => __('White logo for dark backgrounds', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_brand_favicon',
            __('Favicon', 'happy-place-theme'),
            array(__CLASS__, 'render_media_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_brand_favicon',
                'description' => __('Site favicon (32x32 or 16x16)', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_brand_colors',
            __('Brand Colors', 'happy-place-theme'),
            array(__CLASS__, 'render_color_palette_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_brand_colors',
                'description' => __('Your brand color palette', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_social_links',
            __('Social Media Links', 'happy-place-theme'),
            array(__CLASS__, 'render_social_links_field'),
            self::PAGE_SLUG,
            'hph_brand_constants',
            array(
                'field' => 'hph_social_links',
                'description' => __('Your social media profiles', 'happy-place-theme')
            )
        );
    }
    
    /**
     * Add API key fields
     */
    private static function add_api_fields() {
        add_settings_field(
            'hph_google_maps_api_key',
            __('Google Maps API Key', 'happy-place-theme'),
            array(__CLASS__, 'render_password_field'),
            self::PAGE_SLUG,
            'hph_api_keys',
            array(
                'field' => 'hph_google_maps_api_key',
                'description' => __('Required for property maps and location features', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_google_analytics_id',
            __('Google Analytics ID', 'happy-place-theme'),
            array(__CLASS__, 'render_text_field'),
            self::PAGE_SLUG,
            'hph_api_keys',
            array(
                'field' => 'hph_google_analytics_id',
                'description' => __('Google Analytics tracking ID (GA-XXXXXXXXX)', 'happy-place-theme'),
                'placeholder' => 'GA-XXXXXXXXX'
            )
        );
        
        add_settings_field(
            'hph_facebook_pixel_id',
            __('Facebook Pixel ID', 'happy-place-theme'),
            array(__CLASS__, 'render_text_field'),
            self::PAGE_SLUG,
            'hph_api_keys',
            array(
                'field' => 'hph_facebook_pixel_id',
                'description' => __('Facebook Pixel ID for advertising tracking', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_mls_api_key',
            __('MLS API Key', 'happy-place-theme'),
            array(__CLASS__, 'render_password_field'),
            self::PAGE_SLUG,
            'hph_api_keys',
            array(
                'field' => 'hph_mls_api_key',
                'description' => __('MLS data integration API key', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_mls_api_secret',
            __('MLS API Secret', 'happy-place-theme'),
            array(__CLASS__, 'render_password_field'),
            self::PAGE_SLUG,
            'hph_api_keys',
            array(
                'field' => 'hph_mls_api_secret',
                'description' => __('MLS data integration API secret', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_mailchimp_api_key',
            __('Mailchimp API Key', 'happy-place-theme'),
            array(__CLASS__, 'render_password_field'),
            self::PAGE_SLUG,
            'hph_api_keys',
            array(
                'field' => 'hph_mailchimp_api_key',
                'description' => __('Mailchimp API key for newsletter integration', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_mailchimp_list_id',
            __('Mailchimp List ID', 'happy-place-theme'),
            array(__CLASS__, 'render_text_field'),
            self::PAGE_SLUG,
            'hph_api_keys',
            array(
                'field' => 'hph_mailchimp_list_id',
                'description' => __('Mailchimp audience/list ID', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_recaptcha_site_key',
            __('reCAPTCHA Site Key', 'happy-place-theme'),
            array(__CLASS__, 'render_text_field'),
            self::PAGE_SLUG,
            'hph_api_keys',
            array(
                'field' => 'hph_recaptcha_site_key',
                'description' => __('Google reCAPTCHA site key for form protection', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_recaptcha_secret_key',
            __('reCAPTCHA Secret Key', 'happy-place-theme'),
            array(__CLASS__, 'render_password_field'),
            self::PAGE_SLUG,
            'hph_api_keys',
            array(
                'field' => 'hph_recaptcha_secret_key',
                'description' => __('Google reCAPTCHA secret key', 'happy-place-theme')
            )
        );
    }
    
    /**
     * Add theme feature fields
     */
    private static function add_feature_fields() {
        add_settings_field(
            'hph_enable_sticky_header',
            __('Sticky Header', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_theme_features',
            array(
                'field' => 'hph_enable_sticky_header',
                'description' => __('Enable sticky header with scroll behavior', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_enable_dark_mode',
            __('Dark Mode', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_theme_features',
            array(
                'field' => 'hph_enable_dark_mode',
                'description' => __('Enable dark mode toggle', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_enable_lazy_loading',
            __('Lazy Loading', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_theme_features',
            array(
                'field' => 'hph_enable_lazy_loading',
                'description' => __('Enable lazy loading for images', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_enable_breadcrumbs',
            __('Breadcrumbs', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_theme_features',
            array(
                'field' => 'hph_enable_breadcrumbs',
                'description' => __('Enable breadcrumb navigation', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_enable_property_favorites',
            __('Property Favorites', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_theme_features',
            array(
                'field' => 'hph_enable_property_favorites',
                'description' => __('Allow users to save favorite properties', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_enable_advanced_search',
            __('Advanced Search', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_theme_features',
            array(
                'field' => 'hph_enable_advanced_search',
                'description' => __('Enable advanced property search filters', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_enable_virtual_tours',
            __('Virtual Tours', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_theme_features',
            array(
                'field' => 'hph_enable_virtual_tours',
                'description' => __('Enable virtual tour integration', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_enable_mortgage_calculator',
            __('Mortgage Calculator', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_theme_features',
            array(
                'field' => 'hph_enable_mortgage_calculator',
                'description' => __('Enable mortgage payment calculator', 'happy-place-theme')
            )
        );
    }
    
    /**
     * Add performance fields
     */
    private static function add_performance_fields() {
        add_settings_field(
            'hph_cache_listings',
            __('Cache Listings', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_performance',
            array(
                'field' => 'hph_cache_listings',
                'description' => __('Enable caching for property listings', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_cache_duration',
            __('Cache Duration', 'happy-place-theme'),
            array(__CLASS__, 'render_select_field'),
            self::PAGE_SLUG,
            'hph_performance',
            array(
                'field' => 'hph_cache_duration',
                'description' => __('How long to cache listing data', 'happy-place-theme'),
                'options' => array(
                    '300' => __('5 minutes', 'happy-place-theme'),
                    '900' => __('15 minutes', 'happy-place-theme'),
                    '1800' => __('30 minutes', 'happy-place-theme'),
                    '3600' => __('1 hour', 'happy-place-theme'),
                    '7200' => __('2 hours', 'happy-place-theme'),
                    '21600' => __('6 hours', 'happy-place-theme'),
                    '43200' => __('12 hours', 'happy-place-theme'),
                    '86400' => __('24 hours', 'happy-place-theme')
                )
            )
        );
        
        add_settings_field(
            'hph_optimize_images',
            __('Optimize Images', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_performance',
            array(
                'field' => 'hph_optimize_images',
                'description' => __('Automatically optimize uploaded images', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_minify_assets',
            __('Minify Assets', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_performance',
            array(
                'field' => 'hph_minify_assets',
                'description' => __('Minify CSS and JavaScript files', 'happy-place-theme')
            )
        );
        
        add_settings_field(
            'hph_preload_critical_css',
            __('Preload Critical CSS', 'happy-place-theme'),
            array(__CLASS__, 'render_checkbox_field'),
            self::PAGE_SLUG,
            'hph_performance',
            array(
                'field' => 'hph_preload_critical_css',
                'description' => __('Preload critical CSS for faster loading', 'happy-place-theme')
            )
        );
    }
    
    /**
     * Render the main settings page
     */
    public static function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings message
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                self::OPTION_GROUP,
                'settings-updated',
                __('Settings saved successfully!', 'happy-place-theme'),
                'updated'
            );
        }
        
        settings_errors(self::OPTION_GROUP);
        ?>
        <div class="wrap hph-admin-settings">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="hph-admin-header">
                <div class="hph-admin-logo">
                    <h2><?php esc_html_e('Happy Place Theme Settings', 'happy-place-theme'); ?></h2>
                    <p><?php esc_html_e('Configure your brand constants, API keys, and theme features.', 'happy-place-theme'); ?></p>
                </div>
                <div class="hph-admin-version">
                    <span class="version-badge">v<?php echo HPH_VERSION; ?></span>
                </div>
            </div>
            
            <div class="hph-admin-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#brand" class="nav-tab nav-tab-active" data-tab="brand">
                        <i class="dashicons dashicons-art"></i>
                        <?php esc_html_e('Brand Constants', 'happy-place-theme'); ?>
                    </a>
                    <a href="#api" class="nav-tab" data-tab="api">
                        <i class="dashicons dashicons-admin-network"></i>
                        <?php esc_html_e('API Keys', 'happy-place-theme'); ?>
                    </a>
                    <a href="#features" class="nav-tab" data-tab="features">
                        <i class="dashicons dashicons-admin-generic"></i>
                        <?php esc_html_e('Features', 'happy-place-theme'); ?>
                    </a>
                    <a href="#performance" class="nav-tab" data-tab="performance">
                        <i class="dashicons dashicons-performance"></i>
                        <?php esc_html_e('Performance', 'happy-place-theme'); ?>
                    </a>
                </nav>
            </div>
            
            <form method="post" action="options.php" enctype="multipart/form-data">
                <?php settings_fields(self::OPTION_GROUP); ?>
                
                <div class="hph-tab-content" id="brand-tab">
                    <div class="hph-settings-section">
                        <?php do_settings_sections(self::PAGE_SLUG); ?>
                    </div>
                </div>
                
                <div class="hph-tab-content" id="api-tab" style="display: none;">
                    <div class="hph-settings-section">
                        <h2><?php esc_html_e('API Keys & Integrations', 'happy-place-theme'); ?></h2>
                        <table class="form-table" role="presentation">
                            <?php self::render_api_section_fields(); ?>
                        </table>
                    </div>
                </div>
                
                <div class="hph-tab-content" id="features-tab" style="display: none;">
                    <div class="hph-settings-section">
                        <h2><?php esc_html_e('Theme Features', 'happy-place-theme'); ?></h2>
                        <table class="form-table" role="presentation">
                            <?php self::render_features_section_fields(); ?>
                        </table>
                    </div>
                </div>
                
                <div class="hph-tab-content" id="performance-tab" style="display: none;">
                    <div class="hph-settings-section">
                        <h2><?php esc_html_e('Performance & Optimization', 'happy-place-theme'); ?></h2>
                        <table class="form-table" role="presentation">
                            <?php self::render_performance_section_fields(); ?>
                        </table>
                    </div>
                </div>
                
                <div class="hph-settings-footer">
                    <?php submit_button(__('Save All Settings', 'happy-place-theme'), 'primary', 'submit', false); ?>
                    <button type="button" class="button" id="export-settings">
                        <?php esc_html_e('Export Settings', 'happy-place-theme'); ?>
                    </button>
                    <button type="button" class="button" id="import-settings">
                        <?php esc_html_e('Import Settings', 'happy-place-theme'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render section descriptions
     */
    public static function render_brand_constants_description() {
        echo '<p>' . esc_html__('Configure your agency branding, contact information, and visual assets.', 'happy-place-theme') . '</p>';
    }
    
    public static function render_api_keys_description() {
        echo '<p>' . esc_html__('Enter your API keys and configure third-party integrations.', 'happy-place-theme') . '</p>';
    }
    
    public static function render_theme_features_description() {
        echo '<p>' . esc_html__('Enable or disable specific theme features and functionality.', 'happy-place-theme') . '</p>';
    }
    
    public static function render_performance_description() {
        echo '<p>' . esc_html__('Configure performance optimization settings for better site speed.', 'happy-place-theme') . '</p>';
    }
    
    /**
     * Field rendering methods
     */
    public static function render_text_field($args) {
        $field = $args['field'];
        $value = get_option($field, '');
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        ?>
        <input type="text" 
               id="<?php echo esc_attr($field); ?>" 
               name="<?php echo esc_attr($field); ?>" 
               value="<?php echo esc_attr($value); ?>"
               placeholder="<?php echo esc_attr($placeholder); ?>"
               class="regular-text" />
        <?php if ($description) : ?>
            <p class="description"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
        <?php
    }
    
    public static function render_email_field($args) {
        $field = $args['field'];
        $value = get_option($field, '');
        $description = isset($args['description']) ? $args['description'] : '';
        ?>
        <input type="email" 
               id="<?php echo esc_attr($field); ?>" 
               name="<?php echo esc_attr($field); ?>" 
               value="<?php echo esc_attr($value); ?>"
               class="regular-text" />
        <?php if ($description) : ?>
            <p class="description"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
        <?php
    }
    
    public static function render_password_field($args) {
        $field = $args['field'];
        $value = get_option($field, '');
        $description = isset($args['description']) ? $args['description'] : '';
        ?>
        <input type="password" 
               id="<?php echo esc_attr($field); ?>" 
               name="<?php echo esc_attr($field); ?>" 
               value="<?php echo esc_attr($value); ?>"
               class="regular-text" />
        <button type="button" class="button toggle-password" data-target="<?php echo esc_attr($field); ?>">
            <i class="dashicons dashicons-visibility"></i>
        </button>
        <?php if ($description) : ?>
            <p class="description"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
        <?php
    }
    
    public static function render_textarea_field($args) {
        $field = $args['field'];
        $value = get_option($field, '');
        $rows = isset($args['rows']) ? $args['rows'] : 4;
        $description = isset($args['description']) ? $args['description'] : '';
        ?>
        <textarea id="<?php echo esc_attr($field); ?>" 
                  name="<?php echo esc_attr($field); ?>" 
                  rows="<?php echo esc_attr($rows); ?>"
                  class="large-text"><?php echo esc_textarea($value); ?></textarea>
        <?php if ($description) : ?>
            <p class="description"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
        <?php
    }
    
    public static function render_checkbox_field($args) {
        $field = $args['field'];
        $value = get_option($field, false);
        $description = isset($args['description']) ? $args['description'] : '';
        ?>
        <label for="<?php echo esc_attr($field); ?>">
            <input type="checkbox" 
                   id="<?php echo esc_attr($field); ?>" 
                   name="<?php echo esc_attr($field); ?>" 
                   value="1" 
                   <?php checked($value, 1); ?> />
            <?php echo esc_html($description); ?>
        </label>
        <?php
    }
    
    public static function render_select_field($args) {
        $field = $args['field'];
        $value = get_option($field, '');
        $options = isset($args['options']) ? $args['options'] : array();
        $description = isset($args['description']) ? $args['description'] : '';
        ?>
        <select id="<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($field); ?>">
            <?php foreach ($options as $option_value => $option_label) : ?>
                <option value="<?php echo esc_attr($option_value); ?>" <?php selected($value, $option_value); ?>>
                    <?php echo esc_html($option_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($description) : ?>
            <p class="description"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
        <?php
    }
    
    public static function render_media_field($args) {
        $field = $args['field'];
        $value = get_option($field, '');
        $description = isset($args['description']) ? $args['description'] : '';
        ?>
        <div class="media-upload-wrapper">
            <input type="hidden" 
                   id="<?php echo esc_attr($field); ?>" 
                   name="<?php echo esc_attr($field); ?>" 
                   value="<?php echo esc_attr($value); ?>" />
            
            <div class="media-preview" id="<?php echo esc_attr($field); ?>-preview">
                <?php if ($value) : ?>
                    <?php $image_url = wp_get_attachment_image_url($value, 'medium'); ?>
                    <?php if ($image_url) : ?>
                        <img src="<?php echo esc_url($image_url); ?>" alt="" style="max-width: 150px; height: auto;" />
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <p>
                <button type="button" class="button upload-media" data-field="<?php echo esc_attr($field); ?>">
                    <?php esc_html_e('Choose Image', 'happy-place-theme'); ?>
                </button>
                <button type="button" class="button remove-media" data-field="<?php echo esc_attr($field); ?>" <?php echo !$value ? 'style="display:none;"' : ''; ?>>
                    <?php esc_html_e('Remove', 'happy-place-theme'); ?>
                </button>
            </p>
            
            <?php if ($description) : ?>
                <p class="description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public static function render_color_palette_field($args) {
        $field = $args['field'];
        $colors = get_option($field, array(
            'primary' => '#0073aa',
            'secondary' => '#005177',
            'accent' => '#00a0d2',
            'success' => '#46b450',
            'warning' => '#ffb900',
            'error' => '#dc3232'
        ));
        $description = isset($args['description']) ? $args['description'] : '';
        ?>
        <div class="color-palette-wrapper">
            <?php
            $color_labels = array(
                'primary' => __('Primary', 'happy-place-theme'),
                'secondary' => __('Secondary', 'happy-place-theme'),
                'accent' => __('Accent', 'happy-place-theme'),
                'success' => __('Success', 'happy-place-theme'),
                'warning' => __('Warning', 'happy-place-theme'),
                'error' => __('Error', 'happy-place-theme')
            );
            
            foreach ($color_labels as $color_key => $label) :
                $color_value = isset($colors[$color_key]) ? $colors[$color_key] : '#0073aa';
            ?>
                <div class="color-picker-item">
                    <label for="<?php echo esc_attr($field . '_' . $color_key); ?>">
                        <?php echo esc_html($label); ?>
                    </label>
                    <input type="text" 
                           id="<?php echo esc_attr($field . '_' . $color_key); ?>" 
                           name="<?php echo esc_attr($field . '[' . $color_key . ']'); ?>" 
                           value="<?php echo esc_attr($color_value); ?>"
                           class="color-picker" 
                           data-default-color="<?php echo esc_attr($color_value); ?>" />
                </div>
            <?php endforeach; ?>
            
            <?php if ($description) : ?>
                <p class="description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public static function render_social_links_field($args) {
        $field = $args['field'];
        $links = get_option($field, array());
        $description = isset($args['description']) ? $args['description'] : '';
        
        $platforms = array(
            'facebook' => array('label' => 'Facebook', 'icon' => 'facebook-alt'),
            'instagram' => array('label' => 'Instagram', 'icon' => 'instagram'),
            'twitter' => array('label' => 'Twitter', 'icon' => 'twitter'),
            'linkedin' => array('label' => 'LinkedIn', 'icon' => 'linkedin'),
            'youtube' => array('label' => 'YouTube', 'icon' => 'youtube'),
            'pinterest' => array('label' => 'Pinterest', 'icon' => 'pinterest')
        );
        ?>
        <div class="social-links-wrapper">
            <?php foreach ($platforms as $platform => $data) : ?>
                <div class="social-link-item">
                    <label for="<?php echo esc_attr($field . '_' . $platform); ?>">
                        <i class="dashicons dashicons-<?php echo esc_attr($data['icon']); ?>"></i>
                        <?php echo esc_html($data['label']); ?>
                    </label>
                    <input type="url" 
                           id="<?php echo esc_attr($field . '_' . $platform); ?>" 
                           name="<?php echo esc_attr($field . '[' . $platform . ']'); ?>" 
                           value="<?php echo esc_attr(isset($links[$platform]) ? $links[$platform] : ''); ?>"
                           placeholder="https://<?php echo esc_attr($platform); ?>.com/yourpage"
                           class="regular-text" />
                </div>
            <?php endforeach; ?>
            
            <?php if ($description) : ?>
                <p class="description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render individual section fields (for tabs)
     */
    public static function render_api_section_fields() {
        // This would render only API-related fields
        // Implementation would be similar to do_settings_fields but filtered
    }
    
    public static function render_features_section_fields() {
        // This would render only feature-related fields
    }
    
    public static function render_performance_section_fields() {
        // This would render only performance-related fields
    }
    
    /**
     * Sanitization callbacks
     */
    public static function sanitize_brand_colors($colors) {
        if (!is_array($colors)) {
            return array();
        }
        
        $sanitized = array();
        foreach ($colors as $key => $color) {
            $sanitized[$key] = sanitize_hex_color($color);
        }
        
        return $sanitized;
    }
    
    public static function sanitize_social_links($links) {
        if (!is_array($links)) {
            return array();
        }
        
        $sanitized = array();
        foreach ($links as $platform => $url) {
            if (!empty($url)) {
                $sanitized[$platform] = esc_url_raw($url);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Enqueue admin assets
     */
    public static function enqueue_admin_assets($hook) {
        if ('appearance_page_' . self::PAGE_SLUG !== $hook) {
            return;
        }
        
        // Enqueue WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue media uploader
        wp_enqueue_media();
        
        // Enqueue custom admin styles and scripts
        wp_enqueue_style(
            'hph-admin-settings',
            HPH_THEME_URI . '/assets/css/admin/admin-settings.css',
            array('wp-color-picker'),
            HPH_VERSION
        );
        
        wp_enqueue_script(
            'hph-admin-settings',
            HPH_THEME_URI . '/assets/js/admin/admin-settings.js',
            array('jquery', 'wp-color-picker', 'media-upload'),
            HPH_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('hph-admin-settings', 'hphAdmin', array(
            'nonce' => wp_create_nonce('hph_admin_nonce'),
            'strings' => array(
                'chooseImage' => __('Choose Image', 'happy-place-theme'),
                'useImage' => __('Use Image', 'happy-place-theme'),
                'removeImage' => __('Remove Image', 'happy-place-theme'),
                'exportSuccess' => __('Settings exported successfully!', 'happy-place-theme'),
                'importSuccess' => __('Settings imported successfully!', 'happy-place-theme'),
                'importError' => __('Error importing settings. Please check the file format.', 'happy-place-theme')
            )
        ));
    }
    
    /**
     * Get option with fallback
     */
    public static function get_option($option_name, $default = '') {
        return get_option($option_name, $default);
    }
    
    /**
     * Export settings
     */
    public static function export_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access', 'happy-place-theme'));
        }
        
        // Get all theme settings
        $settings = array();
        $option_keys = array(
            'hph_agency_name', 'hph_agency_tagline', 'hph_agency_phone', 'hph_agency_email',
            'hph_agency_address', 'hph_agency_license', 'hph_agency_hours',
            'hph_brand_logo', 'hph_brand_logo_white', 'hph_brand_favicon', 'hph_brand_colors',
            'hph_social_links', 'hph_google_maps_api_key', 'hph_google_analytics_id',
            'hph_facebook_pixel_id', 'hph_mls_api_key', 'hph_mls_api_secret',
            'hph_mailchimp_api_key', 'hph_mailchimp_list_id', 'hph_recaptcha_site_key',
            'hph_recaptcha_secret_key'
        );
        
        foreach ($option_keys as $key) {
            $settings[$key] = get_option($key, '');
        }
        
        // Set headers for download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="hph-theme-settings-' . date('Y-m-d') . '.json"');
        
        echo json_encode($settings, JSON_PRETTY_PRINT);
        exit;
    }
}

// Initialize the admin settings
HPH_Admin_Settings::init();
