<?php
require_once 'wp-config.php';
require_once 'wp-load.php';

echo "<h1>🔍 Direct AJAX Debug Test</h1>";

// Enable WordPress debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>1. Direct Function Test</h2>";

// Test if bridge functions can be loaded directly
$bridge_dir = get_template_directory() . '/includes/bridge/';
$bridge_files = [
    'listing-bridge.php',
    'agent-bridge.php',
    'city-bridge.php',
    'community-bridge.php',
    'gallery-bridge.php'
];

foreach ($bridge_files as $file) {
    $path = $bridge_dir . $file;
    if (file_exists($path)) {
        echo "✅ Loading {$file}...<br>";
        try {
            require_once $path;
            echo "&nbsp;&nbsp;&nbsp;→ Successfully loaded<br>";
        } catch (Exception $e) {
            echo "&nbsp;&nbsp;&nbsp;→ ❌ Error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ {$file} not found<br>";
    }
}

echo "<h2>2. Test Bridge Functions</h2>";

// Test if a bridge function works
if (function_exists('hpt_get_listing_price')) {
    echo "✅ hpt_get_listing_price function exists<br>";
    
    // Get a listing to test with
    $test_listing = get_posts([
        'post_type' => 'listing',
        'post_status' => 'publish',
        'numberposts' => 1
    ]);
    
    if (!empty($test_listing)) {
        $listing_id = $test_listing[0]->ID;
        echo "Testing with listing ID: {$listing_id}<br>";
        
        try {
            $price = hpt_get_listing_price($listing_id);
            echo "&nbsp;&nbsp;&nbsp;→ Price: " . ($price ?: 'NULL') . "<br>";
        } catch (Exception $e) {
            echo "&nbsp;&nbsp;&nbsp;→ ❌ Price function error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ No listings found to test with<br>";
    }
} else {
    echo "❌ hpt_get_listing_price function does not exist<br>";
}

echo "<h2>3. Simulate AJAX Call</h2>";

// Simulate the exact AJAX call
$_POST['action'] = 'hph_load_listings';
$_POST['nonce'] = wp_create_nonce('hph_listings_nonce');
$_POST['filters'] = ['status' => '', 'search' => ''];

echo "Simulating AJAX call with POST data:<br>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

// Try to call the function directly
if (function_exists('hpt_handle_archive_ajax')) {
    echo "✅ hpt_handle_archive_ajax function exists<br>";
    echo "Attempting direct call...<br>";
    
    try {
        ob_start();
        hpt_handle_archive_ajax();
        $output = ob_get_clean();
        echo "<strong>Function output:</strong><br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    } catch (Exception $e) {
        echo "❌ Function error: " . $e->getMessage() . "<br>";
        echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
    }
} else {
    echo "❌ hpt_handle_archive_ajax function does not exist<br>";
}

echo "<h2>4. Check WordPress AJAX Setup</h2>";

// Check if WordPress AJAX is set up correctly
echo "admin-ajax.php URL: " . admin_url('admin-ajax.php') . "<br>";
echo "wp_doing_ajax(): " . (wp_doing_ajax() ? 'true' : 'false') . "<br>";
echo "DOING_AJAX constant: " . (defined('DOING_AJAX') ? 'true' : 'false') . "<br>";

// Check global wp_filter
global $wp_filter;
$ajax_actions = ['wp_ajax_hph_load_listings', 'wp_ajax_nopriv_hph_load_listings'];
foreach ($ajax_actions as $action) {
    if (isset($wp_filter[$action])) {
        echo "✅ {$action} is registered<br>";
        $callbacks = $wp_filter[$action]->callbacks;
        foreach ($callbacks as $priority => $funcs) {
            foreach ($funcs as $func) {
                $name = is_array($func['function']) ? get_class($func['function'][0]) . '::' . $func['function'][1] : $func['function'];
                echo "&nbsp;&nbsp;&nbsp;→ Handler: {$name}<br>";
            }
        }
    } else {
        echo "❌ {$action} is NOT registered<br>";
    }
}
?>