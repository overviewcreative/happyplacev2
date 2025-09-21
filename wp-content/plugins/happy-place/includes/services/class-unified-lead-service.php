<?php
/**
 * Unified Lead Service - Enhanced Lead Management System
 *
 * Combines the comprehensive features of HPH_Unified_Lead_Handler with
 * the proper service architecture. Handles all lead submissions across
 * different forms with advanced features.
 *
 * @package HappyPlace\Services
 * @version 4.1.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class UnifiedLeadService extends Service {

    protected string $name = 'unified_lead_service';
    protected string $version = '4.1.0';

    /**
     * Lead types and their configurations
     */
    private array $lead_types = [
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
     * Lead table name
     */
    private string $table_name;

    /**
     * Lead notes table name
     */
    private string $notes_table_name;

    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }

        global $wpdb;
        $this->table_name = $wpdb->prefix . 'hp_leads';
        $this->notes_table_name = $wpdb->prefix . 'hp_lead_notes';

        // Create tables if they don't exist
        $this->create_tables();

        // Register AJAX handlers - unified system with backward compatibility
        $this->register_ajax_handlers();

        // Register shortcodes
        $this->register_shortcodes();

        // Register admin functionality
        if (is_admin()) {
            add_action('admin_menu', [$this, 'register_admin_menu']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        }

        // Register frontend assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);

        $this->initialized = true;
        $this->log('Unified Lead Service initialized successfully');
    }

    /**
     * Create database tables
     */
    private function create_tables(): void {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Enhanced leads table with all features from both systems
        $sql_leads = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20),
            message text,
            source varchar(50) DEFAULT 'website',
            source_url varchar(500),
            listing_id int(11),
            agent_id int(11),
            status varchar(20) DEFAULT 'new',
            priority varchar(20) DEFAULT 'normal',
            tags text,
            lead_score int(11) DEFAULT 0,
            ip_address varchar(45),
            user_agent varchar(255),
            referrer varchar(500),
            utm_source varchar(100),
            utm_medium varchar(100),
            utm_campaign varchar(100),
            inquiry_type varchar(100),
            budget varchar(100),
            timeline varchar(100),
            preferred_contact varchar(20) DEFAULT 'email',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            assigned_to int(11),
            last_contacted datetime,
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            last_contact datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_email (email),
            KEY idx_status (status),
            KEY idx_created (created_at),
            KEY idx_listing (listing_id),
            KEY idx_agent (agent_id),
            KEY idx_assigned (assigned_to)
        ) $charset_collate;";

        // Lead notes table
        $sql_notes = "CREATE TABLE IF NOT EXISTS {$this->notes_table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            lead_id int(11) NOT NULL,
            user_id int(11) NOT NULL,
            note text NOT NULL,
            type varchar(50) DEFAULT 'note',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_lead (lead_id),
            KEY idx_created (created_at),
            FOREIGN KEY (lead_id) REFERENCES {$this->table_name}(id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_leads);
        dbDelta($sql_notes);
    }

    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        // Main unified handler
        add_action('wp_ajax_hph_submit_lead', [$this, 'handle_lead_submission']);
        add_action('wp_ajax_nopriv_hph_submit_lead', [$this, 'handle_lead_submission']);

        // Backward compatibility handlers
        add_action('wp_ajax_send_agent_contact', [$this, 'handle_agent_contact_legacy']);
        add_action('wp_ajax_nopriv_send_agent_contact', [$this, 'handle_agent_contact_legacy']);

        add_action('wp_ajax_send_general_inquiry', [$this, 'handle_general_inquiry_legacy']);
        add_action('wp_ajax_nopriv_send_general_inquiry', [$this, 'handle_general_inquiry_legacy']);

        // Additional handlers for theme compatibility
        add_action('wp_ajax_hp_submit_lead', [$this, 'handle_lead_submission']);
        add_action('wp_ajax_nopriv_hp_submit_lead', [$this, 'handle_lead_submission']);

        // Admin handlers
        add_action('wp_ajax_hp_update_lead_status', [$this, 'ajax_update_lead_status']);
        add_action('wp_ajax_hp_add_lead_note', [$this, 'ajax_add_lead_note']);
        add_action('wp_ajax_hp_delete_lead', [$this, 'ajax_delete_lead']);
        add_action('wp_ajax_hp_export_leads', [$this, 'ajax_export_leads']);
    }

    /**
     * Register shortcodes
     */
    private function register_shortcodes(): void {
        add_shortcode('hp_lead_form', [$this, 'render_lead_form_shortcode']);
        add_shortcode('hp_contact_form', [$this, 'render_contact_form_shortcode']);
        add_shortcode('hp_property_inquiry', [$this, 'render_property_inquiry_shortcode']);
    }

    /**
     * Main lead submission handler - enhanced from HPH_Unified_Lead_Handler
     */
    public function handle_lead_submission(): void {
        try {
            $this->log('Unified lead submission received');

            // Verify nonce
            $nonce_action = sanitize_text_field($_POST['nonce_action'] ?? 'hph_lead_nonce');
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $nonce_action)) {
                $this->log('Lead submission: Nonce verification failed', 'error');
                wp_die(json_encode(['success' => false, 'message' => 'Security check failed']));
            }

            // Get and validate lead type
            $lead_type = sanitize_text_field($_POST['lead_type'] ?? 'general_inquiry');
            if (!isset($this->lead_types[$lead_type])) {
                $this->log('Lead submission: Invalid lead type: ' . $lead_type, 'error');
                wp_die(json_encode(['success' => false, 'message' => 'Invalid lead type']));
            }

            $config = $this->lead_types[$lead_type];

            // Extract and sanitize form data
            $form_data = $this->extract_form_data($_POST);

            // Validate required fields
            $validation_result = $this->validate_lead_data($form_data, $config);
            if (!$validation_result['valid']) {
                wp_die(json_encode([
                    'success' => false,
                    'message' => $validation_result['message']
                ]));
            }

            // Create lead in database
            $lead_id = $this->create_lead($form_data, $config);

            if (!$lead_id) {
                $this->log('Failed to create lead in database', 'error');
                wp_die(json_encode([
                    'success' => false,
                    'message' => 'Failed to save lead. Please try again.'
                ]));
            }

            // Send notification emails
            $email_sent = $this->send_notifications($form_data, $config);

            // Trigger FollowUp Boss sync
            $this->trigger_followup_boss_sync($lead_id, $form_data, $config);

            // Trigger hook for other integrations
            do_action('hp_lead_created', $lead_id, $form_data);

            // Log success
            $this->log("✅ Lead created successfully. ID: {$lead_id}, Type: {$lead_type}");

            wp_die(json_encode([
                'success' => true,
                'message' => 'Your submission has been received successfully!',
                'lead_id' => $lead_id
            ]));

        } catch (Exception $e) {
            $this->log('Lead submission error: ' . $e->getMessage(), 'error');
            wp_die(json_encode([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ]));
        }
    }

    /**
     * Extract and sanitize form data - enhanced from HPH_Unified_Lead_Handler
     */
    private function extract_form_data(array $post_data): array {
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
     * Validate lead data - enhanced from HPH_Unified_Lead_Handler
     */
    private function validate_lead_data(array $form_data, array $config): array {
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
     * Create lead in database - enhanced with lead scoring
     */
    public function create_lead(array $form_data, array $config): int {
        global $wpdb;

        // Calculate lead score
        $lead_score = $this->calculate_lead_score($form_data);

        // Auto-assign to agent if applicable
        $assigned_to = $form_data['agent_id'] ?: null;
        if (empty($assigned_to) && !empty($form_data['listing_id'])) {
            $listing_agent = get_post_meta($form_data['listing_id'], 'listing_agent', true);
            if ($listing_agent) {
                $assigned_to = $listing_agent;
            }
        }

        $current_time = current_time('mysql');

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
            'lead_score' => $lead_score,
            'ip_address' => $form_data['ip_address'] ?: null,
            'user_agent' => $form_data['user_agent'] ?: null,
            'referrer' => $form_data['source_url'] ?: null,
            'utm_source' => $form_data['utm_source'] ?: null,
            'utm_medium' => $form_data['utm_medium'] ?: null,
            'utm_campaign' => $form_data['utm_campaign'] ?: null,
            'inquiry_type' => $form_data['inquiry_type'] ?: null,
            'budget' => $form_data['budget'] ?: null,
            'timeline' => $form_data['timeline'] ?: null,
            'preferred_contact' => $form_data['preferred_contact'],
            'assigned_to' => $assigned_to,
            'created_at' => $current_time,
            'updated_at' => $current_time,
            'created_date' => $current_time,
            'last_contact' => $current_time
        ];

        // Create format specifiers
        $formats = [
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', // first_name through source_url
            '%d', '%d', '%s', '%s', '%d', '%s', '%s', // listing_id through user_agent
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', // referrer through timeline
            '%s', '%d', '%s', '%s', '%s', '%s'       // preferred_contact through last_contact
        ];

        $result = $wpdb->insert(
            $this->table_name,
            $lead_data,
            $formats
        );

        if ($result === false) {
            $this->log('Failed to create lead: ' . $wpdb->last_error, 'error');
            return 0;
        }

        return $wpdb->insert_id;
    }

    /**
     * Calculate lead score based on various factors
     */
    private function calculate_lead_score(array $data): int {
        $score = 0;

        // Has phone number (+20)
        if (!empty($data['phone'])) {
            $score += 20;
        }

        // Has detailed message (+15)
        if (strlen($data['message']) > 100) {
            $score += 15;
        }

        // Specific property inquiry (+25)
        if (!empty($data['listing_id'])) {
            $score += 25;
        }

        // Has UTM tracking (+10)
        if (!empty($data['utm_source'])) {
            $score += 10;
        }

        // Has budget information (+15)
        if (!empty($data['budget'])) {
            $score += 15;
        }

        // Has timeline (+10)
        if (!empty($data['timeline'])) {
            $score += 10;
        }

        // Time of submission (business hours +5)
        $hour = date('G');
        if ($hour >= 9 && $hour <= 17) {
            $score += 5;
        }

        return min($score, 100); // Cap at 100
    }

    /**
     * Send notification emails - enhanced from HPH_Unified_Lead_Handler
     */
    private function send_notifications(array $form_data, array $config): bool {
        $notifications_sent = 0;

        // Send to agent if specified
        if (!empty($form_data['agent_id'])) {
            $agent_email = get_field('email', $form_data['agent_id']);
            if ($agent_email) {
                $sent = $this->send_agent_notification($agent_email, $form_data, $config);
                if ($sent) $notifications_sent++;
            }
        }

        // Send to admin
        $admin_email = get_option('admin_email');
        if ($admin_email) {
            $sent = $this->send_admin_notification($admin_email, $form_data, $config);
            if ($sent) $notifications_sent++;
        }

        // Send confirmation to user
        $sent = $this->send_user_confirmation($form_data['email'], $form_data, $config);
        if ($sent) $notifications_sent++;

        return $notifications_sent > 0;
    }

    /**
     * Send agent notification - from HPH_Unified_Lead_Handler
     */
    private function send_agent_notification(string $agent_email, array $form_data, array $config): bool {
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
     * Send admin notification - from HPH_Unified_Lead_Handler
     */
    private function send_admin_notification(string $admin_email, array $form_data, array $config): bool {
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
     * Send user confirmation - from HPH_Unified_Lead_Handler
     */
    private function send_user_confirmation(string $user_email, array $form_data, array $config): bool {
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
     * Trigger FollowUp Boss sync - from HPH_Unified_Lead_Handler
     */
    private function trigger_followup_boss_sync(int $lead_id, array $form_data, array $config): void {
        try {
            // Check if FollowUp Boss integration is available and enabled
            if (!class_exists('\HappyPlace\Integrations\FollowUp_Boss_Integration')) {
                $this->log('FollowUp Boss integration class not found, skipping sync');
                return;
            }

            // Prepare lead data in the format expected by the existing FUB integration
            $fub_lead_data = [
                'lead_id' => $lead_id,
                'first_name' => $form_data['first_name'],
                'last_name' => $form_data['last_name'],
                'email' => $form_data['email'],
                'phone' => $form_data['phone'],
                'message' => $form_data['message'],
                'source' => $config['source'],
                'listing_id' => $form_data['listing_id'],
                'agent_id' => $form_data['agent_id']
            ];

            // Trigger the existing FollowUp Boss hook that the integration listens for
            do_action('hp_lead_captured', $fub_lead_data, $form_data['listing_id']);

            $this->log("✅ FollowUp Boss sync triggered for lead ID: {$lead_id}");

        } catch (Exception $e) {
            $this->log("❌ FollowUp Boss sync trigger failed: " . $e->getMessage(), 'error');
        }
    }

    /**
     * Legacy handler for agent contact forms
     */
    public function handle_agent_contact_legacy(): void {
        // Convert legacy format to unified format
        $_POST['lead_type'] = 'agent_contact';
        $_POST['nonce_action'] = 'agent_contact_nonce';

        // Call unified handler
        $this->handle_lead_submission();
    }

    /**
     * Legacy handler for general inquiry forms
     */
    public function handle_general_inquiry_legacy(): void {
        // Convert legacy format to unified format
        $_POST['lead_type'] = 'general_inquiry';
        $_POST['nonce_action'] = 'general_inquiry_nonce';

        // Call unified handler
        $this->handle_lead_submission();
    }

    /**
     * Get leads with filters
     */
    public function get_leads(array $args = []): array {
        global $wpdb;

        $defaults = [
            'status' => '',
            'agent_id' => 0,
            'listing_id' => 0,
            'date_from' => '',
            'date_to' => '',
            'search' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 20,
            'offset' => 0
        ];

        $args = wp_parse_args($args, $defaults);

        $where = ['1=1'];
        $values = [];

        if ($args['status']) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if ($args['agent_id']) {
            $where[] = '(agent_id = %d OR assigned_to = %d)';
            $values[] = $args['agent_id'];
            $values[] = $args['agent_id'];
        }

        if ($args['listing_id']) {
            $where[] = 'listing_id = %d';
            $values[] = $args['listing_id'];
        }

        if ($args['date_from']) {
            $where[] = 'created_at >= %s';
            $values[] = $args['date_from'];
        }

        if ($args['date_to']) {
            $where[] = 'created_at <= %s';
            $values[] = $args['date_to'];
        }

        if ($args['search']) {
            $where[] = '(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }

        $where_clause = implode(' AND ', $where);
        $order_clause = sprintf('%s %s', $args['orderby'], $args['order']);
        $limit_clause = sprintf('LIMIT %d OFFSET %d', $args['limit'], $args['offset']);

        $query = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY {$order_clause} {$limit_clause}";

        if (!empty($values)) {
            $query = $wpdb->prepare($query, $values);
        }

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Get lead statistics
     */
    public function get_lead_stats(): array {
        global $wpdb;

        $stats = [
            'total' => 0,
            'new' => 0,
            'contacted' => 0,
            'qualified' => 0,
            'converted' => 0,
            'by_source' => [],
            'avg_score' => 0
        ];

        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name) {
            $stats['total'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
            $stats['new'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'new'");
            $stats['contacted'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'contacted'");
            $stats['qualified'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'qualified'");
            $stats['converted'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'converted'");
            $stats['avg_score'] = (float) $wpdb->get_var("SELECT AVG(lead_score) FROM {$this->table_name}");

            $source_stats = $wpdb->get_results("SELECT source, COUNT(*) as count FROM {$this->table_name} GROUP BY source");
            foreach ($source_stats as $stat) {
                $stats['by_source'][$stat->source] = (int) $stat->count;
            }
        }

        return $stats;
    }

    /**
     * Register admin menu
     */
    public function register_admin_menu(): void {
        add_menu_page(
            'Leads',
            'Leads',
            'manage_options',
            'happy-place-leads',
            [$this, 'render_admin_page'],
            'dashicons-groups',
            30
        );

        add_submenu_page(
            'happy-place-leads',
            'Lead Settings',
            'Settings',
            'manage_options',
            'happy-place-lead-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Render admin page (placeholder)
     */
    public function render_admin_page(): void {
        echo '<div class="wrap"><h1>Lead Management</h1><p>Lead management interface will be implemented here.</p></div>';
    }

    /**
     * Render settings page (placeholder)
     */
    public function render_settings_page(): void {
        echo '<div class="wrap"><h1>Lead Settings</h1><p>Lead settings interface will be implemented here.</p></div>';
    }

    /**
     * Render lead form shortcode
     */
    public function render_lead_form_shortcode($atts): string {
        $atts = shortcode_atts([
            'title' => 'Get In Touch',
            'button_text' => 'Send Message',
            'show_phone' => 'true',
            'show_message' => 'true',
            'listing_id' => 0,
            'agent_id' => 0,
            'source' => 'shortcode',
            'class' => ''
        ], $atts);

        // Return basic form HTML for now
        return '<div class="hp-lead-form-wrapper">Form will be implemented</div>';
    }

    /**
     * Render contact form shortcode
     */
    public function render_contact_form_shortcode($atts): string {
        return $this->render_lead_form_shortcode($atts);
    }

    /**
     * Render property inquiry shortcode
     */
    public function render_property_inquiry_shortcode($atts): string {
        return $this->render_lead_form_shortcode($atts);
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets(): void {
        // Assets will be handled by theme for now
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook): void {
        if (strpos($hook, 'happy-place-leads') === false) {
            return;
        }
        // Admin assets will be implemented later
    }

    // Additional AJAX handlers (stub implementations)
    public function ajax_update_lead_status(): void { wp_send_json_error('Not implemented'); }
    public function ajax_add_lead_note(): void { wp_send_json_error('Not implemented'); }
    public function ajax_delete_lead(): void { wp_send_json_error('Not implemented'); }
    public function ajax_export_leads(): void { wp_send_json_error('Not implemented'); }
}