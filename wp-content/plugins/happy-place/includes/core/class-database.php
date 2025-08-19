<?php
/**
 * Database Class
 * 
 * Handles database operations and custom table creation for Happy Place
 *
 * @package HappyPlace\Core
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Database {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize database
     */
    private function __construct() {
        add_action('hp_activate', [$this, 'create_tables']);
    }
    
    /**
     * Initialize component
     */
    public function init() {
        // Database operations are handled via activation hook
        hp_log('Database component initialized', 'debug', 'DATABASE');
    }
    
    /**
     * Create custom database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Analytics table for tracking views, inquiries, etc.
        $analytics_table = HP_TABLE_PREFIX . 'analytics';
        $sql_analytics = "CREATE TABLE $analytics_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id bigint(20) UNSIGNED NOT NULL,
            post_type varchar(20) NOT NULL DEFAULT '',
            event_type varchar(50) NOT NULL DEFAULT '',
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            referrer varchar(255) DEFAULT NULL,
            metadata longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY post_type (post_type),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Lead management table
        $leads_table = HP_TABLE_PREFIX . 'leads';
        $sql_leads = "CREATE TABLE $leads_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            listing_id bigint(20) UNSIGNED DEFAULT NULL,
            agent_id bigint(20) UNSIGNED DEFAULT NULL,
            name varchar(100) NOT NULL DEFAULT '',
            email varchar(100) NOT NULL DEFAULT '',
            phone varchar(20) DEFAULT NULL,
            message longtext DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'new',
            source varchar(50) DEFAULT NULL,
            priority varchar(10) DEFAULT 'medium',
            assigned_to bigint(20) UNSIGNED DEFAULT NULL,
            metadata longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY listing_id (listing_id),
            KEY agent_id (agent_id),
            KEY email (email),
            KEY status (status),
            KEY assigned_to (assigned_to),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Saved searches table
        $searches_table = HP_TABLE_PREFIX . 'saved_searches';
        $sql_searches = "CREATE TABLE $searches_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            name varchar(100) NOT NULL DEFAULT '',
            search_params longtext NOT NULL,
            alert_frequency varchar(20) DEFAULT 'weekly',
            last_alert datetime DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY is_active (is_active),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_analytics);
        dbDelta($sql_leads);
        dbDelta($sql_searches);
        
        hp_log('Custom database tables created', 'info', 'DATABASE');
    }
    
    /**
     * Get analytics data
     */
    public function get_analytics($args = []) {
        global $wpdb;
        
        $defaults = [
            'post_id' => null,
            'post_type' => null,
            'event_type' => null,
            'date_from' => null,
            'date_to' => null,
            'limit' => 100,
            'offset' => 0,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $table = HP_TABLE_PREFIX . 'analytics';
        $where = ['1=1'];
        $values = [];
        
        if ($args['post_id']) {
            $where[] = 'post_id = %d';
            $values[] = $args['post_id'];
        }
        
        if ($args['post_type']) {
            $where[] = 'post_type = %s';
            $values[] = $args['post_type'];
        }
        
        if ($args['event_type']) {
            $where[] = 'event_type = %s';
            $values[] = $args['event_type'];
        }
        
        if ($args['date_from']) {
            $where[] = 'created_at >= %s';
            $values[] = $args['date_from'];
        }
        
        if ($args['date_to']) {
            $where[] = 'created_at <= %s';
            $values[] = $args['date_to'];
        }
        
        $where_clause = implode(' AND ', $where);
        $limit_clause = '';
        
        if ($args['limit'] > 0) {
            $limit_clause = $wpdb->prepare(' LIMIT %d OFFSET %d', $args['limit'], $args['offset']);
        }
        
        $sql = "SELECT * FROM $table WHERE $where_clause ORDER BY created_at DESC$limit_clause";
        
        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }
        
        return $wpdb->get_results($sql, ARRAY_A) ?: [];
    }
    
    /**
     * Track analytics event
     */
    public function track_event($post_id, $event_type, $metadata = []) {
        global $wpdb;
        
        $table = HP_TABLE_PREFIX . 'analytics';
        $post = get_post($post_id);
        
        if (!$post) {
            return false;
        }
        
        $data = [
            'post_id' => $post_id,
            'post_type' => $post->post_type,
            'event_type' => sanitize_key($event_type),
            'user_id' => get_current_user_id() ?: null,
            'ip_address' => $this->get_user_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'referrer' => wp_get_referer() ?: null,
            'metadata' => json_encode($metadata),
        ];
        
        $formats = ['%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s'];
        
        $result = $wpdb->insert($table, $data, $formats);
        
        if ($result === false) {
            hp_log("Failed to track event: {$event_type} for post: {$post_id}", 'error', 'DATABASE');
            return false;
        }
        
        return true;
    }
    
    /**
     * Save lead
     */
    public function save_lead($lead_data) {
        global $wpdb;
        
        $table = HP_TABLE_PREFIX . 'leads';
        
        $data = wp_parse_args($lead_data, [
            'listing_id' => null,
            'agent_id' => null,
            'name' => '',
            'email' => '',
            'phone' => '',
            'message' => '',
            'status' => 'new',
            'source' => 'website',
            'priority' => 'medium',
            'assigned_to' => null,
            'metadata' => json_encode([]),
        ]);
        
        // Sanitize data
        $data['name'] = sanitize_text_field($data['name']);
        $data['email'] = sanitize_email($data['email']);
        $data['phone'] = sanitize_text_field($data['phone']);
        $data['message'] = sanitize_textarea_field($data['message']);
        $data['status'] = sanitize_key($data['status']);
        $data['source'] = sanitize_key($data['source']);
        $data['priority'] = sanitize_key($data['priority']);
        
        $formats = ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s'];
        
        $result = $wpdb->insert($table, $data, $formats);
        
        if ($result === false) {
            hp_log("Failed to save lead for email: {$data['email']}", 'error', 'DATABASE');
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get leads
     */
    public function get_leads($args = []) {
        global $wpdb;
        
        $defaults = [
            'agent_id' => null,
            'listing_id' => null,
            'status' => null,
            'limit' => 50,
            'offset' => 0,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $table = HP_TABLE_PREFIX . 'leads';
        $where = ['1=1'];
        $values = [];
        
        if ($args['agent_id']) {
            $where[] = 'agent_id = %d';
            $values[] = $args['agent_id'];
        }
        
        if ($args['listing_id']) {
            $where[] = 'listing_id = %d';
            $values[] = $args['listing_id'];
        }
        
        if ($args['status']) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }
        
        $where_clause = implode(' AND ', $where);
        $limit_clause = $wpdb->prepare(' LIMIT %d OFFSET %d', $args['limit'], $args['offset']);
        
        $sql = "SELECT * FROM $table WHERE $where_clause ORDER BY created_at DESC$limit_clause";
        
        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }
        
        return $wpdb->get_results($sql, ARRAY_A) ?: [];
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = sanitize_text_field($_SERVER[$key]);
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}