<?php
namespace HappyPlace\Local\Relations;

class BindCity {
  public static function sync($post_id, $post, $update) {
    if (wp_is_post_revision($post_id)) return;
    $types = ['local_event','local_place'];
    if (!in_array($post->post_type, $types, true)) return;

    if (function_exists('get_field')) {
      $city_id = (int) get_field('primary_city', $post_id);
      if ($city_id) {
        $slug = get_post_field('post_name', $city_id);
        update_post_meta($post_id, 'hpl_city_slug', $slug ?: '');
        update_post_meta($post_id, 'hpl_city_id', $city_id);
      }
    }
  }
}
