<?php
/**
 * Search Service
 *
 * Handles universal search functionality across all post types.
 * Migrated from theme search-ajax.php for proper separation of concerns.
 *
 * @package HappyPlace\Services
 * @since 4.1.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;
use WP_Query;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Search Service Class
 *
 * Provides universal search functionality including:
 * - Multi-post type search with mixed results
 * - Single post type filtered search
 * - Search autocomplete and suggestions
 * - Advanced filtering and sorting
 */
class SearchService extends Service {

    /**
     * Supported post types for search
     */
    private array $searchable_post_types = [
        'listing',
        'agent',
        'local_place',
        'local_event',
        'city',
        'community',
        'open_house'
    ];

    /**
     * Initialize the service
     */
    public function init(): void {
        $this->register_ajax_handlers();
    }

    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        // Universal search
        add_action('wp_ajax_hpt_universal_search', [$this, 'handle_universal_search']);
        add_action('wp_ajax_nopriv_hpt_universal_search', [$this, 'handle_universal_search']);

        // Search autocomplete
        add_action('wp_ajax_hpt_search_autocomplete', [$this, 'handle_search_autocomplete']);
        add_action('wp_ajax_nopriv_hpt_search_autocomplete', [$this, 'handle_search_autocomplete']);

        // Additional search endpoints
        add_action('wp_ajax_hpt_advanced_search', [$this, 'handle_advanced_search']);
        add_action('wp_ajax_nopriv_hpt_advanced_search', [$this, 'handle_advanced_search']);
    }

    /**
     * Handle universal search AJAX request
     * Supports both mixed results and single post type filtering
     */
    public function handle_universal_search(): void {
        try {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'search_form_nonce')) {
                wp_send_json_error('Security check failed', 403);
                return;
            }

            // Sanitize input parameters
            $search_query = sanitize_text_field($_POST['s'] ?? '');
            $post_type = sanitize_text_field($_POST['type'] ?? 'all');
            $view_mode = sanitize_text_field($_POST['view'] ?? 'grid');
            $sort = sanitize_text_field($_POST['sort'] ?? 'relevance');
            $page = max(1, intval($_POST['paged'] ?? 1));
            $per_page = min(48, max(1, intval($_POST['per_page'] ?? 12)));

            // Get filters and sanitize
            $filters = $_POST['filters'] ?? [];
            $sanitized_filters = $this->sanitize_filters($filters);

            // Validate required parameters
            if (empty($search_query) && empty($sanitized_filters)) {
                wp_send_json_error('Search query or filters required', 400);
                return;
            }

            // Perform search
            $search_results = $this->perform_search([
                'query' => $search_query,
                'post_type' => $post_type,
                'filters' => $sanitized_filters,
                'sort' => $sort,
                'page' => $page,
                'per_page' => $per_page
            ]);

            // Format results for response
            $response = $this->format_search_response($search_results, $view_mode);

            wp_send_json_success($response);

        } catch (Exception $e) {
            error_log('Search error: ' . $e->getMessage());
            wp_send_json_error('Search failed', 500);
        }
    }

    /**
     * Handle search autocomplete AJAX request
     */
    public function handle_search_autocomplete(): void {
        try {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'search_form_nonce')) {
                wp_send_json_error('Security check failed', 403);
                return;
            }

            $query = sanitize_text_field($_POST['query'] ?? '');
            $type = sanitize_text_field($_POST['type'] ?? 'all');
            $limit = min(10, max(1, intval($_POST['limit'] ?? 5)));

            if (strlen($query) < 2) {
                wp_send_json_success(['suggestions' => []]);
                return;
            }

            $suggestions = $this->get_search_suggestions($query, $type, $limit);

            wp_send_json_success(['suggestions' => $suggestions]);

        } catch (Exception $e) {
            error_log('Search autocomplete error: ' . $e->getMessage());
            wp_send_json_error('Autocomplete failed', 500);
        }
    }

    /**
     * Sanitize filters array
     */
    private function sanitize_filters(array $filters): array {
        $sanitized = [];

        if (is_array($filters)) {
            foreach ($filters as $key => $value) {
                $sanitized_key = sanitize_key($key);
                if (is_array($value)) {
                    $sanitized[$sanitized_key] = array_map('sanitize_text_field', $value);
                } else {
                    $sanitized[$sanitized_key] = sanitize_text_field($value);
                }
            }
        }

        return $sanitized;
    }

    /**
     * Perform the actual search
     */
    private function perform_search(array $args): array {
        $search_query = $args['query'] ?? '';
        $post_type = $args['post_type'] ?? 'all';
        $filters = $args['filters'] ?? [];
        $sort = $args['sort'] ?? 'relevance';
        $page = $args['page'] ?? 1;
        $per_page = $args['per_page'] ?? 12;

        // Determine post types to search
        if ($post_type === 'all') {
            $post_types = $this->searchable_post_types;
        } else {
            $post_types = in_array($post_type, $this->searchable_post_types) ? [$post_type] : ['listing'];
        }

        // Build WP_Query args
        $query_args = [
            'post_type' => $post_types,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => [],
            'tax_query' => []
        ];

        // Add search query if provided
        if (!empty($search_query)) {
            $query_args['s'] = $search_query;
        }

        // Apply filters
        $query_args = $this->apply_search_filters($query_args, $filters);

        // Apply sorting
        $query_args = $this->apply_search_sorting($query_args, $sort);

        // Perform query
        $wp_query = new WP_Query($query_args);

        return [
            'posts' => $wp_query->posts,
            'total' => $wp_query->found_posts,
            'max_pages' => $wp_query->max_num_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ];
    }

    /**
     * Apply search filters to query args
     */
    private function apply_search_filters(array $query_args, array $filters): array {
        // Price range filter
        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $price_query = ['key' => '_listing_price'];

            if (!empty($filters['min_price']) && !empty($filters['max_price'])) {
                $price_query['value'] = [floatval($filters['min_price']), floatval($filters['max_price'])];
                $price_query['type'] = 'NUMERIC';
                $price_query['compare'] = 'BETWEEN';
            } elseif (!empty($filters['min_price'])) {
                $price_query['value'] = floatval($filters['min_price']);
                $price_query['type'] = 'NUMERIC';
                $price_query['compare'] = '>=';
            } elseif (!empty($filters['max_price'])) {
                $price_query['value'] = floatval($filters['max_price']);
                $price_query['type'] = 'NUMERIC';
                $price_query['compare'] = '<=';
            }

            $query_args['meta_query'][] = $price_query;
        }

        // Bedrooms filter
        if (!empty($filters['bedrooms'])) {
            $query_args['meta_query'][] = [
                'key' => '_listing_bedrooms',
                'value' => intval($filters['bedrooms']),
                'type' => 'NUMERIC',
                'compare' => '>='
            ];
        }

        // Bathrooms filter
        if (!empty($filters['bathrooms'])) {
            $query_args['meta_query'][] = [
                'key' => '_listing_bathrooms',
                'value' => floatval($filters['bathrooms']),
                'type' => 'NUMERIC',
                'compare' => '>='
            ];
        }

        // Property type filter
        if (!empty($filters['property_type'])) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $filters['property_type']
            ];
        }

        // Location filters
        if (!empty($filters['city'])) {
            $query_args['meta_query'][] = [
                'key' => '_listing_city',
                'value' => $filters['city'],
                'compare' => 'LIKE'
            ];
        }

        // Set meta_query relation if multiple conditions
        if (count($query_args['meta_query']) > 1) {
            $query_args['meta_query']['relation'] = 'AND';
        }

        // Set tax_query relation if multiple conditions
        if (count($query_args['tax_query']) > 1) {
            $query_args['tax_query']['relation'] = 'AND';
        }

        return $query_args;
    }

    /**
     * Apply search sorting to query args
     */
    private function apply_search_sorting(array $query_args, string $sort): array {
        switch ($sort) {
            case 'price_low':
                $query_args['meta_key'] = '_listing_price';
                $query_args['orderby'] = 'meta_value_num';
                $query_args['order'] = 'ASC';
                break;

            case 'price_high':
                $query_args['meta_key'] = '_listing_price';
                $query_args['orderby'] = 'meta_value_num';
                $query_args['order'] = 'DESC';
                break;

            case 'newest':
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'DESC';
                break;

            case 'oldest':
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'ASC';
                break;

            case 'title':
                $query_args['orderby'] = 'title';
                $query_args['order'] = 'ASC';
                break;

            case 'relevance':
            default:
                $query_args['orderby'] = 'relevance';
                $query_args['order'] = 'DESC';
                break;
        }

        return $query_args;
    }

    /**
     * Get search suggestions for autocomplete
     */
    private function get_search_suggestions(string $query, string $type, int $limit): array {
        $suggestions = [];

        // Get post title suggestions
        $post_args = [
            'post_type' => $type === 'all' ? $this->searchable_post_types : [$type],
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            's' => $query,
            'fields' => 'ids'
        ];

        $posts = get_posts($post_args);

        foreach ($posts as $post_id) {
            $title = get_the_title($post_id);
            $post_type = get_post_type($post_id);

            $suggestions[] = [
                'value' => $title,
                'label' => $title,
                'type' => $post_type,
                'id' => $post_id,
                'url' => get_permalink($post_id)
            ];
        }

        // Add location suggestions for listings
        if ($type === 'all' || $type === 'listing') {
            $location_suggestions = $this->get_location_suggestions($query, $limit - count($suggestions));
            $suggestions = array_merge($suggestions, $location_suggestions);
        }

        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Get location-based suggestions
     */
    private function get_location_suggestions(string $query, int $limit): array {
        global $wpdb;

        $suggestions = [];

        if ($limit <= 0) {
            return $suggestions;
        }

        // Search cities
        $cities = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT meta_value as city
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_listing_city'
             AND meta_value LIKE %s
             LIMIT %d",
            '%' . $wpdb->esc_like($query) . '%',
            $limit
        ));

        foreach ($cities as $city) {
            $suggestions[] = [
                'value' => $city->city,
                'label' => $city->city . ' (City)',
                'type' => 'location',
                'category' => 'city'
            ];
        }

        return $suggestions;
    }

    /**
     * Format search response for frontend
     */
    private function format_search_response(array $search_results, string $view_mode): array {
        $formatted_posts = [];

        foreach ($search_results['posts'] as $post) {
            $post_data = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $post->post_type,
                'excerpt' => wp_trim_words($post->post_excerpt ?: $post->post_content, 20),
                'permalink' => get_permalink($post->ID),
                'date' => get_the_date('M j, Y', $post->ID)
            ];

            // Add post-type specific data
            switch ($post->post_type) {
                case 'listing':
                    $post_data = array_merge($post_data, [
                        'price' => get_post_meta($post->ID, '_listing_price', true),
                        'bedrooms' => get_post_meta($post->ID, '_listing_bedrooms', true),
                        'bathrooms' => get_post_meta($post->ID, '_listing_bathrooms', true),
                        'address' => get_post_meta($post->ID, '_listing_address', true),
                        'featured_image' => get_the_post_thumbnail_url($post->ID, 'medium')
                    ]);
                    break;

                case 'agent':
                    $post_data = array_merge($post_data, [
                        'email' => get_post_meta($post->ID, 'email', true),
                        'phone' => get_post_meta($post->ID, 'phone', true),
                        'specialty' => get_post_meta($post->ID, 'specialty', true),
                        'featured_image' => get_the_post_thumbnail_url($post->ID, 'thumbnail')
                    ]);
                    break;
            }

            $formatted_posts[] = $post_data;
        }

        return [
            'posts' => $formatted_posts,
            'pagination' => [
                'total' => $search_results['total'],
                'pages' => $search_results['max_pages'],
                'current_page' => $search_results['current_page'],
                'per_page' => $search_results['per_page']
            ],
            'view_mode' => $view_mode,
            'search_meta' => [
                'query_time' => 0, // Can be calculated if needed
                'post_types_found' => array_unique(array_column($formatted_posts, 'type'))
            ]
        ];
    }

    /**
     * Handle advanced search (placeholder for future expansion)
     */
    public function handle_advanced_search(): void {
        wp_send_json_error('Advanced search not implemented yet');
    }

    /**
     * Get searchable post types
     */
    public function get_searchable_post_types(): array {
        return apply_filters('hph_searchable_post_types', $this->searchable_post_types);
    }

    /**
     * Validate search parameters
     */
    private function validate_search_params(array $params): bool {
        // Add validation logic as needed
        return true;
    }
}