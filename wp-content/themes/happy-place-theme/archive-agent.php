<?php
/**
 * Agent Archive Template - Modular Section-Based Architecture
 * 
 * Following front-page.php structure using clean get_template_part() calls
 * with reusable sections for maximum flexibility and maintainability.
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();

// Load our section helper functions
require_once get_template_directory() . '/template-parts/sections/section-helper.php';

// Get current query parameters
$current_view = get_query_var('view', 'grid');
$current_sort = get_query_var('sort', 'name-asc');
$per_page = get_query_var('per_page', get_option('posts_per_page', 12));
$paged = get_query_var('paged', 1);

// Sanitize view mode
$allowed_views = ['grid', 'list'];
if (!in_array($current_view, $allowed_views)) {
    $current_view = 'grid';
}

// Get current WP Query for agents
global $wp_query;
$agents_query = $wp_query;

// Extract posts for component system
$agents = [];
if ($agents_query->have_posts()) {
    while ($agents_query->have_posts()) {
        $agents_query->the_post();
        $agents[] = get_post();
    }
    wp_reset_postdata();
}

/**
 * Get agent archive title with context
 */
function hph_get_agent_archive_title() {
    if (get_search_query()) {
        return sprintf(__('Agent Search Results for "%s"', 'happy-place-theme'), get_search_query());
    } else {
        return __('Our Real Estate Agents', 'happy-place-theme');
    }
}

/**
 * Get agent archive description
 */
function hph_get_agent_archive_description() {
    if (get_search_query()) {
        return sprintf(__('Agents matching your search for "%s"', 'happy-place-theme'), get_search_query());
    }
    return __('Meet our experienced team of Delaware real estate professionals. Each agent brings local expertise, market knowledge, and a commitment to helping you achieve your real estate goals.', 'happy-place-theme');
}

// Helper function to calculate team stats
function hph_calculate_team_stats($agents) {
    if (empty($agents)) {
        return null;
    }
    
    $total_listings = 0;
    $total_sales = 0;
    $combined_experience = 0;
    $total_rating = 0;
    $agents_with_ratings = 0;
    
    foreach ($agents as $agent) {
        if (function_exists('hpt_get_agent')) {
            $agent_data = hpt_get_agent($agent->ID);
            if ($agent_data) {
                $total_listings += $agent_data['stats']['active_listings'] ?? 0;
                $total_sales += $agent_data['stats']['total_sales'] ?? 0;
                $combined_experience += $agent_data['experience_years'] ?? 0;
                
                if (isset($agent_data['stats']['average_rating']) && $agent_data['stats']['average_rating'] > 0) {
                    $total_rating += $agent_data['stats']['average_rating'];
                    $agents_with_ratings++;
                }
            }
        }
    }
    
    return [
        'total_listings' => $total_listings,
        'total_sales' => $total_sales,
        'combined_experience' => $combined_experience,
        'avg_rating' => $agents_with_ratings > 0 ? round($total_rating / $agents_with_ratings, 1) : 0
    ];
}

// Helper function to get featured agent
function hph_get_featured_agent($agents) {
    if (empty($agents)) {
        return null;
    }
    
    $featured_agent = null;
    $highest_score = 0;
    
    foreach ($agents as $agent) {
        if (function_exists('hpt_get_agent')) {
            $agent_data = hpt_get_agent($agent->ID);
            if ($agent_data) {
                // Calculate score based on listings, sales, and rating
                $score = 0;
                $score += ($agent_data['stats']['active_listings'] ?? 0) * 2;
                $score += ($agent_data['stats']['total_sales'] ?? 0) * 1;
                $score += ($agent_data['stats']['average_rating'] ?? 0) * 10;
                
                if ($score > $highest_score) {
                    $highest_score = $score;
                    $featured_agent = $agent->ID;
                }
            }
        }
    }
    
    return $featured_agent;
}

// Hero Section - Agent Team Hero
get_template_part('template-parts/sections/hero', null, array(
    'style' => 'image',
    'height' => 'md',
    'background_image' => get_template_directory_uri() . '/assets/images/agent-hero.jpg',
    'overlay' => 'gradient',
    'overlay_opacity' => '70',
    'alignment' => 'center',
    'content_width' => 'normal',
    'badge' => 'Our Team',
    'headline' => hph_get_agent_archive_title(),
    'subheadline' => sprintf(__('Meet Our %d Professional Agents', 'happy-place-theme'), $agents_query->found_posts),
    'content' => hph_get_agent_archive_description(),
    'buttons' => array(
        array(
            'text' => 'Contact Our Team',
            'url' => home_url('/contact'),
            'style' => 'white',
            'size' => 'lg',
            'icon' => 'fas fa-phone'
        ),
        array(
            'text' => 'View Properties',
            'url' => get_post_type_archive_link('listing'),
            'style' => 'outline-white',
            'size' => 'lg',
            'icon' => 'fas fa-home'
        )
    ),
    'section_id' => 'agent-hero'
));

// Archive Layout Section - Main agent grid with filters
get_template_part('template-parts/layout/archive-layout', null, array(
    'post_type' => 'agent',
    'posts' => $agents,
    'title' => hph_get_agent_archive_title(),
    'description' => hph_get_agent_archive_description(),
    'total_results' => $agents_query->found_posts,
    'current_view' => $current_view,
    'current_sort' => $current_sort,
    'per_page' => $per_page,
    'paged' => $paged,
    'max_pages' => $agents_query->max_num_pages,
    'view_modes' => ['grid', 'list'],
    'sort_options' => [
        'name-asc' => __('Name: A to Z', 'happy-place-theme'),
        'name-desc' => __('Name: Z to A', 'happy-place-theme'),
        'listings-desc' => __('Most Listings', 'happy-place-theme'),
        'experience-desc' => __('Most Experience', 'happy-place-theme'),
        'rating-desc' => __('Highest Rated', 'happy-place-theme')
    ],
    'show_search' => true,
    'show_filters' => true,
    'show_save_search' => false,
    'ajax_enabled' => true,
    'sidebar' => false
));

// Team Performance Stats Section
if (!empty($agents)) {
    $team_stats = hph_calculate_team_stats($agents);
    
    if ($team_stats) {
        get_template_part('template-parts/sections/content', null, array(
            'layout' => 'full-width',
            'background' => 'light',
            'padding' => 'xl',
            'badge' => 'Team Performance',
            'headline' => 'Our Success in Numbers',
            'content' => 'Our agents\' combined success in serving Delaware communities with dedication and expertise.',
            'stats' => array(
                array(
                    'value' => $team_stats['total_listings'],
                    'label' => __('Active Listings', 'happy-place-theme'),
                    'icon' => 'fas fa-home',
                    'format' => 'number'
                ),
                array(
                    'value' => $team_stats['total_sales'],
                    'label' => __('Properties Sold', 'happy-place-theme'),
                    'icon' => 'fas fa-handshake',
                    'format' => 'number'
                ),
                array(
                    'value' => $team_stats['combined_experience'],
                    'label' => __('Years Experience', 'happy-place-theme'),
                    'icon' => 'fas fa-award',
                    'format' => 'number'
                ),
                array(
                    'value' => $team_stats['avg_rating'],
                    'label' => __('Average Rating', 'happy-place-theme'),
                    'icon' => 'fas fa-star',
                    'format' => 'decimal'
                )
            ),
            'section_id' => 'team-stats'
        ));
    }
}

// Featured Agent Spotlight
$featured_agent_id = hph_get_featured_agent($agents);
if ($featured_agent_id && function_exists('hpt_get_agent')) {
    $featured_agent_data = hpt_get_agent($featured_agent_id);
    $featured_agent_post = get_post($featured_agent_id);
    
    if ($featured_agent_post) {
        get_template_part('template-parts/sections/content', null, array(
            'layout' => 'centered',
            'background' => 'white',
            'padding' => 'xl',
            'badge' => 'Agent Spotlight',
            'headline' => 'Meet Our Top Performer',
            'content' => 'Get to know the agent leading our team in client satisfaction and successful transactions.',
            'featured_agent' => array(
                'post_id' => $featured_agent_id,
                'layout' => 'featured',
                'size' => 'large',
                'show_excerpt' => true,
                'show_meta' => true,
                'show_actions' => true
            ),
            'buttons' => array(
                array(
                    'text' => 'Contact ' . get_the_title($featured_agent_id),
                    'url' => get_permalink($featured_agent_id),
                    'style' => 'primary',
                    'size' => 'lg',
                    'icon' => 'fas fa-comments'
                ),
                array(
                    'text' => 'View Their Listings',
                    'url' => add_query_arg('agent', $featured_agent_id, get_post_type_archive_link('listing')),
                    'style' => 'outline',
                    'size' => 'lg',
                    'icon' => 'fas fa-home'
                )
            ),
            'section_id' => 'agent-spotlight'
        ));
    }
}

// Services Section
get_template_part('template-parts/sections/content', null, array(
    'layout' => 'left-image',
    'background' => 'white',
    'padding' => 'xl',
    'image' => array(
        'url' => get_template_directory_uri() . '/assets/images/agent-services.jpg',
        'alt' => 'Delaware real estate services',
        'width' => 600,
        'height' => 400
    ),
    'badge' => 'Our Services',
    'headline' => 'Full-Service Real Estate Solutions',
    'subheadline' => 'Every Step of Your Journey',
    'content' => 'From initial consultation to closing day, our agents provide comprehensive support throughout your real estate transaction. We handle negotiations, paperwork, inspections, and everything in between.',
    'buttons' => array(
        array(
            'text' => 'Learn About Our Process',
            'url' => home_url('/process'),
            'style' => 'primary',
            'size' => 'md',
            'icon' => 'fas fa-list-ol'
        ),
        array(
            'text' => 'Schedule Consultation',
            'url' => home_url('/contact'),
            'style' => 'outline',
            'size' => 'md',
            'icon' => 'fas fa-calendar'
        )
    ),
    'section_id' => 'services'
));

// Contact CTA Section
get_template_part('template-parts/sections/content', null, array(
    'layout' => 'centered',
    'background' => 'primary',
    'padding' => 'xl',
    'headline' => 'Ready to Work with Our Team?',
    'content' => 'Whether you\'re buying your first home or selling a family property, our experienced Delaware agents are here to guide you every step of the way. Contact us today to get started.',
    'buttons' => array(
        array(
            'text' => 'Contact Our Team',
            'url' => home_url('/contact'),
            'style' => 'white',
            'size' => 'lg',
            'icon' => 'fas fa-phone'
        ),
        array(
            'text' => 'View Our Properties',
            'url' => get_post_type_archive_link('listing'),
            'style' => 'outline-white',
            'size' => 'lg',
            'icon' => 'fas fa-home'
        )
    ),
    'section_id' => 'contact-cta'
));

// Testimonials Section
get_template_part('template-parts/sections/content', null, array(
    'layout' => 'full-width',
    'background' => 'light',
    'padding' => 'xl',
    'badge' => 'Client Stories',
    'headline' => 'What Our Clients Say',
    'content' => 'Don\'t just take our word for it — hear from the Delaware families our agents have helped achieve their real estate dreams.',
    'buttons' => array(
        array(
            'text' => 'Read More Reviews',
            'url' => home_url('/testimonials'),
            'style' => 'primary',
            'size' => 'md',
            'icon' => 'fas fa-heart'
        )
    ),
    'section_id' => 'testimonials'
));

// Service Areas Section
get_template_part('template-parts/sections/hero', null, array(
    'style' => 'image',
    'height' => 'sm',
    'background_image' => get_template_directory_uri() . '/assets/images/delaware-communities.jpg',
    'overlay' => 'gradient',
    'overlay_opacity' => '60',
    'alignment' => 'center',
    'content_width' => 'normal',
    'badge' => 'Service Areas',
    'headline' => 'Serving Delaware Communities',
    'subheadline' => 'Local Expertise • Community Knowledge • Neighborhood Insights',
    'content' => 'Our agents know Delaware inside and out. From coastal towns to growing suburban communities, we provide local expertise that makes all the difference.',
    'buttons' => array(
        array(
            'text' => 'View All Service Areas',
            'url' => home_url('/areas'),
            'style' => 'white',
            'size' => 'lg',
            'icon' => 'fas fa-map-marker-alt'
        )
    ),
    'section_id' => 'service-areas'
));

// Enqueue page-specific assets
wp_enqueue_style('hph-archive-agent', get_template_directory_uri() . '/assets/css/pages/archive-agent.css', ['hph-framework'], get_theme_mod('theme_version', '3.0.0'));
wp_enqueue_style('hph-archive-enhancements', get_template_directory_uri() . '/assets/css/archive-enhancements.css', ['hph-framework'], get_theme_mod('theme_version', '3.0.0'));
wp_enqueue_script('hph-archive-agent', get_template_directory_uri() . '/assets/js/pages/archive-agent.js', ['hph-framework-core'], get_theme_mod('theme_version', '3.0.0'), true);
wp_enqueue_script('hph-archive-functionality', get_template_directory_uri() . '/assets/js/archive-functionality.js', ['jquery'], get_theme_mod('theme_version', '3.0.0'), true);

// Localize script with current context
wp_localize_script('hph-archive-agent', 'hphAgentArchive', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_agent_archive_nonce'),
    'postType' => 'agent',
    'currentView' => $current_view,
    'currentSort' => $current_sort,
    'perPage' => $per_page,
    'currentPage' => $paged,
    'maxPages' => $agents_query->max_num_pages,
    'totalResults' => $agents_query->found_posts,
    'strings' => [
        'loading' => __('Loading...', 'happy-place-theme'),
        'contactAgent' => __('Contact Agent', 'happy-place-theme'),
        'viewListings' => __('View Listings', 'happy-place-theme'),
        'error' => __('An error occurred. Please try again.', 'happy-place-theme')
    ]
]);

get_footer();
?>