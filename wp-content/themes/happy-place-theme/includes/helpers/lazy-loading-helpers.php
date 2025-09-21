<?php
/**
 * Lazy Loading Helper Functions
 * 
 * Utility functions for implementing enhanced lazy loading
 * with smooth fade-in transitions
 * 
 * @package HappyPlaceTheme
 * @version 3.2.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced lazy image output with fade-in transitions
 * 
 * @param string|int $image Image URL, attachment ID, or ACF field value
 * @param array $args Arguments for image output
 * @return string HTML img tag with lazy loading
 */
function hph_lazy_image($image, $args = []) {
    $defaults = [
        'size' => 'large',
        'alt' => '',
        'class' => '',
        'loading_type' => 'enhanced', // enhanced, blur, gallery, card, hero
        'placeholder' => true,
        'fallback' => '',
        'blur_src' => '',
        'eager_threshold' => 2, // Load first X images eagerly
        'data_attrs' => []
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // Handle different image input types
    $image_data = hph_process_image_input($image, $args['size']);
    
    if (!$image_data) {
        return hph_get_fallback_image($args['fallback'], $args['alt'], $args['class']);
    }
    
    // Determine if image should be loaded eagerly
    static $image_count = 0;
    $image_count++;
    $is_eager = $image_count <= $args['eager_threshold'];
    
    // Build image attributes
    $attributes = hph_build_lazy_image_attributes($image_data, $args, $is_eager);
    
    return sprintf('<img %s>', implode(' ', $attributes));
}

/**
 * Add Fastly optimization parameters to image URLs
 *
 * @param string $url Image URL
 * @param string $size WordPress image size
 * @return string Optimized URL with Fastly parameters
 */
function hph_add_fastly_optimization($url, $size = 'large') {
    // Only process URLs from this site
    if (empty($url) || !is_string($url)) {
        return $url;
    }

    // Skip if already has optimization parameters
    if (strpos($url, 'auto=') !== false || strpos($url, 'w=') !== false) {
        return $url;
    }

    // Base optimization parameters
    $params = [
        'auto' => 'webp,compress',
        'q' => '85', // 85% quality for good balance
        'fit' => 'crop'
    ];

    // Size-specific dimensions for responsive images
    switch($size) {
        case 'thumbnail':
            $params['w'] = 300;
            $params['h'] = 300;
            break;
        case 'medium':
            $params['w'] = 600;
            $params['h'] = 400;
            break;
        case 'medium_large':
            $params['w'] = 768;
            $params['h'] = 512;
            break;
        case 'large':
            $params['w'] = 1200;
            $params['h'] = 800;
            break;
        case 'full':
        default:
            // For hero images, limit max width but maintain aspect ratio
            $params['w'] = 1920;
            $params['h'] = 1080;
            $params['fit'] = 'max'; // Don't crop full size images
            break;
    }

    return add_query_arg($params, $url);
}

/**
 * Process different types of image input
 *
 * @param mixed $image Image input (URL, ID, ACF field, or HPH image array)
 * @param string $size Image size
 * @return array|false Image data or false on failure
 */
function hph_process_image_input($image, $size = 'large') {
    // Handle attachment ID
    if (is_numeric($image)) {
        $image_data = wp_get_attachment_image_src($image, $size);
        if ($image_data) {
            return [
                'src' => hph_add_fastly_optimization($image_data[0], $size),
                'width' => $image_data[1],
                'height' => $image_data[2],
                'alt' => get_post_meta($image, '_wp_attachment_image_alt', true) ?: '',
                'id' => $image
            ];
        }
    }

    // Handle HPH image array (from our updated hph_get_image_url function)
    if (is_array($image) && isset($image['url'])) {
        // Determine the best URL to use based on size
        $src = $image['url'] ?? ''; // Default to main URL
        $width = $image['width'] ?? 0;
        $height = $image['height'] ?? 0;

        // Try to get specific size if available
        if (isset($image['sizes'][$size]['url'])) {
            $src = $image['sizes'][$size]['url'];
            $width = $image['sizes'][$size]['width'] ?? $width;
            $height = $image['sizes'][$size]['height'] ?? $height;
        } elseif (isset($image['sizes'][$size])) {
            // Handle ACF format with size suffix
            $src = $image['sizes'][$size];
            $width = $image['sizes'][$size . '-width'] ?? $width;
            $height = $image['sizes'][$size . '-height'] ?? $height;
        }

        return [
            'src' => hph_add_fastly_optimization($src, $size),
            'width' => $width,
            'height' => $height,
            'alt' => $image['alt'] ?? $image['title'] ?? '',
            'id' => $image['ID'] ?? $image['id'] ?? 0
        ];
    }

    // Handle direct URL
    if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
        return [
            'src' => hph_add_fastly_optimization($image, $size),
            'width' => 0,
            'height' => 0,
            'alt' => '',
            'id' => 0
        ];
    }

    // Handle file path (try to process through hph_get_image_url)
    if (is_string($image) && !empty($image)) {
        $processed = hph_get_image_url($image);

        // If we get an array back, check if it's already in the right format
        if (is_array($processed)) {
            // If it has the expected structure, return it directly (avoid recursive processing)
            if (isset($processed['url'])) {
                return [
                    'src' => hph_add_fastly_optimization($processed['url'], $size),
                    'width' => $processed['width'] ?? 0,
                    'height' => $processed['height'] ?? 0,
                    'alt' => $processed['alt'] ?? $processed['title'] ?? '',
                    'id' => $processed['ID'] ?? $processed['id'] ?? 0
                ];
            }
            // If it's a different array format, try to process it
            return hph_process_image_input($processed, $size);
        }

        // If we get a URL back, process it as URL
        if (is_string($processed) && filter_var($processed, FILTER_VALIDATE_URL)) {
            return [
                'src' => hph_add_fastly_optimization($processed, $size),
                'width' => 0,
                'height' => 0,
                'alt' => '',
                'id' => 0
            ];
        }
    }

    return false;
}

/**
 * Build lazy loading image attributes
 * 
 * @param array $image_data Image data
 * @param array $args Arguments
 * @param bool $is_eager Whether to load eagerly
 * @return array Attributes array
 */
function hph_build_lazy_image_attributes($image_data, $args, $is_eager) {
    $attributes = [];

    // Ensure src is a string URL, not an array
    $src_url = '';
    if (is_array($image_data['src'])) {
        // If src is an array, try to extract the URL
        $src_url = $image_data['src']['url'] ?? '';
        if (empty($src_url) && isset($image_data['src'][0])) {
            $src_url = $image_data['src'][0]; // Handle WordPress image array format
        }
    } else {
        $src_url = $image_data['src'] ?? '';
    }

    // Fallback to empty string if we still don't have a valid URL
    if (!is_string($src_url)) {
        $src_url = '';
    }

    // Basic attributes
    if ($image_data['alt'] || $args['alt']) {
        $attributes[] = 'alt="' . esc_attr($image_data['alt'] ?: $args['alt']) . '"';
    }

    if ($image_data['width']) {
        $attributes[] = 'width="' . esc_attr($image_data['width']) . '"';
    }

    if ($image_data['height']) {
        $attributes[] = 'height="' . esc_attr($image_data['height']) . '"';
    }

    // Build CSS classes
    $classes = ['hph-image'];
    if ($args['class']) {
        $classes[] = $args['class'];
    }

    // Lazy loading implementation
    if ($is_eager) {
        // Eager loading for above-fold images
        $attributes[] = 'src="' . esc_url($src_url) . '"';
        $attributes[] = 'loading="eager"';
        $classes[] = 'hph-loaded';
    } else {
        // Lazy loading
        $attributes[] = 'data-src="' . esc_url($src_url) . '"';
        $attributes[] = 'loading="lazy"';
        
        // Add loading type class
        switch ($args['loading_type']) {
            case 'blur':
                $classes[] = 'hph-lazy-blur';
                if ($args['blur_src']) {
                    $attributes[] = 'src="' . esc_url($args['blur_src']) . '"';
                    $attributes[] = 'data-blur-src="' . esc_url($args['blur_src']) . '"';
                }
                break;
                
            case 'gallery':
                $classes[] = 'hph-gallery-lazy';
                break;
                
            case 'card':
                $classes[] = 'hph-card-image-lazy';
                break;
                
            case 'hero':
                $classes[] = 'hph-hero-lazy';
                break;
                
            case 'enhanced':
            default:
                $classes[] = 'hph-lazy-enhanced';
                break;
        }
        
        // Add placeholder if enabled
        if ($args['placeholder']) {
            $placeholder_src = hph_generate_placeholder($image_data['width'], $image_data['height']);
            if (!$args['blur_src']) {
                $attributes[] = 'src="' . $placeholder_src . '"';
            }
        }
    }
    
    // Add fallback image
    if ($args['fallback']) {
        $attributes[] = 'data-fallback="' . esc_url($args['fallback']) . '"';
    }
    
    // Add custom data attributes
    foreach ($args['data_attrs'] as $key => $value) {
        $attributes[] = 'data-' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }
    
    // Add classes
    $attributes[] = 'class="' . esc_attr(implode(' ', $classes)) . '"';
    
    return $attributes;
}

/**
 * Generate placeholder image data URL
 * 
 * @param int $width Image width
 * @param int $height Image height
 * @param string $color Placeholder color
 * @return string Data URL
 */
function hph_generate_placeholder($width = 400, $height = 300, $color = '#f0f0f0') {
    // Use a simple SVG data URL for minimal payload with better encoding
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='{$width}' height='{$height}' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='{$color}'/></svg>";
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/**
 * Get fallback image HTML
 * 
 * @param string $fallback Fallback image URL
 * @param string $alt Alt text
 * @param string $class CSS class
 * @return string HTML
 */
function hph_get_fallback_image($fallback, $alt, $class) {
    if (!$fallback) {
        // Default fallback
        return '<div class="hph-image-error ' . esc_attr($class) . '" role="img" aria-label="' . esc_attr($alt ?: 'Image not available') . '"></div>';
    }
    
    return '<img src="' . esc_url($fallback) . '" alt="' . esc_attr($alt) . '" class="hph-image hph-fallback ' . esc_attr($class) . '">';
}

/**
 * Output responsive lazy image with multiple sizes
 * 
 * @param string|int $image Image source
 * @param array $args Arguments including responsive breakpoints
 * @return string HTML picture element or img tag
 */
function hph_responsive_lazy_image($image, $args = []) {
    $defaults = [
        'sizes' => [
            'mobile' => 'medium',
            'tablet' => 'large', 
            'desktop' => 'full'
        ],
        'breakpoints' => [
            'mobile' => 480,
            'tablet' => 768,
            'desktop' => 1024
        ],
        'loading_type' => 'enhanced',
        'alt' => '',
        'class' => ''
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // For now, use single image with srcset (can be enhanced later)
    return hph_lazy_image($image, $args);
}

/**
 * Lazy load background image via CSS custom property
 * 
 * @param string|int $image Image source
 * @param array $args Arguments
 * @return string CSS style attribute
 */
function hph_lazy_background($image, $args = []) {
    $defaults = [
        'size' => 'large',
        'position' => 'center center',
        'repeat' => 'no-repeat',
        'size_css' => 'cover'
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $image_data = hph_process_image_input($image, $args['size']);
    
    if (!$image_data) {
        return '';
    }
    
    // Use CSS custom properties for lazy background loading
    $optimized_src = hph_add_fastly_optimization($image_data['src'], $args['size']);
    $styles = [
        '--hph-bg-image: url(' . esc_url($optimized_src) . ')',
        'background-position: ' . $args['position'],
        'background-repeat: ' . $args['repeat'],
        'background-size: ' . $args['size_css']
    ];
    
    return 'style="' . implode('; ', $styles) . '"';
}

/**
 * Get lazy loading statistics (for debugging)
 * 
 * @return array Statistics
 */
function hph_get_lazy_loading_stats() {
    static $stats = ['total' => 0, 'eager' => 0, 'lazy' => 0];
    return $stats;
}

/**
 * Convert existing img tags to lazy loading
 * 
 * @param string $content HTML content
 * @return string Modified content
 */
function hph_convert_images_to_lazy($content) {
    // Skip if admin or feed
    if (is_admin() || is_feed()) {
        return $content;
    }
    
    // Pattern to match img tags
    $pattern = '/<img([^>]+?)src=[\'"]([^>\'"]+)[\'"]([^>]*?)>/i';
    
    return preg_replace_callback($pattern, function($matches) {
        $before_src = $matches[1];
        $src = $matches[2];
        $after_src = $matches[3];
        
        // Skip if already has data-src or loading attribute
        if (strpos($matches[0], 'data-src') !== false || strpos($matches[0], 'loading') !== false) {
            return $matches[0];
        }
        
        // Skip first few images (likely above fold)
        static $img_count = 0;
        $img_count++;
        
        if ($img_count <= 2) {
            return str_replace('<img', '<img loading="eager"', $matches[0]);
        }
        
        // Determine loading type based on context
        $loading_class = 'hph-lazy-enhanced';
        if (strpos($matches[0], 'gallery') !== false) {
            $loading_class = 'hph-gallery-lazy';
        } elseif (strpos($matches[0], 'card') !== false || strpos($matches[0], 'listing') !== false) {
            $loading_class = 'hph-card-image-lazy';
        } elseif (strpos($matches[0], 'hero') !== false) {
            $loading_class = 'hph-hero-lazy';
        }
        
        // Add class to existing classes
        $new_img = $matches[0];
        if (strpos($new_img, 'class=') !== false) {
            $new_img = preg_replace('/class=[\'"]([^\'"]*)[\'"]/', 'class="$1 ' . $loading_class . '"', $new_img);
        } else {
            $new_img = str_replace('<img', '<img class="' . $loading_class . '"', $new_img);
        }
        
        // Convert to lazy loading
        $new_img = str_replace('src="' . $src . '"', 'data-src="' . $src . '" src="' . hph_generate_placeholder() . '" loading="lazy"', $new_img);
        
        return $new_img;
        
    }, $content);
}

// Auto-convert images in content if enabled
if (get_theme_mod('hph_auto_lazy_loading', true)) {
    add_filter('the_content', 'hph_convert_images_to_lazy', 20);
    add_filter('widget_text', 'hph_convert_images_to_lazy', 20);
    add_filter('post_thumbnail_html', 'hph_convert_images_to_lazy', 20);
    add_filter('wp_get_attachment_image', 'hph_convert_images_to_lazy', 20);
    
    // Catch template output buffer
    add_action('wp_head', 'hph_start_lazy_loading_buffer', 1);
    add_action('wp_footer', 'hph_end_lazy_loading_buffer', 999);
}

/**
 * Start output buffer to catch all template images
 */
function hph_start_lazy_loading_buffer() {
    if (!is_admin() && !is_feed() && !wp_doing_ajax()) {
        ob_start();
    }
}

/**
 * End output buffer and process all images
 */
function hph_end_lazy_loading_buffer() {
    if (!is_admin() && !is_feed() && !wp_doing_ajax()) {
        $content = ob_get_contents();
        if ($content) {
            ob_end_clean();
            echo hph_convert_images_to_lazy($content);
        }
    }
}

/**
 * Add lazy loading support to WordPress images
 */
function hph_add_lazy_loading_to_wp_images($attr, $attachment, $size) {
    // Skip if in admin
    if (is_admin()) {
        return $attr;
    }
    
    // Add lazy loading class
    $attr['class'] = isset($attr['class']) ? $attr['class'] . ' hph-lazy-enhanced' : 'hph-lazy-enhanced';
    
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'hph_add_lazy_loading_to_wp_images', 10, 3);
