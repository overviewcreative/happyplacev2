<?php
/**
 * Lead Service - Complete Lead Management System
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class LeadService extends Service {
    
    protected string $name = 'lead_service';
    protected string $version = '4.0.0';
    
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
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
        
        // Register shortcodes
        $this->register_shortcodes();
        
        // Register admin menu
        if (is_admin()) {
            add_action('admin_menu', [$this, 'register_admin_menu']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        }
        
        // Register frontend assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        
        $this->initialized = true;
        $this->log('Lead Service initialized successfully');
    }
    
    /**
     * Create database tables
     */
    private function create_tables(): void {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Leads table
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
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            assigned_to int(11),
            last_contacted datetime,
            PRIMARY KEY (id),
            KEY idx_email (email),
            KEY idx_status (status),
            KEY idx_created (created_at),
            KEY idx_listing (listing_id),
            KEY idx_agent (agent_id)
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
            KEY idx_created (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_leads);
        dbDelta($sql_notes);
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        // Public handlers
        add_action('wp_ajax_nopriv_hp_submit_lead', [$this, 'ajax_submit_lead']);
        add_action('wp_ajax_hp_submit_lead', [$this, 'ajax_submit_lead']);
        
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
     * AJAX handler for lead submission
     */
    public function ajax_submit_lead(): void {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hp_lead_form_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Sanitize and validate data
        $lead_data = [
            'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'email' => sanitize_email($_POST['email'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'message' => sanitize_textarea_field($_POST['message'] ?? ''),
            'source' => sanitize_text_field($_POST['source'] ?? 'website'),
            'source_url' => esc_url_raw($_POST['source_url'] ?? ''),
            'listing_id' => intval($_POST['listing_id'] ?? 0),
            'agent_id' => intval($_POST['agent_id'] ?? 0),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'utm_source' => sanitize_text_field($_POST['utm_source'] ?? ''),
            'utm_medium' => sanitize_text_field($_POST['utm_medium'] ?? ''),
            'utm_campaign' => sanitize_text_field($_POST['utm_campaign'] ?? '')
        ];
        
        // Validate required fields
        if (empty($lead_data['first_name']) || empty($lead_data['email'])) {
            wp_send_json_error(['message' => 'Please fill in all required fields']);
            return;
        }
        
        if (!is_email($lead_data['email'])) {
            wp_send_json_error(['message' => 'Please enter a valid email address']);
            return;
        }
        
        // Create lead
        $lead_id = $this->create_lead($lead_data);
        
        if (!$lead_id) {
            wp_send_json_error(['message' => 'Failed to save lead. Please try again.']);
            return;
        }
        
        // Send notifications
        $this->send_lead_notifications($lead_id, $lead_data);
        
        // Trigger hook for integrations
        do_action('hp_lead_created', $lead_id, $lead_data);
        
        wp_send_json_success([
            'message' => 'Thank you for your inquiry! We\'ll get back to you soon.',
            'lead_id' => $lead_id,
            'redirect' => apply_filters('hp_lead_success_redirect', '')
        ]);
    }
    
    /**
     * Create a new lead
     */
    public function create_lead(array $data): int {
        global $wpdb;
        
        // Calculate lead score
        $data['lead_score'] = $this->calculate_lead_score($data);
        
        // Auto-assign to agent if applicable
        if (empty($data['agent_id']) && !empty($data['listing_id'])) {
            $listing_agent = get_post_meta($data['listing_id'], 'listing_agent', true);
            if ($listing_agent) {
                $data['agent_id'] = $listing_agent;
                $data['assigned_to'] = $listing_agent;
            }
        }
        
        $result = $wpdb->insert(
            $this->table_name,
            $data,
            [
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', 
                '%d', '%d', '%s', '%s', '%s', '%d', '%s', 
                '%s', '%s', '%s', '%s', '%s', '%d'
            ]
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
        
        // Time of submission (business hours +10)
        $hour = date('G');
        if ($hour >= 9 && $hour <= 17) {
            $score += 10;
        }
        
        return min($score, 100); // Cap at 100
    }
    
    /**
     * Send lead notifications
     */
    private function send_lead_notifications(int $lead_id, array $lead_data): void {
        // Get notification settings
        $admin_email = get_option('admin_email');
        $notification_emails = get_option('hp_lead_notification_emails', $admin_email);
        
        // Prepare email content
        $subject = sprintf('[New Lead] %s %s', $lead_data['first_name'], $lead_data['last_name']);
        
        $message = "You have received a new lead:\n\n";
        $message .= "Name: {$lead_data['first_name']} {$lead_data['last_name']}\n";
        $message .= "Email: {$lead_data['email']}\n";
        $message .= "Phone: {$lead_data['phone']}\n";
        $message .= "Message: {$lead_data['message']}\n\n";
        
        if (!empty($lead_data['listing_id'])) {
            $listing_title = get_the_title($lead_data['listing_id']);
            $listing_url = get_permalink($lead_data['listing_id']);
            $message .= "Property Inquiry: {$listing_title}\n";
            $message .= "Property URL: {$listing_url}\n\n";
        }
        
        $message .= "Lead Score: {$lead_data['lead_score']}/100\n";
        $message .= "Source: {$lead_data['source']}\n";
        
        if (!empty($lead_data['source_url'])) {
            $message .= "Source URL: {$lead_data['source_url']}\n";
        }
        
        $admin_url = admin_url('admin.php?page=hp-leads&lead_id=' . $lead_id);
        $message .= "\nView Lead: {$admin_url}";
        
        // Send email
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        
        // Send to admin/notification emails
        wp_mail($notification_emails, $subject, $message, $headers);
        
        // Send to assigned agent if different
        if (!empty($lead_data['assigned_to'])) {
            $agent = get_user_by('id', $lead_data['assigned_to']);
            if ($agent && $agent->user_email !== $admin_email) {
                wp_mail($agent->user_email, $subject, $message, $headers);
            }
        }
        
        // Send auto-responder to lead
        $this->send_lead_autoresponder($lead_data);
    }
    
    /**
     * Send auto-responder email to lead
     */
    private function send_lead_autoresponder(array $lead_data): void {
        $subject = 'Thank you for your inquiry';
        
        $message = "Dear {$lead_data['first_name']},\n\n";
        $message .= "Thank you for contacting us. We have received your inquiry and will get back to you within 24 hours.\n\n";
        
        if (!empty($lead_data['listing_id'])) {
            $listing_title = get_the_title($lead_data['listing_id']);
            $message .= "Property of Interest: {$listing_title}\n\n";
        }
        
        $message .= "If you have any urgent questions, please don't hesitate to call us.\n\n";
        $message .= "Best regards,\n";
        $message .= get_bloginfo('name');
        
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        
        wp_mail($lead_data['email'], $subject, $message, $headers);
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
        
        ob_start();
        ?>
        <div class="hp-lead-form-wrapper <?php echo esc_attr($atts['class']); ?>">
            <?php if ($atts['title']): ?>
                <h3 class="hp-lead-form-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <form class="hp-lead-form" data-source="<?php echo esc_attr($atts['source']); ?>">
                <div class="hp-form-messages" style="display: none;"></div>
                
                <div class="hp-form-row">
                    <div class="hp-form-group hp-form-half">
                        <label for="lead-first-name">First Name <span class="required">*</span></label>
                        <input type="text" id="lead-first-name" name="first_name" required>
                    </div>
                    
                    <div class="hp-form-group hp-form-half">
                        <label for="lead-last-name">Last Name <span class="required">*</span></label>
                        <input type="text" id="lead-last-name" name="last_name" required>
                    </div>
                </div>
                
                <div class="hp-form-group">
                    <label for="lead-email">Email Address <span class="required">*</span></label>
                    <input type="email" id="lead-email" name="email" required>
                </div>
                
                <?php if ($atts['show_phone'] === 'true'): ?>
                <div class="hp-form-group">
                    <label for="lead-phone">Phone Number</label>
                    <input type="tel" id="lead-phone" name="phone" placeholder="(555) 123-4567">
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_message'] === 'true'): ?>
                <div class="hp-form-group">
                    <label for="lead-message">Message</label>
                    <textarea id="lead-message" name="message" rows="4" placeholder="Tell us how we can help you..."></textarea>
                </div>
                <?php endif; ?>
                
                <?php if ($atts['listing_id']): ?>
                    <input type="hidden" name="listing_id" value="<?php echo esc_attr($atts['listing_id']); ?>">
                <?php endif; ?>
                
                <?php if ($atts['agent_id']): ?>
                    <input type="hidden" name="agent_id" value="<?php echo esc_attr($atts['agent_id']); ?>">
                <?php endif; ?>
                
                <input type="hidden" name="source" value="<?php echo esc_attr($atts['source']); ?>">
                <input type="hidden" name="source_url" value="<?php echo esc_url(get_permalink()); ?>">
                
                <div class="hp-form-group">
                    <button type="submit" class="hp-btn hp-btn-primary hp-btn-block">
                        <span class="hp-btn-text"><?php echo esc_html($atts['button_text']); ?></span>
                        <span class="hp-btn-loading" style="display: none;">
                            <span class="hp-spinner"></span> Sending...
                        </span>
                    </button>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render contact form shortcode
     */
    public function render_contact_form_shortcode($atts): string {
        $atts = shortcode_atts([
            'title' => 'Contact Us',
            'source' => 'contact'
        ], $atts);
        
        $atts['show_message'] = 'true';
        
        return $this->render_lead_form_shortcode($atts);
    }
    
    /**
     * Render property inquiry shortcode
     */
    public function render_property_inquiry_shortcode($atts): string {
        global $post;
        
        $atts = shortcode_atts([
            'title' => 'Inquire About This Property',
            'listing_id' => $post->ID ?? 0,
            'source' => 'property_inquiry'
        ], $atts);
        
        return $this->render_lead_form_shortcode($atts);
    }
    
    /**
     * Register admin menu
     */
    public function register_admin_menu(): void {
        add_menu_page(
            'Leads',
            'Leads',
            'manage_options',
            'hp-leads',
            [$this, 'render_admin_page'],
            'dashicons-groups',
            30
        );
        
        add_submenu_page(
            'hp-leads',
            'Lead Settings',
            'Settings',
            'manage_options',
            'hp-lead-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page(): void {
        include HP_PLUGIN_DIR . 'templates/admin/leads-manager.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page(): void {
        include HP_PLUGIN_DIR . 'templates/admin/lead-settings.php';
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets(): void {
        wp_enqueue_script(
            'hp-lead-forms',
            HP_ASSETS_URL . 'js/lead-forms.js',
            ['jquery'],
            HP_VERSION,
            true
        );
        
        wp_localize_script('hp-lead-forms', 'hp_lead_forms', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_lead_form_nonce'),
            'messages' => [
                'success' => 'Thank you! We\'ll be in touch soon.',
                'error' => 'Something went wrong. Please try again.',
                'validation' => 'Please fill in all required fields.',
                'email_invalid' => 'Please enter a valid email address.'
            ]
        ]);
        
        wp_enqueue_style(
            'hp-lead-forms',
            HP_ASSETS_URL . 'css/lead-forms.css',
            [],
            HP_VERSION
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook): void {
        if (strpos($hook, 'hp-leads') === false) {
            return;
        }
        
        wp_enqueue_script(
            'hp-leads-admin',
            HP_ASSETS_URL . 'js/admin/leads-manager.js',
            ['jquery'],
            HP_VERSION,
            true
        );
        
        wp_localize_script('hp-leads-admin', 'hp_leads_admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_leads_admin_nonce')
        ]);
        
        wp_enqueue_style(
            'hp-leads-admin',
            HP_ASSETS_URL . 'css/admin/leads-manager.css',
            [],
            HP_VERSION
        );
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
            $where[] = 'agent_id = %d';
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
     * Update lead status
     */
    public function update_lead_status(int $lead_id, string $status): bool {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table_name,
            ['status' => $status],
            ['id' => $lead_id],
            ['%s'],
            ['%d']
        );
        
        if ($result !== false) {
            do_action('hp_lead_status_updated', $lead_id, $status);
        }
        
        return $result !== false;
    }
    
    /**
     * Add lead note
     */
    public function add_lead_note(int $lead_id, string $note, string $type = 'note'): int {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->notes_table_name,
            [
                'lead_id' => $lead_id,
                'user_id' => get_current_user_id(),
                'note' => $note,
                'type' => $type
            ],
            ['%d', '%d', '%s', '%s']
        );
        
        if ($result === false) {
            return 0;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * AJAX handler for updating lead status
     */
    public function ajax_update_lead_status(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $lead_id = intval($_POST['lead_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        
        if (!$lead_id || !$status) {
            wp_send_json_error(['message' => 'Invalid request']);
            return;
        }
        
        if ($this->update_lead_status($lead_id, $status)) {
            wp_send_json_success(['message' => 'Status updated successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to update status']);
        }
    }
    
    /**
     * AJAX handler for adding lead note
     */
    public function ajax_add_lead_note(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $lead_id = intval($_POST['lead_id'] ?? 0);
        $note = sanitize_textarea_field($_POST['note'] ?? '');
        
        if (!$lead_id || !$note) {
            wp_send_json_error(['message' => 'Invalid request']);
            return;
        }
        
        $note_id = $this->add_lead_note($lead_id, $note);
        
        if ($note_id) {
            wp_send_json_success([
                'message' => 'Note added successfully',
                'note_id' => $note_id
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to add note']);
        }
    }
    
    /**
     * AJAX handler for deleting lead
     */
    public function ajax_delete_lead(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        global $wpdb;
        
        $lead_id = intval($_POST['lead_id'] ?? 0);
        
        if (!$lead_id) {
            wp_send_json_error(['message' => 'Invalid request']);
            return;
        }
        
        // Delete lead notes first
        $wpdb->delete($this->notes_table_name, ['lead_id' => $lead_id], ['%d']);
        
        // Delete lead
        $result = $wpdb->delete($this->table_name, ['id' => $lead_id], ['%d']);
        
        if ($result) {
            wp_send_json_success(['message' => 'Lead deleted successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete lead']);
        }
    }
    
    /**
     * AJAX handler for exporting leads
     */
    public function ajax_export_leads(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $leads = $this->get_leads(['limit' => 10000]);
        
        if (empty($leads)) {
            wp_send_json_error(['message' => 'No leads to export']);
            return;
        }
        
        $csv = "First Name,Last Name,Email,Phone,Status,Lead Score,Source,Created At\n";
        
        foreach ($leads as $lead) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%d","%s","%s"' . "\n",
                $lead['first_name'],
                $lead['last_name'],
                $lead['email'],
                $lead['phone'],
                $lead['status'],
                $lead['lead_score'],
                $lead['source'],
                $lead['created_at']
            );
        }
        
        wp_send_json_success([
            'csv' => $csv,
            'filename' => 'leads-export-' . date('Y-m-d') . '.csv'
        ]);
    }
}