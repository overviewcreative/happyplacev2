<?php
/**
 * City/Community Card Template Part
 * File: template-parts/listing/city-community-card.php
 * 
 * Displays city and community information cards for a listing
 * Uses HPH framework utility classes
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get listing city and community data
$city_name = null;
$community_id = null;

// Get city from listing
if (function_exists('hpt_get_listing_city')) {
    try {
        $city_name = hpt_get_listing_city($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_city failed: ' . $e->getMessage());
    }
}
if (!$city_name) {
    $city_name = get_field('city', $listing_id);
}

// Get community from listing
if (function_exists('hpt_get_listing_community')) {
    try {
        $community_id = hpt_get_listing_community($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_community failed: ' . $e->getMessage());
    }
}
if (!$community_id) {
    $community_id = get_field('community', $listing_id) ?: get_field('subdivision', $listing_id);
}

// Find city post by name
$city_post = null;
if ($city_name) {
    $city_query = new WP_Query([
        'post_type' => 'city',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'city_name',
                'value' => $city_name,
                'compare' => '='
            ]
        ]
    ]);
    
    if (!$city_query->have_posts()) {
        // Try by post title if meta query fails
        $city_query = new WP_Query([
            'post_type' => 'city',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            's' => $city_name
        ]);
    }
    
    if ($city_query->have_posts()) {
        $city_post = $city_query->posts[0];
    }
    wp_reset_postdata();
}

// Get community post
$community_post = null;
if ($community_id && is_numeric($community_id)) {
    $community_post = get_post($community_id);
    if ($community_post && $community_post->post_type !== 'community') {
        $community_post = null;
    }
}

// Early return if no city or community data
if (!$city_post && !$community_post) {
    return;
}

?>

<section id="city-community-info" class="hph-city-community-section hph-py-3xl hph-bg-gray-50">
    <div class="hph-container">
        
        <!-- Section Header -->
        <div class="hph-section-header hph-text-center hph-mb-xl">
            <h2 class="hph-section-title hph-text-3xl hph-font-bold hph-mb-sm">
                <i class="fas fa-map-marker-alt hph-text-primary hph-mr-2"></i>
                Location Information
            </h2>
            <p class="hph-section-subtitle hph-text-gray-600">
                Learn more about the area and community
            </p>
        </div>
        
        <!-- City and Community Cards -->
        <div class="hph-location-cards hph-grid hph-gap-lg">
            
            <?php if ($city_post) : 
                // Get city data using bridge functions
                $city_id = $city_post->ID;
                $city_title = get_the_title($city_id);
                $city_url = get_permalink($city_id);
                $city_state = get_field('state', $city_id) ?: '';
                $city_population = get_field('population', $city_id) ?: '';
                
                // Get rich content for magazine style
                $city_description = null;
                $city_featured_image = null;
                $city_gallery = null;
                $city_attractions = null;
                $city_dining = null;
                $city_parks = null;
                
                if (function_exists('hpt_get_city_description')) {
                    $city_description = hpt_get_city_description($city_id);
                }
                if (!$city_description) {
                    $city_description = get_field('description', $city_id) ?: get_the_excerpt($city_id);
                }
                
                if (function_exists('hpt_get_city_featured_image')) {
                    $city_featured_image = hpt_get_city_featured_image($city_id);
                }
                if (!$city_featured_image) {
                    $city_featured_image = get_the_post_thumbnail_url($city_id, 'large');
                }
                
                if (function_exists('hpt_get_city_gallery')) {
                    $city_gallery = hpt_get_city_gallery($city_id);
                }
                if (!$city_gallery || !is_array($city_gallery)) {
                    $city_gallery = get_field('gallery', $city_id) ?: get_field('city_gallery', $city_id) ?: [];
                }
                
                if (function_exists('hpt_get_city_attractions')) {
                    $city_attractions = hpt_get_city_attractions($city_id);
                }
                if (!$city_attractions) {
                    $city_attractions = get_field('attractions', $city_id) ?: get_field('points_of_interest', $city_id);
                }
                
                if (function_exists('hpt_get_city_dining')) {
                    $city_dining = hpt_get_city_dining($city_id);
                }
                if (!$city_dining) {
                    $city_dining = get_field('dining_entertainment', $city_id) ?: get_field('restaurants', $city_id);
                }
                
                if (function_exists('hpt_get_city_parks')) {
                    $city_parks = hpt_get_city_parks($city_id);
                }
                if (!$city_parks) {
                    $city_parks = get_field('parks_recreation', $city_id) ?: get_field('recreation', $city_id);
                }
                
                // Get city statistics for small stats box
                $city_listing_count = 0;
                if (function_exists('hpt_get_city_listing_count')) {
                    $city_listing_count = hpt_get_city_listing_count($city_id);
                } else {
                    // Fallback query
                    $city_listings_query = new WP_Query([
                        'post_type' => 'listing',
                        'meta_query' => [
                            [
                                'key' => 'city',
                                'value' => $city_title,
                                'compare' => '='
                            ]
                        ],
                        'post_status' => 'publish',
                        'posts_per_page' => -1
                    ]);
                    $city_listing_count = $city_listings_query->found_posts;
                    wp_reset_postdata();
                }
                
                $median_home_price = get_field('median_home_price', $city_id);
            ?>
            
            <!-- Magazine-Style City Feature -->
            <div class="hph-city-magazine-feature hph-bg-white hph-rounded-lg hph-shadow-lg hph-overflow-hidden">
                
                <!-- Feature Header -->
                <div class="hph-magazine-header hph-p-xl">
                    <div class="hph-magazine-title-section">
                        <span class="hph-magazine-label hph-text-primary hph-text-sm hph-font-semibold hph-uppercase">Featured Location</span>
                        <h3 class="hph-magazine-title hph-text-2xl hph-font-bold hph-mt-sm">
                            Discover <?php echo esc_html($city_title); ?>
                            <?php if ($city_state) : ?>
                                <span class="hph-magazine-subtitle hph-text-gray-600 hph-text-lg hph-block"><?php echo esc_html($city_state); ?></span>
                            <?php endif; ?>
                        </h3>
                        <?php if ($city_population) : ?>
                            <p class="hph-magazine-meta hph-text-gray-600 hph-mt-sm">
                                Home to <?php echo number_format($city_population); ?> residents
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stats Box -->
                    <div class="hph-magazine-stats-box hph-flex hph-gap-lg hph-mt-lg">
                        <?php if ($city_listing_count > 0) : ?>
                            <div class="hph-quick-stat">
                                <span class="hph-stat-number hph-text-2xl hph-font-bold hph-text-primary"><?php echo number_format($city_listing_count); ?></span>
                                <span class="hph-stat-label hph-text-sm hph-text-gray-600 hph-block">Properties</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($median_home_price) : ?>
                            <div class="hph-quick-stat">
                                <span class="hph-stat-number hph-text-2xl hph-font-bold hph-text-success">$<?php echo number_format($median_home_price / 1000); ?>K</span>
                                <span class="hph-stat-label hph-text-sm hph-text-gray-600 hph-block">Median Price</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Feature Content -->
                <div class="hph-magazine-content">
                    
                    <!-- Hero Image Section -->
                    <?php if ($city_featured_image) : ?>
                        <div class="hph-magazine-hero hph-relative">
                            <img src="<?php echo esc_url($city_featured_image); ?>" 
                                 alt="<?php echo esc_attr($city_title . ', ' . $city_state); ?>"
                                 class="hph-w-full hph-h-96 hph-object-cover"
                                 loading="lazy">
                            <div class="hph-magazine-hero-overlay hph-absolute hph-bottom-0 hph-left-0 hph-p-md hph-bg-gradient-dark">
                                <span class="hph-hero-caption hph-text-white">
                                    <i class="fas fa-camera hph-mr-2"></i>
                                    <?php echo esc_html($city_title); ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Article Content -->
                    <div class="hph-magazine-article hph-p-xl">
                        
                        <!-- Main Description -->
                        <?php if ($city_description) : ?>
                            <div class="hph-magazine-lead hph-mb-lg">
                                <p class="hph-lead-text hph-text-lg hph-leading-relaxed">
                                    <?php echo wp_kses_post(wp_trim_words($city_description, 40)); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Content Sections -->
                        <div class="hph-magazine-sections hph-grid hph-grid-cols-1 hph-grid-cols-md-3 hph-gap-lg">
                            
                            <?php if ($city_attractions) : ?>
                                <div class="hph-content-section">
                                    <h4 class="hph-section-heading hph-flex hph-items-center hph-gap-sm hph-mb-sm">
                                        <i class="fas fa-map-marker-alt hph-text-primary"></i>
                                        <span class="hph-font-semibold">What to See & Do</span>
                                    </h4>
                                    <p class="hph-section-text hph-text-sm hph-text-gray-600">
                                        <?php echo esc_html(is_array($city_attractions) ? implode(', ', array_slice($city_attractions, 0, 3)) : wp_trim_words($city_attractions, 20)); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($city_dining) : ?>
                                <div class="hph-content-section">
                                    <h4 class="hph-section-heading hph-flex hph-items-center hph-gap-sm hph-mb-sm">
                                        <i class="fas fa-utensils hph-text-secondary"></i>
                                        <span class="hph-font-semibold">Dining & Entertainment</span>
                                    </h4>
                                    <p class="hph-section-text hph-text-sm hph-text-gray-600">
                                        <?php echo esc_html(is_array($city_dining) ? implode(', ', array_slice($city_dining, 0, 3)) : wp_trim_words($city_dining, 20)); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($city_parks) : ?>
                                <div class="hph-content-section">
                                    <h4 class="hph-section-heading hph-flex hph-items-center hph-gap-sm hph-mb-sm">
                                        <i class="fas fa-tree hph-text-success"></i>
                                        <span class="hph-font-semibold">Parks & Recreation</span>
                                    </h4>
                                    <p class="hph-section-text hph-text-sm hph-text-gray-600">
                                        <?php echo esc_html(is_array($city_parks) ? implode(', ', array_slice($city_parks, 0, 3)) : wp_trim_words($city_parks, 20)); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                        
                        <!-- Photo Gallery -->
                        <?php if (!empty($city_gallery) && is_array($city_gallery) && count($city_gallery) > 0) : ?>
                            <div class="hph-magazine-gallery hph-mt-xl">
                                <h4 class="hph-gallery-heading hph-text-lg hph-font-semibold hph-mb-md">
                                    <i class="fas fa-images hph-text-info hph-mr-2"></i>
                                    Around <?php echo esc_html($city_title); ?>
                                </h4>
                                <div class="hph-gallery-grid hph-grid hph-grid-cols-2 hph-grid-cols-md-4 hph-gap-sm">
                                    <?php foreach (array_slice($city_gallery, 0, 4) as $index => $image) : 
                                        $image_url = is_array($image) ? ($image['url'] ?? $image['sizes']['medium'] ?? '') : $image;
                                        $image_alt = is_array($image) ? ($image['alt'] ?? '') : '';
                                        if ($image_url) :
                                    ?>
                                        <div class="hph-gallery-item <?php echo $index === 0 ? 'hph-col-span-2' : ''; ?> hph-rounded-md hph-overflow-hidden">
                                            <img src="<?php echo esc_url($image_url); ?>" 
                                                 alt="<?php echo esc_attr($image_alt ?: $city_title); ?>"
                                                 class="hph-w-full hph-h-32 hph-object-cover hover:hph-scale-105 hph-transition-transform"
                                                 loading="lazy">
                                        </div>
                                    <?php endif; endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Call to Action -->
                        <div class="hph-magazine-cta hph-mt-xl">
                            <a href="<?php echo esc_url($city_url); ?>" 
                               class="hph-btn hph-btn-primary hph-btn-lg">
                                <span>Learn More About <?php echo esc_html($city_title); ?></span>
                                <i class="fas fa-arrow-right hph-ml-2"></i>
                            </a>
                        </div>
                        
                    </div>
                    
                </div>
                
            </div>
            
            <?php endif; ?>
            
            <?php if ($community_post) : 
                // Get community data
                $community_id = $community_post->ID;
                $community_title = get_the_title($community_id);
                $community_url = get_permalink($community_id);
                $community_featured_image = get_the_post_thumbnail_url($community_id, 'medium');
                $community_type = get_field('community_type', $community_id) ?: '';
                $community_description = get_field('description', $community_id) ?: wp_trim_words(get_the_excerpt($community_id), 25);
                
                // Get community statistics
                $community_listing_count = 0;
                if (function_exists('hpt_get_community_listing_count')) {
                    $community_listing_count = hpt_get_community_listing_count($community_id);
                } else {
                    // Fallback query
                    $community_listings_query = new WP_Query([
                        'post_type' => 'listing',
                        'meta_query' => [
                            [
                                'key' => 'community',
                                'value' => $community_id,
                                'compare' => '='
                            ]
                        ],
                        'post_status' => 'publish',
                        'posts_per_page' => -1
                    ]);
                    $community_listing_count = $community_listings_query->found_posts;
                    wp_reset_postdata();
                }
                
                $average_price = get_field('average_home_price', $community_id);
                $total_homes = get_field('total_homes', $community_id);
                $hoa_fees = get_field('hoa_fees', $community_id);
                $amenities = get_field('amenities', $community_id);
            ?>
            
            <!-- Community Card -->
            <div class="hph-location-card hph-community-card hph-bg-white hph-rounded-lg hph-shadow-md hph-overflow-hidden">
                <div class="hph-card-header">
                    <?php if ($community_featured_image) : ?>
                        <div class="hph-card-image">
                            <img src="<?php echo esc_url($community_featured_image); ?>" 
                                 alt="<?php echo esc_attr($community_title . ' community'); ?>"
                                 class="hph-w-full hph-h-48 hph-object-cover"
                                 loading="lazy">
                        </div>
                    <?php endif; ?>
                    
                    <div class="hph-card-header-content hph-p-lg">
                        <div class="hph-card-title-area hph-flex hph-justify-between hph-items-start">
                            <div>
                                <h3 class="hph-card-title hph-text-xl hph-font-bold">
                                    <i class="fas fa-home hph-text-secondary hph-mr-2"></i>
                                    <?php echo esc_html($community_title); ?>
                                </h3>
                                <?php if ($community_type) : ?>
                                    <p class="hph-card-subtitle hph-text-sm hph-text-gray-600"><?php echo esc_html($community_type); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($hoa_fees) : ?>
                                <div class="hph-card-badge">
                                    <span class="hph-badge hph-badge--info">
                                        HOA: $<?php echo number_format($hoa_fees); ?>/mo
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="hph-card-body hph-p-lg">
                    <?php if ($community_description) : ?>
                        <p class="hph-card-description hph-text-gray-700 hph-mb-lg"><?php echo esc_html($community_description); ?></p>
                    <?php endif; ?>
                    
                    <div class="hph-card-stats hph-grid hph-grid-cols-3 hph-gap-md hph-mb-lg">
                        <?php if ($community_listing_count > 0) : ?>
                            <div class="hph-stat-item hph-text-center">
                                <i class="fas fa-home hph-text-primary hph-text-xl hph-mb-sm hph-block"></i>
                                <span class="hph-stat-value hph-text-lg hph-font-bold hph-block"><?php echo number_format($community_listing_count); ?></span>
                                <span class="hph-stat-label hph-text-xs hph-text-gray-600">Available</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($total_homes) : ?>
                            <div class="hph-stat-item hph-text-center">
                                <i class="fas fa-building hph-text-info hph-text-xl hph-mb-sm hph-block"></i>
                                <span class="hph-stat-value hph-text-lg hph-font-bold hph-block"><?php echo number_format($total_homes); ?></span>
                                <span class="hph-stat-label hph-text-xs hph-text-gray-600">Total Homes</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($average_price) : ?>
                            <div class="hph-stat-item hph-text-center">
                                <i class="fas fa-dollar-sign hph-text-success hph-text-xl hph-mb-sm hph-block"></i>
                                <span class="hph-stat-value hph-text-lg hph-font-bold hph-block">$<?php echo number_format($average_price / 1000); ?>K</span>
                                <span class="hph-stat-label hph-text-xs hph-text-gray-600">Avg Price</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($amenities && is_array($amenities) && !empty($amenities)) : ?>
                        <div class="hph-card-amenities">
                            <h4 class="hph-amenities-title hph-text-sm hph-font-semibold hph-mb-sm">
                                <i class="fas fa-star hph-text-warning hph-mr-2"></i>
                                Community Amenities
                            </h4>
                            <div class="hph-amenities-list hph-flex hph-flex-wrap hph-gap-sm">
                                <?php foreach (array_slice($amenities, 0, 4) as $amenity) : ?>
                                    <span class="hph-amenity-tag hph-badge hph-badge--light"><?php echo esc_html($amenity); ?></span>
                                <?php endforeach; ?>
                                <?php if (count($amenities) > 4) : ?>
                                    <span class="hph-amenity-tag hph-badge hph-badge--secondary">+<?php echo count($amenities) - 4; ?> more</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="hph-card-footer hph-p-lg hph-bg-gray-50">
                    <a href="<?php echo esc_url($community_url); ?>" 
                       class="hph-btn hph-btn-secondary w-full">
                        <i class="fas fa-users hph-mr-2"></i>
                        View Community Details
                    </a>
                </div>
            </div>
            
            <?php endif; ?>
            
        </div>
        
    </div>
</section>
