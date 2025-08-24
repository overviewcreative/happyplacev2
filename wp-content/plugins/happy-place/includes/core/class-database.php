<?php
/**
 * Database Class
 * 
 * Handles database operations, table creation, and queries
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
 * Database Class
 * 
 * @since 4.0.0
 */
class Database {
    
    /**
     * WordPress database object
     * 
     * @var \wpdb
     */
    private \wpdb $wpdb;
    
    /**
     * Database version
     * 
     * @var string
     */
    private string $db_version = '1.0.0';
    
    /**
     * Table names cache
     * 
     * @var array
     */
    private array $tables = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->init_tables();
    }
    
    /**
     * Initialize table names
     * 
     * @return void
     */
    private function init_tables(): void {
        $this->tables = [
            'listings_meta' => HP_TABLE_PREFIX . 'listings_meta',
            'agent_meta' => HP_TABLE_PREFIX . 'agent_meta',
            'analytics' => HP_TABLE_PREFIX . 'analytics',
            'leads' => HP_TABLE_PREFIX . 'leads',
            'lead_meta' => HP_TABLE_PREFIX . 'lead_meta',
            'saved_searches' => HP_TABLE_PREFIX . 'saved_searches',
            'property_views' => HP_TABLE_PREFIX . 'property_views',
            'inquiries' => HP_TABLE_PREFIX . 'inquiries',
            'error_log' => HP_TABLE_PREFIX . 'error_log',
            'activity_log' => HP_TABLE_PREFIX . 'activity_log',
        ];
    }
    
    /**
     * Initialize database
     * 
     * @return void
     */
    public function init(): void {
        // Check if tables need to be created or updated
        $installed_version = get_option('hp_db_version', '0');
        
        if (version_compare($installed_version, $this->db_version, '<')) {
            $this->create_tables();
            update_option('hp_db_version', $this->db_version);
        }
    }
    
    /**
     * Create database tables
     * 
     * @return void
     */
    public function create_tables(): void {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        // Listings Meta Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tables['listings_meta']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            listing_id bigint(20) UNSIGNED NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY listing_id (listing_id),
            KEY meta_key (meta_key),
            KEY listing_meta (listing_id, meta_key)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Agent Meta Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tables['agent_meta']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            agent_id bigint(20) UNSIGNED NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY agent_id (agent_id),
            KEY meta_key (meta_key),
            KEY agent_meta (agent_id, meta_key)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Analytics Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tables['analytics']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            object_type varchar(50) NOT NULL,
            object_id bigint(20) UNSIGNED NOT NULL,
            event_type varchar(50) NOT NULL,
            event_value varchar(255),
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            ip_address varchar(45),
            user_agent text,
            referrer text,
            session_id varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY object_lookup (object_type, object_id),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY created_at (created_at),
            KEY session_id (session_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Leads Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tables['leads']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            first_name varchar(100),
            last_name varchar(100),
            email varchar(255) NOT NULL,
            phone varchar(20),
            source varchar(50),
            status varchar(20) DEFAULT 'new',
            score int(11) DEFAULT 0,
            assigned_agent_id bigint(20) UNSIGNED DEFAULT NULL,
            listing_id bigint(20) UNSIGNED DEFAULT NULL,
            notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email),
            KEY status (status),
            KEY assigned_agent_id (assigned_agent_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Lead Meta Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tables['lead_meta']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            lead_id bigint(20) UNSIGNED NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY lead_id (lead_id),
            KEY meta_key (meta_key)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Saved Searches Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tables['saved_searches']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            search_name varchar(255),
            search_criteria longtext,
            frequency varchar(20) DEFAULT 'daily',
            last_sent datetime DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY is_active (is_active)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Property Views Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tables['property_views']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            listing_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            ip_address varchar(45),
            view_duration int(11) DEFAULT 0,
            referrer text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY listing_id (listing_id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Inquiries Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tables['inquiries']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            listing_id bigint(20) UNSIGNED DEFAULT NULL,
            agent_id bigint(20) UNSIGNED DEFAULT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20),
            message text,
            inquiry_type varchar(50),
            status varchar(20) DEFAULT 'new',
            ip_address varchar(45),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY listing_id (listing_id),
            KEY agent_id (agent_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Error Log Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tables['error_log']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            error_level int(11) NOT NULL,
            error_message text NOT NULL,
            error_context text,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            url varchar(500),
            ip_address varchar(45),
            error_time datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY error_level (error_level),
            KEY user_id (user_id),
            KEY error_time (error_time)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Activity Log Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tables['activity_log']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            action varchar(100) NOT NULL,
            object_type varchar(50),
            object_id bigint(20) UNSIGNED DEFAULT NULL,
            description text,
            ip_address varchar(45),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY object_lookup (object_type, object_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql);
        
        hp_log('Database tables created/updated', 'info', 'DATABASE');
    }
    
    /**
     * Get table name
     * 
     * @param string $table Table identifier
     * @return string|null
     */
    public function get_table(string $table): ?string {
        return $this->tables[$table] ?? null;
    }
    
    /**
     * Insert data into table
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @param array $format Data formats
     * @return int|false Insert ID or false on failure
     */
    public function insert(string $table, array $data, array $format = []): int|false {
        $table_name = $this->get_table($table);
        
        if (!$table_name) {
            return false;
        }
        
        $result = $this->wpdb->insert($table_name, $data, $format);
        
        return $result ? $this->wpdb->insert_id : false;
    }
    
    /**
     * Update data in table
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $where WHERE conditions
     * @param array $format Data formats
     * @param array $where_format WHERE formats
     * @return int|false Number of rows updated or false
     */
    public function update(string $table, array $data, array $where, array $format = [], array $where_format = []): int|false {
        $table_name = $this->get_table($table);
        
        if (!$table_name) {
            return false;
        }
        
        return $this->wpdb->update($table_name, $data, $where, $format, $where_format);
    }
    
    /**
     * Delete data from table
     * 
     * @param string $table Table name
     * @param array $where WHERE conditions
     * @param array $where_format WHERE formats
     * @return int|false Number of rows deleted or false
     */
    public function delete(string $table, array $where, array $where_format = []): int|false {
        $table_name = $this->get_table($table);
        
        if (!$table_name) {
            return false;
        }
        
        return $this->wpdb->delete($table_name, $where, $where_format);
    }
    
    /**
     * Get row from table
     * 
     * @param string $table Table name
     * @param array $where WHERE conditions
     * @param string $output Output type
     * @return mixed
     */
    public function get_row(string $table, array $where, string $output = OBJECT) {
        $table_name = $this->get_table($table);
        
        if (!$table_name) {
            return null;
        }
        
        $where_clause = $this->build_where_clause($where);
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} LIMIT 1";
        
        return $this->wpdb->get_row($query, $output);
    }
    
    /**
     * Get results from table
     * 
     * @param string $table Table name
     * @param array $args Query arguments
     * @param string $output Output type
     * @return array|null
     */
    public function get_results(string $table, array $args = [], string $output = OBJECT): ?array {
        $table_name = $this->get_table($table);
        
        if (!$table_name) {
            return null;
        }
        
        $defaults = [
            'where' => [],
            'orderby' => 'id',
            'order' => 'DESC',
            'limit' => 100,
            'offset' => 0,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $query = "SELECT * FROM {$table_name}";
        
        if (!empty($args['where'])) {
            $where_clause = $this->build_where_clause($args['where']);
            $query .= " WHERE {$where_clause}";
        }
        
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        $query .= " LIMIT {$args['limit']}";
        
        if ($args['offset'] > 0) {
            $query .= " OFFSET {$args['offset']}";
        }
        
        return $this->wpdb->get_results($query, $output);
    }
    
    /**
     * Build WHERE clause from array
     * 
     * @param array $where WHERE conditions
     * @return string
     */
    private function build_where_clause(array $where): string {
        $conditions = [];
        
        foreach ($where as $key => $value) {
            if (is_null($value)) {
                $conditions[] = "`{$key}` IS NULL";
            } elseif (is_array($value)) {
                $values = array_map([$this->wpdb, 'prepare'], array_fill(0, count($value), '%s'), $value);
                $conditions[] = "`{$key}` IN (" . implode(',', $values) . ")";
            } else {
                $conditions[] = $this->wpdb->prepare("`{$key}` = %s", $value);
            }
        }
        
        return implode(' AND ', $conditions);
    }
    
    /**
     * Track analytics event
     * 
     * @param string $object_type Object type
     * @param int $object_id Object ID
     * @param string $event_type Event type
     * @param mixed $event_value Event value
     * @return int|false
     */
    public function track_event(string $object_type, int $object_id, string $event_type, $event_value = null): int|false {
        return $this->insert('analytics', [
            'object_type' => $object_type,
            'object_id' => $object_id,
            'event_type' => $event_type,
            'event_value' => $event_value,
            'user_id' => get_current_user_id() ?: null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
            'session_id' => session_id() ?: null,
        ]);
    }
    
    /**
     * Log activity
     * 
     * @param string $action Action performed
     * @param string|null $object_type Object type
     * @param int|null $object_id Object ID
     * @param string|null $description Description
     * @return int|false
     */
    public function log_activity(string $action, ?string $object_type = null, ?int $object_id = null, ?string $description = null): int|false {
        return $this->insert('activity_log', [
            'user_id' => get_current_user_id() ?: null,
            'action' => $action,
            'object_type' => $object_type,
            'object_id' => $object_id,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
    
    /**
     * Drop all plugin tables
     * 
     * @return void
     */
    public function drop_tables(): void {
        foreach ($this->tables as $table) {
            $this->wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
        
        delete_option('hp_db_version');
        
        hp_log('Database tables dropped', 'info', 'DATABASE');
    }
}