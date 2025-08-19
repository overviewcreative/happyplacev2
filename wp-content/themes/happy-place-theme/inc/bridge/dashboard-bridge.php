<?php
/**
 * Dashboard Bridge Functions
 * 
 * Provides a comprehensive interface between the plugin layer and templates
 * for dashboard and statistics data. All dashboard data access should go through
 * these functions rather than direct WordPress or plugin calls.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get dashboard statistics for a user
 * 
 * @param int|null $user_id User ID, defaults to current user
 * @return array Dashboard statistics
 */
function hpt_get_dashboard_stats($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    // Validate user ID
    if (!$user_id || !get_userdata($user_id)) {
        return array(
            'active_listings' => 0,
            'closed_this_month' => 0,
            'new_leads' => 0,
            'open_houses' => 0,
            'error' => 'Invalid user ID'
        );
    }
    
    return array(
        'active_listings' => hpt_count_user_listings($user_id, 'active'),
        'closed_this_month' => hpt_count_closed_transactions($user_id, 'month'),
        'new_leads' => hpt_count_new_leads($user_id, 'week'),
        'open_houses' => hpt_count_upcoming_open_houses($user_id)
    );
}

/**
 * Get user listings with optional filtering
 * 
 * @param int|null $user_id User ID, defaults to current user
 * @param string   $status  Listing status filter
 * @param int      $limit   Number of listings to return
 * @return array   Array of listing data
 */
function hpt_get_user_listings($user_id = null, $status = 'all', $limit = 10) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    // Validate inputs
    if (!$user_id || !get_userdata($user_id)) {
        return array();
    }
    
    $limit = max(1, min(100, intval($limit))); // Limit between 1-100
    $status = sanitize_text_field($status);
    
    $args = array(
        'post_type' => 'listing',
        'author' => $user_id,
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    if ($status !== 'all') {
        $args['meta_query'] = array(
            array(
                'key' => 'listing_status',
                'value' => $status,
                'compare' => '='
            )
        );
    }
    
    $query = new WP_Query($args);
    $listings = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $listings[] = hpt_get_listing(get_the_ID());
        }
    }
    
    wp_reset_postdata();
    return $listings;
}

/**
 * Count user listings by status
 * 
 * @param int    $user_id User ID
 * @param string $status  Listing status
 * @return int   Count of listings
 */
function hpt_count_user_listings($user_id, $status = 'all') {
    if (!$user_id || !get_userdata($user_id)) {
        return 0;
    }
    
    $status = sanitize_text_field($status);
    
    $args = array(
        'post_type' => 'listing',
        'author' => $user_id,
        'posts_per_page' => -1,
        'fields' => 'ids',
    );
    
    if ($status !== 'all') {
        $args['meta_query'] = array(
            array(
                'key' => 'listing_status',
                'value' => $status,
                'compare' => '='
            )
        );
    }
    
    $query = new WP_Query($args);
    return $query->found_posts;
}

/**
 * Count closed transactions for a user within a timeframe
 * 
 * @param int    $user_id    User ID
 * @param string $timeframe  Timeframe (week, month, year)
 * @return int   Count of closed transactions
 */
function hpt_count_closed_transactions($user_id, $timeframe = 'month') {
    if (!$user_id || !get_userdata($user_id)) {
        return 0;
    }
    
    $allowed_timeframes = array('week', 'month', 'year');
    if (!in_array($timeframe, $allowed_timeframes)) {
        $timeframe = 'month';
    }
    
    $date_query = array();
    
    switch ($timeframe) {
        case 'week':
            $date_query = array(
                'after' => '1 week ago'
            );
            break;
        case 'month':
            $date_query = array(
                'after' => '1 month ago'
            );
            break;
        case 'year':
            $date_query = array(
                'after' => '1 year ago'
            );
            break;
    }
    
    $args = array(
        'post_type' => 'transaction',
        'author' => $user_id,
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => array(
            array(
                'key' => 'transaction_status',
                'value' => 'closed',
                'compare' => '='
            )
        ),
        'date_query' => array($date_query)
    );
    
    $query = new WP_Query($args);
    return $query->found_posts;
}

/**
 * Count new leads for a user within a timeframe
 * 
 * @param int    $user_id    User ID  
 * @param string $timeframe  Timeframe (week, month, year)
 * @return int   Count of new leads
 */
function hpt_count_new_leads($user_id, $timeframe = 'week') {
    if (!$user_id || !get_userdata($user_id)) {
        return 0;
    }
    
    $allowed_timeframes = array('week', 'month', 'year');
    if (!in_array($timeframe, $allowed_timeframes)) {
        $timeframe = 'week';
    }
    
    global $wpdb;
    
    $date_condition = '';
    switch ($timeframe) {
        case 'week':
            $date_condition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $date_condition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            break;
        case 'year':
            $date_condition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            break;
    }
    
    $agent_id = hpt_get_user_agent_id($user_id);
    if (!$agent_id) {
        return 0;
    }
    
    $table_name = $wpdb->prefix . 'hp_leads';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        return 0;
    }
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE agent_id = %d $date_condition",
        $agent_id
    ));
    
    return intval($count);
}

/**
 * Count upcoming open houses for a user
 * 
 * @param int $user_id User ID
 * @return int Count of upcoming open houses
 */
function hpt_count_upcoming_open_houses($user_id) {
    if (!$user_id || !get_userdata($user_id)) {
        return 0;
    }
    
    $agent_id = hpt_get_user_agent_id($user_id);
    if (!$agent_id) {
        return 0;
    }
    
    $args = array(
        'post_type' => 'open_house',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'agent',
                'value' => $agent_id,
                'compare' => '='
            ),
            array(
                'key' => 'start_date',
                'value' => current_time('Y-m-d H:i:s'),
                'compare' => '>='
            )
        )
    );
    
    $query = new WP_Query($args);
    return $query->found_posts;
}

/**
 * Get agent ID for a WordPress user
 * 
 * @param int $user_id WordPress user ID
 * @return int|null Agent post ID or null
 */
function hpt_get_user_agent_id($user_id) {
    if (!$user_id) {
        return null;
    }
    
    // Check if there's an agent post linked to this user
    $args = array(
        'post_type' => 'agent',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => array(
            array(
                'key' => 'user_account',
                'value' => $user_id,
                'compare' => '='
            )
        )
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        return $query->posts[0];
    }
    
    return null;
}