/**
 * Asset Manager Admin JavaScript
 * 
 * Provides admin interface for managing intelligent asset loading
 *
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

(function($) {
    'use strict';

    /**
     * Asset Manager Admin Interface
     */
    const AssetManagerAdmin = {
        
        /**
         * Initialize admin interface
         */
        init() {
            this.bindEvents();
            this.loadAssetAnalytics();
            this.setupRealTimeMonitoring();
        },
        
        /**
         * Bind admin events
         */
        bindEvents() {
            // Clear cache button
            $(document).on('click', '#clear-asset-cache', this.clearCache.bind(this));
            
            // Refresh analytics button
            $(document).on('click', '#refresh-analytics', this.loadAssetAnalytics.bind(this));
            
            // Toggle asset optimization
            $(document).on('change', '#enable-asset-optimization', this.toggleOptimization.bind(this));
            
            // Asset bundle configuration
            $(document).on('click', '.configure-bundle', this.configureBundles.bind(this));
        },
        
        /**
         * Clear asset cache via AJAX
         */
        clearCache() {
            const $button = $('#clear-asset-cache');
            const originalText = $button.text();
            
            $button.text('Clearing...').prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_clear_asset_cache',
                    nonce: $('#asset-cache-nonce').val()
                },
                success: (response) => {
                    if (response.success) {
                        $button.text('Cache Cleared!').addClass('button-success');
                        this.showNotice('success', response.data.message);
                        
                        // Refresh analytics after cache clear
                        setTimeout(() => {
                            this.loadAssetAnalytics();
                            $button.text(originalText).removeClass('button-success').prop('disabled', false);
                        }, 2000);
                    } else {
                        this.showNotice('error', 'Failed to clear cache: ' + response.data);
                        $button.text(originalText).prop('disabled', false);
                    }
                },
                error: () => {
                    this.showNotice('error', 'Failed to clear cache');
                    $button.text(originalText).prop('disabled', false);
                }
            });
        },
        
        /**
         * Load asset analytics and performance data
         */
        loadAssetAnalytics() {
            const $container = $('#asset-analytics-container');
            
            if (!$container.length) {
                return;
            }
            
            $container.html('<div class="loading">Loading analytics...</div>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_get_asset_analytics',
                    nonce: $('#asset-analytics-nonce').val()
                },
                success: (response) => {
                    if (response.success) {
                        this.renderAnalytics(response.data);
                    } else {
                        $container.html('<div class="error">Failed to load analytics</div>');
                    }
                },
                error: () => {
                    $container.html('<div class="error">Failed to load analytics</div>');
                }
            });
        },
        
        /**
         * Render asset analytics dashboard
         */
        renderAnalytics(data) {
            const $container = $('#asset-analytics-container');
            
            const html = `
                <div class="asset-analytics-grid">
                    <div class="analytics-card">
                        <h3>Performance Metrics</h3>
                        <div class="metric">
                            <span class="metric-label">Average Load Time:</span>
                            <span class="metric-value">${data.averageLoadTime}ms</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Assets Loaded:</span>
                            <span class="metric-value">${data.assetsLoaded}</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Cache Hit Rate:</span>
                            <span class="metric-value">${data.cacheHitRate}%</span>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <h3>Asset Usage</h3>
                        <div class="usage-chart">
                            ${this.generateUsageChart(data.assetUsage)}
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <h3>Optimization Status</h3>
                        <div class="optimization-status">
                            ${this.generateOptimizationStatus(data.optimization)}
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <h3>Recent Activity</h3>
                        <div class="activity-log">
                            ${this.generateActivityLog(data.recentActivity)}
                        </div>
                    </div>
                </div>
            `;
            
            $container.html(html);
        },
        
        /**
         * Generate usage chart HTML
         */
        generateUsageChart(usage) {
            let html = '<div class="usage-bars">';
            
            for (const [asset, percentage] of Object.entries(usage)) {
                html += `
                    <div class="usage-bar">
                        <span class="usage-label">${asset}</span>
                        <div class="usage-progress">
                            <div class="usage-fill" style="width: ${percentage}%"></div>
                        </div>
                        <span class="usage-percentage">${percentage}%</span>
                    </div>
                `;
            }
            
            html += '</div>';
            return html;
        },
        
        /**
         * Generate optimization status HTML
         */
        generateOptimizationStatus(optimization) {
            let html = '<div class="optimization-items">';
            
            for (const [feature, status] of Object.entries(optimization)) {
                const statusClass = status ? 'enabled' : 'disabled';
                const statusText = status ? 'Enabled' : 'Disabled';
                
                html += `
                    <div class="optimization-item ${statusClass}">
                        <span class="optimization-feature">${feature}</span>
                        <span class="optimization-status">${statusText}</span>
                        <button class="toggle-optimization" data-feature="${feature}">
                            ${status ? 'Disable' : 'Enable'}
                        </button>
                    </div>
                `;
            }
            
            html += '</div>';
            return html;
        },
        
        /**
         * Generate activity log HTML
         */
        generateActivityLog(activity) {
            if (!activity.length) {
                return '<p>No recent activity</p>';
            }
            
            let html = '<div class="activity-items">';
            
            activity.forEach(item => {
                html += `
                    <div class="activity-item">
                        <span class="activity-time">${item.time}</span>
                        <span class="activity-message">${item.message}</span>
                        <span class="activity-type ${item.type}">${item.type}</span>
                    </div>
                `;
            });
            
            html += '</div>';
            return html;
        },
        
        /**
         * Setup real-time performance monitoring
         */
        setupRealTimeMonitoring() {
            // Monitor page load performance
            if (window.performance && window.performance.navigation) {
                const loadTime = window.performance.timing.loadEventEnd - 
                               window.performance.timing.navigationStart;
                
                this.logPerformanceMetric('page_load_time', loadTime);
            }
            
            // Monitor resource loading
            if (window.performance && window.performance.getEntriesByType) {
                const resources = window.performance.getEntriesByType('resource');
                const assetMetrics = this.analyzeResourcePerformance(resources);
                
                this.logPerformanceMetric('asset_metrics', assetMetrics);
            }
        },
        
        /**
         * Analyze resource performance
         */
        analyzeResourcePerformance(resources) {
            const metrics = {
                totalResources: resources.length,
                totalSize: 0,
                averageLoadTime: 0,
                slowestAsset: null,
                fastestAsset: null
            };
            
            let totalLoadTime = 0;
            let slowestTime = 0;
            let fastestTime = Infinity;
            
            resources.forEach(resource => {
                const loadTime = resource.responseEnd - resource.requestStart;
                const size = resource.transferSize || 0;
                
                metrics.totalSize += size;
                totalLoadTime += loadTime;
                
                if (loadTime > slowestTime) {
                    slowestTime = loadTime;
                    metrics.slowestAsset = {
                        name: resource.name.split('/').pop(),
                        time: loadTime,
                        size: size
                    };
                }
                
                if (loadTime < fastestTime) {
                    fastestTime = loadTime;
                    metrics.fastestAsset = {
                        name: resource.name.split('/').pop(),
                        time: loadTime,
                        size: size
                    };
                }
            });
            
            metrics.averageLoadTime = totalLoadTime / resources.length;
            
            return metrics;
        },
        
        /**
         * Log performance metric
         */
        logPerformanceMetric(type, data) {
            // Only log in development mode
            if (window.hphConfig && window.hphConfig.debug) {
                console.log(`[HPH Asset Manager] ${type}:`, data);
            }
            
            // Send to server for analytics (if configured)
            if (window.hphConfig && window.hphConfig.enableAnalytics) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'hph_log_performance_metric',
                        type: type,
                        data: JSON.stringify(data),
                        nonce: window.hphConfig.nonce
                    }
                });
            }
        },
        
        /**
         * Show admin notice
         */
        showNotice(type, message) {
            const $notice = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);
            
            $('.wrap h1').after($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $notice.fadeOut(() => $notice.remove());
            }, 5000);
            
            // Handle manual dismiss
            $notice.on('click', '.notice-dismiss', function() {
                $notice.fadeOut(() => $notice.remove());
            });
        },
        
        /**
         * Toggle asset optimization feature
         */
        toggleOptimization(e) {
            const $checkbox = $(e.target);
            const feature = $checkbox.data('feature');
            const enabled = $checkbox.is(':checked');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_toggle_optimization',
                    feature: feature,
                    enabled: enabled,
                    nonce: $('#optimization-nonce').val()
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice('success', 
                            `${feature} ${enabled ? 'enabled' : 'disabled'} successfully`);
                    } else {
                        this.showNotice('error', 'Failed to update optimization setting');
                        $checkbox.prop('checked', !enabled); // Revert
                    }
                },
                error: () => {
                    this.showNotice('error', 'Failed to update optimization setting');
                    $checkbox.prop('checked', !enabled); // Revert
                }
            });
        },
        
        /**
         * Configure asset bundles
         */
        configureBundles() {
            // This could open a modal or navigate to a configuration page
            this.showNotice('info', 'Bundle configuration interface coming soon!');
        }
    };
    
    /**
     * Asset Performance Monitor (Frontend)
     */
    const AssetPerformanceMonitor = {
        
        init() {
            if (!window.hphConfig || !window.hphConfig.debug) {
                return;
            }
            
            this.monitorAssetLoading();
            this.setupPerformanceObserver();
        },
        
        /**
         * Monitor asset loading performance
         */
        monitorAssetLoading() {
            const startTime = performance.now();
            
            $(window).on('load', () => {
                const loadTime = performance.now() - startTime;
                console.log(`[HPH] Total asset load time: ${loadTime.toFixed(2)}ms`);
                
                this.analyzeLoadedAssets();
            });
        },
        
        /**
         * Analyze loaded assets
         */
        analyzeLoadedAssets() {
            const styleSheets = document.styleSheets;
            const scripts = document.scripts;
            
            console.log(`[HPH] Loaded ${styleSheets.length} stylesheets`);
            console.log(`[HPH] Loaded ${scripts.length} scripts`);
            
            // Analyze which HPH assets were loaded
            const hphAssets = {
                css: [],
                js: []
            };
            
            Array.from(styleSheets).forEach(sheet => {
                if (sheet.href && sheet.href.includes('hph-')) {
                    hphAssets.css.push(sheet.href.split('/').pop());
                }
            });
            
            Array.from(scripts).forEach(script => {
                if (script.src && script.src.includes('hph-')) {
                    hphAssets.js.push(script.src.split('/').pop());
                }
            });
            
            console.log('[HPH] Asset Analysis:', hphAssets);
        },
        
        /**
         * Setup Performance Observer for detailed metrics
         */
        setupPerformanceObserver() {
            if (!window.PerformanceObserver) {
                return;
            }
            
            // Observe resource loading
            const resourceObserver = new PerformanceObserver((list) => {
                list.getEntries().forEach(entry => {
                    if (entry.name.includes('hph-') || entry.name.includes(window.location.origin)) {
                        const loadTime = entry.responseEnd - entry.requestStart;
                        if (loadTime > 100) { // Log slow assets (>100ms)
                            console.warn(`[HPH] Slow asset: ${entry.name.split('/').pop()} (${loadTime.toFixed(2)}ms)`);
                        }
                    }
                });
            });
            
            resourceObserver.observe({ entryTypes: ['resource'] });
            
            // Observe layout shifts
            const layoutShiftObserver = new PerformanceObserver((list) => {
                let cls = 0;
                list.getEntries().forEach(entry => {
                    if (!entry.hadRecentInput) {
                        cls += entry.value;
                    }
                });
                
                if (cls > 0.1) { // Warn about layout shifts
                    console.warn(`[HPH] Cumulative Layout Shift: ${cls.toFixed(4)}`);
                }
            });
            
            layoutShiftObserver.observe({ entryTypes: ['layout-shift'] });
        }
    };
    
    /**
     * Initialize when DOM is ready
     */
    $(document).ready(() => {
        // Initialize admin interface if in admin
        if (window.pagenow && window.pagenow.includes('theme') || 
            window.location.pathname.includes('wp-admin')) {
            AssetManagerAdmin.init();
        }
        
        // Initialize performance monitoring on frontend
        AssetPerformanceMonitor.init();
    });

})(jQuery);
