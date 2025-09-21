<?php
namespace HappyPlace\Local\Frontend;

class Assets {
  public static function enqueue() {
    wp_register_style('hpl-frontend', HPL_URL . 'assets/css/frontend.css', [], HPL_VERSION);
    wp_enqueue_style('hpl-frontend');
  }
}
