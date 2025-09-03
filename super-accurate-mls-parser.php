<?php
/**
 * Super Accurate MLS Batch Parser
 * Handles your real MLS data format perfectly
 */

require_once 'wp-load.php';

if (!is_user_logged_in() || !current_user_can('edit_posts')) {
    wp_die('You do not have permission to access this page.');
}

function parseMultipleMLSListings($rawText) {
    // Split by addresses - look for street address patterns at start of listings
    $listings = preg_split('/(?=\s*\d+\s+[A-Za-z\s]+(?:Rd|Dr|St|Ave|Ln|Ct|Blvd|Way|Pl|Cir|Trail|Pkwy))/m', $rawText);
    
    $parsedListings = [];
    foreach ($listings as $listingText) {
        if (strlen(trim($listingText)) < 200) continue; // Skip fragments
        
        $parsed = parseMLSListing($listingText);
        if (!empty($parsed['address'])) {
            $parsedListings[] = $parsed;
        }
    }
    
    return $parsedListings;
}

function parseMLSListing($text) {
    $listing = [];
    
    // Extract address - your exact format
    if (preg_match('/(\d+[-\w\s]*\s+[A-Za-z\s]+(?:Rd|Dr|St|Ave|Ln|Ct|Blvd|Way|Pl|Cir|Trail|Pkwy)(?:\s*#?\s*[\w\-]+)?),\s*([A-Za-z\s]+),\s*([A-Z]{2})\s+(\d{5})/', $text, $matches)) {
        $listing['address'] = trim($matches[1]);
        $listing['city'] = trim($matches[2]);
        $listing['state'] = $matches[3];
        $listing['zip_code'] = $matches[4];
        
        // Parse street components
        if (preg_match('/^(\d+[-\w]*)\s+(.+?)\s+(Rd|Dr|St|Ave|Ln|Ct|Blvd|Way|Pl|Cir|Trail|Pkwy)(\s*#?\s*[\w\-]+)?$/i', trim($matches[1]), $streetMatch)) {
            $listing['street_number'] = $streetMatch[1];
            $listing['street_name'] = trim($streetMatch[2]);
            $listing['street_type'] = $streetMatch[3];
            if (isset($streetMatch[4]) && trim($streetMatch[4])) {
                $listing['unit'] = trim($streetMatch[4]);
            }
        }
        
        // Create title
        $listing['title'] = $listing['street_number'] . ' ' . $listing['street_name'] . ' ' . $listing['street_type'];
    }
    
    // Status and price
    if (preg_match('/\s+(Active|Pending|Active Under Contract|Coming Soon)\s+Residential\s+(?:Price (?:Decrease|Increase)\s+)?\$?([\d,]+)/', $text, $matches)) {
        $listing['property_status'] = strtolower(str_replace(' ', '_', $matches[1]));
        $listing['listing_price'] = intval(str_replace(',', '', $matches[2]));
    }
    
    // MLS Number
    if (preg_match('/MLS #:\s*([A-Z0-9]+)/', $text, $matches)) {
        $listing['mls_number'] = $matches[1];
    }
    
    // Tax ID
    if (preg_match('/Tax ID #:\s*([\w\-\.]+)/', $text, $matches)) {
        $listing['tax_id'] = $matches[1];
    }
    
    // Bedrooms
    if (preg_match('/Beds:\s*(\d+)/', $text, $matches)) {
        $listing['bedrooms'] = intval($matches[1]);
    }
    
    // Bathrooms (handle "2 / 1" format)
    if (preg_match('/Baths:\s*(\d+)(?:\s*\/\s*(\d+))?/', $text, $matches)) {
        $listing['bathrooms_full'] = intval($matches[1]);
        $listing['bathrooms_half'] = isset($matches[2]) ? intval($matches[2]) : 0;
    }
    
    // Square feet
    if (preg_match('/Above Grade Fin SQFT:\s*([\d,]+)/', $text, $matches)) {
        $listing['square_feet'] = intval(str_replace(',', '', $matches[1]));
    } elseif (preg_match('/Total Fin SQFT:\s*([\d,]+)/', $text, $matches)) {
        $listing['square_feet'] = intval(str_replace(',', '', $matches[1]));
    }
    
    // Year built
    if (preg_match('/Year Built:\s*(\d{4})/', $text, $matches)) {
        $listing['year_built'] = intval($matches[1]);
    }
    
    // Property style
    if (preg_match('/Style:\s*([^\n\r]+)/', $text, $matches)) {
        $style = trim($matches[1]);
        $styleMap = [
            'Coastal' => 'coastal',
            'Contemporary' => 'contemporary',
            'Traditional' => 'traditional',
            'Ranch/Rambler' => 'ranch',
            'Cape Cod' => 'cape_cod',
            'Victorian' => 'victorian',
            'Craftsman' => 'craftsman',
            'Colonial' => 'colonial',
            'Modern' => 'modern',
            'Farmhouse' => 'farmhouse',
            'Farmhouse/National Folk' => 'farmhouse'
        ];
        $listing['property_style'] = $styleMap[$style] ?? strtolower(str_replace(['/', ' '], ['_', '_'], $style));
    }
    
    // Property condition
    if (preg_match('/Property Condition:\s*([^\n\r]+)/', $text, $matches)) {
        $condition = trim($matches[1]);
        $conditionMap = [
            'Excellent' => 'excellent',
            'Very Good' => 'very_good',
            'Good' => 'good',
            'Fair' => 'fair'
        ];
        $listing['condition'] = $conditionMap[$condition] ?? strtolower(str_replace(' ', '_', $condition));
    }
    
    // Structure type
    if (preg_match('/Structure Type:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['structure_type'] = trim($matches[1]);
    }
    
    // Stories
    if (preg_match('/Levels\/Stories:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['stories'] = trim($matches[1]);
    }
    
    // Central air
    if (preg_match('/Central Air:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['central_air'] = trim($matches[1]) === 'Yes';
    }
    
    // Basement
    if (preg_match('/Basement:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['basement'] = trim($matches[1]) === 'Yes';
    }
    
    // Garage
    if (preg_match('/Garage:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['garage'] = trim($matches[1]) === 'Yes';
    }
    
    // Days on market
    if (preg_match('/DOM:\s*(\d+)/', $text, $matches)) {
        $listing['days_on_market'] = intval($matches[1]);
    }
    
    // Price per sq ft
    if (preg_match('/Price \/ Sq Ft:\s*([\d,\.]+)/', $text, $matches)) {
        $listing['price_per_sqft'] = floatval(str_replace(',', '', $matches[1]));
    }
    
    // County
    if (preg_match('/County:\s*([^\n\r,]+)/', $text, $matches)) {
        $listing['county'] = trim($matches[1]);
    }
    
    // School district
    if (preg_match('/School District:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['school_district'] = trim($matches[1]);
    }
    
    // Annual taxes
    if (preg_match('/Tax Annual Amt \/ Year:\s*\$?([\d,]+)/', $text, $matches)) {
        $listing['property_taxes'] = intval(str_replace(',', '', $matches[1]));
    }
    
    // Lot size
    if (preg_match('/Lot Acres \/ SQFT:\s*([\d\.]+)a \/ ([\d,]+)sf/', $text, $matches)) {
        $listing['lot_size_acres'] = floatval($matches[1]);
        $listing['lot_size_sqft'] = intval(str_replace(',', '', $matches[2]));
    }
    
    // Parking spaces
    if (preg_match('/Total Parking Spaces\s+(\d+)/', $text, $matches)) {
        $listing['garage_spaces'] = intval($matches[1]);
    }
    
    // Builder
    if (preg_match('/Builder Name:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['builder'] = trim($matches[1]);
    }
    
    // Foundation
    if (preg_match('/Foundation Details:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['foundation_type'] = trim($matches[1]);
    }
    
    // Water source
    if (preg_match('/Water Source:\s*([^\n\r;]+)/', $text, $matches)) {
        $water = strtolower(trim($matches[1]));
        $listing['water_source'] = strpos($water, 'well') !== false ? 'well' : 'city';
    }
    
    // Sewer
    if (preg_match('/Sewer:\s*([^\n\r;]+)/', $text, $matches)) {
        $sewer = strtolower(trim($matches[1]));
        if (strpos($sewer, 'septic') !== false) {
            $listing['sewer_system'] = 'septic';
        } elseif (strpos($sewer, 'lpp') !== false) {
            $listing['sewer_system'] = 'lpp';
        } else {
            $listing['sewer_system'] = 'city_sewer';
        }
    }
    
    // Heating
    if (preg_match('/Heating:\s*([^\n\r;]+)/', $text, $matches)) {
        $listing['heating_system'] = trim($matches[1]);
    }
    
    // Heating fuel
    if (preg_match('/Heating Fuel:\s*([^\n\r;]+)/', $text, $matches)) {
        $listing['heating_fuel'] = trim($matches[1]);
    }
    
    // Hot water
    if (preg_match('/Hot Water:\s*([^\n\r;]+)/', $text, $matches)) {
        $listing['hot_water'] = trim($matches[1]);
    }
    
    // Zoning
    if (preg_match('/Zoning:\s*([A-Z0-9\-]+)/', $text, $matches)) {
        $listing['zoning'] = $matches[1];
    }
    
    // Subdivision
    if (preg_match('/Legal Subdivision:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['subdivision'] = trim($matches[1]);
    } elseif (preg_match('/Subdiv \/ Neigh:\s*([^\n\r]+)/', $text, $matches)) {
        $neigh = trim($matches[1]);
        if ($neigh !== 'NONE AVAILABLE') {
            $listing['subdivision'] = $neigh;
        }
    }
    
    // Description
    if (preg_match('/Public:\s*([^(?:Directions|Listing Details)]+)/s', $text, $matches)) {
        $listing['description'] = trim($matches[1]);
    }
    
    // Original price
    if (preg_match('/Original Price:\s*\$?([\d,]+)/', $text, $matches)) {
        $listing['original_price'] = intval(str_replace(',', '', $matches[1]));
    }
    
    // Listing date
    if (preg_match('/Listing Term Begins:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['listing_date'] = trim($matches[1]);
    }
    
    return $listing;
}

function createWordPressListing($data) {
    $post_data = [
        'post_title' => $data['title'] ?? 'New Listing',
        'post_content' => $data['description'] ?? '',
        'post_status' => 'publish',
        'post_type' => 'listing',
    ];
    
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        return false;
    }
    
    // Update all ACF fields
    $acf_fields = [
        'street_number', 'street_name', 'street_type', 'city', 'state', 'zip_code',
        'listing_price', 'mls_number', 'bedrooms', 'bathrooms_full', 'bathrooms_half',
        'square_feet', 'lot_size_acres', 'lot_size_sqft', 'year_built', 'days_on_market',
        'property_taxes', 'tax_id', 'price_per_sqft', 'stories', 'garage_spaces',
        'condition', 'property_style', 'builder', 'foundation_type', 'heating_system',
        'heating_fuel', 'hot_water', 'water_source', 'sewer_system', 'school_district',
        'zoning', 'subdivision', 'county', 'central_air', 'basement', 'garage',
        'structure_type', 'original_price', 'listing_date'
    ];
    
    foreach ($acf_fields as $field) {
        if (isset($data[$field]) && $data[$field] !== '') {
            update_field($field, $data[$field], $post_id);
        }
    }
    
    // Set property status taxonomy
    if (isset($data['property_status'])) {
        wp_set_object_terms($post_id, $data['property_status'], 'property_status');
    }
    
    return $post_id;
}

// Handle form submission
$results = [];
$errors = [];
$success_count = 0;

if ($_POST && isset($_POST['mls_data'])) {
    $rawData = $_POST['mls_data'];
    $parsed = parseMultipleMLSListings($rawData);
    
    foreach ($parsed as $listing) {
        if (isset($_POST['create_listings'])) {
            $post_id = createWordPressListing($listing);
            if ($post_id) {
                $success_count++;
                $listing['wp_post_id'] = $post_id;
            } else {
                $errors[] = "Failed to create listing: " . ($listing['title'] ?? 'Unknown');
            }
        }
        $results[] = $listing;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Super Accurate MLS Batch Parser</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; background: #f8f9fa; }
        .header { background: linear-gradient(135deg, #007cba 0%, #00a0d2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; text-align: center; }
        .form-section { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        textarea { width: 100%; min-height: 400px; padding: 15px; border: 2px solid #ddd; border-radius: 6px; font-family: monospace; font-size: 13px; }
        .btn { background: #007cba; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: bold; margin: 10px 5px 0 0; }
        .btn:hover { background: #005a87; }
        .btn-success { background: #46b450; }
        .btn-success:hover { background: #2e7d32; }
        .results { margin-top: 30px; }
        .listing-card { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 25px; overflow: hidden; }
        .listing-header { background: linear-gradient(135deg, #f1f1f1 0%, #e1e1e1 100%); padding: 20px; border-bottom: 1px solid #ddd; }
        .address { font-size: 22px; font-weight: bold; color: #333; margin-bottom: 10px; }
        .price-status { display: flex; justify-content: space-between; align-items: center; }
        .price { font-size: 24px; font-weight: bold; color: #007cba; }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: #e1f5fe; color: #01579b; }
        .listing-body { padding: 20px; }
        .field-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .field-group { background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 3px solid #007cba; }
        .field-group h4 { margin: 0 0 10px 0; color: #333; font-size: 14px; }
        .field { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px; }
        .field-label { font-weight: 500; color: #666; }
        .field-value { color: #333; }
        .success-msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .summary { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏠 Super Accurate MLS Batch Parser</h1>
        <p>Handles your exact MLS data format perfectly - paste all listings at once!</p>
    </div>

    <div class="form-section">
        <form method="POST">
            <label><strong>Paste All Your MLS Listings:</strong></label>
            <textarea name="mls_data" placeholder="Paste all your MLS listings here. The parser will automatically separate them..." required><?= htmlspecialchars($_POST['mls_data'] ?? '') ?></textarea>
            <div>
                <button type="submit" class="btn">📊 Parse Data</button>
                <?php if (!empty($results)): ?>
                    <button type="submit" name="create_listings" value="1" class="btn btn-success">✨ Create All WordPress Listings</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if ($success_count > 0): ?>
        <div class="success-msg">
            ✅ Successfully created <?= $success_count ?> WordPress listings!
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-msg">
            ❌ Errors: <?= implode(', ', $errors) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
        <div class="summary">
            <h2>📊 Parsing Results</h2>
            <p>Found <strong><?= count($results) ?></strong> listings ready for import</p>
        </div>

        <div class="results">
            <?php foreach ($results as $listing): ?>
                <div class="listing-card">
                    <div class="listing-header">
                        <div class="address"><?= htmlspecialchars($listing['address'] ?? 'Address Not Found') ?></div>
                        <div class="price-status">
                            <div class="price">$<?= number_format($listing['listing_price'] ?? 0) ?></div>
                            <div class="status"><?= htmlspecialchars($listing['property_status'] ?? 'unknown') ?></div>
                        </div>
                    </div>
                    <div class="listing-body">
                        <div class="field-grid">
                            <div class="field-group">
                                <h4>🏠 Basic Info</h4>
                                <div class="field"><span class="field-label">MLS:</span><span class="field-value"><?= htmlspecialchars($listing['mls_number'] ?? 'N/A') ?></span></div>
                                <div class="field"><span class="field-label">Beds:</span><span class="field-value"><?= $listing['bedrooms'] ?? 'N/A' ?></span></div>
                                <div class="field"><span class="field-label">Baths:</span><span class="field-value"><?= ($listing['bathrooms_full'] ?? 0) . '/' . ($listing['bathrooms_half'] ?? 0) ?></span></div>
                                <div class="field"><span class="field-label">Sq Ft:</span><span class="field-value"><?= $listing['square_feet'] ? number_format($listing['square_feet']) : 'N/A' ?></span></div>
                                <div class="field"><span class="field-label">Year:</span><span class="field-value"><?= $listing['year_built'] ?? 'N/A' ?></span></div>
                                <div class="field"><span class="field-label">Style:</span><span class="field-value"><?= htmlspecialchars($listing['property_style'] ?? 'N/A') ?></span></div>
                            </div>
                            
                            <div class="field-group">
                                <h4>📍 Location</h4>
                                <div class="field"><span class="field-label">City:</span><span class="field-value"><?= htmlspecialchars($listing['city'] ?? 'N/A') ?></span></div>
                                <div class="field"><span class="field-label">State:</span><span class="field-value"><?= htmlspecialchars($listing['state'] ?? 'N/A') ?></span></div>
                                <div class="field"><span class="field-label">ZIP:</span><span class="field-value"><?= htmlspecialchars($listing['zip_code'] ?? 'N/A') ?></span></div>
                                <div class="field"><span class="field-label">County:</span><span class="field-value"><?= htmlspecialchars($listing['county'] ?? 'N/A') ?></span></div>
                                <div class="field"><span class="field-label">School:</span><span class="field-value"><?= htmlspecialchars($listing['school_district'] ?? 'N/A') ?></span></div>
                                <div class="field"><span class="field-label">Subdivision:</span><span class="field-value"><?= htmlspecialchars($listing['subdivision'] ?? 'N/A') ?></span></div>
                            </div>
                            
                            <div class="field-group">
                                <h4>💰 Financial</h4>
                                <div class="field"><span class="field-label">$/Sq Ft:</span><span class="field-value">$<?= $listing['price_per_sqft'] ? number_format($listing['price_per_sqft'], 2) : 'N/A' ?></span></div>
                                <div class="field"><span class="field-label">Taxes:</span><span class="field-value">$<?= $listing['property_taxes'] ? number_format($listing['property_taxes']) : 'N/A' ?></span></div>
                                <div class="field"><span class="field-label">DOM:</span><span class="field-value"><?= $listing['days_on_market'] ?? 'N/A' ?></span></div>
                                <div class="field"><span class="field-label">Original:</span><span class="field-value">$<?= $listing['original_price'] ? number_format($listing['original_price']) : 'N/A' ?></span></div>
                                <div class="field"><span class="field-label">Lot Acres:</span><span class="field-value"><?= $listing['lot_size_acres'] ?? 'N/A' ?></span></div>
                                <div class="field"><span class="field-label">Parking:</span><span class="field-value"><?= $listing['garage_spaces'] ?? 'N/A' ?></span></div>
                            </div>
                            
                            <div class="field-group">
                                <h4>🔧 Systems</h4>
                                <div class="field"><span class="field-label">Water:</span><span class="field-value"><?= htmlspecialchars($listing['water_source'] ?? 'N/A') ?></span></div>
                                <div class="field"><span class="field-label">Sewer:</span><span class="field-value"><?= htmlspecialchars($listing['sewer_system'] ?? 'N/A') ?></span></div>
                                <div class="field"><span class="field-label">Heating:</span><span class="field-value"><?= htmlspecialchars($listing['heating_system'] ?? 'N/A') ?></span></div>
                                <div class="field"><span class="field-label">Builder:</span><span class="field-value"><?= htmlspecialchars($listing['builder'] ?? 'N/A') ?></span></div>
                                <div class="field"><span class="field-label">Foundation:</span><span class="field-value"><?= htmlspecialchars($listing['foundation_type'] ?? 'N/A') ?></span></div>
                                <div class="field"><span class="field-label">Condition:</span><span class="field-value"><?= htmlspecialchars($listing['condition'] ?? 'N/A') ?></span></div>
                            </div>
                        </div>
                        
                        <?php if (isset($listing['wp_post_id'])): ?>
                            <div style="margin-top: 15px; padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; text-align: center;">
                                ✅ Created WordPress Listing #<?= $listing['wp_post_id'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
