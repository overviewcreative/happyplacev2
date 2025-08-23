<?php
/**
 * Happy Place Template Loader
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 * 
 * Location: /wp-content/themes/happy-place/includes/class-hph-template-loader.php
 * 
 * This class handles the loading and registration of all template components,
 * shortcodes, assets, and related functionality for the Happy Place theme.
 */

class HPH_Template_Loader {
    
    /**
     * Instance of this class
     * @var HPH_Template_Loader
     */
    private static $instance = null;
    
    /**
     * Registered components
     * @var array
     */
    private $components = array();
    
    /**
     * Registered shortcodes
     * @var array
     */
    private $shortcodes = array();
    
    /**
     * Component dependencies
     * @var array
     */
    private $dependencies = array();
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->define_constants();
        $this->init_hooks();
    }
    
    /**
     * Get singleton instance
     * @return HPH_Template_Loader
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Define theme constants
     */
    private function define_constants() {
        // Theme paths
        if (!defined('HPH_THEME_DIR')) {
            define('HPH_THEME_DIR', get_template_directory());
        }
        if (!defined('HPH_THEME_URI')) {
            define('HPH_THEME_URI', get_template_directory_uri());
        }
        
        // Component paths
        if (!defined('HPH_COMPONENTS_DIR')) {
            define('HPH_COMPONENTS_DIR', HPH_THEME_DIR . '/template-parts/components');
        }
        if (!defined('HPH_INCLUDES_DIR')) {
            define('HPH_INCLUDES_DIR', HPH_THEME_DIR . '/includes');
        }
        
        // Asset paths
        if (!defined('HPH_ASSETS_URI')) {
            define('HPH_ASSETS_URI', HPH_THEME_URI . '/assets');
        }
        if (!defined('HPH_CSS_URI')) {
            define('HPH_CSS_URI', HPH_ASSETS_URI . '/css');
        }
        if (!defined('HPH_JS_URI')) {
            define('HPH_JS_URI', HPH_ASSETS_URI . '/js');
        }
        
        // Version
        if (!defined('HPH_VERSION')) {
            define('HPH_VERSION', wp_get_theme()->get('Version'));
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Core initialization
        add_action('after_setup_theme', array($this, 'load_dependencies'), 5);
        add_action('after_setup_theme', array($this, 'register_components'), 10);
        add_action('init', array($this, 'register_shortcodes'), 10);
        
        // Asset management is now handled by HPH_Assets service
        // No need for manual asset enqueuing
        
        // AJAX handlers
        add_action('wp_ajax_hph_load_component', array($this, 'ajax_load_component'));
        add_action('wp_ajax_nopriv_hph_load_component', array($this, 'ajax_load_component'));
        
        // Block editor support
        add_action('enqueue_block_editor_assets', array($this, 'block_editor_assets'));
        
        // Add theme support
        add_action('after_setup_theme', array($this, 'theme_support'));
    }
    
    /**
     * Load dependencies
     */
    public function load_dependencies() {
        // Load bridge functions
        $this->load_bridge_functions();
        
        // Load helper functions
        $this->load_helpers();
        
        // Load component registry
        $this->load_component_registry();
        
        // Load shortcode handlers
        $this->load_shortcode_handlers();
    }
    
    /**
     * Load bridge functions
     */
    private function load_bridge_functions() {
        $bridge_files = array(
            'listing-bridge',
            'gallery-bridge',
            'agent-bridge',
            'community-bridge',
            'city-bridge',
            'open-house-bridge',
            'transaction-bridge',
            'team-bridge',
            'local-place-bridge'
        );
        
        foreach ($bridge_files as $file) {
            $file_path = HPH_INCLUDES_DIR . '/bridge/' . $file . '.php';
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
        
        // Bridge functions are now integrated into their respective bridge files
        // No need to load separate extended-bridge-functions.php
    }
    
    /**
     * Load helper functions
     */
    private function load_helpers() {
        $helper_files = array(
            'null-safe-helpers',  // Load null-safe helpers first
            'template-helpers',
            'formatting-helpers',
            'media-helpers',
            'user-helpers'
        );
        
        foreach ($helper_files as $file) {
            $file_path = HPH_INCLUDES_DIR . '/helpers/' . $file . '.php';
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * Load component registry
     */
    private function load_component_registry() {
        $registry_file = HPH_INCLUDES_DIR . '/components/component-registry.php';
        if (file_exists($registry_file)) {
            require_once $registry_file;
        }
    }
    
    /**
     * Load shortcode handlers
     */
    private function load_shortcode_handlers() {
        $shortcode_dir = HPH_INCLUDES_DIR . '/shortcodes';
        if (is_dir($shortcode_dir)) {
            $files = glob($shortcode_dir . '/*.php');
            foreach ($files as $file) {
                require_once $file;
            }
        }
    }
    
    /**
     * Register components
     */
    public function register_components() {
        // Define available components - streamlined for service architecture
        $this->components = array(
            'listing-hero' => array(
                'name'        => 'Listing Hero',
                'description' => 'Full-width hero section for single property listings',
                'template'    => 'listing-hero',
                'shortcode'   => 'hph_listing_hero',
                'supports'    => array('single-listing', 'customizer'),
                'version'     => '3.0.0'
            ),
            'listing-card' => array(
                'name'        => 'Listing Card',
                'description' => 'Property listing card for grid/archive displays',
                'template'    => 'listing-card',
                'shortcode'   => 'hph_listing_card',
                'supports'    => array('customizer', 'loop'),
                'version'     => '3.0.0'
            ),
            'listing-details' => array(
                'name'        => 'Listing Details',
                'description' => 'Comprehensive property details component',
                'template'    => 'listing-details',
                'shortcode'   => 'hph_listing_details',
                'supports'    => array('single-listing'),
                'version'     => '3.0.0'
            ),
            'listing-features' => array(
                'name'        => 'Listing Features',
                'description' => 'Property features and amenities display',
                'template'    => 'listing-features',
                'shortcode'   => 'hph_listing_features',
                'supports'    => array('single-listing'),
                'version'     => '3.0.0'
            ),
            'listing-map' => array(
                'name'        => 'Listing Map',
                'description' => 'Interactive property location map',
                'template'    => 'listing-map',
                'shortcode'   => 'hph_listing_map',
                'supports'    => array('single-listing', 'maps'),
                'version'     => '3.0.0'
            ),
            'listing-agent' => array(
                'name'        => 'Listing Agent',
                'description' => 'Agent/team member display card',
                'template'    => 'listing-agent',
                'shortcode'   => 'hph_listing_agent',
                'supports'    => array('customizer', 'loop'),
                'version'     => '3.0.0'
            ),
            'search-form' => array(
                'name'        => 'Search Form',
                'description' => 'Advanced property search form',
                'template'    => 'advanced-search-form',
                'shortcode'   => 'hph_search_form',
                'supports'    => array('customizer', 'ajax'),
                'version'     => '3.0.0'
            ),
            'listing-contact-form' => array(
                'name'        => 'Listing Contact Form',
                'description' => 'Property inquiry contact form',
                'template'    => 'listing-contact-form',
                'shortcode'   => 'hph_listing_contact_form',
                'supports'    => array('customizer', 'ajax'),
                'version'     => '3.0.0'
            ),
            'listing-mortgage-calculator' => array(
                'name'        => 'Listing Mortgage Calculator',
                'description' => 'Interactive mortgage payment calculator',
                'template'    => 'listing-mortgage-calculator',
                'shortcode'   => 'hph_listing_mortgage_calculator',
                'supports'    => array('customizer', 'interactive'),
                'version'     => '3.0.0'
            ),
            'listing-photo-gallery' => array(
                'name'        => 'Listing Photo Gallery',
                'description' => 'Full-width photo gallery with lightbox and multiple display modes',
                'template'    => 'listing-photo-gallery',
                'shortcode'   => 'hph_listing_photo_gallery',
                'supports'    => array('single-listing', 'lightbox', 'responsive'),
                'version'     => '3.1.0'
            ),
            'listing-virtual-tour' => array(
                'name'        => 'Listing Virtual Tour',
                'description' => 'Interactive virtual tour gallery with 360° views and video tours',
                'template'    => 'listing-virtual-tour',
                'shortcode'   => 'hph_listing_virtual_tour',
                'supports'    => array('single-listing', '360-viewer', 'video'),
                'version'     => '3.1.0'
            ),
            'listing-floor-plans' => array(
                'name'        => 'Listing Floor Plans',
                'description' => 'Interactive floor plans with zoom, hotspots, and room details',
                'template'    => 'listing-floor-plans',
                'shortcode'   => 'hph_listing_floor_plans',
                'supports'    => array('single-listing', 'interactive', 'zoomable'),
                'version'     => '3.1.0'
            )
        );
        
        // Allow filtering of components
        $this->components = apply_filters('hph_registered_components', $this->components);
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        foreach ($this->components as $id => $component) {
            if (!empty($component['shortcode'])) {
                add_shortcode($component['shortcode'], array($this, 'render_shortcode'));
                $this->shortcodes[$component['shortcode']] = $id;
            }
        }
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts, $content, $tag) {
        // Get component ID from shortcode tag
        $component_id = isset($this->shortcodes[$tag]) ? $this->shortcodes[$tag] : null;
        
        if (!$component_id || !isset($this->components[$component_id])) {
            return '';
        }
        
        // Load component
        return $this->load_component($component_id, $atts);
    }
    
    /**
     * Load a component
     */
    public function load_component($component_id, $args = array()) {
        if (!isset($this->components[$component_id])) {
            return '';
        }
        
        $component = $this->components[$component_id];
        
        // Assets are automatically loaded by HPH_Assets service
        // No manual enqueuing needed
        
        // Start output buffering
        ob_start();
        
        // Load template
        $template_path = HPH_COMPONENTS_DIR . '/' . $component['template'] . '.php';
        if (file_exists($template_path)) {
            // Make args available to template
            $component_args = wp_parse_args($args, array(
                'component_id' => $component_id,
                'component'    => $component
            ));
            
            // Set up global variable for template access
            set_query_var('component_args', $component_args);
            
            // Include template
            include $template_path;
        } else {
            // Try legacy template loading
            get_template_part('template-parts/components/' . $component['template'], null, $component_args);
        }
        
        return ob_get_clean();
    }
    
    /**
     * Enqueue global assets - DEPRECATED
     * 
     * @deprecated 3.0.0 Use HPH_Assets service instead
     */
    public function enqueue_global_assets() {
        // Asset management is now handled by the HPH_Assets service
        // This method is kept for backward compatibility but does nothing
        
        // To add custom global assets, use the HPH_Assets service:
        // HPH_Assets::register_asset('my-custom-asset', 'path/to/file.js', array('dependencies'));
    }
    
    /**
     * Conditional asset loading - DEPRECATED
     * 
     * @deprecated 3.0.0 Use HPH_Assets service instead
     */
    public function conditional_enqueue() {
        // The HPH_Assets service automatically loads all component assets
        // No conditional logic needed - assets are loaded on-demand
    }
    
    /**
     * Enqueue component assets - DEPRECATED
     * 
     * @deprecated 3.0.0 Use HPH_Assets service instead
     */
    public function enqueue_component_assets($component_id) {
        // Assets are automatically loaded by the HPH_Assets service
        // This method is kept for backward compatibility but does nothing
    }
    
    /**
     * Admin assets
     */
    public function admin_assets($hook) {
        // Admin styles
        wp_enqueue_style(
            'hph-admin',
            HPH_CSS_URI . '/admin.css',
            array(),
            HPH_VERSION
        );
        
        // Admin scripts
        wp_enqueue_script(
            'hph-admin',
            HPH_JS_URI . '/admin.js',
            array('jquery'),
            HPH_VERSION,
            true
        );
    }
    
    /**
     * Block editor assets
     */
    public function block_editor_assets() {
        // Block editor styles
        wp_enqueue_style(
            'hph-block-editor',
            HPH_CSS_URI . '/block-editor.css',
            array('wp-edit-blocks'),
            HPH_VERSION
        );
        
        // Block editor scripts
        wp_enqueue_script(
            'hph-blocks',
            HPH_JS_URI . '/blocks.js',
            array('wp-blocks', 'wp-element', 'wp-editor'),
            HPH_VERSION,
            true
        );
    }
    
    /**
     * Theme support
     */
    public function theme_support() {
        // Add image sizes
        add_image_size('hero-slide', 1920, 1080, true);
        add_image_size('hero-slide-mobile', 768, 600, true);
        add_image_size('listing-card', 600, 400, true);
        add_image_size('agent-profile', 400, 400, true);
        
        // Theme supports
        add_theme_support('post-thumbnails');
        add_theme_support('responsive-embeds');
        add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
        
        // Editor support
        add_theme_support('editor-styles');
        add_editor_style('assets/css/editor-style.css');
    }
    
    /**
     * AJAX load component
     */
    public function ajax_load_component() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_nonce')) {
            wp_send_json_error('Invalid security token');
        }
        
        $component_id = sanitize_text_field($_POST['component']);
        $args = isset($_POST['args']) ? $_POST['args'] : array();
        
        $html = $this->load_component($component_id, $args);
        
        wp_send_json_success(array(
            'html' => $html
        ));
    }
    
    /**
     * Get component info
     */
    public function get_component($component_id) {
        return isset($this->components[$component_id]) ? $this->components[$component_id] : null;
    }
    
    /**
     * Get all components
     */
    public function get_components() {
        return $this->components;
    }
    
    /**
     * Check if component exists
     */
    public function component_exists($component_id) {
        return isset($this->components[$component_id]);
    }
}

// Initialize the template loader
HPH_Template_Loader::get_instance();