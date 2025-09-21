<?php
/**
 * Taxonomy Template: Property Status Archive
 * 
 * Displays listings filtered by property status (active, pending, sold)
 * Uses the unified section system with status-specific filtering and features:
 * - Hero section with status-specific messaging
 * - Quick filters for additional refinement
 * - Archive layout integration for listings display
 * - Map section showing all properties with this status
 * - Contact section for status-specific inquiries
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get current taxonomy term
$current_term = get_queried_object();
$status_slug = $current_term->slug;
$status_name = $current_term->name;
$status_description = $current_term->description;

// Status-specific configurations
$status_config = [
    'active' => [
        'icon' => 'fas fa-home',
        'color' => 'success',
        'description' => 'Browse our current active listings and find your perfect home today.',
        'cta' => 'Find Your Home',
        'badge' => 'Available Now'
    ],
    'pending' => [
        'icon' => 'fas fa-clock',
        'color' => 'warning', 
        'description' => 'Properties under contract but not yet closed. Great insights into market trends.',
        'cta' => 'View Pending Sales',
        'badge' => 'Under Contract'
    ],
    'sold' => [
        'icon' => 'fas fa-check-circle',
        'color' => 'info',
        'description' => 'Recently sold properties showing current market values and trends.',
        'cta' => 'See Market Data',
        'badge' => 'Recently Sold'
    ]
];

$config = $status_config[$status_slug] ?? $status_config['active'];

// Count listings for this status
$listings_count = wp_count_posts('listing');
$total_count = 0;

// Get the proper status count
switch ($status_slug) {
    case 'active':
        $total_count = intval($listings_count->publish ?? 0);
        break;
    case 'pending':
        $total_count = intval($listings_count->pending ?? 0);
        break;
    case 'sold':
        $total_count = intval($listings_count->sold ?? 0);
        break;
}

// Get recent market stats for this status
$recent_query_args = [
    'post_type' => 'listing',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => 'status',
            'value' => $status_slug,
            'compare' => '='
        ]
    ],
    'fields' => 'ids'
];

if ($status_slug === 'sold') {
    // For sold properties, look at recent sales (last 6 months)
    $recent_query_args['meta_query'][] = [
        'key' => 'sold_date',
        'value' => date('Y-m-d', strtotime('-6 months')),
        'compare' => '>='
    ];
}

$recent_listings = new WP_Query($recent_query_args);
$average_price = 0;
$price_range_min = 0;
$price_range_max = 0;

if ($recent_listings->have_posts()) {
    $prices = [];
    foreach ($recent_listings->posts as $listing_id) {
        $price = get_field('price', $listing_id);
        if ($price && is_numeric($price)) {
            $prices[] = $price;
        }
    }
    
    if (!empty($prices)) {
        $average_price = array_sum($prices) / count($prices);
        $price_range_min = min($prices);
        $price_range_max = max($prices);
    }
}
wp_reset_postdata();

// Hero background image based on status
$hero_images = [
    'active' => get_template_directory_uri() . '/assets/images/hero-active-listings.jpg',
    'pending' => get_template_directory_uri() . '/assets/images/hero-pending-listings.jpg', 
    'sold' => get_template_directory_uri() . '/assets/images/hero-sold-listings.jpg'
];

$hero_image = $hero_images[$status_slug] ?? $hero_images['active'];
?>

    <?php
    // ============================================
    // Hero Section - Property Status Overview
    // ============================================
    ?>

    <!-- Custom Hero Section -->
    <section 
        class="hph-hero-section hph-status-hero hph-status-<?php echo esc_attr($status_slug); ?>"
        id="hero-status-<?php echo esc_attr($status_slug); ?>"
        data-bg="image"
        style="position: relative; width: 100%; min-height: 70vh; background-image: url('<?php echo esc_url($hero_image); ?>'); background-size: cover; background-position: center; color: var(--hph-white);"
        data-animation="true"
    >
        <!-- Gradient Overlay -->
        <div class="hph-absolute hph-inset-0 hph-bg-gradient-to-r hph-from-black hph-via-black/70 hph-to-transparent hph-opacity-70"></div>
        
        <!-- Hero Content -->
        <div class="hph-container hph-relative hph-z-10 hph-h-full hph-flex hph-items-center">
            <div class="hph-hero-content hph-max-w-3xl">
                
                <!-- Status Badge -->
                <div class="hph-hero-badge hph-inline-flex hph-items-center hph-gap-2 hph-bg-white/20 hph-backdrop-blur-sm hph-rounded-full hph-px-4 hph-py-2 hph-text-sm hph-font-medium hph-mb-6">
                    <i class="<?php echo esc_attr($config['icon']); ?> hph-text-primary"></i>
                    <span><?php echo esc_html($config['badge']); ?></span>
                </div>
                
                <!-- Main Heading -->
                <h1 class="hph-hero-title hph-text-4xl md:hph-text-6xl hph-font-bold hph-leading-tight hph-mb-6">
                    <?php echo esc_html($status_name); ?> Properties
                </h1>
                
                <!-- Description -->
                <p class="hph-hero-description hph-text-xl hph-leading-relaxed hph-mb-8 hph-opacity-95">
                    <?php echo esc_html($status_description ?: $config['description']); ?>
                </p>
                
                <!-- Status Stats -->
                <div class="hph-hero-stats hph-flex hph-flex-wrap hph-gap-6 hph-mb-8">
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-2">
                        <i class="fas fa-list hph-text-primary"></i>
                        <span class="hph-font-semibold"><?php echo number_format($total_count); ?> Properties</span>
                    </div>
                    
                    <?php if ($average_price > 0): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-2">
                        <i class="fas fa-chart-line hph-text-primary"></i>
                        <span class="hph-font-semibold">$<?php echo number_format($average_price); ?> Avg Price</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($price_range_min > 0 && $price_range_max > 0): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-2">
                        <i class="fas fa-dollar-sign hph-text-primary"></i>
                        <span class="hph-font-semibold">$<?php echo number_format($price_range_min); ?> - $<?php echo number_format($price_range_max); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Hero Actions -->
                <div class="hph-hero-actions hph-flex hph-flex-wrap hph-gap-4">
                    <a href="#listings-archive" class="hph-btn hph-btn--primary hph-btn--lg">
                        <i class="<?php echo esc_attr($config['icon']); ?> hph-mr-2"></i>
                        <?php echo esc_html($config['cta']); ?>
                    </a>
                    
                    <?php if ($status_slug === 'active'): ?>
                    <a href="#contact-form" class="hph-btn hph-btn--outline-white hph-btn--lg">
                        <i class="fas fa-envelope hph-mr-2"></i>
                        Get Alerts
                    </a>
                    <?php elseif ($status_slug === 'sold'): ?>
                    <a href="#market-stats" class="hph-btn hph-btn--outline-white hph-btn--lg">
                        <i class="fas fa-chart-bar hph-mr-2"></i>
                        Market Report
                    </a>
                    <?php else: ?>
                    <a href="#contact-form" class="hph-btn hph-btn--outline-white hph-btn--lg">
                        <i class="fas fa-info-circle hph-mr-2"></i>
                        Learn More
                    </a>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </section>

    <?php
    // ============================================
    // Stats Section - Market Data
    // ============================================
    if ($average_price > 0 || $total_count > 0):
        $stats_data = [];
        
        if ($total_count > 0) {
            $stats_data[] = [
                'number' => number_format($total_count),
                'label' => $status_name . ' Properties',
                'icon' => $config['icon']
            ];
        }
        
        if ($average_price > 0) {
            $stats_data[] = [
                'number' => '$' . number_format($average_price),
                'label' => 'Average Price',
                'icon' => 'fas fa-chart-line'
            ];
        }
        
        if ($price_range_min > 0) {
            $stats_data[] = [
                'number' => '$' . number_format($price_range_min),
                'label' => 'Starting From',
                'icon' => 'fas fa-arrow-up'
            ];
        }
        
        if ($price_range_max > 0) {
            $stats_data[] = [
                'number' => '$' . number_format($price_range_max),
                'label' => 'Up To',
                'icon' => 'fas fa-arrow-down'
            ];
        }
        
        // Add market velocity for active/pending
        if ($status_slug === 'active' || $status_slug === 'pending') {
            $days_on_market = get_field('average_days_on_market', 'option');
            if ($days_on_market) {
                $stats_data[] = [
                    'number' => $days_on_market,
                    'label' => 'Avg Days on Market',
                    'icon' => 'fas fa-clock'
                ];
            }
        }
        
        if (!empty($stats_data)):
            get_template_part('template-parts/sections/stats', null, [
                'background' => 'light',
                'padding' => 'xl',
                'badge' => 'Market Data',
                'headline' => $status_name . ' Properties Overview',
                'subheadline' => 'Current market statistics and trends',
                'stats' => $stats_data,
                'columns' => min(4, count($stats_data)),
                'section_id' => 'market-stats'
            ]);
        endif;
    endif;
    ?>

    <?php
    // ============================================
    // Archive Section - Listings Display
    // ============================================
    ?>
    
    <div id="listings-archive">
        <?php get_template_part('template-parts/archive/layout', null, [
            'post_type' => 'listing',
            'taxonomy' => 'property_status',
            'term' => $current_term,
            'layout' => 'grid',
            'filters' => true,
            'map' => true,
            'pagination' => true,
            'section_id' => 'listings-content'
        ]); ?>
    </div>

    <?php
    // ============================================
    // Contact Section - Status-Specific Inquiries
    // ============================================
    $contact_config = [
        'active' => [
            'headline' => 'Ready to Find Your Home?',
            'subheadline' => 'Get personalized property alerts and expert guidance',
            'content' => 'Stay ahead of the market with instant notifications when new properties match your criteria. Our experienced agents will help you navigate the buying process.',
            'form_title' => 'Get Property Alerts',
            'interests' => [
                'alerts' => 'Property Alerts',
                'viewing' => 'Schedule Viewings', 
                'buying' => 'Buying Guidance',
                'financing' => 'Financing Options'
            ]
        ],
        'pending' => [
            'headline' => 'Interested in Pending Properties?',
            'subheadline' => 'Learn about backup offers and market trends',
            'content' => 'Pending properties can still present opportunities through backup offers. Our team can help you understand the process and position yourself strategically.',
            'form_title' => 'Backup Offer Information',
            'interests' => [
                'backup' => 'Backup Offers',
                'similar' => 'Similar Properties',
                'market' => 'Market Analysis',
                'timing' => 'Timing Strategy'
            ]
        ],
        'sold' => [
            'headline' => 'Want to Know Your Home\'s Value?',
            'subheadline' => 'Get a personalized market analysis',
            'content' => 'Recent sales data helps determine accurate property values. Get a comprehensive market analysis for your home or area of interest.',
            'form_title' => 'Free Market Analysis',
            'interests' => [
                'valuation' => 'Home Valuation',
                'selling' => 'Selling Process',
                'market' => 'Market Trends',
                'timing' => 'Best Time to Sell'
            ]
        ]
    ];
    
    $contact_info = $contact_config[$status_slug] ?? $contact_config['active'];
    
    get_template_part('template-parts/sections/form', null, [
        'layout' => 'left-form',
        'background' => 'gradient',
        'theme' => 'primary',
        'padding' => 'xl',
        'headline' => $contact_info['headline'],
        'subheadline' => $contact_info['subheadline'],
        'content' => $contact_info['content'],
        'form_title' => $contact_info['form_title'],
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
                'options' => $contact_info['interests']
            ],
            [
                'type' => 'textarea',
                'name' => 'message',
                'label' => 'Additional Information',
                'placeholder' => 'Tell us about your specific needs or questions...',
                'rows' => 4
            ],
            [
                'type' => 'hidden',
                'name' => 'property_status',
                'value' => $status_slug
            ],
            [
                'type' => 'hidden',
                'name' => 'subject',
                'value' => $status_name . ' Properties Inquiry'
            ]
        ],
        'submit_text' => 'Get Information',
        'section_id' => 'contact-form'
    ]);
    ?>

<?php get_footer(); ?>
