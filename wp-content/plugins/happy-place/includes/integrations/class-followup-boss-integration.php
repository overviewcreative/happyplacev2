<?php
/**
 * FollowUp Boss Integration - SIMPLIFIED & FIXED
 * 
 * Handles integration with FollowUp Boss CRM for lead management
 * Simplified nonce verification to prevent authentication failures
 * 
 * @package HappyPlace
 * @since 3.1.0
 */

namespace HappyPlace\Integrations;

class FollowUp_Boss_Integration {
    
    /**
     * API base URL
     */
    const API_BASE_URL = 'https://api.followupboss.com/v1/';
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * API key
     */
    private $api_key;
    
    /**
     * Options prefix
     */
    private $option_prefix = 'hp_followup_boss_';
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_config();
        $this->init_hooks();
    }
    
    /**
     * Initialize configuration
     */
    private function init_config($force_refresh = false) {
        // Use Configuration Manager for API key
        if (class_exists('\\HappyPlace\\Core\\ConfigurationManager')) {
            $config_manager = \HappyPlace\Core\ConfigurationManager::get_instance();
            $this->api_key = $config_manager->get('followup_boss_api_key');
        }
        
        // Fallback to direct option if Configuration Manager not available
        if (empty($this->api_key)) {
            $this->api_key = get_option('hp_followup_boss_api_key', '');
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin hooks - settings handled by main integrations page
        add_action('admin_init', [$this, 'register_settings']);
        
        // AJAX hooks - SIMPLIFIED
        add_action('wp_ajax_hp_test_followup_boss_connection', [$this, 'handle_test_connection']);
        add_action('wp_ajax_hp_sync_lead_to_followup_boss', [$this, 'handle_sync_lead']);
        
        // Automatic lead sync - listen for new leads
        add_action('hp_new_lead_submitted', [$this, 'auto_sync_new_lead'], 10, 2);
        
        // Lead capture hooks
        add_action('hp_lead_captured', [$this, 'sync_lead_to_followup_boss'], 10, 2);
        
        // FollowUp Boss pixel tracking
        add_action('wp_head', [$this, 'output_tracking_pixel']);
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'happy-place',
            __('FollowUp Boss Integration', 'happy-place'),
            __('FollowUp Boss', 'happy-place'),
            'manage_options',
            'happy-place-followup-boss',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Register settings - Updated for main integrations page
     */
    public function register_settings() {
        register_setting('hp_integrations_settings', 'hp_followup_boss_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('hp_integrations_settings', 'hp_fub_auto_sync', [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean'
        ]);
        register_setting('hp_integrations_settings', 'hp_fub_lead_source', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('hp_integrations_settings', 'hp_fub_default_lead_type', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('hp_integrations_settings', 'hp_fub_widget_tracker_id', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
    }
    
    /**
     * Handle test connection AJAX request - SIMPLIFIED
     */
    public function handle_test_connection() {
        // SIMPLIFIED nonce check - single action only
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_followup_boss_nonce')) {
            wp_send_json_error(['message' => __('Security verification failed.', 'happy-place')]);
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'happy-place')]);
            return;
        }
        
        try {
            $result = $this->test_connection();
            
            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        // Force refresh config to get the latest API key from database
        $this->init_config(true);
        
        if (empty($this->api_key)) {
            return [
                'success' => false,
                'message' => __('API key is not configured.', 'happy-place')
            ];
        }
        
        $response = wp_remote_get(self::API_BASE_URL . 'people', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->api_key . ':'),
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message()
            ];
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            return [
                'success' => true,
                'message' => __('Connection successful! API key is valid.', 'happy-place'),
                'status' => 'connected'
            ];
        } elseif ($status_code === 401) {
            return [
                'success' => false,
                'message' => __('Authentication failed. Please check your API key.', 'happy-place'),
                'status' => 'unauthorized'
            ];
        } else {
            return [
                'success' => false,
                'message' => sprintf(__('API returned status code: %d', 'happy-place'), $status_code),
                'status' => 'error'
            ];
        }
    }
    
    /**
     * Sync lead to FollowUp Boss
     */
    public function sync_lead_to_followup_boss($lead_data, $listing_id = null) {
        error_log('FollowUp Boss: sync_lead_to_followup_boss called with lead_data: ' . print_r($lead_data, true));
        
        // Re-initialize config to get the latest settings
        $this->init_config();
        
        // Check if integration is enabled
        $enabled = get_option('hp_fub_auto_sync', false);
        if (!$enabled) {
            error_log('FollowUp Boss: Integration is disabled (hp_fub_auto_sync = false)');
            return false;
        }
        
        if (empty($this->api_key)) {
            error_log('FollowUp Boss API key not configured');
            return false;
        }
        
        try {
            $event_data = $this->prepare_event_data($lead_data, $listing_id);
            $result = $this->create_event($event_data);
            
            if ($result['success']) {
                error_log('Lead synced to FollowUp Boss: ' . ($lead_data['email'] ?? 'unknown'));
                
                // Store FollowUp Boss ID for future reference
                if (isset($lead_data['lead_id'])) {
                    update_post_meta($lead_data['lead_id'], '_followup_boss_id', $result['person_id']);
                }
                
                return $result;
            } else {
                error_log('Failed to sync lead to FollowUp Boss: ' . $result['message']);
                return false;
            }
        } catch (\Exception $e) {
            error_log('FollowUp Boss sync error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Prepare event data for FollowUp Boss API
     * Using /v1/events endpoint (recommended approach)
     */
    private function prepare_event_data($lead_data, $listing_id = null) {
        $default_source = get_option('hp_fub_lead_source', 'Website');
        $default_event_type = get_option('hp_fub_default_lead_type', 'General Inquiry');
        
        // Create person data structure
        $person_data = [
            'firstName' => $lead_data['first_name'] ?? '',
            'lastName' => $lead_data['last_name'] ?? '',
            'emails' => [
                ['value' => $lead_data['email']]
            ],
            'tags' => ['Website Lead']
        ];
        
        // Add phone if provided
        if (!empty($lead_data['phone'])) {
            $person_data['phones'] = [
                ['value' => $lead_data['phone']]
            ];
        }
        
        // Create event data structure
        $event_data = [
            'source' => $lead_data['source'] ?? $default_source,
            'type' => $this->map_lead_type_to_event_type($default_event_type),
            'person' => $person_data
        ];
        
        // Add message if provided
        if (!empty($lead_data['message'])) {
            $event_data['message'] = $lead_data['message'];
        }
        
        // Add property information if available
        if ($listing_id) {
            $listing_title = get_the_title($listing_id);
            if ($listing_title) {
                $event_data['person']['tags'][] = 'Property Inquiry';
                $event_data['type'] = 'Property Inquiry';
                
                // Get listing details for property data
                $listing_data = $this->get_listing_data($listing_id);
                if ($listing_data) {
                    $event_data['property'] = $listing_data;
                }
            }
        }
        
        return apply_filters('hp_followup_boss_event_data', $event_data, $lead_data, $listing_id);
    }
    
    /**
     * Create event in FollowUp Boss (recommended approach)
     * This will create/update the person and trigger automations
     */
    private function create_event($event_data) {
        $response = wp_remote_post(self::API_BASE_URL . 'events', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->api_key . ':'),
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($event_data),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message()
            ];
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($status_code === 200 || $status_code === 201 || $status_code === 204) {
            $message = '';
            $person_id = null;
            
            switch ($status_code) {
                case 200:
                    $message = __('Lead updated successfully in FollowUp Boss.', 'happy-place');
                    $person_id = $data['id'] ?? null;
                    break;
                case 201:
                    $message = __('New lead created successfully in FollowUp Boss.', 'happy-place');
                    $person_id = $data['id'] ?? null;
                    break;
                case 204:
                    $message = __('Lead processed (may be archived/ignored based on your settings).', 'happy-place');
                    break;
            }
            
            return [
                'success' => true,
                'message' => $message,
                'person_id' => $person_id,
                'status_code' => $status_code
            ];
        } else {
            error_log('FollowUp Boss API Error - Status: ' . $status_code . ', Body: ' . $body);
            return [
                'success' => false,
                'message' => $data['error'] ?? sprintf(__('API returned status code: %d', 'happy-place'), $status_code),
                'debug_info' => [
                    'status_code' => $status_code,
                    'response_body' => $body,
                    'api_url' => self::API_BASE_URL . 'events'
                ]
            ];
        }
    }
    
    /**
     * Handle sync lead AJAX request
     */
    public function handle_sync_lead() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_followup_boss_nonce')) {
            wp_send_json_error(['message' => __('Security verification failed.', 'happy-place')]);
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'happy-place')]);
            return;
        }
        
        $lead_id = intval($_POST['lead_id'] ?? 0);
        
        if (!$lead_id) {
            wp_send_json_error(['message' => __('Invalid lead ID.', 'happy-place')]);
            return;
        }
        
        // Get lead data from database
        global $wpdb;
        $table_name = $wpdb->prefix . 'hp_leads';
        $lead = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $lead_id));
        
        if (!$lead) {
            wp_send_json_error(['message' => __('Lead not found.', 'happy-place')]);
            return;
        }
        
        $lead_data = [
            'lead_id' => $lead->id,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'message' => $lead->message,
            'source' => $lead->source
        ];
        
        $result = $this->sync_lead_to_followup_boss($lead_data, $lead->listing_id);
        
        if ($result) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(['message' => __('Failed to sync lead.', 'happy-place')]);
        }
    }
    
    /**
     * Check if integration is enabled and configured
     */
    public function is_enabled() {
        return !empty($this->api_key) && get_option($this->option_prefix . 'enabled', false);
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'happy-place_page_happy-place-followup-boss') {
            return;
        }
        
        wp_enqueue_script(
            'hp-followup-boss-admin',
            HP_PLUGIN_URL . 'assets/js/followup-boss-admin.js',
            ['jquery'],
            HP_VERSION ?? '1.0.0',
            true
        );
        
        wp_localize_script('hp-followup-boss-admin', 'hpFollowUpBoss', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_followup_boss_nonce'),
            'strings' => [
                'testing' => __('Testing connection...', 'happy-place'),
                'success' => __('Connection successful!', 'happy-place'),
                'error' => __('Connection failed. Please check your API key.', 'happy-place'),
                'syncing' => __('Syncing lead...', 'happy-place'),
                'syncSuccess' => __('Lead synced successfully!', 'happy-place'),
                'syncError' => __('Failed to sync lead.', 'happy-place')
            ]
        ]);
    }
    
    /**
     * Render settings page - SIMPLIFIED
     */
    public function render_settings_page() {
        $api_key = get_option($this->option_prefix . 'api_key', '');
        $enabled = get_option($this->option_prefix . 'enabled', false);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('FollowUp Boss Integration', 'happy-place'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('hp_integrations_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="api_key"><?php esc_html_e('API Key', 'happy-place'); ?></label>
                        </th>
                        <td>
                            <input type="password" 
                                   id="api_key" 
                                   name="<?php echo esc_attr($this->option_prefix . 'api_key'); ?>" 
                                   value="<?php echo esc_attr($api_key); ?>" 
                                   class="regular-text code">
                            <p class="description">
                                <?php esc_html_e('Get your API key from your FollowUp Boss account settings.', 'happy-place'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Enable Integration', 'happy-place'); ?>
                        </th>
                        <td>
                            <label for="enabled">
                                <input type="checkbox" 
                                       id="enabled" 
                                       name="<?php echo esc_attr($this->option_prefix . 'enabled'); ?>" 
                                       value="1" 
                                       <?php checked(1, $enabled); ?>>
                                <?php esc_html_e('Enable FollowUp Boss integration', 'happy-place'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('When enabled, leads will be automatically synced to FollowUp Boss.', 'happy-place'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="default_source"><?php esc_html_e('Default Source', 'happy-place'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="default_source" 
                                   name="<?php echo esc_attr($this->option_prefix . 'default_source'); ?>" 
                                   value="<?php echo esc_attr(get_option($this->option_prefix . 'default_source', 'Website')); ?>" 
                                   class="regular-text">
                            <p class="description">
                                <?php esc_html_e('The default source to assign to leads from this website.', 'happy-place'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <?php if (!empty($api_key)): ?>
                <p>
                    <button type="button" class="button button-secondary" id="test-connection">
                        <?php esc_html_e('Test Connection', 'happy-place'); ?>
                    </button>
                </p>
                <div id="connection-status"></div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Map lead type to FollowUp Boss event type
     */
    private function map_lead_type_to_event_type($lead_type) {
        $mapping = [
            'General Inquiry' => 'General Inquiry',
            'Property Inquiry' => 'Property Inquiry', 
            'Seller Inquiry' => 'Seller Inquiry',
            'CMA Request' => 'Seller Inquiry',
            'Home Valuation' => 'Seller Inquiry',
            'Buyer Inquiry' => 'General Inquiry',
            'Registration' => 'Registration'
        ];
        
        return $mapping[$lead_type] ?? 'General Inquiry';
    }
    
    /**
     * Get listing data for property information
     */
    private function get_listing_data($listing_id) {
        if (!$listing_id) {
            return null;
        }
        
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            return null;
        }
        
        // Get ACF fields or custom fields
        $street = get_field('address_street', $listing_id) ?: '';
        $city = get_field('address_city', $listing_id) ?: '';
        $state = get_field('address_state', $listing_id) ?: '';
        $zip = get_field('address_zip', $listing_id) ?: '';
        $price = get_field('price', $listing_id) ?: '';
        $bedrooms = get_field('bedrooms', $listing_id) ?: '';
        $bathrooms = get_field('bathrooms', $listing_id) ?: '';
        $property_type = get_field('property_type', $listing_id) ?: '';
        
        $property_data = [];
        
        if ($street) $property_data['street'] = $street;
        if ($city) $property_data['city'] = $city;
        if ($state) $property_data['state'] = $state;
        if ($zip) $property_data['code'] = $zip;
        if ($price) $property_data['price'] = intval($price);
        if ($bedrooms) $property_data['bedrooms'] = intval($bedrooms);
        if ($bathrooms) $property_data['bathrooms'] = floatval($bathrooms);
        if ($property_type) $property_data['type'] = $property_type;
        
        return !empty($property_data) ? $property_data : null;
    }
    
    /**
     * Output FollowUp Boss Widget Tracker pixel code
     */
    public function output_tracking_pixel() {
        // Check if integration is enabled
        $enabled = get_option('hp_fub_auto_sync', false);
        if (!$enabled) {
            return;
        }
        
        // Get Widget Tracker ID
        $widget_tracker_id = get_option('hp_fub_widget_tracker_id', '');
        if (empty($widget_tracker_id)) {
            return;
        }
        
        // Don't load on admin pages
        if (is_admin()) {
            return;
        }
        
        ?>
<!-- FollowUp Boss Widget Tracker Code -->
<script>
(function(w,i,d,g,e,t){w["WidgetTrackerObject"]=g;(w[g]=w[g]||function()
{(w[g].q=w[g].q||[]).push(arguments);}),(w[g].ds=1*new Date());(e="script"),
(t=d.createElement(e)),(e=d.getElementsByTagName(e)[0]);t.async=1;t.src=i;
e.parentNode.insertBefore(t,e);})
(window,"https://widgetbe.com/agent",document,"widgetTracker");
window.widgetTracker("create", "<?php echo esc_js($widget_tracker_id); ?>");
window.widgetTracker("send", "pageview");
</script>
<!-- end FollowUp Boss Widget Tracker Code -->
        <?php
    }
    
    /**
     * Automatically sync new leads to FollowUp Boss
     * Triggered by hp_new_lead_submitted action hook
     */
    public function auto_sync_new_lead($lead_id, $lead_data) {
        // Check if auto sync is enabled
        $enabled = get_option('hp_fub_auto_sync', false);
        if (!$enabled) {
            error_log('FollowUp Boss: Auto sync is disabled');
            return;
        }
        
        // Re-initialize config to ensure we have the latest API key
        $this->init_config();
        
        if (empty($this->api_key)) {
            error_log('FollowUp Boss: API key not configured, skipping auto sync');
            return;
        }
        
        error_log('FollowUp Boss: Auto-syncing new lead ID: ' . $lead_id);
        
        // Convert lead data format if needed
        $formatted_lead_data = $lead_data;
        
        // If lead_data doesn't have the right format, try to get it from the lead post/database
        if (!isset($lead_data['first_name']) || !isset($lead_data['email'])) {
            // Try to get data from post meta or wp_hp_leads table
            if (get_post_type($lead_id) === 'lead') {
                // Lead stored as post
                $formatted_lead_data = [
                    'first_name' => get_post_meta($lead_id, 'first_name', true),
                    'last_name' => get_post_meta($lead_id, 'last_name', true),
                    'email' => get_post_meta($lead_id, 'email', true),
                    'phone' => get_post_meta($lead_id, 'phone', true),
                    'message' => get_post_meta($lead_id, 'message', true),
                    'source' => get_post_meta($lead_id, 'lead_source', true) ?: 'Website'
                ];
            } else {
                // Lead stored in wp_hp_leads table - query database
                global $wpdb;
                $lead_record = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}hp_leads WHERE id = %d", 
                    $lead_id
                ), ARRAY_A);
                
                if ($lead_record) {
                    $formatted_lead_data = [
                        'first_name' => $lead_record['first_name'] ?? '',
                        'last_name' => $lead_record['last_name'] ?? '',
                        'email' => $lead_record['email'] ?? '',
                        'phone' => $lead_record['phone'] ?? '',
                        'message' => $lead_record['message'] ?? '',
                        'source' => $lead_record['source'] ?? 'Website'
                    ];
                }
            }
        }
        
        // Get listing ID if available
        $listing_id = null;
        if (isset($lead_data['listing_id'])) {
            $listing_id = $lead_data['listing_id'];
        } elseif (get_post_type($lead_id) === 'lead') {
            $listing_id = get_post_meta($lead_id, 'listing_id', true);
        }
        
        // Attempt to sync
        $result = $this->sync_lead_to_followup_boss($formatted_lead_data, $listing_id);
        
        if ($result) {
            error_log('FollowUp Boss: Successfully auto-synced lead ID: ' . $lead_id);
            
            // Update lead record to mark as synced
            if (get_post_type($lead_id) === 'lead') {
                update_post_meta($lead_id, 'followup_boss_synced', true);
                update_post_meta($lead_id, 'followup_boss_sync_date', current_time('mysql'));
            } else {
                // Update in wp_hp_leads table
                global $wpdb;
                $wpdb->update(
                    $wpdb->prefix . 'hp_leads',
                    [
                        'followup_boss_synced' => 1,
                        'followup_boss_sync_date' => current_time('mysql')
                    ],
                    ['id' => $lead_id],
                    ['%d', '%s'],
                    ['%d']
                );
            }
        } else {
            error_log('FollowUp Boss: Failed to auto-sync lead ID: ' . $lead_id);
        }
    }
}

// Initialize
FollowUp_Boss_Integration::get_instance();