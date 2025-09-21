<?php
/**
 * Marketing Migration
 * 
 * Database schema for marketing materials and activity tracking
 * 
 * @package HappyPlace
 * @since 4.0.0
 */

namespace HappyPlace\Migrations;

if (!defined('ABSPATH')) {
    exit;
}

class MarketingMigration {
    
    /**
     * Migration version
     */
    const VERSION = '1.0.0';
    
    /**
     * Run migration
     */
    public static function run() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Marketing activity tracking table
        $marketing_activity_table = $wpdb->prefix . 'hp_marketing_activity';
        
        $marketing_activity_sql = "CREATE TABLE $marketing_activity_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            listing_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            activity_type varchar(50) NOT NULL,
            activity_data longtext,
            file_path varchar(500),
            download_count int(11) DEFAULT 0,
            shared_count int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_listing_user (listing_id, user_id),
            KEY idx_activity_type (activity_type),
            KEY idx_created_at (created_at),
            KEY idx_user_activity (user_id, activity_type, created_at)
        ) $charset_collate;";
        
        // Marketing templates table
        $marketing_templates_table = $wpdb->prefix . 'hp_marketing_templates';
        
        $marketing_templates_sql = "CREATE TABLE $marketing_templates_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            template_name varchar(100) NOT NULL,
            template_type varchar(50) NOT NULL COMMENT 'pdf, social, email',
            platform varchar(50) DEFAULT NULL COMMENT 'facebook, instagram, twitter, etc',
            template_data longtext NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_by bigint(20) unsigned,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_template_type (template_type),
            KEY idx_platform (platform),
            KEY idx_active (is_active)
        ) $charset_collate;";
        
        // Marketing performance table  
        $marketing_performance_table = $wpdb->prefix . 'hp_marketing_performance';
        
        $marketing_performance_sql = "CREATE TABLE $marketing_performance_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            marketing_activity_id bigint(20) unsigned NOT NULL,
            metric_type varchar(50) NOT NULL COMMENT 'views, clicks, downloads, shares',
            metric_value int(11) DEFAULT 0,
            metric_date date NOT NULL,
            source varchar(100) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_activity_metric (marketing_activity_id, metric_type),
            KEY idx_metric_date (metric_date),
            FOREIGN KEY (marketing_activity_id) REFERENCES $marketing_activity_table(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create tables
        dbDelta($marketing_activity_sql);
        dbDelta($marketing_templates_sql);  
        dbDelta($marketing_performance_sql);
        
        // Insert default templates
        self::insert_default_templates();
        
        // Update migration version
        update_option('hp_marketing_migration_version', self::VERSION);
        
        hp_log('Marketing Migration: Database tables created successfully');
        
        return true;
    }
    
    /**
     * Insert default marketing templates
     */
    private static function insert_default_templates() {
        global $wpdb;
        
        $templates_table = $wpdb->prefix . 'hp_marketing_templates';
        
        // Check if templates already exist
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM $templates_table");
        if ($existing > 0) {
            return; // Templates already inserted
        }
        
        $default_templates = [
            // PDF Templates
            [
                'template_name' => 'Modern PDF Flyer',
                'template_type' => 'pdf',
                'platform' => null,
                'template_data' => json_encode([
                    'layout' => 'modern',
                    'colors' => ['primary' => '#2563eb', 'secondary' => '#059669'],
                    'fonts' => ['heading' => 'Arial', 'body' => 'Arial'],
                    'sections' => ['header', 'image', 'details', 'description', 'agent', 'qr']
                ])
            ],
            [
                'template_name' => 'Luxury PDF Flyer',
                'template_type' => 'pdf',
                'platform' => null,
                'template_data' => json_encode([
                    'layout' => 'luxury',
                    'colors' => ['primary' => '#1f2937', 'secondary' => '#d4af37'],
                    'fonts' => ['heading' => 'Georgia', 'body' => 'Georgia'],
                    'sections' => ['header', 'image', 'details', 'description', 'agent', 'qr']
                ])
            ],
            
            // Social Media Templates
            [
                'template_name' => 'Facebook Standard Post',
                'template_type' => 'social',
                'platform' => 'facebook',
                'template_data' => json_encode([
                    'template' => "🏠 NEW LISTING ALERT! 🏠\n\n{title}\n💰 {price}\n🏠 {bedrooms} bed, {bathrooms} bath\n📐 {square_feet}\n📍 {address}\n\n{description}\n\nContact {agent_name} for details:\n📞 {agent_phone}\n📧 {agent_email}\n\n{listing_url}\n\n#RealEstate #NewListing #PropertyForSale",
                    'hashtags' => ['#RealEstate', '#NewListing', '#PropertyForSale', '#DreamHome'],
                    'character_limit' => 63206,
                    'recommended_length' => 400
                ])
            ],
            [
                'template_name' => 'Instagram Feed Post',
                'template_type' => 'social',
                'platform' => 'instagram',
                'template_data' => json_encode([
                    'template' => "🏠 Just Listed!\n\n{title}\n💰 {price}\n📍 {address}\n\n✨ {bedrooms}BR | {bathrooms}BA | {square_feet}\n\n{description}\n\nDM or call {agent_name}\n📞 {agent_phone}\n\n{listing_url}\n\n#JustListed #RealEstate #NewHome #PropertyGoals #YourNextHome",
                    'hashtags' => ['#JustListed', '#RealEstate', '#NewHome', '#PropertyGoals', '#YourNextHome', '#DreamHome'],
                    'character_limit' => 2200,
                    'recommended_length' => 125
                ])
            ],
            [
                'template_name' => 'Twitter Standard Tweet',
                'template_type' => 'social',
                'platform' => 'twitter',
                'template_data' => json_encode([
                    'template' => "🏠 NEW LISTING: {title}\n💰 {price} | {bedrooms}BR/{bathrooms}BA | {square_feet}\n📍 {address}\n\nContact {agent_name}: {agent_phone}\n{listing_url}\n\n#RealEstate #NewListing #PropertyForSale",
                    'hashtags' => ['#RealEstate', '#NewListing', '#PropertyForSale', '#JustListed'],
                    'character_limit' => 280,
                    'recommended_length' => 280
                ])
            ],
            
            // Email Templates (placeholder for future implementation)
            [
                'template_name' => 'New Listing Announcement',
                'template_type' => 'email',
                'platform' => null,
                'template_data' => json_encode([
                    'subject' => 'New Listing: {title}',
                    'template' => 'new_listing_announcement',
                    'sections' => ['header', 'property_image', 'details', 'description', 'cta', 'agent_signature']
                ])
            ]
        ];
        
        foreach ($default_templates as $template) {
            $wpdb->insert($templates_table, $template);
        }
        
        hp_log('Marketing Migration: Default templates inserted');
    }
    
    /**
     * Check if migration is needed
     */
    public static function needs_migration() {
        $current_version = get_option('hp_marketing_migration_version', '0.0.0');
        return version_compare($current_version, self::VERSION, '<');
    }
    
    /**
     * Rollback migration
     */
    public static function rollback() {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'hp_marketing_performance',
            $wpdb->prefix . 'hp_marketing_templates', 
            $wpdb->prefix . 'hp_marketing_activity'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        delete_option('hp_marketing_migration_version');
        
        hp_log('Marketing Migration: Rollback completed');
    }
    
    /**
     * Get marketing statistics
     */
    public static function get_stats() {
        global $wpdb;
        
        $activity_table = $wpdb->prefix . 'hp_marketing_activity';
        
        $stats = [];
        
        // Total marketing materials generated
        $stats['total_materials'] = $wpdb->get_var("SELECT COUNT(*) FROM $activity_table");
        
        // Materials by type
        $materials_by_type = $wpdb->get_results(
            "SELECT activity_type, COUNT(*) as count 
             FROM $activity_table 
             GROUP BY activity_type"
        );
        
        foreach ($materials_by_type as $type) {
            $stats['by_type'][$type->activity_type] = intval($type->count);
        }
        
        // Recent activity (last 30 days)
        $stats['recent_activity'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $activity_table 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        // Most active user
        $most_active = $wpdb->get_row(
            "SELECT user_id, COUNT(*) as count 
             FROM $activity_table 
             GROUP BY user_id 
             ORDER BY count DESC 
             LIMIT 1"
        );
        
        if ($most_active) {
            $user = get_user_by('id', $most_active->user_id);
            $stats['most_active_user'] = [
                'name' => $user ? $user->display_name : 'Unknown',
                'count' => intval($most_active->count)
            ];
        }
        
        return $stats;
    }
    
    /**
     * Clean up old marketing files
     */
    public static function cleanup_old_files($days = 90) {
        global $wpdb;
        
        $activity_table = $wpdb->prefix . 'hp_marketing_activity';
        
        // Get old files
        $old_files = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT file_path FROM $activity_table 
                 WHERE file_path IS NOT NULL 
                 AND created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );
        
        $deleted_count = 0;
        
        foreach ($old_files as $file_record) {
            $file_path = $file_record->file_path;
            
            if (file_exists($file_path)) {
                if (unlink($file_path)) {
                    $deleted_count++;
                }
            }
        }
        
        // Remove file references from database
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $activity_table 
                 SET file_path = NULL 
                 WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );
        
        hp_log("Marketing Migration: Cleaned up {$deleted_count} old marketing files");
        
        return $deleted_count;
    }
}