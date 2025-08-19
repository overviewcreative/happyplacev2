<?php
/**
 * Default Terms Class
 * 
 * Creates default taxonomy terms on plugin activation
 *
 * @package HappyPlace\Utilities
 */

namespace HappyPlace\Utilities;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Default_Terms {
    
    /**
     * Initialize default terms creation
     */
    public static function create_default_terms() {
        // Create default property status terms
        self::create_property_status_terms();
        
        // Create default property type terms  
        self::create_property_type_terms();
        
        // Create default listing feature terms
        self::create_listing_feature_terms();
        
        hp_log('Default taxonomy terms created', 'info', 'DEFAULT_TERMS');
    }
    
    /**
     * Create default property status terms
     */
    private static function create_property_status_terms() {
        $property_statuses = [
            'active' => [
                'name' => 'Active',
                'description' => 'Property is actively listed for sale'
            ],
            'pending' => [
                'name' => 'Pending',
                'description' => 'Property has an accepted offer pending closing'
            ],
            'sold' => [
                'name' => 'Sold',
                'description' => 'Property has been sold'
            ],
            'coming_soon' => [
                'name' => 'Coming Soon',
                'description' => 'Property will be listed soon'
            ],
            'off_market' => [
                'name' => 'Off Market',
                'description' => 'Property is temporarily off the market'
            ],
            'contingent' => [
                'name' => 'Contingent',
                'description' => 'Property has an accepted offer with contingencies'
            ]
        ];
        
        foreach ($property_statuses as $slug => $data) {
            $term = term_exists($slug, 'property_status');
            if (!$term) {
                wp_insert_term(
                    $data['name'],
                    'property_status',
                    [
                        'slug' => $slug,
                        'description' => $data['description']
                    ]
                );
                hp_log("Created property status term: {$data['name']}", 'info', 'DEFAULT_TERMS');
            }
        }
    }
    
    /**
     * Create default property type terms
     */
    private static function create_property_type_terms() {
        $property_types = [
            'single_family' => [
                'name' => 'Single Family Home',
                'description' => 'Detached single-family residence'
            ],
            'condo' => [
                'name' => 'Condominium',
                'description' => 'Condominium unit'
            ],
            'townhouse' => [
                'name' => 'Townhouse',
                'description' => 'Attached townhouse or row house'
            ],
            'duplex' => [
                'name' => 'Duplex',
                'description' => 'Two-unit residential building'
            ],
            'multi_family' => [
                'name' => 'Multi-Family',
                'description' => 'Multi-unit residential property'
            ],
            'commercial' => [
                'name' => 'Commercial',
                'description' => 'Commercial property'
            ],
            'land' => [
                'name' => 'Land',
                'description' => 'Vacant land or lot'
            ],
            'mobile_home' => [
                'name' => 'Mobile Home',
                'description' => 'Mobile or manufactured home'
            ]
        ];
        
        foreach ($property_types as $slug => $data) {
            $term = term_exists($slug, 'property_type');
            if (!$term) {
                wp_insert_term(
                    $data['name'],
                    'property_type',
                    [
                        'slug' => $slug,
                        'description' => $data['description']
                    ]
                );
                hp_log("Created property type term: {$data['name']}", 'info', 'DEFAULT_TERMS');
            }
        }
    }
    
    /**
     * Create default listing feature terms
     */
    private static function create_listing_feature_terms() {
        $feature_categories = [
            // Interior Features
            'interior' => [
                'hardwood_floors' => 'Hardwood Floors',
                'granite_countertops' => 'Granite Countertops',
                'stainless_appliances' => 'Stainless Steel Appliances',
                'updated_kitchen' => 'Updated Kitchen',
                'walk_in_closets' => 'Walk-in Closets',
                'fireplace' => 'Fireplace',
                'high_ceilings' => 'High Ceilings'
            ],
            // Exterior Features  
            'exterior' => [
                'covered_patio' => 'Covered Patio',
                'fenced_yard' => 'Fenced Yard',
                'mature_trees' => 'Mature Trees',
                'sprinkler_system' => 'Sprinkler System',
                'three_car_garage' => 'Three Car Garage',
                'workshop' => 'Workshop'
            ],
            // Property Features
            'property' => [
                'new_construction' => 'New Construction',
                'waterfront' => 'Waterfront',
                'golf_course' => 'Golf Course Community',
                'gated_community' => 'Gated Community',
                'corner_lot' => 'Corner Lot',
                'cul_de_sac' => 'Cul-de-sac'
            ]
        ];
        
        foreach ($feature_categories as $category => $features) {
            // Create parent category
            $parent_term = term_exists($category, 'listing_features');
            if (!$parent_term) {
                $parent_result = wp_insert_term(
                    ucfirst($category) . ' Features',
                    'listing_features',
                    [
                        'slug' => $category,
                        'description' => ucfirst($category) . ' property features'
                    ]
                );
                $parent_id = $parent_result['term_id'] ?? 0;
            } else {
                $parent_id = $parent_term['term_id'];
            }
            
            // Create child features
            foreach ($features as $slug => $name) {
                $term = term_exists($slug, 'listing_features');
                if (!$term) {
                    wp_insert_term(
                        $name,
                        'listing_features',
                        [
                            'slug' => $slug,
                            'parent' => $parent_id
                        ]
                    );
                    hp_log("Created listing feature term: {$name}", 'info', 'DEFAULT_TERMS');
                }
            }
        }
    }
}