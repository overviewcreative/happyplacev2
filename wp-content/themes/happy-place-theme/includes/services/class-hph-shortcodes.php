<?php
/**
 * Shortcodes Service
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HPH_Shortcodes
 */
class HPH_Shortcodes implements HPH_Service {
    
    /**
     * Initialize service
     */
    public function init() {
        $this->register_shortcodes();
    }
    
    /**
     * Get service identifier
     */
    public function get_service_id() {
        return 'shortcodes';
    }
    
    /**
     * Check if service is active
     */
    public function is_active() {
        return true;
    }
    
    /**
     * Get service dependencies
     */
    public function get_dependencies() {
        return array();
    }
    
    /**
     * Register shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('hph_listings', array($this, 'listings_shortcode'));
        add_shortcode('hph_search', array($this, 'search_shortcode'));
        add_shortcode('hph_featured_listings', array($this, 'featured_listings_shortcode'));
        add_shortcode('hph_agent_card', array($this, 'agent_card_shortcode'));
        add_shortcode('hph_mortgage_calculator', array($this, 'mortgage_calculator_shortcode'));
    }
    
    /**
     * Listings shortcode
     */
    public function listings_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 6,
            'type' => '',
            'featured' => false,
            'agent' => '',
            'columns' => 3
        ), $atts);
        
        $query_args = array(
            'post_type' => 'listing',
            'posts_per_page' => intval($atts['limit']),
            'meta_query' => array()
        );
        
        if ($atts['featured']) {
            $query_args['meta_query'][] = array(
                'key' => 'featured_listing',
                'value' => '1',
                'compare' => '='
            );
        }
        
        if ($atts['type']) {
            $query_args['meta_query'][] = array(
                'key' => 'property_type',
                'value' => $atts['type'],
                'compare' => '='
            );
        }
        
        $listings = new WP_Query($query_args);
        
        ob_start();
        if ($listings->have_posts()) {
            echo '<div class="hph-listings-grid hph-columns-' . esc_attr($atts['columns']) . '">';
            while ($listings->have_posts()) {
                $listings->the_post();
                get_template_part('template-parts/listing', 'card');
            }
            echo '</div>';
        }
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Search shortcode
     */
    public function search_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style' => 'horizontal',
            'fields' => 'location,type,price,bedrooms,bathrooms'
        ), $atts);
        
        ob_start();
        get_template_part('template-parts/search', 'form', $atts);
        return ob_get_clean();
    }
    
    /**
     * Featured listings shortcode
     */
    public function featured_listings_shortcode($atts) {
        $atts['featured'] = true;
        return $this->listings_shortcode($atts);
    }
    
    /**
     * Agent card shortcode
     */
    public function agent_card_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'style' => 'card'
        ), $atts);
        
        if (!$atts['id']) {
            return '';
        }
        
        $agent = get_post($atts['id']);
        if (!$agent || $agent->post_type !== 'agent') {
            return '';
        }
        
        ob_start();
        set_query_var('agent', $agent);
        set_query_var('style', $atts['style']);
        get_template_part('template-parts/agent', 'card');
        return ob_get_clean();
    }
    
    /**
     * Mortgage calculator shortcode
     */
    public function mortgage_calculator_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style' => 'compact'
        ), $atts);
        
        wp_enqueue_script('hph-mortgage-calculator');
        
        ob_start();
        get_template_part('template-parts/mortgage', 'calculator', $atts);
        return ob_get_clean();
    }
}
