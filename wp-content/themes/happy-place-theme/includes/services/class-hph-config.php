<?php
/**
 * Configuration Service
 * 
 * Handles all theme configuration and settings
 * 
 * @package HappyPlaceTheme
 */

class HPH_Config {
    
    /**
     * Configuration data
     */
    private $config = array();
    
    /**
     * Initialize configuration
     */
    public function init() {
        $this->set_defaults();
        $this->load_config();
        $this->register_settings();
    }
    
    /**
     * Set default configuration
     */
    private function set_defaults() {
        $this->config = array(
            // Location settings
            'default_location' => array(
                'lat'   => 39.7392,
                'lng'   => -104.9903,
                'city'  => 'Denver',
                'state' => 'CO'
            ),
            
            // Display settings
            'listings_per_page' => 12,
            'enable_map'        => true,
            'enable_virtual_tours' => true,
            
            // Property types
            'property_types' => array(
                'house'        => __('House', 'happy-place-theme'),
                'condo'        => __('Condo', 'happy-place-theme'),
                'townhouse'    => __('Townhouse', 'happy-place-theme'),
                'apartment'    => __('Apartment', 'happy-place-theme'),
                'land'         => __('Land', 'happy-place-theme'),
                'commercial'   => __('Commercial', 'happy-place-theme'),
                'multi_family' => __('Multi-Family', 'happy-place-theme'),
            ),
            
            // Price ranges
            'price_ranges' => array(
                50000, 100000, 150000, 200000, 250000, 300000,
                400000, 500000, 600000, 750000, 1000000,
                1500000, 2000000, 3000000, 5000000
            ),
            
            // Image sizes
            'image_sizes' => array(
                'listing-thumbnail' => array(400, 300, true),
                'listing-medium'    => array(600, 450, true),
                'listing-large'     => array(800, 600, true),
                'listing-hero'      => array(1200, 600, true),
                'agent-avatar'      => array(150, 150, true),
                'agent-profile'     => array(300, 300, true),
                'gallery-large'     => array(1200, 800, true),
                'gallery-thumb'     => array(150, 150, true),
            ),
            
            // API Keys - Use environment variables or wp-config.php constants for security
            'google_maps_key' => defined('HPH_GOOGLE_MAPS_API_KEY') ? HPH_GOOGLE_MAPS_API_KEY : get_option('hph_google_maps_key', ''),
        );
    }
    
    /**
     * Load configuration from file or database
     */
    private function load_config() {
        // Load from config file if exists
        $config_file = HPH_THEME_DIR . '/config/theme-config.php';
        if (file_exists($config_file)) {
            $file_config = include $config_file;
            $this->config = wp_parse_args($file_config, $this->config);
        }
        
        // Override with database settings
        $db_config = get_option('hph_theme_config', array());
        if (!empty($db_config)) {
            $this->config = wp_parse_args($db_config, $this->config);
        }
        
        // Apply filters
        $this->config = apply_filters('hph_theme_config', $this->config);
    }
    
    /**
     * Register settings
     */
    private function register_settings() {
        add_action('admin_init', array($this, 'register_admin_settings'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Register admin settings
     */
    public function register_admin_settings() {
        register_setting('hph_theme_settings', 'hph_theme_config', array(
            'sanitize_callback' => array($this, 'sanitize_config'),
        ));
        
        // Add settings sections
        add_settings_section(
            'hph_general_settings',
            __('General Settings', 'happy-place-theme'),
            array($this, 'general_settings_section_callback'),
            'hph_theme_settings'
        );
        
        add_settings_section(
            'hph_api_settings',
            __('API Settings', 'happy-place-theme'),
            array($this, 'api_settings_section_callback'),
            'hph_theme_settings'
        );
        
        // Add settings fields
        add_settings_field(
            'listings_per_page',
            __('Listings Per Page', 'happy-place-theme'),
            array($this, 'listings_per_page_callback'),
            'hph_theme_settings',
            'hph_general_settings'
        );
        
        add_settings_field(
            'enable_map',
            __('Enable Maps', 'happy-place-theme'),
            array($this, 'enable_map_callback'),
            'hph_theme_settings',
            'hph_general_settings'
        );
        
        add_settings_field(
            'google_maps_key',
            __('Google Maps API Key', 'happy-place-theme'),
            array($this, 'google_maps_key_callback'),
            'hph_theme_settings',
            'hph_api_settings'
        );
    }
    
    /**
     * Sanitize configuration
     */
    public function sanitize_config($input) {
        // Sanitize each config value appropriately
        $sanitized = array();
        
        if (isset($input['listings_per_page'])) {
            $sanitized['listings_per_page'] = absint($input['listings_per_page']);
            // Ensure it's within reasonable bounds
            if ($sanitized['listings_per_page'] < 1) {
                $sanitized['listings_per_page'] = 12;
            }
            if ($sanitized['listings_per_page'] > 50) {
                $sanitized['listings_per_page'] = 50;
            }
        }
        
        if (isset($input['enable_map'])) {
            $sanitized['enable_map'] = (bool) $input['enable_map'];
        }
        
        if (isset($input['google_maps_key'])) {
            $sanitized['google_maps_key'] = sanitize_text_field($input['google_maps_key']);
        }
        
        if (isset($input['enable_virtual_tours'])) {
            $sanitized['enable_virtual_tours'] = (bool) $input['enable_virtual_tours'];
        }
        
        return $sanitized;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_theme_page(
            __('Happy Place Theme Settings', 'happy-place-theme'),
            __('Theme Settings', 'happy-place-theme'),
            'manage_options',
            'hph-theme-settings',
            array($this, 'admin_page_callback')
        );
    }
    
    /**
     * Admin page callback
     */
    public function admin_page_callback() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('hph_theme_settings');
                do_settings_sections('hph_theme_settings');
                submit_button(__('Save Settings', 'happy-place-theme'));
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * General settings section callback
     */
    public function general_settings_section_callback() {
        echo '<p>' . __('Configure general theme settings.', 'happy-place-theme') . '</p>';
    }
    
    /**
     * API settings section callback
     */
    public function api_settings_section_callback() {
        echo '<p>' . __('Configure API keys and external service settings.', 'happy-place-theme') . '</p>';
    }
    
    /**
     * Listings per page callback
     */
    public function listings_per_page_callback() {
        $config = get_option('hph_theme_config', array());
        $value = isset($config['listings_per_page']) ? $config['listings_per_page'] : $this->get('listings_per_page');
        ?>
        <input 
            type="number" 
            name="hph_theme_config[listings_per_page]" 
            value="<?php echo esc_attr($value); ?>"
            min="1"
            max="50"
        />
        <p class="description"><?php _e('Number of listings to display per page.', 'happy-place-theme'); ?></p>
        <?php
    }
    
    /**
     * Enable map callback
     */
    public function enable_map_callback() {
        $config = get_option('hph_theme_config', array());
        $value = isset($config['enable_map']) ? $config['enable_map'] : $this->get('enable_map');
        ?>
        <input 
            type="checkbox" 
            name="hph_theme_config[enable_map]" 
            value="1"
            <?php checked($value, true); ?>
        />
        <label for="hph_theme_config[enable_map]"><?php _e('Enable map functionality throughout the theme', 'happy-place-theme'); ?></label>
        <?php
    }
    
    /**
     * Google Maps API key callback
     */
    public function google_maps_key_callback() {
        $config = get_option('hph_theme_config', array());
        $value = isset($config['google_maps_key']) ? $config['google_maps_key'] : '';
        
        // Show if key is defined in wp-config.php
        $is_defined = defined('HPH_GOOGLE_MAPS_API_KEY');
        ?>
        <?php if ($is_defined): ?>
            <p class="notice notice-info inline">
                <strong><?php _e('API Key is defined in wp-config.php', 'happy-place-theme'); ?></strong><br>
                <?php _e('The key defined in wp-config.php will take precedence over this setting.', 'happy-place-theme'); ?>
            </p>
        <?php endif; ?>
        
        <input 
            type="text" 
            name="hph_theme_config[google_maps_key]" 
            value="<?php echo esc_attr($value); ?>"
            class="regular-text"
            <?php echo $is_defined ? 'readonly' : ''; ?>
        />
        <p class="description">
            <?php _e('Enter your Google Maps API key. ', 'happy-place-theme'); ?>
            <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">
                <?php _e('Get an API key', 'happy-place-theme'); ?>
            </a>
        </p>
        <?php if (!$is_defined): ?>
            <p class="description">
                <strong><?php _e('Security tip:', 'happy-place-theme'); ?></strong> 
                <?php _e('For better security, consider defining this in wp-config.php using: define(\'HPH_GOOGLE_MAPS_API_KEY\', \'your-key-here\');', 'happy-place-theme'); ?>
            </p>
        <?php endif; ?>
        <?php
    }

    /**
     * Get configuration value
     */
    public function get($key, $default = null) {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        
        // Check nested keys (dot notation)
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
    
    /**
     * Set configuration value
     */
    public function set($key, $value) {
        $this->config[$key] = $value;
        return $this;
    }
    
    /**
     * Get all configuration
     */
    public function all() {
        return $this->config;
    }
}
