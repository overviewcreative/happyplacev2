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

<div class="hph-listing-details hph-listing-details--<?php echo esc_attr($args['style']); ?>" 
     data-component="listing-details" 
     data-listing-id="<?php echo esc_attr($listing_id); ?>">
    
    <!-- Basic Information Section -->
    <?php if (in_array('basic', $args['sections'])): ?>
    <div class="details-section basic-info-section">
        <h3 class="section-title">Property Details</h3>
        
        <div class="details-grid">
            
            <?php if (!empty($listing_data['property_type'])): ?>
            <div class="detail-item">
                <span class="detail-label">Property Type</span>
                <span class="detail-value"><?php echo esc_html(hph_format_property_value($listing_data['property_type'])); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['bedrooms'])): ?>
            <div class="detail-item">
                <span class="detail-label">Bedrooms</span>
                <span class="detail-value"><?php echo esc_html($listing_data['bedrooms']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['bathrooms'])): ?>
            <div class="detail-item">
                <span class="detail-label">Bathrooms</span>
                <span class="detail-value"><?php echo esc_html($listing_data['bathrooms']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['square_feet'])): ?>
            <div class="detail-item">
                <span class="detail-label">Square Feet</span>
                <span class="detail-value"><?php echo number_format($listing_data['square_feet']); ?> sq ft</span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['year_built'])): ?>
            <div class="detail-item">
                <span class="detail-label">Year Built</span>
                <span class="detail-value"><?php echo esc_html($listing_data['year_built']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['lot_size'])): ?>
            <div class="detail-item">
                <span class="detail-label">Lot Size</span>
                <span class="detail-value"><?php echo esc_html($listing_data['lot_size']); ?> acres</span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['garage'])): ?>
            <div class="detail-item">
                <span class="detail-label">Garage</span>
                <span class="detail-value"><?php echo esc_html($listing_data['garage']); ?></span>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Financial Information Section -->
    <?php if (in_array('financial', $args['sections'])): ?>
    <div class="details-section financial-info-section">
        <h3 class="section-title">Financial Information</h3>
        
        <div class="details-grid">
            
            <?php if (!empty($listing_data['price'])): ?>
            <div class="detail-item">
                <span class="detail-label">List Price</span>
                <span class="detail-value price">$<?php echo number_format($listing_data['price']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['hoa_fees'])): ?>
            <div class="detail-item">
                <span class="detail-label">HOA Fees</span>
                <span class="detail-value">$<?php echo number_format($listing_data['hoa_fees']); ?>/month</span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['property_taxes'])): ?>
            <div class="detail-item">
                <span class="detail-label">Property Taxes</span>
                <span class="detail-value">$<?php echo number_format($listing_data['property_taxes']); ?>/year</span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['insurance_estimate'])): ?>
            <div class="detail-item">
                <span class="detail-label">Insurance Estimate</span>
                <span class="detail-value">$<?php echo number_format($listing_data['insurance_estimate']); ?>/year</span>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Location Information Section -->
    <?php if (in_array('location', $args['sections']) && !empty($listing_data['address'])): ?>
    <div class="details-section location-info-section">
        <h3 class="section-title">Location</h3>
        
        <div class="details-grid">
            
            <div class="detail-item full-width">
                <span class="detail-label">Address</span>
                <span class="detail-value">
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
            <div class="detail-item">
                <span class="detail-label">City</span>
                <span class="detail-value"><?php echo esc_html($listing_data['city']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['state'])): ?>
            <div class="detail-item">
                <span class="detail-label">State</span>
                <span class="detail-value"><?php echo esc_html($listing_data['state']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($listing_data['zip'])): ?>
            <div class="detail-item">
                <span class="detail-label">ZIP Code</span>
                <span class="detail-value"><?php echo esc_html($listing_data['zip']); ?></span>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Features Section -->
    <?php if (in_array('features', $args['sections']) && !empty($listing_data['features'])): ?>
    <div class="details-section features-section">
        <h3 class="section-title">Property Features</h3>
        
        <div class="features-list">
            <?php 
            $features = is_array($listing_data['features']) ? $listing_data['features'] : explode(',', $listing_data['features']);
            foreach ($features as $feature):
                $feature = trim($feature);
                if (!empty($feature)):
            ?>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span><?php echo esc_html($feature); ?></span>
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
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.details-section {
    margin-bottom: 2.5rem;
}

.details-section:last-child {
    margin-bottom: 0;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 0.5rem;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.detail-value {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
}

.detail-value.price {
    color: #059669;
    font-size: 1.125rem;
}

/* Features Section */
.features-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 8px;
}

.feature-item i {
    color: #059669;
    width: 1rem;
    flex-shrink: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hph-listing-details {
        padding: 1.5rem;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .section-title {
        font-size: 1.25rem;
    }
    
    .features-list {
        grid-template-columns: 1fr;
    }
}
</style>
