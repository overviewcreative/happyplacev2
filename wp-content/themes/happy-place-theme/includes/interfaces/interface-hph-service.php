<?php
/**
 * Service Interface
 * 
 * Defines the contract for all HPH services
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface HPH_Service
 * 
 * All theme services must implement this interface
 */
interface HPH_Service {
    
    /**
     * Initialize the service
     * 
     * @return void
     */
    public function init();
    
    /**
     * Get service identifier
     * 
     * @return string
     */
    public function get_service_id();
    
    /**
     * Check if service is active
     * 
     * @return bool
     */
    public function is_active();
    
    /**
     * Get service dependencies
     * 
     * @return array Array of service IDs this service depends on
     */
    public function get_dependencies();
}
