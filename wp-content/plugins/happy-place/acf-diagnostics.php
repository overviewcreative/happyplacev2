<?php
/**
 * ACF Complete Diagnostics
 * Access via: /wp-content/plugins/happy-place/acf-diagnostics.php
 */

// Load WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Check if user is logged in and has proper capabilities
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('Access denied. Please login as an administrator.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>🔍 ACF Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { background: #e8f5e8; border: 1px solid #4caf50; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .error { background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .info { background: #e3f2fd; border: 1px solid #2196f3; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 10px 0; border-radius: 4px; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; font-size: 12px; border-radius: 4px; }
        table { border-collapse: collapse; width: 100%; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .button { background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
        .button:hover { background: #005a87; color: white; }
    </style>
</head>
<body>

<h1>🔍 Complete ACF Diagnostics</h1>

<?php
// Handle actions
if (isset($_GET['action'])) {
    echo "<div class='info'>";
    echo "<h2>🔄 Action Results</h2>";
    
    switch ($_GET['action']) {
        case 'sync_from_json':
            echo "<p><strong>Syncing field groups from JSON...</strong></p>";
            
            if (function_exists('acf_get_field_groups') && function_exists('acf_import_field_group')) {
                $json_path = WP_CONTENT_DIR . '/plugins/happy-place/includes/fields/acf-json/';
                $imported = 0;
                $errors = [];
                
                $json_files = glob($json_path . '*.json');
                foreach ($json_files as $file) {
                    $json_data = json_decode(file_get_contents($file), true);
                    if ($json_data && isset($json_data['key'])) {
                        try {
                            acf_import_field_group($json_data);
                            $imported++;
                            echo "<p>✅ Imported: {$json_data['title']}</p>";
                        } catch (Exception $e) {
                            $errors[] = "Failed to import {$json_data['title']}: " . $e->getMessage();
                        }
                    }
                }
                
                echo "<p><strong>Summary:</strong> Imported {$imported} field groups</p>";
                if (!empty($errors)) {
                    echo "<div class='error'>";
                    foreach ($errors as $error) {
                        echo "<p>❌ {$error}</p>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<div class='error'>";
                echo "<p>❌ ACF import functions not available</p>";
                echo "</div>";
            }
            break;
            
        case 'clear_cache':
            echo "<p><strong>Clearing WordPress caches...</strong></p>";
            
            // Clear various WordPress caches
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
                echo "<p>✅ Object cache flushed</p>";
            }
            
            // Clear transients
            delete_transient('acf_field_groups');
            delete_transient('acf_local_field_groups');
            echo "<p>✅ ACF transients cleared</p>";
            
            // Flush rewrite rules
            flush_rewrite_rules();
            echo "<p>✅ Rewrite rules flushed</p>";
            
            echo "<p><strong>Cache clearing completed!</strong></p>";
            break;
    }
    echo "</div>";
    echo "<p><a href='?' class='button'>← Back to Diagnostics</a></p><hr>";
}
?>

<!-- Action Buttons -->
<div class="info">
    <h2>🚀 Quick Actions</h2>
    <a href="?action=sync_from_json" class="button">📥 Force Import from JSON</a>
    <a href="?action=clear_cache" class="button">🗑️ Clear All Caches</a>
    <a href="force-acf-sync.php" class="button">🔄 Run Field Group Sync</a>
</div>

<!-- ACF Status -->
<div class="<?php echo (class_exists('ACF') || function_exists('acf')) ? 'success' : 'error'; ?>">
    <h2>📊 ACF Status</h2>
    <table>
        <tr><td><strong>ACF Class Exists</strong></td><td><?php echo class_exists('ACF') ? '✅ YES' : '❌ NO'; ?></td></tr>
        <tr><td><strong>ACF Function Exists</strong></td><td><?php echo function_exists('acf') ? '✅ YES' : '❌ NO'; ?></td></tr>
        <tr><td><strong>ACF Version</strong></td><td><?php echo defined('ACF_VERSION') ? ACF_VERSION : 'Unknown'; ?></td></tr>
        <tr><td><strong>ACF Pro Active</strong></td><td><?php echo class_exists('ACF_PRO') ? '✅ YES' : '❌ NO'; ?></td></tr>
        <tr><td><strong>ACF Init Fired</strong></td><td><?php echo did_action('acf/init'); ?> times</td></tr>
    </table>
</div>

<!-- ACF Functions Status -->
<div class="info">
    <h2>🔧 ACF Functions</h2>
    <table>
        <tr><td><strong>acf_get_field_groups()</strong></td><td><?php echo function_exists('acf_get_field_groups') ? '✅ Available' : '❌ Missing'; ?></td></tr>
        <tr><td><strong>acf_add_local_field_group()</strong></td><td><?php echo function_exists('acf_add_local_field_group') ? '✅ Available' : '❌ Missing'; ?></td></tr>
        <tr><td><strong>acf_import_field_group()</strong></td><td><?php echo function_exists('acf_import_field_group') ? '✅ Available' : '❌ Missing'; ?></td></tr>
        <tr><td><strong>get_field()</strong></td><td><?php echo function_exists('get_field') ? '✅ Available' : '❌ Missing'; ?></td></tr>
    </table>
</div>

<!-- WordPress Field Groups -->
<div class="info">
    <h2>📋 WordPress ACF Field Groups</h2>
    <?php
    if (function_exists('acf_get_field_groups')) {
        $all_groups = acf_get_field_groups();
        echo "<p><strong>Total Field Groups:</strong> " . count($all_groups) . "</p>";
        
        if (!empty($all_groups)) {
            echo "<table>";
            echo "<tr><th>Title</th><th>Key</th><th>Location Rules</th><th>Fields</th><th>Local/DB</th></tr>";
            
            foreach ($all_groups as $group) {
                $location_count = isset($group['location']) ? count($group['location']) : 0;
                $fields_count = isset($group['fields']) ? count($group['fields']) : 0;
                $local_json = isset($group['local']) ? $group['local'] : 'db';
                
                echo "<tr>";
                echo "<td>{$group['title']}</td>";
                echo "<td>{$group['key']}</td>";
                echo "<td>{$location_count} rules</td>";
                echo "<td>{$fields_count} fields</td>";
                echo "<td>{$local_json}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='warning'><p>⚠️ No field groups found in WordPress!</p></div>";
        }
    } else {
        echo "<div class='error'><p>❌ ACF function 'acf_get_field_groups' not available</p></div>";
    }
    ?>
</div>

<!-- JSON Files -->
<div class="info">
    <h2>📁 JSON Field Group Files</h2>
    <?php
    $json_path = WP_CONTENT_DIR . '/plugins/happy-place/includes/fields/acf-json/';
    echo "<p><strong>JSON Path:</strong> {$json_path}</p>";
    
    if (is_dir($json_path)) {
        $json_files = glob($json_path . '*.json');
        echo "<p><strong>JSON Files Found:</strong> " . count($json_files) . "</p>";
        
        if (!empty($json_files)) {
            echo "<table>";
            echo "<tr><th>File</th><th>Size</th><th>Title</th><th>Key</th><th>Fields</th><th>Valid</th></tr>";
            
            foreach ($json_files as $file) {
                $filename = basename($file);
                $filesize = round(filesize($file) / 1024, 2) . ' KB';
                
                $json_content = file_get_contents($file);
                $json_data = json_decode($json_content, true);
                
                if ($json_data && isset($json_data['key'], $json_data['title'])) {
                    $fields_count = isset($json_data['fields']) ? count($json_data['fields']) : 0;
                    echo "<tr>";
                    echo "<td>{$filename}</td>";
                    echo "<td>{$filesize}</td>";
                    echo "<td>{$json_data['title']}</td>";
                    echo "<td>{$json_data['key']}</td>";
                    echo "<td>{$fields_count} fields</td>";
                    echo "<td>✅ Valid</td>";
                    echo "</tr>";
                } else {
                    echo "<tr>";
                    echo "<td>{$filename}</td>";
                    echo "<td>{$filesize}</td>";
                    echo "<td colspan='3'>❌ Invalid JSON</td>";
                    echo "<td>❌ Invalid</td>";
                    echo "</tr>";
                }
            }
            echo "</table>";
        }
    } else {
        echo "<div class='error'><p>❌ JSON directory not found!</p></div>";
    }
    ?>
</div>

<!-- Happy Place ACF Manager -->
<div class="info">
    <h2>🎯 Happy Place ACF Manager</h2>
    <?php
    if (class_exists('HappyPlace\\Core\\ACF_Manager')) {
        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
        echo "<p>✅ <strong>ACF Manager class found and instantiated</strong></p>";
        echo "<p><strong>ACF Available:</strong> " . ($acf_manager->is_acf_available() ? '✅ YES' : '❌ NO') . "</p>";
        
        $loaded_groups = $acf_manager->get_field_groups();
        echo "<p><strong>Field Groups Loaded by Manager:</strong> " . count($loaded_groups) . "</p>";
        
        if (!empty($loaded_groups)) {
            echo "<h3>📋 Manager Field Groups:</h3>";
            echo "<table>";
            echo "<tr><th>Title</th><th>Key</th><th>Fields</th></tr>";
            
            foreach ($loaded_groups as $key => $group) {
                $fields_count = isset($group['fields']) ? count($group['fields']) : 0;
                echo "<tr>";
                echo "<td>{$group['title']}</td>";
                echo "<td>{$key}</td>";
                echo "<td>{$fields_count} fields</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='warning'><p>⚠️ No field groups loaded by ACF Manager!</p></div>";
        }
    } else {
        echo "<div class='error'><p>❌ <strong>Happy Place ACF Manager class not found!</strong></p></div>";
    }
    ?>
</div>

<!-- Debug Log Excerpt -->
<div class="info">
    <h2>📝 Recent Debug Log (ACF Related)</h2>
    <pre><?php
    $log_file = WP_CONTENT_DIR . '/debug.log';
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        $lines = explode("\n", $log_content);
        $recent_lines = array_slice($lines, -100); // Get last 100 lines
        
        $acf_lines = array_filter($recent_lines, function($line) {
            return stripos($line, 'acf') !== false || stripos($line, 'field_group') !== false;
        });
        
        if (!empty($acf_lines)) {
            echo implode("\n", array_slice($acf_lines, -20)); // Show last 20 ACF-related lines
        } else {
            echo "No recent ACF-related log entries found.";
        }
    } else {
        echo "Debug log file not found.";
    }
    ?></pre>
</div>

<p><a href="<?php echo admin_url(); ?>" class="button">← Back to WordPress Admin</a></p>

</body>
</html>