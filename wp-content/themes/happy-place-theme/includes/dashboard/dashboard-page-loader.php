<?php
/**
 * Dashboard Page Loader
 * Handles AJAX requests for loading dashboard sections
 * 
 * @package HappyPlaceTheme
 * @subpackage Dashboard
 * 
 * File Location: /wp-content/themes/happy-place/includes/dashboard/dashboard-page-loader.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard Page Loader Class
 */
class HPH_Dashboard_Page_Loader {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_hph_load_dashboard_page', array($this, 'load_dashboard_page'));
        add_action('wp_ajax_nopriv_hph_load_dashboard_page', array($this, 'load_dashboard_page'));
    }
    
    /**
     * Load dashboard page via AJAX
     */
    public function load_dashboard_page() {
        try {
            // Debug logging
            error_log('HPH Dashboard Page Loader: AJAX request received');
            
            // Simple success test first
            if (!isset($_POST['page'])) {
                wp_send_json_error('No page parameter provided');
                return;
            }
            
            $page = sanitize_text_field($_POST['page']);
            $template = isset($_POST['template']) ? sanitize_text_field($_POST['template']) : 'dashboard-' . $page;
            
            error_log('HPH Dashboard Page Loader: Loading page: ' . $page . ', template: ' . $template);
            
            // SECURITY: Verify nonce (required by structure guide)
            if (!check_ajax_referer('wp_rest', 'nonce', false)) {
                error_log('HPH Dashboard Page Loader: Nonce verification failed');
                wp_send_json_error('Security check failed');
                return;
            }
            
            // SECURITY: Check permissions (required by structure guide)
            if (!is_user_logged_in()) {
                wp_send_json_error('User not logged in');
                return;
            }
            
            if (!current_user_can('edit_posts')) {
                wp_send_json_error('Insufficient permissions');
                return;
            }
        
        // Validate page
        $allowed_pages = array(
            'dashboard',
            'listings',
            'open-houses',
            'leads',
            'transactions',
            'profile',
            'marketing',
            'cma',
            'analytics',
            'resources',
            'settings'
        );
        
        if (!in_array($page, $allowed_pages)) {
            wp_send_json_error('Invalid page');
        }
        
        // Get page content
        $content = $this->get_page_content($page, $template);
        
        // Get page data
        $data = array(
            'html' => $content,
            'subtitle' => $this->get_page_subtitle($page),
            'timestamp' => time(),
            'user_data' => $this->get_user_data_for_page($page)
        );
        
        wp_send_json_success($data);
            
        } catch (Exception $e) {
            error_log('HPH Dashboard Page Loader: Exception: ' . $e->getMessage());
            wp_send_json_error('Server error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get page content
     */
    private function get_page_content($page, $template) {
        ob_start();
        
        $template_file = get_template_directory() . '/templates/dashboard/' . $template . '.php';
        
        if (file_exists($template_file)) {
            // Set up global variables for the template
            global $current_user;
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            
            // Include the template
            include $template_file;
        } else {
            // Return default content for pages not yet created
            $this->render_placeholder_content($page);
        }
        
        return ob_get_clean();
    }
    
    /**
     * Get page subtitle
     */
    private function get_page_subtitle($page) {
        $current_user = wp_get_current_user();
        $first_name = $current_user->first_name ?: $current_user->display_name;
        
        $subtitles = array(
            'dashboard' => 'Welcome back, ' . $first_name . '!',
            'listings' => 'Manage your property listings',
            'open-houses' => 'Schedule and manage open houses',
            'leads' => 'Track and nurture your leads',
            'transactions' => 'Monitor your deals in progress',
            'profile' => 'Update your professional profile',
            'marketing' => 'Create marketing materials',
            'cma' => 'Generate comparative market analyses',
            'analytics' => 'View your performance metrics',
            'resources' => 'Access training and resources',
            'settings' => 'Manage your account settings'
        );
        
        return isset($subtitles[$page]) ? $subtitles[$page] : '';
    }
    
    /**
     * Get user data for page
     */
    private function get_user_data_for_page($page) {
        $user_id = get_current_user_id();
        $data = array();
        
        switch($page) {
            case 'dashboard':
                $data = array(
                    'stats' => $this->get_dashboard_stats($user_id),
                    'recent_activity' => $this->get_recent_activity($user_id),
                    'upcoming_events' => $this->get_upcoming_events($user_id)
                );
                break;
                
            case 'listings':
                $data = array(
                    'total_listings' => $this->count_user_listings($user_id),
                    'active_listings' => $this->count_user_listings($user_id, 'active'),
                    'pending_listings' => $this->count_user_listings($user_id, 'pending'),
                    'sold_listings' => $this->count_user_listings($user_id, 'sold')
                );
                break;
                
            case 'leads':
                $data = array(
                    'total_leads' => $this->count_user_leads($user_id),
                    'hot_leads' => $this->count_user_leads($user_id, 'hot'),
                    'new_leads' => $this->count_new_leads($user_id)
                );
                break;
                
            case 'analytics':
                $data = array(
                    'performance_metrics' => $this->get_performance_metrics($user_id),
                    'conversion_rate' => $this->get_conversion_rate($user_id)
                );
                break;
        }
        
        return $data;
    }
    
    /**
     * Render placeholder content for uncreated pages
     */
    private function render_placeholder_content($page) {
        $page_names = array(
            'open-houses' => 'Open Houses',
            'leads' => 'Lead Management',
            'transactions' => 'Transactions',
            'profile' => 'My Profile',
            'marketing' => 'Marketing Suite',
            'cma' => 'CMA Generator',
            'analytics' => 'Analytics',
            'resources' => 'Resources',
            'settings' => 'Settings'
        );
        
        $page_name = isset($page_names[$page]) ? $page_names[$page] : ucfirst($page);
        ?>
        <div class="placeholder-page">
            <div class="placeholder-icon">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="currentColor" opacity="0.3">
                    <path d="M32 8l4 4v8h8l4 4v32H16V24l4-4h8v-8l4-4z"/>
                </svg>
            </div>
            <h2 class="placeholder-title"><?php echo esc_html($page_name); ?></h2>
            <p class="placeholder-text">This section is coming soon!</p>
            <p class="placeholder-description">
                We're working hard to bring you this feature. Check back soon for updates.
            </p>
            
            <?php if ($page === 'open-houses'): ?>
                <div class="coming-soon-features">
                    <h3>Coming Features:</h3>
                    <ul>
                        <li>Schedule open houses for your listings</li>
                        <li>Manage visitor registration</li>
                        <li>Send automated reminders</li>
                        <li>Track attendance and feedback</li>
                        <li>Generate open house flyers</li>
                    </ul>
                </div>
            <?php elseif ($page === 'leads'): ?>
                <div class="coming-soon-features">
                    <h3>Coming Features:</h3>
                    <ul>
                        <li>Lead capture forms</li>
                        <li>FollowUpBoss CRM integration</li>
                        <li>Lead scoring and prioritization</li>
                        <li>Automated follow-up sequences</li>
                        <li>Lead source tracking</li>
                    </ul>
                </div>
            <?php elseif ($page === 'analytics'): ?>
                <div class="coming-soon-features">
                    <h3>Coming Features:</h3>
                    <ul>
                        <li>Performance dashboards</li>
                        <li>Sales metrics and trends</li>
                        <li>Lead conversion analytics</li>
                        <li>Marketing ROI tracking</li>
                        <li>Custom reports</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Helper methods for data retrieval
     */
    private function get_dashboard_stats($user_id) {
        return array(
            'active_listings' => $this->count_user_listings($user_id, 'active'),
            'closed_this_month' => $this->count_closed_transactions($user_id),
            'new_leads' => $this->count_new_leads($user_id),
            'open_houses' => $this->count_upcoming_open_houses($user_id)
        );
    }
    
    private function count_user_listings($user_id, $status = 'all') {
        $args = array(
            'post_type' => 'listing',
            'author' => $user_id,
            'post_status' => 'publish',
            'posts_per_page' => -1
        );
        
        if ($status !== 'all') {
            $args['meta_query'] = array(
                array(
                    'key' => 'listing_status',
                    'value' => $status
                )
            );
        }
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function count_user_leads($user_id, $status = 'all') {
        $args = array(
            'post_type' => 'lead',
            'author' => $user_id,
            'posts_per_page' => -1
        );
        
        if ($status !== 'all') {
            $args['meta_query'] = array(
                array(
                    'key' => 'lead_status',
                    'value' => $status
                )
            );
        }
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function count_new_leads($user_id) {
        $args = array(
            'post_type' => 'lead',
            'author' => $user_id,
            'date_query' => array(
                array(
                    'after' => '1 week ago'
                )
            ),
            'posts_per_page' => -1
        );
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function count_closed_transactions($user_id) {
        $args = array(
            'post_type' => 'transaction',
            'author' => $user_id,
            'meta_query' => array(
                array(
                    'key' => 'status',
                    'value' => 'closed'
                ),
                array(
                    'key' => 'close_date',
                    'value' => date('Y-m-01'),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            ),
            'posts_per_page' => -1
        );
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function count_upcoming_open_houses($user_id) {
        $args = array(
            'post_type' => 'open_house',
            'author' => $user_id,
            'meta_query' => array(
                array(
                    'key' => 'event_date',
                    'value' => current_time('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            ),
            'posts_per_page' => -1
        );
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function get_recent_activity($user_id, $limit = 10) {
        // Implementation for recent activity
        return array();
    }
    
    private function get_upcoming_events($user_id, $limit = 5) {
        // Implementation for upcoming events
        return array();
    }
    
    private function get_performance_metrics($user_id) {
        // Implementation for performance metrics
        return array();
    }
    
    private function get_conversion_rate($user_id) {
        // Implementation for conversion rate
        return 0;
    }
}

// Initialize the page loader
new HPH_Dashboard_Page_Loader();