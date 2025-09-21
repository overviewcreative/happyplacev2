<?php
/**
 * Agent Service - Complete Agent Management
 * 
 * Manages agent profiles, performance tracking, and user integration
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class AgentService extends Service {
    
    protected string $name = 'agent_service';
    protected string $version = '4.0.0';
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        // Agent-User synchronization hooks (disabled until user system requirements are finalized)
        // add_action('user_register', [$this, 'maybe_create_agent_post']);
        // add_action('profile_update', [$this, 'sync_user_to_agent_post']);
        // add_action('save_post_agent', [$this, 'sync_agent_post_to_user'], 10, 2);
        
        // Add agent capabilities
        add_action('init', [$this, 'add_agent_capabilities']);
        
        // Register admin menu
        if (is_admin()) {
            add_action('admin_menu', [$this, 'register_admin_menu']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        }
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
        
        // Add user columns
        add_filter('manage_users_columns', [$this, 'add_user_columns']);
        add_filter('manage_users_custom_column', [$this, 'display_user_columns'], 10, 3);
        
        $this->initialized = true;
        $this->log('Agent Service initialized successfully');
    }
    
    /**
     * Maybe create agent post when user registers
     */
    public function maybe_create_agent_post($user_id): void {
        $user = get_user_by('id', $user_id);
        
        // Only create agent post for users with agent role
        if (!$user || !in_array('agent', $user->roles)) {
            return;
        }
        
        $this->create_agent_post_for_user($user_id);
    }
    
    /**
     * Create agent post for user
     */
    public function create_agent_post_for_user(int $user_id): int {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return 0;
        }
        
        // Check if agent post already exists
        $existing_post = $this->get_agent_post_by_user($user_id);
        if ($existing_post) {
            return $existing_post->ID;
        }
        
        // Create agent post
        $post_data = [
            'post_type' => 'agent',
            'post_title' => $user->display_name ?: $user->user_login,
            'post_status' => 'publish',
            'post_author' => $user_id,
            'meta_input' => [
                'agent_user_id' => $user_id,
                'agent_email' => $user->user_email,
                'agent_phone' => get_user_meta($user_id, 'phone', true),
                'agent_bio' => get_user_meta($user_id, 'description', true),
                'agent_license_number' => get_user_meta($user_id, 'license_number', true),
                'agent_start_date' => get_user_meta($user_id, 'start_date', true) ?: date('Y-m-d'),
                'agent_status' => 'active'
            ]
        ];
        
        $agent_post_id = wp_insert_post($post_data);
        
        if ($agent_post_id && !is_wp_error($agent_post_id)) {
            // Store reverse relationship
            update_user_meta($user_id, 'agent_post_id', $agent_post_id);
            
            // Initialize agent statistics
            $this->initialize_agent_stats($agent_post_id);
            
            do_action('hp_agent_post_created', $agent_post_id, $user_id);
            
            $this->log("Created agent post {$agent_post_id} for user {$user_id}");
        }
        
        return $agent_post_id;
    }
    
    /**
     * Get agent post by user ID
     */
    public function get_agent_post_by_user(int $user_id): ?\WP_Post {
        // Check user meta first
        $agent_post_id = get_user_meta($user_id, 'agent_post_id', true);
        if ($agent_post_id) {
            $post = get_post($agent_post_id);
            if ($post && $post->post_type === 'agent') {
                return $post;
            }
        }
        
        // Search by meta query
        $posts = get_posts([
            'post_type' => 'agent',
            'meta_query' => [
                [
                    'key' => 'agent_user_id',
                    'value' => $user_id,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1
        ]);
        
        if (!empty($posts)) {
            // Update user meta for future reference
            update_user_meta($user_id, 'agent_post_id', $posts[0]->ID);
            return $posts[0];
        }
        
        return null;
    }
    
    /**
     * Sync user data to agent post
     */
    public function sync_user_to_agent_post(int $user_id): void {
        $agent_post = $this->get_agent_post_by_user($user_id);
        if (!$agent_post) {
            return;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }
        
        // Update post title
        wp_update_post([
            'ID' => $agent_post->ID,
            'post_title' => $user->display_name ?: $user->user_login
        ]);
        
        // Update meta fields
        update_post_meta($agent_post->ID, 'agent_email', $user->user_email);
        
        // Sync additional user meta
        $sync_fields = [
            'phone' => 'agent_phone',
            'description' => 'agent_bio',
            'license_number' => 'agent_license_number',
            'start_date' => 'agent_start_date'
        ];
        
        foreach ($sync_fields as $user_meta_key => $post_meta_key) {
            $value = get_user_meta($user_id, $user_meta_key, true);
            if ($value) {
                update_post_meta($agent_post->ID, $post_meta_key, $value);
            }
        }
        
        do_action('hp_agent_user_synced', $agent_post->ID, $user_id);
    }
    
    /**
     * Sync agent post data to user
     */
    public function sync_agent_post_to_user(int $post_id, \WP_Post $post): void {
        if ($post->post_type !== 'agent') {
            return;
        }
        
        $user_id = get_post_meta($post_id, 'agent_user_id', true);
        if (!$user_id) {
            return;
        }
        
        // Update user display name if changed
        $user = get_user_by('id', $user_id);
        if ($user && $user->display_name !== $post->post_title) {
            wp_update_user([
                'ID' => $user_id,
                'display_name' => $post->post_title
            ]);
        }
        
        // Sync meta fields back to user
        $sync_fields = [
            'agent_phone' => 'phone',
            'agent_bio' => 'description',
            'agent_license_number' => 'license_number',
            'agent_start_date' => 'start_date'
        ];
        
        foreach ($sync_fields as $post_meta_key => $user_meta_key) {
            $value = get_post_meta($post_id, $post_meta_key, true);
            if ($value) {
                update_user_meta($user_id, $user_meta_key, $value);
            }
        }
        
        do_action('hp_agent_post_synced', $post_id, $user_id);
    }
    
    /**
     * Initialize agent statistics
     */
    public function initialize_agent_stats(int $agent_post_id): void {
        $stats = [
            'total_listings' => 0,
            'active_listings' => 0,
            'sold_listings' => 0,
            'pending_listings' => 0,
            'total_leads' => 0,
            'converted_leads' => 0,
            'total_transactions' => 0,
            'total_volume' => 0,
            'ytd_volume' => 0,
            'avg_days_on_market' => 0,
            'conversion_rate' => 0,
            'last_calculated' => current_time('mysql')
        ];
        
        foreach ($stats as $key => $value) {
            update_post_meta($agent_post_id, "agent_stat_{$key}", $value);
        }
    }
    
    /**
     * Calculate agent statistics
     */
    public function calculate_agent_stats(int $agent_post_id): array {
        $user_id = get_post_meta($agent_post_id, 'agent_user_id', true);
        if (!$user_id) {
            return [];
        }
        
        global $wpdb;
        
        // Get listing statistics
        $listing_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_listings,
                SUM(CASE WHEN post_status = 'publish' THEN 1 ELSE 0 END) as active_listings,
                SUM(CASE WHEN meta_value = 'sold' THEN 1 ELSE 0 END) as sold_listings,
                SUM(CASE WHEN meta_value = 'pending' THEN 1 ELSE 0 END) as pending_listings,
                AVG(CASE WHEN meta_value = 'sold' 
                    THEN DATEDIFF(STR_TO_DATE(sold_date.meta_value, '%%Y-%%m-%%d'), post_date) 
                    ELSE NULL END) as avg_days_on_market
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'listing_agent'
            LEFT JOIN {$wpdb->postmeta} status ON p.ID = status.post_id AND status.meta_key = 'listing_status'
            LEFT JOIN {$wpdb->postmeta} sold_date ON p.ID = sold_date.post_id AND sold_date.meta_key = 'sold_date'
            WHERE p.post_type = 'listing' 
            AND pm.meta_value = %s
        ", $user_id), ARRAY_A);
        
        // Get lead statistics
        $lead_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_leads,
                SUM(CASE WHEN status IN ('closed_won', 'qualified') THEN 1 ELSE 0 END) as converted_leads
            FROM {$wpdb->prefix}hp_leads
            WHERE assigned_to = %s
        ", $user_id), ARRAY_A);
        
        // Get transaction volume
        $volume_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_transactions,
                COALESCE(SUM(CAST(sale_price.meta_value AS DECIMAL(15,2))), 0) as total_volume,
                COALESCE(SUM(CASE WHEN YEAR(p.post_date) = YEAR(NOW()) 
                    THEN CAST(sale_price.meta_value AS DECIMAL(15,2)) 
                    ELSE 0 END), 0) as ytd_volume
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} agent_meta ON p.ID = agent_meta.post_id AND agent_meta.meta_key = 'transaction_agent'
            LEFT JOIN {$wpdb->postmeta} sale_price ON p.ID = sale_price.post_id AND sale_price.meta_key = 'sale_price'
            WHERE p.post_type = 'transaction' 
            AND agent_meta.meta_value = %s
            AND p.post_status = 'publish'
        ", $user_id), ARRAY_A);
        
        // Calculate conversion rate
        $conversion_rate = 0;
        if ($lead_stats['total_leads'] > 0) {
            $conversion_rate = ($lead_stats['converted_leads'] / $lead_stats['total_leads']) * 100;
        }
        
        // Compile statistics
        $stats = array_merge($listing_stats ?: [], $lead_stats ?: [], $volume_stats ?: []);
        $stats['conversion_rate'] = round($conversion_rate, 2);
        $stats['avg_days_on_market'] = round($stats['avg_days_on_market'] ?: 0, 1);
        $stats['last_calculated'] = current_time('mysql');
        
        // Update post meta
        foreach ($stats as $key => $value) {
            update_post_meta($agent_post_id, "agent_stat_{$key}", $value);
        }
        
        do_action('hp_agent_stats_calculated', $agent_post_id, $stats);
        
        return $stats;
    }
    
    /**
     * Get agent statistics
     */
    public function get_agent_stats(int $agent_post_id, bool $force_recalculate = false): array {
        // Check if we need to recalculate
        $last_calculated = get_post_meta($agent_post_id, 'agent_stat_last_calculated', true);
        $needs_recalc = $force_recalculate || !$last_calculated || 
                       strtotime($last_calculated) < strtotime('-1 day');
        
        if ($needs_recalc) {
            return $this->calculate_agent_stats($agent_post_id);
        }
        
        // Return cached stats
        $stat_keys = [
            'total_listings', 'active_listings', 'sold_listings', 'pending_listings',
            'total_leads', 'converted_leads', 'total_transactions', 'total_volume',
            'ytd_volume', 'avg_days_on_market', 'conversion_rate', 'last_calculated'
        ];
        
        $stats = [];
        foreach ($stat_keys as $key) {
            $stats[$key] = get_post_meta($agent_post_id, "agent_stat_{$key}", true);
        }
        
        return $stats;
    }
    
    /**
     * Get agent by user ID
     */
    public function get_agent_by_user(int $user_id): ?array {
        $agent_post = $this->get_agent_post_by_user($user_id);
        if (!$agent_post) {
            return null;
        }
        
        $user = get_user_by('id', $user_id);
        $stats = $this->get_agent_stats($agent_post->ID);
        
        return [
            'post_id' => $agent_post->ID,
            'user_id' => $user_id,
            'name' => $agent_post->post_title,
            'email' => $user->user_email,
            'phone' => get_post_meta($agent_post->ID, 'agent_phone', true),
            'bio' => get_post_meta($agent_post->ID, 'agent_bio', true),
            'license_number' => get_post_meta($agent_post->ID, 'agent_license_number', true),
            'start_date' => get_post_meta($agent_post->ID, 'agent_start_date', true),
            'status' => get_post_meta($agent_post->ID, 'agent_status', true),
            'photo' => get_the_post_thumbnail_url($agent_post->ID, 'medium'),
            'stats' => $stats
        ];
    }
    
    /**
     * Get all agents
     */
    public function get_all_agents(array $args = []): array {
        $defaults = [
            'status' => 'active',
            'orderby' => 'title',
            'order' => 'ASC',
            'posts_per_page' => -1
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = [
            'post_type' => 'agent',
            'post_status' => 'publish',
            'posts_per_page' => $args['posts_per_page'],
            'orderby' => $args['orderby'],
            'order' => $args['order']
        ];
        
        if ($args['status']) {
            $query_args['meta_query'] = [
                [
                    'key' => 'agent_status',
                    'value' => $args['status'],
                    'compare' => '='
                ]
            ];
        }
        
        $posts = get_posts($query_args);
        $agents = [];
        
        foreach ($posts as $post) {
            $user_id = get_post_meta($post->ID, 'agent_user_id', true);
            if ($user_id) {
                $agent = $this->get_agent_by_user($user_id);
                if ($agent) {
                    $agents[] = $agent;
                }
            }
        }
        
        return $agents;
    }
    
    /**
     * Add agent capabilities
     * NOTE: Role creation is now handled by UserRoleService
     */
    public function add_agent_capabilities(): void {
        // Role creation is now handled by UserRoleService
        // This method is kept for backward compatibility but capabilities
        // are managed centrally by the UserRoleService
        
        $this->log('Agent capabilities managed by UserRoleService');
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        add_action('wp_ajax_hp_recalculate_agent_stats', [$this, 'ajax_recalculate_stats']);
        add_action('wp_ajax_hp_update_agent_status', [$this, 'ajax_update_agent_status']);
        add_action('wp_ajax_hp_get_agent_performance', [$this, 'ajax_get_agent_performance']);
    }
    
    /**
     * AJAX: Recalculate agent stats
     */
    public function ajax_recalculate_stats(): void {
        if (!current_user_can('manage_agents')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $agent_post_id = intval($_POST['agent_id'] ?? 0);
        if (!$agent_post_id) {
            wp_send_json_error(['message' => 'Invalid agent ID']);
            return;
        }
        
        $stats = $this->calculate_agent_stats($agent_post_id);
        
        wp_send_json_success([
            'message' => 'Statistics updated successfully',
            'stats' => $stats
        ]);
    }
    
    /**
     * Register admin menu
     */
    public function register_admin_menu(): void {
        add_submenu_page(
            'edit.php?post_type=agent',
            'Agent Performance',
            'Performance',
            'view_agent_stats',
            'agent-performance',
            [$this, 'render_performance_page']
        );
    }
    
    /**
     * Render performance page
     */
    public function render_performance_page(): void {
        $agents = $this->get_all_agents();
        include HP_PLUGIN_DIR . 'templates/admin/agent-performance.php';
    }
    
    /**
     * Add user columns
     */
    public function add_user_columns($columns): array {
        $columns['agent_stats'] = 'Agent Stats';
        return $columns;
    }
    
    /**
     * Display user columns
     */
    public function display_user_columns($output, $column_name, $user_id): string {
        if ($column_name === 'agent_stats') {
            $user = get_user_by('id', $user_id);
            if ($user && in_array('agent', $user->roles)) {
                $agent = $this->get_agent_by_user($user_id);
                if ($agent) {
                    $stats = $agent['stats'];
                    return sprintf(
                        'Listings: %d | Leads: %d | Volume: $%s',
                        $stats['total_listings'] ?? 0,
                        $stats['total_leads'] ?? 0,
                        number_format($stats['ytd_volume'] ?? 0)
                    );
                }
            }
        }
        
        return $output;
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook): void {
        if (strpos($hook, 'agent') === false) {
            return;
        }
        
        wp_enqueue_script(
            'hp-agent-admin',
            HP_ASSETS_URL . 'js/admin/agent-manager.js',
            ['jquery'],
            HP_VERSION,
            true
        );
        
        wp_localize_script('hp-agent-admin', 'hp_agent_admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_agent_admin_nonce')
        ]);
    }
}