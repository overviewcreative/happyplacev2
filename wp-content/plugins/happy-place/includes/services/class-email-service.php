<?php
/**
 * Email Service
 *
 * Handles all email configuration and delivery for Happy Place
 * Migrated from theme to establish proper plugin-theme separation
 *
 * @package HappyPlace
 * @subpackage Services
 * @since 4.3.0 - Migrated from theme
 */

namespace HappyPlace\Services;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email Service Class
 *
 * Handles SMTP configuration, email delivery, and testing
 */
class EmailService {

    /**
     * Service initialization
     */
    public function init() {
        // Configure SMTP settings
        add_action('phpmailer_init', [$this, 'configure_smtp']);

        // Set proper FROM address
        add_filter('wp_mail_from', [$this, 'set_from_email']);
        add_filter('wp_mail_from_name', [$this, 'set_from_name']);

        // Enhance HTML emails
        add_filter('wp_mail_content_type', [$this, 'set_html_content_type']);

        // Admin hooks for email testing
        add_action('wp_ajax_hph_test_email', [$this, 'handle_test_email_ajax']);
    }

    /**
     * Configure SMTP settings for production
     */
    public function configure_smtp($phpmailer) {
        // Only configure if not in local environment
        if (defined('WP_ENV') && WP_ENV === 'development') {
            return;
        }

        $phpmailer->isSMTP();

        // SendGrid Configuration (recommended)
        if (defined('SENDGRID_API_KEY')) {
            $phpmailer->Host = 'smtp.sendgrid.net';
            $phpmailer->Port = 587;
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = 'apikey';
            $phpmailer->Password = SENDGRID_API_KEY;
            $phpmailer->SMTPSecure = 'tls';
        }
        // Mailgun Configuration (alternative)
        elseif (defined('MAILGUN_USERNAME') && defined('MAILGUN_PASSWORD')) {
            $phpmailer->Host = 'smtp.mailgun.org';
            $phpmailer->Port = 587;
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = MAILGUN_USERNAME;
            $phpmailer->Password = MAILGUN_PASSWORD;
            $phpmailer->SMTPSecure = 'tls';
        }
        // Gmail/Google Workspace (for smaller operations)
        elseif (defined('GOOGLE_APP_PASSWORD')) {
            $phpmailer->Host = 'smtp.gmail.com';
            $phpmailer->Port = 587;
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = get_option('admin_email');
            $phpmailer->Password = GOOGLE_APP_PASSWORD;
            $phpmailer->SMTPSecure = 'tls';
        }
        // Amazon SES Configuration
        elseif (defined('AWS_SES_ACCESS_KEY') && defined('AWS_SES_SECRET_KEY')) {
            $phpmailer->Host = 'email-smtp.' . (defined('AWS_SES_REGION') ? AWS_SES_REGION : 'us-east-1') . '.amazonaws.com';
            $phpmailer->Port = 587;
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = AWS_SES_ACCESS_KEY;
            $phpmailer->Password = AWS_SES_SECRET_KEY;
            $phpmailer->SMTPSecure = 'tls';
        }

        // Enhanced debugging for production
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $phpmailer->SMTPDebug = 1;
            $phpmailer->Debugoutput = 'error_log';
        }
    }

    /**
     * Set professional FROM email address
     */
    public function set_from_email($from_email) {
        $domain = parse_url(home_url(), PHP_URL_HOST);
        return 'noreply@' . $domain;
    }

    /**
     * Set professional FROM name
     */
    public function set_from_name($from_name) {
        return get_bloginfo('name') . ' - Real Estate';
    }

    /**
     * Set HTML content type for better email formatting
     */
    public function set_html_content_type() {
        return 'text/html';
    }

    /**
     * Test email functionality
     */
    public function test_email_delivery($test_email = null) {
        if (!$test_email) {
            $test_email = get_option('admin_email');
        }

        $subject = 'Happy Place Email Test - ' . date('Y-m-d H:i:s');
        $message = $this->get_test_email_template();

        $sent = wp_mail($test_email, $subject, $message);

        if ($sent) {
            error_log("Test email sent successfully to: $test_email");
            return [
                'success' => true,
                'message' => "Test email sent successfully to: $test_email"
            ];
        } else {
            error_log("Test email failed to: $test_email");
            return [
                'success' => false,
                'message' => "Test email failed to: $test_email"
            ];
        }
    }

    /**
     * Handle AJAX email test request
     */
    public function handle_test_email_ajax() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $test_email = sanitize_email($_POST['test_email'] ?? '');
        if (!$test_email) {
            $test_email = get_option('admin_email');
        }

        $result = $this->test_email_delivery($test_email);

        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Get test email template
     */
    private function get_test_email_template() {
        $smtp_provider = $this->get_configured_smtp_provider();

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Email Test</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #2c5aa0;">Happy Place Email System Test</h2>

                <p>This is a test email to verify your email configuration is working properly.</p>

                <div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #2c5aa0; margin: 20px 0;">
                    <strong>Test Details:</strong><br>
                    Date: ' . date('Y-m-d H:i:s') . '<br>
                    Server: ' . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . '<br>
                    WordPress Version: ' . get_bloginfo('version') . '<br>
                    SMTP Provider: ' . $smtp_provider . '
                </div>

                <p>If you received this email, your email configuration is working correctly!</p>

                <div style="background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3 style="margin-top: 0; color: #2d5016;">✅ Email System Status: OPERATIONAL</h3>
                    <p style="margin-bottom: 0;">Your Happy Place email system is properly configured and ready for production use.</p>
                </div>

                <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">

                <p style="font-size: 12px; color: #666;">
                    This test email was sent from your Happy Place real estate website plugin email service.
                </p>
            </div>
        </body>
        </html>';
    }

    /**
     * Get configured SMTP provider name
     */
    private function get_configured_smtp_provider() {
        if (defined('SENDGRID_API_KEY')) {
            return 'SendGrid';
        } elseif (defined('MAILGUN_USERNAME') && defined('MAILGUN_PASSWORD')) {
            return 'Mailgun';
        } elseif (defined('GOOGLE_APP_PASSWORD')) {
            return 'Gmail/Google Workspace';
        } elseif (defined('AWS_SES_ACCESS_KEY') && defined('AWS_SES_SECRET_KEY')) {
            return 'Amazon SES';
        } else {
            return 'Default WordPress Mail';
        }
    }

    /**
     * Send formatted real estate email
     */
    public function send_real_estate_email($to, $subject, $message, $lead_data = []) {
        // Add real estate specific email formatting
        $formatted_message = $this->format_real_estate_email($message, $lead_data);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->set_from_name('') . ' <' . $this->set_from_email('') . '>'
        ];

        return wp_mail($to, $subject, $formatted_message, $headers);
    }

    /**
     * Format email with real estate branding
     */
    private function format_real_estate_email($message, $lead_data = []) {
        $site_name = get_bloginfo('name');
        $site_url = home_url();

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>$site_name</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;'>
            <div style='max-width: 600px; margin: 0 auto; background: #fff;'>
                <div style='background: #2c5aa0; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>$site_name</h1>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>Your Real Estate Partner</p>
                </div>

                <div style='padding: 30px 20px;'>
                    $message
                </div>

                <div style='background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eee;'>
                    <p style='margin: 0; font-size: 14px; color: #666;'>
                        <a href='$site_url' style='color: #2c5aa0; text-decoration: none;'>$site_name</a> |
                        Your Trusted Real Estate Professionals
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }
}