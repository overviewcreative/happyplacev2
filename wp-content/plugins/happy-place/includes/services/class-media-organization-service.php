<?php
/**
 * Real Estate Media Organization System
 *
 * Handles file renaming, metadata, organization, and optimization
 * for property listings media uploads.
 *
 * @package HappyPlace
 */

namespace HappyPlace\Services;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MediaOrganizationService {
    
    private $naming_pattern = '{type}_{listing}_{index}_{date}';
    private $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    private $metadata_fields = [];
    
    public function __construct() {
        $this->init_metadata_fields();
    }

    /**
     * Initialize the service
     */
    public function init() {
        // File handling
        // TEMPORARILY COMMENTED OUT - Causing issues with bulk uploads
        // add_filter('wp_handle_upload_prefilter', [$this, 'rename_on_upload']);
        add_filter('wp_handle_upload', [$this, 'organize_upload_directory']);

        // Metadata handling
        add_action('add_attachment', [$this, 'add_automatic_metadata']);
        add_filter('attachment_fields_to_edit', [$this, 'add_custom_media_fields'], 10, 2);
        add_filter('attachment_fields_to_save', [$this, 'save_custom_media_fields'], 10, 2);

        // Admin interface
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        // Bulk operations
        add_filter('bulk_actions-upload', [$this, 'register_bulk_actions']);
        add_filter('handle_bulk_actions-upload', [$this, 'handle_bulk_actions'], 10, 3);

        // AJAX handlers
        add_action('wp_ajax_hpt_process_media_batch', [$this, 'ajax_process_batch']);

        error_log('Happy Place: Media Organization Service initialized');
    }
    
    /**
     * Initialize metadata field definitions
     */
    private function init_metadata_fields() {
        $this->metadata_fields = [
            'property_area' => [
                'label' => 'Property Area',
                'type' => 'select',
                'options' => [
                    'exterior' => 'Exterior',
                    'interior' => 'Interior',
                    'kitchen' => 'Kitchen',
                    'bedroom' => 'Bedroom',
                    'bathroom' => 'Bathroom',
                    'living_room' => 'Living Room',
                    'basement' => 'Basement',
                    'garage' => 'Garage',
                    'yard' => 'Yard',
                    'pool' => 'Pool',
                    'aerial' => 'Aerial/Drone',
                    'floorplan' => 'Floor Plan',
                    'neighborhood' => 'Neighborhood'
                ]
            ],
            'image_season' => [
                'label' => 'Season',
                'type' => 'select',
                'options' => [
                    'spring' => 'Spring',
                    'summer' => 'Summer',
                    'fall' => 'Fall',
                    'winter' => 'Winter'
                ]
            ],
            'time_of_day' => [
                'label' => 'Time of Day',
                'type' => 'select',
                'options' => [
                    'dawn' => 'Dawn',
                    'morning' => 'Morning',
                    'afternoon' => 'Afternoon',
                    'dusk' => 'Dusk',
                    'night' => 'Night'
                ]
            ],
            'photographer' => [
                'label' => 'Photographer',
                'type' => 'text',
                'placeholder' => 'Photographer name'
            ],
            'mls_compliant' => [
                'label' => 'MLS Compliant',
                'type' => 'checkbox'
            ],
            'usage_rights' => [
                'label' => 'Usage Rights',
                'type' => 'select',
                'options' => [
                    'owned' => 'Fully Owned',
                    'licensed' => 'Licensed',
                    'mls_only' => 'MLS Use Only',
                    'restricted' => 'Restricted Use'
                ]
            ],
            'watermark_position' => [
                'label' => 'Watermark Position',
                'type' => 'select',
                'options' => [
                    'none' => 'No Watermark',
                    'bottom_right' => 'Bottom Right',
                    'bottom_left' => 'Bottom Left',
                    'center' => 'Center (Subtle)'
                ]
            ]
        ];
    }
    
    /**
     * Rename files on upload based on context
     */
    public function rename_on_upload($file) {
        // Only process allowed file types
        if (!in_array($file['type'], $this->allowed_types)) {
            return $file;
        }
        
        $upload_context = $this->get_upload_context();
        
        if ($upload_context['type'] === 'listing') {
            $new_name = $this->generate_filename($file, $upload_context);
            $file['name'] = $new_name;
        }
        
        return $file;
    }
    
    /**
     * Generate standardized filename
     */
    private function generate_filename($file, $context) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $listing_id = $context['post_id'];
        $listing = get_post($listing_id);
        
        // Get listing address for readable names
        $address = get_field('street_address', $listing_id);
        $address_slug = $address ? sanitize_title($address) : 'listing-' . $listing_id;
        
        // Determine image type from original filename
        $type = $this->detect_image_type($file['name']);
        
        // Get sequential index for this listing
        $index = $this->get_next_image_index($listing_id, $type);
        
        // Generate filename parts
        $parts = [
            $address_slug,
            $type,
            str_pad($index, 3, '0', STR_PAD_LEFT),
            date('Ymd')
        ];
        
        // Build final filename
        $new_name = implode('_', array_filter($parts)) . '.' . $extension;
        
        // Ensure filename is unique
        $new_name = wp_unique_filename(wp_upload_dir()['path'], $new_name);
        
        return $new_name;
    }
    
    /**
     * Detect image type from filename patterns
     */
    private function detect_image_type($filename) {
        $patterns = [
            'hero' => '/hero|main|featured/i',
            'exterior' => '/exterior|outside|front|curb/i',
            'interior' => '/interior|inside|room/i',
            'kitchen' => '/kitchen|cooking/i',
            'bedroom' => '/bedroom|bed|master/i',
            'bathroom' => '/bathroom|bath|shower/i',
            'living' => '/living|family|great/i',
            'dining' => '/dining|breakfast/i',
            'basement' => '/basement|lower/i',
            'garage' => '/garage|parking/i',
            'yard' => '/yard|garden|landscape/i',
            'pool' => '/pool|spa|hot.?tub/i',
            'aerial' => '/aerial|drone|overhead/i',
            'floorplan' => '/floor.?plan|layout|blueprint/i'
        ];
        
        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $filename)) {
                return $type;
            }
        }
        
        return 'general';
    }
    
    /**
     * Get next sequential index for image type
     */
    private function get_next_image_index($listing_id, $type) {
        global $wpdb;
        
        $pattern = '%_' . $type . '_%';
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->posts} 
            WHERE post_parent = %d 
            AND post_type = 'attachment'
            AND post_title LIKE %s
        ", $listing_id, $pattern));
        
        return $count + 1;
    }
    
    /**
     * Organize upload directory structure
     */
    public function organize_upload_directory($upload) {
        $context = $this->get_upload_context();
        
        if ($context['type'] === 'listing') {
            // Create listing-specific directory structure
            $listing_id = $context['post_id'];
            $year = date('Y');
            $month = date('m');
            
            $custom_dir = "/listings/{$year}/{$month}/listing-{$listing_id}";
            
            $upload['path'] = WP_CONTENT_DIR . '/uploads' . $custom_dir;
            $upload['url'] = WP_CONTENT_URL . '/uploads' . $custom_dir;
            $upload['subdir'] = $custom_dir;
            
            // Create directory if it doesn't exist
            if (!file_exists($upload['path'])) {
                wp_mkdir_p($upload['path']);
            }
        }
        
        return $upload;
    }
    
    /**
     * Add automatic metadata on upload
     */
    public function add_automatic_metadata($attachment_id) {
        $context = $this->get_upload_context();
        
        if ($context['type'] !== 'listing') {
            return;
        }
        
        $listing_id = $context['post_id'];
        $filename = get_attached_file($attachment_id);
        
        // Set attachment to listing
        wp_update_post([
            'ID' => $attachment_id,
            'post_parent' => $listing_id
        ]);
        
        // Extract and save EXIF data
        $exif_data = $this->extract_exif_data($filename);
        if ($exif_data) {
            update_post_meta($attachment_id, '_exif_data', $exif_data);
            
            // Save specific EXIF fields as separate meta
            if (isset($exif_data['DateTimeOriginal'])) {
                update_post_meta($attachment_id, '_photo_taken_date', $exif_data['DateTimeOriginal']);
            }
            if (isset($exif_data['GPSLatitude'])) {
                update_post_meta($attachment_id, '_photo_gps', [
                    'lat' => $exif_data['GPSLatitude'],
                    'lng' => $exif_data['GPSLongitude']
                ]);
            }
        }
        
        // Auto-detect and set property area
        $detected_type = $this->detect_image_type(basename($filename));
        update_post_meta($attachment_id, 'property_area', $detected_type);
        
        // Set listing reference
        update_post_meta($attachment_id, '_listing_id', $listing_id);
        
        // Add upload timestamp
        update_post_meta($attachment_id, '_upload_timestamp', current_time('timestamp'));
        
        // Add uploader info
        update_post_meta($attachment_id, '_uploaded_by', get_current_user_id());
        
        // Set initial processing status
        update_post_meta($attachment_id, '_processing_status', 'pending');
    }
    
    /**
     * Extract EXIF data from image
     */
    private function extract_exif_data($filename) {
        if (!function_exists('exif_read_data')) {
            return false;
        }
        
        $exif = @exif_read_data($filename);
        if (!$exif) {
            return false;
        }
        
        // Extract relevant EXIF data
        $relevant_fields = [
            'DateTimeOriginal',
            'Make',
            'Model',
            'ExposureTime',
            'FNumber',
            'ISO',
            'FocalLength',
            'GPSLatitude',
            'GPSLongitude'
        ];
        
        $data = [];
        foreach ($relevant_fields as $field) {
            if (isset($exif[$field])) {
                $data[$field] = $exif[$field];
            }
        }
        
        return $data;
    }
    
    /**
     * Add custom fields to media editor
     */
    public function add_custom_media_fields($form_fields, $post) {
        foreach ($this->metadata_fields as $key => $field) {
            $value = get_post_meta($post->ID, $key, true);
            
            $form_field = [
                'label' => $field['label'],
                'input' => 'html',
                'value' => $value
            ];
            
            switch ($field['type']) {
                case 'select':
                    $html = '<select name="attachments[' . $post->ID . '][' . $key . ']">';
                    $html .= '<option value="">— Select —</option>';
                    foreach ($field['options'] as $opt_value => $opt_label) {
                        $selected = selected($value, $opt_value, false);
                        $html .= '<option value="' . esc_attr($opt_value) . '"' . $selected . '>';
                        $html .= esc_html($opt_label) . '</option>';
                    }
                    $html .= '</select>';
                    $form_field['html'] = $html;
                    break;
                    
                case 'checkbox':
                    $checked = checked($value, '1', false);
                    $html = '<input type="checkbox" name="attachments[' . $post->ID . '][' . $key . ']" value="1"' . $checked . '>';
                    $form_field['html'] = $html;
                    break;
                    
                case 'text':
                default:
                    $placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
                    $html = '<input type="text" name="attachments[' . $post->ID . '][' . $key . ']" ';
                    $html .= 'value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '">';
                    $form_field['html'] = $html;
                    break;
            }
            
            $form_fields[$key] = $form_field;
        }
        
        // Add processing status indicator
        $status = get_post_meta($post->ID, '_processing_status', true);
        if ($status) {
            $form_fields['processing_status'] = [
                'label' => 'Processing Status',
                'input' => 'html',
                'html' => '<span class="status-' . esc_attr($status) . '">' . ucfirst($status) . '</span>'
            ];
        }
        
        return $form_fields;
    }
    
    /**
     * Save custom media fields
     */
    public function save_custom_media_fields($post, $attachment) {
        foreach ($this->metadata_fields as $key => $field) {
            if (isset($attachment[$key])) {
                update_post_meta($post['ID'], $key, sanitize_text_field($attachment[$key]));
            }
        }
        
        return $post;
    }
    
    /**
     * Get upload context
     */
    private function get_upload_context() {
        $context = [
            'type' => 'general',
            'post_id' => 0
        ];
        
        // Check if uploading to a specific post
        if (isset($_REQUEST['post_id'])) {
            $post_id = intval($_REQUEST['post_id']);
            $post_type = get_post_type($post_id);
            
            $context['post_id'] = $post_id;
            $context['type'] = $post_type;
        }
        
        // Check if in media modal with a post selected
        if (isset($_POST['post_id'])) {
            $post_id = intval($_POST['post_id']);
            $post_type = get_post_type($post_id);
            
            $context['post_id'] = $post_id;
            $context['type'] = $post_type;
        }
        
        return $context;
    }
    
    /**
     * Add admin page for bulk operations
     */
    public function add_admin_page() {
        add_media_page(
            'Media Organization',
            'Organization Tools',
            'manage_options',
            'hpt-media-organization',
            [$this, 'render_admin_page']
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>Media Organization Tools</h1>
            
            <div class="hpt-media-tools">
                <div class="tool-section">
                    <h2>Bulk Rename</h2>
                    <p>Rename all images for a listing following the standard pattern.</p>
                    <form method="post" action="">
                        <select name="listing_id">
                            <option value="">Select a listing...</option>
                            <?php
                            $listings = get_posts(['post_type' => 'listing', 'posts_per_page' => -1]);
                            foreach ($listings as $listing) {
                                echo '<option value="' . $listing->ID . '">' . esc_html($listing->post_title) . '</option>';
                            }
                            ?>
                        </select>
                        <button type="submit" class="button button-primary">Process Images</button>
                    </form>
                </div>
                
                <div class="tool-section">
                    <h2>Orphaned Media</h2>
                    <p>Find and clean up media not attached to any listing.</p>
                    <button class="button" id="find-orphaned">Find Orphaned Media</button>
                    <div id="orphaned-results"></div>
                </div>
                
                <div class="tool-section">
                    <h2>Statistics</h2>
                    <?php $this->display_media_statistics(); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display media statistics
     */
    private function display_media_statistics() {
        global $wpdb;
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'");
        $organized = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_listing_id'");
        $unprocessed = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_processing_status' AND meta_value = 'pending'");
        
        echo '<ul>';
        echo '<li>Total media files: ' . number_format($total) . '</li>';
        echo '<li>Organized files: ' . number_format($organized) . '</li>';
        echo '<li>Pending processing: ' . number_format($unprocessed) . '</li>';
        echo '</ul>';
    }
    
    /**
     * Register bulk actions
     */
    public function register_bulk_actions($actions) {
        $actions['organize_media'] = 'Organize Selected Media';
        $actions['add_watermark'] = 'Add Watermark';
        $actions['generate_alt_text'] = 'Generate Alt Text';
        return $actions;
    }
    
    /**
     * Handle bulk actions
     */
    public function handle_bulk_actions($redirect_to, $action, $post_ids) {
        if ($action === 'organize_media') {
            foreach ($post_ids as $post_id) {
                $this->organize_single_media($post_id);
            }
            $redirect_to = add_query_arg('organized', count($post_ids), $redirect_to);
        }
        
        return $redirect_to;
    }
    
    /**
     * Organize single media item
     */
    private function organize_single_media($attachment_id) {
        $parent_id = wp_get_post_parent_id($attachment_id);
        
        if ($parent_id && get_post_type($parent_id) === 'listing') {
            // Re-run metadata extraction
            $this->add_automatic_metadata($attachment_id);
            
            // Generate alt text
            $this->generate_alt_text($attachment_id);
            
            // Mark as processed
            update_post_meta($attachment_id, '_processing_status', 'complete');
        }
    }
    
    /**
     * Generate alt text based on metadata
     */
    private function generate_alt_text($attachment_id) {
        $parent_id = wp_get_post_parent_id($attachment_id);
        if (!$parent_id) return;
        
        $property_area = get_post_meta($attachment_id, 'property_area', true);
        $address = get_field('street_address', $parent_id);
        
        $alt_parts = [];
        
        if ($property_area && isset($this->metadata_fields['property_area']['options'][$property_area])) {
            $alt_parts[] = $this->metadata_fields['property_area']['options'][$property_area];
        }
        
        if ($address) {
            $alt_parts[] = 'at ' . $address;
        }
        
        if (!empty($alt_parts)) {
            $alt_text = implode(' ', $alt_parts);
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        }
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'media_page_hpt-media-organization') {
            return;
        }
        
        wp_enqueue_script(
            'hpt-media-admin',
            plugin_dir_url(HP_PLUGIN_FILE) . 'assets/js/admin-media-organization.js',
            ['jquery', 'jquery-ui-draggable', 'jquery-ui-droppable'],
            '1.0.0',
            true
        );

        wp_enqueue_style(
            'hpt-media-admin',
            plugin_dir_url(HP_PLUGIN_FILE) . 'assets/css/admin-media-organization.css',
            [],
            '1.0.0'
        );
        
        wp_localize_script('hpt-media-admin', 'hptMedia', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hpt_media_nonce')
        ]);
    }
    
    /**
     * AJAX handler for batch processing
     */
    public function ajax_process_batch() {
        check_ajax_referer('hpt_media_nonce', 'nonce');
        
        $batch_size = 10;
        $offset = intval($_POST['offset'] ?? 0);
        
        $attachments = get_posts([
            'post_type' => 'attachment',
            'posts_per_page' => $batch_size,
            'offset' => $offset,
            'meta_query' => [
                [
                    'key' => '_processing_status',
                    'value' => 'pending'
                ]
            ]
        ]);
        
        $processed = 0;
        foreach ($attachments as $attachment) {
            $this->organize_single_media($attachment->ID);
            $processed++;
        }
        
        wp_send_json_success([
            'processed' => $processed,
            'complete' => count($attachments) < $batch_size
        ]);
    }
}