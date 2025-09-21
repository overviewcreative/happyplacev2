<?php
/**
 * Archive Hero Helper Functions
 * 
 * Provides consistent hero data for all archive types
 * 
 * @package Happy_Place_Theme
 */

if (!function_exists('hpt_get_archive_hero_data')) {
    /**
     * Get hero data for archive pages
     * 
     * @param string $post_type Optional. Post type to get data for. Defaults to current query.
     * @param array $overrides Optional. Data to override defaults.
     * @return array Hero data array
     */
    function hpt_get_archive_hero_data($post_type = '', $overrides = []) {
        if (empty($post_type)) {
            $post_type = get_post_type() ?: get_query_var('post_type') ?: 'post';
        }
        
        // Default hero configurations for each post type
        $hero_configs = [
            'local_place' => [
                'title' => 'Discover Amazing Local Places',
                'subtitle' => 'Find unique destinations, hidden gems, and local favorites in your area',
                'background_image' => get_template_directory_uri() . '/assets/images/archive-place-bg.jpg',
                'background_color' => 'var(--hph-primary, #50bae1)',
                'show_search' => true,
                'show_filters' => true
            ],
            'city' => [
                'title' => 'Explore Cities & Towns',
                'subtitle' => 'Discover the best places to visit, live, and explore across different cities',
                'background_image' => get_template_directory_uri() . '/assets/images/archive-city-bg.jpg',
                'background_color' => 'var(--hph-secondary, #e8a87c)',
                'show_search' => true,
                'show_filters' => true
            ],
            'open_house' => [
                'title' => 'Open Houses Near You',
                'subtitle' => 'Tour homes that are open for viewing this weekend',
                'background_image' => get_template_directory_uri() . '/assets/images/archive-openhouse-bg.jpg',
                'background_color' => 'var(--hph-danger, #e57f6c)',
                'show_search' => true,
                'show_filters' => true
            ],
            'community' => [
                'title' => 'Browse Communities',
                'subtitle' => 'Find the perfect neighborhood and community for your lifestyle',
                'background_image' => get_template_directory_uri() . '/assets/images/archive-community-bg.jpg',
                'background_color' => '#9333ea',
                'show_search' => true,
                'show_filters' => true
            ],
            'event' => [
                'title' => 'Upcoming Events',
                'subtitle' => 'Discover local events, activities, and happenings in your area',
                'background_image' => get_template_directory_uri() . '/assets/images/archive-event-bg.jpg',
                'background_color' => '#ea580c',
                'show_search' => true,
                'show_filters' => true
            ],
            'post' => [
                'title' => 'Latest News & Insights',
                'subtitle' => 'Stay informed with our latest articles, market updates, and expert insights',
                'background_image' => get_template_directory_uri() . '/assets/images/archive-blog-bg.jpg',
                'background_color' => 'var(--hph-info, #51bae0)',
                'show_search' => true,
                'show_filters' => true
            ],
            'blog_post' => [
                'title' => 'Our Blog',
                'subtitle' => 'Stories, insights, and updates from our team',
                'background_image' => get_template_directory_uri() . '/assets/images/archive-blog-bg.jpg',
                'background_color' => 'var(--hph-info, #51bae0)',
                'show_search' => true,
                'show_filters' => true,
                'search_form' => 'hero-search-blog_post'
            ]
        ];
        
        // Get default config for this post type
        $default_config = $hero_configs[$post_type] ?? $hero_configs['local_place'];
        
        // Add post type to config
        $default_config['post_type'] = $post_type;
        
        // Check for custom hero title/subtitle from archive options
        if (function_exists('get_field')) {
            $custom_title = get_field('archive_hero_title', $post_type . '_archive_options');
            $custom_subtitle = get_field('archive_hero_subtitle', $post_type . '_archive_options');
            $custom_background = get_field('archive_hero_background', $post_type . '_archive_options');
            
            if (!empty($custom_title)) {
                $default_config['title'] = $custom_title;
            }
            if (!empty($custom_subtitle)) {
                $default_config['subtitle'] = $custom_subtitle;
            }
            if (!empty($custom_background)) {
                $default_config['background_image'] = $custom_background;
            }
        }
        
        // Apply any overrides
        if (!empty($overrides)) {
            $default_config = wp_parse_args($overrides, $default_config);
        }
        
        // Apply filters for theme/plugin customization
        return apply_filters('hpt_archive_hero_data', $default_config, $post_type);
    }
}

if (!function_exists('hpt_get_archive_hero_background_url')) {
    /**
     * Get the background image URL for an archive hero
     * 
     * @param string $post_type Post type
     * @param string $fallback_filename Fallback filename in theme images directory
     * @return string Background image URL
     */
    function hpt_get_archive_hero_background_url($post_type, $fallback_filename = 'archive-default-bg.jpg') {
        $theme_images_dir = get_template_directory_uri() . '/assets/images/';
        
        // Try post-type specific image first
        $specific_filename = 'archive-' . str_replace('_', '', $post_type) . '-bg.jpg';
        $specific_path = get_template_directory() . '/assets/images/' . $specific_filename;
        
        if (file_exists($specific_path)) {
            return $theme_images_dir . $specific_filename;
        }
        
        // Fall back to provided fallback
        $fallback_path = get_template_directory() . '/assets/images/' . $fallback_filename;
        if (file_exists($fallback_path)) {
            return $theme_images_dir . $fallback_filename;
        }
        
        // Ultimate fallback - empty string (will use CSS background color)
        return '';
    }
}

if (!function_exists('hpt_render_archive_hero')) {
    /**
     * Render the archive hero section
     * 
     * @param array $hero_data Optional. Hero data array. If empty, will auto-generate.
     */
    function hpt_render_archive_hero($hero_data = []) {
        if (empty($hero_data)) {
            $hero_data = hpt_get_archive_hero_data();
        }

        get_template_part('template-parts/archive-hero', null, $hero_data);
    }
}

if (!function_exists('hpt_get_archive_hero_data')) {
    /**
     * Get archive hero data based on current context
     *
     * @param array $overrides Optional. Data to override defaults.
     * @return array Hero configuration array
     */
    function hpt_get_archive_hero_data($overrides = []) {
        $post_type = get_query_var('post_type') ?: 'post';

        // Default configurations for different post types
        $configs = [
            'listing' => [
                'title' => 'Available Properties',
                'subtitle' => 'Find your perfect home in our exclusive listings',
                'search_placeholder' => 'Search by city, address, or MLS...',
                'background_type' => 'image',
                'background_image' => hpt_get_archive_hero_background_url('listing'),
                'quick_filters' => [
                    ['label' => 'For Sale', 'value' => 'active', 'type' => 'status'],
                    ['label' => 'Under Contract', 'value' => 'pending', 'type' => 'status'],
                    ['label' => 'Recently Sold', 'value' => 'sold', 'type' => 'status']
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

if (!function_exists('hpt_generate_listing_section_title')) {
    /**
     * Generate dynamic listing section title based on filters
     *
     * @param array $args Universal loop arguments
     * @return string Generated title
     */
    function hpt_generate_listing_section_title($args) {
        // Check for change tracking based titles first
        if (!empty($args['recent_changes_only'])) {
            return 'Recently Updated';
        }

        if (!empty($args['new_listings_only'])) {
            return 'New Listings';
        }

        if (!empty($args['price_changes_only'])) {
            return 'Price Reductions';
        }

        // Check for specific filter-based titles
        if ($args['featured_only']) {
            return 'Featured Properties';
        }

        if ($args['open_house_only']) {
            return 'Open Houses';
        }

        if (!empty($args['property_status'])) {
            switch ($args['property_status']) {
                case 'active':
                    return 'Available Properties';
                case 'pending':
                    return 'Under Contract';
                case 'sold':
                    return 'Recently Sold';
                default:
                    return 'Properties';
            }
        }

        if (!empty($args['property_type'])) {
            $type_names = [
                'single-family' => 'Single Family Homes',
                'condo' => 'Condominiums',
                'townhouse' => 'Townhouses',
                'land' => 'Land for Sale',
                'multi-family' => 'Multi-Family Properties',
                'commercial' => 'Commercial Properties'
            ];
            return $type_names[$args['property_type']] ?? ucwords(str_replace('-', ' ', $args['property_type']));
        }

        if (!empty($args['city'])) {
            if (is_array($args['city'])) {
                return 'Properties in ' . implode(' & ', array_slice($args['city'], 0, 2));
            }
            return 'Properties in ' . $args['city'];
        }

        if ($args['min_price'] && $args['max_price']) {
            if ($args['min_price'] >= 1000000) {
                return 'Luxury Properties';
            } elseif ($args['max_price'] <= 300000) {
                return 'Affordable Homes';
            } else {
                return 'Properties $' . number_format($args['min_price'] / 1000) . 'K - $' . number_format($args['max_price'] / 1000) . 'K';
            }
        } elseif ($args['min_price'] >= 1000000) {
            return 'Luxury Properties';
        }

        if ($args['min_bedrooms'] >= 4) {
            return 'Large Family Homes';
        } elseif ($args['min_bedrooms'] >= 1 && $args['max_bedrooms'] <= 2) {
            return 'Starter Homes & Condos';
        }

        // Default titles based on context
        if ($args['orderby'] === 'date') {
            return 'Recently Updated';
        }

        return 'Featured Listings';
    }
}

if (!function_exists('hpt_generate_listing_section_subtitle')) {
    /**
     * Generate dynamic listing section subtitle based on filters
     *
     * @param array $args Universal loop arguments
     * @return string Generated subtitle
     */
    function hpt_generate_listing_section_subtitle($args) {
        $subtitle_parts = [];

        // Add location context
        if (!empty($args['city'])) {
            if (is_array($args['city'])) {
                $subtitle_parts[] = 'in ' . implode(', ', array_slice($args['city'], 0, 3));
            } else {
                $subtitle_parts[] = 'in ' . $args['city'];
            }
        } elseif (!empty($args['state'])) {
            $subtitle_parts[] = 'in ' . $args['state'];
        }

        // Add price context
        if ($args['min_price'] || $args['max_price']) {
            if ($args['min_price'] && $args['max_price']) {
                $subtitle_parts[] = 'from $' . number_format($args['min_price']) . ' to $' . number_format($args['max_price']);
            } elseif ($args['min_price']) {
                $subtitle_parts[] = 'starting at $' . number_format($args['min_price']);
            } else {
                $subtitle_parts[] = 'up to $' . number_format($args['max_price']);
            }
        }

        // Add bedroom context
        if ($args['min_bedrooms'] || $args['max_bedrooms']) {
            if ($args['min_bedrooms'] && $args['max_bedrooms']) {
                $subtitle_parts[] = $args['min_bedrooms'] . '-' . $args['max_bedrooms'] . ' bedrooms';
            } elseif ($args['min_bedrooms']) {
                $subtitle_parts[] = $args['min_bedrooms'] . '+ bedrooms';
            }
        }

        // Default subtitles based on change tracking filters
        if (!empty($args['recent_changes_only'])) {
            return 'Properties with recent price updates, status changes, and new features' . (!empty($subtitle_parts) ? ' ' . implode(' ', $subtitle_parts) : '');
        }

        if (!empty($args['new_listings_only'])) {
            return 'Fresh properties just added to the market' . (!empty($subtitle_parts) ? ' ' . implode(' ', $subtitle_parts) : '');
        }

        if (!empty($args['price_changes_only'])) {
            return 'Properties with recent price adjustments' . (!empty($subtitle_parts) ? ' ' . implode(' ', $subtitle_parts) : '');
        }

        // Default subtitles based on filters
        if ($args['featured_only']) {
            return 'Hand-picked properties showcasing the best of our listings' . (!empty($subtitle_parts) ? ' ' . implode(' ', $subtitle_parts) : '');
        }

        if ($args['open_house_only']) {
            return 'Tour these properties this weekend' . (!empty($subtitle_parts) ? ' ' . implode(' ', $subtitle_parts) : '');
        }

        if (!empty($args['property_status'])) {
            switch ($args['property_status']) {
                case 'active':
                    return 'Ready to view and purchase' . (!empty($subtitle_parts) ? ' ' . implode(' ', $subtitle_parts) : '');
                case 'pending':
                    return 'Properties currently under contract' . (!empty($subtitle_parts) ? ' ' . implode(' ', $subtitle_parts) : '');
                case 'sold':
                    return 'Recently closed transactions' . (!empty($subtitle_parts) ? ' ' . implode(' ', $subtitle_parts) : '');
            }
        }

        // Build default subtitle
        if (!empty($subtitle_parts)) {
            return 'Discover your perfect home ' . implode(' ', $subtitle_parts);
        }

        return 'Find your perfect property from our curated selection';
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
