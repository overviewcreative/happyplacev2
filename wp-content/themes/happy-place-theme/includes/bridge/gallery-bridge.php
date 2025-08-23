<?php
/**
 * Gallery Bridge Functions
 * 
 * Comprehensive interface for photo gallery, virtual tour, and floor plan functionality
 * All gallery data access should go through these functions.
 *
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get complete gallery data for listing
 * 
 * @param int $listing_id Listing ID
 * @return array Complete gallery configuration
 */
function hpt_get_listing_gallery_data($listing_id) {
    $images = hpt_get_listing_gallery($listing_id);
    $categories = hpt_get_gallery_categories($listing_id);
    
    return array(
        'images' => $images,
        'categories' => $categories,
        'virtual_tour_url' => hpt_get_listing_virtual_tour_url($listing_id),
        'video_tour_url' => hpt_get_listing_video($listing_id),
        'floor_plan_images' => hpt_get_listing_floor_plans($listing_id),
        'default_view' => get_field('gallery_default_view', $listing_id) ?: 'grid',
        'images_per_row' => get_field('gallery_images_per_row', $listing_id) ?: 4,
        'show_count' => get_field('gallery_show_count', $listing_id) !== false,
        'enable_download' => get_field('gallery_enable_download', $listing_id) ?: false,
        'enable_fullscreen' => get_field('gallery_enable_fullscreen', $listing_id) !== false,
        'lazy_load' => get_field('gallery_lazy_load', $listing_id) !== false,
        'section_id' => 'property-gallery-' . $listing_id
    );
}

/**
 * Get enhanced gallery images with categories
 * 
 * @param int $listing_id Listing ID
 * @return array Enhanced image data
 */
function hpt_get_listing_gallery_enhanced($listing_id) {
    $gallery = hpt_get_listing_gallery($listing_id);
    
    if (empty($gallery)) {
        return hpt_get_default_gallery_images();
    }
    
    return array_map(function($image, $index) {
        $category = get_field('image_category', $image['id']) ?: hpt_categorize_image_by_title($image['alt'] ?: $image['caption'] ?: '');
        
        return array(
            'id' => $image['id'],
            'url' => $image['url'],
            'thumbnail' => $image['thumbnail'],
            'medium' => $image['medium'],
            'large' => $image['large'],
            'title' => $image['alt'] ?: $image['caption'] ?: ('Image ' . ($index + 1)),
            'caption' => $image['caption'] ?: '',
            'alt' => $image['alt'] ?: '',
            'category' => $category,
            'order' => get_field('image_order', $image['id']) ?: $index
        );
    }, $gallery, array_keys($gallery));
}

/**
 * Get gallery categories
 * 
 * @param int $listing_id Listing ID
 * @return array Available categories
 */
function hpt_get_gallery_categories($listing_id = null) {
    $default_categories = array('All', 'Exterior', 'Interior', 'Kitchen', 'Bedrooms', 'Bathrooms', 'Living Areas', 'Outdoor');
    
    if (!$listing_id) {
        return $default_categories;
    }
    
    // Get custom categories if set
    $custom_categories = get_field('gallery_categories', $listing_id);
    if (!empty($custom_categories) && is_array($custom_categories)) {
        array_unshift($custom_categories, 'All');
        return $custom_categories;
    }
    
    return $default_categories;
}

/**
 * Auto-categorize image by title/filename
 * 
 * @param string $title Image title or filename
 * @return string Category name
 */
function hpt_categorize_image_by_title($title) {
    $title = strtolower($title);
    
    $categories = array(
        'Kitchen' => array('kitchen', 'dining', 'cook'),
        'Bedrooms' => array('bedroom', 'bed', 'master', 'guest'),
        'Bathrooms' => array('bathroom', 'bath', 'toilet', 'shower'),
        'Living Areas' => array('living', 'family', 'great', 'room', 'den', 'office'),
        'Exterior' => array('exterior', 'front', 'facade', 'curb', 'street'),
        'Outdoor' => array('backyard', 'patio', 'deck', 'garden', 'pool', 'yard')
    );
    
    foreach ($categories as $category => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($title, $keyword) !== false) {
                return $category;
            }
        }
    }
    
    return 'Interior';
}

/**
 * Get default gallery images (fallback)
 * 
 * @return array Default image set
 */
function hpt_get_default_gallery_images() {
    return array(
        array(
            'id' => 0,
            'url' => get_template_directory_uri() . '/assets/images/property-placeholder-1.jpg',
            'thumbnail' => get_template_directory_uri() . '/assets/images/property-placeholder-1.jpg',
            'medium' => get_template_directory_uri() . '/assets/images/property-placeholder-1.jpg',
            'large' => get_template_directory_uri() . '/assets/images/property-placeholder-1.jpg',
            'title' => 'Front Exterior',
            'caption' => 'Beautiful curb appeal with manicured landscaping',
            'alt' => 'Property exterior view',
            'category' => 'Exterior',
            'order' => 0
        ),
        array(
            'id' => 0,
            'url' => get_template_directory_uri() . '/assets/images/property-placeholder-2.jpg',
            'thumbnail' => get_template_directory_uri() . '/assets/images/property-placeholder-2.jpg',
            'medium' => get_template_directory_uri() . '/assets/images/property-placeholder-2.jpg',
            'large' => get_template_directory_uri() . '/assets/images/property-placeholder-2.jpg',
            'title' => 'Living Room',
            'caption' => 'Spacious living room with natural light',
            'alt' => 'Living room interior',
            'category' => 'Living Areas',
            'order' => 1
        ),
        array(
            'id' => 0,
            'url' => get_template_directory_uri() . '/assets/images/property-placeholder-3.jpg',
            'thumbnail' => get_template_directory_uri() . '/assets/images/property-placeholder-3.jpg',
            'medium' => get_template_directory_uri() . '/assets/images/property-placeholder-3.jpg',
            'large' => get_template_directory_uri() . '/assets/images/property-placeholder-3.jpg',
            'title' => 'Kitchen',
            'caption' => 'Modern kitchen with stainless steel appliances',
            'alt' => 'Kitchen interior',
            'category' => 'Kitchen',
            'order' => 2
        )
    );
}

/**
 * Get gallery configuration for JavaScript
 * 
 * @param int $listing_id Listing ID
 * @return array JavaScript configuration
 */
function hpt_get_gallery_js_config($listing_id) {
    $data = hpt_get_listing_gallery_data($listing_id);
    
    return array(
        'images' => $data['images'],
        'enableDownload' => $data['enable_download'],
        'lazyLoad' => $data['lazy_load'],
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hph_gallery_nonce'),
        'listingId' => $listing_id
    );
}

/**
 * Enqueue gallery assets
 * 
 * @param string $handle Optional handle suffix
 */
function hpt_enqueue_gallery_assets($handle = '') {
    $handle_suffix = $handle ? '-' . $handle : '';
    
    // Enqueue CSS
    wp_enqueue_style(
        'hph-gallery-components' . $handle_suffix,
        get_template_directory_uri() . '/assets/css/framework/03-components/hph-gallery-components.css',
        array(),
        HPH_VERSION
    );
    
    // Enqueue JavaScript
    wp_enqueue_script(
        'hph-listing-gallery' . $handle_suffix,
        get_template_directory_uri() . '/assets/js/components/listing-gallery.js',
        array('jquery'),
        HPH_VERSION,
        true
    );
    
    // Enqueue Font Awesome if not already loaded
    if (!wp_style_is('font-awesome', 'enqueued')) {
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
            array(),
            '6.5.1'
        );
    }
}

/**
 * Register gallery AJAX handlers
 */
function hpt_register_gallery_ajax_handlers() {
    add_action('wp_ajax_hph_load_more_images', 'hpt_ajax_load_more_images');
    add_action('wp_ajax_nopriv_hph_load_more_images', 'hpt_ajax_load_more_images');
    
    add_action('wp_ajax_hph_get_image_data', 'hpt_ajax_get_image_data');
    add_action('wp_ajax_nopriv_hph_get_image_data', 'hpt_ajax_get_image_data');
}

/**
 * AJAX handler for loading more images
 */
function hpt_ajax_load_more_images() {
    check_ajax_referer('hph_gallery_nonce', 'nonce');
    
    $listing_id = intval($_POST['listing_id'] ?? 0);
    $offset = intval($_POST['offset'] ?? 0);
    $limit = intval($_POST['limit'] ?? 12);
    $category = sanitize_text_field($_POST['category'] ?? 'all');
    
    if (!$listing_id) {
        wp_send_json_error('Invalid listing ID');
    }
    
    $images = hpt_get_listing_gallery_enhanced($listing_id);
    
    // Filter by category if specified
    if ($category !== 'all') {
        $images = array_filter($images, function($image) use ($category) {
            return strtolower(str_replace(' ', '-', $image['category'])) === $category;
        });
    }
    
    // Get requested slice
    $images_slice = array_slice($images, $offset, $limit);
    
    wp_send_json_success(array(
        'images' => $images_slice,
        'hasMore' => count($images) > ($offset + $limit)
    ));
}

/**
 * AJAX handler for getting image data
 */
function hpt_ajax_get_image_data() {
    check_ajax_referer('hph_gallery_nonce', 'nonce');
    
    $image_id = intval($_POST['image_id'] ?? 0);
    
    if (!$image_id) {
        wp_send_json_error('Invalid image ID');
    }
    
    $image_data = array(
        'id' => $image_id,
        'url' => wp_get_attachment_image_url($image_id, 'full'),
        'title' => get_the_title($image_id),
        'caption' => wp_get_attachment_caption($image_id),
        'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
        'metadata' => wp_get_attachment_metadata($image_id)
    );
    
    wp_send_json_success($image_data);
}

// Initialize gallery AJAX handlers
add_action('init', 'hpt_register_gallery_ajax_handlers');
