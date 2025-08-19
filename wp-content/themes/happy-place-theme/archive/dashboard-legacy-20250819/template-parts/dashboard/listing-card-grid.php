<?php
/**
 * Listing Card - Grid View
 * 
 * Grid layout card for listing management
 */

$listing_id = $args['listing_id'] ?? get_the_ID();
$price = get_field('price', $listing_id);
$status = get_field('property_status', $listing_id);
$address = get_field('street_address', $listing_id);
$city = get_field('city', $listing_id);
$bedrooms = get_field('bedrooms', $listing_id);
$bathrooms = get_field('bathrooms', $listing_id);
$square_feet = get_field('square_feet', $listing_id);
$featured_image = get_the_post_thumbnail_url($listing_id, 'medium');
$mls_number = get_field('mls_number', $listing_id);

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

<div class="listing-card-grid" data-listing-id="<?php echo esc_attr($listing_id); ?>">
    <div class="listing-card-inner">
        
        <!-- Card Header with Image -->
        <div class="card-header">
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
                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $status))); ?>
                </div>
                
                <!-- Quick Actions Overlay -->
                <div class="card-overlay">
                    <div class="quick-actions">
                        <a href="<?php echo add_query_arg(['dashboard_section' => 'listings', 'action' => 'edit', 'listing_id' => $listing_id], get_permalink()); ?>" 
                           class="action-btn edit-btn" title="<?php _e('Edit Listing', 'happy-place'); ?>">
                            <span class="hph-icon-edit"></span>
                        </a>
                        <a href="<?php echo get_permalink($listing_id); ?>" 
                           class="action-btn view-btn" title="<?php _e('View Listing', 'happy-place'); ?>" target="_blank">
                            <span class="hph-icon-eye"></span>
                        </a>
                        <button type="button" 
                                class="action-btn delete-btn" 
                                data-listing-id="<?php echo esc_attr($listing_id); ?>"
                                title="<?php _e('Delete Listing', 'happy-place'); ?>">
                            <span class="hph-icon-trash"></span>
                        </button>
                    </div>
                </div>
                
                <!-- Selection Checkbox -->
                <div class="selection-checkbox">
                    <input type="checkbox" 
                           class="listing-checkbox" 
                           value="<?php echo esc_attr($listing_id); ?>"
                           id="listing-<?php echo esc_attr($listing_id); ?>">
                    <label for="listing-<?php echo esc_attr($listing_id); ?>"></label>
                </div>
            </div>
        </div>
        
        <!-- Card Content -->
        <div class="card-content">
            <div class="listing-header">
                <h3 class="listing-title">
                    <a href="<?php echo add_query_arg(['dashboard_section' => 'listings', 'action' => 'edit', 'listing_id' => $listing_id], get_permalink()); ?>">
                        <?php echo esc_html(get_the_title($listing_id)); ?>
                    </a>
                </h3>
                <?php if ($mls_number): ?>
                    <span class="mls-number">MLS# <?php echo esc_html($mls_number); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="listing-address">
                <span class="hph-icon-location"></span>
                <?php echo esc_html($address . ($city ? ', ' . $city : '')); ?>
            </div>
            
            <div class="listing-price">
                <?php if ($price): ?>
                    <span class="price-amount">$<?php echo number_format($price); ?></span>
                <?php else: ?>
                    <span class="price-pending"><?php _e('Price on Request', 'happy-place'); ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Property Details -->
            <div class="property-details">
                <?php if ($bedrooms): ?>
                    <div class="detail-item">
                        <span class="hph-icon-bed"></span>
                        <span><?php echo esc_html($bedrooms); ?> <?php _e('bed', 'happy-place'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($bathrooms): ?>
                    <div class="detail-item">
                        <span class="hph-icon-bath"></span>
                        <span><?php echo esc_html($bathrooms); ?> <?php _e('bath', 'happy-place'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($square_feet): ?>
                    <div class="detail-item">
                        <span class="hph-icon-ruler"></span>
                        <span><?php echo number_format($square_feet); ?> <?php _e('sqft', 'happy-place'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Card Footer -->
        <div class="card-footer">
            <div class="listing-meta">
                <span class="created-date">
                    <span class="hph-icon-calendar"></span>
                    <?php echo esc_html(get_the_date('M j, Y', $listing_id)); ?>
                </span>
                <span class="views-count">
                    <span class="hph-icon-eye"></span>
                    <?php echo esc_html(get_post_meta($listing_id, 'view_count', true) ?: '0'); ?> <?php _e('views', 'happy-place'); ?>
                </span>
            </div>
            
            <div class="card-actions">
                <div class="action-buttons">
                    <button type="button" 
                            class="btn btn-sm btn-outline-primary duplicate-btn"
                            data-listing-id="<?php echo esc_attr($listing_id); ?>"
                            title="<?php _e('Duplicate Listing', 'happy-place'); ?>">
                        <span class="hph-icon-copy"></span>
                    </button>
                    
                    <button type="button" 
                            class="btn btn-sm btn-outline-secondary share-btn"
                            data-listing-id="<?php echo esc_attr($listing_id); ?>"
                            title="<?php _e('Share Listing', 'happy-place'); ?>">
                        <span class="hph-icon-share"></span>
                    </button>
                    
                    <div class="dropdown">
                        <button type="button" 
                                class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                data-bs-toggle="dropdown">
                            <span class="hph-icon-more"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#" data-action="feature">
                                    <span class="hph-icon-star"></span>
                                    <?php _e('Feature Listing', 'happy-place'); ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-action="analytics">
                                    <span class="hph-icon-chart"></span>
                                    <?php _e('View Analytics', 'happy-place'); ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-action="export">
                                    <span class="hph-icon-download"></span>
                                    <?php _e('Export Data', 'happy-place'); ?>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" data-action="delete">
                                    <span class="hph-icon-trash"></span>
                                    <?php _e('Delete Listing', 'happy-place'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>