<?php
/**
 * Neighborhood Section Template Part
 * File: template-parts/listing/neighborhood-section.php
 * 
 * Displays neighborhood information and nearby amenities using bridge functions
 * Uses HPH framework utilities and CSS variables
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get location data using bridge functions with fallbacks
$listing_address = null;
if (function_exists('hpt_get_listing_address')) {
    try {
        $listing_address = hpt_get_listing_address($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_address failed: ' . $e->getMessage());
    }
}

$nearby_schools = null;
if (function_exists('hpt_get_listing_nearby_schools')) {
    try {
        $nearby_schools = hpt_get_listing_nearby_schools($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_nearby_schools failed: ' . $e->getMessage());
    }
}

// Fallback to direct field access if bridge functions fail
if (empty($nearby_schools)) {
    $nearby_schools = get_field('nearby_schools', $listing_id) ?: [];
}

$location = [
    'city' => $listing_address['city'] ?? get_field('city', $listing_id),
    'state' => $listing_address['state'] ?? get_field('state', $listing_id),
    'zip_code' => $listing_address['zip_code'] ?? get_field('zip_code', $listing_id),
    'county' => $listing_address['county'] ?? get_field('county', $listing_id),
    'neighborhood' => get_field('neighborhood', $listing_id), // Direct fallback
    'school_district' => get_field('school_district', $listing_id), // Direct fallback
    'schools' => $nearby_schools
];

// Neighborhood features (could be from ACF or hardcoded)
$amenities = [
    'walkability_score' => get_field('walkability_score', $listing_id) ?: 'N/A',
    'transit_score' => get_field('transit_score', $listing_id) ?: 'N/A',
    'bike_score' => get_field('bike_score', $listing_id) ?: 'N/A'
];

?>

<section class="hph-neighborhood-section hph-py-3xl hph-bg-gray-50">
    <div class="hph-container">
        
        <div class="hph-section__header hph-text-center hph-mb-xl">
            <h2 class="hph-section__title hph-text-3xl hph-font-bold hph-mb-sm">
                Neighborhood & Location
            </h2>
            <?php if ($location['neighborhood']) : ?>
            <p class="hph-section__subtitle hph-text-lg hph-text-gray-600">
                <?php echo esc_html($location['neighborhood']); ?> Neighborhood
            </p>
            <?php endif; ?>
        </div>
        
        <div class="hph-grid hph-grid-cols-1 hph-grid-cols-lg-2 hph-gap-xl">
            
            <!-- Location Details -->
            <div class="hph-neighborhood__details hph-bg-white hph-rounded-lg hph-shadow-sm hph-p-xl">
                
                <h3 class="hph-text-xl hph-font-semibold hph-mb-lg hph-flex hph-items-center hph-gap-sm">
                    <i class="fas fa-map-marker-alt hph-text-primary"></i>
                    Location Details
                </h3>
                
                <dl class="hph-detail-list hph-space-y-md">
                    <?php if ($location['neighborhood']) : ?>
                    <div class="hph-detail-row hph-flex hph-justify-between hph-py-sm hph-border-b hph-border-gray-100">
                        <dt class="hph-text-gray-600">Neighborhood:</dt>
                        <dd class="hph-font-medium"><?php echo esc_html($location['neighborhood']); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($location['city']) : ?>
                    <div class="hph-detail-row hph-flex hph-justify-between hph-py-sm hph-border-b hph-border-gray-100">
                        <dt class="hph-text-gray-600">City:</dt>
                        <dd class="hph-font-medium"><?php echo esc_html($location['city']); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($location['county']) : ?>
                    <div class="hph-detail-row hph-flex hph-justify-between hph-py-sm hph-border-b hph-border-gray-100">
                        <dt class="hph-text-gray-600">County:</dt>
                        <dd class="hph-font-medium"><?php echo esc_html($location['county']); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($location['state']) : ?>
                    <div class="hph-detail-row hph-flex hph-justify-between hph-py-sm hph-border-b hph-border-gray-100">
                        <dt class="hph-text-gray-600">State:</dt>
                        <dd class="hph-font-medium"><?php echo esc_html($location['state']); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($location['zip_code']) : ?>
                    <div class="hph-detail-row hph-flex hph-justify-between hph-py-sm">
                        <dt class="hph-text-gray-600">ZIP Code:</dt>
                        <dd class="hph-font-medium"><?php echo esc_html($location['zip_code']); ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
                
            </div>
            
            <!-- School Information -->
            <div class="hph-neighborhood__schools hph-bg-white hph-rounded-lg hph-shadow-sm hph-p-xl">
                
                <h3 class="hph-text-xl hph-font-semibold hph-mb-lg hph-flex hph-items-center hph-gap-sm">
                    <i class="fas fa-graduation-cap hph-text-primary"></i>
                    School Information
                </h3>
                
                <?php if ($location['school_district']) : ?>
                <div class="hph-school-district hph-mb-lg hph-p-md hph-bg-primary-50 hph-rounded-md">
                    <span class="hph-text-sm hph-text-gray-600">School District:</span>
                    <div class="hph-font-semibold hph-text-lg"><?php echo esc_html($location['school_district']); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="hph-schools-list hph-space-y-md">
                    <?php if ($location['elementary_school']) : ?>
                    <div class="hph-school-item hph-flex hph-items-start hph-gap-sm">
                        <i class="fas fa-school hph-text-primary hph-mt-xs"></i>
                        <div>
                            <div class="hph-text-sm hph-text-gray-600">Elementary:</div>
                            <div class="hph-font-medium"><?php echo esc_html($location['elementary_school']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($location['middle_school']) : ?>
                    <div class="hph-school-item hph-flex hph-items-start hph-gap-sm">
                        <i class="fas fa-school hph-text-primary hph-mt-xs"></i>
                        <div>
                            <div class="hph-text-sm hph-text-gray-600">Middle:</div>
                            <div class="hph-font-medium"><?php echo esc_html($location['middle_school']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($location['high_school']) : ?>
                    <div class="hph-school-item hph-flex hph-items-start hph-gap-sm">
                        <i class="fas fa-school hph-text-primary hph-mt-xs"></i>
                        <div>
                            <div class="hph-text-sm hph-text-gray-600">High:</div>
                            <div class="hph-font-medium"><?php echo esc_html($location['high_school']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
            </div>
            
        </div>
        
        <!-- Walkability Scores -->
        <div class="hph-scores hph-mt-xl">
            <h3 class="hph-text-xl hph-font-semibold hph-mb-lg hph-text-center">
                Neighborhood Scores
            </h3>
            
            <div class="hph-grid hph-grid-cols-1 hph-grid-cols-md-3 hph-gap-lg">
                
                <div class="hph-score-card hph-bg-white hph-rounded-lg hph-shadow-sm hph-p-lg hph-text-center">
                    <div class="hph-score-icon hph-text-3xl hph-text-primary hph-mb-sm">
                        <i class="fas fa-walking"></i>
                    </div>
                    <div class="hph-score-value hph-text-2xl hph-font-bold hph-mb-xs">
                        <?php echo esc_html($amenities['walkability_score']); ?>
                    </div>
                    <div class="hph-score-label hph-text-sm hph-text-gray-600">Walk Score</div>
                </div>
                
                <div class="hph-score-card hph-bg-white hph-rounded-lg hph-shadow-sm hph-p-lg hph-text-center">
                    <div class="hph-score-icon hph-text-3xl hph-text-primary hph-mb-sm">
                        <i class="fas fa-bus"></i>
                    </div>
                    <div class="hph-score-value hph-text-2xl hph-font-bold hph-mb-xs">
                        <?php echo esc_html($amenities['transit_score']); ?>
                    </div>
                    <div class="hph-score-label hph-text-sm hph-text-gray-600">Transit Score</div>
                </div>
                
                <div class="hph-score-card hph-bg-white hph-rounded-lg hph-shadow-sm hph-p-lg hph-text-center">
                    <div class="hph-score-icon hph-text-3xl hph-text-primary hph-mb-sm">
                        <i class="fas fa-bicycle"></i>
                    </div>
                    <div class="hph-score-value hph-text-2xl hph-font-bold hph-mb-xs">
                        <?php echo esc_html($amenities['bike_score']); ?>
                    </div>
                    <div class="hph-score-label hph-text-sm hph-text-gray-600">Bike Score</div>
                </div>
                
            </div>
        </div>
        
        <!-- Nearby Amenities -->
        <div class="hph-amenities hph-mt-xl">
            <h3 class="hph-text-xl hph-font-semibold hph-mb-lg hph-text-center">
                What's Nearby
            </h3>
            
            <div class="hph-grid hph-grid-cols-2 hph-grid-cols-md-4 hph-gap-md">
                
                <div class="hph-amenity-item hph-bg-white hph-rounded-lg hph-p-md hph-text-center hph-shadow-sm hover:hph-shadow-md hph-transition-shadow">
                    <i class="fas fa-shopping-cart hph-text-2xl hph-text-primary hph-mb-sm"></i>
                    <div class="hph-text-sm hph-font-medium">Grocery Stores</div>
                </div>
                
                <div class="hph-amenity-item hph-bg-white hph-rounded-lg hph-p-md hph-text-center hph-shadow-sm hover:hph-shadow-md hph-transition-shadow">
                    <i class="fas fa-utensils hph-text-2xl hph-text-primary hph-mb-sm"></i>
                    <div class="hph-text-sm hph-font-medium">Restaurants</div>
                </div>
                
                <div class="hph-amenity-item hph-bg-white hph-rounded-lg hph-p-md hph-text-center hph-shadow-sm hover:hph-shadow-md hph-transition-shadow">
                    <i class="fas fa-tree hph-text-2xl hph-text-primary hph-mb-sm"></i>
                    <div class="hph-text-sm hph-font-medium">Parks</div>
                </div>
                
                <div class="hph-amenity-item hph-bg-white hph-rounded-lg hph-p-md hph-text-center hph-shadow-sm hover:hph-shadow-md hph-transition-shadow">
                    <i class="fas fa-hospital hph-text-2xl hph-text-primary hph-mb-sm"></i>
                    <div class="hph-text-sm hph-font-medium">Healthcare</div>
                </div>
                
                <div class="hph-amenity-item hph-bg-white hph-rounded-lg hph-p-md hph-text-center hph-shadow-sm hover:hph-shadow-md hph-transition-shadow">
                    <i class="fas fa-dumbbell hph-text-2xl hph-text-primary hph-mb-sm"></i>
                    <div class="hph-text-sm hph-font-medium">Fitness</div>
                </div>
                
                <div class="hph-amenity-item hph-bg-white hph-rounded-lg hph-p-md hph-text-center hph-shadow-sm hover:hph-shadow-md hph-transition-shadow">
                    <i class="fas fa-coffee hph-text-2xl hph-text-primary hph-mb-sm"></i>
                    <div class="hph-text-sm hph-font-medium">Coffee Shops</div>
                </div>
                
                <div class="hph-amenity-item hph-bg-white hph-rounded-lg hph-p-md hph-text-center hph-shadow-sm hover:hph-shadow-md hph-transition-shadow">
                    <i class="fas fa-bank hph-text-2xl hph-text-primary hph-mb-sm"></i>
                    <div class="hph-text-sm hph-font-medium">Banks</div>
                </div>
                
                <div class="hph-amenity-item hph-bg-white hph-rounded-lg hph-p-md hph-text-center hph-shadow-sm hover:hph-shadow-md hph-transition-shadow">
                    <i class="fas fa-gas-pump hph-text-2xl hph-text-primary hph-mb-sm"></i>
                    <div class="hph-text-sm hph-font-medium">Gas Stations</div>
                </div>
                
            </div>
        </div>
        
    </div>
</section>