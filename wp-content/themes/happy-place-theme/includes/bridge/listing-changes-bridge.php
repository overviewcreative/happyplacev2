<?php
/**
 * Listing Changes Bridge - Track and display listing updates
 *
 * Automatically detects changes to listings and creates badges/notifications
 * that expire after a specified time period.
 *
 * @package HappyPlaceTheme
 * @subpackage Bridge
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize listing change tracking hooks
 */
function hpt_init_listing_change_tracking() {
    // Hook into post save to track changes
    add_action('save_post', 'hpt_track_listing_changes', 10, 3);

    // Hook into ACF field updates
    add_action('acf/save_post', 'hpt_track_acf_listing_changes', 20);

    // Daily cleanup of expired changes
    if (!wp_next_scheduled('hpt_cleanup_expired_listing_changes')) {
        wp_schedule_event(time(), 'daily', 'hpt_cleanup_expired_listing_changes');
    }
    add_action('hpt_cleanup_expired_listing_changes', 'hpt_cleanup_expired_changes');
}
add_action('init', 'hpt_init_listing_change_tracking');

/**
 * Track changes when a listing post is saved
 *
 * @param int $post_id Post ID
 * @param WP_Post $post Post object
 * @param bool $update Whether this is an update
 */
function hpt_track_listing_changes($post_id, $post, $update) {
    // Only track listing post type
    if ($post->post_type !== 'listing') {
        return;
    }

    // Skip autosaves and revisions
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    // Skip if user doesn't have permission
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Track new listing - only if this is truly a new listing being published
    if (($post->post_status === 'publish') && (get_post_meta($post_id, '_listing_first_published', true) === '')) {
        hpt_track_new_listing($post_id);
    }

    // Track status changes
    hpt_track_listing_status_change($post_id);
}

/**
 * Track ACF field changes for listings
 *
 * @param int $post_id Post ID
 */
function hpt_track_acf_listing_changes($post_id) {
    // Only track listing post type
    if (get_post_type($post_id) !== 'listing') {
        return;
    }

    // Track price changes
    hpt_track_price_change($post_id);

    // Track open house changes (legacy repeater field)
    hpt_track_open_house_change($post_id);

    // NEW: Track open house post relationships
    hpt_track_open_house_posts($post_id);

    // NEW: Track highlight field changes
    hpt_track_highlight_field($post_id);

    // Track other significant field changes
    hpt_track_property_updates($post_id);

    // Update last modified tracking
    hpt_update_listing_last_modified($post_id);
}

/**
 * Track new listing - ENHANCED: Uses listing_date instead of publish date
 *
 * @param int $post_id Listing ID
 */
function hpt_track_new_listing($post_id) {
    $current_time = current_time('timestamp');

    // Get listing date from ACF field (preferred) or fallback to post date
    $listing_date = get_field('listing_date', $post_id);
    if ($listing_date) {
        $listing_timestamp = strtotime($listing_date);
    } else {
        $post = get_post($post_id);
        $listing_timestamp = strtotime($post->post_date);
    }

    // Mark as first published if not already set - use listing date
    if (!get_post_meta($post_id, '_listing_first_published', true)) {
        update_post_meta($post_id, '_listing_first_published', $listing_timestamp);
    }

    // Track days on market
    hpt_update_days_on_market($post_id, $listing_timestamp);

    // Add new listing change record
    hpt_add_listing_change($post_id, 'new_listing', [
        'message' => 'New Listing',
        'timestamp' => $listing_timestamp,
        'expires_at' => $listing_timestamp + (14 * DAY_IN_SECONDS), // 14 days from listing date
        'badge_color' => 'success',
        'priority' => 10
    ]);
}

/**
 * Track price changes
 *
 * @param int $post_id Listing ID
 */
function hpt_track_price_change($post_id) {
    $new_price = get_field('listing_price', $post_id);
    $old_price = get_post_meta($post_id, '_previous_listing_price', true);

    // Skip if no price set
    if (empty($new_price)) {
        return;
    }

    // Convert to numbers for comparison
    $new_price_num = floatval(str_replace(',', '', $new_price));
    $old_price_num = floatval(str_replace(',', '', $old_price));

    // Check if price actually changed
    if ($old_price_num > 0 && $new_price_num !== $old_price_num) {
        $change_amount = $new_price_num - $old_price_num;
        $change_percent = round(($change_amount / $old_price_num) * 100, 1);

        $change_type = $change_amount > 0 ? 'increase' : 'decrease';
        $badge_color = $change_amount > 0 ? 'warning' : 'success';

        $message = sprintf(
            'Price %s %s (%s%s%%)',
            $change_type === 'increase' ? 'Increased' : 'Reduced',
            '$' . number_format(abs($change_amount)),
            $change_amount > 0 ? '+' : '-',
            abs($change_percent)
        );

        hpt_add_listing_change($post_id, 'price_change', [
            'message' => $message,
            'old_value' => '$' . number_format($old_price_num),
            'new_value' => '$' . number_format($new_price_num),
            'change_amount' => $change_amount,
            'change_percent' => $change_percent,
            'timestamp' => current_time('timestamp'),
            'expires_at' => current_time('timestamp') + (14 * DAY_IN_SECONDS),
            'badge_color' => $badge_color,
            'priority' => 8
        ]);
    }

    // Update previous price for next comparison
    update_post_meta($post_id, '_previous_listing_price', $new_price);
}

/**
 * Track open house changes
 *
 * @param int $post_id Listing ID
 */
function hpt_track_open_house_change($post_id) {
    $open_house_dates = get_field('listing_open_house_dates', $post_id);
    $previous_dates = get_post_meta($post_id, '_previous_open_house_dates', true);

    // Convert to comparable format
    $current_dates = is_array($open_house_dates) ? serialize($open_house_dates) : '';
    $previous_dates = $previous_dates ? $previous_dates : '';

    // Check if open house dates changed
    if ($current_dates !== $previous_dates) {
        $has_upcoming = false;

        if (is_array($open_house_dates)) {
            $now = current_time('timestamp');
            foreach ($open_house_dates as $date_info) {
                if (isset($date_info['date']) && strtotime($date_info['date']) > $now) {
                    $has_upcoming = true;
                    break;
                }
            }
        }

        if ($has_upcoming) {
            hpt_add_listing_change($post_id, 'open_house', [
                'message' => 'Open House Scheduled',
                'timestamp' => current_time('timestamp'),
                'expires_at' => current_time('timestamp') + (7 * DAY_IN_SECONDS), // 7 days
                'badge_color' => 'primary',
                'priority' => 9
            ]);
        }
    }

    // Update previous dates
    update_post_meta($post_id, '_previous_open_house_dates', $current_dates);
}

/**
 * Track listing status changes
 *
 * @param int $post_id Listing ID
 */
function hpt_track_listing_status_change($post_id) {
    $current_status = get_field('property_status', $post_id);
    $previous_status = get_post_meta($post_id, '_previous_property_status', true);

    if ($current_status && $previous_status && $current_status !== $previous_status) {
        $message = '';
        $badge_color = 'default';

        switch ($current_status) {
            case 'pending':
                $message = 'Under Contract';
                $badge_color = 'warning';
                break;
            case 'sold':
                $message = 'Recently Sold';
                $badge_color = 'error';
                break;
            case 'active':
                if ($previous_status === 'pending') {
                    $message = 'Back on Market';
                    $badge_color = 'success';
                }
                break;
        }

        if ($message) {
            hpt_add_listing_change($post_id, 'status_change', [
                'message' => $message,
                'old_status' => $previous_status,
                'new_status' => $current_status,
                'timestamp' => current_time('timestamp'),
                'expires_at' => current_time('timestamp') + (14 * DAY_IN_SECONDS),
                'badge_color' => $badge_color,
                'priority' => 7
            ]);
        }
    }

    // Update previous status
    update_post_meta($post_id, '_previous_property_status', $current_status);
}

/**
 * Track other property updates
 *
 * @param int $post_id Listing ID
 */
function hpt_track_property_updates($post_id) {
    // Track significant field updates (bedrooms, bathrooms, sqft)
    $fields_to_track = [
        'listing_bedrooms' => 'Bedrooms Updated',
        'listing_bathrooms' => 'Bathrooms Updated',
        'listing_sqft' => 'Square Footage Updated'
    ];

    foreach ($fields_to_track as $field => $message) {
        $new_value = get_field($field, $post_id);
        $old_value = get_post_meta($post_id, '_previous_' . $field, true);

        if ($old_value && $new_value && $old_value != $new_value) {
            hpt_add_listing_change($post_id, 'property_update', [
                'message' => $message,
                'field' => $field,
                'old_value' => $old_value,
                'new_value' => $new_value,
                'timestamp' => current_time('timestamp'),
                'expires_at' => current_time('timestamp') + (7 * DAY_IN_SECONDS),
                'badge_color' => 'info',
                'priority' => 5
            ]);
        }

        // Update previous value
        update_post_meta($post_id, '_previous_' . $field, $new_value);
    }
}

/**
 * Add a listing change record
 *
 * @param int $post_id Listing ID
 * @param string $change_type Type of change
 * @param array $change_data Change data
 */
function hpt_add_listing_change($post_id, $change_type, $change_data) {
    $changes = get_post_meta($post_id, '_listing_changes', true);
    if (!is_array($changes)) {
        $changes = [];
    }

    // Remove existing change of same type to avoid duplicates
    $changes = array_filter($changes, function($change) use ($change_type) {
        return $change['type'] !== $change_type;
    });

    // Add new change
    $changes[] = array_merge([
        'type' => $change_type,
        'id' => uniqid()
    ], $change_data);

    // Sort by priority (higher priority first)
    usort($changes, function($a, $b) {
        return ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0);
    });

    update_post_meta($post_id, '_listing_changes', $changes);
}

/**
 * Get active listing changes
 *
 * @param int $post_id Listing ID
 * @param string $type Optional change type filter
 * @return array Active changes
 */
function hpt_get_listing_changes($post_id, $type = '') {
    $changes = get_post_meta($post_id, '_listing_changes', true);
    if (!is_array($changes)) {
        return [];
    }

    $now = current_time('timestamp');
    $active_changes = [];

    foreach ($changes as $change) {
        // Skip expired changes
        if (isset($change['expires_at']) && $change['expires_at'] < $now) {
            continue;
        }

        // Filter by type if specified
        if ($type && $change['type'] !== $type) {
            continue;
        }

        $active_changes[] = $change;
    }

    return $active_changes;
}

/**
 * Check if listing is new (within specified days) - ENHANCED: Uses listing_date
 *
 * @param int $post_id Listing ID
 * @param int $days Number of days to consider "new"
 * @return bool
 */
function hpt_is_new_listing($post_id, $days = 14) {
    // Try to get listing date first
    $listing_date = get_field('listing_date', $post_id);
    if ($listing_date) {
        $listing_timestamp = strtotime($listing_date);
    } else {
        // Fallback to first published timestamp
        $listing_timestamp = get_post_meta($post_id, '_listing_first_published', true);
        if (!$listing_timestamp) {
            // Final fallback to post date
            $post = get_post($post_id);
            $listing_timestamp = strtotime($post->post_date);
        }
    }

    if (!$listing_timestamp) {
        return false;
    }

    $cutoff = current_time('timestamp') - ($days * DAY_IN_SECONDS);
    return $listing_timestamp > $cutoff;
}

/**
 * Get listing change badges for display
 *
 * @param int $post_id Listing ID
 * @param int $limit Maximum number of badges to return
 * @return array Badge data for display
 */
function hpt_get_listing_badges($post_id, $limit = 2) {
    $changes = hpt_get_listing_changes($post_id);
    $badges = [];

    foreach ($changes as $change) {
        if (count($badges) >= $limit) {
            break;
        }

        $badges[] = [
            'text' => $change['message'],
            'variant' => $change['badge_color'] ?? 'default',
            'type' => $change['type'],
            'priority' => $change['priority'] ?? 0,
            'data' => $change
        ];
    }

    return $badges;
}

/**
 * Track open house posts linked to this listing
 *
 * @param int $post_id Listing ID
 */
function hpt_track_open_house_posts($post_id) {
    // Query open house posts linked to this listing
    $open_houses = get_posts([
        'post_type' => 'open_house',
        'meta_query' => [
            [
                'key' => 'listing_id', // ACF relationship field
                'value' => $post_id,
                'compare' => '='
            ],
            [
                'key' => 'start_date',
                'value' => date('Y-m-d'),
                'compare' => '>='
            ]
        ],
        'meta_key' => 'start_date',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'posts_per_page' => 5
    ]);

    if (!empty($open_houses)) {
        foreach ($open_houses as $open_house) {
            $start_date = get_field('start_date', $open_house->ID);
            $start_time = get_field('start_time', $open_house->ID);

            if ($start_date && strtotime($start_date) > time() &&
                strtotime($start_date) < (time() + 7 * 24 * 60 * 60)) { // Within 7 days

                $formatted_date = date('M j', strtotime($start_date));
                if ($start_time) {
                    $formatted_date .= ' at ' . date('g:ia', strtotime($start_time));
                }

                hpt_add_listing_change($post_id, 'open_house_post', [
                    'message' => 'Open House ' . $formatted_date,
                    'open_house_id' => $open_house->ID,
                    'open_house_url' => get_permalink($open_house->ID),
                    'timestamp' => current_time('timestamp'),
                    'expires_at' => strtotime($start_date) + (24 * 60 * 60), // Expires day after event
                    'badge_color' => 'primary',
                    'priority' => 9
                ]);
                break; // Only show the next upcoming open house
            }
        }
    }
}

/**
 * Track highlight field changes
 *
 * @param int $post_id Listing ID
 */
function hpt_track_highlight_field($post_id) {
    $highlight_text = get_field('highlight_text', $post_id);
    $previous_highlight = get_post_meta($post_id, '_previous_highlight_text', true);

    // If highlight text is set and different from previous
    if (!empty($highlight_text) && $highlight_text !== $previous_highlight) {
        hpt_add_listing_change($post_id, 'highlight', [
            'message' => $highlight_text,
            'timestamp' => current_time('timestamp'),
            'expires_at' => current_time('timestamp') + (30 * DAY_IN_SECONDS), // 30 days
            'badge_color' => 'warning',
            'priority' => 8
        ]);
    }

    // Update previous value
    update_post_meta($post_id, '_previous_highlight_text', $highlight_text);
}

/**
 * Update days on market calculation
 *
 * @param int $post_id Listing ID
 * @param int $listing_timestamp Listing date timestamp
 */
function hpt_update_days_on_market($post_id, $listing_timestamp = null) {
    if (!$listing_timestamp) {
        // Get listing date from ACF field or post date
        $listing_date = get_field('listing_date', $post_id);
        if ($listing_date) {
            $listing_timestamp = strtotime($listing_date);
        } else {
            $post = get_post($post_id);
            $listing_timestamp = strtotime($post->post_date);
        }
    }

    // Ensure we have a valid timestamp
    if (empty($listing_timestamp) || !is_numeric($listing_timestamp)) {
        $post = get_post($post_id);
        $listing_timestamp = strtotime($post->post_date);
    }

    $current_time = current_time('timestamp');
    $days_on_market = floor(($current_time - $listing_timestamp) / DAY_IN_SECONDS);

    // Ensure days on market is not negative
    $days_on_market = max(0, $days_on_market);

    // Store days on market
    update_post_meta($post_id, '_days_on_market', $days_on_market);
    update_post_meta($post_id, '_listing_date_timestamp', $listing_timestamp);

    return $days_on_market;
}

/**
 * Update listing last modified timestamp
 *
 * @param int $post_id Listing ID
 */
function hpt_update_listing_last_modified($post_id) {
    $current_time = current_time('timestamp');
    update_post_meta($post_id, '_listing_last_updated', $current_time);
}

/**
 * Get days on market for a listing
 *
 * @param int $post_id Listing ID
 * @return int Days on market
 */
function hpt_get_days_on_market($post_id) {
    $days = get_post_meta($post_id, '_days_on_market', true);
    if ($days === '') {
        // Calculate if not stored
        $days = hpt_update_days_on_market($post_id);
    }
    return intval($days);
}

/**
 * Get listing last updated timestamp
 *
 * @param int $post_id Listing ID
 * @return int Timestamp of last update
 */
function hpt_get_listing_last_updated($post_id) {
    $last_updated = get_post_meta($post_id, '_listing_last_updated', true);
    $first_published = get_post_meta($post_id, '_listing_first_published', true);

    // Return the most recent timestamp, or post modified time as fallback
    $timestamp = $last_updated ?: $first_published;

    // Ensure we have a valid timestamp, fallback to post modified time
    if (empty($timestamp) || !is_numeric($timestamp)) {
        $post = get_post($post_id);
        $timestamp = strtotime($post->post_modified);
    }

    return $timestamp;
}

/**
 * Cleanup expired listing changes
 */
function hpt_cleanup_expired_changes() {
    global $wpdb;

    // Get all listings with change data
    $listing_ids = $wpdb->get_col("
        SELECT post_id
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_listing_changes'
    ");

    $cleaned_count = 0;

    foreach ($listing_ids as $post_id) {
        $changes = get_post_meta($post_id, '_listing_changes', true);
        if (!is_array($changes)) {
            continue;
        }

        $now = current_time('timestamp');
        $active_changes = array_filter($changes, function($change) use ($now) {
            return !isset($change['expires_at']) || $change['expires_at'] >= $now;
        });

        // Update if changes were removed
        if (count($active_changes) !== count($changes)) {
            update_post_meta($post_id, '_listing_changes', array_values($active_changes));
            $cleaned_count++;
        }
    }

    if ($cleaned_count > 0) {
        error_log("Cleaned expired listing changes from {$cleaned_count} listings");
    }
}

/**
 * Bridge function: Get formatted listing changes for templates
 *
 * @param int $post_id Listing ID
 * @return array Formatted change data
 */
function hpt_bridge_get_listing_changes($post_id) {
    if (!$post_id) {
        return [];
    }

    return hpt_get_listing_changes($post_id);
}

/**
 * Bridge function: Get listing badges for card display
 *
 * @param int $post_id Listing ID
 * @param int $limit Maximum badges to show
 * @return array Badge data
 */
function hpt_bridge_get_listing_badges($post_id, $limit = 2) {
    if (!$post_id) {
        return [];
    }

    return hpt_get_listing_badges($post_id, $limit);
}

/**
 * Get comprehensive listing badges including all types
 *
 * @param int $post_id Listing ID
 * @param int $limit Maximum badges to show
 * @return array Complete badge data
 */
function hpt_get_comprehensive_listing_badges($post_id, $limit = 3) {
    if (!$post_id) {
        return [];
    }

    $all_badges = [];

    // 1. Get existing change tracking badges (price changes, status changes, etc.)
    $change_badges = hpt_get_listing_badges($post_id, $limit + 2); // Get more to sort properly
    $all_badges = array_merge($all_badges, $change_badges);

    // 2. Add multiselect status badges (if different from 'active')
    if (function_exists('hpt_get_listing_statuses')) {
        $statuses = hpt_get_listing_statuses($post_id);
        foreach ($statuses as $status) {
            if ($status !== 'active') { // Don't show "Active" as a badge
                $all_badges[] = [
                    'text' => ucwords(str_replace('-', ' ', $status)),
                    'variant' => function_exists('hpt_get_status_variant') ? hpt_get_status_variant($status) : 'default',
                    'type' => 'status',
                    'priority' => 7,
                    'data' => ['status' => $status, 'all_statuses' => $statuses]
                ];
            }
        }
    } else {
        // Fallback to ACF field
        $status = get_field('listing_status', $post_id);
        if ($status && $status !== 'active') {
            $all_badges[] = [
                'text' => ucwords(str_replace('-', ' ', $status)),
                'variant' => 'status',
                'type' => 'status',
                'priority' => 7,
                'data' => ['status' => $status]
            ];
        }
    }

    // 3. Add days on market badge (if significant)
    $days_on_market = hpt_get_days_on_market($post_id);
    if ($days_on_market > 60) { // Only show if over 60 days
        $all_badges[] = [
            'text' => $days_on_market . ' Days on Market',
            'variant' => 'info',
            'type' => 'days_on_market',
            'priority' => 4,
            'data' => ['days' => $days_on_market]
        ];
    }

    // 4. Force refresh tracking for current badges (ensures they're up-to-date)
    hpt_track_open_house_posts($post_id);
    hpt_track_highlight_field($post_id);

    // 5. Get refreshed change badges
    $refreshed_badges = hpt_get_listing_badges($post_id, $limit + 2);

    // Remove duplicates and merge
    $all_badges = array_filter($all_badges, function($badge) use ($refreshed_badges) {
        foreach ($refreshed_badges as $refresh_badge) {
            if ($badge['type'] === $refresh_badge['type']) {
                return false; // Prefer refreshed version
            }
        }
        return true;
    });

    $all_badges = array_merge($refreshed_badges, $all_badges);

    // Sort by priority (higher priority first)
    usort($all_badges, function($a, $b) {
        $a_priority = $a['priority'] ?? 0;
        $b_priority = $b['priority'] ?? 0;
        return $b_priority <=> $a_priority;
    });

    // Return limited results
    return array_slice($all_badges, 0, $limit);
}

/**
 * Bridge function: Get comprehensive listing badges for templates
 *
 * @param int $post_id Listing ID
 * @param int $limit Maximum badges to show
 * @return array Complete badge data
 */
function hpt_bridge_get_comprehensive_badges($post_id, $limit = 3) {
    if (!$post_id) {
        return [];
    }

    return hpt_get_comprehensive_listing_badges($post_id, $limit);
}

/**
 * Bridge function: Check if listing has recent changes
 *
 * @param int $post_id Listing ID
 * @return bool
 */
function hpt_bridge_has_recent_changes($post_id) {
    if (!$post_id) {
        return false;
    }

    $changes = hpt_get_listing_changes($post_id);
    return !empty($changes);
}