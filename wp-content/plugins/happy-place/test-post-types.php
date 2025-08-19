<?php
/**
 * Quick test to check if post types are registered
 * Access via: /wp-content/plugins/happy-place/test-post-types.php
 */

// Load WordPress
require_once('../../../../wp-load.php');

echo "<h1>🔍 Happy Place Post Types Test</h1>";

// Check if our custom post types are registered
$custom_post_types = ['listing', 'agent', 'community', 'city', 'open_house', 'local_place', 'team', 'transaction'];

echo "<h2>📋 Registered Post Types</h2>";
foreach ($custom_post_types as $post_type) {
    $exists = post_type_exists($post_type);
    $status = $exists ? '✅ REGISTERED' : '❌ NOT REGISTERED';
    echo "<p><strong>{$post_type}:</strong> {$status}</p>";
    
    if ($exists) {
        $post_type_object = get_post_type_object($post_type);
        echo "<div style='margin-left: 20px; font-size: 12px; color: #666;'>";
        echo "Labels: " . $post_type_object->labels->name . " | ";
        echo "Public: " . ($post_type_object->public ? 'Yes' : 'No') . " | ";
        echo "Menu Position: " . ($post_type_object->menu_position ?? 'None');
        echo "</div>";
    }
}

// Check ACF
echo "<h2>🎯 ACF Integration</h2>";
echo "<p><strong>ACF Class Exists:</strong> " . (class_exists('ACF') ? '✅ YES' : '❌ NO') . "</p>";
echo "<p><strong>ACF Function Exists:</strong> " . (function_exists('acf') ? '✅ YES' : '❌ NO') . "</p>";

if (function_exists('acf_get_field_groups')) {
    $field_groups = acf_get_field_groups();
    echo "<p><strong>Total ACF Field Groups:</strong> " . count($field_groups) . "</p>";
    
    $happy_place_groups = array_filter($field_groups, function($group) {
        return strpos($group['key'], 'group_') === 0;
    });
    
    echo "<p><strong>Happy Place Field Groups:</strong> " . count($happy_place_groups) . "</p>";
    
    if (!empty($happy_place_groups)) {
        echo "<h3>📋 Our Field Groups:</h3>";
        foreach ($happy_place_groups as $group) {
            echo "<p>• {$group['title']} ({$group['key']})</p>";
        }
    }
} else {
    echo "<p>❌ ACF field group functions not available</p>";
}

// Check Plugin Class
echo "<h2>🔧 Plugin Classes</h2>";
$classes_to_check = [
    'HappyPlace\\Core\\Post_Types',
    'HappyPlace\\Core\\Taxonomies', 
    'HappyPlace\\Core\\ACF_Manager',
    'HappyPlace\\Admin\\Admin_Menu',
    'HappyPlace\\Integrations\\Airtable_Sync_Manager'
];

foreach ($classes_to_check as $class) {
    $exists = class_exists($class);
    $status = $exists ? '✅ LOADED' : '❌ NOT FOUND';
    echo "<p><strong>{$class}:</strong> {$status}</p>";
}

echo "<h2>📊 WordPress Info</h2>";
echo "<p><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Current User:</strong> " . (is_user_logged_in() ? wp_get_current_user()->user_login : 'Not logged in') . "</p>";