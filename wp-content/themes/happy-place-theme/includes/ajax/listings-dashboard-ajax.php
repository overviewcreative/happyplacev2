<?php
/**
 * AJAX Handler for Listings Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class HPH_Listings_Dashboard_Ajax {
    
    public function __construct() {
        error_log('HPH_Listings_Dashboard_Ajax constructor called');
        add_action('wp_ajax_hph_load_listings', [$this, 'load_listings']);
        add_action('wp_ajax_hph_delete_listing', [$this, 'delete_listing']);
        add_action('wp_ajax_hph_bulk_delete_listings', [$this, 'bulk_delete_listings']);
        add_action('wp_ajax_hph_update_listing_field', [$this, 'update_listing_field']);
        add_action('wp_ajax_hph_get_listings_stats', [$this, 'get_listings_stats']);
        add_action('wp_ajax_hph_get_listing_stats', [$this, 'get_listing_stats']);
        add_action('wp_ajax_hph_get_listings', [$this, 'get_listings']);
        add_action('wp_ajax_hph_get_listing_details', [$this, 'get_listing_details']);
    }
    
    private function verify_nonce() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hph_listings_nonce')) {
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
        error_log('HPH_Listings_Dashboard_Ajax::load_listings called');
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
        
        // Use the EXACT SAME query logic as archive-listing.php
        $args = [
            'post_type' => 'listing',
            'post_status' => 'publish', 
            'posts_per_page' => $per_page,
            'paged' => $page
        ];
        
        // Add search if provided
        if ($search) {
            $args['s'] = $search;
        }
        
        // Initialize meta_query only if we need it
        $meta_queries = [];
        
        // Add status filter (dashboard specific) - but only if not "all"
        if ($status && $status !== 'all') {
            $meta_queries[] = [
                'key' => 'listing_status',
                'value' => $status,
                'compare' => '='
            ];
        }
        
        // Add price range filter (same as archive)
        if ($price_min || $price_max) {
            $price_query = [
                'key' => 'listing_price',
                'type' => 'NUMERIC'
            ];
            
            if ($price_min && $price_max) {
                $price_query['value'] = [$price_min, $price_max];
                $price_query['compare'] = 'BETWEEN';
            } elseif ($price_min) {
                $price_query['value'] = $price_min;
                $price_query['compare'] = '>=';
            } elseif ($price_max) {
                $price_query['value'] = $price_max;
                $price_query['compare'] = '<=';
            }
            
            $meta_queries[] = $price_query;
        }
        
        // Add meta_query to args only if we have filters
        if (!empty($meta_queries)) {
            $args['meta_query'] = $meta_queries;
        }
        
        // ONLY filter by author if user is not admin (dashboard specific behavior)
        if (!$is_admin) {
            $args['author'] = $current_user_id;
        }
        
        // Execute query
        $query = new WP_Query($args);
        error_log('Query found posts: ' . ($query->have_posts() ? 'YES' : 'NO'));
        error_log('Query args: ' . json_encode($args));
        
        if (!$query->have_posts()) {
            wp_send_json_success([
                'listings' => '<div class="no-listings"><p>No listings found.</p></div>',
                'pagination' => '',
                'total' => 0,
                'current_page' => $page
            ]);
        }
        
        $listings_html = '';
        
        while ($query->have_posts()) {
            $query->the_post();
            $card_html = $this->render_listing_card(get_the_ID());
            if ($card_html) {
                $listings_html .= $card_html;
            }
        }
        
        wp_reset_postdata();
        
        // Generate pagination
        $pagination_html = $this->render_pagination($query->max_num_pages, $page);
        
        wp_send_json_success([
            'listings' => $listings_html,
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
     * Get listing statistics for dashboard
     */
    public function get_listings_stats() {
        $this->verify_nonce();
        $this->check_permissions();
        
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        
        // Base query args
        $base_args = [
            'post_type' => 'listing',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ];
        
        // If not admin, filter by author
        if (!$is_admin) {
            $base_args['author'] = $current_user_id;
        }
        
        $stats = [
            'active' => 0,
            'pending' => 0, 
            'sold' => 0,
            'draft' => 0,
            'total' => 0
        ];
        
        // Get counts for each status
        $statuses = ['active', 'pending', 'sold', 'draft'];
        
        foreach ($statuses as $status) {
            $args = $base_args;
            $args['meta_query'] = [
                [
                    'key' => 'listing_status',
                    'value' => $status,
                    'compare' => '='
                ]
            ];
            
            $query = new WP_Query($args);
            $stats[$status] = $query->found_posts;
        }
        
        // Get total count (all statuses)
        $total_query = new WP_Query($base_args);
        $stats['total'] = $total_query->found_posts;
        
        wp_send_json_success($stats);
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
    
    /**
     * Get listing stats (alias for get_listings_stats for dashboard compatibility)
     */
    public function get_listing_stats() {
        $this->get_listings_stats();
    }
    
    /**
     * Get listings (for dashboard grid)
     */
    public function get_listings() {
        // Check permissions and nonce - but be flexible with nonce names
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        // Check for either dashboard nonce or listing nonce
        $nonce_valid = false;
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'hph_dashboard_nonce')) {
            $nonce_valid = true;
        }
        if (isset($_POST['hph_dashboard_nonce']) && wp_verify_nonce($_POST['hph_dashboard_nonce'], 'hph_dashboard_nonce')) {
            $nonce_valid = true;
        }
        
        if (!$nonce_valid) {
            wp_send_json_error('Invalid nonce');
        }
        
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        
        // Get parameters
        $page = max(1, intval($_POST['page'] ?? 1));
        $per_page = 12; // Fixed for dashboard
        $search = sanitize_text_field($_POST['search'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        $sort = sanitize_text_field($_POST['sort'] ?? 'date-desc');
        
        // Build query args
        $args = [
            'post_type' => 'listing',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page
        ];
        
        // Add search if provided
        if ($search) {
            $args['s'] = $search;
        }
        
        // Add status filter
        if ($status && $status !== 'all') {
            $args['meta_query'] = [[
                'key' => 'listing_status',
                'value' => $status,
                'compare' => '='
            ]];
        }
        
        // Add sorting
        switch ($sort) {
            case 'price-desc':
                $args['meta_key'] = 'price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'price-asc':
                $args['meta_key'] = 'price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'date-asc':
                $args['orderby'] = 'date';
                $args['order'] = 'ASC';
                break;
            default: // date-desc
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }
        
        $query = new WP_Query($args);
        
        $listings_html = '';
        $total = $query->found_posts;
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $listing_id = get_the_ID();
                $price = get_field('price', $listing_id);
                $status = get_field('listing_status', $listing_id) ?: 'active';
                $bedrooms = get_field('bedrooms', $listing_id);
                $bathrooms = get_field('bathrooms_full', $listing_id);
                $sqft = get_field('square_feet', $listing_id);
                $city = get_field('city', $listing_id);
                $state = get_field('state', $listing_id);
                
                // Get featured image
                $featured_image = get_the_post_thumbnail_url($listing_id, 'large');
                if (!$featured_image) {
                    $featured_image = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="240" viewBox="0 0 400 240"%3E%3Crect width="400" height="240" fill="%23f3f4f6"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-family="system-ui" font-size="14" fill="%23666"%3ENo Image%3C/text%3E%3C/svg%3E';
                }
                
                // Status badge config
                $status_config = [
                    'active' => ['text' => 'Active', 'class' => 'hph-bg-success'],
                    'pending' => ['text' => 'Pending', 'class' => 'hph-bg-warning'],
                    'sold' => ['text' => 'Sold', 'class' => 'hph-bg-danger'],
                    'draft' => ['text' => 'Draft', 'class' => 'hph-bg-gray-500']
                ];
                
                $status_info = $status_config[$status] ?? $status_config['active'];
                
                $listings_html .= '<article class="listing-card-enhanced hph-card hph-card-elevated hph-h-full hph-flex hph-flex-col hph-transition-all hover:hph-shadow-xl hph-min-w-lg hph-relative" data-listing-id="' . $listing_id . '">';
                
                // Link wrapper for entire card
                $listings_html .= '<a href="' . get_permalink($listing_id) . '" class="hph-block hph-h-full hph-flex hph-flex-col">';
                
                // Image container with proper aspect ratio
                $listings_html .= '<div class="hph-relative hph-aspect-ratio-16-9 hph-overflow-hidden hph-bg-gray-200">';
                $listings_html .= '<img src="' . esc_url($featured_image) . '" alt="' . esc_attr(get_the_title()) . '" class="hph-w-full hph-h-full hph-object-cover" loading="lazy">';
                
                // Status badge
                $listings_html .= '<div class="hph-absolute hph-top-md hph-left-md">';
                $listings_html .= '<span class="' . $status_info['class'] . ' hph-text-white hph-px-sm hph-py-xs hph-rounded-md hph-text-xs hph-font-semibold">';
                $listings_html .= $status_info['text'];
                $listings_html .= '</span>';
                $listings_html .= '</div>';
                $listings_html .= '</div>';
                
                // Close link wrapper
                $listings_html .= '</a>';
                
                // Action buttons overlay (outside the link so they don't interfere)
                $listings_html .= '<div class="hph-absolute hph-top-md hph-right-md hph-flex hph-gap-xs hph-z-10" style="pointer-events: auto;">';
                $listings_html .= '<button onclick="event.preventDefault(); event.stopPropagation(); editListing(' . $listing_id . ')" class="hph-btn hph-btn-sm hph-bg-black hph-bg-opacity-75 hph-text-white hover:hph-bg-opacity-90 hph-transition-all" title="Edit Listing">';
                $listings_html .= '<i class="fas fa-edit"></i>';
                $listings_html .= '</button>';
                $listings_html .= '<button onclick="event.preventDefault(); event.stopPropagation(); deleteListing(' . $listing_id . ')" class="hph-btn hph-btn-sm hph-bg-red-600 hph-bg-opacity-75 hph-text-white hover:hph-bg-opacity-90 hph-transition-all" title="Delete Listing">';
                $listings_html .= '<i class="fas fa-trash"></i>';
                $listings_html .= '</button>';
                $listings_html .= '</div>';
                
                // Card content
                $listings_html .= '<div class="hph-p-md hph-flex-grow hph-flex hph-flex-col">';
                
                // Price & additional info row
                $listings_html .= '<div class="hph-flex hph-justify-between hph-items-start hph-mb-sm">';
                if ($price) {
                    $listings_html .= '<div class="hph-text-2xl hph-font-bold hph-text-primary">$' . number_format($price) . '</div>';
                }
                $listings_html .= '</div>';
                
                // Title
                $listings_html .= '<h3 class="hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-xs hph-line-clamp-1">' . get_the_title() . '</h3>';
                
                // Location
                if ($city && $state) {
                    $listings_html .= '<p class="hph-text-sm hph-text-gray-600 hph-mb-md hph-line-clamp-1">';
                    $listings_html .= '<i class="fas fa-map-marker-alt hph-mr-xs hph-text-primary"></i>';
                    $listings_html .= esc_html($city . ', ' . $state);
                    $listings_html .= '</p>';
                }
                
                // Property details
                $listings_html .= '<div class="hph-flex hph-flex-wrap hph-gap-md hph-text-sm hph-text-gray-700 hph-mt-auto">';
                if ($bedrooms) {
                    $listings_html .= '<span class="hph-flex hph-items-center">';
                    $listings_html .= '<i class="fas fa-bed hph-mr-xs hph-text-primary"></i>';
                    $listings_html .= $bedrooms . ' bed' . ($bedrooms != 1 ? 's' : '');
                    $listings_html .= '</span>';
                }
                if ($bathrooms) {
                    $listings_html .= '<span class="hph-flex hph-items-center">';
                    $listings_html .= '<i class="fas fa-bath hph-mr-xs hph-text-primary"></i>';
                    $listings_html .= $bathrooms . ' bath' . ($bathrooms != 1 ? 's' : '');
                    $listings_html .= '</span>';
                }
                if ($sqft) {
                    $listings_html .= '<span class="hph-flex hph-items-center">';
                    $listings_html .= '<i class="fas fa-ruler-combined hph-mr-xs hph-text-primary"></i>';
                    $listings_html .= number_format($sqft) . ' sqft';
                    $listings_html .= '</span>';
                }
                $listings_html .= '</div>';
                
                $listings_html .= '</div>';
                $listings_html .= '</article>';
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success([
            'listings' => $listings_html,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ]);
    }
    
    /**
     * Get single listing details for editing
     */
    public function get_listing_details() {
        // Check permissions and nonce
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        // Check for either dashboard nonce or listing nonce  
        $nonce_valid = false;
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'hph_dashboard_nonce')) {
            $nonce_valid = true;
        }
        if (isset($_POST['hph_dashboard_nonce']) && wp_verify_nonce($_POST['hph_dashboard_nonce'], 'hph_dashboard_nonce')) {
            $nonce_valid = true;
        }
        
        if (!$nonce_valid) {
            wp_send_json_error('Invalid nonce');
        }
        
        $listing_id = intval($_POST['listing_id'] ?? 0);
        
        if (!$listing_id) {
            wp_send_json_error('Invalid listing ID');
        }
        
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            wp_send_json_error('Listing not found');
        }
        
        // Get all the listing data
        $listing_data = [
            'ID' => $listing_id,
            'title' => $listing->post_title,
            'content' => $listing->post_content,
            'status' => $listing->post_status,
        ];
        
        // Get all ACF fields
        $fields = get_fields($listing_id) ?: [];
        $listing_data = array_merge($listing_data, $fields);
        
        wp_send_json_success($listing_data);
    }
}

// Initialize
new HPH_Listings_Dashboard_Ajax();
