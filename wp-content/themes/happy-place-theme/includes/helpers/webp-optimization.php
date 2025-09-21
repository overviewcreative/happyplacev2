<?php
/**
 * WebP Image Optimization System
 *
 * Dynamically converts images to WebP format for frontend serving
 * while maintaining original files for downloads
 *
 * Compatible with Flywheel hosting and CDN integration
 *
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class HPH_WebP_Optimizer {

    private $webp_cache_dir;
    private $webp_cache_url;
    private $conversion_enabled;
    private $browser_supports_webp;

    public function __construct() {
        $this->init();
    }

    /**
     * Initialize WebP optimization system
     */
    private function init() {
        // Set up cache directory in uploads
        $uploads = wp_upload_dir();
        $this->webp_cache_dir = $uploads['basedir'] . '/webp-cache/';
        $this->webp_cache_url = $uploads['baseurl'] . '/webp-cache/';

        // Check if WebP conversion is possible
        $this->conversion_enabled = $this->check_webp_support();

        // Detect browser WebP support
        $this->browser_supports_webp = $this->detect_browser_webp_support();

        // Create cache directory if needed
        $this->maybe_create_cache_directory();

        // Hook into WordPress
        $this->setup_hooks();
    }

    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        // Only proceed if WebP conversion is enabled
        if (!$this->conversion_enabled) {
            return;
        }

        // Hook into image URL generation
        add_filter('wp_get_attachment_image_src', [$this, 'maybe_convert_to_webp'], 10, 4);
        add_filter('wp_calculate_image_srcset', [$this, 'convert_srcset_to_webp'], 10, 5);

        // Clean up old WebP files periodically
        add_action('wp_scheduled_delete', [$this, 'cleanup_old_webp_files']);

        // Add rewrite rules for WebP serving
        add_action('init', [$this, 'add_webp_rewrite_rules']);

        // Handle WebP requests
        add_action('template_redirect', [$this, 'maybe_serve_webp']);
    }

    /**
     * Check if server supports WebP conversion
     */
    private function check_webp_support() {
        // Check if admin has disabled WebP
        if (get_option('hph_disable_webp', false)) {
            return false;
        }

        // Check GD library support
        if (function_exists('imagewebp') && function_exists('imagecreatefromjpeg') && function_exists('imagecreatefrompng')) {
            return true;
        }

        // Check ImageMagick support
        if (class_exists('Imagick')) {
            $imagick = new Imagick();
            $formats = $imagick->queryFormats();
            return in_array('WEBP', $formats);
        }

        return false;
    }

    /**
     * Detect if browser supports WebP
     */
    private function detect_browser_webp_support() {
        // Check Accept header for WebP support
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'image/webp') !== false) {
            return true;
        }

        // Check User-Agent for known WebP-supporting browsers
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Chrome 23+, Opera 12.1+, Android 4.2+, Edge 18+
        if (preg_match('/Chrome\/([0-9]+)/', $user_agent, $matches)) {
            return intval($matches[1]) >= 23;
        }

        if (preg_match('/Opera\/([0-9]+)/', $user_agent, $matches)) {
            return intval($matches[1]) >= 12;
        }

        if (preg_match('/Edge\/([0-9]+)/', $user_agent, $matches)) {
            return intval($matches[1]) >= 18;
        }

        // Firefox 65+ supports WebP
        if (preg_match('/Firefox\/([0-9]+)/', $user_agent, $matches)) {
            return intval($matches[1]) >= 65;
        }

        // Safari 14+ supports WebP
        if (preg_match('/Safari\/([0-9]+)/', $user_agent, $matches)) {
            // This is a simplified check - Safari version detection is complex
            return strpos($user_agent, 'Version/14') !== false || strpos($user_agent, 'Version/15') !== false || strpos($user_agent, 'Version/16') !== false;
        }

        return false;
    }

    /**
     * Create WebP cache directory if it doesn't exist
     */
    private function maybe_create_cache_directory() {
        if (!file_exists($this->webp_cache_dir)) {
            wp_mkdir_p($this->webp_cache_dir);

            // Add .htaccess for direct serving
            $htaccess_content = "# WebP Cache Directory\n";
            $htaccess_content .= "<IfModule mod_expires.c>\n";
            $htaccess_content .= "ExpiresActive On\n";
            $htaccess_content .= "ExpiresByType image/webp \"access plus 1 year\"\n";
            $htaccess_content .= "</IfModule>\n\n";
            $htaccess_content .= "<IfModule mod_headers.c>\n";
            $htaccess_content .= "Header set Cache-Control \"public, max-age=31536000\"\n";
            $htaccess_content .= "</IfModule>\n";

            file_put_contents($this->webp_cache_dir . '.htaccess', $htaccess_content);
        }
    }

    /**
     * Convert image to WebP and return new URL
     */
    public function get_webp_url($image_url, $attachment_id = 0) {
        // Don't convert if browser doesn't support WebP
        if (!$this->browser_supports_webp || !$this->conversion_enabled) {
            return $image_url;
        }

        // Skip if already WebP
        if (strpos($image_url, '.webp') !== false) {
            return $image_url;
        }

        // Generate WebP filename
        $webp_filename = $this->generate_webp_filename($image_url);
        $webp_path = $this->webp_cache_dir . $webp_filename;
        $webp_url = $this->webp_cache_url . $webp_filename;

        // Return existing WebP if available and fresh
        if (file_exists($webp_path) && $this->is_webp_fresh($webp_path, $image_url, $attachment_id)) {
            return $webp_url;
        }

        // Convert to WebP
        if ($this->convert_image_to_webp($image_url, $webp_path, $attachment_id)) {
            return $webp_url;
        }

        // Return original if conversion failed
        return $image_url;
    }

    /**
     * Generate WebP filename from original URL
     */
    private function generate_webp_filename($image_url) {
        $path_info = pathinfo(parse_url($image_url, PHP_URL_PATH));
        $filename = $path_info['filename'];
        $directory = str_replace('/', '-', trim($path_info['dirname'], '/'));

        // Create unique filename including path to avoid conflicts
        return $directory . '-' . $filename . '.webp';
    }

    /**
     * Check if WebP file is fresher than original
     */
    private function is_webp_fresh($webp_path, $image_url, $attachment_id = 0) {
        if (!file_exists($webp_path)) {
            return false;
        }

        $webp_time = filemtime($webp_path);

        // For WordPress attachments, check attachment modified time
        if ($attachment_id > 0) {
            $attachment_time = strtotime(get_post_modified_time('Y-m-d H:i:s', false, $attachment_id));
            return $webp_time > $attachment_time;
        }

        // For external URLs or theme assets, check if WebP is less than 24 hours old
        return (time() - $webp_time) < (24 * 60 * 60);
    }

    /**
     * Convert image to WebP format
     */
    private function convert_image_to_webp($image_url, $webp_path, $attachment_id = 0) {
        // Get original image path
        $original_path = $this->get_local_image_path($image_url, $attachment_id);

        if (!$original_path || !file_exists($original_path)) {
            return false;
        }

        // Determine original image type
        $image_type = wp_check_filetype($original_path)['type'];

        // Create image resource based on type
        $image = null;
        switch ($image_type) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($original_path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($original_path);
                // Preserve transparency for PNG
                imagealphablending($image, false);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($original_path);
                break;
            default:
                return false;
        }

        if (!$image) {
            return false;
        }

        // Convert to WebP with quality setting
        $quality = get_option('hph_webp_quality', 85);
        $success = imagewebp($image, $webp_path, $quality);

        // Clean up memory
        imagedestroy($image);

        return $success;
    }

    /**
     * Get local file path from URL
     */
    private function get_local_image_path($image_url, $attachment_id = 0) {
        // For WordPress attachments, use get_attached_file
        if ($attachment_id > 0) {
            return get_attached_file($attachment_id);
        }

        // For other URLs, convert URL to local path
        $uploads = wp_upload_dir();

        // Check if it's a WordPress upload
        if (strpos($image_url, $uploads['baseurl']) === 0) {
            return str_replace($uploads['baseurl'], $uploads['basedir'], $image_url);
        }

        // Check if it's a theme asset
        $theme_url = get_template_directory_uri();
        if (strpos($image_url, $theme_url) === 0) {
            return str_replace($theme_url, get_template_directory(), $image_url);
        }

        return false;
    }

    /**
     * Hook to convert WordPress image sources to WebP
     */
    public function maybe_convert_to_webp($image, $attachment_id, $size, $icon) {
        if (!$image || !$this->browser_supports_webp) {
            return $image;
        }

        $webp_url = $this->get_webp_url($image[0], $attachment_id);

        if ($webp_url !== $image[0]) {
            $image[0] = $webp_url;
        }

        return $image;
    }

    /**
     * Convert srcset URLs to WebP
     */
    public function convert_srcset_to_webp($sources, $size_array, $image_src, $image_meta, $attachment_id) {
        if (!$this->browser_supports_webp || empty($sources)) {
            return $sources;
        }

        foreach ($sources as $width => $source) {
            $webp_url = $this->get_webp_url($source['url'], $attachment_id);
            if ($webp_url !== $source['url']) {
                $sources[$width]['url'] = $webp_url;
            }
        }

        return $sources;
    }

    /**
     * Add rewrite rules for WebP serving
     */
    public function add_webp_rewrite_rules() {
        // Add rule to serve WebP files directly if they exist
        add_rewrite_rule(
            '^wp-content/uploads/webp-cache/(.+)\.webp$',
            'index.php?webp_file=$matches[1]',
            'top'
        );

        add_rewrite_tag('%webp_file%', '([^&]+)');
    }

    /**
     * Serve WebP files with proper headers
     */
    public function maybe_serve_webp() {
        $webp_file = get_query_var('webp_file');

        if ($webp_file) {
            $webp_path = $this->webp_cache_dir . $webp_file . '.webp';

            if (file_exists($webp_path)) {
                // Set proper headers
                header('Content-Type: image/webp');
                header('Cache-Control: public, max-age=31536000');
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

                // Serve the file
                readfile($webp_path);
                exit;
            }
        }
    }

    /**
     * Clean up old WebP files
     */
    public function cleanup_old_webp_files() {
        if (!is_dir($this->webp_cache_dir)) {
            return;
        }

        $files = glob($this->webp_cache_dir . '*.webp');
        $max_age = 30 * 24 * 60 * 60; // 30 days

        foreach ($files as $file) {
            if (time() - filemtime($file) > $max_age) {
                unlink($file);
            }
        }
    }

    /**
     * Get conversion statistics
     */
    public function get_stats() {
        $stats = array(
            'conversion_enabled' => $this->conversion_enabled,
            'browser_supports_webp' => $this->browser_supports_webp,
            'cache_directory' => $this->webp_cache_dir,
            'cached_files' => 0,
            'cache_size' => 0
        );

        if (is_dir($this->webp_cache_dir)) {
            $files = glob($this->webp_cache_dir . '*.webp');
            $stats['cached_files'] = count($files);

            foreach ($files as $file) {
                $stats['cache_size'] += filesize($file);
            }
        }

        return $stats;
    }

    /**
     * Clear WebP cache
     */
    public function clear_cache() {
        if (is_dir($this->webp_cache_dir)) {
            $files = glob($this->webp_cache_dir . '*.webp');
            foreach ($files as $file) {
                unlink($file);
            }
        }

        return true;
    }
}

// Initialize the WebP optimizer
global $hph_webp_optimizer;
$hph_webp_optimizer = new HPH_WebP_Optimizer();

/**
 * Public function to get WebP URL
 */
function hph_get_webp_url($image_url, $attachment_id = 0) {
    global $hph_webp_optimizer;
    return $hph_webp_optimizer->get_webp_url($image_url, $attachment_id);
}

/**
 * Check if browser supports WebP
 */
function hph_browser_supports_webp() {
    global $hph_webp_optimizer;
    return $hph_webp_optimizer->browser_supports_webp ?? false;
}

/**
 * Get WebP optimization stats
 */
function hph_get_webp_stats() {
    global $hph_webp_optimizer;
    return $hph_webp_optimizer->get_stats();
}

/**
 * Clear WebP cache
 */
function hph_clear_webp_cache() {
    global $hph_webp_optimizer;
    return $hph_webp_optimizer->clear_cache();
}