<?php
/**
 * Overview Dashboard Section
 * 
 * Displays key metrics and summary information for agents
 *
 * @package HappyPlace\Dashboard
 */

namespace HappyPlace\Dashboard;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Overview_Section {
    
    private $dashboard_manager;
    
    /**
     * Constructor
     */
    public function __construct($dashboard_manager) {
        $this->dashboard_manager = $dashboard_manager;
        hp_log('Overview Section initialized', 'debug', 'DASHBOARD');
    }
    
    /**
     * Render the overview section
     */
    public function render(): string {
        ob_start();
        ?>
        <div class="hpt-dashboard-section hpt-overview-section">
            <div class="hpt-section-header">
                <h2><?php _e('Dashboard Overview', 'happy-place'); ?></h2>
                <p class="hpt-section-description"><?php _e('Your key metrics and recent activity', 'happy-place'); ?></p>
            </div>
            
            <div class="hpt-overview-grid">
                <div class="hpt-stat-card">
                    <div class="hpt-stat-icon">
                        <i class="dashicons dashicons-admin-home"></i>
                    </div>
                    <div class="hpt-stat-content">
                        <h3><?php echo $this->get_active_listings_count(); ?></h3>
                        <p><?php _e('Active Listings', 'happy-place'); ?></p>
                    </div>
                </div>
                
                <div class="hpt-stat-card">
                    <div class="hpt-stat-icon">
                        <i class="dashicons dashicons-money-alt"></i>
                    </div>
                    <div class="hpt-stat-content">
                        <h3><?php echo $this->format_currency($this->get_total_volume()); ?></h3>
                        <p><?php _e('Total Volume', 'happy-place'); ?></p>
                    </div>
                </div>
                
                <div class="hpt-stat-card">
                    <div class="hpt-stat-icon">
                        <i class="dashicons dashicons-groups"></i>
                    </div>
                    <div class="hpt-stat-content">
                        <h3><?php echo $this->get_leads_count(); ?></h3>
                        <p><?php _e('This Month', 'happy-place'); ?></p>
                    </div>
                </div>
                
                <div class="hpt-stat-card">
                    <div class="hpt-stat-icon">
                        <i class="dashicons dashicons-visibility"></i>
                    </div>
                    <div class="hpt-stat-content">
                        <h3><?php echo $this->get_total_views(); ?></h3>
                        <p><?php _e('Total Views', 'happy-place'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="hpt-overview-charts">
                <div class="hpt-chart-container">
                    <h3><?php _e('Listing Performance', 'happy-place'); ?></h3>
                    <canvas id="hpt-performance-chart" width="400" height="200"></canvas>
                </div>
                
                <div class="hpt-recent-activity">
                    <h3><?php _e('Recent Activity', 'happy-place'); ?></h3>
                    <div class="hpt-activity-list">
                        <?php echo $this->get_recent_activity(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get agent's active listings count
     */
    private function get_active_listings_count(): int {
        $agent_id = $this->get_current_agent_id();
        
        if (!$agent_id) {
            return 0;
        }
        
        $listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'listing_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ],
                [
                    'key' => 'listing_status',
                    'value' => 'active',
                    'compare' => '='
                ]
            ]
        ]);
        
        return count($listings);
    }
    
    /**
     * Get agent's total sales volume
     */
    private function get_total_volume(): float {
        $agent_id = $this->get_current_agent_id();
        
        if (!$agent_id) {
            return 0;
        }
        
        $sold_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'listing_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ],
                [
                    'key' => 'listing_status',
                    'value' => 'sold',
                    'compare' => '='
                ]
            ]
        ]);
        
        $total_volume = 0;
        foreach ($sold_listings as $listing_id) {
            $price = get_post_meta($listing_id, 'price', true);
            if ($price) {
                $total_volume += floatval($price);
            }
        }
        
        return $total_volume;
    }
    
    /**
     * Get leads count for current month
     */
    private function get_leads_count(): int {
        $agent_id = $this->get_current_agent_id();
        
        if (!$agent_id) {
            return 0;
        }
        
        $start_of_month = date('Y-m-01 00:00:00');
        $end_of_month = date('Y-m-t 23:59:59');
        
        $leads = get_posts([
            'post_type' => 'lead',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'date_query' => [
                [
                    'after' => $start_of_month,
                    'before' => $end_of_month,
                    'inclusive' => true
                ]
            ],
            'meta_query' => [
                [
                    'key' => 'assigned_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ]
            ]
        ]);
        
        return count($leads);
    }
    
    /**
     * Get total views across all agent's listings
     */
    private function get_total_views(): int {
        $agent_id = $this->get_current_agent_id();
        
        if (!$agent_id) {
            return 0;
        }
        
        $listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'listing_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ]
            ]
        ]);
        
        $total_views = 0;
        foreach ($listings as $listing_id) {
            $views = get_post_meta($listing_id, '_listing_views', true);
            if ($views) {
                $total_views += intval($views);
            }
        }
        
        return $total_views;
    }
    
    /**
     * Get recent activity for the agent
     */
    private function get_recent_activity(): string {
        $agent_id = $this->get_current_agent_id();
        
        if (!$agent_id) {
            return '<p>' . __('No recent activity', 'happy-place') . '</p>';
        }
        
        $activities = [];
        
        // Get recent listings
        $recent_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => 3,
            'meta_query' => [
                [
                    'key' => 'listing_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ]
            ]
        ]);
        
        foreach ($recent_listings as $listing) {
            $status = get_post_meta($listing->ID, 'listing_status', true) ?: 'active';
            $activities[] = [
                'date' => $listing->post_date,
                'action' => sprintf(__('Listed property: %s', 'happy-place'), $listing->post_title),
                'type' => 'listing',
                'status' => $status
            ];
        }
        
        // Get recent leads
        $recent_leads = get_posts([
            'post_type' => 'lead',
            'posts_per_page' => 3,
            'meta_query' => [
                [
                    'key' => 'assigned_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ]
            ]
        ]);
        
        foreach ($recent_leads as $lead) {
            $activities[] = [
                'date' => $lead->post_date,
                'action' => sprintf(__('New lead: %s', 'happy-place'), $lead->post_title),
                'type' => 'lead',
                'status' => 'new'
            ];
        }
        
        // Sort by date
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        // Limit to 5 most recent
        $activities = array_slice($activities, 0, 5);
        
        if (empty($activities)) {
            return '<p>' . __('No recent activity', 'happy-place') . '</p>';
        }
        
        $output = '';
        foreach ($activities as $activity) {
            $time_ago = human_time_diff(strtotime($activity['date']), current_time('timestamp'));
            $output .= sprintf(
                '<div class="hpt-activity-item hpt-activity-%s">
                    <div class="hpt-activity-icon">
                        <i class="dashicons dashicons-%s"></i>
                    </div>
                    <div class="hpt-activity-content">
                        <p>%s</p>
                        <span class="hpt-activity-time">%s ago</span>
                    </div>
                </div>',
                esc_attr($activity['type']),
                $activity['type'] === 'listing' ? 'admin-home' : 'groups',
                esc_html($activity['action']),
                esc_html($time_ago)
            );
        }
        
        return $output;
    }
    
    /**
     * Get current agent ID
     */
    private function get_current_agent_id(): int {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return 0;
        }
        
        // Check if user has linked agent profile
        $agent_id = get_user_meta($user_id, 'hpt_agent_id', true);
        
        if ($agent_id) {
            return intval($agent_id);
        }
        
        // Try to find agent by email
        $user = get_userdata($user_id);
        if ($user) {
            $agents = get_posts([
                'post_type' => 'agent',
                'posts_per_page' => 1,
                'meta_query' => [
                    [
                        'key' => 'email',
                        'value' => $user->user_email,
                        'compare' => '='
                    ]
                ]
            ]);
            
            if (!empty($agents)) {
                $agent_id = $agents[0]->ID;
                // Cache the relationship
                update_user_meta($user_id, 'hpt_agent_id', $agent_id);
                return $agent_id;
            }
        }
        
        return 0;
    }
    
    /**
     * Format currency value
     */
    private function format_currency($amount): string {
        if ($amount >= 1000000) {
            return '$' . number_format($amount / 1000000, 1) . 'M';
        } elseif ($amount >= 1000) {
            return '$' . number_format($amount / 1000, 0) . 'K';
        } else {
            return '$' . number_format($amount, 0);
        }
    }
    
    /**
     * Get section data for AJAX requests
     */
    public function get_data(): array {
        return [
            'active_listings' => $this->get_active_listings_count(),
            'total_volume' => $this->get_total_volume(),
            'leads_count' => $this->get_leads_count(),
            'total_views' => $this->get_total_views(),
            'recent_activity' => $this->get_recent_activity()
        ];
    }
}