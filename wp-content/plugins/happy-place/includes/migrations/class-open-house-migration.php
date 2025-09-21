<?php
/**
 * Open House Database Migration
 * Creates tables for open house scheduling, visitors, and analytics
 * 
 * @package HappyPlace
 * @subpackage Migrations
 * @since 4.1.0
 */

namespace HappyPlace\Migrations;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Open House Migration Class
 */
class OpenHouseMigration {
    
    /**
     * Migration version
     */
    const VERSION = '1.0.0';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'maybe_run_migration'));
    }
    
    /**
     * Check if migration should run
     */
    public function maybe_run_migration() {
        $current_version = get_option('hp_open_house_migration_version', '0.0.0');
        
        if (version_compare($current_version, self::VERSION, '<')) {
            $this->run_migration();
        }
    }
    
    /**
     * Run the migration
     */
    public function run_migration() {
        global $wpdb;
        
        $this->create_open_houses_table();
        $this->create_open_house_visitors_table();
        $this->create_open_house_analytics_table();
        
        // Update version
        update_option('hp_open_house_migration_version', self::VERSION);
        
        // Log migration
        if (function_exists('hp_log')) {
            hp_log('Open House migration completed to version ' . self::VERSION, 'migration');
        }
    }
    
    /**
     * Create open houses table
     */
    private function create_open_houses_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_open_houses';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            listing_id bigint(20) unsigned NOT NULL,
            agent_id bigint(20) unsigned NOT NULL,
            title varchar(255) NOT NULL DEFAULT '',
            description text,
            event_date date NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            timezone varchar(50) DEFAULT 'America/New_York',
            status varchar(20) DEFAULT 'scheduled',
            max_visitors int(11) DEFAULT 0,
            require_registration tinyint(1) DEFAULT 1,
            public_visibility tinyint(1) DEFAULT 1,
            send_reminders tinyint(1) DEFAULT 1,
            special_instructions text,
            qr_code_token varchar(100) DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY listing_id (listing_id),
            KEY agent_id (agent_id),
            KEY event_date (event_date),
            KEY status (status),
            KEY qr_code_token (qr_code_token)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create open house visitors table
     */
    private function create_open_house_visitors_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_open_house_visitors';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            open_house_id bigint(20) unsigned NOT NULL,
            visitor_type enum('registered_user', 'lead', 'guest') DEFAULT 'guest',
            user_id bigint(20) unsigned DEFAULT NULL,
            lead_id bigint(20) unsigned DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(200) NOT NULL,
            phone varchar(20) DEFAULT '',
            registration_source varchar(50) DEFAULT 'manual',
            sign_in_time datetime DEFAULT NULL,
            sign_out_time datetime DEFAULT NULL,
            attended tinyint(1) DEFAULT 0,
            interested_level tinyint(1) DEFAULT 3,
            notes text,
            follow_up_requested tinyint(1) DEFAULT 0,
            follow_up_completed tinyint(1) DEFAULT 0,
            marketing_consent tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY open_house_id (open_house_id),
            KEY user_id (user_id),
            KEY lead_id (lead_id),
            KEY email (email),
            KEY attended (attended),
            KEY registration_source (registration_source)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create open house analytics table
     */
    private function create_open_house_analytics_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_open_house_analytics';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            open_house_id bigint(20) unsigned NOT NULL,
            metric_type varchar(50) NOT NULL,
            metric_value decimal(10,2) NOT NULL DEFAULT 0.00,
            metric_data longtext,
            recorded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY open_house_id (open_house_id),
            KEY metric_type (metric_type),
            KEY recorded_at (recorded_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Add indexes for performance
     */
    private function add_indexes() {
        global $wpdb;
        
        // Composite indexes for common queries
        $wpdb->query("CREATE INDEX idx_open_house_date_agent ON {$wpdb->prefix}hp_open_houses (agent_id, event_date, status)");
        $wpdb->query("CREATE INDEX idx_visitor_attendance ON {$wpdb->prefix}hp_open_house_visitors (open_house_id, attended, sign_in_time)");
        $wpdb->query("CREATE INDEX idx_analytics_lookup ON {$wpdb->prefix}hp_open_house_analytics (open_house_id, metric_type, recorded_at)");
    }
    
    /**
     * Seed sample data (development only)
     */
    public function seed_sample_data() {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        global $wpdb;
        
        // Only seed if tables are empty
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hp_open_houses");
        if ($count > 0) {
            return;
        }
        
        // Get a sample listing and agent
        $listing = $wpdb->get_row("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'listing' AND post_status = 'publish' LIMIT 1");
        $agent = $wpdb->get_row("SELECT ID FROM {$wpdb->users} WHERE ID IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'wp_capabilities' AND meta_value LIKE '%agent%') LIMIT 1");
        
        if (!$listing || !$agent) {
            return;
        }
        
        // Sample open house
        $wpdb->insert(
            $wpdb->prefix . 'hp_open_houses',
            array(
                'listing_id' => $listing->ID,
                'agent_id' => $agent->ID,
                'title' => 'Open House - Beautiful Family Home',
                'description' => 'Join us for a showing of this stunning property. Light refreshments will be provided.',
                'event_date' => date('Y-m-d', strtotime('+3 days')),
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'status' => 'scheduled',
                'max_visitors' => 50,
                'qr_code_token' => $this->generate_qr_token()
            )
        );
        
        $open_house_id = $wpdb->insert_id;
        
        // Sample visitor registration
        $wpdb->insert(
            $wpdb->prefix . 'hp_open_house_visitors',
            array(
                'open_house_id' => $open_house_id,
                'visitor_type' => 'guest',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'phone' => '555-0123',
                'registration_source' => 'website',
                'interested_level' => 4,
                'marketing_consent' => 1
            )
        );
    }
    
    /**
     * Generate unique QR code token
     */
    private function generate_qr_token() {
        return 'oh_' . substr(md5(uniqid(rand(), true)), 0, 12);
    }
    
    /**
     * Rollback migration (for development)
     */
    public function rollback() {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return false;
        }
        
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'hp_open_house_analytics',
            $wpdb->prefix . 'hp_open_house_visitors', 
            $wpdb->prefix . 'hp_open_houses'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        delete_option('hp_open_house_migration_version');
        
        return true;
    }
}

// Initialize migration
new OpenHouseMigration();