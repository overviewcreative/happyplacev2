<?php
/**
 * Archive Template for Local Places
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 */

// Enqueue required assets
wp_enqueue_style('hph-local-places', get_template_directory_uri() . '/assets/css/framework/features/local/local-archive.css', ['hph-framework'], '1.0.0');
wp_enqueue_script('hph-local-filters', get_template_directory_uri() . '/assets/js/components/local/local-filters.js', ['hph-framework'], '1.0.0', true);
wp_enqueue_script('hph-archive-places', get_template_directory_uri() . '/assets/js/pages/archive-places.js', ['hph-framework'], '1.0.0', true);

// Check for map functionality
$mapbox_token = '';
if (function_exists('hp_get_mapbox_token')) {
    $mapbox_token = hp_get_mapbox_token();
} elseif (defined('HP_MAPBOX_ACCESS_TOKEN')) {
    $mapbox_token = HP_MAPBOX_ACCESS_TOKEN;
}

if (!empty($mapbox_token)) {
    wp_enqueue_style('mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css', [], '2.15.0');
    wp_enqueue_script('mapbox-gl-js', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], '2.15.0', false);
    wp_enqueue_script('hph-places-map', get_template_directory_uri() . '/assets/js/components/local/places-map.js', ['mapbox-gl-js'], '1.0.0', true);
}

// Build query args from filters
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$posts_per_page = 12;

$query_args = [
    'post_type' => 'local_place',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
];

// Apply search filter
if (!empty($_GET['search'])) {
    $query_args['s'] = sanitize_text_field($_GET['search']);
}

// Build meta query
$meta_query = [];

// City filter
if (!empty($_GET['city'])) {
    $meta_query[] = [
        'key' => 'primary_city',
        'value' => intval($_GET['city']),
        'compare' => '='
    ];
}

// Category filter (assuming it's stored in post meta)
if (!empty($_GET['category'])) {
    $meta_query[] = [
        'key' => 'place_category',
        'value' => sanitize_text_field($_GET['category']),
        'compare' => '='
    ];
}

// Price filter
if (!empty($_GET['price'])) {
    $meta_query[] = [
        'key' => 'price_range',
        'value' => sanitize_text_field($_GET['price']),
        'compare' => '='
    ];
}

// Family friendly filter
if (!empty($_GET['family_friendly'])) {
    $meta_query[] = [
        'key' => 'is_family_friendly',
        'value' => '1',
        'compare' => '='
    ];
}

if (!empty($meta_query)) {
    $query_args['meta_query'] = $meta_query;
}

// Execute query
$places_query = new WP_Query($query_args);

// Store original query for map data
$map_places = [];
if ($places_query->have_posts()) {
    while ($places_query->have_posts()) {
        $places_query->the_post();
        $lat = get_field('lat');
        $lng = get_field('lng');
        
        if ($lat && $lng) {
            $map_places[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'lat' => floatval($lat),
                'lng' => floatval($lng),
                'url' => get_permalink(),
                'category' => get_field('place_category'),
                'thumbnail' => get_the_post_thumbnail_url(null, 'thumbnail')
            ];
        }
    }
    wp_reset_postdata();
}

get_header();
?>

<div class="hph-archive hph-archive--places">
    
    <!-- Archive Hero -->
    <section class="hph-archive__hero">
        <div class="hph-container">
            <div class="hph-archive__hero-content">
                <h1 class="hph-archive__title">Explore Local Places</h1>
                <p class="hph-archive__subtitle">Discover the best restaurants, parks, shopping, and attractions in your area</p>
            </div>
        </div>
    </section>
    
    <!-- Filters Section -->
    <section class="hph-archive__filters">
        <div class="hph-container">
            <?php get_template_part('template-parts/components/local/local-filters', null, [
                'post_type' => 'local_place',
                'show_map_toggle' => !empty($mapbox_token),
                'show_search' => true
            ]); ?>
        </div>
    </section>
    
    <!-- Main Content -->
    <section class="hph-archive__content">
        <div class="hph-container">
            
            <!-- Results Header -->
            <div class="hph-archive__header">
                <div class="hph-archive__count">
                    <?php if ($places_query->found_posts > 0): ?>
                        <span class="hph-archive__count-number"><?php echo $places_query->found_posts; ?></span>
                        <span class="hph-archive__count-label">
                            <?php echo $places_query->found_posts === 1 ? 'Place Found' : 'Places Found'; ?>
                        </span>
                    <?php else: ?>
                        <span class="hph-archive__count-label">No places found</span>
                    <?php endif; ?>
                </div>
                
                <div class="hph-archive__controls">
                    <div class="hph-sort-control">
                        <label class="hph-sort-control__label">Sort by:</label>
                        <select class="hph-sort-control__select" data-sort-places>
                            <option value="date">Newest First</option>
                            <option value="title">Name A-Z</option>
                            <option value="popular">Most Popular</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Archive Layout Container -->
            <div class="hph-archive__layout" data-archive-layout="grid">
                
                <?php if ($places_query->have_posts()): ?>
                
                <!-- Grid View -->
                <div class="hph-archive__grid hph-grid hph-grid--3" data-view-content="grid">
                    <?php while ($places_query->have_posts()): $places_query->the_post(); ?>
                        <div class="hph-grid__item">
                            <?php get_template_part('template-parts/components/local/place-card', null, [
                                'place_id' => get_the_ID(),
                                'variant' => 'grid',
                                'show_distance' => false
                            ]); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- List View (hidden by default) -->
                <div class="hph-archive__list" data-view-content="list" style="display: none;">
                    <?php 
                    $places_query->rewind_posts();
                    while ($places_query->have_posts()): $places_query->the_post(); 
                    ?>
                        <div class="hph-archive__list-item">
                            <?php get_template_part('template-parts/components/local/place-card', null, [
                                'place_id' => get_the_ID(),
                                'variant' => 'list',
                                'show_distance' => false
                            ]); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <?php if (!empty($mapbox_token)): ?>
                <!-- Map View (hidden by default) -->
                <div class="hph-archive__map-container" data-view-content="map" style="display: none;">
                    <div class="hph-archive__map" id="places-map" data-places='<?php echo json_encode($map_places); ?>'></div>
                    <div class="hph-archive__map-sidebar">
                        <div class="hph-map-results">
                            <?php 
                            $places_query->rewind_posts();
                            while ($places_query->have_posts()): $places_query->the_post(); 
                            ?>
                                <div class="hph-map-results__item" data-place-id="<?php echo get_the_ID(); ?>">
                                    <?php get_template_part('template-parts/components/local/place-card', null, [
                                        'place_id' => get_the_ID(),
                                        'variant' => 'minimal',
                                        'show_distance' => true
                                    ]); ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                
                <!-- No Results -->
                <div class="hph-archive__empty">
                    <div class="hph-empty-state">
                        <i class="hph-empty-state__icon hph-icon hph-icon--search-empty"></i>
                        <h2 class="hph-empty-state__title">No Places Found</h2>
                        <p class="hph-empty-state__text">
                            Try adjusting your filters or search terms to find what you're looking for.
                        </p>
                        <a href="<?php echo esc_url(get_post_type_archive_link('local_place')); ?>" class="hph-btn hph-btn--primary">
                            Clear Filters
                        </a>
                    </div>
                </div>
                
                <?php endif; ?>
                
                <?php wp_reset_postdata(); ?>
                
            </div>
            
            <!-- Pagination -->
            <?php if ($places_query->max_num_pages > 1): ?>
            <div class="hph-archive__pagination">
                <?php
                $pagination_args = [
                    'total' => $places_query->max_num_pages,
                    'current' => $paged,
                    'prev_text' => '<i class="hph-icon hph-icon--arrow-left"></i> Previous',
                    'next_text' => 'Next <i class="hph-icon hph-icon--arrow-right"></i>',
                    'type' => 'list',
                    'end_size' => 2,
                    'mid_size' => 2
                ];
                
                // Preserve filter parameters in pagination links
                if (!empty($_GET)) {
                    $pagination_args['add_args'] = $_GET;
                }
                
                echo '<nav class="hph-pagination">';
                echo paginate_links($pagination_args);
                echo '</nav>';
                ?>
            </div>
            <?php endif; ?>
            
        </div>
    </section>
    
    <!-- Call to Action -->
    <section class="hph-archive__cta">
        <div class="hph-container">
            <div class="hph-cta hph-cta--centered">
                <h2 class="hph-cta__title">Know a Great Local Spot?</h2>
                <p class="hph-cta__text">Help us build a comprehensive guide to the best places in our community.</p>
                <a href="/suggest-a-place" class="hph-btn hph-btn--secondary">
                    Suggest a Place
                </a>
            </div>
        </div>
    </section>
    
</div>

<?php get_footer(); ?>
