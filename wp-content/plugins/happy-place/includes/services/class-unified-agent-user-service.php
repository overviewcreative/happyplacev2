<?php
/**
 * Unified Agent User Service
 *
 * Migrated from theme and enhanced with plugin architecture
 * Manages bidirectional synchronization between agent posts and WordPress users
 *
 * @package HappyPlace\Services
 * @since 4.1.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class UnifiedAgentUserService extends Service {

    protected string $name = 'unified_agent_user_service';
    protected string $version = '4.1.0';

    /**
     * Default sync settings
     */
    private array $sync_settings = [
        'auto_sync_enabled' => false, // Keep disabled by default to prevent conflicts
        'sync_on_agent_save' => false,
        'sync_on_user_update' => true,
        'create_users_for_agents' => false,
        'default_user_role' => 'agent'
    ];

    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }

        // Load sync settings
        $this->load_sync_settings();

        // Hook into user profile updates (safer than auto-creation)
        if ($this->sync_settings['sync_on_user_update']) {
            add_action('profile_update', [$this, 'sync_user_to_agent'], 10, 2);
        }

        // Conditional agent sync (only if enabled)
        if ($this->sync_settings['sync_on_agent_save']) {
            add_action('save_post_agent', [$this, 'sync_agent_to_user'], 10, 3);
        }

        // Add admin interfaces
        if (is_admin()) {
            add_action('admin_init', [$this, 'register_admin_actions']);
            add_filter('bulk_actions-edit-agent', [$this, 'add_bulk_sync_action']);
            add_filter('handle_bulk_actions-edit-agent', [$this, 'handle_bulk_sync_action'], 10, 3);
            add_action('add_meta_boxes', [$this, 'add_user_sync_meta_box']);
            add_action('show_user_profile', [$this, 'add_agent_sync_fields']);
            add_action('edit_user_profile', [$this, 'add_agent_sync_fields']);
        }

        // Register AJAX handlers
        $this->register_ajax_handlers();

        $this->initialized = true;
        $this->log('Unified Agent User Service initialized');
    }

    /**
     * Load sync settings from options
     */
    private function load_sync_settings(): void {
        $stored_settings = get_option('hp_agent_user_sync_settings', []);
        $this->sync_settings = array_merge($this->sync_settings, $stored_settings);
    }

    /**
     * Get agent data from post
     */
    public function get_agent_data(int $post_id): array {
        $post = get_post($post_id);
        if (!$post) {
            return [];
        }

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
            'experience_years' => get_field('experience_years', $post_id) ?: 0,
            'languages' => get_field('languages_spoken', $post_id) ?: '',
            'certifications' => get_field('certifications', $post_id) ?: []
        ];
    }

    /**
     * Sync agent post to WordPress user
     */
    public function sync_agent_to_user(int $post_id, $post, bool $update): void {
        // Skip if disabled or conditions not met
        if (!$this->sync_settings['sync_on_agent_save']) {
            return;
        }

        // Skip if this is an autosave or revision
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        // Skip if post is not published
        if ($post->post_status !== 'publish') {
            return;
        }

        try {
            $agent_data = $this->get_agent_data($post_id);
            $existing_user_id = get_post_meta($post_id, '_synced_user_id', true);

            if ($existing_user_id && get_user_by('id', $existing_user_id)) {
                $this->update_user_from_agent($existing_user_id, $agent_data, $post_id);
            } else if ($this->sync_settings['create_users_for_agents']) {
                $user_id = $this->create_user_from_agent($agent_data, $post_id);
                if ($user_id && !is_wp_error($user_id)) {
                    update_post_meta($post_id, '_synced_user_id', $user_id);
                    update_user_meta($user_id, '_synced_agent_id', $post_id);
                    $this->log("Created user {$user_id} for agent {$post_id}");
                }
            }
        } catch (Exception $e) {
            $this->log('Error syncing agent to user: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Create WordPress user from agent data
     */
    public function create_user_from_agent(array $agent_data, int $post_id) {
        if (empty($agent_data['email']) || !is_email($agent_data['email'])) {
            // Skip creation if no valid email
            $this->log("Skipping user creation for agent {$post_id} - no valid email");
            return false;
        }

        // Check if email already exists
        if (email_exists($agent_data['email'])) {
            $existing_user = get_user_by('email', $agent_data['email']);
            if ($existing_user && !get_user_meta($existing_user->ID, '_synced_agent_id', true)) {
                // Link existing user to agent
                update_user_meta($existing_user->ID, '_synced_agent_id', $post_id);
                update_post_meta($post_id, '_synced_user_id', $existing_user->ID);
                $this->log("Linked existing user {$existing_user->ID} to agent {$post_id}");
                return $existing_user->ID;
            }
            return false;
        }

        // Generate username and password
        $username = $this->generate_unique_username($agent_data);
        $password = wp_generate_password(16, true, true);

        // Create user
        $user_id = wp_create_user($username, $password, $agent_data['email']);

        if (is_wp_error($user_id)) {
            $this->log('Agent User Sync Error: ' . $user_id->get_error_message(), 'error');
            return false;
        }

        // Set user role
        $user = get_user_by('id', $user_id);
        $user->set_role($this->sync_settings['default_user_role']);

        // Update user meta with agent data
        $this->update_user_meta_from_agent($user_id, $agent_data);

        $this->log("Created user {$user_id} for agent {$post_id}");
        return $user_id;
    }

    /**
     * Update existing user from agent data
     */
    public function update_user_from_agent(int $user_id, array $agent_data, int $post_id): void {
        $user_data = [
            'ID' => $user_id,
            'display_name' => $agent_data['display_name'],
        ];

        // Only update email if it's valid and not a placeholder
        if (!empty($agent_data['email']) && is_email($agent_data['email'])) {
            $user_data['user_email'] = $agent_data['email'];
        }

        wp_update_user($user_data);
        $this->update_user_meta_from_agent($user_id, $agent_data);
        $this->log("Updated user {$user_id} from agent {$post_id}");
    }

    /**
     * Update user meta from agent data
     */
    private function update_user_meta_from_agent(int $user_id, array $agent_data): void {
        $meta_mappings = [
            'first_name' => $agent_data['first_name'],
            'last_name' => $agent_data['last_name'],
            'description' => $agent_data['bio'],
            'agent_phone' => $agent_data['phone'],
            'agent_license_number' => $agent_data['license_number'],
            'agent_specialties' => $agent_data['specialties'],
            'agent_website' => $agent_data['website'],
            'agent_social_media' => $agent_data['social_media'],
            'agent_experience_years' => $agent_data['experience_years'],
            'agent_languages' => $agent_data['languages'],
            'agent_certifications' => $agent_data['certifications']
        ];

        foreach ($meta_mappings as $meta_key => $value) {
            if (!empty($value)) {
                update_user_meta($user_id, $meta_key, $value);
            }
        }
    }

    /**
     * Generate unique username
     */
    private function generate_unique_username(array $agent_data): string {
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
    public function sync_user_to_agent(int $user_id, $old_user_data): void {
        $agent_id = get_user_meta($user_id, '_synced_agent_id', true);

        if (!$agent_id || !get_post($agent_id)) {
            return;
        }

        try {
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
            $this->log("Synced user {$user_id} updates to agent {$agent_id}");
        } catch (Exception $e) {
            $this->log('Error syncing user to agent: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Sync user meta to agent ACF fields
     */
    private function sync_user_meta_to_agent_fields(int $user_id, int $agent_id): void {
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
            'agent_experience_years' => 'experience_years',
            'agent_languages' => 'languages_spoken',
            'agent_certifications' => 'certifications'
        ];

        foreach ($field_mappings as $user_meta_key => $acf_field_key) {
            if ($user_meta_key === 'user_email') {
                $user = get_user_by('id', $user_id);
                $value = $user->user_email;
            } else {
                $value = get_user_meta($user_id, $user_meta_key, true);
            }

            if (!empty($value)) {
                update_field($acf_field_key, $value, $agent_id);
            }
        }
    }

    /**
     * Manual sync specific agent to user
     */
    public function manual_sync_agent(int $agent_id): array {
        try {
            $agent_data = $this->get_agent_data($agent_id);
            if (empty($agent_data)) {
                return ['success' => false, 'message' => 'Agent not found'];
            }

            $existing_user_id = get_post_meta($agent_id, '_synced_user_id', true);

            if ($existing_user_id && get_user_by('id', $existing_user_id)) {
                $this->update_user_from_agent($existing_user_id, $agent_data, $agent_id);
                return ['success' => true, 'message' => 'User updated', 'user_id' => $existing_user_id];
            } else {
                $user_id = $this->create_user_from_agent($agent_data, $agent_id);
                if ($user_id && !is_wp_error($user_id)) {
                    update_post_meta($agent_id, '_synced_user_id', $user_id);
                    update_user_meta($user_id, '_synced_agent_id', $agent_id);
                    return ['success' => true, 'message' => 'User created', 'user_id' => $user_id];
                } else {
                    return ['success' => false, 'message' => 'Failed to create user'];
                }
            }
        } catch (Exception $e) {
            $this->log('Manual sync error: ' . $e->getMessage(), 'error');
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Bulk sync all existing agents
     */
    public function bulk_sync_all_agents(): array {
        $agents = get_posts([
            'post_type' => 'agent',
            'post_status' => 'publish',
            'numberposts' => -1
        ]);

        $synced = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($agents as $agent) {
            $existing_user_id = get_post_meta($agent->ID, '_synced_user_id', true);

            if (!$existing_user_id || !get_user_by('id', $existing_user_id)) {
                $result = $this->manual_sync_agent($agent->ID);
                if ($result['success']) {
                    $synced++;
                } else {
                    $errors++;
                }
            } else {
                $skipped++;
            }
        }

        $this->log("Bulk sync completed: {$synced} synced, {$skipped} skipped, {$errors} errors");
        return ['synced' => $synced, 'skipped' => $skipped, 'errors' => $errors];
    }

    /**
     * Register admin actions
     */
    public function register_admin_actions(): void {
        if (isset($_GET['action']) && $_GET['action'] === 'sync_all_agents' && current_user_can('manage_options')) {
            $results = $this->bulk_sync_all_agents();

            $message = sprintf(
                'Agent sync completed: %d agents synced, %d skipped, %d errors.',
                $results['synced'],
                $results['skipped'],
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
    public function add_bulk_sync_action(array $actions): array {
        $actions['sync_to_users'] = 'Sync to Users';
        return $actions;
    }

    /**
     * Handle bulk sync action
     */
    public function handle_bulk_sync_action(string $redirect_to, string $doaction, array $post_ids): string {
        if ($doaction !== 'sync_to_users') {
            return $redirect_to;
        }

        $synced = 0;
        foreach ($post_ids as $post_id) {
            $result = $this->manual_sync_agent($post_id);
            if ($result['success']) {
                $synced++;
            }
        }

        $redirect_to = add_query_arg('bulk_synced', $synced, $redirect_to);
        return $redirect_to;
    }

    /**
     * Add meta box to agent edit screen
     */
    public function add_user_sync_meta_box(): void {
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
    public function render_user_sync_meta_box($post): void {
        $user_id = get_post_meta($post->ID, '_synced_user_id', true);

        if ($user_id && ($user = get_user_by('id', $user_id))) {
            echo '<p><strong>Synced User:</strong></p>';
            echo '<p><a href="' . get_edit_user_link($user_id) . '">' . esc_html($user->display_name) . '</a></p>';
            echo '<p><strong>Email:</strong> ' . esc_html($user->user_email) . '</p>';
            echo '<p><strong>Role:</strong> ' . implode(', ', $user->roles) . '</p>';
            echo '<p><button type="button" class="button" onclick="manualSyncAgent(' . $post->ID . ')">Sync Now</button></p>';
        } else {
            echo '<p>No user account synced.</p>';
            echo '<p><button type="button" class="button button-primary" onclick="manualSyncAgent(' . $post->ID . ')">Create User</button></p>';
        }

        // Add JavaScript for manual sync
        ?>
        <script>
        function manualSyncAgent(agentId) {
            const data = {
                action: 'hp_manual_agent_sync',
                agent_id: agentId,
                nonce: '<?php echo wp_create_nonce('hp_agent_sync'); ?>'
            };

            fetch(ajaxurl, {
                method: 'POST',
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Sync failed: ' + (result.data?.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Sync failed: ' + error.message);
            });
        }
        </script>
        <?php
    }

    /**
     * Add agent sync fields to user profile
     */
    public function add_agent_sync_fields($user): void {
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
            echo '<th><label>Agent Post</label></th>';
            echo '<td>';
            echo '<p class="description">No agent post is synced with this user.</p>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';
    }

    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        add_action('wp_ajax_hp_manual_agent_sync', [$this, 'handle_manual_sync_ajax']);
    }

    /**
     * Handle manual sync AJAX request
     */
    public function handle_manual_sync_ajax(): void {
        if (!wp_verify_nonce($_POST['nonce'], 'hp_agent_sync') || !current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Access denied']);
            return;
        }

        $agent_id = intval($_POST['agent_id']);
        if (!$agent_id) {
            wp_send_json_error(['message' => 'Invalid agent ID']);
            return;
        }

        $result = $this->manual_sync_agent($agent_id);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Get sync settings
     */
    public function get_sync_settings(): array {
        return $this->sync_settings;
    }

    /**
     * Update sync settings
     */
    public function update_sync_settings(array $settings): void {
        $this->sync_settings = array_merge($this->sync_settings, $settings);
        update_option('hp_agent_user_sync_settings', $this->sync_settings);
        $this->log('Sync settings updated');
    }
}