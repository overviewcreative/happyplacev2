<?php
/**
 * Agent User Synchronization System
 * 
 * Automatically creates and syncs WordPress users for agent posts
 * Maintains bidirectional relationship between agent posts and users
 */

class HPH_Agent_User_Sync {
    
    /**
     * Initialize the sync system
     */
    public function init() {
        // Hook into agent post save/update
        add_action('save_post_agent', [$this, 'sync_agent_to_user'], 10, 3);
        
        // Hook into user profile updates
        add_action('profile_update', [$this, 'sync_user_to_agent'], 10, 2);
        
        // Add admin actions
        add_action('admin_init', [$this, 'register_admin_actions']);
        
        // Add bulk action for existing agents
        add_filter('bulk_actions-edit-agent', [$this, 'add_bulk_sync_action']);
        add_filter('handle_bulk_actions-edit-agent', [$this, 'handle_bulk_sync_action'], 10, 3);
        
        // Add agent post meta box
        add_action('add_meta_boxes', [$this, 'add_user_sync_meta_box']);
        
        // Add user profile fields
        add_action('show_user_profile', [$this, 'add_agent_sync_fields']);
        add_action('edit_user_profile', [$this, 'add_agent_sync_fields']);
    }

    /**
     * Sync agent post to WordPress user
     */
    public function sync_agent_to_user($post_id, $post, $update) {
        // Skip if this is an autosave or revision
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        // Skip if post is not published
        if ($post->post_status !== 'publish') {
            return;
        }

        // Get agent data
        $agent_data = $this->get_agent_data($post_id);
        
        // Check if user already exists
        $existing_user_id = get_post_meta($post_id, '_synced_user_id', true);
        
        if ($existing_user_id && get_user_by('id', $existing_user_id)) {
            // Update existing user
            $this->update_user_from_agent($existing_user_id, $agent_data, $post_id);
        } else {
            // Create new user
            $user_id = $this->create_user_from_agent($agent_data, $post_id);
            if ($user_id && !is_wp_error($user_id)) {
                update_post_meta($post_id, '_synced_user_id', $user_id);
                update_user_meta($user_id, '_synced_agent_id', $post_id);
            }
        }
    }

    /**
     * Get agent data from post
     */
    private function get_agent_data($post_id) {
        $post = get_post($post_id);
        
        return [
            'display_name' => $post->post_title,
            'first_name' => get_field('first_name', $post_id) ?: '',
            'last_name' => get_field('last_name', $post_id) ?: '',
            'email' => get_field('email', $post_id) ?: '',
            'phone' => get_field('phone', $post_id) ?: '',
            'bio' => get_field('bio', $post_id) ?: $post->post_content,
            'license_number' => get_field('license_number', $post_id) ?: '',
            'specialties' => get_field('specialties', $post_id) ?: '',
            'website' => get_field('website', $post_id) ?: '',
            'social_media' => get_field('social_media', $post_id) ?: [],
        ];
    }

    /**
     * Create WordPress user from agent data
     */
    private function create_user_from_agent($agent_data, $post_id) {
        // Generate username from name or email
        $username = $this->generate_username($agent_data);
        
        // Use email if available, otherwise generate placeholder
        $email = $agent_data['email'];
        if (empty($email) || !is_email($email)) {
            $email = $username . '@placeholder.local';
        }

        // Check if email already exists
        if (email_exists($email)) {
            // If email exists, try to link to existing user
            $existing_user = get_user_by('email', $email);
            if ($existing_user && !get_user_meta($existing_user->ID, '_synced_agent_id', true)) {
                update_user_meta($existing_user->ID, '_synced_agent_id', $post_id);
                return $existing_user->ID;
            }
            return false;
        }

        // Generate secure password
        $password = wp_generate_password(16, true, true);

        // Create user
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            error_log('Agent User Sync Error: ' . $user_id->get_error_message());
            return false;
        }

        // Set user role to agent
        $user = get_user_by('id', $user_id);
        $user->set_role('agent');

        // Update user meta with agent data
        $this->update_user_meta_from_agent($user_id, $agent_data);

        // Log the creation
        error_log("Created user {$user_id} for agent post {$post_id}");

        return $user_id;
    }

    /**
     * Update existing user from agent data
     */
    private function update_user_from_agent($user_id, $agent_data, $post_id) {
        // Update user data
        $user_data = [
            'ID' => $user_id,
            'display_name' => $agent_data['display_name'],
        ];

        // Only update email if it's valid and not a placeholder
        if (!empty($agent_data['email']) && is_email($agent_data['email']) && 
            !str_ends_with($agent_data['email'], '@placeholder.local')) {
            $user_data['user_email'] = $agent_data['email'];
        }

        wp_update_user($user_data);

        // Update user meta
        $this->update_user_meta_from_agent($user_id, $agent_data);
    }

    /**
     * Update user meta from agent data
     */
    private function update_user_meta_from_agent($user_id, $agent_data) {
        update_user_meta($user_id, 'first_name', $agent_data['first_name']);
        update_user_meta($user_id, 'last_name', $agent_data['last_name']);
        update_user_meta($user_id, 'description', $agent_data['bio']);
        update_user_meta($user_id, 'agent_phone', $agent_data['phone']);
        update_user_meta($user_id, 'agent_license_number', $agent_data['license_number']);
        update_user_meta($user_id, 'agent_specialties', $agent_data['specialties']);
        update_user_meta($user_id, 'agent_website', $agent_data['website']);
        update_user_meta($user_id, 'agent_social_media', $agent_data['social_media']);
    }

    /**
     * Generate unique username
     */
    private function generate_username($agent_data) {
        $base_username = '';
        
        // Try first name + last name
        if (!empty($agent_data['first_name']) && !empty($agent_data['last_name'])) {
            $base_username = strtolower($agent_data['first_name'] . '.' . $agent_data['last_name']);
        }
        // Try email prefix
        elseif (!empty($agent_data['email'])) {
            $base_username = strtolower(explode('@', $agent_data['email'])[0]);
        }
        // Try display name
        else {
            $base_username = strtolower(str_replace(' ', '.', $agent_data['display_name']));
        }

        // Sanitize username
        $base_username = sanitize_user($base_username, true);
        
        // Make sure it's unique
        $username = $base_username;
        $counter = 1;
        
        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Sync user updates back to agent post
     */
    public function sync_user_to_agent($user_id, $old_user_data) {
        $agent_id = get_user_meta($user_id, '_synced_agent_id', true);
        
        if (!$agent_id || !get_post($agent_id)) {
            return;
        }

        $user = get_user_by('id', $user_id);
        
        // Update agent post title if display name changed
        if ($user->display_name !== $old_user_data->display_name) {
            wp_update_post([
                'ID' => $agent_id,
                'post_title' => $user->display_name
            ]);
        }

        // Update ACF fields if they exist and user meta has changed
        $this->sync_user_meta_to_agent_fields($user_id, $agent_id);
    }

    /**
     * Sync user meta to agent ACF fields
     */
    private function sync_user_meta_to_agent_fields($user_id, $agent_id) {
        $field_mappings = [
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'user_email' => 'email',
            'description' => 'bio',
            'agent_phone' => 'phone',
            'agent_license_number' => 'license_number',
            'agent_specialties' => 'specialties',
            'agent_website' => 'website',
            'agent_social_media' => 'social_media',
        ];

        foreach ($field_mappings as $user_meta_key => $acf_field_key) {
            if ($user_meta_key === 'user_email') {
                $user = get_user_by('id', $user_id);
                $value = $user->user_email;
            } else {
                $value = get_user_meta($user_id, $user_meta_key, true);
            }

            if ($value && !str_ends_with($value, '@placeholder.local')) {
                update_field($acf_field_key, $value, $agent_id);
            }
        }
    }

    /**
     * Bulk sync all existing agents
     */
    public function bulk_sync_all_agents() {
        $agents = get_posts([
            'post_type' => 'agent',
            'post_status' => 'publish',
            'numberposts' => -1
        ]);

        $synced = 0;
        $errors = 0;

        foreach ($agents as $agent) {
            $existing_user_id = get_post_meta($agent->ID, '_synced_user_id', true);
            
            if (!$existing_user_id || !get_user_by('id', $existing_user_id)) {
                $agent_data = $this->get_agent_data($agent->ID);
                $user_id = $this->create_user_from_agent($agent_data, $agent->ID);
                
                if ($user_id && !is_wp_error($user_id)) {
                    update_post_meta($agent->ID, '_synced_user_id', $user_id);
                    update_user_meta($user_id, '_synced_agent_id', $agent->ID);
                    $synced++;
                } else {
                    $errors++;
                }
            }
        }

        return ['synced' => $synced, 'errors' => $errors];
    }

    /**
     * Register admin actions
     */
    public function register_admin_actions() {
        if (isset($_GET['action']) && $_GET['action'] === 'sync_all_agents' && current_user_can('manage_options')) {
            $results = $this->bulk_sync_all_agents();
            
            $message = sprintf(
                'Agent sync completed: %d agents synced, %d errors.',
                $results['synced'],
                $results['errors']
            );
            
            add_action('admin_notices', function() use ($message) {
                echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
            });
        }
    }

    /**
     * Add bulk sync action
     */
    public function add_bulk_sync_action($actions) {
        $actions['sync_to_users'] = 'Sync to Users';
        return $actions;
    }

    /**
     * Handle bulk sync action
     */
    public function handle_bulk_sync_action($redirect_to, $doaction, $post_ids) {
        if ($doaction !== 'sync_to_users') {
            return $redirect_to;
        }

        $synced = 0;
        foreach ($post_ids as $post_id) {
            $agent_data = $this->get_agent_data($post_id);
            $user_id = $this->create_user_from_agent($agent_data, $post_id);
            
            if ($user_id && !is_wp_error($user_id)) {
                update_post_meta($post_id, '_synced_user_id', $user_id);
                update_user_meta($user_id, '_synced_agent_id', $post_id);
                $synced++;
            }
        }

        $redirect_to = add_query_arg('bulk_synced', $synced, $redirect_to);
        return $redirect_to;
    }

    /**
     * Add meta box to agent edit screen
     */
    public function add_user_sync_meta_box() {
        add_meta_box(
            'agent_user_sync',
            'User Account Sync',
            [$this, 'render_user_sync_meta_box'],
            'agent',
            'side',
            'high'
        );
    }

    /**
     * Render user sync meta box
     */
    public function render_user_sync_meta_box($post) {
        $user_id = get_post_meta($post->ID, '_synced_user_id', true);
        
        if ($user_id && ($user = get_user_by('id', $user_id))) {
            echo '<p><strong>Synced User:</strong></p>';
            echo '<p><a href="' . get_edit_user_link($user_id) . '">' . esc_html($user->display_name) . '</a></p>';
            echo '<p><strong>Email:</strong> ' . esc_html($user->user_email) . '</p>';
            echo '<p><strong>Role:</strong> ' . implode(', ', $user->roles) . '</p>';
        } else {
            echo '<p>No user account synced.</p>';
            echo '<p><em>User will be created automatically when agent is published.</em></p>';
        }
    }

    /**
     * Add agent sync fields to user profile
     */
    public function add_agent_sync_fields($user) {
        $agent_id = get_user_meta($user->ID, '_synced_agent_id', true);
        
        echo '<h3>Agent Profile Sync</h3>';
        echo '<table class="form-table">';
        
        if ($agent_id && ($agent_post = get_post($agent_id))) {
            echo '<tr>';
            echo '<th><label>Synced Agent Post</label></th>';
            echo '<td>';
            echo '<a href="' . get_edit_post_link($agent_id) . '">' . esc_html($agent_post->post_title) . '</a>';
            echo '<p class="description">This user is automatically synced with the agent post.</p>';
            echo '</td>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<th><label>Agent Profile</label></th>';
            echo '<td>';
            echo '<p class="description">This user is not linked to an agent post.</p>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }
}

// Initialize the sync system
function hph_init_agent_user_sync() {
    $sync = new HPH_Agent_User_Sync();
    $sync->init();
}
add_action('init', 'hph_init_agent_user_sync');

// Admin utility function to sync all agents
function hph_sync_all_agents_admin_link() {
    if (current_user_can('manage_options')) {
        add_action('admin_notices', function() {
            $url = add_query_arg(['action' => 'sync_all_agents'], admin_url('edit.php?post_type=agent'));
            echo '<div class="notice notice-info">';
            echo '<p><strong>Agent User Sync:</strong> <a href="' . esc_url($url) . '" class="button">Sync All Existing Agents to Users</a></p>';
            echo '</div>';
        });
    }
}
add_action('admin_init', 'hph_sync_all_agents_admin_link');
