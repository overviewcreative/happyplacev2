<?php
/**
 * Database migration for FollowUp Boss integration
 * Adds necessary fields to wp_hp_leads table
 */

require_once 'wp-load.php';

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

global $wpdb;
$table_name = $wpdb->prefix . 'hp_leads';

echo "<h1>FollowUp Boss Database Migration</h1>\n";

// Check if table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;

if (!$table_exists) {
    echo "<p>❌ Error: hp_leads table does not exist. Please ensure the unified lead system is properly installed.</p>\n";
    exit;
}

echo "<p>✅ hp_leads table found</p>\n";

// Check existing columns
$columns = $wpdb->get_col("DESC {$table_name}", 0);
echo "<h2>Current table columns:</h2>\n";
echo "<ul>\n";
foreach ($columns as $column) {
    echo "<li>" . esc_html($column) . "</li>\n";
}
echo "</ul>\n";

// Define FollowUp Boss columns to add
$fub_columns = [
    'fub_contact_id' => "VARCHAR(50) NULL COMMENT 'FollowUp Boss contact ID'",
    'fub_sync_status' => "VARCHAR(20) DEFAULT 'pending' COMMENT 'Sync status: pending, synced, failed, failed_permanent'",
    'fub_last_sync' => "DATETIME NULL COMMENT 'Last sync attempt timestamp'",
    'fub_error_message' => "TEXT NULL COMMENT 'Error message from last failed sync'"
];

echo "<h2>Adding FollowUp Boss Integration Fields</h2>\n";

$added_columns = [];
$errors = [];

foreach ($fub_columns as $column_name => $column_definition) {
    // Check if column already exists
    if (in_array($column_name, $columns)) {
        echo "<p>⚠️ Column '{$column_name}' already exists, skipping</p>\n";
        continue;
    }
    
    // Add the column
    $sql = "ALTER TABLE {$table_name} ADD COLUMN {$column_name} {$column_definition}";
    
    echo "<p>Adding column: <code>{$column_name}</code>... ";
    
    $result = $wpdb->query($sql);
    
    if ($result !== false) {
        echo "✅ Success</p>\n";
        $added_columns[] = $column_name;
    } else {
        $error = $wpdb->last_error;
        echo "❌ Failed: {$error}</p>\n";
        $errors[] = "Column '{$column_name}': {$error}";
    }
}

// Add indexes for better performance
echo "<h2>Adding Database Indexes</h2>\n";

$indexes = [
    'idx_fub_contact' => "fub_contact_id",
    'idx_fub_sync' => "fub_sync_status",
    'idx_fub_last_sync' => "fub_last_sync"
];

foreach ($indexes as $index_name => $index_column) {
    // Check if index already exists
    $existing_indexes = $wpdb->get_results("SHOW INDEX FROM {$table_name} WHERE Key_name = '{$index_name}'");
    
    if (!empty($existing_indexes)) {
        echo "<p>⚠️ Index '{$index_name}' already exists, skipping</p>\n";
        continue;
    }
    
    $sql = "ALTER TABLE {$table_name} ADD INDEX {$index_name} ({$index_column})";
    
    echo "<p>Adding index: <code>{$index_name}</code>... ";
    
    $result = $wpdb->query($sql);
    
    if ($result !== false) {
        echo "✅ Success</p>\n";
    } else {
        $error = $wpdb->last_error;
        echo "❌ Failed: {$error}</p>\n";
        $errors[] = "Index '{$index_name}': {$error}";
    }
}

// Show final status
echo "<h2>Migration Summary</h2>\n";

if (!empty($added_columns)) {
    echo "<p>✅ Successfully added columns:</p>\n";
    echo "<ul>\n";
    foreach ($added_columns as $column) {
        echo "<li>{$column}</li>\n";
    }
    echo "</ul>\n";
}

if (!empty($errors)) {
    echo "<p>❌ Errors encountered:</p>\n";
    echo "<ul>\n";
    foreach ($errors as $error) {
        echo "<li>{$error}</li>\n";
    }
    echo "</ul>\n";
} else {
    echo "<p>✅ Migration completed successfully!</p>\n";
}

// Show updated table structure
$updated_columns = $wpdb->get_col("DESC {$table_name}", 0);
echo "<h2>Updated table structure:</h2>\n";
echo "<ul>\n";
foreach ($updated_columns as $column) {
    $is_new = in_array($column, $added_columns);
    echo "<li" . ($is_new ? " style='color: green; font-weight: bold;'" : "") . ">" . esc_html($column) . ($is_new ? " (NEW)" : "") . "</li>\n";
}
echo "</ul>\n";

// Test query to verify structure
echo "<h2>Verification</h2>\n";
$test_query = "SELECT COUNT(*) as total_leads, 
                      COUNT(fub_contact_id) as synced_leads,
                      COUNT(*) - COUNT(fub_contact_id) as pending_sync
               FROM {$table_name}";

$test_result = $wpdb->get_row($test_query);

if ($test_result) {
    echo "<p>✅ Database structure verified:</p>\n";
    echo "<ul>\n";
    echo "<li>Total leads: " . $test_result->total_leads . "</li>\n";
    echo "<li>Synced to FUB: " . $test_result->synced_leads . "</li>\n";
    echo "<li>Pending sync: " . $test_result->pending_sync . "</li>\n";
    echo "</ul>\n";
} else {
    echo "<p>❌ Could not verify database structure</p>\n";
}

echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Configure your FollowUp Boss API key in <a href='" . admin_url('admin.php?page=hp-integrations#followup-boss') . "'>Happy Place > Integrations > FollowUp Boss</a></li>\n";
echo "<li>Test the connection using the 'Test Connection' button</li>\n";
echo "<li>Enable auto-sync for new leads</li>\n";
echo "<li>Use the bulk sync feature to sync existing leads</li>\n";
echo "</ul>\n";

echo "<p><a href='" . admin_url('admin.php?page=hp-integrations') . "' class='button button-primary'>Go to Integrations Settings</a></p>\n";
?>
