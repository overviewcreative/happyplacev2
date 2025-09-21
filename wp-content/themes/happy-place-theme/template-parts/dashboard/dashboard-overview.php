<?php
/**
 * Dashboard Overview Component - UNIFIED SYSTEM
 * Uses ONLY main theme components for perfect consistency
 * 
 * @package HappyPlaceTheme
 * @version UNIFIED-1.0.0
 */

// Include unified components
require_once get_template_directory() . '/template-parts/dashboard/unified-components.php';

$user = $args['user'] ?? wp_get_current_user();
$is_agent = $args['is_agent'] ?? false;

// Get dashboard stats based on user type
if ($is_agent) {
    $active_listings = get_posts([
        'post_type' => 'listing',
        'post_status' => 'publish',
        'author' => $user->ID,
        'meta_query' => [
            [
                'key' => 'listing_status',
                'value' => 'active'
            ]
        ],
        'posts_per_page' => -1
    ]);
    
    $sold_listings = get_posts([
        'post_type' => 'listing',
        'post_status' => 'publish', 
        'author' => $user->ID,
        'meta_query' => [
            [
                'key' => 'listing_status',
                'value' => 'sold'
            ]
        ],
        'posts_per_page' => -1,
        'date_query' => [
            [
                'after' => '1 month ago'
            ]
        ]
    ]);
    
    $leads_count = get_posts([
        'post_type' => 'lead',
        'post_status' => 'publish',
        'author' => $user->ID,
        'posts_per_page' => -1
    ]);
    
    // Prepare stats data
    $stats_data = [
        'total_listings' => count($active_listings) + count($sold_listings),
        'active_listings' => count($active_listings), 
        'pending_listings' => 0, // Will be populated via AJAX
        'total_leads' => count($leads_count)
    ];
    
} else {
    // Regular user stats
    $saved_listings = get_user_meta($user->ID, 'saved_listings', true);
    $saved_listings = is_array($saved_listings) ? $saved_listings : [];
    
    $saved_searches = get_user_meta($user->ID, 'saved_searches', true);
    $saved_searches = is_array($saved_searches) ? $saved_searches : [];
    
    $recent_views = get_user_meta($user->ID, 'recent_listing_views', true);
    $recent_views = is_array($recent_views) ? array_slice($recent_views, 0, 5) : [];
    
    $stats_data = [
        'saved_properties' => count($saved_listings),
        'saved_searches' => count($saved_searches), 
        'recent_views' => count($recent_views),
        'new_matches' => 3 // Example - would come from real data
    ];
}

// Get recent activity
$recent_listings = get_posts([
    'post_type' => 'listing',
    'post_status' => 'publish',
    'posts_per_page' => 5,
    'meta_key' => 'listing_status',
    'meta_value' => 'active'
]);
?>

<div class="dashboard-content unified-system-active">
    
    <!-- Welcome Section -->
    <header class="hph-section-header">
        <div class="hph-flex hph-justify-between hph-items-start hph-gap-lg">
            <div class="hph-flex-1">
                <h2 class="hph-section-title">
                    <i class="fas fa-home"></i>
                    <?php 
                    printf(
                        __('Welcome back, %s!', 'happy-place-theme'),
                        esc_html($user->display_name)
                    );
                    ?>
                </h2>
                <p class="hph-section-description">
                    <?php if ($is_agent): ?>
                        <?php _e('Here\'s an overview of your real estate business.', 'happy-place-theme'); ?>
                    <?php else: ?>
                        <?php _e('Track your property searches and saved listings.', 'happy-place-theme'); ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- Quick Actions -->
            <div class="hph-flex-shrink-0 hph-hidden lg:hph-block">
                <?php if ($is_agent): ?>
                    <button id="overview-quickAddListingBtn-header" class="hph-btn hph-btn-primary">
                        <i class="fas fa-plus"></i>
                        <?php _e('Add Listing', 'happy-place-theme'); ?>
                    </button>
                <?php else: ?>
                    <a href="<?php echo home_url('/listings/'); ?>" class="hph-btn hph-btn-primary">
                        <i class="fas fa-search"></i>
                        <?php _e('Search Properties', 'happy-place-theme'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Stats Section - Uses Unified Components -->
    <?php HPH_Unified_Dashboard_Components::stats_overview($stats_data); ?>

    <!-- Main Content Grid -->
    <div class="hph-dashboard-grid">
        
        <!-- Recent Activity Section -->
        <div class="hph-card">
            <div class="hph-card__header">
                <h3 class="hph-card__title">
                    <?php echo $is_agent ? __('Recent Listings', 'happy-place-theme') : __('Recently Viewed', 'happy-place-theme'); ?>
                </h3>
                <a href="<?php echo $is_agent ? '?section=listings' : '?section=favorites'; ?>" class="hph-btn hph-btn-link hph-btn-sm">
                    <?php _e('View All', 'happy-place-theme'); ?>
                </a>
            </div>
            
            <div class="hph-card__content">
                <div class="hph-dashboard-list" id="recentActivityContent">
                    <?php if (!empty($recent_listings)): ?>
                        <?php foreach (array_slice($recent_listings, 0, 3) as $listing): ?>
                            <?php 
                            $listing_data = [
                                'id' => $listing->ID,
                                'title' => $listing->post_title,
                                'excerpt' => wp_trim_words($listing->post_excerpt, 15),
                                'price' => get_post_meta($listing->ID, 'listing_price', true),
                                'location' => get_post_meta($listing->ID, 'listing_location', true),
                                'status' => get_post_meta($listing->ID, 'listing_status', true),
                                'featured_image' => get_the_post_thumbnail_url($listing->ID, 'thumbnail'),
                                'permalink' => get_permalink($listing->ID)
                            ];
                            HPH_Unified_Dashboard_Components::listing_card($listing_data); 
                            ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="hph-text-center hph-py-lg">
                            <i class="fas fa-home hph-text-4xl hph-text-gray-300 hph-mb-md"></i>
                            <p class="hph-text-secondary">
                                <?php echo $is_agent ? __('No listings yet. Create your first listing!', 'happy-place-theme') : __('Start exploring properties to see them here.', 'happy-place-theme'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="hph-card">
            <div class="hph-card__header">
                <h3 class="hph-card__title">
                    <?php _e('Quick Actions', 'happy-place-theme'); ?>
                </h3>
            </div>
            
            <div class="hph-card__content">
                <div class="hph-dashboard-list">
                    <?php if ($is_agent): ?>
                        <a href="?section=create-listing" class="hph-btn hph-btn-secondary w-full">
                            <i class="fas fa-plus"></i>
                            <?php _e('Create New Listing', 'happy-place-theme'); ?>
                        </a>
                        <a href="?section=leads" class="hph-btn hph-btn-secondary w-full">
                            <i class="fas fa-users"></i>
                            <?php _e('Manage Leads', 'happy-place-theme'); ?>
                        </a>
                        <a href="?section=analytics" class="hph-btn hph-btn-secondary w-full">
                            <i class="fas fa-chart-bar"></i>
                            <?php _e('View Analytics', 'happy-place-theme'); ?>
                        </a>
                    <?php else: ?>
                        <a href="/listings/" class="hph-btn hph-btn-secondary w-full">
                            <i class="fas fa-search"></i>
                            <?php _e('Search Properties', 'happy-place-theme'); ?>
                        </a>
                        <a href="?section=favorites" class="hph-btn hph-btn-secondary w-full">
                            <i class="fas fa-heart"></i>
                            <?php _e('Saved Properties', 'happy-place-theme'); ?>
                        </a>
                        <a href="?section=searches" class="hph-btn hph-btn-secondary w-full">
                            <i class="fas fa-bookmark"></i>
                            <?php _e('Saved Searches', 'happy-place-theme'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Market Trends or Recent Matches -->
        <div class="hph-card">
            <div class="hph-card__header">
                <h3 class="hph-card__title">
                    <?php echo $is_agent ? __('Market Insights', 'happy-place-theme') : __('New Matches', 'happy-place-theme'); ?>
                </h3>
            </div>
            
            <div class="hph-card__content">
                <?php if ($is_agent): ?>
                    <div class="hph-text-center hph-py-lg">
                        <i class="fas fa-chart-line hph-text-4xl hph-text-primary hph-mb-md"></i>
                        <p class="hph-text-secondary hph-mb-md">
                            <?php _e('Market data will be available soon', 'happy-place-theme'); ?>
                        </p>
                        <button class="hph-btn hph-btn-primary hph-btn-sm">
                            <?php _e('Learn More', 'happy-place-theme'); ?>
                        </button>
                    </div>
                <?php else: ?>
                    <div class="hph-text-center hph-py-lg">
                        <i class="fas fa-bell hph-text-4xl hph-text-secondary hph-mb-md"></i>
                        <p class="hph-text-secondary hph-mb-md">
                            <?php _e('We\'ll notify you when new properties match your searches', 'happy-place-theme'); ?>
                        </p>
                        <a href="?section=searches" class="hph-btn hph-btn-primary hph-btn-sm">
                            <?php _e('Manage Alerts', 'happy-place-theme'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
                        <div class="hph-loading-spinner">
                            <i class="fas fa-spinner fa-spin hph-text-2xl hph-text-gray-400"></i>
                            <p class="hph-text-gray-500 hph-mt-2"><?php _e('Loading recent activity...', 'happy-place-theme'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- New Listings Card -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title"><?php _e('New Listings', 'happy-place-theme'); ?></h3>
                    <a href="<?php echo home_url('/listings/'); ?>" class="hph-text-sm hph-text-primary hover:hph-underline">
                        <?php _e('View All', 'happy-place-theme'); ?>
                    </a>
                </div>
                
                <?php if (!empty($recent_listings)): ?>
                    <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-2 hph-gap-md">
                        <?php foreach ($recent_listings as $listing): ?>
                            <?php
                            $listing_title = get_the_title($listing->ID);
                            $price = get_field('price', $listing->ID);
                            $bedrooms = get_field('bedrooms', $listing->ID);
                            $bathrooms = get_field('bathrooms_full', $listing->ID);
                            $featured_image = has_post_thumbnail($listing->ID) ? get_the_post_thumbnail_url($listing->ID, 'thumbnail') : '';
                            ?>
                            <a href="<?php echo get_permalink($listing->ID); ?>" class="hph-block hph-p-3 hph-bg-gray-50 hph-rounded-lg hover:hph-bg-gray-100 hph-transition hph-no-underline">
                                <?php if ($featured_image): ?>
                                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($listing_title); ?>" class="hph-w-full hph-h-32 hph-object-cover hph-rounded-lg hph-mb-3">
                                <?php endif; ?>
                                <h4 class="hph-font-medium hph-text-gray-900 hph-mb-1"><?php echo esc_html($listing_title); ?></h4>
                                <div class="hph-text-lg hph-font-semibold hph-text-primary hph-mb-2">
                                    <?php echo $price ? '$' . number_format($price) : __('Contact for Price', 'happy-place-theme'); ?>
                                </div>
                                <div class="hph-text-sm hph-text-gray-600">
                                    <?php if ($bedrooms): ?><?php echo $bedrooms; ?> bed<?php endif; ?>
                                    <?php if ($bedrooms && $bathrooms): ?> • <?php endif; ?>
                                    <?php if ($bathrooms): ?><?php echo $bathrooms; ?> bath<?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="empty-state-title"><?php _e('No New Listings', 'happy-place-theme'); ?></div>
                        <div class="empty-state-description"><?php _e('No new listings available.', 'happy-place-theme'); ?></div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($is_agent): ?>
                <!-- Hot Leads Widget - AJAX Powered -->
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title"><?php _e('Hot Leads', 'happy-place-theme'); ?></h3>
                        <a href="?section=leads" class="hph-text-sm hph-text-primary hover:hph-underline">
                            <?php _e('Manage Leads', 'happy-place-theme'); ?>
                        </a>
                    </div>
                    
                    <!-- Hot leads content will be populated via AJAX -->
                    <div id="hotLeadsContent">
                        <div class="hph-text-center hph-py-8">
                            <div class="hph-loading-spinner">
                                <i class="fas fa-spinner fa-spin hph-text-2xl hph-text-gray-400"></i>
                                <p class="hph-text-gray-500 hph-mt-2"><?php _e('Loading hot leads...', 'happy-place-theme'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column - Takes 1 column on large screens -->
        <div class="lg:hph-col-span-1 hph-flex hph-flex-col hph-gap-lg">
            
            <!-- Quick Actions Widget -->
            <div class="dashboard-card">
                <h3 class="dashboard-card-title hph-mb-4"><?php _e('Quick Actions', 'happy-place-theme'); ?></h3>
                
                <div class="hph-flex hph-flex-col hph-gap-sm">
                    <?php if ($is_agent): ?>
                        <button id="overview-quickAddListingBtn-actions" class="hph-btn hph-btn-primary hph-btn-md hph-w-full">
                            <i class="fas fa-plus hph-mr-2"></i>
                            <?php _e('Add New Listing', 'happy-place-theme'); ?>
                        </button>
                        
                        <a href="?section=leads" class="hph-btn hph-btn-outline hph-btn-md hph-w-full">
                            <i class="fas fa-user-plus hph-mr-2"></i>
                            <?php _e('Add New Lead', 'happy-place-theme'); ?>
                        </a>
                        
                        <a href="?section=analytics" class="hph-btn hph-btn-outline hph-btn-md hph-w-full">
                            <i class="fas fa-chart-line hph-mr-2"></i>
                            <?php _e('View Analytics', 'happy-place-theme'); ?>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo home_url('/listings/'); ?>" class="hph-btn hph-btn-primary hph-btn-md hph-w-full">
                            <i class="fas fa-search hph-mr-2"></i>
                            <?php _e('Search Properties', 'happy-place-theme'); ?>
                        </a>
                        
                        <a href="?section=searches" class="hph-btn hph-btn-outline hph-btn-md hph-w-full">
                            <i class="fas fa-bookmark hph-mr-2"></i>
                            <?php _e('Saved Searches', 'happy-place-theme'); ?>
                        </a>
                        
                        <a href="?section=favorites" class="hph-btn hph-btn-outline hph-btn-md hph-w-full">
                            <i class="fas fa-heart hph-mr-2"></i>
                            <?php _e('View Favorites', 'happy-place-theme'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Market Chart (Placeholder) -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">
                        <?php echo $is_agent ? __('Performance', 'happy-place-theme') : __('Market Trends', 'happy-place-theme'); ?>
                    </h3>
                    <select class="hph-form-select hph-w-auto">
                        <option><?php _e('Last 30 days', 'happy-place-theme'); ?></option>
                        <option><?php _e('Last 3 months', 'happy-place-theme'); ?></option>
                        <option><?php _e('Last year', 'happy-place-theme'); ?></option>
                    </select>
                </div>
                <div class="hph-h-64 hph-flex hph-items-center hph-justify-center hph-bg-gray-50 hph-rounded-lg">
                    <span class="hph-text-gray-400"><?php _e('Chart placeholder', 'happy-place-theme'); ?></span>
                </div>
            </div>
            
            <?php if ($is_agent): ?>
                <!-- Upcoming Events - AJAX Powered -->
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title"><?php _e('Upcoming Events', 'happy-place-theme'); ?></h3>
                        <a href="?section=open-houses" class="hph-text-sm hph-text-primary hover:hph-underline">
                            <?php _e('View All', 'happy-place-theme'); ?>
                        </a>
                    </div>
                    
                    <!-- Events content will be populated via AJAX -->
                    <div id="upcomingEventsContent">
                        <div class="hph-text-center hph-py-8">
                            <div class="hph-loading-spinner">
                                <i class="fas fa-spinner fa-spin hph-text-2xl hph-text-gray-400"></i>
                                <p class="hph-text-gray-500 hph-mt-2"><?php _e('Loading upcoming events...', 'happy-place-theme'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div> <!-- End dashboard-content -->
