<?php
/**
 * Listings Management Section
 * Agent dashboard section for managing property listings
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!is_user_logged_in()) {
    return;
}

$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
$is_agent = in_array('agent', $user_roles) || in_array('administrator', $user_roles);

if (!$is_agent) {
    return;
}

// Get agent's listings
$listings_query = new WP_Query([
    'post_type' => 'listing',
    'post_status' => 'publish',
    'author' => $current_user->ID,
    'posts_per_page' => 20,
    'meta_key' => '_edit_last',
    'orderby' => 'meta_value_num date',
    'order' => 'DESC'
]);

// Get listing status filter
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

// Filter by status if specified
if ($status_filter !== 'all') {
    $listings_query = new WP_Query([
        'post_type' => 'listing',
        'post_status' => 'publish',
        'author' => $current_user->ID,
        'posts_per_page' => 20,
        'meta_query' => [
            [
                'key' => 'listing_status',
                'value' => $status_filter
            ]
        ],
        'meta_key' => '_edit_last',
        'orderby' => 'meta_value_num date',
        'order' => 'DESC'
    ]);
}

// Get status counts
$status_counts = [];
$statuses = ['active', 'pending', 'sold', 'draft'];
foreach ($statuses as $status) {
    $count_query = new WP_Query([
        'post_type' => 'listing',
        'post_status' => 'publish',
        'author' => $current_user->ID,
        'meta_query' => [
            [
                'key' => 'listing_status',
                'value' => $status
            ]
        ],
        'posts_per_page' => -1
    ]);
    $status_counts[$status] = $count_query->found_posts;
}
?>

<div class="d-flex flex-column hph-gap-lg hph-p-lg">
    
    <!-- Section Header -->
    <header class="d-flex align-items-start justify-content-between hph-gap-md flex-wrap">
        <div class="flex-1" style="min-width: 300px;">
            <h2 class="hph-text-3xl hph-font-bold hph-text-gray-900 hph-m-0 hph-mb-sm hph-leading-tight"><?php _e('My Listings', 'happy-place-theme'); ?></h2>
            <p class="hph-text-lg hph-text-gray-600 hph-m-0 hph-leading-relaxed">
                <?php _e('Manage your property listings, track performance, and update details.', 'happy-place-theme'); ?>
            </p>
        </div>
        
        <div class="d-flex hph-gap-sm flex-shrink-0">
            <?php 
            hph_component('button', [
                'text' => __('Add New Listing', 'happy-place-theme'),
                'variant' => 'primary',
                'size' => 'md',
                'icon' => 'plus',
                'attributes' => ['id' => 'addNewListingBtn']
            ]);
            
            hph_component('button', [
                'text' => __('Import Listings', 'happy-place-theme'),
                'variant' => 'outline',
                'size' => 'md',
                'icon' => 'upload',
                'attributes' => ['id' => 'importListingsBtn']
            ]);
            ?>
        </div>
    </header>

    <!-- Status Filter Tabs -->
    <div class="d-flex hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-xs hph-gap-xs hph-mb-lg">
        <a href="?section=listings&status=all" class="hph-filter-tab<?php echo $status_filter === 'all' ? ' active' : ''; ?>">
            <span><?php _e('All Listings', 'happy-place-theme'); ?></span>
            <?php 
            hph_component('badge', [
                'text' => $listings_query->found_posts,
                'variant' => $status_filter === 'all' ? 'light' : 'default',
                'size' => 'sm'
            ]);
            ?>
        </a>
        
        <a href="?section=listings&status=active" class="hph-filter-tab<?php echo $status_filter === 'active' ? ' active-success' : ''; ?>">
            <span><?php _e('Active', 'happy-place-theme'); ?></span>
            <?php 
            hph_component('badge', [
                'text' => $status_counts['active'],
                'variant' => 'success',
                'size' => 'sm'
            ]);
            ?>
        </a>
        
        <a href="?section=listings&status=pending" class="hph-filter-tab<?php echo $status_filter === 'pending' ? ' active-warning' : ''; ?>">
            <span><?php _e('Pending', 'happy-place-theme'); ?></span>
            <?php 
            hph_component('badge', [
                'text' => $status_counts['pending'],
                'variant' => 'warning',
                'size' => 'sm'
            ]);
            ?>
        </a>
        
        <a href="?section=listings&status=sold" class="hph-filter-tab<?php echo $status_filter === 'sold' ? ' active' : ''; ?>">
            <span><?php _e('Sold', 'happy-place-theme'); ?></span>
            <?php 
            hph_component('badge', [
                'text' => $status_counts['sold'],
                'variant' => 'info',
                'size' => 'sm'
            ]);
            ?>
        </a>
        
        <a href="?section=listings&status=draft" class="hph-filter-tab<?php echo $status_filter === 'draft' ? ' active' : ''; ?>">
            <span><?php _e('Draft', 'happy-place-theme'); ?></span>
            <?php 
            hph_component('badge', [
                'text' => $status_counts['draft'],
                'variant' => 'default',
                'size' => 'sm'
            ]);
            ?>
        </a>
    </div>
    
    <!-- Filter Controls -->
    <div class="d-flex justify-content-between align-items-center hph-gap-md flex-wrap hph-mb-lg">
        <div style="position: relative; max-width: 300px;">
            <input type="search" 
                   id="listingsSearch"
                   placeholder="<?php _e('Search listings...', 'happy-place-theme'); ?>" 
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

    <!-- Listings Grid -->
    <div id="listingsContainer">
        <?php if ($listings_query->have_posts()): ?>
            <div style="
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: var(--hph-gap-6);
            " id="listingsGrid">
                <?php while ($listings_query->have_posts()): $listings_query->the_post(); ?>
                    <?php
                    $listing_id = get_the_ID();
                    $listing_status = get_field('listing_status', $listing_id) ?: 'draft';
                    $views = get_post_meta($listing_id, '_listing_views', true) ?: 0;
                    $leads = get_posts([
                        'post_type' => 'lead',
                        'meta_query' => [[
                            'key' => 'interested_listing',
                            'value' => $listing_id
                        ]],
                        'posts_per_page' => -1
                    ]);
                    
                    // Prepare custom card content with management features
                    ob_start();
                    ?>
                    <div style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: var(--hph-margin-3);
                    ">
                        <?php 
                        hph_component('badge', [
                            'text' => ucfirst($listing_status),
                            'variant' => $listing_status === 'active' ? 'success' : ($listing_status === 'pending' ? 'warning' : 'default'),
                            'size' => 'sm'
                        ]);
                        ?>
                        
                        <?php 
                        hph_component('dropdown', [
                            'trigger' => '<i class="fas fa-ellipsis-v"></i>',
                            'placement' => 'bottom-end',
                            'items' => [
                                [
                                    'text' => __('Edit Listing', 'happy-place-theme'),
                                    'icon' => 'edit',
                                    'href' => '#',
                                    'data' => ['action' => 'edit', 'listing-id' => $listing_id]
                                ],
                                [
                                    'text' => __('View Public', 'happy-place-theme'),
                                    'icon' => 'eye',
                                    'href' => get_permalink($listing_id),
                                    'target' => '_blank'
                                ],
                                [
                                    'text' => __('Duplicate', 'happy-place-theme'),
                                    'icon' => 'copy',
                                    'href' => '#',
                                    'data' => ['action' => 'duplicate', 'listing-id' => $listing_id]
                                ],
                                'divider',
                                [
                                    'text' => __('Delete', 'happy-place-theme'),
                                    'icon' => 'trash',
                                    'href' => '#',
                                    'variant' => 'danger',
                                    'data' => ['action' => 'delete', 'listing-id' => $listing_id]
                                ]
                            ]
                        ]);
                        ?>
                    </div>
                    
                    <!-- Performance Stats -->
                    <div style="
                        display: flex;
                        justify-content: space-between;
                        margin-top: var(--hph-margin-4);
                        padding: var(--hph-padding-3);
                        background: var(--hph-gray-50);
                        border-radius: var(--hph-border-radius);
                        font-size: var(--hph-text-sm);
                    ">
                        <div style="text-align: center;">
                            <div style="
                                font-weight: var(--hph-font-bold);
                                color: var(--hph-gray-900);
                            "><?php echo $views; ?></div>
                            <div style="color: var(--hph-gray-600);">
                                <i class="fas fa-eye" style="margin-right: var(--hph-margin-1);"></i>
                                <?php _e('Views', 'happy-place-theme'); ?>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div style="
                                font-weight: var(--hph-font-bold);
                                color: var(--hph-gray-900);
                            "><?php echo count($leads); ?></div>
                            <div style="color: var(--hph-gray-600);">
                                <i class="fas fa-users" style="margin-right: var(--hph-margin-1);"></i>
                                <?php _e('Leads', 'happy-place-theme'); ?>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div style="
                                font-weight: var(--hph-font-bold);
                                color: var(--hph-gray-900);
                            "><?php echo get_the_date('M j', $listing_id); ?></div>
                            <div style="color: var(--hph-gray-600);">
                                <i class="fas fa-calendar" style="margin-right: var(--hph-margin-1);"></i>
                                <?php _e('Listed', 'happy-place-theme'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Management Actions -->
                    <div style="
                        display: flex;
                        gap: var(--hph-gap-2);
                        margin-top: var(--hph-margin-4);
                    ">
                        <?php 
                        hph_component('button', [
                            'text' => __('Edit', 'happy-place-theme'),
                            'variant' => 'primary',
                            'size' => 'sm',
                            'icon' => 'edit',
                            'attributes' => [
                                'data-action' => 'edit',
                                'data-listing-id' => $listing_id
                            ]
                        ]);
                        
                        hph_component('button', [
                            'text' => __('Promote', 'happy-place-theme'),
                            'variant' => 'outline',
                            'size' => 'sm',
                            'icon' => 'star',
                            'attributes' => [
                                'data-action' => 'promote',
                                'data-listing-id' => $listing_id
                            ]
                        ]);
                        ?>
                    </div>
                    <?php
                    $management_content = ob_get_clean();
                    
                    // Create listing card using HPH card component directly
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
                        'description' => implode(' • ', $property_details) . $management_content,
                        'attributes' => ['data-listing-id' => $listing_id],
                        'class' => 'hph-listing-management-card'
                    ]);
                    ?>
                    
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($listings_query->max_num_pages > 1): ?>
                <?php
                hph_component('pagination', [
                    'current_page' => max(1, get_query_var('paged')),
                    'total_pages' => $listings_query->max_num_pages,
                    'variant' => 'default'
                ]);
                ?>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <?php 
            $empty_title = $status_filter === 'all' ? 
                __('No Listings Yet', 'happy-place-theme') : 
                sprintf(__('No %s Listings', 'happy-place-theme'), ucfirst($status_filter));
                
            $empty_description = $status_filter === 'all' ? 
                __('Start building your portfolio by adding your first property listing.', 'happy-place-theme') :
                sprintf(__('You don\'t have any listings with %s status at the moment.', 'happy-place-theme'), $status_filter);
                
            $empty_action = $status_filter === 'all' ? [
                'text' => __('Add Your First Listing', 'happy-place-theme'),
                'icon' => 'plus',
                'variant' => 'primary',
                'attributes' => ['id' => 'addFirstListingBtn']
            ] : [
                'text' => __('View All Listings', 'happy-place-theme'),
                'variant' => 'outline',
                'href' => '?section=listings'
            ];
            
            hph_component('empty-state', [
                'title' => $empty_title,
                'description' => $empty_description,
                'icon' => 'home',
                'action' => $empty_action
            ]);
            ?>
        <?php endif; ?>
    </div>
</div>