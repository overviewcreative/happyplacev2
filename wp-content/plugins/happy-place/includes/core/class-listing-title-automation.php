<?php
/**
 * Listing Title Automation Class
 * 
 * Handles automatic title generation and nested URL structure for listings
 * 
 * @package HappyPlace\Core
 * @version 1.0.0
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ListingTitleAutomation Class
 */
class ListingTitleAutomation {
    
    /**
     * Single instance
     */
    private static ?ListingTitleAutomation $instance = null;
    
    /**
     * Street type abbreviations mapping
     */
    private array $street_type_abbreviations = [
        'street' => 'St',
        'avenue' => 'Ave',
        'boulevard' => 'Blvd',
        'drive' => 'Dr',
        'road' => 'Rd',
        'lane' => 'Ln',
        'court' => 'Ct',
        'place' => 'Pl',
        'way' => 'Way',
        'circle' => 'Cir',
        'plaza' => 'Plz',
        'terrace' => 'Ter',
        'trail' => 'Trl',
        'parkway' => 'Pkwy',
        'highway' => 'Hwy',
        'square' => 'Sq',
        'loop' => 'Loop',
        'ridge' => 'Rdg',
        'creek' => 'Crk',
        'point' => 'Pt',
        'crossing' => 'Xing',
        'commons' => 'Cmns',
        'center' => 'Ctr',
        'heights' => 'Hts',
        'village' => 'Vlg',
        'junction' => 'Jct',
        'meadows' => 'Mdws',
        'gardens' => 'Gdns',
        'grove' => 'Grv',
        'valley' => 'Vly',
        'estates' => 'Est',
        'manor' => 'Mnr',
        'hills' => 'Hls'
    ];
    
    /**
     * Get instance
     */
    public static function get_instance(): ListingTitleAutomation {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks(): void {
        // Auto-generate title when post is saved
        add_action('acf/save_post', [$this, 'auto_generate_listing_title'], 20);
        
        // Add rewrite rules for nested URLs
        add_action('init', [$this, 'add_listing_rewrite_rules'], 20);
        
        // Modify permalink structure
        add_filter('post_type_link', [$this, 'custom_listing_permalink'], 10, 2);
        
        // Handle query vars for nested URLs
        add_filter('query_vars', [$this, 'add_listing_query_vars']);
        
        // Parse custom query
        add_action('parse_query', [$this, 'parse_listing_query']);
        
        hp_log('Listing Title Automation initialized', 'info', 'TITLE_AUTO');
    }
    
    /**
     * Auto-generate listing title from ACF fields
     */
    public function auto_generate_listing_title($post_id): void {
        // Only process listing post type
        if (get_post_type($post_id) !== 'listing') {
            return;
        }
        
        // Avoid infinite loops
        remove_action('acf/save_post', [$this, 'auto_generate_listing_title'], 20);
        
        $generated_title = $this->build_listing_title($post_id);
        
        if (!empty($generated_title)) {
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $generated_title
            ]);
            
            hp_log("Auto-generated title for listing {$post_id}: {$generated_title}", 'info', 'TITLE_AUTO');
        }
        
        // Re-add the hook
        add_action('acf/save_post', [$this, 'auto_generate_listing_title'], 20);
    }
    
    /**
     * Build listing title from address components
     */
    public function build_listing_title($post_id): string {
        $street_number = get_field('street_number', $post_id);
        $street_name = get_field('street_name', $post_id);
        $street_type = get_field('street_type', $post_id); // Corrected field name
        
        if (empty($street_number) || empty($street_name)) {
            return '';
        }
        
        // Abbreviate street type if it's a full word
        $abbreviated_type = $this->abbreviate_street_type($street_type);
        
        // Build title: "123 Main St"
        $title_parts = array_filter([
            $street_number,
            $street_name,
            $abbreviated_type
        ]);
        
        return implode(' ', $title_parts);
    }
    
    /**
     * Abbreviate street type
     */
    public function abbreviate_street_type($street_type): string {
        if (empty($street_type)) {
            return '';
        }
        
        $lower_type = strtolower(trim($street_type));
        
        // If it's already abbreviated, return as-is
        if (in_array($street_type, array_values($this->street_type_abbreviations))) {
            return $street_type;
        }
        
        // Return abbreviated version if found
        if (isset($this->street_type_abbreviations[$lower_type])) {
            return $this->street_type_abbreviations[$lower_type];
        }
        
        // Return original if no abbreviation found
        return $street_type;
    }
    
    /**
     * Add custom rewrite rules for nested listing URLs
     */
    public function add_listing_rewrite_rules(): void {
        // Add nested rewrite rules for listings
        // Format: /listings/{status}/{property_type}/{city}/{state}/{listing_name}
        
        add_rewrite_rule(
            '^listings/([^/]+)/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$',
            'index.php?post_type=listing&listing_status=$matches[1]&listing_property_type=$matches[2]&listing_city=$matches[3]&listing_state=$matches[4]&name=$matches[5]',
            'top'
        );
        
        // Add simpler rules for partial matches
        add_rewrite_rule(
            '^listings/([^/]+)/([^/]+)/([^/]+)/?$',
            'index.php?post_type=listing&listing_status=$matches[1]&listing_property_type=$matches[2]&listing_city=$matches[3]',
            'top'
        );
        
        add_rewrite_rule(
            '^listings/([^/]+)/([^/]+)/?$',
            'index.php?post_type=listing&listing_status=$matches[1]&listing_property_type=$matches[2]',
            'top'
        );
        
        add_rewrite_rule(
            '^listings/([^/]+)/?$',
            'index.php?post_type=listing&listing_status=$matches[1]',
            'top'
        );
        
        hp_log('Listing rewrite rules added', 'debug', 'TITLE_AUTO');
    }
    
    /**
     * Add query vars for custom listing URLs
     */
    public function add_listing_query_vars($vars): array {
        $vars[] = 'listing_status';
        $vars[] = 'listing_property_type';
        $vars[] = 'listing_city';
        $vars[] = 'listing_state';
        return $vars;
    }
    
    /**
     * Parse custom listing query
     */
    public function parse_listing_query($query): void {
        if (!is_admin() && $query->is_main_query()) {
            $status = get_query_var('listing_status');
            $property_type = get_query_var('listing_property_type');
            $city = get_query_var('listing_city');
            $state = get_query_var('listing_state');
            
            if ($status || $property_type || $city || $state) {
                $query->set('post_type', 'listing');
                
                $meta_query = [];
                $tax_query = [];
                
                // Add status filter
                if ($status) {
                    $tax_query[] = [
                        'taxonomy' => 'property_status',
                        'field' => 'slug',
                        'terms' => $status
                    ];
                }
                
                // Add property type filter
                if ($property_type) {
                    $tax_query[] = [
                        'taxonomy' => 'property_type',
                        'field' => 'slug',
                        'terms' => $property_type
                    ];
                }
                
                // Add city filter
                if ($city) {
                    $meta_query[] = [
                        'key' => 'city',
                        'value' => str_replace('-', ' ', $city),
                        'compare' => 'LIKE'
                    ];
                }
                
                // Add state filter
                if ($state) {
                    $meta_query[] = [
                        'key' => 'state',
                        'value' => strtoupper($state),
                        'compare' => '='
                    ];
                }
                
                if (!empty($tax_query)) {
                    $tax_query['relation'] = 'AND';
                    $query->set('tax_query', $tax_query);
                }
                
                if (!empty($meta_query)) {
                    $meta_query['relation'] = 'AND';
                    $query->set('meta_query', $meta_query);
                }
            }
        }
    }
    
    /**
     * Custom permalink structure for listings
     */
    public function custom_listing_permalink($post_link, $post): string {
        if ($post->post_type !== 'listing') {
            return $post_link;
        }
        
        // Get listing data
        $status_terms = get_the_terms($post->ID, 'property_status');
        $type_terms = get_the_terms($post->ID, 'property_type');
        $city = get_field('city', $post->ID);
        $state = get_field('state', $post->ID);
        
        $status_slug = $status_terms && !is_wp_error($status_terms) ? $status_terms[0]->slug : 'active';
        $type_slug = $type_terms && !is_wp_error($type_terms) ? $type_terms[0]->slug : 'single-family';
        $city_slug = $city ? sanitize_title($city) : 'unknown-city';
        $state_slug = $state ? strtolower($state) : 'unknown-state';
        
        // Build nested URL
        $nested_url = sprintf(
            '/listings/%s/%s/%s/%s/%s/',
            $status_slug,
            $type_slug,
            $city_slug,
            $state_slug,
            $post->post_name
        );
        
        return home_url($nested_url);
    }
    
    /**
     * Get street type abbreviations array
     */
    public function get_street_type_abbreviations(): array {
        return $this->street_type_abbreviations;
    }
    
    /**
     * Flush rewrite rules (call this when activating the plugin)
     */
    public function flush_rewrite_rules(): void {
        $this->add_listing_rewrite_rules();
        flush_rewrite_rules();
        hp_log('Rewrite rules flushed for listing automation', 'info', 'TITLE_AUTO');
    }
}
