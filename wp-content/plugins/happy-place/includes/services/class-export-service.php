<?php
/**
 * Export Service - Data Export & Formatting
 * 
 * Handles exporting of listings, leads, agents and other data to various formats.
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
 * Export Service Class
 * 
 * Handles data export operations in various formats
 */
class ExportService extends Service {
    
    /**
     * Service name
     */
    protected string $name = 'export_service';
    
    /**
     * Service version
     */
    protected string $version = '4.0.0';
    
    /**
     * Supported export formats
     */
    private array $supported_formats = ['csv', 'json', 'xml'];
    
    /**
     * Export batch size
     */
    private int $batch_size = 1000;
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        // Register AJAX handlers
        add_action('wp_ajax_hp_export_listings', [$this, 'ajax_export_listings']);
        add_action('wp_ajax_hp_export_leads', [$this, 'ajax_export_leads']);
        add_action('wp_ajax_hp_export_agents', [$this, 'ajax_export_agents']);
        add_action('wp_ajax_hp_export_custom', [$this, 'ajax_export_custom']);
        
        // Register admin post handlers for downloads
        add_action('admin_post_hp_export_listings', [$this, 'handle_export_listings']);
        add_action('admin_post_hp_export_leads', [$this, 'handle_export_leads']);
        add_action('admin_post_hp_export_agents', [$this, 'handle_export_agents']);
        
        $this->initialized = true;
        $this->log('Export Service initialized successfully');
    }
    
    /**
     * Export listings to specified format
     * 
     * @param array $args Export arguments
     * @return array|WP_Error Export data or error
     */
    public function export_listings(array $args = []) {
        $defaults = [
            'format' => 'csv',
            'status' => 'all',
            'date_from' => '',
            'date_to' => '',
            'fields' => 'all',
            'limit' => -1,
            'author_id' => ''
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate format
        if (!in_array($args['format'], $this->supported_formats)) {
            return new \WP_Error('invalid_format', 'Unsupported export format');
        }
        
        // Map status to WordPress post status
        $status_mapping = [
            'active' => 'publish',
            'sold' => 'publish', // We'll filter by meta field for sold
            'pending' => 'publish', // We'll filter by meta field for pending
            'draft' => 'draft',
            'all' => ['publish', 'draft', 'private']
        ];
        
        $post_status = $status_mapping[$args['status']] ?? $args['status'];
        
        // Build query arguments
        $query_args = [
            'post_type' => 'listing',
            'post_status' => $post_status,
            'posts_per_page' => $args['limit'],
            'meta_query' => []
        ];
        
        // Add status meta query for sold/pending listings
        if ($args['status'] === 'sold') {
            $query_args['meta_query'][] = [
                'key' => 'status',
                'value' => 'sold',
                'compare' => '='
            ];
        } elseif ($args['status'] === 'pending') {
            $query_args['meta_query'][] = [
                'key' => 'status', 
                'value' => 'pending',
                'compare' => '='
            ];
        } elseif ($args['status'] === 'active') {
            $query_args['meta_query'][] = [
                'key' => 'status',
                'value' => ['active', 'available', ''],
                'compare' => 'IN'
            ];
        }
        
        // Add author filter
        if (!empty($args['author_id'])) {
            $query_args['author'] = $args['author_id'];
        }
        
        // Add date filters
        if (!empty($args['date_from']) || !empty($args['date_to'])) {
            $date_query = [];
            if (!empty($args['date_from'])) {
                $date_query['after'] = $args['date_from'];
            }
            if (!empty($args['date_to'])) {
                $date_query['before'] = $args['date_to'];
            }
            $query_args['date_query'] = [$date_query];
        }
        
        // Get listings
        $listings = get_posts($query_args);
        
        if (HP_DEBUG) {
            $this->log('Export query args: ' . print_r($query_args, true), 'debug', 'EXPORT');
            $this->log('Found ' . count($listings) . ' listings to export', 'debug', 'EXPORT');
        }
        
        if (empty($listings)) {
            return new \WP_Error('no_data', 'No listings found to export');
        }
        
        // Prepare data for export
        $export_data = $this->prepare_listings_data($listings, $args['fields']);
        
        // Format data based on requested format
        return $this->format_export_data($export_data, $args['format'], 'listings');
    }
    
    /**
     * Export leads to specified format
     * 
     * @param array $args Export arguments
     * @return array|WP_Error Export data or error
     */
    public function export_leads(array $args = []) {
        $defaults = [
            'format' => 'csv',
            'status' => 'all',
            'date_from' => '',
            'date_to' => '',
            'fields' => 'all',
            'limit' => -1
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate format
        if (!in_array($args['format'], $this->supported_formats)) {
            return new \WP_Error('invalid_format', 'Unsupported export format');
        }
        
        // Use LeadService to get leads data
        if (!class_exists('HappyPlace\\Services\\LeadService')) {
            return new \WP_Error('service_missing', 'LeadService not available');
        }
        
        $lead_service = new \HappyPlace\Services\LeadService();
        $lead_service->init();
        
        $leads = $lead_service->get_leads([
            'status' => $args['status'] !== 'all' ? $args['status'] : '',
            'limit' => $args['limit']
        ]);
        
        if (empty($leads)) {
            return new \WP_Error('no_data', 'No leads found to export');
        }
        
        // Prepare data for export
        $export_data = $this->prepare_leads_data($leads, $args['fields']);
        
        // Format data based on requested format
        return $this->format_export_data($export_data, $args['format'], 'leads');
    }
    
    /**
     * Export agents to specified format
     * 
     * @param array $args Export arguments
     * @return array|WP_Error Export data or error
     */
    public function export_agents(array $args = []) {
        $defaults = [
            'format' => 'csv',
            'status' => 'all',
            'fields' => 'all',
            'limit' => -1
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate format
        if (!in_array($args['format'], $this->supported_formats)) {
            return new \WP_Error('invalid_format', 'Unsupported export format');
        }
        
        // Get agents (assuming they're stored as users with agent role)
        $user_args = [
            'role' => 'agent',
            'number' => $args['limit']
        ];
        
        $agents = get_users($user_args);
        
        if (empty($agents)) {
            return new \WP_Error('no_data', 'No agents found to export');
        }
        
        // Prepare data for export
        $export_data = $this->prepare_agents_data($agents, $args['fields']);
        
        // Format data based on requested format
        return $this->format_export_data($export_data, $args['format'], 'agents');
    }
    
    /**
     * Prepare listings data for export
     * 
     * @param array $listings Array of listing posts
     * @param string $fields Fields to include
     * @return array Prepared data
     */
    private function prepare_listings_data(array $listings, string $fields): array {
        $data = [];
        
        foreach ($listings as $listing) {
            $row = [
                'ID' => $listing->ID,
                'Title' => $listing->post_title,
                'Description' => wp_strip_all_tags($listing->post_content),
                'Status' => $listing->post_status,
                'Date Created' => $listing->post_date,
                'Date Modified' => $listing->post_modified
            ];
            
            // Add ACF fields
            $acf_fields = get_fields($listing->ID);
            if ($acf_fields) {
                foreach ($acf_fields as $field_name => $field_value) {
                    // Format field names for export
                    $formatted_name = ucwords(str_replace('_', ' ', $field_name));
                    $row[$formatted_name] = $this->format_field_value($field_value);
                }
            }
            
            // Add meta fields
            $meta_data = get_post_meta($listing->ID);
            foreach ($meta_data as $key => $values) {
                if (strpos($key, '_') !== 0) { // Skip private meta fields
                    $formatted_name = ucwords(str_replace('_', ' ', $key));
                    $row[$formatted_name] = is_array($values) ? implode(', ', $values) : $values[0];
                }
            }
            
            $data[] = $row;
        }
        
        return $data;
    }
    
    /**
     * Prepare leads data for export
     * 
     * @param array $leads Array of leads
     * @param string $fields Fields to include
     * @return array Prepared data
     */
    private function prepare_leads_data(array $leads, string $fields): array {
        $data = [];
        
        foreach ($leads as $lead) {
            $row = [
                'ID' => $lead['id'] ?? '',
                'First Name' => $lead['first_name'] ?? '',
                'Last Name' => $lead['last_name'] ?? '',
                'Email' => $lead['email'] ?? '',
                'Phone' => $lead['phone'] ?? '',
                'Status' => $lead['status'] ?? '',
                'Lead Score' => $lead['lead_score'] ?? '',
                'Source' => $lead['source'] ?? '',
                'Notes' => $lead['notes'] ?? '',
                'Created At' => $lead['created_at'] ?? '',
                'Updated At' => $lead['updated_at'] ?? ''
            ];
            
            $data[] = $row;
        }
        
        return $data;
    }
    
    /**
     * Prepare agents data for export
     * 
     * @param array $agents Array of agent users
     * @param string $fields Fields to include
     * @return array Prepared data
     */
    private function prepare_agents_data(array $agents, string $fields): array {
        $data = [];
        
        foreach ($agents as $agent) {
            $row = [
                'ID' => $agent->ID,
                'Username' => $agent->user_login,
                'Email' => $agent->user_email,
                'Display Name' => $agent->display_name,
                'First Name' => $agent->first_name,
                'Last Name' => $agent->last_name,
                'Phone' => get_user_meta($agent->ID, 'phone', true),
                'License Number' => get_user_meta($agent->ID, 'license_number', true),
                'Bio' => get_user_meta($agent->ID, 'description', true),
                'Date Registered' => $agent->user_registered
            ];
            
            $data[] = $row;
        }
        
        return $data;
    }
    
    /**
     * Format export data based on format
     * 
     * @param array $data Raw data
     * @param string $format Export format
     * @param string $type Data type
     * @return array|WP_Error Formatted data or error
     */
    private function format_export_data(array $data, string $format, string $type) {
        $filename = sprintf('%s-export-%s.%s', $type, date('Y-m-d-His'), $format);
        
        switch ($format) {
            case 'csv':
                return [
                    'content' => $this->array_to_csv($data),
                    'filename' => $filename,
                    'mime_type' => 'text/csv'
                ];
                
            case 'json':
                return [
                    'content' => json_encode($data, JSON_PRETTY_PRINT),
                    'filename' => $filename,
                    'mime_type' => 'application/json'
                ];
                
            case 'xml':
                return [
                    'content' => $this->array_to_xml($data, $type),
                    'filename' => $filename,
                    'mime_type' => 'application/xml'
                ];
                
            default:
                return new \WP_Error('invalid_format', 'Unsupported format');
        }
    }
    
    /**
     * Convert array to CSV format
     * 
     * @param array $data Data array
     * @return string CSV content
     */
    private function array_to_csv(array $data): string {
        if (empty($data)) {
            return '';
        }
        
        ob_start();
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, array_keys($data[0]));
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        return ob_get_clean();
    }
    
    /**
     * Convert array to XML format
     * 
     * @param array $data Data array
     * @param string $root_element Root element name
     * @return string XML content
     */
    private function array_to_xml(array $data, string $root_element): string {
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><{$root_element}></{$root_element}>");
        
        foreach ($data as $index => $item) {
            $record = $xml->addChild('record');
            foreach ($item as $key => $value) {
                $record->addChild(str_replace(' ', '_', strtolower($key)), htmlspecialchars($value));
            }
        }
        
        return $xml->asXML();
    }
    
    /**
     * Format field value for export
     * 
     * @param mixed $value Field value
     * @return string Formatted value
     */
    private function format_field_value($value): string {
        if (is_array($value)) {
            return implode(', ', array_filter($value));
        }
        
        if (is_object($value)) {
            return json_encode($value);
        }
        
        return (string) $value;
    }
    
    /**
     * AJAX handler for listing export
     */
    public function ajax_export_listings(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_export')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $args = [
            'format' => sanitize_text_field($_POST['format'] ?? 'csv'),
            'status' => sanitize_text_field($_POST['status'] ?? 'all'),
            'date_from' => sanitize_text_field($_POST['date_from'] ?? ''),
            'date_to' => sanitize_text_field($_POST['date_to'] ?? ''),
            'author_id' => intval($_POST['author_id'] ?? 0)
        ];
        
        $result = $this->export_listings($args);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX handler for leads export
     */
    public function ajax_export_leads(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_export')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $args = [
            'format' => sanitize_text_field($_POST['format'] ?? 'csv'),
            'status' => sanitize_text_field($_POST['status'] ?? 'all'),
            'date_from' => sanitize_text_field($_POST['date_from'] ?? ''),
            'date_to' => sanitize_text_field($_POST['date_to'] ?? '')
        ];
        
        $result = $this->export_leads($args);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX handler for agents export
     */
    public function ajax_export_agents(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_export')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $args = [
            'format' => sanitize_text_field($_POST['format'] ?? 'csv')
        ];
        
        $result = $this->export_agents($args);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * Handle listing export download
     */
    public function handle_export_listings(): void {
        // Verify nonce
        if (!wp_verify_nonce($_REQUEST['_wpnonce'] ?? '', 'hp_export_listings')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Handle both POST (form) and GET (quick links) data
        $request_data = array_merge($_GET, $_POST);
        
        $args = [
            'format' => sanitize_text_field($request_data['export_format'] ?? $request_data['format'] ?? 'csv'),
            'status' => sanitize_text_field($request_data['status_filter'] ?? $request_data['status'] ?? 'all'),
            'date_from' => sanitize_text_field($request_data['date_from'] ?? ''),
            'date_to' => sanitize_text_field($request_data['date_to'] ?? ''),
            'property_type' => sanitize_text_field($request_data['property_type'] ?? ''),
            'include_images' => !empty($request_data['include_images']),
            'include_agent_info' => !empty($request_data['include_agent_info'])
        ];
        
        // Handle quick export special cases
        if (!empty($request_data['quick'])) {
            switch ($request_data['quick']) {
                case 'active':
                    $args['status'] = 'active';
                    break;
                case 'sold_this_month':
                    $args['status'] = 'sold';
                    $args['date_from'] = date('Y-m-01');
                    break;
            }
        }
        
        $result = $this->export_listings($args);
        
        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
        }
        
        // Send download headers
        header('Content-Type: ' . $result['mime_type']);
        header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $result['content'];
        exit;
    }
    
    /**
     * Handle leads export download
     */
    public function handle_export_leads(): void {
        // Verify nonce
        if (!wp_verify_nonce($_REQUEST['_wpnonce'] ?? '', 'hp_export')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $result = $this->export_leads(['format' => 'csv']);
        
        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
        }
        
        // Send download headers
        header('Content-Type: ' . $result['mime_type']);
        header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $result['content'];
        exit;
    }
    
    /**
     * Handle agents export download
     */
    public function handle_export_agents(): void {
        // Verify nonce
        if (!wp_verify_nonce($_REQUEST['_wpnonce'] ?? '', 'hp_export')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $result = $this->export_agents(['format' => 'csv']);
        
        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
        }
        
        // Send download headers
        header('Content-Type: ' . $result['mime_type']);
        header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $result['content'];
        exit;
    }
}
