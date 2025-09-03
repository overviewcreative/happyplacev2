<?php
/**
 * Dashboard Analytics Overview Section
 * 
 * @package HappyPlaceTheme
 */

$user = wp_get_current_user();
$is_agent = in_array('agent', $user->roles) || in_array('administrator', $user->roles);

if (!$is_agent) {
    echo '<div class="hph-error"><p>Access denied. This section is for agents only.</p></div>';
    return;
}
?>

<div class="hph-dashboard-section hph-analytics-section">
    
    <!-- Analytics Header -->
    <div class="hph-section-header">
        <h2 class="hph-section-title">
            <i class="fas fa-chart-line"></i>
            Analytics & Performance
        </h2>
        <p class="hph-section-description">
            Track your listing performance, lead conversion, and business insights.
        </p>
    </div>

    <!-- Analytics Cards -->
    <div class="hph-analytics-grid">
        
        <!-- Performance Overview -->
        <div class="hph-analytics-card hph-card-full">
            <div class="hph-card-header">
                <h3 class="hph-card-title">Performance Overview</h3>
                <div class="hph-card-actions">
                    <select class="hph-select hph-select-sm">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 90 days</option>
                    </select>
                </div>
            </div>
            <div class="hph-card-content">
                <div class="hph-stats-row">
                    <div class="hph-stat-item">
                        <div class="hph-stat-icon hph-bg-primary">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="hph-stat-content">
                            <div class="hph-stat-number">1,247</div>
                            <div class="hph-stat-label">Page Views</div>
                            <div class="hph-stat-change hph-stat-up">+12%</div>
                        </div>
                    </div>
                    
                    <div class="hph-stat-item">
                        <div class="hph-stat-icon hph-bg-success">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="hph-stat-content">
                            <div class="hph-stat-number">34</div>
                            <div class="hph-stat-label">Phone Inquiries</div>
                            <div class="hph-stat-change hph-stat-up">+8%</div>
                        </div>
                    </div>
                    
                    <div class="hph-stat-item">
                        <div class="hph-stat-icon hph-bg-warning">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="hph-stat-content">
                            <div class="hph-stat-number">67</div>
                            <div class="hph-stat-label">Email Leads</div>
                            <div class="hph-stat-change hph-stat-down">-3%</div>
                        </div>
                    </div>
                    
                    <div class="hph-stat-item">
                        <div class="hph-stat-icon hph-bg-info">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="hph-stat-content">
                            <div class="hph-stat-number">18</div>
                            <div class="hph-stat-label">Showings Booked</div>
                            <div class="hph-stat-change hph-stat-up">+25%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performing Listings -->
        <div class="hph-analytics-card">
            <div class="hph-card-header">
                <h3 class="hph-card-title">Top Performing Listings</h3>
            </div>
            <div class="hph-card-content">
                <div class="hph-performance-list">
                    <div class="hph-performance-item">
                        <div class="hph-performance-info">
                            <h4 class="hph-performance-title">123 Main Street</h4>
                            <p class="hph-performance-address">Downtown, City</p>
                        </div>
                        <div class="hph-performance-stats">
                            <span class="hph-performance-views">245 views</span>
                            <span class="hph-performance-leads">12 leads</span>
                        </div>
                    </div>
                    
                    <div class="hph-performance-item">
                        <div class="hph-performance-info">
                            <h4 class="hph-performance-title">456 Oak Avenue</h4>
                            <p class="hph-performance-address">Suburb, City</p>
                        </div>
                        <div class="hph-performance-stats">
                            <span class="hph-performance-views">189 views</span>
                            <span class="hph-performance-leads">8 leads</span>
                        </div>
                    </div>
                    
                    <div class="hph-performance-item">
                        <div class="hph-performance-info">
                            <h4 class="hph-performance-title">789 Pine Street</h4>
                            <p class="hph-performance-address">Northside, City</p>
                        </div>
                        <div class="hph-performance-stats">
                            <span class="hph-performance-views">156 views</span>
                            <span class="hph-performance-leads">6 leads</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lead Sources -->
        <div class="hph-analytics-card">
            <div class="hph-card-header">
                <h3 class="hph-card-title">Lead Sources</h3>
            </div>
            <div class="hph-card-content">
                <div class="hph-lead-sources">
                    <div class="hph-source-item">
                        <div class="hph-source-info">
                            <span class="hph-source-name">Website Forms</span>
                            <span class="hph-source-percentage">45%</span>
                        </div>
                        <div class="hph-source-bar">
                            <div class="hph-source-fill" style="width: 45%"></div>
                        </div>
                    </div>
                    
                    <div class="hph-source-item">
                        <div class="hph-source-info">
                            <span class="hph-source-name">Phone Calls</span>
                            <span class="hph-source-percentage">30%</span>
                        </div>
                        <div class="hph-source-bar">
                            <div class="hph-source-fill" style="width: 30%"></div>
                        </div>
                    </div>
                    
                    <div class="hph-source-item">
                        <div class="hph-source-info">
                            <span class="hph-source-name">Social Media</span>
                            <span class="hph-source-percentage">15%</span>
                        </div>
                        <div class="hph-source-bar">
                            <div class="hph-source-fill" style="width: 15%"></div>
                        </div>
                    </div>
                    
                    <div class="hph-source-item">
                        <div class="hph-source-info">
                            <span class="hph-source-name">Referrals</span>
                            <span class="hph-source-percentage">10%</span>
                        </div>
                        <div class="hph-source-bar">
                            <div class="hph-source-fill" style="width: 10%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coming Soon Message -->
    <div class="hph-coming-soon">
        <div class="hph-coming-soon-content">
            <i class="fas fa-chart-pie hph-coming-soon-icon"></i>
            <h3>Advanced Analytics Coming Soon</h3>
            <p>We're working on detailed charts, conversion tracking, and market insights to help you grow your business.</p>
        </div>
    </div>

</div>
