<?php
/**
 * Debug ACF Field Group Sync
 * Direct access to force ACF sync and diagnostics
 */

// Load WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

echo "<h1>🔧 Debug ACF Field Group Sync</h1>";

// Check ACF availability
echo "<h2>📊 ACF Status Check</h2>";
echo "<p><strong>ACF Class:</strong> " . (class_exists('ACF') ? '✅ Available' : '❌ Not Available') . "</p>";
echo "<p><strong>ACF Functions:</strong> " . (function_exists('acf') ? '✅ Available' : '❌ Not Available') . "</p>";
echo "<p><strong>ACF Pro:</strong> " . (class_exists('ACF_PRO') ? '✅ Available' : '❌ Not Available') . "</p>";
echo "<p><strong>ACF Version:</strong> " . (defined('ACF_VERSION') ? ACF_VERSION : 'Unknown') . "</p>";

// Check key ACF functions
$required_functions = ['acf_get_field_groups', 'acf_add_local_field_group', 'acf_import_field_group'];
echo "<h3>🔧 Required ACF Functions</h3>";
foreach ($required_functions as $func) {
    echo "<p><strong>{$func}:</strong> " . (function_exists($func) ? '✅ Available' : '❌ Not Available') . "</p>";
}

// Check JSON files
echo "<h2>📁 JSON Files Status</h2>";
$json_path = WP_CONTENT_DIR . '/plugins/happy-place/includes/fields/acf-json/';
echo "<p><strong>JSON Directory:</strong> {$json_path}</p>";
echo "<p><strong>Directory Exists:</strong> " . (is_dir($json_path) ? '✅ Yes' : '❌ No') . "</p>";

if (is_dir($json_path)) {
    $json_files = glob($json_path . '*.json');
    echo "<p><strong>JSON Files Found:</strong> " . count($json_files) . "</p>";
    
    $valid_groups = 0;
    foreach ($json_files as $file) {
        $filename = basename($file);
        $json_data = json_decode(file_get_contents($file), true);
        
        if ($json_data && isset($json_data['key'], $json_data['title'])) {
            $valid_groups++;
            echo "<p>✅ {$filename} - {$json_data['title']} ({$json_data['key']})</p>";
        } else {
            echo "<p>❌ {$filename} - Invalid JSON structure</p>";
        }
    }
    echo "<p><strong>Valid Field Groups:</strong> {$valid_groups}</p>";
}

// Check ACF Manager
echo "<h2>🎯 Happy Place ACF Manager</h2>";
if (class_exists('HappyPlace\\Core\\ACF_Manager')) {
    echo "<p>✅ <strong>ACF Manager class found</strong></p>";
    
    $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
    echo "<p><strong>Manager Instance:</strong> " . (is_object($acf_manager) ? '✅ Created' : '❌ Failed') . "</p>";
    
    if (is_object($acf_manager)) {
        echo "<p><strong>ACF Available in Manager:</strong> " . ($acf_manager->is_acf_available() ? '✅ Yes' : '❌ No') . "</p>";
        
        $loaded_groups = $acf_manager->get_field_groups();
        echo "<p><strong>Field Groups Loaded by Manager:</strong> " . count($loaded_groups) . "</p>";
        
        if (!empty($loaded_groups)) {
            echo "<h3>📋 Manager Field Groups</h3>";
            foreach ($loaded_groups as $key => $group) {
                echo "<p>• {$group['title']} ({$key})</p>";
            }
        }
    }
} else {
    echo "<p>❌ <strong>ACF Manager class not found</strong></p>";
}

// Check WordPress registered field groups
echo "<h2>🎯 WordPress ACF Field Groups</h2>";
if (function_exists('acf_get_field_groups')) {
    $wp_field_groups = acf_get_field_groups();
    echo "<p><strong>Total Field Groups in WordPress:</strong> " . count($wp_field_groups) . "</p>";
    
    $happy_place_groups = array_filter($wp_field_groups, function($group) {
        return strpos($group['key'], 'group_') === 0;
    });
    
    echo "<p><strong>Happy Place Field Groups in WordPress:</strong> " . count($happy_place_groups) . "</p>";
    
    if (!empty($happy_place_groups)) {
        echo "<h3>📋 Registered in WordPress</h3>";
        foreach ($happy_place_groups as $group) {
            echo "<p>• {$group['title']} ({$group['key']})</p>";
        }
    }
} else {
    echo "<p>❌ <strong>acf_get_field_groups function not available</strong></p>";
}

// MANUAL SYNC ATTEMPT
echo "<h2>🔄 Manual Sync Attempt</h2>";

if (isset($_GET['force_sync']) && $_GET['force_sync'] === '1') {
    echo "<div style='background: #fff3cd; padding: 20px; border: 1px solid #ffc107; border-radius: 4px; margin: 20px 0;'>";
    echo "<h3>🚀 Forcing Manual Sync...</h3>";
    
    if (function_exists('acf_add_local_field_group') && is_dir($json_path)) {
        $json_files = glob($json_path . '*.json');
        $loaded_count = 0;
        
        foreach ($json_files as $file) {
            $filename = basename($file);
            $json_data = json_decode(file_get_contents($file), true);
            
            if ($json_data && isset($json_data['key'], $json_data['title'])) {
                try {
                    // Add local field group
                    acf_add_local_field_group($json_data);
                    echo "<p>✅ Loaded: {$json_data['title']}</p>";
                    $loaded_count++;
                } catch (Exception $e) {
                    echo "<p>❌ Failed to load {$json_data['title']}: {$e->getMessage()}</p>";
                }
            }
        }
        
        echo "<p><strong>Manual sync completed! Loaded {$loaded_count} field groups.</strong></p>";
        
        // Verify after manual loading
        if (function_exists('acf_get_field_groups')) {
            $wp_field_groups_after = acf_get_field_groups();
            $happy_place_groups_after = array_filter($wp_field_groups_after, function($group) {
                return strpos($group['key'], 'group_') === 0;
            });
            echo "<p><strong>Happy Place groups now in WordPress:</strong> " . count($happy_place_groups_after) . "</p>";
        }
        
    } else {
        echo "<p>❌ Cannot perform manual sync - ACF functions not available</p>";
    }
    echo "</div>";
    echo "<p><a href='?'>← Back to Diagnostics</a></p>";
} else {
    echo "<p><a href='?force_sync=1' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>🔄 Force Manual Sync</a></p>";
}

// Debug information
echo "<h2>🔍 Additional Debug Info</h2>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</p>";
echo "<p><strong>Current User Can Manage Options:</strong> " . (current_user_can('manage_options') ? '✅ Yes' : '❌ No') . "</p>";
echo "<p><strong>ACF Init Hook Fired:</strong> " . did_action('acf/init') . " times</p>";
echo "<p><strong>Init Hook Fired:</strong> " . did_action('init') . " times</p>";
echo "<p><strong>Admin Init Hook Fired:</strong> " . did_action('admin_init') . " times</p>";

echo "<p><a href='" . admin_url() . "' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>← Back to WordPress Admin</a></p>";
?>