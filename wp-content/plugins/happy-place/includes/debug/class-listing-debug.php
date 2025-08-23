<?php
/**
 * Listing Debug Class
 * Diagnostic tools for troubleshooting save issues
 *
 * @package HappyPlace\Debug
 */

namespace HappyPlace\Debug;

if (!defined('ABSPATH')) {
    exit;
}

class Listing_Debug {
    
    public static function init() {
        // Add debug info to admin footer
        add_action('admin_footer', [__CLASS__, 'show_debug_info']);
        
        // Log all save attempts
        add_action('save_post', [__CLASS__, 'log_save_attempt'], 1, 3);
        add_action('acf/save_post', [__CLASS__, 'log_acf_save'], 1);
        
        // Add debug meta box
        add_action('add_meta_boxes', [__CLASS__, 'add_debug_meta_box']);
    }
    
    /**
     * Log save attempts
     */
    public static function log_save_attempt($post_id, $post, $update) {
        if ($post->post_type !== 'listing') {
            return;
        }
        
        error_log('=== LISTING SAVE ATTEMPT ===');
        error_log('Post ID: ' . $post_id);
        error_log('Update: ' . ($update ? 'Yes' : 'No'));
        error_log('POST data keys: ' . implode(', ', array_keys($_POST)));
        
        // Check for ACF fields
        if (isset($_POST['acf'])) {
            error_log('ACF fields present: Yes');
            error_log('ACF field count: ' . count($_POST['acf']));
        } else {
            error_log('ACF fields present: No');
        }
        
        // Check for nonces
        $nonces = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'nonce') !== false || strpos($key, '_wpnonce') !== false) {
                $nonces[$key] = $value;
            }
        }
        error_log('Nonces found: ' . json_encode(array_keys($nonces)));
        
        error_log('=== END SAVE ATTEMPT ===');
    }
    
    /**
     * Log ACF save
     */
    public static function log_acf_save($post_id) {
        if (get_post_type($post_id) !== 'listing') {
            return;
        }
        
        error_log('=== ACF SAVE ===');
        error_log('Post ID: ' . $post_id);
        
        if (isset($_POST['acf'])) {
            foreach ($_POST['acf'] as $field_key => $value) {
                error_log("ACF Field: {$field_key} = " . (is_array($value) ? json_encode($value) : $value));
            }
        }
        
        error_log('=== END ACF SAVE ===');
    }
    
    /**
     * Add debug meta box
     */
    public static function add_debug_meta_box() {
        add_meta_box(
            'hp_listing_debug',
            __('Debug Information', 'happy-place'),
            [__CLASS__, 'render_debug_meta_box'],
            'listing',
            'side',
            'low'
        );
    }
    
    /**
     * Render debug meta box
     */
    public static function render_debug_meta_box($post) {
        echo '<div style="font-size: 11px; font-family: monospace;">';
        
        // Check ACF
        echo '<strong>ACF Status:</strong><br>';
        if (function_exists('acf')) {
            echo '✅ ACF is active<br>';
            
            // Check field groups
            $field_groups = acf_get_field_groups(['post_type' => 'listing']);
            echo 'Field Groups: ' . count($field_groups) . '<br>';
            
            if (!empty($field_groups)) {
                echo '<details><summary>Field Groups</summary><ul>';
                foreach ($field_groups as $group) {
                    echo '<li>' . esc_html($group['title']) . ' (' . esc_html($group['key']) . ')</li>';
                }
                echo '</ul></details>';
            }
            
            // Check fields
            $fields = get_field_objects($post->ID);
            if ($fields) {
                echo 'Fields loaded: ' . count($fields) . '<br>';
                echo '<details><summary>Field Values</summary><pre style="font-size: 10px; max-height: 200px; overflow: auto;">';
                foreach ($fields as $field) {
                    echo esc_html($field['name']) . ': ' . esc_html(is_array($field['value']) ? json_encode($field['value']) : $field['value']) . "\n";
                }
                echo '</pre></details>';
            } else {
                echo 'No fields loaded<br>';
            }
        } else {
            echo '❌ ACF is NOT active<br>';
        }
        
        // Check meta values
        echo '<br><strong>Meta Values:</strong><br>';
        $meta = get_post_meta($post->ID);
        echo 'Total meta fields: ' . count($meta) . '<br>';
        
        echo '<details><summary>All Meta</summary><pre style="font-size: 10px; max-height: 200px; overflow: auto;">';
        foreach ($meta as $key => $value) {
            if (strpos($key, '_') !== 0 || strpos($key, '_listing') === 0 || strpos($key, '_featured') === 0) {
                echo esc_html($key) . ': ' . esc_html(is_array($value[0]) ? json_encode($value[0]) : $value[0]) . "\n";
            }
        }
        echo '</pre></details>';
        
        echo '</div>';
    }
    
    /**
     * Show debug info in admin footer
     */
    public static function show_debug_info() {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'listing' || $screen->base !== 'post') {
            return;
        }
        
        ?>
        <script>
        jQuery(document).ready(function($) {
            console.log('=== Happy Place Debug ===');
            
            // Check for ACF fields
            var acfFields = $('#acf-form, .acf-fields, [data-name*="field_"]').length;
            console.log('ACF form elements found:', acfFields);
            
            // Monitor form submission
            $('form#post').on('submit', function(e) {
                console.log('Form submitting...');
                
                // Check ACF fields
                var acfData = {};
                $('[name^="acf["]').each(function() {
                    acfData[$(this).attr('name')] = $(this).val();
                });
                console.log('ACF fields being submitted:', acfData);
                
                // Check our custom fields
                var customData = {
                    listing_status: $('[name="listing_status"]').val(),
                    featured_listing: $('[name="featured_listing"]').is(':checked')
                };
                console.log('Custom fields being submitted:', customData);
            });
        });
        </script>
        <?php
    }
}