<?php
namespace HappyPlace\Local\Admin;

class Columns {
  public static function boot() {
    add_filter('manage_local_event_posts_columns', [self::class,'cols_event']);
    add_action('manage_local_event_posts_custom_column', [self::class,'render_event'], 10, 2);
    add_filter('manage_local_place_posts_columns', [self::class,'cols_place']);
    add_action('manage_local_place_posts_custom_column', [self::class,'render_place'], 10, 2);
  }
  public static function cols_event($cols) { $cols['hpl_city']='City'; $cols['hpl_start']='Start'; $cols['hpl_price']='Price'; return $cols; }
  public static function render_event($col, $post_id) {
    if ($col==='hpl_city') echo esc_html(get_post_meta($post_id,'hpl_city_slug',true));
    if ($col==='hpl_start') echo esc_html(get_post_meta($post_id,'start_datetime',true));
    if ($col==='hpl_price') echo esc_html(get_post_meta($post_id,'price',true));
  }
  public static function cols_place($cols) { $cols['hpl_latlng']='Lat/Lng'; return $cols; }
  public static function render_place($col, $post_id) {
    if ($col==='hpl_latlng') {
      $lat = get_post_meta($post_id,'lat',true); $lng = get_post_meta($post_id,'lng',true);
      echo esc_html(trim($lat.' , '.$lng, ' ,'));
    }
  }
}
