<?php
/**
 * Error Handler
 * 
 * Comprehensive error handling with recovery mechanisms and logging
 *
 * @package HappyPlace\Core
 * @version 4.0.0
 */

namespace HappyPlace\Core;

use Exception;
use Throwable;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Error Handler Class
 * 
 * @since 4.0.0
 */
class ErrorHandler {
    
    /**
     * Container instance
     * 
     * @var Container
     */
    private Container $container;
    
    /**
     * Error log file
     * 
     * @var string
     */
    private string $log_file;
    
    /**
     * Error levels to handle
     * 
     * @var array
     */
    private array $error_levels = [
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
    ];
    
    /**
     * Critical errors that should halt execution
     * 
     * @var array
     */
    private array $critical_errors = [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
    ];
    
    /**
     * Constructor
     * 
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
        $this->log_file = WP_CONTENT_DIR . '/hp-errors.log';
    }
    
    /**
     * Handle PHP errors
     * 
     * @param int $errno Error level
     * @param string $errstr Error message
     * @param string $errfile Error file
     * @param int $errline Error line
     * @return bool
     */
    public function handle_error(
        int $errno, 
        string $errstr, 
        string $errfile, 
        int $errline
    ): bool {
        // Check if error reporting is disabled
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        // Format error message
        $error_type = $this->error_levels[$errno] ?? 'Unknown Error';
        $message = "[{$error_type}] {$errstr} in {$errfile} on line {$errline}";
        
        // Log error
        $this->log_error($message, $errno);
        
        // Handle based on severity
        if (in_array($errno, $this->critical_errors)) {
            $this->handle_critical_error($message);
        } else {
            $this->handle_non_critical_error($message, $errno);
        }
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    /**
     * Handle exceptions
     * 
     * @param Throwable $exception
     * @return void
     */
    public function handle_exception(Throwable $exception): void {
        $message = sprintf(
            "[Exception] %s: %s in %s on line %d\nStack trace:\n%s",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        // Log exception
        $this->log_error($message, E_ERROR);
        
        // Handle based on type
        if ($exception instanceof CriticalException) {
            $this->handle_critical_error($exception->getMessage(), $exception);
        } else {
            $this->handle_non_critical_error($message, E_ERROR);
        }
    }
    
    /**
     * Handle shutdown errors
     * 
     * @return void
     */
    public function handle_shutdown(): void {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], $this->critical_errors)) {
            $message = sprintf(
                "[Shutdown] %s: %s in %s on line %d",
                $this->error_levels[$error['type']] ?? 'Unknown Error',
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            $this->log_error($message, $error['type']);
            $this->handle_critical_error($message);
        }
    }
    
    /**
     * Handle critical errors
     * 
     * @param string $message Error message
     * @param Throwable|null $exception Original exception
     * @return void
     */
    public function handle_critical_error(string $message, ?Throwable $exception = null): void {
        // Log critical error
        hp_log("CRITICAL: {$message}", 'critical', 'ERROR_HANDLER');
        
        // Send admin notification if configured
        $this->send_admin_notification($message, 'critical');
        
        // Show user-friendly error
        if (!defined('DOING_AJAX') && !wp_doing_ajax()) {
            $this->show_error_page($message);
        } else {
            // For AJAX requests, send JSON error
            wp_send_json_error([
                'message' => __('A critical error occurred. Please try again later.', 'happy-place'),
                'code' => 'critical_error'
            ], 500);
        }
        
        // Deactivate plugin if too many critical errors
        $this->check_error_threshold();
        
        // Stop execution for critical errors
        die();
    }
    
    /**
     * Handle non-critical errors
     * 
     * @param string $message Error message
     * @param int $errno Error level
     * @return void
     */
    private function handle_non_critical_error(string $message, int $errno): void {
        // Log error
        hp_log($message, 'error', 'ERROR_HANDLER');
        
        // Show admin notice if in admin area
        if (is_admin() && current_user_can('manage_options')) {
            add_action('admin_notices', function() use ($message, $errno) {
                $class = ($errno === E_WARNING) ? 'notice-warning' : 'notice-error';
                ?>
                <div class="notice <?php echo esc_attr($class); ?> is-dismissible">
                    <p><strong><?php _e('Happy Place Plugin Error:', 'happy-place'); ?></strong></p>
                    <p><?php echo esc_html($message); ?></p>
                </div>
                <?php
            });
        }
        
        // Track error for monitoring
        $this->track_error($message, $errno);
    }
    
    /**
     * Log error to file
     * 
     * @param string $message Error message
     * @param int $level Error level
     * @return void
     */
    private function log_error(string $message, int $level): void {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] {$message}\n";
        
        // Write to custom log file
        @file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        // Also use WordPress error log if debug is enabled
        if (WP_DEBUG) {
            error_log("HappyPlace: {$message}");
        }
    }
    
    /**
     * Send admin notification for critical errors
     * 
     * @param string $message Error message
     * @param string $severity Severity level
     * @return void
     */
    private function send_admin_notification(string $message, string $severity): void {
        // Check if notifications are enabled
        $notify = get_option('hp_error_notifications', true);
        
        if (!$notify) {
            return;
        }
        
        // Get admin email
        $admin_email = get_option('admin_email');
        
        if (!$admin_email) {
            return;
        }
        
        // Throttle notifications (max 1 per hour)
        $last_sent = get_transient('hp_last_error_notification');
        
        if ($last_sent && (time() - $last_sent) < 3600) {
            return;
        }
        
        // Send email
        $subject = sprintf(
            '[%s] Critical Error in Happy Place Plugin',
            get_bloginfo('name')
        );
        
        $body = sprintf(
            "A critical error occurred in the Happy Place Plugin:\n\n%s\n\nTime: %s\nSite: %s\n\nPlease check the error logs for more details.",
            $message,
            date('Y-m-d H:i:s'),
            home_url()
        );
        
        wp_mail($admin_email, $subject, $body);
        
        // Set throttle
        set_transient('hp_last_error_notification', time(), 3600);
    }
    
    /**
     * Show error page
     * 
     * @param string $message Error message
     * @return void
     */
    private function show_error_page(string $message): void {
        // Clear any output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set HTTP status
        status_header(500);
        
        // Show error page
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?php _e('Error', 'happy-place'); ?></title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: #f0f0f0;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                }
                .error-container {
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    padding: 40px;
                    max-width: 600px;
                    text-align: center;
                }
                h1 {
                    color: #dc3545;
                    margin-bottom: 20px;
                }
                p {
                    line-height: 1.6;
                    margin: 20px 0;
                }
                .error-code {
                    background: #f8f9fa;
                    border-radius: 4px;
                    padding: 10px;
                    font-family: monospace;
                    font-size: 12px;
                    margin-top: 20px;
                }
                .back-link {
                    display: inline-block;
                    margin-top: 20px;
                    color: #007cba;
                    text-decoration: none;
                }
                .back-link:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1><?php _e('Something went wrong', 'happy-place'); ?></h1>
                <p><?php _e('We apologize for the inconvenience. An error has occurred and we\'re working to fix it.', 'happy-place'); ?></p>
                
                <?php if (WP_DEBUG && current_user_can('manage_options')): ?>
                    <div class="error-code">
                        <?php echo esc_html($message); ?>
                    </div>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(home_url()); ?>" class="back-link">
                    <?php _e('← Back to Homepage', 'happy-place'); ?>
                </a>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Check error threshold and deactivate if exceeded
     * 
     * @return void
     */
    private function check_error_threshold(): void {
        $threshold = 10; // Max errors in time window
        $window = 300; // 5 minutes
        
        // Get error count
        $errors = get_transient('hp_critical_error_count') ?: [];
        $now = time();
        
        // Filter old errors
        $errors = array_filter($errors, function($time) use ($now, $window) {
            return ($now - $time) < $window;
        });
        
        // Add current error
        $errors[] = $now;
        
        // Check threshold
        if (count($errors) >= $threshold) {
            // Deactivate plugin
            deactivate_plugins(plugin_basename(HP_PLUGIN_FILE));
            
            // Clear errors
            delete_transient('hp_critical_error_count');
            
            // Add admin notice
            add_option('hp_deactivated_due_to_errors', true);
            
            // Log deactivation
            hp_log('Plugin deactivated due to excessive errors', 'critical', 'ERROR_HANDLER');
        } else {
            // Update error count
            set_transient('hp_critical_error_count', $errors, $window);
        }
    }
    
    /**
     * Track error for monitoring
     * 
     * @param string $message Error message
     * @param int $level Error level
     * @return void
     */
    private function track_error(string $message, int $level): void {
        // Store in database for analytics
        global $wpdb;
        
        $table = $wpdb->prefix . 'hp_error_log';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table) {
            $wpdb->insert(
                $table,
                [
                    'error_level' => $level,
                    'error_message' => $message,
                    'error_time' => current_time('mysql'),
                    'user_id' => get_current_user_id(),
                    'url' => $_SERVER['REQUEST_URI'] ?? '',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                ],
                ['%d', '%s', '%s', '%d', '%s', '%s']
            );
        }
    }
}

/**
 * Critical Exception Class
 */
class CriticalException extends Exception {}