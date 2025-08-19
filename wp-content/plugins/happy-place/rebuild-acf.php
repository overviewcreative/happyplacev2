<?php
/**
 * ACF Complete Rebuild Script
 * Forces complete reload and rebuild of all ACF field groups
 * 
 * Access via: /wp-content/plugins/happy-place/rebuild-acf.php
 */

// Load WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Access denied. Administrator access required.');
}

echo "<h1>🔧 ACF Complete Rebuild</h1>";

// Step 1: Check ACF availability
echo "<div style='background: #f0f8ff; padding: 20px; border-left: 4px solid #0073aa; margin: 20px 0;'>";
echo "<h2>📊 Step 1: ACF Status Check</h2>";

$acf_available = class_exists('ACF') || function_exists('acf');
echo "<p><strong>ACF Available:</strong> " . ($acf_available ? '✅ Yes' : '❌ No') . "</p>";

if (!$acf_available) {
    echo "<p style='color: #d63638;'>❌ <strong>Cannot continue - ACF is not available!</strong></p>";
    echo "<p>Please ensure Advanced Custom Fields is installed and activated.</p>";
    echo "</div>";
    exit;
}

echo "<p><strong>ACF Version:</strong> " . (defined('ACF_VERSION') ? ACF_VERSION : 'Unknown') . "</p>";
echo "<p><strong>ACF Pro:</strong> " . (class_exists('ACF_PRO') ? '✅ Yes' : '❌ No') . "</p>";

$required_functions = ['acf_get_field_groups', 'acf_add_local_field_group', 'acf_delete_field_group'];
$missing_functions = [];
foreach ($required_functions as $func) {
    if (!function_exists($func)) {
        $missing_functions[] = $func;
    }
}

if (!empty($missing_functions)) {
    echo "<p style='color: #d63638;'>❌ Missing required ACF functions: " . implode(', ', $missing_functions) . "</p>";
    echo "</div>";
    exit;
}

echo "<p>✅ All required ACF functions are available.</p>";
echo "</div>";

// Step 2: Check existing field groups status
echo "<div style='background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
echo "<h2>🔍 Step 2: Current Field Groups Status</h2>";

$existing_groups = acf_get_field_groups();
$happy_place_groups = array_filter($existing_groups, function($group) {
    return strpos($group['key'], 'group_') === 0 || 
           strpos($group['key'], 'happy_place') !== false || 
           strpos($group['key'], 'hp_') !== false;
});

echo "<p><strong>Found " . count($happy_place_groups) . " Happy Place field groups currently loaded</strong></p>";

$local_count = 0;
$db_count = 0;

foreach ($happy_place_groups as $group) {
    $source = isset($group['local']) && $group['local'] === 'json' ? 'JSON (Local)' : 'Database';
    if ($source === 'JSON (Local)') {
        $local_count++;
        echo "<p>📁 {$group['title']} ({$group['key']}) - <strong>Loaded from JSON</strong></p>";
    } else {
        $db_count++;
        echo "<p>💾 {$group['title']} ({$group['key']}) - <strong>Stored in Database</strong></p>";
    }
}

echo "<p><strong>Summary:</strong> {$local_count} from JSON files, {$db_count} from database</p>";

if ($local_count > 0) {
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
    echo "<p>✅ <strong>Good news!</strong> Your field groups are loading from JSON files, which is the recommended approach.</p>";
    echo "<p>This means they're managed by your code and won't be lost if the database is reset.</p>";
    echo "</div>";
}

echo "</div>";

// Step 3: Clear WordPress caches
echo "<div style='background: #e8f5e8; padding: 20px; border-left: 4px solid #4caf50; margin: 20px 0;'>";
echo "<h2>🧹 Step 3: Clear Caches</h2>";

// Object cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "<p>✅ Object cache flushed</p>";
}

// ACF specific transients
delete_transient('acf_field_groups');
delete_transient('acf_local_field_groups');
echo "<p>✅ ACF transients cleared</p>";

// Rewrite rules
flush_rewrite_rules();
echo "<p>✅ Rewrite rules flushed</p>";

echo "</div>";

// Step 4: Force reload ACF Manager
echo "<div style='background: #e3f2fd; padding: 20px; border-left: 4px solid #2196f3; margin: 20px 0;'>";
echo "<h2>🔄 Step 4: Reload ACF Manager</h2>";

if (class_exists('HappyPlace\\Core\\ACF_Manager')) {
    $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
    echo "<p>✅ ACF Manager instance obtained</p>";
    
    // Force sync
    if (method_exists($acf_manager, 'force_sync_field_groups')) {
        $sync_result = $acf_manager->force_sync_field_groups();
        echo "<p><strong>Force sync result:</strong> " . ($sync_result !== false ? "✅ {$sync_result} field groups synced" : "❌ Sync failed") . "</p>";
    }
    
    // Check loaded groups
    $loaded_groups = $acf_manager->get_field_groups();
    echo "<p><strong>Field groups loaded by manager:</strong> " . count($loaded_groups) . "</p>";
    
    if (!empty($loaded_groups)) {
        echo "<h3>📋 Loaded Field Groups:</h3>";
        foreach ($loaded_groups as $key => $group) {
            echo "<p>• {$group['title']} ({$key})</p>";
        }
    }
} else {
    echo "<p>❌ ACF Manager class not found</p>";
}
echo "</div>";

// Step 5: Manual JSON Loading
echo "<div style='background: #f3e5f5; padding: 20px; border-left: 4px solid #9c27b0; margin: 20px 0;'>";
echo "<h2>📁 Step 5: Manual JSON Loading</h2>";

$json_path = WP_CONTENT_DIR . '/plugins/happy-place/includes/fields/acf-json/';
echo "<p><strong>Loading from:</strong> {$json_path}</p>";

if (!is_dir($json_path)) {
    echo "<p>❌ JSON directory not found!</p>";
} else {
    $json_files = glob($json_path . '*.json');
    $valid_files = array_filter($json_files, function($file) {
        $filename = basename($file);
        return strpos($filename, 'acf-export-') !== 0; // Skip export files
    });
    
    echo "<p><strong>Found " . count($valid_files) . " valid JSON files</strong></p>";
    
    $manually_loaded = 0;
    foreach ($valid_files as $file) {
        $filename = basename($file);
        $json_data = json_decode(file_get_contents($file), true);
        
        if ($json_data && isset($json_data['key'], $json_data['title'])) {
            try {
                acf_add_local_field_group($json_data);
                echo "<p>✅ Manually loaded: {$json_data['title']} ({$json_data['key']})</p>";
                $manually_loaded++;
            } catch (Exception $e) {
                echo "<p>❌ Failed to load {$json_data['title']}: {$e->getMessage()}</p>";
            }
        } else {
            echo "<p>⚠️ Invalid JSON in: {$filename}</p>";
        }
    }
    
    echo "<p><strong>Manually loaded {$manually_loaded} field groups</strong></p>";
}
echo "</div>";

// Step 6: Final Verification
echo "<div style='background: #e8f5e8; padding: 20px; border-left: 4px solid #4caf50; margin: 20px 0;'>";
echo "<h2>✅ Step 6: Final Verification</h2>";

$final_groups = acf_get_field_groups();
$final_happy_place = array_filter($final_groups, function($group) {
    return strpos($group['key'], 'group_') === 0;
});

echo "<p><strong>Total WordPress field groups:</strong> " . count($final_groups) . "</p>";
echo "<p><strong>Happy Place field groups:</strong> " . count($final_happy_place) . "</p>";

if (!empty($final_happy_place)) {
    echo "<h3>🎯 Successfully Loaded Field Groups:</h3>";
    foreach ($final_happy_place as $group) {
        $location_count = isset($group['location']) ? count($group['location']) : 0;
        echo "<p style='color: #2e7d32;'>✅ {$group['title']} ({$group['key']}) - {$location_count} location rules</p>";
        
        // Check location rules for listing post type
        if (isset($group['location']) && is_array($group['location'])) {
            $has_listing_rule = false;
            foreach ($group['location'] as $rule_group) {
                foreach ($rule_group as $rule) {
                    if (isset($rule['param']) && $rule['param'] === 'post_type' && 
                        isset($rule['value']) && $rule['value'] === 'listing') {
                        $has_listing_rule = true;
                        break 2;
                    }
                }
            }
            if ($has_listing_rule) {
                echo "<p style='margin-left: 20px; color: #1565c0; font-size: 14px;'>📍 Configured for 'listing' post type</p>";
            }
        }
    }
    
    // Test field group availability for listing post type specifically
    echo "<h3>🧪 Testing Field Groups for Listing Post Type:</h3>";
    $listing_groups = acf_get_field_groups(['post_type' => 'listing']);
    echo "<p><strong>Field groups available for 'listing' post type:</strong> " . count($listing_groups) . "</p>";
    
    if (!empty($listing_groups)) {
        foreach ($listing_groups as $group) {
            echo "<p style='color: #2e7d32; margin-left: 20px;'>📝 {$group['title']} will show on listings</p>";
        }
    } else {
        echo "<p style='color: #d32f2f; margin-left: 20px;'>⚠️ No field groups found for listing post type - this may indicate a location rule issue</p>";
    }
    
    echo "<div style='background: #4caf50; color: white; padding: 15px; border-radius: 4px; margin: 20px 0;'>";
    echo "<h3 style='margin: 0; color: white;'>🎉 SUCCESS!</h3>";
    echo "<p style='margin: 10px 0 0 0; color: white;'>ACF field groups have been successfully rebuilt and are now available!</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f44336; color: white; padding: 15px; border-radius: 4px; margin: 20px 0;'>";
    echo "<h3 style='margin: 0; color: white;'>❌ ISSUE REMAINS</h3>";
    echo "<p style='margin: 10px 0 0 0; color: white;'>Field groups are still not loading properly. Check debug logs for more details.</p>";
    echo "</div>";
}

echo "</div>";

// Debug Info
echo "<div style='background: #f8f9fa; padding: 20px; border-left: 4px solid #6c757d; margin: 20px 0;'>";
echo "<h2>🔍 Debug Information</h2>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>ACF Init Hook Fired:</strong> " . did_action('acf/init') . " times</p>";
echo "<p><strong>Init Hook Fired:</strong> " . did_action('init') . " times</p>";
echo "<p><strong>Current Hook:</strong> " . current_filter() . "</p>";
echo "<p><strong>Is Admin:</strong> " . (is_admin() ? 'Yes' : 'No') . "</p>";
echo "</div>";

echo "<p><a href='" . admin_url() . "' style='background: #0073aa; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;'>← Return to WordPress Admin</a></p>";
echo "<p><a href='" . admin_url('edit.php?post_type=listing') . "' style='background: #00a32a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;'>🏠 Check Listings (Test Field Groups)</a></p>";
?>