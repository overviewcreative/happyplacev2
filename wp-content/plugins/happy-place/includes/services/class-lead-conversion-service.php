<?php
/**
 * Lead Conversion Service
 * 
 * Handles converting leads to registered users,
 * manages the conversion funnel and user onboarding
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class LeadConversionService extends Service {
    
    protected string $name = 'lead_conversion_service';
    protected string $version = '4.0.0';
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        // Register hooks for lead conversion opportunities
        $this->register_conversion_hooks();
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
        
        // Register shortcodes
        add_shortcode('lead_conversion_form', [$this, 'render_conversion_form_shortcode']);
        add_shortcode('user_registration_prompt', [$this, 'render_registration_prompt_shortcode']);
        
        $this->initialized = true;
        $this->log('Lead Conversion Service initialized successfully');
    }
    
    /**
     * Register conversion opportunity hooks
     */
    private function register_conversion_hooks(): void {
        // Hook into lead creation to offer account creation
        add_action('hp_lead_created', [$this, 'offer_account_creation'], 10, 2);
        
        // Hook into favorite actions for non-users
        add_action('wp_ajax_nopriv_toggle_favorite', [$this, 'handle_guest_favorite_attempt']);
        
        // Hook into search save attempts
        add_action('wp_ajax_nopriv_save_search', [$this, 'handle_guest_save_search_attempt']);
        
        // User registration hook
        add_action('user_register', [$this, 'handle_new_user_registration']);
        
        // Profile completion prompts
        add_action('wp_login', [$this, 'check_profile_completion'], 10, 2);
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        add_action('wp_ajax_convert_lead_to_user', [$this, 'ajax_convert_lead_to_user']);
        add_action('wp_ajax_nopriv_quick_register', [$this, 'ajax_quick_register']);
        add_action('wp_ajax_get_conversion_stats', [$this, 'ajax_get_conversion_stats']);
        add_action('wp_ajax_send_conversion_invite', [$this, 'ajax_send_conversion_invite']);
    }
    
    /**
     * Convert a lead to a registered user
     */
    public function convert_lead_to_user(int $lead_id, array $options = []): array {
        global $wpdb;
        
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        // Get lead data
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$leads_table} WHERE id = %d",
            $lead_id
        ), ARRAY_A);
        
        if (!$lead) {
            return [
                'success' => false,
                'message' => 'Lead not found'
            ];
        }
        
        // Check if user already exists with this email
        if (email_exists($lead['email'])) {
            $existing_user_id = email_exists($lead['email']);
            
            // Link lead to existing user
            $this->link_lead_to_existing_user($lead_id, $existing_user_id);
            
            return [
                'success' => true,
                'message' => 'Lead linked to existing user account',
                'user_id' => $existing_user_id,
                'action' => 'linked'
            ];
        }
        
        // Prepare user data
        $password = $options['password'] ?? wp_generate_password(12);
        $send_password = $options['send_password'] ?? true;
        
        $userdata = [
            'user_login' => $this->generate_unique_username($lead['email']),
            'user_email' => $lead['email'],
            'user_pass' => $password,
            'first_name' => $lead['first_name'],
            'last_name' => $lead['last_name'],
            'display_name' => trim($lead['first_name'] . ' ' . $lead['last_name']),
            'role' => $options['role'] ?? 'subscriber'
        ];
        
        // Create user
        $user_id = wp_insert_user($userdata);
        
        if (is_wp_error($user_id)) {
            return [
                'success' => false,
                'message' => $user_id->get_error_message()
            ];
        }
        
        // Update lead record with user connection
        $this->link_lead_to_user($lead_id, $user_id);
        
        // Set additional user meta from lead data
        $this->populate_user_meta_from_lead($user_id, $lead);
        
        // Initialize user preferences based on lead activity
        $this->initialize_user_preferences($user_id, $lead);
        
        // Send welcome email if requested
        if ($send_password) {
            $this->send_conversion_welcome_email($user_id, $password, $lead);
        }
        
        // Track conversion
        if (class_exists('\HappyPlace\Services\UserEngagementService')) {
            $engagement_service = new \HappyPlace\Services\UserEngagementService();
            $engagement_service->track_activity($user_id, 'registration', null, null, [
                'converted_from_lead' => true,
                'lead_id' => $lead_id,
                'lead_source' => $lead['source']
            ]);
        }
        
        // Fire hooks for integrations
        do_action('hp_lead_converted_to_user', $user_id, $lead_id, $lead);
        
        $this->log("Lead converted to user: Lead ID {$lead_id} → User ID {$user_id}");
        
        return [
            'success' => true,
            'message' => 'Account created successfully',
            'user_id' => $user_id,
            'action' => 'created'
        ];
    }
    
    /**
     * Generate unique username from email
     */
    private function generate_unique_username(string $email): string {
        $username = sanitize_user(strstr($email, '@', true));
        
        // If username exists, append number
        $original_username = $username;
        $counter = 1;
        
        while (username_exists($username)) {
            $username = $original_username . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    /**
     * Link lead to existing user
     */
    private function link_lead_to_existing_user(int $lead_id, int $user_id): void {
        global $wpdb;
        
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        $wpdb->update(
            $leads_table,
            [
                'user_id' => $user_id,
                'account_status' => 'existing_user',
                'conversion_date' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $lead_id],
            ['%d', '%s', '%s', '%s'],
            ['%d']
        );
    }
    
    /**
     * Link lead to newly created user
     */
    private function link_lead_to_user(int $lead_id, int $user_id): void {
        global $wpdb;
        
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        $wpdb->update(
            $leads_table,
            [
                'user_id' => $user_id,
                'account_status' => 'converted',
                'conversion_date' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $lead_id],
            ['%d', '%s', '%s', '%s'],
            ['%d']
        );
    }
    
    /**
     * Populate user meta from lead data
     */
    private function populate_user_meta_from_lead(int $user_id, array $lead): void {
        // Contact information
        if (!empty($lead['phone'])) {
            update_user_meta($user_id, 'phone', $lead['phone']);
        }
        
        // Lead tracking data
        update_user_meta($user_id, 'original_lead_id', $lead['id']);
        update_user_meta($user_id, 'lead_source', $lead['source']);
        update_user_meta($user_id, 'conversion_date', current_time('mysql'));
        
        if (!empty($lead['lead_score'])) {
            update_user_meta($user_id, 'lead_score', $lead['lead_score']);
            update_user_meta($user_id, 'engagement_score', $lead['lead_score']);
        }
        
        // UTM tracking
        if (!empty($lead['utm_source'])) {
            update_user_meta($user_id, 'utm_source', $lead['utm_source']);
        }
        if (!empty($lead['utm_medium'])) {
            update_user_meta($user_id, 'utm_medium', $lead['utm_medium']);
        }
        if (!empty($lead['utm_campaign'])) {
            update_user_meta($user_id, 'utm_campaign', $lead['utm_campaign']);
        }
        
        // Agent assignment
        if (!empty($lead['agent_id'])) {
            update_user_meta($user_id, 'assigned_agent_id', $lead['agent_id']);
        }
    }
    
    /**
     * Initialize user preferences based on lead activity
     */
    private function initialize_user_preferences(int $user_id, array $lead): void {
        // Communication preferences
        $communication_prefs = [
            'email_notifications' => true,
            'marketing_emails' => true,
            'search_alerts' => true,
            'phone_contact' => !empty($lead['phone'])
        ];
        
        update_user_meta($user_id, 'communication_preferences', $communication_prefs);
        
        // If lead was interested in a specific listing, add insights
        if (!empty($lead['listing_id'])) {
            $listing = get_post($lead['listing_id']);
            if ($listing) {
                // Extract preferences from the listing they were interested in
                $property_type = get_field('property_type', $lead['listing_id']);
                $price = get_field('listing_price', $lead['listing_id']);
                $city = get_field('city', $lead['listing_id']);
                
                $preferences = [];
                
                if ($property_type) {
                    $preferences['preferred_property_types'] = [$property_type];
                }
                
                if ($price) {
                    $preferences['price_range_min'] = intval($price * 0.8);
                    $preferences['price_range_max'] = intval($price * 1.2);
                }
                
                if ($city) {
                    $preferences['preferred_locations'] = [$city];
                }
                
                if (!empty($preferences)) {
                    update_user_meta($user_id, 'property_preferences', $preferences);
                }
                
                // Auto-favorite the listing they inquired about
                if (class_exists('\HappyPlace\Services\UserFavoritesService')) {
                    $favorites_service = new \HappyPlace\Services\UserFavoritesService();
                    $favorites_service->add_favorite($lead['listing_id'], $user_id, [
                        'notes' => 'Originally inquired about this property'
                    ]);
                }
            }
        }
    }
    
    /**
     * Send conversion welcome email
     */
    private function send_conversion_welcome_email(int $user_id, string $password, array $lead): void {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }
        
        $subject = sprintf('Welcome to %s - Your Account is Ready!', get_bloginfo('name'));
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Welcome to <?php echo esc_html(get_bloginfo('name')); ?></title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; }
                .credentials { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0; }
                .btn { background: #007cba; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 10px 0; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 0.9em; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
                    <h2>Welcome to Your New Account!</h2>
                </div>
                
                <div class="content">
                    <p>Hi <?php echo esc_html($lead['first_name']); ?>,</p>
                    
                    <p>Thank you for your interest in our properties! We've created a personalized account for you to make your property search even better.</p>
                    
                    <div class="credentials">
                        <h3>Your Login Details:</h3>
                        <p><strong>Email:</strong> <?php echo esc_html($user->user_email); ?></p>
                        <p><strong>Password:</strong> <?php echo esc_html($password); ?></p>
                    </div>
                    
                    <p>With your new account, you can:</p>
                    <ul>
                        <li>Save your favorite properties</li>
                        <li>Create saved searches and get email alerts for new matches</li>
                        <li>Track your viewing history</li>
                        <li>Communicate directly with our agents</li>
                        <li>Access exclusive listings and market insights</li>
                    </ul>
                    
                    <?php if (!empty($lead['listing_id'])): ?>
                        <p>We've already added the property you inquired about to your favorites to get you started!</p>
                    <?php endif; ?>
                    
                    <p style="text-align: center;">
                        <a href="<?php echo wp_login_url(home_url('/my-account')); ?>" class="btn">
                            Login to Your Account
                        </a>
                    </p>
                    
                    <p>If you have any questions, please don't hesitate to reach out to us.</p>
                    
                    <p>Welcome aboard!</p>
                    <p><strong>The <?php echo esc_html(get_bloginfo('name')); ?> Team</strong></p>
                </div>
                
                <div class="footer">
                    <p>This account was created because you submitted an inquiry through our website.</p>
                    <p>If you'd prefer not to have an account, you can <a href="<?php echo home_url('/contact'); ?>">contact us</a> to remove it.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        $email_content = ob_get_clean();
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <noreply@' . parse_url(home_url(), PHP_URL_HOST) . '>'
        ];
        
        $email_sent = wp_mail($user->user_email, $subject, $email_content, $headers);
        
        if ($email_sent) {
            $this->log("Conversion welcome email sent to {$user->user_email}");
        } else {
            $this->log("Failed to send conversion welcome email to {$user->user_email}", 'error');
        }
    }
    
    /**
     * Offer account creation after lead submission
     */
    public function offer_account_creation(int $lead_id, array $lead_data): void {
        // Only offer for high-quality leads (score > 30)
        if (($lead_data['lead_score'] ?? 0) < 30) {
            return;
        }
        
        // Check if email already has an account
        if (email_exists($lead_data['email'])) {
            return;
        }
        
        // Store conversion opportunity
        update_option("conversion_opportunity_lead_{$lead_id}", [
            'lead_id' => $lead_id,
            'created_at' => current_time('mysql'),
            'lead_score' => $lead_data['lead_score'] ?? 0
        ], false);
        
        // Send conversion invitation email after a delay
        wp_schedule_single_event(time() + (2 * HOUR_IN_SECONDS), 'hp_send_conversion_invitation', [$lead_id]);
    }
    
    /**
     * Handle guest favorite attempts
     */
    public function handle_guest_favorite_attempt(): void {
        wp_send_json_error([
            'message' => 'Please create an account to save your favorite properties',
            'requires_registration' => true,
            'benefits' => [
                'Save unlimited favorite properties',
                'Get email alerts for new matches',
                'Track your viewing history',
                'Direct agent communication'
            ]
        ]);
    }
    
    /**
     * Handle guest save search attempts
     */
    public function handle_guest_save_search_attempt(): void {
        wp_send_json_error([
            'message' => 'Create a free account to save your search and get email alerts',
            'requires_registration' => true,
            'conversion_context' => 'save_search'
        ]);
    }
    
    /**
     * Handle new user registration
     */
    public function handle_new_user_registration(int $user_id): void {
        // Check if this user was converted from a lead
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }
        
        // Look for existing lead with same email
        global $wpdb;
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        $existing_lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$leads_table} WHERE email = %s AND user_id IS NULL ORDER BY created_at DESC LIMIT 1",
            $user->user_email
        ), ARRAY_A);
        
        if ($existing_lead) {
            // Link the lead to this new user
            $this->link_lead_to_user($existing_lead['id'], $user_id);
            $this->populate_user_meta_from_lead($user_id, $existing_lead);
            $this->initialize_user_preferences($user_id, $existing_lead);
            
            $this->log("New user registration linked to existing lead: User {$user_id}, Lead {$existing_lead['id']}");
        }
    }
    
    /**
     * Check profile completion on login
     */
    public function check_profile_completion(string $user_login, \WP_User $user): void {
        $completeness = $this->calculate_profile_completeness($user);
        
        if ($completeness < 50) {
            // Set notice for profile completion
            update_user_meta($user->ID, 'show_profile_completion_prompt', true);
        }
    }
    
    /**
     * Calculate profile completeness percentage
     */
    private function calculate_profile_completeness(\WP_User $user): int {
        $required_fields = [
            'first_name' => get_user_meta($user->ID, 'first_name', true),
            'last_name' => get_user_meta($user->ID, 'last_name', true),
            'phone' => get_user_meta($user->ID, 'phone', true),
            'description' => $user->description,
            'location' => get_user_meta($user->ID, 'location', true)
        ];
        
        $filled_fields = array_filter($required_fields);
        $completeness = (count($filled_fields) / count($required_fields)) * 100;
        
        return (int) $completeness;
    }
    
    /**
     * Get conversion statistics
     */
    public function get_conversion_stats(int $days = 30): array {
        global $wpdb;
        
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        // Total leads in period
        $total_leads = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$leads_table} 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Converted leads in period
        $converted_leads = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$leads_table} 
             WHERE account_status = 'converted' 
             AND conversion_date >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Linked leads (existing users)
        $linked_leads = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$leads_table} 
             WHERE account_status = 'existing_user' 
             AND conversion_date >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        $total_with_accounts = $converted_leads + $linked_leads;
        $conversion_rate = $total_leads > 0 ? ($total_with_accounts / $total_leads) * 100 : 0;
        
        return [
            'period_days' => $days,
            'total_leads' => $total_leads,
            'converted_leads' => $converted_leads,
            'linked_leads' => $linked_leads,
            'total_with_accounts' => $total_with_accounts,
            'conversion_rate' => round($conversion_rate, 2)
        ];
    }
    
    /**
     * AJAX handler for lead conversion
     */
    public function ajax_convert_lead_to_user(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $lead_id = intval($_POST['lead_id'] ?? 0);
        $send_password = $_POST['send_password'] === 'true';
        
        if (!$lead_id) {
            wp_send_json_error(['message' => 'Invalid lead ID']);
            return;
        }
        
        $result = $this->convert_lead_to_user($lead_id, [
            'send_password' => $send_password
        ]);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX handler for quick registration
     */
    public function ajax_quick_register(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $email = sanitize_email($_POST['email'] ?? '');
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        
        if (!$email || !is_email($email)) {
            wp_send_json_error(['message' => 'Valid email address required']);
            return;
        }
        
        if (email_exists($email)) {
            wp_send_json_error(['message' => 'An account with this email already exists']);
            return;
        }
        
        // Create user
        $password = wp_generate_password(12);
        $userdata = [
            'user_login' => $this->generate_unique_username($email),
            'user_email' => $email,
            'user_pass' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => trim($first_name . ' ' . $last_name),
            'role' => 'subscriber'
        ];
        
        $user_id = wp_insert_user($userdata);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()]);
            return;
        }
        
        // Auto-login the user
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        wp_send_json_success([
            'message' => 'Account created successfully!',
            'user_id' => $user_id,
            'redirect' => home_url('/my-account')
        ]);
    }
    
    /**
     * Render conversion form shortcode
     */
    public function render_conversion_form_shortcode($atts): string {
        $atts = shortcode_atts([
            'title' => 'Create Your Free Account',
            'subtitle' => 'Save properties, get alerts, and track your search',
            'show_benefits' => 'true'
        ], $atts);
        
        ob_start();
        ?>
        <div class="lead-conversion-form">
            <div class="conversion-header">
                <h3><?php echo esc_html($atts['title']); ?></h3>
                <?php if ($atts['subtitle']): ?>
                    <p class="subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if ($atts['show_benefits'] === 'true'): ?>
                <div class="conversion-benefits">
                    <ul>
                        <li><i class="fas fa-heart"></i> Save unlimited favorite properties</li>
                        <li><i class="fas fa-bell"></i> Get instant email alerts for new matches</li>
                        <li><i class="fas fa-history"></i> Track your viewing history</li>
                        <li><i class="fas fa-user-tie"></i> Direct communication with our agents</li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form class="quick-registration-form" data-nonce="<?php echo wp_create_nonce('hph_nonce'); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="first_name" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="last_name" placeholder="Last Name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="btn-text">Create Free Account</span>
                        <span class="btn-loading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Creating...
                        </span>
                    </button>
                </div>
                
                <p class="form-note">
                    By creating an account, you agree to our <a href="/privacy-policy" target="_blank">Privacy Policy</a>.
                    We'll send you a password via email to complete your account setup.
                </p>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}