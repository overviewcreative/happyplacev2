<?php
/**
 * HPH Property Features & Amenities Section Template
 * 
 * Displays property features organized by MLS categories using bridge functions
 * Location: /wp-content/themes/happy-place/template-parts/components/listing-features.php
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * MLS-Standard Categories:
 * - Interior Features
 * - Exterior Features
 * - Heating & Cooling
 * - Kitchen & Appliances
 * - Flooring
 * - Parking & Garage
 * - Lot Features
 * - Utilities
 * - Construction
 * - Roof
 * - Foundation
 * - Security Features
 * 
 * Args:
 * - listing_id: int (required for bridge functions)
 * - interior_features: array (optional - will use bridge if not provided)
 * - exterior_features: array (optional)
 * - heating_cooling: array (optional)
 * - kitchen_appliances: array (optional)
 * - flooring: array (optional)
 * - parking_garage: array (optional)
 * - lot_features: array (optional)
 * - utilities: array (optional)
 * - construction: array (optional)
 * - roof_info: array (optional)
 * - foundation: array (optional)
 * - security_features: array (optional)
 * - accessibility_features: array (optional)
 * - green_features: array (optional)
 * - additional_features: array (catch-all) (optional)
 * - display_mode: 'grid' | 'accordion' | 'tabs'
 * - show_icons: boolean
 * - show_empty_categories: boolean
 * - highlight_premium: boolean
 * - section_id: string
 */

// Extract listing ID from args or global context
$listing_id = $args['listing_id'] ?? get_the_ID();

// Default arguments
$defaults = array(
    'listing_id' => $listing_id,
    'interior_features' => array(),
    'exterior_features' => array(),
    'heating_cooling' => array(),
    'kitchen_appliances' => array(),
    'flooring' => array(),
    'parking_garage' => array(),
    'lot_features' => array(),
    'utilities' => array(),
    'construction' => array(),
    'roof_info' => array(),
    'foundation' => array(),
    'security_features' => array(),
    'accessibility_features' => array(),
    'green_features' => array(),
    'additional_features' => array(),
    'display_mode' => 'grid',
    'show_icons' => true,
    'show_empty_categories' => false,
    'highlight_premium' => true,
    'section_id' => 'property-features'
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Use bridge functions to get data if not provided and listing_id exists
if ($listing_id) {
    $features_data = hpt_get_listing_features_categorized($listing_id);
    
    // Use bridge data if component args are empty
    foreach ($features_data as $key => $value) {
        if (empty($config[$key]) && !empty($value)) {
            $config[$key] = $value;
        }
    }
}

extract($config);

// Component assets are loaded by HPH_Assets service automatically

// Pass configuration to JavaScript
wp_localize_script('hph-listing-features', 'hphFeaturesConfig', array(
    'listingId' => $listing_id,
    'displayMode' => $display_mode,
    'showIcons' => $show_icons,
    'highlightPremium' => $highlight_premium,
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_features_nonce')
));

// Feature categories with icons and labels
$feature_categories = array(
    'interior_features' => array(
        'label' => 'Interior Features',
        'icon' => 'fas fa-home',
        'features' => $interior_features,
        'premium_keywords' => array('Cathedral', 'Crown', 'Custom', 'Built-In', 'Walk-In', 'Primary')
    ),
    'kitchen_appliances' => array(
        'label' => 'Kitchen & Appliances',
        'icon' => 'fas fa-blender',
        'features' => $kitchen_appliances,
        'premium_keywords' => array('Stainless', 'Granite', 'Quartz', 'Island', 'Double', 'Wine')
    ),
    'exterior_features' => array(
        'label' => 'Exterior Features',
        'icon' => 'fas fa-tree',
        'features' => $exterior_features,
        'premium_keywords' => array('Pool', 'Outdoor Kitchen', 'Fire Pit', 'Covered', 'Professional')
    ),
    'heating_cooling' => array(
        'label' => 'Heating & Cooling',
        'icon' => 'fas fa-thermometer-half',
        'features' => $heating_cooling,
        'premium_keywords' => array('Zoned', 'Smart', 'Heat Pump', 'Geothermal')
    ),
    'flooring' => array(
        'label' => 'Flooring',
        'icon' => 'fas fa-th-large',
        'features' => $flooring,
        'premium_keywords' => array('Hardwood', 'Marble', 'Heated', 'Bamboo')
    ),
    'parking_garage' => array(
        'label' => 'Parking & Garage',
        'icon' => 'fas fa-car-side',
        'features' => $parking_garage,
        'premium_keywords' => array('3 Car', 'Oversized', 'Heated', 'EV Charger')
    ),
    'lot_features' => array(
        'label' => 'Lot Features',
        'icon' => 'fas fa-map',
        'features' => $lot_features,
        'premium_keywords' => array('Water', 'Golf', 'View', 'Corner', 'Private')
    ),
    'utilities' => array(
        'label' => 'Utilities',
        'icon' => 'fas fa-plug',
        'features' => $utilities,
        'premium_keywords' => array('Solar', 'Fiber', 'Generator', 'Smart')
    ),
    'construction' => array(
        'label' => 'Construction',
        'icon' => 'fas fa-hammer',
        'features' => $construction,
        'premium_keywords' => array('Brick', 'Stone', 'Custom', 'Steel')
    ),
    'security_features' => array(
        'label' => 'Security',
        'icon' => 'fas fa-shield-alt',
        'features' => $security_features,
        'premium_keywords' => array('Alarm', 'Camera', 'Smart', 'Gated')
    ),
    'accessibility_features' => array(
        'label' => 'Accessibility',
        'icon' => 'fas fa-wheelchair',
        'features' => $accessibility_features,
        'premium_keywords' => array('Elevator', 'Ramp', 'Wide', 'Roll-In')
    ),
    'green_features' => array(
        'label' => 'Energy & Green Features',
        'icon' => 'fas fa-leaf',
        'features' => $green_features,
        'premium_keywords' => array('Solar', 'Energy Star', 'LEED', 'Geothermal', 'Tankless')
    )
);

// Filter out empty categories if needed
if (!$show_empty_categories) {
    $feature_categories = array_filter($feature_categories, function($category) {
        return !empty($category['features']);
    });
}

// Function to check if feature is premium
function is_premium_feature($feature, $keywords) {
    foreach ($keywords as $keyword) {
        if (stripos($feature, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Count total features
$total_features = 0;
foreach ($feature_categories as $category) {
    $total_features += count($category['features']);
}

// Component assets are loaded by HPH_Assets service automatically
?>

<section class="hph-property-features" id="<?php echo esc_attr($section_id); ?>">
    <div class="hph-features-container">
        
        <!-- Section Header -->
        <div class="hph-features-header">
            <div class="hph-features-title-wrapper">
                <h2 class="hph-features-title">
                    <i class="fas fa-list-check"></i>
                    Features & Amenities
                </h2>
                <span class="hph-features-count">
                    <?php echo esc_html($total_features); ?> Total Features
                </span>
            </div>
            
            <!-- View Controls -->
            <div class="hph-features-controls">
                <div class="hph-view-switcher" role="tablist">
                    <button class="hph-view-btn <?php echo $display_mode === 'grid' ? 'active' : ''; ?>" 
                            data-view="grid" 
                            role="tab"
                            aria-selected="<?php echo $display_mode === 'grid' ? 'true' : 'false'; ?>">
                        <i class="fas fa-th"></i>
                        <span>Grid</span>
                    </button>
                    <button class="hph-view-btn <?php echo $display_mode === 'accordion' ? 'active' : ''; ?>" 
                            data-view="accordion" 
                            role="tab"
                            aria-selected="<?php echo $display_mode === 'accordion' ? 'true' : 'false'; ?>">
                        <i class="fas fa-bars"></i>
                        <span>List</span>
                    </button>
                    <button class="hph-view-btn <?php echo $display_mode === 'tabs' ? 'active' : ''; ?>" 
                            data-view="tabs" 
                            role="tab"
                            aria-selected="<?php echo $display_mode === 'tabs' ? 'true' : 'false'; ?>">
                        <i class="fas fa-folder"></i>
                        <span>Categories</span>
                    </button>
                </div>
                
                <!-- Quick Filters -->
                <div class="hph-quick-filters">
                    <button class="hph-filter-btn active" data-filter="all">
                        <i class="fas fa-border-all"></i>
                        All
                    </button>
                    <button class="hph-filter-btn" data-filter="premium">
                        <i class="fas fa-star"></i>
                        Premium
                    </button>
                    <button class="hph-filter-btn" data-filter="green">
                        <i class="fas fa-leaf"></i>
                        Green
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="hph-features-search">
            <div class="hph-search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" 
                       class="hph-search-input" 
                       placeholder="Search features..."
                       aria-label="Search features">
                <button class="hph-search-clear" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="hph-search-results" style="display: none;">
                <span class="hph-results-count">0 features found</span>
            </div>
        </div>
        
        <!-- Features Content -->
        <div class="hph-features-content">
            
            <!-- Grid View -->
            <div class="hph-features-view hph-grid-view <?php echo $display_mode === 'grid' ? 'active' : ''; ?>" 
                 data-view-type="grid">
                <div class="hph-features-grid">
                    <?php foreach ($feature_categories as $key => $category): ?>
                    <?php if (!empty($category['features'])): ?>
                    <div class="hph-feature-category" data-category="<?php echo esc_attr($key); ?>">
                        <div class="hph-category-header">
                            <?php if ($show_icons): ?>
                            <div class="hph-category-icon">
                                <i class="<?php echo esc_attr($category['icon']); ?>"></i>
                            </div>
                            <?php endif; ?>
                            <h3 class="hph-category-title"><?php echo esc_html($category['label']); ?></h3>
                            <span class="hph-category-count"><?php echo count($category['features']); ?></span>
                        </div>
                        <ul class="hph-feature-list">
                            <?php foreach ($category['features'] as $feature): 
                                $is_premium = $highlight_premium && is_premium_feature($feature, $category['premium_keywords']);
                            ?>
                            <li class="hph-feature-item <?php echo $is_premium ? 'hph-premium' : ''; ?>" 
                                data-feature="<?php echo esc_attr(strtolower($feature)); ?>">
                                <?php if ($is_premium): ?>
                                <i class="fas fa-star hph-premium-icon"></i>
                                <?php else: ?>
                                <i class="fas fa-check hph-check-icon"></i>
                                <?php endif; ?>
                                <span><?php echo esc_html($feature); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Accordion View -->
            <div class="hph-features-view hph-accordion-view <?php echo $display_mode === 'accordion' ? 'active' : ''; ?>" 
                 data-view-type="accordion">
                <div class="hph-accordion">
                    <?php foreach ($feature_categories as $key => $category): ?>
                    <?php if (!empty($category['features'])): ?>
                    <div class="hph-accordion-item" data-category="<?php echo esc_attr($key); ?>">
                        <button class="hph-accordion-header" aria-expanded="false">
                            <div class="hph-accordion-title">
                                <?php if ($show_icons): ?>
                                <i class="<?php echo esc_attr($category['icon']); ?>"></i>
                                <?php endif; ?>
                                <span><?php echo esc_html($category['label']); ?></span>
                                <span class="hph-accordion-count"><?php echo count($category['features']); ?></span>
                            </div>
                            <i class="fas fa-chevron-down hph-accordion-arrow"></i>
                        </button>
                        <div class="hph-accordion-content">
                            <div class="hph-accordion-body">
                                <div class="hph-feature-tags">
                                    <?php foreach ($category['features'] as $feature): 
                                        $is_premium = $highlight_premium && is_premium_feature($feature, $category['premium_keywords']);
                                    ?>
                                    <span class="hph-feature-tag <?php echo $is_premium ? 'hph-tag-premium' : ''; ?>"
                                          data-feature="<?php echo esc_attr(strtolower($feature)); ?>">
                                        <?php if ($is_premium): ?>
                                        <i class="fas fa-star"></i>
                                        <?php endif; ?>
                                        <?php echo esc_html($feature); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Tabs View -->
            <div class="hph-features-view hph-tabs-view <?php echo $display_mode === 'tabs' ? 'active' : ''; ?>" 
                 data-view-type="tabs">
                <div class="hph-tabs-container">
                    <!-- Tab Navigation -->
                    <div class="hph-tabs-nav">
                        <?php $first = true; ?>
                        <?php foreach ($feature_categories as $key => $category): ?>
                        <?php if (!empty($category['features'])): ?>
                        <button class="hph-tab-btn <?php echo $first ? 'active' : ''; ?>" 
                                data-tab="<?php echo esc_attr($key); ?>">
                            <?php if ($show_icons): ?>
                            <i class="<?php echo esc_attr($category['icon']); ?>"></i>
                            <?php endif; ?>
                            <span class="hph-tab-label"><?php echo esc_html($category['label']); ?></span>
                            <span class="hph-tab-count"><?php echo count($category['features']); ?></span>
                        </button>
                        <?php $first = false; ?>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="hph-tabs-content">
                        <?php $first = true; ?>
                        <?php foreach ($feature_categories as $key => $category): ?>
                        <?php if (!empty($category['features'])): ?>
                        <div class="hph-tab-panel <?php echo $first ? 'active' : ''; ?>" 
                             data-panel="<?php echo esc_attr($key); ?>">
                            <div class="hph-panel-grid">
                                <?php foreach ($category['features'] as $feature): 
                                    $is_premium = $highlight_premium && is_premium_feature($feature, $category['premium_keywords']);
                                ?>
                                <div class="hph-panel-item <?php echo $is_premium ? 'hph-item-premium' : ''; ?>"
                                     data-feature="<?php echo esc_attr(strtolower($feature)); ?>">
                                    <div class="hph-item-icon">
                                        <?php if ($is_premium): ?>
                                        <i class="fas fa-star"></i>
                                        <?php else: ?>
                                        <i class="fas fa-check-circle"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="hph-item-text">
                                        <?php echo esc_html($feature); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php $first = false; ?>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Premium Features Highlight -->
        <?php if ($highlight_premium): ?>
        <div class="hph-premium-highlight">
            <div class="hph-premium-header">
                <i class="fas fa-star"></i>
                <h3>Premium Features</h3>
            </div>
            <div class="hph-premium-grid">
                <?php 
                $all_premium = array();
                foreach ($feature_categories as $category) {
                    foreach ($category['features'] as $feature) {
                        if (is_premium_feature($feature, $category['premium_keywords'])) {
                            $all_premium[] = $feature;
                        }
                    }
                }
                foreach (array_slice($all_premium, 0, 6) as $premium_feature): 
                ?>
                <div class="hph-premium-item">
                    <i class="fas fa-crown"></i>
                    <span><?php echo esc_html($premium_feature); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Additional Features -->
        <?php if (!empty($additional_features)): ?>
        <div class="hph-additional-features">
            <h3 class="hph-additional-title">
                <i class="fas fa-plus-circle"></i>
                Additional Features
            </h3>
            <div class="hph-additional-text">
                <?php echo wp_kses_post(wpautop(implode(', ', $additional_features))); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Print/Share Actions -->
        <div class="hph-features-actions">
            <button class="hph-action-btn hph-print-features" onclick="window.print()">
                <i class="fas fa-print"></i>
                Print Features List
            </button>
            <button class="hph-action-btn hph-download-features">
                <i class="fas fa-download"></i>
                Download PDF
            </button>
            <button class="hph-action-btn hph-share-features">
                <i class="fas fa-share-alt"></i>
                Share
            </button>
        </div>
        
    </div>
</section>