// File: /includes/admin/class-hp-settings.php

<?php
class HP_Settings {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    public function add_settings_page() {
        add_options_page(
            'Happy Place Settings',
            'Happy Place',
            'manage_options',
            'hp-settings',
            [$this, 'render_settings_page']
        );
    }
    
    public function register_settings() {
        // API Settings
        register_setting('hp_settings', 'hp_google_maps_api_key');
        register_setting('hp_settings', 'hp_walkscore_api_key');
        register_setting('hp_settings', 'hp_default_listing_office');
        register_setting('hp_settings', 'hp_default_office_phone');
        
        add_settings_section(
            'hp_api_settings',
            'API Settings',
            [$this, 'render_api_section'],
            'hp-settings'
        );
        
        add_settings_field(
            'hp_google_maps_api_key',
            'Google Maps API Key',
            [$this, 'render_google_api_field'],
            'hp-settings',
            'hp_api_settings'
        );
        
        add_settings_field(
            'hp_walkscore_api_key',
            'Walk Score API Key',
            [$this, 'render_walkscore_field'],
            'hp-settings',
            'hp_api_settings'
        );
        
        // Office Defaults
        add_settings_section(
            'hp_office_settings',
            'Office Defaults',
            [$this, 'render_office_section'],
            'hp-settings'
        );
        
        add_settings_field(
            'hp_default_listing_office',
            'Default Office Name',
            [$this, 'render_office_name_field'],
            'hp-settings',
            'hp_office_settings'
        );
        
        add_settings_field(
            'hp_default_office_phone',
            'Default Office Phone',
            [$this, 'render_office_phone_field'],
            'hp-settings',
            'hp_office_settings'
        );
    }
    
    public function render_api_section() {
        echo '<p>API keys for external services integration.</p>';
    }
    
    public function render_office_section() {
        echo '<p>Default values for new listings.</p>';
    }
    
    public function render_google_api_field() {
        $api_key = get_option('hp_google_maps_api_key', '');
        ?>
        <input type="text" 
               name="hp_google_maps_api_key" 
               value="<?php echo esc_attr($api_key); ?>" 
               class="regular-text code" />
        <p class="description">
            Required for geocoding and maps. 
            <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">
                Get an API key →
            </a>
        </p>
        <?php
    }
    
    public function render_walkscore_field() {
        $api_key = get_option('hp_walkscore_api_key', '');
        ?>
        <input type="text" 
               name="hp_walkscore_api_key" 
               value="<?php echo esc_attr($api_key); ?>" 
               class="regular-text code" />
        <p class="description">
            Optional: For walkability scores. 
            <a href="https://www.walkscore.com/professional/walk-score-apis.php" target="_blank">
                Get an API key →
            </a>
        </p>
        <?php
    }
    
    public function render_office_name_field() {
        $office = get_option('hp_default_listing_office', 'Happy Place Homes');
        ?>
        <input type="text" 
               name="hp_default_listing_office" 
               value="<?php echo esc_attr($office); ?>" 
               class="regular-text" />
        <?php
    }
    
    public function render_office_phone_field() {
        $phone = get_option('hp_default_office_phone', '');
        ?>
        <input type="text" 
               name="hp_default_office_phone" 
               value="<?php echo esc_attr($phone); ?>" 
               class="regular-text" 
               placeholder="(302) 555-0123" />
        <?php
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>
                <span class="dashicons dashicons-admin-home" style="font-size: 30px; margin-right: 10px;"></span>
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>
            
            <?php settings_errors(); ?>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('hp_settings');
                do_settings_sections('hp-settings');
                submit_button();
                ?>
            </form>
            
            <div class="hp-settings-footer" style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd;">
                <p>
                    <strong>Happy Place Plugin</strong> v<?php echo HP_VERSION; ?> | 
                    <?php if (HP_DEBUG): ?>
                        <span style="color: orange;">🐛 Debug Mode Active</span> | 
                    <?php endif; ?>
                    <a href="#" onclick="HPTestGeocoding(); return false;">Test Geocoding</a>
                </p>
            </div>
        </div>
        
        <script>
        function HPTestGeocoding() {
            if (confirm('Test geocoding with a sample address?')) {
                jQuery.post(ajaxurl, {
                    action: 'hp_test_geocoding',
                    _ajax_nonce: '<?php echo wp_create_nonce('hp_test'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('✓ Geocoding works!\n\nTest address: ' + response.data.address + '\nLat: ' + response.data.lat + '\nLng: ' + response.data.lng);
                    } else {
                        alert('✗ Geocoding failed. Check your API key.');
                    }
                });
            }
        }
        </script>
        <?php
    }
}

// Initialize
add_action('init', function() {
    new HP_Settings();
});

// Test geocoding endpoint
add_action('wp_ajax_hp_test_geocoding', function() {
    check_ajax_referer('hp_test', '_ajax_nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $api_key = get_option('hp_google_maps_api_key', '');
    if (!$api_key) {
        wp_send_json_error('No API key configured');
    }
    
    // Test with White House address
    $test_address = '1600 Pennsylvania Avenue NW, Washington, DC 20500';
    $url = 'https://maps.googleapis.com/maps/api/geocode/json';
    $response = wp_remote_get(add_query_arg([
        'address' => $test_address,
        'key' => $api_key
    ], $url));
    
    if (!is_wp_error($response)) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($data['status'] === 'OK') {
            wp_send_json_success([
                'address' => $test_address,
                'lat' => $data['results'][0]['geometry']['location']['lat'],
                'lng' => $data['results'][0]['geometry']['location']['lng']
            ]);
        }
    }
    
    wp_send_json_error('Geocoding failed');
});