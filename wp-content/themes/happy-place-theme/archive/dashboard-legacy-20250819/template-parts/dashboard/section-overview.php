<?php
/**
 * Dashboard Overview Section
 * 
 * Main dashboard overview with key metrics and quick actions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and dashboard instance
$current_user = wp_get_current_user();
$dashboard = \HappyPlace\Dashboard\Frontend_Admin_Dashboard::get_instance();

// Get user role for customized display
$user_roles = $current_user->roles;
$primary_role = $user_roles[0] ?? 'subscriber';

// Get stats based on user permissions
$stats = $this->get_user_stats($current_user->ID);
?>

<div class="dashboard-overview">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="dashboard-card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <?php printf(__('Welcome back, %s!', 'happy-place'), esc_html($current_user->display_name)); ?>
                            </h2>
                            <p class="text-muted mb-0">
                                <?php
                                $role_messages = [
                                    'administrator' => __('You have full administrative access to the system.', 'happy-place'),
                                    'broker' => __('Manage your brokerage operations and team performance.', 'happy-place'),
                                    'agent' => __('Track your listings, leads, and performance metrics.', 'happy-place'),
                                    'team_leader' => __('Monitor your team\'s performance and manage assignments.', 'happy-place'),
                                    'assistant' => __('Access your assigned tasks and listings.', 'happy-place')
                                ];
                                echo esc_html($role_messages[$primary_role] ?? __('Access your dashboard tools and information.', 'happy-place'));
                                ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="welcome-actions">
                                <?php if ($dashboard->user_can('manage_own_listings') || $dashboard->user_can('manage_all_listings')): ?>
                                    <a href="<?php echo esc_url(add_query_arg('dashboard_section', 'listings', get_permalink())); ?>" class="btn btn-primary">
                                        <span class="hph-icon-plus"></span>
                                        <?php _e('Add Listing', 'happy-place'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="row mb-4">
        <?php if ($dashboard->user_can('manage_all_listings') || $dashboard->user_can('manage_own_listings')): ?>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card">
                    <div class="stat-card">
                        <span class="stat-value"><?php echo esc_html($stats['active_listings'] ?? '0'); ?></span>
                        <span class="stat-label"><?php _e('Active Listings', 'happy-place'); ?></span>
                        <?php if (isset($stats['listings_change'])): ?>
                            <div class="stat-change <?php echo $stats['listings_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $stats['listings_change'] >= 0 ? '+' : ''; ?><?php echo esc_html($stats['listings_change']); ?>%
                                <?php _e('this month', 'happy-place'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($dashboard->user_can('view_analytics') || $dashboard->user_can('view_own_analytics')): ?>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card">
                    <div class="stat-card">
                        <span class="stat-value">$<?php echo esc_html(number_format($stats['total_volume'] ?? 0)); ?></span>
                        <span class="stat-label"><?php _e('Sales Volume', 'happy-place'); ?></span>
                        <?php if (isset($stats['volume_change'])): ?>
                            <div class="stat-change <?php echo $stats['volume_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $stats['volume_change'] >= 0 ? '+' : ''; ?><?php echo esc_html($stats['volume_change']); ?>%
                                <?php _e('this month', 'happy-place'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card">
                    <div class="stat-card">
                        <span class="stat-value"><?php echo esc_html($stats['total_transactions'] ?? '0'); ?></span>
                        <span class="stat-label"><?php _e('Transactions', 'happy-place'); ?></span>
                        <?php if (isset($stats['transactions_change'])): ?>
                            <div class="stat-change <?php echo $stats['transactions_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $stats['transactions_change'] >= 0 ? '+' : ''; ?><?php echo esc_html($stats['transactions_change']); ?>%
                                <?php _e('this month', 'happy-place'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card">
                    <div class="stat-card">
                        <span class="stat-value"><?php echo esc_html($stats['pending_leads'] ?? '0'); ?></span>
                        <span class="stat-label"><?php _e('Pending Leads', 'happy-place'); ?></span>
                        <?php if (isset($stats['leads_change'])): ?>
                            <div class="stat-change <?php echo $stats['leads_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $stats['leads_change'] >= 0 ? '+' : ''; ?><?php echo esc_html($stats['leads_change']); ?>%
                                <?php _e('this week', 'happy-place'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dashboard Content Sections -->
    <div class="row">
        <!-- Recent Activity -->
        <div class="col-lg-8 mb-4">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="dashboard-card-title"><?php _e('Recent Activity', 'happy-place'); ?></h3>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-outline-primary" id="refresh-activity">
                                <span class="hph-icon-refresh"></span>
                                <?php _e('Refresh', 'happy-place'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="dashboard-card-body">
                    <div id="recent-activity-list">
                        <!-- Activity items will be loaded via AJAX -->
                        <div class="text-center py-4">
                            <div class="spinner"></div>
                            <p class="loading-text"><?php _e('Loading recent activity...', 'happy-place'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title"><?php _e('Quick Actions', 'happy-place'); ?></h3>
                </div>
                <div class="dashboard-card-body">
                    <div class="quick-actions-list">
                        <?php if ($dashboard->user_can('manage_own_listings') || $dashboard->user_can('manage_all_listings')): ?>
                            <a href="<?php echo esc_url(add_query_arg('dashboard_section', 'listings', get_permalink())); ?>" class="quick-action-item">
                                <span class="hph-icon-home"></span>
                                <div class="action-content">
                                    <h4><?php _e('Manage Listings', 'happy-place'); ?></h4>
                                    <p><?php _e('Add, edit, or update property listings', 'happy-place'); ?></p>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if ($dashboard->user_can('edit_profile')): ?>
                            <a href="<?php echo esc_url(add_query_arg('dashboard_section', 'profile', get_permalink())); ?>" class="quick-action-item">
                                <span class="hph-icon-user"></span>
                                <div class="action-content">
                                    <h4><?php _e('Update Profile', 'happy-place'); ?></h4>
                                    <p><?php _e('Edit your agent profile and contact information', 'happy-place'); ?></p>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if ($dashboard->user_can('generate_marketing')): ?>
                            <a href="<?php echo esc_url(add_query_arg('dashboard_section', 'marketing', get_permalink())); ?>" class="quick-action-item">
                                <span class="hph-icon-megaphone"></span>
                                <div class="action-content">
                                    <h4><?php _e('Marketing Tools', 'happy-place'); ?></h4>
                                    <p><?php _e('Generate flyers and marketing materials', 'happy-place'); ?></p>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if ($dashboard->user_can('sync_data')): ?>
                            <a href="<?php echo esc_url(add_query_arg('dashboard_section', 'sync', get_permalink())); ?>" class="quick-action-item">
                                <span class="hph-icon-sync"></span>
                                <div class="action-content">
                                    <h4><?php _e('Sync Data', 'happy-place'); ?></h4>
                                    <p><?php _e('Synchronize with Airtable and external systems', 'happy-place'); ?></p>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if ($dashboard->user_can('view_analytics')): ?>
                            <a href="<?php echo esc_url(add_query_arg('dashboard_section', 'analytics', get_permalink())); ?>" class="quick-action-item">
                                <span class="hph-icon-chart"></span>
                                <div class="action-content">
                                    <h4><?php _e('View Analytics', 'happy-place'); ?></h4>
                                    <p><?php _e('Review performance metrics and reports', 'happy-place'); ?></p>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status (for admins) -->
    <?php if ($dashboard->user_can('manage_settings')): ?>
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title"><?php _e('System Status', 'happy-place'); ?></h3>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="status-item">
                                    <span class="status-indicator status-success"></span>
                                    <span class="status-label"><?php _e('WordPress', 'happy-place'); ?></span>
                                    <span class="status-value"><?php echo get_bloginfo('version'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="status-item">
                                    <span class="status-indicator status-success"></span>
                                    <span class="status-label"><?php _e('Plugin', 'happy-place'); ?></span>
                                    <span class="status-value"><?php echo HP_VERSION; ?></span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="status-item">
                                    <span class="status-indicator status-warning"></span>
                                    <span class="status-label"><?php _e('Last Sync', 'happy-place'); ?></span>
                                    <span class="status-value"><?php _e('2 hours ago', 'happy-place'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="status-item">
                                    <span class="status-indicator status-success"></span>
                                    <span class="status-label"><?php _e('Database', 'happy-place'); ?></span>
                                    <span class="status-value"><?php _e('Healthy', 'happy-place'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Quick Actions Styling */
.quick-actions-list {
    display: flex;
    flex-direction: column;
    gap: var(--hph-space-md);
}

.quick-action-item {
    display: flex;
    align-items: center;
    padding: var(--hph-space-md);
    border: 1px solid var(--hph-border-color);
    border-radius: var(--hph-border-radius);
    text-decoration: none;
    color: var(--hph-text-color);
    transition: var(--hph-transition);
}

.quick-action-item:hover {
    background: var(--hph-gray-50);
    border-color: var(--hph-primary);
    text-decoration: none;
    color: var(--hph-text-color);
}

.quick-action-item span[class*="hph-icon-"] {
    width: 40px;
    height: 40px;
    background: var(--hph-primary-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: var(--hph-space-md);
    color: var(--hph-primary);
}

.action-content h4 {
    margin: 0 0 var(--hph-space-xs) 0;
    font-size: var(--hph-text-sm);
    font-weight: var(--hph-font-semibold);
}

.action-content p {
    margin: 0;
    font-size: var(--hph-text-xs);
    color: var(--hph-text-muted);
}

/* Status Items */
.status-item {
    display: flex;
    align-items: center;
    gap: var(--hph-space-xs);
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.status-success {
    background: var(--hph-success);
}

.status-warning {
    background: var(--hph-warning);
}

.status-error {
    background: var(--hph-danger);
}

.status-label {
    font-weight: var(--hph-font-semibold);
    color: var(--hph-text-color);
}

.status-value {
    color: var(--hph-text-muted);
    font-size: var(--hph-text-sm);
}

/* Welcome Actions */
.welcome-actions {
    display: flex;
    gap: var(--hph-space-sm);
    justify-content: flex-end;
}

@media (max-width: 767px) {
    .welcome-actions {
        justify-content: flex-start;
        margin-top: var(--hph-space-sm);
    }
}
</style>

<?php
// Add method to get user stats (this would typically be in a separate class)
function get_user_stats($user_id) {
    // This is a placeholder - in a real implementation, this would query the database
    return [
        'active_listings' => wp_count_posts('listing')->publish ?? 0,
        'total_volume' => 2500000,
        'total_transactions' => 45,
        'pending_leads' => 12,
        'listings_change' => 15,
        'volume_change' => 8,
        'transactions_change' => 22,
        'leads_change' => -5
    ];
}
?>