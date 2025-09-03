<?php
/**
 * Create Test Listing Script
 * Run once to create a test listing if none exist
 */

// Bootstrap WordPress
require_once 'wp-config.php';
require_once 'wp-load.php';

// Check if we have any listings first
$existing_listings = get_posts([
    'post_type' => 'listing',
    'post_status' => 'publish',
    'posts_per_page' => 1
]);

if (!empty($existing_listings)) {
    echo "Listings already exist. No need to create test data.\n";
    exit;
}

// Create a test listing
$test_listing = array(
    'post_title' => 'Beautiful Test Home',
    'post_content' => 'This is a beautiful test home with all the amenities you need. Features include modern kitchen, spacious living areas, and a lovely backyard.',
    'post_status' => 'publish',
    'post_type' => 'listing',
    'post_author' => 1,
);

$listing_id = wp_insert_post($test_listing);

if (is_wp_error($listing_id)) {
    echo "Error creating listing: " . $listing_id->get_error_message() . "\n";
    exit;
}

echo "Created test listing with ID: " . $listing_id . "\n";

// Add some test meta data
update_field('price', 450000, $listing_id);
update_field('bedrooms', 3, $listing_id);
update_field('bathrooms_full', 2, $listing_id);
update_field('bathrooms_half', 1, $listing_id);
update_field('square_feet', 1800, $listing_id);
update_field('listing_status', 'active', $listing_id);
update_field('property_type', 'single_family', $listing_id);

// Add address fields
update_field('street_number', '123', $listing_id);
update_field('street_name', 'Test Street', $listing_id);
update_field('city', 'Test City', $listing_id);
update_field('state', 'TX', $listing_id);
update_field('zip_code', '12345', $listing_id);

echo "Added test meta data to listing.\n";

// Create a second test listing
$test_listing_2 = array(
    'post_title' => 'Luxury Condo Downtown',
    'post_content' => 'Modern luxury condominium in the heart of downtown. Amazing city views, high-end finishes, and premium location.',
    'post_status' => 'publish',
    'post_type' => 'listing',
    'post_author' => 1,
);

$listing_id_2 = wp_insert_post($test_listing_2);

if (!is_wp_error($listing_id_2)) {
    update_field('price', 675000, $listing_id_2);
    update_field('bedrooms', 2, $listing_id_2);
    update_field('bathrooms_full', 2, $listing_id_2);
    update_field('square_feet', 1200, $listing_id_2);
    update_field('listing_status', 'active', $listing_id_2);
    update_field('property_type', 'condo', $listing_id_2);
    
    update_field('street_number', '456', $listing_id_2);
    update_field('street_name', 'Downtown Ave', $listing_id_2);
    update_field('city', 'Test City', $listing_id_2);
    update_field('state', 'TX', $listing_id_2);
    update_field('zip_code', '12346', $listing_id_2);
    
    echo "Created second test listing with ID: " . $listing_id_2 . "\n";
}

echo "Test listings created successfully!\n";
echo "Visit /listings/ to view the archive.\n";
?>