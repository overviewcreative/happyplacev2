<?php
/**
 * Neighborhood Section Template Part
 * File: template-parts/listing/neighborhood-section.php
 * 
 * Displays neighborhood information and nearby amenities
 * Uses theme's existing styles and patterns
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
    'neighborhood' => get_field('neighborhood', $listing_id),
    'school_district' => get_field('school_district', $listing_id),
    'elementary_school' => get_field('elementary_school', $listing_id),
    'middle_school' => get_field('middle_school', $listing_id),
    'high_school' => get_field('high_school', $listing_id),
    'schools' => $nearby_schools
];

// Neighborhood features
$amenities = [
    'walkability_score' => get_field('walkability_score', $listing_id) ?: 'N/A',
    'transit_score' => get_field('transit_score', $listing_id) ?: 'N/A',
    'bike_score' => get_field('bike_score', $listing_id) ?: 'N/A'
];

?>

<section class="template-section section-features theme-light padding-xl">
    <div class="container">
        
        <div class="section-header text-center">
            <h2 class="section-title">Neighborhood & Location</h2>
            <?php if ($location['neighborhood']) : ?>
            <p class="section-subtitle">
                <?php echo esc_html($location['neighborhood']); ?> Neighborhood
            </p>
            <?php endif; ?>
        </div>
        
        <div class="row">
            
            <!-- Location Details -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                            Location Details
                        </h3>
                        
                        <table class="table table-borderless">
                            <tbody>
                                <?php if ($location['neighborhood']) : ?>
                                <tr>
                                    <td class="text-muted">Neighborhood:</td>
                                    <td class="font-weight-medium text-right">
                                        <?php echo esc_html($location['neighborhood']); ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if ($location['city']) : ?>
                                <tr>
                                    <td class="text-muted">City:</td>
                                    <td class="font-weight-medium text-right">
                                        <?php echo esc_html($location['city']); ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if ($location['county']) : ?>
                                <tr>
                                    <td class="text-muted">County:</td>
                                    <td class="font-weight-medium text-right">
                                        <?php echo esc_html($location['county']); ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if ($location['state']) : ?>
                                <tr>
                                    <td class="text-muted">State:</td>
                                    <td class="font-weight-medium text-right">
                                        <?php echo esc_html($location['state']); ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if ($location['zip_code']) : ?>
                                <tr>
                                    <td class="text-muted">ZIP Code:</td>
                                    <td class="font-weight-medium text-right">
                                        <?php echo esc_html($location['zip_code']); ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- School Information -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-graduation-cap text-primary mr-2"></i>
                            School Information
                        </h3>
                        
                        <?php if ($location['school_district']) : ?>
                        <div class="alert alert-info mb-4">
                            <div class="small text-muted mb-1">School District:</div>
                            <div class="font-weight-bold">
                                <?php echo esc_html($location['school_district']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="school-list">
                            <?php if ($location['elementary_school']) : ?>
                            <div class="d-flex align-items-start mb-3">
                                <i class="fas fa-school text-primary mt-1 mr-3"></i>
                                <div>
                                    <div class="small text-muted">Elementary:</div>
                                    <div class="font-weight-medium">
                                        <?php echo esc_html($location['elementary_school']); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($location['middle_school']) : ?>
                            <div class="d-flex align-items-start mb-3">
                                <i class="fas fa-school text-primary mt-1 mr-3"></i>
                                <div>
                                    <div class="small text-muted">Middle:</div>
                                    <div class="font-weight-medium">
                                        <?php echo esc_html($location['middle_school']); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($location['high_school']) : ?>
                            <div class="d-flex align-items-start">
                                <i class="fas fa-school text-primary mt-1 mr-3"></i>
                                <div>
                                    <div class="small text-muted">High:</div>
                                    <div class="font-weight-medium">
                                        <?php echo esc_html($location['high_school']); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!$location['elementary_school'] && !$location['middle_school'] && !$location['high_school']) : ?>
                            <p class="text-muted">School information not available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Walkability Scores -->
        <?php if ($amenities['walkability_score'] !== 'N/A' || $amenities['transit_score'] !== 'N/A' || $amenities['bike_score'] !== 'N/A') : ?>
        <div class="scores-section mt-5">
            <h3 class="text-center mb-4">Neighborhood Scores</h3>
            
            <div class="row">
                
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="score-circle mx-auto mb-3" style="width: 100px; height: 100px; border-radius: 50%; background: #f8f9fa; display: flex; align-items: center; justify-content: center; flex-direction: column; border: 3px solid var(--primary-color, #007bff);">
                            <i class="fas fa-walking text-primary mb-2" style="font-size: 1.5rem;"></i>
                            <div class="h3 mb-0 font-weight-bold">
                                <?php echo esc_html($amenities['walkability_score']); ?>
                            </div>
                        </div>
                        <div class="text-muted">Walk Score</div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="score-circle mx-auto mb-3" style="width: 100px; height: 100px; border-radius: 50%; background: #f8f9fa; display: flex; align-items: center; justify-content: center; flex-direction: column; border: 3px solid var(--primary-color, #007bff);">
                            <i class="fas fa-bus text-primary mb-2" style="font-size: 1.5rem;"></i>
                            <div class="h3 mb-0 font-weight-bold">
                                <?php echo esc_html($amenities['transit_score']); ?>
                            </div>
                        </div>
                        <div class="text-muted">Transit Score</div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="score-circle mx-auto mb-3" style="width: 100px; height: 100px; border-radius: 50%; background: #f8f9fa; display: flex; align-items: center; justify-content: center; flex-direction: column; border: 3px solid var(--primary-color, #007bff);">
                            <i class="fas fa-bicycle text-primary mb-2" style="font-size: 1.5rem;"></i>
                            <div class="h3 mb-0 font-weight-bold">
                                <?php echo esc_html($amenities['bike_score']); ?>
                            </div>
                        </div>
                        <div class="text-muted">Bike Score</div>
                    </div>
                </div>
                
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Nearby Amenities -->
        <div class="amenities-section mt-5">
            <h3 class="text-center mb-4">What's Nearby</h3>
            
            <div class="row">
                
                <?php 
                $amenity_items = [
                    ['icon' => 'fa-shopping-cart', 'label' => 'Grocery Stores'],
                    ['icon' => 'fa-utensils', 'label' => 'Restaurants'],
                    ['icon' => 'fa-tree', 'label' => 'Parks'],
                    ['icon' => 'fa-hospital', 'label' => 'Healthcare'],
                    ['icon' => 'fa-dumbbell', 'label' => 'Fitness'],
                    ['icon' => 'fa-coffee', 'label' => 'Coffee Shops'],
                    ['icon' => 'fa-university', 'label' => 'Banks'],
                    ['icon' => 'fa-gas-pump', 'label' => 'Gas Stations'],
                ];
                
                foreach ($amenity_items as $item) : ?>
                <div class="col-6 col-md-3 mb-4">
                    <div class="amenity-item text-center p-3 h-100" style="background: #ffffff; border: 1px solid #e9ecef; border-radius: 0.25rem; transition: all 0.3s ease;">
                        <i class="fas <?php echo esc_attr($item['icon']); ?> text-primary mb-2" style="font-size: 2rem;"></i>
                        <div class="small font-weight-medium">
                            <?php echo esc_html($item['label']); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
            </div>
        </div>
        
        <!-- Map Section (optional) -->
        <?php if (get_field('show_neighborhood_map', $listing_id)) : ?>
        <div class="map-section mt-5">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">
                        <i class="fas fa-map text-primary mr-2"></i>
                        Explore the Area
                    </h3>
                    <div class="embed-responsive embed-responsive-16by9">
                        <!-- Map embed would go here -->
                        <div class="embed-responsive-item bg-light d-flex align-items-center justify-content-center">
                            <span class="text-muted">Interactive map coming soon</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</section>

<style>
/* Hover effects for amenity items */
.amenity-item:hover {
    background: #f8f9fa !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

/* Score circle animation on hover */
.score-circle {
    transition: transform 0.3s ease;
}
.score-circle:hover {
    transform: scale(1.05);
}

/* Ensure cards match theme styling */
.template-section.theme-light {
    background: #f8f9fa;
}
.template-section .card {
    border: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    transition: box-shadow 0.3s ease;
}
.template-section .card:hover {
    box-shadow: 0 3px 6px rgba(0,0,0,0.15), 0 2px 4px rgba(0,0,0,0.12);
}
</style>
