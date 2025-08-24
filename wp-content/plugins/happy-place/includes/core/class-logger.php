<?php
/**
 * Logger Class
 * 
 * Advanced logging system with multiple handlers and log levels
 *
 * @package HappyPlace\Core
 * @version 4.0.0
 */

namespace HappyPlace\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Logger Class
 * 
 * @since 4.0.0
 */
class Logger {
    
    /**
     * Log levels
     */
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';
    
    /**
     * Log level priorities
     * 
     * @var array
     */
    private array $levels = [
        self::EMERGENCY => 800,
        self::ALERT => 700,
        self::CRITICAL => 600,
        self::ERROR => 500,
        self::WARNING => 400,
        self::NOTICE => 300,
        self::INFO => 200,
        self::DEBUG => 100,
    ];
    
    /**
     * Minimum log level
     * 
     * @var string
     */
    private string $min_level;
    
    /**
     * Log directory
     * 
     * @var string
     */
    private string $log_dir;
    
    /**
     * Current log file
     * 
     * @var string
     */
    private string $log_file;
    
    /**
     * Max file size in bytes
     * 
     * @var int
     */
    private int $max_size = 10485760; // 10MB
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->min_level = HP_DEBUG ? self::DEBUG : self::WARNING;
        
        // Set up log directory
        $upload_dir = wp_upload_dir();
        $this->log_dir = $upload_dir['basedir'] . '/hp-logs/';
        
        // Create directory if it doesn't exist
        if (!file_exists($this->log_dir)) {
            wp_mkdir_p($this->log_dir);
            
            // Protect log directory
            $htaccess = $this->log_dir . '.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, 'Deny from all');
            }
        }
        
        // Set current log file
        $this->log_file = $this->log_dir . 'hp-' . date('Y-m-d') . '.log';
        
        // Rotate logs if needed
        $this->rotate_logs();
    }
    
    /**
     * Log a message
     * 
     * @param string $level Log level
     * @param string $message Message to log
     * @param array $context Context data
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void {
        // Check if we should log this level
        if (!$this->should_log($level)) {
            return;
        }
        
        // Format the log entry
        $entry = $this->format_entry($level, $message, $context);
        
        // Write to file
        $this->write($entry);
        
        // Also log to database for critical errors
        if (in_array($level, [self::EMERGENCY, self::ALERT, self::CRITICAL, self::ERROR])) {
            $this->log_to_database($level, $message, $context);
        }
        
        // Send notifications for emergency/alert
        if (in_array($level, [self::EMERGENCY, self::ALERT])) {
            $this->send_notification($level, $message, $context);
        }
    }
    
    /**
     * Log emergency message
     * 
     * @param string $message
     * @param array $context
     */
    public function emergency(string $message, array $context = []): void {
        $this->log(self::EMERGENCY, $message, $context);
    }
    
    /**
     * Log alert message
     * 
     * @param string $message
     * @param array $context
     */
    public function alert(string $message, array $context = []): void {
        $this->log(self::ALERT, $message, $context);
    }
    
    /**
     * Log critical message
     * 
     * @param string $message
     * @param array $context
     */
    public function critical(string $message, array $context = []): void {
        $this->log(self::CRITICAL, $message, $context);
    }
    
    /**
     * Log error message
     * 
     * @param string $message
     * @param array $context
     */
    public function error(string $message, array $context = []): void {
        $this->log(self::ERROR, $message, $context);
    }
    
    /**
     * Log warning message
     * 
     * @param string $message
     * @param array $context
     */
    public function warning(string $message, array $context = []): void {
        $this->log(self::WARNING, $message, $context);
    }
    
    /**
     * Log notice message
     * 
     * @param string $message
     * @param array $context
     */
    public function notice(string $message, array $context = []): void {
        $this->log(self::NOTICE, $message, $context);
    }
    
    /**
     * Log info message
     * 
     * @param string $message
     * @param array $context
     */
    public function info(string $message, array $context = []): void {
        $this->log(self::INFO, $message, $context);
    }
    
    /**
     * Log debug message
     * 
     * @param string $message
     * @param array $context
     */
    public function debug(string $message, array $context = []): void {
        $this->log(self::DEBUG, $message, $context);
    }
    
    /**
     * Check if should log this level
     * 
     * @param string $level
     * @return bool
     */
    private function should_log(string $level): bool {
        $level_priority = $this->levels[$level] ?? 0;
        $min_priority = $this->levels[$this->min_level] ?? 0;
        
        return $level_priority >= $min_priority;
    }
    
    /**
     * Format log entry
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     * @return string
     */
    private function format_entry(string $level, string $message, array $context): string {
        $timestamp = date('Y-m-d H:i:s');
        $level_str = strtoupper($level);
        
        // Add context to message if provided
        if (!empty($context)) {
            $message .= ' ' . json_encode($context);
        }
        
        // Add additional info
        $user_id = get_current_user_id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $url = $_SERVER['REQUEST_URI'] ?? 'N/A';
        
        return "[{$timestamp}] [{$level_str}] {$message} | User: {$user_id} | IP: {$ip} | URL: {$url}\n";
    }
    
    /**
     * Write log entry to file
     * 
     * @param string $entry
     * @return void
     */
    private function write(string $entry): void {
        // Check file size and rotate if needed
        if (file_exists($this->log_file) && filesize($this->log_file) > $this->max_size) {
            $this->rotate_logs();
        }
        
        // Write to file
        @file_put_contents($this->log_file, $entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Rotate log files
     * 
     * @return void
     */
    private function rotate_logs(): void {
        if (!file_exists($this->log_file)) {
            return;
        }
        
        // Check file size
        if (filesize($this->log_file) > $this->max_size) {
            // Rename current file
            $rotated_file = $this->log_dir . 'hp-' . date('Y-m-d-His') . '.log';
            rename($this->log_file, $rotated_file);
            
            // Compress old file
            if (function_exists('gzopen')) {
                $gz = gzopen($rotated_file . '.gz', 'w9');
                gzwrite($gz, file_get_contents($rotated_file));
                gzclose($gz);
                unlink($rotated_file);
            }
        }
        
        // Clean old logs (keep 30 days)
        $this->clean_old_logs();
    }
    
    /**
     * Clean old log files
     * 
     * @return void
     */
    private function clean_old_logs(): void {
        $files = glob($this->log_dir . '*.log*');
        $now = time();
        
        foreach ($files as $file) {
            if ($now - filemtime($file) > 30 * 86400) { // 30 days
                unlink($file);
            }
        }
    }
    
    /**
     * Log to database
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    private function log_to_database(string $level, string $message, array $context): void {
        global $wpdb;
        
        $table = $wpdb->prefix . 'hp_error_log';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return;
        }
        
        $wpdb->insert(
            $table,
            [
                'error_level' => $this->levels[$level] ?? 0,
                'error_message' => $message,
                'error_context' => json_encode($context),
                'user_id' => get_current_user_id() ?: null,
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ],
            ['%d', '%s', '%s', '%d', '%s', '%s']
        );
    }
    
    /**
     * Send notification for critical logs
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    private function send_notification(string $level, string $message, array $context): void {
        // Check if notifications are enabled
        if (!get_option('hp_log_notifications', false)) {
            return;
        }
        
        // Throttle notifications
        $last_sent = get_transient('hp_last_log_notification');
        if ($last_sent && (time() - $last_sent) < 3600) {
            return;
        }
        
        // Send email to admin
        $to = get_option('admin_email');
        $subject = sprintf('[%s] %s Alert: Happy Place Plugin', get_bloginfo('name'), ucfirst($level));
        $body = "An {$level} level event occurred:\n\n{$message}\n\n";
        
        if (!empty($context)) {
            $body .= "Context:\n" . print_r($context, true);
        }
        
        wp_mail($to, $subject, $body);
        
        // Set throttle
        set_transient('hp_last_log_notification', time(), 3600);
    }
    
    /**
     * Get recent log entries
     * 
     * @param int $limit Number of entries
     * @param string|null $level Filter by level
     * @return array
     */
    public function get_recent_logs(int $limit = 100, ?string $level = null): array {
        $logs = [];
        
        if (!file_exists($this->log_file)) {
            return $logs;
        }
        
        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_reverse($lines);
        
        foreach ($lines as $line) {
            if ($level && stripos($line, "[{$level}]") === false) {
                continue;
            }
            
            $logs[] = $line;
            
            if (count($logs) >= $limit) {
                break;
            }
        }
        
        return $logs;
    }
}