<?php
/**
 * Archive Template for Listings (Properties)
 * 
 * Enhanced archive template with modern section patterns
 * Supports grid, list, and map views with advanced filtering
 * Uses the new section-based architecture for consistency
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * @version 3.2.0
 */

get_header();

// Get search and filter parameters
$search_query = sanitize_text_field($_GET['s'] ?? '');
$paged = get_query_var('paged', 1);
$posts_per_page = intval($_GET['per_page'] ?? 12);
$view_mode = sanitize_text_field($_GET['view'] ?? 'grid');
$sort = sanitize_text_field($_GET['sort'] ?? 'date_desc');

// Build base query args
$query_args = [
    'post_type' => 'listing',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_status' => 'publish',
    'meta_query' => ['relation' => 'AND'],
];

// Add search query if provided
if (!empty($search_query)) {
    $query_args['s'] = $search_query;
    // Also search MLS numbers and addresses
    $query_args['meta_query'][] = [
        'relation' => 'OR',
        [
            'key' => 'mls_number',
            'value' => $search_query,
            'compare' => 'LIKE',
        ],
        [
            'key' => 'street_address', 
            'value' => $search_query,
            'compare' => 'LIKE',
        ],
    ];
}

// Filter by active listings by default
$status_filter = sanitize_text_field($_GET['status'] ?? 'active');
if (!empty($status_filter) && $status_filter !== 'all') {
    $query_args['meta_query'][] = [
        'key' => 'property_status',
        'value' => $status_filter,
        'compare' => '='
    ];
}

// Price range filter
$min_price = intval($_GET['min_price'] ?? 0);
$max_price = intval($_GET['max_price'] ?? 0);
if ($min_price > 0 || $max_price > 0) {
    $price_query = ['key' => 'listing_price', 'type' => 'NUMERIC'];
    if ($min_price > 0 && $max_price > 0) {
        $price_query['value'] = [$min_price, $max_price];
        $price_query['compare'] = 'BETWEEN';
    } elseif ($min_price > 0) {
        $price_query['value'] = $min_price;
        $price_query['compare'] = '>=';
    } else {
        $price_query['value'] = $max_price;
        $price_query['compare'] = '<=';
    }
    $query_args['meta_query'][] = $price_query;
}

// Bedroom filter
$bedrooms = intval($_GET['bedrooms'] ?? 0);
if ($bedrooms > 0) {
    $query_args['meta_query'][] = [
        'key' => 'bedrooms',
        'value' => $bedrooms,
        'compare' => '>=',
        'type' => 'NUMERIC'
    ];
}

// Bathroom filter  
$bathrooms = floatval($_GET['bathrooms'] ?? 0);
if ($bathrooms > 0) {
    $query_args['meta_query'][] = [
        'key' => 'bathrooms_full',
        'value' => $bathrooms,
        'compare' => '>=',
        'type' => 'NUMERIC'
    ];
}

// Property type filter
$property_type = sanitize_text_field($_GET['property_type'] ?? '');
if (!empty($property_type)) {
    $query_args['meta_query'][] = [
        'key' => 'property_type',
        'value' => $property_type,
        'compare' => '='
    ];
}

// Handle sorting
switch ($sort) {
    case 'price_asc':
        $query_args['meta_key'] = 'listing_price';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'ASC';
        break;
    case 'price_desc':
        $query_args['meta_key'] = 'listing_price';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'DESC';
        break;
    case 'bedrooms_desc':
        $query_args['meta_key'] = 'bedrooms';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'DESC';
        break;
    case 'sqft_desc':
        $query_args['meta_key'] = 'square_feet';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'DESC';
        break;
    default:
        $query_args['orderby'] = 'date';
        $query_args['order'] = 'DESC';
}

// Execute query
$listing_query = new WP_Query($query_args);

// Debug output - remove in production
if (WP_DEBUG) {
    error_log('Archive Listing Debug:');
    error_log('Query args: ' . print_r($query_args, true));
    error_log('Found posts: ' . $listing_query->found_posts);
    error_log('Post count: ' . $listing_query->post_count);
    error_log('Post type exists: ' . (post_type_exists('listing') ? 'Yes' : 'No'));
}

// Prepare archive title and description
$archive_title = __('Properties', 'happy-place-theme');
$archive_description = '';

if (!empty($search_query)) {
    $archive_title = sprintf(__('Properties matching "%s"', 'happy-place-theme'), $search_query);
}

if ($listing_query->found_posts > 0) {
    $archive_description = sprintf(
        _n('%d property found', '%d properties found', $listing_query->found_posts, 'happy-place-theme'),
        $listing_query->found_posts
    );
} else {
    $archive_description = __('No properties found matching your criteria', 'happy-place-theme');
}

// Convert posts to listing data using bridge functions
$listing_data = [];
if ($listing_query->have_posts()) {
    while ($listing_query->have_posts()) {
        $listing_query->the_post();
        $listing_id = get_the_ID();
        
        // Debug each listing
        if (WP_DEBUG) {
            error_log('Processing listing ID: ' . $listing_id);
            error_log('hpt_get_listing function exists: ' . (function_exists('hpt_get_listing') ? 'Yes' : 'No'));
        }
        
        // Use bridge function to get standardized listing data
        if (function_exists('hpt_get_listing')) {
            $listing = hpt_get_listing($listing_id);
            if ($listing) {
                $listing_data[] = $listing;
                if (WP_DEBUG) {
                    error_log('Successfully got listing data for ID: ' . $listing_id);
                }
            } else {
                if (WP_DEBUG) {
                    error_log('No listing data returned for ID: ' . $listing_id);
                }
            }
        } else {
            // Fallback: create basic listing data from post
            $listing_data[] = [
                'id' => $listing_id,
                'title' => get_the_title(),
                'url' => get_permalink(),
                'featured_image' => [
                    'url' => get_the_post_thumbnail_url($listing_id, 'large'),
                    'alt' => get_the_title()
                ]
            ];
            if (WP_DEBUG) {
                error_log('Used fallback data for listing ID: ' . $listing_id);
            }
        }
    }
    wp_reset_postdata();
}

if (WP_DEBUG) {
    error_log('Total listing_data count: ' . count($listing_data));
}

// TEMPORARY: If no listing data and no posts, add demo data for testing
if (empty($listing_data) && $listing_query->found_posts === 0) {
    $listing_data = [
        [
            'id' => 0,
            'title' => 'Demo Luxury Villa',
            'address' => '123 Demo Street, Test City, TX',
            'price' => 850000,
            'listing_status' => 'For Sale',
            'property_type' => 'House',
            'bedrooms' => 4,
            'bathrooms' => 3.5,
            'square_feet' => 3200,
            'featured_image' => [
                'url' => get_template_directory_uri() . '/assets/images/listing-placeholder.jpg',
                'alt' => 'Demo Property'
            ],
            'is_featured' => true,
            'url' => '#demo-property'
        ],
        [
            'id' => 0,
            'title' => 'Demo Modern Condo',
            'address' => '456 Demo Plaza, Test City, TX',
            'price' => 425000,
            'listing_status' => 'For Sale',
            'property_type' => 'Condo',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'square_feet' => 1800,
            'featured_image' => [
                'url' => get_template_directory_uri() . '/assets/images/listing-placeholder.jpg',
                'alt' => 'Demo Condo'
            ],
            'is_featured' => false,
            'url' => '#demo-condo'
        ]
    ];
    
    // Update found_posts for display
    $listing_query->found_posts = 2;
    
    if (WP_DEBUG) {
        error_log('Added demo listing data for testing');
    }
}

// Archive Header Section
?>

<?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
<!-- DEBUG INFO - Remove in production -->
<div style="background: #f0f0f0; padding: 20px; margin: 20px; border: 1px solid #ccc;">
    <h3>DEBUG INFO</h3>
    <p><strong>Query found posts:</strong> <?php echo $listing_query->found_posts; ?></p>
    <p><strong>Query post count:</strong> <?php echo $listing_query->post_count; ?></p>
    <p><strong>Listing data count:</strong> <?php echo count($listing_data); ?></p>
    <p><strong>Post type exists:</strong> <?php echo post_type_exists('listing') ? 'Yes' : 'No'; ?></p>
    <p><strong>Bridge function exists:</strong> <?php echo function_exists('hpt_get_listing') ? 'Yes' : 'No'; ?></p>
    <p><strong>Current query vars:</strong> <?php echo http_build_query($_GET); ?></p>
    <?php if (!empty($listing_data)): ?>
        <p><strong>First listing data:</strong></p>
        <pre><?php print_r(array_slice($listing_data, 0, 1)); ?></pre>
    <?php endif; ?>
</div>
<?php endif; ?>

<section class="hph-archive-header hph-bg-gray-50 hph-py-xl">
    <div class="hph-container">
        <div class="hph-text-center">
            <?php if ($listing_query->found_posts > 0): ?>
            <div class="hph-eyebrow hph-text-primary hph-text-sm hph-font-semibold hph-uppercase hph-tracking-wide hph-mb-sm">
                <?php echo esc_html($listing_query->found_posts . ' ' . __('Properties Found', 'happy-place-theme')); ?>
            </div>
            <?php endif; ?>
            <h1 class="hph-title hph-text-4xl hph-font-bold hph-text-gray-900 hph-mb-md">
                <?php echo esc_html($archive_title); ?>
            </h1>
            <?php if ($archive_description): ?>
            <p class="hph-description hph-text-xl hph-text-gray-600 hph-max-w-2xl hph-mx-auto">
                <?php echo esc_html($archive_description); ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Archive Controls Section
get_template_part('template-parts/layout/archive-controls', null, [
    'post_type' => 'listing',
    'total_results' => $listing_query->found_posts,
    'current_view' => $view_mode,
    'view_options' => ['grid', 'list', 'map'],
    'current_sort' => $sort,
    'sort_options' => [
        'date_desc' => __('Newest First', 'happy-place-theme'),
        'price_asc' => __('Price: Low to High', 'happy-place-theme'),
        'price_desc' => __('Price: High to Low', 'happy-place-theme'),
        'bedrooms_desc' => __('Most Bedrooms', 'happy-place-theme'),
        'sqft_desc' => __('Largest Size', 'happy-place-theme')
    ],
    'per_page_options' => [12, 24, 48],
    'current_per_page' => $posts_per_page
]);

// Main Listings Section
if (!empty($listing_data)) {
    // Debug info
    if (defined('WP_DEBUG') && WP_DEBUG) {
        echo "<!-- Loading listings-loop with " . count($listing_data) . " listings -->\n";
    }
    
    get_template_part('template-parts/sections/listings-loop', null, [
        'layout' => $view_mode === 'list' ? 'list' : 'grid',
        'columns' => 3,
        'background' => 'white',
        'padding' => '2xl',
        'content_width' => 'wide',
        'listings' => $listing_data,
        'show_price' => true,
        'show_status' => true,
        'show_details' => true,
        'show_agent' => false,
        'show_favorite' => is_user_logged_in(),
        'show_compare' => true,
        'image_aspect' => '4:3',
        'animation' => true,
        'filters' => false,
        'section_id' => 'listings-archive-main'
    ]);
} else {
    // Debug info for empty state
    if (defined('WP_DEBUG') && WP_DEBUG) {
        echo "<!-- No listing data, showing fallback. Query found: " . $listing_query->found_posts . " posts -->\n";
    }
    
    // If we have posts but no listing data, try loading listings-loop anyway (it has fallback data)
    if ($listing_query->found_posts > 0) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            echo "<!-- Posts exist but no listing data - loading template part anyway -->\n";
        }
        get_template_part('template-parts/sections/listings-loop', null, [
            'layout' => 'grid',
            'columns' => 3,
            'listings' => [], // Empty - template will use demo data
            'section_id' => 'listings-archive-fallback'
        ]);
    } else {
        // No Results Section
        ?>
        <section class="hph-no-results hph-py-2xl hph-text-center">
            <div class="hph-container">
                <h2 class="hph-text-3xl hph-font-bold hph-text-gray-900 hph-mb-md">
                    <?php _e('No Properties Found', 'happy-place-theme'); ?>
                </h2>
                <p class="hph-text-xl hph-text-gray-600 hph-mb-lg">
                    <?php _e('Try adjusting your search criteria or browse all available properties.', 'happy-place-theme'); ?>
                </p>
                <div class="hph-flex hph-justify-center hph-gap-md">
                    <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" 
                       class="hph-btn hph-btn-primary">
                        <?php _e('View All Properties', 'happy-place-theme'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/advanced-search/')); ?>" 
                       class="hph-btn hph-btn-outline">
                        <?php _e('Advanced Search', 'happy-place-theme'); ?>
                    </a>
                </div>
            </div>
        </section>
        <?php
    }
}

// Pagination Section
if ($listing_query->max_num_pages > 1) {
    $pagination_args = [
        'total' => $listing_query->max_num_pages,
        'current' => $paged,
        'base' => get_post_type_archive_link('listing') . '%_%',
        'format' => 'page/%#%/',
        'add_args' => array_filter([
            's' => $search_query,
            'view' => $view_mode !== 'grid' ? $view_mode : null,
            'sort' => $sort !== 'date_desc' ? $sort : null,
            'per_page' => $posts_per_page !== 12 ? $posts_per_page : null,
            'property_type' => $_GET['property_type'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'bedrooms' => $_GET['bedrooms'] ?? null,
            'bathrooms' => $_GET['bathrooms'] ?? null,
            'status' => $_GET['status'] ?? null
        ]),
        'show_all' => false,
        'end_size' => 1,
        'mid_size' => 2
    ];
    
    ?>
    <section class="hph-pagination-section hph-py-xl">
        <div class="hph-container hph-text-center">
            <?php echo paginate_links($pagination_args); ?>
        </div>
    </section>
    <?php
}

wp_reset_postdata();

get_footer();
