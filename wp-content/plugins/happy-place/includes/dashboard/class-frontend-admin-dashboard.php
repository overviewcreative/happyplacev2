<?php
/**
 * Frontend Admin Dashboard Controller
 * 
 * Comprehensive dashboard system for role-based content management
 * Utilizes theme CSS framework for consistent styling
 *
 * @package HappyPlace\Dashboard
 */

namespace HappyPlace\Dashboard;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Admin_Dashboard {

    private static $instance = null;
    private $user_roles = [];
    private $dashboard_sections = [];
    private $current_section = 'overview';
    private $user_permissions = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->init_user_roles();
        $this->init_dashboard_sections();
        $this->init_hooks();
    }

    /**
     * Initialize user roles and permissions
     */
    private function init_user_roles() {
        $this->user_roles = [
            'administrator' => [
                'label' => 'Administrator',
                'permissions' => ['all']
            ],
            'broker' => [
                'label' => 'Broker',
                'permissions' => ['manage_all_listings', 'manage_all_agents', 'view_analytics', 'manage_settings', 'sync_data']
            ],
            'agent' => [
                'label' => 'Agent',
                'permissions' => ['manage_own_listings', 'edit_profile', 'view_own_analytics', 'generate_marketing']
            ],
            'team_leader' => [
                'label' => 'Team Leader',
                'permissions' => ['manage_team_listings', 'manage_team_members', 'view_team_analytics']
            ],
            'assistant' => [
                'label' => 'Assistant',
                'permissions' => ['manage_assigned_listings', 'basic_analytics']
            ]
        ];
    }

    /**
     * Initialize dashboard sections
     */
    private function init_dashboard_sections() {
        $this->dashboard_sections = [
            'overview' => [
                'title' => 'Dashboard Overview',
                'icon' => 'hph-icon-dashboard',
                'permissions' => ['all'],
                'order' => 1
            ],
            'listings' => [
                'title' => 'Property Listings',
                'icon' => 'hph-icon-home',
                'permissions' => ['manage_all_listings', 'manage_own_listings', 'manage_team_listings', 'manage_assigned_listings'],
                'order' => 2
            ],
            'agents' => [
                'title' => 'Agent Management',
                'icon' => 'hph-icon-users',
                'permissions' => ['manage_all_agents', 'manage_team_members'],
                'order' => 3
            ],
            'profile' => [
                'title' => 'My Profile',
                'icon' => 'hph-icon-user',
                'permissions' => ['edit_profile'],
                'order' => 4
            ],
            'analytics' => [
                'title' => 'Analytics & Reports',
                'icon' => 'hph-icon-chart',
                'permissions' => ['view_analytics', 'view_own_analytics', 'view_team_analytics', 'basic_analytics'],
                'order' => 5
            ],
            'marketing' => [
                'title' => 'Marketing Suite',
                'icon' => 'hph-icon-megaphone',
                'permissions' => ['generate_marketing'],
                'order' => 6
            ],
            'sync' => [
                'title' => 'Data Sync',
                'icon' => 'hph-icon-sync',
                'permissions' => ['sync_data'],
                'order' => 7
            ],
            'settings' => [
                'title' => 'System Settings',
                'icon' => 'hph-icon-settings',
                'permissions' => ['manage_settings'],
                'order' => 8
            ],
            'users' => [
                'title' => 'User Management',
                'icon' => 'hph-icon-user-plus',
                'permissions' => ['manage_all_agents'],
                'order' => 9
            ]
        ];

        // Sort sections by order
        uasort($this->dashboard_sections, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('init', [$this, 'register_dashboard_page']);
        add_action('init', [$this, 'init_ajax_handlers']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_dashboard_assets']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'handle_dashboard_routing']);
        add_filter('body_class', [$this, 'add_dashboard_body_classes']);
    }
    
    /**
     * Initialize AJAX handlers
     */
    public function init_ajax_handlers() {
        // Initialize the AJAX handlers singleton
        Frontend_Ajax_Handlers::get_instance();
    }

    /**
     * Register the dashboard page
     */
    public function register_dashboard_page() {
        // Create dashboard page if it doesn't exist
        $dashboard_page = get_page_by_path('admin-dashboard');
        
        if (!$dashboard_page) {
            $page_data = [
                'post_title' => 'Admin Dashboard',
                'post_name' => 'admin-dashboard',
                'post_content' => '<!-- Frontend Admin Dashboard -->',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1,
                'comment_status' => 'closed',
                'ping_status' => 'closed'
            ];

            $page_id = wp_insert_post($page_data);
            
            if ($page_id && !is_wp_error($page_id)) {
                update_post_meta($page_id, '_wp_page_template', 'page-admin-dashboard.php');
            }
        }
    }

    /**
     * Add query vars for dashboard routing
     */
    public function add_query_vars($vars) {
        $vars[] = 'dashboard_section';
        $vars[] = 'dashboard_action';
        $vars[] = 'dashboard_id';
        return $vars;
    }

    /**
     * Handle dashboard routing and access control
     */
    public function handle_dashboard_routing() {
        if (!$this->is_dashboard_page()) {
            return;
        }

        // Use plugin's consolidated access control
        $main_dashboard = \HappyPlace\Dashboard\Dashboard_Manager::get_instance();
        if (!$main_dashboard->user_has_dashboard_access()) {
            wp_redirect(wp_login_url(get_permalink()));
            exit;
        }

        // Get current user permissions
        $this->user_permissions = $this->get_user_permissions();

        // Get requested section from plugin's routing
        $this->current_section = $main_dashboard->get_current_section();

        // Check access permissions
        if (!$this->user_can_access_section($this->current_section)) {
            $this->current_section = 'overview'; // Fallback to overview
        }

        // Handle AJAX requests
        if (wp_doing_ajax()) {
            return;
        }
    }

    /**
     * Check if current page is dashboard
     */
    private function is_dashboard_page() {
        global $wp_query;
        return isset($wp_query->query_vars['pagename']) && 
               in_array($wp_query->query_vars['pagename'], ['admin-dashboard', 'agent-dashboard']);
    }

    /**
     * Get current user permissions
     */
    private function get_user_permissions() {
        $user = wp_get_current_user();
        $permissions = [];

        foreach ($user->roles as $role) {
            if (isset($this->user_roles[$role])) {
                $role_permissions = $this->user_roles[$role]['permissions'];
                if (in_array('all', $role_permissions)) {
                    return ['all']; // Admin has all permissions
                }
                $permissions = array_merge($permissions, $role_permissions);
            }
        }

        return array_unique($permissions);
    }

    /**
     * Check if user can access section
     */
    private function user_can_access_section($section) {
        if (!isset($this->dashboard_sections[$section])) {
            return false;
        }

        $required_permissions = $this->dashboard_sections[$section]['permissions'];
        
        // Check for admin access
        if (in_array('all', $this->user_permissions)) {
            return true;
        }

        // Check for specific permissions
        return !empty(array_intersect($required_permissions, $this->user_permissions));
    }

    /**
     * Get accessible sections for current user
     */
    public function get_accessible_sections() {
        $accessible = [];
        
        foreach ($this->dashboard_sections as $key => $section) {
            if ($this->user_can_access_section($key)) {
                $accessible[$key] = $section;
            }
        }
        
        return $accessible;
    }

    /**
     * Enqueue dashboard assets - coordinates with plugin
     */
    public function enqueue_dashboard_assets() {
        if (!$this->is_dashboard_page()) {
            return;
        }

        // Let the theme handle asset loading for better integration
        // Plugin will only load if theme assets are not present
        
        // Enqueue theme framework CSS
        wp_enqueue_style('hph-framework', get_template_directory_uri() . '/assets/css/hph-framework.css', [], HPT_VERSION);
        
        // Legacy dashboard variables - removed (file missing)
        // wp_enqueue_style('hph-dashboard-variables', get_template_directory_uri() . '/assets/css/framework/base/dashboard-variables.css', ['hph-framework'], HPT_VERSION);
        
        // Enqueue grid system - updated dependencies
        wp_enqueue_style('hph-grid', get_template_directory_uri() . '/assets/css/framework/layout/grid.css', ['hph-framework'], HPT_VERSION);
        
        // Legacy dashboard-specific CSS - removed (file missing)
        // wp_enqueue_style('hph-dashboard', get_template_directory_uri() . '/assets/css/pages/dashboard.css', ['hph-framework'], HPT_VERSION);
        
        // Legacy dashboard forms CSS - removed (file missing)
        // wp_enqueue_style('hph-dashboard-forms', get_template_directory_uri() . '/assets/css/components/dashboard-forms.css', ['hph-dashboard'], HPT_VERSION);
        
        // Enqueue modern UI enhancements - updated dependencies  
        wp_enqueue_style('hph-modern-ui', get_template_directory_uri() . '/assets/css/components/modern-ui-enhancements.css', ['hph-grid'], HPT_VERSION);
        
        // Enqueue section-specific CSS
        if ($this->current_section === 'profile') {
            wp_enqueue_style('hph-profile-redesigned', get_template_directory_uri() . '/assets/css/components/profile-redesigned.css', ['hph-modern-ui'], HPT_VERSION);
        } elseif ($this->current_section === 'listings') {
            wp_enqueue_style('hph-listings-redesigned', get_template_directory_uri() . '/assets/css/components/listings-redesigned.css', ['hph-modern-ui'], HPT_VERSION);
        } elseif ($this->current_section === 'agents') {
            wp_enqueue_style('hph-agents-redesigned', get_template_directory_uri() . '/assets/css/components/agents-redesigned.css', ['hph-modern-ui'], HPT_VERSION);
        }
        
        // Enqueue JavaScript
        wp_enqueue_script('hph-dashboard', get_template_directory_uri() . '/assets/js/dashboard-admin.js', ['jquery'], HPT_VERSION, true);
        
        // Localize script with consolidated backend data
        $main_dashboard = \HappyPlace\Dashboard\Dashboard_Manager::get_instance();
        
        wp_localize_script('hph-dashboard', 'hph_dashboard', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hpt_dashboard'), // Use plugin's nonce
            'current_section' => $this->current_section,
            'user_permissions' => $this->user_permissions,
            'agent_id' => $main_dashboard->get_current_agent_id(),
            'user_id' => get_current_user_id(),
            'rest_url' => rest_url('happy-place/v1/'),
            'strings' => [
                'loading' => __('Loading...', 'happy-place'),
                'error' => __('An error occurred', 'happy-place'),
                'success' => __('Success!', 'happy-place'),
                'confirm_delete' => __('Are you sure you want to delete this item?', 'happy-place'),
                'save_changes' => __('Save Changes', 'happy-place'),
                'cancel' => __('Cancel', 'happy-place')
            ]
        ]);
    }

    /**
     * Add dashboard body classes
     */
    public function add_dashboard_body_classes($classes) {
        if ($this->is_dashboard_page()) {
            $classes[] = 'hph-dashboard';
            $classes[] = 'hph-dashboard-' . $this->current_section;
        }
        return $classes;
    }

    /**
     * Render dashboard navigation
     */
    public function render_navigation() {
        $accessible_sections = $this->get_accessible_sections();
        
        if (empty($accessible_sections)) {
            return;
        }
        ?>
        <nav class="hph-dashboard-nav">
            <div class="nav-header">
                <h2 class="nav-title"><?php _e('Dashboard', 'happy-place'); ?></h2>
                <div class="nav-user">
                    <?php
                    $current_user = wp_get_current_user();
                    echo esc_html($current_user->display_name);
                    ?>
                </div>
            </div>
            <ul class="nav-menu">
                <?php foreach ($accessible_sections as $key => $section): ?>
                    <li class="nav-item <?php echo ($key === $this->current_section) ? 'active' : ''; ?>">
                        <a href="<?php echo esc_url($this->get_section_url($key)); ?>" class="nav-link">
                            <span class="nav-icon <?php echo esc_attr($section['icon']); ?>"></span>
                            <span class="nav-text"><?php echo esc_html($section['title']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="nav-footer">
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="nav-logout">
                    <span class="hph-icon-logout"></span>
                    <?php _e('Logout', 'happy-place'); ?>
                </a>
            </div>
        </nav>
        <?php
    }

    /**
     * Get section URL - uses consolidated routing
     */
    private function get_section_url($section) {
        // Use the plugin's unified URL structure
        return home_url("/agent-dashboard/{$section}/");
    }

    /**
     * Render dashboard content
     */
    public function render_content() {
        $section_file = $this->get_section_template($this->current_section);
        
        if ($section_file && file_exists($section_file)) {
            // Set up section data
            $section_data = [
                'current_section' => $this->current_section,
                'user_permissions' => $this->user_permissions,
                'section_config' => $this->dashboard_sections[$this->current_section] ?? []
            ];
            
            // Include section template
            include $section_file;
        } else {
            $this->render_section_not_found();
        }
    }

    /**
     * Get section template file path
     */
    private function get_section_template($section) {
        // Check for redesigned templates first
        if ($section === 'profile') {
            $redesigned_template = get_template_directory() . "/template-parts/dashboard/section-profile-redesigned.php";
            if (file_exists($redesigned_template)) {
                return $redesigned_template;
            }
        } elseif ($section === 'listings') {
            $redesigned_template = get_template_directory() . "/template-parts/dashboard/section-listings-redesigned.php";
            if (file_exists($redesigned_template)) {
                return $redesigned_template;
            }
        } elseif ($section === 'agents') {
            $redesigned_template = get_template_directory() . "/template-parts/dashboard/section-agents-redesigned.php";
            if (file_exists($redesigned_template)) {
                return $redesigned_template;
            }
        }
        
        $template_paths = [
            get_template_directory() . "/template-parts/dashboard/section-{$section}.php",
            HP_PLUGIN_DIR . "templates/dashboard/section-{$section}.php"
        ];

        foreach ($template_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return false;
    }

    /**
     * Render section not found
     */
    private function render_section_not_found() {
        ?>
        <div class="hph-dashboard-error">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php _e('Section Not Found', 'happy-place'); ?></h3>
                    <p><?php _e('The requested dashboard section could not be found or you do not have permission to access it.', 'happy-place'); ?></p>
                    <a href="<?php echo esc_url($this->get_section_url('overview')); ?>" class="btn btn-primary">
                        <?php _e('Return to Dashboard', 'happy-place'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get current section
     */
    public function get_current_section() {
        return $this->current_section;
    }

    /**
     * Get user permissions
     */
    public function get_user_permissions_public() {
        return $this->user_permissions;
    }

    /**
     * Check specific permission
     */
    public function user_can($permission) {
        return in_array('all', $this->user_permissions) || in_array($permission, $this->user_permissions);
    }
}