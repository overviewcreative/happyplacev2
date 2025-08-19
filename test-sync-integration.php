<?php
/**
 * Test Sync Integration
 * Simple test to verify that all new listing fields are properly handled by the Airtable sync
 */

// WordPress environment
require_once('wp-config.php');
require_once('wp-load.php');

if (!current_user_can('manage_options')) {
    die('Access denied');
}

echo "<h1>Happy Place Airtable Sync Integration Test</h1>\n";

// Test 1: Check if all classes are loaded
echo "<h2>Class Loading Test</h2>\n";

$classes_to_check = [
    'HappyPlace\\Core\\Listing_Automation',
    'HappyPlace\\Integrations\\Airtable_Sync_Manager',
    'HappyPlace\\Core\\Assets_Manager'
];

foreach ($classes_to_check as $class) {
    if (class_exists($class)) {
        echo "✅ {$class} - Loaded<br>\n";
    } else {
        echo "❌ {$class} - Not loaded<br>\n";
    }
}

// Test 2: Check ACF field groups
echo "<h2>ACF Field Groups Test</h2>\n";

$field_groups = [
    'group_listing_basic',
    'group_listing_features', 
    'group_listing_media'
];

foreach ($field_groups as $group) {
    $fields = acf_get_field_group($group);
    if ($fields) {
        echo "✅ {$group} - Found (" . count($fields['fields'] ?? []) . " fields)<br>\n";
    } else {
        echo "❌ {$group} - Not found<br>\n";
    }
}

// Test 3: Check if new listing fields exist
echo "<h2>New Listing Fields Test</h2>\n";

$new_fields = [
    'short_description',
    'full_description', 
    'full_bathrooms',
    'half_bathrooms',
    'lot_size_acres',
    'listing_date',
    'gallery_images'
];

foreach ($new_fields as $field_name) {
    $field = acf_get_field($field_name);
    if ($field) {
        echo "✅ {$field_name} - Found (Type: {$field['type']})<br>\n";
    } else {
        echo "❌ {$field_name} - Not found<br>\n";
    }
}

// Test 4: Test Listing Automation methods
echo "<h2>Listing Automation Test</h2>\n";

if (class_exists('HappyPlace\\Core\\Listing_Automation')) {
    $automation = \HappyPlace\Core\Listing_Automation::get_instance();
    
    // Test formatted bathrooms method
    if (method_exists($automation, 'get_formatted_bathrooms')) {
        echo "✅ get_formatted_bathrooms method - Available<br>\n";
    } else {
        echo "❌ get_formatted_bathrooms method - Not available<br>\n";
    }
    
    // Test address building method  
    if (method_exists($automation, 'build_address_from_components')) {
        echo "✅ build_address_from_components method - Available<br>\n";
    } else {
        echo "❌ build_address_from_components method - Not available<br>\n";
    }
} else {
    echo "❌ Listing_Automation class not available<br>\n";
}

// Test 5: Check Airtable sync field mapping
echo "<h2>Airtable Sync Field Mapping Test</h2>\n";

if (class_exists('HappyPlace\\Integrations\\Airtable_Sync_Manager')) {
    $sync_manager = \HappyPlace\Integrations\Airtable_Sync_Manager::get_instance();
    
    // Check if field mappers are registered
    $reflection = new ReflectionClass($sync_manager);
    if ($reflection->hasProperty('field_mappers')) {
        echo "✅ Field mappers property - Available<br>\n";
    } else {
        echo "❌ Field mappers property - Not available<br>\n";
    }
    
    // Check if listing mapper methods exist
    if (method_exists($sync_manager, 'get_listing_field_mapper')) {
        echo "✅ get_listing_field_mapper method - Available<br>\n";
    } else {
        echo "❌ get_listing_field_mapper method - Not available<br>\n";
    }
} else {
    echo "❌ Airtable_Sync_Manager class not available<br>\n";
}

// Test 6: Check taxonomy terms
echo "<h2>Taxonomy Terms Test</h2>\n";

$taxonomies_to_check = [
    'property_status' => ['active', 'pending', 'sold', 'coming_soon'],
    'property_type' => ['single_family', 'condo', 'townhouse']
];

foreach ($taxonomies_to_check as $taxonomy => $expected_terms) {
    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
    if (!is_wp_error($terms) && !empty($terms)) {
        echo "✅ {$taxonomy} - Found " . count($terms) . " terms<br>\n";
        foreach ($expected_terms as $expected_term) {
            $term_exists = term_exists($expected_term, $taxonomy);
            if ($term_exists) {
                echo "&nbsp;&nbsp;✅ {$expected_term}<br>\n";
            } else {
                echo "&nbsp;&nbsp;❌ {$expected_term}<br>\n";
            }
        }
    } else {
        echo "❌ {$taxonomy} - No terms found<br>\n";
    }
}

// Test 7: Check asset loading
echo "<h2>Asset Loading Test</h2>\n";

$asset_files = [
    'assets/js/listing-automation.js',
    'assets/css/listing-automation.css'
];

foreach ($asset_files as $asset) {
    $file_path = WP_PLUGIN_DIR . '/happy-place/' . $asset;
    if (file_exists($file_path)) {
        echo "✅ {$asset} - Found<br>\n";
    } else {
        echo "❌ {$asset} - Not found<br>\n";
    }
}

echo "<h2>Test Complete</h2>\n";
echo "<p>If you see mostly green checkmarks (✅), the integration is working correctly.</p>\n";
echo "<p>Any red X marks (❌) indicate areas that need attention.</p>\n";

?>