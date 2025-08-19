<?php
/**
 * Force ACF Field Group Sync
 * Access via: /wp-content/plugins/happy-place/force-acf-sync.php
 */

// Load WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Check if user is logged in and has proper capabilities
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('Access denied. Please login as an administrator.');
}

echo "<h1>🔄 Force ACF Field Group Sync</h1>";

// Get ACF Manager instance
if (class_exists('HappyPlace\\Core\\ACF_Manager')) {
    $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
    
    echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h2>📊 Current Status</h2>";
    echo "<p><strong>ACF Available:</strong> " . ($acf_manager->is_acf_available() ? '✅ YES' : '❌ NO') . "</p>";
    echo "<p><strong>Current Field Groups Loaded:</strong> " . count($acf_manager->get_field_groups()) . "</p>";
    
    $field_groups = $acf_manager->get_field_groups();
    if (!empty($field_groups)) {
        echo "<h3>📋 Currently Loaded Field Groups:</h3>";
        foreach ($field_groups as $key => $group) {
            echo "<p>• {$group['title']} ({$key})</p>";
        }
    }
    echo "</div>";
    
    // Handle sync request
    if (isset($_GET['sync']) && $_GET['sync'] === '1') {
        echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h2>🔄 Forcing Field Group Sync...</h2>";
        
        $result = $acf_manager->force_sync_field_groups();
        
        if ($result !== false) {
            echo "<p style='color: #28a745;'>✅ <strong>Sync completed successfully!</strong></p>";
            echo "<p>Loaded <strong>{$result}</strong> field groups from JSON files.</p>";
            
            // Show updated field groups
            $updated_field_groups = $acf_manager->get_field_groups();
            if (!empty($updated_field_groups)) {
                echo "<h3>📋 Field Groups After Sync:</h3>";
                foreach ($updated_field_groups as $key => $group) {
                    echo "<p>• {$group['title']} ({$key})</p>";
                }
            }
        } else {
            echo "<p style='color: #dc3545;'>❌ <strong>Sync failed!</strong></p>";
            echo "<p>Check debug log for error details.</p>";
        }
        echo "</div>";
        
        echo "<p><a href='?'>← Back to Status</a></p>";
    } else {
        // Show sync button
        echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h2>🚀 Manual Sync</h2>";
        echo "<p>Click the button below to force reload all ACF field groups from JSON files:</p>";
        echo "<p><a href='?sync=1' class='button button-primary' style='background: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 4px; display: inline-block;'>🔄 Force Sync Field Groups</a></p>";
        echo "</div>";
    }
    
    // Debug information
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h2>🔍 Debug Information</h2>";
    echo "<p><strong>WordPress ACF Field Groups:</strong> " . (function_exists('acf_get_field_groups') ? count(acf_get_field_groups()) : 'Function not available') . "</p>";
    echo "<p><strong>ACF Version:</strong> " . (defined('ACF_VERSION') ? ACF_VERSION : 'Unknown') . "</p>";
    echo "<p><strong>Did ACF Init:</strong> " . did_action('acf/init') . " times</p>";
    echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "<p><strong>JSON Directory:</strong> " . (is_dir(WP_CONTENT_DIR . '/plugins/happy-place/includes/fields/acf-json/') ? '✅ EXISTS' : '❌ MISSING') . "</p>";
    
    // Check JSON files
    $json_files = glob(WP_CONTENT_DIR . '/plugins/happy-place/includes/fields/acf-json/*.json');
    echo "<p><strong>JSON Files Found:</strong> " . count($json_files) . "</p>";
    
    if (!empty($json_files)) {
        echo "<h3>📁 JSON Files:</h3>";
        foreach ($json_files as $file) {
            $filename = basename($file);
            $filesize = round(filesize($file) / 1024, 2);
            echo "<p>• {$filename} ({$filesize} KB)</p>";
        }
    }
    echo "</div>";
    
} else {
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h2>❌ Error</h2>";
    echo "<p><strong>ACF Manager class not found!</strong></p>";
    echo "<p>The Happy Place plugin may not be properly loaded or activated.</p>";
    echo "</div>";
}

echo "<p><a href='" . admin_url() . "'>← Back to WordPress Admin</a></p>";
?>