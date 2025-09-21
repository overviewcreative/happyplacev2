<?php
/**
 * Unified Form Router with Calendly Integration
 * 
 * Single, authoritative form routing system that consolidates all form submissions
 * and provides flexible routing configurations with Calendly booking integration
 * 
 * @package HappyPlace\Services
 * @version 1.0.0
 * @since 4.1.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class FormRouter extends Service {
    
    protected string $name = 'form_router';
    protected string $version = '1.0.0';
    
    /**
     * Route configurations
     */
    private array $routes = [];
    
    /**
     * Field mapping rules
     */
    private array $field_mappings = [];
    
    /**
     * Conditional routing rules
     */
    private array $conditional_rules = [];
    
    /**
     * Integration handlers
     */
    private array $integrations = [];
    
    /**
     * Default route configurations
     */
    private array $default_routes = [
        'lead_capture' => [
            'name' => 'Lead Capture',
            'description' => 'Saves to database + Follow Up Boss sync',
            'actions' => ['database', 'followup_boss', 'email_notification'],
            'priority' => 10,
            'email_template' => 'lead_captured',
            'success_message' => 'Thank you! We\'ll contact you soon.',
            'redirect_url' => '',
            'skip_database' => false,
            'enable_calendly' => false
        ],
        'email_only' => [
            'name' => 'Email Only',
            'description' => 'Direct email to staff, no database',
            'actions' => ['email_notification'],
            'priority' => 5,
            'email_template' => 'form_submission',
            'success_message' => 'Your message has been sent successfully.',
            'redirect_url' => '',
            'skip_database' => true,
            'enable_calendly' => false
        ],
        'booking_request' => [
            'name' => 'Booking Request',
            'description' => 'Creates lead + triggers Calendly scheduling',
            'actions' => ['database', 'calendly_booking', 'email_notification'],
            'priority' => 15,
            'email_template' => 'booking_request',
            'success_message' => 'Please select your preferred appointment time.',
            'redirect_url' => '',
            'skip_database' => false,
            'enable_calendly' => true,
            'calendly_settings' => [
                'duration' => 30,
                'buffer_time' => 15,
                'calendar_type' => 'consultation'
            ]
        ],
        'support_ticket' => [
            'name' => 'Support Ticket',
            'description' => 'Email to support team + creates ticket',
            'actions' => ['email_notification', 'create_ticket'],
            'priority' => 8,
            'email_template' => 'support_ticket',
            'success_message' => 'Support ticket created. We\'ll respond within 24 hours.',
            'redirect_url' => '',
            'skip_database' => false,
            'enable_calendly' => false
        ],
        'property_inquiry' => [
            'name' => 'Property Inquiry',
            'description' => 'Property-specific lead with agent assignment',
            'actions' => ['database', 'followup_boss', 'email_notification', 'agent_notification'],
            'priority' => 12,
            'email_template' => 'property_inquiry',
            'success_message' => 'Thank you for your interest! The listing agent will contact you soon.',
            'redirect_url' => '',
            'skip_database' => false,
            'enable_calendly' => true,
            'calendly_settings' => [
                'duration' => 60,
                'buffer_time' => 30,
                'calendar_type' => 'showing'
            ],
            'conditional_rules' => [
                [
                    'field' => 'listing_price',
                    'operator' => '>',
                    'value' => 1000000,
                    'action' => 'route_to_luxury_team'
                ],
                [
                    'field' => 'time_of_day',
                    'operator' => 'outside_business_hours',
                    'value' => '',
                    'action' => 'route_to_on_call'
                ]
            ]
        ],
        'valuation_request' => [
            'name' => 'Valuation Request',
            'description' => 'High-priority lead with valuation team assignment',
            'actions' => ['database', 'email_notification', 'team_assignment', 'calendly_booking'],
            'priority' => 20,
            'email_template' => 'valuation_request',
            'success_message' => 'We\'ll prepare your property valuation and contact you to schedule a consultation.',
            'redirect_url' => '',
            'skip_database' => false,
            'enable_calendly' => true,
            'calendly_settings' => [
                'duration' => 90,
                'buffer_time' => 30,
                'calendar_type' => 'valuation'
            ]
        ],
        'agent_contact' => [
            'name' => 'Agent Contact',
            'description' => 'Direct contact to specific agent with optional booking',
            'actions' => ['database', 'followup_boss', 'agent_notification', 'email_notification'],
            'priority' => 18,
            'email_template' => 'agent_contact',
            'success_message' => 'Your message has been sent to the agent. They will contact you soon.',
            'redirect_url' => '',
            'skip_database' => false,
            'enable_calendly' => true,
            'calendly_settings' => [
                'duration' => 30,
                'buffer_time' => 15,
                'calendar_type' => 'agent_consultation'
            ]
        ],
        'showing_request' => [
            'name' => 'Showing Request',
            'description' => 'Property showing with calendar scheduling priority',
            'actions' => ['database', 'calendly_booking', 'agent_notification', 'email_notification'],
            'priority' => 25,
            'email_template' => 'showing_request',
            'success_message' => 'Please schedule your showing time using the calendar link we\'ll send you.',
            'redirect_url' => '',
            'skip_database' => false,
            'enable_calendly' => true,
            'calendly_settings' => [
                'duration' => 45,
                'buffer_time' => 15,
                'calendar_type' => 'property_showing'
            ]
        ]
    ];
    
    /**
     * Initialize the Form Router
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        // Load route configurations
        $this->load_route_configurations();
        
        // Load field mappings
        $this->load_field_mappings();
        
        // Initialize integrations
        $this->init_integrations();
        
        // Register unified AJAX handler
        add_action('wp_ajax_hph_route_form', [$this, 'handle_form_submission']);
        add_action('wp_ajax_nopriv_hph_route_form', [$this, 'handle_form_submission']);
        
        // Register Calendly webhook handler
        add_action('wp_ajax_hph_calendly_webhook', [$this, 'handle_calendly_webhook']);
        add_action('wp_ajax_nopriv_hph_calendly_webhook', [$this, 'handle_calendly_webhook']);
        
        // Register compatibility layer for existing forms
        $this->register_compatibility_handlers();
        
        // Note: Admin interface is handled by FormRouterAdmin class
        // Remove this registration as it's handled separately
        
        // Register REST API endpoints
        add_action('rest_api_init', [$this, 'register_rest_endpoints']);
        
        // Initialize email settings
        $this->init_email_settings();
        
        $this->initialized = true;
        $this->log('Form Router initialized successfully');
    }
    
    /**
     * Initialize email notification settings
     */
    private function init_email_settings(): void {
        // Set default email recipients if not configured
        if (!get_option('hp_global_form_recipients')) {
            update_option('hp_global_form_recipients', get_option('admin_email'));
        }
        
        // Set default recipients for each route type
        $default_recipients = [
            'lead_capture' => get_option('admin_email'),
            'property_inquiry' => get_option('admin_email'),
            'valuation_request' => get_option('admin_email'),
            'booking_request' => get_option('admin_email'),
            'support_ticket' => get_option('admin_email'),
            'email_only' => get_option('admin_email')
        ];
        
        foreach ($default_recipients as $route => $default_email) {
            $option_key = "hp_email_recipients_{$route}";
            if (!get_option($option_key)) {
                update_option($option_key, $default_email);
            }
        }
    }
    
    /**
     * Handle unified form submission
     */
    public function handle_form_submission(): void {
        try {
            // Debug logging
            error_log('FormRouter: Form submission received - POST data: ' . print_r($_POST, true));
            
            // Verify nonce
            if (!$this->verify_form_nonce()) {
                error_log('FormRouter: Nonce verification failed');
                wp_send_json_error(['message' => 'Security verification failed']);
                return;
            }
            
            // Extract and normalize form data
            $form_data = $this->extract_form_data($_POST);
            error_log('FormRouter: Extracted form data: ' . print_r($form_data, true));
            
            // Determine route type
            $route_type = $this->determine_route_type($form_data);
            
            // Get route configuration
            $route_config = $this->get_route_config($route_type);
            if (!$route_config) {
                wp_send_json_error(['message' => 'Invalid route configuration']);
                return;
            }
            
            // Validate form data
            $validation_result = $this->validate_form_data($form_data, $route_config);
            if (!$validation_result['valid']) {
                wp_send_json_error(['message' => $validation_result['message']]);
                return;
            }
            
            // Apply conditional routing
            $route_config = $this->apply_conditional_routing($form_data, $route_config);
            
            // Execute routing actions
            $results = $this->execute_route_actions($form_data, $route_config);
            
            // Prepare response
            $response = [
                'success' => true,
                'message' => $route_config['success_message'],
                'results' => $results
            ];
            
            // Add Calendly link if enabled
            if ($route_config['enable_calendly'] && isset($results['calendly_link'])) {
                $response['calendly_link'] = $results['calendly_link'];
                $response['redirect_to_calendly'] = true;
            }
            
            // Add redirect URL if configured
            if (!empty($route_config['redirect_url'])) {
                $response['redirect_url'] = $route_config['redirect_url'];
            }
            
            error_log('FormRouter: Sending success response: ' . print_r($response, true));
            wp_send_json_success($response);
            
        } catch (Exception $e) {
            $this->log('Form submission error: ' . $e->getMessage(), 'error');
            error_log('FormRouter: Exception caught: ' . $e->getMessage());
            wp_send_json_error(['message' => 'An error occurred processing your submission. Please try again.']);
        }
    }
    
    /**
     * Extract and normalize form data from POST
     */
    private function extract_form_data(array $post_data): array {
        $form_data = [];
        
        // Apply field mappings to normalize data
        foreach ($this->field_mappings as $standard_field => $mapping_config) {
            $value = $this->extract_field_value($post_data, $mapping_config);
            if ($value !== null) {
                // Handle split_name transformation which returns an array
                if (is_array($value) && isset($value['first_name']) && isset($value['last_name'])) {
                    $form_data['first_name'] = $value['first_name'];
                    $form_data['last_name'] = $value['last_name'];
                } else {
                    $form_data[$standard_field] = $value;
                }
            }
        }
        
        // Extract additional metadata
        $form_data['_meta'] = [
            'source_url' => esc_url_raw($_SERVER['HTTP_REFERER'] ?? ''),
            'ip_address' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'timestamp' => current_time('mysql'),
            'form_id' => sanitize_text_field($post_data['form_id'] ?? 'unknown'),
            'route_type' => sanitize_text_field($post_data['route_type'] ?? 'auto')
        ];
        
        // Extract UTM parameters
        $utm_params = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        foreach ($utm_params as $param) {
            if (!empty($post_data[$param])) {
                $form_data['_meta'][$param] = sanitize_text_field($post_data[$param]);
            }
        }
        
        return $form_data;
    }
    
    /**
     * Extract field value based on mapping configuration
     */
    private function extract_field_value(array $post_data, array $mapping_config) {
        $sources = $mapping_config['sources'] ?? [];
        $transform = $mapping_config['transform'] ?? null;
        $sanitize = $mapping_config['sanitize'] ?? 'text';
        
        $value = null;
        
        // Try each source until we find a value
        foreach ($sources as $source) {
            if (isset($post_data[$source]) && !empty($post_data[$source])) {
                $value = $post_data[$source];
                break;
            }
        }
        
        if ($value === null) {
            return null;
        }
        
        // Apply sanitization
        switch ($sanitize) {
            case 'email':
                $value = sanitize_email($value);
                break;
            case 'text':
                $value = sanitize_text_field($value);
                break;
            case 'textarea':
                $value = sanitize_textarea_field($value);
                break;
            case 'url':
                $value = esc_url_raw($value);
                break;
            case 'number':
                $value = floatval($value);
                break;
            case 'integer':
                $value = intval($value);
                break;
        }
        
        // Apply transformation if specified
        if ($transform) {
            $value = $this->apply_field_transformation($value, $transform, $post_data);
        }
        
        return $value;
    }
    
    /**
     * Apply field transformation
     */
    private function apply_field_transformation($value, string $transform, array $post_data) {
        switch ($transform) {
            case 'split_name':
                // Split full name into first and last
                $parts = explode(' ', trim($value), 2);
                return [
                    'first_name' => $parts[0] ?? '',
                    'last_name' => $parts[1] ?? ''
                ];
                
            case 'combine_name':
                // Combine first and last name
                $first = sanitize_text_field($post_data['first_name'] ?? '');
                $last = sanitize_text_field($post_data['last_name'] ?? '');
                return trim($first . ' ' . $last);
                
            case 'format_phone':
                // Format phone number
                return preg_replace('/[^0-9]/', '', $value);
                
            case 'capitalize':
                return ucwords(strtolower($value));
                
            case 'uppercase':
                return strtoupper($value);
                
            case 'lowercase':
                return strtolower($value);
        }
        
        return $value;
    }
    
    /**
     * Determine route type based on form data
     */
    private function determine_route_type(array $form_data): string {
        // Check if explicitly specified
        if (!empty($form_data['_meta']['route_type']) && $form_data['_meta']['route_type'] !== 'auto') {
            return $form_data['_meta']['route_type'];
        }
        
        // Auto-detection based on form content and context
        $form_id = $form_data['_meta']['form_id'] ?? 'unknown';
        
        // Form ID based routing
        $form_routes = [
            'contact-form' => 'email_only',
            'agent-contact' => 'lead_capture',
            'property-inquiry' => 'property_inquiry',
            'valuation-request' => 'valuation_request',
            'showing-request' => 'booking_request',
            'support-ticket' => 'support_ticket'
        ];
        
        if (isset($form_routes[$form_id])) {
            return $form_routes[$form_id];
        }
        
        // Content-based detection
        if (!empty($form_data['listing_id']) || !empty($form_data['property_id'])) {
            return 'property_inquiry';
        }
        
        if (!empty($form_data['agent_id'])) {
            return 'lead_capture';
        }
        
        $message = strtolower($form_data['message'] ?? '');
        if (strpos($message, 'valuation') !== false || strpos($message, 'estimate') !== false) {
            return 'valuation_request';
        }
        
        if (strpos($message, 'showing') !== false || strpos($message, 'tour') !== false) {
            return 'booking_request';
        }
        
        if (strpos($message, 'support') !== false || strpos($message, 'help') !== false) {
            return 'support_ticket';
        }
        
        // Default route
        return 'lead_capture';
    }
    
    /**
     * Get route configuration
     */
    private function get_route_config(string $route_type): ?array {
        return $this->routes[$route_type] ?? null;
    }
    
    /**
     * Validate form data against route requirements
     */
    private function validate_form_data(array $form_data, array $route_config): array {
        $errors = [];
        
        // Basic required field validation
        $required_fields = [
            'first_name' => 'First name is required',
            'email' => 'Email address is required',
            'message' => 'Message is required'
        ];
        
        foreach ($required_fields as $field => $error_message) {
            if (empty($form_data[$field])) {
                $errors[] = $error_message;
            }
        }
        
        // Email validation
        if (!empty($form_data['email']) && !is_email($form_data['email'])) {
            $errors[] = 'Please enter a valid email address';
        }
        
        // Route-specific validation
        if (isset($route_config['validation'])) {
            $route_errors = $this->apply_route_validation($form_data, $route_config['validation']);
            $errors = array_merge($errors, $route_errors);
        }
        
        return [
            'valid' => empty($errors),
            'message' => implode(', ', $errors)
        ];
    }
    
    /**
     * Apply conditional routing rules
     */
    private function apply_conditional_routing(array $form_data, array $route_config): array {
        $conditional_rules = $route_config['conditional_rules'] ?? [];
        
        foreach ($conditional_rules as $rule) {
            if ($this->evaluate_conditional_rule($form_data, $rule)) {
                $route_config = $this->apply_conditional_action($route_config, $rule['action'], $form_data);
            }
        }
        
        return $route_config;
    }
    
    /**
     * Execute routing actions
     */
    private function execute_route_actions(array $form_data, array $route_config): array {
        $results = [];
        $actions = $route_config['actions'] ?? [];
        
        foreach ($actions as $action) {
            try {
                switch ($action) {
                    case 'database':
                        if (!$route_config['skip_database']) {
                            $results['lead_id'] = $this->save_to_database($form_data, $route_config);
                        }
                        break;
                        
                    case 'email_notification':
                        $results['email_sent'] = $this->send_email_notification($form_data, $route_config);
                        break;
                        
                    case 'agent_notification':
                        $results['agent_notified'] = $this->send_agent_notification($form_data, $route_config);
                        break;
                        
                    case 'followup_boss':
                        $results['fub_synced'] = $this->sync_to_followup_boss($form_data, $route_config);
                        break;
                        
                    case 'calendly_booking':
                        $results['calendly_link'] = $this->generate_calendly_link($form_data, $route_config);
                        break;
                        
                    case 'team_assignment':
                        $results['team_assigned'] = $this->assign_to_team($form_data, $route_config);
                        break;
                        
                    case 'create_ticket':
                        $results['ticket_id'] = $this->create_support_ticket($form_data, $route_config);
                        break;
                        
                    default:
                        // Allow custom actions via hooks
                        $results[$action] = apply_filters('hph_form_router_custom_action', false, $action, $form_data, $route_config);
                }
                
                $this->log("Action '{$action}' executed successfully");
                
            } catch (Exception $e) {
                $this->log("Action '{$action}' failed: " . $e->getMessage(), 'error');
                $results[$action . '_error'] = $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Load route configurations from database/options
     */
    private function load_route_configurations(): void {
        // Load custom configurations from options
        $custom_routes = get_option('hph_form_router_routes', []);
        
        // Merge with defaults
        $this->routes = array_merge($this->default_routes, $custom_routes);
        
        // Allow plugins/themes to modify routes
        $this->routes = apply_filters('hph_form_router_routes', $this->routes);
    }
    
    /**
     * Load field mapping configurations
     */
    private function load_field_mappings(): void {
        $this->field_mappings = [
            'first_name' => [
                'sources' => ['first_name', 'fname', 'firstName'],
                'sanitize' => 'text',
                'transform' => null
            ],
            'last_name' => [
                'sources' => ['last_name', 'lname', 'lastName'],
                'sanitize' => 'text',
                'transform' => null
            ],
            'full_name' => [
                'sources' => ['name', 'full_name', 'fullName'],
                'sanitize' => 'text',
                'transform' => 'split_name'
            ],
            'email' => [
                'sources' => ['email', 'email_address', 'user_email'],
                'sanitize' => 'email',
                'transform' => null
            ],
            'phone' => [
                'sources' => ['phone', 'phone_number', 'tel'],
                'sanitize' => 'text',
                'transform' => 'format_phone'
            ],
            'message' => [
                'sources' => ['message', 'comments', 'inquiry', 'description'],
                'sanitize' => 'textarea',
                'transform' => null
            ],
            'listing_id' => [
                'sources' => ['listing_id', 'property_id', 'post_id'],
                'sanitize' => 'integer',
                'transform' => null
            ],
            'agent_id' => [
                'sources' => ['agent_id', 'assigned_agent', 'realtor_id'],
                'sanitize' => 'integer',
                'transform' => null
            ],
            'department' => [
                'sources' => ['department', 'inquiry_type', 'help_type', 'subject_type'],
                'sanitize' => 'text',
                'transform' => null
            ],
            'subject' => [
                'sources' => ['subject', 'topic', 'inquiry_subject'],
                'sanitize' => 'text', 
                'transform' => null
            ]
        ];
        
        // Allow customization
        $this->field_mappings = apply_filters('hph_form_router_field_mappings', $this->field_mappings);
    }
    
    /**
     * Initialize integration handlers
     */
    private function init_integrations(): void {
        try {
            // Calendly integration
            if (class_exists('\HappyPlace\Services\CalendlyService')) {
                $this->integrations['calendly'] = new \HappyPlace\Services\CalendlyService();
                $this->log('Calendly integration loaded successfully');
            }
            
            // FollowUp Boss integration (singleton pattern)
            if (class_exists('\HappyPlace\Integrations\FollowUp_Boss_Integration')) {
                $this->integrations['followup_boss'] = \HappyPlace\Integrations\FollowUp_Boss_Integration::get_instance();
                $this->log('FollowUp Boss integration loaded successfully');
            }
            
            // Allow plugins to register additional integrations
            $this->integrations = apply_filters('hph_form_router_integrations', $this->integrations);
            
            $this->log('Form Router integrations initialized: ' . implode(', ', array_keys($this->integrations)));
            
        } catch (\Exception $e) {
            $this->log('Error initializing integrations: ' . $e->getMessage(), 'error');
            // Continue without integrations rather than failing completely
        }
    }
    
    /**
     * Register compatibility handlers for existing forms
     */
    private function register_compatibility_handlers(): void {
        $legacy_handlers = [
            'hph_submit_lead' => 'lead_capture',
            'send_agent_contact' => 'property_inquiry', 
            'send_general_inquiry' => 'email_only',
            'hph_submit_contact_form' => 'email_only',
            'hph_submit_lead_form' => 'lead_capture'
        ];
        
        foreach ($legacy_handlers as $action => $route_type) {
            add_action("wp_ajax_$action", function() use ($route_type) {
                $_POST['route_type'] = $route_type;
                $this->handle_form_submission();
            }, 5);
            
            add_action("wp_ajax_nopriv_$action", function() use ($route_type) {
                $_POST['route_type'] = $route_type;
                $this->handle_form_submission();
            }, 5);
        }
        
        $this->log('Registered compatibility handlers for legacy forms');
    }
    
    /**
     * Verify form nonce security
     */
    private function verify_form_nonce(): bool {
        // Try multiple nonce field names and actions for maximum compatibility
        $possible_nonce_fields = [
            'nonce',
            'contact_nonce', 
            'inquiry_nonce',
            '_wpnonce',
            'security'
        ];
        
        $valid_nonce_actions = [
            'hph_form_router_nonce',
            'hph_lead_nonce',
            'hph_general_contact',
            'hph_property_inquiry',
            'agent_contact_nonce',
            'general_inquiry_nonce',
            'hp_lead_form_nonce',
            'hph_route_form_nonce'
        ];
        
        // Try all combinations of nonce fields and actions
        foreach ($possible_nonce_fields as $field) {
            $nonce_value = $_POST[$field] ?? '';
            if (empty($nonce_value)) continue;
            
            foreach ($valid_nonce_actions as $action) {
                if (wp_verify_nonce($nonce_value, $action)) {
                    error_log("FormRouter: Nonce verified successfully with field '{$field}' and action '{$action}'");
                    return true;
                }
            }
        }
        
        // Log all attempted nonce values for debugging
        error_log('FormRouter: Nonce verification failed. Available nonces: ' . print_r([
            'nonce' => $_POST['nonce'] ?? 'not set',
            'contact_nonce' => $_POST['contact_nonce'] ?? 'not set', 
            'inquiry_nonce' => $_POST['inquiry_nonce'] ?? 'not set',
            '_wpnonce' => $_POST['_wpnonce'] ?? 'not set'
        ], true));
        
        return false;
    }
    
    /**
     * Save form data to database
     */
    private function save_to_database(array $form_data, array $route_config): ?int {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_leads';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $this->log('Leads table does not exist, skipping database save', 'warning');
            return null;
        }
        
        $lead_data = [
            'first_name' => $form_data['first_name'] ?? '',
            'last_name' => $form_data['last_name'] ?? '',
            'email' => $form_data['email'] ?? '',
            'phone' => $form_data['phone'] ?? '',
            'message' => $form_data['message'] ?? '',
            'source' => $route_config['name'] ?? 'form_router',
            'source_url' => $form_data['_meta']['source_url'] ?? '',
            'listing_id' => $form_data['listing_id'] ?? null,
            'agent_id' => $form_data['agent_id'] ?? null,
            'status' => 'new',
            'priority' => $this->calculate_priority($form_data, $route_config),
            'tags' => $this->generate_lead_tags($form_data, $route_config),
            'ip_address' => $form_data['_meta']['ip_address'] ?? '',
            'user_agent' => $form_data['_meta']['user_agent'] ?? '',
            'referrer' => $form_data['_meta']['source_url'] ?? '',
            'utm_source' => $form_data['_meta']['utm_source'] ?? '',
            'utm_medium' => $form_data['_meta']['utm_medium'] ?? '',
            'utm_campaign' => $form_data['_meta']['utm_campaign'] ?? '',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];
        
        // Debug logging
        error_log('FormRouter: Saving lead data: ' . print_r($lead_data, true));
        
        $result = $wpdb->insert($table_name, $lead_data);
        
        if ($result === false) {
            error_log('FormRouter: Database insert failed: ' . $wpdb->last_error);
            throw new \Exception('Failed to save lead to database: ' . $wpdb->last_error);
        }
        
        $lead_id = $wpdb->insert_id;
        error_log('FormRouter: Lead saved successfully with ID: ' . $lead_id);
        
        return $lead_id;
    }
    
    /**
     * Calculate priority based on form data and route config
     */
    private function calculate_priority(array $form_data, array $route_config): string {
        $base_priority = $route_config['priority'] ?? 10;
        
        // Increase priority for certain conditions
        if (!empty($form_data['listing_id'])) {
            $base_priority += 5; // Property inquiries are higher priority
        }
        
        if (!empty($form_data['agent_id'])) {
            $base_priority += 3; // Agent-specific inquiries are higher priority
        }
        
        // Convert to string priority
        if ($base_priority >= 15) {
            return 'high';
        } elseif ($base_priority >= 10) {
            return 'normal';
        } else {
            return 'low';
        }
    }
    
    /**
     * Generate lead tags based on form data and route config
     */
    private function generate_lead_tags(array $form_data, array $route_config): string {
        $tags = [];
        
        // Add route-based tag
        $route_tag = $route_config['name'] ?? '';
        if ($route_tag) {
            $tags[] = str_replace(' ', '_', strtolower($route_tag));
        }
        
        // Add department-based tags from "How can we help you?" dropdown
        if (!empty($form_data['department'])) {
            $department = strtolower($form_data['department']);
            
            // Map departments to more descriptive tags
            $department_tags = [
                'buying' => ['buyer', 'purchase', 'home_search'],
                'selling' => ['seller', 'listing', 'market_analysis'],
                'renting' => ['rental', 'tenant', 'lease'],
                'investment' => ['investor', 'investment_property', 'roi'],
                'commercial' => ['commercial', 'business_property', 'cre'],
                'property_management' => ['property_management', 'landlord', 'rental_management'],
                'market_analysis' => ['market_analysis', 'valuation', 'property_value'],
                'partnership' => ['partnership', 'business', 'collaboration'],
                'website' => ['support', 'technical', 'website'],
                'general' => ['general_inquiry']
            ];
            
            if (isset($department_tags[$department])) {
                $tags = array_merge($tags, $department_tags[$department]);
            } else {
                $tags[] = $department;
            }
        }
        
        // Add property-specific tags
        if (!empty($form_data['listing_id'])) {
            $tags[] = 'property_inquiry';
            $tags[] = 'specific_listing';
        }
        
        // Add agent-specific tags
        if (!empty($form_data['agent_id'])) {
            $tags[] = 'agent_request';
        }
        
        // Add form type tags
        $form_type = $form_data['form_type'] ?? '';
        if ($form_type) {
            $tags[] = str_replace('_', '_', strtolower($form_type));
        }
        
        // Add source tags based on URL
        $source_url = $form_data['_meta']['source_url'] ?? '';
        if ($source_url) {
            if (strpos($source_url, '/contact') !== false) {
                $tags[] = 'contact_page';
            } elseif (strpos($source_url, '/listing') !== false) {
                $tags[] = 'listing_page';
            } elseif (strpos($source_url, '/agent') !== false) {
                $tags[] = 'agent_page';
            }
        }
        
        // Remove duplicates and empty values
        $tags = array_unique(array_filter($tags));
        
        // Convert to comma-separated string for database storage
        return implode(',', $tags);
    }
    
    /**
     * Send email notification
     */
    private function send_email_notification(array $form_data, array $route_config): bool {
        // Get recipients - allow multiple recipients
        $recipients = $this->get_email_recipients($route_config);
        
        if (empty($recipients)) {
            error_log('FormRouter: No email recipients configured for route: ' . ($route_config['name'] ?? 'unknown'));
            return false;
        }
        
        $template = $route_config['email_template'] ?? 'form_submission';
        
        // Build subject with more context
        $subject = $this->build_email_subject($form_data, $route_config);
        
        // Build message with enhanced template
        $message = $this->build_email_message($form_data, $route_config);
        
        // Prepare headers with better formatting
        $from_name = get_option('hp_email_from_name', get_bloginfo('name'));
        $from_email = get_option('hp_email_from_email', 'noreply@' . parse_url(home_url(), PHP_URL_HOST));
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '')) . ' <' . ($form_data['email'] ?? 'noreply@example.com') . '>'
        ];
        
        $success = true;
        $emails_sent = 0;
        
        // Enable debug logging if configured
        $debug_logging = get_option('hp_email_debug_logging', false);
        
        if ($debug_logging) {
            error_log("FormRouter Email Debug: Attempting to send notifications for route: " . ($route_config['name'] ?? 'unknown'));
            error_log("FormRouter Email Debug: Recipients: " . implode(', ', $recipients));
            error_log("FormRouter Email Debug: Subject: " . $subject);
        }
        
        // Add error capture for wp_mail
        add_action('wp_mail_failed', [$this, 'log_mail_error']);
        
        // Send to each recipient
        foreach ($recipients as $recipient) {
            $result = wp_mail($recipient, $subject, $message, $headers);
            if (!$result) {
                error_log("FormRouter: Failed to send email to {$recipient} for route: " . ($route_config['name'] ?? 'unknown'));
                
                // For local development, log the email content as fallback
                if ($this->is_local_development()) {
                    $this->log_email_fallback($recipient, $subject, $message, $form_data);
                }
                
                $success = false;
            } else {
                error_log("FormRouter: Email sent successfully to {$recipient} for route: " . ($route_config['name'] ?? 'unknown'));
                $emails_sent++;
            }
            
            if ($debug_logging) {
                error_log("FormRouter Email Debug: Result for {$recipient}: " . ($result ? 'SUCCESS' : 'FAILED'));
            }
        }
        
        // Remove error capture
        remove_action('wp_mail_failed', [$this, 'log_mail_error']);
        
        // Send copy to customer if enabled
        if (get_option('hp_email_send_copy_to_customer') && !empty($form_data['email'])) {
            $customer_subject = 'Thank you for contacting ' . get_bloginfo('name');
            $customer_message = $this->build_customer_confirmation_email($form_data, $route_config);
            
            $customer_result = wp_mail($form_data['email'], $customer_subject, $customer_message, $headers);
            if ($customer_result) {
                $emails_sent++;
                error_log("FormRouter: Confirmation email sent to customer: " . $form_data['email']);
            } else {
                error_log("FormRouter: Failed to send confirmation email to customer: " . $form_data['email']);
            }
            
            if ($debug_logging) {
                error_log("FormRouter Email Debug: Customer confirmation result: " . ($customer_result ? 'SUCCESS' : 'FAILED'));
            }
        }
        
        // Update email statistics
        if ($emails_sent > 0) {
            $this->update_email_stats($emails_sent);
        }
        
        return $success;
    }
    
    /**
     * Get email recipients for a route
     */
    private function get_email_recipients(array $route_config): array {
        $recipients = [];
        
        // Check route-specific recipients first
        if (isset($route_config['email_recipients'])) {
            if (is_array($route_config['email_recipients'])) {
                $recipients = array_merge($recipients, $route_config['email_recipients']);
            } else {
                $recipients[] = $route_config['email_recipients'];
            }
        }
        
        // Check route-specific options
        $route_name = strtolower(str_replace(' ', '_', $route_config['name'] ?? ''));
        $option_key = "hp_email_recipients_{$route_name}";
        $route_recipients = get_option($option_key, '');
        
        if ($route_recipients) {
            $route_emails = array_map('trim', explode(',', $route_recipients));
            $recipients = array_merge($recipients, $route_emails);
        }
        
        // Global fallback recipients
        $global_recipients = get_option('hp_global_form_recipients', '');
        if ($global_recipients) {
            $global_emails = array_map('trim', explode(',', $global_recipients));
            $recipients = array_merge($recipients, $global_emails);
        }
        
        // Final fallback to admin email
        if (empty($recipients)) {
            $recipients[] = get_option('admin_email');
        }
        
        // Remove duplicates and validate emails
        $recipients = array_unique($recipients);
        $recipients = array_filter($recipients, 'is_email');
        
        return $recipients;
    }
    
    /**
     * Build email subject with context
     */
    private function build_email_subject(array $form_data, array $route_config): string {
        $site_name = get_bloginfo('name');
        $form_type = $route_config['name'] ?? 'Form Submission';
        $customer_name = trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? ''));
        
        // Build subject based on form type
        switch ($route_config['email_template'] ?? 'form_submission') {
            case 'property_inquiry':
                $property_title = '';
                if (!empty($form_data['listing_id'])) {
                    $property_title = get_the_title($form_data['listing_id']);
                }
                return sprintf('🏠 New Property Inquiry%s - %s', 
                    $property_title ? " for {$property_title}" : '', 
                    $site_name
                );
                
            case 'valuation_request':
                return sprintf('💰 New Valuation Request from %s - %s', 
                    $customer_name ?: 'Anonymous', 
                    $site_name
                );
                
            case 'booking_request':
                return sprintf('📅 New Appointment Request from %s - %s', 
                    $customer_name ?: 'Anonymous', 
                    $site_name
                );
                
            case 'support_ticket':
                return sprintf('🎫 New Support Ticket from %s - %s', 
                    $customer_name ?: 'Anonymous', 
                    $site_name
                );
                
            case 'lead_captured':
                return sprintf('🎯 New Lead: %s - %s', 
                    $customer_name ?: 'Anonymous Contact', 
                    $site_name
                );
                
            default:
                return sprintf('📝 New %s from %s - %s', 
                    $form_type, 
                    $customer_name ?: 'Website Visitor', 
                    $site_name
                );
        }
    }
    
    /**
     * Send agent notification
     */
    private function send_agent_notification(array $form_data, array $route_config): bool {
        if (empty($form_data['agent_id'])) {
            return false;
        }
        
        $agent_email = get_field('email', $form_data['agent_id']);
        if (!$agent_email) {
            return false;
        }
        
        $subject = sprintf('New Lead Assignment - %s', get_bloginfo('name'));
        $message = $this->build_agent_email_message($form_data, $route_config);
        
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <noreply@' . parse_url(home_url(), PHP_URL_HOST) . '>',
            'Reply-To: ' . ($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '') . ' <' . ($form_data['email'] ?? '') . '>'
        ];
        
        return wp_mail($agent_email, $subject, $message, $headers);
    }
    
    /**
     * Sync to FollowUp Boss
     */
    private function sync_to_followup_boss(array $form_data, array $route_config): bool {
        if (!class_exists('HappyPlace\\Integrations\\FollowUp_Boss_Integration')) {
            error_log('FormRouter: FollowUp Boss Integration class not found');
            return false;
        }
        
        $fub_data = [
            'first_name' => $form_data['first_name'] ?? '',
            'last_name' => $form_data['last_name'] ?? '',
            'email' => $form_data['email'] ?? '',
            'phone' => $form_data['phone'] ?? '',
            'message' => $form_data['message'] ?? '',
            'source' => $route_config['name'] ?? 'form_router',
            'listing_id' => $form_data['listing_id'] ?? null,
            'agent_id' => $form_data['agent_id'] ?? null
        ];
        
        error_log('FormRouter: Triggering hp_lead_captured action with data: ' . print_r($fub_data, true));
        do_action('hp_lead_captured', $fub_data, $form_data['listing_id'] ?? null);
        error_log('FormRouter: hp_lead_captured action completed');
        
        return true;
    }
    
    /**
     * Generate Calendly link
     */
    private function generate_calendly_link(array $form_data, array $route_config): string {
        if (isset($this->integrations['calendly'])) {
            return $this->integrations['calendly']->generate_booking_link($form_data, $route_config);
        }
        
        return '';
    }
    
    /**
     * Assign to team
     */
    private function assign_to_team(array $form_data, array $route_config): bool {
        // Custom team assignment logic based on route configuration
        $team_assignment_rules = $route_config['team_assignment'] ?? [];
        
        foreach ($team_assignment_rules as $rule) {
            if ($this->evaluate_conditional_rule($form_data, $rule)) {
                $team_id = $rule['team_id'] ?? null;
                if ($team_id) {
                    // Update lead assignment
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'hp_leads';
                    
                    $wpdb->update(
                        $table_name,
                        ['assigned_to' => $team_id],
                        ['email' => $form_data['email']],
                        ['%d'],
                        ['%s']
                    );
                    
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Create support ticket
     */
    private function create_support_ticket(array $form_data, array $route_config): int {
        // Basic support ticket creation
        global $wpdb;
        $table_name = $wpdb->prefix . 'hp_support_tickets';
        
        // Create table if it doesn't exist
        $this->create_support_tickets_table();
        
        $ticket_data = [
            'subject' => 'Support Request from ' . ($form_data['first_name'] ?? 'Unknown'),
            'description' => $form_data['message'] ?? '',
            'email' => $form_data['email'] ?? '',
            'name' => trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '')),
            'priority' => $this->calculate_priority($form_data, $route_config),
            'status' => 'open',
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert($table_name, $ticket_data);
        
        return $result ? $wpdb->insert_id : 0;
    }
    
    /**
     * Evaluate conditional rule
     */
    private function evaluate_conditional_rule(array $form_data, array $rule): bool {
        $field = $rule['field'] ?? '';
        $operator = $rule['operator'] ?? '';
        $value = $rule['value'] ?? '';
        
        $form_value = $this->get_field_value($form_data, $field);
        
        switch ($operator) {
            case '==':
                return $form_value == $value;
            case '!=':
                return $form_value != $value;
            case '>':
                return floatval($form_value) > floatval($value);
            case '<':
                return floatval($form_value) < floatval($value);
            case '>=':
                return floatval($form_value) >= floatval($value);
            case '<=':
                return floatval($form_value) <= floatval($value);
            case 'contains':
                return strpos(strtolower($form_value), strtolower($value)) !== false;
            case 'not_contains':
                return strpos(strtolower($form_value), strtolower($value)) === false;
            case 'outside_business_hours':
                return $this->is_outside_business_hours();
            case 'is_weekend':
                return $this->is_weekend();
            default:
                return false;
        }
    }
    
    /**
     * Apply conditional action
     */
    private function apply_conditional_action(array $route_config, string $action, array $form_data): array {
        switch ($action) {
            case 'route_to_luxury_team':
                $route_config['actions'][] = 'team_assignment';
                $route_config['team_assignment'] = [
                    ['team_id' => get_option('hp_luxury_team_id', 1)]
                ];
                break;
                
            case 'route_to_on_call':
                $route_config['actions'][] = 'team_assignment';
                $route_config['team_assignment'] = [
                    ['team_id' => get_option('hp_on_call_agent_id', 1)]
                ];
                break;
                
            case 'increase_priority':
                $route_config['priority'] = ($route_config['priority'] ?? 10) + 5;
                break;
                
            case 'add_calendly':
                if (!in_array('calendly_booking', $route_config['actions'])) {
                    $route_config['actions'][] = 'calendly_booking';
                    $route_config['enable_calendly'] = true;
                }
                break;
        }
        
        return $route_config;
    }
    
    /**
     * Get field value from form data with dot notation support
     */
    private function get_field_value(array $form_data, string $field) {
        // Support dot notation for nested values
        if (strpos($field, '.') !== false) {
            $keys = explode('.', $field);
            $value = $form_data;
            
            foreach ($keys as $key) {
                $value = $value[$key] ?? null;
                if ($value === null) {
                    break;
                }
            }
            
            return $value;
        }
        
        // Special field handling
        switch ($field) {
            case 'listing_price':
                if (!empty($form_data['listing_id'])) {
                    return floatval(get_field('price', $form_data['listing_id']));
                }
                return 0;
                
            case 'time_of_day':
                return current_time('H:i');
                
            default:
                return $form_data[$field] ?? null;
        }
    }
    
    /**
     * Check if outside business hours
     */
    private function is_outside_business_hours(): bool {
        $current_hour = intval(current_time('H'));
        $start_hour = intval(get_option('hp_business_start_hour', 9));
        $end_hour = intval(get_option('hp_business_end_hour', 17));
        
        return $current_hour < $start_hour || $current_hour >= $end_hour;
    }
    
    /**
     * Check if weekend
     */
    private function is_weekend(): bool {
        $day_of_week = current_time('w'); // 0 = Sunday, 6 = Saturday
        return $day_of_week == 0 || $day_of_week == 6;
    }
    
    /**
     * Apply route validation
     */
    private function apply_route_validation(array $form_data, array $validation_rules): array {
        $errors = [];
        
        foreach ($validation_rules as $rule) {
            $field = $rule['field'] ?? '';
            $type = $rule['type'] ?? 'required';
            $message = $rule['message'] ?? "Field {$field} validation failed";
            
            $value = $form_data[$field] ?? '';
            
            switch ($type) {
                case 'required':
                    if (empty($value)) {
                        $errors[] = $message;
                    }
                    break;
                    
                case 'min_length':
                    if (strlen($value) < ($rule['value'] ?? 0)) {
                        $errors[] = $message;
                    }
                    break;
                    
                case 'max_length':
                    if (strlen($value) > ($rule['value'] ?? 255)) {
                        $errors[] = $message;
                    }
                    break;
                    
                case 'numeric':
                    if (!is_numeric($value)) {
                        $errors[] = $message;
                    }
                    break;
                    
                case 'phone':
                    if (!preg_match('/^[\+]?[1-9][\d]{0,15}$/', preg_replace('/[^0-9+]/', '', $value))) {
                        $errors[] = $message;
                    }
                    break;
            }
        }
        
        return $errors;
    }
    
    /**
     * Build email message
     */
    /**
     * Build email message with HTML formatting
     */
    private function build_email_message(array $form_data, array $route_config): string {
        $name = trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? ''));
        $form_type = $route_config['name'] ?? 'Form Submission';
        $site_name = get_bloginfo('name');
        $admin_url = admin_url('admin.php?page=happy-place-leads');
        
        // Start HTML email
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . esc_html($form_type) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .section { margin-bottom: 25px; }
        .section h3 { color: #2563eb; margin-bottom: 10px; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; }
        .info-row { display: flex; margin-bottom: 8px; }
        .info-label { font-weight: bold; min-width: 100px; color: #6b7280; }
        .info-value { flex: 1; }
        .message-box { background: #f8fafc; border-left: 4px solid #3b82f6; padding: 15px; margin: 15px 0; }
        .property-info { background: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; margin: 15px 0; }
        .cta-button { display: inline-block; background: #2563eb; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; margin: 15px 0; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
        .urgent { border-left-color: #ef4444 !important; background: #fef2f2; }
        @media (max-width: 600px) {
            .container { margin: 0; border-radius: 0; }
            .content { padding: 20px; }
            .info-row { flex-direction: column; }
            .info-label { min-width: auto; margin-bottom: 2px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 ' . esc_html($form_type) . '</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">New submission from ' . esc_html($site_name) . '</p>
        </div>
        
        <div class="content">
            <div class="section">
                <h3>👤 Contact Information</h3>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">' . esc_html($name ?: 'Not provided') . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><a href="mailto:' . esc_attr($form_data['email'] ?? '') . '">' . esc_html($form_data['email'] ?? 'Not provided') . '</a></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">' . ($form_data['phone'] ? '<a href="tel:' . esc_attr($form_data['phone']) . '">' . esc_html($form_data['phone']) . '</a>' : 'Not provided') . '</span>
                </div>
            </div>';
            
        // Add message section
        if (!empty($form_data['message'])) {
            $html .= '<div class="section">
                <h3>💬 Message</h3>
                <div class="message-box">
                    ' . nl2br(esc_html($form_data['message'])) . '
                </div>
            </div>';
        }
        
        // Add property information if available
        if (!empty($form_data['listing_id'])) {
            $listing_title = get_the_title($form_data['listing_id']);
            $listing_address = get_field('address', $form_data['listing_id']);
            $listing_price = get_field('price', $form_data['listing_id']);
            $listing_url = get_permalink($form_data['listing_id']);
            
            $html .= '<div class="section">
                <h3>🏠 Property Information</h3>
                <div class="property-info">
                    <div class="info-row">
                        <span class="info-label">Property:</span>
                        <span class="info-value"><strong>' . esc_html($listing_title) . '</strong></span>
                    </div>';
                    
            if ($listing_address) {
                $html .= '<div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">' . esc_html($listing_address) . '</span>
                </div>';
            }
            
            if ($listing_price) {
                $html .= '<div class="info-row">
                    <span class="info-label">Price:</span>
                    <span class="info-value">$' . number_format($listing_price) . '</span>
                </div>';
            }
            
            if ($listing_url) {
                $html .= '<div style="margin-top: 10px;">
                    <a href="' . esc_url($listing_url) . '" class="cta-button">View Property</a>
                </div>';
            }
            
            $html .= '</div>
            </div>';
        }
        
        // Add agent information if available
        if (!empty($form_data['agent_id'])) {
            $agent_name = get_the_title($form_data['agent_id']);
            $agent_email = get_field('email', $form_data['agent_id']);
            $agent_phone = get_field('phone', $form_data['agent_id']);
            
            $html .= '<div class="section">
                <h3>👨‍💼 Assigned Agent</h3>
                <div class="info-row">
                    <span class="info-label">Agent:</span>
                    <span class="info-value">' . esc_html($agent_name) . '</span>
                </div>';
                
            if ($agent_email) {
                $html .= '<div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><a href="mailto:' . esc_attr($agent_email) . '">' . esc_html($agent_email) . '</a></span>
                </div>';
            }
            
            if ($agent_phone) {
                $html .= '<div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><a href="tel:' . esc_attr($agent_phone) . '">' . esc_html($agent_phone) . '</a></span>
                </div>';
            }
            
            $html .= '</div>';
        }
        
        // Add submission details
        $html .= '<div class="section">
            <h3>📊 Submission Details</h3>
            <div class="info-row">
                <span class="info-label">Form Type:</span>
                <span class="info-value">' . esc_html($form_type) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Source Page:</span>
                <span class="info-value">' . esc_html($form_data['_meta']['source_url'] ?? 'Direct submission') . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">IP Address:</span>
                <span class="info-value">' . esc_html($form_data['_meta']['ip_address'] ?? 'Unknown') . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Submitted:</span>
                <span class="info-value">' . current_time('F j, Y g:i A T') . '</span>
            </div>
        </div>';
        
        // Add action buttons
        $html .= '<div class="section" style="text-align: center;">
            <a href="' . esc_url($admin_url) . '" class="cta-button">View in Dashboard</a>
        </div>
        
        </div>
        
        <div class="footer">
            <p>This email was automatically generated by the ' . esc_html($site_name) . ' contact form system.</p>
            <p><strong>⚡ Quick Response Required:</strong> Customer inquiries should be responded to within 15 minutes for best conversion rates.</p>
        </div>
    </div>
</body>
</html>';

        return $html;
    }
    
    /**
     * Build customer confirmation email
     */
    private function build_customer_confirmation_email(array $form_data, array $route_config): string {
        $name = trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? ''));
        $site_name = get_bloginfo('name');
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - ' . esc_html($site_name) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: #10b981; color: white; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .section { margin-bottom: 25px; }
        .highlight-box { background: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; margin: 15px 0; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Thank You!</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Your message has been received</p>
        </div>
        
        <div class="content">
            <div class="section">
                <h3>Hi ' . esc_html($name ?: 'there') . '!</h3>
                <p>Thank you for contacting <strong>' . esc_html($site_name) . '</strong>. We\'ve received your message and will respond as soon as possible.</p>
            </div>
            
            <div class="highlight-box">
                <h4>📞 What happens next?</h4>
                <ul>
                    <li><strong>Response Time:</strong> We typically respond within 15 minutes during business hours</li>
                    <li><strong>Contact Method:</strong> We\'ll reach out via ' . (!empty($form_data['phone']) ? 'phone or email' : 'email') . '</li>
                    <li><strong>Next Steps:</strong> ' . $this->get_next_steps_message($route_config) . '</li>
                </ul>
            </div>
            
            <div class="section">
                <h4>📋 Your Message Summary</h4>
                <p><strong>Submitted:</strong> ' . current_time('F j, Y g:i A T') . '</p>';
                
        if (!empty($form_data['message'])) {
            $html .= '<p><strong>Your Message:</strong><br>' . nl2br(esc_html($form_data['message'])) . '</p>';
        }
        
        // Add property info if applicable
        if (!empty($form_data['listing_id'])) {
            $listing_title = get_the_title($form_data['listing_id']);
            $html .= '<p><strong>Property of Interest:</strong> ' . esc_html($listing_title) . '</p>';
        }
        
        $html .= '</div>
            
            <div class="section">
                <h4>📞 Need Immediate Assistance?</h4>
                <p>If you need immediate assistance, please don\'t hesitate to call us:</p>';
                
        $phone = get_option('hp_office_phone', '');
        if ($phone) {
            $html .= '<p><strong>Phone:</strong> <a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a></p>';
        }
        
        $email = get_option('hp_office_email', get_option('admin_email'));
        $html .= '<p><strong>Email:</strong> <a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></p>
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated confirmation email from ' . esc_html($site_name) . '.</p>
            <p>Please do not reply to this email. If you need to reach us, use the contact information above.</p>
        </div>
    </div>
</body>
</html>';

        return $html;
    }
    
    /**
     * Get next steps message based on route config
     */
    private function get_next_steps_message(array $route_config): string {
        switch ($route_config['email_template'] ?? 'form_submission') {
            case 'property_inquiry':
                return 'The listing agent will contact you with property details and availability';
                
            case 'valuation_request':
                return 'We\'ll prepare your property valuation and schedule a consultation';
                
            case 'booking_request':
                return 'We\'ll confirm your appointment and send calendar details';
                
            case 'support_ticket':
                return 'Our support team will investigate and provide a solution';
                
            default:
                return 'We\'ll review your inquiry and provide a personalized response';
        }
    }
    
    /**
     * Update email statistics
     */
    private function update_email_stats(int $count): void {
        // Update daily count
        $today_key = 'hp_emails_sent_today';
        $today_count = get_transient($today_key) ?: 0;
        set_transient($today_key, $today_count + $count, DAY_IN_SECONDS);
        
        // Update weekly count
        $week_key = 'hp_emails_sent_week';
        $week_count = get_transient($week_key) ?: 0;
        set_transient($week_key, $week_count + $count, WEEK_IN_SECONDS);
        
        // Update monthly count
        $month_key = 'hp_emails_sent_month';
        $month_count = get_transient($month_key) ?: 0;
        set_transient($month_key, $month_count + $count, MONTH_IN_SECONDS);
        
        // Update last sent timestamp
        update_option('hp_last_email_sent', current_time('F j, Y g:i A'));
    }
    
    /**
     * Log wp_mail errors
     */
    public function log_mail_error($wp_error): void {
        error_log('FormRouter Mail Error: ' . $wp_error->get_error_message());
        
        if (get_option('hp_email_debug_logging', false)) {
            error_log('FormRouter Email Debug: Detailed mail error: ' . print_r($wp_error->get_error_data(), true));
        }
    }
    
    /**
     * Check if running in local development environment
     */
    private function is_local_development(): bool {
        $host = parse_url(home_url(), PHP_URL_HOST);
        return in_array($host, ['localhost', '127.0.0.1', '::1']) || strpos($host, '.local') !== false;
    }
    
    /**
     * Log email content as fallback for local development
     */
    private function log_email_fallback(string $recipient, string $subject, string $message, array $form_data): void {
        $log_entry = "\n" . str_repeat('=', 80) . "\n";
        $log_entry .= "EMAIL FALLBACK LOG - " . current_time('Y-m-d H:i:s') . "\n";
        $log_entry .= str_repeat('=', 80) . "\n";
        $log_entry .= "TO: {$recipient}\n";
        $log_entry .= "SUBJECT: {$subject}\n";
        $log_entry .= "FORM TYPE: " . ($form_data['form_type'] ?? 'unknown') . "\n";
        $log_entry .= "CUSTOMER: " . trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '')) . "\n";
        $log_entry .= "CUSTOMER EMAIL: " . ($form_data['email'] ?? 'none') . "\n";
        $log_entry .= str_repeat('-', 80) . "\n";
        $log_entry .= "MESSAGE CONTENT:\n";
        $log_entry .= strip_tags($message) . "\n";
        $log_entry .= str_repeat('=', 80) . "\n\n";
        
        error_log($log_entry);
        
        // Also create a simple HTML file for easy viewing (local dev only)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $upload_dir = wp_upload_dir();
            $log_file = $upload_dir['basedir'] . '/email-fallback-log.html';
            
            $html_log = '';
            if (file_exists($log_file)) {
                $html_log = file_get_contents($log_file);
            } else {
                $html_log = '<!DOCTYPE html><html><head><title>Email Fallback Log</title><style>
                body { font-family: monospace; margin: 20px; }
                .email-entry { border: 1px solid #ccc; margin: 10px 0; padding: 15px; background: #f9f9f9; }
                .email-header { background: #e1ecf4; padding: 10px; margin: -15px -15px 15px -15px; }
                .email-content { white-space: pre-wrap; }
                </style></head><body><h1>Email Fallback Log (Local Development)</h1>';
            }
            
            $html_log .= '<div class="email-entry">';
            $html_log .= '<div class="email-header">';
            $html_log .= '<strong>Date:</strong> ' . current_time('Y-m-d H:i:s') . '<br>';
            $html_log .= '<strong>To:</strong> ' . esc_html($recipient) . '<br>';
            $html_log .= '<strong>Subject:</strong> ' . esc_html($subject) . '<br>';
            $html_log .= '<strong>Form Type:</strong> ' . esc_html($form_data['form_type'] ?? 'unknown') . '<br>';
            $html_log .= '<strong>Customer:</strong> ' . esc_html(trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? ''))) . '<br>';
            $html_log .= '</div>';
            $html_log .= '<div class="email-content">' . $message . '</div>';
            $html_log .= '</div>';
            
            $html_log .= '</body></html>';
            
            file_put_contents($log_file, $html_log);
        }
    }
    
    /**
     * Build agent email message
     */
    private function build_agent_email_message(array $form_data, array $route_config): string {
        $name = trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? ''));
        
        $message = sprintf(
            "You have been assigned a new lead.\n\n" .
            "Contact Information:\n" .
            "Name: %s\n" .
            "Email: %s\n" .
            "Phone: %s\n\n" .
            "Message:\n%s\n\n" .
            "Please contact this lead as soon as possible.\n\n" .
            "Lead submitted: %s\n",
            $name,
            $form_data['email'] ?? 'Not provided',
            $form_data['phone'] ?? 'Not provided',
            $form_data['message'] ?? 'No message provided',
            current_time('F j, Y g:i A')
        );
        
        return $message;
    }
    
    /**
     * Create support tickets table
     */
    private function create_support_tickets_table(): void {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hp_support_tickets';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            subject varchar(255) NOT NULL,
            description text,
            email varchar(255) NOT NULL,
            name varchar(255),
            priority varchar(20) DEFAULT 'normal',
            status varchar(20) DEFAULT 'open',
            assigned_to int(11),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_status (status),
            KEY idx_priority (priority),
            KEY idx_created (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Register REST API endpoints
     */
    public function register_rest_endpoints(): void {
        register_rest_route('hph/v1', '/form-router/submit', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_rest_form_submission'],
            'permission_callback' => '__return_true',
            'args' => [
                'nonce' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'route_type' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        register_rest_route('hph/v1', '/form-router/routes', [
            'methods' => 'GET',
            'callback' => [$this, 'get_available_routes'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ]);
    }
    
    /**
     * Handle REST API form submission
     */
    public function handle_rest_form_submission(\WP_REST_Request $request): \WP_REST_Response {
        // Convert REST request to $_POST format for compatibility
        $params = $request->get_params();
        $_POST = array_merge($_POST, $params);
        
        // Call the main handler
        ob_start();
        $this->handle_form_submission();
        $output = ob_get_clean();
        
        // Parse the JSON response
        $response_data = json_decode($output, true);
        
        if ($response_data && isset($response_data['success'])) {
            if ($response_data['success']) {
                return new \WP_REST_Response($response_data['data'] ?? [], 200);
            } else {
                return new \WP_REST_Response(['error' => $response_data['data']['message'] ?? 'Unknown error'], 400);
            }
        }
        
        return new \WP_REST_Response(['error' => 'Invalid response'], 500);
    }
    
    /**
     * Get available routes for API
     */
    public function get_available_routes(\WP_REST_Request $request): \WP_REST_Response {
        $routes = $this->routes;
        
        // Remove sensitive configuration data
        foreach ($routes as &$route) {
            unset($route['conditional_rules']);
            unset($route['team_assignment']);
        }
        
        return new \WP_REST_Response($routes, 200);
    }
}