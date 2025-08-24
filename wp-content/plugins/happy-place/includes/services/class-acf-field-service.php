<?php
/**
 * ACF Field Service
 * Manages dynamic ACF field configurations and rendering
 * 
 * @package HappyPlace\Services
 * @version 1.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ACF_Field_Service extends Service {
    
    /**
     * Field group configurations cache
     */
    private array $field_groups = [];
    
    /**
     * Cache key prefix
     */
    private string $cache_prefix = 'hph_acf_config_';
    
    /**
     * Cache expiration time
     */
    private int $cache_expiry = DAY_IN_SECONDS;
    
    /**
     * Initialize service
     */
    public function init(): void {
        // Set up ACF Local JSON paths
        add_filter('acf/settings/save_json', [$this, 'set_save_json_path']);
        add_filter('acf/settings/load_json', [$this, 'set_load_json_path']);
        
        // Clear cache when field groups are updated
        add_action('acf/save_field_group', [$this, 'clear_field_cache']);
        add_action('acf/delete_field_group', [$this, 'clear_field_cache']);
        add_action('acf/update_field_group', [$this, 'clear_field_cache']);
        
        hp_log('ACF Field Service initialized', 'info', 'ACF_SERVICE');
    }
    
    /**
     * Set ACF Local JSON save path
     */
    public function set_save_json_path($path): string {
        return HP_PLUGIN_DIR . '/includes/fields/acf-json';
    }
    
    /**
     * Set ACF Local JSON load paths
     */
    public function set_load_json_path($paths): array {
        unset($paths[0]);
        $paths[] = HP_PLUGIN_DIR . '/includes/fields/acf-json';
        return $paths;
    }
    
    /**
     * Get all listing field groups
     */
    public function get_listing_field_groups(): array {
        $cache_key = $this->cache_prefix . 'listing_groups';
        $cached = get_transient($cache_key);
        
        if (false !== $cached) {
            return $cached;
        }
        
        // Field groups for listings in order
        $group_keys = [
            'group_listing_core',
            'group_listing_address',
            'group_listing_content',
            'group_listing_features',
            'group_listing_media',
            'group_listing_financial',
            'group_listing_agent'
        ];
        
        $groups = [];
        foreach ($group_keys as $key) {
            $group = $this->get_field_group_config($key);
            if ($group) {
                $groups[$key] = $group;
            }
        }
        
        set_transient($cache_key, $groups, $this->cache_expiry);
        return $groups;
    }
    
    /**
     * Get field group configuration with organized fields
     */
    public function get_field_group_config(string $group_key): ?array {
        // Check cache first
        $cache_key = $this->cache_prefix . $group_key;
        $cached = get_transient($cache_key);
        
        if (false !== $cached) {
            return $cached;
        }
        
        // Get field group from ACF
        $field_group = acf_get_field_group($group_key);
        
        if (!$field_group) {
            hp_log("Field group not found: {$group_key}", 'warning', 'ACF_SERVICE');
            return null;
        }
        
        // Get all fields in this group
        $fields = acf_get_fields($field_group);
        
        if (!$fields) {
            hp_log("No fields found for group: {$group_key}", 'warning', 'ACF_SERVICE');
            return null;
        }
        
        // Organize fields by tabs/sections
        $organized_fields = $this->organize_fields_by_section($fields);
        
        $config = [
            'group' => $field_group,
            'fields' => $organized_fields,
            'field_map' => $this->create_field_map($fields)
        ];
        
        // Cache the configuration
        set_transient($cache_key, $config, $this->cache_expiry);
        
        return $config;
    }
    
    /**
     * Organize fields by sections (tabs)
     */
    private function organize_fields_by_section(array $fields): array {
        $organized = [];
        $current_section = 'main';
        
        foreach ($fields as $field) {
            // Check if this is a tab field
            if ($field['type'] === 'tab') {
                $current_section = sanitize_title($field['label']);
                continue;
            }
            
            // Initialize section if needed
            if (!isset($organized[$current_section])) {
                $organized[$current_section] = [];
            }
            
            // Add field to current section
            $organized[$current_section][] = $field;
        }
        
        return $organized;
    }
    
    /**
     * Create a field map for quick lookups
     */
    private function create_field_map(array $fields): array {
        $map = [];
        
        foreach ($fields as $field) {
            if ($field['type'] !== 'tab') {
                $map[$field['key']] = $field['name'];
                $map[$field['name']] = $field['key'];
            }
        }
        
        return $map;
    }
    
    /**
     * Render a field with proper HTML structure
     */
    public function render_field(array $field, $value = null, ?int $post_id = null): string {
        // Get value if post_id provided
        if ($post_id && !$value) {
            $value = get_field($field['name'], $post_id);
        }
        
        // Build wrapper classes
        $wrapper_classes = [
            'hph-field-wrapper',
            'hph-field-' . $field['type'],
            'field-' . $field['name']
        ];
        
        if (!empty($field['wrapper']['class'])) {
            $wrapper_classes[] = $field['wrapper']['class'];
        }
        
        if (!empty($field['required'])) {
            $wrapper_classes[] = 'required-field';
        }
        
        // Build wrapper attributes
        $wrapper_attrs = [
            'class' => implode(' ', $wrapper_classes),
            'data-field-key' => $field['key'],
            'data-field-type' => $field['type']
        ];
        
        if (!empty($field['wrapper']['width'])) {
            $wrapper_attrs['style'] = 'width: ' . $field['wrapper']['width'] . '%;';
        }
        
        if (!empty($field['conditional_logic'])) {
            $wrapper_attrs['data-conditional-logic'] = json_encode($field['conditional_logic']);
        }
        
        // Start building HTML
        $html = '<div';
        foreach ($wrapper_attrs as $attr => $attr_value) {
            $html .= ' ' . $attr . '="' . esc_attr($attr_value) . '"';
        }
        $html .= '>';
        
        // Add label
        if (!empty($field['label'])) {
            $html .= $this->render_field_label($field);
        }
        
        // Add input/control
        $html .= '<div class="hph-field-input">';
        $html .= $this->render_field_input($field, $value);
        $html .= '</div>';
        
        // Add instructions
        if (!empty($field['instructions'])) {
            $html .= '<p class="hph-field-instructions">' . esc_html($field['instructions']) . '</p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render field label
     */
    private function render_field_label(array $field): string {
        $required = !empty($field['required']) ? '<span class="required">*</span>' : '';
        
        return sprintf(
            '<label for="%s" class="hph-field-label">%s%s</label>',
            esc_attr($field['key']),
            esc_html($field['label']),
            $required
        );
    }
    
    /**
     * Render field input based on type
     */
    private function render_field_input(array $field, $value): string {
        $name = 'acf[' . $field['key'] . ']';
        $id = $field['key'];
        $required = !empty($field['required']) ? 'required' : '';
        
        switch ($field['type']) {
            case 'text':
            case 'email':
            case 'url':
                return $this->render_text_field($field, $name, $id, $value, $required);
                
            case 'number':
                return $this->render_number_field($field, $name, $id, $value, $required);
                
            case 'textarea':
                return $this->render_textarea_field($field, $name, $id, $value, $required);
                
            case 'select':
                return $this->render_select_field($field, $name, $id, $value, $required);
                
            case 'checkbox':
                return $this->render_checkbox_field($field, $name, $id, $value);
                
            case 'true_false':
                return $this->render_true_false_field($field, $name, $id, $value);
                
            case 'date_picker':
                return $this->render_date_picker_field($field, $name, $id, $value, $required);
                
            case 'image':
                return $this->render_image_field($field, $name, $id, $value, $required);
                
            case 'gallery':
                return $this->render_gallery_field($field, $name, $id, $value);
                
            case 'repeater':
                return $this->render_repeater_field($field, $name, $id, $value);
                
            case 'wysiwyg':
                return $this->render_wysiwyg_field($field, $name, $id, $value, $required);
                
            case 'user':
                return $this->render_user_field($field, $name, $id, $value, $required);
                
            default:
                // Fallback to ACF's own rendering
                ob_start();
                $field['name'] = $name;
                $field['value'] = $value;
                acf_render_field($field);
                return ob_get_clean();
        }
    }
    
    /**
     * Render text field
     */
    private function render_text_field($field, $name, $id, $value, $required): string {
        $type = $field['type'];
        $maxlength = !empty($field['maxlength']) ? 'maxlength="' . $field['maxlength'] . '"' : '';
        $placeholder = !empty($field['placeholder']) ? 'placeholder="' . esc_attr($field['placeholder']) . '"' : '';
        $prepend = !empty($field['prepend']) ? $field['prepend'] : '';
        $append = !empty($field['append']) ? $field['append'] : '';
        
        $html = '';
        
        if ($prepend || $append) {
            $html .= '<div class="hph-input-group">';
            if ($prepend) {
                $html .= '<span class="hph-input-prepend">' . esc_html($prepend) . '</span>';
            }
        }
        
        $html .= sprintf(
            '<input type="%s" id="%s" name="%s" value="%s" class="hph-input" %s %s %s>',
            $type,
            esc_attr($id),
            esc_attr($name),
            esc_attr($value),
            $maxlength,
            $placeholder,
            $required
        );
        
        if ($prepend || $append) {
            if ($append) {
                $html .= '<span class="hph-input-append">' . esc_html($append) . '</span>';
            }
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Render number field
     */
    private function render_number_field($field, $name, $id, $value, $required): string {
        $min = isset($field['min']) ? 'min="' . $field['min'] . '"' : '';
        $max = isset($field['max']) ? 'max="' . $field['max'] . '"' : '';
        $step = isset($field['step']) ? 'step="' . $field['step'] . '"' : '';
        $placeholder = !empty($field['placeholder']) ? 'placeholder="' . esc_attr($field['placeholder']) . '"' : '';
        $prepend = !empty($field['prepend']) ? $field['prepend'] : '';
        $append = !empty($field['append']) ? $field['append'] : '';
        
        $html = '';
        
        if ($prepend || $append) {
            $html .= '<div class="hph-input-group">';
            if ($prepend) {
                $html .= '<span class="hph-input-prepend">' . esc_html($prepend) . '</span>';
            }
        }
        
        $html .= sprintf(
            '<input type="number" id="%s" name="%s" value="%s" class="hph-input" %s %s %s %s %s>',
            esc_attr($id),
            esc_attr($name),
            esc_attr($value),
            $min,
            $max,
            $step,
            $placeholder,
            $required
        );
        
        if ($prepend || $append) {
            if ($append) {
                $html .= '<span class="hph-input-append">' . esc_html($append) . '</span>';
            }
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Render select field
     */
    private function render_select_field($field, $name, $id, $value, $required): string {
        $multiple = !empty($field['multiple']) ? 'multiple' : '';
        $ui = !empty($field['ui']) ? 'data-ui="1"' : '';
        
        $html = sprintf(
            '<select id="%s" name="%s%s" class="hph-select" %s %s %s>',
            esc_attr($id),
            esc_attr($name),
            $multiple ? '[]' : '',
            $multiple,
            $ui,
            $required
        );
        
        // Add placeholder option
        if (!empty($field['placeholder'])) {
            $html .= '<option value="">' . esc_html($field['placeholder']) . '</option>';
        } elseif (!empty($field['allow_null'])) {
            $html .= '<option value="">- Select -</option>';
        }
        
        // Add choices
        if (!empty($field['choices'])) {
            foreach ($field['choices'] as $choice_value => $choice_label) {
                $selected = '';
                if (is_array($value)) {
                    $selected = in_array($choice_value, $value) ? 'selected' : '';
                } else {
                    $selected = ($value == $choice_value) ? 'selected' : '';
                }
                
                $html .= sprintf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($choice_value),
                    $selected,
                    esc_html($choice_label)
                );
            }
        }
        
        $html .= '</select>';
        
        return $html;
    }
    
    /**
     * Clear field cache
     */
    public function clear_field_cache($field_group = null): void {
        if ($field_group && is_array($field_group) && isset($field_group['key'])) {
            delete_transient($this->cache_prefix . $field_group['key']);
        }
        
        // Clear listing groups cache
        delete_transient($this->cache_prefix . 'listing_groups');
        
        hp_log('ACF field cache cleared', 'debug', 'ACF_SERVICE');
    }
}