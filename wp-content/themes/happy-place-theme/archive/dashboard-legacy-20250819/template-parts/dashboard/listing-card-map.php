<?php
/**
 * Listing Card - Map View
 * 
 * Compact card for map sidebar listing
 */

$listing_id = $args['listing_id'] ?? get_the_ID();
$price = get_field('price', $listing_id);
$status = get_field('property_status', $listing_id);
$address = get_field('street_address', $listing_id);
$city = get_field('city', $listing_id);
$bedrooms = get_field('bedrooms', $listing_id);
$bathrooms = get_field('bathrooms', $listing_id);
$square_feet = get_field('square_feet', $listing_id);
$featured_image = get_the_post_thumbnail_url($listing_id, 'thumbnail');
$lat = get_field('latitude', $listing_id);
$lng = get_field('longitude', $listing_id);

// Status color mapping
$status_colors = [
    'active' => 'success',
    'pending' => 'warning', 
    'sold' => 'info',
    'coming_soon' => 'primary',
    'withdrawn' => 'danger'
];
$status_color = $status_colors[$status] ?? 'secondary';
?>

<div class="listing-card-map" 
     data-listing-id="<?php echo esc_attr($listing_id); ?>"
     data-lat="<?php echo esc_attr($lat); ?>"
     data-lng="<?php echo esc_attr($lng); ?>">
    <div class="listing-card-inner">
        
        <!-- Listing Image -->
        <div class="listing-image">
            <?php if ($featured_image): ?>
                <img src="<?php echo esc_url($featured_image); ?>" 
                     alt="<?php echo esc_attr(get_the_title($listing_id)); ?>">
            <?php else: ?>
                <div class="image-placeholder">
                    <span class="hph-icon-home"></span>
                </div>
            <?php endif; ?>
            
            <!-- Status Badge -->
            <div class="status-badge status-<?php echo esc_attr($status_color); ?>">
                <?php echo esc_html(substr(ucfirst(str_replace('_', ' ', $status)), 0, 1)); ?>
            </div>
            
            <!-- Map Location Indicator -->
            <?php if ($lat && $lng): ?>
                <div class="map-indicator" 
                     data-action="center-map"
                     title="<?php _e('Center map on this listing', 'happy-place'); ?>">
                    <span class="hph-icon-location"></span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Listing Info -->
        <div class="listing-info">
            <div class="listing-header">
                <h4 class="listing-title">
                    <a href="<?php echo add_query_arg(['dashboard_section' => 'listings', 'action' => 'edit', 'listing_id' => $listing_id], get_permalink()); ?>">
                        <?php echo esc_html(wp_trim_words(get_the_title($listing_id), 4)); ?>
                    </a>
                </h4>
                
                <div class="listing-price">
                    <?php if ($price): ?>
                        <span class="price-amount">$<?php echo number_format($price); ?></span>
                    <?php else: ?>
                        <span class="price-pending"><?php _e('POA', 'happy-place'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="listing-address">
                <span class="hph-icon-location"></span>
                <?php echo esc_html(wp_trim_words($address . ($city ? ', ' . $city : ''), 6)); ?>
            </div>
            
            <!-- Compact Property Details -->
            <div class="property-details">
                <?php if ($bedrooms || $bathrooms || $square_feet): ?>
                    <div class="details-inline">
                        <?php if ($bedrooms): ?>
                            <span class="detail-item">
                                <span class="hph-icon-bed"></span>
                                <?php echo esc_html($bedrooms); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($bathrooms): ?>
                            <span class="detail-item">
                                <span class="hph-icon-bath"></span>
                                <?php echo esc_html($bathrooms); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($square_feet): ?>
                            <span class="detail-item">
                                <span class="hph-icon-ruler"></span>
                                <?php echo esc_html(number_format($square_feet, 0)); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="listing-actions">
            <div class="action-buttons">
                <a href="<?php echo add_query_arg(['dashboard_section' => 'listings', 'action' => 'edit', 'listing_id' => $listing_id], get_permalink()); ?>" 
                   class="action-btn edit-btn" 
                   title="<?php _e('Edit Listing', 'happy-place'); ?>">
                    <span class="hph-icon-edit"></span>
                </a>
                
                <a href="<?php echo get_permalink($listing_id); ?>" 
                   class="action-btn view-btn" 
                   title="<?php _e('View Listing', 'happy-place'); ?>" 
                   target="_blank">
                    <span class="hph-icon-eye"></span>
                </a>
                
                <button type="button" 
                        class="action-btn more-btn"
                        data-bs-toggle="dropdown"
                        title="<?php _e('More Actions', 'happy-place'); ?>">
                    <span class="hph-icon-more"></span>
                </button>
                
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#" data-action="duplicate">
                            <span class="hph-icon-copy"></span>
                            <?php _e('Duplicate', 'happy-place'); ?>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-action="feature">
                            <span class="hph-icon-star"></span>
                            <?php _e('Feature', 'happy-place'); ?>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-action="share">
                            <span class="hph-icon-share"></span>
                            <?php _e('Share', 'happy-place'); ?>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" 
                           href="#" 
                           data-action="delete"
                           data-listing-id="<?php echo esc_attr($listing_id); ?>">
                            <span class="hph-icon-trash"></span>
                            <?php _e('Delete', 'happy-place'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Selection Checkbox -->
        <div class="selection-checkbox">
            <input type="checkbox" 
                   class="listing-checkbox" 
                   value="<?php echo esc_attr($listing_id); ?>"
                   id="listing-map-<?php echo esc_attr($listing_id); ?>">
            <label for="listing-map-<?php echo esc_attr($listing_id); ?>"></label>
        </div>
    </div>
</div>