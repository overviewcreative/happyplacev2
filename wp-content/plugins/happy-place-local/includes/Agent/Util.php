<?php
namespace HappyPlace\Local\Agent;

/**
 * Helper utilities for the AI agent pipeline
 */
class Util {

  /**
   * Guard against missing array keys with default values
   */
  public static function safe_array_get(array $data, string $key, $default = null) {
    return isset($data[$key]) ? $data[$key] : $default;
  }

  /**
   * Check if a Google Places types array indicates a city/locality
   */
  public static function looks_like_city(array $types): bool {
    $city_types = ['locality', 'postal_town', 'administrative_area_level_3', 'political'];
    $establishment_types = ['establishment', 'restaurant', 'food', 'store', 'point_of_interest'];
    
    $has_city_type = !empty(array_intersect($types, $city_types));
    $has_establishment_type = !empty(array_intersect($types, $establishment_types));
    
    // If it has establishment types, it's a place not a city
    if ($has_establishment_type) {
      return false;
    }
    
    // If it only has city types, it's a city
    return $has_city_type;
  }

  /**
   * Validate a post_id parameter
   */
  public static function valid_post_id(int $post_id): bool {
    return $post_id > 0 && get_post($post_id) !== null;
  }

  /**
   * Get API key with fallback priority: constant > option > config file
   */
  public static function get_api_key(string $key_type): string {
    switch ($key_type) {
      case 'google_places':
        if (defined('HPL_GOOGLE_PLACES_KEY') && !empty(HPL_GOOGLE_PLACES_KEY)) {
          return HPL_GOOGLE_PLACES_KEY;
        }
        break;
        
      case 'openai':
        if (defined('HPL_OPENAI_API_KEY') && !empty(HPL_OPENAI_API_KEY)) {
          return HPL_OPENAI_API_KEY;
        }
        break;
        
      case 'anthropic':
        if (defined('HPL_ANTHROPIC_API_KEY') && !empty(HPL_ANTHROPIC_API_KEY)) {
          return HPL_ANTHROPIC_API_KEY;
        }
        break;
    }

    // Fall back to options
    $config = get_option('hpl_config', []);
    $option_key = $key_type . '_api_key';
    if (!empty($config[$option_key])) {
      return trim($config[$option_key]);
    }

    // Final fallback to config file
    $file = WP_CONTENT_DIR . '/plugins/happy-place-local/config/sources.local.json';
    if (file_exists($file)) {
      try {
        $json = json_decode(file_get_contents($file), true) ?: [];
        if ($key_type === 'google_places' && !empty($json['google']['api_key'])) {
          return trim($json['google']['api_key']);
        }
        if ($key_type === 'openai' && !empty($json['openai']['api_key'])) {
          return trim($json['openai']['api_key']);
        }
      } catch (\Throwable $e) {
        error_log('[HPL] Util: failed to read config file: ' . $e->getMessage());
      }
    }

    return '';
  }

  /**
   * Log pipeline errors with consistent format
   */
  public static function log_error(string $component, int $post_id, string $message, \Throwable $exception = null): void {
    $log_message = "[HPL] {$component}: #{$post_id} - {$message}";
    if ($exception) {
      $log_message .= ' (' . $exception->getMessage() . ')';
    }
    error_log($log_message);
  }

  /**
   * Check if we should require LLM for a given stage
   */
  public static function stage_requires_llm(string $stage): bool {
    return in_array($stage, ['new', 'classified', 'rewritten'], true);
  }

  /**
   * Get configurable publish threshold
   */
  public static function get_publish_threshold(): int {
    $config = get_option('hpl_config', []);
    return (int) ($config['publish_threshold'] ?? 80);
  }
}