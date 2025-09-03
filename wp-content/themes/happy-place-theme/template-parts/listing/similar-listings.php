<?php
/**
 * Similar Listings Template Part
 * File: template-parts/listing/similar-listings.php
 * 
 * Displays related properties based on location and type using bridge functions
 * Uses HPH framework utilities and CSS variables
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get current listing data for comparison using bridge functions with fallbacks
$listing_data = null;
if (function_exists('hpt_get_listing')) {
    try {
        $listing_data = hpt_get_listing($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing failed: ' . $e->getMessage());
    }
}

$listing_address = null;
if (function_exists('hpt_get_listing_address')) {
    try {
        $listing_address = hpt_get_listing_address($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_address failed: ' . $e->getMessage());
    }
}

$property_type = null;
if (function_exists('hpt_get_listing_property_type')) {
    try {
        $property_type = hpt_get_listing_property_type($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_property_type failed: ' . $e->getMessage());
    }
}

$bedrooms = null;
if (function_exists('hpt_get_listing_bedrooms')) {
    try {
        $bedrooms = hpt_get_listing_bedrooms($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_bedrooms failed: ' . $e->getMessage());
    }
}

$current_data = [
    'city' => $listing_address['city'] ?? get_field('city', $listing_id),
    'property_type' => $property_type ?: get_field('property_type', $listing_id),
    'price' => $listing_data['price'] ?? get_field('listing_price', $listing_id) ?: 0,
    'bedrooms' => $bedrooms ?: get_field('bedrooms', $listing_id),
    'neighborhood' => get_field('neighborhood', $listing_id) // Direct fallback
];

// Build query for similar listings
$similar_args = [
    'post_type' => 'listing',
    'post_status' => 'publish',
    'posts_per_page' => 3,
    'post__not_in' => [$listing_id],
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => 'city',
            'value' => $current_data['city'],
            'compare' => '='
        ],
        [
            'key' => 'property_type',
            'value' => $current_data['property_type'],
            'compare' => '='
        ]
    ]
];

// Add price range filter if price exists
if ($current_data['price']) {
    $price_min = $current_data['price'] * 0.8;
    $price_max = $current_data['price'] * 1.2;
    
    $similar_args['meta_query'][] = [
        'key' => 'listing_price',
        'value' => [$price_min, $price_max],
        'type' => 'NUMERIC',
        'compare' => 'BETWEEN'
    ];
}

$similar_listings = new WP_Query($similar_args);

if (!$similar_listings->have_posts()) {
    return;
}
?>

<section class="hph-similar-listings hph-py-3xl hph-bg-white">
    <div class="hph-container">
        
        <div class="hph-section__header hph-text-center hph-mb-xl">
            <h2 class="hph-section__title hph-text-3xl hph-font-bold hph-mb-sm">
                Similar Properties
            </h2>
            <p class="hph-section__subtitle hph-text-lg hph-text-gray-600">
                Other homes you might like in <?php echo esc_html($current_data['city']); ?>
            </p>
        </div>
        
        <div class="hph-listings-grid hph-grid hph-grid-cols-1 hph-grid-cols-md-2 hph-grid-cols-lg-3 hph-gap-xl">
            
            <?php while ($similar_listings->have_posts()) : $similar_listings->the_post(); ?>
            <?php 
            $similar_id = get_the_ID();
            $similar_price = get_field('listing_price', $similar_id);
            $similar_beds = get_field('bedrooms', $similar_id);
            $similar_baths = get_field('bathrooms_full', $similar_id);
            $similar_sqft = get_field('square_feet', $similar_id);
            $similar_address = trim(get_field('street_number', $similar_id) . ' ' . 
                                   get_field('street_name', $similar_id) . ' ' . 
                                   get_field('street_type', $similar_id));
            ?>
            
            <article class="hph-listing-card hph-bg-white hph-rounded-lg hph-shadow-md hover:hph-shadow-xl hph-transition-shadow hph-overflow-hidden">
                
                <!-- Card Image -->
                <a href="<?php the_permalink(); ?>" class="hph-card__image-link hph-block hph-relative hph-overflow-hidden" style="height: 240px;">
                    <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('large', [
                        'class' => 'hph-w-full hph-h-full hph-object-cover hover:hph-scale-110 hph-transition-transform hph-duration-500'
                    ]); ?>
                    <?php else : ?>
                    <div class="hph-w-full hph-h-full hph-bg-gray-200 hph-flex hph-items-center hph-justify-center">
                        <i class="fas fa-home hph-text-4xl hph-text-gray-400"></i>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Price Badge -->
                    <?php if ($similar_price) : ?>
                    <div class="hph-absolute hph-bottom-md hph-left-md hph-bg-white hph-px-md hph-py-sm hph-rounded-md hph-font-bold hph-text-lg">
                        $<?php echo esc_html(number_format($similar_price)); ?>
                    </div>
                    <?php endif; ?>
                </a>
                
                <!-- Card Body -->
                <div class="hph-card__body hph-p-lg">
                    
                    <h3 class="hph-card__title hph-text-lg hph-font-semibold hph-mb-sm">
                        <a href="<?php the_permalink(); ?>" class="hph-text-gray-900 hover:hph-text-primary hph-transition-colors">
                            <?php the_title(); ?>
                        </a>
                    </h3>
                    
                    <?php if ($similar_address) : ?>
                    <p class="hph-card__address hph-text-sm hph-text-gray-600 hph-mb-md">
                        <i class="fas fa-map-marker-alt hph-text-primary hph-mr-xs"></i>
                        <?php echo esc_html($similar_address); ?>
                    </p>
                    <?php endif; ?>
                    
                    <!-- Property Stats -->
                    <div class="hph-card__stats hph-flex hph-gap-md hph-text-sm hph-text-gray-700">
                        <?php if ($similar_beds) : ?>
                        <div class="hph-stat hph-flex hph-items-center hph-gap-xs">
                            <i class="fas fa-bed hph-text-gray-400"></i>
                            <span><?php echo esc_html($similar_beds); ?> Beds</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($similar_baths) : ?>
                        <div class="hph-stat hph-flex hph-items-center hph-gap-xs">
                            <i class="fas fa-bath hph-text-gray-400"></i>
                            <span><?php echo esc_html($similar_baths); ?> Baths</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($similar_sqft) : ?>
                        <div class="hph-stat hph-flex hph-items-center hph-gap-xs">
                            <i class="fas fa-ruler-combined hph-text-gray-400"></i>
                            <span><?php echo esc_html(number_format($similar_sqft)); ?> sqft</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
                
                <!-- Card Footer -->
                <div class="hph-card__footer hph-px-lg hph-py-md hph-bg-gray-50 hph-border-t hph-border-gray-200">
                    <a href="<?php the_permalink(); ?>" class="hph-btn hph-btn--primary hph-btn--sm hph-btn--full">
                        View Details
                    </a>
                </div>
                
            </article>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
            
        </div>
        
        <!-- View All Link -->
        <div class="hph-text-center hph-mt-xl">
            <a href="<?php echo esc_url(home_url('/listings')); ?>" 
               class="hph-btn hph-btn--secondary hph-btn--lg hph-inline-flex hph-items-center hph-gap-sm">
                <i class="fas fa-search"></i>
                <span>Browse All Properties</span>
            </a>
        </div>
        
    </div>
</section>