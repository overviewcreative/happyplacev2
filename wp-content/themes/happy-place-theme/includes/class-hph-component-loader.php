<?php
/**
 * Component Loader Class - Organized component loading system
 *
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HPH_Component_Loader
 */
class HPH_Component_Loader {
    
    /**
     * Component registry
     * @var array
     */
    private static $components = array();
    
    /**
     * Base component directory
     * @var string
     */
    private static $base_dir = '';
    
    /**
     * Initialize the component loader
     */
    public static function init() {
        self::$base_dir = HPH_TEMPLATE_DIR . '/';
        self::register_components();
        
        // Add WordPress hooks
        add_action('wp_loaded', array(__CLASS__, 'register_shortcodes'));
    }
    
    /**
     * Register all available components
     */
    private static function register_components() {
        self::$components = array(
            // Base Components
            'stat-card' => array(
                'path' => 'base/stat-card',
                'name' => 'Stat Card',
                'description' => 'Statistics display card with customizable styling',
                'category' => 'base',
                'args' => array('title', 'value', 'subtitle', 'icon', 'color', 'format')
            ),
            'data-table' => array(
                'path' => 'base/data-table',
                'name' => 'Data Table',
                'description' => 'Responsive data table with search and sorting',
                'category' => 'base',
                'args' => array('title', 'columns', 'data', 'pagination', 'search')
            ),
            'dashboard-form' => array(
                'path' => 'base/dashboard-form',
                'name' => 'Dashboard Form',
                'description' => 'Reusable form container with AJAX support',
                'category' => 'base',
                'args' => array('title', 'fields', 'action', 'method')
            ),
            'dashboard-layout' => array(
                'path' => 'base/dashboard-layout',
                'name' => 'Dashboard Layout',
                'description' => 'Flexible layout container for dashboard content',
                'category' => 'base',
                'args' => array('title', 'layout', 'breadcrumbs', 'actions')
            ),
            'dashboard-widget' => array(
                'path' => 'base/dashboard-widget',
                'name' => 'Dashboard Widget',
                'description' => 'Widget container with header and controls',
                'category' => 'base',
                'args' => array('title', 'icon', 'collapsible', 'actions')
            ),
            'dashboard-chart' => array(
                'path' => 'base/dashboard-chart',
                'name' => 'Dashboard Chart',
                'description' => 'Chart.js wrapper for data visualization',
                'category' => 'base',
                'args' => array('type', 'data', 'labels', 'title')
            ),
            'dashboard-map' => array(
                'path' => 'base/dashboard-map',
                'name' => 'Dashboard Map',
                'description' => 'Mapbox GL wrapper for interactive maps',
                'category' => 'base',
                'args' => array('center', 'zoom', 'markers', 'style')
            ),
            'card' => array(
                'path' => 'base/card',
                'name' => 'Base Card',
                'description' => 'Flexible card layout for any post type',
                'category' => 'base',
                'args' => array('post_id', 'post_type', 'layout', 'show_image', 'show_actions')
            ),
            'card-grid' => array(
                'path' => 'base/card-grid',
                'name' => 'Card Grid',
                'description' => 'Grid layout for displaying multiple cards',
                'category' => 'base',
                'args' => array('posts', 'columns', 'card_args', 'search_form', 'filter_controls')
            ),
            'card-list' => array(
                'path' => 'base/card-list',
                'name' => 'Card List',
                'description' => 'List layout for displaying cards vertically',
                'category' => 'base',
                'args' => array('posts', 'card_args', 'spacing', 'dividers', 'search_form')
            ),
            'card-map' => array(
                'path' => 'base/card-map',
                'name' => 'Card Map',
                'description' => 'Map layout with cards as popups or sidebar',
                'category' => 'base',
                'args' => array('posts', 'layout', 'sidebar_position', 'map_height', 'clustering')
            ),
            
            // Listing Components
            'listing-card' => array(
                'path' => 'components/listing/listing-card',
                'name' => 'Listing Card',
                'description' => 'Property listing card display',
                'category' => 'listing',
                'args' => array('listing_id', 'layout', 'show_agent')
            ),
            'listings-grid' => array(
                'path' => 'components/listing/listings-grid',
                'name' => 'Listings Grid',
                'description' => 'Grid display of property listings with search and filters',
                'category' => 'listing',
                'args' => array('posts', 'columns', 'show_search', 'show_filters', 'card_layout')
            ),
            'listings-list' => array(
                'path' => 'components/listing/listings-list',
                'name' => 'Listings List',
                'description' => 'List display of property listings',
                'category' => 'listing',
                'args' => array('posts', 'show_search', 'show_filters', 'show_excerpts')
            ),
            'listings-map' => array(
                'path' => 'components/listing/listings-map',
                'name' => 'Listings Map',
                'description' => 'Map display of property listings with interactive markers',
                'category' => 'listing',
                'args' => array('posts', 'layout', 'sidebar_position', 'clustering')
            ),
            'listing-hero' => array(
                'path' => 'components/listing/listing-hero',
                'name' => 'Listing Hero',
                'description' => 'Property hero section with images',
                'category' => 'listing',
                'args' => array('listing_id', 'show_gallery')
            ),
            'listing-details' => array(
                'path' => 'components/listing/listing-details',
                'name' => 'Listing Details',
                'description' => 'Property details and specifications',
                'category' => 'listing',
                'args' => array('listing_id', 'sections')
            ),
            'listing-map' => array(
                'path' => 'components/listing/listing-map',
                'name' => 'Listing Map',
                'description' => 'Property location map',
                'category' => 'listing',
                'args' => array('listing_id', 'height', 'zoom')
            ),
            'listing-contact-form' => array(
                'path' => 'components/listing/listing-contact-form',
                'name' => 'Listing Contact Form',
                'description' => 'Contact form for property inquiries',
                'category' => 'listing',
                'args' => array('listing_id', 'agent_id')
            ),
            'listing-gallery' => array(
                'path' => 'components/listing/listing-photo-gallery',
                'name' => 'Listing Gallery',
                'description' => 'Property photo gallery with lightbox',
                'category' => 'listing',
                'args' => array('images', 'listing_id', 'style', 'show_thumbnails', 'lightbox')
            ),
            'listing-features' => array(
                'path' => 'components/listing/listing-features',
                'name' => 'Listing Features',
                'description' => 'Property features and amenities display',
                'category' => 'listing',
                'args' => array('features', 'listing_id', 'style')
            ),
            'listing-floor-plans' => array(
                'path' => 'components/listing/listing-floor-plans',
                'name' => 'Listing Floor Plans',
                'description' => 'Property floor plans display',
                'category' => 'listing',
                'args' => array('floor_plans', 'listing_id')
            ),
            'listing-virtual-tour' => array(
                'path' => 'components/listing/listing-virtual-tour',
                'name' => 'Listing Virtual Tour',
                'description' => 'Virtual tour integration',
                'category' => 'listing',
                'args' => array('virtual_tour', 'listing_id')
            ),
            'mortgage-calculator' => array(
                'path' => 'components/listing/listing-mortgage-calculator',
                'name' => 'Mortgage Calculator',
                'description' => 'Property mortgage calculator',
                'category' => 'listing',
                'args' => array('listing_price', 'listing_id', 'style')
            ),
            'neighborhood-info' => array(
                'path' => 'components/listing/listing-neighborhood-info',
                'name' => 'Neighborhood Info',
                'description' => 'Neighborhood information display',
                'category' => 'listing',
                'args' => array('neighborhood', 'listing_location')
            ),
            'property-actions' => array(
                'path' => 'components/listing/listing-property-actions',
                'name' => 'Property Actions',
                'description' => 'Property action buttons (favorite, share, etc)',
                'category' => 'listing',
                'args' => array('listing_id', 'listing_data', 'actions')
            ),
            'schedule-showing-form' => array(
                'path' => 'components/listing/listing-schedule-showing',
                'name' => 'Schedule Showing Form',
                'description' => 'Form to schedule property showing',
                'category' => 'listing',
                'args' => array('listing_id', 'agent_data')
            ),
            'listing-header' => array(
                'path' => 'components/listing/listing-header',
                'name' => 'Listing Header',
                'description' => 'Property header with title, price, and basic info',
                'category' => 'listing',
                'args' => array('listing_data', 'show_price', 'show_status', 'show_address', 'show_mls', 'show_actions')
            ),
            
            // Agent Components
            'agent-card' => array(
                'path' => 'components/agent/agent-card',
                'name' => 'Agent Card',
                'description' => 'Agent profile card with contact info',
                'category' => 'agent',
                'args' => array('agent_id', 'layout', 'show_stats')
            ),
            'agents-grid' => array(
                'path' => 'components/agent/agents-grid',
                'name' => 'Agents Grid',
                'description' => 'Grid display of agent profiles with search and contact options',
                'category' => 'agent',
                'args' => array('posts', 'columns', 'show_search', 'show_stats', 'show_contact')
            ),
            'agent-contact-form' => array(
                'path' => 'components/agent/agent-contact-form',
                'name' => 'Agent Contact Form',
                'description' => 'Contact form for agent inquiries',
                'category' => 'agent',
                'args' => array('agent_data', 'form_style', 'background')
            ),
            
            // Open House Components
            'open-house-card' => array(
                'path' => 'components/open-house/open-house-card',
                'name' => 'Open House Card',
                'description' => 'Open house event card',
                'category' => 'open-house',
                'args' => array('open_house_id', 'show_rsvp')
            ),
            'open-house-widget' => array(
                'path' => 'components/open-house/open-house-widget',
                'name' => 'Open House Widget',
                'description' => 'Upcoming open houses widget',
                'category' => 'open-house',
                'args' => array('limit', 'show_rsvp')
            ),
            'open-houses-grid' => array(
                'path' => 'components/open-house/open-houses-grid',
                'name' => 'Open Houses Grid',
                'description' => 'Grid display of open house events with RSVP options',
                'category' => 'open-house',
                'args' => array('posts', 'columns', 'show_filters', 'show_rsvp')
            ),
            'transactions-list' => array(
                'path' => 'components/transaction/transactions-list',
                'name' => 'Transactions List',
                'description' => 'List display of transaction cards with commission visibility controls',
                'category' => 'transaction',
                'args' => array('posts', 'agent_id', 'show_search', 'show_filters', 'show_commission')
            ),
            
            // Transaction Components
            'transaction-dashboard' => array(
                'path' => 'components/transaction/transaction-dashboard',
                'name' => 'Transaction Dashboard',
                'description' => 'Agent deal pipeline dashboard',
                'category' => 'transaction',
                'args' => array('agent_id', 'show_stats')
            ),
            'transaction-status' => array(
                'path' => 'components/transaction/transaction-status',
                'name' => 'Transaction Status',
                'description' => 'Transaction progress tracker',
                'category' => 'transaction',
                'args' => array('transaction_id', 'show_timeline')
            ),
            
            // Analytics Components
            'real-estate-charts' => array(
                'path' => 'components/analytics/real-estate-charts',
                'name' => 'Real Estate Charts',
                'description' => 'Pre-configured real estate data charts',
                'category' => 'analytics',
                'args' => array('chart_type', 'agent_id', 'timeframe')
            ),
            
            // Form Components
            'advanced-search-form' => array(
                'path' => 'components/forms/advanced-search-form',
                'name' => 'Advanced Search Form',
                'description' => 'Advanced property search form',
                'category' => 'forms',
                'args' => array('layout', 'fields', 'action')
            ),
            
            // UI Components
            'faq-accordion' => array(
                'path' => 'components/ui/faq-accordion',
                'name' => 'FAQ Accordion',
                'description' => 'Interactive FAQ accordion with search functionality',
                'category' => 'ui',
                'args' => array('faqs', 'allow_multiple_open', 'search_enabled', 'headline')
            ),
            'features-grid' => array(
                'path' => 'components/ui/features-grid',
                'name' => 'Features Grid',
                'description' => 'Grid display of features with icons',
                'category' => 'ui',
                'args' => array('features', 'columns', 'show_icons')
            ),
            'stats-counter' => array(
                'path' => 'components/ui/stats-counter',
                'name' => 'Stats Counter',
                'description' => 'Animated statistics counter',
                'category' => 'ui',
                'args' => array('stats', 'animate', 'columns')
            ),
            'testimonials-carousel' => array(
                'path' => 'components/ui/testimonials-carousel',
                'name' => 'Testimonials Carousel',
                'description' => 'Sliding testimonials carousel',
                'category' => 'ui',
                'args' => array('testimonials', 'autoplay', 'show_navigation')
            ),
            
            // Layout Templates
            'archive-layout' => array(
                'path' => 'layout/archive-layout',
                'name' => 'Archive Layout',
                'description' => 'Main archive template structure with header, controls, and content',
                'category' => 'layout',
                'args' => array('post_type', 'title', 'view_modes', 'show_sidebar', 'layout')
            ),
            'archive-header' => array(
                'path' => 'layout/archive-header',
                'name' => 'Archive Header',
                'description' => 'Archive title, description, counts, and breadcrumbs',
                'category' => 'layout',
                'args' => array('title', 'description', 'total_results', 'show_breadcrumbs', 'background')
            ),
            'archive-controls' => array(
                'path' => 'layout/archive-controls',
                'name' => 'Archive Controls',
                'description' => 'Search, filters, view switcher, and sort options',
                'category' => 'layout',
                'args' => array('view_modes', 'current_view', 'show_search_toggle', 'sort_options')
            ),
            'archive-filters' => array(
                'path' => 'layout/archive-filters',
                'name' => 'Archive Filters',
                'description' => 'Active filters display with remove functionality',
                'category' => 'layout',
                'args' => array('show_active_filters', 'show_clear_all', 'filter_labels')
            ),
            'archive-no-results' => array(
                'path' => 'layout/archive-no-results',
                'name' => 'Archive No Results',
                'description' => 'No results state with contextual CTAs',
                'category' => 'layout',
                'args' => array('post_type', 'icon', 'title', 'message', 'custom_actions')
            ),
            'pagination' => array(
                'path' => 'layout/pagination',
                'name' => 'Pagination',
                'description' => 'Consistent pagination across all archives',
                'category' => 'layout',
                'args' => array('query', 'current_page', 'prev_text', 'next_text', 'class')
            ),
            'single-layout' => array(
                'path' => 'layout/single-layout',
                'name' => 'Single Layout',
                'description' => 'Main single post template structure',
                'category' => 'layout',
                'args' => array('post_id', 'show_hero', 'show_sidebar', 'show_related', 'layout')
            ),
            'single-hero' => array(
                'path' => 'layout/single-hero',
                'name' => 'Single Hero',
                'description' => 'Hero section with images, gallery, or minimal display',
                'category' => 'layout',
                'args' => array('post_id', 'style', 'height', 'show_gallery', 'overlay')
            ),
            'single-content' => array(
                'path' => 'layout/single-content',
                'name' => 'Single Content',
                'description' => 'Main content sections wrapper with post-type specific sections',
                'category' => 'layout',
                'args' => array('post_id', 'sections', 'show_content', 'show_meta', 'show_sharing')
            ),
            'single-sidebar' => array(
                'path' => 'layout/single-sidebar',
                'name' => 'Single Sidebar',
                'description' => 'Sidebar with related info, CTAs, and widgets',
                'category' => 'layout',
                'args' => array('post_id', 'widgets', 'show_related_posts', 'show_contact_cta')
            ),
            'single-cta' => array(
                'path' => 'layout/single-cta',
                'name' => 'Single CTA',
                'description' => 'Call-to-action sections with post-type specific content',
                'category' => 'layout',
                'args' => array('post_id', 'style', 'layout', 'headline', 'buttons')
            ),
            'single-related' => array(
                'path' => 'layout/single-related',
                'name' => 'Single Related',
                'description' => 'Related items section with grid, list, or carousel layouts',
                'category' => 'layout',
                'args' => array('post_id', 'count', 'columns', 'layout', 'card_style')
            ),
            'breadcrumbs' => array(
                'path' => 'layout/breadcrumbs',
                'name' => 'Breadcrumbs',
                'description' => 'Site navigation breadcrumbs with contextual hierarchy',
                'category' => 'layout',
                'args' => array('post_id', 'separator', 'home_text', 'show_current')
            ),
            
            // Content Components
            'content-none' => array(
                'path' => 'content-none',
                'name' => 'No Content',
                'description' => 'No content found message',
                'category' => 'content',
                'args' => array('message', 'show_search')
            )
        );
    }
    
    /**
     * Load a component by name
     * 
     * @param string $component Component name
     * @param array $args Component arguments
     * @param bool $echo Whether to echo output or return it
     * @return string|void
     */
    public static function load_component($component, $args = array(), $echo = true) {
        if (!isset(self::$components[$component])) {
            if (HPH_DEV_MODE) {
                error_log("HPH Component Loader: Component '{$component}' not found");
            }
            return $echo ? '' : false;
        }
        
        $component_data = self::$components[$component];
        $component_path = self::$base_dir . $component_data['path'] . '.php';
        
        if (!file_exists($component_path)) {
            if (HPH_DEV_MODE) {
                error_log("HPH Component Loader: Component file not found: {$component_path}");
            }
            return $echo ? '' : false;
        }
        
        // Set args for the component
        set_query_var('args', $args);
        
        if ($echo) {
            include $component_path;
        } else {
            ob_start();
            include $component_path;
            return ob_get_clean();
        }
    }
    
    /**
     * Get component info
     * 
     * @param string $component Component name
     * @return array|false
     */
    public static function get_component_info($component) {
        return isset(self::$components[$component]) ? self::$components[$component] : false;
    }
    
    /**
     * Get all components
     * 
     * @param string $category Optional category filter
     * @return array
     */
    public static function get_components($category = '') {
        if (empty($category)) {
            return self::$components;
        }
        
        return array_filter(self::$components, function($component) use ($category) {
            return $component['category'] === $category;
        });
    }
    
    /**
     * Get components by category
     * 
     * @return array
     */
    public static function get_components_by_category() {
        $categorized = array();
        
        foreach (self::$components as $name => $component) {
            $category = $component['category'];
            if (!isset($categorized[$category])) {
                $categorized[$category] = array();
            }
            $categorized[$category][$name] = $component;
        }
        
        return $categorized;
    }
    
    /**
     * Register component shortcodes
     */
    public static function register_shortcodes() {
        foreach (self::$components as $name => $component) {
            $shortcode_name = 'hph_' . str_replace('-', '_', $name);
            add_shortcode($shortcode_name, function($atts) use ($name) {
                return self::load_component($name, (array) $atts, false);
            });
        }
    }
    
    /**
     * Magic method to load components as static methods
     * 
     * @param string $name
     * @param array $arguments
     * @return string|void
     */
    public static function __callStatic($name, $arguments) {
        $component_name = str_replace('_', '-', $name);
        $args = isset($arguments[0]) ? (array) $arguments[0] : array();
        $echo = isset($arguments[1]) ? (bool) $arguments[1] : true;
        
        return self::load_component($component_name, $args, $echo);
    }
}

/**
 * Helper function to load components
 * 
 * @param string $component Component name
 * @param array $args Component arguments
 * @param bool $echo Whether to echo output or return it
 * @return string|void
 */
function hph_component($component, $args = array(), $echo = true) {
    return HPH_Component_Loader::load_component($component, $args, $echo);
}

/**
 * Helper function to get component info
 * 
 * @param string $component Component name
 * @return array|false
 */
function hph_component_info($component) {
    return HPH_Component_Loader::get_component_info($component);
}

/**
 * Helper function to get all components
 * 
 * @param string $category Optional category filter
 * @return array
 */
function hph_components($category = '') {
    return HPH_Component_Loader::get_components($category);
}