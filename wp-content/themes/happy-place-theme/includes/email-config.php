<?php
/**
 * Email Configuration for Happy Place Real Estate
 * Configure SMTP settings for production email delivery
 * 
 * @package HappyPlaceTheme
 */

// Don't load directly
if (!defined('ABSPATH')) {
    exit;
}

class HPH_Email_Config {
    
    public static function init() {
        // Configure SMTP settings
        add_action('phpmailer_init', [__CLASS__, 'configure_smtp']);
        
        // Set proper FROM address
        add_filter('wp_mail_from', [__CLASS__, 'set_from_email']);
        add_filter('wp_mail_from_name', [__CLASS__, 'set_from_name']);
        
        // Enhance HTML emails
        add_filter('wp_mail_content_type', [__CLASS__, 'set_html_content_type']);
    }
    
    /**
     * Configure SMTP settings for production
     */
    public static function configure_smtp($phpmailer) {
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
        
        // Enhanced debugging for production
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $phpmailer->SMTPDebug = 1;
            $phpmailer->Debugoutput = 'error_log';
        }
    }
    
    /**
     * Set professional FROM email address
     */
    public static function set_from_email($from_email) {
        $domain = parse_url(home_url(), PHP_URL_HOST);
        return 'noreply@' . $domain;
    }
    
    /**
     * Set professional FROM name
     */
    public static function set_from_name($from_name) {
        return get_bloginfo('name') . ' - Real Estate';
    }
    
    /**
     * Set HTML content type for better email formatting
     */
    public static function set_html_content_type() {
        return 'text/html';
    }
    
    /**
     * Test email functionality
     */
    public static function test_email_delivery($test_email = null) {
        if (!$test_email) {
            $test_email = get_option('admin_email');
        }
        
        $subject = 'Happy Place Email Test - ' . date('Y-m-d H:i:s');
        $message = self::get_test_email_template();
        
        $sent = wp_mail($test_email, $subject, $message);
        
        if ($sent) {
            error_log("Test email sent successfully to: $test_email");
            return true;
        } else {
            error_log("Test email failed to: $test_email");
            return false;
        }
    }
    
    /**
     * Get test email template
     */
    private static function get_test_email_template() {
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
                    Server: ' . $_SERVER['HTTP_HOST'] . '<br>
                    WordPress Version: ' . get_bloginfo('version') . '
                </div>
                
                <p>If you received this email, your email configuration is working correctly!</p>
                
                <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
                
                <p style="font-size: 12px; color: #666;">
                    This test email was sent from your Happy Place real estate website.
                </p>
            </div>
        </body>
        </html>';
    }
}

// Initialize email configuration
HPH_Email_Config::init();
