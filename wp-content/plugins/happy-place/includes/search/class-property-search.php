<?php
/**
 * Property Search System
 * 
 * Advanced search functionality with filters, map integration, and saved searches
 * Provides both frontend interface and backend processing
 *
 * @package HappyPlace\Search
 */

namespace HappyPlace\Search;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Property_Search {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Search filters configuration
     */
    private array $filter_config = [
        'price' => [
            'type' => 'range',
            'label' => 'Price Range',
            'min' => 0,
            'max' => 10000000,
            'step' => 10000,
            'format' => 'currency',
        ],
        'bedrooms' => [
            'type' => 'select',
            'label' => 'Bedrooms',
            'options' => [
                '' => 'Any',
                '1' => '1+',
                '2' => '2+',
                '3' => '3+',
                '4' => '4+',
                '5' => '5+',
            ],
        ],
        'bathrooms' => [
            'type' => 'select',
            'label' => 'Bathrooms',
            'options' => [
                '' => 'Any',
                '1' => '1+',
                '1.5' => '1.5+',
                '2' => '2+',
                '2.5' => '2.5+',
                '3' => '3+',
                '4' => '4+',
            ],
        ],
        'property_type' => [
            'type' => 'checkbox',
            'label' => 'Property Type',
            'options' => [
                'single_family' => 'Single Family',
                'condo' => 'Condo',
                'townhouse' => 'Townhouse',
                'multi_family' => 'Multi-Family',
                'land' => 'Land',
                'commercial' => 'Commercial',
            ],
        ],
        'square_feet' => [
            'type' => 'range',
            'label' => 'Square Feet',
            'min' => 0,
            'max' => 10000,
            'step' => 100,
        ],
        'lot_size' => [
            'type' => 'range',
            'label' => 'Lot Size (acres)',
            'min' => 0,
            'max' => 100,
            'step' => 0.1,
        ],
        'year_built' => [
            'type' => 'range',
            'label' => 'Year Built',
            'min' => 1900,
            'max' => 2025, // Will be updated dynamically
            'step' => 1,
        ],
        'property_status' => [
            'type' => 'checkbox',
            'label' => 'Status',
            'options' => [
                'active' => 'Active',
                'pending' => 'Pending',
                'coming_soon' => 'Coming Soon',
                'sold' => 'Sold',
            ],
        ],
        'features' => [
            'type' => 'checkbox',
            'label' => 'Features',
            'options' => [
                'pool' => 'Pool',
                'spa' => 'Hot Tub/Spa',
                'garage' => 'Garage',
                'fireplace' => 'Fireplace',
                'waterfront' => 'Waterfront',
                'view' => 'View',
                'basement' => 'Basement',
                'hardwood' => 'Hardwood Floors',
                'ac' => 'Air Conditioning',
                'solar' => 'Solar Panels',
            ],
        ],
        'city' => [
            'type' => 'select',
            'label' => 'City',
            'options' => 'dynamic', // Populated from database
        ],
        'community' => [
            'type' => 'select',
            'label' => 'Community',
            'options' => 'dynamic', // Populated from database
        ],
        'school_district' => [
            'type' => 'select',
            'label' => 'School District',
            'options' => 'dynamic',
        ],
        'sort_by' => [
            'type' => 'select',
            'label' => 'Sort By',
            'options' => [
                'date_desc' => 'Newest First',
                'date_asc' => 'Oldest First',
                'price_asc' => 'Price (Low to High)',
                'price_desc' => 'Price (High to Low)',
                'bedrooms_desc' => 'Bedrooms (Most First)',
                'sqft_desc' => 'Square Feet (Largest First)',
            ],
        ],
    ];
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize search system
     */
    public function init() {
        // Register shortcodes
        add_shortcode('hp_property_search', [$this, 'render_search_form']);
        add_shortcode('hp_search_results', [$this, 'render_search_results']);
        add_shortcode('hp_map_search', [$this, 'render_map_search']);
        add_shortcode('hp_quick_search', [$this, 'render_quick_search']);
        
        // Register AJAX handlers
        add_action('wp_ajax_hp_search_properties', [$this, 'ajax_search_properties']);
        add_action('wp_ajax_nopriv_hp_search_properties', [$this, 'ajax_search_properties']);
        
        add_action('wp_ajax_hp_save_search', [$this, 'ajax_save_search']);
        add_action('wp_ajax_hp_get_saved_searches', [$this, 'ajax_get_saved_searches']);
        add_action('wp_ajax_hp_delete_saved_search', [$this, 'ajax_delete_saved_search']);
        
        add_action('wp_ajax_hp_autocomplete', [$this, 'ajax_autocomplete']);
        add_action('wp_ajax_nopriv_hp_autocomplete', [$this, 'ajax_autocomplete']);
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Register saved search post type
        add_action('init', [$this, 'register_saved_search_post_type']);
        
        // Schedule search alerts
        add_action('hp_check_search_alerts', [$this, 'process_search_alerts']);
        if (!wp_next_scheduled('hp_check_search_alerts')) {
            wp_schedule_event(time(), 'daily', 'hp_check_search_alerts');
        }
        
        hp_log('Property Search system initialized', 'info', 'SEARCH');
    }
    
    /**
     * Register saved search post type
     */
    public function register_saved_search_post_type() {
        $args = [
            'label' => 'Saved Searches',
            'public' => false,
            'show_ui' => false,
            'supports' => ['title'],
            'capability_type' => 'post',
        ];
        
        register_post_type('saved_search', $args);
    }
    
    /**
     * Enqueue search assets
     */
    public function enqueue_assets() {
        if (!$this->should_load_assets()) {
            return;
        }
        
        // Styles
        wp_enqueue_style(
            'hp-property-search',
            HP_PLUGIN_URL . 'assets/css/property-search.css',
            [],
            HP_VERSION
        );
        
        // Scripts
        wp_enqueue_script(
            'hp-property-search',
            HP_PLUGIN_URL . 'assets/js/property-search.js',
            ['jquery'],
            HP_VERSION,
            true
        );
        
        // Map integration if enabled
        if (get_option('hp_enable_map_search', true)) {
            $google_maps_key = get_option('hp_google_maps_api_key');
            if ($google_maps_key) {
                wp_enqueue_script(
                    'google-maps',
                    "https://maps.googleapis.com/maps/api/js?key={$google_maps_key}&libraries=places,drawing",
                    [],
                    null,
                    true
                );
                
                wp_enqueue_script(
                    'hp-map-search',
                    HP_PLUGIN_URL . 'assets/js/map-search.js',
                    ['jquery', 'google-maps'],
                    HP_VERSION,
                    true
                );
            }
        }
        
        // Localize script
        wp_localize_script('hp-property-search', 'hp_search', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_search_nonce'),
            'is_logged_in' => is_user_logged_in(),
            'messages' => [
                'no_results' => __('No properties found matching your criteria.', 'happy-place'),
                'loading' => __('Searching properties...', 'happy-place'),
                'error' => __('An error occurred. Please try again.', 'happy-place'),
                'saved' => __('Search saved successfully!', 'happy-place'),
                'removed' => __('Saved search removed.', 'happy-place'),
            ],
            'map_config' => [
                'center_lat' => get_option('hp_map_center_lat', 40.7128),
                'center_lng' => get_option('hp_map_center_lng', -74.0060),
                'default_zoom' => get_option('hp_map_default_zoom', 11),
                'cluster_enabled' => get_option('hp_map_clustering', true),
            ],
        ]);
    }
    
    /**
     * Check if assets should load
     */
    private function should_load_assets() {
        // Check for search shortcodes
        global $post;
        if ($post) {
            $shortcodes = ['hp_property_search', 'hp_search_results', 'hp_map_search', 'hp_quick_search'];
            foreach ($shortcodes as $shortcode) {
                if (has_shortcode($post->post_content, $shortcode)) {
                    return true;
                }
            }
        }
        
        // Load on listing archive pages
        if (is_post_type_archive('listing')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Render search form shortcode
     */
    public function render_search_form($atts) {
        $atts = shortcode_atts([
            'style' => 'horizontal', // horizontal, vertical, advanced
            'filters' => 'price,bedrooms,bathrooms,property_type,city',
            'show_map' => false,
            'show_saved' => true,
            'results_page' => '',
        ], $atts);
        
        $filters_to_show = explode(',', $atts['filters']);
        
        ob_start();
        ?>
        <div class="hp-search-form-wrapper hp-search-<?php echo esc_attr($atts['style']); ?>">
            <form class="hp-search-form" data-style="<?php echo esc_attr($atts['style']); ?>">
                
                <!-- Quick search bar -->
                <div class="hp-search-bar">
                    <input type="text" 
                           name="search_query" 
                           class="hp-search-input" 
                           placeholder="<?php _e('Search by address, city, ZIP, or MLS#...', 'happy-place'); ?>">
                    <button type="submit" class="hp-search-submit">
                        <span class="hp-search-icon">🔍</span>
                        <span class="hp-search-text"><?php _e('Search', 'happy-place'); ?></span>
                    </button>
                </div>
                
                <!-- Filters -->
                <div class="hp-search-filters">
                    <?php foreach ($filters_to_show as $filter_key): ?>
                        <?php $this->render_filter($filter_key); ?>
                    <?php endforeach; ?>
                    
                    <?php if ($atts['style'] === 'advanced'): ?>
                        <button type="button" class="hp-more-filters-toggle">
                            <?php _e('More Filters', 'happy-place'); ?>
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Advanced filters (hidden by default) -->
                <?php if ($atts['style'] === 'advanced'): ?>
                <div class="hp-advanced-filters" style="display: none;">
                    <?php 
                    $advanced_filters = ['square_feet', 'lot_size', 'year_built', 'features', 'school_district'];
                    foreach ($advanced_filters as $filter_key):
                        $this->render_filter($filter_key);
                    endforeach;
                    ?>
                </div>
                <?php endif; ?>
                
                <!-- Search actions -->
                <div class="hp-search-actions">
                    <?php if ($atts['show_saved'] && is_user_logged_in()): ?>
                        <button type="button" class="hp-save-search-btn">
                            <?php _e('Save Search', 'happy-place'); ?>
                        </button>
                    <?php endif; ?>
                    
                    <button type="button" class="hp-reset-filters">
                        <?php _e('Reset', 'happy-place'); ?>
                    </button>
                </div>
                
                <!-- Hidden fields -->
                <input type="hidden" name="action" value="hp_search_properties">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hp_search_nonce'); ?>">
                <?php if ($atts['results_page']): ?>
                    <input type="hidden" name="results_page" value="<?php echo esc_attr($atts['results_page']); ?>">
                <?php endif; ?>
            </form>
            
            <!-- Map container if enabled -->
            <?php if ($atts['show_map']): ?>
            <div class="hp-search-map-container">
                <div id="hp-search-map" class="hp-search-map"></div>
                <div class="hp-map-controls">
                    <button type="button" class="hp-draw-search-area">
                        <?php _e('Draw Search Area', 'happy-place'); ?>
                    </button>
                    <button type="button" class="hp-clear-search-area" style="display: none;">
                        <?php _e('Clear Area', 'happy-place'); ?>
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render individual filter
     */
    private function render_filter($filter_key) {
        if (!isset($this->filter_config[$filter_key])) {
            return;
        }
        
        $config = $this->filter_config[$filter_key];
        $filter_id = 'hp-filter-' . $filter_key;
        
        ?>
        <div class="hp-filter-group hp-filter-<?php echo esc_attr($filter_key); ?>">
            <?php
            switch ($config['type']) {
                case 'range':
                    $this->render_range_filter($filter_key, $config);
                    break;
                    
                case 'select':
                    $this->render_select_filter($filter_key, $config);
                    break;
                    
                case 'checkbox':
                    $this->render_checkbox_filter($filter_key, $config);
                    break;
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Render range filter
     */
    private function render_range_filter($key, $config) {
        $min_id = 'hp-' . $key . '-min';
        $max_id = 'hp-' . $key . '-max';
        ?>
        <label class="hp-filter-label"><?php echo esc_html($config['label']); ?></label>
        <div class="hp-range-inputs">
            <input type="number" 
                   id="<?php echo esc_attr($min_id); ?>"
                   name="<?php echo esc_attr($key); ?>_min" 
                   class="hp-range-min"
                   placeholder="<?php _e('Min', 'happy-place'); ?>"
                   min="<?php echo esc_attr($config['min']); ?>"
                   max="<?php echo esc_attr($config['max']); ?>"
                   step="<?php echo esc_attr($config['step']); ?>">
            <span class="hp-range-separator">-</span>
            <input type="number" 
                   id="<?php echo esc_attr($max_id); ?>"
                   name="<?php echo esc_attr($key); ?>_max" 
                   class="hp-range-max"
                   placeholder="<?php _e('Max', 'happy-place'); ?>"
                   min="<?php echo esc_attr($config['min']); ?>"
                   max="<?php echo esc_attr($config['max']); ?>"
                   step="<?php echo esc_attr($config['step']); ?>">
        </div>
        <?php
    }
    
    /**
     * Render select filter
     */
    private function render_select_filter($key, $config) {
        $options = $config['options'];
        
        // Load dynamic options
        if ($options === 'dynamic') {
            $options = $this->get_dynamic_options($key);
        }
        
        ?>
        <label class="hp-filter-label" for="hp-<?php echo esc_attr($key); ?>">
            <?php echo esc_html($config['label']); ?>
        </label>
        <select id="hp-<?php echo esc_attr($key); ?>" 
                name="<?php echo esc_attr($key); ?>" 
                class="hp-filter-select">
            <?php foreach ($options as $value => $label): ?>
                <option value="<?php echo esc_attr($value); ?>">
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
    
    /**
     * Render checkbox filter
     */
    private function render_checkbox_filter($key, $config) {
        ?>
        <label class="hp-filter-label"><?php echo esc_html($config['label']); ?></label>
        <div class="hp-checkbox-group">
            <?php foreach ($config['options'] as $value => $label): ?>
                <label class="hp-checkbox-label">
                    <input type="checkbox" 
                           name="<?php echo esc_attr($key); ?>[]" 
                           value="<?php echo esc_attr($value); ?>"
                           class="hp-filter-checkbox">
                    <span><?php echo esc_html($label); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * Get dynamic options for filters
     */
    private function get_dynamic_options($key) {
        $options = ['' => __('Any', 'happy-place')];
        
        switch ($key) {
            case 'city':
                $cities = get_posts([
                    'post_type' => 'city',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC',
                ]);
                foreach ($cities as $city) {
                    $options[$city->ID] = $city->post_title;
                }
                break;
                
            case 'community':
                $communities = get_posts([
                    'post_type' => 'community',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC',
                ]);
                foreach ($communities as $community) {
                    $options[$community->ID] = $community->post_title;
                }
                break;
                
            case 'school_district':
                // Get unique school districts from listings
                global $wpdb;
                $districts = $wpdb->get_col("
                    SELECT DISTINCT meta_value 
                    FROM {$wpdb->postmeta} 
                    WHERE meta_key = 'school_district' 
                    AND meta_value != ''
                    ORDER BY meta_value ASC
                ");
                foreach ($districts as $district) {
                    $options[$district] = $district;
                }
                break;
        }
        
        return $options;
    }
    
    /**
     * Render search results shortcode
     */
    public function render_search_results($atts) {
        $atts = shortcode_atts([
            'per_page' => 12,
            'layout' => 'grid', // grid, list, map
            'show_filters' => true,
            'show_sort' => true,
            'show_save' => true,
        ], $atts);
        
        ob_start();
        ?>
        <div class="hp-search-results-wrapper" data-layout="<?php echo esc_attr($atts['layout']); ?>">
            
            <!-- Results header -->
            <div class="hp-results-header">
                <div class="hp-results-count">
                    <span class="hp-count-number">0</span> 
                    <?php _e('Properties Found', 'happy-place'); ?>
                </div>
                
                <?php if ($atts['show_sort']): ?>
                <div class="hp-results-sort">
                    <?php $this->render_filter('sort_by'); ?>
                </div>
                <?php endif; ?>
                
                <div class="hp-results-view">
                    <button type="button" class="hp-view-grid <?php echo $atts['layout'] === 'grid' ? 'active' : ''; ?>" data-view="grid">
                        <span>⊞</span>
                    </button>
                    <button type="button" class="hp-view-list <?php echo $atts['layout'] === 'list' ? 'active' : ''; ?>" data-view="list">
                        <span>☰</span>
                    </button>
                    <button type="button" class="hp-view-map <?php echo $atts['layout'] === 'map' ? 'active' : ''; ?>" data-view="map">
                        <span>📍</span>
                    </button>
                </div>
            </div>
            
            <!-- Active filters display -->
            <div class="hp-active-filters" style="display: none;">
                <span class="hp-active-filters-label"><?php _e('Active Filters:', 'happy-place'); ?></span>
                <div class="hp-filter-tags"></div>
                <button type="button" class="hp-clear-all-filters">
                    <?php _e('Clear All', 'happy-place'); ?>
                </button>
            </div>
            
            <!-- Results container -->
            <div class="hp-results-container hp-layout-<?php echo esc_attr($atts['layout']); ?>">
                <div class="hp-results-loading">
                    <div class="hp-spinner"></div>
                    <p><?php _e('Loading properties...', 'happy-place'); ?></p>
                </div>
                
                <div class="hp-results-grid" style="display: none;"></div>
                
                <div class="hp-results-empty" style="display: none;">
                    <p><?php _e('No properties found matching your criteria.', 'happy-place'); ?></p>
                    <button type="button" class="hp-modify-search">
                        <?php _e('Modify Search', 'happy-place'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="hp-results-pagination" style="display: none;">
                <button type="button" class="hp-page-prev" disabled>
                    <?php _e('Previous', 'happy-place'); ?>
                </button>
                <div class="hp-page-numbers"></div>
                <button type="button" class="hp-page-next">
                    <?php _e('Next', 'happy-place'); ?>
                </button>
            </div>
            
            <!-- Hidden data -->
            <input type="hidden" class="hp-results-per-page" value="<?php echo esc_attr($atts['per_page']); ?>">
            <input type="hidden" class="hp-current-page" value="1">
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * AJAX search properties handler
     */
    public function ajax_search_properties() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_search_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Get search parameters
        $params = $this->sanitize_search_params($_POST);
        
        // Perform search
        $results = $this->search_properties($params);
        
        // Format response
        $response = [
            'success' => true,
            'results' => $results['properties'],
            'total' => $results['total'],
            'pages' => $results['pages'],
            'page' => $params['page'],
            'per_page' => $params['per_page'],
        ];
        
        // Add map data if requested
        if (!empty($params['include_map_data'])) {
            $response['map_data'] = $this->get_map_data($results['properties']);
        }
        
        wp_send_json_success($response);
    }
    
    /**
     * Sanitize search parameters
     */
    private function sanitize_search_params($data) {
        $params = [];
        
        // Text search
        $params['search_query'] = sanitize_text_field($data['search_query'] ?? '');
        
        // Numeric filters
        $numeric_fields = [
            'price_min', 'price_max',
            'bedrooms', 'bathrooms',
            'square_feet_min', 'square_feet_max',
            'lot_size_min', 'lot_size_max',
            'year_built_min', 'year_built_max',
        ];
        
        foreach ($numeric_fields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $params[$field] = floatval($data[$field]);
            }
        }
        
        // Select fields
        $select_fields = ['city', 'community', 'school_district', 'sort_by'];
        foreach ($select_fields as $field) {
            if (!empty($data[$field])) {
                $params[$field] = sanitize_text_field($data[$field]);
            }
        }
        
        // Checkbox fields
        $checkbox_fields = ['property_type', 'property_status', 'features'];
        foreach ($checkbox_fields as $field) {
            if (!empty($data[$field]) && is_array($data[$field])) {
                $params[$field] = array_map('sanitize_text_field', $data[$field]);
            }
        }
        
        // Map bounds
        if (!empty($data['map_bounds'])) {
            $params['map_bounds'] = [
                'north' => floatval($data['map_bounds']['north'] ?? 0),
                'south' => floatval($data['map_bounds']['south'] ?? 0),
                'east' => floatval($data['map_bounds']['east'] ?? 0),
                'west' => floatval($data['map_bounds']['west'] ?? 0),
            ];
        }
        
        // Pagination
        $params['page'] = max(1, intval($data['page'] ?? 1));
        $params['per_page'] = min(100, max(1, intval($data['per_page'] ?? 12)));
        
        return $params;
    }
    
    /**
     * Search properties
     */
    public function search_properties($params) {
        $args = [
            'post_type' => 'listing',
            'post_status' => 'publish',
            'posts_per_page' => $params['per_page'],
            'paged' => $params['page'],
            'meta_query' => ['relation' => 'AND'],
            'tax_query' => ['relation' => 'AND'],
        ];
        
        // Text search
        if (!empty($params['search_query'])) {
            $args['s'] = $params['search_query'];
            
            // Also search by MLS number
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => 'mls_number',
                    'value' => $params['search_query'],
                    'compare' => 'LIKE',
                ],
            ];
        }
        
        // Price range
        if (isset($params['price_min']) || isset($params['price_max'])) {
            $price_query = ['key' => 'price', 'type' => 'NUMERIC'];
            
            if (isset($params['price_min']) && isset($params['price_max'])) {
                $price_query['value'] = [$params['price_min'], $params['price_max']];
                $price_query['compare'] = 'BETWEEN';
            } elseif (isset($params['price_min'])) {
                $price_query['value'] = $params['price_min'];
                $price_query['compare'] = '>=';
            } else {
                $price_query['value'] = $params['price_max'];
                $price_query['compare'] = '<=';
            }
            
            $args['meta_query'][] = $price_query;
        }
        
        // Bedrooms
        if (!empty($params['bedrooms'])) {
            $args['meta_query'][] = [
                'key' => 'bedrooms',
                'value' => $params['bedrooms'],
                'compare' => '>=',
                'type' => 'NUMERIC',
            ];
        }
        
        // Bathrooms
        if (!empty($params['bathrooms'])) {
            $args['meta_query'][] = [
                'key' => 'bathrooms_full',
                'value' => $params['bathrooms'],
                'compare' => '>=',
                'type' => 'NUMERIC',
            ];
        }
        
        // Property type
        if (!empty($params['property_type'])) {
            $args['meta_query'][] = [
                'key' => 'property_type',
                'value' => $params['property_type'],
                'compare' => 'IN',
            ];
        }
        
        // Property status
        if (!empty($params['property_status'])) {
            $args['meta_query'][] = [
                'key' => 'property_status',
                'value' => $params['property_status'],
                'compare' => 'IN',
            ];
        }
        
        // Square feet range
        if (isset($params['square_feet_min']) || isset($params['square_feet_max'])) {
            $sqft_query = ['key' => 'square_feet', 'type' => 'NUMERIC'];
            
            if (isset($params['square_feet_min']) && isset($params['square_feet_max'])) {
                $sqft_query['value'] = [$params['square_feet_min'], $params['square_feet_max']];
                $sqft_query['compare'] = 'BETWEEN';
            } elseif (isset($params['square_feet_min'])) {
                $sqft_query['value'] = $params['square_feet_min'];
                $sqft_query['compare'] = '>=';
            } else {
                $sqft_query['value'] = $params['square_feet_max'];
                $sqft_query['compare'] = '<=';
            }
            
            $args['meta_query'][] = $sqft_query;
        }
        
        // City filter
        if (!empty($params['city'])) {
            $args['meta_query'][] = [
                'key' => 'city',
                'value' => $params['city'],
                'compare' => '=',
            ];
        }
        
        // Community filter
        if (!empty($params['community'])) {
            $args['meta_query'][] = [
                'key' => 'community',
                'value' => $params['community'],
                'compare' => '=',
            ];
        }
        
        // Map bounds filter
        if (!empty($params['map_bounds'])) {
            $args['meta_query'][] = [
                [
                    'key' => 'latitude',
                    'value' => [$params['map_bounds']['south'], $params['map_bounds']['north']],
                    'compare' => 'BETWEEN',
                    'type' => 'DECIMAL(10,8)',
                ],
                [
                    'key' => 'longitude',
                    'value' => [$params['map_bounds']['west'], $params['map_bounds']['east']],
                    'compare' => 'BETWEEN',
                    'type' => 'DECIMAL(11,8)',
                ],
            ];
        }
        
        // Sorting
        if (!empty($params['sort_by'])) {
            switch ($params['sort_by']) {
                case 'price_asc':
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_key'] = 'price';
                    $args['order'] = 'ASC';
                    break;
                case 'price_desc':
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_key'] = 'price';
                    $args['order'] = 'DESC';
                    break;
                case 'date_asc':
                    $args['orderby'] = 'date';
                    $args['order'] = 'ASC';
                    break;
                case 'date_desc':
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                    break;
                case 'bedrooms_desc':
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_key'] = 'bedrooms';
                    $args['order'] = 'DESC';
                    break;
                case 'sqft_desc':
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_key'] = 'square_feet';
                    $args['order'] = 'DESC';
                    break;
            }
        } else {
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
        }
        
        // Execute query
        $query = new \WP_Query($args);
        
        // Format results
        $properties = [];
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $properties[] = $this->format_property_data(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return [
            'properties' => $properties,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ];
    }
    
    /**
     * Format property data for response
     */
    private function format_property_data($listing_id) {
        $listing = get_post($listing_id);
        
        $data = [
            'id' => $listing_id,
            'title' => get_the_title($listing_id),
            'url' => get_permalink($listing_id),
            'excerpt' => get_the_excerpt($listing_id),
        ];
        
        // Add ACF fields
        if (function_exists('get_field')) {
            $data['price'] = get_field('listing_price', $listing_id);
            $data['bedrooms'] = get_field('bedrooms', $listing_id);
            $data['bathrooms'] = get_field('bathrooms_full', $listing_id);
            $data['square_feet'] = get_field('square_feet', $listing_id);
            $data['property_status'] = get_field('property_status', $listing_id);
            $data['property_type'] = get_field('property_type', $listing_id);
            
            // Address
            $data['address'] = [
                'street' => get_field('street_address', $listing_id),
                'city' => get_field('city', $listing_id),
                'state' => get_field('state', $listing_id),
                'zip' => get_field('zip_code', $listing_id),
            ];
            
            // Coordinates for map
            $data['coordinates'] = [
                'lat' => get_field('latitude', $listing_id),
                'lng' => get_field('longitude', $listing_id),
            ];
            
            // Featured image
            $featured_image_id = get_post_thumbnail_id($listing_id);
            if ($featured_image_id) {
                $data['image'] = [
                    'thumb' => wp_get_attachment_image_url($featured_image_id, 'thumbnail'),
                    'medium' => wp_get_attachment_image_url($featured_image_id, 'medium'),
                    'large' => wp_get_attachment_image_url($featured_image_id, 'large'),
                ];
            }
            
            // Agent info
            $agent_id = get_field('listing_agent', $listing_id);
            if ($agent_id) {
                $data['agent'] = [
                    'id' => $agent_id,
                    'name' => get_the_title($agent_id),
                    'phone' => get_field('phone', $agent_id),
                ];
            }
        }
        
        return $data;
    }
    
    /**
     * Get map data for properties
     */
    private function get_map_data($properties) {
        $markers = [];
        
        foreach ($properties as $property) {
            if (!empty($property['coordinates']['lat']) && !empty($property['coordinates']['lng'])) {
                $markers[] = [
                    'id' => $property['id'],
                    'lat' => floatval($property['coordinates']['lat']),
                    'lng' => floatval($property['coordinates']['lng']),
                    'title' => $property['title'],
                    'price' => $property['price'],
                    'beds' => $property['bedrooms'],
                    'baths' => $property['bathrooms'],
                    'image' => $property['image']['thumb'] ?? '',
                    'url' => $property['url'],
                ];
            }
        }
        
        return [
            'markers' => $markers,
            'bounds' => $this->calculate_bounds($markers),
        ];
    }
    
    /**
     * Calculate map bounds from markers
     */
    private function calculate_bounds($markers) {
        if (empty($markers)) {
            return null;
        }
        
        $lats = array_column($markers, 'lat');
        $lngs = array_column($markers, 'lng');
        
        return [
            'north' => max($lats),
            'south' => min($lats),
            'east' => max($lngs),
            'west' => min($lngs),
        ];
    }
    
    /**
     * AJAX save search handler
     */
    public function ajax_save_search() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'You must be logged in to save searches']);
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_search_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Get search parameters
        $search_params = $this->sanitize_search_params($_POST);
        $search_name = sanitize_text_field($_POST['search_name'] ?? 'My Search');
        $alert_enabled = !empty($_POST['alert_enabled']);
        
        // Create saved search post
        $post_data = [
            'post_title' => $search_name,
            'post_type' => 'saved_search',
            'post_status' => 'private',
            'post_author' => get_current_user_id(),
        ];
        
        $search_id = wp_insert_post($post_data);
        
        if (is_wp_error($search_id)) {
            wp_send_json_error(['message' => 'Failed to save search']);
            return;
        }
        
        // Save search parameters as meta
        update_post_meta($search_id, 'search_params', $search_params);
        update_post_meta($search_id, 'alert_enabled', $alert_enabled);
        update_post_meta($search_id, 'last_alert_sent', current_time('mysql'));
        
        wp_send_json_success([
            'message' => 'Search saved successfully',
            'search_id' => $search_id,
        ]);
    }
    
    /**
     * AJAX get saved searches handler
     */
    public function ajax_get_saved_searches() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'You must be logged in']);
            return;
        }
        
        // Get user's saved searches
        $searches = get_posts([
            'post_type' => 'saved_search',
            'post_status' => 'private',
            'author' => get_current_user_id(),
            'posts_per_page' => -1,
        ]);
        
        $saved_searches = [];
        foreach ($searches as $search) {
            $saved_searches[] = [
                'id' => $search->ID,
                'name' => $search->post_title,
                'date' => $search->post_date,
                'params' => get_post_meta($search->ID, 'search_params', true),
                'alert_enabled' => get_post_meta($search->ID, 'alert_enabled', true),
            ];
        }
        
        wp_send_json_success(['searches' => $saved_searches]);
    }
    
    /**
     * AJAX delete saved search handler
     */
    public function ajax_delete_saved_search() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'You must be logged in']);
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_search_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $search_id = intval($_POST['search_id'] ?? 0);
        
        // Verify ownership
        $search = get_post($search_id);
        if (!$search || $search->post_author != get_current_user_id()) {
            wp_send_json_error(['message' => 'Invalid search']);
            return;
        }
        
        // Delete the search
        wp_delete_post($search_id, true);
        
        wp_send_json_success(['message' => 'Search deleted successfully']);
    }
    
    /**
     * AJAX autocomplete handler
     */
    public function ajax_autocomplete() {
        $query = sanitize_text_field($_GET['q'] ?? '');
        $type = sanitize_text_field($_GET['type'] ?? 'all');
        
        if (strlen($query) < 2) {
            wp_send_json_success(['suggestions' => []]);
            return;
        }
        
        $suggestions = [];
        
        // Search cities
        if ($type === 'all' || $type === 'city') {
            $cities = get_posts([
                'post_type' => 'city',
                's' => $query,
                'posts_per_page' => 5,
            ]);
            
            foreach ($cities as $city) {
                $suggestions[] = [
                    'type' => 'city',
                    'value' => $city->post_title,
                    'label' => $city->post_title . ', ' . get_field('state', $city->ID),
                ];
            }
        }
        
        // Search addresses
        if ($type === 'all' || $type === 'address') {
            global $wpdb;
            $addresses = $wpdb->get_results($wpdb->prepare("
                SELECT DISTINCT meta_value 
                FROM {$wpdb->postmeta} 
                WHERE meta_key = 'street_address' 
                AND meta_value LIKE %s
                LIMIT 5
            ", '%' . $wpdb->esc_like($query) . '%'));
            
            foreach ($addresses as $address) {
                $suggestions[] = [
                    'type' => 'address',
                    'value' => $address->meta_value,
                    'label' => $address->meta_value,
                ];
            }
        }
        
        // Search MLS numbers
        if ($type === 'all' || $type === 'mls') {
            global $wpdb;
            $mls_numbers = $wpdb->get_results($wpdb->prepare("
                SELECT DISTINCT meta_value 
                FROM {$wpdb->postmeta} 
                WHERE meta_key = 'mls_number' 
                AND meta_value LIKE %s
                LIMIT 5
            ", '%' . $wpdb->esc_like($query) . '%'));
            
            foreach ($mls_numbers as $mls) {
                $suggestions[] = [
                    'type' => 'mls',
                    'value' => $mls->meta_value,
                    'label' => 'MLS# ' . $mls->meta_value,
                ];
            }
        }
        
        wp_send_json_success(['suggestions' => $suggestions]);
    }
    
    /**
     * Process search alerts (scheduled)
     */
    public function process_search_alerts() {
        // Get all saved searches with alerts enabled
        $searches = get_posts([
            'post_type' => 'saved_search',
            'post_status' => 'private',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'alert_enabled',
                    'value' => '1',
                    'compare' => '=',
                ],
            ],
        ]);
        
        foreach ($searches as $search) {
            $this->process_single_alert($search);
        }
    }
    
    /**
     * Process single search alert
     */
    private function process_single_alert($search) {
        $params = get_post_meta($search->ID, 'search_params', true);
        $last_sent = get_post_meta($search->ID, 'last_alert_sent', true);
        
        // Add date filter to get only new listings
        $params['date_after'] = $last_sent;
        
        // Search for new properties
        $results = $this->search_properties($params);
        
        if (empty($results['properties'])) {
            return;
        }
        
        // Send email notification
        $user = get_user_by('id', $search->post_author);
        if (!$user) {
            return;
        }
        
        $subject = sprintf(
            '%d new properties match your saved search: %s',
            count($results['properties']),
            $search->post_title
        );
        
        $message = $this->format_alert_email($search, $results['properties']);
        
        wp_mail($user->user_email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
        
        // Update last sent time
        update_post_meta($search->ID, 'last_alert_sent', current_time('mysql'));
    }
    
    /**
     * Format alert email
     */
    private function format_alert_email($search, $properties) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #3498db; color: white; padding: 20px; text-align: center; }
                .property { border: 1px solid #ddd; padding: 15px; margin: 15px 0; }
                .property img { max-width: 150px; float: left; margin-right: 15px; }
                .property-details { overflow: hidden; }
                .button { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>New Properties Match Your Search</h2>
                    <p><?php echo esc_html($search->post_title); ?></p>
                </div>
                
                <?php foreach ($properties as $property): ?>
                <div class="property">
                    <?php if (!empty($property['image']['thumb'])): ?>
                        <img src="<?php echo esc_url($property['image']['thumb']); ?>" alt="">
                    <?php endif; ?>
                    
                    <div class="property-details">
                        <h3><?php echo esc_html($property['title']); ?></h3>
                        <p>
                            $<?php echo number_format($property['price']); ?> | 
                            <?php echo $property['bedrooms']; ?> beds | 
                            <?php echo $property['bathrooms']; ?> baths
                        </p>
                        <p><?php echo esc_html($property['address']['street']); ?></p>
                        <a href="<?php echo esc_url($property['url']); ?>" class="button">View Property</a>
                    </div>
                    <div style="clear: both;"></div>
                </div>
                <?php endforeach; ?>
                
                <div style="text-align: center; margin-top: 30px;">
                    <p>To manage your saved searches, visit your dashboard.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render map search shortcode
     */
    public function render_map_search($atts) {
        $atts = shortcode_atts([
            'height' => '600px',
            'show_filters' => true,
            'default_view' => 'map',
        ], $atts);
        
        ob_start();
        ?>
        <div class="hp-map-search-wrapper">
            <?php if ($atts['show_filters']): ?>
                <?php echo $this->render_search_form(['style' => 'horizontal', 'show_map' => false]); ?>
            <?php endif; ?>
            
            <div class="hp-map-search-container" style="height: <?php echo esc_attr($atts['height']); ?>;">
                <div id="hp-map-search-main" class="hp-map-full"></div>
                
                <div class="hp-map-sidebar">
                    <div class="hp-map-results"></div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render quick search shortcode
     */
    public function render_quick_search($atts) {
        $atts = shortcode_atts([
            'placeholder' => 'Enter address, city, or ZIP...',
            'button_text' => 'Search',
            'results_page' => '',
        ], $atts);
        
        ob_start();
        ?>
        <div class="hp-quick-search">
            <form class="hp-quick-search-form" action="<?php echo esc_url($atts['results_page']); ?>" method="get">
                <input type="text" 
                       name="q" 
                       class="hp-quick-search-input" 
                       placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                       autocomplete="off">
                <div class="hp-quick-search-suggestions"></div>
                <button type="submit" class="hp-quick-search-button">
                    <?php echo esc_html($atts['button_text']); ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
