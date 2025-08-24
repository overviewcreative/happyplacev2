<?php
/**
 * Base Service Class
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
 * Abstract Service Class
 */
abstract class Service {
    
    /**
     * Service name
     */
    protected string $name = '';
    
    /**
     * Service version
     */
    protected string $version = '1.0.0';
    
    /**
     * Is initialized
     */
    protected bool $initialized = false;
    
    /**
     * Initialize the service
     */
    abstract public function init(): void;
    
    /**
     * Get service name
     */
    public function get_name(): string {
        return $this->name;
    }
    
    /**
     * Get service version
     */
    public function get_version(): string {
        return $this->version;
    }
    
    /**
     * Check if initialized
     */
    public function is_initialized(): bool {
        return $this->initialized;
    }
    
    /**
     * Log service activity
     */
    protected function log(string $message, string $level = 'info'): void {
        hp_log($message, $level, strtoupper($this->name));
    }
}