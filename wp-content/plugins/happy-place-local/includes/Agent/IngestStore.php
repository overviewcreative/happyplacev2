<?php
namespace HappyPlace\Local\Agent;

class IngestStore {

  /**
   * Create an ingest item.
   */
  public static function create(array $payload, string $type='event'): int {
    $title = $payload['title'] ?? ($payload['name'] ?? ucfirst($type));
    $post_id = wp_insert_post([
      'post_type'   => 'hpl_ingest',
      'post_status' => 'publish',
      'post_title'  => wp_strip_all_tags($title),
      'post_content'=> isset($payload['body']) && is_string($payload['body']) ? wp_kses_post($payload['body']) : '',
    ], true);

    if (is_wp_error($post_id) || !$post_id) return 0;

    update_post_meta($post_id, '_hpl_type', $type);
    // Preferred raw slot
    update_post_meta($post_id, '_hpl_raw', wp_json_encode($payload));
    update_post_meta($post_id, '_hpl_stage', 'new');

    return (int)$post_id;
  }

  /**
   * Read normalized raw payload for an ingest post.
   * Looks in _hpl_raw (preferred), then _hpl_raw_data, then try post_content (JSON).
   * Never fatals; always returns array.
   */
  public static function read(int $post_id): array {
    try {
      if ($post_id <= 0) {
        error_log('[HPL] IngestStore::read - invalid post_id: ' . $post_id);
        return [];
      }
      
      // 1) Preferred: _hpl_raw (JSON or array)
      $raw = get_post_meta($post_id, '_hpl_raw', true);
      if (is_array($raw)) {
        return $raw;
      } elseif (is_string($raw) && $raw !== '') {
        $j = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($j)) {
          return $j;
        }
      }

      // 2) Legacy/alt: _hpl_raw_data (Admin import_places)
      $alt = get_post_meta($post_id, '_hpl_raw_data', true);
      if (is_array($alt)) {
        return $alt;
      } elseif (is_string($alt) && $alt !== '') {
        $j = json_decode($alt, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($j)) {
          return $j;
        }
      }

      // 3) Fallback: post_content might contain JSON
      $post = get_post($post_id);
      if ($post && !empty($post->post_content)) {
        $j = json_decode($post->post_content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($j)) {
          return $j;
        }
      }

      // 4) Nothing found → empty payload
      return [];
    } catch (\Throwable $e) {
      error_log('[HPL] IngestStore::read failed for #' . $post_id . ': ' . $e->getMessage());
      return [];
    }
  }

  /**
   * Convenience: write arbitrary meta (arrays auto-JSON-encoded).
   * If array → save array; if object → json_encode; if scalar → save scalar.
   * Never fatals; logs errors gracefully.
   */
  public static function write(int $post_id, string $key, $val): void {
    try {
      if ($post_id <= 0 || empty($key)) {
        error_log('[HPL] IngestStore::write - invalid params: post_id=' . $post_id . ', key=' . $key);
        return;
      }
      
      if (is_array($val)) {
        update_post_meta($post_id, $key, $val);
      } elseif (is_object($val)) {
        update_post_meta($post_id, $key, wp_json_encode($val));
      } else {
        // Handle scalar values including null, empty string, etc.
        update_post_meta($post_id, $key, $val);
      }
    } catch (\Throwable $e) {
      error_log('[HPL] IngestStore::write failed for #' . $post_id . ' key ' . $key . ': ' . $e->getMessage());
    }
  }

  /**
   * Stage setter.
   * Never fatals; validates inputs.
   */
  public static function stage(int $post_id, string $stage): void {
    try {
      if ($post_id <= 0 || empty($stage)) {
        error_log('[HPL] IngestStore::stage - invalid params: post_id=' . $post_id . ', stage=' . $stage);
        return;
      }
      
      update_post_meta($post_id, '_hpl_stage', $stage);
    } catch (\Throwable $e) {
      error_log('[HPL] IngestStore::stage failed for #' . $post_id . ': ' . $e->getMessage());
    }
  }

  /**
   * Read meta as mixed (array if JSON, scalar otherwise).
   * Returns array when meta is array or JSON string; when scalar, wrap as ['value' => scalar].
   * Never fatals; always returns array.
   */
  public static function read_meta(int $post_id, string $key, $default = null) {
    try {
      if ($post_id <= 0 || empty($key)) {
        error_log('[HPL] IngestStore::read_meta - invalid params: post_id=' . $post_id . ', key=' . $key);
        return $default !== null ? $default : [];
      }
      
      $raw = get_post_meta($post_id, $key, true);
      if ($raw === '' || $raw === null) {
        return $default !== null ? $default : [];
      }
      
      if (is_array($raw)) {
        return $raw;
      }
      
      if (is_string($raw)) {
        $d = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($d)) {
          return $d;
        }
      }
      
      // For scalar values, wrap as array according to spec
      return ['value' => $raw];
    } catch (\Throwable $e) {
      error_log('[HPL] IngestStore::read_meta failed for #' . $post_id . ' key ' . $key . ': ' . $e->getMessage());
      return $default !== null ? $default : [];
    }
  }

  /**
   * Read integer meta with default.
   * Never fatals; validates inputs and returns safe defaults.
   */
  public static function read_int(int $post_id, string $key, int $default = 0): int {
    try {
      if ($post_id <= 0 || empty($key)) {
        error_log('[HPL] IngestStore::read_int - invalid params: post_id=' . $post_id . ', key=' . $key);
        return $default;
      }
      
      $v = get_post_meta($post_id, $key, true);
      if ($v === '' || $v === null) {
        return $default;
      }
      
      return (int)$v;
    } catch (\Throwable $e) {
      error_log('[HPL] IngestStore::read_int failed for #' . $post_id . ' key ' . $key . ': ' . $e->getMessage());
      return $default;
    }
  }
}
