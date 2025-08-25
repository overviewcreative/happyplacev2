<?php
/**
 * Listing Archive Template - Modular Section-Based Architecture
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
$current_view = get_query_var('view', get_option('hph_default_listing_view', 'grid'));
$current_sort = get_query_var('sort', 'date-desc');
$per_page = get_query_var('per_page', get_option('posts_per_page', 12));
$paged = get_query_var('paged', 1);

// Sanitize view mode
$allowed_views = ['grid', 'list', 'map'];
if (!in_array($current_view, $allowed_views)) {
    $current_view = 'grid';
}

// Get current WP Query for listings
global $wp_query;
$listings_query = $wp_query;

// Extract posts for component system
$listings = [];
if ($listings_query->have_posts()) {
    while ($listings_query->have_posts()) {
        $listings_query->the_post();
        $listings[] = get_post();
    }
    wp_reset_postdata();
}

/**
 * Get archive title with context
 */
function hph_get_archive_title() {
    if (is_tax('property_type')) {
        return single_term_title('', false) . ' ' . __('Properties', 'happy-place-theme');
    } elseif (is_tax('listing_status')) {
        return single_term_title('', false) . ' ' . __('Listings', 'happy-place-theme');
    } elseif (get_search_query()) {
        return sprintf(__('Search Results for "%s"', 'happy-place-theme'), get_search_query());
    } else {
        return __('All Properties', 'happy-place-theme');
    }
}

/**
 * Get archive description
 */
function hph_get_archive_description() {
    if (is_tax()) {
        return term_description();
    } elseif (get_search_query()) {
        return sprintf(__('Properties matching your search for "%s"', 'happy-place-theme'), get_search_query());
    }
    return __('Discover your perfect home in our extensive collection of Delaware properties.', 'happy-place-theme');
}

// Hero Section - Property Search Hero
get_template_part('template-parts/sections/hero', null, array(
    'style' => 'image',
    'height' => 'md',
    'background_image' => get_template_directory_uri() . '/assets/images/listing-hero.jpg',
    'overlay' => 'gradient',
    'overlay_opacity' => '70',
    'alignment' => 'center',
    'content_width' => 'normal',
    'badge' => 'Properties',
    'headline' => hph_get_archive_title(),
    'subheadline' => sprintf(__('Browse %d Available Properties', 'happy-place-theme'), $listings_query->found_posts),
    'content' => hph_get_archive_description(),
    'buttons' => array(
        array(
            'text' => 'Advanced Search',
            'url' => '#search-filters',
            'style' => 'white',
            'size' => 'lg',
            'icon' => 'fas fa-search'
        ),
        array(
            'text' => 'Contact an Agent',
            'url' => get_post_type_archive_link('agent'),
            'style' => 'outline-white',
            'size' => 'lg',
            'icon' => 'fas fa-user-tie'
        )
    ),
    'section_id' => 'property-hero'
));

// Archive Layout Section - Main listings grid with filters
get_template_part('template-parts/layout/archive-layout', null, array(
    'post_type' => 'listing',
    'posts' => $listings,
    'title' => hph_get_archive_title(),
    'description' => hph_get_archive_description(),
    'total_results' => $listings_query->found_posts,
    'current_view' => $current_view,
    'current_sort' => $current_sort,
    'per_page' => $per_page,
    'paged' => $paged,
    'max_pages' => $listings_query->max_num_pages,
    'view_modes' => ['grid', 'list', 'map'],
    'sort_options' => [
        'date-desc' => __('Newest First', 'happy-place-theme'),
        'date-asc' => __('Oldest First', 'happy-place-theme'),
        'price-desc' => __('Price: High to Low', 'happy-place-theme'),
        'price-asc' => __('Price: Low to High', 'happy-place-theme'),
        'featured' => __('Featured First', 'happy-place-theme')
    ],
    'show_search' => true,
    'show_filters' => true,
    'show_save_search' => is_user_logged_in(),
    'ajax_enabled' => true,
    'sidebar' => 'listing-archive'
));

// Market Stats Section - Using content section with stats
if (!empty($listings)) {
    // Calculate market stats
    $total_active = 0;
    $avg_price = 0;
    $price_count = 0;
    $total_sq_ft = 0;
    $sq_ft_count = 0;
    
    foreach ($listings as $listing) {
        if (function_exists('hpt_get_listing')) {
            $listing_data = hpt_get_listing($listing->ID);
            if ($listing_data && $listing_data['status'] === 'active') {
                $total_active++;
            }
            if ($listing_data && isset($listing_data['price']) && $listing_data['price'] > 0) {
                $avg_price += $listing_data['price'];
                $price_count++;
            }
            if ($listing_data && isset($listing_data['square_feet']) && $listing_data['square_feet'] > 0) {
                $total_sq_ft += $listing_data['square_feet'];
                $sq_ft_count++;
            }
        }
    }
    
    $avg_price = $price_count > 0 ? $avg_price / $price_count : 0;
    $avg_sq_ft = $sq_ft_count > 0 ? $total_sq_ft / $sq_ft_count : 0;
    
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'full-width',
        'background' => 'light',
        'padding' => 'xl',
        'badge' => 'Market Overview',
        'headline' => 'Delaware Real Estate Market',
        'content' => 'Current market statistics and trends for Delaware properties.',
        'stats' => array(
            array(
                'value' => $total_active,
                'label' => __('Active Listings', 'happy-place-theme'),
                'icon' => 'fas fa-home',
                'format' => 'number'
            ),
            array(
                'value' => $avg_price,
                'label' => __('Average Price', 'happy-place-theme'),
                'icon' => 'fas fa-dollar-sign',
                'format' => 'currency'
            ),
            array(
                'value' => $avg_sq_ft,
                'label' => __('Average Sq Ft', 'happy-place-theme'),
                'icon' => 'fas fa-ruler-combined',
                'format' => 'number'
            ),
            array(
                'value' => $listings_query->found_posts,
                'label' => __('Total Properties', 'happy-place-theme'),
                'icon' => 'fas fa-building',
                'format' => 'number'
            )
        ),
        'section_id' => 'market-stats'
    ));
}

// Featured Properties Section - Only if we have listings
if (!empty($listings)) {
    $featured_args = [
        'post_type' => 'listing',
        'posts_per_page' => 3,
        'post__not_in' => wp_list_pluck($listings, 'ID'),
        'meta_query' => [
            [
                'key' => '_listing_featured',
                'value' => '1',
                'compare' => '='
            ]
        ]
    ];
    
    $featured_query = new WP_Query($featured_args);
    
    if ($featured_query->have_posts()) {
        get_template_part('template-parts/sections/featured-properties', null, array(
            'background' => 'white',
            'padding' => 'xl',
            'badge' => 'Featured',
            'headline' => 'Premium Properties',
            'subheadline' => 'Handpicked Selection',
            'content' => 'Discover our curated collection of exceptional Delaware properties.',
            'query' => $featured_query,
            'columns' => 3,
            'card_style' => 'featured',
            'buttons' => array(
                array(
                    'text' => 'View All Featured',
                    'url' => add_query_arg('featured', '1', get_post_type_archive_link('listing')),
                    'style' => 'primary',
                    'size' => 'md',
                    'icon' => 'fas fa-star'
                )
            ),
            'section_id' => 'featured-properties'
        ));
    }
}

// Saved Searches Section - For logged in users
if (is_user_logged_in()) {
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'centered',
        'background' => 'white',
        'padding' => 'lg',
        'border' => 'top',
        'badge' => 'Your Account',
        'headline' => 'Saved Searches',
        'content' => 'Manage and track your property search criteria for faster browsing.',
        'custom_content' => '<div id="user-saved-searches" data-user-id="' . get_current_user_id() . '" class="hph-bg-gray-50 hph-rounded-lg hph-p-lg hph-min-h-32"></div>',
        'buttons' => array(
            array(
                'text' => 'Manage All Searches',
                'url' => home_url('/dashboard/saved-searches'),
                'style' => 'primary',
                'size' => 'md',
                'icon' => 'fas fa-cog'
            )
        ),
        'section_id' => 'saved-searches'
    ));
}

// Contact CTA Section
get_template_part('template-parts/sections/content', null, array(
    'layout' => 'centered',
    'background' => 'primary',
    'padding' => 'xl',
    'headline' => 'Need Help Finding Your Perfect Property?',
    'content' => 'Our experienced Delaware real estate agents are ready to help you find exactly what you\'re looking for. From first-time buyers to seasoned investors, we guide you every step of the way.',
    'buttons' => array(
        array(
            'text' => 'Contact an Agent',
            'url' => home_url('/contact'),
            'style' => 'white',
            'size' => 'lg',
            'icon' => 'fas fa-comments'
        ),
        array(
            'text' => 'Meet Our Team',
            'url' => get_post_type_archive_link('agent'),
            'style' => 'outline-white',
            'size' => 'lg',
            'icon' => 'fas fa-users'
        )
    ),
    'section_id' => 'contact-cta'
));

// Service Areas Section
get_template_part('template-parts/sections/hero', null, array(
    'style' => 'image',
    'height' => 'sm',
    'background_image' => get_template_directory_uri() . '/assets/images/delaware-map.jpg',
    'overlay' => 'gradient',
    'overlay_opacity' => '60',
    'alignment' => 'center',
    'content_width' => 'normal',
    'badge' => 'Service Areas',
    'headline' => 'Serving All of Delaware',
    'subheadline' => 'Georgetown • Milford • Middletown • Sussex • Kent • New Castle',
    'content' => 'From coastal communities to growing suburban areas, we know Delaware inside and out.',
    'buttons' => array(
        array(
            'text' => 'View Service Areas',
            'url' => '/areas/',
            'style' => 'white',
            'size' => 'lg',
            'icon' => 'fas fa-map-marker-alt'
        )
    ),
    'section_id' => 'service-areas'
));

// Enqueue page-specific assets
wp_enqueue_style('hph-archive-listing', get_template_directory_uri() . '/assets/css/pages/archive-listing.css', ['hph-framework'], get_theme_mod('theme_version', '3.0.0'));
wp_enqueue_style('hph-archive-enhancements', get_template_directory_uri() . '/assets/css/archive-enhancements.css', ['hph-framework'], get_theme_mod('theme_version', '3.0.0'));
wp_enqueue_script('hph-archive-listing', get_template_directory_uri() . '/assets/js/pages/archive-listing.js', ['hph-framework-core'], get_theme_mod('theme_version', '3.0.0'), true);
wp_enqueue_script('hph-archive-functionality', get_template_directory_uri() . '/assets/js/archive-functionality.js', ['jquery'], get_theme_mod('theme_version', '3.0.0'), true);

// Localize script with current context
wp_localize_script('hph-archive-listing', 'hphArchive', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_archive_nonce'),
    'postType' => 'listing',
    'currentView' => $current_view,
    'currentSort' => $current_sort,
    'perPage' => $per_page,
    'currentPage' => $paged,
    'maxPages' => $listings_query->max_num_pages,
    'totalResults' => $listings_query->found_posts,
    'isLoggedIn' => is_user_logged_in(),
    'userId' => get_current_user_id(),
    'strings' => [
        'loading' => __('Loading...', 'happy-place-theme'),
        'loadingMore' => __('Loading more properties...', 'happy-place-theme'),
        'noResults' => __('No properties found.', 'happy-place-theme'),
        'error' => __('An error occurred. Please try again.', 'happy-place-theme'),
        'searchSaved' => __('Search saved successfully!', 'happy-place-theme'),
        'loginRequired' => __('Please log in to save searches.', 'happy-place-theme')
    ]
]);

get_footer();
?>