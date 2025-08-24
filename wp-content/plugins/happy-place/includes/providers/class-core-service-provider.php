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
        // Configuration - loads from config/ directory
        $this->singleton('config', function() {
            return new Config(HP_CONFIG_DIR);
        });
        
        // Database abstraction
        $this->singleton('database', function() {
            return new Database();
        });
        
        // Cache layer
        $this->singleton('cache', function($container) {
            $config = $container->get('config');
            $cache_config = $config->get('cache', [
                'driver' => 'transient',
                'prefix' => 'hp_cache_',
                'ttl' => 3600
            ]);
            return new Cache($cache_config);
        });
        
        // Logger
        $this->singleton('logger', function() {
            return new Logger();
        });
        
        // Scheduler for cron jobs
        $this->singleton('scheduler', function() {
            return new Scheduler();
        });
        
        // Create aliases for convenience
        $this->alias('db', 'database');
        $this->alias('log', 'logger');
    }
    
    /**
     * Boot services
     * 
     * @return void
     */
    public function boot(): void {
        // Initialize database tables if needed
        $database = $this->container->get('database');
        if (method_exists($database, 'init')) {
            $database->init();
        }
        
        // Set up scheduled jobs
        $scheduler = $this->container->get('scheduler');
        if (method_exists($scheduler, 'init')) {
            $scheduler->init();
        }
        
        // Set up hooks for cache clearing
        add_action('hp_clear_cache', function() {
            $this->container->get('cache')->flush();
        });
    }
}