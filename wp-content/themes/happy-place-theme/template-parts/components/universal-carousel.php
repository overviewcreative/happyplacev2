<?php
/**
 * Universal Carousel Component
 * Flexible carousel that can display any post type with filters
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 * 
 * @param array $args {
 *     @type string $post_type        Post type to display (required)
 *     @type string $title           Section title
 *     @type string $subtitle        Section subtitle
 *     @type array  $query_args      Custom WP_Query arguments
 *     @type array  $meta_query      Meta query filters
 *     @type array  $tax_query       Taxonomy query filters
 *     @type string $card_variant    Card variant ('grid', 'list', 'minimal', 'featured')
 *     @type int    $posts_per_page  Number of posts to show
 *     @type string $orderby         Order by (date, title, menu_order, meta_value)
 *     @type string $order           Order direction (ASC, DESC)
 *     @type string $meta_key        Meta key for ordering
 *     @type bool   $show_navigation Show carousel navigation
 *     @type bool   $show_dots       Show carousel dots
 *     @type bool   $autoplay        Enable autoplay
 *     @type int    $autoplay_speed  Autoplay speed in ms
 *     @type int    $slides_to_show  Number of slides to show
 *     @type array  $breakpoints     Responsive breakpoints
 *     @type string $carousel_id     Unique carousel ID
 * }
 */

// Default arguments
$defaults = [
    'post_type' => 'post',
    'title' => '',
    'subtitle' => '',
    'query_args' => [],
    'meta_query' => [],
    'tax_query' => [],
    'card_variant' => 'grid',
    'posts_per_page' => 6,
    'orderby' => 'date',
    'order' => 'DESC',
    'meta_key' => '',
    'show_navigation' => true,
    'show_dots' => true,
    'autoplay' => false,
    'autoplay_speed' => 5000,
    'slides_to_show' => 3,
    'breakpoints' => [
        'mobile' => ['breakpoint' => 480, 'slides' => 1],
        'tablet' => ['breakpoint' => 768, 'slides' => 2],
        'desktop' => ['breakpoint' => 1024, 'slides' => 3]
    ],
    'carousel_id' => 'carousel-' . uniqid()
];

$args = wp_parse_args($args ?? [], $defaults);
extract($args);

// Build WP_Query arguments
$base_query_args = [
    'post_type' => $post_type,
    'posts_per_page' => $posts_per_page,
    'post_status' => 'publish',
    'orderby' => $orderby,
    'order' => $order,
    'no_found_rows' => true,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false
];

// Add meta key for ordering if specified
if ($meta_key) {
    $base_query_args['meta_key'] = $meta_key;
}

// Merge with custom query args
$final_query_args = array_merge($base_query_args, $query_args);

// Add meta query if provided
if (!empty($meta_query)) {
    $final_query_args['meta_query'] = $meta_query;
}

// Add taxonomy query if provided
if (!empty($tax_query)) {
    $final_query_args['tax_query'] = $tax_query;
}

// Execute query
$carousel_query = new WP_Query($final_query_args);

// Post type configuration mapping - Using HPH color variables only
$post_type_configs = [
    'listing' => [
        'card_template' => 'listing-card-enhanced',
        'icon' => 'hph-icon--home',
        'color_scheme' => 'primary'
    ],
    'agent' => [
        'card_template' => 'agent-card-grid',
        'icon' => 'hph-icon--user',
        'color_scheme' => 'secondary'
    ],
    'local_place' => [
        'card_template' => 'components/local/place-card',
        'icon' => 'hph-icon--location',
        'color_scheme' => 'tertiary'
    ],
    'local_event' => [
        'card_template' => 'components/local/event-card',
        'icon' => 'hph-icon--calendar',
        'color_scheme' => 'info'
    ],
    'community' => [
        'card_template' => 'listing-card', // Reuse listing card
        'icon' => 'hph-icon--map',
        'color_scheme' => 'success'
    ],
    'city' => [
        'card_template' => 'listing-card', // Reuse listing card
        'icon' => 'hph-icon--location-alt',
        'color_scheme' => 'primary'
    ],
    'open_house' => [
        'card_template' => 'components/local/event-card', // Similar to events
        'icon' => 'hph-icon--calendar-alt',
        'color_scheme' => 'orange'
    ],
    'post' => [
        'card_template' => 'post-card',
        'icon' => 'hph-icon--post',
        'color_scheme' => 'gray'
    ]
];

$config = $post_type_configs[$post_type] ?? $post_type_configs['post'];

// Enqueue carousel styles and scripts
// Universal carousel assets now handled by Vite Asset Loader bundles
// wp_enqueue_style('hph-universal-carousel', get_template_directory_uri() . '/assets/css/framework/components/organisms/universal-carousel.css', ['hph-framework'], '1.0.0');
// wp_enqueue_script('hph-universal-carousel', get_template_directory_uri() . '/assets/js/components/universal-carousel.js', ['jquery'], '1.0.1', true);

// Don't render if no posts
if (!$carousel_query->have_posts()) {
    wp_reset_postdata();
    return;
}
?>

<section class="hph-universal-carousel hph-carousel-<?php echo esc_attr($post_type); ?> hph-color-scheme--<?php echo esc_attr($config['color_scheme']); ?>">
    
    <?php if ($title || $subtitle): ?>
    <!-- Section Header -->
    <div class="hph-container">
        <div class="hph-carousel__header">
            <?php if ($title): ?>
            <h2 class="hph-carousel__title">
                <i class="<?php echo esc_attr($config['icon']); ?>"></i>
                <?php echo esc_html($title); ?>
            </h2>
            <?php endif; ?>
            
            <?php if ($subtitle): ?>
            <p class="hph-carousel__subtitle"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>
            
            <!-- View All Link -->
            <?php if (get_post_type_archive_link($post_type)): ?>
            <div class="hph-carousel__actions">
                <a href="<?php echo esc_url(get_post_type_archive_link($post_type)); ?>" 
                   class="hph-btn hph-btn-outline-primary hph-btn-small">
                    View All
                    <i class="hph-icon hph-icon--arrow-right"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Carousel Container - Full Width -->
    <div class="hph-container-full">
        <div class="hph-carousel__container"
             id="<?php echo esc_attr($carousel_id); ?>"
             data-post-type="<?php echo esc_attr($post_type); ?>"
             data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
             data-autoplay-speed="<?php echo esc_attr($autoplay_speed); ?>"
             data-show-navigation="<?php echo $show_navigation ? 'true' : 'false'; ?>"
             data-show-dots="<?php echo $show_dots ? 'true' : 'false'; ?>"
             data-infinite="true">
            
            <?php if ($show_navigation): ?>
            <!-- Navigation Arrows -->
            <button class="hph-carousel__nav hph-carousel__nav--prev" 
                    aria-label="Previous slides"
                    data-carousel-prev>
                <i class="hph-icon hph-icon--arrow-left"></i>
            </button>
            <button class="hph-carousel__nav hph-carousel__nav--next" 
                    aria-label="Next slides"
                    data-carousel-next>
                <i class="hph-icon hph-icon--arrow-right"></i>
            </button>
            <?php endif; ?>
            
            <!-- Carousel Track -->
            <div class="hph-carousel__track-container">
                <div class="hph-carousel__track hph-gap-xl" data-carousel-track style="gap: 1.5rem !important;">
                    
                    <?php while ($carousel_query->have_posts()): $carousel_query->the_post(); ?>
                    <div class="hph-carousel__slide" data-slide-index="<?php echo $carousel_query->current_post; ?>">
                        <div class="hph-carousel__slide-content">
                            <?php
                            // Use universal card component for all post types
                            hph_component('universal-card', [
                                'post_id' => get_the_ID(),
                                'post_type' => $post_type,
                                'layout' => 'vertical',
                                'variant' => $card_variant,
                                'size' => 'md'
                            ]);
                            ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    
                </div>
            </div>
            
            <?php if ($show_dots): ?>
            <!-- Dots Indicator -->
            <div class="hph-carousel__dots" data-carousel-dots>
                <?php for ($i = 0; $i < $carousel_query->post_count; $i += $slides_to_show): ?>
                <button class="hph-carousel__dot <?php echo $i === 0 ? 'is-active' : ''; ?>" 
                        data-slide-to="<?php echo $i; ?>"
                        aria-label="Go to slide group <?php echo ($i / $slides_to_show) + 1; ?>">
                </button>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    
    <!-- Loading State -->
    <div class="hph-carousel__loading" data-carousel-loading style="display: none;">
        <div class="hph-spinner"></div>
        <p>Loading <?php echo esc_html($post_type); ?>s...</p>
    </div>
    
</section>

<?php 
wp_reset_postdata();

// Add inline styles for color scheme - using HPH variables only
$color_var = "--hph-carousel-primary";
switch ($config['color_scheme']) {
    case 'primary': $color_value = "var(--hph-primary)"; break;
    case 'secondary': $color_value = "var(--hph-secondary)"; break;
    case 'tertiary': $color_value = "var(--hph-tertiary)"; break;
    case 'success': $color_value = "var(--hph-success)"; break;
    case 'warning': $color_value = "var(--hph-warning)"; break;
    case 'danger': $color_value = "var(--hph-danger)"; break;
    case 'orange': $color_value = "var(--hph-orange)"; break;
    case 'info': $color_value = "var(--hph-info)"; break;
    case 'gray': $color_value = "var(--hph-gray-600)"; break;
    default: $color_value = "var(--hph-primary)";
}

echo "<style>
.hph-carousel-{$post_type} {
    {$color_var}: {$color_value};
}
</style>";
?>
