<?php
/**
 * Listing Card Component - Modern Property Card Display
 * 
 * @package HappyPlaceTheme
 * @subpackage Components
 * @since 3.0.0
 * 
 * Versatile listing card with multiple display modes:
 * - Grid layout (default)
 * - List layout (horizontal)
 * - Map popup layout (compact)
 * - Dashboard layout (with actions)
 * - Featured layout (highlighted)
 * 
 * Args:
 * - listing_id: int
 * - display_mode: 'grid' | 'list' | 'map' | 'dashboard' | 'featured'
 * - size: 'sm' | 'md' | 'lg'
 * - show_actions: boolean
 * - show_stats: boolean
 * - show_agent: boolean
 * - show_favorite: boolean
 * - compact: boolean
 */

// Get component args with defaults
$component_args = get_query_var('component_args', array());
$defaults = array(
    'listing_id' => get_the_ID(),
    'display_mode' => 'grid',
    'size' => 'md',
    'show_actions' => false,
    'show_stats' => true,
    'show_agent' => false,
    'show_favorite' => true,
    'compact' => false
);

$config = wp_parse_args($component_args, $defaults);
$listing_id = $config['listing_id'];

if (!$listing_id) {
    return;
}

// Verify this is a listing post
$post = get_post($listing_id);
if (!$post || $post->post_type !== 'listing') {
    return;
}

// Null-safe data fetching with bridge functions
$listing_data = array();

// Basic listing information with null handlers
$listing_data['title'] = get_the_title($listing_id) ?: 'Property Listing';
$listing_data['url'] = get_permalink($listing_id) ?: '#';

// Address components with null handlers
$listing_data['address'] = function_exists('hpt_get_listing_address') ? 
                          hpt_get_listing_address($listing_id, 'street') : $listing_data['title'];
$listing_data['city'] = function_exists('hpt_get_listing_city') ? 
                       hpt_get_listing_city($listing_id) : '';
$listing_data['state'] = function_exists('hpt_get_listing_state') ? 
                        hpt_get_listing_state($listing_id) : '';

// Price data with fallbacks
$listing_data['price'] = function_exists('hpt_get_listing_price_formatted') ? 
                        hpt_get_listing_price_formatted($listing_id) : 'Contact for Price';
$listing_data['price_raw'] = function_exists('hpt_get_listing_price_raw') ? 
                            hpt_get_listing_price_raw($listing_id) : null;

// Property details with defaults
$listing_data['status'] = function_exists('hpt_get_listing_status') ? 
                         hpt_get_listing_status($listing_id) : 'Active';
$listing_data['bedrooms'] = function_exists('hpt_get_listing_bedrooms') ? 
                           hpt_get_listing_bedrooms($listing_id) : '';
$listing_data['bathrooms'] = function_exists('hpt_get_listing_bathrooms') ? 
                            hpt_get_listing_bathrooms($listing_id) : '';
$listing_data['sqft'] = function_exists('hpt_get_listing_square_feet') ? 
                       hpt_get_listing_square_feet($listing_id) : '';
$listing_data['property_type'] = function_exists('hpt_get_listing_property_type') ? 
                                hpt_get_listing_property_type($listing_id) : 'Residential';

// Status flags
$listing_data['featured'] = function_exists('hpt_is_listing_featured') ? 
                           hpt_is_listing_featured($listing_id) : false;
$listing_data['is_favorite'] = function_exists('hpt_is_listing_favorite') ? 
                               hpt_is_listing_favorite($listing_id, get_current_user_id()) : false;

// Agent information
$listing_data['agent_id'] = function_exists('hpt_get_listing_agent') ? 
                           hpt_get_listing_agent($listing_id) : null;
$listing_data['agent_name'] = '';
if ($listing_data['agent_id'] && function_exists('hpt_get_agent_name')) {
    $listing_data['agent_name'] = hpt_get_agent_name($listing_data['agent_id']);
}

// Image handling with multiple fallbacks
$listing_data['image_url'] = '';
$listing_data['image_alt'] = $listing_data['title'];

if (has_post_thumbnail($listing_id)) {
    $listing_data['image_url'] = get_the_post_thumbnail_url($listing_id, 'medium_large');
} elseif (function_exists('hpt_get_listing_gallery')) {
    $gallery = hpt_get_listing_gallery($listing_id);
    if (!empty($gallery) && is_array($gallery)) {
        $listing_data['image_url'] = $gallery[0]['url'] ?? '';
        $listing_data['image_alt'] = $gallery[0]['alt'] ?? $listing_data['title'];
    }
}

// Final fallback to placeholder
if (!$listing_data['image_url']) {
    $listing_data['image_url'] = get_template_directory_uri() . '/assets/images/listing-placeholder.jpg';
}

// Format location string
$location_parts = array_filter(array($listing_data['city'], $listing_data['state']));
$location_string = implode(', ', $location_parts);

// Build card classes
$card_classes = array(
    'hph-listing-card',
    'hph-listing-card-' . $config['display_mode'],
    'hph-listing-card-' . $config['size'],
    'hph-relative',
    'hph-overflow-hidden',
    'hph-transition-all',
    'hph-duration-300',
    'hph-hover-lift'
);

if ($config['compact']) {
    $card_classes[] = 'hph-listing-card-compact';
}

if ($listing_data['featured']) {
    $card_classes[] = 'hph-listing-card-featured';
}

$card_classes[] = 'hph-listing-status-' . strtolower($listing_data['status']);

// Date information
$listing_date = get_the_date('M j, Y', $listing_id);
$days_on_market = function_exists('hpt_get_listing_days_on_market') ? 
                  hpt_get_listing_days_on_market($listing_id) : null;
?>

<article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" 
         data-listing-id="<?php echo esc_attr($listing_id); ?>"
         data-status="<?php echo esc_attr($listing_data['status']); ?>"
         data-price="<?php echo esc_attr($listing_data['price_raw'] ?: 0); ?>">
    
    <?php if ($config['display_mode'] === 'list'): ?>
    <!-- List Layout -->
    <div class="hph-listing-card-inner hph-flex hph-gap-lg hph-h-full">
        
        <!-- Image Section -->
        <div class="hph-listing-image-section hph-flex-shrink-0">
            <a href="<?php echo esc_url($listing_data['url']); ?>" class="hph-listing-image-link">
                <div class="hph-listing-image" style="background-image: url('<?php echo esc_url($listing_data['image_url']); ?>');">
                    <?php if ($config['show_favorite']): ?>
                    <button class="hph-favorite-btn hph-absolute hph-top-sm hph-right-sm <?php echo $listing_data['is_favorite'] ? 'hph-favorite-active' : ''; ?>" 
                            data-action="toggle-favorite" 
                            data-listing="<?php echo esc_attr($listing_id); ?>"
                            aria-label="<?php echo $listing_data['is_favorite'] ? 'Remove from' : 'Add to'; ?> favorites">
                        <i class="<?php echo $listing_data['is_favorite'] ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                    <?php endif; ?>
                    
                    <!-- Status Badge -->
                    <div class="hph-status-badge hph-status-badge-<?php echo esc_attr(strtolower($listing_data['status'])); ?> hph-absolute hph-bottom-sm hph-left-sm">
                        <?php echo esc_html($listing_data['status']); ?>
                    </div>
                    
                    <?php if ($listing_data['featured']): ?>
                    <div class="hph-featured-badge hph-absolute hph-top-sm hph-left-sm">
                        <i class="fas fa-star"></i>
                        Featured
                    </div>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        
        <!-- Content Section -->
        <div class="hph-listing-content hph-flex-1 hph-flex hph-flex-col">
            
            <!-- Header -->
            <div class="hph-listing-header hph-mb-md">
                <div class="hph-listing-price hph-text-2xl hph-font-bold hph-text-primary hph-mb-xs">
                    <?php echo esc_html($listing_data['price']); ?>
                </div>
                <h3 class="hph-listing-title hph-text-lg hph-font-semibold hph-mb-xs hph-leading-snug">
                    <a href="<?php echo esc_url($listing_data['url']); ?>" class="hph-text-gray-900 hph-hover-text-primary">
                        <?php echo esc_html($listing_data['address']); ?>
                    </a>
                </h3>
                <?php if ($location_string): ?>
                <div class="hph-listing-location hph-text-sm hph-text-gray-600">
                    <i class="fas fa-map-marker-alt hph-mr-xs"></i>
                    <?php echo esc_html($location_string); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Features -->
            <?php if ($config['show_stats']): ?>
            <div class="hph-listing-features hph-flex hph-gap-lg hph-mb-md">
                <?php if ($listing_data['bedrooms']): ?>
                <div class="hph-feature-item hph-flex hph-items-center hph-text-sm hph-text-gray-700">
                    <i class="fas fa-bed hph-mr-xs hph-text-primary"></i>
                    <span><?php echo esc_html($listing_data['bedrooms']); ?> bed<?php echo $listing_data['bedrooms'] > 1 ? 's' : ''; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($listing_data['bathrooms']): ?>
                <div class="hph-feature-item hph-flex hph-items-center hph-text-sm hph-text-gray-700">
                    <i class="fas fa-bath hph-mr-xs hph-text-primary"></i>
                    <span><?php echo esc_html($listing_data['bathrooms']); ?> bath<?php echo $listing_data['bathrooms'] > 1 ? 's' : ''; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($listing_data['sqft']): ?>
                <div class="hph-feature-item hph-flex hph-items-center hph-text-sm hph-text-gray-700">
                    <i class="fas fa-ruler-combined hph-mr-xs hph-text-primary"></i>
                    <span><?php echo esc_html(number_format($listing_data['sqft'])); ?> sqft</span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Agent & Date -->
            <div class="hph-listing-meta hph-mt-auto hph-flex hph-justify-between hph-items-center hph-text-xs hph-text-gray-500">
                <?php if ($config['show_agent'] && $listing_data['agent_name']): ?>
                <span>Listed by <?php echo esc_html($listing_data['agent_name']); ?></span>
                <?php endif; ?>
                <span><?php echo esc_html($listing_date); ?></span>
            </div>
            
        </div>
        
    </div>
    
    <?php else: ?>
    <!-- Grid/Card Layout (Default) -->
    <div class="hph-listing-card-inner">
        
        <!-- Image Section -->
        <div class="hph-listing-image-section hph-relative">
            <a href="<?php echo esc_url($listing_data['url']); ?>" class="hph-listing-image-link">
                <div class="hph-listing-image" style="background-image: url('<?php echo esc_url($listing_data['image_url']); ?>');">
                </div>
            </a>
            
            <!-- Top Overlay Elements -->
            <div class="hph-listing-image-overlay hph-absolute hph-inset-0 hph-pointer-events-none">
                <!-- Status Badge -->
                <div class="hph-status-badge hph-status-badge-<?php echo esc_attr(strtolower($listing_data['status'])); ?> hph-absolute hph-top-sm hph-left-sm hph-pointer-events-auto">
                    <?php echo esc_html($listing_data['status']); ?>
                </div>
                
                <?php if ($listing_data['featured']): ?>
                <!-- Featured Badge -->
                <div class="hph-featured-badge hph-absolute hph-top-sm hph-right-sm">
                    <i class="fas fa-star"></i>
                    Featured
                </div>
                <?php endif; ?>
                
                <?php if ($config['show_favorite']): ?>
                <!-- Favorite Button -->
                <button class="hph-favorite-btn hph-absolute hph-bottom-sm hph-right-sm hph-pointer-events-auto <?php echo $listing_data['is_favorite'] ? 'hph-favorite-active' : ''; ?>" 
                        data-action="toggle-favorite" 
                        data-listing="<?php echo esc_attr($listing_id); ?>"
                        aria-label="<?php echo $listing_data['is_favorite'] ? 'Remove from' : 'Add to'; ?> favorites">
                    <i class="<?php echo $listing_data['is_favorite'] ? 'fas' : 'far'; ?> fa-heart"></i>
                </button>
                <?php endif; ?>
                
                <!-- Price Overlay -->
                <div class="hph-price-overlay hph-absolute hph-bottom-sm hph-left-sm">
                    <div class="hph-listing-price hph-text-white hph-text-lg hph-font-bold hph-bg-black hph-bg-opacity-75 hph-px-sm hph-py-xs hph-rounded">
                        <?php echo esc_html($listing_data['price']); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="hph-listing-content hph-p-lg">
            
            <!-- Property Type -->
            <?php if (!$config['compact']): ?>
            <div class="hph-property-type hph-text-xs hph-text-primary hph-font-medium hph-uppercase hph-tracking-wide hph-mb-xs">
                <?php echo esc_html($listing_data['property_type']); ?>
            </div>
            <?php endif; ?>
            
            <!-- Title/Address -->
            <h3 class="hph-listing-title hph-text-lg hph-font-semibold hph-mb-xs hph-leading-snug hph-line-clamp-2">
                <a href="<?php echo esc_url($listing_data['url']); ?>" class="hph-text-gray-900 hph-hover-text-primary hph-transition-colors">
                    <?php echo esc_html($listing_data['address']); ?>
                </a>
            </h3>
            
            <!-- Location -->
            <?php if ($location_string): ?>
            <div class="hph-listing-location hph-text-sm hph-text-gray-600 hph-mb-md">
                <i class="fas fa-map-marker-alt hph-mr-xs"></i>
                <?php echo esc_html($location_string); ?>
            </div>
            <?php endif; ?>
            
            <!-- Features -->
            <?php if ($config['show_stats']): ?>
            <div class="hph-listing-features hph-flex hph-gap-md hph-mb-md">
                <?php if ($listing_data['bedrooms']): ?>
                <div class="hph-feature-item hph-text-center">
                    <div class="hph-feature-icon hph-w-8 hph-h-8 hph-mx-auto hph-mb-xs hph-bg-primary-light hph-text-primary hph-rounded-full hph-flex hph-items-center hph-justify-center">
                        <i class="fas fa-bed hph-text-xs"></i>
                    </div>
                    <div class="hph-feature-text hph-text-xs hph-text-gray-700">
                        <?php echo esc_html($listing_data['bedrooms']); ?> bed<?php echo $listing_data['bedrooms'] > 1 ? 's' : ''; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($listing_data['bathrooms']): ?>
                <div class="hph-feature-item hph-text-center">
                    <div class="hph-feature-icon hph-w-8 hph-h-8 hph-mx-auto hph-mb-xs hph-bg-primary-light hph-text-primary hph-rounded-full hph-flex hph-items-center hph-justify-center">
                        <i class="fas fa-bath hph-text-xs"></i>
                    </div>
                    <div class="hph-feature-text hph-text-xs hph-text-gray-700">
                        <?php echo esc_html($listing_data['bathrooms']); ?> bath<?php echo $listing_data['bathrooms'] > 1 ? 's' : ''; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($listing_data['sqft']): ?>
                <div class="hph-feature-item hph-text-center">
                    <div class="hph-feature-icon hph-w-8 hph-h-8 hph-mx-auto hph-mb-xs hph-bg-primary-light hph-text-primary hph-rounded-full hph-flex hph-items-center hph-justify-center">
                        <i class="fas fa-ruler-combined hph-text-xs"></i>
                    </div>
                    <div class="hph-feature-text hph-text-xs hph-text-gray-700">
                        <?php echo esc_html(number_format($listing_data['sqft'])); ?> sqft
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="hph-listing-footer hph-flex hph-justify-between hph-items-center hph-pt-md hph-border-t hph-border-gray-200">
                
                <!-- Agent/Date -->
                <div class="hph-listing-meta hph-text-xs hph-text-gray-500">
                    <?php if ($config['show_agent'] && $listing_data['agent_name']): ?>
                        <div><?php echo esc_html($listing_data['agent_name']); ?></div>
                    <?php endif; ?>
                    <div><?php echo esc_html($listing_date); ?></div>
                    <?php if ($days_on_market && $days_on_market > 0): ?>
                        <div><?php echo esc_html($days_on_market); ?> days on market</div>
                    <?php endif; ?>
                </div>
                
                <!-- Actions -->
                <?php if ($config['show_actions'] && current_user_can('edit_posts')): ?>
                <div class="hph-listing-actions hph-flex hph-gap-xs">
                    <a href="<?php echo esc_url(get_edit_post_link($listing_id)); ?>" 
                       class="hph-action-btn hph-action-btn-edit" 
                       title="Edit Listing"
                       aria-label="Edit listing">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button class="hph-action-btn hph-action-btn-duplicate" 
                            data-action="duplicate" 
                            data-listing="<?php echo esc_attr($listing_id); ?>"
                            title="Duplicate Listing"
                            aria-label="Duplicate listing">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        
    </div>
    <?php endif; ?>
    
</article>