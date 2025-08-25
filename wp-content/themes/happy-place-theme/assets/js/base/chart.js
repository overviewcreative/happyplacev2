/**
 * Chart Component JavaScript
 * Dashboard charts and data visualization
 * 
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Chart namespace
    HPH.Chart = {
        
        /**
         * Chart instances storage
         */
        instances: {},
        
        /**
         * Initialize charts
         */
        init: function() {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js is not loaded. Charts will not be initialized.');
                return;
            }
            
            $('.hph-dashboard-chart').each(function() {
                HPH.Chart.initChart($(this));
            });
            
            // Initialize chart controls
            HPH.Chart.initControls();
        },
        
        /**
         * Initialize individual chart
         */
        initChart: function($container) {
            var chartId = $container.data('chart-id') || HPH.Chart.generateId();
            var chartType = $container.data('chart-type') || 'line';
            var chartData = $container.data('chart-data');
            var chartOptions = $container.data('chart-options') || {};
            
            // Create canvas if it doesn't exist
            var $canvas = $container.find('canvas');
            if (!$canvas.length) {
                $canvas = $('<canvas></canvas>');
                $container.append($canvas);
            }
            
            var ctx = $canvas[0].getContext('2d');
            
            // Default chart configuration
            var config = {
                type: chartType,
                data: chartData || HPH.Chart.getDefaultData(chartType),
                options: $.extend(true, {}, HPH.Chart.getDefaultOptions(chartType), chartOptions)
            };
            
            // Create chart instance
            var chart = new Chart(ctx, config);
            
            // Store instance
            HPH.Chart.instances[chartId] = {
                chart: chart,
                $container: $container,
                type: chartType,
                id: chartId
            };
            
            // Store chart ID on container
            $container.data('chart-id', chartId);
            
            // Initialize responsive behavior
            HPH.Chart.makeResponsive($container, chart);
            
            return chart;
        },
        
        /**
         * Initialize chart controls
         */
        initControls: function() {
            // Period selector
            $('.chart-period-selector').on('change', function() {
                var $selector = $(this);
                var chartId = $selector.data('chart-id');
                var period = $selector.val();
                
                if (HPH.Chart.instances[chartId]) {
                    HPH.Chart.updatePeriod(chartId, period);
                }
            });
            
            // Chart type selector
            $('.chart-type-selector').on('change', function() {
                var $selector = $(this);
                var chartId = $selector.data('chart-id');
                var newType = $selector.val();
                
                if (HPH.Chart.instances[chartId]) {
                    HPH.Chart.changeType(chartId, newType);
                }
            });
            
            // Refresh button
            $('.chart-refresh').on('click', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var chartId = $btn.data('chart-id');
                
                if (HPH.Chart.instances[chartId]) {
                    HPH.Chart.refresh(chartId);
                }
            });
            
            // Download button
            $('.chart-download').on('click', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var chartId = $btn.data('chart-id');
                var format = $btn.data('format') || 'png';
                
                if (HPH.Chart.instances[chartId]) {
                    HPH.Chart.download(chartId, format);
                }
            });
        },
        
        /**
         * Get default chart data
         */
        getDefaultData: function(type) {
            var labels = [];
            var data = [];
            
            // Generate sample data based on type
            switch (type) {
                case 'line':
                case 'bar':
                    // Last 7 days
                    for (var i = 6; i >= 0; i--) {
                        var date = new Date();
                        date.setDate(date.getDate() - i);
                        labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                        data.push(Math.floor(Math.random() * 100) + 10);
                    }
                    break;
                    
                case 'doughnut':
                case 'pie':
                    labels = ['Active', 'Sold', 'Pending', 'Off Market'];
                    data = [45, 25, 20, 10];
                    break;
                    
                case 'radar':
                    labels = ['Price', 'Location', 'Size', 'Amenities', 'Condition', 'Market'];
                    data = [4, 5, 3, 4, 4, 5];
                    break;
            }
            
            return {
                labels: labels,
                datasets: [{
                    label: 'Sample Data',
                    data: data,
                    backgroundColor: HPH.Chart.getColors(type === 'doughnut' || type === 'pie' ? data.length : 1),
                    borderColor: HPH.Chart.getColors(1)[0],
                    borderWidth: 2,
                    fill: type === 'line' ? false : true
                }]
            };
        },
        
        /**
         * Get default chart options
         */
        getDefaultOptions: function(type) {
            var baseOptions = {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeOutCubic'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 8,
                        padding: 12
                    }
                }
            };
            
            // Type-specific options
            switch (type) {
                case 'line':
                    baseOptions.scales = {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    };
                    baseOptions.elements = {
                        point: {
                            radius: 4,
                            hoverRadius: 6
                        },
                        line: {
                            tension: 0.3
                        }
                    };
                    break;
                    
                case 'bar':
                    baseOptions.scales = {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    };
                    break;
                    
                case 'doughnut':
                case 'pie':
                    baseOptions.cutout = type === 'doughnut' ? '60%' : 0;
                    baseOptions.plugins.legend.position = 'right';
                    break;
                    
                case 'radar':
                    baseOptions.scales = {
                        r: {
                            beginAtZero: true,
                            max: 5
                        }
                    };
                    break;
            }
            
            return baseOptions;
        },
        
        /**
         * Get chart colors
         */
        getColors: function(count) {
            var primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--hph-primary').trim();
            var colors = [
                primaryColor,
                '#10B981', // Green
                '#F59E0B', // Yellow
                '#EF4444', // Red
                '#8B5CF6', // Purple
                '#06B6D4', // Cyan
                '#F97316', // Orange
                '#84CC16', // Lime
            ];
            
            return colors.slice(0, count);
        },
        
        /**
         * Make chart responsive
         */
        makeResponsive: function($container, chart) {
            // Handle container resize
            var resizeObserver = new ResizeObserver(function(entries) {
                chart.resize();
            });
            
            resizeObserver.observe($container[0]);
        },
        
        /**
         * Update chart period
         */
        updatePeriod: function(chartId, period) {
            var instance = HPH.Chart.instances[chartId];
            if (!instance) return;
            
            // Show loading state
            instance.$container.addClass('loading');
            
            $.ajax({
                url: hphContext.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_get_chart_data',
                    nonce: hphContext.nonce,
                    chart_id: chartId,
                    period: period
                },
                success: function(response) {
                    if (response.success) {
                        // Update chart data
                        instance.chart.data = response.data;
                        instance.chart.update('active');
                    } else {
                        HPH.showAlert(response.data.message || 'Failed to update chart', 'error');
                    }
                },
                error: function() {
                    HPH.showAlert('Error updating chart data', 'error');
                },
                complete: function() {
                    instance.$container.removeClass('loading');
                }
            });
        },
        
        /**
         * Change chart type
         */
        changeType: function(chartId, newType) {
            var instance = HPH.Chart.instances[chartId];
            if (!instance) return;
            
            // Destroy current chart
            instance.chart.destroy();
            
            // Create new chart with new type
            var config = {
                type: newType,
                data: instance.chart.data,
                options: HPH.Chart.getDefaultOptions(newType)
            };
            
            var ctx = instance.$container.find('canvas')[0].getContext('2d');
            instance.chart = new Chart(ctx, config);
            instance.type = newType;
        },
        
        /**
         * Refresh chart data
         */
        refresh: function(chartId) {
            var instance = HPH.Chart.instances[chartId];
            if (!instance) return;
            
            instance.$container.addClass('loading');
            
            $.ajax({
                url: hphContext.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_refresh_chart',
                    nonce: hphContext.nonce,
                    chart_id: chartId
                },
                success: function(response) {
                    if (response.success) {
                        instance.chart.data = response.data;
                        instance.chart.update('active');
                        HPH.showAlert('Chart refreshed', 'success');
                    } else {
                        HPH.showAlert(response.data.message || 'Failed to refresh chart', 'error');
                    }
                },
                error: function() {
                    HPH.showAlert('Error refreshing chart', 'error');
                },
                complete: function() {
                    instance.$container.removeClass('loading');
                }
            });
        },
        
        /**
         * Download chart as image
         */
        download: function(chartId, format) {
            var instance = HPH.Chart.instances[chartId];
            if (!instance) return;
            
            var canvas = instance.chart.canvas;
            var link = document.createElement('a');
            
            link.download = `chart-${chartId}.${format}`;
            link.href = canvas.toDataURL(`image/${format}`);
            link.click();
        },
        
        /**
         * Update chart data
         */
        updateData: function(chartId, newData) {
            var instance = HPH.Chart.instances[chartId];
            if (!instance) return;
            
            instance.chart.data = newData;
            instance.chart.update('active');
        },
        
        /**
         * Destroy chart
         */
        destroy: function(chartId) {
            var instance = HPH.Chart.instances[chartId];
            if (!instance) return;
            
            instance.chart.destroy();
            delete HPH.Chart.instances[chartId];
        },
        
        /**
         * Generate unique ID
         */
        generateId: function() {
            return 'chart-' + Math.random().toString(36).substr(2, 9);
        }
    };
    
    // Initialize charts when DOM is ready
    $(document).ready(function() {
        // Wait for Chart.js to load
        if (typeof Chart !== 'undefined') {
            HPH.Chart.init();
        } else {
            // Poll for Chart.js availability
            var checkChart = setInterval(function() {
                if (typeof Chart !== 'undefined') {
                    clearInterval(checkChart);
                    HPH.Chart.init();
                }
            }, 100);
            
            // Stop polling after 5 seconds
            setTimeout(function() {
                clearInterval(checkChart);
            }, 5000);
        }
    });
    
    // Expose chart methods globally
    window.hphChart = {
        create: function(container, config) {
            return HPH.Chart.initChart($(container));
        },
        
        update: function(chartId, data) {
            HPH.Chart.updateData(chartId, data);
        },
        
        refresh: function(chartId) {
            HPH.Chart.refresh(chartId);
        },
        
        destroy: function(chartId) {
            HPH.Chart.destroy(chartId);
        },
        
        download: function(chartId, format) {
            HPH.Chart.download(chartId, format || 'png');
        }
    };
    
})(jQuery);