<?php
/**
 * Saved Listings Section
 * User dashboard section for managing saved/favorite properties
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!is_user_logged_in()) {
    return;
}

$current_user = wp_get_current_user();

// Get user's saved listings
$saved_listings = get_user_meta($current_user->ID, 'saved_listings', true);
$saved_listings = is_array($saved_listings) ? $saved_listings : [];

// Get recent viewed listings
$recent_views = get_user_meta($current_user->ID, 'recent_listing_views', true);
$recent_views = is_array($recent_views) ? array_slice($recent_views, 0, 10) : [];

// Query saved listings
$saved_query = null;
if (!empty($saved_listings)) {
    $saved_query = new WP_Query([
        'post_type' => 'listing',
        'post_status' => 'publish',
        'post__in' => $saved_listings,
        'posts_per_page' => 20,
        'orderby' => 'post__in'
    ]);
}

// Query recent views
$recent_query = null;
if (!empty($recent_views)) {
    $recent_query = new WP_Query([
        'post_type' => 'listing',
        'post_status' => 'publish',
        'post__in' => $recent_views,
        'posts_per_page' => 10,
        'orderby' => 'post__in'
    ]);
}

// Current view
$current_view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'saved';
?>

<div style="
    display: flex;
    flex-direction: column;
    gap: var(--hph-gap-6);
    padding: var(--hph-padding-6);
">
    
    <!-- Section Header -->
    <header style="
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: var(--hph-gap-4);
        flex-wrap: wrap;
    ">
        <div style="flex: 1; min-width: 300px;">
            <h2 style="
                font-size: var(--hph-text-3xl);
                font-weight: var(--hph-font-bold);
                color: var(--hph-gray-900);
                margin: 0 0 var(--hph-margin-2) 0;
                line-height: var(--hph-leading-tight);
            "><?php _e('My Saved Properties', 'happy-place-theme'); ?></h2>
            <p style="
                font-size: var(--hph-text-lg);
                color: var(--hph-gray-600);
                margin: 0;
                line-height: var(--hph-leading-relaxed);
            ">
                <?php _e('Keep track of properties you\'re interested in and recently viewed listings.', 'happy-place-theme'); ?>
            </p>
        </div>
        
        <div style="flex-shrink: 0;">
            <?php 
            hph_component('button', [
                'text' => __('Search Properties', 'happy-place-theme'),
                'variant' => 'primary',
                'size' => 'md',
                'icon' => 'search',
                'href' => home_url('/listings/')
            ]);
            ?>
        </div>
    </header>

    <!-- View Toggle Tabs -->
    <div style="
        display: flex;
        background: var(--hph-white);
        border: 1px solid var(--hph-gray-200);
        border-radius: var(--hph-border-radius-lg);
        padding: var(--hph-padding-1);
        gap: var(--hph-gap-1);
        margin-bottom: var(--hph-margin-6);
    ">
        <a href="?section=listings&view=saved" style="
            display: flex;
            align-items: center;
            gap: var(--hph-gap-2);
            padding: var(--hph-padding-3) var(--hph-padding-4);
            border-radius: var(--hph-border-radius);
            font-size: var(--hph-text-sm);
            font-weight: var(--hph-font-medium);
            text-decoration: none;
            transition: all 0.2s ease;
            <?php echo $current_view === 'saved' ? 'background: var(--hph-primary); color: var(--hph-white);' : 'color: var(--hph-gray-600);'; ?>
        ">
            <i class="fas fa-heart"></i>
            <span><?php _e('Saved Properties', 'happy-place-theme'); ?></span>
            <?php 
            hph_component('badge', [
                'text' => count($saved_listings),
                'variant' => $current_view === 'saved' ? 'light' : 'default',
                'size' => 'sm'
            ]);
            ?>
        </a>
        
        <a href="?section=listings&view=recent" style="
            display: flex;
            align-items: center;
            gap: var(--hph-gap-2);
            padding: var(--hph-padding-3) var(--hph-padding-4);
            border-radius: var(--hph-border-radius);
            font-size: var(--hph-text-sm);
            font-weight: var(--hph-font-medium);
            text-decoration: none;
            transition: all 0.2s ease;
            <?php echo $current_view === 'recent' ? 'background: var(--hph-info); color: var(--hph-white);' : 'color: var(--hph-gray-600);'; ?>
        ">
            <i class="fas fa-clock"></i>
            <span><?php _e('Recently Viewed', 'happy-place-theme'); ?></span>
            <?php 
            hph_component('badge', [
                'text' => count($recent_views),
                'variant' => $current_view === 'recent' ? 'light' : 'info',
                'size' => 'sm'
            ]);
            ?>
        </a>
    </div>
    
    <!-- Search and Controls -->
    <div style="
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: var(--hph-gap-4);
        flex-wrap: wrap;
        margin-bottom: var(--hph-margin-6);
    ">
        <div style="position: relative; max-width: 300px;">
            <input type="search" 
                   id="savedListingsSearch"
                   placeholder="<?php _e('Search saved properties...', 'happy-place-theme'); ?>" 
                   style="
                       width: 100%;
                       padding: var(--hph-padding-3) var(--hph-padding-4) var(--hph-padding-3) var(--hph-padding-10);
                       border: 1px solid var(--hph-gray-300);
                       border-radius: var(--hph-border-radius);
                       font-size: var(--hph-text-sm);
                       background: var(--hph-white);
                   ">
            <i class="fas fa-search" style="
                position: absolute;
                left: var(--hph-padding-3);
                top: 50%;
                transform: translateY(-50%);
                color: var(--hph-gray-400);
                font-size: var(--hph-text-sm);
            "></i>
        </div>
        
        <div style="
            display: flex;
            gap: var(--hph-gap-2);
            background: var(--hph-gray-100);
            padding: var(--hph-padding-1);
            border-radius: var(--hph-border-radius);
        ">
            <?php 
            hph_component('button', [
                'text' => '',
                'variant' => 'ghost',
                'size' => 'sm',
                'icon' => 'th',
                'attributes' => [
                    'data-view' => 'grid',
                    'title' => __('Grid View', 'happy-place-theme'),
                    'class' => 'hph-view-toggle active'
                ]
            ]);
            
            hph_component('button', [
                'text' => '',
                'variant' => 'ghost',
                'size' => 'sm',
                'icon' => 'list',
                'attributes' => [
                    'data-view' => 'list',
                    'title' => __('List View', 'happy-place-theme'),
                    'class' => 'hph-view-toggle'
                ]
            ]);
            ?>
        </div>
    </div>

    <!-- Content Area -->
    <div id="savedListingsContainer">
        
        <?php if ($current_view === 'saved'): ?>
            <!-- Saved Properties -->
            <?php if ($saved_query && $saved_query->have_posts()): ?>
                <div style="
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                    gap: var(--hph-gap-6);
                " id="savedListingsGrid">
                    <?php while ($saved_query->have_posts()): $saved_query->the_post(); ?>
                        <?php
                        $listing_id = get_the_ID();
                        $date_saved = get_user_meta($current_user->ID, "listing_saved_date_{$listing_id}", true);
                        
                        // Create custom content for saved properties
                        ob_start();
                        ?>
                        <div style="
                            display: flex;
                            align-items: center;
                            gap: var(--hph-gap-2);
                            margin-top: var(--hph-margin-3);
                            padding: var(--hph-padding-2);
                            background: var(--hph-success-light);
                            border-radius: var(--hph-border-radius);
                            font-size: var(--hph-text-sm);
                            color: var(--hph-success);
                        ">
                            <i class="fas fa-heart"></i>
                            <span>
                                <?php 
                                if ($date_saved) {
                                    printf(__('Saved %s', 'happy-place-theme'), human_time_diff($date_saved, current_time('timestamp')) . ' ago');
                                } else {
                                    _e('Saved property', 'happy-place-theme');
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div style="
                            display: flex;
                            gap: var(--hph-gap-2);
                            margin-top: var(--hph-margin-4);
                        ">
                            <?php 
                            hph_component('button', [
                                'text' => __('View Details', 'happy-place-theme'),
                                'variant' => 'primary',
                                'size' => 'sm',
                                'href' => get_permalink($listing_id)
                            ]);
                            
                            hph_component('button', [
                                'text' => __('Contact', 'happy-place-theme'),
                                'variant' => 'outline',
                                'size' => 'sm',
                                'icon' => 'envelope',
                                'attributes' => [
                                    'data-action' => 'contact-agent',
                                    'data-listing-id' => $listing_id
                                ]
                            ]);
                            ?>
                        </div>
                        <?php
                        $saved_content = ob_get_clean();
                        
                        // Create saved listing card using HPH card component directly
                        $listing_title = get_the_title($listing_id);
                        $price = get_field('price', $listing_id);
                        $bedrooms = get_field('bedrooms', $listing_id);
                        $bathrooms = get_field('bathrooms_full', $listing_id);
                        $square_feet = get_field('square_feet', $listing_id);
                        $featured_image = '';
                        
                        if (has_post_thumbnail($listing_id)) {
                            $featured_image = get_the_post_thumbnail_url($listing_id, 'medium');
                        }
                        
                        // Build property details
                        $property_details = [];
                        if ($bedrooms) $property_details[] = $bedrooms . ' bed';
                        if ($bathrooms) $property_details[] = $bathrooms . ' bath';
                        if ($square_feet) $property_details[] = number_format($square_feet) . ' sqft';
                        
                        hph_component('card', [
                            'variant' => 'elevated',
                            'size' => 'lg',
                            'hover_effect' => 'lift',
                            'image' => [
                                'src' => $featured_image,
                                'alt' => $listing_title,
                                'ratio' => 'landscape'
                            ],
                            'title' => [
                                'text' => $listing_title,
                                'tag' => 'h3',
                                'link' => get_permalink($listing_id)
                            ],
                            'subtitle' => $price ? '$' . number_format($price) : __('Contact for Price', 'happy-place-theme'),
                            'description' => implode(' • ', $property_details) . $saved_content,
                            'badges' => [[
                                'text' => 'Saved',
                                'variant' => 'success',
                                'icon' => 'heart'
                            ]],
                            'attributes' => ['data-listing-id' => $listing_id],
                            'class' => 'hph-saved-listing-card'
                        ]);
                        ?>
                        
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
                
            <?php else: ?>
                <!-- Empty Saved State -->
                <?php 
                hph_component('empty-state', [
                    'title' => __('No Saved Properties', 'happy-place-theme'),
                    'description' => __('Start saving properties you\'re interested in to keep track of them here. Click the heart icon on any property to save it.', 'happy-place-theme'),
                    'icon' => 'heart',
                    'action' => [
                        'text' => __('Browse Properties', 'happy-place-theme'),
                        'variant' => 'primary',
                        'icon' => 'search',
                        'href' => home_url('/listings/')
                    ]
                ]);
                ?>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Recently Viewed -->
            <?php if ($recent_query && $recent_query->have_posts()): ?>
                <div style="
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                    gap: var(--hph-gap-6);
                " id="recentListingsGrid">
                    <?php while ($recent_query->have_posts()): $recent_query->the_post(); ?>
                        <?php
                        $listing_id = get_the_ID();
                        $is_saved = in_array($listing_id, $saved_listings);
                        
                        // Create custom content for recent views
                        ob_start();
                        ?>
                        <div style="
                            display: flex;
                            align-items: center;
                            gap: var(--hph-gap-2);
                            margin-top: var(--hph-margin-3);
                            padding: var(--hph-padding-2);
                            background: var(--hph-info-light);
                            border-radius: var(--hph-border-radius);
                            font-size: var(--hph-text-sm);
                            color: var(--hph-info);
                        ">
                            <i class="fas fa-clock"></i>
                            <span><?php _e('Recently viewed', 'happy-place-theme'); ?></span>
                        </div>
                        
                        <div style="
                            display: flex;
                            gap: var(--hph-gap-2);
                            margin-top: var(--hph-margin-4);
                        ">
                            <?php 
                            hph_component('button', [
                                'text' => __('View Again', 'happy-place-theme'),
                                'variant' => 'primary',
                                'size' => 'sm',
                                'href' => get_permalink($listing_id)
                            ]);
                            
                            hph_component('button', [
                                'text' => __('Contact', 'happy-place-theme'),
                                'variant' => 'outline',
                                'size' => 'sm',
                                'icon' => 'envelope',
                                'attributes' => [
                                    'data-action' => 'contact-agent',
                                    'data-listing-id' => $listing_id
                                ]
                            ]);
                            ?>
                        </div>
                        <?php
                        $recent_content = ob_get_clean();
                        
                        // Create recent listing card using HPH card component directly
                        $listing_title = get_the_title($listing_id);
                        $price = get_field('price', $listing_id);
                        $bedrooms = get_field('bedrooms', $listing_id);
                        $bathrooms = get_field('bathrooms_full', $listing_id);
                        $square_feet = get_field('square_feet', $listing_id);
                        $featured_image = '';
                        
                        if (has_post_thumbnail($listing_id)) {
                            $featured_image = get_the_post_thumbnail_url($listing_id, 'medium');
                        }
                        
                        // Build property details
                        $property_details = [];
                        if ($bedrooms) $property_details[] = $bedrooms . ' bed';
                        if ($bathrooms) $property_details[] = $bathrooms . ' bath';
                        if ($square_feet) $property_details[] = number_format($square_feet) . ' sqft';
                        
                        hph_component('card', [
                            'variant' => 'default',
                            'size' => 'lg',
                            'hover_effect' => 'lift',
                            'image' => [
                                'src' => $featured_image,
                                'alt' => $listing_title,
                                'ratio' => 'landscape'
                            ],
                            'title' => [
                                'text' => $listing_title,
                                'tag' => 'h3',
                                'link' => get_permalink($listing_id)
                            ],
                            'subtitle' => $price ? '$' . number_format($price) : __('Contact for Price', 'happy-place-theme'),
                            'description' => implode(' • ', $property_details) . $recent_content,
                            'badges' => $is_saved ? [[
                                'text' => 'Saved',
                                'variant' => 'success',
                                'icon' => 'heart'
                            ]] : [],
                            'attributes' => ['data-listing-id' => $listing_id],
                            'class' => 'hph-recent-listing-card'
                        ]);
                        ?>
                        
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
                
            <?php else: ?>
                <!-- Empty Recent State -->
                <?php 
                hph_component('empty-state', [
                    'title' => __('No Recent Views', 'happy-place-theme'),
                    'description' => __('Properties you view will appear here for easy access later.', 'happy-place-theme'),
                    'icon' => 'clock',
                    'action' => [
                        'text' => __('Browse Properties', 'happy-place-theme'),
                        'variant' => 'primary',
                        'icon' => 'search',
                        'href' => home_url('/listings/')
                    ]
                ]);
                ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>