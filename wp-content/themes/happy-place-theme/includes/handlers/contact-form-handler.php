<?php
/**
 * Contact Form Handler
 * 
 * Basic contact form processing functionality
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class HPH_Contact_Form_Handler {
    
    public function __construct() {
        add_action('wp_ajax_hph_contact_form', array($this, 'process_contact_form'));
        add_action('wp_ajax_nopriv_hph_contact_form', array($this, 'process_contact_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_contact_scripts'));
    }
    
    /**
     * Enqueue contact form scripts
     */
    public function enqueue_contact_scripts() {
        if (is_page_template('page-contact.php') || is_page('contact')) {
            wp_enqueue_script(
                'hph-contact-form',
                get_template_directory_uri() . '/assets/js/contact-form.js',
                array('jquery'),
                HPH_VERSION,
                true
            );
            
            wp_localize_script('hph-contact-form', 'hphContact', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hph_contact_nonce'),
                'messages' => array(
                    'sending' => __('Sending...', 'happy-place-theme'),
                    'success' => __('Thank you! Your message has been sent successfully.', 'happy-place-theme'),
                    'error' => __('Sorry, there was an error sending your message. Please try again.', 'happy-place-theme'),
                    'validation' => __('Please fill in all required fields.', 'happy-place-theme')
                )
            ));
        }
    }
    
    /**
     * Process contact form submission
     */
    public function process_contact_form() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_contact_nonce')) {
            wp_die('Security check failed');
        }
        
        // Sanitize form data
        $form_data = array(
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'inquiry_type' => sanitize_text_field($_POST['inquiry_type']),
            'budget' => sanitize_text_field($_POST['budget']),
            'timeline' => sanitize_text_field($_POST['timeline']),
            'message' => sanitize_textarea_field($_POST['message']),
            'newsletter' => isset($_POST['newsletter']) ? 1 : 0
        );
        
        // Validate required fields
        $required_fields = array('first_name', 'last_name', 'email', 'inquiry_type', 'message');
        $validation_errors = array();
        
        foreach ($required_fields as $field) {
            if (empty($form_data[$field])) {
                $validation_errors[] = $field;
            }
        }
        
        if (!empty($validation_errors)) {
            wp_send_json_error(array(
                'message' => 'Please fill in all required fields',
                'fields' => $validation_errors
            ));
        }
        
        // Validate email
        if (!is_email($form_data['email'])) {
            wp_send_json_error(array(
                'message' => 'Please enter a valid email address',
                'fields' => array('email')
            ));
        }
        
        // Send email notification
        $email_sent = $this->send_contact_email($form_data);
        
        if ($email_sent) {
            // Save to database (optional)
            $this->save_contact_submission($form_data);
            
            wp_send_json_success(array(
                'message' => 'Thank you! Your message has been sent successfully.'
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Sorry, there was an error sending your message. Please try again.'
            ));
        }
    }
    
    /**
     * Send contact email notification
     */
    private function send_contact_email($form_data) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf('[%s] New Contact Form Submission - %s', $site_name, $form_data['inquiry_type']);
        
        $message = "New contact form submission:\n\n";
        $message .= "Name: {$form_data['first_name']} {$form_data['last_name']}\n";
        $message .= "Email: {$form_data['email']}\n";
        $message .= "Phone: {$form_data['phone']}\n";
        $message .= "Inquiry Type: {$form_data['inquiry_type']}\n";
        $message .= "Budget: {$form_data['budget']}\n";
        $message .= "Timeline: {$form_data['timeline']}\n";
        $message .= "Newsletter: " . ($form_data['newsletter'] ? 'Yes' : 'No') . "\n\n";
        $message .= "Message:\n{$form_data['message']}\n\n";
        $message .= "---\n";
        $message .= "Submitted on: " . current_time('mysql') . "\n";
        $message .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $form_data['first_name'] . ' ' . $form_data['last_name'] . ' <' . $form_data['email'] . '>'
        );
        
        // Send to admin
        $admin_sent = wp_mail($admin_email, $subject, $message, $headers);
        
        // Send confirmation to user
        $user_subject = sprintf('Thank you for contacting %s', $site_name);
        $user_message = "Dear {$form_data['first_name']},\n\n";
        $user_message .= "Thank you for contacting us! We have received your message and will get back to you within 24 hours.\n\n";
        $user_message .= "Here's a copy of your message:\n\n";
        $user_message .= "Inquiry Type: {$form_data['inquiry_type']}\n";
        $user_message .= "Message: {$form_data['message']}\n\n";
        $user_message .= "Best regards,\n";
        $user_message .= "The {$site_name} Team\n";
        
        wp_mail($form_data['email'], $user_subject, $user_message, $headers);
        
        return $admin_sent;
    }
    
    /**
     * Save contact submission to database
     */
    private function save_contact_submission($form_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hph_contact_submissions';
        
        // Create table if it doesn't exist
        $this->create_submissions_table();
        
        return $wpdb->insert(
            $table_name,
            array(
                'first_name' => $form_data['first_name'],
                'last_name' => $form_data['last_name'],
                'email' => $form_data['email'],
                'phone' => $form_data['phone'],
                'inquiry_type' => $form_data['inquiry_type'],
                'budget' => $form_data['budget'],
                'timeline' => $form_data['timeline'],
                'message' => $form_data['message'],
                'newsletter' => $form_data['newsletter'],
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'submitted_at' => current_time('mysql')
            ),
            array(
                '%s', '%s', '%s', '%s', '%s', 
                '%s', '%s', '%s', '%d', '%s', 
                '%s', '%s'
            )
        );
    }
    
    /**
     * Create contact submissions table
     */
    private function create_submissions_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hph_contact_submissions';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20),
            inquiry_type varchar(50) NOT NULL,
            budget varchar(50),
            timeline varchar(50),
            message text NOT NULL,
            newsletter tinyint(1) DEFAULT 0,
            ip_address varchar(45),
            user_agent text,
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'new',
            PRIMARY KEY (id),
            KEY email (email),
            KEY submitted_at (submitted_at),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get contact submissions (for admin use)
     */
    public function get_submissions($limit = 50, $status = 'all') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hph_contact_submissions';
        
        $where = '';
        if ($status !== 'all') {
            $where = $wpdb->prepare("WHERE status = %s", $status);
        }
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name $where ORDER BY submitted_at DESC LIMIT %d",
                $limit
            )
        );
    }
}

// Initialize the contact form handler
new HPH_Contact_Form_Handler();