<?php
/**
 * Config Sync Manager Class
 * 
 * Handles synchronization of configuration between files, database, and ACF
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
 * Config Sync Manager Class
 * 
 * @since 4.0.0
 */
class Config_Sync_Manager {
    
    /**
     * Single instance
     * 
     * @var Config_Sync_Manager|null
     */
    private static ?Config_Sync_Manager $instance = null;
    
    /**
     * Configuration sources
     * 
     * @var array
     */
    private array $sources = [
        'files' => [],
        'database' => [],
        'acf' => [],
    ];
    
    /**
     * Sync status
     * 
     * @var array
     */
    private array $sync_status = [];
    
    /**
     * Config directory
     * 
     * @var string
     */
    private string $config_dir;
    
    /**
     * Get instance
     * 
     * @return Config_Sync_Manager
     */
    public static function get_instance(): Config_Sync_Manager {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->config_dir = HP_CONFIG_DIR;
    }
    
    /**
     * Initialize config sync manager
     * 
     * @return void
     */
    public function init(): void {
        // Load configurations from all sources
        add_action('init', [$this, 'load_configurations'], 5);
        
        // Sync on admin init
        add_action('admin_init', [$this, 'maybe_sync_configurations']);
        
        // Add admin notices for sync status
        add_action('admin_notices', [$this, 'show_sync_notices']);
        
        // Register sync AJAX handlers
        add_action('wp_ajax_hp_sync_config', [$this, 'ajax_sync_config']);
        
        // Export/Import handlers
        add_action('admin_post_hp_export_config', [$this, 'handle_export']);
        add_action('admin_post_hp_import_config', [$this, 'handle_import']);
        
        hp_log('Config Sync Manager initialized', 'info', 'CONFIG_SYNC');
    }
    
    /**
     * Load configurations from all sources
     * 
     * @return void
     */
    public function load_configurations(): void {
        $this->load_file_configs();
        $this->load_database_configs();
        $this->load_acf_configs();
        
        // Check for differences
        $this->check_sync_status();
    }
    
    /**
     * Load file-based configurations
     * 
     * @return void
     */
    private function load_file_configs(): void {
        $config_files = [
            'post-types.json',
            'taxonomies.json',
            'settings.json',
            'features.json',
            'api.json',
        ];
        
        foreach ($config_files as $file) {
            $path = $this->config_dir . $file;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $config = json_decode($content, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    $key = str_replace('.json', '', $file);
                    $this->sources['files'][$key] = $config;
                    $this->sources['files'][$key]['_modified'] = filemtime($path);
                } else {
                    hp_log("Invalid JSON in config file: {$file}", 'error', 'CONFIG_SYNC');
                }
            }
        }
    }
    
    /**
     * Load database configurations
     * 
     * @return void
     */
    private function load_database_configs(): void {
        $db_configs = [
            'post-types' => 'hp_config_post_types',
            'taxonomies' => 'hp_config_taxonomies',
            'settings' => 'hp_settings',
            'features' => 'hp_features',
            'api' => 'hp_api_config',
        ];
        
        foreach ($db_configs as $key => $option_name) {
            $config = get_option($option_name);
            if ($config !== false) {
                $this->sources['database'][$key] = $config;
                $this->sources['database'][$key]['_modified'] = get_option($option_name . '_modified', 0);
            }
        }
    }
    
    /**
     * Load ACF configurations
     * 
     * @return void
     */
    private function load_acf_configs(): void {
        if (!function_exists('acf_get_field_groups')) {
            return;
        }
        
        // Load ACF field groups
        $field_groups = acf_get_field_groups();
        
        foreach ($field_groups as $group) {
            // Check if it's a Happy Place field group
            if (strpos($group['key'], 'group_hp_') === 0) {
                $key = str_replace('group_hp_', '', $group['key']);
                $this->sources['acf'][$key] = [
                    'group' => $group,
                    'fields' => acf_get_fields($group['key']),
                    '_modified' => strtotime($group['modified'] ?? 'now'),
                ];
            }
        }
    }
    
    /**
     * Check sync status between sources
     * 
     * @return void
     */
    private function check_sync_status(): void {
        $this->sync_status = [];
        
        // Get all unique keys
        $all_keys = array_unique(array_merge(
            array_keys($this->sources['files']),
            array_keys($this->sources['database']),
            array_keys($this->sources['acf'])
        ));
        
        foreach ($all_keys as $key) {
            $status = [
                'key' => $key,
                'in_files' => isset($this->sources['files'][$key]),
                'in_database' => isset($this->sources['database'][$key]),
                'in_acf' => isset($this->sources['acf'][$key]),
                'synced' => true,
                'conflicts' => [],
            ];
            
            // Check for conflicts
            $sources_with_key = [];
            foreach (['files', 'database', 'acf'] as $source) {
                if (isset($this->sources[$source][$key])) {
                    $sources_with_key[$source] = $this->sources[$source][$key];
                }
            }
            
            if (count($sources_with_key) > 1) {
                // Compare configurations
                $hashes = [];
                foreach ($sources_with_key as $source => $config) {
                    // Remove metadata before comparison
                    unset($config['_modified']);
                    $hashes[$source] = md5(json_encode($config));
                }
                
                if (count(array_unique($hashes)) > 1) {
                    $status['synced'] = false;
                    $status['conflicts'] = $this->identify_conflicts($key, $sources_with_key);
                }
            }
            
            $this->sync_status[$key] = $status;
        }
    }
    
    /**
     * Identify conflicts between configurations
     * 
     * @param string $key
     * @param array $sources
     * @return array
     */
    private function identify_conflicts(string $key, array $sources): array {
        $conflicts = [];
        
        // Get the most recent source
        $most_recent = null;
        $most_recent_time = 0;
        
        foreach ($sources as $source_name => $config) {
            $modified = $config['_modified'] ?? 0;
            if ($modified > $most_recent_time) {
                $most_recent = $source_name;
                $most_recent_time = $modified;
            }
        }
        
        $conflicts[] = [
            'type' => 'version_mismatch',
            'message' => sprintf(
                __('Configuration "%s" differs between sources. Most recent: %s', 'happy-place'),
                $key,
                $most_recent
            ),
            'most_recent' => $most_recent,
        ];
        
        return $conflicts;
    }
    
    /**
     * Maybe sync configurations
     * 
     * @return void
     */
    public function maybe_sync_configurations(): void {
        // Check if auto-sync is enabled
        if (!get_option('hp_auto_sync_config', false)) {
            return;
        }
        
        // Check if there are conflicts
        $has_conflicts = false;
        foreach ($this->sync_status as $status) {
            if (!$status['synced']) {
                $has_conflicts = true;
                break;
            }
        }
        
        if ($has_conflicts) {
            $this->sync_all_configurations();
        }
    }
    
    /**
     * Sync all configurations
     * 
     * @param string $priority_source Which source takes priority
     * @return bool
     */
    public function sync_all_configurations(string $priority_source = 'files'): bool {
        $success = true;
        
        foreach ($this->sync_status as $key => $status) {
            if (!$status['synced']) {
                if (!$this->sync_configuration($key, $priority_source)) {
                    $success = false;
                }
            }
        }
        
        // Clear cache after sync
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        return $success;
    }
    
    /**
     * Sync a specific configuration
     * 
     * @param string $key
     * @param string $priority_source
     * @return bool
     */
    public function sync_configuration(string $key, string $priority_source = 'files'): bool {
        // Get the source configuration
        if (!isset($this->sources[$priority_source][$key])) {
            return false;
        }
        
        $config = $this->sources[$priority_source][$key];
        unset($config['_modified']);
        
        $success = true;
        
        // Sync to files
        if ($priority_source !== 'files') {
            if (!$this->save_to_file($key, $config)) {
                $success = false;
            }
        }
        
        // Sync to database
        if ($priority_source !== 'database') {
            if (!$this->save_to_database($key, $config)) {
                $success = false;
            }
        }
        
        // Sync to ACF (if applicable)
        if ($priority_source !== 'acf' && $this->is_acf_config($key)) {
            if (!$this->save_to_acf($key, $config)) {
                $success = false;
            }
        }
        
        if ($success) {
            hp_log("Configuration '{$key}' synced from {$priority_source}", 'info', 'CONFIG_SYNC');
        }
        
        return $success;
    }
    
    /**
     * Save configuration to file
     * 
     * @param string $key
     * @param array $config
     * @return bool
     */
    private function save_to_file(string $key, array $config): bool {
        $file_path = $this->config_dir . $key . '.json';
        
        // Create backup
        if (file_exists($file_path)) {
            $backup_path = $this->config_dir . 'backups/' . $key . '-' . date('Y-m-d-His') . '.json';
            wp_mkdir_p(dirname($backup_path));
            copy($file_path, $backup_path);
        }
        
        // Save configuration
        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if (file_put_contents($file_path, $json) !== false) {
            $this->sources['files'][$key] = $config;
            $this->sources['files'][$key]['_modified'] = time();
            return true;
        }
        
        return false;
    }
    
    /**
     * Save configuration to database
     * 
     * @param string $key
     * @param array $config
     * @return bool
     */
    private function save_to_database(string $key, array $config): bool {
        $option_map = [
            'post-types' => 'hp_config_post_types',
            'taxonomies' => 'hp_config_taxonomies',
            'settings' => 'hp_settings',
            'features' => 'hp_features',
            'api' => 'hp_api_config',
        ];
        
        if (!isset($option_map[$key])) {
            return false;
        }
        
        $option_name = $option_map[$key];
        
        if (update_option($option_name, $config)) {
            update_option($option_name . '_modified', time());
            $this->sources['database'][$key] = $config;
            $this->sources['database'][$key]['_modified'] = time();
            return true;
        }
        
        return false;
    }
    
    /**
     * Save configuration to ACF
     * 
     * @param string $key
     * @param array $config
     * @return bool
     */
    private function save_to_acf(string $key, array $config): bool {
        if (!function_exists('acf_update_field_group')) {
            return false;
        }
        
        // This would require complex ACF field group manipulation
        // For now, just log that manual ACF sync is needed
        hp_log("ACF configuration for '{$key}' needs manual sync", 'warning', 'CONFIG_SYNC');
        
        return false;
    }
    
    /**
     * Check if configuration is ACF-related
     * 
     * @param string $key
     * @return bool
     */
    private function is_acf_config(string $key): bool {
        $acf_configs = ['listing_fields', 'agent_fields', 'settings_fields'];
        return in_array($key, $acf_configs);
    }
    
    /**
     * Show sync notices in admin
     * 
     * @return void
     */
    public function show_sync_notices(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $has_conflicts = false;
        foreach ($this->sync_status as $status) {
            if (!$status['synced']) {
                $has_conflicts = true;
                break;
            }
        }
        
        if ($has_conflicts) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong><?php _e('Happy Place Configuration Sync:', 'happy-place'); ?></strong>
                    <?php _e('Some configurations are out of sync.', 'happy-place'); ?>
                    <a href="<?php echo admin_url('admin.php?page=hp-config-sync'); ?>">
                        <?php _e('Review and sync', 'happy-place'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * AJAX handler for config sync
     * 
     * @return void
     */
    public function ajax_sync_config(): void {
        check_ajax_referer('hp_sync_config', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'happy-place'));
        }
        
        $key = sanitize_text_field($_POST['key'] ?? '');
        $source = sanitize_text_field($_POST['source'] ?? 'files');
        
        if (empty($key)) {
            wp_send_json_error(__('Invalid configuration key', 'happy-place'));
        }
        
        if ($this->sync_configuration($key, $source)) {
            wp_send_json_success([
                'message' => sprintf(__('Configuration "%s" synced successfully', 'happy-place'), $key),
            ]);
        } else {
            wp_send_json_error(__('Sync failed', 'happy-place'));
        }
    }
    
    /**
     * Handle configuration export
     * 
     * @return void
     */
    public function handle_export(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'happy-place'));
        }
        
        check_admin_referer('hp_export_config');
        
        $export_data = [
            'version' => HP_VERSION,
            'exported' => date('Y-m-d H:i:s'),
            'site_url' => home_url(),
            'configurations' => [],
        ];
        
        // Collect all configurations
        foreach ($this->sources['files'] as $key => $config) {
            unset($config['_modified']);
            $export_data['configurations'][$key] = $config;
        }
        
        // Generate filename
        $filename = 'hp-config-' . date('Y-m-d-His') . '.json';
        
        // Send download headers
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo json_encode($export_data, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Handle configuration import
     * 
     * @return void
     */
    public function handle_import(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'happy-place'));
        }
        
        check_admin_referer('hp_import_config');
        
        if (!isset($_FILES['import_file'])) {
            wp_die(__('No file uploaded', 'happy-place'));
        }
        
        $file = $_FILES['import_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_die(__('Upload failed', 'happy-place'));
        }
        
        $content = file_get_contents($file['tmp_name']);
        $import_data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_die(__('Invalid JSON file', 'happy-place'));
        }
        
        // Validate import data
        if (!isset($import_data['configurations'])) {
            wp_die(__('Invalid configuration file', 'happy-place'));
        }
        
        // Import configurations
        $success = true;
        foreach ($import_data['configurations'] as $key => $config) {
            if (!$this->save_to_file($key, $config) || !$this->save_to_database($key, $config)) {
                $success = false;
            }
        }
        
        if ($success) {
            wp_redirect(admin_url('admin.php?page=hp-config-sync&imported=1'));
        } else {
            wp_die(__('Import failed', 'happy-place'));
        }
        
        exit;
    }
    
    /**
     * Get sync status
     * 
     * @return array
     */
    public function get_sync_status(): array {
        return $this->sync_status;
    }
    
    /**
     * Get configuration sources
     * 
     * @return array
     */
    public function get_sources(): array {
        return $this->sources;
    }
}