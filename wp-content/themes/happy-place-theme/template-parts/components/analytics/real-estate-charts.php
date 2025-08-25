<?php
/**
 * Real Estate Chart Examples - Pre-configured charts for real estate data
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$charts_args = wp_parse_args($args ?? [], [
    'chart_type' => 'sales_trend', // sales_trend, price_range, property_types, market_stats, agent_performance
    'agent_id' => null,
    'timeframe' => 'ytd', // mtd, qtd, ytd, custom
    'data_source' => 'service', // service, mock, custom
    'data' => null,
    'title_override' => '',
    'height' => '350px',
    'container_class' => ''
]);

// Get data based on source and chart type
$chart_data = [];
$chart_config = [];

switch ($charts_args['chart_type']) {
    case 'sales_trend':
        $chart_config = get_sales_trend_config($charts_args);
        break;
    case 'price_range':
        $chart_config = get_price_range_config($charts_args);
        break;
    case 'property_types':
        $chart_config = get_property_types_config($charts_args);
        break;
    case 'market_stats':
        $chart_config = get_market_stats_config($charts_args);
        break;
    case 'agent_performance':
        $chart_config = get_agent_performance_config($charts_args);
        break;
    default:
        $chart_config = get_default_chart_config($charts_args);
}

// Override title if provided
if ($charts_args['title_override']) {
    $chart_config['title'] = $charts_args['title_override'];
}

// Override height and container class
$chart_config['height'] = $charts_args['height'];
$chart_config['container_class'] = $charts_args['container_class'];

// Include the chart component
get_template_part('template-parts/components/dashboard-chart', '', $chart_config);

/**
 * Sales Trend Configuration
 */
function get_sales_trend_config($args) {
    if ($args['data_source'] === 'service' && class_exists('HappyPlace\\Services\\TransactionService')) {
        $service = new \HappyPlace\Services\TransactionService();
        $service->init();
        $stats = $service->get_transaction_stats($args['agent_id'], $args['timeframe']);
        
        // Convert service data to chart format
        $labels = [];
        $data = [];
        
        // Generate monthly data for the year
        for ($i = 11; $i >= 0; $i--) {
            $month = date('M', strtotime("-{$i} months"));
            $labels[] = $month;
            // In real implementation, you'd get actual monthly data
            $data[] = rand(2, 15); // Mock data for now
        }
    } else {
        // Mock data
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $data = [5, 8, 12, 7, 15, 18, 22, 19, 25, 21, 16, 13];
    }
    
    return [
        'title' => 'Sales Trend - ' . strtoupper($args['timeframe']),
        'subtitle' => 'Number of closed transactions by month',
        'type' => 'line',
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Closed Sales',
            'data' => $data,
            'borderColor' => '#10b981',
            'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
            'borderWidth' => 3,
            'fill' => true,
            'tension' => 0.4
        ]],
        'options' => [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Sales'
                    ]
                ]
            ]
        ]
    ];
}

/**
 * Price Range Configuration
 */
function get_price_range_config($args) {
    // Price ranges typical for real estate
    $labels = ['Under $300K', '$300K-$500K', '$500K-$750K', '$750K-$1M', 'Over $1M'];
    $data = [15, 35, 28, 12, 8]; // Percentage distribution
    $colors = ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6'];
    
    return [
        'title' => 'Listings by Price Range',
        'subtitle' => 'Distribution of active listings',
        'type' => 'doughnut',
        'labels' => $labels,
        'datasets' => [[
            'data' => $data,
            'backgroundColor' => $colors,
            'borderColor' => '#ffffff',
            'borderWidth' => 2
        ]],
        'options' => [
            'plugins' => [
                'legend' => [
                    'position' => 'right'
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.label + ": " + context.parsed + "%"; }'
                    ]
                ]
            ]
        ]
    ];
}

/**
 * Property Types Configuration
 */
function get_property_types_config($args) {
    $labels = ['Single Family', 'Condos', 'Townhomes', 'Multi-Family', 'Land'];
    $data = [45, 25, 15, 10, 5];
    $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
    
    return [
        'title' => 'Property Types',
        'subtitle' => 'Breakdown by property type',
        'type' => 'pie',
        'labels' => $labels,
        'datasets' => [[
            'data' => $data,
            'backgroundColor' => $colors,
            'borderColor' => '#ffffff',
            'borderWidth' => 2
        ]],
        'options' => [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom'
                ]
            ]
        ]
    ];
}

/**
 * Market Stats Configuration
 */
function get_market_stats_config($args) {
    $labels = ['Q1', 'Q2', 'Q3', 'Q4'];
    
    return [
        'title' => 'Market Performance',
        'subtitle' => 'Quarterly comparison - listings vs sales',
        'type' => 'bar',
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'New Listings',
                'data' => [125, 145, 135, 155],
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#3b82f6',
                'borderWidth' => 1
            ],
            [
                'label' => 'Closed Sales',
                'data' => [98, 112, 108, 128],
                'backgroundColor' => '#10b981',
                'borderColor' => '#10b981',
                'borderWidth' => 1
            ]
        ],
        'options' => [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'top'
                ]
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Properties'
                    ]
                ]
            ]
        ]
    ];
}

/**
 * Agent Performance Configuration
 */
function get_agent_performance_config($args) {
    if ($args['data_source'] === 'service' && $args['agent_id']) {
        // Get real agent data from service
        if (class_exists('HappyPlace\\Services\\TransactionService')) {
            $service = new \HappyPlace\Services\TransactionService();
            $service->init();
            $stats = $service->get_transaction_stats($args['agent_id'], $args['timeframe']);
            
            $labels = ['Listings', 'Pending', 'Closed', 'Volume ($M)'];
            $data = [
                $stats['active_transactions'] ?? 0,
                ($stats['active_transactions'] ?? 0) - ($stats['closed_transactions'] ?? 0),
                $stats['closed_transactions'] ?? 0,
                round(($stats['total_volume'] ?? 0) / 1000000, 1)
            ];
        } else {
            // Fallback mock data
            $labels = ['Listings', 'Pending', 'Closed', 'Volume ($M)'];
            $data = [12, 8, 15, 4.5];
        }
    } else {
        // Mock data for demonstration
        $labels = ['Listings', 'Pending', 'Closed', 'Volume ($M)'];
        $data = [18, 12, 22, 6.8];
    }
    
    return [
        'title' => 'Agent Performance',
        'subtitle' => 'Current period metrics',
        'type' => 'radar',
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Performance',
            'data' => $data,
            'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
            'borderColor' => '#3b82f6',
            'pointBackgroundColor' => '#3b82f6',
            'pointBorderColor' => '#ffffff',
            'pointHoverBackgroundColor' => '#ffffff',
            'pointHoverBorderColor' => '#3b82f6',
            'borderWidth' => 2
        ]],
        'options' => [
            'scales' => [
                'r' => [
                    'angleLines' => [
                        'display' => true
                    ],
                    'suggestedMin' => 0,
                    'ticks' => [
                        'display' => true
                    ]
                ]
            ],
            'plugins' => [
                'legend' => [
                    'display' => false
                ]
            ]
        ]
    ];
}

/**
 * Default Chart Configuration
 */
function get_default_chart_config($args) {
    return [
        'title' => 'Sample Chart',
        'subtitle' => 'No data available',
        'type' => 'bar',
        'labels' => ['No Data'],
        'data' => [0],
        'error' => true,
        'error_message' => 'Chart type not recognized'
    ];
}
?>

<?php
/**
 * Usage Examples:
 * 
 * Sales trend chart:
 * get_template_part('template-parts/components/real-estate-charts', '', [
 *     'chart_type' => 'sales_trend',
 *     'timeframe' => 'ytd',
 *     'agent_id' => 123
 * ]);
 * 
 * Price range distribution:
 * get_template_part('template-parts/components/real-estate-charts', '', [
 *     'chart_type' => 'price_range',
 *     'title_override' => 'Active Listings by Price'
 * ]);
 * 
 * Property types pie chart:
 * get_template_part('template-parts/components/real-estate-charts', '', [
 *     'chart_type' => 'property_types',
 *     'height' => '300px'
 * ]);
 * 
 * Market performance comparison:
 * get_template_part('template-parts/components/real-estate-charts', '', [
 *     'chart_type' => 'market_stats',
 *     'timeframe' => 'ytd'
 * ]);
 * 
 * Agent performance radar:
 * get_template_part('template-parts/components/real-estate-charts', '', [
 *     'chart_type' => 'agent_performance',
 *     'agent_id' => get_current_user_id(),
 *     'data_source' => 'service'
 * ]);
 */
?>