<?php
/**
 * Contact Form AJAX Handlers
 * 
 * Handles all contact-related forms including:
 * - Property contact/inquiry forms
 * - Tour scheduling requests
 * - Enhanced multi-type contact forms
 * - Mortgage calculator sharing
 * 
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle listing contact form submission
 */
if (!function_exists('handle_listing_contact_submit')) {
    add_action('wp_ajax_listing_contact_submit', 'handle_listing_contact_submit');
    add_action('wp_ajax_nopriv_listing_contact_submit', 'handle_listing_contact_submit');

    function handle_listing_contact_submit() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['listing_contact_nonce'], 'listing_contact_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Sanitize input data
    $listing_id = intval($_POST['listing_id']);
    $name = sanitize_text_field($_POST['contact_name']);
    $email = sanitize_email($_POST['contact_email']);
    $phone = sanitize_text_field($_POST['contact_phone']);
    $message = sanitize_textarea_field($_POST['contact_message']);
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error('Please fill in all required fields');
    }
    
    if (!is_email($email)) {
        wp_send_json_error('Please enter a valid email address');
    }
    
    // Get listing info
    $listing = get_post($listing_id);
    if (!$listing || $listing->post_type !== 'listing') {
        wp_send_json_error('Invalid listing');
    }
    
    // Get listing details for email
    $listing_address = hpt_get_listing_address($listing_id);
    $listing_price = hpt_get_listing_price_formatted($listing_id);
    
    // Prepare email data
    $subject = sprintf('Property Inquiry: %s', $listing_address ?: get_the_title($listing_id));
    
    $email_body = "
    New property inquiry received:
    
    Property: {$listing_address}
    Price: {$listing_price}
    Listing URL: " . get_permalink($listing_id) . "
    
    From: {$name}
    Email: {$email}
    Phone: {$phone}
    
    Message:
    {$message}
    
    ---
    Sent from: " . get_bloginfo('name') . "
    Time: " . current_time('mysql') . "
    ";
    
    // Get recipient email (listing agent or site admin)
    $agent_id = get_post_meta($listing_id, 'listing_agent', true);
    $to_email = get_option('admin_email'); // Default fallback
    
    if ($agent_id && function_exists('hpt_get_agent_email')) {
        $agent_email = hpt_get_agent_email($agent_id);
        if ($agent_email && is_email($agent_email)) {
            $to_email = $agent_email;
        }
    }
    
    // Email headers
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        'Reply-To: ' . $name . ' <' . $email . '>',
    );
    
    // Send email
    $sent = wp_mail($to_email, $subject, $email_body, $headers);
    
    if ($sent) {
        // Log the inquiry (optional)
        do_action('hph_listing_inquiry_sent', $listing_id, $email, $name);
        
        wp_send_json_success('Your message has been sent successfully!');
    } else {
        wp_send_json_error('Sorry, there was an error sending your message. Please try again.');
    }
    }
}

/**
 * Handle schedule tour request
 */
if (!function_exists('handle_schedule_tour_request')) {
    add_action('wp_ajax_schedule_tour_request', 'handle_schedule_tour_request');
    add_action('wp_ajax_nopriv_schedule_tour_request', 'handle_schedule_tour_request');

    function handle_schedule_tour_request() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hph_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Sanitize input data
    $listing_id = intval($_POST['listing_id']);
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $preferred_date = sanitize_text_field($_POST['preferred_date']);
    $preferred_time = sanitize_text_field($_POST['preferred_time']);
    $message = sanitize_textarea_field($_POST['message']);
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($preferred_date)) {
        wp_send_json_error('Please fill in all required fields');
    }
    
    if (!is_email($email)) {
        wp_send_json_error('Please enter a valid email address');
    }
    
    // Get listing info
    $listing = get_post($listing_id);
    if (!$listing || $listing->post_type !== 'listing') {
        wp_send_json_error('Invalid listing');
    }
    
    // Process tour request
    $listing_address = hpt_get_listing_address($listing_id);
    $subject = sprintf('Tour Request: %s', $listing_address ?: get_the_title($listing_id));
    
    $email_body = "
    New tour request received:
    
    Property: {$listing_address}
    Listing URL: " . get_permalink($listing_id) . "
    
    Requested by: {$name}
    Email: {$email}
    Phone: {$phone}
    
    Preferred Date: {$preferred_date}
    Preferred Time: {$preferred_time}
    
    Message:
    {$message}
    
    ---
    Sent from: " . get_bloginfo('name') . "
    Time: " . current_time('mysql') . "
    ";
    
    // Get recipient email
    $agent_id = get_post_meta($listing_id, 'listing_agent', true);
    $to_email = get_option('admin_email');
    
    if ($agent_id && function_exists('hpt_get_agent_email')) {
        $agent_email = hpt_get_agent_email($agent_id);
        if ($agent_email && is_email($agent_email)) {
            $to_email = $agent_email;
        }
    }
    
    // Email headers
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        'Reply-To: ' . $name . ' <' . $email . '>',
    );
    
    // Send email
    $sent = wp_mail($to_email, $subject, $email_body, $headers);
    
    if ($sent) {
        // Log the tour request (optional)
        do_action('hph_tour_request_sent', $listing_id, $email, $name, $preferred_date);
        
        wp_send_json_success('Your tour request has been sent successfully!');
    } else {
        wp_send_json_error('Sorry, there was an error sending your request. Please try again.');
    }
    }
}

/**
 * Enhanced Contact Form Handler - Supporting Multiple Form Types
 */
if (!function_exists('handle_hph_submit_contact_form')) {
    add_action('wp_ajax_hph_submit_contact_form', 'handle_hph_submit_contact_form');
    add_action('wp_ajax_nopriv_hph_submit_contact_form', 'handle_hph_submit_contact_form');

    function handle_hph_submit_contact_form() {
    // Verify nonce with listing-specific nonce
    $listing_id = intval($_POST['listing_id']);
    $expected_nonce = 'hph_contact_form_' . $listing_id;
    
    if (!wp_verify_nonce($_POST['nonce'], $expected_nonce)) {
        wp_send_json_error(['message' => 'Security check failed', 'code' => 'invalid_nonce']);
    }
    
    // Sanitize input data
    $form_data = array(
        'listing_id' => $listing_id,
        'agent_id' => intval($_POST['agent_id']),
        'form_type' => sanitize_text_field($_POST['form_type']),
        'contact_name' => sanitize_text_field($_POST['contact_name']),
        'contact_email' => sanitize_email($_POST['contact_email']),
        'contact_phone' => sanitize_text_field($_POST['contact_phone']),
        'contact_message' => sanitize_textarea_field($_POST['contact_message']),
        'preferred_date' => sanitize_text_field($_POST['preferred_date']),
        'preferred_time' => sanitize_text_field($_POST['preferred_time']),
        'budget_range' => sanitize_text_field($_POST['budget_range']),
        'timeline' => sanitize_text_field($_POST['timeline']),
        'prequalified' => isset($_POST['prequalified']) ? 1 : 0,
        'first_time_buyer' => isset($_POST['first_time_buyer']) ? 1 : 0,
        'newsletter_signup' => isset($_POST['newsletter_signup']) ? 1 : 0,
        'listing_title' => sanitize_text_field($_POST['listing_title']),
        'listing_url' => esc_url_raw($_POST['listing_url']),
        'listing_price' => sanitize_text_field($_POST['listing_price']),
        'listing_mls' => sanitize_text_field($_POST['listing_mls'])
    );
    
    // Validate required fields
    if (empty($form_data['contact_name']) || empty($form_data['contact_email'])) {
        wp_send_json_error(['message' => 'Name and email are required', 'code' => 'missing_required']);
    }
    
    if (!is_email($form_data['contact_email'])) {
        wp_send_json_error(['message' => 'Please enter a valid email address', 'code' => 'invalid_email']);
    }
    
    // Validate listing
    $listing = get_post($form_data['listing_id']);
    if (!$listing || $listing->post_type !== 'listing') {
        wp_send_json_error(['message' => 'Invalid listing', 'code' => 'invalid_listing']);
    }
    
    // Validate phone if provided and form type requires it
    if (!empty($form_data['contact_phone'])) {
        $cleaned_phone = preg_replace('/[^\d]/', '', $form_data['contact_phone']);
        if (strlen($cleaned_phone) < 10) {
            wp_send_json_error(['message' => 'Please enter a valid phone number', 'code' => 'invalid_phone']);
        }
    }
    
    // Get form type configuration
    $form_configs = array(
        'inquiry' => ['title' => 'Property Inquiry', 'priority' => 'normal'],
        'tour' => ['title' => 'Tour Request', 'priority' => 'high'],
        'callback' => ['title' => 'Callback Request', 'priority' => 'high'],
        'info' => ['title' => 'Information Request', 'priority' => 'normal'],
        'offer' => ['title' => 'Offer Submission', 'priority' => 'urgent']
    );
    
    $form_config = $form_configs[$form_data['form_type']] ?? $form_configs['inquiry'];
    
    // Prepare email content
    $subject = sprintf('%s: %s', $form_config['title'], $form_data['listing_title']);
    
    // Build comprehensive email body
    $email_body = sprintf("
=== %s ===

PROPERTY INFORMATION:
Property: %s
%s
%s
Listing URL: %s

CONTACT INFORMATION:
Name: %s
Email: %s
Phone: %s

",
        strtoupper($form_config['title']),
        $form_data['listing_title'],
        !empty($form_data['listing_price']) ? "Price: {$form_data['listing_price']}" : '',
        !empty($form_data['listing_mls']) ? "MLS #: {$form_data['listing_mls']}" : '',
        $form_data['listing_url'],
        $form_data['contact_name'],
        $form_data['contact_email'],
        !empty($form_data['contact_phone']) ? $form_data['contact_phone'] : 'Not provided'
    );
    
    // Add form-specific information
    if ($form_data['form_type'] === 'tour' && (!empty($form_data['preferred_date']) || !empty($form_data['preferred_time']))) {
        $email_body .= "TOUR PREFERENCES:\n";
        if (!empty($form_data['preferred_date'])) {
            $email_body .= "Preferred Date: " . date('F j, Y', strtotime($form_data['preferred_date'])) . "\n";
        }
        if (!empty($form_data['preferred_time'])) {
            $email_body .= "Preferred Time: {$form_data['preferred_time']}\n";
        }
        $email_body .= "\n";
    }
    
    // Add purchase information if provided
    if (!empty($form_data['budget_range']) || !empty($form_data['timeline'])) {
        $email_body .= "PURCHASE INFORMATION:\n";
        if (!empty($form_data['budget_range'])) {
            $email_body .= "Budget Range: {$form_data['budget_range']}\n";
        }
        if (!empty($form_data['timeline'])) {
            $email_body .= "Timeline: {$form_data['timeline']}\n";
        }
        $email_body .= "\n";
    }
    
    // Add buyer qualifications
    if ($form_data['prequalified'] || $form_data['first_time_buyer']) {
        $email_body .= "BUYER QUALIFICATIONS:\n";
        if ($form_data['prequalified']) {
            $email_body .= "✓ Pre-qualified for mortgage\n";
        }
        if ($form_data['first_time_buyer']) {
            $email_body .= "✓ First-time home buyer\n";
        }
        $email_body .= "\n";
    }
    
    // Add message
    if (!empty($form_data['contact_message'])) {
        $email_body .= "MESSAGE:\n{$form_data['contact_message']}\n\n";
    }
    
    // Add footer
    $email_body .= "---\n";
    $email_body .= "Form Type: {$form_config['title']}\n";
    $email_body .= "Priority: {$form_config['priority']}\n";
    $email_body .= "Submitted: " . current_time('F j, Y g:i A T') . "\n";
    $email_body .= "From: " . get_bloginfo('name') . "\n";
    $email_body .= "User Agent: " . sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "\n";
    
    // Determine recipient email
    $to_email = get_option('admin_email'); // Default fallback
    
    // Try to get agent email first
    if ($form_data['agent_id'] && function_exists('hpt_get_agent_email')) {
        $agent_email = hpt_get_agent_email($form_data['agent_id']);
        if ($agent_email && is_email($agent_email)) {
            $to_email = $agent_email;
        }
    } elseif (function_exists('hpt_get_listing_agent_id')) {
        // Try to get agent from listing
        $listing_agent_id = hpt_get_listing_agent_id($form_data['listing_id']);
        if ($listing_agent_id && function_exists('hpt_get_agent_email')) {
            $agent_email = hpt_get_agent_email($listing_agent_id);
            if ($agent_email && is_email($agent_email)) {
                $to_email = $agent_email;
            }
        }
    }
    
    // Email headers with priority
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        'Reply-To: ' . $form_data['contact_name'] . ' <' . $form_data['contact_email'] . '>',
    );
    
    // Add priority header for urgent requests
    if ($form_config['priority'] === 'urgent') {
        $headers[] = 'X-Priority: 1';
        $headers[] = 'X-MSMail-Priority: High';
    } elseif ($form_config['priority'] === 'high') {
        $headers[] = 'X-Priority: 2';
    }
    
    // Send email
    $sent = wp_mail($to_email, $subject, $email_body, $headers);
    
    if ($sent) {
        // Log the inquiry to database if lead capture is available
        if (function_exists('hpt_create_lead')) {
            $lead_data = array(
                'listing_id' => $form_data['listing_id'],
                'agent_id' => $form_data['agent_id'],
                'name' => $form_data['contact_name'],
                'email' => $form_data['contact_email'],
                'phone' => $form_data['contact_phone'],
                'message' => $form_data['contact_message'],
                'source' => 'listing_' . $form_data['form_type'],
                'form_data' => json_encode($form_data)
            );
            hpt_create_lead($lead_data);
        }
        
        // Update listing inquiry count
        $inquiry_count = get_post_meta($form_data['listing_id'], 'inquiry_count', true) ?: 0;
        update_post_meta($form_data['listing_id'], 'inquiry_count', $inquiry_count + 1);
        
        // Add to newsletter if requested
        if ($form_data['newsletter_signup'] && function_exists('hpt_add_newsletter_subscriber')) {
            hpt_add_newsletter_subscriber($form_data['contact_email'], $form_data['contact_name']);
        }
        
        // Fire action hook for integrations
        do_action('hph_contact_form_submitted', $form_data, $form_config);
        
        wp_send_json_success([
            'message' => 'Your message has been sent successfully! We will contact you soon.',
            'form_type' => $form_data['form_type']
        ]);
    } else {
        wp_send_json_error([
            'message' => 'Sorry, there was an error sending your message. Please try again or contact us directly.',
            'code' => 'email_failed'
        ]);
    }
    }
}

/**
 * Handle mortgage calculator sharing
 */
if (!function_exists('handle_hph_share_mortgage_calculation')) {
    add_action('wp_ajax_hph_share_mortgage_calculation', 'handle_hph_share_mortgage_calculation');
    add_action('wp_ajax_nopriv_hph_share_mortgage_calculation', 'handle_hph_share_mortgage_calculation');

    function handle_hph_share_mortgage_calculation() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hph_mortgage_calc_' . intval($_POST['listing_id']))) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    $calculation_data = array(
        'listing_id' => intval($_POST['listing_id']),
        'home_price' => floatval($_POST['home_price']),
        'down_payment' => floatval($_POST['down_payment']),
        'interest_rate' => floatval($_POST['interest_rate']),
        'loan_term' => intval($_POST['loan_term']),
        'monthly_payment' => floatval($_POST['monthly_payment']),
        'created_at' => current_time('mysql')
    );
    
    // Store calculation temporarily (24 hours)
    $share_id = wp_generate_uuid4();
    set_transient('hph_mortgage_calc_' . $share_id, $calculation_data, 24 * HOUR_IN_SECONDS);
    
    // Generate share URL
    $share_url = add_query_arg([
        'calc_share' => $share_id
    ], get_permalink($calculation_data['listing_id']));
    
    wp_send_json_success([
        'share_url' => $share_url,
        'share_id' => $share_id
    ]);
    }
}
