<?php
/**
 * Email Notification Settings
 * 
 * Provides admin interface for configuring email notifications
 * for different form types
 * 
 * @package HappyPlace\Admin
 * @since 4.0.0
 */

namespace HappyPlace\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class EmailSettings {
    
    /**
     * Initialize email settings
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu'], 25);
        add_action('admin_init', [$this, 'init_settings']);
        add_action('wp_ajax_test_email_notification', [$this, 'test_email_notification']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu(): void {
        add_submenu_page(
            'happy-place',
            'Email Notifications',
            'Email Notifications',
            'manage_options',
            'happy-place-email-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings(): void {
        register_setting('hp_email_settings', 'hp_global_form_recipients');
        register_setting('hp_email_settings', 'hp_email_recipients_lead_capture');
        register_setting('hp_email_settings', 'hp_email_recipients_property_inquiry');
        register_setting('hp_email_settings', 'hp_email_recipients_valuation_request');
        register_setting('hp_email_settings', 'hp_email_recipients_booking_request');
        register_setting('hp_email_settings', 'hp_email_recipients_support_ticket');
        register_setting('hp_email_settings', 'hp_email_recipients_email_only');
        register_setting('hp_email_settings', 'hp_email_send_copy_to_customer');
        register_setting('hp_email_settings', 'hp_email_from_name');
        register_setting('hp_email_settings', 'hp_email_from_email');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page(): void {
        if (isset($_POST['submit'])) {
            $this->save_settings();
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><i class="dashicons dashicons-email-alt"></i> Email Notification Settings</h1>
            <p>Configure email recipients for different types of form submissions.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('hp_email_settings_nonce'); ?>
                
                <div class="hph-settings-container">
                    <div class="hph-settings-main">
                        
                        <!-- Global Settings -->
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2>📧 Global Email Settings</h2>
                            </div>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">From Name</th>
                                        <td>
                                            <input type="text" name="hp_email_from_name" 
                                                   value="<?php echo esc_attr(get_option('hp_email_from_name', get_bloginfo('name'))); ?>" 
                                                   class="regular-text" />
                                            <p class="description">Name that appears in the "From" field of notification emails.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">From Email</th>
                                        <td>
                                            <input type="email" name="hp_email_from_email" 
                                                   value="<?php echo esc_attr(get_option('hp_email_from_email', 'noreply@' . parse_url(home_url(), PHP_URL_HOST))); ?>" 
                                                   class="regular-text" />
                                            <p class="description">Email address that appears in the "From" field.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Global Recipients</th>
                                        <td>
                                            <input type="text" name="hp_global_form_recipients" 
                                                   value="<?php echo esc_attr(get_option('hp_global_form_recipients', get_option('admin_email'))); ?>" 
                                                   class="large-text" />
                                            <p class="description">Default email addresses for all form notifications (comma-separated). Used as fallback when specific recipients aren't configured.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Send Copy to Customer</th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="hp_email_send_copy_to_customer" 
                                                       value="1" <?php checked(get_option('hp_email_send_copy_to_customer')); ?> />
                                                Send a confirmation copy to the customer
                                            </label>
                                            <p class="description">When enabled, customers will receive a confirmation email after submitting a form.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Debug Email Logging</th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="hp_email_debug_logging" 
                                                       value="1" <?php checked(get_option('hp_email_debug_logging')); ?> />
                                                Enable email debug logging
                                            </label>
                                            <p class="description">When enabled, all email attempts will be logged to the debug log. Useful for troubleshooting.</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Form-Specific Recipients -->
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2>📝 Form-Specific Recipients</h2>
                            </div>
                            <div class="inside">
                                <p>Configure specific email recipients for different types of form submissions. Leave blank to use global recipients.</p>
                                <table class="form-table">
                                    <?php
                                    $form_types = [
                                        'lead_capture' => [
                                            'name' => 'General Lead Capture',
                                            'description' => 'Contact forms, newsletter signups, general inquiries',
                                            'icon' => '🎯'
                                        ],
                                        'property_inquiry' => [
                                            'name' => 'Property Inquiries',
                                            'description' => 'Property-specific questions, requests for information',
                                            'icon' => '🏠'
                                        ],
                                        'valuation_request' => [
                                            'name' => 'Valuation Requests',
                                            'description' => 'Home valuation requests, CMA requests',
                                            'icon' => '💰'
                                        ],
                                        'booking_request' => [
                                            'name' => 'Booking Requests',
                                            'description' => 'Showings, consultations, appointments',
                                            'icon' => '📅'
                                        ],
                                        'support_ticket' => [
                                            'name' => 'Support Tickets',
                                            'description' => 'Technical support, customer service',
                                            'icon' => '🎫'
                                        ],
                                        'email_only' => [
                                            'name' => 'Email Only Forms',
                                            'description' => 'Simple contact forms that only send email',
                                            'icon' => '✉️'
                                        ]
                                    ];
                                    
                                    foreach ($form_types as $key => $form_type) {
                                        $option_key = "hp_email_recipients_{$key}";
                                        $current_value = get_option($option_key, '');
                                        ?>
                                        <tr>
                                            <th scope="row">
                                                <?php echo $form_type['icon']; ?> <?php echo esc_html($form_type['name']); ?>
                                            </th>
                                            <td>
                                                <input type="text" name="<?php echo esc_attr($option_key); ?>" 
                                                       value="<?php echo esc_attr($current_value); ?>" 
                                                       class="large-text" 
                                                       placeholder="<?php echo esc_attr(get_option('hp_global_form_recipients', get_option('admin_email'))); ?>" />
                                                <p class="description"><?php echo esc_html($form_type['description']); ?> (comma-separated emails)</p>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Email Testing -->
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2>🧪 Email Testing & Diagnostics</h2>
                            </div>
                            <div class="inside">
                                <div class="email-diagnostics">
                                    <?php $this->render_email_diagnostics(); ?>
                                </div>
                                <hr>
                                <p>Test your email configuration to make sure notifications are working correctly.</p>
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Test Email</th>
                                        <td>
                                            <input type="email" id="test-email-address" 
                                                   value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" 
                                                   class="regular-text" />
                                            <button type="button" id="send-test-email" class="button button-secondary">Send Test Email</button>
                                            <p class="description">Send a test notification email to verify your settings.</p>
                                            <div id="test-email-result"></div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- SMTP Configuration Status -->
                        <?php if (is_plugin_active('wp-mail-smtp/wp_mail_smtp.php')): ?>
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2>📡 SMTP Configuration Status</h2>
                            </div>
                            <div class="inside">
                                <?php $this->render_smtp_status(); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="hph-settings-sidebar">
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2>💡 Quick Tips</h2>
                            </div>
                            <div class="inside">
                                <ul>
                                    <li><strong>Multiple Recipients:</strong> Separate email addresses with commas</li>
                                    <li><strong>Priority:</strong> Form-specific recipients override global recipients</li>
                                    <li><strong>Validation:</strong> Invalid email addresses are automatically filtered out</li>
                                    <li><strong>Response Time:</strong> Aim to respond to leads within 15 minutes for best conversion</li>
                                    <li><strong>Testing:</strong> Use the test email feature to verify delivery</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2>🚨 Email Issues?</h2>
                            </div>
                            <div class="inside">
                                <p><strong>Common Solutions:</strong></p>
                                <ul>
                                    <li><strong>Local Development:</strong> Install an SMTP plugin like "WP Mail SMTP"</li>
                                    <li><strong>Shared Hosting:</strong> Use your host's recommended SMTP settings</li>
                                    <li><strong>Gmail/Outlook:</strong> Configure SMTP with app passwords</li>
                                    <li><strong>Server Issues:</strong> Check PHP mail() function availability</li>
                                </ul>
                                <p><a href="https://wordpress.org/plugins/wp-mail-smtp/" target="_blank" class="button button-secondary">Install WP Mail SMTP Plugin</a></p>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2>📊 Email Statistics</h2>
                            </div>
                            <div class="inside">
                                <?php
                                $email_stats = $this->get_email_stats();
                                ?>
                                <p><strong>Today:</strong> <?php echo $email_stats['today']; ?> emails sent</p>
                                <p><strong>This Week:</strong> <?php echo $email_stats['week']; ?> emails sent</p>
                                <p><strong>This Month:</strong> <?php echo $email_stats['month']; ?> emails sent</p>
                                <p><strong>Last Email:</strong> <?php echo $email_stats['last_sent'] ?: 'Never'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Save Email Settings" />
                </p>
            </form>
        </div>
        
        <style>
        .hph-settings-container { display: flex; gap: 20px; }
        .hph-settings-main { flex: 2; }
        .hph-settings-sidebar { flex: 1; }
        @media (max-width: 1200px) { 
            .hph-settings-container { flex-direction: column; }
        }
        #test-email-result { margin-top: 10px; padding: 10px; border-radius: 4px; }
        #test-email-result.success { background: #d4edda; color: #155724; }
        #test-email-result.error { background: #f8d7da; color: #721c24; }
        
        /* Email Diagnostics Styles */
        .email-diagnostics-grid { display: grid; gap: 8px; margin-bottom: 15px; }
        .diagnostic-item { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 4px; font-size: 13px; }
        .diagnostic-item.success { background: #d4edda; color: #155724; }
        .diagnostic-item.warning { background: #fff3cd; color: #856404; }
        .diagnostic-item.error { background: #f8d7da; color: #721c24; }
        .diagnostic-item.info { background: #d1ecf1; color: #0c5460; }
        .diagnostic-icon { font-size: 16px; }
        .diagnostic-label { font-weight: 600; min-width: 120px; }
        .diagnostic-message { flex: 1; }
        .email-recommendations { background: #e7f3ff; border-left: 4px solid #0073aa; padding: 15px; margin-top: 15px; }
        .email-recommendations h4 { margin-top: 0; }
        .email-recommendations ul { margin-bottom: 0; }
        
        /* SMTP Status Styles */
        .smtp-status-grid { display: grid; gap: 8px; margin-bottom: 15px; }
        .smtp-status-item { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #f8f9fa; border-radius: 4px; }
        .smtp-label { font-weight: 600; min-width: 140px; color: #555; }
        .smtp-value { flex: 1; font-family: monospace; font-size: 13px; }
        .smtp-recommendation { padding: 15px; margin-top: 15px; border-radius: 4px; }
        .smtp-recommendation.success { background: #d4edda; border-left: 4px solid #28a745; }
        .smtp-recommendation.warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .smtp-recommendation h4 { margin-top: 0; }
        .integration-note { background: #e8f5e8; border-left: 4px solid #28a745; padding: 15px; margin-top: 15px; }
        .integration-note h4 { margin-top: 0; }
        .integration-note ul { margin-bottom: 0; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#send-test-email').on('click', function() {
                var email = $('#test-email-address').val();
                var button = $(this);
                var result = $('#test-email-result');
                
                if (!email) {
                    result.removeClass('success').addClass('error').text('Please enter an email address');
                    return;
                }
                
                button.prop('disabled', true).text('Sending...');
                result.removeClass('success error').text('');
                
                $.post(ajaxurl, {
                    action: 'test_email_notification',
                    email: email,
                    nonce: '<?php echo wp_create_nonce('test_email_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        result.addClass('success').text('✅ Test email sent successfully!');
                    } else {
                        result.addClass('error').text('❌ Failed to send test email: ' + response.data);
                    }
                }).fail(function() {
                    result.addClass('error').text('❌ Network error occurred');
                }).always(function() {
                    button.prop('disabled', false).text('Send Test Email');
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Save settings
     */
    private function save_settings(): void {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'hp_email_settings_nonce')) {
            return;
        }
        
        $fields = [
            'hp_global_form_recipients',
            'hp_email_recipients_lead_capture',
            'hp_email_recipients_property_inquiry',
            'hp_email_recipients_valuation_request',
            'hp_email_recipients_booking_request',
            'hp_email_recipients_support_ticket',
            'hp_email_recipients_email_only',
            'hp_email_send_copy_to_customer',
            'hp_email_debug_logging',
            'hp_email_from_name',
            'hp_email_from_email'
        ];
        
        foreach ($fields as $field) {
            $value = $_POST[$field] ?? '';
            if (in_array($field, ['hp_email_send_copy_to_customer', 'hp_email_debug_logging'])) {
                update_option($field, !empty($value));
            } else {
                update_option($field, sanitize_text_field($value));
            }
        }
    }
    
    /**
     * Test email notification
     */
    public function test_email_notification(): void {
        if (!wp_verify_nonce($_POST['nonce'], 'test_email_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        $email = sanitize_email($_POST['email']);
        if (!$email) {
            wp_send_json_error('Invalid email address');
            return;
        }
        
        // Check WordPress mail configuration first
        $mail_issues = $this->check_mail_configuration();
        if (!empty($mail_issues)) {
            wp_send_json_error('Mail configuration issues: ' . implode(', ', $mail_issues));
            return;
        }
        
        $subject = 'Test Email Notification - ' . get_bloginfo('name');
        $message = '<h2>Test Email Successful! ✅</h2>
                    <p>This is a test email from your Happy Place email notification system.</p>
                    <p><strong>Configuration Status:</strong> Working correctly</p>
                    <p><strong>Sent via:</strong> ' . $this->get_mail_method() . '</p>
                    <p><strong>Sent:</strong> ' . current_time('F j, Y g:i A T') . '</p>
                    <p><strong>Server:</strong> ' . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . '</p>
                    <p><strong>PHP Mail Function:</strong> ' . (function_exists('mail') ? 'Available' : 'Not Available') . '</p>
                    <p><strong>From Email:</strong> ' . get_option('hp_email_from_email', 'noreply@' . parse_url(home_url(), PHP_URL_HOST)) . '</p>';
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_option('hp_email_from_name', get_bloginfo('name')) . ' <' . get_option('hp_email_from_email', 'noreply@' . parse_url(home_url(), PHP_URL_HOST)) . '>'
        ];
        
        // Add error capture for wp_mail
        add_action('wp_mail_failed', [$this, 'capture_mail_error']);
        $this->last_mail_error = null;
        
        $result = wp_mail($email, $subject, $message, $headers);
        
        // Remove error capture
        remove_action('wp_mail_failed', [$this, 'capture_mail_error']);
        
        if ($result) {
            wp_send_json_success('Test email sent successfully');
        } else {
            $error_msg = 'Failed to send test email';
            if ($this->last_mail_error) {
                $error_msg .= ': ' . $this->last_mail_error;
            } else {
                $error_msg .= '. Check your server\'s mail configuration or consider using an SMTP plugin.';
            }
            wp_send_json_error($error_msg);
        }
    }
    
    /**
     * Property to store last mail error
     */
    private $last_mail_error = null;
    
    /**
     * Capture wp_mail errors
     */
    public function capture_mail_error($wp_error): void {
        $this->last_mail_error = $wp_error->get_error_message();
    }
    
    /**
     * Check mail configuration
     */
    private function check_mail_configuration(): array {
        $issues = [];
        
        // Check if PHP mail function exists
        if (!function_exists('mail')) {
            $issues[] = 'PHP mail() function is not available';
        }
        
        // Check if we're in local development
        $host = parse_url(home_url(), PHP_URL_HOST);
        if (in_array($host, ['localhost', '127.0.0.1', '::1']) || strpos($host, '.local') !== false) {
            $issues[] = 'Local development environment detected - email may not work without SMTP configuration';
        }
        
        // Check from email domain
        $from_email = get_option('hp_email_from_email', 'noreply@' . $host);
        $from_domain = substr(strrchr($from_email, "@"), 1);
        if ($from_domain !== $host && $from_domain !== 'gmail.com' && $from_domain !== 'outlook.com') {
            // This is just a warning, not a blocking issue
        }
        
        return $issues;
    }
    
    /**
     * Render email diagnostics
     */
    private function render_email_diagnostics(): void {
        $diagnostics = [];
        
        // Check PHP mail function
        $diagnostics[] = [
            'label' => 'PHP mail() function',
            'status' => function_exists('mail') ? 'available' : 'missing',
            'message' => function_exists('mail') ? 'Available' : 'Not available - SMTP plugin required'
        ];
        
        // Check environment
        $host = parse_url(home_url(), PHP_URL_HOST);
        $is_local = in_array($host, ['localhost', '127.0.0.1', '::1']) || strpos($host, '.local') !== false;
        $diagnostics[] = [
            'label' => 'Environment',
            'status' => $is_local ? 'local' : 'production',
            'message' => $is_local ? 'Local development - may need SMTP configuration' : 'Production environment'
        ];
        
        // Check SMTP plugins
        $smtp_plugins = [
            'wp-mail-smtp/wp_mail_smtp.php' => 'WP Mail SMTP',
            'easy-wp-smtp/easy-wp-smtp.php' => 'Easy WP SMTP',
            'post-smtp/postman-smtp.php' => 'Post SMTP',
            'wp-smtp/wp-smtp.php' => 'WP SMTP'
        ];
        
        $active_smtp = null;
        $smtp_configured = false;
        
        foreach ($smtp_plugins as $plugin_path => $plugin_name) {
            if (is_plugin_active($plugin_path)) {
                $active_smtp = $plugin_name;
                
                // Check if WP Mail SMTP is properly configured
                if ($plugin_path === 'wp-mail-smtp/wp_mail_smtp.php') {
                    $wp_mail_smtp_options = get_option('wp_mail_smtp', []);
                    $smtp_configured = !empty($wp_mail_smtp_options['mail']['mailer']) && 
                                     $wp_mail_smtp_options['mail']['mailer'] !== 'mail';
                }
                break;
            }
        }
        
        $smtp_status = 'none';
        $smtp_message = 'No SMTP plugin detected';
        
        if ($active_smtp) {
            if ($smtp_configured) {
                $smtp_status = 'configured';
                $smtp_message = "Active & Configured: {$active_smtp}";
            } else {
                $smtp_status = 'active';
                $smtp_message = "Active but may need configuration: {$active_smtp}";
            }
        }
        
        $diagnostics[] = [
            'label' => 'SMTP Plugin',
            'status' => $smtp_status,
            'message' => $smtp_message
        ];
        
        // Check from email configuration
        $from_email = get_option('hp_email_from_email', 'noreply@' . $host);
        $diagnostics[] = [
            'label' => 'From Email',
            'status' => is_email($from_email) ? 'valid' : 'invalid',
            'message' => $from_email
        ];
        
        // Render diagnostics
        echo '<div class="email-diagnostics-grid">';
        foreach ($diagnostics as $diagnostic) {
            $status_class = '';
            $status_icon = '';
            
            switch ($diagnostic['status']) {
                case 'available':
                case 'valid':
                case 'configured':
                    $status_class = 'success';
                    $status_icon = '✅';
                    break;
                case 'active':
                    $status_class = 'success';
                    $status_icon = '🟡'; // Yellow circle for "active but check config"
                    break;
                case 'production':
                    $status_class = 'info';
                    $status_icon = 'ℹ️';
                    break;
                case 'local':
                case 'none':
                    $status_class = 'warning';
                    $status_icon = '⚠️';
                    break;
                case 'missing':
                case 'invalid':
                default:
                    $status_class = 'error';
                    $status_icon = '❌';
                    break;
            }
            
            echo '<div class="diagnostic-item ' . esc_attr($status_class) . '">';
            echo '<span class="diagnostic-icon">' . $status_icon . '</span>';
            echo '<span class="diagnostic-label">' . esc_html($diagnostic['label']) . ':</span>';
            echo '<span class="diagnostic-message">' . esc_html($diagnostic['message']) . '</span>';
            echo '</div>';
        }
        echo '</div>';
        
        // Show recommendations if needed
        if ($is_local || !$active_smtp) {
            echo '<div class="email-recommendations">';
            echo '<h4>📋 Recommendations:</h4>';
            echo '<ul>';
            
            if ($is_local) {
                echo '<li><strong>For Local Development:</strong> Install and configure an SMTP plugin</li>';
            }
            
            if (!$active_smtp) {
                echo '<li><strong>For Better Deliverability:</strong> Use an SMTP service like Gmail, SendGrid, or Mailgun</li>';
            }
            
            echo '<li><strong>Free Option:</strong> <a href="' . admin_url('plugin-install.php?s=wp+mail+smtp&tab=search&type=term') . '">Install WP Mail SMTP plugin</a></li>';
            echo '</ul>';
            echo '</div>';
        }
    }
    
    /**
     * Get current mail sending method
     */
    private function get_mail_method(): string {
        // Check for WP Mail SMTP
        if (is_plugin_active('wp-mail-smtp/wp_mail_smtp.php')) {
            $wp_mail_smtp_options = get_option('wp_mail_smtp', []);
            if (!empty($wp_mail_smtp_options['mail']['mailer'])) {
                $mailer = $wp_mail_smtp_options['mail']['mailer'];
                switch ($mailer) {
                    case 'smtp':
                        $smtp_host = $wp_mail_smtp_options['smtp']['host'] ?? 'Unknown SMTP Server';
                        return "WP Mail SMTP ({$smtp_host})";
                    case 'gmail':
                        return 'WP Mail SMTP (Gmail)';
                    case 'outlook':
                        return 'WP Mail SMTP (Outlook)';
                    case 'amazonses':
                        return 'WP Mail SMTP (Amazon SES)';
                    case 'mailgun':
                        return 'WP Mail SMTP (Mailgun)';
                    case 'sendgrid':
                        return 'WP Mail SMTP (SendGrid)';
                    case 'postmark':
                        return 'WP Mail SMTP (Postmark)';
                    default:
                        return "WP Mail SMTP ({$mailer})";
                }
            }
            return 'WP Mail SMTP (Not Configured)';
        }
        
        // Check for other SMTP plugins
        if (is_plugin_active('easy-wp-smtp/easy-wp-smtp.php')) {
            return 'Easy WP SMTP';
        }
        
        if (is_plugin_active('post-smtp/postman-smtp.php')) {
            return 'Post SMTP';
        }
        
        // Default PHP mail
        return 'PHP mail() function';
    }
    
    /**
     * Render SMTP status information
     */
    private function render_smtp_status(): void {
        if (!is_plugin_active('wp-mail-smtp/wp_mail_smtp.php')) {
            echo '<p>WP Mail SMTP plugin not detected.</p>';
            return;
        }
        
        $wp_mail_smtp_options = get_option('wp_mail_smtp', []);
        $mailer = $wp_mail_smtp_options['mail']['mailer'] ?? 'mail';
        
        echo '<div class="smtp-status-grid">';
        
        // Mailer type
        echo '<div class="smtp-status-item">';
        echo '<span class="smtp-label">Mail Service:</span>';
        echo '<span class="smtp-value">';
        switch ($mailer) {
            case 'smtp':
                $host = $wp_mail_smtp_options['smtp']['host'] ?? 'Not configured';
                echo "SMTP ({$host})";
                break;
            case 'gmail':
                echo 'Gmail API';
                break;
            case 'outlook':
                echo 'Outlook/Hotmail';
                break;
            case 'amazonses':
                echo 'Amazon SES';
                break;
            case 'mailgun':
                echo 'Mailgun';
                break;
            case 'sendgrid':
                echo 'SendGrid';
                break;
            case 'postmark':
                echo 'Postmark';
                break;
            case 'mail':
                echo 'PHP mail() - ⚠️ Consider using SMTP';
                break;
            default:
                echo ucfirst($mailer);
        }
        echo '</span>';
        echo '</div>';
        
        // From email
        $from_email = $wp_mail_smtp_options['mail']['from_email'] ?? '';
        echo '<div class="smtp-status-item">';
        echo '<span class="smtp-label">SMTP From Email:</span>';
        echo '<span class="smtp-value">' . ($from_email ?: 'Not set') . '</span>';
        echo '</div>';
        
        // From name
        $from_name = $wp_mail_smtp_options['mail']['from_name'] ?? '';
        echo '<div class="smtp-status-item">';
        echo '<span class="smtp-label">SMTP From Name:</span>';
        echo '<span class="smtp-value">' . ($from_name ?: 'Not set') . '</span>';
        echo '</div>';
        
        echo '</div>';
        
        // Configuration recommendations
        if ($mailer === 'mail') {
            echo '<div class="smtp-recommendation warning">';
            echo '<h4>⚠️ Recommendation</h4>';
            echo '<p>You\'re using PHP mail() function. For better deliverability, consider configuring SMTP:</p>';
            echo '<a href="' . admin_url('options-general.php?page=wp-mail-smtp') . '" class="button button-primary">Configure WP Mail SMTP</a>';
            echo '</div>';
        } else {
            echo '<div class="smtp-recommendation success">';
            echo '<h4>✅ SMTP Configured</h4>';
            echo '<p>Your emails are being sent via ' . $this->get_mail_method() . '. This provides better deliverability than PHP mail().</p>';
            echo '<a href="' . admin_url('options-general.php?page=wp-mail-smtp') . '" class="button button-secondary">View SMTP Settings</a>';
            echo '</div>';
        }
        
        // Test email integration note
        echo '<div class="integration-note">';
        echo '<h4>📧 Form Integration</h4>';
        echo '<p><strong>Good news!</strong> All Happy Place form notifications automatically use your WP Mail SMTP configuration. No additional setup required.</p>';
        echo '<ul>';
        echo '<li>✅ Contact forms use SMTP</li>';
        echo '<li>✅ Property inquiries use SMTP</li>';
        echo '<li>✅ Lead notifications use SMTP</li>';
        echo '<li>✅ Customer confirmations use SMTP</li>';
        echo '</ul>';
        echo '</div>';
    }
    
    /**
     * Get email statistics
     */
    private function get_email_stats(): array {
        // This could be enhanced to track actual email sending statistics
        // For now, return placeholder data
        return [
            'today' => get_transient('hp_emails_sent_today') ?: 0,
            'week' => get_transient('hp_emails_sent_week') ?: 0,
            'month' => get_transient('hp_emails_sent_month') ?: 0,
            'last_sent' => get_option('hp_last_email_sent', '')
        ];
    }
}
