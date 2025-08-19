<?php
/**
 * Frontend AJAX Handlers
 * 
 * Comprehensive AJAX handler for all frontend dashboard operations
 */

namespace HappyPlace\Dashboard;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Ajax_Handlers {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize
     */
    private function __construct() {
        add_action('wp_ajax_hph_save_listing', [$this, 'save_listing']);
        add_action('wp_ajax_hph_save_agent_profile', [$this, 'save_agent_profile']);
        add_action('wp_ajax_hph_preview_agent_profile', [$this, 'preview_agent_profile']);
        add_action('wp_ajax_hph_delete_item', [$this, 'delete_item']);
        add_action('wp_ajax_hph_bulk_action', [$this, 'bulk_action']);
        add_action('wp_ajax_hph_refresh_section', [$this, 'refresh_section']);
        add_action('wp_ajax_hph_global_search', [$this, 'global_search']);
        add_action('wp_ajax_hph_upload_image', [$this, 'upload_image']);
        add_action('wp_ajax_hph_geocode_address', [$this, 'geocode_address']);
    }
    
    /**
     * Save listing (create or update)
     */
    public function save_listing() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['listing_nonce'], 'hph_save_listing')) {
            wp_send_json_error(['message' => __('Security check failed', 'happy-place')]);
        }
        
        // Check permissions
        $dashboard = Frontend_Admin_Dashboard::get_instance();
        if (!$dashboard->user_can('manage_own_listings') && !$dashboard->user_can('manage_all_listings')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'happy-place')]);
        }
        
        try {
            $listing_id = intval($_POST['listing_id'] ?? 0);
            $is_update = $listing_id > 0;
            
            // Validate required fields
            $validation_errors = $this->validate_listing_data($_POST);
            if (!empty($validation_errors)) {
                wp_send_json_error(['message' => implode('<br>', $validation_errors)]);
            }
            
            // Prepare post data
            $post_data = [
                'post_type' => 'listing',
                'post_status' => 'publish',
                'post_title' => $this->generate_listing_title($_POST),
                'post_content' => sanitize_textarea_field($_POST['description'] ?? ''),
                'meta_input' => []
            ];
            
            if ($is_update) {
                $post_data['ID'] = $listing_id;
                
                // Check ownership for agents
                if (!$dashboard->user_can('manage_all_listings')) {
                    $current_agent = get_field('listing_agent', $listing_id);
                    if ($current_agent != get_current_user_id()) {
                        wp_send_json_error(['message' => __('You can only edit your own listings', 'happy-place')]);
                    }
                }
            }
            
            // Save or update post
            if ($is_update) {
                $result = wp_update_post($post_data, true);
            } else {
                $result = wp_insert_post($post_data, true);
                $listing_id = $result;
            }
            
            if (is_wp_error($result)) {
                wp_send_json_error(['message' => $result->get_error_message()]);
            }
            
            // Save ACF fields
            $this->save_listing_fields($listing_id, $_POST);
            
            // Handle image uploads
            $this->handle_listing_images($listing_id, $_FILES);
            
            // Set listing agent if not already set
            if (!$is_update || !get_field('listing_agent', $listing_id)) {
                update_field('listing_agent', get_current_user_id(), $listing_id);
            }
            
            // Trigger automation (slug generation, etc.)
            do_action('hph_listing_saved', $listing_id, $is_update);
            
            $message = $is_update ? 
                __('Listing updated successfully!', 'happy-place') : 
                __('Listing created successfully!', 'happy-place');
            
            wp_send_json_success([
                'message' => $message,
                'listing_id' => $listing_id,
                'redirect' => add_query_arg([
                    'dashboard_section' => 'listings',
                    'dashboard_action' => 'view',
                    'dashboard_id' => $listing_id
                ], get_permalink())
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error(['message' => __('An error occurred while saving the listing', 'happy-place')]);
        }
    }
    
    /**
     * Save agent profile
     */
    public function save_agent_profile() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['profile_nonce'], 'hph_save_agent_profile')) {
            wp_send_json_error(['message' => __('Security check failed', 'happy-place')]);
        }
        
        // Check permissions
        $dashboard = Frontend_Admin_Dashboard::get_instance();
        if (!$dashboard->user_can('edit_profile')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'happy-place')]);
        }
        
        try {
            $agent_id = intval($_POST['agent_id'] ?? 0);
            $current_user_id = get_current_user_id();
            
            // Find or create agent post for current user
            if (!$agent_id) {
                $agent_posts = get_posts([
                    'post_type' => 'agent',
                    'meta_query' => [
                        [
                            'key' => 'wordpress_user_id',
                            'value' => $current_user_id,
                            'compare' => '='
                        ]
                    ],
                    'posts_per_page' => 1
                ]);
                
                if ($agent_posts) {
                    $agent_id = $agent_posts[0]->ID;
                }
            }
            
            // Validate required fields
            if (empty($_POST['first_name']) || empty($_POST['last_name'])) {
                wp_send_json_error(['message' => __('First name and last name are required', 'happy-place')]);
            }
            
            // Generate display name if not provided
            $display_name = sanitize_text_field($_POST['display_name']);
            if (empty($display_name)) {
                $name_parts = array_filter([
                    sanitize_text_field($_POST['first_name']),
                    sanitize_text_field($_POST['middle_name']),
                    sanitize_text_field($_POST['last_name']),
                    sanitize_text_field($_POST['suffix'])
                ]);
                $display_name = implode(' ', $name_parts);
            }
            
            // Prepare post data
            $post_data = [
                'post_type' => 'agent',
                'post_status' => 'publish',
                'post_title' => $display_name,
                'post_content' => sanitize_textarea_field($_POST['full_bio'] ?? '')
            ];
            
            if ($agent_id) {
                $post_data['ID'] = $agent_id;
                $result = wp_update_post($post_data, true);
            } else {
                $result = wp_insert_post($post_data, true);
                $agent_id = $result;
                
                // Link to WordPress user
                update_field('wordpress_user_id', $current_user_id, $agent_id);
            }
            
            if (is_wp_error($result)) {
                wp_send_json_error(['message' => $result->get_error_message()]);
            }
            
            // Save all ACF fields
            $this->save_agent_fields($agent_id, $_POST);
            
            // Handle image uploads
            $this->handle_agent_images($agent_id, $_FILES);
            
            // Trigger automation
            do_action('hph_agent_profile_saved', $agent_id);
            
            wp_send_json_success([
                'message' => __('Profile saved successfully!', 'happy-place'),
                'agent_id' => $agent_id
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error(['message' => __('An error occurred while saving your profile', 'happy-place')]);
        }
    }
    
    /**
     * Preview agent profile
     */
    public function preview_agent_profile() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['profile_nonce'], 'hph_save_agent_profile')) {
            wp_send_json_error(['message' => __('Security check failed', 'happy-place')]);
        }
        
        // Generate preview HTML
        $preview_html = $this->generate_agent_preview($_POST);
        
        wp_send_json_success(['preview' => $preview_html]);
    }
    
    /**
     * Delete item
     */
    public function delete_item() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => __('Security check failed', 'happy-place')]);
        }
        
        $item_id = intval($_POST['id']);
        $item_type = sanitize_text_field($_POST['type']);
        
        // Check permissions
        $dashboard = Frontend_Admin_Dashboard::get_instance();
        
        if ($item_type === 'listing') {
            if (!$dashboard->user_can('manage_all_listings')) {
                // Check ownership
                $listing_agent = get_field('listing_agent', $item_id);
                if ($listing_agent != get_current_user_id()) {
                    wp_send_json_error(['message' => __('You can only delete your own listings', 'happy-place')]);
                }
            }
        } else {
            if (!$dashboard->user_can('manage_all_content')) {
                wp_send_json_error(['message' => __('Insufficient permissions', 'happy-place')]);
            }
        }
        
        // Perform deletion
        $result = wp_delete_post($item_id, true);
        
        if ($result) {
            wp_send_json_success(['message' => __('Item deleted successfully', 'happy-place')]);
        } else {
            wp_send_json_error(['message' => __('Failed to delete item', 'happy-place')]);
        }
    }
    
    /**
     * Handle bulk actions
     */
    public function bulk_action() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => __('Security check failed', 'happy-place')]);
        }
        
        $action = sanitize_text_field($_POST['bulk_action']);
        $ids = array_map('intval', $_POST['ids']);
        
        if (empty($ids)) {
            wp_send_json_error(['message' => __('No items selected', 'happy-place')]);
        }
        
        $dashboard = Frontend_Admin_Dashboard::get_instance();
        
        switch ($action) {
            case 'delete':
                if (!$dashboard->user_can('manage_all_content')) {
                    wp_send_json_error(['message' => __('Insufficient permissions', 'happy-place')]);
                }
                
                $deleted = 0;
                foreach ($ids as $id) {
                    if (wp_delete_post($id, true)) {
                        $deleted++;
                    }
                }
                
                wp_send_json_success(['message' => sprintf(__('Deleted %d items', 'happy-place'), $deleted)]);
                break;
                
            case 'publish':
                if (!$dashboard->user_can('manage_all_content')) {
                    wp_send_json_error(['message' => __('Insufficient permissions', 'happy-place')]);
                }
                
                $updated = 0;
                foreach ($ids as $id) {
                    if (wp_update_post(['ID' => $id, 'post_status' => 'publish'])) {
                        $updated++;
                    }
                }
                
                wp_send_json_success(['message' => sprintf(__('Published %d items', 'happy-place'), $updated)]);
                break;
                
            default:
                wp_send_json_error(['message' => __('Invalid action', 'happy-place')]);
        }
    }
    
    /**
     * Refresh dashboard section
     */
    public function refresh_section() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => __('Security check failed', 'happy-place')]);
        }
        
        $section = sanitize_text_field($_POST['section']);
        $dashboard = Frontend_Admin_Dashboard::get_instance();
        
        ob_start();
        $dashboard->render_section_content($section);
        $content = ob_get_clean();
        
        wp_send_json_success(['content' => $content]);
    }
    
    /**
     * Global search
     */
    public function global_search() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => __('Security check failed', 'happy-place')]);
        }
        
        $query = sanitize_text_field($_POST['query']);
        
        if (strlen($query) < 3) {
            wp_send_json_error(['message' => __('Query too short', 'happy-place')]);
        }
        
        // Search across multiple post types
        $search_results = [
            'listings' => $this->search_listings($query),
            'agents' => $this->search_agents($query),
            'communities' => $this->search_communities($query)
        ];
        
        wp_send_json_success($search_results);
    }
    
    /**
     * Upload image via AJAX
     */
    public function upload_image() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => __('Security check failed', 'happy-place')]);
        }
        
        if (empty($_FILES['image'])) {
            wp_send_json_error(['message' => __('No image uploaded', 'happy-place')]);
        }
        
        // Handle the upload
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $attachment_id = media_handle_upload('image', 0);
        
        if (is_wp_error($attachment_id)) {
            wp_send_json_error(['message' => $attachment_id->get_error_message()]);
        }
        
        $image_url = wp_get_attachment_image_url($attachment_id, 'medium');
        $image_thumb = wp_get_attachment_image_url($attachment_id, 'thumbnail');
        
        wp_send_json_success([
            'attachment_id' => $attachment_id,
            'image_url' => $image_url,
            'thumbnail_url' => $image_thumb
        ]);
    }
    
    /**
     * Geocode address
     */
    public function geocode_address() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_dashboard')) {
            wp_send_json_error(['message' => __('Security check failed', 'happy-place')]);
        }
        
        $address = sanitize_text_field($_POST['address']);
        
        if (empty($address)) {
            wp_send_json_error(['message' => __('Address required', 'happy-place')]);
        }
        
        // Use Google Geocoding API or similar service
        $coordinates = $this->geocode_address_api($address);
        
        if ($coordinates) {
            wp_send_json_success($coordinates);
        } else {
            wp_send_json_error(['message' => __('Could not geocode address', 'happy-place')]);
        }
    }
    
    /**
     * Validate listing data
     */
    private function validate_listing_data($data) {
        $errors = [];
        
        if (empty($data['street_address'])) {
            $errors[] = __('Street address is required', 'happy-place');
        }
        
        if (empty($data['city'])) {
            $errors[] = __('City is required', 'happy-place');
        }
        
        if (empty($data['state'])) {
            $errors[] = __('State is required', 'happy-place');
        }
        
        if (!empty($data['price']) && !is_numeric($data['price'])) {
            $errors[] = __('Price must be a valid number', 'happy-place');
        }
        
        if (!empty($data['bedrooms']) && !is_numeric($data['bedrooms'])) {
            $errors[] = __('Bedrooms must be a valid number', 'happy-place');
        }
        
        if (!empty($data['bathrooms']) && !is_numeric($data['bathrooms'])) {
            $errors[] = __('Bathrooms must be a valid number', 'happy-place');
        }
        
        return $errors;
    }
    
    /**
     * Generate listing title from address
     */
    private function generate_listing_title($data) {
        $title_parts = array_filter([
            sanitize_text_field($data['street_address']),
            sanitize_text_field($data['city']),
            sanitize_text_field($data['state'])
        ]);
        
        return implode(', ', $title_parts);
    }
    
    /**
     * Save listing ACF fields
     */
    private function save_listing_fields($listing_id, $data) {
        $field_mapping = [
            'price' => 'price',
            'property_status' => 'property_status',
            'mls_number' => 'mls_number',
            'bedrooms' => 'bedrooms',
            'bathrooms' => 'bathrooms',
            'square_feet' => 'square_feet',
            'year_built' => 'year_built',
            'lot_size' => 'lot_size',
            'street_address' => 'street_address',
            'city' => 'city',
            'state' => 'state',
            'zip_code' => 'zip_code',
            'county' => 'county',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'garage_spaces' => 'garage_spaces',
            'has_pool' => 'has_pool',
            'has_spa' => 'has_spa',
            'hoa_fees' => 'hoa_fees',
            'property_taxes' => 'property_taxes',
            'virtual_tour_url' => 'virtual_tour_url',
            'video_tour_url' => 'video_tour_url'
        ];
        
        foreach ($field_mapping as $form_field => $acf_field) {
            if (isset($data[$form_field])) {
                $value = $data[$form_field];
                
                // Sanitize based on field type
                if (in_array($form_field, ['price', 'bedrooms', 'bathrooms', 'square_feet', 'year_built', 'lot_size', 'garage_spaces', 'hoa_fees', 'property_taxes'])) {
                    $value = is_numeric($value) ? floatval($value) : 0;
                } elseif (in_array($form_field, ['has_pool', 'has_spa'])) {
                    $value = !empty($value) ? true : false;
                } elseif (in_array($form_field, ['latitude', 'longitude'])) {
                    $value = is_numeric($value) ? floatval($value) : '';
                } else {
                    $value = sanitize_text_field($value);
                }
                
                update_field($acf_field, $value, $listing_id);
            }
        }
        
        // Handle checkbox arrays
        if (isset($data['property_features']) && is_array($data['property_features'])) {
            update_field('property_features', array_map('sanitize_text_field', $data['property_features']), $listing_id);
        }
        
        if (isset($data['interior_features']) && is_array($data['interior_features'])) {
            update_field('interior_features', array_map('sanitize_text_field', $data['interior_features']), $listing_id);
        }
        
        if (isset($data['exterior_features']) && is_array($data['exterior_features'])) {
            update_field('exterior_features', array_map('sanitize_text_field', $data['exterior_features']), $listing_id);
        }
    }
    
    /**
     * Save agent ACF fields
     */
    private function save_agent_fields($agent_id, $data) {
        $field_mapping = [
            'first_name' => 'first_name',
            'middle_name' => 'middle_name',
            'last_name' => 'last_name',
            'suffix' => 'suffix',
            'display_name' => 'display_name',
            'title' => 'title',
            'short_bio' => 'short_bio',
            'full_bio' => 'full_bio',
            'email' => 'email',
            'phone' => 'phone',
            'mobile_phone' => 'mobile_phone',
            'office_phone' => 'office_phone',
            'website_url' => 'website_url',
            'license_number' => 'license_number',
            'license_state' => 'license_state',
            'license_expiration' => 'license_expiration',
            'date_started' => 'date_started',
            'years_experience' => 'years_experience',
            'office_name' => 'office_name',
            'facebook_url' => 'facebook_url',
            'instagram_url' => 'instagram_url',
            'linkedin_url' => 'linkedin_url',
            'twitter_url' => 'twitter_url',
            'youtube_url' => 'youtube_url',
            'zillow_profile_url' => 'zillow_profile_url'
        ];
        
        foreach ($field_mapping as $form_field => $acf_field) {
            if (isset($data[$form_field])) {
                $value = sanitize_text_field($data[$form_field]);
                update_field($acf_field, $value, $agent_id);
            }
        }
        
        // Handle array fields
        if (isset($data['specialties']) && is_array($data['specialties'])) {
            update_field('specialties', array_map('sanitize_text_field', $data['specialties']), $agent_id);
        }
        
        if (isset($data['languages']) && is_array($data['languages'])) {
            update_field('languages', array_map('sanitize_text_field', $data['languages']), $agent_id);
        }
    }
    
    /**
     * Handle listing image uploads
     */
    private function handle_listing_images($listing_id, $files) {
        if (empty($files)) return;
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        // Handle featured image
        if (!empty($files['featured_image']['name'])) {
            $attachment_id = media_handle_upload('featured_image', $listing_id);
            if (!is_wp_error($attachment_id)) {
                set_post_thumbnail($listing_id, $attachment_id);
            }
        }
        
        // Handle gallery images
        if (!empty($files['gallery_images']['name'][0])) {
            $gallery_ids = [];
            
            foreach ($files['gallery_images']['name'] as $key => $name) {
                if (!empty($name)) {
                    $_FILES['gallery_image'] = [
                        'name' => $files['gallery_images']['name'][$key],
                        'type' => $files['gallery_images']['type'][$key],
                        'tmp_name' => $files['gallery_images']['tmp_name'][$key],
                        'error' => $files['gallery_images']['error'][$key],
                        'size' => $files['gallery_images']['size'][$key]
                    ];
                    
                    $attachment_id = media_handle_upload('gallery_image', $listing_id);
                    if (!is_wp_error($attachment_id)) {
                        $gallery_ids[] = $attachment_id;
                    }
                }
            }
            
            if (!empty($gallery_ids)) {
                update_field('gallery_images', $gallery_ids, $listing_id);
            }
        }
    }
    
    /**
     * Handle agent image uploads
     */
    private function handle_agent_images($agent_id, $files) {
        if (empty($files)) return;
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        // Handle profile photo
        if (!empty($files['profile_photo']['name'])) {
            $attachment_id = media_handle_upload('profile_photo', $agent_id);
            if (!is_wp_error($attachment_id)) {
                set_post_thumbnail($agent_id, $attachment_id);
                update_field('profile_photo', $attachment_id, $agent_id);
            }
        }
        
        // Handle cover photo
        if (!empty($files['cover_photo']['name'])) {
            $attachment_id = media_handle_upload('cover_photo', $agent_id);
            if (!is_wp_error($attachment_id)) {
                update_field('cover_photo', $attachment_id, $agent_id);
            }
        }
    }
    
    /**
     * Generate agent profile preview
     */
    private function generate_agent_preview($data) {
        $display_name = !empty($data['display_name']) ? $data['display_name'] : 
            implode(' ', array_filter([
                $data['first_name'],
                $data['middle_name'], 
                $data['last_name'],
                $data['suffix']
            ]));
        
        ob_start();
        ?>
        <div class="agent-preview">
            <div class="agent-preview-header">
                <h2><?php echo esc_html($display_name); ?></h2>
                <?php if (!empty($data['title'])): ?>
                    <p class="agent-title"><?php echo esc_html($data['title']); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($data['short_bio'])): ?>
                <div class="agent-preview-bio">
                    <h4><?php _e('Short Bio', 'happy-place'); ?></h4>
                    <p><?php echo esc_html($data['short_bio']); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="agent-preview-contact">
                <h4><?php _e('Contact Information', 'happy-place'); ?></h4>
                <ul>
                    <?php if (!empty($data['email'])): ?>
                        <li><strong><?php _e('Email:', 'happy-place'); ?></strong> <?php echo esc_html($data['email']); ?></li>
                    <?php endif; ?>
                    <?php if (!empty($data['phone'])): ?>
                        <li><strong><?php _e('Phone:', 'happy-place'); ?></strong> <?php echo esc_html($data['phone']); ?></li>
                    <?php endif; ?>
                    <?php if (!empty($data['mobile_phone'])): ?>
                        <li><strong><?php _e('Mobile:', 'happy-place'); ?></strong> <?php echo esc_html($data['mobile_phone']); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <?php if (!empty($data['specialties'])): ?>
                <div class="agent-preview-specialties">
                    <h4><?php _e('Specialties', 'happy-place'); ?></h4>
                    <p><?php echo esc_html(implode(', ', (array)$data['specialties'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .agent-preview {
            padding: var(--hph-space-lg);
            background: var(--hph-white);
            border-radius: var(--hph-border-radius);
        }
        .agent-preview-header h2 {
            margin: 0 0 var(--hph-space-xs) 0;
            color: var(--hph-primary);
        }
        .agent-title {
            color: var(--hph-text-muted);
            font-style: italic;
            margin-bottom: var(--hph-space-md);
        }
        .agent-preview h4 {
            color: var(--hph-primary-dark);
            margin: var(--hph-space-md) 0 var(--hph-space-sm) 0;
        }
        .agent-preview ul {
            list-style: none;
            padding: 0;
        }
        .agent-preview li {
            margin-bottom: var(--hph-space-xs);
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Search listings
     */
    private function search_listings($query) {
        $args = [
            'post_type' => 'listing',
            'posts_per_page' => 10,
            's' => $query,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'street_address',
                    'value' => $query,
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'city',
                    'value' => $query,
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'mls_number',
                    'value' => $query,
                    'compare' => 'LIKE'
                ]
            ]
        ];
        
        $listings = get_posts($args);
        return array_map(function($listing) {
            return [
                'id' => $listing->ID,
                'title' => $listing->post_title,
                'url' => get_permalink($listing->ID)
            ];
        }, $listings);
    }
    
    /**
     * Search agents
     */
    private function search_agents($query) {
        $args = [
            'post_type' => 'agent',
            'posts_per_page' => 10,
            's' => $query
        ];
        
        $agents = get_posts($args);
        return array_map(function($agent) {
            return [
                'id' => $agent->ID,
                'title' => $agent->post_title,
                'url' => get_permalink($agent->ID)
            ];
        }, $agents);
    }
    
    /**
     * Search communities
     */
    private function search_communities($query) {
        $args = [
            'post_type' => 'community',
            'posts_per_page' => 10,
            's' => $query
        ];
        
        $communities = get_posts($args);
        return array_map(function($community) {
            return [
                'id' => $community->ID,
                'title' => $community->post_title,
                'url' => get_permalink($community->ID)
            ];
        }, $communities);
    }
    
    /**
     * Geocode address using API
     */
    private function geocode_address_api($address) {
        // This would integrate with Google Geocoding API or similar
        // For now, return dummy coordinates
        return [
            'latitude' => 0,
            'longitude' => 0,
            'formatted_address' => $address
        ];
    }
}

// Initialize
Frontend_Ajax_Handlers::get_instance();