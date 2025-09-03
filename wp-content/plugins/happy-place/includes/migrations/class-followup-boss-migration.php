<?php
namespace HappyPlace\Migrations;

/**
 * FollowUp Boss Integration Migration
 * 
 * Creates necessary database tables and migrates existing settings
 * for FollowUp Boss CRM integration.
 */
class FollowUp_Boss_Migration {
    
    /**
     * Migration version
     */
    const VERSION = '1.0.0';
    
    /**
     * Migration name
     */
    const NAME = 'followup_boss_integration';
    
    /**
     * Get migration version
     */
    public function get_version() {
        return self::VERSION;
    }
    
    /**
     * Get migration name
     */
    public function get_name() {
        return self::NAME;
    }
    
    /**
     * Log migration messages
     */
    private function log($message, $level = 'info') {
        if (function_exists('hp_log')) {
            hp_log('[Migration] ' . $message, $level, 'migration');
        } else {
            error_log('[Happy Place Migration] ' . $message);
        }
    }
    
    /**
     * Execute the migration
     */
    public function up() {
        global $wpdb;
        
        $this->log('Starting FollowUp Boss integration migration...');
        
        try {
            // 1. Create FollowUp Boss sync log table
            $this->create_sync_log_table();
            
            // 2. Create FollowUp Boss person mapping table
            $this->create_person_mapping_table();
            
            // 3. Migrate existing settings to new structure
            $this->migrate_existing_settings();
            
            // 4. Add default configuration
            $this->add_default_configuration();
            
            // 5. Update lead meta for existing leads
            $this->update_existing_lead_meta();
            
            $this->log('FollowUp Boss integration migration completed successfully');
            
        } catch (\Exception $e) {
            $this->log('FollowUp Boss migration failed: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Rollback the migration
     */
    public function down() {
        global $wpdb;
        
        $this->log('Rolling back FollowUp Boss integration migration...');
        
        try {
            // Drop created tables
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}hp_followup_boss_sync_log");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}hp_followup_boss_person_mapping");
            
            // Remove migrated settings (keep original legacy settings)
            $this->remove_migrated_settings();
            
            $this->log('FollowUp Boss integration migration rolled back successfully');
            
        } catch (\Exception $e) {
            $this->log('FollowUp Boss migration rollback failed: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Create sync log table
     */
    private function create_sync_log_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_followup_boss_sync_log';
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            lead_id bigint(20) unsigned DEFAULT NULL,
            followup_boss_id varchar(255) DEFAULT NULL,
            sync_type varchar(50) NOT NULL DEFAULT 'create',
            sync_status varchar(20) NOT NULL DEFAULT 'pending',
            sync_data longtext DEFAULT NULL,
            error_message text DEFAULT NULL,
            synced_at datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY lead_id (lead_id),
            KEY followup_boss_id (followup_boss_id),
            KEY sync_status (sync_status),
            KEY created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        $this->log('Created FollowUp Boss sync log table');
    }
    
    /**
     * Create person mapping table
     */
    private function create_person_mapping_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hp_followup_boss_person_mapping';
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            lead_id bigint(20) unsigned NOT NULL,
            followup_boss_id varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            first_name varchar(255) DEFAULT NULL,
            last_name varchar(255) DEFAULT NULL,
            phone varchar(50) DEFAULT NULL,
            source varchar(100) DEFAULT NULL,
            tags text DEFAULT NULL,
            last_sync_at datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_lead_mapping (lead_id, followup_boss_id),
            KEY email (email),
            KEY followup_boss_id (followup_boss_id),
            KEY last_sync_at (last_sync_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        $this->log('Created FollowUp Boss person mapping table');
    }
    
    /**
     * Migrate existing settings to new structure
     */
    private function migrate_existing_settings() {
        $legacy_prefix = 'hp_followup_boss_';
        
        // Get existing legacy settings
        $legacy_settings = [
            'api_key' => get_option($legacy_prefix . 'api_key', ''),
            'enabled' => get_option($legacy_prefix . 'enabled', false),
            'auto_sync' => get_option($legacy_prefix . 'auto_sync', false),
            'default_source' => get_option($legacy_prefix . 'default_source', 'Website'),
            'default_status' => get_option($legacy_prefix . 'default_status', 'New'),
            'assign_to_agent' => get_option($legacy_prefix . 'assign_to_agent', false)
        ];
        
        // Only migrate if we have legacy settings
        $has_legacy_settings = false;
        foreach ($legacy_settings as $key => $value) {
            if (!empty($value) || $value === true) {
                $has_legacy_settings = true;
                break;
            }
        }
        
        if (!$has_legacy_settings) {
            $this->log('No legacy FollowUp Boss settings found to migrate');
            return;
        }
        
        // Get current integration settings
        $integration_settings = get_option('hp_integration_settings', []);
        
        // Migrate to new structure if not already present
        if (!isset($integration_settings['followup_boss'])) {
            $integration_settings['followup_boss'] = [
                'enabled' => (bool) $legacy_settings['enabled'],
                'api_key' => $legacy_settings['api_key'],
                'auto_sync' => (bool) $legacy_settings['auto_sync'],
                'lead_source' => $legacy_settings['default_source'],
                'default_status' => $legacy_settings['default_status'],
                'assign_to_agent' => (bool) $legacy_settings['assign_to_agent'],
                'migrated_from_legacy' => true,
                'migration_date' => current_time('mysql')
            ];
            
            update_option('hp_integration_settings', $integration_settings);
            $this->log('Migrated legacy FollowUp Boss settings to new structure');
        } else {
            $this->log('FollowUp Boss settings already exist in new structure');
        }
    }
    
    /**
     * Add default configuration
     */
    private function add_default_configuration() {
        $integration_settings = get_option('hp_integration_settings', []);
        
        // Add default FollowUp Boss configuration if not present
        if (!isset($integration_settings['followup_boss'])) {
            $integration_settings['followup_boss'] = [
                'enabled' => false,
                'api_key' => '',
                'auto_sync' => true,
                'lead_source' => 'Website',
                'default_status' => 'New',
                'assign_to_agent' => false,
                'sync_on_form_submit' => true,
                'sync_on_lead_update' => false,
                'webhook_url' => '',
                'webhook_secret' => '',
                'retry_failed_syncs' => true,
                'max_retry_attempts' => 3,
                'field_mapping' => [
                    'first_name' => 'firstName',
                    'last_name' => 'lastName',
                    'email' => 'email',
                    'phone' => 'phone',
                    'message' => 'note'
                ]
            ];
            
            update_option('hp_integration_settings', $integration_settings);
            $this->log('Added default FollowUp Boss configuration');
        }
    }
    
    /**
     * Update existing lead meta for FollowUp Boss tracking
     */
    private function update_existing_lead_meta() {
        global $wpdb;
        
        // Find leads that might already be synced (have FollowUp Boss ID)
        $existing_synced_leads = $wpdb->get_results("
            SELECT post_id, meta_value as followup_boss_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_followup_boss_id' 
            AND meta_value != ''
        ");
        
        if (empty($existing_synced_leads)) {
            $this->log('No existing FollowUp Boss synced leads found');
            return;
        }
        
        $sync_log_table = $wpdb->prefix . 'hp_followup_boss_sync_log';
        $mapping_table = $wpdb->prefix . 'hp_followup_boss_person_mapping';
        
        foreach ($existing_synced_leads as $synced_lead) {
            $lead_id = $synced_lead->post_id;
            $followup_boss_id = $synced_lead->followup_boss_id;
            
            // Get lead data
            $lead_post = get_post($lead_id);
            if (!$lead_post) continue;
            
            $lead_meta = get_post_meta($lead_id);
            $email = $lead_meta['_lead_email'][0] ?? '';
            $first_name = $lead_meta['_lead_first_name'][0] ?? '';
            $last_name = $lead_meta['_lead_last_name'][0] ?? '';
            $phone = $lead_meta['_lead_phone'][0] ?? '';
            $source = $lead_meta['_lead_source'][0] ?? 'Website';
            
            // Add to sync log
            $wpdb->insert(
                $sync_log_table,
                [
                    'lead_id' => $lead_id,
                    'followup_boss_id' => $followup_boss_id,
                    'sync_type' => 'create',
                    'sync_status' => 'completed',
                    'synced_at' => $lead_post->post_date,
                    'created_at' => current_time('mysql')
                ],
                ['%d', '%s', '%s', '%s', '%s', '%s']
            );
            
            // Add to person mapping
            $wpdb->insert(
                $mapping_table,
                [
                    'lead_id' => $lead_id,
                    'followup_boss_id' => $followup_boss_id,
                    'email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone' => $phone,
                    'source' => $source,
                    'last_sync_at' => $lead_post->post_date,
                    'created_at' => current_time('mysql')
                ],
                ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
            );
        }
        
        $this->log('Updated ' . count($existing_synced_leads) . ' existing synced leads');
    }
    
    /**
     * Remove migrated settings (for rollback)
     */
    private function remove_migrated_settings() {
        $integration_settings = get_option('hp_integration_settings', []);
        
        if (isset($integration_settings['followup_boss'])) {
            unset($integration_settings['followup_boss']);
            update_option('hp_integration_settings', $integration_settings);
            $this->log('Removed FollowUp Boss settings from integration configuration');
        }
    }
}
