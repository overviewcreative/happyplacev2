<?php
/**
 * Transaction Dashboard Component - Agent Deal Pipeline
 *
 * @package HappyPlaceTheme
 */

// Check if user is logged in and is an agent
if (!is_user_logged_in()) {
    return;
}

$current_user = wp_get_current_user();
$is_agent = in_array('agent', $current_user->roles) || current_user_can('manage_options');

if (!$is_agent) {
    return;
}

// Default attributes
$dashboard_args = wp_parse_args($args ?? [], [
    'title' => 'My Deal Pipeline',
    'show_stats' => true,
    'show_pipeline' => true,
    'limit' => 10,
    'agent_id' => $current_user->ID
]);

// Get Transaction Service
$transaction_service = null;
$pipeline_data = [];
$stats_data = [];

if (class_exists('HappyPlace\\Services\\TransactionService')) {
    $transaction_service = new \HappyPlace\Services\TransactionService();
    $transaction_service->init();
    
    $pipeline_data = $transaction_service->get_transaction_pipeline($dashboard_args['agent_id']);
    $stats_data = $transaction_service->get_transaction_stats($dashboard_args['agent_id'], 'ytd');
}
?>

<div class="hph-transaction-dashboard hph-widget">
    <?php if ($dashboard_args['title']) : ?>
        <div class="hph-widget-header">
            <h3 class="hph-widget-title"><?php echo esc_html($dashboard_args['title']); ?></h3>
        </div>
    <?php endif; ?>
    
    <div class="hph-widget-content">
        
        <?php if ($dashboard_args['show_stats'] && !empty($stats_data)) : ?>
            <!-- Transaction Stats Summary -->
            <div class="hph-transaction-stats hph-grid hph-grid-cols-2 md:hph-grid-cols-4 hph-gap-4 hph-mb-6">
                <div class="hph-stat-card hph-p-4 hph-bg-primary hph-bg-opacity-10 hph-rounded">
                    <div class="hph-stat-value hph-text-2xl hph-font-bold hph-text-primary">
                        <?php echo esc_html($stats_data['active_transactions'] ?? 0); ?>
                    </div>
                    <div class="hph-stat-label hph-text-sm hph-text-muted">Active Deals</div>
                </div>
                
                <div class="hph-stat-card hph-p-4 hph-bg-success hph-bg-opacity-10 hph-rounded">
                    <div class="hph-stat-value hph-text-2xl hph-font-bold hph-text-success">
                        <?php echo esc_html($stats_data['closed_transactions'] ?? 0); ?>
                    </div>
                    <div class="hph-stat-label hph-text-sm hph-text-muted">Closed YTD</div>
                </div>
                
                <div class="hph-stat-card hph-p-4 hph-bg-info hph-bg-opacity-10 hph-rounded">
                    <div class="hph-stat-value hph-text-xl hph-font-bold hph-text-info">
                        $<?php echo number_format(($stats_data['total_volume'] ?? 0) / 1000000, 1); ?>M
                    </div>
                    <div class="hph-stat-label hph-text-sm hph-text-muted">Total Volume</div>
                </div>
                
                <div class="hph-stat-card hph-p-4 hph-bg-warning hph-bg-opacity-10 hph-rounded">
                    <div class="hph-stat-value hph-text-xl hph-font-bold hph-text-warning">
                        $<?php echo number_format($stats_data['total_commission'] ?? 0); ?>
                    </div>
                    <div class="hph-stat-label hph-text-sm hph-text-muted">Commission YTD</div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($dashboard_args['show_pipeline'] && !empty($pipeline_data)) : ?>
            <!-- Transaction Pipeline -->
            <div class="hph-transaction-pipeline">
                <div class="hph-pipeline-stages">
                    <?php 
                    $pipeline_statuses = [
                        'draft' => ['label' => 'Draft', 'color' => 'secondary'],
                        'offer_submitted' => ['label' => 'Offer Submitted', 'color' => 'info'],
                        'under_contract' => ['label' => 'Under Contract', 'color' => 'primary'],
                        'inspection' => ['label' => 'Inspection', 'color' => 'warning'],
                        'financing' => ['label' => 'Financing', 'color' => 'warning'],
                        'closing' => ['label' => 'Closing', 'color' => 'success']
                    ];
                    
                    foreach ($pipeline_statuses as $status => $config) :
                        $stage_data = $pipeline_data[$status] ?? ['transactions' => [], 'count' => 0, 'total_value' => 0];
                        if ($stage_data['count'] == 0) continue;
                    ?>
                        <div class="hph-pipeline-stage hph-mb-4">
                            <div class="hph-stage-header hph-flex hph-items-center hph-justify-between hph-p-3 hph-bg-<?php echo $config['color']; ?> hph-bg-opacity-10 hph-rounded-t">
                                <h4 class="hph-stage-title hph-font-medium hph-text-<?php echo $config['color']; ?>">
                                    <?php echo esc_html($config['label']); ?>
                                </h4>
                                <div class="hph-stage-summary hph-text-sm">
                                    <span class="hph-stage-count hph-font-bold"><?php echo esc_html($stage_data['count']); ?></span>
                                    deals
                                    <?php if ($stage_data['total_value'] > 0) : ?>
                                        · $<?php echo number_format($stage_data['total_value']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="hph-stage-transactions">
                                <?php foreach (array_slice($stage_data['transactions'], 0, 3) as $transaction) : ?>
                                    <div class="hph-transaction-item hph-flex hph-items-center hph-justify-between hph-p-3 hph-border-b hph-border-gray-100">
                                        <div class="hph-transaction-info hph-flex-1">
                                            <div class="hph-transaction-title hph-font-medium hph-text-sm">
                                                <?php echo esc_html($transaction['listing_title'] ?: $transaction['title']); ?>
                                            </div>
                                            
                                            <?php if ($transaction['buyer_name']) : ?>
                                                <div class="hph-buyer-name hph-text-xs hph-text-muted">
                                                    Buyer: <?php echo esc_html($transaction['buyer_name']); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($transaction['closing_date']) : ?>
                                                <div class="hph-closing-date hph-text-xs hph-text-muted">
                                                    <i class="fas fa-calendar hph-mr-1"></i>
                                                    <?php echo date('M j, Y', strtotime($transaction['closing_date'])); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="hph-transaction-value hph-text-right">
                                            <?php if ($transaction['sale_price'] > 0) : ?>
                                                <div class="hph-sale-price hph-font-bold hph-text-sm">
                                                    $<?php echo number_format($transaction['sale_price']); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($transaction['agent_commission'] > 0) : ?>
                                                <div class="hph-commission hph-text-xs hph-text-success">
                                                    $<?php echo number_format($transaction['agent_commission']); ?> comm.
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($transaction['close_probability'] > 0) : ?>
                                                <div class="hph-probability hph-text-xs hph-text-muted">
                                                    <?php echo esc_html($transaction['close_probability']); ?>% prob.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($stage_data['transactions']) > 3) : ?>
                                    <div class="hph-stage-more hph-p-3 hph-text-center">
                                        <span class="hph-text-sm hph-text-muted">
                                            +<?php echo count($stage_data['transactions']) - 3; ?> more deals
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Quick Actions -->
                <div class="hph-pipeline-actions hph-mt-6 hph-p-4 hph-bg-light hph-rounded">
                    <h4 class="hph-section-title hph-font-medium hph-mb-3">Quick Actions</h4>
                    
                    <div class="hph-action-buttons hph-grid hph-grid-cols-2 md:hph-grid-cols-3 hph-gap-3">
                        <a href="<?php echo admin_url('post-new.php?post_type=transaction'); ?>" 
                           class="hph-btn hph-btn-primary hph-btn-sm">
                            <i class="fas fa-plus hph-mr-2"></i>
                            New Deal
                        </a>
                        
                        <a href="<?php echo admin_url('edit.php?post_type=transaction'); ?>" 
                           class="hph-btn hph-btn-outline hph-btn-sm">
                            <i class="fas fa-list hph-mr-2"></i>
                            All Transactions
                        </a>
                        
                        <a href="<?php echo admin_url('edit.php?post_type=transaction&page=transaction-pipeline'); ?>" 
                           class="hph-btn hph-btn-outline hph-btn-sm">
                            <i class="fas fa-chart-line hph-mr-2"></i>
                            Full Pipeline
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (empty($pipeline_data) || (array_sum(array_column($pipeline_data, 'count')) === 0)) : ?>
            <!-- Empty State -->
            <div class="hph-empty-state hph-text-center hph-p-8">
                <div class="hph-empty-icon hph-text-4xl hph-text-muted hph-mb-4">
                    <i class="fas fa-handshake"></i>
                </div>
                <h4 class="hph-empty-title hph-font-medium hph-mb-2">No Active Deals</h4>
                <p class="hph-empty-description hph-text-sm hph-text-muted hph-mb-4">
                    Start tracking your transactions and deals to see your pipeline here.
                </p>
                <a href="<?php echo admin_url('post-new.php?post_type=transaction'); ?>" 
                   class="hph-btn hph-btn-primary">
                    <i class="fas fa-plus hph-mr-2"></i>
                    Add Your First Deal
                </a>
            </div>
        <?php endif; ?>
        
    </div>
</div>