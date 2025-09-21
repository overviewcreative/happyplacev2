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
                'background_color' => 'var(--hph-tertiary, #A1BA89)',
                'show_search' => true,
                'show_filters' => true
            ],
            'event' => [
                'title' => 'Upcoming Events',
                'subtitle' => 'Discover local events, activities, and happenings in your area',
                'background_image' => get_template_directory_uri() . '/assets/images/archive-event-bg.jpg',
                'background_color' => 'var(--hph-orange, #e88c4a)',
                'show_search' => true,
                'show_filters' => true
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