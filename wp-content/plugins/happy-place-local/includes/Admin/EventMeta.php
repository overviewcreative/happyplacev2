<?php
namespace HappyPlace\Local\Admin;

class EventMeta {
  public static function register() {
    add_meta_box('hpl_event_details', 'Event Details', [self::class, 'box'], 'local_event', 'side', 'default');
  }
  public static function box($post) {
    wp_nonce_field('hpl_event_meta', 'hpl_event_meta_nonce');
    $start = get_post_meta($post->ID, 'start_datetime', true);
    $end   = get_post_meta($post->ID, 'end_datetime', true);
    $price = get_post_meta($post->ID, 'price', true);
    $src   = get_post_meta($post->ID, 'source_url', true);
    $city  = get_post_meta($post->ID, 'hpl_city_slug', true);
    echo '<p><label>Start</label><br/><input type="datetime-local" name="start_datetime" value="'.esc_attr(self::toLocal($start)).'" style="width:100%"/></p>';
    echo '<p><label>End</label><br/><input type="datetime-local" name="end_datetime" value="'.esc_attr(self::toLocal($end)).'" style="width:100%"/></p>';
    echo '<p><label>Price</label><br/><input type="text" name="price" value="'.esc_attr($price).'" style="width:100%"/></p>';
    echo '<p><label>Source URL</label><br/><input type="url" name="source_url" value="'.esc_attr($src).'" style="width:100%"/></p>';
    echo '<p><label>City Slug (fallback if ACF not installed)</label><br/><input type="text" name="hpl_city_slug" value="'.esc_attr($city).'" placeholder="georgetown" style="width:100%"/></p>';
  }
  private static function toLocal($iso) {
    if (!$iso) return '';
    return preg_replace('/\+.*$/','', str_replace('Z','', $iso));
  }
  public static function save($post_id) {
    if (!isset($_POST['hpl_event_meta_nonce']) || !wp_verify_nonce($_POST['hpl_event_meta_nonce'], 'hpl_event_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = ['start_datetime','end_datetime','price','source_url','hpl_city_slug'];
    foreach ($fields as $f) {
      if (isset($_POST[$f])) {
        $val = sanitize_text_field($_POST[$f]);
        if (in_array($f, ['start_datetime','end_datetime']) && $val) {
          $val = date('c', strtotime($val));
        }
        update_post_meta($post_id, $f, $val);
      }
    }
  }
}
