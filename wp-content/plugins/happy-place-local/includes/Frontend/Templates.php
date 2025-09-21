<?php
namespace HappyPlace\Local\Frontend;

class Templates {
  public static function maybe_templates($template) {
    if (is_post_type_archive('local_event')) {
      $t = HPL_PATH . 'templates/archive-local_event.php';
      if (file_exists($t)) return $t;
    }
    return $template;
  }
}
