<?php
/**
 * Universal Calendar Component - Fixed for Open House Post Type
 *
 * @package HappyPlaceTheme
 */

// Ensure we have the required arguments
if (!isset($args) || !is_array($args)) {
    return;
}

// Set defaults for open house calendar
$defaults = [
    'view' => 'month',
    'post_type' => 'open_house', // Changed from 'listing' to 'open_house'
    'current_date' => date('Y-m-d'),
    'calendar_id' => 'hph-calendar-' . wp_generate_uuid4(),
    'css_classes' => [],
    'show_navigation' => true,
    'show_view_selector' => true,
    'meta_query' => [],
    'additional_meta_query' => []
];

$args = wp_parse_args($args, $defaults);

// Sanitize inputs
$view = sanitize_key($args['view']);
$post_type = sanitize_key($args['post_type']);
$current_date = sanitize_text_field($args['current_date']);
$calendar_id = sanitize_key($args['calendar_id']);

// Parse current date
$date_obj = DateTime::createFromFormat('Y-m-d', $current_date);
if (!$date_obj) {
    $date_obj = new DateTime();
}

$current_year = $date_obj->format('Y');
$current_month = $date_obj->format('n');
$current_day = $date_obj->format('j');

// Calculate calendar navigation dates
$prev_month = clone $date_obj;
$prev_month->modify('-1 month');
$next_month = clone $date_obj;
$next_month->modify('+1 month');

// Build CSS classes
$css_classes = array_merge(['hph-universal-calendar', 'hph-calendar-view-' . $view], $args['css_classes']);
$css_class_string = implode(' ', array_map('sanitize_html_class', $css_classes));

// Query events based on view
$events_data = hph_get_calendar_open_houses($args);
?>

<div id="<?php echo esc_attr($calendar_id); ?>" class="<?php echo esc_attr($css_class_string); ?>" data-view="<?php echo esc_attr($view); ?>" data-date="<?php echo esc_attr($current_date); ?>">
    
    <?php if ($args['show_navigation'] || $args['show_view_selector']) : ?>
    <!-- Calendar Header -->
    <div class="hph-calendar-header">
        <h2 class="hph-calendar-title">
            <?php echo esc_html($date_obj->format('F Y')); ?>
        </h2>
        
        <div class="hph-calendar-nav">
            <?php if ($args['show_navigation']) : ?>
            <button class="hph-calendar-nav-btn hph-calendar-prev" 
                    data-date="<?php echo esc_attr($prev_month->format('Y-m-d')); ?>"
                    aria-label="<?php esc_attr_e('Previous month', 'happy-place-theme'); ?>">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            
            <button class="hph-calendar-nav-btn hph-calendar-today" 
                    data-date="<?php echo esc_attr(date('Y-m-d')); ?>"
                    aria-label="<?php esc_attr_e('Today', 'happy-place-theme'); ?>">
                <?php esc_html_e('Today', 'happy-place-theme'); ?>
            </button>
            
            <button class="hph-calendar-nav-btn hph-calendar-next" 
                    data-date="<?php echo esc_attr($next_month->format('Y-m-d')); ?>"
                    aria-label="<?php esc_attr_e('Next month', 'happy-place-theme'); ?>">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
            <?php endif; ?>
            
            <?php if ($args['show_view_selector']) : ?>
            <div class="hph-calendar-view-selector">
                <select class="hph-form-select" data-calendar-view-selector>
                    <option value="month" <?php selected($view, 'month'); ?>><?php esc_html_e('Month', 'happy-place-theme'); ?></option>
                    <option value="list" <?php selected($view, 'list'); ?>><?php esc_html_e('List', 'happy-place-theme'); ?></option>
                    <option value="grid" <?php selected($view, 'grid'); ?>><?php esc_html_e('Grid', 'happy-place-theme'); ?></option>
                </select>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Calendar Content -->
    <div class="hph-calendar-content">
        <?php
        // Debug: Show events data info
        if (empty($events_data['events']) && current_user_can('manage_options')) {
            echo '<div class="hph-debug-info" style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px 0; border-radius: 4px;">';
            echo '<strong>Debug Info:</strong><br>';
            echo 'Events found: ' . (isset($events_data['events']) ? count($events_data['events']) : 'N/A') . '<br>';
            echo 'Query object available: ' . (isset($events_data['query']) ? 'Yes' : 'No') . '<br>';
            if (isset($events_data['query']) && $events_data['query'] instanceof WP_Query) {
                echo 'Posts found: ' . $events_data['query']->found_posts . '<br>';
                echo 'Post type: ' . $post_type . '<br>';
            }
            echo '</div>';
        }

        if (empty($events_data['events'])) {
            echo '<div class="hph-no-events" style="text-align: center; padding: 40px 20px;">';
            echo '<h3>' . esc_html__('No Open Houses Found', 'happy-place-theme') . '</h3>';
            echo '<p>' . esc_html__('There are currently no open houses scheduled for this period.', 'happy-place-theme') . '</p>';
            echo '</div>';
        } else {
            switch ($view) {
                case 'month':
                    hph_render_open_house_month_view($date_obj, $events_data, $args);
                    break;

                case 'list':
                    hph_render_open_house_list_view($events_data, $args);
                    break;

                case 'grid':
                    hph_render_open_house_grid_view($events_data, $args);
                    break;

                default:
                    hph_render_open_house_month_view($date_obj, $events_data, $args);
                    break;
            }
        }
        ?>
    </div>
    
    <?php if (empty($events_data['events'])) : ?>
    <div class="hph-calendar-empty-state">
        <div class="hph-empty-state">
            <i class="fas fa-calendar-alt hph-empty-state-icon" aria-hidden="true"></i>
            <h3 class="hph-empty-state-title"><?php esc_html_e('No Open Houses Found', 'happy-place-theme'); ?></h3>
            <p class="hph-empty-state-description">
                <?php esc_html_e('There are no open houses scheduled for this time period.', 'happy-place-theme'); ?>
            </p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
/**
 * Calendar helper functions for Open Houses
 */

/**
 * Get calendar open houses data
 */
function hph_get_calendar_open_houses($args) {
    // Build the query for open_house post type
    $query_args = array(
        'post_type' => 'open_house',
        'post_status' => 'publish',
        'posts_per_page' => isset($args['posts_per_page']) ? $args['posts_per_page'] : ($args['view'] === 'list' ? 20 : -1),
        'meta_query' => array(
            array(
                'key' => 'open_house_date',
                'compare' => 'EXISTS'
            )
        ),
        'meta_key' => 'open_house_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    );
    
    // Add additional meta queries if provided
    if (!empty($args['additional_meta_query'])) {
        $query_args['meta_query'] = array_merge($query_args['meta_query'], $args['additional_meta_query']);
        $query_args['meta_query']['relation'] = 'AND';
    }
    
    // Add date range filtering based on view
    $date_obj = DateTime::createFromFormat('Y-m-d', $args['current_date']);
    if ($date_obj) {
        switch ($args['view']) {
            case 'month':
                $start_date = $date_obj->format('Y-m-01');
                $end_date = $date_obj->format('Y-m-t');
                break;
            
            case 'week':
                $start_of_week = clone $date_obj;
                $start_of_week->modify('monday this week');
                $end_of_week = clone $start_of_week;
                $end_of_week->modify('+6 days');
                $start_date = $start_of_week->format('Y-m-d');
                $end_date = $end_of_week->format('Y-m-d');
                break;
            
            case 'day':
                $start_date = $date_obj->format('Y-m-d');
                $end_date = $start_date;
                break;
            
            default:
                // For list and grid views, get events from current date forward
                $start_date = $args['current_date'];
                $end_date = null;
                break;
        }
        
        if ($start_date) {
            $query_args['meta_query'][] = array(
                'key' => 'open_house_date',
                'value' => $start_date,
                'compare' => '>='
            );
        }
        
        if ($end_date) {
            $query_args['meta_query'][] = array(
                'key' => 'open_house_date',
                'value' => $end_date . ' 23:59:59',
                'compare' => '<='
            );
        }
    }
    
    $events_query = new WP_Query($query_args);
    
    // Process events data
    $events = array();
    $events_by_date = array();
    
    if ($events_query->have_posts()) {
        while ($events_query->have_posts()) {
            $events_query->the_post();
            
            // Get open house data using bridge functions
            $open_house_id = get_the_ID();
            $listing_id = hpt_get_open_house_listing($open_house_id);
            
            $event_data = array(
                'id' => $open_house_id,
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'excerpt' => get_the_excerpt(),
                'date' => hpt_get_open_house_date($open_house_id),
                'start_time' => hpt_get_open_house_start_time($open_house_id),
                'end_time' => hpt_get_open_house_end_time($open_house_id),
                'formatted_datetime' => hpt_get_open_house_formatted_datetime($open_house_id),
                'is_virtual' => hpt_is_open_house_virtual($open_house_id),
                'virtual_link' => hpt_get_open_house_virtual_link($open_house_id),
                'registration_required' => hpt_is_open_house_registration_required($open_house_id),
                'available_spots' => hpt_get_open_house_available_spots($open_house_id),
                'listing_id' => $listing_id,
                'event_type' => 'open_house'
            );
            
            // Add listing data if available
            if ($listing_id && function_exists('hpt_get_listing')) {
                $event_data['listing'] = array(
                    'id' => $listing_id,
                    'title' => hpt_get_listing_title($listing_id),
                    'url' => get_permalink($listing_id),
                    'price' => hpt_get_listing_price($listing_id),
                    'price_formatted' => hpt_get_listing_price_formatted($listing_id),
                    'bedrooms' => hpt_get_listing_bedrooms($listing_id),
                    'bathrooms' => hpt_get_listing_bathrooms($listing_id),
                    'square_feet' => hpt_get_listing_square_feet($listing_id),
                    'address' => hpt_get_listing_address($listing_id),
                    'city' => hpt_get_listing_city($listing_id),
                    'featured_image' => hpt_get_listing_featured_image($listing_id)
                );
            }
            
            $events[] = $event_data;
            
            // Group by date for calendar grid
            $event_date = date('Y-m-d', strtotime($event_data['date']));
            if (!isset($events_by_date[$event_date])) {
                $events_by_date[$event_date] = array();
            }
            $events_by_date[$event_date][] = $event_data;
        }
        wp_reset_postdata();
    }
    
    return array(
        'events' => $events,
        'events_by_date' => $events_by_date,
        'query' => $events_query
    );
}

/**
 * Render month view for open houses
 */
function hph_render_open_house_month_view($date_obj, $events_data, $args) {
    $first_day_of_month = clone $date_obj;
    $first_day_of_month->setDate($date_obj->format('Y'), $date_obj->format('n'), 1);
    
    // Get the first Monday of the calendar grid
    $start_date = clone $first_day_of_month;
    if ($start_date->format('N') != 1) {
        $start_date->modify('last monday');
    }
    
    ?>
    <div class="hph-calendar-grid">
        <!-- Day headers -->
        <div class="hph-calendar-header-row">
            <?php
            $days = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
            foreach ($days as $day) :
            ?>
            <div class="hph-calendar-day-header"><?php echo esc_html($day); ?></div>
            <?php endforeach; ?>
        </div>
        
        <!-- Calendar days -->
        <?php
        $current_date = clone $start_date;
        for ($week = 0; $week < 6; $week++) :
            for ($day = 0; $day < 7; $day++) :
                $date_key = $current_date->format('Y-m-d');
                $is_current_month = $current_date->format('n') == $date_obj->format('n');
                $is_today = $current_date->format('Y-m-d') === date('Y-m-d');
                $day_events = isset($events_data['events_by_date'][$date_key]) ? $events_data['events_by_date'][$date_key] : array();
                
                $day_classes = array('hph-calendar-day');
                if (!$is_current_month) $day_classes[] = 'is-other-month';
                if ($is_today) $day_classes[] = 'is-today';
                if (!empty($day_events)) $day_classes[] = 'has-events';
                ?>
                
                <div class="<?php echo esc_attr(implode(' ', $day_classes)); ?>" data-date="<?php echo esc_attr($date_key); ?>">
                    <div class="hph-calendar-day-number"><?php echo esc_html($current_date->format('j')); ?></div>
                    
                    <?php if (!empty($day_events)) : ?>
                    <div class="hph-calendar-events">
                        <?php 
                        $displayed_events = 0;
                        $max_events = 3;
                        
                        foreach ($day_events as $event) :
                            if ($displayed_events >= $max_events) break;
                            ?>
                            <div class="hph-calendar-event event-type-open-house" data-event-id="<?php echo esc_attr($event['id']); ?>">
                                <div class="hph-calendar-event-time">
                                    <?php echo esc_html(date('g:i A', strtotime($event['start_time']))); ?>
                                </div>
                                <div class="hph-calendar-event-title">
                                    <?php 
                                    if (!empty($event['listing']['address'])) {
                                        echo esc_html($event['listing']['address']);
                                    } else {
                                        echo esc_html($event['title']);
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php 
                            $displayed_events++;
                        endforeach;
                        
                        if (count($day_events) > $max_events) :
                            $remaining = count($day_events) - $max_events;
                            ?>
                            <button class="hph-calendar-more-events" data-date="<?php echo esc_attr($date_key); ?>">
                                +<?php echo esc_html($remaining); ?> <?php esc_html_e('more', 'happy-place-theme'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php
                $current_date->modify('+1 day');
            endfor;
        endfor;
        ?>
    </div>
    <?php
}

/**
 * Render list view for open houses
 */
function hph_render_open_house_list_view($events_data, $args) {
    if (empty($events_data['events'])) {
        return;
    }
    ?>
    <div class="hph-calendar-list">
        <div class="hph-loop-container">
            <?php foreach ($events_data['events'] as $event) : ?>
                <div class="hph-open-house-card">
                    <div class="hph-card">
                        <div class="hph-card-body">
                            <div class="hph-open-house-date-badge">
                                <span class="hph-date-month"><?php echo esc_html(date('M', strtotime($event['date']))); ?></span>
                                <span class="hph-date-day"><?php echo esc_html(date('j', strtotime($event['date']))); ?></span>
                            </div>
                            
                            <div class="hph-open-house-content">
                                <h3 class="hph-open-house-title">
                                    <a href="<?php echo esc_url($event['permalink']); ?>">
                                        <?php 
                                        if (!empty($event['listing']['address'])) {
                                            echo esc_html($event['listing']['address']);
                                        } else {
                                            echo esc_html($event['title']);
                                        }
                                        ?>
                                    </a>
                                </h3>
                                
                                <div class="hph-open-house-meta">
                                    <span class="hph-open-house-time">
                                        <i class="fas fa-clock"></i>
                                        <?php echo esc_html(date('g:i A', strtotime($event['start_time']))); ?> - 
                                        <?php echo esc_html(date('g:i A', strtotime($event['end_time']))); ?>
                                    </span>
                                    
                                    <?php if (!empty($event['listing']['price_formatted'])) : ?>
                                    <span class="hph-open-house-price">
                                        <i class="fas fa-tag"></i>
                                        <?php echo esc_html($event['listing']['price_formatted']); ?>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($event['is_virtual']) : ?>
                                    <span class="hph-open-house-virtual">
                                        <i class="fas fa-video"></i>
                                        <?php esc_html_e('Virtual Tour Available', 'happy-place-theme'); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($event['listing'])) : ?>
                                <div class="hph-open-house-property-details">
                                    <span><?php echo esc_html($event['listing']['bedrooms']); ?> beds</span>
                                    <span><?php echo esc_html($event['listing']['bathrooms']); ?> baths</span>
                                    <span><?php echo esc_html(number_format($event['listing']['square_feet'])); ?> sqft</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Render grid view for open houses
 */
function hph_render_open_house_grid_view($events_data, $args) {
    if (empty($events_data['events'])) {
        return;
    }
    ?>
    <div class="hph-calendar-grid-view">
        <div class="hph-loop-container hph-loop-container--grid hph-grid-cols-3">
            <?php foreach ($events_data['events'] as $event) : ?>
                <div class="hph-open-house-card-grid">
                    <?php if (!empty($event['listing']['featured_image']['url'])) : ?>
                    <div class="hph-card-image">
                        <img src="<?php echo esc_url($event['listing']['featured_image']['url']); ?>" 
                             alt="<?php echo esc_attr($event['listing']['title']); ?>">
                        <div class="hph-open-house-date-overlay">
                            <?php echo esc_html(date('M j', strtotime($event['date']))); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="hph-card-content">
                        <h3 class="hph-card-title">
                            <a href="<?php echo esc_url($event['permalink']); ?>">
                                <?php 
                                if (!empty($event['listing']['address'])) {
                                    echo esc_html($event['listing']['address']);
                                } else {
                                    echo esc_html($event['title']);
                                }
                                ?>
                            </a>
                        </h3>
                        
                        <div class="hph-open-house-time">
                            <?php echo esc_html($event['formatted_datetime']); ?>
                        </div>
                        
                        <?php if (!empty($event['listing']['price_formatted'])) : ?>
                        <div class="hph-open-house-price">
                            <?php echo esc_html($event['listing']['price_formatted']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}
?>