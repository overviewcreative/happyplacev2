<?php
/**
 * Universal Loop Section Template
 *
 * Flexible loop component that can display any post type in multiple layouts.
 * Consolidates functionality from listings-loop, agents-loop, and universal-carousel.
 * Uses the universal card system for consistent styling across all post types.
 *
 * @package HappyPlaceTheme
 * @since 3.0.0
 *
 * @param array $args {
 *     @type string $post_type        Post type to display (required)
 *     @type string $layout           Layout type: 'grid', 'list', 'carousel', 'masonry', 'featured'
 *     @type string $title            Section title
 *     @type string $subtitle         Section subtitle
 *     @type string $badge            Small badge text above title
 *     @type string $background       Background style: 'white', 'light', 'dark', 'primary'
 *     @type string $padding          Padding: 'sm', 'md', 'lg', 'xl', '2xl'
 *     @type int    $columns          Grid columns (2-6)
 *     @type array  $query_args       Custom WP_Query arguments
 *     @type array  $meta_query       Meta query filters
 *     @type array  $tax_query        Taxonomy query filters
 *     @type string $card_variant     Card variant ('default', 'elevated', 'bordered', 'minimal')
 *     @type string $card_size        Card size ('sm', 'md', 'lg')
 *     @type int    $posts_per_page   Number of posts to show
 *     @type string $orderby          Order by (date, title, menu_order, meta_value)
 *     @type string $order            Order direction (ASC, DESC)
 *     @type string $meta_key         Meta key for ordering
 *     @type bool   $show_filters     Show filter controls (grid/list layouts only)
 *     @type bool   $show_sorting     Show sorting dropdown
 *     @type bool   $show_pagination  Show pagination
 *     @type bool   $show_empty_state Show empty state when no posts
 *     @type string $section_id       Unique section ID
 *
 *     // Carousel specific options (when layout = 'carousel')
 *     @type bool   $show_navigation  Show carousel navigation
 *     @type bool   $show_dots        Show carousel dots
 *     @type bool   $autoplay         Enable autoplay
 *     @type int    $autoplay_speed   Autoplay speed in ms
 *     @type int    $slides_to_show   Number of slides to show
 *     @type array  $breakpoints      Responsive breakpoints
 *
 *     // Listing-specific parameters
 *     @type string $property_status    Property status ('active', 'pending', 'sold', 'draft')
 *     @type string $property_type      Property type ('single-family', 'condo', 'townhouse', 'land')
 *     @type int    $min_price          Minimum price filter
 *     @type int    $max_price          Maximum price filter
 *     @type int    $min_bedrooms       Minimum bedrooms
 *     @type int    $max_bedrooms       Maximum bedrooms
 *     @type int    $min_bathrooms      Minimum bathrooms
 *     @type int    $max_bathrooms      Maximum bathrooms
 *     @type int    $min_sqft           Minimum square footage
 *     @type int    $max_sqft           Maximum square footage
 *     @type string $city               Filter by city
 *     @type string $state              Filter by state
 *     @type string $zip_code           Filter by zip code
 *     @type bool   $featured_only      Show only featured listings
 *     @type bool   $open_house_only    Show only listings with open houses
 *     @type string $listing_agent      Filter by agent ID
 *     @type array  $exclude_ids        Exclude specific listing IDs
 *     @type bool   $recent_changes_only Show only listings with recent changes
 *     @type bool   $new_listings_only   Show only listings added within 14 days
 *     @type bool   $price_changes_only  Show only listings with recent price changes
 * }
 */

// Default arguments
$defaults = [
    'post_type' => 'post', // Post type to display (any valid post type)
    'layout' => 'grid', // Options: 'grid', 'list', 'carousel', 'masonry', 'featured'
    'title' => '', // Section title text
    'subtitle' => '', // Section subtitle text
    'badge' => '', // Small badge text above title
    'background' => 'white', // Options: 'white', 'light', 'dark', 'primary'
    'padding' => 'xl', // Options: 'sm', 'md', 'lg', 'xl', '2xl'
    'columns' => 3, // Number of grid columns (2-6)
    'query_args' => [], // Custom WP_Query arguments array
    'meta_query' => [], // Meta query filters array
    'tax_query' => [], // Taxonomy query filters array
    'card_variant' => 'default', // Options: 'default', 'elevated', 'bordered', 'minimal'
    'card_size' => 'md', // Options: 'sm', 'md', 'lg'
    'posts_per_page' => 12, // Number of posts to show (-1 for all)
    'orderby' => 'date', // Options: 'date', 'title', 'menu_order', 'meta_value', 'meta_value_num'
    'order' => 'DESC', // Options: 'ASC', 'DESC'
    'meta_key' => '', // Meta key for ordering (when orderby is meta_value)
    'show_filters' => false, // Boolean: true/false - show filter controls
    'show_sorting' => false, // Boolean: true/false - show sorting dropdown
    'show_pagination' => true, // Boolean: true/false - show pagination
    'show_empty_state' => true, // Boolean: true/false - show empty state when no posts
    'section_id' => 'universal-loop-' . uniqid(), // Unique section ID

    // Carousel options (when layout = 'carousel')
    'show_navigation' => true, // Boolean: true/false - show carousel navigation arrows
    'show_dots' => true, // Boolean: true/false - show carousel dots
    'autoplay' => false, // Boolean: true/false - enable autoplay
    'autoplay_speed' => 5000, // Autoplay speed in milliseconds (1000-10000)
    'slides_to_show' => 3, // Number of slides to show at once (1-6)
    'breakpoints' => [ // Responsive breakpoints configuration
        'mobile' => ['breakpoint' => 480, 'slides' => 1],
        'tablet' => ['breakpoint' => 768, 'slides' => 2],
        'desktop' => ['breakpoint' => 1024, 'slides' => 3]
    ],

    // Listing-specific parameters (when post_type = 'listing')
    'property_status' => '', // Options: 'active', 'pending', 'sold', 'draft'
    'property_type' => '', // Options: 'single-family', 'condo', 'townhouse', 'land'
    'min_price' => 0, // Minimum price filter (integer)
    'max_price' => 0, // Maximum price filter (integer)
    'min_bedrooms' => 0, // Minimum bedrooms (integer)
    'max_bedrooms' => 0, // Maximum bedrooms (integer)
    'min_bathrooms' => 0, // Minimum bathrooms (integer)
    'max_bathrooms' => 0, // Maximum bathrooms (integer)
    'min_sqft' => 0, // Minimum square footage (integer)
    'max_sqft' => 0, // Maximum square footage (integer)
    'city' => '', // Filter by city name
    'state' => '', // Filter by state name/code
    'zip_code' => '', // Filter by zip code
    'featured_only' => false, // Boolean: true/false - show only featured listings
    'open_house_only' => false, // Boolean: true/false - show only listings with open houses
    'listing_agent' => '', // Filter by agent ID
    'exclude_ids' => [], // Array of listing IDs to exclude

    // Change tracking filters (for listings)
    'recent_changes_only' => false, // Boolean: true/false - show only listings with recent changes
    'new_listings_only' => false, // Boolean: true/false - show only listings added within 14 days
    'price_changes_only' => false // Boolean: true/false - show only listings with recent price changes
];

$args = wp_parse_args($args ?? [], $defaults);

// Generate dynamic titles/subtitles for listing sections based on filters
if ($args['post_type'] === 'listing') {
    // Auto-generate title and subtitle if not provided, based on filters
    if (empty($args['title'])) {
        $args['title'] = hpt_generate_listing_section_title($args);
    }
    if (empty($args['subtitle'])) {
        $args['subtitle'] = hpt_generate_listing_section_subtitle($args);
    }
}

extract($args);

// For carousel layout, delegate to universal-carousel with enhanced parameters
if ($layout === 'carousel') {
    get_template_part('template-parts/components/universal-carousel', null, array_merge($args, [
        'title' => $title,
        'subtitle' => $subtitle,
        'card_variant' => $card_variant
    ]));
    return;
}

// Build WP_Query arguments for other layouts
$base_query_args = [
    'post_type' => $post_type,
    'posts_per_page' => $posts_per_page,
    'post_status' => 'publish',
    'orderby' => $orderby,
    'order' => $order,
    'no_found_rows' => false, // We need pagination
    'update_post_meta_cache' => true,
    'update_post_term_cache' => true
];

// Enhanced sorting for listings
if ($post_type === 'listing') {
    switch ($orderby) {
        case 'price':
            $base_query_args['meta_key'] = 'listing_price';
            $base_query_args['orderby'] = 'meta_value_num';
            break;
        case 'sqft':
            $base_query_args['meta_key'] = 'listing_sqft';
            $base_query_args['orderby'] = 'meta_value_num';
            break;
        case 'bedrooms':
            $base_query_args['meta_key'] = 'listing_bedrooms';
            $base_query_args['orderby'] = 'meta_value_num';
            break;
        case 'bathrooms':
            $base_query_args['meta_key'] = 'listing_bathrooms';
            $base_query_args['orderby'] = 'meta_value_num';
            break;
        case 'date_listed':
            $base_query_args['meta_key'] = 'listing_date';
            $base_query_args['orderby'] = 'meta_value';
            break;
        case 'featured':
            $base_query_args['meta_key'] = 'listing_featured';
            $base_query_args['orderby'] = ['meta_value' => 'DESC', 'date' => 'DESC'];
            break;
    }
}

// Add meta key for ordering if specified (fallback for other post types)
if ($meta_key && $post_type !== 'listing') {
    $base_query_args['meta_key'] = $meta_key;
}

// Handle pagination
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$base_query_args['paged'] = $paged;

// Process listing-specific parameters for listing post type
if ($post_type === 'listing') {
    $listing_meta_query = [];
    $listing_tax_query = [];

    // Property status filter
    if (!empty($property_status)) {
        $listing_tax_query[] = [
            'taxonomy' => 'property_status',
            'field' => 'slug',
            'terms' => $property_status
        ];
    }

    // Property type filter
    if (!empty($property_type)) {
        $listing_tax_query[] = [
            'taxonomy' => 'property_type',
            'field' => 'slug',
            'terms' => $property_type
        ];
    }

    // Price range filters
    if ($min_price > 0 || $max_price > 0) {
        $price_query = [
            'key' => 'listing_price',
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN'
        ];

        if ($min_price > 0 && $max_price > 0) {
            $price_query['value'] = [$min_price, $max_price];
        } elseif ($min_price > 0) {
            $price_query['value'] = $min_price;
            $price_query['compare'] = '>=';
        } elseif ($max_price > 0) {
            $price_query['value'] = $max_price;
            $price_query['compare'] = '<=';
        }

        $listing_meta_query[] = $price_query;
    }

    // Bedroom filters
    if ($min_bedrooms > 0 || $max_bedrooms > 0) {
        $bedroom_query = [
            'key' => 'listing_bedrooms',
            'type' => 'NUMERIC'
        ];

        if ($min_bedrooms > 0 && $max_bedrooms > 0) {
            $bedroom_query['value'] = [$min_bedrooms, $max_bedrooms];
            $bedroom_query['compare'] = 'BETWEEN';
        } elseif ($min_bedrooms > 0) {
            $bedroom_query['value'] = $min_bedrooms;
            $bedroom_query['compare'] = '>=';
        } elseif ($max_bedrooms > 0) {
            $bedroom_query['value'] = $max_bedrooms;
            $bedroom_query['compare'] = '<=';
        }

        $listing_meta_query[] = $bedroom_query;
    }

    // Bathroom filters
    if ($min_bathrooms > 0 || $max_bathrooms > 0) {
        $bathroom_query = [
            'key' => 'listing_bathrooms',
            'type' => 'NUMERIC'
        ];

        if ($min_bathrooms > 0 && $max_bathrooms > 0) {
            $bathroom_query['value'] = [$min_bathrooms, $max_bathrooms];
            $bathroom_query['compare'] = 'BETWEEN';
        } elseif ($min_bathrooms > 0) {
            $bathroom_query['value'] = $min_bathrooms;
            $bathroom_query['compare'] = '>=';
        } elseif ($max_bathrooms > 0) {
            $bathroom_query['value'] = $max_bathrooms;
            $bathroom_query['compare'] = '<=';
        }

        $listing_meta_query[] = $bathroom_query;
    }

    // Square footage filters
    if ($min_sqft > 0 || $max_sqft > 0) {
        $sqft_query = [
            'key' => 'listing_sqft',
            'type' => 'NUMERIC'
        ];

        if ($min_sqft > 0 && $max_sqft > 0) {
            $sqft_query['value'] = [$min_sqft, $max_sqft];
            $sqft_query['compare'] = 'BETWEEN';
        } elseif ($min_sqft > 0) {
            $sqft_query['value'] = $min_sqft;
            $sqft_query['compare'] = '>=';
        } elseif ($max_sqft > 0) {
            $sqft_query['value'] = $max_sqft;
            $sqft_query['compare'] = '<=';
        }

        $listing_meta_query[] = $sqft_query;
    }

    // Location filters
    if (!empty($city)) {
        $listing_meta_query[] = [
            'key' => 'listing_city',
            'value' => $city,
            'compare' => 'LIKE'
        ];
    }

    if (!empty($state)) {
        $listing_meta_query[] = [
            'key' => 'listing_state',
            'value' => $state,
            'compare' => '='
        ];
    }

    if (!empty($zip_code)) {
        $listing_meta_query[] = [
            'key' => 'listing_zip',
            'value' => $zip_code,
            'compare' => '='
        ];
    }

    // Featured listings only
    if ($featured_only) {
        $listing_meta_query[] = [
            'key' => 'listing_featured',
            'value' => '1',
            'compare' => '='
        ];
    }

    // Open house only
    if ($open_house_only) {
        $listing_meta_query[] = [
            'key' => 'listing_has_open_house',
            'value' => '1',
            'compare' => '='
        ];
    }

    // Filter by specific agent
    if (!empty($listing_agent)) {
        $listing_meta_query[] = [
            'key' => 'listing_agent',
            'value' => $listing_agent,
            'compare' => '='
        ];
    }

    // Change tracking filters
    if ($recent_changes_only) {
        $listing_meta_query[] = [
            'key' => '_listing_changes',
            'compare' => 'EXISTS'
        ];
    }

    if ($new_listings_only) {
        $listing_meta_query[] = [
            'key' => '_listing_first_published',
            'value' => current_time('timestamp') - (14 * DAY_IN_SECONDS), // 14 days ago
            'compare' => '>',
            'type' => 'NUMERIC'
        ];
    }

    if ($price_changes_only) {
        $listing_meta_query[] = [
            'key' => '_listing_changes',
            'value' => 'price_change',
            'compare' => 'LIKE'
        ];
    }

    // Exclude specific IDs
    if (!empty($exclude_ids)) {
        $base_query_args['post__not_in'] = $exclude_ids;
    }

    // Merge listing queries with existing queries
    if (!empty($listing_meta_query)) {
        $meta_query = array_merge($meta_query, $listing_meta_query);
        if (count($meta_query) > 1) {
            $meta_query['relation'] = 'AND';
        }
    }

    if (!empty($listing_tax_query)) {
        $tax_query = array_merge($tax_query, $listing_tax_query);
        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }
    }
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
$loop_query = new WP_Query($final_query_args);

// Post type configuration
$post_type_configs = [
    'listing' => [
        'icon' => 'hph-icon--home',
        'color_scheme' => 'primary',
        'empty_message' => 'No listings available at the moment.',
        'archive_link' => get_post_type_archive_link('listing')
    ],
    'agent' => [
        'icon' => 'hph-icon--user',
        'color_scheme' => 'secondary',
        'empty_message' => 'No agents available at the moment.',
        'archive_link' => get_post_type_archive_link('agent')
    ],
    'local_place' => [
        'icon' => 'hph-icon--location',
        'color_scheme' => 'tertiary',
        'empty_message' => 'No places available at the moment.',
        'archive_link' => get_post_type_archive_link('local_place')
    ],
    'local_event' => [
        'icon' => 'hph-icon--calendar',
        'color_scheme' => 'info',
        'empty_message' => 'No events available at the moment.',
        'archive_link' => get_post_type_archive_link('local_event')
    ],
    'community' => [
        'icon' => 'hph-icon--map',
        'color_scheme' => 'success',
        'empty_message' => 'No communities available at the moment.',
        'archive_link' => get_post_type_archive_link('community')
    ],
    'city' => [
        'icon' => 'hph-icon--location-alt',
        'color_scheme' => 'primary',
        'empty_message' => 'No cities available at the moment.',
        'archive_link' => get_post_type_archive_link('city')
    ],
    'open_house' => [
        'icon' => 'hph-icon--calendar-alt',
        'color_scheme' => 'orange',
        'empty_message' => 'No open houses available at the moment.',
        'archive_link' => get_post_type_archive_link('open_house')
    ],
    'staff' => [
        'icon' => 'hph-icon--users',
        'color_scheme' => 'secondary',
        'empty_message' => 'No staff members available at the moment.',
        'archive_link' => get_post_type_archive_link('staff')
    ],
    'post' => [
        'icon' => 'hph-icon--post',
        'color_scheme' => 'gray',
        'empty_message' => 'No posts available at the moment.',
        'archive_link' => get_permalink(get_option('page_for_posts'))
    ]
];

$config = $post_type_configs[$post_type] ?? $post_type_configs['post'];

// Background classes
$bg_classes = ['hph-section', 'hph-universal-loop'];
switch ($background) {
    case 'light':
        $bg_classes[] = 'hph-bg-gray-50';
        break;
    case 'dark':
        $bg_classes[] = 'hph-bg-gray-900 hph-text-white';
        break;
    case 'primary':
        $bg_classes[] = 'hph-bg-primary hph-text-white';
        break;
    default:
        $bg_classes[] = 'hph-bg-white';
}

// Padding classes
$padding_classes = [];
switch ($padding) {
    case 'sm': $padding_classes[] = 'hph-py-lg'; break;
    case 'md': $padding_classes[] = 'hph-py-xl'; break;
    case 'lg': $padding_classes[] = 'hph-py-2xl'; break;
    case 'xl': $padding_classes[] = 'hph-py-3xl'; break;
    case '2xl': $padding_classes[] = 'hph-py-4xl'; break;
    default: $padding_classes[] = 'hph-py-2xl';
}

$section_classes = implode(' ', array_merge($bg_classes, $padding_classes));

// Grid classes based on layout and columns
$grid_classes = [];
if ($layout === 'grid' || $layout === 'masonry') {
    switch ($columns) {
        case 2: $grid_classes[] = 'hph-grid hph-grid-cols-1 md:hph-grid-cols-2'; break;
        case 4: $grid_classes[] = 'hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-4'; break;
        case 5: $grid_classes[] = 'hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 xl:hph-grid-cols-5'; break;
        case 6: $grid_classes[] = 'hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 xl:hph-grid-cols-6'; break;
        default: $grid_classes[] = 'hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3'; // 3 columns
    }
    $grid_classes[] = 'hph-gap-lg';
}

if ($layout === 'masonry') {
    $grid_classes[] = 'hph-masonry';
}

// Card layout mapping
$card_layout_map = [
    'grid' => 'vertical',
    'list' => 'horizontal',
    'masonry' => 'vertical',
    'featured' => 'vertical'
];

$card_layout = $card_layout_map[$layout] ?? 'vertical';

// Layout mapping complete

// Enqueue required assets
wp_enqueue_style('hph-universal-loop', get_template_directory_uri() . '/assets/css/framework/components/organisms/universal-loop.css', ['hph-framework'], '1.0.0');
// wp_enqueue_script('hph-universal-loop', get_template_directory_uri() . '/assets/js/components/universal-loop.js', ['hph-framework'], '1.0.0', true);

if ($layout === 'masonry') {
    wp_enqueue_script('masonry');
}
?>

<section class="<?php echo esc_attr($section_classes); ?>"
         id="<?php echo esc_attr($section_id); ?>"
         data-post-type="<?php echo esc_attr($post_type); ?>"
         data-layout="<?php echo esc_attr($layout); ?>"
         data-color-scheme="<?php echo esc_attr($config['color_scheme']); ?>">

    <div class="hph-container">

        <?php if ($title || $subtitle || $badge): ?>
        <!-- Section Header -->
        <div class="hph-section-header" style="text-align: center; margin-bottom: var(--hph-space-12); max-width: 800px; margin-left: auto; margin-right: auto;">
            <?php if ($badge): ?>
            <div class="hph-section-badge" style="display: inline-block; padding: var(--hph-space-1) var(--hph-space-4); font-size: var(--hph-text-sm); font-weight: 600; color: var(--hph-primary); background: var(--hph-primary-light, rgba(var(--hph-primary-rgb), 0.1)); border-radius: 9999px; margin-bottom: var(--hph-space-4); text-transform: uppercase; letter-spacing: 0.05em;">
                <?php echo esc_html($badge); ?>
            </div>
            <?php endif; ?>

            <?php if ($title): ?>
            <h2 class="hph-section-title" style="font-size: clamp(1.875rem, 4vw, 3rem); font-weight: 700; line-height: 1.2; margin-bottom: var(--hph-space-4); color: inherit; letter-spacing: -0.02em;">
                <?php echo esc_html($title); ?>
            </h2>
            <?php endif; ?>

            <?php if ($subtitle): ?>
            <p class="hph-section-subtitle" style="font-size: var(--hph-text-lg); line-height: 1.6; color: var(--hph-gray-600); max-width: 600px; margin: 0 auto; font-weight: 400;">
                <?php echo esc_html($subtitle); ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($show_filters || $show_sorting): ?>
        <!-- Controls -->
        <div class="hph-loop-controls hph-flex hph-flex-col md:hph-flex-row hph-justify-between hph-items-start md:hph-items-center hph-mb-lg hph-gap-md">

            <?php if ($show_filters && ($layout === 'grid' || $layout === 'list')): ?>
            <div class="hph-view-controls hph-flex hph-items-center hph-gap-sm">
                <span class="hph-text-sm hph-text-gray-600 hph-mr-xs">View:</span>
                <div class="hph-view-toggle hph-flex hph-rounded-md hph-overflow-hidden hph-border">
                    <button class="hph-view-btn <?php echo $layout === 'grid' ? 'hph-view-btn-active' : ''; ?>"
                            data-view="grid"
                            data-target="<?php echo esc_attr($section_id); ?>">
                        <i class="hph-icon hph-icon--grid"></i>
                    </button>
                    <button class="hph-view-btn <?php echo $layout === 'list' ? 'hph-view-btn-active' : ''; ?>"
                            data-view="list"
                            data-target="<?php echo esc_attr($section_id); ?>">
                        <i class="hph-icon hph-icon--list"></i>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($show_sorting): ?>
            <div class="hph-sort-control hph-flex hph-items-center hph-gap-xs">
                <label class="hph-text-sm hph-text-gray-600">Sort:</label>
                <select class="hph-form-select hph-form-select-sm"
                        data-sort-posts
                        data-target="<?php echo esc_attr($section_id); ?>">
                    <option value="date" <?php selected($orderby, 'date'); ?>>Newest</option>
                    <option value="title" <?php selected($orderby, 'title'); ?>>A-Z</option>
                    <?php if ($post_type === 'listing'): ?>
                    <option value="price" <?php selected($meta_key, 'listing_price'); ?>>Price</option>
                    <?php endif; ?>
                </select>
            </div>
            <?php endif; ?>

        </div>
        <?php endif; ?>

        <?php if ($loop_query->have_posts()): ?>

        <!-- Results Info -->
        <?php if ($loop_query->found_posts > $posts_per_page): ?>
        <div class="hph-results-info hph-text-sm hph-text-gray-600 hph-mb-md">
            Showing <?php echo count($loop_query->posts); ?> of <?php echo $loop_query->found_posts; ?>
            <?php echo strtolower($post_type); ?><?php echo $loop_query->found_posts !== 1 ? 's' : ''; ?>
        </div>
        <?php endif; ?>

        <!-- Loop Container -->
        <div class="hph-loop-container hph-loop-container--<?php echo esc_attr($layout); ?> <?php echo esc_attr(implode(' ', $grid_classes)); ?>"
             data-layout="<?php echo esc_attr($layout); ?>">

            <?php while ($loop_query->have_posts()): $loop_query->the_post();
                $current_post_id = get_the_ID();

                // Prepare change tracking data for listings
                $component_args = [
                    'post_id' => $current_post_id,
                    'post_type' => $post_type,
                    'layout' => $card_layout,
                    'variant' => $card_variant,
                    'size' => $card_size,
                    'show_meta' => true,
                    'show_actions' => true,
                    'show_excerpt' => $layout !== 'list', // Show excerpt for grid/masonry, not list
                    'clickable' => true,
                    'hover_effects' => true
                ];

                // Add change tracking data for listings
                if ($post_type === 'listing') {
                    $component_args['listing_changes'] = function_exists('hpt_bridge_get_listing_changes') ? hpt_bridge_get_listing_changes($current_post_id) : [];
                    $component_args['listing_badges'] = function_exists('hpt_bridge_get_listing_badges') ? hpt_bridge_get_listing_badges($current_post_id, 2) : [];
                    $component_args['has_recent_changes'] = function_exists('hpt_bridge_has_recent_changes') ? hpt_bridge_has_recent_changes($current_post_id) : false;
                    $component_args['is_new_listing'] = function_exists('hpt_is_new_listing') ? hpt_is_new_listing($current_post_id) : false;
                }
            ?>
            <div class="hph-loop-item hph-loop-item--<?php echo esc_attr($card_size); ?>">
                <?php hph_component('universal-card', $component_args); ?>
            </div>
            <?php endwhile; ?>

        </div>

        <?php if ($show_pagination && $loop_query->max_num_pages > 1): ?>
        <!-- Pagination -->
        <div class="hph-pagination-wrapper hph-mt-xl hph-pt-lg hph-border-t hph-text-center">
            <?php
            echo paginate_links([
                'total' => $loop_query->max_num_pages,
                'current' => $paged,
                'prev_text' => '<i class="hph-icon hph-icon--arrow-left"></i> Previous',
                'next_text' => 'Next <i class="hph-icon hph-icon--arrow-right"></i>',
                'type' => 'list',
                'end_size' => 2,
                'mid_size' => 2,
                'class' => 'hph-pagination'
            ]);
            ?>
        </div>
        <?php endif; ?>

        <?php else: ?>

        <?php if ($show_empty_state): ?>
        <!-- Empty State -->
        <div class="hph-empty-state hph-text-center hph-py-xl">
            <div class="hph-empty-icon hph-mb-lg">
                <i class="<?php echo esc_attr($config['icon']); ?> hph-text-6xl hph-text-gray-300"></i>
            </div>
            <h3 class="hph-text-xl hph-font-semibold hph-text-gray-900 hph-mb-md">
                No <?php echo ucfirst($post_type); ?>s Found
            </h3>
            <p class="hph-text-gray-600 hph-mb-lg">
                <?php echo esc_html($config['empty_message']); ?>
            </p>
            <?php if ($config['archive_link']): ?>
            <a href="<?php echo esc_url($config['archive_link']); ?>"
               class="hph-btn hph-btn-primary">
                View All <?php echo ucfirst($post_type); ?>s
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>

    </div>

    <?php if ($config['archive_link'] && $loop_query->have_posts()): ?>
    <!-- View All Link -->
    <div class="hph-section-footer hph-text-center hph-mt-xl">
        <a href="<?php echo esc_url($config['archive_link']); ?>"
           class="hph-btn hph-btn-outline-primary hph-btn-lg">
            View All <?php echo ucfirst($post_type); ?>s
            <i class="hph-icon hph-icon--arrow-right hph-ml-sm"></i>
        </a>
    </div>
    <?php endif; ?>

</section>

<?php
wp_reset_postdata();

// Initialize masonry if needed
if ($layout === 'masonry') {
    add_action('wp_footer', function() use ($section_id) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('#" . esc_js($section_id) . " .hph-masonry');
            if (container && typeof Masonry !== 'undefined') {
                new Masonry(container, {
                    itemSelector: '.hph-loop-item',
                    columnWidth: '.hph-loop-item',
                    gutter: 24,
                    percentPosition: true
                });
            }
        });
        </script>";
    });
}
?>