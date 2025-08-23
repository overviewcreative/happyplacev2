<?php
/**
 * Airtable Sync Manager
 * Handles two-way synchronization between WordPress and Airtable
 *
 * @package HappyPlace
 */

namespace HappyPlace\Integrations;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

class Airtable_Sync_Manager {

    private static $instance = null;
    private $api_key;
    private $base_id;
    private $field_mappers = [];
    private $sync_log = [];

    // Airtable table names for each post type
    private $table_mapping = [
        'listing' => 'Listings',
        'agent' => 'Agents',
        'community' => 'Communities',
        'open_house' => 'Open Houses'
    ];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->init_hooks();
        $this->load_settings();
        $this->register_field_mappers();
    }

    private function init_hooks() {
        // Admin menu - use admin_menu hook with proper priority
        add_action('admin_menu', [$this, 'add_admin_menu'], 99);
        
        // AJAX handlers
        add_action('wp_ajax_hpt_airtable_sync_from_airtable', [$this, 'handle_sync_from_airtable']);
        add_action('wp_ajax_hpt_airtable_sync_to_airtable', [$this, 'handle_sync_to_airtable']);
        add_action('wp_ajax_hpt_airtable_test_connection', [$this, 'handle_test_connection']);
        add_action('wp_ajax_hpt_airtable_validate_fields', [$this, 'handle_validate_fields']);
        
        // Save post hooks for automatic sync
        add_action('save_post', [$this, 'maybe_sync_post_to_airtable'], 10, 2);
    }

    private function load_settings() {
        // For now, hardcode the credentials for testing
        $this->api_key = 'patM4ULeXoGRmKns6.d9be29a96ded648326504aef076c15d0c29d9ed05f81155f20a2fb90a7587dc2';
        $this->base_id = 'appL1Nh4fM4HQU13e';
    }

    private function register_field_mappers() {
        // For now, use simple field mapping arrays instead of complex classes
        $this->field_mappers = [
            'listing' => $this->get_simple_field_mapper('listing'),
            'agent' => $this->get_simple_field_mapper('agent'),
            'community' => $this->get_simple_field_mapper('community'),
            'open_house' => $this->get_simple_field_mapper('open_house'),
        ];
    }

    private function get_simple_field_mapper($post_type) {
        if ($post_type === 'listing') {
            return $this->get_listing_field_mapper();
        }
        
        if ($post_type === 'agent') {
            return $this->get_agent_field_mapper();
        }
        
        // Return a simple object with basic field mapping methods for other post types
        return new class($post_type) {
            private $post_type;
            
            public function __construct($post_type) {
                $this->post_type = $post_type;
            }
            
            public function airtable_to_wordpress($record) {
                $fields = $record['fields'] ?? [];
                
                return [
                    'post_title' => $fields['Name'] ?? $fields['Title'] ?? 'Untitled',
                    'post_content' => $fields['Description'] ?? $fields['Content'] ?? '',
                    'post_status' => 'publish',
                    'post_type' => $this->post_type
                ];
            }
            
            public function wordpress_to_airtable($post) {
                return [
                    'Name' => $post->post_title,
                    'Description' => $post->post_content,
                    'WordPress ID' => $post->ID,
                    'Status' => $post->post_status
                ];
            }
            
            public function get_field_mapping() {
                return [
                    'post_title' => 'Name',
                    'post_content' => 'Description',
                    'ID' => 'WordPress ID',
                    'post_status' => 'Status'
                ];
            }
        };
    }
    
    /**
     * Get comprehensive listing field mapper
     */
    private function get_listing_field_mapper() {
        return new class() {
            
            public function airtable_to_wordpress($record) {
                $fields = $record['fields'] ?? [];
                
                // Basic post data
                $post_data = [
                    'post_title' => $this->build_title_from_fields($fields),
                    'post_content' => $fields['Full Description'] ?? '',
                    'post_excerpt' => $fields['Short Description'] ?? '',
                    'post_status' => 'publish',
                    'post_type' => 'listing'
                ];
                
                return $post_data;
            }
            
            public function wordpress_to_airtable($post) {
                $airtable_data = [];
                
                // Basic post fields
                $airtable_data['Property Address'] = $this->get_full_address($post->ID);
                $airtable_data['Short Description'] = $post->post_excerpt ?? get_field('short_description', $post->ID);
                $airtable_data['Full Description'] = $post->post_content ?? get_field('full_description', $post->ID);
                $airtable_data['WordPress ID'] = $post->ID;
                $airtable_data['Post Status'] = $post->post_status;
                
                // Basic property information
                $airtable_data['Price'] = (float) get_field('listing_price', $post->ID);
                $airtable_data['MLS Number'] = get_field('mls_number', $post->ID);
                $airtable_data['Property Type'] = get_field('property_type', $post->ID);
                $airtable_data['Listing Date'] = get_field('listing_date', $post->ID);
                $airtable_data['Featured Listing'] = get_field('featured_listing', $post->ID) ? 'Yes' : 'No';
                
                // Property specifications
                $airtable_data['Bedrooms'] = (int) get_field('bedrooms', $post->ID);
                $airtable_data['Full Bathrooms'] = (int) get_field('full_bathrooms', $post->ID);
                $airtable_data['Half Bathrooms'] = (int) get_field('half_bathrooms', $post->ID);
                $airtable_data['Bathroom Display'] = \HappyPlace\Core\Listing_Automation::get_formatted_bathrooms($post->ID);
                $airtable_data['Square Feet'] = (int) get_field('square_feet', $post->ID);
                $airtable_data['Lot Size (Acres)'] = (float) get_field('lot_size_acres', $post->ID);
                $airtable_data['Year Built'] = (int) get_field('year_built', $post->ID);
                $airtable_data['Garage Spaces'] = (int) get_field('garage_spaces', $post->ID);
                
                // Address components
                $airtable_data['Street Number'] = get_field('street_number', $post->ID);
                $airtable_data['Street Name'] = get_field('street_name', $post->ID);
                $airtable_data['Street Suffix'] = get_field('street_suffix', $post->ID);
                $airtable_data['Unit Number'] = get_field('unit_number', $post->ID);
                $airtable_data['City'] = get_field('city', $post->ID);
                $airtable_data['State'] = get_field('state', $post->ID);
                $airtable_data['ZIP Code'] = get_field('zip_code', $post->ID);
                $airtable_data['County'] = get_field('county', $post->ID);
                
                // Property status from taxonomy
                $status_terms = get_the_terms($post->ID, 'property_status');
                $airtable_data['Listing Status'] = $status_terms && !is_wp_error($status_terms) ? $status_terms[0]->name : '';
                
                // Features (convert arrays to comma-separated strings)
                $interior_features = get_field('interior_features', $post->ID);
                $airtable_data['Interior Features'] = is_array($interior_features) ? implode(', ', $interior_features) : '';
                
                $exterior_features = get_field('exterior_features', $post->ID);
                $airtable_data['Exterior Features'] = is_array($exterior_features) ? implode(', ', $exterior_features) : '';
                
                $property_features = get_field('property_features', $post->ID);
                $airtable_data['Property Features'] = is_array($property_features) ? implode(', ', $property_features) : '';
                
                // Special amenities
                $airtable_data['Has Pool'] = get_field('has_pool', $post->ID) ? 'Yes' : 'No';
                $airtable_data['Pool Type'] = get_field('pool_type', $post->ID);
                $airtable_data['Has Spa'] = get_field('has_spa', $post->ID) ? 'Yes' : 'No';
                $airtable_data['Garage Type'] = get_field('garage_type', $post->ID);
                $airtable_data['Air Conditioning'] = get_field('air_conditioning', $post->ID);
                $airtable_data['Heating System'] = get_field('heating', $post->ID);
                $airtable_data['Water Heater'] = get_field('water_heater', $post->ID);
                
                // Financial information
                $airtable_data['HOA Fees'] = (float) get_field('hoa_fees', $post->ID);
                $airtable_data['Property Taxes'] = (float) get_field('property_taxes', $post->ID);
                $airtable_data['Price Per Sq Ft'] = (float) get_field('price_per_sqft', $post->ID);
                
                // Media information
                $airtable_data['Virtual Tour URL'] = get_field('virtual_tour_url', $post->ID);
                $airtable_data['Video Tour URL'] = get_field('video_tour_url', $post->ID);
                
                // Gallery image count
                $gallery_images = get_field('gallery_images', $post->ID);
                $airtable_data['Image Count'] = is_array($gallery_images) ? count($gallery_images) : 0;
                
                // Remove empty values
                return array_filter($airtable_data, function($value) {
                    return $value !== '' && $value !== null && $value !== 0;
                });
            }
            
            public function sync_acf_fields_from_airtable($post_id, $record) {
                $fields = $record['fields'] ?? [];
                
                // Helper function to safely update field with type validation
                $safe_update = function($field_key, $value, $post_id, $type = 'string') use (&$fields) {
                    // Skip if value is null, empty string (for non-boolean), or not set
                    if ($type !== 'boolean' && ($value === null || $value === '')) {
                        return;
                    }
                    
                    try {
                        // Type casting and validation based on field type
                        switch ($type) {
                            case 'int':
                                if (is_numeric($value)) {
                                    $value = (int) $value;
                                } else {
                                    hp_log("Invalid integer value for field {$field_key}: {$value}", 'warning', 'AIRTABLE_SYNC');
                                    return;
                                }
                                break;
                            case 'float':
                                if (is_numeric($value)) {
                                    $value = (float) $value;
                                } else {
                                    hp_log("Invalid float value for field {$field_key}: {$value}", 'warning', 'AIRTABLE_SYNC');
                                    return;
                                }
                                break;
                            case 'boolean':
                                // Handle Airtable checkbox behavior (true or undefined)
                                if ($value === true || $value === 'Yes' || $value === 'yes' || $value === 1 || $value === '1') {
                                    $value = true;
                                } else {
                                    $value = false;
                                }
                                break;
                            case 'array':
                                if (is_array($value)) {
                                    // Already an array from Multiple Select field
                                    $value = array_filter($value);
                                } elseif (is_string($value) && $value !== '') {
                                    // Convert comma-separated string to array
                                    $value = array_filter(array_map('trim', explode(',', $value)));
                                } else {
                                    return; // Skip empty arrays
                                }
                                
                                if (empty($value)) {
                                    return;
                                }
                                break;
                            case 'url':
                                if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                                    hp_log("Invalid URL for field {$field_key}: {$value}", 'warning', 'AIRTABLE_SYNC');
                                    return;
                                }
                                break;
                            case 'email':
                                if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                    hp_log("Invalid email for field {$field_key}: {$value}", 'warning', 'AIRTABLE_SYNC');
                                    return;
                                }
                                break;
                            case 'date':
                                // Validate date format (should be YYYY-MM-DD from Airtable)
                                if ($value !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                                    hp_log("Invalid date format for field {$field_key}: {$value}. Expected YYYY-MM-DD", 'warning', 'AIRTABLE_SYNC');
                                    return;
                                }
                                break;
                        }
                        
                        update_field($field_key, $value, $post_id);
                        
                    } catch (Exception $e) {
                        hp_log("Error updating field {$field_key}: " . $e->getMessage(), 'error', 'AIRTABLE_SYNC');
                    }
                };
                
                // Basic information
                if (isset($fields['Price']) && $fields['Price'] !== '') {
                    $safe_update('price', $fields['Price'], $post_id, 'float');
                }
                
                if (isset($fields['Short Description']) && $fields['Short Description'] !== '') {
                    $safe_update('short_description', $fields['Short Description'], $post_id);
                }
                
                if (isset($fields['Full Description']) && $fields['Full Description'] !== '') {
                    $safe_update('full_description', $fields['Full Description'], $post_id);
                }
                
                if (isset($fields['MLS Number']) && $fields['MLS Number'] !== '') {
                    $safe_update('mls_number', $fields['MLS Number'], $post_id);
                }
                
                if (isset($fields['Property Type']) && $fields['Property Type'] !== '') {
                    $safe_update('property_type', $fields['Property Type'], $post_id);
                }
                
                if (isset($fields['Listing Date']) && $fields['Listing Date'] !== '') {
                    $safe_update('listing_date', $fields['Listing Date'], $post_id, 'date');
                }
                
                if (isset($fields['Featured Listing'])) {
                    $safe_update('featured_listing', $fields['Featured Listing'], $post_id, 'boolean');
                }
                
                // Property specifications
                if (isset($fields['Bedrooms']) && $fields['Bedrooms'] !== '') {
                    $safe_update('bedrooms', $fields['Bedrooms'], $post_id, 'int');
                }
                
                if (isset($fields['Full Bathrooms']) && $fields['Full Bathrooms'] !== '') {
                    $safe_update('full_bathrooms', $fields['Full Bathrooms'], $post_id, 'int');
                }
                
                if (isset($fields['Half Bathrooms']) && $fields['Half Bathrooms'] !== '') {
                    $safe_update('half_bathrooms', $fields['Half Bathrooms'], $post_id, 'int');
                }
                
                if (isset($fields['Square Feet']) && $fields['Square Feet'] !== '') {
                    $safe_update('square_feet', $fields['Square Feet'], $post_id, 'int');
                }
                
                if (isset($fields['Lot Size (Acres)']) && $fields['Lot Size (Acres)'] !== '') {
                    $safe_update('lot_size_acres', $fields['Lot Size (Acres)'], $post_id, 'float');
                }
                
                if (isset($fields['Year Built']) && $fields['Year Built'] !== '') {
                    $safe_update('year_built', $fields['Year Built'], $post_id, 'int');
                }
                
                if (isset($fields['Garage Spaces']) && $fields['Garage Spaces'] !== '') {
                    $safe_update('garage_spaces', $fields['Garage Spaces'], $post_id, 'int');
                }
                
                // Address components
                if (isset($fields['Street Number'])) {
                    update_field('street_number', $fields['Street Number'], $post_id);
                }
                
                if (isset($fields['Street Name'])) {
                    update_field('street_name', $fields['Street Name'], $post_id);
                }
                
                if (isset($fields['Street Suffix'])) {
                    update_field('street_suffix', $fields['Street Suffix'], $post_id);
                }
                
                if (isset($fields['Unit Number'])) {
                    update_field('unit_number', $fields['Unit Number'], $post_id);
                }
                
                if (isset($fields['City'])) {
                    update_field('city', $fields['City'], $post_id);
                }
                
                if (isset($fields['State'])) {
                    update_field('state', $fields['State'], $post_id);
                }
                
                if (isset($fields['ZIP Code'])) {
                    update_field('zip_code', $fields['ZIP Code'], $post_id);
                }
                
                // Property status - sync to taxonomy
                if (isset($fields['Listing Status'])) {
                    $this->sync_property_status($post_id, $fields['Listing Status']);
                }
                
                // Features (convert comma-separated strings to arrays)
                if (isset($fields['Interior Features']) && $fields['Interior Features'] !== '') {
                    $safe_update('interior_features', $fields['Interior Features'], $post_id, 'array');
                }
                
                if (isset($fields['Exterior Features']) && $fields['Exterior Features'] !== '') {
                    $safe_update('exterior_features', $fields['Exterior Features'], $post_id, 'array');
                }
                
                if (isset($fields['Property Features']) && $fields['Property Features'] !== '') {
                    $safe_update('property_features', $fields['Property Features'], $post_id, 'array');
                }
                
                // Special amenities
                if (isset($fields['Has Pool'])) {
                    update_field('has_pool', $fields['Has Pool'] === 'Yes', $post_id);
                }
                
                if (isset($fields['Pool Type'])) {
                    update_field('pool_type', $fields['Pool Type'], $post_id);
                }
                
                if (isset($fields['Has Spa'])) {
                    update_field('has_spa', $fields['Has Spa'] === 'Yes', $post_id);
                }
                
                if (isset($fields['Garage Type'])) {
                    update_field('garage_type', $fields['Garage Type'], $post_id);
                }
                
                if (isset($fields['Air Conditioning'])) {
                    update_field('air_conditioning', $fields['Air Conditioning'], $post_id);
                }
                
                if (isset($fields['Heating System'])) {
                    update_field('heating', $fields['Heating System'], $post_id);
                }
                
                if (isset($fields['Water Heater'])) {
                    update_field('water_heater', $fields['Water Heater'], $post_id);
                }
                
                // Financial information
                if (isset($fields['HOA Fees'])) {
                    update_field('hoa_fees', (float) $fields['HOA Fees'], $post_id);
                }
                
                if (isset($fields['Property Taxes'])) {
                    update_field('property_taxes', (float) $fields['Property Taxes'], $post_id);
                }
                
                // Media information
                if (isset($fields['Virtual Tour URL']) && $fields['Virtual Tour URL'] !== '') {
                    $safe_update('virtual_tour_url', $fields['Virtual Tour URL'], $post_id, 'url');
                }
                
                if (isset($fields['Video Tour URL']) && $fields['Video Tour URL'] !== '') {
                    $safe_update('video_tour_url', $fields['Video Tour URL'], $post_id, 'url');
                }
            }
            
            private function build_title_from_fields($fields) {
                $title_parts = [];
                
                // Build from address components if available
                if (!empty($fields['Street Number']) && !empty($fields['Street Name'])) {
                    $address_parts = [
                        $fields['Street Number'],
                        $fields['Street Name'],
                        $fields['Street Suffix'] ?? ''
                    ];
                    $street_address = trim(implode(' ', array_filter($address_parts)));
                    $title_parts[] = $street_address;
                    
                    if (!empty($fields['City']) && !empty($fields['State'])) {
                        $title_parts[] = $fields['City'] . ', ' . $fields['State'];
                    } elseif (!empty($fields['City'])) {
                        $title_parts[] = $fields['City'];
                    }
                } else {
                    // Fallback to Property Address field or Name
                    $title_parts[] = $fields['Property Address'] ?? $fields['Name'] ?? 'Property Listing';
                }
                
                return implode(' - ', array_filter($title_parts));
            }
            
            private function get_full_address($post_id) {
                $address_parts = [
                    get_field('street_number', $post_id),
                    get_field('street_name', $post_id),
                    get_field('street_suffix', $post_id)
                ];
                
                $street_address = trim(implode(' ', array_filter($address_parts)));
                
                if (!empty(get_field('unit_number', $post_id))) {
                    $street_address .= ', ' . get_field('unit_number', $post_id);
                }
                
                $city = get_field('city', $post_id);
                $state = get_field('state', $post_id);
                $zip = get_field('zip_code', $post_id);
                
                $full_parts = [$street_address];
                
                if ($city && $state) {
                    $full_parts[] = $city . ', ' . $state;
                    if ($zip) {
                        $full_parts[] = $zip;
                    }
                }
                
                return implode(' ', array_filter($full_parts));
            }
            
            private function sync_property_status($post_id, $status_name) {
                // Find or create the status term
                $term = get_term_by('name', $status_name, 'property_status');
                
                if (!$term) {
                    // Create the term if it doesn't exist
                    $slug = sanitize_title($status_name);
                    $result = wp_insert_term($status_name, 'property_status', ['slug' => $slug]);
                    if (!is_wp_error($result)) {
                        $term_id = $result['term_id'];
                    }
                } else {
                    $term_id = $term->term_id;
                }
                
                if (isset($term_id)) {
                    wp_set_object_terms($post_id, $term_id, 'property_status');
                }
            }
            
            public function get_field_mapping() {
                return [
                    // Basic Information
                    'post_title' => 'Property Address',
                    'post_excerpt' => 'Short Description',
                    'post_content' => 'Full Description',
                    'ID' => 'WordPress ID',
                    'post_status' => 'Post Status',
                    
                    // Core Property Data
                    'price' => 'Price',
                    'mls_number' => 'MLS Number',
                    'property_type' => 'Property Type',
                    'listing_date' => 'Listing Date',
                    'featured_listing' => 'Featured Listing',
                    
                    // Property Specifications
                    'bedrooms' => 'Bedrooms',
                    'full_bathrooms' => 'Full Bathrooms',
                    'half_bathrooms' => 'Half Bathrooms',
                    'bathroom_display' => 'Bathroom Display',
                    'square_feet' => 'Square Feet',
                    'lot_size_acres' => 'Lot Size (Acres)',
                    'year_built' => 'Year Built',
                    'garage_spaces' => 'Garage Spaces',
                    
                    // Address Components
                    'street_number' => 'Street Number',
                    'street_name' => 'Street Name',
                    'street_suffix' => 'Street Suffix',
                    'unit_number' => 'Unit Number',
                    'city' => 'City',
                    'state' => 'State',
                    'zip_code' => 'ZIP Code',
                    'county' => 'County',
                    
                    // Status & Features
                    'listing_status_taxonomy' => 'Listing Status',
                    'interior_features' => 'Interior Features',
                    'exterior_features' => 'Exterior Features',
                    'property_features' => 'Property Features',
                    
                    // Amenities
                    'has_pool' => 'Has Pool',
                    'pool_type' => 'Pool Type',
                    'has_spa' => 'Has Spa',
                    'garage_type' => 'Garage Type',
                    'air_conditioning' => 'Air Conditioning',
                    'heating' => 'Heating System',
                    'water_heater' => 'Water Heater',
                    
                    // Financial
                    'hoa_fees' => 'HOA Fees',
                    'property_taxes' => 'Property Taxes',
                    
                    // Media
                    'virtual_tour_url' => 'Virtual Tour URL',
                    'video_tour_url' => 'Video Tour URL',
                    'gallery_images_count' => 'Image Count'
                ];
            }
        };
    }

    /**
     * Get comprehensive agent field mapper
     */
    private function get_agent_field_mapper() {
        return new class() {
            
            public function airtable_to_wordpress($record) {
                $fields = $record['fields'] ?? [];
                
                // Basic post data - build agent name from components
                $post_data = [
                    'post_title' => $this->build_agent_name_from_fields($fields),
                    'post_content' => $fields['Full Bio'] ?? $fields['Bio'] ?? '',
                    'post_excerpt' => $fields['Short Bio'] ?? '',
                    'post_status' => 'publish',
                    'post_type' => 'agent'
                ];
                
                return $post_data;
            }
            
            public function sync_acf_fields($record, $post_id) {
                $fields = $record['fields'] ?? [];
                
                // Helper function to safely update field  
                $safe_update = function($field_key, $value, $post_id, $type = 'string') {
                    // Skip if value is null or empty string (except for booleans)
                    if ($type !== 'boolean' && ($value === null || $value === '')) {
                        return;
                    }
                    
                    // Type casting based on field type
                    switch ($type) {
                        case 'int':
                            $value = (int) $value;
                            break;
                        case 'float':
                            $value = (float) $value;
                            break;
                        case 'boolean':
                            $value = in_array($value, ['Yes', 'yes', true, 1, '1'], true);
                            break;
                        case 'array':
                            if (is_string($value)) {
                                $value = array_filter(array_map('trim', explode(',', $value)));
                            }
                            if (empty($value)) {
                                return;
                            }
                            break;
                    }
                    
                    update_field($field_key, $value, $post_id);
                };
                
                // Basic Information
                if (isset($fields['First Name']) && $fields['First Name'] !== '') {
                    $safe_update('first_name', $fields['First Name'], $post_id);
                }
                
                if (isset($fields['Middle Name']) && $fields['Middle Name'] !== '') {
                    $safe_update('middle_name', $fields['Middle Name'], $post_id);
                }
                
                if (isset($fields['Last Name']) && $fields['Last Name'] !== '') {
                    $safe_update('last_name', $fields['Last Name'], $post_id);
                }
                
                if (isset($fields['Display Name'])) {
                    update_field('display_name', $fields['Display Name'], $post_id);
                }
                
                if (isset($fields['Suffix'])) {
                    update_field('suffix', $fields['Suffix'], $post_id);
                }
                
                if (isset($fields['Short Bio'])) {
                    update_field('short_bio', $fields['Short Bio'], $post_id);
                }
                
                if (isset($fields['Full Bio'])) {
                    update_field('full_bio', $fields['Full Bio'], $post_id);
                }
                
                // Professional Details
                if (isset($fields['Title'])) {
                    update_field('title', $fields['Title'], $post_id);
                }
                
                if (isset($fields['License Number'])) {
                    update_field('license_number', $fields['License Number'], $post_id);
                }
                
                if (isset($fields['License State'])) {
                    update_field('license_state', $fields['License State'], $post_id);
                }
                
                if (isset($fields['License Expiration'])) {
                    update_field('license_expiration', $fields['License Expiration'], $post_id);
                }
                
                if (isset($fields['Years Experience'])) {
                    update_field('years_experience', (int)$fields['Years Experience'], $post_id);
                }
                
                if (isset($fields['Date Started'])) {
                    update_field('date_started', $fields['Date Started'], $post_id);
                }
                
                if (isset($fields['Specialties'])) {
                    $specialties = is_array($fields['Specialties']) ? $fields['Specialties'] : explode(',', $fields['Specialties']);
                    update_field('specialties', array_map('trim', $specialties), $post_id);
                }
                
                if (isset($fields['Languages'])) {
                    $languages = is_array($fields['Languages']) ? $fields['Languages'] : explode(',', $fields['Languages']);
                    update_field('languages', array_map('trim', $languages), $post_id);
                }
                
                // Contact Information
                if (isset($fields['Email'])) {
                    update_field('email', $fields['Email'], $post_id);
                }
                
                if (isset($fields['Phone'])) {
                    update_field('phone', $fields['Phone'], $post_id);
                }
                
                if (isset($fields['Mobile Phone'])) {
                    update_field('mobile_phone', $fields['Mobile Phone'], $post_id);
                }
                
                if (isset($fields['Office Phone'])) {
                    update_field('office_phone', $fields['Office Phone'], $post_id);
                }
                
                if (isset($fields['Fax'])) {
                    update_field('fax', $fields['Fax'], $post_id);
                }
                
                if (isset($fields['Website URL'])) {
                    update_field('website_url', $fields['Website URL'], $post_id);
                }
                
                // Social Media
                if (isset($fields['Facebook URL'])) {
                    update_field('facebook_url', $fields['Facebook URL'], $post_id);
                }
                
                if (isset($fields['Instagram URL'])) {
                    update_field('instagram_url', $fields['Instagram URL'], $post_id);
                }
                
                if (isset($fields['LinkedIn URL'])) {
                    update_field('linkedin_url', $fields['LinkedIn URL'], $post_id);
                }
                
                if (isset($fields['Twitter URL'])) {
                    update_field('twitter_url', $fields['Twitter URL'], $post_id);
                }
                
                if (isset($fields['YouTube URL'])) {
                    update_field('youtube_url', $fields['YouTube URL'], $post_id);
                }
                
                if (isset($fields['TikTok URL'])) {
                    update_field('tiktok_url', $fields['TikTok URL'], $post_id);
                }
                
                if (isset($fields['Zillow Profile URL'])) {
                    update_field('zillow_profile_url', $fields['Zillow Profile URL'], $post_id);
                }
                
                // Performance Metrics
                if (isset($fields['Total Sales Volume'])) {
                    update_field('total_sales_volume', (float)$fields['Total Sales Volume'], $post_id);
                }
                
                if (isset($fields['Total Transactions'])) {
                    update_field('total_transactions', (int)$fields['Total Transactions'], $post_id);
                }
                
                if (isset($fields['Average Sale Price'])) {
                    update_field('average_sale_price', (float)$fields['Average Sale Price'], $post_id);
                }
                
                if (isset($fields['Average DOM'])) {
                    update_field('average_dom', (int)$fields['Average DOM'], $post_id);
                }
                
                if (isset($fields['Active Listings'])) {
                    update_field('active_listings_count', (int)$fields['Active Listings'], $post_id);
                }
                
                if (isset($fields['Sold Listings'])) {
                    update_field('sold_listings_count', (int)$fields['Sold Listings'], $post_id);
                }
                
                if (isset($fields['Client Satisfaction'])) {
                    update_field('client_satisfaction_rating', (float)$fields['Client Satisfaction'], $post_id);
                }
                
                if (isset($fields['Lead Response Time'])) {
                    update_field('lead_response_time', (int)$fields['Lead Response Time'], $post_id);
                }
                
                // Team and Office Information  
                if (isset($fields['Office Name'])) {
                    update_field('office_name', $fields['Office Name'], $post_id);
                }
                
                if (isset($fields['Team Name'])) {
                    update_field('team_name', $fields['Team Name'], $post_id);
                }
                
                if (isset($fields['Team Role'])) {
                    update_field('team_role', $fields['Team Role'], $post_id);
                }
                
                if (isset($fields['Reports To'])) {
                    update_field('reports_to', $fields['Reports To'], $post_id);
                }
                
                // Handle certifications as repeater field
                if (isset($fields['Certifications']) && is_array($fields['Certifications'])) {
                    $certifications = [];
                    foreach ($fields['Certifications'] as $cert_data) {
                        if (is_string($cert_data)) {
                            // Simple string format
                            $certifications[] = [
                                'certification_name' => $cert_data,
                                'abbreviation' => '',
                                'issuing_organization' => '',
                                'date_earned' => '',
                                'expiration_date' => ''
                            ];
                        } elseif (is_array($cert_data)) {
                            // Full certification data
                            $certifications[] = [
                                'certification_name' => $cert_data['name'] ?? '',
                                'abbreviation' => $cert_data['abbreviation'] ?? '',
                                'issuing_organization' => $cert_data['organization'] ?? '',
                                'date_earned' => $cert_data['date_earned'] ?? '',
                                'expiration_date' => $cert_data['expiration'] ?? ''
                            ];
                        }
                    }
                    update_field('certifications', $certifications, $post_id);
                }
            }
            
            public function wordpress_to_airtable($post) {
                $agent_data = [
                    'Name' => $post->post_title,
                    'Full Bio' => $post->post_content,
                    'Short Bio' => $post->post_excerpt,
                    'WordPress ID' => $post->ID,
                    'Status' => $post->post_status
                ];
                
                // Get ACF fields
                $acf_fields = $this->get_acf_fields_for_airtable($post->ID);
                
                return array_merge($agent_data, $acf_fields);
            }
            
            private function get_acf_fields_for_airtable($post_id) {
                $airtable_fields = [];
                
                // Basic Information
                if ($first_name = get_field('first_name', $post_id)) {
                    $airtable_fields['First Name'] = $first_name;
                }
                
                if ($middle_name = get_field('middle_name', $post_id)) {
                    $airtable_fields['Middle Name'] = $middle_name;
                }
                
                if ($last_name = get_field('last_name', $post_id)) {
                    $airtable_fields['Last Name'] = $last_name;
                }
                
                if ($display_name = get_field('display_name', $post_id)) {
                    $airtable_fields['Display Name'] = $display_name;
                }
                
                if ($suffix = get_field('suffix', $post_id)) {
                    $airtable_fields['Suffix'] = $suffix;
                }
                
                if ($short_bio = get_field('short_bio', $post_id)) {
                    $airtable_fields['Short Bio'] = $short_bio;
                }
                
                // Professional Details
                if ($title = get_field('title', $post_id)) {
                    $airtable_fields['Title'] = $title;
                }
                
                if ($license_number = get_field('license_number', $post_id)) {
                    $airtable_fields['License Number'] = $license_number;
                }
                
                if ($license_state = get_field('license_state', $post_id)) {
                    $airtable_fields['License State'] = $license_state;
                }
                
                if ($license_expiration = get_field('license_expiration', $post_id)) {
                    $airtable_fields['License Expiration'] = $license_expiration;
                }
                
                if ($years_experience = get_field('years_experience', $post_id)) {
                    $airtable_fields['Years Experience'] = (int)$years_experience;
                }
                
                if ($date_started = get_field('date_started', $post_id)) {
                    $airtable_fields['Date Started'] = $date_started;
                }
                
                if ($specialties = get_field('specialties', $post_id)) {
                    $airtable_fields['Specialties'] = is_array($specialties) ? $specialties : [$specialties];
                }
                
                if ($languages = get_field('languages', $post_id)) {
                    $airtable_fields['Languages'] = is_array($languages) ? $languages : [$languages];
                }
                
                // Contact Information
                if ($email = get_field('email', $post_id)) {
                    $airtable_fields['Email'] = $email;
                }
                
                if ($phone = get_field('phone', $post_id)) {
                    $airtable_fields['Phone'] = $phone;
                }
                
                if ($mobile_phone = get_field('mobile_phone', $post_id)) {
                    $airtable_fields['Mobile Phone'] = $mobile_phone;
                }
                
                if ($office_phone = get_field('office_phone', $post_id)) {
                    $airtable_fields['Office Phone'] = $office_phone;
                }
                
                if ($website_url = get_field('website_url', $post_id)) {
                    $airtable_fields['Website URL'] = $website_url;
                }
                
                // Social Media
                if ($facebook_url = get_field('facebook_url', $post_id)) {
                    $airtable_fields['Facebook URL'] = $facebook_url;
                }
                
                if ($instagram_url = get_field('instagram_url', $post_id)) {
                    $airtable_fields['Instagram URL'] = $instagram_url;
                }
                
                if ($linkedin_url = get_field('linkedin_url', $post_id)) {
                    $airtable_fields['LinkedIn URL'] = $linkedin_url;
                }
                
                if ($twitter_url = get_field('twitter_url', $post_id)) {
                    $airtable_fields['Twitter URL'] = $twitter_url;
                }
                
                if ($youtube_url = get_field('youtube_url', $post_id)) {
                    $airtable_fields['YouTube URL'] = $youtube_url;
                }
                
                if ($zillow_profile_url = get_field('zillow_profile_url', $post_id)) {
                    $airtable_fields['Zillow Profile URL'] = $zillow_profile_url;
                }
                
                // Performance Metrics
                if ($total_sales_volume = get_field('total_sales_volume', $post_id)) {
                    $airtable_fields['Total Sales Volume'] = (float)$total_sales_volume;
                }
                
                if ($total_transactions = get_field('total_transactions', $post_id)) {
                    $airtable_fields['Total Transactions'] = (int)$total_transactions;
                }
                
                if ($average_sale_price = get_field('average_sale_price', $post_id)) {
                    $airtable_fields['Average Sale Price'] = (float)$average_sale_price;
                }
                
                if ($average_dom = get_field('average_dom', $post_id)) {
                    $airtable_fields['Average DOM'] = (int)$average_dom;
                }
                
                if ($active_listings_count = get_field('active_listings_count', $post_id)) {
                    $airtable_fields['Active Listings'] = (int)$active_listings_count;
                }
                
                if ($sold_listings_count = get_field('sold_listings_count', $post_id)) {
                    $airtable_fields['Sold Listings'] = (int)$sold_listings_count;
                }
                
                if ($client_satisfaction_rating = get_field('client_satisfaction_rating', $post_id)) {
                    $airtable_fields['Client Satisfaction'] = (float)$client_satisfaction_rating;
                }
                
                // Team Information
                if ($office_name = get_field('office_name', $post_id)) {
                    $airtable_fields['Office Name'] = $office_name;
                }
                
                if ($team_name = get_field('team_name', $post_id)) {
                    $airtable_fields['Team Name'] = $team_name;
                }
                
                if ($team_role = get_field('team_role', $post_id)) {
                    $airtable_fields['Team Role'] = $team_role;
                }
                
                // Handle certifications repeater field
                if ($certifications = get_field('certifications', $post_id)) {
                    $cert_data = [];
                    foreach ($certifications as $cert) {
                        $cert_data[] = [
                            'name' => $cert['certification_name'] ?? '',
                            'abbreviation' => $cert['abbreviation'] ?? '',
                            'organization' => $cert['issuing_organization'] ?? '',
                            'date_earned' => $cert['date_earned'] ?? '',
                            'expiration' => $cert['expiration_date'] ?? ''
                        ];
                    }
                    $airtable_fields['Certifications'] = $cert_data;
                }
                
                return $airtable_fields;
            }
            
            private function build_agent_name_from_fields($fields) {
                $name_parts = [];
                
                // Build agent name from components
                if (!empty($fields['First Name'])) {
                    $name_parts[] = $fields['First Name'];
                }
                
                if (!empty($fields['Middle Name'])) {
                    $name_parts[] = $fields['Middle Name'];
                }
                
                if (!empty($fields['Last Name'])) {
                    $name_parts[] = $fields['Last Name'];
                }
                
                if (!empty($fields['Suffix'])) {
                    $name_parts[] = $fields['Suffix'];
                }
                
                if (!empty($name_parts)) {
                    return implode(' ', $name_parts);
                }
                
                // Fallback options
                if (!empty($fields['Display Name'])) {
                    return $fields['Display Name'];
                }
                
                if (!empty($fields['Name'])) {
                    return $fields['Name'];
                }
                
                return 'Unnamed Agent';
            }
            
            public function get_field_mapping() {
                return [
                    // Basic Post Fields
                    'post_title' => 'Name',
                    'post_content' => 'Full Bio',
                    'post_excerpt' => 'Short Bio',
                    'ID' => 'WordPress ID',
                    'post_status' => 'Status',
                    
                    // Basic Information
                    'first_name' => 'First Name',
                    'middle_name' => 'Middle Name', 
                    'last_name' => 'Last Name',
                    'display_name' => 'Display Name',
                    'suffix' => 'Suffix',
                    'short_bio' => 'Short Bio',
                    'full_bio' => 'Full Bio',
                    
                    // Professional Details
                    'title' => 'Title',
                    'license_number' => 'License Number',
                    'license_state' => 'License State',
                    'license_expiration' => 'License Expiration',
                    'years_experience' => 'Years Experience',
                    'date_started' => 'Date Started',
                    'specialties' => 'Specialties',
                    'languages' => 'Languages',
                    
                    // Contact Information
                    'email' => 'Email',
                    'phone' => 'Phone',
                    'mobile_phone' => 'Mobile Phone',
                    'office_phone' => 'Office Phone',
                    'fax' => 'Fax',
                    'website_url' => 'Website URL',
                    
                    // Social Media
                    'facebook_url' => 'Facebook URL',
                    'instagram_url' => 'Instagram URL',
                    'linkedin_url' => 'LinkedIn URL', 
                    'twitter_url' => 'Twitter URL',
                    'youtube_url' => 'YouTube URL',
                    'tiktok_url' => 'TikTok URL',
                    'zillow_profile_url' => 'Zillow Profile URL',
                    
                    // Performance Metrics
                    'total_sales_volume' => 'Total Sales Volume',
                    'total_transactions' => 'Total Transactions',
                    'average_sale_price' => 'Average Sale Price',
                    'average_dom' => 'Average DOM',
                    'active_listings_count' => 'Active Listings',
                    'sold_listings_count' => 'Sold Listings',
                    'client_satisfaction_rating' => 'Client Satisfaction',
                    'lead_response_time' => 'Lead Response Time',
                    
                    // Team Information
                    'office_name' => 'Office Name',
                    'team_name' => 'Team Name',
                    'team_role' => 'Team Role',
                    'reports_to' => 'Reports To',
                    'certifications' => 'Certifications'
                ];
            }
        };
    }

    public function add_admin_menu() {
        // First try to add as submenu under Happy Place dashboard
        $parent_slug = 'happy-place-dashboard';
        
        // Check if parent menu exists, if not use tools menu as fallback
        if (!$this->menu_exists($parent_slug)) {
            $parent_slug = 'tools.php';
        }
        
        add_submenu_page(
            $parent_slug,
            __('Airtable Sync', 'happy-place'),
            __('Airtable Sync', 'happy-place'),
            'manage_options',
            'happy-place-airtable-sync',
            [$this, 'render_admin_page']
        );
    }
    
    private function menu_exists($slug) {
        global $menu;
        if (!is_array($menu)) return false;
        
        foreach ($menu as $item) {
            if (isset($item[2]) && $item[2] === $slug) {
                return true;
            }
        }
        return false;
    }

    // AJAX Handlers
    public function handle_sync_from_airtable() {
        check_ajax_referer('hpt_airtable_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $post_type = sanitize_text_field($_POST['post_type']);
        
        try {
            $result = $this->sync_from_airtable($post_type);
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function handle_sync_to_airtable() {
        check_ajax_referer('hpt_airtable_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $post_type = sanitize_text_field($_POST['post_type']);
        
        try {
            $result = $this->sync_to_airtable($post_type);
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function handle_test_connection() {
        check_ajax_referer('hpt_airtable_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        try {
            $result = $this->test_connection();
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    public function handle_validate_fields() {
        check_ajax_referer('hpt_airtable_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $table_name = sanitize_text_field($_POST['table_name'] ?? 'Listings');
        
        try {
            $result = $this->validate_field_types($table_name);
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    // Core Sync Methods
    public function sync_from_airtable($post_type) {
        if (!isset($this->table_mapping[$post_type])) {
            throw new Exception("Unknown post type: $post_type");
        }

        $table_name = $this->table_mapping[$post_type];
        $records = $this->fetch_airtable_records($table_name);
        
        $created_count = 0;
        $updated_count = 0;
        
        foreach ($records as $record) {
            $mapper = $this->field_mappers[$post_type];
            $post_data = $mapper->airtable_to_wordpress($record);
            
            // Check if post already exists
            $existing_post = $this->find_existing_post($post_type, $record['id']);
            
            if ($existing_post) {
                $post_data['ID'] = $existing_post->ID;
                wp_update_post($post_data);
                
                // Sync ACF fields for listings
                if ($post_type === 'listing' && method_exists($mapper, 'sync_acf_fields_from_airtable')) {
                    $mapper->sync_acf_fields_from_airtable($existing_post->ID, $record);
                }
                
                $updated_count++;
            } else {
                $post_id = wp_insert_post($post_data);
                if ($post_id && !is_wp_error($post_id)) {
                    update_post_meta($post_id, '_airtable_record_id', $record['id']);
                    
                    // Sync ACF fields for listings
                    if ($post_type === 'listing' && method_exists($mapper, 'sync_acf_fields_from_airtable')) {
                        $mapper->sync_acf_fields_from_airtable($post_id, $record);
                    }
                    
                    $created_count++;
                }
            }
        }
        
        return [
            'message' => "Synced $created_count new and $updated_count updated records from Airtable",
            'created' => $created_count,
            'updated' => $updated_count
        ];
    }

    public function sync_to_airtable($post_type) {
        if (!isset($this->table_mapping[$post_type])) {
            throw new Exception("Unknown post type: $post_type");
        }

        $table_name = $this->table_mapping[$post_type];
        $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => ['publish', 'private', 'draft']
        ]);
        
        $created_count = 0;
        $updated_count = 0;
        
        foreach ($posts as $post) {
            $mapper = $this->field_mappers[$post_type];
            $airtable_data = $mapper->wordpress_to_airtable($post);
            
            $airtable_record_id = get_post_meta($post->ID, '_airtable_record_id', true);
            
            if ($airtable_record_id) {
                // Update existing record
                $this->update_airtable_record($table_name, $airtable_record_id, $airtable_data);
                $updated_count++;
            } else {
                // Create new record
                $record = $this->create_airtable_record($table_name, $airtable_data);
                if ($record && isset($record['id'])) {
                    update_post_meta($post->ID, '_airtable_record_id', $record['id']);
                    $created_count++;
                }
            }
        }
        
        return [
            'message' => "Synced $created_count new and $updated_count updated records to Airtable",
            'created' => $created_count,
            'updated' => $updated_count
        ];
    }

    public function test_connection() {
        $url = "https://api.airtable.com/v0/{$this->base_id}/Listings";
        
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Connection failed: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            $error_data = json_decode($body, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'Unknown error';
            throw new Exception("Airtable API error (HTTP $status_code): $error_message");
        }

        return [
            'message' => 'Connection successful!',
            'status_code' => $status_code
        ];
    }
    
    /**
     * Validate field types by examining sample data from Airtable
     */
    public function validate_field_types($table_name = 'Listings') {
        try {
            $records = $this->fetch_airtable_records($table_name);
            
            if (empty($records)) {
                return [
                    'message' => 'No records found to validate field types',
                    'warnings' => []
                ];
            }
            
            $warnings = [];
            $sample_record = $records[0];
            $fields = $sample_record['fields'] ?? [];
            
            // Expected field types for listings
            $expected_types = [
                'Price' => 'number',
                'Bedrooms' => 'integer',
                'Full Bathrooms' => 'integer',
                'Half Bathrooms' => 'integer',
                'Square Feet' => 'integer',
                'Year Built' => 'integer',
                'Lot Size (Acres)' => 'number',
                'Featured Listing' => 'boolean',
                'Interior Features' => 'array',
                'Exterior Features' => 'array',
                'Property Features' => 'array',
                'Has Pool' => 'boolean',
                'Has Spa' => 'boolean',
                'Virtual Tour URL' => 'string',
                'Video Tour URL' => 'string',
                'Listing Date' => 'string',
                'ZIP Code' => 'string'
            ];
            
            foreach ($expected_types as $field_name => $expected_type) {
                if (!isset($fields[$field_name])) {
                    $warnings[] = "Missing field: {$field_name}";
                    continue;
                }
                
                $actual_value = $fields[$field_name];
                $actual_type = gettype($actual_value);
                
                $type_ok = false;
                
                switch ($expected_type) {
                    case 'number':
                    case 'integer':
                        $type_ok = is_numeric($actual_value);
                        if (!$type_ok) {
                            $warnings[] = "Field '{$field_name}' should be numeric but is {$actual_type}: " . var_export($actual_value, true);
                        }
                        break;
                        
                    case 'boolean':
                        $type_ok = is_bool($actual_value) || $actual_value === null;
                        if (!$type_ok) {
                            $warnings[] = "Field '{$field_name}' should be boolean (checkbox) but is {$actual_type}: " . var_export($actual_value, true) . ". Use Checkbox field type in Airtable.";
                        }
                        break;
                        
                    case 'array':
                        $type_ok = is_array($actual_value);
                        if (!$type_ok) {
                            $warnings[] = "Field '{$field_name}' should be array (Multiple Select) but is {$actual_type}: " . var_export($actual_value, true) . ". Use Multiple Select field type in Airtable.";
                        }
                        break;
                        
                    case 'string':
                        if ($field_name === 'ZIP Code' && is_numeric($actual_value)) {
                            $warnings[] = "Field '{$field_name}' should be text to preserve leading zeros but is numeric: " . var_export($actual_value, true) . ". Change to Single Line Text in Airtable.";
                        }
                        break;
                }
            }
            
            return [
                'message' => count($warnings) === 0 ? 'All field types look correct!' : 'Found ' . count($warnings) . ' field type issues',
                'warnings' => $warnings,
                'sample_fields' => array_keys($fields)
            ];
            
        } catch (Exception $e) {
            throw new Exception('Field validation failed: ' . $e->getMessage());
        }
    }

    // Helper Methods
    private function fetch_airtable_records($table_name) {
        $url = "https://api.airtable.com/v0/{$this->base_id}/" . urlencode($table_name);
        
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Failed to fetch records: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['records'])) {
            throw new Exception('Invalid response from Airtable');
        }

        return $data['records'];
    }

    private function find_existing_post($post_type, $airtable_record_id) {
        $posts = get_posts([
            'post_type' => $post_type,
            'meta_query' => [
                [
                    'key' => '_airtable_record_id',
                    'value' => $airtable_record_id,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1
        ]);

        return !empty($posts) ? $posts[0] : null;
    }

    private function create_airtable_record($table_name, $data) {
        $url = "https://api.airtable.com/v0/{$this->base_id}/" . urlencode($table_name);
        
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode(['fields' => $data]),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Failed to create record: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    private function update_airtable_record($table_name, $record_id, $data) {
        $url = "https://api.airtable.com/v0/{$this->base_id}/" . urlencode($table_name) . "/" . $record_id;
        
        $response = wp_remote_request($url, [
            'method' => 'PATCH',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode(['fields' => $data]),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Failed to update record: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public function maybe_sync_post_to_airtable($post_id, $post) {
        // Only sync certain post types
        if (!in_array($post->post_type, array_keys($this->table_mapping))) {
            return;
        }

        // Skip auto-sync if disabled
        if (get_option('hpt_airtable_auto_sync', false) === false) {
            return;
        }

        try {
            $this->sync_single_post_to_airtable($post);
        } catch (Exception $e) {
            error_log('Auto-sync to Airtable failed: ' . $e->getMessage());
        }
    }

    private function sync_single_post_to_airtable($post) {
        $table_name = $this->table_mapping[$post->post_type];
        $mapper = $this->field_mappers[$post->post_type];
        $airtable_data = $mapper->wordpress_to_airtable($post);
        
        $airtable_record_id = get_post_meta($post->ID, '_airtable_record_id', true);
        
        if ($airtable_record_id) {
            $this->update_airtable_record($table_name, $airtable_record_id, $airtable_data);
        } else {
            $record = $this->create_airtable_record($table_name, $airtable_data);
            if ($record && isset($record['id'])) {
                update_post_meta($post->ID, '_airtable_record_id', $record['id']);
            }
        }
    }

    public function render_admin_page() {
        $nonce = wp_create_nonce('hpt_airtable_nonce');
        ?>
        <div class="wrap">
            <h1><?php _e('Airtable Sync', 'happy-place'); ?></h1>
            
            <div class="hpt-airtable-container">
                <!-- Connection Status -->
                <div class="card">
                    <h2><?php _e('Connection Status', 'happy-place'); ?></h2>
                    <p>
                        <strong><?php _e('Base ID:', 'happy-place'); ?></strong> <?php echo esc_html($this->base_id); ?><br>
                        <strong><?php _e('API Key:', 'happy-place'); ?></strong> <?php echo esc_html(substr($this->api_key, 0, 10) . '...'); ?>
                    </p>
                    <p>
                        <button type="button" class="button button-secondary" onclick="testAirtableConnection()"><?php _e('Test Connection', 'happy-place'); ?></button>
                        <button type="button" class="button button-secondary" onclick="validateFieldTypes('Listings')" style="margin-left: 10px;"><?php _e('Validate Field Types', 'happy-place'); ?></button>
                    </p>
                </div>

                <!-- Sync Status Overview -->
                <div class="card">
                    <h2><?php _e('Sync Status Overview', 'happy-place'); ?></h2>
                    <div class="status-grid">
                        <?php foreach ($this->table_mapping as $post_type => $table_name): ?>
                            <div class="status-item">
                                <h3><?php echo esc_html(ucfirst($post_type)); ?></h3>
                                <div class="status-numbers">
                                    <span class="wp-count"><?php echo $this->get_wp_post_count($post_type); ?></span>
                                    <span class="divider">⟷</span>
                                    <span class="airtable-count">?</span>
                                </div>
                                <div class="last-sync">Last sync: Never</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Sync Actions -->
                <div class="card">
                    <h2><?php _e('Sync Actions', 'happy-place'); ?></h2>
                    <div class="sync-buttons">
                        <div class="sync-group">
                            <h3><?php _e('Sync FROM Airtable', 'happy-place'); ?></h3>
                            <p><?php _e('Import data from Airtable to WordPress', 'happy-place'); ?></p>
                            <?php foreach ($this->table_mapping as $post_type => $table_name): ?>
                                <a href="#" class="button button-primary" onclick="syncFromAirtable('<?php echo esc_js($post_type); ?>')"><?php echo sprintf(__('Sync %s FROM Airtable', 'happy-place'), ucfirst($post_type)); ?></a>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="sync-group">
                            <h3><?php _e('Sync TO Airtable', 'happy-place'); ?></h3>
                            <p><?php _e('Export data from WordPress to Airtable', 'happy-place'); ?></p>
                            <?php foreach ($this->table_mapping as $post_type => $table_name): ?>
                                <a href="#" class="button button-secondary" onclick="syncToAirtable('<?php echo esc_js($post_type); ?>')"><?php echo sprintf(__('Sync %s TO Airtable', 'happy-place'), ucfirst($post_type)); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Field Mapping -->
                <div class="card">
                    <h2><?php _e('Field Mapping', 'happy-place'); ?></h2>
                    <div class="mapping-tables">
                        <?php foreach ($this->table_mapping as $post_type => $table_name): ?>
                            <div class="mapping-table">
                                <h3><?php echo esc_html(ucfirst($post_type)); ?></h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th><?php _e('WordPress Field', 'happy-place'); ?></th>
                                            <th><?php _e('Airtable Field', 'happy-place'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (isset($this->field_mappers[$post_type])): ?>
                                            <?php foreach ($this->field_mappers[$post_type]->get_field_mapping() as $wp_field => $airtable_field): ?>
                                                <tr>
                                                    <td><?php echo esc_html($wp_field); ?></td>
                                                    <td><?php echo esc_html($airtable_field); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="2"><?php _e('Field mapper not available', 'happy-place'); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Sync Log -->
                <div class="hpt-sync-log card">
                    <h2><?php _e('Sync Log', 'happy-place'); ?></h2>
                    <div id="sync-log-content" class="log-content">
                        <p><?php _e('Sync operations will be logged here...', 'happy-place'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .hpt-airtable-container { margin-top: 20px; }
        .hpt-airtable-container .card { padding: 20px; margin-bottom: 20px; }
        .airtable-actions { margin-top: 20px; }
        .airtable-actions .button { margin-right: 10px; }
        .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .status-item { text-align: center; padding: 15px; background: #f9f9f9; border-radius: 4px; }
        .status-numbers { font-size: 18px; font-weight: bold; margin: 10px 0; }
        .wp-count { color: #0073aa; }
        .airtable-count { color: #f5a623; }
        .divider { margin: 0 10px; color: #ccc; }
        .last-sync { font-size: 12px; color: #666; }
        .sync-buttons { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .sync-group h3 { margin-bottom: 5px; }
        .sync-group p { margin-bottom: 15px; color: #666; }
        .sync-group .button { display: block; width: 100%; margin-bottom: 8px; }
        .mapping-tables { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
        .mapping-table { background: #f9f9f9; padding: 15px; border-radius: 4px; }
        .mapping-table table { width: 100%; border-collapse: collapse; }
        .mapping-table th, .mapping-table td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .mapping-table th { background: #fff; font-weight: 600; }
        .log-content { background: #f9f9f9; padding: 15px; border-radius: 4px; height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; }
        </style>
        
        <script type="text/javascript">
        function syncFromAirtable(postType) {
            if (typeof jQuery === 'undefined') {
                alert('jQuery not loaded! Please refresh the page.');
                return false;
            }
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'hpt_airtable_sync_from_airtable',
                    post_type: postType,
                    nonce: '<?php echo $nonce; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('✅ Sync completed successfully!\n\nDetails: ' + JSON.stringify(response.data, null, 2));
                    } else {
                        alert('❌ Sync failed: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('❌ Network error: ' + error);
                }
            });
            return false;
        }
        
        function syncToAirtable(postType) {
            if (typeof jQuery === 'undefined') {
                alert('jQuery not loaded! Please refresh the page.');
                return false;
            }
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'hpt_airtable_sync_to_airtable',
                    post_type: postType,
                    nonce: '<?php echo $nonce; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('✅ Sync completed successfully!\n\nDetails: ' + JSON.stringify(response.data, null, 2));
                    } else {
                        alert('❌ Sync failed: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('❌ Network error: ' + error);
                }
            });
            return false;
        }
        
        function testAirtableConnection() {
            if (typeof jQuery === 'undefined') {
                alert('jQuery not loaded! Please refresh the page.');
                return false;
            }
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'hpt_airtable_test_connection',
                    nonce: '<?php echo $nonce; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('✅ Connection test successful: ' + response.data.message);
                    } else {
                        alert('❌ Connection test failed: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('❌ Network error: ' + error);
                }
            });
            return false;
        }
        
        function validateFieldTypes(tableName) {
            if (typeof jQuery === 'undefined') {
                alert('jQuery not loaded! Please refresh the page.');
                return false;
            }
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'hpt_airtable_validate_fields',
                    table_name: tableName,
                    nonce: '<?php echo $nonce; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        let message = '✅ ' + response.data.message;
                        if (response.data.warnings && response.data.warnings.length > 0) {
                            message += '\\n\\n⚠️ Issues found:\\n' + response.data.warnings.join('\\n');
                        }
                        alert(message);
                    } else {
                        alert('❌ Field validation failed: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('❌ Network error: ' + error);
                }
            });
            return false;
        }
        </script>
        <?php
    }

    private function get_wp_post_count($post_type) {
        $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => ['publish', 'private', 'draft']
        ]);
        return count($posts);
    }
}
