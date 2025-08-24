<?php
/**
 * Dashboard Listings Management Section
 * 
 * @package HappyPlaceTheme
 * @subpackage Dashboard
 * @version 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get theme instance and services
$theme = HPH_Theme::instance();
$dashboard_service = $theme->get_service('dashboard');

// Ensure user has proper permissions
if (!current_user_can('edit_posts')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Get current user's agent ID using bridge function
$current_user_id = get_current_user_id();
$agent_id = hpt_get_user_agent_id($current_user_id);

// Use enhanced service-powered listings if available
$listings = array();
$total_listings = 0;

if (function_exists('hpt_get_user_listings_via_service') && function_exists('hpt_services_available') && hpt_services_available()) {
    // Use plugin services for enhanced performance and features
    try {
        $listings = hpt_get_user_listings_via_service($current_user_id, array(
            'per_page' => 20,
            'page' => 1,
            'status' => 'all',
            'sort' => 'date-desc'
        ));
        $total_listings = hpt_count_user_listings_enhanced($current_user_id, 'all');
        
        // Create mock query object for template compatibility
        $listings_query = (object) array(
            'posts' => array_map(function($listing) {
                return (object) $listing;
            }, $listings),
            'found_posts' => $total_listings,
            'have_posts' => !empty($listings),
            'post_count' => count($listings)
        );
        
        $using_services = true;
        
    } catch (Exception $e) {
        if (function_exists('hp_log')) {
            hp_log('Service listings error: ' . $e->getMessage(), 'error', 'dashboard');
        }
        $using_services = false;
    }
} else {
    $using_services = false;
}

// Fallback to traditional WP_Query if services aren't available
if (!$using_services) {
    // Build query args using proper WP_Query structure
    $query_args = array(
        'post_type' => 'listing',
        'posts_per_page' => 20,
        'post_status' => array('publish', 'draft', 'private'),
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => false, // We need pagination data
    );

    // For non-admin users, show only their own listings
    if (!current_user_can('manage_options') && $agent_id) {
        $query_args['meta_query'] = array(
            array(
                'key' => 'listing_agent',
                'value' => $agent_id,
                'compare' => '='
            )
        );
    } elseif (!current_user_can('manage_options')) {
        // If no agent ID found, show author's posts
        $query_args['author'] = $current_user_id;
    }

    // Execute query
    $listings_query = new WP_Query($query_args);
}

// Get enhanced dashboard stats using service layer
$stats = array(
    'active_listings' => 0,
    'pending_listings' => 0,
    'sold_listings' => 0,
    'total_leads' => 0
);

// Try to use enhanced plugin service stats first
if (function_exists('hpt_get_dashboard_stats_enhanced') && $using_services) {
    try {
        $enhanced_stats = hpt_get_dashboard_stats_enhanced($current_user_id);
        if ($enhanced_stats && is_array($enhanced_stats)) {
            $stats = array_merge($stats, $enhanced_stats);
        }
    } catch (Exception $e) {
        if (function_exists('hp_log')) {
            hp_log('Enhanced stats error: ' . $e->getMessage(), 'error', 'dashboard');
        }
    }
}

// Fallback to theme service if plugin services aren't available
if ((!$stats || array_sum($stats) === 0) && $dashboard_service && method_exists($dashboard_service, 'get_user_stats')) {
    try {
        $stats = $dashboard_service->get_user_stats($current_user_id);
    } catch (Exception $e) {
        // Log error and use fallback stats
        if (function_exists('hp_log')) {
            hp_log('Dashboard stats error: ' . $e->getMessage(), 'error', 'dashboard');
        }
    }
}

// Final fallback stats calculation if all services fail
if (!$stats || empty($stats) || array_sum($stats) === 0) {
    // Use the query args from fallback if they exist, otherwise create minimal ones
    $fallback_query_args = isset($query_args) ? $query_args : array(
        'post_type' => 'listing',
        'author' => $current_user_id,
        'posts_per_page' => -1
    );
    
    $active_count = new WP_Query(array_merge($fallback_query_args, array(
        'post_status' => 'publish',
        'fields' => 'ids'
    )));
    
    $draft_count = new WP_Query(array_merge($fallback_query_args, array(
        'post_status' => 'draft',
        'fields' => 'ids'
    )));
    
    $stats = array(
        'active_listings' => $active_count->found_posts,
        'draft_listings' => $draft_count->found_posts,
        'pending_listings' => 0,
        'sold_listings' => 0,
        'total_listings' => $active_count->found_posts + $draft_count->found_posts
    );
    wp_reset_postdata();
}

?>

<div class="dashboard-section listings-section" id="listingsSection">
    
    <!-- Dashboard Header -->
    <div class="section-header">
        <div class="header-content">
            <h1 class="hph-section-title">
                <i class="fas fa-home"></i>
                Listings Management
            </h1>
            <p class="section-description">Manage your property listings, track performance, and handle inquiries.</p>
        </div>
        <div class="header-actions">
            <button type="button" class="btn btn-outline-primary" onclick="toggleListingView()">
                <i class="fas fa-th" id="viewToggleIcon"></i>
                <span id="viewToggleText">Grid View</span>
            </button>
            <button type="button" class="btn btn-primary" onclick="openListingModal()">
                <i class="fas fa-plus"></i>
                Add Listing
            </button>
        </div>
    </div>
    
    <!-- Stats Overview -->
    <div class="dashboard-stats">
        <div class="stat-card stat-primary">
            <div class="hph-stat-icon">
                <i class="fas fa-home"></i>
            </div>
            <div class="hph-stat-content">
                <div class="hph-stat-value"><?php echo esc_html($stats['active_listings']); ?></div>
                <div class="hph-stat-label">Active Listings</div>
            </div>
        </div>
        
        <div class="stat-card stat-warning">
            <div class="hph-stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="hph-stat-content">
                <div class="hph-stat-value"><?php echo esc_html($stats['pending_listings']); ?></div>
                <div class="hph-stat-label">Pending</div>
            </div>
        </div>
        
        <div class="stat-card stat-success">
            <div class="hph-stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="hph-stat-content">
                <div class="hph-stat-value"><?php echo esc_html($stats['sold_listings']); ?></div>
                <div class="hph-stat-label">Sold</div>
            </div>
        </div>
        
        <div class="stat-card stat-info">
            <div class="hph-stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="hph-stat-content">
                <div class="hph-stat-value"><?php echo esc_html($stats['total_leads']); ?></div>
                <div class="hph-stat-label">Total Leads</div>
            </div>
        </div>
    </div>
    
    <!-- Filters and Search -->
    <div class="hph-listings-filters">
        <div class="filter-controls">
            <div class="search-group">
                <div class="input-group">
                    <input type="search" 
                           id="listingSearch" 
                           class="hph-form-control" 
                           placeholder="Search listings..."
                           autocomplete="off">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="hph-filter-group">
                <select id="statusFilter" class="hph-form-control">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="sold">Sold</option>
                    <option value="draft">Draft</option>
                </select>
                
                <select id="priceFilter" class="hph-form-control">
                    <option value="">All Prices</option>
                    <option value="0-200000">Under $200K</option>
                    <option value="200000-400000">$200K - $400K</option>
                    <option value="400000-600000">$400K - $600K</option>
                    <option value="600000-800000">$600K - $800K</option>
                    <option value="800000-">$800K+</option>
                </select>
                
                <button type="button" class="btn btn-outline-secondary" id="clearFilters">
                    <i class="fas fa-times"></i>
                    Clear
                </button>
            </div>
            
            <div class="bulk-actions">
                <select id="bulkAction" class="hph-form-control">
                    <option value="">Bulk Actions</option>
                    <option value="activate">Mark Active</option>
                    <option value="deactivate">Mark Inactive</option>
                    <option value="delete">Delete Selected</option>
                </select>
                <button type="button" class="btn btn-outline-primary" id="applyBulkAction" disabled>
                    Apply
                </button>
            </div>
        </div>
    </div>
    
    <!-- Listings Content -->
    <div class="listings-container" id="listingsContainer">
        
        <!-- Loading State -->
        <div class="listings-loading" id="listingsLoading" style="display: none;">
            <div class="loading-grid">
                <?php for ($i = 0; $i < 6; $i++) : ?>
                    <div class="listing-skeleton">
                        <div class="skeleton-image"></div>
                        <div class="skeleton-content">
                            <div class="skeleton-line skeleton-title"></div>
                            <div class="skeleton-line skeleton-price"></div>
                            <div class="skeleton-line skeleton-address"></div>
                            <div class="skeleton-features">
                                <div class="skeleton-line skeleton-feature"></div>
                                <div class="skeleton-line skeleton-feature"></div>
                                <div class="skeleton-line skeleton-feature"></div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!-- Listings Grid -->
        <div class="listings-grid" id="listingsGrid">
            <?php if ($listings_query->have_posts()) : ?>
                <?php while ($listings_query->have_posts()) : $listings_query->the_post(); ?>
                    <?php
                    // Use the HPH framework's component loader for consistent rendering
                    $template_loader = HPH_Template_Loader::get_instance();
                    if ($template_loader && $template_loader->component_exists('listing-card')) {
                        echo $template_loader->load_component('listing-card', array(
                            'listing_id' => get_the_ID(),
                            'display_mode' => 'dashboard',
                            'show_actions' => true,
                            'show_stats' => true
                        ));
                    } else {
                        // Fallback to manual rendering if component system fails
                        $listing_id = get_the_ID();
                    
                    // Comprehensive data collection with null safety
                    $listing_title = get_the_title() ?: 'Untitled Property';
                    
                    // Price handling with multiple fallbacks
                    $price_raw = null;
                    if (function_exists('hpt_get_listing_price')) {
                        $price_raw = hpt_get_listing_price($listing_id);
                    }
                    if (!$price_raw && function_exists('get_field')) {
                        $price_raw = get_field('price', $listing_id) ?: get_field('list_price', $listing_id) ?: get_field('listing_price', $listing_id);
                    }
                    $formatted_price = $price_raw && is_numeric($price_raw) ? '$' . number_format($price_raw) : 'Contact for Price';
                    
                    // Address handling with multiple field checks
                    $address_data = array();
                    if (function_exists('get_field')) {
                        $address_data = array(
                            'street' => get_field('street_address', $listing_id) ?: get_field('address', $listing_id) ?: get_field('street', $listing_id),
                            'city' => get_field('city', $listing_id) ?: get_field('listing_city', $listing_id),
                            'state' => get_field('state', $listing_id) ?: get_field('listing_state', $listing_id),
                            'zip' => get_field('zip_code', $listing_id) ?: get_field('zip', $listing_id) ?: get_field('postal_code', $listing_id)
                        );
                    }
                    
                    // Extract location from title if no address fields found
                    $address_string = '';
                    $address_parts = array_filter($address_data);
                    if (!empty($address_parts)) {
                        $address_string = implode(', ', $address_parts);
                    } else {
                        // Try to extract from title like "123 Main St - Austin, TX"
                        $title_parts = explode(' - ', $listing_title);
                        if (count($title_parts) >= 2) {
                            $address_string = trim($title_parts[1]);
                            if (empty($address_data['street']) && isset($title_parts[0])) {
                                $address_data['street'] = trim($title_parts[0]);
                            }
                        }
                    }
                    
                    // If still no address, provide fallback
                    if (empty($address_string)) {
                        $address_string = 'Address Available Upon Request';
                    }
                    
                    // Features with extensive fallbacks
                    $features = array();
                    if (function_exists('get_field')) {
                        $features = array(
                            'bedrooms' => get_field('bedrooms', $listing_id) ?: get_field('beds', $listing_id) ?: get_field('num_bedrooms', $listing_id),
                            'bathrooms' => get_field('full_bathrooms', $listing_id) ?: get_field('bathrooms', $listing_id) ?: get_field('baths', $listing_id) ?: get_field('num_bathrooms', $listing_id),
                            'square_feet' => get_field('square_feet', $listing_id) ?: get_field('sqft', $listing_id) ?: get_field('size', $listing_id) ?: get_field('living_area', $listing_id)
                        );
                    }
                    
                    // Status with fallback
                    $status = 'active'; // default
                    if (function_exists('get_field')) {
                        $status = get_field('listing_status', $listing_id) ?: get_field('status', $listing_id) ?: get_field('property_status', $listing_id) ?: 'active';
                    }
                    
                    // Featured status
                    $featured = false;
                    if (function_exists('get_field')) {
                        $featured = get_field('featured_listing', $listing_id) ?: get_field('featured', $listing_id) ?: get_field('is_featured', $listing_id) ?: false;
                    }
                    
                    // Ensure featured is boolean
                    $featured = (bool) $featured;
                    
                    // Post meta fallbacks for missing ACF data
                    if (!$price_raw) {
                        $price_raw = get_post_meta($listing_id, '_price', true) ?: get_post_meta($listing_id, 'price', true);
                        if ($price_raw && is_numeric($price_raw)) {
                            $formatted_price = '$' . number_format($price_raw);
                        }
                    }
                    ?>
                    
                    <div class="listing-card" 
                         data-listing-id="<?php echo esc_attr($listing_id); ?>"
                         data-status="<?php echo esc_attr($status); ?>"
                         data-price="<?php echo esc_attr($price ?: 0); ?>"
                         data-featured="<?php echo $featured ? 'true' : 'false'; ?>">
                        
                        <!-- Selection Checkbox -->
                        <div class="listing-checkbox">
                            <input type="checkbox" 
                                   class="listing-select" 
                                   value="<?php echo esc_attr($listing_id); ?>"
                                   data-listing-id="<?php echo esc_attr($listing_id); ?>">
                        </div>
                        
                        <!-- Listing Image -->
                        <div class="listing-image">
                            <a href="<?php the_permalink(); ?>" target="_blank">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('listing-card', array('class' => 'listing-photo')); ?>
                                <?php else : ?>
                                    <div class="no-image-placeholder">
                                        <i class="fas fa-home"></i>
                                        <span>No Image</span>
                                    </div>
                                <?php endif; ?>
                            </a>
                            
                            <!-- Status Badge -->
                            <div class="listing-status status-<?php echo esc_attr($status); ?>">
                                <i class="fas fa-<?php echo $status === 'active' ? 'check' : ($status === 'pending' ? 'clock' : ($status === 'sold' ? 'handshake' : 'edit')); ?>"></i>
                                <?php echo esc_html(ucfirst($status)); ?>
                            </div>
                            
                            <!-- Featured Badge -->
                            <?php if ($featured) : ?>
                                <div class="featured-badge">
                                    <i class="fas fa-star"></i>
                                    Featured
                                </div>
                            <?php endif; ?>
                            
                            <!-- Quick Actions Overlay -->
                            <div class="listing-overlay">
                                <div class="overlay-actions">
                                    <button type="button" 
                                            class="overlay-btn btn-edit" 
                                            onclick="editListing(<?php echo esc_js($listing_id); ?>)"
                                            title="Edit Listing">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" 
                                            class="overlay-btn btn-view" 
                                            onclick="window.open('<?php the_permalink(); ?>', '_blank')"
                                            title="View Listing">
                                        <i class="fas fa-external-link-alt"></i>
                                    </button>
                                    <button type="button" 
                                            class="overlay-btn btn-duplicate" 
                                            onclick="duplicateListing(<?php echo esc_js($listing_id); ?>)"
                                            title="Duplicate Listing">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Listing Content -->
                        <div class="listing-content">
                            
                            <!-- Price -->
                            <div class="listing-price">
                                <?php echo esc_html($formatted_price ?: 'Contact for Price'); ?>
                            </div>
                            
                            <!-- Title -->
                            <h3 class="listing-title">
                                <a href="<?php the_permalink(); ?>" target="_blank">
                                    <?php echo esc_html($listing_title); ?>
                                </a>
                            </h3>
                            
                            <!-- Address - Always show with fallback -->
                            <div class="listing-address">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo esc_html($address_string); ?></span>
                            </div>
                            
                            <!-- Features - Always show with fallbacks -->
                            <div class="listing-features">
                                <div class="feature-item">
                                    <i class="fas fa-bed"></i>
                                    <span>
                                        <?php 
                                        if (!empty($features['bedrooms']) && is_numeric($features['bedrooms'])) {
                                            echo esc_html($features['bedrooms']) . ' bed' . ($features['bedrooms'] > 1 ? 's' : '');
                                        } else {
                                            echo 'Beds: TBD';
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="feature-item">
                                    <i class="fas fa-bath"></i>
                                    <span>
                                        <?php 
                                        if (!empty($features['bathrooms']) && is_numeric($features['bathrooms'])) {
                                            echo esc_html($features['bathrooms']) . ' bath' . ($features['bathrooms'] > 1 ? 's' : '');
                                        } else {
                                            echo 'Baths: TBD';
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="feature-item">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>
                                        <?php 
                                        if (!empty($features['square_feet']) && is_numeric($features['square_feet'])) {
                                            echo esc_html(number_format($features['square_feet'])) . ' sqft';
                                        } else {
                                            echo 'Size: TBD';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Listing Meta -->
                            <div class="listing-meta">
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo get_the_date('M j, Y'); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-eye"></i>
                                    <span id="views-<?php echo esc_attr($listing_id); ?>">-</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-heart"></i>
                                    <span id="leads-<?php echo esc_attr($listing_id); ?>">-</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="listing-actions">
                            <button type="button" 
                                    class="action-btn btn-primary" 
                                    onclick="editListing(<?php echo esc_js($listing_id); ?>)"
                                    title="Edit Listing">
                                <i class="fas fa-edit"></i>
                                Edit
                            </button>
                            
                            <div class="action-dropdown">
                                <button type="button" class="action-btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" onclick="scheduleOpenHouse(<?php echo esc_js($listing_id); ?>)">
                                        <i class="fas fa-calendar-plus"></i> Schedule Open House
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="createLead(<?php echo esc_js($listing_id); ?>)">
                                        <i class="fas fa-user-plus"></i> Add Lead
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="generateFlyer(<?php echo esc_js($listing_id); ?>)">
                                        <i class="fas fa-file-pdf"></i> Generate Flyer
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#" onclick="toggleFeatured(<?php echo esc_js($listing_id); ?>)">
                                        <i class="fas fa-star"></i> <?php echo $featured ? 'Remove Featured' : 'Mark Featured'; ?>
                                    </a>
                                    <a class="dropdown-item text-danger" href="#" onclick="deleteListing(<?php echo esc_js($listing_id); ?>)">
                                        <i class="fas fa-trash"></i> Delete Listing
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    } // End fallback rendering
                    ?>
                    
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <!-- Empty State -->
                <div class="empty-state" id="emptyState">
                    <div class="empty-state-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="empty-state-title">No listings found</h3>
                    <p class="empty-state-description">
                        <?php if (isset($query_args['meta_query']) || isset($query_args['author'])) : ?>
                            You haven't created any listings yet. Get started by adding your first property!
                        <?php else : ?>
                            No listings match your current filters. Try adjusting your search criteria.
                        <?php endif; ?>
                    </p>
                    <button type="button" class="btn btn-primary btn-lg" onclick="openListingModal()">
                        <i class="fas fa-plus"></i>
                        Add Your First Listing
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($listings_query->max_num_pages > 1) : ?>
            <div class="listings-pagination">
                <div class="pagination-info">
                    Showing <?php echo esc_html($listings_query->post_count); ?> of <?php echo esc_html($listings_query->found_posts); ?> listings
                </div>
                <div class="pagination-controls">
                    <?php
                    $pagination_args = array(
                        'total' => $listings_query->max_num_pages,
                        'current' => max(1, get_query_var('paged')),
                        'prev_text' => '<i class="fas fa-chevron-left"></i> Previous',
                        'next_text' => 'Next <i class="fas fa-chevron-right"></i>',
                        'type' => 'array'
                    );
                    
                    $pagination_links = paginate_links($pagination_args);
                    
                    if ($pagination_links) {
                        echo '<nav class="pagination-nav">';
                        echo '<ul class="pagination">';
                        foreach ($pagination_links as $link) {
                            echo '<li class="page-item">' . $link . '</li>';
                        }
                        echo '</ul>';
                        echo '</nav>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Load More Button (Alternative to pagination) -->
    <div class="load-more-container" id="loadMoreContainer" style="display: none;">
        <button type="button" class="btn btn-outline-primary btn-lg" id="loadMoreListings" data-page="2">
            <i class="fas fa-plus"></i>
            Load More Listings
        </button>
    </div>
    
</div>

<!-- Include Modal Templates -->
<?php
// Load modals using template loader
$modal_templates = array(
    'listing-form-modal',
    'open-house-modal', 
    'lead-modal',
    'transaction-modal'
);

foreach ($modal_templates as $modal_template) {
    $modal_path = get_template_directory() . '/templates/dashboard/' . $modal_template . '.php';
    if (file_exists($modal_path)) {
        include $modal_path;
    }
}
?>

<script>
/**
 * Dashboard Listings JavaScript
 * Handles all listing management functionality
 */
(function($) {
    'use strict';
    
    // Configuration
    const DashboardListings = {
        ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('hph_dashboard_nonce'); ?>',
        currentView: 'grid',
        filters: {
            search: '',
            status: '',
            price: ''
        },
        currentPage: 1,
        loading: false
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        initializeListings();
        bindEvents();
        loadListingStats();
    });
    
    /**
     * Initialize listings functionality
     */
    function initializeListings() {
        // Initialize tooltips
        if ($.fn.tooltip) {
            $('[title]').tooltip();
        }
        
        // Initialize dropdowns
        if ($.fn.dropdown) {
            $('.dropdown-toggle').dropdown();
        }
        
        // Load initial listing data
        refreshListingData();
    }
    
    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Search functionality
        $('#listingSearch').on('input', debounce(handleSearch, 300));
        
        // Filter changes
        $('#statusFilter, #priceFilter').on('change', handleFilterChange);
        
        // Clear filters
        $('#clearFilters').on('click', clearAllFilters);
        
        // Bulk actions
        $('.listing-select').on('change', handleBulkSelection);
        $('#applyBulkAction').on('click', handleBulkAction);
        
        // View toggle
        $('#viewToggle').on('click', toggleListingView);
        
        // Load more
        $('#loadMoreListings').on('click', loadMoreListings);
        
        // Keyboard shortcuts
        $(document).on('keydown', handleKeyboardShortcuts);
    }
    
    /**
     * Handle search input
     */
    function handleSearch() {
        DashboardListings.filters.search = $(this).val();
        DashboardListings.currentPage = 1;
        refreshListings();
    }
    
    /**
     * Handle filter changes
     */
    function handleFilterChange() {
        DashboardListings.filters.status = $('#statusFilter').val();
        DashboardListings.filters.price = $('#priceFilter').val();
        DashboardListings.currentPage = 1;
        refreshListings();
    }
    
    /**
     * Clear all filters
     */
    function clearAllFilters() {
        $('#listingSearch, #statusFilter, #priceFilter').val('');
        DashboardListings.filters = { search: '', status: '', price: '' };
        DashboardListings.currentPage = 1;
        refreshListings();
    }
    
    /**
     * Handle bulk selection
     */
    function handleBulkSelection() {
        const selectedCount = $('.listing-select:checked').length;
        $('#applyBulkAction').prop('disabled', selectedCount === 0);
        
        // Update bulk action button text
        if (selectedCount > 0) {
            $('#applyBulkAction').text(`Apply (${selectedCount})`);
        } else {
            $('#applyBulkAction').text('Apply');
        }
    }
    
    /**
     * Refresh listings with current filters
     */
    function refreshListings() {
        if (DashboardListings.loading) return;
        
        DashboardListings.loading = true;
        showLoadingState();
        
        const ajaxData = {
            action: 'hph_load_dashboard_listings',
            nonce: DashboardListings.nonce,
            filters: DashboardListings.filters,
            page: DashboardListings.currentPage,
            view: DashboardListings.currentView
        };
        
        $.post(DashboardListings.ajaxUrl, ajaxData)
            .done(function(response) {
                if (response.success) {
                    updateListingsGrid(response.data.html);
                    updatePagination(response.data.pagination);
                } else {
                    showNotification(response.data || 'Failed to load listings', 'error');
                }
            })
            .fail(function() {
                showNotification('Network error occurred', 'error');
            })
            .always(function() {
                DashboardListings.loading = false;
                hideLoadingState();
            });
    }
    
    /**
     * Load listing statistics
     */
    function loadListingStats() {
        $('.listing-card').each(function() {
            const listingId = $(this).data('listing-id');
            loadListingMetrics(listingId);
        });
    }
    
    /**
     * Load individual listing metrics
     */
    function loadListingMetrics(listingId) {
        const ajaxData = {
            action: 'hph_get_listing_metrics',
            nonce: DashboardListings.nonce,
            listing_id: listingId
        };
        
        $.post(DashboardListings.ajaxUrl, ajaxData)
            .done(function(response) {
                if (response.success) {
                    const metrics = response.data;
                    $(`#views-${listingId}`).text(metrics.views || 0);
                    $(`#leads-${listingId}`).text(metrics.leads || 0);
                }
            });
    }
    
    /**
     * Show loading state
     */
    function showLoadingState() {
        $('#listingsGrid').hide();
        $('#listingsLoading').show();
    }
    
    /**
     * Hide loading state
     */
    function hideLoadingState() {
        $('#listingsLoading').hide();
        $('#listingsGrid').show();
    }
    
    /**
     * Update listings grid with new content
     */
    function updateListingsGrid(html) {
        $('#listingsGrid').html(html);
        
        // Reinitialize tooltips and dropdowns
        if ($.fn.tooltip) {
            $('[title]').tooltip();
        }
        if ($.fn.dropdown) {
            $('.dropdown-toggle').dropdown();
        }
        
        // Rebind bulk selection handlers
        $('.listing-select').on('change', handleBulkSelection);
        
        // Refresh listing stats
        loadListingStats();
    }
    
    /**
     * Show notification message
     */
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        $('.hph-notification').remove();
        
        // Create notification
        const notification = $(`
            <div class="hph-notification hph-notification-${type}">
                <div class="notification-content">
                    <span class="notification-message">${message}</span>
                    <button type="button" class="notification-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `);
        
        // Add to page
        $('body').append(notification);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
        
        // Close button handler
        notification.find('.notification-close').on('click', function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        });
    }
    
    /**
     * Utility function for debouncing
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Expose functions globally for inline onclick handlers
    window.editListing = function(listingId) {
        window.location.href = `/wp-admin/post.php?post=${listingId}&action=edit`;
    };
    
    window.deleteListing = function(listingId) {
        if (!confirm('Are you sure you want to delete this listing? This action cannot be undone.')) {
            return;
        }
        
        const ajaxData = {
            action: 'hph_delete_listing',
            nonce: DashboardListings.nonce,
            listing_id: listingId
        };
        
        $.post(DashboardListings.ajaxUrl, ajaxData)
            .done(function(response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    $(`[data-listing-id="${listingId}"]`).fadeOut(function() {
                        $(this).remove();
                    });
                    refreshListingData();
                } else {
                    showNotification(response.data || 'Failed to delete listing', 'error');
                }
            })
            .fail(function() {
                showNotification('Network error occurred', 'error');
            });
    };
    
    window.duplicateListing = function(listingId) {
        const ajaxData = {
            action: 'hph_duplicate_listing',
            nonce: DashboardListings.nonce,
            listing_id: listingId
        };
        
        showNotification('Duplicating listing...', 'info');
        
        $.post(DashboardListings.ajaxUrl, ajaxData)
            .done(function(response) {
                if (response.success) {
                    showNotification('Listing duplicated successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(response.data || 'Failed to duplicate listing', 'error');
                }
            })
            .fail(function() {
                showNotification('Network error occurred', 'error');
            });
    };
    
    window.toggleFeatured = function(listingId) {
        const ajaxData = {
            action: 'hph_toggle_featured_listing',
            nonce: DashboardListings.nonce,
            listing_id: listingId
        };
        
        $.post(DashboardListings.ajaxUrl, ajaxData)
            .done(function(response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    const card = $(`[data-listing-id="${listingId}"]`);
                    if (response.data.featured) {
                        card.attr('data-featured', 'true');
                        if (card.find('.featured-badge').length === 0) {
                            card.find('.listing-image').append('<div class="featured-badge"><i class="fas fa-star"></i> Featured</div>');
                        }
                    } else {
                        card.attr('data-featured', 'false');
                        card.find('.featured-badge').remove();
                    }
                } else {
                    showNotification(response.data || 'Failed to update listing', 'error');
                }
            })
            .fail(function() {
                showNotification('Network error occurred', 'error');
            });
    };
    
    window.scheduleOpenHouse = function(listingId) {
        // Open modal or redirect to scheduling interface
        if (typeof openModal === 'function') {
            openModal('openHouseModal', listingId);
        } else {
            window.location.href = `/wp-admin/post-new.php?post_type=open_house&listing_id=${listingId}`;
        }
    };
    
    window.createLead = function(listingId) {
        // Open modal or redirect to lead creation interface
        if (typeof openModal === 'function') {
            openModal('leadModal', listingId);
        } else {
            window.location.href = `/wp-admin/post-new.php?post_type=lead&listing_id=${listingId}`;
        }
    };
    
    window.generateFlyer = function(listingId) {
        const ajaxData = {
            action: 'hph_generate_listing_flyer',
            nonce: DashboardListings.nonce,
            listing_id: listingId
        };
        
        showNotification('Generating flyer...', 'info');
        
        $.post(DashboardListings.ajaxUrl, ajaxData)
            .done(function(response) {
                if (response.success) {
                    showNotification('Flyer generated successfully!', 'success');
                    if (response.data.download_url) {
                        window.open(response.data.download_url, '_blank');
                    }
                } else {
                    showNotification(response.data || 'Failed to generate flyer', 'error');
                }
            })
            .fail(function() {
                showNotification('Network error occurred', 'error');
            });
    };
    
    window.openListingModal = function() {
        if (typeof openModal === 'function') {
            openModal('listingModal');
        } else {
            window.location.href = '/wp-admin/post-new.php?post_type=listing';
        }
    };
    
    window.toggleListingView = function() {
        DashboardListings.currentView = DashboardListings.currentView === 'grid' ? 'list' : 'grid';
        
        const icon = $('#viewToggleIcon');
        const text = $('#viewToggleText');
        
        if (DashboardListings.currentView === 'grid') {
            icon.removeClass('fa-list').addClass('fa-th');
            text.text('Grid View');
            $('#listingsGrid').removeClass('hph-list-view').addClass('hph-grid-view');
        } else {
            icon.removeClass('fa-th').addClass('fa-list');
            text.text('List View');
            $('#listingsGrid').removeClass('hph-grid-view').addClass('hph-list-view');
        }
    };
    
    /**
     * Refresh listing data and stats
     */
    function refreshListingData() {
        // Reload stats
        const statsData = {
            action: 'hph_get_dashboard_stats',
            nonce: DashboardListings.nonce
        };
        
        $.post(DashboardListings.ajaxUrl, statsData)
            .done(function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('.stat-card .hph-stat-value').each(function() {
                        const card = $(this).closest('.stat-card');
                        if (card.hasClass('stat-primary')) $(this).text(stats.active_listings || 0);
                        else if (card.hasClass('stat-warning')) $(this).text(stats.pending_listings || 0);
                        else if (card.hasClass('stat-success')) $(this).text(stats.sold_listings || 0);
                        else if (card.hasClass('stat-info')) $(this).text(stats.total_leads || 0);
                    });
                }
            });
    }
    
})(jQuery);
</script>
