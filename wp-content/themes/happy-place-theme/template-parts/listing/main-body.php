<?php
/**
 * Main Body Content Template Part
 * File: template-parts/listing/main-body.php
 * 
 * Displays property description, details, features, and additional information
 * Uses bridge functions for data access with HPH framework utilities
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get change tracking data from args (passed from single-listing.php)
$listing_changes = $args['listing_changes'] ?? [];
$listing_badges = $args['listing_badges'] ?? [];
$has_recent_changes = $args['has_recent_changes'] ?? false;
$is_new_listing = $args['is_new_listing'] ?? false;

// Get listing data using bridge function with error handling
$listing_data = null;
if (function_exists('hpt_get_listing')) {
    try {
        $listing_data = hpt_get_listing($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing failed: ' . $e->getMessage());
    }
}

// Don't return early - continue with fallbacks
$fields = [];

// Basic Information with fallbacks
$fields['property_description'] = $listing_data['description'] ?? get_field('property_description', $listing_id) ?: get_the_content(null, false, $listing_id);
$fields['property_title'] = $listing_data['title'] ?? get_field('property_title', $listing_id) ?: get_the_title($listing_id);
$fields['listing_price'] = $listing_data['price'] ?? get_field('listing_price', $listing_id) ?: 0;
$fields['price_per_sqft'] = $listing_data['price_per_sqft'] ?? null;

// Core Details using bridge functions with fallbacks
$fields['bedrooms'] = null;
if (function_exists('hpt_get_listing_bedrooms')) {
    try {
        $fields['bedrooms'] = hpt_get_listing_bedrooms($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_bedrooms failed: ' . $e->getMessage());
    }
}
if (!$fields['bedrooms']) {
    $fields['bedrooms'] = get_field('bedrooms', $listing_id) ?: 0;
}

$fields['bathrooms_full'] = null;
if (function_exists('hpt_get_listing_bathrooms_full')) {
    try {
        $fields['bathrooms_full'] = hpt_get_listing_bathrooms_full($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_bathrooms_full failed: ' . $e->getMessage());
    }
}
if (!$fields['bathrooms_full']) {
    $fields['bathrooms_full'] = get_field('bathrooms_full', $listing_id) ?: 0;
}

$fields['bathrooms_half'] = null;
if (function_exists('hpt_get_listing_bathrooms_half')) {
    try {
        $fields['bathrooms_half'] = hpt_get_listing_bathrooms_half($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_bathrooms_half failed: ' . $e->getMessage());
    }
}
if (!$fields['bathrooms_half']) {
    $fields['bathrooms_half'] = get_field('bathrooms_half', $listing_id) ?: 0;
}

$fields['bathrooms_formatted'] = null;
if (function_exists('hpt_get_listing_bathrooms_formatted')) {
    try {
        $fields['bathrooms_formatted'] = hpt_get_listing_bathrooms_formatted($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_bathrooms_formatted failed: ' . $e->getMessage());
    }
}
if (!$fields['bathrooms_formatted']) {
    $total_baths = $fields['bathrooms_full'] + ($fields['bathrooms_half'] * 0.5);
    $fields['bathrooms_formatted'] = $total_baths > 0 ? number_format($total_baths, ($fields['bathrooms_half'] > 0 ? 1 : 0)) : '0';
}

$fields['square_feet'] = null;
if (function_exists('hpt_get_listing_square_feet')) {
    try {
        $fields['square_feet'] = hpt_get_listing_square_feet($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_square_feet failed: ' . $e->getMessage());
    }
}
if (!$fields['square_feet']) {
    $fields['square_feet'] = get_field('square_feet', $listing_id) ?: 0;
}

$fields['square_feet_formatted'] = null;
if (function_exists('hpt_get_listing_square_feet_formatted')) {
    try {
        $fields['square_feet_formatted'] = hpt_get_listing_square_feet_formatted($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_square_feet_formatted failed: ' . $e->getMessage());
    }
}
if (!$fields['square_feet_formatted']) {
    $fields['square_feet_formatted'] = $fields['square_feet'] ? number_format($fields['square_feet']) . ' sq ft' : '';
}

$fields['lot_size_formatted'] = null;
if (function_exists('hpt_get_listing_lot_size_formatted')) {
    try {
        $fields['lot_size_formatted'] = hpt_get_listing_lot_size_formatted($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_lot_size_formatted failed: ' . $e->getMessage());
    }
}
if (!$fields['lot_size_formatted']) {
    $lot_acres = get_field('lot_size_acres', $listing_id);
    $lot_sqft = get_field('lot_size_sqft', $listing_id);
    if ($lot_acres) {
        $fields['lot_size_formatted'] = number_format($lot_acres, 2) . ' acres';
    } elseif ($lot_sqft) {
        $fields['lot_size_formatted'] = number_format($lot_sqft) . ' sq ft';
    } else {
        $fields['lot_size_formatted'] = '';
    }
}

$fields['year_built'] = null;
if (function_exists('hpt_get_listing_year_built')) {
    try {
        $fields['year_built'] = hpt_get_listing_year_built($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_year_built failed: ' . $e->getMessage());
    }
}
if (!$fields['year_built']) {
    $fields['year_built'] = get_field('year_built', $listing_id);
}

// Property details with fallbacks
$fields['garage_spaces'] = $listing_data['garage_spaces'] ?? get_field('garage_spaces', $listing_id);
$fields['garage_type'] = $listing_data['garage_type'] ?? get_field('garage_type', $listing_id);

$fields['property_type'] = null;
if (function_exists('hpt_get_listing_property_type')) {
    try {
        $fields['property_type'] = hpt_get_listing_property_type($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_property_type failed: ' . $e->getMessage());
    }
}
if (!$fields['property_type']) {
    $fields['property_type'] = get_field('property_type', $listing_id);
}

$fields['property_style'] = $listing_data['property_style'] ?? get_field('property_style', $listing_id);
$fields['stories'] = $listing_data['stories'] ?? get_field('stories', $listing_id);
$fields['basement'] = $listing_data['basement'] ?? get_field('basement', $listing_id);
$fields['condition'] = $listing_data['condition'] ?? get_field('condition', $listing_id);

// Format display values for property details
// Apply formatting to property values
$fields['property_type'] = hph_format_property_value($fields['property_type']);
$fields['property_style'] = hph_format_property_value($fields['property_style']); 
$fields['garage_type'] = hph_format_property_value($fields['garage_type']);
$fields['condition'] = hph_format_property_value($fields['condition']);
$fields['basement'] = hph_format_property_value($fields['basement']);

// Features Arrays - fallback to direct field access
$fields['interior_features'] = get_field('interior_features', $listing_id) ?: [];
$fields['exterior_features'] = get_field('exterior_features', $listing_id) ?: [];
$fields['property_features'] = get_field('property_features', $listing_id) ?: [];
$fields['appliances'] = get_field('appliances', $listing_id) ?: [];
$fields['flooring'] = get_field('flooring', $listing_id) ?: [];
$fields['heating_cooling'] = get_field('heating_cooling', $listing_id) ?: [];

// Special Features
$fields['has_pool'] = get_field('has_pool', $listing_id);
$fields['pool_type'] = get_field('pool_type', $listing_id);
$fields['has_spa'] = get_field('has_spa', $listing_id);
$fields['has_view'] = get_field('has_view', $listing_id);
$fields['view_description'] = get_field('view_description', $listing_id);

// Financial Information
$fields['annual_taxes'] = get_field('annual_taxes', $listing_id);
$fields['tax_year'] = get_field('tax_year', $listing_id);
$fields['hoa_fee'] = get_field('hoa_fee', $listing_id);
$fields['hoa_frequency'] = get_field('hoa_frequency', $listing_id);
$fields['hoa_includes'] = get_field('hoa_includes', $listing_id) ?: [];

// Utility Information
$fields['utilities_included'] = get_field('utilities_included', $listing_id) ?: [];
$fields['water_type'] = get_field('water_type', $listing_id);
$fields['sewer_type'] = get_field('sewer_type', $listing_id);
$fields['electric_provider'] = get_field('electric_provider', $listing_id);
$fields['gas_provider'] = get_field('gas_provider', $listing_id);

// Additional Info
$fields['zoning'] = get_field('zoning', $listing_id);
$fields['parcel_number'] = get_field('parcel_number', $listing_id);
$fields['legal_description'] = get_field('legal_description', $listing_id);
$fields['restrictions'] = get_field('restrictions', $listing_id);
$fields['easements'] = get_field('easements', $listing_id);

// Extract variables for template use
extract($fields);

// Calculate display values using bridge function results
$total_bathrooms = $fields['bathrooms_formatted']; // Use pre-formatted value from bridge function
$lot_display = $fields['lot_size_formatted']; // Use pre-formatted value from bridge function

// Feature labels for better display
$feature_labels = [
    'central_air' => 'Central Air Conditioning',
    'forced_air' => 'Forced Air Heating',
    'hardwood' => 'Hardwood Floors',
    'tile' => 'Tile Flooring',
    'carpet' => 'Carpeting',
    'swimming_pool' => 'Swimming Pool',
    'spa_hot_tub' => 'Spa/Hot Tub'
];
?>

<div class="hph-listing-main-body">
    <div class="hph-content-wrapper">
        
        <!-- Property Description -->
        <?php if ($property_description) : ?>
        <section class="hph-section hph-mb-md">
            <div class="hph-section__header hph-mb-md">
                <h2 class="hph-section__title hph-text-xl hph-font-bold">About <?php echo esc_html($property_title); ?></h2>
            </div>
            <div class="hph-section__content hph-text-flow">
                <div class="hph-description-text hph-text-base hph-text-gray-700">
                    <?php echo wp_kses_post($property_description); ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Mobile Agent Card (only visible on mobile) -->
        <?php 
        // Get agent data for mobile card
        $agent_posts = get_field('listing_agent', $listing_id);
        $agent_data = null;

        if (!empty($agent_posts) && is_array($agent_posts)) {
            $agent_post = $agent_posts[0];
            
            if ($agent_post && isset($agent_post->ID)) {
                $agent_id = $agent_post->ID;
                $first_name = get_field('first_name', $agent_id) ?: '';
                $last_name = get_field('last_name', $agent_id) ?: '';
                $full_name = trim($first_name . ' ' . $last_name) ?: get_the_title($agent_id);
                
                $agent_data = [
                    'id' => $agent_id,
                    'name' => $full_name,
                    'title' => get_field('title', $agent_id) ?: 'REALTOR®',
                    'phone' => get_field('phone', $agent_id),
                    'email' => get_field('email', $agent_id),
                    'photo' => get_field('profile_photo', $agent_id),
                ];
            }
        }
        
        if ($agent_data) : ?>
        <div class="hph-mobile-agent-card">
            <div class="card-content">
                <div class="agent-photo">
                    <?php 
                    $agent_photo = null;
                    if ($agent_data['photo']) {
                        if (is_array($agent_data['photo'])) {
                            $agent_photo = $agent_data['photo']['sizes']['medium'] ?? $agent_data['photo']['url'];
                        } elseif (is_numeric($agent_data['photo'])) {
                            $agent_photo = wp_get_attachment_image_url($agent_data['photo'], 'medium');
                        }
                    }
                    
                    if ($agent_photo): ?>
                        <img src="<?php echo esc_url($agent_photo); ?>" alt="<?php echo esc_attr($agent_data['name']); ?>">
                    <?php else: ?>
                        <div class="agent-photo-placeholder">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h3 class="agent-name"><?php echo esc_html($agent_data['name']); ?></h3>
                <div class="agent-title"><?php echo esc_html($agent_data['title']); ?></div>
                
                <div class="agent-contact">
                    <?php if ($agent_data['phone']): ?>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <a href="tel:<?php echo esc_attr($agent_data['phone']); ?>"><?php echo esc_html($agent_data['phone']); ?></a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($agent_data['email']): ?>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>"><?php echo esc_html($agent_data['email']); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="agent-actions">
                    <?php if ($agent_data['phone']): ?>
                        <a href="tel:<?php echo esc_attr($agent_data['phone']); ?>" class="agent-btn">
                            <i class="fas fa-phone"></i>
                            <span>Call Now</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($agent_data['email']): ?>
                        <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>" class="agent-btn agent-btn--secondary">
                            <i class="fas fa-envelope"></i>
                            <span>Send Email</span>
                        </a>
                    <?php endif; ?>
                    
                    <button type="button" class="agent-btn agent-btn--schedule" data-modal-trigger="agent-contact">
                        <i class="fas fa-calendar-check"></i>
                        <span>Schedule a Showing</span>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Property Details - Modernized Component -->
        <?php get_template_part('template-parts/listing/details', null, ['listing_id' => $listing_id, 'fields' => $fields]); ?>
        
        <!-- Property Features -->
        <?php if (!empty($interior_features) || !empty($exterior_features) || !empty($appliances)) : ?>
        <section class="hph-section hph-mb-md">
            <div class="hph-section__header hph-mb-md">
                <h2 class="hph-section__title hph-text-xl hph-font-bold">Features & Amenities</h2>
            </div>
            
            <div class="hph-features-grid hph-grid hph-grid-cols-1 sm:hph-grid-cols-2 lg:hph-grid-cols-3 gap-responsive">
                
                <?php if (!empty($interior_features)) : ?>
                <div class="hph-feature-group">
                    <h3 class="hph-feature-group__title text-responsive-base hph-font-semibold hph-mb-md hph-flex hph-items-center hph-gap-sm">
                        <i class="hph-feature-icon fas fa-home hph-text-primary"></i>
                        Interior Features
                    </h3>
                    <ul class="hph-feature-list hph-space-y-sm">
                        <?php foreach ($interior_features as $feature) : ?>
                        <li class="hph-feature-item hph-flex hph-items-center hph-gap-sm">
                            <i class="hph-feature-icon fas fa-check hph-text-success hph-text-sm"></i>
                            <span class="text-responsive-sm"><?php echo esc_html($feature_labels[$feature] ?? ucwords(str_replace('_', ' ', $feature))); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($exterior_features)) : ?>
                <div class="hph-feature-group">
                    <h3 class="hph-feature-group__title text-responsive-base hph-font-semibold hph-mb-md hph-flex hph-items-center hph-gap-sm">
                        <i class="hph-feature-icon fas fa-tree hph-text-primary"></i>
                        Exterior Features
                    </h3>
                    <ul class="hph-feature-list hph-space-y-sm">
                        <?php foreach ($exterior_features as $feature) : ?>
                        <li class="hph-feature-item hph-flex hph-items-center hph-gap-sm">
                            <i class="hph-feature-icon fas fa-check hph-text-success hph-text-sm"></i>
                            <span class="text-responsive-sm"><?php echo esc_html($feature_labels[$feature] ?? ucwords(str_replace('_', ' ', $feature))); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($appliances)) : ?>
                <div class="hph-feature-group">
                    <h3 class="hph-feature-group__title hph-text-lg hph-font-semibold hph-mb-md hph-flex hph-items-center hph-gap-sm">
                        <i class="fas fa-blender hph-text-primary"></i>
                        Appliances
                    </h3>
                    <ul class="hph-feature-list hph-space-y-sm">
                        <?php foreach ($appliances as $appliance) : ?>
                        <li class="hph-feature-item hph-flex hph-items-center hph-gap-sm">
                            <i class="fas fa-check hph-text-success hph-text-sm"></i>
                            <span><?php echo esc_html(ucwords(str_replace('_', ' ', $appliance))); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Financial Information -->
        <?php if ($annual_taxes || $hoa_fee) : ?>
        <section class="hph-section hph-mb-lg">
            <div class="hph-section__header hph-mb-md">
                <h2 class="hph-section__title hph-text-xl hph-font-bold">Financial Information</h2>
            </div>
            
            <div class="hph-financial-grid hph-grid hph-grid-cols-1 hph-grid-cols-md-2 hph-gap-lg">
                
                <?php if ($annual_taxes) : ?>
                <div class="hph-financial-item hph-p-lg hph-bg-gray-50 hph-rounded-lg">
                    <h3 class="hph-text-lg hph-font-semibold hph-mb-md">Property Taxes</h3>
                    <dl class="hph-space-y-sm">
                        <div class="hph-flex hph-justify-between">
                            <dt class="hph-text-gray-600">Annual Taxes:</dt>
                            <dd class="hph-font-semibold">$<?php echo esc_html(number_format($annual_taxes)); ?></dd>
                        </div>
                        <?php if ($tax_year) : ?>
                        <div class="hph-flex hph-justify-between">
                            <dt class="hph-text-gray-600">Tax Year:</dt>
                            <dd><?php echo esc_html($tax_year); ?></dd>
                        </div>
                        <?php endif; ?>
                        <div class="hph-flex hph-justify-between">
                            <dt class="hph-text-gray-600">Monthly Est.:</dt>
                            <dd>$<?php echo esc_html(number_format($annual_taxes / 12)); ?></dd>
                        </div>
                    </dl>
                </div>
                <?php endif; ?>
                
                <?php if ($hoa_fee) : ?>
                <div class="hph-financial-item hph-p-lg hph-bg-gray-50 hph-rounded-lg">
                    <h3 class="hph-text-lg hph-font-semibold hph-mb-md">HOA Information</h3>
                    <dl class="hph-space-y-sm">
                        <div class="hph-flex hph-justify-between">
                            <dt class="hph-text-gray-600">HOA Fee:</dt>
                            <dd class="hph-font-semibold">$<?php echo esc_html(number_format($hoa_fee)); ?></dd>
                        </div>
                        <?php if ($hoa_frequency) : ?>
                        <div class="hph-flex hph-justify-between">
                            <dt class="hph-text-gray-600">Frequency:</dt>
                            <dd><?php echo esc_html(ucfirst($hoa_frequency)); ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($hoa_includes)) : ?>
                        <div class="hph-mt-md">
                            <dt class="hph-text-gray-600 hph-mb-sm">HOA Includes:</dt>
                            <dd>
                                <ul class="hph-list-disc hph-pl-lg hph-space-y-xs">
                                    <?php foreach ($hoa_includes as $item) : ?>
                                    <li class="hph-text-sm"><?php echo esc_html(ucwords(str_replace('_', ' ', $item))); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
                <?php endif; ?>
                
            </div>
        </section>
        <?php endif; ?>
        
    </div>
</div>

<script>
function toggleDescription() {
    const content = document.querySelector('.hph-description__content');
    const button = event.currentTarget;
    const showMore = button.querySelector('.show-more');
    const showLess = button.querySelector('.show-less');
    const icon = button.querySelector('i');
    
    content.classList.toggle('expanded');
    
    if (content.classList.contains('expanded')) {
        showMore.style.display = 'none';
        showLess.style.display = 'inline';
        icon.style.transform = 'rotate(180deg)';
    } else {
        showMore.style.display = 'inline';
        showLess.style.display = 'none';
        icon.style.transform = 'rotate(0)';
    }
}
</script>
