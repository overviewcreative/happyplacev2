<?php
/**
 * Dashboard Service
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HPH_Dashboard
 */
class HPH_Dashboard implements HPH_Service {
    
    /**
     * Initialize service
     */
    public function init() {
        // Let the asset service handle all asset loading automatically
        // We just need to provide dashboard-specific functionality
        add_action('wp_head', array($this, 'dashboard_head'));
    }
    
    /**
     * Get service identifier
     */
    public function get_service_id() {
        return 'dashboard';
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
        return array('assets', 'router');
    }
    
    /**
     * Check if we're on a dashboard page
     */
    public function is_dashboard_page() {
        return get_query_var('agent_dashboard') || get_query_var('dashboard_page');
    }
    
    /**
     * Get current dashboard section
     */
    public function get_current_section() {
        // Check for section in URL parameters
        $section = get_query_var('dashboard_page', '');
        if (empty($section)) {
            $section = isset($_GET['dashboard_page']) ? sanitize_text_field($_GET['dashboard_page']) : 'overview';
        }
        return $section;
    }
    
    /**
     * Get dashboard sections
     */
    public function get_dashboard_sections() {
        $sections = array(
            'overview' => array(
                'title' => __('Overview', 'happy-place-theme'),
                'icon' => 'fas fa-tachometer-alt',
                'capability' => 'read',
                'template' => 'dashboard-overview',
            ),
            'listings' => array(
                'title' => __('My Listings', 'happy-place-theme'),
                'icon' => 'fas fa-home', 
                'capability' => 'edit_posts',
                'template' => 'dashboard-listings',
            ),
            'leads' => array(
                'title' => __('Leads', 'happy-place-theme'),
                'icon' => 'fas fa-users',
                'capability' => 'edit_posts', 
                'template' => 'dashboard-leads',
            ),
            'transactions' => array(
                'title' => __('Transactions', 'happy-place-theme'),
                'icon' => 'fas fa-handshake',
                'capability' => 'edit_posts',
                'template' => 'dashboard-transactions',
            ),
            'open-houses' => array(
                'title' => __('Open Houses', 'happy-place-theme'),
                'icon' => 'fas fa-calendar',
                'capability' => 'edit_posts',
                'template' => 'dashboard-open-houses',
            ),
        );
        
        return apply_filters('hph_dashboard_sections', $sections);
    }
    
    /**
     * Dashboard head content
     */
    public function dashboard_head() {
        if ($this->is_dashboard_page()) {
            echo '<meta name="robots" content="noindex, nofollow">' . "\n";
        }
    }
    
    /**
     * Get user dashboard stats
     */
    public function get_user_stats($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $stats = array(
            'active_listings' => $this->count_user_listings($user_id, 'active'),
            'pending_listings' => $this->count_user_listings($user_id, 'pending'),
            'sold_listings' => $this->count_user_listings($user_id, 'sold'),
            'total_leads' => $this->count_user_leads($user_id),
            'new_leads' => $this->count_user_leads($user_id, 'new'),
            'active_transactions' => $this->count_user_transactions($user_id, 'active'),
            'upcoming_open_houses' => $this->count_upcoming_open_houses($user_id),
        );
        
        return apply_filters('hph_dashboard_stats', $stats, $user_id);
    }
    
    /**
     * Count user listings by status
     */
    private function count_user_listings($user_id, $status = 'all') {
        $args = array(
            'post_type' => 'listing',
            'author' => $user_id,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
        );
        
        if ($status !== 'all') {
            $args['meta_query'] = array(
                array(
                    'key' => 'listing_status',
                    'value' => $status,
                    'compare' => '='
                )
            );
        }
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    /**
     * Count user leads by status
     */
    private function count_user_leads($user_id, $status = 'all') {
        $args = array(
            'post_type' => 'lead',
            'author' => $user_id,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
        );
        
        if ($status !== 'all') {
            $args['meta_query'] = array(
                array(
                    'key' => 'lead_status',
                    'value' => $status,
                    'compare' => '='
                )
            );
        }
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    /**
     * Count user transactions by status
     */
    private function count_user_transactions($user_id, $status = 'all') {
        $args = array(
            'post_type' => 'transaction',
            'author' => $user_id,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
        );
        
        if ($status !== 'all') {
            $args['meta_query'] = array(
                array(
                    'key' => 'status',
                    'value' => $status,
                    'compare' => '='
                )
            );
        }
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    /**
     * Count upcoming open houses
     */
    private function count_upcoming_open_houses($user_id) {
        $args = array(
            'post_type' => 'open_house',
            'author' => $user_id,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'meta_query' => array(
                array(
                    'key' => 'event_date',
                    'value' => current_time('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            )
        );
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
}
