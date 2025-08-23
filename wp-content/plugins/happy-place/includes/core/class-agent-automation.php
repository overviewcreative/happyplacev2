<?php
/**
 * Agent Automation Class
 * 
 * Handles automated functionality for agent post type including:
 * - Auto-generating post slugs from agent names
 * - Auto-renaming uploaded images
 * - Formatting display names
 * - Performance calculations
 *
 * @package HappyPlace\Core
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Agent_Automation {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - Set up hooks
     */
    private function __construct() {
        // Auto-generate post slug from agent name
        add_filter('wp_insert_post_data', [$this, 'generate_post_slug'], 10, 2);
        
        // Auto-rename uploaded images
        add_filter('wp_handle_upload_prefilter', [$this, 'rename_uploaded_image']);
        
        // Auto-set post title from agent name
        add_action('acf/save_post', [$this, 'update_post_title'], 20);
        
        // Auto-calculate years of experience
        add_action('acf/save_post', [$this, 'calculate_years_experience'], 15);
        
        // Auto-calculate performance metrics
        add_action('save_post_agent', [$this, 'calculate_performance_metrics'], 10, 3);
        
        hp_log('Agent Automation initialized', 'info', 'AGENT_AUTOMATION');
    }
    
    /**
     * Generate post slug from agent name
     */
    public function generate_post_slug($data, $postarr) {
        // Only process agent posts
        if ($data['post_type'] !== 'agent') {
            return $data;
        }
        
        // Skip if manually set or updating existing post with slug
        if (!empty($data['post_name']) && $data['post_name'] !== sanitize_title($data['post_title'])) {
            return $data;
        }
        
        // Build slug from name components
        $slug_parts = [];
        
        // Get name fields from ACF
        $first_name = $_POST['acf']['field_agent_first_name'] ?? '';
        $last_name = $_POST['acf']['field_agent_last_name'] ?? '';
        
        if (empty($first_name) && empty($last_name)) {
            // Try to get from existing post meta if updating
            if (!empty($postarr['ID'])) {
                $first_name = get_field('first_name', $postarr['ID']);
                $last_name = get_field('last_name', $postarr['ID']);
            }
        }
        
        // Build slug: firstname-lastname
        if (!empty($first_name)) {
            $slug_parts[] = sanitize_title($first_name);
        }
        if (!empty($last_name)) {
            $slug_parts[] = sanitize_title($last_name);
        }
        
        if (!empty($slug_parts)) {
            $data['post_name'] = implode('-', $slug_parts);
            hp_log("Generated agent slug: {$data['post_name']}", 'info', 'AGENT_AUTOMATION');
        }
        
        return $data;
    }
    
    /**
     * Rename uploaded images for agent posts
     */
    public function rename_uploaded_image($file) {
        // Check if we're uploading to an agent post
        if (!isset($_REQUEST['post_id'])) {
            return $file;
        }
        
        $post_id = intval($_REQUEST['post_id']);
        $post_type = get_post_type($post_id);
        
        if ($post_type !== 'agent') {
            return $file;
        }
        
        // Get agent name
        $first_name = get_field('first_name', $post_id);
        $last_name = get_field('last_name', $post_id);
        
        if (empty($first_name) && empty($last_name)) {
            return $file;
        }
        
        // Build new filename
        $name_parts = [];
        if ($first_name) $name_parts[] = sanitize_title($first_name);
        if ($last_name) $name_parts[] = sanitize_title($last_name);
        
        // Add timestamp for uniqueness
        $name_parts[] = time();
        
        // Get file extension
        $file_info = pathinfo($file['name']);
        $extension = $file_info['extension'] ?? 'jpg';
        
        // Create new filename
        $new_filename = implode('-', $name_parts) . '.' . $extension;
        $file['name'] = $new_filename;
        
        hp_log("Renamed agent image to: {$new_filename}", 'info', 'AGENT_AUTOMATION');
        
        return $file;
    }
    
    /**
     * Update post title from agent name
     */
    public function update_post_title($post_id) {
        // Check if it's an agent post
        if (get_post_type($post_id) !== 'agent') {
            return;
        }
        
        // Get name components
        $first_name = get_field('first_name', $post_id);
        $middle_name = get_field('middle_name', $post_id);
        $last_name = get_field('last_name', $post_id);
        $suffix = get_field('suffix', $post_id);
        $display_name = get_field('display_name', $post_id);
        
        // Use display name if set, otherwise build from components
        if (!empty($display_name)) {
            $post_title = $display_name;
        } else {
            $name_parts = [];
            if ($first_name) $name_parts[] = $first_name;
            if ($middle_name) $name_parts[] = $middle_name;
            if ($last_name) $name_parts[] = $last_name;
            if ($suffix && $suffix !== '') $name_parts[] = $suffix;
            
            $post_title = implode(' ', $name_parts);
            
            // Update display name field
            if (!empty($name_parts)) {
                update_field('display_name', $post_title, $post_id);
            }
        }
        
        // Update post title if changed
        if (!empty($post_title)) {
            $current_title = get_the_title($post_id);
            if ($current_title !== $post_title) {
                wp_update_post([
                    'ID' => $post_id,
                    'post_title' => $post_title
                ]);
                hp_log("Updated agent title to: {$post_title}", 'info', 'AGENT_AUTOMATION');
            }
        }
    }
    
    /**
     * Calculate years of experience from start date
     */
    public function calculate_years_experience($post_id) {
        // Check if it's an agent post
        if (get_post_type($post_id) !== 'agent') {
            return;
        }
        
        $date_started = get_field('date_started', $post_id);
        
        if (!empty($date_started)) {
            $start_date = new \DateTime($date_started);
            $current_date = new \DateTime();
            $diff = $current_date->diff($start_date);
            $years = $diff->y;
            
            // Update years of experience field
            update_field('years_experience', $years, $post_id);
            hp_log("Calculated {$years} years of experience for agent {$post_id}", 'info', 'AGENT_AUTOMATION');
        }
    }
    
    /**
     * Calculate performance metrics
     */
    public function calculate_performance_metrics($post_id, $post, $update) {
        // Skip on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Only for published agents
        if ($post->post_status !== 'publish') {
            return;
        }
        
        // Calculate active listings count
        $active_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'listing_agent',
                    'value' => $post_id,
                    'compare' => '='
                ]
            ],
            'tax_query' => [
                [
                    'taxonomy' => 'property_status',
                    'field' => 'slug',
                    'terms' => 'active'
                ]
            ]
        ]);
        
        $active_count = count($active_listings);
        update_field('active_listings_count', $active_count, $post_id);
        
        // Calculate average sale price from sold listings
        $sold_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'listing_agent',
                    'value' => $post_id,
                    'compare' => '='
                ]
            ],
            'tax_query' => [
                [
                    'taxonomy' => 'property_status',
                    'field' => 'slug',
                    'terms' => 'sold'
                ]
            ]
        ]);
        
        if (!empty($sold_listings)) {
            $total_price = 0;
            $count = 0;
            
            foreach ($sold_listings as $listing) {
                $price = get_field('listing_price', $listing->ID);
                if ($price > 0) {
                    $total_price += $price;
                    $count++;
                }
            }
            
            if ($count > 0) {
                $average_price = round($total_price / $count);
                update_field('average_sale_price', $average_price, $post_id);
            }
        }
        
        hp_log("Updated performance metrics for agent {$post_id}", 'info', 'AGENT_AUTOMATION');
    }
    
    /**
     * Get formatted agent name
     */
    public static function get_formatted_name($post_id, $include_suffix = true) {
        $first_name = get_field('first_name', $post_id);
        $middle_name = get_field('middle_name', $post_id);
        $last_name = get_field('last_name', $post_id);
        $suffix = get_field('suffix', $post_id);
        $display_name = get_field('display_name', $post_id);
        
        // Use display name if set
        if (!empty($display_name)) {
            return $display_name;
        }
        
        // Build from components
        $name_parts = [];
        if ($first_name) $name_parts[] = $first_name;
        if ($middle_name) $name_parts[] = $middle_name;
        if ($last_name) $name_parts[] = $last_name;
        if ($include_suffix && $suffix && $suffix !== '') {
            $name_parts[] = $suffix;
        }
        
        return implode(' ', $name_parts);
    }
    
    /**
     * Get formatted contact information
     */
    public static function get_formatted_contact($post_id) {
        $contact = [];
        
        // Phone numbers
        $office_phone = get_field('office_phone', $post_id);
        $direct_phone = get_field('direct_phone', $post_id);
        $mobile_phone = get_field('mobile_phone', $post_id);
        
        if ($direct_phone) {
            $contact['primary_phone'] = $direct_phone;
        } elseif ($mobile_phone) {
            $contact['primary_phone'] = $mobile_phone;
        } elseif ($office_phone) {
            $contact['primary_phone'] = $office_phone;
        }
        
        // Email
        $contact['email'] = get_field('email', $post_id);
        
        // Website
        $contact['website'] = get_field('website', $post_id);
        
        // Office address
        $office_address = get_field('office_address', $post_id);
        if ($office_address && is_array($office_address)) {
            $address_parts = [];
            if (!empty($office_address['street'])) $address_parts[] = $office_address['street'];
            if (!empty($office_address['city'])) $address_parts[] = $office_address['city'];
            if (!empty($office_address['state'])) $address_parts[] = $office_address['state'];
            if (!empty($office_address['zip'])) $address_parts[] = $office_address['zip'];
            
            $contact['office_address'] = implode(', ', $address_parts);
        }
        
        return $contact;
    }
    
    /**
     * Get agent's social media links
     */
    public static function get_social_links($post_id) {
        return [
            'facebook' => get_field('facebook_url', $post_id),
            'instagram' => get_field('instagram_url', $post_id),
            'linkedin' => get_field('linkedin_url', $post_id),
            'twitter' => get_field('twitter_url', $post_id),
            'youtube' => get_field('youtube_url', $post_id),
            'tiktok' => get_field('tiktok_url', $post_id),
            'pinterest' => get_field('pinterest_url', $post_id),
            'zillow' => get_field('zillow_url', $post_id),
            'realtor' => get_field('realtor_url', $post_id),
            'google_business' => get_field('google_business', $post_id)
        ];
    }
    
    /**
     * Get agent's performance stats
     */
    public static function get_performance_stats($post_id) {
        return [
            'total_sales_volume' => get_field('total_sales_volume', $post_id),
            'annual_sales_volume' => get_field('annual_sales_volume', $post_id),
            'total_transactions' => get_field('total_transactions', $post_id),
            'annual_transactions' => get_field('annual_transactions', $post_id),
            'average_sale_price' => get_field('average_sale_price', $post_id),
            'average_dom' => get_field('average_dom', $post_id),
            'list_to_sale_ratio' => get_field('list_to_sale_ratio', $post_id),
            'client_satisfaction' => get_field('client_satisfaction', $post_id),
            'total_reviews' => get_field('total_reviews', $post_id),
            'active_listings_count' => get_field('active_listings_count', $post_id)
        ];
    }
}