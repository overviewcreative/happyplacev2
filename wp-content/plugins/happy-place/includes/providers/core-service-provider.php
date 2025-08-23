<?php
/**
 * Core Service Provider
 * 
 * Registers core services for the plugin
 *
 * @package HappyPlace\Providers
 * @version 4.0.0
 */

namespace HappyPlace\Providers;

use HappyPlace\Core\ServiceProvider;
use HappyPlace\Core\Database;
use HappyPlace\Core\Cache;
use HappyPlace\Core\Scheduler;
use HappyPlace\Core\Logger;
use HappyPlace\Core\Config;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core Service Provider Class
 * 
 * @since 4.0.0
 */
class CoreServiceProvider extends ServiceProvider {
    
    /**
     * Register services
     * 
     * @return void
     */
    public function register(): void {
        // Configuration
        $this->singleton('config', function() {
            return new Config(HP_PLUGIN_DIR . 'config/');
        });
        
        // Database
        $this->singleton('database', function() {
            return new Database();
        });
        
        // Cache
        $this->singleton('cache', function($container) {
            $config = $container->get('config');
            return new Cache($config->get('cache'));
        });
        
        // Logger
        $this->singleton('logger', function() {
            return new Logger();
        });
        
        // Scheduler
        $this->singleton('scheduler', function() {
            return new Scheduler();
        });
        
        // Aliases
        $this->alias('db', 'database');
        $this->alias('log', 'logger');
    }
    
    /**
     * Boot services
     * 
     * @return void
     */
    public function boot(): void {
        // Initialize database
        $this->container->get('database')->init();
        
        // Set up scheduled jobs
        $this->container->get('scheduler')->init();
    }
}