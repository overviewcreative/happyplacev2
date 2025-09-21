<?php
/**
 * Single City Template - Modern Section-Based Design
 * 
 * Complete city profile using the unified section system:
 * - Hero section with city image and key info
 * - Stats section showcasing market data
 * - Features section highlighting city highlights
 * - Content section with detailed description
 * - Communities showcase for neighborhoods in this city
 * - Listings showcase for properties in this city
 * - Contact form section for inquiries
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get city ID and verify it exists
$city_id = get_the_ID();
if (!$city_id || get_post_type($city_id) !== 'city') {
    get_template_part('template-parts/base/content-none');
    get_footer();
    return;
}

// Get city data
$city_name = get_the_title($city_id);
$city_description = get_the_content();
$short_description = get_field('short_description', $city_id);
$state = get_field('state', $city_id);
$county = get_field('county', $city_id);
$zip_codes = get_field('zip_codes', $city_id);

// City stats
$population = get_field('population', $city_id);
$median_home_price = get_field('median_home_price', $city_id);
$median_income = get_field('median_income', $city_id);
$total_listings = get_field('total_listings', $city_id);
$school_rating = get_field('school_rating', $city_id);
$crime_rating = get_field('crime_rating', $city_id);

// Features and highlights
$city_highlights = get_field('city_highlights', $city_id);
$top_schools = get_field('top_schools', $city_id);
$local_attractions = get_field('local_attractions', $city_id);
$transportation = get_field('transportation', $city_id);

// Location and contact
$latitude = get_field('latitude', $city_id);
$longitude = get_field('longitude', $city_id);
$website = get_field('website', $city_id);
$chamber_of_commerce = get_field('chamber_of_commerce', $city_id);

// Get city image
$city_image = get_the_post_thumbnail_url($city_id, 'full');
if (!$city_image) {
    $city_image = get_template_directory_uri() . '/assets/images/placeholder-city.jpg';
}

// Build location string
$location_display = $city_name;
if ($state) {
    $location_display .= ', ' . $state;
}
if ($county) {
    $location_display .= ' (' . $county . ' County)';
}

// Get current market stats
$total_listings_count = 0;
$average_price = 0;

// Query current listings in this city for real-time stats
$city_listings_query = new WP_Query([
    'post_type' => 'listing',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => 'city',
            'value' => $city_id,
            'compare' => '='
        ]
    ],
    'fields' => 'ids'
]);

if ($city_listings_query->have_posts()) {
    $total_listings_count = $city_listings_query->found_posts;
    
    // Calculate average price from current listings
    $price_sum = 0;
    $price_count = 0;
    
    foreach ($city_listings_query->posts as $listing_id) {
        $listing_price = get_field('price', $listing_id);
        if ($listing_price && is_numeric($listing_price)) {
            $price_sum += $listing_price;
            $price_count++;
        }
    }
    
    if ($price_count > 0) {
        $average_price = $price_sum / $price_count;
    }
}
wp_reset_postdata();
?>

    <?php
    // ============================================
    // Hero Section - City Profile
    // ============================================
    $description_preview = $short_description ?: ($city_description ? wp_trim_words(strip_tags($city_description), 25, '...') : 'Discover life in ' . $city_name . ' with its unique character, excellent amenities, and prime location.');
    ?>

    <!-- Custom Hero Section with City Image -->
    <section 
        class="hph-hero-section hph-city-hero"
        id="hero-city-<?php echo esc_attr($city_id); ?>"
        data-bg="image"
        style="position: relative; width: 100%; min-height: 70vh; background-image: url('<?php echo esc_url($city_image); ?>'); background-size: cover; background-position: center; color: var(--hph-white);"
        data-animation="true"
    >
        <!-- Gradient Overlay -->
        <div class="hph-absolute hph-inset-0 hph-bg-gradient-to-r hph-from-black hph-via-black/70 hph-to-transparent hph-opacity-60"></div>
        
        <!-- Hero Content -->
        <div class="hph-container hph-relative hph-z-10 hph-h-full hph-flex hph-items-center">
            <div class="hph-hero-content hph-max-w-2xl">
                
                <!-- City Badge -->
                <div class="hph-hero-badge hph-inline-flex hph-items-center hph-gap-2 hph-bg-white/20 hph-backdrop-blur-sm hph-rounded-full hph-px-4 hph-py-2 hph-text-sm hph-font-medium hph-mb-6">
                    <i class="fas fa-city hph-text-primary"></i>
                    <span>City Profile</span>
                </div>
                
                <!-- City Name -->
                <h1 class="hph-hero-title hph-text-4xl md:hph-text-6xl hph-font-bold hph-leading-tight hph-mb-6">
                    <?php echo esc_html($city_name); ?>
                </h1>
                
                <!-- Location -->
                <div class="hph-hero-location hph-flex hph-items-center hph-gap-2 hph-text-xl hph-mb-6 hph-opacity-95">
                    <i class="fas fa-map-marker-alt hph-text-primary"></i>
                    <span><?php echo esc_html($location_display); ?></span>
                </div>
                
                <!-- Description -->
                <p class="hph-hero-description hph-text-lg hph-leading-relaxed hph-mb-8 hph-opacity-95">
                    <?php echo esc_html($description_preview); ?>
                </p>
                
                <!-- Quick Stats -->
                <div class="hph-hero-stats hph-flex hph-flex-wrap hph-gap-6 hph-mb-8">
                    <?php if ($population): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-2">
                        <i class="fas fa-users hph-text-primary"></i>
                        <span class="hph-font-semibold"><?php echo number_format($population); ?> Residents</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($total_listings_count > 0): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-2">
                        <i class="fas fa-home hph-text-primary"></i>
                        <span class="hph-font-semibold"><?php echo $total_listings_count; ?> Listings</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($median_home_price): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-2">
                        <i class="fas fa-dollar-sign hph-text-primary"></i>
                        <span class="hph-font-semibold">$<?php echo number_format($median_home_price); ?> Median</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($school_rating): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-2">
                        <i class="fas fa-star hph-text-primary"></i>
                        <span class="hph-font-semibold"><?php echo esc_html($school_rating); ?>/10 Schools</span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Hero Actions -->
                <div class="hph-hero-actions hph-flex hph-flex-wrap hph-gap-4">
                    <a href="#city-listings" class="hph-btn hph-btn--primary hph-btn--lg">
                        <i class="fas fa-search hph-mr-2"></i>
                        Browse Properties
                    </a>
                    
                    <?php if ($website): ?>
                    <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener" class="hph-btn hph-btn--outline-white hph-btn--lg">
                        <i class="fas fa-external-link-alt hph-mr-2"></i>
                        City Website
                    </a>
                    <?php else: ?>
                    <a href="#city-contact" class="hph-btn hph-btn--outline-white hph-btn--lg">
                        <i class="fas fa-envelope hph-mr-2"></i>
                        Get Information
                    </a>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </section>

    <?php
    // ============================================
    // Stats Section - Market Overview
    // ============================================
    $stats_data = [];
    
    if ($population) {
        $stats_data[] = [
            'number' => number_format($population),
            'label' => 'Population',
            'icon' => 'fas fa-users'
        ];
    }
    
    if ($median_home_price) {
        $stats_data[] = [
            'number' => '$' . number_format($median_home_price),
            'label' => 'Median Home Price',
            'icon' => 'fas fa-home'
        ];
    }
    
    if ($average_price > 0) {
        $stats_data[] = [
            'number' => '$' . number_format($average_price),
            'label' => 'Average Listing Price',
            'icon' => 'fas fa-tag'
        ];
    }
    
    if ($total_listings_count > 0) {
        $stats_data[] = [
            'number' => $total_listings_count,
            'label' => 'Active Listings',
            'icon' => 'fas fa-list'
        ];
    }
    
    if ($median_income) {
        $stats_data[] = [
            'number' => '$' . number_format($median_income),
            'label' => 'Median Income',
            'icon' => 'fas fa-chart-line'
        ];
    }
    
    if ($school_rating) {
        $stats_data[] = [
            'number' => $school_rating . '/10',
            'label' => 'School Rating',
            'icon' => 'fas fa-graduation-cap'
        ];
    }
    
    if (!empty($stats_data)):
        get_template_part('template-parts/sections/stats', null, [
            'background' => 'light',
            'padding' => 'xl',
            'badge' => 'Market Overview',
            'headline' => $city_name . ' at a Glance',
            'subheadline' => 'Key statistics and market data',
            'stats' => array_slice($stats_data, 0, 6), // Limit to 6 stats for clean layout
            'columns' => min(6, count($stats_data)) > 3 ? 3 : min(6, count($stats_data)),
            'section_id' => 'city-stats'
        ]);
    endif;
    ?>

    <?php
    // ============================================
    // Features Section - City Highlights
    // ============================================
    if ($city_highlights || $top_schools || $local_attractions || $transportation):
        $features_data = [];
        
        if ($city_highlights) {
            $highlights_list = is_array($city_highlights) ? $city_highlights : explode(',', $city_highlights);
            if (!empty($highlights_list)) {
                $features_data[] = [
                    'icon' => 'fas fa-star',
                    'title' => 'City Highlights',
                    'content' => implode(' • ', array_map('trim', $highlights_list))
                ];
            }
        }
        
        if ($top_schools) {
            $schools_list = is_array($top_schools) ? $top_schools : explode(',', $top_schools);
            if (!empty($schools_list)) {
                $features_data[] = [
                    'icon' => 'fas fa-graduation-cap',
                    'title' => 'Top-Rated Schools',
                    'content' => implode(' • ', array_map('trim', $schools_list))
                ];
            }
        }
        
        if ($local_attractions) {
            $attractions_list = is_array($local_attractions) ? $local_attractions : explode(',', $local_attractions);
            if (!empty($attractions_list)) {
                $features_data[] = [
                    'icon' => 'fas fa-camera',
                    'title' => 'Local Attractions',
                    'content' => implode(' • ', array_map('trim', $attractions_list))
                ];
            }
        }
        
        if ($transportation) {
            $transport_list = is_array($transportation) ? $transportation : explode(',', $transportation);
            if (!empty($transport_list)) {
                $features_data[] = [
                    'icon' => 'fas fa-subway',
                    'title' => 'Transportation',
                    'content' => implode(' • ', array_map('trim', $transport_list))
                ];
            }
        }
        
        if (!empty($features_data)):
            get_template_part('template-parts/sections/features', null, [
                'layout' => 'grid',
                'background' => 'white',
                'padding' => 'xl',
                'columns' => min(4, count($features_data)),
                'badge' => 'Living in ' . $city_name,
                'headline' => 'Why Choose ' . $city_name . '?',
                'subheadline' => 'Discover what makes this city special',
                'features' => $features_data,
                'section_id' => 'city-features'
            ]);
        endif;
    endif;
    ?>

    <?php
    // ============================================
    // Content Section - Detailed Description
    // ============================================
    if ($city_description):
        get_template_part('template-parts/sections/content', null, [
            'layout' => 'centered',
            'background' => 'light',
            'padding' => 'xl',
            'alignment' => 'center',
            'headline' => 'About ' . $city_name,
            'content' => $city_description,
            'content_width' => 'wide',
            'animation' => true,
            'section_id' => 'city-description'
        ]);
    endif;
    ?>

    <?php
    // ============================================
    // Communities Section - Neighborhoods
    // ============================================
    
    // Query for communities in this city
    $communities_args = [
        'post_type' => 'community',
        'post_status' => 'publish',
        'posts_per_page' => 6,
        'meta_query' => [
            [
                'key' => 'city',
                'value' => $city_id,
                'compare' => '='
            ]
        ]
    ];
    
    $city_communities = new WP_Query($communities_args);
    
    if ($city_communities->have_posts()):
    ?>
    <section id="city-communities" class="hph-section hph-bg-white hph-py-24">
        <div class="hph-container">
            
            <!-- Section Header -->
            <div class="hph-text-center hph-mb-16">
                <div class="hph-badge hph-badge--primary hph-mb-4">Neighborhoods</div>
                <h2 class="hph-heading-2 hph-mb-6">Communities in <?php echo esc_html($city_name); ?></h2>
                <p class="hph-text-xl hph-text-muted hph-max-w-2xl hph-mx-auto">
                    Explore the diverse neighborhoods and communities that make up <?php echo esc_html($city_name); ?>
                </p>
            </div>
            
            <!-- Communities Grid -->
            <div class="hph-communities-grid hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 hph-gap-8 hph-mb-12">
                <?php while ($city_communities->have_posts()): $city_communities->the_post(); ?>
                    <?php get_template_part('template-parts/components/community/card-grid'); ?>
                <?php endwhile; ?>
            </div>
            
            <!-- View All Button -->
            <div class="hph-text-center">
                <a href="<?php echo esc_url(add_query_arg(['city' => $city_id], get_post_type_archive_link('community'))); ?>" 
                   class="hph-btn hph-btn--primary hph-btn--lg">
                    <i class="fas fa-map hph-mr-2"></i>
                    View All Communities (<?php echo $city_communities->found_posts; ?>)
                </a>
            </div>
            
        </div>
    </section>
    <?php 
    wp_reset_postdata();
    endif;
    ?>

    <?php
    // ============================================
    // Listings Section - Properties for Sale
    // ============================================
    
    // Query for listings in this city
    $listings_args = [
        'post_type' => 'listing',
        'post_status' => 'publish',
        'posts_per_page' => 6,
        'meta_query' => [
            [
                'key' => 'city',
                'value' => $city_id,
                'compare' => '='
            ]
        ]
    ];
    
    $city_listings = new WP_Query($listings_args);
    
    if ($city_listings->have_posts()):
    ?>
    <section id="city-listings" class="hph-section hph-bg-light hph-py-24">
        <div class="hph-container">
            
            <!-- Section Header -->
            <div class="hph-text-center hph-mb-16">
                <div class="hph-badge hph-badge--primary hph-mb-4">Properties for Sale</div>
                <h2 class="hph-heading-2 hph-mb-6">Homes for Sale in <?php echo esc_html($city_name); ?></h2>
                <p class="hph-text-xl hph-text-muted hph-max-w-2xl hph-mx-auto">
                    Discover your perfect home in <?php echo esc_html($city_name); ?> with our current property listings
                </p>
            </div>
            
            <!-- Listings Grid -->
            <div class="hph-listings-grid hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 hph-gap-8 hph-mb-12">
                <?php while ($city_listings->have_posts()): $city_listings->the_post(); ?>
                    <?php get_template_part('template-parts/components/listing/card-grid'); ?>
                <?php endwhile; ?>
            </div>
            
            <!-- View All Button -->
            <div class="hph-text-center">
                <a href="<?php echo esc_url(add_query_arg(['city' => $city_id], get_post_type_archive_link('listing'))); ?>" 
                   class="hph-btn hph-btn--primary hph-btn--lg">
                    <i class="fas fa-search hph-mr-2"></i>
                    View All Properties (<?php echo $city_listings->found_posts; ?>)
                </a>
            </div>
            
        </div>
    </section>
    <?php 
    wp_reset_postdata();
    endif;
    ?>

    <?php
    // ============================================
    // Contact Section - City Information
    // ============================================
    get_template_part('template-parts/sections/form', null, [
        'layout' => 'left-form',
        'background' => 'gradient',
        'theme' => 'primary',
        'padding' => 'xl',
        'headline' => 'Interested in Living in ' . $city_name . '?',
        'subheadline' => 'Get local market insights and property information',
        'content' => 'Our local experts have deep knowledge of the ' . $city_name . ' market. Contact us for detailed neighborhood information, market trends, or to discuss your home buying or selling needs.',
        'form_title' => 'Get City & Market Information',
        'form_fields' => [
            [
                'type' => 'text',
                'name' => 'first_name',
                'label' => 'First Name',
                'required' => true,
                'placeholder' => 'Your first name'
            ],
            [
                'type' => 'text',
                'name' => 'last_name', 
                'label' => 'Last Name',
                'required' => true,
                'placeholder' => 'Your last name'
            ],
            [
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email Address',
                'required' => true,
                'placeholder' => 'your.email@example.com'
            ],
            [
                'type' => 'tel',
                'name' => 'phone',
                'label' => 'Phone Number',
                'placeholder' => '(555) 123-4567'
            ],
            [
                'type' => 'select',
                'name' => 'interest_type',
                'label' => 'I\'m interested in...',
                'options' => [
                    'buying' => 'Buying a Home',
                    'selling' => 'Selling a Home',
                    'market-info' => 'Market Information',
                    'investment' => 'Investment Properties',
                    'relocation' => 'Relocation Assistance'
                ]
            ],
            [
                'type' => 'textarea',
                'name' => 'message',
                'label' => 'Questions or Comments',
                'placeholder' => 'Tell us what you\'d like to know about living in ' . $city_name . '...',
                'rows' => 4
            ],
            [
                'type' => 'hidden',
                'name' => 'city_id',
                'value' => $city_id
            ],
            [
                'type' => 'hidden',
                'name' => 'subject',
                'value' => 'City Inquiry - ' . $city_name . ', ' . $state
            ]
        ],
        'submit_text' => 'Get Information',
        'section_id' => 'city-contact'
    ]);
    ?>

    <?php
    // ============================================
    // Map Section - City Location
    // ============================================
    if ($latitude && $longitude):
        get_template_part('template-parts/sections/map', null, [
            'background' => 'light',
            'padding' => 'xl',
            'headline' => 'Explore ' . $city_name,
            'subheadline' => 'See the location and surrounding areas',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => 12,
            'markers' => [
                [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'title' => $city_name,
                    'description' => $location_display,
                    'type' => 'city'
                ]
            ],
            'section_id' => 'city-map'
        ]);
    endif;
    ?>

<?php get_footer(); ?>
