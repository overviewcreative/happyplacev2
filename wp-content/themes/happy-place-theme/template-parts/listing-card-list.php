<?php
/**
 * Listing Card - List View
 * File: template-parts/listing-card-list.php
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

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

// Other fields
$listing_status = get_field('listing_status', $listing_id);
$property_type = get_field('property_type', $listing_id);
$is_featured = get_field('is_featured', $listing_id);
$mls_number = get_field('mls_number', $listing_id);

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

// Build location: City, State, Zip
$location = '';
if ($city) {
    $location = $city;
    if ($state) {
        $location .= ', ' . $state;
    }
    if ($zip) {
        $location .= ', ' . $zip;
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
?>

<article class="hph-card hph-card-elevated hph-transition-all hover:hph-shadow-xl">
    <a href="<?php echo get_permalink($listing_id); ?>" class="hph-flex hph-flex-col md:hph-flex-row">
        
        <!-- Image Container -->
        <div class="hph-relative md:hph-w-1/3 hph-aspect-ratio-16-9 md:hph-aspect-ratio-none md:hph-h-auto hph-overflow-hidden hph-bg-gray-200">
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
            <div class="hph-absolute hph-top-md hph-right-md">
                <span class="hph-bg-gold hph-text-white hph-px-xs hph-py-xs hph-rounded hph-text-xs hph-font-semibold" title="Featured">
                    <i class="fas fa-star"></i>
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Content -->
        <div class="hph-flex-grow hph-p-lg hph-flex hph-flex-col">
            
            <!-- Header Row -->
            <div class="hph-flex hph-justify-between hph-items-start hph-mb-md">
                <div>
                    <!-- Title (Street Address) -->
                    <?php if ($listing_title): ?>
                    <h3 class="hph-text-xl hph-font-semibold hph-text-gray-900 hph-mb-xs">
                        <?php echo esc_html($listing_title); ?>
                    </h3>
                    <?php endif; ?>
                    
                    <!-- Location -->
                    <?php if ($location): ?>
                    <p class="hph-text-gray-600 hph-text-sm">
                        <i class="fas fa-map-marker-alt hph-mr-xs"></i>
                        <?php echo esc_html($location); ?>
                    </p>
                    <?php endif; ?>
                    
                    <!-- Property Type and MLS -->
                    <div class="hph-flex hph-gap-sm hph-mt-xs">
                        <?php if ($property_type): ?>
                        <span class="hph-text-xs hph-text-gray-500">
                            <?php echo esc_html(hph_format_property_value($property_type)); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($mls_number): ?>
                        <span class="hph-text-xs hph-text-gray-500">
                            • MLS# <?php echo esc_html($mls_number); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Price -->
                <?php if ($price): ?>
                <div class="hph-text-2xl hph-font-bold hph-text-primary">
                    $<?php echo number_format($price); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Description -->
            <p class="hph-text-gray-700 hph-mb-md hph-line-clamp-2">
                <?php echo wp_trim_words(get_the_excerpt($listing_id), 30); ?>
            </p>
            
            <!-- Property Details -->
            <div class="hph-flex hph-flex-wrap hph-gap-lg hph-text-sm hph-text-gray-700 hph-mt-auto">
                <?php if ($bedrooms): ?>
                <span class="hph-flex hph-items-center hph-gap-xs">
                    <i class="fas fa-bed"></i>
                    <span><?php echo $bedrooms; ?> bed<?php echo $bedrooms != 1 ? 's' : ''; ?></span>
                </span>
                <?php endif; ?>
                
                <?php if ($total_baths > 0): ?>
                <span class="hph-flex hph-items-center hph-gap-xs">
                    <i class="fas fa-bath"></i>
                    <span><?php echo $total_baths; ?> bath<?php echo $total_baths != 1 ? 's' : ''; ?></span>
                </span>
                <?php endif; ?>
                
                <?php if ($square_feet): ?>
                <span class="hph-flex hph-items-center hph-gap-xs">
                    <i class="fas fa-ruler-combined"></i>
                    <span><?php echo number_format($square_feet); ?> sqft</span>
                </span>
                <?php endif; ?>
                
                <?php if ($price && $square_feet): ?>
                <span class="hph-flex hph-items-center hph-gap-xs">
                    <i class="fas fa-calculator"></i>
                    <span>$<?php echo number_format(round($price / $square_feet)); ?>/sqft</span>
                </span>
                <?php endif; ?>
            </div>
            
        </div>
    </a>
</article>
