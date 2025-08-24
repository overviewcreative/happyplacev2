<?php
/**
 * Listing Service - Primary CRUD Operations
 * 
 * Handles all listing-related data operations including create, read, update, delete
 * and bulk operations for property listings.
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Listing Service Class
 * 
 * Primary service for listing CRUD operations as specified in services.md
 */
class ListingService extends Service {
    
    /**
     * Service name
     */
    protected string $name = 'listing_service';
    
    /**
     * Service version
     */
    protected string $version = '4.0.0';
    
    /**
     * Cache key prefix
     */
    private string $cache_prefix = 'hp_listing_';
    
    /**
     * Listing post type
     */
    private string $post_type = 'listing';
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        // Service-specific initialization
        add_action('init', [$this, 'setup_hooks']);
        
        $this->initialized = true;
        $this->log('Listing Service initialized successfully');
    }
    
    /**
     * Setup WordPress hooks
     */
    public function setup_hooks(): void {
        // Add any WordPress hooks specific to listing service
        add_filter('wp_insert_post_data', [$this, 'filter_listing_data'], 10, 2);
    }
    
    /**
     * Filter listing data before insertion
     */
    public function filter_listing_data(array $data, array $postarr): array {
        if ($data['post_type'] === $this->post_type) {
            // Add any default processing here
            $this->log("Processing listing data for post ID: " . ($postarr['ID'] ?? 'new'));
        }
        return $data;
    }
    
    /**
     * Create a new listing
     * 
     * @param array $data Listing data
     * @return int|WP_Error Listing ID on success, WP_Error on failure
     */
    public function create_listing(array $data) {
        // Validate required fields
        $validation = $this->validate_required_fields($data);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Prepare post data
        $post_data = [
            'post_title' => $data['title'] ?? '',
            'post_content' => $data['description'] ?? '',
            'post_type' => $this->post_type,
            'post_status' => $data['status'] ?? 'draft',
            'post_author' => $data['author_id'] ?? get_current_user_id(),
            'meta_input' => $this->prepare_meta_data($data)
        ];
        
        // Insert post
        $listing_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($listing_id)) {
            return $listing_id;
        }
        
        // Update ACF fields
        $this->update_listing_fields($listing_id, $data);
        
        // Handle media attachments
        if (!empty($data['images'])) {
            $this->attach_images($listing_id, $data['images']);
        }
        
        // Clear cache
        $this->clear_listing_cache($listing_id);
        
        // Log activity
        $this->log_activity('create_listing', $listing_id, $data);
        
        return $listing_id;
    }
    
    /**
     * Update an existing listing
     * 
     * @param int $listing_id Listing ID
     * @param array $data Updated data
     * @return int|WP_Error Listing ID on success, WP_Error on failure
     */
    public function update_listing(int $listing_id, array $data) {
        // Check if listing exists
        if (!$this->listing_exists($listing_id)) {
            return new \WP_Error('listing_not_found', 'Listing not found');
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $listing_id)) {
            return new \WP_Error('insufficient_permissions', 'Insufficient permissions to edit this listing');
        }
        
        // Prepare update data
        $update_data = [
            'ID' => $listing_id,
            'post_title' => $data['title'] ?? null,
            'post_content' => $data['description'] ?? null,
            'post_status' => $data['status'] ?? null
        ];
        
        // Remove null values
        $update_data = array_filter($update_data, function($value) {
            return $value !== null;
        });
        
        // Update post
        $result = wp_update_post($update_data, true);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Update ACF fields
        $this->update_listing_fields($listing_id, $data);
        
        // Handle media updates
        if (isset($data['images'])) {
            $this->attach_images($listing_id, $data['images']);
        }
        
        if (isset($data['featured_image_id'])) {
            $this->set_featured_image($listing_id, $data['featured_image_id']);
        }
        
        // Clear cache
        $this->clear_listing_cache($listing_id);
        
        // Log activity
        $this->log_activity('update_listing', $listing_id, $data);
        
        return $listing_id;
    }
    
    /**
     * Delete a listing
     * 
     * @param int $listing_id Listing ID
     * @param bool $force_delete Whether to force delete or move to trash
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function delete_listing(int $listing_id, bool $force_delete = false) {
        // Check if listing exists
        if (!$this->listing_exists($listing_id)) {
            return new \WP_Error('listing_not_found', 'Listing not found');
        }
        
        // Check permissions
        if (!current_user_can('delete_post', $listing_id)) {
            return new \WP_Error('insufficient_permissions', 'Insufficient permissions to delete this listing');
        }
        
        // Delete post
        $result = wp_delete_post($listing_id, $force_delete);
        
        if (!$result) {
            return new \WP_Error('delete_failed', 'Failed to delete listing');
        }
        
        // Clear cache
        $this->clear_listing_cache($listing_id);
        
        // Log activity
        $this->log_activity('delete_listing', $listing_id, ['force_delete' => $force_delete]);
        
        return true;
    }
    
    /**
     * Duplicate a listing
     * 
     * @param int $listing_id Listing ID to duplicate
     * @return int|WP_Error New listing ID on success, WP_Error on failure
     */
    public function duplicate_listing(int $listing_id) {
        // Get original listing
        $original = get_post($listing_id);
        
        if (!$original || $original->post_type !== $this->post_type) {
            return new \WP_Error('listing_not_found', 'Original listing not found');
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $listing_id)) {
            return new \WP_Error('insufficient_permissions', 'Insufficient permissions to duplicate this listing');
        }
        
        // Get all meta data
        $meta_data = get_post_meta($listing_id);
        
        // Prepare duplicate data
        $duplicate_data = [
            'post_title' => $original->post_title . ' (Copy)',
            'post_content' => $original->post_content,
            'post_type' => $original->post_type,
            'post_status' => 'draft',
            'post_author' => get_current_user_id()
        ];
        
        // Create duplicate
        $new_listing_id = wp_insert_post($duplicate_data, true);
        
        if (is_wp_error($new_listing_id)) {
            return $new_listing_id;
        }
        
        // Copy meta data
        foreach ($meta_data as $key => $values) {
            foreach ($values as $value) {
                add_post_meta($new_listing_id, $key, maybe_unserialize($value));
            }
        }
        
        // Log activity
        $this->log_activity('duplicate_listing', $new_listing_id, ['original_id' => $listing_id]);
        
        return $new_listing_id;
    }
    
    /**
     * Bulk update listings
     * 
     * @param array $listing_ids Array of listing IDs
     * @param array $data Data to update
     * @return array Results array with success/failure counts
     */
    public function bulk_update(array $listing_ids, array $data): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($listing_ids as $listing_id) {
            $result = $this->update_listing($listing_id, $data);
            
            if (is_wp_error($result)) {
                $results['failed']++;
                $results['errors'][] = [
                    'listing_id' => $listing_id,
                    'error' => $result->get_error_message()
                ];
            } else {
                $results['success']++;
            }
        }
        
        // Log bulk activity
        $this->log_activity('bulk_update', 0, [
            'listing_ids' => $listing_ids,
            'data' => $data,
            'results' => $results
        ]);
        
        return $results;
    }
    
    /**
     * Bulk delete listings
     * 
     * @param array $listing_ids Array of listing IDs
     * @param bool $force_delete Whether to force delete
     * @return array Results array
     */
    public function bulk_delete(array $listing_ids, bool $force_delete = false): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($listing_ids as $listing_id) {
            $result = $this->delete_listing($listing_id, $force_delete);
            
            if (is_wp_error($result)) {
                $results['failed']++;
                $results['errors'][] = [
                    'listing_id' => $listing_id,
                    'error' => $result->get_error_message()
                ];
            } else {
                $results['success']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Update listing status
     * 
     * @param int $listing_id Listing ID
     * @param string $status New status
     * @return bool|WP_Error
     */
    public function update_status(int $listing_id, string $status) {
        // Validate status
        $valid_statuses = ['draft', 'publish', 'pending', 'private', 'archived'];
        if (!in_array($status, $valid_statuses)) {
            return new \WP_Error('invalid_status', 'Invalid status provided');
        }
        
        return $this->update_listing($listing_id, ['status' => $status]);
    }
    
    /**
     * Archive a listing
     * 
     * @param int $listing_id Listing ID
     * @return bool|WP_Error
     */
    public function archive_listing(int $listing_id) {
        return $this->update_status($listing_id, 'archived');
    }
    
    /**
     * Attach images to listing
     * 
     * @param int $listing_id Listing ID
     * @param array $images Array of image IDs or URLs
     * @return bool|WP_Error
     */
    public function attach_images(int $listing_id, array $images) {
        if (!$this->listing_exists($listing_id)) {
            return new \WP_Error('listing_not_found', 'Listing not found');
        }
        
        $processed_images = [];
        
        foreach ($images as $image) {
            if (is_numeric($image)) {
                // It's an attachment ID
                $processed_images[] = $image;
            } elseif (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
                // It's a URL, need to handle image upload
                $attachment_id = $this->handle_image_upload($image);
                if ($attachment_id && !is_wp_error($attachment_id)) {
                    $processed_images[] = $attachment_id;
                }
            }
        }
        
        // Update ACF gallery field
        update_field('listing_gallery', $processed_images, $listing_id);
        
        return true;
    }
    
    /**
     * Set featured image
     * 
     * @param int $listing_id Listing ID
     * @param int $image_id Image attachment ID
     * @return bool|WP_Error
     */
    public function set_featured_image(int $listing_id, int $image_id) {
        if (!$this->listing_exists($listing_id)) {
            return new \WP_Error('listing_not_found', 'Listing not found');
        }
        
        $result = set_post_thumbnail($listing_id, $image_id);
        
        return $result ? true : new \WP_Error('featured_image_failed', 'Failed to set featured image');
    }
    
    /**
     * Validate required fields
     * 
     * @param array $data Listing data
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    private function validate_required_fields(array $data) {
        $required_fields = ['title'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new \WP_Error('missing_required_field', "Required field '{$field}' is missing");
            }
        }
        
        return true;
    }
    
    /**
     * Map form data to ACF fields
     * 
     * @param int $listing_id Listing ID
     * @param array $data Form data
     * @return void
     */
    private function update_listing_fields(int $listing_id, array $data): void {
        // Field mapping from form to ACF field keys
        $field_mapping = [
            'price' => 'field_listing_price',
            'bedrooms' => 'field_listing_bedrooms',
            'bathrooms' => 'field_listing_bathrooms',
            'square_feet' => 'field_listing_square_feet',
            'lot_size' => 'field_listing_lot_size',
            'year_built' => 'field_listing_year_built',
            'property_type' => 'field_listing_property_type',
            'listing_type' => 'field_listing_type',
            'mls_number' => 'field_listing_mls_number',
            'address' => [
                'street_address' => 'field_listing_street_address',
                'city' => 'field_listing_city',
                'state' => 'field_listing_state',
                'zip_code' => 'field_listing_zip_code',
                'country' => 'field_listing_country'
            ]
        ];
        
        foreach ($field_mapping as $form_field => $acf_field) {
            if (isset($data[$form_field])) {
                if (is_array($acf_field)) {
                    // Handle address sub-fields
                    if ($form_field === 'address' && is_array($data[$form_field])) {
                        foreach ($acf_field as $sub_field => $acf_sub_field) {
                            if (isset($data[$form_field][$sub_field])) {
                                update_field($acf_sub_field, $data[$form_field][$sub_field], $listing_id);
                            }
                        }
                    }
                } else {
                    update_field($acf_field, $data[$form_field], $listing_id);
                }
            }
        }
    }
    
    /**
     * Prepare meta data for wp_insert_post
     * 
     * @param array $data Listing data
     * @return array Meta data array
     */
    private function prepare_meta_data(array $data): array {
        $meta_data = [];
        
        // Add basic meta fields that don't require ACF
        if (isset($data['featured'])) {
            $meta_data['_listing_featured'] = $data['featured'] ? 1 : 0;
        }
        
        if (isset($data['virtual_tour_url'])) {
            $meta_data['_listing_virtual_tour'] = $data['virtual_tour_url'];
        }
        
        return $meta_data;
    }
    
    /**
     * Handle image upload from URL
     * 
     * @param string $image_url Image URL
     * @return int|WP_Error Attachment ID on success
     */
    private function handle_image_upload(string $image_url) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $attachment_id = media_sideload_image($image_url, 0, null, 'id');
        
        return $attachment_id;
    }
    
    /**
     * Check if listing exists
     * 
     * @param int $listing_id Listing ID
     * @return bool
     */
    private function listing_exists(int $listing_id): bool {
        $post = get_post($listing_id);
        return $post && $post->post_type === $this->post_type;
    }
    
    /**
     * Clear listing cache
     * 
     * @param int $listing_id Listing ID
     * @return void
     */
    private function clear_listing_cache(int $listing_id): void {
        wp_cache_delete($this->cache_prefix . $listing_id);
        wp_cache_delete($this->cache_prefix . 'user_' . get_current_user_id());
        
        // Clear any transients
        delete_transient($this->cache_prefix . 'recent_listings');
        delete_transient($this->cache_prefix . 'featured_listings');
    }
    
    /**
     * Log activity
     * 
     * @param string $action Action performed
     * @param int $listing_id Listing ID (0 for bulk operations)
     * @param array $data Additional data
     * @return void
     */
    private function log_activity(string $action, int $listing_id, array $data = []): void {
        if (HP_DEBUG) {
            error_log(sprintf(
                'Happy Place Listing Service: %s - Listing ID: %d - Data: %s',
                $action,
                $listing_id,
                json_encode($data)
            ));
        }
        
        // Could also integrate with a proper logging service here
        do_action('hp_listing_service_activity', $action, $listing_id, $data);
    }
}