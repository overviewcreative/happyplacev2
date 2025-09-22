<?php
/**
 * Query Service
 *
 * Handles all query modifications and custom query variables
 * Migrated from theme to establish proper plugin-theme separation
 *
 * @package HappyPlace
 * @subpackage Services
 * @since 4.3.0 - Migrated from theme
 */

namespace HappyPlace\Services;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Query Service Class
 *
 * Manages all custom query variables and query modifications
 */
class QueryService {

    /**
     * Custom query variables
     */
    private $query_vars = array();

    /**
     * Service initialization
     */
    public function init() {
        $this->register_query_vars();
        $this->setup_hooks();
    }

    /**
     * Register custom query variables
     */
    private function register_query_vars() {
        $this->query_vars = array(
            // Listing filters
            'min_price',
            'max_price',
            'bedrooms',
            'bathrooms',
            'property_type',
            'property_status',
            'city',
            'state',
            'zip_code',
            'features',
            'show_all_status',

            // Dashboard
            'agent_dashboard',
            'dashboard_page',

            // Search
            'listing_search',
            'location_search',

            // Sorting
            'orderby',
            'order',

            // Advanced filters
            'square_feet_min',
            'square_feet_max',
            'lot_size_min',
            'lot_size_max',
            'year_built_min',
            'year_built_max',
            'listing_agent',
            'listing_office',
            'mls_number',

            // Geographic filters
            'neighborhood',
            'school_district',
            'latitude',
            'longitude',
            'radius'
        );

        add_filter('query_vars', [$this, 'add_query_vars']);
    }

    /**
     * Setup hooks
     */
    private function setup_hooks() {
        add_action('pre_get_posts', [$this, 'modify_queries']);
        add_action('wp_ajax_hph_advanced_search', [$this, 'handle_advanced_search']);
        add_action('wp_ajax_nopriv_hph_advanced_search', [$this, 'handle_advanced_search']);
    }

    /**
     * Add query variables
     */
    public function add_query_vars($vars) {
        return array_merge($vars, $this->query_vars);
    }

    /**
     * Modify queries
     */
    public function modify_queries($query) {
        if (!is_admin() && $query->is_main_query()) {
            if ($this->is_listing_query($query)) {
                $this->modify_listing_query($query);
            } elseif ($this->is_blog_query($query)) {
                $this->modify_blog_query($query);
            }
        }
    }

    /**
     * Check if this is a listing query
     */
    private function is_listing_query($query) {
        return is_post_type_archive('listing') ||
               is_tax('property_type') ||
               is_tax('listing_status') ||
               is_tax('property_status');
    }

    /**
     * Check if this is a blog query
     */
    private function is_blog_query($query) {
        return is_post_type_archive('blog_post') ||
               is_tax('blog_category') ||
               is_tax('blog_tag');
    }

    /**
     * Modify listing query
     */
    private function modify_listing_query($query) {
        // Build meta query
        $meta_query = $this->build_listing_meta_query();

        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }

        // Set posts per page
        $per_page = apply_filters('hph_listings_per_page', 12);
        $per_page = max(1, intval($per_page));
        $query->set('posts_per_page', $per_page);

        // Handle sorting
        $this->apply_listing_sorting($query);

        // Handle geographic filtering
        $this->apply_geographic_filters($query);
    }

    /**
     * Modify blog query
     */
    private function modify_blog_query($query) {
        // Set posts per page for blog
        $per_page = apply_filters('hph_blog_posts_per_page', 10);
        $per_page = max(1, intval($per_page));
        $query->set('posts_per_page', $per_page);

        // Default sorting by date
        $query->set('orderby', 'date');
        $query->set('order', 'DESC');
    }

    /**
     * Build listing meta query
     */
    private function build_listing_meta_query() {
        $meta_query = array('relation' => 'AND');

        // Price range
        if ($min_price = get_query_var('min_price')) {
            $meta_query[] = array(
                'key'     => 'listing_price',
                'value'   => intval($min_price),
                'type'    => 'NUMERIC',
                'compare' => '>='
            );
        }

        if ($max_price = get_query_var('max_price')) {
            $meta_query[] = array(
                'key'     => 'listing_price',
                'value'   => intval($max_price),
                'type'    => 'NUMERIC',
                'compare' => '<='
            );
        }

        // Square footage
        if ($sqft_min = get_query_var('square_feet_min')) {
            $meta_query[] = array(
                'key'     => 'square_feet',
                'value'   => intval($sqft_min),
                'type'    => 'NUMERIC',
                'compare' => '>='
            );
        }

        if ($sqft_max = get_query_var('square_feet_max')) {
            $meta_query[] = array(
                'key'     => 'square_feet',
                'value'   => intval($sqft_max),
                'type'    => 'NUMERIC',
                'compare' => '<='
            );
        }

        // Bedrooms
        if ($bedrooms = get_query_var('bedrooms')) {
            $meta_query[] = array(
                'key'     => 'bedrooms',
                'value'   => intval($bedrooms),
                'type'    => 'NUMERIC',
                'compare' => '>='
            );
        }

        // Bathrooms
        if ($bathrooms = get_query_var('bathrooms')) {
            $meta_query[] = array(
                'key'     => 'bathrooms',
                'value'   => intval($bathrooms),
                'type'    => 'NUMERIC',
                'compare' => '>='
            );
        }

        // Property type
        if ($property_type = get_query_var('property_type')) {
            $meta_query[] = array(
                'key'     => 'property_type',
                'value'   => sanitize_text_field($property_type),
                'compare' => '='
            );
        }

        // Year built range
        if ($year_min = get_query_var('year_built_min')) {
            $meta_query[] = array(
                'key'     => 'year_built',
                'value'   => intval($year_min),
                'type'    => 'NUMERIC',
                'compare' => '>='
            );
        }

        if ($year_max = get_query_var('year_built_max')) {
            $meta_query[] = array(
                'key'     => 'year_built',
                'value'   => intval($year_max),
                'type'    => 'NUMERIC',
                'compare' => '<='
            );
        }

        // Listing agent
        if ($agent = get_query_var('listing_agent')) {
            $meta_query[] = array(
                'key'     => 'listing_agent',
                'value'   => sanitize_text_field($agent),
                'compare' => '='
            );
        }

        // MLS number
        if ($mls = get_query_var('mls_number')) {
            $meta_query[] = array(
                'key'     => 'mls_number',
                'value'   => sanitize_text_field($mls),
                'compare' => '='
            );
        }

        // Status filter - include active listings and those without status set
        if (!get_query_var('show_all_status')) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key'     => 'listing_status',
                    'value'   => 'active',
                    'compare' => '='
                ),
                array(
                    'key'     => 'listing_status',
                    'value'   => '',
                    'compare' => '='
                ),
                array(
                    'key'     => 'listing_status',
                    'compare' => 'NOT EXISTS'
                )
            );
        }

        return count($meta_query) > 1 ? $meta_query : array();
    }

    /**
     * Apply geographic filters
     */
    private function apply_geographic_filters($query) {
        $latitude = get_query_var('latitude');
        $longitude = get_query_var('longitude');
        $radius = get_query_var('radius');

        if ($latitude && $longitude && $radius) {
            // Add geographic meta query for radius search
            $existing_meta = $query->get('meta_query') ?: array();
            $existing_meta[] = array(
                'relation' => 'AND',
                array(
                    'key'     => 'latitude',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key'     => 'longitude',
                    'compare' => 'EXISTS'
                )
            );

            $query->set('meta_query', $existing_meta);

            // Add custom WHERE clause for distance calculation
            add_filter('posts_where', function($where) use ($latitude, $longitude, $radius) {
                global $wpdb;
                $where .= $wpdb->prepare("
                    AND (
                        6371 * acos(
                            cos(radians(%f)) *
                            cos(radians(CAST(lat_meta.meta_value AS DECIMAL(10,6)))) *
                            cos(radians(CAST(lng_meta.meta_value AS DECIMAL(10,6))) - radians(%f)) +
                            sin(radians(%f)) *
                            sin(radians(CAST(lat_meta.meta_value AS DECIMAL(10,6))))
                        )
                    ) <= %f
                ", $latitude, $longitude, $latitude, $radius);
                return $where;
            });

            // Add JOINs for latitude and longitude meta
            add_filter('posts_join', function($join) {
                global $wpdb;
                $join .= " LEFT JOIN {$wpdb->postmeta} AS lat_meta ON {$wpdb->posts}.ID = lat_meta.post_id AND lat_meta.meta_key = 'latitude'";
                $join .= " LEFT JOIN {$wpdb->postmeta} AS lng_meta ON {$wpdb->posts}.ID = lng_meta.post_id AND lng_meta.meta_key = 'longitude'";
                return $join;
            });
        }
    }

    /**
     * Apply sorting to listing query
     */
    private function apply_listing_sorting($query) {
        $orderby = get_query_var('orderby') ?: 'date';
        $order = get_query_var('order') ?: 'DESC';

        switch ($orderby) {
            case 'price':
                $query->set('meta_key', 'listing_price');
                $query->set('orderby', 'meta_value_num');
                break;

            case 'bedrooms':
                $query->set('meta_key', 'bedrooms');
                $query->set('orderby', 'meta_value_num');
                break;

            case 'sqft':
                $query->set('meta_key', 'square_feet');
                $query->set('orderby', 'meta_value_num');
                break;

            case 'year_built':
                $query->set('meta_key', 'year_built');
                $query->set('orderby', 'meta_value_num');
                break;

            default:
                $query->set('orderby', $orderby);
                break;
        }

        $query->set('order', $order);
    }

    /**
     * Build search query
     */
    public function build_search_query($args = array()) {
        $defaults = array(
            'post_type'      => 'listing',
            'posts_per_page' => 12,
            'post_status'    => 'publish',
        );

        $args = wp_parse_args($args, $defaults);

        // Add meta query if needed
        if (!empty($args['filters'])) {
            $args['meta_query'] = $this->build_meta_query_from_filters($args['filters']);
            unset($args['filters']);
        }

        return new \WP_Query($args);
    }

    /**
     * Build meta query from filters
     */
    private function build_meta_query_from_filters($filters) {
        $meta_query = array('relation' => 'AND');

        foreach ($filters as $key => $value) {
            if (empty($value)) continue;

            switch ($key) {
                case 'min_price':
                case 'max_price':
                    $compare = strpos($key, 'min') !== false ? '>=' : '<=';
                    $meta_query[] = array(
                        'key'     => 'listing_price',
                        'value'   => intval($value),
                        'type'    => 'NUMERIC',
                        'compare' => $compare
                    );
                    break;

                case 'square_feet_min':
                case 'square_feet_max':
                    $compare = strpos($key, 'min') !== false ? '>=' : '<=';
                    $meta_query[] = array(
                        'key'     => 'square_feet',
                        'value'   => intval($value),
                        'type'    => 'NUMERIC',
                        'compare' => $compare
                    );
                    break;

                case 'bedrooms':
                case 'bathrooms':
                    $meta_query[] = array(
                        'key'     => $key,
                        'value'   => intval($value),
                        'type'    => 'NUMERIC',
                        'compare' => '>='
                    );
                    break;

                default:
                    $meta_query[] = array(
                        'key'     => $key,
                        'value'   => sanitize_text_field($value),
                        'compare' => '='
                    );
                    break;
            }
        }

        return $meta_query;
    }

    /**
     * Handle advanced search AJAX
     */
    public function handle_advanced_search() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_search_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        $filters = $_POST['filters'] ?? array();
        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 12);

        $args = array(
            'post_type' => 'listing',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'filters' => $filters
        );

        $search_query = $this->build_search_query($args);

        $results = array();
        if ($search_query->have_posts()) {
            while ($search_query->have_posts()) {
                $search_query->the_post();
                $results[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'featured_image' => get_the_post_thumbnail_url(get_the_ID(), 'medium_large'),
                    'price' => get_post_meta(get_the_ID(), 'listing_price', true),
                    'bedrooms' => get_post_meta(get_the_ID(), 'bedrooms', true),
                    'bathrooms' => get_post_meta(get_the_ID(), 'bathrooms', true),
                    'square_feet' => get_post_meta(get_the_ID(), 'square_feet', true)
                );
            }
            wp_reset_postdata();
        }

        wp_send_json_success(array(
            'results' => $results,
            'total' => $search_query->found_posts,
            'pages' => $search_query->max_num_pages,
            'current_page' => $page
        ));
    }

    /**
     * Get available query variables
     */
    public function get_query_vars() {
        return $this->query_vars;
    }

    /**
     * Check if a query variable is registered
     */
    public function is_registered_query_var($var) {
        return in_array($var, $this->query_vars);
    }
}