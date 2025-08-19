<?php
/**
 * Dashboard Manager
 * Manages the agent dashboard system
 *
 * @package HappyPlace
 */

namespace HappyPlace\Dashboard;

if (!defined('ABSPATH')) {
    exit;
}

class Dashboard_Manager {

    private static $instance = null;
    private $sections = [];
    private $dashboard_data = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Alias for backwards compatibility
    public static function instance() {
        return self::get_instance();
    }

    private function __construct() {
        $this->init_hooks();
        $this->load_sections();
        
        // Force rewrite rules flush if needed
        if (!get_option('hp_dashboard_rewrite_version') || get_option('hp_dashboard_rewrite_version') < 2) {
            update_option('hp_flush_rewrite_rules', true);
            update_option('hp_dashboard_rewrite_version', 2);
        }
    }

    private function init_hooks() {
        add_action('init', [$this, 'add_dashboard_rewrite_rules']);
        add_action('init', [$this, 'register_dashboard_page']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_dashboard_assets']);
        add_filter('query_vars', [$this, 'add_dashboard_query_vars']);
        add_action('template_redirect', [$this, 'dashboard_access_control']);
        add_filter('body_class', [$this, 'add_dashboard_body_classes']);
        
        // Initialize AJAX handlers
        add_action('init', [$this, 'init_ajax_handlers']);
        
        // Flush rewrite rules if needed
        add_action('init', [$this, 'maybe_flush_rewrite_rules'], 20);
    }

    private function load_sections() {
        $section_files = [
            'Overview_Section' => 'sections/class-overview-section.php',
            'Listings_Section' => 'sections/class-listings-section.php', 
            'Marketing_Section' => 'sections/class-marketing-section.php',
            'Analytics_Section' => 'sections/class-analytics-section.php',
            'Calendar_Section' => 'sections/class-calendar-section.php',
            'Leads_Section' => 'sections/class-leads-section.php',
        ];

        foreach ($section_files as $class_name => $file_path) {
            $full_path = HP_PLUGIN_DIR . 'includes/dashboard/' . $file_path;
            $section_key = strtolower(str_replace('_Section', '', $class_name));
            
            if (file_exists($full_path)) {
                require_once $full_path;
                $full_class = 'HappyPlace\\Dashboard\\' . $class_name;
                
                if (class_exists($full_class)) {
                    try {
                        $this->sections[$section_key] = new $full_class($this);
                        hp_log("Dashboard section loaded: {$section_key}", 'info', 'DASHBOARD');
                    } catch (Exception $e) {
                        hp_log("Failed to instantiate section {$class_name}: " . $e->getMessage(), 'error', 'DASHBOARD');
                    }
                } else {
                    hp_log("Section class not found: {$full_class}", 'warning', 'DASHBOARD');
                }
            } else {
                hp_log("Section file not found: {$full_path}", 'warning', 'DASHBOARD');
            }
        }
        
        hp_log("Loaded dashboard sections: " . implode(', ', array_keys($this->sections)), 'info', 'DASHBOARD');
    }

    public function add_dashboard_rewrite_rules() {
        // Listing edit/view URLs with ID: /agent-dashboard/listings/edit/123/
        add_rewrite_rule(
            '^agent-dashboard/([^/]+)/([^/]+)/([0-9]+)/?$',
            'index.php?pagename=agent-dashboard&dashboard_section=$matches[1]&dashboard_action=$matches[2]&dashboard_id=$matches[3]',
            'top'
        );
        
        // Section with action: /agent-dashboard/listings/add/
        add_rewrite_rule(
            '^agent-dashboard/([^/]+)/([^/]+)/?$',
            'index.php?pagename=agent-dashboard&dashboard_section=$matches[1]&dashboard_action=$matches[2]',
            'top'
        );
        
        // Section only: /agent-dashboard/listings/
        add_rewrite_rule(
            '^agent-dashboard/([^/]+)/?$',
            'index.php?pagename=agent-dashboard&dashboard_section=$matches[1]',
            'top'
        );
        
        // Base dashboard URL: /agent-dashboard/
        add_rewrite_rule(
            '^agent-dashboard/?$',
            'index.php?pagename=agent-dashboard',
            'top'
        );
    }

    public function maybe_flush_rewrite_rules() {
        if (get_option('hp_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('hp_flush_rewrite_rules');
            hp_log('Dashboard rewrite rules flushed', 'info', 'DASHBOARD');
        }
    }

    public function add_dashboard_query_vars($vars) {
        $vars[] = 'dashboard_section';
        $vars[] = 'dashboard_action';
        $vars[] = 'dashboard_id';
        $vars[] = 'view_agent';
        return $vars;
    }

    public function dashboard_access_control() {
        global $wp_query;

        if (!isset($wp_query->query_vars['pagename']) || 
            $wp_query->query_vars['pagename'] !== 'agent-dashboard') {
            return;
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(home_url('/agent-dashboard/')));
            exit;
        }

        // Check if user has dashboard access
        if (!$this->user_has_dashboard_access()) {
            wp_die(__('You do not have permission to access the agent dashboard.', 'happy-place'), 
                   __('Access Denied', 'happy-place'), 
                   ['response' => 403]);
        }
    }

    public function user_has_dashboard_access($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        // Allow administrators full access
        if ($user->has_cap('manage_options')) {
            return true;
        }

        // Check for required capabilities
        $required_capabilities = ['edit_posts', 'upload_files'];
        foreach ($required_capabilities as $cap) {
            if (!$user->has_cap($cap)) {
                return false;
            }
        }

        // Check if user is linked to an agent post
        $agent_query = new \WP_Query([
            'post_type' => 'agent',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'wordpress_user',
                    'value' => $user_id,
                    'compare' => '='
                ]
            ]
        ]);

        return $agent_query->have_posts();
    }

    public function enqueue_dashboard_assets() {
        global $wp_query;

        if (!isset($wp_query->query_vars['pagename']) || 
            !in_array($wp_query->query_vars['pagename'], ['agent-dashboard', 'admin-dashboard'])) {
            return;
        }

        // Prevent double loading if theme assets are already enqueued
        if (wp_style_is('hph-dashboard', 'enqueued') || wp_script_is('hph-dashboard', 'enqueued')) {
            return;
        }

        // Ensure constants are available
        if (!defined('HP_ASSETS_URL')) {
            error_log('HP_ASSETS_URL not defined - cannot load dashboard assets');
            return;
        }

        // Dashboard CSS - check file exists in dist directory
        $dashboard_css = HP_ASSETS_URL . 'dist/dashboard.css';
        $dashboard_css_path = HP_PLUGIN_DIR . 'dist/dashboard.css';
        
        if (file_exists($dashboard_css_path)) {
            wp_enqueue_style(
                'hpt-dashboard',
                $dashboard_css,
                [],
                HP_VERSION
            );
            hp_log('Dashboard CSS enqueued successfully', 'debug', 'ASSETS');
        } else {
            error_log('Dashboard CSS file not found: ' . $dashboard_css_path);
        }

        // Dashboard JavaScript - check file exists in dist directory
        $dashboard_js = HP_ASSETS_URL . 'dist/dashboard.js';
        $dashboard_js_path = HP_PLUGIN_DIR . 'dist/dashboard.js';
        
        if (file_exists($dashboard_js_path)) {
            wp_enqueue_script(
                'hpt-dashboard',
                $dashboard_js,
                ['jquery'],
                HP_VERSION,
                true
            );
            hp_log('Dashboard JS enqueued successfully', 'debug', 'ASSETS');
        } else {
            error_log('Dashboard JS file not found: ' . $dashboard_js_path);
        }

        // Also enqueue vendors bundle
        $vendors_js = HP_ASSETS_URL . 'dist/vendors.js';
        $vendors_js_path = HP_PLUGIN_DIR . 'dist/vendors.js';
        
        if (file_exists($vendors_js_path)) {
            wp_enqueue_script(
                'hpt-vendors',
                $vendors_js,
                ['jquery'],
                HP_VERSION,
                true
            );
            hp_log('Vendors JS enqueued successfully', 'debug', 'ASSETS');
        }

        // Chart.js for analytics
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
            [],
            '3.9.1',
            true
        );

        // DataTables for listings management
        wp_enqueue_style(
            'datatables',
            'https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css',
            [],
            '1.13.1'
        );

        wp_enqueue_script(
            'datatables',
            'https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js',
            ['jquery'],
            '1.13.1',
            true
        );

        // Single localization for dashboard data (removed duplicate)
        wp_localize_script('hpt-dashboard', 'hptDashboard', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hpt_dashboard'),
            'userId' => get_current_user_id(),
            'agentId' => $this->get_current_agent_id(),
            'sections' => array_keys($this->sections),
            'currentSection' => $this->get_current_section(),
            'strings' => [
                'loading' => __('Loading...', 'happy-place'),
                'error' => __('An error occurred', 'happy-place'),
                'success' => __('Success!', 'happy-place'),
                'confirm' => __('Are you sure?', 'happy-place'),
                'confirmDelete' => __('Are you sure you want to delete this item?', 'happy-place'),
                'noData' => __('No data available', 'happy-place')
            ]
        ]);
    }

    public function get_current_agent_id() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return 0;
        }

        // Check if this is an admin viewing a specific agent
        if (current_user_can('manage_options')) {
            $view_agent = get_query_var('view_agent');
            if ($view_agent) {
                return intval($view_agent);
            }
            
            // For admins without specific agent view, return the first available agent
            $all_agents = get_posts([
                'post_type' => 'agent',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'post_status' => 'publish'
            ]);
            
            if (!empty($all_agents)) {
                return $all_agents[0];
            }
        }

        $agent_query = new \WP_Query([
            'post_type' => 'agent',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'wordpress_user',
                    'value' => $user_id,
                    'compare' => '='
                ]
            ]
        ]);

        if ($agent_query->have_posts()) {
            return $agent_query->posts[0]->ID;
        }

        return 0;
    }

    public function get_current_section() {
        return get_query_var('dashboard_section', 'overview');
    }

    public function get_current_action() {
        return get_query_var('dashboard_action', '');
    }

    public function handle_dashboard_ajax() {
        // Log incoming request for debugging
        error_log('Dashboard Ajax Request: ' . print_r($_POST, true));
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hpt_dashboard')) {
            error_log('Dashboard Ajax Error: Invalid nonce');
            wp_send_json_error(['message' => __('Security check failed', 'happy-place')]);
        }

        // Check user access
        if (!$this->user_has_dashboard_access()) {
            error_log('Dashboard Ajax Error: Access denied for user ID ' . get_current_user_id());
            wp_send_json_error(['message' => __('Access denied', 'happy-place')]);
        }

        $section = sanitize_text_field($_POST['section'] ?? '');
        $action = sanitize_text_field($_POST['action_type'] ?? '');
        
        error_log("Dashboard Ajax: Section={$section}, Action={$action}");

        if (!isset($this->sections[$section])) {
            error_log("Dashboard Ajax Error: Invalid section '{$section}'. Available sections: " . implode(', ', array_keys($this->sections)));
            wp_send_json_error(['message' => __('Invalid section', 'happy-place')]);
        }

        $section_instance = $this->sections[$section];
        $method_name = 'handle_ajax_' . $action;
        
        if (method_exists($section_instance, $method_name)) {
            error_log("Dashboard Ajax: Calling {$method_name} on " . get_class($section_instance));
            $result = $section_instance->$method_name($_POST);
            wp_send_json($result);
        }

        error_log("Dashboard Ajax Error: Method '{$method_name}' not found on " . get_class($section_instance));
        wp_send_json_error(['message' => __('Invalid action', 'happy-place')]);
    }

    public function handle_dashboard_actions() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hpt_dashboard')) {
            wp_send_json_error(['message' => __('Security check failed', 'happy-place')]);
        }

        // Check user access
        if (!$this->user_has_dashboard_access()) {
            wp_send_json_error(['message' => __('Access denied', 'happy-place')]);
        }

        $action = sanitize_text_field($_POST['dashboard_action'] ?? '');
        $data = $_POST;

        switch ($action) {
            case 'save_listing':
                $this->handle_save_listing($data);
                break;
                
            case 'delete_listing':
                $this->handle_delete_listing($data);
                break;
                
            case 'toggle_featured':
                $this->handle_toggle_featured($data);
                break;
                
            case 'update_profile':
                $this->handle_update_profile($data);
                break;
                
            case 'schedule_openhouse':
                $this->handle_schedule_openhouse($data);
                break;
                
            case 'delete_openhouse':
                $this->handle_delete_openhouse($data);
                break;
                
            case 'get_listing_data':
                // Route to appropriate section based on context
                $source = sanitize_text_field($data['source'] ?? 'listings');
                if ($source === 'marketing' && isset($this->sections['marketing'])) {
                    $result = $this->sections['marketing']->handle_ajax_get_listing_data($data);
                } else if (isset($this->sections['listings'])) {
                    $result = $this->sections['listings']->handle_ajax_get_listing_data($data);
                } else {
                    wp_send_json_error(['message' => __('Section not available', 'happy-place')]);
                }
                wp_send_json($result);
                break;
                
            // Marketing actions - route to marketing section
            case 'generate_pdf':
            case 'generate_marketing':
                if (isset($this->sections['marketing'])) {
                    $method_name = 'handle_ajax_' . $action;
                    if (method_exists($this->sections['marketing'], $method_name)) {
                        $result = $this->sections['marketing']->$method_name($data);
                        wp_send_json($result);
                    }
                }
                wp_send_json_error(['message' => __('Marketing action not available', 'happy-place')]);
                break;
                
            default:
                wp_send_json_error(['message' => __('Invalid action', 'happy-place')]);
        }
    }

    private function handle_save_listing($data) {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();

        if (!$agent_id) {
            wp_send_json_error(['message' => __('Agent not found', 'happy-place')]);
        }

        // Validate listing ownership or create new
        if ($listing_id > 0) {
            $listing = get_post($listing_id);
            if (!$listing || $listing->post_type !== 'listing') {
                wp_send_json_error(['message' => __('Listing not found', 'happy-place')]);
            }

            // Check if agent owns this listing
            $listing_agent = get_field('listing_agent', $listing_id);
            if (!$listing_agent || !in_array($agent_id, wp_list_pluck($listing_agent, 'ID'))) {
                wp_send_json_error(['message' => __('You do not have permission to edit this listing', 'happy-place')]);
            }
        }

        // Sanitize and validate data
        $listing_data = [
            'post_title' => sanitize_text_field($data['title'] ?? ''),
            'post_content' => wp_kses_post($data['description'] ?? ''),
            'post_status' => 'publish',
            'post_type' => 'listing'
        ];

        if ($listing_id > 0) {
            $listing_data['ID'] = $listing_id;
            $result = wp_update_post($listing_data);
        } else {
            $result = wp_insert_post($listing_data);
        }

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        $listing_id = $listing_id > 0 ? $listing_id : $result;

        // Save ACF fields
        $acf_fields = [
            'price' => (float) ($data['price'] ?? 0),
            'listing_status' => sanitize_text_field($data['status'] ?? 'active'),
            'bedrooms' => (int) ($data['bedrooms'] ?? 0),
            'bathrooms' => (float) ($data['bathrooms'] ?? 0),
            'square_feet' => (int) ($data['square_feet'] ?? 0),
            'street_address' => sanitize_text_field($data['address'] ?? ''),
            'city' => sanitize_text_field($data['city'] ?? ''),
            'state' => sanitize_text_field($data['state'] ?? ''),
            'zip_code' => sanitize_text_field($data['zip_code'] ?? ''),
            'listing_agent' => [$agent_id]
        ];

        foreach ($acf_fields as $key => $value) {
            update_field($key, $value, $listing_id);
        }

        wp_send_json_success([
            'message' => __('Listing saved successfully', 'happy-place'),
            'listing_id' => $listing_id,
            'redirect' => home_url("/agent-dashboard/listings/edit/{$listing_id}")
        ]);
    }

    private function handle_delete_listing($data) {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();

        if (!$listing_id || !$agent_id) {
            wp_send_json_error(['message' => __('Invalid request', 'happy-place')]);
        }

        // Verify ownership
        $listing_agent = get_field('listing_agent', $listing_id);
        if (!$listing_agent || !in_array($agent_id, wp_list_pluck($listing_agent, 'ID'))) {
            wp_send_json_error(['message' => __('You do not have permission to delete this listing', 'happy-place')]);
        }

        $result = wp_delete_post($listing_id, true);
        
        if ($result) {
            wp_send_json_success(['message' => __('Listing deleted successfully', 'happy-place')]);
        } else {
            wp_send_json_error(['message' => __('Failed to delete listing', 'happy-place')]);
        }
    }

    private function handle_toggle_featured($data) {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();

        if (!$listing_id || !$agent_id) {
            wp_send_json_error(['message' => __('Invalid request', 'happy-place')]);
        }

        // Verify ownership
        $listing_agent = get_field('listing_agent', $listing_id);
        if (!$listing_agent || !in_array($agent_id, wp_list_pluck($listing_agent, 'ID'))) {
            wp_send_json_error(['message' => __('You do not have permission to modify this listing', 'happy-place')]);
        }

        $current_featured = get_field('featured_listing', $listing_id);
        $new_featured = !$current_featured;
        
        update_field('featured_listing', $new_featured, $listing_id);

        wp_send_json_success([
            'message' => $new_featured ? __('Listing featured', 'happy-place') : __('Listing unfeatured', 'happy-place'),
            'featured' => $new_featured
        ]);
    }

    private function handle_update_profile($data) {
        $agent_id = $this->get_current_agent_id();
        
        if (!$agent_id) {
            wp_send_json_error(['message' => __('Agent not found', 'happy-place')]);
        }

        // Update agent post
        $agent_data = [
            'ID' => $agent_id,
            'post_content' => wp_kses_post($data['bio'] ?? '')
        ];

        wp_update_post($agent_data);

        // Update ACF fields
        $acf_fields = [
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'mobile_phone' => sanitize_text_field($data['mobile_phone'] ?? ''),
            'email' => sanitize_email($data['email'] ?? ''),
            'website' => esc_url_raw($data['website'] ?? ''),
            'facebook_url' => esc_url_raw($data['facebook_url'] ?? ''),
            'instagram_url' => esc_url_raw($data['instagram_url'] ?? ''),
            'linkedin_url' => esc_url_raw($data['linkedin_url'] ?? ''),
            'twitter_url' => esc_url_raw($data['twitter_url'] ?? ''),
        ];

        foreach ($acf_fields as $key => $value) {
            if ($value) {
                update_field($key, $value, $agent_id);
            }
        }

        wp_send_json_success(['message' => __('Profile updated successfully', 'happy-place')]);
    }

    public function render_dashboard() {
        $current_section = $this->get_current_section();
        $current_action = $this->get_current_action();
        $section_instance = $this->sections[$current_section] ?? $this->sections['overview'];
        
        // Simple debug for development
        echo '<!-- Dashboard Debug: Section=' . esc_html($current_section) . ', Action=' . esc_html($current_action) . ' -->';
        
        echo '<div id="hpt-dashboard" class="hpt-dashboard">';
        echo '<div class="hpt-dashboard__sidebar">';
        $this->render_sidebar();
        echo '</div>';
        
        echo '<div class="hpt-dashboard__main">';
        echo '<div class="hpt-dashboard__header">';
        $this->render_header();
        echo '</div>';
        
        echo '<div class="hpt-dashboard__content">';
        
        // Check if section exists and render
        if (!$section_instance) {
            echo '<div class="hpt-error" style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;">';
            echo '<h3>Section Not Found</h3>';
            echo '<p>Requested section: <strong>' . esc_html($current_section) . '</strong></p>';
            echo '<p>Available sections: <strong>' . implode(', ', array_keys($this->sections)) . '</strong></p>';
            echo '<p>Sections loaded: <strong>' . count($this->sections) . '</strong></p>';
            echo '</div>';
        } else if (!method_exists($section_instance, 'render')) {
            echo '<div class="hpt-error" style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;">';
            echo '<h3>Render Method Missing</h3>';
            echo '<p>Section <strong>' . esc_html($current_section) . '</strong> does not have a render method.</p>';
            echo '<p>Class: <strong>' . get_class($section_instance) . '</strong></p>';
            echo '</div>';
        } else {
            try {
                $section_instance->render();
            } catch (Throwable $e) {
                echo '<div class="hpt-error" style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;">';
                echo '<h3>Rendering Error</h3>';
                echo '<p>Error: <strong>' . esc_html($e->getMessage()) . '</strong></p>';
                echo '<p>File: <strong>' . esc_html($e->getFile()) . ':' . $e->getLine() . '</strong></p>';
                echo '</div>';
                
                // Log the error for debugging
                error_log('HPT Dashboard Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            }
        }
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    private function render_sidebar() {
        $current_section = $this->get_current_section();
        $agent_data = $this->get_current_agent_data();
        
        echo '<div class="hpt-dashboard-sidebar">';
        
        // Admin agent selector
        if (current_user_can('manage_options')) {
            echo '<div class="hpt-sidebar-admin-controls">';
            echo '<div class="hpt-admin-agent-selector">';
            echo '<label for="admin-agent-select">' . __('Viewing Dashboard for:', 'happy-place') . '</label>';
            echo '<select id="admin-agent-select" class="hpt-form__select">';
            
            $current_agent_id = $this->get_current_agent_id();
            $all_agents = get_posts([
                'post_type' => 'agent',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'title',
                'order' => 'ASC'
            ]);
            
            foreach ($all_agents as $agent) {
                $selected = ($agent->ID == $current_agent_id) ? ' selected' : '';
                echo '<option value="' . esc_attr($agent->ID) . '"' . $selected . '>' . esc_html($agent->post_title) . '</option>';
            }
            
            echo '</select>';
            echo '</div>';
            echo '</div>';
        }

        // Agent info
        if ($agent_data) {
            echo '<div class="hpt-sidebar-agent">';
            if ($agent_data['profile_photo']) {
                echo '<img src="' . esc_url($agent_data['profile_photo']['sizes']['agent-small'] ?? $agent_data['profile_photo']['url']) . '" alt="' . esc_attr($agent_data['name']) . '" class="hpt-sidebar-agent__photo">';
            }
            echo '<div class="hpt-sidebar-agent__info">';
            echo '<h3 class="hpt-sidebar-agent__name">' . esc_html($agent_data['name']) . '</h3>';
            if ($agent_data['title']) {
                echo '<div class="hpt-sidebar-agent__title">' . esc_html($agent_data['title']) . '</div>';
            }
            echo '</div>';
            echo '</div>';
        }

        // Navigation menu
        echo '<nav class="hpt-dashboard-nav">';
        echo '<ul class="hpt-dashboard-nav__list">';
        
        $nav_items = [
            'overview' => ['Overview', 'dashicons-dashboard'],
            'listings' => ['Listings', 'dashicons-admin-home'],
            'marketing' => ['Marketing', 'dashicons-megaphone'],
            'analytics' => ['Analytics', 'dashicons-chart-bar'],
            'calendar' => ['Calendar', 'dashicons-calendar-alt'],
            'leads' => ['Leads', 'dashicons-groups'],
        ];

        foreach ($nav_items as $section => $item) {
            $is_active = $current_section === $section;
            $url = home_url("/agent-dashboard/{$section}/");
            
            echo '<li class="hpt-dashboard-nav__item' . ($is_active ? ' is-active' : '') . '">';
            echo '<a href="' . esc_url($url) . '" class="hpt-dashboard-nav__link">';
            echo '<span class="hpt-dashboard-nav__icon dashicons ' . esc_attr($item[1]) . '"></span>';
            echo '<span class="hpt-dashboard-nav__text">' . esc_html($item[0]) . '</span>';
            echo '</a>';
            echo '</li>';
        }
        
        echo '</ul>';
        echo '</nav>';
        
        echo '</div>';
    }

    private function render_header() {
        $current_section = $this->get_current_section();
        $section_title = ucfirst(str_replace('_', ' ', $current_section));
        
        echo '<div class="hpt-dashboard-header">';
        echo '<div class="hpt-dashboard-header__title">';
        echo '<h1>' . esc_html($section_title) . '</h1>';
        echo '</div>';
        
        echo '<div class="hpt-dashboard-header__actions">';
        echo '<a href="' . esc_url(home_url()) . '" class="hpt-button hpt-button--outline hpt-button--sm">';
        echo __('View Site', 'happy-place');
        echo '</a>';
        echo '<a href="' . esc_url(wp_logout_url(home_url())) . '" class="hpt-button hpt-button--secondary hpt-button--sm">';
        echo __('Logout', 'happy-place');
        echo '</a>';
        echo '</div>';
        
        echo '</div>';
    }

    private function get_current_agent_data() {
        $agent_id = $this->get_current_agent_id();
        
        if (!$agent_id) {
            return null;
        }

        // Use bridge function if theme is loaded
        if (function_exists('hpt_get_agent_data')) {
            return hpt_get_agent_data($agent_id);
        }

        // Fallback to basic data
        $agent = get_post($agent_id);
        if (!$agent) {
            return null;
        }

        return [
            'id' => $agent_id,
            'name' => $agent->post_title,
            'title' => get_field('title', $agent_id),
            'profile_photo' => get_field('profile_photo', $agent_id)
        ];
    }

    public function get_section($section) {
        return $this->sections[$section] ?? null;
    }

    public function get_dashboard_stats() {
        $agent_id = $this->get_current_agent_id();
        if (!$agent_id) {
            return [];
        }

        // Cache stats for 5 minutes
        $cache_key = "dashboard_stats_{$agent_id}";
        $stats = wp_cache_get($cache_key);
        
        if ($stats === false) {
            $stats = $this->calculate_dashboard_stats($agent_id);
            wp_cache_set($cache_key, $stats, '', 300);
        }

        return $stats;
    }

    private function calculate_dashboard_stats($agent_id) {
        // Active listings
        $active_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'listing_status',
                    'value' => 'active',
                    'compare' => '='
                ]
            ]
        ]);

        // Sold this month
        $sold_this_month = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'date_query' => [
                [
                    'after' => date('Y-m-01'),
                    'before' => date('Y-m-t'),
                    'inclusive' => true,
                ],
            ],
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'listing_status',
                    'value' => 'sold',
                    'compare' => '='
                ]
            ]
        ]);

        // Calculate sales volume for this month
        $sales_volume = 0;
        foreach ($sold_this_month as $listing_id) {
            $price = get_field('price', $listing_id);
            if ($price) {
                $sales_volume += $price;
            }
        }

        return [
            'active_listings' => count($active_listings),
            'sold_this_month' => count($sold_this_month),
            'sales_volume' => $sales_volume,
            'pending_inquiries' => 0, // Would be calculated from leads system
        ];
    }

    private function handle_schedule_openhouse($data) {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        $openhouse_id = (int) ($data['openhouse_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();

        if (!$agent_id || !$listing_id) {
            wp_send_json_error(['message' => __('Invalid data provided', 'happy-place')]);
        }

        // Validate listing ownership
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            wp_send_json_error(['message' => __('Listing not found', 'happy-place')]);
        }

        $start_date = sanitize_text_field($data['start_date'] ?? '');
        $start_time = sanitize_text_field($data['start_time'] ?? '');
        $end_time = sanitize_text_field($data['end_time'] ?? '');
        $description = sanitize_textarea_field($data['description'] ?? '');

        if (!$start_date || !$start_time || !$end_time) {
            wp_send_json_error(['message' => __('Please fill in all required fields', 'happy-place')]);
        }

        // Create or update open house
        $openhouse_data = [
            'post_title' => 'Open House - ' . get_the_title($listing_id),
            'post_content' => $description,
            'post_status' => 'publish',
            'post_type' => 'open_house',
            'post_author' => get_current_user_id(),
        ];

        if ($openhouse_id > 0) {
            $openhouse_data['ID'] = $openhouse_id;
            $result = wp_update_post($openhouse_data);
            $action_text = 'updated';
        } else {
            $result = wp_insert_post($openhouse_data);
            $action_text = 'scheduled';
        }

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => __('Failed to save open house', 'happy-place')]);
        }

        $openhouse_id = $openhouse_id > 0 ? $openhouse_id : $result;

        // Update ACF fields
        update_field('listing', $listing_id, $openhouse_id);
        update_field('start_date', $start_date, $openhouse_id);
        update_field('start_time', $start_time, $openhouse_id);
        update_field('end_time', $end_time, $openhouse_id);

        wp_send_json_success([
            'message' => sprintf(__('Open house %s successfully', 'happy-place'), $action_text),
            'openhouse_id' => $openhouse_id,
            'redirect' => true
        ]);
    }

    private function handle_delete_openhouse($data) {
        $openhouse_id = (int) ($data['openhouse_id'] ?? 0);
        $agent_id = $this->get_current_agent_id();

        if (!$agent_id || !$openhouse_id) {
            wp_send_json_error(['message' => __('Invalid data provided', 'happy-place')]);
        }

        $openhouse = get_post($openhouse_id);
        if (!$openhouse || $openhouse->post_type !== 'open_house') {
            wp_send_json_error(['message' => __('Open house not found', 'happy-place')]);
        }

        // Verify ownership through listing
        $listing_id = get_field('listing', $openhouse_id);
        if (!$listing_id) {
            wp_send_json_error(['message' => __('Invalid open house data', 'happy-place')]);
        }

        $result = wp_delete_post($openhouse_id, true);
        if (!$result) {
            wp_send_json_error(['message' => __('Failed to delete open house', 'happy-place')]);
        }

        wp_send_json_success(['message' => __('Open house deleted successfully', 'happy-place')]);
    }

    /**
     * Register dashboard page programmatically
     */
    public function register_dashboard_page() {
        // Check for agent-dashboard page first, fallback to admin-dashboard
        $dashboard_page = get_page_by_path('agent-dashboard') ?: get_page_by_path('admin-dashboard');
        
        if (!$dashboard_page) {
            $page_data = [
                'post_title' => 'Agent Dashboard',
                'post_name' => 'agent-dashboard',
                'post_content' => '<!-- Agent Dashboard - Managed by Happy Place Plugin -->',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1,
                'comment_status' => 'closed',
                'ping_status' => 'closed'
            ];

            $page_id = wp_insert_post($page_data);
            
            if ($page_id && !is_wp_error($page_id)) {
                // Use the theme's admin dashboard template
                update_post_meta($page_id, '_wp_page_template', 'page-admin-dashboard.php');
                hp_log('Agent dashboard page created with ID: ' . $page_id, 'info', 'DASHBOARD');
            }
        } else if ($dashboard_page->post_name === 'admin-dashboard') {
            // If admin-dashboard page exists, update its slug to agent-dashboard for consistency
            wp_update_post([
                'ID' => $dashboard_page->ID,
                'post_name' => 'agent-dashboard'
            ]);
            hp_log('Updated admin-dashboard page slug to agent-dashboard', 'info', 'DASHBOARD');
        }
    }

    /**
     * Initialize AJAX handlers
     */
    public function init_ajax_handlers() {
        // Initialize the unified AJAX system
        if (class_exists('HappyPlace\\API\\Ajax\\Dashboard_Ajax')) {
            $ajax_handler = \HappyPlace\API\Ajax\Dashboard_Ajax::get_instance();
            $ajax_handler->init();
        }

        // Initialize consolidation handler
        if (class_exists('HappyPlace\\Dashboard\\Dashboard_Consolidation')) {
            Dashboard_Consolidation::get_instance();
        }
    }

    /**
     * Add dashboard body classes
     */
    public function add_dashboard_body_classes($classes) {
        global $wp_query;
        
        if (isset($wp_query->query_vars['pagename']) && 
            $wp_query->query_vars['pagename'] === 'agent-dashboard') {
            $classes[] = 'hph-dashboard';
            $classes[] = 'hph-dashboard-' . $this->get_current_section();
            
            // Add role-based classes
            $user = wp_get_current_user();
            if (!empty($user->roles)) {
                foreach ($user->roles as $role) {
                    $classes[] = 'hph-dashboard-role-' . $role;
                }
            }
        }
        
        return $classes;
    }
}