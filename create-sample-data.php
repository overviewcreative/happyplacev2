<?php
/**
 * Sample Data Generator for Happy Place
 * Creates sample listings and agents with comprehensive ACF field data
 */

// Ensure this script can only be run from command line or localhost
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('Access denied');
}

require_once __DIR__ . '/wp-config.php';

/**
 * Create a sample listing with comprehensive ACF field data
 */
function create_sample_listing($title, $data) {
    $listing_id = wp_insert_post([
        'post_title' => $title,
        'post_type' => 'listing',
        'post_status' => 'publish',
        'post_content' => $data['post_content'] ?? ''
    ]);

    if (is_wp_error($listing_id)) {
        echo "Error creating listing: " . $listing_id->get_error_message() . "\n";
        return false;
    }

    // Core listing data
    if (isset($data['price'])) update_field('price', $data['price'], $listing_id);
    if (isset($data['bedrooms'])) update_field('bedrooms', $data['bedrooms'], $listing_id);
    if (isset($data['bathrooms_full'])) update_field('bathrooms_full', $data['bathrooms_full'], $listing_id);
    if (isset($data['bathrooms_half'])) update_field('bathrooms_half', $data['bathrooms_half'], $listing_id);
    if (isset($data['square_feet'])) update_field('square_feet', $data['square_feet'], $listing_id);
    if (isset($data['year_built'])) update_field('year_built', $data['year_built'], $listing_id);
    if (isset($data['property_type'])) update_field('property_type', $data['property_type'], $listing_id);
    if (isset($data['status'])) update_field('status', $data['status'], $listing_id);

    // Address data
    if (isset($data['street_number'])) update_field('street_number', $data['street_number'], $listing_id);
    if (isset($data['street_name'])) update_field('street_name', $data['street_name'], $listing_id);
    if (isset($data['street_type'])) update_field('street_type', $data['street_type'], $listing_id);
    if (isset($data['city'])) update_field('city', $data['city'], $listing_id);
    if (isset($data['state'])) update_field('state', $data['state'], $listing_id);
    if (isset($data['zip'])) update_field('zip', $data['zip'], $listing_id);
    if (isset($data['address_display'])) update_field('address_display', $data['address_display'], $listing_id);
    if (isset($data['parcel_number'])) update_field('parcel_number', $data['parcel_number'], $listing_id);

    // Content data
    if (isset($data['property_description'])) update_field('property_description', $data['property_description'], $listing_id);
    if (isset($data['property_title'])) update_field('property_title', $data['property_title'], $listing_id);
    if (isset($data['property_highlights'])) update_field('property_highlights', $data['property_highlights'], $listing_id);
    if (isset($data['showing_instructions'])) update_field('showing_instructions', $data['showing_instructions'], $listing_id);
    if (isset($data['internal_notes'])) update_field('internal_notes', $data['internal_notes'], $listing_id);

    // Lot size data
    if (isset($data['lot_size_acres'])) update_field('lot_size_acres', $data['lot_size_acres'], $listing_id);
    if (isset($data['lot_size_sqft'])) update_field('lot_size_sqft', $data['lot_size_sqft'], $listing_id);

    // Financial data
    if (isset($data['property_taxes'])) update_field('property_taxes', $data['property_taxes'], $listing_id);
    if (isset($data['hoa_fees'])) update_field('hoa_fees', $data['hoa_fees'], $listing_id);
    if (isset($data['buyer_commission'])) update_field('buyer_commission', $data['buyer_commission'], $listing_id);
    if (isset($data['estimated_insurance'])) update_field('estimated_insurance', $data['estimated_insurance'], $listing_id);
    if (isset($data['estimated_utilities'])) update_field('estimated_utilities', $data['estimated_utilities'], $listing_id);

    // Features data
    if (isset($data['interior_features'])) update_field('interior_features', $data['interior_features'], $listing_id);
    if (isset($data['exterior_features'])) update_field('exterior_features', $data['exterior_features'], $listing_id);
    if (isset($data['property_features'])) update_field('property_features', $data['property_features'], $listing_id);

    // Amenities
    if (isset($data['has_pool'])) update_field('has_pool', $data['has_pool'], $listing_id);
    if (isset($data['pool_type'])) update_field('pool_type', $data['pool_type'], $listing_id);
    if (isset($data['has_spa'])) update_field('has_spa', $data['has_spa'], $listing_id);
    if (isset($data['garage_type'])) update_field('garage_type', $data['garage_type'], $listing_id);

    echo "Created listing: {$title} (ID: {$listing_id})\n";
    return $listing_id;
}

/**
 * Create a sample agent with comprehensive ACF field data
 */
function create_sample_agent($name, $data) {
    $agent_id = wp_insert_post([
        'post_title' => $name,
        'post_type' => 'agent',
        'post_status' => 'publish',
        'post_content' => $data['post_content'] ?? ''
    ]);

    if (is_wp_error($agent_id)) {
        echo "Error creating agent: " . $agent_id->get_error_message() . "\n";
        return false;
    }

    // Basic agent data
    if (isset($data['email'])) update_field('email', $data['email'], $agent_id);
    if (isset($data['phone'])) update_field('phone', $data['phone'], $agent_id);
    if (isset($data['title'])) update_field('title', $data['title'], $agent_id);
    if (isset($data['license_number'])) update_field('license_number', $data['license_number'], $agent_id);
    if (isset($data['specialties'])) update_field('specialties', $data['specialties'], $agent_id);

    echo "Created agent: {$name} (ID: {$agent_id})\n";
    return $agent_id;
}

echo "=== Creating Sample Data for Happy Place ===\n\n";

// Sample Listing 1: Luxury Waterfront Estate
$listing1_data = [
    'post_content' => 'Stunning waterfront estate with panoramic lake views.',
    'price' => 2500000,
    'bedrooms' => 5,
    'bathrooms_full' => 4,
    'bathrooms_half' => 1,
    'square_feet' => 4500,
    'year_built' => 2018,
    'property_type' => 'single_family',
    'status' => 'active',
    'street_number' => '123',
    'street_name' => 'Lakeside',
    'street_type' => 'Drive',
    'city' => 'Waterfront Heights',
    'state' => 'FL',
    'zip' => '33101',
    'address_display' => 'partial',
    'parcel_number' => 'WF-123-456-789',
    'property_description' => 'This magnificent waterfront estate offers unparalleled luxury living with 4,500 square feet of meticulously designed space. Floor-to-ceiling windows showcase breathtaking lake views from every room. The gourmet kitchen features top-of-the-line appliances, granite countertops, and a spacious island perfect for entertaining.',
    'property_title' => 'Luxury Waterfront Estate with Panoramic Lake Views',
    'property_highlights' => [
        ['highlight' => 'Private deep-water dock with boat slip'],
        ['highlight' => 'Infinity pool overlooking the lake'],
        ['highlight' => 'Chef\'s kitchen with premium appliances'],
        ['highlight' => 'Master suite with private balcony'],
        ['highlight' => 'Three-car attached garage']
    ],
    'showing_instructions' => '24-hour notice required. Please use lockbox code 1234. Remove shoes when entering.',
    'internal_notes' => 'Owner relocating for work. Motivated seller. Pool equipment recently serviced.',
    'lot_size_acres' => 1.2,
    'lot_size_sqft' => 52272,
    'property_taxes' => 12500,
    'hoa_fees' => 450,
    'buyer_commission' => '3%',
    'estimated_insurance' => 850,
    'estimated_utilities' => 425,
    'interior_features' => ['hardwood_floors', 'granite_countertops', 'stainless_appliances', 'fireplace', 'high_ceilings', 'walk_in_closets', 'master_suite'],
    'exterior_features' => ['covered_patio', 'deck', 'backyard', 'mature_trees', 'landscaped', 'sprinkler_system', 'outdoor_kitchen'],
    'property_features' => ['waterfront', 'water_view', 'move_in_ready', 'view_lot', 'level_lot'],
    'has_pool' => true,
    'pool_type' => 'infinity',
    'has_spa' => true,
    'garage_type' => 'attached'
];

$listing1_id = create_sample_listing('Luxury Waterfront Estate', $listing1_data);

// Sample Listing 2: Modern Family Home
$listing2_data = [
    'post_content' => 'Beautiful modern family home in desirable neighborhood.',
    'price' => 675000,
    'bedrooms' => 4,
    'bathrooms_full' => 2,
    'bathrooms_half' => 1,
    'square_feet' => 2850,
    'year_built' => 2020,
    'property_type' => 'single_family',
    'status' => 'active',
    'street_number' => '456',
    'street_name' => 'Oak',
    'street_type' => 'Street',
    'city' => 'Suburban Hills',
    'state' => 'FL',
    'zip' => '33102',
    'address_display' => 'full',
    'parcel_number' => 'SH-456-789-012',
    'property_description' => 'This stunning modern home combines contemporary design with family-friendly functionality. Built in 2020, it features an open-concept layout, premium finishes, and a thoughtfully designed floor plan that maximizes both privacy and togetherness.',
    'property_title' => 'Modern Family Home in Prime Neighborhood',
    'property_highlights' => [
        ['highlight' => 'Open-concept great room design'],
        ['highlight' => 'Gourmet kitchen with quartz countertops'],
        ['highlight' => 'Large master suite with walk-in closet'],
        ['highlight' => 'Covered patio perfect for entertaining'],
        ['highlight' => 'Two-car garage with workshop space']
    ],
    'showing_instructions' => 'Key available at listing office. Please schedule 2 hours in advance.',
    'internal_notes' => 'Builder warranty remaining. HOA includes lawn maintenance.',
    'lot_size_acres' => 0.25,
    'lot_size_sqft' => 10890,
    'property_taxes' => 8200,
    'hoa_fees' => 125,
    'buyer_commission' => '2.5%',
    'estimated_insurance' => 325,
    'estimated_utilities' => 225,
    'interior_features' => ['tile_floors', 'quartz_countertops', 'stainless_appliances', 'island_kitchen', 'walk_in_closets', 'pantry', 'laundry_room'],
    'exterior_features' => ['covered_patio', 'backyard', 'fenced_yard', 'landscaped', 'sprinkler_system'],
    'property_features' => ['new_construction', 'move_in_ready', 'hoa_community', 'family_friendly', 'corner_lot'],
    'has_pool' => false,
    'has_spa' => false,
    'garage_type' => 'attached'
];

$listing2_id = create_sample_listing('Modern Family Home', $listing2_data);

// Sample Listing 3: Cozy Starter Home
$listing3_data = [
    'post_content' => 'Perfect starter home with great potential.',
    'price' => 285000,
    'bedrooms' => 3,
    'bathrooms_full' => 2,
    'bathrooms_half' => 0,
    'square_feet' => 1650,
    'year_built' => 2005,
    'property_type' => 'single_family',
    'status' => 'active',
    'street_number' => '789',
    'street_name' => 'Pine',
    'street_type' => 'Avenue',
    'city' => 'Friendly Grove',
    'state' => 'FL',
    'zip' => '33103',
    'address_display' => 'full',
    'parcel_number' => 'FG-789-012-345',
    'property_description' => 'Charming 3-bedroom, 2-bathroom home perfect for first-time buyers or investors. This well-maintained property features a split floor plan, updated kitchen, and spacious backyard. Located in a quiet neighborhood with easy access to schools and shopping.',
    'property_title' => 'Charming Starter Home with Great Potential',
    'property_highlights' => [
        ['highlight' => 'Split floor plan for privacy'],
        ['highlight' => 'Updated kitchen with breakfast bar'],
        ['highlight' => 'Spacious master bedroom'],
        ['highlight' => 'Large fenced backyard'],
        ['highlight' => 'Close to top-rated schools']
    ],
    'showing_instructions' => 'Vacant - use showing key. Turn off alarm (code 5678).',
    'internal_notes' => 'Roof replaced 2022. AC unit serviced regularly. Great rental potential.',
    'lot_size_acres' => 0.18,
    'lot_size_sqft' => 7840,
    'property_taxes' => 4500,
    'hoa_fees' => 0,
    'buyer_commission' => '3%',
    'estimated_insurance' => 185,
    'estimated_utilities' => 165,
    'interior_features' => ['laminate_floors', 'updated_kitchen', 'breakfast_bar', 'ceiling_fans', 'walk_in_closets'],
    'exterior_features' => ['backyard', 'fenced_yard', 'storage_shed', 'mature_trees'],
    'property_features' => ['move_in_ready', 'family_friendly', 'investment_property'],
    'has_pool' => false,
    'has_spa' => false,
    'garage_type' => 'none'
];

$listing3_id = create_sample_listing('Cozy Starter Home', $listing3_data);

echo "\n=== Creating Sample Agents ===\n\n";

// Sample Agent 1
$agent1_data = [
    'post_content' => 'Experienced luxury real estate specialist with over 15 years in the industry.',
    'email' => 'sarah.johnson@happyplace.com',
    'phone' => '555-123-4567',
    'title' => 'Senior Real Estate Advisor',
    'license_number' => 'FL-RE-123456',
    'specialties' => ['luxury_homes', 'waterfront', 'investment_properties']
];

$agent1_id = create_sample_agent('Sarah Johnson', $agent1_data);

// Sample Agent 2
$agent2_data = [
    'post_content' => 'Dedicated to helping families find their perfect home in great neighborhoods.',
    'email' => 'mike.williams@happyplace.com',
    'phone' => '555-234-5678',
    'title' => 'Family Home Specialist',
    'license_number' => 'FL-RE-234567',
    'specialties' => ['family_homes', 'first_time_buyers', 'relocations']
];

$agent2_id = create_sample_agent('Mike Williams', $agent2_data);

echo "\n=== Sample Data Creation Complete! ===\n\n";
echo "Created Listings:\n";
echo "- Luxury Waterfront Estate (ID: {$listing1_id})\n";
echo "- Modern Family Home (ID: {$listing2_id})\n";
echo "- Cozy Starter Home (ID: {$listing3_id})\n\n";
echo "Created Agents:\n";
echo "- Sarah Johnson (ID: {$agent1_id})\n";
echo "- Mike Williams (ID: {$agent2_id})\n\n";
echo "Next step: Test bridge functions with this data!\n";