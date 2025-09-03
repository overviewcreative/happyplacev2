<!DOCTYPE html>
<html>
<head>
    <title>Complete MLS Parser - Auto WordPress Listing Creation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .processing { background: #f0f0f0; padding: 10px; margin: 10px 0; }
        ul { list-style-type: none; }
        li { margin: 5px 0; }
    </style>
</head>
<body>

<?php
/*
 * Complete MLS Parser - Direct WordPress Listing Creation
 * Reads MLS.md and creates WordPress listings automatically
 */

// WordPress environment
require_once('wp-config.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');

// Enhanced MLS parsing functions
function parseCompleteMLSFile($filePath) {
    $content = file_get_contents($filePath);
    if (!$content) {
        die("Could not read MLS file: $filePath");
    }
    
    // Split by listing headers - look for patterns like "26590 Mariners Rd" or "17170 Brittany Pl"
    $listings = preg_split('/(?=\s*\d+\s+[A-Za-z\s,]+(?:Rd|St|Ave|Dr|Ln|Ct|Pl|Way|Blvd|Pkwy|Cir),\s*[A-Za-z\s]+,\s*[A-Z]{2}\s+\d{5}.*?(?:Active|Pending|Sold|Under Contract))/m', $content);
    
    $parsedListings = [];
    
    foreach ($listings as $listingText) {
        if (strlen(trim($listingText)) < 200) continue; // Skip short sections
        
        $parsed = parseIndividualMLSListing(trim($listingText));
        if ($parsed && !empty($parsed['address'])) {
            $parsedListings[] = $parsed;
        }
    }
    
    return $parsedListings;
}

function parseIndividualMLSListing($text) {
    $listing = [];
    
    // Extract address and basic info from header
    if (preg_match('/(\d+.*?(?:Rd|St|Ave|Dr|Ln|Ct|Pl|Way|Blvd|Pkwy|Cir)[^,]*),\s*([A-Za-z\s]+),\s*([A-Z]{2})\s+(\d{5}).*?(Active|Pending|Sold|Under Contract|Active Under Contract).*?Residential.*?\$?([\d,]+)/s', $text, $matches)) {
        $listing['address'] = trim($matches[1]);
        $listing['city'] = trim($matches[2]);
        $listing['state'] = $matches[3];
        $listing['zip'] = $matches[4];
        $listing['status'] = $matches[5];
        $listing['price'] = str_replace(',', '', $matches[6]);
    }
    
    // Enhanced field extraction patterns
    $patterns = [
        'mls_number' => '/MLS #:\s*([A-Z0-9]+)/i',
        'beds' => '/Beds:\s*(\d+)/i',
        'baths' => '/Baths:\s*([\d\s\/]+)/i',
        'sqft' => '/Above Grade Fin SQFT:\s*([\d,]+)/i',
        'year_built' => '/Year Built:\s*(\d{4})/i',
        'style' => '/Style:\s*([^\n\r]+)/i',
        'lot_size' => '/Lot Acres.*?:\s*([\d\.]+)a/i',
        'garage' => '/Garage:\s*(Yes|No)/i',
        'basement' => '/Basement:\s*(Yes|No)/i',
        'central_air' => '/Central Air:\s*(Yes|No)/i',
        'waterfront' => '/Waterfront:\s*(Yes|No)/i',
        'hoa_fee' => '/HOA Fee:\s*\$?([\d,]+)\s*\/\s*([A-Za-z]+)/i',
        'taxes' => '/Tax Annual Amt.*?\$?([\d,]+)/i',
        'school_district' => '/School District:\s*([^\n\r]+)/i',
        'county' => '/County:\s*([^\n\r]+)/i',
        'subdivision' => '/Subdiv.*?:\s*([^\n\r]+)/i',
        'zoning' => '/Zoning:\s*([^\n\r]+)/i',
        'parking_spaces' => '/Total Parking Spaces\s*(\d+)/i',
        'heating' => '/Heating:\s*([^\n\r;]+)/i',
        'cooling' => '/Cooling.*?:\s*([^\n\r;]+)/i',
        'water_source' => '/Water Source:\s*([^\n\r;]+)/i',
        'sewer' => '/Sewer:\s*([^\n\r;]+)/i',
        'flooring' => '/Flooring Type:\s*([^\n\r]+)/i',
        'roof' => '/Roof:\s*([^\n\r]+)/i',
        'foundation' => '/Foundation Details:\s*([^\n\r]+)/i',
    ];
    
    foreach ($patterns as $field => $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $listing[$field] = trim($matches[1]);
        }
    }
    
    // Extract property description
    if (preg_match('/Public:\s*(.*?)(?=Directions|Listing Details|$)/s', $text, $matches)) {
        $listing['description'] = trim($matches[1]);
    }
    
    // Extract amenities
    if (preg_match('/Amenities:\s*([^\n\r]+)/i', $text, $matches)) {
        $listing['amenities'] = trim($matches[1]);
    }
    
    // Extract photo count
    if (preg_match('/View Photos\s*\((\d+)\)/i', $text, $matches)) {
        $listing['photo_count'] = $matches[1];
    }
    
    // Extract virtual tour
    $listing['virtual_tour'] = strpos($text, 'Virtual Tour') !== false ? 'Yes' : 'No';
    
    // Clean up bath format
    if (isset($listing['baths'])) {
        $listing['baths'] = preg_replace('/\s*\/\s*\d*/', '', $listing['baths']);
        $listing['baths'] = trim($listing['baths']);
    }
    
    return $listing;
}

function createWordPressListing($listing) {
    // Create post title
    $title = $listing['address'] . ', ' . $listing['city'] . ', ' . $listing['state'] . ' ' . $listing['zip'];
    
    // Create post content
    $content = '';
    if (!empty($listing['description'])) {
        $content = wpautop($listing['description']);
    }
    
    // Prepare post data
    $post_data = [
        'post_title' => $title,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'listing',
        'post_author' => 1,
    ];
    
    // Insert the post
    $post_id = wp_insert_post($post_data, true);
    
    if (is_wp_error($post_id)) {
        return ['success' => false, 'error' => $post_id->get_error_message()];
    }
    
    // Add ACF fields
    $acf_fields = [
        'listing_price' => isset($listing['price']) ? intval($listing['price']) : 0,
        'listing_address' => $listing['address'] ?? '',
        'listing_city' => $listing['city'] ?? '',
        'listing_state' => $listing['state'] ?? '',
        'listing_zip' => $listing['zip'] ?? '',
        'listing_bedrooms' => isset($listing['beds']) ? intval($listing['beds']) : 0,
        'listing_bathrooms' => $listing['baths'] ?? '',
        'listing_sqft' => isset($listing['sqft']) ? intval(str_replace(',', '', $listing['sqft'])) : 0,
        'listing_year_built' => isset($listing['year_built']) ? intval($listing['year_built']) : 0,
        'listing_style' => $listing['style'] ?? '',
        'listing_lot_size' => $listing['lot_size'] ?? '',
        'listing_garage' => $listing['garage'] ?? '',
        'listing_basement' => $listing['basement'] ?? '',
        'listing_central_air' => $listing['central_air'] ?? '',
        'listing_waterfront' => $listing['waterfront'] ?? '',
        'listing_hoa_fee' => $listing['hoa_fee'] ?? '',
        'listing_taxes' => isset($listing['taxes']) ? str_replace(',', '', $listing['taxes']) : '',
        'listing_school_district' => $listing['school_district'] ?? '',
        'listing_county' => $listing['county'] ?? '',
        'listing_subdivision' => $listing['subdivision'] ?? '',
        'listing_zoning' => $listing['zoning'] ?? '',
        'listing_parking_spaces' => isset($listing['parking_spaces']) ? intval($listing['parking_spaces']) : 0,
        'listing_heating' => $listing['heating'] ?? '',
        'listing_cooling' => $listing['cooling'] ?? '',
        'listing_water_source' => $listing['water_source'] ?? '',
        'listing_sewer' => $listing['sewer'] ?? '',
        'listing_flooring' => $listing['flooring'] ?? '',
        'listing_roof' => $listing['roof'] ?? '',
        'listing_foundation' => $listing['foundation'] ?? '',
        'listing_mls_number' => $listing['mls_number'] ?? '',
        'listing_amenities' => $listing['amenities'] ?? '',
        'listing_photo_count' => isset($listing['photo_count']) ? intval($listing['photo_count']) : 0,
        'listing_virtual_tour' => $listing['virtual_tour'] ?? 'No',
    ];
    
    // Save ACF fields
    foreach ($acf_fields as $field_name => $field_value) {
        update_field($field_name, $field_value, $post_id);
    }
    
    // Set listing status taxonomy
    if (isset($listing['status'])) {
        wp_set_post_terms($post_id, [$listing['status']], 'listing_status');
    }
    
    // Set location taxonomy
    if (isset($listing['city'])) {
        wp_set_post_terms($post_id, [$listing['city']], 'location');
    }
    
    // Set property type
    wp_set_post_terms($post_id, ['Residential'], 'property_type');
    
    return ['success' => true, 'post_id' => $post_id, 'title' => $title];
}

// Main execution
$mls_file = __DIR__ . '/wp-content/MLS.md';

echo "<h1>Complete MLS Parser - Creating WordPress Listings</h1>\n";
echo "<p class='info'>Processing MLS file: $mls_file</p>\n";

if (!file_exists($mls_file)) {
    die("<p class='error'>MLS file not found: $mls_file</p>");
}

// Parse all listings
echo "<h2>Parsing MLS Data...</h2>\n";
$listings = parseCompleteMLSFile($mls_file);

echo "<p class='info'>Found " . count($listings) . " listings to process.</p>\n";

// Show first few listings for verification
echo "<div class='processing'>";
echo "<h3>Sample Parsed Listings (first 3):</h3>";
for ($i = 0; $i < min(3, count($listings)); $i++) {
    echo "<p><strong>" . ($i + 1) . ".</strong> " . esc_html($listings[$i]['address'] ?? 'Unknown address') . " - $" . number_format($listings[$i]['price'] ?? 0) . "</p>";
}
echo "</div>";

// Process each listing
$created_count = 0;
$errors = [];

echo "<h2>Creating WordPress Listings...</h2>\n";
echo "<ul>\n";

foreach ($listings as $index => $listing) {
    $result = createWordPressListing($listing);
    
    if ($result['success']) {
        $created_count++;
        echo "<li class='success'>✓ Created: " . esc_html($result['title']) . " (Post ID: " . $result['post_id'] . ")</li>\n";
    } else {
        $errors[] = "Failed to create listing: " . ($listing['address'] ?? 'Unknown address') . " - " . $result['error'];
        echo "<li class='error'>✗ Failed: " . esc_html($listing['address'] ?? 'Unknown address') . " - " . esc_html($result['error']) . "</li>\n";
    }
    
    // Flush output for real-time display
    if (ob_get_level()) ob_flush();
    flush();
}

echo "</ul>\n";

echo "<h2>Summary</h2>\n";
echo "<p><strong>Total listings processed:</strong> " . count($listings) . "</p>\n";
echo "<p><strong>Successfully created:</strong> <span class='success'>$created_count</span></p>\n";
echo "<p><strong>Errors:</strong> <span class='error'>" . count($errors) . "</span></p>\n";

if (!empty($errors)) {
    echo "<h3>Error Details:</h3>\n";
    echo "<ul>\n";
    foreach ($errors as $error) {
        echo "<li class='error'>" . esc_html($error) . "</li>\n";
    }
    echo "</ul>\n";
}

echo "<p><strong>All done!</strong> Check your WordPress admin to see the created listings.</p>\n";
?>

</body>
</html>
