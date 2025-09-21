<?php
/**
 * Archive Template for Local Places
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 */

// Enqueue hero search form CSS to match listing archive
wp_enqueue_style('hph-hero-search-form', get_template_directory_uri() . '/assets/css/framework/components/organisms/hero-search-form.css', ['hph-framework'], filemtime(get_template_directory() . '/assets/css/framework/components/organisms/hero-search-form.css'));

// Enqueue archive JavaScript for load more functionality
// wp_enqueue_script('hph-archive-local-places', get_template_directory_uri() . '/assets/js/pages/archive-local-places.js', ['jquery', 'hph-framework'], '1.0.0', true);

// Localize script with AJAX data
wp_localize_script('hph-archive-local-places', 'hph_theme', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_load_more_nonce')
]);

// Add body class for local place archive styling
add_filter('body_class', function($classes) {
    $classes[] = 'archive-local-place';
    return $classes;
});

// Get query data
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$posts_per_page = 12;

// Get filter values
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$selected_city = isset($_GET['city']) ? intval($_GET['city']) : '';
$selected_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
$selected_price_range = isset($_GET['price_range']) ? sanitize_text_field($_GET['price_range']) : '';
$sort_by = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';

// Build query args
$query_args = [
    'post_type' => 'local_place',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
];

// Apply sorting
switch ($sort_by) {
    case 'title':
        $query_args['orderby'] = 'title';
        $query_args['order'] = 'ASC';
        break;
    case 'city':
        $query_args['meta_key'] = 'primary_city';
        $query_args['orderby'] = 'meta_value';
        $query_args['order'] = 'ASC';
        break;
    case 'type':
        $query_args['orderby'] = 'title'; // Fallback to title if no place type
        $query_args['order'] = 'ASC';
        break;
    case 'price':
        $query_args['meta_key'] = 'price_range';
        $query_args['orderby'] = 'meta_value';
        $query_args['order'] = 'ASC';
        break;
    case 'rating':
        $query_args['meta_key'] = 'rating';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'DESC';
        break;
    default:
        // Keep default date ordering
        break;
}

// Apply search
if (!empty($search_query)) {
    $query_args['s'] = $search_query;
}

// Build meta query
$meta_query = [];

if (!empty($selected_city)) {
    $meta_query[] = [
        'key' => 'primary_city',
        'value' => $selected_city,
        'compare' => '='
    ];
}

if (!empty($selected_price_range)) {
    $meta_query[] = [
        'key' => 'price_range',
        'value' => $selected_price_range,
        'compare' => '='
    ];
}

if (!empty($meta_query)) {
    $query_args['meta_query'] = $meta_query;
}

// Build tax query
$tax_query = [];

if (!empty($selected_type)) {
    $tax_query[] = [
        'taxonomy' => 'place-type',
        'field' => 'slug',
        'terms' => $selected_type
    ];
}

if (!empty($tax_query)) {
    $query_args['tax_query'] = $tax_query;
}

// Execute query
$places_query = new WP_Query($query_args);

// Get all cities for dropdown (with error handling)
$cities = [];
if (post_type_exists('city')) {
    $cities = get_posts([
        'post_type' => 'city',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
}

// Get place types for dropdown (with error handling)
$place_types = [];
if (taxonomy_exists('place-type')) {
    $place_types = get_terms([
        'taxonomy' => 'place-type',
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC'
    ]);
    
    // Check if get_terms returned an error
    if (is_wp_error($place_types)) {
        $place_types = [];
    }
}

get_header();
?>

<main class="hph-main">
    
    <!-- Archive Hero Section - Matching listing archive pattern -->
    <div class="hph-archive-hero-section hph-pt-24 md:hph-pt-32" data-hide-in-views="map">
        <div class="hph-archive-local-place-hero" style="background-image: url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/archive-place-bg.jpg'); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <div class="hph-archive-hero-overlay"></div>
            <div class="hph-archive-hero-container">
                <div class="hph-archive-hero-content">
                    <h1 class="hph-hero-headline">
                        <?php echo empty($search_query) ? 'Discover Local Places' : 'Local Place Search Results'; ?>
                    </h1>
                    <p class="hph-hero-subheadline">
                        <?php 
                        if (empty($search_query)) {
                            echo 'Explore the best restaurants, shops, parks, and attractions in your community';
                        } else {
                            echo 'Found ' . number_format($places_query->found_posts) . ' places matching your search';
                        }
                        ?>
                    </p>

                    <!-- Integrated Search Form -->
                    <div class="hph-hero-search">
                        <form class="hph-hero-search-form" action="<?php echo esc_url(home_url('/places/')); ?>" method="GET">
                            <div class="hph-hero-search-row">
                                
                                <!-- Search Input -->
                                <div class="hph-search-input-container hph-hero-search-input">
                                    <div class="hph-search-input-wrapper has-close">
                                        <input type="text"
                                               name="search"
                                               id="hero-local-place-search-input"
                                               class="hph-form-input hph-search-input"
                                               placeholder="Search restaurants, shops, parks..."
                                               value="<?php echo esc_attr($search_query); ?>"
                                               autocomplete="off">
                                        <i class="fas fa-search hph-search-input-icon"></i>
                                        <?php if (!empty($search_query)) : ?>
                                            <button type="button" class="hph-btn-clear hph-btn-circle" aria-label="Clear search">
                                                <i class="fas fa-times hph-btn-icon"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- City Dropdown -->
                                <select name="city" class="hph-form-select">
                                    <option value="">All Cities</option>
                                    <?php if (!empty($cities) && is_array($cities)): ?>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?php echo $city->ID; ?>" <?php selected($selected_city, $city->ID); ?>>
                                                <?php echo esc_html($city->post_title); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>

                                <!-- Place Type Dropdown -->
                                <select name="type" class="hph-form-select">
                                    <option value="">All Types</option>
                                    <?php if (!empty($place_types) && is_array($place_types)): ?>
                                        <?php foreach ($place_types as $type): ?>
                                            <option value="<?php echo $type->slug; ?>" <?php selected($selected_type, $type->slug); ?>>
                                                <?php echo esc_html($type->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>

                                <!-- Price Range -->
                                <select name="price_range" class="hph-form-select">
                                    <option value="">Any Price</option>
                                    <option value="$" <?php selected($selected_price_range, '$'); ?>>$ - Budget Friendly</option>
                                    <option value="$$" <?php selected($selected_price_range, '$$'); ?>>$$ - Moderate</option>
                                    <option value="$$$" <?php selected($selected_price_range, '$$$'); ?>>$$$ - Upscale</option>
                                    <option value="$$$$" <?php selected($selected_price_range, '$$$$'); ?>>$$$$ - Fine Dining</option>
                                </select>

                                <!-- Sort By -->
                                <select name="sort" class="hph-form-select">
                                    <option value="">Sort By</option>
                                    <option value="date" <?php selected($_GET['sort'] ?? '', 'date'); ?>>Newest</option>
                                    <option value="title" <?php selected($_GET['sort'] ?? '', 'title'); ?>>A-Z</option>
                                    <option value="city" <?php selected($_GET['sort'] ?? '', 'city'); ?>>City</option>
                                    <option value="type" <?php selected($_GET['sort'] ?? '', 'type'); ?>>Type</option>
                                    <option value="price" <?php selected($_GET['sort'] ?? '', 'price'); ?>>Price Range</option>
                                    <option value="rating" <?php selected($_GET['sort'] ?? '', 'rating'); ?>>Rating</option>
                                </select>

                                <!-- Search Button -->
                                <button type="submit" class="hph-btn hph-btn-primary hph-btn-lg">
                                    <i class="fas fa-search hph-mr-sm"></i>
                                    Search Places
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Section -->
    <section class="hph-archive-content-section hph-py-xl">
        <div class="hph-container">
            
            <?php if ($places_query->have_posts()): ?>
            
            <!-- Grid View (Default) -->
            <div class="hph-places-grid hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 hph-gap-lg" data-view-content="grid" data-load-more-container>
                <?php while ($places_query->have_posts()): $places_query->the_post(); ?>
                    <div class="hph-place-card-wrapper">
                        <?php hph_component('universal-card', [
                            'post_id' => get_the_ID(),
                            'post_type' => 'local_place',
                            'layout' => 'vertical',
                            'variant' => 'default',
                            'size' => 'md',
                            'show_meta' => true,
                            'show_actions' => true,
                            'clickable' => true,
                            'hover_effects' => true
                        ]); ?>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- List View (Hidden) -->
            <div class="hph-places-list hph-space-y-md hph-hidden" data-view-content="list">
                <?php 
                $places_query->rewind_posts();
                while ($places_query->have_posts()): $places_query->the_post(); 
                ?>
                    <div class="hph-place-card-wrapper">
                        <?php hph_component('universal-card', [
                            'post_id' => get_the_ID(),
                            'post_type' => 'local_place',
                            'layout' => 'horizontal',
                            'variant' => 'default',
                            'size' => 'md',
                            'show_meta' => true,
                            'show_actions' => true,
                            'clickable' => true,
                            'hover_effects' => true
                        ]); ?>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <?php else: ?>
            
            <!-- Empty State -->
            <div class="hph-empty-state hph-text-center hph-py-xl">
                <div class="hph-empty-icon hph-mb-lg">
                    <span class="hph-icon hph-icon-search hph-text-6xl hph-text-gray-300"></span>
                </div>
                <h3 class="hph-text-xl hph-font-semibold hph-text-gray-900 hph-mb-md">No places found</h3>
                <p class="hph-text-gray-600 hph-mb-lg">
                    Try adjusting your search terms or filters to find what you're looking for.
                </p>
                <a href="<?php echo get_post_type_archive_link('local_place'); ?>" 
                   class="hph-btn hph-btn-primary">
                    View All Places
                </a>
            </div>
            
            <?php endif; ?>
            
            <?php wp_reset_postdata(); ?>
            
            <!-- Load More Button -->
            <?php if ($places_query->max_num_pages > 1): ?>
            <div class="hph-load-more-wrapper hph-mt-xl hph-text-center">
                <button 
                    type="button" 
                    class="hph-btn hph-btn-outline hph-btn-lg" 
                    data-load-more-btn
                    data-post-type="local_place"
                    data-current-page="1"
                    data-max-pages="<?php echo $places_query->max_num_pages; ?>"
                    data-search-params="<?php echo esc_attr(http_build_query($_GET)); ?>"
                >
                    <i class="fas fa-plus hph-mr-sm"></i>
                    Load More Places
                </button>
                <div class="hph-load-more-spinner hph-hidden hph-mt-sm">
                    <i class="fas fa-spinner fa-spin"></i>
                    Loading...
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </section>
    
</main>

<?php get_footer(); ?>
