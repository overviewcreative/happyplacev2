<?php
namespace HappyPlace\Local\Admin;

class PlaceMeta {
  public static function register() {
    add_meta_box('hpl_place_details', 'Place Details', [self::class, 'box'], 'local_place', 'side', 'default');
  }
  public static function box($post) {
    wp_nonce_field('hpl_place_meta', 'hpl_place_meta_nonce');
    $lat = get_post_meta($post->ID, 'lat', true);
    $lng = get_post_meta($post->ID, 'lng', true);
    $hrs = get_post_meta($post->ID, 'hours_json', true);
    echo '<p><label>Latitude</label><br/><input type="text" name="lat" value="'.esc_attr($lat).'" style="width:100%"/></p>';
    echo '<p><label>Longitude</label><br/><input type="text" name="lng" value="'.esc_attr($lng).'" style="width:100%"/></p>';
    echo '<p><label>Hours (JSON)</label><br/><textarea name="hours_json" rows="3" style="width:100%">'.esc_textarea($hrs).'</textarea></p>';
  }
  public static function save($post_id) {
    if (!isset($_POST['hpl_place_meta_nonce']) || !wp_verify_nonce($_POST['hpl_place_meta_nonce'], 'hpl_place_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = ['lat','lng','hours_json'];
    foreach ($fields as $f) {
      if (isset($_POST[$f])) {
        $val = ('hours_json' === $f) ? wp_kses_post($_POST[$f]) : sanitize_text_field($_POST[$f]);
        update_post_meta($post_id, $f, $val);
      }
    }
  }
}
