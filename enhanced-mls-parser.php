<?php
/**
 * Enhanced MLS Parser for Real Estate Listings
 * Handles all variations found in actual MLS data
 */

// Include WordPress if needed
if (file_exists('wp-load.php')) {
    require_once('wp-load.php');
}

function parseMLSData($mlsText) {
    // Split into individual listings
    $listings = preg_split('/(?=\s+\d+\s+[A-Za-z\s]+(?:Rd|Dr|St|Ave|Ln|Ct|Blvd|Way|Pl|Cir|Trail|Pkwy),\s*[A-Za-z\s]+,\s*[A-Z]{2}\s+\d{5})/m', $mlsText);
    
    $parsedListings = [];
    
    foreach ($listings as $listingText) {
        if (strlen(trim($listingText)) < 100) continue; // Skip short fragments
        
        $listing = parseIndividualListing($listingText);
        if (!empty($listing['address'])) {
            $parsedListings[] = $listing;
        }
    }
    
    return $parsedListings;
}

function parseIndividualListing($text) {
    $listing = [];
    
    // Extract address (first line pattern)
    if (preg_match('/(\d+[-\w\s]*\s+[A-Za-z\s]+(?:Rd|Dr|St|Ave|Ln|Ct|Blvd|Way|Pl|Cir|Trail|Pkwy)(?:\s*#?\s*\w+)?),\s*([A-Za-z\s]+),\s*([A-Z]{2})\s+(\d{5})/', $text, $matches)) {
        $listing['address'] = trim($matches[1]);
        $listing['city'] = trim($matches[2]);
        $listing['state'] = $matches[3];
        $listing['zip'] = $matches[4];
        
        // Parse street components
        if (preg_match('/^(\d+[-\w]*)\s+(.+?)\s+(Rd|Dr|St|Ave|Ln|Ct|Blvd|Way|Pl|Cir|Trail|Pkwy)(\s*#?\s*\w+)?$/i', trim($matches[1]), $streetMatch)) {
            $listing['street_number'] = $streetMatch[1];
            $listing['street_name'] = trim($streetMatch[2]);
            $listing['street_type'] = $streetMatch[3];
            $listing['unit'] = isset($streetMatch[4]) ? trim($streetMatch[4]) : '';
        }
    }
    
    // Extract status and price
    if (preg_match('/\s+(Active|Pending|Active Under Contract|Coming Soon)\s+Residential\s+(?:Price (?:Decrease|Increase)\s+)?\$?([\d,]+)/', $text, $matches)) {
        $listing['status'] = $matches[1];
        $listing['price'] = str_replace(',', '', $matches[2]);
    }
    
    // MLS Number
    if (preg_match('/MLS #:\s*([A-Z0-9]+)/', $text, $matches)) {
        $listing['mls_number'] = $matches[1];
    }
    
    // Tax ID
    if (preg_match('/Tax ID #:\s*([\w\-\.]+)/', $text, $matches)) {
        $listing['tax_id'] = $matches[1];
    }
    
    // Basic property info
    extractBasicInfo($text, $listing);
    
    // Property details
    extractPropertyDetails($text, $listing);
    
    // Location details
    extractLocationDetails($text, $listing);
    
    // Building information
    extractBuildingInfo($text, $listing);
    
    // Lot information
    extractLotInfo($text, $listing);
    
    // Utilities and features
    extractUtilitiesAndFeatures($text, $listing);
    
    // Financial information
    extractFinancialInfo($text, $listing);
    
    // Listing details
    extractListingDetails($text, $listing);
    
    return $listing;
}

function extractBasicInfo($text, &$listing) {
    // Beds
    if (preg_match('/Beds:\s*(\d+)/', $text, $matches)) {
        $listing['bedrooms'] = $matches[1];
    }
    
    // Baths (handle various formats like "2", "2 / 1", "3 / 0")
    if (preg_match('/Baths:\s*(\d+)(?:\s*\/\s*(\d+))?/', $text, $matches)) {
        $fullBaths = $matches[1];
        $halfBaths = isset($matches[2]) ? $matches[2] : 0;
        $listing['bathrooms_full'] = $fullBaths;
        $listing['bathrooms_half'] = $halfBaths;
        $listing['bathrooms_total'] = $fullBaths + ($halfBaths * 0.5);
    }
    
    // Square footage
    if (preg_match('/Above Grade Fin SQFT:\s*([\d,]+)/', $text, $matches)) {
        $listing['square_feet'] = str_replace(',', '', $matches[1]);
    }
    
    if (preg_match('/Total Fin SQFT:\s*([\d,]+)/', $text, $matches)) {
        $listing['total_square_feet'] = str_replace(',', '', $matches[1]);
    }
    
    // Year built
    if (preg_match('/Year Built:\s*(\d{4})/', $text, $matches)) {
        $listing['year_built'] = $matches[1];
    }
    
    // Style
    if (preg_match('/Style:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['style'] = trim($matches[1]);
    }
    
    // Property condition
    if (preg_match('/Property Condition:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['condition'] = trim($matches[1]);
    }
    
    // Structure type
    if (preg_match('/Structure Type:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['structure_type'] = trim($matches[1]);
    }
    
    // Levels/Stories
    if (preg_match('/Levels\/Stories:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['stories'] = trim($matches[1]);
    }
    
    // Central Air
    if (preg_match('/Central Air:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['central_air'] = trim($matches[1]);
    }
    
    // Basement
    if (preg_match('/Basement:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['basement'] = trim($matches[1]);
    }
    
    // Garage
    if (preg_match('/Garage:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['garage'] = trim($matches[1]);
    }
    
    // Waterfront
    if (preg_match('/Waterfront:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['waterfront'] = trim($matches[1]);
    }
}

function extractPropertyDetails($text, &$listing) {
    // Price per sq ft
    if (preg_match('/Price \/ Sq Ft:\s*([\d,\.]+)/', $text, $matches)) {
        $listing['price_per_sqft'] = str_replace(',', '', $matches[1]);
    }
    
    // Days on market
    if (preg_match('/DOM:\s*(\d+)/', $text, $matches)) {
        $listing['days_on_market'] = $matches[1];
    }
    
    // Original price
    if (preg_match('/Original Price:\s*\$?([\d,]+)/', $text, $matches)) {
        $listing['original_price'] = str_replace(',', '', $matches[1]);
    }
    
    // Previous list price
    if (preg_match('/Previous List Price:\s*\$?([\d,]+)/', $text, $matches)) {
        $listing['previous_price'] = str_replace(',', '', $matches[1]);
    }
    
    // Total rooms
    if (preg_match('/Total Rooms:\s*(\d+)/', $text, $matches)) {
        $listing['total_rooms'] = $matches[1];
    }
}

function extractLocationDetails($text, &$listing) {
    // County
    if (preg_match('/County:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['county'] = trim($matches[1]);
    }
    
    // School district
    if (preg_match('/School District:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['school_district'] = trim($matches[1]);
    }
    
    // MLS Area
    if (preg_match('/MLS Area:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['mls_area'] = trim($matches[1]);
    }
    
    // Legal subdivision
    if (preg_match('/Legal Subdivision:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['subdivision'] = trim($matches[1]);
    }
    
    // Subdiv/Neigh
    if (preg_match('/Subdiv \/ Neigh:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['neighborhood'] = trim($matches[1]);
    }
    
    // In city limits
    if (preg_match('/In City Limits:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['in_city_limits'] = trim($matches[1]);
    }
    
    // Zoning
    if (preg_match('/Zoning:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['zoning'] = trim($matches[1]);
    }
}

function extractBuildingInfo($text, &$listing) {
    // Builder name
    if (preg_match('/Builder Name:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['builder'] = trim($matches[1]);
    }
    
    // Foundation details
    if (preg_match('/Foundation Details:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['foundation'] = trim($matches[1]);
    }
    
    // Construction materials
    if (preg_match('/Construction Materials:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['construction_materials'] = trim($matches[1]);
    }
    
    // Flooring type
    if (preg_match('/Flooring Type:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['flooring'] = trim($matches[1]);
    }
    
    // Roof
    if (preg_match('/Roof:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['roof'] = trim($matches[1]);
    }
    
    // Wall & Ceiling Types
    if (preg_match('/Wall & Ceiling Types:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['wall_ceiling_types'] = trim($matches[1]);
    }
    
    // Basement type
    if (preg_match('/Basement Type:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['basement_type'] = trim($matches[1]);
    }
}

function extractLotInfo($text, &$listing) {
    // Lot acres/sqft
    if (preg_match('/Lot Acres \/ SQFT:\s*([\d\.]+)a \/ ([\d,]+)sf/', $text, $matches)) {
        $listing['lot_acres'] = $matches[1];
        $listing['lot_sqft'] = str_replace(',', '', $matches[2]);
    }
    
    // Lot size dimensions
    if (preg_match('/Lot Size Dimensions:\s*([\d\.]+) x ([\d\.]+)/', $text, $matches)) {
        $listing['lot_width'] = $matches[1];
        $listing['lot_depth'] = $matches[2];
    }
    
    // Lot features
    if (preg_match('/Lot Features:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['lot_features'] = trim($matches[1]);
    }
    
    // Fencing
    if (preg_match('/Fencing:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['fencing'] = trim($matches[1]);
    }
    
    // Views
    if (preg_match('/Views:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['views'] = trim($matches[1]);
    }
}

function extractUtilitiesAndFeatures($text, &$listing) {
    // Parking spaces
    if (preg_match('/Total Parking Spaces\s+(\d+)/', $text, $matches)) {
        $listing['parking_spaces'] = $matches[1];
    }
    
    // Attached garage spaces
    if (preg_match('/Attached Garage - # of Spaces\s+(\d+)/', $text, $matches)) {
        $listing['garage_spaces'] = $matches[1];
    }
    
    // Water source
    if (preg_match('/Water Source:\s*([^\n\r;]+)/', $text, $matches)) {
        $listing['water_source'] = trim($matches[1]);
    }
    
    // Sewer
    if (preg_match('/Sewer:\s*([^\n\r;]+)/', $text, $matches)) {
        $listing['sewer'] = trim($matches[1]);
    }
    
    // Heating
    if (preg_match('/Heating:\s*([^\n\r;]+)/', $text, $matches)) {
        $listing['heating'] = trim($matches[1]);
    }
    
    // Heating fuel
    if (preg_match('/Heating Fuel:\s*([^\n\r;]+)/', $text, $matches)) {
        $listing['heating_fuel'] = trim($matches[1]);
    }
    
    // Cooling fuel
    if (preg_match('/Cooling Fuel:\s*([^\n\r;]+)/', $text, $matches)) {
        $listing['cooling_fuel'] = trim($matches[1]);
    }
    
    // Hot water
    if (preg_match('/Hot Water:\s*([^\n\r;]+)/', $text, $matches)) {
        $listing['hot_water'] = trim($matches[1]);
    }
    
    // Electric service
    if (preg_match('/Electric Service:\s*([^\n\r;]+)/', $text, $matches)) {
        $listing['electric_service'] = trim($matches[1]);
    }
}

function extractFinancialInfo($text, &$listing) {
    // Tax annual amount
    if (preg_match('/Tax Annual Amt \/ Year:\s*\$?([\d,]+)/', $text, $matches)) {
        $listing['annual_taxes'] = str_replace(',', '', $matches[1]);
    }
    
    // School tax
    if (preg_match('/School Tax:\s*\$?([\d,]+)/', $text, $matches)) {
        $listing['school_tax'] = str_replace(',', '', $matches[1]);
    }
    
    // County tax
    if (preg_match('/County Tax:\s*\$?([\d,]+)/', $text, $matches)) {
        $listing['county_tax'] = str_replace(',', '', $matches[1]);
    }
    
    // HOA fee
    if (preg_match('/HOA Fee:\s*\$?([\d,]+)\s*\/\s*([^\n\r]+)/', $text, $matches)) {
        $listing['hoa_fee'] = str_replace(',', '', $matches[1]);
        $listing['hoa_frequency'] = trim($matches[2]);
    }
    
    // Tax assessed value
    if (preg_match('/Tax Assessed Value:\s*\$?([\d,]+)/', $text, $matches)) {
        $listing['assessed_value'] = str_replace(',', '', $matches[1]);
    }
}

function extractListingDetails($text, &$listing) {
    // Listing term begins
    if (preg_match('/Listing Term Begins:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['listing_date'] = trim($matches[1]);
    }
    
    // Possession
    if (preg_match('/Possession:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['possession'] = trim($matches[1]);
    }
    
    // Acceptable financing
    if (preg_match('/Acceptable Financing:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['financing'] = trim($matches[1]);
    }
    
    // Federal flood zone
    if (preg_match('/Federal Flood Zone:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['flood_zone'] = trim($matches[1]);
    }
    
    // Vacation rental
    if (preg_match('/Vacation Rental:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['vacation_rental'] = trim($matches[1]);
    }
    
    // Pets allowed
    if (preg_match('/Pets Allowed:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['pets_allowed'] = trim($matches[1]);
    }
    
    // Pet restrictions
    if (preg_match('/Pet Restrictions:\s*([^\n\r]+)/', $text, $matches)) {
        $listing['pet_restrictions'] = trim($matches[1]);
    }
    
    // Extract description from Public section
    if (preg_match('/Public:\s*([^(?:Directions|Listing Details)]+)/s', $text, $matches)) {
        $listing['description'] = trim($matches[1]);
    }
}

// Handle the form submission
$results = [];
$rawData = '';

if ($_POST && isset($_POST['mls_data'])) {
    $rawData = $_POST['mls_data'];
    $results = parseMLSData($rawData);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced MLS Parser - Real Estate Data</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background: #f8fafc;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .input-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        textarea {
            width: 100%;
            min-height: 300px;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-family: monospace;
            font-size: 13px;
            line-height: 1.5;
            resize: vertical;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin-top: 15px;
        }
        
        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .results-section {
            margin-top: 30px;
        }
        
        .listing-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            overflow: hidden;
            border-left: 4px solid #667eea;
        }
        
        .listing-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .address {
            font-size: 24px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
        }
        
        .price-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .price {
            font-size: 28px;
            font-weight: 800;
            color: #667eea;
        }
        
        .status {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .status.active { background: #dcfce7; color: #166534; }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.contract { background: #dbeafe; color: #1e40af; }
        
        .listing-body {
            padding: 25px;
        }
        
        .field-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .field-group {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }
        
        .field-group h3 {
            margin: 0 0 15px 0;
            color: #1a202c;
            font-size: 16px;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }
        
        .field {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .field-label {
            font-weight: 500;
            color: #4a5568;
        }
        
        .field-value {
            color: #1a202c;
            font-weight: 400;
            text-align: right;
        }
        
        .description {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 3px solid #48bb78;
            margin-top: 20px;
        }
        
        .description h3 {
            margin: 0 0 12px 0;
            color: #1a202c;
            font-size: 16px;
        }
        
        .description p {
            line-height: 1.6;
            color: #4a5568;
            margin: 0;
        }
        
        .stats-summary {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .stats-summary h2 {
            margin: 0 0 15px 0;
            color: #1a202c;
        }
        
        .create-btn {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .create-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(72, 187, 120, 0.3);
        }
        
        .error {
            background: #fed7d7;
            color: #9b2c2c;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .success {
            background: #c6f6d5;
            color: #276749;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏠 Enhanced MLS Parser</h1>
        <p>Advanced parsing for real MLS listing data with comprehensive field extraction</p>
    </div>

    <div class="input-section">
        <form method="POST">
            <label for="mls_data"><strong>Paste Your MLS Listing Data:</strong></label>
            <textarea name="mls_data" id="mls_data" placeholder="Paste all your MLS listings here. The parser will automatically detect and separate individual listings..." required><?= htmlspecialchars($rawData) ?></textarea>
            <button type="submit" class="submit-btn">🔍 Parse MLS Data</button>
        </form>
    </div>

    <?php if (!empty($results)): ?>
        <div class="stats-summary">
            <h2>📊 Parsing Results</h2>
            <p>Successfully parsed <strong><?= count($results) ?></strong> listings from your MLS data</p>
        </div>

        <div class="results-section">
            <?php foreach ($results as $index => $listing): ?>
                <div class="listing-card">
                    <div class="listing-header">
                        <div class="address">
                            <?= htmlspecialchars($listing['address'] ?? 'Address Not Found') ?>
                        </div>
                        <div class="price-status">
                            <div class="price">
                                $<?= number_format($listing['price'] ?? 0) ?>
                            </div>
                            <div class="status <?= strtolower(str_replace(' ', '', $listing['status'] ?? 'unknown')) ?>">
                                <?= htmlspecialchars($listing['status'] ?? 'Unknown') ?>
                            </div>
                        </div>
                    </div>

                    <div class="listing-body">
                        <div class="field-grid">
                            <!-- Basic Information -->
                            <div class="field-group">
                                <h3>🏠 Basic Information</h3>
                                <div class="field">
                                    <span class="field-label">MLS #:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['mls_number'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Bedrooms:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['bedrooms'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Bathrooms:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['bathrooms_total'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Square Feet:</span>
                                    <span class="field-value"><?= $listing['square_feet'] ? number_format($listing['square_feet']) : 'N/A' ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Year Built:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['year_built'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Style:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['style'] ?? 'N/A') ?></span>
                                </div>
                            </div>

                            <!-- Location Details -->
                            <div class="field-group">
                                <h3>📍 Location</h3>
                                <div class="field">
                                    <span class="field-label">City:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['city'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">State:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['state'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">ZIP:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['zip'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">County:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['county'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">School District:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['school_district'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Neighborhood:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['neighborhood'] ?? 'N/A') ?></span>
                                </div>
                            </div>

                            <!-- Property Features -->
                            <div class="field-group">
                                <h3>🏗️ Property Features</h3>
                                <div class="field">
                                    <span class="field-label">Structure Type:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['structure_type'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Stories:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['stories'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Central Air:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['central_air'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Basement:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['basement'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Garage:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['garage'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Waterfront:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['waterfront'] ?? 'N/A') ?></span>
                                </div>
                            </div>

                            <!-- Financial Information -->
                            <div class="field-group">
                                <h3>💰 Financial Details</h3>
                                <div class="field">
                                    <span class="field-label">Price/Sq Ft:</span>
                                    <span class="field-value">$<?= $listing['price_per_sqft'] ? number_format($listing['price_per_sqft'], 2) : 'N/A' ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Annual Taxes:</span>
                                    <span class="field-value">$<?= $listing['annual_taxes'] ? number_format($listing['annual_taxes']) : 'N/A' ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Days on Market:</span>
                                    <span class="field-value"><?= htmlspecialchars($listing['days_on_market'] ?? 'N/A') ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Original Price:</span>
                                    <span class="field-value">$<?= $listing['original_price'] ? number_format($listing['original_price']) : 'N/A' ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">HOA Fee:</span>
                                    <span class="field-value">$<?= $listing['hoa_fee'] ? number_format($listing['hoa_fee']) . ' ' . ($listing['hoa_frequency'] ?? '') : 'N/A' ?></span>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($listing['description'])): ?>
                            <div class="description">
                                <h3>📝 Property Description</h3>
                                <p><?= htmlspecialchars(substr($listing['description'], 0, 500)) ?><?= strlen($listing['description']) > 500 ? '...' : '' ?></p>
                            </div>
                        <?php endif; ?>

                        <div style="text-align: center; margin-top: 20px;">
                            <button onclick="createListing(<?= $index ?>)" class="create-btn">
                                ✨ Create WordPress Listing
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <script>
        function createListing(index) {
            // This would integrate with your WordPress listing creation
            alert('Creating WordPress listing for property #' + (index + 1) + '. This would integrate with your create-listing-from-mls.php script.');
        }
    </script>
</body>
</html>
