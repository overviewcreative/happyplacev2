<?php
namespace HappyPlace\Local\Agent;

use HappyPlace\Local\Agent\Tasks\Classify;
use HappyPlace\Local\Agent\Tasks\Enrich;
use HappyPlace\Local\Agent\Tasks\Rewrite;
use HappyPlace\Local\Agent\Tasks\Score;
use HappyPlace\Local\Agent\Tasks\Publish;

class Runner {
  public static function tick() {
    // Fetch new items from existing ingestors (stub hook)
    do_action('hpl/agent/fetch_sources');

    // Process a small batch from ingest CPT
    $q = new \WP_Query([
      'post_type' => 'hpl_ingest',
      'posts_per_page' => 10,
      'meta_key' => '_hpl_stage',
      'meta_value' => ['new','classified','enriched','rewritten','scored'],
      'meta_compare' => 'IN',
      'orderby' => 'date',
      'order' => 'ASC'
    ]);

    while ($q->have_posts()) { $q->the_post();
      $id = get_the_ID();
      $stage = get_post_meta($id, '_hpl_stage', true) ?: 'new';
      switch ($stage) {
        case 'new':        Classify::handle($id);   break;
        case 'classified': Enrich::handle($id);     break;
        case 'enriched':   Rewrite::handle($id);    break;
        case 'rewritten':  Score::handle($id);      break;
        case 'scored':     Publish::handle($id);    break;
      }
      // Avoid long loops in single tick
      if (rand(0,100) < 5) break;
    }
    wp_reset_postdata();
  }
}
