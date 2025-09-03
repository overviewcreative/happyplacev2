<?php
/**
 * Create Listing from MLS Data
 * Example: 21222 Cool Spring Rd, Milton, DE 19968
 * 
 * This script creates a WordPress listing post with all ACF fields populated
 * from the provided MLS data.
 */

// Prevent direct access
if (!defined('WPINC')) {
    die;
}

// Listing data from MLS
$listing_data = [
    // Basic Info
    'title' => '21222 Cool Spring Rd',
    'status' => 'publish',
    'post_type' => 'listing',
    
    // Address
    'street_number' => '21222',
    'street_name' => 'Cool Spring',
    'street_type' => 'Rd',
    'city' => 'Milton',
    'state' => 'DE',
    'zip_code' => '19968',
    'county' => 'Sussex',
    
    // Core Property Info
    'listing_price' => 869900,
    'mls_number' => 'DESU2095796',
    'property_status' => 'active', // Taxonomy term
    'property_type' => 'single-family', // Taxonomy term
    'is_featured' => false,
    
    // Property Details
    'bedrooms' => 4,
    'bathrooms_full' => 3,
    'bathrooms_half' => 0,
    'square_feet' => 2910,
    'lot_size_acres' => 1.45, // Use acres field
    'lot_size_sqft' => 63162, // Alternative sq ft field
    'year_built' => 2022,
    
    // Features (using our checkbox system)
    'interior_features' => ['fireplace', 'high_ceilings'], // Checkbox array
    'exterior_features' => ['garage'], // Checkbox array
    'pool' => false,
    'waterfront' => false,
    
    // Financial (fields we actually have)
    'property_taxes' => 1210, // Combined school + county
    'hoa_fees' => 0, // No HOA
    
    // Dates
    'listing_date' => '2024-12-17',
    'days_on_market' => 2,
    'last_updated' => '2025-08-31',
    
    // Construction & Systems (new fields)
    'builder' => 'WN Builders inc',
    'property_style' => 'coastal',
    'condition' => 'excellent',
    'stories' => 2,
    'roof_type' => 'architectural_shingle',
    'foundation_type' => 'crawl_space',
    'exterior_materials' => ['vinyl_siding'],
    'flooring_types' => ['luxury_vinyl', 'tile'],
    'heating_system' => 'heat_pump',
    'heating_fuel' => 'electric',
    'cooling_system' => 'heat_pump',
    'cooling_fuel' => 'electric',
    'water_source' => 'well',
    'sewer_system' => 'lpp',
    'electric_service' => '200_plus_amp',
    'hot_water' => 'tankless_gas',
    
    // Location details (new fields)
    'school_district' => 'Cape Henlopen',
    'zoning' => 'AR-1',
    'subdivision' => '',
    'flood_zone' => 'no',
    'county' => 'Sussex',
    
    // Financial (new fields)
    'tax_id' => '234-10.00-75.10',
    'price_per_sqft' => 298.93,
    
    // Description
    'description' => 'Welcome to 21222 Cool Spring Road in Milton, Delaware masterfully crafted by local WN Builders in 2022. This 4-bedroom, 3-bathroom home offers over 2,900 square feet of carefully refined living space on a tranquil 1.45-acre lot, surrounded by mature trees with no HOA and no restrictions! Inside, the open-concept design is bathed in natural light, showcasing a chef\'s kitchen with granite countertops, stainless steel appliances, convenient walk-in pantry with extra cabinetry, and a generous dining room perfect for hosting. The stunning sunroom, complete with a temperature-controlled gas fireplace, provides an escape to relaxation like no other. Relaxation is only increased by the included hot tub on the patio just steps outside. The first-floor primary suite boasts a luxurious bathroom offering its own water closet, & standalone soaking tub. You\'ll also find a beautiful walk-in custom closet to provide you with both function and style. Step outside to the professionally curated lawn complete with a built-in irrigation system and marvel at the 24x40 fully insulated pole barn with a 12 high door and an internal RV plug so you can say goodbye to paying for storage for your RVs, boats, or other toys! Across the street is the Woodridge neighborhood - perfect for getting your steps in! Located just minutes from downtown Milton and Long Neck, you can enjoy shopping, historic architecture, and local events all year long. Plus, with Rehoboth and Lewes Beaches only a short drive away, you never have to worry about having nothing to do! Other incredible features include a separate private guest suite, a whole house generator, a whole house surge protector, and an attached 2-car garage. With low property taxes and no HOA, what are you waiting for?',
    
    // Special Features
    'inclusions' => 'WN9500DF Generator',
    'exclusions' => 'Furniture',
    
    // Open Houses
    'open_houses' => [
        [
            'date' => '2025-09-06',
            'start_time' => '10:00 AM',
            'end_time' => '1:00 PM',
            'method' => 'In-Person Only',
            'type' => 'Public'
        ],
        [
            'date' => '2025-09-07', 
            'start_time' => '10:00 AM',
            'end_time' => '1:00 PM',
            'method' => 'In-Person Only',
            'type' => 'Public'
        ]
    ]
];

function create_listing_from_mls_data($data) {
    // Create the post
    $post_data = [
        'post_title' => $data['title'],
        'post_content' => $data['description'],
        'post_status' => $data['status'],
        'post_type' => $data['post_type'],
        'meta_input' => []
    ];
    
    // Insert the post
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        return false;
    }
    
    // Update ACF fields - only use fields we actually have
    $acf_fields = [
        // Address fields
        'street_number' => $data['street_number'],
        'street_name' => $data['street_name'], 
        'street_type' => $data['street_type'],
        'city' => $data['city'],
        'state' => $data['state'],
        'zip_code' => $data['zip_code'],
        
        // Core property info
        'listing_price' => $data['listing_price'],
        'mls_number' => $data['mls_number'],
        'is_featured' => $data['is_featured'],
        
        // Property details
        'bedrooms' => $data['bedrooms'],
        'bathrooms_full' => $data['bathrooms_full'],
        'bathrooms_half' => $data['bathrooms_half'],
        'square_feet' => $data['square_feet'],
        'lot_size_acres' => $data['lot_size_acres'] ?? null,
        'lot_size_sqft' => $data['lot_size_sqft'] ?? null,
        'year_built' => $data['year_built'],
        
        // Features (checkbox arrays)
        'interior_features' => $data['interior_features'] ?? [],
        'exterior_features' => $data['exterior_features'] ?? [],
        
        // Financial (fields we actually have)
        'property_taxes' => $data['property_taxes'] ?? null,
        'hoa_fees' => $data['hoa_fees'] ?? null,
        
        // Dates
        'listing_date' => $data['listing_date'] ?? null,
        'days_on_market' => $data['days_on_market'] ?? null,
        'last_updated' => $data['last_updated'] ?? null,
        
        // Construction & Systems
        'builder' => $data['builder'] ?? null,
        'property_style' => $data['property_style'] ?? null,
        'condition' => $data['condition'] ?? null,
        'stories' => $data['stories'] ?? null,
        'roof_type' => $data['roof_type'] ?? null,
        'foundation_type' => $data['foundation_type'] ?? null,
        'exterior_materials' => $data['exterior_materials'] ?? [],
        'flooring_types' => $data['flooring_types'] ?? [],
        'heating_system' => $data['heating_system'] ?? null,
        'heating_fuel' => $data['heating_fuel'] ?? null,
        'cooling_system' => $data['cooling_system'] ?? null,
        'cooling_fuel' => $data['cooling_fuel'] ?? null,
        'water_source' => $data['water_source'] ?? null,
        'sewer_system' => $data['sewer_system'] ?? null,
        'electric_service' => $data['electric_service'] ?? null,
        'hot_water' => $data['hot_water'] ?? null,
        
        // Location details
        'school_district' => $data['school_district'] ?? null,
        'zoning' => $data['zoning'] ?? null,
        'subdivision' => $data['subdivision'] ?? null,
        'flood_zone' => $data['flood_zone'] ?? null,
        'county' => $data['county'] ?? null,
        
        // Financial (new fields)
        'tax_id' => $data['tax_id'] ?? null,
        'price_per_sqft' => $data['price_per_sqft'] ?? null,
        
        // Special features
        'inclusions' => $data['inclusions'] ?? null,
        'exclusions' => $data['exclusions'] ?? null,
    ];
    
    // Update all ACF fields
    foreach ($acf_fields as $field_name => $field_value) {
        if ($field_value !== null) {
            update_field($field_name, $field_value, $post_id);
        }
    }
    
    // Set taxonomies
    if (isset($data['property_status'])) {
        wp_set_object_terms($post_id, $data['property_status'], 'property_status');
    }
    
    if (isset($data['property_type'])) {
        wp_set_object_terms($post_id, $data['property_type'], 'property_type');
    }
    
    // Handle open houses (if you have a repeater field for this)
    if (isset($data['open_houses']) && !empty($data['open_houses'])) {
        update_field('open_houses', $data['open_houses'], $post_id);
    }
    
    return $post_id;
}

// Second listing example from your data
$listing_data_2 = [
    // Basic Info
    'title' => '515 Peachtree Ln',
    'status' => 'publish',
    'post_type' => 'listing',
    
    // Address
    'street_number' => '515',
    'street_name' => 'Peachtree',
    'street_type' => 'Ln',
    'city' => 'Seaford',
    'state' => 'DE',
    'zip_code' => '19973',
    'county' => 'Sussex',
    
    // Core Property Info
    'listing_price' => 349000,
    'mls_number' => 'DESU2092906',
    'property_status' => 'active',
    'property_type' => 'single-family',
    'is_featured' => false,
    
    // Property Details
    'bedrooms' => 4,
    'bathrooms_full' => 2,
    'bathrooms_half' => 1,
    'square_feet' => 1741, // Total finished
    'lot_size_acres' => 0.17,
    'lot_size_sqft' => 7405,
    'year_built' => 2023,
    'stories' => 2,
    'garage_spaces' => 2,
    
    // Features
    'interior_features' => ['walk_in_closets', 'island_kitchen'], 
    'exterior_features' => ['attached_garage', 'sidewalks'],
    
    // Financial
    'property_taxes' => 1993,
    'hoa_fees' => 360, // Annual
    'tax_id' => '331-05.00-307.00',
    'price_per_sqft' => 244.23,
    
    // Construction & Systems
    'builder' => '',
    'property_style' => 'traditional',
    'condition' => 'excellent', // New construction
    'roof_type' => '',
    'foundation_type' => 'full_basement',
    'exterior_materials' => ['mixed'],
    'flooring_types' => ['carpet'],
    'heating_system' => 'forced_air',
    'heating_fuel' => 'electric',
    'cooling_system' => 'central_air',
    'cooling_fuel' => 'electric',
    'water_source' => 'city',
    'sewer_system' => 'city_sewer',
    'electric_service' => '',
    'hot_water' => 'electric_tank',
    
    // Location details
    'school_district' => 'Seaford',
    'zoning' => 'TN',
    'subdivision' => 'Mearfield',
    'flood_zone' => 'no',
    
    // Dates
    'listing_date' => '2025-02-18',
    'days_on_market' => 19,
    
    // Description
    'description' => 'Motivated Sellers! Welcome to this stunning 4-bedroom, 2.5-bath home in the desirable Mearfield community, located within Seaford city limits. This spacious home offers an inviting layout, featuring a large living room that seamlessly flows into a cozy living area, perfect for entertaining or relaxing. The open-concept kitchen is filled with natural light and equipped with modern appliances, ample cabinetry, and generous counter space, making meal prep effortless. Upstairs, the large bedrooms provide plenty of space for rest and relaxation, while the oversized primary suite features a private bath and a walk-in closet. The fully finished basement, complete with a bedroom, adds even more versatile living space—ideal for a recreation room, home office, or gym. Outside, enjoy the 0.17-acre lot, offering a great yard for outdoor activities and a new deck for relaxing or entertaining. Mearfield residents benefit from community amenities, including a swimming pool and sidewalks throughout the neighborhood. Conveniently located near shopping, dining, parks, and Hooper\'s Landing Golf Course, this home offers both comfort and convenience in a prime location.',
    
    // Special Features
    'inclusions' => 'Range with oven, range hood-exhaust fan, kitchen refrigerator, dishwasher, disposal, microwave, washer, dryer, water filter, water heater, sump pump, storm windows/doors, screens, draperies/curtains, drapery/curtain roods, shades/blinds, smoke detectors, carbon monoxide detectors, garage openers with remotes, irrigation system',
    'exclusions' => '',
];

// Usage example for second listing:
if (isset($_GET['create_seaford_listing']) && current_user_can('edit_posts')) {
    $new_listing_id = create_listing_from_mls_data($listing_data_2);
    
    if ($new_listing_id) {
        echo "Successfully created Seaford listing with ID: " . $new_listing_id . "\n";
        echo "Listing URL: " . get_permalink($new_listing_id) . "\n";
        echo "Edit URL: " . admin_url('post.php?post=' . $new_listing_id . '&action=edit') . "\n";
    } else {
        echo "Failed to create Seaford listing.\n";
    }
}

// Usage example:
if (isset($_GET['create_sample_listing']) && current_user_can('edit_posts')) {
    $new_listing_id = create_listing_from_mls_data($listing_data);
    
    if ($new_listing_id) {
        echo "Successfully created listing with ID: " . $new_listing_id . "\n";
        echo "Listing URL: " . get_permalink($new_listing_id) . "\n";
        echo "Edit URL: " . admin_url('post.php?post=' . $new_listing_id . '&action=edit') . "\n";
    } else {
        echo "Failed to create listing.\n";
    }
}

// Also create a simplified function for quick listing creation
function quick_create_listing($address, $city, $state, $zip, $price, $beds, $baths, $sqft, $mls = '') {
    // Parse address
    $address_parts = explode(' ', $address);
    $street_number = array_shift($address_parts);
    $street_type = array_pop($address_parts);
    $street_name = implode(' ', $address_parts);
    
    $quick_data = [
        'title' => $address,
        'status' => 'publish',
        'post_type' => 'listing',
        'street_number' => $street_number,
        'street_name' => $street_name,
        'street_type' => $street_type,
        'city' => $city,
        'state' => $state,
        'zip_code' => $zip,
        'listing_price' => $price,
        'bedrooms' => $beds,
        'bathrooms_full' => $baths,
        'square_feet' => $sqft,
        'mls_number' => $mls,
        'property_status' => 'active',
        'property_type' => 'single-family',
        'description' => 'Beautiful home at ' . $address . ' in ' . $city . ', ' . $state . '.'
    ];
    
    return create_listing_from_mls_data($quick_data);
}

?>
