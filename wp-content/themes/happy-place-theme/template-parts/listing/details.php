<?php
/**
 * Listing Details Template Part
 * Simple property details using data from main-body.php
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get data from args passed by main-body.php
$listing_id = $args['listing_id'] ?? get_the_ID();
$fields = $args['fields'] ?? [];

if (!$listing_id || empty($fields)) {
    return;
}
?>

<section class="hph-section hph-mb-md">
    <div class="hph-section__header hph-mb-md">
        <h2 class="hph-section__title hph-text-xl hph-font-bold">Property Details</h2>
    </div>
    
    <!-- Clean Card-Based Layout -->
    <div class="hph-property-details-modern">
        <div class="hph-details-container hph-grid hph-gap-sm" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
            
            <?php if ($fields['property_type']) : ?>
            <div class="hph-detail-card hph-bg-white hph-p-xs hph-rounded hph-shadow-sm hph-border hph-border-gray-100">
                <div class="hph-detail-content hph-text-center">
                    <div class="hph-detail-label hph-text-xs hph-font-medium hph-uppercase hph-tracking-wider hph-mb-xs" style="color: var(--hph-primary);">TYPE</div>
                    <div class="hph-detail-value hph-text-sm hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($fields['property_type']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($fields['property_style']) : ?>
            <div class="hph-detail-card hph-bg-white hph-p-xs hph-rounded hph-shadow-sm hph-border hph-border-gray-100">
                <div class="hph-detail-content hph-text-center">
                    <div class="hph-detail-label hph-text-xs hph-font-medium hph-uppercase hph-tracking-wider hph-mb-xs" style="color: var(--hph-primary);">STYLE</div>
                    <div class="hph-detail-value hph-text-sm hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($fields['property_style']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($fields['bedrooms']) : ?>
            <div class="hph-detail-card hph-bg-white hph-p-xs hph-rounded hph-shadow-sm hph-border hph-border-gray-100">
                <div class="hph-detail-content hph-text-center">
                    <div class="hph-detail-label hph-text-xs hph-font-medium hph-uppercase hph-tracking-wider hph-mb-xs" style="color: var(--hph-primary);">BEDROOMS</div>
                    <div class="hph-detail-value hph-text-sm hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($fields['bedrooms']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($fields['bathrooms_formatted']) : ?>
            <div class="hph-detail-card hph-bg-white hph-p-xs hph-rounded hph-shadow-sm hph-border hph-border-gray-100">
                <div class="hph-detail-content hph-text-center">
                    <div class="hph-detail-label hph-text-xs hph-font-medium hph-uppercase hph-tracking-wider hph-mb-xs" style="color: var(--hph-primary);">BATHROOMS</div>
                    <div class="hph-detail-value hph-text-sm hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($fields['bathrooms_formatted']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($fields['square_feet_formatted']) : ?>
            <div class="hph-detail-card hph-bg-white hph-p-xs hph-rounded hph-shadow-sm hph-border hph-border-gray-100">
                <div class="hph-detail-content hph-text-center">
                    <div class="hph-detail-label hph-text-xs hph-font-medium hph-uppercase hph-tracking-wider hph-mb-xs" style="color: var(--hph-primary);">SQUARE FEET</div>
                    <div class="hph-detail-value hph-text-sm hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($fields['square_feet_formatted']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($fields['garage_spaces']) : ?>
            <div class="hph-detail-card hph-bg-white hph-p-xs hph-rounded hph-shadow-sm hph-border hph-border-gray-100">
                <div class="hph-detail-content hph-text-center">
                    <div class="hph-detail-label hph-text-xs hph-font-medium hph-uppercase hph-tracking-wider hph-mb-xs" style="color: var(--hph-primary);">GARAGE</div>
                    <div class="hph-detail-value hph-text-sm hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html(hph_format_garage_display($fields['garage_spaces'], $fields['garage_type'])); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($fields['stories']) : ?>
            <div class="hph-detail-card hph-bg-white hph-p-xs hph-rounded hph-shadow-sm hph-border hph-border-gray-100">
                <div class="hph-detail-content hph-text-center">
                    <div class="hph-detail-label hph-text-xs hph-font-medium hph-uppercase hph-tracking-wider hph-mb-xs" style="color: var(--hph-primary);">STORIES</div>
                    <div class="hph-detail-value hph-text-sm hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($fields['stories']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($fields['lot_size_formatted']) : ?>
            <div class="hph-detail-card hph-bg-white hph-p-xs hph-rounded hph-shadow-sm hph-border hph-border-gray-100">
                <div class="hph-detail-content hph-text-center">
                    <div class="hph-detail-label hph-text-xs hph-font-medium hph-uppercase hph-tracking-wider hph-mb-xs" style="color: var(--hph-primary);">LOT SIZE</div>
                    <div class="hph-detail-value hph-text-sm hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($fields['lot_size_formatted']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($fields['year_built']) : ?>
            <div class="hph-detail-card hph-bg-white hph-p-xs hph-rounded hph-shadow-sm hph-border hph-border-gray-100">
                <div class="hph-detail-content hph-text-center">
                    <div class="hph-detail-label hph-text-xs hph-font-medium hph-uppercase hph-tracking-wider hph-mb-xs" style="color: var(--hph-primary);">YEAR BUILT</div>
                    <div class="hph-detail-value hph-text-sm hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($fields['year_built']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</section>