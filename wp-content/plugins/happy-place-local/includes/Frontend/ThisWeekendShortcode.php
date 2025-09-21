<?php
namespace HappyPlace\Local\Frontend;

class ThisWeekendShortcode {
  public static function render($atts = []) {
    $atts = shortcode_atts([
      'city'  => '',
      'limit' => 6,
    ], $atts, 'hpl_this_weekend');

    $tz = wp_timezone();
    $now = new \DateTime('now', $tz);
    $weekDay = (int)$now->format('N'); // 1..7
    $diffToFri = 5 - $weekDay;
    $fri = (clone $now)->modify( ($diffToFri >= 0 ? '+' : '') . $diffToFri . ' days')->setTime(0,0,0);
    $sun = (clone $fri)->modify('+2 days')->setTime(23,59,59);

    $meta_query = [
      ['key'=>'start_datetime','value'=>$fri->format('c'),'compare'=>'>=','type'=>'CHAR'],
      ['key'=>'start_datetime','value'=>$sun->format('c'),'compare'=>'<=','type'=>'CHAR'],
    ];
    if (!empty($atts['city'])) {
      $meta_query[] = ['key'=>'hpl_city_slug','value'=>sanitize_title($atts['city']),'compare'=>'='];
    }

    $q = new \WP_Query([
      'post_type'      => 'local_event',
      'posts_per_page' => (int)$atts['limit'],
      'meta_query'     => $meta_query,
      'orderby'        => 'meta_value',
      'meta_key'       => 'start_datetime',
      'order'          => 'ASC',
      'no_found_rows'  => true,
    ]);

    ob_start();
    echo '<div class="hpl-grid hpl-grid--events">';
    if ($q->have_posts()) {
      while ($q->have_posts()) { $q->the_post();
        include HPL_PATH . 'templates/parts/card-event.php';
      }
      wp_reset_postdata();
    } else {
      echo '<p>No events found for this weekend.</p>';
    }
    echo '</div>';
    return ob_get_clean();
  }
}
