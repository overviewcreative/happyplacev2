<?php
/**
 * Asset Manager Admin Page
 * 
 * Admin interface for the intelligent asset manager
 *
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class HPH_Asset_Admin {
    
    /**
     * Initialize admin interface
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_theme_page(
            __('Asset Manager', 'happy-place-theme'),
            __('Asset Manager', 'happy-place-theme'),
            'manage_options',
            'hph-asset-manager',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('hph_asset_settings', 'hph_asset_optimization');
        register_setting('hph_asset_settings', 'hph_enable_intelligent_loading');
        register_setting('hph_asset_settings', 'hph_enable_critical_css');
        register_setting('hph_asset_settings', 'hph_enable_preloading');
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'appearance_page_hph-asset-manager') {
            return;
        }
        
        wp_enqueue_style(
            'hph-asset-admin',
            get_template_directory_uri() . '/assets/css/admin/admin-assets.css',
            array(),
            HPH_VERSION
        );
        
        wp_enqueue_script(
            'hph-asset-admin',
            get_template_directory_uri() . '/assets/js/admin/asset-manager.js',
            array('jquery'),
            HPH_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('hph-asset-admin', 'hphAssetAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_asset_admin'),
            'enableAnalytics' => true,
            'debug' => defined('WP_DEBUG') && WP_DEBUG
        ));
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        $current_optimization = get_option('hph_asset_optimization', array());
        $intelligent_loading = get_option('hph_enable_intelligent_loading', true);
        $critical_css = get_option('hph_enable_critical_css', true);
        $preloading = get_option('hph_enable_preloading', true);
        ?>
        <div class="wrap">
            <h1><?php _e('Intelligent Asset Manager', 'happy-place-theme'); ?></h1>
            
            <div class="hph-notice info">
                <strong>Intelligent Asset Loading:</strong> The asset manager analyzes each page to load only the assets that are actually needed, improving performance and reducing bandwidth usage.
            </div>
            
            <!-- Asset Manager Actions -->
            <div class="asset-manager-actions">
                <button id="clear-asset-cache" class="button button-secondary">
                    <?php _e('Clear Asset Cache', 'happy-place-theme'); ?>
                </button>
                <button id="refresh-analytics" class="button button-secondary">
                    <?php _e('Refresh Analytics', 'happy-place-theme'); ?>
                </button>
                <?php wp_nonce_field('hph_asset_cache', 'asset-cache-nonce', false); ?>
                <?php wp_nonce_field('hph_asset_analytics', 'asset-analytics-nonce', false); ?>
                <?php wp_nonce_field('hph_optimization', 'optimization-nonce', false); ?>
            </div>
            
            <!-- Asset Analytics Dashboard -->
            <div id="asset-analytics-container">
                <div class="loading">Loading analytics...</div>
            </div>
            
            <!-- Asset Manager Settings -->
            <div class="asset-manager-settings">
                <h2><?php _e('Asset Loading Settings', 'happy-place-theme'); ?></h2>
                
                <form method="post" action="options.php">
                    <?php settings_fields('hph_asset_settings'); ?>
                    
                    <div class="setting-group">
                        <div class="setting-toggle">
                            <input type="checkbox" id="enable-intelligent-loading" name="hph_enable_intelligent_loading" value="1" <?php checked($intelligent_loading, true); ?>>
                            <label for="enable-intelligent-loading" class="setting-label">
                                <?php _e('Enable Intelligent Asset Loading', 'happy-place-theme'); ?>
                            </label>
                        </div>
                        <p class="setting-description">
                            <?php _e('Analyzes page content to load only necessary assets. Significantly improves performance by reducing unnecessary HTTP requests.', 'happy-place-theme'); ?>
                        </p>
                    </div>
                    
                    <div class="setting-group">
                        <div class="setting-toggle">
                            <input type="checkbox" id="enable-critical-css" name="hph_enable_critical_css" value="1" <?php checked($critical_css, true); ?>>
                            <label for="enable-critical-css" class="setting-label">
                                <?php _e('Enable Critical CSS Inlining', 'happy-place-theme'); ?>
                            </label>
                        </div>
                        <p class="setting-description">
                            <?php _e('Inlines critical CSS directly in the HTML head to prevent render-blocking and improve First Contentful Paint (FCP).', 'happy-place-theme'); ?>
                        </p>
                    </div>
                    
                    <div class="setting-group">
                        <div class="setting-toggle">
                            <input type="checkbox" id="enable-preloading" name="hph_enable_preloading" value="1" <?php checked($preloading, true); ?>>
                            <label for="enable-preloading" class="setting-label">
                                <?php _e('Enable Resource Preloading', 'happy-place-theme'); ?>
                            </label>
                        </div>
                        <p class="setting-description">
                            <?php _e('Preloads critical resources and adds DNS prefetch hints for external services to reduce loading times.', 'happy-place-theme'); ?>
                        </p>
                    </div>
                    
                    <?php submit_button(__('Save Settings', 'happy-place-theme')); ?>
                </form>
            </div>
            
            <!-- Asset Registry Information -->
            <div class="asset-manager-settings">
                <h2><?php _e('Asset Registry Information', 'happy-place-theme'); ?></h2>
                
                <div class="asset-analytics-grid">
                    <div class="analytics-card">
                        <h3><?php _e('Intelligent Detection', 'happy-place-theme'); ?></h3>
                        <p>The asset manager analyzes:</p>
                        <ul>
                            <li>✅ Page content for interactive elements</li>
                            <li>✅ Template requirements</li>
                            <li>✅ ACF flexible content layouts</li>
                            <li>✅ User device and capabilities</li>
                            <li>✅ Previous page behavior patterns</li>
                        </ul>
                    </div>
                    
                    <div class="analytics-card">
                        <h3><?php _e('Performance Optimizations', 'happy-place-theme'); ?></h3>
                        <ul>
                            <li>🚀 Critical CSS inlining</li>
                            <li>🚀 Lazy loading for non-critical assets</li>
                            <li>🚀 Intelligent bundling</li>
                            <li>🚀 Resource preloading</li>
                            <li>🚀 Async/defer script loading</li>
                            <li>🚀 DNS prefetch hints</li>
                        </ul>
                    </div>
                    
                    <div class="analytics-card">
                        <h3><?php _e('Asset Categories', 'happy-place-theme'); ?></h3>
                        <div class="metric">
                            <span class="metric-label">Critical Assets:</span>
                            <span class="metric-value">3</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Base Assets:</span>
                            <span class="metric-value">8</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Conditional Assets:</span>
                            <span class="metric-value">15</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">External Assets:</span>
                            <span class="metric-value">4</span>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <h3><?php _e('Loading Strategy', 'happy-place-theme'); ?></h3>
                        <p><strong>Phase 1:</strong> Critical CSS inlined</p>
                        <p><strong>Phase 2:</strong> Core framework JS</p>
                        <p><strong>Phase 3:</strong> Page-specific assets</p>
                        <p><strong>Phase 4:</strong> Interactive elements</p>
                        <p><strong>Phase 5:</strong> Advanced features (lazy)</p>
                    </div>
                </div>
            </div>
            
            <!-- Debug Information -->
            <?php if (defined('WP_DEBUG') && WP_DEBUG) : ?>
                <div class="asset-manager-settings">
                    <h2><?php _e('Debug Information', 'happy-place-theme'); ?></h2>
                    <pre style="background: #f0f0f1; padding: 15px; border-radius: 4px; overflow-x: auto;">
Current Asset Service: <?php echo class_exists('HPH_Assets_Intelligent') ? 'Intelligent Asset Manager' : 'Legacy Asset Manager'; ?>
WordPress Version: <?php echo get_bloginfo('version'); ?>
Theme Version: <?php echo HPH_VERSION; ?>
PHP Version: <?php echo PHP_VERSION; ?>
Debug Mode: <?php echo WP_DEBUG ? 'Enabled' : 'Disabled'; ?>
Cache Status: <?php echo wp_using_ext_object_cache() ? 'External Cache Active' : 'Default Cache'; ?>
                    </pre>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
            .setting-toggle {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 8px;
            }
            
            .setting-toggle input[type="checkbox"] {
                margin: 0;
            }
            
            .setting-label {
                font-weight: 600;
                margin: 0;
            }
            
            .analytics-card ul {
                list-style: none;
                padding: 0;
            }
            
            .analytics-card li {
                padding: 4px 0;
                border-bottom: 1px solid #f0f0f1;
            }
            
            .analytics-card li:last-child {
                border-bottom: none;
            }
        </style>
        <?php
    }
}

// Initialize admin interface
if (is_admin()) {
    new HPH_Asset_Admin();
}