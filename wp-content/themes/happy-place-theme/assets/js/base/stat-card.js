/**
 * Stat Card Component JavaScript
 * Statistics display cards with animations and charts
 * 
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Stat Card namespace
    HPH.StatCard = {
        
        /**
         * Initialize stat cards
         */
        init: function() {
            $('.hph-stat-card').each(function() {
                HPH.StatCard.initCard($(this));
            });
            
            // Initialize on scroll animation
            HPH.StatCard.initScrollAnimation();
        },
        
        /**
         * Initialize individual stat card
         */
        initCard: function($card) {
            var instance = {
                $card: $card,
                value: parseFloat($card.data('value')) || 0,
                targetValue: parseFloat($card.data('target')) || parseFloat($card.find('.hph-stat-card__value').text()) || 0,
                duration: parseInt($card.data('duration')) || 2000,
                hasChart: $card.find('.hph-stat-card__chart').length > 0,
                animated: false
            };
            
            // Store instance
            $card.data('statCard', instance);
            
            // Initialize chart if present
            if (instance.hasChart) {
                HPH.StatCard.initChart(instance);
            }
            
            // Initialize refresh functionality
            HPH.StatCard.initRefresh(instance);
        },
        
        /**
         * Initialize scroll-based animation
         */
        initScrollAnimation: function() {
            if ('IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var $card = $(entry.target);
                            var instance = $card.data('statCard');
                            
                            if (instance && !instance.animated) {
                                HPH.StatCard.animateValue(instance);
                                instance.animated = true;
                            }
                        }
                    });
                }, {
                    threshold: 0.5,
                    rootMargin: '50px 0px'
                });
                
                $('.hph-stat-card').each(function() {
                    observer.observe(this);
                });
            } else {
                // Fallback for browsers without IntersectionObserver
                $('.hph-stat-card').each(function() {
                    var instance = $(this).data('statCard');
                    if (instance) {
                        HPH.StatCard.animateValue(instance);
                    }
                });
            }
        },
        
        /**
         * Animate stat card value
         */
        animateValue: function(instance) {
            var $valueElement = instance.$card.find('.hph-stat-card__value');
            var startValue = instance.value;
            var endValue = instance.targetValue;
            var duration = instance.duration;
            var startTime = performance.now();
            
            // Format value based on data type
            var formatValue = function(value) {
                var format = instance.$card.data('format') || 'number';
                
                switch (format) {
                    case 'currency':
                        return '$' + HPH.formatNumber(Math.round(value));
                    case 'percentage':
                        return Math.round(value) + '%';
                    case 'decimal':
                        return parseFloat(value).toFixed(2);
                    default:
                        return HPH.formatNumber(Math.round(value));
                }
            };
            
            // Animation function
            var animate = function(currentTime) {
                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);
                
                // Easing function (ease-out-cubic)
                var easeProgress = 1 - Math.pow(1 - progress, 3);
                
                var currentValue = startValue + (endValue - startValue) * easeProgress;
                $valueElement.text(formatValue(currentValue));
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    // Animation complete - trigger event
                    instance.$card.trigger('statCard:animationComplete');
                }
            };
            
            // Start animation
            requestAnimationFrame(animate);
        },
        
        /**
         * Initialize chart for stat card
         */
        initChart: function(instance) {
            var $chartContainer = instance.$card.find('.hph-stat-card__chart');
            if (!$chartContainer.length || typeof Chart === 'undefined') {
                return;
            }
            
            var chartData = instance.$card.data('chart-data');
            var chartType = instance.$card.data('chart-type') || 'line';
            
            if (!chartData) {
                // Generate sample data if none provided
                chartData = HPH.StatCard.generateSampleData(chartType);
            }
            
            // Create canvas
            var canvasId = 'stat-chart-' + Math.random().toString(36).substr(2, 9);
            var $canvas = $(`<canvas id="${canvasId}"></canvas>`);
            $chartContainer.append($canvas);
            
            var ctx = $canvas[0].getContext('2d');
            
            // Chart configuration
            var config = {
                type: chartType,
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false
                        }
                    },
                    elements: {
                        point: {
                            radius: 0
                        },
                        line: {
                            borderWidth: 2,
                            tension: 0.4
                        }
                    },
                    animation: {
                        delay: 1000,
                        duration: 1500,
                        easing: 'easeOutCubic'
                    }
                }
            };
            
            // Create chart
            var chart = new Chart(ctx, config);
            
            // Store chart instance
            instance.chart = chart;
        },
        
        /**
         * Generate sample chart data
         */
        generateSampleData: function(type) {
            var data = [];
            var labels = [];
            
            // Generate last 7 days of data
            for (var i = 6; i >= 0; i--) {
                var date = new Date();
                date.setDate(date.getDate() - i);
                labels.push(date.toLocaleDateString('en-US', { weekday: 'short' }));
                data.push(Math.floor(Math.random() * 100) + 20);
            }
            
            var color = getComputedStyle(document.documentElement).getPropertyValue('--hph-primary').trim();
            
            return {
                labels: labels,
                datasets: [{
                    data: data,
                    borderColor: color,
                    backgroundColor: color + '20',
                    fill: type === 'area'
                }]
            };
        },
        
        /**
         * Initialize refresh functionality
         */
        initRefresh: function(instance) {
            var refreshInterval = instance.$card.data('refresh-interval');
            
            if (refreshInterval) {
                setInterval(function() {
                    HPH.StatCard.refreshCard(instance);
                }, refreshInterval * 1000);
            }
        },
        
        /**
         * Refresh stat card data
         */
        refreshCard: function(instance) {
            var cardId = instance.$card.data('card-id');
            if (!cardId) return;
            
            $.ajax({
                url: hphContext.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_refresh_stat_card',
                    nonce: hphContext.nonce,
                    card_id: cardId
                },
                success: function(response) {
                    if (response.success) {
                        // Update value
                        instance.targetValue = parseFloat(response.data.value);
                        instance.value = parseFloat(instance.$card.find('.hph-stat-card__value').text()) || 0;
                        
                        // Animate to new value
                        HPH.StatCard.animateValue(instance);
                        
                        // Update trend if provided
                        if (response.data.trend) {
                            var $trend = instance.$card.find('.hph-stat-card__trend');
                            $trend.removeClass('hph-stat-card__trend--up hph-stat-card__trend--down hph-stat-card__trend--neutral');
                            $trend.addClass('hph-stat-card__trend--' + response.data.trend.direction);
                            $trend.find('.hph-stat-card__trend-value').text(response.data.trend.value);
                            
                            // Update trend icon
                            var $icon = $trend.find('.hph-stat-card__trend-icon');
                            $icon.removeClass('fa-arrow-up fa-arrow-down fa-minus');
                            
                            switch (response.data.trend.direction) {
                                case 'up':
                                    $icon.addClass('fa-arrow-up');
                                    break;
                                case 'down':
                                    $icon.addClass('fa-arrow-down');
                                    break;
                                default:
                                    $icon.addClass('fa-minus');
                            }
                        }
                        
                        // Update chart if it exists
                        if (instance.chart && response.data.chart_data) {
                            HPH.StatCard.updateChart(instance, response.data.chart_data);
                        }
                    }
                },
                error: function() {
                    console.log('Failed to refresh stat card:', cardId);
                }
            });
        },
        
        /**
         * Update chart data
         */
        updateChart: function(instance, newData) {
            if (!instance.chart) return;
            
            // Update chart data
            instance.chart.data = newData;
            instance.chart.update('active');
        },
        
        /**
         * Get stat card value
         */
        getValue: function($card) {
            var instance = $card.data('statCard');
            return instance ? instance.targetValue : 0;
        },
        
        /**
         * Set stat card value
         */
        setValue: function($card, newValue) {
            var instance = $card.data('statCard');
            if (!instance) return;
            
            instance.value = instance.targetValue;
            instance.targetValue = newValue;
            HPH.StatCard.animateValue(instance);
        },
        
        /**
         * Reset animation
         */
        resetAnimation: function($card) {
            var instance = $card.data('statCard');
            if (!instance) return;
            
            instance.animated = false;
            instance.$card.find('.hph-stat-card__value').text(instance.value);
        }
    };
    
    // Initialize stat cards when DOM is ready
    $(document).ready(function() {
        HPH.StatCard.init();
    });
    
    // Expose methods globally
    window.hphStatCard = {
        refresh: function(cardId) {
            var $card = $(`[data-card-id="${cardId}"]`);
            if ($card.length) {
                var instance = $card.data('statCard');
                if (instance) {
                    HPH.StatCard.refreshCard(instance);
                }
            }
        },
        
        setValue: function(cardId, value) {
            var $card = $(`[data-card-id="${cardId}"]`);
            if ($card.length) {
                HPH.StatCard.setValue($card, value);
            }
        },
        
        resetAnimation: function(cardId) {
            var $card = $(`[data-card-id="${cardId}"]`);
            if ($card.length) {
                HPH.StatCard.resetAnimation($card);
            }
        }
    };
    
})(jQuery);
