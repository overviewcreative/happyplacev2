<?php
/**
 * Temporary fix for listing archive posts_per_page issue
 * This ensures the listing archive shows more than 1 post
 */

// Fix the main query for listing archives
function hph_fix_listing_archive_query($query) {
    // Only modify the main query on listing archives, not admin queries
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('listing')) {
        // Override posts_per_page to show 12 listings
        $query->set('posts_per_page', 12);
        
        // Debug: log what we're doing
        error_log('HPH: Fixed listing archive query to show 12 posts per page');
    }
}
add_action('pre_get_posts', 'hph_fix_listing_archive_query', 999); // High priority to override other modifications

// Also provide a function to manually check the query status
function hph_debug_query_status() {
    global $wp_query;
    if (is_post_type_archive('listing')) {
        error_log('HPH Debug - Listing Archive Query:');
        error_log('- Found posts: ' . $wp_query->found_posts);
        error_log('- Post count: ' . $wp_query->post_count);
        error_log('- Posts per page: ' . $wp_query->query_vars['posts_per_page']);
        error_log('- Max pages: ' . $wp_query->max_num_pages);
    }
}
add_action('wp', 'hph_debug_query_status');
?>
