<?php
/**
 * Maintenance Mode Handler
 *
 * Simple maintenance mode with admin toggle that redirects to under construction page
 *
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

class HPH_Maintenance_Mode {

    const OPTION_KEY = 'hph_maintenance_mode';

    public function __construct() {
        add_action('init', array($this, 'init'));
    }

    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Handle form submission
        add_action('admin_post_hph_maintenance_toggle', array($this, 'handle_toggle'));

        // Add admin bar indicator
        add_action('admin_bar_menu', array($this, 'add_admin_bar_indicator'), 100);

        // Check for maintenance mode redirect (early hook)
        add_action('template_redirect', array($this, 'check_maintenance_mode'), 1);

        // Add admin notice when maintenance mode is active
        add_action('admin_notices', array($this, 'show_admin_notice'));
    }

    /**
     * Check if maintenance mode is enabled
     */
    public static function is_enabled() {
        return get_option(self::OPTION_KEY, false);
    }

    /**
     * Enable maintenance mode
     */
    public static function enable() {
        update_option(self::OPTION_KEY, true);
    }

    /**
     * Disable maintenance mode
     */
    public static function disable() {
        update_option(self::OPTION_KEY, false);
    }

    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_options_page(
            'Maintenance Mode',
            'Maintenance Mode',
            'manage_options',
            'hph-maintenance-mode',
            array($this, 'admin_page')
        );
    }

    /**
     * Admin page content
     */
    public function admin_page() {
        $is_enabled = self::is_enabled();
        $nonce = wp_create_nonce('hph_maintenance_toggle');
        ?>
        <div class="wrap">
            <h1>Maintenance Mode</h1>

            <div class="card" style="max-width: 600px;">
                <h2>Site Maintenance Control</h2>

                <div style="padding: 20px 0;">
                    <p><strong>Current Status:</strong>
                        <span style="color: <?php echo $is_enabled ? '#d63384' : '#198754'; ?>; font-weight: bold;">
                            <?php echo $is_enabled ? 'MAINTENANCE MODE ACTIVE' : 'Site is Live'; ?>
                        </span>
                    </p>

                    <?php if ($is_enabled): ?>
                        <div style="background: #f8d7da; border: 1px solid #f5c2c7; border-radius: 4px; padding: 15px; margin: 15px 0;">
                            <strong>⚠️ Warning:</strong> Your site is currently in maintenance mode.
                            All non-admin visitors will see the under construction page.
                        </div>
                    <?php endif; ?>

                    <p>When maintenance mode is enabled:</p>
                    <ul style="margin-left: 20px;">
                        <li>All visitors (except administrators) will be redirected to the under construction page</li>
                        <li>Administrators can still access the full site</li>
                        <li><strong>The /agent page remains accessible to EVERYONE</strong> (individual agent profiles will still be blocked)</li>
                        <li>Perfect for updates, testing, or development work</li>
                    </ul>
                </div>

                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="hph_maintenance_toggle">
                    <input type="hidden" name="nonce" value="<?php echo $nonce; ?>">
                    <input type="hidden" name="current_status" value="<?php echo $is_enabled ? '1' : '0'; ?>">

                    <?php if ($is_enabled): ?>
                        <button type="submit" class="button button-primary button-large"
                                style="background: #198754; border-color: #198754;">
                            <span class="dashicons dashicons-yes" style="margin-top: 3px;"></span>
                            Turn OFF Maintenance Mode
                        </button>
                    <?php else: ?>
                        <button type="submit" class="button button-primary button-large"
                                style="background: #dc3545; border-color: #dc3545;"
                                onclick="return confirm('Are you sure you want to enable maintenance mode? All visitors will see the under construction page.')">
                            <span class="dashicons dashicons-warning" style="margin-top: 3px;"></span>
                            Turn ON Maintenance Mode
                        </button>
                    <?php endif; ?>
                </form>

                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                    <p><strong>Quick Access:</strong></p>
                    <a href="<?php echo admin_url('themes.php?page=page-templates'); ?>" class="button">
                        Manage Page Templates
                    </a>
                    <a href="<?php echo home_url('/?preview_maintenance=1'); ?>" class="button" target="_blank">
                        Preview Under Construction Page
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Handle maintenance mode toggle
     */
    public function handle_toggle() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'hph_maintenance_toggle') || !current_user_can('manage_options')) {
            wp_die('Security check failed.');
        }

        $current_status = !empty($_POST['current_status']);

        if ($current_status) {
            self::disable();
            $message = 'Maintenance mode has been <strong>disabled</strong>. Your site is now live.';
            $type = 'success';
        } else {
            self::enable();
            $message = 'Maintenance mode has been <strong>enabled</strong>. Visitors will see the under construction page.';
            $type = 'warning';
        }

        // Redirect back with message
        $redirect_url = add_query_arg(array(
            'page' => 'hph-maintenance-mode',
            'message' => urlencode($message),
            'type' => $type
        ), admin_url('options-general.php'));

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Add admin bar indicator
     */
    public function add_admin_bar_indicator($wp_admin_bar) {
        if (!self::is_enabled() || !current_user_can('manage_options')) {
            return;
        }

        $wp_admin_bar->add_node(array(
            'id' => 'hph-maintenance-indicator',
            'title' => '<span style="color: #ff6b6b;">🚧 Maintenance Mode</span>',
            'href' => admin_url('options-general.php?page=hph-maintenance-mode'),
            'meta' => array(
                'title' => 'Click to manage maintenance mode'
            )
        ));
    }

    /**
     * Check for maintenance mode and redirect if needed
     */
    public function check_maintenance_mode() {
        // Skip if maintenance mode is disabled
        if (!self::is_enabled()) {
            return;
        }

        // Allow admins to access the site
        if (current_user_can('manage_options')) {
            return;
        }

        // Allow preview for admins
        if (isset($_GET['preview_maintenance']) && current_user_can('manage_options')) {
            return;
        }

        // Skip if already on a maintenance/construction page
        if (is_page_template('page-under-construction.php') ||
            strpos($_SERVER['REQUEST_URI'], 'maintenance') !== false) {
            return;
        }

        // Allow ANYONE to access only the /agent slug (not individual agent pages)
        if ($_SERVER['REQUEST_URI'] === '/agent' || $_SERVER['REQUEST_URI'] === '/agent/') {
            return;
        }

        // Skip AJAX, cron, and API requests
        if (wp_doing_ajax() || wp_doing_cron() ||
            (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        // Skip admin area and login pages
        if (is_admin() || strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== false ||
            strpos($_SERVER['REQUEST_URI'], 'wp-login') !== false) {
            return;
        }

        // Find or create a maintenance page
        $maintenance_page = $this->get_or_create_maintenance_page();

        if ($maintenance_page) {
            // Set 503 status first, then redirect
            status_header(503);
            wp_redirect(get_permalink($maintenance_page), 302);
            exit;
        }

        // Fallback to simple message if no page found
        $this->show_simple_maintenance_page();
    }

    /**
     * Get or create maintenance page using under construction template
     */
    private function get_or_create_maintenance_page() {
        // Look for existing maintenance/construction page
        $existing_page = get_page_by_path('maintenance');
        if (!$existing_page) {
            $existing_page = get_page_by_path('under-construction');
        }

        // Check if any page uses the under construction template
        if (!$existing_page) {
            $pages = get_pages(array(
                'meta_key' => '_wp_page_template',
                'meta_value' => 'page-under-construction.php',
                'number' => 1
            ));
            if (!empty($pages)) {
                $existing_page = $pages[0];
            }
        }

        // Create maintenance page if none exists
        if (!$existing_page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'Site Maintenance',
                'post_content' => 'We are currently performing scheduled maintenance.',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'maintenance',
                'meta_input' => array(
                    '_wp_page_template' => 'page-under-construction.php'
                )
            ));

            if (!is_wp_error($page_id)) {
                return $page_id;
            }
        }

        return $existing_page ? $existing_page->ID : false;
    }

    /**
     * Show simple maintenance page if template fails
     */
    private function show_simple_maintenance_page() {
        status_header(503);
        nocache_headers();

        $site_name = get_bloginfo('name');
        $contact_email = get_option('admin_email', 'cheers@theparkergroup.com');

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Maintenance - <?php echo esc_html($site_name); ?></title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                       margin: 0; padding: 40px; text-align: center; background: #f8f9fa; }
                .container { max-width: 600px; margin: 0 auto; background: white;
                           padding: 60px 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #333; margin-bottom: 20px; }
                p { color: #666; line-height: 1.6; margin-bottom: 30px; }
                .contact { background: #f8f9fa; padding: 20px; border-radius: 4px; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🚧 Site Under Maintenance</h1>
                <p>We're currently performing scheduled maintenance to improve your experience.
                   Please check back shortly.</p>
                <div class="contact">
                    <strong>Need immediate assistance?</strong><br>
                    Email: <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Show admin notice when maintenance mode is active
     */
    public function show_admin_notice() {
        if (!self::is_enabled() || !current_user_can('manage_options')) {
            return;
        }

        // Show success/error messages from toggle action
        if (isset($_GET['message']) && isset($_GET['type'])) {
            $message = urldecode($_GET['message']);
            $type = $_GET['type'] === 'success' ? 'notice-success' : 'notice-warning';
            echo '<div class="notice ' . $type . ' is-dismissible"><p>' . $message . '</p></div>';
        }

        // Always show maintenance mode indicator
        echo '<div class="notice notice-warning">
                <p><strong>🚧 Maintenance Mode Active:</strong>
                   Your site is currently showing the under construction page to all visitors.
                   <a href="' . admin_url('options-general.php?page=hph-maintenance-mode') . '">Manage Maintenance Mode</a>
                </p>
              </div>';
    }
}

// Initialize maintenance mode
new HPH_Maintenance_Mode();