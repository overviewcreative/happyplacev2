<?php
/**
 * Query Manager Service
 * 
 * Handles all query modifications and custom query variables
 * 
 * @package HappyPlaceTheme
 */

class HPH_Query_Manager {
    
    /**
     * Custom query variables
     */
    private $query_vars = array();
    
    /**
     * Initialize query manager
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
            'order'
        );
        
        add_filter('query_vars', array($this, 'add_query_vars'));
    }
    
    /**
     * Setup hooks
     */
    private function setup_hooks() {
        add_action('pre_get_posts', array($this, 'modify_queries'));
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
            }
        }
    }
    
    /**
     * Check if this is a listing query
     */
    private function is_listing_query($query) {
        return is_post_type_archive('listing') || 
               is_tax('property_type') || 
               is_tax('listing_status');
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
        $config = HPH_Theme::instance()->get_service('config');
        $per_page = $config->get('listings_per_page', 12);
        $query->set('posts_per_page', apply_filters('hph_listings_per_page', $per_page));
        
        // Handle sorting
        $this->apply_sorting($query);
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
        
        // Status filter
        if (!get_query_var('show_all_status')) {
            $meta_query[] = array(
                'key'     => 'listing_status',
                'value'   => 'active',
                'compare' => '='
            );
        }
        
        return count($meta_query) > 1 ? $meta_query : array();
    }
    
    /**
     * Apply sorting to query
     */
    private function apply_sorting($query) {
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
        
        return new WP_Query($args);
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
}