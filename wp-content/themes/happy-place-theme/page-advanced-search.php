<?php
/**
 * Template Name: Advanced Search
 * 
 * Universal advanced search page that searches across all post types
 * Supports natural language queries and complex filtering
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();

// Get search parameters
$search_query = sanitize_text_field($_GET['s'] ?? '');
$search_type = sanitize_text_field($_GET['type'] ?? 'all');
$paged = get_query_var('paged', 1);
$posts_per_page = 12;

// Initialize results arrays
$results = [
    'listings' => [],
    'agents' => [],
    'cities' => [],
    'communities' => [],
    'total' => 0
];

// If there's a search query, perform the search
if (!empty($search_query)) {
    // Search listings
    if ($search_type === 'all' || $search_type === 'listing') {
        $listing_query = new WP_Query([
            'post_type' => 'listing',
            'posts_per_page' => $search_type === 'listing' ? $posts_per_page : 5,
            'paged' => $search_type === 'listing' ? $paged : 1,
            'post_status' => 'publish',
            's' => $search_query,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'mls_number',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'street_address',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ]
            ]
        ]);
        $results['listings'] = $listing_query->posts;
        if ($search_type === 'listing') {
            $results['total'] = $listing_query->found_posts;
            $results['max_pages'] = $listing_query->max_num_pages;
        }
        wp_reset_postdata();
    }
    
    // Search agents
    if ($search_type === 'all' || $search_type === 'agent') {
        $agent_query = new WP_Query([
            'post_type' => 'agent',
            'posts_per_page' => $search_type === 'agent' ? $posts_per_page : 3,
            'paged' => $search_type === 'agent' ? $paged : 1,
            'post_status' => 'publish',
            's' => $search_query,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'agent_email',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'agent_bio',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'agent_specialties',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ]
            ]
        ]);
        $results['agents'] = $agent_query->posts;
        if ($search_type === 'agent') {
            $results['total'] = $agent_query->found_posts;
            $results['max_pages'] = $agent_query->max_num_pages;
        }
        wp_reset_postdata();
    }
    
    // Search cities
    if ($search_type === 'all' || $search_type === 'city') {
        $city_query = new WP_Query([
            'post_type' => 'city',
            'posts_per_page' => $search_type === 'city' ? $posts_per_page : 3,
            'paged' => $search_type === 'city' ? $paged : 1,
            'post_status' => 'publish',
            's' => $search_query,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'state',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'city_description',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ]
            ]
        ]);
        $results['cities'] = $city_query->posts;
        if ($search_type === 'city') {
            $results['total'] = $city_query->found_posts;
            $results['max_pages'] = $city_query->max_num_pages;
        }
        wp_reset_postdata();
    }
    
    // Search communities
    if ($search_type === 'all' || $search_type === 'community') {
        $community_query = new WP_Query([
            'post_type' => 'community',
            'posts_per_page' => $search_type === 'community' ? $posts_per_page : 3,
            'paged' => $search_type === 'community' ? $paged : 1,
            'post_status' => 'publish',
            's' => $search_query,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'community_description',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'community_features',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ]
            ]
        ]);
        $results['communities'] = $community_query->posts;
        if ($search_type === 'community') {
            $results['total'] = $community_query->found_posts;
            $results['max_pages'] = $community_query->max_num_pages;
        }
        wp_reset_postdata();
    }
    
    // Calculate total for "all" search
    if ($search_type === 'all') {
        $results['total'] = count($results['listings']) + count($results['agents']) + count($results['cities']) + count($results['communities']);
    }
}
?>

<main class="hph-main hph-advanced-search-page" role="main">
    
    <!-- Hero Section with Search -->
    <section class="hph-hero hph-hero-gradient" 
             style="background: var(--hph-gradient-primary); color: var(--hph-white); padding: var(--hph-padding-3xl) 0; position: relative; overflow: hidden; min-height: 60vh; display: flex; align-items: center;">
        <div style="max-width: var(--hph-container-xl); margin: 0 auto; padding: 0 var(--hph-space-6); width: 100%;">
            <div style="text-align: center; color: var(--hph-white);">
                
                <!-- Badge -->
                <div style="margin-bottom: var(--hph-space-6);">
                    <span style="display: inline-flex; align-items: center; gap: var(--hph-gap-sm); padding: var(--hph-space-2) var(--hph-space-6); background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius: var(--hph-radius-full); font-size: var(--hph-text-base); font-weight: var(--hph-font-semibold);">
                        <i class="fas fa-search"></i>
                        Advanced Search
                    </span>
                </div>
                
                <!-- Title -->
                <h1 style="font-size: clamp(var(--hph-text-4xl), 5vw, var(--hph-text-6xl)); font-weight: var(--hph-font-bold); margin: 0 0 var(--hph-space-6) 0; line-height: var(--hph-leading-tight);">
                    Find What You're Looking For
                </h1>
                
                <!-- Subtitle -->
                <p style="font-size: clamp(var(--hph-text-lg), 2vw, var(--hph-text-2xl)); margin: 0 auto var(--hph-margin-2xl) auto; opacity: 0.9; max-width: 900px; line-height: var(--hph-leading-normal);">
                    Search properties, agents, cities, and communities with our powerful search engine. 
                    Try natural language like "3 bedroom house in Rehoboth with pool"
                </p>
                
                <!-- Advanced Search Form -->
                <?php 
                hph_component('search-form', [
                    'form_id' => 'hero-advanced-search',
                    'placeholder' => __('Search properties, agents, cities, or try "waterfront home under 500k"...', 'happy-place-theme'),
                    'show_filters' => true,
                    'show_post_type_selector' => true,
                    'current_post_type' => $search_type,
                    'current_query' => $search_query,
                    'advanced_mode' => true,
                    'form_action' => get_permalink(),
                    'form_method' => 'GET'
                ]); 
                ?>
                
            </div>
        </div>
    </section>
    
    <!-- Results Section -->
    <?php if (!empty($search_query)) : ?>
        
        <section style="background: var(--hph-white); padding: var(--hph-padding-3xl) 0;">
            <div style="max-width: var(--hph-container-2xl); margin: 0 auto; padding: 0 var(--hph-space-6);">
                
                <!-- Results Header -->
                <div style="text-align: center; margin-bottom: var(--hph-margin-2xl);">
                    <h2 style="font-size: var(--hph-text-3xl); font-weight: var(--hph-font-bold); margin: 0 0 var(--hph-space-4) 0; color: var(--hph-gray-900);">
                        <?php if ($search_type === 'all') : ?>
                            Search Results for "<?php echo esc_html($search_query); ?>"
                        <?php else : ?>
                            <?php echo ucfirst($search_type); ?> Results for "<?php echo esc_html($search_query); ?>"
                        <?php endif; ?>
                    </h2>
                    
                    <?php if ($results['total'] > 0) : ?>
                        <p style="color: var(--hph-gray-600); font-size: var(--hph-text-lg);">
                            Found <?php echo number_format($results['total']); ?> result<?php echo $results['total'] !== 1 ? 's' : ''; ?>
                        </p>
                    <?php else : ?>
                        <p style="color: var(--hph-gray-600); font-size: var(--hph-text-lg);">No results found. Try different search terms or filters.</p>
                    <?php endif; ?>
                </div>
                
                <?php if ($results['total'] > 0) : ?>
                    
                    <?php if ($search_type === 'all') : ?>
                        
                        <!-- Multi-type Results -->
                        
                        <?php if (!empty($results['listings'])) : ?>
                            <div style="margin-bottom: var(--hph-margin-3xl);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--hph-space-8); flex-wrap: wrap; gap: var(--hph-gap-md);">
                                    <h3 style="font-size: var(--hph-text-2xl); font-weight: var(--hph-font-semibold); color: var(--hph-gray-900); margin: 0;">
                                        Properties (<?php echo count($results['listings']); ?>)
                                    </h3>
                                    <a href="<?php echo esc_url(add_query_arg(['s' => $search_query, 'type' => 'listing'])); ?>" 
                                       style="display: inline-flex; align-items: center; gap: var(--hph-gap-sm); padding: var(--hph-space-2) var(--hph-space-6); border: 2px solid var(--hph-primary); border-radius: var(--hph-radius-md); color: var(--hph-primary); text-decoration: none; font-size: var(--hph-text-sm); font-weight: var(--hph-font-medium); transition: all 0.2s ease;"
                                       onmouseover="this.style.background='var(--hph-primary)'; this.style.color='var(--hph-white)'"
                                       onmouseout="this.style.background='transparent'; this.style.color='var(--hph-primary)'">
                                        View All Properties
                                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--hph-gap-lg);">
                                    <?php foreach ($results['listings'] as $post) : ?>
                                        <?php setup_postdata($post); ?>
                                        <?php 
                                        hph_component('universal-card', [
                                            'post_id' => get_the_ID(),
                                            'layout' => 'vertical',
                                            'show_agent' => true
                                        ]);
                                        ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($results['agents'])) : ?>
                            <div class="hph-results-section hph-mb-3xl">
                                <div class="hph-flex hph-justify-between hph-items-center hph-mb-xl">
                                    <h3 class="hph-text-2xl hph-font-semibold">
                                        Agents (<?php echo count($results['agents']); ?>)
                                    </h3>
                                    <a href="<?php echo esc_url(add_query_arg(['s' => $search_query, 'type' => 'agent'])); ?>" class="hph-btn hph-btn-outline hph-btn-sm">
                                        View All Agents
                                    </a>
                                </div>
                                
                                <div class="hph-grid hph-grid-cols-1 hph-md:grid-cols-2 hph-lg:grid-cols-3 hph-gap-lg">
                                    <?php foreach ($results['agents'] as $post) : ?>
                                        <?php setup_postdata($post); ?>
                                        <?php 
                                        // Use unified component system
                                        hph_component('agent-card', [
                                            'agent_id' => get_the_ID(),
                                            'layout' => 'compact',
                                            'show_stats' => true
                                        ]);
                                        ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($results['cities'])) : ?>
                            <div class="hph-results-section hph-mb-3xl">
                                <div class="hph-flex hph-justify-between hph-items-center hph-mb-xl">
                                    <h3 class="hph-text-2xl hph-font-semibold">
                                        Cities (<?php echo count($results['cities']); ?>)
                                    </h3>
                                    <a href="<?php echo esc_url(add_query_arg(['s' => $search_query, 'type' => 'city'])); ?>" class="hph-btn hph-btn-outline hph-btn-sm">
                                        View All Cities
                                    </a>
                                </div>
                                
                                <div class="hph-grid hph-grid-cols-1 hph-md:grid-cols-2 hph-lg:grid-cols-4 hph-gap-lg">
                                    <?php foreach ($results['cities'] as $post) : ?>
                                        <?php setup_postdata($post); ?>
                                        <?php 
                                        // Use universal card system for cities
                                        hph_component('universal-card', [
                                            'post_id' => get_the_ID(),
                                            'layout' => 'vertical'
                                        ]);
                                        ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($results['communities'])) : ?>
                            <div class="hph-results-section hph-mb-3xl">
                                <div class="hph-flex hph-justify-between hph-items-center hph-mb-xl">
                                    <h3 class="hph-text-2xl hph-font-semibold">
                                        Communities (<?php echo count($results['communities']); ?>)
                                    </h3>
                                    <a href="<?php echo esc_url(add_query_arg(['s' => $search_query, 'type' => 'community'])); ?>" class="hph-btn hph-btn-outline hph-btn-sm">
                                        View All Communities
                                    </a>
                                </div>
                                
                                <div class="hph-grid hph-grid-cols-1 hph-md:grid-cols-2 hph-lg:grid-cols-3 hph-gap-lg">
                                    <?php foreach ($results['communities'] as $post) : ?>
                                        <?php setup_postdata($post); ?>
                                        <?php 
                                        // Use universal card system for communities
                                        hph_component('universal-card', [
                                            'post_id' => get_the_ID(),
                                            'layout' => 'vertical'
                                        ]);
                                        ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    <?php else : ?>
                        
                        <!-- Single Type Results -->
                        <?php 
                        $single_results = $results[$search_type . 's'] ?? [];
                        if (!empty($single_results)) : ?>
                            
                            <div class="hph-grid hph-grid-cols-1 hph-md:grid-cols-2 hph-lg:grid-cols-3 hph-gap-lg">
                                <?php foreach ($single_results as $post) : ?>
                                    <?php setup_postdata($post); ?>
                                    <?php 
                                    // Use unified component system
                                    $component_name = $search_type . '-card';
                                    if (array_key_exists($component_name, HPH_Component_Loader::get_components())) {
                                        hph_component($component_name, [
                                            $search_type . '_id' => get_the_ID(),
                                            'layout' => 'grid',
                                            'variant' => 'compact'
                                        ]);
                                    } else {
                                        // Use universal card as fallback
                                        hph_component('universal-card', [
                                            'post_id' => get_the_ID(),
                                            'layout' => 'vertical'
                                        ]);
                                    }
                                    ?>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination for single type results -->
                            <?php if (isset($results['max_pages']) && $results['max_pages'] > 1) : ?>
                                <div class="hph-pagination-wrapper hph-mt-3xl hph-text-center">
                                    <?php
                                    echo paginate_links([
                                        'current' => $paged,
                                        'total' => $results['max_pages'],
                                        'prev_text' => '<i class="fas fa-chevron-left"></i> Previous',
                                        'next_text' => 'Next <i class="fas fa-chevron-right"></i>',
                                        'type' => 'list'
                                    ]);
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                        <?php endif; ?>
                        
                    <?php endif; ?>
                    
                <?php else : ?>
                    
                    <!-- No Results -->
                    <div style="text-align: center; padding: var(--hph-padding-3xl); background: var(--hph-gray-50); border-radius: var(--hph-radius-lg); border: 1px solid var(--hph-gray-200);">
                        <div style="max-width: 500px; margin: 0 auto;">
                            <svg style="width: 4rem; height: 4rem; color: var(--hph-gray-400); margin: 0 auto var(--hph-space-6) auto;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <h3 style="font-size: var(--hph-text-2xl); font-weight: var(--hph-font-bold); color: var(--hph-gray-900); margin: 0 0 var(--hph-space-4) 0;">
                                No Results Found
                            </h3>
                            <p style="color: var(--hph-gray-600); font-size: var(--hph-text-lg); line-height: var(--hph-leading-relaxed); margin: 0 0 var(--hph-space-8) 0;">
                                We couldn't find anything matching "<?php echo esc_html($search_query); ?>". 
                                Try different keywords or browse our categories.
                            </p>
                            <div style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-md); justify-content: center;">
                                <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" 
                                   style="display: inline-flex; align-items: center; gap: var(--hph-gap-sm); padding: var(--hph-space-2) var(--hph-space-6); border: 2px solid var(--hph-primary); border-radius: var(--hph-radius-md); background: var(--hph-primary); color: var(--hph-white); text-decoration: none; font-weight: var(--hph-font-semibold); transition: all 0.2s ease;"
                                   onmouseover="this.style.background='var(--hph-primary-600)'; this.style.borderColor='var(--hph-primary-600)'"
                                   onmouseout="this.style.background='var(--hph-primary)'; this.style.borderColor='var(--hph-primary)'">
                                    <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    Browse Properties
                                </a>
                                <a href="<?php echo esc_url(get_post_type_archive_link('agent')); ?>" 
                                   style="display: inline-flex; align-items: center; gap: var(--hph-gap-sm); padding: var(--hph-space-2) var(--hph-space-6); border: 2px solid var(--hph-primary); border-radius: var(--hph-radius-md); background: transparent; color: var(--hph-primary); text-decoration: none; font-weight: var(--hph-font-medium); transition: all 0.2s ease;"
                                   onmouseover="this.style.background='var(--hph-primary)'; this.style.color='var(--hph-white)'"
                                   onmouseout="this.style.background='transparent'; this.style.color='var(--hph-primary)'">
                                    <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Find Agents
                                </a>
                            </div>
                        </div>
                    </div>
                    
                <?php endif; ?>
                
            </div>
        </section>
        
    <?php else : ?>
        
        <!-- Search Tips Section -->
        <section class="hph-section hph-section-light">
            <div class="hph-container">
                <div class="hph-text-center hph-mb-2xl">
                    <h2 class="hph-text-3xl hph-font-bold hph-mb-md">Search Tips</h2>
                    <p class="hph-text-gray-600">Get the most out of our advanced search</p>
                </div>
                
                <div class="hph-grid hph-grid-cols-1 hph-md:grid-cols-2 hph-lg:grid-cols-3 hph-gap-lg">
                    
                    <div class="hph-card hph-text-center">
                        <div class="hph-card-body">
                            <i class="fas fa-home hph-text-3xl hph-text-primary hph-mb-md"></i>
                            <h3 class="hph-font-semibold hph-mb-sm">Property Search</h3>
                            <p class="hph-text-sm hph-text-gray-600">
                                Try "3 bedroom waterfront under 500k" or "Rehoboth Beach condo with pool"
                            </p>
                        </div>
                    </div>
                    
                    <div class="hph-card hph-text-center">
                        <div class="hph-card-body">
                            <i class="fas fa-user-tie hph-text-3xl hph-text-primary hph-mb-md"></i>
                            <h3 class="hph-font-semibold hph-mb-sm">Agent Search</h3>
                            <p class="hph-text-sm hph-text-gray-600">
                                Search by name, specialty, or area like "luxury agent" or "John Smith"
                            </p>
                        </div>
                    </div>
                    
                    <div class="hph-card hph-text-center">
                        <div class="hph-card-body">
                            <i class="fas fa-map-marked-alt hph-text-3xl hph-text-primary hph-mb-md"></i>
                            <h3 class="hph-font-semibold hph-mb-sm">Location Search</h3>
                            <p class="hph-text-sm hph-text-gray-600">
                                Find cities and communities by name, school district, or features
                            </p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>
        
    <?php endif; ?>
    
</main>

<?php wp_reset_postdata(); ?>

<?php get_footer(); ?>
