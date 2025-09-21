<?php
/**
 * AJAX Handler for Listings Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class HPH_Listings_Dashboard_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_hph_load_listings', [$this, 'load_listings']);
        add_action('wp_ajax_hph_delete_listing', [$this, 'delete_listing']);
        add_action('wp_ajax_hph_bulk_delete_listings', [$this, 'bulk_delete_listings']);
        add_action('wp_ajax_hph_update_listing_field', [$this, 'update_listing_field']);
    }
    
    private function verify_nonce() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hph_dashboard_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
    }
    
    private function check_permissions() {
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
    }
    
    /**
     * Load listings for dashboard with pagination
     */
    public function load_listings() {
        $this->verify_nonce();
        $this->check_permissions();
        
        // Get parameters
        $page = max(1, intval($_POST['page'] ?? 1));
        $per_page = max(1, min(50, intval($_POST['per_page'] ?? 12)));
        $search = sanitize_text_field($_POST['search'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        $price_min = intval($_POST['price_min'] ?? 0);
        $price_max = intval($_POST['price_max'] ?? 0);
        
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        
        // Build query args
        $args = [
            'post_type' => 'listing',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => 'publish',
            'meta_query' => ['relation' => 'AND']
        ];
        
        // If not admin, show only user's listings
        if (!$is_admin) {
            $args['author'] = $current_user_id;
        }
        
        // Search in title and content
        if ($search) {
            $args['s'] = $search;
        }
        
        // Filter by status
        if ($status) {
            $args['meta_query'][] = [
                'key' => 'listing_status',
                'value' => $status,
                'compare' => '='
            ];
        }
        
        // Price range filter
        if ($price_min > 0) {
            $args['meta_query'][] = [
                'key' => 'listing_price',
                'value' => $price_min,
                'type' => 'NUMERIC',
                'compare' => '>='
            ];
        }
        
        if ($price_max > 0) {
            $args['meta_query'][] = [
                'key' => 'listing_price',
                'value' => $price_max,
                'type' => 'NUMERIC',
                'compare' => '<='
            ];
        }
        
        // Execute query
        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            wp_send_json_success([
                'html' => '<div class="no-listings"><p>No listings found.</p></div>',
                'pagination' => '',
                'total' => 0,
                'current_page' => $page
            ]);
        }
        
        $listings_html = '';
        
        while ($query->have_posts()) {
            $query->the_post();
            $listings_html .= $this->render_listing_card(get_the_ID());
        }
        
        wp_reset_postdata();
        
        // Generate pagination
        $pagination_html = $this->render_pagination($query->max_num_pages, $page);
        
        wp_send_json_success([
            'html' => $listings_html,
            'pagination' => $pagination_html,
            'total' => $query->found_posts,
            'current_page' => $page
        ]);
    }
    
    /**
     * Delete a single listing
     */
    public function delete_listing() {
        $this->verify_nonce();
        $this->check_permissions();
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        
        if (!$listing_id) {
            wp_send_json_error('Invalid listing ID');
        }
        
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            wp_send_json_error('Listing not found');
        }
        
        // Check permissions
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        
        if (!$is_admin && $listing->post_author != $current_user_id) {
            wp_send_json_error('Permission denied');
        }
        
        // Delete the listing
        if (wp_delete_post($listing_id, true)) {
            wp_send_json_success('Listing deleted successfully');
        } else {
            wp_send_json_error('Failed to delete listing');
        }
    }
    
    /**
     * Bulk delete listings
     */
    public function bulk_delete_listings() {
        $this->verify_nonce();
        $this->check_permissions();
        
        $listing_ids = $_POST['listing_ids'] ?? [];
        
        if (!is_array($listing_ids) || empty($listing_ids)) {
            wp_send_json_error('No listings selected');
        }
        
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        $success_count = 0;
        
        foreach ($listing_ids as $listing_id) {
            $listing_id = intval($listing_id);
            if (!$listing_id) continue;
            
            $listing = get_post($listing_id);
            if (!$listing || $listing->post_type !== 'listing') continue;
            
            // Check permissions
            if (!$is_admin && $listing->post_author != $current_user_id) continue;
            
            // Delete the listing
            if (wp_delete_post($listing_id, true)) {
                $success_count++;
            }
        }
        
        wp_send_json_success([
            'message' => "$success_count listings processed successfully",
            'count' => $success_count
        ]);
    }
    
    /**
     * Update individual listing field (for inline editing)
     */
    public function update_listing_field() {
        $this->verify_nonce();
        $this->check_permissions();
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        $field = sanitize_text_field($_POST['field'] ?? '');
        $value = sanitize_text_field($_POST['value'] ?? '');
        
        if (!$listing_id) {
            wp_send_json_error('Invalid listing ID');
        }
        
        if (!$field) {
            wp_send_json_error('Invalid field');
        }
        
        // Check permissions - user must own listing or be admin
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            wp_send_json_error('Listing not found');
        }
        
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        
        if (!$is_admin && $listing->post_author != $current_user_id) {
            wp_send_json_error('Permission denied');
        }
        
        // Update the field based on field type
        switch ($field) {
            case 'price':
            case 'listing_price':
                $price = floatval($value);
                if ($price < 0) {
                    wp_send_json_error('Price must be a positive number');
                }
                
                // Try both possible field names
                $updated = update_field('listing_price', $price, $listing_id);
                if (!$updated) {
                    $updated = update_field('price', $price, $listing_id);
                }
                
                if ($updated) {
                    wp_send_json_success([
                        'message' => 'Price updated successfully',
                        'field' => $field,
                        'value' => $price,
                        'formatted_value' => '$' . number_format($price)
                    ]);
                } else {
                    wp_send_json_error('Failed to update price');
                }
                break;
                
            case 'listing_status':
                $allowed_statuses = ['active', 'pending', 'sold', 'new', 'draft'];
                if (!in_array($value, $allowed_statuses)) {
                    wp_send_json_error('Invalid status');
                }
                
                $updated = update_field('listing_status', $value, $listing_id);
                
                if ($updated) {
                    wp_send_json_success([
                        'message' => 'Status updated successfully',
                        'field' => $field,
                        'value' => $value,
                        'formatted_value' => ucfirst($value)
                    ]);
                } else {
                    wp_send_json_error('Failed to update status');
                }
                break;
                
            default:
                wp_send_json_error('Unsupported field: ' . $field);
        }
    }
    
    /**
     * Render listing card with dashboard functionality
     */
    public function render_listing_card($listing_id) {
        // Get listing data
        $listing_price = floatval(get_field('listing_price', $listing_id));
        $price = $listing_price > 0 ? $listing_price : floatval(get_field('price', $listing_id));
        
        $bedrooms = get_field('bedrooms', $listing_id);
        $bathrooms = get_field('bathrooms', $listing_id);
        $square_feet = get_field('square_feet', $listing_id);
        $listing_status = get_field('listing_status', $listing_id) ?: 'active';
        $is_featured = get_field('is_featured', $listing_id);
        
        // Address components
        $street_number = get_field('street_number', $listing_id);
        $street_name = get_field('street_name', $listing_id);
        $street_type = get_field('street_type', $listing_id);
        $city = get_field('city', $listing_id);
        $state = get_field('state', $listing_id);
        
        // Build address
        $address = '';
        if ($street_number) $address .= $street_number . ' ';
        if ($street_name) $address .= $street_name . ' ';
        if ($street_type) $address .= $street_type;
        
        if (!$address) {
            $address = get_the_title($listing_id);
        }
        
        // Location
        $location = '';
        if ($city) {
            $location = $city;
            if ($state) $location .= ', ' . $state;
        }
        
        // Featured image
        $featured_image = get_the_post_thumbnail_url($listing_id, 'large');
        if (!$featured_image) {
            $featured_image = get_template_directory_uri() . '/assets/images/placeholder-property.jpg';
        }
        
        // Status color
        $status_color = $this->get_status_color($listing_status);
        
        return '<article class="listing-card dashboard-card" data-listing-id="' . $listing_id . '" style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; transition: all 0.3s ease; margin-bottom: 1.5rem;">
            <!-- Image -->
            <div style="position: relative; height: 240px; background: #f8f9fa;">
                <img src="' . esc_url($featured_image) . '" alt="' . esc_attr($address) . '" style="width: 100%; height: 100%; object-fit: cover;">
                <span style="position: absolute; top: 12px; left: 12px; background: ' . $status_color . '; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: capitalize;">' . esc_html($listing_status) . '</span>
                <button class="edit-listing-btn" data-listing-id="' . $listing_id . '" style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.7); color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 12px;"><i class="fas fa-edit"></i> Edit</button>
            </div>
            <!-- Content -->
            <div style="padding: 1rem;">
                ' . ($price > 0 ? '<div class="editable-price" data-listing-id="' . $listing_id . '" data-field="price" style="font-size: 1.5rem; font-weight: 700; color: #007bff; cursor: pointer; margin-bottom: 12px;" title="Click to edit price">$' . number_format($price) . '</div>' : '') . '
                <h3 style="margin: 0 0 8px 0; font-size: 1.1rem; font-weight: 600; color: #333;"><a href="' . get_permalink($listing_id) . '" style="color: inherit; text-decoration: none;">' . esc_html($address) . '</a></h3>
                ' . ($location ? '<p style="margin: 0 0 12px 0; color: #666; font-size: 0.9rem;"><i class="fas fa-map-marker-alt" style="margin-right: 6px; color: #007bff;"></i>' . esc_html($location) . '</p>' : '') . '
                <div style="display: flex; flex-wrap: wrap; gap: 16px; font-size: 0.9rem; color: #555;">
                    ' . ($bedrooms > 0 ? '<span><i class="fas fa-bed" style="color: #007bff; margin-right: 4px;"></i>' . $bedrooms . ' bed' . ($bedrooms != 1 ? 's' : '') . '</span>' : '') . '
                    ' . ($bathrooms > 0 ? '<span><i class="fas fa-bath" style="color: #007bff; margin-right: 4px;"></i>' . $bathrooms . ' bath' . ($bathrooms != 1 ? 's' : '') . '</span>' : '') . '
                    ' . ($square_feet > 0 ? '<span><i class="fas fa-ruler-combined" style="color: #007bff; margin-right: 4px;"></i>' . number_format($square_feet) . ' sqft</span>' : '') . '
                </div>
                ' . (($price > 0 && $square_feet > 0) ? '<div style="margin-top: 8px; font-size: 0.8rem; color: #999;">$' . number_format(round($price / $square_feet)) . ' per sqft</div>' : '') . '
            </div>
        </article>';
    }
    
    /**
     * Get status badge color
     */
    private function get_status_color($status) {
        $colors = [
            'active' => '#28a745',
            'pending' => '#ffc107',
            'sold' => '#dc3545',
            'draft' => '#6c757d',
            'new' => '#007bff'
        ];
        
        return $colors[strtolower($status)] ?? '#007bff';
    }
    
    /**
     * Render pagination
     */
    private function render_pagination($total_pages, $current_page) {
        if ($total_pages <= 1) {
            return '';
        }
        
        $html = '<div class="pagination" style="display: flex; justify-content: center; gap: 8px; margin-top: 2rem;">';
        
        // Previous button
        if ($current_page > 1) {
            $html .= '<button class="page-btn" onclick="changePage(' . ($current_page - 1) . ')" style="padding: 8px 12px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Previous</button>';
        }
        
        // Page numbers
        $start = max(1, $current_page - 2);
        $end = min($total_pages, $current_page + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $current_page ? 'background: #007bff; color: white;' : 'background: white; color: #333;';
            $html .= '<button class="page-btn" onclick="changePage(' . $i . ')" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; ' . $active . '">' . $i . '</button>';
        }
        
        // Next button
        if ($current_page < $total_pages) {
            $html .= '<button class="page-btn" onclick="changePage(' . ($current_page + 1) . ')" style="padding: 8px 12px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Next</button>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}

// Initialize
new HPH_Listings_Dashboard_Ajax();
