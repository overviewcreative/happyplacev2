<?php
/**
 * Transaction Status Component - Progress Tracker
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$status_args = wp_parse_args($args ?? [], [
    'transaction_id' => get_the_ID(),
    'show_timeline' => true,
    'show_details' => true,
    'compact_mode' => false
]);

// Get transaction data
$transaction_id = $status_args['transaction_id'];
if (!$transaction_id) {
    return;
}

$current_status = get_post_meta($transaction_id, 'transaction_status', true) ?: 'draft';
$close_probability = get_post_meta($transaction_id, 'close_probability', true) ?: 0;
$sale_price = get_post_meta($transaction_id, 'sale_price', true);
$closing_date = get_post_meta($transaction_id, 'closing_date', true);
$agent_commission = get_post_meta($transaction_id, 'agent_commission', true);

// Transaction status flow
$status_flow = [
    'draft' => ['label' => 'Draft', 'icon' => 'fa-edit', 'color' => 'secondary'],
    'offer_submitted' => ['label' => 'Offer Submitted', 'icon' => 'fa-paper-plane', 'color' => 'info'],
    'under_contract' => ['label' => 'Under Contract', 'icon' => 'fa-handshake', 'color' => 'primary'],
    'inspection' => ['label' => 'Inspection', 'icon' => 'fa-search', 'color' => 'warning'],
    'appraisal' => ['label' => 'Appraisal', 'icon' => 'fa-calculator', 'color' => 'warning'],
    'financing' => ['label' => 'Financing', 'icon' => 'fa-bank', 'color' => 'warning'],
    'final_walkthrough' => ['label' => 'Final Walkthrough', 'icon' => 'fa-walking', 'color' => 'info'],
    'closing' => ['label' => 'Closing', 'icon' => 'fa-key', 'color' => 'success'],
    'closed' => ['label' => 'Closed', 'icon' => 'fa-check-circle', 'color' => 'success'],
    'cancelled' => ['label' => 'Cancelled', 'icon' => 'fa-times-circle', 'color' => 'danger']
];

// Find current status index
$status_keys = array_keys($status_flow);
$current_index = array_search($current_status, $status_keys);
?>

<div class="hph-transaction-status hph-widget">
    
    <?php if ($status_args['show_details']) : ?>
        <!-- Status Header -->
        <div class="hph-status-header hph-flex hph-items-center hph-justify-between hph-p-4 hph-bg-light hph-rounded-t">
            <div class="hph-current-status hph-flex hph-items-center hph-gap-3">
                <div class="hph-status-icon hph-w-10 hph-h-10 hph-rounded-full hph-bg-<?php echo $status_flow[$current_status]['color']; ?> hph-flex hph-items-center hph-justify-center hph-text-white">
                    <i class="fas <?php echo $status_flow[$current_status]['icon']; ?>"></i>
                </div>
                
                <div class="hph-status-info">
                    <div class="hph-status-label hph-font-medium">
                        <?php echo esc_html($status_flow[$current_status]['label']); ?>
                    </div>
                    <div class="hph-status-subtitle hph-text-sm hph-text-muted">
                        Current Status
                    </div>
                </div>
            </div>
            
            <div class="hph-status-probability hph-text-right">
                <div class="hph-probability-value hph-text-lg hph-font-bold hph-text-<?php echo $status_flow[$current_status]['color']; ?>">
                    <?php echo esc_html($close_probability); ?>%
                </div>
                <div class="hph-probability-label hph-text-xs hph-text-muted">
                    Close Probability
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($status_args['show_timeline']) : ?>
        <!-- Status Timeline -->
        <div class="hph-status-timeline hph-p-4">
            <?php 
            // Exclude cancelled from main timeline unless it's the current status
            $timeline_statuses = $status_flow;
            if ($current_status !== 'cancelled') {
                unset($timeline_statuses['cancelled']);
            }
            
            foreach ($timeline_statuses as $status => $config) :
                $is_current = ($status === $current_status);
                $is_completed = ($current_index !== false && array_search($status, $status_keys) < $current_index);
                $is_future = ($current_index !== false && array_search($status, $status_keys) > $current_index);
                
                // Skip if cancelled and not current
                if ($status === 'cancelled' && !$is_current) {
                    continue;
                }
                
                $step_class = '';
                if ($is_current) {
                    $step_class = 'hph-step-current hph-text-' . $config['color'];
                } elseif ($is_completed) {
                    $step_class = 'hph-step-completed hph-text-success';
                } else {
                    $step_class = 'hph-step-future hph-text-muted';
                }
            ?>
                <div class="hph-timeline-step hph-flex hph-items-center hph-gap-3 hph-mb-4 <?php echo esc_attr($step_class); ?>">
                    <div class="hph-step-icon hph-w-8 hph-h-8 hph-rounded-full hph-border-2 hph-flex hph-items-center hph-justify-center
                                <?php echo $is_current ? 'hph-border-' . $config['color'] . ' hph-bg-' . $config['color'] . ' hph-text-white' : ''; ?>
                                <?php echo $is_completed ? 'hph-border-success hph-bg-success hph-text-white' : ''; ?>
                                <?php echo $is_future ? 'hph-border-gray-300' : ''; ?>">
                        <i class="fas <?php echo $config['icon']; ?> hph-text-xs"></i>
                    </div>
                    
                    <div class="hph-step-content hph-flex-1">
                        <div class="hph-step-label hph-font-medium hph-text-sm">
                            <?php echo esc_html($config['label']); ?>
                        </div>
                        
                        <?php if ($is_current && $closing_date && $status === 'closing') : ?>
                            <div class="hph-step-detail hph-text-xs hph-text-muted">
                                Scheduled: <?php echo date('M j, Y', strtotime($closing_date)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($is_current) : ?>
                        <div class="hph-step-badge hph-px-2 hph-py-1 hph-bg-<?php echo $config['color']; ?> hph-text-white hph-text-xs hph-rounded-full">
                            Current
                        </div>
                    <?php elseif ($is_completed) : ?>
                        <div class="hph-step-badge hph-px-2 hph-py-1 hph-bg-success hph-text-white hph-text-xs hph-rounded-full">
                            <i class="fas fa-check"></i>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Transaction Details -->
    <?php if ($status_args['show_details'] && ($sale_price || $agent_commission || $closing_date)) : ?>
        <div class="hph-transaction-details hph-p-4 hph-bg-light hph-rounded-b">
            <div class="hph-details-grid hph-grid hph-grid-cols-2 md:hph-grid-cols-3 hph-gap-4">
                
                <?php if ($sale_price) : ?>
                    <div class="hph-detail-item hph-text-center">
                        <div class="hph-detail-value hph-font-bold hph-text-lg hph-text-primary">
                            $<?php echo number_format($sale_price); ?>
                        </div>
                        <div class="hph-detail-label hph-text-xs hph-text-muted">Sale Price</div>
                    </div>
                <?php endif; ?>
                
                <?php if ($agent_commission) : ?>
                    <div class="hph-detail-item hph-text-center">
                        <div class="hph-detail-value hph-font-bold hph-text-lg hph-text-success">
                            $<?php echo number_format($agent_commission); ?>
                        </div>
                        <div class="hph-detail-label hph-text-xs hph-text-muted">Commission</div>
                    </div>
                <?php endif; ?>
                
                <?php if ($closing_date) : ?>
                    <div class="hph-detail-item hph-text-center">
                        <div class="hph-detail-value hph-font-bold hph-text-lg">
                            <?php echo date('M j', strtotime($closing_date)); ?>
                        </div>
                        <div class="hph-detail-label hph-text-xs hph-text-muted">
                            <?php echo $current_status === 'closed' ? 'Closed' : 'Closing'; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Quick Actions for Agents -->
    <?php if (current_user_can('edit_posts')) : ?>
        <div class="hph-transaction-actions hph-p-4 hph-border-t">
            <div class="hph-action-buttons hph-flex hph-gap-2 hph-justify-center">
                <a href="<?php echo get_edit_post_link($transaction_id); ?>" 
                   class="hph-btn hph-btn-outline hph-btn-sm">
                    <i class="fas fa-edit hph-mr-1"></i>
                    Edit
                </a>
                
                <?php if ($current_status !== 'closed' && $current_status !== 'cancelled') : ?>
                    <button class="hph-btn hph-btn-primary hph-btn-sm hph-update-status-btn" 
                            data-transaction-id="<?php echo esc_attr($transaction_id); ?>"
                            data-current-status="<?php echo esc_attr($current_status); ?>">
                        <i class="fas fa-arrow-right hph-mr-1"></i>
                        Advance Status
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<script>
jQuery(document).ready(function($) {
    $('.hph-update-status-btn').on('click', function() {
        var transactionId = $(this).data('transaction-id');
        var currentStatus = $(this).data('current-status');
        
        // Simple status advancement logic
        var statusFlow = <?php echo json_encode(array_keys($status_flow)); ?>;
        var currentIndex = statusFlow.indexOf(currentStatus);
        var nextStatus = statusFlow[currentIndex + 1];
        
        if (nextStatus && confirm('Advance this transaction to "' + nextStatus.replace('_', ' ') + '"?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hp_update_transaction_status',
                    post_id: transactionId,
                    status: nextStatus,
                    nonce: '<?php echo wp_create_nonce('hp_transaction_update'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to update status: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Network error. Please try again.');
                }
            });
        }
    });
});
</script>