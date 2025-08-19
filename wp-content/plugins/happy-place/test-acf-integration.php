<?php
/**
 * ACF Integration Test
 * Access via: /wp-content/plugins/happy-place/test-acf-integration.php
 */

// Load WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

echo "<h1>🔍 ACF Integration Test</h1>";

// Check ACF availability
echo "<h2>📋 ACF Status</h2>";
echo "<p><strong>ACF Class Exists:</strong> " . (class_exists('ACF') ? '✅ YES' : '❌ NO') . "</p>";
echo "<p><strong>ACF Function Exists:</strong> " . (function_exists('acf') ? '✅ YES' : '❌ NO') . "</p>";
echo "<p><strong>ACF Get Field Groups Function:</strong> " . (function_exists('acf_get_field_groups') ? '✅ YES' : '❌ NO') . "</p>";
echo "<p><strong>ACF Add Local Field Group Function:</strong> " . (function_exists('acf_add_local_field_group') ? '✅ YES' : '❌ NO') . "</p>";

// Check ACF Manager class
echo "<h2>🔧 Happy Place ACF Manager</h2>";
if (class_exists('HappyPlace\\Core\\ACF_Manager')) {
    echo "<p>✅ <strong>ACF Manager class found</strong></p>";
    
    $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
    if ($acf_manager) {
        echo "<p>✅ <strong>ACF Manager instance created</strong></p>";
        echo "<p><strong>ACF Available:</strong> " . ($acf_manager->is_acf_available() ? '✅ YES' : '❌ NO') . "</p>";
        
        // Try to get field groups
        $field_groups = $acf_manager->get_field_groups();
        echo "<p><strong>Loaded Field Groups:</strong> " . count($field_groups) . "</p>";
        
        if (!empty($field_groups)) {
            echo "<h3>📋 Loaded Field Groups:</h3>";
            foreach ($field_groups as $key => $group) {
                echo "<p>• {$group['title']} ({$key})</p>";
            }
        }
    } else {
        echo "<p>❌ Failed to create ACF Manager instance</p>";
    }
} else {
    echo "<p>❌ <strong>ACF Manager class not found</strong></p>";
}

// Check JSON files
echo "<h2>📁 ACF JSON Files</h2>";
$json_path = WP_CONTENT_DIR . '/plugins/happy-place/includes/fields/acf-json/';
echo "<p><strong>JSON Path:</strong> {$json_path}</p>";

if (is_dir($json_path)) {
    $json_files = glob($json_path . '*.json');
    echo "<p><strong>JSON Files Found:</strong> " . count($json_files) . "</p>";
    
    foreach ($json_files as $file) {
        $filename = basename($file);
        $filesize = round(filesize($file) / 1024, 2);
        echo "<p>• {$filename} ({$filesize} KB)</p>";
        
        // Try to read and validate JSON
        $json_content = file_get_contents($file);
        $json_data = json_decode($json_content, true);
        
        if ($json_data && isset($json_data['key'], $json_data['title'])) {
            echo "<div style='margin-left: 20px; color: #666; font-size: 12px;'>";
            echo "✅ Valid: {$json_data['title']} ({$json_data['key']})";
            if (isset($json_data['fields'])) {
                echo " - " . count($json_data['fields']) . " fields";
            }
            echo "</div>";
        } else {
            echo "<div style='margin-left: 20px; color: #d63638; font-size: 12px;'>❌ Invalid JSON format</div>";
        }
    }
} else {
    echo "<p>❌ <strong>JSON directory not found</strong></p>";
}

// Check WordPress field groups
echo "<h2>🎯 WordPress ACF Field Groups</h2>";
if (function_exists('acf_get_field_groups')) {
    $all_groups = acf_get_field_groups();
    echo "<p><strong>Total Field Groups in WordPress:</strong> " . count($all_groups) . "</p>";
    
    $our_groups = array_filter($all_groups, function($group) {
        return strpos($group['key'], 'group_') === 0;
    });
    
    echo "<p><strong>Happy Place Field Groups in WordPress:</strong> " . count($our_groups) . "</p>";
    
    if (!empty($our_groups)) {
        echo "<h3>📋 Registered Field Groups:</h3>";
        foreach ($our_groups as $group) {
            $location_count = isset($group['location']) ? count($group['location']) : 0;
            echo "<p>• {$group['title']} ({$group['key']}) - {$location_count} location rules</p>";
        }
    }
} else {
    echo "<p>❌ <strong>ACF get field groups function not available</strong></p>";
}

// Manual field group loading test
echo "<h2>🧪 Manual Field Group Loading Test</h2>";
if (class_exists('HappyPlace\\Core\\ACF_Manager') && function_exists('acf_add_local_field_group')) {
    echo "<p>🔄 <strong>Attempting to manually load field groups...</strong></p>";
    
    $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
    
    // Try to call the field loading methods directly
    if (method_exists($acf_manager, 'acf_init')) {
        echo "<p>⚡ Calling acf_init() method directly...</p>";
        $acf_manager->acf_init();
        
        // Check if field groups are now loaded
        $field_groups_after = $acf_manager->get_field_groups();
        echo "<p><strong>Field Groups After Manual Init:</strong> " . count($field_groups_after) . "</p>";
        
        if (!empty($field_groups_after)) {
            echo "<h4>✅ Successfully Loaded:</h4>";
            foreach ($field_groups_after as $key => $group) {
                echo "<p>• {$group['title']} ({$key})</p>";
            }
        }
    } else {
        echo "<p>❌ acf_init method not found</p>";
    }
} else {
    echo "<p>❌ <strong>Cannot perform manual loading test</strong></p>";
}

echo "<h2>🔍 Debug Info</h2>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</p>";
echo "<p><strong>ACF Version:</strong> " . (defined('ACF_VERSION') ? ACF_VERSION : 'Unknown') . "</p>";
echo "<p><strong>Current Hook:</strong> " . current_filter() . "</p>";
echo "<p><strong>Did Action 'acf/init':</strong> " . did_action('acf/init') . "</p>";