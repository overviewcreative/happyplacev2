<?php
/**
 * View Listing Details
 * 
 * Read-only listing details view for dashboard
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get listing data
$listing_id = get_query_var('dashboard_id', 0);
if (!$listing_id) {
    echo '<div class="alert alert-warning">' . __('No listing specified.', 'happy-place') . '</div>';
    return;
}

$listing = get_post($listing_id);
if (!$listing || $listing->post_type !== 'listing') {
    echo '<div class="alert alert-warning">' . __('Listing not found.', 'happy-place') . '</div>';
    return;
}

// Get all listing data
$listing_data = [
    'price' => get_field('price', $listing_id),
    'property_status' => get_field('property_status', $listing_id),
    'mls_number' => get_field('mls_number', $listing_id),
    'bedrooms' => get_field('bedrooms', $listing_id),
    'bathrooms' => get_field('bathrooms', $listing_id),
    'square_feet' => get_field('square_feet', $listing_id),
    'year_built' => get_field('year_built', $listing_id),
    'street_address' => get_field('street_address', $listing_id),
    'city' => get_field('city', $listing_id),
    'state' => get_field('state', $listing_id),
    'zip_code' => get_field('zip_code', $listing_id),
    'listing_agent' => get_field('listing_agent', $listing_id),
];

$agent = $listing_data['listing_agent'] ? get_post($listing_data['listing_agent']) : null;
$dashboard = \HappyPlace\Dashboard\Frontend_Admin_Dashboard::get_instance();
?>

<div class="view-listing">
    <!-- Listing Header -->
    <div class="listing-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="listing-title"><?php echo esc_html($listing->post_title); ?></h2>
                <p class="listing-address">
                    <?php echo esc_html($listing_data['street_address']); ?>, 
                    <?php echo esc_html($listing_data['city']); ?>, 
                    <?php echo esc_html($listing_data['state']); ?> 
                    <?php echo esc_html($listing_data['zip_code']); ?>
                </p>
                <?php if ($listing_data['mls_number']): ?>
                    <p class="listing-mls">MLS# <?php echo esc_html($listing_data['mls_number']); ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <div class="listing-price"><?php echo $listing_data['price'] ? '$' . number_format($listing_data['price']) : __('Price not set', 'happy-place'); ?></div>
                <span class="listing-status-badge status-<?php echo esc_attr($listing_data['property_status']); ?>">
                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $listing_data['property_status']))); ?>
                </span>
            </div>
        </div>
        
        <div class="listing-actions">
            <a href="<?php echo get_permalink($listing_id); ?>" target="_blank" class="btn btn-outline-primary">
                <span class="hph-icon-external-link"></span>
                <?php _e('View Live', 'happy-place'); ?>
            </a>
            <?php if ($dashboard->user_can('manage_all_listings') || 
                      ($dashboard->user_can('manage_own_listings') && $listing_data['listing_agent'] == get_current_user_id())): ?>
                <a href="<?php echo esc_url(add_query_arg(['dashboard_action' => 'edit', 'dashboard_id' => $listing_id], get_permalink())); ?>" 
                   class="btn btn-primary">
                    <span class="hph-icon-edit"></span>
                    <?php _e('Edit Listing', 'happy-place'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Property Images -->
    <?php 
    $featured_image = get_the_post_thumbnail($listing_id, 'large');
    $gallery_images = get_field('gallery_images', $listing_id);
    if ($featured_image || $gallery_images): 
    ?>
        <div class="listing-images">
            <div class="row">
                <?php if ($featured_image): ?>
                    <div class="col-md-8">
                        <div class="featured-image">
                            <?php echo $featured_image; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="gallery-images">
                            <?php 
                            if ($gallery_images) {
                                $count = 0;
                                foreach ($gallery_images as $image_id) {
                                    if ($count < 4) {
                                        echo '<div class="gallery-item">' . wp_get_attachment_image($image_id, 'medium') . '</div>';
                                        $count++;
                                    }
                                }
                                if (count($gallery_images) > 4) {
                                    echo '<div class="gallery-more">+' . (count($gallery_images) - 4) . ' more</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-12">
                        <div class="no-image-placeholder">
                            <span class="hph-icon-camera"></span>
                            <p><?php _e('No images available', 'happy-place'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Property Details -->
    <div class="listing-details">
        <div class="row">
            <!-- Basic Facts -->
            <div class="col-lg-8">
                <div class="details-section">
                    <h3><?php _e('Property Details', 'happy-place'); ?></h3>
                    <div class="details-grid">
                        <?php if ($listing_data['bedrooms']): ?>
                            <div class="detail-item">
                                <strong><?php _e('Bedrooms', 'happy-place'); ?></strong>
                                <span><?php echo esc_html($listing_data['bedrooms']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($listing_data['bathrooms']): ?>
                            <div class="detail-item">
                                <strong><?php _e('Bathrooms', 'happy-place'); ?></strong>
                                <span><?php echo esc_html($listing_data['bathrooms']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($listing_data['square_feet']): ?>
                            <div class="detail-item">
                                <strong><?php _e('Square Feet', 'happy-place'); ?></strong>
                                <span><?php echo number_format($listing_data['square_feet']); ?> sq ft</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($listing_data['year_built']): ?>
                            <div class="detail-item">
                                <strong><?php _e('Year Built', 'happy-place'); ?></strong>
                                <span><?php echo esc_html($listing_data['year_built']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($listing->post_content): ?>
                    <div class="details-section">
                        <h3><?php _e('Description', 'happy-place'); ?></h3>
                        <div class="listing-description">
                            <?php echo wpautop(wp_kses_post($listing->post_content)); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Agent Info -->
            <div class="col-lg-4">
                <?php if ($agent): ?>
                    <div class="agent-card">
                        <h3><?php _e('Listing Agent', 'happy-place'); ?></h3>
                        <div class="agent-info">
                            <?php 
                            $agent_photo = get_the_post_thumbnail($agent->ID, 'thumbnail');
                            if ($agent_photo): 
                            ?>
                                <div class="agent-photo"><?php echo $agent_photo; ?></div>
                            <?php endif; ?>
                            <div class="agent-details">
                                <h4><?php echo esc_html($agent->post_title); ?></h4>
                                <?php 
                                $agent_phone = get_field('phone', $agent->ID);
                                $agent_email = get_field('email', $agent->ID);
                                ?>
                                <?php if ($agent_phone): ?>
                                    <p><strong><?php _e('Phone:', 'happy-place'); ?></strong> <?php echo esc_html($agent_phone); ?></p>
                                <?php endif; ?>
                                <?php if ($agent_email): ?>
                                    <p><strong><?php _e('Email:', 'happy-place'); ?></strong> <?php echo esc_html($agent_email); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.listing-header {
    background: var(--hph-white);
    border-radius: var(--hph-card-radius);
    padding: var(--hph-space-lg);
    margin-bottom: var(--hph-space-lg);
    box-shadow: var(--hph-shadow-sm);
}

.listing-title {
    font-size: var(--hph-text-2xl);
    font-weight: var(--hph-font-bold);
    margin: 0 0 var(--hph-space-xs) 0;
}

.listing-address {
    font-size: var(--hph-text-lg);
    color: var(--hph-text-muted);
    margin: 0 0 var(--hph-space-xs) 0;
}

.listing-mls {
    font-size: var(--hph-text-sm);
    color: var(--hph-text-muted);
    margin: 0;
}

.listing-price {
    font-size: var(--hph-text-2xl);
    font-weight: var(--hph-font-bold);
    color: var(--hph-success);
    margin-bottom: var(--hph-space-xs);
}

.listing-actions {
    margin-top: var(--hph-space-md);
    display: flex;
    gap: var(--hph-space-sm);
}

.listing-images {
    background: var(--hph-white);
    border-radius: var(--hph-card-radius);
    padding: var(--hph-space-lg);
    margin-bottom: var(--hph-space-lg);
    box-shadow: var(--hph-shadow-sm);
}

.featured-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: var(--hph-border-radius);
}

.gallery-images {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--hph-space-sm);
    height: 400px;
}

.gallery-item {
    border-radius: var(--hph-border-radius);
    overflow: hidden;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-more {
    background: var(--hph-gray-800);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--hph-font-bold);
    border-radius: var(--hph-border-radius);
}

.no-image-placeholder {
    text-align: center;
    padding: var(--hph-space-xl);
    background: var(--hph-gray-100);
    border-radius: var(--hph-border-radius);
}

.no-image-placeholder span {
    font-size: 48px;
    opacity: 0.3;
}

.listing-details {
    background: var(--hph-white);
    border-radius: var(--hph-card-radius);
    padding: var(--hph-space-lg);
    box-shadow: var(--hph-shadow-sm);
}

.details-section {
    margin-bottom: var(--hph-space-xl);
}

.details-section h3 {
    margin-bottom: var(--hph-space-md);
    font-size: var(--hph-text-xl);
    font-weight: var(--hph-font-semibold);
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--hph-space-md);
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--hph-space-sm);
    background: var(--hph-gray-50);
    border-radius: var(--hph-border-radius);
}

.agent-card {
    background: var(--hph-primary-light);
    border-radius: var(--hph-card-radius);
    padding: var(--hph-space-lg);
    margin-bottom: var(--hph-space-lg);
}

.agent-card h3 {
    margin-bottom: var(--hph-space-md);
    color: var(--hph-primary-dark);
}

.agent-info {
    display: flex;
    gap: var(--hph-space-md);
    align-items: flex-start;
}

.agent-photo img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
}

.agent-details h4 {
    margin: 0 0 var(--hph-space-sm) 0;
    color: var(--hph-primary-dark);
}

.agent-details p {
    margin-bottom: var(--hph-space-xs);
    font-size: var(--hph-text-sm);
}

@media (max-width: 767px) {
    .listing-actions {
        flex-direction: column;
    }
    
    .featured-image img,
    .gallery-images {
        height: 250px;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
    
    .agent-info {
        flex-direction: column;
        text-align: center;
    }
}
</style>