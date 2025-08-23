<?php
/**
 * Hookable Interface
 * 
 * Defines the contract for classes that need to register WordPress hooks
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface HPH_Hookable
 * 
 * Classes that register WordPress hooks should implement this interface
 */
interface HPH_Hookable {
    
    /**
     * Register WordPress hooks
     * 
     * @return void
     */
    public function register_hooks();
    
    /**
     * Get hook priority for actions/filters
     * 
     * @return int
     */
    public function get_hook_priority();
}
