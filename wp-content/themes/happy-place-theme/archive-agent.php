<?php
/**
 * Agent Archive Template - Foundation-First Architecture
 * Core components only - clean and focused
 * 
 * @package HappyPlaceTheme
 * @version 3.0.0
 */

get_header();

// Get current query parameters and sanitize them
$current_view = sanitize_text_field($_GET['view'] ?? 'grid');
$current_sort = sanitize_text_field($_GET['sort'] ?? 'name-asc');
$per_page = intval($_GET['per_page'] ?? get_option('posts_per_page', 12));
$paged = max(1, intval(get_query_var('paged', 1)));

// Validate parameters
$allowed_views = ['grid', 'list'];
if (!in_array($current_view, $allowed_views)) {
    $current_view = 'grid';
}

// Get the current WordPress query
global $wp_query;
$agents_query = $wp_query;

// Extract posts for our component system
$agents = [];
if ($agents_query->have_posts()) {
    while ($agents_query->have_posts()) {
        $agents_query->the_post();
        $agents[] = get_post();
    }
    wp_reset_postdata();
}

/**
 * Get contextual archive title
 */
function hph_get_agent_archive_title() {
    if (is_tax('agent_specialty')) {
        return single_term_title('', false) . ' ' . __('Specialists', 'happy-place-theme');
    } elseif (is_tax('agent_location')) {
        return single_term_title('', false) . ' ' . __('Agents', 'happy-place-theme');
    } elseif (get_search_query()) {
        return sprintf(__('Agent Search: "%s"', 'happy-place-theme'), get_search_query());
    } else {
        return __('Our Real Estate Team', 'happy-place-theme');
    }
}

/**
 * Get contextual archive description
 */
function hph_get_agent_archive_description() {
    if (is_tax()) {
        $description = term_description();
        if ($description) {
            return $description;
        }
    } elseif (get_search_query()) {
        return sprintf(__('Agents matching "%s" in Delaware', 'happy-place-theme'), get_search_query());
    }
    return __('Meet our dedicated team of Delaware real estate professionals.', 'happy-place-theme');
}

// ================================================
// HERO SECTION
// ================================================
get_template_part('template-parts/sections/hero', null, [
    'style' => 'image',
    'height' => 'lg',
    'background_image' => get_template_directory_uri() . '/assets/images/delaware-agent-team-hero.jpg',
    'overlay' => 'gradient',
    'overlay_opacity' => '65',
    'alignment' => 'center',
    'badge' => __('Our Team', 'happy-place-theme'),
    'headline' => hph_get_agent_archive_title(),
    'subheadline' => sprintf(
        _n(
            '%d Professional Agent',
            '%d Professional Agents', 
            $agents_query->found_posts,
            'happy-place-theme'
        ),
        $agents_query->found_posts
    ),
    'content' => hph_get_agent_archive_description(),
    'buttons' => [
        [
            'text' => __('Contact Our Team', 'happy-place-theme'),
            'url' => home_url('/contact'),
            'style' => 'white',
            'size' => 'lg',
            'icon' => 'fas fa-phone'
        ]
    ],
    'section_id' => 'agents-hero'
]);

// ================================================
// ARCHIVE LAYOUT - Core agent display
// ================================================
get_template_part('template-parts/layout/archive-layout', null, [
    // Core Configuration
    'post_type' => 'agent',
    'posts' => $agents,
    'total_results' => $agents_query->found_posts,
    'max_pages' => $agents_query->max_num_pages,
    
    // View Configuration  
    'current_view' => $current_view,
    'default_view' => 'grid',
    'view_modes' => ['grid', 'list'],
    
    // Sorting Configuration
    'current_sort' => $current_sort,
    'sort_options' => [
        'name-asc' => __('Name: A to Z', 'happy-place-theme'),
        'name-desc' => __('Name: Z to A', 'happy-place-theme'),
        'experience-desc' => __('Most Experience', 'happy-place-theme'),
        'listings-desc' => __('Most Listings', 'happy-place-theme'),
        'rating-desc' => __('Highest Rated', 'happy-place-theme')
    ],
    
    // Pagination
    'per_page' => $per_page,
    'per_page_options' => [12, 24, 48],
    'current_page' => $paged,
    
    // Display Features
    'show_search' => true,
    'show_filters' => true,
    'show_results_count' => true,
    'show_sidebar' => true,
    'sidebar_position' => 'right',
    
    // Grid Configuration
    'columns' => 3,
    'columns_tablet' => 2,
    'columns_mobile' => 1,
    'gap' => 'xl',
    'card_style' => 'modern',
    'card_size' => 'large',
    
    // Advanced Features
    'ajax_enabled' => true,
    'lazy_loading' => true
]);

// ================================================
// CONTACT CTA
// ================================================
get_template_part('template-parts/sections/content', null, [
    'layout' => 'centered',
    'background' => 'primary',
    'padding' => 'xl',
    'headline' => __('Ready to Work with Our Team?', 'happy-place-theme'),
    'content' => __('Whether you\'re buying your first home or selling a family property, our experienced Delaware agents are ready to guide you every step of the way.', 'happy-place-theme'),
    'buttons' => [
        [
            'text' => __('Contact Our Team', 'happy-place-theme'),
            'url' => home_url('/contact'),
            'style' => 'white',
            'size' => 'lg',
            'icon' => 'fas fa-phone'
        ]
    ],
    'section_id' => 'contact-team'
]);

// Enqueue page-specific assets
wp_enqueue_style('hph-archive-agent', get_template_directory_uri() . '/assets/css/pages/archive-agent.css', ['hph-framework'], '3.0.0');
wp_enqueue_script('hph-archive-agent', get_template_directory_uri() . '/assets/js/pages/archive-agent.js', ['hph-framework-core'], '3.0.0', true);

// Localize script with essential configuration
wp_localize_script('hph-archive-agent', 'hphAgentArchive', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_agent_archive_nonce'),
    'postType' => 'agent',
    'currentView' => $current_view,
    'currentSort' => $current_sort,
    'perPage' => $per_page,
    'currentPage' => $paged,
    'totalResults' => $agents_query->found_posts,
    'maxPages' => $agents_query->max_num_pages,
    'strings' => [
        'loading' => __('Loading agents...', 'happy-place-theme'),
        'noResults' => __('No agents found.', 'happy-place-theme'),
        'error' => __('Unable to load agents. Please try again.', 'happy-place-theme')
    ]
]);

get_footer();
?>