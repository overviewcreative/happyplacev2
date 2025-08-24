<?php
/**
 * Cache Class
 * 
 * Handles caching with support for multiple drivers (transients, object cache, etc.)
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
 * Cache Class
 * 
 * @since 4.0.0
 */
class Cache {
    
    /**
     * Cache driver
     * 
     * @var string
     */
    private string $driver;
    
    /**
     * Cache prefix
     * 
     * @var string
     */
    private string $prefix;
    
    /**
     * Default TTL (time to live) in seconds
     * 
     * @var int
     */
    private int $default_ttl;
    
    /**
     * Cache groups
     * 
     * @var array
     */
    private array $groups = [];
    
    /**
     * Constructor
     * 
     * @param array $config Configuration options
     */
    public function __construct(array $config = []) {
        $this->driver = $config['driver'] ?? $this->detect_driver();
        $this->prefix = $config['prefix'] ?? 'hp_cache_';
        $this->default_ttl = $config['ttl'] ?? 3600; // 1 hour default
        
        $this->init_groups();
    }
    
    /**
     * Detect best available cache driver
     * 
     * @return string
     */
    private function detect_driver(): string {
        // Check for object cache
        if (wp_using_ext_object_cache()) {
            return 'object';
        }
        
        // Default to transients
        return 'transient';
    }
    
    /**
     * Initialize cache groups
     * 
     * @return void
     */
    private function init_groups(): void {
        $this->groups = [
            'listings' => 3600,      // 1 hour
            'agents' => 7200,        // 2 hours
            'searches' => 1800,      // 30 minutes
            'analytics' => 300,      // 5 minutes
            'api' => 600,           // 10 minutes
            'templates' => 86400,    // 24 hours
            'queries' => 1800,       // 30 minutes
        ];
    }
    
    /**
     * Get cached value
     * 
     * @param string $key Cache key
     * @param string $group Cache group
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $key, string $group = 'default', $default = null) {
        $cache_key = $this->build_key($key, $group);
        
        switch ($this->driver) {
            case 'object':
                $value = wp_cache_get($cache_key, $group);
                break;
                
            case 'transient':
            default:
                $value = get_transient($cache_key);
                break;
        }
        
        if (false === $value) {
            return $default;
        }
        
        return $value;
    }
    
    /**
     * Set cache value
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param string $group Cache group
     * @param int|null $ttl Time to live in seconds
     * @return bool
     */
    public function set(string $key, $value, string $group = 'default', ?int $ttl = null): bool {
        $cache_key = $this->build_key($key, $group);
        
        if (null === $ttl) {
            $ttl = $this->groups[$group] ?? $this->default_ttl;
        }
        
        switch ($this->driver) {
            case 'object':
                return wp_cache_set($cache_key, $value, $group, $ttl);
                
            case 'transient':
            default:
                return set_transient($cache_key, $value, $ttl);
        }
    }
    
    /**
     * Delete cached value
     * 
     * @param string $key Cache key
     * @param string $group Cache group
     * @return bool
     */
    public function delete(string $key, string $group = 'default'): bool {
        $cache_key = $this->build_key($key, $group);
        
        switch ($this->driver) {
            case 'object':
                return wp_cache_delete($cache_key, $group);
                
            case 'transient':
            default:
                return delete_transient($cache_key);
        }
    }
    
    /**
     * Remember value with callback
     * 
     * @param string $key Cache key
     * @param callable $callback Callback to generate value
     * @param string $group Cache group
     * @param int|null $ttl Time to live
     * @return mixed
     */
    public function remember(string $key, callable $callback, string $group = 'default', ?int $ttl = null) {
        $value = $this->get($key, $group);
        
        if (null !== $value && false !== $value) {
            return $value;
        }
        
        $value = call_user_func($callback);
        
        if (null !== $value && false !== $value) {
            $this->set($key, $value, $group, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Flush cache group
     * 
     * @param string $group Group to flush
     * @return bool
     */
    public function flush_group(string $group): bool {
        global $wpdb;
        
        switch ($this->driver) {
            case 'object':
                return wp_cache_flush_group($group);
                
            case 'transient':
            default:
                // For transients, we need to delete by pattern
                $prefix = $this->build_key('', $group);
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM {$wpdb->options} 
                        WHERE option_name LIKE %s 
                        OR option_name LIKE %s",
                        '_transient_' . $prefix . '%',
                        '_transient_timeout_' . $prefix . '%'
                    )
                );
                return true;
        }
    }
    
    /**
     * Flush all cache
     * 
     * @return bool
     */
    public function flush(): bool {
        switch ($this->driver) {
            case 'object':
                return wp_cache_flush();
                
            case 'transient':
            default:
                global $wpdb;
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM {$wpdb->options} 
                        WHERE option_name LIKE %s 
                        OR option_name LIKE %s",
                        '_transient_' . $this->prefix . '%',
                        '_transient_timeout_' . $this->prefix . '%'
                    )
                );
                return true;
        }
    }
    
    /**
     * Build cache key
     * 
     * @param string $key Key
     * @param string $group Group
     * @return string
     */
    private function build_key(string $key, string $group): string {
        if (empty($key)) {
            return $this->prefix . $group;
        }
        
        return $this->prefix . $group . '_' . md5($key);
    }
    
    /**
     * Get cache statistics
     * 
     * @return array
     */
    public function get_stats(): array {
        global $wpdb;
        
        $stats = [
            'driver' => $this->driver,
            'groups' => array_keys($this->groups),
            'entries' => 0,
        ];
        
        if ($this->driver === 'transient') {
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->options} 
                    WHERE option_name LIKE %s",
                    '_transient_' . $this->prefix . '%'
                )
            );
            $stats['entries'] = intval($count);
        }
        
        return $stats;
    }
    
    /**
     * Increment numeric cache value
     * 
     * @param string $key Cache key
     * @param int $offset Amount to increment
     * @param string $group Cache group
     * @return int|false New value or false on failure
     */
    public function increment(string $key, int $offset = 1, string $group = 'default'): int|false {
        $value = $this->get($key, $group, 0);
        
        if (!is_numeric($value)) {
            return false;
        }
        
        $new_value = intval($value) + $offset;
        
        if ($this->set($key, $new_value, $group)) {
            return $new_value;
        }
        
        return false;
    }
    
    /**
     * Decrement numeric cache value
     * 
     * @param string $key Cache key
     * @param int $offset Amount to decrement
     * @param string $group Cache group
     * @return int|false New value or false on failure
     */
    public function decrement(string $key, int $offset = 1, string $group = 'default'): int|false {
        return $this->increment($key, -$offset, $group);
    }
}