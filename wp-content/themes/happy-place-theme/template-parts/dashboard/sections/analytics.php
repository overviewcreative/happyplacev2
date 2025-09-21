<?php
/**
 * Dashboard Analytics Section
 * Provides comprehensive analytics and reporting for agents
 * 
 * @package HappyPlaceTheme
 */

$user = wp_get_current_user();
$is_agent = in_array('agent', $user->roles) || in_array('administrator', $user->roles);

// Only show analytics to agents and admins
if (!$is_agent) {
    wp_redirect('?section=overview');
    exit;
}
?>

<div class="hph-dashboard-section hph-analytics-section">
    
    <!-- Analytics Header -->
    <div class="hph-section-header">
        <h2 class="hph-section-title">
            <i class="fas fa-chart-line"></i>
            Analytics & Reports
        </h2>
        <p class="hph-section-description">
            Track your performance with comprehensive analytics and reporting tools.
        </p>
        
        <!-- Date Range Selector -->
        <div class="hph-section-actions">
            <select id="analyticsDateRange" class="hph-form-select">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 3 months</option>
                <option value="365">Last year</option>
                <option value="custom">Custom range</option>
            </select>
            
            <button type="button" class="hph-btn hph-btn-outline" id="exportAnalyticsBtn">
                <i class="fas fa-download"></i>
                Export Report
            </button>
            
            <button type="button" class="hph-btn hph-btn-primary" id="refreshAnalyticsBtn">
                <i class="fas fa-sync"></i>
                Refresh Data
            </button>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="hph-analytics-kpis hph-grid hph-grid-cols-1 sm:hph-grid-cols-2 lg:hph-grid-cols-4 hph-gap-lg hph-mb-8">
        
        <div class="hph-kpi-card primary" id="kpi-revenue">
            <div class="hph-kpi-header">
                <div class="hph-kpi-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="hph-kpi-trend" id="revenue-trend">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
            <div class="hph-kpi-content">
                <div class="hph-kpi-value" id="revenue-value">
                    <div class="hph-loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
                <div class="hph-kpi-label">Total Revenue</div>
                <div class="hph-kpi-period" id="revenue-period">This period</div>
            </div>
        </div>
        
        <div class="hph-kpi-card success" id="kpi-conversion">
            <div class="hph-kpi-header">
                <div class="hph-kpi-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="hph-kpi-trend" id="conversion-trend">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
            <div class="hph-kpi-content">
                <div class="hph-kpi-value" id="conversion-value">
                    <div class="hph-loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
                <div class="hph-kpi-label">Conversion Rate</div>
                <div class="hph-kpi-period" id="conversion-period">Lead to sale</div>
            </div>
        </div>
        
        <div class="hph-kpi-card warning" id="kpi-avg-days">
            <div class="hph-kpi-header">
                <div class="hph-kpi-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="hph-kpi-trend" id="days-trend">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
            <div class="hph-kpi-content">
                <div class="hph-kpi-value" id="days-value">
                    <div class="hph-loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
                <div class="hph-kpi-label">Avg. Days on Market</div>
                <div class="hph-kpi-period" id="days-period">Listings sold</div>
            </div>
        </div>
        
        <div class="hph-kpi-card info" id="kpi-lead-score">
            <div class="hph-kpi-header">
                <div class="hph-kpi-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="hph-kpi-trend" id="lead-score-trend">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
            <div class="hph-kpi-content">
                <div class="hph-kpi-value" id="lead-score-value">
                    <div class="hph-loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
                <div class="hph-kpi-label">Avg. Lead Score</div>
                <div class="hph-kpi-period" id="lead-score-period">Active leads</div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Grid -->
    <div class="hph-grid hph-grid-cols-1 lg:hph-grid-cols-2 hph-gap-lg hph-mb-8">
        
        <!-- Revenue Chart -->
        <div class="hph-analytics-card">
            <div class="hph-card-header">
                <h3 class="hph-card-title">Revenue Trend</h3>
                <div class="hph-card-actions">
                    <button type="button" class="hph-btn hph-btn-outline hph-btn-sm" data-chart="revenue" data-view="line">
                        <i class="fas fa-chart-line"></i>
                    </button>
                    <button type="button" class="hph-btn hph-btn-outline hph-btn-sm" data-chart="revenue" data-view="bar">
                        <i class="fas fa-chart-bar"></i>
                    </button>
                </div>
            </div>
            <div class="hph-card-content">
                <div class="hph-chart-container" id="revenueChart">
                    <div class="hph-chart-loading">
                        <i class="fas fa-spinner fa-spin hph-text-2xl hph-text-gray-400"></i>
                        <p class="hph-text-gray-500 hph-mt-2">Loading revenue data...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Listings Performance Chart -->
        <div class="hph-analytics-card">
            <div class="hph-card-header">
                <h3 class="hph-card-title">Listings Performance</h3>
                <div class="hph-card-actions">
                    <select class="hph-form-select hph-w-auto" id="listingsMetric">
                        <option value="views">Views</option>
                        <option value="leads">Leads Generated</option>
                        <option value="inquiries">Inquiries</option>
                        <option value="showings">Showings</option>
                    </select>
                </div>
            </div>
            <div class="hph-card-content">
                <div class="hph-chart-container" id="listingsChart">
                    <div class="hph-chart-loading">
                        <i class="fas fa-spinner fa-spin hph-text-2xl hph-text-gray-400"></i>
                        <p class="hph-text-gray-500 hph-mt-2">Loading listings data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Tables Grid -->
    <div class="hph-grid hph-grid-cols-1 lg:hph-grid-cols-2 hph-gap-lg hph-mb-8">
        
        <!-- Top Performing Listings -->
        <div class="hph-analytics-card">
            <div class="hph-card-header">
                <h3 class="hph-card-title">Top Performing Listings</h3>
                <a href="?section=listings&sort=performance" class="hph-text-sm hph-text-primary hover:hph-underline">
                    View All
                </a>
            </div>
            <div class="hph-card-content">
                <div id="topListingsContent">
                    <div class="hph-text-center hph-py-8">
                        <div class="hph-loading-spinner">
                            <i class="fas fa-spinner fa-spin hph-text-2xl hph-text-gray-400"></i>
                            <p class="hph-text-gray-500 hph-mt-2">Loading top listings...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lead Sources -->
        <div class="hph-analytics-card">
            <div class="hph-card-header">
                <h3 class="hph-card-title">Lead Sources</h3>
                <div class="hph-card-actions">
                    <button type="button" class="hph-btn hph-btn-outline hph-btn-sm" id="leadSourcesRefresh">
                        <i class="fas fa-sync"></i>
                    </button>
                </div>
            </div>
            <div class="hph-card-content">
                <div id="leadSourcesContent">
                    <div class="hph-text-center hph-py-8">
                        <div class="hph-loading-spinner">
                            <i class="fas fa-spinner fa-spin hph-text-2xl hph-text-gray-400"></i>
                            <p class="hph-text-gray-500 hph-mt-2">Loading lead sources...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Market Insights -->
    <div class="hph-analytics-card hph-mb-8">
        <div class="hph-card-header">
            <h3 class="hph-card-title">Market Insights</h3>
            <div class="hph-card-actions">
                <button type="button" class="hph-btn hph-btn-outline" id="generateMarketReportBtn">
                    <i class="fas fa-file-pdf"></i>
                    Generate Market Report
                </button>
            </div>
        </div>
        <div class="hph-card-content">
            <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-3 hph-gap-lg">
                
                <!-- Market Trends -->
                <div class="hph-insight-card">
                    <div class="hph-insight-header">
                        <i class="fas fa-trending-up hph-text-success"></i>
                        <h4>Market Trends</h4>
                    </div>
                    <div id="marketTrendsContent">
                        <div class="hph-loading-spinner hph-text-center">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Price Analysis -->
                <div class="hph-insight-card">
                    <div class="hph-insight-header">
                        <i class="fas fa-chart-pie hph-text-info"></i>
                        <h4>Price Analysis</h4>
                    </div>
                    <div id="priceAnalysisContent">
                        <div class="hph-loading-spinner hph-text-center">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Competition -->
                <div class="hph-insight-card">
                    <div class="hph-insight-header">
                        <i class="fas fa-users hph-text-warning"></i>
                        <h4>Competition</h4>
                    </div>
                    <div id="competitionContent">
                        <div class="hph-loading-spinner hph-text-center">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Reports -->
    <div class="hph-analytics-card">
        <div class="hph-card-header">
            <h3 class="hph-card-title">Quick Reports</h3>
            <p class="hph-text-sm hph-text-gray-600">Generate instant reports for your business</p>
        </div>
        <div class="hph-card-content">
            <div class="hph-grid hph-grid-cols-1 sm:hph-grid-cols-2 md:hph-grid-cols-4 hph-gap-md">
                
                <button type="button" class="hph-report-btn" data-report="monthly-summary">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Monthly Summary</span>
                </button>
                
                <button type="button" class="hph-report-btn" data-report="listings-performance">
                    <i class="fas fa-home"></i>
                    <span>Listings Performance</span>
                </button>
                
                <button type="button" class="hph-report-btn" data-report="leads-analysis">
                    <i class="fas fa-users"></i>
                    <span>Leads Analysis</span>
                </button>
                
                <button type="button" class="hph-report-btn" data-report="market-comparison">
                    <i class="fas fa-chart-bar"></i>
                    <span>Market Comparison</span>
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Custom Date Range Modal -->
<div id="customDateRangeModal" class="hph-modal" style="display: none;">
    <div class="hph-modal-content">
        <div class="hph-modal-header">
            <h3>Custom Date Range</h3>
            <button type="button" class="hph-modal-close">&times;</button>
        </div>
        <div class="hph-modal-body">
            <div class="hph-grid hph-grid-cols-2 hph-gap-md">
                <div class="hph-form-group">
                    <label for="customDateFrom" class="hph-form-label">From Date</label>
                    <input type="date" id="customDateFrom" class="hph-form-input" required>
                </div>
                <div class="hph-form-group">
                    <label for="customDateTo" class="hph-form-label">To Date</label>
                    <input type="date" id="customDateTo" class="hph-form-input" required>
                </div>
            </div>
        </div>
        <div class="hph-modal-footer">
            <button type="button" class="hph-btn hph-btn-outline" onclick="$('#customDateRangeModal').hide();">Cancel</button>
            <button type="button" class="hph-btn hph-btn-primary" id="applyCustomDateRange">Apply Range</button>
        </div>
    </div>
</div>
