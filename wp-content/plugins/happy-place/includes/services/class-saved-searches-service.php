<?php
/**
 * Saved Searches Service
 * 
 * Manages user saved searches with automated email alerts
 * for new matching properties
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class SavedSearchesService extends Service {
    
    protected string $name = 'saved_searches_service';
    protected string $version = '4.0.0';
    
    /**
     * Table name
     */
    private string $table_name;
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'saved_searches';
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
        
        // Register cron hooks for email alerts
        $this->register_cron_hooks();
        
        // Register shortcodes
        add_shortcode('saved_searches', [$this, 'render_saved_searches_shortcode']);
        add_shortcode('save_search_form', [$this, 'render_save_search_form_shortcode']);
        
        $this->initialized = true;
        $this->log('Saved Searches Service initialized successfully');
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        // User handlers
        add_action('wp_ajax_save_search', [$this, 'ajax_save_search']);
        add_action('wp_ajax_get_saved_searches', [$this, 'ajax_get_saved_searches']);
        add_action('wp_ajax_delete_saved_search', [$this, 'ajax_delete_saved_search']);
        add_action('wp_ajax_update_search_frequency', [$this, 'ajax_update_search_frequency']);
        add_action('wp_ajax_toggle_search_active', [$this, 'ajax_toggle_search_active']);
        
        // Admin handlers
        add_action('wp_ajax_send_test_search_alert', [$this, 'ajax_send_test_alert']);
    }
    
    /**
     * Register cron hooks
     */
    private function register_cron_hooks(): void {
        // Schedule cron jobs if they don't exist
        if (!wp_next_scheduled('hp_process_search_alerts')) {
            wp_schedule_event(time(), 'hourly', 'hp_process_search_alerts');
        }
        
        if (!wp_next_scheduled('hp_daily_search_digest')) {
            wp_schedule_event(time() + (8 * HOUR_IN_SECONDS), 'daily', 'hp_daily_search_digest');
        }
        
        // Hook into cron events
        add_action('hp_process_search_alerts', [$this, 'process_search_alerts']);
        add_action('hp_daily_search_digest', [$this, 'send_daily_digest']);
    }
    
    /**
     * Save a new search for user
     */
    public function save_search(int $user_id, array $search_data): int {
        global $wpdb;
        
        // Validate search criteria
        if (empty($search_data['criteria'])) {
            return 0;
        }
        
        // Prepare search data
        $data = [
            'user_id' => $user_id,
            'search_name' => sanitize_text_field($search_data['name'] ?? 'My Search ' . date('M j')),
            'search_criteria' => wp_json_encode($search_data['criteria']),
            'email_frequency' => sanitize_text_field($search_data['frequency'] ?? 'daily'),
            'is_active' => true,
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert($this->table_name, $data);
        
        if ($result !== false) {
            $search_id = $wpdb->insert_id;
            
            // Track engagement
            if (class_exists('\HappyPlace\Services\UserEngagementService')) {
                $engagement_service = new \HappyPlace\Services\UserEngagementService();
                $engagement_service->track_activity($user_id, 'search_saved', $search_id, 'saved_search', [
                    'points' => 8 // Award points for saving searches
                ]);
            }
            
            $this->log("Saved search created: ID {$search_id}, User {$user_id}");
            return $search_id;
        }
        
        $this->log("Failed to save search for user {$user_id}: " . $wpdb->last_error, 'error');
        return 0;
    }
    
    /**
     * Get user's saved searches
     */
    public function get_user_searches(int $user_id, bool $active_only = false): array {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table_name} WHERE user_id = %d";
        $params = [$user_id];
        
        if ($active_only) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $searches = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
        
        // Decode JSON criteria
        foreach ($searches as &$search) {
            $search['criteria'] = json_decode($search['search_criteria'], true);
        }
        
        return $searches;
    }
    
    /**
     * Delete saved search
     */
    public function delete_search(int $search_id, int $user_id = null): bool {
        global $wpdb;
        
        $where = ['id' => $search_id];
        $where_format = ['%d'];
        
        // If user_id provided, ensure ownership
        if ($user_id) {
            $where['user_id'] = $user_id;
            $where_format[] = '%d';
        }
        
        $result = $wpdb->delete($this->table_name, $where, $where_format);
        
        if ($result !== false) {
            $this->log("Deleted saved search: ID {$search_id}");
            return true;
        }
        
        return false;
    }
    
    /**
     * Update search frequency
     */
    public function update_search_frequency(int $search_id, string $frequency, int $user_id = null): bool {
        global $wpdb;
        
        $valid_frequencies = ['instant', 'daily', 'weekly', 'monthly'];
        if (!in_array($frequency, $valid_frequencies)) {
            return false;
        }
        
        $where = ['id' => $search_id];
        $where_format = ['%d'];
        
        if ($user_id) {
            $where['user_id'] = $user_id;
            $where_format[] = '%d';
        }
        
        $result = $wpdb->update(
            $this->table_name,
            ['email_frequency' => $frequency],
            $where,
            ['%s'],
            $where_format
        );
        
        return $result !== false;
    }
    
    /**
     * Toggle search active status
     */
    public function toggle_search_active(int $search_id, int $user_id = null): bool {
        global $wpdb;
        
        // Get current status
        $sql = "SELECT is_active FROM {$this->table_name} WHERE id = %d";
        $params = [$search_id];
        
        if ($user_id) {
            $sql .= " AND user_id = %d";
            $params[] = $user_id;
        }
        
        $current_status = $wpdb->get_var($wpdb->prepare($sql, $params));
        
        if ($current_status === null) {
            return false;
        }
        
        $new_status = !$current_status;
        
        $where = ['id' => $search_id];
        $where_format = ['%d'];
        
        if ($user_id) {
            $where['user_id'] = $user_id;
            $where_format[] = '%d';
        }
        
        $result = $wpdb->update(
            $this->table_name,
            ['is_active' => $new_status],
            $where,
            ['%d'],
            $where_format
        );
        
        return $result !== false;
    }
    
    /**
     * Process search alerts (called by cron)
     */
    public function process_search_alerts(): void {
        global $wpdb;
        
        $this->log('Processing search alerts...');
        
        // Get searches that need alerts
        $current_time = current_time('mysql');
        
        $sql = "SELECT * FROM {$this->table_name} 
                WHERE is_active = 1 
                AND (
                    (email_frequency = 'instant') OR
                    (email_frequency = 'daily' AND (last_sent IS NULL OR last_sent < DATE_SUB(NOW(), INTERVAL 23 HOUR))) OR
                    (email_frequency = 'weekly' AND (last_sent IS NULL OR last_sent < DATE_SUB(NOW(), INTERVAL 6 DAY))) OR
                    (email_frequency = 'monthly' AND (last_sent IS NULL OR last_sent < DATE_SUB(NOW(), INTERVAL 29 DAY)))
                )
                ORDER BY last_sent ASC
                LIMIT 50"; // Process in batches
        
        $searches = $wpdb->get_results($sql, ARRAY_A);
        
        $alerts_sent = 0;
        
        foreach ($searches as $search) {
            $search_criteria = json_decode($search['search_criteria'], true);
            
            // Find new listings matching criteria
            $new_listings = $this->find_matching_listings($search_criteria, $search['last_sent']);
            
            if (!empty($new_listings)) {
                // Send alert email
                $email_sent = $this->send_search_alert($search, $new_listings);
                
                if ($email_sent) {
                    // Update last_sent timestamp
                    $wpdb->update(
                        $this->table_name,
                        [
                            'last_sent' => $current_time,
                            'total_sent' => $search['total_sent'] + 1
                        ],
                        ['id' => $search['id']],
                        ['%s', '%d'],
                        ['%d']
                    );
                    
                    $alerts_sent++;
                }
                
                // Add delay to prevent overwhelming email servers
                sleep(1);
            }
        }
        
        $this->log("Search alerts processed: {$alerts_sent} alerts sent");
    }
    
    /**
     * Find listings matching search criteria
     */
    private function find_matching_listings(array $criteria, ?string $since_date): array {
        $query_args = [
            'post_type' => 'listing',
            'post_status' => 'publish',
            'posts_per_page' => 10, // Limit to prevent huge emails
            'meta_query' => ['relation' => 'AND'],
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        // Date filter - only new listings
        if ($since_date) {
            $query_args['date_query'] = [
                'after' => $since_date,
                'inclusive' => false
            ];
        } else {
            // First time - get listings from last 7 days
            $query_args['date_query'] = [
                'after' => '7 days ago'
            ];
        }
        
        // Build meta query from criteria
        if (!empty($criteria['min_price'])) {
            $query_args['meta_query'][] = [
                'key' => 'listing_price',
                'value' => intval($criteria['min_price']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            ];
        }
        
        if (!empty($criteria['max_price'])) {
            $query_args['meta_query'][] = [
                'key' => 'listing_price',
                'value' => intval($criteria['max_price']),
                'compare' => '<=',
                'type' => 'NUMERIC'
            ];
        }
        
        if (!empty($criteria['property_type'])) {
            $query_args['meta_query'][] = [
                'key' => 'property_type',
                'value' => sanitize_text_field($criteria['property_type']),
                'compare' => '='
            ];
        }
        
        if (!empty($criteria['bedrooms'])) {
            $query_args['meta_query'][] = [
                'key' => 'bedrooms',
                'value' => intval($criteria['bedrooms']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            ];
        }
        
        if (!empty($criteria['bathrooms'])) {
            $query_args['meta_query'][] = [
                'key' => 'bathrooms_full',
                'value' => floatval($criteria['bathrooms']),
                'compare' => '>=',
                'type' => 'DECIMAL'
            ];
        }
        
        // Location search
        if (!empty($criteria['location'])) {
            $location = sanitize_text_field($criteria['location']);
            $query_args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => 'city',
                    'value' => $location,
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'neighborhood',
                    'value' => $location,
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'zip_code',
                    'value' => $location,
                    'compare' => 'LIKE'
                ]
            ];
        }
        
        // Text search in title and content
        if (!empty($criteria['keywords'])) {
            $query_args['s'] = sanitize_text_field($criteria['keywords']);
        }
        
        $listings_query = new \WP_Query($query_args);
        
        return $listings_query->posts;
    }
    
    /**
     * Send search alert email
     */
    private function send_search_alert(array $search, array $listings): bool {
        $user = get_user_by('id', $search['user_id']);
        if (!$user) {
            return false;
        }
        
        $subject = sprintf(
            '%d New Properties Match Your Saved Search - %s',
            count($listings),
            $search['search_name']
        );
        
        // Generate email content
        ob_start();
        $this->render_search_alert_email($search, $listings, $user);
        $email_content = ob_get_clean();
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <noreply@' . parse_url(home_url(), PHP_URL_HOST) . '>'
        ];
        
        $email_sent = wp_mail($user->user_email, $subject, $email_content, $headers);
        
        if ($email_sent) {
            $this->log("Search alert sent to {$user->user_email} for search '{$search['search_name']}'");
        } else {
            $this->log("Failed to send search alert to {$user->user_email}", 'error');
        }
        
        return $email_sent;
    }
    
    /**
     * Render search alert email template
     */
    private function render_search_alert_email(array $search, array $listings, \WP_User $user): void {
        $site_name = get_bloginfo('name');
        $site_url = home_url();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>New Properties - <?php echo esc_html($search['search_name']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; }
                .listing { border: 1px solid #ddd; margin: 20px 0; padding: 15px; border-radius: 8px; }
                .listing h3 { margin: 0 0 10px 0; color: #007cba; }
                .listing-details { margin: 10px 0; }
                .price { font-size: 1.2em; font-weight: bold; color: #28a745; }
                .btn { background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 0.9em; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><?php echo esc_html($site_name); ?></h1>
                    <h2>New Properties Match Your Search</h2>
                    <p><strong><?php echo esc_html($search['search_name']); ?></strong></p>
                </div>
                
                <p>Hi <?php echo esc_html($user->first_name ?: $user->display_name); ?>,</p>
                
                <p>We found <?php echo count($listings); ?> new properties that match your saved search criteria:</p>
                
                <?php foreach ($listings as $listing): ?>
                    <div class="listing">
                        <h3><?php echo esc_html($listing->post_title); ?></h3>
                        
                        <div class="listing-details">
                            <?php
                            $price = hpt_get_listing_price_formatted($listing->ID);
                            $address = hpt_get_listing_address($listing->ID);
                            $bedrooms = get_field('bedrooms', $listing->ID);
                            $bathrooms = hpt_get_listing_bathrooms($listing->ID);
                            $sqft = get_field('square_feet', $listing->ID);
                            ?>
                            
                            <?php if ($price): ?>
                                <div class="price"><?php echo esc_html($price); ?></div>
                            <?php endif; ?>
                            
                            <?php if ($address): ?>
                                <div><strong>Address:</strong> <?php echo esc_html($address); ?></div>
                            <?php endif; ?>
                            
                            <div>
                                <?php if ($bedrooms): ?>
                                    <strong><?php echo $bedrooms; ?></strong> bed<?php echo $bedrooms > 1 ? 's' : ''; ?>
                                <?php endif; ?>
                                
                                <?php if ($bathrooms): ?>
                                    • <strong><?php echo $bathrooms; ?></strong> bath<?php echo $bathrooms > 1 ? 's' : ''; ?>
                                <?php endif; ?>
                                
                                <?php if ($sqft): ?>
                                    • <strong><?php echo number_format($sqft); ?></strong> sq ft
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($listing->post_excerpt): ?>
                                <p><?php echo wp_trim_words($listing->post_excerpt, 25); ?></p>
                            <?php endif; ?>
                            
                            <p>
                                <a href="<?php echo get_permalink($listing->ID); ?>" class="btn">View Property</a>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <p>
                    <a href="<?php echo home_url('/my-account/saved-searches'); ?>" class="btn">
                        Manage Your Saved Searches
                    </a>
                </p>
                
                <div class="footer">
                    <p>You're receiving this email because you have an active saved search alert.</p>
                    <p>
                        <a href="<?php echo home_url('/my-account/saved-searches'); ?>">Update your preferences</a> |
                        <a href="<?php echo $site_url; ?>">Visit <?php echo esc_html($site_name); ?></a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * AJAX handler for saving search
     */
    public function ajax_save_search(): void {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please login to save searches']);
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hph_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $user_id = get_current_user_id();
        $search_data = [
            'name' => sanitize_text_field($_POST['search_name'] ?? ''),
            'criteria' => $_POST['criteria'] ?? [],
            'frequency' => sanitize_text_field($_POST['frequency'] ?? 'daily')
        ];
        
        // Validate criteria
        if (empty($search_data['criteria'])) {
            wp_send_json_error(['message' => 'Search criteria is required']);
            return;
        }
        
        $search_id = $this->save_search($user_id, $search_data);
        
        if ($search_id) {
            wp_send_json_success([
                'message' => 'Search saved successfully!',
                'search_id' => $search_id
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to save search']);
        }
    }
    
    /**
     * AJAX handler for getting saved searches
     */
    public function ajax_get_saved_searches(): void {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please login to view saved searches']);
            return;
        }
        
        $user_id = get_current_user_id();
        $searches = $this->get_user_searches($user_id);
        
        wp_send_json_success([
            'searches' => $searches,
            'total_count' => count($searches)
        ]);
    }
    
    /**
     * Render saved searches shortcode
     */
    public function render_saved_searches_shortcode($atts): string {
        if (!is_user_logged_in()) {
            return '<p>Please <a href="' . wp_login_url(get_permalink()) . '">login</a> to view your saved searches.</p>';
        }
        
        $user_id = get_current_user_id();
        $searches = $this->get_user_searches($user_id);
        
        if (empty($searches)) {
            return '<p>You haven\'t saved any searches yet. <a href="' . home_url('/listings') . '">Browse properties</a> and save your search to get alerts for new matches.</p>';
        }
        
        ob_start();
        ?>
        <div class="saved-searches-list">
            <h3>Your Saved Searches</h3>
            <?php foreach ($searches as $search): ?>
                <div class="saved-search-item" data-search-id="<?php echo esc_attr($search['id']); ?>">
                    <div class="search-header">
                        <h4><?php echo esc_html($search['search_name']); ?></h4>
                        <div class="search-actions">
                            <button type="button" class="btn btn-sm toggle-search-active" 
                                    data-active="<?php echo $search['is_active'] ? '1' : '0'; ?>">
                                <?php echo $search['is_active'] ? 'Active' : 'Paused'; ?>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-search">Delete</button>
                        </div>
                    </div>
                    
                    <div class="search-details">
                        <p><strong>Frequency:</strong> <?php echo ucfirst($search['email_frequency']); ?> alerts</p>
                        <p><strong>Created:</strong> <?php echo date('M j, Y', strtotime($search['created_at'])); ?></p>
                        <?php if ($search['last_sent']): ?>
                            <p><strong>Last Alert:</strong> <?php echo date('M j, Y', strtotime($search['last_sent'])); ?></p>
                        <?php endif; ?>
                        <p><strong>Total Alerts:</strong> <?php echo intval($search['total_sent']); ?></p>
                    </div>
                    
                    <div class="search-criteria">
                        <strong>Search Criteria:</strong>
                        <ul>
                            <?php foreach ($search['criteria'] as $key => $value): ?>
                                <?php if (!empty($value)): ?>
                                    <li><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?>: <?php echo esc_html($value); ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}