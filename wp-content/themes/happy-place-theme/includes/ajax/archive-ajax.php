<?php
/**
 * Archive Filtering AJAX Handlers
 * 
 * Handles archive page filtering, sorting, and pagination including:
 * - Multi-post type archive filtering
 * - Advanced filtering with meta queries
 * - Sorting and view mode changes
 * - AJAX pagination
 * - Dynamic archive layout generation
 * 
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle archive AJAX requests (filtering, sorting, pagination)
 */
if (!function_exists('hpt_handle_archive_ajax')) {
    add_action('wp_ajax_hpt_archive_ajax', 'hpt_handle_archive_ajax');
    add_action('wp_ajax_nopriv_hpt_archive_ajax', 'hpt_handle_archive_ajax');
    
    // Also handle the JavaScript action name
    add_action('wp_ajax_hph_load_listings', 'hpt_handle_archive_ajax');
    add_action('wp_ajax_nopriv_hph_load_listings', 'hpt_handle_archive_ajax');

    function hpt_handle_archive_ajax() {
        // Debug logging
        error_log('HPT AJAX: Function called');
        
        // Load bridge functions
        $bridge_dir = get_template_directory() . '/includes/bridge/';
        $bridge_files = [
            'listing-bridge.php',
            'agent-bridge.php',
            'city-bridge.php',
            'community-bridge.php',
            'gallery-bridge.php'
        ];
        
        foreach ($bridge_files as $file) {
            $path = $bridge_dir . $file;
            if (file_exists($path)) {
                require_once $path;
                error_log("HPT AJAX: Loaded bridge file - {$file}");
            }
        }
        
        // Load adapter functions
        $adapter_dir = get_template_directory() . '/includes/adapters/';
        $adapter_files = [
            'listing-card-adapter.php',
            'agent-card-adapter.php',
            'city-card-adapter.php',
            'community-card-adapter.php'
        ];
        
        foreach ($adapter_files as $file) {
            $path = $adapter_dir . $file;
            if (file_exists($path)) {
                require_once $path;
                error_log("HPT AJAX: Loaded adapter file - {$file}");
            }
        }
        
        // Check if bridge functions are available
        if (!function_exists('hpt_get_listing_price')) {
            error_log('HPT AJAX: Bridge function hpt_get_listing_price not available');
            wp_send_json_error('Bridge functions not loaded');
            return;
        }
        
        // Check if adapter functions are available
        if (!function_exists('get_listing_card_props')) {
            error_log('HPT AJAX: Adapter function get_listing_card_props not available');
            wp_send_json_error('Adapter functions not loaded');
            return;
        }
        
        error_log('HPT AJAX: All functions loaded successfully');
        
        // Verify nonce (support both nonce names)
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'hph_listings_nonce') && !wp_verify_nonce($nonce, 'archive_ajax_nonce')) {
            error_log('HPT AJAX: Nonce verification failed');
            wp_send_json_error('Security check failed');
            return;
        }
        
        error_log('HPT AJAX: Nonce verification passed');

        // Sanitize parameters - handle both 'type' (from hero form) and 'post_type' (from JavaScript)
        $post_type = sanitize_text_field($_POST['post_type'] ?? $_POST['type'] ?? 'listing');
        $action_type = sanitize_text_field($_POST['action_type'] ?? 'filter'); // filter, sort, paginate, view_change
        $view_mode = sanitize_text_field($_POST['view'] ?? 'grid');
        $sort = sanitize_text_field($_POST['sort'] ?? 'date_desc');
        $per_page = intval($_POST['per_page'] ?? $_POST['posts_per_page'] ?? 12);
        $paged = intval($_POST['paged'] ?? $_POST['page'] ?? 1);
        $search = sanitize_text_field($_POST['s'] ?? '');
        
        // Handle additional form fields from hero search
        $price_range = sanitize_text_field($_POST['price_range'] ?? '');
        $bedrooms = sanitize_text_field($_POST['bedrooms'] ?? '');
        $bathrooms = sanitize_text_field($_POST['bathrooms'] ?? '');

        // Get and sanitize filters - handle both nested and flat filter structures
        $filters = $_POST['filters'] ?? [];
        $sanitized_filters = [];
        if (is_array($filters)) {
            foreach ($filters as $key => $value) {
                $sanitized_filters[sanitize_key($key)] = is_array($value) ? 
                    array_map('sanitize_text_field', $value) : 
                    sanitize_text_field($value);
            }
        }
        
        // Also handle direct filter parameters from the archive page JavaScript
        if (isset($_POST['status']) && !isset($sanitized_filters['status'])) {
            $sanitized_filters['status'] = sanitize_text_field($_POST['status']);
        }
        if (isset($_POST['feature']) && !isset($sanitized_filters['feature'])) {
            $sanitized_filters['feature'] = sanitize_text_field($_POST['feature']);
        }
        if (isset($_POST['property_type']) && !isset($sanitized_filters['property_type'])) {
            $sanitized_filters['property_type'] = sanitize_text_field($_POST['property_type']);
        }
        
        // Handle hero form fields
        if (!empty($price_range) && !isset($sanitized_filters['price_range'])) {
            $sanitized_filters['price_range'] = $price_range;
            // Parse price range like "250000-500000"
            if (strpos($price_range, '-') !== false) {
                list($min_price, $max_price) = explode('-', $price_range);
                $sanitized_filters['min_price'] = trim($min_price);
                $sanitized_filters['max_price'] = trim($max_price);
            }
        }
        if (!empty($bedrooms) && !isset($sanitized_filters['bedrooms'])) {
            $sanitized_filters['bedrooms'] = $bedrooms;
        }
        if (!empty($bathrooms) && !isset($sanitized_filters['bathrooms'])) {
            $sanitized_filters['bathrooms'] = $bathrooms;
        }

        // Build query args based on post type using existing filter functions
        $query_args = [
            'post_type' => $post_type,
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'post_status' => 'publish',
            'meta_query' => ['relation' => 'AND']
        ];

        // Add search if provided
        if (!empty($search)) {
            $query_args['s'] = $search;
            
            // Add post type specific meta searches
            if (function_exists('hpt_get_post_type_meta_searches')) {
                $meta_searches = hpt_get_post_type_meta_searches($post_type, $search);
                if (!empty($meta_searches)) {
                    $query_args['meta_query'][] = [
                        'relation' => 'OR',
                        ...$meta_searches
                    ];
                }
            }
        }

        // Apply post type specific filters using existing functions
        if (function_exists('hpt_apply_post_type_filters')) {
            $query_args = hpt_apply_post_type_filters($post_type, $query_args, $sanitized_filters);
        }
        
        // Apply sorting using existing function
        if (function_exists('hpt_apply_search_sorting')) {
            $query_args = hpt_apply_search_sorting($post_type, $query_args, $sort);
        }
        
        // Execute query
        error_log('HPT AJAX: Executing WP_Query with args: ' . wp_json_encode($query_args));
        $archive_query = new WP_Query($query_args);
        error_log('HPT AJAX: Query executed. Found posts: ' . $archive_query->found_posts);

        // Generate HTML for results
        $html = '';
        if ($archive_query->have_posts()) {
            error_log('HPT AJAX: Starting HTML generation');
            ob_start();
            
            // Build card-based display similar to archive-layout
            $layout_classes = [];
            switch ($view_mode) {
                case 'grid':
                    $layout_classes = ['hph-grid', 'hph-grid-cols-1', 'md:hph-grid-cols-2', 'lg:hph-grid-cols-3', 'hph-gap-lg'];
                    break;
                case 'list':
                    $layout_classes = ['hph-space-y-lg'];
                    break;
                case 'masonry':
                    $layout_classes = ['hph-columns-1', 'md:hph-columns-2', 'lg:hph-columns-3', 'hph-gap-lg'];
                    break;
            }
            
            echo '<div class="' . esc_attr(implode(' ', $layout_classes)) . '">';
            
            // Determine adapter function
            $adapter_functions = [
                'listing' => 'get_listing_card_props',
                'agent' => 'get_agent_card_props', 
                'city' => 'get_city_card_props',
                'community' => 'get_community_card_props'
            ];
            
            $adapter_function = $adapter_functions[$post_type] ?? null;
            
            while ($archive_query->have_posts()) {
                $archive_query->the_post();
                $post_id = get_the_ID();
                
                if ($adapter_function && function_exists($adapter_function)) {
                    try {
                        $card_props = $adapter_function($post_id, [
                            'layout' => $view_mode === 'list' ? 'horizontal' : 'vertical',
                            'variant' => $view_mode === 'list' ? 'horizontal' : 'elevated',
                            'size' => 'md'
                        ]);
                        
                        error_log('HPT AJAX: Card props generated for post ' . $post_id);
                        
                        // Set the card props as 'args' query var for the template
                        set_query_var('args', $card_props);
                        
                        ob_start();
                        get_template_part('template-parts/base/card');
                        $card_html = ob_get_clean();
                        
                        if (empty(trim($card_html))) {
                            error_log('HPT AJAX: Card template produced no output, using fallback');
                            // Fallback to simple HTML if card template fails
                            echo '<div class="hph-listing-card hph-fallback" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px;">';
                            echo '<h3><a href="' . get_permalink($post_id) . '">' . get_the_title($post_id) . '</a></h3>';
                            if (function_exists('hpt_get_listing_price')) {
                                $price = hpt_get_listing_price($post_id);
                                if ($price) echo '<div class="price">Price: $' . number_format($price) . '</div>';
                            }
                            if (function_exists('hpt_get_listing_bedrooms')) {
                                $bedrooms = hpt_get_listing_bedrooms($post_id);
                                if ($bedrooms) echo '<div class="beds">Bedrooms: ' . $bedrooms . '</div>';
                            }
                            echo '<div class="excerpt">' . get_the_excerpt($post_id) . '</div>';
                            echo '</div>';
                        } else {
                            echo $card_html;
                        }
                    } catch (Exception $e) {
                        error_log('HPT AJAX: Error in card generation: ' . $e->getMessage());
                        echo '<div class="hph-error-card" style="border: 1px solid red; padding: 15px; margin-bottom: 15px;">';
                        echo '<h3><a href="' . get_permalink($post_id) . '">' . get_the_title($post_id) . '</a></h3>';
                        echo '<div style="color: red;">Error loading card data</div>';
                        echo '</div>';
                    }
                } else {
                    error_log('HPT AJAX: No adapter function available, using basic display');
                    // Fallback to basic post display
                    echo '<div class="hph-basic-card" style="border: 1px solid #ccc; padding: 15px; margin-bottom: 15px;">';
                    echo '<h3><a href="' . get_permalink($post_id) . '">' . get_the_title($post_id) . '</a></h3>';
                    echo '<div class="excerpt">' . get_the_excerpt($post_id) . '</div>';
                    echo '</div>';
                }
            }
            
            echo '</div>';
            
            // Generate pagination if needed
            if ($archive_query->max_num_pages > 1) {
                echo '<nav class="hph-archive-pagination hph-mt-xl" role="navigation">';
                
                $pagination_args = [
                    'total' => $archive_query->max_num_pages,
                    'current' => $paged,
                    'prev_text' => __('Previous', 'happy-place-theme'),
                    'next_text' => __('Next', 'happy-place-theme'),
                    'type' => 'array'
                ];
                
                $page_links = paginate_links($pagination_args);
                if ($page_links) {
                    echo '<ul class="hph-pagination hph-flex hph-flex-wrap hph-justify-center hph-gap-2">';
                    foreach ($page_links as $link) {
                        echo '<li>' . $link . '</li>';
                    }
                    echo '</ul>';
                }
                
                echo '</nav>';
            }
            
            $html = ob_get_clean();
            wp_reset_postdata();
        } else {
            // No results state
            ob_start();
            ?>
            <div class="hph-empty-state hph-text-center hph-py-xl">
                <div class="hph-empty-state-icon hph-text-6xl hph-text-gray-300 hph-mb-md">
                    <?php
                    $icons = [
                        'listing' => '🏠',
                        'agent' => '👥', 
                        'city' => '🏙️',
                        'community' => '🏘️'
                    ];
                    echo $icons[$post_type] ?? '📄';
                    ?>
                </div>
                <h3 class="hph-text-xl hph-font-semibold hph-mb-sm">
                    <?php printf(__('No %s found', 'happy-place-theme'), ucfirst($post_type . 's')); ?>
                </h3>
                <p class="hph-text-gray-600 hph-mb-lg">
                    <?php _e('Try adjusting your search criteria or browse all items.', 'happy-place-theme'); ?>
                </p>
                <button type="button" class="hph-button hph-button-outline hpt-clear-filters">
                    <?php _e('Clear All Filters', 'happy-place-theme'); ?>
                </button>
            </div>
            <?php
            $html = ob_get_clean();
        }

        // Build response with all necessary data
        $response_data = [
            'html' => $html,
            'grid_html' => $html, // Legacy field name for filter buttons
            'total' => $archive_query->found_posts,
            'count' => $archive_query->found_posts, // Legacy field name for filter buttons
            'max_pages' => $archive_query->max_num_pages,
            'current_page' => $paged,
            'has_more' => ($paged < $archive_query->max_num_pages),
            'post_type' => $post_type,
            'view_mode' => $view_mode,
            'sort' => $sort,
            'per_page' => $per_page,
            'filters' => $sanitized_filters,
            'search_query' => $search,
            'action_type' => $action_type,
            'has_results' => $archive_query->have_posts(),
            'results_text' => sprintf(
                _n('%d result found', '%d results found', $archive_query->found_posts, 'happy-place-theme'),
                $archive_query->found_posts
            )
        ];

        // Add filter summary for UI updates
        $active_filters = [];
        foreach ($sanitized_filters as $key => $value) {
            if (!empty($value)) {
                $active_filters[$key] = $value;
            }
        }
        $response_data['active_filters'] = $active_filters;
        $response_data['active_filter_count'] = count($active_filters);

        // TEST MODE: Add simple hard-coded HTML for debugging
        if (isset($_POST['test_mode']) || $sanitized_filters['feature'] === 'waterfront') {
            error_log('HPT AJAX: TEST MODE - Adding simple HTML for debugging');
            $test_html = '
                <div class="hph-test-card" style="border: 2px solid #007cba; padding: 20px; margin: 10px; background: #f0f8ff;">
                    <h3 style="color: #007cba; margin-bottom: 10px;">🏠 TEST WATERFRONT PROPERTY</h3>
                    <div style="font-size: 18px; font-weight: bold; color: #333; margin-bottom: 10px;">$750,000</div>
                    <p style="color: #666; margin-bottom: 10px;">📍 123 Waterfront Drive, Beautiful Bay, TX</p>
                    <div style="display: flex; gap: 15px; color: #555;">
                        <span>🛏️ 3 beds</span>
                        <span>🛁 2.5 baths</span>
                        <span>📐 2,150 sq ft</span>
                        <span>🌊 Waterfront</span>
                    </div>
                </div>
                <div class="hph-test-card" style="border: 2px solid #007cba; padding: 20px; margin: 10px; background: #f0f8ff;">
                    <h3 style="color: #007cba; margin-bottom: 10px;">🏡 ANOTHER TEST PROPERTY</h3>
                    <div style="font-size: 18px; font-weight: bold; color: #333; margin-bottom: 10px;">$525,000</div>
                    <p style="color: #666; margin-bottom: 10px;">📍 456 Lake View Lane, Scenic Waters, TX</p>
                    <div style="display: flex; gap: 15px; color: #555;">
                        <span>🛏️ 2 beds</span>
                        <span>🛁 2 baths</span>
                        <span>📐 1,800 sq ft</span>
                        <span>🌊 Waterfront</span>
                    </div>
                </div>';
            
            $response_data['html'] = $test_html;
            $response_data['grid_html'] = $test_html;
            $response_data['total'] = 2;
            $response_data['count'] = 2;
            $response_data['test_mode'] = true;
            error_log('HPT AJAX: TEST MODE - Sending test response');
        }

        error_log('HPT AJAX: About to send success response with ' . $response_data['total'] . ' results');
        wp_send_json_success($response_data);
    }
}

/**
 * Handle basic listing filtering (legacy)
 */
if (!function_exists('hph_filter_listings')) {
    add_action('wp_ajax_hph_filter_listings', 'hph_filter_listings');
    add_action('wp_ajax_nopriv_hph_filter_listings', 'hph_filter_listings');

    function hph_filter_listings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_ajax_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Get search parameters (same logic as archive-listing.php)
        $search = sanitize_text_field($_POST['s'] ?? '');
        $property_type = sanitize_text_field($_POST['property_type'] ?? '');
        $price_range = sanitize_text_field($_POST['price_range'] ?? '');
        $zip_code = sanitize_text_field($_POST['zip_code'] ?? '');
        $bedrooms = sanitize_text_field($_POST['bedrooms'] ?? '');
        $bathrooms = sanitize_text_field($_POST['bathrooms'] ?? '');
        $sort = sanitize_text_field($_POST['sort'] ?? '');
        $paged = intval($_POST['paged'] ?? 1);
        
        // Handle price range
        $min_price = 0;
        $max_price = 0;
        if (!empty($price_range) && strpos($price_range, '-') !== false) {
            list($min_price, $max_price) = explode('-', $price_range);
            $min_price = intval($min_price);
            $max_price = intval($max_price);
        }
        
        // Build query (same as archive-listing.php)
        $args = [
            'post_type' => 'listing',
            'post_status' => 'publish', 
            'posts_per_page' => 12,
            'paged' => $paged
        ];
        
        // Add search
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        // Initialize meta_query
        $meta_queries = [];
        
        // Add price range filter
        if ($min_price || $max_price) {
            $price_query = [
                'key' => 'listing_price',
                'type' => 'NUMERIC'
            ];
            
            if ($min_price && $max_price) {
                $price_query['value'] = [$min_price, $max_price];
                $price_query['compare'] = 'BETWEEN';
            } elseif ($min_price) {
                $price_query['value'] = $min_price;
                $price_query['compare'] = '>=';
            } elseif ($max_price) {
                $price_query['value'] = $max_price;
                $price_query['compare'] = '<=';
            }
            
            $meta_queries[] = $price_query;
        }
        
        // Add bedrooms filter
        if (!empty($bedrooms)) {
            $bedroom_value = intval($bedrooms);
            if ($bedrooms === '4+' || $bedroom_value >= 4) {
                $meta_queries[] = [
                    'key' => 'bedrooms',
                    'value' => 4,
                    'type' => 'NUMERIC',
                    'compare' => '>='
                ];
            } else {
                $meta_queries[] = [
                    'key' => 'bedrooms',
                    'value' => $bedroom_value,
                    'type' => 'NUMERIC',
                    'compare' => '>='
                ];
            }
        }
        
        // Add bathrooms filter
        if (!empty($bathrooms)) {
            $bathroom_value = intval($bathrooms);
            if ($bathrooms === '3+' || $bathroom_value >= 3) {
                $meta_queries[] = [
                    'key' => 'bathrooms_full',
                    'value' => 3,
                    'type' => 'NUMERIC',
                    'compare' => '>='
                ];
            } else {
                $meta_queries[] = [
                    'key' => 'bathrooms_full',
                    'value' => $bathroom_value,
                    'type' => 'NUMERIC',
                    'compare' => '>='
                ];
            }
        }
        
        // Add property type filter
        if (!empty($property_type)) {
            $meta_queries[] = [
                'key' => 'property_type',
                'value' => $property_type,
                'compare' => '='
            ];
        }
        
        // Add zip code filter
        if (!empty($zip_code)) {
            $meta_queries[] = [
                'key' => 'zip_code',
                'value' => $zip_code,
                'compare' => '='
            ];
        }
        
        // Add meta_query to args
        if (!empty($meta_queries)) {
            $args['meta_query'] = $meta_queries;
        }
        
        // Add sorting
        if (!empty($sort)) {
            switch ($sort) {
                case 'price_desc':
                    $args['meta_key'] = 'listing_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                case 'price_asc':
                    $args['meta_key'] = 'listing_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                    break;
                case 'date_asc':
                    $args['orderby'] = 'date';
                    $args['order'] = 'ASC';
                    break;
                default:
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
            }
        }
        
        $query = new WP_Query($args);
        
        // Generate HTML for results
        ob_start();
        $grid_html = '';
        $list_html = '';
        
        if ($query->have_posts()) {
            // Grid HTML
            ob_start();
            while ($query->have_posts()) {
                $query->the_post();
                get_template_part('template-parts/listing-card-enhanced', null, ['listing_id' => get_the_ID()]);
            }
            $grid_html = ob_get_clean();
            
            // List HTML
            $query->rewind_posts();
            ob_start();
            while ($query->have_posts()) {
                $query->the_post();
                get_template_part('template-parts/listing-card-list', null, ['listing_id' => get_the_ID()]);
            }
            $list_html = ob_get_clean();
        }
        
        // Generate count text
        $count_text = '';
        if ($query->found_posts > 0) {
            $count_text = '<strong>' . number_format($query->found_posts) . '</strong> ';
            $count_text .= 'propert' . ($query->found_posts != 1 ? 'ies' : 'y') . ' found';
        } else {
            $count_text = 'No properties found';
        }
        
        // Generate pagination
        $pagination_html = '';
        if ($query->max_num_pages > 1) {
            ob_start();
            ?>
            <div class="hph-pagination-section">
                <div class="hph-container">
                    <nav class="hph-pagination-nav" aria-label="Listings pagination">
                        <?php
                        $pagination_links = paginate_links([
                            'total' => $query->max_num_pages,
                            'current' => $paged,
                            'prev_text' => '<i class="fas fa-chevron-left"></i><span>Previous</span>',
                            'next_text' => '<span>Next</span><i class="fas fa-chevron-right"></i>',
                            'type' => 'array',
                            'before_page_number' => '<span class="page-number">',
                            'after_page_number' => '</span>'
                        ]);
                        
                        if (is_array($pagination_links)) {
                            echo '<div class="hph-pagination-container">';
                            foreach ($pagination_links as $link) {
                                echo $link;
                            }
                            echo '</div>';
                        }
                        ?>
                    </nav>
                </div>
            </div>
            <?php
            $pagination_html = ob_get_clean();
        }
        
        // Generate map data for map view
        $map_data = [];
        if ($query->have_posts()) {
            $query->rewind_posts();
            while ($query->have_posts()) {
                $query->the_post();
                $listing_id = get_the_ID();
                
                // Get coordinates
                $lat = get_field('latitude', $listing_id);
                $lng = get_field('longitude', $listing_id);
                
                if ($lat && $lng) {
                    $listing_data = [
                        'id' => $listing_id,
                        'title' => get_the_title($listing_id),
                        'latitude' => floatval($lat),
                        'longitude' => floatval($lng),
                        'permalink' => get_permalink($listing_id),
                        'status' => get_field('listing_status', $listing_id) ?: 'active',
                        'featured_image' => get_the_post_thumbnail_url($listing_id, 'medium'),
                        'price' => get_field('price', $listing_id),
                        'bedrooms' => get_field('bedrooms', $listing_id),
                        'bathrooms' => get_field('bathrooms_full', $listing_id),
                        'square_feet' => get_field('square_feet', $listing_id),
                        'street_address' => trim((get_field('street_number', $listing_id) ?: '') . ' ' . (get_field('street_name', $listing_id) ?: '') . ' ' . (get_field('street_type', $listing_id) ?: '')),
                        'city' => get_field('city', $listing_id),
                        'state' => get_field('state', $listing_id),
                        'zip_code' => get_field('zip_code', $listing_id)
                    ];
                    
                    $map_data[] = $listing_data;
                }
            }
        }
        
        wp_reset_postdata();
        
        // Return JSON response
        wp_send_json_success([
            'grid_html' => $grid_html,
            'list_html' => $list_html,
            'count_text' => $count_text,
            'pagination_html' => $pagination_html,
            'map_data' => $map_data,
            'zip_code' => $zip_code, // Include zip code for boundary visualization
            'found_posts' => $query->found_posts,
            'max_pages' => $query->max_num_pages,
            'current_page' => $paged
        ]);
    }
}

/**
 * Handle agent archive filtering AJAX request
 */
if (!function_exists('hph_handle_agent_filter_ajax')) {
    add_action('wp_ajax_hph_filter_agents', 'hph_handle_agent_filter_ajax');
    add_action('wp_ajax_nopriv_hph_filter_agents', 'hph_handle_agent_filter_ajax');

    function hph_handle_agent_filter_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_agent_archive_nonce')) {
            wp_send_json_error('Security check failed');
        }

        // Sanitize inputs
        $filters = array_map('sanitize_text_field', $_POST['filters'] ?? []);
        $sort = sanitize_text_field($_POST['sort'] ?? 'name_asc');
        $view_mode = sanitize_text_field($_POST['view'] ?? 'grid');
        $per_page = intval($_POST['per_page'] ?? 12);
        $paged = intval($_POST['paged'] ?? 1);

        // Build query arguments
        $args = [
            'post_type' => 'agent',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'meta_query' => ['relation' => 'AND']
        ];

        // Apply agent-specific filters using search system if available
        if (function_exists('hpt_apply_agent_filters')) {
            $args = hpt_apply_agent_filters($args, $filters);
        } else {
            // Basic filter fallbacks
            if (!empty($filters['status']) && $filters['status'] === 'featured') {
                $args['meta_query'][] = [
                    'key' => 'is_featured_agent',
                    'value' => '1',
                    'compare' => '='
                ];
            }
        }

        // Apply sorting
        switch ($sort) {
            case 'name_asc':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'name_desc':
                $args['orderby'] = 'title';
                $args['order'] = 'DESC';
                break;
            case 'featured':
                $args['orderby'] = ['meta_value_num' => 'DESC', 'title' => 'ASC'];
                $args['meta_key'] = 'is_featured_agent';
                break;
            case 'newest':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
        }

        // Execute query
        $agent_query = new WP_Query($args);

        // Generate HTML output
        $html = '';
        if ($agent_query->have_posts()) {
            ob_start();
            
            // Choose layout classes based on view mode
            $layout_classes = $view_mode === 'list' ? 
                ['hph-space-y-lg'] : 
                ['hph-grid', 'hph-grid-cols-1', 'md:hph-grid-cols-2', 'lg:hph-grid-cols-3', 'hph-gap-lg'];
            
            echo '<div class="' . esc_attr(implode(' ', $layout_classes)) . '">';
            
            while ($agent_query->have_posts()) {
                $agent_query->the_post();
                $agent_id = get_the_ID();
                
                // Use agent card adapter if available
                if (function_exists('get_agent_card_props')) {
                    $card_props = get_agent_card_props($agent_id, [
                        'layout' => $view_mode === 'list' ? 'horizontal' : 'vertical',
                        'variant' => 'elevated',
                        'show_contact' => true,
                        'show_stats' => true
                    ]);
                    
                    get_template_part('template-parts/base/card', null, $card_props);
                } else {
                    // Fallback to agent card component
                    hph_component('agent-card', ['agent_id' => $agent_id]);
                }
            }
            
            echo '</div>';
            
            // Add pagination
            if ($agent_query->max_num_pages > 1) {
                echo '<nav class="hph-agent-pagination hph-mt-xl">';
                echo paginate_links([
                    'total' => $agent_query->max_num_pages,
                    'current' => $paged,
                    'type' => 'list'
                ]);
                echo '</nav>';
            }
            
            $html = ob_get_clean();
            wp_reset_postdata();
        } else {
            $html = '<div class="hph-no-agents hph-text-center hph-py-xl">
                <p>' . __('No agents found matching your criteria.', 'happy-place-theme') . '</p>
                <button type="button" class="hph-button hph-button-outline hpt-clear-agent-filters">
                    ' . __('Clear Filters', 'happy-place-theme') . '
                </button>
            </div>';
        }

        // Send response
        wp_send_json_success([
            'html' => $html,
            'total' => $agent_query->found_posts,
            'max_pages' => $agent_query->max_num_pages,
            'current_page' => $paged,
            'filters' => $filters,
            'sort' => $sort,
            'view_mode' => $view_mode,
            'has_results' => $agent_query->have_posts()
        ]);
    }
}

/**
 * Handle city archive filtering (if needed)
 */
if (!function_exists('hph_handle_city_filter_ajax')) {
    add_action('wp_ajax_hph_filter_cities', 'hph_handle_city_filter_ajax');
    add_action('wp_ajax_nopriv_hph_filter_cities', 'hph_handle_city_filter_ajax');

    function hph_handle_city_filter_ajax() {
        // Use the main archive handler for cities
        $_POST['post_type'] = 'city';
        hpt_handle_archive_ajax();
    }
}

/**
 * Handle community archive filtering (if needed)
 */
if (!function_exists('hph_handle_community_filter_ajax')) {
    add_action('wp_ajax_hph_filter_communities', 'hph_handle_community_filter_ajax');
    add_action('wp_ajax_nopriv_hph_filter_communities', 'hph_handle_community_filter_ajax');

    function hph_handle_community_filter_ajax() {
        // Use the main archive handler for communities
        $_POST['post_type'] = 'community';
        hpt_handle_archive_ajax();
    }
}

/**
 * Utility function to get meta search queries for different post types
 */
if (!function_exists('hpt_get_post_type_meta_searches')) {
    function hpt_get_post_type_meta_searches($post_type, $search_query) {
        $meta_searches = [];
        
        switch ($post_type) {
            case 'listing':
                $meta_searches = [
                    ['key' => 'mls_number', 'value' => $search_query, 'compare' => 'LIKE'],
                    ['key' => 'street_number', 'value' => $search_query, 'compare' => 'LIKE'],
                    ['key' => 'street_name', 'value' => $search_query, 'compare' => 'LIKE'],
                    ['key' => 'city', 'value' => $search_query, 'compare' => 'LIKE'],
                    ['key' => 'state', 'value' => $search_query, 'compare' => 'LIKE'],
                    ['key' => 'zip_code', 'value' => $search_query, 'compare' => 'LIKE'],
                ];
                break;
            case 'agent':
                $meta_searches = [
                    ['key' => 'agent_phone', 'value' => $search_query, 'compare' => 'LIKE'],
                    ['key' => 'agent_email', 'value' => $search_query, 'compare' => 'LIKE'],
                    ['key' => 'agent_specialties', 'value' => $search_query, 'compare' => 'LIKE'],
                ];
                break;
            case 'city':
                $meta_searches = [
                    ['key' => 'city_state', 'value' => $search_query, 'compare' => 'LIKE'],
                    ['key' => 'city_description', 'value' => $search_query, 'compare' => 'LIKE'],
                ];
                break;
        }
        
        return $meta_searches;
    }
}

/**
 * Apply post type specific filters to query args
 */
if (!function_exists('hpt_apply_post_type_filters')) {
    function hpt_apply_post_type_filters($post_type, $query_args, $filters) {
        if (empty($filters)) {
            return $query_args;
        }
        
        switch ($post_type) {
            case 'listing':
                // Status filter
                if (!empty($filters['status']) && $filters['status'] !== 'all') {
                    if ($filters['status'] === 'new') {
                        $query_args['date_query'] = [
                            'after' => '30 days ago'
                        ];
                    } elseif ($filters['status'] === 'reduced') {
                        $query_args['meta_query'][] = [
                            'key' => 'price_reduced',
                            'value' => '1',
                            'compare' => '='
                        ];
                    } else {
                        $query_args['meta_query'][] = [
                            'key' => 'listing_status',
                            'value' => $filters['status'],
                            'compare' => '='
                        ];
                    }
                }
                
                // Feature filter
                if (!empty($filters['feature'])) {
                    switch ($filters['feature']) {
                        case 'waterfront':
                            $query_args['meta_query'][] = [
                                'key' => 'waterfront',
                                'value' => '1',
                                'compare' => '='
                            ];
                            break;
                        case 'pool':
                            $query_args['meta_query'][] = [
                                'key' => 'pool',
                                'value' => '1',
                                'compare' => '='
                            ];
                            break;
                        case 'garage':
                            $query_args['meta_query'][] = [
                                'key' => 'garage',
                                'value' => '1',
                                'compare' => '='
                            ];
                            break;
                    }
                }
                
                // Property type filter
                if (!empty($filters['property_type']) && $filters['property_type'] !== 'all') {
                    $query_args['meta_query'][] = [
                        'key' => 'property_type',
                        'value' => $filters['property_type'],
                        'compare' => '='
                    ];
                }
                
                // Price range filters
                if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
                    $price_query = ['key' => 'price', 'type' => 'NUMERIC'];
                    if (!empty($filters['min_price']) && !empty($filters['max_price'])) {
                        $price_query['value'] = [intval($filters['min_price']), intval($filters['max_price'])];
                        $price_query['compare'] = 'BETWEEN';
                    } elseif (!empty($filters['min_price'])) {
                        $price_query['value'] = intval($filters['min_price']);
                        $price_query['compare'] = '>=';
                    } else {
                        $price_query['value'] = intval($filters['max_price']);
                        $price_query['compare'] = '<=';
                    }
                    $query_args['meta_query'][] = $price_query;
                }
                
                // Bedroom filter
                if (!empty($filters['bedrooms'])) {
                    $query_args['meta_query'][] = [
                        'key' => 'bedrooms',
                        'value' => intval($filters['bedrooms']),
                        'compare' => '>=',
                        'type' => 'NUMERIC'
                    ];
                }
                
                // Bathroom filter
                if (!empty($filters['bathrooms'])) {
                    $query_args['meta_query'][] = [
                        'key' => 'bathrooms_full',
                        'value' => floatval($filters['bathrooms']),
                        'compare' => '>=',
                        'type' => 'NUMERIC'
                    ];
                }
                break;
                
            case 'agent':
                // Featured filter
                if (!empty($filters['featured']) && $filters['featured'] === '1') {
                    $query_args['meta_query'][] = [
                        'key' => 'is_featured_agent',
                        'value' => '1',
                        'compare' => '='
                    ];
                }
                break;
        }
        
        return $query_args;
    }
}

/**
 * Apply sorting to query args
 */
if (!function_exists('hpt_apply_search_sorting')) {
    function hpt_apply_search_sorting($post_type, $query_args, $sort) {
        switch ($post_type) {
            case 'listing':
                switch ($sort) {
                    case 'price_asc':
                        $query_args['meta_key'] = 'price';
                        $query_args['orderby'] = 'meta_value_num';
                        $query_args['order'] = 'ASC';
                        break;
                    case 'price_desc':
                        $query_args['meta_key'] = 'price';
                        $query_args['orderby'] = 'meta_value_num';
                        $query_args['order'] = 'DESC';
                        break;
                    case 'bedrooms_desc':
                        $query_args['meta_key'] = 'bedrooms';
                        $query_args['orderby'] = 'meta_value_num';
                        $query_args['order'] = 'DESC';
                        break;
                    case 'sqft_desc':
                        $query_args['meta_key'] = 'square_feet';
                        $query_args['orderby'] = 'meta_value_num';
                        $query_args['order'] = 'DESC';
                        break;
                    case 'date_asc':
                        $query_args['orderby'] = 'date';
                        $query_args['order'] = 'ASC';
                        break;
                    default: // date_desc
                        $query_args['orderby'] = 'date';
                        $query_args['order'] = 'DESC';
                }
                break;
                
            case 'agent':
                switch ($sort) {
                    case 'name_asc':
                        $query_args['orderby'] = 'title';
                        $query_args['order'] = 'ASC';
                        break;
                    case 'name_desc':
                        $query_args['orderby'] = 'title';
                        $query_args['order'] = 'DESC';
                        break;
                    case 'featured':
                        $query_args['orderby'] = ['meta_value_num' => 'DESC', 'title' => 'ASC'];
                        $query_args['meta_key'] = 'is_featured_agent';
                        break;
                    default:
                        $query_args['orderby'] = 'title';
                        $query_args['order'] = 'ASC';
                }
                break;
                
            default:
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'DESC';
        }
        
        return $query_args;
    }
}

/**
 * Handle search autocomplete AJAX request
 */
if (!function_exists('hph_handle_search_autocomplete')) {
    add_action('wp_ajax_hph_search_autocomplete', 'hph_handle_search_autocomplete');
    add_action('wp_ajax_nopriv_hph_search_autocomplete', 'hph_handle_search_autocomplete');
    
    // Also handle the action that the JavaScript is calling
    add_action('wp_ajax_hpt_search_autocomplete', 'hph_handle_search_autocomplete');
    add_action('wp_ajax_nopriv_hpt_search_autocomplete', 'hph_handle_search_autocomplete');
    
    function hph_handle_search_autocomplete() {
        // Get search query
        $query = sanitize_text_field($_GET['q'] ?? $_POST['q'] ?? '');
        
        if (strlen($query) < 2) {
            wp_send_json_success(['suggestions' => []]);
            return;
        }
        
        $suggestions = [];
        
        // Search listings
        $listing_args = [
            'post_type' => 'listing',
            'posts_per_page' => 5,
            'post_status' => 'publish',
            's' => $query,
            'meta_query' => [
                'relation' => 'OR',
                ['key' => 'city', 'value' => $query, 'compare' => 'LIKE'],
                ['key' => 'street_name', 'value' => $query, 'compare' => 'LIKE'],
                ['key' => 'zip_code', 'value' => $query, 'compare' => 'LIKE'],
                ['key' => 'mls_number', 'value' => $query, 'compare' => 'LIKE'],
            ]
        ];
        
        $listing_query = new WP_Query($listing_args);
        
        if ($listing_query->have_posts()) {
            while ($listing_query->have_posts()) {
                $listing_query->the_post();
                $listing_id = get_the_ID();
                
                $suggestions[] = [
                    'type' => 'listing',
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'price' => get_field('price', $listing_id),
                    'city' => get_field('city', $listing_id),
                    'image' => get_the_post_thumbnail_url($listing_id, 'thumbnail')
                ];
            }
            wp_reset_postdata();
        }
        
        // Search cities/locations
        $city_args = [
            'post_type' => 'city',
            'posts_per_page' => 3,
            'post_status' => 'publish',
            's' => $query
        ];
        
        $city_query = new WP_Query($city_args);
        
        if ($city_query->have_posts()) {
            while ($city_query->have_posts()) {
                $city_query->the_post();
                
                $suggestions[] = [
                    'type' => 'city',
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'description' => wp_trim_words(get_the_excerpt(), 10)
                ];
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success(['suggestions' => $suggestions]);
    }
}

/**
 * Handle load more listings
 */
if (!function_exists('hph_load_more_listings')) {
    add_action('wp_ajax_hph_load_more_listings', 'hph_load_more_listings');
    add_action('wp_ajax_nopriv_hph_load_more_listings', 'hph_load_more_listings');
    
    function hph_load_more_listings() {
        // Use the main archive handler but force post_type to listing
        $_POST['post_type'] = 'listing';
        $_POST['action_type'] = 'load_more';
        hpt_handle_archive_ajax();
    }
}

/**
 * Handle load more agents
 */
if (!function_exists('hph_load_more_agents')) {
    add_action('wp_ajax_hph_load_more_agents', 'hph_load_more_agents');
    add_action('wp_ajax_nopriv_hph_load_more_agents', 'hph_load_more_agents');
    
    function hph_load_more_agents() {
        // Use the agent filter handler
        hph_handle_agent_filter_ajax();
    }
}

/**
 * Handle toggle favorite (listings)
 */
if (!function_exists('hph_toggle_favorite')) {
    add_action('wp_ajax_hph_toggle_favorite', 'hph_toggle_favorite');
    add_action('wp_ajax_nopriv_hph_toggle_favorite', 'hph_toggle_favorite');
    
    function hph_toggle_favorite() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please log in to save favorites']);
        }
        
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'hph_archive_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        $action = sanitize_text_field($_POST['favorite_action'] ?? 'add');
        
        if (!$listing_id) {
            wp_send_json_error(['message' => 'Invalid listing ID']);
        }
        
        $user_id = get_current_user_id();
        $favorites = get_user_meta($user_id, 'favorite_listings', true) ?: [];
        
        if ($action === 'add') {
            if (!in_array($listing_id, $favorites)) {
                $favorites[] = $listing_id;
                $message = 'Added to favorites';
            } else {
                $message = 'Already in favorites';
            }
        } else {
            $favorites = array_diff($favorites, [$listing_id]);
            $message = 'Removed from favorites';
        }
        
        update_user_meta($user_id, 'favorite_listings', array_values($favorites));
        
        wp_send_json_success([
            'message' => $message,
            'action' => $action,
            'listing_id' => $listing_id,
            'favorites_count' => count($favorites)
        ]);
    }
}

/**
 * Handle toggle agent favorite
 */
if (!function_exists('hph_toggle_agent_favorite')) {
    add_action('wp_ajax_hph_toggle_agent_favorite', 'hph_toggle_agent_favorite');
    add_action('wp_ajax_nopriv_hph_toggle_agent_favorite', 'hph_toggle_agent_favorite');
    
    function hph_toggle_agent_favorite() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please log in to save favorite agents']);
        }
        
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'hph_archive_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $agent_id = intval($_POST['agent_id'] ?? 0);
        $action = sanitize_text_field($_POST['favorite_action'] ?? 'add');
        
        if (!$agent_id) {
            wp_send_json_error(['message' => 'Invalid agent ID']);
        }
        
        $user_id = get_current_user_id();
        $favorites = get_user_meta($user_id, 'favorite_agents', true) ?: [];
        
        if ($action === 'add') {
            if (!in_array($agent_id, $favorites)) {
                $favorites[] = $agent_id;
                $message = 'Added to favorite agents';
            } else {
                $message = 'Already in favorite agents';
            }
        } else {
            $favorites = array_diff($favorites, [$agent_id]);
            $message = 'Removed from favorite agents';
        }
        
        update_user_meta($user_id, 'favorite_agents', array_values($favorites));
        
        wp_send_json_success([
            'message' => $message,
            'action' => $action,
            'agent_id' => $agent_id,
            'favorites_count' => count($favorites)
        ]);
    }
}

/**
 * Handle bulk favorite listings
 */
if (!function_exists('hph_bulk_favorite')) {
    add_action('wp_ajax_hph_bulk_favorite', 'hph_bulk_favorite');
    add_action('wp_ajax_nopriv_hph_bulk_favorite', 'hph_bulk_favorite');
    
    function hph_bulk_favorite() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please log in to save favorites']);
        }
        
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'hph_archive_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $listing_ids = explode(',', $_POST['listing_ids'] ?? '');
        $listing_ids = array_map('intval', array_filter($listing_ids));
        
        if (empty($listing_ids)) {
            wp_send_json_error(['message' => 'No listings selected']);
        }
        
        $user_id = get_current_user_id();
        $existing_favorites = get_user_meta($user_id, 'favorite_listings', true) ?: [];
        $new_favorites = array_unique(array_merge($existing_favorites, $listing_ids));
        
        update_user_meta($user_id, 'favorite_listings', $new_favorites);
        
        wp_send_json_success([
            'message' => sprintf('Added %d listings to favorites', count($listing_ids)),
            'added_count' => count($listing_ids),
            'total_favorites' => count($new_favorites)
        ]);
    }
}

/**
 * Handle agent matching
 */
if (!function_exists('hph_match_agents')) {
    add_action('wp_ajax_hph_match_agents', 'hph_match_agents');
    add_action('wp_ajax_nopriv_hph_match_agents', 'hph_match_agents');
    
    function hph_match_agents() {
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'hph_archive_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $preferences = [
            'goal' => sanitize_text_field($_POST['goal'] ?? ''),
            'language' => sanitize_text_field($_POST['language'] ?? ''),
            'property_type' => sanitize_text_field($_POST['property_type'] ?? ''),
            'first_time' => sanitize_text_field($_POST['first_time'] ?? ''),
            'budget_range' => sanitize_text_field($_POST['budget_range'] ?? ''),
            'timeline' => sanitize_text_field($_POST['timeline'] ?? '')
        ];
        
        // Build agent query based on preferences
        $agent_args = [
            'post_type' => 'agent',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'meta_query' => ['relation' => 'AND']
        ];
        
        // Add language filter
        if (!empty($preferences['language'])) {
            $agent_args['meta_query'][] = [
                'key' => 'languages',
                'value' => $preferences['language'],
                'compare' => 'LIKE'
            ];
        }
        
        // Add specialty filter based on goal
        if (!empty($preferences['goal'])) {
            $specialties_map = [
                'buy' => 'buyer',
                'sell' => 'seller',
                'luxury' => 'luxury',
                'invest' => 'investment',
                'commercial' => 'commercial'
            ];
            
            if (isset($specialties_map[$preferences['goal']])) {
                $agent_args['meta_query'][] = [
                    'key' => 'specialties',
                    'value' => $specialties_map[$preferences['goal']],
                    'compare' => 'LIKE'
                ];
            }
        }
        
        $agents_query = new WP_Query($agent_args);
        $matched_agents = [];
        
        if ($agents_query->have_posts()) {
            while ($agents_query->have_posts()) {
                $agents_query->the_post();
                $agent_id = get_the_ID();
                
                $matched_agents[] = [
                    'id' => $agent_id,
                    'name' => get_the_title(),
                    'title' => get_field('agent_title', $agent_id),
                    'specialties' => get_field('specialties', $agent_id),
                    'languages' => get_field('languages', $agent_id),
                    'experience' => get_field('years_experience', $agent_id),
                    'phone' => get_field('agent_phone', $agent_id),
                    'email' => get_field('agent_email', $agent_id),
                    'photo' => get_the_post_thumbnail_url($agent_id, 'medium'),
                    'url' => get_permalink($agent_id)
                ];
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success([
            'agents' => $matched_agents,
            'preferences' => $preferences,
            'total' => count($matched_agents)
        ]);
    }
}

/**
 * Handle agent contact form
 */
if (!function_exists('hph_contact_agent')) {
    add_action('wp_ajax_hph_contact_agent', 'hph_contact_agent');
    add_action('wp_ajax_nopriv_hph_contact_agent', 'hph_contact_agent');
    
    function hph_contact_agent() {
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'hph_archive_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $agent_id = intval($_POST['agent_id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        
        if (!$agent_id || !$name || !$email || !$message) {
            wp_send_json_error(['message' => 'Please fill in all required fields']);
        }
        
        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Please enter a valid email address']);
        }
        
        $agent = get_post($agent_id);
        if (!$agent) {
            wp_send_json_error(['message' => 'Agent not found']);
        }
        
        $agent_email = get_field('agent_email', $agent_id);
        if (!$agent_email) {
            wp_send_json_error(['message' => 'Agent contact information not available']);
        }
        
        // Create email
        $subject = sprintf('New Contact from %s via Happy Place Website', $name);
        
        $email_message = "New contact form submission:\n\n";
        $email_message .= "From: {$name}\n";
        $email_message .= "Email: {$email}\n";
        if ($phone) $email_message .= "Phone: {$phone}\n";
        $email_message .= "\nMessage:\n{$message}\n";
        
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $email
        ];
        
        $sent = wp_mail($agent_email, $subject, $email_message, $headers);
        
        if ($sent) {
            // Log the contact for tracking
            $contact_data = [
                'agent_id' => $agent_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
                'date' => current_time('mysql'),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
            ];
            
            // Store in custom table or post meta
            add_post_meta($agent_id, '_agent_contact', $contact_data);
            
            wp_send_json_success([
                'message' => 'Your message has been sent successfully! The agent will contact you soon.'
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Failed to send message. Please try again or contact the agent directly.'
            ]);
        }
    }
}

/**
 * Handle save search
 */
if (!function_exists('hph_save_search')) {
    add_action('wp_ajax_hph_save_search', 'hph_save_search');
    add_action('wp_ajax_nopriv_hph_save_search', 'hph_save_search');
    
    function hph_save_search() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please log in to save searches']);
        }
        
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'hph_archive_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $search_name = sanitize_text_field($_POST['search_name'] ?? '');
        $search_params = $_POST['search_params'] ?? '';
        
        if (!$search_name) {
            wp_send_json_error(['message' => 'Please enter a name for this search']);
        }
        
        $search_params = json_decode($search_params, true);
        if (!$search_params) {
            wp_send_json_error(['message' => 'Invalid search parameters']);
        }
        
        $user_id = get_current_user_id();
        $saved_searches = get_user_meta($user_id, 'saved_searches', true) ?: [];
        
        $saved_searches[] = [
            'name' => $search_name,
            'params' => $search_params,
            'date_saved' => current_time('timestamp'),
            'post_type' => $search_params['post_type'] ?? 'listing'
        ];
        
        update_user_meta($user_id, 'saved_searches', $saved_searches);
        
        wp_send_json_success([
            'message' => 'Search saved successfully!',
            'search_name' => $search_name,
            'total_searches' => count($saved_searches)
        ]);
    }
}
