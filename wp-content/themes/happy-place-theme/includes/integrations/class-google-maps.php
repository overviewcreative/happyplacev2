<?php
/**
 * Google Maps Integration
 * 
 * Handles Google Maps API integration for property maps
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HPH_Google_Maps
 */
class HPH_Google_Maps {
    
    /**
     * Google Maps API Key
     */
    private $api_key;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = $this->get_api_key();
        $this->init();
    }
    
    /**
     * Initialize Google Maps integration
     */
    public function init() {
        // Only load on pages that need maps
        if ($this->should_load_maps()) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_maps_api'), 5);
            add_action('wp_head', array($this, 'add_maps_callback'));
        }
        
        // Add settings for API key management
        add_action('init', array($this, 'register_settings'));
    }
    
    /**
     * Check if maps should be loaded on current page
     */
    private function should_load_maps() {
        // Load on single listing pages
        if (is_singular('listing')) {
            return true;
        }
        
        // Load on pages with map shortcodes or components
        global $post;
        if ($post && (
            has_shortcode($post->post_content, 'hph_property_map') ||
            strpos($post->post_content, 'property-map') !== false
        )) {
            return true;
        }
        
        // Load on search results pages
        if (is_search() || is_post_type_archive('listing')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get Google Maps API key
     */
    private function get_api_key() {
        // Try to get from environment variable first (most secure)
        $api_key = getenv('GOOGLE_MAPS_API_KEY');
        
        // Fallback to WordPress option
        if (!$api_key) {
            $api_key = get_option('hph_google_maps_api_key', '');
        }
        
        // Fallback to constant (for wp-config.php)
        if (!$api_key && defined('HPH_GOOGLE_MAPS_API_KEY')) {
            $api_key = HPH_GOOGLE_MAPS_API_KEY;
        }
        
        return $api_key;
    }
    
    /**
     * Enqueue Google Maps API
     */
    public function enqueue_maps_api() {
        if (!$this->api_key) {
            if (WP_DEBUG) {
                error_log('Google Maps API key not found. Please set HPH_GOOGLE_MAPS_API_KEY or configure in WordPress admin.');
            }
            return;
        }
        
        $maps_url = add_query_arg(array(
            'key' => $this->api_key,
            'libraries' => 'places,geometry,drawing',
            'callback' => 'initPropertyMaps',
            'loading' => 'async'
        ), 'https://maps.googleapis.com/maps/api/js');
        
        wp_enqueue_script(
            'google-maps-api',
            $maps_url,
            array(),
            null, // No version to prevent caching issues
            true
        );
        
        // Enqueue our property map script
        wp_enqueue_script(
            'hph-property-map',
            HPH_THEME_URI . '/assets/js/components/property-map.js',
            array('jquery'),
            HPH_VERSION,
            true
        );
        
        // Enqueue map styles
        wp_enqueue_style(
            'hph-property-map',
            HPH_THEME_URI . '/assets/css/components/property-map.css',
            array(),
            HPH_VERSION
        );
        
        // Add inline script for theme URI
        wp_add_inline_script('hph-property-map', 
            'window.HPH_THEME_URI = "' . esc_js(HPH_THEME_URI) . '";', 
            'before'
        );
    }
    
    /**
     * Add Google Maps callback to head
     */
    public function add_maps_callback() {
        if (!$this->api_key) {
            return;
        }
        ?>
        <script>
        // Google Maps callback function
        window.initPropertyMaps = function() {
            if (typeof HPH !== 'undefined' && HPH.PropertyMap) {
                HPH.PropertyMap.init();
            } else {
                // Retry if HPH object not ready yet
                setTimeout(window.initPropertyMaps, 100);
            }
        };
        </script>
        <?php
    }
    
    /**
     * Register settings for API key management
     */
    public function register_settings() {
        // Register setting
        register_setting('hph_maps_settings', 'hph_google_maps_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));
        
        // Add admin menu if in admin
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
        }
    }
    
    /**
     * Add admin menu for maps settings
     */
    public function add_admin_menu() {
        add_options_page(
            __('Google Maps Settings', 'happy-place-theme'),
            __('Google Maps', 'happy-place-theme'),
            'manage_options',
            'hph-google-maps',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Settings page content
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Google Maps Settings', 'happy-place-theme'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('hph_maps_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="hph_google_maps_api_key">
                                <?php _e('Google Maps API Key', 'happy-place-theme'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="hph_google_maps_api_key" 
                                   name="hph_google_maps_api_key" 
                                   value="<?php echo esc_attr(get_option('hph_google_maps_api_key', '')); ?>" 
                                   class="regular-text" />
                            <p class="description">
                                <?php _e('Enter your Google Maps API key. Get one from the', 'happy-place-theme'); ?>
                                <a href="https://console.cloud.google.com/apis/credentials" target="_blank">
                                    <?php _e('Google Cloud Console', 'happy-place-theme'); ?>
                                </a>.
                            </p>
                            
                            <?php if (!$this->api_key): ?>
                                <div class="notice notice-warning inline">
                                    <p>
                                        <strong><?php _e('API Key Required:', 'happy-place-theme'); ?></strong>
                                        <?php _e('Google Maps functionality will not work without a valid API key.', 'happy-place-theme'); ?>
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="notice notice-success inline">
                                    <p>
                                        <strong><?php _e('API Key Configured:', 'happy-place-theme'); ?></strong>
                                        <?php _e('Google Maps is ready to use.', 'happy-place-theme'); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <h2><?php _e('Required APIs', 'happy-place-theme'); ?></h2>
                <p><?php _e('Make sure the following APIs are enabled in your Google Cloud project:', 'happy-place-theme'); ?></p>
                <ul class="ul-disc">
                    <li>Maps JavaScript API</li>
                    <li>Places API</li>
                    <li>Geocoding API</li>
                    <li>Directions API</li>
                    <li>Distance Matrix API</li>
                </ul>
                
                <?php submit_button(); ?>
            </form>
            
            <h2><?php _e('Test Map', 'happy-place-theme'); ?></h2>
            <p><?php _e('Use this shortcode to test the map functionality:', 'happy-place-theme'); ?></p>
            <code>[hph_property_map lat="39.7392" lng="-104.9903" zoom="15"]</code>
        </div>
        <?php
    }
    
    /**
     * Geocode an address
     */
    public function geocode_address($address) {
        if (!$this->api_key) {
            return false;
        }
        
        $url = add_query_arg(array(
            'address' => urlencode($address),
            'key' => $this->api_key
        ), 'https://maps.googleapis.com/maps/api/geocode/json');
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data['status'] === 'OK' && !empty($data['results'])) {
            $location = $data['results'][0]['geometry']['location'];
            return array(
                'lat' => $location['lat'],
                'lng' => $location['lng']
            );
        }
        
        return false;
    }
    
    /**
     * Get singleton instance
     */
    public static function instance() {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }
        return $instance;
    }
}

// Initialize Google Maps integration
HPH_Google_Maps::instance();