<?php
/**
 * Service Provider Base Class
 * 
 * Abstract base for all service providers
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
 * Service Provider Abstract Class
 * 
 * @since 4.0.0
 */
abstract class ServiceProvider {
    
    /**
     * Container instance
     * 
     * @var Container
     */
    protected Container $container;
    
    /**
     * Services provided
     * 
     * @var array
     */
    protected array $provides = [];
    
    /**
     * Constructor
     * 
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }
    
    /**
     * Register services
     * 
     * @return void
     */
    abstract public function register(): void;
    
    /**
     * Boot services
     * 
     * @return void
     */
    public function boot(): void {
        // Override in child classes if needed
    }
    
    /**
     * Get provided services
     * 
     * @return array
     */
    public function provides(): array {
        return $this->provides;
    }
    
    /**
     * Bind a service
     * 
     * @param string $id
     * @param mixed $concrete
     * @param bool $shared
     * @return void
     */
    protected function bind(string $id, $concrete = null, bool $shared = false): void {
        $this->container->bind($id, $concrete, $shared);
        $this->provides[] = $id;
    }
    
    /**
     * Bind a singleton
     * 
     * @param string $id
     * @param mixed $concrete
     * @return void
     */
    protected function singleton(string $id, $concrete = null): void {
        $this->container->singleton($id, $concrete);
        $this->provides[] = $id;
    }
    
    /**
     * Create an alias
     * 
     * @param string $alias
     * @param string $id
     * @return void
     */
    protected function alias(string $alias, string $id): void {
        $this->container->alias($alias, $id);
    }
}