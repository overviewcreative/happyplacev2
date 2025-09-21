<?php
/**
 * Archive Template for Local Events
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 */

// Enqueue required assets
wp_enqueue_style('hph-local-events', get_template_directory_uri() . '/assets/css/framework/features/local/local-archive.css', ['hph-framework'], '1.0.0');
wp_enqueue_style('hph-event-timeline', get_template_directory_uri() . '/assets/css/framework/features/local/event-timeline.css', ['hph-framework'], '1.0.0');
wp_enqueue_script('hph-local-filters', get_template_directory_uri() . '/assets/js/components/local/local-filters.js', ['hph-framework'], '1.0.0', true);
wp_enqueue_script('hph-archive-events', get_template_directory_uri() . '/assets/js/pages/archive-events.js', ['hph-framework'], '1.0.0', true);

// Build query args from filters
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$posts_per_page = 20;

// Base query args
$query_args = [
    'post_type' => 'local_event',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_status' => 'publish',
    'meta_key' => 'start_datetime',
    'orderby' => 'meta_value',
    'order' => 'ASC'
];

// Apply search filter
if (!empty($_GET['search'])) {
    $query_args['s'] = sanitize_text_field($_GET['search']);
}

// Build meta query
$meta_query = [];

// Date filter logic
$date_filter = $_GET['date'] ?? '';
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

switch ($date_filter) {
    case 'today':
        $meta_query[] = [
            'key' => 'start_datetime',
            'value' => [$today . ' 00:00:00', $today . ' 23:59:59'],
            'compare' => 'BETWEEN',
            'type' => 'DATETIME'
        ];
        break;
        
    case 'tomorrow':
        $meta_query[] = [
            'key' => 'start_datetime',
            'value' => [$tomorrow . ' 00:00:00', $tomorrow . ' 23:59:59'],
            'compare' => 'BETWEEN',
            'type' => 'DATETIME'
        ];
        break;
        
    case 'this-week':
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime('sunday this week'));
        $meta_query[] = [
            'key' => 'start_datetime',
            'value' => [$week_start . ' 00:00:00', $week_end . ' 23:59:59'],
            'compare' => 'BETWEEN',
            'type' => 'DATETIME'
        ];
        break;
        
    case 'this-weekend':
        $friday = date('Y-m-d', strtotime('friday this week'));
        $sunday = date('Y-m-d', strtotime('sunday this week'));
        $meta_query[] = [
            'key' => 'start_datetime',
            'value' => [$friday . ' 00:00:00', $sunday . ' 23:59:59'],
            'compare' => 'BETWEEN',
            'type' => 'DATETIME'
        ];
        break;
        
    case 'next-week':
        $next_monday = date('Y-m-d', strtotime('monday next week'));
        $next_sunday = date('Y-m-d', strtotime('sunday next week'));
        $meta_query[] = [
            'key' => 'start_datetime',
            'value' => [$next_monday . ' 00:00:00', $next_sunday . ' 23:59:59'],
            'compare' => 'BETWEEN',
            'type' => 'DATETIME'
        ];
        break;
        
    case 'this-month':
        $month_start = date('Y-m-01');
        $month_end = date('Y-m-t');
        $meta_query[] = [
            'key' => 'start_datetime',
            'value' => [$month_start . ' 00:00:00', $month_end . ' 23:59:59'],
            'compare' => 'BETWEEN',
            'type' => 'DATETIME'
        ];
        break;
        
    default:
        // Show upcoming events by default (not past events)
        $meta_query[] = [
            'key' => 'start_datetime',
            'value' => date('Y-m-d H:i:s'),
            'compare' => '>=',
            'type' => 'DATETIME'
        ];
}

// City filter
if (!empty($_GET['city'])) {
    $meta_query[] = [
        'key' => 'primary_city',
        'value' => intval($_GET['city']),
        'compare' => '='
    ];
}

// Category filter
if (!empty($_GET['category'])) {
    $meta_query[] = [
        'key' => 'event_category',
        'value' => sanitize_text_field($_GET['category']),
        'compare' => '='
    ];
}

// Free events filter
if (!empty($_GET['free_only'])) {
    $meta_query[] = [
        'key' => 'is_free',
        'value' => '1',
        'compare' => '='
    ];
}

if (!empty($meta_query)) {
    $query_args['meta_query'] = $meta_query;
}

// Execute query
$events_query = new WP_Query($query_args);

// Group events by date for timeline view
$events_by_date = [];
if ($events_query->have_posts()) {
    while ($events_query->have_posts()) {
        $events_query->the_post();
        $start_datetime = get_field('start_datetime');
        $event_date = date('Y-m-d', strtotime($start_datetime));
        
        if (!isset($events_by_date[$event_date])) {
            $events_by_date[$event_date] = [];
        }
        
        $events_by_date[$event_date][] = get_the_ID();
    }
    wp_reset_postdata();
}

get_header();
?>

<div class="hph-archive hph-archive--events">
    
    <!-- Archive Hero -->
    <section class="hph-archive__hero hph-archive__hero--events">
        <div class="hph-container">
            <div class="hph-archive__hero-content">
                <h1 class="hph-archive__title">Local Events & Activities</h1>
                <p class="hph-archive__subtitle">Discover what's happening in your community</p>
                
                <!-- Quick Date Filters -->
                <div class="hph-quick-filters">
                    <a href="?date=today" class="hph-btn hph-btn--outline <?php echo $date_filter === 'today' ? 'is-active' : ''; ?>">
                        Today
                    </a>
                    <a href="?date=tomorrow" class="hph-btn hph-btn--outline <?php echo $date_filter === 'tomorrow' ? 'is-active' : ''; ?>">
                        Tomorrow
                    </a>
                    <a href="?date=this-weekend" class="hph-btn hph-btn--outline <?php echo $date_filter === 'this-weekend' ? 'is-active' : ''; ?>">
                        This Weekend
                    </a>
                    <a href="?date=this-week" class="hph-btn hph-btn--outline <?php echo $date_filter === 'this-week' ? 'is-active' : ''; ?>">
                        This Week
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Filters Section -->
    <section class="hph-archive__filters">
        <div class="hph-container">
            <?php get_template_part('template-parts/components/local/local-filters', null, [
                'post_type' => 'local_event',
                'show_map_toggle' => false,
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
                    <?php if ($events_query->found_posts > 0): ?>
                        <span class="hph-archive__count-number"><?php echo $events_query->found_posts; ?></span>
                        <span class="hph-archive__count-label">
                            <?php echo $events_query->found_posts === 1 ? 'Event Found' : 'Events Found'; ?>
                        </span>
                    <?php else: ?>
                        <span class="hph-archive__count-label">No events found</span>
                    <?php endif; ?>
                </div>
                
                <div class="hph-archive__controls">
                    <div class="hph-view-toggle" data-view-toggle>
                        <button type="button" class="hph-view-toggle__btn is-active" data-view="timeline">
                            <i class="hph-icon hph-icon--timeline"></i>
                            Timeline
                        </button>
                        <button type="button" class="hph-view-toggle__btn" data-view="grid">
                            <i class="hph-icon hph-icon--grid"></i>
                            Grid
                        </button>
                        <button type="button" class="hph-view-toggle__btn" data-view="calendar">
                            <i class="hph-icon hph-icon--calendar"></i>
                            Calendar
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Archive Layout Container -->
            <div class="hph-archive__layout" data-archive-layout="timeline">
                
                <?php if ($events_query->have_posts()): ?>
                
                <!-- Timeline View (default) -->
                <div class="hph-events-timeline" data-view-content="timeline">
                    <?php foreach ($events_by_date as $date => $event_ids): ?>
                        <?php
                        $display_date = date('l, F j, Y', strtotime($date));
                        $is_today = $date === date('Y-m-d');
                        $is_tomorrow = $date === date('Y-m-d', strtotime('+1 day'));
                        ?>
                        
                        <div class="hph-timeline__section">
                            <div class="hph-timeline__date-marker">
                                <span class="hph-timeline__date">
                                    <?php echo esc_html($display_date); ?>
                                </span>
                                <?php if ($is_today): ?>
                                    <span class="hph-badge hph-badge--today">Today</span>
                                <?php elseif ($is_tomorrow): ?>
                                    <span class="hph-badge hph-badge--tomorrow">Tomorrow</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="hph-timeline__events">
                                <?php foreach ($event_ids as $event_id): ?>
                                    <div class="hph-timeline__item">
                                        <?php get_template_part('template-parts/components/local/event-card', null, [
                                            'event_id' => $event_id,
                                            'variant' => 'timeline'
                                        ]); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Grid View (hidden by default) -->
                <div class="hph-archive__grid hph-grid hph-grid--2" data-view-content="grid" style="display: none;">
                    <?php 
                    $events_query->rewind_posts();
                    while ($events_query->have_posts()): $events_query->the_post(); 
                    ?>
                        <div class="hph-grid__item">
                            <?php get_template_part('template-parts/components/local/event-card', null, [
                                'event_id' => get_the_ID(),
                                'variant' => 'default'
                            ]); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Calendar View (hidden by default) -->
                <div class="hph-events-calendar" data-view-content="calendar" style="display: none;">
                    <div class="hph-calendar" id="events-calendar" data-events='<?php echo json_encode($events_by_date); ?>'>
                        <!-- Calendar will be rendered by JavaScript -->
                        <div class="hph-calendar__loading">
                            <i class="hph-icon hph-icon--spinner"></i>
                            Loading calendar...
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                
                <!-- No Results -->
                <div class="hph-archive__empty">
                    <div class="hph-empty-state">
                        <i class="hph-empty-state__icon hph-icon hph-icon--calendar-empty"></i>
                        <h2 class="hph-empty-state__title">No Events Found</h2>
                        <p class="hph-empty-state__text">
                            <?php if ($date_filter): ?>
                                There are no events scheduled for the selected time period.
                            <?php else: ?>
                                Try adjusting your filters to find upcoming events.
                            <?php endif; ?>
                        </p>
                        <a href="<?php echo esc_url(get_post_type_archive_link('local_event')); ?>" class="hph-btn hph-btn--primary">
                            View All Events
                        </a>
                    </div>
                </div>
                
                <?php endif; ?>
                
                <?php wp_reset_postdata(); ?>
                
            </div>
            
            <!-- Pagination -->
            <?php if ($events_query->max_num_pages > 1): ?>
            <div class="hph-archive__pagination">
                <?php
                $pagination_args = [
                    'total' => $events_query->max_num_pages,
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
    
    <!-- Newsletter CTA -->
    <section class="hph-archive__cta">
        <div class="hph-container">
            <div class="hph-cta hph-cta--newsletter">
                <div class="hph-cta__content">
                    <h2 class="hph-cta__title">Never Miss an Event</h2>
                    <p class="hph-cta__text">Get weekly updates about upcoming events in your area.</p>
                </div>
                <form class="hph-cta__form hph-newsletter-form" action="/newsletter-signup" method="post">
                    <input type="email" name="email" class="hph-input" placeholder="Enter your email" required>
                    <button type="submit" class="hph-btn hph-btn--primary">Subscribe</button>
                </form>
            </div>
        </div>
    </section>
    
</div>

<?php get_footer(); ?>
