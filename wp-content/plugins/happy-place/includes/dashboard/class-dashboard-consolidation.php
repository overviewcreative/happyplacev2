<?php
/**
 * Dashboard Consolidation Handler
 * 
 * Handles the transition between dual dashboard systems
 * and ensures smooth consolidation
 *
 * @package HappyPlace\Dashboard
 */

namespace HappyPlace\Dashboard;

if (!defined('ABSPATH')) {
    exit;
}

class Dashboard_Consolidation {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('admin_notices', [$this, 'show_consolidation_notice']);
        add_action('wp_ajax_hp_dismiss_consolidation_notice', [$this, 'dismiss_notice']);
    }

    /**
     * Show consolidation notice to administrators
     */
    public function show_consolidation_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (get_option('hp_dashboard_consolidation_dismissed')) {
            return;
        }

        // Check if both dashboard pages exist
        $agent_dashboard = get_page_by_path('agent-dashboard');
        $admin_dashboard = get_page_by_path('admin-dashboard');
        
        if ($agent_dashboard && $admin_dashboard) {
            ?>
            <div class="notice notice-info is-dismissible hp-consolidation-notice">
                <h3><?php _e('Dashboard Consolidation Complete', 'happy-place'); ?></h3>
                <p><?php _e('The Happy Place dashboard system has been consolidated. The agent dashboard is now the primary dashboard interface.', 'happy-place'); ?></p>
                <p>
                    <strong><?php _e('What changed:', 'happy-place'); ?></strong>
                </p>
                <ul>
                    <li><?php _e('✅ Backend functionality consolidated in plugin', 'happy-place'); ?></li>
                    <li><?php _e('✅ Frontend templates remain in theme for customization', 'happy-place'); ?></li>
                    <li><?php _e('✅ Asset loading optimized to prevent conflicts', 'happy-place'); ?></li>
                    <li><?php _e('✅ AJAX handlers unified under single system', 'happy-place'); ?></li>
                </ul>
                <p>
                    <a href="<?php echo home_url('/agent-dashboard/'); ?>" class="button button-primary">
                        <?php _e('Visit Dashboard', 'happy-place'); ?>
                    </a>
                    <button type="button" class="button" id="dismiss-consolidation-notice">
                        <?php _e('Dismiss Notice', 'happy-place'); ?>
                    </button>
                </p>
            </div>
            <script>
            jQuery(document).ready(function($) {
                $('#dismiss-consolidation-notice').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'hp_dismiss_consolidation_notice',
                            nonce: '<?php echo wp_create_nonce('hp_consolidation'); ?>'
                        },
                        success: function() {
                            $('.hp-consolidation-notice').fadeOut();
                        }
                    });
                });
            });
            </script>
            <?php
        }
    }

    /**
     * Dismiss the consolidation notice
     */
    public function dismiss_notice() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_consolidation')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }

        update_option('hp_dashboard_consolidation_dismissed', true);
        wp_send_json_success();
    }

    /**
     * Clean up old dashboard files and settings
     */
    public function cleanup_old_system() {
        // Remove duplicate admin-dashboard page if agent-dashboard exists
        $agent_dashboard = get_page_by_path('agent-dashboard');
        $admin_dashboard = get_page_by_path('admin-dashboard');
        
        if ($agent_dashboard && $admin_dashboard) {
            // Only remove admin-dashboard if it's using the template
            $template = get_page_template_slug($admin_dashboard->ID);
            if ($template === 'page-admin-dashboard.php') {
                hp_log('Removing duplicate admin-dashboard page', 'info', 'CONSOLIDATION');
                wp_delete_post($admin_dashboard->ID, true);
            }
        }

        // Clear any cached data
        wp_cache_flush();
        
        hp_log('Dashboard consolidation cleanup completed', 'info', 'CONSOLIDATION');
    }

    /**
     * Verify consolidation integrity
     */
    public function verify_consolidation() {
        $issues = [];

        // Check if main dashboard page exists
        $dashboard_page = get_page_by_path('agent-dashboard');
        if (!$dashboard_page) {
            $issues[] = 'Agent dashboard page not found';
        }

        // Check if template exists
        $template_path = get_template_directory() . '/page-admin-dashboard.php';
        if (!file_exists($template_path)) {
            $issues[] = 'Dashboard template file missing';
        }

        // Check if required classes exist
        if (!class_exists('HappyPlace\\Dashboard\\Dashboard_Manager')) {
            $issues[] = 'Dashboard Manager class not found';
        }

        if (!class_exists('HappyPlace\\Dashboard\\Frontend_Admin_Dashboard')) {
            $issues[] = 'Frontend Admin Dashboard class not found';
        }

        // Check if AJAX handlers are properly initialized
        if (!class_exists('HappyPlace\\API\\Ajax\\Dashboard_Ajax')) {
            $issues[] = 'Dashboard AJAX handler not found';
        }

        return empty($issues) ? true : $issues;
    }
}