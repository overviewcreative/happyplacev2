<?php
/**
 * Dashboard Overview Component
 * Main dashboard content with stats, recent activity, and quick actions
 * 
 * @package HappyPlaceTheme
 */

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
    
    $hot_leads = get_posts([
        'post_type' => 'lead',
        'post_status' => 'publish',
        'author' => $user->ID,
        'meta_query' => [
            [
                'key' => 'lead_status',
                'value' => 'hot'
            ]
        ],
        'posts_per_page' => -1
    ]);
    
} else {
    // Regular user stats
    $saved_listings = get_user_meta($user->ID, 'saved_listings', true);
    $saved_listings = is_array($saved_listings) ? $saved_listings : [];
    
    $saved_searches = get_user_meta($user->ID, 'saved_searches', true);
    $saved_searches = is_array($saved_searches) ? $saved_searches : [];
    
    $recent_views = get_user_meta($user->ID, 'recent_listing_views', true);
    $recent_views = is_array($recent_views) ? array_slice($recent_views, 0, 5) : [];
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

<section class="d-flex flex-column hph-gap-lg hph-p-lg">
    
    <!-- Welcome Section -->
    <header class="d-flex align-items-start justify-content-between hph-gap-md">
        <div class="flex-1">
            <h2 class="hph-text-3xl hph-font-bold hph-text-gray-900 hph-m-0 hph-mb-sm hph-leading-tight">
                <?php 
                printf(
                    __('Welcome back, %s!', 'happy-place-theme'),
                    esc_html($user->display_name)
                );
                ?>
            </h2>
            <p class="hph-text-lg hph-text-gray-600 hph-m-0 hph-leading-relaxed">
                <?php if ($is_agent): ?>
                    <?php _e('Here\'s an overview of your real estate business.', 'happy-place-theme'); ?>
                <?php else: ?>
                    <?php _e('Track your property searches and saved listings.', 'happy-place-theme'); ?>
                <?php endif; ?>
            </p>
        </div>
        
        <!-- Quick Actions -->
        <div class="flex-shrink-0">
            <?php if ($is_agent): ?>
                <?php 
                hph_component('button', [
                    'text' => __('Add Listing', 'happy-place-theme'),
                    'variant' => 'primary',
                    'size' => 'md',
                    'icon' => 'plus',
                    'attributes' => ['id' => 'quickAddListingBtn']
                ]);
                ?>
            <?php else: ?>
                <?php 
                hph_component('button', [
                    'text' => __('Search Properties', 'happy-place-theme'),
                    'variant' => 'primary',
                    'size' => 'md',
                    'icon' => 'search',
                    'href' => home_url('/listings/')
                ]);
                ?>
            <?php endif; ?>
        </div>
    </header>

    <!-- Stats Cards -->
    <div class="hph-grid hph-grid-auto-fit hph-grid-min-240 hph-gap-md">
        
        <?php if ($is_agent): ?>
            <!-- Agent Stats -->
            <?php 
            hph_component('card', [
                'variant' => 'elevated',
                'layout' => 'horizontal',
                'size' => 'md',
                'hover_effect' => 'lift',
                'title' => [
                    'text' => count($active_listings),
                    'tag' => 'div'
                ],
                'description' => __('Active Listings', 'happy-place-theme'),
                'meta_items' => [[
                    'icon' => 'home',
                    'text' => ''
                ]],
                'class' => 'hph-stat-card hph-stat-primary'
            ]);
            ?>
            
            <?php 
            hph_component('card', [
                'variant' => 'elevated',
                'layout' => 'horizontal', 
                'size' => 'md',
                'hover_effect' => 'lift',
                'title' => [
                    'text' => count($sold_listings),
                    'tag' => 'div'
                ],
                'description' => __('Sold This Month', 'happy-place-theme'),
                'meta_items' => [[
                    'icon' => 'check-circle',
                    'text' => ''
                ]],
                'class' => 'hph-stat-card hph-stat-success'
            ]);
            ?>
            
            <?php 
            hph_component('card', [
                'variant' => 'elevated',
                'layout' => 'horizontal',
                'size' => 'md', 
                'hover_effect' => 'lift',
                'title' => [
                    'text' => count($hot_leads),
                    'tag' => 'div'
                ],
                'description' => __('Hot Leads', 'happy-place-theme'),
                'meta_items' => [[
                    'icon' => 'fire',
                    'text' => ''
                ]],
                'class' => 'hph-stat-card hph-stat-warning'
            ]);
            ?>
            
            <?php 
            hph_component('card', [
                'variant' => 'elevated',
                'layout' => 'horizontal',
                'size' => 'md',
                'hover_effect' => 'lift', 
                'title' => [
                    'text' => count($leads_count),
                    'tag' => 'div'
                ],
                'description' => __('Total Leads', 'happy-place-theme'),
                'meta_items' => [[
                    'icon' => 'users',
                    'text' => ''
                ]],
                'class' => 'hph-stat-card hph-stat-info'
            ]);
            ?>
            
        <?php else: ?>
            <!-- User Stats -->
            <?php 
            hph_component('card', [
                'variant' => 'elevated',
                'layout' => 'horizontal',
                'size' => 'md',
                'hover_effect' => 'lift',
                'title' => [
                    'text' => count($saved_listings),
                    'tag' => 'div'
                ],
                'description' => __('Saved Properties', 'happy-place-theme'),
                'meta_items' => [[
                    'icon' => 'heart',
                    'text' => ''
                ]],
                'class' => 'hph-stat-card hph-stat-primary'
            ]);
            ?>
            
            <?php 
            hph_component('card', [
                'variant' => 'elevated',
                'layout' => 'horizontal',
                'size' => 'md',
                'hover_effect' => 'lift',
                'title' => [
                    'text' => count($saved_searches),
                    'tag' => 'div'
                ],
                'description' => __('Saved Searches', 'happy-place-theme'),
                'meta_items' => [[
                    'icon' => 'bookmark',
                    'text' => ''
                ]],
                'class' => 'hph-stat-card hph-stat-info'
            ]);
            ?>
            
            <?php 
            hph_component('card', [
                'variant' => 'elevated', 
                'layout' => 'horizontal',
                'size' => 'md',
                'hover_effect' => 'lift',
                'title' => [
                    'text' => count($recent_views),
                    'tag' => 'div'
                ],
                'description' => __('Recent Views', 'happy-place-theme'),
                'meta_items' => [[
                    'icon' => 'eye',
                    'text' => ''
                ]],
                'class' => 'hph-stat-card hph-stat-success'
            ]);
            ?>
            
            <?php 
            hph_component('card', [
                'variant' => 'elevated',
                'layout' => 'horizontal',
                'size' => 'md',
                'hover_effect' => 'lift',
                'title' => [
                    'text' => '3',
                    'tag' => 'div'
                ],
                'description' => __('New Alerts', 'happy-place-theme'),
                'meta_items' => [[
                    'icon' => 'bell',
                    'text' => ''
                ]],
                'class' => 'hph-stat-card hph-stat-warning'
            ]);
            ?>
        <?php endif; ?>
    </div>

    <!-- Dashboard Content Grid -->
    <div class="hph-grid hph-grid-auto-fit hph-grid-min-350 hph-gap-lg hph-mt-lg">
        
        <!-- Recent Activity -->
        <?php 
        // Prepare recent activity content
        ob_start();
        ?>
        <?php if ($is_agent && !empty($active_listings)): ?>
            <div class="d-flex flex-column hph-gap-sm">
                <?php foreach (array_slice($active_listings, 0, 5) as $listing): ?>
                    <div class="d-flex align-items-center hph-gap-sm hph-p-sm hph-rounded hph-transition-colors hph-hover-bg-gray-50">
                        <div class="hph-w-10 hph-h-10 hph-bg-primary-100 hph-text-primary-600 hph-rounded d-flex align-items-center justify-content-center hph-text-lg flex-shrink-0"><i class="fas fa-home"></i></div>
                        <div class="flex-1 hph-min-w-0">
                            <div class="hph-font-medium hph-text-gray-900 hph-mb-xs hph-truncate"><?php echo esc_html($listing->post_title); ?></div>
                            <div class="hph-text-sm hph-text-gray-600 d-flex align-items-center hph-gap-xs">
                                <?php 
                                $price = get_field('price', $listing->ID);
                                if ($price) {
                                    echo '$' . number_format($price);
                                }
                                ?>
                                <span>•</span>
                                <span><?php echo human_time_diff(strtotime($listing->post_date), current_time('timestamp')) . ' ago'; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!$is_agent && !empty($recent_views)): ?>
            <div class="d-flex flex-column hph-gap-sm">
                <?php foreach ($recent_views as $listing_id): ?>
                    <?php $listing = get_post($listing_id); ?>
                    <?php if ($listing): ?>
                        <div class="d-flex align-items-center hph-gap-sm hph-p-sm hph-rounded hph-transition-colors hph-hover-bg-gray-50">
                            <div class="hph-w-10 hph-h-10 hph-bg-success-light hph-text-success hph-rounded d-flex align-items-center justify-content-center hph-text-lg flex-shrink-0"><i class="fas fa-eye"></i></div>
                            <div class="flex-1 hph-min-w-0">
                                <div class="hph-font-medium hph-text-gray-900 hph-mb-xs hph-truncate"><?php echo esc_html($listing->post_title); ?></div>
                                <div class="hph-text-sm hph-text-gray-600">
                                    <?php 
                                    $price = get_field('price', $listing->ID);
                                    if ($price) {
                                        echo '$' . number_format($price);
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <?php 
            hph_component('empty-state', [
                'title' => $is_agent ? __('No Recent Activity', 'happy-place-theme') : __('No Recent Views', 'happy-place-theme'),
                'description' => $is_agent ? __('No recent listing activity.', 'happy-place-theme') : __('No recently viewed properties.', 'happy-place-theme'),
                'icon' => 'inbox'
            ]);
            ?>
        <?php endif; ?>
        <?php
        $activity_content = ob_get_clean();
        
        // Render recent activity card
        hph_component('card', [
            'variant' => 'default',
            'size' => 'lg',
            'title' => [
                'text' => $is_agent ? __('Recent Listing Activity', 'happy-place-theme') : __('Recently Viewed Properties', 'happy-place-theme'),
                'tag' => 'h3'
            ],
            'actions' => [[
                'text' => __('View All', 'happy-place-theme'),
                'href' => $is_agent ? '?section=listings' : '?section=favorites',
                'variant' => 'outline',
                'size' => 'sm'
            ]],
            'description' => $activity_content
        ]);
        ?>

        <!-- New Listings -->
        <?php 
        // Prepare new listings content
        ob_start();
        if (!empty($recent_listings)): 
        ?>
            <div class="hph-grid hph-grid-auto-fit hph-grid-min-280 hph-gap-md">
                <?php foreach ($recent_listings as $listing): ?>
                    <?php
                    // Create simple listing card for recent listings
                    $listing_title = get_the_title($listing->ID);
                    $price = get_field('price', $listing->ID);
                    $bedrooms = get_field('bedrooms', $listing->ID);
                    $bathrooms = get_field('bathrooms_full', $listing->ID);
                    $featured_image = '';
                    
                    if (has_post_thumbnail($listing->ID)) {
                        $featured_image = get_the_post_thumbnail_url($listing->ID, 'thumbnail');
                    }
                    
                    // Build property details
                    $property_details = [];
                    if ($bedrooms) $property_details[] = $bedrooms . ' bed';
                    if ($bathrooms) $property_details[] = $bathrooms . ' bath';
                    
                    hph_component('card', [
                        'variant' => 'default',
                        'size' => 'sm',
                        'layout' => 'vertical',
                        'image' => [
                            'src' => $featured_image,
                            'alt' => $listing_title,
                            'ratio' => 'landscape'
                        ],
                        'title' => [
                            'text' => $listing_title,
                            'tag' => 'h4',
                            'link' => get_permalink($listing->ID)
                        ],
                        'subtitle' => $price ? '$' . number_format($price) : __('Contact for Price', 'happy-place-theme'),
                        'description' => implode(' • ', $property_details),
                        'class' => 'hph-recent-listing-card'
                    ]);
                    ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <?php 
            hph_component('empty-state', [
                'title' => __('No New Listings', 'happy-place-theme'),
                'description' => __('No new listings available.', 'happy-place-theme'),
                'icon' => 'home'
            ]);
            ?>
        <?php 
        endif;
        $listings_content = ob_get_clean();
        
        // Render new listings card
        hph_component('card', [
            'variant' => 'default',
            'size' => 'lg',
            'title' => [
                'text' => __('New Listings', 'happy-place-theme'),
                'tag' => 'h3'
            ],
            'actions' => [[
                'text' => __('View All', 'happy-place-theme'),
                'href' => home_url('/listings/'),
                'variant' => 'outline',
                'size' => 'sm'
            ]],
            'description' => $listings_content
        ]);
        ?>

        <?php if ($is_agent): ?>
            <!-- Lead Management Widget -->
            <?php 
            // Prepare leads content
            ob_start();
            if (!empty($hot_leads)): 
            ?>
                <div class="d-flex flex-column hph-gap-sm">
                    <?php foreach (array_slice($hot_leads, 0, 3) as $lead): ?>
                        <div class="d-flex align-items-center hph-gap-sm hph-p-sm hph-border hph-border-gray-200 hph-rounded hph-transition-all hph-hover-border-primary hph-hover-shadow-sm">
                            <?php 
                            hph_component('badge', [
                                'text' => 'HOT',
                                'variant' => 'warning',
                                'size' => 'sm'
                            ]);
                            ?>
                            <div class="flex-1 hph-min-w-0">
                                <div class="hph-font-medium hph-text-gray-900 hph-mb-xs"><?php echo esc_html($lead->post_title); ?></div>
                                <div class="hph-text-sm hph-text-gray-600">
                                    <?php 
                                    $phone = get_field('lead_phone', $lead->ID);
                                    $email = get_field('lead_email', $lead->ID);
                                    if ($phone) echo esc_html($phone);
                                    if ($phone && $email) echo ' • ';
                                    if ($email) echo esc_html($email);
                                    ?>
                                </div>
                            </div>
                            <?php 
                            hph_component('button', [
                                'text' => __('Contact', 'happy-place-theme'),
                                'variant' => 'outline',
                                'size' => 'sm'
                            ]);
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?php 
                hph_component('empty-state', [
                    'title' => __('No Active Leads', 'happy-place-theme'),
                    'description' => __('No active leads at this time.', 'happy-place-theme'),
                    'icon' => 'users'
                ]);
                ?>
            <?php 
            endif;
            $leads_content = ob_get_clean();
            
            // Render leads card
            hph_component('card', [
                'variant' => 'default',
                'size' => 'lg',
                'title' => [
                    'text' => __('Recent Leads', 'happy-place-theme'),
                    'tag' => 'h3'
                ],
                'actions' => [[
                    'text' => __('Manage Leads', 'happy-place-theme'),
                    'href' => '?section=leads',
                    'variant' => 'outline',
                    'size' => 'sm'
                ]],
                'description' => $leads_content
            ]);
            ?>
        <?php endif; ?>

        <!-- Quick Actions Widget -->
        <?php 
        // Prepare quick actions content
        ob_start();
        ?>
        <div class="d-flex flex-column hph-gap-sm">
            <?php if ($is_agent): ?>
                <?php 
                hph_component('button', [
                    'text' => __('Add New Listing', 'happy-place-theme'),
                    'variant' => 'primary',
                    'size' => 'md',
                    'icon' => 'plus',
                    'attributes' => ['id' => 'quickAddListing'],
                    'class' => 'hph-full-width'
                ]);
                
                hph_component('button', [
                    'text' => __('Add New Lead', 'happy-place-theme'),
                    'variant' => 'outline',
                    'size' => 'md',
                    'icon' => 'user-plus',
                    'href' => '?section=leads',
                    'class' => 'hph-full-width'
                ]);
                
                hph_component('button', [
                    'text' => __('View Analytics', 'happy-place-theme'),
                    'variant' => 'outline',
                    'size' => 'md',
                    'icon' => 'chart-line',
                    'href' => '?section=analytics',
                    'class' => 'hph-full-width'
                ]);
                ?>
            <?php else: ?>
                <?php 
                hph_component('button', [
                    'text' => __('Search Properties', 'happy-place-theme'),
                    'variant' => 'primary',
                    'size' => 'md',
                    'icon' => 'search',
                    'href' => home_url('/listings/'),
                    'class' => 'hph-full-width'
                ]);
                
                hph_component('button', [
                    'text' => __('Saved Searches', 'happy-place-theme'),
                    'variant' => 'outline',
                    'size' => 'md',
                    'icon' => 'bookmark',
                    'href' => '?section=searches',
                    'class' => 'hph-full-width'
                ]);
                
                hph_component('button', [
                    'text' => __('View Favorites', 'happy-place-theme'),
                    'variant' => 'outline',
                    'size' => 'md',
                    'icon' => 'heart',
                    'href' => '?section=favorites',
                    'class' => 'hph-full-width'
                ]);
                ?>
            <?php endif; ?>
        </div>
        <?php
        $actions_content = ob_get_clean();
        
        // Render quick actions card
        hph_component('card', [
            'variant' => 'default',
            'size' => 'lg',
            'title' => [
                'text' => __('Quick Actions', 'happy-place-theme'),
                'tag' => 'h3'
            ],
            'description' => $actions_content
        ]);
        ?>
    </div>
</section>
