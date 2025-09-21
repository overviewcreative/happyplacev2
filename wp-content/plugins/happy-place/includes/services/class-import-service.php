<?php
/**
 * Import Service - CSV & Bulk Operations
 * 
 * Handles CSV importing, field mapping, progress tracking, and bulk data operations.
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
 * Import Service Class
 * 
 * Handles CSV import and bulk operations as specified in services.md
 */
class ImportService extends Service {
    
    /**
     * Service name
     */
    protected string $name = 'import_service';
    
    /**
     * Service version
     */
    protected string $version = '4.0.0';
    
    /**
     * Session key prefix
     */
    private string $session_prefix = 'hp_import_session_';
    
    /**
     * Maximum file size (in bytes)
     */
    private int $max_file_size = 10485760; // 10MB
    
    /**
     * Allowed file types
     */
    private array $allowed_types = ['csv', 'txt'];
    
    /**
     * Mapping templates cache
     */
    private array $mapping_templates = [];
    
    /**
     * Default field mappings
     */
    private array $default_mappings = [];
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        // Load mapping templates
        $this->load_mapping_templates();
        $this->load_default_mappings();
        
        // Register AJAX handlers
        add_action('wp_ajax_hp_upload_import_file', [$this, 'ajax_upload_file']);
        add_action('wp_ajax_hp_validate_csv', [$this, 'ajax_validate_csv']);
        add_action('wp_ajax_hp_get_csv_sample', [$this, 'ajax_get_csv_sample']);
        add_action('wp_ajax_hp_auto_map_fields', [$this, 'ajax_auto_map_fields']);
        add_action('wp_ajax_hp_save_mapping_template', [$this, 'ajax_save_mapping_template']);
        add_action('wp_ajax_hp_get_mapping_templates', [$this, 'ajax_get_mapping_templates']);
        add_action('wp_ajax_hp_process_import', [$this, 'ajax_process_import']);
        add_action('wp_ajax_hp_get_import_progress', [$this, 'ajax_get_progress']);
        
        $this->initialized = true;
        $this->log('Import Service initialized successfully');
    }
    
    /**
     * Import CSV file
     * 
     * @param string $file_path Path to CSV file
     * @param array $mapping Field mapping configuration
     * @return array|WP_Error Import results or error
     */
    public function import_csv(string $file_path, array $mapping) {
        // Validate file
        $validation = $this->validate_csv($file_path);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Get CSV headers and data
        $csv_data = $this->parse_csv($file_path);
        if (is_wp_error($csv_data)) {
            return $csv_data;
        }
        
        // Initialize import session
        $total_rows = count($csv_data['data']);
        $session_id = $this->init_import_session($total_rows);
        
        // Process data in batches
        $batch_size = 50; // Process 50 records at a time
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
            'session_id' => $session_id
        ];
        
        // Initialize listing service
        if (!class_exists('HappyPlace\\Services\\ListingService')) {
            return new \WP_Error('service_missing', 'ListingService not available');
        }
        
        $listing_service = new \HappyPlace\Services\ListingService();
        
        for ($i = 0; $i < $total_rows; $i += $batch_size) {
            $batch = array_slice($csv_data['data'], $i, $batch_size);
            
            foreach ($batch as $row_index => $row) {
                $actual_row = $i + $row_index + 1; // +1 for header row
                
                try {
                    // Map CSV row to listing data
                    $listing_data = $this->map_csv_row($row, $csv_data['headers'], $mapping);
                    
                    // Debug logging
                    if (HP_DEBUG) {
                        $this->log('Row ' . $actual_row . ' mapped data: ' . print_r($listing_data, true), 'debug', 'IMPORT');
                    }
                    
                    // Skip empty rows - check for post_title or title
                    if (empty($listing_data['title']) && empty($listing_data['post_title'])) {
                        if (HP_DEBUG) {
                            $this->log('Row ' . $actual_row . ' skipped - no title found', 'debug', 'IMPORT');
                        }
                        $results['skipped']++;
                        continue;
                    }
                    
                    // Check for duplicates and update if found
                    $existing_listing = $this->find_existing_listing($listing_data);
                    if ($existing_listing) {
                        // Update existing listing
                        $listing_data['ID'] = $existing_listing->ID;
                        $updated_id = $this->update_existing_listing($existing_listing->ID, $listing_data);
                        
                        if ($updated_id && !is_wp_error($updated_id)) {
                            $this->log("Updated existing listing {$updated_id} for row {$actual_row}", 'info', 'IMPORT');
                            $results['updated']++;
                        } else {
                            $this->log_import_error($actual_row, 'Failed to update existing listing: ' . ($updated_id ? $updated_id->get_error_message() : 'Unknown error'));
                            $results['failed']++;
                        }
                        continue;
                    }
                    
                    // Create listing
                    if (HP_DEBUG) {
                        $this->log('Creating listing for row ' . $actual_row, 'debug', 'IMPORT');
                    }
                    
                    $listing_id = $listing_service->create_listing($listing_data);
                    
                    if (is_wp_error($listing_id)) {
                        $this->log_import_error($actual_row, $listing_id->get_error_message());
                        $results['failed']++;
                        $results['errors'][] = [
                            'row' => $actual_row,
                            'error' => $listing_id->get_error_message(),
                            'data' => $listing_data
                        ];
                        
                        if (HP_DEBUG) {
                            $this->log('Row ' . $actual_row . ' failed: ' . $listing_id->get_error_message(), 'error', 'IMPORT');
                        }
                    } else {
                        $results['success']++;
                        if (HP_DEBUG) {
                            $this->log('Row ' . $actual_row . ' created listing ID: ' . $listing_id, 'debug', 'IMPORT');
                        }
                    }
                    
                } catch (\Exception $e) {
                    $this->log_import_error($actual_row, $e->getMessage());
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $actual_row,
                        'error' => $e->getMessage(),
                        'data' => $row ?? []
                    ];
                }
            }
            
            // Update progress
            $processed = min($i + $batch_size, $total_rows);
            $this->update_progress($session_id, $processed, $total_rows);
            
            // Allow for memory cleanup
            if ($i % 200 === 0) {
                wp_cache_flush();
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
        }
        
        // Complete import session
        if (HP_DEBUG) {
            $this->log('Import completed. Final results: ' . print_r($results, true), 'debug', 'IMPORT');
        }
        
        $this->complete_import_session($session_id, $results);
        
        return $results;
    }
    
    /**
     * Validate CSV file
     * 
     * @param string $file_path Path to CSV file
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    public function validate_csv(string $file_path) {
        // Check if file exists
        if (!file_exists($file_path)) {
            return new \WP_Error('file_not_found', 'CSV file not found');
        }
        
        // Check file size
        $file_size = filesize($file_path);
        if ($file_size > $this->max_file_size) {
            return new \WP_Error('file_too_large', sprintf('File size exceeds maximum of %s', size_format($this->max_file_size)));
        }
        
        // Check file extension
        $file_info = pathinfo($file_path);
        if (!in_array(strtolower($file_info['extension']), $this->allowed_types)) {
            return new \WP_Error('invalid_file_type', 'Invalid file type. Only CSV files are allowed.');
        }
        
        // Try to parse first few lines
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return new \WP_Error('file_read_error', 'Unable to read CSV file');
        }
        
        // Check for valid CSV format
        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 2) {
            fclose($handle);
            return new \WP_Error('invalid_csv', 'Invalid CSV format or insufficient columns');
        }
        
        fclose($handle);
        
        return true;
    }
    
    /**
     * Get CSV headers
     * 
     * @param string $file_path Path to CSV file
     * @return array|WP_Error Headers array or error
     */
    public function get_csv_headers(string $file_path) {
        $validation = $this->validate_csv($file_path);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return new \WP_Error('file_read_error', 'Unable to read CSV file');
        }
        
        $headers = fgetcsv($handle);
        fclose($handle);
        
        return array_map('trim', $headers);
    }
    
    /**
     * Auto-map CSV fields to listing fields
     * 
     * @param array $csv_headers CSV column headers
     * @return array Suggested field mapping
     */
    public function auto_map_fields(array $csv_headers): array {
        $mapping = [];
        
        foreach ($csv_headers as $index => $header) {
            $header_lower = strtolower(trim($header));
            $mapped_field = null;
            
            // Try to find matching field
            foreach ($this->default_mappings as $csv_pattern => $listing_field) {
                if (stripos($header_lower, $csv_pattern) !== false) {
                    $mapped_field = $listing_field;
                    break;
                }
            }
            
            $mapping[$index] = [
                'csv_header' => $header,
                'listing_field' => $mapped_field,
                'suggested' => !is_null($mapped_field)
            ];
        }
        
        return $mapping;
    }
    
    /**
     * Save mapping template
     * 
     * @param string $name Template name
     * @param array $mapping Field mapping
     * @return bool Success status
     */
    public function save_mapping_template(string $name, array $mapping): bool {
        $templates = get_option('hp_import_mapping_templates', []);
        $templates[$name] = [
            'name' => $name,
            'mapping' => $mapping,
            'created' => current_time('mysql'),
            'created_by' => get_current_user_id()
        ];
        
        return update_option('hp_import_mapping_templates', $templates);
    }
    
    /**
     * Get mapping templates
     * 
     * @return array Available mapping templates
     */
    public function get_mapping_templates(): array {
        return get_option('hp_import_mapping_templates', []);
    }
    
    /**
     * Initialize import session
     * 
     * @param int $total_rows Total number of rows to process
     * @return string Session ID
     */
    public function init_import_session(int $total_rows): string {
        $session_id = uniqid('import_');
        
        $session_data = [
            'id' => $session_id,
            'total_rows' => $total_rows,
            'processed' => 0,
            'status' => 'in_progress',
            'started' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'errors' => []
        ];
        
        set_transient($this->session_prefix . $session_id, $session_data, 3600); // 1 hour
        
        return $session_id;
    }
    
    /**
     * Update import progress
     * 
     * @param string $session_id Session ID
     * @param int $processed Number of processed rows
     * @param int $total Total rows
     * @return bool Success status
     */
    public function update_progress(string $session_id, int $processed, int $total): bool {
        $session_data = get_transient($this->session_prefix . $session_id);
        
        if (!$session_data) {
            return false;
        }
        
        $session_data['processed'] = $processed;
        $session_data['progress_percent'] = ($processed / $total) * 100;
        $session_data['updated'] = current_time('mysql');
        
        return set_transient($this->session_prefix . $session_id, $session_data, 3600);
    }
    
    /**
     * Complete import session
     * 
     * @param string $session_id Session ID
     * @param array $results Import results
     * @return bool Success status
     */
    public function complete_import_session(string $session_id, array $results): bool {
        $session_data = get_transient($this->session_prefix . $session_id);
        
        if (!$session_data) {
            return false;
        }
        
        $session_data['status'] = 'completed';
        $session_data['completed'] = current_time('mysql');
        $session_data['results'] = $results;
        
        // Store completed session for 24 hours
        return set_transient($this->session_prefix . $session_id, $session_data, 86400);
    }
    
    /**
     * Get import progress
     * 
     * @param string $session_id Session ID
     * @return array|false Session data or false if not found
     */
    public function get_import_progress(string $session_id) {
        return get_transient($this->session_prefix . $session_id);
    }
    
    /**
     * Log import error
     * 
     * @param int $row Row number
     * @param string $error Error message
     * @return void
     */
    public function log_import_error(int $row, string $error): void {
        $log_entry = sprintf('[Row %d] %s', $row, $error);
        
        if (HP_DEBUG) {
            error_log('Happy Place Import Error: ' . $log_entry);
        }
        
        // Could also save to database for detailed reporting
        do_action('hp_import_error', $row, $error);
    }
    
    /**
     * Get import report
     * 
     * @param string $session_id Session ID
     * @return array|WP_Error Import report or error
     */
    public function get_import_report(string $session_id) {
        $session_data = get_transient($this->session_prefix . $session_id);
        
        if (!$session_data) {
            return new \WP_Error('session_not_found', 'Import session not found');
        }
        
        $report = [
            'session_id' => $session_id,
            'status' => $session_data['status'],
            'started' => $session_data['started'],
            'completed' => $session_data['completed'] ?? null,
            'total_rows' => $session_data['total_rows'],
            'processed' => $session_data['processed'],
            'progress_percent' => $session_data['progress_percent'] ?? 0
        ];
        
        if (isset($session_data['results'])) {
            $report['results'] = $session_data['results'];
        }
        
        return $report;
    }
    
    /**
     * Parse CSV file
     * 
     * @param string $file_path Path to CSV file
     * @return array|WP_Error Parsed data or error
     */
    private function parse_csv(string $file_path) {
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return new \WP_Error('file_read_error', 'Unable to read CSV file');
        }
        
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return new \WP_Error('no_headers', 'CSV file has no headers');
        }
        
        $data = [];
        while (($row = fgetcsv($handle)) !== false) {
            // Ensure row has same number of columns as headers
            $row = array_pad($row, count($headers), '');
            $data[] = array_combine($headers, $row);
        }
        
        fclose($handle);
        
        return [
            'headers' => $headers,
            'data' => $data
        ];
    }
    
    /**
     * Map CSV row to listing data
     * 
     * @param array $row CSV row data
     * @param array $headers CSV headers
     * @param array $mapping Field mapping
     * @return array Mapped listing data
     */
    private function map_csv_row(array $row, array $headers, array $mapping): array {
        $listing_data = [];
        
        foreach ($mapping as $csv_index => $field_config) {
            if (empty($field_config['listing_field'])) {
                continue;
            }
            
            $csv_header = $headers[$csv_index] ?? '';
            $value = $row[$csv_header] ?? '';
            $listing_field = $field_config['listing_field'];
            
            // Apply data transformation if specified
            if (!empty($field_config['transform'])) {
                $value = $this->transform_field_value($value, $field_config['transform']);
            }
            
            // Special handling for full address parsing
            if ($listing_field === 'full_address_to_parse' && !empty($value)) {
                $parsed_address = $this->parse_full_address($value);
                if ($parsed_address) {
                    $listing_data = array_merge($listing_data, $parsed_address);
                }
                continue;
            }
            
            // Handle nested fields (like address)
            if (strpos($listing_field, '.') !== false) {
                $parts = explode('.', $listing_field);
                if (count($parts) === 2) {
                    if (!isset($listing_data[$parts[0]])) {
                        $listing_data[$parts[0]] = [];
                    }
                    $listing_data[$parts[0]][$parts[1]] = $value;
                }
            } else {
                $listing_data[$listing_field] = $value;
            }
        }
        
        // Transform field names to match what ListingService expects
        $this->transform_field_names($listing_data);
        
        // Set default values
        if (empty($listing_data['status'])) {
            $listing_data['status'] = 'draft';
        }
        
        if (empty($listing_data['author_id'])) {
            $listing_data['author_id'] = get_current_user_id();
        }
        
        return $listing_data;
    }
    
    /**
     * Transform field names to match ListingService expectations
     * 
     * @param array &$listing_data Reference to listing data array
     * @return void
     */
    private function transform_field_names(array &$listing_data): void {
        $field_mapping = [
            'post_title' => 'title',
            'post_content' => 'description',
            'property_description' => 'description',
            'property_title' => 'marketing_title',
        ];
        
        foreach ($field_mapping as $import_field => $service_field) {
            if (isset($listing_data[$import_field])) {
                // Move the value to the expected field name
                $listing_data[$service_field] = $listing_data[$import_field];
                
                // Keep the original field too for ACF processing
                // Don't unset the original as it may be needed for ACF
            }
        }
        
        // Ensure we have a title from some source
        if (empty($listing_data['title'])) {
            if (!empty($listing_data['post_title'])) {
                $listing_data['title'] = $listing_data['post_title'];
            } elseif (!empty($listing_data['property_title'])) {
                $listing_data['title'] = $listing_data['property_title'];
            } else {
                // Generate a title from address or MLS if available
                if (!empty($listing_data['street_name'])) {
                    $listing_data['title'] = $listing_data['street_name'];
                    if (!empty($listing_data['city'])) {
                        $listing_data['title'] .= ', ' . $listing_data['city'];
                    }
                } elseif (!empty($listing_data['mls_number'])) {
                    $listing_data['title'] = 'Property ' . $listing_data['mls_number'];
                }
            }
        }
        
        // Ensure we have a description from some source
        if (empty($listing_data['description'])) {
            if (!empty($listing_data['property_description'])) {
                $listing_data['description'] = $listing_data['property_description'];
            } elseif (!empty($listing_data['post_content'])) {
                $listing_data['description'] = $listing_data['post_content'];
            }
        }
    }
    
    /**
     * Transform field value based on transformation rules
     * 
     * @param mixed $value Original value
     * @param array $transform Transformation rules
     * @return mixed Transformed value
     */
    private function transform_field_value($value, array $transform) {
        switch ($transform['type'] ?? '') {
            case 'price':
                // Remove currency symbols and convert to number
                $value = preg_replace('/[^0-9.]/', '', $value);
                return floatval($value);
                
            case 'boolean':
                $true_values = ['yes', 'y', 'true', '1', 'on'];
                return in_array(strtolower(trim($value)), $true_values);
                
            case 'date':
                $timestamp = strtotime($value);
                return $timestamp ? date('Y-m-d', $timestamp) : '';
                
            case 'phone':
                // Clean phone number
                return preg_replace('/[^0-9]/', '', $value);
                
            case 'mapping':
                // Map values using provided mapping
                return $transform['map'][strtolower(trim($value))] ?? $value;
                
            default:
                return trim($value);
        }
    }
    
    /**
     * Find existing listing by MLS number or address
     * 
     * @param array $listing_data Listing data
     * @return \WP_Post|null Existing listing post or null
     */
    private function find_existing_listing(array $listing_data): ?\WP_Post {
        $args = [
            'post_type' => 'listing',
            'post_status' => ['publish', 'draft', 'pending'],
            'posts_per_page' => 1,
            'meta_query' => []
        ];
        
        // Check by MLS number first (most reliable)
        if (!empty($listing_data['mls_number'])) {
            $args['meta_query'][] = [
                'key' => 'mls_number',
                'value' => $listing_data['mls_number'],
                'compare' => '='
            ];
            
            $query = new \WP_Query($args);
            if ($query->have_posts()) {
                return $query->posts[0];
            }
        }
        
        // Check by full address components if available
        if (!empty($listing_data['street_number']) && !empty($listing_data['street_name'])) {
            $args['meta_query'] = [
                'relation' => 'AND',
                [
                    'key' => 'street_number',
                    'value' => $listing_data['street_number'],
                    'compare' => '='
                ],
                [
                    'key' => 'street_name',
                    'value' => $listing_data['street_name'],
                    'compare' => '='
                ]
            ];
            
            // Add city if available for more precise matching
            if (!empty($listing_data['city'])) {
                $args['meta_query'][] = [
                    'key' => 'city',
                    'value' => $listing_data['city'],
                    'compare' => '='
                ];
            }
            
            $query = new \WP_Query($args);
            if ($query->have_posts()) {
                return $query->posts[0];
            }
        }
        
        // Fallback: Check by legacy address field if available
        elseif (!empty($listing_data['address']['street_address'])) {
            $args['meta_query'] = [
                [
                    'key' => 'street_address',
                    'value' => $listing_data['address']['street_address'],
                    'compare' => '='
                ]
            ];
            
            $query = new \WP_Query($args);
            if ($query->have_posts()) {
                return $query->posts[0];
            }
        }
        
        return null;
    }
    
    /**
     * Update existing listing with new data
     * 
     * @param int $listing_id Existing listing ID
     * @param array $listing_data New listing data
     * @return int|\WP_Error Updated listing ID or error
     */
    private function update_existing_listing(int $listing_id, array $listing_data) {
        // Prepare post data for update
        $post_data = [
            'ID' => $listing_id,
            'post_type' => 'listing',
            'post_status' => 'publish'
        ];
        
        // Update basic post fields if provided
        if (!empty($listing_data['post_title'])) {
            $post_data['post_title'] = $listing_data['post_title'];
        }
        
        if (!empty($listing_data['post_content'])) {
            $post_data['post_content'] = $listing_data['post_content'];
        }
        
        // Update the post
        $result = wp_update_post($post_data, true);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Update ACF fields
        foreach ($listing_data as $field_name => $field_value) {
            // Skip WordPress core fields
            if (in_array($field_name, ['ID', 'post_title', 'post_content', 'post_status', 'post_type'])) {
                continue;
            }
            
            // Update ACF field
            update_field($field_name, $field_value, $listing_id);
        }
        
        // Update taxonomies if provided
        if (!empty($listing_data['status'])) {
            wp_set_object_terms($listing_id, $listing_data['status'], 'listing_status');
        }
        
        if (!empty($listing_data['property_type'])) {
            wp_set_object_terms($listing_id, $listing_data['property_type'], 'property_type');
        }
        
        do_action('hp_listing_updated', $listing_id, $listing_data);
        
        return $listing_id;
    }
    
    /**
     * Check if listing is duplicate (legacy method - kept for compatibility)
     * 
     * @param array $listing_data Listing data
     * @return bool True if duplicate found
     */
    private function is_duplicate(array $listing_data): bool {
        return $this->find_existing_listing($listing_data) !== null;
    }
    
    /**
     * Load mapping templates from database
     */
    private function load_mapping_templates(): void {
        $this->mapping_templates = get_option('hp_import_mapping_templates', []);
    }
    
    /**
     * Load default field mappings
     */
    private function load_default_mappings(): void {
        $this->default_mappings = [
            // Basic Post Information
            'title' => 'post_title',
            'listing title' => 'post_title',
            'property title' => 'post_title',
            'name' => 'post_title',
            
            'description' => 'post_content',
            'property description' => 'property_description',
            'listing description' => 'property_description',
            'content' => 'post_content',
            
            // Core Listing Fields
            'marketing title' => 'property_title',
            'property highlights' => 'property_highlights',
            'key highlights' => 'property_highlights',
            'highlights' => 'property_highlights',
            
            // Pricing & Financial
            'price' => 'listing_price',
            'listing price' => 'listing_price',
            'asking price' => 'listing_price',
            'sale price' => 'listing_price',
            'cost' => 'listing_price',
            
            'property taxes' => 'property_taxes',
            'taxes' => 'property_taxes',
            'annual property tax' => 'property_taxes',
            'tax' => 'property_taxes',
            
            'hoa' => 'hoa_fees',
            'hoa fees' => 'hoa_fees',
            'homeowner fees' => 'hoa_fees',
            'association fees' => 'hoa_fees',
            
            'buyer commission' => 'buyer_commission',
            'commission' => 'buyer_commission',
            'buyer agent commission' => 'buyer_commission',
            
            'insurance' => 'estimated_insurance',
            'estimated insurance' => 'estimated_insurance',
            'monthly insurance' => 'estimated_insurance',
            'est monthly insurance' => 'estimated_insurance',
            
            'utilities' => 'estimated_utilities',
            'estimated utilities' => 'estimated_utilities',
            'monthly utilities' => 'estimated_utilities',
            'est monthly utilities' => 'estimated_utilities',
            
            'tax id' => 'tax_id',
            'parcel number' => 'tax_id',
            'tax parcel' => 'tax_id',
            
            'price per sqft' => 'price_per_sqft',
            'price per sq ft' => 'price_per_sqft',
            'cost per sqft' => 'price_per_sqft',
            
            // Property Details
            'bedrooms' => 'bedrooms',
            'beds' => 'bedrooms',
            'bedroom' => 'bedrooms',
            'bed' => 'bedrooms',
            'br' => 'bedrooms',
            
            'bathrooms full' => 'bathrooms_full',
            'full baths' => 'bathrooms_full',
            'full bathrooms' => 'bathrooms_full',
            'full bath' => 'bathrooms_full',
            
            'bathrooms half' => 'bathrooms_half',
            'half baths' => 'bathrooms_half',
            'half bathrooms' => 'bathrooms_half',
            'half bath' => 'bathrooms_half',
            'powder rooms' => 'bathrooms_half',
            
            // Legacy bathroom mapping (will be split into full/half if needed)
            'bathrooms' => 'bathrooms_full',
            'baths' => 'bathrooms_full',
            'bathroom' => 'bathrooms_full',
            'bath' => 'bathrooms_full',
            'ba' => 'bathrooms_full',
            
            'square feet' => 'square_feet',
            'sqft' => 'square_feet',
            'sq ft' => 'square_feet',
            'size' => 'square_feet',
            'square footage' => 'square_feet',
            'floor area' => 'square_feet',
            
            'lot size acres' => 'lot_size_acres',
            'acres' => 'lot_size_acres',
            'lot acres' => 'lot_size_acres',
            'acreage' => 'lot_size_acres',
            
            'lot size sqft' => 'lot_size_sqft',
            'lot size' => 'lot_size_sqft',
            'lot' => 'lot_size_sqft',
            'lot square feet' => 'lot_size_sqft',
            'lot sq ft' => 'lot_size_sqft',
            
            'year built' => 'year_built',
            'built' => 'year_built',
            'construction year' => 'year_built',
            'built year' => 'year_built',
            'yr built' => 'year_built',
            
            'garage spaces' => 'garage_spaces',
            'garage' => 'garage_spaces',
            'car garage' => 'garage_spaces',
            'parking spaces' => 'garage_spaces',
            
            'architectural style' => 'property_style',
            'style' => 'property_style',
            'home style' => 'property_style',
            'architecture' => 'property_style',
            'structure type' => 'property_style',
            
            'listing date' => 'listing_date',
            'date listed' => 'listing_date',
            'listed' => 'listing_date',
            'list date' => 'listing_date',
            
            'days on market' => 'days_on_market',
            'dom' => 'days_on_market',
            'days listed' => 'days_on_market',
            'market days' => 'days_on_market',
            'cdom' => 'days_on_market',
            
            'stories' => 'stories',
            'levels' => 'stories',
            'floors' => 'stories',
            'story' => 'stories',
            'levels/stories' => 'stories',
            
            'condition' => 'condition',
            'property condition' => 'condition',
            'home condition' => 'condition',
            
            'featured' => 'is_featured',
            'is featured' => 'is_featured',
            'featured listing' => 'is_featured',
            
            // Address Fields
            'street number' => 'street_number',
            'number' => 'street_number',
            'house number' => 'street_number',
            'address number' => 'street_number',
            
            'street dir prefix' => 'street_dir_prefix',
            'prefix' => 'street_dir_prefix',
            'direction prefix' => 'street_dir_prefix',
            
            'street name' => 'street_name',
            'street' => 'street_name',
            'road' => 'street_name',
            'road name' => 'street_name',
            
            'street type' => 'street_type',
            'street suffix' => 'street_type',
            'suffix' => 'street_type',
            'road type' => 'street_type',
            
            'street dir suffix' => 'street_dir_suffix',
            'direction suffix' => 'street_dir_suffix',
            'suffix direction' => 'street_dir_suffix',
            
            'unit' => 'unit_number',
            'unit number' => 'unit_number',
            'apt' => 'unit_number',
            'apartment' => 'unit_number',
            'suite' => 'unit_number',
            
            // This is the key one for full address parsing
            'address' => 'full_address_to_parse',
            'full address' => 'full_address_to_parse',
            'street address' => 'full_address_to_parse',
            'property address' => 'full_address_to_parse',
            'complete address' => 'full_address_to_parse',
            'full street address' => 'full_address_to_parse',
            
            'city' => 'city',
            'municipality' => 'city',
            'town' => 'city',
            
            'state' => 'state',
            'province' => 'state',
            'region' => 'state',
            
            'zip' => 'zip_code',
            'zip code' => 'zip_code',
            'postal code' => 'zip_code',
            'postal' => 'zip_code',
            
            'county' => 'county',
            'parish' => 'county',
            
            'parcel number' => 'parcel_number',
            'parcel' => 'parcel_number',
            'parcel id' => 'parcel_number',
            'tax parcel' => 'parcel_number',
            
            'subdivision' => 'subdivision',
            'neighborhood' => 'subdivision',
            'development' => 'subdivision',
            'community' => 'subdivision',
            
            'school district' => 'school_district',
            'district' => 'school_district',
            'schools' => 'school_district',
            
            'zoning' => 'zoning',
            'zone' => 'zoning',
            'zoned' => 'zoning',
            
            'flood zone' => 'flood_zone',
            'flood' => 'flood_zone',
            'fema zone' => 'flood_zone',
            
            // Location & Coordinates
            'latitude' => 'latitude',
            'lat' => 'latitude',
            'geo lat' => 'latitude',
            
            'longitude' => 'longitude',
            'lng' => 'longitude',
            'lon' => 'longitude',
            'geo lng' => 'longitude',
            'geo lon' => 'longitude',
            
            // Listing Information
            'mls' => 'mls_number',
            'mls number' => 'mls_number',
            'mls #' => 'mls_number',
            'mls id' => 'mls_number',
            'listing id' => 'mls_number',
            'multiple listing' => 'mls_number',
            
            'status' => 'status',
            'listing status' => 'status',
            'property status' => 'status',
            
            'sold date' => 'sold_date',
            'date sold' => 'sold_date',
            'close date' => 'sold_date',
            'closing date' => 'sold_date',
            
            'close price' => 'sold_price',
            'sold price' => 'sold_price',
            'sale price' => 'sold_price',
            'final price' => 'sold_price',
            
            'basement' => 'basement',
            'basement yn' => 'basement',
            'has basement' => 'basement',
            
            'fireplaces' => 'fireplaces',
            'fireplaces total' => 'fireplaces',
            'fireplace count' => 'fireplaces',
            
            // Agent & Office Information
            'listing agent' => 'listing_agent',
            'agent' => 'listing_agent',
            'primary agent' => 'listing_agent',
            'lead agent' => 'listing_agent',
            
            'co listing agent' => 'co_listing_agent',
            'co agent' => 'co_listing_agent',
            'second agent' => 'co_listing_agent',
            
            'listing office' => 'listing_office',
            'office' => 'listing_office',
            'brokerage' => 'listing_office',
            'company' => 'listing_office',
            
            'office phone' => 'listing_office_phone',
            'listing office phone' => 'listing_office_phone',
            'broker phone' => 'listing_office_phone',
            
            // Construction & Features
            'builder' => 'builder',
            'contractor' => 'builder',
            'construction company' => 'builder',
            
            'roof type' => 'roof_type',
            'roof' => 'roof_type',
            'roofing' => 'roof_type',
            
            'foundation' => 'foundation_type',
            'foundation type' => 'foundation_type',
            
            'exterior materials' => 'exterior_materials',
            'exterior' => 'exterior_materials',
            'siding' => 'exterior_materials',
            
            'flooring' => 'flooring_types',
            'flooring types' => 'flooring_types',
            'floor types' => 'flooring_types',
            'floors' => 'flooring_types',
            
            'heating system' => 'heating_system',
            'heating' => 'heating_system',
            'heat' => 'heating_system',
            
            'heating fuel' => 'heating_fuel',
            'heat fuel' => 'heating_fuel',
            
            'cooling system' => 'cooling_system',
            'cooling' => 'cooling_system',
            'ac' => 'cooling_system',
            'air conditioning' => 'air_conditioning',
            
            'cooling fuel' => 'cooling_fuel',
            'ac fuel' => 'cooling_fuel',
            
            'water source' => 'water_source',
            'water' => 'water_source',
            
            'sewer system' => 'sewer_system',
            'sewer' => 'sewer_system',
            'septic' => 'sewer_system',
            
            'electric service' => 'electric_service',
            'electrical' => 'electric_service',
            'electric' => 'electric_service',
            
            'hot water' => 'hot_water',
            'water heater' => 'water_heater',
            'hw' => 'hot_water',
            
            'construction materials' => 'construction_materials',
            'materials' => 'construction_materials',
            
            // Features & Amenities
            'interior features' => 'interior_features',
            'interior' => 'interior_features',
            'inside features' => 'interior_features',
            
            'exterior features' => 'exterior_features',
            'outside features' => 'exterior_features',
            'yard features' => 'exterior_features',
            
            'property features' => 'property_features',
            'features' => 'property_features',
            'amenities' => 'property_features',
            
            // Pool & Spa
            'pool' => 'has_pool',
            'swimming pool' => 'has_pool',
            'has pool' => 'has_pool',
            
            'pool type' => 'pool_type',
            'pool style' => 'pool_type',
            
            'spa' => 'has_spa',
            'hot tub' => 'has_spa',
            'jacuzzi' => 'has_spa',
            'has spa' => 'has_spa',
            
            'garage type' => 'garage_type',
            'garage style' => 'garage_type',
            'parking type' => 'garage_type',
            
            // Media & Virtual Tours
            'primary photo' => 'primary_photo',
            'main photo' => 'primary_photo',
            'featured photo' => 'primary_photo',
            
            'photo gallery' => 'photo_gallery',
            'photos' => 'photo_gallery',
            'images' => 'photo_gallery',
            'gallery' => 'photo_gallery',
            
            'virtual tour' => 'virtual_tour_url',
            'virtual tour url' => 'virtual_tour_url',
            'tour link' => 'virtual_tour_url',
            '3d tour' => 'virtual_tour_url',
            
            'video' => 'video_url',
            'video url' => 'video_url',
            'video tour' => 'video_url',
            'youtube' => 'video_url',
            
            'floor plans' => 'floor_plans',
            'floorplan' => 'floor_plans',
            'blueprints' => 'floor_plans',
            
            // Additional Content
            'showing instructions' => 'showing_instructions',
            'instructions' => 'showing_instructions',
            'show instructions' => 'showing_instructions',
            'showing notes' => 'showing_instructions',
            
            'internal notes' => 'internal_notes',
            'notes' => 'internal_notes',
            'private notes' => 'internal_notes',
            'admin notes' => 'internal_notes',
            'agent notes' => 'internal_notes'
        ];
    }
    
    /**
     * Parse full address into components
     * 
     * @param string $full_address Complete address string
     * @return array Parsed address components
     */
    private function parse_full_address(string $full_address): array {
        $address_parts = [];
        
        // Clean the address
        $address = trim($full_address);
        if (empty($address)) {
            return $address_parts;
        }
        
        // Split by comma for initial parsing
        $parts = array_map('trim', explode(',', $address));
        
        if (count($parts) >= 2) {
            // Last part is typically "State ZIP" or just "ZIP"
            $last_part = array_pop($parts);
            
            // Extract state and ZIP from last part
            if (preg_match('/([A-Z]{2})\s+(\d{5}(-\d{4})?)/', $last_part, $matches)) {
                $address_parts['state'] = $matches[1];
                $address_parts['zip_code'] = $matches[2];
            } elseif (preg_match('/^(\d{5}(-\d{4})?)$/', $last_part, $matches)) {
                // Just ZIP code
                $address_parts['zip_code'] = $matches[1];
            } else {
                // Might be a state name or other format
                $state_zip = $this->extract_state_zip_from_string($last_part);
                if ($state_zip) {
                    $address_parts = array_merge($address_parts, $state_zip);
                }
            }
            
            // Second to last is typically city
            if (count($parts) >= 1) {
                $address_parts['city'] = array_pop($parts);
            }
            
            // Remaining parts form the street address
            if (!empty($parts)) {
                $street_address = implode(', ', $parts);
                $street_parts = $this->parse_street_address($street_address);
                $address_parts = array_merge($address_parts, $street_parts);
            }
        } else {
            // Single part - try to parse as street address only
            $street_parts = $this->parse_street_address($address);
            $address_parts = array_merge($address_parts, $street_parts);
        }
        
        return $address_parts;
    }
    
    /**
     * Parse street address into components
     * 
     * @param string $street_address Street portion of address
     * @return array Street address components
     */
    private function parse_street_address(string $street_address): array {
        $parts = [];
        $address = trim($street_address);
        
        if (empty($address)) {
            return $parts;
        }
        
        // Common street types for matching
        $street_types = [
            'ST', 'STREET', 'AVE', 'AVENUE', 'BLVD', 'BOULEVARD', 'RD', 'ROAD',
            'DR', 'DRIVE', 'LN', 'LANE', 'CT', 'COURT', 'CIR', 'CIRCLE',
            'PL', 'PLACE', 'WAY', 'TRL', 'TRAIL', 'PKWY', 'PARKWAY',
            'TER', 'TERRACE', 'SQ', 'SQUARE', 'LOOP', 'PATH', 'WALK'
        ];
        
        // Directional prefixes/suffixes
        $directions = ['N', 'S', 'E', 'W', 'NE', 'NW', 'SE', 'SW', 'NORTH', 'SOUTH', 'EAST', 'WEST'];
        
        // Split into words
        $words = preg_split('/\s+/', strtoupper($address));
        
        // Extract unit number if present (patterns like "APT 2", "UNIT B", "#5")
        $unit_patterns = ['APT', 'APARTMENT', 'UNIT', 'SUITE', 'STE', '#'];
        for ($i = 0; $i < count($words); $i++) {
            if (in_array($words[$i], $unit_patterns) && isset($words[$i + 1])) {
                $parts['unit_number'] = $words[$i + 1];
                // Remove unit info from words array
                array_splice($words, $i, 2);
                break;
            } elseif (substr($words[$i], 0, 1) === '#') {
                $parts['unit_number'] = substr($words[$i], 1);
                array_splice($words, $i, 1);
                break;
            }
        }
        
        if (empty($words)) {
            return $parts;
        }
        
        // First word is typically the street number
        if (preg_match('/^\d+[A-Z]?$/', $words[0])) {
            $parts['street_number'] = array_shift($words);
        }
        
        // Check for directional prefix
        if (!empty($words) && in_array($words[0], $directions)) {
            $parts['street_dir_prefix'] = array_shift($words);
        }
        
        // Look for street type and directional suffix from the end
        $street_name_words = $words;
        
        // Check for directional suffix
        if (!empty($words) && in_array(end($words), $directions)) {
            $parts['street_dir_suffix'] = array_pop($street_name_words);
        }
        
        // Check for street type
        if (!empty($street_name_words) && in_array(end($street_name_words), $street_types)) {
            $parts['street_type'] = array_pop($street_name_words);
        }
        
        // Remaining words form the street name
        if (!empty($street_name_words)) {
            $parts['street_name'] = implode(' ', $street_name_words);
        }
        
        return $parts;
    }
    
    /**
     * Extract state and ZIP from a string
     * 
     * @param string $text Text to parse
     * @return array State and ZIP if found
     */
    private function extract_state_zip_from_string(string $text): array {
        $parts = [];
        
        // Try various patterns
        $patterns = [
            '/([A-Z]{2})\s+(\d{5}(-\d{4})?)/',  // "CA 90210"
            '/(\d{5}(-\d{4})?)\s+([A-Z]{2})/',  // "90210 CA"
            '/(\d{5}(-\d{4})?)/',               // Just ZIP
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, strtoupper($text), $matches)) {
                if (isset($matches[3])) {
                    // Pattern with state after ZIP
                    $parts['zip_code'] = $matches[1];
                    $parts['state'] = $matches[3];
                } elseif (isset($matches[2]) && strlen($matches[1]) === 2) {
                    // Pattern with state before ZIP
                    $parts['state'] = $matches[1];
                    $parts['zip_code'] = $matches[2];
                } elseif (preg_match('/^\d{5}/', $matches[1])) {
                    // Just ZIP code
                    $parts['zip_code'] = $matches[1];
                }
                break;
            }
        }
        
        return $parts;
    }
    
    /**
     * AJAX handler for file upload
     */
    public function ajax_upload_file(): void {
        // Debug information
        if (HP_DEBUG) {
            $this->log('AJAX upload file called', 'debug', 'IMPORT');
            $this->log('POST data: ' . print_r($_POST, true), 'debug', 'IMPORT');
            $this->log('FILES data: ' . print_r($_FILES, true), 'debug', 'IMPORT');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_import_nonce')) {
            if (HP_DEBUG) {
                $this->log('Nonce verification failed. Expected: hp_import_nonce, Received: ' . ($_POST['nonce'] ?? 'none'), 'error', 'IMPORT');
            }
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        // Check permissions
        if (!current_user_can('import')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        // Handle file upload
        $uploaded_file = $_FILES['csv_file'] ?? null;
        if (!$uploaded_file || $uploaded_file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => 'File upload failed']);
        }
        
        // Move file to temporary location
        $upload_dir = wp_upload_dir();
        $temp_file = $upload_dir['path'] . '/' . uniqid('import_') . '.csv';
        
        if (!move_uploaded_file($uploaded_file['tmp_name'], $temp_file)) {
            wp_send_json_error(['message' => 'Failed to save uploaded file']);
        }
        
        // Validate CSV
        $validation = $this->validate_csv($temp_file);
        if (is_wp_error($validation)) {
            unlink($temp_file);
            wp_send_json_error(['message' => $validation->get_error_message()]);
        }
        
        // Get headers for mapping
        $headers = $this->get_csv_headers($temp_file);
        if (is_wp_error($headers)) {
            unlink($temp_file);
            wp_send_json_error(['message' => $headers->get_error_message()]);
        }
        
        // Auto-map fields
        $suggested_mapping = $this->auto_map_fields($headers);
        
        wp_send_json_success([
            'file_path' => $temp_file,
            'headers' => $headers,
            'suggested_mapping' => $suggested_mapping,
            'mapping_templates' => $this->get_mapping_templates()
        ]);
    }
    
    /**
     * AJAX handler for CSV validation
     */
    public function ajax_validate_csv(): void {
        $file_path = $_POST['file_path'] ?? '';
        
        if (empty($file_path) || !file_exists($file_path)) {
            wp_send_json_error(['message' => 'File not found']);
        }
        
        $validation = $this->validate_csv($file_path);
        
        if (is_wp_error($validation)) {
            wp_send_json_error(['message' => $validation->get_error_message()]);
        }
        
        wp_send_json_success(['message' => 'CSV file is valid']);
    }
    
    /**
     * AJAX handler for import processing
     */
    public function ajax_process_import(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        // Check permissions
        if (!current_user_can('import')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $file_path = $_POST['file_path'] ?? '';
        $mapping = $_POST['mapping'] ?? [];
        
        if (empty($file_path) || !file_exists($file_path)) {
            wp_send_json_error(['message' => 'File not found']);
        }
        
        if (empty($mapping)) {
            wp_send_json_error(['message' => 'Field mapping is required']);
        }
        
        // Start import process
        $results = $this->import_csv($file_path, $mapping);
        
        if (is_wp_error($results)) {
            wp_send_json_error(['message' => $results->get_error_message()]);
        }
        
        // Clean up temp file
        unlink($file_path);
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX handler for progress checking
     */
    public function ajax_get_progress(): void {
        $session_id = $_POST['session_id'] ?? '';
        
        if (empty($session_id)) {
            wp_send_json_error(['message' => 'Session ID required']);
        }
        
        $progress = $this->get_import_progress($session_id);
        
        if (!$progress) {
            wp_send_json_error(['message' => 'Session not found']);
        }
        
        wp_send_json_success($progress);
    }
    
    /**
     * AJAX handler for getting CSV sample data
     */
    public function ajax_get_csv_sample(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $file_path = sanitize_text_field($_POST['file_path'] ?? '');
        
        if (empty($file_path) || !file_exists($file_path)) {
            wp_send_json_error(['message' => 'File not found']);
        }
        
        // Get sample data (first 3 rows)
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            wp_send_json_error(['message' => 'Unable to read CSV file']);
        }
        
        // Skip headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            wp_send_json_error(['message' => 'CSV file has no headers']);
        }
        
        // Get up to 3 sample rows
        $sample = [];
        $row_count = 0;
        while (($row = fgetcsv($handle)) !== false && $row_count < 3) {
            // Ensure row has same number of columns as headers
            $row = array_pad($row, count($headers), '');
            $sample[] = $row;
            $row_count++;
        }
        
        fclose($handle);
        
        wp_send_json_success([
            'sample' => $sample,
            'row_count' => $row_count
        ]);
    }
    
    /**
     * AJAX handler for auto-mapping fields
     */
    public function ajax_auto_map_fields(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $file_path = sanitize_text_field($_POST['file_path'] ?? '');
        
        if (empty($file_path) || !file_exists($file_path)) {
            wp_send_json_error(['message' => 'File not found']);
        }
        
        // Get CSV headers
        $headers = $this->get_csv_headers($file_path);
        if (is_wp_error($headers)) {
            wp_send_json_error(['message' => $headers->get_error_message()]);
        }
        
        // Auto-map fields
        $mapping = $this->auto_map_fields($headers);
        
        wp_send_json_success(['mapping' => $mapping]);
    }
    
    /**
     * AJAX handler for saving mapping templates
     */
    public function ajax_save_mapping_template(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $template_name = sanitize_text_field($_POST['template_name'] ?? '');
        $mapping = $_POST['mapping'] ?? [];
        
        if (empty($template_name)) {
            wp_send_json_error(['message' => 'Template name is required']);
        }
        
        // Sanitize mapping data
        $clean_mapping = [];
        foreach ($mapping as $key => $value) {
            if (is_array($value)) {
                $clean_mapping[intval($key)] = [
                    'csv_header' => sanitize_text_field($value['csv_header'] ?? ''),
                    'listing_field' => sanitize_text_field($value['listing_field'] ?? '')
                ];
            }
        }
        
        $result = $this->save_mapping_template($template_name, $clean_mapping);
        
        if ($result) {
            wp_send_json_success(['message' => 'Template saved successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to save template']);
        }
    }
    
    /**
     * AJAX handler for getting mapping templates
     */
    public function ajax_get_mapping_templates(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        $templates = $this->get_mapping_templates();
        
        wp_send_json_success(['templates' => $templates]);
    }
}