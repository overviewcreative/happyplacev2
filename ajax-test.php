<?php
/**
 * WORDPRESS AJAX ENDPOINT TEST
 * Tests if the plugin's AJAX handlers are working
 */

require_once 'wp-config.php';

echo "<h1>🎯 WORDPRESS AJAX ENDPOINT TEST</h1>";
echo "<p>Testing if the plugin's AJAX endpoints are accessible...</p>";

if (isset($_POST['test_ajax'])) {
    echo "<h2>🔥 TESTING AJAX ENDPOINTS...</h2>";
    
    // Create a nonce
    $nonce = wp_create_nonce('hpt_airtable_nonce');
    
    // Test the AJAX endpoint directly
    $ajax_url = admin_url('admin-ajax.php');
    
    $test_data = array(
        'action' => 'hpt_airtable_test_connection',
        'nonce' => $nonce
    );
    
    echo "<p><strong>AJAX URL:</strong> $ajax_url</p>";
    echo "<p><strong>Action:</strong> hpt_airtable_test_connection</p>";
    echo "<p><strong>Nonce:</strong> $nonce</p>";
    
    $response = wp_remote_post($ajax_url, array(
        'body' => $test_data,
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 15px;'>";
        echo "<h3 style='color: #f44336;'>❌ AJAX REQUEST FAILED</h3>";
        echo "<p>" . $response->get_error_message() . "</p>";
        echo "</div>";
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        echo "<div style='background: #e8f5e8; border: 1px solid #4caf50; padding: 15px;'>";
        echo "<h3 style='color: #4caf50;'>✅ AJAX REQUEST SUCCESSFUL</h3>";
        echo "<p><strong>Status:</strong> $status_code</p>";
        echo "<p><strong>Response:</strong></p>";
        echo "<pre style='background: #f9f9f9; padding: 10px; overflow-x: auto;'>" . htmlspecialchars($body) . "</pre>";
        
        // Try to decode JSON
        $json_data = json_decode($body, true);
        if ($json_data) {
            echo "<p><strong>JSON Response:</strong></p>";
            echo "<pre style='background: #f0f8ff; padding: 10px;'>" . htmlspecialchars(json_encode($json_data, JSON_PRETTY_PRINT)) . "</pre>";
            
            if (isset($json_data['success'])) {
                if ($json_data['success']) {
                    echo "<p style='color: green; font-weight: bold;'>🎉 AJAX ENDPOINT IS WORKING!</p>";
                } else {
                    echo "<p style='color: orange; font-weight: bold;'>⚠️ AJAX endpoint works but returned error: " . ($json_data['data'] ?? 'Unknown error') . "</p>";
                }
            }
        }
        echo "</div>";
    }
    
    // Also test if the class exists and method is callable
    echo "<h3>🔍 Class and Method Check</h3>";
    
    if (class_exists('HappyPlace\\Integrations\\Airtable_Sync_Manager')) {
        echo "<p>✅ Airtable_Sync_Manager class exists</p>";
        
        $manager = \HappyPlace\Integrations\Airtable_Sync_Manager::get_instance();
        
        if (method_exists($manager, 'ajax_test_connection')) {
            echo "<p>✅ ajax_test_connection method exists</p>";
        } else {
            echo "<p>❌ ajax_test_connection method missing</p>";
        }
        
        // Check if hooks are registered
        global $wp_filter;
        if (isset($wp_filter['wp_ajax_hpt_airtable_test_connection'])) {
            echo "<p>✅ wp_ajax_hpt_airtable_test_connection hook registered</p>";
        } else {
            echo "<p>❌ wp_ajax_hpt_airtable_test_connection hook NOT registered</p>";
        }
        
    } else {
        echo "<p>❌ Airtable_Sync_Manager class does not exist</p>";
    }
}

?>

<form method="post" style="margin: 20px 0;">
    <button type="submit" name="test_ajax" style="background: blue; color: white; padding: 15px 30px; font-size: 16px; border: none; cursor: pointer; border-radius: 5px;">
        🎯 TEST WORDPRESS AJAX ENDPOINTS
    </button>
</form>

<h2>🧪 JavaScript Console Test</h2>
<p>Copy this code into your browser console (F12) on the admin page:</p>
<textarea style="width: 100%; height: 150px; font-family: monospace; font-size: 12px;" readonly>
// Test AJAX call manually
jQuery.ajax({
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    type: 'POST',
    data: {
        action: 'hpt_airtable_test_connection',
        nonce: '<?php echo wp_create_nonce('hpt_airtable_nonce'); ?>'
    },
    success: function(response) {
        console.log('✅ AJAX Success:', response);
        alert('AJAX works! Response: ' + JSON.stringify(response));
    },
    error: function(xhr, status, error) {
        console.log('❌ AJAX Error:', error);
        alert('AJAX failed: ' + error);
    }
});
</textarea>

<h2>🚨 The Problem Is Likely:</h2>
<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;">
    <ol>
        <li><strong>JavaScript not loading</strong> on the admin page</li>
        <li><strong>AJAX endpoints not registered</strong> properly</li>
        <li><strong>Wrong nonce/security check</strong> failing</li>
        <li><strong>Hook name mismatch</strong> in the AJAX action</li>
    </ol>
    
    <p><strong>Run this test to see which one it is!</strong></p>
</div>

<hr>
<p><strong>🕐 Generated:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
