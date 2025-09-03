<?php
/**
 * Universal Search AJAX Handlers
 * 
 * Handles universal search across all post types including:
 * - Multi-post type search with mixed results
 * - Single post type filtered search  
 * - Search autocomplete and suggestions
 * - Advanced filtering and sorting
 * 
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle universal search AJAX request
 * Supports both mixed results and single post type filtering
 */
if (!function_exists('hpt_handle_universal_search')) {
    add_action('wp_ajax_hpt_universal_search', 'hpt_handle_universal_search');
    add_action('wp_ajax_nopriv_hpt_universal_search', 'hpt_handle_universal_search');

    function hpt_handle_universal_search() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'search_form_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Sanitize input parameters
        $search_query = sanitize_text_field($_POST['s'] ?? '');
        $post_type = sanitize_text_field($_POST['type'] ?? 'all');
        $view_mode = sanitize_text_field($_POST['view'] ?? 'grid');
        $sort = sanitize_text_field($_POST['sort'] ?? 'relevance');
        $page = max(1, intval($_POST['paged'] ?? 1));
        $per_page = min(48, max(1, intval($_POST['per_page'] ?? 12)));
        
        // Get filters array and sanitize
        $filters = $_POST['filters'] ?? [];
        $sanitized_filters = [];
        if (is_array($filters)) {
            foreach ($filters as $key => $value) {
                $sanitized_filters[sanitize_key($key)] = is_array($value) ? 
                    array_map('sanitize_text_field', $value) : 
                    sanitize_text_field($value);
            }
        }
        
        $response_data = [
            'results' => [],
            'total' => 0,
            'max_pages' => 1,
            'current_page' => $page,
            'post_type' => $post_type,
            'view_mode' => $view_mode,
            'search_query' => $search_query
        ];
        
        try {
            if ($post_type === 'all') {
                // Mixed post type search - search across all types
                $response_data['results'] = hpt_search_all_post_types($search_query, $sanitized_filters, $per_page, $sort);
                $response_data['total'] = array_sum(array_map('count', $response_data['results']));
                $response_data['is_mixed'] = true;
            } else {
                // Single post type search
                $results = hpt_search_single_post_type($post_type, $search_query, $sanitized_filters, $page, $per_page, $sort);
                $response_data['results'] = $results['posts'];
                $response_data['total'] = $results['total'];
                $response_data['max_pages'] = $results['max_pages'];
                $response_data['is_mixed'] = false;
            }
            
            // Generate HTML for results
            ob_start();
            
            // Load search results component with data
            hph_component('search-results', [
                'results' => $response_data['results'],
                'post_type' => $post_type,
                'view_mode' => $view_mode,
                'total_results' => $response_data['total'],
                'current_page' => $page,
                'max_pages' => $response_data['max_pages'],
                'search_query' => $search_query
            ]);
            
            $response_data['html'] = ob_get_clean();
            
            wp_send_json_success($response_data);
            
        } catch (Exception $e) {
            error_log('Universal search error: ' . $e->getMessage());
            wp_send_json_error('Search failed. Please try again.');
        }
    }
}

/**
 * Search across all post types and return grouped results
 */
if (!function_exists('hpt_search_all_post_types')) {
    function hpt_search_all_post_types($search_query, $filters = [], $limit = 12, $sort = 'relevance') {
        $results = [
            'listing' => [],
            'agent' => [],
            'city' => [],
            'community' => []
        ];
        
        // Limit per post type to maintain performance
        $per_type_limit = max(1, floor($limit / 4));
        $remaining_limit = $limit;
        
        foreach (['listing', 'agent', 'city', 'community'] as $post_type) {
            if ($remaining_limit <= 0) break;
            
            $type_limit = min($per_type_limit, $remaining_limit);
            $type_results = hpt_search_single_post_type(
                $post_type, 
                $search_query, 
                $filters, 
                1, // Always page 1 for mixed results
                $type_limit, 
                $sort
            );
            
            $results[$post_type] = $type_results['posts'];
            $remaining_limit -= count($type_results['posts']);
        }
        
        return $results;
    }
}

/**
 * Search within a single post type with pagination
 */
if (!function_exists('hpt_search_single_post_type')) {
    function hpt_search_single_post_type($post_type, $search_query, $filters = [], $page = 1, $per_page = 12, $sort = 'relevance') {
        // Base query arguments
        $query_args = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            's' => $search_query,
            'meta_query' => [],
            'tax_query' => []
        ];
        
        // Add meta field searches
        if (!empty($search_query)) {
            $query_args['meta_query'][] = hpt_get_post_type_meta_searches($post_type, $search_query);
        }
        
        // Apply post type specific filters
        $query_args = hpt_apply_post_type_filters($post_type, $query_args, $filters);
        
        // Apply sorting
        $query_args = hpt_apply_search_sorting($post_type, $query_args, $sort);
        
        // Execute query
        $query = new WP_Query($query_args);
        
        return [
            'posts' => $query->posts,
            'total' => $query->found_posts,
            'max_pages' => $query->max_num_pages,
            'current_page' => $page
        ];
    }
}

/**
 * Get meta field search conditions for post type
 */
if (!function_exists('hpt_get_post_type_meta_searches')) {
    function hpt_get_post_type_meta_searches($post_type, $search_query) {
        $meta_conditions = ['relation' => 'OR'];
        
        switch ($post_type) {
            case 'listing':
                $meta_conditions[] = ['key' => 'property_address', 'value' => $search_query, 'compare' => 'LIKE'];
                $meta_conditions[] = ['key' => 'property_city', 'value' => $search_query, 'compare' => 'LIKE'];
                $meta_conditions[] = ['key' => 'property_state', 'value' => $search_query, 'compare' => 'LIKE'];
                $meta_conditions[] = ['key' => 'property_zip', 'value' => $search_query, 'compare' => 'LIKE'];
                $meta_conditions[] = ['key' => 'mls_number', 'value' => $search_query, 'compare' => 'LIKE'];
                break;
                
            case 'agent':
                $meta_conditions[] = ['key' => 'agent_email', 'value' => $search_query, 'compare' => 'LIKE'];
                $meta_conditions[] = ['key' => 'agent_phone', 'value' => $search_query, 'compare' => 'LIKE'];
                $meta_conditions[] = ['key' => 'agent_specialties', 'value' => $search_query, 'compare' => 'LIKE'];
                break;
                
            case 'city':
                $meta_conditions[] = ['key' => 'city_state', 'value' => $search_query, 'compare' => 'LIKE'];
                $meta_conditions[] = ['key' => 'city_county', 'value' => $search_query, 'compare' => 'LIKE'];
                break;
                
            case 'community':
                $meta_conditions[] = ['key' => 'community_city', 'value' => $search_query, 'compare' => 'LIKE'];
                $meta_conditions[] = ['key' => 'community_builder', 'value' => $search_query, 'compare' => 'LIKE'];
                break;
        }
        
        return $meta_conditions;
    }
}

/**
 * Apply filters based on post type
 */
if (!function_exists('hpt_apply_post_type_filters')) {
    function hpt_apply_post_type_filters($post_type, $query_args, $filters) {
        switch ($post_type) {
            case 'listing':
                return hpt_apply_listing_filters($query_args, $filters);
            case 'agent':
                return hpt_apply_agent_filters($query_args, $filters);
            case 'city':
                return hpt_apply_city_filters($query_args, $filters);
            case 'community':
                return hpt_apply_community_filters($query_args, $filters);
            default:
                return $query_args;
        }
    }
}

/**
 * Apply listing-specific filters
 */
if (!function_exists('hpt_apply_listing_filters')) {
    function hpt_apply_listing_filters($query_args, $filters) {
        // Price range
        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $price_query = ['key' => 'listing_price', 'type' => 'NUMERIC'];
            
            if (!empty($filters['min_price'])) {
                $price_query['compare'] = !empty($filters['max_price']) ? 'BETWEEN' : '>=';
                $price_query['value'] = !empty($filters['max_price']) ? 
                    [$filters['min_price'], $filters['max_price']] : 
                    $filters['min_price'];
            } elseif (!empty($filters['max_price'])) {
                $price_query['compare'] = '<=';
                $price_query['value'] = $filters['max_price'];
            }
            
            $query_args['meta_query'][] = $price_query;
        }
        
        // Bedrooms
        if (!empty($filters['bedrooms'])) {
            $query_args['meta_query'][] = [
                'key' => 'bedrooms',
                'value' => intval($filters['bedrooms']),
                'compare' => '>='
            ];
        }
        
        // Bathrooms  
        if (!empty($filters['bathrooms'])) {
            $query_args['meta_query'][] = [
                'key' => 'bathrooms_total', 
                'value' => floatval($filters['bathrooms']),
                'compare' => '>=',
                'type' => 'DECIMAL'
            ];
        }
        
        // Property type
        if (!empty($filters['property_type'])) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => is_array($filters['property_type']) ? $filters['property_type'] : [$filters['property_type']]
            ];
        }
        
        // Property status
        if (!empty($filters['property_status'])) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'property_status', 
                'field' => 'slug',
                'terms' => is_array($filters['property_status']) ? $filters['property_status'] : [$filters['property_status']]
            ];
        }
        
        // Features
        if (!empty($filters['features']) && is_array($filters['features'])) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'property_features',
                'field' => 'slug', 
                'terms' => $filters['features'],
                'operator' => 'AND'
            ];
        }
        
        // Location filters
        if (!empty($filters['city'])) {
            $query_args['meta_query'][] = [
                'key' => 'property_city',
                'value' => $filters['city'],
                'compare' => 'LIKE'
            ];
        }
        
        if (!empty($filters['zip'])) {
            $query_args['meta_query'][] = [
                'key' => 'property_zip',
                'value' => $filters['zip'],
                'compare' => '='
            ];
        }
        
        return $query_args;
    }
}

/**
 * Apply agent-specific filters
 */
if (!function_exists('hpt_apply_agent_filters')) {
    function hpt_apply_agent_filters($query_args, $filters) {
        // Agent specialties
        if (!empty($filters['specialties']) && is_array($filters['specialties'])) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'agent_specialty',
                'field' => 'slug',
                'terms' => $filters['specialties'],
                'operator' => 'IN'
            ];
        }
        
        // Experience level
        if (!empty($filters['experience'])) {
            $query_args['meta_query'][] = [
                'key' => 'years_experience',
                'value' => intval($filters['experience']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            ];
        }
        
        // Agent status (active/featured)
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'featured') {
                $query_args['meta_query'][] = [
                    'key' => 'is_featured_agent',
                    'value' => '1',
                    'compare' => '='
                ];
            } elseif ($filters['status'] === 'active') {
                $query_args['meta_query'][] = [
                    'key' => 'agent_status',
                    'value' => 'active',
                    'compare' => '='
                ];
            }
        }
        
        // Office/Team
        if (!empty($filters['office'])) {
            $query_args['meta_query'][] = [
                'key' => 'agent_office',
                'value' => $filters['office'],
                'compare' => 'LIKE'
            ];
        }
        
        return $query_args;
    }
}

/**
 * Apply city-specific filters
 */
if (!function_exists('hpt_apply_city_filters')) {
    function hpt_apply_city_filters($query_args, $filters) {
        // State filter
        if (!empty($filters['state'])) {
            $query_args['meta_query'][] = [
                'key' => 'city_state',
                'value' => $filters['state'],
                'compare' => '='
            ];
        }
        
        // County filter
        if (!empty($filters['county'])) {
            $query_args['meta_query'][] = [
                'key' => 'city_county', 
                'value' => $filters['county'],
                'compare' => 'LIKE'
            ];
        }
        
        // Population range
        if (!empty($filters['min_population']) || !empty($filters['max_population'])) {
            $pop_query = ['key' => 'city_population', 'type' => 'NUMERIC'];
            
            if (!empty($filters['min_population']) && !empty($filters['max_population'])) {
                $pop_query['compare'] = 'BETWEEN';
                $pop_query['value'] = [$filters['min_population'], $filters['max_population']];
            } elseif (!empty($filters['min_population'])) {
                $pop_query['compare'] = '>=';
                $pop_query['value'] = $filters['min_population'];
            } elseif (!empty($filters['max_population'])) {
                $pop_query['compare'] = '<=';
                $pop_query['value'] = $filters['max_population'];
            }
            
            $query_args['meta_query'][] = $pop_query;
        }
        
        return $query_args;
    }
}

/**
 * Apply community-specific filters
 */
if (!function_exists('hpt_apply_community_filters')) {
    function hpt_apply_community_filters($query_args, $filters) {
        // Community type
        if (!empty($filters['community_type'])) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'community_type',
                'field' => 'slug',
                'terms' => is_array($filters['community_type']) ? $filters['community_type'] : [$filters['community_type']]
            ];
        }
        
        // Builder
        if (!empty($filters['builder'])) {
            $query_args['meta_query'][] = [
                'key' => 'community_builder',
                'value' => $filters['builder'],
                'compare' => 'LIKE'
            ];
        }
        
        // City
        if (!empty($filters['city'])) {
            $query_args['meta_query'][] = [
                'key' => 'community_city',
                'value' => $filters['city'],
                'compare' => 'LIKE'
            ];
        }
        
        // Price range for community
        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $price_query = ['key' => 'starting_price', 'type' => 'NUMERIC'];
            
            if (!empty($filters['min_price']) && !empty($filters['max_price'])) {
                $price_query['compare'] = 'BETWEEN';
                $price_query['value'] = [$filters['min_price'], $filters['max_price']];
            } elseif (!empty($filters['min_price'])) {
                $price_query['compare'] = '>=';
                $price_query['value'] = $filters['min_price'];
            } elseif (!empty($filters['max_price'])) {
                $price_query['compare'] = '<=';
                $price_query['value'] = $filters['max_price'];
            }
            
            $query_args['meta_query'][] = $price_query;
        }
        
        return $query_args;
    }
}

/**
 * Apply sorting based on post type and sort parameter
 */
if (!function_exists('hpt_apply_search_sorting')) {
    function hpt_apply_search_sorting($post_type, $query_args, $sort) {
        switch ($sort) {
            case 'relevance':
                // WordPress default relevance sorting
                break;
                
            case 'date_desc':
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'DESC';
                break;
                
            case 'date_asc':
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'ASC';
                break;
                
            case 'title_asc':
                $query_args['orderby'] = 'title';
                $query_args['order'] = 'ASC';
                break;
                
            case 'title_desc':
                $query_args['orderby'] = 'title';
                $query_args['order'] = 'DESC';
                break;
                
            case 'price_asc':
                if (in_array($post_type, ['listing', 'community'])) {
                    $query_args['orderby'] = 'meta_value_num';
                    $query_args['meta_key'] = $post_type === 'listing' ? 'listing_price' : 'starting_price';
                    $query_args['order'] = 'ASC';
                }
                break;
                
            case 'price_desc':
                if (in_array($post_type, ['listing', 'community'])) {
                    $query_args['orderby'] = 'meta_value_num';
                    $query_args['meta_key'] = $post_type === 'listing' ? 'listing_price' : 'starting_price';
                    $query_args['order'] = 'DESC';
                }
                break;
                
            case 'featured':
                if ($post_type === 'listing') {
                    $query_args['orderby'] = ['meta_value_num' => 'DESC', 'date' => 'DESC'];
                    $query_args['meta_key'] = 'is_featured';
                } elseif ($post_type === 'agent') {
                    $query_args['orderby'] = ['meta_value_num' => 'DESC', 'date' => 'DESC'];
                    $query_args['meta_key'] = 'is_featured_agent';
                }
                break;
                
            case 'random':
                $query_args['orderby'] = 'rand';
                break;
                
            default:
                // Default to date descending for unknown sort types
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'DESC';
        }
        
        return $query_args;
    }
}

/**
 * Handle search autocomplete AJAX request
 */
if (!function_exists('hpt_handle_search_autocomplete')) {
    add_action('wp_ajax_hpt_search_autocomplete', 'hpt_handle_search_autocomplete');
    add_action('wp_ajax_nopriv_hpt_search_autocomplete', 'hpt_handle_search_autocomplete');

    function hpt_handle_search_autocomplete() {
        // Basic security check
        if (!isset($_POST['q']) || empty(trim($_POST['q']))) {
            wp_send_json_error('No search query provided');
        }
        
        $query = sanitize_text_field(trim($_POST['q']));
        $post_types = !empty($_POST['types']) ? array_map('sanitize_text_field', $_POST['types']) : ['listing', 'agent', 'city', 'community'];
        $max_results = min(20, max(5, intval($_POST['limit'] ?? 10)));
        
        $suggestions = hpt_get_autocomplete_suggestions($query, $post_types, $max_results);
        
        wp_send_json_success([
            'suggestions' => $suggestions,
            'query' => $query,
            'total' => count($suggestions)
        ]);
    }
}

/**
 * Get autocomplete suggestions across post types
 */
if (!function_exists('hpt_get_autocomplete_suggestions')) {
    function hpt_get_autocomplete_suggestions($query, $post_types, $max_results) {
        $suggestions = [];
        $results_per_type = max(1, floor($max_results / count($post_types)));
        
        foreach ($post_types as $post_type) {
            $type_suggestions = hpt_get_post_type_suggestions($post_type, $query, $results_per_type);
            $suggestions = array_merge($suggestions, $type_suggestions);
            
            if (count($suggestions) >= $max_results) {
                break;
            }
        }
        
        return array_slice($suggestions, 0, $max_results);
    }
}

/**
 * Get suggestions for specific post type
 */
if (!function_exists('hpt_get_post_type_suggestions')) {
    function hpt_get_post_type_suggestions($post_type, $query, $limit) {
        $args = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            's' => $query,
            'fields' => 'ids'
        ];
        
        $posts = get_posts($args);
        $suggestions = [];
        
        foreach ($posts as $post_id) {
            $post = get_post($post_id);
            $suggestions[] = [
                'id' => $post_id,
                'title' => get_the_title($post_id),
                'subtitle' => hpt_get_post_subtitle($post_type, $post_id),
                'url' => get_permalink($post_id),
                'type' => $post_type,
                'type_label' => hpt_get_post_type_label($post_type)
            ];
        }
        
        return $suggestions;
    }
}

/**
 * Get subtitle for autocomplete suggestion
 */
if (!function_exists('hpt_get_post_subtitle')) {
    function hpt_get_post_subtitle($post_type, $post_id) {
        switch ($post_type) {
            case 'listing':
                $address = get_post_meta($post_id, 'property_address', true);
                $price = hpt_get_listing_price_formatted($post_id);
                return ($address && $price) ? "$address • $price" : ($address ?: $price);
                
            case 'agent':
                $title = get_post_meta($post_id, 'agent_title', true);
                $phone = get_post_meta($post_id, 'agent_phone', true);
                return ($title && $phone) ? "$title • $phone" : ($title ?: $phone);
                
            case 'city':
                $state = get_post_meta($post_id, 'city_state', true);
                $population = get_post_meta($post_id, 'city_population', true);
                if ($state && $population) {
                    return "$state • " . number_format($population) . " residents";
                }
                return $state ?: ($population ? number_format($population) . " residents" : '');
                
            case 'community':
                $city = get_post_meta($post_id, 'community_city', true);
                $builder = get_post_meta($post_id, 'community_builder', true);
                return ($city && $builder) ? "$city • by $builder" : ($city ?: $builder);
                
            default:
                return '';
        }
    }
}

/**
 * Get human readable label for post type
 */
if (!function_exists('hpt_get_post_type_label')) {
    function hpt_get_post_type_label($post_type) {
        $labels = [
            'listing' => 'Property',
            'agent' => 'Agent',
            'city' => 'City',
            'community' => 'Community'
        ];
        
        return $labels[$post_type] ?? ucfirst($post_type);
    }
}