<?php
/**
 * Listing Card - List View
 * 
 * Horizontal list layout card for listing management
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
$listing_agent = get_field('listing_agent', $listing_id);

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

<div class="listing-card-list" data-listing-id="<?php echo esc_attr($listing_id); ?>">
    <div class="listing-card-inner">
        
        <!-- Selection Checkbox -->
        <div class="listing-selection">
            <input type="checkbox" 
                   class="listing-checkbox" 
                   value="<?php echo esc_attr($listing_id); ?>"
                   id="listing-list-<?php echo esc_attr($listing_id); ?>">
            <label for="listing-list-<?php echo esc_attr($listing_id); ?>"></label>
        </div>
        
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
                <?php echo esc_html(ucfirst(str_replace('_', ' ', $status))); ?>
            </div>
        </div>
        
        <!-- Listing Info -->
        <div class="listing-info">
            <div class="listing-header">
                <div class="title-section">
                    <h3 class="listing-title">
                        <a href="<?php echo add_query_arg(['dashboard_section' => 'listings', 'action' => 'edit', 'listing_id' => $listing_id], get_permalink()); ?>">
                            <?php echo esc_html(get_the_title($listing_id)); ?>
                        </a>
                    </h3>
                    <?php if ($mls_number): ?>
                        <span class="mls-number">MLS# <?php echo esc_html($mls_number); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="price-section">
                    <?php if ($price): ?>
                        <span class="price-amount">$<?php echo number_format($price); ?></span>
                    <?php else: ?>
                        <span class="price-pending"><?php _e('Price on Request', 'happy-place'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="listing-address">
                <span class="hph-icon-location"></span>
                <?php echo esc_html($address . ($city ? ', ' . $city : '')); ?>
            </div>
            
            <!-- Property Details -->
            <div class="property-details">
                <?php if ($bedrooms): ?>
                    <div class="detail-item">
                        <span class="hph-icon-bed"></span>
                        <span><?php echo esc_html($bedrooms); ?> <?php _e('Bedrooms', 'happy-place'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($bathrooms): ?>
                    <div class="detail-item">
                        <span class="hph-icon-bath"></span>
                        <span><?php echo esc_html($bathrooms); ?> <?php _e('Bathrooms', 'happy-place'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($square_feet): ?>
                    <div class="detail-item">
                        <span class="hph-icon-ruler"></span>
                        <span><?php echo number_format($square_feet); ?> <?php _e('Sq Ft', 'happy-place'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Listing Meta -->
            <div class="listing-meta">
                <div class="meta-item">
                    <span class="hph-icon-calendar"></span>
                    <span><?php _e('Created:', 'happy-place'); ?> <?php echo esc_html(get_the_date('M j, Y', $listing_id)); ?></span>
                </div>
                
                <div class="meta-item">
                    <span class="hph-icon-eye"></span>
                    <span><?php echo esc_html(get_post_meta($listing_id, 'view_count', true) ?: '0'); ?> <?php _e('Views', 'happy-place'); ?></span>
                </div>
                
                <?php if ($listing_agent): ?>
                    <?php 
                    $agent_name = get_the_title($listing_agent);
                    if ($agent_name): 
                    ?>
                        <div class="meta-item">
                            <span class="hph-icon-user"></span>
                            <span><?php _e('Agent:', 'happy-place'); ?> <?php echo esc_html($agent_name); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="listing-actions">
            <div class="action-buttons">
                <a href="<?php echo add_query_arg(['dashboard_section' => 'listings', 'action' => 'edit', 'listing_id' => $listing_id], get_permalink()); ?>" 
                   class="btn btn-sm btn-primary">
                    <span class="hph-icon-edit"></span>
                    <?php _e('Edit', 'happy-place'); ?>
                </a>
                
                <a href="<?php echo get_permalink($listing_id); ?>" 
                   class="btn btn-sm btn-outline-secondary" target="_blank">
                    <span class="hph-icon-eye"></span>
                    <?php _e('View', 'happy-place'); ?>
                </a>
                
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary duplicate-btn"
                        data-listing-id="<?php echo esc_attr($listing_id); ?>">
                    <span class="hph-icon-copy"></span>
                    <?php _e('Duplicate', 'happy-place'); ?>
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
                            <a class="dropdown-item" href="#" data-action="share">
                                <span class="hph-icon-share"></span>
                                <?php _e('Share Listing', 'happy-place'); ?>
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
                            <a class="dropdown-item text-danger" 
                               href="#" 
                               data-action="delete"
                               data-listing-id="<?php echo esc_attr($listing_id); ?>">
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