<?php
/**
 * User Engagement Service
 * 
 * Tracks user behavior, calculates engagement scores,
 * and provides analytics for lead scoring
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class UserEngagementService extends Service {
    
    protected string $name = 'user_engagement_service';
    protected string $version = '4.0.0';
    
    /**
     * Activity table name
     */
    private string $table_name;
    
    /**
     * Scoring system for different actions
     */
    private array $scoring_actions = [
        'registration' => 10,
        'profile_complete' => 5,
        'favorite_added' => 5,
        'favorite_removed' => -2,
        'search_saved' => 8,
        'listing_viewed' => 2,
        'listing_inquiry' => 15,
        'agent_contact' => 12,
        'phone_call_made' => 20,
        'showing_requested' => 18,
        'document_downloaded' => 7,
        'mortgage_calculator_used' => 6,
        'property_shared' => 4,
        'email_opened' => 2,
        'email_clicked' => 3,
        'login' => 1,
        'return_visit' => 1,
        'time_on_site' => 0 // Calculated differently
    ];
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'user_activity';
        
        // Register hooks for automatic tracking
        $this->register_tracking_hooks();
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
        
        // Register cron for engagement analysis
        $this->register_cron_hooks();
        
        $this->initialized = true;
        $this->log('User Engagement Service initialized successfully');
    }
    
    /**
     * Register automatic tracking hooks
     */
    private function register_tracking_hooks(): void {
        // User lifecycle events
        add_action('wp_login', [$this, 'track_user_login'], 10, 2);
        add_action('user_register', [$this, 'track_user_registration']);
        add_action('profile_update', [$this, 'track_profile_update'], 10, 2);
        
        // Content interactions
        add_action('wp_ajax_hph_track_listing_view', [$this, 'track_listing_view']);
        add_action('wp_ajax_nopriv_hph_track_listing_view', [$this, 'track_listing_view']);
        
        // Lead and form submissions (hook into existing system)
        add_action('hp_lead_created', [$this, 'track_lead_submission'], 10, 2);
        
        // Site engagement
        add_action('wp_footer', [$this, 'enqueue_engagement_tracker']);
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        add_action('wp_ajax_track_engagement', [$this, 'ajax_track_engagement']);
        add_action('wp_ajax_nopriv_track_engagement', [$this, 'ajax_track_engagement']);
        
        add_action('wp_ajax_get_user_engagement', [$this, 'ajax_get_user_engagement']);
        add_action('wp_ajax_get_engagement_leaderboard', [$this, 'ajax_get_engagement_leaderboard']);
    }
    
    /**
     * Register cron hooks
     */
    private function register_cron_hooks(): void {
        // Schedule engagement analysis
        if (!wp_next_scheduled('hp_analyze_user_engagement')) {
            wp_schedule_event(time(), 'daily', 'hp_analyze_user_engagement');
        }
        
        add_action('hp_analyze_user_engagement', [$this, 'analyze_daily_engagement']);
    }
    
    /**
     * Track user activity
     */
    public function track_activity(int $user_id, string $action, int $object_id = null, string $object_type = null, array $metadata = []): bool {
        global $wpdb;
        
        // Calculate points for this action
        $points = $this->calculate_action_points($action, $metadata);
        
        // Prepare data
        $data = [
            'user_id' => $user_id,
            'action' => sanitize_key($action),
            'object_id' => $object_id,
            'object_type' => $object_type ? sanitize_key($object_type) : null,
            'points' => $points,
            'metadata' => !empty($metadata) ? wp_json_encode($metadata) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert($this->table_name, $data);
        
        if ($result !== false) {
            // Update user's total engagement score
            $this->update_user_engagement_score($user_id);
            
            // Check for engagement milestones
            $this->check_engagement_milestones($user_id, $action, $points);
            
            return true;
        }
        
        $this->log("Failed to track activity: User {$user_id}, Action {$action} - " . $wpdb->last_error, 'error');
        return false;
    }
    
    /**
     * Calculate points for an action
     */
    private function calculate_action_points(string $action, array $metadata): int {
        $base_points = $this->scoring_actions[$action] ?? 0;
        
        // Special calculations for certain actions
        switch ($action) {
            case 'time_on_site':
                // Award 1 point per minute, capped at 10 points
                $minutes = intval($metadata['minutes'] ?? 0);
                return min($minutes, 10);
                
            case 'listing_viewed':
                // Extra points for longer view times
                $view_time = intval($metadata['view_time_seconds'] ?? 0);
                $bonus = $view_time > 60 ? 1 : 0; // Bonus for viewing > 1 minute
                return $base_points + $bonus;
                
            case 'listing_inquiry':
                // Extra points for detailed inquiries
                $message_length = intval($metadata['message_length'] ?? 0);
                $bonus = $message_length > 100 ? 3 : 0; // Bonus for detailed messages
                return $base_points + $bonus;
                
            default:
                return $base_points;
        }
    }
    
    /**
     * Update user's total engagement score
     */
    private function update_user_engagement_score(int $user_id): void {
        global $wpdb;
        
        // Calculate total score from last 30 days
        $total_score = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(points) FROM {$this->table_name} 
             WHERE user_id = %d AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            $user_id
        ));
        
        // Update user meta
        update_user_meta($user_id, 'engagement_score', $total_score);
        update_user_meta($user_id, 'engagement_updated', current_time('mysql'));
        
        // Also update lead score if user was converted from lead
        $this->sync_lead_score($user_id, $total_score);
    }
    
    /**
     * Sync engagement score with lead record
     */
    private function sync_lead_score(int $user_id, int $engagement_score): void {
        global $wpdb;
        
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        // Check if leads table exists and has user_id column
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$leads_table'") == $leads_table;
        if (!$table_exists) {
            return;
        }
        
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$leads_table} LIKE 'user_id'");
        if (empty($column_exists)) {
            return;
        }
        
        // Update lead score
        $wpdb->update(
            $leads_table,
            ['lead_score' => min($engagement_score, 100)], // Cap at 100
            ['user_id' => $user_id],
            ['%d'],
            ['%d']
        );
    }
    
    /**
     * Check for engagement milestones
     */
    private function check_engagement_milestones(int $user_id, string $action, int $points): void {
        $current_score = (int) get_user_meta($user_id, 'engagement_score', true);
        
        // Define milestones
        $milestones = [
            25 => 'engaged_visitor',
            50 => 'active_prospect',
            75 => 'hot_lead',
            100 => 'ready_to_buy'
        ];
        
        foreach ($milestones as $threshold => $milestone) {
            if ($current_score >= $threshold && !get_user_meta($user_id, "milestone_{$milestone}", true)) {
                // Award milestone
                update_user_meta($user_id, "milestone_{$milestone}", current_time('mysql'));
                
                // Fire action for other systems (like FollowUp Boss sync)
                do_action('hp_user_engagement_milestone', $user_id, $milestone, $current_score);
                
                $this->log("User {$user_id} reached milestone: {$milestone} (Score: {$current_score})");
            }
        }
    }
    
    /**
     * Get user engagement summary
     */
    public function get_user_engagement(int $user_id, int $days = 30): array {
        global $wpdb;
        
        $sql = "SELECT 
                    action,
                    COUNT(*) as count,
                    SUM(points) as total_points,
                    MAX(created_at) as last_action
                FROM {$this->table_name} 
                WHERE user_id = %d 
                AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY action
                ORDER BY total_points DESC";
        
        $activities = $wpdb->get_results($wpdb->prepare($sql, $user_id, $days), ARRAY_A);
        
        $total_score = (int) get_user_meta($user_id, 'engagement_score', true);
        $total_activities = array_sum(array_column($activities, 'count'));
        
        return [
            'user_id' => $user_id,
            'total_score' => $total_score,
            'total_activities' => $total_activities,
            'activities' => $activities,
            'level' => $this->get_engagement_level($total_score),
            'period_days' => $days
        ];
    }
    
    /**
     * Get engagement level based on score
     */
    private function get_engagement_level(int $score): string {
        if ($score >= 100) return 'ready_to_buy';
        if ($score >= 75) return 'hot_lead';
        if ($score >= 50) return 'active_prospect';
        if ($score >= 25) return 'engaged_visitor';
        return 'new_visitor';
    }
    
    /**
     * Get top engaged users
     */
    public function get_engagement_leaderboard(int $limit = 10, int $days = 30): array {
        global $wpdb;
        
        $sql = "SELECT 
                    u.user_id,
                    u.user_login,
                    u.user_email,
                    u.display_name,
                    SUM(a.points) as total_score,
                    COUNT(a.id) as activity_count,
                    MAX(a.created_at) as last_activity
                FROM {$wpdb->users} u
                JOIN {$this->table_name} a ON u.ID = a.user_id
                WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY u.ID
                ORDER BY total_score DESC, activity_count DESC
                LIMIT %d";
        
        return $wpdb->get_results($wpdb->prepare($sql, $days, $limit), ARRAY_A);
    }
    
    /**
     * Track user login
     */
    public function track_user_login(string $user_login, \WP_User $user): void {
        $this->track_activity($user->ID, 'login');
        
        // Check if it's a return visit
        $last_login = get_user_meta($user->ID, 'last_login', true);
        if ($last_login && (time() - strtotime($last_login)) > DAY_IN_SECONDS) {
            $this->track_activity($user->ID, 'return_visit');
        }
        
        update_user_meta($user->ID, 'last_login', current_time('mysql'));
    }
    
    /**
     * Track user registration
     */
    public function track_user_registration(int $user_id): void {
        $this->track_activity($user_id, 'registration');
    }
    
    /**
     * Track profile updates
     */
    public function track_profile_update(int $user_id, \WP_User $old_user_data): void {
        // Check if profile is more complete now
        $user = get_user_by('id', $user_id);
        $completeness = $this->calculate_profile_completeness($user);
        
        if ($completeness >= 80) { // 80% complete threshold
            $already_awarded = get_user_meta($user_id, 'profile_complete_awarded', true);
            if (!$already_awarded) {
                $this->track_activity($user_id, 'profile_complete');
                update_user_meta($user_id, 'profile_complete_awarded', true);
            }
        }
    }
    
    /**
     * Calculate profile completeness
     */
    private function calculate_profile_completeness(\WP_User $user): int {
        $fields = [
            'first_name' => get_user_meta($user->ID, 'first_name', true),
            'last_name' => get_user_meta($user->ID, 'last_name', true),
            'description' => $user->description,
            'phone' => get_user_meta($user->ID, 'phone', true),
            'location' => get_user_meta($user->ID, 'location', true)
        ];
        
        $filled_fields = array_filter($fields);
        $completeness = (count($filled_fields) / count($fields)) * 100;
        
        return (int) $completeness;
    }
    
    /**
     * Track lead submissions
     */
    public function track_lead_submission(int $lead_id, array $lead_data): void {
        $user_id = get_current_user_id();
        if ($user_id) {
            $metadata = [
                'lead_id' => $lead_id,
                'message_length' => strlen($lead_data['message'] ?? '')
            ];
            
            $this->track_activity($user_id, 'listing_inquiry', $lead_data['listing_id'] ?? null, 'listing', $metadata);
        }
    }
    
    /**
     * Track listing views
     */
    public function track_listing_view(): void {
        $listing_id = intval($_POST['listing_id'] ?? 0);
        $user_id = get_current_user_id();
        
        if (!$listing_id) {
            wp_send_json_error(['message' => 'Invalid listing ID']);
            return;
        }
        
        // Track for logged-in users
        if ($user_id) {
            $view_time = intval($_POST['view_time'] ?? 0);
            $metadata = [
                'view_time_seconds' => $view_time,
                'source' => sanitize_text_field($_POST['source'] ?? 'direct')
            ];
            
            $this->track_activity($user_id, 'listing_viewed', $listing_id, 'listing', $metadata);
        }
        
        // Also update the listing view count (for analytics)
        $view_count = get_post_meta($listing_id, 'view_count', true) ?: 0;
        update_post_meta($listing_id, 'view_count', $view_count + 1);
        
        wp_send_json_success(['message' => 'View tracked']);
    }
    
    /**
     * Enqueue engagement tracking script
     */
    public function enqueue_engagement_tracker(): void {
        if (!is_user_logged_in()) {
            return;
        }
        
        $user_id = get_current_user_id();
        ?>
        <script>
        (function() {
            let startTime = Date.now();
            let pageViewed = false;
            
            // Track time on page when user leaves
            window.addEventListener('beforeunload', function() {
                let timeSpent = Math.floor((Date.now() - startTime) / 1000);
                let minutes = Math.floor(timeSpent / 60);
                
                if (minutes > 0) {
                    // Send beacon for time tracking
                    navigator.sendBeacon('<?php echo admin_url('admin-ajax.php'); ?>', new FormData(Object.assign(document.createElement('form'), {
                        innerHTML: '<input name="action" value="track_engagement">' +
                                  '<input name="user_id" value="<?php echo $user_id; ?>">' +
                                  '<input name="engagement_action" value="time_on_site">' +
                                  '<input name="minutes" value="' + minutes + '">'
                    })));
                }
            });
            
            // Track scroll depth for engagement
            let maxScroll = 0;
            window.addEventListener('scroll', function() {
                let scrollPercent = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
                if (scrollPercent > maxScroll) {
                    maxScroll = scrollPercent;
                }
            });
        })();
        </script>
        <?php
    }
    
    /**
     * AJAX handler for tracking engagement
     */
    public function ajax_track_engagement(): void {
        $user_id = intval($_POST['user_id'] ?? 0);
        $action = sanitize_key($_POST['engagement_action'] ?? '');
        
        if (!$user_id || !$action) {
            wp_send_json_error(['message' => 'Invalid request']);
            return;
        }
        
        // Verify user can track for this user ID
        if (get_current_user_id() !== $user_id && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
            return;
        }
        
        // Prepare metadata
        $metadata = [];
        if (isset($_POST['minutes'])) {
            $metadata['minutes'] = intval($_POST['minutes']);
        }
        
        $success = $this->track_activity($user_id, $action, null, null, $metadata);
        
        if ($success) {
            wp_send_json_success(['message' => 'Engagement tracked']);
        } else {
            wp_send_json_error(['message' => 'Failed to track engagement']);
        }
    }
    
    /**
     * Daily engagement analysis
     */
    public function analyze_daily_engagement(): void {
        global $wpdb;
        
        $this->log('Running daily engagement analysis...');
        
        // Update engagement scores for all users with recent activity
        $sql = "SELECT DISTINCT user_id 
                FROM {$this->table_name} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        
        $active_users = $wpdb->get_col($sql);
        
        foreach ($active_users as $user_id) {
            $this->update_user_engagement_score($user_id);
        }
        
        $this->log("Updated engagement scores for " . count($active_users) . " active users");
    }
}