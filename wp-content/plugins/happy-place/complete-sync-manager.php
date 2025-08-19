<?php
/**
 * Complete Configuration Sync Manager
 * Comprehensive tool for managing all Happy Place configurations
 * 
 * Access via: /wp-content/plugins/happy-place/complete-sync-manager.php
 */

// Load WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Access denied. Administrator access required.');
}

// Get sync manager instance
$sync_manager = null;
if (class_exists('HappyPlace\\Core\\Config_Sync_Manager')) {
    $sync_manager = \HappyPlace\Core\Config_Sync_Manager::get_instance();
}

echo "<h1>🎯 Complete Configuration Sync Manager</h1>";

if (!$sync_manager) {
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 4px; margin: 20px 0;'>";
    echo "<p>❌ <strong>Config Sync Manager not available.</strong> Please ensure the Happy Place plugin is properly loaded.</p>";
    echo "</div>";
    exit;
}

// Handle actions
if (isset($_GET['action'])) {
    echo "<div style='background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
    echo "<h2>🚀 Action Results</h2>";
    
    switch ($_GET['action']) {
        case 'check_changes':
            echo "<h3>🔍 Checking for Configuration Changes</h3>";
            $sync_manager->check_config_changes();
            echo "<p>✅ Configuration check completed.</p>";
            break;
            
        case 'sync_all':
            echo "<h3>🔄 Syncing All Configurations</h3>";
            $results = $sync_manager->sync_all();
            
            foreach ($results as $type => $result) {
                $type_name = ucfirst(str_replace('_', ' ', $type));
                if ($result['success']) {
                    echo "<p>✅ <strong>{$type_name}:</strong> Successfully synced {$result['synced']} items</p>";
                    if (!empty($result['errors'])) {
                        echo "<div style='margin-left: 20px; color: #666;'>";
                        foreach ($result['errors'] as $error) {
                            echo "<p>⚠️ {$error}</p>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<p>❌ <strong>{$type_name}:</strong> {$result['message']}</p>";
                }
            }
            break;
            
        case 'export_configs':
            echo "<h3>📤 Exporting Current Configurations</h3>";
            $results = $sync_manager->export_current_configs();
            
            foreach ($results as $type => $result) {
                $type_name = ucfirst(str_replace('_', ' ', $type));
                if ($result['success']) {
                    echo "<p>✅ <strong>{$type_name}:</strong> Exported {$result['count']} configurations to JSON</p>";
                } else {
                    echo "<p>❌ <strong>{$type_name}:</strong> Export failed</p>";
                }
            }
            break;
    }
    
    echo "</div>";
    echo "<p><a href='?' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>← Back to Main Dashboard</a></p>";
    echo "<hr>";
}

// Main dashboard
$sync_status = $sync_manager->get_sync_status();
?>

<style>
    .sync-dashboard {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .sync-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin: 20px 0;
    }
    
    .sync-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #ddd;
    }
    
    .sync-card h3 {
        margin: 0 0 15px 0;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .sync-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-up-to-date {
        background: #d4edda;
        color: #155724;
    }
    
    .status-needs-sync {
        background: #fff3cd;
        color: #856404;
        animation: pulse 2s infinite;
    }
    
    .status-error {
        background: #f8d7da;
        color: #721c24;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .sync-actions {
        display: flex;
        gap: 10px;
        margin: 15px 0;
        flex-wrap: wrap;
    }
    
    .sync-btn {
        padding: 10px 20px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    
    .sync-btn-primary {
        background: #0073aa;
        color: white;
    }
    
    .sync-btn-primary:hover {
        background: #005a87;
        color: white;
    }
    
    .sync-btn-success {
        background: #4caf50;
        color: white;
    }
    
    .sync-btn-success:hover {
        background: #45a049;
        color: white;
    }
    
    .sync-btn-warning {
        background: #ff9800;
        color: white;
    }
    
    .sync-btn-warning:hover {
        background: #e68900;
        color: white;
    }
    
    .sync-btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .sync-btn-secondary:hover {
        background: #545b62;
        color: white;
    }
    
    .config-overview {
        grid-column: 1 / -1;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
        padding: 40px 20px;
    }
    
    .config-overview h2 {
        margin: 0 0 10px 0;
        font-size: 28px;
    }
    
    .config-overview p {
        margin: 0;
        opacity: 0.9;
        font-size: 16px;
    }
    
    .sync-details {
        background: #f8f9fa;
        border-radius: 4px;
        padding: 15px;
        margin: 10px 0;
    }
    
    .sync-change-item {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 4px;
        padding: 12px;
        margin: 8px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .change-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .change-icon {
        font-size: 18px;
    }
    
    .change-details h4 {
        margin: 0 0 4px 0;
        font-size: 14px;
    }
    
    .change-time {
        color: #666;
        font-size: 12px;
    }
</style>

<div class="sync-dashboard">
    <div class="sync-grid">
        <div class="config-overview">
            <h2>🎯 Happy Place Configuration Manager</h2>
            <p>Centralized management for post types, taxonomies, and ACF field groups</p>
        </div>

        <!-- Current Status -->
        <div class="sync-card">
            <h3>📊 Overall Status</h3>
            
            <?php if ($sync_status['has_changes']): ?>
                <p><span class="sync-status status-needs-sync">Configuration Updates Available</span></p>
                <p><strong><?php echo count($sync_status['changes']); ?></strong> configuration<?php echo count($sync_status['changes']) !== 1 ? 's need' : ' needs'; ?> syncing</p>
                
                <div class="sync-actions">
                    <a href="?action=sync_all" class="sync-btn sync-btn-success">
                        🚀 Sync All Now
                    </a>
                    <a href="?action=check_changes" class="sync-btn sync-btn-primary">
                        🔍 Recheck Changes
                    </a>
                </div>
            <?php else: ?>
                <p><span class="sync-status status-up-to-date">All Up to Date</span></p>
                <p>All configurations are synchronized</p>
                
                <div class="sync-actions">
                    <a href="?action=check_changes" class="sync-btn sync-btn-primary">
                        🔍 Check for Changes
                    </a>
                    <a href="?action=export_configs" class="sync-btn sync-btn-secondary">
                        📤 Export Configurations
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="sync-details">
                <p><strong>Last Check:</strong> <?php echo $sync_status['last_check'] ? date('M j, Y g:i A', $sync_status['last_check']) : 'Never'; ?></p>
            </div>
        </div>

        <!-- Configuration Changes -->
        <div class="sync-card">
            <h3>📋 Configuration Changes</h3>
            
            <?php if ($sync_status['has_changes']): ?>
                <?php 
                $change_labels = [
                    'post_types' => ['📋', 'Post Types', 'Custom post type definitions'],
                    'taxonomies' => ['🏷️', 'Taxonomies', 'Taxonomy configurations'],
                    'acf_fields' => ['🔧', 'ACF Field Groups', 'Field group definitions']
                ];
                
                foreach ($sync_status['changes'] as $type => $change): 
                    $info = $change_labels[$type] ?? ['⚙️', ucfirst($type), 'Configuration'];
                ?>
                    <div class="sync-change-item">
                        <div class="change-info">
                            <span class="change-icon"><?php echo $info[0]; ?></span>
                            <div class="change-details">
                                <h4><?php echo $info[1]; ?></h4>
                                <div class="change-time">Modified: <?php echo date('M j, Y g:i A', $change['last_modified']); ?></div>
                            </div>
                        </div>
                        <div class="sync-actions">
                            <a href="?action=sync_<?php echo $type; ?>" class="sync-btn sync-btn-primary">
                                Sync
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #666; font-style: italic;">No configuration changes detected</p>
            <?php endif; ?>
        </div>

        <!-- Configuration Files -->
        <div class="sync-card">
            <h3>📁 Configuration Files</h3>
            
            <div class="sync-details">
                <h4>Post Types Configuration</h4>
                <p><code><?php echo HP_PLUGIN_DIR; ?>includes/config/post-types.json</code></p>
                <p><strong>Status:</strong> <?php echo file_exists(HP_PLUGIN_DIR . 'includes/config/post-types.json') ? '✅ Found' : '❌ Missing'; ?></p>
            </div>
            
            <div class="sync-details">
                <h4>Taxonomies Configuration</h4>
                <p><code><?php echo HP_PLUGIN_DIR; ?>includes/config/taxonomies.json</code></p>
                <p><strong>Status:</strong> <?php echo file_exists(HP_PLUGIN_DIR . 'includes/config/taxonomies.json') ? '✅ Found' : '❌ Missing'; ?></p>
            </div>
            
            <div class="sync-details">
                <h4>ACF Field Groups</h4>
                <p><code><?php echo HP_PLUGIN_DIR; ?>includes/fields/acf-json/</code></p>
                <?php 
                $acf_json_count = count(glob(HP_PLUGIN_DIR . 'includes/fields/acf-json/*.json'));
                ?>
                <p><strong>Field Groups:</strong> <?php echo $acf_json_count; ?> JSON files found</p>
            </div>
        </div>

        <!-- Sync Tools -->
        <div class="sync-card">
            <h3>🛠️ Sync Tools</h3>
            
            <div class="sync-actions">
                <a href="<?php echo HP_PLUGIN_URL; ?>acf-pro-sync.php" class="sync-btn sync-btn-primary" target="_blank">
                    🔄 ACF Pro Sync
                </a>
                <a href="<?php echo HP_PLUGIN_URL; ?>rebuild-acf.php" class="sync-btn sync-btn-warning" target="_blank">
                    🔧 ACF Rebuild Tool
                </a>
                <a href="<?php echo HP_PLUGIN_URL; ?>acf-diagnostics.php" class="sync-btn sync-btn-secondary" target="_blank">
                    🔍 ACF Diagnostics
                </a>
            </div>
            
            <div class="sync-details">
                <h4>Dashboard Integration</h4>
                <p>The sync widget automatically appears in your Happy Place dashboard when configuration changes are detected.</p>
                <p><strong>Widget Location:</strong> <code><?php echo HP_PLUGIN_URL; ?>sync-dashboard-widget.php</code></p>
            </div>
        </div>
    </div>

    <!-- Advanced Actions -->
    <div class="sync-card" style="margin-top: 20px;">
        <h3>⚙️ Advanced Actions</h3>
        
        <div class="sync-actions">
            <a href="?action=export_configs" class="sync-btn sync-btn-secondary">
                📤 Export Current Configurations
            </a>
            <a href="?action=sync_all" class="sync-btn sync-btn-success">
                🚀 Force Complete Sync
            </a>
            <a href="<?php echo admin_url('edit.php?post_type=listing'); ?>" class="sync-btn sync-btn-primary">
                🏠 Test Listings
            </a>
            <a href="<?php echo admin_url(); ?>" class="sync-btn sync-btn-secondary">
                ← WordPress Admin
            </a>
        </div>
        
        <div class="sync-details">
            <h4>How It Works</h4>
            <ol style="margin: 10px 0;">
                <li><strong>Automatic Detection:</strong> File modification times are monitored every 5 minutes</li>
                <li><strong>Dashboard Alerts:</strong> Changes trigger notifications in the Happy Place dashboard</li>
                <li><strong>One-Click Sync:</strong> Apply changes directly from the dashboard or this interface</li>
                <li><strong>Version Control Ready:</strong> All configurations are stored in JSON files for Git compatibility</li>
                <li><strong>Fallback Support:</strong> Default configurations ensure the system works even without JSON files</li>
            </ol>
        </div>
    </div>
</div>
?>