<?php
/**
 * Archive Hero Helper Functions
 * 
 * @package HappyPlaceTheme
 */

if (!function_exists('hph_get_archive_hero_config')) {
    /**
     * Get post type specific archive hero configuration
     * 
     * @param string $post_type Current post type
     * @param array $overrides Optional configuration overrides
     * @return array Hero configuration array
     */
    function hph_get_archive_hero_config($post_type = 'post', $overrides = []) {
        // Base configurations for different post types
        $configs = [
            'agent' => [
                'title' => 'Our Real Estate Agents',
                'subtitle' => 'Meet our experienced team of professionals ready to help you find your dream home',
                'search_placeholder' => 'Search agents by name, specialty, or location...',
                'background_type' => 'gradient',
                'gradient' => 'linear-gradient(135deg, #51bae0 0%, #0c4a6e 100%)',
                'quick_filters' => [
                    'featured' => 'Featured',
                    'luxury' => 'Luxury Specialist',
                    'commercial' => 'Commercial',
                    'residential' => 'Residential',
                    'new-construction' => 'New Construction'
                ],
                'stats' => [
                    'total_agents' => wp_count_posts('agent')->publish,
                    'average_experience' => '15+ years',
                    'properties_sold' => '2,500+'
                ],
                'show_stats' => true
            ],
            'listing' => [
                'title' => 'Available Properties',
                'subtitle' => 'Discover your perfect home from our curated selection',
                'search_placeholder' => 'Search by address, city, ZIP, or MLS#...',
                'background_type' => 'gradient',
                'gradient' => 'linear-gradient(135deg, #7c9b59 0%, #4a5e35 100%)',
                'quick_filters' => [
                    'for-sale' => 'For Sale',
                    'for-rent' => 'For Rent',
                    'new-listing' => 'New Listings',
                    'open-house' => 'Open Houses',
                    'reduced' => 'Price Reduced'
                ],
                'stats' => [
                    'active_listings' => wp_count_posts('listing')->publish,
                    'avg_days_on_market' => '28',
                    'sold_this_month' => '45'
                ],
                'show_stats' => true
            ],
            'post' => [
                'title' => get_the_archive_title(),
                'subtitle' => get_the_archive_description(),
                'search_placeholder' => 'Search articles...',
                'background_type' => 'gradient',
                'gradient' => 'linear-gradient(135deg, #e8a87c 0%, #9c6347 100%)',
                'quick_filters' => [],
                'stats' => [],
                'show_stats' => false
            ]
        ];

        // Get base config for post type
        $config = $configs[$post_type] ?? $configs['post'];
        
        // Add dynamic filters based on post type
        $config = hph_add_dynamic_filters($config, $post_type);
        
        // Apply context-aware modifications
        $config = hph_apply_context_modifications($config);
        
        // Merge with any overrides
        return wp_parse_args($overrides, $config);
    }
}

if (!function_exists('hph_add_dynamic_filters')) {
    /**
     * Add dynamic filters based on actual data
     * 
     * @param array $config Current configuration
     * @param string $post_type Post type
     * @return array Updated configuration
     */
    function hph_add_dynamic_filters($config, $post_type) {
        switch ($post_type) {
            case 'agent':
                // Get from ACF field choices if available
                $specialty_field = get_field_object('field_specialties');
                if ($specialty_field && isset($specialty_field['choices'])) {
                    $config['quick_filters'] = array_merge(
                        $config['quick_filters'], 
                        $specialty_field['choices']
                    );
                }
                break;
                
            case 'listing':
                // Get from property type taxonomy
                $property_types = get_terms([
                    'taxonomy' => 'property_type', 
                    'hide_empty' => true
                ]);
                if (!is_wp_error($property_types)) {
                    $filters = [];
                    foreach ($property_types as $type) {
                        $filters[$type->slug] = $type->name;
                    }
                    $config['quick_filters'] = array_merge(
                        $config['quick_filters'], 
                        $filters
                    );
                }
                break;
        }
        
        return $config;
    }
}

if (!function_exists('hph_apply_context_modifications')) {
    /**
     * Apply context-aware title/subtitle modifications
     * 
     * @param array $config Current configuration
     * @return array Updated configuration
     */
    function hph_apply_context_modifications($config) {
        global $wp_query;
        
        if (is_tax()) {
            $term = get_queried_object();
            $config['title'] = single_term_title('', false);
            $config['subtitle'] = $term->description ?: $config['subtitle'];
        } elseif (is_search()) {
            $total_results = $wp_query->found_posts ?? 0;
            $config['title'] = sprintf('Search Results for "%s"', get_search_query());
            $config['subtitle'] = sprintf('Found %d results', $total_results);
        } elseif (is_author()) {
            $author = get_queried_object();
            $config['title'] = 'Agent Profile: ' . $author->display_name;
            $config['subtitle'] = $author->description ?: 'View all listings and information for this agent';
        } elseif (is_date()) {
            $config['title'] = get_the_archive_title();
            $config['subtitle'] = get_the_archive_description();
        }
        
        return $config;
    }
}

if (!function_exists('hph_render_archive_hero')) {
    /**
     * Render archive hero with simplified function call
     * 
     * @param string $post_type Post type
     * @param array $args Additional arguments
     */
    function hph_render_archive_hero($post_type = 'post', $args = []) {
        $config = hph_get_archive_hero_config($post_type, $args);
        get_template_part('template-parts/layout/archive-header', null, $config);
    }
}
