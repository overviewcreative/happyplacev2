<?php
namespace HappyPlace\Local\Services;

class CityWriter {
  public function upsert(array $payload): int {
    $slug = sanitize_title($payload['name'] ?? '');
    if (!$slug) return 0;
    $existing = get_page_by_path($slug, OBJECT, 'city');
    $postarr = [
      'post_type'   => 'city',
      'post_status' => 'publish',
      'post_title'  => $payload['name'],
      'post_name'   => $slug
    ];
    $post_id = $existing ? (wp_update_post(['ID'=>$existing->ID] + $postarr, true) ?: 0)
                         : (wp_insert_post($postarr, true) ?: 0);
    if (is_wp_error($post_id) || !$post_id) return 0;

    $set = function(string $k, $v) use ($post_id) {
      if (function_exists('update_field')) update_field($k, $v, $post_id);
      else update_post_meta($post_id, $k, $v);
    };
    $set('state', $payload['state'] ?? '');
    $set('county', $payload['county'] ?? '');
    $set('population', $payload['population'] ?? '');
    $set('lat', $payload['lat'] ?? '');
    $set('lng', $payload['lng'] ?? '');
    $set('tagline', $payload['tagline'] ?? '');
    $set('description', $payload['description'] ?? '');

    if (!empty($payload['hero_image_url'])) {
      $att_id = media_sideload_image($payload['hero_image_url'], $post_id, $payload['name'], 'id');
      if (!is_wp_error($att_id)) set_post_thumbnail($post_id, $att_id);
    }

    if (!empty($payload['external_links']) && is_array($payload['external_links']) && function_exists('update_field')) {
      $rows = [];
      foreach ($payload['external_links'] as $link) {
        $rows[] = ['label' => $link['label'] ?? '', 'url' => $link['url'] ?? ''];
      }
      update_field('external_links', $rows, $post_id);
    }
    return $post_id;
  }
}
