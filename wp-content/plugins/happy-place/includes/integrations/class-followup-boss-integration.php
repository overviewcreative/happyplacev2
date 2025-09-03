<?php
/**
 * FollowUp Boss Integration
 * 
 * Handles integration with FollowUp Boss CRM for lead management
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
    private function init_config() {
        $settings = get_option('hp_integration_settings', []);
        $fub_settings = $settings['followup_boss'] ?? [];
        
        // Use new configuration structure or fallback to old settings
        $this->api_key = $fub_settings['api_key'] ?? get_option($this->option_prefix . 'api_key', '');
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin hooks
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        
        // AJAX hooks
        add_action('wp_ajax_hp_test_followup_boss_connection', [$this, 'handle_test_connection']);
        add_action('wp_ajax_hp_sync_lead_to_followup_boss', [$this, 'handle_sync_lead']);
        
        // Lead capture hooks
        add_action('hp_lead_captured', [$this, 'sync_lead_to_followup_boss'], 10, 2);
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'happy-place-settings',
            __('FollowUp Boss Integration', 'happy-place'),
            __('FollowUp Boss', 'happy-place'),
            'manage_options',
            'happy-place-followup-boss',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('hp_followup_boss_settings', $this->option_prefix . 'api_key');
        register_setting('hp_followup_boss_settings', $this->option_prefix . 'enabled');
        register_setting('hp_followup_boss_settings', $this->option_prefix . 'auto_sync');
        register_setting('hp_followup_boss_settings', $this->option_prefix . 'default_source');
        register_setting('hp_followup_boss_settings', $this->option_prefix . 'default_status');
        register_setting('hp_followup_boss_settings', $this->option_prefix . 'assign_to_agent');
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
            HP_VERSION,
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
     * Render settings page
     */
    public function render_settings_page() {
        $api_key = get_option($this->option_prefix . 'api_key', '');
        $enabled = get_option($this->option_prefix . 'enabled', false);
        $auto_sync = get_option($this->option_prefix . 'auto_sync', false);
        $default_source = get_option($this->option_prefix . 'default_source', 'Website');
        $default_status = get_option($this->option_prefix . 'default_status', 'New');
        $assign_to_agent = get_option($this->option_prefix . 'assign_to_agent', false);
        
        $nonce = wp_create_nonce('hp_followup_boss_nonce');
        ?>
        
        <div class="wrap">
            <h1><?php esc_html_e('FollowUp Boss Integration', 'happy-place'); ?></h1>
            
            <div class="hph-admin-layout">
                <div class="hph-admin-main">
                    
                    <!-- Integration Status -->
                    <div class="card">
                        <h2><?php esc_html_e('Connection Status', 'happy-place'); ?></h2>
                        <div class="connection-status" id="connection-status">
                            <?php if (!empty($api_key)): ?>
                                <div class="status-indicator status-unknown">
                                    <i class="fas fa-question-circle"></i>
                                    <span><?php esc_html_e('Connection status unknown', 'happy-place'); ?></span>
                                </div>
                            <?php else: ?>
                                <div class="status-indicator status-disconnected">
                                    <i class="fas fa-times-circle"></i>
                                    <span><?php esc_html_e('Not configured', 'happy-place'); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($api_key)): ?>
                            <p>
                                <button type="button" class="button button-secondary" id="test-connection">
                                    <i class="fas fa-plug"></i>
                                    <?php esc_html_e('Test Connection', 'happy-place'); ?>
                                </button>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Settings Form -->
                    <form method="post" action="options.php" class="hph-settings-form">
                        <?php settings_fields('hp_followup_boss_settings'); ?>
                        
                        <!-- API Configuration -->
                        <div class="card">
                            <h2><?php esc_html_e('API Configuration', 'happy-place'); ?></h2>
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
                                               class="regular-text code"
                                               placeholder="fka_..."
                                               autocomplete="off">
                                        <button type="button" class="button button-small toggle-password" data-target="api_key">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <p class="description">
                                            <?php 
                                            printf(
                                                esc_html__('Get your API key from your %s account settings.', 'happy-place'),
                                                '<a href="https://app.followupboss.com/2/settings/integrations" target="_blank">FollowUp Boss</a>'
                                            ); 
                                            ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <?php esc_html_e('Enable Integration', 'happy-place'); ?>
                                    </th>
                                    <td>
                                        <fieldset>
                                            <legend class="screen-reader-text">
                                                <span><?php esc_html_e('Enable Integration', 'happy-place'); ?></span>
                                            </legend>
                                            <label for="enabled">
                                                <input type="checkbox" 
                                                       id="enabled" 
                                                       name="<?php echo esc_attr($this->option_prefix . 'enabled'); ?>" 
                                                       value="1" 
                                                       <?php checked(1, $enabled); ?>>
                                                <?php esc_html_e('Enable FollowUp Boss integration', 'happy-place'); ?>
                                            </label>
                                            <p class="description">
                                                <?php esc_html_e('When enabled, leads will be synced to FollowUp Boss automatically.', 'happy-place'); ?>
                                            </p>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Sync Settings -->
                        <div class="card">
                            <h2><?php esc_html_e('Sync Settings', 'happy-place'); ?></h2>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <?php esc_html_e('Auto Sync', 'happy-place'); ?>
                                    </th>
                                    <td>
                                        <fieldset>
                                            <legend class="screen-reader-text">
                                                <span><?php esc_html_e('Auto Sync', 'happy-place'); ?></span>
                                            </legend>
                                            <label for="auto_sync">
                                                <input type="checkbox" 
                                                       id="auto_sync" 
                                                       name="<?php echo esc_attr($this->option_prefix . 'auto_sync'); ?>" 
                                                       value="1" 
                                                       <?php checked(1, $auto_sync); ?>>
                                                <?php esc_html_e('Automatically sync new leads', 'happy-place'); ?>
                                            </label>
                                            <p class="description">
                                                <?php esc_html_e('When enabled, leads will be synced immediately upon capture.', 'happy-place'); ?>
                                            </p>
                                        </fieldset>
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
                                               value="<?php echo esc_attr($default_source); ?>" 
                                               class="regular-text">
                                        <p class="description">
                                            <?php esc_html_e('The default source to assign to leads from this website.', 'happy-place'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="default_status"><?php esc_html_e('Default Status', 'happy-place'); ?></label>
                                    </th>
                                    <td>
                                        <select id="default_status" 
                                                name="<?php echo esc_attr($this->option_prefix . 'default_status'); ?>">
                                            <option value="New" <?php selected('New', $default_status); ?>><?php esc_html_e('New', 'happy-place'); ?></option>
                                            <option value="Active" <?php selected('Active', $default_status); ?>><?php esc_html_e('Active', 'happy-place'); ?></option>
                                            <option value="Nurture" <?php selected('Nurture', $default_status); ?>><?php esc_html_e('Nurture', 'happy-place'); ?></option>
                                            <option value="Hot" <?php selected('Hot', $default_status); ?>><?php esc_html_e('Hot', 'happy-place'); ?></option>
                                        </select>
                                        <p class="description">
                                            <?php esc_html_e('The default status to assign to new leads.', 'happy-place'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <?php esc_html_e('Agent Assignment', 'happy-place'); ?>
                                    </th>
                                    <td>
                                        <fieldset>
                                            <legend class="screen-reader-text">
                                                <span><?php esc_html_e('Agent Assignment', 'happy-place'); ?></span>
                                            </legend>
                                            <label for="assign_to_agent">
                                                <input type="checkbox" 
                                                       id="assign_to_agent" 
                                                       name="<?php echo esc_attr($this->option_prefix . 'assign_to_agent'); ?>" 
                                                       value="1" 
                                                       <?php checked(1, $assign_to_agent); ?>>
                                                <?php esc_html_e('Automatically assign leads to listing agents', 'happy-place'); ?>
                                            </label>
                                            <p class="description">
                                                <?php esc_html_e('When enabled, leads will be assigned to the agent associated with the property listing.', 'happy-place'); ?>
                                            </p>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <?php submit_button(); ?>
                    </form>
                </div>
                
                <!-- Sidebar -->
                <div class="hph-admin-sidebar">
                    
                    <!-- Help Card -->
                    <div class="card">
                        <h3><?php esc_html_e('About FollowUp Boss', 'happy-place'); ?></h3>
                        <p><?php esc_html_e('FollowUp Boss is a powerful CRM designed specifically for real estate teams. This integration automatically syncs leads from your website.', 'happy-place'); ?></p>
                        
                        <h4><?php esc_html_e('Features:', 'happy-place'); ?></h4>
                        <ul>
                            <li><?php esc_html_e('Automatic lead sync', 'happy-place'); ?></li>
                            <li><?php esc_html_e('Agent assignment', 'happy-place'); ?></li>
                            <li><?php esc_html_e('Source tracking', 'happy-place'); ?></li>
                            <li><?php esc_html_e('Status management', 'happy-place'); ?></li>
                        </ul>
                        
                        <p>
                            <a href="https://www.followupboss.com" target="_blank" class="button button-secondary">
                                <?php esc_html_e('Learn More', 'happy-place'); ?>
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </p>
                    </div>
                    
                    <!-- API Documentation -->
                    <div class="card">
                        <h3><?php esc_html_e('API Documentation', 'happy-place'); ?></h3>
                        <p><?php esc_html_e('Need help setting up your API key?', 'happy-place'); ?></p>
                        
                        <ol>
                            <li><?php esc_html_e('Log in to your FollowUp Boss account', 'happy-place'); ?></li>
                            <li><?php esc_html_e('Go to Settings → Integrations', 'happy-place'); ?></li>
                            <li><?php esc_html_e('Generate a new API key', 'happy-place'); ?></li>
                            <li><?php esc_html_e('Copy and paste the key above', 'happy-place'); ?></li>
                        </ol>
                        
                        <p>
                            <a href="https://docs.followupboss.com/article/77-api-getting-started" target="_blank">
                                <?php esc_html_e('View API Docs', 'happy-place'); ?>
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </p>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="card">
                        <h3><?php esc_html_e('Recent Activity', 'happy-place'); ?></h3>
                        <div id="recent-activity">
                            <p class="description"><?php esc_html_e('Recent sync activity will appear here.', 'happy-place'); ?></p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <style>
        .hph-admin-layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            margin-top: 20px;
        }
        
        .connection-status {
            margin: 15px 0;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .status-indicator i {
            font-size: 18px;
        }
        
        .status-connected {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-disconnected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-unknown {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .toggle-password {
            margin-left: 5px;
        }
        
        .hph-admin-sidebar .card {
            margin-bottom: 20px;
        }
        
        .hph-admin-sidebar ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .hph-admin-sidebar ol {
            padding-left: 20px;
        }
        
        @media (max-width: 782px) {
            .hph-admin-layout {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <?php
    }
    
    /**
     * Handle test connection AJAX request
     */
    public function handle_test_connection() {
        check_ajax_referer('hp_followup_boss_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'happy-place')]);
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
        // Get current configuration
        $settings = get_option('hp_integration_settings', []);
        $fub_settings = $settings['followup_boss'] ?? [];
        
        // Check if integration is enabled
        $enabled = $fub_settings['enabled'] ?? get_option($this->option_prefix . 'enabled', false);
        if (!$enabled) {
            return false;
        }
        
        if (empty($this->api_key)) {
            hp_log('FollowUp Boss API key not configured', 'warning', 'followup-boss');
            return false;
        }
        
        try {
            $person_data = $this->prepare_person_data($lead_data, $listing_id);
            $result = $this->create_person($person_data);
            
            if ($result['success']) {
                hp_log('Lead synced to FollowUp Boss: ' . $lead_data['email'], 'info', 'followup-boss');
                
                // Store FollowUp Boss ID for future reference
                if (isset($lead_data['lead_id'])) {
                    update_post_meta($lead_data['lead_id'], '_followup_boss_id', $result['person_id']);
                }
                
                return $result;
            } else {
                hp_log('Failed to sync lead to FollowUp Boss: ' . $result['message'], 'error', 'followup-boss');
                return false;
            }
        } catch (\Exception $e) {
            hp_log('FollowUp Boss sync error: ' . $e->getMessage(), 'error', 'followup-boss');
            return false;
        }
    }
    
    /**
     * Prepare person data for FollowUp Boss API
     */
    private function prepare_person_data($lead_data, $listing_id = null) {
        // Get configuration settings
        $settings = get_option('hp_integration_settings', []);
        $fub_settings = $settings['followup_boss'] ?? [];
        
        $default_source = $fub_settings['lead_source'] ?? get_option($this->option_prefix . 'default_source', 'Website');
        $default_status = $fub_settings['default_status'] ?? get_option($this->option_prefix . 'default_status', 'New');
        
        $person_data = [
            'firstName' => $lead_data['first_name'] ?? '',
            'lastName' => $lead_data['last_name'] ?? '',
            'emails' => [
                ['value' => $lead_data['email']]
            ],
            'source' => $lead_data['source'] ?? $default_source,
            'status' => $default_status,
            'tags' => ['Website Lead']
        ];
        
        // Add phone if provided
        if (!empty($lead_data['phone'])) {
            $person_data['phones'] = [
                ['value' => $lead_data['phone']]
            ];
        }
        
        // Add notes/message if provided
        if (!empty($lead_data['message'])) {
            $person_data['note'] = $lead_data['message'];
        }
        
        // Add property information if available
        if ($listing_id) {
            $listing_address = hpt_get_listing_address($listing_id, 'full');
            $listing_price = hpt_get_listing_price_formatted($listing_id);
            
            if ($listing_address) {
                $person_data['tags'][] = 'Property Inquiry';
                
                if (empty($person_data['note'])) {
                    $person_data['note'] = '';
                } else {
                    $person_data['note'] .= "\n\n";
                }
                
                $person_data['note'] .= "Property Inquiry:\n";
                $person_data['note'] .= "Address: {$listing_address}\n";
                
                if ($listing_price) {
                    $person_data['note'] .= "Price: {$listing_price}\n";
                }
                
                $person_data['note'] .= "Listing URL: " . get_permalink($listing_id);
            }
        }
        
        // Agent assignment
        if (get_option($this->option_prefix . 'assign_to_agent', false) && $listing_id) {
            $agent_id = get_post_meta($listing_id, 'listing_agent', true);
            if ($agent_id) {
                $agent_email = get_post_meta($agent_id, 'email', true);
                if ($agent_email) {
                    $person_data['assignedTo'] = $agent_email;
                }
            }
        }
        
        return apply_filters('hp_followup_boss_person_data', $person_data, $lead_data, $listing_id);
    }
    
    /**
     * Create person in FollowUp Boss
     */
    private function create_person($person_data) {
        $response = wp_remote_post(self::API_BASE_URL . 'people', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->api_key . ':'),
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($person_data),
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
        
        if ($status_code === 200 || $status_code === 201) {
            return [
                'success' => true,
                'message' => __('Person created successfully in FollowUp Boss.', 'happy-place'),
                'person_id' => $data['id'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'message' => $data['error'] ?? sprintf(__('API returned status code: %d', 'happy-place'), $status_code)
            ];
        }
    }
    
    /**
     * Handle sync lead AJAX request
     */
    public function handle_sync_lead() {
        check_ajax_referer('hp_followup_boss_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'happy-place')]);
        }
        
        $lead_id = intval($_POST['lead_id'] ?? 0);
        
        if (!$lead_id) {
            wp_send_json_error(['message' => __('Invalid lead ID.', 'happy-place')]);
        }
        
        // Get lead data (this would depend on your lead post type structure)
        $lead = get_post($lead_id);
        if (!$lead || $lead->post_type !== 'lead') {
            wp_send_json_error(['message' => __('Lead not found.', 'happy-place')]);
        }
        
        $lead_data = [
            'lead_id' => $lead_id,
            'first_name' => get_post_meta($lead_id, 'first_name', true),
            'last_name' => get_post_meta($lead_id, 'last_name', true),
            'email' => get_post_meta($lead_id, 'email', true),
            'phone' => get_post_meta($lead_id, 'phone', true),
            'message' => get_post_meta($lead_id, 'message', true),
            'source' => get_post_meta($lead_id, 'source', true)
        ];
        
        $listing_id = get_post_meta($lead_id, 'listing_id', true);
        
        $result = $this->sync_lead_to_followup_boss($lead_data, $listing_id);
        
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
     * Get API key (for admin use)
     */
    public function get_api_key() {
        return $this->api_key;
    }
}

// Initialize
FollowUp_Boss_Integration::get_instance();