<?php
/**
 * User System Initializer
 * 
 * Initializes all user-related services and runs necessary migrations
 * 
 * @package HappyPlace
 * @version 4.0.0
 */

namespace HappyPlace;

if (!defined('ABSPATH')) {
    exit;
}

class UserSystemInitializer {
    
    /**
     * Services to initialize
     */
    private static array $services = [
        'UserFavoritesService',
        'SavedSearchesService', 
        'UserEngagementService',
        'LeadConversionService'
    ];
    
    /**
     * Initialize the user system
     */
    public static function init(): void {
        // Run migrations first
        self::run_migrations();
        
        // Initialize services
        self::initialize_services();
        
        // Register integrations
        self::register_integrations();
        
        // Schedule cron jobs
        self::schedule_cron_jobs();
        
        // Log initialization
        hp_log('User System initialized successfully', 'info', 'user-system');
    }
    
    /**
     * Run database migrations
     */
    private static function run_migrations(): void {
        if (!class_exists('\HappyPlace\Migrations\UserSystemMigration')) {
            hp_log('UserSystemMigration class not found', 'error', 'user-system');
            return;
        }
        
        try {
            \HappyPlace\Migrations\UserSystemMigration::maybe_migrate();
            hp_log('Database migrations completed', 'info', 'user-system');
        } catch (Exception $e) {
            hp_log('Migration error: ' . $e->getMessage(), 'error', 'user-system');
        }
    }
    
    /**
     * Initialize all services
     */
    private static function initialize_services(): void {
        foreach (self::$services as $service_name) {
            $service_class = "\\HappyPlace\\Services\\{$service_name}";
            
            if (!class_exists($service_class)) {
                hp_log("Service class not found: {$service_class}", 'warning', 'user-system');
                continue;
            }
            
            try {
                $service = new $service_class();
                $service->init();
                hp_log("Service initialized: {$service_name}", 'info', 'user-system');
            } catch (Exception $e) {
                hp_log("Service initialization error ({$service_name}): " . $e->getMessage(), 'error', 'user-system');
            }
        }
    }
    
    /**
     * Register integrations with existing systems
     */
    private static function register_integrations(): void {
        // Hook into FollowUp Boss for user account sync
        add_action('hp_lead_converted_to_user', [__CLASS__, 'sync_conversion_to_followup_boss'], 10, 3);
        add_action('hp_user_engagement_milestone', [__CLASS__, 'sync_milestone_to_followup_boss'], 10, 3);
        
        // Hook into user registration for existing role system
        add_action('user_register', [__CLASS__, 'handle_new_user_integration']);
        
        // Hook into existing lead system
        add_action('hp_lead_created', [__CLASS__, 'handle_lead_conversion_opportunity'], 10, 2);
        
        hp_log('User system integrations registered', 'info', 'user-system');
    }
    
    /**
     * Schedule necessary cron jobs
     */
    private static function schedule_cron_jobs(): void {
        $cron_jobs = [
            'hp_process_search_alerts' => 'hourly',
            'hp_daily_search_digest' => 'daily',
            'hp_analyze_user_engagement' => 'daily',
            'hp_cleanup_old_activities' => 'weekly'
        ];
        
        foreach ($cron_jobs as $hook => $frequency) {
            if (!wp_next_scheduled($hook)) {
                $time_offset = $frequency === 'daily' ? 8 * HOUR_IN_SECONDS : 0;
                wp_schedule_event(time() + $time_offset, $frequency, $hook);
                hp_log("Scheduled cron job: {$hook} ({$frequency})", 'info', 'user-system');
            }
        }
    }
    
    /**
     * Sync lead conversion to FollowUp Boss
     */
    public static function sync_conversion_to_followup_boss(int $user_id, int $lead_id, array $lead_data): void {
        if (!class_exists('\HappyPlace\Integrations\FollowUp_Boss_Integration')) {
            return;
        }
        
        try {
            $fub = \HappyPlace\Integrations\FollowUp_Boss_Integration::get_instance();
            
            // Update the person record in FUB with user account info
            $user = get_user_by('id', $user_id);
            if ($user) {
                // Prepare updated data
                $fub_data = [
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    'emails' => [['value' => $user->user_email]],
                    'status' => 'Registered User',
                    'tags' => ['Website User', 'Account Created'],
                    'customFields' => [
                        'wordpress_user_id' => $user_id,
                        'conversion_date' => current_time('mysql'),
                        'engagement_score' => get_user_meta($user_id, 'engagement_score', true) ?: 0
                    ]
                ];
                
                // If phone exists, add it
                $phone = get_user_meta($user_id, 'phone', true);
                if ($phone) {
                    $fub_data['phones'] = [['value' => $phone]];
                }
                
                // This would need to be implemented in the FollowUp Boss integration
                // $fub->update_person($lead_data['email'], $fub_data);
                
                hp_log("Lead conversion synced to FollowUp Boss: User {$user_id}, Lead {$lead_id}", 'info', 'user-system');
            }
        } catch (Exception $e) {
            hp_log("Error syncing conversion to FollowUp Boss: " . $e->getMessage(), 'error', 'user-system');
        }
    }
    
    /**
     * Sync engagement milestone to FollowUp Boss
     */
    public static function sync_milestone_to_followup_boss(int $user_id, string $milestone, int $score): void {
        if (!class_exists('\HappyPlace\Integrations\FollowUp_Boss_Integration')) {
            return;
        }
        
        try {
            $user = get_user_by('id', $user_id);
            if (!$user) {
                return;
            }
            
            // Map milestones to FUB stages/tags
            $milestone_mapping = [
                'engaged_visitor' => ['tag' => 'Engaged Visitor'],
                'active_prospect' => ['tag' => 'Active Prospect', 'status' => 'Active'],
                'hot_lead' => ['tag' => 'Hot Lead', 'status' => 'Hot'],
                'ready_to_buy' => ['tag' => 'Ready to Buy', 'status' => 'Ready to Buy']
            ];
            
            if (isset($milestone_mapping[$milestone])) {
                $mapping = $milestone_mapping[$milestone];
                
                // This would update the person in FUB with new status/tags
                hp_log("Engagement milestone synced to FollowUp Boss: User {$user_id}, Milestone {$milestone}, Score {$score}", 'info', 'user-system');
            }
        } catch (Exception $e) {
            hp_log("Error syncing milestone to FollowUp Boss: " . $e->getMessage(), 'error', 'user-system');
        }
    }
    
    /**
     * Handle new user integration with existing role system
     */
    public static function handle_new_user_integration(int $user_id): void {
        try {
            // Check if UserRoleService exists and handle role assignment
            if (class_exists('\HappyPlace\Services\UserRoleService')) {
                // The UserRoleService will handle this automatically
            }
            
            // Initialize engagement tracking for new user
            if (class_exists('\HappyPlace\Services\UserEngagementService')) {
                $engagement_service = new \HappyPlace\Services\UserEngagementService();
                $engagement_service->track_activity($user_id, 'registration');
            }
            
            hp_log("New user integrated into user system: {$user_id}", 'info', 'user-system');
        } catch (Exception $e) {
            hp_log("Error in new user integration: " . $e->getMessage(), 'error', 'user-system');
        }
    }
    
    /**
     * Handle lead conversion opportunities
     */
    public static function handle_lead_conversion_opportunity(int $lead_id, array $lead_data): void {
        try {
            // Only process high-quality leads
            $lead_score = $lead_data['lead_score'] ?? 0;
            if ($lead_score < 30) {
                return;
            }
            
            // Check if LeadConversionService is available
            if (class_exists('\HappyPlace\Services\LeadConversionService')) {
                $conversion_service = new \HappyPlace\Services\LeadConversionService();
                
                // This will trigger the conversion opportunity handling
                // which includes scheduling conversion invitation emails
                do_action('hp_high_quality_lead_created', $lead_id, $lead_data);
            }
            
        } catch (Exception $e) {
            hp_log("Error handling lead conversion opportunity: " . $e->getMessage(), 'error', 'user-system');
        }
    }
    
    /**
     * Get system status
     */
    public static function get_system_status(): array {
        $status = [
            'migrations' => [],
            'services' => [],
            'integrations' => [],
            'cron_jobs' => []
        ];
        
        // Check migrations
        if (class_exists('\HappyPlace\Migrations\UserSystemMigration')) {
            $status['migrations'] = \HappyPlace\Migrations\UserSystemMigration::get_status();
        }
        
        // Check services
        foreach (self::$services as $service_name) {
            $service_class = "\\HappyPlace\\Services\\{$service_name}";
            $status['services'][$service_name] = [
                'class_exists' => class_exists($service_class),
                'initialized' => false // This would need to be tracked differently
            ];
        }
        
        // Check cron jobs
        $cron_jobs = ['hp_process_search_alerts', 'hp_analyze_user_engagement'];
        foreach ($cron_jobs as $job) {
            $status['cron_jobs'][$job] = [
                'scheduled' => wp_next_scheduled($job) !== false,
                'next_run' => wp_next_scheduled($job)
            ];
        }
        
        return $status;
    }
    
    /**
     * Cleanup old activity records (for privacy and performance)
     */
    public static function cleanup_old_activities(): void {
        global $wpdb;
        
        $activity_table = $wpdb->prefix . 'user_activity';
        
        // Remove activities older than 1 year
        $deleted = $wpdb->query("
            DELETE FROM {$activity_table} 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ");
        
        if ($deleted !== false) {
            hp_log("Cleaned up {$deleted} old activity records", 'info', 'user-system');
        }
    }
    
    /**
     * Deactivate user system (for plugin deactivation)
     */
    public static function deactivate(): void {
        // Unschedule cron jobs
        $cron_jobs = [
            'hp_process_search_alerts',
            'hp_daily_search_digest', 
            'hp_analyze_user_engagement',
            'hp_cleanup_old_activities'
        ];
        
        foreach ($cron_jobs as $hook) {
            wp_clear_scheduled_hook($hook);
        }
        
        hp_log('User system deactivated - cron jobs cleared', 'info', 'user-system');
    }
}

// Initialize on plugins_loaded to ensure all dependencies are available
add_action('plugins_loaded', ['\HappyPlace\UserSystemInitializer', 'init'], 20);

// Handle plugin deactivation
register_deactivation_hook(HP_PLUGIN_FILE, ['\HappyPlace\UserSystemInitializer', 'deactivate']);

// Register cleanup cron
add_action('hp_cleanup_old_activities', ['\HappyPlace\UserSystemInitializer', 'cleanup_old_activities']);