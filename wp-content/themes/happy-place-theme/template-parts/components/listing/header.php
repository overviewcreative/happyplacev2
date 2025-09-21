<?php
/**
 * Listing Header Component
 * Displays property title, price, status, address and basic info
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get component args (WordPress standard way)
$args = get_query_var('args', []);
if (empty($args) || !isset($args['listing_data'])) {
    return;
}

$args = wp_parse_args($args, [
    'listing_data' => null,
    'show_price' => true,
    'show_status' => true,
    'show_address' => true,
    'show_mls' => true,
    'show_actions' => true,
    'style' => 'default'
]);

$listing = $args['listing_data'];
?>

<div class="hph-listing-header hph-listing-header--<?php echo esc_attr($args['style']); ?>">
    
    <!-- Title and Price -->
    <div class="hph-listing-header__main hph-flex hph-justify-between hph-items-start hph-mb-4">
        <div class="hph-listing-header__title-section">
            <h1 class="hph-listing-header__title hph-text-3xl hph-font-bold hph-text-gray-900 hph-mb-2">
                <?php echo esc_html($listing['title']); ?>
            </h1>
            
            <?php if ($args['show_address'] && !empty($listing['address'])): ?>
            <div class="hph-listing-header__address hph-text-lg hph-text-gray-600 hph-mb-2">
                <i class="hph-icon hph-icon-map-pin" aria-hidden="true"></i>
                <?php echo esc_html($listing['address']['display'] ?? $listing['address']['full'] ?? ''); ?>
            </div>
            <?php endif; ?>
            
            <div class="hph-listing-header__meta hph-flex hph-items-center hph-gap-4 hph-text-sm hph-text-gray-500">
                <?php if ($args['show_mls'] && !empty($listing['mls_number'])): ?>
                <span class="hph-mls">
                    <?php _e('MLS#', 'happy-place-theme'); ?> <?php echo esc_html($listing['mls_number']); ?>
                </span>
                <?php endif; ?>
                
                <span class="hph-listing-id">
                    <?php _e('ID:', 'happy-place-theme'); ?> <?php echo esc_html($listing['id']); ?>
                </span>
                
                <?php if (!empty($listing['date_created'])): ?>
                <span class="hph-listed-date">
                    <?php _e('Listed:', 'happy-place-theme'); ?> 
                    <?php echo date_i18n(get_option('date_format'), strtotime($listing['date_created'])); ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($args['show_price'] && !empty($listing['price'])): ?>
        <div class="hph-listing-header__price-section hph-text-right">
            <div class="hph-listing-price hph-text-4xl hph-font-bold hph-text-primary-600 hph-mb-1">
                $<?php echo number_format($listing['price']); ?>
            </div>
            
            <?php if (!empty($listing['square_feet'])): ?>
            <div class="hph-price-per-sqft hph-text-sm hph-text-gray-600">
                $<?php echo number_format(round($listing['price'] / $listing['square_feet'])); ?>/sqft
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Property Stats -->
    <div class="hph-listing-header__stats hph-flex hph-items-center hph-gap-6 hph-py-4 hph-border-t hph-border-b hph-border-gray-200 hph-mb-4">
        
        <?php if (!empty($listing['bedrooms'])): ?>
        <div class="hph-stat hph-flex hph-items-center hph-gap-2">
            <i class="hph-icon hph-icon-bed hph-text-gray-400" aria-hidden="true"></i>
            <span class="hph-stat__value hph-font-semibold"><?php echo esc_html($listing['bedrooms']); ?></span>
            <span class="hph-stat__label hph-text-gray-600"><?php _e('Beds', 'happy-place-theme'); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($listing['bathrooms'])): ?>
        <div class="hph-stat hph-flex hph-items-center hph-gap-2">
            <i class="hph-icon hph-icon-bath hph-text-gray-400" aria-hidden="true"></i>
            <span class="hph-stat__value hph-font-semibold"><?php echo esc_html($listing['bathrooms']); ?></span>
            <span class="hph-stat__label hph-text-gray-600"><?php _e('Baths', 'happy-place-theme'); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($listing['square_feet'])): ?>
        <div class="hph-stat hph-flex hph-items-center hph-gap-2">
            <i class="hph-icon hph-icon-home hph-text-gray-400" aria-hidden="true"></i>
            <span class="hph-stat__value hph-font-semibold"><?php echo number_format($listing['square_feet']); ?></span>
            <span class="hph-stat__label hph-text-gray-600"><?php _e('Sq Ft', 'happy-place-theme'); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($listing['lot_size'])): ?>
        <div class="hph-stat hph-flex hph-items-center hph-gap-2">
            <i class="hph-icon hph-icon-maximize hph-text-gray-400" aria-hidden="true"></i>
            <span class="hph-stat__value hph-font-semibold"><?php echo esc_html($listing['lot_size']); ?></span>
            <span class="hph-stat__label hph-text-gray-600"><?php _e('Acres', 'happy-place-theme'); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($listing['year_built'])): ?>
        <div class="hph-stat hph-flex hph-items-center hph-gap-2">
            <i class="hph-icon hph-icon-calendar hph-text-gray-400" aria-hidden="true"></i>
            <span class="hph-stat__value hph-font-semibold"><?php echo esc_html($listing['year_built']); ?></span>
            <span class="hph-stat__label hph-text-gray-600"><?php _e('Built', 'happy-place-theme'); ?></span>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Status and Actions -->
    <div class="hph-listing-header__actions hph-flex hph-justify-between hph-items-center">
        
        <?php if ($args['show_status'] && !empty($listing['listing_status'])): ?>
        <div class="hph-listing-status">
            <span class="hph-badge hph-badge--<?php echo esc_attr(strtolower($listing['listing_status'])); ?>">
                <?php echo esc_html(ucfirst($listing['listing_status'])); ?>
            </span>
        </div>
        <?php endif; ?>
        
        <?php if ($args['show_actions']): ?>
        <div class="hph-listing-actions hph-flex hph-items-center hph-gap-3">
            <button type="button" class="hph-btn hph-btn--outline hph-btn--sm" data-action="favorite" data-listing-id="<?php echo esc_attr($listing['id']); ?>">
                <i class="hph-icon hph-icon-heart" aria-hidden="true"></i>
                <?php _e('Save', 'happy-place-theme'); ?>
            </button>
            
            <button type="button" class="hph-btn hph-btn--outline hph-btn--sm" data-action="share" data-listing-id="<?php echo esc_attr($listing['id']); ?>">
                <i class="hph-icon hph-icon-share" aria-hidden="true"></i>
                <?php _e('Share', 'happy-place-theme'); ?>
            </button>
            
            <button type="button" class="hph-btn hph-btn--outline hph-btn--sm" data-action="print" data-listing-id="<?php echo esc_attr($listing['id']); ?>">
                <i class="hph-icon hph-icon-printer" aria-hidden="true"></i>
                <?php _e('Print', 'happy-place-theme'); ?>
            </button>
        </div>
        <?php endif; ?>
        
    </div>
    
</div>
