<?php
/**
 * Dashboard Analytics Section
 * Performance analytics and reporting
 *
 * @package HappyPlace
 */

namespace HappyPlace\Dashboard;

if (!defined('ABSPATH')) {
    exit;
}

class Analytics_Section {

    private Dashboard_Manager $dashboard_manager;

    public function __construct(Dashboard_Manager $dashboard_manager) {
        $this->dashboard_manager = $dashboard_manager;
    }

    public function render(): void {
        echo '<div class="hpt-analytics-section">';
        
        // Header
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Analytics Dashboard</h2>';
        echo '<p>Track your performance and market insights.</p>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<div class="hpt-date-range-selector">';
        echo '<select id="analytics-date-range" class="hpt-form__select">';
        echo '<option value="30">Last 30 Days</option>';
        echo '<option value="90">Last 90 Days</option>';
        echo '<option value="180">Last 6 Months</option>';
        echo '<option value="365" selected>Last Year</option>';
        echo '</select>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Key Performance Indicators
        echo '<div class="hpt-analytics-kpis">';
        echo '<div class="hpt-kpi-grid">';
        $this->render_kpi_cards();
        echo '</div>';
        echo '</div>';

        // Charts Row
        echo '<div class="hpt-analytics-charts">';
        echo '<div class="hpt-charts-grid">';
        
        // Sales Performance Chart
        echo '<div class="hpt-chart-container hpt-card">';
        echo '<div class="hpt-card__header">';
        echo '<h3>Sales Performance</h3>';
        echo '</div>';
        echo '<div class="hpt-card__body">';
        echo '<canvas id="salesChart" width="400" height="200"></canvas>';
        echo '</div>';
        echo '</div>';

        // Listing Views Chart
        echo '<div class="hpt-chart-container hpt-card">';
        echo '<div class="hpt-card__header">';
        echo '<h3>Listing Views</h3>';
        echo '</div>';
        echo '<div class="hpt-card__body">';
        echo '<canvas id="viewsChart" width="400" height="200"></canvas>';
        echo '</div>';
        echo '</div>';

        echo '</div>';
        echo '</div>';

        // Detailed Analytics
        echo '<div class="hpt-analytics-details">';
        echo '<div class="hpt-analytics-grid">';

        // Top Performing Listings
        echo '<div class="hpt-analytics-widget hpt-card">';
        echo '<div class="hpt-card__header">';
        echo '<h3>Top Performing Listings</h3>';
        echo '</div>';
        echo '<div class="hpt-card__body">';
        $this->render_top_listings();
        echo '</div>';
        echo '</div>';

        // Market Insights
        echo '<div class="hpt-analytics-widget hpt-card">';
        echo '<div class="hpt-card__header">';
        echo '<h3>Market Insights</h3>';
        echo '</div>';
        echo '<div class="hpt-card__body">';
        $this->render_market_insights();
        echo '</div>';
        echo '</div>';

        // Lead Sources
        echo '<div class="hpt-analytics-widget hpt-card">';
        echo '<div class="hpt-card__header">';
        echo '<h3>Lead Sources</h3>';
        echo '</div>';
        echo '<div class="hpt-card__body">';
        echo '<canvas id="leadSourcesChart" width="300" height="300"></canvas>';
        echo '</div>';
        echo '</div>';

        // Conversion Funnel
        echo '<div class="hpt-analytics-widget hpt-card">';
        echo '<div class="hpt-card__header">';
        echo '<h3>Conversion Funnel</h3>';
        echo '</div>';
        echo '<div class="hpt-card__body">';
        $this->render_conversion_funnel();
        echo '</div>';
        echo '</div>';

        echo '</div>';
        echo '</div>';

        $this->render_analytics_scripts();
        echo '</div>';
    }

    private function render_kpi_cards(): void {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        $analytics_data = $this->get_analytics_data($agent_id);
        
        $kpis = [
            [
                'title' => 'Total Sales Volume',
                'value' => '$' . number_format($analytics_data['total_volume'] ?? 0),
                'change' => '+12%',
                'trend' => 'up',
                'icon' => 'dashicons-chart-line'
            ],
            [
                'title' => 'Properties Sold',
                'value' => $analytics_data['properties_sold'] ?? 0,
                'change' => '+8%',
                'trend' => 'up',
                'icon' => 'dashicons-admin-home'
            ],
            [
                'title' => 'Average Days on Market',
                'value' => $analytics_data['avg_dom'] ?? 0,
                'change' => '-5 days',
                'trend' => 'down',
                'icon' => 'dashicons-calendar-alt'
            ],
            [
                'title' => 'Active Listings',
                'value' => $analytics_data['active_listings'] ?? 0,
                'change' => '+3',
                'trend' => 'up',
                'icon' => 'dashicons-visibility'
            ],
            [
                'title' => 'Total Views',
                'value' => number_format($analytics_data['total_views'] ?? 0),
                'change' => '+24%',
                'trend' => 'up',
                'icon' => 'dashicons-visibility'
            ],
            [
                'title' => 'Inquiries This Month',
                'value' => $analytics_data['monthly_inquiries'] ?? 0,
                'change' => '+15%',
                'trend' => 'up',
                'icon' => 'dashicons-email-alt'
            ]
        ];

        foreach ($kpis as $kpi) {
            echo '<div class="hpt-kpi-card">';
            echo '<div class="hpt-kpi-card__icon">';
            echo '<span class="dashicons ' . esc_attr($kpi['icon']) . '"></span>';
            echo '</div>';
            echo '<div class="hpt-kpi-card__content">';
            echo '<div class="hpt-kpi-card__value">' . esc_html($kpi['value']) . '</div>';
            echo '<div class="hpt-kpi-card__title">' . esc_html($kpi['title']) . '</div>';
            echo '<div class="hpt-kpi-card__change hpt-kpi-card__change--' . esc_attr($kpi['trend']) . '">';
            echo esc_html($kpi['change']) . ' from last period';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }

    private function render_top_listings(): void {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        
        // Get top performing listings by views (mock data for now)
        $top_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => 5,
            'meta_query' => [
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ]
            ],
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

        if (empty($top_listings)) {
            echo '<div class="hpt-empty-state">';
            echo '<p>No listing data available.</p>';
            echo '</div>';
            return;
        }

        echo '<div class="hpt-top-listings">';
        foreach ($top_listings as $index => $listing) {
            $address = get_field('street_address', $listing->ID);
            $city = get_field('city', $listing->ID);
            $state = get_field('state', $listing->ID);
            $price = get_field('price', $listing->ID);
            $views = rand(50, 500); // Mock views data
            
            echo '<div class="hpt-top-listing-item">';
            echo '<div class="hpt-listing-rank">' . ($index + 1) . '</div>';
            echo '<div class="hpt-listing-info">';
            echo '<div class="hpt-listing-title">' . esc_html($address ?: $listing->post_title) . '</div>';
            if ($city && $state) {
                echo '<div class="hpt-listing-location">' . esc_html($city . ', ' . $state) . '</div>';
            }
            echo '</div>';
            echo '<div class="hpt-listing-metrics">';
            if ($price) {
                echo '<div class="hpt-metric">$' . number_format($price) . '</div>';
            }
            echo '<div class="hpt-metric">' . $views . ' views</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_market_insights(): void {
        // Mock market data
        $insights = [
            [
                'title' => 'Average Price Per Sq Ft',
                'value' => '$145',
                'change' => '+3.2%',
                'trend' => 'up'
            ],
            [
                'title' => 'Market Inventory',
                'value' => '2.1 months',
                'change' => '-0.3 months',
                'trend' => 'down'
            ],
            [
                'title' => 'Your Market Share',
                'value' => '12.5%',
                'change' => '+1.8%',
                'trend' => 'up'
            ],
            [
                'title' => 'Median Days on Market',
                'value' => '18 days',
                'change' => '-2 days',
                'trend' => 'down'
            ]
        ];

        echo '<div class="hpt-market-insights">';
        foreach ($insights as $insight) {
            echo '<div class="hpt-insight-item">';
            echo '<div class="hpt-insight-title">' . esc_html($insight['title']) . '</div>';
            echo '<div class="hpt-insight-value">' . esc_html($insight['value']) . '</div>';
            echo '<div class="hpt-insight-change hpt-insight-change--' . esc_attr($insight['trend']) . '">';
            echo esc_html($insight['change']);
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_conversion_funnel(): void {
        // Mock conversion data
        $funnel_data = [
            ['stage' => 'Website Visits', 'count' => 1250, 'percentage' => 100],
            ['stage' => 'Listing Views', 'count' => 850, 'percentage' => 68],
            ['stage' => 'Contact Form', 'count' => 125, 'percentage' => 10],
            ['stage' => 'Phone Calls', 'count' => 75, 'percentage' => 6],
            ['stage' => 'Showings Scheduled', 'count' => 45, 'percentage' => 3.6],
            ['stage' => 'Offers Made', 'count' => 15, 'percentage' => 1.2],
            ['stage' => 'Closed Sales', 'count' => 8, 'percentage' => 0.64]
        ];

        echo '<div class="hpt-conversion-funnel">';
        foreach ($funnel_data as $stage) {
            $width = $stage['percentage'];
            echo '<div class="hpt-funnel-stage" style="--width: ' . $width . '%">';
            echo '<div class="hpt-funnel-bar">';
            echo '<div class="hpt-funnel-label">' . esc_html($stage['stage']) . '</div>';
            echo '<div class="hpt-funnel-count">' . number_format($stage['count']) . ' (' . $stage['percentage'] . '%)</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_analytics_scripts(): void {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        $chart_data = $this->get_chart_data($agent_id);
        
        echo '<script>';
        echo 'document.addEventListener("DOMContentLoaded", function() {';
        
        // Sales Chart
        echo 'var salesCtx = document.getElementById("salesChart").getContext("2d");';
        echo 'new Chart(salesCtx, {';
        echo 'type: "line",';
        echo 'data: {';
        echo 'labels: ' . wp_json_encode($chart_data['months']) . ',';
        echo 'datasets: [{';
        echo 'label: "Sales Volume",';
        echo 'data: ' . wp_json_encode($chart_data['sales_volume']) . ',';
        echo 'borderColor: "#51bae0",';
        echo 'backgroundColor: "rgba(81, 186, 224, 0.1)",';
        echo 'tension: 0.4';
        echo '}, {';
        echo 'label: "Properties Sold",';
        echo 'data: ' . wp_json_encode($chart_data['properties_sold']) . ',';
        echo 'borderColor: "#34d399",';
        echo 'backgroundColor: "rgba(52, 211, 153, 0.1)",';
        echo 'tension: 0.4';
        echo '}]';
        echo '},';
        echo 'options: {';
        echo 'responsive: true,';
        echo 'plugins: { legend: { position: "top" } },';
        echo 'scales: { y: { beginAtZero: true } }';
        echo '}';
        echo '});';

        // Views Chart
        echo 'var viewsCtx = document.getElementById("viewsChart").getContext("2d");';
        echo 'new Chart(viewsCtx, {';
        echo 'type: "bar",';
        echo 'data: {';
        echo 'labels: ' . wp_json_encode($chart_data['months']) . ',';
        echo 'datasets: [{';
        echo 'label: "Listing Views",';
        echo 'data: ' . wp_json_encode($chart_data['views']) . ',';
        echo 'backgroundColor: "rgba(81, 186, 224, 0.6)",';
        echo 'borderColor: "#51bae0",';
        echo 'borderWidth: 1';
        echo '}]';
        echo '},';
        echo 'options: {';
        echo 'responsive: true,';
        echo 'plugins: { legend: { display: false } },';
        echo 'scales: { y: { beginAtZero: true } }';
        echo '}';
        echo '});';

        // Lead Sources Chart
        echo 'var leadSourcesCtx = document.getElementById("leadSourcesChart").getContext("2d");';
        echo 'new Chart(leadSourcesCtx, {';
        echo 'type: "doughnut",';
        echo 'data: {';
        echo 'labels: ["Website", "Social Media", "Referrals", "Walk-ins", "Other"],';
        echo 'datasets: [{';
        echo 'data: [45, 25, 15, 10, 5],';
        echo 'backgroundColor: ["#51bae0", "#34d399", "#f59e0b", "#ef4444", "#8b5cf6"]';
        echo '}]';
        echo '},';
        echo 'options: {';
        echo 'responsive: true,';
        echo 'plugins: { legend: { position: "bottom" } }';
        echo '}';
        echo '});';

        // Date range change handler
        echo '$("#analytics-date-range").on("change", function() {';
        echo 'var range = $(this).val();';
        echo '// Reload charts with new date range';
        echo 'console.log("Date range changed to:", range);';
        echo '});';

        echo '});';
        echo '</script>';
    }

    private function get_analytics_data($agent_id): array {
        // Cache analytics data for 30 minutes
        $cache_key = "analytics_data_{$agent_id}";
        $data = wp_cache_get($cache_key);
        
        if ($data === false) {
            $data = $this->calculate_analytics_data($agent_id);
            wp_cache_set($cache_key, $data, '', 1800);
        }
        
        return $data;
    }

    private function calculate_analytics_data($agent_id): array {
        // Get sold listings for volume calculation
        $sold_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'listing_status',
                    'value' => 'sold',
                    'compare' => '='
                ]
            ]
        ]);

        $total_volume = 0;
        foreach ($sold_listings as $listing_id) {
            $price = get_field('price', $listing_id);
            if ($price) {
                $total_volume += $price;
            }
        }

        // Get active listings
        $active_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'listing_status',
                    'value' => 'active',
                    'compare' => '='
                ]
            ]
        ]);

        return [
            'total_volume' => $total_volume,
            'properties_sold' => count($sold_listings),
            'active_listings' => count($active_listings),
            'avg_dom' => rand(15, 45), // Mock data
            'total_views' => rand(5000, 15000), // Mock data
            'monthly_inquiries' => rand(25, 75) // Mock data
        ];
    }

    private function get_chart_data($agent_id): array {
        $months = [];
        $sales_volume = [];
        $properties_sold = [];
        $views = [];

        // Generate last 6 months of data
        for ($i = 5; $i >= 0; $i--) {
            $months[] = date('M Y', strtotime("-{$i} months"));
            $sales_volume[] = rand(500000, 2000000);
            $properties_sold[] = rand(2, 10);
            $views[] = rand(800, 2500);
        }

        return [
            'months' => $months,
            'sales_volume' => $sales_volume,
            'properties_sold' => $properties_sold,
            'views' => $views
        ];
    }

    public function handle_ajax_refresh_analytics($data): array {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        $date_range = (int) ($data['date_range'] ?? 365);
        
        // Clear cache and recalculate
        $cache_key = "analytics_data_{$agent_id}";
        wp_cache_delete($cache_key);
        
        $analytics_data = $this->get_analytics_data($agent_id);
        
        return [
            'success' => true,
            'data' => $analytics_data
        ];
    }
}