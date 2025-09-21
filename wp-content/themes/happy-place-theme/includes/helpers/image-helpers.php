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
 * Get image URL from theme assets directory OR WordPress media library
 *
 * @param string|int $image_path Relative path from assets/images/, attachment ID, or filename in media library
 * @param bool $check_exists Whether to check if file exists (theme assets only)
 * @param string $size Image size for WordPress attachments (thumbnail, medium, large, full)
 * @return string|array Full URL to image, image array for WordPress attachments, or empty string if not found
 */
function hph_get_image_url($image_path, $check_exists = false, $size = 'large') {
    // Handle WordPress attachment ID
    if (is_numeric($image_path)) {
        return hph_get_wordpress_image($image_path, $size);
    }

    // Handle WordPress media library filename lookup
    if (is_string($image_path) && !empty($image_path)) {
        // First try to find in WordPress media library by filename
        $attachment_id = hph_get_attachment_id_by_filename($image_path);
        if ($attachment_id) {
            return hph_get_wordpress_image($attachment_id, $size);
        }
    }

    // Fallback to theme assets directory (original behavior)
    return hph_get_theme_asset_url($image_path, $check_exists);
}

/**
 * Get WordPress media library image data
 *
 * @param int $attachment_id WordPress attachment ID
 * @param string $size Image size
 * @return array|string Image array with full data or just URL
 */
function hph_get_wordpress_image($attachment_id, $size = 'large') {
    if (!$attachment_id || !wp_attachment_is_image($attachment_id)) {
        return '';
    }

    // Get the main image data
    $image_data = wp_get_attachment_image_src($attachment_id, $size);
    if (!$image_data) {
        return '';
    }

    // Get image metadata
    $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
    $caption = wp_get_attachment_caption($attachment_id);
    $title = get_the_title($attachment_id);

    // Build comprehensive image array (compatible with template parts)
    $image_array = array(
        'ID' => $attachment_id,
        'id' => $attachment_id, // Lowercase for ACF compatibility
        'url' => $image_data[0],
        'width' => $image_data[1],
        'height' => $image_data[2],
        'alt' => $alt_text ?: $title,
        'caption' => $caption,
        'title' => $title,
        'description' => get_post_field('post_content', $attachment_id),
        'sizes' => array()
    );

    // Add all available image sizes
    $image_sizes = get_intermediate_image_sizes();
    $image_sizes[] = 'full'; // Add full size

    foreach ($image_sizes as $image_size) {
        $size_data = wp_get_attachment_image_src($attachment_id, $image_size);
        if ($size_data) {
            $image_array['sizes'][$image_size] = array(
                'url' => $size_data[0],
                'width' => $size_data[1],
                'height' => $size_data[2]
            );

            // Add width/height attributes for each size (ACF format)
            $image_array['sizes'][$image_size . '-width'] = $size_data[1];
            $image_array['sizes'][$image_size . '-height'] = $size_data[2];
        }
    }

    return $image_array;
}

/**
 * Get attachment ID by filename
 *
 * @param string $filename Image filename
 * @return int|false Attachment ID or false if not found
 */
function hph_get_attachment_id_by_filename($filename) {
    // Remove file extension for broader search
    $filename_base = pathinfo($filename, PATHINFO_FILENAME);

    global $wpdb;

    // Search for attachment by filename (both with and without extension)
    $attachment_id = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts}
         WHERE post_type = 'attachment'
         AND (post_title = %s OR post_title = %s OR post_name = %s OR post_name = %s)
         ORDER BY ID DESC LIMIT 1",
        $filename,
        $filename_base,
        sanitize_title($filename),
        sanitize_title($filename_base)
    ));

    // Also search in meta_value for cases where filename is stored differently
    if (!$attachment_id) {
        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_wp_attached_file'
             AND meta_value LIKE %s
             ORDER BY post_id DESC LIMIT 1",
            '%' . $wpdb->esc_like($filename) . '%'
        ));
    }

    return $attachment_id ? (int) $attachment_id : false;
}

/**
 * Get theme asset URL (original function logic)
 *
 * @param string $image_path Relative path from assets/images/
 * @param bool $check_exists Whether to check if file exists
 * @return string Full URL to image or empty string if not found
 */
function hph_get_theme_asset_url($image_path, $check_exists = false) {
    // Remove leading slash if present
    $image_path = ltrim($image_path, '/');

    // Build full file path for existence check
    $full_path = get_template_directory() . '/assets/images/' . $image_path;

    // Check if file exists if requested
    if ($check_exists && !file_exists($full_path)) {
        return '';
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
 * Get fallback image for different content types
 *
 * @param string $type Content type (listing, agent, staff, property, person, default)
 * @param string $size Image size
 * @return array Image data with id, url, and alt
 */
function hph_get_typed_fallback_image($type = 'default', $size = 'medium') {
    $fallback_images = [
        'listing' => [
            'filename' => 'listing-placeholder.jpg',
            'alt' => __('Property image', 'happy-place-theme')
        ],
        'property' => [
            'filename' => 'listing-placeholder.jpg',
            'alt' => __('Property image', 'happy-place-theme')
        ],
        'agent' => [
            'filename' => 'agent-placeholder.jpg',
            'alt' => __('Agent photo', 'happy-place-theme')
        ],
        'staff' => [
            'filename' => 'team-placeholder.jpg',
            'alt' => __('Staff photo', 'happy-place-theme')
        ],
        'team' => [
            'filename' => 'team-placeholder.jpg',
            'alt' => __('Team member photo', 'happy-place-theme')
        ],
        'person' => [
            'filename' => 'placeholder-agent.jpg',
            'alt' => __('Person photo', 'happy-place-theme')
        ],
        'default' => [
            'filename' => 'placeholder.jpg',
            'alt' => __('Image placeholder', 'happy-place-theme')
        ]
    ];

    $fallback = $fallback_images[$type] ?? $fallback_images['default'];

    // Try to find actual placeholder file, with fallbacks
    $possible_files = [
        $fallback['filename'],
        'placeholder-' . $type . '.jpg',
        $type . '-placeholder.jpg',  // Alternative naming
        'placeholder-agent.jpg',     // Generic person placeholder
        'agent-placeholder.jpg',     // Alternative person placeholder
        'placeholder-property.jpg',  // Generic property placeholder
        'listing-placeholder.jpg',   // Alternative property placeholder
        'placeholder-post.jpg',      // Generic post placeholder
        'placeholder.jpg',
        'placeholder.svg',
        'no-image.jpg'
    ];

    $base_path = get_template_directory() . '/assets/images/';
    $base_url = get_template_directory_uri() . '/assets/images/';

    foreach ($possible_files as $filename) {
        if (file_exists($base_path . $filename)) {
            return [
                'id' => 0,
                'url' => $base_url . $filename,
                'alt' => $fallback['alt']
            ];
        }
    }

    // Ultimate fallback - return a data URI for a simple gray placeholder
    $svg_content = sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="#f3f4f6"/><text x="50%%" y="50%%" text-anchor="middle" dy=".3em" fill="#9ca3af" font-family="Arial" font-size="16">%s</text></svg>',
        esc_html($fallback['alt'])
    );
    $data_uri = 'data:image/svg+xml;base64,' . base64_encode($svg_content);

    return [
        'id' => 0,
        'url' => $data_uri,
        'alt' => $fallback['alt']
    ];
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
 * @return string|array|null Selected image URL or array
 */
function hph_smart_image_select($options) {
    foreach ($options as $option) {
        $condition = $option['condition'] ?? true;
        $image_path = $option['image'] ?? '';

        if ($condition && $image_path) {
            $result = hph_get_image_url($image_path, true);
            if (!empty($result)) {
                return $result;
            }
        }
    }

    return null;
}

/**
 * Get just the URL from WordPress media library image
 * Convenience function that extracts just the URL from the image array
 *
 * @param string|int $image_path Image filename, path, or attachment ID
 * @param string $size Image size for WordPress attachments
 * @param bool $webp Whether to return WebP optimized URL if available
 * @return string Image URL or empty string
 */
function hph_get_image_url_only($image_path, $size = 'large', $webp = true) {
    $result = hph_get_image_url($image_path, false, $size);

    $url = '';

    // If it's an array (WordPress image), return just the URL
    if (is_array($result) && isset($result['url'])) {
        $url = $result['url'];
        $attachment_id = $result['ID'] ?? $result['id'] ?? 0;
    } elseif (is_string($result)) {
        // If it's already a string (theme asset), return as-is
        $url = $result;
        $attachment_id = 0;
    }

    // Apply WebP optimization if requested and available
    if ($webp && !empty($url) && function_exists('hph_get_webp_url')) {
        $url = hph_get_webp_url($url, $attachment_id);
    }

    return $url;
}

/**
 * Get WordPress image with fallback to theme asset
 *
 * @param string|int $media_image Media library filename or attachment ID
 * @param string $theme_fallback Theme asset fallback path
 * @param string $size Image size for WordPress attachments
 * @return array|string Image array/URL or fallback
 */
function hph_get_image_with_fallback($media_image, $theme_fallback, $size = 'large') {
    // Try media library first
    if (!empty($media_image)) {
        $result = hph_get_image_url($media_image, false, $size);
        if (!empty($result)) {
            return $result;
        }
    }

    // Fallback to theme asset
    return hph_get_theme_asset_url($theme_fallback, true);
}

/**
 * Check if image exists in WordPress media library
 *
 * @param string $filename Image filename to search for
 * @return bool True if image exists in media library
 */
function hph_media_image_exists($filename) {
    return (bool) hph_get_attachment_id_by_filename($filename);
}

/**
 * Get multiple image sizes for a WordPress attachment
 *
 * @param int $attachment_id WordPress attachment ID
 * @param array $sizes Array of size names to retrieve
 * @return array Array of size data
 */
function hph_get_multiple_image_sizes($attachment_id, $sizes = ['thumbnail', 'medium', 'large', 'full']) {
    if (!wp_attachment_is_image($attachment_id)) {
        return array();
    }

    $result = array();

    foreach ($sizes as $size) {
        $size_data = wp_get_attachment_image_src($attachment_id, $size);
        if ($size_data) {
            $result[$size] = array(
                'url' => $size_data[0],
                'width' => $size_data[1],
                'height' => $size_data[2]
            );
        }
    }

    return $result;
}

/**
 * Convert WordPress image array to template part format
 * Ensures compatibility with existing template parts that expect specific format
 *
 * @param int|array $image Attachment ID or image array
 * @param string $size Default image size
 * @return array Template part compatible image array
 */
function hph_format_image_for_template($image, $size = 'large') {
    // If it's already an array, ensure it has required keys
    if (is_array($image)) {
        $defaults = array(
            'url' => '',
            'alt' => '',
            'caption' => '',
            'title' => '',
            'sizes' => array()
        );
        return wp_parse_args($image, $defaults);
    }

    // If it's an attachment ID, get full data
    if (is_numeric($image)) {
        return hph_get_wordpress_image($image, $size);
    }

    // If it's a string, try to get image data
    $result = hph_get_image_url($image, false, $size);

    // If we get an array back, return it
    if (is_array($result)) {
        return $result;
    }

    // If we get a URL back, format it as an array
    if (is_string($result) && !empty($result)) {
        return array(
            'url' => $result,
            'alt' => '',
            'caption' => '',
            'title' => '',
            'sizes' => array()
        );
    }

    // Return empty array if nothing found
    return array();
}
