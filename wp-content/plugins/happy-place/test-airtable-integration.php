<?php
/**
 * Airtable Integration Test
 * Comprehensive test script to verify Airtable integration functionality
 */

// Load WordPress
require_once '../../../wp-load.php';

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Access denied. Administrator privileges required.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Airtable Integration Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
        .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
        .status-item { padding: 10px; border-radius: 3px; text-align: center; }
    </style>
</head>
<body>
    <h1>🔗 Airtable Integration Test</h1>
    <p><strong>Test Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

    <?php
    // Test 1: Class Loading
    echo '<div class="test-section">';
    echo '<h2>📋 Test 1: Class Loading</h2>';
    
    $manager_exists = class_exists('HappyPlace\\Integrations\\Airtable_Sync_Manager');
    if ($manager_exists) {
        echo '<div class="success">✅ Airtable_Sync_Manager class loaded successfully</div>';
        
        try {
            $manager = \HappyPlace\Integrations\Airtable_Sync_Manager::get_instance();
            echo '<div class="success">✅ Manager instance created successfully</div>';
        } catch (Exception $e) {
            echo '<div class="error">❌ Error creating manager: ' . $e->getMessage() . '</div>';
        }
    } else {
        echo '<div class="error">❌ Airtable_Sync_Manager class not found</div>';
    }
    echo '</div>';

    // Test 2: Field Mappers
    echo '<div class="test-section">';
    echo '<h2>🗂️ Test 2: Field Mapper Classes</h2>';
    
    $mapper_classes = [
        'Listing_Field_Mapper',
        'Agent_Field_Mapper', 
        'Community_Field_Mapper',
        'Open_House_Field_Mapper'
    ];
    
    echo '<div class="status-grid">';
    foreach ($mapper_classes as $class_name) {
        $full_class_name = 'HappyPlace\\Integrations\\' . $class_name;
        $class_loaded = class_exists($full_class_name);
        $status_class = $class_loaded ? 'success' : 'error';
        
        echo '<div class="status-item ' . $status_class . '">';
        echo '<strong>' . str_replace('_', ' ', $class_name) . '</strong><br>';
        echo $class_loaded ? '✅ Loaded' : '❌ Missing';
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';

    // Test 3: Configuration
    echo '<div class="test-section">';
    echo '<h2>⚙️ Test 3: Configuration Status</h2>';
    
    $api_key = get_option('hpt_airtable_api_key', '');
    $base_id = get_option('hpt_airtable_base_id', '');
    
    if (!empty($api_key)) {
        $masked_key = substr($api_key, 0, 6) . str_repeat('*', max(0, strlen($api_key) - 6));
        echo '<div class="success">✅ API Key: ' . $masked_key . '</div>';
    } else {
        echo '<div class="warning">⚠️ API Key not configured</div>';
    }
    
    if (!empty($base_id)) {
        echo '<div class="success">✅ Base ID: ' . $base_id . '</div>';
    } else {
        echo '<div class="warning">⚠️ Base ID not configured</div>';
    }
    echo '</div>';

    // Configuration Guide
    echo '<div class="test-section info">';
    echo '<h2>🛠️ Setup Instructions</h2>';
    echo '<ol>';
    echo '<li>Go to <strong>WordPress Admin → Happy Place → Airtable Sync</strong></li>';
    echo '<li>Enter your Airtable API Key</li>';
    echo '<li>Enter your Airtable Base ID</li>';
    echo '<li>Test the connection</li>';
    echo '</ol>';
    echo '</div>';
    ?>

    <div class="test-section">
        <h2>🔄 Quick Actions</h2>
        <button onclick="location.reload()">🔄 Refresh Test</button>
        <button onclick="window.open('/wp-admin/admin.php?page=happy-place-airtable-sync', '_blank')">⚙️ Airtable Settings</button>
    </div>

</body>
</html>
