<?php
/**
 * Property Type Taxonomy Archive Template
 * 
 * Displays listings filtered by property type (single-family, condo, townhouse, etc.)
 * Follows the same structure as archive-listing.php with property type specific modifications
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Archive-specific assets now handled by Vite Asset Loader automatically
// wp_enqueue_style('hph-archive-enhanced', get_template_directory_uri() . '/assets/css/framework/features/listing/archive-enhanced.css', ['hph-framework'], '1.0.0');
// wp_enqueue_script('hph-archive-enhanced', get_template_directory_uri() . '/assets/js/pages/archive-listing-enhanced.js', ['hph-framework'], '1.0.0', true);

// Map component now included in archive bundle
// wp_enqueue_script('hph-map-component', get_template_directory_uri() . '/assets/js/components/hph-map.js', ['hph-framework'], '1.0.0', true);

// Enqueue Mapbox GL JS if we have a token
$mapbox_token = '';
if (function_exists('hp_get_mapbox_token')) {
    // Get Mapbox token for map functionality
    $mapbox_token = get_field('mapbox_access_token', 'option') ?: '';
} else if (defined('HP_MAPBOX_ACCESS_TOKEN')) {
    $mapbox_token = HP_MAPBOX_ACCESS_TOKEN;
}

if (!empty($mapbox_token)) {
    wp_enqueue_style('mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css', [], '2.15.0');
    wp_enqueue_script('mapbox-gl-js', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], '2.15.0', false);
}

// Add body class for archive page styling
add_filter('body_class', function($classes) {
    $classes[] = 'archive-listing';
    $classes[] = 'taxonomy-property_type';
    return $classes;
});

get_header();

// Get current taxonomy term
$current_term = get_queried_object();
$property_type_slug = $current_term->slug;
$property_type_name = $current_term->name;
$property_type_description = $current_term->description;

// Add script to disable header scroll behavior for archive mode and expand search
add_action('wp_footer', function() use ($property_type_slug) {
    echo '<script>
        // Disable header scroll handlers for archive mode
        window.hphArchiveMode = true;
        
        // Override any header scroll functionality
        if (typeof window.hphHeader !== "undefined" && window.hphHeader.destroy) {
            window.hphHeader.destroy();
        }
        
        // Remove any scroll event listeners that might interfere
        document.addEventListener("DOMContentLoaded", function() {
            window.removeEventListener("scroll", window.hphHeaderScroll);
            
            // Auto-expand header search on archive pages
            setTimeout(function() {
                const searchToggle = document.querySelector(".hph-search-toggle");
                const searchBar = document.querySelector(".hph-search-bar[data-search-bar]");
                
                if (searchToggle && searchBar) {
                    // Check if search is not already expanded
                    if (!searchBar.classList.contains("active") && !searchBar.style.display === "block") {
                        searchToggle.click();
                        searchBar.classList.add("archive-auto-expanded");
                        
                        // Pre-select the property type in the search form
                        setTimeout(function() {
                            const propertyTypeSelect = document.querySelector("select[name=\"property_type\"]");
                            if (propertyTypeSelect) {
                                propertyTypeSelect.value = "' . esc_js($property_type_slug) . '";
                            }
                        }, 200);
                    }
                }
            }, 100);
        });
    </script>';
});

// Set Mapbox configuration for HPH Map component
if (!empty($mapbox_token)) {
    wp_add_inline_script('hph-archive-enhanced', '
        // Ensure Mapbox configuration is available for HPH Map component
        window.hph_mapbox_config = window.hph_mapbox_config || {};
        window.hph_mapbox_config.access_token = "' . esc_js($mapbox_token) . '";
    ', 'before');
}

// Get filter parameters
$search = sanitize_text_field($_GET['s'] ?? $_GET['search'] ?? '');

// Handle price range from header search
$price_range = sanitize_text_field($_GET['price_range'] ?? '');
$min_price = 0;
$max_price = 0;

if (!empty($price_range) && strpos($price_range, '-') !== false) {
    list($min_price, $max_price) = explode('-', $price_range);
    $min_price = intval($min_price);
    $max_price = intval($max_price);
} else {
    $min_price = intval($_GET['min_price'] ?? 0);
    $max_price = intval($_GET['max_price'] ?? 0);
}

$bedrooms = sanitize_text_field($_GET['bedrooms'] ?? '');
$bathrooms = sanitize_text_field($_GET['bathrooms'] ?? '');
$zip_code = sanitize_text_field($_GET['zip_code'] ?? '');

// Build query for this property type
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$args = [
    'post_type' => 'listing',
    'post_status' => 'publish', 
    'posts_per_page' => 12,
    'paged' => $paged,
    'tax_query' => [
        [
            'taxonomy' => 'property_type',
            'field' => 'slug',
            'terms' => $property_type_slug
        ]
    ]
];

// Add search
if (!empty($search)) {
    $args['s'] = $search;
}

// Initialize meta_query
$meta_queries = [];

// Add price range filter
if ($min_price || $max_price) {
    $price_query = [
        'key' => 'listing_price',
        'type' => 'NUMERIC'
    ];
    
    if ($min_price && $max_price) {
        $price_query['value'] = [$min_price, $max_price];
        $price_query['compare'] = 'BETWEEN';
    } elseif ($min_price) {
        $price_query['value'] = $min_price;
        $price_query['compare'] = '>=';
    } elseif ($max_price) {
        $price_query['value'] = $max_price;
        $price_query['compare'] = '<=';
    }
    
    $meta_queries[] = $price_query;
}

// Add bedrooms filter
if (!empty($bedrooms)) {
    $bedrooms_num = intval($bedrooms);
    $meta_queries[] = [
        'key' => 'bedrooms',
        'value' => $bedrooms_num,
        'type' => 'NUMERIC',
        'compare' => '>='
    ];
}

// Add bathrooms filter
if (!empty($bathrooms)) {
    $bathrooms_num = intval($bathrooms);
    $meta_queries[] = [
        'key' => 'bathrooms',
        'value' => $bathrooms_num,
        'type' => 'NUMERIC',
        'compare' => '>='
    ];
}

// Add zip code filter
if (!empty($zip_code)) {
    $meta_queries[] = [
        'key' => 'zip_code',
        'value' => $zip_code,
        'compare' => '='
    ];
}

// Apply meta queries if any exist
if (!empty($meta_queries)) {
    $args['meta_query'] = $meta_queries;
    if (count($meta_queries) > 1) {
        $args['meta_query']['relation'] = 'AND';
    }
}

// Execute the query
$listings = new WP_Query($args);

// Prepare data for JavaScript (map functionality)
$listings_data = [];
if ($listings->have_posts()) {
    while ($listings->have_posts()) {
        $listings->the_post();
        $listing_id = get_the_ID();
        
        // Get listing data for map
        $latitude = get_field('latitude', $listing_id);
        $longitude = get_field('longitude', $listing_id);
        $price = get_field('listing_price', $listing_id);
        $address = get_field('address', $listing_id);
        
        if ($latitude && $longitude) {
            $listings_data[] = [
                'id' => $listing_id,
                'latitude' => floatval($latitude),
                'longitude' => floatval($longitude),
                'price' => $price ? '$' . number_format($price) : '',
                'title' => get_the_title(),
                'address' => $address ?: '',
                'permalink' => get_permalink(),
                'image' => get_the_post_thumbnail_url($listing_id, 'medium'),
                'bedrooms' => get_field('bedrooms', $listing_id),
                'bathrooms' => get_field('bathrooms', $listing_id),
                'square_feet' => get_field('square_feet', $listing_id),
                'property_type' => $property_type_name,
                'status' => 'active'
            ];
        }
    }
    wp_reset_postdata();
}
?>

<main id="hph-main-content" class="hph-main-content" role="main">
    
    <!-- Archive Hero Section -->
    <section class="hph-archive-hero hph-relative hph-min-h-64 hph-flex hph-items-center hph-bg-cover hph-bg-center" 
             style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/property-types/<?php echo esc_attr($property_type_slug); ?>-hero.jpg'), url('<?php echo get_template_directory_uri(); ?>/assets/images/listings-hero.jpg');">
        
        <!-- Gradient Overlay -->
        <div class="hph-absolute hph-inset-0 hph-bg-gradient-to-r hph-from-primary hph-to-primary-light hph-opacity-85"></div>
        
        <!-- Hero Content -->
        <div class="hph-container hph-relative hph-z-10">
            <div class="hph-text-center hph-text-white">
                
                <!-- Breadcrumbs -->
                <nav class="hph-breadcrumbs hph-mb-4">
                    <ol class="hph-flex hph-justify-center hph-items-center hph-space-x-2 hph-text-sm hph-opacity-90">
                        <li><a href="<?php echo esc_url(home_url('/')); ?>" class="hph-text-white hph-hover-opacity-80">Home</a></li>
                        <li class="hph-opacity-70">/</li>
                        <li><a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" class="hph-text-white hph-hover-opacity-80">Listings</a></li>
                        <li class="hph-opacity-70">/</li>
                        <li class="hph-opacity-90"><?php echo esc_html($property_type_name); ?></li>
                    </ol>
                </nav>
                
                <!-- Main Title -->
                <h1 class="hph-hero-title hph-text-4xl md:hph-text-5xl hph-font-bold hph-mb-4">
                    <?php echo esc_html($property_type_name); ?> Properties
                </h1>
                
                <!-- Subtitle with count -->
                <p class="hph-hero-subtitle hph-text-xl hph-mb-6 hph-opacity-95">
                    <?php if ($listings->found_posts > 0): ?>
                        Discover <?php echo number_format($listings->found_posts); ?> available <?php echo esc_html(strtolower($property_type_name)); ?> properties
                    <?php else: ?>
                        Explore <?php echo esc_html(strtolower($property_type_name)); ?> properties in your area
                    <?php endif; ?>
                </p>
                
                <!-- Description if available -->
                <?php if (!empty($property_type_description)): ?>
                <div class="hph-hero-description hph-text-lg hph-opacity-90 hph-max-w-2xl hph-mx-auto">
                    <?php echo wp_kses_post($property_type_description); ?>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </section>

    <!-- Archive Layout Component (reuse existing layout) -->
    <?php
    get_template_part('template-parts/layout/archive-layout', null, [
        'query' => $listings,
        'listings_data' => $listings_data,
        'page_title' => $property_type_name . ' Properties',
        'filters' => [
            'search' => $search,
            'property_type' => $property_type_slug, // Pre-set property type
            'min_price' => $min_price,
            'max_price' => $max_price,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'zip_code' => $zip_code
        ],
        'show_property_type_filter' => false, // Hide property type filter since we're already filtered
        'archive_type' => 'property_type',
        'archive_term' => $current_term
    ]);
    ?>

</main>

<?php get_footer(); ?>
