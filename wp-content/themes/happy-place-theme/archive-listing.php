<?php
/**
 * Enhanced Simple Listing Archive Template
 * Using theme's CSS variables and utility classes properly
 * 
 * @package HappyPlaceTheme
 */

get_header();

// Get filter parameters from URL
$search_query = sanitize_text_field($_GET['s'] ?? '');
$property_type = sanitize_text_field($_GET['property_type'] ?? '');
$min_price = intval($_GET['min_price'] ?? 0);
$max_price = intval($_GET['max_price'] ?? 0);
$bedrooms = intval($_GET['bedrooms'] ?? 0);
$bathrooms = intval($_GET['bathrooms'] ?? 0);
$status = sanitize_text_field($_GET['status'] ?? '');
$feature = sanitize_text_field($_GET['feature'] ?? '');
$view = sanitize_text_field($_GET['view'] ?? 'grid');
$sort = sanitize_text_field($_GET['sort'] ?? 'date_desc');

// Build query args
$args = [
    'post_type' => 'listing',
    'post_status' => 'publish',
    'posts_per_page' => 12,
    'paged' => get_query_var('paged') ?: 1,
    'meta_query' => ['relation' => 'AND']
];

// Add search
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

// Add property type filter
if (!empty($property_type)) {
    $args['meta_query'][] = [
        'key' => 'property_type',
        'value' => $property_type,
        'compare' => '='
    ];
}

// Add price filters
if ($min_price > 0) {
    $args['meta_query'][] = [
        'key' => 'price',
        'value' => $min_price,
        'compare' => '>=',
        'type' => 'NUMERIC'
    ];
}
if ($max_price > 0) {
    $args['meta_query'][] = [
        'key' => 'price',
        'value' => $max_price,
        'compare' => '<=',
        'type' => 'NUMERIC'
    ];
}

// Add bedrooms filter
if ($bedrooms > 0) {
    $args['meta_query'][] = [
        'key' => 'bedrooms',
        'value' => $bedrooms,
        'compare' => '>=',
        'type' => 'NUMERIC'
    ];
}

// Add bathrooms filter
if ($bathrooms > 0) {
    $args['meta_query'][] = [
        'key' => 'bathrooms_full',
        'value' => $bathrooms,
        'compare' => '>=',
        'type' => 'NUMERIC'
    ];
}

// Add status filter
if (!empty($status) && $status !== 'all') {
    $args['meta_query'][] = [
        'key' => 'listing_status',
        'value' => $status,
        'compare' => '='
    ];
}

// Add feature filter
if (!empty($feature)) {
    $args['meta_query'][] = [
        'key' => $feature,
        'value' => '1',
        'compare' => '='
    ];
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $args['meta_key'] = 'price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'ASC';
        break;
    case 'price_desc':
        $args['meta_key'] = 'price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'beds_desc':
        $args['meta_key'] = 'bedrooms';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'newest':
    default:
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
}

// Execute query
$listings_query = new WP_Query($args);

// Helper function to build URL with params
function build_filter_url($additional_params = []) {
    $base_url = get_post_type_archive_link('listing');
    $current_params = $_GET;
    $params = array_merge($current_params, $additional_params);
    
    // Remove empty params
    $params = array_filter($params, function($value) {
        return $value !== '' && $value !== '0' && $value !== 0;
    });
    
    if (!empty($params)) {
        return $base_url . '?' . http_build_query($params);
    }
    return $base_url;
}
?>

<!-- Hero Section -->
<section class="hph-bg-gradient-primary hph-py-xl hph-mb-lg">
    <div class="hph-container">
        <div class="hph-max-w-3xl hph-mx-auto hph-text-center hph-text-white">
            <h1 class="hph-text-4xl hph-font-bold hph-mb-md">Find Your Perfect Home</h1>
            <p class="hph-text-lg hph-opacity-90">
                <?php printf('%d listings available', $listings_query->found_posts); ?>
            </p>
        </div>
    </div>
</section>

<div class="hph-container hph-py-lg">
    
    <!-- Search & Filters Card -->
    <div class="hph-bg-white hph-rounded-xl hph-shadow-md hph-p-lg hph-mb-lg">
        <form method="get" id="filter-form">
            <!-- Hidden fields for view and sort -->
            <input type="hidden" name="view" value="<?php echo esc_attr($view); ?>">
            <input type="hidden" name="sort" value="<?php echo esc_attr($sort); ?>">
            
            <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-4 hph-gap-md hph-mb-md">
                
                <!-- Search Field -->
                <div class="md:hph-col-span-2">
                    <label class="hph-block hph-mb-xs hph-font-medium hph-text-gray-700">Search</label>
                    <input type="text" name="s" value="<?php echo esc_attr($search_query); ?>" 
                           placeholder="City, address, or MLS#"
                           class="hph-w-full hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg focus:hph-border-primary focus:hph-outline-none">
                </div>
                
                <!-- Property Type -->
                <div>
                    <label class="hph-block hph-mb-xs hph-font-medium hph-text-gray-700">Property Type</label>
                    <select name="property_type" class="hph-w-full hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg focus:hph-border-primary focus:hph-outline-none">
                        <option value="">All Types</option>
                        <option value="single-family" <?php selected($property_type, 'single-family'); ?>>Single Family</option>
                        <option value="condo" <?php selected($property_type, 'condo'); ?>>Condo</option>
                        <option value="townhome" <?php selected($property_type, 'townhome'); ?>>Townhome</option>
                        <option value="land" <?php selected($property_type, 'land'); ?>>Land</option>
                    </select>
                </div>
                
                <!-- Status -->
                <div>
                    <label class="hph-block hph-mb-xs hph-font-medium hph-text-gray-700">Status</label>
                    <select name="status" class="hph-w-full hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg focus:hph-border-primary focus:hph-outline-none">
                        <option value="">All Status</option>
                        <option value="active" <?php selected($status, 'active'); ?>>Active</option>
                        <option value="pending" <?php selected($status, 'pending'); ?>>Pending</option>
                        <option value="sold" <?php selected($status, 'sold'); ?>>Sold</option>
                    </select>
                </div>
                
                <!-- Bedrooms -->
                <div>
                    <label class="hph-block hph-mb-xs hph-font-medium hph-text-gray-700">Bedrooms</label>
                    <select name="bedrooms" class="hph-w-full hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg focus:hph-border-primary focus:hph-outline-none">
                        <option value="0">Any</option>
                        <option value="1" <?php selected($bedrooms, 1); ?>>1+</option>
                        <option value="2" <?php selected($bedrooms, 2); ?>>2+</option>
                        <option value="3" <?php selected($bedrooms, 3); ?>>3+</option>
                        <option value="4" <?php selected($bedrooms, 4); ?>>4+</option>
                    </select>
                </div>
                
                <!-- Min Price -->
                <div>
                    <label class="hph-block hph-mb-xs hph-font-medium hph-text-gray-700">Min Price</label>
                    <select name="min_price" class="hph-w-full hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg focus:hph-border-primary focus:hph-outline-none">
                        <option value="0">No Min</option>
                        <option value="100000" <?php selected($min_price, 100000); ?>>$100,000</option>
                        <option value="200000" <?php selected($min_price, 200000); ?>>$200,000</option>
                        <option value="300000" <?php selected($min_price, 300000); ?>>$300,000</option>
                        <option value="500000" <?php selected($min_price, 500000); ?>>$500,000</option>
                    </select>
                </div>
                
                <!-- Max Price -->
                <div>
                    <label class="hph-block hph-mb-xs hph-font-medium hph-text-gray-700">Max Price</label>
                    <select name="max_price" class="hph-w-full hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg focus:hph-border-primary focus:hph-outline-none">
                        <option value="0">No Max</option>
                        <option value="300000" <?php selected($max_price, 300000); ?>>$300,000</option>
                        <option value="500000" <?php selected($max_price, 500000); ?>>$500,000</option>
                        <option value="750000" <?php selected($max_price, 750000); ?>>$750,000</option>
                        <option value="1000000" <?php selected($max_price, 1000000); ?>>$1,000,000</option>
                    </select>
                </div>
                
                <!-- Submit Button -->
                <div class="hph-flex hph-items-end">
                    <button type="submit" class="hph-btn hph-btn-primary hph-w-full">
                        <i class="fas fa-search hph-mr-sm"></i> Search
                    </button>
                </div>
            </div>
            
            <!-- Quick Filters -->
            <div class="hph-flex hph-flex-wrap hph-gap-sm hph-pt-md hph-border-t">
                <span class="hph-font-medium hph-text-gray-700 hph-mr-sm">Quick Filters:</span>
                <a href="<?php echo build_filter_url(['feature' => 'waterfront']); ?>" 
                   class="hph-px-md hph-py-xs hph-rounded-full hph-border-2 <?php echo $feature === 'waterfront' ? 'hph-bg-primary hph-text-white hph-border-primary' : 'hph-border-gray-300 hph-text-gray-700 hover:hph-border-primary'; ?>">
                    <i class="fas fa-water"></i> Waterfront
                </a>
                <a href="<?php echo build_filter_url(['feature' => 'pool']); ?>"
                   class="hph-px-md hph-py-xs hph-rounded-full hph-border-2 <?php echo $feature === 'pool' ? 'hph-bg-primary hph-text-white hph-border-primary' : 'hph-border-gray-300 hph-text-gray-700 hover:hph-border-primary'; ?>">
                    <i class="fas fa-swimming-pool"></i> Pool
                </a>
                <a href="<?php echo build_filter_url(['feature' => 'garage']); ?>"
                   class="hph-px-md hph-py-xs hph-rounded-full hph-border-2 <?php echo $feature === 'garage' ? 'hph-bg-primary hph-text-white hph-border-primary' : 'hph-border-gray-300 hph-text-gray-700 hover:hph-border-primary'; ?>">
                    <i class="fas fa-warehouse"></i> Garage
                </a>
                <?php if($search_query || $property_type || $min_price || $max_price || $bedrooms || $status || $feature): ?>
                    <a href="<?php echo get_post_type_archive_link('listing'); ?>" class="hph-px-md hph-py-xs hph-rounded-full hph-border-2 hph-border-danger hph-text-danger hover:hph-bg-danger hover:hph-text-white hph-ml-auto">
                        <i class="fas fa-times"></i> Clear All
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Controls Bar -->
    <div class="hph-bg-white hph-rounded-lg hph-shadow-sm hph-p-md hph-mb-lg">
        <div class="hph-flex hph-justify-between hph-items-center hph-flex-wrap hph-gap-md">
            
            <!-- Results Count -->
            <div class="hph-text-gray-600">
                <span class="hph-font-semibold hph-text-gray-900"><?php echo $listings_query->found_posts; ?></span> 
                listings found
            </div>
            
            <!-- View & Sort Controls -->
            <div class="hph-flex hph-items-center hph-gap-md">
                
                <!-- View Switcher -->
                <div class="hph-btn-group">
                    <a href="<?php echo build_filter_url(['view' => 'grid']); ?>" 
                       class="hph-btn-sm <?php echo $view === 'grid' ? 'hph-btn-primary' : 'hph-btn-outline'; ?>">
                        <i class="fas fa-th"></i> Grid
                    </a>
                    <a href="<?php echo build_filter_url(['view' => 'list']); ?>" 
                       class="hph-btn-sm <?php echo $view === 'list' ? 'hph-btn-primary' : 'hph-btn-outline'; ?>">
                        <i class="fas fa-list"></i> List
                    </a>
                </div>
                
                <!-- Sort Dropdown -->
                <select onchange="window.location.href=this.value" class="hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg">
                    <option value="<?php echo build_filter_url(['sort' => 'newest']); ?>" <?php selected($sort, 'newest'); ?>>Newest First</option>
                    <option value="<?php echo build_filter_url(['sort' => 'price_asc']); ?>" <?php selected($sort, 'price_asc'); ?>>Price: Low to High</option>
                    <option value="<?php echo build_filter_url(['sort' => 'price_desc']); ?>" <?php selected($sort, 'price_desc'); ?>>Price: High to Low</option>
                    <option value="<?php echo build_filter_url(['sort' => 'beds_desc']); ?>" <?php selected($sort, 'beds_desc'); ?>>Most Bedrooms</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Listings -->
    <?php if ($listings_query->have_posts()): ?>
        
        <!-- Grid/List Container -->
        <div class="<?php echo $view === 'list' ? 'hph-space-y-md' : 'hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 hph-gap-lg'; ?>">
            <?php while ($listings_query->have_posts()): $listings_query->the_post(); ?>
                <?php 
                // Use the simple card template
                get_template_part('template-parts/listing-card', $view, ['listing_id' => get_the_ID()]); 
                ?>
            <?php endwhile; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($listings_query->max_num_pages > 1): ?>
            <nav class="hph-mt-xl hph-flex hph-justify-center">
                <div class="hph-pagination">
                    <?php
                    echo paginate_links([
                        'total' => $listings_query->max_num_pages,
                        'current' => max(1, get_query_var('paged')),
                        'format' => '?paged=%#%',
                        'base' => build_filter_url() . '%_%',
                        'add_args' => false,
                        'prev_text' => '<i class="fas fa-chevron-left"></i>',
                        'next_text' => '<i class="fas fa-chevron-right"></i>',
                        'type' => 'list',
                        'end_size' => 2,
                        'mid_size' => 1
                    ]);
                    ?>
                </div>
            </nav>
        <?php endif; ?>
        
    <?php else: ?>
        
        <!-- No Results -->
        <div class="hph-text-center hph-py-xl">
            <div class="hph-mb-lg">
                <i class="fas fa-home hph-text-gray-300 hph-text-6xl"></i>
            </div>
            <h3 class="hph-text-2xl hph-font-semibold hph-mb-md">No Listings Found</h3>
            <p class="hph-text-gray-600 hph-mb-lg">
                Try adjusting your search criteria or removing some filters.
            </p>
            <a href="<?php echo get_post_type_archive_link('listing'); ?>" class="hph-btn hph-btn-primary">
                View All Listings
            </a>
        </div>
        
    <?php endif; ?>
    
</div>

<!-- Simple CSS for pagination styling -->
<style>
.hph-pagination ul {
    display: flex;
    gap: var(--hph-gap-sm);
    list-style: none;
    padding: 0;
}

.hph-pagination a,
.hph-pagination .current {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 var(--hph-padding-md);
    border: 2px solid var(--hph-gray-300);
    border-radius: var(--hph-radius-md);
    color: var(--hph-gray-700);
    text-decoration: none;
    transition: var(--hph-transition-fast);
}

.hph-pagination a:hover {
    border-color: var(--hph-primary);
    color: var(--hph-primary);
}

.hph-pagination .current {
    background: var(--hph-primary);
    color: var(--hph-white);
    border-color: var(--hph-primary);
}

.hph-btn-group {
    display: inline-flex;
    border-radius: var(--hph-radius-md);
    overflow: hidden;
}

.hph-btn-group .hph-btn-sm {
    border-radius: 0;
    margin: 0;
}

.hph-btn-group .hph-btn-sm:first-child {
    border-radius: var(--hph-radius-md) 0 0 var(--hph-radius-md);
}

.hph-btn-group .hph-btn-sm:last-child {
    border-radius: 0 var(--hph-radius-md) var(--hph-radius-md) 0;
}
</style>

<?php
wp_reset_postdata();
get_footer();
?>