<?php
/**
 * Service Container
 * 
 * Dependency injection container with singleton support, auto-wiring,
 * and service provider management
 *
 * @package HappyPlace\Core
 * @version 4.0.0
 */

namespace HappyPlace\Core;

use Closure;
use ReflectionClass;
use ReflectionParameter;
use Exception;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Service Container Class
 * 
 * @since 4.0.0
 */
class Container {
    
    /**
     * Registered services
     * 
     * @var array
     */
    private array $services = [];
    
    /**
     * Singleton instances
     * 
     * @var array
     */
    private array $instances = [];
    
    /**
     * Service aliases
     * 
     * @var array
     */
    private array $aliases = [];
    
    /**
     * Service providers
     * 
     * @var array
     */
    private array $providers = [];
    
    /**
     * Resolved services
     * 
     * @var array
     */
    private array $resolved = [];
    
    /**
     * Bind a service to the container
     * 
     * @param string $id Service identifier
     * @param mixed $concrete Service implementation
     * @param bool $shared Whether to share the instance
     * @return void
     */
    public function bind(string $id, $concrete = null, bool $shared = false): void {
        if (is_null($concrete)) {
            $concrete = $id;
        }
        
        $this->services[$id] = [
            'concrete' => $concrete,
            'shared' => $shared
        ];
        
        // Clear any existing instance
        unset($this->instances[$id]);
        unset($this->resolved[$id]);
    }
    
    /**
     * Bind a singleton service
     * 
     * @param string $id Service identifier
     * @param mixed $concrete Service implementation
     * @return void
     */
    public function singleton(string $id, $concrete = null): void {
        $this->bind($id, $concrete, true);
    }
    
    /**
     * Bind an existing instance
     * 
     * @param string $id Service identifier
     * @param mixed $instance Service instance
     * @return void
     */
    public function instance(string $id, $instance): void {
        $this->instances[$id] = $instance;
        $this->resolved[$id] = true;
    }
    
    /**
     * Create an alias for a service
     * 
     * @param string $alias Alias name
     * @param string $id Service identifier
     * @return void
     */
    public function alias(string $alias, string $id): void {
        $this->aliases[$alias] = $id;
    }
    
    /**
     * Get a service from the container
     * 
     * @param string $id Service identifier
     * @return mixed
     * @throws Exception
     */
    public function get(string $id) {
        // Check for alias
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }
        
        // Return existing instance if available
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        
        // Resolve the service
        return $this->resolve($id);
    }
    
    /**
     * Check if a service exists
     * 
     * @param string $id Service identifier
     * @return bool
     */
    public function has(string $id): bool {
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }
        
        return isset($this->services[$id]) || 
               isset($this->instances[$id]) || 
               class_exists($id);
    }
    
    /**
     * Resolve a service
     * 
     * @param string $id Service identifier
     * @return mixed
     * @throws Exception
     */
    private function resolve(string $id) {
        // Check for circular dependency
        if (isset($this->resolved[$id]) && !isset($this->instances[$id])) {
            throw new Exception("Circular dependency detected for service: {$id}");
        }
        
        $this->resolved[$id] = true;
        
        // Get service definition
        $definition = $this->services[$id] ?? null;
        
        if ($definition === null) {
            // Try to auto-wire the class
            if (class_exists($id)) {
                $concrete = $id;
                $shared = false;
            } else {
                throw new Exception("Service not found: {$id}");
            }
        } else {
            $concrete = $definition['concrete'];
            $shared = $definition['shared'];
        }
        
        // Build the instance
        if ($concrete instanceof Closure) {
            $instance = $concrete($this);
        } elseif (is_string($concrete) && class_exists($concrete)) {
            $instance = $this->build($concrete);
        } else {
            $instance = $concrete;
        }
        
        // Store singleton instance
        if ($shared) {
            $this->instances[$id] = $instance;
        }
        
        return $instance;
    }
    
    /**
     * Build a class instance with dependency injection
     * 
     * @param string $class Class name
     * @return object
     * @throws Exception
     */
    private function build(string $class): object {
        $reflection = new ReflectionClass($class);
        
        // Check if class is instantiable
        if (!$reflection->isInstantiable()) {
            throw new Exception("Class is not instantiable: {$class}");
        }
        
        $constructor = $reflection->getConstructor();
        
        // No constructor, create instance
        if ($constructor === null) {
            return new $class;
        }
        
        // Get constructor parameters
        $parameters = $constructor->getParameters();
        $dependencies = [];
        
        foreach ($parameters as $parameter) {
            $dependencies[] = $this->resolveDependency($parameter);
        }
        
        // Create instance with dependencies
        return $reflection->newInstanceArgs($dependencies);
    }
    
    /**
     * Resolve a dependency parameter
     * 
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws Exception
     */
    private function resolveDependency(ReflectionParameter $parameter) {
        $type = $parameter->getType();
        
        // No type hint, check for default value
        if ($type === null) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            throw new Exception("Cannot resolve parameter: {$parameter->getName()}");
        }
        
        // Get type name
        $typeName = $type->getName();
        
        // Try to resolve from container
        if ($this->has($typeName)) {
            return $this->get($typeName);
        }
        
        // Try to auto-wire
        if (class_exists($typeName)) {
            return $this->build($typeName);
        }
        
        // Check for default value
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }
        
        // Check if nullable
        if ($type->allowsNull()) {
            return null;
        }
        
        throw new Exception("Cannot resolve dependency: {$typeName}");
    }
    
    /**
     * Call a method with dependency injection
     * 
     * @param mixed $callback Callback to invoke
     * @param array $parameters Additional parameters
     * @return mixed
     * @throws Exception
     */
    public function call($callback, array $parameters = []) {
        if (is_string($callback) && strpos($callback, '@') !== false) {
            list($class, $method) = explode('@', $callback);
            $callback = [$this->get($class), $method];
        }
        
        if (is_array($callback)) {
            list($object, $method) = $callback;
            
            if (is_string($object)) {
                $object = $this->get($object);
            }
            
            $reflection = new \ReflectionMethod($object, $method);
            $dependencies = [];
            
            foreach ($reflection->getParameters() as $parameter) {
                if (array_key_exists($parameter->getName(), $parameters)) {
                    $dependencies[] = $parameters[$parameter->getName()];
                } else {
                    $dependencies[] = $this->resolveDependency($parameter);
                }
            }
            
            return $reflection->invokeArgs($object, $dependencies);
        }
        
        return call_user_func_array($callback, $parameters);
    }
    
    /**
     * Add a service provider
     * 
     * @param ServiceProvider $provider
     * @return void
     */
    public function addProvider(ServiceProvider $provider): void {
        $this->providers[] = $provider;
    }
    
    /**
     * Get all service providers
     * 
     * @return array
     */
    public function getProviders(): array {
        return $this->providers;
    }
    
    /**
     * Magic method to get service
     * 
     * @param string $name
     * @return mixed
     */
    public function __get(string $name) {
        return $this->get($name);
    }
    
    /**
     * Magic method to check if service exists
     * 
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool {
        return $this->has($name);
    }
}