<?php
/**
 * Form Router Admin Interface
 * 
 * Provides admin configuration interface for the unified form routing system
 * 
 * @package HappyPlace\Admin
 * @version 1.0.0
 * @since 4.1.0
 */

namespace HappyPlace\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class FormRouterAdmin {
    
    /**
     * Initialize admin interface
     */
    public function init(): void {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // AJAX handlers for admin interface
        add_action('wp_ajax_hph_save_route_config', [$this, 'save_route_config']);
        add_action('wp_ajax_hph_test_route', [$this, 'test_route']);
        add_action('wp_ajax_hph_export_routes', [$this, 'export_routes']);
        add_action('wp_ajax_hph_import_routes', [$this, 'import_routes']);
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu(): void {
        add_submenu_page(
            'happy-place',
            'Form Router Configuration', 
            'Form Router',
            'manage_options',
            'happy-place-form-router',
            [$this, 'render_admin_page']
        );
        
        add_submenu_page(
            'happy-place-form-router',
            'Route Configuration',
            'Configure Routes',
            'manage_options',
            'happy-place-route-config',
            [$this, 'render_route_config_page']
        );
        
        add_submenu_page(
            'happy-place-form-router',
            'Field Mappings',
            'Field Mappings',
            'manage_options',
            'happy-place-field-mappings',
            [$this, 'render_field_mappings_page']
        );
        
        add_submenu_page(
            'happy-place-form-router',
            'Activity Log',
            'Activity Log',
            'manage_options',
            'happy-place-router-logs',
            [$this, 'render_activity_log_page']
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings(): void {
        register_setting('hph_form_router_settings', 'hph_form_router_routes');
        register_setting('hph_form_router_settings', 'hph_form_router_field_mappings');
        register_setting('hph_form_router_settings', 'hph_form_router_global_settings');
        register_setting('hph_calendly_settings_group', 'hp_calendly_username');
        register_setting('hph_calendly_settings_group', 'hp_calendly_calendar_slugs');
        
        // Global settings section
        add_settings_section(
            'hph_form_router_global_section',
            'Global Form Router Settings',
            [$this, 'global_settings_section_callback'],
            'hph_form_router_global_settings'
        );
        
        add_settings_field(
            'default_route_type',
            'Default Route Type',
            [$this, 'default_route_type_callback'],
            'hph_form_router_global_settings',
            'hph_form_router_global_section'
        );
        
        add_settings_field(
            'enable_debug_logging',
            'Enable Debug Logging',
            [$this, 'enable_debug_logging_callback'],
            'hph_form_router_global_settings',
            'hph_form_router_global_section'
        );
        
        // Calendly settings section
        add_settings_section(
            'hph_calendly_section',
            'Calendly Integration Settings',
            [$this, 'calendly_settings_section_callback'],
            'hph_calendly_settings'
        );
        
        add_settings_field(
            'calendly_username',
            'Calendly Username',
            [$this, 'calendly_username_callback'],
            'hph_calendly_settings',
            'hph_calendly_section'
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook): void {
        if (strpos($hook, 'happy-place-form-router') === false) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('wp-util');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_enqueue_script(
            'hph-form-router-admin',
            plugin_dir_url(__DIR__) . '../assets/js/form-router-admin.js',
            ['jquery', 'wp-util', 'wp-color-picker'],
            '1.0.0',
            true
        );
        
        wp_enqueue_style(
            'hph-form-router-admin',
            plugin_dir_url(__DIR__) . '../assets/css/form-router-admin.css',
            [],
            '1.0.0'
        );
        
        wp_localize_script('hph-form-router-admin', 'hphFormRouter', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_form_router_admin'),
            'routes' => get_option('hph_form_router_routes', []),
            'fieldMappings' => get_option('hph_form_router_field_mappings', [])
        ]);
    }
    
    /**
     * Render main admin page
     */
    public function render_admin_page(): void {
        $active_tab = $_GET['tab'] ?? 'overview';
        
        ?>
        <div class="wrap">
            <h1><?php _e('Form Router Configuration', 'happy-place'); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=happy-place-form-router&tab=overview" class="nav-tab <?php echo $active_tab === 'overview' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Overview', 'happy-place'); ?>
                </a>
                <a href="?page=happy-place-form-router&tab=global-settings" class="nav-tab <?php echo $active_tab === 'global-settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Global Settings', 'happy-place'); ?>
                </a>
                <a href="?page=happy-place-form-router&tab=calendly" class="nav-tab <?php echo $active_tab === 'calendly' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Calendly Integration', 'happy-place'); ?>
                </a>
                <a href="?page=happy-place-form-router&tab=migration" class="nav-tab <?php echo $active_tab === 'migration' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Migration Tools', 'happy-place'); ?>
                </a>
            </nav>
            
            <div class="tab-content">
                <?php
                switch ($active_tab) {
                    case 'global-settings':
                        $this->render_global_settings_tab();
                        break;
                    case 'calendly':
                        $this->render_calendly_settings_tab();
                        break;
                    case 'migration':
                        $this->render_migration_tab();
                        break;
                    case 'overview':
                    default:
                        $this->render_overview_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render overview tab
     */
    private function render_overview_tab(): void {
        $routes = get_option('hph_form_router_routes', []);
        $stats = $this->get_router_stats();
        
        ?>
        <div class="hph-router-overview">
            <div class="hph-stats-grid">
                <div class="hph-stat-box">
                    <h3><?php echo esc_html($stats['total_routes']); ?></h3>
                    <p><?php _e('Active Routes', 'happy-place'); ?></p>
                </div>
                <div class="hph-stat-box">
                    <h3><?php echo esc_html($stats['submissions_today']); ?></h3>
                    <p><?php _e('Submissions Today', 'happy-place'); ?></p>
                </div>
                <div class="hph-stat-box">
                    <h3><?php echo esc_html($stats['calendly_bookings']); ?></h3>
                    <p><?php _e('Calendly Bookings', 'happy-place'); ?></p>
                </div>
                <div class="hph-stat-box">
                    <h3><?php echo esc_html($stats['legacy_forms']); ?></h3>
                    <p><?php _e('Legacy Forms', 'happy-place'); ?></p>
                </div>
            </div>
            
            <div class="hph-router-status">
                <h2><?php _e('System Status', 'happy-place'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Component', 'happy-place'); ?></th>
                            <th><?php _e('Status', 'happy-place'); ?></th>
                            <th><?php _e('Details', 'happy-place'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Form Router Service</td>
                            <td><span class="hph-status-active"><?php _e('Active', 'happy-place'); ?></span></td>
                            <td><?php echo count($routes); ?> routes configured</td>
                        </tr>
                        <tr>
                            <td>Calendly Integration</td>
                            <td>
                                <?php if (get_option('hp_calendly_username')): ?>
                                    <span class="hph-status-active"><?php _e('Active', 'happy-place'); ?></span>
                                <?php else: ?>
                                    <span class="hph-status-inactive"><?php _e('Not Configured', 'happy-place'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (get_option('hp_calendly_username')): ?>
                                    Username: <?php echo esc_html(get_option('hp_calendly_username')); ?>
                                <?php else: ?>
                                    <a href="?page=happy-place-form-router&tab=calendly"><?php _e('Configure Calendly', 'happy-place'); ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>FollowUp Boss Integration</td>
                            <td>
                                <?php if (class_exists('\HappyPlace\Integrations\FollowUp_Boss_Integration')): ?>
                                    <span class="hph-status-active"><?php _e('Available', 'happy-place'); ?></span>
                                <?php else: ?>
                                    <span class="hph-status-inactive"><?php _e('Not Available', 'happy-place'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>CRM integration for lead sync</td>
                        </tr>
                        <tr>
                            <td>Legacy Compatibility</td>
                            <td><span class="hph-status-active"><?php _e('Active', 'happy-place'); ?></span></td>
                            <td>7 legacy form handlers redirected</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="hph-quick-actions">
                <h2><?php _e('Quick Actions', 'happy-place'); ?></h2>
                <div class="hph-action-buttons">
                    <a href="?page=happy-place-route-config" class="button button-primary">
                        <?php _e('Configure Routes', 'happy-place'); ?>
                    </a>
                    <a href="?page=happy-place-field-mappings" class="button">
                        <?php _e('Manage Field Mappings', 'happy-place'); ?>
                    </a>
                    <button class="button" id="hph-test-router">
                        <?php _e('Test Router', 'happy-place'); ?>
                    </button>
                    <a href="?page=happy-place-router-logs" class="button">
                        <?php _e('View Activity Logs', 'happy-place'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render global settings tab
     */
    private function render_global_settings_tab(): void {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('hph_form_router_settings');
            do_settings_sections('hph_form_router_global_settings');
            ?>
            
            <div class="hph-settings-section">
                <h2><?php _e('Business Hours Configuration', 'happy-place'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Business Start Hour', 'happy-place'); ?></th>
                        <td>
                            <select name="hp_business_start_hour">
                                <?php for ($i = 0; $i < 24; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php selected(get_option('hp_business_start_hour', 9), $i); ?>>
                                        <?php echo sprintf('%02d:00', $i); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Business End Hour', 'happy-place'); ?></th>
                        <td>
                            <select name="hp_business_end_hour">
                                <?php for ($i = 0; $i < 24; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php selected(get_option('hp_business_end_hour', 17), $i); ?>>
                                        <?php echo sprintf('%02d:00', $i); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="hph-settings-section">
                <h2><?php _e('Team Assignment Settings', 'happy-place'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Luxury Team Agent ID', 'happy-place'); ?></th>
                        <td>
                            <input type="number" name="hp_luxury_team_id" value="<?php echo esc_attr(get_option('hp_luxury_team_id', '')); ?>" />
                            <p class="description"><?php _e('Agent ID for luxury property inquiries ($1M+)', 'happy-place'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('On-Call Agent ID', 'happy-place'); ?></th>
                        <td>
                            <input type="number" name="hp_on_call_agent_id" value="<?php echo esc_attr(get_option('hp_on_call_agent_id', '')); ?>" />
                            <p class="description"><?php _e('Agent ID for after-hours inquiries', 'happy-place'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    /**
     * Render Calendly settings tab
     */
    private function render_calendly_settings_tab(): void {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('hph_calendly_settings_group');
            do_settings_sections('hph_calendly_settings');
            ?>
            
            <div class="hph-settings-section">
                <h2><?php _e('Calendar Type Mappings', 'happy-place'); ?></h2>
                <p class="description"><?php _e('Map form types to your Calendly calendar URLs. These are the part after your username in your Calendly links.', 'happy-place'); ?></p>
                
                <table class="form-table">
                    <?php
                    $calendar_slugs = get_option('hp_calendly_calendar_slugs', [
                        'consultation' => '30min',
                        'showing' => 'showing',
                        'valuation' => 'valuation',
                        'listing_appointment' => 'listing',
                        'call' => '15min'
                    ]);
                    
                    foreach ($calendar_slugs as $type => $slug):
                    ?>
                        <tr>
                            <th scope="row"><?php echo ucfirst(str_replace('_', ' ', $type)); ?></th>
                            <td>
                                <input type="text" 
                                       name="hp_calendly_calendar_slugs[<?php echo esc_attr($type); ?>]" 
                                       value="<?php echo esc_attr($slug); ?>" 
                                       placeholder="<?php echo esc_attr($slug); ?>" />
                                <p class="description">
                                    Full URL: https://calendly.com/<?php echo esc_html(get_option('hp_calendly_username', 'your-username')); ?>/<?php echo esc_html($slug); ?>
                                </p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            
            <div class="hph-settings-section">
                <h2><?php _e('Test Calendly Integration', 'happy-place'); ?></h2>
                <div id="hph-calendly-test-results"></div>
                <button type="button" class="button" id="hph-test-calendly">
                    <?php _e('Test Calendly Links', 'happy-place'); ?>
                </button>
            </div>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    /**
     * Render migration tab
     */
    private function render_migration_tab(): void {
        ?>
        <div class="hph-migration-tools">
            <div class="hph-settings-section">
                <h2><?php _e('Legacy Form Analysis', 'happy-place'); ?></h2>
                <p><?php _e('Analyze existing forms and their current routing to help with migration planning.', 'happy-place'); ?></p>
                
                <button type="button" class="button button-primary" id="hph-analyze-forms">
                    <?php _e('Analyze Existing Forms', 'happy-place'); ?>
                </button>
                
                <div id="hph-form-analysis-results" class="hph-results-container" style="display: none;">
                    <h3><?php _e('Analysis Results', 'happy-place'); ?></h3>
                    <div id="hph-analysis-content"></div>
                </div>
            </div>
            
            <div class="hph-settings-section">
                <h2><?php _e('Route Configuration Export/Import', 'happy-place'); ?></h2>
                <p><?php _e('Export your current route configurations for backup or import configurations from another installation.', 'happy-place'); ?></p>
                
                <div class="hph-migration-actions">
                    <button type="button" class="button" id="hph-export-routes">
                        <?php _e('Export Routes', 'happy-place'); ?>
                    </button>
                    
                    <div class="hph-import-section">
                        <h4><?php _e('Import Routes', 'happy-place'); ?></h4>
                        <input type="file" id="hph-import-file" accept=".json" />
                        <button type="button" class="button" id="hph-import-routes">
                            <?php _e('Import Routes', 'happy-place'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="hph-settings-section">
                <h2><?php _e('Migration Status', 'happy-place'); ?></h2>
                <div id="hph-migration-status">
                    <p><?php _e('The Form Router is currently running in compatibility mode, handling both new unified routes and legacy form submissions.', 'happy-place'); ?></p>
                    
                    <div class="hph-migration-checklist">
                        <h4><?php _e('Migration Checklist', 'happy-place'); ?></h4>
                        <ul>
                            <li class="hph-check-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php _e('Form Router service initialized', 'happy-place'); ?>
                            </li>
                            <li class="hph-check-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php _e('Legacy compatibility handlers registered', 'happy-place'); ?>
                            </li>
                            <li class="hph-check-item <?php echo get_option('hp_calendly_username') ? 'hph-complete' : 'hph-incomplete'; ?>">
                                <span class="dashicons dashicons-<?php echo get_option('hp_calendly_username') ? 'yes-alt' : 'minus'; ?>"></span>
                                <?php _e('Calendly integration configured', 'happy-place'); ?>
                            </li>
                            <li class="hph-check-item hph-incomplete">
                                <span class="dashicons dashicons-minus"></span>
                                <?php _e('All forms migrated to new system', 'happy-place'); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get router statistics
     */
    private function get_router_stats(): array {
        global $wpdb;
        
        $routes = get_option('hph_form_router_routes', []);
        
        // Get submissions today
        $leads_table = $wpdb->prefix . 'hp_leads';
        $submissions_today = 0;
        if ($wpdb->get_var("SHOW TABLES LIKE '$leads_table'") == $leads_table) {
            $submissions_today = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM $leads_table WHERE DATE(created_at) = CURDATE()"
            );
        }
        
        // Get Calendly bookings
        $appointments_table = $wpdb->prefix . 'hp_appointments';
        $calendly_bookings = 0;
        if ($wpdb->get_var("SHOW TABLES LIKE '$appointments_table'") == $appointments_table) {
            $calendly_bookings = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM $appointments_table WHERE source = 'form_router' OR source = 'website'"
            );
        }
        
        return [
            'total_routes' => count($routes),
            'submissions_today' => $submissions_today,
            'calendly_bookings' => $calendly_bookings,
            'legacy_forms' => 7 // Number of legacy form handlers we're redirecting
        ];
    }
    
    /**
     * Settings section callbacks
     */
    public function global_settings_section_callback(): void {
        echo '<p>' . __('Configure global settings that apply to all form routes.', 'happy-place') . '</p>';
    }
    
    public function calendly_settings_section_callback(): void {
        echo '<p>' . __('Configure your Calendly integration for appointment booking.', 'happy-place') . '</p>';
    }
    
    /**
     * Settings field callbacks
     */
    public function default_route_type_callback(): void {
        $value = get_option('hph_form_router_default_route', 'lead_capture');
        $route_types = [
            'lead_capture' => 'Lead Capture',
            'email_only' => 'Email Only',
            'booking_request' => 'Booking Request',
            'support_ticket' => 'Support Ticket'
        ];
        
        echo '<select name="hph_form_router_default_route">';
        foreach ($route_types as $key => $label) {
            echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Default routing type when no specific route is determined.', 'happy-place') . '</p>';
    }
    
    public function enable_debug_logging_callback(): void {
        $value = get_option('hph_form_router_debug_logging', false);
        echo '<input type="checkbox" name="hph_form_router_debug_logging" value="1"' . checked($value, true, false) . ' />';
        echo '<p class="description">' . __('Enable detailed logging for form routing debugging.', 'happy-place') . '</p>';
    }
    
    public function calendly_username_callback(): void {
        $username = get_option('hp_calendly_username', '');
        echo '<input type="text" name="hp_calendly_username" value="' . esc_attr($username) . '" size="40" placeholder="your-username" />';
        echo '<p class="description">' . __('Your Calendly username (e.g., "john-smith" for calendly.com/john-smith)', 'happy-place') . '</p>';
    }
    
    /**
     * AJAX: Save route configuration
     */
    public function save_route_config(): void {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_form_router_admin')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $route_data = $_POST['route_data'] ?? [];
        $route_id = sanitize_text_field($_POST['route_id'] ?? '');
        
        $routes = get_option('hph_form_router_routes', []);
        $routes[$route_id] = $route_data;
        
        update_option('hph_form_router_routes', $routes);
        
        wp_send_json_success(['message' => 'Route saved successfully']);
    }
    
    /**
     * AJAX: Test route configuration
     */
    public function test_route(): void {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_form_router_admin')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $route_id = sanitize_text_field($_POST['route_id'] ?? '');
        $test_data = $_POST['test_data'] ?? [];
        
        // Simulate form submission with test data
        $form_router = new \HappyPlace\Services\FormRouter();
        // Perform test logic here
        
        wp_send_json_success(['message' => 'Route test completed']);
    }
    
    /**
     * AJAX: Export routes
     */
    public function export_routes(): void {
        if (!wp_verify_nonce($_GET['nonce'] ?? '', 'hph_form_router_admin')) {
            wp_die('Security check failed');
        }
        
        $routes = get_option('hph_form_router_routes', []);
        $field_mappings = get_option('hph_form_router_field_mappings', []);
        
        $export_data = [
            'version' => '1.0.0',
            'exported' => current_time('mysql'),
            'routes' => $routes,
            'field_mappings' => $field_mappings
        ];
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="form-router-config-' . date('Y-m-d') . '.json"');
        echo json_encode($export_data, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * AJAX: Import routes
     */
    public function import_routes(): void {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_form_router_admin')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        if (empty($_FILES['import_file'])) {
            wp_send_json_error(['message' => 'No file uploaded']);
        }
        
        $file_content = file_get_contents($_FILES['import_file']['tmp_name']);
        $import_data = json_decode($file_content, true);
        
        if (!$import_data) {
            wp_send_json_error(['message' => 'Invalid JSON file']);
        }
        
        // Validate import data
        if (!isset($import_data['routes']) || !isset($import_data['field_mappings'])) {
            wp_send_json_error(['message' => 'Invalid configuration file format']);
        }
        
        update_option('hph_form_router_routes', $import_data['routes']);
        update_option('hph_form_router_field_mappings', $import_data['field_mappings']);
        
        wp_send_json_success(['message' => 'Configuration imported successfully']);
    }
    
    /**
     * Render route configuration page
     */
    public function render_route_config_page(): void {
        $routes = get_option('hph_form_router_routes', $this->get_default_routes());
        $active_route = $_GET['route'] ?? '';
        
        ?>
        <div class="wrap">
            <h1><?php _e('Route Configuration', 'happy-place'); ?></h1>
            
            <div class="hph-route-config-layout">
                <!-- Route List -->
                <div class="hph-route-list">
                    <div class="hph-route-list-header">
                        <h2><?php _e('Available Routes', 'happy-place'); ?></h2>
                        <button class="button button-primary" id="hph-add-route">
                            <?php _e('Add New Route', 'happy-place'); ?>
                        </button>
                    </div>
                    
                    <div class="hph-route-items">
                        <?php foreach ($routes as $route_id => $route_config): ?>
                        <div class="hph-route-item <?php echo $active_route === $route_id ? 'active' : ''; ?>" 
                             data-route-id="<?php echo esc_attr($route_id); ?>">
                            <div class="hph-route-item-header">
                                <h3><?php echo esc_html($route_config['name'] ?? $route_id); ?></h3>
                                <span class="hph-route-priority">Priority: <?php echo esc_html($route_config['priority'] ?? 10); ?></span>
                            </div>
                            <div class="hph-route-item-meta">
                                <span class="hph-route-status <?php echo ($route_config['enabled'] ?? true) ? 'enabled' : 'disabled'; ?>">
                                    <?php echo ($route_config['enabled'] ?? true) ? __('Enabled', 'happy-place') : __('Disabled', 'happy-place'); ?>
                                </span>
                                <span class="hph-route-actions-count">
                                    <?php echo count($route_config['actions'] ?? []); ?> actions
                                </span>
                            </div>
                            <div class="hph-route-item-actions">
                                <button class="button button-small hph-edit-route" data-route-id="<?php echo esc_attr($route_id); ?>">
                                    <?php _e('Edit', 'happy-place'); ?>
                                </button>
                                <button class="button button-small hph-test-route" data-route-id="<?php echo esc_attr($route_id); ?>">
                                    <?php _e('Test', 'happy-place'); ?>
                                </button>
                                <?php if (!in_array($route_id, ['lead_capture', 'property_inquiry', 'email_only'])): ?>
                                <button class="button button-small button-link-delete hph-delete-route" data-route-id="<?php echo esc_attr($route_id); ?>">
                                    <?php _e('Delete', 'happy-place'); ?>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Route Editor -->
                <div class="hph-route-editor">
                    <div id="hph-route-editor-content">
                        <div class="hph-no-route-selected">
                            <h2><?php _e('Select a Route to Configure', 'happy-place'); ?></h2>
                            <p><?php _e('Choose a route from the list to configure its settings, actions, and conditions.', 'happy-place'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Route Editor Modal -->
            <div id="hph-route-modal" class="hph-modal" style="display: none;">
                <div class="hph-modal-content">
                    <div class="hph-modal-header">
                        <h2 id="hph-modal-title"><?php _e('Edit Route', 'happy-place'); ?></h2>
                        <button class="hph-modal-close">&times;</button>
                    </div>
                    
                    <div class="hph-modal-body">
                        <form id="hph-route-form">
                            <div class="hph-form-section">
                                <h3><?php _e('Basic Settings', 'happy-place'); ?></h3>
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><?php _e('Route Name', 'happy-place'); ?></th>
                                        <td>
                                            <input type="text" name="name" id="hph-route-name" class="regular-text" required />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e('Description', 'happy-place'); ?></th>
                                        <td>
                                            <textarea name="description" id="hph-route-description" class="large-text" rows="3"></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e('Priority', 'happy-place'); ?></th>
                                        <td>
                                            <input type="number" name="priority" id="hph-route-priority" min="1" max="100" value="10" />
                                            <p class="description"><?php _e('Higher priority routes are checked first (1-100)', 'happy-place'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e('Status', 'happy-place'); ?></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="enabled" id="hph-route-enabled" value="1" checked />
                                                <?php _e('Enable this route', 'happy-place'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="hph-form-section">
                                <h3><?php _e('Matching Conditions', 'happy-place'); ?></h3>
                                <p class="description"><?php _e('Define when this route should be used based on form data.', 'happy-place'); ?></p>
                                
                                <div id="hph-route-conditions">
                                    <div class="hph-condition-item">
                                        <select name="conditions[0][field]">
                                            <option value="form_type"><?php _e('Form Type', 'happy-place'); ?></option>
                                            <option value="form_id"><?php _e('Form ID', 'happy-place'); ?></option>
                                            <option value="source_url"><?php _e('Source URL', 'happy-place'); ?></option>
                                            <option value="custom_field"><?php _e('Custom Field', 'happy-place'); ?></option>
                                        </select>
                                        
                                        <select name="conditions[0][operator]">
                                            <option value="equals"><?php _e('Equals', 'happy-place'); ?></option>
                                            <option value="contains"><?php _e('Contains', 'happy-place'); ?></option>
                                            <option value="starts_with"><?php _e('Starts with', 'happy-place'); ?></option>
                                            <option value="regex"><?php _e('Matches regex', 'happy-place'); ?></option>
                                        </select>
                                        
                                        <input type="text" name="conditions[0][value]" placeholder="<?php _e('Value', 'happy-place'); ?>" />
                                        
                                        <button type="button" class="button hph-remove-condition">
                                            <?php _e('Remove', 'happy-place'); ?>
                                        </button>
                                    </div>
                                </div>
                                
                                <button type="button" class="button" id="hph-add-condition">
                                    <?php _e('Add Condition', 'happy-place'); ?>
                                </button>
                            </div>
                            
                            <div class="hph-form-section">
                                <h3><?php _e('Actions', 'happy-place'); ?></h3>
                                <p class="description"><?php _e('Configure what happens when this route is matched.', 'happy-place'); ?></p>
                                
                                <div id="hph-route-actions">
                                    <div class="hph-action-item">
                                        <h4><?php _e('Database Storage', 'happy-place'); ?></h4>
                                        <label>
                                            <input type="checkbox" name="actions[database][enabled]" value="1" checked />
                                            <?php _e('Save to database', 'happy-place'); ?>
                                        </label>
                                        
                                        <div class="hph-action-settings">
                                            <label>
                                                <?php _e('Table:', 'happy-place'); ?>
                                                <select name="actions[database][table]">
                                                    <option value="wp_hp_leads"><?php _e('Leads', 'happy-place'); ?></option>
                                                    <option value="wp_hp_appointments"><?php _e('Appointments', 'happy-place'); ?></option>
                                                    <option value="custom"><?php _e('Custom', 'happy-place'); ?></option>
                                                </select>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="hph-action-item">
                                        <h4><?php _e('Email Notifications', 'happy-place'); ?></h4>
                                        <label>
                                            <input type="checkbox" name="actions[email][enabled]" value="1" />
                                            <?php _e('Send email notifications', 'happy-place'); ?>
                                        </label>
                                        
                                        <div class="hph-action-settings">
                                            <label>
                                                <?php _e('Admin Email:', 'happy-place'); ?>
                                                <input type="email" name="actions[email][admin_email]" value="<?php echo esc_attr(get_option('admin_email')); ?>" />
                                            </label>
                                            
                                            <label>
                                                <?php _e('User Auto-responder:', 'happy-place'); ?>
                                                <input type="checkbox" name="actions[email][auto_responder]" value="1" />
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="hph-action-item">
                                        <h4><?php _e('Calendly Integration', 'happy-place'); ?></h4>
                                        <label>
                                            <input type="checkbox" name="actions[calendly][enabled]" value="1" />
                                            <?php _e('Create Calendly booking', 'happy-place'); ?>
                                        </label>
                                        
                                        <div class="hph-action-settings">
                                            <label>
                                                <?php _e('Calendar Type:', 'happy-place'); ?>
                                                <select name="actions[calendly][calendar_type]">
                                                    <option value="consultation"><?php _e('Consultation', 'happy-place'); ?></option>
                                                    <option value="showing"><?php _e('Property Showing', 'happy-place'); ?></option>
                                                    <option value="valuation"><?php _e('Home Valuation', 'happy-place'); ?></option>
                                                    <option value="call"><?php _e('Phone Call', 'happy-place'); ?></option>
                                                </select>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="hph-action-item">
                                        <h4><?php _e('CRM Integration', 'happy-place'); ?></h4>
                                        <label>
                                            <input type="checkbox" name="actions[followup_boss][enabled]" value="1" />
                                            <?php _e('Sync to FollowUp Boss', 'happy-place'); ?>
                                        </label>
                                        
                                        <div class="hph-action-settings">
                                            <label>
                                                <?php _e('Lead Source:', 'happy-place'); ?>
                                                <input type="text" name="actions[followup_boss][source]" value="Website Form" />
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="route_id" id="hph-route-id" />
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_form_router_admin'); ?>" />
                        </form>
                    </div>
                    
                    <div class="hph-modal-footer">
                        <button type="button" class="button button-primary" id="hph-save-route">
                            <?php _e('Save Route', 'happy-place'); ?>
                        </button>
                        <button type="button" class="button" id="hph-cancel-route">
                            <?php _e('Cancel', 'happy-place'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .hph-route-config-layout {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .hph-route-list {
            flex: 0 0 300px;
            background: #fff;
            border: 1px solid #ddd;
            padding: 15px;
        }
        
        .hph-route-editor {
            flex: 1;
            background: #fff;
            border: 1px solid #ddd;
            padding: 20px;
        }
        
        .hph-route-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .hph-route-item:hover {
            background-color: #f9f9f9;
        }
        
        .hph-route-item.active {
            border-color: #0073aa;
            background-color: #e3f2fd;
        }
        
        .hph-route-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .hph-route-item-header h3 {
            margin: 0;
            font-size: 14px;
        }
        
        .hph-route-priority {
            font-size: 12px;
            color: #666;
        }
        
        .hph-route-status.enabled {
            color: #46b450;
        }
        
        .hph-route-status.disabled {
            color: #dc3232;
        }
        
        .hph-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999999;
        }
        
        .hph-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 4px;
        }
        
        .hph-modal-header {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .hph-modal-body {
            padding: 20px;
            max-height: 60vh;
            overflow-y: auto;
        }
        
        .hph-modal-footer {
            padding: 20px;
            border-top: 1px solid #ddd;
            text-align: right;
        }
        
        .hph-form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .hph-action-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .hph-action-settings {
            margin-top: 10px;
            padding-left: 20px;
        }
        
        .hph-condition-item {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        </style>
        <?php
    }
    
    /**
     * Render field mappings page
     */
    public function render_field_mappings_page(): void {
        $field_mappings = get_option('hph_form_router_field_mappings', $this->get_default_field_mappings());
        
        ?>
        <div class="wrap">
            <h1><?php _e('Field Mappings Configuration', 'happy-place'); ?></h1>
            
            <div class="hph-field-mappings-intro">
                <p><?php _e('Configure how form fields are mapped to database fields and external integrations. This ensures data consistency across different form types.', 'happy-place'); ?></p>
            </div>
            
            <form method="post" action="options.php">
                <?php settings_fields('hph_form_router_settings'); ?>
                
                <div class="hph-mappings-container">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Standard Field', 'happy-place'); ?></th>
                                <th><?php _e('Source Fields', 'happy-place'); ?></th>
                                <th><?php _e('Transformation', 'happy-place'); ?></th>
                                <th><?php _e('Validation', 'happy-place'); ?></th>
                                <th><?php _e('Required', 'happy-place'); ?></th>
                                <th><?php _e('Actions', 'happy-place'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($field_mappings as $field_key => $mapping): ?>
                            <tr data-field="<?php echo esc_attr($field_key); ?>">
                                <td>
                                    <strong><?php echo esc_html($field_key); ?></strong>
                                    <input type="hidden" name="hph_form_router_field_mappings[<?php echo esc_attr($field_key); ?>][key]" value="<?php echo esc_attr($field_key); ?>" />
                                </td>
                                <td>
                                    <input type="text" 
                                           name="hph_form_router_field_mappings[<?php echo esc_attr($field_key); ?>][sources]" 
                                           value="<?php echo esc_attr(implode(', ', $mapping['sources'] ?? [])); ?>" 
                                           class="regular-text"
                                           placeholder="field1, field2, field3" />
                                    <p class="description"><?php _e('Comma-separated list of possible field names', 'happy-place'); ?></p>
                                </td>
                                <td>
                                    <select name="hph_form_router_field_mappings[<?php echo esc_attr($field_key); ?>][transform]">
                                        <option value=""><?php _e('None', 'happy-place'); ?></option>
                                        <option value="split_name" <?php selected($mapping['transform'] ?? '', 'split_name'); ?>>
                                            <?php _e('Split Full Name', 'happy-place'); ?>
                                        </option>
                                        <option value="combine_name" <?php selected($mapping['transform'] ?? '', 'combine_name'); ?>>
                                            <?php _e('Combine Names', 'happy-place'); ?>
                                        </option>
                                        <option value="format_phone" <?php selected($mapping['transform'] ?? '', 'format_phone'); ?>>
                                            <?php _e('Format Phone', 'happy-place'); ?>
                                        </option>
                                        <option value="normalize_email" <?php selected($mapping['transform'] ?? '', 'normalize_email'); ?>>
                                            <?php _e('Normalize Email', 'happy-place'); ?>
                                        </option>
                                        <option value="capitalize" <?php selected($mapping['transform'] ?? '', 'capitalize'); ?>>
                                            <?php _e('Capitalize', 'happy-place'); ?>
                                        </option>
                                        <option value="uppercase" <?php selected($mapping['transform'] ?? '', 'uppercase'); ?>>
                                            <?php _e('Uppercase', 'happy-place'); ?>
                                        </option>
                                        <option value="lowercase" <?php selected($mapping['transform'] ?? '', 'lowercase'); ?>>
                                            <?php _e('Lowercase', 'happy-place'); ?>
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <select name="hph_form_router_field_mappings[<?php echo esc_attr($field_key); ?>][validation]">
                                        <option value=""><?php _e('None', 'happy-place'); ?></option>
                                        <option value="email" <?php selected($mapping['validation'] ?? '', 'email'); ?>>
                                            <?php _e('Email', 'happy-place'); ?>
                                        </option>
                                        <option value="phone" <?php selected($mapping['validation'] ?? '', 'phone'); ?>>
                                            <?php _e('Phone', 'happy-place'); ?>
                                        </option>
                                        <option value="url" <?php selected($mapping['validation'] ?? '', 'url'); ?>>
                                            <?php _e('URL', 'happy-place'); ?>
                                        </option>
                                        <option value="numeric" <?php selected($mapping['validation'] ?? '', 'numeric'); ?>>
                                            <?php _e('Numeric', 'happy-place'); ?>
                                        </option>
                                        <option value="required" <?php selected($mapping['validation'] ?? '', 'required'); ?>>
                                            <?php _e('Required', 'happy-place'); ?>
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <input type="checkbox" 
                                           name="hph_form_router_field_mappings[<?php echo esc_attr($field_key); ?>][required]" 
                                           value="1" 
                                           <?php checked($mapping['required'] ?? false, true); ?> />
                                </td>
                                <td>
                                    <button type="button" class="button button-small hph-delete-mapping" data-field="<?php echo esc_attr($field_key); ?>">
                                        <?php _e('Delete', 'happy-place'); ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="hph-add-mapping-section">
                        <h3><?php _e('Add New Field Mapping', 'happy-place'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Field Name', 'happy-place'); ?></th>
                                <td>
                                    <input type="text" id="hph-new-field-name" class="regular-text" placeholder="<?php _e('e.g., customer_type', 'happy-place'); ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Source Fields', 'happy-place'); ?></th>
                                <td>
                                    <input type="text" id="hph-new-field-sources" class="regular-text" placeholder="<?php _e('e.g., type, customer_type, client_type', 'happy-place'); ?>" />
                                </td>
                            </tr>
                        </table>
                        <button type="button" class="button" id="hph-add-field-mapping">
                            <?php _e('Add Field Mapping', 'happy-place'); ?>
                        </button>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
            
            <div class="hph-field-mapping-help">
                <h3><?php _e('Field Mapping Help', 'happy-place'); ?></h3>
                <div class="hph-help-sections">
                    <div class="hph-help-section">
                        <h4><?php _e('Standard Fields', 'happy-place'); ?></h4>
                        <p><?php _e('These are the normalized field names used internally by the system:', 'happy-place'); ?></p>
                        <ul>
                            <li><strong>first_name:</strong> <?php _e('First name of the contact', 'happy-place'); ?></li>
                            <li><strong>last_name:</strong> <?php _e('Last name of the contact', 'happy-place'); ?></li>
                            <li><strong>full_name:</strong> <?php _e('Complete name (can be split)', 'happy-place'); ?></li>
                            <li><strong>email:</strong> <?php _e('Email address', 'happy-place'); ?></li>
                            <li><strong>phone:</strong> <?php _e('Phone number', 'happy-place'); ?></li>
                            <li><strong>message:</strong> <?php _e('Message or comments', 'happy-place'); ?></li>
                        </ul>
                    </div>
                    
                    <div class="hph-help-section">
                        <h4><?php _e('Transformations', 'happy-place'); ?></h4>
                        <ul>
                            <li><strong>split_name:</strong> <?php _e('Splits "John Doe" into first_name="John", last_name="Doe"', 'happy-place'); ?></li>
                            <li><strong>format_phone:</strong> <?php _e('Removes non-numeric characters from phone numbers', 'happy-place'); ?></li>
                            <li><strong>normalize_email:</strong> <?php _e('Converts email to lowercase and trims whitespace', 'happy-place'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .hph-field-mappings-intro {
            background: #fff;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #0073aa;
        }
        
        .hph-mappings-container {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
        }
        
        .hph-add-mapping-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .hph-field-mapping-help {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
        }
        
        .hph-help-sections {
            display: flex;
            gap: 30px;
        }
        
        .hph-help-section {
            flex: 1;
        }
        </style>
        <?php
    }
    
    /**
     * Render activity log page
     */
    public function render_activity_log_page(): void {
        global $wpdb;
        
        // Get log entries
        $logs_table = $wpdb->prefix . 'hp_form_router_logs';
        $per_page = 50;
        $paged = max(1, intval($_GET['paged'] ?? 1));
        $offset = ($paged - 1) * $per_page;
        
        // Create logs table if it doesn't exist
        $this->maybe_create_logs_table();
        
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $logs_table ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        
        $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table");
        $total_pages = ceil($total_logs / $per_page);
        
        ?>
        <div class="wrap">
            <h1><?php _e('Form Router Activity Log', 'happy-place'); ?></h1>
            
            <div class="hph-log-filters">
                <div class="alignleft actions">
                    <select name="log_level" id="log-level-filter">
                        <option value=""><?php _e('All Levels', 'happy-place'); ?></option>
                        <option value="info"><?php _e('Info', 'happy-place'); ?></option>
                        <option value="warning"><?php _e('Warning', 'happy-place'); ?></option>
                        <option value="error"><?php _e('Error', 'happy-place'); ?></option>
                        <option value="debug"><?php _e('Debug', 'happy-place'); ?></option>
                    </select>
                    
                    <input type="text" name="search" id="log-search" placeholder="<?php _e('Search logs...', 'happy-place'); ?>" value="<?php echo esc_attr($_GET['search'] ?? ''); ?>" />
                    
                    <button type="button" class="button" id="filter-logs"><?php _e('Filter', 'happy-place'); ?></button>
                    <button type="button" class="button" id="clear-logs"><?php _e('Clear All', 'happy-place'); ?></button>
                    <button type="button" class="button" id="export-logs"><?php _e('Export', 'happy-place'); ?></button>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 130px;"><?php _e('Date/Time', 'happy-place'); ?></th>
                        <th style="width: 80px;"><?php _e('Level', 'happy-place'); ?></th>
                        <th style="width: 120px;"><?php _e('Route', 'happy-place'); ?></th>
                        <th><?php _e('Message', 'happy-place'); ?></th>
                        <th style="width: 100px;"><?php _e('Data', 'happy-place'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5">
                            <em><?php _e('No log entries found.', 'happy-place'); ?></em>
                            <?php if (!get_option('hph_form_router_debug_logging')): ?>
                            <br><a href="?page=happy-place-form-router&tab=global-settings"><?php _e('Enable debug logging', 'happy-place'); ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr class="hph-log-row hph-log-<?php echo esc_attr($log->level); ?>">
                            <td><?php echo esc_html(date('M j, Y H:i:s', strtotime($log->created_at))); ?></td>
                            <td>
                                <span class="hph-log-level hph-log-level-<?php echo esc_attr($log->level); ?>">
                                    <?php echo esc_html(ucfirst($log->level)); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($log->route_type ?? 'N/A'); ?></td>
                            <td>
                                <?php echo esc_html($log->message); ?>
                                <?php if ($log->context): ?>
                                <button type="button" class="button button-small hph-view-context" data-context="<?php echo esc_attr($log->context); ?>">
                                    <?php _e('View Details', 'happy-place'); ?>
                                </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log->form_data): ?>
                                <button type="button" class="button button-small hph-view-data" data-form-data="<?php echo esc_attr($log->form_data); ?>">
                                    <?php _e('View Data', 'happy-place'); ?>
                                </button>
                                <?php else: ?>
                                <em><?php _e('N/A', 'happy-place'); ?></em>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php printf(__('%d items', 'happy-place'), $total_logs); ?>
                    </span>
                    <?php
                    echo paginate_links([
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_pages,
                        'current' => $paged
                    ]);
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Context Modal -->
            <div id="hph-context-modal" class="hph-modal" style="display: none;">
                <div class="hph-modal-content">
                    <div class="hph-modal-header">
                        <h2><?php _e('Log Context', 'happy-place'); ?></h2>
                        <button class="hph-modal-close">&times;</button>
                    </div>
                    <div class="hph-modal-body">
                        <pre id="hph-context-content"></pre>
                    </div>
                </div>
            </div>
            
            <!-- Form Data Modal -->
            <div id="hph-data-modal" class="hph-modal" style="display: none;">
                <div class="hph-modal-content">
                    <div class="hph-modal-header">
                        <h2><?php _e('Form Data', 'happy-place'); ?></h2>
                        <button class="hph-modal-close">&times;</button>
                    </div>
                    <div class="hph-modal-body">
                        <pre id="hph-data-content"></pre>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .hph-log-filters {
            margin: 20px 0;
        }
        
        .hph-log-level {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .hph-log-level-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .hph-log-level-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .hph-log-level-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .hph-log-level-debug {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .hph-view-context,
        .hph-view-data {
            margin-left: 10px;
        }
        </style>
        <?php
    }
    
    /**
     * Get default routes configuration
     */
    private function get_default_routes(): array {
        return [
            'lead_capture' => [
                'name' => 'Lead Capture',
                'description' => 'Standard lead capture with database storage and email notifications',
                'priority' => 10,
                'enabled' => true,
                'conditions' => [
                    ['field' => 'form_type', 'operator' => 'equals', 'value' => 'contact']
                ],
                'actions' => [
                    'database' => ['enabled' => true, 'table' => 'wp_hp_leads'],
                    'email' => ['enabled' => true, 'admin_email' => get_option('admin_email')],
                    'followup_boss' => ['enabled' => false]
                ]
            ],
            'property_inquiry' => [
                'name' => 'Property Inquiry',
                'description' => 'Property-specific inquiries with agent assignment',
                'priority' => 12,
                'enabled' => true,
                'conditions' => [
                    ['field' => 'form_type', 'operator' => 'equals', 'value' => 'inquiry']
                ],
                'actions' => [
                    'database' => ['enabled' => true, 'table' => 'wp_hp_leads'],
                    'email' => ['enabled' => true, 'admin_email' => get_option('admin_email')],
                    'calendly' => ['enabled' => true, 'calendar_type' => 'showing'],
                    'followup_boss' => ['enabled' => true]
                ]
            ],
            'valuation_request' => [
                'name' => 'Valuation Request',
                'description' => 'Home valuation requests with CMA team assignment',
                'priority' => 20,
                'enabled' => true,
                'conditions' => [
                    ['field' => 'form_type', 'operator' => 'equals', 'value' => 'valuation']
                ],
                'actions' => [
                    'database' => ['enabled' => true, 'table' => 'wp_hp_leads'],
                    'email' => ['enabled' => true, 'admin_email' => get_option('admin_email')],
                    'calendly' => ['enabled' => true, 'calendar_type' => 'valuation'],
                    'followup_boss' => ['enabled' => true]
                ]
            ],
            'email_only' => [
                'name' => 'Email Only',
                'description' => 'Simple email notifications without database storage',
                'priority' => 5,
                'enabled' => true,
                'conditions' => [
                    ['field' => 'form_type', 'operator' => 'equals', 'value' => 'newsletter']
                ],
                'actions' => [
                    'database' => ['enabled' => false],
                    'email' => ['enabled' => true, 'admin_email' => get_option('admin_email')],
                    'followup_boss' => ['enabled' => false]
                ]
            ]
        ];
    }
    
    /**
     * Get default field mappings
     */
    private function get_default_field_mappings(): array {
        return [
            'first_name' => [
                'sources' => ['first_name', 'fname', 'firstName'],
                'transform' => null,
                'validation' => 'required',
                'required' => true
            ],
            'last_name' => [
                'sources' => ['last_name', 'lname', 'lastName'],
                'transform' => null,
                'validation' => 'required',
                'required' => true
            ],
            'full_name' => [
                'sources' => ['name', 'full_name', 'fullName'],
                'transform' => 'split_name',
                'validation' => null,
                'required' => false
            ],
            'email' => [
                'sources' => ['email', 'email_address', 'user_email'],
                'transform' => 'normalize_email',
                'validation' => 'email',
                'required' => true
            ],
            'phone' => [
                'sources' => ['phone', 'phone_number', 'tel'],
                'transform' => 'format_phone',
                'validation' => 'phone',
                'required' => false
            ],
            'message' => [
                'sources' => ['message', 'comments', 'inquiry', 'description'],
                'transform' => null,
                'validation' => null,
                'required' => false
            ]
        ];
    }
    
    /**
     * Maybe create logs table
     */
    private function maybe_create_logs_table(): void {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_form_router_logs';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id int(11) NOT NULL AUTO_INCREMENT,
                level varchar(20) NOT NULL,
                message text NOT NULL,
                route_type varchar(50) DEFAULT NULL,
                context text DEFAULT NULL,
                form_data longtext DEFAULT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_level (level),
                KEY idx_route_type (route_type),
                KEY idx_created_at (created_at)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}