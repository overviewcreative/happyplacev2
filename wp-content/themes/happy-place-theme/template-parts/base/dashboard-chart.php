<?php
/**
 * Dashboard Chart Component - Chart.js wrapper for data visualization
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$chart_args = wp_parse_args($args ?? [], [
    'chart_id' => '',
    'title' => '',
    'subtitle' => '',
    'type' => 'line', // line, bar, doughnut, pie, radar, polarArea
    'data' => [],
    'labels' => [],
    'datasets' => [],
    'width' => '100%',
    'height' => '400px',
    'responsive' => true,
    'maintainAspectRatio' => false,
    'legend' => true,
    'tooltip' => true,
    'animation' => true,
    'grid' => true,
    'axes' => true,
    'loading' => false,
    'error' => false,
    'error_message' => 'Unable to load chart data',
    'colors' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316'],
    'container_class' => '',
    'options' => []
]);

// Generate unique chart ID if not provided
if (empty($chart_args['chart_id'])) {
    $chart_args['chart_id'] = 'hph-chart-' . uniqid();
}

// Prepare chart data structure
$chart_data = [];
if (!empty($chart_args['datasets'])) {
    // Use provided datasets
    $chart_data = [
        'labels' => $chart_args['labels'],
        'datasets' => $chart_args['datasets']
    ];
} elseif (!empty($chart_args['data'])) {
    // Build dataset from simple data array
    $chart_data = [
        'labels' => $chart_args['labels'],
        'datasets' => [[
            'data' => $chart_args['data'],
            'backgroundColor' => $chart_args['colors'],
            'borderColor' => $chart_args['colors'],
            'borderWidth' => 2,
            'fill' => $chart_args['type'] === 'line' ? false : true
        ]]
    ];
}

// Default chart options
$default_options = [
    'responsive' => $chart_args['responsive'],
    'maintainAspectRatio' => $chart_args['maintainAspectRatio'],
    'plugins' => [
        'legend' => [
            'display' => $chart_args['legend'],
            'position' => 'top'
        ],
        'tooltip' => [
            'enabled' => $chart_args['tooltip']
        ]
    ],
    'animation' => [
        'duration' => $chart_args['animation'] ? 1000 : 0
    ]
];

// Add scales for bar/line charts
if (in_array($chart_args['type'], ['line', 'bar'])) {
    if ($chart_args['axes']) {
        $default_options['scales'] = [
            'y' => [
                'beginAtZero' => true,
                'grid' => [
                    'display' => $chart_args['grid']
                ]
            ],
            'x' => [
                'grid' => [
                    'display' => $chart_args['grid']
                ]
            ]
        ];
    }
}

// Merge with custom options
$chart_options = array_merge_recursive($default_options, $chart_args['options']);

$container_class = 'hph-chart-container hph-relative';
if ($chart_args['container_class']) {
    $container_class .= ' ' . $chart_args['container_class'];
}
?>

<div class="<?php echo esc_attr($container_class); ?>" style="width: <?php echo esc_attr($chart_args['width']); ?>; height: <?php echo esc_attr($chart_args['height']); ?>;">
    
    <?php if ($chart_args['title'] || $chart_args['subtitle']) : ?>
        <div class="hph-chart-header hph-mb-4 hph-text-center">
            <?php if ($chart_args['title']) : ?>
                <h3 class="hph-chart-title hph-font-medium hph-text-lg hph-mb-1">
                    <?php echo esc_html($chart_args['title']); ?>
                </h3>
            <?php endif; ?>
            
            <?php if ($chart_args['subtitle']) : ?>
                <p class="hph-chart-subtitle hph-text-sm hph-text-muted hph-mb-0">
                    <?php echo esc_html($chart_args['subtitle']); ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($chart_args['loading']) : ?>
        <!-- Loading State -->
        <div class="hph-chart-loading hph-flex hph-items-center hph-justify-center hph-absolute hph-inset-0 hph-bg-white hph-bg-opacity-75">
            <div class="hph-text-center">
                <div class="hph-spinner hph-spinner-lg hph-text-primary hph-mb-2">
                    <i class="fas fa-circle-notch fa-spin"></i>
                </div>
                <p class="hph-text-muted hph-text-sm">Loading chart...</p>
            </div>
        </div>
        
    <?php elseif ($chart_args['error']) : ?>
        <!-- Error State -->
        <div class="hph-chart-error hph-flex hph-items-center hph-justify-center hph-absolute hph-inset-0 hph-bg-light">
            <div class="hph-text-center hph-p-6">
                <div class="hph-error-icon hph-text-4xl hph-text-danger hph-mb-2">
                    <i class="fas fa-chart-line-down"></i>
                </div>
                <p class="hph-text-muted"><?php echo esc_html($chart_args['error_message']); ?></p>
            </div>
        </div>
        
    <?php elseif (empty($chart_data['labels']) && empty($chart_data['datasets'])) : ?>
        <!-- No Data State -->
        <div class="hph-chart-empty hph-flex hph-items-center hph-justify-center hph-absolute hph-inset-0 hph-bg-light">
            <div class="hph-text-center hph-p-6">
                <div class="hph-empty-icon hph-text-4xl hph-text-muted hph-mb-2">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <p class="hph-text-muted">No data available for chart</p>
            </div>
        </div>
        
    <?php else : ?>
        <!-- Chart Canvas -->
        <canvas id="<?php echo esc_attr($chart_args['chart_id']); ?>" 
                class="hph-chart-canvas hph-w-full hph-h-full"
                data-chart-type="<?php echo esc_attr($chart_args['type']); ?>"
                data-chart-data="<?php echo esc_attr(json_encode($chart_data)); ?>"
                data-chart-options="<?php echo esc_attr(json_encode($chart_options)); ?>">
        </canvas>
    <?php endif; ?>
    
</div>

<?php if (!$chart_args['loading'] && !$chart_args['error'] && !empty($chart_data)) : ?>
<script>
jQuery(document).ready(function($) {
    // Ensure Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded');
        return;
    }
    
    var chartId = '<?php echo esc_js($chart_args['chart_id']); ?>';
    var canvas = document.getElementById(chartId);
    
    if (!canvas) {
        console.error('Chart canvas not found: ' + chartId);
        return;
    }
    
    var ctx = canvas.getContext('2d');
    var chartData = <?php echo json_encode($chart_data); ?>;
    var chartOptions = <?php echo json_encode($chart_options); ?>;
    var chartType = '<?php echo esc_js($chart_args['type']); ?>';
    
    // Create the chart
    var chart = new Chart(ctx, {
        type: chartType,
        data: chartData,
        options: chartOptions
    });
    
    // Store chart instance for later access
    window.hphCharts = window.hphCharts || {};
    window.hphCharts[chartId] = chart;
    
    // Expose update method
    window['update_' + chartId.replace(/-/g, '_')] = function(newData, newOptions) {
        if (newData) {
            chart.data = newData;
        }
        if (newOptions) {
            chart.options = $.extend(true, chart.options, newOptions);
        }
        chart.update();
    };
    
    // Auto-resize handler
    var resizeTimeout;
    $(window).on('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        }, 100);
    });
});
</script>
<?php endif; ?>

<?php
/**
 * Usage Examples:
 * 
 * Simple line chart:
 * get_template_part('template-parts/components/dashboard-chart', '', [
 *     'title' => 'Sales Over Time',
 *     'type' => 'line',
 *     'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
 *     'data' => [10, 25, 15, 40, 30]
 * ]);
 * 
 * Multi-dataset bar chart:
 * get_template_part('template-parts/components/dashboard-chart', '', [
 *     'title' => 'Listings vs Sales',
 *     'type' => 'bar',
 *     'labels' => ['Q1', 'Q2', 'Q3', 'Q4'],
 *     'datasets' => [
 *         [
 *             'label' => 'Listings',
 *             'data' => [12, 19, 3, 5],
 *             'backgroundColor' => '#3b82f6'
 *         ],
 *         [
 *             'label' => 'Sales',
 *             'data' => [2, 3, 20, 8],
 *             'backgroundColor' => '#10b981'
 *         ]
 *     ]
 * ]);
 * 
 * Pie chart with custom options:
 * get_template_part('template-parts/components/dashboard-chart', '', [
 *     'title' => 'Property Types',
 *     'type' => 'doughnut',
 *     'labels' => ['Houses', 'Condos', 'Townhomes'],
 *     'data' => [45, 30, 25],
 *     'options' => [
 *         'plugins' => [
 *             'legend' => [
 *                 'position' => 'bottom'
 *             ]
 *         ]
 *     ]
 * ]);
 */
?>