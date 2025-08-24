<?php
/**
 * Scheduler Class
 * 
 * Handles cron jobs and scheduled tasks
 *
 * @package HappyPlace\Core
 * @version 4.0.0
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Scheduler Class
 * 
 * @since 4.0.0
 */
class Scheduler {
    
    /**
     * Scheduled jobs
     * 
     * @var array
     */
    private array $jobs = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->define_jobs();
    }
    
    /**
     * Initialize scheduler
     * 
     * @return void
     */
    public function init(): void {
        // Add custom cron schedules
        add_filter('cron_schedules', [$this, 'add_cron_schedules']);
        
        // Register job hooks
        foreach ($this->jobs as $job) {
            add_action($job['hook'], [$this, 'run_job']);
        }
        
        hp_log('Scheduler initialized with ' . count($this->jobs) . ' jobs', 'info', 'SCHEDULER');
    }
    
    /**
     * Define scheduled jobs
     * 
     * @return void
     */
    private function define_jobs(): void {
        $this->jobs = [
            [
                'hook' => 'hp_hourly_sync',
                'callback' => [$this, 'hourly_sync'],
                'schedule' => 'hourly',
                'description' => 'Hourly data synchronization',
            ],
            [
                'hook' => 'hp_daily_cleanup',
                'callback' => [$this, 'daily_cleanup'],
                'schedule' => 'daily',
                'description' => 'Daily cleanup tasks',
            ],
            [
                'hook' => 'hp_weekly_analytics',
                'callback' => [$this, 'weekly_analytics'],
                'schedule' => 'weekly',
                'description' => 'Weekly analytics processing',
            ],
            [
                'hook' => 'hp_monthly_report',
                'callback' => [$this, 'monthly_report'],
                'schedule' => 'monthly',
                'description' => 'Monthly report generation',
            ],
            [
                'hook' => 'hp_cache_cleanup',
                'callback' => [$this, 'cache_cleanup'],
                'schedule' => 'twicedaily',
                'description' => 'Cache cleanup',
            ],
        ];
    }
    
    /**
     * Add custom cron schedules
     * 
     * @param array $schedules
     * @return array
     */
    public function add_cron_schedules(array $schedules): array {
        $schedules['weekly'] = [
            'interval' => 604800,
            'display' => __('Once Weekly', 'happy-place'),
        ];
        
        $schedules['monthly'] = [
            'interval' => 2635200,
            'display' => __('Once Monthly', 'happy-place'),
        ];
        
        $schedules['every_5_minutes'] = [
            'interval' => 300,
            'display' => __('Every 5 Minutes', 'happy-place'),
        ];
        
        return $schedules;
    }
    
    /**
     * Schedule all jobs
     * 
     * @return void
     */
    public function schedule_jobs(): void {
        foreach ($this->jobs as $job) {
            if (!wp_next_scheduled($job['hook'])) {
                wp_schedule_event(time(), $job['schedule'], $job['hook']);
                hp_log("Scheduled job: {$job['hook']}", 'info', 'SCHEDULER');
            }
        }
    }
    
    /**
     * Clear all scheduled jobs
     * 
     * @return void
     */
    public function clear_jobs(): void {
        foreach ($this->jobs as $job) {
            $timestamp = wp_next_scheduled($job['hook']);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $job['hook']);
                hp_log("Unscheduled job: {$job['hook']}", 'info', 'SCHEDULER');
            }
        }
    }
    
    /**
     * Run a scheduled job
     * 
     * @param string $hook Job hook
     * @return void
     */
    public function run_job(string $hook = ''): void {
        // Get current action if not provided
        if (empty($hook)) {
            $hook = current_action();
        }
        
        // Find job configuration
        $job = null;
        foreach ($this->jobs as $j) {
            if ($j['hook'] === $hook) {
                $job = $j;
                break;
            }
        }
        
        if (!$job) {
            return;
        }
        
        hp_log("Running scheduled job: {$hook}", 'info', 'SCHEDULER');
        
        try {
            // Run the job callback
            if (is_callable($job['callback'])) {
                call_user_func($job['callback']);
            }
            
            // Log success
            $this->log_job_run($hook, 'success');
            
        } catch (\Exception $e) {
            hp_log("Job failed: {$hook} - " . $e->getMessage(), 'error', 'SCHEDULER');
            $this->log_job_run($hook, 'failed', $e->getMessage());
        }
    }
    
    /**
     * Hourly sync job
     * 
     * @return void
     */
    public function hourly_sync(): void {
        // Sync listings
        do_action('hp_sync_listings');
        
        // Update analytics
        do_action('hp_update_analytics');
        
        // Process pending leads
        do_action('hp_process_leads');
    }
    
    /**
     * Daily cleanup job
     * 
     * @return void
     */
    public function daily_cleanup(): void {
        global $wpdb;
        
        // Clean old logs
        $wpdb->query(
            "DELETE FROM {$wpdb->prefix}hp_error_log 
            WHERE error_time < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        // Clean old analytics
        $wpdb->query(
            "DELETE FROM {$wpdb->prefix}hp_analytics 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        );
        
        // Clean expired transients
        delete_expired_transients();
        
        // Optimize tables
        $tables = [
            $wpdb->prefix . 'hp_analytics',
            $wpdb->prefix . 'hp_property_views',
            $wpdb->prefix . 'hp_error_log',
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("OPTIMIZE TABLE {$table}");
        }
    }
    
    /**
     * Weekly analytics job
     * 
     * @return void
     */
    public function weekly_analytics(): void {
        // Generate weekly reports
        do_action('hp_generate_weekly_reports');
        
        // Calculate agent performance
        do_action('hp_calculate_agent_performance');
        
        // Update listing statistics
        do_action('hp_update_listing_statistics');
    }
    
    /**
     * Monthly report job
     * 
     * @return void
     */
    public function monthly_report(): void {
        // Generate monthly reports
        do_action('hp_generate_monthly_reports');
        
        // Send reports to admins
        do_action('hp_send_admin_reports');
        
        // Archive old data
        do_action('hp_archive_old_data');
    }
    
    /**
     * Cache cleanup job
     * 
     * @return void
     */
    public function cache_cleanup(): void {
        // Clear expired cache
        if (function_exists('hp_service')) {
            $cache = hp_service('cache');
            if ($cache) {
                $cache->flush_group('queries');
                $cache->flush_group('searches');
            }
        }
        
        // Clear old transients
        delete_expired_transients();
    }
    
    /**
     * Log job run
     * 
     * @param string $hook Job hook
     * @param string $status Status
     * @param string|null $error Error message
     * @return void
     */
    private function log_job_run(string $hook, string $status, ?string $error = null): void {
        global $wpdb;
        
        $table = $wpdb->prefix . 'hp_activity_log';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return;
        }
        
        $wpdb->insert(
            $table,
            [
                'action' => 'cron_job',
                'object_type' => 'scheduler',
                'description' => json_encode([
                    'hook' => $hook,
                    'status' => $status,
                    'error' => $error,
                ]),
            ],
            ['%s', '%s', '%s']
        );
    }
    
    /**
     * Get job status
     * 
     * @return array
     */
    public function get_job_status(): array {
        $status = [];
        
        foreach ($this->jobs as $job) {
            $next_run = wp_next_scheduled($job['hook']);
            
            $status[] = [
                'hook' => $job['hook'],
                'description' => $job['description'],
                'schedule' => $job['schedule'],
                'next_run' => $next_run ? date('Y-m-d H:i:s', $next_run) : 'Not scheduled',
                'is_scheduled' => (bool) $next_run,
            ];
        }
        
        return $status;
    }
}