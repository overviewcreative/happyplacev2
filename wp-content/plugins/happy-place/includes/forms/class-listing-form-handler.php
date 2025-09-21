<?php
/**
 * Listing Form Handler
 * Handles dynamic listing form creation and submission - Updated for v4.0.0
 * 
 * @package HappyPlace\Forms
 * @version 4.0.0
 */

namespace HappyPlace\Forms;

use HappyPlace\Services\FormService;
use HappyPlace\Services\ListingService;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Listing Form Handler Class
 * 
 * Integrates with new FormService and ListingService architecture
 */
class ListingFormHandler {
    
    /**
     * Form Service instance
     */
    private FormService $form_service;
    
    /**
     * Listing Service instance
     */
    private ListingService $listing_service;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->form_service = new FormService();
        $this->listing_service = new ListingService();
        
        // Initialize services
        $this->form_service->init();
        $this->listing_service->init();
        
        // Register AJAX handlers
        add_action('wp_ajax_hp_save_listing', [$this, 'ajax_save_listing']);
        add_action('wp_ajax_nopriv_hp_save_listing', [$this, 'ajax_save_listing']);
        add_action('wp_ajax_hp_validate_listing_field', [$this, 'ajax_validate_field']);
        add_action('wp_ajax_hp_get_listing_form', [$this, 'ajax_get_form']);
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    /**
     * Render listing form
     * 
     * @param int|null $listing_id Listing ID for editing
     * @return string Form HTML
     */
    public function render_form(?int $listing_id = null): string {
        return $this->form_service->render_listing_form($listing_id);
    }
    
    /**
     * Display form (for direct output)
     * 
     * @param int|null $listing_id Listing ID for editing
     * @return void
     */
    public function display_form(?int $listing_id = null): void {
        echo $this->render_form($listing_id);
    }
    
    /**
     * AJAX handler for saving listings
     * 
     * @return void
     */
    public function ajax_save_listing(): void {
        try {
            // Process the form submission
            $result = $this->form_service->process_submission($_POST, 'listing');
            
            if (is_wp_error($result)) {
                wp_send_json_error([
                    'message' => $result->get_error_message(),
                    'errors' => $result->get_error_data()
                ]);
                return;
            }
            
            wp_send_json_success($result);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX handler for field validation
     * 
     * @return void
     */
    public function ajax_validate_field(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_listing_form')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $field_name = sanitize_text_field($_POST['field_name'] ?? '');
        $field_value = sanitize_text_field($_POST['field_value'] ?? '');
        
        if (empty($field_name)) {
            wp_send_json_error(['message' => 'Field name is required']);
            return;
        }
        
        // Validate single field
        $validation = $this->form_service->validate_form(
            [$field_name => $field_value], 
            'listing'
        );
        
        if (is_wp_error($validation)) {
            wp_send_json_error([
                'field' => $field_name,
                'message' => $validation->get_error_message()
            ]);
            return;
        }
        
        wp_send_json_success([
            'field' => $field_name,
            'message' => 'Field is valid'
        ]);
    }
    
    /**
     * AJAX handler for getting form HTML
     * 
     * @return void
     */
    public function ajax_get_form(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_get_form')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_listings')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        $listing_id = $listing_id > 0 ? $listing_id : null;
        
        $form_html = $this->render_form($listing_id);
        
        wp_send_json_success([
            'html' => $form_html,
            'listing_id' => $listing_id
        ]);
    }
    
    /**
     * Enqueue frontend scripts
     * 
     * @return void
     */
    public function enqueue_scripts(): void {
        if (!$this->should_enqueue_scripts()) {
            return;
        }
        
        // Main listing form script
        wp_enqueue_script(
            'hp-listing-form',
            HP_ASSETS_URL . 'js/listing-form.js',
            ['jquery', 'wp-util'],
            HP_VERSION,
            true
        );
        
        // Note: Form validation now done by HPH.Forms unified system in theme
        // Only enqueue if unified system is not available
        if (!wp_script_is('hph-forms-unified', 'enqueued')) {
            // Fallback: Basic jQuery only
            wp_enqueue_script('jquery');
        }
        
        // Listing form styles
        wp_enqueue_style(
            'hp-listing-form',
            HP_ASSETS_URL . 'css/listing-form.css',
            [],
            HP_VERSION
        );
        
        // Localize script
        wp_localize_script('hp-listing-form', 'HP_ListingForm', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_listing_form'),
            'get_form_nonce' => wp_create_nonce('hp_get_form'),
            'messages' => [
                'saving' => __('Saving listing...', 'happy-place'),
                'saved' => __('Listing saved successfully!', 'happy-place'),
                'error' => __('An error occurred while saving.', 'happy-place'),
                'confirm_delete' => __('Are you sure you want to delete this listing?', 'happy-place'),
                'required_field' => __('This field is required.', 'happy-place'),
                'invalid_format' => __('Please enter a valid value.', 'happy-place')
            ],
            'settings' => [
                'auto_save' => true,
                'auto_save_interval' => 30000, // 30 seconds
                'validate_on_blur' => true,
                'show_character_count' => true
            ]
        ]);
    }
    
    /**
     * Enqueue admin scripts
     * 
     * @return void
     */
    public function enqueue_admin_scripts(): void {
        $screen = get_current_screen();
        
        if (!$screen || !in_array($screen->id, ['listing', 'edit-listing'])) {
            return;
        }
        
        // Admin-specific form enhancements
        wp_enqueue_script(
            'hp-admin-listing-form',
            HP_ASSETS_URL . 'js/admin-listing-form.js',
            ['hp-listing-form'],
            HP_VERSION,
            true
        );
        
        wp_enqueue_style(
            'hp-admin-listing-form',
            HP_ASSETS_URL . 'css/admin-listing-form.css',
            ['hp-listing-form'],
            HP_VERSION
        );
    }
    
    /**
     * Check if scripts should be enqueued
     * 
     * @return bool
     */
    private function should_enqueue_scripts(): bool {
        global $post;
        
        // Always enqueue on listing post type pages
        if (is_singular('listing') || is_post_type_archive('listing')) {
            return true;
        }
        
        // Enqueue on pages with listing forms
        if ($post && has_shortcode($post->post_content, 'hp_listing_form')) {
            return true;
        }
        
        // Enqueue on dashboard pages
        if (get_query_var('hp_dashboard')) {
            return true;
        }
        
        // Check for template files that might need the form
        $template = get_page_template_slug();
        $form_templates = [
            'templates/dashboard.php',
            'templates/submit-listing.php',
            'templates/edit-listing.php'
        ];
        
        return in_array($template, $form_templates);
    }
    
    /**
     * Get form field configuration for JavaScript
     * 
     * @param string $form_type Form type
     * @return array Field configuration
     */
    public function get_js_field_config(string $form_type = 'listing'): array {
        $config = $this->form_service->get_form_fields($form_type);
        $validation_rules = $this->form_service->get_validation_rules($form_type);
        
        // Format for JavaScript consumption
        $js_config = [];
        
        foreach ($config as $section => $fields) {
            foreach ($fields as $field_name => $field_config) {
                $js_config[$field_name] = [
                    'type' => $field_config['type'],
                    'label' => $field_config['label'],
                    'required' => $field_config['required'] ?? false,
                    'validation' => $validation_rules[$field_name] ?? []
                ];
            }
        }
        
        return $js_config;
    }
    
    /**
     * Shortcode handler for listing form
     * 
     * @param array $atts Shortcode attributes
     * @return string Form HTML
     */
    public function shortcode_handler(array $atts): string {
        $atts = shortcode_atts([
            'id' => null,
            'class' => 'hp-listing-form-container',
            'redirect' => '',
            'show_title' => 'true'
        ], $atts, 'hp_listing_form');
        
        $listing_id = $atts['id'] ? intval($atts['id']) : null;
        
        // Check permissions
        if (!current_user_can('edit_listings')) {
            return '<p class="hp-error">' . __('You do not have permission to submit listings.', 'happy-place') . '</p>';
        }
        
        $output = '<div class="' . esc_attr($atts['class']) . '">';
        
        if ($atts['show_title'] === 'true') {
            $title = $listing_id ? __('Edit Listing', 'happy-place') : __('Submit New Listing', 'happy-place');
            $output .= '<h2 class="hp-form-title">' . esc_html($title) . '</h2>';
        }
        
        $output .= $this->render_form($listing_id);
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Initialize shortcode
     * 
     * @return void
     */
    public function init_shortcode(): void {
        add_shortcode('hp_listing_form', [$this, 'shortcode_handler']);
    }
    
    /**
     * Process form submission for compatibility
     * 
     * @param array $data Form data
     * @return array|WP_Error Processing result
     * @deprecated Use FormService::process_submission() directly
     */
    public function process_listing_data(array $data) {
        _deprecated_function(__METHOD__, '4.0.0', 'FormService::process_submission()');
        return $this->form_service->process_submission($data, 'listing');
    }
    
    /**
     * Get listing form data for editing
     * 
     * @param int $listing_id Listing ID
     * @return array Form data
     * @deprecated Use FormService methods directly
     */
    public function get_listing_values(int $listing_id): array {
        _deprecated_function(__METHOD__, '4.0.0', 'Use FormService methods directly');
        
        $post = get_post($listing_id);
        if (!$post || $post->post_type !== 'listing') {
            return [];
        }
        
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

// Initialize shortcode support
add_action('init', function() {
    $form_handler = new ListingFormHandler();
    $form_handler->init_shortcode();
});