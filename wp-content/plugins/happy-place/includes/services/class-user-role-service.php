<?php
/**
 * User Role Manager - Clean up and manage custom user roles
 * 
 * NOTE: Automatic agent post creation has been DISABLED to prevent conflicts.
 * Manual agent sync can still be performed through the Agent Service if needed.
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class UserRoleService extends Service {
    
    protected string $name = 'user_role_service';
    protected string $version = '4.0.0';
    
    /**
     * Define the roles we want to keep and their capabilities
     */
    private array $custom_roles = [
        'agent' => [
            'display_name' => 'Real Estate Agent',
            'capabilities' => [
                'read' => true,
                'edit_posts' => true,
                'edit_published_posts' => true,
                'publish_posts' => true,
                'delete_posts' => true,
                'upload_files' => true,
                'edit_listings' => true,
                'edit_published_listings' => true,
                'publish_listings' => true,
                'delete_listings' => true,
                'manage_leads' => true,
                'view_agent_stats' => true,
                'edit_own_agent_profile' => true
            ],
            'connects_to' => 'agent' // CPT connection
        ],
        'lead' => [
            'display_name' => 'Lead',
            'capabilities' => [
                'read' => true,
                'view_listings' => true,
                'save_favorites' => true,
                'view_own_profile' => true
            ],
            'connects_to' => 'leads_table' // Database table connection
        ],
        'staff' => [
            'display_name' => 'Staff Member',
            'capabilities' => [
                'read' => true,
                'edit_posts' => true,
                'upload_files' => true,
                'edit_listings' => true,
                'manage_leads' => true,
                'view_analytics' => true,
                'edit_own_staff_profile' => true
            ],
            'connects_to' => 'staff' // CPT connection
        ],
        'admin' => [
            'display_name' => 'Happy Place Admin',
            'capabilities' => [
                'read' => true,
                'edit_posts' => true,
                'edit_others_posts' => true,
                'edit_published_posts' => true,
                'publish_posts' => true,
                'delete_posts' => true,
                'delete_others_posts' => true,
                'delete_published_posts' => true,
                'edit_pages' => true,
                'edit_others_pages' => true,
                'edit_published_pages' => true,
                'publish_pages' => true,
                'delete_pages' => true,
                'delete_others_pages' => true,
                'delete_published_pages' => true,
                'manage_categories' => true,
                'manage_links' => true,
                'moderate_comments' => true,
                'upload_files' => true,
                'import' => true,
                'unfiltered_html' => true,
                'edit_comments' => true,
                'edit_others_comments' => true,
                'edit_published_comments' => true,
                'delete_comments' => true,
                'delete_others_comments' => true,
                'delete_published_comments' => true,
                'manage_happy_place' => true,
                'edit_listings' => true,
                'edit_published_listings' => true,
                'edit_others_listings' => true,
                'publish_listings' => true,
                'delete_listings' => true,
                'delete_others_listings' => true,
                'delete_published_listings' => true,
                'manage_agents' => true,
                'manage_leads' => true,
                'manage_staff' => true,
                'view_all_analytics' => true,
                'export_data' => true,
                'manage_integrations' => true
            ],
            'connects_to' => null // No specific CPT connection
        ]
    ];
    
    /**
     * Roles to remove (legacy/unwanted roles)
     */
    private array $roles_to_remove = [
        'real_estate_agent', // Duplicate of agent
        'broker', // Legacy role
        'client', // Should be lead instead
        'subscriber', // WordPress default, not needed for real estate
        'contributor', // WordPress default, not needed for real estate
        'author' // WordPress default, not needed for real estate (unless specifically needed)
    ];
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        // Clean up roles on init
        add_action('init', [$this, 'manage_roles'], 5); // Early priority
        
        // Handle user registration
        add_action('user_register', [$this, 'handle_new_user_registration']);
        
        // Handle role changes
        add_action('set_user_role', [$this, 'handle_role_change'], 10, 3);
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_init', [$this, 'admin_init']);
            add_filter('editable_roles', [$this, 'filter_editable_roles']);
        }
        
        $this->initialized = true;
        $this->log('User Role Service initialized successfully');
    }
    
    /**
     * Manage roles - create custom ones and remove unwanted ones
     */
    public function manage_roles(): void {
        // Remove unwanted roles first
        $this->remove_legacy_roles();
        
        // Create/update our custom roles
        $this->create_custom_roles();
        
        // Update administrator capabilities
        $this->update_administrator_capabilities();
        
        $this->log('User roles managed successfully');
    }
    
    /**
     * Remove legacy/unwanted roles
     */
    private function remove_legacy_roles(): void {
        foreach ($this->roles_to_remove as $role_key) {
            $role = get_role($role_key);
            if ($role) {
                // Move users from this role to a default role before removing
                $this->migrate_users_from_role($role_key);
                
                // Remove the role
                remove_role($role_key);
                $this->log("Removed legacy role: {$role_key}");
            }
        }
    }
    
    /**
     * Migrate users from a role that's being removed
     */
    private function migrate_users_from_role(string $old_role): void {
        $users = get_users(['role' => $old_role]);
        
        foreach ($users as $user) {
            // Determine new role based on old role
            $new_role = $this->determine_migration_role($old_role);
            
            // Update user role
            $user->set_role($new_role);
            
            $this->log("Migrated user {$user->user_login} from {$old_role} to {$new_role}");
        }
    }
    
    /**
     * Determine what role to migrate a user to
     */
    private function determine_migration_role(string $old_role): string {
        $migration_map = [
            'real_estate_agent' => 'agent',
            'broker' => 'agent',
            'client' => 'lead',
            'subscriber' => 'lead',
            'contributor' => 'staff',
            'author' => 'staff'
        ];
        
        return $migration_map[$old_role] ?? 'lead';
    }
    
    /**
     * Create custom roles
     */
    private function create_custom_roles(): void {
        foreach ($this->custom_roles as $role_key => $role_config) {
            $existing_role = get_role($role_key);
            
            if (!$existing_role) {
                // Create new role
                add_role(
                    $role_key,
                    $role_config['display_name'],
                    $role_config['capabilities']
                );
                $this->log("Created role: {$role_key}");
            } else {
                // Update existing role capabilities
                foreach ($role_config['capabilities'] as $cap => $grant) {
                    if ($grant) {
                        $existing_role->add_cap($cap);
                    } else {
                        $existing_role->remove_cap($cap);
                    }
                }
                $this->log("Updated role capabilities: {$role_key}");
            }
        }
    }
    
    /**
     * Update administrator capabilities to manage our custom roles
     */
    private function update_administrator_capabilities(): void {
        $admin_role = get_role('administrator');
        if (!$admin_role) {
            return;
        }
        
        // Add capabilities for managing all our custom content types
        $admin_capabilities = [
            'manage_happy_place',
            'edit_agents',
            'edit_published_agents',
            'edit_others_agents',
            'publish_agents',
            'delete_agents',
            'delete_others_agents',
            'delete_published_agents',
            'edit_staff',
            'edit_published_staff',
            'edit_others_staff',
            'publish_staff',
            'delete_staff',
            'delete_others_staff',
            'delete_published_staff',
            'manage_agents',
            'manage_leads',
            'manage_staff',
            'view_all_analytics',
            'export_data',
            'manage_integrations'
        ];
        
        foreach ($admin_capabilities as $cap) {
            $admin_role->add_cap($cap);
        }
        
        $this->log('Updated administrator capabilities');
    }
    
    /**
     * Handle new user registration
     */
    public function handle_new_user_registration(int $user_id): void {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }
        
        // Default new users to 'lead' role if no role is set
        if (empty($user->roles)) {
            $user->set_role('lead');
            $this->log("Set default role 'lead' for new user: {$user->user_login}");
        }
        
        // Create corresponding post/record based on role
        $this->create_user_connections($user_id, $user->roles[0] ?? 'lead');
    }
    
    /**
     * Handle role changes
     */
    public function handle_role_change(int $user_id, string $role, array $old_roles): void {
        // Create corresponding post/record for new role
        $this->create_user_connections($user_id, $role);
        
        // Clean up old connections if needed
        foreach ($old_roles as $old_role) {
            if ($old_role !== $role) {
                $this->cleanup_user_connections($user_id, $old_role);
            }
        }
    }
    
    /**
     * Create connections between users and post types/tables
     */
    private function create_user_connections(int $user_id, string $role): void {
        if (!isset($this->custom_roles[$role])) {
            return;
        }
        
        $connection_type = $this->custom_roles[$role]['connects_to'];
        
        switch ($connection_type) {
            case 'agent':
                // DISABLED: Automatic agent post creation causes too many conflicts
                // Manual sync can still be done through Agent Service if needed
                // if (class_exists('HappyPlace\\Services\\AgentService')) {
                //     $agent_service = new \HappyPlace\Services\AgentService();
                //     if (method_exists($agent_service, 'create_agent_post_for_user')) {
                //         $agent_service->create_agent_post_for_user($user_id);
                //     }
                // }
                break;
                
            case 'staff':
                // Create staff post if it doesn't exist
                $this->create_staff_post_for_user($user_id);
                break;
                
            case 'leads_table':
                // Add entry to leads table if needed
                $this->create_lead_record_for_user($user_id);
                break;
        }
    }
    
    /**
     * Create staff post for user
     */
    private function create_staff_post_for_user(int $user_id): void {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }
        
        // Check if staff post already exists
        $existing_posts = get_posts([
            'post_type' => 'staff',
            'meta_query' => [
                [
                    'key' => 'staff_user_id',
                    'value' => $user_id,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1
        ]);
        
        if (!empty($existing_posts)) {
            return; // Already exists
        }
        
        // Create staff post
        $post_data = [
            'post_type' => 'staff',
            'post_title' => $user->display_name ?: $user->user_login,
            'post_status' => 'publish',
            'post_author' => $user_id,
            'meta_input' => [
                'staff_user_id' => $user_id,
                'staff_email' => $user->user_email,
                'staff_phone' => get_user_meta($user_id, 'phone', true),
                'staff_position' => 'Staff Member',
                'employment_status' => 'active'
            ]
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            $this->log("Created staff post for user: {$user->user_login}");
        }
    }
    
    /**
     * Create lead record for user
     */
    private function create_lead_record_for_user(int $user_id): void {
        global $wpdb;
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }
        
        $leads_table = $wpdb->prefix . 'hp_leads';
        
        // Check if lead record already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$leads_table} WHERE email = %s",
            $user->user_email
        ));
        
        if ($existing) {
            return; // Already exists
        }
        
        // Create lead record
        $wpdb->insert(
            $leads_table,
            [
                'first_name' => get_user_meta($user_id, 'first_name', true) ?: '',
                'last_name' => get_user_meta($user_id, 'last_name', true) ?: '',
                'email' => $user->user_email,
                'phone' => get_user_meta($user_id, 'phone', true) ?: '',
                'source' => 'user_registration',
                'status' => 'registered',
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        if ($wpdb->insert_id) {
            $this->log("Created lead record for user: {$user->user_login}");
        }
    }
    
    /**
     * Clean up connections when role changes
     */
    private function cleanup_user_connections(int $user_id, string $old_role): void {
        // This could be implemented to clean up old connections
        // For now, we'll leave old posts/records in place for data integrity
        $this->log("Role changed for user {$user_id} from {$old_role}");
    }
    
    /**
     * Admin init
     */
    public function admin_init(): void {
        // Add admin notices if needed
        if (get_transient('hp_roles_updated')) {
            add_action('admin_notices', [$this, 'show_roles_updated_notice']);
            delete_transient('hp_roles_updated');
        }
    }
    
    /**
     * Filter editable roles to only show our custom roles
     */
    public function filter_editable_roles(array $roles): array {
        // Keep administrator and our custom roles
        $allowed_roles = array_keys($this->custom_roles);
        $allowed_roles[] = 'administrator';
        
        return array_intersect_key($roles, array_flip($allowed_roles));
    }
    
    /**
     * Show admin notice when roles are updated
     */
    public function show_roles_updated_notice(): void {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Happy Place user roles have been updated successfully.', 'happy-place'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Get custom roles configuration
     */
    public function get_custom_roles(): array {
        return $this->custom_roles;
    }
    
    /**
     * Force role cleanup (can be called manually)
     */
    public function force_role_cleanup(): void {
        $this->manage_roles();
        set_transient('hp_roles_updated', true, 300); // 5 minutes
        $this->log('Forced role cleanup completed');
    }
}
