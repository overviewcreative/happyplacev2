<?php
/**
 * Enhanced Listing Card with Favorites, Compare, and Selection
 * File: template-parts/listing-card-enhanced.php
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();
$view_type = $args['view_type'] ?? 'grid';
$show_selection = $args['show_selection'] ?? false;
$show_favorites = $args['show_favorites'] ?? false;
$show_compare = $args['show_compare'] ?? false;

// Get listing data using correct ACF field names
$price = get_field('listing_price', $listing_id);
$bedrooms = get_field('bedrooms', $listing_id);
$bathrooms_full = get_field('bathrooms_full', $listing_id);
$bathrooms_half = get_field('bathrooms_half', $listing_id);
$square_feet = get_field('square_feet', $listing_id);

// Address fields
$street_number = get_field('street_number', $listing_id);
$street_name = get_field('street_name', $listing_id);
$street_type = get_field('street_type', $listing_id);
$city = get_field('city', $listing_id);
$state = get_field('state', $listing_id);
$zip = get_field('zip_code', $listing_id);

// Additional fields for enhanced display
$listing_status = get_field('listing_status', $listing_id);
$property_type = get_field('property_type', $listing_id);
$is_featured = get_field('is_featured', $listing_id);
$days_on_market = get_field('days_on_market', $listing_id);
$virtual_tour = get_field('virtual_tour_url', $listing_id);
$open_house_date = get_field('next_open_house', $listing_id);

// Location data for map
$latitude = get_field('latitude', $listing_id);
$longitude = get_field('longitude', $listing_id);

// Calculate total bathrooms
$total_baths = floatval($bathrooms_full) + (floatval($bathrooms_half) * 0.5);

// Build title: Street Number + Street Name + Street Type
$listing_title = '';
if ($street_number && $street_name) {
    $listing_title = trim($street_number . ' ' . $street_name);
    if ($street_type) {
        $listing_title .= ' ' . $street_type;
    }
}

// Build location: City, State
$location = '';
if ($city) {
    $location = $city;
    if ($state) {
        $location .= ', ' . $state;
    }
}

// Get featured image
$featured_image = get_the_post_thumbnail_url($listing_id, 'large');
if (!$featured_image) {
    $featured_image = get_template_directory_uri() . '/assets/images/placeholder-property.jpg';
}

// Status badge config
$status_badges = [
    'active' => ['text' => 'Active', 'class' => 'hph-bg-success hph-text-white'],
    'pending' => ['text' => 'Pending', 'class' => 'hph-bg-warning hph-text-white'],
    'sold' => ['text' => 'Sold', 'class' => 'hph-bg-danger hph-text-white'],
    'new' => ['text' => 'New', 'class' => 'hph-bg-primary hph-text-white'],
];

// Check if user has favorited/is comparing this listing
$is_favorited = false;
$is_comparing = false;
if (is_user_logged_in()) {
    $current_user_id = get_current_user_id();
    $is_favorited = get_user_meta($current_user_id, '_favorite_listings', true);
    $is_favorited = is_array($is_favorited) && in_array($listing_id, $is_favorited);
    
    // Check compare list (stored in session/cookie)
    $compare_list = $_COOKIE['hph_compare_listings'] ?? '';
    $compare_list = explode(',', $compare_list);
    $is_comparing = in_array($listing_id, $compare_list);
}

// Card classes based on view type
$card_classes = ['listing-card-enhanced', 'hph-card', 'hph-card-elevated', 'hph-h-full', 'hph-flex', 'hph-transition-all', 'hover:hph-shadow-xl'];
if ($view_type === 'list') {
    $card_classes[] = 'hph-flex-row';
    $card_classes[] = 'hph-max-h-64';
} else {
    $card_classes[] = 'hph-flex-col';
}

if ($is_featured) {
    $card_classes[] = 'featured-listing';
}
?>

<article class="<?php echo implode(' ', $card_classes); ?>" 
         data-listing-id="<?php echo $listing_id; ?>"
         data-price="<?php echo $price; ?>"
         data-bedrooms="<?php echo $bedrooms; ?>"
         data-bathrooms="<?php echo $total_baths; ?>"
         data-sqft="<?php echo $square_feet; ?>"
         data-status="<?php echo $listing_status; ?>"
         data-lat="<?php echo $latitude; ?>"
         data-lng="<?php echo $longitude; ?>">
    
    <!-- Selection Checkbox -->
    <?php if ($show_selection): ?>
    <div class="selection-checkbox" data-listing-id="<?php echo $listing_id; ?>">
        <i class="fas fa-check hph-hidden"></i>
    </div>
    <?php endif; ?>
    
    <a href="<?php echo get_permalink($listing_id); ?>" class="hph-block hph-h-full hph-flex <?php echo $view_type === 'list' ? 'hph-flex-row' : 'hph-flex-col'; ?>">
        
        <!-- Image Container -->
        <div class="hph-relative <?php echo $view_type === 'list' ? 'hph-w-80 hph-flex-shrink-0' : 'hph-aspect-ratio-16-9'; ?> hph-overflow-hidden hph-bg-gray-200">
            <img src="<?php echo esc_url($featured_image); ?>" 
                 alt="<?php echo esc_attr($listing_title ?: get_the_title($listing_id)); ?>"
                 class="hph-w-full hph-h-full hph-object-cover"
                 loading="lazy">
            
            <!-- Status Badge -->
            <?php if ($listing_status && isset($status_badges[$listing_status])): ?>
            <div class="hph-absolute hph-top-md hph-left-md">
                <span class="hph-px-sm hph-py-xs hph-rounded-md hph-text-xs hph-font-semibold <?php echo $status_badges[$listing_status]['class']; ?>">
                    <?php echo $status_badges[$listing_status]['text']; ?>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Featured Badge -->
            <?php if ($is_featured): ?>
            <div class="hph-absolute hph-top-md hph-right-md hph-z-10">
                <span class="hph-bg-gold hph-text-white hph-px-sm hph-py-xs hph-rounded hph-text-xs hph-font-semibold" title="Featured">
                    <i class="fas fa-star"></i>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Additional Badges -->
            <div class="hph-absolute hph-bottom-md hph-left-md hph-flex hph-gap-xs">
                <?php if ($virtual_tour): ?>
                <span class="hph-bg-black hph-bg-opacity-75 hph-text-white hph-px-xs hph-py-xs hph-rounded hph-text-xs hph-font-semibold">
                    <i class="fas fa-video"></i> Virtual Tour
                </span>
                <?php endif; ?>
                
                <?php if ($open_house_date): ?>
                <span class="hph-bg-primary hph-text-white hph-px-xs hph-py-xs hph-rounded hph-text-xs hph-font-semibold">
                    <i class="fas fa-calendar"></i> Open House
                </span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Content -->
        <div class="hph-p-md hph-flex-grow hph-flex hph-flex-col">
            
            <!-- Price & Days on Market -->
            <div class="hph-flex hph-justify-between hph-items-start hph-mb-sm">
                <?php if ($price): ?>
                <div class="hph-text-2xl hph-font-bold hph-text-primary">
                    $<?php echo number_format($price); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($days_on_market): ?>
                <div class="hph-text-xs hph-text-gray-500 hph-bg-gray-100 hph-px-xs hph-py-xs hph-rounded">
                    <?php echo $days_on_market; ?> DOM
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Title (Street Address) -->
            <?php if ($listing_title): ?>
            <h3 class="hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-xs hph-line-clamp-1">
                <?php echo esc_html($listing_title); ?>
            </h3>
            <?php endif; ?>
            
            <!-- Location -->
            <?php if ($location): ?>
            <p class="hph-text-sm hph-text-gray-600 hph-mb-md hph-line-clamp-1">
                <i class="fas fa-map-marker-alt hph-mr-xs hph-text-primary"></i>
                <?php echo esc_html($location); ?>
            </p>
            <?php endif; ?>
            
            <!-- Property Details -->
            <div class="hph-flex hph-flex-wrap hph-gap-md hph-text-sm hph-text-gray-700 hph-mt-auto">
                <?php if ($bedrooms): ?>
                <span class="hph-flex hph-items-center hph-gap-xs">
                    <i class="fas fa-bed hph-text-primary"></i>
                    <span><?php echo $bedrooms; ?> bed<?php echo $bedrooms != 1 ? 's' : ''; ?></span>
                </span>
                <?php endif; ?>
                
                <?php if ($total_baths > 0): ?>
                <span class="hph-flex hph-items-center hph-gap-xs">
                    <i class="fas fa-bath hph-text-primary"></i>
                    <span><?php echo $total_baths; ?> bath<?php echo $total_baths != 1 ? 's' : ''; ?></span>
                </span>
                <?php endif; ?>
                
                <?php if ($square_feet): ?>
                <span class="hph-flex hph-items-center hph-gap-xs">
                    <i class="fas fa-ruler-combined hph-text-primary"></i>
                    <span><?php echo number_format($square_feet); ?> sqft</span>
                </span>
                <?php endif; ?>
                
                <?php if ($property_type): ?>
                <span class="hph-flex hph-items-center hph-gap-xs">
                    <i class="fas fa-home hph-text-primary"></i>
                    <span><?php echo ucwords(str_replace('-', ' ', $property_type)); ?></span>
                </span>
                <?php endif; ?>
            </div>
            
            <!-- Price Per Sqft -->
            <?php if ($price && $square_feet): ?>
            <div class="hph-text-xs hph-text-gray-500 hph-mt-sm">
                $<?php echo number_format($price / $square_feet); ?> per sqft
            </div>
            <?php endif; ?>
        </div>
    </a>
    
    <!-- Action Buttons (Outside of link to prevent nested anchor issues) -->
    <?php if ($show_favorites || $show_compare): ?>
    <div class="listing-actions">
        <?php if ($show_favorites): ?>
        <button class="action-btn favorite-btn <?php echo $is_favorited ? 'favorited' : ''; ?>" 
                data-listing-id="<?php echo $listing_id; ?>"
                data-action="favorite"
                title="<?php echo $is_favorited ? 'Remove from Favorites' : 'Add to Favorites'; ?>">
            <i class="<?php echo $is_favorited ? 'fas' : 'far'; ?> fa-heart"></i>
        </button>
        <?php endif; ?>
        
        <?php if ($show_compare): ?>
        <button class="action-btn compare-btn <?php echo $is_comparing ? 'comparing' : ''; ?>" 
                data-listing-id="<?php echo $listing_id; ?>"
                data-action="compare"
                title="<?php echo $is_comparing ? 'Remove from Compare' : 'Add to Compare'; ?>">
            <i class="fas fa-balance-scale"></i>
        </button>
        <?php endif; ?>
        
        <!-- Share Button -->
        <button class="action-btn share-btn" 
                data-listing-id="<?php echo $listing_id; ?>"
                data-action="share"
                data-url="<?php echo get_permalink($listing_id); ?>"
                data-title="<?php echo esc_attr($listing_title); ?>"
                title="Share this listing">
            <i class="fas fa-share-alt"></i>
        </button>
    </div>
    <?php endif; ?>
    
    <!-- Quick Actions Bar (for list view) -->
    <?php if ($view_type === 'list'): ?>
    <div class="hph-p-md hph-border-t hph-bg-gray-50">
        <div class="hph-flex hph-justify-between hph-items-center hph-text-sm">
            <div class="hph-flex hph-gap-md">
                <?php if ($virtual_tour): ?>
                <a href="<?php echo esc_url($virtual_tour); ?>" target="_blank" class="hph-text-primary hover:hph-underline">
                    <i class="fas fa-video hph-mr-xs"></i> Virtual Tour
                </a>
                <?php endif; ?>
                
                <a href="<?php echo get_permalink($listing_id); ?>" class="hph-text-primary hover:hph-underline">
                    <i class="fas fa-images hph-mr-xs"></i> Photos
                </a>
                
                <a href="#" class="hph-text-primary hover:hph-underline" onclick="showContactModal(<?php echo $listing_id; ?>); return false;">
                    <i class="fas fa-phone hph-mr-xs"></i> Contact
                </a>
            </div>
            
            <div class="hph-text-gray-500">
                MLS# <?php echo get_field('mls_number', $listing_id) ?: 'N/A'; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</article>
