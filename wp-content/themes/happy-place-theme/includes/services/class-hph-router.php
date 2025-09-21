<?php
/**
 * Router Service
 * 
 * Handles custom routing for dashboard and other custom endpoints
 * 
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class HPH_Router implements HPH_Service {
    
    /**
     * Registered routes
     */
    private $routes = array();
    
    /**
     * Get service identifier
     */
    public function get_service_id() {
        return 'router';
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
     * Initialize router
     */
    public function init() {
        $this->register_routes();
        $this->setup_hooks();
        
        // Flush rewrite rules if needed
        $this->maybe_flush_rewrite_rules();
    }
    
    /**
     * Register routes
     */
    private function register_routes() {
        $this->routes = array(
            'agent-dashboard' => array(
                'regex'     => '^agent-dashboard/?',
                'query_var' => 'agent_dashboard',
                'template'  => 'dashboard/dashboard-main.php',
                'auth'      => true,
                'capability' => 'edit_posts',
            ),
            'property-search' => array(
                'regex'     => '^property-search/?',
                'query_var' => 'property_search',
                'template'  => 'search/advanced-search.php',
                'auth'      => false,
            ),
            'virtual-tour' => array(
                'regex'     => '^virtual-tour/([0-9]+)/?',
                'query_var' => 'virtual_tour',
                'template'  => 'single/virtual-tour.php',
                'auth'      => false,
            ),
        );
        
        $this->routes = apply_filters('hph_custom_routes', $this->routes);
    }
    
    /**
     * Setup hooks
     */
    private function setup_hooks() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('template_include', array($this, 'template_routing'));
        add_action('parse_request', array($this, 'parse_request'));
        
        // Flush rules on theme switch
        add_action('after_switch_theme', array($this, 'flush_rewrite_rules'));
    }
    
    /**
     * Add rewrite rules
     */
    public function add_rewrite_rules() {
        foreach ($this->routes as $route) {
            add_rewrite_rule(
                $route['regex'],
                'index.php?' . $route['query_var'] . '=1',
                'top'
            );
            add_rewrite_tag('%' . $route['query_var'] . '%', '([^&]+)');
        }
    }
    
    /**
     * Parse request
     */
    public function parse_request($wp) {
        // Check each route
        foreach ($this->routes as $key => $route) {
            if (isset($wp->query_vars[$route['query_var']])) {
                // Preserve GET parameters
                foreach ($_GET as $param_key => $param_value) {
                    if (!isset($wp->query_vars[$param_key])) {
                        $wp->query_vars[$param_key] = sanitize_text_field($param_value);
                    }
                }
                
                // Store current route
                $wp->query_vars['hph_route'] = $key;
            }
        }
    }
    
    /**
     * Template routing
     */
    public function template_routing($template) {
        // Skip custom routing for WordPress page templates
        if (is_page()) {
            $page_template = get_page_template_slug();
            
            // Allow dashboard and user system templates to use WordPress hierarchy
            $excluded_templates = array(
                'page-dashboard.php',
                'page-user-dashboard.php', 
                'page-test-dashboard.php',
                'page-login.php',
                'page-registration.php',
                'page-contact.php'
            );
            
            if (in_array($page_template, $excluded_templates)) {
                return $template; // Let WordPress handle these templates normally
            }
        }
        
        // Process custom routes only for non-page requests or pages without excluded templates
        foreach ($this->routes as $key => $route) {
            if (get_query_var($route['query_var'])) {
                // Check authentication if required
                if ($route['auth'] && !is_user_logged_in()) {
                    $this->handle_auth_redirect($route);
                    return $template;
                }
                
                // Check capabilities
                if (!empty($route['capability']) && !current_user_can($route['capability'])) {
                    $this->handle_permission_error();
                    return $template;
                }
                
                // Apply route-specific filters
                $this->apply_route_filters($key);
                
                // Get template
                $route_template = $this->get_route_template($route);
                if ($route_template) {
                    return $route_template;
                }
            }
        }
        
        return $template;
    }
    
    /**
     * Handle authentication redirect
     */
    private function handle_auth_redirect($route) {
        $redirect_url = home_url($_SERVER['REQUEST_URI']);
        wp_redirect(wp_login_url($redirect_url));
        exit;
    }
    
    /**
     * Handle permission error
     */
    private function handle_permission_error() {
        wp_die(
            __('You do not have permission to access this page.', 'happy-place-theme'),
            __('Permission Denied', 'happy-place-theme'),
            array('response' => 403)
        );
    }
    
    /**
     * Apply route-specific filters
     */
    private function apply_route_filters($route_key) {
        // Disable admin bar for dashboard
        if ($route_key === 'agent-dashboard') {
            add_filter('show_admin_bar', '__return_false');
        }
        
        // Add body classes
        add_filter('body_class', function($classes) use ($route_key) {
            $classes[] = 'hph-route';
            $classes[] = 'hph-route-' . $route_key;
            return $classes;
        });
    }
    
    /**
     * Get route template
     */
    private function get_route_template($route) {
        $template_path = HPH_THEME_DIR . '/templates/' . $route['template'];
        
        if (file_exists($template_path)) {
            return $template_path;
        }
        
        // Fallback to template part
        $template_part = str_replace('.php', '', $route['template']);
        $template_path = locate_template('template-parts/' . $template_part . '.php');
        
        return $template_path ?: false;
    }
    
    /**
     * Flush rewrite rules
     */
    public function flush_rewrite_rules() {
        $this->add_rewrite_rules();
        flush_rewrite_rules();
    }
    
    /**
     * Maybe flush rewrite rules if theme version changed
     */
    public function maybe_flush_rewrite_rules() {
        $stored_version = get_option('hph_router_version', '0');
        
        if (version_compare($stored_version, HPH_VERSION, '<')) {
            $this->flush_rewrite_rules();
            update_option('hph_router_version', HPH_VERSION);
        }
    }
    
    /**
     * Add custom route
     */
    public function add_route($key, $config) {
        $this->routes[$key] = wp_parse_args($config, array(
            'regex'      => '',
            'query_var'  => $key,
            'template'   => '',
            'auth'       => false,
            'capability' => '',
        ));
        
        // Add the rewrite rule immediately
        add_rewrite_rule(
            $this->routes[$key]['regex'],
            'index.php?' . $this->routes[$key]['query_var'] . '=1',
            'top'
        );
        add_rewrite_tag('%' . $this->routes[$key]['query_var'] . '%', '([^&]+)');
    }
    
    /**
     * Get current route
     */
    public function get_current_route() {
        return get_query_var('hph_route', false);
    }
    
    /**
     * Check if on custom route
     */
    public function is_route($route_key = null) {
        $current = $this->get_current_route();
        
        if ($route_key) {
            return $current === $route_key;
        }
        
        return !empty($current);
    }
}
