<?php
/**
 * Component Loader Class - Organized component loading system
 * Updated with all new base components and sections
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
        self::$base_dir = get_template_directory() . '/template-parts/';
        self::register_components();
        
        // Add WordPress hooks
        add_action('wp_loaded', array(__CLASS__, 'register_shortcodes'));
    }
    
    /**
     * Register all available components
     * Updated to match actual file structure - November 2024
     */
    private static function register_components() {
        self::$components = array(
            
            // =====================================
            // BASE UI COMPONENTS (Actual Files)
            // =====================================
            
            'accordion' => array(
                'path' => 'base/accordion',
                'name' => 'Accordion',
                'description' => 'Collapsible content sections',
                'category' => 'base',
                'args' => array('items', 'variant', 'multiple', 'default_open')
            ),
            
            'alert' => array(
                'path' => 'base/alert',
                'name' => 'Alert',
                'description' => 'Alert and notification component',
                'category' => 'base',
                'args' => array('title', 'message', 'variant', 'dismissible', 'icon')
            ),
            
            'avatar' => array(
                'path' => 'base/avatar',
                'name' => 'Avatar',
                'description' => 'User profile image component',
                'category' => 'base',
                'args' => array('src', 'name', 'size', 'variant', 'status')
            ),
            
            'badge' => array(
                'path' => 'base/badge',
                'name' => 'Badge',
                'description' => 'Status indicators and labels',
                'category' => 'base',
                'args' => array('text', 'variant', 'size', 'icon')
            ),
            
            'breadcrumbs' => array(
                'path' => 'base/breadcrumbs',
                'name' => 'Breadcrumbs',
                'description' => 'Navigation trail component',
                'category' => 'base',
                'args' => array('items', 'separator', 'show_home')
            ),
            
            'button' => array(
                'path' => 'base/button',
                'name' => 'Button',
                'description' => 'Button component with variations',
                'category' => 'base',
                'args' => array('text', 'variant', 'size', 'icon', 'href')
            ),
            
            'card' => array(
                'path' => 'base/card',
                'name' => 'Card',
                'description' => 'Flexible card component',
                'category' => 'base',
                'args' => array('variant', 'layout', 'image', 'title', 'description')
            ),
            
            'card-grid' => array(
                'path' => 'base/card-grid',
                'name' => 'Card Grid',
                'description' => 'Grid layout for cards',
                'category' => 'base',
                'args' => array('columns', 'gap', 'items')
            ),
            
            'carousel' => array(
                'path' => 'base/carousel',
                'name' => 'Carousel',
                'description' => 'Image and content slider',
                'category' => 'base',
                'args' => array('items', 'type', 'navigation', 'pagination')
            ),
            
            'checkbox' => array(
                'path' => 'base/checkbox',
                'name' => 'Checkbox',
                'description' => 'Checkbox input component',
                'category' => 'base',
                'args' => array('name', 'label', 'checked', 'disabled')
            ),
            
            'chip' => array(
                'path' => 'base/chip',
                'name' => 'Chip',
                'description' => 'Chip/tag component',
                'category' => 'base',
                'args' => array('text', 'variant', 'removable', 'icon')
            ),
            
            'content-none' => array(
                'path' => 'base/content-none',
                'name' => 'Content None',
                'description' => 'No content found component',
                'category' => 'base',
                'args' => array('context', 'title', 'message', 'show_search')
            ),
            
            'dropdown' => array(
                'path' => 'base/dropdown',
                'name' => 'Dropdown',
                'description' => 'Dropdown menu component',
                'category' => 'base',
                'args' => array('trigger', 'items', 'placement')
            ),
            
            'empty-state' => array(
                'path' => 'base/empty-state',
                'name' => 'Empty State',
                'description' => 'Empty state component',
                'category' => 'base',
                'args' => array('title', 'description', 'action', 'icon')
            ),
            
            'form-input' => array(
                'path' => 'base/form-input',
                'name' => 'Form Input',
                'description' => 'Form input component',
                'category' => 'base',
                'args' => array('type', 'name', 'label', 'placeholder')
            ),
            
            'grid' => array(
                'path' => 'base/grid',
                'name' => 'Grid',
                'description' => 'Grid layout component',
                'category' => 'base',
                'args' => array('columns', 'gap', 'items')
            ),
            
            'icon' => array(
                'path' => 'base/icon',
                'name' => 'Icon',
                'description' => 'Icon component',
                'category' => 'base',
                'args' => array('name', 'size', 'color')
            ),
            
            'modal' => array(
                'path' => 'base/modal',
                'name' => 'Modal',
                'description' => 'Modal dialog component',
                'category' => 'base',
                'args' => array('title', 'content', 'size', 'closable')
            ),
            
            'navigation' => array(
                'path' => 'base/navigation',
                'name' => 'Navigation',
                'description' => 'Navigation component',
                'category' => 'base',
                'args' => array('items', 'type', 'layout')
            ),
            
            'pagination' => array(
                'path' => 'base/pagination',
                'name' => 'Pagination',
                'description' => 'Pagination component',
                'category' => 'base',
                'args' => array('current_page', 'total_pages', 'variant')
            ),
            
            'progress' => array(
                'path' => 'base/progress',
                'name' => 'Progress',
                'description' => 'Progress indicator component',
                'category' => 'base',
                'args' => array('value', 'max', 'type', 'variant')
            ),
            
            'radio' => array(
                'path' => 'base/radio',
                'name' => 'Radio',
                'description' => 'Radio button component',
                'category' => 'base',
                'args' => array('name', 'options', 'selected')
            ),
            
            'rating' => array(
                'path' => 'base/rating',
                'name' => 'Rating',
                'description' => 'Star rating component',
                'category' => 'base',
                'args' => array('value', 'max', 'interactive')
            ),
            
            'search' => array(
                'path' => 'base/search',
                'name' => 'Search',
                'description' => 'Search input component',
                'category' => 'base',
                'args' => array('placeholder', 'suggestions', 'instant')
            ),
            
            'select' => array(
                'path' => 'base/select',
                'name' => 'Select',
                'description' => 'Select dropdown component',
                'category' => 'base',
                'args' => array('name', 'options', 'placeholder', 'multiple')
            ),
            
            'skeleton' => array(
                'path' => 'base/skeleton',
                'name' => 'Skeleton',
                'description' => 'Loading skeleton component',
                'category' => 'base',
                'args' => array('type', 'width', 'height', 'lines')
            ),
            
            'slider' => array(
                'path' => 'base/slider',
                'name' => 'Slider',
                'description' => 'Range slider component',
                'category' => 'base',
                'args' => array('min', 'max', 'value', 'step')
            ),
            
            'stepper' => array(
                'path' => 'base/stepper',
                'name' => 'Stepper',
                'description' => 'Step indicator component',
                'category' => 'base',
                'args' => array('steps', 'current', 'variant')
            ),
            
            'table' => array(
                'path' => 'base/table',
                'name' => 'Table',
                'description' => 'Data table component',
                'category' => 'base',
                'args' => array('columns', 'rows', 'sortable', 'responsive')
            ),
            
            'tabs' => array(
                'path' => 'base/tabs',
                'name' => 'Tabs',
                'description' => 'Tab panel component',
                'category' => 'base',
                'args' => array('tabs', 'active', 'variant')
            ),
            
            'textarea' => array(
                'path' => 'base/textarea',
                'name' => 'Textarea',
                'description' => 'Textarea input component',
                'category' => 'base',
                'args' => array('name', 'placeholder', 'rows', 'label')
            ),
            
            'toggle' => array(
                'path' => 'base/toggle',
                'name' => 'Toggle',
                'description' => 'Toggle switch component',
                'category' => 'base',
                'args' => array('name', 'label', 'checked', 'disabled')
            ),
            
            'tooltip' => array(
                'path' => 'base/tooltip',
                'name' => 'Tooltip',
                'description' => 'Tooltip component',
                'category' => 'base',
                'args' => array('content', 'trigger', 'placement')
            ),
            
            // =====================================
            // SECTION COMPONENTS (Actual Files)
            // =====================================
            
            'hero' => array(
                'path' => 'sections/hero',
                'name' => 'Hero Section',
                'description' => 'Hero section with multiple styles',
                'category' => 'sections',
                'args' => array('style', 'height', 'background_image', 'headline', 'subheadline')
            ),
            
            'content' => array(
                'path' => 'sections/content',
                'name' => 'Content Section',
                'description' => 'Flexible content section',
                'category' => 'sections',
                'args' => array('layout', 'title', 'content', 'alignment')
            ),
            
            'cta' => array(
                'path' => 'sections/cta',
                'name' => 'CTA Section',
                'description' => 'Call-to-action section',
                'category' => 'sections',
                'args' => array('title', 'description', 'buttons', 'layout')
            ),
            
            'features' => array(
                'path' => 'sections/features',
                'name' => 'Features Section',
                'description' => 'Features showcase section',
                'category' => 'sections',
                'args' => array('features', 'layout', 'columns')
            ),
            
            'agents-loop' => array(
                'path' => 'sections/agents-loop',
                'name' => 'Agents Loop',
                'description' => 'Agent listings loop section',
                'category' => 'sections',
                'args' => array('query', 'layout', 'columns')
            ),
            
            'listings-loop' => array(
                'path' => 'sections/listings-loop',
                'name' => 'Listings Loop',
                'description' => 'Property listings loop section',
                'category' => 'sections',
                'args' => array('query', 'layout', 'columns')
            ),
            
            'section' => array(
                'path' => 'sections/section',
                'name' => 'Generic Section',
                'description' => 'Generic section wrapper',
                'category' => 'sections',
                'args' => array('content', 'background', 'spacing')
            ),
            
            // =====================================
            // LAYOUT COMPONENTS (Actual Files)
            // =====================================
            
            'archive-header' => array(
                'path' => 'layout/archive-header',
                'name' => 'Archive Header',
                'description' => 'Archive page header layout',
                'category' => 'layout',
                'args' => array('title', 'description', 'breadcrumbs')
            ),
            
            'archive-layout' => array(
                'path' => 'layout/archive-layout',
                'name' => 'Archive Layout',
                'description' => 'Archive page layout manager',
                'category' => 'layout',
                'args' => array('layout', 'show_sidebar', 'show_filters')
            ),
            
            'card-layout' => array(
                'path' => 'layout/card-layout',
                'name' => 'Card Layout',
                'description' => 'Card grid/list layout manager',
                'category' => 'layout',
                'args' => array('layout', 'items', 'columns')
            ),
            
            // =====================================
            // COMPONENT ADAPTERS (Actual Files)
            // =====================================
            
            // Listing Components
            'listing-card' => array(
                'path' => 'components/listing/card',
                'name' => 'Listing Card',
                'description' => 'Property listing card adapter',
                'category' => 'listing',
                'args' => array('listing_id', 'variant', 'layout')
            ),
            
            'listing-grid' => array(
                'path' => 'components/listing/grid',
                'name' => 'Listing Grid',
                'description' => 'Property listings grid layout',
                'category' => 'listing',
                'args' => array('query', 'columns', 'show_filters')
            ),
            
            'listing-hero' => array(
                'path' => 'components/listing/hero',
                'name' => 'Listing Hero',
                'description' => 'Property hero section',
                'category' => 'listing',
                'args' => array('listing_id', 'show_gallery', 'show_price')
            ),
            
            'listing-details' => array(
                'path' => 'components/listing/details',
                'name' => 'Listing Details',
                'description' => 'Property details section',
                'category' => 'listing',
                'args' => array('listing_id', 'sections')
            ),
            
            'listing-features' => array(
                'path' => 'components/listing/features',
                'name' => 'Listing Features',
                'description' => 'Property features section',
                'category' => 'listing',
                'args' => array('listing_id', 'layout')
            ),
            
            'listing-gallery' => array(
                'path' => 'components/listing/gallery',
                'name' => 'Listing Gallery',
                'description' => 'Property photo gallery',
                'category' => 'listing',
                'args' => array('listing_id', 'lightbox')
            ),
            
            'listing-contact-form' => array(
                'path' => 'components/listing/contact-form',
                'name' => 'Listing Contact Form',
                'description' => 'Property inquiry form',
                'category' => 'listing',
                'args' => array('listing_id', 'agent_id')
            ),
            
            // Agent Components  
            'agent-card' => array(
                'path' => 'components/agent/card',
                'name' => 'Agent Card',
                'description' => 'Agent profile card',
                'category' => 'agent',
                'args' => array('agent_id', 'layout', 'show_stats')
            ),
            
            'agents-grid' => array(
                'path' => 'components/agent/agents-grid',
                'name' => 'Agents Grid',
                'description' => 'Agent listings grid',
                'category' => 'agent',
                'args' => array('query', 'columns')
            ),
            
            // Search Components
            'search-form' => array(
                'path' => 'components/search/search-form',
                'name' => 'Search Form',
                'description' => 'Advanced search form',
                'category' => 'search',
                'args' => array('post_types', 'fields')
            ),
            
            'search-results' => array(
                'path' => 'components/search/search-results',
                'name' => 'Search Results',
                'description' => 'Search results display',
                'category' => 'search',
                'args' => array('query', 'layout')
            ),
            
            'search-filters' => array(
                'path' => 'components/search/search-filters',
                'name' => 'Search Filters',
                'description' => 'Advanced search filters',
                'category' => 'search',
                'args' => array('current_post_type', 'form_id')
            ),
            
            // Utility Components
            'advanced-filters' => array(
                'path' => 'components/advanced-filters',
                'name' => 'Advanced Filters',
                'description' => 'Advanced filtering interface',
                'category' => 'utility',
                'args' => array('post_type', 'fields')
            ),
            
            'archive-controls' => array(
                'path' => 'components/archive-controls',
                'name' => 'Archive Controls',
                'description' => 'Archive view controls',
                'category' => 'utility',
                'args' => array('layout_options', 'sort_options')
            ),
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
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("HPH Component Loader: Component '{$component}' not found");
            }
            return $echo ? '' : false;
        }
        
        $component_data = self::$components[$component];
        $component_path = self::$base_dir . $component_data['path'] . '.php';
        
        if (!file_exists($component_path)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("HPH Component Loader: Component file not found: {$component_path}");
            }
            return $echo ? '' : false;
        }
        
        // Set args for the component (using global for compatibility with hph_get_arg)
        $GLOBALS['hph_component_args'] = $args;
        
        if ($echo) {
            include $component_path;
        } else {
            ob_start();
            include $component_path;
            $output = ob_get_clean();
            unset($GLOBALS['hph_component_args']);
            return $output;
        }
        
        unset($GLOBALS['hph_component_args']);
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
        $categorized = array(
            'base' => array(),
            'sections' => array(),
            'layout' => array(),
            'listing' => array(),
            'agent' => array(),
            'templates' => array()
        );
        
        foreach (self::$components as $name => $component) {
            $category = $component['category'];
            if (!isset($categorized[$category])) {
                $categorized[$category] = array();
            }
            $categorized[$category][$name] = $component;
        }
        
        // Sort categories by priority
        $priority = array('base', 'sections', 'layout', 'listing', 'agent', 'templates');
        $sorted = array();
        foreach ($priority as $cat) {
            if (isset($categorized[$cat]) && !empty($categorized[$cat])) {
                $sorted[$cat] = $categorized[$cat];
            }
        }
        
        return $sorted;
    }
    
    /**
     * Register component shortcodes (Only for public-facing components)
     * Updated to match actual components that exist and should be user-accessible
     */
    public static function register_shortcodes() {
        // Only register shortcodes for user-facing components that exist
        $public_components = array(
            // Base UI components
            'button', 'card', 'alert', 'accordion', 'carousel', 'modal', 'tabs',
            
            // Section components  
            'hero', 'content', 'cta', 'features', 
            
            // Listing components
            'listing-card', 'listing-grid', 'listing-hero',
            
            // Agent components
            'agent-card', 'agents-grid',
            
            // Search components
            'search-form', 'search-results'
        );
        
        foreach ($public_components as $name) {
            if (isset(self::$components[$name])) {
                $shortcode_name = 'hph_' . str_replace('-', '_', $name);
                add_shortcode($shortcode_name, function($atts, $content = '') use ($name) {
                    $args = (array) $atts;
                    // Pass content as an argument for nested shortcodes
                    if ($content) {
                        $args['content'] = do_shortcode($content);
                    }
                    return self::load_component($name, $args, false);
                });
            }
        }
    }
    
    /**
     * Check if component exists
     * 
     * @param string $component Component name
     * @return bool
     */
    public static function component_exists($component) {
        return isset(self::$components[$component]);
    }
}

/**
 * Helper function to load components
 * 
 * @param string $component Component name or path
 * @param array $args Component arguments
 * @param bool $echo Whether to echo output or return it
 * @return string|void
 */
function hph_component($component, $args = array(), $echo = true) {
    // Support path-style component names (e.g., 'sections/hero')
    if (strpos($component, '/') !== false) {
        $parts = explode('/', $component);
        $component = end($parts);
    }
    
    return HPH_Component_Loader::load_component($component, $args, $echo);
}

/**
 * Helper function to get component argument
 * Compatible with both the new pure components and existing components
 * 
 * @param string $key Optional key to get specific arg
 * @param mixed $default Default value
 * @return mixed
 */
function hph_get_arg($key = null, $default = null) {
    // Check for component args in global (new system)
    $args = $GLOBALS['hph_component_args'] ?? array();
    
    // Fallback to query var (existing system)
    if (empty($args)) {
        $args = get_query_var('args', array());
    }
    
    if ($key === null) {
        return $args;
    }
    
    return isset($args[$key]) ? $args[$key] : $default;
}

/**
 * Helper function to render HTML attributes
 * Used by base components
 */
if (!function_exists('hph_render_attributes')) {
    function hph_render_attributes($attributes) {
        foreach ($attributes as $key => $value) {
            if ($value === false || $value === null) continue;
            
            if ($value === true) {
                echo esc_attr($key) . ' ';
            } else {
                echo esc_attr($key) . '="' . esc_attr($value) . '" ';
            }
        }
    }
}

// Initialize the component loader
add_action('init', array('HPH_Component_Loader', 'init'));
