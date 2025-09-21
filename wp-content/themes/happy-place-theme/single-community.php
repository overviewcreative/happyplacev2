<?php
/**
 * Single Community Template - Modern Section-Based Design
 * 
 * Complete community profile using the unified section system:
 * - Hero section with community image and key info
 * - Stats section showcasing community features
 * - Features section highlighting amenities
 * - Content section with detailed description
 * - Listings showcase for properties in this community
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

// Get community ID and verify it exists
$community_id = get_the_ID();
if (!$community_id || get_post_type($community_id) !== 'community') {
    get_template_part('template-parts/base/content-none');
    get_footer();
    return;
}

// Get community data
$community_name = get_the_title($community_id);
$community_description = get_the_content();
$short_description = get_field('short_description', $community_id);
$location = get_field('location', $community_id);
$city = get_field('city', $community_id);
$state = get_field('state', $community_id);
$zip_code = get_field('zip_code', $community_id);

// Community stats
$total_homes = get_field('total_homes', $community_id);
$year_built = get_field('year_built', $community_id);
$price_range_min = get_field('price_range_min', $community_id);
$price_range_max = get_field('price_range_max', $community_id);
$lot_size_range = get_field('lot_size_range', $community_id);
$hoa_fee = get_field('hoa_fee', $community_id);

// Amenities and features
$amenities = get_field('amenities', $community_id);
$schools = get_field('schools', $community_id);
$nearby_attractions = get_field('nearby_attractions', $community_id);

// Contact and location
$latitude = get_field('latitude', $community_id);
$longitude = get_field('longitude', $community_id);
$website = get_field('website', $community_id);
$phone = get_field('phone', $community_id);

// Get community image
$community_image = get_the_post_thumbnail_url($community_id, 'full');
if (!$community_image) {
    $community_image = get_template_directory_uri() . '/assets/images/placeholder-community.jpg';
}

// Build address string
$full_address = '';
if ($location) {
    $full_address = $location;
    if ($city) $full_address .= ', ' . $city;
    if ($state) $full_address .= ', ' . $state;
    if ($zip_code) $full_address .= ' ' . $zip_code;
} elseif ($city && $state) {
    $full_address = $city . ', ' . $state;
    if ($zip_code) $full_address .= ' ' . $zip_code;
}

// Format price range
$price_range_display = '';
if ($price_range_min && $price_range_max) {
    $price_range_display = '$' . number_format($price_range_min) . ' - $' . number_format($price_range_max);
} elseif ($price_range_min) {
    $price_range_display = 'From $' . number_format($price_range_min);
} elseif ($price_range_max) {
    $price_range_display = 'Up to $' . number_format($price_range_max);
}
?>

    <?php
    // ============================================
    // Hero Section - Community Profile
    // ============================================
    $description_preview = $short_description ?: ($community_description ? wp_trim_words(strip_tags($community_description), 25, '...') : 'Discover life in this exceptional community with modern amenities and convenient location.');
    ?>

    <!-- Custom Hero Section with Community Image -->
    <section 
        class="hph-hero-section hph-community-hero"
        id="hero-community-<?php echo esc_attr($community_id); ?>"
        data-bg="image"
        style="position: relative; width: 100%; min-height: 70vh; background-image: url('<?php echo esc_url($community_image); ?>'); background-size: cover; background-position: center; color: var(--hph-white);"
        data-animation="true"
    >
        <!-- Gradient Overlay -->
        <div class="hph-absolute hph-inset-0 hph-bg-gradient-to-r hph-from-black hph-via-black/70 hph-to-transparent hph-opacity-60"></div>
        
        <!-- Hero Content -->
        <div class="hph-container hph-relative hph-z-10 hph-h-full hph-flex hph-items-center">
            <div class="hph-hero-content hph-max-w-2xl">
                
                <!-- Community Badge -->
                <div class="hph-hero-badge hph-inline-flex hph-items-center hph-gap-2 hph-bg-white/20 hph-backdrop-blur-sm hph-rounded-full hph-px-4 hph-py-2 hph-text-sm hph-font-medium hph-mb-6">
                    <i class="fas fa-map-marker-alt hph-text-primary"></i>
                    <span>Community</span>
                </div>
                
                <!-- Community Name -->
                <h1 class="hph-hero-title hph-text-4xl md:hph-text-6xl hph-font-bold hph-leading-tight hph-mb-6">
                    <?php echo esc_html($community_name); ?>
                </h1>
                
                <!-- Location -->
                <?php if ($full_address): ?>
                <div class="hph-hero-location hph-flex hph-items-center hph-gap-2 hph-text-xl hph-mb-6 hph-opacity-95">
                    <i class="fas fa-map-marker-alt hph-text-primary"></i>
                    <span><?php echo esc_html($full_address); ?></span>
                </div>
                <?php endif; ?>
                
                <!-- Description -->
                <p class="hph-hero-description hph-text-lg hph-leading-relaxed hph-mb-8 hph-opacity-95">
                    <?php echo esc_html($description_preview); ?>
                </p>
                
                <!-- Quick Stats -->
                <div class="hph-hero-stats hph-flex hph-flex-wrap hph-gap-6 hph-mb-8">
                    <?php if ($total_homes): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-2">
                        <i class="fas fa-home hph-text-primary"></i>
                        <span class="hph-font-semibold"><?php echo esc_html($total_homes); ?> Homes</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($year_built): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-2">
                        <i class="fas fa-calendar hph-text-primary"></i>
                        <span class="hph-font-semibold">Built <?php echo esc_html($year_built); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($price_range_display): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-2">
                        <i class="fas fa-dollar-sign hph-text-primary"></i>
                        <span class="hph-font-semibold"><?php echo esc_html($price_range_display); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Hero Actions -->
                <div class="hph-hero-actions hph-flex hph-flex-wrap hph-gap-4">
                    <a href="#community-listings" class="hph-btn hph-btn--primary hph-btn--lg">
                        <i class="fas fa-search hph-mr-2"></i>
                        View Available Homes
                    </a>
                    
                    <?php if ($website): ?>
                    <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener" class="hph-btn hph-btn--outline-white hph-btn--lg">
                        <i class="fas fa-external-link-alt hph-mr-2"></i>
                        Visit Website
                    </a>
                    <?php else: ?>
                    <a href="#community-contact" class="hph-btn hph-btn--outline-white hph-btn--lg">
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
    // Stats Section - Community Overview
    // ============================================
    $stats_data = [];
    
    if ($total_homes) {
        $stats_data[] = [
            'number' => $total_homes,
            'label' => 'Total Homes',
            'icon' => 'fas fa-home'
        ];
    }
    
    if ($year_built) {
        $stats_data[] = [
            'number' => $year_built,
            'label' => 'Year Built',
            'icon' => 'fas fa-calendar'
        ];
    }
    
    if ($hoa_fee) {
        $stats_data[] = [
            'number' => '$' . number_format($hoa_fee),
            'label' => 'Monthly HOA',
            'icon' => 'fas fa-dollar-sign'
        ];
    }
    
    if ($lot_size_range) {
        $stats_data[] = [
            'number' => $lot_size_range,
            'label' => 'Lot Sizes',
            'icon' => 'fas fa-ruler-combined'
        ];
    }
    
    if (!empty($stats_data)):
        get_template_part('template-parts/sections/stats', null, [
            'background' => 'light',
            'padding' => 'xl',
            'badge' => 'Community Overview',
            'headline' => 'Community at a Glance',
            'subheadline' => 'Key details about ' . $community_name,
            'stats' => $stats_data,
            'columns' => count($stats_data) > 3 ? 4 : count($stats_data),
            'section_id' => 'community-stats'
        ]);
    endif;
    ?>

    <?php
    // ============================================
    // Features Section - Amenities & Features
    // ============================================
    if ($amenities || $schools || $nearby_attractions):
        $features_data = [];
        
        if ($amenities) {
            $amenities_list = is_array($amenities) ? $amenities : explode(',', $amenities);
            if (!empty($amenities_list)) {
                $features_data[] = [
                    'icon' => 'fas fa-swimming-pool',
                    'title' => 'Community Amenities',
                    'content' => implode(' • ', array_map('trim', $amenities_list))
                ];
            }
        }
        
        if ($schools) {
            $schools_list = is_array($schools) ? $schools : explode(',', $schools);
            if (!empty($schools_list)) {
                $features_data[] = [
                    'icon' => 'fas fa-graduation-cap',
                    'title' => 'Nearby Schools',
                    'content' => implode(' • ', array_map('trim', $schools_list))
                ];
            }
        }
        
        if ($nearby_attractions) {
            $attractions_list = is_array($nearby_attractions) ? $nearby_attractions : explode(',', $nearby_attractions);
            if (!empty($attractions_list)) {
                $features_data[] = [
                    'icon' => 'fas fa-map-signs',
                    'title' => 'Nearby Attractions',
                    'content' => implode(' • ', array_map('trim', $attractions_list))
                ];
            }
        }
        
        if (!empty($features_data)):
            get_template_part('template-parts/sections/features', null, [
                'layout' => 'grid',
                'background' => 'white',
                'padding' => 'xl',
                'columns' => min(3, count($features_data)),
                'badge' => 'Community Features',
                'headline' => 'Life in ' . $community_name,
                'subheadline' => 'Discover what makes this community special',
                'features' => $features_data,
                'section_id' => 'community-features'
            ]);
        endif;
    endif;
    ?>

    <?php
    // ============================================
    // Content Section - Detailed Description
    // ============================================
    if ($community_description):
        get_template_part('template-parts/sections/content', null, [
            'layout' => 'centered',
            'background' => 'light',
            'padding' => 'xl',
            'alignment' => 'center',
            'headline' => 'About ' . $community_name,
            'content' => $community_description,
            'content_width' => 'wide',
            'animation' => true,
            'section_id' => 'community-description'
        ]);
    endif;
    ?>

    <?php
    // ============================================
    // Listings Section - Available Properties
    // ============================================
    
    // Query for listings in this community
    $listings_args = [
        'post_type' => 'listing',
        'post_status' => 'publish',
        'posts_per_page' => 6,
        'meta_query' => [
            [
                'key' => 'community',
                'value' => $community_id,
                'compare' => '='
            ]
        ]
    ];
    
    $community_listings = new WP_Query($listings_args);
    
    if ($community_listings->have_posts()):
    ?>
    <section id="community-listings" class="hph-section hph-bg-white hph-py-24">
        <div class="hph-container">
            
            <!-- Section Header -->
            <div class="hph-text-center hph-mb-16">
                <div class="hph-badge hph-badge--primary hph-mb-4">Available Properties</div>
                <h2 class="hph-heading-2 hph-mb-6">Homes for Sale in <?php echo esc_html($community_name); ?></h2>
                <p class="hph-text-xl hph-text-muted hph-max-w-2xl hph-mx-auto">
                    Discover your perfect home in this exceptional community
                </p>
            </div>
            
            <!-- Listings Grid -->
            <div class="hph-listings-grid hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 hph-gap-8 hph-mb-12">
                <?php while ($community_listings->have_posts()): $community_listings->the_post(); ?>
                    <?php get_template_part('template-parts/components/listing/card-grid'); ?>
                <?php endwhile; ?>
            </div>
            
            <!-- View All Button -->
            <div class="hph-text-center">
                <a href="<?php echo esc_url(add_query_arg(['community' => $community_id], get_post_type_archive_link('listing'))); ?>" 
                   class="hph-btn hph-btn--primary hph-btn--lg">
                    <i class="fas fa-search hph-mr-2"></i>
                    View All Properties (<?php echo $community_listings->found_posts; ?>)
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
    // Contact Section - Get Information
    // ============================================
    get_template_part('template-parts/sections/form', null, [
        'layout' => 'left-form',
        'background' => 'gradient',
        'theme' => 'primary',
        'padding' => 'xl',
        'headline' => 'Interested in ' . $community_name . '?',
        'subheadline' => 'Get more information about this community',
        'content' => 'Our local experts know this community inside and out. Contact us for detailed information, upcoming listings, or to schedule a private tour.',
        'form_title' => 'Get Community Information',
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
                'type' => 'textarea',
                'name' => 'message',
                'label' => 'Questions or Comments',
                'placeholder' => 'Tell us what you\'d like to know about ' . $community_name . '...',
                'rows' => 4
            ],
            [
                'type' => 'hidden',
                'name' => 'community_id',
                'value' => $community_id
            ],
            [
                'type' => 'hidden',
                'name' => 'subject',
                'value' => 'Community Inquiry - ' . $community_name
            ]
        ],
        'submit_text' => 'Get Information',
        'section_id' => 'community-contact'
    ]);
    ?>

    <?php
    // ============================================
    // Map Section - Community Location
    // ============================================
    if ($latitude && $longitude):
        get_template_part('template-parts/sections/map', null, [
            'background' => 'light',
            'padding' => 'xl',
            'headline' => 'Community Location',
            'subheadline' => 'Explore the area around ' . $community_name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => 14,
            'markers' => [
                [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'title' => $community_name,
                    'description' => $full_address,
                    'type' => 'community'
                ]
            ],
            'section_id' => 'community-map'
        ]);
    endif;
    ?>

<?php get_footer(); ?>
