<?php
/**
 * HPH Image Helpers
 * 
 * Helper functions for handling images throughout the theme
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register a template part for asset loading
 * Simple logging function for tracking template part usage
 * 
 * @param string $template_part Template part name
 */
function hph_register_template_part($template_part) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("HPH: Template part registered: {$template_part}");
    }
}

/**
 * Get image URL from theme assets directory
 * 
 * @param string $image_path Relative path from assets/images/
 * @param bool $check_exists Whether to check if file exists
 * @return string Full URL to image or empty string if not found
 */
function hph_get_image_url($image_path, $check_exists = false) {
    // Remove leading slash if present
    $image_path = ltrim($image_path, '/');
    
    // Build full file path for existence check
    $full_path = get_template_directory() . '/assets/images/' . $image_path;
    
    // Check if file exists if requested
    if ($check_exists && !file_exists($full_path)) {
        return null;
    }
    
    // Build and return properly encoded URL
    $base_url = get_template_directory_uri() . '/assets/images/';
    $encoded_path = implode('/', array_map('rawurlencode', explode('/', $image_path)));
    
    return $base_url . $encoded_path;
}

/**
 * Get theme image URL with fallback
 * 
 * @param string $image_path Primary image path
 * @param string $fallback_path Fallback image path
 * @return string Image URL (fallback if primary doesn't exist)
 */
function hph_get_image_url_with_fallback($image_path, $fallback_path = 'placeholder.svg') {
    $url = hph_get_image_url($image_path, true);
    
    if ($url === null) {
        $url = hph_get_image_url($fallback_path);
    }
    
    return $url;
}

/**
 * Get responsive image data for a theme image
 * 
 * @param string $image_path Path to image
 * @param array $sizes Array of sizes to generate (e.g., ['sm' => 480, 'md' => 768])
 * @return array Array with 'src', 'srcset', and 'sizes' keys
 */
function hph_get_responsive_image_data($image_path, $sizes = []) {
    $base_url = hph_get_image_url($image_path);
    
    $data = [
        'src' => $base_url,
        'srcset' => '',
        'sizes' => ''
    ];
    
    if (empty($sizes)) {
        return $data;
    }
    
    $srcset_parts = [];
    $sizes_parts = [];
    
    foreach ($sizes as $size_name => $width) {
        // For theme images, we'd typically have pre-generated sizes
        // This is a simplified version - in practice you might have actual resized images
        $srcset_parts[] = $base_url . ' ' . $width . 'w';
        $sizes_parts[] = "(max-width: {$width}px) {$width}px";
    }
    
    $data['srcset'] = implode(', ', $srcset_parts);
    $data['sizes'] = implode(', ', $sizes_parts) . ', 100vw';
    
    return $data;
}

/**
 * Output an optimized image tag
 * 
 * @param string $image_path Path to image
 * @param array $args Image arguments
 */
function hph_image($image_path, $args = []) {
    $defaults = [
        'alt' => '',
        'class' => '',
        'id' => '',
        'loading' => 'lazy',
        'check_exists' => true,
        'fallback' => 'placeholder.svg',
        'responsive' => false,
        'sizes' => []
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // Get image URL with optional fallback
    if ($args['check_exists']) {
        $src = hph_get_image_url_with_fallback($image_path, $args['fallback']);
    } else {
        $src = hph_get_image_url($image_path);
    }
    
    // Build attributes
    $attributes = [
        'src' => esc_url($src),
        'alt' => esc_attr($args['alt'])
    ];
    
    if ($args['class']) {
        $attributes['class'] = esc_attr($args['class']);
    }
    
    if ($args['id']) {
        $attributes['id'] = esc_attr($args['id']);
    }
    
    if ($args['loading']) {
        $attributes['loading'] = esc_attr($args['loading']);
    }
    
    // Add responsive attributes if requested
    if ($args['responsive'] && !empty($args['sizes'])) {
        $responsive_data = hph_get_responsive_image_data($image_path, $args['sizes']);
        if ($responsive_data['srcset']) {
            $attributes['srcset'] = esc_attr($responsive_data['srcset']);
            $attributes['sizes'] = esc_attr($responsive_data['sizes']);
        }
    }
    
    // Build and output the img tag
    $attr_string = '';
    foreach ($attributes as $name => $value) {
        $attr_string .= sprintf(' %s="%s"', $name, $value);
    }
    
    echo sprintf('<img%s>', $attr_string);
}

/**
 * Get all available images in the theme images directory
 * 
 * @param string $subdirectory Optional subdirectory to scan
 * @return array Array of image filenames
 */
function hph_get_available_images($subdirectory = '') {
    $images_dir = get_template_directory() . '/assets/images';
    
    if ($subdirectory) {
        $images_dir .= '/' . trim($subdirectory, '/');
    }
    
    if (!is_dir($images_dir)) {
        return [];
    }
    
    $images = [];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
    
    $files = scandir($images_dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        if (in_array($extension, $allowed_extensions)) {
            $images[] = $subdirectory ? $subdirectory . '/' . $file : $file;
        }
    }
    
    return $images;
}

/**
 * Check if theme image exists
 * 
 * @param string $image_path Path to image relative to assets/images
 * @return bool True if image exists
 */
function hph_image_exists($image_path) {
    $full_path = get_template_directory() . '/assets/images/' . ltrim($image_path, '/');
    return file_exists($full_path);
}

/**
 * Get image dimensions for a theme image
 * 
 * @param string $image_path Path to image
 * @return array|false Array with 'width' and 'height' keys, or false if image not found
 */
function hph_get_image_dimensions($image_path) {
    $full_path = get_template_directory() . '/assets/images/' . ltrim($image_path, '/');
    
    if (!file_exists($full_path)) {
        return false;
    }
    
    $size = getimagesize($full_path);
    
    if ($size === false) {
        return false;
    }
    
    return [
        'width' => $size[0],
        'height' => $size[1]
    ];
}

/**
 * Register image assets for preloading
 * 
 * @param array $images Array of image paths to preload
 */
function hph_preload_images($images) {
    if (!is_array($images)) {
        $images = [$images];
    }
    
    foreach ($images as $image_path) {
        $url = hph_get_image_url($image_path);
        echo sprintf('<link rel="preload" href="%s" as="image">', esc_url($url));
    }
}

/**
 * Smart image selection based on context
 * 
 * @param array $options Array of image options with conditions
 * @return string|null Selected image URL
 */
function hph_smart_image_select($options) {
    foreach ($options as $option) {
        $condition = $option['condition'] ?? true;
        $image_path = $option['image'] ?? '';
        
        if ($condition && $image_path) {
            $url = hph_get_image_url($image_path, true);
            if ($url !== null) {
                return $url;
            }
        }
    }
    
    return null;
}
