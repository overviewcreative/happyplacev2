<?php
/**
 * Dashboard Listings Management Section - Redesigned
 * 
 * Modern listing management with grid, list, and map views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and permissions
$current_user = wp_get_current_user();
$dashboard = \HappyPlace\Dashboard\Frontend_Admin_Dashboard::get_instance();

// Check permissions
if (!$dashboard->user_can('manage_all_listings') && !$dashboard->user_can('manage_own_listings')) {
    echo '<div class="alert alert-warning">' . __('You do not have permission to manage listings.', 'happy-place') . '</div>';
    return;
}

// Get query parameters
$view_mode = $_GET['view'] ?? 'grid';
$status_filter = $_GET['status'] ?? 'all';
$search_query = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'date_created';
$sort_order = $_GET['order'] ?? 'desc';
$page = max(1, intval($_GET['paged'] ?? 1));
$per_page = 12;

// Build listing query
$query_args = [
    'post_type' => 'listing',
    'posts_per_page' => $per_page,
    'paged' => $page,
    'meta_query' => [],
    'orderby' => $sort_by === 'date_created' ? 'date' : 'meta_value',
    'order' => strtoupper($sort_order)
];

// Add search if provided
if (!empty($search_query)) {
    $query_args['s'] = $search_query;
}

// Add status filter
if ($status_filter !== 'all') {
    $query_args['meta_query'][] = [
        'key' => 'property_status',
        'value' => $status_filter,
        'compare' => '='
    ];
}

// Filter by user permissions
if ($dashboard->user_can('manage_own_listings') && !$dashboard->user_can('manage_all_listings')) {
    $query_args['meta_query'][] = [
        'key' => 'listing_agent',
        'value' => $current_user->ID,
        'compare' => '='
    ];
}

// Add meta sorting
if ($sort_by !== 'date_created') {
    $query_args['meta_key'] = $sort_by;
    if ($sort_by === 'price') {
        $query_args['meta_type'] = 'NUMERIC';
    }
}

$listings_query = new WP_Query($query_args);

// Get status counts for filters
$status_counts = [];
$statuses = ['active', 'pending', 'sold', 'coming_soon', 'withdrawn'];
foreach ($statuses as $status) {
    $count_args = array_merge($query_args, [
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => array_merge(
            $query_args['meta_query'],
            [['key' => 'property_status', 'value' => $status, 'compare' => '=']]
        )
    ]);
    $count_query = new WP_Query($count_args);
    $status_counts[$status] = $count_query->found_posts;
}
?>

<div class="listings-management-redesigned">
    
    <!-- Listings Header Section -->
    <div class="listings-hero">
        <div class="listings-hero-bg"></div>
        <div class="listings-hero-content">
            <div class="listings-hero-info">
                <h1 class="listings-title"><?php _e('Property Listings', 'happy-place'); ?></h1>
                <p class="listings-subtitle">
                    <?php 
                    printf(
                        __('Manage your %d active listings and showcase your properties', 'happy-place'),
                        $listings_query->found_posts
                    ); 
                    ?>
                </p>
                <div class="listings-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $status_counts['active'] ?? 0; ?></span>
                        <span class="stat-label"><?php _e('Active', 'happy-place'); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $status_counts['pending'] ?? 0; ?></span>
                        <span class="stat-label"><?php _e('Pending', 'happy-place'); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $status_counts['sold'] ?? 0; ?></span>
                        <span class="stat-label"><?php _e('Sold', 'happy-place'); ?></span>
                    </div>
                </div>
            </div>
            <div class="listings-hero-actions">
                <a href="<?php echo add_query_arg(['dashboard_section' => 'listings', 'action' => 'add'], get_permalink()); ?>" 
                   class="btn btn-primary btn-lg add-listing-btn">
                    <span class="hph-icon-plus"></span>
                    <?php _e('Add New Listing', 'happy-place'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="listings-controls">
        <form class="listings-search-form" method="GET">
            <input type="hidden" name="dashboard_section" value="listings">
            <input type="hidden" name="view" value="<?php echo esc_attr($view_mode); ?>">
            
            <div class="search-controls">
                <div class="search-input-group">
                    <span class="search-icon hph-icon-search"></span>
                    <input type="text" 
                           name="search" 
                           value="<?php echo esc_attr($search_query); ?>"
                           placeholder="<?php _e('Search by address, MLS, or description...', 'happy-place'); ?>"
                           class="search-input">
                    <button type="submit" class="search-btn">
                        <?php _e('Search', 'happy-place'); ?>
                    </button>
                </div>
            </div>
            
            <div class="filter-controls">
                <div class="filter-group">
                    <label for="status-filter"><?php _e('Status', 'happy-place'); ?></label>
                    <select name="status" id="status-filter">
                        <option value="all" <?php selected($status_filter, 'all'); ?>><?php _e('All Statuses', 'happy-place'); ?></option>
                        <option value="active" <?php selected($status_filter, 'active'); ?>><?php _e('Active', 'happy-place'); ?></option>
                        <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php _e('Pending', 'happy-place'); ?></option>
                        <option value="sold" <?php selected($status_filter, 'sold'); ?>><?php _e('Sold', 'happy-place'); ?></option>
                        <option value="coming_soon" <?php selected($status_filter, 'coming_soon'); ?>><?php _e('Coming Soon', 'happy-place'); ?></option>
                        <option value="withdrawn" <?php selected($status_filter, 'withdrawn'); ?>><?php _e('Withdrawn', 'happy-place'); ?></option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="sort-filter"><?php _e('Sort By', 'happy-place'); ?></label>
                    <select name="sort" id="sort-filter">
                        <option value="date_created" <?php selected($sort_by, 'date_created'); ?>><?php _e('Date Created', 'happy-place'); ?></option>
                        <option value="price" <?php selected($sort_by, 'price'); ?>><?php _e('Price', 'happy-place'); ?></option>
                        <option value="property_status" <?php selected($sort_by, 'property_status'); ?>><?php _e('Status', 'happy-place'); ?></option>
                        <option value="street_address" <?php selected($sort_by, 'street_address'); ?>><?php _e('Address', 'happy-place'); ?></option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="order-filter"><?php _e('Order', 'happy-place'); ?></label>
                    <select name="order" id="order-filter">
                        <option value="desc" <?php selected($sort_order, 'desc'); ?>><?php _e('Newest First', 'happy-place'); ?></option>
                        <option value="asc" <?php selected($sort_order, 'asc'); ?>><?php _e('Oldest First', 'happy-place'); ?></option>
                    </select>
                </div>
            </div>
        </form>
        
        <!-- View Mode Toggle -->
        <div class="view-controls">
            <div class="view-toggle">
                <a href="<?php echo add_query_arg('view', 'grid'); ?>" 
                   class="view-btn <?php echo $view_mode === 'grid' ? 'active' : ''; ?>"
                   title="<?php _e('Grid View', 'happy-place'); ?>">
                    <span class="hph-icon-grid"></span>
                </a>
                <a href="<?php echo add_query_arg('view', 'list'); ?>" 
                   class="view-btn <?php echo $view_mode === 'list' ? 'active' : ''; ?>"
                   title="<?php _e('List View', 'happy-place'); ?>">
                    <span class="hph-icon-list"></span>
                </a>
                <a href="<?php echo add_query_arg('view', 'map'); ?>" 
                   class="view-btn <?php echo $view_mode === 'map' ? 'active' : ''; ?>"
                   title="<?php _e('Map View', 'happy-place'); ?>">
                    <span class="hph-icon-map"></span>
                </a>
            </div>
            
            <div class="bulk-actions">
                <select id="bulk-action-select">
                    <option value=""><?php _e('Bulk Actions', 'happy-place'); ?></option>
                    <option value="activate"><?php _e('Mark as Active', 'happy-place'); ?></option>
                    <option value="deactivate"><?php _e('Mark as Inactive', 'happy-place'); ?></option>
                    <option value="delete"><?php _e('Delete Selected', 'happy-place'); ?></option>
                    <option value="export"><?php _e('Export Selected', 'happy-place'); ?></option>
                </select>
                <button type="button" id="apply-bulk-action" class="btn btn-secondary" disabled>
                    <?php _e('Apply', 'happy-place'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Listings Content -->
    <div class="listings-content">
        <?php if ($listings_query->have_posts()): ?>
            
            <!-- Grid View -->
            <?php if ($view_mode === 'grid'): ?>
                <div class="listings-grid">
                    <?php while ($listings_query->have_posts()): $listings_query->the_post(); ?>
                        <?php get_template_part('template-parts/dashboard/listing-card-grid', null, ['listing_id' => get_the_ID()]); ?>
                    <?php endwhile; ?>
                </div>
            
            <!-- List View -->
            <?php elseif ($view_mode === 'list'): ?>
                <div class="listings-list">
                    <?php while ($listings_query->have_posts()): $listings_query->the_post(); ?>
                        <?php get_template_part('template-parts/dashboard/listing-card-list', null, ['listing_id' => get_the_ID()]); ?>
                    <?php endwhile; ?>
                </div>
            
            <!-- Map View -->
            <?php elseif ($view_mode === 'map'): ?>
                <div class="listings-map-view">
                    <div class="map-container">
                        <div id="listings-map" class="listings-map"></div>
                        <div class="map-loading">
                            <div class="spinner"></div>
                            <p><?php _e('Loading map...', 'happy-place'); ?></p>
                        </div>
                    </div>
                    <div class="map-sidebar">
                        <div class="map-listings-list">
                            <?php while ($listings_query->have_posts()): $listings_query->the_post(); ?>
                                <?php get_template_part('template-parts/dashboard/listing-card-map', null, ['listing_id' => get_the_ID()]); ?>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="listings-empty-state">
                <div class="empty-state-icon">
                    <span class="hph-icon-home"></span>
                </div>
                <h3><?php _e('No Listings Found', 'happy-place'); ?></h3>
                <p>
                    <?php if (!empty($search_query) || $status_filter !== 'all'): ?>
                        <?php _e('No listings match your current filters. Try adjusting your search criteria.', 'happy-place'); ?>
                    <?php else: ?>
                        <?php _e('You haven\'t created any listings yet. Get started by adding your first property.', 'happy-place'); ?>
                    <?php endif; ?>
                </p>
                <div class="empty-state-actions">
                    <?php if (!empty($search_query) || $status_filter !== 'all'): ?>
                        <a href="<?php echo remove_query_arg(['search', 'status']); ?>" class="btn btn-secondary">
                            <?php _e('Clear Filters', 'happy-place'); ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo add_query_arg(['dashboard_section' => 'listings', 'action' => 'add'], get_permalink()); ?>" 
                       class="btn btn-primary">
                        <span class="hph-icon-plus"></span>
                        <?php _e('Add Your First Listing', 'happy-place'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($listings_query->max_num_pages > 1): ?>
        <div class="listings-pagination">
            <?php
            $pagination_args = [
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'current' => $page,
                'total' => $listings_query->max_num_pages,
                'prev_text' => '<span class="hph-icon-chevron-left"></span>' . __('Previous', 'happy-place'),
                'next_text' => __('Next', 'happy-place') . '<span class="hph-icon-chevron-right"></span>',
                'type' => 'list',
                'end_size' => 2,
                'mid_size' => 1
            ];
            echo paginate_links($pagination_args);
            ?>
        </div>
    <?php endif; ?>
</div>

<!-- Map Data for JavaScript -->
<?php if ($view_mode === 'map' && $listings_query->have_posts()): ?>
    <script type="application/json" id="listings-map-data">
    {
        "listings": [
            <?php 
            $listings_query->rewind_posts();
            $map_listings = [];
            while ($listings_query->have_posts()): 
                $listings_query->the_post();
                $listing_id = get_the_ID();
                $lat = get_field('latitude', $listing_id);
                $lng = get_field('longitude', $listing_id);
                $price = get_field('price', $listing_id);
                $address = get_field('street_address', $listing_id);
                
                if ($lat && $lng) {
                    $map_listings[] = [
                        'id' => $listing_id,
                        'lat' => floatval($lat),
                        'lng' => floatval($lng),
                        'title' => get_the_title(),
                        'price' => $price ? number_format($price) : '',
                        'address' => $address,
                        'status' => get_field('property_status', $listing_id),
                        'image' => get_the_post_thumbnail_url($listing_id, 'medium'),
                        'url' => get_permalink($listing_id)
                    ];
                }
            endwhile;
            echo json_encode($map_listings);
            ?>
        ]
    }
    </script>
<?php endif; ?>

<?php wp_reset_postdata(); ?>

<script>
jQuery(document).ready(function($) {
    // Auto-submit filters on change
    $('.filter-controls select').on('change', function() {
        $(this).closest('form').submit();
    });
    
    // Bulk actions
    $('#bulk-action-select').on('change', function() {
        $('#apply-bulk-action').prop('disabled', $(this).val() === '');
    });
    
    // Select all checkbox
    $(document).on('change', '#select-all-listings', function() {
        $('.listing-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkActionsState();
    });
    
    // Individual checkboxes
    $(document).on('change', '.listing-checkbox', function() {
        updateBulkActionsState();
    });
    
    function updateBulkActionsState() {
        const checkedCount = $('.listing-checkbox:checked').length;
        $('#apply-bulk-action').prop('disabled', checkedCount === 0 || $('#bulk-action-select').val() === '');
    }
    
    // Apply bulk actions
    $('#apply-bulk-action').on('click', function() {
        const action = $('#bulk-action-select').val();
        const checkedIds = $('.listing-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (action && checkedIds.length > 0) {
            if (action === 'delete') {
                if (!confirm('<?php _e('Are you sure you want to delete the selected listings?', 'happy-place'); ?>')) {
                    return;
                }
            }
            
            // Process bulk action via AJAX
            $.ajax({
                url: hph_dashboard.ajax_url,
                type: 'POST',
                data: {
                    action: 'hph_bulk_listing_action',
                    bulk_action: action,
                    listing_ids: checkedIds,
                    nonce: hph_dashboard.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php _e('An error occurred', 'happy-place'); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred while processing the bulk action', 'happy-place'); ?>');
                }
            });
        }
    });
    
    // Initialize map if in map view
    <?php if ($view_mode === 'map'): ?>
    initListingsMap();
    <?php endif; ?>
});

// Map functionality
function initListingsMap() {
    const mapData = JSON.parse(document.getElementById('listings-map-data').textContent);
    
    if (!mapData.listings.length) {
        jQuery('.map-loading').html('<p><?php _e('No listings with location data found', 'happy-place'); ?></p>');
        return;
    }
    
    // Initialize map (assuming Google Maps)
    const map = new google.maps.Map(document.getElementById('listings-map'), {
        zoom: 12,
        center: { lat: mapData.listings[0].lat, lng: mapData.listings[0].lng },
        styles: [
            // Add custom map styling here
        ]
    });
    
    const bounds = new google.maps.LatLngBounds();
    const markers = [];
    
    mapData.listings.forEach(function(listing) {
        const marker = new google.maps.Marker({
            position: { lat: listing.lat, lng: listing.lng },
            map: map,
            title: listing.title,
            icon: {
                url: 'data:image/svg+xml;base64,' + btoa(`
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="18" fill="#51bae0" stroke="#fff" stroke-width="4"/>
                        <text x="20" y="25" text-anchor="middle" fill="white" font-size="12" font-weight="bold">$</text>
                    </svg>
                `),
                scaledSize: new google.maps.Size(40, 40)
            }
        });
        
        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div class="map-info-window">
                    <img src="${listing.image}" alt="${listing.title}" />
                    <div class="info-content">
                        <h4>${listing.title}</h4>
                        <p class="price">$${listing.price}</p>
                        <p class="address">${listing.address}</p>
                        <a href="${listing.url}" class="btn btn-sm btn-primary"><?php _e('View Details', 'happy-place'); ?></a>
                    </div>
                </div>
            `
        });
        
        marker.addListener('click', function() {
            markers.forEach(m => m.infoWindow.close());
            infoWindow.open(map, marker);
        });
        
        marker.infoWindow = infoWindow;
        markers.push(marker);
        bounds.extend(marker.getPosition());
    });
    
    map.fitBounds(bounds);
    jQuery('.map-loading').hide();
}
</script>