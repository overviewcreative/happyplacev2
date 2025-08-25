<?php
/**
 * Admin Lead Manager Template
 * 
 * @package HappyPlace
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get Lead Service instance
$lead_service = new \HappyPlace\Services\LeadService();
$lead_service->init();

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['s'] ?? '';
$paged = $_GET['paged'] ?? 1;
$per_page = 20;
$offset = ($paged - 1) * $per_page;

// Get leads
$leads = $lead_service->get_leads([
    'status' => $status_filter,
    'search' => $search,
    'limit' => $per_page,
    'offset' => $offset
]);

// Get total count for pagination
global $wpdb;
$table_name = $wpdb->prefix . 'hp_leads';
$total_leads = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
$total_pages = ceil($total_leads / $per_page);

// Lead statuses
$statuses = [
    'new' => 'New',
    'contacted' => 'Contacted',
    'qualified' => 'Qualified',
    'proposal' => 'Proposal',
    'negotiation' => 'Negotiation',
    'closed_won' => 'Closed Won',
    'closed_lost' => 'Closed Lost'
];

// Status colors
$status_colors = [
    'new' => '#0073aa',
    'contacted' => '#2271b1',
    'qualified' => '#00a32a',
    'proposal' => '#dba617',
    'negotiation' => '#d63638',
    'closed_won' => '#00a32a',
    'closed_lost' => '#dc3232'
];
?>

<div class="wrap hp-leads-manager">
    <h1 class="wp-heading-inline">Lead Management</h1>
    <a href="#" class="page-title-action hp-export-leads">Export CSV</a>
    <hr class="wp-header-end">
    
    <!-- Filters -->
    <div class="hp-leads-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="hp-leads">
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select name="status" id="filter-by-status">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($status_filter, $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="submit" class="button" value="Filter">
                </div>
                
                <div class="alignright">
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search leads...">
                    <input type="submit" class="button" value="Search">
                </div>
            </div>
        </form>
    </div>
    
    <!-- Lead Statistics -->
    <div class="hp-lead-stats">
        <?php
        $stats = $wpdb->get_results("
            SELECT status, COUNT(*) as count 
            FROM {$table_name} 
            GROUP BY status
        ");
        
        foreach ($stats as $stat):
            $color = $status_colors[$stat->status] ?? '#666';
        ?>
        <div class="hp-stat-card" style="border-left: 4px solid <?php echo $color; ?>">
            <div class="hp-stat-number"><?php echo $stat->count; ?></div>
            <div class="hp-stat-label"><?php echo $statuses[$stat->status] ?? $stat->status; ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Leads Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column">Name</th>
                <th scope="col" class="manage-column">Contact</th>
                <th scope="col" class="manage-column">Source</th>
                <th scope="col" class="manage-column">Property</th>
                <th scope="col" class="manage-column">Score</th>
                <th scope="col" class="manage-column">Status</th>
                <th scope="col" class="manage-column">Date</th>
                <th scope="col" class="manage-column">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($leads)): ?>
                <tr>
                    <td colspan="8" class="no-items">No leads found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($leads as $lead): ?>
                <tr data-lead-id="<?php echo $lead['id']; ?>">
                    <td>
                        <strong>
                            <a href="#" class="hp-lead-details" data-lead-id="<?php echo $lead['id']; ?>">
                                <?php echo esc_html($lead['first_name'] . ' ' . $lead['last_name']); ?>
                            </a>
                        </strong>
                        <?php if ($lead['message']): ?>
                            <div class="row-actions">
                                <span class="view-message">
                                    <a href="#" class="hp-view-message" data-message="<?php echo esc_attr($lead['message']); ?>">View Message</a>
                                </span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="mailto:<?php echo esc_attr($lead['email']); ?>"><?php echo esc_html($lead['email']); ?></a>
                        <?php if ($lead['phone']): ?>
                            <br><small><?php echo esc_html($lead['phone']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo esc_html($lead['source']); ?>
                        <?php if ($lead['utm_campaign']): ?>
                            <br><small>Campaign: <?php echo esc_html($lead['utm_campaign']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($lead['listing_id']): ?>
                            <a href="<?php echo get_permalink($lead['listing_id']); ?>" target="_blank">
                                <?php echo get_the_title($lead['listing_id']); ?>
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="hp-lead-score">
                            <div class="hp-score-bar">
                                <div class="hp-score-fill" style="width: <?php echo $lead['lead_score']; ?>%; background: <?php echo $lead['lead_score'] > 70 ? '#00a32a' : ($lead['lead_score'] > 40 ? '#dba617' : '#d63638'); ?>"></div>
                            </div>
                            <span class="hp-score-text"><?php echo $lead['lead_score']; ?>/100</span>
                        </div>
                    </td>
                    <td>
                        <select class="hp-lead-status" data-lead-id="<?php echo $lead['id']; ?>">
                            <?php foreach ($statuses as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($lead['status'], $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <?php echo date('M j, Y', strtotime($lead['created_at'])); ?>
                        <br><small><?php echo date('g:i a', strtotime($lead['created_at'])); ?></small>
                    </td>
                    <td>
                        <button class="button button-small hp-add-note" data-lead-id="<?php echo $lead['id']; ?>">Note</button>
                        <button class="button button-small hp-delete-lead" data-lead-id="<?php echo $lead['id']; ?>">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo $total_leads; ?> items</span>
            <span class="pagination-links">
                <?php if ($paged > 1): ?>
                    <a class="prev-page" href="?page=hp-leads&paged=<?php echo $paged - 1; ?>&status=<?php echo $status_filter; ?>&s=<?php echo $search; ?>">
                        <span aria-hidden="true">‹</span>
                    </a>
                <?php endif; ?>
                
                <span class="paging-input">
                    <span class="current-page"><?php echo $paged; ?></span> of <span class="total-pages"><?php echo $total_pages; ?></span>
                </span>
                
                <?php if ($paged < $total_pages): ?>
                    <a class="next-page" href="?page=hp-leads&paged=<?php echo $paged + 1; ?>&status=<?php echo $status_filter; ?>&s=<?php echo $search; ?>">
                        <span aria-hidden="true">›</span>
                    </a>
                <?php endif; ?>
            </span>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Lead Details Modal -->
<div id="hp-lead-details-modal" class="hp-modal" style="display: none;">
    <div class="hp-modal-content">
        <span class="hp-modal-close">&times;</span>
        <h2>Lead Details</h2>
        <div class="hp-lead-details-content"></div>
    </div>
</div>

<!-- Add Note Modal -->
<div id="hp-add-note-modal" class="hp-modal" style="display: none;">
    <div class="hp-modal-content">
        <span class="hp-modal-close">&times;</span>
        <h2>Add Note</h2>
        <form id="hp-add-note-form">
            <input type="hidden" id="note-lead-id" value="">
            <textarea id="note-content" rows="4" placeholder="Enter your note..."></textarea>
            <button type="submit" class="button button-primary">Add Note</button>
        </form>
    </div>
</div>

<style>
.hp-leads-manager {
    max-width: 100%;
}

.hp-lead-stats {
    display: flex;
    gap: 20px;
    margin: 20px 0;
}

.hp-stat-card {
    background: #fff;
    padding: 15px;
    border: 1px solid #ddd;
    flex: 1;
}

.hp-stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.hp-stat-label {
    color: #666;
    font-size: 14px;
    margin-top: 5px;
}

.hp-lead-score {
    display: flex;
    align-items: center;
    gap: 10px;
}

.hp-score-bar {
    width: 60px;
    height: 6px;
    background: #f0f0f0;
    border-radius: 3px;
    overflow: hidden;
}

.hp-score-fill {
    height: 100%;
    transition: width 0.3s;
}

.hp-score-text {
    font-size: 12px;
    color: #666;
}

.hp-modal {
    position: fixed;
    z-index: 999999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.hp-modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
    max-width: 600px;
    position: relative;
}

.hp-modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.hp-modal-close:hover {
    color: #000;
}

#note-content {
    width: 100%;
    margin: 10px 0;
}

.no-items {
    text-align: center;
    padding: 20px;
    color: #666;
}
</style>