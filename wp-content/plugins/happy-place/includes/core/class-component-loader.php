<?php
/**
 * Component Loader
 * 
 * Manages component loading with error recovery and fallback mechanisms
 *
 * @package HappyPlace\Core
 * @version 4.0.0
 */

namespace HappyPlace\Core;

use Exception;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Component Loader Class
 * 
 * @since 4.0.0
 */
class ComponentLoader {
    
    /**
     * Service container
     * 
     * @var Container
     */
    private Container $container;
    
    /**
     * Error handler
     * 
     * @var ErrorHandler
     */
    private ErrorHandler $error_handler;
    
    /**
     * Loaded components
     * 
     * @var array
     */
    private array $loaded = [];
    
    /**
     * Failed components
     * 
     * @var array
     */
    private array $failed = [];
    
    /**
     * Component dependencies
     * 
     * @var array
     */
    private array $dependencies = [
        'PostTypes' => [],
        'Taxonomies' => ['PostTypes'],
        'ACFManager' => [],
        'FieldMapper' => ['ACFManager'],
        'AdminMenu' => ['PostTypes'],
        'RestAPI' => ['PostTypes', 'ACFManager'],
        'AssetsManager' => [],
        'DashboardManager' => ['PostTypes', 'ACFManager'],
        'ConfigSyncManager' => [],
        'Database' => [],
        'Cache' => [],
    ];
    
    /**
     * Critical components that must load
     * 
     * @var array
     */
    private array $critical = [
        'PostTypes',
        'Taxonomies',
    ];
    
    /**
     * Constructor
     * 
     * @param Container $container
     * @param ErrorHandler $error_handler
     */
    public function __construct(Container $container, ErrorHandler $error_handler) {
        $this->container = $container;
        $this->error_handler = $error_handler;
    }
    
    /**
     * Load a component
     * 
     * @param string $component Component name
     * @param array $config Component configuration
     * @return bool
     */
    public function load(string $component, array $config = []): bool {
        // Check if already loaded
        if (isset($this->loaded[$component])) {
            return true;
        }
        
        // Check if previously failed
        if (isset($this->failed[$component])) {
            return false;
        }
        
        try {
            // Check dependencies
            if (!$this->check_dependencies($component)) {
                throw new Exception("Dependencies not met for component: {$component}");
            }
            
            // Get component class - convert component name to class name
            $class_name = str_replace('_', '', $component); // Remove underscores
            $class = $config['class'] ?? "HappyPlace\\Core\\{$class_name}";
            
            // Check if class exists
            if (!class_exists($class)) {
                // Try to load file
                $file = $config['file'] ?? $this->get_component_file($component);
                
                if ($file && file_exists($file)) {
                    require_once $file;
                }
                
                // Check again
                if (!class_exists($class)) {
                    throw new Exception("Component class not found: {$class}");
                }
            }
            
            // Create instance
            $instance = $this->create_instance($class, $config);
            
            // Initialize if needed
            if ($config['init'] ?? false) {
                if (method_exists($instance, 'init')) {
                    $instance->init();
                }
            }
            
            // Register in container
            $this->container->instance($component, $instance);
            
            // Mark as loaded
            $this->loaded[$component] = true;
            
            hp_log("Component loaded: {$component}", 'info', 'LOADER');
            
            return true;
            
        } catch (Exception $e) {
            $this->handle_load_failure($component, $e);
            return false;
        }
    }
    
    /**
     * Load multiple components
     * 
     * @param array $components Components to load
     * @return array Results
     */
    public function load_multiple(array $components): array {
        $results = [];
        
        // Sort by dependencies
        $sorted = $this->sort_by_dependencies($components);
        
        foreach ($sorted as $component => $config) {
            $results[$component] = $this->load($component, $config);
        }
        
        return $results;
    }
    
    /**
     * Check component dependencies
     * 
     * @param string $component Component name
     * @return bool
     */
    private function check_dependencies(string $component): bool {
        $deps = $this->dependencies[$component] ?? [];
        
        foreach ($deps as $dep) {
            if (!isset($this->loaded[$dep])) {
                // Try to load dependency
                if (!$this->load($dep)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get component file path using lowercase hyphenated convention
     * 
     * @param string $component Component name
     * @return string|null
     */
    private function get_component_file(string $component): ?string {
        // Convert component name to file name
        // PostTypes -> class-post-types.php
        // ACFManager -> class-acf-manager.php
        $file_name = preg_replace('/([a-z])([A-Z])/', '$1-$2', $component);
        $file_name = 'class-' . strtolower($file_name) . '.php';
        
        // Map to correct directories
        $directory_mappings = [
            'PostTypes' => 'core',
            'Taxonomies' => 'core',
            'ACFManager' => 'core',
            'ACFJsonLoader' => 'core',
            'FieldMapper' => 'core',
            'AssetsManager' => 'core',
            'ConfigSyncManager' => 'core',
            'Database' => 'core',
            'Cache' => 'core',
            'AdminMenu' => 'admin',
            'ACFSyncManager' => 'admin',
            'RestAPI' => 'api',
            'DashboardManager' => 'dashboard',
            'DashboardAjax' => 'api/ajax',
        ];
        
        $directory = $directory_mappings[$component] ?? 'core';
        $file_path = HP_INCLUDES_DIR . $directory . '/' . $file_name;
        
        // Check if file exists
        if (file_exists($file_path)) {
            return $file_path;
        }
        
        // Try without directory for root-level files
        $root_path = HP_INCLUDES_DIR . $file_name;
        if (file_exists($root_path)) {
            return $root_path;
        }
        
        // Log the attempted paths for debugging
        hp_log("Component file not found. Tried: {$file_path} and {$root_path}", 'debug', 'LOADER');
        
        return null;
    }
    
    /**
     * Create component instance
     * 
     * @param string $class Class name
     * @param array $config Configuration
     * @return object
     */
    private function create_instance(string $class, array $config): object {
        // Check for singleton pattern
        if (method_exists($class, 'get_instance')) {
            return $class::get_instance();
        }
        
        if (method_exists($class, 'instance')) {
            return $class::instance();
        }
        
        // Use container to build with dependencies
        return $this->container->get($class);
    }
    
    /**
     * Handle component load failure
     * 
     * @param string $component Component name
     * @param Exception $e Exception
     * @return void
     */
    private function handle_load_failure(string $component, Exception $e): void {
        // Mark as failed
        $this->failed[$component] = [
            'error' => $e->getMessage(),
            'time' => time()
        ];
        
        // Log error
        hp_log("Failed to load component {$component}: " . $e->getMessage(), 'error', 'LOADER');
        
        // Handle critical components
        if (in_array($component, $this->critical)) {
            $this->error_handler->handle_critical_error(
                "Critical component failed to load: {$component}",
                $e
            );
        } else {
            // Try fallback for non-critical components
            $this->load_fallback($component);
        }
    }
    
    /**
     * Load fallback for failed component
     * 
     * @param string $component Component name
     * @return void
     */
    private function load_fallback(string $component): void {
        $fallbacks = [
            'ACFManager' => 'HappyPlace\\Core\\Fallback\\SimpleFieldManager',
            'AssetsManager' => 'HappyPlace\\Core\\Fallback\\BasicAssetManager',
            'Cache' => 'HappyPlace\\Core\\Fallback\\NoOpCache',
        ];
        
        if (isset($fallbacks[$component])) {
            try {
                $fallback_class = $fallbacks[$component];
                
                // Try to load fallback file
                $fallback_file = $this->get_fallback_file($fallback_class);
                if ($fallback_file && file_exists($fallback_file)) {
                    require_once $fallback_file;
                }
                
                if (class_exists($fallback_class)) {
                    $instance = new $fallback_class();
                    $this->container->instance($component, $instance);
                    
                    hp_log("Loaded fallback for component: {$component}", 'warning', 'LOADER');
                }
            } catch (Exception $e) {
                hp_log("Failed to load fallback for {$component}: " . $e->getMessage(), 'error', 'LOADER');
            }
        }
    }
    
    /**
     * Get fallback file path
     * 
     * @param string $class_name Full class name
     * @return string|null
     */
    private function get_fallback_file(string $class_name): ?string {
        // Extract class name from namespace
        $parts = explode('\\', $class_name);
        $class = array_pop($parts);
        
        // Convert to file name
        $file_name = preg_replace('/([a-z])([A-Z])/', '$1-$2', $class);
        $file_name = 'class-' . strtolower($file_name) . '.php';
        
        // Fallback files are in core/fallback/
        return HP_INCLUDES_DIR . 'core/fallback/' . $file_name;
    }
    
    /**
     * Sort components by dependencies
     * 
     * @param array $components Components to sort
     * @return array Sorted components
     */
    private function sort_by_dependencies(array $components): array {
        $sorted = [];
        $visited = [];
        
        foreach (array_keys($components) as $component) {
            $this->visit_component($component, $components, $sorted, $visited);
        }
        
        return $sorted;
    }
    
    /**
     * Visit component for dependency sorting
     * 
     * @param string $component Component name
     * @param array $components All components
     * @param array &$sorted Sorted result
     * @param array &$visited Visited tracking
     * @return void
     */
    private function visit_component(
        string $component, 
        array $components, 
        array &$sorted, 
        array &$visited
    ): void {
        if (isset($visited[$component])) {
            return;
        }
        
        $visited[$component] = true;
        
        // Visit dependencies first
        $deps = $this->dependencies[$component] ?? [];
        foreach ($deps as $dep) {
            if (isset($components[$dep])) {
                $this->visit_component($dep, $components, $sorted, $visited);
            }
        }
        
        // Add component
        if (isset($components[$component])) {
            $sorted[$component] = $components[$component];
        }
    }
    
    /**
     * Get loaded components
     * 
     * @return array
     */
    public function get_loaded(): array {
        return array_keys($this->loaded);
    }
    
    /**
     * Get failed components
     * 
     * @return array
     */
    public function get_failed(): array {
        return $this->failed;
    }
    
    /**
     * Check if component is loaded
     * 
     * @param string $component Component name
     * @return bool
     */
    public function is_loaded(string $component): bool {
        return isset($this->loaded[$component]);
    }
}