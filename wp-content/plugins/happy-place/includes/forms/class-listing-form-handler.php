<?php
/**
 * Listing Form Handler
 * Handles dynamic listing form creation and submission
 * 
 * @package HappyPlace\Forms
 * @version 1.0.0
 */

namespace HappyPlace\Forms;

use HappyPlace\Services\ACF_Field_Service;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Listing_Form_Handler {
    
    /**
     * ACF Field Service instance
     */
    private ACF_Field_Service $field_service;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->field_service = new ACF_Field_Service();
        $this->field_service->init();
        
        // Register AJAX handlers
        add_action('wp_ajax_hph_save_listing', [$this, 'ajax_save_listing']);
        add_action('wp_ajax_hph_validate_field', [$this, 'ajax_validate_field']);
        add_action('wp_ajax_hph_get_field_config', [$this, 'ajax_get_field_config']);
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        hp_log('Listing Form Handler initialized', 'info', 'FORM_HANDLER');
    }
    
    /**
     * Render dynamic listing form
     */
    public function render_form(?int $listing_id = null): void {
        // Get all listing field groups
        $field_groups = $this->field_service->get_listing_field_groups();
        
        if (empty($field_groups)) {
            echo '<p class="hph-error">No field configuration found for listings.</p>';
            return;
        }
        
        // Get existing values if editing
        $values = [];
        if ($listing_id) {
            $values = $this->get_listing_values($listing_id);
        }
        
        ?>
        <form id="hph-listing-form" class="hph-dynamic-form" data-listing-id="<?php echo esc_attr($listing_id); ?>">
            <?php wp_nonce_field('hph_save_listing', 'listing_nonce'); ?>
            <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
            
            <div class="hph-form-tabs">
                <?php $this->render_form_tabs($field_groups); ?>
            </div>
            
            <div class="hph-form-sections">
                <?php $this->render_form_sections($field_groups, $values, $listing_id); ?>
            </div>
            
            <div class="hph-form-actions">
                <button type="submit" class="hph-btn hph-btn-primary">
                    <span class="btn-text">Save Listing</span>
                    <span class="btn-loading" style="display: none;">Saving...</span>
                </button>
                <button type="button" class="hph-btn hph-btn-secondary" onclick="history.back()">
                    Cancel
                </button>
                <?php if ($listing_id): ?>
                    <a href="<?php echo get_permalink($listing_id); ?>" class="hph-btn hph-btn-link" target="_blank">
                        View Listing
                    </a>
                <?php endif; ?>
            </div>
        </form>
        <?php
    }
    
    /**
     * Render form tabs
     */
    private function render_form_tabs(array $field_groups): void {
        $tabs = [
            'group_listing_core' => ['icon' => 'dashicons-admin-home', 'label' => 'Basic Info'],
            'group_listing_address' => ['icon' => 'dashicons-location', 'label' => 'Address'],
            'group_listing_content' => ['icon' => 'dashicons-edit', 'label' => 'Description'],
            'group_listing_features' => ['icon' => 'dashicons-yes', 'label' => 'Features'],
            'group_listing_media' => ['icon' => 'dashicons-format-gallery', 'label' => 'Media'],
            'group_listing_financial' => ['icon' => 'dashicons-money-alt', 'label' => 'Financial'],
            'group_listing_agent' => ['icon' => 'dashicons-businessperson', 'label' => 'Agent']
        ];
        
        ?>
        <ul class="hph-form-tab-list">
            <?php 
            $first = true;
            foreach ($field_groups as $group_key => $group_config): 
                if (!isset($tabs[$group_key])) continue;
                $tab = $tabs[$group_key];
            ?>
                <li class="hph-form-tab <?php echo $first ? 'active' : ''; ?>" data-tab="<?php echo esc_attr($group_key); ?>">
                    <a href="#<?php echo esc_attr($group_key); ?>">
                        <span class="<?php echo esc_attr($tab['icon']); ?>"></span>
                        <span><?php echo esc_html($tab['label']); ?></span>
                    </a>
                </li>
            <?php 
                $first = false;
            endforeach; 
            ?>
        </ul>
        <?php
    }
    
    /**
     * Render form sections
     */
    private function render_form_sections(array $field_groups, array $values, ?int $listing_id): void {
        $first = true;
        foreach ($field_groups as $group_key => $group_config) {
            ?>
            <div class="hph-form-section <?php echo $first ? 'active' : ''; ?>" id="<?php echo esc_attr($group_key); ?>">
                <h3 class="hph-form-section-title">
                    <?php echo esc_html($group_config['group']['title']); ?>
                </h3>
                
                <?php if (!empty($group_config['group']['description'])): ?>
                    <p class="hph-form-section-description">
                        <?php echo esc_html($group_config['group']['description']); ?>
                    </p>
                <?php endif; ?>
                
                <div class="hph-form-fields">
                    <?php $this->render_section_fields($group_config['fields'], $values, $listing_id); ?>
                </div>
            </div>
            <?php
            $first = false;
        }
    }
    
    /**
     * Render section fields
     */
    private function render_section_fields(array $fields_by_section, array $values, ?int $listing_id): void {
        foreach ($fields_by_section as $section => $fields) {
            if ($section !== 'main' && count($fields_by_section) > 1) {
                echo '<h4 class="hph-form-subsection-title">' . ucfirst(str_replace('-', ' ', $section)) . '</h4>';
            }
            
            echo '<div class="hph-form-row">';
            foreach ($fields as $field) {
                $value = $values[$field['name']] ?? null;
                echo $this->field_service->render_field($field, $value, $listing_id);
            }
            echo '</div>';
        }
    }
    
    /**
     * Get listing values for editing
     */
    private function get_listing_values(int $listing_id): array {
        $values = [];
        
        // Get all ACF fields for the listing
        $fields = get_fields($listing_id);
        
        if ($fields) {
            $values = $fields;
        }
        
        return $values;
    }
    
    /**
     * AJAX: Save listing
     */
    public function ajax_save_listing(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['listing_nonce'] ?? '', 'hph_save_listing')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check capabilities
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $listing_id = !empty($_POST['listing_id']) ? intval($_POST['listing_id']) : null;
        $acf_data = $_POST['acf'] ?? [];
        
        // Validate required fields
        $validation_errors = $this->validate_listing_data($acf_data);
        
        if (!empty($validation_errors)) {
            wp_send_json_error([
                'message' => 'Please fix the validation errors',
                'errors' => $validation_errors
            ]);
        }
        
        try {
            // Create or update the listing post
            $listing_id = $this->save_listing_post($listing_id, $acf_data);
            
            // Save ACF fields
            $this->save_acf_fields($listing_id, $acf_data);
            
            // Clear any caches
            clean_post_cache($listing_id);
            
            wp_send_json_success([
                'listing_id' => $listing_id,
                'message' => 'Listing saved successfully',
                'redirect' => get_permalink($listing_id)
            ]);
            
        } catch (\Exception $e) {
            hp_log('Error saving listing: ' . $e->getMessage(), 'error', 'FORM_HANDLER');
            wp_send_json_error('Error saving listing: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate listing data
     */
    private function validate_listing_data(array $data): array {
        $errors = [];
        
        // Get field configurations for validation
        $field_groups = $this->field_service->get_listing_field_groups();
        
        foreach ($field_groups as $group_config) {
            foreach ($group_config['fields'] as $section => $fields) {
                foreach ($fields as $field) {
                    // Check required fields
                    if (!empty($field['required'])) {
                        $field_value = $data[$field['key']] ?? '';
                        
                        if (empty($field_value)) {
                            $errors[$field['key']] = $field['label'] . ' is required';
                        }
                    }
                    
                    // Type-specific validation
                    if (!empty($data[$field['key']])) {
                        $validation_error = $this->validate_field_value(
                            $data[$field['key']], 
                            $field
                        );
                        
                        if ($validation_error) {
                            $errors[$field['key']] = $validation_error;
                        }
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate field value based on type
     */
    private function validate_field_value($value, array $field): ?string {
        switch ($field['type']) {
            case 'email':
                if (!is_email($value)) {
                    return 'Please enter a valid email address';
                }
                break;
                
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return 'Please enter a valid URL';
                }
                break;
                
            case 'number':
                if (!is_numeric($value)) {
                    return 'Please enter a valid number';
                }
                
                if (isset($field['min']) && $value < $field['min']) {
                    return 'Value must be at least ' . $field['min'];
                }
                
                if (isset($field['max']) && $value > $field['max']) {
                    return 'Value must not exceed ' . $field['max'];
                }
                break;
        }
        
        return null;
    }
    
    /**
     * Save listing post
     */
    private function save_listing_post(?int $listing_id, array $data): int {
        // Generate title from address or marketing title
        $title = $this->generate_listing_title($data);
        
        $post_data = [
            'post_type' => 'listing',
            'post_status' => 'publish',
            'post_title' => $title
        ];
        
        if ($listing_id) {
            $post_data['ID'] = $listing_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
        }
        
        if (is_wp_error($result)) {
            throw new \Exception($result->get_error_message());
        }
        
        return $result;
    }
    
    /**
     * Generate listing title
     */
    private function generate_listing_title(array $data): string {
        // Check for marketing title first
        if (!empty($data['field_property_title'])) {
            return sanitize_text_field($data['field_property_title']);
        }
        
        // Build title from address components
        $title_parts = [];
        
        if (!empty($data['field_street_number'])) {
            $title_parts[] = $data['field_street_number'];
        }
        
        if (!empty($data['field_street_name'])) {
            $title_parts[] = $data['field_street_name'];
        }
        
        if (!empty($data['field_street_type'])) {
            $title_parts[] = $data['field_street_type'];
        }
        
        if (!empty($title_parts)) {
            $title = implode(' ', $title_parts);
            
            if (!empty($data['field_city'])) {
                $title .= ', ' . $data['field_city'];
            }
            
            return sanitize_text_field($title);
        }
        
        return 'Listing ' . date('Y-m-d H:i:s');
    }
    
    /**
     * Save ACF fields
     */
    private function save_acf_fields(int $listing_id, array $data): void {
        foreach ($data as $field_key => $value) {
            // Skip non-field keys
            if (strpos($field_key, 'field_') !== 0) {
                continue;
            }
            
            // Let ACF handle the field saving
            update_field($field_key, $value, $listing_id);
        }
    }
    
    /**
     * Enqueue scripts
     */
    public function enqueue_scripts(): void {
        if (!$this->is_listing_form_page()) {
            return;
        }
        
        // Enqueue ACF scripts if available
        if (function_exists('acf_enqueue_scripts')) {
            acf_enqueue_scripts();
        }
        
        // Enqueue our dynamic form script
        wp_enqueue_script(
            'hph-dynamic-listing-form',
            HP_PLUGIN_URL . '/assets/js/dynamic-listing-form.js',
            ['jquery'],
            HP_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('hph-dynamic-listing-form', 'HPH_Form', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_ajax_nonce'),
            'messages' => [
                'saving' => __('Saving...', 'happy-place'),
                'saved' => __('Saved successfully', 'happy-place'),
                'error' => __('An error occurred', 'happy-place'),
                'validation_error' => __('Please fix the validation errors', 'happy-place')
            ]
        ]);
    }
    
    /**
     * Check if current page has listing form
     */
    private function is_listing_form_page(): bool {
        // Check if we're on a dashboard page or listing edit page
        return is_page_template('template-dashboard.php') || 
               (isset($_GET['hph_section']) && $_GET['hph_section'] === 'listings');
    }
}