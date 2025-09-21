<?php
/**
 * Template Part Performance Improvements
 * Recommended patterns for optimizing template parts
 */

// 1. DATA CACHING PATTERN
class HPH_Template_Cache {
    private static $cache = [];
    
    public static function get_listing_data($listing_id) {
        if (!isset(self::$cache['listing'][$listing_id])) {
            self::$cache['listing'][$listing_id] = hpt_get_listing($listing_id);
        }
        return self::$cache['listing'][$listing_id];
    }
}

// 2. STANDARDIZED DATA ACCESS PATTERN  
function hph_get_template_data($type, $id, $fields = []) {
    static $cache = [];
    $cache_key = $type . '_' . $id;
    
    if (!isset($cache[$cache_key])) {
        try {
            switch($type) {
                case 'listing':
                    $cache[$cache_key] = function_exists('hpt_get_listing') 
                        ? hpt_get_listing($id) 
                        : hph_fallback_listing_data($id);
                    break;
                case 'agent':
                    $cache[$cache_key] = function_exists('hpt_get_agent')
                        ? hpt_get_agent($id)
                        : hph_fallback_agent_data($id);
                    break;
            }
        } catch (Exception $e) {
            error_log("Template data error: {$e->getMessage()}");
            $cache[$cache_key] = null;
        }
    }
    
    return $cache[$cache_key];
}

// 3. ENHANCED LAZY LOADING IMAGE HELPER
function hph_lazy_image($src, $alt = '', $classes = '', $sizes = 'medium', $context = 'default') {
    if (empty($src)) return '';
    
    // Determine context-specific classes
    $context_classes = array();
    switch ($context) {
        case 'hero':
            $context_classes[] = 'hph-hero-lazy';
            break;
        case 'gallery':
            $context_classes[] = 'hph-gallery-lazy';
            break;
        case 'card':
            $context_classes[] = 'hph-card-image-lazy';
            break;
        default:
            $context_classes[] = 'hph-lazy-enhanced';
            break;
    }
    
    // Combine all classes
    $all_classes = array_merge(array('hph-lazy'), $context_classes);
    if (!empty($classes)) {
        $all_classes[] = $classes;
    }
    
    // Generate low-quality placeholder for blur effect
    $placeholder = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 400 300\'%3E%3Crect width=\'100%25\' height=\'100%25\' fill=\'%23f3f4f6\'/%3E%3C/svg%3E';
    
    return sprintf(
        '<img src="%s" 
              data-src="%s" 
              alt="%s" 
              class="%s" 
              loading="lazy"
              sizes="%s"
              data-context="%s">',
        $placeholder,
        esc_url($src),
        esc_attr($alt),
        esc_attr(implode(' ', $all_classes)),
        esc_attr($sizes),
        esc_attr($context)
    );
}

// 3.1. RESPONSIVE LAZY IMAGE HELPER
function hph_responsive_lazy_image($image_array, $alt = '', $classes = '', $context = 'default') {
    // Handle both old array format and new hph_get_image_url format
    if (empty($image_array)) return '';

    // If it's a string, try to process it as a path/URL
    if (is_string($image_array)) {
        $processed = hph_get_image_url($image_array);
        if (is_array($processed)) {
            $image_array = $processed;
        } else {
            // Create basic array format for string URL
            $image_array = array(
                'url' => $processed,
                'alt' => $alt
            );
        }
    }

    // Ensure we have an array at this point
    if (!is_array($image_array)) return '';

    $src = $image_array['url'] ?? '';
    if (empty($src)) return '';

    // Use alt text from image array if available, fallback to parameter
    $image_alt = $image_array['alt'] ?? $alt;

    $srcset = '';
    $sizes = 'medium';

    // Generate responsive srcset if available
    if (isset($image_array['sizes']) && is_array($image_array['sizes'])) {
        $srcset_parts = array();
        foreach ($image_array['sizes'] as $size => $data) {
            $size_url = '';
            $size_width = 0;

            // Handle both formats: array with url/width or direct URL string
            if (is_array($data) && !empty($data['url'])) {
                $size_url = $data['url'];
                $size_width = $data['width'] ?? 0;
            } elseif (is_string($data) && !empty($data)) {
                $size_url = $data;
                // Try to get width from size-width key (ACF format)
                $size_width = $image_array['sizes'][$size . '-width'] ?? 0;
            }

            if (!empty($size_url) && $size_width > 0) {
                $srcset_parts[] = esc_url($size_url) . ' ' . $size_width . 'w';
            }
        }
        if (!empty($srcset_parts)) {
            $srcset = implode(', ', $srcset_parts);
        }
    }

    // Enhanced responsive sizes based on context
    switch ($context) {
        case 'hero':
            $sizes = '100vw';
            break;
        case 'gallery':
            $sizes = '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw';
            break;
        case 'card':
            $sizes = '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 400px';
            break;
        default:
            $sizes = '(max-width: 768px) 100vw, 800px';
            break;
    }

    // Create base image HTML with proper parameters
    $lazy_args = array(
        'alt' => $image_alt,
        'class' => $classes,
        'loading_type' => $context === 'hero' ? 'hero' : 'enhanced'
    );

    $image_html = hph_lazy_image($src, $lazy_args);

    // Add srcset if available
    if (!empty($srcset)) {
        $image_html = str_replace('data-src="', 'data-srcset="' . esc_attr($srcset) . '" data-src="', $image_html);
    }

    return $image_html;
}

// 4. ERROR BOUNDARY PATTERN
function hph_safe_template_part($template, $args = [], $fallback = '') {
    try {
        ob_start();
        get_template_part($template, null, $args);
        $output = ob_get_clean();
        return !empty($output) ? $output : $fallback;
    } catch (Exception $e) {
        error_log("Template part error in {$template}: {$e->getMessage()}");
        return $fallback;
    }
}
?>