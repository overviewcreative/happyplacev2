<?php
/**
 * ACF Pro Sync Management Tool
 * Enables ACF Pro to properly manage Happy Place field groups
 * 
 * Access via: /wp-content/plugins/happy-place/acf-pro-sync.php
 */

// Load WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Access denied. Administrator access required.');
}

echo "<h1>🔄 ACF Pro Sync Management</h1>";

// Handle actions
if (isset($_GET['action'])) {
    echo "<div style='background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
    echo "<h2>🚀 Action Results</h2>";
    
    $acf_manager = null;
    if (class_exists('HappyPlace\\Core\\ACF_Manager')) {
        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
    }
    
    switch ($_GET['action']) {
        case 'sync_to_db':
            echo "<h3>📥 Syncing JSON to Database</h3>";
            
            if ($acf_manager && method_exists($acf_manager, 'force_json_to_db_sync')) {
                $result = $acf_manager->force_json_to_db_sync();
                
                if ($result['success']) {
                    echo "<p>✅ <strong>Successfully synced {$result['synced']} field groups to database</strong></p>";
                    if (!empty($result['errors'])) {
                        echo "<div style='background: #ffebee; padding: 10px; border-radius: 4px;'>";
                        echo "<h4>⚠️ Errors encountered:</h4>";
                        foreach ($result['errors'] as $error) {
                            echo "<p>• {$error}</p>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<p>❌ Sync failed: {$result['message']}</p>";
                }
            } else {
                echo "<p>❌ ACF Manager sync method not available</p>";
            }
            break;
            
        case 'import_from_db':
            echo "<h3>📤 Importing Database to JSON</h3>";
            
            if ($acf_manager && method_exists($acf_manager, 'import_db_field_groups_to_json')) {
                $result = $acf_manager->import_db_field_groups_to_json();
                
                if ($result['success']) {
                    echo "<p>✅ <strong>Successfully imported {$result['imported']} field groups to JSON</strong></p>";
                    if (!empty($result['errors'])) {
                        echo "<div style='background: #ffebee; padding: 10px; border-radius: 4px;'>";
                        echo "<h4>⚠️ Errors encountered:</h4>";
                        foreach ($result['errors'] as $error) {
                            echo "<p>• {$error}</p>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<p>❌ Import failed: {$result['message']}</p>";
                }
            } else {
                echo "<p>❌ ACF Manager import method not available</p>";
            }
            break;
            
        case 'clear_db_groups':
            echo "<h3>🗑️ Clearing Database Field Groups</h3>";
            
            if (function_exists('acf_get_field_groups') && function_exists('acf_delete_field_group')) {
                $db_groups = acf_get_field_groups();
                $deleted_count = 0;
                
                foreach ($db_groups as $group) {
                    // Only delete Happy Place groups that are also in JSON
                    if ((strpos($group['key'], 'group_') === 0 || 
                         strpos($group['key'], 'happy_place') !== false || 
                         strpos($group['key'], 'hp_') !== false) &&
                        (!isset($group['local']) || $group['local'] !== 'json')) {
                        
                        $json_file = WP_CONTENT_DIR . '/plugins/happy-place/includes/fields/acf-json/' . $group['key'] . '.json';
                        if (file_exists($json_file)) {
                            try {
                                if (acf_delete_field_group($group['key'])) {
                                    echo "<p>🗑️ Deleted from database: {$group['title']}</p>";
                                    $deleted_count++;
                                }
                            } catch (Exception $e) {
                                echo "<p>❌ Failed to delete {$group['title']}: {$e->getMessage()}</p>";
                            }
                        }
                    }
                }
                
                echo "<p><strong>Deleted {$deleted_count} field groups from database</strong></p>";
                echo "<p><em>JSON field groups are preserved and will continue to load automatically</em></p>";
            } else {
                echo "<p>❌ ACF functions not available for database cleanup</p>";
            }
            break;
    }
    
    echo "</div>";
    echo "<p><a href='?' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>← Back to Sync Management</a></p>";
    echo "<hr>";
}

// Check ACF Pro status
echo "<div style='background: #f0f8ff; padding: 20px; border-left: 4px solid #0073aa; margin: 20px 0;'>";
echo "<h2>📊 ACF Pro Status</h2>";

$acf_pro_available = class_exists('ACF_PRO');
echo "<p><strong>ACF Pro:</strong> " . ($acf_pro_available ? '✅ Available' : '❌ Not Available') . "</p>";

if (!$acf_pro_available) {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
    echo "<p>❌ <strong>ACF Pro is required for full sync functionality.</strong></p>";
    echo "<p>Without ACF Pro, field groups can only be managed via JSON files.</p>";
    echo "</div>";
} else {
    echo "<p><strong>ACF Version:</strong> " . (defined('ACF_VERSION') ? ACF_VERSION : 'Unknown') . "</p>";
    
    // Check if sync is available
    if (function_exists('acf_get_field_groups')) {
        $total_groups = count(acf_get_field_groups());
        echo "<p><strong>Total Field Groups:</strong> {$total_groups}</p>";
    }
}

echo "</div>";

// Current field groups status
echo "<div style='background: #e8f5e8; padding: 20px; border-left: 4px solid #4caf50; margin: 20px 0;'>";
echo "<h2>📋 Current Field Groups Status</h2>";

if (function_exists('acf_get_field_groups')) {
    $all_groups = acf_get_field_groups();
    $happy_place_groups = array_filter($all_groups, function($group) {
        return strpos($group['key'], 'group_') === 0;
    });
    
    echo "<p><strong>Happy Place Field Groups:</strong> " . count($happy_place_groups) . "</p>";
    
    $json_groups = 0;
    $db_groups = 0;
    
    foreach ($happy_place_groups as $group) {
        $is_json = isset($group['local']) && $group['local'] === 'json';
        if ($is_json) {
            $json_groups++;
        } else {
            $db_groups++;
        }
        
        $source_icon = $is_json ? '📁' : '💾';
        $source_text = $is_json ? 'JSON' : 'Database';
        echo "<p>{$source_icon} {$group['title']} ({$group['key']}) - <strong>{$source_text}</strong></p>";
    }
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
    echo "<p><strong>Summary:</strong> {$json_groups} from JSON, {$db_groups} from database</p>";
    echo "</div>";
    
} else {
    echo "<p>❌ ACF functions not available</p>";
}

echo "</div>";

// JSON files status
echo "<div style='background: #e3f2fd; padding: 20px; border-left: 4px solid #2196f3; margin: 20px 0;'>";
echo "<h2>📁 JSON Files Status</h2>";

$json_path = WP_CONTENT_DIR . '/plugins/happy-place/includes/fields/acf-json/';
echo "<p><strong>JSON Directory:</strong> {$json_path}</p>";
echo "<p><strong>Directory Exists:</strong> " . (is_dir($json_path) ? '✅ Yes' : '❌ No') . "</p>";
echo "<p><strong>Directory Writable:</strong> " . (is_writable($json_path) ? '✅ Yes' : '❌ No') . "</p>";

if (is_dir($json_path)) {
    $json_files = glob($json_path . '*.json');
    $valid_files = array_filter($json_files, function($file) {
        $filename = basename($file);
        return strpos($filename, 'acf-export-') !== 0;
    });
    
    echo "<p><strong>Valid JSON Files:</strong> " . count($valid_files) . "</p>";
    
    foreach ($valid_files as $file) {
        $filename = basename($file);
        $json_data = json_decode(file_get_contents($file), true);
        
        if ($json_data && isset($json_data['key'], $json_data['title'])) {
            echo "<p>📄 {$filename} - {$json_data['title']} ({$json_data['key']})</p>";
        } else {
            echo "<p>⚠️ {$filename} - Invalid JSON format</p>";
        }
    }
}

echo "</div>";

// Sync actions
if ($acf_pro_available) {
    echo "<div style='background: #f3e5f5; padding: 20px; border-left: 4px solid #9c27b0; margin: 20px 0;'>";
    echo "<h2>🔄 Sync Actions</h2>";
    
    echo "<div style='display: flex; gap: 15px; flex-wrap: wrap; margin: 20px 0;'>";
    
    // Sync to database
    echo "<div style='background: white; padding: 15px; border-radius: 8px; border: 1px solid #ddd; flex: 1; min-width: 250px;'>";
    echo "<h3>📥 JSON → Database</h3>";
    echo "<p>Import JSON field groups into the database for ACF Pro management.</p>";
    echo "<p><em>After this, you can edit field groups in ACF Pro admin.</em></p>";
    echo "<a href='?action=sync_to_db' style='background: #2196f3; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;'>📥 Sync to Database</a>";
    echo "</div>";
    
    // Import from database
    echo "<div style='background: white; padding: 15px; border-radius: 8px; border: 1px solid #ddd; flex: 1; min-width: 250px;'>";
    echo "<h3>📤 Database → JSON</h3>";
    echo "<p>Export database field groups back to JSON files for code management.</p>";
    echo "<p><em>Run this after making changes in ACF Pro admin.</em></p>";
    echo "<a href='?action=import_from_db' style='background: #4caf50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;'>📤 Export to JSON</a>";
    echo "</div>";
    
    // Clear database
    echo "<div style='background: white; padding: 15px; border-radius: 8px; border: 1px solid #ddd; flex: 1; min-width: 250px;'>";
    echo "<h3>🗑️ Clean Database</h3>";
    echo "<p>Remove field groups from database while keeping JSON files.</p>";
    echo "<p><em>Use this to clean up after development.</em></p>";
    echo "<a href='?action=clear_db_groups' onclick='return confirm(\"Are you sure? This will delete database field groups that have JSON backups.\")' style='background: #f44336; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;'>🗑️ Clear Database</a>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 4px; margin: 20px 0;'>";
    echo "<h3>🔄 Recommended Workflow</h3>";
    echo "<ol>";
    echo "<li><strong>Development:</strong> Keep field groups as JSON files (current setup)</li>";
    echo "<li><strong>Management:</strong> Sync to database when you need to edit in ACF Pro admin</li>";
    echo "<li><strong>Save Changes:</strong> Export back to JSON after making changes</li>";
    echo "<li><strong>Clean Up:</strong> Clear database groups to keep only JSON versions</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "</div>";
}

// Instructions
echo "<div style='background: #f8f9fa; padding: 20px; border-left: 4px solid #6c757d; margin: 20px 0;'>";
echo "<h2>📖 Instructions</h2>";

echo "<h3>Current Situation:</h3>";
echo "<p>Your field groups are currently loaded from JSON files, which is the <strong>recommended approach</strong> for developers. However, this means they're not directly manageable in the ACF Pro admin interface.</p>";

echo "<h3>To Enable ACF Pro Management:</h3>";
echo "<ol>";
echo "<li><strong>Sync to Database:</strong> Click 'Sync to Database' to import your JSON field groups into the WordPress database</li>";
echo "<li><strong>Manage in ACF Pro:</strong> Go to WordPress Admin → Custom Fields to edit your field groups</li>";
echo "<li><strong>Export Changes:</strong> After making changes, use 'Export to JSON' to save them back to your code</li>";
echo "<li><strong>Clean Up (Optional):</strong> Use 'Clear Database' to remove database copies and keep only JSON versions</li>";
echo "</ol>";

echo "<h3>Best Practices:</h3>";
echo "<ul>";
echo "<li><strong>Version Control:</strong> Always export changes back to JSON for version control</li>";
echo "<li><strong>Development:</strong> Work with JSON files for consistency across environments</li>";
echo "<li><strong>Production:</strong> Use JSON files to avoid database dependencies</li>";
echo "<li><strong>Team Collaboration:</strong> JSON files can be shared via Git, database changes cannot</li>";
echo "</ul>";

echo "</div>";

// Navigation
echo "<div style='margin: 30px 0; text-align: center;'>";
echo "<a href='" . admin_url() . "' style='background: #0073aa; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 0 10px; font-weight: bold;'>← WordPress Admin</a>";
if ($acf_pro_available) {
    echo "<a href='" . admin_url('edit.php?post_type=acf-field-group') . "' style='background: #00a32a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 0 10px; font-weight: bold;'>🎯 ACF Field Groups</a>";
}
echo "<a href='" . admin_url('edit.php?post_type=listing') . "' style='background: #ff9800; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 0 10px; font-weight: bold;'>🏠 Test Listings</a>";
echo "</div>";

?>