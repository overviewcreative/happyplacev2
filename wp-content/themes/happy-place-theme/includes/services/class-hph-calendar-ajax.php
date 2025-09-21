<?php
/**
 * AJAX Handlers for Calendar
 * Add this to your theme's functions.php or a separate inc file
 */

// AJAX handler for loading calendar data
add_action('wp_ajax_hph_load_calendar_data', 'hph_ajax_load_calendar_data');
add_action('wp_ajax_nopriv_hph_load_calendar_data', 'hph_ajax_load_calendar_data');

function hph_ajax_load_calendar_data() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hph_calendar_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Get parameters
    $view = sanitize_key($_POST['view'] ?? 'month');
    $date = sanitize_text_field($_POST['date'] ?? date('Y-m-d'));
    $calendar_id = sanitize_key($_POST['calendar_id'] ?? '');
    
    // Parse date
    $date_obj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$date_obj) {
        $date_obj = new DateTime();
    }
    
    // Build args for calendar
    $args = array(
        'view' => $view,
        'post_type' => 'listing',
        'date_field' => 'open_house_date',
        'time_field' => 'open_house_time',
        'end_date_field' => 'open_house_end_time',
        'location_field' => 'address_full',
        'category_field' => 'property_type',
        'current_date' => $date,
        'calendar_id' => $calendar_id,
        'additional_meta_query' => array(
            array(
                'key' => 'listing_has_open_house',
                'value' => 'yes',
                'compare' => '='
            )
        )
    );
    
    // Get events data
    $events_data = hph_get_calendar_events($args);
    
    // Generate header HTML
    ob_start();
    ?>
    <h2 class="hph-calendar-title">
        <?php echo esc_html($date_obj->format('F Y')); ?>
    </h2>
    
    <div class="hph-calendar-nav">
        <?php
        $prev_month = clone $date_obj;
        $prev_month->modify('-1 month');
        $next_month = clone $date_obj;
        $next_month->modify('+1 month');
        ?>
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
    </div>
    <?php
    $header_html = ob_get_clean();
    
    // Generate content HTML based on view
    ob_start();
    
    switch ($view) {
        case 'month':
            hph_render_calendar_month_view($date_obj, $events_data, $args);
            break;
        case 'list':
            hph_render_calendar_list_view($events_data, $args);
            break;
        case 'grid':
            hph_render_calendar_grid_view($events_data, $args);
            break;
        default:
            hph_render_calendar_month_view($date_obj, $events_data, $args);
            break;
    }
    
    $content_html = ob_get_clean();
    
    // Send response
    wp_send_json_success(array(
        'header' => $header_html,
        'content' => $content_html,
        'events_count' => count($events_data['events']),
        'date' => $date,
        'view' => $view
    ));
}

// Register calendar post meta for listings
add_action('init', 'hph_register_open_house_meta');
function hph_register_open_house_meta() {
    // Register meta fields for open houses
    $meta_fields = array(
        'listing_has_open_house' => array(
            'type' => 'string',
            'description' => 'Whether listing has an open house',
            'single' => true,
            'show_in_rest' => true,
        ),
        'open_house_date' => array(
            'type' => 'string',
            'description' => 'Open house date',
            'single' => true,
            'show_in_rest' => true,
        ),
        'open_house_time' => array(
            'type' => 'string',
            'description' => 'Open house start time',
            'single' => true,
            'show_in_rest' => true,
        ),
        'open_house_end_time' => array(
            'type' => 'string',
            'description' => 'Open house end time',
            'single' => true,
            'show_in_rest' => true,
        ),
    );
    
    foreach ($meta_fields as $meta_key => $args) {
        register_post_meta('listing', $meta_key, $args);
    }
}