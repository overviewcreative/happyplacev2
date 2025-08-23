<?php
/**
 * Field Mapper Class
 * 
 * Maps ACF field names to system field names and handles field synchronization
 *
 * @package HappyPlace\Core
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Field_Mapper {
    
    /**
     * Map ACF field names to system/bridge function field names
     */
    private static $field_map = [
        // Core fields - ACF name => System name
        'listing_price' => 'price',
        'bathrooms_full' => 'bathrooms_full',
        'bathrooms_half' => 'bathrooms_half',
        'is_featured' => 'featured_listing',
        'property_description' => 'description',
        'property_title' => 'title',
        'property_highlights' => 'highlights',
        'showing_instructions' => 'showing_instructions',
        'internal_notes' => 'internal_notes',
        
        // Keep these as-is (ACF name matches system name)
        'bedrooms' => 'bedrooms',
        'square_feet' => 'square_feet',
        'lot_size_acres' => 'lot_size_acres',
        'lot_size_sqft' => 'lot_size_sqft',
        'year_built' => 'year_built',
        'garage_spaces' => 'garage_spaces',
        'mls_number' => 'mls_number',
        'property_style' => 'property_style',
        'listing_date' => 'listing_date',
        
        // Address fields
        'street_address' => 'street_address',
        'city' => 'city',
        'state' => 'state',
        'zip_code' => 'zip_code',
        
        // Media fields
        'primary_photo' => 'featured_image',
        'photo_gallery' => 'gallery',
        'virtual_tour_url' => 'virtual_tour',
        'video_url' => 'video',
        'floor_plans' => 'floor_plans',
        
        // Features
        'interior_features' => 'interior_features',
        'exterior_features' => 'exterior_features',
        'property_features' => 'property_features',
        'has_pool' => 'has_pool',
        'pool_type' => 'pool_type',
        'has_spa' => 'has_spa',
        'garage_type' => 'garage_type',
        'air_conditioning' => 'air_conditioning',
        'heating' => 'heating',
        'water_heater' => 'water_heater',
        
        // Financial
        'property_taxes' => 'property_taxes',
        'hoa_fees' => 'hoa_fees',
        'buyer_commission' => 'commission',
        'estimated_insurance' => 'estimated_insurance',
        'estimated_utilities' => 'estimated_utilities',
        
        // Agent fields
        'listing_agent' => 'agent_id',
        'co_listing_agent' => 'co_agent_id',
        'listing_office' => 'office',
        'listing_office_phone' => 'office_phone'
    ];
    
    /**
     * Get system field name from ACF field name
     */
    public static function get_system_field($acf_field) {
        return self::$field_map[$acf_field] ?? $acf_field;
    }
    
    /**
     * Get ACF field name from system field name
     */
    public static function get_acf_field($system_field) {
        $flipped = array_flip(self::$field_map);
        return $flipped[$system_field] ?? $system_field;
    }
    
    /**
     * Calculate total bathrooms from full and half
     */
    public static function calculate_bathrooms($post_id) {
        $full = get_field('bathrooms_full', $post_id) ?: 0;
        $half = get_field('bathrooms_half', $post_id) ?: 0;
        $total = floatval($full) + (floatval($half) * 0.5);
        
        // Save calculated total for bridge functions
        update_post_meta($post_id, '_calculated_bathrooms', $total);
        update_post_meta($post_id, '_bathrooms_full', $full);
        update_post_meta($post_id, '_bathrooms_half', $half);
        
        hp_log("Calculated bathrooms for listing {$post_id}: {$total} (Full: {$full}, Half: {$half})", 'debug', 'FIELD_MAPPER');
        
        return $total;
    }
    
    /**
     * Sync ACF fields to expected meta keys for bridge functions
     */
    public static function sync_fields($post_id) {
        // Only for listing post type
        if (get_post_type($post_id) !== 'listing') {
            return;
        }
        
        hp_log("Starting field sync for listing {$post_id}", 'info', 'FIELD_MAPPER');
        
        // Sync featured status
        $is_featured = get_field('is_featured', $post_id);
        update_post_meta($post_id, '_featured_listing', $is_featured ? '1' : '0');
        
        // Calculate and save bathrooms
        self::calculate_bathrooms($post_id);
        
        // Sync price for queries and bridge functions
        $price = get_field('listing_price', $post_id);
        if ($price !== false) {
            update_post_meta($post_id, '_listing_price', $price);
            update_post_meta($post_id, 'price', $price); // Also save as 'price' for bridge functions
        }
        
        // Sync other important fields for bridge functions
        $sync_fields = [
            'bedrooms' => 'bedrooms',
            'square_feet' => 'square_feet',
            'lot_size_acres' => 'lot_size_acres',
            'year_built' => 'year_built',
            'garage_spaces' => 'garage_spaces',
            'mls_number' => 'mls_number',
            'property_style' => 'property_style',
            'street_address' => 'street_address',
            'city' => 'city',
            'state' => 'state',
            'zip_code' => 'zip_code'
        ];
        
        foreach ($sync_fields as $acf_field => $meta_key) {
            $value = get_field($acf_field, $post_id);
            if ($value !== false && $value !== null) {
                update_post_meta($post_id, $meta_key, $value);
                update_post_meta($post_id, '_' . $meta_key, $value); // Also with underscore prefix
            }
        }
        
        // Build full address for bridge functions
        $street = get_field('street_address', $post_id) ?: '';
        $city = get_field('city', $post_id) ?: '';
        $state = get_field('state', $post_id) ?: '';
        $zip = get_field('zip_code', $post_id) ?: '';
        
        if ($street || $city) {
            $full_address = trim("{$street}, {$city}, {$state} {$zip}", ', ');
            update_post_meta($post_id, '_full_address', $full_address);
        }
        
        // Sync property description
        $description = get_field('property_description', $post_id);
        if ($description) {
            update_post_meta($post_id, 'description', $description);
            update_post_meta($post_id, '_description', $description);
        }
        
        // Update post title if property_title is set
        $property_title = get_field('property_title', $post_id);
        if ($property_title) {
            // Update the post title
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $property_title
            ]);
        }
        
        hp_log("Field sync completed for listing {$post_id}", 'info', 'FIELD_MAPPER');
    }
    
    /**
     * Get all ACF field values for a listing
     */
    public static function get_listing_fields($post_id) {
        $fields = [];
        
        foreach (self::$field_map as $acf_field => $system_field) {
            $value = get_field($acf_field, $post_id);
            if ($value !== false && $value !== null) {
                $fields[$system_field] = $value;
            }
        }
        
        // Add calculated bathrooms
        $fields['bathrooms'] = self::calculate_bathrooms($post_id);
        
        return $fields;
    }
    
    /**
     * Update ACF field from system field name
     */
    public static function update_system_field($post_id, $system_field, $value) {
        $acf_field = self::get_acf_field($system_field);
        
        if (function_exists('update_field')) {
            $result = update_field($acf_field, $value, $post_id);
            
            // If it's bathrooms, we need to handle specially
            if ($system_field === 'bathrooms' && is_numeric($value)) {
                $full = floor($value);
                $half = ($value - $full) > 0 ? 1 : 0;
                update_field('bathrooms_full', $full, $post_id);
                update_field('bathrooms_half', $half, $post_id);
            }
            
            hp_log("Updated field {$acf_field} (system: {$system_field}) for post {$post_id}", 'debug', 'FIELD_MAPPER');
            
            return $result;
        }
        
        return false;
    }
}