<?php
/**
 * Archive Template for Cities
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Enqueue hero search form CSS to match listing archive
wp_enqueue_style('hph-hero-search-form', get_template_directory_uri() . '/assets/css/framework/components/organisms/hero-search-form.css', ['hph-framework'], filemtime(get_template_directory() . '/assets/css/framework/components/organisms/hero-search-form.css'));

// Enqueue archive JavaScript for load more functionality
// wp_enqueue_script('hph-archive-cities', get_template_directory_uri() . '/assets/js/pages/archive-cities.js', ['jquery', 'hph-framework'], '1.0.0', true);

// Localize script with AJAX data
wp_localize_script('hph-archive-cities', 'hph_theme', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_load_more_nonce')
]);

// Add body class for city archive styling
add_filter('body_class', function($classes) {
    $classes[] = 'archive-city';
    return $classes;
});

// Get query data
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$posts_per_page = 12;

// Get filter values
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$selected_state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';
$sort_by = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';

// Build query args
$query_args = [
    'post_type' => 'city',
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
    case 'state':
        $query_args['meta_key'] = 'state';
        $query_args['orderby'] = 'meta_value';
        $query_args['order'] = 'ASC';
        break;
    case 'population':
        $query_args['meta_key'] = 'population';
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

if (!empty($selected_state)) {
    $meta_query[] = [
        'key' => 'state',
        'value' => $selected_state,
        'compare' => '='
    ];
}

if (!empty($meta_query)) {
    $query_args['meta_query'] = $meta_query;
}

// Execute query
$cities_query = new WP_Query($query_args);

// Get all states for dropdown (simplified approach)
$states = [];

// Try to get states safely
try {
    if (function_exists('get_field')) {
        $state_posts = get_posts([
            'post_type' => 'city',
            'posts_per_page' => 50, // Limit to prevent memory issues
            'post_status' => 'publish'
        ]);

        foreach ($state_posts as $post) {
            $state = get_field('state', $post->ID);
            if ($state && !in_array($state, $states)) {
                $states[] = $state;
            }
        }
        sort($states);
    }
} catch (Exception $e) {
    // If there's an error, continue with empty states array
    $states = [];
}

get_header();
?>
<main class="hph-main">
    
    <!-- Archive Hero Section - Matching listing archive pattern -->
    <div class="hph-archive-hero-section hph-pt-24 md:hph-pt-32" data-hide-in-views="map">
        <div class="hph-archive-city-hero" style="background-image: url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/archive-city-bg.jpg'); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <div class="hph-archive-hero-overlay"></div>
            <div class="hph-archive-hero-container">
                <div class="hph-archive-hero-content">
                    <h1 class="hph-hero-headline">
                        <?php echo empty($search_query) ? 'Cities' : 'City Search Results'; ?>
                    </h1>
                    <p class="hph-hero-subheadline">
                        <?php 
                        if (empty($search_query)) {
                            echo 'Discover Amazing Cities - Explore cities across the United States with detailed information and insights';
                        } else {
                            echo 'Found ' . number_format($cities_query->found_posts) . ' cities matching your search';
                        }
                        ?>
                    </p>

                    <!-- Integrated Search Form -->
                    <div class="hph-hero-search">
                        <form class="hph-hero-search-form" action="<?php echo esc_url(home_url('/cities/')); ?>" method="GET">
                            <div class="hph-hero-search-row">
                                
                                <!-- Search Input -->
                                <div class="hph-search-input-container hph-hero-search-input">
                                    <div class="hph-search-input-wrapper has-close">
                                        <input type="text"
                                               name="search"
                                               id="hero-city-search-input"
                                               class="hph-form-input hph-search-input"
                                               placeholder="Search cities..."
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

                                <!-- State Dropdown -->
                                <select name="state" class="hph-form-select">
                                    <option value="">All States</option>
                                    <?php if (!empty($states) && is_array($states)): ?>
                                        <?php foreach ($states as $state): ?>
                                            <option value="<?php echo esc_attr($state); ?>" <?php selected($selected_state, $state); ?>>
                                                <?php echo esc_html($state); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>

                                <!-- Sort By -->
                                <select name="sort" class="hph-form-select">
                                    <option value="">Sort By</option>
                                    <option value="date" <?php selected($_GET['sort'] ?? '', 'date'); ?>>Newest</option>
                                    <option value="title" <?php selected($_GET['sort'] ?? '', 'title'); ?>>A-Z</option>
                                    <option value="state" <?php selected($_GET['sort'] ?? '', 'state'); ?>>State</option>
                                    <option value="population" <?php selected($_GET['sort'] ?? '', 'population'); ?>>Population</option>
                                </select>

                                <!-- Search Button -->
                                <button type="submit" class="hph-btn hph-btn-primary hph-btn-lg">
                                    <i class="fas fa-search hph-mr-sm"></i>
                                    Search Cities
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
            
            <?php if ($cities_query->have_posts()): ?>
            
            <!-- Grid View (Default) -->
            <div class="hph-cities-grid hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 hph-gap-lg" data-view-content="grid" data-load-more-container>
                <?php while ($cities_query->have_posts()): $cities_query->the_post(); ?>
                    <div class="hph-city-card-wrapper">
                        <?php hph_component('universal-card', [
                            'post_id' => get_the_ID(),
                            'post_type' => 'city',
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
            <div class="hph-cities-list hph-space-y-md hph-hidden" data-view-content="list">
                <?php 
                $cities_query->rewind_posts();
                while ($cities_query->have_posts()): $cities_query->the_post(); 
                ?>
                    <div class="hph-city-card-wrapper">
                        <?php hph_component('universal-card', [
                            'post_id' => get_the_ID(),
                            'post_type' => 'city',
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
                <h3 class="hph-text-xl hph-font-semibold hph-text-gray-900 hph-mb-md">No cities found</h3>
                <p class="hph-text-gray-600 hph-mb-lg">
                    Try adjusting your search terms or filters to find what you're looking for.
                </p>
                <a href="<?php echo get_post_type_archive_link('city'); ?>" 
                   class="hph-btn hph-btn-primary">
                    View All Cities
                </a>
            </div>
            
            <?php endif; ?>
            
            <?php wp_reset_postdata(); ?>
            
            <!-- Load More Button -->
            <?php if ($cities_query->max_num_pages > 1): ?>
            <div class="hph-load-more-wrapper hph-mt-xl hph-text-center">
                <button 
                    type="button" 
                    class="hph-btn hph-btn-outline hph-btn-lg" 
                    data-load-more-btn
                    data-post-type="city"
                    data-current-page="1"
                    data-max-pages="<?php echo $cities_query->max_num_pages; ?>"
                    data-search-params="<?php echo esc_attr(http_build_query($_GET)); ?>"
                >
                    <i class="fas fa-plus hph-mr-sm"></i>
                    Load More Cities
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

// Handle sorting
switch ($sort) {
    case 'name_desc':
        $query_args['orderby'] = 'title';
        $query_args['order'] = 'DESC';
        break;
    case 'population_desc':
        $query_args['meta_key'] = 'population';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'DESC';
        break;
    case 'population_asc':
        $query_args['meta_key'] = 'population';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'ASC';
        break;
    case 'listings_desc':
        // Would need to add a calculated field for this
        $query_args['orderby'] = 'title';
        $query_args['order'] = 'ASC';
        break;
    default: // name_asc
        $query_args['orderby'] = 'title';
        $query_args['order'] = 'ASC';
}

// Execute query
$city_query = new WP_Query($query_args);

// Prepare archive title and description
$archive_title = __('Cities We Serve', 'happy-place-theme');
$archive_description = __('Discover the cities and areas where our expertise can help you find your perfect home', 'happy-place-theme');

if (!empty($search_query)) {
    $archive_title = sprintf(__('Cities matching "%s"', 'happy-place-theme'), $search_query);
}

if ($city_query->found_posts > 0) {
    $archive_description = sprintf(
        _n('Serving %d city', 'Serving %d cities', $city_query->found_posts, 'happy-place-theme'),
        $city_query->found_posts
    );
} else {
    $archive_description = __('We are always expanding our service areas. Contact us to learn about opportunities in your city.', 'happy-place-theme');
}

// Use archive-layout component with city-specific configuration
get_template_part('template-parts/layout/archive-layout', null, [
    // Core configuration
    'post_type' => 'city',
    'posts' => $city_query->posts,
    'title' => $archive_title,
    'description' => $archive_description,
    'total_results' => $city_query->found_posts,
    'max_pages' => $city_query->max_num_pages,
    'current_page' => $paged,
    
    // Layout configuration
    'layout' => 'contained',
    'content_width' => 'normal',
    'padding' => 'xl',
    
    // View configuration
    'view_modes' => ['grid', 'list', 'map'],
    'current_view' => $view_mode,
    'default_view' => 'grid',
    'allow_view_toggle' => true,
    
    // Search & Filter configuration
    'show_search' => true,
    'show_filters' => true,
    'show_save_search' => false,
    
    // Sort configuration  
    'sort_options' => [
        'name_asc' => __('Name A-Z', 'happy-place-theme'),
        'name_desc' => __('Name Z-A', 'happy-place-theme'),
        'population_desc' => __('Largest Population', 'happy-place-theme'),
        'population_asc' => __('Smallest Population', 'happy-place-theme'),
        'listings_desc' => __('Most Properties', 'happy-place-theme')
    ],
    'current_sort' => $sort,
    'default_sort' => 'name_asc',
    
    // Display configuration
    'show_header' => true,
    'show_controls' => true,
    'show_pagination' => true,
    'show_results_count' => true,
    
    // Grid configuration (cities look good in 4-column grid)
    'columns' => 4,
    'columns_tablet' => 3,
    'columns_mobile' => 2,
    'gap' => 'lg',
    
    // Pagination configuration
    'per_page' => $posts_per_page,
    'per_page_options' => [12, 24, 48],
    'pagination_style' => 'numbered',
    
    // Advanced features
    'ajax_enabled' => true,
    'lazy_loading' => true,
    'hover_effects' => true,
    
    // Card configuration for city adapter system
    'card_adapter_function' => 'get_city_card_props',
    'card_style' => 'location',
    'card_size' => 'medium',
    
    // Animation configuration
    'animation_style' => 'fade',
    'stagger_delay' => 100,
    
    // Custom query args for AJAX refreshes
    'query_args' => $query_args
]);

wp_reset_postdata();

get_footer(); 
?>
