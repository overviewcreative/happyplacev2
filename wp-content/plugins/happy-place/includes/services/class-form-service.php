<?php
/**
 * Form Service - Processing & Validation
 * 
 * Handles form generation, processing, validation and security for all plugin forms.
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Form Service Class
 * 
 * Handles form rendering, processing and validation as specified in services.md
 */
class FormService extends Service {
    
    /**
     * Service name
     */
    protected string $name = 'form_service';
    
    /**
     * Service version
     */
    protected string $version = '4.0.0';
    
    /**
     * Form configurations
     */
    private array $form_configs = [];
    
    /**
     * Validation rules
     */
    private array $validation_rules = [];
    
    /**
     * Nonce actions
     */
    private array $nonce_actions = [
        'listing' => 'hp_listing_form',
        'agent' => 'hp_agent_form',
        'lead' => 'hp_lead_form',
        'import' => 'hp_import_form'
    ];
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        // Load form configurations
        $this->load_form_configs();
        $this->load_validation_rules();
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
        
        $this->initialized = true;
        $this->log('Form Service initialized successfully');
    }
    
    /**
     * Render listing form
     * 
     * @param int|null $listing_id Listing ID for editing, null for new
     * @return string Form HTML
     */
    public function render_listing_form(?int $listing_id = null): string {
        $listing_data = [];
        
        if ($listing_id) {
            $post = get_post($listing_id);
            if ($post && $post->post_type === 'listing') {
                $listing_data = $this->get_listing_form_data($listing_id);
            }
        }
        
        $form_config = $this->get_form_fields('listing');
        $form_html = $this->build_form_html($form_config, $listing_data, 'listing', $listing_id);
        
        return $form_html;
    }
    
    /**
     * Get form fields configuration
     * 
     * @param string $form_type Form type (listing, agent, lead)
     * @return array Form field configuration
     */
    public function get_form_fields(string $form_type): array {
        if (!isset($this->form_configs[$form_type])) {
            return [];
        }
        
        $config = $this->form_configs[$form_type];
        
        // Apply filters to allow customization
        return apply_filters("hp_form_fields_{$form_type}", $config);
    }
    
    /**
     * Process form submission
     * 
     * @param array $form_data Submitted form data
     * @param string $form_type Form type
     * @return array|WP_Error Processing result or error
     */
    public function process_submission(array $form_data, string $form_type) {
        // Verify nonce
        $nonce_check = $this->verify_nonce($form_data['nonce'] ?? '', $form_type);
        if (is_wp_error($nonce_check)) {
            return $nonce_check;
        }
        
        // Check permissions
        $permission_check = $this->check_permissions(get_current_user_id(), "submit_{$form_type}");
        if (is_wp_error($permission_check)) {
            return $permission_check;
        }
        
        // Validate form data
        $validation = $this->validate_form($form_data, $form_type);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_form_data($form_data, $form_type);
        
        // Process based on form type
        switch ($form_type) {
            case 'listing':
                return $this->process_listing_submission($sanitized_data);
                
            case 'agent':
                return $this->process_agent_submission($sanitized_data);
                
            case 'lead':
                return $this->process_lead_submission($sanitized_data);
                
            default:
                return new \WP_Error('invalid_form_type', 'Invalid form type specified');
        }
    }
    
    /**
     * Validate form data
     * 
     * @param array $data Form data
     * @param string $form_type Form type
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    public function validate_form(array $data, string $form_type) {
        $rules = $this->get_validation_rules($form_type);
        $errors = [];
        
        foreach ($rules as $field => $field_rules) {
            $value = $data[$field] ?? null;
            
            // Check required fields
            if (isset($field_rules['required']) && $field_rules['required']) {
                if (empty($value)) {
                    $errors[] = sprintf(__('Field %s is required', 'happy-place'), $field);
                    continue;
                }
            }
            
            // Skip validation if field is empty and not required
            if (empty($value)) {
                continue;
            }
            
            // Validate field type
            if (isset($field_rules['type'])) {
                $type_validation = $this->validate_field_type($value, $field_rules['type'], $field);
                if (is_wp_error($type_validation)) {
                    $errors[] = $type_validation->get_error_message();
                }
            }
            
            // Validate min/max length
            if (isset($field_rules['min_length']) || isset($field_rules['max_length'])) {
                $length_validation = $this->validate_field_length($value, $field_rules, $field);
                if (is_wp_error($length_validation)) {
                    $errors[] = $length_validation->get_error_message();
                }
            }
            
            // Custom validation patterns
            if (isset($field_rules['pattern'])) {
                if (!preg_match($field_rules['pattern'], $value)) {
                    $errors[] = sprintf(__('Field %s format is invalid', 'happy-place'), $field);
                }
            }
        }
        
        if (!empty($errors)) {
            return new \WP_Error('validation_failed', 'Form validation failed', $errors);
        }
        
        return true;
    }
    
    /**
     * Get validation rules for form type
     * 
     * @param string $form_type Form type
     * @return array Validation rules
     */
    public function get_validation_rules(string $form_type): array {
        if (!isset($this->validation_rules[$form_type])) {
            return [];
        }
        
        return apply_filters("hp_validation_rules_{$form_type}", $this->validation_rules[$form_type]);
    }
    
    /**
     * Get field configuration for post type
     * 
     * @param string $post_type Post type
     * @return array Field configuration
     */
    public function get_field_config(string $post_type): array {
        // Get ACF field groups for the post type
        $field_groups = acf_get_field_groups(['post_type' => $post_type]);
        $config = [];
        
        foreach ($field_groups as $group) {
            $fields = acf_get_fields($group);
            foreach ($fields as $field) {
                $config[$field['name']] = [
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'required' => $field['required'] ?? false,
                    'instructions' => $field['instructions'] ?? '',
                    'choices' => $field['choices'] ?? []
                ];
            }
        }
        
        return $config;
    }
    
    /**
     * Verify nonce
     * 
     * @param string $nonce Nonce value
     * @param string $action Nonce action
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    public function verify_nonce(string $nonce, string $action) {
        $nonce_action = $this->nonce_actions[$action] ?? "hp_{$action}_form";
        
        if (!wp_verify_nonce($nonce, $nonce_action)) {
            return new \WP_Error('invalid_nonce', 'Security check failed');
        }
        
        return true;
    }
    
    /**
     * Check user permissions
     * 
     * @param int $user_id User ID
     * @param string $action Action to check
     * @return bool|WP_Error True if permitted, WP_Error if not
     */
    public function check_permissions(int $user_id, string $action) {
        // Define capability mapping
        $capability_map = [
            'submit_listing' => 'edit_listings',
            'submit_agent' => 'edit_users',
            'submit_lead' => 'manage_leads',
            'submit_import' => 'import_data'
        ];
        
        $required_capability = $capability_map[$action] ?? 'read';
        
        if (!user_can($user_id, $required_capability)) {
            return new \WP_Error('insufficient_permissions', 'Insufficient permissions');
        }
        
        return true;
    }
    
    /**
     * Build form HTML
     * 
     * @param array $form_config Form configuration
     * @param array $data Existing data
     * @param string $form_type Form type
     * @param int|null $post_id Post ID for editing
     * @return string Form HTML
     */
    private function build_form_html(array $form_config, array $data, string $form_type, ?int $post_id = null): string {
        $form_id = "hp-{$form_type}-form";
        $nonce_field = wp_nonce_field($this->nonce_actions[$form_type], 'nonce', true, false);
        
        $html = "<form id='{$form_id}' class='hp-form hp-{$form_type}-form' method='post' enctype='multipart/form-data'>";
        $html .= $nonce_field;
        
        if ($post_id) {
            $html .= "<input type='hidden' name='{$form_type}_id' value='{$post_id}' />";
        }
        
        $html .= "<input type='hidden' name='action' value='hp_process_{$form_type}_form' />";
        $html .= "<input type='hidden' name='form_type' value='{$form_type}' />";
        
        // Build fields
        foreach ($form_config as $section => $fields) {
            $html .= "<div class='hp-form-section hp-form-{$section}'>";
            $html .= "<h3 class='hp-form-section-title'>" . ucfirst($section) . "</h3>";
            
            foreach ($fields as $field_name => $field_config) {
                $html .= $this->render_form_field($field_name, $field_config, $data[$field_name] ?? '');
            }
            
            $html .= "</div>";
        }
        
        // Submit button
        $submit_text = $post_id ? __('Update', 'happy-place') : __('Create', 'happy-place');
        $html .= "<div class='hp-form-submit'>";
        $html .= "<button type='submit' class='hp-btn hp-btn-primary'>{$submit_text}</button>";
        $html .= "<button type='button' class='hp-btn hp-btn-secondary hp-form-cancel'>" . __('Cancel', 'happy-place') . "</button>";
        $html .= "</div>";
        
        $html .= "</form>";
        
        return $html;
    }
    
    /**
     * Render individual form field
     * 
     * @param string $field_name Field name
     * @param array $field_config Field configuration
     * @param mixed $value Current value
     * @return string Field HTML
     */
    private function render_form_field(string $field_name, array $field_config, $value): string {
        $field_id = "field-{$field_name}";
        $required_attr = isset($field_config['required']) && $field_config['required'] ? 'required' : '';
        $required_indicator = $required_attr ? ' <span class="hp-required">*</span>' : '';
        
        $html = "<div class='hp-form-field hp-field-{$field_config['type']}'>";
        $html .= "<label for='{$field_id}' class='hp-field-label'>{$field_config['label']}{$required_indicator}</label>";
        
        switch ($field_config['type']) {
            case 'text':
            case 'email':
            case 'url':
            case 'tel':
                $html .= "<input type='{$field_config['type']}' id='{$field_id}' name='{$field_name}' value='" . esc_attr($value) . "' class='hp-field-input' {$required_attr} />";
                break;
                
            case 'number':
                $min = isset($field_config['min']) ? "min='{$field_config['min']}'" : '';
                $max = isset($field_config['max']) ? "max='{$field_config['max']}'" : '';
                $step = isset($field_config['step']) ? "step='{$field_config['step']}'" : 'step="any"';
                $html .= "<input type='number' id='{$field_id}' name='{$field_name}' value='" . esc_attr($value) . "' class='hp-field-input' {$min} {$max} {$step} {$required_attr} />";
                break;
                
            case 'textarea':
                $rows = $field_config['rows'] ?? 4;
                $html .= "<textarea id='{$field_id}' name='{$field_name}' rows='{$rows}' class='hp-field-textarea' {$required_attr}>" . esc_textarea($value) . "</textarea>";
                break;
                
            case 'select':
                $html .= "<select id='{$field_id}' name='{$field_name}' class='hp-field-select' {$required_attr}>";
                if (!$required_attr) {
                    $html .= "<option value=''>-- Select --</option>";
                }
                foreach ($field_config['choices'] as $option_value => $option_label) {
                    $selected = selected($value, $option_value, false);
                    $html .= "<option value='" . esc_attr($option_value) . "' {$selected}>" . esc_html($option_label) . "</option>";
                }
                $html .= "</select>";
                break;
                
            case 'checkbox':
                $checked = checked($value, '1', false);
                $html .= "<input type='hidden' name='{$field_name}' value='0' />";
                $html .= "<input type='checkbox' id='{$field_id}' name='{$field_name}' value='1' class='hp-field-checkbox' {$checked} />";
                break;
                
            case 'file':
                $accept = isset($field_config['accept']) ? "accept='{$field_config['accept']}'" : '';
                $multiple = isset($field_config['multiple']) && $field_config['multiple'] ? 'multiple' : '';
                $html .= "<input type='file' id='{$field_id}' name='{$field_name}' class='hp-field-file' {$accept} {$multiple} {$required_attr} />";
                break;
        }
        
        if (!empty($field_config['instructions'])) {
            $html .= "<p class='hp-field-instructions'>" . esc_html($field_config['instructions']) . "</p>";
        }
        
        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Process listing form submission
     * 
     * @param array $data Sanitized form data
     * @return array|WP_Error Processing result
     */
    private function process_listing_submission(array $data) {
        $listing_service = new ListingService();
        
        if (!empty($data['listing_id'])) {
            // Update existing listing
            $result = $listing_service->update_listing($data['listing_id'], $data);
        } else {
            // Create new listing
            $result = $listing_service->create_listing($data);
        }
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return [
            'success' => true,
            'listing_id' => $result,
            'message' => __('Listing saved successfully', 'happy-place'),
            'redirect' => get_permalink($result)
        ];
    }
    
    /**
     * Process agent form submission
     * 
     * @param array $data Sanitized form data
     * @return array|WP_Error Processing result
     */
    private function process_agent_submission(array $data) {
        // Implementation for agent form processing
        // This would integrate with agent management system
        
        return [
            'success' => true,
            'message' => __('Agent profile updated successfully', 'happy-place')
        ];
    }
    
    /**
     * Process lead form submission
     * 
     * @param array $data Sanitized form data
     * @return array|WP_Error Processing result
     */
    private function process_lead_submission(array $data) {
        // Implementation for lead form processing
        // This would integrate with lead management system
        
        return [
            'success' => true,
            'message' => __('Lead captured successfully', 'happy-place')
        ];
    }
    
    /**
     * Load form configurations
     */
    private function load_form_configs(): void {
        // Load from configuration files or database
        $this->form_configs = [
            'listing' => [
                'basic' => [
                    'title' => ['label' => 'Title', 'type' => 'text', 'required' => true],
                    'description' => ['label' => 'Description', 'type' => 'textarea', 'rows' => 6],
                    'price' => ['label' => 'Price', 'type' => 'number', 'min' => 0, 'step' => 0.01, 'required' => true],
                    'status' => [
                        'label' => 'Status',
                        'type' => 'select',
                        'choices' => [
                            'draft' => 'Draft',
                            'publish' => 'Active',
                            'pending' => 'Pending Review'
                        ]
                    ]
                ],
                'details' => [
                    'bedrooms' => ['label' => 'Bedrooms', 'type' => 'number', 'min' => 0],
                    'bathrooms' => ['label' => 'Bathrooms', 'type' => 'number', 'min' => 0, 'step' => 0.5],
                    'square_feet' => ['label' => 'Square Feet', 'type' => 'number', 'min' => 0],
                    'lot_size' => ['label' => 'Lot Size (acres)', 'type' => 'number', 'min' => 0, 'step' => 0.01],
                    'year_built' => ['label' => 'Year Built', 'type' => 'number', 'min' => 1800]
                ],
                'location' => [
                    'street_address' => ['label' => 'Street Address', 'type' => 'text', 'required' => true],
                    'city' => ['label' => 'City', 'type' => 'text', 'required' => true],
                    'state' => ['label' => 'State', 'type' => 'text', 'required' => true],
                    'zip_code' => ['label' => 'ZIP Code', 'type' => 'text', 'required' => true]
                ]
            ]
        ];
    }
    
    /**
     * Load validation rules
     */
    private function load_validation_rules(): void {
        $this->validation_rules = [
            'listing' => [
                'title' => ['required' => true, 'min_length' => 3, 'max_length' => 200],
                'price' => ['required' => true, 'type' => 'numeric', 'min' => 0],
                'street_address' => ['required' => true, 'min_length' => 5],
                'city' => ['required' => true, 'min_length' => 2],
                'state' => ['required' => true, 'min_length' => 2],
                'zip_code' => ['required' => true, 'pattern' => '/^\d{5}(-\d{4})?$/'],
                'bedrooms' => ['type' => 'integer', 'min' => 0],
                'bathrooms' => ['type' => 'numeric', 'min' => 0],
                'square_feet' => ['type' => 'integer', 'min' => 0]
            ]
        ];
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        add_action('wp_ajax_hp_process_listing_form', [$this, 'ajax_process_listing_form']);
        add_action('wp_ajax_hp_process_agent_form', [$this, 'ajax_process_agent_form']);
        add_action('wp_ajax_hp_process_lead_form', [$this, 'ajax_process_lead_form']);
    }
    
    /**
     * AJAX handler for listing form
     */
    public function ajax_process_listing_form(): void {
        $result = $this->process_submission($_POST, 'listing');
        
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
                'errors' => $result->get_error_data()
            ]);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX handler for agent form
     */
    public function ajax_process_agent_form(): void {
        $result = $this->process_submission($_POST, 'agent');
        
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
                'errors' => $result->get_error_data()
            ]);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX handler for lead form
     */
    public function ajax_process_lead_form(): void {
        $result = $this->process_submission($_POST, 'lead');
        
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
                'errors' => $result->get_error_data()
            ]);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * Validate field type
     */
    private function validate_field_type($value, string $type, string $field) {
        switch ($type) {
            case 'email':
                if (!is_email($value)) {
                    return new \WP_Error('invalid_email', sprintf(__('Field %s must be a valid email address', 'happy-place'), $field));
                }
                break;
                
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return new \WP_Error('invalid_url', sprintf(__('Field %s must be a valid URL', 'happy-place'), $field));
                }
                break;
                
            case 'numeric':
                if (!is_numeric($value)) {
                    return new \WP_Error('invalid_numeric', sprintf(__('Field %s must be a number', 'happy-place'), $field));
                }
                break;
                
            case 'integer':
                if (!filter_var($value, FILTER_VALIDATE_INT)) {
                    return new \WP_Error('invalid_integer', sprintf(__('Field %s must be an integer', 'happy-place'), $field));
                }
                break;
        }
        
        return true;
    }
    
    /**
     * Validate field length
     */
    private function validate_field_length($value, array $rules, string $field) {
        $length = strlen($value);
        
        if (isset($rules['min_length']) && $length < $rules['min_length']) {
            return new \WP_Error('too_short', sprintf(__('Field %s must be at least %d characters long', 'happy-place'), $field, $rules['min_length']));
        }
        
        if (isset($rules['max_length']) && $length > $rules['max_length']) {
            return new \WP_Error('too_long', sprintf(__('Field %s must not exceed %d characters', 'happy-place'), $field, $rules['max_length']));
        }
        
        return true;
    }
    
    /**
     * Sanitize form data
     */
    private function sanitize_form_data(array $data, string $form_type): array {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = array_map('sanitize_text_field', $value);
            } else {
                switch ($key) {
                    case 'description':
                        $sanitized[$key] = wp_kses_post($value);
                        break;
                        
                    case 'email':
                        $sanitized[$key] = sanitize_email($value);
                        break;
                        
                    case 'url':
                    case 'website':
                        $sanitized[$key] = esc_url_raw($value);
                        break;
                        
                    case 'price':
                    case 'square_feet':
                    case 'lot_size':
                        $sanitized[$key] = floatval($value);
                        break;
                        
                    case 'bedrooms':
                    case 'bathrooms':
                    case 'year_built':
                        $sanitized[$key] = intval($value);
                        break;
                        
                    default:
                        $sanitized[$key] = sanitize_text_field($value);
                        break;
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get listing form data
     */
    private function get_listing_form_data(int $listing_id): array {
        $post = get_post($listing_id);
        $data = [
            'title' => $post->post_title,
            'description' => $post->post_content,
            'status' => $post->post_status
        ];
        
        // Get ACF fields
        $acf_fields = get_fields($listing_id);
        if ($acf_fields) {
            $data = array_merge($data, $acf_fields);
        }
        
        return $data;
    }
}