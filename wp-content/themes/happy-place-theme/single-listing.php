<?php
/**
 * Single Listing Template - Component-Based Architecture
 * 
 * Uses the new component system with listing bridge functions for data access.
 * Features complete property details, gallery, agent info, and contact functionality.
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get listing ID and verify it exists
$listing_id = get_the_ID();
if (!$listing_id || get_post_type($listing_id) !== 'listing') {
    hph_component('content-none');
    get_footer();
    return;
}

// Get listing data using bridge functions
$listing_data = hpt_get_listing($listing_id);
if (!$listing_data) {
    hph_component('content-none');
    get_footer();
    return;
}

// Check if listing is viewable
if (!current_user_can('read_post', $listing_id)) {
    hph_component('content-none');
    get_footer();
    return;
}

// Get related data
$agent_data = null;
if (!empty($listing_data['agent_id'])) {
    $agent_data = hpt_get_agent($listing_data['agent_id']);
}

// Prepare single layout arguments
$single_args = [
    'post_type' => 'listing',
    'post_id' => $listing_id,
    'title' => $listing_data['title'],
    'data' => $listing_data,
    'agent' => $agent_data,
    'show_sidebar' => true,
    'sidebar_id' => 'listing-single',
    'layout' => 'full-width'
];
?>

<div class="hph-page hph-single-page" data-post-type="listing" data-post-id="<?php echo esc_attr($listing_id); ?>">
    
    <?php
    // Load the main single layout component
    hph_component('single-layout', $single_args);
    ?>
    
    <!-- Listing Details Sections -->
    <div class="hph-listing-sections">
        
        <!-- Photo Gallery Section -->
        <?php if (!empty($listing_data['gallery'])): ?>
        <section class="hph-listing-gallery hph-py-0">
            <div class="hph-container-fluid">
                <?php
                hph_component('listing-gallery', [
                    'images' => $listing_data['gallery'],
                    'listing_id' => $listing_id,
                    'style' => 'hero',
                    'show_thumbnails' => true,
                    'lightbox' => true
                ]);
                ?>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Main Content Area -->
        <section class="hph-listing-content hph-py-12">
            <div class="hph-container">
                <div class="hph-grid hph-grid-cols-12 hph-gap-8">
                    
                    <!-- Primary Content -->
                    <div class="hph-col-span-12 lg:hph-col-span-8">
                        
                        <!-- Property Header -->
                        <div class="hph-listing-header hph-mb-8">
                            <?php
                            hph_component('listing-header', [
                                'listing_data' => $listing_data,
                                'show_price' => true,
                                'show_status' => true,
                                'show_address' => true,
                                'show_mls' => true,
                                'show_actions' => true
                            ]);
                            ?>
                        </div>
                        
                        <!-- Property Details -->
                        <div class="hph-listing-details hph-mb-8">
                            <?php
                            hph_component('listing-details', [
                                'listing_data' => $listing_data,
                                'style' => 'detailed',
                                'show_all' => true
                            ]);
                            ?>
                        </div>
                        
                        <!-- Property Description -->
                        <?php if (!empty($listing_data['description'])): ?>
                        <div class="hph-listing-description hph-mb-8">
                            <div class="hph-card">
                                <div class="hph-card__header">
                                    <h3 class="hph-card__title">
                                        <?php _e('Property Description', 'happy-place-theme'); ?>
                                    </h3>
                                </div>
                                <div class="hph-card__content">
                                    <div class="hph-prose">
                                        <?php echo wp_kses_post($listing_data['description']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Property Features -->
                        <?php if (!empty($listing_data['features'])): ?>
                        <div class="hph-listing-features hph-mb-8">
                            <?php
                            hph_component('listing-features', [
                                'features' => $listing_data['features'],
                                'listing_id' => $listing_id,
                                'style' => 'grid'
                            ]);
                            ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Floor Plans -->
                        <?php if (!empty($listing_data['floor_plans'])): ?>
                        <div class="hph-listing-floor-plans hph-mb-8">
                            <?php
                            hph_component('listing-floor-plans', [
                                'floor_plans' => $listing_data['floor_plans'],
                                'listing_id' => $listing_id
                            ]);
                            ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Virtual Tour -->
                        <?php if (!empty($listing_data['virtual_tour'])): ?>
                        <div class="hph-listing-virtual-tour hph-mb-8">
                            <?php
                            hph_component('listing-virtual-tour', [
                                'virtual_tour' => $listing_data['virtual_tour'],
                                'listing_id' => $listing_id
                            ]);
                            ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Mortgage Calculator -->
                        <div class="hph-mortgage-calculator hph-mb-8">
                            <?php
                            hph_component('mortgage-calculator', [
                                'listing_price' => $listing_data['price'],
                                'listing_id' => $listing_id,
                                'style' => 'embedded'
                            ]);
                            ?>
                        </div>
                        
                        <!-- Neighborhood Info -->
                        <?php if (!empty($listing_data['neighborhood'])): ?>
                        <div class="hph-neighborhood-info hph-mb-8">
                            <?php
                            hph_component('neighborhood-info', [
                                'neighborhood' => $listing_data['neighborhood'],
                                'listing_location' => $listing_data['location'] ?? null
                            ]);
                            ?>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="hph-col-span-12 lg:hph-col-span-4">
                        
                        <!-- Agent Information -->
                        <?php if ($agent_data): ?>
                        <div class="hph-agent-card hph-mb-8">
                            <?php
                            hph_component('agent-card', [
                                'agent_data' => $agent_data,
                                'style' => 'detailed',
                                'show_contact' => true,
                                'show_stats' => true,
                                'context' => 'listing'
                            ]);
                            ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Contact Form -->
                        <div class="hph-contact-form hph-mb-8">
                            <?php
                            hph_component('listing-contact-form', [
                                'listing_data' => $listing_data,
                                'agent_data' => $agent_data,
                                'form_type' => 'inquiry'
                            ]);
                            ?>
                        </div>
                        
                        <!-- Property Actions -->
                        <div class="hph-property-actions hph-mb-8">
                            <?php
                            hph_component('property-actions', [
                                'listing_id' => $listing_id,
                                'listing_data' => $listing_data,
                                'actions' => ['favorite', 'share', 'print', 'schedule']
                            ]);
                            ?>
                        </div>
                        
                        <!-- Similar Listings -->
                        <?php
                        // Get similar listings
                        $similar_listings = hph_get_similar_listings($listing_id, 3);
                        if (!empty($similar_listings)):
                        ?>
                        <div class="hph-similar-listings hph-mb-8">
                            <div class="hph-card">
                                <div class="hph-card__header">
                                    <h3 class="hph-card__title">
                                        <?php _e('Similar Properties', 'happy-place-theme'); ?>
                                    </h3>
                                </div>
                                <div class="hph-card__content">
                                    <?php
                                    hph_component('card-list', [
                                        'posts' => $similar_listings,
                                        'style' => 'compact',
                                        'show_excerpt' => false,
                                        'show_price' => true
                                    ]);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    
                </div>
            </div>
        </section>
        
        <!-- Property Location/Map -->
        <?php if (!empty($listing_data['location'])): ?>
        <section class="hph-listing-map hph-py-12 hph-bg-gray-50">
            <div class="hph-container">
                <div class="hph-section-header hph-mb-8">
                    <h2 class="hph-section-title"><?php _e('Location & Neighborhood', 'happy-place-theme'); ?></h2>
                </div>
                
                <?php
                hph_component('listing-map', [
                    'listing_data' => $listing_data,
                    'show_address' => false, // Don't show exact address for privacy
                    'show_nearby' => true,
                    'height' => '400px'
                ]);
                ?>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Schedule Showing -->
        <section class="hph-schedule-showing hph-py-12">
            <div class="hph-container">
                <div class="hph-text-center hph-mb-8">
                    <h2 class="hph-section-title"><?php _e('Schedule a Showing', 'happy-place-theme'); ?></h2>
                    <p class="hph-section-subtitle">
                        <?php _e('Ready to see this property in person? Schedule a showing today.', 'happy-place-theme'); ?>
                    </p>
                </div>
                
                <div class="hph-max-w-lg hph-mx-auto">
                    <?php
                    hph_component('schedule-showing-form', [
                        'listing_id' => $listing_id,
                        'agent_data' => $agent_data
                    ]);
                    ?>
                </div>
            </div>
        </section>
        
    </div>
    
</div>

<?php
// Helper function to get similar listings
function hph_get_similar_listings($listing_id, $limit = 3) {
    $current_listing = hpt_get_listing($listing_id);
    if (!$current_listing) {
        return [];
    }
    
    // Build query for similar listings
    $args = [
        'post_type' => 'listing',
        'posts_per_page' => $limit,
        'post__not_in' => [$listing_id],
        'post_status' => 'publish',
        'meta_query' => ['relation' => 'AND']
    ];
    
    // Similar price range (±20%)
    if (!empty($current_listing['price'])) {
        $price_min = $current_listing['price'] * 0.8;
        $price_max = $current_listing['price'] * 1.2;
        
        $args['meta_query'][] = [
            'key' => '_listing_price',
            'value' => [$price_min, $price_max],
            'compare' => 'BETWEEN',
            'type' => 'NUMERIC'
        ];
    }
    
    // Similar bedrooms
    if (!empty($current_listing['bedrooms'])) {
        $args['meta_query'][] = [
            'key' => '_listing_bedrooms',
            'value' => [$current_listing['bedrooms'] - 1, $current_listing['bedrooms'] + 1],
            'compare' => 'BETWEEN',
            'type' => 'NUMERIC'
        ];
    }
    
    // Same property type
    if (!empty($current_listing['property_type'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $current_listing['property_type']['slug']
            ]
        ];
    }
    
    $similar_query = new WP_Query($args);
    $similar_listings = [];
    
    if ($similar_query->have_posts()) {
        while ($similar_query->have_posts()) {
            $similar_query->the_post();
            $similar_listings[] = get_post();
        }
        wp_reset_postdata();
    }
    
    return $similar_listings;
}

// Enqueue page-specific assets
wp_enqueue_style('hph-single-listing', HPH_THEME_URI . '/assets/css/framework/05-pages/hph-single-listing.css', ['hph-framework'], HPH_VERSION);
wp_enqueue_script('hph-single-listing', HPH_THEME_URI . '/assets/js/pages/single-listing.js', ['hph-framework-core'], HPH_VERSION, true);

// Enqueue gallery and lightbox assets
wp_enqueue_script('hph-listing-gallery', HPH_THEME_URI . '/assets/js/components/listing/listing-gallery.js', ['hph-framework-core'], HPH_VERSION, true);

// Localize script with listing context
wp_localize_script('hph-single-listing', 'hphListing', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_listing_nonce'),
    'listingId' => $listing_id,
    'listingPrice' => $listing_data['price'] ?? 0,
    'listingAddress' => $listing_data['address'] ?? '',
    'agentId' => $agent_data['id'] ?? null,
    'isLoggedIn' => is_user_logged_in(),
    'userId' => get_current_user_id(),
    'hasVirtualTour' => !empty($listing_data['virtual_tour']),
    'hasFloorPlans' => !empty($listing_data['floor_plans']),
    'strings' => [
        'loading' => __('Loading...', 'happy-place-theme'),
        'contactAgent' => __('Contact Agent', 'happy-place-theme'),
        'scheduleShowing' => __('Schedule Showing', 'happy-place-theme'),
        'addToFavorites' => __('Add to Favorites', 'happy-place-theme'),
        'removeFromFavorites' => __('Remove from Favorites', 'happy-place-theme'),
        'shareProperty' => __('Share Property', 'happy-place-theme'),
        'printProperty' => __('Print Details', 'happy-place-theme'),
        'error' => __('An error occurred. Please try again.', 'happy-place-theme'),
        'showingRequested' => __('Showing request sent successfully!', 'happy-place-theme'),
        'loginRequired' => __('Please log in to save favorites.', 'happy-place-theme')
    ]
]);

get_footer();
?>