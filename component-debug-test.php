<?php
/**
 * Component Debug Test - Check what's happening with hph_component
 */

// Load WordPress
require_once 'wp-load.php';

echo "=== Component Debug Test ===\n";

// Test if function exists
if (function_exists('hph_component')) {
    echo "✓ hph_component function exists\n";
    
    // Test if component is registered  
    $component_loader_class = new ReflectionClass('HPH_Component_Loader');
    $components_property = $component_loader_class->getProperty('components');
    $components_property->setAccessible(true);
    $components = $components_property->getValue();
    
    if (isset($components['listing-hero'])) {
        echo "✓ listing-hero component is registered\n";
        echo "  Path: " . $components['listing-hero']['path'] . "\n";
    } else {
        echo "✗ listing-hero component NOT registered\n";
        echo "Available components: " . implode(', ', array_keys($components)) . "\n";
    }
    
    // Test component loading
    echo "\n--- Testing component output ---\n";
    ob_start();
    $result = hph_component('listing-hero', [
        'listing_id' => 3524,
        'show_gallery' => true,
        'show_price' => true,
        'show_stats' => true
    ], false);
    $output = ob_get_clean();
    
    echo "Component returned: " . ($result ? 'TRUE' : 'FALSE') . "\n";
    echo "Output length: " . strlen($output) . " characters\n";
    
    if (strlen($output) > 0) {
        echo "✓ Component produced output\n";
        echo "First 200 chars: " . substr($output, 0, 200) . "...\n";
    } else {
        echo "✗ Component produced NO output\n";
    }
    
} else {
    echo "✗ hph_component function does NOT exist\n";
}

echo "\n=== Debug Test Complete ===\n";
?>