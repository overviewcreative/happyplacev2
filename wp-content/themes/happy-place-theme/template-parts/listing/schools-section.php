<?php
/**
 * Listing Schools Section Template Part
 * Updated to use bridge functions for data access
 * 
 * @package HappyPlaceTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get schools data using bridge function with fallback
$schools = null;
if (function_exists('hpt_get_listing_nearby_schools')) {
    try {
        $schools = hpt_get_listing_nearby_schools($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_nearby_schools failed: ' . $e->getMessage());
    }
}

// Fallback to direct field access
if (empty($schools)) {
    $schools = get_field('nearby_schools', $listing_id) ?: [];
}

$school_district = get_field('school_district', $listing_id); // Direct fallback

// Check if we have school information
$has_school_info = !empty($schools) || !empty($school_district);

if (!$has_school_info) {
    return;
}
?>

<section class="hph-property-map__schools">
    <div class="hph-container">
        
        <h2 class="hph-map-schools__title">
            <i class="fas fa-graduation-cap"></i>
            School Information
        </h2>
        
        <?php if (!empty($school_district)) : ?>
            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--hph-gray-900); margin-bottom: 0.5rem;">
                    School District
                </h3>
                <p style="color: var(--hph-gray-700); font-size: 1rem;">
                    <?php echo esc_html($school_district); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <div class="hph-school-list">
            
            <?php if (!empty($schools)) : ?>
                <?php foreach ($schools as $school) : ?>
                    <div class="hph-school-item">
                        <div class="hph-school-item__type"><?php echo esc_html($school['type'] ?? 'School'); ?></div>
                        <div class="hph-school-item__info">
                            <h4 class="hph-school-item__name"><?php echo esc_html($school['name'] ?? 'Unknown School'); ?></h4>
                            <?php if (!empty($school['grades'])) : ?>
                                <p class="hph-school-item__grades">Grades: <?php echo esc_html($school['grades']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($school['distance'])) : ?>
                                <p class="hph-school-item__distance">Distance: <?php echo esc_html($school['distance']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($school['rating'])) : ?>
                                <div class="hph-school-item__rating">
                                    Rating: <span class="hph-rating"><?php echo esc_html($school['rating']); ?>/10</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        
        </div>
        
    </div>
</section>
