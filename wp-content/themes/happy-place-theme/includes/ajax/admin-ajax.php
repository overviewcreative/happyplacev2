<?php
/**
 * Admin & Settings AJAX Handlers
 * 
 * Handles admin-specific AJAX functionality including:
 * - Theme settings export/import
 * - Admin configuration management
 * - Performance optimization controls
 * - Cache management
 * - System status checks
 * 
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle theme settings export
 */
if (!function_exists('handle_hph_export_settings')) {
    add_action('wp_ajax_hph_export_settings', 'handle_hph_export_settings');

    function handle_hph_export_settings() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'], 'hph_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Get all theme settings
        $settings = array();
        $option_keys = array(
            'hph_agency_name', 'hph_agency_tagline', 'hph_agency_phone', 'hph_agency_email',
            'hph_agency_address', 'hph_agency_license', 'hph_agency_hours',
            'hph_brand_logo', 'hph_brand_logo_white', 'hph_brand_favicon', 'hph_brand_colors',
            'hph_social_links', 'hph_google_maps_api_key', 'hph_google_analytics_id',
            'hph_facebook_pixel_id', 'hph_mls_api_key', 'hph_mls_api_secret',
            'hph_mailchimp_api_key', 'hph_mailchimp_list_id', 'hph_recaptcha_site_key',
            'hph_recaptcha_secret_key', 'hph_enable_sticky_header', 'hph_enable_dark_mode',
            'hph_enable_lazy_loading', 'hph_enable_breadcrumbs', 'hph_enable_property_favorites',
            'hph_enable_advanced_search', 'hph_enable_virtual_tours', 'hph_enable_mortgage_calculator',
            'hph_cache_listings', 'hph_cache_duration', 'hph_optimize_images',
            'hph_minify_assets', 'hph_preload_critical_css'
        );
        
        foreach ($option_keys as $key) {
            $settings[$key] = get_option($key, '');
        }
        
        // Add export metadata
        $settings['_export_info'] = array(
            'version' => defined('HPH_VERSION') ? HPH_VERSION : '3.2.0',
            'date' => current_time('Y-m-d H:i:s'),
            'site_url' => home_url(),
            'wp_version' => get_bloginfo('version')
        );
        
        // Set headers for JSON download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="hph-theme-settings-' . date('Y-m-d') . '.json"');
        
        echo json_encode($settings, JSON_PRETTY_PRINT);
        exit;
    }
}

/**
 * Handle theme settings import
 */
if (!function_exists('handle_hph_import_settings')) {
    add_action('wp_ajax_hph_import_settings', 'handle_hph_import_settings');

    function handle_hph_import_settings() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check if file was uploaded
        if (empty($_FILES['settings_file']) || $_FILES['settings_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('No file uploaded or upload error occurred');
        }
        
        // Validate file type
        $file_info = pathinfo($_FILES['settings_file']['name']);
        if (!isset($file_info['extension']) || strtolower($file_info['extension']) !== 'json') {
            wp_send_json_error('Invalid file type. Please upload a JSON file');
        }
        
        // Read and decode JSON
        $json_content = file_get_contents($_FILES['settings_file']['tmp_name']);
        $settings = json_decode($json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON format: ' . json_last_error_msg());
        }
        
        // Validate settings structure
        if (!is_array($settings)) {
            wp_send_json_error('Invalid settings format');
        }
        
        // Import settings (excluding metadata)
        $imported_count = 0;
        $skipped = [];
        
        foreach ($settings as $key => $value) {
            // Skip metadata
            if ($key === '_export_info') {
                continue;
            }
            
            // Validate option key
            if (strpos($key, 'hph_') === 0) {
                update_option($key, $value);
                $imported_count++;
            } else {
                $skipped[] = $key;
            }
        }
        
        // Prepare response
        $response = [
            'imported_count' => $imported_count,
            'skipped' => $skipped,
            'message' => sprintf(__('%d settings imported successfully', 'happy-place-theme'), $imported_count)
        ];
        
        if (!empty($skipped)) {
            $response['message'] .= '. ' . sprintf(__('%d settings skipped for security', 'happy-place-theme'), count($skipped));
        }
        
        wp_send_json_success($response);
    }
}

/**
 * Handle cache clearing
 */
if (!function_exists('handle_hph_clear_cache')) {
    add_action('wp_ajax_hph_clear_cache', 'handle_hph_clear_cache');

    function handle_hph_clear_cache() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $cache_type = sanitize_text_field($_POST['cache_type'] ?? 'all');
        $cleared = [];
        
        try {
            switch ($cache_type) {
                case 'transients':
                    // Clear theme-specific transients
                    hph_clear_theme_transients();
                    $cleared[] = 'Transients';
                    break;
                    
                case 'object':
                    // Clear object cache if available
                    if (function_exists('wp_cache_flush')) {
                        wp_cache_flush();
                        $cleared[] = 'Object Cache';
                    }
                    break;
                    
                case 'rewrite':
                    // Flush rewrite rules
                    flush_rewrite_rules();
                    $cleared[] = 'Rewrite Rules';
                    break;
                    
                case 'all':
                default:
                    // Clear all available caches
                    hph_clear_theme_transients();
                    if (function_exists('wp_cache_flush')) {
                        wp_cache_flush();
                    }
                    flush_rewrite_rules();
                    $cleared = ['Transients', 'Object Cache', 'Rewrite Rules'];
                    break;
            }
            
            wp_send_json_success([
                'message' => sprintf(__('Cache cleared: %s', 'happy-place-theme'), implode(', ', $cleared)),
                'cleared' => $cleared,
                'timestamp' => current_time('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error('Cache clearing failed: ' . $e->getMessage());
        }
    }
}

/**
 * Clear theme-specific transients
 */
if (!function_exists('hph_clear_theme_transients')) {
    function hph_clear_theme_transients() {
        global $wpdb;
        
        // Delete theme-specific transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_hph_%' 
             OR option_name LIKE '_transient_timeout_hph_%'
             OR option_name LIKE '_transient_hpt_%' 
             OR option_name LIKE '_transient_timeout_hpt_%'"
        );
        
        // Clean up expired transients while we're at it
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_timeout_%' 
             AND option_value < UNIX_TIMESTAMP()"
        );
    }
}

/**
 * Handle system status check
 */
if (!function_exists('handle_hph_system_status')) {
    add_action('wp_ajax_hph_system_status', 'handle_hph_system_status');

    function handle_hph_system_status() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $status = [];
        
        // PHP Version
        $status['php_version'] = [
            'label' => 'PHP Version',
            'value' => phpversion(),
            'status' => version_compare(phpversion(), '7.4.0', '>=') ? 'good' : 'warning',
            'recommendation' => version_compare(phpversion(), '7.4.0', '<') ? 'Update to PHP 7.4 or higher' : null
        ];
        
        // WordPress Version
        $wp_version = get_bloginfo('version');
        $status['wp_version'] = [
            'label' => 'WordPress Version',
            'value' => $wp_version,
            'status' => version_compare($wp_version, '6.0', '>=') ? 'good' : 'warning',
            'recommendation' => version_compare($wp_version, '6.0', '<') ? 'Update WordPress to 6.0 or higher' : null
        ];
        
        // Memory Limit
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = wp_convert_hr_to_bytes($memory_limit);
        $status['memory_limit'] = [
            'label' => 'PHP Memory Limit',
            'value' => $memory_limit,
            'status' => $memory_limit_bytes >= 256 * 1024 * 1024 ? 'good' : 'warning',
            'recommendation' => $memory_limit_bytes < 256 * 1024 * 1024 ? 'Increase memory limit to 256M or higher' : null
        ];
        
        // Plugin Status
        $hpt_plugin_active = is_plugin_active('happy-place/happy-place.php');
        $status['hpt_plugin'] = [
            'label' => 'Happy Place Plugin',
            'value' => $hpt_plugin_active ? 'Active' : 'Inactive',
            'status' => $hpt_plugin_active ? 'good' : 'error',
            'recommendation' => !$hpt_plugin_active ? 'Activate Happy Place plugin for full functionality' : null
        ];
        
        // Theme Version
        $theme_version = wp_get_theme()->get('Version');
        $status['theme_version'] = [
            'label' => 'Theme Version',
            'value' => $theme_version,
            'status' => 'info'
        ];
        
        // Permalinks
        $permalink_structure = get_option('permalink_structure');
        $status['permalinks'] = [
            'label' => 'Permalink Structure',
            'value' => $permalink_structure ?: 'Default',
            'status' => $permalink_structure ? 'good' : 'warning',
            'recommendation' => !$permalink_structure ? 'Use custom permalinks for better SEO' : null
        ];
        
        // Critical APIs
        $google_maps_key = get_option('hph_google_maps_api_key');
        $status['google_maps'] = [
            'label' => 'Google Maps API',
            'value' => $google_maps_key ? 'Configured' : 'Not Configured',
            'status' => $google_maps_key ? 'good' : 'warning',
            'recommendation' => !$google_maps_key ? 'Configure Google Maps API key for map functionality' : null
        ];
        
        // Object Cache
        $object_cache = wp_using_ext_object_cache();
        $status['object_cache'] = [
            'label' => 'Object Cache',
            'value' => $object_cache ? 'Active' : 'Not Active',
            'status' => $object_cache ? 'good' : 'info',
            'recommendation' => !$object_cache ? 'Consider enabling object caching for better performance' : null
        ];
        
        // Overall status
        $error_count = count(array_filter($status, function($item) { return $item['status'] === 'error'; }));
        $warning_count = count(array_filter($status, function($item) { return $item['status'] === 'warning'; }));
        
        $overall_status = 'good';
        if ($error_count > 0) {
            $overall_status = 'error';
        } elseif ($warning_count > 0) {
            $overall_status = 'warning';
        }
        
        wp_send_json_success([
            'status' => $status,
            'overall_status' => $overall_status,
            'error_count' => $error_count,
            'warning_count' => $warning_count,
            'last_checked' => current_time('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle performance optimization toggle
 */
if (!function_exists('handle_hph_toggle_optimization')) {
    add_action('wp_ajax_hph_toggle_optimization', 'handle_hph_toggle_optimization');

    function handle_hph_toggle_optimization() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $optimization = sanitize_text_field($_POST['optimization']);
        $enabled = filter_var($_POST['enabled'], FILTER_VALIDATE_BOOLEAN);
        
        $valid_optimizations = [
            'lazy_loading' => 'hph_enable_lazy_loading',
            'minify_assets' => 'hph_minify_assets',
            'optimize_images' => 'hph_optimize_images',
            'preload_critical_css' => 'hph_preload_critical_css',
            'cache_listings' => 'hph_cache_listings'
        ];
        
        if (!isset($valid_optimizations[$optimization])) {
            wp_send_json_error('Invalid optimization setting');
        }
        
        $option_name = $valid_optimizations[$optimization];
        $old_value = get_option($option_name);
        $new_value = $enabled ? '1' : '0';
        
        update_option($option_name, $new_value);
        
        // Clear related caches when toggling optimizations
        if ($optimization === 'minify_assets' || $optimization === 'preload_critical_css') {
            hph_clear_theme_transients();
        }
        
        wp_send_json_success([
            'optimization' => $optimization,
            'enabled' => $enabled,
            'old_value' => $old_value,
            'new_value' => $new_value,
            'message' => sprintf(
                __('%s %s successfully', 'happy-place-theme'),
                ucwords(str_replace('_', ' ', $optimization)),
                $enabled ? 'enabled' : 'disabled'
            )
        ]);
    }
}

/**
 * Handle bulk settings update
 */
if (!function_exists('handle_hph_bulk_settings_update')) {
    add_action('wp_ajax_hph_bulk_settings_update', 'handle_hph_bulk_settings_update');

    function handle_hph_bulk_settings_update() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $settings = $_POST['settings'] ?? [];
        if (!is_array($settings)) {
            wp_send_json_error('Invalid settings data');
        }
        
        $updated = [];
        $errors = [];
        
        foreach ($settings as $key => $value) {
            // Validate option key (must start with hph_)
            if (strpos($key, 'hph_') !== 0) {
                $errors[] = "Invalid option key: $key";
                continue;
            }
            
            // Sanitize based on option type
            $sanitized_value = hph_sanitize_option_value($key, $value);
            
            if (update_option($key, $sanitized_value)) {
                $updated[] = $key;
            } else {
                $errors[] = "Failed to update: $key";
            }
        }
        
        wp_send_json_success([
            'updated' => $updated,
            'errors' => $errors,
            'updated_count' => count($updated),
            'error_count' => count($errors),
            'message' => sprintf(__('%d settings updated successfully', 'happy-place-theme'), count($updated))
        ]);
    }
}

/**
 * Sanitize option value based on option key
 */
if (!function_exists('hph_sanitize_option_value')) {
    function hph_sanitize_option_value($key, $value) {
        // Email fields
        if (strpos($key, '_email') !== false) {
            return sanitize_email($value);
        }
        
        // URL fields
        if (strpos($key, '_url') !== false || strpos($key, '_logo') !== false) {
            return esc_url_raw($value);
        }
        
        // API keys
        if (strpos($key, '_api_key') !== false || strpos($key, '_secret') !== false) {
            return sanitize_text_field($value);
        }
        
        // Boolean fields (enable/disable)
        if (strpos($key, 'hph_enable_') === 0) {
            return $value ? '1' : '0';
        }
        
        // Numeric fields
        if (in_array($key, ['hph_cache_duration'])) {
            return intval($value);
        }
        
        // Array fields
        if (is_array($value)) {
            return array_map('sanitize_text_field', $value);
        }
        
        // Default: sanitize as text
        return sanitize_text_field($value);
    }
}