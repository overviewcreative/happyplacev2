<?php
/**
 * Unified Lead Handler
 * 
 * Single, consistent system for handling all lead submissions
 * across different forms (agent contact, general inquiry, RSVP, etc.)
 * 
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class HPH_Unified_Lead_Handler {
    
    /**
     * Lead types and their configurations
     */
    private static $lead_types = [
        'agent_contact' => [
            'source' => 'agent_contact_form',
            'default_status' => 'new',
            'default_priority' => 'normal',
            'requires_agent' => true,
            'email_template' => 'agent_contact'
        ],
        'general_inquiry' => [
            'source' => 'general_inquiry_form',
            'default_status' => 'new',
            'default_priority' => 'normal',
            'requires_agent' => false,
            'email_template' => 'general_inquiry'
        ],
        'listing_inquiry' => [
            'source' => 'listing_inquiry_form',
            'default_status' => 'new',
            'default_priority' => 'high',
            'requires_agent' => false,
            'email_template' => 'listing_inquiry'
        ],
        'rsvp' => [
            'source' => 'rsvp_form',
            'default_status' => 'confirmed',
            'default_priority' => 'normal',
            'requires_agent' => false,
            'email_template' => 'rsvp_confirmation'
        ]
    ];
    
    /**
     * Initialize the handler
     */
    public static function init() {
        // Register unified AJAX handlers
        add_action('wp_ajax_hph_submit_lead', [__CLASS__, 'handle_lead_submission']);
        add_action('wp_ajax_nopriv_hph_submit_lead', [__CLASS__, 'handle_lead_submission']);
        
        // Keep backward compatibility for existing forms
        add_action('wp_ajax_send_agent_contact', [__CLASS__, 'handle_agent_contact_legacy']);
        add_action('wp_ajax_nopriv_send_agent_contact', [__CLASS__, 'handle_agent_contact_legacy']);
        
        add_action('wp_ajax_send_general_inquiry', [__CLASS__, 'handle_general_inquiry_legacy']);
        add_action('wp_ajax_nopriv_send_general_inquiry', [__CLASS__, 'handle_general_inquiry_legacy']);
        
        // Log initialization
        error_log('HPH_Unified_Lead_Handler initialized');
    }
    
    /**
     * Main lead submission handler
     */
    public static function handle_lead_submission() {
        try {
            error_log('Unified lead submission received');
            
            // Verify nonce
            $nonce_action = sanitize_text_field($_POST['nonce_action'] ?? 'hph_lead_nonce');
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $nonce_action)) {
                error_log('Lead submission: Nonce verification failed');
                wp_die(json_encode(['success' => false, 'message' => 'Security check failed']));
            }
            
            // Get and validate lead type
            $lead_type = sanitize_text_field($_POST['lead_type'] ?? 'general_inquiry');
            if (!isset(self::$lead_types[$lead_type])) {
                error_log('Lead submission: Invalid lead type: ' . $lead_type);
                wp_die(json_encode(['success' => false, 'message' => 'Invalid lead type']));
            }
            
            $config = self::$lead_types[$lead_type];
            
            // Extract and sanitize form data
            $form_data = self::extract_form_data($_POST);
            
            // Validate required fields
            $validation_result = self::validate_lead_data($form_data, $config);
            if (!$validation_result['valid']) {
                wp_die(json_encode([
                    'success' => false, 
                    'message' => $validation_result['message']
                ]));
            }
            
            // Create lead in database
            $lead_id = self::create_lead($form_data, $config);
            
            if (!$lead_id) {
                error_log('Failed to create lead in database');
                wp_die(json_encode([
                    'success' => false, 
                    'message' => 'Failed to save lead. Please try again.'
                ]));
            }
            
            // Send notification emails
            $email_sent = self::send_notifications($form_data, $config);
            
            // Log success
            error_log("✅ Lead created successfully. ID: {$lead_id}, Type: {$lead_type}");
            
            wp_die(json_encode([
                'success' => true,
                'message' => 'Your submission has been received successfully!',
                'lead_id' => $lead_id
            ]));
            
        } catch (Exception $e) {
            error_log('Lead submission error: ' . $e->getMessage());
            wp_die(json_encode([
                'success' => false, 
                'message' => 'An error occurred. Please try again.'
            ]));
        }
    }
    
    /**
     * Extract and sanitize form data
     */
    private static function extract_form_data($post_data) {
        $form_data = [];
        
        // Basic contact information
        $form_data['name'] = sanitize_text_field($post_data['name'] ?? '');
        $form_data['first_name'] = sanitize_text_field($post_data['first_name'] ?? '');
        $form_data['last_name'] = sanitize_text_field($post_data['last_name'] ?? '');
        $form_data['email'] = sanitize_email($post_data['email'] ?? '');
        $form_data['phone'] = sanitize_text_field($post_data['phone'] ?? '');
        $form_data['message'] = sanitize_textarea_field($post_data['message'] ?? '');
        
        // If only 'name' is provided, split it
        if (!empty($form_data['name']) && empty($form_data['first_name'])) {
            $name_parts = explode(' ', trim($form_data['name']), 2);
            $form_data['first_name'] = $name_parts[0];
            $form_data['last_name'] = isset($name_parts[1]) ? $name_parts[1] : '';
        }
        
        // Context information
        $form_data['listing_id'] = intval($post_data['listing_id'] ?? 0);
        $form_data['agent_id'] = intval($post_data['agent_id'] ?? 0);
        $form_data['property_id'] = intval($post_data['property_id'] ?? 0);
        $form_data['open_house_id'] = intval($post_data['open_house_id'] ?? 0);
        
        // Additional fields
        $form_data['inquiry_type'] = sanitize_text_field($post_data['inquiry_type'] ?? '');
        $form_data['budget'] = sanitize_text_field($post_data['budget'] ?? '');
        $form_data['timeline'] = sanitize_text_field($post_data['timeline'] ?? '');
        $form_data['preferred_contact'] = sanitize_text_field($post_data['preferred_contact'] ?? 'email');
        
        // Tracking data
        $form_data['source_url'] = esc_url_raw($_SERVER['HTTP_REFERER'] ?? '');
        $form_data['ip_address'] = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
        $form_data['user_agent'] = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
        $form_data['utm_source'] = sanitize_text_field($post_data['utm_source'] ?? '');
        $form_data['utm_medium'] = sanitize_text_field($post_data['utm_medium'] ?? '');
        $form_data['utm_campaign'] = sanitize_text_field($post_data['utm_campaign'] ?? '');
        
        return $form_data;
    }
    
    /**
     * Validate lead data
     */
    private static function validate_lead_data($form_data, $config) {
        $errors = [];
        
        // Check required fields
        if (empty($form_data['first_name'])) {
            $errors[] = 'First name is required';
        }
        
        if (empty($form_data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!is_email($form_data['email'])) {
            $errors[] = 'Valid email address is required';
        }
        
        if (empty($form_data['message'])) {
            $errors[] = 'Message is required';
        }
        
        // Check agent requirement
        if ($config['requires_agent'] && empty($form_data['agent_id'])) {
            $errors[] = 'Agent information is required';
        }
        
        if (!empty($errors)) {
            return [
                'valid' => false,
                'message' => implode(', ', $errors)
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Create lead in database
     */
    private static function create_lead($form_data, $config) {
        global $wpdb;
        
        // Try database insert first
        $table_name = $wpdb->prefix . 'hp_leads';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if ($table_exists) {
            $lead_data = [
                'first_name' => $form_data['first_name'],
                'last_name' => $form_data['last_name'],
                'email' => $form_data['email'],
                'phone' => $form_data['phone'] ?: null,
                'message' => $form_data['message'],
                'source' => $config['source'],
                'source_url' => $form_data['source_url'] ?: null,
                'listing_id' => $form_data['listing_id'] ?: null,
                'agent_id' => $form_data['agent_id'] ?: null,
                'status' => $config['default_status'],
                'priority' => $config['default_priority'],
                'ip_address' => $form_data['ip_address'] ?: null,
                'user_agent' => $form_data['user_agent'] ?: null,
                'referrer' => $form_data['source_url'] ?: null,
                'utm_source' => $form_data['utm_source'] ?: null,
                'utm_medium' => $form_data['utm_medium'] ?: null,
                'utm_campaign' => $form_data['utm_campaign'] ?: null,
                'assigned_to' => $form_data['agent_id'] ?: null,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ];
            
            $insert_result = $wpdb->insert(
                $table_name,
                $lead_data,
                [
                    '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                    '%d', '%d', '%s', '%s', '%s', '%s', '%s',
                    '%s', '%s', '%s', '%s', '%d', '%s', '%s'
                ]
            );
            
            if ($insert_result !== false) {
                $lead_id = $wpdb->insert_id;
                error_log("✅ Direct database insert successful! Lead ID: {$lead_id}");
                
                // Trigger FollowUp Boss sync
                self::trigger_followup_boss_sync($lead_id, $lead_data, $form_data);
                
                return $lead_id;
            } else {
                error_log("❌ Direct database insert failed: " . $wpdb->last_error);
            }
        }
        
        // Fallback to LeadService
        try {
            if (class_exists('\HappyPlace\Services\LeadService')) {
                $lead_service = new \HappyPlace\Services\LeadService();
                $lead_service->init();
                
                $lead_data = [
                    'first_name' => $form_data['first_name'],
                    'last_name' => $form_data['last_name'],
                    'email' => $form_data['email'],
                    'phone' => $form_data['phone'] ?: null,
                    'message' => $form_data['message'],
                    'source' => $config['source'],
                    'source_url' => $form_data['source_url'] ?: null,
                    'listing_id' => $form_data['listing_id'] ?: null,
                    'agent_id' => $form_data['agent_id'] ?: null,
                    'status' => $config['default_status'],
                    'priority' => $config['default_priority'],
                    'assigned_to' => $form_data['agent_id'] ?: null
                ];
                
                $lead_id = $lead_service->create_lead($lead_data);
                
                if ($lead_id && $lead_id > 0) {
                    error_log("✅ LeadService insert successful! Lead ID: {$lead_id}");
                    
                    // Trigger FollowUp Boss sync for LeadService leads too
                    self::trigger_followup_boss_sync($lead_id, $lead_data, $form_data);
                    
                    return $lead_id;
                } else {
                    error_log("❌ LeadService insert failed");
                }
            }
        } catch (Exception $e) {
            error_log("❌ LeadService error: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Send notification emails
     */
    private static function send_notifications($form_data, $config) {
        $notifications_sent = 0;
        
        // Send to agent if specified
        if (!empty($form_data['agent_id'])) {
            $agent_email = get_field('email', $form_data['agent_id']);
            if ($agent_email) {
                $sent = self::send_agent_notification($agent_email, $form_data, $config);
                if ($sent) $notifications_sent++;
            }
        }
        
        // Send to admin
        $admin_email = get_option('admin_email');
        if ($admin_email) {
            $sent = self::send_admin_notification($admin_email, $form_data, $config);
            if ($sent) $notifications_sent++;
        }
        
        // Send confirmation to user
        $sent = self::send_user_confirmation($form_data['email'], $form_data, $config);
        if ($sent) $notifications_sent++;
        
        return $notifications_sent > 0;
    }
    
    /**
     * Send agent notification
     */
    private static function send_agent_notification($agent_email, $form_data, $config) {
        $subject = sprintf('New %s from %s', ucwords(str_replace('_', ' ', $config['source'])), get_bloginfo('name'));
        
        $message = sprintf(
            "You have received a new lead submission.\n\n" .
            "Name: %s %s\n" .
            "Email: %s\n" .
            "Phone: %s\n" .
            "Message: %s\n\n" .
            "Source: %s\n" .
            "Submitted: %s\n\n" .
            "---\n" .
            "This message was sent from %s",
            $form_data['first_name'],
            $form_data['last_name'],
            $form_data['email'],
            $form_data['phone'] ?: 'Not provided',
            $form_data['message'],
            $config['source'],
            current_time('F j, Y g:i A'),
            home_url()
        );
        
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <noreply@' . parse_url(home_url(), PHP_URL_HOST) . '>',
            'Reply-To: ' . $form_data['first_name'] . ' ' . $form_data['last_name'] . ' <' . $form_data['email'] . '>'
        ];
        
        return wp_mail($agent_email, $subject, $message, $headers);
    }
    
    /**
     * Send admin notification
     */
    private static function send_admin_notification($admin_email, $form_data, $config) {
        $subject = sprintf('New Lead: %s %s', $form_data['first_name'], $form_data['last_name']);
        
        $message = sprintf(
            "New lead received through %s\n\n" .
            "Contact Information:\n" .
            "Name: %s %s\n" .
            "Email: %s\n" .
            "Phone: %s\n\n" .
            "Message:\n%s\n\n" .
            "Tracking Information:\n" .
            "Source: %s\n" .
            "Source URL: %s\n" .
            "IP Address: %s\n" .
            "Submitted: %s\n\n" .
            "View in admin: %s",
            $config['source'],
            $form_data['first_name'],
            $form_data['last_name'],
            $form_data['email'],
            $form_data['phone'] ?: 'Not provided',
            $form_data['message'],
            $config['source'],
            $form_data['source_url'] ?: 'Direct',
            $form_data['ip_address'] ?: 'Unknown',
            current_time('F j, Y g:i A'),
            admin_url('admin.php?page=happy-place-leads')
        );
        
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <noreply@' . parse_url(home_url(), PHP_URL_HOST) . '>'
        ];
        
        return wp_mail($admin_email, $subject, $message, $headers);
    }
    
    /**
     * Send user confirmation
     */
    private static function send_user_confirmation($user_email, $form_data, $config) {
        $subject = sprintf('Thank you for contacting %s', get_bloginfo('name'));
        
        $message = sprintf(
            "Dear %s,\n\n" .
            "Thank you for contacting us! We have received your message and will get back to you shortly.\n\n" .
            "Your message:\n%s\n\n" .
            "We typically respond within 24 hours during business days.\n\n" .
            "Best regards,\n" .
            "The %s Team\n\n" .
            "---\n" .
            "%s\n" .
            "%s",
            $form_data['first_name'],
            $form_data['message'],
            get_bloginfo('name'),
            get_bloginfo('name'),
            home_url()
        );
        
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <noreply@' . parse_url(home_url(), PHP_URL_HOST) . '>'
        ];
        
        return wp_mail($user_email, $subject, $message, $headers);
    }
    
    /**
     * Legacy handler for agent contact forms
     */
    public static function handle_agent_contact_legacy() {
        // Convert legacy format to unified format
        $_POST['lead_type'] = 'agent_contact';
        $_POST['nonce_action'] = 'agent_contact_nonce';
        
        // Call unified handler
        self::handle_lead_submission();
    }
    
    /**
     * Legacy handler for general inquiry forms
     */
    public static function handle_general_inquiry_legacy() {
        // Convert legacy format to unified format
        $_POST['lead_type'] = 'general_inquiry';
        $_POST['nonce_action'] = 'general_inquiry_nonce';
        
        // Call unified handler
        self::handle_lead_submission();
    }
    
    /**
     * Get lead statistics
     */
    public static function get_lead_stats() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hp_leads';
        
        $stats = [
            'total' => 0,
            'new' => 0,
            'contacted' => 0,
            'qualified' => 0,
            'converted' => 0,
            'by_source' => []
        ];
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $stats['total'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $stats['new'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'new'");
            $stats['contacted'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'contacted'");
            $stats['qualified'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'qualified'");
            $stats['converted'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'converted'");
            
            $source_stats = $wpdb->get_results("SELECT source, COUNT(*) as count FROM $table_name GROUP BY source");
            foreach ($source_stats as $stat) {
                $stats['by_source'][$stat->source] = (int) $stat->count;
            }
        }
        
        return $stats;
    }
    
    /**
     * Trigger FollowUp Boss sync for newly created leads
     */
    private static function trigger_followup_boss_sync($lead_id, $lead_data, $form_data) {
        try {
            // Check if FollowUp Boss integration is available and enabled
            if (!class_exists('\HappyPlace\Integrations\FollowUp_Boss_Integration')) {
                error_log('FollowUp Boss integration class not found, skipping sync');
                return;
            }
            
            // Prepare lead data in the format expected by the existing FUB integration
            $fub_lead_data = [
                'lead_id' => $lead_id,
                'first_name' => $lead_data['first_name'],
                'last_name' => $lead_data['last_name'],
                'email' => $lead_data['email'],
                'phone' => $lead_data['phone'],
                'message' => $lead_data['message'],
                'source' => $lead_data['source'],
                'listing_id' => $lead_data['listing_id'],
                'agent_id' => $lead_data['agent_id']
            ];
            
            // Trigger the existing FollowUp Boss hook that the integration listens for
            do_action('hp_lead_captured', $fub_lead_data, $lead_data['listing_id']);
            
            error_log("✅ FollowUp Boss sync triggered for lead ID: {$lead_id}");
            
        } catch (Exception $e) {
            error_log("❌ FollowUp Boss sync trigger failed: " . $e->getMessage());
        }
    }
}
