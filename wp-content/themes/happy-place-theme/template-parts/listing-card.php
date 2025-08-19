<?php
/**
 * Listing Card Template Part
 *
 * @package HappyPlaceTheme
 */

$listing_id = get_the_ID();
?>

<div class="hph-card hph-listing-card hph-relative hph-overflow-hidden hph-transition-all hph-hover-shadow-md">
    
    <!-- Listing Image -->
    <div class="hph-card-image hph-relative hph-aspect-video hph-overflow-hidden">
        <?php if (has_post_thumbnail()) : ?>
            <a href="<?php the_permalink(); ?>" class="hph-block hph-w-full hph-h-full">
                <?php the_post_thumbnail('medium_large', array(
                    'class' => 'hph-w-full hph-h-full hph-object-cover hph-transition-transform hph-hover-scale-105'
                )); ?>
            </a>
        <?php else : ?>
            <div class="hph-w-full hph-h-full hph-bg-gray-200 hph-flex hph-items-center hph-justify-center">
                <i class="fas fa-home hph-text-4xl hph-text-gray-400"></i>
            </div>
        <?php endif; ?>
        
        <!-- Status Badge -->
        <?php if (function_exists('hpt_get_listing_status')) : 
            $listing_status = hpt_get_listing_status($listing_id);
            if ($listing_status && $listing_status !== 'active') : ?>
                <div class="hph-absolute hph-top-3 hph-left-3 hph-z-10">
                    <span class="hph-badge hph-badge-<?php echo esc_attr($listing_status === 'sold' ? 'success' : ($listing_status === 'pending' ? 'warning' : 'primary')); ?>">
                        <?php echo esc_html(ucfirst($listing_status)); ?>
                    </span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Price Badge -->
        <?php if (function_exists('hpt_get_listing_price')) : 
            $price = hpt_get_listing_price($listing_id);
            if ($price) : ?>
                <div class="hph-absolute hph-top-3 hph-right-3 hph-z-10">
                    <span class="hph-badge hph-badge-dark hph-font-bold">
                        <?php echo esc_html($price); ?>
                    </span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Listing Content -->
    <div class="hph-card-body hph-p-6">
        
        <!-- Title -->
        <h3 class="hph-card-title hph-mb-3">
            <a href="<?php the_permalink(); ?>" class="hph-text-gray-900 hph-hover-text-primary-600 hph-transition-colors">
                <?php the_title(); ?>
            </a>
        </h3>
        
        <!-- Location -->
        <?php if (function_exists('hpt_get_listing_address')) : 
            $address = hpt_get_listing_address($listing_id);
            if ($address) : ?>
                <div class="hph-flex hph-items-center hph-gap-2 hph-mb-4 hph-text-gray-600">
                    <i class="fas fa-map-marker-alt hph-text-primary-500"></i>
                    <span class="hph-text-sm"><?php echo esc_html($address); ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Property Details -->
        <div class="hph-flex hph-items-center hph-gap-4 hph-mb-4 hph-text-sm hph-text-gray-600">
            <?php if (function_exists('hpt_get_listing_bedrooms')) : 
                $bedrooms = hpt_get_listing_bedrooms($listing_id);
                if ($bedrooms) : ?>
                    <div class="hph-flex hph-items-center hph-gap-1">
                        <i class="fas fa-bed hph-text-primary-500"></i>
                        <span><?php echo esc_html($bedrooms); ?> <?php esc_html_e('beds', 'happy-place-theme'); ?></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (function_exists('hpt_get_listing_bathrooms')) : 
                $bathrooms = hpt_get_listing_bathrooms($listing_id);
                if ($bathrooms) : ?>
                    <div class="hph-flex hph-items-center hph-gap-1">
                        <i class="fas fa-bath hph-text-primary-500"></i>
                        <span><?php echo esc_html($bathrooms); ?> <?php esc_html_e('baths', 'happy-place-theme'); ?></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (function_exists('hpt_get_listing_square_feet')) : 
                $sqft = hpt_get_listing_square_feet($listing_id);
                if ($sqft) : ?>
                    <div class="hph-flex hph-items-center hph-gap-1">
                        <i class="fas fa-ruler hph-text-primary-500"></i>
                        <span><?php echo esc_html(number_format($sqft)); ?> <?php esc_html_e('sqft', 'happy-place-theme'); ?></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Property Type -->
        <?php if (function_exists('hpt_get_listing_property_type')) : 
            $property_type = hpt_get_listing_property_type($listing_id);
            if ($property_type) : ?>
                <div class="hph-mb-4">
                    <span class="hph-badge hph-badge-light hph-text-xs">
                        <?php echo esc_html($property_type); ?>
                    </span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Description -->
        <div class="hph-mb-6">
            <p class="hph-text-gray-700 hph-text-sm hph-line-clamp-2">
                <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
            </p>
        </div>
        
        <!-- Listing Agent -->
        <?php if (function_exists('hpt_get_listing_agent')) : 
            $agent_id = hpt_get_listing_agent($listing_id);
            if ($agent_id && function_exists('hpt_get_agent')) : 
                $agent_data = hpt_get_agent($agent_id);
                if ($agent_data) : ?>
                    <div class="hph-flex hph-items-center hph-gap-3 hph-mb-6 hph-p-3 hph-bg-gray-50 hph-rounded-lg">
                        <?php if ($agent_data['profile_photo']) : ?>
                            <img src="<?php echo esc_url($agent_data['profile_photo']); ?>" 
                                 alt="<?php echo esc_attr($agent_data['name']); ?>" 
                                 class="hph-w-10 hph-h-10 hph-rounded-full hph-object-cover">
                        <?php else : ?>
                            <div class="hph-w-10 hph-h-10 hph-rounded-full hph-bg-primary-100 hph-flex hph-items-center hph-justify-center">
                                <i class="fas fa-user hph-text-primary-600"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="hph-flex-1 hph-min-w-0">
                            <p class="hph-text-sm hph-font-medium hph-text-gray-900 hph-mb-1">
                                <?php echo esc_html($agent_data['name']); ?>
                            </p>
                            <?php if ($agent_data['title']) : ?>
                                <p class="hph-text-xs hph-text-gray-600">
                                    <?php echo esc_html($agent_data['title']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
        
    </div>
    
    <!-- Card Footer -->
    <div class="hph-card-footer hph-p-6 hph-pt-0">
        <div class="hph-flex hph-items-center hph-justify-between hph-gap-3">
            <a href="<?php the_permalink(); ?>" class="hph-btn hph-btn-primary hph-btn-sm hph-flex-1">
                <?php esc_html_e('View Details', 'happy-place-theme'); ?>
                <i class="fas fa-arrow-right hph-ml-2"></i>
            </a>
            
            <div class="hph-flex hph-items-center hph-gap-2">
                <?php 
                // Check if we're in dashboard context and user can edit
                $is_dashboard = (isset($_GET['dashboard_page']) || strpos($_SERVER['REQUEST_URI'], 'agent-dashboard') !== false);
                if ($is_dashboard && current_user_can('edit_post', $listing_id)) : ?>
                    <!-- Edit Button (Dashboard Only) -->
                    <button type="button" 
                            class="hph-btn hph-btn-secondary hph-btn-sm hph-btn-icon edit-listing-btn" 
                            title="<?php esc_attr_e('Edit Listing', 'happy-place-theme'); ?>"
                            data-listing-id="<?php echo esc_attr($listing_id); ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#listingFormModal">
                        <i class="fas fa-edit"></i>
                    </button>
                <?php else : ?>
                    <!-- Favorite Button (Public View) -->
                    <button type="button" class="hph-btn hph-btn-outline hph-btn-sm hph-btn-icon" 
                            title="<?php esc_attr_e('Add to Favorites', 'happy-place-theme'); ?>">
                        <i class="far fa-heart"></i>
                    </button>
                <?php endif; ?>
                
                <!-- Share Button -->
                <button type="button" class="hph-btn hph-btn-outline hph-btn-sm hph-btn-icon" 
                        title="<?php esc_attr_e('Share Property', 'happy-place-theme'); ?>">
                    <i class="fas fa-share-alt"></i>
                </button>
            </div>
        </div>
    </div>
    
</div>