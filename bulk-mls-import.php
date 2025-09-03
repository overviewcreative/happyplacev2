<?php
/**
 * Bulk MLS Import Tool
 * Paste multiple MLS listings and create them all at once
 */

require_once 'wp-load.php';

// Check if user is logged in and has permissions
if (!is_user_logged_in() || !current_user_can('edit_posts')) {
    wp_die('You do not have permission to access this page.');
}

// Function to parse MLS data from raw text
function parse_mls_listing($raw_text) {
    $lines = explode("\n", $raw_text);
    $listing = [];
    
    // Extract address from first line (usually contains address)
    $first_line = trim($lines[0]);
    if (preg_match('/^(.+?),\s*(.+?),\s*([A-Z]{2})\s*(\d{5})/', $first_line, $matches)) {
        $address_parts = explode(' ', trim($matches[1]));
        $listing['street_number'] = array_shift($address_parts);
        $listing['street_type'] = array_pop($address_parts);
        $listing['street_name'] = implode(' ', $address_parts);
        $listing['city'] = $matches[2];
        $listing['state'] = $matches[3];
        $listing['zip_code'] = $matches[4];
        $listing['title'] = trim($matches[1]);
    }
    
    $full_text = implode(' ', $lines);
    
    // Extract common patterns
    $patterns = [
        'listing_price' => '/\$([0-9,]+)/',
        'mls_number' => '/MLS #:\s*([A-Z0-9]+)/',
        'bedrooms' => '/Beds?:\s*(\d+)/',
        'bathrooms_full' => '/Baths?:\s*(\d+)/',
        'square_feet' => '/(\d{1,3}(?:,\d{3})*)\s*(?:\/\s*Estimated)?\s*(?:SQFT|sqft|sq\.?\s*ft\.?)/',
        'year_built' => '/Year Built:\s*(\d{4})/',
        'lot_size_acres' => '/(\d+(?:\.\d+)?)a\s*\//',
        'lot_size_sqft' => '/(\d+(?:\.\d+)?)[a-z]*\s*\/\s*(\d+)sf/',
        'property_status' => '/(Active|Pending|Sold|Under Contract)/',
        'property_type' => '/(Residential|Commercial|Land|Condo|Townhouse)/',
        'days_on_market' => '/DOM:\s*(\d+)/',
        'property_taxes' => '/Property Tax.*?\$([0-9,]+)/',
        'stories' => '/(?:Levels|Stories):\s*(\d+)/',
        'garage_spaces' => '/Total Parking Spaces\s*(\d+)/',
        'builder' => '/Builder Name:\s*([^\n\r]+)/',
        'property_style' => '/Style:\s*([^\n\r]+)/',
        'condition' => '/Property Condition:\s*([^\n\r]+)/',
        'heating_system' => '/Heating:\s*([^\n\r]+)/',
        'cooling_system' => '/Cooling.*?:\s*([^\n\r]+)/',
        'water_source' => '/Water Source:\s*([^\n\r]+)/',
        'sewer_system' => '/Sewer:\s*([^\n\r]+)/',
        'school_district' => '/School District:\s*([^\n\r]+)/',
        'zoning' => '/Zoning:\s*([A-Z0-9\-]+)/',
        'subdivision' => '/Subdiv.*?:\s*([^\n\r]+)/',
        'tax_id' => '/Tax ID #:\s*([\d\-\.]+)/',
    ];
    
    foreach ($patterns as $field => $pattern) {
        if (preg_match($pattern, $full_text, $matches)) {
            $value = trim($matches[1]);
            
            // Clean up specific fields
            if ($field === 'listing_price') {
                $value = intval(str_replace(',', '', $value));
            } elseif (in_array($field, ['bedrooms', 'bathrooms_full', 'square_feet', 'year_built', 'days_on_market', 'property_taxes', 'stories', 'garage_spaces'])) {
                $value = intval(str_replace(',', '', $value));
            } elseif ($field === 'lot_size_sqft' && isset($matches[2])) {
                $value = intval(str_replace(',', '', $matches[2]));
            } elseif ($field === 'lot_size_acres') {
                $value = floatval($value);
            } elseif ($field === 'property_status') {
                // Convert to taxonomy term slug
                $status_map = [
                    'Active' => 'active',
                    'Pending' => 'pending', 
                    'Sold' => 'sold',
                    'Under Contract' => 'pending'
                ];
                $value = $status_map[$value] ?? strtolower($value);
            } elseif ($field === 'property_type') {
                // Convert to taxonomy term slug
                $type_map = [
                    'Residential' => 'single-family',
                    'Commercial' => 'commercial',
                    'Land' => 'land',
                    'Condo' => 'condo',
                    'Townhouse' => 'townhouse'
                ];
                $value = $type_map[$value] ?? strtolower(str_replace(' ', '-', $value));
            } elseif ($field === 'property_style') {
                // Convert to field value
                $style_map = [
                    'Coastal' => 'coastal',
                    'Colonial' => 'colonial',
                    'Contemporary' => 'contemporary',
                    'Traditional' => 'traditional',
                    'Ranch' => 'ranch',
                    'Cape Cod' => 'cape_cod',
                    'Victorian' => 'victorian',
                    'Craftsman' => 'craftsman',
                    'Modern' => 'modern',
                    'Tudor' => 'tudor',
                    'Mediterranean' => 'mediterranean',
                    'Farmhouse' => 'farmhouse',
                    'Two Story' => 'two_story'
                ];
                $value = $style_map[$value] ?? strtolower(str_replace(' ', '_', $value));
            } elseif ($field === 'condition') {
                // Convert to field value
                $condition_map = [
                    'Excellent' => 'excellent',
                    'Very Good' => 'very_good',
                    'Good' => 'good',
                    'Fair' => 'fair',
                    'Needs Work' => 'needs_work'
                ];
                $value = $condition_map[$value] ?? strtolower(str_replace(' ', '_', $value));
            } elseif ($field === 'water_source') {
                $value = strpos(strtolower($value), 'well') !== false ? 'well' : 'city';
            } elseif ($field === 'sewer_system') {
                if (strpos(strtolower($value), 'septic') !== false) {
                    $value = 'septic';
                } elseif (strpos(strtolower($value), 'lpp') !== false) {
                    $value = 'lpp';
                } else {
                    $value = 'city_sewer';
                }
            }
            
            $listing[$field] = $value;
        }
    }
    
    // Extract description from "Public:" or "Remarks" section
    if (preg_match('/Public:\s*(.+?)(?=\n\n|\nDirections|\nListing Details|$)/s', $full_text, $matches)) {
        $listing['description'] = trim($matches[1]);
    }
    
    // Set defaults
    $listing['post_type'] = 'listing';
    $listing['status'] = 'publish';
    $listing['is_featured'] = false;
    
    // Clean up title if not set
    if (!isset($listing['title']) && isset($listing['street_number'], $listing['street_name'])) {
        $listing['title'] = $listing['street_number'] . ' ' . $listing['street_name'];
        if (isset($listing['street_type'])) {
            $listing['title'] .= ' ' . $listing['street_type'];
        }
    }
    
    return $listing;
}

// Include the creation function from the other file
function create_listing_from_parsed_data($data) {
    // Create the post
    $post_data = [
        'post_title' => $data['title'] ?? 'New Listing',
        'post_content' => $data['description'] ?? '',
        'post_status' => 'publish',
        'post_type' => 'listing',
    ];
    
    // Insert the post
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        return false;
    }
    
    // Update ACF fields - only set fields that exist in the data and match our schema
    $acf_field_mapping = [
        'street_number', 'street_name', 'street_type', 'city', 'state', 'zip_code',
        'listing_price', 'mls_number', 'is_featured',
        'bedrooms', 'bathrooms_full', 'bathrooms_half', 'square_feet', 
        'lot_size_acres', 'lot_size_sqft', 'year_built', 'days_on_market',
        'property_taxes', 'hoa_fees', 'tax_id', 'price_per_sqft',
        'stories', 'garage_spaces', 'listing_date', 'condition', 'property_style',
        'builder', 'roof_type', 'foundation_type', 'heating_system', 'heating_fuel',
        'cooling_system', 'cooling_fuel', 'water_source', 'sewer_system',
        'electric_service', 'hot_water', 'school_district', 'zoning', 
        'subdivision', 'flood_zone', 'county'
    ];
    
    foreach ($acf_field_mapping as $field) {
        if (isset($data[$field]) && $data[$field] !== '') {
            update_field($field, $data[$field], $post_id);
        }
    }
    
    // Set taxonomies
    if (isset($data['property_status'])) {
        wp_set_object_terms($post_id, $data['property_status'], 'property_status');
    }
    
    if (isset($data['property_type'])) {
        wp_set_object_terms($post_id, $data['property_type'], 'property_type');
    }
    
    return $post_id;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Bulk MLS Import Tool</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 40px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; height: 400px; }
        .btn { background: #0073aa; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #005a87; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .results { margin-top: 30px; }
        .listing-result { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007cba; }
        .two-column { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .preview { background: #f8f9fa; padding: 15px; border-radius: 4px; max-height: 400px; overflow-y: auto; }
        .parsed-field { margin: 5px 0; }
        .parsed-field strong { color: #007cba; }
    </style>
</head>
<body>
    <h1>🏠 Bulk MLS Import Tool</h1>
    <p>Paste your MLS listings below (one listing per section) and they'll be automatically parsed and created.</p>
    
    <?php
    if ($_POST && isset($_POST['bulk_import'])) {
        $raw_data = $_POST['mls_data'];
        
        // Split by multiple empty lines (separates listings)
        $listings = preg_split('/\n\s*\n\s*\n/', $raw_data);
        $results = [];
        $created_count = 0;
        $failed_count = 0;
        
        foreach ($listings as $index => $listing_text) {
            $listing_text = trim($listing_text);
            if (empty($listing_text)) continue;
            
            $parsed_data = parse_mls_listing($listing_text);
            
            if (!empty($parsed_data['title'])) {
                $post_id = create_listing_from_parsed_data($parsed_data);
                
                if ($post_id) {
                    $results[] = [
                        'status' => 'success',
                        'title' => $parsed_data['title'],
                        'post_id' => $post_id,
                        'url' => get_permalink($post_id),
                        'edit_url' => admin_url('post.php?post=' . $post_id . '&action=edit')
                    ];
                    $created_count++;
                } else {
                    $results[] = [
                        'status' => 'error',
                        'title' => $parsed_data['title'] ?? 'Unknown',
                        'message' => 'Failed to create listing'
                    ];
                    $failed_count++;
                }
            } else {
                $results[] = [
                    'status' => 'warning',
                    'title' => 'Listing #' . ($index + 1),
                    'message' => 'Could not parse address from listing data'
                ];
                $failed_count++;
            }
        }
        
        echo '<div class="success">';
        echo '<h3>Import Complete!</h3>';
        echo '<strong>Created:</strong> ' . $created_count . ' listings<br>';
        if ($failed_count > 0) {
            echo '<strong>Failed:</strong> ' . $failed_count . ' listings<br>';
        }
        echo '</div>';
        
        echo '<div class="results">';
        echo '<h3>Results:</h3>';
        foreach ($results as $result) {
            $class = $result['status'] === 'success' ? 'success' : ($result['status'] === 'error' ? 'error' : 'warning');
            echo '<div class="' . $class . '">';
            echo '<strong>' . htmlspecialchars($result['title']) . '</strong><br>';
            
            if ($result['status'] === 'success') {
                echo 'ID: ' . $result['post_id'] . ' | ';
                echo '<a href="' . $result['url'] . '" target="_blank">View</a> | ';
                echo '<a href="' . $result['edit_url'] . '" target="_blank">Edit</a>';
            } else {
                echo $result['message'];
            }
            echo '</div>';
        }
        echo '</div>';
    }
    ?>
    
    <div class="two-column">
        <div>
            <form method="post">
                <div class="form-group">
                    <label>Paste MLS Listings (separate multiple listings with blank lines):</label>
                    <textarea name="mls_data" placeholder="Paste your MLS listings here...

Example:
21222 Cool Spring Rd, Milton, DE 19968		Active	Residential	$869,900
MLS #: DESU2095796
Beds: 4
Baths: 3
Above Grade Fin SQFT: 2,910
Year Built: 2022

[Separate multiple listings with blank lines like this]

123 Main Street, Dover, DE 19901		Active	Residential	$450,000
MLS #: DESU1234567
Beds: 3
Baths: 2
Above Grade Fin SQFT: 1,800
Year Built: 2015"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="bulk_import" class="btn">📥 Import Listings</button>
                </div>
            </form>
        </div>
        
        <div>
            <h3>📋 How It Works</h3>
            <div class="preview">
                <p><strong>Automatic Parsing:</strong></p>
                <ul>
                    <li>✅ Address (Street, City, State, Zip)</li>
                    <li>✅ Price ($869,900)</li>
                    <li>✅ MLS Number (DESU2095796)</li>
                    <li>✅ Bedrooms & Bathrooms</li>
                    <li>✅ Square Footage</li>
                    <li>✅ Lot Size (Acres & Sq Ft)</li>
                    <li>✅ Year Built</li>
                    <li>✅ Property Status (Active/Pending/Sold) → Taxonomy</li>
                    <li>✅ Property Type (Residential/Commercial) → Taxonomy</li>
                    <li>✅ Days on Market</li>
                    <li>✅ Property Taxes</li>
                    <li>✅ Property Description</li>
                </ul>
                
                <p><strong>Instructions:</strong></p>
                <ol>
                    <li>Copy MLS listing data from your source</li>
                    <li>Paste into the text area</li>
                    <li>Separate multiple listings with blank lines</li>
                    <li>Click "Import Listings"</li>
                    <li>Review results and edit in WordPress if needed</li>
                </ol>
                
                <p><strong>Supported Formats:</strong></p>
                <ul>
                    <li>BRIGHT MLS exports</li>
                    <li>Standard MLS printouts</li>
                    <li>Copy/paste from MLS websites</li>
                    <li>Custom formatted listing data</li>
                </ul>
            </div>
        </div>
    </div>
    
    <hr>
    <p><em>💡 Tip: For best results, include as much detail as possible. The tool automatically extracts common fields like price, MLS number, beds/baths, square footage, and more!</em></p>
</body>
</html>
