<?php
/**
 * Utilities Service
 * 
 * Provides common utility functions and helpers
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

class HPH_Utils implements HPH_Service {
    
    /**
     * Service instance
     * @var HPH_Utils
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     * @return HPH_Utils
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Private constructor for singleton
    }
    
    /**
     * Initialize the service
     */
    public function init() {
        // Utils service doesn't need hooks
    }
    
    /**
     * Format price for display
     * 
     * @param mixed $price Price value
     * @param bool $include_currency Include currency symbol
     * @return string Formatted price
     */
    public static function format_price($price, $include_currency = true) {
        if (empty($price) || !is_numeric($price)) {
            return '';
        }
        
        $formatted = number_format($price, 0);
        
        if ($include_currency) {
            $formatted = '$' . $formatted;
        }
        
        return $formatted;
    }
    
    /**
     * Truncate text with ellipsis
     * 
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @param string $suffix Suffix to append
     * @return string Truncated text
     */
    public static function truncate_text($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Get excerpt with custom length
     * 
     * @param string $content Content to excerpt
     * @param int $length Excerpt length
     * @param string $more More text
     * @return string Excerpt
     */
    public static function get_excerpt($content = '', $length = 55, $more = '...') {
        if (empty($content)) {
            $content = get_the_content();
        }
        
        $content = strip_shortcodes($content);
        $content = wp_strip_all_tags($content);
        $words = explode(' ', $content);
        
        if (count($words) > $length) {
            $words = array_slice($words, 0, $length);
            $content = implode(' ', $words) . $more;
        }
        
        return $content;
    }
    
    /**
     * Sanitize HTML classes
     * 
     * @param string|array $classes Classes to sanitize
     * @return string Sanitized classes
     */
    public static function sanitize_html_classes($classes) {
        if (is_array($classes)) {
            $classes = implode(' ', $classes);
        }
        
        return sanitize_html_class($classes);
    }
    
    /**
     * Get responsive image HTML
     * 
     * @param int $attachment_id Image attachment ID
     * @param string $size Image size
     * @param array $attr Additional attributes
     * @return string Image HTML
     */
    public static function get_responsive_image($attachment_id, $size = 'full', $attr = array()) {
        if (!$attachment_id) {
            return '';
        }
        
        $default_attr = array(
            'loading' => 'lazy',
            'decoding' => 'async',
        );
        
        $attr = array_merge($default_attr, $attr);
        
        return wp_get_attachment_image($attachment_id, $size, false, $attr);
    }
    
    /**
     * Check if we're on a listing page
     * 
     * @return bool
     */
    public static function is_listing_page() {
        return is_singular('listing') || is_post_type_archive('listing');
    }
    
    /**
     * Get the service ID
     * @return string
     */
    public function get_service_id() {
        return 'utils';
    }
    
    /**
     * Check if service is active
     * @return bool
     */
    public function is_active() {
        return true;
    }
    
    /**
     * Get service dependencies
     * @return array
     */
    public function get_dependencies() {
        return array();
    }
}
