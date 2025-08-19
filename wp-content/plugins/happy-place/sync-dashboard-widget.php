<?php
/**
 * Sync Dashboard Widget
 * Displays configuration sync status in the Happy Place dashboard
 * 
 * Access via: /wp-content/plugins/happy-place/sync-dashboard-widget.php
 * Or include in dashboard templates
 */

// Only include if called from within WordPress
if (!defined('ABSPATH') && !isset($GLOBALS['wp'])) {
    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
}

// Security check
if (!current_user_can('manage_options')) {
    return;
}

// Get sync manager instance
$sync_manager = null;
if (class_exists('HappyPlace\\Core\\Config_Sync_Manager')) {
    $sync_manager = \HappyPlace\Core\Config_Sync_Manager::get_instance();
}

if (!$sync_manager) {
    return;
}

$sync_status = $sync_manager->get_sync_status();
?>

<div class="hp-sync-dashboard-widget" id="hp-sync-widget">
    <style>
        .hp-sync-dashboard-widget {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .hp-sync-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .hp-sync-title {
            display: flex;
            align-items: center;
            font-size: 16px;
            font-weight: 600;
        }
        
        .hp-sync-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .hp-sync-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .hp-sync-indicator.up-to-date {
            background: #4caf50;
        }
        
        .hp-sync-indicator.needs-sync {
            background: #ff9800;
            animation: pulse 2s infinite;
        }
        
        .hp-sync-indicator.error {
            background: #f44336;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .hp-sync-changes {
            margin: 15px 0;
        }
        
        .hp-sync-change-item {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .hp-sync-change-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .hp-sync-change-icon {
            font-size: 18px;
        }
        
        .hp-sync-change-details {
            font-size: 14px;
        }
        
        .hp-sync-change-title {
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .hp-sync-change-time {
            color: #666;
            font-size: 12px;
        }
        
        .hp-sync-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        
        .hp-sync-btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        
        .hp-sync-btn-primary {
            background: #0073aa;
            color: white;
        }
        
        .hp-sync-btn-primary:hover {
            background: #005a87;
            color: white;
        }
        
        .hp-sync-btn-secondary {
            background: #f1f1f1;
            color: #333;
        }
        
        .hp-sync-btn-secondary:hover {
            background: #e1e1e1;
        }
        
        .hp-sync-btn-success {
            background: #4caf50;
            color: white;
        }
        
        .hp-sync-btn-success:hover {
            background: #45a049;
            color: white;
        }
        
        .hp-sync-no-changes {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        .hp-sync-loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .hp-sync-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #0073aa;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <div class="hp-sync-header">
        <div class="hp-sync-title">
            🔄 Configuration Sync
        </div>
        <div class="hp-sync-status">
            <span class="hp-sync-indicator <?php echo $sync_status['has_changes'] ? 'needs-sync' : 'up-to-date'; ?>"></span>
            <span><?php echo $sync_status['has_changes'] ? 'Updates Available' : 'Up to Date'; ?></span>
        </div>
    </div>

    <div class="hp-sync-content">
        <?php if ($sync_status['has_changes']): ?>
            <div class="hp-sync-changes">
                <?php 
                $type_labels = [
                    'post_types' => ['📋 Post Types', 'Custom post type configurations have been updated'],
                    'taxonomies' => ['🏷️ Taxonomies', 'Taxonomy configurations have been updated'], 
                    'acf_fields' => ['🔧 ACF Field Groups', 'Field group configurations have been updated']
                ];
                
                foreach ($sync_status['changes'] as $type => $change): 
                    if (in_array($type, $sync_status['dismissed'])) continue;
                    
                    $label_info = $type_labels[$type] ?? [ucfirst($type), 'Configuration has been updated'];
                ?>
                    <div class="hp-sync-change-item" data-type="<?php echo esc_attr($type); ?>">
                        <div class="hp-sync-change-info">
                            <span class="hp-sync-change-icon"><?php echo $label_info[0]; ?></span>
                            <div class="hp-sync-change-details">
                                <div class="hp-sync-change-title"><?php echo $label_info[1]; ?></div>
                                <div class="hp-sync-change-time">
                                    Modified: <?php echo date('M j, Y g:i A', $change['last_modified']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="hp-sync-change-actions">
                            <button class="hp-sync-btn hp-sync-btn-primary hp-sync-individual" 
                                    data-type="<?php echo esc_attr($type); ?>">
                                🔄 Sync
                            </button>
                            <button class="hp-sync-btn hp-sync-btn-secondary hp-dismiss-change" 
                                    data-type="<?php echo esc_attr($type); ?>">
                                ✕
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="hp-sync-actions">
                <button class="hp-sync-btn hp-sync-btn-success" id="hp-sync-all">
                    🚀 Sync All Changes
                </button>
                <a href="<?php echo HP_PLUGIN_URL; ?>acf-pro-sync.php" 
                   class="hp-sync-btn hp-sync-btn-secondary" target="_blank">
                    ⚙️ Advanced Sync
                </a>
            </div>

        <?php else: ?>
            <div class="hp-sync-no-changes">
                ✅ All configurations are up to date
                <br><small>Last checked: <?php echo $sync_status['last_check'] ? date('M j, Y g:i A', $sync_status['last_check']) : 'Never'; ?></small>
            </div>
        <?php endif; ?>

        <div class="hp-sync-loading">
            <div class="hp-sync-spinner"></div>
            <p>Syncing configurations...</p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const widget = $('#hp-sync-widget');
    const loading = widget.find('.hp-sync-loading');
    const content = widget.find('.hp-sync-content');
    
    // Individual sync buttons
    $('.hp-sync-individual').on('click', function() {
        const type = $(this).data('type');
        const item = $(this).closest('.hp-sync-change-item');
        
        syncConfiguration(type, function(success, data) {
            if (success) {
                item.fadeOut(300, function() {
                    $(this).remove();
                    checkIfNoChangesRemain();
                });
                showSyncResult('✅ ' + type.replace('_', ' ') + ' synced successfully');
            } else {
                showSyncResult('❌ Sync failed: ' + (data.message || 'Unknown error'), 'error');
            }
        });
    });
    
    // Sync all button
    $('#hp-sync-all').on('click', function() {
        syncConfiguration('all', function(success, data) {
            if (success) {
                $('.hp-sync-change-item').fadeOut(300, function() {
                    $(this).remove();
                });
                updateSyncStatus();
                showSyncResult('✅ All configurations synced successfully');
            } else {
                showSyncResult('❌ Sync failed: ' + (data.message || 'Unknown error'), 'error');
            }
        });
    });
    
    // Dismiss individual changes
    $('.hp-dismiss-change').on('click', function() {
        const type = $(this).data('type');
        const item = $(this).closest('.hp-sync-change-item');
        
        $.post(ajaxurl, {
            action: 'hp_dismiss_sync_notice',
            type: type,
            nonce: '<?php echo wp_create_nonce("hp_dashboard"); ?>'
        }, function(response) {
            if (response.success) {
                item.fadeOut(300, function() {
                    $(this).remove();
                    checkIfNoChangesRemain();
                });
            }
        });
    });
    
    function syncConfiguration(type, callback) {
        showLoading(true);
        
        $.post(ajaxurl, {
            action: 'hp_sync_config',
            type: type,
            nonce: '<?php echo wp_create_nonce("hp_dashboard"); ?>'
        }, function(response) {
            showLoading(false);
            
            if (response.success) {
                callback(true, response.data);
            } else {
                callback(false, response.data);
            }
        }).fail(function() {
            showLoading(false);
            callback(false, {message: 'Network error'});
        });
    }
    
    function showLoading(show) {
        if (show) {
            content.hide();
            loading.show();
        } else {
            loading.hide();
            content.show();
        }
    }
    
    function showSyncResult(message, type = 'success') {
        const alertClass = type === 'error' ? 'notice-error' : 'notice-success';
        const alert = $('<div class="notice ' + alertClass + ' is-dismissible" style="margin: 10px 0;"><p>' + message + '</p></div>');
        
        widget.prepend(alert);
        
        setTimeout(function() {
            alert.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    function checkIfNoChangesRemain() {
        if ($('.hp-sync-change-item').length === 0) {
            updateSyncStatus();
        }
    }
    
    function updateSyncStatus() {
        const indicator = $('.hp-sync-indicator');
        const statusText = $('.hp-sync-status span').last();
        
        indicator.removeClass('needs-sync').addClass('up-to-date');
        statusText.text('Up to Date');
        
        $('.hp-sync-changes').html('<div class="hp-sync-no-changes">✅ All configurations are up to date</div>');
        $('.hp-sync-actions').hide();
    }
    
    // Auto-refresh sync status every 5 minutes
    setInterval(function() {
        $.post(ajaxurl, {
            action: 'hp_get_sync_status',
            nonce: '<?php echo wp_create_nonce("hp_dashboard"); ?>'
        }, function(response) {
            if (response.success && response.data.has_changes) {
                // Refresh the widget if new changes detected
                location.reload();
            }
        });
    }, 300000); // 5 minutes
});
</script>