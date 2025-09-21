<?php
/**
 * User System Database Migration
 * 
 * Creates tables and schema updates for user favorites, saved searches,
 * and engagement tracking
 * 
 * @package HappyPlace\Migrations
 * @version 4.0.0
 */

namespace HappyPlace\Migrations;

if (!defined('ABSPATH')) {
    exit;
}

class UserSystemMigration {
    
    /**
     * Migration version
     */
    const VERSION = '1.0.0';
    
    /**
     * Option key for tracking migration
     */
    const OPTION_KEY = 'hp_user_system_migration_version';
    
    /**
     * Run migration if needed
     */
    public static function maybe_migrate() {
        $current_version = get_option(self::OPTION_KEY, '0.0.0');
        
        if (version_compare($current_version, self::VERSION, '<')) {
            self::run_migration();
        }
    }
    
    /**
     * Run the full migration
     */
    public static function run_migration() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Track migration start
        hp_log('Starting user system migration', 'info', 'migration');
        
        // Create user favorites table
        self::create_user_favorites_table($charset_collate);
        
        // Create saved searches table
        self::create_saved_searches_table($charset_collate);
        
        // Create user activity table
        self::create_user_activity_table($charset_collate);
        
        // Update existing leads table
        self::update_leads_table();
        
        // Create indexes for performance
        self::create_indexes();
        
        // Update migration version
        update_option(self::OPTION_KEY, self::VERSION);
        
        hp_log('User system migration completed successfully', 'info', 'migration');
    }
    
    /**
     * Create user favorites table
     */
    private static function create_user_favorites_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'user_favorites';
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            listing_id BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            notes TEXT,
            rating INT(1) DEFAULT NULL,
            tags VARCHAR(255) DEFAULT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            UNIQUE KEY user_listing (user_id, listing_id),
            INDEX idx_user (user_id),
            INDEX idx_listing (listing_id),
            INDEX idx_created (created_at),
            INDEX idx_active (is_active)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        if ($wpdb->last_error) {
            hp_log('Error creating user_favorites table: ' . $wpdb->last_error, 'error', 'migration');
        } else {
            hp_log('user_favorites table created successfully', 'info', 'migration');
        }
    }
    
    /**
     * Create saved searches table
     */
    private static function create_saved_searches_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'saved_searches';
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            search_name VARCHAR(100) NOT NULL,
            search_criteria JSON NOT NULL,
            email_frequency VARCHAR(20) DEFAULT 'daily',
            is_active BOOLEAN DEFAULT TRUE,
            last_sent DATETIME DEFAULT NULL,
            total_sent INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_active_frequency (is_active, email_frequency),
            INDEX idx_last_sent (last_sent),
            INDEX idx_created (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        if ($wpdb->last_error) {
            hp_log('Error creating saved_searches table: ' . $wpdb->last_error, 'error', 'migration');
        } else {
            hp_log('saved_searches table created successfully', 'info', 'migration');
        }
    }
    
    /**
     * Create user activity table
     */
    private static function create_user_activity_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'user_activity';
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            action VARCHAR(50) NOT NULL,
            object_id INT DEFAULT NULL,
            object_type VARCHAR(50) DEFAULT NULL,
            points INT DEFAULT 0,
            metadata JSON DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_action (user_id, action),
            INDEX idx_created (created_at),
            INDEX idx_points (points),
            INDEX idx_object (object_type, object_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        if ($wpdb->last_error) {
            hp_log('Error creating user_activity table: ' . $wpdb->last_error, 'error', 'migration');
        } else {
            hp_log('user_activity table created successfully', 'info', 'migration');
        }
    }
    
    /**
     * Update existing leads table
     */
    private static function update_leads_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_leads';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            hp_log('Leads table does not exist, skipping update', 'warning', 'migration');
            return;
        }
        
        // Add user_id column if it doesn't exist
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'user_id'");
        if (empty($column_exists)) {
            $sql = "ALTER TABLE {$table_name} 
                    ADD COLUMN user_id BIGINT(20) UNSIGNED NULL AFTER id";
            $wpdb->query($sql);
            
            if ($wpdb->last_error) {
                hp_log('Error adding user_id to leads table: ' . $wpdb->last_error, 'error', 'migration');
            } else {
                hp_log('Added user_id column to leads table', 'info', 'migration');
            }
        }
        
        // Add conversion_date column if it doesn't exist
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'conversion_date'");
        if (empty($column_exists)) {
            $sql = "ALTER TABLE {$table_name} 
                    ADD COLUMN conversion_date DATETIME NULL AFTER last_contacted";
            $wpdb->query($sql);
            
            if ($wpdb->last_error) {
                hp_log('Error adding conversion_date to leads table: ' . $wpdb->last_error, 'error', 'migration');
            } else {
                hp_log('Added conversion_date column to leads table', 'info', 'migration');
            }
        }
        
        // Add account_status column if it doesn't exist
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'account_status'");
        if (empty($column_exists)) {
            $sql = "ALTER TABLE {$table_name} 
                    ADD COLUMN account_status VARCHAR(20) DEFAULT 'guest' AFTER status";
            $wpdb->query($sql);
            
            if ($wpdb->last_error) {
                hp_log('Error adding account_status to leads table: ' . $wpdb->last_error, 'error', 'migration');
            } else {
                hp_log('Added account_status column to leads table', 'info', 'migration');
            }
        }
    }
    
    /**
     * Create additional indexes for performance
     */
    private static function create_indexes() {
        global $wpdb;
        
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        // Add index on user_id for leads table if it doesn't exist
        $indexes = $wpdb->get_results("SHOW INDEX FROM {$leads_table} WHERE Key_name = 'idx_user_id'");
        if (empty($indexes)) {
            $sql = "ALTER TABLE {$leads_table} ADD INDEX idx_user_id (user_id)";
            $wpdb->query($sql);
            
            if ($wpdb->last_error) {
                hp_log('Error adding user_id index to leads table: ' . $wpdb->last_error, 'error', 'migration');
            } else {
                hp_log('Added user_id index to leads table', 'info', 'migration');
            }
        }
        
        // Add composite index for account status queries
        $indexes = $wpdb->get_results("SHOW INDEX FROM {$leads_table} WHERE Key_name = 'idx_account_status'");
        if (empty($indexes)) {
            $sql = "ALTER TABLE {$leads_table} ADD INDEX idx_account_status (account_status, created_at)";
            $wpdb->query($sql);
            
            if ($wpdb->last_error) {
                hp_log('Error adding account_status index to leads table: ' . $wpdb->last_error, 'error', 'migration');
            } else {
                hp_log('Added account_status index to leads table', 'info', 'migration');
            }
        }
    }
    
    /**
     * Rollback migration (for testing purposes)
     */
    public static function rollback() {
        global $wpdb;
        
        hp_log('Starting user system migration rollback', 'warning', 'migration');
        
        // Drop created tables
        $tables = [
            $wpdb->prefix . 'user_favorites',
            $wpdb->prefix . 'saved_searches',
            $wpdb->prefix . 'user_activity'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
            hp_log("Dropped table: {$table}", 'info', 'migration');
        }
        
        // Remove added columns from leads table
        $leads_table = $wpdb->prefix . 'hp_leads';
        if ($wpdb->get_var("SHOW TABLES LIKE '$leads_table'") == $leads_table) {
            $columns_to_remove = ['user_id', 'conversion_date', 'account_status'];
            
            foreach ($columns_to_remove as $column) {
                $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$leads_table} LIKE '{$column}'");
                if (!empty($column_exists)) {
                    $wpdb->query("ALTER TABLE {$leads_table} DROP COLUMN {$column}");
                    hp_log("Removed column {$column} from leads table", 'info', 'migration');
                }
            }
        }
        
        // Reset migration version
        delete_option(self::OPTION_KEY);
        
        hp_log('User system migration rollback completed', 'warning', 'migration');
    }
    
    /**
     * Get migration status
     */
    public static function get_status() {
        $current_version = get_option(self::OPTION_KEY, '0.0.0');
        $latest_version = self::VERSION;
        
        return [
            'current_version' => $current_version,
            'latest_version' => $latest_version,
            'needs_migration' => version_compare($current_version, $latest_version, '<'),
            'tables' => self::check_tables_exist()
        ];
    }
    
    /**
     * Check if all required tables exist
     */
    private static function check_tables_exist() {
        global $wpdb;
        
        $required_tables = [
            'user_favorites' => $wpdb->prefix . 'user_favorites',
            'saved_searches' => $wpdb->prefix . 'saved_searches',
            'user_activity' => $wpdb->prefix . 'user_activity'
        ];
        
        $status = [];
        
        foreach ($required_tables as $key => $table_name) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            $status[$key] = [
                'table_name' => $table_name,
                'exists' => $exists
            ];
        }
        
        return $status;
    }
}