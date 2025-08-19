<?php
/**
 * Dashboard Listings Content
 * Content for the listings section within the dashboard layout
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get listings - simplified approach to ensure it works
$listings_args = array(
    'post_type' => 'listing',
    'post_status' => 'publish',
    'posts_per_page' => 50,
    'orderby' => 'date',
    'order' => 'DESC'
);

// Query the listings using standard WP_Query
$listings_query = new WP_Query($listings_args);
$listings = $listings_query->posts;

// For now, show all listings regardless of agent
$agent_id = 0;

// Debug information
if (WP_DEBUG) {
    error_log('Dashboard Listings Debug:');
    error_log('Found Listings: ' . count($listings));
}

// Add a simple fallback display for debugging
echo '<!-- Debug: Found ' . count($listings) . ' listings -->';
if (!empty($listings)) {
    echo '<!-- Debug: First listing ID: ' . $listings[0]->ID . ', Title: ' . $listings[0]->post_title . ' -->';
}

// Get stats - simplified for now
$total_listings = count($listings);
$active_listings = $total_listings; // Assume all are active for now
$pending_listings = 0;
$sold_listings = 0;

// TODO: Implement proper status checking when bridge functions are ready
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Total Listings</h3>
            <p class="stat-value" id="totalListings"><?php echo $total_listings; ?></p>
            <p class="stat-change stat-change-up">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                    <path d="M5.293 3.293a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L6 5.414 3.707 7.707a1 1 0 01-1.414-1.414l3-3z"/>
                </svg>
                <span>12% from last month</span>
            </p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Active Listings</h3>
            <p class="stat-value" id="activeListings"><?php echo $active_listings; ?></p>
            <p class="stat-change stat-change-up">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                    <path d="M5.293 3.293a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L6 5.414 3.707 7.707a1 1 0 01-1.414-1.414l3-3z"/>
                </svg>
                <span>8% from last month</span>
            </p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Pending</h3>
            <p class="stat-value" id="pendingListings"><?php echo $pending_listings; ?></p>
            <p class="stat-change stat-change-up">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                    <path d="M5.293 3.293a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L6 5.414 3.707 7.707a1 1 0 01-1.414-1.414l3-3z"/>
                </svg>
                <span>2 this week</span>
            </p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon stat-icon-info">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Sold This Month</h3>
            <p class="stat-value" id="soldListings"><?php echo $sold_listings; ?></p>
            <p class="stat-change stat-change-up">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                    <path d="M5.293 3.293a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L6 5.414 3.707 7.707a1 1 0 01-1.414-1.414l3-3z"/>
                </svg>
                <span>20% from last month</span>
            </p>
        </div>
    </div>
</div>

<!-- Listings Management Interface -->
<div class="listings-management-section">
    <!-- Filters and Controls -->
    <div class="listings-controls">
        <div class="controls-left">
            <!-- Status Filter -->
            <div class="filter-group">
                <label class="filter-label">Status:</label>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-status="all">All (<?php echo $total_listings; ?>)</button>
                    <button class="filter-btn" data-status="active">Active (<?php echo $active_listings; ?>)</button>
                    <button class="filter-btn" data-status="pending">Pending (<?php echo $pending_listings; ?>)</button>
                    <button class="filter-btn" data-status="sold">Sold (<?php echo $sold_listings; ?>)</button>
                </div>
            </div>
            
            <!-- Search -->
            <div class="listings-search">
                <i class="fas fa-search search-icon"></i>
                <input type="search" placeholder="Search by address, MLS#, or client name..." class="search-input" id="listingsSearch">
            </div>
        </div>
        
        <div class="controls-right">
            <!-- Sort Options -->
            <select class="filter-select" id="listingsSort">
                <option value="date_desc">Latest First</option>
                <option value="date_asc">Oldest First</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="address_asc">Address A-Z</option>
                <option value="status_asc">Status</option>
            </select>
            
            <!-- Add New Listing Button -->
            <button class="btn btn-primary me-3" 
                    data-bs-toggle="modal" 
                    data-bs-target="#listingFormModal"
                    data-listing-id="0">
                <i class="fas fa-plus me-2"></i> Add New Listing
            </button>
            
            <!-- View Toggle -->
            <div class="view-toggle">
                <button class="view-btn active" data-view="list" aria-label="List view">
                    <i class="fas fa-list"></i>
                </button>
                <button class="view-btn" data-view="grid" aria-label="Grid view">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-btn" data-view="map" aria-label="Map view">
                    <i class="fas fa-map"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Listings Container -->
    <div class="listings-container" id="listingsContainer">
        <!-- List View (Default) -->
        <div class="listings-list active">
            <!-- List Header -->
            <div class="list-header">
                <div class="list-header-item">
                    <input type="checkbox" class="bulk-checkbox" id="selectAllListings">
                </div>
                <div class="list-header-item">Photo</div>
                <div class="list-header-item">Property Details</div>
                <div class="list-header-item">Price</div>
                <div class="list-header-item">Status</div>
                <div class="list-header-item">Features</div>
                <div class="list-header-item">Actions</div>
            </div>
            
            <!-- Listing Items -->
            <?php if (!empty($listings)) : ?>
                <?php foreach ($listings as $post) : 
                    setup_postdata($post);
                    $listing_id = get_the_ID();
                    
                    // Get listing data using bridge functions with fallbacks
                    $price = function_exists('hpt_get_listing_price_formatted') ? hpt_get_listing_price_formatted($listing_id) : get_post_meta($listing_id, 'price', true);
                    $status = function_exists('hpt_get_listing_status') ? hpt_get_listing_status($listing_id) : get_post_meta($listing_id, 'listing_status', true);
                    $address = function_exists('hpt_get_listing_address') ? hpt_get_listing_address($listing_id) : get_post_meta($listing_id, 'address', true);
                    $bedrooms = function_exists('hpt_get_listing_bedrooms') ? hpt_get_listing_bedrooms($listing_id) : get_post_meta($listing_id, 'bedrooms', true);
                    $bathrooms = function_exists('hpt_get_listing_bathrooms') ? hpt_get_listing_bathrooms($listing_id) : get_post_meta($listing_id, 'bathrooms', true);
                    $sqft = function_exists('hpt_get_listing_square_feet') ? hpt_get_listing_square_feet($listing_id) : get_post_meta($listing_id, 'square_feet', true);
                    $mls = function_exists('hpt_get_listing_mls_number') ? hpt_get_listing_mls_number($listing_id) : get_post_meta($listing_id, 'mls_number', true);
                    $days_ago = human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago';
                    
                    // Format price if it's a number
                    if (is_numeric($price)) {
                        $price = '$' . number_format($price);
                    }
                    
                    // Default status if empty
                    if (empty($status)) {
                        $status = 'active';
                    }
                    
                    // Status class
                    $status_class = $status;
                    if ($status === 'coming-soon') $status_class = 'coming-soon';
                ?>
                <div class="listing-list-item">
                    <div class="list-item-cell">
                        <input type="checkbox" class="listing-checkbox" data-listing-id="<?php echo esc_attr($listing_id); ?>">
                    </div>
                    <div class="list-item-cell">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('thumbnail', array('class' => 'listing-thumbnail')); ?>
                        <?php else : ?>
                            <img src="/wp-content/themes/happy-place-theme/assets/images/placeholder-house.jpg" alt="<?php the_title_attribute(); ?>" class="listing-thumbnail">
                        <?php endif; ?>
                    </div>
                    <div class="list-item-cell">
                        <div class="listing-details">
                            <h4 class="listing-title"><?php the_title(); ?></h4>
                            <?php if ($address) : ?>
                                <p class="listing-address">
                                    <?php 
                                    if (is_array($address)) {
                                        echo esc_html($address['city'] . ', ' . $address['state'] . ' ' . $address['zip']);
                                    } else {
                                        echo esc_html($address);
                                    }
                                    ?>
                                </p>
                            <?php endif; ?>
                            <p class="listing-meta">
                                <?php if ($mls) : ?>MLS# <?php echo esc_html($mls); ?> • <?php endif; ?>
                                Listed <?php echo esc_html($days_ago); ?>
                            </p>
                        </div>
                    </div>
                    <div class="list-item-cell">
                        <span class="listing-price"><?php echo esc_html($price); ?></span>
                    </div>
                    <div class="list-item-cell">
                        <span class="listing-status <?php echo esc_attr($status_class); ?>">
                            <?php echo esc_html(ucfirst($status)); ?>
                        </span>
                    </div>
                    <div class="list-item-cell">
                        <div class="listing-features">
                            <?php if ($bedrooms) : ?><span><?php echo esc_html($bedrooms); ?> bed</span><?php endif; ?>
                            <?php if ($bathrooms) : ?><span><?php echo esc_html($bathrooms); ?> bath</span><?php endif; ?>
                            <?php if ($sqft) : ?><span><?php echo number_format($sqft); ?> sq ft</span><?php endif; ?>
                        </div>
                    </div>
                    <div class="list-item-cell">
                        <div class="listing-actions">
                            <?php if (current_user_can('edit_post', $listing_id)) : ?>
                                <button class="action-btn-sm edit-listing-btn" 
                                        title="Edit" 
                                        data-listing-id="<?php echo esc_attr($listing_id); ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#listingFormModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                            <?php endif; ?>
                            <a href="<?php the_permalink(); ?>" class="action-btn-sm" title="View Details" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="action-btn-sm" title="More Options" data-listing-id="<?php echo esc_attr($listing_id); ?>">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="listing-list-item">
                    <div class="list-item-cell" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <i class="fas fa-home fa-3x text-muted mb-3"></i>
                        <h3>No Listings Found</h3>
                        <p class="text-muted">Start by adding your first property listing.</p>
                        <button class="btn btn-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#listingFormModal"
                                data-listing-id="0">
                            <i class="fas fa-plus me-2"></i> Add Your First Listing
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Grid View -->
        <div class="listings-grid" style="display: none;">
            <?php if (!empty($listings)) : ?>
                <?php 
                // Set up the global post object for each listing
                foreach ($listings as $post) : 
                    setup_postdata($post);
                    // Use the existing listing-card template part
                    get_template_part('template-parts/listing', 'card');
                endforeach;
                // Reset post data
                wp_reset_postdata();
                ?>
            <?php else : ?>
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-home fa-3x text-muted mb-3"></i>
                        <h3>No Listings Yet</h3>
                        <p class="text-muted">Start by adding your first property listing.</p>
                        <button class="btn btn-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#listingFormModal"
                                data-listing-id="0">
                            <i class="fas fa-plus me-2"></i> Add Your First Listing
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Map View -->
        <div class="listings-map" style="display: none;">
            <div class="map-container">
                <div class="map-placeholder">
                    <svg width="64" height="64" viewBox="0 0 64 64" fill="currentColor" opacity="0.3">
                        <path d="M63.267.113A2 2 0 0 1 64 2v60a2 2 0 0 1-1.608 1.96l-20 4a2.01 2.01 0 0 1-.784 0L22 64.08l-19.608 3.92A2 2 0 0 1 0 66V6a2 2 0 0 1 1.608-1.96l20-4a2.01 2.01 0 0 1 .784 0L42 3.92l19.608-3.92A2.04 2.04 0 0 1 63.267.113zM40 7.64l-16-3.2v51.92l16 3.2V7.64zm4 51.92l16-3.2V5.44l-16 3.2v51.92zM20 56.16V4.24L4 7.44v51.92l16-3.2z"/>
                    </svg>
                    <h3>Map View</h3>
                    <p>Interactive map showing all your listings will appear here</p>
                    <button class="btn btn-primary btn-sm">Initialize Map</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="listings-pagination">
        <div class="pagination-info">
            <?php if ($total_listings > 0) : ?>
                Showing 1-<?php echo min($total_listings, 20); ?> of <?php echo $total_listings; ?> listings
            <?php else : ?>
                No listings to display
            <?php endif; ?>
        </div>
        <div class="pagination-controls">
            <button class="pagination-btn" disabled>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
            </button>
            <div class="pagination-numbers">
                <button class="pagination-number active">1</button>
                <button class="pagination-number">2</button>
                <button class="pagination-number">3</button>
            </div>
            <button class="pagination-btn">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Quick Actions Panel -->
<div class="dashboard-grid">
    <!-- Recent Listings -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2 class="card-title">Recent Listings</h2>
            <a href="#" class="card-link">View All</a>
        </div>
        <div class="card-body">
            <div class="recent-listings" id="recentListings">
                <?php if (!empty($listings)) : ?>
                    <?php 
                    // Show only the 3 most recent listings
                    $recent_listings = array_slice($listings, 0, 3);
                    foreach ($recent_listings as $post) : 
                        setup_postdata($post);
                        $listing_id = get_the_ID();
                        
                        // Get listing data using bridge functions with fallbacks
                        $price = function_exists('hpt_get_listing_price_formatted') ? hpt_get_listing_price_formatted($listing_id) : get_post_meta($listing_id, 'price', true);
                        $status = function_exists('hpt_get_listing_status') ? hpt_get_listing_status($listing_id) : get_post_meta($listing_id, 'listing_status', true);
                        $bedrooms = function_exists('hpt_get_listing_bedrooms') ? hpt_get_listing_bedrooms($listing_id) : get_post_meta($listing_id, 'bedrooms', true);
                        $bathrooms = function_exists('hpt_get_listing_bathrooms') ? hpt_get_listing_bathrooms($listing_id) : get_post_meta($listing_id, 'bathrooms', true);
                        
                        // Format price if it's a number
                        if (is_numeric($price)) {
                            $price = '$' . number_format($price);
                        }
                        
                        // Default status if empty
                        if (empty($status)) {
                            $status = 'active';
                        }
                        
                        // Status class
                        $status_class = $status;
                        if ($status === 'coming-soon') $status_class = 'coming-soon';
                    ?>
                    <div class="listing-item">
                        <div class="listing-image">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('thumbnail', array(
                                    'style' => 'width:60px;height:45px;object-fit:cover;border-radius:4px;',
                                    'alt' => get_the_title()
                                )); ?>
                            <?php else : ?>
                                <img src="/wp-content/themes/happy-place-theme/assets/images/placeholder-house.jpg" 
                                     alt="<?php the_title_attribute(); ?>" 
                                     style="width:60px;height:45px;object-fit:cover;border-radius:4px;">
                            <?php endif; ?>
                        </div>
                        <div class="listing-info">
                            <h4 class="listing-title"><?php the_title(); ?></h4>
                            <p class="listing-details">
                                <?php echo esc_html($price); ?>
                                <?php if ($bedrooms || $bathrooms) : ?>
                                    • <?php echo esc_html($bedrooms); ?> bed, <?php echo esc_html($bathrooms); ?> bath
                                <?php endif; ?>
                            </p>
                            <span class="listing-status <?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <div class="listing-item">
                        <div class="listing-info">
                            <p class="text-muted">No recent listings to display.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Performance Chart -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2 class="card-title">Listing Performance</h2>
            <select class="card-select">
                <option>Last 30 days</option>
                <option>Last 90 days</option>
                <option>Year to date</option>
            </select>
        </div>
        <div class="card-body">
            <div class="chart-container" id="listingChart">
                <canvas id="listingPerformanceChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Market Insights -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2 class="card-title">Market Insights</h2>
            <a href="#" class="card-link">View Report</a>
        </div>
        <div class="card-body">
            <div class="insights-list">
                <div class="insight-item">
                    <div class="insight-icon">📈</div>
                    <div class="insight-content">
                        <p class="insight-text">Average days on market: 24 days</p>
                        <span class="insight-change positive">-3 days vs last month</span>
                    </div>
                </div>
                <div class="insight-item">
                    <div class="insight-icon">💰</div>
                    <div class="insight-content">
                        <p class="insight-text">Median sale price: $425,000</p>
                        <span class="insight-change positive">+5.2% vs last month</span>
                    </div>
                </div>
                <div class="insight-item">
                    <div class="insight-icon">🏠</div>
                    <div class="insight-content">
                        <p class="insight-text">Active inventory: 127 homes</p>
                        <span class="insight-change neutral">Same as last month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize listings page when content loads
jQuery(document).ready(function($) {
    console.log('Dashboard listings script loaded');
    
    // Check if we have the listings management section
    if ($('.listings-management-section').length > 0) {
        console.log('Listings section found, initializing controller');
        
        // Define ListingsController inline if not already defined
        if (typeof window.ListingsController === 'undefined') {
            console.log('Loading ListingsController inline');
            // Load the script content here since it might not be loading from external file
        }
        
        // Initialize view switching manually
        $('.view-btn').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const view = $btn.data('view');
            console.log('Switching to view:', view);
            
            // Update button states
            $('.view-btn').removeClass('active');
            $btn.addClass('active');
            
            // Hide all views
            $('.listings-list, .listings-grid, .listings-map').hide().removeClass('active');
            
            // Show selected view
            switch(view) {
                case 'list':
                    $('.listings-list').show().addClass('active');
                    break;
                case 'grid':
                    $('.listings-grid').show().addClass('active');
                    break;
                case 'map':
                    $('.listings-map').show().addClass('active');
                    break;
            }
            
            // Save preference
            localStorage.setItem('listings_view', view);
        });
        
        // Set initial view
        const savedView = localStorage.getItem('listings_view') || 'list';
        $(`.view-btn[data-view="${savedView}"]`).trigger('click');
        
        console.log('View switching initialized');
    }
});
</script>

<?php
// Form components are loaded by the main dashboard template
// Do not duplicate includes here to avoid conflicts
?>

<!-- JavaScript handled by main dashboard template -->