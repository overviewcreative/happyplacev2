<?php
/**
 * Calendly Integration Service
 * 
 * Handles Calendly API integration for appointment booking and management
 * 
 * @package HappyPlace
 * @subpackage Services
 * @since 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

class CalendlyService extends Service {

    protected string $name = 'calendly_service';
    
    private string $api_base = 'https://api.calendly.com';
    private string $api_token;
    private string $user_uri;
    
    /**
     * Initialize the service
     */
    public function init(): void {
        $this->api_token = get_option('hp_calendly_api_token', '');
        $this->user_uri = get_option('hp_calendly_user_uri', '');
        
        // Always initialize basic functionality for Form Router integration
        add_action('wp_ajax_hp_book_appointment', [$this, 'handle_booking_request']);
        add_action('wp_ajax_nopriv_hp_book_appointment', [$this, 'handle_booking_request']);
        add_action('wp_ajax_hp_get_appointments', [$this, 'get_appointments_ajax']);
        add_action('wp_ajax_hp_cancel_appointment', [$this, 'cancel_appointment_ajax']);
        
        // Webhook handler for Calendly events
        add_action('init', [$this, 'register_webhook_endpoint']);
        
        // Create appointments table if it doesn't exist
        add_action('plugins_loaded', [$this, 'create_appointments_table']);
    }
    
    /**
     * Register webhook endpoint for Calendly events
     */
    public function register_webhook_endpoint(): void {
        add_rewrite_rule(
            '^calendly-webhook/?$',
            'index.php?calendly_webhook=1',
            'top'
        );
        add_filter('query_vars', [$this, 'add_webhook_query_var']);
        add_action('template_redirect', [$this, 'handle_webhook']);
    }
    
    public function add_webhook_query_var($vars) {
        $vars[] = 'calendly_webhook';
        return $vars;
    }
    
    /**
     * Handle Calendly webhook events
     */
    public function handle_webhook(): void {
        if (!get_query_var('calendly_webhook')) {
            return;
        }
        
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);
        
        if (!$data || !isset($data['event'])) {
            wp_die('Invalid webhook data', 'Webhook Error', ['response' => 400]);
        }
        
        $this->process_webhook_event($data);
        
        wp_die('OK', 'Webhook Success', ['response' => 200]);
    }
    
    /**
     * Process incoming webhook events
     */
    private function process_webhook_event(array $data): void {
        $event_type = $data['event'];
        $payload = $data['payload'] ?? [];
        
        switch ($event_type) {
            case 'invitee.created':
                $this->sync_appointment_created($payload);
                break;
            case 'invitee.canceled':
                $this->sync_appointment_canceled($payload);
                break;
        }
    }
    
    /**
     * Generate Calendly booking link for Form Router integration
     */
    public function generate_booking_link(array $form_data, array $route_config): string {
        $calendly_settings = $route_config['calendly_settings'] ?? [];
        $calendar_type = $calendly_settings['calendar_type'] ?? 'consultation';
        
        // Get Calendly username from configuration
        $calendly_username = get_option('hp_calendly_username', '');
        if (empty($calendly_username)) {
            $this->log('Calendly username not configured', 'warning');
            return '';
        }
        
        // Build the scheduling URL
        $base_url = "https://calendly.com/{$calendly_username}";
        
        // Get calendar slug mapping
        $calendar_slugs = get_option('hp_calendly_calendar_slugs', [
            'consultation' => '30min',
            'showing' => 'showing', 
            'valuation' => 'valuation',
            'listing_appointment' => 'listing',
            'call' => '15min'
        ]);
        
        $calendar_slug = $calendar_slugs[$calendar_type] ?? $calendar_type;
        $scheduling_url = "{$base_url}/{$calendar_slug}";
        
        // Prepare prefill parameters
        $prefill_params = $this->prepare_prefill_params($form_data, $calendly_settings);
        
        // Add URL parameters
        if (!empty($prefill_params)) {
            $scheduling_url .= '?' . http_build_query($prefill_params);
        }
        
        // Store pending appointment in database
        $this->create_pending_appointment_for_router($form_data, $calendar_type, $scheduling_url);
        
        $this->log("Generated Calendly link: {$scheduling_url}");
        
        return $scheduling_url;
    }
    
    /**
     * Prepare prefill parameters for Calendly URL
     */
    private function prepare_prefill_params(array $form_data, array $calendly_settings): array {
        $params = [];
        
        // Name prefill
        if (!empty($form_data['first_name'])) {
            $params['name'] = trim($form_data['first_name'] . ' ' . ($form_data['last_name'] ?? ''));
        }
        
        // Email prefill
        if (!empty($form_data['email'])) {
            $params['email'] = $form_data['email'];
        }
        
        // Custom questions based on form data
        if (!empty($form_data['phone'])) {
            $params['a1'] = $form_data['phone']; // Assuming first additional question is phone
        }
        
        if (!empty($form_data['message'])) {
            $params['a2'] = $form_data['message']; // Assuming second additional question is message/notes
        }
        
        // Property-specific information
        if (!empty($form_data['listing_id'])) {
            $property_address = get_field('address', $form_data['listing_id']);
            if ($property_address) {
                $params['a3'] = "Property: {$property_address}";
            }
        }
        
        // UTM tracking
        $utm_params = ['utm_source', 'utm_medium', 'utm_campaign'];
        foreach ($utm_params as $param) {
            if (!empty($form_data['_meta'][$param])) {
                $params[$param] = $form_data['_meta'][$param];
            }
        }
        
        return $params;
    }
    
    /**
     * Create pending appointment for Form Router
     */
    private function create_pending_appointment_for_router(array $form_data, string $calendar_type, string $scheduling_url): int {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_appointments';
        
        $appointment_data = [
            'appointment_type' => $calendar_type,
            'client_name' => trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '')),
            'client_email' => $form_data['email'] ?? '',
            'client_phone' => $form_data['phone'] ?? '',
            'listing_id' => $form_data['listing_id'] ?? null,
            'agent_id' => $form_data['agent_id'] ?? null,
            'message' => $form_data['message'] ?? '',
            'booking_url' => $scheduling_url,
            'status' => 'pending',
            'source' => 'form_router',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert($table_name, $appointment_data);
        
        if ($result === false) {
            $this->log('Failed to create pending appointment: ' . $wpdb->last_error, 'error');
            return 0;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Create appointment booking
     */
    public function book_appointment(array $booking_data): array {
        try {
            // First, get available event types
            $event_types = $this->get_event_types();
            
            if (empty($event_types)) {
                throw new \Exception('No event types available');
            }
            
            // Use the first available event type or match by name
            $event_type_uri = $this->find_event_type($event_types, $booking_data['appointment_type'] ?? 'consultation');
            
            if (!$event_type_uri) {
                throw new \Exception('Event type not found');
            }
            
            // Create the booking
            $booking_response = $this->make_api_request('POST', '/scheduling_links', [
                'max_event_count' => 1,
                'owner' => $this->user_uri,
                'owner_type' => 'EventType',
                'owner_uri' => $event_type_uri
            ]);
            
            if (!$booking_response || !isset($booking_response['resource']['booking_url'])) {
                throw new \Exception('Failed to create scheduling link');
            }
            
            // Store booking locally for dashboard
            $appointment_id = $this->store_local_appointment($booking_data, $booking_response);
            
            return [
                'success' => true,
                'booking_url' => $booking_response['resource']['booking_url'],
                'appointment_id' => $appointment_id
            ];
            
        } catch (\Exception $e) {
            hp_log('calendly_error', 'Booking failed: ' . $e->getMessage(), $booking_data);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get available event types
     */
    private function get_event_types(): array {
        $response = $this->make_api_request('GET', '/event_types', [
            'user' => $this->user_uri
        ]);
        
        return $response['collection'] ?? [];
    }
    
    /**
     * Find specific event type by name
     */
    private function find_event_type(array $event_types, string $type_name): ?string {
        $type_mappings = [
            'showing' => ['showing', 'property showing', 'tour'],
            'consultation' => ['consultation', 'buyer consultation', 'seller consultation'],
            'listing' => ['listing appointment', 'listing consultation']
        ];
        
        $search_terms = $type_mappings[$type_name] ?? [$type_name];
        
        foreach ($event_types as $event_type) {
            $name = strtolower($event_type['name']);
            
            foreach ($search_terms as $term) {
                if (strpos($name, strtolower($term)) !== false) {
                    return $event_type['uri'];
                }
            }
        }
        
        // Fallback to first available
        return $event_types[0]['uri'] ?? null;
    }
    
    /**
     * Store appointment locally for dashboard management
     */
    private function store_local_appointment(array $booking_data, array $calendly_response): int {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_appointments';
        
        $appointment_data = [
            'calendly_uri' => $calendly_response['resource']['uri'] ?? '',
            'booking_url' => $calendly_response['resource']['booking_url'] ?? '',
            'appointment_type' => sanitize_text_field($booking_data['appointment_type'] ?? 'consultation'),
            'client_name' => sanitize_text_field($booking_data['client_name'] ?? ''),
            'client_email' => sanitize_email($booking_data['client_email'] ?? ''),
            'client_phone' => sanitize_text_field($booking_data['client_phone'] ?? ''),
            'listing_id' => intval($booking_data['listing_id'] ?? 0),
            'agent_id' => intval($booking_data['agent_id'] ?? 0),
            'message' => sanitize_textarea_field($booking_data['message'] ?? ''),
            'status' => 'scheduled',
            'source' => 'website',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];
        
        $wpdb->insert($table_name, $appointment_data);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get appointments for dashboard
     */
    public function get_appointments(array $args = []): array {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_appointments';
        $per_page = $args['per_page'] ?? 20;
        $page = $args['page'] ?? 1;
        $offset = ($page - 1) * $per_page;
        
        $where_conditions = ['1=1'];
        $where_values = [];
        
        // Filter by agent (for non-admin users)
        if (!current_user_can('manage_options')) {
            $current_user_id = get_current_user_id();
            $where_conditions[] = 'agent_id = %d';
            $where_values[] = $current_user_id;
        }
        
        // Filter by status
        if (!empty($args['status'])) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $args['status'];
        }
        
        // Filter by type
        if (!empty($args['type'])) {
            $where_conditions[] = 'appointment_type = %s';
            $where_values[] = $args['type'];
        }
        
        // Search filter
        if (!empty($args['search'])) {
            $where_conditions[] = '(client_name LIKE %s OR client_email LIKE %s OR message LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
        if (!empty($where_values)) {
            $count_query = $wpdb->prepare($count_query, ...$where_values);
        }
        $total_appointments = $wpdb->get_var($count_query);
        
        // Get appointments
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $query_values = array_merge($where_values, [$per_page, $offset]);
        
        $appointments = $wpdb->get_results($wpdb->prepare($query, ...$query_values), ARRAY_A);
        
        // Enhance appointment data
        foreach ($appointments as &$appointment) {
            $appointment = $this->enhance_appointment_data($appointment);
        }
        
        return [
            'appointments' => $appointments,
            'total' => intval($total_appointments),
            'pages' => ceil($total_appointments / $per_page),
            'current_page' => $page
        ];
    }
    
    /**
     * Enhance appointment data with additional info
     */
    private function enhance_appointment_data(array $appointment): array {
        // Get listing info if available
        if ($appointment['listing_id']) {
            $listing = get_post($appointment['listing_id']);
            if ($listing) {
                $appointment['listing_title'] = $listing->post_title;
                $appointment['listing_address'] = get_field('address', $listing->ID);
                $appointment['listing_price'] = get_field('price', $listing->ID);
            }
        }
        
        // Get agent info if available
        if ($appointment['agent_id']) {
            $agent = get_post($appointment['agent_id']);
            if ($agent) {
                $appointment['agent_name'] = $agent->post_title;
                $appointment['agent_email'] = get_field('email', $agent->ID);
                $appointment['agent_phone'] = get_field('phone', $agent->ID);
            }
        }
        
        // Format dates
        $appointment['created_at_formatted'] = date('M j, Y g:i A', strtotime($appointment['created_at']));
        $appointment['status_badge'] = $this->get_status_badge($appointment['status']);
        $appointment['type_badge'] = $this->get_type_badge($appointment['appointment_type']);
        
        return $appointment;
    }
    
    /**
     * Get status badge HTML
     */
    private function get_status_badge(string $status): string {
        $badges = [
            'scheduled' => '<span class="hph-badge hph-badge-success">Scheduled</span>',
            'completed' => '<span class="hph-badge hph-badge-primary">Completed</span>',
            'canceled' => '<span class="hph-badge hph-badge-danger">Canceled</span>',
            'no_show' => '<span class="hph-badge hph-badge-warning">No Show</span>'
        ];
        
        return $badges[$status] ?? '<span class="hph-badge hph-badge-secondary">' . ucfirst($status) . '</span>';
    }
    
    /**
     * Get type badge HTML
     */
    private function get_type_badge(string $type): string {
        $badges = [
            'showing' => '<span class="hph-badge hph-badge-outline-primary"><i class="fas fa-home"></i> Showing</span>',
            'consultation' => '<span class="hph-badge hph-badge-outline-secondary"><i class="fas fa-handshake"></i> Consultation</span>',
            'listing' => '<span class="hph-badge hph-badge-outline-info"><i class="fas fa-tag"></i> Listing Appt</span>'
        ];
        
        return $badges[$type] ?? '<span class="hph-badge hph-badge-outline-dark">' . ucfirst($type) . '</span>';
    }
    
    /**
     * Make API request to Calendly
     */
    private function make_api_request(string $method, string $endpoint, array $data = []): ?array {
        $url = $this->api_base . $endpoint;
        
        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_token,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ];
        
        if ($method === 'POST' && !empty($data)) {
            $args['body'] = json_encode($data);
        } elseif ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            hp_log('calendly_error', 'API request failed: ' . $response->get_error_message(), compact('method', 'endpoint', 'data'));
            return null;
        }
        
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code < 200 || $status_code >= 300) {
            hp_log('calendly_error', 'API request error: HTTP ' . $status_code, compact('method', 'endpoint', 'body'));
            return null;
        }
        
        return $decoded;
    }
    
    /**
     * Sync appointment when created via webhook
     */
    private function sync_appointment_created(array $payload): void {
        // Update local appointment record with Calendly data
        $this->update_appointment_from_webhook($payload, 'scheduled');
    }
    
    /**
     * Sync appointment when canceled via webhook  
     */
    private function sync_appointment_canceled(array $payload): void {
        $this->update_appointment_from_webhook($payload, 'canceled');
    }
    
    /**
     * Update appointment from webhook data
     */
    private function update_appointment_from_webhook(array $payload, string $status): void {
        global $wpdb;
        
        $calendly_uri = $payload['uri'] ?? '';
        if (empty($calendly_uri)) {
            return;
        }
        
        $table_name = $wpdb->prefix . 'hp_appointments';
        
        $update_data = [
            'status' => $status,
            'calendly_event_uri' => $calendly_uri,
            'scheduled_time' => isset($payload['start_time']) ? date('Y-m-d H:i:s', strtotime($payload['start_time'])) : null,
            'updated_at' => current_time('mysql')
        ];
        
        // Try to match by booking URL or calendly URI
        $wpdb->update(
            $table_name,
            $update_data,
            ['calendly_uri' => $calendly_uri],
            ['%s', '%s', '%s', '%s'],
            ['%s']
        );
    }
    
    /**
     * AJAX handler for booking requests
     */
    public function handle_booking_request(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_book_appointment')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $booking_data = [
            'appointment_type' => sanitize_text_field($_POST['appointment_type'] ?? ''),
            'client_name' => sanitize_text_field($_POST['client_name'] ?? ''),
            'client_email' => sanitize_email($_POST['client_email'] ?? ''),
            'client_phone' => sanitize_text_field($_POST['client_phone'] ?? ''),
            'listing_id' => intval($_POST['listing_id'] ?? 0),
            'agent_id' => intval($_POST['agent_id'] ?? 0),
            'message' => sanitize_textarea_field($_POST['message'] ?? '')
        ];
        
        // Validate required fields
        if (empty($booking_data['client_name']) || empty($booking_data['client_email'])) {
            wp_send_json_error(['message' => 'Name and email are required']);
        }
        
        $result = $this->book_appointment($booking_data);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(['message' => $result['error']]);
        }
    }
    
    /**
     * AJAX handler for getting appointments
     */
    public function get_appointments_ajax(): void {
        if (!wp_verify_nonce($_GET['nonce'] ?? '', 'hp_dashboard_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $args = [
            'page' => intval($_GET['page'] ?? 1),
            'per_page' => intval($_GET['per_page'] ?? 20),
            'status' => sanitize_text_field($_GET['status'] ?? ''),
            'type' => sanitize_text_field($_GET['type'] ?? ''),
            'search' => sanitize_text_field($_GET['search'] ?? '')
        ];
        
        $result = $this->get_appointments($args);
        wp_send_json_success($result);
    }
    
    /**
     * AJAX handler for canceling appointments
     */
    public function cancel_appointment_ajax(): void {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_dashboard_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $appointment_id = intval($_POST['appointment_id'] ?? 0);
        if (!$appointment_id) {
            wp_send_json_error(['message' => 'Invalid appointment ID']);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'hp_appointments';
        
        $result = $wpdb->update(
            $table_name,
            [
                'status' => 'canceled',
                'updated_at' => current_time('mysql')
            ],
            ['id' => $appointment_id],
            ['%s', '%s'],
            ['%d']
        );
        
        if ($result !== false) {
            wp_send_json_success(['message' => 'Appointment canceled successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to cancel appointment']);
        }
    }
    
    /**
     * Create appointments table
     */
    public function create_appointments_table(): bool {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_appointments';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            calendly_uri varchar(500) DEFAULT NULL,
            calendly_event_uri varchar(500) DEFAULT NULL,
            booking_url varchar(500) DEFAULT NULL,
            appointment_type varchar(50) NOT NULL DEFAULT 'consultation',
            client_name varchar(255) NOT NULL,
            client_email varchar(255) NOT NULL,
            client_phone varchar(50) DEFAULT NULL,
            listing_id int(11) DEFAULT NULL,
            agent_id int(11) DEFAULT NULL,
            message text DEFAULT NULL,
            status varchar(50) NOT NULL DEFAULT 'scheduled',
            source varchar(50) NOT NULL DEFAULT 'website',
            scheduled_time datetime DEFAULT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY idx_status (status),
            KEY idx_agent (agent_id),
            KEY idx_listing (listing_id),
            KEY idx_client_email (client_email),
            KEY idx_created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        return true;
    }
}