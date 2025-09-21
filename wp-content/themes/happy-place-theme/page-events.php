<?php
/**
 * Template Name: Events Page Template
 *
 * Displays open houses and other events using existing archive patterns and BEM methodology
 *
 * @package HappyPlaceTheme
 * @version 2.0.0
 */

// Load events-specific CSS
add_action('wp_enqueue_scripts', function() {
    // Load events archive CSS
    $events_css_path = '/assets/css/framework/features/events/events-archive.css';
    if (file_exists(get_template_directory() . $events_css_path)) {
        wp_enqueue_style('hph-events-archive',
            get_template_directory_uri() . $events_css_path,
            ['hph-framework'],
            filemtime(get_template_directory() . $events_css_path)
        );
    }

    // Also load archive bundle if available (includes existing archive styles)
    $archive_css_bundle = '/dist/css/archive.min.css';
    if (file_exists(get_template_directory() . $archive_css_bundle)) {
        wp_enqueue_style('hph-archive-bundle',
            get_template_directory_uri() . $archive_css_bundle,
            ['hph-framework'],
            filemtime(get_template_directory() . $archive_css_bundle)
        );
    }
}, 15);

// Add body class for consistent styling
add_filter('body_class', function($classes) {
    $classes[] = 'archive-events';
    $classes[] = 'page-events';
    return $classes;
});

get_header();

// Get current view mode (default: grid)
$view_mode = isset($_GET['view']) ? sanitize_key($_GET['view']) : 'grid';
$valid_views = ['grid', 'list'];
if (!in_array($view_mode, $valid_views)) {
    $view_mode = 'grid';
}

// Get event type filter
$event_type = isset($_GET['event_type']) ? sanitize_key($_GET['event_type']) : 'all';
$valid_event_types = ['all', 'open_house', 'local_event'];
if (!in_array($event_type, $valid_event_types)) {
    $event_type = 'all';
}

// Get filter parameters
$date_filter = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
$agent_filter = isset($_GET['agent']) ? sanitize_text_field($_GET['agent']) : '';
$city_filter = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '';
$sort = isset($_GET['sort']) ? sanitize_key($_GET['sort']) : 'date_asc';

// Setup query args based on event type
$post_types = [];
$additional_meta_query = [];

switch ($event_type) {
    case 'open_house':
        $post_types = ['open_house'];
        break;
    case 'local_event':
        $post_types = ['local_event'];
        break;
    default:
        $post_types = ['open_house', 'local_event'];
        break;
}

// Build meta query for filters
$meta_query = array('relation' => 'AND');

if (!empty($date_filter)) {
    $meta_query[] = array(
        'relation' => 'OR',
        array(
            'key' => 'event_date',
            'value' => $date_filter,
            'compare' => '='
        ),
        array(
            'key' => 'start_date',
            'value' => $date_filter,
            'compare' => '='
        )
    );
}

if (!empty($agent_filter)) {
    $meta_query[] = array(
        'key' => 'hosting_agent',
        'value' => $agent_filter,
        'compare' => '='
    );
}

// Setup sorting
$orderby = 'meta_value';
$meta_key = ($event_type === 'open_house') ? 'start_date' : 'event_date';
$order = 'ASC';

switch ($sort) {
    case 'date_desc':
        $order = 'DESC';
        break;
    case 'date_asc':
        $order = 'ASC';
        break;
    case 'title_asc':
        $orderby = 'title';
        $meta_key = '';
        $order = 'ASC';
        break;
    case 'title_desc':
        $orderby = 'title';
        $meta_key = '';
        $order = 'DESC';
        break;
}

// Query events
$events_query = new WP_Query(array(
    'post_type' => $post_types,
    'posts_per_page' => 20,
    'post_status' => 'publish',
    'meta_query' => $meta_query,
    'orderby' => $orderby,
    'meta_key' => $meta_key,
    'order' => $order,
    'paged' => get_query_var('paged', 1)
));

// Get stats for hero
$today_open_houses = get_posts(array(
    'post_type' => 'open_house',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => array(
        array(
            'key' => 'start_date',
            'value' => date('Y-m-d'),
            'compare' => '='
        )
    ),
    'fields' => 'ids'
));

$today_events = get_posts(array(
    'post_type' => 'local_event',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => array(
        array(
            'key' => 'event_date',
            'value' => date('Y-m-d'),
            'compare' => '='
        )
    ),
    'fields' => 'ids'
));

$today_count = count($today_open_houses) + count($today_events);
$total_count = $events_query->found_posts;

// Setup active filters for display
$search_terms = array();
if (!empty($date_filter)) {
    $search_terms[] = 'Date: ' . date('F j, Y', strtotime($date_filter));
}
if (!empty($agent_filter)) {
    $agent = get_user_by('ID', $agent_filter);
    if ($agent) {
        $search_terms[] = 'Agent: ' . $agent->display_name;
    }
}
if (!empty($city_filter)) {
    $search_terms[] = 'City: ' . $city_filter;
}
if ($event_type !== 'all') {
    $search_terms[] = 'Type: ' . ucwords(str_replace('_', ' ', $event_type));
}
?>

<!-- Archive Hero Section using existing template part -->
<?php
get_template_part('template-parts/sections/archive-hero', '', array(
    'headline' => 'Events & Open Houses',
    'subheadline' => 'Discover upcoming open houses, community events, and local happenings',
    'badge' => 'Events Calendar',
    'badge_icon' => 'fas fa-calendar-alt',
    'show_search' => false, // Disable search form since we have custom filters
    'show_stats' => true,
    'stats' => array(
        'events' => $total_count,
        'today' => $today_count,
        'open_houses' => count($today_open_houses),
        'local_events' => count($today_events)
    ),
    'theme' => 'primary',
    'height' => 'md'
));
?>

<!-- Archive Controls Section -->
<div class="hph-archive-controls-section" data-hide-in-views="map">
    <div class="hph-container">
        <div class="hph-archive-controls">

            <!-- Left: Results Count & Filter Summary -->
            <div class="hph-archive-controls__left">
                <div class="hph-results-summary" data-results-text>
                    <strong data-results-count><?php echo number_format($events_query->found_posts); ?></strong>
                    <span>events found</span>
                </div>

                <?php if (!empty($search_terms)) : ?>
                <div class="hph-active-filters">
                    <span class="hph-active-filters__label">Filters:</span>
                    <?php foreach ($search_terms as $term) : ?>
                        <span class="hph-active-filters__tag"><?php echo esc_html($term); ?></span>
                    <?php endforeach; ?>
                    <button type="button" class="hph-active-filters__clear" onclick="window.location.href='<?php echo get_permalink(); ?>'">
                        Clear All <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Sort & View Controls -->
            <div class="hph-archive-controls__right">

                <!-- Event Type Filter -->
                <div class="hph-event-type-controls">
                    <div class="hph-event-type-filter" role="group" aria-label="Event type filter">
                        <a href="<?php echo esc_url(add_query_arg('event_type', 'all')); ?>"
                           class="hph-event-type-filter__btn <?php echo $event_type === 'all' ? 'hph-event-type-filter__btn--active' : ''; ?>">
                            All Events
                        </a>
                        <a href="<?php echo esc_url(add_query_arg('event_type', 'open_house')); ?>"
                           class="hph-event-type-filter__btn <?php echo $event_type === 'open_house' ? 'hph-event-type-filter__btn--active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            Open Houses
                        </a>
                        <a href="<?php echo esc_url(add_query_arg('event_type', 'local_event')); ?>"
                           class="hph-event-type-filter__btn <?php echo $event_type === 'local_event' ? 'hph-event-type-filter__btn--active' : ''; ?>">
                            <i class="fas fa-map-marker-alt"></i>
                            Local Events
                        </a>
                    </div>
                </div>

                <!-- Sort Dropdown -->
                <div class="hph-sort-controls">
                    <label for="events-sort" class="hph-sort-controls__label">Sort by:</label>
                    <select id="events-sort" name="sort" class="hph-sort-controls__select" data-sort-select>
                        <option value="date_asc" <?php selected($sort, 'date_asc'); ?>>Date: Upcoming First</option>
                        <option value="date_desc" <?php selected($sort, 'date_desc'); ?>>Date: Latest First</option>
                        <option value="title_asc" <?php selected($sort, 'title_asc'); ?>>Title: A to Z</option>
                        <option value="title_desc" <?php selected($sort, 'title_desc'); ?>>Title: Z to A</option>
                    </select>
                </div>

                <!-- View Toggle Controls -->
                <div class="hph-view-controls">
                    <div class="hph-view-controls__group" role="group" aria-label="View options">
                        <button type="button"
                                class="hph-view-controls__btn <?php echo $view_mode === 'grid' ? 'hph-view-controls__btn--active' : ''; ?>"
                                data-view="grid"
                                title="Grid View"
                                aria-pressed="<?php echo $view_mode === 'grid' ? 'true' : 'false'; ?>">
                            <i class="fas fa-th-large"></i>
                            <span class="sr-only">Grid</span>
                        </button>
                        <button type="button"
                                class="hph-view-controls__btn <?php echo $view_mode === 'list' ? 'hph-view-controls__btn--active' : ''; ?>"
                                data-view="list"
                                title="List View"
                                aria-pressed="<?php echo $view_mode === 'list' ? 'true' : 'false'; ?>">
                            <i class="fas fa-list"></i>
                            <span class="sr-only">List</span>
                        </button>
                    </div>
                </div>

                <!-- Filter Toggle -->
                <button class="hph-filter-toggle"
                        data-toggle="collapse"
                        data-target="#events-filters"
                        aria-expanded="false"
                        aria-controls="events-filters">
                    <i class="fas fa-filter"></i>
                    Filters
                    <i class="fas fa-chevron-down hph-filter-toggle__icon"></i>
                </button>

            </div>

        </div>

        <!-- Advanced Filters Panel -->
        <div id="events-filters" class="hph-archive-filters collapse">
            <form method="get" class="hph-archive-filters__form">
                <input type="hidden" name="view" value="<?php echo esc_attr($view_mode); ?>">
                <input type="hidden" name="event_type" value="<?php echo esc_attr($event_type); ?>">
                <input type="hidden" name="sort" value="<?php echo esc_attr($sort); ?>">

                <div class="hph-archive-filters__grid">

                    <!-- Date Filter -->
                    <div class="hph-archive-filters__group">
                        <label for="date-filter" class="hph-archive-filters__label">Date</label>
                        <input type="date"
                               id="date-filter"
                               name="date"
                               class="hph-archive-filters__input"
                               value="<?php echo esc_attr($date_filter); ?>">
                    </div>

                    <!-- Agent Filter -->
                    <?php if ($event_type === 'all' || $event_type === 'open_house') : ?>
                    <div class="hph-archive-filters__group">
                        <label for="agent-filter" class="hph-archive-filters__label">Agent</label>
                        <select id="agent-filter" name="agent" class="hph-archive-filters__select">
                            <option value="">All Agents</option>
                            <?php
                            $agents = get_users(array('role' => 'agent', 'orderby' => 'display_name'));
                            foreach ($agents as $agent) :
                            ?>
                            <option value="<?php echo esc_attr($agent->ID); ?>" <?php selected($agent_filter, $agent->ID); ?>>
                                <?php echo esc_html($agent->display_name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- City Filter -->
                    <div class="hph-archive-filters__group">
                        <label for="city-filter" class="hph-archive-filters__label">City</label>
                        <input type="text"
                               id="city-filter"
                               name="city"
                               class="hph-archive-filters__input"
                               placeholder="Enter city..."
                               value="<?php echo esc_attr($city_filter); ?>">
                    </div>

                </div>

                <div class="hph-archive-filters__actions">
                    <button type="submit" class="hph-archive-filters__submit">
                        <i class="fas fa-search"></i>
                        Apply Filters
                    </button>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="hph-archive-filters__reset">
                        Reset
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Standard Views Container -->
<div class="hph-standard-views-container" data-hide-in-views="map">
    <?php if ($events_query->have_posts()) : ?>
    <div class="hph-events-container">
        <div class="hph-container">

            <!-- Grid View -->
            <div class="hph-events-grid hph-view-content <?php echo $view_mode === 'grid' ? 'active' : ''; ?>"
                 data-view-content="grid"
                 data-ajax-results="grid">
                <?php while ($events_query->have_posts()) : $events_query->the_post(); ?>
                    <?php
                    $post_type = get_post_type();

                    if ($post_type === 'open_house') {
                        // Check if open house card template exists
                        $template_exists = locate_template('template-parts/components/open-house-card.php');
                        if ($template_exists) {
                            get_template_part('template-parts/components/open-house-card', '', array(
                                'post_id' => get_the_ID(),
                                'view_mode' => 'grid'
                            ));
                        } else {
                            // Fallback to listing card showing related listing
                            $listing_id = hpt_get_open_house_listing(get_the_ID());
                            if ($listing_id) {
                                get_template_part('template-parts/listing-card', '', array(
                                    'post_id' => $listing_id,
                                    'view_mode' => 'grid',
                                    'show_open_house' => true,
                                    'open_house_id' => get_the_ID()
                                ));
                            }
                        }
                    } elseif ($post_type === 'local_event') {
                        get_template_part('template-parts/components/local/event-card', '', array(
                            'post_id' => get_the_ID(),
                            'view_mode' => 'grid'
                        ));
                    }
                    ?>
                <?php endwhile; ?>
            </div>

            <!-- List View -->
            <div class="hph-events-list hph-view-content <?php echo $view_mode === 'list' ? 'active' : ''; ?>"
                 data-view-content="list"
                 data-ajax-results="list">
                <?php
                // Reset query for list view
                $events_query->rewind_posts();
                while ($events_query->have_posts()) : $events_query->the_post();
                ?>
                    <?php
                    $post_type = get_post_type();

                    if ($post_type === 'open_house') {
                        // Check if open house card template exists
                        $template_exists = locate_template('template-parts/components/open-house-card.php');
                        if ($template_exists) {
                            get_template_part('template-parts/components/open-house-card', '', array(
                                'post_id' => get_the_ID(),
                                'view_mode' => 'list'
                            ));
                        } else {
                            // Fallback to listing card showing related listing
                            $listing_id = hpt_get_open_house_listing(get_the_ID());
                            if ($listing_id) {
                                get_template_part('template-parts/listing-card', '', array(
                                    'post_id' => $listing_id,
                                    'view_mode' => 'list',
                                    'show_open_house' => true,
                                    'open_house_id' => get_the_ID()
                                ));
                            }
                        }
                    } elseif ($post_type === 'local_event') {
                        get_template_part('template-parts/components/local/event-card', '', array(
                            'post_id' => get_the_ID(),
                            'view_mode' => 'list'
                        ));
                    }
                    ?>
                <?php endwhile; ?>
            </div>

        </div>
    </div>

    <!-- Pagination -->
    <?php if ($events_query->max_num_pages > 1) : ?>
    <div class="hph-pagination-section">
        <div class="hph-container">
            <div class="hph-pagination-container">
                <?php
                echo paginate_links(array(
                    'total' => $events_query->max_num_pages,
                    'current' => max(1, get_query_var('paged')),
                    'prev_text' => '<i class="fas fa-chevron-left"></i> Previous',
                    'next_text' => 'Next <i class="fas fa-chevron-right"></i>',
                    'format' => '?paged=%#%',
                    'add_args' => array(
                        'view' => $view_mode,
                        'event_type' => $event_type,
                        'date' => $date_filter,
                        'agent' => $agent_filter,
                        'city' => $city_filter,
                        'sort' => $sort
                    )
                ));
                ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php else : ?>

    <!-- No Results -->
    <div class="hph-no-results">
        <div class="hph-container">
            <div class="hph-no-results__content">
                <div class="hph-no-results__icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h2 class="hph-no-results__title">No Events Found</h2>
                <p class="hph-no-results__description">
                    We couldn't find any events matching your criteria. Try adjusting your filters or check back later for new events.
                </p>
                <div class="hph-no-results__actions">
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="hph-no-results__btn hph-no-results__btn--primary">
                        View All Events
                    </a>
                    <a href="<?php echo home_url('/listing/'); ?>" class="hph-no-results__btn hph-no-results__btn--secondary">
                        Browse Properties
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
// Archive view switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('[data-view]');
    const viewContents = document.querySelectorAll('[data-view-content]');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.getAttribute('data-view');

            // Update active states
            viewButtons.forEach(btn => {
                btn.classList.remove('hph-view-controls__btn--active');
                btn.setAttribute('aria-pressed', 'false');
            });
            this.classList.add('hph-view-controls__btn--active');
            this.setAttribute('aria-pressed', 'true');

            // Show/hide content
            viewContents.forEach(content => {
                content.classList.remove('active');
            });
            document.querySelector(`[data-view-content="${view}"]`).classList.add('active');

            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('view', view);
            window.history.pushState({}, '', url);
        });
    });

    // Sort functionality
    const sortSelect = document.querySelector('[data-sort-select]');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('sort', this.value);
            window.location = url.toString();
        });
    }
});
</script>

<?php
wp_reset_postdata();
get_footer();
?>