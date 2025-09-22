<?php
/**
 * Email Bridge Functions
 *
 * Bridge functions to connect theme to plugin EmailService
 * Provides backward compatibility and clean interface
 *
 * @package HappyPlaceTheme
 * @subpackage Bridge
 * @since 4.3.0 - Created for email delegation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test email delivery via plugin service
 */
function hpt_test_email_delivery($test_email = null) {
    if (class_exists('HappyPlace\Services\EmailService')) {
        $email_service = new HappyPlace\Services\EmailService();
        return $email_service->test_email_delivery($test_email);
    }

    return [
        'success' => false,
        'message' => 'Email service not available'
    ];
}

/**
 * Send real estate formatted email via plugin service
 */
function hpt_send_real_estate_email($to, $subject, $message, $lead_data = []) {
    if (class_exists('HappyPlace\Services\EmailService')) {
        $email_service = new HappyPlace\Services\EmailService();
        return $email_service->send_real_estate_email($to, $subject, $message, $lead_data);
    }

    // Fallback to standard wp_mail
    return wp_mail($to, $subject, $message);
}

/**
 * Check if email service is properly configured
 */
function hpt_is_email_configured() {
    return (
        defined('SENDGRID_API_KEY') ||
        (defined('MAILGUN_USERNAME') && defined('MAILGUN_PASSWORD')) ||
        defined('GOOGLE_APP_PASSWORD') ||
        (defined('AWS_SES_ACCESS_KEY') && defined('AWS_SES_SECRET_KEY'))
    );
}

/**
 * Get configured email provider name
 */
function hpt_get_email_provider() {
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