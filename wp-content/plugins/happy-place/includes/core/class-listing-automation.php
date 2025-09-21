<?php
/**
 * Listing Automation Class
 * 
 * Handles automated functionality for listings including:
 * - Auto-generating post titles from street address components with abbreviations
 * - Auto-generating post slugs from street address
 * - Auto-renaming image slugs with property details
 * - Bathroom formatting automation
 * - Nested URL structure for listings (/listings/status/type/city/state/address)
 * - Street type abbreviation (Street -> St, Avenue -> Ave, etc.)
 *
 * @package HappyPlace\Core
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Listing_Automation {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
            hp_log('Listing_Automation instance created', 'debug', 'LISTING_AUTO');
        }
        return self::$instance;
    }
    
    /**
     * Initialize listing automation
     */
    private function __construct() {
        hp_log('Listing_Automation constructor called', 'debug', 'LISTING_AUTO');
    }

    /**
     * Initialize component
     */
    public function init(): void {
        hp_log('Listing_Automation init() method called', 'info', 'LISTING_AUTO');
        
        // Hook into post save to handle automation
        add_action('acf/save_post', [$this, 'handle_listing_automation'], 20);
        
        // Hook into post title generation
        add_filter('wp_insert_post_data', [$this, 'auto_generate_post_slug'], 10, 2);
        
        // Hook into post title auto-generation
        add_filter('wp_insert_post_data', [$this, 'auto_generate_post_title'], 5, 2);
        
        // Hook into image upload for auto-renaming
        // TEMPORARILY COMMENTED OUT - Causing issues with bulk uploads
        // add_filter('wp_handle_upload_prefilter', [$this, 'auto_rename_uploaded_images']);
        
        // Add AJAX endpoint for bathroom formatting
        add_action('wp_ajax_format_bathrooms', [$this, 'ajax_format_bathrooms']);
        add_action('wp_ajax_nopriv_format_bathrooms', [$this, 'ajax_format_bathrooms']);
        
        // Add custom rewrite rules for nested listing URLs
        add_action('init', [$this, 'add_listing_rewrite_rules'], 20);
        add_filter('post_link', [$this, 'custom_listing_permalink'], 10, 2);
        add_filter('post_type_link', [$this, 'custom_listing_permalink'], 10, 2);
        
        // Hook to flush rewrite rules when needed
        add_action('wp_loaded', [$this, 'maybe_flush_rewrite_rules']);
        
        hp_log('Listing Automation component initialized', 'debug', 'LISTING_AUTO');
    }
    
    /**
     * Handle listing automation on post save
     */
    public function handle_listing_automation($post_id): void {
        // Only process listings
        if (get_post_type($post_id) !== 'listing') {
            return;
        }
        
        hp_log("Processing listing automation for post ID: {$post_id}", 'info', 'LISTING_AUTO');
        
        // Auto-generate title from address components
        $this->auto_update_post_title($post_id);
        
        // Generate formatted bathroom display
        $this->update_bathroom_display($post_id);
        
        // Update post slug if needed
        $this->maybe_update_post_slug($post_id);
        
        hp_log("Completed listing automation for post ID: {$post_id}", 'info', 'LISTING_AUTO');
    }
    
    /**
     * Auto-generate post slug from street address
     */
    public function auto_generate_post_slug($data, $postarr) {
        // Only process listings
        if ($data['post_type'] !== 'listing') {
            return $data;
        }
        
        // Only generate slug for new posts or if slug is empty
        if (!empty($data['post_name']) && !isset($postarr['ID'])) {
            return $data;
        }
        
        $post_id = $postarr['ID'] ?? null;
        
        // Build street address from components
        $street_address = '';
        $city = '';
        $state = '';
        
        if ($post_id) {
            $street_address = $this->get_street_address_from_components($post_id);
            $city = get_field('city', $post_id);
            $state = get_field('state', $post_id);
        } else {
            // For new posts, try to get from $_POST data
            $street_number = $_POST['acf']['field_street_number'] ?? '';
            $street_name = $_POST['acf']['field_street_name'] ?? '';
            $street_suffix = $_POST['acf']['field_street_suffix'] ?? '';
            $unit_number = $_POST['acf']['field_unit_number'] ?? '';
            
            $street_parts = array_filter([$street_number, $street_name, $street_suffix]);
            $street_address = implode(' ', $street_parts);
            if (!empty($unit_number)) {
                $street_address .= ' ' . $unit_number;
            }
            
            $city = $_POST['acf']['field_city'] ?? '';
            $state = $_POST['acf']['field_state'] ?? '';
        }
        
        if (!empty($street_address)) {
            $slug_parts = [$street_address];
            
            if (!empty($city)) {
                $slug_parts[] = $city;
            }
            
            if (!empty($state)) {
                $slug_parts[] = $state;
            }
            
            $proposed_slug = sanitize_title(implode('-', $slug_parts));
            
            // Ensure uniqueness
            $unique_slug = wp_unique_post_slug(
                $proposed_slug,
                $post_id ?: 0,
                $data['post_status'],
                $data['post_type'],
                $data['post_parent']
            );
            
            $data['post_name'] = $unique_slug;
            
            hp_log("Generated post slug: {$unique_slug} from address: {$street_address}", 'info', 'LISTING_AUTO');
        }
        
        return $data;
    }
    
    /**
     * Maybe update post slug for existing posts
     */
    private function maybe_update_post_slug($post_id): void {
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        
        // Only update if the current slug is auto-generated or empty
        $current_slug = $post->post_name;
        $street_address = $this->get_street_address_from_components($post_id);
        $city = get_field('city', $post_id);
        $state = get_field('state', $post_id);
        
        if (empty($street_address)) {
            return;
        }
        
        $slug_parts = [$street_address];
        if (!empty($city)) {
            $slug_parts[] = $city;
        }
        if (!empty($state)) {
            $slug_parts[] = $state;
        }
        
        $proposed_slug = sanitize_title(implode('-', $slug_parts));
        
        // Only update if different from current
        if ($current_slug !== $proposed_slug) {
            $unique_slug = wp_unique_post_slug(
                $proposed_slug,
                $post_id,
                $post->post_status,
                $post->post_type,
                $post->post_parent
            );
            
            wp_update_post([
                'ID' => $post_id,
                'post_name' => $unique_slug
            ]);
            
            hp_log("Updated post slug to: {$unique_slug} for post ID: {$post_id}", 'info', 'LISTING_AUTO');
        }
    }
    
    /**
     * Auto-generate post title from address components
     */
    public function auto_generate_post_title($data, $postarr) {
        // Only process listings
        if ($data['post_type'] !== 'listing') {
            return $data;
        }
        
        // Only auto-generate if title is empty or default
        if (!empty($data['post_title']) && $data['post_title'] !== 'Auto Draft') {
            return $data;
        }
        
        $post_id = $postarr['ID'] ?? null;
        $generated_title = '';
        
        if ($post_id) {
            $generated_title = $this->build_listing_title_from_components($post_id);
        } else {
            // For new posts, try to get from $_POST data
            $street_number = $_POST['acf']['field_street_number'] ?? '';
            $street_name = $_POST['acf']['field_street_name'] ?? '';
            $street_suffix = $_POST['acf']['field_street_suffix'] ?? '';
            
            if (!empty($street_number) && !empty($street_name) && !empty($street_suffix)) {
                $abbreviated_suffix = $this->abbreviate_street_type($street_suffix);
                $generated_title = trim($street_number . ' ' . $street_name . ' ' . $abbreviated_suffix);
            }
        }
        
        if (!empty($generated_title)) {
            $data['post_title'] = $generated_title;
            hp_log("Generated post title: {$generated_title}", 'info', 'LISTING_AUTO');
        }
        
        return $data;
    }
    
    /**
     * Auto-update post title for existing posts
     */
    public function auto_update_post_title($post_id): void {
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        
        // Only update if current title is empty, "Auto Draft", or appears to be auto-generated
        $current_title = $post->post_title;
        if (!empty($current_title) && $current_title !== 'Auto Draft' && !$this->is_auto_generated_title($current_title)) {
            return;
        }
        
        $generated_title = $this->build_listing_title_from_components($post_id);
        
        if (!empty($generated_title) && $generated_title !== $current_title) {
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $generated_title
            ]);
            
            hp_log("Updated post title to: {$generated_title} for post ID: {$post_id}", 'info', 'LISTING_AUTO');
        }
    }
    
    /**
     * Build listing title from address components
     */
    private function build_listing_title_from_components($post_id): string {
        $street_number = get_field('street_number', $post_id);
        $street_name = get_field('street_name', $post_id);
        $street_suffix = get_field('street_suffix', $post_id);
        
        if (empty($street_number) || empty($street_name) || empty($street_suffix)) {
            return '';
        }
        
        $abbreviated_suffix = $this->abbreviate_street_type($street_suffix);
        return trim($street_number . ' ' . $street_name . ' ' . $abbreviated_suffix);
    }
    
    /**
     * Abbreviate street type/suffix
     */
    private function abbreviate_street_type($street_type): string {
        if (empty($street_type)) {
            return '';
        }
        
        // Common street type abbreviations
        $abbreviations = [
            'Street' => 'St',
            'Avenue' => 'Ave',
            'Boulevard' => 'Blvd',
            'Drive' => 'Dr',
            'Lane' => 'Ln',
            'Road' => 'Rd',
            'Court' => 'Ct',
            'Place' => 'Pl',
            'Circle' => 'Cir',
            'Way' => 'Way',
            'Trail' => 'Trl',
            'Parkway' => 'Pkwy',
            'Highway' => 'Hwy',
            'Alley' => 'Aly',
            'Crescent' => 'Cres',
            'Terrace' => 'Ter',
            'Square' => 'Sq',
            'Loop' => 'Loop',
            'Ridge' => 'Rdg',
            'Point' => 'Pt',
            'Run' => 'Run',
            'Pass' => 'Pass',
            'Path' => 'Path',
            'Pike' => 'Pike',
            'Plaza' => 'Plz',
            'Green' => 'Grn',
            'Grove' => 'Grv',
            'Heights' => 'Hts',
            'Hill' => 'Hl',
            'Hills' => 'Hls',
            'Knoll' => 'Knl',
            'Manor' => 'Mnr',
            'Park' => 'Park',
            'Pointe' => 'Pte',
            'Ridge' => 'Rdg',
            'View' => 'Vw',
            'Walk' => 'Walk',
            'Woods' => 'Wds'
        ];
        
        // Check for exact match (case-insensitive)
        foreach ($abbreviations as $full => $abbrev) {
            if (strcasecmp($street_type, $full) === 0) {
                return $abbrev;
            }
        }
        
        // If no match found, return original (could already be abbreviated)
        return $street_type;
    }
    
    /**
     * Check if title appears to be auto-generated
     */
    private function is_auto_generated_title($title): bool {
        // Check if title follows pattern: number + street name + abbreviated suffix
        return preg_match('/^\d+\s+[A-Za-z\s]+\s+(St|Ave|Blvd|Dr|Ln|Rd|Ct|Pl|Cir|Way|Trl|Pkwy|Hwy)$/', $title);
    }
    
    /**
     * Auto-rename uploaded images for listings
     */
    public function auto_rename_uploaded_images($file) {
        // Only process if we're uploading for a listing
        $post_id = $_POST['post_id'] ?? $_REQUEST['post_id'] ?? null;
        
        if (!$post_id || get_post_type($post_id) !== 'listing') {
            return $file;
        }
        
        $street_address = self::get_street_address_from_components_static($post_id);
        $city = get_field('city', $post_id);
        $state = get_field('state', $post_id);
        
        if (empty($street_address)) {
            return $file;
        }
        
        // Get file extension
        $file_info = pathinfo($file['name']);
        $extension = $file_info['extension'] ?? 'jpg';
        
        // Create new filename
        $name_parts = [sanitize_title($street_address)];
        
        if (!empty($city)) {
            $name_parts[] = sanitize_title($city);
        }
        
        if (!empty($state)) {
            $name_parts[] = sanitize_title($state);
        }
        
        // Add timestamp to ensure uniqueness
        $name_parts[] = current_time('timestamp');
        
        $new_filename = implode('-', $name_parts) . '.' . $extension;
        
        // Update the file array
        $file['name'] = $new_filename;
        
        hp_log("Renamed image to: {$new_filename} for listing: {$post_id}", 'info', 'LISTING_AUTO');
        
        return $file;
    }
    
    /**
     * Update bathroom display formatting
     */
    private function update_bathroom_display($post_id): void {
        $full_baths = get_field('full_bathrooms', $post_id);
        $half_baths = get_field('half_bathrooms', $post_id);
        
        // Format as "2 | 1" (full | half)
        if (!empty($full_baths) || !empty($half_baths)) {
            $full_baths = intval($full_baths);
            $half_baths = intval($half_baths);
            
            $formatted_display = $full_baths . ' | ' . $half_baths;
            
            // Store the formatted display for easy access
            update_post_meta($post_id, '_bathroom_display', $formatted_display);
            
            hp_log("Updated bathroom display to: {$formatted_display} for post ID: {$post_id}", 'info', 'LISTING_AUTO');
        }
    }
    
    /**
     * AJAX handler for bathroom formatting
     */
    public function ajax_format_bathrooms(): void {
        check_ajax_referer('hp_listing_auto', 'nonce');
        
        $full_baths = intval($_POST['full_baths'] ?? 0);
        $half_baths = intval($_POST['half_baths'] ?? 0);
        
        $formatted_display = $full_baths . ' | ' . $half_baths;
        
        wp_send_json_success([
            'formatted_display' => $formatted_display,
            'full_baths' => $full_baths,
            'half_baths' => $half_baths
        ]);
    }
    
    /**
     * Get formatted bathroom display
     */
    public static function get_formatted_bathrooms($post_id) {
        $cached_display = get_post_meta($post_id, '_bathroom_display', true);
        
        if (!empty($cached_display)) {
            return $cached_display;
        }
        
        // Generate on demand
        $full_baths = intval(get_field('full_bathrooms', $post_id));
        $half_baths = intval(get_field('half_bathrooms', $post_id));
        
        return $full_baths . ' | ' . $half_baths;
    }
    
    /**
     * Generate auto title from address
     */
    public static function generate_listing_title($post_id) {
        $street_number = get_field('street_number', $post_id);
        $street_name = get_field('street_name', $post_id);
        $street_suffix = get_field('street_suffix', $post_id);
        
        if (empty($street_number) || empty($street_name) || empty($street_suffix)) {
            return 'Property Listing';
        }
        
        // Use the abbreviation functionality for consistent formatting
        $instance = self::get_instance();
        $abbreviated_suffix = $instance->abbreviate_street_type($street_suffix);
        
        return trim($street_number . ' ' . $street_name . ' ' . $abbreviated_suffix);
    }
    
    /**
     * Get property status badge class based on listing date
     */
    public static function get_status_badge_class($post_id) {
        $listing_date = get_field('listing_date', $post_id);
        $status_terms = get_the_terms($post_id, 'property_status');
        
        if (empty($listing_date) || empty($status_terms)) {
            return '';
        }
        
        $status = $status_terms[0]->slug;
        $listing_timestamp = strtotime($listing_date);
        $current_timestamp = current_time('timestamp');
        $days_since_listed = floor(($current_timestamp - $listing_timestamp) / DAY_IN_SECONDS);
        
        // Determine badge class based on status and date
        $badge_classes = [];
        
        if ($status === 'coming_soon') {
            $badge_classes[] = 'status-coming-soon';
        } elseif ($status === 'active') {
            if ($days_since_listed <= 7) {
                $badge_classes[] = 'status-new';
            } elseif ($days_since_listed <= 30) {
                $badge_classes[] = 'status-recent';
            }
        } elseif ($status === 'pending') {
            $badge_classes[] = 'status-pending';
        } elseif ($status === 'sold') {
            $badge_classes[] = 'status-sold';
        }
        
        return implode(' ', $badge_classes);
    }
    
    /**
     * Get status badge text
     */
    public static function get_status_badge_text($post_id) {
        $listing_date = get_field('listing_date', $post_id);
        $status_terms = get_the_terms($post_id, 'property_status');
        
        if (empty($listing_date) || empty($status_terms)) {
            return '';
        }
        
        $status = $status_terms[0]->name;
        $listing_timestamp = strtotime($listing_date);
        $current_timestamp = current_time('timestamp');
        $days_since_listed = floor(($current_timestamp - $listing_timestamp) / DAY_IN_SECONDS);
        
        // Customize badge text based on status and date
        if ($status_terms[0]->slug === 'coming_soon') {
            return 'Coming Soon';
        } elseif ($status_terms[0]->slug === 'active') {
            if ($days_since_listed <= 3) {
                return 'New Listing';
            } elseif ($days_since_listed <= 7) {
                return 'New This Week';
            } else {
                return $status;
            }
        }
        
        return $status;
    }
    
    /**
     * Build street address from components
     */
    private function get_street_address_from_components($post_id) {
        $street_number = get_field('street_number', $post_id);
        $street_name = get_field('street_name', $post_id);
        $street_suffix = get_field('street_suffix', $post_id);
        $unit_number = get_field('unit_number', $post_id);
        
        $street_parts = array_filter([$street_number, $street_name, $street_suffix]);
        $street_address = implode(' ', $street_parts);
        
        if (!empty($unit_number)) {
            $street_address .= ', ' . $unit_number;
        }
        
        return $street_address;
    }
    
    /**
     * Static version of get_street_address_from_components for static methods
     */
    public static function get_street_address_from_components_static($post_id) {
        $street_number = get_field('street_number', $post_id);
        $street_name = get_field('street_name', $post_id);
        $street_suffix = get_field('street_suffix', $post_id);
        $unit_number = get_field('unit_number', $post_id);
        
        $street_parts = array_filter([$street_number, $street_name, $street_suffix]);
        $street_address = implode(' ', $street_parts);
        
        if (!empty($unit_number)) {
            $street_address .= ', ' . $unit_number;
        }
        
        return $street_address;
    }
    
    /**
     * Add custom rewrite rules for nested listing URLs
     */
    public function add_listing_rewrite_rules(): void {
        // Add rewrite rules for nested listing structure
        // Format: /listings/{status}/{property_type}/{city}/{state}/{listing-slug}
        
        add_rewrite_rule(
            '^listings/([^/]+)/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$',
            'index.php?post_type=listing&listing_status=$matches[1]&listing_property_type=$matches[2]&listing_city=$matches[3]&listing_state=$matches[4]&name=$matches[5]',
            'top'
        );
        
        // Add query vars for the custom URL structure
        add_filter('query_vars', [$this, 'add_listing_query_vars']);
        
        hp_log('Added custom rewrite rules for nested listing URLs', 'debug', 'LISTING_AUTO');
    }
    
    /**
     * Add custom query vars for listing URLs
     */
    public function add_listing_query_vars($vars) {
        $vars[] = 'listing_status';
        $vars[] = 'listing_property_type';
        $vars[] = 'listing_city';
        $vars[] = 'listing_state';
        return $vars;
    }
    
    /**
     * Generate custom permalink for listings
     */
    public function custom_listing_permalink($permalink, $post) {
        if ($post->post_type !== 'listing') {
            return $permalink;
        }
        
        // Get taxonomy and field values
        $status = $this->get_listing_status_slug($post->ID);
        $property_type = $this->get_listing_property_type_slug($post->ID);
        $city = $this->get_listing_city_slug($post->ID);
        $state = $this->get_listing_state_slug($post->ID);
        
        // Build nested URL if we have all required components
        if (!empty($status) && !empty($property_type) && !empty($city) && !empty($state)) {
            $nested_url = home_url("/listings/{$status}/{$property_type}/{$city}/{$state}/{$post->post_name}/");
            
            hp_log("Generated nested URL: {$nested_url} for listing: {$post->ID}", 'debug', 'LISTING_AUTO');
            
            return $nested_url;
        }
        
        // Fallback to standard listing URL
        return home_url("/listings/{$post->post_name}/");
    }
    
    /**
     * Get listing status slug for URL
     */
    private function get_listing_status_slug($post_id): string {
        $status_terms = get_the_terms($post_id, 'property_status');
        if (!empty($status_terms) && !is_wp_error($status_terms)) {
            return $status_terms[0]->slug;
        }
        return 'active'; // Default fallback
    }
    
    /**
     * Get listing property type slug for URL
     */
    private function get_listing_property_type_slug($post_id): string {
        $type_terms = get_the_terms($post_id, 'property_type');
        if (!empty($type_terms) && !is_wp_error($type_terms)) {
            return $type_terms[0]->slug;
        }
        return 'residential'; // Default fallback
    }
    
    /**
     * Get listing city slug for URL
     */
    private function get_listing_city_slug($post_id): string {
        $city = get_field('city', $post_id);
        if (!empty($city)) {
            return sanitize_title($city);
        }
        return 'unknown'; // Default fallback
    }
    
    /**
     * Get listing state slug for URL
     */
    private function get_listing_state_slug($post_id): string {
        $state = get_field('state', $post_id);
        if (!empty($state)) {
            return strtoupper(sanitize_title($state));
        }
        return 'XX'; // Default fallback
    }
    
    /**
     * Maybe flush rewrite rules if needed
     */
    public function maybe_flush_rewrite_rules(): void {
        if (get_option('hp_listing_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('hp_listing_flush_rewrite_rules');
            hp_log('Flushed rewrite rules for listing automation', 'info', 'LISTING_AUTO');
        }
    }
    
    /**
     * Force flush rewrite rules (useful for activation/updates)
     */
    public static function flush_rewrite_rules(): void {
        update_option('hp_listing_flush_rewrite_rules', true);
    }
}