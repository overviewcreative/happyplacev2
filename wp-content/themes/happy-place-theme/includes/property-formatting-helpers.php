<?php
/**
 * Property Formatting Helpers
 * 
 * Functions to format property values for consistent display
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Format property values for display
 * 
 * @param string $value Raw property value
 * @return string Formatted value ready for display
 */
function hph_format_property_value($value) {
    if (empty($value)) return '';
    
    // Handle specific value mappings
    $value_mappings = [
        // Property Types
        'single_family' => 'Single Family',
        'multi_family' => 'Multi Family', 
        'condo' => 'Condo',
        'condominium' => 'Condominium',
        'townhouse' => 'Townhouse',
        'townhome' => 'Townhome',
        'land' => 'Land',
        'mobile_home' => 'Mobile Home',
        'manufactured_home' => 'Manufactured Home',
        
        // Garage Types  
        'attached' => 'Attached',
        'detached' => 'Detached',
        'carport' => 'Carport',
        'none' => 'None',
        
        // Property Styles
        'traditional' => 'Traditional',
        'contemporary' => 'Contemporary',
        'colonial' => 'Colonial',
        'ranch' => 'Ranch',
        'cape_cod' => 'Cape Cod',
        'victorian' => 'Victorian',
        'craftsman' => 'Craftsman',
        'modern' => 'Modern',
        'tudor' => 'Tudor',
        'split_level' => 'Split Level',
        'split_foyer' => 'Split Foyer',
        'raised_ranch' => 'Raised Ranch',
        'log_home' => 'Log Home',
        'farmhouse' => 'Farmhouse',
        
        // Conditions
        'excellent' => 'Excellent',
        'good' => 'Good',
        'fair' => 'Fair',
        'poor' => 'Poor',
        'needs_work' => 'Needs Work',
        'new_construction' => 'New Construction',
        
        // Basement Types
        'full' => 'Full',
        'partial' => 'Partial',
        'crawl_space' => 'Crawl Space',
        'slab' => 'Slab',
        'walkout' => 'Walkout',
        'daylight' => 'Daylight',
        
        // Heating/Cooling
        'central_air' => 'Central Air',
        'heat_pump' => 'Heat Pump',
        'forced_air' => 'Forced Air',
        'radiant_heat' => 'Radiant Heat',
        'baseboard' => 'Baseboard',
        
        // Flooring
        'hardwood' => 'Hardwood',
        'laminate' => 'Laminate',
        'tile' => 'Tile',
        'carpet' => 'Carpet',
        'vinyl' => 'Vinyl',
        'concrete' => 'Concrete',
        'bamboo' => 'Bamboo'
    ];
    
    // Check for exact mapping first
    $lower_value = strtolower(trim($value));
    if (isset($value_mappings[$lower_value])) {
        return $value_mappings[$lower_value];
    }
    
    // Handle underscores, hyphens, and capitalize
    $formatted = str_replace(['_', '-'], ' ', $value);
    $formatted = ucwords(strtolower($formatted));
    
    return $formatted;
}

/**
 * Format garage display
 * 
 * @param mixed $spaces Number of garage spaces
 * @param string $type Type of garage (attached, detached, etc.)
 * @return string Formatted garage display
 */
function hph_format_garage_display($spaces, $type = '') {
    if (empty($spaces) || $spaces <= 0) {
        return '';
    }
    
    $spaces = intval($spaces);
    $display = $spaces . ' Car' . ($spaces > 1 ? 's' : '');
    
    if (!empty($type)) {
        $display .= ' ' . hph_format_property_value($type);
    }
    
    return $display;
}

/**
 * Format square footage
 * 
 * @param mixed $sqft Square footage value
 * @return string Formatted square footage with commas and "sq ft"
 */
function hph_format_square_feet($sqft) {
    if (empty($sqft) || !is_numeric($sqft)) {
        return '';
    }
    
    return number_format(intval($sqft)) . ' sq ft';
}

/**
 * Format lot size
 * 
 * @param mixed $size Lot size in acres or square feet
 * @param string $unit Unit type ('acres' or 'sqft')
 * @return string Formatted lot size
 */
function hph_format_lot_size($size, $unit = 'acres') {
    if (empty($size) || !is_numeric($size)) {
        return '';
    }
    
    $size = floatval($size);
    
    if ($unit === 'acres') {
        if ($size >= 1) {
            return number_format($size, 2) . ' acres';
        } else {
            // Convert to square feet for display if less than 1 acre
            $sqft = $size * 43560; // 1 acre = 43,560 sq ft
            return number_format($sqft) . ' sq ft';
        }
    } else {
        return number_format($size) . ' sq ft';
    }
}

/**
 * Format year built
 * 
 * @param mixed $year Year built value
 * @return string Formatted year or empty string
 */
function hph_format_year_built($year) {
    if (empty($year)) {
        return '';
    }
    
    $year = intval($year);
    
    // Validate reasonable year range
    if ($year < 1800 || $year > (date('Y') + 5)) {
        return '';
    }
    
    return (string) $year;
}

/**
 * Format price display
 * 
 * @param mixed $price Price value
 * @param bool $include_currency Whether to include $ symbol
 * @return string Formatted price
 */
function hph_format_price($price, $include_currency = true) {
    if (empty($price) || !is_numeric($price)) {
        return '';
    }
    
    $formatted = number_format(intval($price));
    
    if ($include_currency) {
        return '$' . $formatted;
    }
    
    return $formatted;
}
