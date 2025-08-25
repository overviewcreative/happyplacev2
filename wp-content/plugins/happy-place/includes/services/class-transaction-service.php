<?php
/**
 * Transaction Service - Deal Tracking & Management
 * 
 * Manages real estate transactions, offers, and deal progression
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class TransactionService extends Service {
    
    protected string $name = 'transaction_service';
    protected string $version = '4.0.0';
    
    /**
     * Transaction statuses
     */
    const STATUSES = [
        'draft' => 'Draft',
        'offer_submitted' => 'Offer Submitted',
        'under_contract' => 'Under Contract',
        'inspection' => 'Inspection Period',
        'appraisal' => 'Appraisal',
        'financing' => 'Financing',
        'final_walkthrough' => 'Final Walkthrough',
        'closing' => 'Closing',
        'closed' => 'Closed',
        'cancelled' => 'Cancelled'
    ];
    
    /**
     * Transaction types
     */
    const TYPES = [
        'sale' => 'Sale',
        'purchase' => 'Purchase',
        'lease' => 'Lease',
        'rental' => 'Rental'
    ];
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        // Hook into transaction post saves
        add_action('save_post_transaction', [$this, 'process_transaction_save'], 10, 2);
        
        // Hook into post status changes
        add_action('transition_post_status', [$this, 'handle_status_transition'], 10, 3);
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
        
        // Add admin columns
        add_filter('manage_transaction_posts_columns', [$this, 'add_admin_columns']);
        add_action('manage_transaction_posts_custom_column', [$this, 'display_admin_columns'], 10, 2);
        
        // Register admin menu
        if (is_admin()) {
            add_action('admin_menu', [$this, 'register_admin_menu']);
        }
        
        // Add dashboard widget
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
        
        // Schedule commission calculations
        add_action('hp_calculate_commissions', [$this, 'calculate_commissions']);
        if (!wp_next_scheduled('hp_calculate_commissions')) {
            wp_schedule_event(time(), 'daily', 'hp_calculate_commissions');
        }
        
        $this->initialized = true;
        $this->log('Transaction Service initialized successfully');
    }
    
    /**
     * Process transaction save
     */
    public function process_transaction_save(int $post_id, \WP_Post $post): void {
        // Update calculated fields
        $this->update_calculated_fields($post_id);
        
        // Update listing status if connected
        $this->maybe_update_listing_status($post_id);
        
        // Calculate commissions if closed
        $transaction_status = get_post_meta($post_id, 'transaction_status', true);
        if ($transaction_status === 'closed') {
            $this->calculate_transaction_commission($post_id);
        }
        
        do_action('hp_transaction_saved', $post_id, $post);
    }
    
    /**
     * Update calculated fields
     */
    public function update_calculated_fields(int $post_id): void {
        $sale_price = floatval(get_post_meta($post_id, 'sale_price', true));
        $commission_rate = floatval(get_post_meta($post_id, 'commission_rate', true)) ?: 3.0;
        
        // Calculate total commission
        if ($sale_price > 0) {
            $total_commission = ($sale_price * $commission_rate) / 100;
            update_post_meta($post_id, 'total_commission', $total_commission);
            
            // Calculate agent commission (assuming 50/50 split by default)
            $agent_split = floatval(get_post_meta($post_id, 'agent_split', true)) ?: 50.0;
            $agent_commission = ($total_commission * $agent_split) / 100;
            update_post_meta($post_id, 'agent_commission', $agent_commission);
        }
        
        // Calculate days on market if listing is connected
        $listing_id = get_post_meta($post_id, 'listing_id', true);
        if ($listing_id) {
            $listing_date = get_post_field('post_date', $listing_id);
            $closing_date = get_post_meta($post_id, 'closing_date', true) ?: current_time('Y-m-d');
            
            if ($listing_date && $closing_date) {
                $days_on_market = (strtotime($closing_date) - strtotime($listing_date)) / DAY_IN_SECONDS;
                update_post_meta($post_id, 'days_on_market', round($days_on_market));
            }
        }
        
        // Update close probability based on status
        $status = get_post_meta($post_id, 'transaction_status', true);
        $probability = $this->get_close_probability($status);
        update_post_meta($post_id, 'close_probability', $probability);
    }
    
    /**
     * Get close probability based on status
     */
    private function get_close_probability(string $status): int {
        $probabilities = [
            'draft' => 10,
            'offer_submitted' => 25,
            'under_contract' => 50,
            'inspection' => 60,
            'appraisal' => 70,
            'financing' => 80,
            'final_walkthrough' => 90,
            'closing' => 95,
            'closed' => 100,
            'cancelled' => 0
        ];
        
        return $probabilities[$status] ?? 10;
    }
    
    /**
     * Maybe update listing status
     */
    public function maybe_update_listing_status(int $post_id): void {
        $listing_id = get_post_meta($post_id, 'listing_id', true);
        if (!$listing_id) {
            return;
        }
        
        $transaction_status = get_post_meta($post_id, 'transaction_status', true);
        
        $listing_status_map = [
            'under_contract' => 'pending',
            'closed' => 'sold',
            'cancelled' => 'active'
        ];
        
        if (isset($listing_status_map[$transaction_status])) {
            update_post_meta($listing_id, 'listing_status', $listing_status_map[$transaction_status]);
            
            // Set sold date if closed
            if ($transaction_status === 'closed') {
                $closing_date = get_post_meta($post_id, 'closing_date', true);
                if ($closing_date) {
                    update_post_meta($listing_id, 'sold_date', $closing_date);
                }
            }
        }
    }
    
    /**
     * Create transaction
     */
    public function create_transaction(array $data): int {
        $defaults = [
            'post_type' => 'transaction',
            'post_status' => 'publish',
            'post_title' => '',
            'meta_input' => []
        ];
        
        // Generate title if not provided
        if (empty($data['post_title'])) {
            $listing_id = $data['meta_input']['listing_id'] ?? '';
            $property_title = $listing_id ? get_the_title($listing_id) : 'Property';
            $transaction_type = $data['meta_input']['transaction_type'] ?? 'sale';
            
            $data['post_title'] = ucfirst($transaction_type) . ': ' . $property_title;
        }
        
        $data = wp_parse_args($data, $defaults);
        
        // Set default meta values
        $default_meta = [
            'transaction_status' => 'draft',
            'transaction_type' => 'sale',
            'commission_rate' => 3.0,
            'agent_split' => 50.0,
            'close_probability' => 10
        ];
        
        $data['meta_input'] = wp_parse_args($data['meta_input'], $default_meta);
        
        $transaction_id = wp_insert_post($data);
        
        if ($transaction_id && !is_wp_error($transaction_id)) {
            // Create lead if buyer info provided
            if (!empty($data['meta_input']['buyer_email'])) {
                $this->maybe_create_buyer_lead($transaction_id, $data['meta_input']);
            }
            
            do_action('hp_transaction_created', $transaction_id, $data);
            
            $this->log("Created transaction {$transaction_id}");
        }
        
        return $transaction_id;
    }
    
    /**
     * Maybe create buyer lead
     */
    private function maybe_create_buyer_lead(int $transaction_id, array $meta): void {
        if (empty($meta['buyer_email']) || !class_exists('HappyPlace\\Services\\LeadService')) {
            return;
        }
        
        $lead_service = new \HappyPlace\Services\LeadService();
        $lead_service->init();
        
        $lead_data = [
            'first_name' => $meta['buyer_first_name'] ?? 'Buyer',
            'last_name' => $meta['buyer_last_name'] ?? '',
            'email' => $meta['buyer_email'],
            'phone' => $meta['buyer_phone'] ?? '',
            'message' => 'Created from transaction #' . $transaction_id,
            'source' => 'transaction',
            'listing_id' => $meta['listing_id'] ?? 0,
            'agent_id' => $meta['agent_id'] ?? 0,
            'status' => 'qualified'
        ];
        
        $lead_id = $lead_service->create_lead($lead_data);
        
        if ($lead_id) {
            update_post_meta($transaction_id, 'buyer_lead_id', $lead_id);
        }
    }
    
    /**
     * Get transaction pipeline
     */
    public function get_transaction_pipeline(int $agent_id = 0): array {
        $args = [
            'post_type' => 'transaction',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'transaction_status',
                    'value' => ['closed', 'cancelled'],
                    'compare' => 'NOT IN'
                ]
            ]
        ];
        
        if ($agent_id) {
            $args['meta_query'][] = [
                'key' => 'agent_id',
                'value' => $agent_id,
                'compare' => '='
            ];
        }
        
        $posts = get_posts($args);
        $pipeline = [];
        
        foreach (self::STATUSES as $status => $label) {
            if (in_array($status, ['closed', 'cancelled'])) {
                continue;
            }
            
            $pipeline[$status] = [
                'label' => $label,
                'transactions' => [],
                'total_value' => 0,
                'count' => 0
            ];
        }
        
        foreach ($posts as $post) {
            $status = get_post_meta($post->ID, 'transaction_status', true) ?: 'draft';
            $sale_price = floatval(get_post_meta($post->ID, 'sale_price', true));
            $listing_id = get_post_meta($post->ID, 'listing_id', true);
            
            $transaction = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'sale_price' => $sale_price,
                'listing_id' => $listing_id,
                'listing_title' => $listing_id ? get_the_title($listing_id) : '',
                'buyer_name' => get_post_meta($post->ID, 'buyer_name', true),
                'closing_date' => get_post_meta($post->ID, 'closing_date', true),
                'agent_commission' => floatval(get_post_meta($post->ID, 'agent_commission', true)),
                'close_probability' => intval(get_post_meta($post->ID, 'close_probability', true))
            ];
            
            if (isset($pipeline[$status])) {
                $pipeline[$status]['transactions'][] = $transaction;
                $pipeline[$status]['total_value'] += $sale_price;
                $pipeline[$status]['count']++;
            }
        }
        
        return $pipeline;
    }
    
    /**
     * Get transaction statistics
     */
    public function get_transaction_stats(int $agent_id = 0, string $period = 'ytd'): array {
        global $wpdb;
        
        // Date filter
        $date_filter = '';
        switch ($period) {
            case 'mtd':
                $date_filter = "AND MONTH(p.post_date) = MONTH(NOW()) AND YEAR(p.post_date) = YEAR(NOW())";
                break;
            case 'ytd':
                $date_filter = "AND YEAR(p.post_date) = YEAR(NOW())";
                break;
            case 'last_month':
                $date_filter = "AND MONTH(p.post_date) = MONTH(NOW() - INTERVAL 1 MONTH) AND YEAR(p.post_date) = YEAR(NOW())";
                break;
        }
        
        // Agent filter
        $agent_filter = $agent_id ? $wpdb->prepare("AND agent.meta_value = %s", $agent_id) : '';
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status.meta_value = 'closed' THEN 1 ELSE 0 END) as closed_transactions,
                SUM(CASE WHEN status.meta_value NOT IN ('closed', 'cancelled') THEN 1 ELSE 0 END) as active_transactions,
                COALESCE(AVG(CASE WHEN status.meta_value = 'closed' THEN CAST(sale_price.meta_value AS DECIMAL(15,2)) END), 0) as avg_sale_price,
                COALESCE(SUM(CASE WHEN status.meta_value = 'closed' THEN CAST(sale_price.meta_value AS DECIMAL(15,2)) ELSE 0 END), 0) as total_volume,
                COALESCE(SUM(CASE WHEN status.meta_value = 'closed' THEN CAST(agent_commission.meta_value AS DECIMAL(15,2)) ELSE 0 END), 0) as total_commission,
                AVG(CASE WHEN status.meta_value = 'closed' THEN CAST(days_on_market.meta_value AS DECIMAL(10,2)) END) as avg_days_on_market
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} status ON p.ID = status.post_id AND status.meta_key = 'transaction_status'
            LEFT JOIN {$wpdb->postmeta} sale_price ON p.ID = sale_price.post_id AND sale_price.meta_key = 'sale_price'
            LEFT JOIN {$wpdb->postmeta} agent_commission ON p.ID = agent_commission.post_id AND agent_commission.meta_key = 'agent_commission'
            LEFT JOIN {$wpdb->postmeta} days_on_market ON p.ID = days_on_market.post_id AND days_on_market.meta_key = 'days_on_market'
            " . ($agent_id ? "LEFT JOIN {$wpdb->postmeta} agent ON p.ID = agent.post_id AND agent.meta_key = 'agent_id'" : '') . "
            WHERE p.post_type = 'transaction' 
            AND p.post_status = 'publish'
            {$date_filter}
            {$agent_filter}
        ", ARRAY_A);
        
        // Calculate conversion rate
        $conversion_rate = 0;
        if ($stats['total_transactions'] > 0) {
            $conversion_rate = ($stats['closed_transactions'] / $stats['total_transactions']) * 100;
        }
        
        return [
            'total_transactions' => intval($stats['total_transactions']),
            'closed_transactions' => intval($stats['closed_transactions']),
            'active_transactions' => intval($stats['active_transactions']),
            'avg_sale_price' => floatval($stats['avg_sale_price']),
            'total_volume' => floatval($stats['total_volume']),
            'total_commission' => floatval($stats['total_commission']),
            'avg_days_on_market' => round(floatval($stats['avg_days_on_market']) ?: 0, 1),
            'conversion_rate' => round($conversion_rate, 2)
        ];
    }
    
    /**
     * Calculate transaction commission
     */
    public function calculate_transaction_commission(int $post_id): void {
        $sale_price = floatval(get_post_meta($post_id, 'sale_price', true));
        $commission_rate = floatval(get_post_meta($post_id, 'commission_rate', true)) ?: 3.0;
        $agent_split = floatval(get_post_meta($post_id, 'agent_split', true)) ?: 50.0;
        
        if ($sale_price <= 0) {
            return;
        }
        
        // Calculate total commission
        $total_commission = ($sale_price * $commission_rate) / 100;
        
        // Calculate agent commission
        $agent_commission = ($total_commission * $agent_split) / 100;
        
        // Calculate brokerage commission
        $brokerage_commission = $total_commission - $agent_commission;
        
        // Update meta
        update_post_meta($post_id, 'total_commission', $total_commission);
        update_post_meta($post_id, 'agent_commission', $agent_commission);
        update_post_meta($post_id, 'brokerage_commission', $brokerage_commission);
        
        // Record commission calculation date
        update_post_meta($post_id, 'commission_calculated_date', current_time('mysql'));
        
        do_action('hp_transaction_commission_calculated', $post_id, [
            'total_commission' => $total_commission,
            'agent_commission' => $agent_commission,
            'brokerage_commission' => $brokerage_commission
        ]);
    }
    
    /**
     * Handle status transition
     */
    public function handle_status_transition(string $new_status, string $old_status, \WP_Post $post): void {
        if ($post->post_type !== 'transaction') {
            return;
        }
        
        $transaction_status = get_post_meta($post->ID, 'transaction_status', true);
        
        // Send notifications based on status changes
        $this->maybe_send_status_notification($post->ID, $transaction_status, $old_status);
    }
    
    /**
     * Maybe send status notification
     */
    private function maybe_send_status_notification(int $post_id, string $new_status, string $old_status): void {
        // Get agent email
        $agent_id = get_post_meta($post_id, 'agent_id', true);
        if (!$agent_id) {
            return;
        }
        
        $agent = get_user_by('id', $agent_id);
        if (!$agent) {
            return;
        }
        
        $post = get_post($post_id);
        $listing_id = get_post_meta($post_id, 'listing_id', true);
        $property_title = $listing_id ? get_the_title($listing_id) : $post->post_title;
        
        // Send notifications for key milestones
        $notify_statuses = ['under_contract', 'closed', 'cancelled'];
        
        if (!in_array($new_status, $notify_statuses)) {
            return;
        }
        
        $subject = "Transaction Update: {$property_title} - " . self::STATUSES[$new_status];
        
        $message = "Your transaction has been updated:\n\n";
        $message .= "Property: {$property_title}\n";
        $message .= "Status: " . self::STATUSES[$new_status] . "\n";
        $message .= "Transaction: {$post->post_title}\n\n";
        
        if ($new_status === 'closed') {
            $sale_price = get_post_meta($post_id, 'sale_price', true);
            $agent_commission = get_post_meta($post_id, 'agent_commission', true);
            
            if ($sale_price) {
                $message .= "Sale Price: $" . number_format($sale_price) . "\n";
            }
            
            if ($agent_commission) {
                $message .= "Your Commission: $" . number_format($agent_commission) . "\n";
            }
        }
        
        $admin_url = admin_url('post.php?post=' . $post_id . '&action=edit');
        $message .= "\nView Transaction: {$admin_url}";
        
        wp_mail($agent->user_email, $subject, $message);
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        add_action('wp_ajax_hp_update_transaction_status', [$this, 'ajax_update_status']);
        add_action('wp_ajax_hp_get_transaction_pipeline', [$this, 'ajax_get_pipeline']);
        add_action('wp_ajax_hp_calculate_commission', [$this, 'ajax_calculate_commission']);
    }
    
    /**
     * AJAX: Update transaction status
     */
    public function ajax_update_status(): void {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $new_status = sanitize_text_field($_POST['status'] ?? '');
        
        if (!$post_id || !array_key_exists($new_status, self::STATUSES)) {
            wp_send_json_error(['message' => 'Invalid request']);
            return;
        }
        
        update_post_meta($post_id, 'transaction_status', $new_status);
        
        // Trigger save action to update calculated fields
        $post = get_post($post_id);
        if ($post) {
            $this->process_transaction_save($post_id, $post);
        }
        
        wp_send_json_success([
            'message' => 'Status updated successfully',
            'status' => $new_status,
            'status_label' => self::STATUSES[$new_status]
        ]);
    }
    
    /**
     * Add admin columns
     */
    public function add_admin_columns($columns): array {
        $new_columns = [];
        
        foreach ($columns as $key => $title) {
            $new_columns[$key] = $title;
            
            if ($key === 'title') {
                $new_columns['listing'] = 'Property';
                $new_columns['status'] = 'Status';
                $new_columns['sale_price'] = 'Sale Price';
                $new_columns['commission'] = 'Commission';
                $new_columns['closing_date'] = 'Closing Date';
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Display admin columns
     */
    public function display_admin_columns($column, $post_id): void {
        switch ($column) {
            case 'listing':
                $listing_id = get_post_meta($post_id, 'listing_id', true);
                if ($listing_id) {
                    echo '<a href="' . get_edit_post_link($listing_id) . '">' . get_the_title($listing_id) . '</a>';
                } else {
                    echo '—';
                }
                break;
                
            case 'status':
                $status = get_post_meta($post_id, 'transaction_status', true) ?: 'draft';
                $probability = get_post_meta($post_id, 'close_probability', true);
                echo '<span class="hp-status hp-status-' . esc_attr($status) . '">' . self::STATUSES[$status] . '</span>';
                if ($probability) {
                    echo '<br><small>' . $probability . '% probability</small>';
                }
                break;
                
            case 'sale_price':
                $price = get_post_meta($post_id, 'sale_price', true);
                echo $price ? '$' . number_format($price) : '—';
                break;
                
            case 'commission':
                $commission = get_post_meta($post_id, 'agent_commission', true);
                echo $commission ? '$' . number_format($commission) : '—';
                break;
                
            case 'closing_date':
                $date = get_post_meta($post_id, 'closing_date', true);
                echo $date ? date('M j, Y', strtotime($date)) : '—';
                break;
        }
    }
    
    /**
     * Register admin menu
     */
    public function register_admin_menu(): void {
        add_submenu_page(
            'edit.php?post_type=transaction',
            'Transaction Pipeline',
            'Pipeline',
            'edit_posts',
            'transaction-pipeline',
            [$this, 'render_pipeline_page']
        );
    }
    
    /**
     * Render pipeline page
     */
    public function render_pipeline_page(): void {
        $pipeline = $this->get_transaction_pipeline();
        $stats = $this->get_transaction_stats();
        include HP_PLUGIN_DIR . 'templates/admin/transaction-pipeline.php';
    }
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget(): void {
        if (current_user_can('edit_posts')) {
            wp_add_dashboard_widget(
                'hp_transaction_summary',
                'Transaction Summary',
                [$this, 'render_dashboard_widget']
            );
        }
    }
    
    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget(): void {
        $current_user_id = get_current_user_id();
        $user = wp_get_current_user();
        
        // Show agent-specific stats if user is an agent
        $agent_id = in_array('agent', $user->roles) ? $current_user_id : 0;
        
        $stats = $this->get_transaction_stats($agent_id);
        $pipeline = $this->get_transaction_pipeline($agent_id);
        
        ?>
        <div class="hp-transaction-widget">
            <div class="hp-stats-grid">
                <div class="hp-stat">
                    <span class="hp-stat-number"><?php echo $stats['active_transactions']; ?></span>
                    <span class="hp-stat-label">Active Deals</span>
                </div>
                <div class="hp-stat">
                    <span class="hp-stat-number">$<?php echo number_format($stats['total_volume']); ?></span>
                    <span class="hp-stat-label">YTD Volume</span>
                </div>
                <div class="hp-stat">
                    <span class="hp-stat-number">$<?php echo number_format($stats['total_commission']); ?></span>
                    <span class="hp-stat-label">YTD Commission</span>
                </div>
            </div>
            
            <p>
                <a href="<?php echo admin_url('edit.php?post_type=transaction'); ?>">View All Transactions</a> |
                <a href="<?php echo admin_url('edit.php?post_type=transaction&page=transaction-pipeline'); ?>">View Pipeline</a>
            </p>
        </div>
        
        <style>
        .hp-stats-grid {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .hp-stat {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .hp-stat-number {
            display: block;
            font-size: 18px;
            font-weight: bold;
            color: #0073aa;
        }
        .hp-stat-label {
            display: block;
            font-size: 12px;
            color: #666;
        }
        </style>
        <?php
    }
}