<?php
/**
 * Listing Details Component
 * Registered as 'listing-details' in component registry
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Extract component args (compatible with hph_component system)
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', []);
$args = wp_parse_args($component_args, [
    'listing_id' => null,
    'sections' => ['basic', 'financial', 'location', 'features'],
    'style' => 'detailed'
]);

// Validate listing ID
$listing_id = $args['listing_id'];
if (!$listing_id) {
    return;
}

// Get listing data using bridge functions
$listing_data = function_exists('hpt_get_listing') ? hpt_get_listing($listing_id) : null;
if (!$listing_data) {
    return;
}
?>

<div class="hph-listing-details hph-listing-details--<?php echo esc_attr($args['style']); ?> hph-bg-white hph-p-xl hph-shadow-sm" 
     data-component="listing-details" 
     data-listing-id="<?php echo esc_attr($listing_id); ?>"
     style="border-radius: var(--hph-card-radius);">
    
    <!-- Basic Information Section -->
    <?php if (in_array('basic', $args['sections'])): ?>
    <div class="details-section basic-info-section hph-mb-xl">
        <h3 class="section-title hph-text-xl hph-font-semibold hph-mb-lg hph-pb-sm" style="color: var(--hph-text-color); border-bottom: 2px solid var(--hph-gray-200);">
            <i class="fas fa-home" style="color: var(--hph-primary); margin-right: 8px;"></i>
            Property Details
        </h3>
        
        <div class="details-grid hph-grid hph-gap-lg" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
            
            <?php if (!empty($listing_data['property_type'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">Property Type</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html(hph_format_property_value($listing_data['property_type'])); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['bedrooms'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">Bedrooms</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($listing_data['bedrooms']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['bathrooms'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">Bathrooms</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($listing_data['bathrooms']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['square_feet'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">Square Feet</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);"><?php echo number_format($listing_data['square_feet']); ?> sq ft</span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['year_built'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">Year Built</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($listing_data['year_built']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['lot_size'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">Lot Size</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($listing_data['lot_size']); ?> acres</span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['garage'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">Garage</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($listing_data['garage']); ?></span>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Financial Information Section -->
    <?php if (in_array('financial', $args['sections'])): ?>
    <div class="details-section financial-info-section hph-mb-xl">
        <h3 class="section-title hph-text-xl hph-font-semibold hph-mb-lg hph-pb-sm" style="color: var(--hph-text-color); border-bottom: 2px solid var(--hph-gray-200);">
            <i class="fas fa-dollar-sign" style="color: var(--hph-success); margin-right: 8px;"></i>
            Financial Information
        </h3>
        
        <div class="details-grid hph-grid hph-gap-lg" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
            
            <?php if (!empty($listing_data['price'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">List Price</span>
                <span class="detail-value price hph-text-lg hph-font-semibold" style="color: var(--hph-success);">$<?php echo number_format($listing_data['price']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['hoa_fees'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">HOA Fees</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);">$<?php echo number_format($listing_data['hoa_fees']); ?>/month</span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['property_taxes'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">Property Taxes</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);">$<?php echo number_format($listing_data['property_taxes']); ?>/year</span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['insurance_estimate'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">Insurance Estimate</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);">$<?php echo number_format($listing_data['insurance_estimate']); ?>/year</span>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Location Information Section -->
    <?php if (in_array('location', $args['sections']) && !empty($listing_data['address'])): ?>
    <div class="details-section location-info-section hph-mb-xl">
        <h3 class="section-title hph-text-xl hph-font-semibold hph-mb-lg hph-pb-sm" style="color: var(--hph-text-color); border-bottom: 2px solid var(--hph-gray-200);">
            <i class="fas fa-map-marker-alt" style="color: var(--hph-primary); margin-right: 8px;"></i>
            Location
        </h3>
        
        <div class="details-grid hph-grid hph-gap-lg" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
            
            <div class="detail-item full-width d-flex flex-column hph-gap-xs" style="grid-column: 1 / -1;">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">Address</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);">
                    <?php 
                    if (is_array($listing_data['address'])) {
                        echo esc_html($listing_data['address']['display'] ?? $listing_data['address']['full'] ?? '');
                    } else {
                        echo esc_html($listing_data['address']);
                    }
                    ?>
                </span>
            </div>
            
            <?php if (!empty($listing_data['city'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">City</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($listing_data['city']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['state'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">State</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($listing_data['state']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['zip'])): ?>
            <div class="detail-item d-flex flex-column hph-gap-xs">
                <span class="detail-label hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-text-muted); letter-spacing: 0.025em;">ZIP Code</span>
                <span class="detail-value hph-text-base hph-font-semibold" style="color: var(--hph-text-color);"><?php echo esc_html($listing_data['zip']); ?></span>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Features Section -->
    <?php if (in_array('features', $args['sections']) && !empty($listing_data['features'])): ?>
    <div class="details-section features-section">
        <h3 class="section-title hph-text-xl hph-font-semibold hph-mb-lg hph-pb-sm" style="color: var(--hph-text-color); border-bottom: 2px solid var(--hph-gray-200);">
            <i class="fas fa-star" style="color: var(--hph-warning); margin-right: 8px;"></i>
            Property Features
        </h3>
        
        <div class="features-list hph-grid hph-gap-md" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <?php 
            $features = is_array($listing_data['features']) ? $listing_data['features'] : explode(',', $listing_data['features']);
            foreach ($features as $feature):
                $feature = trim($feature);
                if (!empty($feature)):
            ?>
            <div class="feature-item d-flex align-items-center hph-gap-sm hph-py-sm hph-px-md hph-transition-all" style="background: var(--hph-gray-50); border-radius: var(--hph-radius-md);">
                <i class="fas fa-check" style="color: var(--hph-success); width: 1rem; flex-shrink: 0;"></i>
                <span style="color: var(--hph-text-color);"><?php echo esc_html($feature); ?></span>
            </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<style>
.hph-listing-details {
    background: var(--hph-white);
    border-radius: var(--hph-card-radius);
    padding: var(--hph-card-padding);
    box-shadow: var(--hph-shadow-sm);
    transition: var(--hph-transition-fast);
}

.hph-listing-details:hover {
    box-shadow: var(--hph-shadow-md);
}

/* Section styling */
.details-section:last-child {
    margin-bottom: 0;
}

.section-title {
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 3rem;
    height: 2px;
    background: var(--hph-primary);
}

/* Feature hover effects */
.feature-item:hover {
    background: var(--hph-primary-light);
    transform: translateY(-1px);
    box-shadow: var(--hph-shadow-sm);
}

/* Detail value emphasis */
.detail-value.price {
    position: relative;
}

.detail-value.price::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 100%;
    background: var(--hph-success);
    border-radius: var(--hph-radius-sm);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hph-listing-details {
        padding: var(--hph-space-6);
    }
    
    .details-grid {
        grid-template-columns: 1fr !important;
        gap: var(--hph-gap-md) !important;
    }
    
    .section-title {
        font-size: var(--hph-text-lg) !important;
    }
    
    .features-list {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 480px) {
    .hph-listing-details {
        padding: var(--hph-space-4);
    }
    
    .section-title i {
        display: none;
    }
}
</style>
